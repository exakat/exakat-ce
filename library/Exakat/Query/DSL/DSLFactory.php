<?php
/*
 * Copyright 2012-2022 Damien Seguy – Exakat SAS <contact(at)exakat.io>
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
declare(strict_types = 1);

namespace Exakat\Query\DSL;

use Exakat\Exceptions\UnknownDsl;
use Exakat\GraphElements;
use Exakat\Analyzer\Analyzer;
use Exakat\Data\Dictionary;
use Exakat\Datastore;

class DSLFactory {
    public const VARIABLE_WRITE = true;
    public const VARIABLE_READ  = false;

    private array $availableAtoms         = array();
    private array $availableLinks         = array();
    private array $availableFunctioncalls = array();
    private array $availableVariables     = array(); // This one is per query
    protected array $availableLabels        = array(); // This one is per query
    protected array $ignoredcit             = array();
    protected array $ignoredfunctions       = array();
    protected array $ignoredconstants       = array();
    protected Dictionary $dictCode;
    protected Datastore $datastore;
    protected string $linksDown              = '';
    protected array $dependsOn               = array();
    protected string $analyzerQuoted         = '';
    protected int $MAX_LOOPING               = Analyzer::MAX_LOOPING;

    public function __construct(string $analyzer, array $dependsOn = array()) {
        $this->dependsOn = $dependsOn;
        $this->analyzerQuoted = $analyzer;


        $this->dictCode  = Dictionary::getInstance();
        $this->datastore = exakat('datastore');

        $this->linksDown = GraphElements::linksAsList();

        if (empty($this->availableAtoms)) {
            $data = $this->datastore->getCol('TokenCounts', 'token');

            $this->availableAtoms = array('Project',
                                          'File',
                                          'Virtualproperty',
                                          'Virtualglobal',
                                          'Virtualmethod',
                                          'Void',

                                          'Analysis',
                                          'Noresult',
                                          'Identifier',
                                          'Scalartypehint',  // This one may be added by Complete/Returntype
                                          );
            $this->availableLinks = array('DEFINITION',
                                          'ANALYZED',
                                          'PROJECT',
                                          'FILE',
                                          'OVERWRITE',
                                          'PPP',
                                          'DEFAULT',
                                          'RETURNED',
                                          'TYPEHINT',
                                          );

            foreach ($data as $token) {
                if ($token === strtoupper($token)) {
                    $this->availableLinks[] = $token;
                } else {
                    $this->availableAtoms[] = $token;
                }
            }

            $this->availableFunctioncalls = $this->datastore->getCol('functioncalls', 'functioncall');

            $this->ignoredcit       = $this->datastore->getCol('ignoredcit',       'fullnspath');
            $this->ignoredfunctions = $this->datastore->getCol('ignoredfunctions', 'fullnspath');
            $this->ignoredconstants = $this->datastore->getCol('ignoredconstants', 'fullnspath');
        }
    }

    public function factory(string $name): Dsl {
        if (strtolower($name) === '_as') {
            $className = __NAMESPACE__ . '\\_As';
        } else {
            $className = __NAMESPACE__ . '\\' . ucfirst($name);
        }

        if (!class_exists($className)) {
            throw new UnknownDsl($name);
        }

        $return = new $className($this,
            $this->availableAtoms,
            $this->availableLinks,
            $this->availableFunctioncalls,
            $this->availableVariables,
            $this->availableLabels,
            $this->ignoredcit,
            $this->ignoredfunctions,
            $this->ignoredconstants,
            $this->dependsOn,
            $this->analyzerQuoted
        );

        $last = explode('\\', $return::class);
        $last = array_pop($last);
        assert($className == $return::class, "Wrongly Cased DSL : $name instead of " . $last);

        return $return;
    }
}

?>
