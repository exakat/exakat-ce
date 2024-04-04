<?php declare(strict_types = 1);
/*
 * Copyright 2012-2024 Damien Seguy – Exakat SAS <contact(at)exakat.io>
 * This file is part of Exakat.
 *
 * Exakat is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Exakat is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with Exakat.  If not, see <http://www.gnu.org/licenses/>.
 *
 * The latest code can be found at <http://exakat.io/>.
 *
*/

namespace Exakat\Tasks\Helpers;

use Exakat\Tasks\Helpers\Links;

class VariablesUsageContext {
	private const LINK = 'PUIS';
	public const LINEAR = false;
	public const NEST = true;

    private  $links;
	
    private array $list = array();

	private array $branches = array();
	private ?array $root = null;
	private array  $closed = array();

	private array $nests = array();
	private array $roots = array();
	
	private bool $debug = false;
    
    public function __construct($links) {
    	$this->links = $links;
    }

    public function addRoot(AtomInterface $atom, ?string $name = null): void {
		if (in_array($atom->token, array('T_DOLLAR_OPEN_CURLY_BRACES', 'T_DOLLAR'))) { return ; }
    	assert($name === '$this' || empty($this->roots[$name ?? $atom->code]), "root already exists for $atom->code\n");
    	$this->roots[$name ?? $atom->code] = array($atom->id => $atom);
    	$this->list[$name ?? $atom->code] = array($atom->id => $atom);
    }

    public function addRoots(array $atoms): void {
    	foreach($atoms as $atom) {
			if (in_array($atom->token, array('T_DOLLAR_OPEN_CURLY_BRACES', 'T_DOLLAR'))) { continue ; }
    		// @todo special case for $this
    		if (isset($this->roots[$atom->code])) { continue; } // Skip if already existings for fn (conflict between parameter and variable)
    		$this->roots[$atom->code] = array($atom->id => $atom);
    		$this->list[$atom->code] = array($atom->id => $atom);
    	}
    }

    public function finishCycle(): void {
    	foreach($this->list as $name => $atom) {
    		if ($name === '$this' && !isset($this->roots[$name])) { continue; }
	    	assert(isset($this->roots[$name]), "no root for finishCycle with $name");
		    foreach($this->roots[$name] as $roots) {
		    	$this->finishVariable($name, $roots);
		    }
    	}
    }

    public function unsetVariable(string $name, AtomInterface $atom) : void {
		if (in_array($atom->token, array('T_DOLLAR_OPEN_CURLY_BRACES', 'T_DOLLAR'))) { return ; }
	    assert(!empty($this->roots[$name]), "roots $name doesn't exist to unset\n");
	    
		foreach($this->roots[$name] as $roots) {
			$this->finishVariable($name, $roots);
	    }
	    
	    $this->list[$name] = array($atom->id => $this->roots[$name]);
		$this->addLink($name, $atom);
	    $this->list[$name] = array($atom->id => $atom);
	    
	    foreach($this->roots[$name] as $root) {
		    unset($this->closed[$root->id]);
	    }
    }
    
    private function finishVariable(string $name, AtomInterface $atom) : void {
		if (isset($this->closed[$atom->id])) { 
			return; 
		}

		$this->addLink($name, $atom);
		$this->closed[$atom->id] = 1;

	    unset($this->list[$name]);
    }

    public function closeCycle(): void {
    	$this->finishCycle();
    }

    public function followup(AtomInterface $atom): void {
//    		print 'followup '.$atom->code.PHP_EOL;
		if (!in_array($atom->atom, array('Variable', 'Variableobject', 'Variablearray', 'This', 'Staticdefinition'), STRICT_COMPARISON)) { 
//			print "Refusing $atom->atom\n";
			return; 
		}
		if (in_array($atom->token, array('T_DOLLAR_OPEN_CURLY_BRACES', 'T_DOLLAR'))) { return ; }

//		print "Followup $atom->fullcode $atom->id\n";

		$this->addLink($atom->fullcode, $atom);
		$name = trim($atom->fullcode, '&.@');
        $this->list[$name] = array($atom->id => $atom);
    }

    public function nest(): void {
//    		print 'nest '.PHP_EOL;
    	$this->nests[] = array(
    		// @todo remove unused elememnts
    		'list' => $this->list,
    		'branches' => $this->branches,
    		'root' => $this->root,
    		'roots' => $this->roots,
    	);
    	
    	$this->root = null;
    	$this->branches = array();
    	$this->list = array();
    	// @todo : may be not always transmit $this (static fn() ...)
    	if (isset($this->roots['$this'])) {
	    	$this->roots = array('$this' => $this->roots['$this']);
	    	$this->list  = array('$this' => $this->roots['$this']);
    	} else {
	    	$this->roots = array();
	    	$this->list  = array();
    	}
    }
    
