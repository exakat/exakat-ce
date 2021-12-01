.. _Scoping:

Scoping analysis
================

Summary
-------


* `scoping files`_
* `scoping rules`_
* `scoping reports`_



Scoping files
----------------------

ignore_dirs and include_dirs are the option used to select files within a folder. Here are some tips to choose 

* From the full list of files, ignore_dirs[] is applied, then include_dirs is applied. The remaining list is processed.
* ignore one file : 
  `ignore_dirs[] = "/path/to/file.php"`

* ignore one dir : 
  `ignore_dirs[] = "/path/to/dir/"`

* ignore siblings but include one dir : 
  `ignore_dirs[] = "/path/to/parent/";`
  `include_dirs[] = "/path/to/parent/dir/"`

* ignore every name containing 'test' : 
  `ignore_dirs[] = "test";`

* only include one dir (and exclude the rest): 
  `include_dirs[] = "/path/to/dir/";`

* omitting include_dirs defaults to `"include_dirs[] = ""`
* omitting ignore_dirs defaults to `"ignore_dirs[] = ""`
* including or ignoring files multiple times only has effect once

include_dirs has priority over the `config.cache` configuration file. If a folder has been marked for exclusion in the `config.cache` file, it may be forced to be included by configuring its value with include_dirs in the `config.ini` file. 

Scoping rules
------------------------------
to be completed




Scoping reports
------------------------------

Exakat builds a list of analysis to run, based on two directives : `project_reports` and `projects_themes`. Both are list of rulesets. Unknown rulesets are omitted. 

project_reports makes sure you can extract those reports, while `projects_themes` allow you to build reports a la carte later, and avoid running the whole audit again.

Required rulesets
#################
First, analysis are very numerous, and it is very tedious to sort them by hand. Exakat only handles 'themes' which are groups of analysis. There are several list of rulesets available by default, and it is possible to customize those lists. 

When using the `projects_themes` directive, you can configure which rulesets must be processed by exakat, each time a 'project' command is run. Those rulesets are always run. 

Report-needed rulesets
######################

Reports are build based on results found during the auditing phase. Some reports, like 'Ambassador' or 'Drillinstructor' needs the results of specific rulesets. Others, like 'Text' or 'Json' build reports at the last moment. 

As such, exakat uses the project_reports directive to collect the list of necessary rulesets, and add them to the `projects_themes` results. 

Late reports
############

It is possible de extract a report, even if the configuration has not been explicitly set for it. 

For example, it is possible to build the Owasp report after telling exakat to build a 'Ambassador' report, as Ambassador includes all the analysis needed for Owasp. On the other hand, the contrary is not true : one can't get the Ambassador report after running exakat for the Owasp report, as Owasp only covers the security rulesets, and Ambassador requires other rulesets. 

Recommendations
###############

* The 'Ambassador' report has all the classic rulesets, it's the most comprehensive choice. 
* To collect everything possible, use the ruleset 'All'. It's also the longest-running ruleset of all. 
* To get one report, simply configure project_report with that report. 
* You may configure several rulesets, like 'Security', 'Suggestions', 'CompatibilityPHP73', and later extract independant results with the 'Text' or 'Json' format.
* If you just want one compulsory report and two optional reports (total of three), simply configure all of them with project_report. It's better to produce extra reports, than run again a whole audit to collect missing informations. 
* It is possible to configure customized rulesets, and use them in project_rulesets
* Excluding one analyzer is not supported. Use custom rulesets to build a new one instead. 

Example
#######

::

    project_reports[] = 'Drillinstructor';
    project_reports[] = 'Owasp';

    project_themes[] = 'Security';
    project_themes[] = 'Suggestions';
    

With that configuration, the Drillinstructor and the Owasp report are created automatically when running 'project'. Use the following command to get the specific rulesets ; 

::

    php exakat.phar report -p <project> -format Text -T Security -v 
    

Predefined config files
------------------------

7 rulesets detailled here : 

.. _annex-analyze:

Analyze
+++++++


.. _annex-ini-analyze:

Analyze for INI
_______________


