.. _Cobblers:

Cobblers
=================

Introduction
--------------------------
Cobblers mend PHP code. They apply a transformation to it. 

Cobblers are a complement to code analysis : the analysis spot code to be fixed, the cobbler mends the code. Later, the analysis doesn't find those issues anymore.

List of Cobblers
--------------------------

.. _attributes-createphpdoc:

.. _create-phpdoc:

Create Phpdoc
_____________
Create PHPdoc comments for classes, interfaces, traits, methods and functions.

Parameters and return types are collected, along with the name of the structure.


.. _create-phpdoc-before:

Before
^^^^^^
.. code-block:: php

   '<?php
   
   class y {
       function a1(string $error, R $r = null) : int|string
       {
   
       }
   ?>

.. _create-phpdoc-after:

After
^^^^^
.. code-block:: php

   <?php
   
   /**
    * Name : y
    */
   class y {
      /**
       * Name : a1
       *
       * string $error
       * null|R $r
       * @return int|string
       *
       */
       function a1(string $error, R $r = null) : int|string
       {
   
       }
   ?>

.. _create-phpdoc-reverse-cobbler:

Reverse Cobbler
^^^^^^^^^^^^^^^

* :ref:`No anchor for Attributes/RemovePhpdoc <no-anchor-for-attributes-removephpdoc>`



.. _create-phpdoc-specs:

Specs
^^^^^

+----------------+------------------------------------------------------------------+
| Short Name     | Attributes/CreatePhpdoc                                          |
+----------------+------------------------------------------------------------------+
| Exakat version | 2.3.0                                                            |
+----------------+------------------------------------------------------------------+
| Available in   | `Entreprise Edition <https://www.exakat.io/entreprise-edition>`_ |
+----------------+------------------------------------------------------------------+


.. _classes-splitpropertydefinitions:

.. _split-property-definitions:

Split Property Definitions
__________________________
Split multiple properties definition into independent definitions. 

This applies to classes and traits. 

.. _split-property-definitions-before:

Before
^^^^^^
.. code-block:: php

   <?php
       class x {
           private $x, $y, $z;
       }
   ?>
   

.. _split-property-definitions-after:

After
^^^^^
.. code-block:: php

   <?php
       class x {
           private $x;
           private $y;
           private $z;
       }
   ?>

.. _split-property-definitions-suggested-analysis:

Suggested Analysis
^^^^^^^^^^^^^^^^^^

* :ref:`multiple-property-declaration-on-one-line`



.. _split-property-definitions-specs:

Specs
^^^^^

+----------------+------------------------------------------------------------------+
| Short Name     | Classes/SplitPropertyDefinitions                                 |
+----------------+------------------------------------------------------------------+
| Exakat version | 2.3.0                                                            |
+----------------+------------------------------------------------------------------+
| Available in   | `Entreprise Edition <https://www.exakat.io/entreprise-edition>`_ |
+----------------+------------------------------------------------------------------+


.. _classes-vartopublic:

.. _var-to-public:

Var To Public
_____________
Replace the var syntax with public keyword. 

It is also possible to replace it with protected or private, with the parameter. 

.. _var-to-public-before:

Before
^^^^^^
.. code-block:: php

   <?php
   
   class x {
       var $y = 1;
   }
   ?>

.. _var-to-public-after:

After
^^^^^
.. code-block:: php

   <?php
   
   class x {
       public $y = 1;
   }
   ?>


.. _var-to-public-var\_to\_visibility:

Parameters
^^^^^^^^^^

+-------------------+---------+--------+--------------------------------------------------------------------------------------+
| Name              | Default | Type   | Description                                                                          |
+-------------------+---------+--------+--------------------------------------------------------------------------------------+
| var_to_visibility | public  | string | The destination visibility to be used. May be one of: public, protected or private.  |
+-------------------+---------+--------+--------------------------------------------------------------------------------------+

.. _var-to-public-related-cobbler:

Related Cobblers
^^^^^^^^^^^^^^^^

* :ref:`set-typehints`



.. _var-to-public-specs:

Specs
^^^^^

+----------------+------------------------------------------------------------------+
| Short Name     | Classes/VarToPublic                                              |
+----------------+------------------------------------------------------------------+
| Exakat version | 2.3.0                                                            |
+----------------+------------------------------------------------------------------+
| Available in   | `Entreprise Edition <https://www.exakat.io/entreprise-edition>`_ |
+----------------+------------------------------------------------------------------+


.. _functions-makestaticfunction:

.. _make-static-closures-and-arrow-functions:

Make Static Closures And Arrow Functions
________________________________________
Add the static option to closures and arrow functions. This prevents the defining environment to be included in the closure.



.. _make-static-closures-and-arrow-functions-before:

Before
^^^^^^
.. code-block:: php

   <?php
       $a = function () { return 1; };
       $b = fn () => 2;
   ?>
   

.. _make-static-closures-and-arrow-functions-after:

After
^^^^^
.. code-block:: php

   <?php
       $a = static function () { return 1; };
       $b = static fn () => 2;
   ?>

.. _make-static-closures-and-arrow-functions-suggested-analysis:

Suggested Analysis
^^^^^^^^^^^^^^^^^^

* :ref:`could-be-static-closure`

.. _make-static-closures-and-arrow-functions-reverse-cobbler:

Reverse Cobbler
^^^^^^^^^^^^^^^

* :ref:`No anchor for Functions/RemoveStaticFromFunction <no-anchor-for-functions-removestaticfromfunction>`



.. _make-static-closures-and-arrow-functions-specs:

Specs
^^^^^

+----------------+------------------------------------------------------------------+
| Short Name     | Functions/MakeStaticFunction                                     |
+----------------+------------------------------------------------------------------+
| Exakat version | 2.3.0                                                            |
+----------------+------------------------------------------------------------------+
| Available in   | `Entreprise Edition <https://www.exakat.io/entreprise-edition>`_ |
+----------------+------------------------------------------------------------------+


.. _functions-removestaticfromclosure:

.. _remove-static-from-closures-and-arrow-functions:

Remove Static From Closures And Arrow Functions
_______________________________________________
Removes the static option from closures and arrow functions.



.. _remove-static-from-closures-and-arrow-functions-before:

Before
^^^^^^
.. code-block:: php

   <?php
       $a = static function () { return 1; };
       $b = static fn () => 2;
   ?>
   

.. _remove-static-from-closures-and-arrow-functions-after:

After
^^^^^
.. code-block:: php

   <?php
       $a = function () { return 1; };
       $b = fn () => 2;
   ?>

.. _remove-static-from-closures-and-arrow-functions-suggested-analysis:

Suggested Analysis
^^^^^^^^^^^^^^^^^^

* :ref:`cannot-use-static-for-closure`

.. _remove-static-from-closures-and-arrow-functions-reverse-cobbler:

Reverse Cobbler
^^^^^^^^^^^^^^^

* :ref:`make-static-closures-and-arrow-functions`



.. _remove-static-from-closures-and-arrow-functions-specs:

Specs
^^^^^

+----------------+------------------------------------------------------------------+
| Short Name     | Functions/RemoveStaticFromClosure                                |
+----------------+------------------------------------------------------------------+
| Exakat version | 2.3.0                                                            |
+----------------+------------------------------------------------------------------+
| Available in   | `Entreprise Edition <https://www.exakat.io/entreprise-edition>`_ |
+----------------+------------------------------------------------------------------+


.. _functions-removetypes:

.. _remove-typehint:

Remove Typehint
_______________
This cobbler remove the typehint mentions in the code. This might yield some speed when executing, since those tests will be not conveyed at runtime. 

Typehints from arguments, method returns and properties are all removed. 


.. _remove-typehint-before:

Before
^^^^^^
.. code-block:: php

   <?php
   
   class x {
       private string $p;
       
       function foo(D\E $arg) : void {
       
       }
   }
   
   ?>

.. _remove-typehint-after:

After
^^^^^
.. code-block:: php

   <?php
   
   class x {
       private $p;
       
       function foo($arg) {
       
       }
   }
   
   ?>


.. _remove-typehint-type\_to\_remove:

Parameters
^^^^^^^^^^

+----------------+---------+------+----------------------------------------------------------------------------------------------------------+
| Name           | Default | Type | Description                                                                                              |
+----------------+---------+------+----------------------------------------------------------------------------------------------------------+
| type_to_remove | all     | data | A comma separated list of types to remove. For example : never,string,A\B\C;. Use 'All' for everyt type. |
+----------------+---------+------+----------------------------------------------------------------------------------------------------------+

.. _remove-typehint-suggested-analysis:

Suggested Analysis
^^^^^^^^^^^^^^^^^^

* :ref:`php-8.1-typehints`

.. _remove-typehint-reverse-cobbler:

Reverse Cobbler
^^^^^^^^^^^^^^^

* :ref:`set-typehints`



.. _remove-typehint-specs:

Specs
^^^^^

+----------------+------------------------------------------------------------------+
| Short Name     | Functions/RemoveTypes                                            |
+----------------+------------------------------------------------------------------+
| Exakat version | 2.2.5                                                            |
+----------------+------------------------------------------------------------------+
| Available in   | `Entreprise Edition <https://www.exakat.io/entreprise-edition>`_ |
+----------------+------------------------------------------------------------------+


.. _functions-renameparameter:

.. _rename-parameter:

Rename Parameter
________________
Change the name of a parameter to a new name.

The destination parameter name is a constant. 
Suggestions : rename all parameters from the top method (in classes)
rename parameters $a into $b (currently, no $a available)

Limits : this cobbler doesn't check that another parameter is already using that name, nor if a local variable is also using that name. This may lead to unexpected results.


.. _rename-parameter-before:

Before
^^^^^^
.. code-block:: php

   <?php
   
   foo(a: 1);
   
   function foo($a) { 
       return $a;
   }
   
   ?>

.. _rename-parameter-after:

After
^^^^^
.. code-block:: php

   <?php
   
   foo(b: 1);
   
   function foo($b) { 
       return $b;
   }
   
   ?>


.. _rename-parameter-method:

Parameters
^^^^^^^^^^

+---------+---------+--------+------------------------------------------------------------------------------------------------------------------+
| Name    | Default | Type   | Description                                                                                                      |
+---------+---------+--------+------------------------------------------------------------------------------------------------------------------+
| oldName | $A      | string | The original name of the parameter.                                                                              |
+---------+---------+--------+------------------------------------------------------------------------------------------------------------------+
| newName | $B      | string | The new name of the parameter.                                                                                   |
+---------+---------+--------+------------------------------------------------------------------------------------------------------------------+
| method  |         | string | The name of the target method. Use a full qualified name for a function, and the class name::method for methods. |
+---------+---------+--------+------------------------------------------------------------------------------------------------------------------+



.. _rename-parameter-specs:

Specs
^^^^^

+----------------+------------------------------------------------------------------+
| Short Name     | Functions/RenameParameter                                        |
+----------------+------------------------------------------------------------------+
| Exakat version | 2.3.0                                                            |
+----------------+------------------------------------------------------------------+
| Available in   | `Entreprise Edition <https://www.exakat.io/entreprise-edition>`_ |
+----------------+------------------------------------------------------------------+


.. _functions-setnulltype:

.. _set-null-type:

Set Null Type
_____________
Adds a Null type to typehints when necessary. 

This cobbler only adds a null type when there is already another type. It doesn't add a null type when no type is set. 

It works on methods, functions, closures and arrow functions. It doesn't work on properties.

The null type is added as a question mark `?` when the type is unique, and as null when the types are multiple.


.. _set-null-type-before:

Before
^^^^^^
.. code-block:: php

   <?php
   
   function foo() : int {
       if (rand(0, 1)) {
           return 1;
       } else {
           return null;
       }
   }
   
   ?>

.. _set-null-type-after:

After
^^^^^
.. code-block:: php

   <?php
   
   function foo() : ?int {
       if (rand(0, 1)) {
           return 1;
       } else {
           return null;
       }
   }
   
   ?>

.. _set-null-type-reverse-cobbler:

Reverse Cobbler
^^^^^^^^^^^^^^^

* :ref:`remove-typehint`



.. _set-null-type-specs:

Specs
^^^^^

+----------------+------------------------------------------------------------------+
| Short Name     | Functions/SetNullType                                            |
+----------------+------------------------------------------------------------------+
| Exakat version | 2.3.0                                                            |
+----------------+------------------------------------------------------------------+
| Available in   | `Entreprise Edition <https://www.exakat.io/entreprise-edition>`_ |
+----------------+------------------------------------------------------------------+


.. _functions-settypevoid:

.. _set-type-void:

Set Type Void
_____________
Adds the void typehint to functions and methods, when possible

.. _set-type-void-before:

Before
^^^^^^
.. code-block:: php

   <?php
   
   function foo() {
       return;
   }
   
   ?>

.. _set-type-void-after:

After
^^^^^
.. code-block:: php

   <?php
   
   function foo() : void {
       return;
   }
   
   ?>

.. _set-type-void-suggested-analysis:

Suggested Analysis
^^^^^^^^^^^^^^^^^^

* :ref:`could-be-void`

.. _set-type-void-related-cobbler:

Related Cobblers
^^^^^^^^^^^^^^^^

* :ref:`set-typehints`
* :ref:`set-null-type`

.. _set-type-void-reverse-cobbler:

Reverse Cobbler
^^^^^^^^^^^^^^^

* :ref:`remove-typehint`



.. _set-type-void-specs:

Specs
^^^^^

+----------------+------------------------------------------------------------------+
| Short Name     | Functions/SetTypeVoid                                            |
+----------------+------------------------------------------------------------------+
| Exakat version | 2.3.0                                                            |
+----------------+------------------------------------------------------------------+
| Available in   | `Entreprise Edition <https://www.exakat.io/entreprise-edition>`_ |
+----------------+------------------------------------------------------------------+


.. _functions-settypehints:

.. _set-typehints:

Set Typehints
_____________
Automagically add scalar typehints to methods and properties. Arguments and return values are both supported. 

When multiple possible types are identified, no typehint is added. If a typehint is already set, no typehint is added.

Magic methods, such as __get(), __set(), __construct(), __desctruct(), etc are not modified by this cobbler. 

Methods which have parent's methods (resp. children's) are skipped for argument typing (resp return typing) : this may introduce a incompatible definition. On the other hand, methods which have children's methods (resp. parents') are modified for argument typing (resp return typing), thanks to covariance (resp. contravariance). 

