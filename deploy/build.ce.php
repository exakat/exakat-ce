<?php

const PHAR_NAME = 'exakat.ce.phar';

if (file_exists(PHAR_NAME)) {
    unlink(PHAR_NAME);
}

define('ROOT_DIR', getcwd());

shell_exec('composer update --no-dev');

$php = file_get_contents('library/Exakat/Exakat.php');
$build = preg_match('/    const BUILD = (\d+);/', $php, $r);
$neo_build = ++$r[1];

$php = preg_replace('/    const BUILD = (\d+);/', '    const BUILD = '.$neo_build.';', $php);
file_put_contents('library/Exakat/Exakat.php', $php);

$begin = hrtime(true);
// create with alias "project.phar"
$phar = new Phar(PHAR_NAME, 0, PHAR_NAME);
// add all files in the project

$phar->startBuffering();

$directories = array('/library/Exakat/Autoload', 
                     '/library/Exakat/Analyzer/Complete', 
                     '/library/Exakat/Analyzer/Common', 
                     '/library/Exakat/Configsource', 
                     '/library/Exakat/Data', 
                     '/library/Exakat/Dump', 
                     '/library/Exakat/Exceptions', 
                     '/library/Exakat/Graph', 
                     '/library/Exakat/Loader', 
                     '/library/Exakat/Vcs', 
                     '/library/Exakat/Stubs', 
                     '/library/Exakat/Log', 
                     '/library/Exakat/Query', 
                     '/library/Exakat/Tasks', 
                     '/library/Exakat/Reports/Helpers', 
                     '/library/Exakat/Reports/Data', 

                     '/data',
                     '/human',
                     '/server',
                     '/vendor',
                     );

$rulesets = array(
    'Analyze',
    'Inventory',
    'Appinfo',
    'All',
    'CompatibilityPHP74',
    'CompatibilityPHP80',
    'CompatibilityPHP81',
    'Top10',
    'Preferences',
    'Dead code',
    'Appcontent',
    'Security',
    'First',
);

