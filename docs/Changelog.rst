.. Changelog:

Release Note
===============


Here is the release note of exakat. 

**Version 2.3.4 (Yuchi Gong, 2022-01-19)**


+ Cobbler
    + 

+ Report
    + 

+ Analysis
    + New analysis : Report unchecked divisions
    + New analysis : report possible abstract constants in classes (which should be defined in a parent)
    + New analysis : report recycled variables
    + Refactored analysis : Upgraded 'Object references' with union and intersectional types
    + Refactored analysis : Removed edges cases in 'Don't collect void'
    + Refactored analysis : Extension detection now takes into account enums 
    + Refactored analysis : Upgraded AlwaysFalse with better typehinting inference
    + Refactored analysis : indentation levels missed several results while reporting
    + Refactored analysis : interfaces, traits and constants were missing for use expression resolution
    + Refactored analysis : Undefined Interfaces now exclude better PHP or ext's interfaces
    + Refactored analysis : Never Used Parameter confused Void and first argument
    + Refactored analysis : Self were reported as outside a class when in foreach()
    + Refactored analysis : Clone with non-arrays now checks PHP native functions too
    + Refactored analysis : Excluded powers from calculations in IsZero
    + Refactored analysis : Fixed discrepancy between ' and " handling of \

+ Tokenizer
    + Fixed a bug where static keyword is processed as a simple nsname
    + Fixed a bug where typehints were not marked as isPhp, isExt or isStub
    + Fixed an edge case with array functions inside match() syntax
    + Fixed an edge case with Closures and reference-use variable
    + Fixed an edge case with static inside ternary
    + Fixed yield expression scope
    + Added Table for PHP 8.2 compilations checks
    + Removed extra void with use expression for traits

**Version 2.3.3 (Xu Maogong, 2022-01-05)**


+ Cobbler
    + New Cobbler : removes attributes

+ Report
    + 

+ Analysis
    + New analysis : suggest using ?-> when Null is a possiblity
    + New analysis : Report backward incompatibility with overloaded interface constants
    + New analysis : Mark variables as local constants when only assigned once
    + New analysis : suggest using iterable, based on array|traversable usage
    + New analysis : Report usage of PHP 8.1 intersection typehints
    + Refactored analysis : Hidden Nullable rule now handles intersection types
    + Refactored analysis : 'Use Nullable' covers properties too
    + Refactored analysis : 'Could Be stringable' is extended to trait usage
    + Refactored analysis : skip static and globals when counting variable usage in methods
    + Refactored analysis : PHP 8.0 Union type detection includes properties
    + Added tests to Complete/Overloaded* (CPM)

+ Tokenizer
    + Fixed a bug with Ternary and constants

**Version 2.3.2 (Wei Zheng, 2021-12-16)**


+ Cobbler
    + New cobbler : removes a method

+ Report
    + 

+ Analysis
    + New analysis : suggest ::class instead of get_class()
    + New analysis : report when a class extends stdclass (for dynamic properties review)
    + New analysis : Reports when checks are made on the existence of properties
    + Upgraded analysis : Useless Typechecks is upgraded with union and intersectional type checks
    + Upgraded analysis : Reporting invalid access to protected CPM
    + Upgraded analysis : Removed Used Properties with classes with dynamic properties
    + Fixed bug in PropagateConstants 

+ Tokenizer
    + Added detection of typehints for variables

**Version 2.3.1 (Li Shimin, 2021-12-01)**


+ Cobbler
    + Fixed bug with Settypehint when multiple types are available

+ Report
    + New Pdff report : PHP Document File Format

+ Analysis
    + New analysis : report promoted properties
    + New analysis : report deprecated PHP 8.2 callable
    + New analysis : report new in initializers
    + New analysis : report nested attributes
    + New analysis : report direct calls to Trait methods and properties
    + New analysis : report auto vivification of false (PHP 8.1)
    + New analysis : report implicit float to integer conversion for arrays
    + Updated analysis : Declare Static and Global early.
    + Updated analysis : No Null For Native now uses typehints
    + Updated analysis : refined No Static variable in method

+ Tokenizer
    + Fixed bug with __METHOD__ when it is called outside a method

**Version 2.3.0 (Wei, 2021-11-18)**


+ Architecture
    + Catchup tokens from PHP 5.6 till 7.2
    + Report unknown Rulesets during reports command
    + Extended 'catalog' command to list rules too
    + Extended 'catalog' command to return YAML format

+ Report
    + Added several new analysis to the Rector report
    + Added mixed and never to Appinfo report
    + Ugraded Sarif report with bartlett/sarif-php-sdk

+ Analysis
    + New analysis : report the missing mixed returntype for jsonserialize
    + New analysis : report final with constants
    + New analysis : report never usage (typehint)
    + New analysis : report PHP 8.1 typehint incompatibilities
    + New analysis : report PHP 8.0 typehint incompatibilities
    + New analysis : report PHP 8.0 named parameters
    + New analysis : report First Class Callable Syntax
    + New analysis : New Functions in PHP 8.1
    + New analysis : Removed functions in PHP 8.1
    + New analysis : Prepare 'never' for PHP 8.1
    + New analysis : Prepare 'mixed' for PHP 8.0
    + New analysis : detect mixed and never usage as typehints
    + Upgraded analysis : Wrong Number of arguments also works with new first class callable syntax
    + Upgraded analysis : Typehint stats now includes union and intersection types
    + Upgraded analysis : Removed functions in PHP 8.0

**Version 2.2.5 (Wood star, 2021-11-03)**


+ Analysis
    + New analysis : Calling Trait Static Method directly is deprecated in PHP 8.1
    + New analysis : No reference for returned void
    + New analysis : No Null for PHP native methods
    + Updated analysis : Wrong type for argument now covers classes, union type and intersection types. 
    + Updated analysis : Wrong type for argument now covers classes, union type and intersection types. 
    + Updated analysis : Unused Private Methods are also detected with array($this, 'xx') syntax
    + Checked unit tests : 3821 / 3805 test pass (99% pass)

+ Cobblers
    + New cobbler : remove typehints from arguments, returns and properties

**Version 2.2.4 (Gold star, 2021-10-21)**


+ Dataset
    + Updated PHP native dataset with missing classes and typehint.

+ Analysis
    + New analysis : Report incompatible typehint with native PHP methods in PHP 8.1
    + New analysis : Report Missing Attribute Attribute
    + New analysis : Report full_path index in $_FILES usage
    + Updated analysis : Type detection also include return type from methods

+ Cobblers
    + Updated cobbler : Set typehint handles typehint from arguments

+ Tokenizer
    + Added more cases for Constant types

**Version 2.2.3 (Wu, 2021-10-06)**


+ Architecture
    + Updated INI files for PHP 8.1

+ Data
    + Extended PHP directives lists

+ Report
    + New report Migration 8.1

+ Analysis
    + New analysis : PHP 8.1 removed directives
    + New analysis : PHP 8.1 removed constants
    + New analysis : Wrong named parameter for PHP native function
    + New analysis : Report duplicate named arguments
    + New analysis : htmlentities (and co) default 2nd argument
    + Updated analysis : Scalars are not arrays. Extemded with type support.

+ Tokenizer
    + Support for callable strlen(...)
    + Test for new syntax for octal 0o123

**Version 2.2.2 (Si, 2021-09-22)**


+ Architecture
    + Refactored documentation 

+ Report
    + Added support for PHP 8.1 compatiblity

