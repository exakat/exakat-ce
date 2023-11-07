<?php declare(strict_types = 1);
/*
 * Copyright 2012-2022 Damien Seguy – Exakat SAS <contact(at)exakat.io>
 * This file is part of Exakat.
 *
 * Exakat is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Exakat is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with Exakat.  If not, see <http://www.gnu.org/licenses/>.
 *
 * The latest code can be found at <http://exakat.io/>.
 *
*/

namespace Exakat\Reports\Data;

use Exakat\Analyzer\Analyzer;
use Exakat\Reports\Reports;

class Appinfo extends Data {
    private array $extensions = array(
                    'PHP' => array(
                            'Short tags'                    => 'Structures/ShortTags',
                            'Echo tags <?='                 => 'Php/EchoTagUsage',
                            'Incompilable'                  => 'Php/Incompilable',

                            '@ operator'                    => 'Structures/Noscream',
                            'Alternative syntax'            => 'Php/AlternativeSyntax',
                            'Magic constants'               => 'Constants/MagicConstantUsage',
                            'halt_compiler()'               => 'Php/Haltcompiler',

                            'Casting'                       => 'Php/CastingUsage',
                            'Resources'                     => 'Structures/ResourcesUsage',
                            'Nested Loops'                  => 'Structures/NestedLoops',
                            'arrays_ With Callback'         => 'Arrays/WithCallback',

                            'Autoload'                      => 'Php/AutoloadUsage',
                            'include'                       => 'Structures/IncludeUsage',
                            'include_once'                  => 'Structures/OnceUsage',
                            'Output control'                => 'Extensions/Extob',

                            'Goto'                          => 'Php/Gotonames',
                            'Labels'                        => 'Php/Labelnames',
                            'Match'                         => 'Php/UseMatch',

                            'Short Ternary'                 => 'Php/ShortTenary',
                            'Coalesce'                      => 'Php/Coalesce',
                            'Coalesce Equal'                => 'Php/CoalesceEqual',
                            'Trailing Comma'                => 'Php/TrailingComma',
                            'Trailing Comma In Use'         => 'Php/UseTrailingUseComma',
                            'PHP 8.0 Variable Syntax'       => 'Php/Php80VariableSyntax',

                            'Attributes'                    => 'Php/UseAttributes',
                            'Nested Attributes'             => 'Attributes/NestedAttributes',

                            'File upload'                   => 'Structures/FileUploadUsage',
                            'Environment Variables'         => 'Php/UsesEnv',

                            'Dynamically load extensions'   => 'Php/DlUsage',
                            'Relaxed keyword as names'      => 'Php/Php7RelaxedKeyword',
                            'strict_types'                  => 'Php/DeclareStrictType',
                            'encoding'                      => 'Php/DeclareEncoding',
                            'ticks'                         => 'Php/DeclareTicks',
                    ),

                    'Composer' => array(
                            'composer.json'                 => 'Composer/UseComposer',
                            'composer.lock'                 => 'Composer/UseComposerLock',
                            'composer autoload'             => 'Composer/Autoload',
                    ),

                    'Web' => array(
                            '$_GET, _POST...'                => 'Php/UseWeb',
                            'Apache'                         => 'Extensions/Extapache',
                            'Fast CGI'                       => 'Extensions/Extfpm',
                            'IIS'                            => 'Extensions/Extiis',
                            'NSAPI'                          => 'Extensions/Extnsapi',
                            'Session'                        => 'Extensions/Extsession',
                            'Cookies'                        => 'Php/UseCookies',
                            'Browscap'                       => 'Php/UseBrowscap',
                    ),

                    'CLI' => array(
                            '$argv, $argc'                 => 'Php/UseCli',
                            'CLI script'                   => 'Files/IsCliScript',
                            'Ncurses'                      => 'Extensions/Extncurses',
                            'Newt'                         => 'Extensions/Extnewt',
                            'Readline'                     => 'Extensions/Extreadline',
                    ),

                    // filled later
                    'Composer Packages' => array(),

                    'PSR-compatibility' => array(
                        'PSR-3  (Log)'                       => 'Psr/Psr3Usage',
                        'PSR-6  (Caching)'                   => 'Psr/Psr6Usage',
                        'PSR-7  (HTTP message)'              => 'Psr/Psr7Usage',
                        'PSR-11 (Dependency container)'      => 'Psr/Psr11Usage',
                        'PSR-13 (Link)'                      => 'Psr/Psr13Usage',
                        'PSR-16 (Simple cache)'              => 'Psr/Psr16Usage',
                    ),

                    'Patterns' => array(
                        'Dependency Injection'               => 'Patterns/DependencyInjection',
                        'Courrier Anti-pattern'              => 'Patterns/CourrierAntiPattern',
                        'Factory'                            => 'Patterns/Factory',
                    ),

                    'Namespaces' => array(
                            'Namespaces'                     => 'Namespaces/Namespacesnames',
                            'Alias'                          => 'Namespaces/Alias',
                            'Group Use'                      => 'Php/GroupUseDeclaration',
                    ),

                    'Variables' => array(
                            'References'              => 'Variables/References',
                            'Array'                   => 'Arrays/Arrayindex',
                            'Multidimensional arrays' => 'Arrays/Multidimensional',
                            'Array short syntax'      => 'Arrays/ArrayNSUsage',
                            'List short syntax'       => 'Php/ListShortSyntax',
                            'Variable variables'      => 'Variables/VariableVariables',
                            'Unpacking inside arrays' => 'Php/UnpackingInsideArrays',

                            'PHP arrays'              => 'Arrays/Phparrayindex',

                            'Globals'                 => 'Structures/GlobalUsage',
                            'PHP SuperGlobals'        => 'Php/SuperGlobalUsage',
                    ),

                    'Functions' => array(
                            'Functions'                   => 'Functions/Functionnames',
                            'Redeclared PHP Functions'    => 'Functions/RedeclaredPhpFunction',
                            'Overridden PHP Functions'    => 'Php/OveriddenFunction',
                            'Redeclared Custom Functions' => 'Functions/MultipleDeclarations',
                            'Closures'                    => 'Closure',
                            'Arrow functions'             => 'Arrowfunction',
                            'first class callable'        => 'Php/FirstClassCallable',

                            'Types'                       => 'Functions/Typehints',
                            'Scalar Type'                 => 'Php/ScalarTypehintUsage',
                            'Return Type'                 => 'Php/ReturnTypehintUsage',
                            'Nullable Type'               => 'Php/UseNullableType',
                            'Never Type'                  => 'Php/NeverTypeUsage',
                            'Mixed Type'                  => 'Php/MixedUsage',
                            'False-True-Null Standalone'  => 'Typehints/FTNStandaloneType',
                            'PHP 8.0 Scalar Types'        => 'Php/Php80OnlyTypeHints',
                            'PHP 8.0 Union Types'         => 'Php/Php80UnionTypehint',
                            'PHP 8.1 Intersection Types'  => 'Php/Php81IntersectionTypehint',
                            'PHP 8.2 DNF Types'           => 'Php/UseDNF',
                            'Static variables'            => 'Variables/StaticVariables',
                            'Static Variable Initialisation' => 'Variables/StaticVariableInitialisation',

                            'Function dereferencing'      => 'Structures/FunctionSubscripting',
                            'Constant scalar expression'  => 'Structures/ConstantScalarExpression',
                            'Named parameters'            => 'Php/NamedParameterUsage',
                            '... usage'                   => 'Php/EllipsisUsage',
                            'func_get_args'               => 'Functions/VariableArguments',

                            'Dynamic functioncall'        => 'Functions/Dynamiccall',
                            'Fallback functioncall'       => 'Functions/FallbackFunction',

                            'Recursive Functions'         => 'Functions/Recursive',
                            'Generator Functions'         => 'Functions/IsGenerator',
                            'Conditioned Function'        => 'Functions/ConditionedFunctions',
                            'New initializers'            => 'Php/NewInitializers',
                    ),

                    'Classes' => array(
                            'Classes'                    => 'Class',
                            'Anonymous Classes'          => 'Classanonymous',
                            'Class aliases'              => 'Classes/ClassAliasUsage',

                            'Abstract classes'           => 'Classes/Abstractclass',
                            'Interfaces'                 => 'Interfaces',
                            'Traits'                     => 'Trait',
                            'Enums'                      => 'Enum',

                            'Static properties'          => 'Classes/StaticProperties',
                            'Readonly properties'        => 'Classes/ReadonlyUsage',

                            'Static methods'             => 'Classes/StaticMethods',
                            'Abstract methods'           => 'Classes/Abstractmethods',
                            'Final methods'              => 'Classes/Finalmethod',

                            'Class constants'            => 'Classes/ConstantDefinition',
                            'Trait constants'            => 'Traits/ConstantsInTraits',
                            'Overwritten constants'      => 'Classes/OverwrittenConst',
                            'Constant visibility'        => 'Classes/ConstVisibilityUsage',
                            'Typed visibility'           => 'Classes/TypedClassConstants',
                            'Final constants'            => 'Php/FinalConstant',
                            'Dynamic class constants'    => 'Classes/NewDynamicConstantSyntax',
                            'Enum Case In Constants'     => 'Php/UseEnumCaseInConstantExpression',

                            'Magic methods'              => 'Classes/MagicMethod',
                            'Cloning'                    => 'Classes/CloningUsage',
                            'Dynamic class call'         => 'Classes/VariableClasses',
                            'Typed properties'           => 'Php/TypedPropertyUsage',
                            'Promoted properties'        => 'Classes/PromotedProperties',
                            'Covariance'                 => 'Php/UseCovariance',
                            'Contravariance'             => 'Php/UseContravariance',

                            'Class overreach'            => 'Classes/ClassOverreach',
                            'Null Safe Operator ?->'     => 'Php/UseNullSafeOperator',
                            'PHP 4 constructor'          => 'Classes/OldStyleConstructor',
                            'Multiple class in one file' => 'Classes/MultipleClassesInFile',
                    ),

                    'Constants' => array(
                            'Constants'           => 'Constants/ConstantUsage',
                            'Dynamically create'  => 'Constants/DynamicCreation',
                            'Case Insensitive'    => 'Constants/CaseInsensitiveConstants',
                            'Boolean'             => 'Boolean',
                            'Null'                => 'Null',
                            'Variable Constant'   => 'Constants/VariableConstant',
                            'PHP constants'       => 'Constants/PhpConstantUsage',
                            'PHP Magic constants' => 'Constants/MagicConstantUsage',
                            'Conditioned constant'=> 'Constants/ConditionedConstants',
                    ),

                    'Numbers' => array(
                            'Integers'            => 'Integer',
                            'Hexadecimal'         => 'Type/Hexadecimal',
                            'Octal'               => 'Type/Octal',
                            'Binary'              => 'Type/Binary',
                            'Float'               => 'Float',
                            'Not-a-Number'        => 'Php/IsNAN',
                            'Infinity'            => 'Php/IsINF',
                    ),

                    'Strings' => array(
                            'Strings'             => 'String',
                            'Heredoc'             => 'Type/Heredoc',
                            'Nowdoc'              => 'Type/Nowdoc',
                            'Relaxed Heredoc'     => 'Php/FlexibleHeredoc',
                            '++ on strings'       => 'Php/PlusPlusOnLetters',
                     ),

                    'Errors' => array(
                            'Throw exceptions'    => 'Php/ThrowUsage',
                            'Try...Catch'         => 'Php/TryCatchUsage',
                            'Multiple catch'      => 'Php/TryMultipleCatch',
                            'Multiple Exceptions' => 'Exceptions/MultipleCatch',
                            'Finally'             => 'Structures/TryFinally',
                            'Non-capturing catch' => 'Exceptions/CatchUndefinedVariable',

                            'Trigger error'       => 'Php/TriggerErrorUsage',
                            'Error messages'      => 'Structures/ErrorMessages',

                            'Assertions'          => 'Php/AssertionUsage',

                            'Uses debug'          => 'Structures/UseDebug',
                     ),

                    'Crypto' => array(
                            'Argon2'              => 'Php/Argon2Usage',
                            'ext/openssl'         => 'Extensions/Extopenssl',
                            'ext/libsodium'       => 'Extensions/Extlibsodium',
                            'ext/mcrypt'          => 'Extensions/Extmcrypt',
                            'ext/mhash'           => 'Extensions/Extmhahs',
                     ),

                    'External systems' => array(
                            'System'           => 'Structures/ShellUsage',
                            'Files'            => 'Structures/FileUsage',
                            'LDAP'             => 'Extensions/Extldap',
                            'mail'             => 'Structures/MailUsage',
                     ),

                    'Languages' => array(
                            'Json'             => 'Extensions/Extjson',
//                            'pack'             => 'Structures/FileUsage',
//                            'SQL'             => 'Extensions/Extldap',
                            'Regex'             => 'Extensions/Extpcre',
                            'Ereg'             => 'Extensions/Extereg',
                     ),

                    'Extensions' => array(
                            'argon2'         => 'Php/Argon2Usage',
                            'ext/amqp'       => 'Extensions/Extamqp',
                            'ext/apache'     => 'Extensions/Extapache',
                            'ext/apc'        => 'Extensions/Extapc',
                            'ext/apcu'       => 'Extensions/Extapcu',
                            'ext/array'      => 'Extensions/Extarray',
                            'ext/ast'        => 'Extensions/Extast',
                            'ext/async'      => 'Extensions/Extasync',
                            'ext/bcmath'     => 'Extensions/Extbcmath',
                            'ext/bzip2'      => 'Extensions/Extbzip2',
                            'ext/calendar'   => 'Extensions/Extcalendar',
                            'ext/cmark'      => 'Extensions/Extcmark',
                            'ext/com'        => 'Extensions/Extcom',
                            'ext/crypto'     => 'Extensions/Extcrypto',
                            'ext/csv'        => 'Extensions/Extcsv',
                            'ext/ctype'      => 'Extensions/Extctype',
                            'ext/curl'       => 'Extensions/Extcurl',
                            'ext/cyrus'      => 'Extensions/Extcyrus',
                            'ext/date'       => 'Extensions/Extdate',
                            'ext/db2'        => 'Extensions/Extdb2',
                            'ext/dba'        => 'Extensions/Extdba',
                            'ext/decimal'    => 'Extensions/Extdecimal',
                            'ext/dio'        => 'Extensions/Extdio',
                            'ext/dom'        => 'Extensions/Extdom',
                            'ext/ds'         => 'Extensions/Extds',
                            'ext/eaccelerator' => 'Extensions/Exteaccelerator',
                            'ext/eio'        => 'Extensions/Exteio',
                            'ext/enchant'    => 'Extensions/Extenchant',
                            'ext/ereg'       => 'Extensions/Extereg',
                            'ext/ev'         => 'Extensions/Extev',
                            'ext/event'      => 'Extensions/Extevent',
                            'ext/excimer'    => 'Extensions/Extexcimer',
                            'ext/exif'       => 'Extensions/Extexif',
                            'ext/expect'     => 'Extensions/Extexpect',
                            'ext/fam'        => 'Extensions/Extfam',
                            'ext/fann'       => 'Extensions/Extfann',
                            'ext/ffi'        => 'Extensions/Extffi',
                            'ext/file'       => 'Extensions/Extfile',
                            'ext/fileinfo'   => 'Extensions/Extfileinfo',
                            'ext/filter'     => 'Extensions/Extfilter',
                            'ext/fpm'        => 'Extensions/Extfpm',
                            'ext/ftp'        => 'Extensions/Extftp',
                            'ext/gd'         => 'Extensions/Extgd',
                            'ext/gearman'    => 'Extensions/Extgearman',
                            'ext/gender'     => 'Extensions/Extgender',
                            'ext/geoip'      => 'Extensions/Extgeoip',
                            'ext/gettext'    => 'Extensions/Extgettext',
                            'ext/gmagick'    => 'Extensions/Extgmagick',
                            'ext/gmp'        => 'Extensions/Extgmp',
                            'ext/gnupg'      => 'Extensions/Extgnupg',
                            'ext/grpc'       => 'Extensions/Extgrpc',
                            'ext/hash'       => 'Extensions/Exthash',
                            'ext/hrtime'     => 'Extensions/Exthrtime',
                            'ext/ibase'      => 'Extensions/Extibase',
                            'ext/iconv'      => 'Extensions/Exticonv',
                            'ext/igbinary'   => 'Extensions/Extigbinary',
                            'ext/ice'        => 'Extensions/Extice',
                            'ext/iis'        => 'Extensions/Extiis',
                            'ext/imagick'    => 'Extensions/Extimagick',
                            'ext/imap'       => 'Extensions/Extimap',
                            'ext/info'       => 'Extensions/Extinfo',
                            'ext/inotify'    => 'Extensions/Extinotify',
                            'ext/intl'       => 'Extensions/Extintl',
                            'ext/json'       => 'Extensions/Extjson',
                            'ext/judy'       => 'Extensions/Extjudy',
                            'ext/kdm5'       => 'Extensions/Extkdm5',
                            'ext/lapack'     => 'Extensions/Extlapack',
                            'ext/ldap'       => 'Extensions/Extldap',
                            'ext/leveldb'    => 'Extensions/Extleveldb',
                            'ext/libevent'   => 'Extensions/Extlibevent',
                            'ext/libsodium'  => 'Extensions/Extlibsodium',
                            'ext/libxml'     => 'Extensions/Extlibxml',
                            'ext/lua'        => 'Extensions/Extlua',
                            'ext/lzf'        => 'Extensions/Extlzf',
                            'ext/mail'       => 'Extensions/Extmail',
                            'ext/mailparse'  => 'Extensions/Extmailparse',
                            'ext/math'       => 'Extensions/Extmath',
                            'ext/mbstring'   => 'Extensions/Extmbstring',
                            'ext/mcrypt'     => 'Extensions/Extmcrypt',
                            'ext/memcache'   => 'Extensions/Extmemcache',
                            'ext/memcached'  => 'Extensions/Extmemcached',
                            'ext/mhash'      => 'Extensions/Extmhash',
                            'ext/ming'       => 'Extensions/Extming',
                            'ext/mongo'      => 'Extensions/Extmongo',
                            'ext/mongodb'    => 'Extensions/Extmongodb',
                            'ext/msgpack'    => 'Extensions/Extmsgpack',
                            'ext/mssql'      => 'Extensions/Extmssql',
                            'ext/mysql'      => 'Extensions/Extmysql',
                            'ext/mysqli'     => 'Extensions/Extmysqli',
                            'ext/ncurses'    => 'Extensions/Extncurses',
                            'ext/newt'       => 'Extensions/Extnewt',
                            'ext/nsapi'      => 'Extensions/Extnsapi',
                            'ext/ob'         => 'Extensions/Extob',
                            'ext/oci8'       => 'Extensions/Extoci8',
                            'ext/odbc'       => 'Extensions/Extodbc',
                            'ext/opcache'    => 'Extensions/Extopcache',
                            'ext/opencensus' => 'Extensions/Extopencensus',
                            'ext/openssl'    => 'Extensions/Extopenssl',
                            'ext/parle'      => 'Extensions/Extparle',
                            'ext/parsekit'   => 'Extensions/Extparsekit',
                            'ext/password'   => 'Extensions/Extpassword',
                            'ext/pcntl'      => 'Extensions/Extpcntl',
                            'ext/pcov'       => 'Extensions/Extpcov',
                            'ext/pcre'       => 'Extensions/Extpcre',
                            'ext/pdo'        => 'Extensions/Extpdo',
                            'ext/pgsql'      => 'Extensions/Extpgsql',
                            'ext/phalcon'    => 'Extensions/Extphalcon',
                            'ext/phar'       => 'Extensions/Extphar',
                            'ext/php_http'   => 'Extensions/Exthttp',
                            'ext/pkcs11'     => 'Extensions/Extpkcs11',
                            'ext/posix'      => 'Extensions/Extposix',
                            'ext/proctitle'  => 'Extensions/Extproctitle',
                            'ext/protobuf'   => 'Extensions/Extprotobuf',
                            'ext/pspell'     => 'Extensions/Extpspell',
                            'ext/psr'        => 'Extensions/Extpsr',
                            'ext/rar'        => 'Extensions/Extrar',
                            'ext/rdkafka'    => 'Extensions/Extrdkafka',
                            'ext/readline'   => 'Extensions/Extreadline',
                            'ext/recode'     => 'Extensions/Extrecode',
                            'ext/redis'      => 'Extensions/Extredis',
                            'ext/reflexion'  => 'Extensions/Extreflection',
                            'ext/runkit'     => 'Extensions/Extrunkit',
                            'ext/sdl'        => 'Extensions/Extsdl',
                            'ext/seaslog'    => 'Extensions/Extseaslog',
                            'ext/sem'        => 'Extensions/Extsem',
                            'ext/session'    => 'Extensions/Extsession',
                            'ext/shmop'      => 'Extensions/Extshmop',
                            'ext/simplexml'  => 'Extensions/Extsimplexml',
                            'ext/snmp'       => 'Extensions/Extsnmp',
                            'ext/soap'       => 'Extensions/Extsoap',
                            'ext/sockets'    => 'Extensions/Extsockets',
                            'ext/sphinx'     => 'Extensions/Extsphinx',
                            'ext/spl'        => 'Extensions/Extspl',
                            'ext/spx'        => 'Extensions/Extspx',
                            'ext/sqlite'     => 'Extensions/Extsqlite',
                            'ext/sqlite3'    => 'Extensions/Extsqlite3',
                            'ext/sqlsrv'     => 'Extensions/Extsqlsrv',
                            'ext/ssh2'       => 'Extensions/Extssh2',
                            'ext/standard'   => 'Extensions/Extstandard',
                            'ext/stats'      => 'Extensions/Extstats',
                            'ext/stomp'      => 'Extensions/Extstomp',
                            'ext/string'     => 'Extensions/Extstring',
                            'ext/suhosin'    => 'Extensions/Extsuhosin',
                            'ext/svm'        => 'Extensions/Extsvm',
                            'ext/swoole'     => 'Extensions/Extswoole',
                            'ext/taint'      => 'Extensions/Exttaint',
                            'ext/tidy'       => 'Extensions/Exttidy',
                            'ext/tokenizer'  => 'Extensions/Exttokenizer',
                            'ext/tokyotyrant'=> 'Extensions/Exttokyotyrant',
                            'ext/trader'     => 'Extensions/Exttrader',
                            'ext/uopz'       => 'Extensions/Extuopz',
                            'ext/uuid'       => 'Extensions/Extuuid',
                            'ext/v8js'       => 'Extensions/Extv8js',
                            'ext/varnish'    => 'Extensions/Extvarnish',
                            'ext/vips'       => 'Extensions/Extvips',
                            'ext/wasm'       => 'Extensions/Extwasm',
                            'ext/wddx'       => 'Extensions/Extwddx',
                            'ext/weakref'    => 'Extensions/Extweakref',
                            'ext/wikidiff2'  => 'Extensions/Extwikidiff2',
                            'ext/wincache'   => 'Extensions/Extwincache',
                            'ext/xattr'      => 'Extensions/Extxattr',
                            'ext/xdebug'     => 'Extensions/Extxdebug',
                            'ext/xdiff'      => 'Extensions/Extxdiff',
                            'ext/xhprof'     => 'Extensions/Extxhprof',
                            'ext/xml'        => 'Extensions/Extxml',
                            'ext/xmlreader'  => 'Extensions/Extxmlreader',
                            'ext/xmlrpc'     => 'Extensions/Extxmlrpc',
                            'ext/xmlwriter'  => 'Extensions/Extxmlwriter',
                            'ext/xsl'        => 'Extensions/Extxsl',
                            'ext/xxtea'      => 'Extensions/Extxxtea',
                            'ext/yaml'       => 'Extensions/Extyaml',
                            'ext/yar'        => 'Extensions/Extyar',
                            'ext/zendmonitor'=> 'Extensions/Extzendmonitor',
                            'ext/zip'        => 'Extensions/Extzip',
                            'ext/zlib'       => 'Extensions/Extzlib',
                            'ext/zmq'        => 'Extensions/Extzmq',
                            'ext/zookeeper'  => 'Extensions/Extzookeeper',
       //                          'ext/skeleton'   => 'Extensions/Extskeleton',
                    ),

                    'Frameworks' => array(
                            'Codeigniter'          => 'Vendors/Codeigniter',
                            'Concrete5'            => 'Vendors/Concrete5',
                            'Drupal'               => 'Vendors/Drupal',
                            'Ez'                   => 'Vendors/Ez',
                            'Fuel'                 => 'Vendors/Fuel',
                            'Joomla'               => 'Vendors/Joomla',
                            'Laravel'              => 'Vendors/Laravel',
                            'Phalcon'              => 'Vendors/Phalcon',
                            'Sylius'               => 'Vendors/Sylius',
                            'Symfony'              => 'Vendors/Symfony',
                            'Typo3'                => 'Vendors/Typo3',
                            'Wordpress'            => 'Vendors/Wordpress',
                    )
                );