INI configuration for built-in rulesets. Copy them in config/rulesets.ini, and edit them to your owns.
::

 [Analyze]
   analyzer[] = "Arrays/AmbiguousKeys";
   analyzer[] = "Arrays/MultipleIdenticalKeys";
   analyzer[] = "Arrays/NoSpreadForHash";
   analyzer[] = "Arrays/NonConstantArray";
   analyzer[] = "Arrays/NullBoolean";
   analyzer[] = "Arrays/RandomlySortedLiterals";
   analyzer[] = "Arrays/TooManyDimensions";
   analyzer[] = "Attributes/MissingAttributeAttribute";
   analyzer[] = "Attributes/ModifyImmutable";
   analyzer[] = "Classes/AbstractOrImplements";
   analyzer[] = "Classes/AbstractStatic";
   analyzer[] = "Classes/AccessPrivate";
   analyzer[] = "Classes/AccessProtected";
   analyzer[] = "Classes/AmbiguousStatic";
   analyzer[] = "Classes/AmbiguousVisibilities";
   analyzer[] = "Classes/AvoidOptionArrays";
   analyzer[] = "Classes/AvoidOptionalProperties";
   analyzer[] = "Classes/CantExtendFinal";
   analyzer[] = "Classes/CantInstantiateClass";
   analyzer[] = "Classes/CheckOnCallUsage";
   analyzer[] = "Classes/CitSameName";
   analyzer[] = "Classes/CloneWithNonObject";
   analyzer[] = "Classes/CouldBeAbstractClass";
   analyzer[] = "Classes/CouldBeFinal";
   analyzer[] = "Classes/CouldBeStatic";
   analyzer[] = "Classes/CouldBeStringable";
   analyzer[] = "Classes/CyclicReferences";
   analyzer[] = "Classes/DependantAbstractClass";
   analyzer[] = "Classes/DifferentArgumentCounts";
   analyzer[] = "Classes/DirectCallToMagicMethod";
   analyzer[] = "Classes/DontSendThisInConstructor";
   analyzer[] = "Classes/DontUnsetProperties";
   analyzer[] = "Classes/EmptyClass";
   analyzer[] = "Classes/FinalByOcramius";
   analyzer[] = "Classes/HiddenNullable";
   analyzer[] = "Classes/ImplementIsForInterface";
   analyzer[] = "Classes/ImplementedMethodsArePublic";
   analyzer[] = "Classes/IncompatibleSignature";
   analyzer[] = "Classes/IncompatibleSignature74";
   analyzer[] = "Classes/InheritedPropertyMustMatch";
   analyzer[] = "Classes/InstantiatingAbstractClass";
   analyzer[] = "Classes/MakeDefault";
   analyzer[] = "Classes/MakeGlobalAProperty";
   analyzer[] = "Classes/MethodSignatureMustBeCompatible";
   analyzer[] = "Classes/MismatchProperties";
   analyzer[] = "Classes/MissingAbstractMethod";
   analyzer[] = "Classes/MultipleDeclarations";
   analyzer[] = "Classes/MultipleTraitOrInterface";
   analyzer[] = "Classes/NoMagicWithArray";
   analyzer[] = "Classes/NoPSSOutsideClass";
   analyzer[] = "Classes/NoParent";
   analyzer[] = "Classes/NoPublicAccess";
   analyzer[] = "Classes/NoSelfReferencingConstant";
   analyzer[] = "Classes/NonNullableSetters";
   analyzer[] = "Classes/NonPpp";
   analyzer[] = "Classes/NonStaticMethodsCalledStatic";
   analyzer[] = "Classes/OldStyleConstructor";
   analyzer[] = "Classes/OldStyleVar";
   analyzer[] = "Classes/ParentFirst";
   analyzer[] = "Classes/PropertyCouldBeLocal";
   analyzer[] = "Classes/PropertyNeverUsed";
   analyzer[] = "Classes/PropertyUsedInOneMethodOnly";
   analyzer[] = "Classes/PssWithoutClass";
   analyzer[] = "Classes/RedefinedConstants";
   analyzer[] = "Classes/RedefinedDefault";
   analyzer[] = "Classes/RedefinedPrivateProperty";
   analyzer[] = "Classes/ScalarOrObjectProperty";
   analyzer[] = "Classes/ShouldUseSelf";
   analyzer[] = "Classes/ShouldUseThis";
   analyzer[] = "Classes/StaticContainsThis";
   analyzer[] = "Classes/StaticMethodsCalledFromObject";
   analyzer[] = "Classes/SwappedArguments";
   analyzer[] = "Classes/ThisIsForClasses";
   analyzer[] = "Classes/ThisIsNotAnArray";
   analyzer[] = "Classes/ThisIsNotForStatic";
   analyzer[] = "Classes/ThrowInDestruct";
   analyzer[] = "Classes/TooManyDereferencing";
   analyzer[] = "Classes/TooManyFinds";
   analyzer[] = "Classes/TooManyInjections";
   analyzer[] = "Classes/UndeclaredStaticProperty";
   analyzer[] = "Classes/UndefinedClasses";
   analyzer[] = "Classes/UndefinedConstants";
   analyzer[] = "Classes/UndefinedParentMP";
   analyzer[] = "Classes/UndefinedProperty";
   analyzer[] = "Classes/UndefinedStaticMP";
   analyzer[] = "Classes/UndefinedStaticclass";
   analyzer[] = "Classes/UnresolvedClasses";
   analyzer[] = "Classes/UnresolvedInstanceof";
   analyzer[] = "Classes/UnusedClass";
   analyzer[] = "Classes/UnusedConstant";
   analyzer[] = "Classes/UseClassOperator";
   analyzer[] = "Classes/UseInstanceof";
   analyzer[] = "Classes/UsedOnceProperty";
   analyzer[] = "Classes/UselessAbstract";
   analyzer[] = "Classes/UselessConstructor";
   analyzer[] = "Classes/UselessFinal";
   analyzer[] = "Classes/UsingThisOutsideAClass";
   analyzer[] = "Classes/WeakType";
   analyzer[] = "Classes/WrongName";
   analyzer[] = "Classes/WrongTypedPropertyInit";
   analyzer[] = "Constants/BadConstantnames";
   analyzer[] = "Constants/ConstRecommended";
   analyzer[] = "Constants/ConstantStrangeNames";
   analyzer[] = "Constants/CreatedOutsideItsNamespace";
   analyzer[] = "Constants/InvalidName";
   analyzer[] = "Constants/MultipleConstantDefinition";
   analyzer[] = "Constants/StrangeName";
   analyzer[] = "Constants/UndefinedConstants";
   analyzer[] = "Exceptions/CantThrow";
   analyzer[] = "Exceptions/CatchUndefinedVariable";
   analyzer[] = "Exceptions/ForgottenThrown";
   analyzer[] = "Exceptions/OverwriteException";
   analyzer[] = "Exceptions/ThrowFunctioncall";
   analyzer[] = "Exceptions/UncaughtExceptions";
   analyzer[] = "Exceptions/Unthrown";
   analyzer[] = "Exceptions/UselessCatch";
   analyzer[] = "Files/InclusionWrongCase";
   analyzer[] = "Files/MissingInclude";
   analyzer[] = "Functions/AliasesUsage";
   analyzer[] = "Functions/AvoidBooleanArgument";
   analyzer[] = "Functions/CallbackNeedsReturn";
   analyzer[] = "Functions/CancelledParameter";
   analyzer[] = "Functions/CannotUseStaticForClosure";
   analyzer[] = "Functions/CouldCentralize";
   analyzer[] = "Functions/DeepDefinitions";
   analyzer[] = "Functions/DontUseVoid";
   analyzer[] = "Functions/DuplicateNamedParameter";
   analyzer[] = "Functions/EmptyFunction";
   analyzer[] = "Functions/FnArgumentVariableConfusion";
   analyzer[] = "Functions/HardcodedPasswords";
   analyzer[] = "Functions/InsufficientTypehint";
   analyzer[] = "Functions/MismatchParameterAndType";
   analyzer[] = "Functions/MismatchParameterName";
   analyzer[] = "Functions/MismatchTypeAndDefault";
   analyzer[] = "Functions/MismatchedDefaultArguments";
   analyzer[] = "Functions/MismatchedTypehint";
   analyzer[] = "Functions/ModifyTypedParameter";
   analyzer[] = "Functions/MustReturn";
   analyzer[] = "Functions/NeverUsedParameter";
   analyzer[] = "Functions/NoBooleanAsDefault";
   analyzer[] = "Functions/NoLiteralForReference";
   analyzer[] = "Functions/NoReferencedVoid";
   analyzer[] = "Functions/NoReturnUsed";
   analyzer[] = "Functions/OnlyVariableForReference";
   analyzer[] = "Functions/OnlyVariablePassedByReference";
   analyzer[] = "Functions/RedeclaredPhpFunction";
   analyzer[] = "Functions/RelayFunction";
   analyzer[] = "Functions/ShouldUseConstants";
   analyzer[] = "Functions/ShouldYieldWithKey";
   analyzer[] = "Functions/TooManyLocalVariables";
   analyzer[] = "Functions/TypehintMustBeReturned";
   analyzer[] = "Functions/TypehintedReferences";
   analyzer[] = "Functions/UndefinedFunctions";
   analyzer[] = "Functions/UnknownParameterName";
   analyzer[] = "Functions/UnusedArguments";
   analyzer[] = "Functions/UnusedInheritedVariable";
   analyzer[] = "Functions/UnusedReturnedValue";
   analyzer[] = "Functions/UseConstantAsArguments";
   analyzer[] = "Functions/UselessReferenceArgument";
   analyzer[] = "Functions/UselessReturn";
   analyzer[] = "Functions/UsesDefaultArguments";
   analyzer[] = "Functions/UsingDeprecated";
   analyzer[] = "Functions/WithoutReturn";
   analyzer[] = "Functions/WrongArgumentNameWithPhpFunction";
   analyzer[] = "Functions/WrongArgumentType";
   analyzer[] = "Functions/WrongNumberOfArguments";
   analyzer[] = "Functions/WrongOptionalParameter";
   analyzer[] = "Functions/WrongReturnedType";
   analyzer[] = "Functions/WrongTypeWithCall";
   analyzer[] = "Functions/funcGetArgModified";
   analyzer[] = "Interfaces/AlreadyParentsInterface";
   analyzer[] = "Interfaces/CantImplementTraversable";
   analyzer[] = "Interfaces/ConcreteVisibility";
   analyzer[] = "Interfaces/CouldUseInterface";
   analyzer[] = "Interfaces/EmptyInterface";
   analyzer[] = "Interfaces/IsNotImplemented";
   analyzer[] = "Interfaces/NoGaranteeForPropertyConstant";
   analyzer[] = "Interfaces/RepeatedInterface";
   analyzer[] = "Interfaces/UndefinedInterfaces";
   analyzer[] = "Interfaces/UselessInterfaces";
   analyzer[] = "Namespaces/ConstantFullyQualified";
   analyzer[] = "Namespaces/EmptyNamespace";
   analyzer[] = "Namespaces/HiddenUse";
   analyzer[] = "Namespaces/MultipleAliasDefinitionPerFile";
   analyzer[] = "Namespaces/MultipleAliasDefinitions";
   analyzer[] = "Namespaces/ShouldMakeAlias";
   analyzer[] = "Namespaces/UnresolvedUse";
   analyzer[] = "Namespaces/UseWithFullyQualifiedNS";
   analyzer[] = "Performances/ArrayMergeInLoops";
   analyzer[] = "Performances/LogicalToInArray";
   analyzer[] = "Performances/MemoizeMagicCall";
   analyzer[] = "Performances/PrePostIncrement";
   analyzer[] = "Performances/StrposTooMuch";
   analyzer[] = "Performances/UseArraySlice";
   analyzer[] = "Php/ArrayKeyExistsWithObjects";
   analyzer[] = "Php/AssertFunctionIsReserved";
   analyzer[] = "Php/AssignAnd";
   analyzer[] = "Php/Assumptions";
   analyzer[] = "Php/AvoidMbDectectEncoding";
   analyzer[] = "Php/BetterRand";
   analyzer[] = "Php/ConcatAndAddition";
   analyzer[] = "Php/Crc32MightBeNegative";
   analyzer[] = "Php/Deprecated";
   analyzer[] = "Php/DontPolluteGlobalSpace";
   analyzer[] = "Php/EmptyList";
   analyzer[] = "Php/FopenMode";
   analyzer[] = "Php/ForeachObject";
   analyzer[] = "Php/HashAlgos";
   analyzer[] = "Php/Incompilable";
   analyzer[] = "Php/InternalParameterType";
   analyzer[] = "Php/IsAWithString";
   analyzer[] = "Php/IsnullVsEqualNull";
   analyzer[] = "Php/JsonSerializeReturnType";
   analyzer[] = "Php/LogicalInLetters";
   analyzer[] = "Php/MissingMagicIsset";
   analyzer[] = "Php/MissingSubpattern";
   analyzer[] = "Php/MultipleDeclareStrict";
   analyzer[] = "Php/MustCallParentConstructor";
   analyzer[] = "Php/NativeClassTypeCompatibility";
   analyzer[] = "Php/NoClassInGlobal";
   analyzer[] = "Php/NoNullForNative";
   analyzer[] = "Php/NoReferenceForTernary";
   analyzer[] = "Php/OnlyVariableForReference";
   analyzer[] = "Php/PathinfoReturns";
   analyzer[] = "Php/ReservedNames";
   analyzer[] = "Php/ScalarAreNotArrays";
   analyzer[] = "Php/ShortOpenTagRequired";
   analyzer[] = "Php/ShouldUseCoalesce";
   analyzer[] = "Php/StrtrArguments";
   analyzer[] = "Php/TooManyNativeCalls";
   analyzer[] = "Php/UnknownPcre2Option";
   analyzer[] = "Php/UseObjectApi";
   analyzer[] = "Php/UsePathinfo";
   analyzer[] = "Php/UseSetCookie";
   analyzer[] = "Php/UseStdclass";
   analyzer[] = "Php/WrongAttributeConfiguration";
   analyzer[] = "Php/WrongTypeForNativeFunction";
   analyzer[] = "Php/oldAutoloadUsage";
   analyzer[] = "Security/DontEchoError";
   analyzer[] = "Security/ShouldUsePreparedStatement";
   analyzer[] = "Structures/AddZero";
   analyzer[] = "Structures/AlteringForeachWithoutReference";
   analyzer[] = "Structures/AlternativeConsistenceByFile";
   analyzer[] = "Structures/AlwaysFalse";
   analyzer[] = "Structures/ArrayFillWithObjects";
   analyzer[] = "Structures/ArrayMapPassesByValue";
   analyzer[] = "Structures/ArrayMergeAndVariadic";
   analyzer[] = "Structures/ArrayMergeArrayArray";
   analyzer[] = "Structures/AssigneAndCompare";
   analyzer[] = "Structures/AutoUnsetForeach";
   analyzer[] = "Structures/BailOutEarly";
   analyzer[] = "Structures/BooleanStrictComparison";
   analyzer[] = "Structures/BreakOutsideLoop";
   analyzer[] = "Structures/BuriedAssignation";
   analyzer[] = "Structures/CastToBoolean";
   analyzer[] = "Structures/CastingTernary";
   analyzer[] = "Structures/CatchShadowsVariable";
   analyzer[] = "Structures/CheckAllTypes";
   analyzer[] = "Structures/CheckJson";
   analyzer[] = "Structures/CoalesceAndConcat";
   analyzer[] = "Structures/CommonAlternatives";
   analyzer[] = "Structures/ComparedComparison";
   analyzer[] = "Structures/ConcatEmpty";
   analyzer[] = "Structures/ContinueIsForLoop";
   analyzer[] = "Structures/CouldBeElse";
   analyzer[] = "Structures/CouldBeStatic";
   analyzer[] = "Structures/CouldUseDir";
   analyzer[] = "Structures/CouldUseShortAssignation";
   analyzer[] = "Structures/CouldUseStrrepeat";
   analyzer[] = "Structures/DanglingArrayReferences";
   analyzer[] = "Structures/DirThenSlash";
   analyzer[] = "Structures/DontChangeBlindKey";
   analyzer[] = "Structures/DontMixPlusPlus";
   analyzer[] = "Structures/DontReadAndWriteInOneExpression";
   analyzer[] = "Structures/DoubleAssignation";
   analyzer[] = "Structures/DoubleInstruction";
   analyzer[] = "Structures/DoubleObjectAssignation";
   analyzer[] = "Structures/DropElseAfterReturn";
   analyzer[] = "Structures/EchoWithConcat";
   analyzer[] = "Structures/ElseIfElseif";
   analyzer[] = "Structures/EmptyBlocks";
   analyzer[] = "Structures/EmptyLines";
   analyzer[] = "Structures/EmptyTryCatch";
   analyzer[] = "Structures/ErrorReportingWithInteger";
   analyzer[] = "Structures/EvalUsage";
   analyzer[] = "Structures/EvalWithoutTry";
   analyzer[] = "Structures/ExitUsage";
   analyzer[] = "Structures/FailingSubstrComparison";
   analyzer[] = "Structures/ForeachReferenceIsNotModified";
   analyzer[] = "Structures/ForeachSourceValue";
   analyzer[] = "Structures/ForgottenWhiteSpace";
   analyzer[] = "Structures/GlobalUsage";
   analyzer[] = "Structures/Htmlentitiescall";
   analyzer[] = "Structures/HtmlentitiescallDefaultFlag";
   analyzer[] = "Structures/IdenticalConditions";
   analyzer[] = "Structures/IdenticalConsecutive";
   analyzer[] = "Structures/IdenticalOnBothSides";
   analyzer[] = "Structures/IfWithSameConditions";
   analyzer[] = "Structures/Iffectation";
   analyzer[] = "Structures/ImpliedIf";
   analyzer[] = "Structures/ImplodeArgsOrder";
   analyzer[] = "Structures/InconsistentElseif";
   analyzer[] = "Structures/IndicesAreIntOrString";
   analyzer[] = "Structures/InfiniteRecursion";
   analyzer[] = "Structures/InvalidPackFormat";
   analyzer[] = "Structures/InvalidRegex";
   analyzer[] = "Structures/IsZero";
   analyzer[] = "Structures/ListOmissions";
   analyzer[] = "Structures/LogicalMistakes";
   analyzer[] = "Structures/LoneBlock";
   analyzer[] = "Structures/LongArguments";
   analyzer[] = "Structures/MaxLevelOfIdentation";
   analyzer[] = "Structures/MbstringThirdArg";
   analyzer[] = "Structures/MbstringUnknownEncoding";
   analyzer[] = "Structures/MergeIfThen";
   analyzer[] = "Structures/MismatchedTernary";
   analyzer[] = "Structures/MissingCases";
   analyzer[] = "Structures/MissingNew";
   analyzer[] = "Structures/MissingParenthesis";
   analyzer[] = "Structures/MixedConcatInterpolation";
   analyzer[] = "Structures/ModernEmpty";
   analyzer[] = "Structures/MultipleDefinedCase";
   analyzer[] = "Structures/MultipleTypeVariable";
   analyzer[] = "Structures/MultiplyByOne";
   analyzer[] = "Structures/NegativePow";
   analyzer[] = "Structures/NestedIfthen";
   analyzer[] = "Structures/NestedTernary";
   analyzer[] = "Structures/NeverNegative";
   analyzer[] = "Structures/NextMonthTrap";
   analyzer[] = "Structures/NoAppendOnSource";
   analyzer[] = "Structures/NoChangeIncomingVariables";
   analyzer[] = "Structures/NoChoice";
   analyzer[] = "Structures/NoDirectUsage";
   analyzer[] = "Structures/NoEmptyRegex";
   analyzer[] = "Structures/NoGetClassNull";
   analyzer[] = "Structures/NoHardcodedHash";
   analyzer[] = "Structures/NoHardcodedIp";
   analyzer[] = "Structures/NoHardcodedPath";
   analyzer[] = "Structures/NoHardcodedPort";
   analyzer[] = "Structures/NoIssetWithEmpty";
   analyzer[] = "Structures/NoNeedForElse";
   analyzer[] = "Structures/NoNeedForTriple";
   analyzer[] = "Structures/NoObjectAsIndex";
   analyzer[] = "Structures/NoParenthesisForLanguageConstruct";
   analyzer[] = "Structures/NoReferenceOnLeft";
   analyzer[] = "Structures/NoSubstrOne";
   analyzer[] = "Structures/NoVariableIsACondition";
   analyzer[] = "Structures/Noscream";
   analyzer[] = "Structures/NotEqual";
   analyzer[] = "Structures/NotNot";
   analyzer[] = "Structures/ObjectReferences";
   analyzer[] = "Structures/OnceUsage";
   analyzer[] = "Structures/OneLineTwoInstructions";
   analyzer[] = "Structures/OnlyFirstByte";
   analyzer[] = "Structures/OnlyVariableReturnedByReference";
   analyzer[] = "Structures/OrDie";
   analyzer[] = "Structures/PossibleInfiniteLoop";
   analyzer[] = "Structures/PrintAndDie";
   analyzer[] = "Structures/PrintWithoutParenthesis";
   analyzer[] = "Structures/PrintfArguments";
   analyzer[] = "Structures/QueriesInLoop";
   analyzer[] = "Structures/RepeatedPrint";
   analyzer[] = "Structures/RepeatedRegex";
   analyzer[] = "Structures/ResultMayBeMissing";
   analyzer[] = "Structures/ReturnTrueFalse";
   analyzer[] = "Structures/SameConditions";
   analyzer[] = "Structures/ShouldChainException";
   analyzer[] = "Structures/ShouldMakeTernary";
   analyzer[] = "Structures/ShouldPreprocess";
   analyzer[] = "Structures/ShouldUseExplodeArgs";
   analyzer[] = "Structures/StaticLoop";
   analyzer[] = "Structures/StripTagsSkipsClosedTag";
   analyzer[] = "Structures/StrposCompare";
   analyzer[] = "Structures/SuspiciousComparison";
   analyzer[] = "Structures/SwitchToSwitch";
   analyzer[] = "Structures/SwitchWithoutDefault";
   analyzer[] = "Structures/TernaryInConcat";
   analyzer[] = "Structures/TestThenCast";
   analyzer[] = "Structures/ThrowsAndAssign";
   analyzer[] = "Structures/TimestampDifference";
   analyzer[] = "Structures/UncheckedResources";
   analyzer[] = "Structures/UnconditionLoopBreak";
   analyzer[] = "Structures/UnknownPregOption";
   analyzer[] = "Structures/Unpreprocessed";
   analyzer[] = "Structures/UnsetInForeach";
   analyzer[] = "Structures/UnsupportedTypesWithOperators";
   analyzer[] = "Structures/UnusedGlobal";
   analyzer[] = "Structures/UseConstant";
   analyzer[] = "Structures/UseInstanceof";
   analyzer[] = "Structures/UsePositiveCondition";
   analyzer[] = "Structures/UseSystemTmp";
   analyzer[] = "Structures/UselessBrackets";
   analyzer[] = "Structures/UselessCasting";
   analyzer[] = "Structures/UselessCheck";
   analyzer[] = "Structures/UselessGlobal";
   analyzer[] = "Structures/UselessInstruction";
   analyzer[] = "Structures/UselessParenthesis";
   analyzer[] = "Structures/UselessSwitch";
   analyzer[] = "Structures/UselessUnset";
   analyzer[] = "Structures/VardumpUsage";
   analyzer[] = "Structures/WhileListEach";
   analyzer[] = "Structures/WrongRange";
   analyzer[] = "Structures/pregOptionE";
   analyzer[] = "Structures/toStringThrowsException";
   analyzer[] = "Traits/AlreadyParentsTrait";
   analyzer[] = "Traits/DependantTrait";
   analyzer[] = "Traits/EmptyTrait";
   analyzer[] = "Traits/MethodCollisionTraits";
   analyzer[] = "Traits/TraitNotFound";
   analyzer[] = "Traits/UndefinedInsteadof";
   analyzer[] = "Traits/UndefinedTrait";
   analyzer[] = "Traits/UselessAlias";
   analyzer[] = "Type/NoRealComparison";
   analyzer[] = "Type/OneVariableStrings";
   analyzer[] = "Type/ShouldTypecast";
   analyzer[] = "Type/SilentlyCastInteger";
   analyzer[] = "Type/StringHoldAVariable";
   analyzer[] = "Type/StringWithStrangeSpace";
   analyzer[] = "Typehints/MissingReturntype";
   analyzer[] = "Variables/AssignedTwiceOrMore";
   analyzer[] = "Variables/ConstantTypo";
   analyzer[] = "Variables/LostReferences";
   analyzer[] = "Variables/OverwrittenLiterals";
   analyzer[] = "Variables/StrangeName";
   analyzer[] = "Variables/UndefinedConstantName";
   analyzer[] = "Variables/UndefinedVariable";
   analyzer[] = "Variables/VariableNonascii";
   analyzer[] = "Variables/VariableUsedOnce";
   analyzer[] = "Variables/VariableUsedOnceByContext";
   analyzer[] = "Variables/WrittenOnlyVariable";
