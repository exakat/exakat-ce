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

namespace Exakat\Analyzer;

use Exakat\Config;
use Exakat\Data\Dictionary;
use Exakat\Data\Methods;
use Exakat\Graph\Graph;
use Exakat\GraphElements;
use Exakat\Exceptions\GremlinException;
use Exakat\Exceptions\NoSuchAnalyzer;
use Exakat\Exceptions\UnknownDsl;
use Exakat\Query\Multiple;
use Exakat\Datastore;
use Exakat\Project;
use Exakat\Helpers\Called;
use Exakat\Graph\Helpers\GraphResults;
use Exakat\Query\DSL\Command;
use Exakat\Phpexec;
use Exakat\Fileset\{IgnoreDirs, FileExtensions, Namespaces};
use Exakat\Stubs\PdffReader;
use Exakat\Stubs\StubsInterface;
use Exakat\Stubs\Stubs;
use stdClass;
use Exakat\Helpers\Directive;

abstract class Analyzer {
    // Query types
    public const QUERY_DEFAULT       = 1;   // For compatibility purposes
    public const QUERY_ANALYZER      = 2;   // same as above, but explicit
    public const QUERY_VALUE         = 3;   // returns a single value
    public const QUERY_RAW           = 4;   // returns data, no storage
    public const QUERY_HASH          = 5;   // returns a list of values
    public const QUERY_MULTIPLE      = 6;   // returns several links at the same time (TBD)
    public const QUERY_ARRAYS        = 7;   // arrays of array
    public const QUERY_TABLE         = 8;   // to specific table
    public const QUERY_MISSING       = 9;   // store values that are not in the graph
    public const QUERY_PHP_ARRAYS    = 10;  // store a PHP array of values into hashResults
    public const QUERY_PHP_HASH      = 11;  // store a PHP array of values into hashResults
    public const QUERY_NO_ANALYZED   = 12;  // store links, but not the ANALYZED one
    public const QUERY_RESULTS       = 13;  // store results directly to dump, no ANALYZED
    public const QUERY_HASH_ANALYZER = 14;  // store results directly to hashAnalyzer

    public const LINK_ANALYZED     = true;
    public const PROPERTY_COMPLETE = false;

    protected Datastore $datastore;

    protected int $rowCount       = 0; // Number of found values
    protected int $processedCount = 0; // Number of initial values
    protected int $queryCount     = 0; // Number of ran queries
    protected int $rawQueryCount  = 0; // Number of ran queries

    private array $filters          = array(); // list of filters for files and namespaces

    private array    $queries          = array();
    private Multiple $query;

    public Config $config ;

    protected Called $called;

    private static array $pdffCache = array();
    private static array $jsonCache = array();
    private static array $iniCache  = array();

    private string $analyzer         = '';       // Current class of the analyzer (called from below)
    protected string $shortAnalyzer    = '';
    protected string $analyzerQuoted   = '';
    protected int    $analyzerId       = 0;
    protected int    $queryId          = 0;

    protected string $analyzerName      = 'no analyzer name';
    protected string $analyzerTable     = 'no analyzer table name';
    protected string $analyzerSQLTable  = 'no analyzer sql creation';
    protected array  $missingQueries    = array();
    protected array  $analyzerValues    = array();
    protected int    $storageType       = self::QUERY_DEFAULT;

    protected string $phpVersion        = self::PHP_VERSION_ANY;
    protected array  $phpConfiguration  = array('Any');

    private string $exakatSince         = '';

    public const S_CRITICAL = 'Critical';
    public const S_MAJOR    = 'Major';
    public const S_MINOR    = 'Minor';
    public const S_NOTE     = 'Note';
    public const S_NONE     = 'None';

    public const T_NONE    = 'None';    //'0';
    public const T_INSTANT = 'Instant'; //'5';
    public const T_QUICK   = 'Quick';   //30';
    public const T_SLOW    = 'Slow';    //60';
    public const T_LONG    = 'Long';    //360';

    public const P_VERY_HIGH = 'very-high';
    public const P_HIGH      = 'high';
    public const P_MEDIUM    = 'medium';
    public const P_LOW       = 'Low';
    public const P_NONE      = 'Unknown';
    public const P_UNKNOWN   = 'Unknown';

