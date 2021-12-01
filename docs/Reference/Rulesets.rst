.. _Rulesets:

Rulesets
====================

Introduction
------------------------

Exakat provides unique 653 rules to detect BUGS, CODE SMELLS, SECURITY OR QUALITY ISSUES in your PHP code.

For more smoothly usage, the ruleset concept allow you to run a set of rules based on a decidated focus. Beawre that a Ruleset run all the associated rules and any needed dependencies.

Rulesets are configured with the -T option, when running exakat in command line. For example : 

::

   php exakat.phar analyze -p <project> -T <Security>



Summary
------------------------


Here is the list of the current rulesets supported by Exakat Engine.

+-----------------------------------------------+------------------------------------------------------------------------------------------------------+
|Name                                           | Description                                                                                          |
+-----------------------------------------------+------------------------------------------------------------------------------------------------------+
| :ref:`analyze`                                |Check for common best practices.                                                                      |
+-----------------------------------------------+------------------------------------------------------------------------------------------------------+
| :ref:`appinfo`                                |Appinfo is the equivalent of phpinfo() for your code.                                                 |
+-----------------------------------------------+------------------------------------------------------------------------------------------------------+
| :ref:`compatibilityphp74`                     |List features that are incompatible with PHP 7.4.                                                     |
+-----------------------------------------------+------------------------------------------------------------------------------------------------------+
| :ref:`compatibilityphp80`                     |List features that are incompatible with PHP 8.0.                                                     |
+-----------------------------------------------+------------------------------------------------------------------------------------------------------+
| :ref:`compatibilityphp81`                     |List features that are incompatible with PHP 8.1.                                                     |
+-----------------------------------------------+------------------------------------------------------------------------------------------------------+
| :ref:`dump`                                   |Dump is a collector set of rules.                                                                     |
+-----------------------------------------------+------------------------------------------------------------------------------------------------------+
| :ref:`first`                                  |A set of rules that are always run at the beginning of a project, because they are frenquently used.  |
+-----------------------------------------------+------------------------------------------------------------------------------------------------------+

Note : in command line, don't forget to add quotes to rulesets' names that include white space.

List of rulesets
------------------------

.. _ruleset-analyze:

Analyze
+++++++

This ruleset centralizes a large number of classic trap and pitfalls when writing PHP.

Total : 430 analysis

