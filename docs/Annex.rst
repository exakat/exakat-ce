.. Annex:

Annex
=====

* Supported Rulesets
* Supported Reports
* Supported PHP Extensions
* Supported Frameworks
* Applications
* Recognized Libraries
* New analyzers
* External services
* PHP Error messages

Supported Rulesets
------------------

Exakat groups analysis by rulesets. This way, analyzing 'Security' runs all possible analysis related to security. One analysis may belong to multiple rulesets.

* All
* Analyze
* Appinfo
* CompatibilityPHP74
* CompatibilityPHP80
* Dump

Supported Reports
-----------------

Exakat produces various reports. Some are general, covering various aspects in a reference way; others focus on one aspect. 

  * Ambassador
  * Ambassadornomenu
  * Drillinstructor
  * Top10
  * Text
  * Xml
  * Uml
  * Yaml
  * Plantuml
  * None
  * Simplehtml
  * Owasp
  * Perfile
  * Beautycanon
  * Phpconfiguration
  * Phpcompilation
  * Favorites
  * Manual
  * Inventories
  * Clustergrammer
  * Filedependencies
  * Filedependencieshtml
  * Classdependencies
  * Stubs
  * StubsJson
  * Radwellcode
  * Grade
  * Weekly
  * Scrutinizer
  * Codesniffer
  * Phpcsfixer
  * Facetedjson
  * Json
  * Onepagejson
  * Marmelab
  * Simpletable
  * Exakatyaml
  * Codeflower
  * Dependencywheel
  * Phpcity
  * Sarb
  * Exakatvendors
  * Topology
  * Migration73
  * Migration74
  * Migration80
  * Meters


Supported PHP Extensions
------------------------

PHP extensions are used to check for structures usage (classes, interfaces, etc.), to identify dependencies and directives. 

PHP extensions are described with the list of structures they define : functions, classes, constants, traits, variables, interfaces, namespaces, and directives. 



Supported Frameworks
--------------------

Frameworks, components and libraries are supported via Exakat extensions.

List of extensions : there are 10 extensions

* :ref:`Cakephp <extension-cakephp>`
* :ref:`Drupal <extension-drupal>`
* :ref:`Laravel <extension-laravel>`
* :ref:`Pmb <extension-pmb>`
* :ref:`Prestashop <extension-prestashop>`
* :ref:`Shopware <extension-shopware>`
* :ref:`Slim <extension-slim>`
* :ref:`Symfony <extension-symfony>`
* :ref:`Wordpress <extension-wordpress>`
* :ref:`ZendF <extension-zendf>`





Applications
------------

A number of applications were scanned in order to find real life examples of patterns. They are listed here : 

* `OpenEMR <https://www.open-emr.org/>`_
* `Thelia <https://thelia.net/>`_


Recognized Libraries
--------------------

Libraries that are popular, large and often included in repositories are identified early in the analysis process, and ignored. This prevents Exakat to analysis some code foreign to the current repository : it prevents false positives from this code, and make the analysis much lighter. The whole process is entirely automatic. 

Those libraries, or even some of the, may be included again in the analysis by commenting the ignored_dir[] line, in the projects/<project>/config.ini file. 