    public const PHP_VERSION_ANY = 'Any';

    public const COMPATIBLE                 =  0;
    public const UNKNOWN_COMPATIBILITY      = -1;
    public const VERSION_INCOMPATIBLE       = -2;
    public const CONFIGURATION_INCOMPATIBLE = -3;

    public const CASE_SENSITIVE   = true;
    public const CASE_INSENSITIVE = false;

    public const WITH_CONSTANTS    = 1;
    public const WITHOUT_CONSTANTS = false;

    public const WITH_VARIABLES    = 2;
    public const WITHOUT_VARIABLES = false;

    public const TRANSLATE    = true;
    public const NO_TRANSLATE = false;

    public const CONTAINERS       = array('Variable', 'Staticproperty', 'Member', 'Array');
    public const VARIABLES_USER   = array('Variable', 'Variableobject', 'Variablearray');
    public const CONTAINERS_PHP   = array('Variable', 'Staticproperty', 'Member', 'Array', 'Phpvariable', 'This', 'Arrayappend');
    public const CONTAINERS_ROOTS = array('Variable', 'Staticproperty', 'Member', 'Array', 'Variableobject', 'Variablearray');
    public const VARIABLES_SCALAR = array('Variable', 'Variableobject', 'Variablearray', 'Globaldefinition', 'Staticdefinition', 'Phpvariable', 'Parametername');
    public const VARIABLES_ALL    = array('Variable', 'Variableobject', 'Variablearray', 'Globaldefinition', 'Staticdefinition', 'Propertydefinition', 'Phpvariable', 'Parametername');
    public const ARGUMENTS        = array('Ppp', 'Parameter');
    public const PROPERTIES       = array('Member', 'Staticproperty');

    public const LITERALS         = array('Integer', 'Float', 'Null', 'Boolean', 'String', 'Heredoc');
    public const SCALARS          = array('Integer', 'Float', 'Null', 'Boolean', 'String', 'Heredoc', 'Arrayliteral');
    public const LOOPS_ALL        = array('For' , 'Foreach', 'While', 'Dowhile');
    public const SWITCH_ALL       = array('Switch' , 'Match');
    public const LOGICAL_ALL      = array('Logical', 'Bitoperation', 'Spaceship');

    public const FUNCTIONS_TOKENS = array('T_STRING', 'T_NS_SEPARATOR', 'T_ARRAY', 'T_EVAL', 'T_ISSET', 'T_EXIT', 'T_UNSET', 'T_ECHO', 'T_OPEN_TAG_WITH_ECHO', 'T_PRINT', 'T_LIST', 'T_EMPTY', 'T_OPEN_BRACKET', 'T_NAME_FULLY_QUALIFIED', 'T_NAME_RELATIVE', 'T_NAME_QUALIFIED');
    public const FUNCTIONS_ALL    = array('Function', 'Closure', 'Method', 'Magicmethod', 'Arrowfunction');

    public const FUNCTIONS_NAMED  = array('Function', 'Method', 'Magicmethod');
    public const FUNCTIONS        = array('Function', 'Closure', 'Arrowfunction');
    public const FUNCTIONS_ANONYMOUS = array('Closure', 'Arrowfunction');
    public const FUNCTIONS_METHOD = array('Method', 'Magicmethod');

    public const CIT              = array('Class', 'Classanonymous', 'Interface', 'Trait', 'Enum');
    public const CLASSES_ALL      = array('Class', 'Classanonymous');
    public const CLASSES_TRAITS   = array('Class', 'Classanonymous', 'Trait');
    public const RELATIVE_CLASS   = array('Parent', 'Static', 'Self');
    public const STATIC_NAMES     = array('Nsname', 'Identifier');
    public const STATICCALL_TOKEN = array('T_STRING', 'T_STATIC', 'T_NS_SEPARATOR', 'T_NAME_FULLY_QUALIFIED', 'T_NAME_RELATIVE', 'T_NAME_QUALIFIED');
    public const CLASS_ELEMENTS   = array('METHOD', 'MAGICMETHOD', 'PPP', 'CONST', 'USE');
    public const CLASS_METHODS    = array('METHOD', 'MAGICMETHOD');