.. _annex-yaml-analyze:

Analyze for .exakat.yaml
________________________


YAML configuration for built-in rulesets. Copy them in your code, with the name .exakat.yaml, and edit them to your owns.
::

  rulesets:
    'Analyze':
     - 'Arrays/AmbiguousKeys'
     - 'Arrays/MultipleIdenticalKeys'
     - 'Arrays/NoSpreadForHash'
     - 'Arrays/NonConstantArray'
     - 'Arrays/NullBoolean'
     - 'Arrays/RandomlySortedLiterals'
     - 'Arrays/TooManyDimensions'
     - 'Attributes/MissingAttributeAttribute'
     - 'Attributes/ModifyImmutable'
     - 'Classes/AbstractOrImplements'
     - 'Classes/AbstractStatic'
     - 'Classes/AccessPrivate'
     - 'Classes/AccessProtected'
     - 'Classes/AmbiguousStatic'
     - 'Classes/AmbiguousVisibilities'
     - 'Classes/AvoidOptionArrays'
     - 'Classes/AvoidOptionalProperties'
     - 'Classes/CantExtendFinal'
     - 'Classes/CantInstantiateClass'
     - 'Classes/CheckOnCallUsage'
     - 'Classes/CitSameName'
     - 'Classes/CloneWithNonObject'
     - 'Classes/CouldBeAbstractClass'
     - 'Classes/CouldBeFinal'
     - 'Classes/CouldBeStatic'
     - 'Classes/CouldBeStringable'
     - 'Classes/CyclicReferences'
     - 'Classes/DependantAbstractClass'
     - 'Classes/DifferentArgumentCounts'
     - 'Classes/DirectCallToMagicMethod'
     - 'Classes/DontSendThisInConstructor'
     - 'Classes/DontUnsetProperties'
     - 'Classes/EmptyClass'
     - 'Classes/FinalByOcramius'
     - 'Classes/HiddenNullable'
     - 'Classes/ImplementIsForInterface'
     - 'Classes/ImplementedMethodsArePublic'
     - 'Classes/IncompatibleSignature'
     - 'Classes/IncompatibleSignature74'
     - 'Classes/InheritedPropertyMustMatch'
     - 'Classes/InstantiatingAbstractClass'
     - 'Classes/MakeDefault'
     - 'Classes/MakeGlobalAProperty'
     - 'Classes/MethodSignatureMustBeCompatible'
     - 'Classes/MismatchProperties'
     - 'Classes/MissingAbstractMethod'
     - 'Classes/MultipleDeclarations'
     - 'Classes/MultipleTraitOrInterface'
     - 'Classes/NoMagicWithArray'
     - 'Classes/NoPSSOutsideClass'
     - 'Classes/NoParent'
     - 'Classes/NoPublicAccess'
     - 'Classes/NoSelfReferencingConstant'
     - 'Classes/NonNullableSetters'
     - 'Classes/NonPpp'
     - 'Classes/NonStaticMethodsCalledStatic'
     - 'Classes/OldStyleConstructor'
     - 'Classes/OldStyleVar'
     - 'Classes/ParentFirst'
     - 'Classes/PropertyCouldBeLocal'
     - 'Classes/PropertyNeverUsed'
     - 'Classes/PropertyUsedInOneMethodOnly'
     - 'Classes/PssWithoutClass'
     - 'Classes/RedefinedConstants'
     - 'Classes/RedefinedDefault'
     - 'Classes/RedefinedPrivateProperty'
     - 'Classes/ScalarOrObjectProperty'
     - 'Classes/ShouldUseSelf'
     - 'Classes/ShouldUseThis'
     - 'Classes/StaticContainsThis'
     - 'Classes/StaticMethodsCalledFromObject'
     - 'Classes/SwappedArguments'
     - 'Classes/ThisIsForClasses'
     - 'Classes/ThisIsNotAnArray'
     - 'Classes/ThisIsNotForStatic'
     - 'Classes/ThrowInDestruct'
     - 'Classes/TooManyDereferencing'
     - 'Classes/TooManyFinds'
     - 'Classes/TooManyInjections'
     - 'Classes/UndeclaredStaticProperty'
     - 'Classes/UndefinedClasses'
     - 'Classes/UndefinedConstants'
     - 'Classes/UndefinedParentMP'
     - 'Classes/UndefinedProperty'
     - 'Classes/UndefinedStaticMP'
     - 'Classes/UndefinedStaticclass'
     - 'Classes/UnresolvedClasses'
     - 'Classes/UnresolvedInstanceof'
     - 'Classes/UnusedClass'
     - 'Classes/UnusedConstant'
     - 'Classes/UseClassOperator'
     - 'Classes/UseInstanceof'
     - 'Classes/UsedOnceProperty'
     - 'Classes/UselessAbstract'
     - 'Classes/UselessConstructor'
     - 'Classes/UselessFinal'
     - 'Classes/UsingThisOutsideAClass'
     - 'Classes/WeakType'
     - 'Classes/WrongName'
     - 'Classes/WrongTypedPropertyInit'
     - 'Constants/BadConstantnames'
     - 'Constants/ConstRecommended'
     - 'Constants/ConstantStrangeNames'
     - 'Constants/CreatedOutsideItsNamespace'
     - 'Constants/InvalidName'
     - 'Constants/MultipleConstantDefinition'
     - 'Constants/StrangeName'
     - 'Constants/UndefinedConstants'
     - 'Exceptions/CantThrow'
     - 'Exceptions/CatchUndefinedVariable'
     - 'Exceptions/ForgottenThrown'
     - 'Exceptions/OverwriteException'
     - 'Exceptions/ThrowFunctioncall'
     - 'Exceptions/UncaughtExceptions'
     - 'Exceptions/Unthrown'
     - 'Exceptions/UselessCatch'
     - 'Files/InclusionWrongCase'
     - 'Files/MissingInclude'
     - 'Functions/AliasesUsage'
     - 'Functions/AvoidBooleanArgument'
     - 'Functions/CallbackNeedsReturn'
     - 'Functions/CancelledParameter'
     - 'Functions/CannotUseStaticForClosure'
     - 'Functions/CouldCentralize'
     - 'Functions/DeepDefinitions'
     - 'Functions/DontUseVoid'
     - 'Functions/DuplicateNamedParameter'
     - 'Functions/EmptyFunction'
     - 'Functions/FnArgumentVariableConfusion'
     - 'Functions/HardcodedPasswords'
     - 'Functions/InsufficientTypehint'
     - 'Functions/MismatchParameterAndType'
     - 'Functions/MismatchParameterName'
     - 'Functions/MismatchTypeAndDefault'
     - 'Functions/MismatchedDefaultArguments'
     - 'Functions/MismatchedTypehint'
     - 'Functions/ModifyTypedParameter'
     - 'Functions/MustReturn'
     - 'Functions/NeverUsedParameter'
     - 'Functions/NoBooleanAsDefault'
     - 'Functions/NoLiteralForReference'
     - 'Functions/NoReferencedVoid'
     - 'Functions/NoReturnUsed'
     - 'Functions/OnlyVariableForReference'
     - 'Functions/OnlyVariablePassedByReference'
     - 'Functions/RedeclaredPhpFunction'
     - 'Functions/RelayFunction'
     - 'Functions/ShouldUseConstants'
     - 'Functions/ShouldYieldWithKey'
     - 'Functions/TooManyLocalVariables'
     - 'Functions/TypehintMustBeReturned'
     - 'Functions/TypehintedReferences'
     - 'Functions/UndefinedFunctions'
     - 'Functions/UnknownParameterName'
     - 'Functions/UnusedArguments'
     - 'Functions/UnusedInheritedVariable'
     - 'Functions/UnusedReturnedValue'
     - 'Functions/UseConstantAsArguments'
     - 'Functions/UselessReferenceArgument'
     - 'Functions/UselessReturn'
     - 'Functions/UsesDefaultArguments'
     - 'Functions/UsingDeprecated'
     - 'Functions/WithoutReturn'
     - 'Functions/WrongArgumentNameWithPhpFunction'
     - 'Functions/WrongArgumentType'
     - 'Functions/WrongNumberOfArguments'
     - 'Functions/WrongOptionalParameter'
     - 'Functions/WrongReturnedType'
     - 'Functions/WrongTypeWithCall'
     - 'Functions/funcGetArgModified'
     - 'Interfaces/AlreadyParentsInterface'
     - 'Interfaces/CantImplementTraversable'
     - 'Interfaces/ConcreteVisibility'
     - 'Interfaces/CouldUseInterface'
     - 'Interfaces/EmptyInterface'
     - 'Interfaces/IsNotImplemented'
     - 'Interfaces/NoGaranteeForPropertyConstant'
     - 'Interfaces/RepeatedInterface'
     - 'Interfaces/UndefinedInterfaces'
     - 'Interfaces/UselessInterfaces'
     - 'Namespaces/ConstantFullyQualified'
     - 'Namespaces/EmptyNamespace'
     - 'Namespaces/HiddenUse'
     - 'Namespaces/MultipleAliasDefinitionPerFile'
     - 'Namespaces/MultipleAliasDefinitions'
     - 'Namespaces/ShouldMakeAlias'
     - 'Namespaces/UnresolvedUse'
     - 'Namespaces/UseWithFullyQualifiedNS'
     - 'Performances/ArrayMergeInLoops'
     - 'Performances/LogicalToInArray'
     - 'Performances/MemoizeMagicCall'
     - 'Performances/PrePostIncrement'
     - 'Performances/StrposTooMuch'
     - 'Performances/UseArraySlice'
     - 'Php/ArrayKeyExistsWithObjects'
     - 'Php/AssertFunctionIsReserved'
     - 'Php/AssignAnd'
     - 'Php/Assumptions'
     - 'Php/AvoidMbDectectEncoding'
     - 'Php/BetterRand'
     - 'Php/ConcatAndAddition'
     - 'Php/Crc32MightBeNegative'
     - 'Php/Deprecated'
     - 'Php/DontPolluteGlobalSpace'
     - 'Php/EmptyList'
     - 'Php/FopenMode'
     - 'Php/ForeachObject'
     - 'Php/HashAlgos'
     - 'Php/Incompilable'
     - 'Php/InternalParameterType'
     - 'Php/IsAWithString'
     - 'Php/IsnullVsEqualNull'
     - 'Php/JsonSerializeReturnType'
     - 'Php/LogicalInLetters'
     - 'Php/MissingMagicIsset'
     - 'Php/MissingSubpattern'
     - 'Php/MultipleDeclareStrict'
     - 'Php/MustCallParentConstructor'
     - 'Php/NativeClassTypeCompatibility'
     - 'Php/NoClassInGlobal'
     - 'Php/NoNullForNative'
     - 'Php/NoReferenceForTernary'
     - 'Php/OnlyVariableForReference'
     - 'Php/PathinfoReturns'
     - 'Php/ReservedNames'
     - 'Php/ScalarAreNotArrays'
     - 'Php/ShortOpenTagRequired'
     - 'Php/ShouldUseCoalesce'
     - 'Php/StrtrArguments'
     - 'Php/TooManyNativeCalls'
     - 'Php/UnknownPcre2Option'
     - 'Php/UseObjectApi'
     - 'Php/UsePathinfo'
     - 'Php/UseSetCookie'
     - 'Php/UseStdclass'
     - 'Php/WrongAttributeConfiguration'
     - 'Php/WrongTypeForNativeFunction'
     - 'Php/oldAutoloadUsage'
     - 'Security/DontEchoError'
     - 'Security/ShouldUsePreparedStatement'
     - 'Structures/AddZero'
     - 'Structures/AlteringForeachWithoutReference'
     - 'Structures/AlternativeConsistenceByFile'
     - 'Structures/AlwaysFalse'
     - 'Structures/ArrayFillWithObjects'
     - 'Structures/ArrayMapPassesByValue'
     - 'Structures/ArrayMergeAndVariadic'
     - 'Structures/ArrayMergeArrayArray'
     - 'Structures/AssigneAndCompare'
     - 'Structures/AutoUnsetForeach'
     - 'Structures/BailOutEarly'
     - 'Structures/BooleanStrictComparison'
     - 'Structures/BreakOutsideLoop'
     - 'Structures/BuriedAssignation'
     - 'Structures/CastToBoolean'
     - 'Structures/CastingTernary'
     - 'Structures/CatchShadowsVariable'
     - 'Structures/CheckAllTypes'
     - 'Structures/CheckJson'
     - 'Structures/CoalesceAndConcat'
     - 'Structures/CommonAlternatives'
     - 'Structures/ComparedComparison'
     - 'Structures/ConcatEmpty'
     - 'Structures/ContinueIsForLoop'
     - 'Structures/CouldBeElse'
     - 'Structures/CouldBeStatic'
     - 'Structures/CouldUseDir'
     - 'Structures/CouldUseShortAssignation'
     - 'Structures/CouldUseStrrepeat'
     - 'Structures/DanglingArrayReferences'
     - 'Structures/DirThenSlash'
     - 'Structures/DontChangeBlindKey'
     - 'Structures/DontMixPlusPlus'
     - 'Structures/DontReadAndWriteInOneExpression'
     - 'Structures/DoubleAssignation'
     - 'Structures/DoubleInstruction'
     - 'Structures/DoubleObjectAssignation'
     - 'Structures/DropElseAfterReturn'
     - 'Structures/EchoWithConcat'
     - 'Structures/ElseIfElseif'
     - 'Structures/EmptyBlocks'
     - 'Structures/EmptyLines'
     - 'Structures/EmptyTryCatch'
     - 'Structures/ErrorReportingWithInteger'
     - 'Structures/EvalUsage'
     - 'Structures/EvalWithoutTry'
     - 'Structures/ExitUsage'
     - 'Structures/FailingSubstrComparison'
     - 'Structures/ForeachReferenceIsNotModified'
     - 'Structures/ForeachSourceValue'
     - 'Structures/ForgottenWhiteSpace'
     - 'Structures/GlobalUsage'
     - 'Structures/Htmlentitiescall'
     - 'Structures/HtmlentitiescallDefaultFlag'
     - 'Structures/IdenticalConditions'
     - 'Structures/IdenticalConsecutive'
     - 'Structures/IdenticalOnBothSides'
     - 'Structures/IfWithSameConditions'
     - 'Structures/Iffectation'
     - 'Structures/ImpliedIf'
     - 'Structures/ImplodeArgsOrder'
     - 'Structures/InconsistentElseif'
     - 'Structures/IndicesAreIntOrString'
     - 'Structures/InfiniteRecursion'
     - 'Structures/InvalidPackFormat'
     - 'Structures/InvalidRegex'
     - 'Structures/IsZero'
     - 'Structures/ListOmissions'
     - 'Structures/LogicalMistakes'
     - 'Structures/LoneBlock'
     - 'Structures/LongArguments'
     - 'Structures/MaxLevelOfIdentation'
     - 'Structures/MbstringThirdArg'
     - 'Structures/MbstringUnknownEncoding'
     - 'Structures/MergeIfThen'
     - 'Structures/MismatchedTernary'
     - 'Structures/MissingCases'
     - 'Structures/MissingNew'
     - 'Structures/MissingParenthesis'
     - 'Structures/MixedConcatInterpolation'
     - 'Structures/ModernEmpty'
     - 'Structures/MultipleDefinedCase'
     - 'Structures/MultipleTypeVariable'
     - 'Structures/MultiplyByOne'
     - 'Structures/NegativePow'
     - 'Structures/NestedIfthen'
     - 'Structures/NestedTernary'
     - 'Structures/NeverNegative'
     - 'Structures/NextMonthTrap'
     - 'Structures/NoAppendOnSource'
     - 'Structures/NoChangeIncomingVariables'
     - 'Structures/NoChoice'
     - 'Structures/NoDirectUsage'
     - 'Structures/NoEmptyRegex'
     - 'Structures/NoGetClassNull'
     - 'Structures/NoHardcodedHash'
     - 'Structures/NoHardcodedIp'
     - 'Structures/NoHardcodedPath'
     - 'Structures/NoHardcodedPort'
     - 'Structures/NoIssetWithEmpty'
     - 'Structures/NoNeedForElse'
     - 'Structures/NoNeedForTriple'
     - 'Structures/NoObjectAsIndex'
     - 'Structures/NoParenthesisForLanguageConstruct'
     - 'Structures/NoReferenceOnLeft'
     - 'Structures/NoSubstrOne'
     - 'Structures/NoVariableIsACondition'
     - 'Structures/Noscream'
     - 'Structures/NotEqual'
     - 'Structures/NotNot'
     - 'Structures/ObjectReferences'
     - 'Structures/OnceUsage'
     - 'Structures/OneLineTwoInstructions'
     - 'Structures/OnlyFirstByte'
     - 'Structures/OnlyVariableReturnedByReference'
     - 'Structures/OrDie'
     - 'Structures/PossibleInfiniteLoop'
     - 'Structures/PrintAndDie'
     - 'Structures/PrintWithoutParenthesis'
     - 'Structures/PrintfArguments'
     - 'Structures/QueriesInLoop'
     - 'Structures/RepeatedPrint'
     - 'Structures/RepeatedRegex'
     - 'Structures/ResultMayBeMissing'
     - 'Structures/ReturnTrueFalse'
     - 'Structures/SameConditions'
     - 'Structures/ShouldChainException'
     - 'Structures/ShouldMakeTernary'
     - 'Structures/ShouldPreprocess'
     - 'Structures/ShouldUseExplodeArgs'
     - 'Structures/StaticLoop'
     - 'Structures/StripTagsSkipsClosedTag'
     - 'Structures/StrposCompare'
     - 'Structures/SuspiciousComparison'
     - 'Structures/SwitchToSwitch'
     - 'Structures/SwitchWithoutDefault'
     - 'Structures/TernaryInConcat'
     - 'Structures/TestThenCast'
     - 'Structures/ThrowsAndAssign'
     - 'Structures/TimestampDifference'
     - 'Structures/UncheckedResources'
     - 'Structures/UnconditionLoopBreak'
     - 'Structures/UnknownPregOption'
     - 'Structures/Unpreprocessed'
     - 'Structures/UnsetInForeach'
     - 'Structures/UnsupportedTypesWithOperators'
     - 'Structures/UnusedGlobal'
     - 'Structures/UseConstant'
     - 'Structures/UseInstanceof'
     - 'Structures/UsePositiveCondition'
     - 'Structures/UseSystemTmp'
     - 'Structures/UselessBrackets'
     - 'Structures/UselessCasting'
     - 'Structures/UselessCheck'
     - 'Structures/UselessGlobal'
     - 'Structures/UselessInstruction'
     - 'Structures/UselessParenthesis'
     - 'Structures/UselessSwitch'
     - 'Structures/UselessUnset'
     - 'Structures/VardumpUsage'
     - 'Structures/WhileListEach'
     - 'Structures/WrongRange'
     - 'Structures/pregOptionE'
     - 'Structures/toStringThrowsException'
     - 'Traits/AlreadyParentsTrait'
     - 'Traits/DependantTrait'
     - 'Traits/EmptyTrait'
     - 'Traits/MethodCollisionTraits'
     - 'Traits/TraitNotFound'
     - 'Traits/UndefinedInsteadof'
     - 'Traits/UndefinedTrait'
     - 'Traits/UselessAlias'
     - 'Type/NoRealComparison'
     - 'Type/OneVariableStrings'
     - 'Type/ShouldTypecast'
     - 'Type/SilentlyCastInteger'
     - 'Type/StringHoldAVariable'
     - 'Type/StringWithStrangeSpace'
     - 'Typehints/MissingReturntype'
     - 'Variables/AssignedTwiceOrMore'
     - 'Variables/ConstantTypo'
     - 'Variables/LostReferences'
     - 'Variables/OverwrittenLiterals'
     - 'Variables/StrangeName'
     - 'Variables/UndefinedConstantName'
     - 'Variables/UndefinedVariable'
     - 'Variables/VariableNonascii'
     - 'Variables/VariableUsedOnce'
     - 'Variables/VariableUsedOnceByContext'
     - 'Variables/WrittenOnlyVariable'