Void (as a scalar type) and Null types are processed in a separate cobbler. 

By default, and in case of conflict, array is chosen over iterable and int is chosen over float. There are parameter to alter this behavior.



.. _set-typehints-before:

Before
^^^^^^
.. code-block:: php

   <?php
   
   class x {
       private int $p = 2;
   
       function foo(int $a = 1) : int {
           return intdiv($a, $this->p);
       }
   }
   ?>

.. _set-typehints-after:

After
^^^^^
.. code-block:: php

   <?php
   
   class x {
       private int $p = 2;
   
       function foo(int $a = 1) : int {
           return intdiv($a, $this->p);
       }
   }
   ?>
   


.. _set-typehints-int\_or\_float:

Parameters
^^^^^^^^^^

+-------------------+---------+--------+-------------------------------------------------------------------------------------------------------------------+
| Name              | Default | Type   | Description                                                                                                       |
+-------------------+---------+--------+-------------------------------------------------------------------------------------------------------------------+
| array_or_iterable | array   | string | When array and iterable are the only suggestions, choose 'array', 'iterable', or 'omit'. By default, it is array. |
+-------------------+---------+--------+-------------------------------------------------------------------------------------------------------------------+
| int_or_float      | float   | string | When int and float are the only suggestions, choose 'int', 'float', or 'omit'. By default, it is float.           |
+-------------------+---------+--------+-------------------------------------------------------------------------------------------------------------------+

