.. _Rulesets:

Rulesets
====================

Introduction
------------------------

Exakat provides unique 1432 rules to detect BUGS, CODE SMELLS, SECURITY OR QUALITY ISSUES in your PHP code.

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
| :ref:`ruleset-analyze`                        |Check for common best practices.                                                                      |
+-----------------------------------------------+------------------------------------------------------------------------------------------------------+
| :ref:`ruleset-appinfo`                        |Appinfo is the equivalent of phpinfo() for your code.                                                 |
+-----------------------------------------------+------------------------------------------------------------------------------------------------------+
| :ref:`ruleset-attributes`                     |This ruleset gathers all rules that rely on PHP 8.+ attributes.                                       |
+-----------------------------------------------+------------------------------------------------------------------------------------------------------+
| :ref:`ruleset-ce`                             |List of rules that are part of the Community Edition                                                  |
+-----------------------------------------------+------------------------------------------------------------------------------------------------------+
| :ref:`ruleset-ci-checks`                      |Quick check for common best practices.                                                                |
+-----------------------------------------------+------------------------------------------------------------------------------------------------------+
| :ref:`ruleset-classreview`                    |A set of rules dedicate to class hygiene                                                              |
+-----------------------------------------------+------------------------------------------------------------------------------------------------------+
| :ref:`ruleset-coding-conventions`             |List coding conventions violations.                                                                   |
+-----------------------------------------------+------------------------------------------------------------------------------------------------------+
| :ref:`ruleset-compatibilityphp53`             |List features that are incompatible with PHP 5.3.                                                     |
+-----------------------------------------------+------------------------------------------------------------------------------------------------------+
| :ref:`ruleset-compatibilityphp54`             |List features that are incompatible with PHP 5.4.                                                     |
+-----------------------------------------------+------------------------------------------------------------------------------------------------------+
| :ref:`ruleset-compatibilityphp55`             |List features that are incompatible with PHP 5.5.                                                     |
+-----------------------------------------------+------------------------------------------------------------------------------------------------------+
| :ref:`ruleset-compatibilityphp56`             |List features that are incompatible with PHP 5.6.                                                     |
+-----------------------------------------------+------------------------------------------------------------------------------------------------------+
| :ref:`ruleset-compatibilityphp70`             |List features that are incompatible with PHP 7.0.                                                     |
+-----------------------------------------------+------------------------------------------------------------------------------------------------------+
| :ref:`ruleset-compatibilityphp71`             |List features that are incompatible with PHP 7.1.                                                     |
+-----------------------------------------------+------------------------------------------------------------------------------------------------------+
| :ref:`ruleset-compatibilityphp72`             |List features that are incompatible with PHP 7.2.                                                     |
+-----------------------------------------------+------------------------------------------------------------------------------------------------------+
| :ref:`ruleset-compatibilityphp73`             |List features that are incompatible with PHP 7.3.                                                     |
+-----------------------------------------------+------------------------------------------------------------------------------------------------------+
| :ref:`ruleset-compatibilityphp74`             |List features that are incompatible with PHP 7.4.                                                     |
+-----------------------------------------------+------------------------------------------------------------------------------------------------------+
| :ref:`ruleset-compatibilityphp80`             |List features that are incompatible with PHP 8.0.                                                     |
+-----------------------------------------------+------------------------------------------------------------------------------------------------------+
| :ref:`ruleset-compatibilityphp81`             |List features that are incompatible with PHP 8.1.                                                     |
+-----------------------------------------------+------------------------------------------------------------------------------------------------------+
| :ref:`ruleset-dead-code`                      |Check the unused code or unreachable code.                                                            |
+-----------------------------------------------+------------------------------------------------------------------------------------------------------+
| :ref:`ruleset-deprecated`                     |List of deprecated features, across all PHP versions.                                                 |
+-----------------------------------------------+------------------------------------------------------------------------------------------------------+
| :ref:`ruleset-dump`                           |Dump is a collector set of rules.                                                                     |
+-----------------------------------------------+------------------------------------------------------------------------------------------------------+
| :ref:`ruleset-first`                          |A set of rules that are always run at the beginning of a project, because they are frenquently used.  |
+-----------------------------------------------+------------------------------------------------------------------------------------------------------+
| :ref:`ruleset-inventory`                      |A set of rules that collect various definitions from the code                                         |
+-----------------------------------------------+------------------------------------------------------------------------------------------------------+
| :ref:`ruleset-lintbutwontexec`                |Check the code for common errors that will lead to a Fatal error on production, but lint fine.        |
+-----------------------------------------------+------------------------------------------------------------------------------------------------------+
| :ref:`ruleset-performances`                   |Check the code for slow code.                                                                         |
+-----------------------------------------------+------------------------------------------------------------------------------------------------------+
| :ref:`ruleset-php-cs-fixable`                 |Suggests configuration to apply changes with PHP-CS-FIXER                                             |
+-----------------------------------------------+------------------------------------------------------------------------------------------------------+
| :ref:`ruleset-preferences`                    |Identify preferences in the code.                                                                     |
+-----------------------------------------------+------------------------------------------------------------------------------------------------------+
| :ref:`ruleset-rector`                         |Suggests configuration to apply changes with Rector                                                   |
+-----------------------------------------------+------------------------------------------------------------------------------------------------------+
| :ref:`ruleset-security`                       |Check the code for common security bad practices, especially in the Web environnement.                |
+-----------------------------------------------+------------------------------------------------------------------------------------------------------+
| :ref:`ruleset-semantics`                      |Checks the meanings found the names of the code.                                                      |
+-----------------------------------------------+------------------------------------------------------------------------------------------------------+
| :ref:`ruleset-suggestions`                    |List of possible modernisation of the PHP code.                                                       |
+-----------------------------------------------+------------------------------------------------------------------------------------------------------+
| :ref:`ruleset-top10`                          |The most common issues found in the code                                                              |
+-----------------------------------------------+------------------------------------------------------------------------------------------------------+
| :ref:`ruleset-typechecks`                     |Checks related to types.                                                                              |
+-----------------------------------------------+------------------------------------------------------------------------------------------------------+
| :ref:`ruleset-all`                            |All is a dummy ruleset, which includes all the rules.                                                 |
+-----------------------------------------------+------------------------------------------------------------------------------------------------------+
| :ref:`ruleset-compatibilityphp82`             |List features that are incompatible with PHP 8.2.                                                     |
+-----------------------------------------------+------------------------------------------------------------------------------------------------------+
| :ref:`ruleset-classdependencies`              |A set of rules dedicated to show classes dependences                                                  |
+-----------------------------------------------+------------------------------------------------------------------------------------------------------+

Note : in command line, don't forget to add quotes to rulesets' names that include white space.

List of rulesets
------------------------

.. _ruleset-analyze:

Analyze
+++++++

This ruleset centralizes a large number of classic trap and pitfalls when writing PHP.

Total : 433 analysis

* :ref:`adding-zero`
* :ref:`ambiguous-array-index`
* :ref:`multiple-index-definition`
* :ref:`empty-classes`
* :ref:`forgotten-visibility`
* :ref:`non-static-methods-called-in-a-static`
* :ref:`old-style-constructor`
* :ref:`static-methods-called-from-object`
* :ref:`empty-function`
* :ref:`redeclared-php-functions`
* :ref:`methods-without-return`
* :ref:`empty-interfaces`
* :ref:`incompilable-files`
* :ref:`error\_reporting()-with-integers`
* :ref:`eval()-usage`
* :ref:`exit()-usage`
* :ref:`forgotten-whitespace`
* :ref:`iffectations`
* :ref:`multiply-by-one`
* :ref:`@-operator`
* :ref:`not-not`
* :ref:`include\_once()-usage`
* :ref:`strpos()-like-comparison`
* :ref:`throws-an-assignement`
* :ref:`var\_dump()...-usage`
* :ref:`\_\_tostring()-throws-exception`
* :ref:`non-ascii-variables`
* :ref:`used-once-variables`
* :ref:`bad-constants-names`
* :ref:`empty-traits`
* :ref:`use-with-fully-qualified-name`
* :ref:`useless-instructions`
* :ref:`abstract-static-methods`
* :ref:`invalid-constant-name`
* :ref:`multiple-constant-definition`
* :ref:`wrong-optional-parameter`
* :ref:`use-===-null`
* :ref:`$this-is-not-an-array`
* :ref:`one-variable-string`
* :ref:`static-methods-can't-contain-$this`
* :ref:`while(list()-=-each())`
* :ref:`several-instructions-on-the-same-line`
* :ref:`multiples-identical-case`
* :ref:`switch-without-default`
* :ref:`$this-belongs-to-classes-or-traits`
* :ref:`nested-ternary`
* :ref:`non-constant-index-in-array`
* :ref:`undefined-constants`
* :ref:`instantiating-abstract-class`
* :ref:`class,-interface-or-trait-with-identical-names`
* :ref:`empty-try-catch`
* :ref:`undefined-classes`
* :ref:`htmlentities-calls`
* :ref:`undefined-class-constants`
* :ref:`used-once-variables-(in-scope)`
* :ref:`undefined-functions`
* :ref:`deprecated-php-functions`
* :ref:`dangling-array-references`
* :ref:`queries-in-loops`
* :ref:`var-keyword`
* :ref:`aliases-usage`
* :ref:`uses-default-values`
* :ref:`wrong-number-of-arguments`
* :ref:`hardcoded-passwords`
* :ref:`unresolved-classes`
* :ref:`useless-constructor`
* :ref:`implement-is-for-interface`
* :ref:`use-const`
* :ref:`unresolved-use`
* :ref:`undefined-parent`
* :ref:`undefined-static-or-self`
* :ref:`accessing-private`
* :ref:`access-protected-structures`
* :ref:`parent,-static-or-self-outside-class`
* :ref:`list()-may-omit-variables`
* :ref:`or-die`
* :ref:`written-only-variables`
* :ref:`must-return-methods`
* :ref:`empty-instructions`
* :ref:`overwritten-exceptions`
* :ref:`foreach-reference-is-not-modified`
* :ref:`don't-change-incomings`
* :ref:`compared-comparison`
* :ref:`useless-return`
* :ref:`unused-classes`
* :ref:`unpreprocessed-values`
* :ref:`undefined-properties`
* :ref:`short-open-tags`
* :ref:`strict-comparison-with-booleans`
* :ref:`lone-blocks`
* :ref:`$this-is-not-for-static-methods`
* :ref:`global-usage`
* :ref:`logical-should-use-symbolic-operators`
* :ref:`could-use-self`
* :ref:`catch-overwrite-variable`
* :ref:`deep-definitions`
* :ref:`repeated-print()`
* :ref:`avoid-parenthesis`
* :ref:`objects-don't-need-references`
* :ref:`lost-references`
* :ref:`constants-created-outside-its-namespace`
* :ref:`fully-qualified-constants`
* :ref:`never-used-properties`
* :ref:`no-real-comparison`
* :ref:`should-use-local-class`
* :ref:`no-direct-call-to-magic-method`
* :ref:`string-may-hold-a-variable`
* :ref:`echo-with-concat`
* :ref:`unused-global`
* :ref:`useless-global`
* :ref:`preprocessable`
* :ref:`useless-final`
* :ref:`use-constant`
* :ref:`useless-unset`
* :ref:`buried-assignation`
* :ref:`no-array\_merge()-in-loops`
* :ref:`useless-parenthesis`
* :ref:`unresolved-instanceof`
* :ref:`use-php-object-api`
* :ref:`unthrown-exception`
* :ref:`old-style-\_\_autoload()`
* :ref:`altering-foreach-without-reference`
* :ref:`use-pathinfo`
* :ref:`should-use-constants`
* :ref:`hash-algorithms`
* :ref:`no-parenthesis-for-language-construct`
* :ref:`no-hardcoded-path`
* :ref:`no-hardcoded-port`
* :ref:`use-constant-as-arguments`
* :ref:`implied-if`
* :ref:`overwritten-literals`
* :ref:`assign-default-to-properties`
* :ref:`no-public-access`
* :ref:`should-chain-exception`
* :ref:`useless-interfaces`
* :ref:`undefined-interfaces`
* :ref:`concrete-visibility`
* :ref:`double-instructions`
* :ref:`should-use-prepared-statement`
* :ref:`print-and-die`
* :ref:`unchecked-resources`
* :ref:`no-hardcoded-ip`
* :ref:`else-if-versus-elseif`
* :ref:`unset-in-foreach`
* :ref:`could-be-static`
* :ref:`multiple-class-declarations`
* :ref:`empty-namespace`
* :ref:`could-use-short-assignation`
* :ref:`useless-abstract-class`
* :ref:`static-loop`
* :ref:`pre-increment`
* :ref:`only-variable-returned-by-reference`
* :ref:`indices-are-int-or-string`
* :ref:`should-typecast`
* :ref:`no-self-referencing-constant`
* :ref:`no-direct-usage`
* :ref:`break-outside-loop`
* :ref:`avoid-substr()-one`
* :ref:`double-assignation`
* :ref:`empty-list`
* :ref:`useless-brackets`
* :ref:`preg\_replace-with-option-e`
* :ref:`eval()-without-try`
* :ref:`relay-function`
* :ref:`func\_get\_arg()-modified`
* :ref:`avoid-get\_class()`
* :ref:`silently-cast-integer`
* :ref:`timestamp-difference`
* :ref:`unused-arguments`
* :ref:`switch-to-switch`
* :ref:`wrong-parameter-type`
* :ref:`redefined-class-constants`
* :ref:`redefined-default`
* :ref:`wrong-fopen()-mode`
* :ref:`negative-power`
* :ref:`already-parents-interface`
* :ref:`use-random\_int()`
* :ref:`can't-extend-final`
* :ref:`ternary-in-concat`
* :ref:`using-$this-outside-a-class`
* :ref:`undefined-trait`
* :ref:`no-hardcoded-hash`
* :ref:`identical-conditions`
* :ref:`unkown-regex-options`
* :ref:`no-choice`
* :ref:`common-alternatives`
* :ref:`logical-mistakes`
* :ref:`uncaught-exceptions`
* :ref:`same-conditions-in-condition`
* :ref:`return-true-false`
* :ref:`useless-switch`
* :ref:`could-use-\_\_dir\_\_`
* :ref:`should-use-coalesce`
* :ref:`make-global-a-property`
* :ref:`if-with-same-conditions`
* :ref:`throw-functioncall`
* :ref:`use-instanceof`
* :ref:`results-may-be-missing`
* :ref:`always-positive-comparison`
* :ref:`empty-blocks`
* :ref:`throw-in-destruct`
* :ref:`use-system-tmp`
* :ref:`dependant-trait`
* :ref:`hidden-use-expression`
* :ref:`should-make-alias`
* :ref:`multiple-identical-trait-or-interface`
* :ref:`multiple-alias-definitions`
* :ref:`nested-ifthen`
* :ref:`cast-to-boolean`
* :ref:`failed-substr-comparison`
* :ref:`should-make-ternary`
* :ref:`unused-returned-value`
* :ref:`modernize-empty-with-expression`
* :ref:`use-positive-condition`
* :ref:`drop-else-after-return`
* :ref:`use-class-operator`
* :ref:`don't-echo-error`
* :ref:`useless-type-casting`
* :ref:`no-isset()-with-empty()`
* :ref:`useless-check`
* :ref:`bail-out-early`
* :ref:`dont-change-the-blind-var`
* :ref:`avoid-using-stdclass`
* :ref:`too-many-local-variables`
* :ref:`illegal-name-for-method`
* :ref:`long-arguments`
* :ref:`assigned-twice`
* :ref:`no-boolean-as-default`
* :ref:`forgotten-thrown`
* :ref:`multiple-alias-definitions-per-file`
* :ref:`\_\_dir\_\_-then-slash`
* :ref:`self,-parent,-static-outside-class`
* :ref:`used-once-property`
* :ref:`property-used-in-one-method-only`
* :ref:`no-need-for-else`
* :ref:`strange-name-for-constants`
* :ref:`too-many-finds`
* :ref:`should-use-setcookie()`
* :ref:`check-all-types`
* :ref:`missing-cases-in-switch`
* :ref:`repeated-regex`
* :ref:`no-class-in-global`
* :ref:`crc32()-might-be-negative`
* :ref:`could-use-str\_repeat()`
* :ref:`suspicious-comparison`
* :ref:`strings-with-strange-space`
* :ref:`no-empty-regex`
* :ref:`alternative-syntax-consistence`
* :ref:`randomly-sorted-arrays`
* :ref:`only-variable-passed-by-reference`
* :ref:`no-return-used`
* :ref:`no-reference-on-left-side`
* :ref:`implemented-methods-are-public`
* :ref:`mixed-concat-and-interpolation`
* :ref:`too-many-injections`
* :ref:`could-make-a-function`
* :ref:`forgotten-interface`
* :ref:`avoid-optional-properties`
* :ref:`mismatched-ternary-alternatives`
* :ref:`mismatched-default-arguments`
* :ref:`mismatched-typehint`
* :ref:`scalar-or-object-property`
* :ref:`assign-with-and-precedence`
* :ref:`no-magic-method-with-array`
* :ref:`logical-to-in\_array`
* :ref:`pathinfo()-returns-may-vary`
* :ref:`multiple-type-variable`
* :ref:`is-actually-zero`
* :ref:`unconditional-break-in-loop`
* :ref:`could-be-else`
* :ref:`next-month-trap`
* :ref:`printf-number-of-arguments`
* :ref:`ambiguous-static`
* :ref:`don't-send-$this-in-constructor`
* :ref:`no-get\_class()-with-null`
* :ref:`maybe-missing-new`
* :ref:`unknown-pcre2-option`
* :ref:`parent-first`
* :ref:`invalid-regex`
* :ref:`use-named-boolean-in-argument-definition`
* :ref:`same-variable-foreach`
* :ref:`never-called-parameter`
* :ref:`identical-on-both-sides`
* :ref:`identical-consecutive-expression`
* :ref:`no-reference-for-ternary`
* :ref:`unused-inherited-variable-in-closure`
* :ref:`inclusion-wrong-case`
* :ref:`missing-include`
* :ref:`useless-referenced-argument`
* :ref:`useless-catch`
* :ref:`possible-infinite-loop`
* :ref:`test-then-cast`
* :ref:`foreach-on-object`
* :ref:`property-could-be-local`
* :ref:`too-many-native-calls`
* :ref:`redefined-private-property`
* :ref:`don't-unset-properties`
* :ref:`strtr-arguments`
* :ref:`missing-parenthesis`
* :ref:`callback-function-needs-return`
* :ref:`wrong-range-check`
* :ref:`cant-instantiate-class`
* :ref:`strpos()-too-much`
* :ref:`typehinted-references`
* :ref:`weak-typing`
* :ref:`method-signature-must-be-compatible`
* :ref:`mismatch-type-and-default`
* :ref:`check-json`
* :ref:`dont-mix-++`
* :ref:`can't-throw-throwable`
* :ref:`abstract-or-implements`
* :ref:`incompatible-signature-methods`
* :ref:`ambiguous-visibilities`
* :ref:`undefined-class`
* :ref:`assert-function-is-reserved`
* :ref:`could-be-abstract-class`
* :ref:`continue-is-for-loop`
* :ref:`must-call-parent-constructor`
* :ref:`undefined-variable`
* :ref:`undefined-insteadof`
* :ref:`method-collision-traits`
* :ref:`class-could-be-final`
* :ref:`inconsistent-elseif`
* :ref:`only-variable-for-reference`
* :ref:`wrong-access-style-to-property`
* :ref:`invalid-pack-format`
* :ref:`repeated-interface`
* :ref:`don't-read-and-write-in-one-expression`
* :ref:`should-yield-with-key`
* :ref:`useless-alias`
* :ref:`method-could-be-static`
* :ref:`possible-missing-subpattern`
* :ref:`assign-and-compare`
* :ref:`variable-is-not-a-condition`
* :ref:`insufficient-typehint`
* :ref:`typehint-must-be-returned`
* :ref:`clone-with-non-object`
* :ref:`check-on-\_\_call-usage`
* :ref:`avoid-option-arrays-in-constructors`
* :ref:`already-parents-trait`
* :ref:`trait-not-found`
* :ref:`casting-ternary`
* :ref:`concat-empty-string`
* :ref:`concat-and-addition`
* :ref:`no-append-on-source`
* :ref:`memoize-magiccall`
* :ref:`unused-class-constant`
* :ref:`infinite-recursion`
* :ref:`null-or-boolean-arrays`
* :ref:`dependant-abstract-classes`
* :ref:`wrong-type-returned`
* :ref:`overwritten-source-and-value`
* :ref:`avoid-mb\_dectect\_encoding()`
* :ref:`array\_key\_exists()-works-on-arrays`
* :ref:`class-without-parent`
* :ref:`scalar-are-not-arrays`
* :ref:`array\_merge()-and-variadic`
* :ref:`implode()-arguments-order`
* :ref:`strip\_tags-skips-closed-tag`
* :ref:`no-spread-for-hash`
* :ref:`max-level-of-nesting`
* :ref:`should-use-explode-args`
* :ref:`use-array\_slice()`
* :ref:`too-many-array-dimensions`
* :ref:`coalesce-and-concat`
* :ref:`comparison-is-always-true`
* :ref:`incompatible-signature-methods-with-covariance`
* :ref:`interfaces-is-not-implemented`
* :ref:`no-literal-for-reference`
* :ref:`interfaces-don't-ensure-properties`
* :ref:`non-nullable-getters`
* :ref:`too-many-dereferencing`
* :ref:`cant-implement-traversable`
* :ref:`is\_a()-with-string`
* :ref:`mbstring-unknown-encoding`
* :ref:`mbstring-third-arg`
* :ref:`merge-if-then`
* :ref:`wrong-type-with-call`
* :ref:`not-equal-is-not-!==`
* :ref:`dont-collect-void`
* :ref:`wrong-typed-property-default`
* :ref:`hidden-nullable-typehint`
* :ref:`fn-argument-variable-confusion`
* :ref:`missing-abstract-method`
* :ref:`undefined-constant-name`
* :ref:`using-deprecated-method`
* :ref:`cyclic-references`
* :ref:`double-object-assignation`
* :ref:`wrong-argument-type`
* :ref:`mismatch-properties-typehints`
* :ref:`no-need-for-triple-equal`
* :ref:`array\_merge-needs-array-of-arrays`
* :ref:`wrong-type-for-native-php-function`
* :ref:`catch-undefined-variable`
* :ref:`swapped-arguments`
* :ref:`different-argument-counts`
* :ref:`unknown-parameter-name`
* :ref:`missing-some-returntype`
* :ref:`don't-pollute-global-space`
* :ref:`mismatch-parameter-name`
* :ref:`multiple-declaration-of-strict\_types`
* :ref:`mismatch-parameter-and-type`
* :ref:`array\_fill()-with-objects`
* :ref:`modified-typed-parameter`
* :ref:`assumptions`
* :ref:`unsupported-types-with-operators`
* :ref:`wrong-attribute-configuration`
* :ref:`cancelled-parameter`
* :ref:`constant-typo-looks-like-a-variable`
* :ref:`array\_map()-passes-by-value`
* :ref:`missing-\_\_isset()-method`
* :ref:`modify-immutable`
* :ref:`only-container-for-reference`
* :ref:`cannot-use-static-for-closure`
* :ref:`only-first-byte-`
* :ref:`inherited-property-type-must-match`
* :ref:`no-object-as-index`
* :ref:`htmlentities-using-default-flag`
* :ref:`wrong-argument-name-with-php-function`
* :ref:`duplicate-named-parameter`
* :ref:`php-native-class-type-compatibility`
* :ref:`missing-attribute-attribute`
* :ref:`no-null-for-native-php-functions`
* :ref:`no-referenced-void`
* :ref:`jsonserialize()-mixed-return-type`
* :ref:`new-functions-in-php-8.1`
* :ref:`never-keyword`
* :ref:`false-to-array-conversion`
* :ref:`float-conversion-as-index`
* :ref:`cannot-call-trait-method-directly`
* :ref:`overwritten-foreach-var`
* :ref:`variables-recycledvariables`
* :ref:`check-division-by-zero`