* `ADOdb <https://adodb.org/dokuwiki/doku.php/>`_
* `atoum <http://atoum.org/>`_
* `BBQ <https://github.com/eventio/bbq>`_
* `CakePHP <https://cakephp.org/>`_
* `CI xmlRPC <http://apigen.juzna.cz/doc/ci-bonfire/Bonfire/class-CI_Xmlrpc.html>`_
* `CPDF <https://pear.php.net/reference/PhpDocumentor-latest/li_Cpdf.html>`_
* `Codeception <https://codeception.com/>`_
* `DomPDF <https://github.com/dompdf/dompdf>`_
* `FPDF <http://www.fpdf.org/>`_
* `phpGACL <http://phpgacl.sourceforge.net/>`_
* `gettext Reader <http://pivotx.net/dev/docs/trunk/External/PHP-gettext/gettext_reader.html>`_
* `jpGraph <http://jpgraph.net/>`_
* `HTML2PDF <http://sourceforge.net/projects/phphtml2pdf/>`_
* `HTML Purifier <http://htmlpurifier.org/>`_
* http_class
* `IDNA convert <https://github.com/phpWhois/idna-convert>`_
* `lessc <http://leafo.net/lessphp/>`_
* `magpieRSS <http://magpierss.sourceforge.net/>`_
* `MarkDown Parser <http://processwire.com/apigen/class-Markdown_Parser.html>`_
* `Markdown <https://github.com/michelf/php-markdown>`_
* `mpdf <http://www.mpdf1.com/mpdf/index.php>`_
* oauthToken
* passwordHash
* `pChart <http://www.pchart.net/>`_
* `pclZip <http://www.phpconcept.net/pclzip/>`_
* `Propel <http://propelorm.org/>`_
* `phpExecl <https://phpexcel.codeplex.com/>`_
* `phpMailer <https://github.com/PHPMailer/PHPMailer>`_
* `PHPSpec <http://www.phpspec.net/en/latest/>`_
* `PHPUnit <https://www.phpunit.de/>`_
* `qrCode <http://phpqrcode.sourceforge.net/>`_
* `Services_JSON <https://pear.php.net/package/Services_JSON>`_
* `sfYaml <https://github.com/fabpot-graveyard/yaml/blob/master/lib/sfYaml.php>`_
* `SimplePie <http://simplepie.org/>`_
* `SimpleTest <https://github.com/simpletest/simpletest>`_
* `swift <http://swiftmailer.org/>`_
* `Smarty <http://www.smarty.net/>`_
* `Symfony Unit Test <https://symfony.com/doc/current/testing.html>`_
* `tcpdf <http://www.tcpdf.org/>`_
* `text_diff <https://pear.php.net/package/Text_Diff>`_
* `text highlighter <https://pear.php.net/package/Text_Highlighter/>`_
* `tfpdf <http://www.fpdf.org/en/script/script92.php>`_
* `Typo3TestingFramework <https://github.com/TYPO3/testing-framework>`_
* UTF8
* `Xajax <https://github.com/Xajax/Xajax>`_
* `Yii <http://www.yiiframework.com/>`_
* `Zend Framework <http://framework.zend.com/>`_

New analyzers
-------------

List of analyzers, by version of introduction, newest to oldest. In parenthesis, the first element is the analyzer name, used with 'analyze -P' command, and the seconds, if any, are the ruleset, used with the -T option. Rulesets are separated by commas, as the same analysis may be used in several rulesets.


* 0.8.4

  * Adding Zero (Structures/AddZero)




PHP Error messages
------------------

Exakat helps reduce the amount of error and warning that code is producing by reporting pattern that are likely to emit errors.

0 PHP error message detailled : 

* 




External services
-----------------

List of external services whose configuration files has been commited in the code.