.. _set-typehints-suggested-analysis:

Suggested Analysis
^^^^^^^^^^^^^^^^^^

* :ref:`could-be-void`

.. _set-typehints-related-cobbler:

Related Cobblers
^^^^^^^^^^^^^^^^

* :ref:`var-to-public`
* :ref:`split-property-definitions`
* :ref:`set-null-type`
* :ref:`set-type-void`



.. _set-typehints-specs:

Specs
^^^^^

+----------------+------------------------------------------------------------------------------------------------------------------------+
| Short Name     | Functions/SetTypehints                                                                                                 |
+----------------+------------------------------------------------------------------------------------------------------------------------+
| Exakat version | 2.3.0                                                                                                                  |
+----------------+------------------------------------------------------------------------------------------------------------------------+
| Available in   | `Entreprise Edition <https://www.exakat.io/entreprise-edition>`_, `Exakat Cloud <https://www.exakat.io/exakat-cloud>`_ |
+----------------+------------------------------------------------------------------------------------------------------------------------+


.. _namespaces-gatheruse:

.. _gather-use-expression:

Gather Use Expression
_____________________
Move lone use expression to the beginning of the file

.. _gather-use-expression-before:

Before
^^^^^^
.. code-block:: php

   <?php
       use A;
       ++$a;
       use B;
   ?>
   