.. _annex-appinfo:

Appinfo
+++++++


.. _annex-ini-appinfo:

Appinfo for INI
_______________


INI configuration for built-in rulesets. Copy them in config/rulesets.ini, and edit them to your owns.
::

 [Appinfo]
   analyzer[] = "Arrays/ArrayNSUsage";
   analyzer[] = "Arrays/Arrayindex";
   analyzer[] = "Arrays/Multidimensional";
   analyzer[] = "Arrays/Phparrayindex";
   analyzer[] = "Arrays/WithCallback";
   analyzer[] = "Classes/Abstractclass";
   analyzer[] = "Classes/Abstractmethods";
   analyzer[] = "Classes/Anonymous";
   analyzer[] = "Classes/ClassAliasUsage";
   analyzer[] = "Classes/ClassOverreach";
   analyzer[] = "Classes/Classnames";
   analyzer[] = "Classes/CloningUsage";
   analyzer[] = "Classes/ConstVisibilityUsage";
   analyzer[] = "Classes/ConstantDefinition";
   analyzer[] = "Classes/DynamicClass";
   analyzer[] = "Classes/DynamicConstantCall";
   analyzer[] = "Classes/DynamicMethodCall";
   analyzer[] = "Classes/DynamicNew";
   analyzer[] = "Classes/DynamicPropertyCall";
   analyzer[] = "Classes/ImmutableSignature";
   analyzer[] = "Classes/MagicMethod";
   analyzer[] = "Classes/MultipleClassesInFile";
   analyzer[] = "Classes/OldStyleConstructor";
   analyzer[] = "Classes/OverwrittenConst";
   analyzer[] = "Classes/RedefinedMethods";
   analyzer[] = "Classes/StaticMethods";
   analyzer[] = "Classes/StaticProperties";
   analyzer[] = "Classes/TestClass";
   analyzer[] = "Classes/VariableClasses";
   analyzer[] = "Composer/Autoload";
   analyzer[] = "Composer/IsComposerNsname";
   analyzer[] = "Composer/UseComposer";
   analyzer[] = "Composer/UseComposerLock";
   analyzer[] = "Constants/CaseInsensitiveConstants";
   analyzer[] = "Constants/ConditionedConstants";
   analyzer[] = "Constants/ConstantUsage";
   analyzer[] = "Constants/DynamicCreation";
   analyzer[] = "Constants/MagicConstantUsage";
   analyzer[] = "Constants/PhpConstantUsage";
   analyzer[] = "Constants/VariableConstant";
   analyzer[] = "Dump/ParameterArgumentsLinks";
   analyzer[] = "Exceptions/DefinedExceptions";
   analyzer[] = "Exceptions/MultipleCatch";
   analyzer[] = "Exceptions/ThrownExceptions";
   analyzer[] = "Extensions/Extamqp";
   analyzer[] = "Extensions/Extapache";
   analyzer[] = "Extensions/Extapc";
   analyzer[] = "Extensions/Extapcu";
   analyzer[] = "Extensions/Extarray";
   analyzer[] = "Extensions/Extast";
   analyzer[] = "Extensions/Extasync";
   analyzer[] = "Extensions/Extbcmath";
   analyzer[] = "Extensions/Extbzip2";
   analyzer[] = "Extensions/Extcairo";
   analyzer[] = "Extensions/Extcalendar";
   analyzer[] = "Extensions/Extcmark";
   analyzer[] = "Extensions/Extcom";
   analyzer[] = "Extensions/Extcrypto";
   analyzer[] = "Extensions/Extcsprng";
   analyzer[] = "Extensions/Extctype";
   analyzer[] = "Extensions/Extcurl";
   analyzer[] = "Extensions/Extcyrus";
   analyzer[] = "Extensions/Extdate";
   analyzer[] = "Extensions/Extdb2";
   analyzer[] = "Extensions/Extdba";
   analyzer[] = "Extensions/Extdecimal";
   analyzer[] = "Extensions/Extdio";
   analyzer[] = "Extensions/Extdom";
   analyzer[] = "Extensions/Extds";
   analyzer[] = "Extensions/Exteaccelerator";
   analyzer[] = "Extensions/Exteio";
   analyzer[] = "Extensions/Extenchant";
   analyzer[] = "Extensions/Extereg";
   analyzer[] = "Extensions/Extev";
   analyzer[] = "Extensions/Extevent";
   analyzer[] = "Extensions/Extexif";
   analyzer[] = "Extensions/Extexpect";
   analyzer[] = "Extensions/Extfam";
   analyzer[] = "Extensions/Extfann";
   analyzer[] = "Extensions/Extfdf";
   analyzer[] = "Extensions/Extffi";
   analyzer[] = "Extensions/Extffmpeg";
   analyzer[] = "Extensions/Extfile";
   analyzer[] = "Extensions/Extfileinfo";
   analyzer[] = "Extensions/Extfilter";
   analyzer[] = "Extensions/Extfpm";
   analyzer[] = "Extensions/Extftp";
   analyzer[] = "Extensions/Extgd";
   analyzer[] = "Extensions/Extgearman";
   analyzer[] = "Extensions/Extgender";
   analyzer[] = "Extensions/Extgeoip";
   analyzer[] = "Extensions/Extgettext";
   analyzer[] = "Extensions/Extgmagick";
   analyzer[] = "Extensions/Extgmp";
   analyzer[] = "Extensions/Extgnupg";
   analyzer[] = "Extensions/Extgrpc";
   analyzer[] = "Extensions/Exthash";
   analyzer[] = "Extensions/Exthrtime";
   analyzer[] = "Extensions/Exthttp";
   analyzer[] = "Extensions/Extibase";
   analyzer[] = "Extensions/Exticonv";
   analyzer[] = "Extensions/Extigbinary";
   analyzer[] = "Extensions/Extiis";
   analyzer[] = "Extensions/Extimagick";
   analyzer[] = "Extensions/Extimap";
   analyzer[] = "Extensions/Extinfo";
   analyzer[] = "Extensions/Extinotify";
   analyzer[] = "Extensions/Extintl";
   analyzer[] = "Extensions/Extjson";
   analyzer[] = "Extensions/Extjudy";
   analyzer[] = "Extensions/Extkdm5";
   analyzer[] = "Extensions/Extlapack";
   analyzer[] = "Extensions/Extldap";
   analyzer[] = "Extensions/Extleveldb";
   analyzer[] = "Extensions/Extlibevent";
   analyzer[] = "Extensions/Extlibsodium";
   analyzer[] = "Extensions/Extlibxml";
   analyzer[] = "Extensions/Extlua";
   analyzer[] = "Extensions/Extlzf";
   analyzer[] = "Extensions/Extmail";
   analyzer[] = "Extensions/Extmailparse";
   analyzer[] = "Extensions/Extmath";
   analyzer[] = "Extensions/Extmbstring";
   analyzer[] = "Extensions/Extmcrypt";
   analyzer[] = "Extensions/Extmemcache";
   analyzer[] = "Extensions/Extmemcached";
   analyzer[] = "Extensions/Extmhash";
   analyzer[] = "Extensions/Extming";
   analyzer[] = "Extensions/Extmongo";
   analyzer[] = "Extensions/Extmongodb";
   analyzer[] = "Extensions/Extmsgpack";
   analyzer[] = "Extensions/Extmssql";
   analyzer[] = "Extensions/Extmysql";
   analyzer[] = "Extensions/Extmysqli";
   analyzer[] = "Extensions/Extncurses";
   analyzer[] = "Extensions/Extnewt";
   analyzer[] = "Extensions/Extnsapi";
   analyzer[] = "Extensions/Extob";
   analyzer[] = "Extensions/Extoci8";
   analyzer[] = "Extensions/Extodbc";
   analyzer[] = "Extensions/Extopcache";
   analyzer[] = "Extensions/Extopencensus";
   analyzer[] = "Extensions/Extopenssl";
   analyzer[] = "Extensions/Extparle";
   analyzer[] = "Extensions/Extparsekit";
   analyzer[] = "Extensions/Extpassword";
   analyzer[] = "Extensions/Extpcntl";
   analyzer[] = "Extensions/Extpcov";
   analyzer[] = "Extensions/Extpcre";
   analyzer[] = "Extensions/Extpdo";
   analyzer[] = "Extensions/Extpgsql";
   analyzer[] = "Extensions/Extphalcon";
   analyzer[] = "Extensions/Extphar";
   analyzer[] = "Extensions/Extposix";
   analyzer[] = "Extensions/Extproctitle";
   analyzer[] = "Extensions/Extpspell";
   analyzer[] = "Extensions/Extpsr";
   analyzer[] = "Extensions/Extrar";
   analyzer[] = "Extensions/Extrdkafka";
   analyzer[] = "Extensions/Extreadline";
   analyzer[] = "Extensions/Extrecode";
   analyzer[] = "Extensions/Extredis";
   analyzer[] = "Extensions/Extreflection";
   analyzer[] = "Extensions/Extrunkit";
   analyzer[] = "Extensions/Extsdl";
   analyzer[] = "Extensions/Extseaslog";
   analyzer[] = "Extensions/Extsem";
   analyzer[] = "Extensions/Extsession";
   analyzer[] = "Extensions/Extshmop";
   analyzer[] = "Extensions/Extsimplexml";
   analyzer[] = "Extensions/Extsnmp";
   analyzer[] = "Extensions/Extsoap";
   analyzer[] = "Extensions/Extsockets";
   analyzer[] = "Extensions/Extsphinx";
   analyzer[] = "Extensions/Extspl";
   analyzer[] = "Extensions/Extsqlite";
   analyzer[] = "Extensions/Extsqlite3";
   analyzer[] = "Extensions/Extsqlsrv";
   analyzer[] = "Extensions/Extssh2";
   analyzer[] = "Extensions/Extstandard";
   analyzer[] = "Extensions/Extstats";
   analyzer[] = "Extensions/Extstring";
   analyzer[] = "Extensions/Extsuhosin";
   analyzer[] = "Extensions/Extsvm";
   analyzer[] = "Extensions/Extswoole";
   analyzer[] = "Extensions/Exttidy";
   analyzer[] = "Extensions/Exttokenizer";
   analyzer[] = "Extensions/Exttokyotyrant";
   analyzer[] = "Extensions/Exttrader";
   analyzer[] = "Extensions/Extuopz";
   analyzer[] = "Extensions/Extuuid";
   analyzer[] = "Extensions/Extv8js";
   analyzer[] = "Extensions/Extvarnish";
   analyzer[] = "Extensions/Extvips";
   analyzer[] = "Extensions/Extwasm";
   analyzer[] = "Extensions/Extwddx";
   analyzer[] = "Extensions/Extweakref";
   analyzer[] = "Extensions/Extwikidiff2";
   analyzer[] = "Extensions/Extwincache";
   analyzer[] = "Extensions/Extxattr";
   analyzer[] = "Extensions/Extxcache";
   analyzer[] = "Extensions/Extxdebug";
   analyzer[] = "Extensions/Extxdiff";
   analyzer[] = "Extensions/Extxhprof";
   analyzer[] = "Extensions/Extxml";
   analyzer[] = "Extensions/Extxmlreader";
   analyzer[] = "Extensions/Extxmlrpc";
   analyzer[] = "Extensions/Extxmlwriter";
   analyzer[] = "Extensions/Extxsl";
   analyzer[] = "Extensions/Extxxtea";
   analyzer[] = "Extensions/Extyaml";
   analyzer[] = "Extensions/Extyis";
   analyzer[] = "Extensions/Extzbarcode";
   analyzer[] = "Extensions/Extzendmonitor";
   analyzer[] = "Extensions/Extzip";
   analyzer[] = "Extensions/Extzlib";
   analyzer[] = "Extensions/Extzmq";
   analyzer[] = "Extensions/Extzookeeper";
   analyzer[] = "Files/IsCliScript";
   analyzer[] = "Files/NotDefinitionsOnly";
   analyzer[] = "Functions/Closures";
   analyzer[] = "Functions/ConditionedFunctions";
   analyzer[] = "Functions/DeepDefinitions";
   analyzer[] = "Functions/Dynamiccall";
   analyzer[] = "Functions/FallbackFunction";
   analyzer[] = "Functions/Functionnames";
   analyzer[] = "Functions/FunctionsUsingReference";
   analyzer[] = "Functions/IsGenerator";
   analyzer[] = "Functions/MarkCallable";
   analyzer[] = "Functions/MultipleDeclarations";
   analyzer[] = "Functions/Recursive";
   analyzer[] = "Functions/RedeclaredPhpFunction";
   analyzer[] = "Functions/Typehints";
   analyzer[] = "Functions/UseArrowFunctions";
   analyzer[] = "Functions/VariableArguments";
   analyzer[] = "Interfaces/Interfacenames";
   analyzer[] = "Namespaces/Alias";
   analyzer[] = "Namespaces/NamespaceUsage";
   analyzer[] = "Namespaces/Namespacesnames";
   analyzer[] = "Patterns/CourrierAntiPattern";
   analyzer[] = "Patterns/DependencyInjection";
   analyzer[] = "Patterns/Factory";
   analyzer[] = "Php/AlternativeSyntax";
   analyzer[] = "Php/Argon2Usage";
   analyzer[] = "Php/AssertionUsage";
   analyzer[] = "Php/AutoloadUsage";
   analyzer[] = "Php/CastingUsage";
   analyzer[] = "Php/Coalesce";
   analyzer[] = "Php/CryptoUsage";
   analyzer[] = "Php/DeclareEncoding";
   analyzer[] = "Php/DeclareStrict";
   analyzer[] = "Php/DeclareStrictType";
   analyzer[] = "Php/DeclareTicks";
   analyzer[] = "Php/DirectivesUsage";
   analyzer[] = "Php/DlUsage";
   analyzer[] = "Php/EchoTagUsage";
   analyzer[] = "Php/EllipsisUsage";
   analyzer[] = "Php/ErrorLogUsage";
   analyzer[] = "Php/FinalConstant";
   analyzer[] = "Php/Gotonames";
   analyzer[] = "Php/GroupUseDeclaration";
   analyzer[] = "Php/Haltcompiler";
   analyzer[] = "Php/Incompilable";
   analyzer[] = "Php/IntegerSeparatorUsage";
   analyzer[] = "Php/IsINF";
   analyzer[] = "Php/IsNAN";
   analyzer[] = "Php/Labelnames";
   analyzer[] = "Php/ListShortSyntax";
   analyzer[] = "Php/ListWithKeys";
   analyzer[] = "Php/MiddleVersion";
   analyzer[] = "Php/NestedTernaryWithoutParenthesis";
   analyzer[] = "Php/NeverTypehintUsage";
   analyzer[] = "Php/OveriddenFunction";
   analyzer[] = "Php/PearUsage";
   analyzer[] = "Php/Php7RelaxedKeyword";
   analyzer[] = "Php/Php80OnlyTypeHints";
   analyzer[] = "Php/Php80UnionTypehint";
   analyzer[] = "Php/Php80VariableSyntax";
   analyzer[] = "Php/RawPostDataUsage";
   analyzer[] = "Php/ReturnTypehintUsage";
   analyzer[] = "Php/ScalarTypehintUsage";
   analyzer[] = "Php/SpreadOperatorForArray";
   analyzer[] = "Php/SuperGlobalUsage";
   analyzer[] = "Php/ThrowUsage";
   analyzer[] = "Php/TrailingComma";
   analyzer[] = "Php/TriggerErrorUsage";
   analyzer[] = "Php/TryCatchUsage";
   analyzer[] = "Php/TryMultipleCatch";
   analyzer[] = "Php/TypedPropertyUsage";
   analyzer[] = "Php/UseAttributes";
   analyzer[] = "Php/UseBrowscap";
   analyzer[] = "Php/UseCli";
   analyzer[] = "Php/UseContravariance";
   analyzer[] = "Php/UseCookies";
   analyzer[] = "Php/UseCovariance";
   analyzer[] = "Php/UseNullSafeOperator";
   analyzer[] = "Php/UseNullableType";
   analyzer[] = "Php/UseTrailingUseComma";
   analyzer[] = "Php/UseWeb";
   analyzer[] = "Php/UsesEnv";
   analyzer[] = "Php/YieldFromUsage";
   analyzer[] = "Php/YieldUsage";
   analyzer[] = "Psr/Psr11Usage";
   analyzer[] = "Psr/Psr13Usage";
   analyzer[] = "Psr/Psr16Usage";
   analyzer[] = "Psr/Psr3Usage";
   analyzer[] = "Psr/Psr6Usage";
   analyzer[] = "Psr/Psr7Usage";
   analyzer[] = "Security/CantDisableClass";
   analyzer[] = "Security/CantDisableFunction";
   analyzer[] = "Structures/ComplexExpression";
   analyzer[] = "Structures/ConstDefineFavorite";
   analyzer[] = "Structures/ConstantScalarExpression";
   analyzer[] = "Structures/DereferencingAS";
   analyzer[] = "Structures/DynamicCalls";
   analyzer[] = "Structures/DynamicCode";
   analyzer[] = "Structures/ElseUsage";
   analyzer[] = "Structures/ErrorMessages";
   analyzer[] = "Structures/EvalUsage";
   analyzer[] = "Structures/ExitUsage";
   analyzer[] = "Structures/FileUploadUsage";
   analyzer[] = "Structures/FileUsage";
   analyzer[] = "Structures/FunctionSubscripting";
   analyzer[] = "Structures/GlobalInGlobal";
   analyzer[] = "Structures/GlobalUsage";
   analyzer[] = "Structures/IncludeUsage";
   analyzer[] = "Structures/MailUsage";
   analyzer[] = "Structures/MultipleCatch";
   analyzer[] = "Structures/NestedLoops";
   analyzer[] = "Structures/NoDirectAccess";
   analyzer[] = "Structures/NonBreakableSpaceInNames";
   analyzer[] = "Structures/Noscream";
   analyzer[] = "Structures/OnceUsage";
   analyzer[] = "Structures/ResourcesUsage";
   analyzer[] = "Structures/ShellUsage";
   analyzer[] = "Structures/ShortTags";
   analyzer[] = "Structures/TryFinally";
   analyzer[] = "Structures/UseDebug";
   analyzer[] = "Traits/Php";
   analyzer[] = "Traits/TraitUsage";
   analyzer[] = "Traits/Traitnames";
   analyzer[] = "Type/ArrayIndex";
   analyzer[] = "Type/Binary";
   analyzer[] = "Type/Email";
   analyzer[] = "Type/GPCIndex";
   analyzer[] = "Type/Heredoc";
   analyzer[] = "Type/Hexadecimal";
   analyzer[] = "Type/Md5String";
   analyzer[] = "Type/Nowdoc";
   analyzer[] = "Type/Octal";
   analyzer[] = "Type/Pack";
   analyzer[] = "Type/Path";
   analyzer[] = "Type/Printf";
   analyzer[] = "Type/Protocols";
   analyzer[] = "Type/Regex";
   analyzer[] = "Type/Shellcommands";
   analyzer[] = "Type/Sql";
   analyzer[] = "Type/Url";
   analyzer[] = "Variables/References";
   analyzer[] = "Variables/StaticVariables";
   analyzer[] = "Variables/UncommonEnvVar";
   analyzer[] = "Variables/VariableLong";
   analyzer[] = "Variables/VariableVariables";
   analyzer[] = "Vendors/Codeigniter";
   analyzer[] = "Vendors/Concrete5";
   analyzer[] = "Vendors/Drupal";
   analyzer[] = "Vendors/Ez";
   analyzer[] = "Vendors/Fuel";
   analyzer[] = "Vendors/Joomla";
   analyzer[] = "Vendors/Laravel";
   analyzer[] = "Vendors/Phalcon";
   analyzer[] = "Vendors/Symfony";
   analyzer[] = "Vendors/Typo3";
   analyzer[] = "Vendors/Wordpress";
   analyzer[] = "Vendors/Yii";