    public const ATTRIBUTE_ATOMS  = array('Ppp', 'Method', 'Magicmethod', 'Propertydefinition', 'Class', 'Function', 'Closure', 'Arrowfunction', 'Const', 'Parameter', 'Enumcase', 'Enum', 'Interface', 'Trait');
    public const DEFINITIONS      = array('Closure', 'Classanonymous', 'Function', 'Class', 'Trait', 'Interface', 'Enum');

    public const FUNCTIONS_CALLS  = array('Functioncall', 'Newcall', 'Newcallname', 'Methodcall', 'Staticmethodcall', 'Self', 'Parent'); // @todo check for self parent and static
    public const CALLS            = array('Functioncall', 'Methodcall', 'Staticmethodcall' );
    public const METHOD_CALLS     = array('Methodcall', 'Staticmethodcall' );
    public const FUNCTIONS_USAGE  = array('Functioncall', 'Methodcall', 'Staticmethodcall', 'Eval', 'Echo', 'Print', 'Unset' );
    public const NEW_CALLS        = array('Newcall', 'Newcallname' );

    public const STRINGS_ALL      = array('Concatenation', 'Heredoc', 'String', 'Identifier', 'Nsname', 'Staticclass', 'Magicconstant');
    public const STRINGS_LITERALS = array('Concatenation', 'Heredoc', 'String', 'Magicconstant', 'Staticclass');

    public const CONSTANTS_ALL    = array('Identifier', 'Nsname');

    public const EXPRESSION_ATOMS = array('Addition', 'Multiplication', 'Power', 'Ternary', 'Not', 'Parenthesis', 'Functioncall' );
    public const TYPE_ATOMS       = array('Integer', 'String', 'Arrayliteral', 'Float', 'Boolean', 'Null', 'Closure', 'Concatenation', 'Magicconstant', 'Heredoc', 'Power' , 'Staticclass', 'Comparison', 'Not', 'Addition', 'Multiplication', 'Bitshift', 'Bitoperation', 'Logical');
    public const BREAKS           = array('Goto', 'Return', 'Break', 'Continue');
    public const ANONYMOUS        = array('Closure', 'Arrowfunction', 'Classanonymous');

    public const TYPEHINTABLE     = array('Parameter', 'Ppp', 'Function', 'Closure', 'Method', 'Magicmethod', 'Arrowfunction');
    public const TYPE_LINKS       = array('TYPEHINT', 'RETURNTYPE');

    public const SCALAR_TYPEHINTS = array('\\int', '\\\float', '\\object', '\\bool', '\\string', '\\array', '\\callable', '\\iterable', '\\void', '\\never');

    public const LEFT_RIGHT       = array('LEFT', 'RIGHT');

    public const INCLUDE_SELF     = false;
    public const EXCLUDE_SELF     = true;

    public const CONTEXT_IN_CLOSURE = 1;
    public const CONTEXT_OUTSIDE_CLOSURE = 2;

    protected const REDUCE_VALUE = true;
    protected const REDUCE_KEY   = false;

    public const MAX_LOOPING   = 15;    // hard limit for do...while when navigating the tree
    public const MAX_SEARCHING = 8;     // hard limit for searching the tree (failing the rest is not bad)
    public const TIME_LIMIT    = 1000;  // 1s, used with timelimit() from gremlin.

    private static array $rulesId         = array();

    protected Rulesets $rulesets;

    protected Methods $methods;
    protected Graph $gremlin;
    protected Dictionary $dictCode;

    protected Stubs		$stubs		 ;
    protected Stubs		$phpCore	 ;
    protected Stubs		$extensions	 ;
    private bool		$initedStubs = false;

    protected string $linksDown;

