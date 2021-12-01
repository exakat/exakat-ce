.. _user-Configuration:

Configuration
===============


Summary
-------

* `Common Behavior`_
* `Project Configuration`_
* `In-code Configuration`_
* `Commandline Configuration`_
* `Specific analysis configurations`_

Common Behavior
---------------

General Philosophy
##################
Exakat tries to avoid configuration as much as possible, so as to focus on working out of the box, rather than spend time on pre-requisite.

As such, it probably does more work, but that may be dismissed later, at reading time.

More configuration options appear with the evolution of the engine.

Precedence
##########

The exakat engine read directives from five places, with the following precedence :

1. The command line options
2. The .exakat.ini or .exakat.yaml file at the root of the code
3. The config.ini file in the project directory
4. The exakat.ini file in the config directory
5. The default values in the code


The precedence of the directives is the same as the list above : command line options always have highest priority, config.ini files are in second, when command line are not available, and finally, the default values are read in the code.

Some of the directives are only available in the config.ini files, or at the engine level.

Common Options
###############
 
All options are the same, whatever the command provided to exakat. -f always means files, and -q always means quick. 

Any option that a command doesn't understand is ignored. 

Any option that is not recognized is ignored and reported (with visibility).


Project Configuration
---------------------

Project configuration are were the project specific configuration are stored. For example, the project name, the ignored directories or its external libraries are kept. Configurations only affect one project and not the others.

Project configuration file are called 'config.ini'. They are located, one per project, in the 'projects/&lt;project name&gt;/config.ini' file. 

Available Options
#################

Here are the currently available options in Exakat's project configuration file : projects/&lt;project name&gt;/config.ini

phpversion
++++++++++

PHP Version with which to run the code analysis. 

It may be one of : 8.2, 8.1, 8.0, 7.4, 7.3, 7.2, 7.1, 7.0, 5.6, 5.5, 5.4, 5.3, 5.2.                          
Default is 8.0 or the CLI version used to init the project.                              
8.2 is currently the development version. 5.* versions are available, but are less tested.      
phpversion it is a string.                                   

include_dirs
++++++++++++

This is the list of files and dir to include in the project's directory. It is chrooted in the project's folder. Values provided with a starting / are used as a path prefix.  

Values without / are used as a substring, anywhere in the path.
include_dirs are added AFTER ignore_dirs, so as to partially ignore a folder, such as the vendor folder from composer.
include_dirs is an array of string.                                                       

ignore_dirs
++++++++++++

This is the list of files and dir to ignore in the project's directory. It is chrooted in the project's folder. Values provided with a starting / are used as a path prefix. Values without / are used as a substring, anywhere in the path.

ignore_dirs is an array of string.                                                       

file_extensions
++++++++++++++++++++++++

This is the list of file extensions that is considered as PHP scripts. All others are ignored. All files bearing those extensions are subject to check, though they are scanned first for PHP tags before being analyzed. The extensions are comma separated, without dot.                                                                             

The default are : php, php3, inc, tpl, phtml, tmpl, phps, ctp                            
file_extensions may be a comma-separated list of values as a string, or an array.

project_name
++++++++++++++++++++++++

This is the project name, as it appears at the top left in the Ambassador report.

project_url
++++++++++++++++++++++++

This is the repository URL for the project. It is used to get the source for the project.

project_vcs
++++++++++++++++++++++++

This is the VCS used to fetch the project source.

project_description
++++++++++++++++++++++++

This is the description of the project.

project_packagist
++++++++++++++++++++++++

This is the packagist name for the code, when the code is fetched with composer. 


In-code Configuration
---------------------

In-code configuration is a configuration file that sits at the root of the code. When exakat finds it, it uses it for in-code auditing.

The file is `.exakat.yaml`, and is a valid YAML file. `.exakat.yml` is also valid, but not recommended.

In case the file is found but not valid, Exakat reverts to default values. 

Unrecognized values are ignored. 

Exakat in-code example
######################
:: 

    project: exakat
    project_name: exakat
    project_rulesets: 
    - my_ruleset
    - Security
    project_report: 
    - Ambassador
    file_extensions: php,php3,phtml
    include_dirs: 
      - /
    ignore_dirs: 
      - /tests
      - /vendor
      - /docs
      - /media
    ignore_rules:
      - Structures/AddZero
    rulesets: 
      my_ruleset: 
          - Structures/AddZero
          - Structures/MultiplyByOne


Exakat in-code skeleton
#######################

Copy-paste this YAML code into a file called `.exakat.yaml`, located at the root of your repository.

:: 

    file_extensions: php,php3,phtml
    project: <project short name>
    project_name: <project name, as displayed in reports>
    project_rulesets: 
    - <list of rulesets to apply>
    - Analysis
    file_extensions: php,php3,phtml
    project_report: 
    - <list of reports to build>
    - Ambassador
    include_dirs: 
      - /
    ignore_rules:
      - 
    exclude_rules:
      - 
    ignore_dirs: 
      - /tests
      - /vendor
      - /docs
      - /media