* :ref:`adding-zero`
* :ref:`No anchor for Arrays/AmbiguousKeys <no-anchor-for-arrays-ambiguouskeys>`
* :ref:`multiple-index-definition`
* :ref:`No anchor for Classes/EmptyClass <no-anchor-for-classes-emptyclass>`
* :ref:`forgotten-visibility`
* :ref:`non-static-methods-called-in-a-static`
* :ref:`old-style-constructor`
* :ref:`static-methods-called-from-object`
* :ref:`constants-with-strange-names`
* :ref:`No anchor for Functions/EmptyFunction <no-anchor-for-functions-emptyfunction>`
* :ref:`redeclared-php-functions`
* :ref:`No anchor for Functions/WithoutReturn <no-anchor-for-functions-withoutreturn>`
* :ref:`No anchor for Interfaces/EmptyInterface <no-anchor-for-interfaces-emptyinterface>`
* :ref:`incompilable-files`
* :ref:`error\_reporting()-with-integers`
* :ref:`eval()-usage`
* :ref:`exit()-usage`
* :ref:`forgotten-whitespace`
* :ref:`No anchor for Structures/Iffectation <no-anchor-for-structures-iffectation>`
* :ref:`multiply-by-one`
* :ref:`@-operator`
* :ref:`not-not`
* :ref:`include\_once()-usage`
* :ref:`strpos()-like-comparison`
* :ref:`throws-an-assignement`
* :ref:`var\_dump()...-usage`
* :ref:`No anchor for Structures/toStringThrowsException <no-anchor-for-structures-tostringthrowsexception>`
* :ref:`No anchor for Variables/VariableNonascii <no-anchor-for-variables-variablenonascii>`
* :ref:`No anchor for Variables/VariableUsedOnce <no-anchor-for-variables-variableusedonce>`
* :ref:`No anchor for Constants/BadConstantnames <no-anchor-for-constants-badconstantnames>`
* :ref:`No anchor for Traits/EmptyTrait <no-anchor-for-traits-emptytrait>`
* :ref:`No anchor for Namespaces/UseWithFullyQualifiedNS <no-anchor-for-namespaces-usewithfullyqualifiedns>`
* :ref:`useless-instructions`
* :ref:`No anchor for Classes/AbstractStatic <no-anchor-for-classes-abstractstatic>`
* :ref:`No anchor for Constants/InvalidName <no-anchor-for-constants-invalidname>`
* :ref:`multiple-constant-definition`
* :ref:`wrong-optional-parameter`
* :ref:`use-===-null`
* :ref:`No anchor for Classes/ThisIsNotAnArray <no-anchor-for-classes-thisisnotanarray>`
* :ref:`one-variable-string`
* :ref:`static-methods-can't-contain-$this`
* :ref:`while(list()-=-each())`
* :ref:`No anchor for Structures/OneLineTwoInstructions <no-anchor-for-structures-onelinetwoinstructions>`
* :ref:`multiples-identical-case`
* :ref:`switch-without-default`
* :ref:`No anchor for Classes/ThisIsForClasses <no-anchor-for-classes-thisisforclasses>`
* :ref:`nested-ternary`
* :ref:`No anchor for Arrays/NonConstantArray <no-anchor-for-arrays-nonconstantarray>`
* :ref:`undefined-constants`
* :ref:`No anchor for Classes/InstantiatingAbstractClass <no-anchor-for-classes-instantiatingabstractclass>`
* :ref:`No anchor for Classes/CitSameName <no-anchor-for-classes-citsamename>`
* :ref:`No anchor for Structures/EmptyTryCatch <no-anchor-for-structures-emptytrycatch>`
* :ref:`No anchor for Classes/UndefinedClasses <no-anchor-for-classes-undefinedclasses>`
* :ref:`htmlentities-calls`
* :ref:`undefined-class-constants`
* :ref:`used-once-variables-(in-scope)`
* :ref:`undefined-functions`
* :ref:`deprecated-php-functions`
* :ref:`dangling-array-references`
* :ref:`No anchor for Structures/QueriesInLoop <no-anchor-for-structures-queriesinloop>`
* :ref:`No anchor for Classes/OldStyleVar <no-anchor-for-classes-oldstylevar>`
* :ref:`aliases-usage`
* :ref:`uses-default-values`
* :ref:`wrong-number-of-arguments`
* :ref:`No anchor for Functions/HardcodedPasswords <no-anchor-for-functions-hardcodedpasswords>`
* :ref:`No anchor for Classes/UnresolvedClasses <no-anchor-for-classes-unresolvedclasses>`
* :ref:`No anchor for Classes/UselessConstructor <no-anchor-for-classes-uselessconstructor>`
* :ref:`No anchor for Classes/ImplementIsForInterface <no-anchor-for-classes-implementisforinterface>`
* :ref:`use-const`
* :ref:`No anchor for Namespaces/UnresolvedUse <no-anchor-for-namespaces-unresolveduse>`
* :ref:`No anchor for Classes/UndefinedParentMP <no-anchor-for-classes-undefinedparentmp>`
* :ref:`No anchor for Classes/UndefinedStaticMP <no-anchor-for-classes-undefinedstaticmp>`
* :ref:`No anchor for Classes/AccessPrivate <no-anchor-for-classes-accessprivate>`
* :ref:`No anchor for Classes/AccessProtected <no-anchor-for-classes-accessprotected>`
* :ref:`No anchor for Classes/PssWithoutClass <no-anchor-for-classes-psswithoutclass>`
* :ref:`list()-may-omit-variables`
* :ref:`or-die`
* :ref:`No anchor for Variables/WrittenOnlyVariable <no-anchor-for-variables-writtenonlyvariable>`
* :ref:`must-return-methods`
* :ref:`No anchor for Structures/EmptyLines <no-anchor-for-structures-emptylines>`
* :ref:`overwritten-exceptions`
* :ref:`foreach-reference-is-not-modified`
* :ref:`No anchor for Structures/NoChangeIncomingVariables <no-anchor-for-structures-nochangeincomingvariables>`
* :ref:`No anchor for Structures/ComparedComparison <no-anchor-for-structures-comparedcomparison>`
* :ref:`No anchor for Functions/UselessReturn <no-anchor-for-functions-uselessreturn>`
* :ref:`No anchor for Classes/UnusedClass <no-anchor-for-classes-unusedclass>`
* :ref:`No anchor for Structures/Unpreprocessed <no-anchor-for-structures-unpreprocessed>`
* :ref:`undefined-properties`
* :ref:`No anchor for Php/ShortOpenTagRequired <no-anchor-for-php-shortopentagrequired>`
* :ref:`strict-comparison-with-booleans`
* :ref:`lone-blocks`
* :ref:`No anchor for Classes/ThisIsNotForStatic <no-anchor-for-classes-thisisnotforstatic>`
* :ref:`global-usage`
* :ref:`No anchor for Php/ReservedNames <no-anchor-for-php-reservednames>`
* :ref:`logical-should-use-symbolic-operators`
* :ref:`No anchor for Classes/ShouldUseSelf <no-anchor-for-classes-shoulduseself>`
* :ref:`No anchor for Structures/CatchShadowsVariable <no-anchor-for-structures-catchshadowsvariable>`
* :ref:`deep-definitions`
* :ref:`repeated-print()`
* :ref:`avoid-parenthesis`
* :ref:`objects-don't-need-references`
* :ref:`No anchor for Variables/LostReferences <no-anchor-for-variables-lostreferences>`
* :ref:`No anchor for Constants/CreatedOutsideItsNamespace <no-anchor-for-constants-createdoutsideitsnamespace>`
* :ref:`No anchor for Namespaces/ConstantFullyQualified <no-anchor-for-namespaces-constantfullyqualified>`
* :ref:`No anchor for Classes/PropertyNeverUsed <no-anchor-for-classes-propertyneverused>`
* :ref:`no-real-comparison`
* :ref:`No anchor for Classes/ShouldUseThis <no-anchor-for-classes-shouldusethis>`
* :ref:`no-direct-call-to-magic-method`
* :ref:`No anchor for Type/StringHoldAVariable <no-anchor-for-type-stringholdavariable>`
* :ref:`No anchor for Structures/EchoWithConcat <no-anchor-for-structures-echowithconcat>`
* :ref:`No anchor for Structures/UnusedGlobal <no-anchor-for-structures-unusedglobal>`
* :ref:`No anchor for Structures/UselessGlobal <no-anchor-for-structures-uselessglobal>`
* :ref:`No anchor for Structures/ShouldPreprocess <no-anchor-for-structures-shouldpreprocess>`
* :ref:`useless-final`
* :ref:`use-constant`
* :ref:`useless-unset`
* :ref:`No anchor for Structures/BuriedAssignation <no-anchor-for-structures-buriedassignation>`
* :ref:`no-array\_merge()-in-loops`
* :ref:`useless-parenthesis`
* :ref:`No anchor for Classes/UnresolvedInstanceof <no-anchor-for-classes-unresolvedinstanceof>`
* :ref:`use-php-object-api`
* :ref:`No anchor for Exceptions/Unthrown <no-anchor-for-exceptions-unthrown>`
* :ref:`No anchor for Php/oldAutoloadUsage <no-anchor-for-php-oldautoloadusage>`
* :ref:`altering-foreach-without-reference`
* :ref:`use-pathinfo`
* :ref:`No anchor for Functions/ShouldUseConstants <no-anchor-for-functions-shoulduseconstants>`
* :ref:`No anchor for Php/HashAlgos <no-anchor-for-php-hashalgos>`
* :ref:`no-parenthesis-for-language-construct`
* :ref:`No anchor for Structures/NoHardcodedPath <no-anchor-for-structures-nohardcodedpath>`
* :ref:`No anchor for Structures/NoHardcodedPort <no-anchor-for-structures-nohardcodedport>`
* :ref:`use-constant-as-arguments`
* :ref:`implied-if`
* :ref:`No anchor for Variables/OverwrittenLiterals <no-anchor-for-variables-overwrittenliterals>`
* :ref:`No anchor for Classes/MakeDefault <no-anchor-for-classes-makedefault>`
* :ref:`No anchor for Classes/NoPublicAccess <no-anchor-for-classes-nopublicaccess>`
* :ref:`should-chain-exception`
* :ref:`No anchor for Interfaces/UselessInterfaces <no-anchor-for-interfaces-uselessinterfaces>`
* :ref:`undefined-interfaces`
* :ref:`No anchor for Interfaces/ConcreteVisibility <no-anchor-for-interfaces-concretevisibility>`
* :ref:`No anchor for Structures/DoubleInstruction <no-anchor-for-structures-doubleinstruction>`
* :ref:`should-use-prepared-statement`
* :ref:`print-and-die`
* :ref:`unchecked-resources`
* :ref:`No anchor for Structures/NoHardcodedIp <no-anchor-for-structures-nohardcodedip>`
* :ref:`else-if-versus-elseif`
* :ref:`No anchor for Structures/UnsetInForeach <no-anchor-for-structures-unsetinforeach>`
* :ref:`No anchor for Structures/CouldBeStatic <no-anchor-for-structures-couldbestatic>`
* :ref:`multiple-class-declarations`
* :ref:`empty-namespace`
* :ref:`could-use-short-assignation`
* :ref:`No anchor for Classes/UselessAbstract <no-anchor-for-classes-uselessabstract>`
* :ref:`No anchor for Structures/StaticLoop <no-anchor-for-structures-staticloop>`
* :ref:`pre-increment`
* :ref:`No anchor for Structures/OnlyVariableReturnedByReference <no-anchor-for-structures-onlyvariablereturnedbyreference>`
* :ref:`indices-are-int-or-string`
* :ref:`should-typecast`
* :ref:`No anchor for Classes/NoSelfReferencingConstant <no-anchor-for-classes-noselfreferencingconstant>`
* :ref:`No anchor for Structures/NoDirectUsage <no-anchor-for-structures-nodirectusage>`
* :ref:`No anchor for Structures/BreakOutsideLoop <no-anchor-for-structures-breakoutsideloop>`
* :ref:`avoid-substr()-one`
* :ref:`No anchor for Structures/DoubleAssignation <no-anchor-for-structures-doubleassignation>`
* :ref:`No anchor for Php/EmptyList <no-anchor-for-php-emptylist>`
* :ref:`useless-brackets`
* :ref:`preg\_replace-with-option-e`
* :ref:`eval()-without-try`
* :ref:`No anchor for Functions/RelayFunction <no-anchor-for-functions-relayfunction>`
* :ref:`No anchor for Functions/funcGetArgModified <no-anchor-for-functions-funcgetargmodified>`
* :ref:`avoid-get\_class()`
* :ref:`silently-cast-integer`
* :ref:`timestamp-difference`
* :ref:`No anchor for Functions/UnusedArguments <no-anchor-for-functions-unusedarguments>`
* :ref:`No anchor for Structures/SwitchToSwitch <no-anchor-for-structures-switchtoswitch>`
* :ref:`wrong-parameter-type`
* :ref:`redefined-class-constants`
* :ref:`redefined-default`
* :ref:`wrong-fopen()-mode`
* :ref:`negative-power`
* :ref:`No anchor for Interfaces/AlreadyParentsInterface <no-anchor-for-interfaces-alreadyparentsinterface>`
* :ref:`use-random\_int()`
* :ref:`No anchor for Classes/CantExtendFinal <no-anchor-for-classes-cantextendfinal>`
* :ref:`ternary-in-concat`
* :ref:`No anchor for Classes/UsingThisOutsideAClass <no-anchor-for-classes-usingthisoutsideaclass>`
* :ref:`undefined-trait`
* :ref:`No anchor for Structures/NoHardcodedHash <no-anchor-for-structures-nohardcodedhash>`
* :ref:`identical-conditions`
* :ref:`unkown-regex-options`
* :ref:`no-choice`
* :ref:`No anchor for Structures/CommonAlternatives <no-anchor-for-structures-commonalternatives>`
* :ref:`logical-mistakes`
* :ref:`No anchor for Exceptions/UncaughtExceptions <no-anchor-for-exceptions-uncaughtexceptions>`
* :ref:`same-conditions-in-condition`
* :ref:`return-true-false`
* :ref:`No anchor for Structures/UselessSwitch <no-anchor-for-structures-uselessswitch>`
* :ref:`could-use-\_\_dir\_\_`
* :ref:`should-use-coalesce`
* :ref:`No anchor for Classes/MakeGlobalAProperty <no-anchor-for-classes-makeglobalaproperty>`
* :ref:`if-with-same-conditions`
* :ref:`throw-functioncall`
* :ref:`use-instanceof`
* :ref:`results-may-be-missing`
* :ref:`always-positive-comparison`
* :ref:`empty-blocks`
* :ref:`throw-in-destruct`
* :ref:`use-system-tmp`
* :ref:`No anchor for Traits/DependantTrait <no-anchor-for-traits-dependanttrait>`
* :ref:`hidden-use-expression`
* :ref:`should-make-alias`
* :ref:`multiple-identical-trait-or-interface`
* :ref:`multiple-alias-definitions`
* :ref:`No anchor for Structures/NestedIfthen <no-anchor-for-structures-nestedifthen>`
* :ref:`No anchor for Structures/CastToBoolean <no-anchor-for-structures-casttoboolean>`
* :ref:`failed-substr-comparison`
* :ref:`should-make-ternary`
* :ref:`No anchor for Functions/UnusedReturnedValue <no-anchor-for-functions-unusedreturnedvalue>`
* :ref:`No anchor for Structures/ModernEmpty <no-anchor-for-structures-modernempty>`
* :ref:`No anchor for Structures/UsePositiveCondition <no-anchor-for-structures-usepositivecondition>`
* :ref:`drop-else-after-return`
* :ref:`use-class-operator`
* :ref:`don't-echo-error`
* :ref:`useless-type-casting`
* :ref:`no-isset()-with-empty()`
* :ref:`useless-check`
* :ref:`No anchor for Structures/BailOutEarly <no-anchor-for-structures-bailoutearly>`
* :ref:`No anchor for Structures/DontChangeBlindKey <no-anchor-for-structures-dontchangeblindkey>`
* :ref:`No anchor for Php/UseStdclass <no-anchor-for-php-usestdclass>`
* :ref:`No anchor for Functions/TooManyLocalVariables <no-anchor-for-functions-toomanylocalvariables>`
* :ref:`No anchor for Classes/WrongName <no-anchor-for-classes-wrongname>`
* :ref:`No anchor for Classes/FinalByOcramius <no-anchor-for-classes-finalbyocramius>`
* :ref:`No anchor for Structures/LongArguments <no-anchor-for-structures-longarguments>`
* :ref:`No anchor for Variables/AssignedTwiceOrMore <no-anchor-for-variables-assignedtwiceormore>`
* :ref:`No anchor for Functions/NoBooleanAsDefault <no-anchor-for-functions-nobooleanasdefault>`
* :ref:`No anchor for Exceptions/ForgottenThrown <no-anchor-for-exceptions-forgottenthrown>`
* :ref:`multiple-alias-definitions-per-file`
* :ref:`\_\_dir\_\_-then-slash`
* :ref:`No anchor for Classes/NoPSSOutsideClass <no-anchor-for-classes-nopssoutsideclass>`
* :ref:`No anchor for Classes/UsedOnceProperty <no-anchor-for-classes-usedonceproperty>`
* :ref:`No anchor for Classes/PropertyUsedInOneMethodOnly <no-anchor-for-classes-propertyusedinonemethodonly>`
* :ref:`No anchor for Structures/NoNeedForElse <no-anchor-for-structures-noneedforelse>`
* :ref:`No anchor for Variables/StrangeName <no-anchor-for-variables-strangename>`
* :ref:`No anchor for Constants/StrangeName <no-anchor-for-constants-strangename>`
* :ref:`No anchor for Classes/TooManyFinds <no-anchor-for-classes-toomanyfinds>`
* :ref:`No anchor for Php/UseSetCookie <no-anchor-for-php-usesetcookie>`
* :ref:`No anchor for Structures/CheckAllTypes <no-anchor-for-structures-checkalltypes>`
* :ref:`No anchor for Structures/MissingCases <no-anchor-for-structures-missingcases>`
* :ref:`repeated-regex`
* :ref:`no-class-in-global`
* :ref:`No anchor for Php/Crc32MightBeNegative <no-anchor-for-php-crc32mightbenegative>`
* :ref:`could-use-str\_repeat()`
* :ref:`No anchor for Structures/SuspiciousComparison <no-anchor-for-structures-suspiciouscomparison>`
* :ref:`strings-with-strange-space`
* :ref:`no-empty-regex`
* :ref:`No anchor for Structures/AlternativeConsistenceByFile <no-anchor-for-structures-alternativeconsistencebyfile>`
* :ref:`No anchor for Arrays/RandomlySortedLiterals <no-anchor-for-arrays-randomlysortedliterals>`
* :ref:`No anchor for Functions/OnlyVariablePassedByReference <no-anchor-for-functions-onlyvariablepassedbyreference>`
* :ref:`No anchor for Functions/NoReturnUsed <no-anchor-for-functions-noreturnused>`
* :ref:`no-reference-on-left-side`
* :ref:`No anchor for Classes/ImplementedMethodsArePublic <no-anchor-for-classes-implementedmethodsarepublic>`
* :ref:`No anchor for Structures/MixedConcatInterpolation <no-anchor-for-structures-mixedconcatinterpolation>`
* :ref:`No anchor for Classes/TooManyInjections <no-anchor-for-classes-toomanyinjections>`
* :ref:`No anchor for Functions/CouldCentralize <no-anchor-for-functions-couldcentralize>`
* :ref:`No anchor for Interfaces/CouldUseInterface <no-anchor-for-interfaces-coulduseinterface>`
* :ref:`No anchor for Classes/AvoidOptionalProperties <no-anchor-for-classes-avoidoptionalproperties>`
* :ref:`No anchor for Structures/MismatchedTernary <no-anchor-for-structures-mismatchedternary>`
* :ref:`No anchor for Functions/MismatchedDefaultArguments <no-anchor-for-functions-mismatcheddefaultarguments>`
* :ref:`No anchor for Functions/MismatchedTypehint <no-anchor-for-functions-mismatchedtypehint>`
* :ref:`No anchor for Classes/ScalarOrObjectProperty <no-anchor-for-classes-scalarorobjectproperty>`
* :ref:`assign-with-and-precedence`
* :ref:`no-magic-method-with-array`
* :ref:`No anchor for Performances/LogicalToInArray <no-anchor-for-performances-logicaltoinarray>`
* :ref:`No anchor for Php/PathinfoReturns <no-anchor-for-php-pathinforeturns>`
* :ref:`No anchor for Structures/MultipleTypeVariable <no-anchor-for-structures-multipletypevariable>`
* :ref:`is-actually-zero`
* :ref:`unconditional-break-in-loop`
* :ref:`No anchor for Structures/CouldBeElse <no-anchor-for-structures-couldbeelse>`
* :ref:`next-month-trap`
* :ref:`printf-number-of-arguments`
* :ref:`No anchor for Classes/AmbiguousStatic <no-anchor-for-classes-ambiguousstatic>`
* :ref:`No anchor for Classes/DontSendThisInConstructor <no-anchor-for-classes-dontsendthisinconstructor>`
* :ref:`No anchor for Structures/NoGetClassNull <no-anchor-for-structures-nogetclassnull>`
* :ref:`No anchor for Structures/MissingNew <no-anchor-for-structures-missingnew>`
* :ref:`No anchor for Php/UnknownPcre2Option <no-anchor-for-php-unknownpcre2option>`
* :ref:`No anchor for Classes/ParentFirst <no-anchor-for-classes-parentfirst>`
* :ref:`invalid-regex`
* :ref:`No anchor for Functions/AvoidBooleanArgument <no-anchor-for-functions-avoidbooleanargument>`
* :ref:`same-variable-foreach`
* :ref:`No anchor for Functions/NeverUsedParameter <no-anchor-for-functions-neverusedparameter>`
* :ref:`identical-on-both-sides`
* :ref:`No anchor for Structures/IdenticalConsecutive <no-anchor-for-structures-identicalconsecutive>`
* :ref:`no-reference-for-ternary`
* :ref:`unused-inherited-variable-in-closure`
* :ref:`No anchor for Files/InclusionWrongCase <no-anchor-for-files-inclusionwrongcase>`
* :ref:`No anchor for Files/MissingInclude <no-anchor-for-files-missinginclude>`
* :ref:`No anchor for Functions/UselessReferenceArgument <no-anchor-for-functions-uselessreferenceargument>`
* :ref:`useless-catch`
* :ref:`No anchor for Structures/PossibleInfiniteLoop <no-anchor-for-structures-possibleinfiniteloop>`
* :ref:`No anchor for Structures/TestThenCast <no-anchor-for-structures-testthencast>`
* :ref:`No anchor for Php/ForeachObject <no-anchor-for-php-foreachobject>`
* :ref:`No anchor for Classes/PropertyCouldBeLocal <no-anchor-for-classes-propertycouldbelocal>`
* :ref:`No anchor for Php/TooManyNativeCalls <no-anchor-for-php-toomanynativecalls>`
* :ref:`No anchor for Classes/RedefinedPrivateProperty <no-anchor-for-classes-redefinedprivateproperty>`
* :ref:`don't-unset-properties`
* :ref:`strtr-arguments`
* :ref:`missing-parenthesis`
* :ref:`callback-function-needs-return`
* :ref:`No anchor for Structures/WrongRange <no-anchor-for-structures-wrongrange>`
* :ref:`No anchor for Classes/CantInstantiateClass <no-anchor-for-classes-cantinstantiateclass>`
* :ref:`strpos()-too-much`
* :ref:`typehinted-references`
* :ref:`No anchor for Classes/WeakType <no-anchor-for-classes-weaktype>`
* :ref:`No anchor for Classes/MethodSignatureMustBeCompatible <no-anchor-for-classes-methodsignaturemustbecompatible>`
* :ref:`No anchor for Functions/MismatchTypeAndDefault <no-anchor-for-functions-mismatchtypeanddefault>`
* :ref:`check-json`
* :ref:`No anchor for Structures/DontMixPlusPlus <no-anchor-for-structures-dontmixplusplus>`
* :ref:`No anchor for Exceptions/CantThrow <no-anchor-for-exceptions-cantthrow>`
* :ref:`No anchor for Classes/AbstractOrImplements <no-anchor-for-classes-abstractorimplements>`
* :ref:`No anchor for Classes/IncompatibleSignature <no-anchor-for-classes-incompatiblesignature>`
* :ref:`No anchor for Classes/AmbiguousVisibilities <no-anchor-for-classes-ambiguousvisibilities>`
* :ref:`undefined-class`
* :ref:`No anchor for Php/AssertFunctionIsReserved <no-anchor-for-php-assertfunctionisreserved>`
* :ref:`No anchor for Classes/CouldBeAbstractClass <no-anchor-for-classes-couldbeabstractclass>`
* :ref:`No anchor for Structures/ContinueIsForLoop <no-anchor-for-structures-continueisforloop>`
* :ref:`No anchor for Php/MustCallParentConstructor <no-anchor-for-php-mustcallparentconstructor>`
* :ref:`undefined-variable`
* :ref:`undefined-insteadof`
* :ref:`No anchor for Traits/MethodCollisionTraits <no-anchor-for-traits-methodcollisiontraits>`
* :ref:`No anchor for Classes/CouldBeFinal <no-anchor-for-classes-couldbefinal>`
* :ref:`No anchor for Structures/InconsistentElseif <no-anchor-for-structures-inconsistentelseif>`
* :ref:`No anchor for Functions/OnlyVariableForReference <no-anchor-for-functions-onlyvariableforreference>`
* :ref:`wrong-access-style-to-property`
* :ref:`invalid-pack-format`
* :ref:`No anchor for Interfaces/RepeatedInterface <no-anchor-for-interfaces-repeatedinterface>`
* :ref:`don't-read-and-write-in-one-expression`
* :ref:`should-yield-with-key`
* :ref:`useless-alias`
* :ref:`No anchor for Classes/CouldBeStatic <no-anchor-for-classes-couldbestatic>`
* :ref:`possible-missing-subpattern`
* :ref:`assign-and-compare`
* :ref:`No anchor for Structures/NoVariableIsACondition <no-anchor-for-structures-novariableisacondition>`
* :ref:`No anchor for Functions/InsufficientTypehint <no-anchor-for-functions-insufficienttypehint>`
* :ref:`typehint-must-be-returned`
* :ref:`No anchor for Classes/CloneWithNonObject <no-anchor-for-classes-clonewithnonobject>`
* :ref:`check-on-\_\_call-usage`
* :ref:`No anchor for Classes/AvoidOptionArrays <no-anchor-for-classes-avoidoptionarrays>`
* :ref:`No anchor for Traits/AlreadyParentsTrait <no-anchor-for-traits-alreadyparentstrait>`
* :ref:`No anchor for Traits/TraitNotFound <no-anchor-for-traits-traitnotfound>`
* :ref:`casting-ternary`
* :ref:`No anchor for Structures/ConcatEmpty <no-anchor-for-structures-concatempty>`
* :ref:`concat-and-addition`
* :ref:`No anchor for Structures/NoAppendOnSource <no-anchor-for-structures-noappendonsource>`
* :ref:`No anchor for Performances/MemoizeMagicCall <no-anchor-for-performances-memoizemagiccall>`
* :ref:`No anchor for Classes/UnusedConstant <no-anchor-for-classes-unusedconstant>`
* :ref:`No anchor for Structures/InfiniteRecursion <no-anchor-for-structures-infiniterecursion>`
* :ref:`No anchor for Arrays/NullBoolean <no-anchor-for-arrays-nullboolean>`
* :ref:`No anchor for Classes/DependantAbstractClass <no-anchor-for-classes-dependantabstractclass>`
* :ref:`wrong-type-returned`
* :ref:`No anchor for Structures/ForeachSourceValue <no-anchor-for-structures-foreachsourcevalue>`
* :ref:`No anchor for Php/AvoidMbDectectEncoding <no-anchor-for-php-avoidmbdectectencoding>`
* :ref:`array\_key\_exists()-works-on-arrays`
* :ref:`class-without-parent`
* :ref:`scalar-are-not-arrays`
* :ref:`No anchor for Structures/ArrayMergeAndVariadic <no-anchor-for-structures-arraymergeandvariadic>`
* :ref:`implode()-arguments-order`
* :ref:`strip\_tags-skips-closed-tag`
* :ref:`No anchor for Arrays/NoSpreadForHash <no-anchor-for-arrays-nospreadforhash>`
* :ref:`No anchor for Structures/MaxLevelOfIdentation <no-anchor-for-structures-maxlevelofidentation>`
* :ref:`should-use-explode-args`
* :ref:`use-array\_slice()`
* :ref:`No anchor for Arrays/TooManyDimensions <no-anchor-for-arrays-toomanydimensions>`
* :ref:`coalesce-and-concat`
* :ref:`No anchor for Structures/AlwaysFalse <no-anchor-for-structures-alwaysfalse>`
* :ref:`No anchor for Classes/IncompatibleSignature74 <no-anchor-for-classes-incompatiblesignature74>`
* :ref:`interfaces-is-not-implemented`
* :ref:`no-literal-for-reference`
* :ref:`No anchor for Interfaces/NoGaranteeForPropertyConstant <no-anchor-for-interfaces-nogaranteeforpropertyconstant>`
* :ref:`No anchor for Classes/NonNullableSetters <no-anchor-for-classes-nonnullablesetters>`
* :ref:`No anchor for Classes/TooManyDereferencing <no-anchor-for-classes-toomanydereferencing>`
* :ref:`cant-implement-traversable`
* :ref:`is\_a()-with-string`
* :ref:`mbstring-unknown-encoding`
* :ref:`mbstring-third-arg`
* :ref:`merge-if-then`
* :ref:`wrong-type-with-call`
* :ref:`not-equal-is-not-!==`
* :ref:`No anchor for Functions/DontUseVoid <no-anchor-for-functions-dontusevoid>`
* :ref:`wrong-typed-property-default`
* :ref:`No anchor for Classes/HiddenNullable <no-anchor-for-classes-hiddennullable>`
* :ref:`No anchor for Functions/FnArgumentVariableConfusion <no-anchor-for-functions-fnargumentvariableconfusion>`
* :ref:`No anchor for Classes/MissingAbstractMethod <no-anchor-for-classes-missingabstractmethod>`
* :ref:`No anchor for Variables/UndefinedConstantName <no-anchor-for-variables-undefinedconstantname>`
* :ref:`No anchor for Functions/UsingDeprecated <no-anchor-for-functions-usingdeprecated>`
* :ref:`No anchor for Classes/CyclicReferences <no-anchor-for-classes-cyclicreferences>`
* :ref:`No anchor for Structures/DoubleObjectAssignation <no-anchor-for-structures-doubleobjectassignation>`
* :ref:`No anchor for Functions/WrongArgumentType <no-anchor-for-functions-wrongargumenttype>`
* :ref:`No anchor for Classes/MismatchProperties <no-anchor-for-classes-mismatchproperties>`
* :ref:`No anchor for Structures/NoNeedForTriple <no-anchor-for-structures-noneedfortriple>`
* :ref:`No anchor for Structures/ArrayMergeArrayArray <no-anchor-for-structures-arraymergearrayarray>`
* :ref:`wrong-type-for-native-php-function`
* :ref:`No anchor for Exceptions/CatchUndefinedVariable <no-anchor-for-exceptions-catchundefinedvariable>`
* :ref:`No anchor for Classes/SwappedArguments <no-anchor-for-classes-swappedarguments>`
* :ref:`No anchor for Classes/DifferentArgumentCounts <no-anchor-for-classes-differentargumentcounts>`
* :ref:`unknown-parameter-name`
* :ref:`missing-some-returntype`
* :ref:`No anchor for Php/DontPolluteGlobalSpace <no-anchor-for-php-dontpolluteglobalspace>`
* :ref:`mismatch-parameter-name`
* :ref:`No anchor for Php/MultipleDeclareStrict <no-anchor-for-php-multipledeclarestrict>`
* :ref:`No anchor for Functions/MismatchParameterAndType <no-anchor-for-functions-mismatchparameterandtype>`
* :ref:`No anchor for Structures/ArrayFillWithObjects <no-anchor-for-structures-arrayfillwithobjects>`
* :ref:`No anchor for Functions/ModifyTypedParameter <no-anchor-for-functions-modifytypedparameter>`
* :ref:`No anchor for Php/Assumptions <no-anchor-for-php-assumptions>`
* :ref:`unsupported-types-with-operators`
* :ref:`No anchor for Classes/CouldBeStringable <no-anchor-for-classes-couldbestringable>`
* :ref:`No anchor for Php/WrongAttributeConfiguration <no-anchor-for-php-wrongattributeconfiguration>`
* :ref:`No anchor for Functions/CancelledParameter <no-anchor-for-functions-cancelledparameter>`
* :ref:`No anchor for Variables/ConstantTypo <no-anchor-for-variables-constanttypo>`
* :ref:`array\_map()-passes-by-value`
* :ref:`No anchor for Php/MissingMagicIsset <no-anchor-for-php-missingmagicisset>`
* :ref:`No anchor for Attributes/ModifyImmutable <no-anchor-for-attributes-modifyimmutable>`
* :ref:`No anchor for Php/OnlyVariableForReference <no-anchor-for-php-onlyvariableforreference>`
* :ref:`No anchor for Functions/CannotUseStaticForClosure <no-anchor-for-functions-cannotusestaticforclosure>`
* :ref:`No anchor for Structures/OnlyFirstByte <no-anchor-for-structures-onlyfirstbyte>`
* :ref:`No anchor for Classes/InheritedPropertyMustMatch <no-anchor-for-classes-inheritedpropertymustmatch>`
* :ref:`No anchor for Structures/NoObjectAsIndex <no-anchor-for-structures-noobjectasindex>`
* :ref:`No anchor for Structures/HtmlentitiescallDefaultFlag <no-anchor-for-structures-htmlentitiescalldefaultflag>`
* :ref:`No anchor for Functions/WrongArgumentNameWithPhpFunction <no-anchor-for-functions-wrongargumentnamewithphpfunction>`
* :ref:`No anchor for Functions/DuplicateNamedParameter <no-anchor-for-functions-duplicatenamedparameter>`
* :ref:`No anchor for Php/NativeClassTypeCompatibility <no-anchor-for-php-nativeclasstypecompatibility>`
* :ref:`No anchor for Attributes/MissingAttributeAttribute <no-anchor-for-attributes-missingattributeattribute>`
* :ref:`No anchor for Php/NoNullForNative <no-anchor-for-php-nonullfornative>`
* :ref:`No anchor for Functions/NoReferencedVoid <no-anchor-for-functions-noreferencedvoid>`
* :ref:`No anchor for Php/JsonSerializeReturnType <no-anchor-for-php-jsonserializereturntype>`

