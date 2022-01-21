.. _administrator-Configuration:

Configuration
=============


Common Behavior
---------------

General Philosophy
##################
Exakat tries to avoid configuration as much as possible, so as to focus on working out of the box, rather than spend time on pre-requisite.

As such, it probably does more work, but that may be dismissed later, at reading time.

More configuration options appear with the evolution of the engine.

Reminder of precedences
#######################

The exakat engine read directives from six places, with the following precedence :

#. The command line options
#. The .exakat.ini or .exakat.yaml file at the root of the code
#. The environment variables
#. The config.ini file in the project directory
#. The exakat.ini file in the config directory
#. The default values in the code

The precedence of the directives is the same as the list above.

Some of the directives are only available in some specific configuration locations : they may not have usefulness in every places. See :ref:`user-option-availability`.

Common Options
###############
 
All options are the same, whatever the command provided to exakat. -f always means files, and -q always means quick. 

Any option that a command doesn't understand is ignored. 

Any option that is not recognized is ignored and reported (with visibility).

Engine configuration
--------------------

Engine configuration is were the exakat engine general configuration are stored. For example, the php binaries or the neo4j folder are there. Engine configurations affect all projects.

Configuration File
##################

The Exakat engine is configured in the 'config/exakat.ini' file. 

This file is created with the 'doctor' command, or simply by copying another such file from another installation.

::

   php exakat.phar doctor

When the doctor can't find the 'config/config.ini' file, it attempts to create one, with reasonable values. It is recommended to use this to create the exakat.ini skeleton, and later, modify it.

Available Options
#################

Here are the currently available options in Exakat's configuration file : config/config.ini

graphdb
+++++++++++++++

The graph database to use.                                                               
Currently, it may be gsneo4jv3, or tinkergraphv3.                                            

gsneo4j_host
+++++++++++++++

The host to connect to reach the graph database, when using gsneo4j driver.              
The default value is 'localhost'                                                         

gsneo4j_host
+++++++++++++++

The port to use on the host to reach the graph database, when using gsneo4j driver..     
The default value is '8182'                                                              

gsneo4j_folder
+++++++++++++++

The folder where the code for the graph database resides, when using gsneo4j driver.   
The default value is 'tinkergraph', and is located near exakat.phar                    

tinkergraph_host
++++++++++++++++++++

The host to connect to reach the graph database, when using tinkergraph driver.          
The default value is 'localhost'                                                         

tinkergraph_port
++++++++++++++++++++

The port to use on the host to reach the graph database, when using tinkergraph driver. 
The default value is '8182'                                                             

tinkergraph_folder
++++++++++++++++++++

The folder where the code for the graph database resides, when using tinkergraph driver.
The default value is 'tinkergraph', and is located near exakat.phar                     

gsneo4jv3_host
+++++++++++++++

The host to connect to reach the graph database, when using gsneo4jv3 driver.              
The default value is 'localhost'                                                         

gsneo4jv3_port
+++++++++++++++

The host to connect to reach the graph database, when using gsneo4jv3 driver.              
The default value is '8182'                                                         

gsneo4jv3_folder
+++++++++++++++++

The folder where the code for the graph database resides, when using gsneo4jv3 driver.  
The default value is 'tinkergraph', and is located near exakat.phar                   

tinkergraphv3_host
+++++++++++++++++++

The host to connect to reach the graph database, when using tinkergraphv3 driver.              
The default value is 'localhost'                                                         

tinkergraphv3_port
++++++++++++++++++

The host to connect to reach the graph database, when using tinkergraphv3 driver.              
The default value is '8182'                                                         

tinkergraphv3_folder
++++++++++++++++++++

The folder where the code for the graph database resides, when using tinkergraphv3 driver.  
The default value is 'tinkergraph', and is located near exakat.phar                   

project_rulesets
++++++++++++++++++++

List of rulesets to be run. The list may include extra rulesets that are not requested by the individual reports. That way, they will be available for the generic reports.

project_rulesets[] = 'Security'

project_reports
++++++++++++++++++++

The list of reports that produced when running 'project' command, without the -format option. This list may automatically add extra rulesets if a report requires them. For example, the 'Ambassador' report requires 'Security' ruleset, while 'Text' has no pre-requisite. 

project_reports contains 'Diplomat', by default.                                            

token_limit
++++++++++++++++++++

Maximum size of the analyzed project, in number of PHP tokens (excluding whitespace). Use this to avoid running a really long analyze without knowing it.

Default is 1 million.                                                                   

php
++++++++++++++++++++

Link to the PHP binary. This binary is the one that runs Exakat. It is recommended to use PHP 8.0, or 8.1. The same binary may be used with the following options.                 

php82
+++++