    public function __construct() {
        assert(func_num_args() === 0, 'Too many arguments for ' . static::class);

        $this->analyzer       = static::class;
        $this->analyzerQuoted = self::getName($this->analyzer);
        $this->shortAnalyzer  = str_replace('\\', '/', substr($this->analyzer, 16));

        $this->config    = exakat('config');
        $this->rulesets  = exakat('rulesets');
        $this->gremlin   = exakat('graphdb');
        $this->called    = new Called($this->gremlin);
        $this->datastore = exakat('datastore');
        $this->datastore->reuse();

        $this->dictCode  = Dictionary::getInstance();
        $docs            = exakat('docs');

        if (!str_contains($this->analyzer, '\\Common\\')  ) {
            $parameters        = $docs->getDocs($this->shortAnalyzer)['parameter'];
            $this->exakatSince = $docs->getDocs($this->shortAnalyzer)['exakatSince'];

            if (isset($this->config->{$this->analyzerQuoted}['ignore_dirs']) ) {
                $this->filters[] = new IgnoreDirs(makeArray($this->config->{$this->analyzerQuoted}['ignore_dirs'] ?? array() ),
                    makeArray($this->config->{$this->analyzerQuoted}['include_dirs'] ?? array() )
                );
            }

            if (isset($this->config->{$this->analyzerQuoted}['file_extensions']) ) {
                $this->filters[] = new FileExtensions(makeArray($this->config->{$this->analyzerQuoted}['file_extensions'] ?? array() ));
            }

            if (isset($this->config->{$this->analyzerQuoted}['namespaces']) ) {
                $this->filters[] = new Namespaces(makeArray($this->config->{$this->analyzerQuoted}['namespaces'] ?? array() ));
            }

            foreach ($parameters as $parameter) {
                assert(isset($this->{$parameter['name']}), "Missing definition for library/Exakat/Analyzer/$this->analyzerQuoted.php :\nprotected \$$parameter[name] = '" . ($parameter['default'] ?? '') . "';\n");
                if (isset($this->config->directives[$parameter['name']])) {
                    $value = $this->config->directives[$parameter['name']];

                    if (!isset($parameter['default'])) {
                        continue;
                    }
                    /*
                } elseif (isset($this->config->{$this->analyzerQuoted}[$parameter['name']])) {
                    $value = $this->config->{$this->analyzerQuoted}[$parameter['name']];

                    if (!isset($parameter['default'])) {
                        continue;
                    }
                    */
                } elseif (isset($parameter['default'])) {
                    $value = new Directive();
                    $value->add($parameter['default']);
                } else {
                    // Else, we reuse the default values in the code
                    continue;
                }

                switch ($parameter['type']) {
                    case 'integer':
                        $this->{$parameter['name']} = (int) $value->single();
                        break;

                    case 'string':
                        $this->{$parameter['name']} = (string) $value->single();
                        break;

                    case 'data':
                        $value = $value->single();
                        if (is_string($value)) {
                            if (substr($value, -4) === 'json') {
                                $this->{$parameter['name']} = $this->loadJson($value);
                            } elseif (substr($value, -3) === 'ini') {
                                $this->{$parameter['name']} = $this->loadIni($value);
                            }
                        }
                        break;

                    case 'ini_hash':
                        $this->{$parameter['name']} = parse_ini_string($value->single())[$parameter['name']] ?? array();
                        break;

                    case 'json':
                        $this->{$parameter['name']} = json_decode($value->single(), true, 3, JSON_OBJECT_AS_ARRAY);
                        break;

                    case 'array':
                        $value = explode(',', $value->single());
                        $this->{$parameter['name']} = array_map('trim', $value);

                        break;

                    default :
                        // Nothing, really
                }
            }
        }

        $this->linksDown = GraphElements::linksAsList();

        $this->initNewQuery();
    }

    public function getExakatSince(): string {
        return $this->exakatSince;
    }

    public function getFilters(): array {
        return $this->filters;
    }