.. _ruleset-appinfo:

Appinfo
+++++++

A set of rules that describes with PHP features is used in the code.

Total : 375 analysis

* :ref:`array-index`
* :ref:`multidimensional-arrays`
* :ref:`php-arrays-index`
* :ref:`classes-names`
* :ref:`constant-definition`
* :ref:`magic-methods`
* :ref:`old-style-constructor`
* :ref:`static-methods`
* :ref:`static-properties`
* :ref:`constants-usage`
* :ref:`magic-constant-usage`
* :ref:`php-constant-usage`
* :ref:`defined-exceptions`
* :ref:`thrown-exceptions`
* :ref:`ext-apc`
* :ref:`ext-bcmath`
* :ref:`ext-bzip2`
* :ref:`ext-calendar`
* :ref:`ext-crypto`
* :ref:`ext-ctype`
* :ref:`ext-curl`
* :ref:`ext-date`
* :ref:`ext-dba`
* :ref:`ext-dom`
* :ref:`ext-enchant`
* :ref:`ext-ereg`
* :ref:`ext-exif`
* :ref:`ext-fdf`
* :ref:`ext-fileinfo`
* :ref:`ext-filter`
* :ref:`ext-ftp`
* :ref:`ext-gd`
* :ref:`ext-gmp`
* :ref:`ext-gnupgp`
* :ref:`ext-hash`
* :ref:`ext-iconv`
* :ref:`ext-json`
* :ref:`ext-kdm5`
* :ref:`ext-ldap`
* :ref:`ext-libxml`
* :ref:`ext-mbstring`
* :ref:`ext-mcrypt`
* :ref:`ext-mongo`
* :ref:`ext-mssql`
* :ref:`ext-mysql`
* :ref:`ext-mysqli`
* :ref:`ext-odbc`
* :ref:`ext-openssl`
* :ref:`ext-pcre`
* :ref:`ext-pdo`
* :ref:`ext-pgsql`
* :ref:`ext-phar`
* :ref:`ext-posix`
* :ref:`ext-readline`
* :ref:`ext-reflection`
* :ref:`ext-sem`
* :ref:`ext-session`
* :ref:`ext-shmop`
* :ref:`ext-simplexml`
* :ref:`ext-snmp`
* :ref:`ext-soap`
* :ref:`ext-sockets`
* :ref:`ext-spl`
* :ref:`ext-sqlite`
* :ref:`ext-sqlite3`
* :ref:`ext-ssh2`
* :ref:`ext-standard`
* :ref:`ext-tidy`
* :ref:`ext-tokenizer`
* :ref:`ext-wddx`
* :ref:`ext-xdebug`
* :ref:`ext-xmlreader`
* :ref:`ext-xmlrpc`
* :ref:`ext-xmlwriter`
* :ref:`ext-xsl`
* :ref:`ext-yaml`
* :ref:`ext-zip`
* :ref:`ext-zlib`
* :ref:`closures-glossary`
* :ref:`functions-glossary`
* :ref:`recursive-functions`
* :ref:`redeclared-php-functions`
* :ref:`typehints`
* :ref:`interfaces-glossary`
* :ref:`aliases`
* :ref:`namespaces-glossary`
* :ref:`autoloading`
* :ref:`goto-names`
* :ref:`\_\_halt\_compiler`
* :ref:`incompilable-files`
* :ref:`labels`
* :ref:`throw`
* :ref:`trigger-errors`
* :ref:`caught-expressions`
* :ref:`eval()-usage`
* :ref:`exit()-usage`
* :ref:`@-operator`
* :ref:`include\_once()-usage`
* :ref:`using-short-tags`
* :ref:`binary-glossary`
* :ref:`email-addresses`
* :ref:`heredoc-delimiter-glossary`
* :ref:`hexadecimal-glossary`
* :ref:`md5-strings`
* :ref:`nowdoc-delimiter-glossary`
* :ref:`octal-glossary`
* :ref:`url-list`
* :ref:`variable-references`
* :ref:`static-variables`
* :ref:`variables-with-long-names`
* :ref:`variable-variables`
* :ref:`abstract-class-usage`
* :ref:`abstract-methods-usage`
* :ref:`clone-usage`
* :ref:`variable-constants`
* :ref:`redefined-php-traits`
* :ref:`traits-usage`
* :ref:`trait-names`
* :ref:`php-alternative-syntax`
* :ref:`short-syntax-for-arrays`
* :ref:`inclusions`
* :ref:`ext-file`
* :ref:`ext-array`
* :ref:`ext-ffmpeg`
* :ref:`ext-info`
* :ref:`ext-math`
* :ref:`$http\_raw\_post\_data-usage`
* :ref:`ext-yis`
* :ref:`assertions`
* :ref:`cast-usage`
* :ref:`function-subscripting`
* :ref:`nested-loops`
* :ref:`I?=-usage`
* :ref:`ext-pcntl`
* :ref:`ext-ming`
* :ref:`ext-redis`
* :ref:`ext-cyrus`
* :ref:`ext-sqlsrv`
* :ref:`ellipsis-usage`
* :ref:`ext-0mq`
* :ref:`ext-memcache`
* :ref:`ext-memcached`
* :ref:`dynamic-function-call`
* :ref:`has-variable-arguments`
* :ref:`multiple-catch`
* :ref:`dynamically-called-classes`
* :ref:`conditioned-function`
* :ref:`conditioned-constants`
* :ref:`is-generator`
* :ref:`try-with-finally`
* :ref:`dereferencing-string-and-arrays`
* :ref:`constant-scalar-expressions`
* :ref:`ext-imagick`
* :ref:`ext-oci8`
* :ref:`ext-imap`
* :ref:`overwritten-class-const`
* :ref:`dynamic-class-constant`
* :ref:`dynamic-methodcall`
* :ref:`dynamic-new`
* :ref:`dynamic-property`
* :ref:`dynamic-classes`
* :ref:`multiple-classes-in-one-file`
* :ref:`file-uploads`
* :ref:`ext-intl`
* :ref:`ext-cairo`
* :ref:`dynamic-code`
* :ref:`ext-pspell`
* :ref:`no-direct-access`
* :ref:`ext-opcache`
* :ref:`ext-expect`
* :ref:`ext-recode`
* :ref:`ext-parsekit`
* :ref:`ext-runkit`
* :ref:`ext-gettext`
* :ref:`super-global-usage`
* :ref:`global-usage`
* :ref:`namespaces`
* :ref:`deep-definitions`
* :ref:`not-definitions-only`
* :ref:`usage-of-class\_alias()`
* :ref:`ext-apache`
* :ref:`ext-eaccelerator`
* :ref:`ext-fpm`
* :ref:`ext-iis`
* :ref:`ext-xcache`
* :ref:`ext-wincache`
* :ref:`resources-usage`
* :ref:`shell-usage`
* :ref:`file-usage`
* :ref:`mail-usage`
* :ref:`dynamic-calls`
* :ref:`test-class`
* :ref:`mark-callable`
* :ref:`ext-dio`
* :ref:`ext-phalcon`
* :ref:`composer-usage`
* :ref:`composer's-autoload`
* :ref:`composer-namespace`
* :ref:`ext-apcu`
* :ref:`ext-trader`
* :ref:`ext-mailparse`
* :ref:`ext-mail`
* :ref:`scalar-typehint-usage`
* :ref:`return-typehint-usage`
* :ref:`ext-ob`
* :ref:`ext-geoip`
* :ref:`ext-event`
* :ref:`ext-amqp`
* :ref:`ext-gearman`
* :ref:`ext-com`
* :ref:`ext-gmagick`
* :ref:`ext-ibase`
* :ref:`ext-inotify`
* :ref:`ext-proctitle`
* :ref:`ext-wikidiff2`
* :ref:`ext-xdiff`
* :ref:`ext-libevent`
* :ref:`ext-ev`
* :ref:`ext-php-ast`
* :ref:`ext-xml`
* :ref:`ext-xhprof`
* :ref:`else-usage`
* :ref:`anonymous-classes`
* :ref:`coalesce`
* :ref:`directives-usage`
* :ref:`global-in-global`
* :ref:`ext-fann`
* :ref:`use-web`
* :ref:`use-cli`
* :ref:`error-messages`
* :ref:`php7-relaxed-keyword`
* :ref:`ext-pecl\_http`
* :ref:`uses-environment`
* :ref:`redefined-methods`
* :ref:`is-cli-script`
* :ref:`php-bugfixes`
* :ref:`ext-tokyotyrant`
* :ref:`ext-v8js`
* :ref:`yield-usage`
* :ref:`yield-from-usage`
* :ref:`pear-usage`
* :ref:`ext-lua`
* :ref:`list-with-keys`
* :ref:`ext-suhosin`
* :ref:`can't-disable-function`
* :ref:`functions-using-reference`
* :ref:`list-short-syntax`
* :ref:`use-nullable-type`
* :ref:`multiple-exceptions-catch()`
* :ref:`ext-rar`
* :ref:`ext-nsapi`
* :ref:`ext-newt`
* :ref:`ext-ncurses`
* :ref:`use-composer-lock`
* :ref:`string`
* :ref:`ext-mhash`
* :ref:`ext-zbarcode`
* :ref:`ext-mongodb`
* :ref:`error\_log()-usage`
* :ref:`sql-queries`
* :ref:`ext-libsodium`
* :ref:`ext-ds`
* :ref:`use-cookies`
* :ref:`group-use-declaration`
* :ref:`ext-sphinx`
* :ref:`try-with-multiple-catch`
* :ref:`ext-grpc`
* :ref:`use-browscap`
* :ref:`use-debug`
* :ref:`psr-16-usage`
* :ref:`psr-7-usage`
* :ref:`psr-6-usage`
* :ref:`psr-3-usage`
* :ref:`psr-11-usage`
* :ref:`psr-13-usage`
* :ref:`ext-stats`
* :ref:`dependency-injection`
* :ref:`courier-anti-pattern`
* :ref:`ext-gender`
* :ref:`ext-judy`
* :ref:`yii-usage`
* :ref:`codeigniter-usage`
* :ref:`laravel-usage`
* :ref:`symfony-usage`
* :ref:`wordpress-usage`
* :ref:`ez-cms-usage`
* :ref:`joomla-usage`
* :ref:`non-breakable-space-in-names`
* :ref:`multiple-functions-declarations`
* :ref:`ext-swoole`
* :ref:`manipulates-nan`
* :ref:`manipulates-inf`
* :ref:`const-or-define`
* :ref:`strict\_types-preference`
* :ref:`declare-strict\_types-usage`
* :ref:`encoding-usage`
* :ref:`ticks-usage`
* :ref:`ext-lapack`
* :ref:`ext-xattr`
* :ref:`ext-rdkafka`
* :ref:`ext-fam`
* :ref:`ext-parle`
* :ref:`regex-inventory`
* :ref:`too-complex-expression`
* :ref:`drupal-usage`
* :ref:`phalcon-usage`
* :ref:`fuelphp-usage`
* :ref:`argon2-usage`
* :ref:`crypto-usage`
* :ref:`type-array-index`
* :ref:`incoming-variable-index-inventory`
* :ref:`ext-vips`
* :ref:`dl()-usage`
* :ref:`environment-variables`
* :ref:`ext-igbinary`
* :ref:`fallback-function`
* :ref:`ext-hrtime`
* :ref:`ext-xxtea`
* :ref:`ext-uopz`
* :ref:`ext-varnish`
* :ref:`ext-opencensus`
* :ref:`ext-leveldb`
* :ref:`ext-db2`
* :ref:`ext-zookeeper`
* :ref:`ext-cmark`
* :ref:`No anchor for Classes/ConstVisibilityUsage <no-anchor-for-classes-constvisibilityusage>`
* :ref:`ext-eio`
* :ref:`ext-csprng`
* :ref:`ext-lzf`
* :ref:`ext-msgpack`
* :ref:`case-insensitive-constants`
* :ref:`handle-arrays-with-callback`
* :ref:`trailing-comma-in-calls`
* :ref:`can't-disable-class`
* :ref:`ext-seaslog`
* :ref:`pack-format-inventory`
* :ref:`printf-format-inventory`
* :ref:`ext-decimal`
* :ref:`ext-psr`
* :ref:`ext-sdl`
* :ref:`ext-async`
* :ref:`ext-wasm`
* :ref:`path-lists`
* :ref:`typed-property-usage`
* :ref:`ext-weakref`
* :ref:`ext-pcov`
* :ref:`constant-dynamic-creation`
* :ref:`an-oop-factory`
* :ref:`php-overridden-function`
* :ref:`ext-svm`
* :ref:`ext-ffi`
* :ref:`ext-password`
* :ref:`ext-zend\_monitor`
* :ref:`ext-uuid`
* :ref:`numeric-literal-separator`
* :ref:`use-covariance`
* :ref:`use-contravariance`
* :ref:`use-arrow-functions`
* :ref:`spread-operator-for-array`
* :ref:`nested-ternary-without-parenthesis`
* :ref:`typo-3-usage`
* :ref:`concrete-usage`
* :ref:`immutable-signature`
* :ref:`shell-commands`
* :ref:`links-between-parameter-and-argument`
* :ref:`php-8.0-variable-syntax-tweaks`
* :ref:`php-8.0-only-typehints`
* :ref:`union-typehint`
* :ref:`protocol-lists`
* :ref:`use-php-attributes`
* :ref:`use-nullsafe-operator`
* :ref:`use-closure-trailing-comma`
* :ref:`No anchor for Classes/ClassOverreach <no-anchor-for-classes-classoverreach>`
* :ref:`No anchor for Php/FinalConstant <no-anchor-for-php-finalconstant>`
* :ref:`No anchor for Php/NeverTypehintUsage <no-anchor-for-php-nevertypehintusage>`

