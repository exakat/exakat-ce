<?php declare(strict_types = 1);
/*
 * Copyright 2012-2022 Damien Seguy Ð Exakat SAS <contact(at)exakat.io>
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

namespace Exakat\Tasks;

use Exakat\Helpers\Timer;
use Exakat\GraphElements;
use Exakat\Graph\Graph;
use Exakat\Project;
use Exakat\Exceptions\InvalidPHPBinary;
use Exakat\Exceptions\LoadError;
use Exakat\Exceptions\MustBeAFile;
use Exakat\Exceptions\MustBeADir;
use Exakat\Exceptions\NoFileToProcess;
use Exakat\Exceptions\UnknownCase;
use Exakat\Tasks\LoadFinal\LoadFinal;
use Exakat\Tasks\Helpers\Fullnspaths;
use Exakat\Tasks\Helpers\AtomInterface;
use Exakat\Tasks\Helpers\AtomGroup;
use Exakat\Tasks\Helpers\Calls;
use Exakat\Tasks\Helpers\Encoding;
use Exakat\Tasks\Helpers\Context;
use Exakat\Tasks\Helpers\Intval;
use Exakat\Tasks\Helpers\Strval;
use Exakat\Tasks\Helpers\Boolval;
use Exakat\Tasks\Helpers\Nullval;
use Exakat\Tasks\Helpers\Constant;
use Exakat\Tasks\Helpers\Precedence;
use Exakat\Tasks\Helpers\IsPhp;
use Exakat\Tasks\Helpers\IsStub;
use Exakat\Tasks\Helpers\IsExt;
use Exakat\Tasks\Helpers\IsRead;
use Exakat\Tasks\Helpers\IsModified;
use Exakat\Tasks\Helpers\Php;
use Exakat\Tasks\Helpers\FunctionContext;
use Exakat\Tasks\Helpers\Sequences;
use Exakat\Tasks\Helpers\NestedCollector;
use Exakat\Tasks\Helpers\ContextVariables;
use Exakat\Tasks\Helpers\ContextMethods;
use Exakat\Tasks\Helpers\ContextProperties;
use Exakat\Tasks\Helpers\ClassTraitContext;
use Exakat\Tasks\Helpers\AnonymousNames;
use ProgressBar\Manager as ProgressBar;
use Exakat\Loader\Collector;
use Exakat\Log\Timing as TimingLog;
use Exakat\Fileset\{All, Filenames, FileExtensions, IgnoreDirs};
use Exakat\Analyzer\Analyzer;
use Sqlite3;
use Exakat\Log;
use Exakat\Tasks\Helpers\Atom;
use Exakat\Loader\Loader;
use Exakat\Phpexec;

use const STRICT_COMPARISON;

class Load extends Tasks {
    public const CONCURENCE = self::NONE;

    // @todo : Move this outside the code, to handle PHP versions
    // @todo hanlde PHP version of introduction for true, false, etc...
    private $SCALAR_TYPE = array('int',
                                 'bool',
                                 'void',
                                 'float',
                                 'string',
                                 'array',
                                 'callable',
                                 'iterable',
                                 'object',
                                 'false',
                                 'true',
                                 'null',
                                 'mixed',
                                 'never',
                                 );

    private $PHP_SUPERGLOBALS = array('$GLOBALS',
                                      '$_SERVER',
                                      '$_GET',
                                      '$_POST',
                                      '$_FILES',
                                      '$_REQUEST',
                                      '$_SESSION',
                                      '$_ENV',
                                      '$_COOKIE',
                                      '$php_errormsg',
                                      '$HTTP_RAW_POST_DATA',
                                      '$http_response_header',
                                      '$argc',
                                      '$argv',
                                      '$HTTP_POST_VARS',
                                      '$HTTP_GET_VARS',
                                      );

    private array $assignations = array();
    private array $phpKeywords  = array();

    private Phpexec $php;
    private ?Loader $loader;

    private bool $debug = false;

    private Precedence $precedence;
    private Php        $phptokens;

    private AtomGroup $atomGroup;
    private Calls $calls;
    private Sqlite3 $callsDatabase;

    private string $namespace = '\\';
    private Fullnspaths $uses;
    private string $filename;

    private array $links   = array();
    private array $relicat = array();
    private int    $minId  = \PHP_INT_MAX;

    private Sequences $sequences;
    private Atom      $sequence;

    private FunctionContext   $currentFunction;
    private ContextVariables  $currentVariables;
    private ClassTraitContext $currentClassTrait;
    private ContextProperties $currentProperties;
    private NestedCollector   $cases;
    private ContextMethods    $currentMethods;

    private bool $withWs = AtomInterface::WITHOUT_WS;

    private array $tokens   = array();
    private int   $id       = 0;
    private AtomInterface  $id0;

    private array $phpDocs    = array();
    private array $attributes = array();

//    private $sqliteLocation = '/tmp/load.sqlite';
    // for debug purpose
    private string $sqliteLocation = ':memory:';

    public const ALTERNATIVE_SYNTAX = true;
    public const NORMAL_SYNTAX      = false;

    public const FULLCODE_SEQUENCE = ' /**/ ';
    public const FULLCODE_BLOCK    = ' { /**/ } ';
    public const FULLCODE_VOID     = ' ';

    // No ALIASED : it is the actual value of the alias
    public const NOT_ALIASED       = '';

    public const NO_LINE           = -1;

    public const VARIADIC          = true;
    public const NOT_VARIADIC      = false;

    public const FLEXIBLE          = true;
    public const NOT_FLEXIBLE      = false;

    public const REFERENCE         = true;
    public const NOT_REFERENCE     = false;

    public const BRACKET          = true;
    public const NOT_BRACKET      = false;

    public const ENCLOSING        = true;
    public const NO_ENCLOSING     = false;

    public const ALTERNATIVE      = true;
    public const NOT_ALTERNATIVE  = false;

    public const TRAILING         = true;
    public const NOT_TRAILING     = false;

    public const ELLIPSIS         = true;
    public const NOT_ELLIPSIS     = false;

    public const CLOSING_TAG      = true;
    public const NO_CLOSING_TAG   = false;

    public const NOT_BINARY        = ''; // other values b, B (binary)

    public const ABSOLUTE     = true;
    public const NOT_ABSOLUTE = false;

    public const WITH_FULLNSPATH      = true;
    public const WITHOUT_FULLNSPATH   = false;

    public const CONSTANT_EXPRESSION       = true;
    public const NOT_CONSTANT_EXPRESSION   = false;

    public const FULLNSPATH_UNDEFINED = 'undefined';

    public const STANDALONE_BLOCK         = true;
    public const RELATED_BLOCK            = false;

    public const NO_NAMESPACE = '';

    public const CASE_SENSITIVE         = true;
    public const CASE_INSENSITIVE       = false;

    public const COMPILE_CHECK    = true;
    public const COMPILE_NO_CHECK = false;

    public const PROMOTED     = true;
    public const PROMOTED_NOT = false;

    public const READONLY      = true;
    public const NOSCREAM      = true;
    public const STATIC        = true;
    public const ABSTRACT      = true;
    public const FINAL         = true;

    private Context $contexts;

    private array $expressions         = array();
    private array $atoms               = array();
    private array $argumentsId         = array();

    private array $processing = array();

    private array $plugins = array();

    private array $stats = array(   'loc'			=> 0,
                                    'totalLoc'  	=> 0,
                                    'files'     	=> 0,
                                    'tokens'    	=> 0,
                          );

    private AtomInterface $atomVoid;
    private AnonymousNames $anonymousNames;
    protected TimingLog $logLoad;

    private array $END_OF_EXPRESSION = array(); // that should be a constant

    public function __construct(bool $subtask = self::IS_NOT_SUBTASK) {
        parent::__construct($subtask);

        $this->log = new Log('load',
            "{$this->config->projects_root}/projects/{$this->config->project}",
        );

        $this->atomGroup = new AtomGroup();

        $this->contexts  = new Context();
        $this->currentFunction = new FunctionContext();

        $this->php = exakat('php');
        if (!$this->php->isValid()) {
            throw new InvalidPHPBinary($this->php->getConfiguration('phpversion'));
        }
        $tokens = $this->php->getTokens();
        $this->phptokens  = Php::getInstance($tokens);

        $this->assignations = array($this->phptokens::T_EQUAL,
                                    $this->phptokens::T_PLUS_EQUAL,
                                    $this->phptokens::T_AND_EQUAL,
                                    $this->phptokens::T_CONCAT_EQUAL,
                                    $this->phptokens::T_DIV_EQUAL,
                                    $this->phptokens::T_MINUS_EQUAL,
                                    $this->phptokens::T_MOD_EQUAL,
                                    $this->phptokens::T_MUL_EQUAL,
                                    $this->phptokens::T_OR_EQUAL,
                                    $this->phptokens::T_POW_EQUAL,
                                    $this->phptokens::T_SL_EQUAL,
                                    $this->phptokens::T_SR_EQUAL,
                                    $this->phptokens::T_XOR_EQUAL,
                                    $this->phptokens::T_COALESCE_EQUAL,
                                   );

        // Init all plugins here
        $this->plugins[] = new Strval($this->config->constants ?? array(), $this->php);
        $this->plugins[] = new Boolval();
        $this->plugins[] = new Intval();
        $this->plugins[] = new Nullval();
        $this->plugins[] = new Constant();
        $this->plugins[] = new IsRead();
        $this->plugins[] = new IsModified();
        $this->plugins[] = new IsPhp();
        $this->plugins[] = new IsExt();
        $this->plugins[] = new IsStub();
        $this->plugins[] = new Encoding();

        $this->sequences = new Sequences();

        $this->currentVariables = new ContextVariables();

        $this->precedence = Precedence::getInstance($this->phptokens::class);

        $this->phpKeywords = array(
// '__halt_compiler', Fatal error is produced
$this->phptokens::T_ABSTRACT,
$this->phptokens::T_LOGICAL_AND,
$this->phptokens::T_LOGICAL_OR,
$this->phptokens::T_ARRAY,
$this->phptokens::T_AS,
$this->phptokens::T_BREAK,
$this->phptokens::T_CALLABLE,
$this->phptokens::T_CASE,
$this->phptokens::T_CATCH,
$this->phptokens::T_CLASS,
$this->phptokens::T_CLONE,
$this->phptokens::T_CONST,
$this->phptokens::T_CONTINUE,
$this->phptokens::T_DECLARE,
$this->phptokens::T_DEFAULT,
$this->phptokens::T_EXIT,
$this->phptokens::T_DO,
$this->phptokens::T_ECHO,
$this->phptokens::T_ELSE,
$this->phptokens::T_ELSEIF,
$this->phptokens::T_EMPTY,
$this->phptokens::T_ENDDECLARE,
$this->phptokens::T_ENDFOR,
$this->phptokens::T_ENDFOREACH,
$this->phptokens::T_ENDIF,
$this->phptokens::T_ENDSWITCH,
$this->phptokens::T_ENDWHILE,
$this->phptokens::T_EVAL,
$this->phptokens::T_EXIT,
$this->phptokens::T_EXTENDS,
$this->phptokens::T_FINAL,
$this->phptokens::T_FINALLY,
$this->phptokens::T_FN,
$this->phptokens::T_FOR,
$this->phptokens::T_FOREACH,
$this->phptokens::T_FUNCTION,
$this->phptokens::T_GLOBAL,
$this->phptokens::T_GOTO,
$this->phptokens::T_IF,
$this->phptokens::T_IMPLEMENTS,
$this->phptokens::T_INCLUDE,
$this->phptokens::T_INCLUDE_ONCE,
$this->phptokens::T_INSTEADOF,
$this->phptokens::T_INSTANCEOF,
$this->phptokens::T_INTERFACE,
$this->phptokens::T_ISSET,
$this->phptokens::T_LIST,
$this->phptokens::T_MATCH,
$this->phptokens::T_NAMESPACE,
$this->phptokens::T_NEW,
$this->phptokens::T_PRINT,
$this->phptokens::T_PRIVATE,
$this->phptokens::T_PUBLIC,
$this->phptokens::T_PROTECTED,
$this->phptokens::T_READONLY,
$this->phptokens::T_REQUIRE,
$this->phptokens::T_REQUIRE_ONCE,
$this->phptokens::T_RETURN,
$this->phptokens::T_STATIC,
$this->phptokens::T_SWITCH,
$this->phptokens::T_THROW,
$this->phptokens::T_TRAIT,
$this->phptokens::T_TRY,
$this->phptokens::T_UNSET,
$this->phptokens::T_USE,
$this->phptokens::T_VAR,
$this->phptokens::T_WHILE,
$this->phptokens::T_XOR,
$this->phptokens::T_YIELD,
);


        $this->processing = array(
            $this->phptokens::T_OPEN_TAG                 => 'processOpenTag',
            $this->phptokens::T_OPEN_TAG_WITH_ECHO       => 'processOpenTag',

            $this->phptokens::T_DOLLAR                   => 'processDollar',
            $this->phptokens::T_VARIABLE                 => 'processVariable',
            $this->phptokens::T_LNUMBER                  => 'processInteger',
            $this->phptokens::T_DNUMBER                  => 'processFloat',

            $this->phptokens::T_OPEN_PARENTHESIS         => 'processParenthesis',

            $this->phptokens::T_PLUS                     => 'processAddition',
            $this->phptokens::T_MINUS                    => 'processAddition',
            $this->phptokens::T_STAR                     => 'processMultiplication',
            $this->phptokens::T_SLASH                    => 'processMultiplication',
            $this->phptokens::T_PERCENTAGE               => 'processMultiplication',
            $this->phptokens::T_POW                      => 'processPower',
            $this->phptokens::T_INSTANCEOF               => 'processInstanceof',
            $this->phptokens::T_SL                       => 'processBitshift',
            $this->phptokens::T_SR                       => 'processBitshift',

            $this->phptokens::T_DOUBLE_COLON             => 'processDoubleColon',
            $this->phptokens::T_OBJECT_OPERATOR          => 'processObjectOperator',
            $this->phptokens::T_NULLSAFE_OBJECT_OPERATOR => 'processObjectOperator',
            $this->phptokens::T_NEW                      => 'processNew',

            $this->phptokens::T_DOT                      => 'processDot',
            $this->phptokens::T_OPEN_CURLY               => 'processBlock',

            $this->phptokens::T_IS_SMALLER_OR_EQUAL      => 'processComparison',
            $this->phptokens::T_IS_GREATER_OR_EQUAL      => 'processComparison',
            $this->phptokens::T_GREATER                  => 'processComparison',
            $this->phptokens::T_SMALLER                  => 'processComparison',

            $this->phptokens::T_IS_EQUAL                 => 'processComparison',
            $this->phptokens::T_IS_NOT_EQUAL             => 'processComparison',
            $this->phptokens::T_IS_IDENTICAL             => 'processComparison',
            $this->phptokens::T_IS_NOT_IDENTICAL         => 'processComparison',
            $this->phptokens::T_SPACESHIP                => 'processSpaceship',

            $this->phptokens::T_OPEN_BRACKET             => 'processArrayLiteral',
            $this->phptokens::T_ARRAY                    => 'processArrayLiteral',
            $this->phptokens::T_UNSET                    => 'processIsset',
            $this->phptokens::T_ISSET                    => 'processIsset',
            $this->phptokens::T_EMPTY                    => 'processIsset',
            $this->phptokens::T_LIST                     => 'processString', // Can't move to processEcho, because of omissions
            $this->phptokens::T_EVAL                     => 'processIsset',
            $this->phptokens::T_ECHO                     => 'processEcho',
            $this->phptokens::T_EXIT                     => 'processExit',
            $this->phptokens::T_DOUBLE_ARROW             => 'processKeyvalue',

            $this->phptokens::T_HALT_COMPILER            => 'processHalt',
            $this->phptokens::T_PRINT                    => 'processPrint',
            $this->phptokens::T_INCLUDE                  => 'processPrint',
            $this->phptokens::T_INCLUDE_ONCE             => 'processPrint',
            $this->phptokens::T_REQUIRE                  => 'processPrint',
            $this->phptokens::T_REQUIRE_ONCE             => 'processPrint',
            $this->phptokens::T_RETURN                   => 'processReturn',
            $this->phptokens::T_THROW                    => 'processThrow',
            $this->phptokens::T_YIELD                    => 'processYield',
            $this->phptokens::T_YIELD_FROM               => 'processYieldfrom',

            $this->phptokens::T_EQUAL                    => 'processAssignation',
            $this->phptokens::T_PLUS_EQUAL               => 'processAssignation',
            $this->phptokens::T_AND_EQUAL                => 'processAssignation',
            $this->phptokens::T_CONCAT_EQUAL             => 'processAssignation',
            $this->phptokens::T_DIV_EQUAL                => 'processAssignation',
            $this->phptokens::T_MINUS_EQUAL              => 'processAssignation',
            $this->phptokens::T_MOD_EQUAL                => 'processAssignation',
            $this->phptokens::T_MUL_EQUAL                => 'processAssignation',
            $this->phptokens::T_OR_EQUAL                 => 'processAssignation',
            $this->phptokens::T_POW_EQUAL                => 'processAssignation',
            $this->phptokens::T_SL_EQUAL                 => 'processAssignation',
            $this->phptokens::T_SR_EQUAL                 => 'processAssignation',
            $this->phptokens::T_XOR_EQUAL                => 'processAssignation',
            $this->phptokens::T_COALESCE_EQUAL           => 'processAssignation',

            $this->phptokens::T_CONTINUE                 => 'processBreak',
            $this->phptokens::T_BREAK                    => 'processBreak',

            $this->phptokens::T_LOGICAL_AND              => 'processLogical',
            $this->phptokens::T_LOGICAL_XOR              => 'processLogical',
            $this->phptokens::T_LOGICAL_OR               => 'processLogical',
            $this->phptokens::T_XOR                      => 'processBitoperation',
            $this->phptokens::T_OR                       => 'processBitoperation',
            $this->phptokens::T_AND                      => 'processAnd',
            $this->phptokens::T_AMPERSAND_NOT_FOLLOWED_BY_VAR_OR_VARARG => 'processAnd', // &$var
            $this->phptokens::T_AMPERSAND_FOLLOWED_BY_VAR_OR_VARARG => 'processAnd', // &$var

            $this->phptokens::T_BOOLEAN_AND              => 'processLogical',
            $this->phptokens::T_BOOLEAN_OR               => 'processLogical',

            $this->phptokens::T_QUESTION                 => 'processTernary',
            $this->phptokens::T_NS_SEPARATOR             => 'processNsname',
            $this->phptokens::T_COALESCE                 => 'processCoalesce',

            $this->phptokens::T_INLINE_HTML              => 'processInlinehtml',

            $this->phptokens::T_INC                      => 'processPrePlusplus',
            $this->phptokens::T_DEC                      => 'processPrePlusplus',

            $this->phptokens::T_WHILE                    => 'processWhile',
            $this->phptokens::T_DO                       => 'processDo',
            $this->phptokens::T_IF                       => 'processIfthen',
            $this->phptokens::T_FOREACH                  => 'processForeach',
            $this->phptokens::T_FOR                      => 'processFor',
            $this->phptokens::T_TRY                      => 'processTry',
            $this->phptokens::T_CONST                    => 'processConst',
            $this->phptokens::T_SWITCH                   => 'processSwitch',
            $this->phptokens::T_MATCH                    => 'processMatch',
# Those are now integrated inside Switch
#            $this->phptokens::T_DEFAULT                  => 'processDefault',
#            $this->phptokens::T_CASE                     => 'processCase',
            $this->phptokens::T_CASE                     => 'processEnumCase',
            $this->phptokens::T_DECLARE                  => 'processDeclare',

            $this->phptokens::T_AT                       => 'processNoscream',
            $this->phptokens::T_CLONE                    => 'processClone',
            $this->phptokens::T_GOTO                     => 'processGoto',

            $this->phptokens::T_STRING                   => 'processString',
            $this->phptokens::T_NAME_QUALIFIED           => 'processString',
            $this->phptokens::T_NAME_RELATIVE            => 'processString',
            $this->phptokens::T_NAME_FULLY_QUALIFIED     => 'processString',
            $this->phptokens::T_STRING_VARNAME           => 'processString', // ${x} x is here
            $this->phptokens::T_CONSTANT_ENCAPSED_STRING => 'processLiteral',
            $this->phptokens::T_ENCAPSED_AND_WHITESPACE  => 'processLiteral',
            $this->phptokens::T_NUM_STRING               => 'processLiteral',

            $this->phptokens::T_ARRAY_CAST               => 'processCast',
            $this->phptokens::T_BOOL_CAST                => 'processCast',
            $this->phptokens::T_DOUBLE_CAST              => 'processCast',
            $this->phptokens::T_INT_CAST                 => 'processCast',
            $this->phptokens::T_OBJECT_CAST              => 'processCast',
            $this->phptokens::T_STRING_CAST              => 'processCast',
            $this->phptokens::T_UNSET_CAST               => 'processCast',

            $this->phptokens::T_FILE                     => 'processMagicConstant',
            $this->phptokens::T_CLASS_C                  => 'processMagicConstant',
            $this->phptokens::T_FUNC_C                   => 'processMagicConstant',
            $this->phptokens::T_LINE                     => 'processMagicConstant',
            $this->phptokens::T_DIR                      => 'processMagicConstant',
            $this->phptokens::T_METHOD_C                 => 'processMagicConstant',
            $this->phptokens::T_NS_C                     => 'processMagicConstant',
            $this->phptokens::T_TRAIT_C                  => 'processMagicConstant',

            $this->phptokens::T_BANG                     => 'processNot',
            $this->phptokens::T_TILDE                    => 'processNot',
            $this->phptokens::T_ELLIPSIS                 => 'processEllipsis',

            $this->phptokens::T_SEMICOLON                => 'processSemicolon',
            $this->phptokens::T_CLOSE_TAG                => 'processClosingTag',

            $this->phptokens::T_FUNCTION                 => 'processFunction',
            $this->phptokens::T_FN                       => 'processFn',
            $this->phptokens::T_CLASS                    => 'processClass',
            $this->phptokens::T_TRAIT                    => 'processTrait',
            $this->phptokens::T_INTERFACE                => 'processInterface',
            $this->phptokens::T_NAMESPACE                => 'processNamespace',
            $this->phptokens::T_USE                      => 'processUse',
            $this->phptokens::T_ENUM                     => 'processEnum',

            $this->phptokens::T_ABSTRACT                 => 'processAbstract',
            $this->phptokens::T_READONLY                 => 'processReadonly',
            $this->phptokens::T_FINAL                    => 'processFinal',
            $this->phptokens::T_PRIVATE                  => 'processPPP',
            $this->phptokens::T_PROTECTED                => 'processPPP',
            $this->phptokens::T_PUBLIC                   => 'processPPP',
            $this->phptokens::T_VAR                      => 'processVar',

            $this->phptokens::T_QUOTE                    => 'processQuote',
            $this->phptokens::T_START_HEREDOC            => 'processQuote',
            $this->phptokens::T_BACKTICK                 => 'processQuote',
            $this->phptokens::T_DOLLAR_OPEN_CURLY_BRACES => 'processDollarCurly',
            $this->phptokens::T_STATIC                   => 'processStatic',
            $this->phptokens::T_GLOBAL                   => 'processGlobalVariable',

            $this->phptokens::T_DOC_COMMENT              => 'processPhpdoc',
            $this->phptokens::T_ATTRIBUTE                => 'processAttribute',
        );

        $this->END_OF_EXPRESSION = array($this->phptokens::T_COMMA,
                                         $this->phptokens::T_CLOSE_PARENTHESIS,
                                         $this->phptokens::T_CLOSE_CURLY,
                                         $this->phptokens::T_SEMICOLON,
                                         $this->phptokens::T_CLOSE_BRACKET,
                                         $this->phptokens::T_CLOSE_TAG,
                                         $this->phptokens::T_COLON,
                                         $this->phptokens::T_DOUBLE_ARROW,
                                         $this->phptokens::T_SEMICOLON,
                                         );

        $this->cases = new NestedCollector();
        $this->logLoad = new TimingLog('load.timing.csv');

        $this->anonymousNames    = new AnonymousNames();
        $this->currentClassTrait = new ClassTraitContext();
        $this->currentMethods    = new ContextMethods();
        $this->currentProperties = new ContextProperties();
    }

    public function __destruct() {
        $this->loader        = null;

        if (file_exists("{$this->config->projects_root}/projects/.exakat/calls.sqlite")) {
            unlink("{$this->config->projects_root}/projects/.exakat/calls.sqlite");
        }
    }

    public function runPlugins(AtomInterface $atom, array $linked = array()): void {
        foreach ($this->plugins as $plugin) {
            try {
                $plugin->run($atom, $linked);
            } catch (\Exception $t) {
                $this->logLoad->log('Runplugin error : ' . $t->getMessage() . ' ' . $t->getFile() . ' ' . $t->getLine());
                display('Runplugin error (' . get_class($plugin) . ', ' . $t->getLine() . ') : ' . $t->getMessage() . "\nFile: " . $this->filename . "\n");
            }
        }
    }

    public function setWs(): void {
        $this->withWs = AtomInterface::WITH_WS;
    }

    public function run(): void {
        $this->logTime('Start');
        // Clean tmp folder
        $files = glob("{$this->config->tmp_dir}/*.csv");

        foreach ($files as $file) {
            unlink($file);
        }

        $this->checkTokenLimit();

        // Reset Atom.
        $this->id0 = $this->addAtom('Project');
        $this->id0->code      = 'Whole';
        $this->id0->atom      = 'Project';
        $this->id0->code      = (string) $this->config->project;
        $this->id0->fullcode  = $this->config->project_name;
        $this->id0->token     = 'T_WHOLE';
        $this->atoms          = array();
        $this->minId          = \PHP_INT_MAX;

        $this->atomVoid = $this->addAtomVoid();

        // Cleaning the databases
        $this->datastore->cleanTable('tokenCounts');
        $this->datastore->cleanTable('dictionary');
        $this->logTime('Init');

        if ($filenames = $this->config->filename) {
            foreach ($filenames as $filename) {
                // Use this to make it phar compatible
                $filename = realpath($filename);
                if (!is_file($filename)) {
                    throw new MustBeAFile($filename);
                }

                try {
                    $this->callsDatabase = new Sqlite3($this->sqliteLocation);
                    $this->calls         = new Calls($this->callsDatabase);

                    $this->loader = Loader::getInstance($this->config->loader, $this->callsDatabase, $this->id0, $this->withWs);

                    ++$this->stats['files'];
                    if ($this->processFile($filename, '')) {
                        $this->loader->finalize($this->relicat);
                    } else {
                        print "Error while finalize the file.\n";
                    }
                } catch (NoFileToProcess $e) {
                    $this->datastore->ignoreFile($filename, $e->getMessage());
                    $this->log->log('Process File error : ' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
                    display('Process File error : ' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
                }
            }
        } elseif ($dirName = $this->config->dirname) {
            // Use this to make it phar compatible
            $dirName = realpath($dirName);
            if (!is_dir($dirName)) {
                throw new MustBeADir($dirName);
            }
            $this->processDir($dirName);
        } elseif (!($project = $this->config->project)->isDefault()) {
            $this->processProject($project);
        } else {
            throw new \Exception('Default processing should not happen.');
        }

        $this->logTime('Load in graph');

        $stats = array(array('key' => 'loc',         'value' => $this->stats['loc']),
                       array('key' => 'locTotal',    'value' => $this->stats['totalLoc']),
                       array('key' => 'files',       'value' => $this->stats['files']),
                       array('key' => 'tokens',      'value' => $this->stats['tokens']),
                       );
        $this->datastore->addRow('hash', $stats);

        if ($this->stats['loc'] !== 0) {
            $this->datastore->addRow('hash', array('status' => 'Load'));

            $loadFinal = new LoadFinal();
            $this->logTime('LoadFinal new');
            $loadFinal->run();
            $this->logTime('The End');
        }
    }

    private function processProject(Project $project): array {
        $files = $this->datastore->getCol('files', 'file');

        if (empty($files)) {
            throw new NoFileToProcess((string) $project, "No file to load.\n");
        }

        $stubs = $this->config->stubs;

        display('Sequential processing');

        $this->gremlin = Graph::getConnexion($this->config, $this->config->gremlin);

        $nbTokens = $this->runProjectCore($files);

        return array('files'  => count($files),
                     'tokens' => $nbTokens);
    }

    private function runProjectCore(array $files): int {
        $this->callsDatabase = new Sqlite3($this->sqliteLocation);

        $loader = Loader::getInstance($this->config->loader, $this->callsDatabase, $this->id0, $this->withWs);
        display('Loading with ' . $this->config->loader . "\n");

        $this->loader = $loader;
        $this->calls = new Calls($this->callsDatabase);

        $version = $this->php->getVersion();
        $this->datastore->addRow('hash', array('notCompilable' . $version[0] . $version[2] => 0));

        $nbTokens = 0;
        if ($this->config->verbose && !$this->config->quiet) {
            $progressBar = new Progressbar(0, count($files), $this->config->screen_cols);
        }

        foreach ($files as $file) {
            try {
                ++$this->stats['files'];
                $r = $this->processFile($file, $this->config->code_dir);
                $nbTokens += $r;
                if (isset($progressBar)) {
                    echo $progressBar->advance();
                }
            } catch (NoFileToProcess $e) {
                $this->datastore->ignoreFile($file, $e->getMessage());
                if (isset($progressBar)) {
                    echo $progressBar->advance();
                }
            }
            // Reduce memory as Atoms are not kept between files.
            gc_collect_cycles();
        }
        $this->loader->finalize($this->relicat);

        return $nbTokens;
    }

    private function processDir(string $dir): array {
        if (!file_exists($dir)) {
            return array('files'  => -1,
                         'tokens' => -1);
        }

        $this->callsDatabase = new Sqlite3($this->sqliteLocation);

        $set = new All($dir);
        $set->addFilter(new Filenames($this->config->dir_root));
        $set->addFilter(new FileExtensions($this->config->file_extensions));
        $set->addFilter(new IgnoreDirs($this->config->ignore_dirs, $this->config->include_dirs));

        $files = $set->getFiles();
        $ignoredFiles = $set->getIgnored();

        $clientClass = Loader::getInstance($this->config->loader, $this->callsDatabase, $this->id0, $this->withWs);
        $this->calls = new Calls($this->callsDatabase);
        $this->loader = new $clientClass($this->callsDatabase, $this->id0);

        $nbTokens = 0;
        foreach ($files as $file) {
            try {
                ++$this->stats['files'];
                $r = $this->processFile($file, $dir);
                $nbTokens += $r;
            } catch (NoFileToProcess $e) {
                $this->datastore->ignoreFile($file, $e->getMessage());
            }
        }
        $this->loader->finalize($this->relicat);

        $this->loader = new Collector($this->callsDatabase, $this->id0);
        $stats = $this->stats;
        foreach ($ignoredFiles as $file) {
            try {
                $this->processFile($file, $dir);
            } catch (NoFileToProcess $e) {
                $this->datastore->ignoreFile($file, $e->getMessage());
            }
        }
        $this->loader->finalize($this->relicat);
        $this->stats = $stats;

        return array('files'  => count($files),
                     'tokens' => $nbTokens);
    }

    private function reset(): void {
        $this->atoms   = array();
        $this->links   = array();
        $this->minId  = \PHP_INT_MAX;

        $this->contexts    = new Context();
        $this->expressions = array();
        $this->uses        = new Fullnspaths();

        $this->currentFunction         = new FunctionContext();
        $this->currentClassTrait       = new ClassTraitContext();
        $this->currentVariables        = new ContextVariables();

        $this->tokens                  = array();
        $this->phpDocs                 = array();
        $this->attributes              = array();
    }

    public function initDiff(): void {
        $clientClass = Loader::getInstance($this->config->loader, $this->callsDatabase, $this->id0, $this->withWs);

        $res = $this->gremlin->query('g.V().id().max()');
        $this->atomGroup = new AtomGroup($res->toInt() + 1);

        $this->id0 = $this->addAtom('Project');
        $this->id0->code      = 'Whole';
        $this->id0->atom      = 'Project';
        $this->id0->code      = (string) $this->config->project;
        $this->id0->fullcode  = $this->config->project_name;
        $this->id0->token     = 'T_WHOLE';
        $this->atoms          = array();
        $this->minId         = \PHP_INT_MAX;

        $this->loader = new $clientClass($this->callsDatabase, $this->id0);
    }

    public function finishDiff(): void {
        $this->loader->finalize(array());

        $loadFinal = new LoadFinal();
        $this->logTime('LoadFinal new');
        $loadFinal->run();
        $this->logTime('The End');

        $this->reset();
    }

    public function processDiffFile(string $filename, string $path): void {
        try {
            $this->processFile($filename, $path);
        } catch (NoFileToProcess $e ) {
            $this->datastore->ignoreFile($filename, $e->getMessage());
        }
    }

    private function processFile(string $filename, string $path, bool $compileCheck = self::COMPILE_CHECK): int {
        $timer = new Timer();
        $fullpath = $path . $filename;

        $this->filename = $filename;

        $log = array();

        if (is_link($fullpath)) {
            return 0;
        }
        if (!file_exists($fullpath)) {
            throw new NoFileToProcess($filename, 'unreachable file');
        }

        if (filesize($fullpath) === 0) {
            throw new NoFileToProcess($filename, 'empty file');
        }

        if ($compileCheck === self::COMPILE_CHECK && !$this->php->compile($fullpath)) {
            $error = $this->php->getError();
            $error['file'] = $filename;

            $version = $this->php->getVersion();
            $this->datastore->addRow('compilation' . $version[0] . $version[2], array($error));

            $count = $this->datastore->gethash('notCompilable' . $version[0] . $version[2]);
            $this->datastore->addRow('hash', array('notCompilable' . $version[0] . $version[2] => (int) $count + 1));

            return 0;
        }

        $tokens = $this->php->getTokenFromFile($fullpath);
        $log['token_initial'] = count($tokens);

        if (count($tokens) < 3) {
            throw new NoFileToProcess($filename, 'Only ' . count($tokens) . ' tokens');
        }

        $comments     = 0;
        $this->tokens = array();
        $total        = 0;
        $line         = 0;
        $ws           = '';
        foreach ($tokens as $position => $t) {
            if (is_array($t)) {
                switch ($t[0]) {
                    case $this->phptokens::T_WHITESPACE:
                        $line += substr_count($t[1], "\n");
                        $ws   .= $t[1];
                        break;

                    case $this->phptokens::T_COMMENT :
                        $c        = substr_count($t[1], "\n");
                        $line     += $c;
                        $comments += $c;
                        $ws       .= $t[1];
                        break;

                    case $this->phptokens::T_BAD_CHARACTER :
                        // Ignore all
                        break;

                    case $this->phptokens::T_DOC_COMMENT:
                        $t[] = $position;
                        $t[] = '';
                        $ws  = &$t[4];
                        $this->tokens[] = $t;
                        $comments += substr_count($t[1], "\n") + 1;
                        break;

                    default :
                        $t[] = $position;
                        $t[] = '';
                        $ws  = &$t[4];
                        $line = $t[2];
                        $this->tokens[] = $t;
                        ++$total;
                }
            } elseif (is_string($t)) {
                $token = array($this->phptokens::TOKENS[$t],
                               $t,
                               $line,
                               $position,
                               '',
                               );
                $ws             = &$token[4];
                $this->tokens[] = $token;
                ++$total;
            } else {
                throw new LoadError("$t is in a wrong token type : " . gettype($t));
            }
        }
        $this->stats['loc'] -= $comments;

        // Final token
        $this->tokens[] = array(0 => $this->phptokens::T_END,
                                1 => '/* END */',
                                2 => $line,
                                3 => 0,
                                4 => '');
        $this->stats['tokens'] += count($tokens);
        unset($tokens);

        $this->uses   = new Fullnspaths();

        $id1 = $this->addAtom('File');
        $id1->code     = $filename;
        $id1->fullcode = $filename;
        $id1->token    = 'T_FILENAME';

        $this->currentFunction->add($id1);

        try {
            $n = count($this->tokens) - 2;
            $this->id = 0; // set to 0 so as to calculate line in the next call.
            $this->startSequence(); // At least, one sequence available
            $this->sequence->ws->opening = '';
            $this->sequence->ws->closing = '';

            $this->id = -1;
            do {
                $theExpression = $this->processNext();
                $this->addToSequence($theExpression);
            } while ($this->id < $n);

            $sequence = $this->sequence;
            $sequence->ws->separators[] = '';

            $this->addLink($id1, $sequence, 'FILE');
        } catch (LoadError $e) {
            if ($compileCheck === self::COMPILE_CHECK) {
                $this->log->log('Can\'t process file \'' . $this->filename . '\' during load (\'' . $this->tokens[$this->id][0] . '\', line \'' . $this->tokens[$this->id][2] . '\'). Ignoring' . PHP_EOL . $e->getMessage() . PHP_EOL);
            }
            $this->reset();
            $this->calls->reset();
            throw new NoFileToProcess($filename, 'empty (1)', 0, $e);
        } finally {
            try {
                $this->checkTokens($filename);
                $this->calls->save();
            } catch (LoadError $e) {
                $this->log->log('Can\'t process file \'' . $this->filename . '\' during load (finally) (\'' . $this->tokens[$this->id][0] . '\', line \'' . $this->tokens[$this->id][2] . '\'). Ignoring' . PHP_EOL . $e->getMessage() . PHP_EOL);
                $this->reset();
                $this->calls->reset();
                throw new NoFileToProcess($filename, 'empty (2) : ' . $e->getMessage(), 0, $e);
            }

            $this->stats['totalLoc'] += $line;
            $this->stats['loc'] += $line;
        }

        $timer->end();
        $load = $timer->duration(Timer::MS);

        $atoms = count($this->atoms);
        $links = count($this->links);

        $timer = new Timer();
        $this->saveFiles();
        $timer->end();
        $save = $timer->duration(Timer::MS);

        $this->log->log("$filename\t$load\t$save\t$log[token_initial]\t$atoms\t$links");

        return $log['token_initial'];
    }

    private function processNext(): AtomInterface {
        $this->moveToNext();

        if ($this->nextIs(array($this->phptokens::T_END), 0)          ||
            !isset($this->processing[ $this->tokens[$this->id][0] ])) {
            display("Can't process file '$this->filename' during load ('{$this->tokens[$this->id][0]}', line {$this->tokens[$this->id][2]}), token {$this->tokens[$this->id][0]}) : {$this->tokens[$this->id][1]}). Ignoring\n");
            $this->log->log("Can't process file '$this->filename' during load ('{$this->tokens[$this->id][0]}', line {$this->tokens[$this->id][2]}). Ignoring\n");

            throw new LoadError('Processing error (processNext end)');
        }
        $method = $this->processing[ $this->tokens[$this->id][0] ];

        if ($this->debug === true) {
            print "  $method in" . PHP_EOL;
        }
        $atom = $this->$method();
        $this->checkPHPdoc();
        if ($this->debug === true) {
            print "  $method out " . PHP_EOL;
        }

        return $atom;
    }

    private function processExpression(array $finals): AtomInterface {
        do {
            $expression = $this->processNext();
            $this->checkPhpdoc();
        } while (!$this->nextIs($finals));

        $this->popExpression();

        return $expression;
    }

    private function processColon(): AtomInterface {
        $current = $this->id;
        --$this->id;
        $tag = $this->processNextAsIdentifier(self::WITHOUT_FULLNSPATH);
        $this->moveToNext();

        $label = $this->addAtom('Gotolabel', $this->id);
        $this->addLink($label, $tag, 'GOTOLABEL');
        $label->fullcode = $tag->fullcode . ' :';
        $label->ws->opening = $this->tokens[$current + 1][1] . $this->tokens[$current + 1][4];

        if ($this->currentClassTrait->getCurrent() === ClassTraitContext::NO_CLASS_TRAIT_CONTEXT) {
            $class = '\global';
        } else {
            $class = $this->currentClassTrait->getCurrent()->fullnspath;
        }

        $method = $this->currentFunction->currentFullnspath();

        $this->calls->addDefinition(Calls::GOTO, $class . '::' . $method . '..' . $tag->fullcode, $label);

        $this->addToSequence($label);
        $this->sequence->ws->separators[] = '';

        return $label;
    }

    //////////////////////////////////////////////////////
    /// processing complex tokens
    //////////////////////////////////////////////////////
    private function processQuote(): AtomInterface {
        $current = $this->id;
        $fullcode = array();
        $rank = -1;
        $elements = array();

        if ($this->tokens[$current][0] === $this->phptokens::T_QUOTE) {
            $string = $this->addAtom('String', $current);
            $finalToken = $this->phptokens::T_QUOTE;
            $closeQuote = '"';
            $type = $this->phptokens::T_QUOTE;

            $openQuote = $this->tokens[$this->id][1];
            if (mb_strtolower($this->tokens[$current][1][0]) === 'b') {
                $string->binaryString = $openQuote[0];
                $openQuote = '"';
            }
        } elseif ($this->tokens[$current][0] === $this->phptokens::T_BACKTICK) {
            $string = $this->addAtom('Shell', $current);
            $finalToken = $this->phptokens::T_BACKTICK;
            $openQuote = '`';
            $closeQuote = '`';
            $type = $this->phptokens::T_BACKTICK;
        } elseif ($this->tokens[$current][0] === $this->phptokens::T_START_HEREDOC) {
            $string = $this->addAtom('Heredoc', $current);
            $finalToken = $this->phptokens::T_END_HEREDOC;
            $openQuote = $this->tokens[$this->id][1];
            if (strtolower($openQuote[0]) === 'b') {
                $string->binaryString = $openQuote[0];
                $openQuote = substr($openQuote, 1);
            }

            $closeQuote = $openQuote[3] === "'" ? substr($openQuote, 4, -2) : substr($openQuote, 3);

            $type = $this->phptokens::T_START_HEREDOC;
        } else {
            throw new LoadError(__METHOD__ . ' : unsupported type of open quote : ' . $this->tokens[$current][0]);
        }

        // Set default, in case the whole loop is skipped
        $string->noDelimiter = '';
        $string->delimiter   = '';

        while ($this->tokens[$this->id + 1][0] !== $finalToken) {
            $currentVariable = $this->id + 1;
            if ($this->nextIs(array($this->phptokens::T_CURLY_OPEN))) {
                $open = $this->id + 1;
                $this->moveToNext(); // Skip {
                do {
                    $part = $this->processNext();
                } while (!$this->nextIs(array($this->phptokens::T_CLOSE_CURLY)));
                $this->moveToNext(); // Skip }

                $this->popExpression();

                $part->enclosing = self::ENCLOSING;
                $part->fullcode  = $this->tokens[$open][1] . $part->fullcode . '}';
                $part->token     = $this->getToken($this->tokens[$currentVariable][0]);
                $part->bracket   = self::BRACKET;

                $this->pushExpression($part);

                $elements[] = $part;
            } elseif ($this->nextIs(array($this->phptokens::T_DOLLAR_OPEN_CURLY_BRACES))) {
                $part = $this->processDollarCurly();

                $part->enclosing = self::ENCLOSING;
                $part->token     = $this->getToken($this->tokens[$currentVariable][0]);
                $this->pushExpression($part);

                $elements[] = $part;
            } elseif ($this->nextIs(array($this->phptokens::T_VARIABLE))) {
                if ($this->tokens[$this->id + 1][1] === '$this') {
                    $atom = 'This';
                } elseif (in_array($this->tokens[$this->id + 1][1], $this->PHP_SUPERGLOBALS, STRICT_COMPARISON)) {
                    $atom = 'Phpvariable';
                } elseif ($this->nextIs(array($this->phptokens::T_OBJECT_OPERATOR,
                                              $this->phptokens::T_NULLSAFE_OBJECT_OPERATOR), 2)) {
                    $atom = 'Variableobject';
                } elseif ($this->nextIs(array($this->phptokens::T_OPEN_BRACKET), 2)) {
                    $atom = 'Variablearray';
                } else {
                    $atom = 'Variable';
                }
                $this->moveToNext();
                $variable = $this->processSingle($atom);
                $variable->ws->opening = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];

                if ($atom === 'This' && ($class = $this->currentClassTrait->getCurrentForThis())) {
                    $variable->fullnspath = $class->fullnspath;
                    $this->calls->addCall(Calls::A_CLASS, $class->fullnspath, $variable);
                }

                if ($this->nextIs(array($this->phptokens::T_OBJECT_OPERATOR,
                                        $this->phptokens::T_NULLSAFE_OBJECT_OPERATOR,
                                        ))) {
                    $this->moveToNext();
                    $property = $this->addAtom('Member', $this->id);
                    $property->ws->operator = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];

                    $propertyName = $this->processNextAsIdentifier();

                    $property->fullcode  = "{$variable->fullcode}->{$propertyName->fullcode}";
                    $property->enclosing = self::NO_ENCLOSING;

                    $this->addLink($property, $variable, 'OBJECT');
                    $this->addLink($property, $propertyName, 'MEMBER');
                    $this->runPlugins($property, array('OBJECT' => $variable,
                                                       'MEMBER' => $propertyName,
                                                       ));

                    if ($variable->atom === 'This' &&
                        $propertyName->token   === 'T_STRING') {
                        $this->calls->addCall(Calls::PROPERTY, "{$variable->fullnspath}::{$propertyName->code}", $property);
                        $this->currentProperties->addCall($propertyName->code, $property);
                    }

                    $this->pushExpression($property);

                    $elements[] = $property;
                } elseif ($this->nextIs(array($this->phptokens::T_OPEN_BRACKET))) {
                    $this->moveToNext(); // Skip $a
                    $array = $this->addAtom('Array', $this->id);
                    $array->ws->opening = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];
                    $this->moveToNext(); // Skip [

                    if ($this->nextIs(array($this->phptokens::T_NUM_STRING), 0)) {
                        $index = $this->processSingle('Integer');
                        $this->runPlugins($index);
                    } elseif ($this->nextIs(array($this->phptokens::T_MINUS), 0)) {
                        $this->moveToNext();
                        if ($this->tokens[$this->id][1][0] === '0') {
                            $index            = $this->processSingle('String');
                            $index->code      = "-{$index->code}";
                            $index->fullcode  = "-{$index->fullcode}";
                        } else {
                            $index            = $this->processSingle('Integer');
                            $index->code      = (string) (-1 * $index->code);
                            $index->fullcode  = (string) (-1 * $index->fullcode);
                        }
                    } elseif ($this->nextIs(array($this->phptokens::T_STRING), 0)) {
                        $index = $this->processSingle('String');
                        $index->ws->opening = '';
                        $index->ws->closing = '';
                    } elseif ($this->nextIs(array($this->phptokens::T_VARIABLE), 0)) {
                        $index = $this->processVariable();
                        $this->popExpression();
                    } else {
                        throw new UnknownCase('Couldn\'t read that token inside quotes : ' . $this->tokens[$this->id][0]);
                    }
                    $this->moveToNext(); // Skip ]
                    $array->ws->closing = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];

                    $array->fullcode  = "{$variable->fullcode}[{$index->fullcode}]";
                    $array->enclosing = self::NO_ENCLOSING;

                    $this->addLink($array, $variable, 'VARIABLE');
                    $this->addLink($array, $index, 'INDEX');
                    $this->runPlugins($array, array('VARIABLE' => $variable,
                                                    'INDEX'    => $index,
                                                     ));

                    $this->pushExpression($array);
                    $elements[] = $array;
                } else {
                    $this->pushExpression($variable);
                }
            } else {
                $this->processNext();
            }

            $part = $this->popExpression();
            if ($part->atom === 'String') {
                $part->noDelimiter = $part->code;
                $part->delimiter   = '';
                $part->ws->opening = '';
            } else {
                $part->noDelimiter = '';
                $part->delimiter   = '';
            }
            $part->rank = ++$rank;
            $fullcode[] = $part->fullcode;
            $elements[] = $part;

            $this->addLink($string, $part, 'CONCAT');
        }

        if ($type === $this->phptokens::T_START_HEREDOC) {
            /*
            This might be a PHP 7 artefact. Keep it here until checked.
            if (!empty($elements)) {
                // This is the last part
                $part = array_pop($elements);
                $part->noDelimiter = rtrim($part->noDelimiter, "\n");
                $part->code        = rtrim($part->code,        "\n");
                $part->fullcode    = rtrim($part->fullcode,    "\n");
                $elements[]        = $part;
            }*/
            // Get the closing quote for flexibility
            $closeQuote = $this->tokens[$this->id + 1][1];
            if (trim($closeQuote) !== $closeQuote) {
                $string->flexible = self::FLEXIBLE;
            }
        }

        $this->moveToNext();
        $string->fullcode    = $string->binaryString . $openQuote . implode('', $fullcode) . $closeQuote;
        $string->noDelimiter = implode('', $fullcode);
        $string->delimiter   = $openQuote;
        $string->count       = $rank + 1;

        $string->ws->opening = $openQuote;
        $string->ws->closing = $closeQuote . $this->tokens[$this->id][4];

        if ($type === $this->phptokens::T_START_HEREDOC) {
            $string->delimiter = trim($closeQuote);
            $string->heredoc   = $openQuote[3] !== "'";
        }

        $this->runPlugins($string, $elements);
        $this->pushExpression($string);

        if ($type === $this->phptokens::T_QUOTE) {
            $string = $this->processFCOA($string);
        }

        $this->checkExpression();

        return $string;
    }

    private function processDollarCurly(): AtomInterface {
        $current = $this->id;
        $atom = $this->nextIs(array($this->phptokens::T_GLOBAL), -1) ? 'Globaldefinition' : 'Variable';
        $variable = $this->addAtom($atom, $current);

        $this->moveToNext(); // Skip ${
        do {
            $name = $this->processNext();
        } while (!$this->nextIs(array($this->phptokens::T_CLOSE_CURLY)));
        $this->moveToNext(); // Skip }

        $this->popExpression();
        $this->addLink($variable, $name, 'NAME');

        $variable->fullcode  = '${' . $name->fullcode . '}';
        $variable->enclosing = self::ENCLOSING;
        $variable->ws->opening = $this->tokens[$current + 1][1] . $this->tokens[$current + 1][4];
        $variable->ws->closing = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];

        $this->runPlugins($variable, array('NAME' => $name));

        $this->checkExpression();

        return $variable;
    }

    private function processTry(): AtomInterface {
        $current = $this->id;
        $try = $this->addAtom('Try', $current);
        $try->ws->opening = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];

        $block = $this->processFollowingBlock(array($this->phptokens::T_CLOSE_CURLY));
        $try->ws->closing = '';

        $this->addLink($try, $block, 'BLOCK');
        $extras = array('BLOCK' => $block);

        $rank = 0;
        $fullcode = array();
        $this->checkPhpdoc();
        while ($this->nextIs(array($this->phptokens::T_CATCH))) {
            $catchId = $this->id + 1;
            $this->moveToNext(); // Skip catch
            $this->moveToNext(); // Skip (

            $catch = $this->addAtom('Catch', $catchId);
            $catchFullcode = array();
            $extrasCatch = array();
            $rankCatch = -1;
            $catch->ws->opening = $this->tokens[$this->id - 1][1] . $this->tokens[$this->id - 1][4] .
                                  $this->tokens[$this->id][1] . $this->tokens[$this->id][4];

            // processing the typehint (including multiple types)
            $rankClass = -1;
            while (!$this->nextIs(array($this->phptokens::T_VARIABLE,
                                        $this->phptokens::T_CLOSE_PARENTHESIS))) {
                $class = $this->processOneNsname();
                $this->addLink($catch, $class, 'CLASS');
                $class->rank = ++$rankClass;

                $this->calls->addCall(Calls::A_CLASS, $class->fullnspath, $class);
                $catchFullcode[] = $class->fullcode;
                $extrasCatch['CLASS' . $rankCatch] = $class;

                if ($this->nextIs(array($this->phptokens::T_OR))) {
                    $catch->ws->separators[] = $this->tokens[$this->id + 1][1] . $this->tokens[$this->id + 1][4];
                    $this->moveToNext(); // Skip |
                } else {
                    $catch->ws->separators[] = '';
                }
            }
            $catch->rank = ++$rankCatch;
            $catch->count = $rankClass + 1;
            $catchFullcode = implode(' | ', $catchFullcode);

            // Process variable
            if ($this->nextIs(array($this->phptokens::T_VARIABLE))) {
                $variable = $this->processNext();

                $this->popExpression();
                $this->addLink($catch, $variable, 'VARIABLE');
                $extrasCatch['VARIABLE'] = $variable;

                $variableFullcode = $variable->fullcode;
            } else {
                $variableFullcode = '';
            }

            // Skip )
            $this->moveToNext();
            $catch->ws->toblock = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];

            // Skip }
            $blockCatch = $this->processFollowingBlock(array($this->phptokens::T_CLOSE_CURLY));
            $catch->ws->closing = '';

            $this->addLink($catch, $blockCatch, 'BLOCK');
            $extrasCatch['BLOCK'] = $blockCatch;

            $catch->fullcode = $this->tokens[$catchId][1] . ' (' . $catchFullcode . ' ' . $variableFullcode . ')' . static::FULLCODE_BLOCK;
            $catch->rank     = ++$rank;

            $this->addLink($try, $catch, 'CATCH');
            $fullcode[] = $catch->fullcode;

            $extras['CATCH' . $rank] = $catch;
            $this->runPlugins($catch, $extrasCatch);
            $this->checkPhpdoc();
        }

        $this->checkPhpdoc();
        if ($this->nextIs(array($this->phptokens::T_FINALLY))) {
            $finally = $this->processFinally();

            $this->addLink($try, $finally, 'FINALLY');
        }

        $try->fullcode = $this->tokens[$current][1] . static::FULLCODE_BLOCK . implode('', $fullcode) . ( isset($finally) ? $finally->fullcode : '');
        $try->count    = $rank;

        $this->addToSequence($try);
        $this->sequence->ws->separators[] = '';

        $this->runPlugins($try, $extras);
        return $try;
    }

    private function processFinally(): AtomInterface {
        $finallyId = $this->id + 1;
        $finally = $this->addAtom('Finally', $finallyId);
        $finally->ws->opening = $this->tokens[$this->id + 1][1] . $this->tokens[$this->id + 1][4];

        $this->moveToNext();
        $finallyBlock = $this->processFollowingBlock(array($this->phptokens::T_CLOSE_CURLY));
        $this->addLink($finally, $finallyBlock, 'BLOCK');

        $finally->ws->closing = '';
        $finally->fullcode = $this->tokens[$finallyId][1] . static::FULLCODE_BLOCK;

        $this->runPlugins($finally, array('BLOCK' => $finallyBlock));

        return $finally;
    }

    private function processFn(): AtomInterface {
        $current = $this->id;

        if ($this->nextIs(array($this->phptokens::T_AND,
                                $this->phptokens::T_AMPERSAND_NOT_FOLLOWED_BY_VAR_OR_VARARG))) {
            $this->moveToNext();
            $reference = self::REFERENCE;
        } else {
            $reference = self::NOT_REFERENCE;
        }

        $this->moveToNext();
        $atom     = 'Arrowfunction';

        // Keep a copy of the current variables, to remove the arguments when we are done
        $previousContextVariables = clone $this->currentVariables;

        $fn              = $this->processParameters($atom);
        $fn->reference   = $reference;
        $fn->code        = $this->tokens[$current][1];
        $fn->ws->opening = $this->tokens[$current][1] . $this->tokens[$current][4] .
                           $this->tokens[$current + 1][1] . $this->tokens[$current + 1][4];

        // Process return type
        $fn->ws->endargs = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];
        $returnTypeFullcode = $this->processTypehint($fn);

        $fn->ws->toblock = $this->tokens[$this->id + 1][1] . $this->tokens[$this->id + 1][4];
        $this->moveToNext($this->phptokens::T_DOUBLE_ARROW); // skip =>

        $this->contexts->nestContext(Context::CONTEXT_FUNCTION);
        $this->contexts->toggleContext(Context::CONTEXT_FUNCTION);

        // arrowfunction may be static
        if ($this->tokens[$current - 1][0] === $this->phptokens::T_STATIC) {
            $this->currentClassTrait->toggleThisContext();
        }

        $block = $this->processExpression($this->END_OF_EXPRESSION);
        $this->currentFunction->remove();

        // arrowfunction may be static
        if ($this->tokens[$current - 1][0] === $this->phptokens::T_STATIC) {
            $this->currentClassTrait->toggleThisContext();
        }

        $this->contexts->exitContext(Context::CONTEXT_FUNCTION);

        $this->addLink($fn, $block, 'BLOCK');
        $this->addLink($fn, $block, 'RETURNED');
        $this->addLink($fn, $block, 'RETURN');

        $fn->token    = $this->getToken($this->tokens[$current][0]);
        $fn->fullcode = $this->tokens[$current][1] . ' ' .
                        ($fn->reference ? '&' : '') .
                        '(' . $fn->fullcode . ')' .
                        $returnTypeFullcode .
                        ' => ' . $block->fullcode;
        $fn->fullnspath = $this->anonymousNames->getName(AnonymousNames::A_ARROW_FUNCTION);

        $this->currentVariables = $previousContextVariables;

        $this->pushExpression($fn);
        $this->checkExpression();

        return $fn;
    }

    private function processFunction(): AtomInterface {
        $current = $this->id;

        if ( $this->contexts->isContext(Context::CONTEXT_CLASS) &&
             !$this->contexts->isContext(Context::CONTEXT_FUNCTION)) {
            if (in_array(mb_strtolower($this->tokens[$this->id + 1][1]),
                array('__construct',
                      '__destruct',
                      '__call',
                      '__callstatic',
                      '__get',
                      '__set',
                      '__isset',
                      '__unset',
                      '__sleep',
                      '__wakeup',
                      '__tostring',
                      '__invoke',
                      '__set_state',
                      '__clone',
                      '__debuginfo',
                      '__serialize',
                      '__unserialize',
                      ),
                STRICT_COMPARISON)) {
                $atom = 'Magicmethod';
            } else {
                $atom = 'Method';
            }
        } elseif ($this->nextIs(array($this->phptokens::T_OPEN_PARENTHESIS))) {
            $atom = 'Closure';
        } elseif ($this->nextIs(array($this->phptokens::T_AND,
                                      $this->phptokens::T_AMPERSAND_NOT_FOLLOWED_BY_VAR_OR_VARARG)) &&
                  $this->nextIs(array($this->phptokens::T_OPEN_PARENTHESIS), 2)) {
            $atom = 'Closure';
        } else {
            $atom = 'Function';
        }

        $this->contexts->nestContext(Context::CONTEXT_CLASS);
        $this->contexts->nestContext(Context::CONTEXT_FUNCTION);
        $this->contexts->toggleContext(Context::CONTEXT_FUNCTION);

        $previousContextVariables = $this->currentVariables;
        $this->currentVariables = new ContextVariables();

        if ($this->nextIs(array($this->phptokens::T_AND,
                                $this->phptokens::T_AMPERSAND_FOLLOWED_BY_VAR_OR_VARARG,
                                $this->phptokens::T_AMPERSAND_NOT_FOLLOWED_BY_VAR_OR_VARARG,
                                ))) {
            $this->moveToNext();
            $referenceWS = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];
            $reference = self::REFERENCE;
        } else {
            $referenceWS = '';
            $reference = self::NOT_REFERENCE;
        }

        if ($atom !== 'Closure') {
            $name = $this->processNextAsIdentifier(self::WITHOUT_FULLNSPATH);
        }
        $this->moveToNext();
        $toargsWS = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];

        // Process arguments
        $function              = $this->processParameters($atom);
        $function->ws->opening = $this->tokens[$current][1] . $this->tokens[$current][4] .
                                 $this->tokens[$current + 1][1] . $this->tokens[$current + 1][4];
        $function->ws->toargs  = $toargsWS;

        $function->code = $function->atom === 'Closure' ? 'function' : $name->fullcode;
        $function->ws->opening = $this->tokens[$current][1] . $this->tokens[$current][4];

        if ($function->atom === 'Function') {
            $this->getFullnspath($name, 'function', $function);
            $this->calls->addDefinition(Calls::FUNCTION, $function->fullnspath, $function);

            $this->addLink($function, $name, 'NAME');
        } elseif ($function->atom === 'Closure') {
            $function->fullnspath = $this->anonymousNames->getName(AnonymousNames::A_FUNCTION);

            // closure may be static
            if ($this->tokens[$current - 1][0] === $this->phptokens::T_STATIC) {
                $this->currentClassTrait->toggleThisContext();
            }
        } elseif ($function->isA(array('Method', 'Magicmethod'))) {
            $function->fullnspath = $this->currentClassTrait->getCurrent()->fullnspath . '::' . mb_strtolower($name->code);

            if (empty($function->visibility)) {
                $function->visibility = 'none';
            }

            $this->addLink($function, $name, 'NAME');
        } else {
            throw new LoadError(__METHOD__ . ' : wrong type of function ' . $function->atom);
        }

        $function->token   = $this->getToken($this->tokens[$current][0]);

        $argumentsFullcode = $function->fullcode;
        $function->reference = $reference;

        $function->ws->reference = $referenceWS;
        $function->ws->endargs = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];

        // Process use
        $useFullcode = array();
        if ($this->nextIs(array($this->phptokens::T_USE))) {
            $function->ws->touse = $this->tokens[$this->id + 1][1] . $this->tokens[$this->id + 1][4] .
                                   $this->tokens[$this->id + 2][1] . $this->tokens[$this->id + 2][4];
            $this->moveToNext(); // Skip use
            $this->moveToNext(); // Skip (

            $rank = 0;
            $uses = array();
            do {
                $this->checkPhpDoc();
                $this->moveToNext(); // Skip ( or ,

                if ($this->nextIs(array($this->phptokens::T_CLOSE_PARENTHESIS), 0)) {
                    $useFullcode[] = '';

                    continue;
                }

                if ($this->nextIs(array($this->phptokens::T_AND,
                                        $this->phptokens::T_AMPERSAND_FOLLOWED_BY_VAR_OR_VARARG,
                                        $this->phptokens::T_AMPERSAND_NOT_FOLLOWED_BY_VAR_OR_VARARG,
                                        ), 0)) {
                    $this->moveToNext();
                    $arg = $this->processSingle('Parameter');
                    $arg->ws->reference = $this->tokens[$this->id - 1][1] . $this->tokens[$this->id - 1][4];
                    $arg->reference = self::REFERENCE;
                    $arg->fullcode = "&$arg->fullcode";
                } else {
                    $arg = $this->processSingle('Parameter');
                }
                $arg->typehint = 'one'; // in fact, none
                $this->moveToNext();
                $uses[] = $arg;

                $useFullcode[] = $arg->fullcode;
                $arg->rank = ++$rank;

                $this->addLink($function, $arg, 'USE');
                $this->currentVariables->set($arg->code, $arg);
                if ($previousContextVariables->exists($arg->code)) {
                    $this->addLink($previousContextVariables->get($arg->code), $arg, 'DEFINITION');
                }

                if ($this->nextIs(array($this->phptokens::T_COMMA), 0)) {
                    $function->ws->touseseparators[] = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];
                } else {
                    $function->ws->touseseparators[] = $this->tokens[$this->id - 1][4] .
                                                       $this->tokens[$this->id][1] . $this->tokens[$this->id][4];
                }
            } while ($this->nextIs(array($this->phptokens::T_COMMA), 0));
            $this->runPlugins($function, array('USE' => $uses));
        }

        // Process return type
        $returnTypes = $this->processTypehint($function);
        $function->ws->toblock = $this->tokens[$this->id + 1][1] . $this->tokens[$this->id + 1][4];

        // Process block
        if ($this->nextIs(array($this->phptokens::T_SEMICOLON))) {
            $block = $this->addAtomVoid();
            $block->ws->opening = ';' . $this->tokens[$this->id + 1][4];
            $this->addLink($function, $block, 'BLOCK');
            $this->moveToNext(); // skip the next ;
            $blockFullcode = ' ;';
            $this->runPlugins($block);
        } else {
            $block = $this->processFollowingBlock(array($this->phptokens::T_CLOSE_CURLY));
            $this->addLink($function, $block, 'BLOCK');
            $blockFullcode = self::FULLCODE_BLOCK;
        }

        $function->fullcode   = $this->tokens[$current][1] . ' ' . ($function->reference ? '&' : '') .
                                ($function->atom === 'Closure' ? '' : $name->fullcode) . '(' . $argumentsFullcode . ')' .
                                (empty($useFullcode) ? '' : ' use (' . implode(', ', $useFullcode) . ')') . // No space before use
                                $returnTypes .
                                $blockFullcode;

        if ($function->atom === 'Closure' &&
           $this->tokens[$current - 1][0] === $this->phptokens::T_STATIC) {
            $this->currentClassTrait->toggleThisContext();
        }

        $this->contexts->exitContext(Context::CONTEXT_CLASS);
        $this->contexts->exitContext(Context::CONTEXT_FUNCTION);
        $this->runPlugins($function, array('BLOCK' => $block));

        $this->currentFunction->remove();
        $this->currentVariables = $previousContextVariables;

        $this->pushExpression($function);

        if ($function->atom === 'Function') {
            $this->processSemicolon();
        } elseif ($function->atom === 'Closure' &&
                  $this->tokens[$current  - 1][0] !== $this->phptokens::T_EQUAL          &&
                  $this->nextIs(array($this->phptokens::T_CLOSE_TAG))) {
            $this->processSemicolon();
        } elseif ($function->atom === 'Method' && !empty(preg_match('/^static$/i', $function->fullcode))) {
            $this->calls->addDefinition(Calls::STATICMETHOD, $function->fullnspath, $function);
            $this->currentMethods->addMethod(mb_strtolower($function->code), $function);

            $this->sequence->ws->separators[] = '';
        } elseif ($function->atom === 'Method') {
            $this->calls->addDefinition(Calls::METHOD, $function->fullnspath, $function);
            $this->currentMethods->addMethod(mb_strtolower($function->code), $function);
            // double call for internal reference
            $this->calls->addDefinition(Calls::STATICMETHOD, $function->fullnspath, $function);
        } elseif ($function->atom === 'Magicmethod') {
            if (mb_strtolower($this->tokens[$current + 1][1]) === '__construct' &&
                $this->currentClassTrait->getCurrent()->atom === 'Classanonymous') {
                $this->addLink($function, $this->currentClassTrait->getCurrent(), 'DEFINITION');
            }

            $this->calls->addDefinition(Calls::METHOD, $function->fullnspath, $function);

            $this->currentMethods->addMethod(mb_strtolower($function->code), $function);
        }

        $function->ws->closing = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];

        return $function;
    }

    private function processOneNsname(bool $getFullnspath = self::WITH_FULLNSPATH): AtomInterface {
        $this->moveToNext();
        $nsname = $this->makeNsname();

        if ($getFullnspath === self::WITH_FULLNSPATH) {
            $this->getFullnspath($nsname, 'class', $nsname);
            $this->calls->addCall(Calls::A_CLASS, $nsname->fullnspath, $nsname);
        }

        return $nsname;
    }

    private function processTrait(): AtomInterface {
        $current = $this->id;
        $trait = $this->addAtom('Trait', $current);
        $this->currentClassTrait->pushContext($trait);
        $this->makeAttributes($trait);
        $this->makePhpdoc($trait);
        $trait->ws->opening = $this->tokens[$current][1] . $this->tokens[$current][4];

        $this->contexts->nestContext(Context::CONTEXT_CLASS);
        $this->contexts->toggleContext(Context::CONTEXT_CLASS);

        $name = $this->processNextAsIdentifier(self::WITHOUT_FULLNSPATH);
        $this->addLink($trait, $name, 'NAME');

        $this->getFullnspath($name, 'class', $trait);
        $this->calls->addDefinition(Calls::A_CLASS, $trait->fullnspath, $trait);
        $trait->ws->toblock = $this->tokens[$this->id + 1][1] . $this->tokens[$this->id + 1][4];

        // Process block
        $this->makeCitBody($trait);
        $this->runPlugins($trait, array());

        $trait->fullcode   = $this->tokens[$current][1] . ' ' . $name->fullcode . static::FULLCODE_BLOCK;
        $trait->ws->closing = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];

        $this->addToSequence($trait);
        $this->sequence->ws->separators[] = '';

        $this->contexts->exitContext(Context::CONTEXT_CLASS);

        $this->currentClassTrait->popContext();

        return $trait;
    }

    private function processInterface(): AtomInterface {
        $current = $this->id;
        $interface = $this->addAtom('Interface', $current);
        $this->currentClassTrait->pushContext($interface);
        $this->makeAttributes($interface);
        $this->makePhpdoc($interface);
        $extras = array('EXTENDS'   => array(),
                        );
        $interface->ws->opening = $this->tokens[$current][1] . $this->tokens[$current][4];

        $this->contexts->nestContext(Context::CONTEXT_CLASS);
        $this->contexts->toggleContext(Context::CONTEXT_CLASS);

        $name = $this->processNextAsIdentifier(self::WITHOUT_FULLNSPATH);
        $this->addLink($interface, $name, 'NAME');

        $this->getFullnspath($name, 'class', $interface);

        $this->calls->addDefinition(Calls::A_CLASS, $interface->fullnspath, $interface);

        $this->checkPhpdoc();

        // Process extends
        $rank = 0;
        $fullcode= array();
        $extendsKeyword = '';
        if ($this->nextIs(array($this->phptokens::T_EXTENDS))) {
            $extendsKeyword = $this->tokens[$this->id + 1][1];
            $interface->ws->toextends =  $this->tokens[$this->id + 1][1] . $this->tokens[$this->id + 1][4];
            do {
                $this->moveToNext(); // Skip extends or ,
                $this->checkPhpdoc();
                $extends = $this->processOneNsname(self::WITH_FULLNSPATH);
                $extends->rank = $rank;

                $this->addLink($interface, $extends, 'EXTENDS');
                $this->calls->addCall(Calls::A_CLASS, $extends->fullnspath, $extends);

                $fullcode[] = $extends->fullcode;
                $extras['EXTENDS'][] = $extends;
                $interface->ws->toextendsseparator[] = $this->tokens[$this->id + 1][1] . $this->tokens[$this->id + 1][4];
            } while ($this->nextIs(array($this->phptokens::T_COMMA)));
            array_pop($interface->ws->toextendsseparator);
            $interface->ws->toextendsseparator[] = '';
        }

        $this->checkPhpdoc();
        $interface->ws->toblock = $this->tokens[$this->id + 1][1] . $this->tokens[$this->id + 1][4];

        // Process block
        $this->makeCitBody($interface);

        $this->runPlugins($interface, $extras);

        $interface->fullcode   = $this->tokens[$current][1] . ' ' . $name->fullcode . (empty($extendsKeyword) ? '' : ' ' . $extendsKeyword . ' ' . implode(', ', $fullcode)) . static::FULLCODE_BLOCK;
        $interface->ws->closing = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];

        $this->addToSequence($interface);
        $this->sequence->ws->separators[] = '';

        $this->contexts->exitContext(Context::CONTEXT_CLASS);
        $this->currentClassTrait->popContext();

        return $interface;
    }

    private function makeCitBody(AtomInterface $class): void {
        $this->moveToNext();
        $rank = -1;

        $this->currentProperties      = new ContextProperties();
        $this->currentMethods         = new ContextMethods();

        $this->checkPhpdoc();
        while (!$this->nextIs(array($this->phptokens::T_CLOSE_CURLY))) {
            $this->checkAttribute();
            $this->checkPhpdoc();
            if ($this->nextIs(array($this->phptokens::T_STATIC))) {
                $cpm = $this->processStaticDefinition();
            } elseif ($this->nextIs(array($this->phptokens::T_READONLY))) {
                $this->moveToNext();
                $cpm = $this->processReadonly(self::PROMOTED, true);
            } else {
                $cpm = $this->processNext();
            }
            $class->ws->bodyseparator[] = '';
            $this->popExpression();

            switch ($cpm->atom) {
                case 'Usetrait':
                    $link = 'USE';
                    break;

                case 'Phpdoc':
                    // Skip everything for phpdocs
                    continue 2;

                default:
                    // PPP, METHOD, MAGICMETHOD, CONST, ENUMCASE
                    $link = strtoupper($cpm->atom);
                    break;
            }
            $cpm->rank = ++$rank;

            if ($class->atom === 'Interface' && $cpm->isA(array('Method', 'Magicmethod'))) {
                $cpm->abstract = self::ABSTRACT;
            }

            $this->addLink($class, $cpm, $link);
            $last = count($class->ws->bodyseparator) - 1;
            if ($this->nextIs(array($this->phptokens::T_SEMICOLON))) {
                $this->moveToNext();
                $class->ws->bodyseparator[$last] .= ';' . $this->tokens[$this->id][4];
            } else {
                $class->ws->bodyseparator[$last] = '';
            }
            $this->checkPhpdoc();
        }

        $currentClass = $this->currentClassTrait->getCurrent();

        $diff = $this->currentProperties->listUndefinedProperties();
        foreach ($diff as $missing) {
            $ppp = $this->addAtom('Ppp');
            $ppp->fullcode     = 'public $' . $missing;
            $ppp->visibility   = 'none';
            $ppp->code         = $missing;
            $ppp->count        = 1;
            $ppp->line         = -1;
            $this->addLink($currentClass, $ppp, 'PPP');

            $virtual = $this->addAtom('Virtualproperty');
            $virtual->code	       = '$' . $missing;
            $virtual->fullcode     = $virtual->code;
            $virtual->propertyname = $missing;
            $virtual->line         = -1;
            $this->addLink($ppp, $virtual, 'PPP');
            $this->addLink($virtual, $this->addAtomVoid(), 'DEFAULT');

            foreach ($this->currentProperties->getCurrentPropertyCalls($missing) as $member) {
                $this->addLink($virtual, $member, 'DEFINITION');
            }

            $this->currentProperties->setCurrentPropertyCalls($missing, $virtual);
        }

        $diff = $this->currentMethods->listUndefinedMethods();
        foreach ($diff as $missing) {
            $virtual = $this->addAtom('Virtualmethod');
            $virtual->code		   = 'function ' . $missing . ' ( ) { /**/ } ';
            $virtual->fullcode     = $virtual->code;
            $virtual->visibility   = 'none';
            $virtual->code         = mb_strtolower($missing);
            $virtual->line         = -1;
            $this->addLink($currentClass, $virtual, 'METHOD');
            // @todo : may be MAGICMETHOD ?

            foreach ($this->currentMethods->getCurrentMethodsCalls($missing) as $member) {
                $this->addLink($virtual, $member, 'DEFINITION');
            }

            $this->currentMethods->setCurrentMethodsCalls($missing, $virtual);
        }

        $this->currentProperties      = new ContextProperties();
        $this->currentMethods         = new ContextMethods();

        $this->moveToNext();
    }

    private function processEnumCase(): AtomInterface {
        $case = $this->addAtom('Enumcase', $this->id);

        $this->makeAttributes($case);
        $this->makePhpdoc($case);

        $case->ws->opening = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];

        $name = $this->processNextAsIdentifier(self::WITHOUT_FULLNSPATH);
        $case->fullcode = 'case ' . $name->fullcode;
        $this->addLink($case, $name, 'NAME');
        $this->moveToNext();

        $this->calls->addDefinition(Calls::STATICCONSTANT,   $this->currentClassTrait->getCurrent()->fullnspath . '::' . $name->fullcode, $case);

        if ($this->nextIs(array($this->phptokens::T_EQUAL), 0)) {
            $case->ws->operator = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];

            $default = $this->processExpression(array($this->phptokens::T_SEMICOLON,
                                                      $this->phptokens::T_COMMA,
                                                      $this->phptokens::T_DOC_COMMENT,
                                                      $this->phptokens::T_CLOSE_TAG,
                                                    ));

            $this->addLink($case, $default, 'VALUE');
            $case->fullcode .= ' = ' . $default->fullcode;
            $case->ws->closing = '';
        } else {
            $case->ws->closing = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];
        }


        return $case;
    }

    private function processImplements(AtomInterface $class): string {
        // Process implements
        if (!$this->nextIs(array($this->phptokens::T_IMPLEMENTS))) {
            $this->checkPhpdoc();
            return '';
        }

        $class->ws->toimplements =  $this->tokens[$this->id + 1][1] . $this->tokens[$this->id + 1][4];

        $implementsKeyword = $this->tokens[$this->id + 1][1];
        $fullcodeImplements = array();
        $extras = array('IMPLEMENTS' => array());
        do {
            $this->moveToNext(); // Skip implements
            $this->checkPhpdoc();
            $implements = $this->processOneNsname(self::WITHOUT_FULLNSPATH);
            $class->ws->toimplementsseparator[] = $this->tokens[$this->id + 1][1] . $this->tokens[$this->id + 1][4];

            $this->addLink($class, $implements, 'IMPLEMENTS');
            $fullcodeImplements[] = $implements->fullcode;
            $extras['IMPLEMENTS'][] = $implements;

            $this->getFullnspath($implements, 'class', $implements);
            $this->calls->addCall(Calls::A_CLASS, $implements->fullnspath, $implements);
        } while ($this->nextIs(array($this->phptokens::T_COMMA)));
        array_pop($class->ws->toimplementsseparator);
        $class->ws->toimplementsseparator[] = '';
        $implements = ' ' . $implementsKeyword . ' ' . implode(', ', $fullcodeImplements);
        $this->runPlugins($class, $extras);

        $this->checkPhpdoc();

        return $implements;
    }

    private function processEnum(): AtomInterface {
        $current = $this->id;
        $enum = $this->addAtom('Enum', $current);

        $this->makeAttributes($enum);

        $name = $this->processNextAsIdentifier(self::WITHOUT_FULLNSPATH);

        $this->getFullnspath($name, 'class', $enum);

        $this->calls->addDefinition(Calls::A_CLASS, $enum->fullnspath, $enum);
        $this->addLink($enum, $name, 'NAME');

        if ($this->nextIs(array($this->phptokens::T_COLON))) {
            $this->moveToNext();
            $returnTypes = ' : ' . $this->processTypehint($enum);
        } else {
            $enum->typehint = 'one';
            $returnTypes = '';
        }

        $implements = $this->processImplements($enum);

        $this->currentClassTrait->pushContext($enum);

        $this->makePhpdoc($enum);

        $enum->ws->opening = $this->tokens[$current][1] . $this->tokens[$current][4];

        $this->contexts->nestContext(Context::CONTEXT_CLASS);
        $this->contexts->toggleContext(Context::CONTEXT_CLASS);
        $this->contexts->nestContext(Context::CONTEXT_NEW);
        $this->contexts->nestContext(Context::CONTEXT_FUNCTION);

        $previousContextVariables = $this->currentVariables;
        $this->currentVariables = new ContextVariables();

        $extras = array();

        $enum->ws->toblock = $this->tokens[$this->id + 1][1] . $this->tokens[$this->id + 1][4];

        // Process block
        $this->makeCitBody($enum);

        $enum->ws->closing = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];

        $this->contexts->exitContext(Context::CONTEXT_CLASS);
        $this->contexts->exitContext(Context::CONTEXT_NEW);
        $this->contexts->exitContext(Context::CONTEXT_FUNCTION);

        $this->currentClassTrait->popContext();

        $this->currentVariables = $previousContextVariables;

        $this->runPlugins($enum, $extras);

        $enum->fullcode   = $this->tokens[$current][1] . ' ' . $name->fullcode
                            . $returnTypes
                            . $implements
                            . static::FULLCODE_BLOCK;

        $this->addToSequence($enum);
        $this->sequence->ws->separators[] = '';

        return $enum;
    }

    private function processClass(): AtomInterface {
        $current = $this->id;

        if ($this->nextIs(array($this->phptokens::T_STRING))) {
            $class = $this->addAtom('Class', $current);

            $name = $this->processNextAsIdentifier(self::WITHOUT_FULLNSPATH);

            $this->getFullnspath($name, 'class', $class);

            $this->calls->addDefinition(Calls::A_CLASS, $class->fullnspath, $class);
            $this->addLink($class, $name, 'NAME');
        } else {
            if ($this->nextIs(array($this->phptokens::T_OPEN_PARENTHESIS))) {
                // Process arguments
                $this->moveToNext(); // Skip (
                $argsId = $this->id;
                $class = $this->processArguments('Classanonymous', array());
                $class->ws->toargs = $this->tokens[$argsId][1] . $this->tokens[$argsId][4];
                $class->ws->endargs = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];
                $argumentsFullcode = $class->fullcode;
            } else {
                $class = $this->addAtom('Classanonymous', $current);
            }

            $class->fullnspath = $this->anonymousNames->getName(AnonymousNames::A_CLASS);
            $this->calls->addDefinition(Calls::A_CLASS, $class->fullnspath, $class);
        }
        $this->makePhpdoc($class);
        if ($class->atom === 'Class') {
            $this->makeAttributes($class);
        }

        $class->ws->opening = $this->tokens[$current][1] . $this->tokens[$current][4];

        $this->currentClassTrait->pushContext($class);

        $this->contexts->nestContext(Context::CONTEXT_CLASS);
        $this->contexts->toggleContext(Context::CONTEXT_CLASS);
        $this->contexts->nestContext(Context::CONTEXT_NEW);
        $this->contexts->nestContext(Context::CONTEXT_FUNCTION);

        $previousContextVariables = $this->currentVariables;
        $this->currentVariables = new ContextVariables();

        $extras = array();
        // Process extends
        if ($this->nextIs(array($this->phptokens::T_EXTENDS))) {
            $extendsKeyword = $this->tokens[$this->id + 1][1];
            $this->moveToNext(); // Skip extends
            $class->ws->toextends =  $this->tokens[$this->id][1] . $this->tokens[$this->id][4];

            $this->checkPhpdoc();
            $extends = $this->processOneNsname(self::WITHOUT_FULLNSPATH);
            $class->ws->toextendsseparator[] = '';

            $this->addLink($class, $extends, 'EXTENDS');
            $this->getFullnspath($extends, 'class', $extends);
            $extras['EXTENDS'] = $extends;

            $this->calls->addCall(Calls::A_CLASS, $extends->fullnspath, $extends);
        } else {
            $extends = '';
            $class->ws->toextends = '';
        }
        $this->checkPhpdoc();

        $implements = $this->processImplements($class);

        $class->ws->toblock = $this->tokens[$this->id + 1][1] . $this->tokens[$this->id + 1][4];

        // Process block
        $this->makeCitBody($class);

        $class->ws->closing = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];

        $this->runPlugins($class, $extras);

        $class->fullcode   = $this->tokens[$current][1] . ($class->atom === 'Classanonymous' ? '' : ' ' . $name->fullcode)
                             . (isset($argumentsFullcode) ? ' (' . $argumentsFullcode . ')' : '')
                             . (empty($extends) ? '' : ' ' . $extendsKeyword . ' ' . $extends->fullcode)
                             . $implements
                             . static::FULLCODE_BLOCK;

        // Case of anonymous classes
        if ($this->tokens[$current - 1][0] === $this->phptokens::T_NEW) {
            $this->pushExpression($class);
        } else {
            $this->addToSequence($class);
            $this->sequence->ws->separators[] = '';
        }

        $this->contexts->exitContext(Context::CONTEXT_CLASS);
        $this->contexts->exitContext(Context::CONTEXT_NEW);
        $this->contexts->exitContext(Context::CONTEXT_FUNCTION);

        $this->currentClassTrait->popContext();

        $this->currentVariables = $previousContextVariables;
        return $class;
    }

    private function processOpenTag(): AtomInterface {
        $current = $this->id;
        $phpcode = $this->addAtom('Php', $current);
        $phpcode->ws->opening = $this->tokens[$current][1] . $this->tokens[$current][4];

        $this->startSequence();

        $this->checkPhpdoc();

        // Special case for pretty much empty script (<?php .... END)
        if ($this->nextIs(array($this->phptokens::T_END))) {
            $void = $this->addAtomVoid();
            $void->ws->closing = '';
            $this->addToSequence($void);

            $this->addLink($phpcode, $this->sequence, 'CODE');
            $this->sequence->ws->opening = '';
            $this->sequence->ws->separators[] = '';
            $this->endSequence();
            $closing = '';

            $phpcode->ws->closing = '';
            $phpcode->code        = $this->tokens[$current][1];
            $phpcode->close_tag   = self::NO_CLOSING_TAG;

            return $phpcode;
        }

        $n = count($this->tokens) - 2;
        if ($this->tokens[$n][0] === $this->phptokens::T_INLINE_HTML) {
            --$n;
        }

        while ($this->id < $n) {
            if ($this->nextIs(array($this->phptokens::T_OPEN_TAG_WITH_ECHO), 0)) {
                --$this->id;
                $echo = $this->processOpenWithEcho();
                /// processing the first expression as an echo
                $this->addToSequence($echo);
                if ($this->nextIs(array($this->phptokens::T_END))) {
                    --$this->id;
                }
            } elseif ($this->nextIs(array($this->phptokens::T_CLOSE_TAG), 0)) {
                --$this->id;
            }
            $this->processNext();
        }

        if ($this->nextIs(array($this->phptokens::T_INLINE_HTML), 0)) {
            --$this->id;
        }

        if ($this->nextIs(array($this->phptokens::T_CLOSE_TAG), 0)) {
            $closeTag = self::CLOSING_TAG;
            $closing = $this->tokens[$this->id][1]; // This includes final new lines
        } elseif ($this->nextIs(array($this->phptokens::T_HALT_COMPILER), 0)) {
            $closeTag = self::NO_CLOSING_TAG;
            $this->moveToNext(); // Go to HaltCompiler
            $this->processHalt();
            $closing = '';
        } else {
            // Why?
            // This prevents a missing separator, on the main php code sequence, with some < ?php if ( true ) {	? >A<?php }
            $this->sequence->ws->separators[] = '';

            $closeTag = self::NO_CLOSING_TAG;
            $closing = '';
        }

        $phpcode->ws->closing  = $closing;
        if ($this->nextIs(array($this->phptokens::T_OPEN_TAG), -1)) {
            $void = $this->addAtomVoid();
            $this->addToSequence($void);
        }

        $this->sequence->ws->opening = '';
        $this->addLink($phpcode, $this->sequence, 'CODE');
        $this->endSequence();

        $phpcode->code         = $this->tokens[$current][1];
        $phpcode->fullcode     = '<?php ' . self::FULLCODE_SEQUENCE . ' ' . $closing;
        $phpcode->token        = $this->getToken($this->tokens[$current][0]);
        $phpcode->close_tag    = $closeTag;

        return $phpcode;
    }

    private function processSemicolon(): AtomInterface {
        $atom = $this->popExpression();
        if ($atom->atom === 'Void') {
            $atom->ws->closing = '';
        }
        if ($this->tokens[$this->id][1] === ';') {
            $this->sequence->ws->separators[] = ';' . $this->tokens[$this->id][4];
        } else {
            $this->sequence->ws->separators[] = '';
        }
        $this->addToSequence($atom);

        return $atom;
    }

    private function processClosingTag(): AtomInterface {
        if ($this->nextIs(array($this->phptokens::T_INLINE_HTML)) &&
            $this->nextIs(array($this->phptokens::T_OPEN_TAG,
                                $this->phptokens::T_OPEN_TAG_WITH_ECHO,
                                $this->phptokens::T_INLINE_HTML), 2)) {
            // it is possible to have multiple INLINE_HTML in a row : <?php//b ? >
            do {
                $this->moveToNext();
                assert($this->nextIs(array($this->phptokens::T_INLINE_HTML), 0), 'Not an inline HTML : ' . print_r($this->tokens[$this->id], true));
                $return = $this->processInlinehtml();
                $this->addToSequence($return);
                $return->ws->closing = $this->tokens[$this->id + 1][1] . $this->tokens[$this->id + 1][4];
            } while ( $this->nextIs(array($this->phptokens::T_INLINE_HTML)));

            if ($this->nextIs(array($this->phptokens::T_OPEN_TAG_WITH_ECHO))) {
                $return = $this->processOpenWithEcho();

                if ($this->nextIs(array($this->phptokens::T_SEMICOLON), 0)) {
                    $this->addToSequence($return);

                    // for ; ending the <?= 'c';
                    $this->sequence->ws->separators[] = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];
                } elseif (!$this->nextIs(array($this->phptokens::T_SEMICOLON))) {
                    $this->addToSequence($return);
                }
            } else {
                $this->sequence->ws->separators[] = '';
                $this->moveToNext(); // set to opening tag
            }
        } elseif ($this->nextIs(array($this->phptokens::T_OPEN_TAG,
                                      $this->phptokens::T_OPEN_TAG_WITH_ECHO,
                                      ))) {
            if ($this->nextIs(array($this->phptokens::T_OPEN_TAG_WITH_ECHO))) {
                $return = $this->processOpenWithEcho();
                if (!$this->nextIs(array($this->phptokens::T_SEMICOLON))) {
                    $this->addToSequence($return);
                    $this->sequence->ws->separators[] = '?><?= /*Y*/ ';
                }
                $return->ws->opening = '';
            } else {
                $return = $this->addAtomVoid();
                $this->addToSequence($return);
                $this->sequence->ws->separators[] = '?><?php /*Z*/ ';
                $this->moveToNext(); // set to opening tag
            }
        } elseif ($this->nextIs(array($this->phptokens::T_END,
                                      ))) {
            // return dummy atom, not added anywhere
            return $this->atomVoid;
        } else {
            $return = $this->processSemicolon();
        }

        return $return;
    }

    private function processOpenWithEcho(): AtomInterface {
        // Processing ECHO
        $echo = $this->processNextAsIdentifier(self::WITHOUT_FULLNSPATH);

        $noSequence = $this->contexts->isContext(Context::CONTEXT_NOSEQUENCE);
        if ($noSequence === false) {
            $this->contexts->toggleContext(Context::CONTEXT_NOSEQUENCE);
        }
        $functioncall = $this->processArguments('Echo',
            array($this->phptokens::T_SEMICOLON,
                  $this->phptokens::T_CLOSE_TAG,
                  $this->phptokens::T_END,
                  ));
        $argumentsFullcode = $functioncall->fullcode;

        if ($noSequence === false) {
            $this->contexts->toggleContext(Context::CONTEXT_NOSEQUENCE);
        }

        //processArguments goes too far, up to ;
        if ($this->nextIs(array($this->phptokens::T_CLOSE_TAG), 0)) {
            --$this->id;
        }

        $functioncall->code        = $echo->code;
        $functioncall->fullcode    = '<?= ' . $argumentsFullcode;
        $functioncall->token       = 'T_OPEN_TAG_WITH_ECHO';
        $functioncall->fullnspath  = '\echo';
        $functioncall->ws->opening = '';
        $functioncall->ws->closing = $this->tokens[$this->id + 1][4];

        $this->addLink($functioncall, $echo, 'NAME');

        return $functioncall;
    }

    private function makeNsname(): AtomInterface {
        if ($this->nextIs(array($this->phptokens::T_NAME_QUALIFIED), 0)) {
            $fullcode = array($this->tokens[$this->id][1]);
            $token = 'T_NAME_QUALIFIED';
            $absolute = self::NOT_ABSOLUTE;

            if ($this->contexts->isContext(Context::CONTEXT_NEW)) {
                $atom = 'Newcallname';
            } else {
                $atom = 'Nsname';
            }
        } elseif ($this->nextIs($this->phpKeywords, 0)) {
            $fullcode = array($this->tokens[$this->id][1]);
            $token = 'T_NAME_QUALIFIED';
            $absolute = self::NOT_ABSOLUTE;

            if ($this->contexts->isContext(Context::CONTEXT_NEW)) {
                $atom = 'Newcallname';
            } else {
                $atom = 'Nsname';
            }
        } elseif ($this->nextIs(array($this->phptokens::T_NAME_FULLY_QUALIFIED), 0)) {
            $fullcode = array($this->tokens[$this->id][1]);
            $token = 'T_NAME_FULLY_QUALIFIED';
            $absolute = self::ABSOLUTE;

            if ($this->contexts->isContext(Context::CONTEXT_NEW)) {
                $atom = 'Newcallname';
            } elseif (in_array(mb_strtolower($this->tokens[$this->id][1]), array('\\true', '\\false'), STRICT_COMPARISON)) {
                $atom = 'Boolean';
            } elseif (in_array(mb_strtolower($this->tokens[$this->id][1]), array('\\null'), STRICT_COMPARISON)) {
                $atom = 'Null';
            } else {
                $atom = 'Nsname';
            }
        } elseif ($this->nextIs(array($this->phptokens::T_NAME_RELATIVE), 0)) {
            $fullcode = array($this->tokens[$this->id][1]);
            $token = 'T_NAME_RELATIVE';
            $absolute = self::NOT_ABSOLUTE;

            if ($this->contexts->isContext(Context::CONTEXT_NEW)) {
                $atom = 'Newcallname';
            } else {
                $atom = 'Nsname';
            }
        } else {
            $token = 'T_NS_SEPARATOR';
            $fullcode = array();

            if ($this->nextIs(array($this->phptokens::T_NS_SEPARATOR), 0)             &&
                $this->nextIs(array($this->phptokens::T_STRING))                            &&
                in_array(mb_strtolower($this->tokens[$this->id + 1][1]), array('true', 'false'), STRICT_COMPARISON) &&
                !$this->nextIs(array($this->phptokens::T_NS_SEPARATOR), 2)
            ) {
                $atom = 'Boolean';
            } elseif ($this->nextIs(array($this->phptokens::T_NS_SEPARATOR), 0) &&
                      $this->nextIs(array($this->phptokens::T_STRING))                     &&
                      mb_strtolower($this->tokens[$this->id + 1][1]) === 'null'            &&
                      !$this->nextIs(array($this->phptokens::T_NS_SEPARATOR), 2)           ) {
                $atom = 'Null';
            } elseif (mb_strtolower($this->tokens[$this->id][1]) === 'parent') {
                $atom = 'Parent';
            } elseif (mb_strtolower($this->tokens[$this->id][1]) === 'self') {
                $atom = 'Self';
            } elseif ($this->nextIs(array($this->phptokens::T_NS_SEPARATOR), 0) &&
                      $this->nextIs(array($this->phptokens::T_STRING))                     &&
                      mb_strtolower($this->tokens[$this->id + 1][1]) === 'self'            &&
                      !$this->nextIs(array($this->phptokens::T_NS_SEPARATOR), 2)           ) {
                $atom = 'Self';
            } elseif ($this->contexts->isContext(Context::CONTEXT_NEW)) {
                $atom = 'Newcall';
            } else {
                $atom = 'Nsname';
                $token = 'T_STRING';
            }

            if ($this->nextIs(array($this->phptokens::T_STRING,
                                    $this->phptokens::T_INSTEADOF,
                                    $this->phptokens::T_TRAIT,
                                    $this->phptokens::T_ENUM,
                                    $this->phptokens::T_ISSET,
                                    $this->phptokens::T_CALLABLE,
                                    // @todo the list should be longer and may be moved to a constant
                                     ), 0)) {
                $fullcode[] = $this->tokens[$this->id][1];
                $this->moveToNext();

                $absolute = self::NOT_ABSOLUTE;
            } elseif ($this->nextIs(array($this->phptokens::T_NAMESPACE), -1)) {
                $fullcode[] = $this->tokens[$this->id - 1][1];

                $absolute = self::NOT_ABSOLUTE;
            } elseif ($this->nextIs(array($this->phptokens::T_NS_SEPARATOR), 0)) {
                $fullcode[] = '';

                $absolute = self::ABSOLUTE;
            } else {
                $fullcode[] = $this->tokens[$this->id][1];
                $this->moveToNext();

                $absolute = self::NOT_ABSOLUTE;
            }

            while ( $this->nextIs(array($this->phptokens::T_NS_SEPARATOR), 0)    &&
                   !$this->nextIs(array($this->phptokens::T_OPEN_CURLY))
            ) {
                $this->moveToNext(); // skip \
                $fullcode[] = $this->tokens[$this->id][1];

                // Go to next
                $this->moveToNext(); // skip \
                $token = 'T_NS_SEPARATOR';
            }

            // Back up a bit
            --$this->id;
        }

        if ($this->contexts->isContext(Context::CONTEXT_NEW)) {
            if ($this->nextIs(array($this->phptokens::T_OPEN_PARENTHESIS))) {
                $atom = 'Newcallname';
            } elseif ($this->nextIs(array($this->phptokens::T_DOUBLE_COLON), 0)) {
                // Finally, it is D::$D
                $atom = 'Identifier';
            }
        }

        $nsname = $this->addAtom($atom);
        $nsname->code        = implode('\\', $fullcode);
        $nsname->fullcode    = $nsname->code;
        $nsname->token       = $token;
        $nsname->absolute    = $absolute;
        $nsname->ws->opening = $nsname->code;
        $this->runPlugins($nsname);

        return $nsname;
    }

    private function processNsname(): AtomInterface {
        if ($this->debug) {
            print '  ' . __METHOD__ . ' in ' . PHP_EOL;
        }
        $nsname = $this->makeNsname();

        // Review this : most nsname will end up as constants!
        if ($this->nextIs(array($this->phptokens::T_INSTANCEOF), -1)   ||
            $this->nextIs(array($this->phptokens::T_DOUBLE_COLON))     ||
            $this->nextIs(array($this->phptokens::T_VARIABLE       ))) {
            $this->getFullnspath($nsname, 'class', $nsname);

            $this->calls->addCall(Calls::A_CLASS, $nsname->fullnspath, $nsname);
            $this->pushExpression($nsname);
        } elseif ($this->contexts->isContext(Context::CONTEXT_NEW) &&
                  !$this->nextIs(array($this->phptokens::T_OPEN_PARENTHESIS))) {
            $this->getFullnspath($nsname, 'class', $nsname);
            $this->calls->addCall(Calls::A_CLASS, $nsname->fullnspath, $nsname);
        } elseif ($this->nextIs(array($this->phptokens::T_OPEN_PARENTHESIS))) {
            $this->pushExpression($nsname);
            $nsname = $this->processFCOA($nsname);
        } elseif ($this->nextIs(array($this->phptokens::T_OPEN_BRACKET))) {
            $this->pushExpression($nsname);
            $nsname = $this->processBracket();
            $this->popExpression();
        } else {
            $this->getFullnspath($nsname, 'const', $nsname);
            $nsname->use = 'const';
            $this->runPlugins($nsname);
            $this->calls->addCall(Calls::CONST, $nsname->fullnspath, $nsname);
            $this->pushExpression($nsname);
        }

        if ($this->debug) {
            print '  ' . __METHOD__ . ' out ' . PHP_EOL;
        }
        return $nsname;
    }

    private function processTypehint(AtomInterface $holder, string $link = ''): string {
        $typehintToken = array($this->phptokens::T_NS_SEPARATOR,
                               $this->phptokens::T_STRING,
                               $this->phptokens::T_NAMESPACE,
                               $this->phptokens::T_ARRAY,
                               $this->phptokens::T_CALLABLE,
                               $this->phptokens::T_STATIC,
                               $this->phptokens::T_QUESTION,
                               $this->phptokens::T_NAME_QUALIFIED,
                               $this->phptokens::T_NAME_RELATIVE,
                               $this->phptokens::T_NAME_FULLY_QUALIFIED,
                               $this->phptokens::T_OPEN_PARENTHESIS,
//                               $this->phptokens::T_CLOSE_PARENTHESIS, Unused
        );

        // default typehint style is 'one'
        $holder->typehint = 'one';

        // return type allows for static. Not valid for arguments.
        if ($holder->isA(array('Ppp', 'Parameter', 'Enum', 'Void'))) {
            $link = 'TYPEHINT';
            $holder->ws->totype = '';
        } elseif ($holder->isA(array('Const'))) {
            $link = 'TYPEHINT';
            $holder->ws->totype = '';
        } elseif ($holder->isA(array('Method', 'Magicmethod', 'Function', 'Closure', 'Arrowfunction'))) {
            $link = 'RETURNTYPE';
            $holder->ws->totype = $this->tokens[$this->id + 1][1] . $this->tokens[$this->id + 1][4];

            // Skip : if it is here (it might not)
            if ($this->nextIs(array($this->phptokens::T_COLON))) {
                $this->moveToNext();
            }
        } elseif ($holder->isA(array('Parenthesis'))) {
            // Actually, nothing. Leaving it going.
        } else {
            die("Did not recognize a type for {$holder->atom}\n");
        }

        if (!$this->nextIs($typehintToken)) {
            if ($this->nextIs(array($this->phptokens::T_ELLIPSIS))) {
                $typehint = $this->addAtom('Scalartypehint', $this->id + 1);
                $typehint->fullnspath = '\\array';
                $typehint->fullcode   = '';
            } else {
                $typehint = $this->addAtomVoid();
                $typehint->rank = 0;
            }

            $this->addLink($holder, $typehint, $link);
            $holder->ws->totype = '';

            return '';
        }

        $return = array();

        if ($this->nextIs(array($this->phptokens::T_QUESTION))) {
            $null = $this->addAtom('Scalartypehint');
            $null->code        = '?';
            $null->fullcode    = '?';
            $null->token       = 'T_STRING';
            $null->noDelimiter = '';
            $null->delimiter   = '';
            $null->fullnspath  = '\\null';
            $null->ws->closing = $this->tokens[$this->id + 1][4];

            $return[] = $null;
            $this->moveToNext();

            $holder->typehint = 'or';
        }

        --$this->id;
        $dnf = 0;
        do {
            $this->moveToNext();

            if (in_array(mb_strtolower($this->tokens[$this->id + 1][1]), $this->SCALAR_TYPE, STRICT_COMPARISON) &&
                 !$this->nextIs(array($this->phptokens::T_NS_SEPARATOR), 2)) {
                $this->moveToNext();
                $nsname = $this->processSingle('Scalartypehint');
                $nsname->fullnspath = '\\' . mb_strtolower($nsname->code);
            } elseif ($this->nextIs(array($this->phptokens::T_STATIC))) {
                $this->moveToNext();

                $nsname = $this->addAtom('Static');
                $nsname->code        = $this->tokens[$this->id][1];
                $nsname->fullcode    = $this->tokens[$this->id][1];
                $nsname->token       = 'T_STATIC';
                $nsname->noDelimiter = '';
                $nsname->delimiter   = '';
                $nsname->fullnspath  = '\\static';
                $nsname->ws->closing = $this->tokens[$this->id + 1][4];
            } elseif (mb_strtolower($this->tokens[$this->id + 1][1]) === 'null') {
                $this->moveToNext();
                $nsname = $this->processSingle('Null');
                $nsname->fullnspath = '\\null';
            } elseif ($this->nextIs(array($this->phptokens::T_OPEN_PARENTHESIS))) {
                $this->moveToNext();
                $nsname = $this->addAtom('Parenthesis', $this->id);
                $nsname->fullnspath = '\\void';

                $sub = $this->processTypehint($nsname, $link);
                $sub = trim($sub, ': ');
                $nsname->fullcode = '( ' . $sub . ' )';
                $this->moveToNext();

                $holder->typehint = 'dnf';
                $nsname->ws->closing .= $this->tokens[$this->id + 1][1] . $this->tokens[$this->id + 1][4];
            } else {
                $nsname = $this->processOneNsname(self::WITHOUT_FULLNSPATH);
                $this->getFullnspath($nsname, 'class', $nsname);
                $nsname->use = 'class';
                $this->runPlugins($nsname);
                $this->calls->addCall(Calls::A_CLASS, $nsname->fullnspath, $nsname);
            }

            if ($this->nextIs(array($this->phptokens::T_OR))) {
                // @todo no fallback to and from dnf
                $holder->typehint = 'or';
                $nsname->ws->closing .= $this->tokens[$this->id + 1][1] . $this->tokens[$this->id + 1][4];
            }

            if ($this->nextIs(array($this->phptokens::T_AMPERSAND_NOT_FOLLOWED_BY_VAR_OR_VARARG))) {
                // @todo no fallback to and from dnf
                $holder->typehint = 'and';
                $nsname->ws->closing .= $this->tokens[$this->id + 1][1] . $this->tokens[$this->id + 1][4];
            }

            $return[] = $nsname;
        } while ($this->nextIs(array($this->phptokens::T_OR,
                                     $this->phptokens::T_AMPERSAND_NOT_FOLLOWED_BY_VAR_OR_VARARG,
                                     ))
                                     &&
                 !$this->nextIs(array($this->phptokens::T_VARIABLE), 2)
        );

        if ($this->tokens[$this->id + 1][1] === ',') {
            $this->moveToNext();
        }

        if ($return[0]->code === '?') {
            $this->addLink($holder, $return[0], $link);
            $this->addLink($holder, $return[1], $link);

            $return[0]->rank = 0;
            $return[1]->rank = 1;

            $returnTypeFullcode = '?' . $return[1]->fullcode;
        } else {
            $fullcode = array();
            $rank = -1;
            foreach ($return as $returnType) {
                $this->addLink($holder, $returnType, $link);
                $returnType->rank = ++$rank;

                if ($returnType->isA(array('Void'))) {
                    $fullcode[] = '(' . $returnType->fullcode . ')';
                } elseif ($returnType->code === '?') {
                    array_unshift($fullcode, '?');
                    $fullcode = array_values($fullcode);
                } else {
                    $fullcode[] = $returnType->fullcode;
                }
            }

            if ($holder->typehint === 'or') {
                $returnTypeFullcode = implode('|', $fullcode);
            } elseif ($holder->typehint === 'and') {
                $returnTypeFullcode = implode('&', $fullcode);
            } elseif ($holder->typehint === 'dnf') {
                $returnTypeFullcode = implode(' | ', $fullcode);
            } elseif ($holder->typehint === 'one') {
                $returnTypeFullcode = implode('', $fullcode);
            } else {
                throw new LoadError("Typehint is not or, and or dnf : '$holder->typehint'");
            }
        }

        switch ($link) {
            case 'RETURNTYPE':
                $returnTypeFullcode = ' : ' . $returnTypeFullcode;
                break;

            case 'TYPEHINT':
                $returnTypeFullcode .= ' ';
                break;

            default:
                throw new LoadError('Typehint link is neither RETURNTYPE nor TYPEHINT.');
        }

        return $returnTypeFullcode;
    }

    private function processParameters(string $atom): AtomInterface {
        $current   = $this->id;
        $arguments = $this->addAtom($atom, $current);
        $this->makePhpdoc($arguments);
        $this->makeAttributes($arguments);

        $this->currentFunction->add($arguments);

        $argumentsList  = array();

        $this->checkAttribute();
        if ($this->nextIs(array($this->phptokens::T_CLOSE_PARENTHESIS))) {
            $void = $this->addAtomVoid();
            $void->rank = 0;
            $this->addLink($arguments, $void, 'ARGUMENT');
            $void->ws->closing = '';

            $arguments->code     = $this->tokens[$current][1];
            $arguments->fullcode = self::FULLCODE_VOID;
            $arguments->token    = $this->getToken($this->tokens[$current][0]);
            $arguments->args_max = 0;
            $arguments->args_min = 0;
            $arguments->count    = 0;

            $this->runPlugins($arguments, array($void));

            $argumentsList[] = $void;

            // Skip the )
            $this->moveToNext();
            return $arguments;
        }

        $fullcode  = array();
        $argsMax   = 0;
        $argsMin   = 0;
        $rank      = -1;
        $default   = 0;
        $variadic  = self::NOT_ELLIPSIS;

        do {
            do {
                $this->checkPhpdoc();
                $this->checkAttribute();

                // PHP 8.0's trailing comma in signature
                if ($this->nextIs(array($this->phptokens::T_CLOSE_PARENTHESIS))) {
                    $fullcode[] = ' ';
                    $this->moveToNext();
                    break 1;
                }

                ++$argsMax;

                if ($this->nextIs(array($this->phptokens::T_READONLY))) {
                    $this->moveToNext();
                    $index = $this->processReadonly(self::PROMOTED, true);

                    $this->addLink($this->currentClassTrait->getCurrent(), $index, 'PPP');

                    $index->rank = ++$rank;
                    $this->popExpression();
                    $fullcode[] = $index->fullcode;
                    $this->addLink($arguments, $index, 'ARGUMENT');
                    $argumentsList[] = $index;
                    $this->moveToNext();

                    continue;
                }

                if ($this->nextIs(array($this->phptokens::T_PUBLIC,
                                        $this->phptokens::T_PRIVATE,
                                        $this->phptokens::T_PROTECTED,
                    ))
                ) {
                    $this->moveToNext();
                    $index = $this->processPPP(self::PROMOTED);

                    $this->moveToNext();

                    $this->addLink($this->currentClassTrait->getCurrent(), $index, 'PPP');
                    // Nothing better to do?
                    preg_match('/\$([^ ]+?)/', $index->fullcode, $r);
                    $this->currentVariables->set($r[0], $index);

                    $index->rank = ++$rank;
                    $this->popExpression();
                    $fullcode[] = $index->fullcode;
                    $this->addLink($arguments, $index, 'ARGUMENT');
                    $argumentsList[] = $index;

                    continue;
                }

                $index = $this->addAtom('Parameter');

                $typehints = $this->processTypehint($index);
                $this->checkPhpdoc();
                $this->moveToNext();

                if ($this->nextIs(array($this->phptokens::T_AND,
                                        $this->phptokens::T_AMPERSAND_FOLLOWED_BY_VAR_OR_VARARG), 0)) {
                    $reference = self::REFERENCE;
                    $referenceWS = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];
                    $this->moveToNext();
                } else {
                    $reference   = self::NOT_REFERENCE;
                    $referenceWS = null;
                }

                if ($this->nextIs(array($this->phptokens::T_ELLIPSIS), 0)) {
                    $variadic = self::ELLIPSIS;
                    $ellipsisWS = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];
                    $this->moveToNext();
                }

                assert($this->nextIs(array($this->phptokens::T_VARIABLE), 0), 'No variable in parameter list (' . $this->tokens[$this->id][1] . ') in file ' . $this->filename . ' on line ' . $this->tokens[$this->id][2]);

                $variable   = $this->addAtom('Parametername');
                $this->makeAttributes($index);
                $this->makePhpdoc($index);

                $variable->code     = $this->tokens[$this->id][1];
                $variable->fullcode = $this->tokens[$this->id][1];
                $variable->token    = $this->getToken($this->tokens[$this->id][0]);
                $variable->ws->opening = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];
                $variable->ws->closing = '';
                $this->runPlugins($variable);

                $index->code     = $variable->fullcode;
                $index->fullcode = $variable->fullcode;
                $index->token    = 'T_VARIABLE';

                if ($variadic === self::ELLIPSIS) {
                    $index->fullcode     = '...' . $index->fullcode;
                    $index->variadic     = self::ELLIPSIS;
                    $index->ws->ellipsis = $ellipsisWS;
                }

                if ($reference === self::REFERENCE) {
                    $index->fullcode  = '&' . $index->fullcode;
                    $index->reference = self::REFERENCE;
                    $index->ws->reference = $referenceWS;
                }

                $this->addLink($index, $variable, 'NAME');
                $variable->fullnspath = $index->fullnspath;
                $variable->isPhp      = $index->isPhp;
                $variable->isExt      = $index->isExt;
                $variable->isStub     = $index->isStub;
                $this->currentVariables->set($variable->code, $index);

                $this->checkPhpdoc();
                if ($this->nextIs(array($this->phptokens::T_EQUAL))) {
                    $this->moveToNext(); // Skip =
                    $index->ws->operator = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];
                    $finals      = array($this->phptokens::T_COMMA,
                                         $this->phptokens::T_CLOSE_PARENTHESIS
                                         );
                    $default = $this->processExpression($finals);
                } else {
                    if ($index->variadic === self::ELLIPSIS) {
                        $argsMax = \MAX_ARGS;
                    } else {
                        ++$argsMin;
                    }
                    $default = $this->addAtomVoid();
                }
                $this->addLink($index, $default, 'DEFAULT');
                if ($default->atom !== 'Void') {
                    $index->fullcode .= ' = ' . $default->fullcode;

                    // When Null is default, then typehint is also nullable
                    if ($default->atom === 'Null' &&
                        !str_contains($typehints, '?')   &&
                        preg_match('/\bnull\b/i', $typehints) === 0
                    ) {
                        $this->addLink($index, $default, 'TYPEHINT');
                    }
                }

                $index->rank = ++$rank;

                $index->fullcode = $typehints . $index->fullcode;
                $fullcode[] = $index->fullcode;
                $this->addLink($arguments, $index, 'ARGUMENT');
                $this->runPlugins($index);
                $argumentsList[] = $index;

                $this->moveToNext();
                $arguments->ws->separators[] = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];
            } while ($this->nextIs(array($this->phptokens::T_COMMA), 0));

            --$this->id;
        } while (!$this->nextIs(array($this->phptokens::T_CLOSE_PARENTHESIS)));
        $arguments->count    = $rank + 1;
        array_pop($arguments->ws->separators);

        // Skip the )
        $this->moveToNext();

        $arguments->fullcode = implode(', ', $fullcode);
        $arguments->token    = 'T_COMMA';
        $arguments->args_max = $argsMax;
        $arguments->args_min = $argsMin;
        $this->runPlugins($arguments, $argumentsList);

        return $arguments;
    }

    private function processArguments(string $atom, array $finals = array(), array &$argumentsList = array()): AtomInterface {
        if (empty($finals)) {
            $finals = array($this->phptokens::T_CLOSE_PARENTHESIS);
        }
        $current = $this->id;
        $arguments = $this->addAtom($atom, $current);
        $this->makePhpdoc($arguments);
        $argumentsId = array();

        $this->contexts->nestContext(Context::CONTEXT_NEW);
        $this->contexts->nestContext(Context::CONTEXT_NOSEQUENCE);
        $this->contexts->toggleContext(Context::CONTEXT_NOSEQUENCE);
        $fullcode = array();
        $this->checkPhpdoc();

        // case of empty arguments : adding void
        if ($this->nextIs(array($this->phptokens::T_CLOSE_PARENTHESIS,
                                $this->phptokens::T_CLOSE_BRACKET))) {
            $void = $this->addAtomVoid();
            $void->rank = 0;
            $void->ws->closing = '';
            $this->addLink($arguments, $void, 'ARGUMENT');
            $arguments->ws->separators[] = '';

            $arguments->code     = $this->tokens[$current][1];
            $arguments->fullcode = self::FULLCODE_VOID;
            $arguments->token    = $this->getToken($this->tokens[$current][0]);
            $arguments->args_max = 0;
            $arguments->args_min = 0;
            $arguments->count    = 0;
            $argumentsId[]       = $void;
            // $argument->ws is default value

            $argumentsList = array($void);
            $this->runPlugins($arguments, $argumentsList);

            $this->moveToNext();

            $this->contexts->exitContext(Context::CONTEXT_NOSEQUENCE);
            $this->contexts->exitContext(Context::CONTEXT_NEW);

            return $arguments;
        }

        // case strlen(...) with ellipsis
        if ( $this->nextIs(array($this->phptokens::T_ELLIPSIS)) &&
             $this->nextIs(array($this->phptokens::T_CLOSE_PARENTHESIS,
                                 $this->phptokens::T_CLOSE_BRACKET), 2)) {
            $void = $this->addAtomVoid();
            $void->rank = 0;
            $void->ws->closing = '';
            $void->fullcode = '...';
            $void->variadic = self::VARIADIC;
            $void->token    = 'T_ELLIPSIS';
            $this->addLink($arguments, $void, 'ARGUMENT');
            $arguments->ws->separators[] = '';

            $arguments->code     = $this->tokens[$current][1];
            $arguments->fullcode = $this->tokens[$current + 1][1]; // the ...
            $arguments->token    = $this->getToken($this->tokens[$current][0]);
            $arguments->args_max = 0;
            $arguments->args_min = 0;
            $arguments->count    = 0;
            $argumentsId[]       = $void;
            // $argument->ws is default value

            $argumentsList = array($void);
            $this->runPlugins($arguments, $argumentsList);

            $this->moveToNext(); // skip ...
            $this->moveToNext(); // skip ...

            $this->contexts->exitContext(Context::CONTEXT_NOSEQUENCE);
            $this->contexts->exitContext(Context::CONTEXT_NEW);

            return $arguments;
        }

        // Normal case, with some arguments
        $index      = 0;
        $argsMax    = 0;
        $argsMin    = 0;
        $rank       = -1;
        $rankName  = '';
        $argumentsList  = array();

        while (!$this->nextIs($finals)) {
            $initialId = $this->id;
            ++$argsMax;

            // named parameters PHP 8.0
            // based only on id + 2 == T_COLON
            if ($this->nextIs(array($this->phptokens::T_COLON ), 2)) {
                $this->moveToNext();
                $rankName = $this->tokens[$this->id][1];
                $this->moveToNext(); // skip :
            }

            while (!$this->nextIs(array($this->phptokens::T_COMMA,
                                        $this->phptokens::T_CLOSE_PARENTHESIS,
                                        $this->phptokens::T_CLOSE_CURLY,
                                        $this->phptokens::T_SEMICOLON,
                                        $this->phptokens::T_CLOSE_BRACKET,
                                        $this->phptokens::T_CLOSE_TAG,
                                        $this->phptokens::T_COLON,
                                        ))) {
                $index = $this->processNext();
                $this->checkPhpdoc();
            }
            $this->popExpression();
            if (!empty($rankName)) {
                $index->rankName = '$' . $rankName;
            }

            $this->checkPhpdoc();
            while ($this->nextIs(array($this->phptokens::T_COMMA))) {
                if ($index === 0) {
                    $index = $this->addAtomVoid();
                    $index->rank = 0;
                    $index->ws->opening = '';
                    $index->ws->closing = '';
                }

                $index->rank = ++$rank;

                $this->addLink($arguments, $index, 'ARGUMENT');
                $argumentsId[] = $index;
                // array($this, 'b'); for Callback syntax.
                if ($index->atom === 'Variable' &&
                    $index->code === '$this'    &&
                    $index->rank === 0 ) {
                    $this->calls->addCall(Calls::A_CLASS, $this->currentClassTrait->getCurrent()->fullnspath, $index);
                }

                $fullcode[] = (empty($index->rankName) ? '' : trim($index->rankName, '$') . ' : ' ) . $index->fullcode;
                $arguments->ws->separators[] = $this->tokens[$this->id + 1][1] . $this->tokens[$this->id + 1][4];
                $argumentsList[] = $index;

                $this->moveToNext(); // Skipping the comma ,
                $this->checkPhpdoc();
                $index = 0;
            }

            if ($initialId === $this->id) {
                throw new NoFileToProcess($this->filename, ' not processable with the current code, on line ' . $this->tokens[$this->id][2]);
            }
        }

        if ($index === 0) {
            if ($atom === 'List') {
                $index = $this->addAtomVoid();
                $index->ws->opening = '';
                $index->ws->closing = '';

                $index->rank = ++$rank;
                $argumentsId[] = $index;
                $this->argumentsId = $argumentsId; // This avoid overwriting when nesting functioncall
                if ($this->tokens[$this->id + 1][1] === ')') {
                    $arguments->ws->separators[] = '';
                } else {
                    $arguments->ws->separators[] = $this->tokens[$this->id + 1][1] . $this->tokens[$this->id + 1][4];
                }

                $this->addLink($arguments, $index, 'ARGUMENT');

                $fullcode[] = (empty($index->rankName) ? '' : trim($index->rankName, '$') . ' : ' ) . $index->fullcode;
                $argumentsList[] = $index;
            } else {
                $fullcode[] = ' ';
            }
        } else {
            $index->rank = ++$rank;
            $argumentsId[] = $index;
            $this->argumentsId = $argumentsId; // This avoid overwriting when nesting functioncall
            $arguments->ws->separators[] = '';

            $this->makePhpdoc($index);

            $this->addLink($arguments, $index, 'ARGUMENT');

            $fullcode[] = (empty($index->rankName) ? '' : trim($index->rankName, '$') . ' : ' ) . $index->fullcode;
            $argumentsList[] = $index;
        }

        // Skip the )
        $this->moveToNext();

        $arguments->fullcode = implode(', ', $fullcode);
        $arguments->token    = 'T_COMMA';
        $arguments->count    = $rank + 1;
        $arguments->args_max = $argsMax;
        $arguments->args_min = $argsMin;
        $this->runPlugins($arguments, $argumentsList);

        $this->contexts->exitContext(Context::CONTEXT_NOSEQUENCE);
        $this->contexts->exitContext(Context::CONTEXT_NEW);

        return $arguments;
    }

    private function processNextAsIdentifier(bool $getFullnspath = self::WITH_FULLNSPATH): AtomInterface {
        $this->moveToNext();

        $identifier = $this->addAtom($getFullnspath === self::WITH_FULLNSPATH ? 'Identifier' : 'Name', $this->id);
        $identifier->fullcode    = $this->tokens[$this->id][1];
        $identifier->ws->opening = $this->tokens[$this->id][1];

        if ($getFullnspath === self::WITH_FULLNSPATH) {
            $this->getFullnspath($identifier, 'const', $identifier);
        }
        $this->runPlugins($identifier);

        return $identifier;
    }

    private function guessType(AtomInterface $atom): ?Atominterface {
        switch ($atom->atom) {
            case 'Integer' :
            case 'Addition' :
            case 'Multiplication' :
            case 'Power' :
            case 'Bitshift' :
            case 'Bitoperation':
            case 'Sign':
            case 'Spaceship':
                $type = $this->addAtom('Scalartypehint');
                $type->fullnspath = '\\int';
                $type->fullcode   = 'int';
                $type->code       = 'int';
                break;

            case 'Float' :
                $type = $this->addAtom('Scalartypehint');
                $type->fullnspath = '\\float';
                $type->fullcode = 'float';
                $type->code     = 'float';
                break;

            case 'String' :
            case 'Concatenation' :
            case 'Heredoc' :
            case 'Magicconstant' :
                $type = $this->addAtom('Scalartypehint');
                $type->fullnspath = '\\string';
                $type->fullcode = 'string';
                $type->code     = 'string';
                break;

            case 'Null' :
                $type = $this->addAtom('Scalartypehint');
                $type->fullnspath = '\\null';
                $type->fullcode = 'null';
                $type->code     = 'null';
                break;

            case 'Boolean' :
            case 'Logical' :
            case 'Comparison' :
            case 'Not' :
                $type = $this->addAtom('Scalartypehint');
                $type->fullnspath = '\\bool';
                $type->fullcode = 'bool';
                $type->code 	= 'bool';
                break;

            case 'Arrayliteral' :
                $type = $this->addAtom('Scalartypehint');
                $type->fullnspath = '\\array';
                $type->fullcode = 'array';
                $type->code 	= 'array';
                break;

            case 'New' :
                // The NEXT id is necessarly the newcallname.
                $type = $this->addAtom($this->atoms[$atom->id + 1]->token === 'T_STRING' ? 'Identifier' : 'Nsname');
                $type->fullnspath = $this->atoms[$atom->id + 1]->fullnspath;
                $type->fullcode = $this->atoms[$atom->id + 1]->fullcode;
                $type->code 	= 'array';
                break;

            case 'Identifier' :
            case 'Nsname' :
            case 'Ternary' :
            case 'Coalesce' :
            case 'Staticconstant' :
            case 'Staticclass' :
            case 'Parenthesis':
            case 'Array':
            case 'Member':
                // in case of doubt, skip it.
                $type = null;
                break;

            default:
                throw new LoadError("No guess type for {$atom->atom}\n");
        }

        return $type;
    }

    private function processConst(): AtomInterface {
        $current = $this->id;
        $const = $this->addAtom('Const', $current);
        $const->ws->opening = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];
        $this->makePhpdoc($const);
        $this->makeAttributes($const);

        // This works because types can only be one token.
        // instantiation are not supported (yet 8.3)
        if ($this->nextIs(array($this->phptokens::T_EQUAL), 2)) {
            $returnTypes = '';
            $const->typehint = 'none';
        } else {
            $returnTypes = $this->processTypehint($const);
        }

        $rank = -1;
        --$this->id; // back one step for the init in the next loop

        if (empty($const->visibility) || $const->visibility === 'none') {
            $const->visibility = 'none';
            $const->ws->visibility = '';
        }

        $fullcode = array();
        do {
            $this->moveToNext();
            $this->checkPhpdoc();
            $name = $this->processNextAsIdentifier();

            $this->moveToNext(); // Skip =
            $def = $this->addAtom('Constant', $this->id);
            $def->ws->operator = '=' . $this->tokens[$this->id][4];
            $value = $this->processExpression(array($this->phptokens::T_SEMICOLON,
                                                    $this->phptokens::T_COMMA,
                                                    $this->phptokens::T_DOC_COMMENT,
                                                    $this->phptokens::T_CLOSE_TAG,
                                                    ));

            $this->addLink($def, $name, 'NAME');
            $this->addLink($def, $value, 'VALUE');

            $type = $this->guessType($value);
            if (!empty($type)) {
                $this->addLink($def, $type, 'TYPEHINT');
                $def->typehint = 'one';
            }

            $def->fullcode = $name->fullcode . ' = ' . $value->fullcode;
            $def->rank     = ++$rank;

            $fullcode[] = $def->fullcode;
            $this->runPlugins($def, array('VALUE' => $value,
                                          'NAME'  => $name,
                                          ));

            $this->getFullnspath($name, 'const', $name);

            $this->addLink($const, $def, 'CONST');

            if ($this->contexts->isContext(Context::CONTEXT_CLASS)) {
                $this->calls->addDefinition(Calls::STATICCONSTANT,   $this->currentClassTrait->getCurrent()->fullnspath . '::' . $name->fullcode, $def);
            } else {
                $this->calls->addDefinition(Calls::CONST, $name->fullnspath, $def);
            }
            $this->makePhpdoc($def);
            $this->checkPhpdoc();

            if ($this->nextIs(array($this->phptokens::T_COMMA))) {
                $const->ws->separators[] = ',' . $this->tokens[$this->id + 1][4];
            }
        } while (!$this->nextIs(array($this->phptokens::T_SEMICOLON,
                                      $this->phptokens::T_CLOSE_TAG,
                                      )));
        $const->ws->separators[] = '';

        $const->fullcode = $this->tokens[$current][1] . ' ' .
                           ($returnTypes === '' ? '' : $returnTypes) .
                           implode(', ', $fullcode);
        $const->count    = $rank + 1;

        $this->pushExpression($const);
        if ($this->nextIs(array($this->phptokens::T_CLOSE_TAG))) {
            $this->processSemicolon();
        }

        return $const;
    }

    private function processAbstract(): AtomInterface {
        $current = $this->id;
        $abstract = $this->tokens[$this->id][1];

        if ($this->nextIs(array($this->phptokens::T_STATIC))) {
            $next = $this->processStaticDefinition();
        } else {
            $next = $this->processNext();
        }

        $next->abstract = self::ABSTRACT;
        $next->ws->abstract = $this->tokens[$current][1] . $this->tokens[$current][4];
        $next->fullcode = "$abstract $next->fullcode";
        $this->makePhpdoc($next);

        return $next;
    }

    private function processReadonly(bool $promoted = self::PROMOTED_NOT, bool $citBody = false): AtomInterface {
        $current = $this->id;
        $readonly = $this->tokens[$this->id][1];

        if ($this->nextIs(array($this->phptokens::T_PRIVATE,
                                $this->phptokens::T_PROTECTED,
                                $this->phptokens::T_PUBLIC,
                               ))) {
            $this->moveToNext();
            $next = $this->processPpp($promoted);
            $this->makePhpdoc($next);
            $next->fullcode = "$readonly $next->fullcode";
            $next->ws->readonly = $this->tokens[$current][1] . $this->tokens[$current][4];
        } elseif ($this->nextIs(array($this->phptokens::T_FINAL,
                                      $this->phptokens::T_ABSTRACT,
                                      ))) {
            $next = $this->processNext();
            $this->makePhpdoc($next);
            $next->fullcode = "$readonly $next->fullcode";
            $next->ws->readonly = $this->tokens[$current][1] . $this->tokens[$current][4];
        } elseif ($this->nextIs(array($this->phptokens::T_CLASS))) {
            $next = $this->processNext();
            $this->makePhpdoc($next);
            $next->fullcode = "$readonly $next->fullcode";
            $next->ws->readonly = $this->tokens[$current][1] . $this->tokens[$current][4];
        } elseif ($this->nextIs(array($this->phptokens::T_OPEN_PARENTHESIS)) &&
                  $citBody === false) {
            --$this->id;
            $next = $this->processNextAsIdentifier();
            $this->pushExpression($next);
            return $this->processFCOA($next);
        } else {
            // next is variables or typehints
            $next = $this->addAtom('Ppp', $current);
            $this->makePhpdoc($next);
            $returnTypes = $this->processTypehint($next);

            $variable = $this->processSGVariable($next, $promoted);
            $this->makeAttributes($variable);
            $next->ws->opening = '';

            $next->ws->readonly = $this->tokens[$current][1] . $this->tokens[$current][4];
            $next->fullcode = "$readonly $returnTypes $next->fullcode";
            $this->makePhpdoc($next);
        }
        $next->visibility = 'none';

        $next->readonly = self::READONLY;

        return $next;
    }

    private function processFinal(): AtomInterface {
        $current = $this->id;
        $final = $this->tokens[$this->id][1];
        $this->checkPhpdoc();

        if ($this->nextIs(array($this->phptokens::T_STATIC))) {
            $next = $this->processStaticDefinition();
        } else {
            $next = $this->processNext();
        }

        $next->final     = self::FINAL;
        $next->ws->final = $this->tokens[$current][1] . $this->tokens[$current][4];
        $next->fullcode  = "$final $next->fullcode";
        $this->makePhpdoc($next);

        return $next;
    }

    private function processVar(): AtomInterface {
        $current = $this->id;
        $visibility = $this->tokens[$this->id][1];
        $ppp = $this->addAtom('Ppp', $current);
        $returnTypes = $this->processTypehint($ppp);

        $this->processSGVariable($ppp);

        $ppp->visibility = 'none';
        $ppp->fullcode   = "$visibility {$returnTypes}$ppp->fullcode";
        $this->makePhpdoc($ppp);

        return $ppp;
    }

    private function processPPP(bool $promoted = self::PROMOTED_NOT): AtomInterface {
        $current = $this->id;
        $visibility = $this->tokens[$this->id][1];
        $this->checkPhpdoc();

        if ($this->nextIs(array($this->phptokens::T_FINAL,
                                $this->phptokens::T_ABSTRACT,
                                ))) {
            $ppp = $this->processNext();

            $this->makePhpdoc($ppp);
            $this->makeAttributes($ppp);
            $returnTypes = '';
        } elseif ($this->nextIs(array($this->phptokens::T_STATIC))) {
            $ppp = $this->processStaticDefinition();

            $this->makePhpdoc($ppp);
            $this->makeAttributes($ppp);
            $returnTypes = '';
        } elseif ( $this->nextIs(array($this->phptokens::T_READONLY) )) {
            $this->moveToNext();
            $ppp = $this->processReadonly($promoted, true);

            $this->makeAttributes($ppp);
            $this->makePhpdoc($ppp);
            $returnTypes = '';
        } elseif ($this->nextIs(array($this->phptokens::T_FUNCTION,
                                      $this->phptokens::T_CONST))) {
            $ppp = $this->processNext();
            $this->makePhpdoc($ppp);
            $returnTypes = '';
        } else { // visibility or typehints
            $ppp = $this->addAtom('Ppp', $current);
            $this->makePhpdoc($ppp);
            $this->makeAttributes($ppp);
            $returnTypes = $this->processTypehint($ppp);

            $this->processSGVariable($ppp, $promoted);
            $ppp->ws->opening = '';
        }

        $ppp->visibility = strtolower($visibility);
        $ppp->ws->visibility = $visibility . $this->tokens[$current][4];
        $ppp->fullcode   = "$visibility {$returnTypes}$ppp->fullcode";

        return $ppp;
    }

    private function processDefineConstant(AtomInterface $namecall): AtomInterface {
        $namecall->atom = 'Defineconstant';
        $namecall->fullnspath = '\\define';
        $namecall->ws->opening = $this->tokens[$this->id - 1][1] . $this->tokens[$this->id - 1][4] .
                                 $this->tokens[$this->id][1] . $this->tokens[$this->id][4];
        $this->makePhpdoc($namecall);

        // Empty call
        if ($this->nextIs(array($this->phptokens::T_CLOSE_PARENTHESIS))) {
            $namecall->fullcode   = $namecall->code . '( )';
            $this->pushExpression($namecall);

            $this->runPlugins($namecall, array());
            $this->moveToNext(); // Skip )
            $namecall->ws->closing = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];

            $this->checkExpression();
            return $namecall;
        }

        // First argument : constant name
        $this->moveToNext();
        if ($this->nextIs(array($this->phptokens::T_CONSTANT_ENCAPSED_STRING), 0) &&
            $this->nextIs(array($this->phptokens::T_COMMA))
        ) {
            $name = $this->processSingle('Identifier');
            $name->ws->opening = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];
            $namecall->ws->separators[] = $this->tokens[$this->id + 1][1] . $this->tokens[$this->id + 1][4];

            $this->runPlugins($name);
            $name->delimiter   = $name->code[0];
            if (strtolower($name->delimiter) === 'b') {
                $name->binaryString = $name->delimiter;
                $name->delimiter    = $name->code[1];
                $name->noDelimiter  = substr($name->code, 2, -1);
            } else {
                $name->noDelimiter = substr($name->code, 1, -1);
            }
            $this->getFullnspath($name, 'const', $name);

            $this->calls->addDefinition(Calls::CONST, $name->fullnspath, $namecall);

            $this->runPlugins($name, array());
            if ($this->nextIs(array($this->phptokens::T_OPEN_BRACKET))) {
                $name = $this->processBracket();
            }
        } else {
            // back one step
            --$this->id;
            $name = $this->processExpression(array($this->phptokens::T_COMMA,
                                                   $this->phptokens::T_CLOSE_PARENTHESIS // In case of missing arguments...
                                                   ));
            $namecall->ws->separators[] = $this->tokens[$this->id + 1][1] . $this->tokens[$this->id + 1][4];
        }
        $this->addLink($namecall, $name, 'NAME');

        if ($this->nextIs(array($this->phptokens::T_CLOSE_PARENTHESIS))) {
            $namecall->fullcode   = "{$namecall->code}({$name->code})";
            $this->pushExpression($namecall);

            $this->runPlugins($namecall, array('NAME'  => $name, ));
            $this->moveToNext(); // Skip )
            $namecall->ws->closing = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];

            $this->checkExpression();
            return $namecall;
        }

        // Second argument constant value
        $this->moveToNext(); // Skip ,
        $value = $this->processExpression(array($this->phptokens::T_COMMA,
                                                $this->phptokens::T_CLOSE_PARENTHESIS // In case of missing arguments...
                                               ));
        $this->addLink($namecall, $value, 'VALUE');

        // Most common point of exit
        if ($this->nextIs(array($this->phptokens::T_CLOSE_PARENTHESIS))) {
            $namecall->fullcode   = "{$namecall->code}({$name->fullcode}, {$value->fullcode})";
            $this->pushExpression($namecall);

            $this->runPlugins($namecall, array('NAME'  => $name,
                                               'VALUE' => $value,
                                               ));
            $this->moveToNext(); // Skip )
            $namecall->ws->closing = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];

            $this->processDefineAsConstants($namecall, $name, self::CASE_INSENSITIVE);

            $this->checkExpression();

            return $namecall;
        }

        // Third argument : case sensitive
        $this->moveToNext(); // Skip ,
        $namecall->ws->separators[] = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];
        $case = $this->processExpression(array($this->phptokens::T_COMMA,
                                               $this->phptokens::T_CLOSE_PARENTHESIS // In case of missing arguments...
                                              ));
        $this->addLink($namecall, $case, 'CASE');

        $this->processDefineAsConstants($namecall, $name, (bool) $case->boolean);

        $namecall->fullcode   = $namecall->code . '(' . $name->fullcode . ', ' . $value->fullcode . ', ' . $case->fullcode . ')';
        $this->pushExpression($namecall);

        $this->runPlugins($namecall, array('NAME'  => $name,
                                           'VALUE' => $value,
                                           'CASE'  => $case,
                                           ));

        if ($this->nextIs(array($this->phptokens::T_CLOSE_PARENTHESIS))) {
            $this->moveToNext(); // Skip )
            $namecall->ws->closing = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];

            $this->checkExpression();
            return $namecall;
        }

        // Ignore everything else
        $parenthese = 1;
        while ($parenthese > 0) {
            $this->moveToNext();

            if ($this->nextIs(array($this->phptokens::T_CLOSE_PARENTHESIS), 0)) {
                --$parenthese;
            } elseif ($this->nextIs(array($this->phptokens::T_OPEN_PARENTHESIS), 0)) {
                ++$parenthese;
            }
        }

        $namecall->ws->closing = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];
        $this->checkExpression();
        return $namecall;
    }

    private function processFunctioncall(bool $getFullnspath = self::WITH_FULLNSPATH): AtomInterface {
        $name = $this->popExpression();
        $this->moveToNext(); // Skipping the name, set on (
        $current = $this->id;

        if ($this->contexts->isContext(Context::CONTEXT_NEW)) {
            if ($this->nextIs(array($this->phptokens::T_DOUBLE_COLON))) {
                $atom = 'Identifier';
            } else {
                $atom = 'Newcall';
            }
        } elseif ($getFullnspath === self::WITH_FULLNSPATH) {
            if (strtolower($name->code) === '\\define') {
                return $this->processDefineConstant($name);
            } elseif (strtolower($name->code) === 'define') {
                return $this->processDefineConstant($name);
            } elseif (strtolower($name->code) === '\\class_alias') {
                $atom = 'Classalias';
            } elseif (strtolower($name->code) === 'class_alias') {
                $atom = 'Classalias';
            } elseif ($name->fullnspath === '\\list') {
                $atom = 'List';
            } elseif ($this->nextIs(array($this->phptokens::T_ELLIPSIS)) &&
                      $this->nextIs(array($this->phptokens::T_CLOSE_PARENTHESIS), 2)
            ) {
                $atom = 'Callable';
            } else {
                $atom = 'Functioncall';
            }
        } else {
            if ($this->nextIs(array($this->phptokens::T_ELLIPSIS)) &&
                $this->nextIs(array($this->phptokens::T_CLOSE_PARENTHESIS), 2)
            ) {
                $atom = 'Callable';
            } else {
                $atom = 'Methodcallname';
            }
        }

        $argumentsList = array();
        $functioncall = $this->processArguments($atom, array($this->phptokens::T_CLOSE_PARENTHESIS), $argumentsList);
        $this->makePhpdoc($functioncall);
        $argumentsFullcode           = $functioncall->fullcode;
        $functioncall->ws->opening  = '';
        $functioncall->ws->toargs   = '(' . $this->tokens[$current][4];
        $functioncall->ws->closing  = ')' . $this->tokens[$this->id][4];

        $functioncall->code      = $name->code;
        $functioncall->fullcode  = "{$name->fullcode}({$argumentsFullcode})";
        $functioncall->token     = $name->token;

        if ($atom === 'Newcall') {
            $this->getFullnspath($name, 'class', $functioncall);

            $this->calls->addCall(Calls::A_CLASS, $functioncall->fullnspath, $functioncall);
        } elseif ($atom === 'Classalias') {
            $functioncall->fullnspath = '\\classalias';

            $this->processDefineAsClassalias($argumentsList);
        } elseif (in_array($atom, array('Methodcallname', 'List', 'Closure'), STRICT_COMPARISON)) {
            // literally, nothing
        } elseif (in_array(mb_strtolower($name->code), array('defined', 'constant'), STRICT_COMPARISON)) {
            if ($argumentsList[0]->constant === self::CONSTANT_EXPRESSION &&
                !empty($argumentsList[0]->noDelimiter)) {
                $fullnspath = makeFullNsPath($argumentsList[0]->noDelimiter, \FNP_CONSTANT);
                if ($argumentsList[0]->noDelimiter[0] === '\\') {
                    $fullnspath = "\\$fullnspath";
                }
                $argumentsList[0]->fullnspath = $fullnspath;
                $this->calls->addCall(!str_contains($fullnspath, '::') ? 'const' : 'staticconstant', $fullnspath, $argumentsList[0]);
            }

            $functioncall->fullnspath = '\\' . mb_strtolower($name->code);
        } elseif ($atom === 'Callable') { // A first class callable
            $this->getFullnspath($name, 'function', $functioncall);
            $functioncall->absolute   = $name->absolute;

            $this->calls->addCall(Calls::FUNCTION, $functioncall->fullnspath, $functioncall);
        } elseif ($getFullnspath === self::WITH_FULLNSPATH) { // A functioncall
            $this->getFullnspath($name, 'function', $functioncall);
            $functioncall->absolute   = $name->absolute;

            $this->calls->addCall(Calls::FUNCTION, $functioncall->fullnspath, $functioncall);
        } else {
            throw new LoadError("Unprocessed atom in functioncall definition (its name) : $atom : $this->filename : " . __LINE__);
        }

        $this->addLink($functioncall, $name, 'NAME');
        if ($name->atom === 'Name') {
            $this->runPlugins($name);
        }
        $this->pushExpression($functioncall);
        $this->checkPhpdoc();

        if ($functioncall->atom === 'Methodcallname') {
            $argumentsList[] = $name;
            $this->runPlugins($functioncall, $argumentsList);
        } elseif ( !$this->contexts->isContext(Context::CONTEXT_NOSEQUENCE) &&
                   $this->nextIs(array($this->phptokens::T_CLOSE_TAG))      &&
                   $getFullnspath === self::WITH_FULLNSPATH ) {
            $this->processSemicolon();
        } elseif ($functioncall->atom === 'Callable' &&
                  $getFullnspath === self::WITHOUT_FULLNSPATH) {
            // Nothing
        } else {
            $argumentsList[] = $name;
            $this->runPlugins($functioncall, $argumentsList);
            $functioncall = $this->processFCOA($functioncall);
        }

        return $functioncall;
    }

    private function processString(): AtomInterface {
        if ($this->nextIs(array($this->phptokens::T_NS_SEPARATOR))) {
            $nsname = $this->processNsname();
            $this->runPlugins($nsname);

            return $this->processFCOA($nsname);
        } elseif ($this->nextIs(array($this->phptokens::T_NAME_QUALIFIED,
                                      $this->phptokens::T_NAME_RELATIVE,
                                      $this->phptokens::T_NAME_FULLY_QUALIFIED), 0)) {
            $nsname = $this->processNsname();

            return $nsname;
        } elseif ($this->nextIs(array($this->phptokens::T_SEMICOLON,
                                      $this->phptokens::T_OPEN_CURLY,
                                      $this->phptokens::T_CLOSE_CURLY,
                                      $this->phptokens::T_COLON,
                                      $this->phptokens::T_OPEN_TAG,
                                      $this->phptokens::T_DOC_COMMENT,
                                      ), -1) &&
                   $this->nextIs(array($this->phptokens::T_COLON       ))) {
            return $this->processColon();
        } elseif (mb_strtolower($this->tokens[$this->id][1]) === 'self') {
            $string = $this->addAtom('Self', $this->id);
        } elseif (mb_strtolower($this->tokens[$this->id][1]) === 'parent') {
            $string = $this->addAtom('Parent', $this->id);
        } elseif (mb_strtolower($this->tokens[$this->id][1]) === 'list') {
            $string = $this->addAtom('Name', $this->id);
            $string->fullnspath = '\\list';
        } elseif ($this->contexts->isContext(Context::CONTEXT_NEW)) {
            // This catchs new A and new A()
            $string = $this->addAtom('Newcallname', $this->id);
            $this->runPlugins($string);
        } elseif ($this->nextIs(array($this->phptokens::T_OPEN_PARENTHESIS ))) {
            $string = $this->addAtom('Name', $this->id);
        } elseif (in_array(mb_strtolower($this->tokens[$this->id][1]), array('true', 'false'), STRICT_COMPARISON)) {
            $string = $this->addAtom('Boolean', $this->id);

            $string->noDelimiter = mb_strtolower($string->code) === 'true' ? '1' : '';
            $string->fullnspath = '\\' . mb_strtolower($string->code);
        } elseif (mb_strtolower($this->tokens[$this->id][1]) === 'null') {
            $string = $this->addAtom('Null', $this->id);
            $string->fullnspath = '\\null';
        } else {
            $string = $this->addAtom('Identifier', $this->id);
            $string->ws->opening = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];
            $string->ws->closing = '';
        }

        $string->fullcode   = $this->tokens[$this->id][1];
        $string->absolute   = self::NOT_ABSOLUTE;

        $this->pushExpression($string);

        if ($string->isA(array('Parent', 'Self', 'Static', 'Newcall', 'Newcallname'))) {
            $this->getFullnspath($string, 'class', $string);
            $this->calls->addCall(Calls::A_CLASS, $string->fullnspath, $string);

            if ($this->contexts->isContext(Context::CONTEXT_NEW)) {
                $string->count = 0;
            }
        } elseif ($this->nextIs(array($this->phptokens::T_DOUBLE_COLON))) {
            $this->getFullnspath($string, 'class', $string);
            if ($this->currentClassTrait->getCurrent() !== null && $this->currentClassTrait->getCurrent()->fullnspath === $string->fullnspath) {
                $this->calls->addCall(Calls::A_CLASS, $string->fullnspath, $string);
            }
        } elseif ($this->nextIs(array($this->phptokens::T_INSTANCEOF), -1)   ||
                  $this->nextIs(array($this->phptokens::T_NEW), -1)
        ) {
            if (!$this->nextIs(array($this->phptokens::T_OPEN_PARENTHESIS))) {
                $this->calls->addCall(Calls::A_CLASS, $string->fullnspath, $string);
            }
        } elseif ($this->nextIs(array($this->phptokens::T_OPEN_PARENTHESIS))) {
            // Nothing to do
        } else {
            $this->calls->addCall(Calls::CONST, $string->fullnspath, $string);
            $this->getFullnspath($string, 'const', $string);
            if (isset($this->config->constants[$string->fullnspath])) {
                $string->noDelimiter = $this->config->constants[$string->fullnspath];
            }
        }

        $this->runPlugins($string);

        if ( !$this->contexts->isContext(Context::CONTEXT_NOSEQUENCE) && $this->nextIs(array($this->phptokens::T_CLOSE_TAG))) {
            $this->processSemicolon();
        } else {
            $string = $this->processFCOA($string);
        }

        return $string;
    }

    private function processPostPlusplus(AtomInterface $previous): AtomInterface {
        $this->moveToNext();
        $current = $this->id;
        $this->popExpression();
        $plusplus = $this->addAtom('Postplusplus', $this->id);

        $this->addLink($plusplus, $previous, 'POSTPLUSPLUS');

        $plusplus->fullcode = $previous->fullcode . $this->tokens[$this->id][1];
        $plusplus->ws->opening = $this->tokens[$current][1] . $this->tokens[$current][4];

        $this->pushExpression($plusplus);
        $this->runPlugins($plusplus, array('POSTPLUSPLUS' => $previous));

        $this->checkExpression();

        return $plusplus;
    }

    private function processPrePlusplus(): AtomInterface {
        $current = $this->id;

        $operator = $this->addAtom('Preplusplus', $this->id);
        $this->processSingleOperator($operator, $this->precedence->get($this->tokens[$this->id][0]), 'PREPLUSPLUS');
        $operator = $this->popExpression();
        $operator->ws->opening = $this->tokens[$current][1] . $this->tokens[$current][4];
        $this->pushExpression($operator);

        $this->checkExpression();

        return $operator;
    }

    private function processStaticDefinition(): AtomInterface {
        $this->moveToNext(); // skip static
        $this->checkPhpdoc();
        $current = $this->id;

        // static public function () {}
        if ($this->nextIs(array($this->phptokens::T_PRIVATE,
                                $this->phptokens::T_PUBLIC,
                                $this->phptokens::T_PROTECTED,
                                $this->phptokens::T_FINAL,
                                $this->phptokens::T_ABSTRACT,
                                ))) {
            $next = $this->processNext();
            $next->ws->opening = '';

            $next->static      = self::STATIC;
            $next->ws->static  = $this->tokens[$current][1] . $this->tokens[$current][4];
            $next->ws->opening = '';
            $next->code	      = $this->tokens[$current][1] . " $next->fullcode";
            $next->fullcode    = $next->code;

            return $next;
        }

        // static ?A $a = 1; (static property declaration)
        if ($this->nextIs(array($this->phptokens::T_NS_SEPARATOR,
                                $this->phptokens::T_QUESTION,
                                $this->phptokens::T_STRING,
                                $this->phptokens::T_NAMESPACE,
                                $this->phptokens::T_ARRAY,
                                $this->phptokens::T_CALLABLE,
                                $this->phptokens::T_NAME_QUALIFIED,
                                $this->phptokens::T_NAME_RELATIVE,
                                $this->phptokens::T_NAME_FULLY_QUALIFIED,
                                $this->phptokens::T_OPEN_PARENTHESIS,
                                ))) {
            $current = $this->id;
            $option = $this->tokens[$this->id][1];

            $ppp = $this->addAtom('Ppp', $current);
            $returnTypes = $this->processTypehint($ppp);

            $this->processSGVariable($ppp);
            $ppp->ws->opening = '';

            $ppp->static = self::STATIC;
            $ppp->ws->static = $this->tokens[$current][1] . $this->tokens[$current][4];
            $ppp->visibility = 'none';
            $ppp->code		 = "$option {$returnTypes}$ppp->fullcode";
            $ppp->fullcode   = $ppp->code;
            $this->makePhpdoc($ppp);

            return $ppp;
        }

        if ($this->nextIs(array($this->phptokens::T_VARIABLE))) {
            if ($this->contexts->isContext(Context::CONTEXT_FUNCTION)) {
                $ppp = $this->processStaticVariable();

                $void = $this->addAtomVoid();
                $void->rank = 0;
                $this->addLink($ppp, $void, 'TYPEHINT');
            } else {
                // something like public static
                $option = $this->tokens[$this->id][1];

                $ppp = $this->addAtom('Ppp', $current);
                $ppp->typehint = 'one';
                $this->processSGVariable($ppp);
                $ppp->ws->opening = '';

                $void = $this->addAtomVoid();
                $this->addLink($ppp, $void, 'TYPEHINT');
                $void->rank = 0;

                if (empty($ppp->visibility)) {
                    $ppp->visibility = 'none';
                }
                $this->popExpression();

                $ppp->static = self::STATIC;
                $ppp->ws->static = $this->tokens[$current][1] . $this->tokens[$current][4];
                $ppp->ws->opening = '';
                $ppp->code	   = "$option $ppp->fullcode";
                $ppp->fullcode = $ppp->code;
            }
            return $ppp;
        }

        // static methods only
        if ($this->nextIs(array($this->phptokens::T_FUNCTION,
                                ))) {
            $static = $this->tokens[$this->id][1];

            $this->currentClassTrait->toggleThisContext();
            $ppp = $this->processNext();
            $this->currentClassTrait->toggleThisContext();

            $ppp->static   = self::STATIC;
            $ppp->ws->static = $this->tokens[$current][1] . $this->tokens[$current][4];

            $ppp->code     = "$static $ppp->fullcode";
            $ppp->fullcode = $ppp->code;
            $this->makePhpdoc($ppp);
            return $ppp;
        }

        assert(false, "Could not process static Definition in $this->filename");
    }

    private function processStatic(): AtomInterface {
        $this->checkPhpdoc();
        $current = $this->id;

        if ($this->nextIs(array($this->phptokens::T_DOUBLE_COLON))   ||
            $this->nextIs(array($this->phptokens::T_INSTANCEOF), -1) ) {
            $identifier = $this->processSingle('Static');
            $identifier->ws->opening = $this->tokens[$current][1] . $this->tokens[$current][4];
            $this->pushExpression($identifier);
            $this->getFullnspath($identifier, 'class', $identifier);
            $this->calls->addCall(Calls::A_CLASS, $identifier->fullnspath, $identifier);

            return $identifier;
        }

        // static at the end of an expression
        if ($this->nextIs(array_merge(array($this->phptokens::T_OPEN_PARENTHESIS,
                                            $this->phptokens::T_PLUS,  // return new static + new A:
                                            $this->phptokens::T_MINUS,
                                            ),
            $this->END_OF_EXPRESSION))
        ) {
            $name = $this->addAtom('Static', $this->id);
            $name->fullcode   = $this->tokens[$this->id][1];

            $this->getFullnspath($name, 'class', $name);
            $name->ws->opening = $this->tokens[$current][1] . $this->tokens[$current][4];

            if ($this->contexts->isContext(Context::CONTEXT_NEW)) {
                $this->getFullnspath($name, 'class', $name);

                $this->calls->addCall(Calls::A_CLASS, $name->fullnspath, $name);
            }

            $this->pushExpression($name);

            return $this->processFCOA($name);
        }

        if ($this->nextIs(array($this->phptokens::T_VARIABLE))) {
            if (!$this->contexts->isContext(Context::CONTEXT_CLASS)) {
                $ppp = $this->processStaticVariable();

                $void = $this->addAtomVoid();
                $void->rank = 0;
                $this->addLink($ppp, $void, 'TYPEHINT');

                return $ppp;
            }

            // something like public static
            $option = $this->tokens[$this->id][1];

            $ppp = $this->addAtom('Ppp', $current);
            $ppp->typehint = 'one';
            $this->processSGVariable($ppp);
            $ppp->ws->opening = '';

            $void = $this->addAtomVoid();
            $this->addLink($ppp, $void, 'TYPEHINT');
            $void->rank = 0;

            if (empty($ppp->visibility)) {
                $ppp->visibility = 'none';
            }
            $this->popExpression();

            $ppp->static = self::STATIC;
            $ppp->ws->static = $this->tokens[$current][1] . $this->tokens[$current][4];
            $ppp->ws->opening = '';
            $ppp->code	   = "$option $ppp->fullcode";
            $ppp->fullcode = $ppp->code;

            return $ppp;
        }

        // static closures and arrow functions
        if ($this->nextIs(array($this->phptokens::T_FUNCTION,
                                $this->phptokens::T_FN,
                                ))) {
            $static = $this->tokens[$this->id][1];

            $this->currentClassTrait->toggleThisContext();
            $ppp = $this->processNext();
            $this->currentClassTrait->toggleThisContext();

            $ppp->static   = self::STATIC;
            $ppp->ws->static = $this->tokens[$current][1] . $this->tokens[$current][4];

            $ppp->code     = "$static $ppp->fullcode";
            $ppp->fullcode = $ppp->code;
            $this->makePhpdoc($ppp);

            return $ppp;
        }

        assert(false, "Could not process static in $this->filename");
    }

    private function processSGVariable(AtomInterface $static, bool $promoted = self::PROMOTED_NOT): AtomInterface {
        $current = $this->id;
        $rank = -1;

        $this->makePhpdoc($static);
        if ($static->isA(array('Global', 'Static'))) {
            $fullcodePrefix = $this->tokens[$this->id][1];
            $link = strtoupper($static->atom);
            $atom = $static->atom . 'definition';
        } else {
            $fullcodePrefix= array();
            $link = 'PPP';
            $atom = 'Propertydefinition';

            if (!isset($static->visibility)) {
                $static->visibility = 'none';
            }
            $fullcodePrefix = implode(' ', $fullcodePrefix);
        }
        $static->code = $this->tokens[$this->id][1];
        $static->ws->opening = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];

        if (!isset($fullcodePrefix)) {
            $fullcodePrefix = $this->tokens[$current][1];
        }

        $finals = array($this->phptokens::T_SEMICOLON,
                        $this->phptokens::T_CLOSE_TAG,
                        $this->phptokens::T_CLOSE_PARENTHESIS,
                        );

        // This is only for promoted properties. Only one definition per PPP
        if ($promoted === self::PROMOTED) {
            $finals[] = $this->phptokens::T_COMMA;
        }

        $fullcode = array();
        $extras = array();
        --$this->id;
        do {
            $this->moveToNext();
            $this->checkPhpdoc();

            if ($this->nextIs(array($this->phptokens::T_AND,
                                   $this->phptokens::T_AMPERSAND_FOLLOWED_BY_VAR_OR_VARARG))) {
                $reference = self::REFERENCE;
                $this->moveToNext();
            } else {
                $reference = self::NOT_REFERENCE;
            }

            if ($this->nextIs(array($this->phptokens::T_VARIABLE))) {
                $this->moveToNext();

                $name = $this->addAtom('Name');
                $name->code     = $this->tokens[$this->id][1];
                $name->fullcode = $name->code;
                $name->token = 'T_VARIABLE';

                $element = $this->processSingle($atom);
                $this->addLink($element, $name, 'NAME');
                $this->makePhpdoc($element);

                if ($element->isA(array('Globaldefinition', 'Staticdefinition', 'Variabledefinition'))) {
                    if ($this->currentVariables->exists($element->code)) {
                        $definition = $this->currentVariables->get($this->tokens[$this->id][1]);
                    } else {
                        $definition = $this->addAtom('Variabledefinition');
                        $definition->code 	  = $this->tokens[$this->id][1];
                        $definition->fullcode = $definition->code;
                        $this->addLink($this->currentFunction->current(), $definition, 'DEFINITION');
                        $this->currentVariables->set($definition->code, $definition);
                    }

                    $this->addLink($definition, $element, 'DEFINITION');
                }

                if ($this->nextIs(array($this->phptokens::T_EQUAL))) {
                    $this->moveToNext();
                    $element->ws->operator = '=' . $this->tokens[$this->id][4];
                    $default = $this->processExpression(array($this->phptokens::T_SEMICOLON,
                                                              $this->phptokens::T_CLOSE_TAG,
                                                              $this->phptokens::T_COMMA,
                                                              $this->phptokens::T_CLOSE_PARENTHESIS,
                                                              $this->phptokens::T_DOC_COMMENT,
                                                              ));
                    $element->ws->closing = $this->tokens[$this->id][4];
                } else {
                    $element->ws->operator = '';
                    $default = $this->addAtomVoid();
                }
            } else {
                // global $a[2] = 2 ?
                $element = $this->processExpression(array($this->phptokens::T_SEMICOLON,
                                                         $this->phptokens::T_CLOSE_TAG,
                                                         $this->phptokens::T_COMMA,
                                                         $this->phptokens::T_DOC_COMMENT,
                                                         ));
                $this->makePhpdoc($element);
                $this->popExpression();
                $default = $this->addAtomVoid();
                $element->ws->operator = '';
            }

            if ($reference === self::REFERENCE) {
                $element->fullcode  = '&' . $element->fullcode;
                $element->reference = self::REFERENCE;
            }

            $element->rank = ++$rank;
            $this->addLink($static, $element, $link);

            if ($atom === 'Propertydefinition') {
                $this->makeAttributes($element);
                // drop $
                $element->propertyname = ltrim($element->code, '$');
                $this->currentProperties->addProperty($element->propertyname, $element);

                if ($this->currentClassTrait->getCurrent() !== ClassTraitContext::NO_CLASS_TRAIT_CONTEXT) {
                    $currentFNP = $this->currentClassTrait->getCurrent()->fullnspath;
                    $this->calls->addDefinition(Calls::STATICPROPERTY, $currentFNP . '::' . $element->code, $element);
                    $this->calls->addDefinition(Calls::PROPERTY, $currentFNP . '::' . ltrim($element->code, '$'), $element);
                }
            }

            $this->addLink($element, $default, 'DEFAULT');
            if ($default->atom === 'Void') {
                $this->runPlugins($element);
            } else {
                $element->fullcode .= " = {$default->fullcode}";
                $this->runPlugins($element, array('DEFAULT' => $default));
            }
            $fullcode[] = $element->fullcode;
            $extras[] = $element;
            $this->checkPhpdoc();
            if ($this->tokens[$this->id + 1][1] === ',') {
                $static->ws->separators[] = $this->tokens[$this->id + 1][1] . $this->tokens[$this->id + 1][4];
            }
        } while (!$this->nextIs($finals));
        $static->ws->separators[] = '';

        $static->fullcode = (!empty($fullcodePrefix) ? $fullcodePrefix . ' ' : '') . implode(', ', $fullcode);
        $static->count    = $rank + 1;
        $this->runPlugins($static, $extras);

        $this->pushExpression($static);

        $this->checkExpression();

        return $element;
    }

    private function processStaticVariable(): AtomInterface {
        $variable = $this->addAtom('Static');
        $this->processSGVariable($variable);

        return $variable;
    }

    private function processGlobalVariable(): AtomInterface {
        $variable = $this->addAtom('Global');
        $this->processSGVariable($variable);

        return $variable;
    }

    private function processBracket(): AtomInterface {
        $current = $this->id;
        $bracket = $this->addAtom('Array', $current + 1);

        $variable = $this->popExpression();
        $this->addLink($bracket, $variable, 'VARIABLE');

        // Skip opening bracket
        $opening = $this->tokens[$this->id + 1][0];
        if ($opening === '{') {
            $closing = '}';
        } else {
            $closing = ']';
        }
        $bracket->ws->opening = $this->tokens[$this->id + 1][1] . $this->tokens[$this->id + 1][4];

        $this->moveToNext();
        $resetContext = false;
        if ($this->contexts->isContext(Context::CONTEXT_NEW)) {
            $resetContext = true;
            $this->contexts->toggleContext(Context::CONTEXT_NEW);
        }
        $index = $this->processExpression(array($this->phptokens::T_CLOSE_BRACKET,
                                                $this->phptokens::T_CLOSE_CURLY,
                                                ));

        if ($resetContext === true) {
            $this->contexts->toggleContext(Context::CONTEXT_NEW);
        }

        // Skip closing bracket
        $this->moveToNext();
        $this->addLink($bracket, $index, 'INDEX');

        if ($variable->code === '$GLOBALS' && !empty($index->noDelimiter)) {
            // Build the name of the global, dropping the fi
            $bracket->globalvar = '$' . $index->noDelimiter;
        }

        $bracket->fullcode  = $variable->fullcode . $opening . $index->fullcode . $closing ;
        $bracket->enclosing = self::NO_ENCLOSING;
        $bracket->ws->closing = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];

        $this->pushExpression($bracket);
        $this->runPlugins($bracket, array('VARIABLE' => $variable,
                                          'INDEX'    => $index));

        $bracket = $this->processFCOA($bracket);
        $this->checkExpression();

        return $bracket;
    }

    private function processBlock(bool $standalone = self::STANDALONE_BLOCK): AtomInterface {
        $current = $this->id;
        $this->startSequence();
        $this->checkPhpdoc();

        // Case for {}
        if ($this->nextIs(array($this->phptokens::T_CLOSE_CURLY))) {
            $void = $this->addAtomVoid();
            $void->ws->opening = '';
            $void->ws->closing = '';
            $this->addToSequence($void);
        } else {
            $this->contexts->nestContext(Context::CONTEXT_NOSEQUENCE);
            while (!$this->nextIs(array($this->phptokens::T_CLOSE_CURLY))) {
                $this->processNext();
            }
            $this->contexts->exitContext(Context::CONTEXT_NOSEQUENCE);

            $this->checkExpression();
        }

        $block = $this->sequence;
        $this->endSequence();

        $block->code     = '{}';
        $block->fullcode = static::FULLCODE_BLOCK;
        $block->token    = $this->getToken($this->tokens[$this->id][0]);
        $block->bracket  = self::BRACKET;
        $block->ws->opening = $this->tokens[$current][1] . $this->tokens[$current][4];
        $block->ws->closing = $this->tokens[$this->id + 1][1] . $this->tokens[$this->id + 1][4];

        $this->moveToNext(); // skip }

        $this->pushExpression($block);
        if ($standalone === self::STANDALONE_BLOCK) {
            $this->processSemicolon();
        }

        return $block;
    }

    private function processForblock(array $finals = array()): AtomInterface {
        $this->startSequence();
        $block = $this->sequence;

        if ($this->nextIs($finals)) {
            $element                          = $this->addAtomVoid();
            $element->ws->opening             = '';
            $this->sequence->ws->separators[] = '';
        } else {
            do {
                $element = $this->processNext();

                if ($this->nextIs(array($this->phptokens::T_COMMA))) {
                    $element = $this->popExpression();
                    $this->addToSequence($element);
                    $this->sequence->ws->separators[] = $this->tokens[$this->id + 1][1] . $this->tokens[$this->id + 1][4];

                    $this->moveToNext();
                }
            } while (!$this->nextIs($finals));
            $this->sequence->ws->separators[] = '';
        }
        $this->popExpression();
        $this->addToSequence($element);

        $this->moveToNext();
        $sequence           = $this->sequence;
        $this->endSequence();
        $block->code        = $sequence->code;
        $block->fullcode    = self::FULLCODE_SEQUENCE;
        $block->token       = $this->getToken($this->tokens[$this->id][0]);
        $block->ws->closing = '';
        $block->ws->opening = '';

        if ($sequence->count === 1) {
            $block->fullcode = $element->fullcode;
        }

        return $block;
    }

    private function processFor(): AtomInterface {
        $current = $this->id;
        $for = $this->addAtom('For', $current);
        $this->makePhpdoc($for);
        $for->ws->opening = $this->tokens[$this->id][1] . $this->tokens[$this->id][4] .
                            $this->tokens[$this->id + 1][1] . $this->tokens[$this->id + 1][4];
        $this->moveToNext(); // Skip for

        $init = $this->processForblock(array($this->phptokens::T_SEMICOLON));
        $this->addLink($for, $init, 'INIT');
        $for->ws->init = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];

        $final = $this->processForblock(array($this->phptokens::T_SEMICOLON));
        $this->addLink($for, $final, 'FINAL');
        $for->ws->final = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];

        $increment = $this->processForblock(array($this->phptokens::T_CLOSE_PARENTHESIS));
        $this->addLink($for, $increment, 'INCREMENT');
        $for->ws->toblock = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];
        $isColon = $this->whichSyntax($current, $this->id + 1);

        if ($isColon === self::ALTERNATIVE_SYNTAX) {
            $for->ws->toblock .= $this->tokens[$this->id + 1][1] . $this->tokens[$this->id + 1][4];
        }

        $block = $this->processFollowingBlock($isColon === self::ALTERNATIVE_SYNTAX ? array($this->phptokens::T_ENDFOR) : array());
        $this->addLink($for, $block, 'BLOCK');

        if ($isColon === self::ALTERNATIVE_SYNTAX) {
            $fullcode = $this->tokens[$current][1] . '(' . $init->fullcode . ' ; ' . $final->fullcode . ' ; ' . $increment->fullcode . ') : ' . self::FULLCODE_SEQUENCE . ' ' . $this->tokens[$this->id + 1][1];
            // include endoforeach and the final ;
            $for->ws->closing = $this->tokens[$this->id + 1][1] . $this->tokens[$this->id + 1][4];
            if ($this->tokens[$this->id + 2][1] === ';') {
                $for->ws->closing .= $this->tokens[$this->id + 2][1] . $this->tokens[$this->id + 2][4];
            }
        } else {
            $fullcode = $this->tokens[$current][1] . '(' . $init->fullcode . ' ; ' . $final->fullcode . ' ; ' . $increment->fullcode . ')' . ($block->bracket === self::BRACKET ? self::FULLCODE_BLOCK : self::FULLCODE_SEQUENCE);
            $for->ws->closing = '';
        }

        $for->fullcode    = $fullcode;
        $for->alternative = $isColon;

        $this->runPlugins($for, array('INIT'      => $init,
                                      'FINAL'     => $final,
                                      'INCREMENT' => $increment,
                                      'BLOCK'     => $block));

        $this->pushExpression($for);
        $this->finishWithAlternative($isColon);

        return $for;
    }

    private function processForeach(): AtomInterface {
        $current = $this->id;
        $foreach = $this->addAtom('Foreach', $current);
        $this->makePhpdoc($foreach);
        $foreach->ws->opening = $this->tokens[$this->id][1] . $this->tokens[$this->id][4] .
                                $this->tokens[$this->id + 1][1] . $this->tokens[$this->id + 1][4];
        $this->moveToNext(); // Skip foreach

        do {
            $source = $this->processNext();
        } while (!$this->nextIs(array($this->phptokens::T_AS)));

        $this->popExpression();
        $this->addLink($foreach, $source, 'SOURCE');

        $as = $this->tokens[$this->id + 1][1];
        $this->moveToNext(); // Skip as
        $foreach->ws->as = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];
        $variablesStart = max(array_keys($this->atoms));

        do {
            $value = $this->processNext();
        } while (!$this->nextIs(array($this->phptokens::T_CLOSE_PARENTHESIS,
                                    $this->phptokens::T_DOUBLE_ARROW)));
        $this->popExpression();
        $valueFullcode = $value->fullcode;

        if ($this->nextIs(array($this->phptokens::T_DOUBLE_ARROW))) {
            $this->addLink($foreach, $value, 'INDEX');
            $variablesStart = max(array_keys($this->atoms));
            $index = $value;
            $this->moveToNext();
            $index->ws->operator ??= $this->tokens[$this->id][1] . $this->tokens[$this->id][4];
            do {
                $value = $this->processNext();
            } while (!$this->nextIs(array($this->phptokens::T_CLOSE_PARENTHESIS)));
            $this->popExpression();
            $valueFullcode .= " => {$value->fullcode}";
        }
        $this->addLink($foreach, $value, 'VALUE');

        // Warning : this is also connecting variables used for reading : foreach($a as [$b => $c]) { }
        $max = max(array_keys($this->atoms));
        $double = array($value->code => 1);
        for ($i = $variablesStart + 1; $i < $max; ++$i) {
            if ($this->atoms[$i]->atom === 'Variable' && !isset($double[$this->atoms[$i]->code])) {
                $double[$this->atoms[$i]->code] = 1;
                $this->addLink($foreach, $this->atoms[$i], 'VALUE');
            }
        }
        unset($double);

        $this->moveToNext(); // Skip )
        $foreach->ws->toblock = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];
        $isColon = $this->whichSyntax($current, $this->id + 1);
        if ($isColon === self::ALTERNATIVE_SYNTAX) {
            $foreach->ws->toblock .= $this->tokens[$this->id + 1][1] . $this->tokens[$this->id + 1][4];
        }

        $block = $this->processFollowingBlock($isColon === self::ALTERNATIVE_SYNTAX ? array($this->phptokens::T_ENDFOREACH) : array());
        $this->addLink($foreach, $block, 'BLOCK');

        if ($isColon === self::ALTERNATIVE_SYNTAX) {
            $fullcode = $this->tokens[$current][1] . '(' . $source->fullcode . ' ' . $as . ' ' . $valueFullcode . ') : ' . self::FULLCODE_SEQUENCE . ' endforeach';
            // include endoforeach and the final ;
            $foreach->ws->closing = $this->tokens[$this->id + 1][1] . $this->tokens[$this->id + 1][4];
            if ($this->tokens[$this->id + 2][1] === ';') {
                $foreach->ws->closing .= $this->tokens[$this->id + 2][1] . $this->tokens[$this->id + 2][4];
            }
        } else {
            $fullcode = $this->tokens[$current][1] . '(' . $source->fullcode . ' ' . $as . ' ' . $valueFullcode . ')' . ($block->bracket === self::BRACKET ? self::FULLCODE_BLOCK : self::FULLCODE_SEQUENCE);
            $foreach->ws->closing = '';
        }

        $foreach->fullcode    = $fullcode;
        $foreach->alternative = $isColon;

        $extras = array('SOURCE'    => $source,
                        'VALUE'     => $value,
                        'BLOCK'     => $block);
        if (isset($index)) {
            $extras['INDEX'] = $index;
        }
        $this->runPlugins($foreach, $extras);

        $this->pushExpression($foreach);
        $this->finishWithAlternative($isColon);


        return $foreach;
    }

    private function processFollowingBlock(array $finals = array()): AtomInterface {
        $this->checkPhpdoc();
        if ($this->nextIs(array($this->phptokens::T_OPEN_CURLY))) {
            $this->moveToNext();
            $current = $this->id;
            $block = $this->processBlock(self::RELATED_BLOCK);
            $block->ws->opening = $this->tokens[$current][1] . $this->tokens[$current][4];
            $block->ws->separators[] = '';
            $block->bracket = self::BRACKET;
            $this->popExpression(); // drop it
            $block->ws->closing = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];
            // Finish on the last token of the block
        } elseif ($this->nextIs(array($this->phptokens::T_COLON))) {
            $this->startSequence();
            $block = $this->sequence;
            $this->moveToNext(); // skip :

            while (!$this->nextIs($finals)) {
                $this->processNext();
            }

            $this->sequence->ws->opening = '';
            $this->sequence->ws->closing = '';

            $this->endSequence();
        } elseif ($this->nextIs(array($this->phptokens::T_SEMICOLON))) {
            // void; One epxression block, with ;
            $this->startSequence();
            $block = $this->sequence;
            $block->ws->opening = '';
            $block->ws->separators[] = '';
            $block->ws->closing = $this->tokens[$this->id + 1][1] . $this->tokens[$this->id + 1][4];

            $void = $this->addAtomVoid();
            $this->addToSequence($void);
            $this->endSequence();
            $this->moveToNext();
        } elseif ($this->nextIs(array($this->phptokens::T_CLOSE_TAG,
                                      $this->phptokens::T_CLOSE_CURLY,
                                      $this->phptokens::T_CLOSE_PARENTHESIS,
                                      ))) {
            // Completely void (not even ;)
            $this->startSequence();
            $block = $this->sequence;

            $void = $this->addAtomVoid();
            $this->addToSequence($void);
            $this->endSequence();
        } else {
            // One expression only
            $this->startSequence();
            $block = $this->sequence;
            $current = $this->id;

            // This may include WHILE in the list of finals for do....while
            $finals = array_merge(array($this->phptokens::T_SEMICOLON,
                                        $this->phptokens::T_CLOSE_TAG,
                                        $this->phptokens::T_ELSE,
                                        $this->phptokens::T_END,
                                        $this->phptokens::T_CLOSE_CURLY,
                                        ), $finals);
            $specials = array($this->phptokens::T_IF,
                              $this->phptokens::T_FOREACH,
                              $this->phptokens::T_SWITCH,
                              $this->phptokens::T_FOR,
                              $this->phptokens::T_TRY,
                              $this->phptokens::T_WHILE,
                              );
            if ($this->nextIs($specials)) {
                $this->processNext();

                // backtrack on step, to avoid missing the next token
                --$this->id;
            } else {
                do {
                    $expression = $this->processNext();
                } while (!$this->nextIs($finals));
                $this->popExpression();
                if (!$this->nextIs(array($this->phptokens::T_CLOSE_TAG))) {
                    $this->addToSequence($expression);
                }
                $this->runPlugins($block, array($expression));
            }

            $this->sequence->ws->separators[] = $this->tokens[$this->id + 1][1] . $this->tokens[$this->id + 1][4];
            $this->sequence->ws->opening = '';
            $this->sequence->ws->closing = '';

            $this->endSequence();

            // Finish on the final ; of the block
            $this->moveToNext();
        }

        return $block;
    }

    private function processDo(): AtomInterface {
        $current = $this->id;
        $dowhile = $this->addAtom('Dowhile', $this->id);
        $dowhile->ws->opening = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];

        $block = $this->processFollowingBlock(array($this->phptokens::T_WHILE));
        $this->addLink($dowhile, $block, 'BLOCK');

        $while = $this->tokens[$this->id + 1][1];
        $this->moveToNext(); // Skip while
        $dowhile->ws->toblock = $this->tokens[$this->id][1] . $this->tokens[$this->id][4] .
                                $this->tokens[$this->id + 1][1] . $this->tokens[$this->id + 1][4];
        $this->moveToNext(); // Skip (

        do {
            $condition = $this->processNext();
        } while (!$this->nextIs(array($this->phptokens::T_CLOSE_PARENTHESIS)));
        $this->moveToNext(); // skip )
        $this->popExpression();
        $this->addLink($dowhile, $condition, 'CONDITION');

        $dowhile->fullcode = $this->tokens[$current][1] . ( $block->bracket === self::BRACKET ? self::FULLCODE_BLOCK : self::FULLCODE_SEQUENCE) . $while . '(' . $condition->fullcode . ')';
        $dowhile->ws->closing = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];

        $this->runPlugins($dowhile, array('CONDITION' => $condition,
                                          'BLOCK'     => $block));
        $this->pushExpression($dowhile);

        $this->checkExpression();

        return $dowhile;
    }

    private function processWhile(): AtomInterface {
        $current = $this->id;
        $while = $this->addAtom('While', $current);
        $while->ws->opening = $this->tokens[$this->id][1] . $this->tokens[$this->id][4] .
                              $this->tokens[$this->id + 1][1] . $this->tokens[$this->id + 1][4];

        $this->moveToNext(); // Skip while

        do {
            $condition = $this->processNext();
        } while (!$this->nextIs(array($this->phptokens::T_CLOSE_PARENTHESIS)));
        $this->popExpression();
        $this->addLink($while, $condition, 'CONDITION');

        $this->moveToNext(); // Skip )
        $while->ws->toblock = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];
        if ($this->tokens[$this->id + 1][1] === ':') {
            $while->ws->toblock .= $this->tokens[$this->id + 1][1] . $this->tokens[$this->id + 1][4];
        }

        $isColon = $this->whichSyntax($current, $this->id + 1);
        $block = $this->processFollowingBlock($isColon === self::ALTERNATIVE_SYNTAX ? array($this->phptokens::T_ENDWHILE) : array());
        $this->addLink($while, $block, 'BLOCK');

        if ($isColon === self::ALTERNATIVE_SYNTAX) {
            $fullcode = $this->tokens[$current][1] . ' (' . $condition->fullcode . ') : ' . self::FULLCODE_SEQUENCE . ' ' . $this->tokens[$this->id + 1][1];
            $while->ws->closing = $this->tokens[$this->id + 1][1] . $this->tokens[$this->id + 1][4] .
                                  $this->tokens[$this->id + 2][1] . $this->tokens[$this->id + 2][4];
        } else {
            $fullcode = $this->tokens[$current][1] . ' (' . $condition->fullcode . ')' . ($block->bracket === self::BRACKET ? self::FULLCODE_BLOCK : self::FULLCODE_SEQUENCE);
            $while->ws->closing = '';
        }

        $while->fullcode    = $fullcode;
        $while->alternative = $isColon;

        $this->runPlugins($while, array('CONDITION' => $condition,
                                        'BLOCK'     => $block));

        $this->pushExpression($while);
        $this->finishWithAlternative($isColon);

        return $while;
    }

    private function processDeclare(): AtomInterface {
        $current = $this->id;
        $declare = $this->addAtom('Declare', $current);
        $fullcode = array();
        $declare->ws->opening = $this->tokens[$this->id][1] . $this->tokens[$this->id][4] . $this->tokens[$this->id + 1][1] . $this->tokens[$this->id + 1][4];

        $this->moveToNext(); // Skip declare
        $strictTypes = false;
        do {
            $this->moveToNext(); // Skip ( or ,
            $name = $this->processSingle('Name');

            $declaredefinition = $this->addAtom('Declaredefinition');
            $declaredefinition->ws->operator = '=' . $this->tokens[$this->id][4];
            $this->moveToNext(); // Skip =
            $config = $this->processNext();
            $this->popExpression();
            $declaredefinition->code = $name->code . ' = ' . $config->code;

            $this->addLink($declaredefinition, $name, 'NAME');
            $this->addLink($declaredefinition, $config, 'VALUE');

            $strictTypes = $strictTypes || strtolower($name->code) === 'strict_types';

            $this->addLink($declare, $declaredefinition, 'DECLARE');
            $declaredefinition->fullcode = $name->fullcode . ' = ' . $config->fullcode;
            $fullcode[] = $declaredefinition->fullcode;

            $this->moveToNext(); // Skip value
        } while ($this->nextIs(array($this->phptokens::T_COMMA), 0));

        if ($strictTypes === true) {
            $fullcode = $this->tokens[$current][1] . ' (' . implode(', ', $fullcode) . ') ';
            $declare->ws->endargs = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];
            $this->moveToNext(); // skip ;

            $isColon = false;
        } else {
            $isColon = $this->whichSyntax($current, $this->id + 1);
            $declare->ws->endargs = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];
            if ($this->nextIs(array($this->phptokens::T_SEMICOLON))) {
                $fullcode = $this->tokens[$current][1] . ' (' . implode(', ', $fullcode) . ') ;';
                $declare->ws->endargs = $this->tokens[$this->id][1] . $this->tokens[$this->id][4] .
                                        $this->tokens[$this->id + 1][1] . $this->tokens[$this->id + 1][4];
                $this->moveToNext(); // skip ;
            } else {
                $block = $this->processFollowingBlock($isColon === self::ALTERNATIVE_SYNTAX ? array($this->phptokens::T_ENDDECLARE) : array());
                $this->addLink($declare, $block, 'BLOCK');

                if ($isColon === self::ALTERNATIVE_SYNTAX) {
                    $fullcode = $this->tokens[$current][1] . ' (' . implode(', ', $fullcode) . ') : ' . self::FULLCODE_SEQUENCE . ' ' . $this->tokens[$this->id + 1][1];
                } else {
                    $fullcode = $this->tokens[$current][1] . ' (' . implode(', ', $fullcode) . ') ' . self::FULLCODE_BLOCK;
                }
            }
        }

        $declare->fullcode    = $fullcode;
        $declare->ws->closing = '';
        $declare->alternative = $isColon;

        if ($isColon === self::ALTERNATIVE_SYNTAX) {
            $this->moveToNext(); // Skip endforeach
            if ($this->nextIs(array($this->phptokens::T_CLOSE_TAG), 0)) {
                --$this->id;
            }
            $declare->ws->closing = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];
            $this->pushExpression($declare);
            $this->processSemicolon();
        } else {
            if ($this->nextIs(array($this->phptokens::T_CLOSE_TAG), 0)) {
                --$this->id;
            }

            $this->sequence->ws->separators[] = '';
            $this->addToSequence($declare);
        }

        return $declare;
    }

    private function processSwitchDefault(): AtomInterface {
        $current = $this->id;
        $default = $this->addAtom('Default', $current);
        $default->ws->opening = $this->tokens[$this->id][1] . $this->tokens[$this->id][4] .
                                $this->tokens[$this->id + 1][1] . $this->tokens[$this->id + 1][4];

        if ($this->nextIs(array($this->phptokens::T_COLON,
                                 $this->phptokens::T_SEMICOLON))) {
            $this->moveToNext(); // Skip :
        }

        $default->fullcode = $this->tokens[$current][1] . ' : ' . self::FULLCODE_SEQUENCE;

        if ($this->nextIs(array($this->phptokens::T_CASE,
                                $this->phptokens::T_DEFAULT,
                                $this->phptokens::T_ENDSWITCH))) {
            $this->cases->add(array($default, null));
            $default->ws->final = false;

            return $default ;
        }
        $default->ws->final = true;

        $this->startSequence();
        if ($this->nextIs(array($this->phptokens::T_CLOSE_CURLY))) {
            $void = $this->addAtomVoid();
            $void->ws->closing                = '';
            $this->addToSequence($void);
            $this->sequence->ws->separators[] = '';
            $this->sequence->ws->closing      = '';
            $this->sequence->ws->opening      = '';
        } else {
            while (!$this->nextIs(array($this->phptokens::T_CLOSE_CURLY,
                                        $this->phptokens::T_CASE,
                                        $this->phptokens::T_DEFAULT,
                                        $this->phptokens::T_ENDSWITCH))) {
                $this->processNext();
            }
        }
        $code = $this->sequence;
        $this->endSequence();
        $code->ws->opening = '';

        foreach ($this->cases->getAll() as $aCase) {
            $this->addLink($aCase[0], $code, 'CODE');

            if ($aCase[0]->atom === 'Default') {
                $this->runPlugins($aCase[0], array('CODE' => $code));
            } else {
                $this->runPlugins($aCase[0], array('CASE' => $aCase[1],
                                                   'CODE' => $code));
            }
        }

        $this->addLink($default, $code, 'CODE');
        $this->runPlugins($default, array('CODE' => $code));

        return $default;
    }

    // This process Case and Default inside a Match (also, trailing voids)
    private function processMatchCase(): AtomInterface {
        $current = $this->id;

        if ($this->nextIs(array($this->phptokens::T_CLOSE_CURLY))) {
            return $this->addAtomVoid();
        }

        if ($this->nextIs(array($this->phptokens::T_DEFAULT))) {
            $case = $this->addAtom('Default', $current);
            $case->fullcode = $this->tokens[$this->id + 1][1] . $this->tokens[$this->id + 1][4];
            
            $item = null;
            $this->moveToNext();
            $case->ws->opening = $this->tokens[$this->id][1] . $this->tokens[$this->id][4] .
                                 $this->tokens[$this->id + 1][1] . $this->tokens[$this->id + 1][4];
        } else {
            $case = $this->addAtom('Case', $current);

            $this->contexts->nestContext(Context::CONTEXT_NOSEQUENCE);
            do {
                $item = $this->processNext();
            } while (!$this->nextIs(array($this->phptokens::T_DOUBLE_ARROW,
                                          $this->phptokens::T_COMMA)));
            $this->contexts->exitContext(Context::CONTEXT_NOSEQUENCE);

            $this->popExpression();
            $this->addLink($case, $item, 'CASE');
            $case->fullcode = $item->fullcode;

            $case->ws->opening = '';
            $case->ws->toblock = $this->tokens[$this->id + 1][1] . $this->tokens[$this->id + 1][4];
        }
        $this->cases->add(array($case, $item));
        $case->ws->final = $this->nextIs(array($this->phptokens::T_CLOSE_CURLY));

        if ($this->nextIs(array($this->phptokens::T_COMMA))) {
            $this->moveToNext();
            if (!$this->nextIs(array($this->phptokens::T_DOUBLE_ARROW))) {
                return $case;
            }
        }
        $this->moveToNext(); // Skip => or ,

        $this->startSequence();
        do {
            $expression = $this->processNext();
        } while (!$this->nextIs(array($this->phptokens::T_CLOSE_CURLY,
                                      $this->phptokens::T_COMMA)));

        if ($this->nextIs(array($this->phptokens::T_COMMA))) {
            $this->moveToNext();
        }
        $this->sequence->ws->separators[] = '';
        $this->addToSequence($expression);
        $code = $this->sequence;
        $this->endSequence();

        foreach ($this->cases->getAll() as $aCase) {
            $this->addLink($aCase[0], $code, 'CODE');

            if ($aCase[0]->atom === 'Default') {
                $this->runPlugins($aCase[0], array( 'CODE' => $code));
            } else {
                $this->runPlugins($aCase[0], array('CASE' => $aCase[1],
                                                   'CODE' => $code));
            }
        }

        $children = array('CODE' => $code);
        if ($case->atom === 'Case') {
            $children['CASE'] = $item;
        }
        $this->runPlugins($case, $children);

        return $case;
    }

    private function processSwitchCase(): AtomInterface {
        $current = $this->id;
        $case = $this->addAtom('Case', $current);
        $case->ws->opening = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];

        $this->contexts->nestContext(Context::CONTEXT_NOSEQUENCE);
        do {
            $item = $this->processNext();
        } while (!$this->nextIs(array($this->phptokens::T_COLON,
                                      $this->phptokens::T_SEMICOLON,
                                      $this->phptokens::T_CLOSE_TAG,
                                      )));
        $this->contexts->exitContext(Context::CONTEXT_NOSEQUENCE);
        $case->ws->toblock = $this->tokens[$this->id + 1][1] . $this->tokens[$this->id + 1][4];

        $this->popExpression();
        $this->addLink($case, $item, 'CASE');

        if ($this->nextIs(array($this->phptokens::T_COLON,
                                 $this->phptokens::T_SEMICOLON))) {
            $this->moveToNext(); // Skip :
        }

        $case->fullcode = $this->tokens[$current][1] . ' ' . $item->fullcode . ' : ' . self::FULLCODE_SEQUENCE . ' ';

        if ($this->nextIs(array($this->phptokens::T_CASE,
                                $this->phptokens::T_DEFAULT,
                                $this->phptokens::T_ENDSWITCH,
                                ))) {
            $this->cases->add(array($case, $item));
            $case->ws->final = false;

            return $case;
        }
        $case->ws->final = true;

        $this->startSequence();
        if ($this->nextIs(array($this->phptokens::T_CLOSE_CURLY))) {
            $void = $this->addAtomVoid();
            $void->ws->closing                = '';
            $this->addToSequence($void);
            $this->sequence->ws->separators[] = '';
            $this->sequence->ws->closing      = '';
            $this->sequence->ws->opening      = '';
        } else {
            while (!$this->nextIs(array($this->phptokens::T_CLOSE_CURLY,
                                        $this->phptokens::T_CASE,
                                        $this->phptokens::T_DEFAULT,
                                        $this->phptokens::T_ENDSWITCH))) {
                $this->processNext();
            }
        }

        $code = $this->sequence;
        $this->endSequence();
        $code->ws->opening = '';

        foreach ($this->cases->getAll() as $aCase) {
            $code->ws->opening = '';
            $this->addLink($aCase[0], $code, 'CODE');

            if ($aCase[0]->atom === 'Default') {
                $this->runPlugins($aCase[0], array('CODE' => $code));
            } else {
                $this->runPlugins($aCase[0], array('CASE' => $aCase[1],
                                                   'CODE' => $code));
            }
        }

        $this->addLink($case, $code, 'CODE');

        $this->runPlugins($case, array( 'CASE' => $item,
                                        'CODE' => $code));

        return $case;
    }

    private function processSwitchCaseDefault(): AtomInterface {
        $this->checkPhpdoc();

        // skip { or :
        $this->moveToNext();

        switch ($this->tokens[$this->id][0]) {
            case $this->phptokens::T_CASE:
                $case = $this->processSwitchCase();
                break;

            case $this->phptokens::T_DEFAULT:
                $case = $this->processSwitchDefault();
                break;

            case $this->phptokens::T_CLOSE_TAG:
                $case = $this->processClosingTag();
                break;

            default:
                throw new LoadError('Switch case : not a case nor a default : ' . print_r($this->tokens[$this->id], true) . "\n{$this->filename}\n");
        }

        return $case;
    }

    private function processSwitch(): AtomInterface {
        $current = $this->id;
        $switch = $this->addAtom('Switch', $current);
        $switch->ws->opening = $this->tokens[$this->id][1] . $this->tokens[$this->id][4] . $this->tokens[$this->id + 1][1] . $this->tokens[$this->id + 1][4];
        $this->moveToNext(); // Skip (
        $this->cases->push();

        do {
            $name = $this->processNext();
        } while (!$this->nextIs(array($this->phptokens::T_CLOSE_PARENTHESIS)));
        $this->popExpression();
        $this->addLink($switch, $name, 'CONDITION');
        $this->moveToNext(); // skip )
        $switch->ws->toblock = $this->tokens[$this->id][1] . $this->tokens[$this->id][4] . $this->tokens[$this->id + 1][1] . $this->tokens[$this->id + 1][4];

        $isColon = $this->whichSyntax($current, $this->id + 1);

        $cases = $this->addAtom('Sequence', $current);
        $cases->code         = self::FULLCODE_SEQUENCE;
        $cases->fullcode     = $cases->code;
        $cases->bracket      = $isColon === true ? self::NOT_BRACKET : self::BRACKET;

        $this->addLink($switch, $cases, 'CASES');
        $extraCases = array();

        $rank = -1;
        if ($this->nextIs(array($this->phptokens::T_CLOSE_PARENTHESIS))) {
            $void = $this->addAtomVoid();
            $this->addLink($cases, $void, 'EXPRESSION');
            $void->rank = $rank;
            $extraCases[] = $void;

            $this->moveToNext();
        } else {
            if ($this->nextIs(array($this->phptokens::T_OPEN_CURLY))) {
                $this->moveToNext();
                $finals = array($this->phptokens::T_CLOSE_CURLY);
            } else {
                $this->moveToNext(); // skip :
                $finals = array($this->phptokens::T_ENDSWITCH);
            }
            while (!$this->nextIs($finals)) {
                // process case or default.
                $case = $this->processSwitchCaseDefault();

                $this->popExpression();
                $this->addLink($cases, $case, 'EXPRESSION');
                $case->rank = ++$rank;
                $extraCases[] = $case;
                $cases->ws->separators[] = ''; //$this->tokens[$this->id][1] . $this->tokens[$this->id][4];
            }
        }
        $this->moveToNext();
        $cases->count = $rank + 1;
        $cases->ws->opening = '';
        $cases->ws->closing = '';

        if ($isColon === self::ALTERNATIVE_SYNTAX) {
            $fullcode = $this->tokens[$current][1] . ' (' . $name->fullcode . ') :' . self::FULLCODE_SEQUENCE . ' ' . $this->tokens[$this->id][1];
            $switch->ws->closing = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];
        } else {
            $fullcode = $this->tokens[$current][1] . ' (' . $name->fullcode . ')' . self::FULLCODE_BLOCK;
            $switch->ws->closing = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];
        }

        $switch->fullcode    = $fullcode;
        $switch->alternative = $isColon;
        $this->runPlugins($cases, $extraCases);

        $this->runPlugins($switch, array('CONDITION' => $name,
                                         'CASES'     => $cases, ));

        $this->pushExpression($switch);
        $this->finishWithAlternative($isColon);

        $this->cases->pop();

        return $switch;
    }

    private function processMatch(): AtomInterface {
        $current = $this->id;
        $match = $this->addAtom('Match', $current);
        $match->ws->opening = $this->tokens[$this->id][1] . $this->tokens[$this->id][4] .
                              $this->tokens[$this->id + 1][1] . $this->tokens[$this->id + 1][4];

        $this->moveToNext(); // Skip (
        $this->cases->push();

        do {
            $name = $this->processNext();
        } while (!$this->nextIs(array($this->phptokens::T_CLOSE_PARENTHESIS)));
        $this->popExpression();
        $this->addLink($match, $name, 'CONDITION');

        $cases = $this->addAtom('Sequence', $current);
        $cases->code     = self::FULLCODE_SEQUENCE;
        $cases->fullcode = $cases->code;
        $cases->bracket  = self::BRACKET;

        $this->addLink($match, $cases, 'CASES');
        $extraCases = array();
        $this->moveToNext();
        $match->ws->toblock = $this->tokens[$this->id][1] . $this->tokens[$this->id][4] .
                              $this->tokens[$this->id + 1][1] . $this->tokens[$this->id + 1][4];

        $rank = -1;
        if ($this->nextIs(array($this->phptokens::T_CLOSE_PARENTHESIS))) {
            // case of an empty Match
            $void = $this->addAtomVoid();
            $this->addLink($cases, $void, 'EXPRESSION');
            $void->rank = $rank;
            $extraCases[] = $void;

            $this->moveToNext();
        } else {
            if ($this->nextIs(array($this->phptokens::T_OPEN_CURLY))) {
                $this->moveToNext();
                $finals = array($this->phptokens::T_CLOSE_CURLY);
            } else {
                $this->moveToNext(); // skip :
                $finals = array($this->phptokens::T_ENDSWITCH);
            }
            do {
                $this->checkPhpdoc();
                $case = $this->processMatchCase();

                $this->popExpression();
                $this->addLink($cases, $case, 'EXPRESSION');
                $case->rank = ++$rank;
                $extraCases[] = $case;
                if ($this->nextIs(array(','), 0)) {
                    $cases->ws->separators[] = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];
                } else {
                    $cases->ws->separators[] = '';
                }
            } while (!$this->nextIs($finals));
        }
        $this->moveToNext();
        $cases->count = $rank + 1;
        $cases->ws->opening = '';

        $fullcode = $this->tokens[$current][1] . ' (' . $name->fullcode . ')' . self::FULLCODE_BLOCK;
        $match->ws->closing = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];

        $match->fullcode    = $fullcode;

        $this->runPlugins($cases, $extraCases);
        $this->runPlugins($match, array('CONDITION' => $name,
                                        'CASES'     => $cases, ));

        $this->pushExpression($match);

        $this->cases->pop();

        return $match;
    }

    private function processIfthen(): AtomInterface {
        $this->checkPhpdoc();
        $current = $this->id;
        $ifthen = $this->addAtom('Ifthen', $current);
        $this->makePhpdoc($ifthen);
        $ifthen->ws->opening = $this->tokens[$current][1] . $this->tokens[$current][4] .
                               $this->tokens[$current + 1][1] . $this->tokens[$current + 1][4];
        $this->moveToNext(); // Skip (

        do {
            $condition = $this->processNext();
        } while (!$this->nextIs(array($this->phptokens::T_CLOSE_PARENTHESIS)));

        $this->popExpression();
        $this->addLink($ifthen, $condition, 'CONDITION');
        $extras = array('CONDITION' => $condition);

        $this->moveToNext(); // Skip )
        $ifthen->ws->toblock = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];
        if ($this->tokens[$this->id + 1][1] === ':') {
            $ifthen->ws->toblock .= $this->tokens[$this->id + 1][1] . $this->tokens[$this->id + 1][4];
        }

        $isInitialIf = $this->tokens[$current][0] === $this->phptokens::T_IF;
        $isColon = $this->whichSyntax($current, $this->id + 1);

        $then = $this->processFollowingBlock(array($this->phptokens::T_ENDIF,
                                                   $this->phptokens::T_ELSE,
                                                   $this->phptokens::T_ELSEIF,
                                                   ));
        $this->addLink($ifthen, $then, 'THEN');
        $extras['THEN'] = $then;

        $this->checkPhpdoc();
        // Managing else case
        if ($this->nextIs(array($this->phptokens::T_END,
                                $this->phptokens::T_CLOSE_TAG), 0)) {
            $elseFullcode = '';
            // No else, end of a script
            --$this->id;
            // Back up one unit to allow later processing for sequence
        } elseif ($this->nextIs(array($this->phptokens::T_ELSEIF))) {
            $this->moveToNext();
            $this->checkPhpdoc();

            $elseif = $this->processIfthen();
            $this->addLink($ifthen, $elseif, 'ELSE');
            $extras['ELSE'] = $elseif;
            $ifthen->ws->else = '';

            $elseFullcode = $elseif->fullcode;
        } elseif ($this->nextIs(array($this->phptokens::T_ELSE))) {
            $this->moveToNext(); // Skip else
            $elseFullcode = $this->tokens[$this->id][1];
            $ifthen->ws->else = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];
            if ($this->tokens[$this->id + 1][1] === ':') {
                $ifthen->ws->else .= $this->tokens[$this->id + 1][1] . $this->tokens[$this->id + 1][4];
            }

            $else = $this->processFollowingBlock(array($this->phptokens::T_ENDIF));

            $this->addLink($ifthen, $else, 'ELSE');
            $extras['ELSE'] = $else;

            if ($isColon === self::ALTERNATIVE_SYNTAX) {
                $elseFullcode .= ' :';
            }
            $elseFullcode .= $else->fullcode;
        } else {
            $elseFullcode = '';
        }

        if ($isInitialIf === true && $isColon === self::ALTERNATIVE_SYNTAX) {
            if ($this->nextIs(array($this->phptokens::T_SEMICOLON))) {
                $this->moveToNext(); // skip ;
            }
            $this->moveToNext(); // skip ;
        }

        if ($isColon === self::ALTERNATIVE_SYNTAX) {
            $fullcode = $this->tokens[$current][1] . '(' . $condition->fullcode . ') : ' . $then->fullcode . $elseFullcode . ($isInitialIf === true ? ' endif' : '');
            $ifthen->ws->closing = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];
        } else {
            $fullcode = $this->tokens[$current][1] . '(' . $condition->fullcode . ')' . $then->fullcode . $elseFullcode;
            $ifthen->ws->closing = '';
        }

        $ifthen->fullcode    = $fullcode;
        $ifthen->alternative = $isColon;

        $this->runPlugins($ifthen, $extras);

        if ($this->tokens[$current][0] === $this->phptokens::T_IF) {
            $this->pushExpression($ifthen);
            $this->finishWithAlternative($isColon);
        }

        return $ifthen;
    }

    private function checkPhpdoc(): void {
        if (!isset($this->tokens[$this->id + 1])) {
            return;
        }

        while ($this->nextIs(array($this->phptokens::T_DOC_COMMENT))) {
            ++$this->id;
            $this->processPhpdoc();
        }
    }

    private function checkAttribute(): void {
        while ($this->nextIs(array($this->phptokens::T_ATTRIBUTE))) {
            ++$this->id;
            $this->processAttribute();
            $this->checkPhpdoc();
        }
    }

    private function processParenthesis(): AtomInterface {
        $current = $this->id;
        $parenthese = $this->addAtom('Parenthesis', $current);

        $resetContext = false;
        if ($this->contexts->isContext(Context::CONTEXT_NEW)) {
            $resetContext = true;
            $this->contexts->toggleContext(Context::CONTEXT_NEW);
        }
        $this->contexts->nestContext(Context::CONTEXT_NOSEQUENCE);

        while (!$this->nextIs(array($this->phptokens::T_CLOSE_PARENTHESIS))) {
            $this->processNext();
        }
        $this->contexts->exitContext(Context::CONTEXT_NOSEQUENCE);
        if ($resetContext === true) {
            $this->contexts->toggleContext(Context::CONTEXT_NEW);
        }

        $code = $this->popExpression();
        $this->addLink($parenthese, $code, 'CODE');

        $parenthese->fullcode    = '(' . $code->fullcode . ')';
        $parenthese->noDelimiter = $code->noDelimiter;
        $parenthese->ws->opening = $this->tokens[$current][1] . $this->tokens[$current][4];
        $parenthese->ws->closing = $this->tokens[$this->id + 1][1] . $this->tokens[$this->id + 1][4];
        $this->runPlugins($parenthese, array('CODE' => $code));

        $this->pushExpression($parenthese);
        $this->moveToNext(); // Skipping the )

        if ( !$this->contexts->isContext(Context::CONTEXT_NOSEQUENCE) && $this->nextIs(array($this->phptokens::T_CLOSE_TAG))) {
            $this->processSemicolon();
        } else {
            $parenthese = $this->processFCOA($parenthese);
        }

        return $parenthese;
    }

    private function processExit(): AtomInterface {
        $current = $this->id;
        if ($this->nextIs(array($this->phptokens::T_OPEN_PARENTHESIS))) {
            $this->moveToNext();
            $functioncall = $this->processArguments('Exit',
                array($this->phptokens::T_SEMICOLON,
                      $this->phptokens::T_CLOSE_TAG,
                      $this->phptokens::T_CLOSE_PARENTHESIS,
                      $this->phptokens::T_CLOSE_BRACKET,
                      $this->phptokens::T_CLOSE_CURLY,
                      $this->phptokens::T_COLON,
                      $this->phptokens::T_END,
                      ));
            $argumentsFullcode = $functioncall->fullcode;
            $argumentsFullcode = "($argumentsFullcode)";

            $functioncall->code        = $this->tokens[$current][1];
            $functioncall->fullcode    = $this->tokens[$current][1] . $argumentsFullcode;
            $functioncall->fullnspath  = '\\' . mb_strtolower($this->tokens[$current][1]);
            $functioncall->ws->opening = $this->tokens[$current][1] . $this->tokens[$current][4] . $this->tokens[$current + 1][1] . $this->tokens[$current + 1][4];
            $functioncall->ws->closing = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];

            $this->pushExpression($functioncall);
            $this->runPlugins($functioncall);

            $this->checkExpression();

            return $functioncall;
        } else {
            $functioncall = $this->addAtom('Exit', $this->id);

            $functioncall->fullcode    = $this->tokens[$this->id][1] . ' ';
            $functioncall->count       = 0;
            $functioncall->fullnspath  = '\\' . mb_strtolower($functioncall->code);
            $functioncall->ws->opening = $this->tokens[$current][1] . $this->tokens[$current][4];
            $functioncall->ws->closing = '';

            $void = $this->addAtomVoid();
            $void->rank = 0;

            $this->addLink($functioncall, $void, 'ARGUMENT');

            if ( !$this->contexts->isContext(Context::CONTEXT_NOSEQUENCE) &&
                 $this->nextIs(array($this->phptokens::T_CLOSE_TAG,
                                     $this->phptokens::T_COMMA))) {
                $this->processSemicolon();
            }

            $this->pushExpression($functioncall);
            $this->checkExpression();

            return $functioncall;
        }
    }

    private function processArrayLiteral(): AtomInterface {
        $current = $this->id;

        $argumentsList = array();
        if ($this->tokens[$current][0] === $this->phptokens::T_ARRAY) {
            $this->moveToNext(); // Skipping the name, set on (
            $array = $this->processArguments('Arrayliteral', array(), $argumentsList);
            $argumentsFullcode = $array->fullcode;
            $array->token    = 'T_ARRAY';
            $array->fullcode = $this->tokens[$current][1] . '(' . $argumentsFullcode . ')';
            $array->ws->opening   = $this->tokens[$current][1] . $this->tokens[$current][4] .
                                    $this->tokens[$current + 1][1] . $this->tokens[$current + 1][4];
            $array->ws->closing   = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];
        } else {
            $bracket = 1;
            $id = $this->id;
            while ($bracket > 0) {
                ++$id;
                if (!isset($this->tokens[$id])) {
                    throw new LoadError('File is finished in Arraylist :' . $this->filename . ':' . $this->tokens[$current][2]);
                }

                if ($this->tokens[$id][0] === $this->phptokens::T_CLOSE_BRACKET) {
                    --$bracket;
                } elseif ($this->tokens[$id][0] === $this->phptokens::T_OPEN_BRACKET) {
                    ++$bracket;
                }
            }

            if ($this->tokens[$id + 1][0] === $this->phptokens::T_EQUAL ||
                $this->tokens[$current - 1][0] === $this->phptokens::T_AS ||
                $this->contexts->isContext(Context::CONTEXT_LIST)
            ) {
                $this->contexts->nestContext(Context::CONTEXT_LIST);
                $this->contexts->toggleContext(Context::CONTEXT_LIST);
                $array = $this->processArguments('List', array($this->phptokens::T_CLOSE_BRACKET), $argumentsList);
                $this->contexts->toggleContext(Context::CONTEXT_LIST);
                $this->contexts->exitContext(Context::CONTEXT_LIST);
                $argumentsFullcode = $array->fullcode;

                // This is a T_LIST !
                $array->token      = 'T_OPEN_BRACKET';
                $array->fullnspath = '\list';
                $array->fullcode   = "[$argumentsFullcode]";
                $array->ws->opening   = $this->tokens[$current][1] . $this->tokens[$current][4];
                $array->ws->closing = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];
                $array->ws->toargs    = '';
            } else {
                $array = $this->processArguments('Arrayliteral', array($this->phptokens::T_CLOSE_BRACKET), $argumentsList);
                $argumentsFullcode = $array->fullcode;

                $array->token         = 'T_OPEN_BRACKET';
                $array->fullcode      = "[$argumentsFullcode]";
                $array->ws->opening   = $this->tokens[$current][1] . $this->tokens[$current][4];
                $array->ws->closing   = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];
            }
        }

        $array->code      = $this->tokens[$current][1];
        $this->runPlugins($array, $argumentsList);

        $this->pushExpression($array);

        if ( !$this->contexts->isContext(Context::CONTEXT_NOSEQUENCE) && $this->nextIs(array($this->phptokens::T_CLOSE_TAG))) {
            $this->processSemicolon();
        } else {
            $array = $this->processFCOA($array);
        }

        return $array;
    }

    private function processTernary(): AtomInterface {
        $current = $this->id;
        $condition = $this->popExpression();
        $ternary = $this->addAtom('Ternary', $current);
        $ternary->ws->operator = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];

        $names = array($this->phptokens::T_STRING,
                       $this->phptokens::T_NAME_FULLY_QUALIFIED,
                       $this->phptokens::T_NAME_RELATIVE,
                       $this->phptokens::T_NAME_QUALIFIED,
        );

        if ($this->nextIs($names) &&
            $this->nextIs(array($this->phptokens::T_COLON), 2)) {
            if (in_array(mb_strtolower($this->tokens[$this->id + 1][1]), array('true', 'false', '\true', '\false'), STRICT_COMPARISON)) {
                $this->moveToNext();
                $then = $this->processSingle('Boolean');
                $this->runPlugins($then);
            } elseif (mb_strtolower($this->tokens[$this->id + 1][1]) === 'null') {
                $this->moveToNext();
                $then = $this->processSingle('Null');
                $this->runPlugins($then);
            } else {
                $then = $this->processNextAsIdentifier();
                $this->getFullnspath($then, 'const', $then);
                $this->calls->addCall(Calls::CONST, $then->fullnspath, $then);
                $this->runPlugins($then);
            }
        } else {
            $this->contexts->nestContext(Context::CONTEXT_NOSEQUENCE);
            if ($this->nextIs(array($this->phptokens::T_COLON))) {
                $then = $this->addAtomVoid();
            } else {
                do {
                    $then = $this->processNext();
                } while (!$this->nextIs(array($this->phptokens::T_COLON)) );
            }

            $this->contexts->exitContext(Context::CONTEXT_NOSEQUENCE);
            $this->popExpression();
        }

        $this->moveToNext(); // Skip colon
        $ternary->ws->else = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];

        // special cases for T_STRING
        if ($this->nextIs($names) &&
            $this->nextIs(array($this->phptokens::T_COLON), 2)) {
            if (in_array(mb_strtolower($this->tokens[$this->id + 1][1]), array('true', 'false'), STRICT_COMPARISON)) {
                $this->moveToNext();
                $else = $this->processSingle('Boolean');
                $this->runPlugins($else);
            } elseif (mb_strtolower($this->tokens[$this->id + 1][1]) === 'null') { // should also check on T_STRING
                $this->moveToNext();
                $else = $this->processSingle('Null');
                $this->runPlugins($else);
            } else {
                $else = $this->processNextAsIdentifier();
            }
        } else {
            $finals = $this->precedence->get($this->tokens[$this->id][0]);
            $finals[] = $this->phptokens::T_COLON; // Added from nested Ternary
            $finals[] = $this->phptokens::T_CLOSE_TAG;

            $this->contexts->nestContext(Context::CONTEXT_NOSEQUENCE);
            $this->contexts->toggleContext(Context::CONTEXT_NOSEQUENCE);
            do {
                $else = $this->processNext();
            } while (!$this->nextIs($finals));
            $this->contexts->exitContext(Context::CONTEXT_NOSEQUENCE);

            $this->popExpression();
        }

        if ($then->isA(array('Identifier', 'Nsname'))) {
            $this->calls->addCall(Calls::CONST, $then->fullnspath, $then);
        }
        $this->addLink($ternary, $condition, 'CONDITION');
        $this->addLink($ternary, $then, 'THEN');
        $this->addLink($ternary, $else, 'ELSE');

        $ternary->fullcode = $condition->fullcode . ' ?' . ($then->atom === 'Void' ? '' : ' ' . $then->fullcode . ' ' ) . ': ' . $else->fullcode;
        $this->runPlugins($ternary, array('CONDITION' => $condition,
                                          'THEN'      => $then,
                                          'ELSE'      => $else,
                                          ));

        $this->pushExpression($ternary);

        $this->checkExpression();

        return $ternary;
    }

    //////////////////////////////////////////////////////
    /// processing single tokens
    //////////////////////////////////////////////////////
    private function processSingle(string $atomName): AtomInterface {
        $atom = $this->addAtom($atomName, $this->id);
        $atom->fullcode = $this->tokens[$this->id][1];

        if ($atomName === 'Phpvariable' && in_array($atom->code, array('$GLOBALS', '$_SERVER', '$_REQUEST', '$_POST', '$_GET', '$_FILES', '$_ENV', '$_COOKIE', '$_SESSION'), STRICT_COMPARISON)) {
            // do nothing
        } elseif (!in_array($atomName, array('Parametername', 'Parameter', 'Staticpropertyname', 'Propertydefinition', 'Globaldefinition', 'Staticdefinition', 'This'), STRICT_COMPARISON) &&
            $this->nextIs(array($this->phptokens::T_VARIABLE), 0)) {
            if ($this->currentVariables->exists($atom->code)) {
                $this->addLink($this->currentVariables->get($atom->code), $atom, 'DEFINITION');
            } else {
                $definition = $this->addAtom('Variabledefinition');
                $definition->code = $atom->code;
                $definition->fullcode = $atom->fullcode;
                $this->addLink($this->currentFunction->current(), $definition, 'DEFINITION');
                $this->currentVariables->set($atom->code, $definition);

                $this->addLink($definition, $atom, 'DEFINITION');
            }
        }

        return $atom;
    }

    private function processInlinehtml(): AtomInterface {
        $inlineHtml = $this->processSingle('Inlinehtml');

        if ($this->id > 0 && $this->nextIs(array($this->phptokens::T_CLOSE_TAG), -1)) {
            $inlineHtml->ws->opening = $this->tokens[$this->id - 1][1];
        } else {
            $inlineHtml->ws->opening = '';
        }

        $inlineHtml->ws->closing = '';

        $this->sequence->ws->separators[] = '';

        return $inlineHtml;
    }

    private function processNamespaceBlock(): AtomInterface {
        $this->startSequence();

        while (!$this->nextIs(array($this->phptokens::T_CLOSE_TAG,
                                    $this->phptokens::T_NAMESPACE,
                                    $this->phptokens::T_END,
                                    ))) {
            $this->processNext();

            if ($this->nextIs(array($this->phptokens::T_NAMESPACE)) &&
                $this->nextIs(array($this->phptokens::T_NS_SEPARATOR), 2)) {
                $this->processNext();
            }
        }
        $block = $this->sequence;
        $this->endSequence();

        $block->code     = ' ';
        $block->fullcode = ' ' . self::FULLCODE_SEQUENCE . ' ';
        $block->token    = $this->getToken($this->tokens[$this->id][0]);

        return $block;
    }

    private function processNamespace(): AtomInterface {
        $current = $this->id;

        if ($this->nextIs(array($this->phptokens::T_NS_SEPARATOR))) {
            $nsname = $this->processOneNsname(self::WITH_FULLNSPATH);

            $this->pushExpression($nsname);

            return $this->processFCOA($nsname);
        }

        if ($this->nextIs(array($this->phptokens::T_OPEN_CURLY))) {
            $name = $this->addAtomVoid();
            $name->ws->closing = '';
        } else {
            $this->moveToNext();
            $name = $this->makeNsName();
            $name->fullnspath = ($name->fullcode[0] === '\\' ? '' : '\\') . mb_strtolower($name->fullcode);
        }

        $namespace = $this->addAtom('Namespace', $current);
        $this->makePhpdoc($namespace);
        $this->addLink($namespace, $name, 'NAME');
        $this->setNamespace($name->fullcode === ' ' ? self::NO_NAMESPACE : $name->fullcode);

        // Here, we make sure namespace is encompassing the next elements.
        if ($this->nextIs(array($this->phptokens::T_SEMICOLON))) {
            // Process block
            $this->moveToNext(); // Skip ; to start actual sequence
            if ($this->nextIs(array($this->phptokens::T_END))) {
                $namespace->ws->toblock = '';

                $void = $this->addAtomVoid();
                $block = $this->addAtom('Sequence', $this->id);
                $block->code       = '{}';
                $block->fullcode   = self::FULLCODE_BLOCK;
                $block->bracket    = self::NOT_BRACKET;
                $block->ws->opening      = ';';
                $block->ws->closing      = '';

                $this->addLink($block, $void, 'EXPRESSION');
            } else {
                $namespace->ws->toblock = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];
                $block = $this->processNamespaceBlock();
                $block->ws->opening      = '';
            }
            $this->addLink($namespace, $block, 'BLOCK');
            $this->addToSequence($namespace);
            $block = ';';
            $namespace->ws->closing = '';
        } else {
            // Process block

            $block = $this->processFollowingBlock(array($this->phptokens::T_CLOSE_CURLY));
            $this->addLink($namespace, $block, 'BLOCK');

            $this->addToSequence($namespace);

            $block = self::FULLCODE_BLOCK;
            $namespace->ws->toblock = '';
            $namespace->ws->closing = '';
        }
        $this->setNamespace(self::NO_NAMESPACE);

        $namespace->fullcode   = $this->tokens[$current][1] . ' ' . $name->fullcode . $block;
        $namespace->fullnspath = $name->atom === 'Void' ? '\\' : $name->fullnspath;
        $namespace->ws->opening = $this->tokens[$current][1] . $this->tokens[$current][4];

        $this->sequence->ws->separators[] = '';
        return $namespace;
    }

    private function processAlias(string $useType): AtomInterface {
        $current = $this->id;
        $as = $this->addAtom('As', $current);
        $as->ws->opening = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];

        $left = $this->popExpression();
        assert($left->atom !== 'Void', 'Poping a void expression!');
        $this->addLink($as, $left, 'NAME');

        $right = $this->processNextAsIdentifier(self::WITHOUT_FULLNSPATH);
        $right->fullnspath = '\\' . mb_strtolower($right->code);
        $this->addLink($as, $right, 'AS');

        $as->fullcode = $left->fullcode . ' ' . $this->tokens[$this->id - 1][1] . ' ' . $right->fullcode;

        $this->addNamespaceUse($left, $as, $useType, $as);

        return $as;
    }

    private function processAsTrait(): AtomInterface {
        $current = $this->id;
        $as = $this->addAtom('As', $current);

        // special case for use t, t2 { as as yes; }
        if ($this->nextIs(array($this->phptokens::T_AS))) {
            $left = $this->processNextAsIdentifier();
        } else {
            $left = $this->popExpression();
            assert($left->atom !== 'Void', 'Poping a void expression!');
        }

        $as->ws->opening = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];

        $this->calls->addCall(Calls::STATICMETHOD, $left->fullnspath, $left);

        $this->addLink($as, $left, 'NAME');
        $fullcode = array($left->fullcode, $this->tokens[$current][1]);

        if ($this->nextIs(array($this->phptokens::T_PRIVATE,
                                $this->phptokens::T_PUBLIC,
                                $this->phptokens::T_PROTECTED,
                                ))) {
            $fullcode[] = $this->tokens[$this->id + 1][1];
            $as->visibility = strtolower($this->tokens[$this->id + 1][1]);
            $as->ws->visibility = $this->tokens[$this->id + 1][1] . $this->tokens[$this->id + 1][4];
            $this->moveToNext();
        }

        if (!$this->nextIs(array($this->phptokens::T_SEMICOLON))) {
            $alias = $this->processNextAsIdentifier();
            $this->addLink($as, $alias, 'AS');
            $fullcode[] = $alias->fullcode;
        }

        $as->fullcode = implode(' ', $fullcode);

        $this->pushExpression($as);

        return $as;
    }

    private function processInsteadof(): AtomInterface {
        $insteadof = $this->processOperator('Insteadof', $this->precedence->get($this->tokens[$this->id][0]), array('NAME', 'INSTEADOF'));
        while ($this->nextIs(array($this->phptokens::T_COMMA))) {
            $this->moveToNext();
            $nsname = $this->processOneNsname();

            $this->addLink($insteadof, $nsname, 'INSTEADOF');
        }
        $insteadof->ws->closing = '';

        return $insteadof;
    }

    private function processUse(): AtomInterface {
        if ($this->currentClassTrait->getCurrent() === ClassTraitContext::NO_CLASS_TRAIT_CONTEXT) {
            return $this->processUseNamespace();
        } else {
            return $this->processUseTrait();
        }
    }

    private function processUseNamespace(): AtomInterface {
        $current = $this->id;
        $use = $this->addAtom('Usenamespace', $current);
        $use->use = 'class';
        $use->ws->opening = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];
        $this->makePhpdoc($use);

        $fullcode = array();

        $use->ws->operator = '';
        // use const
        if ($this->nextIs(array($this->phptokens::T_CONST))) {
            $use->ws->operator = $this->tokens[$this->id + 1][1] . $this->tokens[$this->id + 1][4];
            $this->moveToNext();

            $use->use = 'const';
        }

        // use function
        if ($this->nextIs(array($this->phptokens::T_FUNCTION))) {
            $use->ws->operator = $this->tokens[$this->id + 1][1] . $this->tokens[$this->id + 1][4];
            $this->moveToNext();

            $use->use = 'function';
        }

        $rank = -1;
        $useType = $use->use;
        --$this->id;
        $usesDefinitions = array();
        do {
            ++$rank;
            $prefix = '';
            $this->moveToNext();
            $this->checkPhpdoc();
            $namespace = $this->processOneNsname(self::WITHOUT_FULLNSPATH);
            // Default case : use A\B
            $alias = $namespace;
            $origin = $namespace;

            $fullnspath = mb_strtolower($namespace->fullcode);

            if ($fullnspath[0] !== '\\') {
                list($prefix) = explode('\\', $fullnspath, 1);
                $fullnspath = "\\$fullnspath";
            }

            if ($useType === 'class') {
                $this->calls->addCall(Calls::A_CLASS, $fullnspath, $namespace);
            }

            if ($this->nextIs(array($this->phptokens::T_AS))) {
                // use A\B as C
                $this->moveToNext();

                $fullnspath = makeFullNsPath($namespace->fullcode, $useType === 'const' ? \FNP_CONSTANT : \FNP_NOT_CONSTANT);
                $namespace->fullnspath = $fullnspath;

                $this->pushExpression($namespace);
                $as = $this->processAlias($useType);
                $as->fullnspath = makeFullNsPath($namespace->fullcode, $useType === 'const');
                $as->ws->totype = '';
                $as->rank = $rank;
                $fullcode[] = $as->fullcode;
                $as->alias = mb_strtolower(substr($as->fullcode, strrpos($as->fullcode, ' as ') + 4));

                $alias = $this->addNamespaceUse($origin, $as, $useType, $as);

                $this->addLink($use, $as, 'USE');

                $namespace             = $as;
                $namespace->fullnspath = $fullnspath;
                $namespace->use        = $useType;
                $usesDefinitions[]     = $namespace;
                $this->runPlugins($namespace, array());
            } elseif ($this->nextIs(array($this->phptokens::T_NS_SEPARATOR))) {
                //use A\B\ {}
                $this->addLink($use, $namespace, 'GROUPUSE');
                $prefix = makeFullNsPath($namespace->fullcode);
                if ($prefix[0] !== '\\') {
                    $prefix = "\\$prefix";
                }
                $prefix .= '\\';
                $use->ws->toblock = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];

                $this->moveToNext(); // Skip \

                $useTypeGeneric = $useType;
                do {
                    $this->moveToNext(); // Skip { or ,
                    // trailing comma
                    if ($this->nextIs(array($this->phptokens::T_CLOSE_CURLY))) {
                        $use->trailing = self::TRAILING;
                        $last = count($use->ws->touseseparators) - 1;
                        $use->ws->touseseparators[$last] .= $this->tokens[$this->id + 1][1] . $this->tokens[$this->id + 1][4];

                        continue;
                    }

                    $useType = $useTypeGeneric;
                    $totype = '';
                    if ($this->nextIs(array($this->phptokens::T_CONST))) {
                        // use const
                        $this->moveToNext();

                        $useType = 'const';
                        $totype = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];
                    }

                    if ($this->nextIs(array($this->phptokens::T_FUNCTION))) {
                        // use function
                        $this->moveToNext();

                        $useType = 'function';
                        $totype = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];
                    }

                    $nsname = $this->processOneNsname();

                    if ($this->nextIs(array($this->phptokens::T_AS))) {
                        // A\B as C
                        $this->moveToNext();
                        $this->pushExpression($nsname);
                        $alias = $this->processAlias($useType);
                        $alias->ws->totype = $totype;

                        if ($useType === 'const') {
                            $nsname->fullnspath = $prefix . $nsname->fullcode;
                            $nsname->origin     = $nsname->fullnspath;

                            $alias->fullnspath  = $nsname->fullnspath;
                            $alias->origin      = $nsname->origin;
                        } else {
                            $nsname->fullnspath = $prefix . mb_strtolower($nsname->fullcode);
                            $nsname->origin     = $nsname->fullnspath;

                            $alias->fullnspath  = $nsname->fullnspath;
                            $alias->origin      = $nsname->origin;
                        }

                        $aliasName = $this->addNamespaceUse($nsname, $alias, $useType, $alias);
                        $alias->alias = $aliasName;
                        $this->addLink($use, $alias, 'USE');
                        $usesDefinitions[] = $alias;
                    } else {
                        $this->addLink($use, $nsname, 'USE');
                        if ($useType === 'const') {
                            $nsname->fullnspath = $prefix . $nsname->fullcode;
                            $nsname->origin     = $nsname->fullnspath;
                        } else {
                            $nsname->fullnspath = $prefix . mb_strtolower($nsname->fullcode);
                            $nsname->origin     = $nsname->fullnspath;
                        }

                        $alias = $this->addNamespaceUse($nsname, $nsname, $useType, $nsname);

                        $nsname->alias = $alias;
                        $usesDefinitions[] = $nsname;
                    }

                    $nsname->use = $useType;
                    $nsname->ws->totype = $totype;
                    $use->ws->touseseparators[] = $this->tokens[$this->id + 1][1] . $this->tokens[$this->id + 1][4];
                } while ( $this->nextIs(array($this->phptokens::T_COMMA)));

                $fullcode[] = $namespace->fullcode . self::FULLCODE_BLOCK;

                $this->moveToNext(); // Skip }
            } else {
                $this->addLink($use, $namespace, 'USE');
                $namespace->rank = $rank;
                $usesDefinitions[] = $namespace;
                $namespace->use    = $useType;
                $namespace->ws->totype = '';

                $fullnspath = makeFullNsPath($namespace->fullcode, $useType === 'const' ? \FNP_CONSTANT : \FNP_NOT_CONSTANT);
                $namespace->fullnspath = $fullnspath;
                $namespace->origin     = $fullnspath;

                if (($use2 = $this->uses->get('class', $prefix)) instanceof AtomInterface) {
                    $this->addLink($namespace, $use2, 'DEFINITION');
                }

                $namespace->fullnspath = $fullnspath;
                $this->runPlugins($namespace, array());

                $alias = $this->addNamespaceUse($alias, $alias, $useType, $namespace);

                $namespace->alias = $alias;
                $origin->alias = $alias;

                $fullcode[] = $namespace->fullcode;
            }

            if ($this->nextIs(array($this->phptokens::T_COMMA))) {
                $use->ws->touseseparators[] = $this->tokens[$this->id + 1][1] . $this->tokens[$this->id + 1][4];
            } else {
                $use->ws->touseseparators[] = '';
            }
        } while ($this->nextIs(array($this->phptokens::T_COMMA)));
        $this->runPlugins($use, $usesDefinitions);
        $use->count = $rank + 1; // final rank is the count total

        $use->fullcode = $this->tokens[$current][1] . ($useType !== 'class' ? ' ' . $useType : '') . ' ' . implode(', ', $fullcode);

        $this->pushExpression($use);

        $this->checkExpression();

        return $use;
    }

    private function processUseTrait(): AtomInterface {
        $current = $this->id;
        $use = $this->addAtom('Usetrait', $current);
        $use->ws->opening = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];

        $fullcode = array();

        --$this->id;
        $extras = array();
        do {
            $this->moveToNext();
            $this->checkPhpdoc();
            $namespace = $this->processOneNsname(self::WITHOUT_FULLNSPATH);

            $fullcode[] = $namespace->fullcode;

            $this->getFullnspath($namespace, 'class', $namespace);

            $this->calls->addCall(Calls::A_CLASS, $namespace->fullnspath, $namespace);

            $this->addLink($use, $namespace, 'USE');
            $extras[] = $namespace;
            $this->checkPhpdoc();
            $use->ws->separators[] = $this->tokens[$this->id + 1][1] . $this->tokens[$this->id + 1][4];
        } while ($this->nextIs(array($this->phptokens::T_COMMA)));
        array_pop($use->ws->separators);
        $use->ws->separators[] = '';

        $fullcode = implode(', ', $fullcode);
        $this->runPlugins($use, $extras);

        if ($this->nextIs(array($this->phptokens::T_OPEN_CURLY))) {
            //use A\B{} // Group
            $currentBlock = $this->id + 1;
            $block = $this->processUseBlock();
            $block->ws->opening = $this->tokens[$currentBlock][1] . $this->tokens[$currentBlock][4];
            $block->ws->closing = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];

            $this->addLink($use, $block, 'BLOCK');
            $fullcode .= ' ' . $block->fullcode;

            // Several namespaces ? This has to be recalculated inside the block!!
            $namespace->fullnspath = makeFullNsPath($namespace->fullcode);
        }

        $use->fullcode = $this->tokens[$current][1] . ' ' . $fullcode;
        $this->pushExpression($use);

        return $use;
    }

    private function processUseBlock(): AtomInterface {
        $this->startSequence();

        // Case for {}
        $this->moveToNext();
        if ($this->nextIs(array($this->phptokens::T_CLOSE_CURLY))) {
            $void = $this->addAtomVoid();
            $this->addToSequence($void);

            $this->moveToNext(); // skip }
        } else {
            $this->contexts->nestContext(Context::CONTEXT_NOSEQUENCE);
            do {
                $origin = $this->processOneNsname();
                $this->checkPhpdoc();
                if ($this->nextIs(array($this->phptokens::T_DOUBLE_COLON))) {
                    $this->moveToNext(); // skip ::
                    $this->checkPhpdoc();
                    $method =  $this->processNextAsIdentifier();

                    $class = $origin;
                    $this->calls->addCall(Calls::A_CLASS, $class->fullnspath, $class);

                    $staticmethod = $this->addAtom('Staticmethod');
                    $this->addLink($staticmethod, $origin, 'CLASS');
                    $this->addLink($staticmethod, $method, 'METHOD');
                    $origin = $staticmethod;

                    $origin->fullcode = "{$class->fullcode}::{$method->fullcode}";
                }
                $this->pushExpression($origin);

                $this->checkPhpdoc();
                $this->moveToNext();

                if ($this->nextIs(array($this->phptokens::T_AS), 0)) {
                    $this->processAsTrait();
                } elseif ($this->nextIs(array($this->phptokens::T_INSTEADOF), 0)) {
                    $this->processInsteadof();
                } else {
                    throw new UnknownCase('Usetrait without as or insteadof : ' . $this->tokens[$this->id + 1][1]);
                }

                $this->moveToNext();
                $this->processSemicolon(); // ;
                $this->checkPhpdoc();
            } while (!$this->nextIs(array($this->phptokens::T_CLOSE_CURLY)));
            $this->contexts->exitContext(Context::CONTEXT_NOSEQUENCE);
            $this->moveToNext();
        }

        $this->checkExpression();

        $block = $this->sequence;
        $this->endSequence();

        $block->code     = '{}';
        $block->fullcode = static::FULLCODE_BLOCK;
        $block->bracket  = self::BRACKET;

        return $block;
    }

    private function processVariable(): AtomInterface {
        if ($this->tokens[$this->id][1] === '$this') {
            $atom = 'This';
        } elseif (in_array($this->tokens[$this->id][1], $this->PHP_SUPERGLOBALS, STRICT_COMPARISON)) {
            $atom = 'Phpvariable';
        } elseif ($this->nextIs(array($this->phptokens::T_OBJECT_OPERATOR,
                                      $this->phptokens::T_NULLSAFE_OBJECT_OPERATOR,
                                      ))) {
            $atom = 'Variableobject';
        } elseif ($this->nextIs(array($this->phptokens::T_OPEN_BRACKET))) {
            $atom = 'Variablearray';
        } else {
            $atom = 'Variable';
        }
        $variable = $this->processSingle($atom);
        $this->pushExpression($variable);

        if ($atom === 'This' && ($class = $this->currentClassTrait->getCurrentForThis())) {
            $variable->fullnspath = $class->fullnspath;
            $this->calls->addCall(Calls::A_CLASS, $class->fullnspath, $variable);
        }
        $this->runPlugins($variable);
        $variable->ws->opening = $this->tokens[$this->id][1];

        if ( !$this->contexts->isContext(Context::CONTEXT_NOSEQUENCE) && $this->nextIs(array($this->phptokens::T_CLOSE_TAG))) {
            $this->processSemicolon();
        } else {
            $variable = $this->processFCOA($variable);
        }

        return $variable;
    }

    private function processFCOA(AtomInterface $nsname): AtomInterface {
        // for functions
        if ($this->nextIs(array($this->phptokens::T_OPEN_PARENTHESIS))) {
            return $this->processFunctioncall();
        }

        // for $a++
        if ($this->nextIs(array($this->phptokens::T_INC,
                                $this->phptokens::T_DEC))) {
            return $this->processPostPlusplus($nsname);
        }

        // for array appends
        if ($this->nextIs(array($this->phptokens::T_OPEN_BRACKET)) &&
            $this->nextIs(array($this->phptokens::T_CLOSE_BRACKET), 2)) {
            return $this->processAppend();
        }

        // for arrays
        if ($this->nextIs(array($this->phptokens::T_OPEN_BRACKET)) ||
            $this->nextIs(array($this->phptokens::T_OPEN_CURLY))) {
            if ($nsname->isA(array('Nsname', 'Identifier', 'Boolean'))) {
                $type = $this->contexts->isContext(Context::CONTEXT_NEW) ? 'class' : 'const';
                if ($type === 'const') {
                    $this->getFullnspath($nsname, $type, $nsname);
                    $this->runPlugins($nsname);
                    $this->calls->addCall(Calls::CONST, $nsname->fullnspath, $nsname);
                }
            }

            return $this->processBracket();
        }

        // for simple identifiers
        if ($this->nextIs(array($this->phptokens::T_DOUBLE_COLON))     ||
            $this->nextIs(array($this->phptokens::T_NS_SEPARATOR))     ||
            $this->nextIs(array($this->phptokens::T_INSTANCEOF), -1)   ||
            $this->nextIs(array($this->phptokens::T_IMPLEMENTS), -1)   ||
            $this->nextIs(array($this->phptokens::T_EXTENDS), -1)   ||
            $this->nextIs(array($this->phptokens::T_AS), -1)) {
            return $nsname;
        }

        if ($nsname->atom === 'Newcall') {
            // New call, but no () : it still requires an argument count
            $nsname->count ??= 0;

            return $nsname;
        }

        if ($nsname->isA(array('Nsname', 'Identifier'))) {
            $type = $this->contexts->isContext(Context::CONTEXT_NEW) ? 'class' : 'const';
            $this->getFullnspath($nsname, $type, $nsname);

            if ($type === 'const') {
                $this->runPlugins($nsname);
                $this->calls->addCall(Calls::CONST, $nsname->fullnspath, $nsname);
            }
        }

        return $nsname;
    }

    private function processAppend(): AtomInterface {
        $current = $this->id;
        $append = $this->addAtom('Arrayappend', $current);

        $left = $this->popExpression();
        assert($left->atom !== 'Void', 'Poping a void expression!');
        $this->addLink($append, $left, 'APPEND');

        $append->fullcode = $left->fullcode . '[]';
        $append->ws->closing = '[' . $this->tokens[$current + 1][4] . ']' . $this->tokens[$current + 2][4];

        $this->pushExpression($append);
        $this->runPlugins($append, array('APPEND' => $left));

        $this->moveToNext();
        $this->moveToNext();

        if ( !$this->contexts->isContext(Context::CONTEXT_NOSEQUENCE) && $this->nextIs(array($this->phptokens::T_CLOSE_TAG))) {
            $this->processSemicolon();
        } else {
            // Mostly for arrays
            $append = $this->processFCOA($append);
        }

        return $append;
    }

    private function processInteger(): AtomInterface {
        $integer = $this->addAtom('Integer', $this->id);

        $integer->code     = $this->tokens[$this->id][1];
        $integer->fullcode = $this->tokens[$this->id][1];

        $this->pushExpression($integer);
        $this->runPlugins($integer);
        $this->checkExpression();

        return $integer;
    }

    private function processFloat(): AtomInterface {
        $float = $this->addAtom('Float', $this->id);

        $float->code     = $this->tokens[$this->id][1];
        $float->fullcode = $float->code;

        $this->pushExpression($float);
        // (int) is for loading into the database
        $this->runPlugins($float);

        $this->checkExpression();

        return $float;
    }

    private function processLiteral(): AtomInterface {
        $literal = $this->processSingle('String');
        $this->pushExpression($literal);

        if ($this->nextIs(array($this->phptokens::T_CONSTANT_ENCAPSED_STRING), 0)) {
            $literal->delimiter   = $literal->code[0];
            if (in_array($literal->delimiter, array('b', 'B'), STRICT_COMPARISON)) {
                $literal->binaryString = $literal->delimiter;
                $literal->delimiter    = $literal->code[1];
                $literal->noDelimiter  = substr($literal->code, 2, -1);
            } else {
                $literal->noDelimiter = substr($literal->code, 1, -1);
            }

            if (in_array(mb_strtolower($literal->noDelimiter),  array('parent', 'self', 'static'), STRICT_COMPARISON)) {
                $this->getFullnspath($literal, 'class', $literal);

                $this->calls->addCall(Calls::A_CLASS, $literal->fullnspath, $literal);
            } else {
                $this->calls->addNoDelimiterCall($literal);
            }
        } elseif ($this->nextIs(array($this->phptokens::T_NUM_STRING), 0)) {
            $literal->delimiter   = '';
            $literal->noDelimiter = $literal->code;

            $this->calls->addNoDelimiterCall($literal);
        } else {
            $literal->delimiter   = '';
            $literal->noDelimiter = '';
        }
        $this->runPlugins($literal);
        if ($this->nextIs(array($this->phptokens::T_OPEN_BRACKET))) {
            $literal = $this->processBracket();
        }

        if ( !$this->contexts->isContext(Context::CONTEXT_NOSEQUENCE) && $this->nextIs(array($this->phptokens::T_CLOSE_TAG))) {
            $this->processSemicolon();
        } else {
            $literal = $this->processFCOA($literal);
        }

        $literal->ws->opening = '';

        return $literal;
    }

    private function processMagicConstant(): AtomInterface {
        $constant = $this->processSingle('Magicconstant');
        $this->pushExpression($constant);

        if (mb_strtolower($constant->fullcode) === '__dir__') {
            $path = dirname($this->filename);
            $constant->noDelimiter = $path === '/' ? '' : $path;
        } elseif (mb_strtolower($constant->fullcode) === '__file__') {
            $constant->noDelimiter = $this->filename;
        } elseif (mb_strtolower($constant->fullcode) === '__function__') {
            $constant->noDelimiter = $this->currentFunction->currentCode();
        } elseif (mb_strtolower($constant->fullcode) === '__class__') {
            if ($this->currentClassTrait->getCurrent() === ClassTraitContext::NO_CLASS_TRAIT_CONTEXT) {
                $constant->noDelimiter = '';
            } elseif ($this->currentClassTrait->getCurrent()->atom === 'Class') {
                $constant->noDelimiter = $this->currentClassTrait->getCurrent()->fullnspath;
            } else {
                $constant->noDelimiter = '';
            }
        } elseif (mb_strtolower($constant->fullcode) === '__trait__') {
            if ($this->currentClassTrait->getCurrent() === ClassTraitContext::NO_CLASS_TRAIT_CONTEXT) {
                $constant->noDelimiter = '';
            } elseif ($this->currentClassTrait->getCurrent()->atom === 'Trait') {
                $constant->noDelimiter = $this->currentClassTrait->getCurrent()->fullnspath;
            } else {
                $constant->noDelimiter = '';
            }
        } elseif (mb_strtolower($constant->fullcode) === '__line__') {
            $constant->noDelimiter = (string) $this->tokens[$this->id][2];
        } elseif (mb_strtolower($constant->fullcode) === '__method__') {
            if ($this->currentClassTrait->getCurrent() === ClassTraitContext::NO_CLASS_TRAIT_CONTEXT) {
                if ($this->currentFunction->currentFullnspath() === 'global') {
                    $constant->noDelimiter = '';
                } else {
                    $constant->noDelimiter = $this->currentFunction->currentCode();
                }
            } elseif ($this->currentFunction->currentFullnspath() === 'global') {
                $constant->noDelimiter = '';
            } elseif ($this->currentClassTrait->getCurrent() !== ClassTraitContext::NO_CLASS_TRAIT_CONTEXT) {
                $constant->noDelimiter = $this->currentClassTrait->getCurrent()->fullnspath .
                                         '::' .
                                         $this->currentClassTrait->getCurrent()->code;
            } else {
                $constant->noDelimiter = '';
            }
        }

        $constant->intval  = (int) $constant->noDelimiter;
        $constant->boolean = (bool) $constant->intval;
        $this->runPlugins($constant);

        $constant = $this->processFCOA($constant);

        return $constant;
    }

    //////////////////////////////////////////////////////
    /// processing single operators
    //////////////////////////////////////////////////////
    private function processSingleOperator(AtomInterface $operator, array $finals = array(), string $link = '', string $separator = ''): AtomInterface {
        assert($link !== '', 'Link cannot be empty');

        $current = $this->id;

        $this->contexts->nestContext(Context::CONTEXT_NOSEQUENCE);
        $this->contexts->toggleContext(Context::CONTEXT_NOSEQUENCE);
        // Do while, so that AT least one loop is done.
        do {
            $operand = $this->processNext();
        } while (!$this->nextIs($finals));
        $this->contexts->exitContext(Context::CONTEXT_NOSEQUENCE);

        $this->popExpression();
        $this->addLink($operator, $operand, $link);

        $operator->fullcode = $this->tokens[$current][1] . $separator . $operand->fullcode;

        $this->runPlugins($operator, array($link => $operand));
        $this->pushExpression($operator);

        $this->checkExpression();

        return $operand;
    }

    private function processCast(): AtomInterface {
        $operator = $this->addAtom('Cast', $this->id);
        $operator->ws->opening  = $this->tokens[$this->id][1] . $this->tokens[$this->id][4] ;

        $this->processSingleOperator($operator, $this->precedence->get($this->tokens[$this->id][0]), 'CAST', ' ');
        $this->popExpression();
        if (strtolower($operator->code) === '(binary)') {
            $operator->binaryString = $operator->code[1];
        }
        $this->pushExpression($operator);

        $cast2fnp = array(
            'T_ARRAY_CAST'               => '\\array',
            'T_BOOL_CAST'                => '\\bool',
            'T_DOUBLE_CAST'              => '\\float',
            'T_INT_CAST'                 => '\\int',
            'T_OBJECT_CAST'              => '\\stdclass',
            'T_STRING_CAST'              => '\\string',
            'T_UNSET_CAST'               => '\\unset',
        );
        $operator->fullnspath = $cast2fnp[$operator->token];


        return $operator;
    }

    private function processReturn(): AtomInterface {
        $current = $this->id;
        // Case of return ;
        $return = $this->addAtom('Return', $current);
        $this->makePhpdoc($return);
        $return->ws->opening  = $this->tokens[$current][1] . $this->tokens[$current][4] ;

        if ($this->nextIs(array($this->phptokens::T_CLOSE_TAG,
                                $this->phptokens::T_SEMICOLON))) {
            $returnArg = $this->addAtomVoid();
            $returnArg->ws->closing = '';
            $this->addLink($return, $returnArg, 'RETURN');

            $return->fullcode = $this->tokens[$current][1] . ' ;';

            $this->runPlugins($return, array('RETURN' => $returnArg) );

            $this->pushExpression($return);
            if ($this->nextIs(array($this->phptokens::T_CLOSE_TAG))) {
                $this->processSemicolon();
            }

            if ($this->currentFunction->currentFullnspath() !== 'global') {
                $this->addLink($this->currentFunction->current(), $returnArg, 'RETURNED');
            }

            return $return;
        }

        $this->contexts->nestContext(Context::CONTEXT_NOSEQUENCE);
        $this->contexts->toggleContext(Context::CONTEXT_NOSEQUENCE);
        $finals =  $this->precedence->get($this->tokens[$this->id][0]);
        do {
            $returned = $this->processNext();
        } while (!$this->nextIs($finals));
        $this->popExpression();

        $this->contexts->exitContext(Context::CONTEXT_NOSEQUENCE);

        $this->addLink($return, $returned, 'RETURN');

        $return->fullcode     = $this->tokens[$current][1] . ' ' . $returned->fullcode;

        $this->addLink($this->currentFunction->current(), $returned, 'RETURNED');

        $this->runPlugins($return, array('RETURN' => $returned) );

        $this->pushExpression($return);
        $this->checkExpression();

        return $return;
    }

    private function processThrow(): AtomInterface {
        $operator = $this->addAtom('Throw', $this->id);
        $operator->ws->opening = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];
        $this->processSingleOperator($operator, $this->precedence->get($this->tokens[$this->id][0]), 'THROW', ' ');
        $operator = $this->popExpression();
        $this->pushExpression($operator);

        $this->checkExpression();

        return $operator;
    }

    private function makeAttributes(AtomInterface $node): void {
        assert(in_array($node->atom, Analyzer::ATTRIBUTE_ATOMS, STRICT_COMPARISON), "Wrong type for assigning Attribute ($node->atom), $this->filename\n");

        foreach ($this->attributes as $attribute) {
            $this->addLink($node, $attribute, 'ATTRIBUTE');
        }

        $this->attributes = array();
    }

    private function processYield(): AtomInterface {
        if ($this->nextIs($this->END_OF_EXPRESSION)) {
            $current = $this->id;

            // Case of return ;
            $yieldArg = $this->addAtomVoid();
            $yield = $this->addAtom('Yield', $current);

            $this->addLink($yield, $yieldArg, 'YIELD');

            $yield->fullcode = $this->tokens[$current][1] . ' ;';
            $yield->ws->opening = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];

            $this->pushExpression($yield);
            $this->runPlugins($yield, array('YIELD' => $yieldArg) );

            if ($this->nextIs(array($this->phptokens::T_CLOSE_TAG))) {
                $this->processSemicolon();
            }

            return $yield;
        }

        // => is actually a lower priority
        $finals = $this->precedence->get($this->tokens[$this->id][0]);
        $id = array_search($this->phptokens::T_DOUBLE_ARROW, $finals);
        unset($finals[$id]);

        $operator = $this->addAtom('Yield', $this->id);
        $operator->ws->opening = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];

        if ($this->nextIs(array($this->phptokens::T_YIELD,
                                ))) {
            $yieldArg = $this->addAtomVoid();
            $this->addLink($operator, $yieldArg, 'YIELD');
            $this->processSingleOperator($operator, $finals, 'YIELD', ' ');
        } elseif ($this->nextIs(array($this->phptokens::T_IS_SMALLER_OR_EQUAL,
                                      $this->phptokens::T_IS_GREATER_OR_EQUAL,
                                      $this->phptokens::T_GREATER			,
                                      $this->phptokens::T_SMALLER			,

                                      $this->phptokens::T_IS_EQUAL		   ,
                                      $this->phptokens::T_IS_NOT_EQUAL	   ,
                                      $this->phptokens::T_IS_IDENTICAL	   ,
                                      $this->phptokens::T_IS_NOT_IDENTICAL   ,
                                      $this->phptokens::T_SPACESHIP		  ,

                                      $this->phptokens::T_PLUS		  ,
                                      $this->phptokens::T_MINUS		  ,
                                      $this->phptokens::T_STAR		  ,
                                      $this->phptokens::T_PERCENTAGE		  ,
                                      $this->phptokens::T_SLASH		  ,
                                      $this->phptokens::T_POW		  ,

                                 ))) {
            $yieldArg = $this->addAtomVoid();
            $this->addLink($operator, $yieldArg, 'YIELD');
            $operator->fullcode = $this->tokens[$this->id][1];
            $this->pushExpression($operator);
            $this->moveToNext();
            $operator = $this->processComparison();
        } else {
            $this->processSingleOperator($operator, $finals, 'YIELD', ' ');
        }

        return $operator;
    }

    private function processYieldfrom(): AtomInterface {
        $operator = $this->addAtom('Yieldfrom', $this->id);
        $operator->ws->opening = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];

        $this->processSingleOperator($operator, $this->precedence->get($this->tokens[$this->id][0]), 'YIELD', ' ');

        $this->checkExpression();

        return $operator;
    }

    private function processNot(): AtomInterface {
        $current = $this->id;
        $finals = array_diff($this->precedence->get($this->tokens[$this->id][0]),
            $this->assignations
        );
        $operator = $this->addAtom('Not', $this->id);
        $operator->ws->opening = $this->tokens[$current][1] . $this->tokens[$current][4];
        $this->processSingleOperator($operator, $finals, 'NOT');

        $this->checkExpression();

        return $operator;
    }

    private function processCurlyExpression(): AtomInterface {
        $current = $this->id;
        $this->moveToNext();
        do {
            $code = $this->processNext();
        } while (!$this->nextIs(array($this->phptokens::T_CLOSE_CURLY)));
        $this->popExpression();

        $block = $this->addAtom('Block', $this->id);
        $block->code     = '{}';
        $block->fullcode = '{' . $code->fullcode . '}';
        $block->ws->opening = $this->tokens[$current + 1][1] . $this->tokens[$current + 1][4];

        $this->addLink($block, $code, 'CODE');

        $this->runPlugins($block, array('CODE' => $code));

        $this->moveToNext(); // Skip }
        $block->ws->closing = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];

        return $block;
    }

    private function processDollar(): AtomInterface {
        $this->contexts->nestContext(Context::CONTEXT_NOSEQUENCE);
        $this->contexts->toggleContext(Context::CONTEXT_NOSEQUENCE);
        $current = $this->id;

        if ($this->nextIs(array($this->phptokens::T_OPEN_CURLY))) {
            $current = $this->id;

            $variable = $this->addAtom('Variable', $current);
            $variable->token = 'T_DOLLAR_OPEN_CURLY_BRACES';

            $this->moveToNext();
            while (!$this->nextIs(array($this->phptokens::T_CLOSE_CURLY))) {
                $this->processNext();
            }

            // Skip }
            $this->moveToNext();

            $expression = $this->popExpression();
            $this->addLink($variable, $expression, 'NAME');

            $variable->fullcode = $this->tokens[$current][1] . '{' . $expression->fullcode . '}';
            $this->runPlugins($variable, array('NAME' => $expression));
            $this->pushExpression($variable);

            $variable->ws->opening  = $this->tokens[$current][1] . $this->tokens[$current][4] .
                                      $this->tokens[$current + 1][1] . $this->tokens[$current + 1][4];
            $variable->ws->closing  = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];

            if ( !$this->contexts->isContext(Context::CONTEXT_NOSEQUENCE) && $this->nextIs(array($this->phptokens::T_CLOSE_TAG))) {
                $this->processSemicolon();
            } elseif (!in_array($this->tokens[$current - 1][0], array($this->phptokens::T_OBJECT_OPERATOR,
                                                                      $this->phptokens::T_NULLSAFE_OBJECT_OPERATOR,
                                                                      $this->phptokens::T_DOUBLE_COLON,
                                                                      ),
                STRICT_COMPARISON)) {
                $variable = $this->processFCOA($variable);
            }
        } else {
            $operator = $this->addAtom('Variable', $this->id);
            $this->processSingleOperator($operator, $this->precedence->get($this->tokens[$this->id][0]), 'NAME');
            $variable = $this->popExpression();
            $variable->ws->opening  = $this->tokens[$current][1] . $this->tokens[$current][4];
            $variable->ws->closing  = '';

            $this->pushExpression($variable);
        }

        $this->contexts->exitContext(Context::CONTEXT_NOSEQUENCE);
        $this->checkExpression();

        return $variable;
    }

    private function processClone(): AtomInterface {
        $operator = $this->addAtom('Clone', $this->id);
        $operator->ws->opening  = $this->tokens[$this->id][1] . $this->tokens[$this->id][4] ;
        $this->processSingleOperator($operator, $this->precedence->get($this->tokens[$this->id][0]), 'CLONE', ' ' );
        $operatorId = $this->popExpression();
        $this->pushExpression($operatorId);

        return $operatorId;
    }

    private function processGoto(): AtomInterface {
        $current = $this->id;

        $label = $this->processNextAsIdentifier(self::WITHOUT_FULLNSPATH);

        $goto = $this->addAtom('Goto', $current);
        $goto->fullcode  = $this->tokens[$current][1] . ' ' . $label->fullcode;
        $goto->ws->opening = $this->tokens[$current][1] . $this->tokens[$current][4];

        $this->addLink($goto, $label, 'GOTO');

        if ($this->currentClassTrait->getCurrent() === ClassTraitContext::NO_CLASS_TRAIT_CONTEXT) {
            $class = '\global';
        } else {
            $class = $this->currentClassTrait->getCurrent()->fullnspath;
        }

        $method = $this->currentFunction->currentFullnspath();

        $this->runPlugins($goto, array('GOTO' => $label));
        $this->calls->addCall(Calls::GOTO, $class . '::' . $method . '..' . $this->tokens[$this->id][1], $goto);
        $this->pushExpression($goto);

        return $goto;
    }

    private function processNoscream(): AtomInterface {
        $current = $this->id;
        $atom = $this->processExpression($this->precedence->get($this->tokens[$this->id][0]));
        $atom->noscream = self::NOSCREAM;
        $atom->ws->noscream = $this->tokens[$current][1] . $this->tokens[$current][4];
        $atom->fullcode = "@{$atom->fullcode}";
        $this->pushExpression($atom);

        $this->checkExpression();

        return $atom;
    }

    private function processNew(): AtomInterface {
        $current = $this->id;

        $this->checkAttribute();

        $this->contexts->toggleContext(Context::CONTEXT_NEW);
        $noSequence = $this->contexts->isContext(Context::CONTEXT_NOSEQUENCE);
        if ($noSequence === false) {
            $this->contexts->toggleContext(Context::CONTEXT_NOSEQUENCE);
        }

        $operator = $this->addAtom('New', $current);
        $operator->fullcode = $this->tokens[$current][1];
        $operator->ws->opening = $this->tokens[$current][1] . $this->tokens[$current][4];
        $newcall = $this->processSingleOperator($operator, $this->precedence->get($this->tokens[$current][0]), 'NEW', ' ');
        $this->runPlugins($newcall, array());
        
        if ($this->currentClassTrait->getCurrent() !== ClassTraitContext::NO_CLASS_TRAIT_CONTEXT && 
        	$newcall->fullnspath === $this->currentClassTrait->getCurrent()->fullnspath) {
            $this->calls->addCall(Calls::METHOD, $this->currentClassTrait->getCurrent()->fullnspath . '::__construct', $newcall);
            $this->currentMethods->addCall('__construct', $newcall);
        }

        $this->contexts->toggleContext(Context::CONTEXT_NEW);
        if ($noSequence === false) {
            $this->contexts->toggleContext(Context::CONTEXT_NOSEQUENCE);
        }

        $operator = $this->popExpression();
        $this->pushExpression($operator);

        $this->checkExpression();

        return $operator;
    }

    //////////////////////////////////////////////////////
    /// processing binary operators
    //////////////////////////////////////////////////////
    private function processSign(): AtomInterface {
        $current = $this->id;
        $signExpression = $this->tokens[$this->id][1];
        $whitespaces    = array($this->tokens[$this->id][1] . $this->tokens[$this->id][4]);
        while ($this->nextIs(array($this->phptokens::T_PLUS,
                                   $this->phptokens::T_MINUS))) {
            $this->moveToNext();
            $signExpression = $this->tokens[$this->id][1] . $signExpression;
            $whitespaces[] = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];
        }

        if (($this->nextIs(array($this->phptokens::T_LNUMBER)) ||
             $this->nextIs(array($this->phptokens::T_DNUMBER))) &&
             !$this->nextIs(array($this->phptokens::T_POW), 2)) {
            $operand = $this->processNext();

            $operand->code     = $signExpression . $operand->code;
            $operand->fullcode = $signExpression . $operand->fullcode;
            $operand->token    = $this->getToken($this->tokens[$this->id][0]);
            $this->runPlugins($operand);

            return $operand;
        }

        $finals = $this->precedence->get($this->tokens[$this->id][0]);
        $finals[] = '-';
        $finals[] = '+';

        $noSequence = $this->contexts->isContext(Context::CONTEXT_NOSEQUENCE);
        if ($noSequence === false) {
            $this->contexts->toggleContext(Context::CONTEXT_NOSEQUENCE);
        }
        do {
            $this->processNext();
        } while (!$this->nextIs($finals));
        if ($noSequence === false) {
            $this->contexts->toggleContext(Context::CONTEXT_NOSEQUENCE);
        }
        $signed = $this->popExpression();
        $firstSigned = $signed;

        for ($i = strlen($signExpression) - 1; $i >= 0; --$i) {
            $sign = $this->addAtom('Sign', $current);
            $this->addLink($sign, $signed, 'SIGN');

            $sign->code     = $signExpression[$i];
            $sign->fullcode = $signExpression[$i] . $signed->fullcode;

            $signed = $sign;
        }
        $signed->ws->opening = implode('', $whitespaces);
        $this->runPlugins($signed, array('SIGN' => $firstSigned));

        $this->pushExpression($signed);

        $this->checkExpression();
        return $signed;
    }

    private function processAddition(): AtomInterface {
        if (!$this->hasExpression() ||
            $this->tokens[$this->id - 1][0] === $this->phptokens::T_DOT
        ) {
            return $this->processSign();
        }

        $finals = $this->precedence->get($this->tokens[$this->id][0], Precedence::WITH_SELF);
        $finals = array_diff($finals, $this->assignations);
        $finals = array_unique($finals);

        return $this->processOperator('Addition', $finals, array('LEFT', 'RIGHT'));
    }

    private function processBreak(): AtomInterface {
        $current = $this->id;
        $break = $this->addAtom($this->nextIs(array($this->phptokens::T_BREAK), 0) ? 'Break' : 'Continue', $current);

        if ($this->nextIs(array($this->phptokens::T_LNUMBER))) {
            $noSequence = $this->contexts->isContext(Context::CONTEXT_NOSEQUENCE);
            if ($noSequence === false) {
                $this->contexts->toggleContext(Context::CONTEXT_NOSEQUENCE);
            }

            $this->moveToNext();
            $breakLevel = $this->processInteger();
            $this->popExpression();

            if ($noSequence === false) {
                $this->contexts->toggleContext(Context::CONTEXT_NOSEQUENCE);
            }
        } elseif ($this->nextIs(array($this->phptokens::T_OPEN_PARENTHESIS))) {
            $this->moveToNext(); // skip (
            $this->processNext();
            $this->moveToNext(); // skip )

            $breakLevel = $this->popExpression();
        } elseif ($this->nextIs(array($this->phptokens::T_CLOSE_TAG)) ||
                  $this->nextIs(array($this->phptokens::T_SEMICOLON ))) {
            $breakLevel = $this->addAtomVoid();
        } else {
            $this->processNext();

            $breakLevel = $this->popExpression();
        }

        $link = $this->tokens[$current][0] === $this->phptokens::T_BREAK ? 'BREAK' : 'CONTINUE';
        $this->addLink($break, $breakLevel, $link);
        $break->fullcode = $this->tokens[$current][1] . ( $breakLevel->atom !== 'Void' ? ' ' . $breakLevel->fullcode : '');
        $break->ws->opening = $this->tokens[$current][1] . $this->tokens[$current][4];

        $this->runPlugins($break, array($link => $breakLevel));
        $this->pushExpression($break);

        $this->checkExpression();

        return $break;
    }

    private function processDoubleColon(): AtomInterface {
        $current = $this->id;

        $left = $this->popExpression();
        assert($left->atom !== 'Void', 'Poping a void expression!');

        $this->checkPhpdoc();

        $this->contexts->nestContext(Context::CONTEXT_NEW);
        $this->contexts->nestContext(Context::CONTEXT_NOSEQUENCE);
        $this->contexts->toggleContext(Context::CONTEXT_NOSEQUENCE);
        if ($this->nextIs(array($this->phptokens::T_OPEN_CURLY))) {
            $right = $this->processCurlyExpression();
        } elseif ($this->nextIs(array($this->phptokens::T_DOLLAR))) {
            $this->moveToNext(); // Skip ::
            $right = $this->processDollar();
            $this->popExpression();
        } elseif ($this->nextIs(array($this->phptokens::T_CLASS))) {
            if ($this->nextIs(array($this->phptokens::T_OPEN_PARENTHESIS), 2)) {
                $this->moveToNext();
                $right = $this->processSingle('Name');
            } else {
                $right = $this->tokens[$this->id + 1][1];
                $this->moveToNext(); // Skip ::
            }
        } elseif ($this->nextIs(array($this->phptokens::T_VARIABLE))) {
            $this->moveToNext();
            $right = $this->processSingle('Staticpropertyname');
        } else {
            $right = $this->processNextAsIdentifier(self::WITHOUT_FULLNSPATH);
        }

        if ($this->nextIs(array($this->phptokens::T_OPEN_PARENTHESIS))) {
            $this->pushExpression($right);
            $right = $this->processFunctioncall(self::WITHOUT_FULLNSPATH);
            $this->popExpression();
        }

        $this->contexts->exitContext(Context::CONTEXT_NEW);
        $this->contexts->exitContext(Context::CONTEXT_NOSEQUENCE);

        // @todo : remove $right is a string (cf L 6568, to make $right always an object)
        if (is_string($right) && mb_strtolower($right) === 'class') {
            $static = $this->addAtom('Staticclass', $current);
            $fullcode = "$left->fullcode::$right";
            $static->ws->closing = $this->tokens[$current][1] . $this->tokens[$current][4] . $this->tokens[$current + 1][1] . $this->tokens[$current + 1][4];

            if (!$left->isA(array('Functioncall', 'Methodcall', 'Staticmethodcall'))) {
                $this->calls->addCall(Calls::A_CLASS, $left->fullnspath, $left);
            }
            // We are not sending $left, as it has no impact
            $this->runPlugins($left);
            $this->runPlugins($static, array('CLASS' => $left));
            // This should actually be the value of any USE statement
            if (($use = $this->uses->get('class', mb_strtolower($left->fullcode))) instanceof AtomInterface) {
                $noDelimiter = $use->fullcode;
                if (($length = strpos($noDelimiter, ' ')) !== false) {
                    $noDelimiter = substr($noDelimiter, 0, $length);
                }
                $static->noDelimiter = $noDelimiter;
            } else {
                $static->noDelimiter = $left->fullcode;
            }
        } elseif ($right->isA(array('Name'))) {
            $static = $this->addAtom('Staticconstant', $current);
            $this->addLink($static, $right, 'CONSTANT');
            $fullcode = "{$left->fullcode}::{$right->fullcode}";

            if ($left->isA(array('Identifier', 'Nsname', 'Parent', 'Static', 'Self'))) {
                $this->calls->addCall(Calls::A_CLASS, $left->fullnspath, $left);

                $static->fullnspath = "{$left->fullnspath}::{$right->fullcode}";
            }

            $this->runPlugins($static, array('CLASS'    => $left,
                                             'CONSTANT' => $right));
        } elseif ($right->isA(array('Variable',
                                    'Array',
                                    'Arrayappend',
                                    'MagicConstant',
                                    'Concatenation',
                                    'Boolean',
                                    'Null',
                                    'Staticpropertyname',
                                    ))) {
            $static = $this->addAtom('Staticproperty', $current);

            if ($left->isA(array('Identifier', 'Nsname', 'Parent', 'Static', 'Self'))) {
                $this->calls->addCall(Calls::A_CLASS, $left->fullnspath, $left);

                $static->fullnspath = "{$left->fullnspath}::{$right->fullcode}";
            }
            $this->addLink($static, $right, 'MEMBER');
            $fullcode = "{$left->fullcode}::{$right->fullcode}";

            $this->runPlugins($static, array('CLASS'  => $left,
                                             'MEMBER' => $right));
        } elseif ($right->isA(array('Block', ))) {
            $static = $this->addAtom('Staticconstant', $current);

            $this->addLink($static, $right, 'CONSTANT');
            $fullcode = "{$left->fullcode}::{$right->fullcode}";

            $this->runPlugins($static, array('CLASS'  => $left,
                                             'CONSTANT' => $right));
        } elseif ($right->isA(array('Methodcallname', 'Callable'))) {
            if ($right->isA(array('Callable'))) {
                $static = $this->addAtom('Callable', $current);
            } else {
                $static = $this->addAtom('Staticmethodcall', $current);
            }
            $this->addLink($static, $right, 'METHOD');

            if ($left->isA(array('Identifier', 'Nsname', 'Parent', 'Static', 'Self'))) {
                $this->calls->addCall(Calls::A_CLASS, $left->fullnspath, $left);

                $static->fullnspath = $left->fullnspath . '::' . mb_strtolower($right->code);
            }
            $fullcode = "{$left->fullcode}::{$right->fullcode}";
            $this->runPlugins($static, array('CLASS'  => $left,
                                             'METHOD' => $right));
        } else {
            throw new LoadError('Unprocessed atom in static call (right) : ' . $right->atom . ':' . $this->filename . ':' . __LINE__);
        }
        $this->makePhpdoc($static);

        $this->addLink($static, $left, 'CLASS');
        if ($static->atom  === 'Staticproperty'                                                  &&
            in_array($left->token, array('T_STRING', 'T_STATIC'), STRICT_COMPARISON)             &&
            $this->currentClassTrait->getCurrent() !== ClassTraitContext::NO_CLASS_TRAIT_CONTEXT &&
            ($left->fullnspath === $this->currentClassTrait->getCurrent()->fullnspath ||
             $left->fullnspath === '\\parent')) {
            $name = ltrim($right->code, '$');
            if (!empty($name)) {
                $this->currentProperties->addCall($name, $static);
            }
        }

        if ($static->atom  === 'Staticmethodcall'                                                &&
            in_array($left->token, array('T_STRING', 'T_STATIC'), STRICT_COMPARISON)             &&
            $this->currentClassTrait->getCurrent() !== ClassTraitContext::NO_CLASS_TRAIT_CONTEXT &&
            ($left->fullnspath === $this->currentClassTrait->getCurrent()->fullnspath ||
            $left->fullnspath === '\\parent')) {

            $this->currentMethods->addCall(mb_strtolower($right->code), $static);
        }

        $static->fullcode = $fullcode;
        $static->ws->opening  = '';
        $static->ws->operator = '::' . $this->tokens[$current][4];

        if (!empty($left->fullnspath)) {
            if ($static->isA(array('Staticmethodcall', 'Staticmethod'))) {
                $name = mb_strtolower($right->code);
                $this->calls->addCall(Calls::STATICMETHOD,  "$left->fullnspath::$name", $static);

                //
                if ($left->isA(array('Self', 'Static', 'Parent'))) {
                    $this->calls->addCall(Calls::METHOD,  "$left->fullnspath::$name", $static);
                } elseif ($this->currentClassTrait->getCurrent()!== null &&
                          $this->currentClassTrait->getCurrent()->fullnspath === $left->fullnspath) {
                    $this->calls->addCall(Calls::METHOD,  "$left->fullnspath::$name", $static);
                }
            } elseif ($static->atom === 'Staticconstant') {
                $this->calls->addCall(Calls::STATICCONSTANT,  "$left->fullnspath::$right->code", $static);
            } elseif ($static->atom === 'Staticproperty' && ($right->token === 'T_VARIABLE')) {
                $this->calls->addCall(Calls::STATICPROPERTY, "$left->fullnspath::$right->code", $static);
            }
        }

        $this->pushExpression($static);

        if ( !$this->contexts->isContext(Context::CONTEXT_NOSEQUENCE) && $this->nextIs(array($this->phptokens::T_CLOSE_TAG))) {
            $this->processSemicolon();
        } else {
            $static = $this->processFCOA($static);
        }

        return $static;
    }

    private function processOperator(string $atom, array $finals, array $links = array('LEFT', 'RIGHT')): AtomInterface {
        $current = $this->id;
        $operator = $this->addAtom($atom, $current);
        $this->makePhpdoc($operator);

        $left = $this->popExpression();
        assert($left->atom !== 'Void', "Poping a void expression on $this->filename: " . $this->tokens[$this->id][2]);
        $this->addLink($operator, $left, $links[0]);

        $this->contexts->nestContext(Context::CONTEXT_NOSEQUENCE);
        $this->contexts->toggleContext(Context::CONTEXT_NOSEQUENCE);
        do {
            $right = $this->processNext();

            if ($this->nextIs($this->assignations)) {
                $right = $this->processNext();
            }
            $this->checkPhpdoc();
        } while (!$this->nextIs($finals) );

        $this->contexts->exitContext(Context::CONTEXT_NOSEQUENCE);
        $this->popExpression();

        $this->addLink($operator, $right, $links[1]);

        // This adds DEFAULT to local variables.
        if ($operator->code === '='        &&
            $left->atom     === 'Variable' &&
            $this->currentVariables->exists($left->code)) {
            $this->addLink($this->currentVariables->get($left->code), $right, 'DEFAULT');
        }

        $operator->fullcode  = $left->fullcode . ' ' . $this->tokens[$current][1] . ' ' . $right->fullcode;
        $operator->ws->operator = $this->tokens[$current][1] . $this->tokens[$current][4];

        $extras = array($links[0] => $left, $links[1] => $right);
        $this->runPlugins($operator, $extras);

        $this->pushExpression($operator);
        $this->checkExpression();

        return $operator;
    }

    private function processObjectOperator(): AtomInterface {
        $current = $this->id;

        $left = $this->popExpression();
        assert($left->atom !== 'Void', 'Poping a void expression!');
        if ($this->currentVariables->exists($left->code)) {
            $cv = $this->currentVariables->get($left->code);
            $left->isPhp      = $cv->isPhp;
            $left->isExt      = $cv->isExt;
            $left->isStub     = $cv->isStub;
            $left->fullnspath = $cv->fullnspath;
        }
        $this->checkPhpdoc();

        $this->contexts->nestContext(Context::CONTEXT_NEW);
        $this->contexts->nestContext(Context::CONTEXT_NOSEQUENCE);
        $this->contexts->toggleContext(Context::CONTEXT_NOSEQUENCE);
        if ($this->nextIs(array($this->phptokens::T_OPEN_CURLY))) {
            $right = $this->processCurlyExpression();
        } elseif ($this->nextIs(array($this->phptokens::T_VARIABLE))) {
            $this->moveToNext();
            $right = $this->processSingle('Variable');
            $right->ws->opening = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];
            $right->ws->closing = '';
        } elseif ($this->nextIs(array($this->phptokens::T_DOLLAR))) {
            $this->moveToNext();
            $right = $this->processDollar();
            $this->popExpression();
        } else {
            $right = $this->processNextAsIdentifier(self::WITHOUT_FULLNSPATH);
        }

        if ($this->nextIs(array($this->phptokens::T_OPEN_PARENTHESIS))) {
            $this->pushExpression($right);
            $right = $this->processFunctioncall(self::WITHOUT_FULLNSPATH);
            $this->popExpression();
        }

        $this->contexts->exitContext(Context::CONTEXT_NEW);
        $this->contexts->exitContext(Context::CONTEXT_NOSEQUENCE);

        if ($right->isA(array('Variable',
                              'Array',
                              'Name',
                              'Concatenation',
                              'Arrayappend',
                              'Member',
                              'MagicConstant',
                              'Block',
                              'Boolean',
                              'Null',
                              ))) {
            $static = $this->addAtom('Member', $current);
            $links = 'MEMBER';
            $static->enclosing = self::NO_ENCLOSING;
        } elseif ($right->isA(array('Closure'))) {
            $static = $this->addAtom('Closure', $current);
            $links = 'METHOD';
        } elseif ($right->isA(array('Callable'))) {
            $static = $this->addAtom('Callable', $current);
            $links = 'METHOD';
        } elseif ($right->isA(array('Methodcallname', 'Methodcall'))) {
            $static = $this->addAtom('Methodcall', $current);
            $links = 'METHOD';
        } else {
            throw new LoadError('Unprocessed atom in object call (right) : ' . $right->atom . ':' . $this->filename . ':' . __LINE__);
        }

        $this->addLink($static, $left, 'OBJECT');
        $this->addLink($static, $right, $links);

        $static->fullcode     = $left->fullcode . $this->tokens[$current][1] . $right->fullcode;
        $static->ws->opening  = '';
        $static->ws->operator = $this->tokens[$current][1] . $this->tokens[$current][4];

        if ($left->atom === 'This' ) {
            if ($static->atom === 'Methodcall') {
                $this->calls->addCall(Calls::METHOD, $left->fullnspath . '::' . mb_strtolower($right->code), $static);
                $this->currentMethods->addCall(mb_strtolower($right->code), $static);
                $static->fullnspath = $left->fullnspath . '::' . mb_strtolower($right->code);
            } elseif ($static->atom  === 'Member'   &&
                      $right->token  === 'T_STRING') {
                $this->calls->addCall(Calls::PROPERTY, "{$left->fullnspath}::{$right->code}", $static);
                $this->currentProperties->addCall($right->code, $static);
            }
        }
        $this->runPlugins($static, array('OBJECT' => $left,
                                         $links   => $right,
                                         ));
        $this->pushExpression($static);

        if ( !$this->contexts->isContext(Context::CONTEXT_NOSEQUENCE) && $this->nextIs(array($this->phptokens::T_CLOSE_TAG))) {
            $this->processSemicolon();
        } else {
            $static = $this->processFCOA($static);
        }

        return $static;
    }

    private function processAssignation(): AtomInterface {
        $finals = $this->precedence->get($this->tokens[$this->id][0]);
        $finals = array_merge($finals, $this->assignations);

        return $this->processOperator('Assignation', $finals);
    }

    private function processCoalesce(): AtomInterface {
        return $this->processOperator('Coalesce', $this->precedence->get($this->tokens[$this->id][0], Precedence::WITH_SELF));
    }

    private function processEllipsis(): AtomInterface {
        $current = $this->id;
        // Simply skipping the ...
        $finals = $this->precedence->get($this->phptokens::T_ELLIPSIS);
        do {
            $operand = $this->processNext();
        } while (!$this->nextIs($finals));

        $this->popExpression();
        $operand->fullcode     = '...' . $operand->fullcode;
        $operand->variadic     = self::VARIADIC;
        $operand->ws->ellipsis =  $this->tokens[$current][1] . $this->tokens[$current][4];

        $this->pushExpression($operand);

        return $operand;
    }

    private function processAnd(): AtomInterface {
        if ($this->hasExpression()) {
            return $this->processOperator('Bitoperation', $this->precedence->get($this->tokens[$this->id][0]));
        }

        $current = $this->id;
        // Simply skipping the &
        do {
            $operand = $this->processNext();
        } while ($this->nextIs(array($this->phptokens::T_DOUBLE_COLON,
                                     $this->phptokens::T_OBJECT_OPERATOR,
                                     $this->phptokens::T_NULLSAFE_OBJECT_OPERATOR,
                                     )));

        $this->popExpression();
        $operand->fullcode  = '&' . $operand->fullcode;
        $operand->reference = self::REFERENCE;
        $operand->ws->reference = '&' . $this->tokens[$current][4];

        $this->pushExpression($operand);

        return $operand;
    }

    private function processLogical(): AtomInterface {
        return $this->processOperator('Logical', $this->precedence->get($this->tokens[$this->id][0]));
    }

    private function processBitoperation(): AtomInterface {
        return $this->processOperator('Bitoperation', $this->precedence->get($this->tokens[$this->id][0]));
    }

    private function processMultiplication(): AtomInterface {
        return $this->processOperator('Multiplication', $this->precedence->get($this->tokens[$this->id][0], Precedence::WITH_SELF));
    }

    private function processPower(): AtomInterface {
        return $this->processOperator('Power', $this->precedence->get($this->tokens[$this->id][0], Precedence::WITH_SELF));
    }

    private function processSpaceship(): AtomInterface {
        return $this->processOperator('Spaceship', $this->precedence->get($this->tokens[$this->id][0]));
    }

    private function processComparison(): AtomInterface {
        return $this->processOperator('Comparison', $this->precedence->get($this->tokens[$this->id][0]));
    }

    private function processDot(): AtomInterface {
        $concatenation = $this->addAtom('Concatenation', $this->id);
        $fullcode      = array();
        $concat        = array();
        $noDelimiter   = '';
        $rank          = -1;

        $contains       = $this->popExpression();
        $contains->rank = ++$rank;
        $fullcode[]     = $contains->fullcode;
        $concat[]       = $contains;
        $noDelimiter   .= $contains->noDelimiter;
        $this->addLink($concatenation, $contains, 'CONCAT');
        $concatenation->ws->opening = '';
        $concatenation->ws->separators[] = '.' . $this->tokens[$this->id][4];
        $concatenation->ws->closing = '';

        $this->contexts->nestContext(Context::CONTEXT_NOSEQUENCE);
        $this->contexts->toggleContext(Context::CONTEXT_NOSEQUENCE);

        $finals = $this->precedence->get($this->tokens[$this->id][0]);
        $finals = array_diff($finals, array($this->phptokens::T_REQUIRE,
                                            $this->phptokens::T_REQUIRE_ONCE,
                                            $this->phptokens::T_INCLUDE,
                                            $this->phptokens::T_INCLUDE_ONCE,
                                            $this->phptokens::T_PRINT,
                                            $this->phptokens::T_ECHO,
                                            $this->phptokens::T_YIELD,
                                            $this->phptokens::T_YIELD_FROM,
                                            // This is for 'a' . -$y
                                            $this->phptokens::T_PLUS,
                                            $this->phptokens::T_MINUS,
                                            ));

        while (!$this->nextIs($finals)) {
            $contains = $this->processNext();
            $this->checkPhpdoc();

            if ($this->nextIs(array($this->phptokens::T_DOT))) {
                $this->popExpression();
                $this->addLink($concatenation, $contains, 'CONCAT');
                $fullcode[]     = $contains->fullcode;
                $concat[]       = $contains;
                $noDelimiter   .= $contains->noDelimiter;
                $contains->rank = ++$rank;

                $this->moveToNext();
                $concatenation->ws->separators[] = '.' . $this->tokens[$this->id][4];
            }
        }

        $this->contexts->exitContext(Context::CONTEXT_NOSEQUENCE);

        $this->popExpression();
        $this->addLink($concatenation, $contains, 'CONCAT');
        $fullcode[]     = $contains->fullcode;
        $concat[]       = $contains;
        $noDelimiter   .= $contains->noDelimiter;
        $contains->rank = ++$rank;

        $concatenation->fullcode    = implode(' . ', $fullcode);
        $concatenation->noDelimiter = $noDelimiter;
        $concatenation->count       = $rank + 1;

        $this->pushExpression($concatenation);
        $this->runPlugins($concatenation, $concat);
        $this->calls->addNoDelimiterCall($concatenation);

        $this->checkExpression();

        return $concatenation;
    }

    private function processInstanceof(): AtomInterface {
        $current = $this->id;
        $instanceof = $this->addAtom('Instanceof', $current);

        $left = $this->popExpression();
        assert($left->atom !== 'Void', 'Poping a void expression!');
        $this->addLink($instanceof, $left, 'VARIABLE');

        $finals = $this->precedence->get($this->tokens[$this->id][0]);
        do {
            $right = $this->processNext();
        } while (!$this->nextIs($finals));
        $this->popExpression();

        $this->addLink($instanceof, $right, 'CLASS');

        $this->getFullnspath($right, 'class', $right);
        $this->calls->addCall(Calls::A_CLASS, $right->fullnspath, $right);

        $instanceof->fullcode = $left->fullcode . ' ' . $this->tokens[$current][1] . ' ' . $right->fullcode;
        $instanceof->ws->opening  = $this->tokens[$current][1] . $this->tokens[$current][4];

        $this->runPlugins($instanceof, array('VARIABLE' => $left,
                                             'CLASS'    => $right));
        $this->pushExpression($instanceof);

        return $instanceof;
    }

    private function processKeyvalue(): AtomInterface {
        return $this->processOperator('Keyvalue', $this->precedence->get($this->tokens[$this->id][0]), array('INDEX', 'VALUE'));
    }

    private function processPhpdoc(): AtomInterface {
        $id = count($this->phpDocs);
        $this->phpDocs[$id] = $this->tokens[$this->id];
        $this->phpDocs[$id]['id'] = $this->id;

        return $this->atomVoid;
    }

    private function makePhpdoc(AtomInterface $node): void {
        foreach ($this->phpDocs as $phpDoc) {
            $atom = $this->addAtom('Phpdoc', $phpDoc['id']);
            $atom->code = $phpDoc[1];
            $atom->fullcode = $phpDoc[1];
            $atom->ws->closing = $phpDoc[4];
            $this->addLink($node, $atom, 'PHPDOC');
        }

        $this->phpDocs = array();
    }

    private function processAttribute(): AtomInterface {
        do {
            // This is for the final comma , ]
            if ($this->nextIs(array($this->phptokens::T_CLOSE_BRACKET))) {
                $this->moveToNext();
                break;
            }

            $current = $this->id;
            // Attributes are only classes, not functions
            $this->contexts->toggleContext(Context::CONTEXT_NEW);
            $attribute = $this->processNext();
            $this->runPlugins($attribute, array());

            $this->contexts->toggleContext(Context::CONTEXT_NEW);
            $attribute->ws->opening = $this->tokens[$current][1] . $this->tokens[$current][4];

            // @todo : currently handled as a 'new'
            // This may be a methodcall, with a wrongly build fullnspath.
            if (($id = strpos($attribute->fullnspath, '(')) !== false) {
                $attribute->fullnspath = substr($attribute->fullnspath, 0, $id);
            }
            $this->calls->addCall(Calls::A_CLASS, $attribute->fullnspath, $attribute);

            $this->popExpression();
            $attribute->ws->closing .= $this->tokens[$this->id + 1][1] . $this->tokens[$this->id + 1][4];

            $this->attributes[] = $attribute;
            $this->moveToNext(); // skip ]
        } while ($this->nextIs(array($this->phptokens::T_COMMA), 0));

        return $attribute;
    }

    private function processBitshift(): AtomInterface {
        // Classic bitshift expression
        return $this->processOperator('Bitshift', $this->precedence->get($this->tokens[$this->id][0]));
    }

    private function processIsset(): AtomInterface {
        $current = $this->id;

        $atom = ucfirst(mb_strtolower($this->tokens[$current][1]));
        $this->moveToNext();
        $argumentsList = array();
        $functioncall = $this->processArguments($atom, array(), $argumentsList);
        $this->makePhpdoc($functioncall);

        $argumentsFullcode = $functioncall->fullcode;

        $functioncall->code       = $this->tokens[$current][1];
        $functioncall->fullcode   = $this->tokens[$current][1] . '(' . $argumentsFullcode . ')';
        $functioncall->token      = $this->getToken($this->tokens[$current][0]);
        $functioncall->fullnspath = '\\' . mb_strtolower($this->tokens[$current][1]);
        $functioncall->ws->opening = $this->tokens[$current][1] . $this->tokens[$current][4] . $this->tokens[$current + 1][1] . $this->tokens[$current + 1][4];
        $functioncall->ws->closing = $this->tokens[$this->id][1] . $this->tokens[$this->id][4];

        $this->runPlugins($functioncall, $argumentsList);

        $this->pushExpression($functioncall);

        $this->checkExpression();

        return $functioncall;
    }

    private function processEcho(): AtomInterface {
        $current = $this->id;

        $argumentsList = array();
        $functioncall = $this->processArguments('Echo',
            array($this->phptokens::T_SEMICOLON,
                  $this->phptokens::T_CLOSE_TAG,
                  $this->phptokens::T_END,
                 ),
            $argumentsList);
        $argumentsFullcode = $functioncall->fullcode;

        $functioncall->code        = $this->tokens[$current][1];
        $functioncall->fullcode    = $this->tokens[$current][1] . ' ' . $argumentsFullcode;
        $functioncall->token       = $this->getToken($this->tokens[$current][0]);
        $functioncall->fullnspath  = '\\' . mb_strtolower($this->tokens[$current][1]);
        $functioncall->ws->opening = $this->tokens[$current][1] . $this->tokens[$current][4];
        $functioncall->ws->closing = '';

        $this->pushExpression($functioncall);

        $this->runPlugins($functioncall, $argumentsList);

        // processArguments goes too far, up to ;
        --$this->id;

        if ($this->nextIs(array($this->phptokens::T_CLOSE_TAG))) {
            $this->processSemicolon();
        }

        return $functioncall;
    }

    private function processHalt(): AtomInterface {
        $halt = $this->addAtom('Halt', $this->id);
        $halt->fullcode = $this->tokens[$this->id][1];
        $halt->ws->opening = $this->tokens[$this->id][1] . $this->tokens[$this->id][4] .
                             $this->tokens[$this->id + 1][1] . $this->tokens[$this->id + 1][4] .  // (
                             $this->tokens[$this->id + 2][1] . $this->tokens[$this->id + 2][4] .  // )
                             $this->tokens[$this->id + 3][1] . $this->tokens[$this->id + 3][4] ;  // ;


        $this->moveToNext(); // skip halt
        $this->moveToNext(); // skip (
        // Skipping all arguments. This is not a function!
        $this->moveToNext(); // skip ;

        $this->sequence->ws->separators[] = '';
        $this->addToSequence($halt);

        return $halt;
    }

    private function processPrint(): AtomInterface {
        $current = $this->id;

        if ($this->nextIs(array($this->phptokens::T_INCLUDE,
                                $this->phptokens::T_INCLUDE_ONCE,
                                $this->phptokens::T_REQUIRE,
                                $this->phptokens::T_REQUIRE_ONCE,
                                ), 0)) {
            $functioncall = $this->addAtom('Include', $current);
        } else {
            $functioncall = $this->addAtom('Print', $current);
        }
        $this->makePhpdoc($functioncall);

        $noSequence = $this->contexts->isContext(Context::CONTEXT_NOSEQUENCE);
        if ($noSequence === false) {
            $this->contexts->toggleContext(Context::CONTEXT_NOSEQUENCE);
        }

        $finals = $this->precedence->get($this->tokens[$this->id][0]);
        while (!$this->nextIs($finals)) {
            $this->processNext();
        }
        if ($noSequence === false) {
            $this->contexts->toggleContext(Context::CONTEXT_NOSEQUENCE);
        }

        $index = $this->popExpression();
        $index->rank = 0;
        $this->addLink($functioncall, $index, 'ARGUMENT');

        $functioncall->fullcode   = $this->tokens[$current][1] . ' ' . $index->fullcode;
        $functioncall->count      = 1; // Only one argument for print
        $functioncall->fullnspath = '\\' . mb_strtolower($this->tokens[$current][1]);
        $functioncall->ws->opening = $this->tokens[$current][1] . $this->tokens[$current][4];
        $functioncall->ws->closing = '';

        $this->pushExpression($functioncall);
        $this->runPlugins($functioncall, array('ARGUMENT' => $index));

        $this->checkExpression();

        return $functioncall;
    }

    //////////////////////////////////////////////////////
    /// generic methods
    //////////////////////////////////////////////////////
    private function addAtom(string $atomName, int $id = null): AtomInterface {
        if (!in_array($atomName, GraphElements::$ATOMS, STRICT_COMPARISON)) {
            throw new LoadError('Undefined atom ' . $atomName . ':' . $this->filename . ':' . __LINE__);
        }

        $line = $this->tokens[$this->id][2] ?? $this->tokens[$this->id - 1][2] ?? $this->tokens[$this->id - 2][2] ?? -1;
        $atom = $this->atomGroup->factory($atomName, $line, $this->tokens[$this->id][4] ?? '');

        if ($id !== null) {
            $atom->code  = $this->tokens[$id][1];
            $atom->token = $this->getToken($this->tokens[$id][0]);
        }

        $this->atoms[$atom->id] = $atom;
        if ($atom->id < $this->minId) {
            $this->minId = $atom->id;
        }

        return $atom;
    }

    private function addAtomVoid(): AtomInterface {
        $void = $this->addAtom('Void');
        $void->code        = 'Void';
        $void->fullcode    = self::FULLCODE_VOID;
        $void->token       = $this->phptokens::T_VOID;
        $this->makePhpdoc($void);

        $this->runPlugins($void);

        return $void;
    }

    private function addLink(AtomInterface $origin, AtomInterface $destination, string $label): void {
        if (!in_array($label, array_merge(GraphElements::$LINKS, GraphElements::$LINKS_EXAKAT), STRICT_COMPARISON)) {
            debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            throw new LoadError('Undefined link ' . $label . ' for atom ' . $origin->atom . ' : ' . $this->filename . ':' . $origin->line);
        }

        if ($origin->id === 2) {
            print_r($origin);
            print_r($this->filename . PHP_EOL);
            die(o);
        }
        if ($destination->id === 2) {
            print_r($origin);
            print_r($this->filename . PHP_EOL);
            die(d);
        }
        $this->links[] = array($label, $origin->id, $destination->id);
    }

    private function pushExpression(AtomInterface $atom): void {
        $this->expressions[] = $atom;
    }

    private function hasExpression(): bool {
        return !empty($this->expressions);
    }

    private function popExpression(): AtomInterface {
        if (empty($this->expressions)) {
            $id = $this->addAtomVoid();
        } else {
            $id = array_pop($this->expressions);
        }

        return $id;
    }

    private function checkTokens(string $filename): void {
        if (!empty($this->expressions)) {
            debug_print_backtrace();
            throw new LoadError( "Warning : expression is not empty in $filename : " . count($this->expressions));
        }

        if (($count = $this->contexts->getCount(Context::CONTEXT_NOSEQUENCE)) !== false) {
            throw new LoadError( "Warning : context for sequence is not back to 0 in $filename\n");
        }

        if (($count = $this->contexts->getCount(Context::CONTEXT_NEW)) !== false) {
            throw new LoadError( "Warning : context for new is not back to 0 in $filename\n");
        }

        if (($count = $this->contexts->getCount(Context::CONTEXT_FUNCTION)) !== false) {
            throw new LoadError( "Warning : context for function is not back to 0 in $filename\n");
        }

        if (($count = $this->contexts->getCount(Context::CONTEXT_CLASS)) !== false) {
            throw new LoadError( "Warning : context for class is not back to 0 in $filename\n");
        }
    }

    private function processDefineAsClassalias(array $argumentsId): void {
        if (!isset($argumentsId[0]) ||
            $argumentsId[0]->atom === 'Void') {
            return;
        }

        if (!isset($argumentsId[0], $argumentsId[1])) {
            // no/partial argument used with class_alias. Just bail.
            return;
        }

        if (empty($argumentsId[0]->noDelimiter) ||
            empty($argumentsId[1]->noDelimiter)   ) {
            $argumentsId[0]->fullnspath = '\\'; // cancels it all
            $argumentsId[1]->fullnspath = '\\';

            return;
        }

        if (preg_match('/[$ #?;%^\*\'\"\. <>~&,|\(\){}\[\]\/\s=+!`@\-]/is', $argumentsId[0]->noDelimiter)) {
            $argumentsId[0]->fullnspath = '\\'; // cancels it all
            $argumentsId[1]->fullnspath = '\\';

            return; // Can't be a class anyway.
        }

        if (preg_match('/[$ #?;%^\*\'\"\. <>~&,|\(\){}\[\]\/\s=+!`@\-]/is', $argumentsId[1]->noDelimiter)) {
            $argumentsId[0]->fullnspath = '\\'; // cancels it all
            $argumentsId[1]->fullnspath = '\\';

            return; // Can't be a class anyway.
        }

        $fullnspathClass = makeFullNsPath($argumentsId[0]->noDelimiter, \FNP_NOT_CONSTANT);
        $argumentsId[0]->fullnspath = $fullnspathClass;

        $fullnspathAlias = makeFullNsPath($argumentsId[1]->noDelimiter, \FNP_NOT_CONSTANT);
        $argumentsId[1]->fullnspath = $fullnspathAlias;

        $this->calls->addCall(Calls::A_CLASS, $fullnspathClass, $argumentsId[0]);
        $this->calls->addDefinition(Calls::A_CLASS, $fullnspathAlias, $argumentsId[1]);
    }

    private function processDefineAsConstants(AtomInterface $const, AtomInterface $name, bool $caseInsensitive = self::CASE_INSENSITIVE): void {
        if (empty($name->noDelimiter)) {
            $name->fullnspath = '\\';
            return;
        }

        if (preg_match('/[$ #?;%^\*\'\"\. <>~&,|\(\){}\[\]\/\s=+!`@\-]/is', $name->noDelimiter)) {
            return; // Can't be a constant anyway.
        }

        $fullnspath = makeFullNsPath($name->noDelimiter, \FNP_CONSTANT);
        if ($name->noDelimiter[0] === '\\') {
            // Added a second \\ when the string already has one. Actual PHP behavior
            $fullnspath = "\\$fullnspath";
        }

        $this->calls->addDefinition(Calls::CONST, $fullnspath, $const);
        $name->fullnspath = $fullnspath;

        if ($caseInsensitive === true) {
            $this->calls->addDefinition(Calls::CONST, mb_strtolower($fullnspath), $const);
        }
    }

    private function saveFiles(): void {
        $this->loader->saveFiles($this->config->tmp_dir, $this->atoms, $this->links);
        $this->reset();
    }

    private function startSequence(): void {
        $this->sequence              = $this->addAtom('Sequence');
        $this->sequence->code        = ';';
        $this->sequence->fullcode    = ' ' . self::FULLCODE_SEQUENCE . ' ';
        $this->sequence->token       = 'T_SEMICOLON';
        $this->sequence->bracket     = self::NOT_BRACKET;
        $this->sequence->ws->closing = '';

        $this->sequences->start($this->sequence);
    }

    private function addToSequence(AtomInterface $element): void {
        $this->addLink($this->sequence, $element, 'EXPRESSION');

        $this->sequences->add($element);
    }

    private function endSequence(): void {
        $elements = $this->sequences->getElements();
        $this->runPlugins($this->sequence, $elements);

        $this->sequence = $this->sequences->end();
    }

    // token may be string or int
    private function getToken(string|int $token): string {
        return $this->php->getTokenName($token);
    }

    private function getFullnspath(AtomInterface $name, string $type = 'class', AtomInterface $apply = null): void {
        assert($apply !== null, "\$apply can't be null in " . __METHOD__);

        // Handle static, self, parent and PHP natives function
        if (isset($name->absolute) && ($name->absolute === self::ABSOLUTE)) {
            if ($type === 'const') {
                if (($use = $this->uses->get('class', mb_strtolower($name->fullnspath))) instanceof AtomInterface) {
                    $apply->fullnspath = mb_strtolower($name->fullnspath);
                    return;
                }
                $fullnspath = preg_replace_callback('/^(.*)\\\\([^\\\\]+)$/', function (array $r): string {
                    return mb_strtolower($r[1]) . '\\' . $r[2];
                }, $name->fullcode);
                $apply->fullnspath = $fullnspath;
                return;
            }
            $apply->fullnspath = mb_strtolower($name->fullcode);

            return;
        }

        if (!$name->isA(array('Nsname', 'Identifier', 'Name', 'String', 'Null', 'Boolean', 'Static', 'Parent', 'Self', 'Newcall', 'Newcallname', 'This'))) {
            // No fullnamespace for non literal namespaces
            $apply->fullnspath = '';
            return;
        } elseif (in_array($name->token, array('T_ARRAY', 'T_EVAL', 'T_ISSET', 'T_EXIT', 'T_UNSET', 'T_ECHO', 'T_PRINT', 'T_LIST', 'T_EMPTY', ), STRICT_COMPARISON)) {
            // For language structures, it is always in global space, like eval or list
            $apply->fullnspath = '\\' . mb_strtolower($name->code);
            return;
        } elseif (mb_strtolower(substr($name->fullcode, 0, 10)) === 'namespace\\') {
            $details = explode('\\', $name->fullcode);
            if ($type === 'const') {
                array_shift($details); // namespace
                $const = array_pop($details);
                $fullnspath = mb_strtolower(implode('\\', $details)) . '\\' . $const;
            } else {
                array_shift($details); // namespace
                $fullnspath = '\\' . mb_strtolower(implode('\\', $details));
            }

            $apply->fullnspath = substr($this->namespace, 0, -1) . $fullnspath;
            return;
        } elseif ($name->isA(array('This'))) {
            if ($this->currentClassTrait->getCurrent() === ClassTraitContext::NO_CLASS_TRAIT_CONTEXT) {
                $apply->fullnspath = self::FULLNSPATH_UNDEFINED;
                return;
            } else {
                $apply->fullnspath = $this->currentClassTrait->getCurrent()->fullnspath;
                return;
            }
        } elseif ($name->isA(array('Static', 'Self'))) {
            if ($this->currentClassTrait->getCurrent() instanceof AtomInterface) {
                $apply->fullnspath = $this->currentClassTrait->getCurrent()->fullnspath;
                return;
            } else {
                $apply->fullnspath = self::FULLNSPATH_UNDEFINED;
                return;
            }
        } elseif ($name->atom === 'Newcall' && mb_strtolower($name->code) === 'static') {
            if ($this->currentClassTrait->getCurrent() === ClassTraitContext::NO_CLASS_TRAIT_CONTEXT) {
                $apply->fullnspath = self::FULLNSPATH_UNDEFINED;
                return;
            } else {
                $apply->fullnspath = $this->currentClassTrait->getCurrent()->fullnspath;
                return;
            }
        } elseif ($name->atom === 'Parent') {
            $apply->fullnspath = '\\parent';
            return;
        } elseif ($name->isA(array('Boolean', 'Null'))) {
            $apply->fullnspath = '\\' . mb_strtolower($name->fullcode);
            return;
        } elseif ($name->isA(array('Identifier', 'Name', 'Newcall', 'Newcallname'))) {
            if ($name->isA(array('Newcall', 'Name', 'Newcallname'))) {
                $fnp = mb_strtolower($name->code);
            } else {
                $fnp = $name->code;
            }

            if (($offset = strpos($fnp, '\\')) === false) {
                $prefix = $fnp;
            } else {
                $prefix = substr($fnp, 0, $offset);
            }

            // This is an identifier, self or parent
            if ($type === 'class' && ($use = $this->uses->get('class',mb_strtolower($fnp) )) instanceof AtomInterface) {
                $this->addLink($name, $use, 'USED');
                $apply->fullnspath = $use->fullnspath;
                return;
            } elseif ($type === 'class' && ($use = $this->uses->get('class', $prefix)) instanceof AtomInterface) {
                $this->addLink($name, $use, 'USED');
                $apply->fullnspath = $use->fullnspath . '\\' . preg_replace('/^' . $prefix . '\\\\/', '', $fnp);
                return;
            } elseif ($type === 'const' && ($use = $this->uses->get('const', $name->code)) instanceof AtomInterface) {
                $this->addLink($name, $use, 'USED');
                $apply->fullnspath = $use->fullnspath;
                return;
            } elseif ($type === 'const' && ($use = $this->uses->get('class', mb_strtolower($name->fullnspath))) instanceof AtomInterface) {
                $this->addLink($name, $use, 'USED');
                $apply->fullnspath = mb_strtolower($name->fullnspath);
                return;
            } elseif ($type === 'function' && ($use = $this->uses->get('function', $prefix)) instanceof AtomInterface) {
                $this->addLink($name, $use, 'USED');
                $apply->fullnspath = $use->fullnspath;
                return;
            } elseif ($type === 'const') {
                $apply->fullnspath = $this->namespace . $name->fullcode;
                return;
            } else {
                $apply->fullnspath = $this->namespace . mb_strtolower($name->fullcode);
                return;
            }
        } elseif ($name->atom === 'String' && isset($name->noDelimiter)) {
            if ($this->currentClassTrait->getCurrent() === ClassTraitContext::NO_CLASS_TRAIT_CONTEXT) {
                $apply->fullnspath = self::FULLNSPATH_UNDEFINED;
            } else {
                $apply->fullnspath = $this->currentClassTrait->getCurrent()->fullnspath;
                return;
            }

            $prefix =  str_replace('\\\\', '\\', mb_strtolower($name->noDelimiter));
            $prefix = "\\$prefix";

            // define doesn't care about use...
            $apply->fullnspath = $prefix;
            return;
        } else {
            // Finally, the case for a nsname
            $prefix = mb_strtolower( substr($name->code, 0, strpos($name->code . '\\', '\\')) );

            if (($use = $this->uses->get($type, $prefix)) instanceof AtomInterface) {
                $this->addLink( $name, $use, 'USED');
                $apply->fullnspath = $use->fullnspath . mb_strtolower( substr($name->fullcode, strlen($prefix)) );
                return;
            } elseif ($type === 'const') {
                $parts = explode('\\', $name->fullcode);
                $last = array_pop($parts);
                $fullnspath = $this->namespace . mb_strtolower(implode('\\', $parts)) . '\\' . $last;
                $apply->fullnspath = $fullnspath;
                return;
            } else {
                $apply->fullnspath = $this->namespace . mb_strtolower($name->fullcode);
                return;
            }
        }
    }

    private function setNamespace(string $namespace = self::NO_NAMESPACE): void {
        if ($namespace === self::NO_NAMESPACE) {
            $this->namespace = '\\';
            $this->uses = new Fullnspaths();
        } else {
            $this->namespace = mb_strtolower($namespace) . '\\';
            if ($this->namespace[0] !== '\\') {
                $this->namespace = '\\' . $this->namespace;
            }
        }
    }

    private function addNamespaceUse(AtomInterface $origin, AtomInterface $alias, string $useType, AtomInterface $use): string {
        if ($origin !== $alias) { // Case of A as B
            // Alias is the 'As' expression.
            $offset = strrpos($alias->fullcode, ' as ');
            if ($useType === 'const') {
                $return = substr($alias->fullcode, $offset + 4);
            } else {
                $return = mb_strtolower(substr($alias->fullcode, $offset + 4));
            }
        } elseif (($offset = strrpos($alias->code, '\\')) !== false) {
            // namespace with \
            $return = substr($alias->code, $offset + 1);
        } else {
            // namespace without \
            $return = $alias->code;
        }

        if ($useType !== 'const') {
            $return = mb_strtolower($return);
        }

        $this->uses->set($useType, $return, $use);

        return $return;
    }

    private function logTime(string $step): void {
        $this->log->log($step);
    }

    private function finishWithAlternative(bool $isColon): void {
        if ($isColon === self::ALTERNATIVE_SYNTAX) {
            $this->moveToNext(); // Skip endforeach
            if ($this->nextIs(array($this->phptokens::T_CLOSE_TAG), 0)) {
                --$this->id;
            }
            $this->processSemicolon();
            if ($this->nextIs(array($this->phptokens::T_SEMICOLON))) {
                $this->moveToNext();
            }
        } else {
            if ($this->nextIs(array($this->phptokens::T_CLOSE_TAG), 0)) {
                --$this->id;
            }

            $atom = $this->popExpression();
            $this->sequence->ws->separators[] = '';
            $this->addToSequence($atom);
        }
    }

    private function checkExpression(): void {
        $this->checkPhpdoc();

        if ( !$this->contexts->isContext(Context::CONTEXT_NOSEQUENCE) && $this->nextIs(array($this->phptokens::T_CLOSE_TAG))) {
            $this->processSemicolon();
        }
    }

    private function whichSyntax(int $current, int $colon): bool {
        return in_array($this->tokens[$current][0], array($this->phptokens::T_FOR,
                                                          $this->phptokens::T_FOREACH,
                                                          $this->phptokens::T_WHILE,
                                                          $this->phptokens::T_DO,
                                                          $this->phptokens::T_DECLARE,
                                                          $this->phptokens::T_SWITCH,
                                                          $this->phptokens::T_IF,
                                                          $this->phptokens::T_ELSEIF,
                                                         ), STRICT_COMPARISON) &&
               ($this->tokens[$colon][0] === $this->phptokens::T_COLON) ?
                self::ALTERNATIVE_SYNTAX :
                self::NORMAL_SYNTAX;
    }

    private function nextIs(array $tokens, int $offset = 1): bool {
        if (!isset($this->tokens[$this->id + $offset])) {
            debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            die();
        }
        return in_array($this->tokens[$this->id + $offset][0], $tokens, STRICT_COMPARISON);
    }

    private function moveToNext(int $token = 0): int {
        ++$this->id; // Skip default
        if (!empty($token)) {
            assert($this->tokens[$this->id][0] === $token, 'Not the expected token when moving : found ' . $this->tokens[$this->id][1]);
        }

        return $this->id;
    }

    private function printToken(int $token = 0): void {
        print_r($this->tokens[$this->id + $token]);
        die();
    }
}

?>