Path to the PHP 8.2.x binary. This binary is needed to test the compilation with the 8.2 series or if the analyze should be run with this version (see project's config.ini). 

Comment it out if you don't want this version tested. It is not recommended to use this  version for the analyze                                                                  

php81
+++++

Path to the PHP 8.1.x binary. This binary is needed to test the compilation with the 8.1 series or if the analyze should be run with this version (see project's config.ini). 

Comment it out if you don't want this version tested. It is not recommended to use this  version for the analyze                                                                  

php80
+++++

Path to the PHP 8.0.x binary. This binary is needed to test the compilation with the 8.0 series or if the analyze should be run with this version (see project's config.ini). 

Comment it out if you don't want this version tested. It is not recommended to use this  version for the analyze                                                                  

php74
+++++

Path to the PHP 7.4.x binary. This binary is needed to test the compilation with the 7.4 series or if the analyze should be run with this version (see project's config.ini). 

Comment it out if you don't want this version tested. It is not recommended to use this  version for the analyze                                                                  

php73
+++++

Path to the PHP 7.3.x binary. This binary is needed to test the compilation with the 7.3 series or if the analyze should be run with this version (see project's config.ini). 

Comment it out if you don't want this version tested. It is not recommended to use this  version for the analyze                                                                  

php72
+++++

Path to the PHP 7.2.x binary. This binary is needed to test the compilation with the 7.2 series or if the analyze should be run with this version (see project's config.ini). 

Comment it out if you don't want this version tested. It is not recommended to use this  version for the analyze                                                                  

php71
+++++

Path to the PHP 7.1.x binary. This binary is needed to test the compilation with the 7.1 series or if the analyze should be run with this version (see project's config.ini). 

Comment it out if you don't want this version tested. It is not recommended to use this  version for the analyze                                                                  

php70
+++++

Path to the PHP 7.0.x binary. This binary is needed to test the compilation with the 7.0 series or if the analyze should be run with this version (see project's config.ini). 

Comment it out if you don't want this version tested. It is not recommended to use this  version for the analyze                                                                  

php56
+++++

Path to the PHP 5.6.x binary. This binary is needed to test the compilation with the 5.6 series or if the analyze should be run with this version (see project's config.ini). 

Comment it out if you don't want this version tested. It is not recommended to use this  version for the analyze                                                                  

php55
+++++

Path to the PHP 5.5.x binary. This binary is needed to test the compilation with the 5.5 series or if the analyze should be run with this version (see project's config.ini). 

Comment it out if you don't want this version tested. It is not recommended to use this  version for the analyze                                                                  

php54
+++++

Path to the PHP 5.4.x binary. This binary is needed to test the compilation with the 5.4 series or if the analyze should be run with this version (see project's config.ini). 

Comment it out if you don't want this version tested. It is not recommended to use this  version for the analyze                                                                  

php53
+++++

Path to the PHP 5.3.x binary. This binary is needed to test the compilation with the 5.3 series or if the analyze should be run with this version (see project's config.ini). 

Comment it out if you don't want this version tested. It is not recommended to use this  version for the analyze                                                                  

php52
+++++

Path to the PHP 5.2.x binary. This binary is needed to test the compilation with the 5.2 series or if the analyze should be run with this version (see project's config.ini). 

Comment it out if you don't want this version tested. It is not recommended to use this  version for the analyze                                                                  

php_extensions
++++++++++++++

List of PHP extensions to use when spotting functions, methods, constants, classes, etc. 

Default to 'all', which are all in the source code. Can be set to 'none' to skip the detection                                               


Note : php** configuration may be either a valid PHP binary path, or a valid Docker image. The path on the system may be `/usr/bin/php`, `/usr/sbin/php80`, or `/usr/local/Cellar/php71/7.1.30/bin/php`. The Docker configuration must have the form `abc/def:tag`. The image's name may be any value, as long as Exakat manage to run it, and get the valid PHP signature, with `php -v`. When using Docker, the docker server must be running. 

Custom rulesets
###############

Create custom rulesets by creating a 'config/rulesets.ini' directive files. 

This file is a .INI file, build with multiple sections. Each section is the name of a ruleset : for example, 'mine' is the name for the ruleset below. 

There may be several sections, as long as the names are distinct. 

It is recommended to use all low-case names for custom rulesets. Exakat uses names with a first capital letter, which prevents conflicts. Behavior is undefined if a custom ruleset has the same name as a default ruleset.

:: 

    ['mine']
    analyzer[] = 'Structures/AddZero';
    analyzer[] = 'Performances/ArrayMergeInLoops';


The list of analyzer in the ruleset is based on the 'analyzer' array. The analyzer is identified by its 'shortname'. Analyzer shortname may be found in the documentation (:ref:`Rules` or within the Ambassador report). Analyzers names have a 'A/B' structure.

The list of available rulesets, including the custom ones, is listed with the `doctor` command.
    

Check Install
-------------

Once the prerequisite are installed, it is advised to run to check if all is found : 

`php exakat.phar doctor`

After this run, you may edit 'config/config.ini' to change some of the default values. Most of the time, the default values will be OK for a quick start.