* `Apache <http://www.apache.org/>`_ - .htaccess, htaccess.txt
* `Apple <http://www.apple.com/>`_ - .DS_Store
* `appveyor <http://www.appveyor.com/>`_ - appveyor.yml, .appveyor.yml
* `ant <https://ant.apache.org/>`_ - build.xml
* `apigen <http://apigen.github.io/ApiGen/>`_ - apigen.yml, apigen.neon
* `arcunit <https://www.archunit.org/>`_ - .arcunit
* `artisan <http://laravel.com/docs/5.1/artisan>`_ - artisan
* `atoum <http://atoum.org/>`_ - .bootstrap.atoum.php, .atoum.php, .atoum.bootstrap.php
* `arcanist <https://secure.phabricator.com/book/phabricator/article/arcanist_lint/>`_ - .arclint, .arcconfig
* `bazaar <http://bazaar.canonical.com/en/>`_ - .bzr
* `babeljs <https://babeljs.io/>`_ - .babel.rc, .babel.js, .babelrc
* `behat <http://docs.behat.org/en/v2.5/>`_ - behat.yml.dist, behat.yml
* `box2 <https://github.com/box-project/box2>`_ - box.json, box.json.dist
* `bower <http://bower.io/>`_ - bower.json, .bowerrc
* `circleCI <https://circleci.com/>`_ - circle.yml, .circleci
* `codacy <http://www.codacy.com/>`_ - .codacy.json
* `codeception <https://codeception.com/>`_ - codeception.yml, codeception.dist.yml
* `codecov <https://codecov.io/>`_ - .codecov.yml, codecov.yml
* `codeclimate <http://www.codeclimate.com/>`_ - .codeclimate.yml
* `composer <https://getcomposer.org/>`_ - composer.json, composer.lock, vendor
* `couscous <http://couscous.io/>`_ - couscous.yml
* `Code Sniffer <https://github.com/squizlabs/PHP_CodeSniffer>`_ - .php_cs, .php_cs.dist, .phpcs.xml, php_cs.dist, phpcs.xml, phpcs.xml.dist
* `coveralls <https://coveralls.zendesk.com/>`_ - .coveralls.yml
* `crowdin <https://crowdin.com/>`_ - crowdin.yml
* `cvs <http://savannah.nongnu.org/projects/cvs>`_ - CVS
* `docker <http://www.docker.com/>`_ - .dockerignore, .docker, docker-compose.yml, Dockerfile
* `dotenv <https://symfony.com/doc/current/components/dotenv.htmls>`_ - .env.dist, .env, .env.example
* `drone <http://docs.drone.io/>`_ - .dockerignore, .docker
* `drupalci <https://www.drupal.org/project/drupalci>`_ - drupalci.yml
* `drush <https://www.drupal.org/project/drupalci>`_ - drush.services.yml
* `editorconfig <https://editorconfig.org/>`_ - .editorconfig
* `eslint <http://eslint.org/>`_ - .eslintrc, .eslintignore, eslintrc.js, .eslintrc.js, .eslintrc.json
* `Exakat <https://www.exakat.io/>`_ - .exakat.yaml, .exakat.yml, .exakat.ini
* `flintci <https://flintci.io/>`_ - .flintci.yml
* `git <https://git-scm.com/>`_ - .git, .gitignore, .gitattributes, .gitmodules, .mailmap, .githooks
* `github <https://www.github.com/>`_ - .github
* `gitlab <https://www.gitlab.com/>`_ - .gitlab-ci.yml
* `gulpfile <http://gulpjs.com/>`_ - gulpfile.js
* `grumphp <https://github.com/phpro/grumphp>`_ - grumphp.yml.dist, grumphp.yml
* `gush <https://github.com/gushphp/gush>`_ - .gush.yml
* `gruntjs <https://gruntjs.com/>`_ - Gruntfile.js
* `humbug <https://github.com/humbug/box.git>`_ - humbug.json.dist, humbug.json
* `infection <https://infection.github.io/>`_ - infection.yml, .infection.yml, infection.json.dist
* `insight <https://insight.sensiolabs.com/>`_ - .sensiolabs.yml
* `jetbrains <https://www.jetbrains.com/phpstorm/>`_ - .idea
* `jshint <http://jshint.com/>`_ - .jshintrc, .jshintignore
* `mercurial <https://www.mercurial-scm.org/>`_ - .hg, .hgtags, .hgignore, .hgeol
* `mkdocs <http://www.mkdocs.org>`_ - mkdocs.yml
* `npm <https://www.npmjs.com/>`_ - package.json, .npmignore, .npmrc, package-lock.json
* `openshift <https://www.openshift.com/>`_ - .openshift
* `phan <https://github.com/etsy/phan>`_ - .phan
* `pharcc <https://github.com/cbednarski/pharcc>`_ - .pharcc.yml
* `phalcon <https://phalconphp.com/>`_ - .phalcon
* `phpbench <https://github.com/phpbench/phpbench>`_ - phpbench.json
* `phpci <https://www.phptesting.org/>`_ - phpci.yml
* `Phpdocumentor <https://www.phpdoc.org/>`_ - .phpdoc.xml, phpdoc.dist.xml
* `phpdox <https://github.com/theseer/phpdox>`_ - phpdox.xml.dist, phpdox.xml
* `phinx <https://phinx.org/>`_ - phinx.yml
* `phpformatter <https://github.com/mmoreram/php-formatter>`_ - .formatter.yml
* `phpmetrics <http://www.phpmetrics.org/>`_ - .phpmetrics.yml.dist
* `phpsa <https://github.com/ovr/phpsa>`_ - .phpsa.yml
* `phpspec <http://www.phpspec.net/en/latest/>`_ - phpspec.yml, .phpspec, phpspec.yml.dist
* `phpstan <https://github.com/phpstan>`_ - phpstan.neon, .phpstan.neon, phpstan.neon.dist
* `phpswitch <https://github.com/jubianchi/phpswitch>`_ - .phpswitch.yml
* `PHPUnit <https://www.phpunit.de/>`_ - phpunit.xml.dist, phpunit.xml
* `prettier <https://prettier.io/>`_ - .prettierrc, .prettierignore
* `psalm <https://getpsalm.org/>`_ - psalm.xml
* `puppet <https://puppet.com/>`_ - .puppet
* `rmt <https://github.com/liip/RMT>`_ - .rmt.yml
* `robo <https://robo.li/>`_ - RoboFile.php
* `scrutinizer <https://scrutinizer-ci.com/>`_ - .scrutinizer.yml
* `semantic versioning <http://semver.org/>`_ - .semver
* `SPIP <https://www.spip.net/>`_ - paquet.xml
* `stickler <https://stickler-ci.com/docs>`_ - .stickler.yml
* `storyplayer <https://datasift.github.io/storyplayer/>`_ - storyplayer.json.dist
* `styleci <https://styleci.io/>`_ - .styleci.yml
* `stylelint <https://stylelint.io/>`_ - .stylelintrc
* `sublimelinter <http://www.sublimelinter.com/en/latest/>`_ - .csslintrc
* `svn <https://subversion.apache.org/>`_ - svn.revision, .svn, .svnignore
* `transifex <https://www.transifex.com/>`_ - .tx
* `Robots.txt <http://www.robotstxt.org/>`_ - robots.txt
* `travis <https://travis-ci.org/>`_ - .travis.yml, .env.travis, .travis, .travis.php.ini, .travis.coverage.sh, .travis.ini
* `varci <https://var.ci/>`_ - .varci, .varci.yml
* `Vagrant <https://www.vagrantup.com/>`_ - Vagrantfile
* `visualstudio <https://code.visualstudio.com/>`_ - .vscode
* `webpack <https://webpack.js.org/>`_ - webpack.mix.js, webpack.config.js
* `yarn <https://yarnpkg.com/lang/en/>`_ - yarn.lock
* `Zend_Tool <https://framework.zend.com/>`_ - zfproject.xml