.. _gather-use-expression-after:

After
^^^^^
.. code-block:: php

   <?php
       use A;
       use B;
       ++$a;
   ?>

.. _gather-use-expression-suggested-analysis:

Suggested Analysis
^^^^^^^^^^^^^^^^^^

* :ref:`hidden-use-expression`



.. _gather-use-expression-specs:

Specs
^^^^^

+----------------+------------------------------------------------------------------+
| Short Name     | Namespaces/GatherUse                                             |
+----------------+------------------------------------------------------------------+
| Exakat version | 2.3.0                                                            |
+----------------+------------------------------------------------------------------+
| Available in   | `Entreprise Edition <https://www.exakat.io/entreprise-edition>`_ |
+----------------+------------------------------------------------------------------+


.. _namespaces-usealias:

.. _use-available-alias:

Use Available Alias
___________________
Apply systematically the use expression in the code.

.. _use-available-alias-before:

Before
^^^^^^
.. code-block:: php

   <?php
       use A\B\C as D;
       new A\B\C();
   ?>
   

.. _use-available-alias-after:

After
^^^^^
.. code-block:: php

   <?php
       use A\B\C as D;
       new D();
   ?>

.. _use-available-alias-suggested-analysis:

Suggested Analysis
^^^^^^^^^^^^^^^^^^

* :ref:`could-use-alias`



.. _use-available-alias-specs:

Specs
^^^^^

+----------------+------------------------------------------------------------------+
| Short Name     | Namespaces/UseAlias                                              |
+----------------+------------------------------------------------------------------+
| Exakat version | 2.3.0                                                            |
+----------------+------------------------------------------------------------------+
| Available in   | `Entreprise Edition <https://www.exakat.io/entreprise-edition>`_ |
+----------------+------------------------------------------------------------------+