    public function init(int $analyzerId = -1): int {
        // always reload list of analysis from the database
        $query = <<<'GREMLIN'
g.V().hasLabel("Analysis").as("analyzer", "id").select("analyzer", "id").by("analyzer").by(id);
GREMLIN;
        $res = $this->gremlin->query($query);

        // Double is a safe guard, in case analysis were created twice
        $double = array();
        foreach ($res as list('analyzer' => $analyzer, 'id' => $id)) {
            if (isset(self::$rulesId[$analyzer]) && self::$rulesId[$analyzer] !== $id) {
                $double[] = $id;
            } else {
                self::$rulesId[$analyzer] = $id;
            }
        }

        if (!empty($double)) {
            $chunks = array_chunk($double, 200);
            foreach ($chunks as $list) {
                $list = makeList($list);
                $query = <<<GREMLIN
g.V({$list}).drop()
GREMLIN;
                $this->gremlin->query($query);
            }
        }

        if ($analyzerId === -1) {
            if (isset(self::$rulesId[$this->shortAnalyzer])) {
                // Removing all edges
                $this->analyzerId = self::$rulesId[$this->shortAnalyzer];
                $query = <<<GREMLIN
g.V({$this->analyzerId}).property("count", -2).outE("ANALYZED").drop()
GREMLIN;
                $this->gremlin->query($query);
                assert(!empty($this->analyzerId), self::class . ' was inited with Id ' . var_export($this->analyzerId, true) . ', already in. Can\'t save with that!');
            } else {
                $resId = $this->gremlin->getId();

                if ($resId === 'null') {
                    $resId = '';
                } else {
                    $resId = ".property(id, $resId)";
                }

                $query = <<<GREMLIN
g.addV()$resId
        .property(label, "Analysis")
        .property("analyzer", "{$this->analyzerQuoted}")
        .property("count", -1)
        .id()
GREMLIN;
                $res = $this->gremlin->query($query);
                $this->analyzerId = $res->toInt();

                self::$rulesId[$this->shortAnalyzer] = $this->analyzerId;
                assert(!empty($this->analyzerId), self::class . ' was inited with Id ' . var_export($this->analyzerId, true) . ', all new. Can\'t save with that!');
            }
        } else {
            $this->analyzerId = $analyzerId;
        }
        assert(!empty($this->analyzerId), self::class . ' was inited with Id ' . var_export($this->analyzerId, true) . '. Can\'t save with that!');

        return $this->analyzerId;
    }

    public function __destruct() {
    }

    public function setAnalyzer(string $analyzer): void {
        $className = $this->rulesets->getClass($analyzer);
        if ($className === '') {
            throw new NoSuchAnalyzer($analyzer, $this->rulesets);
        }
        $this->analyzer = $className;
        $this->analyzerQuoted = self::getName($this->analyzer);
        $this->shortAnalyzer  = str_replace('\\', '/', substr($this->analyzer, 16));
    }

    public function getInBaseName(): string {
        return $this->analyzerQuoted;
    }

    public function getShortAnalyzer(): string {
        return $this->shortAnalyzer;
    }

    public static function getName(string $classname): string {
        return str_replace( array('Exakat\\Analyzer\\', '\\'), array('', '/'), $classname);
    }

    public function getDump(): array {
        $this->atomIs('Analysis')
             ->is('analyzer', array($this->shortAnalyzer))
             ->savePropertyAs('analyzer', 'analyzer')
             ->outIs('ANALYZED')
             ->raw(<<<GREMLIN
 sideEffect{ line = it.get().value("line");
             fullcode = it.get().label() == 'Sequence' ? ' { /**/ } ' : it.get().value("fullcode");
             file="None"; 
             theFunction = ""; 
             theClass=""; 
             theNamespace=""; 
             }
.where( __.until( hasLabel("Project") ).repeat( 
    __.in($this->linksDown)
      .sideEffect{ if (theFunction == "" && it.get().label() in ["Function", "Closure", "Arrowfunction", "Magicmethod", "Method"]) { theFunction = it.get().value("fullcode")} }
      .sideEffect{ if (theClass == ""    && it.get().label() in ["Class", "Trait", "Interface", "Classanonymous"]                ) { theClass = it.get().value("fullcode")   } }
      .sideEffect{ if (it.get().label() == "File") { file = it.get().value("fullcode")} }
       ).fold()
)
.map{ ["fullcode":fullcode, 
       "file":file, 
       "line":line, 
       "namespace":theNamespace, 
       "class":theClass, 
       "function":theFunction,
       "analyzer":analyzer];
}
GREMLIN
             );

        return $this->rawQuery()->toArray();
    }

    public function getRulesets(): array {
        $analyzer = self::getName($this->analyzerQuoted);
        return $this->rulesets->getRulesetForAnalyzer($analyzer);
    }