    public function originals(): array {
        return $this->extensions;
    }

    public function prepare(): void {
        // collecting information for Extensions
        $themed = array_merge(...array_values($this->extensions));
        $res = $this->dump->fetchAnalysersCounts($themed);
        $sources = $res->toHash('analyzer', 'count');

        $res = $this->dump->fetchTable('atomsCounts');
        $atoms   = $res->toHash('atom', 'count');

        $sources = array_merge($sources, $atoms);

        foreach ($this->extensions as $section => $hash) {
            $this->values[$section] = array();

            foreach ($hash as $name => $ext) {
                if (!isset($sources[$ext]) &&
                        !str_contains($ext, '/')  ) {
                    $this->values[$section][$name] = Reports::NO;
                    continue;
                }

                if (!isset($sources[$ext])) {
                    $this->values[$section][$name] = Reports::NOT_RUN;
                    continue;
                }
                if (!in_array($ext, $themed)) {
                    $this->values[$section][$name] = Reports::NOT_RUN;
                    continue;
                }

                // incompatible
                if ($sources[$ext] == Analyzer::CONFIGURATION_INCOMPATIBLE) {
                    $this->values[$section][$name] = Reports::INCOMPATIBLE;
                    continue ;
                }

                if ($sources[$ext] == Analyzer::VERSION_INCOMPATIBLE) {
                    $this->values[$section][$name] = Reports::INCOMPATIBLE;
                    continue ;
                }

                $this->values[$section][$name] = $sources[$ext] > 0 ? Reports::YES : Reports::NO;
            }

            if ($section == 'Extensions') {
                $list = $this->values[$section];
                uksort($this->values[$section], function (string $ka, string $kb) use ($list): int {
                    if ($list[$ka] !== $list[$kb]) {
                        return $list[$ka] === Reports::YES ? -1 : 1;
                    }

                    return $ka <=> $kb;
                });
            }
        }
        // collecting information for Composer
        if (isset($sources['Composer/PackagesNames'])) {
            $this->values['Composer Packages'] = array();
            $res = $this->dump->fetchAnalyzer(array('Composer/PackagesNames'));
            $this->values = array_map('PHPsyntax', $res->getColumn('fullcode'));
        } else {
            unset($this->values['Composer Packages']);
        }

        // Special case for the encodings : one tick each.
        $res = $this->dump->fetchTable('stringEncodings');
        // sort
        foreach ($res->toArray() as $row) {
            if (empty($row['encoding'])) {
                $this->values['Strings']['Unknown encoding'] = Reports::YES;
            } elseif (empty($row['block'])) {
                $this->values['Strings'][$row['encoding']] = Reports::YES;
            } else {
                $this->values['Strings'][$row['encoding'] . ' (' . $row['block'] . ')' ] = Reports::YES;
            }
        }
    }
}

?>