.. _ruleset-compatibilityphp74:

CompatibilityPHP74
++++++++++++++++++

This ruleset centralizes all analysis for the migration from PHP 7.3 to 7.4.

Total : 36 analysis

* :ref:`detect-current-class`
* :ref:`don't-read-and-write-in-one-expression`
* :ref:`idn\_to\_ascii()-new-default`
* :ref:`concat-and-addition`
* :ref:`new-functions-in-php-7.4`
* :ref:`curl\_version()-has-no-argument`
* :ref:`php-7.4-new-class`
* :ref:`new-constants-in-php-7.4`
* :ref:`php-7.4-removed-functions`
* :ref:`mb\_strrpos()-third-argument`
* :ref:`array\_key\_exists()-works-on-arrays`
* :ref:`reflection-export()-is-deprecated`
* :ref:`unbinding-closures`
* :ref:`scalar-are-not-arrays`
* :ref:`php-7.4-reserved-keyword`
* :ref:`no-more-curly-arrays`
* :ref:`php-7.4-constant-deprecation`
* :ref:`php-7.4-removed-directives`
* :ref:`hash-algorithms-incompatible-with-php-7.4-`
* :ref:`openssl\_random\_pseudo\_byte()-second-argument`
* :ref:`nested-ternary-without-parenthesis`
* :ref:`filter-to-add\_slashes()`
* :ref:`php-8.0-variable-syntax-tweaks`
* :ref:`new-functions-in-php-8.0`
* :ref:`php-8.0-only-typehints`
* :ref:`union-typehint`
* :ref:`signature-trailing-comma`
* :ref:`throw-was-an-expression`
* :ref:`uses-php-8-match()`
* :ref:`No anchor for Php/AvoidGetobjectVars <no-anchor-for-php-avoidgetobjectvars>`
* :ref:`No anchor for Php/EnumUsage <no-anchor-for-php-enumusage>`
* :ref:`No anchor for Php/FilesFullPath <no-anchor-for-php-filesfullpath>`
* :ref:`No anchor for Php/FinalConstant <no-anchor-for-php-finalconstant>`
* :ref:`No anchor for Php/NeverTypehintUsage <no-anchor-for-php-nevertypehintusage>`
* :ref:`No anchor for Php/PHP81scalartypehints <no-anchor-for-php-php81scalartypehints>`
* :ref:`No anchor for Php/PHP80scalartypehints <no-anchor-for-php-php80scalartypehints>`