    public function unnest(): void {
//    		print 'unnest '.PHP_EOL;
    	$nest = array_pop($this->nests);
    	
    	$this->root = $nest['root'];
    	$this->roots = $nest['roots'];
    	$this->branches = $nest['branches'];
    	$this->list = $nest['list'];
    }
    
    public function split(bool $type = self::LINEAR): void {
    	if ($type === self::NEST) {
//	    	print "split-nest\n";
    		$this->nests[] = array(
    			// @todo remove unused elememnts
    			'list' => $this->list,
    			'branches' => $this->branches,
    			'root' => $this->root,
    			'roots' => $this->list,
    		);
    		$this->root = $this->list;
	    	$this->branches = array();

	    	return;
    	}

    	if ($this->root === null) {
//	    	print "split-null\n";
    		$this->root = $this->list;
	    	$this->branches = array();
    	} else {
//	    	print "split-next\n";
	    	$this->branches[] = $this->list;
	    	$this->list = $this->root;
    	}
    }

    public function merge(bool $type = self::LINEAR): void {
//	    print "merge-nest (".count($this->nests).")\n";
//    	assert(!empty($this->nests));
    	$nest = array_pop($this->nests); 

    	$branches = $this->branches;
    	$branches[] = $this->list;

    	$this->list = array();
    	foreach($branches as $branch) {
    		foreach($branch as $name => $atom) {
    			if (isset($this->list[$name])) {
	    			foreach($atom as $a) {
		  				$this->list[$name][$a->id] = $a;
    				}
    			} else {
	    			foreach($atom as $a) {
		    			$this->list[$name] = array($a->id => $a);
    				}
    			}
    		}
    	}

		$this->root = $nest['root'];
		$this->branches = $nest['branches'];
	}

    public function addLink(string $name, AtomInterface $atom): void {
		if (!isset($this->list[$name])) {
//	        print "0-link\n";
			return;
		}
		
		if ($this->list[$name] instanceof AtomInterface) {
			die("No more simple atoms, only arrays");
		}
		
		if (is_array($this->list[$name])) {
			foreach($this->list[$name] as $origin) {
//				print "$origin->fullcode $origin->line\n";
				if (is_array($origin)) {
					foreach($origin as $o) {
						if (is_array($o)) { print_r($this->list);;die(ici2); }
				        $this->links->addLink($o, $atom, self::LINK);
					}
				} else {
					if ($origin->atom === 'Class' && $atom->atom === 'Class') { continue;  } // skip CLass -> Class
			        $this->links->addLink($origin, $atom, self::LINK);
				}
			}
	        return;
		}
    }

    public function checkContext(): bool {
    	return !empty($this->nests);
    }
    
    public function exportList() : array {
    	$return = array();
    	
    	foreach($this->list as $name => $atom) {
    		if (is_array($atom)) {
    			foreach($atom as $atom2) {
	    			$return[$name] = $atom2->line;
    			}
    		} elseif ($atom instanceof Atom) {
	    		$return[$name] = $atom->line;
    		} else {
    			die(error);
    		}
    	}

    	return $return;
    }

	function print_r($print = true) : ?string {
		$return = [];
		$return []= "List (".count($this->list)."): ";
		foreach($this->list as $name => $atom) {
			if (is_array($atom)) {
				$return []= ' +'. $name." => ".implode(', ', array_keys($atom));
			} else {
				$return []= ' +'. $name." => ".$atom->id;
			}
		}
		$return []= '';

		$return []= "Root (".count($this->root ?? [])."): ";
		foreach($this->root ?? [] as $name => $atom) {
			if (is_array($atom)) {
				$return []= ' +'. $name." => ".implode(', ', array_keys($atom));
			} else {
				$return []= ' +'. $name." => ".$atom->id;
			}
		}
		$return []= '';

		$return []= "Branches (".count($this->branches ?? [])."): ";
		foreach($this->branches ?? [] as $idBranch => $branch) {
			foreach($branch as $name => $atom) {
				if (is_array($atom)) {
					$return []= '   +'. $name." => ".implode(', ', array_keys($atom));
				} else {
					$return []= '   +'. $name." => ".$atom->id;
				}
			}
		}
		$return []= '';

		if ($print) {
			print implode(PHP_EOL, $return).PHP_EOL;
			return null;
		} else {
			return implode(PHP_EOL, $return).PHP_EOL;
		} 
	}

	function importFromPrevious(string $name) : void {
		$index = array_key_last($this->nests);

		if (!isset($this->nests[$index]['list'][$name])) { 
			return; 
		}

		$this->list[$name] = $this->nests[$index]['list'][$name];
		$this->roots[$name] = $this->nests[$index]['list'][$name];
	}

}

?>