    public function getPhpVersion(): string {
        return $this->phpVersion;
    }

    public function checkPhpConfiguration(Phpexec $php): bool {
        // this handles Any version of PHP
        if ($this->phpConfiguration[0] === 'Any') {
            return true;
        }

        foreach ($this->phpConfiguration as $ini => $value) {
            if ($php->getConfiguration($ini) != $value) {
                return false;
            }
        }

        return true;
    }

    public function checkPhpVersion(string $version): bool {
        return checkVersionRange($this->phpVersion, $version);
    }

    // @doc return the list of dependences that must be prepared before the execution of an analyzer
    // @doc by default, nothing.
    public function dependsOn(): array {
        return array();
    }

    public function query(string $queryString, array $arguments = array()): GraphResults {
        try {
            $result = $this->gremlin->query($queryString, $arguments);
        } catch (GremlinException $e) {
            display($e->getMessage() . $queryString);

            $result = new GraphResults();
        }

        return $result;
    }

    public function prepareSide(): Command {
        return $this->query->prepareSide();
    }

    public function run(): int {
        $this->analyze();
        $this->execQuery();

        return $this->rowCount;
    }

    public function getRowCount(): int {
        return $this->rowCount;
    }

    public function getProcessedCount(): int {
        return $this->processedCount;
    }

    public function getRawQueryCount(): int {
        return $this->rawQueryCount;
    }

    public function getQueryCount(): int {
        return $this->queryCount;
    }

    abstract public function analyze(): void ;

    public function printQuery() {
        $this->query->printQuery();
    }

    public function prepareQuery(): void {
        switch ($this->storageType) {
            case self::QUERY_MISSING:
                $this->storeMissing();
                break;

            case self::QUERY_NO_ANALYZED:
                $this->storeToGraph(self::PROPERTY_COMPLETE);
                break;

            case self::QUERY_DEFAULT:
            default:
                $this->storeToGraph(self::LINK_ANALYZED);
                break;
        }

        // initializing a new query
        $this->initNewQuery();
    }

    public function storeMissing() {
        foreach ($this->missingQueries as $m) {
            $query = <<<GREMLIN
g.addV().{$m->toAddV()}
        .addE('ANALYZED')
        .from(__.V({$this->analyzerId}))
GREMLIN;

            $this->gremlin->query($query, array());

            ++$this->processedCount;
            ++$this->rowCount;
        }
    }

    public function storeError(string $error = 'An error happened', int $error_type = self::UNKNOWN_COMPATIBILITY): void {
        $query = <<<GREMLIN
g.addV('Noresult').property('code',                              0)
                  .property('fullcode',                          '$error')
                  .property('virtual',                            true)
                  .property('line',                               $error_type)
                  .addE('ANALYZED')
                  .from(__.V($this->analyzerId));
GREMLIN;

        $this->gremlin->query($query);

        $this->datastore->addRow('analyzed', array($this->shortAnalyzer => -1 ) );
    }

    private function storeToGraph(bool $analyzed): void {
        if ($this->query->canSkip()) {
            return;
        }
        ++$this->queryId;

        $this->tailQuery($analyzed);

        $this->query->prepareQuery();
        $this->queries[] = $this->query;
    }

    public function rawQuery(): GraphResults {
        $this->query->prepareRawQuery();
        if ($this->query->canSkip()) {
            $result = new GraphResults();
        } else {
            $result = $this->gremlin->query($this->query->getQuery(), $this->query->getArguments());
        }

        $this->initNewQuery();

        return $result;
    }

    public function printRawQuery(): void {
        $this->query->prepareRawQuery();
        print $this->query->getQuery();

        print_r($this->query->getArguments());

        die(__METHOD__);
    }

    private function initNewQuery(): void {
        $this->query = new Multiple((count($this->queries) + 1),
            new Project('test'),
            $this->analyzerQuoted,
            $this->config->executable,
            $this->dependsOn()
        );
    }