.. _annex-yaml-appinfo:

Appinfo for .exakat.yaml
________________________


YAML configuration for built-in rulesets. Copy them in your code, with the name .exakat.yaml, and edit them to your owns.
::

  rulesets:
    'Appinfo':
     - 'Arrays/ArrayNSUsage'
     - 'Arrays/Arrayindex'
     - 'Arrays/Multidimensional'
     - 'Arrays/Phparrayindex'
     - 'Arrays/WithCallback'
     - 'Classes/Abstractclass'
     - 'Classes/Abstractmethods'
     - 'Classes/Anonymous'
     - 'Classes/ClassAliasUsage'
     - 'Classes/ClassOverreach'
     - 'Classes/Classnames'
     - 'Classes/CloningUsage'
     - 'Classes/ConstVisibilityUsage'
     - 'Classes/ConstantDefinition'
     - 'Classes/DynamicClass'
     - 'Classes/DynamicConstantCall'
     - 'Classes/DynamicMethodCall'
     - 'Classes/DynamicNew'
     - 'Classes/DynamicPropertyCall'
     - 'Classes/ImmutableSignature'
     - 'Classes/MagicMethod'
     - 'Classes/MultipleClassesInFile'
     - 'Classes/OldStyleConstructor'
     - 'Classes/OverwrittenConst'
     - 'Classes/RedefinedMethods'
     - 'Classes/StaticMethods'
     - 'Classes/StaticProperties'
     - 'Classes/TestClass'
     - 'Classes/VariableClasses'
     - 'Composer/Autoload'
     - 'Composer/IsComposerNsname'
     - 'Composer/UseComposer'
     - 'Composer/UseComposerLock'
     - 'Constants/CaseInsensitiveConstants'
     - 'Constants/ConditionedConstants'
     - 'Constants/ConstantUsage'
     - 'Constants/DynamicCreation'
     - 'Constants/MagicConstantUsage'
     - 'Constants/PhpConstantUsage'
     - 'Constants/VariableConstant'
     - 'Dump/ParameterArgumentsLinks'
     - 'Exceptions/DefinedExceptions'
     - 'Exceptions/MultipleCatch'
     - 'Exceptions/ThrownExceptions'
     - 'Extensions/Extamqp'
     - 'Extensions/Extapache'
     - 'Extensions/Extapc'
     - 'Extensions/Extapcu'
     - 'Extensions/Extarray'
     - 'Extensions/Extast'
     - 'Extensions/Extasync'
     - 'Extensions/Extbcmath'
     - 'Extensions/Extbzip2'
     - 'Extensions/Extcairo'
     - 'Extensions/Extcalendar'
     - 'Extensions/Extcmark'
     - 'Extensions/Extcom'
     - 'Extensions/Extcrypto'
     - 'Extensions/Extcsprng'
     - 'Extensions/Extctype'
     - 'Extensions/Extcurl'
     - 'Extensions/Extcyrus'
     - 'Extensions/Extdate'
     - 'Extensions/Extdb2'
     - 'Extensions/Extdba'
     - 'Extensions/Extdecimal'
     - 'Extensions/Extdio'
     - 'Extensions/Extdom'
     - 'Extensions/Extds'
     - 'Extensions/Exteaccelerator'
     - 'Extensions/Exteio'
     - 'Extensions/Extenchant'
     - 'Extensions/Extereg'
     - 'Extensions/Extev'
     - 'Extensions/Extevent'
     - 'Extensions/Extexif'
     - 'Extensions/Extexpect'
     - 'Extensions/Extfam'
     - 'Extensions/Extfann'
     - 'Extensions/Extfdf'
     - 'Extensions/Extffi'
     - 'Extensions/Extffmpeg'
     - 'Extensions/Extfile'
     - 'Extensions/Extfileinfo'
     - 'Extensions/Extfilter'
     - 'Extensions/Extfpm'
     - 'Extensions/Extftp'
     - 'Extensions/Extgd'
     - 'Extensions/Extgearman'
     - 'Extensions/Extgender'
     - 'Extensions/Extgeoip'
     - 'Extensions/Extgettext'
     - 'Extensions/Extgmagick'
     - 'Extensions/Extgmp'
     - 'Extensions/Extgnupg'
     - 'Extensions/Extgrpc'
     - 'Extensions/Exthash'
     - 'Extensions/Exthrtime'
     - 'Extensions/Exthttp'
     - 'Extensions/Extibase'
     - 'Extensions/Exticonv'
     - 'Extensions/Extigbinary'
     - 'Extensions/Extiis'
     - 'Extensions/Extimagick'
     - 'Extensions/Extimap'
     - 'Extensions/Extinfo'
     - 'Extensions/Extinotify'
     - 'Extensions/Extintl'
     - 'Extensions/Extjson'
     - 'Extensions/Extjudy'
     - 'Extensions/Extkdm5'
     - 'Extensions/Extlapack'
     - 'Extensions/Extldap'
     - 'Extensions/Extleveldb'
     - 'Extensions/Extlibevent'
     - 'Extensions/Extlibsodium'
     - 'Extensions/Extlibxml'
     - 'Extensions/Extlua'
     - 'Extensions/Extlzf'
     - 'Extensions/Extmail'
     - 'Extensions/Extmailparse'
     - 'Extensions/Extmath'
     - 'Extensions/Extmbstring'
     - 'Extensions/Extmcrypt'
     - 'Extensions/Extmemcache'
     - 'Extensions/Extmemcached'
     - 'Extensions/Extmhash'
     - 'Extensions/Extming'
     - 'Extensions/Extmongo'
     - 'Extensions/Extmongodb'
     - 'Extensions/Extmsgpack'
     - 'Extensions/Extmssql'
     - 'Extensions/Extmysql'
     - 'Extensions/Extmysqli'
     - 'Extensions/Extncurses'
     - 'Extensions/Extnewt'
     - 'Extensions/Extnsapi'
     - 'Extensions/Extob'
     - 'Extensions/Extoci8'
     - 'Extensions/Extodbc'
     - 'Extensions/Extopcache'
     - 'Extensions/Extopencensus'
     - 'Extensions/Extopenssl'
     - 'Extensions/Extparle'
     - 'Extensions/Extparsekit'
     - 'Extensions/Extpassword'
     - 'Extensions/Extpcntl'
     - 'Extensions/Extpcov'
     - 'Extensions/Extpcre'
     - 'Extensions/Extpdo'
     - 'Extensions/Extpgsql'
     - 'Extensions/Extphalcon'
     - 'Extensions/Extphar'
     - 'Extensions/Extposix'
     - 'Extensions/Extproctitle'
     - 'Extensions/Extpspell'
     - 'Extensions/Extpsr'
     - 'Extensions/Extrar'
     - 'Extensions/Extrdkafka'
     - 'Extensions/Extreadline'
     - 'Extensions/Extrecode'
     - 'Extensions/Extredis'
     - 'Extensions/Extreflection'
     - 'Extensions/Extrunkit'
     - 'Extensions/Extsdl'
     - 'Extensions/Extseaslog'
     - 'Extensions/Extsem'
     - 'Extensions/Extsession'
     - 'Extensions/Extshmop'
     - 'Extensions/Extsimplexml'
     - 'Extensions/Extsnmp'
     - 'Extensions/Extsoap'
     - 'Extensions/Extsockets'
     - 'Extensions/Extsphinx'
     - 'Extensions/Extspl'
     - 'Extensions/Extsqlite'
     - 'Extensions/Extsqlite3'
     - 'Extensions/Extsqlsrv'
     - 'Extensions/Extssh2'
     - 'Extensions/Extstandard'
     - 'Extensions/Extstats'
     - 'Extensions/Extstring'
     - 'Extensions/Extsuhosin'
     - 'Extensions/Extsvm'
     - 'Extensions/Extswoole'
     - 'Extensions/Exttidy'
     - 'Extensions/Exttokenizer'
     - 'Extensions/Exttokyotyrant'
     - 'Extensions/Exttrader'
     - 'Extensions/Extuopz'
     - 'Extensions/Extuuid'
     - 'Extensions/Extv8js'
     - 'Extensions/Extvarnish'
     - 'Extensions/Extvips'
     - 'Extensions/Extwasm'
     - 'Extensions/Extwddx'
     - 'Extensions/Extweakref'
     - 'Extensions/Extwikidiff2'
     - 'Extensions/Extwincache'
     - 'Extensions/Extxattr'
     - 'Extensions/Extxcache'
     - 'Extensions/Extxdebug'
     - 'Extensions/Extxdiff'
     - 'Extensions/Extxhprof'
     - 'Extensions/Extxml'
     - 'Extensions/Extxmlreader'
     - 'Extensions/Extxmlrpc'
     - 'Extensions/Extxmlwriter'
     - 'Extensions/Extxsl'
     - 'Extensions/Extxxtea'
     - 'Extensions/Extyaml'
     - 'Extensions/Extyis'
     - 'Extensions/Extzbarcode'
     - 'Extensions/Extzendmonitor'
     - 'Extensions/Extzip'
     - 'Extensions/Extzlib'
     - 'Extensions/Extzmq'
     - 'Extensions/Extzookeeper'
     - 'Files/IsCliScript'
     - 'Files/NotDefinitionsOnly'
     - 'Functions/Closures'
     - 'Functions/ConditionedFunctions'
     - 'Functions/DeepDefinitions'
     - 'Functions/Dynamiccall'
     - 'Functions/FallbackFunction'
     - 'Functions/Functionnames'
     - 'Functions/FunctionsUsingReference'
     - 'Functions/IsGenerator'
     - 'Functions/MarkCallable'
     - 'Functions/MultipleDeclarations'
     - 'Functions/Recursive'
     - 'Functions/RedeclaredPhpFunction'
     - 'Functions/Typehints'
     - 'Functions/UseArrowFunctions'
     - 'Functions/VariableArguments'
     - 'Interfaces/Interfacenames'
     - 'Namespaces/Alias'
     - 'Namespaces/NamespaceUsage'
     - 'Namespaces/Namespacesnames'
     - 'Patterns/CourrierAntiPattern'
     - 'Patterns/DependencyInjection'
     - 'Patterns/Factory'
     - 'Php/AlternativeSyntax'
     - 'Php/Argon2Usage'
     - 'Php/AssertionUsage'
     - 'Php/AutoloadUsage'
     - 'Php/CastingUsage'
     - 'Php/Coalesce'
     - 'Php/CryptoUsage'
     - 'Php/DeclareEncoding'
     - 'Php/DeclareStrict'
     - 'Php/DeclareStrictType'
     - 'Php/DeclareTicks'
     - 'Php/DirectivesUsage'
     - 'Php/DlUsage'
     - 'Php/EchoTagUsage'
     - 'Php/EllipsisUsage'
     - 'Php/ErrorLogUsage'
     - 'Php/FinalConstant'
     - 'Php/Gotonames'
     - 'Php/GroupUseDeclaration'
     - 'Php/Haltcompiler'
     - 'Php/Incompilable'
     - 'Php/IntegerSeparatorUsage'
     - 'Php/IsINF'
     - 'Php/IsNAN'
     - 'Php/Labelnames'
     - 'Php/ListShortSyntax'
     - 'Php/ListWithKeys'
     - 'Php/MiddleVersion'
     - 'Php/NestedTernaryWithoutParenthesis'
     - 'Php/NeverTypehintUsage'
     - 'Php/OveriddenFunction'
     - 'Php/PearUsage'
     - 'Php/Php7RelaxedKeyword'
     - 'Php/Php80OnlyTypeHints'
     - 'Php/Php80UnionTypehint'
     - 'Php/Php80VariableSyntax'
     - 'Php/RawPostDataUsage'
     - 'Php/ReturnTypehintUsage'
     - 'Php/ScalarTypehintUsage'
     - 'Php/SpreadOperatorForArray'
     - 'Php/SuperGlobalUsage'
     - 'Php/ThrowUsage'
     - 'Php/TrailingComma'
     - 'Php/TriggerErrorUsage'
     - 'Php/TryCatchUsage'
     - 'Php/TryMultipleCatch'
     - 'Php/TypedPropertyUsage'
     - 'Php/UseAttributes'
     - 'Php/UseBrowscap'
     - 'Php/UseCli'
     - 'Php/UseContravariance'
     - 'Php/UseCookies'
     - 'Php/UseCovariance'
     - 'Php/UseNullSafeOperator'
     - 'Php/UseNullableType'
     - 'Php/UseTrailingUseComma'
     - 'Php/UseWeb'
     - 'Php/UsesEnv'
     - 'Php/YieldFromUsage'
     - 'Php/YieldUsage'
     - 'Psr/Psr11Usage'
     - 'Psr/Psr13Usage'
     - 'Psr/Psr16Usage'
     - 'Psr/Psr3Usage'
     - 'Psr/Psr6Usage'
     - 'Psr/Psr7Usage'
     - 'Security/CantDisableClass'
     - 'Security/CantDisableFunction'
     - 'Structures/ComplexExpression'
     - 'Structures/ConstDefineFavorite'
     - 'Structures/ConstantScalarExpression'
     - 'Structures/DereferencingAS'
     - 'Structures/DynamicCalls'
     - 'Structures/DynamicCode'
     - 'Structures/ElseUsage'
     - 'Structures/ErrorMessages'
     - 'Structures/EvalUsage'
     - 'Structures/ExitUsage'
     - 'Structures/FileUploadUsage'
     - 'Structures/FileUsage'
     - 'Structures/FunctionSubscripting'
     - 'Structures/GlobalInGlobal'
     - 'Structures/GlobalUsage'
     - 'Structures/IncludeUsage'
     - 'Structures/MailUsage'
     - 'Structures/MultipleCatch'
     - 'Structures/NestedLoops'
     - 'Structures/NoDirectAccess'
     - 'Structures/NonBreakableSpaceInNames'
     - 'Structures/Noscream'
     - 'Structures/OnceUsage'
     - 'Structures/ResourcesUsage'
     - 'Structures/ShellUsage'
     - 'Structures/ShortTags'
     - 'Structures/TryFinally'
     - 'Structures/UseDebug'
     - 'Traits/Php'
     - 'Traits/TraitUsage'
     - 'Traits/Traitnames'
     - 'Type/ArrayIndex'
     - 'Type/Binary'
     - 'Type/Email'
     - 'Type/GPCIndex'
     - 'Type/Heredoc'
     - 'Type/Hexadecimal'
     - 'Type/Md5String'
     - 'Type/Nowdoc'
     - 'Type/Octal'
     - 'Type/Pack'
     - 'Type/Path'
     - 'Type/Printf'
     - 'Type/Protocols'
     - 'Type/Regex'
     - 'Type/Shellcommands'
     - 'Type/Sql'
     - 'Type/Url'
     - 'Variables/References'
     - 'Variables/StaticVariables'
     - 'Variables/UncommonEnvVar'
     - 'Variables/VariableLong'
     - 'Variables/VariableVariables'
     - 'Vendors/Codeigniter'
     - 'Vendors/Concrete5'
     - 'Vendors/Drupal'
     - 'Vendors/Ez'
     - 'Vendors/Fuel'
     - 'Vendors/Joomla'
     - 'Vendors/Laravel'
     - 'Vendors/Phalcon'
     - 'Vendors/Symfony'
     - 'Vendors/Typo3'
     - 'Vendors/Wordpress'
     - 'Vendors/Yii'