.. _structures-addnoscream:

.. _add-no-scream-@:

Add No Scream @
_______________
Adds the no scream operator `@` to an expression. 

.. _add-no-scream-@-before:

Before
^^^^^^
.. code-block:: php

   <?php
       $a;
   ?>

.. _add-no-scream-@-after:

After
^^^^^
.. code-block:: php

   <?php
       @$a;
   ?>

.. _add-no-scream-@-suggested-analysis:

Suggested Analysis
^^^^^^^^^^^^^^^^^^

* :ref:`No anchor for Utils/Selector <no-anchor-for-utils-selector>`

.. _add-no-scream-@-reverse-cobbler:

Reverse Cobbler
^^^^^^^^^^^^^^^

* :ref:`remove-noscream-@`



.. _add-no-scream-@-specs:

Specs
^^^^^

+----------------+------------------------------------------------------------------+
| Short Name     | Structures/AddNoScream                                           |
+----------------+------------------------------------------------------------------+
| Exakat version | 2.3.0                                                            |
+----------------+------------------------------------------------------------------+
| Available in   | `Entreprise Edition <https://www.exakat.io/entreprise-edition>`_ |
+----------------+------------------------------------------------------------------+


.. _structures-arraytobracket:

.. _array-to-bracket:

Array To Bracket
________________
This cobbler updates the array() syntax, and changes it to the bracket syntax.


.. _array-to-bracket-before:

Before
^^^^^^
.. code-block:: php

   <?php
   $a = array(1, 2, 3);
   ?>

.. _array-to-bracket-after:

After
^^^^^
.. code-block:: php

   <?php
   $a = [1, 2, 3];
   ?>



.. _array-to-bracket-specs:

Specs
^^^^^

+----------------+------------------------------------------------------------------+
| Short Name     | Structures/ArrayToBracket                                        |
+----------------+------------------------------------------------------------------+
| Exakat version | 2.3.0                                                            |
+----------------+------------------------------------------------------------------+
| Available in   | `Entreprise Edition <https://www.exakat.io/entreprise-edition>`_ |
+----------------+------------------------------------------------------------------+


.. _structures-plusonetopre:

.. _plus-one-to-pre-plusplus:

Plus One To Pre Plusplus
________________________
Transforms a `+ 1` or `- 1` operation into a plus-plus (or minus-minus).

.. _plus-one-to-pre-plusplus-before:

Before
^^^^^^
.. code-block:: php

   <?php
       $a = $a + 1;
   ?>

.. _plus-one-to-pre-plusplus-after:

After
^^^^^
.. code-block:: php

   <?php
       ++$a;
   ?>



.. _plus-one-to-pre-plusplus-specs:

Specs
^^^^^

+----------------+------------------------------------------------------------------------------------------------------------------------+
| Short Name     | Structures/PlusOneToPre                                                                                                |
+----------------+------------------------------------------------------------------------------------------------------------------------+
| Exakat version | 2.3.0                                                                                                                  |
+----------------+------------------------------------------------------------------------------------------------------------------------+
| Available in   | `Entreprise Edition <https://www.exakat.io/entreprise-edition>`_, `Exakat Cloud <https://www.exakat.io/exakat-cloud>`_ |
+----------------+------------------------------------------------------------------------------------------------------------------------+


.. _structures-posttopre:

.. _post-to-pre-plusplus:

Post to Pre Plusplus
____________________
Transforms a post plus-plus (or minus-minus) operator, into a pre plus-plus (or minus-minus) operator.



.. _post-to-pre-plusplus-before:

Before
^^^^^^
.. code-block:: php

   <?php 
       $a++;
   ?>

.. _post-to-pre-plusplus-after:

After
^^^^^
.. code-block:: php

   <?php
       ++$a;
   ?>



.. _post-to-pre-plusplus-specs:

Specs
^^^^^

+----------------+------------------------------------------------------------------------------------------------------------------------+
| Short Name     | Structures/PostToPre                                                                                                   |
+----------------+------------------------------------------------------------------------------------------------------------------------+
| Exakat version | 2.3.0                                                                                                                  |
+----------------+------------------------------------------------------------------------------------------------------------------------+
| Available in   | `Entreprise Edition <https://www.exakat.io/entreprise-edition>`_, `Exakat Cloud <https://www.exakat.io/exakat-cloud>`_ |
+----------------+------------------------------------------------------------------------------------------------------------------------+


.. _structures-removecode:

.. _remove-instructions:

Remove Instructions
___________________
Removes atomic instructions from the code. The whole expression is removed, and the slot is closed. 

This cobbler works with element of a block, and not with part of larger expression (like remove a condition in a if/then, or remove the block expression of a while). 

.. _remove-instructions-before:

Before
^^^^^^
.. code-block:: php

   <?php
       $a = 1; // Code to be removed
       foo(1); 
       
       do          // can remove the while expression
           ++$a;   // removing the block of the do...wihle will generate an compilation error
       while ($a < 10);
       
   ?>

.. _remove-instructions-after:

After
^^^^^
.. code-block:: php

   <?php
       foo(1); 
   ?>

.. _remove-instructions-suggested-analysis:

Suggested Analysis
^^^^^^^^^^^^^^^^^^

* :ref:`useless-instructions`



.. _remove-instructions-specs:

Specs
^^^^^

+----------------+------------------------------------------------------------------+
| Short Name     | Structures/RemoveCode                                            |
+----------------+------------------------------------------------------------------+
| Exakat version | 2.3.0                                                            |
+----------------+------------------------------------------------------------------+
| Available in   | `Entreprise Edition <https://www.exakat.io/entreprise-edition>`_ |
+----------------+------------------------------------------------------------------+


.. _structures-removenoscream:

.. _remove-noscream-@:

Remove Noscream @
_________________
Removes the @ operator.

.. _remove-noscream-@-before:

Before
^^^^^^
.. code-block:: php

   <?php
       @$a;
   ?>

.. _remove-noscream-@-after:

After
^^^^^
.. code-block:: php

   <?php
       $a;
   ?>

.. _remove-noscream-@-suggested-analysis:

Suggested Analysis
^^^^^^^^^^^^^^^^^^

* :ref:`@-operator`

.. _remove-noscream-@-reverse-cobbler:

Reverse Cobbler
^^^^^^^^^^^^^^^

* This cobbler is its own reverse. 



.. _remove-noscream-@-specs:

Specs
^^^^^

+----------------+------------------------------------------------------------------------------------------------------------------------+
| Short Name     | Structures/RemoveNoScream                                                                                              |
+----------------+------------------------------------------------------------------------------------------------------------------------+
| Exakat version | 2.3.0                                                                                                                  |
+----------------+------------------------------------------------------------------------------------------------------------------------+
| Available in   | `Entreprise Edition <https://www.exakat.io/entreprise-edition>`_, `Exakat Cloud <https://www.exakat.io/exakat-cloud>`_ |
+----------------+------------------------------------------------------------------------------------------------------------------------+


.. _structures-removeparenthesis:

.. _remove-parenthesis:

Remove Parenthesis
__________________
Remove useless parenthesis from return expression.

.. _remove-parenthesis-before:

Before
^^^^^^
.. code-block:: php

   <?php
   function foo() {
       return (1);
   }
   ?>

.. _remove-parenthesis-after:

After
^^^^^
.. code-block:: php

   <?php
   function foo() {
       return 1;
   }
   ?>

.. _remove-parenthesis-suggested-analysis:

Suggested Analysis
^^^^^^^^^^^^^^^^^^

* :ref:`no-parenthesis-for-language-construct`



.. _remove-parenthesis-specs:

Specs
^^^^^

+----------------+------------------------------------------------------------------+
| Short Name     | Structures/RemoveParenthesis                                     |
+----------------+------------------------------------------------------------------+
| Exakat version | 2.3.0                                                            |
+----------------+------------------------------------------------------------------+
| Available in   | `Entreprise Edition <https://www.exakat.io/entreprise-edition>`_ |
+----------------+------------------------------------------------------------------+


.. _structures-renamefunction:

.. _rename-a-function:

Rename A Function
_________________
Give a function with a new name. 

This cobbler doesn't update the name of the functioncalls. 

This cobbler may be used with functions, and methods. Functions may be identified with their fully qualified name (i.e. \path\foo) and methods with the extended fully qualified name (i.e. : \path\aClass::methodName). 



.. _rename-a-function-before:

Before
^^^^^^
.. code-block:: php

   <?php
       function foo() {
       
       }
   ?>

.. _rename-a-function-after:

After
^^^^^
.. code-block:: php

   <?php
       function bar() {
       
       }
   ?>


.. _rename-a-function-name:

Parameters
^^^^^^^^^^

+------+---------+--------+-------------------------------+
| Name | Default | Type   | Description                   |
+------+---------+--------+-------------------------------+
| name | foo     | string | The new name of the function. |
+------+---------+--------+-------------------------------+

.. _rename-a-function-suggested-analysis:

Suggested Analysis
^^^^^^^^^^^^^^^^^^

* :ref:`No anchor for Utils/Selector <no-anchor-for-utils-selector>`

.. _rename-a-function-related-cobbler:

Related Cobblers
^^^^^^^^^^^^^^^^

* :ref:`rename-functioncalls`

.. _rename-a-function-reverse-cobbler:

Reverse Cobbler
^^^^^^^^^^^^^^^

* This cobbler is its own reverse. 



.. _rename-a-function-specs:

Specs
^^^^^