    public function execQuery(): int {
        if (empty($this->queries)) {
            $this->gremlin->query("g.V({$this->analyzerId}).property(\"count\", __.V({$this->analyzerId}).out(\"ANALYZED\").count())", array());

            return 0;
        }

        // @todo add a test here ?
        foreach ($this->queries as $query) {
            if ($query->canSkip()) {
                continue;
            }

            $r = $this->gremlin->query($query->getQuery(), $query->getArguments());
            ++$this->queryCount;

            $this->processedCount += $r[0]['processed'];
            $this->rowCount       += $r[0]['total'];
        }

        // count the number of results
        $this->gremlin->query("g.V({$this->analyzerId}).property(\"count\", __.V({$this->analyzerId}).out(\"ANALYZED\").count())", array());

        // reset for the next
        $this->queries = array();

        // @todo multiple results ?
        // @todo store result in the object until reading.
        return $this->rowCount;
    }

    protected function loadIni(string $file, string $index = null): array|stdClass {
        assert(substr($file, -4) === '.ini', "Trying to loadIni on a non INI file : $file");
        $fullpath = "{$this->config->dir_root}/data/$file";

        if (isset(self::$iniCache[$fullpath]->$index)) {
            if ($index === null) {
                return self::$iniCache[$fullpath];
            } else {
                return self::$iniCache[$fullpath]->$index;
            }
        }

        if (file_exists($fullpath)) {
            $ini = (object) parse_ini_file($fullpath, \INI_PROCESS_SECTIONS);
        } elseif (($iniString = $this->config->ext->loadData("data/$file")) != '') {
            $ini = (object) parse_ini_string($iniString, \INI_PROCESS_SECTIONS);
        } elseif (($this->config->extension_dev !== null) &&
                  file_exists("{$this->config->extension_dev}/data/$file")) {
            $ini = (object) parse_ini_file("{$this->config->extension_dev}/data/$file", \INI_PROCESS_SECTIONS);
        } else {
            assert(false, "No INI for '$file'.");
            return array();
        }

        if (!isset(self::$iniCache[$fullpath])) {
            self::$iniCache[$fullpath] = $ini;
        }

        if ($index !== null && isset(self::$iniCache[$fullpath]->$index)) {
            return self::$iniCache[$fullpath]->$index;
        }

        return self::$iniCache[$fullpath];
    }

    protected function loadJson(string $file, string $property = null): array|stdClass {
        assert(substr($file, -5) === '.json', "Trying to loadIni on a non JSON file : $file");
        $fullpath = "{$this->config->dir_root}/data/$file";

        if (!isset(self::$jsonCache[$fullpath])) {
            if (file_exists($fullpath)) {
                $json = json_decode(file_get_contents($fullpath), \JSON_OBJECT);
            } elseif (!empty($jsonString = $this->config->ext->loadData("data/$file"))) {
                $json = json_decode($jsonString, \JSON_OBJECT);
            } elseif (($this->config->extension_dev !== null) && !empty($jsonString = $this->config->dev->loadData("data/$file"))) {
                $json = json_decode($jsonString, \JSON_OBJECT);
            } else {
                assert(false, "No JSON for '$file'.");
            }
            assert($json !== null, "JSON was not decoded for '$file'.");

            self::$jsonCache[$fullpath] = $json;
        }

        if ($property !== null && isset(self::$jsonCache[$fullpath]->$property)) {
            return self::$jsonCache[$fullpath]->$property;
        }

        return self::$jsonCache[$fullpath];
    }

    protected function loadPdff(string $file): PdffReader {
        assert(substr($file, -5) === '.pdff', "Trying to loadPDff on a non PDFF file : $file");
        $fullpath = "{$this->config->dir_root}/data/core/$file";

        if (!isset(self::$pdffCache[$fullpath])) {
            if (file_exists($fullpath)) {
                self::$pdffCache[$fullpath] = new PdffReader($fullpath);
            } else {
                $fullpath = "{$this->config->dir_root}/data/extensions/$file";

                if (file_exists($fullpath)) {
                    self::$pdffCache[$fullpath] = new PdffReader($fullpath);
                } else {
                    $fullpath = "{$this->config->dir_root}/data/vendors/$file";

                    if (file_exists($fullpath)) {
                        self::$pdffCache[$fullpath] = new PdffReader($fullpath);
                    } else {
                        $fullpath = "{$this->config->dir_root}/$file";

                        if (file_exists($fullpath)) {
                            self::$pdffCache[$fullpath] = new PdffReader($fullpath);
                        } else {
                            assert(file_exists($fullpath), "No such PDFF file as $file (core, ext, vendors, compat)\n");
                        }
                    }
                }
            }

            self::$pdffCache[$fullpath] = new PdffReader($fullpath);
        }

        return self::$pdffCache[$fullpath];
    }