.. _annex-compatibilityphp74:

CompatibilityPHP74
++++++++++++++++++


.. _annex-ini-compatibilityphp74:

CompatibilityPHP74 for INI
__________________________


INI configuration for built-in rulesets. Copy them in config/rulesets.ini, and edit them to your owns.
::

 [CompatibilityPHP74]
   analyzer[] = "Functions/UnbindingClosures";
   analyzer[] = "Php/ArrayKeyExistsWithObjects";
   analyzer[] = "Php/AvoidGetobjectVars";
   analyzer[] = "Php/ConcatAndAddition";
   analyzer[] = "Php/DetectCurrentClass";
   analyzer[] = "Php/EnumUsage";
   analyzer[] = "Php/FilesFullPath";
   analyzer[] = "Php/FilterToAddSlashes";
   analyzer[] = "Php/FinalConstant";
   analyzer[] = "Php/HashAlgos74";
   analyzer[] = "Php/IdnUts46";
   analyzer[] = "Php/NestedTernaryWithoutParenthesis";
   analyzer[] = "Php/NeverTypehintUsage";
   analyzer[] = "Php/NoMoreCurlyArrays";
   analyzer[] = "Php/PHP80scalartypehints";
   analyzer[] = "Php/PHP81scalartypehints";
   analyzer[] = "Php/Php74Deprecation";
   analyzer[] = "Php/Php74NewClasses";
   analyzer[] = "Php/Php74NewConstants";
   analyzer[] = "Php/Php74NewFunctions";
   analyzer[] = "Php/Php74RemovedDirective";
   analyzer[] = "Php/Php74RemovedFunctions";
   analyzer[] = "Php/Php74ReservedKeyword";
   analyzer[] = "Php/Php74mbstrrpos3rdArg";
   analyzer[] = "Php/Php80NewFunctions";
   analyzer[] = "Php/Php80OnlyTypeHints";
   analyzer[] = "Php/Php80UnionTypehint";
   analyzer[] = "Php/Php80VariableSyntax";
   analyzer[] = "Php/ReflectionExportIsDeprecated";
   analyzer[] = "Php/ScalarAreNotArrays";
   analyzer[] = "Php/SignatureTrailingComma";
   analyzer[] = "Php/ThrowWasAnExpression";
   analyzer[] = "Php/UseMatch";
   analyzer[] = "Structures/CurlVersionNow";
   analyzer[] = "Structures/DontReadAndWriteInOneExpression";
   analyzer[] = "Structures/OpensslRandomPseudoByteSecondArg";
