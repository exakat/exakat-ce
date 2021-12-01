.. _Bare:

with a Bare metal installation
********************************

Here are 2 tutorials to run Exakat on your code. You may install exakat with the `projects` folder, and centralize multiple audits in one place, or run exakat in-code, right from the source code. You may also run exakat on a host machine (aka, bare-metal), or as a docker container.

+ Bare metal install

 + with projects folder

 + within the code


All tutorials follow the same steps : 

+ Project initialisation
+ Audit run
+ Reports access


Bare metal install, with projects folder
----------------------------------------

Installation
____________

Refer to the :ref:`Installation<Installation>` section in the ADMINISTRATOR GUIDE to install Exakat.

Initialization
______________

First, fetch the code to be audited. This has to be done once. Later, the code may be updated.

::

    php exakat.phar init -p sculpin -R https://github.com/sculpin/sculpin

This command inits the project in the 'projects' folder, with the name 'sculpin', then clone the code with the provided repository. By default, the cloning is done by git. 

Exakat requires a copy of the code to run an audit. When accessing via VCS, such as git, mercurial, svn, etc., read-only access is sufficient and recommended. Exakat doesn't write anything in the code, nor stage, commit or push.

More information on options in the _Commands.

Execution
_________

After initialization, you may run an audit : 

:: 

    php exakat.phar project -p sculpin

This command runs the whole auditing cycle : code loading, code audits and report building. It is ready to work with the initial configuration. The configuration may be adapted later.

Once the run is finished, the reports are place in the folder `projects/sculpin/`. For example, a HTML version is available in `projects/sculpin/report/index.html`. Simply open the 'projects/sculpin/report/index.html' file in a browser.

More reports
____________

Once the 'project' command has been fully run, you may run the 'report' command to create different reports. Usually, 'Diplomat' has the most complete report, and other focused reports are available. 

It is possible to create the remaining reports, once an audit has been finished. Here is an example of a Uml report.

:: 

    php exakat.phar report -p sculpin -format Uml -file uml

This export the current project in UML format. The file is called 'uml.dot' : dot is added by exakat, as the report has to be opened by `graphviz <http://www.graphviz.org/>`_ compatible software.

The full list of available reports are in the :ref:`reports` section.

Once it is finished, the reports are in the folder `projects/sculpin/` under different names.

New run
_______

After adding some modifications in the code, commit them in the repository. Then, run : 

:: 

    php exakat.phar update  -p sculpin
    php exakat.phar project -p sculpin

This command updates the repository to the last modification, then runs the whole audit again. If the code is not using a VCS repository, then the update command has no effect on the code. You should update the code manually, by replacing it with a newer version. 

Once the audit is finished, the reports are in the same folders as previously : `projects/sculpin/report` (HTML version). 

The reports replace any previous report. To keep a report of a previous version, move it away from the current location, or give it another name.

Bare metal install, within the code
-----------------------------------

This tutorial runs exakat from the source code repository.

Installation
____________

Refer to the :ref:`Installation<Installation>` section in the ADMINISTRATOR GUIDE to install Exakat.


Initialization
______________

Go to the directory that contains the source code.

Create a configuration file called `.exakat.yml`, with the following content : 

:: 

    project: "name"

This is the minimum configuration for that file. It is sufficient for this tutorial, and we will produce more reports later. You will read more about _Configuration in the dedicated section. 

Execution
_________

After creating the configuration file above, an audit may be run : 

:: 

    exakat project 

This command runs the whole cycle : code loading, code audits and report building. It works without initial configuration. 

Once it is finished, the reports are in the current folder. Simply open the 'report/index.html' file in a browser.

More reports
____________

When running exakat inside code, audits must be configured before the run of the audit. 

Edit the .exakat.yml file, and update the file with the following lines : 

:: 

    project: "name"
    project_reports: 
      - Uml
      - Plantuml
      - Ambassador

Then, run the audit as explained in the previous section. 

This configuration produces 3 reports : "Ambassador", which is the default report, "Uml", available in the 'uml.dot' file, and "Plantuml", that may be opened with `plantuml <http://plantuml.com/>`_.

The full list of available reports are in the 'Command' section.

New run
_______

After some modifications in the code, run again exakat with the same command than the first time. Since the audit is run within the code source, no update operation is needed.

Check the `config.ini` file before running the audit, to check if all the reports you want are configured.

:: 

    exakat project 