.. _ruleset-compatibilityphp80:

CompatibilityPHP80
++++++++++++++++++

This ruleset centralizes all analysis for the migration from PHP 7.4 to 8.0.

Total : 23 analysis

* :ref:`old-style-constructor`
* :ref:`wrong-optional-parameter`
* :ref:`php-8.0-removed-functions`
* :ref:`php-8.0-removed-constants`
* :ref:`concat-and-addition`
* :ref:`php-7.4-removed-directives`
* :ref:`cast-unset-usage`
* :ref:`$php\_errormsg-usage`
* :ref:`mismatch-parameter-name`
* :ref:`php-8.0-removed-directives`
* :ref:`unsupported-types-with-operators`
* :ref:`negative-start-index-in-array`
* :ref:`nullable-with-constant`
* :ref:`php-resources-turned-into-objects`
* :ref:`php-80-named-parameter-variadic`
* :ref:`final-private-methods`
* :ref:`array\_map()-passes-by-value`
* :ref:`No anchor for Php/ReservedMatchKeyword <no-anchor-for-php-reservedmatchkeyword>`
* :ref:`No anchor for Php/AvoidGetobjectVars <no-anchor-for-php-avoidgetobjectvars>`
* :ref:`No anchor for Php/EnumUsage <no-anchor-for-php-enumusage>`
* :ref:`No anchor for Php/FinalConstant <no-anchor-for-php-finalconstant>`
* :ref:`No anchor for Php/NeverTypehintUsage <no-anchor-for-php-nevertypehintusage>`
* :ref:`No anchor for Php/PHP81scalartypehints <no-anchor-for-php-php81scalartypehints>`