$analyzers = array(
'Complete/PropagateConstants',
'Classes/NonPpp',
'Complete/SetParentDefinition',
'Complete/OverwrittenProperties',
'Classes/StaticMethodsCalledFromObject',
'Constants/Constantnames',
'Functions/RedeclaredPhpFunction',
'Structures/ErrorReportingWithInteger',
'Structures/NoDirectAccess',
'Files/IsCliScript',
'Structures/ForgottenWhiteSpace',
'Structures/Noscream',
'Structures/NotNot',
'Structures/StrposCompare',
'Structures/ThrowsAndAssign',
'Structures/VardumpUsage',
'Complete/SetClassRemoteDefinitionWithTypehint',
'Constants/MultipleConstantDefinition',
'Functions/WrongOptionalParameter',
'Php/IsnullVsEqualNull',
'Type/OneVariableStrings',
'Classes/StaticContainsThis',
'Structures/WhileListEach',
'Structures/MultipleDefinedCase',
'Structures/SwitchWithoutDefault',
'Structures/NestedTernary',
'Complete/SetStringMethodDefinition',
'Structures/Htmlentitiescall',
'Complete/OverwrittenConstants',
'Classes/ClassUsage',
'Composer/IsComposerNsname',
#'Modules/DefinedClassConstants',
'Functions/IsExtFunction',
#'Modules/DefinedFunctions',
'Interfaces/InterfaceUsage',
'Traits/TraitUsage',
'Namespaces/NamespaceUsage',
'Php/DirectivesUsage',
'Structures/DanglingArrayReferences',
'Functions/AliasesUsage',
'Functions/UsesDefaultArguments',
'Complete/OverwrittenMethods',
'Functions/VariableArguments',
'Complete/CreateCompactVariables',
'Functions/CantUse',
'Exceptions/OverwriteException',
'Classes/HasMagicProperty',
#'Modules/DefinedProperty',
'Structures/BooleanStrictComparison',
'Structures/LoneBlock',
'Php/LogicalInLetters',
'Classes/ConstantClass',
'Structures/RepeatedPrint',
'Structures/PrintWithoutParenthesis',
'Structures/ObjectReferences',
'Type/NoRealComparison',
'Classes/DirectCallToMagicMethod',
'Classes/UselessFinal',
'Structures/UseConstant',
'Structures/UselessUnset',
'Performances/ArrayMergeInLoops',
'Structures/UselessParenthesis',
'Php/UseObjectApi',
'Structures/AlteringForeachWithoutReference',
'Php/UsePathinfo',
'Structures/NoParenthesisForLanguageConstruct',
'Constants/IsPhpConstant',
'Structures/ImpliedIf',
'Structures/ShouldChainException',
'Interfaces/IsExtInterface',
'Composer/IsComposerClass',
#'Modules/DefinedInterfaces',
#'Modules/DefinedClasses',
'Security/ShouldUsePreparedStatement',
'Structures/PrintAndDie',
'Structures/UncheckedResources',
'Structures/ElseIfElseif',
'Classes/MultipleDeclarations',
'Namespaces/EmptyNamespace',
'Structures/CouldUseShortAssignation',
'Performances/PrePostIncrement',
'Structures/IndicesAreIntOrString',
'Type/ShouldTypecast',
'Structures/NoSubstrOne',
'Structures/UselessBrackets',
'Structures/pregOptionE',
'Structures/EvalWithoutTry',
'Structures/UseInstanceof',
'Type/SilentlyCastInteger',
'Structures/TimestampDifference',
'Classes/RedefinedConstants',
'Classes/RedefinedDefault',
'Php/FopenMode',
'Structures/NegativePow',
'Php/BetterRand',
'Structures/TernaryInConcat',
#'Modules/DefinedTraits',
'Structures/IdenticalConditions',
'Structures/NoChoice',
'Structures/LogicalMistakes',
'Structures/SameConditions',
'Structures/ReturnTrueFalse',
'Structures/CouldUseDir',
'Php/ShouldUseCoalesce',
'Structures/IfWithSameConditions',
'Exceptions/ThrowFunctioncall',
'Classes/UseInstanceof',
'Structures/ResultMayBeMissing',
'Structures/NeverNegative',
'Structures/EmptyBlocks',
'Classes/ThrowInDestruct',
'Structures/UseSystemTmp',
'Namespaces/HiddenUse',
'Namespaces/ShouldMakeAlias',
'Classes/MultipleTraitOrInterface',
'Namespaces/MultipleAliasDefinitions',
'Structures/FailingSubstrComparison',
'Structures/ShouldMakeTernary',
'Structures/DropElseAfterReturn',
'Classes/UseClassOperator',
'Security/DontEchoError',
'Structures/NoIssetWithEmpty',
'Structures/UselessCheck',
'Namespaces/MultipleAliasDefinitionPerFile',
'Structures/DirThenSlash',
'Structures/RepeatedRegex',
'Php/NoClassInGlobal',
'Structures/CouldUseStrrepeat',
'Type/StringWithStrangeSpace',
'Structures/NoEmptyRegex',
'Structures/NoReferenceOnLeft',
'Complete/SetClassRemoteDefinitionWithGlobal',
'Complete/SetClassRemoteDefinitionWithInjection',
'Complete/SetClassRemoteDefinitionWithParenthesis',
'Structures/IsZero',
'Structures/UnconditionLoopBreak',
'Structures/NextMonthTrap',
'Structures/AutoUnsetForeach',
'Structures/IdenticalOnBothSides',
'Php/NoReferenceForTernary',
'Functions/UnusedInheritedVariable',
'Exceptions/UselessCatch',
'Classes/DontUnsetProperties',
'Php/StrtrArguments',
'Structures/MissingParenthesis',
'Performances/StrposTooMuch',
'Functions/TypehintedReferences',
'Structures/CheckJson',
'Variables/UndefinedVariable',
'Functions/ShouldYieldWithKey',
'Traits/UselessAlias',
'Php/MissingSubpattern',
'Structures/AssigneAndCompare',
'Functions/TypehintMustBeReturned',
'Classes/IsNotFamily',
'Structures/CastingTernary',
'Php/ConcatAndAddition',
'Functions/IsGenerator',
'Classes/NoParent',
'Structures/ShouldUseExplodeArgs',
'Performances/UseArraySlice',
'Structures/CoalesceAndConcat',
'Interfaces/IsNotImplemented',
'Interfaces/CantImplementTraversable',
'Structures/MergeIfThen',
'Structures/NotEqual',
'Php/WrongTypeForNativeFunction',
'Structures/AddZero',
'Arrays/MultipleIdenticalKeys',
'Complete/SetClassMethodRemoteDefinition',
'Complete/CreateDefaultValues',
'Constants/ConstantStrangeNames',
'Structures/ExitUsage',
'Structures/MultiplyByOne',
'Complete/CreateMagicProperty',
'Constants/ConstantUsage',
'Complete/MakeClassConstantDefinition',
'Classes/IsExtClass',
'Functions/UndefinedFunctions',
'Variables/VariableUsedOnceByContext',
'Structures/OrDie',
'Functions/MustReturn',
'Classes/DefinedProperty',
'Functions/UseConstantAsArguments',
'Composer/IsComposerInterface',
'Php/InternalParameterType',
'Traits/UndefinedTrait',
'Structures/PrintfArguments',
'Structures/InvalidRegex',
'Classes/UndeclaredStaticProperty',
'Classes/CheckOnCallUsage',
'Structures/StripTagsSkipsClosedTag',
'Complete/PropagateCalls',
'Classes/IsaMagicProperty',
'Constants/IsExtConstant',
'Constants/CustomConstantUsage',
'Classes/DefinedConstants',
'Complete/MakeClassMethodDefinition',
'Constants/ConstRecommended',
'Classes/UndefinedProperty',
'Interfaces/UndefinedInterfaces',
'Complete/SetClassRemoteDefinitionWithLocalNew',
'Complete/SetClassRemoteDefinitionWithReturnTypehint',
'Classes/UndefinedStaticclass',
'Type/Pack',
'Structures/ImplodeArgsOrder',
'Functions/NoLiteralForReference',
'Complete/FollowClosureDefinition',
'Typehints/CouldBeNull',
'Typehints/CouldBeString',
'Typehints/CouldBeFloat',
'Typehints/CouldBeInt',
'Typehints/CouldBeBoolean',
'Typehints/CouldBeArray',
'Typehints/CouldBeCIT',

'Structures/ListOmissions',
'Classes/NoMagicWithArray',
'Traits/UndefinedInsteadof',
'Complete/SetArrayClassDefinition',
'Structures/UselessInstruction',
'Constants/UndefinedConstants',
'Classes/UndefinedConstants',
'Php/Deprecated',
'Functions/WrongNumberOfArguments',
'Complete/MakeFunctioncallWithReference',
'Structures/UselessCasting',
'Functions/KillsApp',
'Structures/InvalidPackFormat',
'Functions/WrongReturnedType',
'Php/ScalarAreNotArrays',
'Php/IsAWithString',
'Structures/MbstringUnknownEncoding',
'Structures/MbstringThirdArg',
'Functions/WrongTypeWithCall',
'Classes/WrongTypedPropertyInit',
'Functions/UnknownParameterName',
'Typehints/MissingReturntype',
'Classes/NonStaticMethodsCalledStatic',
'Structures/ForeachReferenceIsNotModified',
'Php/AssignAnd',
'Functions/CallbackNeedsReturn',
'Functions/DynamicCode',
'Variables/SelfTransform',
);