External links
--------------

List of external links mentionned in this documentation.

* `.exakat.ini` or `.exakat.yaml` file. See `Add Exakat To Your CI Pipeline <https://www.exakat.io/add-exakat-to-your-ci-pipeline/>`_
* `.phar` from the exakat.io website : `www.exakat.io <http://www.exakat.io/versions/>`_
* `7z <https://www.7-zip.org/7z.html>`_
* `Bazaar <https://bazaar.canonical.com/en/>`_
* `cat: write error: Broken pipe <https://askubuntu.com/questions/421663/cat-write-error-broken-pipe>`_
* `composer <https://getcomposer.org/>`_
* `curl <http://www.php.net/curl>`_
* `CVS <https://www.nongnu.org/cvs/>`_
* `dist.exakat.io <http://dist.exakat.io/>`_
* `Docker <http://www.docker.com/>`_
* `Docker image <https://hub.docker.com/r/exakat/exakat/>`_
* `dotdeb instruction <https://www.dotdeb.org/instructions/>`_
* `download <https://www.exakat.io/download-exakat/>`_
* `Exakat <http://www.exakat.io/>`_
* `Exakat cloud <https://www.exakat.io/exakat-cloud/>`_
* `Exakat SAS <https://www.exakat.io/get-php-expertise/>`_
* `exakat/exakat <https://hub.docker.com/r/exakat/exakat/>`_
* `Git <https://git-scm.com/>`_
* `Github Action <https://docs.github.com/en/actions>`_
* `Github.com/exakat/exakat <https://github.com/exakat/exakat>`_
* `Gremlin server <http://tinkerpop.apache.org/>`_
* `hash <http://www.php.net/hash>`_
* `https://hub.docker.com/r/exakat/exakat-ga <https://hub.docker.com/r/exakat/exakat-ga>`_
* `https://www.exakat.io/ <https://www.exakat.io/>`_
* `https://www.exakat.io/versionss/index.php?file=latest <https://www.exakat.io/versions/index.php?file=latest>`_
* `mercurial <https://www.mercurial-scm.org/>`_
* `PHP <https://www.php.net/>`_
* `PHPUnit <https://www.phpunit.de/>`_
* `plantuml <http://plantuml.com/>`_
* `rar <https://en.wikipedia.org/wiki/RAR_(file_format)>`_
* `sqlite3 <http://www.php.net/sqlite3>`_
* `Static Analysis Results Interchange Format (SARIF) <https://docs.oasis-open.org/sarif/sarif/v2.0/sarif-v2.0.html>`_
* `Svn <https://subversion.apache.org/>`_
* `tetraweb/php <https://hub.docker.com/r/tetraweb/php/>`_
* `The main PPA for PHP (7.4, 7.3, 7.2, 7.1, 7.0, 5.6)  <https://launchpad.net/~ondrej/+archive/ubuntu/php>`_
* `tokenizer <http://www.php.net/tokenizer>`_
* `Vagrant file <https://github.com/exakat/exakat-vagrant>`_
* `zip <https://en.wikipedia.org/wiki/Zip_(file_format)>`_


Ruleset configurations
----------------------

INI configuration for built-in rulesets. Copy them in config/themes.ini, and make your owns.

1 rulesets detailled here : 

* `Analyze <ruleset_ini_analyze>`_



.. _ruleset_ini_analyze:

Analyze
This ruleset centralizes a large number of classic trap and pitfalls when writing PHP.
_______

| [Analyze]
|   analyzer[] = "Structures/AddZero";| 