Specs
^^^^^
+--------------+----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| Short name   | Analyze                                                                                                                                                                                |
+--------------+----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| Available in | `Entreprise Edition <https://www.exakat.io/entreprise-edition>`_, `Community Edition <https://www.exakat.io/community-edition>`_, `Exakat Cloud <https://www.exakat.io/exakat-cloud>`_ |
+--------------+----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| Reports      | :ref:`report-ambassador`, :ref:`report-diplomat`                                                                                                                                       |
+--------------+----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+


.. _ruleset-appinfo:

Appinfo
+++++++

A set of rules that describes with PHP features is used in the code.

Total : 383 analysis

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
* :ref:`const-visibility-usage`
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
* :ref:`class-overreach`
* :ref:`final-constant`
* :ref:`never-typehint-usage`
* :ref:`named-parameter-usage`
* :ref:`first-class-callable`
* :ref:`never-keyword`
* :ref:`mixed-typehint-usage`
* :ref:`nested-attributes`
* :ref:`new-initializers`
* :ref:`promoted-properties`
* :ref:`intersection-typehint`



Specs
^^^^^
+--------------+----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| Short name   | Appinfo                                                                                                                                                                                |
+--------------+----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| Available in | `Entreprise Edition <https://www.exakat.io/entreprise-edition>`_, `Community Edition <https://www.exakat.io/community-edition>`_, `Exakat Cloud <https://www.exakat.io/exakat-cloud>`_ |
+--------------+----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| Reports      | :ref:`report-diplomat`, :ref:`report-ambassador`                                                                                                                                       |
+--------------+----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+


.. _ruleset-attributes:

Attributes
++++++++++

This ruleset gathers all rules that rely on PHP 8.+ attributes.

Total : 4 analysis

* :ref:`exit-like-methods`
* :ref:`using-deprecated-method`
* :ref:`modify-immutable`
* :ref:`missing-attribute-attribute`



Specs
^^^^^
+--------------+------------------------------------------------------------------------------------------------------------------------+
| Short name   | Attributes                                                                                                             |
+--------------+------------------------------------------------------------------------------------------------------------------------+
| Available in | `Entreprise Edition <https://www.exakat.io/entreprise-edition>`_, `Exakat Cloud <https://www.exakat.io/exakat-cloud>`_ |
+--------------+------------------------------------------------------------------------------------------------------------------------+


.. _ruleset-ce:

CE
++

This ruleset is the Community Edition list. It holds all the analysis that are in the community edition version of Exakat.

Total : 656 analysis

* :ref:`adding-zero`
* :ref:`array-index`
* :ref:`multidimensional-arrays`
* :ref:`multiple-index-definition`
* :ref:`php-arrays-index`
* :ref:`classes-names`
* :ref:`constant-definition`
* :ref:`magic-methods`
* :ref:`forgotten-visibility`
* :ref:`non-static-methods-called-in-a-static`
* :ref:`old-style-constructor`
* :ref:`static-methods`
* :ref:`static-methods-called-from-object`
* :ref:`static-properties`
* :ref:`constants-with-strange-names`
* :ref:`constants-usage`
* :ref:`constants-names`
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
* :ref:`error\_reporting()-with-integers`
* :ref:`eval()-usage`
* :ref:`exit()-usage`
* :ref:`forgotten-whitespace`
* :ref:`multiply-by-one`
* :ref:`@-operator`
* :ref:`not-not`
* :ref:`include\_once()-usage`
* :ref:`using-short-tags`
* :ref:`strpos()-like-comparison`
* :ref:`throws-an-assignement`
* :ref:`var\_dump()...-usage`
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
* :ref:`useless-instructions`
* :ref:`multiple-constant-definition`
* :ref:`wrong-optional-parameter`
* :ref:`use-===-null`
* :ref:`assertions`
* :ref:`one-variable-string`
* :ref:`cast-usage`
* :ref:`function-subscripting`
* :ref:`nested-loops`
* :ref:`I?=-usage`
* :ref:`static-methods-can't-contain-$this`
* :ref:`while(list()-=-each())`
* :ref:`multiples-identical-case`
* :ref:`switch-without-default`
* :ref:`nested-ternary`
* :ref:`undefined-constants`
* :ref:`custom-constant-usage`
* :ref:`ext-pcntl`
* :ref:`ext-ming`
* :ref:`ext-redis`
* :ref:`is-an-extension-function`
* :ref:`is-an-extension-interface`
* :ref:`is-an-extension-constant`
* :ref:`htmlentities-calls`
* :ref:`defined-class-constants`
* :ref:`undefined-class-constants`
* :ref:`used-once-variables-(in-scope)`
* :ref:`undefined-functions`
* :ref:`deprecated-php-functions`
* :ref:`dangling-array-references`
* :ref:`ext-cyrus`
* :ref:`ext-sqlsrv`
* :ref:`aliases-usage`
* :ref:`uses-default-values`
* :ref:`wrong-number-of-arguments`
* :ref:`ellipsis-usage`
* :ref:`use-const`
* :ref:`ext-0mq`
* :ref:`ext-memcache`
* :ref:`ext-memcached`
* :ref:`is-extension-trait`
* :ref:`dynamic-function-call`
* :ref:`has-variable-arguments`
* :ref:`multiple-catch`
* :ref:`dynamically-called-classes`
* :ref:`conditioned-function`
* :ref:`conditioned-constants`
* :ref:`is-generator`
* :ref:`try-with-finally`
* :ref:`dereferencing-string-and-arrays`
* :ref:`list()-may-omit-variables`
* :ref:`or-die`
* :ref:`constant-scalar-expressions`
* :ref:`exit-like-methods`
* :ref:`must-return-methods`
* :ref:`ext-imagick`
* :ref:`ext-oci8`
* :ref:`overwritten-exceptions`
* :ref:`foreach-reference-is-not-modified`
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
* :ref:`is-php-constant`
* :ref:`ext-expect`
* :ref:`defined-properties`
* :ref:`undefined-properties`
* :ref:`has-magic-method`
* :ref:`ext-recode`
* :ref:`ext-parsekit`
* :ref:`ext-runkit`
* :ref:`ext-gettext`
* :ref:`strict-comparison-with-booleans`
* :ref:`lone-blocks`
* :ref:`super-global-usage`
* :ref:`global-usage`
* :ref:`logical-should-use-symbolic-operators`
* :ref:`namespaces`
* :ref:`deep-definitions`
* :ref:`constant-class`
* :ref:`not-definitions-only`
* :ref:`repeated-print()`
* :ref:`avoid-parenthesis`
* :ref:`objects-don't-need-references`
* :ref:`no-real-comparison`
* :ref:`usage-of-class\_alias()`
* :ref:`ext-apache`
* :ref:`ext-eaccelerator`
* :ref:`ext-fpm`
* :ref:`ext-iis`
* :ref:`ext-xcache`
* :ref:`ext-wincache`
* :ref:`no-direct-call-to-magic-method`
* :ref:`useless-final`
* :ref:`use-constant`
* :ref:`resources-usage`
* :ref:`useless-unset`
* :ref:`no-array\_merge()-in-loops`
* :ref:`useless-parenthesis`
* :ref:`shell-usage`
* :ref:`file-usage`
* :ref:`mail-usage`
* :ref:`dynamic-calls`
* :ref:`use-php-object-api`
* :ref:`altering-foreach-without-reference`
* :ref:`test-class`
* :ref:`mark-callable`
* :ref:`use-pathinfo`
* :ref:`ext-dio`
* :ref:`no-parenthesis-for-language-construct`
* :ref:`ext-phalcon`
* :ref:`use-constant-as-arguments`
* :ref:`implied-if`
* :ref:`composer-usage`
* :ref:`composer's-autoload`
* :ref:`composer-namespace`
* :ref:`should-chain-exception`
* :ref:`undefined-interfaces`
* :ref:`ext-apcu`
* :ref:`should-use-prepared-statement`
* :ref:`print-and-die`
* :ref:`unchecked-resources`
* :ref:`ext-trader`
* :ref:`ext-mailparse`
* :ref:`ext-mail`
* :ref:`else-if-versus-elseif`
* :ref:`multiple-class-declarations`
* :ref:`empty-namespace`
* :ref:`could-use-short-assignation`
* :ref:`scalar-typehint-usage`
* :ref:`return-typehint-usage`
* :ref:`ext-ob`
* :ref:`pre-increment`
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
* :ref:`indices-are-int-or-string`
* :ref:`should-typecast`
* :ref:`else-usage`
* :ref:`is-composer-class`
* :ref:`is-composer-interface`
* :ref:`avoid-substr()-one`
* :ref:`anonymous-classes`
* :ref:`coalesce`
* :ref:`directives-usage`
* :ref:`useless-brackets`
* :ref:`preg\_replace-with-option-e`
* :ref:`eval()-without-try`
* :ref:`is-not-class-family`
* :ref:`global-in-global`
* :ref:`ext-fann`
* :ref:`use-web`
* :ref:`use-cli`
* :ref:`avoid-get\_class()`
* :ref:`silently-cast-integer`
* :ref:`error-messages`
* :ref:`timestamp-difference`
* :ref:`php7-relaxed-keyword`
* :ref:`ext-pecl\_http`
* :ref:`uses-environment`
* :ref:`wrong-parameter-type`
* :ref:`redefined-methods`
* :ref:`redefined-class-constants`
* :ref:`redefined-default`
* :ref:`wrong-fopen()-mode`
* :ref:`is-cli-script`
* :ref:`php-bugfixes`
* :ref:`negative-power`
* :ref:`use-random\_int()`
* :ref:`ternary-in-concat`
* :ref:`ext-tokyotyrant`
* :ref:`ext-v8js`
* :ref:`yield-usage`
* :ref:`yield-from-usage`
* :ref:`pear-usage`
* :ref:`undefined-trait`
* :ref:`identical-conditions`
* :ref:`unkown-regex-options`
* :ref:`no-choice`
* :ref:`logical-mistakes`
* :ref:`ext-lua`
* :ref:`same-conditions-in-condition`
* :ref:`return-true-false`
* :ref:`could-use-\_\_dir\_\_`
* :ref:`should-use-coalesce`
* :ref:`list-with-keys`
* :ref:`if-with-same-conditions`
* :ref:`ext-suhosin`
* :ref:`throw-functioncall`
* :ref:`can't-disable-function`
* :ref:`functions-using-reference`
* :ref:`use-instanceof`
* :ref:`list-short-syntax`
* :ref:`results-may-be-missing`
* :ref:`use-nullable-type`
* :ref:`always-positive-comparison`
* :ref:`multiple-exceptions-catch()`
* :ref:`empty-blocks`
* :ref:`throw-in-destruct`
* :ref:`use-system-tmp`
* :ref:`hidden-use-expression`
* :ref:`should-make-alias`
* :ref:`multiple-identical-trait-or-interface`
* :ref:`multiple-alias-definitions`
* :ref:`failed-substr-comparison`
* :ref:`should-make-ternary`
* :ref:`drop-else-after-return`
* :ref:`use-class-operator`
* :ref:`ext-rar`
* :ref:`don't-echo-error`
* :ref:`useless-type-casting`
* :ref:`no-isset()-with-empty()`
* :ref:`useless-check`
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
* :ref:`multiple-alias-definitions-per-file`
* :ref:`\_\_dir\_\_-then-slash`
* :ref:`ext-ds`
* :ref:`use-cookies`
* :ref:`group-use-declaration`
* :ref:`repeated-regex`
* :ref:`no-class-in-global`
* :ref:`could-use-str\_repeat()`
* :ref:`strings-with-strange-space`
* :ref:`no-empty-regex`
* :ref:`ext-sphinx`
* :ref:`try-with-multiple-catch`
* :ref:`ext-grpc`
* :ref:`use-browscap`
* :ref:`use-debug`
* :ref:`no-reference-on-left-side`
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
* :ref:`assign-with-and-precedence`
* :ref:`no-magic-method-with-array`
* :ref:`ext-xattr`
* :ref:`ext-rdkafka`
* :ref:`ext-fam`
* :ref:`ext-parle`
* :ref:`regex-inventory`
* :ref:`is-actually-zero`
* :ref:`unconditional-break-in-loop`
* :ref:`too-complex-expression`
* :ref:`is-a-php-magic-property`
* :ref:`next-month-trap`
* :ref:`printf-number-of-arguments`
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
* :ref:`invalid-regex`
* :ref:`same-variable-foreach`
* :ref:`ext-igbinary`
* :ref:`identical-on-both-sides`
* :ref:`no-reference-for-ternary`
* :ref:`unused-inherited-variable-in-closure`
* :ref:`fallback-function`
* :ref:`useless-catch`
* :ref:`ext-hrtime`
* :ref:`ext-xxtea`
* :ref:`ext-uopz`
* :ref:`ext-varnish`
* :ref:`ext-opencensus`
* :ref:`ext-leveldb`
* :ref:`ext-db2`
* :ref:`don't-unset-properties`
* :ref:`strtr-arguments`
* :ref:`missing-parenthesis`
* :ref:`callback-function-needs-return`
* :ref:`ext-zookeeper`
* :ref:`ext-cmark`
* :ref:`strpos()-too-much`
* :ref:`typehinted-references`
* :ref:`check-json`
* :ref:`ext-eio`
* :ref:`ext-csprng`
* :ref:`undefined-class`
* :ref:`ext-lzf`
* :ref:`ext-msgpack`
* :ref:`case-insensitive-constants`
* :ref:`handle-arrays-with-callback`
* :ref:`detect-current-class`
* :ref:`trailing-comma-in-calls`
* :ref:`undefined-variable`
* :ref:`undefined-insteadof`
* :ref:`can't-disable-class`
* :ref:`ext-seaslog`
* :ref:`wrong-access-style-to-property`
* :ref:`invalid-pack-format`
* :ref:`don't-read-and-write-in-one-expression`
* :ref:`pack-format-inventory`
* :ref:`printf-format-inventory`
* :ref:`idn\_to\_ascii()-new-default`
* :ref:`ext-decimal`
* :ref:`ext-psr`
* :ref:`should-yield-with-key`
* :ref:`useless-alias`
* :ref:`ext-sdl`
* :ref:`ext-async`
* :ref:`ext-wasm`
* :ref:`path-lists`
* :ref:`possible-missing-subpattern`
* :ref:`assign-and-compare`
* :ref:`typed-property-usage`
* :ref:`ext-weakref`
* :ref:`ext-pcov`
* :ref:`constant-dynamic-creation`
* :ref:`php-8.0-removed-functions`
* :ref:`php-8.0-removed-constants`
* :ref:`an-oop-factory`
* :ref:`typehint-must-be-returned`
* :ref:`self-transforming-variables`
* :ref:`check-on-\_\_call-usage`
* :ref:`php-overridden-function`
* :ref:`ext-svm`
* :ref:`ext-ffi`
* :ref:`ext-password`
* :ref:`ext-zend\_monitor`
* :ref:`ext-uuid`
* :ref:`casting-ternary`
* :ref:`concat-and-addition`
* :ref:`new-functions-in-php-7.4`
* :ref:`curl\_version()-has-no-argument`
* :ref:`php-7.4-new-class`
* :ref:`new-constants-in-php-7.4`
* :ref:`wrong-type-returned`
* :ref:`cant-use-function`
* :ref:`php-7.4-removed-functions`
* :ref:`mb\_strrpos()-third-argument`
* :ref:`array\_key\_exists()-works-on-arrays`
* :ref:`reflection-export()-is-deprecated`
* :ref:`unbinding-closures`
* :ref:`numeric-literal-separator`
* :ref:`class-without-parent`
* :ref:`scalar-are-not-arrays`
* :ref:`create-compact-variables`
* :ref:`php-7.4-reserved-keyword`
* :ref:`no-more-curly-arrays`
* :ref:`overwritten-properties`
* :ref:`overwritten-constant`
* :ref:`create-magic-property`
* :ref:`set-parent-definition`
* :ref:`make-class-constant-definition`
* :ref:`follow-closure-definition`
* :ref:`php-7.4-constant-deprecation`
* :ref:`implode()-arguments-order`
* :ref:`php-7.4-removed-directives`
* :ref:`hash-algorithms-incompatible-with-php-7.4-`
* :ref:`openssl\_random\_pseudo\_byte()-second-argument`
* :ref:`strip\_tags-skips-closed-tag`
* :ref:`use-covariance`
* :ref:`use-contravariance`
* :ref:`seta-rray-class-definition`
* :ref:`set-string-method-definition`
* :ref:`use-arrow-functions`
* :ref:`environment-variable-usage`
* :ref:`indentation-levels`
* :ref:`spread-operator-for-array`
* :ref:`nested-ternary-without-parenthesis`
* :ref:`cyclomatic-complexity`
* :ref:`should-use-explode-args`
* :ref:`use-array\_slice()`
* :ref:`coalesce-and-concat`
* :ref:`interfaces-is-not-implemented`
* :ref:`no-literal-for-reference`
* :ref:`collect-literals`
* :ref:`no-weak-ssl-crypto`
* :ref:`collect-parameter-counts`
* :ref:`collect-local-variable-counts`
* :ref:`dereferencing-levels`
* :ref:`make-functioncall-with-reference`
* :ref:`foreach()-favorite`
* :ref:`cant-implement-traversable`
* :ref:`is\_a()-with-string`
* :ref:`mbstring-unknown-encoding`
* :ref:`collect-mbstring-encodings`
* :ref:`filter-to-add\_slashes()`
* :ref:`mbstring-third-arg`
* :ref:`typehinting-stats`
* :ref:`typo-3-usage`
* :ref:`concrete-usage`
* :ref:`immutable-signature`
* :ref:`merge-if-then`
* :ref:`wrong-type-with-call`
* :ref:`shell-commands`
* :ref:`inclusions`
* :ref:`typehint-order`
* :ref:`new-order`
* :ref:`links-between-parameter-and-argument`
* :ref:`collect-class-interface-counts`
* :ref:`collect-class-depth`
* :ref:`collect-class-children-count`
* :ref:`not-equal-is-not-!==`
* :ref:`constant-order`
* :ref:`php-8.0-variable-syntax-tweaks`
* :ref:`new-functions-in-php-8.0`
* :ref:`php-8.0-only-typehints`
* :ref:`union-typehint`
* :ref:`wrong-typed-property-default`
* :ref:`signature-trailing-comma`
* :ref:`throw-was-an-expression`
* :ref:`collect-property-counts`
* :ref:`collect-method-counts`
* :ref:`collect-class-constant-counts`
* :ref:`could-be-string`
* :ref:`could-be-boolean`
* :ref:`could-be-array-typehint`
* :ref:`could-be-cit`
* :ref:`protocol-lists`
* :ref:`could-be-integer`
* :ref:`call-order`
* :ref:`could-be-null`
* :ref:`uses-php-8-match()`
* :ref:`could-be-float`
* :ref:`collect-parameter-names`
* :ref:`wrong-type-for-native-php-function`
* :ref:`dump-fossilizedmethods`
* :ref:`collect-static-class-changes`
* :ref:`use-php-attributes`
* :ref:`use-nullsafe-operator`
* :ref:`use-closure-trailing-comma`
* :ref:`unknown-parameter-name`
* :ref:`missing-some-returntype`
* :ref:`collect-variables`
* :ref:`collect-global-variables`
* :ref:`collect-readability`
* :ref:`collect-definitions-statistics`
* :ref:`collect-class-traits-counts`
* :ref:`collect-native-calls-per-expressions`
* :ref:`function-with-dynamic-code`
* :ref:`cast-unset-usage`
* :ref:`$php\_errormsg-usage`
* :ref:`mismatch-parameter-name`
* :ref:`collect-files-dependencies`
* :ref:`collect-atom-counts`
* :ref:`collect-classes-dependencies`
* :ref:`collect-php-structures`
* :ref:`collect-use-counts`
* :ref:`php-8.0-removed-directives`
* :ref:`unsupported-types-with-operators`
* :ref:`negative-start-index-in-array`
* :ref:`nullable-with-constant`
* :ref:`php-8.0-resources-turned-into-objects`
* :ref:`php-80-named-parameter-variadic`
* :ref:`final-private-methods`
* :ref:`array\_map()-passes-by-value`
* :ref:`php-8.1-removed-directives`
* :ref:`htmlentities-using-default-flag`