Available Options
#################

Here are the currently available options in Exakat's project configuration file : projects/&lt;project name&gt;/config.ini

+-----------------------+-------------------------------------------------------------------------------------------+
| Option                | Description                                                                               |
+=======================+===========================================================================================+
| include_dirs[]        | This is the list of files and dir to include in the project's directory. It is chrooted   |
|                       | in the project's folder. Values provided with a starting / are used as a path prefix.     |
|                       | Values without / are used as a substring, anywhere in the path.                           |
|                       | include_dirs are added AFTER ignore_dirs, so as to partially ignore a folder, such as     |
|                       | the vendor folder from composer.                                                          |
+-----------------------+-------------------------------------------------------------------------------------------+
| ignore_dirs[]         | This is the list of files and dir to ignore in the project's directory. It is chrooted in |
|                       | the project's folder. Values provided with a starting / are used as a path prefix. Values |
|                       | without / are used as a substring, anywhere in the path.                                  |
+-----------------------+-------------------------------------------------------------------------------------------+
| ignore_rules[]        | The rules mentioned in this list are ignored when running the audit. Rules are ignored    |
|                       | after loading the rulesets configuration : as such, it is possible to ignore rules inside |
|                       | a ruleset, without ignoring the whole ruleset.                                            |
|                       | The rules in this list are Exakat's short name : ignore_rules[] = "Structures/AddZero"    |
+-----------------------+-------------------------------------------------------------------------------------------+
| include_rules[]       | There is no include_rules directive. Create a custom Ruleset, and include it with         |
|                       | project_rulesets (see below)                                                              |
+-----------------------+-------------------------------------------------------------------------------------------+
| file_extensions       | This is the list of file extensions that is considered as PHP scripts. All others are     |
|                       | ignored. All files bearing those extensions are subject to check, though they are         |
|                       | scanned first for PHP tags before being analyzed. The extensions are comma separated,     |
|                       | without dot.                                                                              |
|                       | The default are : php, php3, inc, tpl, phtml, tmpl, phps, ctp                             |
+-----------------------+-------------------------------------------------------------------------------------------+
| project_name          | This is the project name, as it appears at the top left in the Ambassador report.         |
+-----------------------+-------------------------------------------------------------------------------------------+
| project_url           | This is the repository URL for the project. It is used to get the source for the project. |
+-----------------------+-------------------------------------------------------------------------------------------+
| project_vcs           | This is the VCS used to fetch the project source.                                         |
+-----------------------+-------------------------------------------------------------------------------------------+
| project_description   | This is the description of the project.                                                   |
|                       | This is free text, used in reports. The default is : '' (empty string)                    |
+-----------------------+-------------------------------------------------------------------------------------------+
| project_packagist     | This is the packagist name for the code, when the code is fetched with composer.          |
+-----------------------+-------------------------------------------------------------------------------------------+
| project_rulesets      | This is the list of default rules to run for this project.                                |
|                       | The default are : CompatibilityPHP70, CompatibilityPHP71, CompatibilityPHP72,             |
|                       | CompatibilityPHP73, CompatibilityPHP74, CompatibilityPHP80, Suggestions, Dead code,       | 
|                       | Security, Analyze, Top10, Preferences, Appinfo, Appcontent, Suggestions                   |
|                       | This an array of strings, which are all ruleset names                                     |
+-----------------------+-------------------------------------------------------------------------------------------+
| project_reports       | This is the list of default reports to run for this project.                              |
|                       | The default are : Diplomat                                                                |
|                       | This an array of strings, which are all reports names                                     |
+-----------------------+-------------------------------------------------------------------------------------------+

Commandline Configuration
-------------------------

Commandline configurations are detailled with each command, in the _Commands section.


Specific analysis configurations
--------------------------------

Some analyzer may be configured individually. Those parameters are then specific to one analyzer, and it only affects their behavior. 

Analyzers may be configured in the `project/*/config.ini`; they may also be configured globally in the `config/exakat.ini` file.

:ref:`Immutable Signature <immutable-signature>`
  + maxOverwrite : 8

    + Minimal number of method overwrite to consider that any refactor on the method signature is now hard.
:ref:`Should Use Prepared Statement <should-use-prepared-statement>`
  + queryMethod : query_methods.json

    + Methods that call a query.
:ref:`Too Complex Expression <too-complex-expression>`
  + complexExpressionThreshold : 30

    + Minimal number of operators in one expression to report.
:ref:`@ Operator <@-operator>`
  + authorizedFunctions : noscream_functions.json

    + Functions that are authorized to sports a @.
:ref:`Variables With Long Names <variables-with-long-names>`
  + variableLength : 20

    + Minimum size of a long variable name, including the initial $.


    

Check Install
-------------

Once the prerequisite are installed, it is advised to run to check if all is found : 

`php exakat.phar doctor`

After this run, you may edit 'config/config.ini' to change some of the default values. Most of the time, the default values will be OK for a quick start.