.. _ruleset-compatibilityphp81:

CompatibilityPHP81
++++++++++++++++++

This ruleset centralizes all analysis for the migration from PHP 8.0 to 8.1.

Total : 12 analysis

* :ref:`php-7.4-removed-directives`
* :ref:`php-8.0-removed-directives`
* :ref:`No anchor for Php/RestrictGlobalUsage <no-anchor-for-php-restrictglobalusage>`
* :ref:`No anchor for Variables/InheritedStaticVariable <no-anchor-for-variables-inheritedstaticvariable>`
* :ref:`No anchor for Php/Php81RemovedDirective <no-anchor-for-php-php81removeddirective>`
* :ref:`No anchor for Php/OpensslEncryptAlgoChange <no-anchor-for-php-opensslencryptalgochange>`
* :ref:`No anchor for Php/Php81RemovedConstant <no-anchor-for-php-php81removedconstant>`
* :ref:`No anchor for Php/NativeClassTypeCompatibility <no-anchor-for-php-nativeclasstypecompatibility>`
* :ref:`No anchor for Php/NoNullForNative <no-anchor-for-php-nonullfornative>`
* :ref:`No anchor for Php/CallingStaticTraitMethod <no-anchor-for-php-callingstatictraitmethod>`
* :ref:`No anchor for Functions/NoReferencedVoid <no-anchor-for-functions-noreferencedvoid>`
* :ref:`No anchor for Php/JsonSerializeReturnType <no-anchor-for-php-jsonserializereturntype>`