    protected function load(string $file, string $property = null): array|stdClass {
        $inifile = "{$this->config->dir_root}/data/$file.ini";
        if (file_exists($inifile)) {
            $ini = $this->loadIni("$file.ini", $property);
        } else {
            $inifile = "{$this->config->dir_root}/data/$file.json";
            if (file_exists($inifile)) {
                $ini = $this->loadJson("$file.json", $property);
            } else {
                $ini = array();
            }
        }

        return $ini;
    }

    public function hasResults(): bool {
        return $this->rowCount > 0;
    }

    public static function makeBaseName(string $className): string {
        // No Exakat, no Analyzer, using / instead of \
        return $className;
    }

    protected function loadCode(string $path): string {
        if (file_exists($this->config->code_dir . $path)) {
            return (string) file_get_contents($this->config->code_dir . $path);
        } else {
            return '';
        }
    }

    public function __call(string $name, array $args): self {
        if ($this->query->canSkip()) {
            return $this;
        }

        $name = $name === 'as' ? '_as' : $name;

        try {
            $this->query->$name(...$args);
        } catch (UnknownDsl $e) {
            $this->query->StopQuery();
            $rank = $this->queryId + 1;
            throw new UnknownDsl("Found an unknown DSL '$name', in {$this->shortAnalyzer}#{$rank}. Aborting query\n");
        }

        return $this;
    }

    public function prepareForDump(array $dumpQueries): void {
        if (empty($dumpQueries)) {
            return;
        }
        $export = '<?php $queries = ' . var_export($dumpQueries, true) . '; ?>';
        $id = crc32($export);

        file_put_contents($this->config->tmp_dir . '/dump-' . $id . '.php', $export);
    }

    public function tailQuery(bool $analyzed): void {
        if ($analyzed === self::LINK_ANALYZED) {
            $analyzed = ".addE(\"ANALYZED\").from(__.V({$this->analyzerId}))";
        } else {
            $analyzed = '.property("complete", "' . $this->shortAnalyzer . '")';
        }

        $this->raw(<<<GREMLIN
dedup().sack{m,v -> ++m["total"]; m;}
        $analyzed
       .sideEffect( __.V({$this->analyzerId}).property("count", -1))
       .count()
       .sack()

// Query (#{$this->queryId}) for {$this->analyzer}
// php {$this->config->php} analyze -p {$this->config->project} -P {$this->analyzer} -v

GREMLIN
        );
    }

    protected function initStubs(): void {
        $this->stubs = new Stubs(dirname($this->config->ext_root) . '/stubs/',
            $this->config->stubs,
        );
        $this->phpCore = new Stubs($this->config->dir_root . '/data/core/',
            $this->config->php_core ?? array(),
        );
        $this->extensions = new Stubs($this->config->dir_root . '/data/extensions/',
            $this->config->php_extensions ?? array(),
        );

        $this->initedStubs = true;
    }

    protected function readStubs(string $method, array $args = array()): array {
        assert(method_exists(StubsInterface::class, $method), "No such method as '$method' to read stubs files\n");

        if (!$this->initedStubs) {
            $this->initStubs();
        }

        return array_merge($this->phpCore   ->$method(...$args),
            $this->extensions->$method(...$args),
            $this->stubs     ->$method(...$args),
        );
    }

    protected function reduceStubs(array $stubs, string $whichMethod, bool $keyOrValue = self::REDUCE_VALUE): array {
        if ($keyOrValue === self::REDUCE_VALUE) {
            assert(method_exists($this->called, $whichMethod), "No such method as $whichMethod. Should be \$getCalledClasses(), or else.");

            return array_intersect($stubs, $this->called->$whichMethod());
        }

        assert(false, 'No processing yet for REDUCE_KEY.');
    }
}
?>
