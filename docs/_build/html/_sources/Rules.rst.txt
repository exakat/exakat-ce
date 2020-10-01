.. _Rules:

Rules list
----------

Introduction
############

.. comment: The rest of the document is automatically generated. Don't modify it manually. 
.. comment: Rules details
.. comment: Generation date : Thu, 01 Oct 2020 08:49:01 +0000
.. comment: Generation hash : d56341dff8b96c44eaed7292894efafcc7dad5e0


.. _adding-zero:

Adding Zero
###########


Adding 0 is useless, as 0 is the neutral element for addition. Besides, when one of the argument is an integer, PHP triggers a cast to integer. 

It is recommended to make the cast explicit with ``(int)``. 

.. code-block:: php

   <?php
   
   // Explicit cast
   $a = (int) foo();
   
   // Useless addition
   $a = foo() + 0;
   $a = 0 + foo();
   
   // Also works with minus
   $b = 0 - $c; // drop the 0, but keep the minus
   $b = $c - 0; // drop the 0 and the minus
   
   $a += 0;
   $a -= 0;
   
   ?>


Adding zero is also reported when the zero is a defined constants. 

If it is used to type cast a value to integer, then casting with ``(int)`` is clearer. 



Suggestions
^^^^^^^^^^^

* Remove the +/- 0, may be the whole assignation
* Use an explicit type casting operator (int)

+-------------+-----------------------------------------------------------------------------------------------+
| Short name  | Structures/AddZero                                                                            |
+-------------+-----------------------------------------------------------------------------------------------+
| Rulesets    | :ref:`Analyze`                                                                                |
+-------------+-----------------------------------------------------------------------------------------------+
| Severity    | Minor                                                                                         |
+-------------+-----------------------------------------------------------------------------------------------+
| Time To Fix | Instant (5 mins)                                                                              |
+-------------+-----------------------------------------------------------------------------------------------+
| ClearPHP    | `no-useless-math <https://github.com/dseguy/clearPHP/tree/master/rules/no-useless-math.md>`__ |
+-------------+-----------------------------------------------------------------------------------------------+
| Examples    | :ref:`thelia-structures-addzero`, :ref:`openemr-structures-addzero`                           |
+-------------+-----------------------------------------------------------------------------------------------+