.. _ruleset-dump:

Dump
++++

This ruleset collect various names given to different structures in the code ; variables, classes, methods, constants, etc. It is mostly used internally.

Total : 36 analysis

* :ref:`environment-variable-usage`
* :ref:`indentation-levels`
* :ref:`cyclomatic-complexity`
* :ref:`collect-literals`
* :ref:`collect-parameter-counts`
* :ref:`collect-local-variable-counts`
* :ref:`dump-dereferencinglevels`
* :ref:`foreach()-favorite`
* :ref:`collect-mbstring-encodings`
* :ref:`typehinting-stats`
* :ref:`dump-inclusions`
* :ref:`typehint-order`
* :ref:`new-order`
* :ref:`collect-class-interface-counts`
* :ref:`collect-class-depth`
* :ref:`collect-class-children-count`
* :ref:`constant-order`
* :ref:`collect-property-counts`
* :ref:`collect-method-counts`
* :ref:`collect-class-constant-counts`
* :ref:`call-order`
* :ref:`collect-parameter-names`
* :ref:`dump-fossilizedmethods`
* :ref:`dump-collectclasschanges`
* :ref:`collect-variables`
* :ref:`dump-collectglobalvariables`
* :ref:`collect-readability`
* :ref:`dump-collectdefinitionsstats`
* :ref:`collect-class-traits-counts`
* :ref:`collect-native-calls-per-expressions`
* :ref:`collect-files-dependencies`
* :ref:`collect-atom-counts`
* :ref:`collect-classes-dependencies`
* :ref:`collect-php-structures`
* :ref:`collect-use-counts`
* :ref:`No anchor for Dump/CollectBlockSize <no-anchor-for-dump-collectblocksize>`

.. _ruleset-first:

First
+++++

A set of rules that are always run at the beginning of a project, because they are frenquently used. It is mostly used internally.

Total : 5 analysis

* :ref:`is-an-extension-function`
* :ref:`is-an-extension-interface`
* :ref:`is-an-extension-constant`
* :ref:`is-extension-trait`
* :ref:`mark-callable`