+ Analysis
    + New analysis : Restrict $GLOBALS usage
    + New analysis : No object as array's index
    + New analysis : Overreaching classes (PHP feature)
    + New analysis : Report Enum usage
    + Updated analysis : Typehints/* got new Unit Tests
    + Updated analysis : Explode optimisation 

+ Tokenizer
    + Reduced the number of DEFAULT creation for properties
    + Added support for new PHP 8.1 syntax (Enum )

**Version 2.2.1 (Chen, 2020-11-20)**


+ Architecture
    + Export : WIP of exporting PHP code from graph
    + New directives : rules_version_max, rules_version_min, ignore_rules and ignore_namespace

+ Report
    + Sarif : Fixed line number that may be null or less 
    + Ambassador : Fixed visibility report

+ Analysis
    + New analysis : check for match as a keyword
    + New analysis : replace static variable by static properties
    + New analysis : warn about usage of get_object_vars()
    + New analysis : report global and static variables that are declared multiple times
    + Updated analysis : extended Used Classes to abstract classes
    + Updated analysis : wrong number of argument now supports $this()
    + Updated analysis : parse_str last argument doesn't apply anymore in PHP 8
    + Updated analysis : useless argument now omits parameter with default value
    + Checked unit tests : 3797 / 3800 test pass (99% pass)

+ Tokenizer
    + Fixed race condition with phpdocs 
    + Refactored static and global variables definitions (avoid double definitions)
    + Fixed detection of [] inside a list()
    + Fixed detection of alternative syntax for switch
    + Added use property to usenamespace too (for grouping)

**Version 2.2.0 (Mao, 2020-10-15)**


+ Architecture
    + Extended Export command to produce PHP scripts from the graph database
    + Added more typehints
    + Added new command 'onefile'
    + Sped up database restart with id reset
    + Updated list of functions for several extensions. Started adding methods, class constants..

+ Report
    + Ambassador : updated popularities
    + Ambassador : added missing PHP 8.0 ruleset

+ Analysis
    + New analysis : report arguments and properties whose name clashes with the typehint
    + New analysis : report long preparation before throw command
    + New analysis : missing __isset() method
    + New analysis : suggest array_keys() for array_search in loops
    + New analysis : array_map() complains with values by reference
    + New analysis : report final private properties
    + New analysis : report misnamed constant/variable
    + New analysis : check for attribute configuration (PHP 8.0)
    + New analysis : suggest dropping variable in catch clause
    + New analysis : report resources that should not be tested with is_resource (PHP 8.0)
    + New analysis : check for named arguments and variadic
    + Updated analysis : wrong number of argument now supports $this()
    + Updated analysis : redefined private property uses OVERWRITE
    + Updated analysis : refactored UndefinedFunctions for speed
    + Updated analysis : array_map() complains with values by reference
    + Updated analysis : removed false positives on properties in strings
    + Updated analysis : unsupported types with operators skips cast values
    + Updated analysis : cancelled parameters are also for array_map/array_walk
    + Updated analysis : variable variable skips variables inside strings
    + Updated analysis : removed functions are not reported when in if/then with function_exists()
    + Updated analysis : wrong optional parameter fixed false positive with ...
    + Updated analysis : extended list of removed directives, functions and constants
    + Removed analysis : RealVariables
    + Checked unit tests : 3761 / 3772 test pass (99% pass)

+ Tokenizer
    + Added Void to empty default/case
    + Bitoperation added to isRead
    + Fixed list[] in a Foreach
    + Fixed token T_OPEN_DOLLAR_CURLY_BRACKET

**Version 2.1.9 (Yin, 2020-10-01)**


+ Architecture
    + Removed old and unused commands
    + Modernized usage of docker as phpexec
    + New directive php_extensions to managed list of ext

+ Report
    + Ambassador : removed 3 gremlins from typehint stats, added scalar types
    + New Migration80 report, dedicated to PHP 8.0 migrations
    + New Stubs.ini report, dedicated to exakat extensions production

+ Analysis
    + New analysis : report arguments which are not nullable because of constants.
    + New analysis : could use stringable interface
    + New analysis : suggest explode()'s third argument when applicable
    + New analysis : suggest PHP 8.0 promoted properties
    + New analysis : report arrays with negative index, and auto-indexing 
    + New analysis : report unsupported types with operators
    + New analysis : report usage of track_errors directive (PHP 8.0)
    + New analysis : report useless types on __get/__set
    + New analysis : count the number of use expressions in a file
    + New analysis : Avoid modifying typed arguments
    + New analysis : Report Assumptions in the code
    + New analysis : array_fill() usage with objects
    + New analysis : mismatch between parameter name and type
    + Updated analysis : magic methods definitions also find usage for __invoke()
    + Updated analysis : noscream operator usage may have exceptions
    + Updated analysis : identical methods and identical closures
    + Updated data : list of exceptions and their emitters

+ Tokenizer
    + Upgraded detection of extensions' structures, beyond functions

**Version 2.1.8 (Chou, 2020-09-18)**


+ Architecture
    + added '--' options, and kept the '-' options, for migration purposes. (--format and -format are both available)
    + Added support for PHP 8 attributes in dump.sqlite
    + Added 'precision' to rule docs. 
    + Moved all but one data collection from Dump -collect to Dump/ analysis. 

+ Report
    + New report : SARIF
    + Typehint suggestion report : Tick classes when they are fully covered
    + Weekly report : fix donuts display.
    + Stubsjson : Added support for PHP attributes
    + Stubs : Added support for PHP attributes

+ Analysis
    + New ruleset : CI-Checks
    + New analysis : 'Multiple declare(strict_types = 1)'
    + New analysis : 'No more (unset) in PHP 8'
    + New analysis : Cancel methods in parent : when methods should not have been abstracted in parent class.
    + New analysis : '$php_errormsg is removed in PHP 8'
    + New analysis : 'Mismatch Parameter Name' checks parameter names between inherited methods for consistency
    + Upgraded analysis : 'Useless Arguments' is accelerated
    + Upgraded analysis : 'Don't use Void' weeded out false positives
    + Upgraded analysis : 'Wrong type for native calls' weeded out false positives
    + Upgraded analysis : 'Non static methods called statically' was refactored for PHP 8.0 support
    + Upgraded analysis : 'PHP Keywords' includes 'match'
    + Upgraded analysis : 'Useless instruction' reports '$a ?? null' as useless.
    + Upgraded analysis : 'Uncaught exceptions' is extended to local variables
    + Upgraded analysis : 'Foreach favorites' also covers the keys
    + Upgraded analysis : 'Should Preprocess' skips expressions with constants
    + Upgraded analysis : 'Compare Hashes' has more functions covered
    + Removed analysis : 'Normal Properties' : no need anymore.

+ Tokenizer
    + Moved isPhp attribute to Task/Load plugin
    + Created isExt attribute to Task/Load plugin

**Version 2.1.7 (zi, 2020-09-07)**


+ Architecture
    + Refactored loading class, to keep query load at optimal size for Gremlin
    + GC during load to free memory
    + More typehints
    + Move several collections to Dump/ ruleset

+ Report
    + Upgraded Typesuggestion report with report on closures and arrow functions
    + Added Arrowfunctions in inventories
    + Added collection of arguments and details for closures and arrowfunctions

+ Analysis
    + New analysis : Could Be In Parent : suggest methods that should be defined in a parent
    + New analysis : Don't pollute namespace
    + New analysis : report insufficient return typehints
    + Upgraded analysis : 'Method signature must be compatible' now PHP 8.0 compatible
    + Upgraded analysis : 'Wrong type with native function' fixes false positives
    + Upgraded analysis : 'Same condition' added coverage for || conditions
    + Upgraded analysis : 'Missing returntype' extended to class typehints
    + Upgraded analysis : 'Should Use This' also covers special functions like get_class_called()
    + Upgraded analysis : 'No concat in loop' skips nested loops
    + Upgraded analysis : 'Always false' covers typehint usage 
    + Upgraded analysis : 'NoChoice' doesn't report large expressions
    + Upgraded analysis : 'Dont mix PlusPlus' skip () and =
    + Upgraded analysis : 'Fallthrough' don't report final cases without break
    + Checked unit tests : 3663 / 3630 test pass (99% pass)

+ Tokenizer
    + Removed 'root' property
    + Upgraded to new Attributes #[] in detection and normalisation
    + Fixed constant detection within instanceof
    + Created RETURN and RETURNED for Arrowfunctions (there is no return otherwise)
    + Parent method also calls children methods when those are not defined there
    + Support for multiple attributes in one syntax

**Version 2.1.6 (Night Patrol Deity, 2020-08-28)**


+ Architecture
    + More typehints coverage
    + Various speed-up
    + Lighter logging with gremlin
    + Fixed installation path

+ Report
    + Upgraded Typesuggestion report
    + Upgraded Stubs and Stubsjson

+ Analysis
    + New analysis : report PHP 8.0 unknown parameters
    + New analysis : overwritten methods with different argument counts
    + New analysis : Warn of iconv and TRANSLIT for portability
    + New analysis : Warn of glob and  {} for portability
    + Upgraded analysis : 'Useless check' covers new situations.
    + Upgraded analysis : 'Abstract away' now covers new calls.
    + Upgraded analysis : 'Must return Typehint' skips Void.
    + Upgraded analysis : 'Missing new' with less false positives
    + Checked unit tests : 3559 / 3630 test pass (98% pass)

+ Tokenizer
    + Support for Virtualmethod and imports from traits
    + Refactored Usenamespace atom
    + Fixed calculations of fullnspath for static::class
    + Fixed detection of null/true/false in new()
    + Added support for T_BAD_CHARACTER

**Version 2.1.5 (Day Patrol Deity, 2020-08-04)**


+ Architecture
    + Fixed comment size estimation by 1 for T_COMMENT
    + Added more typehints to code

+ Report
    + Typehint suggestions : added ticks to fully typed methods
    + Emissary : Extract more information from dump.sqlite, instead of datastore.sqlite
    + Ambassador : Added a list of parameters, defined in the application
    + Ambassador : Added a list of fossilised methods
    + Stubs : Added check around PHP native functions and CIT
    + StubsJson : Added property for PHP native structures

+ Analysis
    + New analysis : Report insufficient initialisation for array_merge() collector variable
    + New analysis : Report useless triple equals 
    + New analysis : Don't compare typed boolean return values
    + New analysis : Report wrong type used with PHP functions
    + New analysis : Suggest abstracting away some PHP native functions
    + New analysis : Report try block that are too large
    + New analysis : Report variables potentially undefined in catch clause
    + New analysis : Report swapped arguments in methods overwriting
    + Upgraded analysis : InvalidPackFormat speed up
    + Upgraded analysis : Added parameter to Security/ShouldUsePreparedStatement to choose the preparing method
    + Upgraded analysis : Added parameter to Security/HardcodedPasswords to choose the name of properties/index
    + Upgraded analysis : PHP 8.0 new scalar typehint, stringable interface

+ Tokenizer
    + Added support for named parameters (PHP 8.0)
    + Trimmed some properties from atoms
    + Removed non-existent atom mentions
    + Added support for Attributes (WIP)
    + Added support for ?-> 
    + Added support for new T_*_NAME tokens

**Version 2.1.4 (Marshal of Heavenly Blessing, 2020-07-23)**


+ Architecture
    + Added time of last commit in audit results
    + Added more typehints
    + Upgraded PHP native method description with typehints (WIP)

+ Report
    + Typehint suggestion report
    + New toplogies : call order, 
    + Ambassador : new statistics for typehint usage

+ Analysis
    + New analysis : Report double assignation of objects
    + New analysis : Typehints/CouldBe*, which makes suggestions for typehints
    + New analysis : Checks for argument type when typehint is present in custom methods
    + Upgraded analysis : Too Many Finds may be configured for threshold and prefix/suffix
    + Upgraded analysis : Typehints stats were extended to properties and multiple typehints
    + Upgraded analysis : Global outside Loop is extended to static variable too
    + Upgraded analysis : ErrorMessages also detect local variable contents
    + Upgraded analysis : Speed up for NullBoolean, Interfaces IsNotImplemented, InvalidPackFormat, arrayIndex, noWeakCrypto
    + Checked unit tests : 3532 / 3496 test pass (99% pass)

+ Tokenizer
    + Removed 'aliased' property in atoms
    + Fixed spotting of PHP native constants, when in Define() structure
    + Fixed loading of false values
    + Added support for the trailing comma in closure's use expression
    + more handling of phpdocs
    + Null is now reused when it is a default value, as a typehint. 
    + Logical was split in two : Logical and Bitoperation
    + Added support for match() {} expression
    + Fixed boolean calculations during Load
    + Removed auto-referencing in DEFAULT calculations

**Version 2.1.3 (Marshal of the Heavenly Canopy, 2020-07-02)**


+ Architecture
    + Removed all usage of datastore in Reports, and only rely on dump.
    + ignore_rules is now case insensitive
    + Moved some of the loading to a separate gremlin call to reduce the size of node load.
    + Fixed the branch option with Git calls.
    + Storing trait's use expresion's options.

+ Report
    + Ambassador ; New inventory : PHP protocol used (php, phar, glob://...)
    + Stubs and StubsJson, have been tested extensively

+ Analysis
    + New analysis : report double assignations of the same object ($a = $b = new C)
    + New analysis : report cyclic references
    + Upgraded analysis : Used Constants edge situations
    + Upgraded analysis : No real comparison : extended analysis to constants
    + Upgraded analysis : extended detection of dynamic method calls to call_user_func*
    + Upgraded analysis : paths are detected with new functions
    + Checked unit tests : 3490 / 3520 test pass (99% pass)

+ Tokenizer
    + More phpdoc support (from code to report)
    + Added isPHP to absolute FQN notations

**Version 2.1.2 (Mountain Deity, 2020-06-25)**


+ Architecture
    + Removed files task from initproject.
    + Added ignore_rule directive, to ignore specific rules while running a specific report
    + More documentation (in particular, modifications section)
    + Exakat avoids to return twice the same results (file and line)
    + Sped up some analysis, and added a time limit per analysis
    + Removed double linking for static variables

+ Report
    + New reports ; Stubs and StubsJson, which produce the stubs of the audited code (PHP and JSON format) (WIP)
    + New report ; Typehint suggestion (WIP)
    + Ambassador ; offers the configuration for all the rules that spotted issues in the current audit, for reuse in other codes
    + Collect the number of property per class

+ Analysis
    + New analysis : Report methods that are too much indented on average
    + New analysis : Report possible confusion between a class and an alias
    + New analysis : Report variables that are static and global at the same time
    + New analysis : Report statement with long blocks
    + New analysis : Report phpdoc's deprecated methods and function calls
    + Upgraded analysis : Dereferencing levels now include () and = 
    + Upgraded analysis : Unused Methods now skips classes that calls themselves dynamically 
    + Upgraded analysis : No Need Get_class() was refactored
    + Upgraded analysis : Avoid Optional Properties was refactored
    + Upgraded analysis : Variable inconsistent Usage was extended with more reach
    + Upgraded analysis : Indirect Injections was upgraded with better reach with variables
    + Upgraded analysis : Direct Injections was upgraded with include
    + Upgraded analysis : PHP 8.0 new scalar typehint, stringable interface
    + Upgraded analysis : Mismatch Type and default now avoids undefined constants
    + Upgraded analysis : Wrong Optional Parameter is upgraded for PHP 8.0
    + Upgraded analysis : Indentation level was refactored
    + Checked unit tests : 3480 / 3510 test pass (99% pass)

+ Tokenizer
    + Upgraded detection of PHP native constants, when they are in absolute notation
    + Dump task stores use expressions' options, plus minor fixes
    + Added support for Attributes (PHP 8.0)
    + Added support for Union types (PHP 8.0)
    + AtomIs step (WITH_VARIABLE) was extended with local variables
    + DEFAULT doesn't point anymore on auto-updated values
    + Extended support for phpdoc in the code
    + Added support for promoted properties (PHP 8.0)

**Version 2.1.1 (Earth Deity, 2020-06-01)**


+ Architecture
    + Using timeLimit() to prevent Gremlin from running too deep in the rabbit hole
    + Added Neo4j Graphson V3 Graph driver
    + Moved 'Dump' rules to a specific Ruleset for easier administration
    + Propagated the upgrade to PHP 8.0 union types to three more rules
    + Fixed access to the list of ignored files
    + Added support for explicit stub files
    + Fixed multiple calls to Dump (better reentrant)

+ Report
    + New report : Meters, which holds measures for the audited code.
    + Ambassador : inventory of OpenSSL ciphers

+ Analysis
    + New analysis : Report unused traits
    + New analysis : Report chmod 777 system calls
    + New analysis : Check for keylength when generated by PHP
    + New analysis : Report methods with prefix/suffix and expected typehint
    + New analysis : Mark classes when they call dynamically their own methods
    + New analysis : Check for constants hidden in variable names ${X} != $X;
    + New analysis : Throw will be an expression in PHP 8.0
    + Upgraded analysis : Dangling operator now checks for loops too
    + Upgraded analysis : 'Variables used once' now skips variable definitions
    + Upgraded analysis : 'Access Private' takes into account dynamic classes
    + Upgraded analysis : 'Could Centralize' now uses a custom threshold. Default is 8 usage of an expression to centralize.
    + Upgraded analysis : 'Return true/false' checks that they are alone in the blocks
    + Upgraded analysis : 'Unreachable code' checks on constants values before reporting the next expression
    + Upgraded analysis : 'Magic methods' are case insensitive
    + Upgraded analysis : 'No Hardcoded passwords' has new functions that require a password
    + Upgraded analysis : 'Unused methods' are omitted for dynamically called methods and overwritten methods
    + Upgraded analysis : Insufficient Property Typehint also works for untyped properties
    + Upgraded analysis : PHP 8.0 new scalar typehint, stringable interface
    + Checked unit tests : 3383 / 3444 test pass (98% pass)

+ Tokenizer
    + Arguments with null as default values, automatically are nullable
    + Intval is also an integer for logical operations
    + Default Values now omits recursives assignations
    + Fixed fullnspath for PHP short tags
    + Added link between new command and constructor of anonymous classes.

**Version 2.1.0 (City God, 2020-05-13)**


+ Architecture
    + results stored in HashResults are now testable
    + Moved all query methods to Query/DSL namespace, from Analyzer class

+ Report
    + New report : ClassReview, with focus on classes structures
    + New report : Typechecks, with focus on type hint usage
    + Ambassador : Added typehint stats section
    + Ambassador : fixed display of classes name in classes tree
    + Ambassador : some missing sections have been rehabilitated

+ Analysis
    + New analysis : Trailing comma in signature (PHP 8.0)
    + New analysis : Hidden nullable types
    + New analysis : Not implemented abstract methods
    + New analysis : Report confusion between variables and arguments with arrow functions
    + Upgraded analysis : No literal for reference was extended 
    + Upgraded analysis : Add zero is extended to constants
    + Upgraded analysis : This is for classes is now valid with arrow functions
    + Upgraded analysis : Useless arguments takes also into account constants
    + Upgraded analysis : Wrong Type With Call supports variadic arguments
    + Upgraded analysis : Extension constants now support fully qualified names
    + Upgraded analysis : Bad Typehint relay is compatible with union types
    + Upgraded analysis : Multiple Identical Cases now handles constants too
    + Checked unit tests : 3437 / 3477 test pass (99% pass)

+ Tokenizer
    + Restored 'List' atom
    + Interface methods are now 'abstract' by default
    + Added 'array' typehint for variadic arguments
    + Distinguish between argument and local variable in fn functions
    + Removed nullable property
    + propagate calls now propagates closures and arrow functions
    + Added support for union types (PHP 8.0)
    + Check all error messages from php, not just the first ones

**Version 2.0.9 (Jialan, 2020-04-30)**


+ Architecture
    + Added option in TU for analysis that won't fill the result table.
    + Reduced the number of duplicate links in the graph
    + Upgraded tokens for PHP 8.0. 

+ Analysis
    + New analysis : Don't collect void
    + New analysis : Wrongly inited properties
    + New analysis : Not inited properties
    + Upgraded analysis : PHP 8.0 removed functions
    + Upgraded analysis : Useless instructions also include global/static variables
    + Upgraded analysis : Bad Relay Function now works with return types and property types
    + Upgraded analysis : 'Scalar or object properties' are upgraded with static calls
    + Removed analysis : Classes and Arrays IsRead and IsModified. Use properties now.
    + Checked unit tests : 3347 / 3420 test pass (97% pass)

+ Tokenizer
    + Fixed edge case for xor, with intval
    + Refactored multiple calculation for cast values
    + Added support for links between constants and use expressions
    + Linked classes with calls, when using use expression

**Version 2.0.8 (Ao Run, 2020-04-20)**


+ Architecture
    + Added new information in dump.sqlite, to make report autonomous

+ Analysis
    + Upgraded analysis : Paths are also recognized with constants, and more functions
    + Upgraded analysis : Should Use single Quotes
    + Checked unit tests : 3328 / 3398 test pass (97% pass)

+ Tokenizer
    + Fixed detection of PHP constants

**Version 2.0.7 (Ao Shun, 2020-04-14)**


+ Architecture
    + Adopted strict_types
    + Removed ctype1 attribute
    + Moved linting into separate processes
    + Refactored analysis to export to dump via SQL
    + Added 'None' ruleset to Dump task

+ Report
    + Ambassador : Added Constant's order report
    + None : Added support for No report

+ Analysis
    + Upgraded analysis : Undefined class constants
    + Upgraded analysis : Undefined global constants
    + Upgraded analysis : Undefined property
    + Checked unit tests : 3347 / 3420 test pass (97% pass)

+ Tokenizer
    + Support PHP 8.0's tokens
    + Added support for multiple typehint in the engine
    + Fixed edge case for boolean type casting

**Version 2.0.6 (Ao Qin, 2020-03-04)**


+ Architecture
    + Refactored analysis types for first UT
    + Moving to PHP 7.4 by default

+ Report
    + Rector : added more coverage
    + All : better display of typed properties

+ Analysis
    + New analysis : Semantic names of arguments
    + New analysis : !$a == $b
    + New prototype : possibles interfaces
    + Upgraded analysis : Overwritten literals now skips .=
    + Upgraded analysis : Scalar or object handles return type
    + Checked unit tests : 3322 / 3420 test pass (97% pass)

**Version 2.0.5 (Ao Guang, 2019-11-25)**


+ Architecture
    + Fixed access to severity and timetofix from compiled extension

+ Report
    + Ambassador : Fixed links to documentation

+ Analysis
    + Upgraded analysis : Mismatched Type and Default now omit undefined constants
    + Checked unit tests : 3366 / 3402 test pass (99% pass)

**Version 2.0.4 (Army Defeating Star of Heaven's Gate, 2019-11-18)**


+ Architecture
    + Reducing Analyzer's class method count
    + Moving more collections to Dump/ and Complete/

+ Report
    + Rector : added more coverage
    + Ambassador : Skiped analysis are now reported, not with -1
    + Ambassador : Foreach favorites's graph is displayed
    + Ambassador : Visibility suggestion has full method names

+ Analysis
    + Upgraded analysis : Don't Mix ++ now skips $a[$b++]
    + Upgraded analysis : Type hint stats skips some return values
    + Checked unit tests : 3365 / 3401 test pass (99% pass)

**Version 2.0.3 (Military Star of the North Pole, 2019-11-11)**


+ Architecture
    + Added check on xdebug presence (nesting limit)
    + Moving more collections to Dump/

+ Analysis
    + New analysis : Nullable typehint requires a test on NULL
    + New analysis : Typehint that requires too much
    + Upgraded analysis : Printf check on arguments works with '.'
    + Upgraded analysis : No magic for arrays skips __get()
    + Upgraded analysis : Const recommended, but not when methods are used
    + Upgraded analysis : Written only variables handles compact()
    + Upgraded analysis : Callbacks need returns, but not for spl_autoload_register()
    + Upgraded analysis : Extended analysis to Concatenation an Heredoc for Email
    + Upgraded analysis : Disconnected classes handles case sensitivity
    + Checked unit tests : 3371 / 3397 test pass (99% pass)

**Version 2.0.2 (Danyuan Star of Honesty and Chasity, 2019-11-04)**


+ Architecture
    + Adding more typehint
    + Created new class to build Dot files
    + Cleaned double examples
    + Dump handles multiple definitions for constants, class, trait, functions.

+ Report
    + Added new Topology report
    + Added new Type hint topology sort
    + Stubs : added class constant visibility

+ Analysis
    + New analysis : Report argument whose name clashes with typehint
    + New analysis : Report properties that are insufficiently typed
    + Moved 'Inclusions' to Dump/
    + Added steps to find original and relayed arguments

+ Tokenizer
    + Fixed paralellisation bug in Load

**Version 2.0.1 (Military Star of the North Pole, 2019-10-28)**


+ Architecture
    + Added more return type
    + Centralized reading for ini or json

+ Report
    + Ambassador: fixed Foreach favorites
    + Ambassador: added sort to number of parameter list
    + Checked unit tests : 3345 / 3377 test pass (99% pass)

+ Analysis
    + Upgraded xmlwriter to json

**Version 2.0.0 (Civil Star of Mystery and Darkness, 2019-10-21)**


+ Architecture
    + Manual file/line fixes
    + More simplifcations in load step

+ Report
    + Ambassador : fixed performance display
    + Ambassador : report list of shell commands
    + Typehint4all : first report
    + Perfile : fixed sorting

+ Analysis
    + New analysis : Report possible typehint for bool, int, string, array. WIP
    + Upgraded analysis : common alternatives are extended to switch and elsif
    + Upgraded analysis : xmlreader description includes class constants, properties and methods.
    + Upgraded analysis : callback needs return, is extended to php native functions
    + Checked unit tests : 3345 / 3377 test pass (99% pass)

**Version 1.9.9 (Lasting Prosperity Star of True Man, 2019-10-14)**


+ Architecture
    + Documentation review

+ Report
    + New reports : Stubs, Rector
    + Typehint stats
    + Stubs takes into account use expression
    + Added Concrete5 and Typo3 as vendors

+ Analysis
    + New analysis : checks on is_a third argument
    + New analysis : Invalid mbstring encodings
    + New analysis : Weird Index in arrays
    + New analysis : Avoid FILTER_SANITIZE_MAGIC_QUOTES
    + New analysis : Don't forget third argument
    + New analysis : Hard to update methods
    + New analysis : Merge two ifthen into one
    + New analysis : Report wrong type with calls
    + New analysis : Check case for namespaces
    + Updated analysis : Undefined interfaces now includes interfaces extensions
    + Updated analysis : Report more wrong types with return type 
    + Updated analysis : Register globals also applied to class
    + Updated analysis : Could Use Try covers more new, functions and static calls
    + Updated analysis : Useless Cast also reports (string) array (always Array)
    + Checked unit tests : 3343 / 3366 test pass (99% pass)

+ Tokenizer
    + Create default values for foreach
    + Load captures empty files, and omit them
    + Create default values also handles ??=

**Version 1.9.8 (Giant Gate Star of Dark Essence, 2019-10-07)**


+ Architecture
    + Upgraded dump command to handle multiple -P
    + .yaml configuration handles multiple reports
    + Started journey to strict_types
    + Code cleaning

+ Report
    + Ambassador : Fixed report of Flexible Docs
    + Ambassador : trimmed delimiters in inventories
    + Inventory : Foreach, with key values

+ Analysis
    + New analysis : Wrong case for functions
    + New analysis : Parameter Hiding
    + New analysis : Report usage of Traversable
    + Updated analysis : Undeclared properties skips undefined properties
    + Updated analysis : Useless Interface, modernized query
    + Updated analysis : String Holding Variables now skips default, const, sprintf
    + Updated analysis : Binaries are not confused with hex
    + Updated analysis : Extended 'Insufficient typehint' to abstract classes
    + Checked unit tests : 3324 / 3343 test pass (99% pass)

+ Tokenizer
    + Fixed handling of large powers
    + Added more escaping when storing to SQLITE

**Version 1.9.7 (Greedy Wolf Star of Sunlight, 2019-09-30)**


+ Architecture
    + Added support for analysis reporting missing values in a reference list
    + Fixe batch dumping of results

+ Report
    + Ambassador : new inventory : dereferencing levels

+ Analysis
    + New analysis : Use PHP Native URL parsing functions
    + New analysis : Maximum dereferencing level
    + New analysis : Use case value in a switch : it was already tested
    + Updated analysis : No class as typehint accepts abstract classes
    + Updated analysis : Create Magic Property reachs out to traits
    + Updated analysis : Security also reports usage of unserialize()
    + Updated analysis : Mistmatched default argument also covers methods
    + Updated analysis : Never used parameter also covers methods
    + Updated analysis : Unused global also cover static variables
    + Updated analysis : Duplicate strings threshold is not 15, not 5.
    + Checked unit tests : 3289 / 3319 test pass (99% pass)

+ Tokenizer
    + RETURNTYPE, TYPEHINT, and DEFAUT are not always on, with Void atom, or better.
    + DEFAULT value targets end-values, skips ??, ?:, () and =.
    + Exceptions now reports errors in the Query, not where it is thrown

**Version 1.9.6 (Star of Birth, 2019-09-23)**


+ Architecture
    + Moved new elements to Complete/
    + Moved new elements to Dump/
    + Initial configuration of project now includes analysis parameters with default
    + Added descriptions to Rulesets
    + New command Config : displays current configuration for reuse and editing
    + Upgraded Doctor : support for docker-php, in-code 

+ Report
    + Ambassador : removed {} on magic property inventory
    + Ambassador : new inventory of network protocols used (udp://, ssh2://...)

+ Analysis
    + New analysis : avoid mb_string inside loops
    + New analysis : avoid SSLvx and TLSv1.0
    + New analysis : report duplicate literal in the code, with parameter
    + New analysis : warn about null property
    + New coverage : calls to __call and __callStatic
    + Updated coverage : expressions with parenthesis
    + Updated coverage : default values are now targeting the final value in multiple assignations.
    + Updated analysis : Strange Variable name skips Staticdefinition and its default value 
    + Updated analysis : Useless instructions are upgrade with pure functions
    + Updated analysis : Extended Closure2string with Arrowfunctions
    + Updated analysis : Extended 'Could be local variable' to traits
    + Updated analysis : Unused Global also covers static variables
    + Checked unit tests : 3279 / 3304 test pass (99% pass)

+ Tokenizer
    + Updated tokens for PHP 7.4

**Version 1.9.5 (Star of Adversity, 2019-09-16)**


+ Architecture
    + Added count property to Analysis node, stepstone for Diff analysis
    + Added support for 'optional' step 
    + Added support for 'interfaces' as typehint for remote definitions
    + Removed more true/false values
    + Fixed strtolower with mb_strtolower in Dump

+ Report
    + Added several PHP error messages 
    + Ambassador : added inventory of magic properties
    + Ambassador : added inventory of typehints for methods (WIP)
    + Added support for function/closure/argument arguments
    + Added support for function/closure/argument arguments

+ Analysis
    + New analysis : No literal value as referenced argument
    + New analysis : use array_slice or array_splice
    + New analysis : Useless typechecks with Typehint
    + New analysis : Report non-implemented interfaces
    + New analysis : Incompatible Signatures with Self (PHP 7.4+)
    + New analysis : Report wrong expectations from interfaces
    + Upgraded analysis : Excluded __construct and __destruct from Magic Methods
    + Upgraded analysis : Concat and Addition : Now also for bitshift
    + Upgraded analysis : Incompatible Signatures with Self (PHP 7.3)
    + Upgraded analysis : Elseif and Sequences are omitted in Level analysis

+ Tokenizer
    + Upgraded support for magic properties

**Version 1.9.4 (Star of Benefit, 2019-09-09)**


+ Architecture
    + Dump avoid storing multiple definition for the same class
    + Added more native return definitions
    + Adding UT for Complete/
    + Dump inventories are being moved to analysis class
    + Moving more Themes => rulesets

+ Report
    + Ambassador : Fixed several internal links
    + Ambassador : Displays the levels of nesting in the code
    + Ambassador : Upgraded compatibility report with PHP 7.4
    + New report : Stubs

+ Analysis
    + New analysis : PHP 7.4 New Directives
    + New analysis : Too many dimensions with array
    + New analysis : Check concat and coalesce precedence
    + New analysis : Adopt explode() third argument
    + New analysis : Ternary and useless assignation
    + New analysis : Nested ternary without parenthesis
    + New analysis : Spread operator with arrays
    + New analysis : Max level of indentation 
    + New analysis : Use Arrowfunctions
    + Upgraded analysis : Clone with non object handles containers
    + Upgraded analysis : Calling non-static methods statically
    + Upgraded analysis : Unresolved Instanceof
    + Upgraded analysis : Array_merge and variadic, extended to isset
    + Checked unit tests : 3234 / 3259 test pass (99% pass)

+ Tokenizer
    + Last element of list() is not omitted anymore

**Version 1.9.3 (Star of Longevity, 2019-09-02)**


+ Architecture
    + Created new Complete category, with data complement for analysis
    + Refactored constant propagation
    + Made code compatible with PHP 7.4
    + Rename project_themas to project_rulesets
    + Added support of -p with .exakat.yaml

+ Report
    + Ambassador : reworked presentation for visibility suggestions

+ Analysis
    + New analysis : report covariance and contravariance for compatibility
    + New analysis : no spread operator for hash values
    + New analysis : self-closing tags are omitted by strip_tags
    + New analysis : report Openssl_random_pseudo_byte second argument usage
    + New analysis : CURLPIPE_HTTP1 is obsolete
    + New analysis : removed PHP 7.4 directives
    + New analysis : do not use ... with array_merge without checks
    + Updated analysis : added crc32c as hash algorithm
    + Removed analysis : Removed Curly Arrays (double take)
    + Checked unit tests : 3219 / 3240 test pass (99% pass)

+ Tokenizer
    + Extended OVERWRITE to Interfaces
    + Extended support for class_alias()

**Version 1.9.2 (Star of Prosperity, 2019-08-26)**


+ Architecture
    + Introduced a new set of analysis : Complete
    + Cleaned code for PHP 7.4 usage
    + Refactored Query to skip impossible Gremlin calls
    + Now using Project for project names

+ Report
    + New report : classes dependencies (HTML version)
    + New report : files dependencies (HTML and DOT version)
    + Ambassador : datas -> data

+ Analysis
    + New analysis : {} are deprecated in PHP 7.4
    + New analysis : Don't use ENT_IGNORE
    + New analysis : fn is a PHP 7.4 keyword
    + Updated analysis : Functions/UseConstantAsArguments covers also password_hash()
    + Updated analysis : printf arguments now handles positional formatters
    + Checked unit tests : 3172 / 3199 test pass (99% pass)

+ Tokenizer
    + Fixed precedence for left associativity

**Version 1.9.1 (Star of Life, 2019-08-19)**


+ Architecture
    + Fixed zip as code source

+ Report
    + Ambassador : Fixed issues list for Favorites
    + Owasp : switched dashboards

+ Analysis
    + Updated analysis : Loop Calling got one extra check
    + Checked unit tests : 3148 / 3187 test pass (99% pass)

**Version 1.9.0 (Ming Wenzhang of Jiayin, 2019-07-29)**


+ Architecture
    + Added missing configuration file for tinkergraph 3.4
    + Upgraded support for running exakat with PHP 7.4

+ Analysis
    + New analysis : array_key_exists() now report object usage
    + New analysis : report mb_strrpos 4th argument
    + New analysis : Reflection export are deprecated
    + New analysis : Report classes without parents but with 'parent'
    + New analysis : Don't use scalar as arrays
    + New analysis : Report use of PHP 7.4 serialize method
    + Updated analysis : Multiple Identical Keys checks for undefined keys first
    + Updated analysis : Dont be too manual : extended to catch clauses
    + Updated analysis : setcookie detection anchors the keyword at the beginning of the string
    + Updated analysis : Failed Substr comparison now works with constants
    + Updated analysis : Added support for continue 2 and 3
    + Checked unit tests : 3147 / 3186 test pass (99% pass)

+ Tokenizer
    + Added support for __serialize and __unserialize
    + Added support for numeric literal separator
    + Skip entirely unparsable files

**Version 1.8.9 (Meng Feiqing of Jiachen, 2019-07-22)**


+ Architecture
    + Check on graphdb configuration : default to nogremlin
    + Added support for baseline for project and report
    + Moved more doc to ruleset
    + Check on .git folder for update
    + Added -version option for upgrade command
    + Doctor honors .exakat.yml file

+ Analysis
    + New analysis : Report useless type of checks
    + New analysis : Disconnected classes 
    + New analysis : Avoid using mb_detect_encoding()
    + New analysis : Check that source and blind variables are different in foreach
    + New analysis : ~ or ! favorite
    + Updated analysis : Is Zero omits multiplications
    + Updated analysis : Used Private Property is upgraded
    + Updated analysis : Multiple Identical Keys : refactored
    + Updated analysis : Undefined variables now skips extract, include, eval
    + Checked unit tests : 3147 / 3166 test pass (99% pass)

+ Tokenizer
    + Refactored support for Foreach : each blind variable is in VALUE
    + Upgraded precedence for ! (not)
    + Propagate constants with assignations
    + Fixed link to $this inside heredoc and co
    + Fixed an edgecase where Static method call was confused with Newcall

**Version 1.8.8 (Wei Yuqing of Jiawu, 2019-07-15)**


+ Architecture
    + Modernized tinkergraph support
    + When pcntl is available, stubs are produced in a child process
    + Removed duplicated methods
    + Exported sequences to helpers
    + More UT libraries are supported
    + Federated BUSYTIMEOUT in constant

+ Report
    + Ambassador and all dependend reports were refactored : menu is configurable with Yaml
    + Emissary is the upcoming configurable report. 

+ Analysis
    + New step : Load data from code
    + New analysis : Variables used for setting aside value temporarily
    + New analysis : Use PHP array_* functions, instead of loops
    + Updated analysis : Unused methods now skips methods from PHP native interfaces (Arrayaccess)
    + Updated analysis : No class for typehint is now omitting PHP and extensions classes
    + Updated analysis : Switch to Switch applies to comparisons now
    + Updated analysis : Close namingg was sped up significantly
    + Updated analysis : array_column() suggestion was refined
    + Updated analysis : Htmlentities parameters also support some parenthesis usage
    + Updated analysis : Constant Scalar Expression only target specified expressions
    + Updated analysis : Static Properties skip Virtual properties
    + Checked unit tests : 3131 / 3155 test pass (99% pass)

+ Tokenizer
    + Refactored support for Exit and Die
    + Added raw support for phpdoc

**Version 1.8.7 (Hu Wenchang of Jiashen, 2019-07-08)**


+ Architecture
    + Added bugs fixes up to 7.3.7
    + New factory method for the graph

+ Analysis
    + New analysis : Backward compatible check on generators (can't return)
    + New analysis : Report wrong return typehint
    + New analysis : Use DateTimeImmutable
    + New concept : Methods that throw errors 
    + Updated analysis : Recursive functions disambiguate methods
    + Updated analysis : Refactored property/variable confusion
    + Updated analysis : Could typehint checks on type validations
    + Updated analysis : Variable used once check for abstract methods
    + Updated analysis : Array_merge in loops omits file_put_contents()
    + Updated analysis : Simple Regex covers all special sequences, and unicode sequences
    + Checked unit tests : 3131 / 3142 test pass (99% pass)

+ Tokenizer
    + Differentiated support for self and static in calls
    + Moved Symfony support to its extension
    + Reworked loading to make it parallels. 

**Version 1.8.6 (Wei Yuqing of Jiawu, 2019-07-01)**


+ Architecture
    + Added support for Tinkegraph 3.4
    + Extended support for Dev 
    + Renamed Themes to Ruleset (WIP)
    + Split several long running queries into smaller chunks
    + Cached files to memory, write them once only
    + Optimized sides queries : omitting them when possible
    + Added count of issues in Analyse node
    + Optimized loading by grouping by inV
    + More coverage for Arrowfunction

+ Report
    + Dump : collect PHP cyclomatic complexity

+ Analysis
    + New analysis : Dependant abstract classes
    + New analysis : Don't use Null or Boolean as an array
    + New analysis : Infinite recursion
    + Updated analysis : Raised levels 
    + Updated analysis : Method signature must be compatible
    + Updated analysis : Access Private in Trait is OK
    + Updated analysis : Recursive function 
    + Checked unit tests : 3099 / 3105 test pass (99% pass)

+ Tokenizer
    + Upgraded support for 'Modules'

**Version 1.8.5 (Zhan Zijiang of Jiaxu, 2019-06-24)**


+ Architecture
    + Fixed several bugs in the online documentation
    + Started removing analysis, replacing with analysis
    + Fixed path in docker PHP usage.

+ Report
    + Ambassador : Export full INI and YAML config to replicate audit

+ Analysis
    + New analysis : Unused class constants
    + New analysis : Could Use available Trait
    + New analysis : literal that Could Be Constant 
    + Updated analysis : Access Private in Trait is OK
    + Updated analysis : multiple identical argument is extended to closures, methods
    + Updated analysis : ext/rdkafka
    + Updated analysis : No Hardcoded Hash is accelerated
    + Updated analysis : Extended printf() check to constants
    + Updated analysis : Optimized 'redefined method'
    + Updated analysis : Memoize Magic Call
    + Updated analysis : set_locale requires constants
    + Checked unit tests : 3099 / 3105 test pass (99% pass)

+ Tokenizer
    + Added missing isModified to Foreach keys
    + Class Method Definition handles old style constructor
    + strict_types don't yield a block
    + Added typed values for magic constants
    + Refactored new -> constructor link for Self, Static, parent
    + Added missing arguments count to Newcall
    
**Version 1.8.4 (Wang Wenqing of Jiazi, 2019-06-17)**


+ Architecture
    + Added support for PHP in docker images for compilation tests
    + First prototype for Gremlin in a specific docker image 

+ Report
    + Ambassador : restored original URL
    + Replaced 'Complexity' => 'Time To Fix'
    + Replaced 'Receipt' => Ruleset

+ Analysis
    + New analysis : regex with arrays
    + New analysis : Complex property names
    + New analysis : array_key_exists speed up
    + New analysis : curl_version forbidden argument
    + New analysis : PHP 7.4 new functions, classes and constants
    + Fixed analysis : Long Variable
    + Updated analysis : printf() format check extended to constants
    + Updated analysis : Written only variables is extended to static and global
    + Updated analysis : refactored 'Make default' 
    + Updated analysis : 'Wrong number of arguments' is extended to methods
    + Updated analysis : 'Use coalesce' checks for
    + Updated analysis : Refactored 'Nested ifthen' to have a parameter
    + Updated analysis : Extended 'Class Usage' to return typehint
    + Updated analysis : Sped up 'Used Classes'
    + Checked unit tests : 2993 / 3071 test pass (97% pass)

+ Tokenizer
    + Upgraded handling of declare with strict_types
    + Support for magic properties across classes and traits
    + Added support for parent with properties
    + Properties are handled with static and normal at the same time
    + Fixed virtualproperties with static keyword (self and parent are ok)
    + Added argument count for 'new A', without parenthesis
    + Restored old break behavior for PHP 5 and older.

**Version 1.8.3 (Jade Man of Yang, 2019-06-10)**


+ Architecture
    + Extension docs show version numbers
    + Manual uses internal links

+ Report
    + New report : SARB
    + Updated report : Ambassador list number of arguments in natural order

+ Analysis
    + New analysis : from substr() to trim()
    + New analysis : suggest making magic property a concrete one (2 ways)
    + New analysis : no array auto-append
    + Updated analysis : 'Scalar or object property' refactored
    + Updated analysis : 'Multiple identical keys' get a new check on intval, broadened to constants
    + Updated analysis : 'Indirect injection' accelerated
    + Updated analysis : 'Could be class constant' accelerated
    + Updated analysis : 'Never used property' refactored
    + Updated analysis : 'Modern empty' modernized and broadened
    + Updated analysis : 'Useless check' skips isset/empty as they may be useful
    + Updated analysis : 'Identical methoods' skips abstract methods
    + Updated analysis : 'No Count Zero' also uses sizeof(), skips switch()
    + Checked unit tests : 2993 / 3071 test pass (97% pass)

+ Tokenizer
    + Upgraded local definitions for properties to Load phase
    + Handle static keyword in closures
    + Moved 'Real' to 'Float' 
    + Created 'Scalartypehint' atom
    + Fixed intval, boolval for \true and \false

**Version 1.8.2 (Zhao Ziyu of Dingchou, 2019-06-03)**


+ Architecture
    + Refactored 'Update' command, to VCS
    + Collect missing definitions counts
    + Report handles a list of analysis names

+ Analysis
    + New analysis : No Need To Get_Class
    + New analysis : Report identical inherited methods
    + New analysis : Function returning -1 in case of error
    + Updated analysis : TypeHint must be returned, doesn't apply to abstract methods or interface methods
    + Updated analysis : 'Could Use Interface' also checks for static and visibility
    + Updated analysis : 'Concat empty' skips variables
    + Checked unit tests : 3024 / 3048 test pass (99% pass)

+ Tokenizer
    + Created 'virtual' properties, for limiting children agglomerations
    + Fixed normalized code for use traits
    + Added DEFAULT to all variable definitions
    + Connect strings to class definitions
    + Handle variable in 'compact', when they are static

**Version 1.8.1 (Zhang Wentong of Dinghai, 2019-05-27)**


+ Architecture
    + Fixed Symlink destination
    + Added collecting classes children, traits and interfaces counts
    + Added support for constants and functions in modules
    + Added missing functions in data

+ Report
    + New report : exakatYaml, which help configuring exakat
    + New report : Yaml
    + New report : Top10
    + Updated report : Json, text and xml get 'fullcode'

+ Analysis
    + Updated analysis : Should use self is extended to parent classes
    + Updated analysis : Should use prepared statement now skips some SQL queries
    + Checked unit tests : 3024 / 3048 test pass (99% pass)

**Version 1.8.0 (Zang Wengong of Dingyou, 2019-05-20)**


+ Architecture
    + Added missing native PHP functions
    + Restored anchor for ignore_dirs[] configuration
    + Removed more MAX_LOOPING usage

+ Report
    + Ambassador : removed { & @ } artefacts from globals

+ Analysis
    + New analysis : Function returning -1 in case of error
    + New analysis : Report PHP 7.4 unpacking inside array
    + New analysis : Report PHP 7.4 new functions and fn
    + New analysis : Useless arguments
    + New analysis : Addition and concatenation precedence for PHP 7.4
    + New analysis : report concatenation of empty strings
    + New analysis : casting has precedence over ternary
    + New analysis : report already used traits
    + New analysis : report missing traits in use expression
    + Updated analysis : isset on whole arrays : extended analysis to Phpvariables
    + Updated analysis : SQLITE3 requires single quotes
    + Updated analysis : Dir then slash : extended to constants
    + Updated analysis : Variable Strange Name extended to strange types
    + Updated analysis : Possible interface's analysis is sped up
    + Checked unit tests : 3021 / 3045 test pass (99% pass)

+ Tokenizer
    + Fixed fullcode of Usetrait
    + Extended method definitions to traits
    + Extended fluent interface detection to parents
    + Fixed dump for visibility change
    + Handle method aliases in use expression (as)
    + Better noDelimiter for double quotes strings

**Version 1.7.9 (Shi Shutong of Dingwei, 2019-05-13)**


+ Architecture
    + Upgraded list of functions by extension : openssl, math, hrtime
    + Added global atom to track all globals
    + Rewrote several Dump queries with DSL
    + Added support for Notice in Phpexec
    + Added support for .exakat.ini and .exakat.yaml
    + Added support for arrow functions : fn => 
    + Added support for spread operator in arrays [...[1,2,3]]

+ Report
    + Inventories : added 'inclusions' and 'global variables'
    + Ambassador : added global variables

+ Analysis
    + New analysis : support for ext/ffi, uuid
    + Updated analysis : Nested Ternary handles parenthesis
    + Updated analysis : Static loops is extended to references and arrays
    + Updated analysis : Recursive function is extended to Magic methods and Closures
    + Checked unit tests : 3014 / 3019 test pass (99% pass)

+ Tokenizer
    + Moved 'is_in_ignored_dir' to a property
    + Cleaned getFullnspath() call in Load
    + Fixed latent bug on Function fullnspath
    + Heredoc and Nowdoc are reported as constant if needed
    + Isset() is not read
    + Ignore PHP notices when linting
    + Globals are now centralised across a repository
    + Extended definitions for Virtualproperties
    + Removed double DEFINITION link with new

**Version 1.7.8 (Cui Juqing of Dingyi, 2019-05-06)**


+ Architecture
    + renamed test.php to ut.php in tests
    + reorganized destinations folders 
    + organized exakat for 'inside code' audit

+ Analysis
    + New analysis : support for libsvm
    + Updated analysis : Multiple unset() handles unset() at the beginning of the scope
    + Updated analysis : undefined static class now accounts for PHP and module classes
    + Checked unit tests : 2961 / 2995 test pass (99% pass)

+ Tokenizer
    + Extended class usage to static::class.
    + refactored 2 analysis for speed : double instruction and double assignations
    + fixed recent bug where Project token is twice.

**Version 1.7.7 (Sima Qing of Dingmao, 2019-04-29)**


+ Architecture
    + Upgraded to gremlin-php 3.1.1
    + Moved autoload into its own namespace
    + Started extending themes to modules
    + Skip external libraries when unit testing
    + Dump got one more query moved to DSL
    + Fixed build for overwritten methods, extended to magic methods
    + Load tokens by batch (5000+ tokens), not by file. 

+ Analysis
    + New analysis : Security : integer conversion
    + New analysis : implode() with one argument
    + Updated analysis : Invalid Regex handles \\ more precisely
    + Updated analysis : delimiter detection was checked for all of them
    + Checked unit tests : 2947 / 2983 test pass (99% pass)

+ Tokenizer
    + Upgraded Fallback detection for functions

**Version 1.7.6 (Jade Maiden of Yin, 2019-04-22)**


+ Architecture
    + Refactored Class definition with return typehint 
    + Added configuration for including development extensions.
    + Extended LoadFinal typehint hunting

+ Report
    + Phpcsfixer : new report
    + Ambassador : report usage of overridden PHP functions
    + Ambassador : new favorite : variable name in catch clause

+ Analysis
    + New analysis : array_merge and ellipsis should use coalesce
    + New analysis : Report overridden PHP native functions
    + New analysis : Merge all unset() into one
    + Updated analysis : Added missing constant for curl, pgsql, openssl
    + Updated analysis : Variadic are not variable arguments
    + Updated analysis : Useless Reference argument extended to foreach()
    + Updated analysis : Use Constant also covers pi()
    + Updated analysis : Inclusion Wrong Case handles dirname with 2nd argument
    + Updated analysis : Useless Argument : handles some edge cases with arrays
    + Checked unit tests : 2947 / 2975 test pass (99% pass)

+ Tokenizer
    + Upgraded handling of isRead and isModified attributes
    + Changed variadic argument counts in method declarations
    + Fixed original value in 'Sign'

**Version 1.7.5 (Xue King Zhuanlun, 2019-04-15)**


+ Architecture
    + Cleaned unused variables

+ Report
    + Ambassador : bugfixes report version 7.3, dropped 5.6 and 5.5

+ Analysis
    + Updated analysis : Already interface : extended to interface parents
    + Updated analysis : Else if to elseif : extended to one-liners
    + Updated analysis : No reference for ternary was extended
    + Updated analysis : Implements is for interface
    + Updated analysis : Refactored Is a Magic Property
    + Updated analysis : Refactored Conditional structures for constants
    + Checked unit tests : 2926 / 2950 test pass (99% pass)

+ Tokenizer
    + Link properties to magicmethod
    + Deduplicated virtual properties
    + Added isRead and IsModified properties. Omitting the corresponding analysis.

**Version 1.7.4 (Lu King Pingdeng, 2019-04-08)**


+ Architecture
    + reports, themes may be specified multiple times
    + 'project' command also work on themes and report from command line
    + Added htmlpurifier in auto-ignored libraries
    + Counting definitions, omitting Virtualproperties
    + Automatically detect identical files

+ Report
    + Inventories are grouped by values, sorted by count

+ Analysis
    + Updated analysis : This is for class : extended analysis to self and parent
    + Updated analysis : Undefined Classes
    + Updated analysis : Refactored Defined Parent MP 
    + Updated analysis : Redefined PHP function is restricted to global scope
    + Updated analysis : Could Use Alias also covers functions, constants.
    + Updated analysis : Refined SQL detection
    + Fixed step : goToALlParentsTrait missed some of the parent
    + Checked unit tests : 2916 / 2944 test pass (99% pass)

+ Tokenizer
    + Removed impossible implementations of traits
    + Fixed functioncalls' 'absolute' property
    + Refined parent's definitions
    + Trait also sports virtualproperties
    + Virtualproperties now respect visibilities
    + Distinguish Variables from Staticpropertynames
    + Added missing DEFINITION for Use (namespaces)

**Version 1.7.3 (Huang, King Dushi, 2019-04-01)**


+ Architecture
    + New command 'show' that display project creation command
    + Refactored UT detection mechanism

+ Report
    + Ambassador : report identical files in the code
    + Ambassador : global variable inventory is now grouped by name

+ Analysis
    + Updated analysis : PPPDeclaration style : handles Virtualproperties
    + Updated analysis : Closure2string : extended analysis
    + Updated analysis : Non-Ascii variable skips { }, & and @
    + Updated analysis : Could Be Static exclude abstract methods
    + Updated analysis : MismatchedTypehint : handles methodcalls and class hierarchy
    + Updated analysis : Could Use Try : refined analysis to avoid literals
    + Updated analysis : Hidden use, handles Virtualproperty
    + Updated analysis : Classes, wrong case, handles FQN
    + Checked unit tests : 2846 / 2926 test pass (97% pass)

+ Tokenizer
    + Moved creation of Virtualproperty early, to catch more situations
    + Virtualproperty mimic Propertydefinition
    + Added extra check when roaming the classes tree
    + Handles Sign constant values correctly

**Version 1.7.2 (Dong King Taishan, 2019-03-25)**


+ Architecture
    + Restored the external library checker
    + Added support for extension's CIT (Symfony, Drupal)

+ Report
    + Ambassador : added Suggestions theme to docs.
    + Perfile : New report, text, per file

+ Analysis
    + New analysis : Report potential 'unsupported operand type'
    + New analysis : Check for existence with __call() and __callstatic
    + Updated analysis : Wrong number of arguments (methods) upgraded
    + Updated analysis : Could Be Static ignores empty methods, constants methods
    + Updated analysis : Added Variable to possibly useless expression
    + Updated analysis : Constant names are detected based on available noDelimiter
    + Updated analysis : Abstract classes may have no abstract methods
    + Checked unit tests : 2889 / 2912 test pass (99% pass)

+ Tokenizer
    + Added link between __clone and clone
    + Now handling functions and constants when ignored
    + Fixed dynamic constants in collector

**Version 1.7.1 (Bi King Biancheng, 2019-03-18)**


+ Report
    + Ambassador : report lines that concentrate lots of issues

+ Analysis
    + Extended GoToAllImplements to extended interfaces
    + Updated analysis : NoScream usage, with authorized functioncall list like fopen
    + Updated analysis : HiddenUse with support for virtual properties
    + Checked unit tests : 2867 / 2900 test pass (99% pass)

+ Tokenizer
    + Added support for 'Virtualproperties'
    + Harmonized file escaping feature

**Version 1.7.0 (Bao King Yama, 2019-03-11)**


+ Architecture
    + Added auto-documenting 'ignored' cit to weed out obvious false positive

+ Report
    + Made Diplomat the default report
    + Added History report : it stores metrics from audit to audit

+ Analysis
    + New analysis : Identify self transforming variables ($x = foo($x))
    + New analysis : Report unclonable variables
    + Updated analysis : Undefined Classes, Interfaces and Trait now omit 'ignored' cit from folders
    + Updated analysis : Inconsistent usage is refactored for properties
    + Updated analysis : Useless expression, with clone new x
    + Updated analysis : Only Variable For Reference accepts $this, $_GET
    + Updated analysis : Lost References was modernized
    + Checked unit tests : 2854 / 2884 test pass (99% pass)

+ Tokenizer
    + Refactored support for Staticmethod (in a trait's use)
    + Added definitions for trait's use
    
**Version 1.6.9 (Lu King Wuguan, 2019-03-04)**


+ Architecture
    + Optimized Dump when navigating the links to the File Atom
    + Refactored LoadFinal into separate classes
    + Upgraded to Tinkergraph 3.3.5
    + Added options to cleandb to stop and start gremlin from exakat
    + Skip the task if no analysis has to run

+ Analysis
    + New analysis : Report inconsistent usage of properties or variables
    + New analysis : Typehinted return must return
    + Updated analysis : Variables used once handles closure (use) correctly
    + Updated analysis : Is Zero was refactored partially (WIP)
    + Updated analysis : Bad Typehint relay got a fix
    + Updated analysis : Function Subscripting is only suggested for one usage
    + Updated analysis : Lost References was modernized
    + Checked unit tests : 2854 / 2881 test pass (99% pass)

+ Tokenizer
    + Added definition for injected properties
    + Fixed sack() for subqueries
    + $this is not a classic variable
    + Removed double DEFINITION links
    + Fixed edge case with define() at the end of a script

**Version 1.6.8 (Yu King Songdi, 2019-02-25)**


+ Architecture
    + Added support for PHP 8.0
    + Fixed Constant FNP
    + Advance progressbar when ignoring files

+ Report
    + Ambassador : report usage of factories
    + Collect stats about Foreach usage

+ Analysis
    + New analysis : Report violation of law of Demeter
    + New analysis : Report removed constants and functions in PHP 8.0
    + Updated analysis : Refactored Nullable Typehint
    + Checked unit tests : 2851 / 2872 test pass (99% pass)

+ Tokenizer
    + Fixed edge case for Logical with strings
    + Reduced max level of looping in GoToAllParents
    + Distinguish $$ and ${$

**Version 1.6.7 (Li King Chujiang, 2019-02-18)**


+ Architecture
    + Documentation covers more PHP functions
    + Added some missing PHP functions
    + Fixed destination folder for extensions

+ Report
    + Ambassador : limited size of default values in visibility report.
    + Ambassador : reporting class depth
    + Ambassador : reporting dynamically created constants
    + Diplomat : leanner, meaner version of Ambassador
    + New category : Top 10 classic mistakes

+ Analysis
    + New analysis : Report when relayed typehint are not the sames
    + Updated analysis : Regex now handles local variables and constants
    + Updated analysis : Variables Used Once now covers closures and use
    + Checked unit tests : 2846 / 2867 test pass (99% pass)

+ Tokenizer
    + Defineconstant may be constant
    + Fixed handling of Nullable for typehint
    + Started preparing for Gremlin 3.4.0 : WIP

**Version 1.6.6 (Jiang King Qinguang , 2019-02-11)**


+ Architecture
    + Removed FetchContext() from DSL
    + Added options to follow constants from atomIs.

+ Report
    + Now dumps magic methods

+ Analysis
    + New analysis : Report insufficient interfaces in typehint
    + Updated analysis : Class constant now ignore empty classes
    + Checked unit tests : 2837 / 2858 test pass (99% pass)

+ Tokenizer
    + Moved 'Define' to its own atom
    + Upgraded Logical to hanlde Strings as PHP
    + Fixed T_POWER => T_POW
    + Refactored calculation for globalpath
    + Fixed edgecase with endswitch;

**Version 1.6.5 (Mahagate, 2019-02-04)**


+ Architecture
    + Added CVS as an external service
    + Graph GSNeo4j export variable for shell access. putenv is not sufficient
    + Dump : report class name, not its code
    + Extended listAllThemes to extensions
    + Fixed bug in extension loader with phar

+ Report
    + Ambassador : restored file dependencies tree
    + Ambassador : fixed altered directive filename
    + Ambassador : added direct link to docs

+ Analysis
    + New analysis : arrays that are initialized with strings
    + New analysis : Avoid Lone variables as conditions
    + New analysis : Added support for weakref and pcov
    + Updated analysis : extended regex to arrays in preg_* calls
    + Updated analysis : Implicit globals now also marks the variable in global space
    + Updated analysis : Add Zero, Multiply by One also cover 2 * $x = 1;
    + Updated analysis : Could Use Interface now takes into account PHP interfaces, and classes first level.
    + Updated analysis : Relay Functions now omits calls to parent's __construct and __destruct
    + Checked unit tests : 2830 / 2852 test pass (99% pass)

**Version 1.6.4 (Parasamgate, 2019-01-28)**


+ Architecture
    + Added support for CVS as a VCS
    + Upgraded support for tar as a VCS
    + Added support modification counts by files
    + Added first tracking for closures
    + Upgraded Tinkergraph driver

+ Report
    + Added Atoms in the documentations
    + Extra protection for Class Changes

+ Analysis
    + Updated analysis : Use-arguments are now counted as arguments
    + Updated analysis : Max Argument check was refactored
    + Updated analysis : IsModified now takes into account extensions
    + Updated analysis : Should Use This now exclude empty methods
    + Updated analysis : undefined classes now support PHP 7.4 typed properties
    + Updated analysis : added missing scalar PHP types
    + Updated analysis : uncaught exceptions now cover parents
    + Updated analysis : refactored incompatibility checks for methods
    + Checked unit tests : 2824 / 2841 test pass (99% pass)

+ Tokenizer
    + Refactored alternative ending, removed extra VOID
    + Upgraded contexts and their nesting
    + Added extra checks on variables names
    + Added support for ??= (PHP 7.4)

**Version 1.6.3 (Paragate, 2019-01-21)**


+ Architecture
    + Better presentation for exakat extensions
    + Added build.xml for Jenkins
    + Fixed copyright years

+ Report
    + Ambassador : fixed class name for Phpcompilation

+ Analysis
    + New analysis : assign and compare at the same time
    + Updated analysis : uncaught exceptions now cover parents
    + Updated analysis : strpos too much is extended to strrpos and strripos
    + Updated analysis : Refactored Indirect injections for more refined reports
    + Updated analysis : Empty Block doesn't omit Ifthen anymore
    + Updated analysis : Implemented methods are public mistook interface methods
    + Updated analysis : Object Reference omits arguments that are wholly assigned
    + Checked unit tests : 2808 / 2826 test pass (99% pass)

+ Tokenizer
    + Added support for PHP 7.4 typed properties (needs PHP 7.4-dev)

**Version 1.6.2 (Silver Headed Gate, 2019-01-14)**


+ Architecture
    + Fixed infinite loop when an option missed a value
    + Produce phpversion in config.ini, but leave it commented

+ Report
    + Ambassador : colored syntax for visibility report
    + Ambassador : inventory reports now display number of usages

+ Analysis
    + Updated analysis : Added support for PHP 7.2.14
    + Updated analysis : Avoid Using Class handles \
    + Updated analysis : Unused Functions works with multiple identical functions
    + Checked unit tests : 2795 / 2817 test pass (99% pass)

+ Tokenizer
    + Fixed bug that mixed T_OR and T_XOR
    + Fixed bug that missed intval for Power
    + Handles multiple definitions of functions
    + Removed one Void too many with closing tag

**Version 1.6.1 (Golden Light Gate, 2019-01-07)**


+ Architecture
    + Upgraded documentation for Extensions
    + Upgraded processing of files, specially with special chars
    + Project stops when no token are found
    + Storing hash for each files. RFU.

+ Report
    + Ambassador : added support for class constant's changes
    + Ambassador : added classSize report
    + Ambassador : 'New issues' now takes line difference into account
    + Themes are better dumped

+ Analysis
    + New analysis : array_key_exists() is faster in PHP 7.4
    + New analysis : partial report from preg_match()
    + Updated analysis : Avoid Using Class handles \
    + Updated analysis : Class Usage uses class_alias()
    + Updated analysis : Empty traits 
    + Updated analysis : Unused arguments now skips __set()
    + Updated analysis : Path strings
    + Updated analysis : Missing include handles more concatenations
    + Checked unit tests : 2792 / 2812 test pass (99% pass)

+ Tokenizer
    + Fixed precedence for identical operators
    + Fixed bug with ?> inside switch

**Version 1.6.0 (VirupakSa, 2018-12-31)**


+ Architecture
    + VCS are not tested when they are not used

+ Analysis
    + Updated analysis : Php Reserved names ignores variable variables
    + Updated analysis : Array not using a constant, with Heredoc
    + Updated analysis : Long arguments
    + Updated analysis : Empty With Expression ignores simple assignations
    + Refactored analysis : Callback needs returns
    + Refactored analysis : No Return used
    + Checked unit tests : 2780 / 2805 test pass (99% pass)

+ Tokenizer
    + Fixed regression with Yield and =>
    + Fixed edge case "$a[-0x00]"

**Version 1.5.9 (Dhrtarastra, 2018-12-24)**


+ Architecture
    + Use PHP in project config for default PHP version
    + cleandb uses -p
    + Moved projects/.exakat to projects/<-p>/.exakat folders
    + Using $config and not more hardcoded tinkergraph
    + Extra check on doctor 

+ Report
    + Ambassador : extra check for 'previous' report

+ Analysis
    + Upgraded analysis : Empty With Expression skip a few false positive
    + Checked unit tests : 2770 / 2795 test pass (99% pass)

+ Tokenizer
    + Fixed edgecase for methods named 'class'
    + Fixed class name in Project

**Version 1.5.8 (Virudhaka, 2018-12-17)**


+ Architecture
    + Handles themas provided by extensions
    + Added busyTimeout for dump.sqlite
    + Reduced size of thema tables
    + Docs handle parameter dynamically
    + Added 'update' for extensions

+ Report
    + Ambassador : added a 'Path' inventory, with file paths

+ Analysis
    + New analysis : Closures that are identical
    + Upgraded analysis : Url and SQL detection, case sensitivity
    + Upgraded analysis : Could Use array_fill_keys
    + Upgraded analysis : Undefined functions doesn't miss functions inside classes, handles interfaces
    + Upgraded analysis : Empty Functions better handles return; 
    + Upgraded analysis : Long Argument may be configured
    + Upgraded analysis : Fixed bug with empty include path
    + Checked unit tests : 2770 / 2795 test pass (99% pass)

+ Tokenizer
    + Added FNP to strings
    + First link between method and definition with typehint
    + Support for class_alias
    + Fixed edge case with use ?>
    + Fixed variable in string behavior for $this and $php variables

**Version 1.5.7 (Vaisravana, 2018-12-10)**


+ Architecture
    + Extended Dump to support aliased methods
    + Support for SQLITE in extensions
    + Moved each framework to extensions
    + Added Laravel extension

+ Documentation
    + First version for the Extension chapter
    + Fixed mysterious ' in the docs

+ Report
    + Ambassador : added a 'New issues' section, with new analysis
    + Ambassador : added trait matrix
    + Ambassador : fixed an infinite loop when trait include themselves in cycles
    + Added more message count to several reports

+ Analysis
    + New analysis : method could be static
    + New analysis : multiple inclusion of traits
    + New analysis : avoid self using traits
    + New analysis : ext/wasm and ext/async
    + Upgraded analysis : No Hardcoded Hash, skip hexadecimal numbers
    + Upgraded analysis : Defined properties extends to traits
    + Upgraded analysis : PSS outside a class, when PSS are in strings
    + Upgraded analysis : Access private works with methods (not just static)
    + Checked unit tests : 2772 / 2785 test pass (99% pass)

+ Tokenizer
    + Fixed bug in Dump, when nothing to clean
    + Fixed edge bug on Callable detection
    + Extended support for self, static and parent, in typehint and new
    + Fixed precedence of yield and yield from
    + Fixed handling of throw at the end of a script
    + Added support to solve conflict on traits

**Version 1.5.6 (Jingang, 2018-12-03)**


+ Architecture
    + Moved all framework to extensions. WIP.
    + Code cleaning
    + Refactored the analysis dependency sorting
    + Now display progress bar for files
    + Fixed configuration for directories and files

+ Report
    + Fixed FileDependecy and DependencyWheel, to actually count messages

+ Analysis
    + Added a lot more new method descriptions for PHP native classes
    + New analysis : suggestion simplification for !isset($a) || !isset($a[1])
    + New analysis : Useless Trait alias
    + New analysis : report usage of ext/sdl
    + Upgraded analysis : Refactored IsZero, to handle assignations and parenthesis
    + Upgraded analysis : pack format is better checked
    + Checked unit tests : 2759 / 2771 test pass (99% pass)

+ Tokenizer
    + Fixed a missing fullnspath for origin in Use for Traits
    + Handles simple aliases for traits methods
    + Fixed mishandling of variables inside strings
    + Fixed support of negative numbers inside strings
    + Fixed bug with yield inside an array
    + Fixed strange case with define and integers as constant names

**Version 1.5.5 (Ratnadhvaja, 2018-11-25)**


+ Architecture
    + Initial version of Exakat extensions
    + Moved processing of 2-tokens files to Load
    + Speed up CSV creations
    + Upgrades are read from https, no http
    + Moved loading's sqlite to memory for speed gain
    + Doctor now auto-create test folder

+ Report
    + New report : Php city. See your PHP code as a city
    + Ambassador : Appinfo() now reports keywords used as method or property
    + Fixed reported names of properties

+ Analysis
    + New analysis : checks some HTTP headers for security
    + New analysis : Use _file() functions, not file_get_contents()
    + New analysis : Optimize looks for fgetcsv()
    + Upgraded analysis : Several refactored analysis
    + Checked unit tests : 3083 / 3096 test pass (99% pass)

+ Tokenizer
    + Fixed encoding error in loading, for clone types.

**Version 1.5.4 (Mahakasyapa, 2018-11-19)**


+ Architecture
    + Added error message for memory limit 
    + Added GC to Project action
    + Migrated Melis to extension
    + Dumping data is now done en masse
    + Analysers now handle side-queries
    + Clear message in case of memory limit
    + Doctor doesn't stop at missing helpers
    + VCS leak less errors
    + Added support for 7z
    + Extended validation for themas
    + Restored Tinkergraph driver 
    + Upgrade logs with extra reports

+ Analysis
    + New analysis : Report problems with class constant visibilities
    + New analysis : Avoid self, parent and static in interfaces
    + Upgraded analysis : Variable reuse now skips empty arrays
    + Checked unit tests : 3077 / 3090 test pass (99% pass)

+ Tokenizer
    + Fixed bug where variable was mistaken for a string inside strings

**Version 1.5.3 (Ananda, 2018-11-12)**


+ Architecture
    + Extended results to methods, traits
    + Added support for PHP 7.2.12
    + 'master' is not used anymore as default branch
    + Fixed creation of initial config/exakat.ini
    + Fixed handling badly written exakat.ini or PHP binary paths

+ Report
    + Ambassador : report classes that could be final or abstract

+ Analysis
    + New analysis : Property Used Once : now includes redefined functions
    + New analysis : iterator_to_array() should use yield with keys or array_merge()
    + New analysis : Don't loop on yield : use yield from
    + Upgraded analysis : Dependant trait now include parent-traits
    + Checked unit tests : 3080 / 3093 test pass (99% pass)

+ Tokenizer
    + Changed handling of variable that are both global AND local
    + Disambiguated variables and properties
    + Extended OVERWRITE to constants and methods

**Version 1.5.2 (Master Puti, 2018-11-05)**


+ Report
    + Fixed storage of themes in dump.sqlite
    + Ambassador : report nothing when there are no trait, interface or class in the tree.

+ Analysis
    + New analysis : idn_to_ascii() will get new default
    + New analysis : support for decimal extension
    + New analysis : support for psr extension
    + Upgraded analysis : Extended support to PHP native exceptions
    + Upgraded analysis : Could use typecast now handles intval() second param
    + Upgraded analysis : Variable strange names avoids properties
    + Checked unit tests : 3058 / 3085 test pass (99% pass)

+ Tokenizer
    + Upgraded support for arrays inside strings (string/constant distinction)
    + Added DEFINITION for constant() and defined()
    + Fixed value of line for some placeholder definition

**Version 1.5.1 (Eighteen Arhats, 2018-10-29)**


+ Analysis
    + New analysis : could use basename() second args
    + Upgraded analysis : Variables strange names do not report ...
    + Checked unit tests : 3061 / 3079 test pass (99% pass)

+ Tokenizer
    + Moved TRAILING as a property
    + Moved NULLABLE as a property
    + Sync ALIAS with AS
    + Fixed link between Use expression when using an alias

**Version 1.5.0 (Pilanpo Bodhisattva, 2018-10-22)**


+ Architecture
    + Fixed " in the examples of the manual
    + Upgraded stability with new history testing

+ Report
    + Ambassador : now report interface and trait hierarchy
    + Ambassador : new format inventory for pack and printf
    + Dump : Fixed list of traits

+ Analysis
    + New analysis : Could Use Try, for native calls that may produce an exception
    + New analysis : idn_to_ascii() will get new default
    + Upgraded analysis : Undefined variables exclude $this
    + Upgraded analysis : Variables used once avoid properties
    + Upgraded analysis : ext/json : JsonException
    + Upgraded analysis : added new PHP 7.3 constants (curl, pgsql, mbstring, standard)
    + Upgraded analysis : scalar or object property now ignore NULL as default
    + Refactored analysis : UsedProtectedMethod
    + Checked unit tests : 3059 / 3071 test pass (99% pass)

+ Tokenizer
    + Handles NaN and INF when the literals reach them
    + Static constant may be variable if object is variable
    + Removed superfluous linking for static calls.

**Version 1.4.9 (Lingji Bodhisattva, 2018-10-15)**


+ Architecture
    + Extended documentation with phpVersion, time to fix and severity
    + Upgraded bufixes to PHP 7.2.11
    + Added more tests on arguments in the DSL
    + Removed double definitions for class constants
    + Initial support for extension folder

+ Report
    + Collect the number of local variables, per method

+ Analysis
    + New analysis : report accessing properties the wrong way
    + New analysis : suggest named patterns
    + New analysis : check Pack() arguments
    + New analysis : Return in generators, for PHP 7.0 +
    + New analysis : Repeated interfaces
    + New analysis : Static properties shouldn't use references until PHP 7.3
    + New analysis : Don't read and write in the same expression
    + Upgraded analysis : is interface methods, extended to magic methods
    + Upgraded analysis : empty regex
    + Upgraded analysis : never used properties
    + Upgraded analysis : logical operators in letters
    + Upgraded analysis : could use interface, extended with PHP native interfaces
    + Upgraded analysis : Is Zero, better handling of mixed expressions
    + Refactored analysis : Empty functions
    + Refactored analysis : Used Private Methods
    + Checked unit tests : 3036 / 3055 test pass (99% pass)

+ Tokenizer
    + Added DEFINITION between new and __construct
    + Added support for className::class() 
    + Added better support for dynamic method calls
    + Added better support for dynamic property calls
    + Removed some usage of TokenIs

**Version 1.4.8 (Ksitigarbha, 2018-10-08)**


+ Architecture
    + Adding more validation at DSL step level : stricter check on args, speed gain
    + Cleaning more analysis from MAX_LOOPING variable
    + Better protection for file names 
    + Removed static properties from DSL

+ Analysis
    + New analysis : Don't use __clone before PHP 7.0
    + New analysis : Watch out for filter_input as a data source
    + Upgraded analysis : Method Used Below refactored for speed
    + Upgraded analysis : Undefined class constants now takes into account interfaces
    + Removed anaysis : Relaxed Heredoc was double with Flexible Heredoc
    + Checked unit tests : 3016 / 3033 test pass (99% pass)

+ Tokenizer
    + Build links between methodcall and method in a class
    + Added links between method and its overwritten version in child
    + Fixed fallback for functions
    + Fixed linked between traits and their definition
    + Removed variable definition for Parametername
    + Simplified double usage between return and pushExpression()

**Version 1.4.7 (Maitreya, 2018-10-01)**


+ Architecture
    + Added 'Suggestions' section to documentation, for many rules
    + WIP : removing usage of MAX_LOOPING in analysis
    + Added a lot of new external services
    + Added documentation for creating a new analysis

+ Analysis
    + Upgraded analysis : No interface was dropped in PHP 7.2
    + Upgraded analysis : IsAMagicProperty extended to parents
    + Removed anaysis : Relaxed Heredoc was double with Flexible Heredoc
    + Checked unit tests : 3017 / 3029 test pass (99% pass)

+ Tokenizer
    + Linking variable in closure's use to its local variable
    + Removed some unused atoms from GraphElements

**Version 1.4.6 (Dipankara, 2018-09-24)**


+ Architecture
    + Various code refactorisations
    + Migration to PHPUnit 7.3.5
    + Fixed filenames case
    + Better handling of VCS
    + More validations for project names
    + More docs

+ Report
    + Ambassador/Weekly : fixed ' in analyser titles

+ Analysis
    + Upgraded analysis : Fopen mode accepts 'r+b'
    + Upgraded analysis : Unused Traits
    + Upgraded analysis : Undefined Variables
    + Checked unit tests : 3020 / 3033 test pass (99% pass)

+ Tokenizer
    + New analysis : report literal used with reference
    + Added support for boolval to Keyvalue
    + Fixed support for boolval to Arraylist
    + Added DEFINITION to static methods
    + Added Variabledefinition for local variables
    + Fixed bug in Not

**Version 1.4.5 (Guanyin Bodhisattva, 2018-09-17)**


+ Architecture
    + Removed times() for until() in Dumps

+ Report
    + Manual : added folders tree

+ Analysis
    + New analysis : Add Default To Parameter
    + Upgraded analysis : Avoid reporting PHP function as classes
    + Upgraded analysis : More empty Functions than just foo() {}
    + Upgraded analysis : Wrong Number of argument now takes into account variadic
    + Upgraded analysis : Should Use Constant now encompasses () and ?: structures
    + Upgraded analysis : This Is Not An Array now takes ArrayObject/SimpleXmlElement into account
    + Checked unit tests : 3009 / 3020 test pass (99% pass)

+ Tokenizer
    + Fixed 'constant' status with Arrayliteral
    + Fixed bug where strings are build close to the end of the script

**Version 1.4.4 (White Dragon Horse, 2018-09-10)**


+ Architecture
    + Doctor reports the set of tokens used
    + Lots of docs checks

+ Report
    + Ambassador / Phpconfiguration : report disable_functions and disable_classes
    + Finished Weekly report

+ Analysis
    + New analysis : report ext/seaslog
    + Upgraded analysis : Incompatible signatures
    + Fixed DSL : analysisIs
    + Checked unit tests : 3000 / 3010 test pass (99% pass)

+ Tokenizer
    + Closure are now processed with runplugin
    + Removed depencencies to usedClasses
    + Fixed detections of Closure at the end of a script

**Version 1.4.3 (Sha Wujing, 2018-09-03)**


+ Architecture
    + No error if missing svn
    + Extended 'First' thema
    + Now reporting PHP native CIT, constants and functions

+ Report
    + Ambassador : php.ini suggestions includes disable_functions

+ Analysis
    + New analysis : report typecasting for json_decode
    + New analysis : report classes that could be final
    + New analysis : simplify closure into callback
    + New analysis : report inconsistent elseif conditions
    + Upgraded analysis : Reduced false positive on Type/Default mismatch
    + Upgraded analysis : Drop Else After Return uses elsif
    + Upgraded analysis : Unused Private Property (rare)
    + Checked unit tests : 2990 / 3004 test pass (99% pass)

+ Tokenizer
    + Removed extra Void after function definitions
    + Fixed fullnspath with define()

**Version 1.4.2 (Zhu Bajie, 2018-08-27)**


+ Architecture
    + Fixed leftover bugs in the new DSL language
    + Adopter Query in LoadFinal (first test)
    + Extended support for clone type 1

+ Report
    + New Report : Weekly report

+ Analysis
    + New analysis : report forgotten conflict in traits
    + New analysis : undefined insteadof
    + New analysis : undefined variable
    + New analysis : report classes that must call parent::__construct
    + Upgraded analysis : Inexistant Compact variable
    + Upgraded analysis : Test class was refactored
    + Checked unit tests : 2975 / 2989 test pass (99% pass)

+ Tokenizer
    + New atom : Staticmethod, for Insteadof (replacing 'Staticconstant')
    + Added DEFINITION link for array('class', 'method') structure

**Version 1.4.1 (Tang Sanzang, 2018-08-20)**


+ Architecture
    + Spined off Query for Gremlin, with Exakat DSL.
    + Centralized 'methods' property in Analysis class
    + Extended MAX_LOOPING usage

+ Analysis
    + Added new thema : Class Review
    + Upgraded analysis : Defined Parent MP (less queries)
    + Upgraded analysis : Less false positives
    + Added support for PHP 7.2.9
    + Checked unit tests : 2965 / 2980 test pass (99% pass). 

+ Tokenizer
    + Fixed Edge case with Ternary and Boolean
    + Added Staticpropertyname to distinguish from variables
    + Added support for remote definitions to methods
    + Removed global path for CIT (no fallback) 

**Version 1.4.0 (Sun Wu Kong, 2018-08-13)**


+ Architecture
    + Chunked result inserts for Dump
    + More support for PHP 7.4

+ Report
    + Ambassador : added new Appinfo for relaxed Heredoc, trailing comma...

+ Analysis
    + New analysis : class can be abstract
    + New analysis : trailing comma
    + New analysis : relaxed heredoc
    + New analysis : removed functions in PHP 7.3
    + New analysis : continue versus break
    + Upgraded analysis : Hardcoded passwords is extended to objects
    + Checked unit tests : 2964 / 2979 test pass (99% pass). 

+ Tokenizer
    + Measure definitions stats for classes. 
    + Added support for relaxed heredoc
    + Added support for closure as a return value
    + Refactored support for Ternary and Labels

**Version 1.3.9 (Du Ruhui, 2018-08-06)**


+ Architecture
    + Added support for PHP 7.4
    + 'Copy' won't update anymore

+ Report
    + Ambassador : fixed repeated 'compatibility' menu entry

+ Analysis
    + New analysis : avoid __CLASS__ and get_called_class().
    + New analysis : prepare for (real) deprecation 
    + New analysis : const / define preference
    + New analysis : define case sensitivity preference
    + New analysis : avoid defining assert() in namespaces
    + Removed analysis : Variables/Arguments
    + Checked unit tests : 2957 / 2971 test pass (99% pass). 

+ Tokenizer
    + Removed Noscream - AT atom
    + Added definition for class constants
    + Fixed bug : can't apply ~ to false
    + Extended DEFINITION support to closure's use and references

**Version 1.3.8 (Fang Xuanling, 2018-07-30)**


+ Architecture
    + 'Copy' won't update code anymore.

+ Analysis
    + Upgraded analysis : 'should use operator' only applies to constant chr() call
    + Upgraded analysis : Useless Instructions is faster
    + Checked unit tests : 2948 / 2962 test pass (99% pass). 

+ Tokenizer
    + Added support for variable definitions in methods

**Version 1.3.7 (unnamed demon, 2018-07-16)**


+ Architecture
    + Fixed handling of multiple updates

+ Report
    + More documentations

+ Analysis
    + New analysis : report usage of callback to process array
    + New analysis : report usage of case insensitive constants
    + Upgraded analysis : Hardcoded passwords is extended to objects
    + Upgraded analysis : Go To Key Directly handles comparisons
    + Added support for PHP 7.0.20
    + Checked unit tests : 2948 / 2962 test pass (99% pass). 

**Version 1.3.6 (Zhang Gongjin, 2018-07-16)**


+ Architecture
    + Added support for Rar archives
    + Removed call to gremlin server at 'status' time

+ Analysis
    + New analysis : support for msgpack extension
    + New analysis : support for lzf extension
    + Upgraded analysis : added missing function names in several extensions
    + Checked unit tests : 2941 / 2955 test pass (99% pass). 

**Version 1.3.5 (Gao Shilian, 2018-07-09)**


+ Architecture
    + Removed 4 unused exceptions
    + Extracted Query from Analysis

+ Report
    + Reports : centralized all doc reading
    + Reports : doc reading now parses sections (avoid overlap)
    + Ambassador : Added exakat version and build to dashboard.
    + Ambassador : Added Class Tree (All class hierarchies)

+ Analysis
    + Fixed bug with 'last' and '2last'
    + New analysis : Report undefined::class
    + New analysis : Report returned assignations as useless
    + New analysis : Split scalar typehint by versions
    + Upgraded analysis : Extended Reuse Variable to instantiations
    + Upgraded analysis : Masking parenthesis are only for referenced arguments
    + Upgraded analysis : Wrong case doesn't apply to parent/static/self
    + Upgraded analysis : Locally Unused Properties are extended to traits
    + Upgraded analysis : Should Preprocess is extended to concatenations
    + Upgraded analysis : Array_key_fill exclude variables by default
    + Upgraded analysis : Ambiguous static reports the whole property definition
    + Checked unit tests : 2919 / 2944 test pass (99% pass). 

+ Tokenizer
    + Added missing constants
    + Fixed support for goto true;
    + Fixed edge case for nested ternaries and boolean
    + Moved Goto and Label to Name Atom

**Version 1.3.4 (Cheng Yaojin, 2018-07-02)**


+ Architecture
    + Added check when unarchiving tar.gz and tar.bz
    + Added check for neo4j installation, (error grabing)
    + Moved Upgrade to tmp folder

+ Analysis
    + Parameters are actually defined in the class
    + New analysis : ambiguous visibilities of properties
    + New analysis : report usage of PHP 7.1+ hash algorithm
    + New analysis : csprng (random_bytes and random_int)
    + New analysis : ext/libeio
    + New analysis : report incompatible signatures for methods
    + Upgraded analysis : Unused Private Methods handles fluent interfaces
    + Upgraded analysis : Defined Parent keyword
    + Upgraded analysis : Recursion
    + Refactored codeIs/codeIsNot
    + Checked unit tests : 2908 / 2923 test pass (99% pass). 

+ Tokenizer
    + Added support for 'parent' definitions
    + Fixed element counts in concatenation 
    + Fixed operator priority in Strval
    + Upgraded handling of undefined constants to string

**Version 1.3.3 (Ma Sanbao, 2018-06-25)**


+ Architecture
    + Better handling of fallback to global for functions
    + Weekly code clean
    + Refactored several analysis for speed

+ Report
    + Ambassador : fixed regression in the dashboard
    + Fixed edge case with properties

+ Analysis
    + New analysis : closure that can be static
    + Upgraded analysis : empty function doesn't count static or global
    + Upgraded analysis : reported globals include $GLOBALS also
    + Checked unit tests : 2881 / 2911 test pass (98% pass). 

+ Tokenizer
    + Moved collection of functioncall to LoadFinal
    + Added collection of interfaces and newcall
    + Moved Declare to its own token
    + Moved Property definitions to its own token

Version 1.3.2 (Duan Zhixian, coming up)

+ Architecture
    + Reading stats from store, not graph.
    + Git now fails silently if login is requested at clone / pull

+ Report
    + New analysis : == or === favorites
    + New analysis : > or < favorites
    + Upgraded analysis : written only variables is now faster
    + Upgraded analysis : PHP reserved words has now 2 parameters
    + Removed analysis : Type/Integer, Real, Closures.
    + Checked unit tests : 2901 / 2914 test pass (99% pass). 

+ Tokenizer
    + Static, PPP, Final and Abstract are now properties
    + Fixed regex in several rules
    + Added support for code clone detection (WIP)

**Version 1.3.1 (Liu Hongji, 2018-06-03)**


+ Architecture
    + Cleaned code of unused classes and ;
    + Fixed connexion script to the database
    + Fixed check of php.log folder

+ Report
    + Ambassador : display correct compilation state

+ Analysis
    + Upgraded analysis : used constant is also applied to defined()
    + Upgraded analysis : used protected methods is case insensitive
    + Upgraded analysis : Empty class omits extended classes
    + Upgraded analysis : More sequences to SimplePreg
    + Upgraded analysis : Throwable is not 'unthrown' anymore
    + Removed analysis : Static CPM
    + Checked unit tests : 2901 / 2914 test pass (99% pass). 

+ Tokenizer
    + Upgraded support for ::class

**Version 1.3.0 (Xue Rengui, 2018-06-03)**


+ Architecture
    + Added support for Tinkergraph 3.3.3
    + Handles situations where exakat has no database
    + Check for PHP version at bootstrap

+ Report
    + Ambassador : Updated PHP recommendation report with PHP 7.3
    + All : Variables don't sport ... nor & anymore

+ Analysis
    + New analysis : Single Use Variable
    + New analysis : Should Use Operator
    + New analysis : Check JSON production
    + New analysis : Report visibility usage with constants
    + Upgraded analysis : used constant is also applied to defined()
    + Upgraded analysis : used protected methods is case insensitive
    + Upgraded analysis : used directives handle function version
    + Upgraded analysis : added lcg_value for better rand
    + Upgraded analysis : Use Nullable extended to methods, closures.
    + Upgraded analysis : Fixed support for '_' native function
    + Checked unit tests : 2895 / 2907 test pass (99% pass). 

**Version 1.2.9 (Wang Gui, 2018-05-28)**


+ Architecture
    + Removed query cache from gremlin
    + Added pre-query check to prevent queries that have no chance of result

+ Report
    + Ambassador : first 50% of documentation fix : double quotes are not well displayed
    + Ambassador : Results are ordered by files, then by lines

+ Analysis
    + New analysis : Flexible Heredoc syntax
    + New analysis : Non-compatible methods
    + New analysis : Use the Blind Var
    + New analysis : Inexistant Compact
    + New analysis : Typehint / default value mismatch
    + Upgraded analysis : strict_types are not recognized as undefined constant
    + Upgraded analysis : More new methods for PHP 7.3
    + Upgraded analysis : Dependant traits
    + Upgraded analysis : Strpos comparison
    + Upgraded analysis : Method Must Return
    + Checked unit tests : 2885 / 2889 test pass (99% pass). 

+ Tokenizer
    + Interface may have const, not traits (Loading)
    + Added support for static call to methods

**Version 1.2.8 (Xu Jingzong, 2018-05-21)**


+ Architecture
    + Implemented a cache for speed boost.
    + Refactored files finding method
    + Git VCS always submit a user when cloning (using exakat by default)
    + Moved custom themes from themas.ini to themes.ini

+ Report
    + Ambassador : fixed naming the audit
    + Ambassador : added 'Dead code' section
    + Doctor : split themes display (default/customs)

+ Analysis
    + New analysis : Report what should be done in SQL
    + New analysis : Typehinted reference
    + New analysis : Strpos doing too much work
    + New analysis : Can't instantiate class
    + Upgraded analysis : Don't echo error
    + Upgraded analysis : PPP Declaration style
    + Upgraded analysis : Useless abstract class
    + Upgraded analysis : Buried assignation doesn't report declare anymore
    + Upgraded analysis : Abstract methods are not reported as unused
    + Upgraded analysis : relaxed version constraint for all Extensions/*
    + Checked unit tests : 2852 / 2856 test pass (99% pass). 

+ Tokenizer
    + Fixed handling of short_open_tags
    + Fixed edge case with % 

**Version 1.2.7 (Li Yuanji, 2018-05-14)**


+ Architecture
    + Extended status command to all VCS
    + Added support for customized themes
    + Added Upgrading section, List of parametrized analysis, revamped summary
    + Simplified handling of commandline options
    + Removed usage of JSON for 'doctor'

+ Report
    + A lot more documentation, examples, links.
    + Optimized type downloader
    + Added report themes pre-requisites

+ Analysis
    + New analysis : ext/cmark
    + Upgraded analysis : too many children is configurable
    + Upgraded analysis : error_reporting 0 and -1 are not reported as issues.
    + Checked unit tests : 2835 / 2839 test pass (99% pass). 

+ Tokenizer
    + Fixed bug where constant self referenced.
    + Moved Identifiers to Names
    + Added first definitions for members. 

**Version 1.2.6 (Li Jiancheng, 2018-05-07)**


+ Architecture
    + Moved more classes to helpers
    + Removed constants for Tokens
    + Upgraded to Robo 1.2.3

+ Report
    + Added support for custom themas for reports.

+ Analysis
    + New analysis : zookeeper
    + New analysis : Report missing parenthesis
    + New analysis : Report invalid interval checks
    + New analysis : Suggest array_unique when possible
    + New analysis : Report when callback needs a return
    + New analysis : Reduce the number of if
    + Updated Exception list, up to PHP 7.3
    + Upgraded analysis : Printf Arguments
    + Upgraded analysis : Count On Null
    + Upgraded analysis : Regex on Collector
    + Upgraded analysis : File Inclusion wrong case handles parenthesis
    + Upgraded analysis : Make globals a property
    + Upgraded analysis : Invalid regex
    + Checked unit tests : 2814 / 2818 test pass (99% pass). 

+ Tokenizer
    + Added definition links for staticmethodcalls.
    + Added boolean and int values to __DIR__ and co.
    + Removed several static properties
    + Fixed precedence of instanceof
    + Added support for Null val

**Version 1.2.5 (Li Yuan, 2018-04-30)**


+ Architecture
    + Added command 'config' to configure project from commandline
    + Made Exakat reentrant
    + Moved Configuration creation to external file
    + Upgraded status when audit isn't run yet

+ Analysis
    + New analysis : Regex on Collector
    + Upgraded analysis : Only Variable with reference argument
    + Upgraded analysis : File Inclusion Wrong Case
    + Upgraded analysis : Invalid Regex
    + Added support for PHP 7.2.5, 7.1.17 and 7.0.30
    + Checked unit tests : 2802 / 2809 test pass (99% pass). 

+ Tokenizer
    + Fixed various bugs with constant scalar expression

**Version 1.2.4 (Li Chunfeng, 2018-04-23)**


+ Architecture
    + Now fail with explicit message for memory running out

+ Report
    + Ambassador : Updated 'confusing variables' report

+ Analysis
    + Upgraded analysis : Could be short assignment
    + Upgraded analysis : Could be static
    + Upgraded analysis : Fail Substr Comparison (handles constants)
    + Checked unit tests : 2796 / 2801 test pass (99% pass). 

+ Tokenizer
    + Added propagation of constants when value can be processed
    + Introduced 'Parameter' token, to differentiate with Variable
    + Fixed syntax highlighting
    + Fixed a bug with negative bitshift

**Version 1.2.3 (Yuan Tiangang, 2018-04-16)**


+ Architecture
    + New append for logs

+ Report
    + New report : Manual.
    + Ambassador : Rewrote the export of 'confusing variables'

+ Analysis
    + New analysis : report strtr bad usage
    + New analysis : don't unset properties
    + Upgraded analysis : Invalid Regex
    + Upgraded analysis : Property Could Be Local 
    + Upgraded analysis : No Hardcoded path
    + Upgraded analysis : echo/print preferences also report printf
    + Removed analysis : Close Naming (now done at Report level)
    + Checked unit tests : 2770 / 2786 test pass (99% pass). 

+ Tokenizer
    + Removed double definition for functioncalls

**Version 1.2.2 (Yin Kaishan, 2018-04-09)**


+ Architecture
    + Cleaned doctor so it works even without requirements
    + Fixed special chars with git URL

+ Report
    + Ambassador : new inventory with classes changes in heritage
    + Ambassador : new inventory of large expressions
    + Upgraded report : Defined Exceptions are cleaned of doubles

+ Analysis
    + New analysis : report Redefined Private Properties
    + New analysis : report substr() usage with strlen
    + Upgraded analysis for Inclusion Wrong Case filenames
    + Upgraded analysis : Cast To Boolean is extended to True/False
    + Upgraded analysis : Omit negative lengths
    + Upgraded analysis : interface search also include parameter counts
    + Upgraded analysis : Failed Substr Comparison handles special chars
    + Upgraded analysis : Identical consecutive omits arrays
    + Checked unit tests : 2757 / 2775 test pass (99% pass). 

**Version 1.2.1 (Fu Yi, 2018-04-02)**


+ Architecture
    + Fixed generation of analysis logs
    + Fixed doctor, which wouldn't diagnostic the absence of needed extensions

+ Report
    + More real-life examples in docs

+ Analysis
    + New favorites : property declaration unique or multiples ? 
    + New analysis : $a = +$b;
    + New analysis for Melis : Regex check and Route constraints
    + Upgraded analysis : Constant used below
    + Checked unit tests : 2760 / 2766 test pass (99% pass). 

+ Tokenizer
    + Fixed counts in property declarations
    + Fixed final new lines in heredoc/nowdoc

**Version 1.2.0 (Xiao Yu, 2018-03-26)**


+ Architecture
    + Upgraded concurrency with analysis
    + Replaced $_SERVER['_'] by PHP_BINARY
    + Removed old code (> 1.0.0)
    + Adopted 'stable' version for progressbar
    + Fixed loading with Bazaar
    + Added support for Parametrized analysis
    + Better initial configuration with doctor

+ Report
    + Ambassador : upgraded analysis settings table

+ Analysis
    + New analysis : Report Private functions for Wordpress
    + New analysis : Suggest simplifying chr(123);
    + New analysis : Too many native calls
    + Updated analysis : fallthrough are not reported with die
    + New Theme : Random
    + Collecting more stats for classes.
    + Checked unit tests : 2758 / 2741 test pass (99% pass). 

+ Tokenizer
    + Upgraded support for Heredoc

**Version 1.1.9 (Qin Qiong, 2018-03-19)**


+ Architecture
    + Better documentation for reports
    + Adding Real Code examples to documentation
    + Refactored Config reading
    + Moved more VCS information to its own class

+ Report
    + Upgraded report : Ambassador reports the number of parameters in methods
    + New report : favorites (spin-off from Ambassador)
    + Upgraded report : Inventories also covers Dateformat, Regex, Sql, Url, Email, Unicode Blocks.

+ Analysis
    + New analysis : too many parameters
    + New analysis : report mass creation of arrays
    + Checked unit tests : 2755 / 2738 test pass (99% pass). 

**Version 1.1.8 (Yuchi Gong, 2018-03-12)**


+ Architecture
    + Reduced cache when running analysis
    + Fixed order of analysis 

+ Report
    + Ambassador : fixed faceted search problems
    + Codacy : added codacy-style report

+ Analysis
    + New analysis : support for IBM db2, leveldb
    + New analysis : should use count's second argument
    + Upgraded analysis : Randomly sorted arrays
    + Checked unit tests : 2749 / 2731 test pass (99% pass). 

+ Tokenizer
    + Fixed edge case where die is an argument
    + Fixed edge case where Yield returns a array

**Version 1.1.7 (Xu Maogong, 2018-03-05)**


+ Architecture
    + Removed most static in Analysis

+ Report
    + New format : All, that produces all reports
    + Ambassador : new report estimates fitting PHP version
    + Ambassador : report enable_dl in configuration

+ Analysis
    + New analysis : report dynamic library loading
    + New analysis : suggest array_fill_keys()
    + New analysis : PHP 7.3 optional last argument
    + New analysis : added support for xxtea, opencensus, varnish, uopz
    + Upgraded BugFixes report to PHP 7.2.3
    + Updated analysis : ext/cairo has new functions
    + Updated analysis : PHP 7.3 new functions
    + Removed analysis : NullCoalesce (double)
    + Checked unit tests : 2743 / 2731 test pass (99% pass). 

+ Tokenizer
    + Moved 'constant' to plugins
    + Fixed bug when updating with HG

**Version 1.1.6 (Wei Zheng, 2018-02-26)**


+ Architecture
    + Created 'First', a recipe of initial analysis
    + Prepared installation for compose

+ Report
    + Restored 'INLINE' results 
    + New reports : Stats
    + Collect PHP native function cool

+ Analysis
    + New analysis : report suggest compact instead of array
    + New analysis : list with references (PHP 7.3+)
    + New analysis : report situation where check is done on non-cast value
    + New analysis : foreach( $array as $o -> $v) as error prone
    + Handle cases where PHP regex are not compilable anyway
    + Checked unit tests : 2732 / 2722 test pass (99% pass). 

+ Tokenizer
    + Propagate constant concatenation values.
    + Fixed calculation of intval
    + Refactored Configuration readers
    + Fixed bug when calculating __METHOD__

**Version 1.1.5 (Li Shimin, 2018-02-19)**


+ Architecture
    + Refactored all reports
    + Removed outdated Devoops report

+ Report
    + Upgraded BugFixes report to PHP 7.2.2
    + Ambassador : generates a list of confusing variables
    + New report : OWASP

+ Analysis
    + New analysis : Use Math
    + New analysis : Extensions ext/hrtime
    + New analysis : Possible Infinite Loops
    + Upgraded analysis : addZero, Multiply by one supports new situations
    + Upgraded analysis : added microtime, uniqid .. to better rand.
    + Checked unit tests : 2719 / 2724 test pass (99% pass). 

+ Tokenizer
    + Fixed check on script compilation that was too strict.
    + Fixed internal assert() 
    + Exported VCS to separate classes
    + Refactored load with 3 separate plugins : intval, noDelimiter, booval

**Version 1.1.4 (The Great White Turle, 2018-02-12)**


+ Architecture
    + Build concatenation values in scalar constante expression.
    + Upgraded export of file dependencies values

+ Report
    + Ambassador : fixed duration of audit.
    + Composer : provides a full list of depend extensions

+ Analysis
    + New analysis : Report useless catch
    + New analysis : suggest using array_search / array_keys instead of foreach
    + New analysis : double array_flip is slow
    + New analysis : Suggest using cached values
    + New analysis : Functions that fallback to global namespace
    + Upgraded analysis : Encoded letters supports leading 0 in unicode codepoint
    + Upgraded analysis : Variable strange names now report 3 identical consecutive letters
    + Upgraded analysis : Upgraded support to __dir__
    + Checked unit tests : 2716 / 2711 test pass (99% pass). 

+ Tokenizer
    + Fixed definitions link for functions

**Version 1.1.3 (The fairy Su'e, 2018-02-05)**


+ Report
    + Fixed Ambassador : the favorites weren't displayed. 

+ Analysis
    + New analysis : Report useless references
    + New analysis : Melis configuration : Undefined configuration array
    + New analysis : Melis configuration : make string.
    + Upgraded analysis : Parent first
    + Checked unit tests : 2700 / 2695 test pass (99% pass). 

+ Tokenizer
    + Better handling of Labels. 
    + Fixed edge case where class and constants where mistaken one for the other

**Version 1.1.2 (Jade Rabbit Spirit, 2018-01-29)**


+ Architecture
    + Upgraded docs to tinkergraph 3.2.7

+ Analysis
    + New analysis : Report missing included files
    + New analysis : ZF3 : No Echo Outside a View.
    + New analysis : Local Global variable : report variable that looks global but are not
    + Upgraded analysis : Directive names are check with case sensitive analysis
    + Checked unit tests : 2687 / 2693 test pass (99% pass). 

+ Tokenizer
    + Magic Constant hold their actual value
    + Fixed Fullnspath for constants (case sensitive)
    + Fixed edge case with exit and die
    + Fixed edge case with exit and die and -1

**Version 1.1.1 (Wood Xie of Dipper, 2018-01-22)**


+ Architecture
    + Fixed path when calling exakat from outside its install folder
    + First analysis for Melis Framework
    + Optimized dictionary collection

+ Report
    + Ambassador : upgraded graph for class sizes

+ Analysis
    + New analysis : report case problems with includes
    + New analysis : Melis framework
    + New analysis : inventory of view properties for Zend Framework
    + New analysis : report view files for Zend Framework
    + Upgraded analysis : + is accepted as regex delimiter
    + Upgraded analysis : same condition searches inside blocks
    + Checked unit tests : 2665 / 2671 test pass (99% pass). 

+ Tokenizer
    + Magic constants __DIR__ and __FILE__ get their actual value in noDelimiter
    + Created Eval atom
    + Removed 'Name' token for echo, print, die, exit.
    + Upgraded handling of constant names inside strings
    + Removed a bug when storing dictionary.

**Version 1.1.0 (Wood Dragon of Horn, 2018-01-15)**


+ Architecture
    + Replaced 'code' property with a dictionary

+ Tokenizer
    + Introduced 'Magicmethod' for Magic methods in class
    + Fixed a bug when ' is in file path
    + Fixed a bug when several raw HTML are in a PHP script.
 
Version 1.0.11 (Wood Dragon of Well, 2018-01-08)

+ Architecture
    + Added assertion for property name.

+ Report
    + Ambassador : Added report of classes's size.
    + Fixed missing audit end's time. 

+ Analysis
    + New analysis : Sqlite3 doesn't escape " 
    + Upgraded analysis : Strange names also report qqqq sequences in variable names
    + Checked unit tests : 2617 / 2657 test pass (99% pass). 

+ Tokenizer
    + Fixed fullnspath handling for constants (case insensitive for the constant name)
 
Version 1.0.10 (Wood Wolf of Legs, 2018-01-01)

+ Architecture
    + Fixed Sqlite3 escaping error : use ', not "

+ Report
    + 

+ Analysis
    + Upgraded analysis : ? is possible as delimiter
    + Analysis works better with nested structures
    + Checked unit tests : 2601 / 2649 test pass (99% pass). 

+ Tokenizer
    + First plugin for Load Task.
    + Upgraded support for define-d constant.
    + Introduced Phpvariable
    + Fixed scoping with array index.
 
**Version 1.0.9 (King of Dust Protection, 2017-12-25)**


+ Report
    + Ambassador : list complex expressions.
    + Dump : added function inventory
    + Dump : added begin and end line for structures.

+ Analysis
    + New analysis : report reference error with Ternary operator
    + New analysis : report Undefined classes in Wordpress.
    + Upgraded analysis : preg option E, tighter regex.

+ Tokenizer
    + Better handling of long path name. TBC.
    + Introduced Parent, Static, Self, Exit, Echo, Print.
 
**Version 1.0.8 (King of Heat Protection, 2017-12-18)**


+ Architecture
    + Doctor reports memory_limit and JAVA_OPTIONS/JAVA_HOME
    + Made database restart more portable 
    + Added spell checking on docs

+ Report
    + Ambassador : Regex inventory added
    + Ambassador : Largest expressions reported

+ Analysis
    + New analysis : report identical operands on both sides of operator
    + New analysis : report potentially mistaken concatenation in array
    + New analysis : report mistaken scalar typehint
    + New analysis : report undefined classes by symfony version
    + New analysis : report undefined classes by wordpress version
    + Upgraded analysis : Interfaces are also reported from return typehint
    + Upgraded analysis : Mistaken concatenation got rid of various false-positives
    + Checked unit tests : 2601 / 2633 test pass (99% pass). 

+ Tokenizer
    + Isset, Empty, Phpvariables now have their own atom.
    + Fixed edge case with $ token
    + Fixed Constant fqn building
    + UTF-8 protection for propertyname

**Version 1.0.7 (King of Heat Protection, 2017-12-11)**


+ Architecture
    + Added /var to default omitted folders

+ Analysis
    + New analysis : should use array_filter.
    + New analysis : ext/igbinary
    + Checked unit tests : 2533 / 2599 test pass (97% pass). 

+ Tokenizer
    + Fixed 
 
**Version 1.0.6 (Fuli, 2017-12-04)**


+ Architecture
    + Refactored description
    + Moved PHPsyntax to a function

+ Analysis
    + New analysis : Never used parameter.
    + New analysis : always use named boolean parameters
    + Upgraded analysis : unused arguments
    + Checked unit tests : 2573 / 2585 test pass (99% pass). 

+ Tokenizer
    + Added new token : This for $this
    + Updated loader to handle PHP 7.3 functioncall syntax (final ,)
    + Turned Markcallable into an independant analysis

**Version 1.0.5 (King of Cold Protection, 2017-11-27)**


+ Architecture
    + Configured Exakat for Tinkergraph 3.3. Still unfinished.
    + Documentation now has an external link to extensions. 

+ Report
    + Ambassador : added more inventories : URL SQL, email, GET index, MD5, Mime

+ Analysis
    + New analysis : parent first
    + New analysis : Report uncommon Environment Vars
    + New analysis : Report invalid Regex
    + New analysis : Report contatenation in Zend DB
    + Fixed analysis : Deprecated Functions
    + Fixed analysis : Unknown PCRE2 option 
    + Upgraded analysis : hardcoded password
    + Upgraded analysis : array_merge in loops 
    + Upgraded analysis : substr() first. Handle following expressions
    + Refactored analysis : Used Functions
    + Refactored analysis : Add Zero
    + Checked unit tests : 2573 / 2585 test pass (99% pass). 

+ Tokenizer
    + Fixed a bug that linked functions and definitions

**Version 1.0.4 (Boxiang Demon, 2017-11-20)**


+ Architecture
    + PhpExec, get only path to binary.
    + Cleaned docs of double links
    + Cleaned code

+ Report
    + Added libsodium, Argon2 to Crypto; DL() usage to PHP.
    + Compatibility report only focuses on backward incompatibilities.
    + New recipes will cover 'suggestions for better code'. Coming up. 

+ Analysis
    + New analysis : " string is better than ' (sorry...)
    + New analysis : PHP 7.3's PCRE 2 
    + New analysis : report missing 'new' in front of class name.
    + New analysis : use is_object instead of is_resource for ext/hash
    + New analysis : report non-countable calls
    + New analysis : report DL usage in Appinfo
    + New analysis : slice first, then map arrays. 
    + New analysis : Avoid 5th argument in PHP 7.2 for set_error_handler
    + New analysis : avoid null with get_class()
    + New analysis : suggest using list() with foreach instead of arrays
    + New analysis : avoid using $this as argument in constructor
    + New analysis : Report usage of ext/vips
    + New inventory : GPC variables
    + Updated analysis : Use Class Operator doesn't report methods names anymore
    + Updated analysis : Long argument size is raised to 60 chars
    + Updated analysis : ignore when missing break is in last case
    + Updated analysis : Use This ignores 'self'.
    + Updated analysis : Randomly sorted Arrays ignores arrays of 3 or less.
    + Updated analysis : ext/mcrypt gets its constants
    + Updated analysis : more strange names being used in code
    + Updated analysis : more PHP 7.2 removed functions
    + Checked unit tests : 2563 / 2572 test pass (99% pass). 

+ Tokenizer
    + Reduced duplicated that may lead to loading error. 

**Version 1.0.3 (Baize Demon, 2017-11-13)**


+ Architecture
    + Fixed driver Tinkergraph, which was not setting the right ids.
    + Doctor now reports $JAVA_OPTIONS, in case one need to allocate more memory
    + Doctor now reports token limit
    + Moved config.ini creation to first phase of init.
    + Fixed collect of error when init with git.
    + Upgraded driver gremlin-php to 3.0.2

+ Report
    + Ambassador : Now reports the namespaces as a tree.
    + New analysis : report members that are static and not. 
    + Updated analyzis : normal method called statically.

+ Analysis
    + Added support for Drupal, FuelPHP and Phalcon.

**Version 1.0.2 (Suanni Demon, 2017-11-06)**


+ Architecture
    + Better report of error messages from VCS.
    + Updated support for Vagrant 

+ Report
    + Ambassador : Fixed display for 'Callback'

+ Analysis
    + New analysis : substr() first, then replace.
    + New analysis : report double prepare (WP).
    + New analysis : avoir the +1 month trap
    + New analysis : check for printf() options
    + New analysis : check for placeholder in prepare (WP)
    + New analysis : avoid direct injection into prepare (WP)
    + New analysis : performance recommendation for switch. 
    + New analysis : merge if/if into if/then/else
    + Checked unit tests : 2500 / 2536 test pass (99% pass). 

**Version 1.0.1 (Xueshi Demon, 2017-10-30)**


+ Architecture
    + Created Result class for Graphdb results
    + Docker image is updated with version 1.0.1
    + Vagrant files are updated with version 1.0.1
    + Preparing support for Gremlin 3.3.0

+ Report
    + Added support for PHP 7.1.11 and 7.0.25

+ Analysis
    + New analysis : could be else (for consecutive opposite if/then)
    + Checked unit tests : 2517 / 2527 test pass (99% pass). 

**Version 1.0.0 (Roushi Demon, 2017-10-23)**


+ Architecture
    + Tested on Gremlin 3.2.6. Checked Gremlin 3.3.0, but it needs more work.
    + Upgraded doctor for installation and report.
    + Upgraded docs to set gremlin-server as default install.

+ Report
    + Added support for Clang-style report.
    + Ambassador : fixed link to exception Tree.
    + Inventories : Date format, 
    + Audit names are reported in every Ambassador-style report. 

+ Analysis
    + Upgraded PHP directive list.
    + Functions In For loop : prevent issue if the function uses a loop variable.
    + Useless instruction : do not report return $i++ if $i is reference
    + Useless instruction : Avoir reporting properties when they are magic
    + New analysis : mark properties to be magic.
    + Upgraded list of PHP logins, to report hard coded passwords.
    + Upgraded close naming : variables that differ with 1 chars are reported.
    + Added assert(false...) to list of branching syntax.
    + Checked unit tests : 2515 / 2525 test pass (99% pass). 

Version 0.12.16 (Tawny Lion Demon, 2017-10-16)

+ Report
    + Beta version for Drill Instructor
    + Upgraded Inventories report with Sessions, Cookies, Incoming variables

+ Analysis
    + New analysis : Expression too complex.
    + New analysis : Session Handler must implements SessionUpdateTimestampHandlerInterface
    + New analysis : is Zero : additions that negate some terms
    + New analysis : unconditional loops
    + Upgraded Zend Framework review with latest versions (feed, http, eventmanager...)
    + Upgraded 'Strange names' with new typos
    + Upgraded 'Logical to in_array' to handle separated comparisons
    + Checked unit tests : 2505 / 2515 test pass (99% pass). 

+ Tokenizer
    + Fixed bug with Sign in Additions.

Version 0.12.15 (Nine Headed Lion, 2017-10-09)

+ Architecture
    + Server : now supports stop, start and restart.
    + Every audit gets a random name, for easy differentiation
    + Added support for PHP 7.3

+ Report
    + Ambassador : list of analysis that report nothing : Good job! 
    + Slim report : fixed build

+ Analysis
    + New analysis : file upload names vulnerability check
    + New analysis : variable that may hold different types of date
    + New analysis : always anchor regex
    + Checked unit tests : 2475 / 2480 test pass (99% pass). 

Version 0.12.14 (Grand Saint of Nine Spirits, 2017-10-02)

+ Architecture
    + Support UTF-8 on Gremlin Server (other encoding are not)
    + Better display of vcs updates

+ Report
    + Ambassador : added Security and Performances
    + Ambassador : Upgraded exception presentation

+ Analysis
    + New analysis : report fallthrough in switch
    + New analysis : inventory regex 
    + Added support for PHP 7.1.10 and 7.0.24

Version 0.12.13 (King of the Southern Hill, 2017-09-25)

+ Architecture
    + Code cleaning

+ Report
    + Ambassador : changed display of the audit

+ Analysis
    + Refactored several analysis

Version 0.12.12 (Ruler of the Kingdom of Miefa, 2017-09-18)

+ Report
    + Ambassador : fixed collect of interfaces and trait names

+ Analysis
    + New analysis : ext/Parle
    + New analysis : help optimize pathinfo() usage
    + New analysis : catch array_values() usage with list and pathinfo()
    + Updated analysis : Don't show error messages with catch->getMessage();
    + Updated analysis : No concat in loop handles $x = $c . $x;
    + Checked unit tests : 2456 / 2461 test pass (99% pass). 

+ Tokenizer
    + Added support for ', " and > in file names. Still missing support for \
    + Restaured fallback to global constants.
    + Fixed special case : <?php ++$x ?>

Version 0.12.11 (Half-Guanyin, 2017-09-11)

+ Architecture
    + Added support options for branches and tags
    + Added support for config in server mode

+ Report
    + Fixed methods dump for interfaces.

+ Analysis
    + Added all analysis to report could be private/protected for 

+ Tokenizer
    + Fixed handling of '<' char in paths

Version 0.12.10 (Golden Nosed Albino Rat Spirit, 2017-09-04)

+ Architecture
    + Upgraded server version with config alteration features.
    + New generated config-cache

+ Report
    + Fixed property names in Visibility report

+ Analysis
    + Arrays/IsModified : arrays are not modified unless in a (unset)

+ Tokenizer
    + Fixed 'constant' for functioncalls
    + Introduced 'Name' for Identifier without a fullnspath
    + Added support for branches and tags in init
    + Fixed edge case with $o->$$b

Version 0.12.9 (Lady Earth Flow, 2017-08-28)

+ Architecture
    + Creates config.cache, with cached calculated configs. Remove to update.

+ Report
    + GraphQL : Upgraded GraphQL report, with relationships.

+ Analysis
    + New analysis : suggest moving for() to foreach()
    + New analysis : shell_exec/exec/`backtick` favorite
    + Update analysis : Abstract Static is for PHP 7.0-

+ Tokenizer
    + Removed Arguments and ARGUMENTS. 
    + Finished 'factory' from Config.
    + Better handling of long path names.

Version 0.12.8 (ruler of the Kingdom of Biqiu, 2017-08-21)

+ Analysis
    + New analysis : use foreach, not for()
    + New analysis : ext/fam, ext/rdkafka

+ Tokenizer
    + Fixed edge case where pathnames are too long on OSX.

Version 0.12.7 (Old Man of the South Pole, 2017-08-14)

+ Architecture
    + Fixed project_vcs when none is used.

+ Analysis
    + Better documentation for in_array replacements and array_unique()
    + Added support for PHP 7.1.8 and 7.0.22

Version 0.12.6 (White Faced Vixen Spirit, 2017-08-07)

+ Analysis
    + New analysis : no negative for strings before 7.1
    + New analysis : use in_array instead of ||
    + Updated analysis : preg_quote has no delimiter

+ Tokenizer
    + Fixed bug in handling real value for negative numbers

Version 0.12.5 (White Deer Spirit, 2017-07-31)

+ Architecture
    + Removed config singleton

+ Report
    + New report : simpletables (HTML)

+ Analysis
    + New analysis : report optional parameters
    + New analysis : report concat inside a loop
    + Updated analysis : Could Be Class Constant, when no visibility is provided.

Version 0.12.4 (peacock Mahamayuri, 2017-07-24)

+ Architecture
    + Optimized performances for large projects (over 2M tokens)
    + Support Neo4j as a driver for Tinkgerpop

+ Report
    + Now covering all PHP 7.2 features

+ Analysis
    + New analysis : Extension xattr
    + New analysis : report 'object' as a class name
    + New analysis : No Array for magic property
    + New analysis : suggest reducing code for isset
    + New favorite : and / &&
    + Updated analysis : fetch correct delimiter, even if escaped.
    + Extended coverage for several analysis
    + Removed several nested-subqueries (bad for performances)

+ Tokenizer
    + Tinkergraph/Neo4j : reworked loading data from disk.
    + Added protection for $ in filename

Version 0.12.3 (Golden Winged Great Peng, 2017-07-17)

+ Architecture
    + Prepared options for several back servers : Tinkergraph, Gremlin-Server/Neo4j, Janusgraph

+ Report
    + New report : Marmelab (GraphQL server)

+ Analysis
    + New analysis : Report when a property is used as object or scalar
    + New analysis : Mismatched Typehint 
    + New analysis : Mismatched Default values 
    + Upgraded analysis : 
    + Fixed a gremlin bug in noAtomInside

+ Tokenizer
    + Added support for trailing comma in group use (PHP 7.2)
    + Fixed building of constants' values

Version 0.12.2 (Samantabhadra, 2017-07-10)

+ Architecture
    + Added support for Tinkergraph as graph backend

+ Report
    + Ambassador : reports callback/closures, all 3 declares (ticks, encoding, strict_types)
    + Ambassador : reports strict_types as favorite
    + PlantUML : upgraded report

+ Analysis
    + New analysis : Mismatched ternary branches
    + New analysis : mkdir, by default, uses 777. 
    + New analysis : ext/lapack
    + Upgraded analysis : option E for preg_match, refined results
    + Checked unit tests : 2337 / 2366 test pass (99% pass). 

+ Tokenizer
    + Added support for Instanceof and GROUPUSE with Nsname

Version 0.12.1 (Yellow Toothed Elephant, 2017-07-03)

+ Architecture
    + Refactored structures extractions in dump

+ Report
    + New report : PlantUML
    + Ambassador : Appinfo now reports how popular is a feature

+ Analysis
    + New analysis : Const / Define() favorite for constants
    + New analysis : do not return in finally
    + Upgraded analysis : Add Zero was refactored

+ Tokenizer
    + Prepared list of tokens and relations

Version 0.12.0 (Manjusri, 2017-06-26)

+ Architecture
    + Added support for Janusgraph (Gremlin 3)
    + Refactored dump's data collection for speed.bb

+ Report
    + Added support for Wordpress and Joomla as Frameworks

+ Analysis
    + New analysis : Avoid Optional properties
    + New analysis : Multiple declarations of functions
    + New analysis : Non breakable spaces in names
    + New analysis : Favorite Heredoc delimiter
    + New analysis : ext/swoole

+ Tokenizer
    + Modified several nodes/links names, for compatibility purposes

Version 0.11.8 (Xiaozuanfeng, 2017-06-19)

+ Architecture
    + Starte working on JanusGraph to add to Neo4j/Gremlin3

+ Report
    + Ambassador : reports Strings encoding and Unicode-block (when available)
    + Ambassador : reports framework founds (first 6, more as we go).
    + Ambassador : reports how frequently an analysis yield results to compare with current situation

+ Analysis
    + New analysis : Classes where declaration order differs from : use, const, properties and methods.
    + New analysis : Could use interface (but implements is missing)
    + New analysis : Cant Inherit Abstract Method (PHP 7.2 upgrade)
    + New analysis : use session_start() options
    + Updated analysis : Dynamica method calls cover {} too
    + Checked unit tests : 2305 / 2305 test pass (100% pass). 

+ Tokenizer
    + Checked code on early PHP 7.2 version

Version 0.11.7 (Long Armed Ape Monkey, 2017-06-12)

+ Report
    + Ambassador : report detected patterns (2 firsts)
    + None report : for when dump is sufficient

+ Analysis
    + New analysis : could factor functioncalls
    + New analysis : PSR-* usage
    + New analysis : support for Judy and Gender extensions
    + Added thema for Compatibility PHP 7.3
    + Added thema for Dependency Injection 

+ Tokenizer
    + Fixed edge case where classes starting with 'namespace' where mistakenly processed
    + Removed Block from CIT 

Version 0.11.6 (Red Bottomed Horse Monkey, 2017-06-05)

+ Architecture
    + Removed singleton to Config. WIP

+ Report
    + Ambassador : reports usage of PSR 3,6,7,11,13,16.
    + UML : report now protects file names

+ Analysis
    + New analysis : Ext stats
    + New analysis : report mixed concatenation / interpolation strings
    + Updated analysis : htmlentities actually uses combinaison, not alternatives, 
    + Updated analysis : Close Tag consistency ignores __HALT_COMPILER files

Version 0.11.5 (Intelligent Stone Monkey, 2017-05-30)

+ Report
    + Ambassador : fixed visibility suggestion
    + New report : Dependency wheel

+ Analysis
    + New analysis : avoid typehinting with classes
    + New analysis : implemented methods must be public
    + New analysis : no reference on left of assignement
    + New analysis : Could typehint with instanceof
    + Updated analysis : Useless parenthesis cover clone, yield, yield from.
    + Updated analysis : Make One Call also reports nested calls

+ Tokenizer
    + Split functions and closures, 
    + Split classes and anonymous classes
    + Split variable with definitions (Property, Static and Global)
    + File count is always reported (even 0)
 
Version 0.11.4 (Six Eared Macaque, 2017-05-22)

+ Architecture
    + Results : returns now multiple results at once

+ Report
    + New report : codeflower
    + Ambassador : report usage of Debug functions, browscap
    + Ambassador : omits 0 in donuts
    + Ambassador : faceted search for compatiblity

+ Analysis
    + New analysis : report functions whose return is not used
    + New analysis : only variable can be passed by reference
    + Added limits to all in-depth searches
    + Checked unit tests : 2216 / 2216 test pass (100% pass). 

+ Tokenizer
    + Fixed edge case, where return is finished by a close tag
    + Split Variables into Variables, Objects and Arrays.

Version 0.11.3 (Sun Deity of Mao, 2017-05-15)

+ Architecture
    + Speed up batch processing for lists of analysis
    + Split data collection from the initial dump.

+ Report
    + Ambassador : Upgraded presentation of issues, and internals links.

+ Analysis
    + New analysis : Sphinx extension
    + New analysis : GRPC extension
    + New analysis : reports arrays that are randomly sorted.
    + New analysis : report multiple catch clauses
    + Updated analysis : direct injections include all SERVER_* values
    + Upgrade for PHP 7.1.15 and 7.0.19

+ Tokenizer
    + Split Functioncall into Functioncall, MethocallCall and Newcall. 
    + Added support for 'namespace' in any full name.

Version 0.11.2 (Scorpion Demon, 2017-05-08)

+ Architecture
    + Code cleaning, and more stability

+ Analysis
    + New analysis : Report preference between != and <>
    + New analysis : report empty regex and wrong delimiters
    + Added protection for $ in RegexDelimiters 

Version 0.11.1 (Ruler of Women's Country, 2017-05-01)

+ Architecture
    + Fixed handling for large list of data in gremlin queries
    + Handles static in anonymous classes correctly

+ Report
    + Reports handle traits like class.

+ Analysis
    + New analysis : ends arrays with , or not (favorite)
    + New analysis : suspicious comparison 
    + New analysis : strange spaces in strings

+ Tokenizer
    + Arrays are now Arrayliteral, split from Functioncall

Version 0.11.0 (Immortal Ruyi, 2017-04-24)

+ Architecture
    + Removed prepared statements from loops in dump
    + made Gremlin cache compatible with 32bits platforms

+ Report
    + Ambassador : first work on upgrading visibilities for properties.

+ Analysis
    + New analysis : could use str_repeat()
    + New analysis : Crc32() Might Be Negative
    + Update analysis : Queries in loop reports cubrid and sqlsrv, prepared statements.
    + Update analysis : type mismatch for indices works on constants too.
    + Update analysis : Loop calling covers less ground

+ Tokenizer
    + Split function and method entities for differentiated processing

Version 0.10.9 (Single Horned Rhinoceros King, 2017-04-17)

+ Architecture
    + File extensions are processed before include/ignore dirs.
    + Reduced number of DEFINITION links, leading to less processing.
    + Added several assertion() in the code
    + Added assertions report in doctor (better leave them out with phar)

+ Report
    + Added support for PHP 7.0.18 and 7.1.4
    + Ambassador : better layout for favorites
    + Zend Framework : 8 new components supported
    + Zend Framework : now supports zendframework/zendframework too
    + Zend Framework : report unused components 

+ Analysis
    + New analysis : report nested Use expressions
    + New analysis : report repeated regex (to be federated)
    + New analysis : report code that output directly to std
    + Updated analysis : Should use this now omits overwritten methods
    + New analysis : report overwritten methods
    + Upgraded analysis : 2123 / 2123 test pass (100% pass)

Version 0.10.8 (King of Spiritual Touch, 2017-04-10)

+ Report
    + Slim report : list of routes used. 

+ Analysis
    + New analysis : report Group Use Declaration (PHP 7.0+)
    + Zend Framework : 30 components are now covered. 
    + Slim : No echo in route callable and Inventory of routes.
    + PHP : list of new PHP 7.2 functions.

+ Tokenizer
    + Sped up loading time by 10%. 
    + Added support for PHP6 binary string : $a = u'b';

Version 0.10.7 (Immortal of Antelope Power, 2017-04-03)

+ Report
    + Ambassador : fixed composer report.
    + Added report for Composer (beta phase)
    + Added report for Slim framework.

+ Analysis
    + Added support for Slim versions.
    + Added 10 new components for Zend Framework 3

+ Tokenizer
    + Fixed support for $ in file names.

Version 0.10.6 (Immortal of Elk Power, 2017-03-27)

+ Architecture
    + Major speed up of loading and analysis
    + Fixed themes configuration.

+ Report
    + Ambassador : report cookies usage, infinite and NAN usage
    + Zend Framework : Report incompatibilites component/version for ZF3

+ Analysis
    + Upgraded analysis : 1941 / 1941 test pass (100.00% pass)
    + New analysis : Zend Framework 3 Deprecated 
    + New analysis : Zend cache, view, db.
    + New analysis : Report missing type tests.
    + New analysis : suggest setcookie() with safe arguments
    + New analysis : Do not cast to Int
    + New analysis : CakePHP classes compatibilities from 2.5 to 3.3
    + Upgraded analysis : instanceof doesn't report traits anymore
    + Upgraded analysis : mb_ereg has options in the 4th arguments
    + Upgraded analysis : more strange names 

+ Tokenizer
    + Reviewed most of the load processing.
    + Reduced the number of 'fullnspath' properties.

Version 0.10.5 (Immortal of Tiger Power, 2017-03-13)

+ Architecture
    + Collect graph size in dump.sqlite
    + Collect memory usage in dump.sqlite
    + Now uses the calling PHP version to run all parts of exakat (no config)
    + Doctor report the ran gremlin version. 

+ Report
    + Ported the Zend Framework report to ambassador
    + Added regex delimiter in favorites.
    + Ambassador : syntax coloring 

+ Analysis
    + New analysis : could be typehinted 'callable'
    + New analysis : encoded letters in strings for security
    + New analysis : report arguments that may be callable
    + New analysis : report strangely named variables
    + New analysis : report strangely named constants
    + New analysis : too many FindsBy*() methods
    + Updated analysis : Useless Instructions doesn't report array_merge(_recursive) with one argument
    + Updated analysis : array_replace handles ... 
    + Updated analysis : 7.2 deprecation with assert()
    + Generalized usage of commons for CIT
    + Added first 4 set of analysis for Zend Framework 3
    + Added support for dynamic new $a[i];

+ Tokenizer
    + Fixed fullnspath with new on functioncall
    + Reduced the number of fullnspath loaded
    + Added support for 's'() as functioncall
    + Fixed case where file names has ' ' in it
    
Version 0.10.4 (Dragon King of the West Sea, 2017-03-06)

+ Architecture
    + Ignore some classic files by default (README, LICENSE...)

+ Report
    + Ambassador : protection of HTML values
    + PHPcompilation : fixed export to stdout

+ Analysis
    + New analysis : report useless else branches
    + New analysis : should regenerate session Id, for PHP and Zend Framework
    + Added support for Extension Data structures (ext/ds)
    + Upgraded analysis : Hardcoded Hash
    + Speed up analysis for extensions

+ Tokenizer
    + Fixed edge case where a constant was used inside a ternary operator
    + Fixed processing of labels
    
Version 0.10.3 (Dragon King of the Jing River, 2017-02-27)

+ Architecture
    + Added URL glossary to Manual.
    + Extended CS ruleset
    + Use exakat/exakat as user/login for git. 
    + New helper to rename analysis
    + Project command now accept -P/-T to run one analysis/Thema directly

+ Report
    + New report style : Codesniffer

+ Analysis
    + New analysis : suggest usage for array_column()
    + New analysis : __DIR__ must be concatenated with a string starting with '/'
    + New analysis : report usage of parent, self and static outside a class/trait
    + New analysis : report properties used only in one method
    + New analysis : report properties used only once at all
    + New analysis : multiple aliases per class
    + Updated analysis : Fopen() mode support 'e' option (7.1.2 + )
    + Updated analysis : Make One Call covers str_replace, substr_replace, preg_replace*
    + Updated analysis : Unused arguments : now ignores arguments from interface or parent

+ Tokenizer
    + Removed double DEFINITION link. Faster loading, less processing.
    + Fixed an edge case when function name is boolean or null.
    + Cleaned atom and tokens names
    + Fixed edge case when object is instantiated in a ternary
    
Version 0.10.2 (Water Lizard Dragon, 2017-02-20)

+ Architecture
    + 

+ Report
    + Text format now understand -T, -P to extract only some of the results.
    + Fixed dump of extends. 

+ Analysis
    + Added support for PHP 7.1.2 and PHP 7.0.16
    + New analysis : report forgotten 'throw' keyword.
    + New analysis : report class / function confusing name
    + Added support for libsodium
    + Upgraded PHP Relaxed Keyword : Ignore properties.
    + Upgraded analysis : 1824 / 1826 test pass (99.9% pass)

+ Tokenizer
    + Fixed a bug that mistakes native PHP classes for functions
    + Fixed rare situation with grouped const/function.
    
Version 0.10.1 (King of Wuji Kingdom, 2017-02-13)

+ Architecture
    + Report SVN revision when updating or not.
    + Default reports are in config.
    + Configure now supports include_dirs, to include files.
    + Project name is now noted in datastore.
    + Inventories is a default themas; PHP Compatibility < 5.6 are not default anymore.

+ Documentation
    + Fixed outgoing links
    + Better coverage of PHP functions

+ Report
    + Added 'Inventories' report : reports all names and literals
    + Ambassador : Added list of included files, Yield From and classes stats

+ Analysis
    + New Analysis : Strange Names For Methods (Classes/StrangeName)
    + New Analysis : SQL queries (Type/Sql)
    + New Analysis : Avoid Non Wordpress Globals (Wordpress/AvoidOtherGlobals)
    + Upgraded analysis : Should be single quote, escape sequences refined.
    + Upgraded analysis : Should Preprocess now support determinist PHP functions
    + Upgraded analysis : 1817 / 1824 test pass (99.6% pass)

+ Tokenizer
    + Fixed LOC counting.
    + Fixed edge case when closure is directly use as argument
    + Fixed double inventories for Use's Definitions
    
Version 0.10.0 (Azure Lion, 2017-02-06)

+ Architecture
    + Replacement of booleans with constants (WIP)
    + Removed PHPloc (merged features into load)
    + Added coding standard for Code Sniffer (ruleset.xml)
    + PHP version used default to running script version
    + Now reading Token Constants from the binaries
    + Doctor reports project configuration if -p is used

+ Report
    + 

+ Analysis
    + New Analysis : No Boolean As Default 
    + New Analysis : Raised Access Level 
    + New Analysis : Recommend Wpdb->prepare when variables are in query
    + Directive suggestion now include error_log
    + Upgraded analysis : UselessParenthesis also checks Typehint
    + Upgraded analysis : 1804 / 1811 test pass (99.6% pass)

+ Tokenizer
    + Reinforced detection of parsable PHP script
    + Fixed Files command : it now cleans data before running
    + Removed warning about memory
    + Index creation made lighter

Version 0.9.9 (Pilanpo Bodhisattva, 2017/01/30)

+ Architecture
    + Moving true/false to constants

+ Report
    + Ambassador : Added 'Compilation' and Version compatibility reports.
    + Prepared collection of dependencies in dump

+ Analysis
    + New Thema : Compatibility PHP 7.2
    + New analysis : Deprecated Features of PHP 7.2
    + New analysis : Removed Function for PHP 7.2
    + New preference : New Line Style
    + Upgraded analysis : 1781 / 1802 test pass (98.9% pass)

**Version 0.9.8 (Multiple Eyed Creature, 2017-01-23)**


+ Architecture
    + Moved 'Truthy/Falsy' as 'boolean' characteristics
    + Updated Gremlin3 interface to handle Groovy maps
    + Added default name when creating project

+ Report
    + Added checks on merged table at Dump stage
    + Added support for PHP 7.1.1 and 7.0.15

+ Analysis
    + New analysis : variables assigned twice or more
    + New preference : new x() / new x;
    + Upgraded analysis : 1785 / 1794 test pass (99.5% pass)
    + Fixed Interface usage : missing interfaces extends interfaces
    + Added extra check for Functioncalls

+ Tokenizer
    + Added support for instanceof + several names

**Version 0.9.7 (Hundred Eyed Demon Lord, 2017-01-16)**


+ Architecture
    + Fixed constant names for tokens in Load
    + Changed duplication check to dedup(). Cleaned analysis for duplicates.
    + Speed but for large projects. Work in Progress. 
    + Reduced usage of static properties
    + Better detection of PHP scripts during project

+ Report
    + Fixed generation of inventories when no target is provided

+ Analysis
    + New analysis : Could Be Protected Property (not a public)
    + New analysis : avoid large literal arrays in local variables. 
    + New analysis : report long arguments. 
    + Removed analysis : Structures/EchoArguments (double with Echo With Concat)

+ Tokenizer
    + Fixed list of constants for PHP 7.1

**Version 0.9.6 (Spider Demons, 2017-01-09)**


+ Architecture
    + Added support for report/analysis theme list in config (exakat and project)
    + Better cleaning of projects
    + Doctor : Initialisation with themes/reports; Reports executable being used.
    + Added a log for gremlin Queries
    + Rebuild the server command
    + Added 'catalog' command

+ Report
    + Split Phpconfiguration into eponymous and Phpcompilation

+ Analysis
    + New analysis : avoid Glob, use scandir without sorting.
    + New analysis : always configure ext/sqlite3 FetchRow()
    + New analysis : no string with append
    + Removed analysis : Structures/ForeachSourcesNotVariable
    + Upgraded Analysis 'Should Import Functions'
    + Upgraded analysis : 1764 / 1773 test pass (99.5% pass). 

+ Tokenizer
    + Added 'aliased' property to nodes.

**Version 0.9.5 (Immortal Ziyang, 2017-01-04)**


+ Architecture
    + Better check of PHP version 

+ Report
    + Ambassador : report analysis settings
    + PHP Compilations : supports all extensions
    + New report : Inventories

+ Analysis
    + New analysis : Don't Use Fallback to Global space
    + New analysis : MongoDB (ext/mongo version 3)
    + New analysis : zbarcode 
    + Bug : Fixed intval for octals in Arrays/MultipleIdenticalKeys
    + Removed analysis : Php/InconsistantClosingTag (double)

+ Tokenizer
    + Ranking arguments, not functioncall

**Version 0.9.4 (Lady of Jinsheng Palace, 2016-12-19)**


+ Architecture
    + Rewrote the concurrence check (removed needs for ext/sem)
    + Results are never double anymore
    + Upgraded gremlin calls, to handle \n
    + Dump cleans the previous values before dumping
    + Excluded namespaces classes when searching for external libraries

+ Report
    + Ambassador : extension usage, inventories, global lists, stats, PHP Compilation directives
    + Covers more compilation directives (Not finished)

+ Analysis
    + New analysis : Final by Ocramius
    + Upgraded : Comparison with == : added curl_exec
    + Upgraded : isset with constant (mistake on properties as arrays)
    + Upgraded : Avoid using now uses full NS path
    + Upgraded : Useless instructions handles for() correctly
    + Upgraded : Recursive, IsGenerator and Loop Calling includes yield from
    + Upgraded analysis : 1741 / 1750 test pass (99.5% pass). 

**Version 0.9.3 (Purple-Gold Bells, 2016-12-12)**


+ Architecture
    + Lots of cleaned code
    + Harmonized data for extensions
    + Stop 'project' if no code is available
    + Now using stub in phar.

+ Report
    + Added directives, bugfixes, external services and 
    + Added support for PHP 7.0.14 and 5.6.29

+ Analysis
    + New analysis : Wordpress, recommend prepare()
    + More favorite reports : final ?> and unset()/(unset)
    + Reduced number of double reports for many analysis
    + Update : Fixed analysis with $THIS
    + Upgrade : report useless casting of comparisons
    + Update : Should use this takes into account parent

**Version 0.9.2 (Golden Haired Hou, 2016-12-05)**


+ Architecture
    + First version of Exakat for docker (beta)
    + Added a waiting loop in cleandb
    + Docs include a list of new analysis per version

+ Report
    + Added 2 first inventories, Appinfo() in Ambassador
    + Favorites now reports global/$GLOBALS
    + Restore composer.lock report
    + Upgraded uselessReturn for the final return. 

+ Analysis
    + New analysis for Newt, Nsapi, 
    + New analysis : __ in methods names
    + New analysis : Too many local variables
    + New analysis : Avoid array_push()
    + Upgraded ext/apache coverage

**Version 0.9.1 (Sai Tai Sui, 2016-11-28)**


+ Architecture
    + Docker supported in exakat/config.ini for PHP binaries. 
    + Added exakatSince in analysis documentation
    + Added some missing tokens in anonymize command

+ Report
    + Added several new analysis for PHP 7.1

+ Analysis
    + new analysis : find methods that could return Void
    + new analysis : find malformed octal sequence in strings
    + new analysis : spot rethrown exception
    + new analysis : reach the last element
    + new analysis : find undefined Zend Framework classes (2.0 to 3.0)
    + Upgraded analysis : 1706 / 1714 test pass (99.5% pass). 

+ Tokenizer
    + Fixed handling references (some were missing)
    + Fixed handling of ellipsis (some were missing)

**Version 0.9.0 (Python Demon, 2016-11-21)**


+ Architecture
    + Project now include 'Preference' analysis
    + Dump is now incremental (-u option), and doesn't need to be run in paralell
    + Added new hashAnalysis table, to handle generic results from analysis.
    + Added project name in the graph.
    + New command 'status' to report the current status of exakat

+ Report
    + Ambassador includes 'Preferences' section and new menu system
    + Upgraded progressbar to display project processing

+ Analysis
    + New analysis : Early Bail Out (with if/then)
    + New analysis : PHP 7.1 backward incompatibilities with microseconds
    + New analysis : Wordpress : recommend using WP api, not PHP.
    + Upgraded 'Constant condition' to include do..while()
    + Upgraded 'Useless Abstract' to include methodless classes
    + Upgraded analysis : 1687 / 1697 test pass (99% pass). 

+ Tokenizer
    + Added 'Array' to list of determinist functions (more constants are spotted)
    + Fixed 'Name' for Array Short Syntax.
    + Fixed variadic support

**Version 0.8.9 (Yellow Brows Great King, 2016-11-14)**


+ Architecture
    + Fixed and document -tgz and -zip option of init
    + Removed progress folder
    + Made MagicNumber a parallel task in Project.
    + Turned some die into assertion()
    + .phar doesn't report any PHP errors. 
    + Checked compilation with PHP 5.3->7.2

+ Report
    + Removed Faceted report
    + Added Bugfixes for PHP 7.0.13, 5.6.28 and PHP 7.2
    + Added 'One variable string' to Radwell report

+ Analysis
    + New analysis : Object Calisthenics #1, #4
    + New analysis : check that properties are all set at constructor time.
    + New analysis : spot useless checks
    + Updated UndefinedParentMP to take PHP ext classes into account
    + Upgraded 'array_merge in loops' with file_put_contents
    + Upgraded 'useless parenthesis' with math operations
    + Upgraded analysis : 1666 / 1682 test pass (99% pass). 
    + Added debug Query method to analysis

+ Tokenizer
    + Fixed Files to compile first, then count tokens
    + Find Ext Lib handle UT classes better
    + Added limit to 'code' before loading into database. There is a 2M limit.
    + Fixed edge case with nested foreach()
    + Fixed segmentation fault when getting tokens from a script with wrong encoding
    

**Version 0.8.8 (Apricot Immortal, 2016-11-07)**


+ Architecture
    + Added concurency test to avoid running several instance at the same time
    + Report error when it happens with git clone
    + Added UT classes to external libraries
    + Dump is now hidden until finished.
    + Better detection of java and composer (Thanks Julien)

+ Report
    + New report : Radwell
    + New report : PhpConfiguration helping with configure and php.ini
    + Ambassador : Fixed dashboard values

+ Analysis
    + New analysis : time() vs strtotime('now')
    + New analysis : useless casting
    + New analysis : No Isset() with Empty()
    + New analysis : don't echo errors
    + New analysis : ext/rar
    + New analysis : use Class::class when possible
    + Added array_key_exists() to slow functions list.
    + Upgraded UpperCaseKeywords to handle partial uppercase
    + Added reported directives for ext/filter
    + Upgraded 'Variables used once' to exclude $this and arguments
    + Upgraded Unreachable Code with break/continue;
    + Multiple Identical Keys now handles null, boolean, real.
    + Upgraded analysis : 1652 / 1668 test pass (99% pass). 

+ Tokenizer
    + Now spots \true, \false, \null as Boolean and Null
    + Removed 'xargs too many arguments' error on Linux

**Version 0.8.7 (Naked Demon, 2016-10-31)**


+ Architecture
    + Upgraded Boolean and Integer to report results without storing them in graph

+ Analysis
    + New analysis : modernizable empty() calls
    + New analysis : recommend Positive conditions
    + New analysis : drop else after return
    + Upgraded analysis : unreacheable code handles if/then with returns.
    + Added tests for Boolean and Null
    + More not Hashes dict.
    + Upgraded analysis : 1637 / 1650 test pass (99% pass). 

+ Tokenizer
    + Fixed line number of <?= 
    + Fixed token on arguments

**Version 0.8.6 (Fuyun Sou, 2016-10-24)**


+ Architecture
    + New command to ping a queue
    + More documentation

+ Report
    + Ambassador report sped up multiple times
    + Text, Json and XML all report only analysis (not the dependencies)

+ Analysis
    + New analysis : suggest ternary instead of Ifthen
    + New analysis : check for returned value usage
    + Added support for PHP 7.0.12 and 5.6.27
    + Added more bugs fixing from extensions
    + Fixed analysis for Zend Framework 1
    + Ignore $this in variable used once
    + Fixed report with unlimited arguments functions
    + Overwritten literals : Ignore assignations in for()
    + Upgraded old PHP 5.* analysis to Gremlin 3
    + Upgraded analysis : 1639 / 1645 test pass (99% pass). 

+ Tokenizer
    + Fixed precedence between require and .
    + Better fullcode for <?= 

**Version 0.8.5 (Naked Demon, 2016-10-17)**


+ Architecture
    + Moved all classes under Exakat folder for clean hierarchy

+ Report
    + Ambassador : restored line number in display

+ Analysis
    + New analysis, check for substr() comparisons with literals
    + New analysis, suggest boolean cast, instead of Ternary.
    + New analysis, spot 3 levels of if/then
    + Upgraded 'hardcoded password', for kadm5 and hash_* functions
    + Upgraded 'external libs', with Zend Framework
    + Upgraded analysis : 1625 / 1638 test pass (99% pass). 

**Version 0.8.4 (Lingkongzi, 2016-10-10)**


+ Architecture
    + Moved Tasks into Exkat\Tasks
    + Fixed findExternalLibs

+ Report
    + Ambassador report got good annex, fixed settings and faceted search
    + Omit clearPHP if not present in docs

+ Analysis
    + New analysis : detect multiple identical traits/interface in CIT
    + New analysis : suggest creating aliases to reduce code
    + New analysis : spot aliases that may be reused again
    + New analysis : hidden use, that are not at the beginning of the code
    + Upgraded analysis : 1607 / 1618 test pass (99% pass). 
    + More documentations to many analysis
    + HasMagicProperty report all magic methods 
    + Upgraded 'Useless Parenthesis' with more situations
    + Upgraded 'Unchecked resources' with 2 more situations
    + Fixed several analysis when using Boolean and Null as a class
    + Fixed analysisIsNot with arrays
    + Removed include-like from undefined functions
    + Arrays/AmbiguousKeys : Extended to arrays calls

+ Tokenizer
    + Fixed edge case with return ?>
    + Fixed path for reporting

**Version 0.8.3 (Guzhi Gong, 2016-10-03)**


+ Architecture
    + Created temp folder .exakat in projects_dir
    + Removed mentions of float, only using Real
    + Moved Config to Exakat\Config
    + More examples in docs

+ Report
    + Added settings and files to Ambassador

+ Analysis
    + New analysis for dependant Traits
    + Added new Theme 'Cakephp' with 6 analysis for migration
    + New values for Not-a-hash
    + Unresolved Catch now takes Throwable into account

+ Tokenizer
    + Fixed edge case where return is used inside if/then without {} nor value.
    + Fixed 'code' and 'token' for ?: and ()

**Version 0.8.2 (Jinjie Shiba Gong, 2016-09-26)**


+ Architecture
    + More examples in docs
    + Fixed 'file' in results

+ Report
    + Added more media for Ambassador

+ Analysis
    + New analysis for count/strlen compared to 0
    + Upgraded analysis : 1563 / 1579 test pass (99% pass). 
    + Backported all 4 Wordpress analysis (wpdb, nonce usage)
    + Added new Wordpress analysis : variable escaping in templates

+ Tokenizer
    + Fixed <?= so it is handled like echo

**Version 0.8.1 (Babo'erben, 2016-09-19)**


+ Architecture
    + Added main Try/Catch

+ Report
    + Added 'Ambassador' report. 

+ Analysis
    + Upgraded analysis : 1540 / 1561 test pass (99% pass). 
    + More documentation (examples, glossary) 
    + Added a list of stopwords for No Hardcoded Hash
    + Upgraded analysis 'No Hardcoded Path' with protocols and glob with wildcards
    + Upgraded analysis 'No Hardcoded Hash' with stopwords
    + Added new Analysis for portability : spot common Linux files
    + Added new Analysis : use system temp dir, not hardcoded one 
    + New analysis that spot unused protected methods
    + Added Time-to-fix and severity to all analysis

+ Tokenizer
    + Fixed edge case with if/then and try/catch 
    + Synchronized constants in Tokens/Consts*.php
    + Added support for PHP 7.2

**Version 0.8.0 (Benbo'erba, 2016-09-12)**


+ Architecture
    + More examples in the docs
    + Better find root in export

+ Report
    + Prepared code for new report style

+ Analysis
    + New analysis : no throw in __destruct
    + New analysis : spot empty blocks in control structures
    + Update : Check parse_str and mb_parse_str()
    + Upgraded analysis : 1524 / 1540 test pass (99% pass). 

+ Tokenizer
    + Fixed representation of [] and [index] with static properties

Version 0.7.10 (Nine Headed Bug, 2016-09-05)

+ Architecture
    + Added optional dependency to mbstring in Doctor
    + 

+ Analysis
    + Added analysis for PHP 7.1 features
    + Upgraded analysis : 1377 / 1510 test pass (91% pass). 

+ Tokenizer
    + Removed parasit 'void' added in sequences.
    + Raised export max depth to 15. 
    + Fixed FQN for new without parenthesis
    + Fixed support for PHP 5.5/5.6. 
    + Added support for iterable
    + Checked support for extensions and ignore dirs 

**Version 0.7.9 (Wansheng Princess, 2016-08-29)**


+ Architecture
    + Added several features at Loading time : mark global variables in $GLOBALS,
      fallback FQN in functions, link constant to definitions.

+ Analysis
    + Added analysis for impossible comparisons (count($a) < or >= 0)
    + Added analysis for PHP 7.1 : removed directives, added functions
    + Upgraded analysis : 1485 / 1522 test pass (97.5% pass). 

+ Tokenizer
    + Fixed edge case with <?= $v;
    + Fixed priorities between include and .
    + Better support of trait in classes

**Version 0.7.8 (Wansheng Dragon King, 2016-08-22)**


+ Architecture
    + Prepared databases for PHP 7.2

+ Analysis
    + Reports that preg_match results are not checked
    + Report List short syntax usage.
    + Upgraded analysis : 1224 / 1493 test pass. 

+ Tokenizer
    + 

**Version 0.7.7 (Water Repelling Golden Crystal Beast, 2016-08-17)**


+ Analysis
    + Upgraded Bug database to handle PHP 7.0.10, 5.6.24 and 5.5.38

**Version 0.7.5 (Jade Faced Princess, 2016-07-19)**


+ Architecture
    + Added 'anonymize' command, that anonymize files and projects

+ Analysis
    + new analysis : recommend preg_replace_callback_array() when there are several call to preg_replace_callback_array()
    + Upgraded analysis : 1103 / 1464 test pass. 

+ Tokenizer
    + Lots of fixes for stability : tested on 28M tokens 

**Version 0.7.4 (Great Sage Who Pacifies Heaven, 2016-07-12)**


+ Architecture
    + Entirely rewrote the 'Tokenizer' part
    + Upgraded database schema 

+ Analysis
    + Upgraded analysis : 1027 / 1461 test pass. 

+ Tokenizer
    + Entirely rewrote the 'Tokenizer' part
    + 1851 UT pass correctly (extra 51)

**Version 0.6.7 (Red boy, 2016-05-30)**


+ Report
    + Added List With Keys in Appinfo()
    + Added by-reference functions mention
    + Now reporting good visibility/static for __callstatic
    + Added bug info for PHP 7.0.7, 5.5.36, 5.6.21

+ Analysis
    + New : recommend instanceof over is_object()
    + Fixed several ignored limitations, due to case : $phpversion

+ Tokenizer
    + Fixed 'originclass' in namespaced use

**Version 0.6.6 (Princess Iron Fan, 2016-05-23)**


+ Report
    + New report, suggest disable_functions directive value.
    + Added support for memcached directives

+ Analysis
    + New analysis : spot throw without new
    + New analysis : suggest adding 2nd parameter to unserialize in PHP 7.0+
    + New analysis : spot successive if/then with the same condition
    + Added support for zendoptimizer and suhosin extensions
    + PHP7 indirect expression : added support for {} in properties

+ Tokenizer
    + Raised cycle count, to speed up building AST for large projects

**Version 0.6.5 (Great Sage Who Pacifies Heaven, 2016-05-16)**


+ Analysis
    + New analysis : spot globals that may be turned into property
    + New analysis : check that ZF1 classes are well located
    + Upgraded 'dangling foreach reference' to support key=>value
    + Better support for PHP 7 indirect expression
    + More directives for xdebug
    + Eval Without Try is PHP 7 only
    + No Choice analysis is now case insensitive

+ Tokenizer
    + Added support for keys in list() (PHP 7.1)
    + Added support for constant visibility (PHP 7.2)
    + Added support for Multi catch : catch(A|B $e) (PHP 7.1)
    + Fixed bug with + and instanceof
    + Fixed precedence between :: and ??

**Version 0.6.4 (Bull Demon King, 2016-05-09)**


+ Architecture
    + Externalized the list of recognized libraries to Json
    + Added 'Wordpress' and 'Coding convention' as Recipes

+ Report
    + Initial report for Zend Framework. Still prototyping.

+ Analysis
    + Accelerated analysis for Implicit GLobals variables
    + New analyze : Indirect Injections (Security)
    + New analyze : Should Use Coalesce (code upgrade)
    + New analyze : Suggest dirname(__FILE__) => __DIR__
    + Added 'str_rot13' as unsafe 'crypto'
    + Properties without default can't be redefined
    + Added Yield and Yield From as structures without parenthesis needs
    + Double Assignation, unless 2nd call is a functioncall (less false positives)

**Version 0.6.3 (Jade Faced Princess, 2016-05-02)**


+ Architecture
    + Removed several useless pieces of code (self analysis)
    + Added documentation for Wordpress Recipes
    + Lengthened Cycle for tokenizer

+ Report
    + Added bugfixes for PHP 7.0.6, 5.6.21, 5.5.35.
    + Now reporting token counts per files 

+ Analysis
    + New analysis : Spot variable that holds $_GET, $_POST, $_REQUEST or $_COOKIE values (internal)
    + New analysis : Report variables that are overwritten by themselves
    + New analysis : Report useless switch (empty, 1 case only)
    + Upgraded NoChoice to handle larger sequences
    + Upgraded Useless Global to handle global $x / $GLOBALS['x'] situations
    + New analysis : Wordpress Recipe : Unverified Nonce, Best Usage for $wpdb
    + New analysis : Void for PHP 7.1   

+ Tokenizer
    + Fixed but with Typehint
    + Added phppowerpoint class in external libraries

**Version 0.6.2 (Long Armed Ape Monkey, 2016-04-25)**


+ Architecture
    + Fixed phar detection (based on ext/phar)
    + Cleaned code with myself

+ Report
    + New report format : clustergrammer

+ Analysis
    + New analysis : same conditions in If / Then
    + New analysis : spot dead code in catch expressions
    + Static loops now exclude methods usage
    + Indirect variable expression are stricter
    + preg_* Option e has better support for delimiters
    + Upgraded Direct Injection in case of concatenation
    + Detect Ellipsis when counting arguments
    + Could use short assignation : avoid $a += $a + 3;

+ Tokenizer
    + Sped up Typehint detection
    + No indexing for T_STRING in properties
    + Reduced errors from token_get_all() 

**Version 0.6.1 (Red Bottomed Horse Monkey, 2016-04-18)**


+ Architecture
    + Prepared to support PHP 7.1
    + Fixed bug in user / passwords when initing the project
    + Better support for ::class when searching for libraries

+ Analysis
    + UselessParenthesis : spot nested parenthesis
    + Spot exceptions that are thrown but uncaught by the current code
    + Support for ext/lua, 
    + New : Check catch order in try/catch
    + Better identification of Composer classes, based on composer.json
    + Now spot interfaces in use declarations (less undefined interfaces)

+ Tokenizer
    + Added support for PHP 7.1
    + key => value in list() calls
    + visibility for constants in Classes and Interfaces
    + Accelerated up Typehint support

**Version 0.6.0 (Intelligent Stone Monkey, 2016-04-11)**


+ Architecture
    + Fixed a bug in Find external libraries
    + Applied fixed based on new analysis audit
    + Fixed a bug that prevented results to be prepared for report (Thanks Philippe G.)

+ Report
    + Now reports reason for excluding a file from analysis

+ Analysis
    + New analysis : Logical Mistake (first version),
    + New analysis : Iffectations (code restoration)
    + New analysis : Common alternatives
    + New analysis : No Choice (No alternatives)
    + New analysis : Random_* Without Try (security risk)
    + New analysis : Unknown PCRE options
    + New analysis : Identical conditions
    + New analysis : Hardcoded hashes 
    + Upgrade List with appends with variable name
    + Upgrade /e option detection
    + Fixed detection of unused use, with long namespaces.
    + Added finfo to ext/finfo
    + Finds exceptions that are reserved for later throwing
    + Exclude anonymous classes from Already Defined Interface

+ Tokenizer
    + Extended cycle number to speed up tokenizer. 
    + Better escaping of file names 

**Version 0.5.9 (Six Eared Macaque, 2016-04-04)**


+ Architecture
    + One progressbar per Recipe during project analysis
    + report's documentation
    + Upgraded 'External Lib' to ignore Composer folders.
    + Fixed a bug about interpreting tokens 
    + Dump collects classes, interfaces, traits definitions 
    + Now storing project name in database for future use
    + Removed PHP configuration modifications (error_reporting, display_errors)

+ Report
    + Added 'Uml' report : hierarchy report
    + Now reports Pear Usage
    + Upgraded Bugfix database for 7.0.5, 5.6.20 and 5.5.34
    + Report Yield (from) usage
    + New external configuration files : bazar, github, docker, openshift

+ Analysis
    + Added detection for undefined classes in ZF (1.8 to 1.12)
    + New : report undefined Traits
    + Added support for parent/grandparent when checking argument numbers
    + Added support for V8js

+ Tokenizer
    + Fixed bug in fullnspath for use within trait or class
    + It is possible to reach a property on an array append
    + Fixed AST between PHP 5 and 7 for globals
    + Simplified ++ analysis

**Version 0.5.8 (Sun Deity of Mao, 2016-03-28)**


+ Architecture
    + Moved to self::, instead of static::.
    + First UT for command line
    + Sped up phploc. Prepare code for finite states, in Tasks.
    + Prepare for Gremlin3 (moved gremlin calls to class)
    + Reduced shell_exec usage

+ Report
    + Fixed display bugs in Devoops report
    + Removed double analysis
    + 'Wrong number of arguments' now supports constructors

+ Analysis
    + Upgraded 'No Hardcoded IP' to handle constants, spot domains
    + Added support for TokyoTyrant
    + New analysis : spot simple regex, and suggest strpos
    + Excluded "$a[b]" from undefined constants

+ Tokenizer
    + Fixed bug with nested call to echo.
    + Fixed bug where concatenation ends on a 'AS' keyword
    + Added support of Constants in Foreach
    + Fixed multiple bugs in Grouped Use
    + Support for function as 'class' in static calls
    + Comparison accepts powers
    + Added support for empty array short syntax in sequence
    + Support constant with visibility
    + Parenthesis may be the base for Arrays

**Version 0.5.7 (Scorpion Demon, 2016-03-21)**


+ Architecture
    + Added support for folders in UT, for tests that requires several files
    + Improved compatibility with PHPunit
    + Moving gremlin_query() to Gremlin2 class
    + Doctor also reports for phar
    + Improved adaptation to PHP and Exakt in server mode
    + Autoload shouldn't die
    + Fixed case when calling Phpexec
    + Upgraded status presentation in server mode

+ Report
    + More details for Global Variable list

+ Analysis
    + Now spotting class when it is inside a string
    + Check for $this outside a trait/class
    + Check for ternary/concatenation precedence
    + Spot classes that attempt to extend final 
    + Spot set_exception_handler() that may need rework
    + Refined array_merge analysis, in case of nested loops

+ Tokenizer
    + Yield [from] may be inside an array
    + Refactored for/foreach tokens
    + Added support for a 'Project' node

**Version 0.5.6 (Ruler of Women's Country, 2016-03-14)**


+ Architecture
    + Fixed some backward compatibility with PHP 5.4
    + Started revamping 'Status' command 
    + Centralized all tokenizations to PhpExec class
    + Removed usage of __DIR__ and __FILE__ 

+ Analysis
    + Spot usage of empty() that can't work on PHP 5.4
    + Suggest using random_int instead of rand
    + Upgraded 'No Array_merge in loops' with array_merge_recursive
    + Added support for scalar type hint in Undefined Classes
    + New analysis : Better rand()

+ Tokenizer
    + Instanceof has lower precedence than comparison

**Version 0.5.5 (Immortal Ruyi, 2016-03-07)**


+ Architecture
    + Added default values for all neo4j_* configs

+ Report
    + Added support for bugfixes in 7.0.4, 5.6.19 and 5.5.33
    + Added support for bugfixes in 7.1.0-dev

+ Analysis
    + Added support for Typehint in Undeclared Classes
    + Extended 'Multiple Classes in One File' to interfaces and traits
    + Added analysis for truthy and falsy
    + Spot interfaces implemented by parents (Thanks PHP Inspect)
    + Report usage for unsafe Curl options

+ Tokenizer
    + Fixed emptyString inside a Heredoc
    + Fixed bug where Sign has lower priority than Power

**Version 0.5.4 (Nezha, 2016-02-29)**


+ Architecture
    + Removed some shell_exec() to help with portability
    + Clean command now rebuilds an empty datastore
    + Check the availability of php binaries before using
    + Produce report in a hidden folder, then push it

+ Report
    + Report the list of bug fixes that apply to code

+ Analysis
    + Help using preg_match_all options

+ Tokenizer
    + Fixed a bug with reference and instanceof

**Version 0.5.3 (Li Jing, 2016-02-22)**


+ Architecture
    + More UT
    + Supports symlinks for neo4j's folder
    + Supports symlinks for 'code' folder in projects
    + Added upgrade command to check for exakat's available versions and upgrade

+ Analysis
    + Spot CLI scripts
    + Undefined Interfaces avoids self, parent, static
    + Fixed bug in spotting undefined Interface
    + Variable Used Once in a method are not arguments
    + Added support for all structures in Double Assignation

**Version 0.5.2 (Single Horned Rhinoceros King, 2016-02-15)**


+ Analysis
    + Fixed functioncall detection with 'empty'
    + Refined 'Buried assignation' analysis
    + Fixed a bug when using definitions (class, trait, interface, functions...)
    + Better support for case-insensitive constants
    + 

+ Tokenizer
    + Fixed bug in use statement
    + Now spots PHP code in files without extension
    + Upgraded support for grouped Use statement
    + namespace may be a valid nsname part
    + Fixed bracket reports in do...while

**Version 0.5.1 (King of Spiritual Touch, 2016-02-08)**


+ Architecture
    + Added test in UT to skip incompilable sources
    + Stabilized tokenizer's UT (partial)

+ Report
    + HTML protection in Devoops format
    + No display of negative stats
    + Added support for directives : wincache, xcache, apc, opcache
    + Added support for eaccelerator and openssl

+ Analysis
    + New analysis : Spot unknown PHP directive names
    + Fixed Constants/MultipleDefinedConstants
    + Better detection of functioncalls (with List)
    + Better spotting of ini_set arguments
    + Unreachable code now finds die and exit
    + ObjectReference won't report references on scalar types
    + Revamped 'pregOptionE' analysis
    + Cleaned code with too many arguments
    + Removed useless print
    + Better report of eval() usage
    + Revamped 'Dynamic code' report
    + Fixed bug in Case/Default that are empty
    + Avoided sequences of sequences in Case/Default
    + Fixed Detection of classes' usage with extension

+ Tokenizer
    + Fixed bracket detection on While and DoWhile
    + Detect void in DoWhile
    + Removed useless T_DIE token
    + Fixed fullcode processing for anonymous classes

**Version 0.5.0 (Immortal of Antelope Power, 2016-02-01)**


+ Architecture
    + Added support for HTTP API, through 'server' command. 

+ Analysis
    + Fopen modes checked
    + Redefined default, in class's properties

+ Tokenizer
    + Fixed situation where echo and print used parenthesis (they don't)
    + Fixed rare but with instanceof and concatenation
    + Fixed support of integers in Gremlin
    + Fixed bug in addslashes and and $ protection order
    + Made Assignations more robust (no un-processed tokens)
    + Reduced the number of shell_exec usage => speed up
    + Finished support for relaxed keyword support in classes (PHP 7)

**Version 0.4.6 (Immortal of Elk Power, 2016-01-25)**


+ Architecture
    + New installation script with Vagrant and Ansible (Thanks Alexis!)
    + Updated documentation
    + Added a command to remove a project

+ Report
    + Devoops reports has case-insensitive menu sort

+ Analysis
    + Spot redefined properties, classes and methods. 
    + Spot properties that may be turned private
    + Fixed special case in Wrong Number Of Arguments
    + Fixed 'OnePage' analysis

+ Tokenizer
    + Finished support for relaxed keywords in classes
    + Sped up tokenizer by keeping counts of tokens in datastore
    + Fixed detection of CakePHP
    + Fixed special case with Labels
    + Fixed rare case with die() within ternary operator

**Version 0.4.5 (Immortal of Tiger Power, 2016-01-18)**


+ Architecture
    + Upgraded documentation
    + Default command is 'help'

+ Report
    + Better version for FacetedJson report

+ Analysis
    + New analysis that spots wrong type of argument in PHP internal functions
    + Fixed Isset With Constant for PHP 7
    + Fixed a bug that limited query size during analysis (good for bigger projects)
    + Include variadic (...) to Variable Argument Number

+ Tokenizer
    + Fixed a bug that blocked tokenizer when a analyzed script generated parse errors.
    + Added support for bazar, svn.
    + Fixed a bug in Nsnames at Loading time.

**Version 0.4.4 (Crown Prince Mo'ang, 2016-01-11)**


+ Architecture
    + Reviewed OnePage analysis
    + Dump as now an option to select Recipes
    + Dump forces line to be integer
    + Added a task to update a project's code (git only ATM)

+ Report
    + Better check when opening database for report (more to come)
    + FacetedJson (and Json) report ignore non-unicode lines
    + Added 'search' box to facetedJson

+ Analysis
    + Switch To Switch suggestions
    + Unused arguments patch for arguments used in methods
    + Unused properties doesn't mistake function static variable

+ Tokenizer
    + All Nsnames are now build at Loading time
    + Constants may be calld 'const'
    + More relaxed syntax for methods (exit, include, eval...)
    + Foreach may use coalesce
    + Fixed an edge case with Closures in functioncall

**Version 0.4.3 (Tuolong, 2015-01-04)**


+ Architecture
    + Copyright year bump
    + Doctor reports memory_limit and php version consistency
    + Switched to rmDirRecursive

+ Report
    + Removed old style reporting system

+ Analysis
    + Fixed fileupload and filesystem directives reports
    + Added report of Environment variable usage
    + Added iconv_set_encoding to the list of directive usage
    + Extension analyzes now takes into account namespaces and traits
    + Analysiss all have severity and time to fix

+ Tokenizer
    + 

**Version 0.4.2 (Red Boy, 2015-12-22)**


+ Architecture
    + Published documentation on http://exakat.readthedocs.org
    + First version of the faceted report (-format Faceted)

+ Report
    + First version of the faceted report (-format Faceted)
    + Fixed Dump that actually finishes after some time

+ Analysis
    + Spot unused arguments
    + Fixed notInInterface() filter
    + Upgraded HtmlEntitiesCall

**Version 0.4.1 (Azure Lion, 2015-12-14)**


+ Architecture
    + Rebuild the report system, for speed and versatility.

+ Report
    + Available format : JSON, Sqlite, XML, Text and HTML (Devoops).
    + Rules are now part of the documentation. 

+ Analysis
    + Upgraded 'Buried assignations' 
    + Locally Unused also spots properties without visibility (but with definition)
    + Could be class constant, if the property is used at least once
    + Better detection of files that are Definitions only (fix at Namespace calls)
    + ++ is now correctly reported as isRead and isWritten in Arguments
    + Closure's use($x) are now reported in both context (calling and called)
    + Removed usage of 'back' method, that is blocking at high token counts

+ Tokenizer
    + Fixed support for {} and {$ } inside strings
    + Fixed bug with Typehint, that prevented compilation
    + Fixed several (rare) edge cases with Sign and Staticproperties.
    + Fixed detection of closing tags

**Version 0.4.0 (Lion Lynx Demon, 2015-12-07)**


+ Architecture
    + Made PHP 7.0 the default (moved to 0.4.0)
    + Ran unit tests on PHPunit 5.1
    + Added a background tasks to build report. Will allow for progressive report.

+ Report
    + Rewrote the report from scratch. Should be finished next iteration.
    + New report is working for XML and Text report.

+ Analysis
    + Added support for ext/pecl_http
    + Added several classic folders as ignored by default (change this in config.ini)
    + Create a check for functioncall (and not methods)
    + Spots join('', file())
    + Safely ignoring some dynamic calls in undefined functions (Thanks Marc Delisle)
    + Removed ArrayAppend from double assignation

+ Tokenizer
    + Fixed a bug when class was auto-referenced.
    + Fixed detecting Static properties when they are also arrays.
    + Fixed fatal errors for mal-formed octals

Version 0.3.12 (Nine Tailed Vixen, 2015-11-30)

+ Architecture
    + ProgressBar is now displayed during Analyze phase.

+ Report
    + Report list of error messages used in the library

+ Analysis
    + Omit eval with hardcoded strings
    + Exclude some index from _SERVER from the report (they are safe)
    + Exclude php://* files as hard coded path
    + Report usage of timestamp to calculate duration 
    + Spots unused traits
    + Fixed support for big integers

+ Tokenizer
    + First support for relaxed keywords in classes. More to come.
    + Checked UT on PHP 7 (Soon to become default version)
    + Fixed version detection in Tokenizer
    + Fixed fullnspath in Use expression;

Version 0.3.11 (Hu A'qi, 2015-11-16)

+ Architecture
    + Report external services files that may be in the repository

+ Report
    + Report nested dirname calls (may be changed in PHP 7)

+ Analysis
    + Better spotting of static loops
    + Don't confuse $globals and $GLOBALS

+ Tokenizer
    + Rewrote support for As in classes. 
    + Fixed arguments that were indexed as Void
    + Trimmed code

Version 0.3.10 (Silver Horned King, 2015-11-09)

+ Architecture
    + Centralized call to cypher.

+ Report
    + Sped up several analyzes

+ Analysis
    + Fixed naming bug with reflexion
    + Support class name in arrays, short syntax
    + Report Relay Functions
    + More PHP 7 incompatibilities reports

+ Tokenizer
    + Support for 7.1 compilation (dev only)
    + Added cakephp to external libraries
    + Fixed parsing bug with static (as property definition)
    + Fixed 'count' in sequences from Function
    + Rewrote Argument detection (when there is no parenthesis)

Version 0.3.9 (Golden Horned King, 2015-11-02 up)

+ Architecture
    + Cleaned code with Exakat

+ Analysis
    + Refined report about double assignation
    + Fixed argument counting in Function Definition
    + Better support of array in Locally Used Properties
    + Updated Composer database

+ Tokenizer
    + Fixed a bug that ignored Blocks
    + Fixed a rare bug with echo and the following arguments

**Version 0.3.8 (Baihuaxiu, 2015-10-26)**


+ Architecture
    + Cleaned too many display (they go to log now), leaving commandline empty (or -v)
    + A lot more PHP 7 incompatibilities spotted 

+ Report
    + Added the list of global variables in the projects (if any)
    + Fixed reports for PHP 5.2 (they were ignored)

+ Analysis
    + Better handling of composer in unresolved classes
    + Spot setlocale with string (PHP 7)
    + Spot string unpacking (PHP 7)
    + Upgraded static method call, to avoid classes of the same family
    + Report eval without try/catch
    + Report preg_replace with /e 
    + Fixed report for empty list()
    + Spot hexadecimal in strings 
    + Report usort (and co) as incompatibilities between PHP 7 and 5

+ Tokenizer
    + Fixed edge case with Sign and namespaced function
    + Added xajax, adodb and gacl as common library
    + Fixed arguments in short array syntax
    + Fixed case where [3] was spotted inside a string

**Version 0.3.7 (Yellow Robe Demon, 2015-10-19)**


+ Architecture
    + Added and reviewed many UT. More stability.

+ Report
    + Fixed the report of the actual version of PHP being used.
    + Non-run analysis are not marked with a stethoscope
    + Report now report closures and not the containing method
    + Removed some dashboard that would generate empty links

+ Analysis
    + Better spot of blocks inside Alternative syntax
    + Speed up method spotting
    + Fixed properties which were mistaken with deep definitions

+ Tokenizer
    + Fixed fullcode for Typehint
    + Removed Ppp and moved it to Visibility

**Version 0.3.6 (White Bone Demon, 2015-10-12)**


+ Architecture
    + Large speed up at Parsing stage, for large projects
    + Added git informations in Doctor

+ Tokenizer
    + Changed processing for Arguments.
    + Support for more PHP 7 features, including Use Grouping, 
    + Fixed support for ~ 
    + Simplified ::class handling

**Version 0.3.5 (Mingyue, 2015-10-06)**


+ Architecture
    + Reported usage of array constants, improving backward compatibility
    + Checked running on PHP 7

+ Report
    + Added Definition annex
    + Fixed 'version incompatible' report that was mistaken with 'no result'
    + List all directives being modified in the code
    + List more directives that should be set for production.

+ Analysis
    + Reworked the Themes about compatibility. 
    + Added many tests for PHP 7.0 compatibility
    + Sped up UsedMethod analysis
    + Added support for PHP 7 feature : Unicode Escape Sequences, New functions/classes/interfaces, Removed Functions, 

+ Tokenizer
    + Changed processing for Empty PHP code
    + Support Variable Indirection for both PHP 5 and 7 (depends on exec version)
    + Avoid ignoring all code when finding External Libraries
    + Fixed edge cases with declare() when it is conditional.
    + Support for PHP 7's f()()()

Version 0.3.4 (Qingfeng, 2015-09-28 up)

+ Architecture
    + Added token_limit configuration to avoid running too large project (default is 1 000 000)
    + Several new tools for internal consistency check.
    + Removed support for neo-contrib's gremlin plugin

+ Report
    + Report libraries that were found and ignored

+ Analysis
    + Sped up queries that required previous analysis or multiples atoms
    + Spot global keywords inside loops (perf)
    + Better spotting of Composer classes 
    + Report double assignations

+ Tokenizer
    + Added support for Anonymous classes (PHP 7)
    + Fixed namespace manipulations (They weren't lower case)
    + Mark constants as fail back globals or local to the namespace
    + Support Null Coalesce operator (PHP 7)
    + Fixed rare case for empty strings and noDelimiter

**Version 0.3.3 (Immortal Zhenyuan, 2015-09-21)**


+ Architecture
    + Removed some shell stderr that leaked to the main script

+ Report
    + Added the list of used analysis
    + favicon is now used in the report (Devoops)
    + Fixed count report for Else 
    + Fixed directive reports for trader, bcmath and ldap.

+ Analysis
    + Rebuild the composer database
    + Fixed htmlentities analyze
    + Spot usage of 'substr($s, $p, +/- 1)' and recommend '$s[$p]'

+ Tokenizer
    + Fixed Multiplication with instantiation

**Version 0.3.2 (Tiger Vanguard, 2015-09-14)**


+ Report
    + Added link back from analysis to its themes.

+ Analysis
    + Useless Returns are now Trait compatible
    + Optimized Composer validation 
    + Removed IsKnownVendor analyze (replaced by Composer)
    + Spot inconsistent concatenations ("$a b".$c)

+ Tokenizer
    + Fixed situation where forgotten white spaces didn't have a file
    + Removed DELETE and S_STRING index
    + Fixed compatibility with Debian (shell commands)
    + Added UT for and / && precedence versus =
    + Fixed identification of empty instructions (Functions / Closure have different behaviors)

**Version 0.3.1 (Yellow Wind Demon, 2015-09-03)**


+ Architecture
    + Removed usage of Everyman dependencies
    + Added support for Neo4j Authentication
    + Added a JobQueue
    + Cleaned code with exakat itself

+ Report
    + Added Dump to SQLITE format for custom manipulations of the results
    + Added new collection of rules for Calesthenics (dev)
    + Updated composer database 
    + Now reporting found Composer.

+ Analysis
    + Fixed Compilation spotting

+ Tokenizer
    + Fixed an edge case with Sign, when used in a concatenation

Version 0.3.0 (Lingxuzi, 2015-Aug-25)

+ Architecture
    + Moved to Thinkaurelius's gremlin plug-in, Neo4j 2.2.4 and Java 8.

+ Report
    + Added a view by File 
    + Added sorting for results (by file and by analyze)

+ Analysis
    + Spot functions whose results should be checked before they are used
    + Spot breaks/continue out of a loop
    + Exports all the results in a dump.sqlite file

+ Tokenizer
    + Fixed a minor bug with ::class (messed up the {} counts)
    + removed dependency to Everyman's Neo4j classes.
    + Added a step that removes big and identifiable libraries in PHP (such as tcpdf, jpgraph, etc..)

Version 0.2.5 (Scholar in a White Robe, 2015-Aug-17)

+ Report
    + List the files that are ignored in the annex

+ Analysis
    + Updated Knowledge Database for memcache, aliases, zlib, standard
    + Added more directives to Review
    + Added support for xhprof

+ Tokenizer
    + Fixed bug with Else (Not-alternative)
    + Fixed Sequence creation with If-Then
    + Yield may be assigned
    + Removed one Tokenizer's operation (filterOut2)
    + Fixed priorities with Concatenation, Multiplication, Additions
    + Process Echo and Print separately
    + Automatically removes common bundled libraries to reduce app size

**Version 0.2.4 (Black Wind Demon, 2015-06-22)**


+ Analysis
    + Rebuild the composer database
    + Lots of new extensions supported : ev, libevent, event, php-ast, wikidiff2, proctitle, inotify, ibase, amqp, geoip, output buffering,
    + Report errors when non-variables are returned by reference
    + Marked more analyzes for PHP 7
    + Fixed Unpreprocess structures with split
    + Upgraded spotting for useless parenthesis
    + Added a check ++$i vs $i++;
    + Exclude abstract methods from Variables Used Once
    + Added new directives
    + Also check for ASP Tags

+ Tokenizer
    + Fixed the fullpath for functions when they are not defined in the code
    + Upgraded support for Return Type (PHP 7.0+)
    + error_reporting with -1 is OK
    + Fixed a precedence problem with & and &&
    + Refactored Ifthen token to support return type 
    + Added a kill command when cleaning Database

**Version 0.2.3 (Techu Shi, 2015-06-22)**


+ Analysis
    + Report usage of Return Typehint, and Scalar Typehint
    + Report usage of classes that used to return null on new
    + Report useless abstract classes

+ Tokenizer
    + Upgraded 'init' command, to handle various VCS
    + Added support for Return Typehint

**Version 0.2.2 (Xiong Shangjun, 2015-06-16)**


+ Analysis
    + Now spots short assignations
    + More UselessInstructions spotted
    + Ignore Unset as modified values in loops

+ Tokenizer
    + Added support for PHP7 new tokens (T_SPACESHIP, T_COALESCE, T_YIELD_FROM)
    + Split loading into more .csv files for lighter and more robust queries
    + Better support for arrays [1,2,3] as functioncall (just like array())
    + Process tokens by batches of 800
    + Clean vertex at each queries, not Sequence

**Version 0.2.1 (General Yin, 2015-06-02)**


+ Analysis
    + sizeOf may have 2 arguments
    + 2 clearPHP link added in documentation

+ Tokenizer
    + Fixed bug with Bitshift and Addition
    + Fixed bug with Sequence when merging sequences
    + Fixed bug with String and Addition
    + Fixed Visibility in Use instruction
    + Foreach accepts Constants as Source
    + Fixed special case for nested IfThen

**Version 0.2.0 (Demon of Confusion, 2015-05-15)**


+ First version