Specs
^^^^^
+--------------+------------------------------------------------------------------------------------------------------------------------+
| Short name   | CE                                                                                                                     |
+--------------+------------------------------------------------------------------------------------------------------------------------+
| Available in | `Entreprise Edition <https://www.exakat.io/entreprise-edition>`_, `Exakat Cloud <https://www.exakat.io/exakat-cloud>`_ |
+--------------+------------------------------------------------------------------------------------------------------------------------+


.. _ruleset-ci-checks:

CI-checks
+++++++++

This ruleset is a collection of important rules to run in a CI pipeline.

Total : 178 analysis

* :ref:`adding-zero`
* :ref:`multiple-index-definition`
* :ref:`forgotten-visibility`
* :ref:`non-static-methods-called-in-a-static`
* :ref:`static-methods-called-from-object`
* :ref:`constants-with-strange-names`
* :ref:`redeclared-php-functions`
* :ref:`error\_reporting()-with-integers`
* :ref:`exit()-usage`
* :ref:`forgotten-whitespace`
* :ref:`multiply-by-one`
* :ref:`@-operator`
* :ref:`not-not`
* :ref:`strpos()-like-comparison`
* :ref:`throws-an-assignement`
* :ref:`var\_dump()...-usage`
* :ref:`useless-instructions`
* :ref:`multiple-constant-definition`
* :ref:`wrong-optional-parameter`
* :ref:`use-===-null`
* :ref:`one-variable-string`
* :ref:`static-methods-can't-contain-$this`
* :ref:`while(list()-=-each())`
* :ref:`multiples-identical-case`
* :ref:`switch-without-default`
* :ref:`nested-ternary`
* :ref:`undefined-constants`
* :ref:`htmlentities-calls`
* :ref:`undefined-class-constants`
* :ref:`undefined-functions`
* :ref:`deprecated-php-functions`
* :ref:`dangling-array-references`
* :ref:`aliases-usage`
* :ref:`uses-default-values`
* :ref:`wrong-number-of-arguments`
* :ref:`use-const`
* :ref:`list()-may-omit-variables`
* :ref:`or-die`
* :ref:`must-return-methods`
* :ref:`overwritten-exceptions`
* :ref:`foreach-reference-is-not-modified`
* :ref:`undefined-properties`
* :ref:`strict-comparison-with-booleans`
* :ref:`lone-blocks`
* :ref:`logical-should-use-symbolic-operators`
* :ref:`repeated-print()`
* :ref:`avoid-parenthesis`
* :ref:`objects-don't-need-references`
* :ref:`no-real-comparison`
* :ref:`no-direct-call-to-magic-method`
* :ref:`useless-final`
* :ref:`use-constant`
* :ref:`useless-unset`
* :ref:`no-array\_merge()-in-loops`
* :ref:`useless-parenthesis`
* :ref:`use-php-object-api`
* :ref:`altering-foreach-without-reference`
* :ref:`use-pathinfo`
* :ref:`no-parenthesis-for-language-construct`
* :ref:`use-constant-as-arguments`
* :ref:`implied-if`
* :ref:`should-chain-exception`
* :ref:`undefined-interfaces`
* :ref:`should-use-prepared-statement`
* :ref:`print-and-die`
* :ref:`unchecked-resources`
* :ref:`else-if-versus-elseif`
* :ref:`multiple-class-declarations`
* :ref:`empty-namespace`
* :ref:`could-use-short-assignation`
* :ref:`pre-increment`
* :ref:`indices-are-int-or-string`
* :ref:`should-typecast`
* :ref:`avoid-substr()-one`
* :ref:`useless-brackets`
* :ref:`preg\_replace-with-option-e`
* :ref:`eval()-without-try`
* :ref:`avoid-get\_class()`
* :ref:`silently-cast-integer`
* :ref:`timestamp-difference`
* :ref:`wrong-parameter-type`
* :ref:`redefined-class-constants`
* :ref:`redefined-default`
* :ref:`wrong-fopen()-mode`
* :ref:`negative-power`
* :ref:`use-random\_int()`
* :ref:`ternary-in-concat`
* :ref:`undefined-trait`
* :ref:`identical-conditions`
* :ref:`no-choice`
* :ref:`logical-mistakes`
* :ref:`same-conditions-in-condition`
* :ref:`return-true-false`
* :ref:`could-use-\_\_dir\_\_`
* :ref:`should-use-coalesce`
* :ref:`if-with-same-conditions`
* :ref:`throw-functioncall`
* :ref:`use-instanceof`
* :ref:`results-may-be-missing`
* :ref:`always-positive-comparison`
* :ref:`empty-blocks`
* :ref:`throw-in-destruct`
* :ref:`use-system-tmp`
* :ref:`hidden-use-expression`
* :ref:`should-make-alias`
* :ref:`multiple-identical-trait-or-interface`
* :ref:`multiple-alias-definitions`
* :ref:`failed-substr-comparison`
* :ref:`should-make-ternary`
* :ref:`drop-else-after-return`
* :ref:`use-class-operator`
* :ref:`don't-echo-error`
* :ref:`useless-type-casting`
* :ref:`no-isset()-with-empty()`
* :ref:`useless-check`
* :ref:`multiple-alias-definitions-per-file`
* :ref:`\_\_dir\_\_-then-slash`
* :ref:`repeated-regex`
* :ref:`no-class-in-global`
* :ref:`could-use-str\_repeat()`
* :ref:`strings-with-strange-space`
* :ref:`no-empty-regex`
* :ref:`no-reference-on-left-side`
* :ref:`assign-with-and-precedence`
* :ref:`no-magic-method-with-array`
* :ref:`is-actually-zero`
* :ref:`unconditional-break-in-loop`
* :ref:`next-month-trap`
* :ref:`printf-number-of-arguments`
* :ref:`invalid-regex`
* :ref:`same-variable-foreach`
* :ref:`identical-on-both-sides`
* :ref:`no-reference-for-ternary`
* :ref:`unused-inherited-variable-in-closure`
* :ref:`useless-catch`
* :ref:`don't-unset-properties`
* :ref:`strtr-arguments`
* :ref:`missing-parenthesis`
* :ref:`callback-function-needs-return`
* :ref:`strpos()-too-much`
* :ref:`typehinted-references`
* :ref:`check-json`
* :ref:`undefined-class`
* :ref:`undefined-variable`
* :ref:`undefined-insteadof`
* :ref:`wrong-access-style-to-property`
* :ref:`invalid-pack-format`
* :ref:`should-yield-with-key`
* :ref:`useless-alias`
* :ref:`possible-missing-subpattern`
* :ref:`assign-and-compare`
* :ref:`typehint-must-be-returned`
* :ref:`check-on-\_\_call-usage`
* :ref:`casting-ternary`
* :ref:`concat-and-addition`
* :ref:`wrong-type-returned`
* :ref:`class-without-parent`
* :ref:`scalar-are-not-arrays`
* :ref:`implode()-arguments-order`
* :ref:`strip\_tags-skips-closed-tag`
* :ref:`should-use-explode-args`
* :ref:`use-array\_slice()`
* :ref:`coalesce-and-concat`
* :ref:`interfaces-is-not-implemented`
* :ref:`no-literal-for-reference`
* :ref:`cant-implement-traversable`
* :ref:`is\_a()-with-string`
* :ref:`mbstring-unknown-encoding`
* :ref:`mbstring-third-arg`
* :ref:`merge-if-then`
* :ref:`wrong-type-with-call`
* :ref:`not-equal-is-not-!==`
* :ref:`wrong-typed-property-default`
* :ref:`wrong-type-for-native-php-function`
* :ref:`unknown-parameter-name`
* :ref:`missing-some-returntype`
* :ref:`htmlentities-using-default-flag`
* :ref:`wrong-argument-name-with-php-function`



Specs
^^^^^
+--------------+------------------------------------------------------------------------------------------------------------------------+
| Short name   | CI-checks                                                                                                              |
+--------------+------------------------------------------------------------------------------------------------------------------------+
| Available in | `Entreprise Edition <https://www.exakat.io/entreprise-edition>`_, `Exakat Cloud <https://www.exakat.io/exakat-cloud>`_ |
+--------------+------------------------------------------------------------------------------------------------------------------------+


.. _ruleset-classreview:

ClassReview
+++++++++++

This ruleset focuses on classes construction issues, and their related structures : traits, interfaces, methods, properties, constants.

Total : 59 analysis

* :ref:`final-class-usage`
* :ref:`final-methods-usage`
* :ref:`classes-mutually-extending-each-other`
* :ref:`could-use-self`
* :ref:`constant-class`
* :ref:`redefined-property`
* :ref:`useless-interfaces`
* :ref:`could-be-class-constant`
* :ref:`could-be-static`
* :ref:`no-self-referencing-constant`
* :ref:`property-could-be-private-property`
* :ref:`class-should-be-final-by-ocramius`
* :ref:`could-be-protected-property`
* :ref:`raised-access-level`
* :ref:`could-be-private-class-constant`
* :ref:`could-be-protected-class-constant`
* :ref:`method-could-be-private-method`
* :ref:`could-be-protected-method`
* :ref:`property-could-be-local`
* :ref:`could-be-abstract-class`
* :ref:`class-could-be-final`
* :ref:`wrong-access-style-to-property`
* :ref:`unreachable-class-constant`
* :ref:`avoid-self-in-interface`
* :ref:`self-using-trait`
* :ref:`method-could-be-static`
* :ref:`avoid-option-arrays-in-constructors`
* :ref:`memoize-magiccall`
* :ref:`unused-class-constant`
* :ref:`dependant-abstract-classes`
* :ref:`wrong-type-returned`
* :ref:`disconnected-classes`
* :ref:`class-without-parent`
* :ref:`interfaces-is-not-implemented`
* :ref:`interfaces-don't-ensure-properties`
* :ref:`non-nullable-getters`
* :ref:`insufficient-property-typehint`
* :ref:`exceeding-typehint`
* :ref:`nullable-without-check`
* :ref:`fossilized-method`
* :ref:`uninitialized-property`
* :ref:`wrong-typed-property-default`
* :ref:`hidden-nullable-typehint`
* :ref:`missing-abstract-method`
* :ref:`unused-trait-in-class`
* :ref:`cyclic-references`
* :ref:`double-object-assignation`
* :ref:`mismatch-properties-typehints`
* :ref:`different-argument-counts`
* :ref:`could-be-parent-method`
* :ref:`cancel-common-method`
* :ref:`modified-typed-parameter`
* :ref:`useless-typehint`
* :ref:`could-be-stringable`
* :ref:`final-private-methods`
* :ref:`missing-\_\_isset()-method`
* :ref:`no-static-variable-in-a-method`
* :ref:`inherited-property-type-must-match`
* :ref:`abstract-class-constants`



Specs
^^^^^
+--------------+------------------------------------------------------------------------------------------------------------------------+
| Short name   | ClassReview                                                                                                            |
+--------------+------------------------------------------------------------------------------------------------------------------------+
| Available in | `Entreprise Edition <https://www.exakat.io/entreprise-edition>`_, `Exakat Cloud <https://www.exakat.io/exakat-cloud>`_ |
+--------------+------------------------------------------------------------------------------------------------------------------------+


.. _ruleset-coding-conventions:

Coding conventions
++++++++++++++++++

This ruleset centralizes all analysis related to coding conventions. Sometimes, those are easy to extract with static analysis, and so here they are. No all o them are available.

Total : 0 analysis

* 



Specs
^^^^^
+--------------+------------------------------------------------------------------------------------------------------------------------+
| Short name   | Coding conventions                                                                                                     |
+--------------+------------------------------------------------------------------------------------------------------------------------+
| Available in | `Entreprise Edition <https://www.exakat.io/entreprise-edition>`_, `Exakat Cloud <https://www.exakat.io/exakat-cloud>`_ |
+--------------+------------------------------------------------------------------------------------------------------------------------+


.. _ruleset-compatibilityphp53:

CompatibilityPHP53
++++++++++++++++++

This ruleset centralizes all analysis for the migration from PHP 5.2 to 5.3.

Total : 86 analysis

* :ref:`non-static-methods-called-in-a-static`
* :ref:`ext-dba`
* :ref:`ext-fdf`
* :ref:`use-lower-case-for-parent,-static-and-self`
* :ref:`break-with-0`
* :ref:`binary-glossary`
* :ref:`malformed-octal`
* :ref:`short-syntax-for-arrays`
* :ref:`new-functions-in-php-5.4`
* :ref:`new-functions-in-php-5.5`
* :ref:`new-functions-in-php-5.6`
* :ref:`multiple-definition-of-the-same-argument`
* :ref:`function-subscripting`
* :ref:`closure-may-use-$this`
* :ref:`switch-with-too-many-default`
* :ref:`ext-ming`
* :ref:`ellipsis-usage`
* :ref:`exponent-usage`
* :ref:`dereferencing-string-and-arrays`
* :ref:`class`
* :ref:`foreach-with-list()`
* :ref:`use-const-and-functions`
* :ref:`constant-scalar-expressions`
* :ref:`\_\_debuginfo()-usage`
* :ref:`mixed-keys-arrays`
* :ref:`const-with-array`
* :ref:`methodcall-on-new`
* :ref:`hash-algorithms-incompatible-with-php-5.3`
* :ref:`class-const-with-array`
* :ref:`variable-global`
* :ref:`null-on-new`
* :ref:`isset()-with-constant`
* :ref:`anonymous-classes`
* :ref:`unicode-escape-syntax`
* :ref:`new-functions-in-php-7.0`
* :ref:`php-7.0-new-classes`
* :ref:`php-7.0-new-interfaces`
* :ref:`parenthesis-as-parameter`
* :ref:`php5-indirect-variable-expression`
* :ref:`php-7-indirect-expression`
* :ref:`unicode-escape-partial`
* :ref:`define-with-array`
* :ref:`no-list-with-string`
* :ref:`php7-dirname`
* :ref:`php7-relaxed-keyword`
* :ref:`cant-use-return-value-in-write-context`
* :ref:`php-7.1-new-class`
* :ref:`list-with-keys`
* :ref:`list-short-syntax`
* :ref:`use-nullable-type`
* :ref:`multiple-exceptions-catch()`
* :ref:`no-string-with-append`
* :ref:`group-use-declaration`
* :ref:`new-functions-in-php-7.3`
* :ref:`cant-inherit-abstract-method`
* :ref:`group-use-trailing-comma`
* :ref:`child-class-removes-typehint`
* :ref:`no-substr-minus-one`
* :ref:`integer-as-property`
* :ref:`no-get\_class()-with-null`
* :ref:`php-7.2-new-class`
* :ref:`list-with-reference`
* :ref:`php-7.3-last-empty-argument`
* :ref:`flexible-heredoc`
* :ref:`const-visibility-usage`
* :ref:`hash-algorithms-incompatible-with-php-7.1-`
* :ref:`php-7.0-scalar-typehints`
* :ref:`php-7.1-scalar-typehints`
* :ref:`php-7.2-scalar-typehints`
* :ref:`continue-is-for-loop`
* :ref:`trailing-comma-in-calls`
* :ref:`direct-call-to-\_\_clone()`
* :ref:`no-return-for-generator`
* :ref:`no-reference-for-static-property`
* :ref:`typed-property-usage`
* :ref:`concat-and-addition`
* :ref:`unpacking-inside-arrays`
* :ref:`generator-cannot-return`
* :ref:`coalesce-equal`
* :ref:`enum-usage`
* :ref:`$files-full\_path`
* :ref:`never-typehint-usage`
* :ref:`php-8.1-typehints`
* :ref:`php-8.0-typehints`
* :ref:`named-parameter-usage`
* :ref:`cant-overload-constants`



Specs
^^^^^
+--------------+------------------------------------------------------------------------------------------------------------------------+
| Short name   | CompatibilityPHP53                                                                                                     |
+--------------+------------------------------------------------------------------------------------------------------------------------+
| Available in | `Entreprise Edition <https://www.exakat.io/entreprise-edition>`_, `Exakat Cloud <https://www.exakat.io/exakat-cloud>`_ |
+--------------+------------------------------------------------------------------------------------------------------------------------+


.. _ruleset-compatibilityphp54:

CompatibilityPHP54
++++++++++++++++++

This ruleset centralizes all analysis for the migration from PHP 5.3 to 5.4.

Total : 82 analysis

* :ref:`non-static-methods-called-in-a-static`
* :ref:`use-lower-case-for-parent,-static-and-self`
* :ref:`functions-removed-in-php-5.4`
* :ref:`break-with-non-integer`
* :ref:`calltime-pass-by-reference`
* :ref:`malformed-octal`
* :ref:`new-functions-in-php-5.5`
* :ref:`new-functions-in-php-5.6`
* :ref:`multiple-definition-of-the-same-argument`
* :ref:`switch-with-too-many-default`
* :ref:`crypt()-without-salt`
* :ref:`ellipsis-usage`
* :ref:`exponent-usage`
* :ref:`dereferencing-string-and-arrays`
* :ref:`class`
* :ref:`foreach-with-list()`
* :ref:`use-const-and-functions`
* :ref:`constant-scalar-expressions`
* :ref:`\_\_debuginfo()-usage`
* :ref:`mixed-keys-arrays`
* :ref:`const-with-array`
* :ref:`hash-algorithms-incompatible-with-php-5.3`
* :ref:`hash-algorithms-incompatible-with-php-5.4-5.5`
* :ref:`class-const-with-array`
* :ref:`variable-global`
* :ref:`null-on-new`
* :ref:`isset()-with-constant`
* :ref:`anonymous-classes`
* :ref:`unicode-escape-syntax`
* :ref:`new-functions-in-php-7.0`
* :ref:`php-7.0-new-classes`
* :ref:`php-7.0-new-interfaces`
* :ref:`parenthesis-as-parameter`
* :ref:`php5-indirect-variable-expression`
* :ref:`php-7-indirect-expression`
* :ref:`unicode-escape-partial`
* :ref:`define-with-array`
* :ref:`no-list-with-string`
* :ref:`php7-dirname`
* :ref:`php7-relaxed-keyword`
* :ref:`cant-use-return-value-in-write-context`
* :ref:`php-7.1-new-class`
* :ref:`list-with-keys`
* :ref:`list-short-syntax`
* :ref:`use-nullable-type`
* :ref:`multiple-exceptions-catch()`
* :ref:`ext-mhash`
* :ref:`no-string-with-append`
* :ref:`group-use-declaration`
* :ref:`new-functions-in-php-7.3`
* :ref:`cant-inherit-abstract-method`
* :ref:`group-use-trailing-comma`
* :ref:`child-class-removes-typehint`
* :ref:`no-substr-minus-one`
* :ref:`integer-as-property`
* :ref:`no-get\_class()-with-null`
* :ref:`php-7.2-new-class`
* :ref:`list-with-reference`
* :ref:`php-7.3-last-empty-argument`
* :ref:`flexible-heredoc`
* :ref:`const-visibility-usage`
* :ref:`hash-algorithms-incompatible-with-php-7.1-`
* :ref:`php-7.0-scalar-typehints`
* :ref:`php-7.1-scalar-typehints`
* :ref:`php-7.2-scalar-typehints`
* :ref:`continue-is-for-loop`
* :ref:`trailing-comma-in-calls`
* :ref:`direct-call-to-\_\_clone()`
* :ref:`no-return-for-generator`
* :ref:`no-reference-for-static-property`
* :ref:`typed-property-usage`
* :ref:`concat-and-addition`
* :ref:`unpacking-inside-arrays`
* :ref:`generator-cannot-return`
* :ref:`coalesce-equal`
* :ref:`enum-usage`
* :ref:`$files-full\_path`
* :ref:`never-typehint-usage`
* :ref:`php-8.1-typehints`
* :ref:`php-8.0-typehints`
* :ref:`named-parameter-usage`
* :ref:`cant-overload-constants`