$missing = 0;
foreach($analyzers as $analyzer) {
    try {
        $phar->addFile(ROOT_DIR . '/library/Exakat/Analyzer/' . $analyzer . '.php', '/library/Exakat/Analyzer/'.$analyzer. '.php');
    } catch (\Exception $e) {
        print "'$analyzer'\n";
        $missing++;
    }
}

if ($missing > 0) {
    die();
}

$reports = array('None',
#'Perfile',
'Sarif',
'Reports',
'Diplomat',
'Emissary',
);
foreach($reports as $report) {
    $phar->addFile(ROOT_DIR . '/library/Exakat/Reports/' . $report . '.php', '/library/Exakat/Reports/'.$report. '.php');
}

$fichiers = array('exakat', 
                  'library/helpers.php',
                  'library/Exakat/Log.php',
                  'library/Exakat/Phpexec.php',
                  'library/Exakat/Project.php',
                  'library/Exakat/Stats.php',
                  'library/Exakat/Container.php',
                  'library/Exakat/Datastore.php',
                  'library/Exakat/Exakat.php',
                  'library/Exakat/Config.php',
                  'library/Exakat/GraphElements.php', 

                  'library/Exakat/Analyzer/Typehints/CouldBeType.php', 
                  'library/Exakat/Analyzer/Structures/UnknownPregOption.php', 
                  'library/Exakat/Analyzer/Analyzer.php', 
                  'library/Exakat/Analyzer/MissingResult.php',
                  'library/Exakat/Analyzer/Rulesets.php', 
                  'library/Exakat/Analyzer/RulesetsDev.php', 
                  'library/Exakat/Analyzer/RulesetsExtra.php', 
                  'library/Exakat/Analyzer/RulesetsIgnore.php', 
                  'library/Exakat/Analyzer/RulesetsInterface.php', 
                  'library/Exakat/Analyzer/RulesetsMain.php', 
                 );

