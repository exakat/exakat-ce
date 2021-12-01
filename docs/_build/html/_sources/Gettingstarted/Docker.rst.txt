.. _Docker:

with a Docker installation
**************************

Here are 2 tutorials to run Exakat on your code. You may install exakat with the projects folder, and centralize your audits in one place, or run exakat in-code, right from the source code. You may also run exakat with a bare-metal installation, or as a docker container.


+ Docker container

 + with projects folder

 + within the code

All four tutorials offer the same steps : 
+ Project initialisation
+ Audit run
+ Reports access



Docker container, with projects folder
----------------------------------------

This tutorial runs exakat audits, when source code are organized in the `projects` folder. Any folder will do, since exakat is now hosted in the docker image.

Initialization
______________

Go to the directory that contains the 'projects' folder. 

Init the project with the following command : 

::

  docker run -it --rm -v /Users/famille/Desktop/analyzeG3/projects:/usr/src/exakat/projects exakat/exakat:latest exakat init -p sculpin -R https://github.com/sculpin/sculpin -git

This will create a 'projects/sculpin' folder, with various documents and folder. The most important folder being 'code', where the code of the project is fetched, an cached. See _Commands for more details about the `init` command.

Execution
_________

After creating the project, an audit may be run from the same directory: 

:: 

    docker run -it --rm -v /Users/famille/Desktop/analyzeG3/projects:/usr/src/exakat/projects exakat/exakat:dev exakat project -p sculpin 

This command runs the whole cycle : code loading, code audits and report building. 

Once it is finished, the report is available in the `projects/sculpin/report/` folder. Open `projects/sculpin/report/index.htmll` with a browser.

More reports
____________

When running exakat with the projects folder, reports may be configured before the run of the audit, in the config.ini file, or in command line, or extracted after the run.

After a first audit, use the `report` command. Here is an example with the `Uml` report. 

:: 

    docker run -it --rm -v /Users/famille/Desktop/analyzeG3/projects:/usr/src/exakat/projects exakat/exakat:dev exakat report -p sculpin -format Uml 
    
Reports may only be build if the analysis they depend on, were already processed.

In command line, use the `-format` option, multiple times if necessary.

:: 

    docker run -it --rm -v /Users/famille/Desktop/analyzeG3/projects:/usr/src/exakat/projects exakat/exakat:dev exakat project -p sculpin -format Uml 

In config.ini, edit the `projects/sculpin/report/config.ini` file, and add the following lines : 

:: 

    project_reports[] = 'Uml';
    project_reports[] = 'Plantuml';
    project_reports[] = 'Ambassador';


Then, run the audit as explained in the previous section. 

The full list of available reports are in the _Reports section.

New run
_______

After adding some modifications to the code and committing them, you need to update the code before running it again : otherwise, it will run on the previous version of the code. 

:: 

    docker run -it --rm -v /Users/famille/Desktop/analyzeG3/projects:/usr/src/exakat/projects exakat/exakat:dev exakat update -p sculpin 
    docker run -it --rm -v /Users/famille/Desktop/analyzeG3/projects:/usr/src/exakat/projects exakat/exakat:dev exakat project -p sculpin


Docker container, within the code folder
-----------------------------------------

This tutorial runs exakat audits from the source code repository, with a docker container.

Installation
____________

Refer to the _Installation section to install Exakat on docker.


Initialization
______________

Go to the directory that contains the source code.

Create a configuration file called `.exakat.yml`, with the following content : 

:: 

    project: "name"

This is the minimum configuration for that file. You may read more about _Configuration in the dedicated section.

Execution
_________

After creating the configuration file, an audit may be run from the same directory: 

:: 

    docker run -it --rm -v $(`pwd`):/src exakat/exakat:latest exakat project

This command runs the whole cycle : code loading, code audits and report building. It works without initial configuration. 

Once it is finished, the report is displayed on the standard output (aka, the screen).

More reports
____________

When running exakat inside code, reports must be configured before the run of the audit : they will be build immediately. 

Edit the .exakat.yml file, and add the following lines : 

:: 

    project: "name"
    project_reports: 
      - Uml
      - Plantuml
      - Ambassador


Then, run the audit as explained in the previous section. 

This configuration produces 3 reports : "Ambassador", which is the default report, "Uml", available in the 'uml.dot' file, and "Plantuml", that may be opened with `plantuml <http://plantuml.com/>`_.

The full list of available reports are in the _Reports section.

New run
_______

After adding some modifications to the code, run again exakat with the same command than the first time. Since the audit is run within the code source, no explicit update operation is needed.

Check the `.exakat.yml` file before running the audit, to check if all the reports you want are configured.

:: 

    docker run -it --rm -w /src -v $(pwd):/src --entrypoint "/usr/src/exakat/exakat.phar" exakat/exakat:latest project