Specs
^^^^^
+--------------+------------------------------------------------------------------------------------------------------------------------+
| Short name   | CompatibilityPHP54                                                                                                     |
+--------------+------------------------------------------------------------------------------------------------------------------------+
| Available in | `Entreprise Edition <https://www.exakat.io/entreprise-edition>`_, `Exakat Cloud <https://www.exakat.io/exakat-cloud>`_ |
+--------------+------------------------------------------------------------------------------------------------------------------------+
| Reports      | :ref:`report-ambassador`                                                                                               |
+--------------+------------------------------------------------------------------------------------------------------------------------+


.. _ruleset-compatibilityphp55:

CompatibilityPHP55
++++++++++++++++++

This ruleset centralizes all analysis for the migration from PHP 5.4 to 5.5.

Total : 74 analysis

* :ref:`non-static-methods-called-in-a-static`
* :ref:`ext-apc`
* :ref:`ext-mysql`
* :ref:`functions-removed-in-php-5.5`
* :ref:`malformed-octal`
* :ref:`new-functions-in-php-5.6`
* :ref:`multiple-definition-of-the-same-argument`
* :ref:`switch-with-too-many-default`
* :ref:`ellipsis-usage`
* :ref:`exponent-usage`
* :ref:`use-password\_hash()`
* :ref:`use-const-and-functions`
* :ref:`constant-scalar-expressions`
* :ref:`\_\_debuginfo()-usage`
* :ref:`const-with-array`
* :ref:`hash-algorithms-incompatible-with-php-5.3`
* :ref:`hash-algorithms-incompatible-with-php-5.4-5.5`
* :ref:`class-const-with-array`
* :ref:`variable-global`
* :ref:`null-on-new`
* :ref:`isset()-with-constant`
* :ref:`anonymous-classes`
* :ref:`unicode-escape-syntax`
* :ref:`new-functions-in-php-7.0`
* :ref:`php-7.0-new-classes`
* :ref:`php-7.0-new-interfaces`
* :ref:`parenthesis-as-parameter`
* :ref:`php5-indirect-variable-expression`
* :ref:`php-7-indirect-expression`
* :ref:`unicode-escape-partial`
* :ref:`define-with-array`
* :ref:`no-list-with-string`
* :ref:`php7-dirname`
* :ref:`php7-relaxed-keyword`
* :ref:`php-7.1-new-class`
* :ref:`list-with-keys`
* :ref:`list-short-syntax`
* :ref:`use-nullable-type`
* :ref:`multiple-exceptions-catch()`
* :ref:`no-string-with-append`
* :ref:`group-use-declaration`
* :ref:`new-functions-in-php-7.3`
* :ref:`cant-inherit-abstract-method`
* :ref:`group-use-trailing-comma`
* :ref:`child-class-removes-typehint`
* :ref:`no-substr-minus-one`
* :ref:`integer-as-property`
* :ref:`no-get\_class()-with-null`
* :ref:`php-7.2-new-class`
* :ref:`list-with-reference`
* :ref:`php-7.3-last-empty-argument`
* :ref:`flexible-heredoc`
* :ref:`const-visibility-usage`
* :ref:`hash-algorithms-incompatible-with-php-7.1-`
* :ref:`php-7.0-scalar-typehints`
* :ref:`php-7.1-scalar-typehints`
* :ref:`php-7.2-scalar-typehints`
* :ref:`continue-is-for-loop`
* :ref:`trailing-comma-in-calls`
* :ref:`direct-call-to-\_\_clone()`
* :ref:`no-return-for-generator`
* :ref:`no-reference-for-static-property`
* :ref:`typed-property-usage`
* :ref:`concat-and-addition`
* :ref:`unpacking-inside-arrays`
* :ref:`generator-cannot-return`
* :ref:`coalesce-equal`
* :ref:`enum-usage`
* :ref:`$files-full\_path`
* :ref:`never-typehint-usage`
* :ref:`php-8.1-typehints`
* :ref:`php-8.0-typehints`
* :ref:`named-parameter-usage`
* :ref:`cant-overload-constants`



Specs
^^^^^
+--------------+------------------------------------------------------------------------------------------------------------------------+
| Short name   | CompatibilityPHP55                                                                                                     |
+--------------+------------------------------------------------------------------------------------------------------------------------+
| Available in | `Entreprise Edition <https://www.exakat.io/entreprise-edition>`_, `Exakat Cloud <https://www.exakat.io/exakat-cloud>`_ |
+--------------+------------------------------------------------------------------------------------------------------------------------+
| Reports      | :ref:`report-ambassador`                                                                                               |
+--------------+------------------------------------------------------------------------------------------------------------------------+


.. _ruleset-compatibilityphp56:

CompatibilityPHP56
++++++++++++++++++

This ruleset centralizes all analysis for the migration from PHP 5.5 to 5.6.

Total : 64 analysis

* :ref:`non-static-methods-called-in-a-static`
* :ref:`malformed-octal`
* :ref:`$http\_raw\_post\_data-usage`
* :ref:`multiple-definition-of-the-same-argument`
* :ref:`switch-with-too-many-default`
* :ref:`hash-algorithms-incompatible-with-php-5.3`
* :ref:`hash-algorithms-incompatible-with-php-5.4-5.5`
* :ref:`variable-global`
* :ref:`null-on-new`
* :ref:`isset()-with-constant`
* :ref:`anonymous-classes`
* :ref:`unicode-escape-syntax`
* :ref:`new-functions-in-php-7.0`
* :ref:`php-7.0-new-classes`
* :ref:`php-7.0-new-interfaces`
* :ref:`parenthesis-as-parameter`
* :ref:`php5-indirect-variable-expression`
* :ref:`php-7-indirect-expression`
* :ref:`unicode-escape-partial`
* :ref:`define-with-array`
* :ref:`no-list-with-string`
* :ref:`php7-dirname`
* :ref:`php7-relaxed-keyword`
* :ref:`php-7.1-new-class`
* :ref:`list-with-keys`
* :ref:`list-short-syntax`
* :ref:`use-nullable-type`
* :ref:`multiple-exceptions-catch()`
* :ref:`no-string-with-append`
* :ref:`group-use-declaration`
* :ref:`new-functions-in-php-7.3`
* :ref:`cant-inherit-abstract-method`
* :ref:`group-use-trailing-comma`
* :ref:`child-class-removes-typehint`
* :ref:`no-substr-minus-one`
* :ref:`integer-as-property`
* :ref:`no-get\_class()-with-null`
* :ref:`php-7.2-new-class`
* :ref:`list-with-reference`
* :ref:`php-7.3-last-empty-argument`
* :ref:`flexible-heredoc`
* :ref:`const-visibility-usage`
* :ref:`hash-algorithms-incompatible-with-php-7.1-`
* :ref:`php-7.0-scalar-typehints`
* :ref:`php-7.1-scalar-typehints`
* :ref:`php-7.2-scalar-typehints`
* :ref:`continue-is-for-loop`
* :ref:`trailing-comma-in-calls`
* :ref:`direct-call-to-\_\_clone()`
* :ref:`no-return-for-generator`
* :ref:`no-reference-for-static-property`
* :ref:`typed-property-usage`
* :ref:`concat-and-addition`
* :ref:`unpacking-inside-arrays`
* :ref:`generator-cannot-return`
* :ref:`coalesce-equal`
* :ref:`php-8.0-only-typehints`
* :ref:`enum-usage`
* :ref:`$files-full\_path`
* :ref:`never-typehint-usage`
* :ref:`php-8.1-typehints`
* :ref:`php-8.0-typehints`
* :ref:`named-parameter-usage`
* :ref:`cant-overload-constants`



Specs
^^^^^
+--------------+------------------------------------------------------------------------------------------------------------------------+
| Short name   | CompatibilityPHP56                                                                                                     |
+--------------+------------------------------------------------------------------------------------------------------------------------+
| Available in | `Entreprise Edition <https://www.exakat.io/entreprise-edition>`_, `Exakat Cloud <https://www.exakat.io/exakat-cloud>`_ |
+--------------+------------------------------------------------------------------------------------------------------------------------+
| Reports      | :ref:`report-ambassador`                                                                                               |
+--------------+------------------------------------------------------------------------------------------------------------------------+


.. _ruleset-compatibilityphp70:

CompatibilityPHP70
++++++++++++++++++

This ruleset centralizes all analysis for the migration from PHP 5.6 to 7.0.

Total : 57 analysis

* :ref:`ext-ereg`
* :ref:`mcrypt\_create\_iv()-with-default-values`
* :ref:`magic-visibility`
* :ref:`hash-algorithms-incompatible-with-php-5.3`
* :ref:`hash-algorithms-incompatible-with-php-5.4-5.5`
* :ref:`reserved-keywords-in-php-7`
* :ref:`break-outside-loop`
* :ref:`php-7.0-removed-functions`
* :ref:`empty-list`
* :ref:`list-with-appends`
* :ref:`simple-global-variable`
* :ref:`foreach-don't-change-pointer`
* :ref:`php-7-indirect-expression`
* :ref:`php-7.0-removed-directives`
* :ref:`preg\_replace-with-option-e`
* :ref:`setlocale()-uses-constants`
* :ref:`usort-sorting-in-php-7.0`
* :ref:`hexadecimal-in-string`
* :ref:`func\_get\_arg()-modified`
* :ref:`set\_exception\_handler()-warning`
* :ref:`php-7.1-new-class`
* :ref:`list-with-keys`
* :ref:`list-short-syntax`
* :ref:`use-nullable-type`
* :ref:`multiple-exceptions-catch()`
* :ref:`new-functions-in-php-7.3`
* :ref:`cant-inherit-abstract-method`
* :ref:`group-use-trailing-comma`
* :ref:`child-class-removes-typehint`
* :ref:`no-substr-minus-one`
* :ref:`integer-as-property`
* :ref:`no-get\_class()-with-null`
* :ref:`php-7.2-new-class`
* :ref:`list-with-reference`
* :ref:`php-7.3-last-empty-argument`
* :ref:`flexible-heredoc`
* :ref:`const-visibility-usage`
* :ref:`hash-algorithms-incompatible-with-php-7.1-`
* :ref:`php-7.1-scalar-typehints`
* :ref:`php-7.2-scalar-typehints`
* :ref:`continue-is-for-loop`
* :ref:`trailing-comma-in-calls`
* :ref:`no-reference-for-static-property`
* :ref:`typed-property-usage`
* :ref:`concat-and-addition`
* :ref:`unpacking-inside-arrays`
* :ref:`coalesce-equal`
* :ref:`php-8.0-only-typehints`
* :ref:`union-typehint`
* :ref:`enum-usage`
* :ref:`$files-full\_path`
* :ref:`final-constant`
* :ref:`never-typehint-usage`
* :ref:`php-8.1-typehints`
* :ref:`php-8.0-typehints`
* :ref:`named-parameter-usage`
* :ref:`cant-overload-constants`



Specs
^^^^^
+--------------+------------------------------------------------------------------------------------------------------------------------+
| Short name   | CompatibilityPHP70                                                                                                     |
+--------------+------------------------------------------------------------------------------------------------------------------------+
| Available in | `Entreprise Edition <https://www.exakat.io/entreprise-edition>`_, `Exakat Cloud <https://www.exakat.io/exakat-cloud>`_ |
+--------------+------------------------------------------------------------------------------------------------------------------------+
| Reports      | :ref:`report-ambassador`                                                                                               |
+--------------+------------------------------------------------------------------------------------------------------------------------+


.. _ruleset-compatibilityphp71:

CompatibilityPHP71
++++++++++++++++++

This ruleset centralizes all analysis for the migration from PHP 7.0 to 7.1.

Total : 44 analysis

* :ref:`ext-mcrypt`
* :ref:`hash-algorithms-incompatible-with-php-5.3`
* :ref:`hash-algorithms-incompatible-with-php-5.4-5.5`
* :ref:`avoid-substr()-one`
* :ref:`php-7.0-removed-functions`
* :ref:`php-7.0-removed-directives`
* :ref:`preg\_replace-with-option-e`
* :ref:`hexadecimal-in-string`
* :ref:`use-random\_int()`
* :ref:`using-$this-outside-a-class`
* :ref:`php-7.1-removed-directives`
* :ref:`new-functions-in-php-7.1`
* :ref:`php-7.1-microseconds`
* :ref:`invalid-octal-in-string`
* :ref:`new-functions-in-php-7.3`
* :ref:`cant-inherit-abstract-method`
* :ref:`group-use-trailing-comma`
* :ref:`child-class-removes-typehint`
* :ref:`integer-as-property`
* :ref:`no-get\_class()-with-null`
* :ref:`php-7.2-new-class`
* :ref:`list-with-reference`
* :ref:`php-7.3-last-empty-argument`
* :ref:`flexible-heredoc`
* :ref:`php-7.2-scalar-typehints`
* :ref:`continue-is-for-loop`
* :ref:`trailing-comma-in-calls`
* :ref:`no-reference-for-static-property`
* :ref:`typed-property-usage`
* :ref:`string-initialization`
* :ref:`concat-and-addition`
* :ref:`unpacking-inside-arrays`
* :ref:`coalesce-equal`
* :ref:`php-8.0-only-typehints`
* :ref:`union-typehint`
* :ref:`signature-trailing-comma`
* :ref:`enum-usage`
* :ref:`$files-full\_path`
* :ref:`final-constant`
* :ref:`never-typehint-usage`
* :ref:`php-8.1-typehints`
* :ref:`php-8.0-typehints`
* :ref:`named-parameter-usage`
* :ref:`cant-overload-constants`



Specs
^^^^^
+--------------+------------------------------------------------------------------------------------------------------------------------+
| Short name   | CompatibilityPHP71                                                                                                     |
+--------------+------------------------------------------------------------------------------------------------------------------------+
| Available in | `Entreprise Edition <https://www.exakat.io/entreprise-edition>`_, `Exakat Cloud <https://www.exakat.io/exakat-cloud>`_ |
+--------------+------------------------------------------------------------------------------------------------------------------------+
| Reports      | :ref:`report-ambassador`                                                                                               |
+--------------+------------------------------------------------------------------------------------------------------------------------+


.. _ruleset-compatibilityphp72:

CompatibilityPHP72
++++++++++++++++++

This ruleset centralizes all analysis for the migration from PHP 7.1 to 7.2.

Total : 37 analysis

* :ref:`undefined-constants`
* :ref:`hash-algorithms-incompatible-with-php-5.3`
* :ref:`hash-algorithms-incompatible-with-php-5.4-5.5`
* :ref:`preg\_replace-with-option-e`
* :ref:`php-7.2-deprecations`
* :ref:`php-7.2-removed-functions`
* :ref:`new-functions-in-php-7.2`
* :ref:`new-constants-in-php-7.2`
* :ref:`new-functions-in-php-7.3`
* :ref:`php-7.2-object-keyword`
* :ref:`no-get\_class()-with-null`
* :ref:`php-7.2-new-class`
* :ref:`avoid-set\_error\_handler-$context-argument`
* :ref:`hash-will-use-objects`
* :ref:`can't-count-non-countable`
* :ref:`list-with-reference`
* :ref:`php-7.3-last-empty-argument`
* :ref:`flexible-heredoc`
* :ref:`continue-is-for-loop`
* :ref:`trailing-comma-in-calls`
* :ref:`no-reference-for-static-property`
* :ref:`typed-property-usage`
* :ref:`concat-and-addition`
* :ref:`unpacking-inside-arrays`
* :ref:`coalesce-equal`
* :ref:`php-8.0-only-typehints`
* :ref:`union-typehint`
* :ref:`signature-trailing-comma`
* :ref:`throw-was-an-expression`
* :ref:`enum-usage`
* :ref:`$files-full\_path`
* :ref:`final-constant`
* :ref:`never-typehint-usage`
* :ref:`php-8.1-typehints`
* :ref:`php-8.0-typehints`
* :ref:`named-parameter-usage`
* :ref:`cant-overload-constants`



Specs
^^^^^
+--------------+------------------------------------------------------------------------------------------------------------------------+
| Short name   | CompatibilityPHP72                                                                                                     |
+--------------+------------------------------------------------------------------------------------------------------------------------+
| Available in | `Entreprise Edition <https://www.exakat.io/entreprise-edition>`_, `Exakat Cloud <https://www.exakat.io/exakat-cloud>`_ |
+--------------+------------------------------------------------------------------------------------------------------------------------+
| Reports      | :ref:`report-ambassador`                                                                                               |
+--------------+------------------------------------------------------------------------------------------------------------------------+


.. _ruleset-compatibilityphp73:

CompatibilityPHP73
++++++++++++++++++

This ruleset centralizes all analysis for the migration from PHP 7.2 to 7.3.

Total : 28 analysis

* :ref:`new-functions-in-php-7.3`
* :ref:`unknown-pcre2-option`
* :ref:`compact-inexistant-variable`
* :ref:`case-insensitive-constants`
* :ref:`assert-function-is-reserved`
* :ref:`continue-is-for-loop`
* :ref:`php-7.3-removed-functions`
* :ref:`don't-read-and-write-in-one-expression`
* :ref:`typed-property-usage`
* :ref:`concat-and-addition`
* :ref:`unpacking-inside-arrays`
* :ref:`numeric-literal-separator`
* :ref:`php-74-new-directives`
* :ref:`coalesce-equal`
* :ref:`php-8.0-only-typehints`
* :ref:`union-typehint`
* :ref:`signature-trailing-comma`
* :ref:`throw-was-an-expression`
* :ref:`enum-usage`
* :ref:`$files-full\_path`
* :ref:`final-constant`
* :ref:`never-typehint-usage`
* :ref:`php-8.1-typehints`
* :ref:`php-8.0-typehints`
* :ref:`named-parameter-usage`
* :ref:`nested-attributes`
* :ref:`new-initializers`
* :ref:`cant-overload-constants`



Specs
^^^^^
+--------------+------------------------------------------------------------------------------------------------------------------------+
| Short name   | CompatibilityPHP73                                                                                                     |
+--------------+------------------------------------------------------------------------------------------------------------------------+
| Available in | `Entreprise Edition <https://www.exakat.io/entreprise-edition>`_, `Exakat Cloud <https://www.exakat.io/exakat-cloud>`_ |
+--------------+------------------------------------------------------------------------------------------------------------------------+
| Reports      | :ref:`report-ambassador`                                                                                               |
+--------------+------------------------------------------------------------------------------------------------------------------------+


.. _ruleset-compatibilityphp74:

CompatibilityPHP74
++++++++++++++++++

This ruleset centralizes all analysis for the migration from PHP 7.3 to 7.4.

Total : 40 analysis

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
* :ref:`avoid-get\_object\_vars()`
* :ref:`enum-usage`
* :ref:`$files-full\_path`
* :ref:`final-constant`
* :ref:`never-typehint-usage`
* :ref:`php-8.1-typehints`
* :ref:`php-8.0-typehints`
* :ref:`named-parameter-usage`
* :ref:`nested-attributes`
* :ref:`new-initializers`
* :ref:`cant-overload-constants`



Specs
^^^^^
+--------------+----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| Short name   | CompatibilityPHP74                                                                                                                                                                     |
+--------------+----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| Available in | `Entreprise Edition <https://www.exakat.io/entreprise-edition>`_, `Community Edition <https://www.exakat.io/community-edition>`_, `Exakat Cloud <https://www.exakat.io/exakat-cloud>`_ |
+--------------+----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| Reports      | :ref:`report-diplomat`, :ref:`report-ambassador`                                                                                                                                       |
+--------------+----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+


.. _ruleset-compatibilityphp80:

CompatibilityPHP80
++++++++++++++++++

This ruleset centralizes all analysis for the migration from PHP 7.4 to 8.0.

Total : 26 analysis

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
* :ref:`php-8.0-resources-turned-into-objects`
* :ref:`php-80-named-parameter-variadic`
* :ref:`final-private-methods`
* :ref:`array\_map()-passes-by-value`
* :ref:`reserved-match-keyword`
* :ref:`avoid-get\_object\_vars()`
* :ref:`enum-usage`
* :ref:`final-constant`
* :ref:`never-typehint-usage`
* :ref:`php-8.1-typehints`
* :ref:`nested-attributes`
* :ref:`new-initializers`
* :ref:`cant-overload-constants`



Specs
^^^^^
+--------------+----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| Short name   | CompatibilityPHP80                                                                                                                                                                     |
+--------------+----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| Available in | `Entreprise Edition <https://www.exakat.io/entreprise-edition>`_, `Community Edition <https://www.exakat.io/community-edition>`_, `Exakat Cloud <https://www.exakat.io/exakat-cloud>`_ |
+--------------+----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| Reports      | :ref:`report-diplomat`, :ref:`report-ambassador`                                                                                                                                       |
+--------------+----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+


.. _ruleset-compatibilityphp81:

CompatibilityPHP81
++++++++++++++++++

This ruleset centralizes all analysis for the migration from PHP 8.0 to 8.1.

Total : 18 analysis