+----------------+------------------------------------------------------------------+
| Short Name     | Structures/RenameFunction                                        |
+----------------+------------------------------------------------------------------+
| Exakat version | 2.3.0                                                            |
+----------------+------------------------------------------------------------------+
| Available in   | `Entreprise Edition <https://www.exakat.io/entreprise-edition>`_ |
+----------------+------------------------------------------------------------------+


.. _structures-renamefunctioncall:

.. _rename-functioncalls:

Rename FunctionCalls
____________________
Rename a function call to another function.

.. _rename-functioncalls-before:

Before
^^^^^^
.. code-block:: php

   <?php
       foo(1, 2);
   ?>

.. _rename-functioncalls-after:

After
^^^^^
.. code-block:: php

   <?php
       bar(1, 2);
   ?>


.. _rename-functioncalls-destination:

Parameters
^^^^^^^^^^

+-------------+---------------+--------+-----------------------------------------------------------------------------------------+
| Name        | Default       | Type   | Description                                                                             |
+-------------+---------------+--------+-----------------------------------------------------------------------------------------+
| origin      | strtolower    | string | The function name to rename. It will be use lower-cased, and as a fully qualified name. |
+-------------+---------------+--------+-----------------------------------------------------------------------------------------+
| destination | mb_strtolower | string | The function name to rename. It will be use as is. FQN is possible.                     |
+-------------+---------------+--------+-----------------------------------------------------------------------------------------+

.. _rename-functioncalls-suggested-analysis:

Suggested Analysis
^^^^^^^^^^^^^^^^^^

* :ref:`No anchor for Utils/Selector <no-anchor-for-utils-selector>`

.. _rename-functioncalls-related-cobbler:

Related Cobblers
^^^^^^^^^^^^^^^^

* :ref:`rename-a-function`
* :ref:`rename-methodcall`

.. _rename-functioncalls-reverse-cobbler:

Reverse Cobbler
^^^^^^^^^^^^^^^

* This cobbler is its own reverse. 



.. _rename-functioncalls-specs:

Specs
^^^^^

+----------------+------------------------------------------------------------------+
| Short Name     | Structures/RenameFunctionCall                                    |
+----------------+------------------------------------------------------------------+
| Exakat version | 2.3.0                                                            |
+----------------+------------------------------------------------------------------+
| Available in   | `Entreprise Edition <https://www.exakat.io/entreprise-edition>`_ |
+----------------+------------------------------------------------------------------+


.. _structures-renamemethodcall:

.. _rename-methodcall:

Rename Methodcall
_________________
Rename a method, in a methodcall, with a new name. 

This cobbler doesn't update the definition of the method. It works both on static and non-static methods. 



.. _rename-methodcall-before:

Before
^^^^^^
.. code-block:: php

   <?php
       $o->method();
   ?>

.. _rename-methodcall-after:

After
^^^^^
.. code-block:: php

   <?php
       $o->newName();
   ?>


.. _rename-methodcall-destination:

Parameters
^^^^^^^^^^

+-------------+---------------+--------+-----------------------------------------------------------------------------------------+
| Name        | Default       | Type   | Description                                                                             |
+-------------+---------------+--------+-----------------------------------------------------------------------------------------+
| origin      | strtolower    | string | The function name to rename. It will be use lower-cased, and as a fully qualified name. |
+-------------+---------------+--------+-----------------------------------------------------------------------------------------+
| destination | mb_strtolower | string | The function name to rename. It will be use as is. FQN is possible.                     |
+-------------+---------------+--------+-----------------------------------------------------------------------------------------+

.. _rename-methodcall-suggested-analysis:

Suggested Analysis
^^^^^^^^^^^^^^^^^^

* :ref:`No anchor for Utils/Selector <no-anchor-for-utils-selector>`

.. _rename-methodcall-related-cobbler:

Related Cobblers
^^^^^^^^^^^^^^^^

* :ref:`rename-functioncalls`
* :ref:`rename-a-function`

.. _rename-methodcall-reverse-cobbler:

Reverse Cobbler
^^^^^^^^^^^^^^^

* :ref:`No anchor for Structures/RemoveMethodCall <no-anchor-for-structures-removemethodcall>`



.. _rename-methodcall-specs:

Specs
^^^^^

+----------------+------------------------------------------------------------------+
| Short Name     | Structures/RenameMethodcall                                      |
+----------------+------------------------------------------------------------------+
| Exakat version | 2.3.0                                                            |
+----------------+------------------------------------------------------------------+
| Available in   | `Entreprise Edition <https://www.exakat.io/entreprise-edition>`_ |
+----------------+------------------------------------------------------------------+


.. _structures-switchtomatch:

.. _switch-to-match:

Switch To Match
_______________
Transforms a switch() into a match() expression.

The switch() syntax must have each of the cases assigning the same variable (or similar). There should not be any other operation, besides break;



.. _switch-to-match-before:

Before
^^^^^^
.. code-block:: php

   <?php
       switch($a) {
           case 1: 
               $b = '1';
               break;
           case 2: 
               $b = '3';
               break;
           default:  
               $b = '0';
               break; 
       }
   ?>
   

.. _switch-to-match-after:

After
^^^^^
.. code-block:: php

   <?php
       $b = match($a) {
           1 => '1',
           2 => '3',
           default => '0'
       };
   ?>
   

.. _switch-to-match-suggested-analysis:

Suggested Analysis
^^^^^^^^^^^^^^^^^^

* :ref:`could-use-match`

.. _switch-to-match-related-cobbler:

Related Cobblers
^^^^^^^^^^^^^^^^

* :ref:`post-to-pre-plusplus`

.. _switch-to-match-reverse-cobbler:

Reverse Cobbler
^^^^^^^^^^^^^^^

* :ref:`remove-instructions`



.. _switch-to-match-specs:

Specs
^^^^^

+----------------+------------------------------------------------------------------+
| Short Name     | Structures/SwitchToMatch                                         |
+----------------+------------------------------------------------------------------+
| Exakat version | 2.3.0                                                            |
+----------------+------------------------------------------------------------------+
| Available in   | `Entreprise Edition <https://www.exakat.io/entreprise-edition>`_ |
+----------------+------------------------------------------------------------------+


.. _utils-multi:

.. _:





.. _-before:

Before
^^^^^^
.. code-block:: php

   

.. _-after:

After
^^^^^
.. code-block:: php

   


.. _-configfile:

Parameters
^^^^^^^^^^

+------------+---------+--------+---------------------------------------+
| Name       | Default | Type   | Description                           |
+------------+---------+--------+---------------------------------------+
| configFile |         | string | The .yaml file in the project folder. |
+------------+---------+--------+---------------------------------------+



.. _-specs:

Specs
^^^^^

+----------------+------------------------------------------------------------------+
| Short Name     | Utils/Multi                                                      |
+----------------+------------------------------------------------------------------+
| Exakat version | 2.3.0                                                            |
+----------------+------------------------------------------------------------------+
| Available in   | `Entreprise Edition <https://www.exakat.io/entreprise-edition>`_ |
+----------------+------------------------------------------------------------------+


.. _classes-removemethod:

.. _name:

name
____
Fully qualified name of the method to remove. Only one allowed.

.. _name-before:

Before
^^^^^^
.. code-block:: php

   <?php
   
   // removing method \x::method1 
   class x {
       function method1() {}
       function method2() {}
   }
   
   ?>

.. _name-after:

After
^^^^^
.. code-block:: php

   <?php
   
   // removed method \x::method1 
   class x {
       function method2() {}
   }
   
   ?>



.. _name-specs:

Specs
^^^^^

+----------------+----------------------+
| Short Name     | Classes/RemoveMethod |
+----------------+----------------------+
| Exakat version | 2.3.0                |
+----------------+----------------------+
| Available in   |                      |
+----------------+----------------------+


.. _attributes-removeattribute:

.. _remove-the-attribute:

Remove The Attribute
____________________
Remove attributes from all supporting structures.

Attributes are located on functions, classes, class constants, properties, methods and arguments.


.. _remove-the-attribute-before:

Before
^^^^^^
.. code-block:: php

   <?php
   
   #[Attribute] 
   function foo(#[AttributeArgument] $arg) {
   
   }
   ?>

.. _remove-the-attribute-after:

After
^^^^^
.. code-block:: php

   <?php
   
   
   function foo($arg) {
   
   }
   ?>



.. _remove-the-attribute-specs:

Specs
^^^^^

+----------------+----------------------------+
| Short Name     | Attributes/RemoveAttribute |
+----------------+----------------------------+
| Exakat version | 2.3.0                      |
+----------------+----------------------------+
| Available in   |                            |
+----------------+----------------------------+


.. _namespaces-removeuse:

.. _remove-unused-use:

Remove Unused Use
_________________
Removes the unused use expression from the top of the file. Groupuse are not processed yet.

.. _remove-unused-use-before:

Before
^^^^^^
.. code-block:: php

   <?php
   
   use a\b;
   use c\d;
   
   new b();
   
   ?>

.. _remove-unused-use-after:

After
^^^^^
.. code-block:: php

   <?php
   
   use a\b;
   
   new b();
   
   ?>

.. _remove-unused-use-suggested-analysis:

Suggested Analysis
^^^^^^^^^^^^^^^^^^

* :ref:`unused-use`



.. _remove-unused-use-specs:

Specs
^^^^^

+----------------+----------------------+
| Short Name     | Namespaces/RemoveUse |
+----------------+----------------------+
| Exakat version | 2.3.0                |
+----------------+----------------------+
| Available in   |                      |
+----------------+----------------------+



