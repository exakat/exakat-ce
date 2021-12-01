.. _Cobbler:

Cobbler
=======

What are cobblers
-----------------

Cobblers mend PHP code. They apply a transformation to it. 

Cobblers are a complement to code analysis : the analysis spot code to be fixed, the cobbler mends the code. Later, the analysis doesn't find those issues anymore.

Cobbler command
---------------

To run a cobbler, use the `cobble` command.

:: 

   php exakat cobble -p <project> <write-options> -P <Cobbler/Name> 


The <project> parameter is the project on which the cobbler is run. It must have been `init`-ed with Exakat.

<Cobbler/Name> is the name of the cobbler to run. The list of available cobblers are in the documentation.

<write-options> configure the destination of the updated code. The available options are : 

+ --branch <branch> : the modified code is written in a new branch, called <branch>. The branch may be configured for each cobbler.
+ --inplace : the analyzed code is replaced by the modified code. This cannot be reverted 
+ -f <filename> : the modified code is written in the <filename> file. Only one file is written.
+ -d <dirname> : the modified codes are written in the <directory> folder. Files are written with the original name and path from the root of the repository. 
+ default behavior : --branch Exakat/Cobbler/Name.


Analysis and Cobblers
---------------------

The analysis come first, and then the cobbler. The analysis reads the code, assess the situation and report patterns in the code that should be fixed. 
Then, the results from the analysis are given to the Cobbler, as a starting point. The cobbler applies various modifications in the code, and then, produce a new code.
That code is now free of issues that the analysis found. 

One analysis, one cobbler
-------------------------
For example, Performances/PrePostIncrement is the analysis that reports post-increment that should be converted into pre-increments. 
This is the base analysis for the Structure/PostToPre cobbler. This cobbler updates the code and turns ``$a++`` into ``++$a``, and ``$b--`` into ``--$b``.
The resulting code is then stored into a new VCS branch, so that it may be reviewed before PR. 

Cobblers are often created to apply one of the possible fixes related to one analysis. For example, Performances/PrePostIncrement might be fixed by turning the Post increment into a pre-increment, but it may also be replaced by a constant, instead of a literal. 


.. code-block:: php

   <?php
   
   $a++;
   
   // Speed up the code with pre-increment
   // ++$a;
   
   // Make the ++ operation configurable
   // const C = 1;
   // $a = $a + C;
   
   ?>

It is not possible to apply the two cobblers at the same time, since they do not pursue the same goals. One is a performance improvement, the other one make the code configurable. 

One analysis, multiple cobblers
-------------------------------

When one analysis produces results that may be fixed with multiple cobbler, apply the following strategy : 
+ Run the different cobblers, and write the results in different branches
+ Do a PR with each branch, and cherry pick the transformations

Multiple analysis, one cobbler
------------------------------

It is possible to apply the same cobbler to the results of multiple analysis : for example, the Structures/RemoveCode may be applied simultaneously to the analysis `Structures/UselessExpressions` and `Classes/UnusedClasses`. Both analysis spot unused code, that may well be removed. 


Cobbler configuration
---------------------

Cobblers take the following configuration directives : 

+ Source analysis : the analysis which should be resolved by the cobbler. One or more analysis may be provided. Default values are provided, and available in the documentation.
+ Branch name : the branch used in the current VCS, to store the mended code. 
+ Specific configuration : some cobblers accept customs configuration. They are detailled in the documentation of the cobbler.

INI configuration example:
--------------------------

.. code-block:: 

   [Structures/RemoveCode]
   analysis[] = "Structures/UselessExpression"
   analysis[] = "Classes/UnusedClass"
   branch = "code-cleaning"


Cobbler tutorial
----------------

Pre-requisite
-------------
We assume that Exakat has been `install`-ed, and that an exakat project is already inited. 



The way to run a cobbler is to call the `cobble` command. In this example, exakat removes the noscream `@` operator, based on the `Structures/NoScream` analysis, and store the results in the `target-branch` for the `project name`.

::

   > php exakat init -p phulp -R <URL> -git 
   > php exakat cobble -p <project name> -b <target_branch> -P Structures/RemoveNoScream 