* :ref:`php-7.4-removed-directives`
* :ref:`php-8.0-removed-directives`
* :ref:`restrict-global-usage`
* :ref:`inherited-static-variable`
* :ref:`php-8.1-removed-directives`
* :ref:`openssl-encrypt-default-algorithm-change`
* :ref:`php-8.1-removed-constants`
* :ref:`php-native-class-type-compatibility`
* :ref:`no-null-for-native-php-functions`
* :ref:`calling-static-trait-method`
* :ref:`no-referenced-void`
* :ref:`jsonserialize()-mixed-return-type`
* :ref:`new-functions-in-php-8.1`
* :ref:`php-8.1-removed-functions`
* :ref:`never-keyword`
* :ref:`false-to-array-conversion`
* :ref:`float-conversion-as-index`
* :ref:`cannot-call-trait-method-directly`



Specs
^^^^^
+--------------+----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| Short name   | CompatibilityPHP81                                                                                                                                                                     |
+--------------+----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| Available in | `Entreprise Edition <https://www.exakat.io/entreprise-edition>`_, `Community Edition <https://www.exakat.io/community-edition>`_, `Exakat Cloud <https://www.exakat.io/exakat-cloud>`_ |
+--------------+----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| Reports      | :ref:`report-diplomat`, :ref:`report-ambassador`                                                                                                                                       |
+--------------+----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+


.. _ruleset-dead-code:

Dead code
+++++++++

This ruleset focuses on dead code : expressions or even structures that are written, valid but never used.

Total : 26 analysis

* :ref:`unused-use`
* :ref:`unused-private-properties`
* :ref:`unused-private-methods`
* :ref:`unused-functions`
* :ref:`unused-constants`
* :ref:`unreachable-code`
* :ref:`empty-instructions`
* :ref:`unused-methods`
* :ref:`unused-classes`
* :ref:`locally-unused-property`
* :ref:`unresolved-instanceof`
* :ref:`unthrown-exception`
* :ref:`unused-label`
* :ref:`unused-interfaces`
* :ref:`unresolved-catch`
* :ref:`unset-in-foreach`
* :ref:`empty-namespace`
* :ref:`can't-extend-final`
* :ref:`exception-order`
* :ref:`undefined-caught-exceptions`
* :ref:`unused-protected-methods`
* :ref:`unused-returned-value`
* :ref:`rethrown-exceptions`
* :ref:`unused-inherited-variable-in-closure`
* :ref:`self-using-trait`
* :ref:`useless-type-check`



Specs
^^^^^
+--------------+------------------------------------------------------------------------------------------------------------------------+
| Short name   | Dead code                                                                                                              |
+--------------+------------------------------------------------------------------------------------------------------------------------+
| Available in | `Entreprise Edition <https://www.exakat.io/entreprise-edition>`_, `Exakat Cloud <https://www.exakat.io/exakat-cloud>`_ |
+--------------+------------------------------------------------------------------------------------------------------------------------+
| Reports      | :ref:`report-ambassador`, :ref:`report-rector`                                                                         |
+--------------+------------------------------------------------------------------------------------------------------------------------+


.. _ruleset-deprecated:

Deprecated
++++++++++

This ruleset centralizes all analysis that are marked as 'deprecated feature' for some versions.

For example : 

+ Php/NestedTernaryWithoutParenthesis : deprecated PHP 7.4, removed PHP 8.0
+ Php/NoMoreCurlyArrays : deprecated PHP 7.4, removed PHP 8.0
+ Classes/NoParent : deprecated PHP 7.4, removed PHP 8.0
+ Php/Php74RemovedDirective : deprecated PHP 7.4, removed PHP 8.0
+ Php/ArrayKeyExistsWithObjects : deprecated PHP 7.4, removed PHP 8.0



Total : 7 analysis

* :ref:`case-insensitive-constants`
* :ref:`assert-function-is-reserved`
* :ref:`nested-ternary-without-parenthesis`
* :ref:`no-null-for-native-php-functions`
* :ref:`calling-static-trait-method`
* :ref:`no-referenced-void`
* :ref:`jsonserialize()-mixed-return-type`



Specs
^^^^^
+--------------+------------------------------------------------------------------------------------------------------------------------+
| Short name   | Deprecated                                                                                                             |
+--------------+------------------------------------------------------------------------------------------------------------------------+
| Available in | `Entreprise Edition <https://www.exakat.io/entreprise-edition>`_, `Exakat Cloud <https://www.exakat.io/exakat-cloud>`_ |
+--------------+------------------------------------------------------------------------------------------------------------------------+


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
* :ref:`dereferencing-levels`
* :ref:`foreach()-favorite`
* :ref:`collect-mbstring-encodings`
* :ref:`typehinting-stats`
* :ref:`inclusions`
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
* :ref:`collect-static-class-changes`
* :ref:`collect-variables`
* :ref:`collect-global-variables`
* :ref:`collect-readability`
* :ref:`collect-definitions-statistics`
* :ref:`collect-class-traits-counts`
* :ref:`collect-native-calls-per-expressions`
* :ref:`collect-files-dependencies`
* :ref:`collect-atom-counts`
* :ref:`collect-classes-dependencies`
* :ref:`collect-php-structures`
* :ref:`collect-use-counts`
* :ref:`collect-block-size`



Specs
^^^^^
+--------------+----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| Short name   | Dump                                                                                                                                                                                   |
+--------------+----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| Available in | `Entreprise Edition <https://www.exakat.io/entreprise-edition>`_, `Community Edition <https://www.exakat.io/community-edition>`_, `Exakat Cloud <https://www.exakat.io/exakat-cloud>`_ |
+--------------+----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| Reports      |                                                                                                                                                                                        |
+--------------+----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+


.. _ruleset-first:

First
+++++

A set of rules that are always run at the beginning of a project, because they are frenquently used. It is mostly used internally.

Total : 7 analysis

* :ref:`is-an-extension-function`
* :ref:`is-an-extension-interface`
* :ref:`is-an-extension-constant`
* :ref:`is-extension-trait`
* :ref:`mark-callable`
* :ref:`variable-typehint`
* :ref:`variable-is-a-local-constant`



Specs
^^^^^
+--------------+----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| Short name   | First                                                                                                                                                                                  |
+--------------+----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| Available in | `Entreprise Edition <https://www.exakat.io/entreprise-edition>`_, `Community Edition <https://www.exakat.io/community-edition>`_, `Exakat Cloud <https://www.exakat.io/exakat-cloud>`_ |
+--------------+----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+


.. _ruleset-inventory:

Inventory
+++++++++

This ruleset collect all free-text names used in the code : variables, global, arguments, etc...

For example : 

+ Classes/MagicProperties
+ Constants/Constantnames : names of global Constants
+ Php/CookieVariables : names of cookies
+ Php/DateFormats : date formats
+ Php/IncomingVariables : names of the GET/POST arguments
+ Php/SessionVariables : names of the session variables
+ Type/ArrayIndex : indices used in arrays
+ Type/Binary : binary values
+ Type/CharString : string values
+ Type/Email : hardcoded emails
+ Type/GPCIndex : GET, POST and COOKIE names
+ Type/Hexadecimal : hexadecimal values
+ Type/HexadecimalString : hexadecimal values
+ Type/HttpHeader : HTTP headers
+ Type/HttpStatus : HTTP status
+ Type/Md5String : MD5 string
+ Type/MimeType : Mime types
+ Type/OctalInString : octal values
+ Type/OpensslCipher : names of OpenSSL cipher 
+ Type/Pack : pack() formats
+ Type/Pcre : regex strings
+ Type/Ports : server ports mentioned
+ Type/Printf : printf() and co formatting strings
+ Type/Regex : regex strings
+ Type/SpecialIntegers : integer, with special values
+ Type/Sql : SQL strings
+ Type/UdpDomains : UDP domains
+ Type/UnicodeBlock : Unicode blocks
+ Type/Url : URL




Total : 33 analysis

* :ref:`constants-names`
* :ref:`binary-glossary`
* :ref:`email-addresses`
* :ref:`hexadecimal-glossary`
* :ref:`http-headers`
* :ref:`http-status-code`
* :ref:`md5-strings`
* :ref:`mime-types`
* :ref:`perl-regex`
* :ref:`internet-ports`
* :ref:`special-integers`
* :ref:`all-strings`
* :ref:`unicode-blocks`
* :ref:`url-list`
* :ref:`hexadecimal-in-string`
* :ref:`invalid-octal-in-string`
* :ref:`sql-queries`
* :ref:`regex-inventory`
* :ref:`switch-fallthrough`
* :ref:`session-variables`
* :ref:`incoming-variables`
* :ref:`cookies-variables`
* :ref:`date-formats`
* :ref:`type-array-index`
* :ref:`incoming-variable-index-inventory`
* :ref:`pack-format-inventory`
* :ref:`printf-format-inventory`
* :ref:`magic-properties`
* :ref:`internet-domains`
* :ref:`openssl-ciphers-used`
* :ref:`promoted-properties`
* :ref:`extends-stdclass`
* :ref:`intersection-typehint`



Specs
^^^^^
+--------------+------------------------------------------------------------------------------------------------------------------------+
| Short name   | Inventory                                                                                                              |
+--------------+------------------------------------------------------------------------------------------------------------------------+
| Available in | `Entreprise Edition <https://www.exakat.io/entreprise-edition>`_, `Exakat Cloud <https://www.exakat.io/exakat-cloud>`_ |
+--------------+------------------------------------------------------------------------------------------------------------------------+


.. _ruleset-lintbutwontexec:

LintButWontExec
+++++++++++++++

This ruleset focuses on PHP code that lint (php -l), but that will not run. As such, this ruleset tries to go further than PHP, by connecting files, just like during execution.

Total : 38 analysis

* :ref:`final-class-usage`
* :ref:`final-methods-usage`
* :ref:`$this-belongs-to-classes-or-traits`
* :ref:`classes-mutually-extending-each-other`
* :ref:`must-return-methods`
* :ref:`undefined-interfaces`
* :ref:`concrete-visibility`
* :ref:`no-self-referencing-constant`
* :ref:`using-$this-outside-a-class`
* :ref:`undefined-trait`
* :ref:`raised-access-level`
* :ref:`self,-parent,-static-outside-class`
* :ref:`no-magic-method-with-array`
* :ref:`method-signature-must-be-compatible`
* :ref:`mismatch-type-and-default`
* :ref:`can't-throw-throwable`
* :ref:`abstract-or-implements`
* :ref:`incompatible-signature-methods`
* :ref:`undefined-insteadof`
* :ref:`method-collision-traits`
* :ref:`only-variable-for-reference`
* :ref:`repeated-interface`
* :ref:`useless-alias`
* :ref:`typehint-must-be-returned`
* :ref:`clone-with-non-object`
* :ref:`trait-not-found`
* :ref:`interfaces-is-not-implemented`
* :ref:`cant-implement-traversable`
* :ref:`wrong-typed-property-default`
* :ref:`mismatch-properties-typehints`
* :ref:`could-be-stringable`
* :ref:`only-container-for-reference`
* :ref:`inherited-property-type-must-match`
* :ref:`duplicate-named-parameter`
* :ref:`jsonserialize()-mixed-return-type`
* :ref:`false-to-array-conversion`
* :ref:`deprecated-callable`
* :ref:`cant-overload-constants`



Specs
^^^^^
+--------------+------------------------------------------------------------------------------------------------------------------------+
| Short name   | LintButWontExec                                                                                                        |
+--------------+------------------------------------------------------------------------------------------------------------------------+
| Available in | `Entreprise Edition <https://www.exakat.io/entreprise-edition>`_, `Exakat Cloud <https://www.exakat.io/exakat-cloud>`_ |
+--------------+------------------------------------------------------------------------------------------------------------------------+


.. _ruleset-performances:

Performances
++++++++++++

This ruleset focuses on performances issues : anything that slows the code's execution.

Total : 47 analysis

* :ref:`eval()-usage`
* :ref:`for-using-functioncall`
* :ref:`@-operator`
* :ref:`while(list()-=-each())`
* :ref:`avoid-array\_unique()`
* :ref:`echo-with-concat`
* :ref:`slow-functions`
* :ref:`no-array\_merge()-in-loops`
* :ref:`could-use-short-assignation`
* :ref:`pre-increment`
* :ref:`avoid-substr()-one`
* :ref:`global-inside-loop`
* :ref:`joining-file()`
* :ref:`simplify-regex`
* :ref:`make-one-call-with-array`
* :ref:`no-count-with-0`
* :ref:`use-class-operator`
* :ref:`time()-vs-strtotime()`
* :ref:`getting-last-element`
* :ref:`avoid-array\_push()`
* :ref:`should-use-function`
* :ref:`fetch-one-row-format`
* :ref:`avoid-glob()-usage`
* :ref:`avoid-large-array-assignation`
* :ref:`should-use-array\_column()`
* :ref:`avoid-concat-in-loop`
* :ref:`use-pathinfo()-arguments`
* :ref:`simple-switch`
* :ref:`substring-first`
* :ref:`use-php7-encapsed-strings`
* :ref:`slice-arrays-first`
* :ref:`double-array\_flip()`
* :ref:`processing-collector`
* :ref:`do-in-base`
* :ref:`cache-variable-outside-loop`
* :ref:`use-the-blind-var`
* :ref:`closure-could-be-a-callback`
* :ref:`fputcsv()-in-loops`
* :ref:`isset()-on-the-whole-array`
* :ref:`array\_key\_exists()-speedup`
* :ref:`autoappend`
* :ref:`make-magic-concrete`
* :ref:`regex-on-arrays`
* :ref:`always-use-function-with-array\_key\_exists()`
* :ref:`no-mb\_substr-in-loop`
* :ref:`optimize-explode()`
* :ref:`scope-resolution-operator`



Specs
^^^^^
+--------------+------------------------------------------------------------------------------------------------------------------------+
| Short name   | Performances                                                                                                           |
+--------------+------------------------------------------------------------------------------------------------------------------------+
| Available in | `Entreprise Edition <https://www.exakat.io/entreprise-edition>`_, `Exakat Cloud <https://www.exakat.io/exakat-cloud>`_ |
+--------------+------------------------------------------------------------------------------------------------------------------------+
| Reports      |                                                                                                                        |
+--------------+------------------------------------------------------------------------------------------------------------------------+


.. _ruleset-php-cs-fixable:

php-cs-fixable
++++++++++++++