foreach($directories as $directory) {
    $phar->buildFromIterator(
        new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(ROOT_DIR.$directory, FilesystemIterator::SKIP_DOTS)
        ),
        ROOT_DIR);
}

foreach($fichiers as $fichier) {
    $phar->addFile(ROOT_DIR . '/' . $fichier, $fichier);
}

copy(ROOT_DIR . '/data/analyzers.sqlite', '/tmp/analyzers.sqlite');
$sqlite = new Sqlite3('/tmp/analyzers.sqlite', SQLITE3_OPEN_READWRITE);

# Remove analyzers that are not published
$sqlite->query('DELETE FROM analyzers WHERE (folder || "/" || name) NOT IN ("'.join('", "', $analyzers).'")');

# Remove links that are non existents
$sqlite->query(<<<SQL
DELETE FROM analyzers_categories WHERE id_analyzer IN (
    SELECT analyzers_categories.id_analyzer FROM analyzers_categories left 
    JOIN analyzers 
        ON analyzers_categories.id_analyzer = analyzers.id 
        WHERE analyzers.id is null
    );
SQL
);

# Remove analyzers that are not published
$sqlite->query('DELETE FROM categories WHERE name NOT IN ("'.join('", "', $rulesets).'")');

# Remove links that are non existents
$sqlite->query(<<<SQL
DELETE FROM analyzers_categories WHERE id_categories IN (
    SELECT analyzers_categories.id_categories 
    FROM analyzers_categories 
    LEFT JOIN categories 
        ON analyzers_categories.id_categories = categories.id 
        WHERE categories.id IS NULL
    );
SQL
);

$res = $sqlite->query('select count(*) from analyzers');
print $res->fetchArray()[0]. " lines in the table analyze\n";
unset($res);

$sqlite->query('VACUUM');
$phar->addFile('/tmp/analyzers.sqlite', '/data/analyzers.sqlite');
unlink('/tmp/analyzers.sqlite');

$defaultStub = $phar->createDefaultStub('exakat', 'exakat');
$stub = "#!/usr/bin/env php \n$defaultStub";

$phar->setStub($stub);

$phar->stopBuffering();

$end = hrtime(true);

print number_format(filesize(PHAR_NAME) / 1024 / 1024, 2).' Mb'.PHP_EOL;
print number_format(($end - $begin) / 1000000).' ms'.PHP_EOL;
print shell_exec('php ' . PHAR_NAME).PHP_EOL;

//copy(PHAR_NAME, '../exakatGithubAction/exakat.phar');
//copy(PHAR_NAME, '../docker/exakat.phar');

?>