.. _annex-yaml-compatibilityphp74:

CompatibilityPHP74 for .exakat.yaml
___________________________________


YAML configuration for built-in rulesets. Copy them in your code, with the name .exakat.yaml, and edit them to your owns.
::

  rulesets:
    'CompatibilityPHP74':
     - 'Functions/UnbindingClosures'
     - 'Php/ArrayKeyExistsWithObjects'
     - 'Php/AvoidGetobjectVars'
     - 'Php/ConcatAndAddition'
     - 'Php/DetectCurrentClass'
     - 'Php/EnumUsage'
     - 'Php/FilesFullPath'
     - 'Php/FilterToAddSlashes'
     - 'Php/FinalConstant'
     - 'Php/HashAlgos74'
     - 'Php/IdnUts46'
     - 'Php/NestedTernaryWithoutParenthesis'
     - 'Php/NeverTypehintUsage'
     - 'Php/NoMoreCurlyArrays'
     - 'Php/PHP80scalartypehints'
     - 'Php/PHP81scalartypehints'
     - 'Php/Php74Deprecation'
     - 'Php/Php74NewClasses'
     - 'Php/Php74NewConstants'
     - 'Php/Php74NewFunctions'
     - 'Php/Php74RemovedDirective'
     - 'Php/Php74RemovedFunctions'
     - 'Php/Php74ReservedKeyword'
     - 'Php/Php74mbstrrpos3rdArg'
     - 'Php/Php80NewFunctions'
     - 'Php/Php80OnlyTypeHints'
     - 'Php/Php80UnionTypehint'
     - 'Php/Php80VariableSyntax'
     - 'Php/ReflectionExportIsDeprecated'
     - 'Php/ScalarAreNotArrays'
     - 'Php/SignatureTrailingComma'
     - 'Php/ThrowWasAnExpression'
     - 'Php/UseMatch'
     - 'Structures/CurlVersionNow'
     - 'Structures/DontReadAndWriteInOneExpression'
     - 'Structures/OpensslRandomPseudoByteSecondArg'