[PHP-CS-FIXER](https://github.com/FriendsOfPHP/PHP-CS-Fixer) is a tool to automatically fix PHP Coding Standards issues. It applies modifications in the PHP code automatically. Exakat finds results which may be automatically updated with PHP-CS-FIXER. 

Total : 11 analysis

* :ref:`use-===-null`
* :ref:`**-for-exponent`
* :ref:`logical-should-use-symbolic-operators`
* :ref:`use-constant`
* :ref:`else-if-versus-elseif`
* :ref:`php7-dirname`
* :ref:`could-use-\_\_dir\_\_`
* :ref:`isset-multiple-arguments`
* :ref:`don't-unset-properties`
* :ref:`multiple-unset()`
* :ref:`implode-one-arg`



Specs
^^^^^
+--------------+------------------------------------------------------------------------------------------------------------------------+
| Short name   | php-cs-fixable                                                                                                         |
+--------------+------------------------------------------------------------------------------------------------------------------------+
| Available in | `Entreprise Edition <https://www.exakat.io/entreprise-edition>`_, `Exakat Cloud <https://www.exakat.io/exakat-cloud>`_ |
+--------------+------------------------------------------------------------------------------------------------------------------------+
| Reports      | :ref:`report-phpcsfixer`                                                                                               |
+--------------+------------------------------------------------------------------------------------------------------------------------+


.. _ruleset-preferences:

Preferences
+++++++++++

This ruleset identify code with multiple forms, and report when one is more frequent than the others. Echo vs print, shell_exec() vs ``, etc.

Total : 29 analysis

* :ref:`true-false-inconsistant-case`
* :ref:`echo-or-print`
* :ref:`constant-comparison`
* :ref:`die-exit-consistence`
* :ref:`array()---[--]-consistence`
* :ref:`$globals-or-global`
* :ref:`unset()-or-(unset)`
* :ref:`close-tags-consistency`
* :ref:`one-expression-brackets-consistency`
* :ref:`new-on-functioncall-or-identifier`
* :ref:`new-line-style`
* :ref:`regex-delimiter`
* :ref:`empty-final-element`
* :ref:`difference-consistence`
* :ref:`concatenation-interpolation-consistence`
* :ref:`strict\_types-preference`
* :ref:`declare-strict\_types-usage`
* :ref:`encoding-usage`
* :ref:`ticks-usage`
* :ref:`logical-operators-favorite`
* :ref:`shell-favorite`
* :ref:`properties-declaration-consistence`
* :ref:`strict-or-relaxed-comparison`
* :ref:`comparisons-orientation`
* :ref:`const-or-define-preference`
* :ref:`constant-case-preference`
* :ref:`caught-variable`
* :ref:`not-or-tilde`
* :ref:`null-type-favorite`



Specs
^^^^^
+--------------+------------------------------------------------------------------------------------------------------------------------+
| Short name   | Preferences                                                                                                            |
+--------------+------------------------------------------------------------------------------------------------------------------------+
| Available in | `Entreprise Edition <https://www.exakat.io/entreprise-edition>`_, `Exakat Cloud <https://www.exakat.io/exakat-cloud>`_ |
+--------------+------------------------------------------------------------------------------------------------------------------------+
| Reports      | :ref:`report-ambassador`, :ref:`report-diplomat`                                                                       |
+--------------+------------------------------------------------------------------------------------------------------------------------+


.. _ruleset-rector:

Rector
++++++

`RectorPHP <https://getrector.org/>`_ is a reconstructor tool. It applies modifications in the PHP code automatically. Exakat finds results which may be automatically updated with rector. 

Total : 14 analysis

* :ref:`adding-zero`
* :ref:`multiple-index-definition`
* :ref:`for-using-functioncall`
* :ref:`multiply-by-one`
* :ref:`multiples-identical-case`
* :ref:`preprocessable`
* :ref:`implied-if`
* :ref:`else-if-versus-elseif`
* :ref:`could-use-short-assignation`
* :ref:`should-typecast`
* :ref:`no-choice`
* :ref:`never-called-parameter`
* :ref:`closure-could-be-a-callback`
* :ref:`is\_a()-with-string`



Specs
^^^^^
+--------------+------------------------------------------------------------------------------------------------------------------------+
| Short name   | Rector                                                                                                                 |
+--------------+------------------------------------------------------------------------------------------------------------------------+
| Available in | `Entreprise Edition <https://www.exakat.io/entreprise-edition>`_, `Exakat Cloud <https://www.exakat.io/exakat-cloud>`_ |
+--------------+------------------------------------------------------------------------------------------------------------------------+
| Reports      | :ref:`report-ambassador`, :ref:`report-rector`                                                                         |
+--------------+------------------------------------------------------------------------------------------------------------------------+


.. _ruleset-security:

Security
++++++++

This ruleset focuses on code security. 

Total : 44 analysis

* :ref:`eval()-usage`
* :ref:`phpinfo`
* :ref:`var\_dump()...-usage`
* :ref:`hardcoded-passwords`
* :ref:`direct-injection`
* :ref:`avoid-sleep()-usleep()`
* :ref:`parse\_str()-warning`
* :ref:`avoid-those-hash-functions`
* :ref:`no-hardcoded-port`
* :ref:`should-use-prepared-statement`
* :ref:`no-hardcoded-ip`
* :ref:`compare-hash`
* :ref:`preg\_replace-with-option-e`
* :ref:`eval()-without-try`
* :ref:`register-globals`
* :ref:`safe-curl-options`
* :ref:`use-random\_int()`
* :ref:`no-hardcoded-hash`
* :ref:`random-without-try`
* :ref:`indirect-injection`
* :ref:`unserialize-second-arg`
* :ref:`don't-echo-error`
* :ref:`should-use-session\_regenerateid()`
* :ref:`encoded-simple-letters`
* :ref:`set-cookie-safe-arguments`
* :ref:`no-return-or-throw-in-finally`
* :ref:`mkdir-default`
* :ref:`switch-fallthrough`
* :ref:`upload-filename-injection`
* :ref:`always-anchor-regex`
* :ref:`session-lazy-write`
* :ref:`sqlite3-requires-single-quotes`
* :ref:`no-net-for-xml-load`
* :ref:`dynamic-library-loading`
* :ref:`configure-extract`
* :ref:`move\_uploaded\_file-instead-of-copy`
* :ref:`filter\_input()-as-a-source`
* :ref:`safe-http-headers`
* :ref:`insecure-integer-validation`
* :ref:`minus-one-on-error`
* :ref:`no-ent\_ignore`
* :ref:`no-weak-ssl-crypto`
* :ref:`keep-files-access-restricted`
* :ref:`check-crypto-key-length`



Specs
^^^^^
+--------------+------------------------------------------------------------------------------------------------------------------------+
| Short name   | Security                                                                                                               |
+--------------+------------------------------------------------------------------------------------------------------------------------+
| Available in | `Entreprise Edition <https://www.exakat.io/entreprise-edition>`_, `Exakat Cloud <https://www.exakat.io/exakat-cloud>`_ |
+--------------+------------------------------------------------------------------------------------------------------------------------+
| Reports      | :ref:`report-ambassador`, :ref:`report-owasp`                                                                          |
+--------------+------------------------------------------------------------------------------------------------------------------------+


.. _ruleset-semantics:

Semantics
+++++++++

This ruleset focuses on human interpretation of the code. It reviews special values of literals, and named structures.

Total : 16 analysis

* :ref:`constants-with-strange-names`
* :ref:`variables-with-one-letter-names`
* :ref:`one-letter-functions`
* :ref:`property-variable-confusion`
* :ref:`php-keywords-as-names`
* :ref:`class-function-confusion`
* :ref:`strange-name-for-variables`
* :ref:`similar-integers`
* :ref:`duplicate-literal`
* :ref:`parameter-hiding`
* :ref:`weird-array-index`
* :ref:`wrong-typehinted-name`
* :ref:`semantic-typing`
* :ref:`fn-argument-variable-confusion`
* :ref:`prefix-and-suffixes-with-typehint`
* :ref:`mismatch-parameter-and-type`



Specs
^^^^^
+--------------+------------------------------------------------------------------------------------------------------------------------+
| Short name   | Semantics                                                                                                              |
+--------------+------------------------------------------------------------------------------------------------------------------------+
| Available in | `Entreprise Edition <https://www.exakat.io/entreprise-edition>`_, `Exakat Cloud <https://www.exakat.io/exakat-cloud>`_ |
+--------------+------------------------------------------------------------------------------------------------------------------------+


.. _ruleset-suggestions:

Suggestions
+++++++++++

This ruleset focuses on possibly better syntax than the one currently used. Those may be code modernization, alternatives, more efficient solutions, or simply left over from older versions. 

Total : 102 analysis

* :ref:`while(list()-=-each())`
* :ref:`function-subscripting,-old-style`
* :ref:`**-for-exponent`
* :ref:`too-many-children`
* :ref:`empty-with-expression`
* :ref:`list()-may-omit-variables`
* :ref:`unreachable-code`
* :ref:`overwritten-exceptions`
* :ref:`return-with-parenthesis`
* :ref:`strict-comparison-with-booleans`
* :ref:`logical-should-use-symbolic-operators`
* :ref:`could-use-self`
* :ref:`preprocess-arrays`
* :ref:`repeated-print()`
* :ref:`echo-with-concat`
* :ref:`no-parenthesis-for-language-construct`
* :ref:`unused-interfaces`
* :ref:`avoid-substr()-one`
* :ref:`php7-dirname`
* :ref:`preg\_match\_all()-flag`
* :ref:`already-parents-interface`
* :ref:`could-use-\_\_dir\_\_`
* :ref:`should-use-coalesce`
* :ref:`could-use-alias`
* :ref:`drop-else-after-return`
* :ref:`unitialized-properties`
* :ref:`should-use-array\_column()`
* :ref:`randomly-sorted-arrays`
* :ref:`no-return-used`
* :ref:`could-make-a-function`
* :ref:`use-session\_start()-options`
* :ref:`mismatched-ternary-alternatives`
* :ref:`isset-multiple-arguments`
* :ref:`should-use-foreach`
* :ref:`substring-first`
* :ref:`use-list-with-foreach`
* :ref:`slice-arrays-first`
* :ref:`parent-first`
* :ref:`never-called-parameter`
* :ref:`should-use-array\_filter()`
* :ref:`reuse-variable`
* :ref:`should-use-math`
* :ref:`could-use-compact`
* :ref:`could-use-array\_fill\_keys`
* :ref:`use-count-recursive`
* :ref:`too-many-parameters`
* :ref:`should-preprocess-chr()`
* :ref:`possible-increment`
* :ref:`drop-substr-last-arg`
* :ref:`one-if-is-sufficient`
* :ref:`could-use-array\_unique`
* :ref:`compact-inexistant-variable`
* :ref:`should-use-operator`
* :ref:`could-be-static-closure`
* :ref:`use-is\_countable`
* :ref:`detect-current-class`
* :ref:`avoid-real`
* :ref:`use-json\_decode()-options`
* :ref:`closure-could-be-a-callback`
* :ref:`add-default-value`
* :ref:`named-regex`
* :ref:`could-use-try`
* :ref:`use-basename-suffix`
* :ref:`don't-loop-on-yield`
* :ref:`should-have-destructor`
* :ref:`directly-use-file`
* :ref:`isset()-on-the-whole-array`
* :ref:`multiple-usage-of-same-trait`
* :ref:`array\_key\_exists()-speedup`
* :ref:`should-deep-clone`
* :ref:`multiple-unset()`
* :ref:`implode-one-arg`
* :ref:`useless-default-argument`
* :ref:`no-need-for-get\_class()`
* :ref:`substr-to-trim`
* :ref:`complex-dynamic-names`
* :ref:`could-be-constant`
* :ref:`use-datetimeimmutable-class`
* :ref:`set-aside-code`
* :ref:`use-array-functions`
* :ref:`use-case-value`
* :ref:`use-url-query-functions`
* :ref:`too-long-a-block`
* :ref:`static-global-variables-confusion`
* :ref:`possible-alias-confusion`
* :ref:`too-much-indented`
* :ref:`dont-compare-typed-boolean`
* :ref:`abstract-away`
* :ref:`large-try-block`
* :ref:`cancel-common-method`
* :ref:`useless-typehint`
* :ref:`could-use-promoted-properties`
* :ref:`use-get\_debug\_type()`
* :ref:`use-str\_contains()`
* :ref:`unused-exception-variable`
* :ref:`searching-for-multiple-keys`
* :ref:`long-preparation-for-throw`
* :ref:`no-static-variable-in-a-method`
* :ref:`declare-static-once`
* :ref:`could-use-match`
* :ref:`could-use-nullable-object-operator`
* :ref:`classes-couldbeiterable`



Specs
^^^^^
+--------------+------------------------------------------------------------------------------------------------------------------------+
| Short name   | Suggestions                                                                                                            |
+--------------+------------------------------------------------------------------------------------------------------------------------+
| Available in | `Entreprise Edition <https://www.exakat.io/entreprise-edition>`_, `Exakat Cloud <https://www.exakat.io/exakat-cloud>`_ |
+--------------+------------------------------------------------------------------------------------------------------------------------+
| Reports      | :ref:`report-diplomat`, :ref:`report-ambassador`                                                                       |
+--------------+------------------------------------------------------------------------------------------------------------------------+


.. _ruleset-top10:

Top10
+++++

This ruleset is a selection of analysis, with the top 10 most common. Actually, it is a little larger than that. 

Total : 28 analysis

* :ref:`for-using-functioncall`
* :ref:`strpos()-like-comparison`
* :ref:`used-once-variables`
* :ref:`dangling-array-references`
* :ref:`queries-in-loops`
* :ref:`use-const`
* :ref:`logical-should-use-symbolic-operators`
* :ref:`repeated-print()`
* :ref:`objects-don't-need-references`
* :ref:`no-real-comparison`
* :ref:`no-array\_merge()-in-loops`
* :ref:`unresolved-instanceof`
* :ref:`avoid-substr()-one`
* :ref:`no-choice`
* :ref:`failed-substr-comparison`
* :ref:`unitialized-properties`
* :ref:`could-use-str\_repeat()`
* :ref:`logical-operators-favorite`
* :ref:`avoid-concat-in-loop`
* :ref:`next-month-trap`
* :ref:`substring-first`
* :ref:`use-list-with-foreach`
* :ref:`don't-unset-properties`
* :ref:`avoid-real`
* :ref:`should-yield-with-key`
* :ref:`fputcsv()-in-loops`
* :ref:`possible-missing-subpattern`
* :ref:`concat-and-addition`



Specs
^^^^^
+--------------+------------------------------------------------------------------------------------------------------------------------+
| Short name   | Top10                                                                                                                  |
+--------------+------------------------------------------------------------------------------------------------------------------------+
| Available in | `Entreprise Edition <https://www.exakat.io/entreprise-edition>`_, `Exakat Cloud <https://www.exakat.io/exakat-cloud>`_ |
+--------------+------------------------------------------------------------------------------------------------------------------------+


.. _ruleset-typechecks:

Typechecks
++++++++++

This ruleset focuses on typehinting. Missing typehint, or inconsistent typehint, are reported. 

Total : 25 analysis

* :ref:`argument-should-be-typehinted`
* :ref:`useless-interfaces`
* :ref:`no-class-as-typehint`
* :ref:`mismatched-default-arguments`
* :ref:`mismatched-typehint`
* :ref:`child-class-removes-typehint`
* :ref:`not-a-scalar-type`
* :ref:`mismatch-type-and-default`
* :ref:`insufficient-typehint`
* :ref:`bad-typehint-relay`
* :ref:`wrong-type-with-call`
* :ref:`missing-typehint`
* :ref:`fossilized-method`
* :ref:`could-be-string`
* :ref:`could-be-void`
* :ref:`could-be-callable`
* :ref:`wrong-argument-type`
* :ref:`could-be-integer`
* :ref:`could-be-null`
* :ref:`could-be-iterable`
* :ref:`could-be-float`
* :ref:`could-be-self`
* :ref:`could-be-parent`
* :ref:`could-be-generator`
* :ref:`classes-couldbeiterable`



Specs
^^^^^
+--------------+------------------------------------------------------------------------------------------------------------------------+
| Short name   | Typechecks                                                                                                             |
+--------------+------------------------------------------------------------------------------------------------------------------------+
| Available in | `Entreprise Edition <https://www.exakat.io/entreprise-edition>`_, `Exakat Cloud <https://www.exakat.io/exakat-cloud>`_ |
+--------------+------------------------------------------------------------------------------------------------------------------------+


.. _ruleset-all:

All
+++

All is a dummy ruleset, which includes all the rules. It is mostly used internally.

Total : 1420 analysis

* :ref:`adding-zero`
* :ref:`ambiguous-array-index`
* :ref:`array-index`
* :ref:`multidimensional-arrays`
* :ref:`multiple-index-definition`
* :ref:`php-arrays-index`
* :ref:`class-usage`
* :ref:`classes-names`
* :ref:`constant-definition`
* :ref:`empty-classes`
* :ref:`magic-methods`
* :ref:`forgotten-visibility`
* :ref:`non-static-methods-called-in-a-static`
* :ref:`old-style-constructor`
* :ref:`property-names`
* :ref:`static-methods`
* :ref:`static-methods-called-from-object`
* :ref:`static-properties`
* :ref:`constants-with-strange-names`
* :ref:`constants-usage`
* :ref:`constants-names`
* :ref:`true-false-inconsistant-case`
* :ref:`magic-constant-usage`
* :ref:`php-constant-usage`
* :ref:`caught-exceptions`
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
* :ref:`empty-function`
* :ref:`function-called-with-other-case-than-defined`
* :ref:`functions-glossary`
* :ref:`recursive-functions`
* :ref:`redeclared-php-functions`
* :ref:`typehints`
* :ref:`unset-arguments`
* :ref:`methods-without-return`
* :ref:`empty-interfaces`
* :ref:`interfaces-usage`
* :ref:`interfaces-glossary`
* :ref:`php-interfaces`
* :ref:`aliases`
* :ref:`namespaces-glossary`
* :ref:`autoloading`
* :ref:`use-lower-case-for-parent,-static-and-self`
* :ref:`goto-names`
* :ref:`\_\_halt\_compiler`
* :ref:`incompilable-files`
* :ref:`labels`
* :ref:`functions-removed-in-php-5.4`
* :ref:`functions-removed-in-php-5.5`
* :ref:`throw`
* :ref:`trigger-errors`
* :ref:`caught-expressions`
* :ref:`break-with-0`
* :ref:`break-with-non-integer`
* :ref:`calltime-pass-by-reference`
* :ref:`error\_reporting()-with-integers`
* :ref:`eval()-usage`
* :ref:`exit()-usage`
* :ref:`for-using-functioncall`
* :ref:`forgotten-whitespace`
* :ref:`iffectations`
* :ref:`multiply-by-one`
* :ref:`@-operator`
* :ref:`not-not`
* :ref:`include\_once()-usage`
* :ref:`phpinfo`
* :ref:`no-plus-one`
* :ref:`using-short-tags`
* :ref:`strpos()-like-comparison`
* :ref:`throws-an-assignement`
* :ref:`var\_dump()...-usage`
* :ref:`\_\_tostring()-throws-exception`
* :ref:`binary-glossary`
* :ref:`continents`
* :ref:`email-addresses`
* :ref:`heredoc-delimiter-glossary`
* :ref:`hexadecimal-glossary`
* :ref:`http-headers`
* :ref:`http-status-code`
* :ref:`malformed-octal`
* :ref:`md5-strings`
* :ref:`mime-types`
* :ref:`nowdoc-delimiter-glossary`
* :ref:`octal-glossary`
* :ref:`perl-regex`
* :ref:`internet-ports`
* :ref:`special-integers`
* :ref:`all-strings`
* :ref:`unicode-blocks`
* :ref:`url-list`
* :ref:`blind-variables`
* :ref:`interface-arguments`
* :ref:`variable-references`
* :ref:`static-variables`
* :ref:`variables-with-long-names`
* :ref:`non-ascii-variables`
* :ref:`variables-with-one-letter-names`
* :ref:`php-variables`
* :ref:`all-uppercase-variables`
* :ref:`used-once-variables`
* :ref:`variable-variables`
* :ref:`abstract-class-usage`
* :ref:`abstract-methods-usage`
* :ref:`clone-usage`
* :ref:`final-class-usage`
* :ref:`final-methods-usage`
* :ref:`bad-constants-names`
* :ref:`variable-constants`
* :ref:`empty-traits`
* :ref:`redefined-php-traits`
* :ref:`traits-usage`
* :ref:`trait-names`
* :ref:`php-alternative-syntax`
* :ref:`short-syntax-for-arrays`
* :ref:`inclusions`
* :ref:`ext-file`
* :ref:`unused-use`
* :ref:`use-with-fully-qualified-name`
* :ref:`used-use`
* :ref:`ext-array`
* :ref:`ext-ffmpeg`
* :ref:`ext-info`
* :ref:`ext-math`
* :ref:`$http\_raw\_post\_data-usage`
* :ref:`non-lowercase-keywords`
* :ref:`ext-yis`
* :ref:`new-functions-in-php-5.4`
* :ref:`new-functions-in-php-5.5`
* :ref:`useless-instructions`
* :ref:`abstract-static-methods`
* :ref:`interface-methods`
* :ref:`new-functions-in-php-5.6`
* :ref:`trait-methods`
* :ref:`invalid-constant-name`
* :ref:`multiple-constant-definition`
* :ref:`wrong-optional-parameter`
* :ref:`multiple-definition-of-the-same-argument`
* :ref:`echo-or-print`
* :ref:`use-===-null`
* :ref:`constant-comparison`
* :ref:`fopen-binary-mode`
* :ref:`assertions`
* :ref:`$this-is-not-an-array`
* :ref:`one-variable-string`
* :ref:`cast-usage`
* :ref:`function-subscripting`
* :ref:`nested-loops`
* :ref:`close-tags`
* :ref:`I?=-usage`
* :ref:`static-methods-can't-contain-$this`
* :ref:`closure-may-use-$this`
* :ref:`while(list()-=-each())`
* :ref:`several-instructions-on-the-same-line`
* :ref:`one-letter-functions`
* :ref:`multiples-identical-case`
* :ref:`switch-without-default`
* :ref:`function-subscripting,-old-style`
* :ref:`internally-used-properties`
* :ref:`$this-belongs-to-classes-or-traits`
* :ref:`nested-ternary`
* :ref:`switch-with-too-many-default`
* :ref:`non-constant-index-in-array`
* :ref:`undefined-constants`
* :ref:`custom-constant-usage`
* :ref:`instantiating-abstract-class`
* :ref:`classes-mutually-extending-each-other`
* :ref:`class,-interface-or-trait-with-identical-names`
* :ref:`empty-try-catch`
* :ref:`ext-pcntl`
* :ref:`undefined-classes`
* :ref:`is-an-extension-class`
* :ref:`wrong-class-name-case`
* :ref:`ext-ming`
* :ref:`ext-redis`
* :ref:`is-an-extension-function`
* :ref:`is-an-extension-interface`
* :ref:`is-an-extension-constant`
* :ref:`htmlentities-calls`
* :ref:`bracketless-blocks`
* :ref:`defined-class-constants`
* :ref:`undefined-class-constants`
* :ref:`unused-private-properties`
* :ref:`used-static-properties`
* :ref:`used-private-methods`
* :ref:`unused-private-methods`
* :ref:`unused-functions`
* :ref:`used-functions`
* :ref:`used-once-variables-(in-scope)`
* :ref:`undefined-functions`
* :ref:`deprecated-php-functions`
* :ref:`crypt()-without-salt`
* :ref:`mcrypt\_create\_iv()-with-default-values`
* :ref:`dangling-array-references`
* :ref:`ext-cyrus`
* :ref:`ext-sqlsrv`
* :ref:`queries-in-loops`
* :ref:`var-keyword`
* :ref:`aliases-usage`
* :ref:`uses-default-values`
* :ref:`wrong-number-of-arguments`
* :ref:`hardcoded-passwords`
* :ref:`functions-in-loop-calls`
* :ref:`unresolved-classes`
* :ref:`ellipsis-usage`
* :ref:`exponent-usage`
* :ref:`**-for-exponent`
* :ref:`constructors`
* :ref:`useless-constructor`
* :ref:`too-many-children`
* :ref:`implement-is-for-interface`
* :ref:`use-const`
* :ref:`unresolved-use`
* :ref:`conditional-structures`
* :ref:`unused-constants`
* :ref:`undefined-parent`
* :ref:`defined-static-or-self`
* :ref:`undefined-static-or-self`
* :ref:`accessing-private`
* :ref:`access-protected-structures`
* :ref:`parent,-static-or-self-outside-class`
* :ref:`ext-0mq`
* :ref:`ext-memcache`
* :ref:`ext-memcached`
* :ref:`is-extension-trait`
* :ref:`dynamic-function-call`
* :ref:`has-variable-arguments`
* :ref:`multiple-catch`
* :ref:`dynamically-called-classes`
* :ref:`conditioned-function`
* :ref:`conditioned-constants`
* :ref:`is-generator`
* :ref:`try-with-finally`
* :ref:`use-password\_hash()`
* :ref:`dereferencing-string-and-arrays`
* :ref:`class`
* :ref:`foreach-with-list()`
* :ref:`empty-with-expression`
* :ref:`list()-may-omit-variables`
* :ref:`or-die`
* :ref:`constant-conditions`
* :ref:`use-const-and-functions`
* :ref:`constant-scalar-expressions`
* :ref:`unusual-case-for-php-functions`
* :ref:`multiple-returns`
* :ref:`unreachable-code`
* :ref:`exit-like-methods`
* :ref:`written-only-variables`
* :ref:`must-return-methods`
* :ref:`\_\_debuginfo()-usage`
* :ref:`empty-instructions`
* :ref:`interpolation`
* :ref:`mixed-keys-arrays`
* :ref:`empty-slots-in-arrays`
* :ref:`wrong-number-of-arguments-in-methods`
* :ref:`class-has-fluent-interface`
* :ref:`method-has-fluent-interface`
* :ref:`method-has-no-fluent-interface`
* :ref:`php-handlers-usage`
* :ref:`ext-imagick`
* :ref:`unused-methods`
* :ref:`property-variable-confusion`
* :ref:`ext-oci8`
* :ref:`used-methods`
* :ref:`overwritten-exceptions`
* :ref:`foreach-needs-reference-array`
* :ref:`foreach-reference-is-not-modified`
* :ref:`ext-imap`
* :ref:`overwritten-class-const`
* :ref:`direct-injection`
* :ref:`dynamic-class-constant`
* :ref:`dynamic-methodcall`
* :ref:`dynamic-new`
* :ref:`dynamic-property`
* :ref:`don't-change-incomings`
* :ref:`super-globals-contagion`
* :ref:`dynamic-classes`
* :ref:`return-void-`
* :ref:`compared-comparison`
* :ref:`useless-return`
* :ref:`multiple-classes-in-one-file`
* :ref:`file-uploads`
* :ref:`return-with-parenthesis`
* :ref:`unused-classes`
* :ref:`used-classes`
* :ref:`ext-intl`
* :ref:`ext-cairo`
* :ref:`dynamic-code`
* :ref:`unpreprocessed-values`
* :ref:`ext-pspell`
* :ref:`no-direct-access`
* :ref:`ext-opcache`
* :ref:`is-php-constant`
* :ref:`sensitive-argument`
* :ref:`functioncall-is-global`
* :ref:`ext-expect`
* :ref:`defined-properties`
* :ref:`undefined-properties`
* :ref:`has-magic-method`
* :ref:`ext-recode`
* :ref:`ext-parsekit`
* :ref:`ext-runkit`
* :ref:`ext-gettext`
* :ref:`short-open-tags`
* :ref:`strict-comparison-with-booleans`
* :ref:`lone-blocks`
* :ref:`$this-is-not-for-static-methods`
* :ref:`avoid-sleep()-usleep()`
* :ref:`argument-should-be-typehinted`
* :ref:`should-be-single-quote`
* :ref:`super-global-usage`
* :ref:`global-usage`
* :ref:`php-keywords-as-names`
* :ref:`logical-should-use-symbolic-operators`
* :ref:`could-use-self`
* :ref:`implicit-global`
* :ref:`const-with-array`
* :ref:`catch-overwrite-variable`
* :ref:`namespaces`
* :ref:`avoid-array\_unique()`
* :ref:`definitions-only`
* :ref:`deep-definitions`
* :ref:`constant-class`
* :ref:`not-definitions-only`
* :ref:`global-code-only`
* :ref:`preprocess-arrays`
* :ref:`repeated-print()`
* :ref:`avoid-parenthesis`
* :ref:`objects-don't-need-references`
* :ref:`redefined-property`
* :ref:`locally-unused-property`
* :ref:`locally-used-property`
* :ref:`lost-references`
* :ref:`constants-created-outside-its-namespace`
* :ref:`fully-qualified-constants`
* :ref:`never-used-properties`
* :ref:`yoda-comparison`
* :ref:`no-real-comparison`
* :ref:`sequences-in-for`
* :ref:`should-use-local-class`
* :ref:`use-this`
* :ref:`usage-of-class\_alias()`
* :ref:`custom-class-usage`
* :ref:`ext-apache`
* :ref:`ext-eaccelerator`
* :ref:`ext-fpm`
* :ref:`ext-iis`
* :ref:`ext-xcache`
* :ref:`ext-wincache`
* :ref:`parse\_str()-warning`
* :ref:`no-direct-call-to-magic-method`
* :ref:`string-may-hold-a-variable`
* :ref:`echo-with-concat`
* :ref:`unused-global`
* :ref:`useless-global`
* :ref:`preprocessable`
* :ref:`slow-functions`
* :ref:`useless-final`
* :ref:`use-constant`
* :ref:`resources-usage`
* :ref:`useless-unset`
* :ref:`buried-assignation`
* :ref:`duplicate-calls`
* :ref:`no-array\_merge()-in-loops`
* :ref:`useless-parenthesis`
* :ref:`shell-usage`
* :ref:`file-usage`
* :ref:`mail-usage`
* :ref:`dynamic-calls`
* :ref:`unresolved-instanceof`
* :ref:`use-php-object-api`
* :ref:`unthrown-exception`
* :ref:`old-style-\_\_autoload()`
* :ref:`altering-foreach-without-reference`
* :ref:`test-class`
* :ref:`mark-callable`
* :ref:`magic-visibility`
* :ref:`use-pathinfo`
* :ref:`should-use-constants`
* :ref:`hash-algorithms`
* :ref:`avoid-those-hash-functions`
* :ref:`ext-dio`
* :ref:`no-parenthesis-for-language-construct`
* :ref:`unused-label`
* :ref:`no-hardcoded-path`
* :ref:`methodcall-on-new`
* :ref:`no-hardcoded-port`
* :ref:`ext-phalcon`
* :ref:`use-constant-as-arguments`
* :ref:`implied-if`
* :ref:`overwritten-literals`
* :ref:`assign-default-to-properties`
* :ref:`no-public-access`
* :ref:`composer-usage`
* :ref:`composer's-autoload`
* :ref:`composer-namespace`
* :ref:`should-chain-exception`
* :ref:`used-interfaces`
* :ref:`unused-interfaces`
* :ref:`useless-interfaces`
* :ref:`undefined-interfaces`
* :ref:`concrete-visibility`
* :ref:`ext-apcu`
* :ref:`double-instructions`
* :ref:`should-use-prepared-statement`
* :ref:`is-interface-method`
* :ref:`hash-algorithms-incompatible-with-php-5.3`
* :ref:`hash-algorithms-incompatible-with-php-5.4-5.5`
* :ref:`print-and-die`
* :ref:`unchecked-resources`
* :ref:`class-const-with-array`
* :ref:`ext-trader`
* :ref:`ext-mailparse`
* :ref:`ext-mail`
* :ref:`unresolved-catch`
* :ref:`no-hardcoded-ip`
* :ref:`variable-global`
* :ref:`else-if-versus-elseif`
* :ref:`reserved-keywords-in-php-7`
* :ref:`unset-in-foreach`
* :ref:`could-be-class-constant`
* :ref:`could-be-static`
* :ref:`multiple-class-declarations`
* :ref:`compare-hash`
* :ref:`empty-namespace`
* :ref:`could-use-short-assignation`
* :ref:`useless-abstract-class`
* :ref:`only-static-methods`
* :ref:`null-on-new`
* :ref:`scalar-typehint-usage`
* :ref:`return-typehint-usage`
* :ref:`ext-ob`
* :ref:`global-import`
* :ref:`static-loop`
* :ref:`pre-increment`
* :ref:`only-variable-returned-by-reference`
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
* :ref:`indices-are-int-or-string`
* :ref:`should-typecast`
* :ref:`no-self-referencing-constant`
* :ref:`no-direct-usage`
* :ref:`break-outside-loop`
* :ref:`inconsistent-concatenation`
* :ref:`else-usage`
* :ref:`one-object-operator-per-line`
* :ref:`isset()-with-constant`
* :ref:`is-composer-class`
* :ref:`is-composer-interface`
* :ref:`avoid-substr()-one`
* :ref:`global-inside-loop`
* :ref:`anonymous-classes`
* :ref:`is-global-constant`
* :ref:`coalesce`
* :ref:`double-assignation`
* :ref:`unicode-escape-syntax`
* :ref:`new-functions-in-php-7.0`
* :ref:`php-7.0-removed-functions`
* :ref:`php-7.0-new-classes`
* :ref:`php-7.0-new-interfaces`
* :ref:`empty-list`
* :ref:`list-with-appends`
* :ref:`simple-global-variable`
* :ref:`parenthesis-as-parameter`
* :ref:`foreach-don't-change-pointer`
* :ref:`php5-indirect-variable-expression`
* :ref:`php-7-indirect-expression`
* :ref:`unicode-escape-partial`
* :ref:`define-with-array`
* :ref:`php-7.0-removed-directives`
* :ref:`directives-usage`
* :ref:`useless-brackets`
* :ref:`preg\_replace-with-option-e`
* :ref:`eval()-without-try`
* :ref:`is-not-class-family`
* :ref:`no-list-with-string`
* :ref:`setlocale()-uses-constants`
* :ref:`global-in-global`
* :ref:`usort-sorting-in-php-7.0`
* :ref:`hexadecimal-in-string`
* :ref:`ext-fann`
* :ref:`relay-function`
* :ref:`func\_get\_arg()-modified`
* :ref:`use-web`
* :ref:`use-cli`
* :ref:`php-sapi`
* :ref:`register-globals`
* :ref:`external-config-files`
* :ref:`avoid-get\_class()`
* :ref:`silently-cast-integer`
* :ref:`used-trait`
* :ref:`unused-traits`
* :ref:`php7-dirname`
* :ref:`error-messages`
* :ref:`timestamp-difference`
* :ref:`php7-relaxed-keyword`
* :ref:`not-same-name-as-file`
* :ref:`ext-pecl\_http`
* :ref:`joining-file()`
* :ref:`real-variables`
* :ref:`real-functions`
* :ref:`normal-methods`
* :ref:`unused-arguments`
* :ref:`uses-environment`
* :ref:`switch-to-switch`
* :ref:`wrong-parameter-type`
* :ref:`property-could-be-private-property`
* :ref:`redefined-methods`
* :ref:`redefined-class-constants`
* :ref:`file-is-component`
* :ref:`redefined-default`
* :ref:`wrong-fopen()-mode`
* :ref:`unknown-directive-name`
* :ref:`confusing-names`
* :ref:`is-cli-script`
* :ref:`php-bugfixes`
* :ref:`preg\_match\_all()-flag`
* :ref:`safe-curl-options`
* :ref:`negative-power`
* :ref:`already-parents-interface`
* :ref:`use-random\_int()`
* :ref:`cant-use-return-value-in-write-context`
* :ref:`set\_exception\_handler()-warning`
* :ref:`can't-extend-final`
* :ref:`ternary-in-concat`
* :ref:`using-$this-outside-a-class`
* :ref:`simplify-regex`
* :ref:`ext-tokyotyrant`
* :ref:`ext-v8js`
* :ref:`yield-usage`
* :ref:`yield-from-usage`
* :ref:`pear-usage`
* :ref:`undefined-trait`
* :ref:`no-hardcoded-hash`
* :ref:`identical-conditions`
* :ref:`unkown-regex-options`
* :ref:`random-without-try`
* :ref:`no-choice`
* :ref:`common-alternatives`
* :ref:`logical-mistakes`
* :ref:`exception-order`
* :ref:`ext-lua`
* :ref:`uncaught-exceptions`
* :ref:`undefined-caught-exceptions`
* :ref:`same-conditions-in-condition`
* :ref:`php-7.1-new-class`
* :ref:`return-true-false`
* :ref:`gprc-aliases`
* :ref:`indirect-injection`
* :ref:`useless-switch`
* :ref:`overwriting-variable`
* :ref:`could-use-\_\_dir\_\_`
* :ref:`should-use-coalesce`
* :ref:`make-global-a-property`
* :ref:`list-with-keys`
* :ref:`if-with-same-conditions`
* :ref:`ext-suhosin`
* :ref:`unserialize-second-arg`
* :ref:`throw-functioncall`
* :ref:`can't-disable-function`
* :ref:`functions-using-reference`
* :ref:`use-instanceof`
* :ref:`make-one-call-with-array`
* :ref:`property-used-above`
* :ref:`property-used-below`
* :ref:`list-short-syntax`
* :ref:`results-may-be-missing`
* :ref:`use-nullable-type`
* :ref:`defined-parent-mp`
* :ref:`globals`
* :ref:`always-positive-comparison`
* :ref:`php-7.1-removed-directives`
* :ref:`new-functions-in-php-7.1`
* :ref:`multiple-exceptions-catch()`
* :ref:`is-upper-family`
* :ref:`empty-blocks`
* :ref:`throw-in-destruct`
* :ref:`used-protected-method`
* :ref:`unused-protected-methods`
* :ref:`use-system-tmp`
* :ref:`linux-only-files`
* :ref:`no-count-with-0`
* :ref:`dependant-trait`
* :ref:`hidden-use-expression`
* :ref:`could-use-alias`
* :ref:`should-make-alias`
* :ref:`multiple-identical-trait-or-interface`
* :ref:`multiple-alias-definitions`
* :ref:`nested-ifthen`
* :ref:`cast-to-boolean`
* :ref:`failed-substr-comparison`
* :ref:`should-make-ternary`
* :ref:`unused-returned-value`
* :ref:`modernize-empty-with-expression`
* :ref:`use-positive-condition`
* :ref:`drop-else-after-return`
* :ref:`use-class-operator`
* :ref:`ext-rar`
* :ref:`don't-echo-error`
* :ref:`useless-type-casting`
* :ref:`no-isset()-with-empty()`
* :ref:`time()-vs-strtotime()`
* :ref:`useless-check`
* :ref:`unitialized-properties`
* :ref:`more-than-one-level-of-indentation`
* :ref:`one-dot-or-object-operator-per-line`
* :ref:`bail-out-early`
* :ref:`die-exit-consistence`
* :ref:`array()---[--]-consistence`
* :ref:`php-7.1-microseconds`
* :ref:`dont-change-the-blind-var`
* :ref:`getting-last-element`
* :ref:`rethrown-exceptions`
* :ref:`avoid-using-stdclass`
* :ref:`invalid-octal-in-string`
* :ref:`avoid-array\_push()`
* :ref:`ext-nsapi`
* :ref:`ext-newt`
* :ref:`ext-ncurses`
* :ref:`use-composer-lock`
* :ref:`too-many-local-variables`
* :ref:`$globals-or-global`
* :ref:`illegal-name-for-method`
* :ref:`unset()-or-(unset)`
* :ref:`close-tags-consistency`
* :ref:`string`
* :ref:`class-should-be-final-by-ocramius`
* :ref:`ext-mhash`
* :ref:`ext-zbarcode`
* :ref:`ext-mongodb`
* :ref:`should-use-function`
* :ref:`one-expression-brackets-consistency`
* :ref:`fetch-one-row-format`
* :ref:`no-string-with-append`
* :ref:`avoid-glob()-usage`
* :ref:`avoid-large-array-assignation`
* :ref:`could-be-protected-property`
* :ref:`long-arguments`
* :ref:`new-on-functioncall-or-identifier`
* :ref:`assigned-twice`
* :ref:`new-line-style`
* :ref:`php-7.2-deprecations`
* :ref:`php-7.2-removed-functions`
* :ref:`error\_log()-usage`
* :ref:`raised-access-level`
* :ref:`no-boolean-as-default`
* :ref:`sql-queries`
* :ref:`strange-names-for-methods`
* :ref:`ext-libsodium`
* :ref:`class-function-confusion`
* :ref:`forgotten-thrown`
* :ref:`should-use-array\_column()`
* :ref:`multiple-alias-definitions-per-file`
* :ref:`\_\_dir\_\_-then-slash`
* :ref:`self,-parent,-static-outside-class`
* :ref:`used-once-property`
* :ref:`property-used-in-one-method-only`
* :ref:`ext-ds`
* :ref:`no-need-for-else`
* :ref:`should-use-session\_regenerateid()`
* :ref:`strange-name-for-variables`
* :ref:`strange-name-for-constants`
* :ref:`regex-delimiter`
* :ref:`could-be-typehinted-callable`
* :ref:`encoded-simple-letters`
* :ref:`too-many-finds`
* :ref:`use-cookies`
* :ref:`should-use-setcookie()`
* :ref:`set-cookie-safe-arguments`
* :ref:`check-all-types`
* :ref:`missing-cases-in-switch`
* :ref:`new-functions-in-php-7.2`
* :ref:`new-constants-in-php-7.2`
* :ref:`group-use-declaration`
* :ref:`method-is-overwritten`
* :ref:`displays-text`
* :ref:`repeated-regex`
* :ref:`no-class-in-global`
* :ref:`crc32()-might-be-negative`
* :ref:`could-use-str\_repeat()`
* :ref:`suspicious-comparison`
* :ref:`empty-final-element`
* :ref:`strings-with-strange-space`
* :ref:`difference-consistence`
* :ref:`no-empty-regex`
* :ref:`alternative-syntax-consistence`
* :ref:`randomly-sorted-arrays`
* :ref:`ext-sphinx`
* :ref:`try-with-multiple-catch`
* :ref:`ext-grpc`
* :ref:`only-variable-passed-by-reference`
* :ref:`no-return-used`
* :ref:`use-browscap`
* :ref:`use-debug`
* :ref:`no-class-as-typehint`
* :ref:`no-reference-on-left-side`
* :ref:`implemented-methods-are-public`
* :ref:`could-typehint`
* :ref:`psr-16-usage`
* :ref:`psr-7-usage`
* :ref:`psr-6-usage`
* :ref:`psr-3-usage`
* :ref:`psr-11-usage`
* :ref:`psr-13-usage`
* :ref:`mixed-concat-and-interpolation`
* :ref:`ext-stats`
* :ref:`di-cyclic-dependencies`
* :ref:`concatenation-interpolation-consistence`
* :ref:`new-functions-in-php-7.3`
* :ref:`too-many-injections`
* :ref:`dependency-injection`
* :ref:`courier-anti-pattern`
* :ref:`ext-gender`
* :ref:`ext-judy`
* :ref:`could-make-a-function`
* :ref:`forgotten-interface`
* :ref:`order-of-declaration`
* :ref:`yii-usage`
* :ref:`codeigniter-usage`
* :ref:`laravel-usage`
* :ref:`symfony-usage`
* :ref:`wordpress-usage`
* :ref:`ez-cms-usage`
* :ref:`use-session\_start()-options`
* :ref:`cant-inherit-abstract-method`
* :ref:`joomla-usage`
* :ref:`non-breakable-space-in-names`
* :ref:`multiple-functions-declarations`
* :ref:`avoid-optional-properties`
* :ref:`heredoc-delimiter`
* :ref:`ext-swoole`
* :ref:`manipulates-nan`
* :ref:`manipulates-inf`
* :ref:`no-return-or-throw-in-finally`
* :ref:`const-or-define`
* :ref:`mkdir-default`
* :ref:`strict\_types-preference`
* :ref:`declare-strict\_types-usage`
* :ref:`encoding-usage`
* :ref:`ticks-usage`
* :ref:`mismatched-ternary-alternatives`
* :ref:`ext-lapack`
* :ref:`mismatched-default-arguments`
* :ref:`mismatched-typehint`
* :ref:`scalar-or-object-property`
* :ref:`group-use-trailing-comma`
* :ref:`assign-with-and-precedence`
* :ref:`logical-operators-favorite`
* :ref:`isset-multiple-arguments`
* :ref:`no-magic-method-with-array`
* :ref:`php-7.2-object-keyword`
* :ref:`child-class-removes-typehint`
* :ref:`ext-xattr`
* :ref:`avoid-concat-in-loop`
* :ref:`optional-parameter`
* :ref:`no-substr-minus-one`
* :ref:`logical-to-in\_array`
* :ref:`should-use-foreach`
* :ref:`ext-rdkafka`
* :ref:`ext-fam`
* :ref:`shell-favorite`
* :ref:`constant-used-below`
* :ref:`could-be-private-class-constant`
* :ref:`could-be-protected-class-constant`
* :ref:`method-used-below`
* :ref:`method-could-be-private-method`
* :ref:`could-be-protected-method`
* :ref:`pathinfo()-returns-may-vary`
* :ref:`use-pathinfo()-arguments`
* :ref:`ext-parle`
* :ref:`regex-inventory`
* :ref:`switch-fallthrough`
* :ref:`upload-filename-injection`
* :ref:`always-anchor-regex`
* :ref:`multiple-type-variable`
* :ref:`is-actually-zero`
* :ref:`unconditional-break-in-loop`
* :ref:`session-lazy-write`
* :ref:`session-variables`
* :ref:`incoming-variables`
* :ref:`cookies-variables`
* :ref:`too-complex-expression`
* :ref:`date-formats`
* :ref:`is-a-php-magic-property`
* :ref:`could-be-else`
* :ref:`simple-switch`
* :ref:`next-month-trap`
* :ref:`printf-number-of-arguments`
* :ref:`substring-first`
* :ref:`drupal-usage`
* :ref:`ambiguous-static`
* :ref:`phalcon-usage`
* :ref:`fuelphp-usage`
* :ref:`use-list-with-foreach`
* :ref:`don't-send-$this-in-constructor`
* :ref:`argon2-usage`
* :ref:`crypto-usage`
* :ref:`integer-as-property`
* :ref:`no-get\_class()-with-null`
* :ref:`php-7.2-new-class`
* :ref:`avoid-set\_error\_handler-$context-argument`
* :ref:`hash-will-use-objects`
* :ref:`can't-count-non-countable`
* :ref:`maybe-missing-new`
* :ref:`unknown-pcre2-option`
* :ref:`use-php7-encapsed-strings`
* :ref:`type-array-index`
* :ref:`incoming-variable-index-inventory`
* :ref:`slice-arrays-first`
* :ref:`ext-vips`
* :ref:`dl()-usage`
* :ref:`parent-first`
* :ref:`environment-variables`
* :ref:`invalid-regex`
* :ref:`assigned-in-one-branch`
* :ref:`use-named-boolean-in-argument-definition`
* :ref:`same-variable-foreach`
* :ref:`never-called-parameter`
* :ref:`ext-igbinary`
* :ref:`should-use-array\_filter()`
* :ref:`not-a-scalar-type`
* :ref:`mistaken-concatenation`
* :ref:`identical-on-both-sides`
* :ref:`identical-consecutive-expression`
* :ref:`no-reference-for-ternary`
* :ref:`sqlite3-requires-single-quotes`
* :ref:`no-net-for-xml-load`
* :ref:`unused-inherited-variable-in-closure`
* :ref:`inclusion-wrong-case`
* :ref:`missing-include`
* :ref:`local-globals`
* :ref:`useless-referenced-argument`
* :ref:`fallback-function`
* :ref:`reuse-variable`
* :ref:`double-array\_flip()`
* :ref:`useless-catch`
* :ref:`find-key-directly`
* :ref:`possible-infinite-loop`
* :ref:`should-use-math`
* :ref:`ext-hrtime`
* :ref:`list-with-reference`
* :ref:`test-then-cast`
* :ref:`could-use-compact`
* :ref:`foreach-on-object`
* :ref:`ext-xxtea`
* :ref:`ext-uopz`
* :ref:`ext-varnish`
* :ref:`ext-opencensus`
* :ref:`dynamic-library-loading`
* :ref:`php-7.3-last-empty-argument`
* :ref:`could-use-array\_fill\_keys`
* :ref:`ext-leveldb`
* :ref:`use-count-recursive`
* :ref:`property-could-be-local`
* :ref:`ext-db2`
* :ref:`mass-creation-of-arrays`
* :ref:`too-many-native-calls`
* :ref:`too-many-parameters`
* :ref:`should-preprocess-chr()`
* :ref:`properties-declaration-consistence`
* :ref:`possible-increment`
* :ref:`drop-substr-last-arg`
* :ref:`redefined-private-property`
* :ref:`don't-unset-properties`
* :ref:`strtr-arguments`
* :ref:`processing-collector`
* :ref:`missing-parenthesis`
* :ref:`one-if-is-sufficient`
* :ref:`could-use-array\_unique`
* :ref:`callback-function-needs-return`
* :ref:`wrong-range-check`
* :ref:`ext-zookeeper`
* :ref:`ext-cmark`
* :ref:`php-failinganalysis`
* :ref:`cant-instantiate-class`
* :ref:`strpos()-too-much`
* :ref:`typehinted-references`
* :ref:`do-in-base`
* :ref:`weak-typing`
* :ref:`cache-variable-outside-loop`
* :ref:`use-the-blind-var`
* :ref:`configure-extract`
* :ref:`compact-inexistant-variable`
* :ref:`method-signature-must-be-compatible`
* :ref:`mismatch-type-and-default`
* :ref:`flexible-heredoc`
* :ref:`check-json`
* :ref:`const-visibility-usage`
* :ref:`should-use-operator`
* :ref:`single-use-variables`
* :ref:`strict-or-relaxed-comparison`
* :ref:`comparisons-orientation`
* :ref:`compared-but-not-assigned-strings`
* :ref:`could-be-static-closure`
* :ref:`move\_uploaded\_file-instead-of-copy`
* :ref:`dont-mix-++`
* :ref:`can't-throw-throwable`
* :ref:`abstract-or-implements`
* :ref:`ext-eio`
* :ref:`incompatible-signature-methods`
* :ref:`ambiguous-visibilities`
* :ref:`ext-csprng`
* :ref:`hash-algorithms-incompatible-with-php-7.1-`
* :ref:`undefined-class`
* :ref:`php-7.0-scalar-typehints`
* :ref:`php-7.1-scalar-typehints`
* :ref:`php-7.2-scalar-typehints`
* :ref:`locally-used-property-in-trait`
* :ref:`ext-lzf`
* :ref:`ext-msgpack`
* :ref:`case-insensitive-constants`
* :ref:`handle-arrays-with-callback`
* :ref:`use-is\_countable`
* :ref:`detect-current-class`
* :ref:`avoid-real`
* :ref:`const-or-define-preference`
* :ref:`constant-case-preference`
* :ref:`assert-function-is-reserved`
* :ref:`could-be-abstract-class`
* :ref:`continue-is-for-loop`
* :ref:`php-7.3-removed-functions`
* :ref:`trailing-comma-in-calls`
* :ref:`must-call-parent-constructor`
* :ref:`undefined-variable`
* :ref:`undefined-insteadof`
* :ref:`method-collision-traits`
* :ref:`use-json\_decode()-options`
* :ref:`class-could-be-final`
* :ref:`closure-could-be-a-callback`
* :ref:`inconsistent-elseif`
* :ref:`can't-disable-class`
* :ref:`ext-seaslog`
* :ref:`add-default-value`
* :ref:`only-variable-for-reference`
* :ref:`direct-call-to-\_\_clone()`
* :ref:`filter\_input()-as-a-source`
* :ref:`wrong-access-style-to-property`
* :ref:`named-regex`
* :ref:`invalid-pack-format`
* :ref:`no-return-for-generator`
* :ref:`repeated-interface`
* :ref:`no-reference-for-static-property`
* :ref:`don't-read-and-write-in-one-expression`
* :ref:`pack-format-inventory`
* :ref:`printf-format-inventory`
* :ref:`idn\_to\_ascii()-new-default`
* :ref:`could-use-try`
* :ref:`use-basename-suffix`
* :ref:`php-exception`
* :ref:`ext-decimal`
* :ref:`ext-psr`
* :ref:`should-yield-with-key`
* :ref:`don't-loop-on-yield`
* :ref:`declare-global-early`
* :ref:`unreachable-class-constant`
* :ref:`avoid-self-in-interface`
* :ref:`should-have-destructor`
* :ref:`safe-http-headers`
* :ref:`fputcsv()-in-loops`
* :ref:`directly-use-file`
* :ref:`useless-alias`
* :ref:`ext-sdl`
* :ref:`isset()-on-the-whole-array`
* :ref:`ext-async`
* :ref:`ext-wasm`
* :ref:`self-using-trait`
* :ref:`multiple-usage-of-same-trait`
* :ref:`method-could-be-static`
* :ref:`multiple-identical-closure`
* :ref:`path-lists`
* :ref:`possible-missing-subpattern`
* :ref:`array\_key\_exists()-speedup`
* :ref:`assign-and-compare`
* :ref:`typed-property-usage`
* :ref:`don't-be-too-manual`
* :ref:`variable-is-not-a-condition`
* :ref:`string-initialization`
* :ref:`ext-weakref`
* :ref:`ext-pcov`
* :ref:`insufficient-typehint`
* :ref:`bad-typehint-relay`
* :ref:`constant-dynamic-creation`
* :ref:`php-8.0-removed-functions`
* :ref:`php-8.0-removed-constants`
* :ref:`law-of-demeter`
* :ref:`an-oop-factory`
* :ref:`typehint-must-be-returned`
* :ref:`inconsistent-variable-usage`
* :ref:`should-deep-clone`
* :ref:`clone-with-non-object`
* :ref:`self-transforming-variables`
* :ref:`check-on-\_\_call-usage`
* :ref:`php-overridden-function`
* :ref:`caught-variable`
* :ref:`multiple-unset()`
* :ref:`implode-one-arg`
* :ref:`insecure-integer-validation`
* :ref:`incoming-values`
* :ref:`ext-svm`
* :ref:`useless-default-argument`
* :ref:`avoid-option-arrays-in-constructors`
* :ref:`ext-ffi`
* :ref:`ext-password`
* :ref:`ext-zend\_monitor`
* :ref:`ext-uuid`
* :ref:`already-parents-trait`
* :ref:`trait-not-found`
* :ref:`casting-ternary`
* :ref:`concat-empty-string`
* :ref:`concat-and-addition`
* :ref:`useless-argument`
* :ref:`new-functions-in-php-7.4`
* :ref:`unpacking-inside-arrays`
* :ref:`minus-one-on-error`
* :ref:`no-need-for-get\_class()`
* :ref:`identical-methods`
* :ref:`no-append-on-source`
* :ref:`autoappend`
* :ref:`memoize-magiccall`
* :ref:`make-magic-concrete`
* :ref:`substr-to-trim`
* :ref:`regex-on-arrays`
* :ref:`always-use-function-with-array\_key\_exists()`
* :ref:`complex-dynamic-names`
* :ref:`curl\_version()-has-no-argument`
* :ref:`php-7.4-new-class`
* :ref:`new-constants-in-php-7.4`
* :ref:`unused-class-constant`
* :ref:`could-be-constant`
* :ref:`could-use-trait`
* :ref:`infinite-recursion`
* :ref:`null-or-boolean-arrays`
* :ref:`dependant-abstract-classes`
* :ref:`wrong-type-returned`
* :ref:`generator-cannot-return`
* :ref:`cant-use-function`
* :ref:`use-datetimeimmutable-class`
* :ref:`set-aside-code`
* :ref:`use-array-functions`
* :ref:`useless-type-check`
* :ref:`disconnected-classes`
* :ref:`not-or-tilde`
* :ref:`overwritten-source-and-value`
* :ref:`avoid-mb\_dectect\_encoding()`
* :ref:`php-7.4-removed-functions`
* :ref:`mb\_strrpos()-third-argument`
* :ref:`array\_key\_exists()-works-on-arrays`
* :ref:`reflection-export()-is-deprecated`
* :ref:`unbinding-closures`
* :ref:`numeric-literal-separator`
* :ref:`class-without-parent`
* :ref:`serialize-magic-method`
* :ref:`scalar-are-not-arrays`
* :ref:`similar-integers`
* :ref:`complete-phpnativereference`
* :ref:`create-compact-variables`
* :ref:`propagate-constants`
* :ref:`php-7.4-reserved-keyword`
* :ref:`no-ent\_ignore`
* :ref:`no-more-curly-arrays`
* :ref:`overwritten-properties`
* :ref:`overwritten-methods`
* :ref:`overwritten-constant`
* :ref:`set-clone-link`
* :ref:`create-magic-property`
* :ref:`set-parent-definition`
* :ref:`make-class-method-definition`
* :ref:`create-default-values`
* :ref:`array\_merge()-and-variadic`
* :ref:`set-class\_alias-definition`
* :ref:`make-class-constant-definition`
* :ref:`complete-setclassremotedefinitionwithinjection`
* :ref:`solve-trait-methods`
* :ref:`follow-closure-definition`
* :ref:`php-7.4-constant-deprecation`
* :ref:`implode()-arguments-order`
* :ref:`php-7.4-removed-directives`
* :ref:`hash-algorithms-incompatible-with-php-7.4-`
* :ref:`openssl\_random\_pseudo\_byte()-second-argument`
* :ref:`strip\_tags-skips-closed-tag`
* :ref:`no-spread-for-hash`
* :ref:`use-covariance`
* :ref:`use-contravariance`
* :ref:`set-class-remote-definition-with-return-typehint`
* :ref:`set-class-remote-definition-with-local-new`
* :ref:`complete-setclassremotedefinitionwithtypehint`
* :ref:`set-class-remote-definition-with-global`
* :ref:`complete-setclassremotedefinitionwithparenthesis`
* :ref:`set-class-property-definition-with-typehint`
* :ref:`seta-rray-class-definition`
* :ref:`set-string-method-definition`
* :ref:`set-class-method-remote-definition`
* :ref:`use-arrow-functions`
* :ref:`max-level-of-nesting`
* :ref:`environment-variable-usage`
* :ref:`indentation-levels`
* :ref:`spread-operator-for-array`
* :ref:`nested-ternary-without-parenthesis`
* :ref:`cyclomatic-complexity`
* :ref:`should-use-explode-args`
* :ref:`use-array\_slice()`
* :ref:`php-74-new-directives`
* :ref:`too-many-array-dimensions`
* :ref:`coalesce-and-concat`
* :ref:`comparison-is-always-true`
* :ref:`incompatible-signature-methods-with-covariance`
* :ref:`interfaces-is-not-implemented`
* :ref:`no-literal-for-reference`
* :ref:`magic-properties`
* :ref:`interfaces-don't-ensure-properties`
* :ref:`collect-literals`
* :ref:`duplicate-literal`
* :ref:`no-weak-ssl-crypto`
* :ref:`internet-domains`
* :ref:`no-mb\_substr-in-loop`
* :ref:`collect-parameter-counts`
* :ref:`collect-local-variable-counts`
* :ref:`non-nullable-getters`
* :ref:`use-case-value`
* :ref:`dereferencing-levels`
* :ref:`too-many-dereferencing`
* :ref:`use-url-query-functions`
* :ref:`make-functioncall-with-reference`
* :ref:`foreach()-favorite`
* :ref:`cant-implement-traversable`
* :ref:`parameter-hiding`
* :ref:`wrong-function-name-case`
* :ref:`propagate-calls`
* :ref:`is\_a()-with-string`
* :ref:`mbstring-unknown-encoding`
* :ref:`collect-mbstring-encodings`
* :ref:`weird-array-index`
* :ref:`filter-to-add\_slashes()`
* :ref:`mbstring-third-arg`
* :ref:`typehinting-stats`
* :ref:`typo-3-usage`
* :ref:`concrete-usage`
* :ref:`wrong-case-namespaces`
* :ref:`complete-createforeachdefault`
* :ref:`immutable-signature`
* :ref:`merge-if-then`
* :ref:`wrong-type-with-call`
* :ref:`could-type-with-int`
* :ref:`could-type-with-string`
* :ref:`could-type-with-array`
* :ref:`could-type-with-boolean`
* :ref:`shell-commands`
* :ref:`could-type-with-iterable`
* :ref:`insufficient-property-typehint`
* :ref:`inclusions`
* :ref:`typehint-order`
* :ref:`new-order`
* :ref:`wrong-typehinted-name`
* :ref:`links-between-parameter-and-argument`
* :ref:`exceeding-typehint`
* :ref:`nullable-without-check`
* :ref:`collect-class-interface-counts`
* :ref:`collect-class-depth`
* :ref:`collect-class-children-count`
* :ref:`semantic-typing`
* :ref:`missing-typehint`
* :ref:`fossilized-method`
* :ref:`not-equal-is-not-!==`
* :ref:`coalesce-equal`
* :ref:`possible-interfaces`
* :ref:`constant-order`
* :ref:`php-8.0-variable-syntax-tweaks`
* :ref:`new-functions-in-php-8.0`
* :ref:`dont-collect-void`
* :ref:`php-8.0-only-typehints`
* :ref:`union-typehint`
* :ref:`uninitialized-property`
* :ref:`wrong-typed-property-default`
* :ref:`signature-trailing-comma`
* :ref:`hidden-nullable-typehint`
* :ref:`fn-argument-variable-confusion`
* :ref:`missing-abstract-method`
* :ref:`throw-was-an-expression`
* :ref:`openssl-ciphers-used`
* :ref:`unused-trait-in-class`
* :ref:`keep-files-access-restricted`
* :ref:`check-crypto-key-length`
* :ref:`undefined-constant-name`
* :ref:`dynamic-self-calls`
* :ref:`prefix-and-suffixes-with-typehint`
* :ref:`using-deprecated-method`
* :ref:`too-long-a-block`
* :ref:`static-global-variables-confusion`
* :ref:`possible-alias-confusion`
* :ref:`collect-property-counts`
* :ref:`collect-method-counts`
* :ref:`collect-class-constant-counts`
* :ref:`too-much-indented`
* :ref:`safe-phpvariables`
* :ref:`could-be-string`
* :ref:`could-be-boolean`
* :ref:`could-be-void`
* :ref:`extended-typehints`
* :ref:`could-be-array-typehint`
* :ref:`could-be-cit`
* :ref:`protocol-lists`
* :ref:`cyclic-references`
* :ref:`double-object-assignation`
* :ref:`could-not-type`
* :ref:`could-be-callable`
* :ref:`wrong-argument-type`
* :ref:`could-be-integer`
* :ref:`call-order`
* :ref:`could-be-null`
* :ref:`could-be-iterable`
* :ref:`uses-php-8-match()`
* :ref:`could-be-float`
* :ref:`mismatch-properties-typehints`
* :ref:`could-be-self`
* :ref:`could-be-parent`
* :ref:`collect-parameter-names`
* :ref:`no-need-for-triple-equal`
* :ref:`array\_merge-needs-array-of-arrays`
* :ref:`dont-compare-typed-boolean`
* :ref:`abstract-away`
* :ref:`wrong-type-for-native-php-function`
* :ref:`large-try-block`
* :ref:`catch-undefined-variable`
* :ref:`swapped-arguments`
* :ref:`dump-fossilizedmethods`
* :ref:`glob\_brace-usage`
* :ref:`iconv-with-translit`
* :ref:`collect-static-class-changes`
* :ref:`different-argument-counts`
* :ref:`use-php-attributes`
* :ref:`use-nullsafe-operator`
* :ref:`use-closure-trailing-comma`
* :ref:`unknown-parameter-name`
* :ref:`missing-some-returntype`
* :ref:`don't-pollute-global-space`
* :ref:`collect-variables`
* :ref:`could-be-parent-method`
* :ref:`collect-global-variables`
* :ref:`collect-readability`
* :ref:`collect-definitions-statistics`
* :ref:`collect-class-traits-counts`
* :ref:`collect-native-calls-per-expressions`
* :ref:`cancel-common-method`
* :ref:`function-with-dynamic-code`
* :ref:`cast-unset-usage`
* :ref:`$php\_errormsg-usage`
* :ref:`mismatch-parameter-name`
* :ref:`multiple-declaration-of-strict\_types`
* :ref:`collect-files-dependencies`
* :ref:`collect-atom-counts`
* :ref:`collect-classes-dependencies`
* :ref:`collect-php-structures`
* :ref:`mismatch-parameter-and-type`
* :ref:`array\_fill()-with-objects`
* :ref:`modified-typed-parameter`
* :ref:`assumptions`
* :ref:`collect-use-counts`
* :ref:`useless-typehint`
* :ref:`php-8.0-removed-directives`
* :ref:`unsupported-types-with-operators`
* :ref:`negative-start-index-in-array`
* :ref:`complete-phpextstubpropertymethod`
* :ref:`optimize-explode()`
* :ref:`could-use-promoted-properties`
* :ref:`could-be-stringable`
* :ref:`nullable-with-constant`
* :ref:`use-get\_debug\_type()`
* :ref:`collect-block-size`
* :ref:`use-str\_contains()`
* :ref:`php-8.0-resources-turned-into-objects`
* :ref:`php-80-named-parameter-variadic`
* :ref:`unused-exception-variable`
* :ref:`wrong-attribute-configuration`
* :ref:`cancelled-parameter`
* :ref:`constant-typo-looks-like-a-variable`
* :ref:`final-private-methods`
* :ref:`array\_map()-passes-by-value`
* :ref:`missing-\_\_isset()-method`
* :ref:`searching-for-multiple-keys`
* :ref:`long-preparation-for-throw`
* :ref:`modify-immutable`
* :ref:`reserved-match-keyword`
* :ref:`no-static-variable-in-a-method`
* :ref:`declare-static-once`
* :ref:`avoid-get\_object\_vars()`
* :ref:`could-use-match`
* :ref:`only-container-for-reference`
* :ref:`cannot-use-static-for-closure`
* :ref:`multiple-property-declaration-on-one-line`
* :ref:`could-be-generator`
* :ref:`only-first-byte-`
* :ref:`restrict-global-usage`
* :ref:`inherited-property-type-must-match`
* :ref:`no-object-as-index`
* :ref:`class-overreach`
* :ref:`inherited-static-variable`
* :ref:`enum-usage`
* :ref:`php-8.1-removed-directives`
* :ref:`htmlentities-using-default-flag`
* :ref:`openssl-encrypt-default-algorithm-change`
* :ref:`php-8.1-removed-constants`
* :ref:`wrong-argument-name-with-php-function`
* :ref:`duplicate-named-parameter`
* :ref:`php-native-class-type-compatibility`
* :ref:`missing-attribute-attribute`
* :ref:`$files-full\_path`
* :ref:`no-null-for-native-php-functions`
* :ref:`calling-static-trait-method`
* :ref:`no-referenced-void`
* :ref:`jsonserialize()-mixed-return-type`
* :ref:`final-constant`
* :ref:`never-typehint-usage`
* :ref:`php-8.1-typehints`
* :ref:`php-8.0-typehints`
* :ref:`named-parameter-usage`
* :ref:`first-class-callable`
* :ref:`new-functions-in-php-8.1`
* :ref:`php-8.1-removed-functions`
* :ref:`never-keyword`
* :ref:`mixed-typehint-usage`
* :ref:`false-to-array-conversion`
* :ref:`float-conversion-as-index`
* :ref:`cannot-call-trait-method-directly`
* :ref:`nested-attributes`
* :ref:`new-initializers`
* :ref:`deprecated-callable`
* :ref:`promoted-properties`
* :ref:`overwritten-foreach-var`
* :ref:`null-type-favorite`
* :ref:`checks-property-existence`
* :ref:`variable-typehint`
* :ref:`extends-stdclass`
* :ref:`scope-resolution-operator`
* :ref:`could-use-nullable-object-operator`
* :ref:`cant-overload-constants`
* :ref:`variable-is-a-local-constant`
* :ref:`classes-couldbeiterable`
* :ref:`intersection-typehint`
* :ref:`abstract-class-constants`
* :ref:`variables-recycledvariables`
* :ref:`check-division-by-zero`
* :ref:`patterns-gettersetter`



Specs
^^^^^
+--------------+------------------------------------------------------------------------------------------------------------------------+
| Short name   | All                                                                                                                    |
+--------------+------------------------------------------------------------------------------------------------------------------------+
| Available in | `Entreprise Edition <https://www.exakat.io/entreprise-edition>`_, `Exakat Cloud <https://www.exakat.io/exakat-cloud>`_ |
+--------------+------------------------------------------------------------------------------------------------------------------------+


.. _ruleset-compatibilityphp82:

CompatibilityPHP82
++++++++++++++++++

This ruleset centralizes all analysis for the migration from PHP 8.1 to 8.2.

Total : 6 analysis

* :ref:`false-to-array-conversion`
* :ref:`float-conversion-as-index`
* :ref:`cannot-call-trait-method-directly`
* :ref:`deprecated-callable`
* :ref:`checks-property-existence`
* :ref:`extends-stdclass`



Specs
^^^^^
+--------------+----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| Short name   | CompatibilityPHP82                                                                                                                                                                     |
+--------------+----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| Available in | `Entreprise Edition <https://www.exakat.io/entreprise-edition>`_, `Community Edition <https://www.exakat.io/community-edition>`_, `Exakat Cloud <https://www.exakat.io/exakat-cloud>`_ |
+--------------+----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| Reports      | :ref:`report-diplomat`, :ref:`report-ambassador`                                                                                                                                       |
+--------------+----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+


.. _ruleset-classdependencies:

Classdependencies
+++++++++++++++++

This ruleset list all dependencies between classes : heritage and type.

Total : 1 analysis

* :ref:`collect-classes-dependencies`



Specs
^^^^^
+--------------+------------------------------------------------------------------------------------------------------------------------+
| Short name   | Classdependencies                                                                                                      |
+--------------+------------------------------------------------------------------------------------------------------------------------+
| Available in | `Entreprise Edition <https://www.exakat.io/entreprise-edition>`_, `Exakat Cloud <https://www.exakat.io/exakat-cloud>`_ |
+--------------+------------------------------------------------------------------------------------------------------------------------+
| Reports      | :ref:`report-classdependencies`                                                                                        |
+--------------+------------------------------------------------------------------------------------------------------------------------+