.. _annex-compatibilityphp80:

CompatibilityPHP80
++++++++++++++++++


.. _annex-ini-compatibilityphp80:

CompatibilityPHP80 for INI
__________________________


INI configuration for built-in rulesets. Copy them in config/rulesets.ini, and edit them to your owns.
::

 [CompatibilityPHP80]
   analyzer[] = "Arrays/NegativeStart";
   analyzer[] = "Classes/FinalPrivate";
   analyzer[] = "Classes/OldStyleConstructor";
   analyzer[] = "Functions/MismatchParameterName";
   analyzer[] = "Functions/NullableWithConstant";
   analyzer[] = "Functions/WrongOptionalParameter";
   analyzer[] = "Php/AvoidGetobjectVars";
   analyzer[] = "Php/CastUnsetUsage";
   analyzer[] = "Php/ConcatAndAddition";
   analyzer[] = "Php/EnumUsage";
   analyzer[] = "Php/FinalConstant";
   analyzer[] = "Php/NeverTypehintUsage";
   analyzer[] = "Php/PHP81scalartypehints";
   analyzer[] = "Php/Php74RemovedDirective";
   analyzer[] = "Php/Php80NamedParameterVariadic";
   analyzer[] = "Php/Php80RemovedConstant";
   analyzer[] = "Php/Php80RemovedDirective";
   analyzer[] = "Php/Php80RemovedFunctions";
   analyzer[] = "Php/Php80RemovesResources";
   analyzer[] = "Php/PhpErrorMsgUsage";
   analyzer[] = "Php/ReservedMatchKeyword";
   analyzer[] = "Structures/ArrayMapPassesByValue";
   analyzer[] = "Structures/UnsupportedTypesWithOperators";
.. _annex-yaml-compatibilityphp80:

CompatibilityPHP80 for .exakat.yaml
___________________________________


YAML configuration for built-in rulesets. Copy them in your code, with the name .exakat.yaml, and edit them to your owns.
::

  rulesets:
    'CompatibilityPHP80':
     - 'Arrays/NegativeStart'
     - 'Classes/FinalPrivate'
     - 'Classes/OldStyleConstructor'
     - 'Functions/MismatchParameterName'
     - 'Functions/NullableWithConstant'
     - 'Functions/WrongOptionalParameter'
     - 'Php/AvoidGetobjectVars'
     - 'Php/CastUnsetUsage'
     - 'Php/ConcatAndAddition'
     - 'Php/EnumUsage'
     - 'Php/FinalConstant'
     - 'Php/NeverTypehintUsage'
     - 'Php/PHP81scalartypehints'
     - 'Php/Php74RemovedDirective'
     - 'Php/Php80NamedParameterVariadic'
     - 'Php/Php80RemovedConstant'
     - 'Php/Php80RemovedDirective'
     - 'Php/Php80RemovedFunctions'
     - 'Php/Php80RemovesResources'
     - 'Php/PhpErrorMsgUsage'
     - 'Php/ReservedMatchKeyword'
     - 'Structures/ArrayMapPassesByValue'
     - 'Structures/UnsupportedTypesWithOperators'



.. _annex-compatibilityphp81:

CompatibilityPHP81
++++++++++++++++++


.. _annex-ini-compatibilityphp81:

CompatibilityPHP81 for INI
__________________________


INI configuration for built-in rulesets. Copy them in config/rulesets.ini, and edit them to your owns.
::

 [CompatibilityPHP81]
   analyzer[] = "Functions/NoReferencedVoid";
   analyzer[] = "Php/CallingStaticTraitMethod";
   analyzer[] = "Php/JsonSerializeReturnType";
   analyzer[] = "Php/NativeClassTypeCompatibility";
   analyzer[] = "Php/NoNullForNative";
   analyzer[] = "Php/OpensslEncryptAlgoChange";
   analyzer[] = "Php/Php74RemovedDirective";
   analyzer[] = "Php/Php80RemovedDirective";
   analyzer[] = "Php/Php81RemovedConstant";
   analyzer[] = "Php/Php81RemovedDirective";
   analyzer[] = "Php/RestrictGlobalUsage";
   analyzer[] = "Variables/InheritedStaticVariable";
.. _annex-yaml-compatibilityphp81:

CompatibilityPHP81 for .exakat.yaml
___________________________________


YAML configuration for built-in rulesets. Copy them in your code, with the name .exakat.yaml, and edit them to your owns.
::

  rulesets:
    'CompatibilityPHP81':
     - 'Functions/NoReferencedVoid'
     - 'Php/CallingStaticTraitMethod'
     - 'Php/JsonSerializeReturnType'
     - 'Php/NativeClassTypeCompatibility'
     - 'Php/NoNullForNative'
     - 'Php/OpensslEncryptAlgoChange'
     - 'Php/Php74RemovedDirective'
     - 'Php/Php80RemovedDirective'
     - 'Php/Php81RemovedConstant'
     - 'Php/Php81RemovedDirective'
     - 'Php/RestrictGlobalUsage'
     - 'Variables/InheritedStaticVariable'



.. _annex-dump:

Dump
++++


.. _annex-ini-dump:

Dump for INI
____________


INI configuration for built-in rulesets. Copy them in config/rulesets.ini, and edit them to your owns.
::

 [Dump]
   analyzer[] = "Dump/CallOrder";
   analyzer[] = "Dump/CollectAtomCounts";
   analyzer[] = "Dump/CollectBlockSize";
   analyzer[] = "Dump/CollectClassChanges";
   analyzer[] = "Dump/CollectClassChildren";
   analyzer[] = "Dump/CollectClassConstantCounts";
   analyzer[] = "Dump/CollectClassDepth";
   analyzer[] = "Dump/CollectClassInterfaceCounts";
   analyzer[] = "Dump/CollectClassTraitsCounts";
   analyzer[] = "Dump/CollectClassesDependencies";
   analyzer[] = "Dump/CollectDefinitionsStats";
   analyzer[] = "Dump/CollectFilesDependencies";
   analyzer[] = "Dump/CollectForeachFavorite";
   analyzer[] = "Dump/CollectGlobalVariables";
   analyzer[] = "Dump/CollectLiterals";
   analyzer[] = "Dump/CollectLocalVariableCounts";
   analyzer[] = "Dump/CollectMbstringEncodings";
   analyzer[] = "Dump/CollectMethodCounts";
   analyzer[] = "Dump/CollectNativeCallsPerExpressions";
   analyzer[] = "Dump/CollectParameterCounts";
   analyzer[] = "Dump/CollectParameterNames";
   analyzer[] = "Dump/CollectPhpStructures";
   analyzer[] = "Dump/CollectPropertyCounts";
   analyzer[] = "Dump/CollectReadability";
   analyzer[] = "Dump/CollectUseCounts";
   analyzer[] = "Dump/CollectVariables";
   analyzer[] = "Dump/ConstantOrder";
   analyzer[] = "Dump/CyclomaticComplexity";
   analyzer[] = "Dump/DereferencingLevels";
   analyzer[] = "Dump/EnvironnementVariables";
   analyzer[] = "Dump/FossilizedMethods";
   analyzer[] = "Dump/Inclusions";
   analyzer[] = "Dump/IndentationLevels";
   analyzer[] = "Dump/NewOrder";
   analyzer[] = "Dump/TypehintingStats";
   analyzer[] = "Dump/Typehintorder";
.. _annex-yaml-dump:

Dump for .exakat.yaml
_____________________


YAML configuration for built-in rulesets. Copy them in your code, with the name .exakat.yaml, and edit them to your owns.
::

  rulesets:
    'Dump':
     - 'Dump/CallOrder'
     - 'Dump/CollectAtomCounts'
     - 'Dump/CollectBlockSize'
     - 'Dump/CollectClassChanges'
     - 'Dump/CollectClassChildren'
     - 'Dump/CollectClassConstantCounts'
     - 'Dump/CollectClassDepth'
     - 'Dump/CollectClassInterfaceCounts'
     - 'Dump/CollectClassTraitsCounts'
     - 'Dump/CollectClassesDependencies'
     - 'Dump/CollectDefinitionsStats'
     - 'Dump/CollectFilesDependencies'
     - 'Dump/CollectForeachFavorite'
     - 'Dump/CollectGlobalVariables'
     - 'Dump/CollectLiterals'
     - 'Dump/CollectLocalVariableCounts'
     - 'Dump/CollectMbstringEncodings'
     - 'Dump/CollectMethodCounts'
     - 'Dump/CollectNativeCallsPerExpressions'
     - 'Dump/CollectParameterCounts'
     - 'Dump/CollectParameterNames'
     - 'Dump/CollectPhpStructures'
     - 'Dump/CollectPropertyCounts'
     - 'Dump/CollectReadability'
     - 'Dump/CollectUseCounts'
     - 'Dump/CollectVariables'
     - 'Dump/ConstantOrder'
     - 'Dump/CyclomaticComplexity'
     - 'Dump/DereferencingLevels'
     - 'Dump/EnvironnementVariables'
     - 'Dump/FossilizedMethods'
     - 'Dump/Inclusions'
     - 'Dump/IndentationLevels'
     - 'Dump/NewOrder'
     - 'Dump/TypehintingStats'
     - 'Dump/Typehintorder'



.. _annex-first:

First
+++++


.. _annex-ini-first:

First for INI
_____________


INI configuration for built-in rulesets. Copy them in config/rulesets.ini, and edit them to your owns.
::

 [First]
   analyzer[] = "Constants/IsExtConstant";
   analyzer[] = "Functions/IsExtFunction";
   analyzer[] = "Functions/MarkCallable";
   analyzer[] = "Interfaces/IsExtInterface";
   analyzer[] = "Traits/IsExtTrait";
.. _annex-yaml-first:

First for .exakat.yaml
______________________


YAML configuration for built-in rulesets. Copy them in your code, with the name .exakat.yaml, and edit them to your owns.
::

  rulesets:
    'First':
     - 'Constants/IsExtConstant'
     - 'Functions/IsExtFunction'
     - 'Functions/MarkCallable'
     - 'Interfaces/IsExtInterface'
     - 'Traits/IsExtTrait'



