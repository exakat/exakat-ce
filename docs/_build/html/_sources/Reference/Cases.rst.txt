.. _Cases:

Real Code Cases
===================

Introduction
---------------


All the examples in this section are real code, extracted from major PHP applications. 


List of real code Cases
------------------------------


.. _case-multiple-index-definition:

Multiple Index Definition
#########################

.. _case-magento-arrays-multipleidenticalkeys:

Magento
+++++++


:ref:`multiple-index-definition`, in app/code/core/Mage/Adminhtml/Block/System/Convert/Gui/Grid.php:80. 

'type' is defined twice. The first one, 'options' is overwritten.

.. code-block:: php

    $this->addColumn('store_id', array(
                'header'    => Mage::helper('adminhtml')->__('Store'),
                'type'      => 'options',
                'align'     => 'center',
                'index'     => 'store_id',
                'type'      => 'store',
                'width'     => '200px',
            ));


.. _case-mediawiki-arrays-multipleidenticalkeys:

MediaWiki
+++++++++


:ref:`multiple-index-definition`, in resources/Resources.php:223. 

'target' is repeated, though with the same values. This is just dead code.

.. code-block:: php

    // inside a big array
    	'jquery.getAttrs' => [
    		'targets' => [ 'desktop', 'mobile' ],
    		'scripts' => 'resources/src/jquery/jquery.getAttrs.js',
    		'targets' => [ 'desktop', 'mobile' ],
    	],
        // big array continues


.. _case-don't-unset-properties:

Don't Unset Properties
######################

.. _case-vanilla-classes-dontunsetproperties:

Vanilla
+++++++


:ref:`don't-unset-properties`, in applications/dashboard/models/class.activitymodel.php:1073. 

The _NotificationQueue property, in this class, is defined as an array. Here, it is destroyed, then recreated. The unset() is too much, as the assignation is sufficient to reset the array 

.. code-block:: php

    /**
         * Clear notification queue.
         *
         * @since 2.0.17
         * @access public
         */
        public function clearNotificationQueue() {
            unset($this->_NotificationQueue);
            $this->_NotificationQueue = [];
        }


.. _case-typo3-classes-dontunsetproperties:

Typo3
+++++


:ref:`don't-unset-properties`, in typo3/sysext/linkvalidator/Classes/Linktype/InternalLinktype.php:73. 

The property errorParams is emptied by unsetting it. The property is actually defined in the above class, as an array. Until the next error is added to this list, any access to the error list has to be checked with isset(), or yield an 'Undefined' warning. 

.. code-block:: php

    public function checkLink($url, $softRefEntry, $reference)
        {
            $anchor = '';
            $this->responseContent = true;
            // Might already contain values - empty it
            unset($this->errorParams);
    //....
    
    abstract class AbstractLinktype implements LinktypeInterface
    {
        /**
         * Contains parameters needed for the rendering of the error message
         *
         * @var array
         */
        protected $errorParams = [];


.. _case-forgotten-visibility:

Forgotten Visibility
####################

.. _case-fuelcms-classes-nonppp:

FuelCMS
+++++++


:ref:`forgotten-visibility`, in /fuel/modules/fuel/controllers/Module.php:713. 

Missing visibility for the index() method,and all the methods in the Module class.

.. code-block:: php

    class Module extends Fuel_base_controller {
    	
    	// --------------------------------------------------------------------
    	
    	/**
    	 * Displays the list (table) view
    	 *
    	 * @access	public
    	 * @return	void
    	 */	
    	function index()
    	{
    		$this->items();
    	}


.. _case-livezilla-classes-nonppp:

LiveZilla
+++++++++


:ref:`forgotten-visibility`, in livezilla/_lib/objects.global.users.inc.php:2516. 

Static method that could be public.

.. code-block:: php

    class Visitor extends BaseUser 
    {
    // Lots of code
    
        static function CreateSPAMFilter($_userId,$_base64=true)
        {
            if(!empty(Server::$Configuration->File[gl_sfa]))
            {


.. _case-non-static-methods-called-in-a-static:

Non Static Methods Called In A Static
#####################################

.. _case-dolphin-classes-nonstaticmethodscalledstatic:

Dolphin
+++++++


:ref:`non-static-methods-called-in-a-static`, in Dolphin-v.7.3.5/xmlrpc/BxDolXMLRPCFriends.php:11. 

getIdByNickname() is indeed defined in the class 'BxDolXMLRPCUtil' and it calls the database. The class relies on functions (not methods) to query the database with the correct connexion. 

.. code-block:: php

    class BxDolXMLRPCFriends
    {
        function getFriends($sUser, $sPwd, $sNick, $sLang)
        {
            $iIdProfile = BxDolXMLRPCUtil::getIdByNickname ($sNick);


.. _case-magento-classes-nonstaticmethodscalledstatic:

Magento
+++++++


:ref:`non-static-methods-called-in-a-static`, in app/code/core/Mage/Paypal/Model/Payflowlink.php:143. 

Mage_Payment_Model_Method_Abstract is an abstract class : this way, it is not possible to instantiate it and then, access its methods. The class is extended, so it could be called from one of the objects. Although, the troubling part is that isAvailable() uses $this, so it can't be static. 

.. code-block:: php

    Mage_Payment_Model_Method_Abstract::isAvailable($quote)


.. _case-redefined-default:

Redefined Default
#################

.. _case-piwigo-classes-redefineddefault:

Piwigo
++++++


:ref:`redefined-default`, in admin/include/updates.class.php:34. 

default_themes is defined as an empty array, then filled with new values. Same for default_plugins. Both may be defined as declaration time, and not during the constructor.

.. code-block:: php

    class updates
    {
      var $types = array();
      var $plugins;
      var $themes;
      var $languages;
      var $missing = array();
      var $default_plugins = array();
      var $default_themes = array();
      var $default_languages = array();
      var $merged_extensions = array();
      var $merged_extension_url = 'http://piwigo.org/download/merged_extensions.txt';
    
      function __construct($page='updates')
      {
        $this->types = array('plugins', 'themes', 'languages');
    
        if (in_array($page, $this->types))
        {
          $this->types = array($page);
        }
        $this->default_themes = array('clear', 'dark', 'Sylvia', 'elegant', 'smartpocket');
        $this->default_plugins = array('AdminTools', 'TakeATour', 'language_switch', 'LocalFilesEditor');


.. _case-static-methods-can't-contain-$this:

Static Methods Can't Contain $this
##################################

.. _case-xataface-classes-staticcontainsthis:

xataface
++++++++


:ref:`static-methods-can't-contain-$this`, in Dataface/LanguageTool.php:48. 

$this is hidden in the arguments of the static call to the method.

.. code-block:: php

    public static function loadRealm($name){
    		return self::getInstance($this->app->_conf['default_language'])->loadRealm($name);
    	}


.. _case-sugarcrm-classes-staticcontainsthis:

SugarCrm
++++++++


:ref:`static-methods-can't-contain-$this`, in SugarCE-Full-6.5.26/modules/ACLActions/ACLAction.php:332. 

Notice how $this is tested for existence before using it. It seems strange, at first, but we have to remember that if $this is never set when calling a static method, a static method may be called with $this. Confusingly, this static method may be called in two ways. 

.. code-block:: php

    static function hasAccess($is_owner=false, $access = 0){
    
            if($access != 0 && $access == ACL_ALLOW_ALL || ($is_owner && $access == ACL_ALLOW_OWNER))return true;
           //if this exists, then this function is not static, so check the aclaccess parameter
            if(isset($this) && isset($this->aclaccess)){
                if($this->aclaccess == ACL_ALLOW_ALL || ($is_owner && $this->aclaccess == ACL_ALLOW_OWNER))
                return true;
            }
            return false;
        }


.. _case-wrong-access-style-to-property:

Wrong Access Style to Property
##############################

.. _case-humo-gen-classes-undeclaredstaticproperty:

HuMo-Gen
++++++++


:ref:`wrong-access-style-to-property`, in wp-admin/includes/misc.php:74. 

lame_binary_path is a static property, but it is accessed as a normal property in the exception call, while it is checked with a valid syntax.

.. code-block:: php

    protected function wavToMp3($data)
        {
            if (!file_exists(self::$lame_binary_path) || !is_executable(self::$lame_binary_path)) {
                throw new Exception('Lame binary  . $this->lame_binary_path .  does not exist or is not executable');
            }


.. _case-undefined-properties:

Undefined Properties
####################

.. _case-wordpress-classes-undefinedproperty:

WordPress
+++++++++


:ref:`undefined-properties`, in wp-admin/includes/misc.php:74. 

Properties are not defined, but they are thoroughly initialized when the XML document is parsed. All those definition should be in a property definition, for clear documentation.

.. code-block:: php

    $this->DeliveryLine1 = '';
            $this->DeliveryLine2 = '';
            $this->City = '';
            $this->State = '';
            $this->ZipAddon = '';


.. _case-mediawiki-classes-undefinedproperty:

MediaWiki
+++++++++


:ref:`undefined-properties`, in includes/logging/LogFormatter.php:561. 

parsedParametersDeleteLog is an undefined property. Defining the property with a null default value is important here, to keep the code running. 

.. code-block:: php

    protected function getMessageParameters() {
    		if ( isset( $this->parsedParametersDeleteLog ) ) {
    			return $this->parsedParametersDeleteLog;
    		}


.. _case-use-class-operator:

Use \:\:Class Operator
######################

.. _case-typo3-classes-useclassoperator:

Typo3
+++++


:ref:`use-class-operator`, in typo3/sysext/install/Configuration/ExtensionScanner/Php/ConstructorArgumentMatcher.php:4. 

``TYPO3\CMS\Core\Package\PackageManager`` could be ``TYPO3\CMS\Core\Package\PackageManager::class``. 

.. code-block:: php

    return [
        'TYPO3\CMS\Core\Package\PackageManager' => [
            'required' => [
                'numberOfMandatoryArguments' => 1,
                'maximumNumberOfArguments' => 1,


.. _case-use-instanceof:

Use Instanceof
##############

.. _case-teampass-classes-useinstanceof:

TeamPass
++++++++


:ref:`use-instanceof`, in includes/libraries/Database/Meekrodb/db.class.php:506. 

In this code, ``is_object()`` and ``instanceof`` have the same basic : they both check that $ts is an object. In fact, ``instanceof`` is more precise, and give more information about the variable. 

.. code-block:: php

    protected function parseTS($ts) {
        if (is_string($ts)) return date('Y-m-d H:i:s', strtotime($ts));
        else if (is_object($ts) && ($ts instanceof DateTime)) return $ts->format('Y-m-d H:i:s');
      }


.. _case-zencart-classes-useinstanceof:

Zencart
+++++++


:ref:`use-instanceof`, in includes/modules/payment/firstdata_hco.php:104. 

In this code, ``is_object()`` is used to check the status of the order. Possibly, $order is false or null in case of incompatible status. Yet, when $object is an object, and in particular being a global that may be assigned anywhere else in the code, it seems that the method 'update_status' is magically always available. Here, using instance of to make sure that $order is an 'paypal' class, or a 'storepickup' or any of the payment class.  

.. code-block:: php

    function __construct() {
        global $order;
    
        // more lines, no mention of $order
        if (is_object($order)) $this->update_status();
    
        // more code
    }


.. _case-use-const:

Use const
#########

.. _case-phpmyadmin-constants-constrecommended:

phpMyAdmin
++++++++++


:ref:`use-const`, in error_report.php:17. 

This may be turned into a `const` call, with a static expression. 

.. code-block:: php

    define('ROOT_PATH', __DIR__ . DIRECTORY_SEPARATOR)


.. _case-piwigo-constants-constrecommended:

Piwigo
++++++


:ref:`use-const`, in include/functions_plugins.inc.php:32. 

Const works efficiently with literal

.. code-block:: php

    define('EVENT_HANDLER_PRIORITY_NEUTRAL', 50)


.. _case-multiple-constant-definition:

Multiple Constant Definition
############################

.. _case-dolibarr-constants-multipleconstantdefinition:

Dolibarr
++++++++


:ref:`multiple-constant-definition`, in htdocs/main.inc.php:914. 

All is documented here : 'Constants used to defined number of lines in textarea'. Constants are not changing during an execution, and this allows the script to set values early in the process, and have them used later, in the templates. Yet, building constants dynamically may lead to confusion, when developpers are not aware of the change. 

.. code-block:: php

    // Constants used to defined number of lines in textarea
    if (empty($conf->browser->firefox))
    {
    	define('ROWS_1',1);
    	define('ROWS_2',2);
    	define('ROWS_3',3);
    	define('ROWS_4',4);
    	define('ROWS_5',5);
    	define('ROWS_6',6);
    	define('ROWS_7',7);
    	define('ROWS_8',8);
    	define('ROWS_9',9);
    }
    else
    {
    	define('ROWS_1',0);
    	define('ROWS_2',1);
    	define('ROWS_3',2);
    	define('ROWS_4',3);
    	define('ROWS_5',4);
    	define('ROWS_6',5);
    	define('ROWS_7',6);
    	define('ROWS_8',7);
    	define('ROWS_9',8);
    }


.. _case-openconf-constants-multipleconstantdefinition:

OpenConf
++++++++


:ref:`multiple-constant-definition`, in modules/request.php:71. 

The constant is build according to the situation, in the part of the script (file request.php). This hides the actual origin of the value, but keeps the rest of the code simple. Just keep in mind that this constant may have different values.

.. code-block:: php

    if (isset($_GET['ocparams']) && !empty($_GET['ocparams'])) {
    		$params = '';
    		if (preg_match_all("/(\w+)--(\w+)_-/", $_GET['ocparams'], $matches)) {
    			foreach ($matches[1] as $idx => $m) {
    				if (($m != 'module') && ($m != 'action') && preg_match("/^[\w-]+$/", $m)) {
    					$params .= '&' . $m . '=' . urlencode($matches[2][$idx]);
    					$_GET[$m] = $matches[2][$idx];
    				}
    			}
    		}
    		unset($_GET['ocparams']);
    		define('OCC_SELF', $_SERVER['PHP_SELF'] . '?module=' . $_REQUEST['module'] . '&action=' . $_GET['action'] . $params);
    	} elseif (isset($_SERVER['REQUEST_URI']) && strstr($_SERVER['REQUEST_URI'], '?')) {
    		define('OCC_SELF', htmlspecialchars($_SERVER['REQUEST_URI']));
    	} elseif (isset($_SERVER['QUERY_STRING']) && strstr($_SERVER['QUERY_STRING'], '&')) {
    		define('OCC_SELF', $_SERVER['PHP_SELF'] . '?' . htmlspecialchars($_SERVER['QUERY_STRING']));
    	} else {
    		err('This server does not support REQUEST_URI or QUERY_STRING','Error');
    	}


.. _case-throw-functioncall:

Throw Functioncall
##################

.. _case-sugarcrm-exceptions-throwfunctioncall:

SugarCrm
++++++++


:ref:`throw-functioncall`, in include/externalAPI/cmis_repository_wrapper.php:918. 

SugarCRM uses exceptions to fill work in progress. Here, we recognize a forgotten 'new' that makes throw call a function named 'Exception'. This fails with a Fatal Error, and doesn't issue the right messsage. The same error had propgated in the code by copy and paste : it is available 17 times in that same file.

.. code-block:: php

    function getContentChanges()
        {
            throw Exception("Not Implemented");
        }


.. _case-zurmo-exceptions-throwfunctioncall:

Zurmo
+++++


:ref:`throw-functioncall`, in app/protected/modules/gamification/rules/collections/GameCollectionRules.php:66. 

Other part of the code actually instantiate the exception before throwing it.

.. code-block:: php

    abstract class GameCollectionRules
        {
            /**
             * @return string
             * @throws NotImplementedException - Implement in children classes
             */
            public static function getType()
            {
                throw NotImplementedException();
            }


.. _case-useless-catch:

Useless Catch
#############

.. _case-zurmo-exceptions-uselesscatch:

Zurmo
+++++


:ref:`useless-catch`, in app/protected/modules/workflows/forms/attributes/ExplicitReadWriteModelPermissionsWorkflowActionAttributeForm.php:99. 

Catch the exception, then return. At least, the comment is honest.

.. code-block:: php

    try
                    {
                        $group = Group::getById((int)$this->type);
                        $explicitReadWriteModelPermissions->addReadWritePermitable($group);
                    }
                    catch (NotFoundException $e)
                    {
                        //todo: handle exception better
                        return;
                    }


.. _case-prestashop-exceptions-uselesscatch:

PrestaShop
++++++++++


:ref:`useless-catch`, in src/Core/Addon/Module/ModuleManagerBuilder.php:170. 

Here, the catch clause will intercept a IO problem while writing element on the disk, and will return false. Since this is a constructor, the returned value will be ignored and the object will be left in a wrong state, since it was not totally inited.

.. code-block:: php

    private function __construct()
        {
        // More code......
                try {
                    $filesystem = new Filesystem();
                    $filesystem->dumpFile($phpConfigFile, '<?php return ' . var_export($config, true) . ';' . \n);
                } catch (IOException $e) {
                    return false;
                }
            }


.. _case-aliases-usage:

Aliases Usage
#############

.. _case-cleverstyle-functions-aliasesusage:

Cleverstyle
+++++++++++


:ref:`aliases-usage`, in modules/HybridAuth/Hybrid/thirdparty/Vimeo/Vimeo.php:422. 

is_writeable() should be written is_writable(). No extra 'e'. 

.. code-block:: php

    is_writeable($chunk_temp_dir)


.. _case-phpmyadmin-functions-aliasesusage:

phpMyAdmin
++++++++++


:ref:`aliases-usage`, in libraries/classes/Server/Privileges.php:5064. 

join() should be written implode()

.. code-block:: php

    join('`, `', $tmp_privs2['Update'])


.. _case-callback-function-needs-return:

Callback Function Needs Return
##############################

.. _case-contao-functions-callbackneedsreturn:

Contao
++++++


:ref:`callback-function-needs-return`, in core-bundle/src/Resources/contao/modules/ModuleQuicklink.php:91. 

The empty closure returns `null`. The array_flip() array has now all its values set to null, and reset, as intended. A better alternative is to use the array_fill_keys() function, which set a default value to every element of an array, once provided with the expected keys.

.. code-block:: php

    $arrPages = array_map(function () {}, array_flip($tmp));


.. _case-phpdocumentor-functions-callbackneedsreturn:

Phpdocumentor
+++++++++++++


:ref:`callback-function-needs-return`, in src/phpDocumentor/Plugin/ServiceProvider.php:24. 

The array_walk() function is called on the plugin's list. Each element is registered with the application, but is not used directly : this is for later. The error mechanism is to throw an exception : this is the only expected feedback. As such, no return is expected. May be a 'foreach' loop would be more appropriate here, but this is syntactic sugar.

.. code-block:: php

    array_walk(
                $plugins,
                function ($plugin) use ($app) {
                    /** @var Plugin $plugin */
                    $provider = (strpos($plugin->getClassName(), '\') === false)
                        ? sprintf('phpDocumentor\Plugin\%s\ServiceProvider', $plugin->getClassName())
                        : $plugin->getClassName();
                    if (!class_exists($provider)) {
                        throw new \RuntimeException('Loading Service Provider for ' . $provider . ' failed.');
                    }
    
                    try {
                        $app->register(new $provider($plugin));
                    } catch (\InvalidArgumentException $e) {
                        throw new \RuntimeException($e->getMessage());
                    }
                }
            );


.. _case-deep-definitions:

Deep Definitions
################

.. _case-dolphin-functions-deepdefinitions:

Dolphin
+++++++


:ref:`deep-definitions`, in wp-admin/includes/misc.php:74. 

The ConstructHiddenValues function builds the ConstructHiddenSubValues function. Thus, ConstructHiddenValues can only be called once. 

.. code-block:: php

    function ConstructHiddenValues($Values)
    {
        /**
         *    Recursive function, processes multidimensional arrays
         *
         * @param string $Name  Full name of array, including all subarrays' names
         *
         * @param array  $Value Array of values, can be multidimensional
         *
         * @return string    Properly consctructed <input type="hidden"...> tags
         */
        function ConstructHiddenSubValues($Name, $Value)
        {
            if (is_array($Value)) {
                $Result = "";
                foreach ($Value as $KeyName => $SubValue) {
                    $Result .= ConstructHiddenSubValues("{$Name}[{$KeyName}]", $SubValue);
                }
            } else // Exit recurse
            {
                $Result = "<input type="hidden" name=\"" . htmlspecialchars($Name) . "\" value=\"" . htmlspecialchars($Value) . "\" />\n";
            }
    
            return $Result;
        }
    
        /* End of ConstructHiddenSubValues function */
    
        $Result = '';
        if (is_array($Values)) {
            foreach ($Values as $KeyName => $Value) {
                $Result .= ConstructHiddenSubValues($KeyName, $Value);
            }
        }
    
        return $Result;
    }


.. _case-unused-inherited-variable-in-closure:

Unused Inherited Variable In Closure
####################################

.. _case-shopware-functions-unusedinheritedvariable:

shopware
++++++++


:ref:`unused-inherited-variable-in-closure`, in recovery/update/src/app.php:129. 

In the first closuree, $containere is used as the root for the method calls, but $app is not used. It may be dropped. In fact, some of the following calls to $app->map() only request one inherited, $container.

.. code-block:: php

    $app->map('/applyMigrations', function () use ($app, $container) {
        $container->get('controller.batch')->applyMigrations();
    })->via('GET', 'POST')->name('applyMigrations');
    
    $app->map('/importSnippets', function () use ($container) {
        $container->get('controller.batch')->importSnippets();
    })->via('GET', 'POST')->name('importSnippets');


.. _case-mautic-functions-unusedinheritedvariable:

Mautic
++++++


:ref:`unused-inherited-variable-in-closure`, in MauticCrmBundle/Tests/Integration/SalesforceIntegrationTest.php:1202. 

$max is relayed to getLeadsToCreate(), while $restart is omitted. It may be dropped, along with its reference.

.. code-block:: php

    function () use (&$restart, $max) {
                        $args = func_get_args();
    
                        if (false === $args[2]) {
                            return $max;
                        }
    
                        $createLeads = $this->getLeadsToCreate($args[2], $max);
    
                        // determine whether to return a count or records
                        if (false === $args[2]) {
                            return count($createLeads);
                        }
    
                        return $createLeads;
                    }


.. _case-use-constant-as-arguments:

Use Constant As Arguments
#########################

.. _case-tikiwiki-functions-useconstantasarguments:

Tikiwiki
++++++++


:ref:`use-constant-as-arguments`, in lib/language/Language.php:112. 

E_WARNING is a valid value, but PHP documentation for trigger_error() explains that E_USER constants should be used. 

.. code-block:: php

    trigger_error("Octal or hexadecimal string '" . $match[1] . "' not supported", E_WARNING)


.. _case-shopware-functions-useconstantasarguments:

shopware
++++++++


:ref:`use-constant-as-arguments`, in engine/Shopware/Plugins/Default/Core/Debug/Components/EventCollector.php:106. 

One example where code review reports errors where unit tests don't : array_multisort actually requires sort order first (SORT_ASC or SORT_DESC), then sort flags (such as SORT_NUMERIC). Here, with SORT_DESC = 3 and SORT_NUMERIC = 1, PHP understands it as the coders expects it. The same error is repeated six times in the code. 

.. code-block:: php

    array_multisort($order, SORT_NUMERIC, SORT_DESC, $this->results)


.. _case-wrong-number-of-arguments:

Wrong Number Of Arguments
#########################

.. _case-xataface-functions-wrongnumberofarguments:

xataface
++++++++


:ref:`wrong-number-of-arguments`, in actions/existing_related_record.php:130. 

df_display() actually requires only 2 arguments, while three are provided. The last argument is completely ignored. df_display() is called in a total of 9 places : this now looks like an API change that left many calls untouched.

.. code-block:: php

    df_display($context, $template, true);
    
    // in public-api.php :
    function df_display($context, $template_name){
    	import( 'Dataface/SkinTool.php');
    	$st = Dataface_SkinTool::getInstance();
    	
    	return $st->display($context, $template_name);
    }


.. _case-wrong-optional-parameter:

Wrong Optional Parameter
########################

.. _case-fuelcms-functions-wrongoptionalparameter:

FuelCMS
+++++++


:ref:`wrong-optional-parameter`, in fuel/modules/fuel/helpers/validator_helper.php:78. 

The $regex parameter should really be first, as it is compulsory. Though, if this is a legacy function, it may be better to give regex a default value, such as empty string or null, and test it before using it.

.. code-block:: php

    if (!function_exists('regex'))
    {
    	function regex($var = null, $regex)
    	{
    		return preg_match('#'.$regex.'#', $var);
    	} 
    }


.. _case-vanilla-functions-wrongoptionalparameter:

Vanilla
+++++++


:ref:`wrong-optional-parameter`, in applications/dashboard/modules/class.navmodule.php:99. 

Note the second parameter, $dropdown, which has no default value. It is relayed to the addDropdown method, which as no default value too. Since both methods are documented, we can see that they should be an addDropdown : null is probably a good idea, coupled with an explicit check on the actual value.

.. code-block:: php

    /**
         * Add a dropdown to the items array if it satisfies the $isAllowed condition.
         *
         * @param bool|string|array $isAllowed Either a boolean to indicate whether to actually add the item
         * or a permission string or array of permission strings (full match) to check.
         * @param DropdownModule $dropdown The dropdown menu to add.
         * @param string $key The item's key (for sorting and CSS targeting).
         * @param string $cssClass The dropdown wrapper's CSS class.
         * @param array|int $sort Either a numeric sort position or and array in the style: array('before|after', 'key').
         * @return NavModule $this The calling object.
         */
        public function addDropdownIf($isAllowed = true, $dropdown, $key = '', $cssClass = '', $sort = []) {
            if (!$this->isAllowed($isAllowed)) {
                return $this;
            } else {
                return $this->addDropdown($dropdown, $key, $cssClass, $sort);
            }
        }


.. _case-undefined-interfaces:

Undefined Interfaces
####################

.. _case-xataface-interfaces-undefinedinterfaces:

xataface
++++++++


:ref:`undefined-interfaces`, in Dataface/Error.php:112. 

Exception seems to be a typo, and leads to an always-true expression.

.. code-block:: php

    public static function isError($obj){
    		if ( !PEAR::isError($obj) and !($obj instanceof Exception_) ) return false;
    		return ($obj->getCode() >= DATAFACE_E_ERROR);
    	}


.. _case-hidden-use-expression:

Hidden Use Expression
#####################

.. _case-tikiwiki-namespaces-hiddenuse:

Tikiwiki
++++++++


:ref:`hidden-use-expression`, in lib/core/Tiki/Command/DailyReportSendCommand.php:17. 

Sneaky error_reporting, hidden among the use calls. 

.. code-block:: php

    namespace Tiki\Command;
    
    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputArgument;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Input\InputOption;
    use Symfony\Component\Console\Output\OutputInterface;
    error_reporting(E_ALL);
    use TikiLib;
    use Reports_Factory;


.. _case-openemr-namespaces-hiddenuse:

OpenEMR
+++++++


:ref:`hidden-use-expression`, in interface/patient_file/summary/browse.php:23. 

Use expression is only reached when the csrf token is checked. This probably save some CPU when no csrf is available, but it breaks the readability of the file.

.. code-block:: php

    <?php
    /**
     * Patient selector for insurance gui
     *
     * @package   OpenEMR
     * @link      http://www.open-emr.org
     * @author    Brady Miller <brady.g.miller@gmail.com>
     * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
     * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
     */
    
    
    require_once(../../globals.php);
    require_once($srcdir/patient.inc);
    require_once($srcdir/options.inc.php);
    
    if (!empty($_POST)) {
        if (!verifyCsrfToken($_POST[csrf_token_form])) {
            csrfNotVerified();
        }
    }
    
    use OpenEMR\Core\Header;


.. _case-multiple-alias-definitions:

Multiple Alias Definitions
##########################

.. _case-churchcrm-namespaces-multiplealiasdefinitions:

ChurchCRM
+++++++++


:ref:`multiple-alias-definitions`, in Various files:--. 

It is actually surprising to find FamilyQuery defined as ChurchCRM\Base\FamilyQuery only once, while all other reference are for ChurchCRM\FamilyQuery. That lone use is actually useful in the code, so it is not a forgotten refactorisation. 

.. code-block:: php

    use ChurchCRM\Base\FamilyQuery	// in /src/MapUsingGoogle.php:7
    
    use ChurchCRM\FamilyQuery	// in /src/ChurchCRM/Dashboard/EventsDashboardItem.php:8
                                // and 29 other files


.. _case-phinx-namespaces-multiplealiasdefinitions:

Phinx
+++++


:ref:`multiple-alias-definitions`, in Various files too:--. 

One 'Command' is refering to a local Command class, while the other is refering to an imported class. They are all in a similar name space Console\Command. 

.. code-block:: php

    use Phinx\Console\Command	                    //in file /src/Phinx/Console/PhinxApplication.php:34
    use Symfony\Component\Console\Command\Command	//in file /src/Phinx/Console/Command/Init.php:31
    use Symfony\Component\Console\Command\Command	//in file /src/Phinx/Console/Command/AbstractCommand.php:32


.. _case-no-array\_merge()-in-loops:

No array_merge() In Loops
#########################

.. _case-tine20-performances-arraymergeinloops:

Tine20
++++++


:ref:`no-array\_merge()-in-loops`, in tine20/Tinebase/User/Ldap.php:670. 

Classic example of array_merge() in loop : here, the attributures should be collected in a local variable, and then merged in one operation, at the end. That includes the attributes provided before the loop, and the array provided after the loop. 
Note that the order of merge will be the same when merging than when collecting the arrays.

.. code-block:: php

    $attributes = array_values($this->_rowNameMapping);
            foreach ($this->_ldapPlugins as $plugin) {
                $attributes = array_merge($attributes, $plugin->getSupportedAttributes());
            }
    
            $attributes = array_merge($attributes, $this->_additionalLdapAttributesToFetch);


.. _case-pre-increment:

Pre-increment
#############

.. _case-expressionengine-performances-prepostincrement:

ExpressionEngine
++++++++++++++++


:ref:`pre-increment`, in system/ee/EllisLab/ExpressionEngine/Controller/Utilities/Communicate.php:650. 

Using preincrement in for() loops is safe and straightforward. 

.. code-block:: php

    for ($x = 0; $x < $number_to_send; $x++)
    		{
    			$email_address = array_shift($recipient_array);
    
    			if ( ! $this->deliverEmail($email, $email_address))
    			{
    				$email->delete();
    
    				$debug_msg = ee()->email->print_debugger(array());
    
    				show_error(lang('error_sending_email').BR.BR.$debug_msg);
    			}
    			$email->total_sent++;
    		}


.. _case-traq-performances-prepostincrement:

Traq
++++


:ref:`pre-increment`, in src/Controllers/Tickets.php:84. 

$this->currentProject->next_ticket_id value is ignored by the code. It may be turned into a preincrement.

.. code-block:: php

    TimelineModel::newTicketEvent($this->currentUser, $ticket)->save();
    
                $this->currentProject->next_ticket_id++;
                $this->currentProject->save();


.. _case-strpos()-too-much:

strpos() Too Much
#################

.. _case-wordpress-performances-strpostoomuch:

WordPress
+++++++++


:ref:`strpos()-too-much`, in core/traits/Request/Server.php:127. 

Instead of searching for ``HTTP_``, it is faster to compare the first 5 chars to the literal ``HTTP_``. In case of absence, this solution returns faster.

.. code-block:: php

    if (strpos($header, 'HTTP_') === 0) {
    				$header = substr($header, 5);
    			} elseif (strpos($header, 'CONTENT_') !== 0) {
    				continue;
    			}


.. _case-assign-with-and-precedence:

Assign With And Precedence
##########################

.. _case-xataface-php-assignand:

xataface
++++++++


:ref:`assign-with-and-precedence`, in Dataface/LanguageTool.php:265. 

The usage of 'and' here is a workaround for PHP version that have no support for the coalesce. $autosubmit receives the value of $params['autosubmit'] only if the latter is set. Yet, with = having higher precedence over 'and', $autosubmit is mistaken with the existence of $params['autosubmit'] : its value is actually omitted.

.. code-block:: php

    $autosubmit = isset($params['autosubmit']) and $params['autosubmit'];


.. _case-use-random\_int():

Use random_int()
################

.. _case-thelia-php-betterrand:

Thelia
++++++


:ref:`use-random\_int()`, in core/lib/Thelia/Tools/TokenProvider.php:151. 

The whole function may be replaced by random_int(), as it generates random tokens. This needs an extra layer of hashing, to get a long and string results. 

.. code-block:: php

    /**
         * @return string
         */
        protected static function getComplexRandom()
        {
            $firstValue = (float) (mt_rand(1, 0xFFFF) * rand(1, 0x10001));
            $secondValues = (float) (rand(1, 0xFFFF) * mt_rand(1, 0x10001));
    
            return microtime() . ceil($firstValue / $secondValues) . uniqid();
        }


.. _case-fuelcms-php-betterrand:

FuelCMS
+++++++


:ref:`use-random\_int()`, in fuel/modules/fuel/libraries/Fuel.php:235. 

Security tokens should be build with a CSPRNG source. uniqid() is based on time, and though it changes anytime (sic), it is easy to guess. Those days, it looks like '5b1262e74dbb9'; 

.. code-block:: php

    $this->installer->change_config('config', '$config[\'encryption_key\'] = \'\';', '$config[\'encryption_key\'] = \''.md5(uniqid()).'\';');


.. _case-deprecated-php-functions:

Deprecated PHP Functions
########################

.. _case-dolphin-php-deprecated:

Dolphin
+++++++


:ref:`deprecated-php-functions`, in Dolphin-v.7.3.5/inc/classes/BxDolAdminSettings.php:270. 

Split() was abandonned in PHP 7.0

.. code-block:: php

    split(',', $aItem['extra']);


.. _case-wrong-fopen()-mode:

Wrong fopen() Mode
##################

.. _case-tikiwiki-php-fopenmode:

Tikiwiki
++++++++


:ref:`wrong-fopen()-mode`, in lib/tikilib.php:6777. 

This fopen() mode doesn't exists. Use 'w' instead.

.. code-block:: php

    fopen('php://temp', 'rw');


.. _case-humo-gen-php-fopenmode:

HuMo-Gen
++++++++


:ref:`wrong-fopen()-mode`, in include/phprtflite/lib/PHPRtfLite/StreamOutput.php:77. 

This fopen() mode doesn't exists. Use 'w' instead.

.. code-block:: php

    fopen($this->_filename, 'wr', false)


.. _case-incompilable-files:

Incompilable Files
##################

.. _case-xataface-php-incompilable:

xataface
++++++++


:ref:`incompilable-files`, in lib/XML/Tree.php:289. 

Compilation error with PHP 7.2 version.

.. code-block:: php

    syntax error, unexpected 'new' (T_NEW)


.. _case-wrong-parameter-type:

Wrong Parameter Type
####################

.. _case-zencart-php-internalparametertype:

Zencart
+++++++


:ref:`wrong-parameter-type`, in admin/includes/header.php:180. 

setlocale() may be called with null or '' (empty string), and will set values from the environment. When called with "0" (the string), it only reports the current setting. Using an integer is probably undocumented behavior, and falls back to the zero string. 

.. code-block:: php

    $loc = setlocale(LC_TIME, 0);
            if ($loc !== FALSE) echo ' - ' . $loc; //what is the locale in use?


.. _case-logical-should-use-symbolic-operators:

Logical Should Use Symbolic Operators
#####################################

.. _case-cleverstyle-php-logicalinletters:

Cleverstyle
+++++++++++


:ref:`logical-should-use-symbolic-operators`, in modules/Uploader/Mime/Mime.php:171. 

$extension is assigned with the results of pathinfo($reference_name, PATHINFO_EXTENSION) and ignores static::hasExtension($extension). The same expression, placed in a condition (like an if), would assign a value to $extension and use another for the condition itself. Here, this code is only an expression in the flow.

.. code-block:: php

    $extension = pathinfo($reference_name, PATHINFO_EXTENSION) and static::hasExtension($extension);


.. _case-openconf-php-logicalinletters:

OpenConf
++++++++


:ref:`logical-should-use-symbolic-operators`, in chair/export.inc:143. 

In this context, the priority of execution is used on purpose; $coreFile only collect the temporary name of the export file, and when this name is empty, then the second operand of OR is executed, though never collected. Since this second argument is a 'die', its return value is lost, but the initial assignation is never used anyway. 

.. code-block:: php

    $coreFile = tempnam('/tmp/', 'ocexport') or die('could not generate Excel file (6)')


.. _case-possible-missing-subpattern:

Possible Missing Subpattern
###########################

.. _case-phpmyadmin-php-missingsubpattern:

phpMyAdmin
++++++++++


:ref:`possible-missing-subpattern`, in libraries/classes/Advisor.php:557. 

The last capturing subpattern is ``( \[(.*)\])?`` and it is optional. Indeed, when the pattern succeed, the captured values are stored in ``$match``. Yet, the code checks for the existence of ``$match[3]`` before using it.

.. code-block:: php

    if (preg_match("/rule\s'(.*)'( \[(.*)\])?$/", $line, $match)) {
                        $ruleLine = 1;
                        $ruleNo++;
                        $rules[$ruleNo] = ['name' => $match[1]];
                        $lines[$ruleNo] = ['name' => $i + 1];
                        if (isset($match[3])) {
                            $rules[$ruleNo]['precondition'] = $match[3];
                            $lines[$ruleNo]['precondition'] = $i + 1;
                        }


.. _case-spip-php-missingsubpattern:

SPIP
++++


:ref:`possible-missing-subpattern`, in ecrire/inc/filtres_dates.php:73. 

This code avoid the PHP notice by padding the resulting array (see comment in French : eviter === avoid)

.. code-block:: php

    if (preg_match("#^([12][0-9]{3}[-/][01]?[0-9])([-/]00)?( [-0-9:]+)?$#", $date, $regs)) {
    				$regs = array_pad($regs, 4, null); // eviter notice php
    				$date = preg_replace("@/@", "-", $regs[1]) . "-00" . $regs[3];
    			} else {
    				$date = date("Y-m-d H:i:s", strtotime($date));
    			}


.. _case-no-class-in-global:

No Class In Global
##################

.. _case-dolphin-php-noclassinglobal:

Dolphin
+++++++


:ref:`no-class-in-global`, in Dolphin-v.7.3.5/inc/classes/BxDolXml.php:10. 

This class should be put away in a 'dolphin' or 'boonex' namespace.

.. code-block:: php

    class BxDolXml { 
        /* class BxDolXML code */ 
    }


.. _case-no-reference-for-ternary:

No Reference For Ternary
########################

.. _case-phpadsnew-php-noreferenceforternary:

phpadsnew
+++++++++


:ref:`no-reference-for-ternary`, in lib/OA/Admin/Menu/Section.php334:334. 

The reference should be removed from the function definition. Either this method returns null, which is never a reference, or it returns $this, which is always a reference, or the results of a methodcall. The latter may or may not be a reference, but the Ternary operator will drop it and return by value. 

.. code-block:: php

    function &getParentOrSelf($type)
    	{
            if ($this->type == $type) {
                return $this;
            }
            else {
                return $this->parentSection != null ? $this->parentSection->getParentOrSelf($type) : null;
            }
    	}


.. _case-should-use-coalesce:

Should Use Coalesce
###################

.. _case-churchcrm-php-shouldusecoalesce:

ChurchCRM
+++++++++


:ref:`should-use-coalesce`, in src/ChurchCRM/Service/FinancialService.php:597. 

ChurchCRM features 5 old style ternary operators, which are all in this SQL query. ChurchCRM requires PHP 7.0, so a simple code review could remove them all.

.. code-block:: php

    $sSQL = "INSERT INTO pledge_plg
                        (plg_famID,
                        plg_FYID, 
                        plg_date, 
                        plg_amount,
                        plg_schedule, 
                        plg_method, 
                        plg_comment, 
                        plg_DateLastEdited, 
                        plg_EditedBy, 
                        plg_PledgeOrPayment, 
                        plg_fundID, 
                        plg_depID, 
                        plg_CheckNo, 
                        plg_scanString, 
                        plg_aut_ID, 
                        plg_NonDeductible, 
                        plg_GroupKey)
                        VALUES ('".
              $payment->FamilyID."','".
              $payment->FYID."','".
              $payment->Date."','".
              $Fund->Amount."','".
              (isset($payment->schedule) ? $payment->schedule : 'NULL')."','".
              $payment->iMethod."','".
              $Fund->Comment."','".
              date('YmdHis')."',".
              $_SESSION['user']->getId().",'".
              $payment->type."',".
              $Fund->FundID.','.
              $payment->DepositID.','.
              (isset($payment->iCheckNo) ? $payment->iCheckNo : 'NULL').",'".
              (isset($payment->tScanString) ? $payment->tScanString : 'NULL')."','".
              (isset($payment->iAutID) ? $payment->iAutID : 'NULL')."','".
              (isset($Fund->NonDeductible) ? $Fund->NonDeductible : 'NULL')."','".
              $sGroupKey."')";


.. _case-cleverstyle-php-shouldusecoalesce:

Cleverstyle
+++++++++++


:ref:`should-use-coalesce`, in modules/Feedback/index.php:37. 

Cleverstyle nests ternary operators when selecting default values. Here, moving some of them to ?? will reduce the code complexity and make it more readable. Cleverstyle requires PHP 7.0 or more recent.

.. code-block:: php

    $Page->content(
    	h::{'cs-form form'}(
    		h::{'section.cs-feedback-form article'}(
    			h::{'header h2.cs-text-center'}($L->Feedback).
    			h::{'table.cs-table[center] tr| td'}(
    				[
    					h::{'cs-input-text input[name=name][required]'}(
    						[
    							'placeholder' => $L->feedback_name,
    							'value'       => $User->user() ? $User->username() : (isset($_POST['name']) ? $_POST['name'] : '')
    						]
    					),
    					h::{'cs-input-text input[type=email][name=email][required]'}(
    						[
    							'placeholder' => $L->feedback_email,
    							'value'       => $User->user() ? $User->email : (isset($_POST['email']) ? $_POST['email'] : '')
    						]
    					),
    					h::{'cs-textarea[autosize] textarea[name=text][required]'}(
    						[
    							'placeholder' => $L->feedback_text,
    							'value'       => isset($_POST['text']) ? $_POST['text'] : ''
    						]
    					),
    					h::{'cs-button button[type=submit]'}($L->feedback_send)
    				]
    			)
    		)
    	)
    );


.. _case-strtr-arguments:

Strtr Arguments
###############

.. _case-suitecrm-php-strtrarguments:

SuiteCrm
++++++++


:ref:`strtr-arguments`, in includes/vCard.php:221. 

This code prepares incoming '$values' for extraction. The keys are cleaned then split with explode(). The '=' sign would stay, as strtr() can't remove it. This means that such keys won't be recognized later in the code, and gets omitted.

.. code-block:: php

    $values = explode(';', $value);
                        $key = strtoupper($keyvalue[0]);
                        $key = strtr($key, '=', '');
                        $key = strtr($key, ',', ';');
                        $keys = explode(';', $key);


.. _case-use-php-object-api:

Use PHP Object API
##################

.. _case-wordpress-php-useobjectapi:

WordPress
+++++++++


:ref:`use-php-object-api`, in wp-includes/functions.php:2558. 

Finfo has also a class, with the same name.

.. code-block:: php

    finfo_open(FILEINFO_MIME_TYPE)


.. _case-prestashop-php-useobjectapi:

PrestaShop
++++++++++


:ref:`use-php-object-api`, in admin-dev/filemanager/include/utils.php:174. 

transliterator_transliterate() has also a class named Transliterator

.. code-block:: php

    transliterator_transliterate('Accents-Any', $str)


.. _case-use-pathinfo:

Use Pathinfo
############

.. _case-suitecrm-php-usepathinfo:

SuiteCrm
++++++++


:ref:`use-pathinfo`, in include/utils/file_utils.php:441. 

Looking for the extension ? Use pathinfo() and PATHINFO_EXTENSION 

.. code-block:: php

    $exp = explode('.', $filename);


.. _case-don't-echo-error:

Don't Echo Error
################

.. _case-churchcrm-security-dontechoerror:

ChurchCRM
+++++++++


:ref:`don't-echo-error`, in wp-admin/includes/misc.php:74. 

This is classic debugging code that should never reach production. mysqli_error() and mysqli_errno() provide valuable information is case of an error, and may be exploited by intruders.

.. code-block:: php

    if (mysqli_error($cnInfoCentral) != '') {
            echo gettext('An error occured: ').mysqli_errno($cnInfoCentral).'--'.mysqli_error($cnInfoCentral);
        } else {


.. _case-phpdocumentor-security-dontechoerror:

Phpdocumentor
+++++++++++++


:ref:`don't-echo-error`, in src/phpDocumentor/Plugin/Graphs/Writer/Graph.php:77. 

Default development behavior : display the caught exception. Production behavior should not display that message, but log it for later review. Also, the return in the catch should be moved to the main code sequence.

.. code-block:: php

    public function processClass(ProjectDescriptor $project, Transformation $transformation)
        {
            try {
                $this->checkIfGraphVizIsInstalled();
            } catch (\Exception $e) {
                echo $e->getMessage();
    
                return;
            }


.. _case-should-use-prepared-statement:

Should Use Prepared Statement
#############################

.. _case-dolibarr-security-shouldusepreparedstatement:

Dolibarr
++++++++


:ref:`should-use-prepared-statement`, in htdocs/product/admin/price_rules.php:76. 

This code is well escaped, as the integer type cast will prevent any special chars to be used. Here, a prepared statement would apply a modern approach to securing this query.

.. code-block:: php

    $db->query("DELETE FROM " . MAIN_DB_PREFIX . "product_pricerules WHERE level = " . (int) $i)


.. _case-adding-zero:

Adding Zero
###########

.. _case-thelia-structures-addzero:

Thelia
++++++


:ref:`adding-zero`, in core/lib/Thelia/Model/Map/ProfileResourceTableMap.php:250. 

This return statement is doing quite a lot, including a buried '0 + $offset'. This call is probably an echo to '1 + $offset', which is a little later in the expression.

.. code-block:: php

    return serialize(array((string) $row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('ProfileId', TableMap::TYPE_PHPNAME, $indexType)], (string) $row[TableMap::TYPE_NUM == $indexType ? 1 + $offset : static::translateFieldName('ResourceId', TableMap::TYPE_PHPNAME, $indexType)]));


.. _case-openemr-structures-addzero:

OpenEMR
+++++++


:ref:`adding-zero`, in interface/forms/fee_sheet/new.php:466:534. 

$main_provid is filtered as an integer. $main_supid is then filtered twice : one with the sufficent (int) and then, added with 0.

.. code-block:: php

    if (!$alertmsg && ($_POST['bn_save'] || $_POST['bn_save_close'] || $_POST['bn_save_stay'])) {
        $main_provid = 0 + $_POST['ProviderID'];
        $main_supid  = 0 + (int)$_POST['SupervisorID'];
        //.....


.. _case-altering-foreach-without-reference:

Altering Foreach Without Reference
##################################

.. _case-contao-structures-alteringforeachwithoutreference:

Contao
++++++


:ref:`altering-foreach-without-reference`, in core-bundle/src/Resources/contao/classes/Theme.php:613. 

$tmp[$kk] is &$vv.

.. code-block:: php

    foreach ($tmp as $kk=>$vv)
    								{
    									// Do not use the FilesModel here – tables are locked!
    									$objFile = $this->Database->prepare(SELECT uuid FROM tl_files WHERE path=?)
    															  ->limit(1)
    															  ->execute($this->customizeUploadPath($vv));
    
    									$tmp[$kk] = $objFile->uuid;
    								}


.. _case-wordpress-structures-alteringforeachwithoutreference:

WordPress
+++++++++


:ref:`altering-foreach-without-reference`, in wp-admin/includes/misc.php:74. 

$ids[$index] is &$rrid. 

.. code-block:: php

    foreach($ids as $index => $rrid)
                    {
                        if($rrid == $this->Id)
                        {
                            $ids[$index] = $_id;
                            $write = true;
                            break;
                        }
                    }


.. _case-strict-comparison-with-booleans:

Strict Comparison With Booleans
###############################

.. _case-phinx-structures-booleanstrictcomparison:

Phinx
+++++


:ref:`strict-comparison-with-booleans`, in src/Phinx/Db/Adapter/MysqlAdapter.php:1131. 

`ìsNull( )`` always returns a boolean : it may be only be ``true`` or ``false``. Until typehinted properties or return typehint are used, isNull() may return anything else. 

.. code-block:: php

    $column->isNull( ) == false


.. _case-typo3-structures-booleanstrictcomparison:

Typo3
+++++


:ref:`strict-comparison-with-booleans`, in typo3/sysext/lowlevel/Classes/Command/FilesWithMultipleReferencesCommand.php:90. 

When ``dry-run`` is not defined, the getOption() method actually returns a ``null`` value. So, comparing the result of getOption() to false is actually wrong : using a constant to prevent values to be inconsistent is recommended here.

.. code-block:: php

    $input->getOption('dry-run') != false


.. _case-check-json:

Check JSON
##########

.. _case-woocommerce-structures-checkjson:

Woocommerce
+++++++++++


:ref:`check-json`, in includes/admin/helper/class-wc-helper-plugin-info.php:66. 

In case the body is an empty string, this will be correctly decoded, but will yield an object with an empty-named property.

.. code-block:: php

    $results = json_decode( wp_remote_retrieve_body( $request ), true );
    		if ( ! empty( $results ) ) {
    			$response = (object) $results;
    		}
    
    		return $response;


.. _case-could-use-\_\_dir\_\_:

Could Use __DIR__
#################

.. _case-woocommerce-structures-couldusedir:

Woocommerce
+++++++++++


:ref:`could-use-\_\_dir\_\_`, in includes/class-wc-api.php:162. 

All the 120 occurrences use `dirname( __FILE__ )`, and could be upgraded to __DIR__ if backward compatibility to PHP 5.2 is not critical. 

.. code-block:: php

    private function rest_api_includes() {
    		// Exception handler.
    		include_once dirname( __FILE__ ) . '/api/class-wc-rest-exception.php';
    
    		// Authentication.
    		include_once dirname( __FILE__ ) . '/api/class-wc-rest-authentication.php';


.. _case-piwigo-structures-couldusedir:

Piwigo
++++++


:ref:`could-use-\_\_dir\_\_`, in include/random_compat/random.php:50. 

`dirname( __FILE__ )` is cached into $RandomCompatDIR, then reused three times. Using __DIR__ would save that detour.

.. code-block:: php

    $RandomCompatDIR = dirname(__FILE__);
    
        require_once $RandomCompatDIR.'/byte_safe_strings.php';
        require_once $RandomCompatDIR.'/cast_to_int.php';
        require_once $RandomCompatDIR.'/error_polyfill.php';


.. _case-could-use-short-assignation:

Could Use Short Assignation
###########################

.. _case-churchcrm-structures-coulduseshortassignation:

ChurchCRM
+++++++++


:ref:`could-use-short-assignation`, in src/ChurchCRM/utils/GeoUtils.php:74. 

Sometimes, the variable is on the other side of the operator.

.. code-block:: php

    $distance = 0.6213712 * $distance;


.. _case-thelia-structures-coulduseshortassignation:

Thelia
++++++


:ref:`could-use-short-assignation`, in local/modules/Tinymce/Resources/js/tinymce/filemanager/include/utils.php:70. 

/= is rare, but it definitely could be used here.

.. code-block:: php

    $size = $size / 1024;


.. _case-could-use-str\_repeat():

Could Use str_repeat()
######################

.. _case-zencart-structures-couldusestrrepeat:

Zencart
+++++++


:ref:`could-use-str\_repeat()`, in includes/functions/functions_general.php:1234. 

That's a 45 repeat of &nbsp;

.. code-block:: php

    if ( (!zen_browser_detect('MSIE')) && (zen_browser_detect('Mozilla/4')) ) {
          for ($i=0; $i<45; $i++) $pre .= '&nbsp;';
        }


.. _case-dangling-array-references:

Dangling Array References
#########################

.. _case-typo3-structures-danglingarrayreferences:

Typo3
+++++


:ref:`dangling-array-references`, in typo3/sysext/impexp/Classes/ImportExport.php:322. 

foreach() reads $lines into $r, and augment those lines. By the end, the $r variable is not unset. Yet, several lines later, in the same method but with different conditions, another loop reuse the variable $r. If is_array($this->dat['header']['pagetree'] and is_array($this->remainHeader['records']) are arrays at the same moment, then both loops are called, and they share the same reference. Values of the latter array will end up in the formar. 

.. code-block:: php

    if (is_array($this->dat['header']['pagetree'])) {
        reset($this->dat['header']['pagetree']);
        $lines = [];
        $this->traversePageTree($this->dat['header']['pagetree'], $lines);
    
        $viewData['dat'] = $this->dat;
        $viewData['update'] = $this->update;
        $viewData['showDiff'] = $this->showDiff;
        if (!empty($lines)) {
            foreach ($lines as &$r) {
                $r['controls'] = $this->renderControls($r);
                $r['fileSize'] = GeneralUtility::formatSize($r['size']);
                $r['message'] = ($r['msg'] && !$this->doesImport ? '<span class=text-danger>' . htmlspecialchars($r['msg']) . '</span>' : '');
            }
            $viewData['pagetreeLines'] = $lines;
        } else {
            $viewData['pagetreeLines'] = [];
        }
    }
    // Print remaining records that were not contained inside the page tree:
    if (is_array($this->remainHeader['records'])) {
        $lines = [];
        if (is_array($this->remainHeader['records']['pages'])) {
            $this->traversePageRecords($this->remainHeader['records']['pages'], $lines);
        }
        $this->traverseAllRecords($this->remainHeader['records'], $lines);
        if (!empty($lines)) {
            foreach ($lines as &$r) {
                $r['controls'] = $this->renderControls($r);
                $r['fileSize'] = GeneralUtility::formatSize($r['size']);
                $r['message'] = ($r['msg'] && !$this->doesImport ? '<span class=text-danger>' . htmlspecialchars($r['msg']) . '</span>' : '');
            }
            $viewData['remainingRecords'] = $lines;
        }
    }


.. _case-sugarcrm-structures-danglingarrayreferences:

SugarCrm
++++++++


:ref:`dangling-array-references`, in SugarCE-Full-6.5.26/modules/Import/CsvAutoDetect.php:165. 

There are two nested foreach here : they both have referenced blind variables. The second one uses $data, but never changes it. Yet, it is reused the next round in the first loop, leading to pollution from the first rows of $this->_parser->data into the lasts. This may happen even if $data is not modified explicitely : in fact, it will be modified the next call to foreach($row as ...), for each element in $row. 

.. code-block:: php

    foreach ($this->_parser->data as &$row) {
        foreach ($row as &$data) {
            $len = strlen($data);
            // check if it begins and ends with single quotes
            // if it does, then it double quotes may not be the enclosure
            if ($len>=2 && $data[0] == " && $data[$len-1] == ") {
                $beginEndWithSingle = true;
                break;
            }
        }
        if ($beginEndWithSingle) {
            break;
        }
        $depth++;
        if ($depth > $this->_max_depth) {
            break;
        }
    }


.. _case-\_\_dir\_\_-then-slash:

__DIR__ Then Slash
##################

.. _case-traq-structures-dirthenslash:

Traq
++++


:ref:`\_\_dir\_\_-then-slash`, in src/Kernel.php:60. 

When executed in a path '/a/b/c', this code will require '/a../../vendor/autoload.php.

.. code-block:: php

    static::$loader = require __DIR__.'../../vendor/autoload.php';


.. _case-else-if-versus-elseif:

Else If Versus Elseif
#####################

.. _case-teampass-structures-elseifelseif:

TeamPass
++++++++


:ref:`else-if-versus-elseif`, in items.php:819. 

This code could be turned into a switch() structure.

.. code-block:: php

    if ($field[3] === 'text') {
                    echo '
                            <input type=text id=edit_field_.$field[0]._.$elem[0]. class=edit_item_field input_text text ui-widget-content ui-corner-all size=40 data-field-type=.$field[3]. data-field-masked=.$field[4]. data-field-is-mandatory=.$field[5]. data-template-id=.$templateID.>';
                } else if ($field[3] === 'textarea') {
                    echo '
                            <textarea id=edit_field_.$field[0]._.$elem[0]. class=edit_item_field input_text text ui-widget-content ui-corner-all colums=40 rows=5 data-field-type=.$field["3"]. data-field-masked=.$field[4]. data-field-is-mandatory=.$field[5]. data-template-id=.$templateID.></textarea>';
                }


.. _case-phpdocumentor-structures-elseifelseif:

Phpdocumentor
+++++++++++++


:ref:`else-if-versus-elseif`, in src/phpDocumentor/Plugin/Core/Transformer/Writer/Xsl.php:112. 

The first then block is long and complex. The else block, on the other hand, only contains a single if/then/else. Both conditions are distinct at first sight, so a if / elseif / then structure would be the best.

.. code-block:: php

    if ($transformation->getQuery() !== '') {
    /** Long then block **/
            } else {
                if (substr($transformation->getArtifact(), 0, 1) == '$') {
                    // not a file, it must become a variable!
                    $variable_name = substr($transformation->getArtifact(), 1);
                    $this->xsl_variables[$variable_name] = $proc->transformToXml($structure);
                } else {
                    $relativeFileName = substr($artifact, strlen($transformation->getTransformer()->getTarget()) + 1);
                    $proc->setParameter('', 'root', str_repeat('../', substr_count($relativeFileName, '/')));
    
                    $this->writeToFile($artifact, $proc, $structure);
                }
            }


.. _case-empty-blocks:

Empty Blocks
############

.. _case-cleverstyle-structures-emptyblocks:

Cleverstyle
+++++++++++


:ref:`empty-blocks`, in modules/Blogs/api/Controller.php:44. 

Else is empty, but commented. 

.. code-block:: php

    public static function posts_get ($Request) {
    		$id = $Request->route_ids(0);
    		if ($id) {
    			$post = Posts::instance()->get($id);
    			if (!$post) {
    				throw new ExitException(404);
    			}
    			return $post;
    		} else {
    			// TODO: implement latest posts
    		}
    	}


.. _case-phpipam-structures-emptyblocks:

PhpIPAM
+++++++


:ref:`empty-blocks`, in wp-admin/includes/misc.php:74. 

The ``then`` block is empty and commented : yet, it may have been clearer to make the condition != and omitted the whole empty block.

.. code-block:: php

    /* checks */
    if($_POST['action'] == delete) {
    	# no cecks
    }
    else {
    	# remove spaces
    	$_POST['name'] = trim($_POST['name']);
    
    	# length > 4 and < 12
    	if( (mb_strlen($_POST['name']) < 2) || (mb_strlen($_POST['name']) > 24) ) 	{ $errors[] = _('Name must be between 4 and 24 characters'); }


.. _case-error\_reporting()-with-integers:

error_reporting() With Integers
###############################

.. _case-sugarcrm-structures-errorreportingwithinteger:

SugarCrm
++++++++


:ref:`error\_reporting()-with-integers`, in modules/UpgradeWizard/silentUpgrade_step1.php:436. 

This only displays E_ERROR, the highest level of error reporting. It should be checked, as it happens in the 'silentUpgrade' script. 

.. code-block:: php

    ini_set('error_reporting', 1);


.. _case-eval()-usage:

Eval() Usage
############

.. _case-xoops-structures-evalusage:

XOOPS
+++++


:ref:`eval()-usage`, in htdocs/modules/system/class/block.php:266. 

eval() execute code that was arbitrarily stored in $this, in one of the properties. Then, it is sent to output, but collected before reaching the browser, and put again in $content. May be the echo/ob_get_contents() could have been skipped.

.. code-block:: php

    ob_start();
                        echo eval($this->getVar('content', 'n'));
                        $content = ob_get_contents();
                        ob_end_clean();


.. _case-mautic-structures-evalusage:

Mautic
++++++


:ref:`eval()-usage`, in app/bundles/InstallBundle/Configurator/Step/CheckStep.php:238. 

create_function() is actually an eval() in disguise : replace it with a closure for code modernization

.. code-block:: php

    create_function('$cfgValue', 'return $cfgValue > 100;')


.. _case-eval()-without-try:

eval() Without Try
##################

.. _case-fuelcms-structures-evalwithouttry:

FuelCMS
+++++++


:ref:`eval()-without-try`, in fuel/modules/fuel/controllers/Blocks.php:268. 

The @ will prevent any error, while the try/catch allows the processing of certain types of error, namely the Fatal ones. 

.. code-block:: php

    @eval($_name_var_eval)


.. _case-expressionengine-structures-evalwithouttry:

ExpressionEngine
++++++++++++++++


:ref:`eval()-without-try`, in system/ee/EllisLab/Addons/member/mod.member_memberlist.php:637. 

$cond is build from values extracted from the $fields array. Although it is probably reasonably safe, a try/catch here will collect any unexpected situation cleaningly.

.. code-block:: php

    elseif (isset($fields[$val['3']]))
    					{
    						if (array_key_exists('m_field_id_'.$fields[$val['3']], $row))
    						{
    							$v = $row['m_field_id_'.$fields[$val['3']]];
    
    							$lcond = str_replace($val['3'], "$v", $lcond);
    							$cond = $lcond.' '.$rcond;
    							$cond = str_replace("\|", "|", $cond);
    
    							eval("$result = ".$cond.";");


.. _case-exit()-usage:

Exit() Usage
############

.. _case-traq-structures-exitusage:

Traq
++++


:ref:`exit()-usage`, in src/Controllers/attachments.php:75. 

This acts as a view. The final 'exit' is meant to ensure that no other piece of data is emitted, potentially polluting the view. This also prevent any code cleaning to happen.

.. code-block:: php

    /**
         * View attachment page
         *
         * @param integer $attachment_id
         */
        public function action_view($attachment_id)
        {
            // Don't try to load a view
            $this->render['view'] = false;
    
            header(Content-type: {$this->attachment->type});
            $content_type = explode('/', $this->attachment->type);
    
            // Check what type of file we're dealing with.
            if($content_type[0] == 'text' or $content_type[0] == 'image') {
                // If the mime-type is text, we can just display it
                // as plain text. I hate having to download files.
                if ($content_type[0] == 'text') {
                    header(Content-type: text/plain);
                }
                header("Content-Disposition: filename=\"{$this->attachment->name}\"");
            }
            // Anything else should be downloaded
            else {
                header("Content-Disposition: attachment; filename=\"{$this->attachment->name}\"");
            }
    
            // Decode the contents and display it
            print(base64_decode($this->attachment->contents));
            exit;
        }


.. _case-thinkphp-structures-exitusage:

ThinkPHP
++++++++


:ref:`exit()-usage`, in ThinkPHP/Library/Vendor/EaseTemplate/template.core.php:60. 

Here, exit is used as a rudimentary error management. When the version is not correctly provided via EaseTemplateVer, the application stop totally.

.. code-block:: php

    $this->version		= (trim($_GET['EaseTemplateVer']))?die('Ease Templae E3!'):'';


.. _case-failed-substr-comparison:

Failed Substr Comparison
########################

.. _case-zurmo-structures-failingsubstrcomparison:

Zurmo
+++++


:ref:`failed-substr-comparison`, in app/protected/modules/zurmo/modules/SecurableModule.php:117. 

filterAuditEvent compares a six char string with 'AUDIT\_EVENT\_' which contains 10 chars. This method returns only FALSE. Although it is used only once, the whole block that calls this method is now dead code. 

.. code-block:: php

    private static function filterAuditEvent($s)
            {
                return substr($s, 0, 6) == 'AUDIT_EVENT_';
            }


.. _case-mediawiki-structures-failingsubstrcomparison:

MediaWiki
+++++++++


:ref:`failed-substr-comparison`, in includes/media/DjVu.php:263. 

$metadata contains data that may be in different formats. When it is a pure XML file, it is 'Old style'. The comment helps understanding that this is not the modern way to go : the Old Style is actually never called, due to a failing condition.

.. code-block:: php

    private function getUnserializedMetadata( File $file ) {
    		$metadata = $file->getMetadata();
    		if ( substr( $metadata, 0, 3 ) === '<?xml' ) {
    			// Old style. Not serialized but instead just a raw string of XML.
    			return $metadata;
    		}


.. _case-foreach-reference-is-not-modified:

Foreach Reference Is Not Modified
#################################

.. _case-dolibarr-structures-foreachreferenceisnotmodified:

Dolibarr
++++++++


:ref:`foreach-reference-is-not-modified`, in htdocs/product/reassort.php:364. 

$wh is an array, and is read for its index 'id', but it is not modified. The reference sign is too much.

.. code-block:: php

    if($nb_warehouse>1) {
        foreach($warehouses_list as &$wh) {
    
            print '<td class=right>';
            print empty($product->stock_warehouse[$wh['id']]->real) ? '0' : $product->stock_warehouse[$wh['id']]->real;
            print '</td>';
        }
    }


.. _case-vanilla-structures-foreachreferenceisnotmodified:

Vanilla
+++++++


:ref:`foreach-reference-is-not-modified`, in applications/vanilla/models/class.discussionmodel.php:944. 

$discussion is also an object : it doesn't need any reference to be modified. And, it is not modified, but only read.

.. code-block:: php

    foreach ($result as $key => &$discussion) {
        if (isset($this->_AnnouncementIDs)) {
            if (in_array($discussion->DiscussionID, $this->_AnnouncementIDs)) {
                unset($result[$key]);
                $unset = true;
            }
        } elseif ($discussion->Announce && $discussion->Dismissed == 0) {
            // Unset discussions that are announced and not dismissed
            unset($result[$key]);
            $unset = true;
        }
    }


.. _case-identical-conditions:

Identical Conditions
####################

.. _case-wordpress-structures-identicalconditions:

WordPress
+++++++++


:ref:`identical-conditions`, in wp-admin/theme-editor.php:247. 

The condition checks first if $has_templates or $theme->parent(), and one of the two is sufficient to be valid. Then, it checks again that $theme->parent() is activated with &&. This condition may be reduced by calling $theme->parent(), as $has_template is unused here.

.. code-block:: php

    <?php if ( ( $has_templates || $theme->parent() ) && $theme->parent() ) : ?>


.. _case-dolibarr-structures-identicalconditions:

Dolibarr
++++++++


:ref:`identical-conditions`, in htdocs/core/lib/files.lib.php:2052. 

Better check twice that $modulepart is really 'apercusupplier_invoice'.

.. code-block:: php

    $modulepart == 'apercusupplier_invoice' || $modulepart == 'apercusupplier_invoice'


.. _case-identical-on-both-sides:

Identical On Both Sides
#######################

.. _case-phpmyadmin-structures-identicalonbothsides:

phpMyAdmin
++++++++++


:ref:`identical-on-both-sides`, in libraries/classes/DatabaseInterface.php:323. 

This code looks like ``($options & DatabaseInterface::QUERY_STORE) == DatabaseInterface::QUERY_STORE``, which would make sense. But PHP precedence is actually executing ``$options & (DatabaseInterface::QUERY_STORE == DatabaseInterface::QUERY_STORE)``, which then doesn't depends on QUERY_STORE but only on $options.

.. code-block:: php

    if ($options & DatabaseInterface::QUERY_STORE == DatabaseInterface::QUERY_STORE) {
        $tmp = $this->_extension->realQuery('
            SHOW COUNT(*) WARNINGS', $this->_links[$link], DatabaseInterface::QUERY_STORE
        );
        $warnings = $this->fetchRow($tmp);
    } else {
        $warnings = 0;
    }


.. _case-humo-gen-structures-identicalonbothsides:

HuMo-Gen
++++++++


:ref:`identical-on-both-sides`, in include/person_cls.php:73. 

In that long logical expression, $personDb->pers_cal_date is tested twice

.. code-block:: php

    // *** Filter person's WITHOUT any date's ***
    			if ($user[group_filter_date]=='j'){
    				if ($personDb->pers_birth_date=='' AND $personDb->pers_bapt_date==''
    				AND $personDb->pers_death_date=='' AND $personDb->pers_buried_date==''
    				AND $personDb->pers_cal_date=='' AND $personDb->pers_cal_date==''
    				){
    					$privacy_person='';
    				}
    			}


.. _case-if-with-same-conditions:

If With Same Conditions
#######################

.. _case-phpmyadmin-structures-ifwithsameconditions:

phpMyAdmin
++++++++++


:ref:`if-with-same-conditions`, in libraries/classes/Response.php:345. 

The first test on $this->_isSuccess settles the situation with _JSON. Then, a second check is made. Both could be merged, also the second one is fairly long (not shown). 

.. code-block:: php

    if ($this->_isSuccess) {
                $this->_JSON['success'] = true;
            } else {
                $this->_JSON['success'] = false;
                $this->_JSON['error']   = $this->_JSON['message'];
                unset($this->_JSON['message']);
            }
    
            if ($this->_isSuccess) {


.. _case-phpdocumentor-structures-ifwithsameconditions:

Phpdocumentor
+++++++++++++


:ref:`if-with-same-conditions`, in src/phpDocumentor/Transformer/Command/Project/TransformCommand.php:239. 

$templates is extracted from $input. If it is empty, a second source is polled. Finally, if nothing has worked, a default value is used ('clean'). In this case, each attempt is an alternative solution to the previous failing call. The second test could be reported on $templatesFromConfig, and not $templates.

.. code-block:: php

    $templates = $input->getOption('template');
            if (!$templates) {
                /** @var Template[] $templatesFromConfig */
                $templatesFromConfig = $configurationHelper->getConfigValueFromPath('transformations/templates');
                foreach ($templatesFromConfig as $template) {
                    $templates[] = $template->getName();
                }
            }
    
            if (!$templates) {
                $templates = array('clean');
            }


.. _case-indices-are-int-or-string:

Indices Are Int Or String
#########################

.. _case-zencart-structures-indicesareintorstring:

Zencart
+++++++


:ref:`indices-are-int-or-string`, in includes/modules/payment/paypaldp.php:2523. 

All those strings ends up as integers.

.. code-block:: php

    // Build Currency format table
        $curFormat = Array();
        $curFormat[036]=2;
        $curFormat[124]=2;
        $curFormat[203]=2;
        $curFormat[208]=2;
        $curFormat[348]=2;
        $curFormat[392]=0;
        $curFormat[554]=2;
        $curFormat[578]=2;
        $curFormat[702]=2;
        $curFormat[752]=2;
        $curFormat[756]=2;
        $curFormat[826]=2;
        $curFormat[840]=2;
        $curFormat[978]=2;
        $curFormat[985]=2;


.. _case-mautic-structures-indicesareintorstring:

Mautic
++++++


:ref:`indices-are-int-or-string`, in app/bundles/CoreBundle/Entity/CommonRepository.php:315. 

$baseCols has 1 and 0 (respectively) for index.

.. code-block:: php

    foreach ($metadata->getAssociationMappings() as $field => $association) {
                        if (in_array($association['type'], [ClassMetadataInfo::ONE_TO_ONE, ClassMetadataInfo::MANY_TO_ONE])) {
                            $baseCols[true][$entityClass][]  = $association['joinColumns'][0]['name'];
                            $baseCols[false][$entityClass][] = $field;
                        }
                    }


.. _case-invalid-regex:

Invalid Regex
#############

.. _case-sugarcrm-structures-invalidregex:

SugarCrm
++++++++


:ref:`invalid-regex`, in SugarCE-Full-6.5.26/include/utils/file_utils.php:513. 

This yields an error at execution time : ``Compilation failed: invalid range in character class at offset 4 ``.

.. code-block:: php

    preg_replace('/[^\w-._]+/i', '', $name)


.. _case-is-actually-zero:

Is Actually Zero
################

.. _case-dolibarr-structures-iszero:

Dolibarr
++++++++


:ref:`is-actually-zero`, in htdocs/compta/ajaxpayment.php:99. 

Here, the $amountToBreakDown is either $currentRemain or $result. 

.. code-block:: php

    $amountToBreakdown = ($result - $currentRemain >= 0 ?
    										$currentRemain : 								// Remain can be fully paid
    										$currentRemain + ($result - $currentRemain));	// Remain can only partially be paid


.. _case-suitecrm-structures-iszero:

SuiteCrm
++++++++


:ref:`is-actually-zero`, in modules/AOR_Charts/lib/pChart/class/pDraw.class.php:523. 

$Xa may only amount to $iX2, though the expression looks weird.

.. code-block:: php

    if ( $X > $iX2 ) { $Xa = $X-($X-$iX2); $Ya = $iY1+($X-$iX2); } else { $Xa = $X; $Ya = $iY1; }


.. _case-list()-may-omit-variables:

list() May Omit Variables
#########################

.. _case-openconf-structures-listomissions:

OpenConf
++++++++


:ref:`list()-may-omit-variables`, in openconf/author/privacy.php:29. 

The first variable in the list(), $none, isn't reused anywhere in the script. In fact, its name convey the meaning that is it useless, but is in the array nonetheless. 

.. code-block:: php

    list($none, $OC_privacy_policy) = oc_getTemplate('privacy_policy');


.. _case-fuelcms-structures-listomissions:

FuelCMS
+++++++


:ref:`list()-may-omit-variables`, in wp-admin/includes/misc.php:74. 

$a is never reused again. $b, on the other hand is. Not assigning any value to $a saves some memory, and avoid polluting the local variable space. 

.. code-block:: php

    list($b, $a) = array(reset($params->me), key($params->me));


.. _case-logical-mistakes:

Logical Mistakes
################

.. _case-dolibarr-structures-logicalmistakes:

Dolibarr
++++++++


:ref:`logical-mistakes`, in htdocs/core/lib/admin.lib.php:1165. 

This expression is always true. When `$nbtabsql` is `$nbtablib`, the left part is true; When `$nbtabsql` is `$nbtabsqlsort`, the right part is true; When any other value is provided, both operands are true. 

.. code-block:: php

    $nbtablib != $nbtabsql || $nbtabsql != $nbtabsqlsort


.. _case-cleverstyle-structures-logicalmistakes:

Cleverstyle
+++++++++++


:ref:`logical-mistakes`, in modules/HybridAuth/Hybrid/Providers/DigitalOcean.php:123. 

This expression is always false. When `$data->account->email_verified` is `true`, the right part is false; When `$data->account->email_verified` is `$data->account->email`, the right part is false; The only viable solution is to have ` $data->account->email`true : this is may be the intend it, though it is not easy to understand. 

.. code-block:: php

    TRUE == $data->account->email_verified and $data->account->email == $data->account->email_verified


.. _case-lone-blocks:

Lone Blocks
###########

.. _case-thinkphp-structures-loneblock:

ThinkPHP
++++++++


:ref:`lone-blocks`, in ThinkPHP/Library/Vendor/Hprose/HproseReader.php:163. 

There is no need for block in a case/default clause. PHP executes all command in order, until a break or the end of the switch. There is another occurrence of that situation in this code : it seems to be a coding convention, while only applied to a few switch statements.

.. code-block:: php

    for ($i = 0; $i < $len; ++$i) {
                switch (ord($this->stream->getc()) >> 4) {
                    case 0:
                    case 1:
                    case 2:
                    case 3:
                    case 4:
                    case 5:
                    case 6:
                    case 7: {
                        // 0xxx xxxx
                        $utf8len++;
                        break;
                    }
                    case 12:
                    case 13: {
                        // 110x xxxx   10xx xxxx
                        $this->stream->skip(1);
                        $utf8len += 2;
                        break;
                    }


.. _case-tine20-structures-loneblock:

Tine20
++++++


:ref:`lone-blocks`, in tine20/Addressbook/Convert/Contact/VCard/Abstract.php:199. 

A case of empty case, with empty blocks. This is useless code. Event the curly brackets with the final case are useless.

.. code-block:: php

    switch ( $property['TYPE'] ) {
                            case 'JPG' : {}
                            case 'jpg' : {}
                            case 'Jpg' : {}
                            case 'Jpeg' : {}
                            case 'jpeg' : {}
                            case 'PNG' : {}
                            case 'png' : {}
                            case 'JPEG' : {
                                if (Tinebase_Core::isLogLevel(Zend_Log::DEBUG)) 
                                    Tinebase_Core::getLogger()->warn(__METHOD__ . '::' . __LINE__ . ' Photo: passing on invalid ' . $property['TYPE'] . ' image as is (' . strlen($property->getValue()) .')' );
                                $jpegphoto = $property->getValue();
                                break;
                            }


.. _case-multiples-identical-case:

Multiples Identical Case
########################

.. _case-sugarcrm-structures-multipledefinedcase:

SugarCrm
++++++++


:ref:`multiples-identical-case`, in modules/ModuleBuilder/MB/MBPackage.php:439. 

It takes a while to find the double 'required' case, but the executed code is actually the same, so this is dead code at worst. 

.. code-block:: php

    switch ($col) {
        case 'custom_module':
        	$installdefs['custom_fields'][$name]['module'] = $res;
        	break;
        case 'required':
        	$installdefs['custom_fields'][$name]['require_option'] = $res;
        	break;
        case 'vname':
        	$installdefs['custom_fields'][$name]['label'] = $res;
        	break;
        case 'required':
        	$installdefs['custom_fields'][$name]['require_option'] = $res;
        	break;
        case 'massupdate':
        	$installdefs['custom_fields'][$name]['mass_update'] = $res;
        	break;
        case 'comments':
        	$installdefs['custom_fields'][$name]['comments'] = $res;
        	break;
        case 'help':
        	$installdefs['custom_fields'][$name]['help'] = $res;
        	break;
        case 'len':
        	$installdefs['custom_fields'][$name]['max_size'] = $res;
        	break;
        default:
        	$installdefs['custom_fields'][$name][$col] = $res;
    }//switch


.. _case-expressionengine-structures-multipledefinedcase:

ExpressionEngine
++++++++++++++++


:ref:`multiples-identical-case`, in ExpressionEngine_Core2.9.2/system/expressionengine/controllers/cp/admin_content.php:577. 

'deft_status' is doubled, with a fallthrough. This looks like some forgotten copy/paste. 

.. code-block:: php

    switch ($key){
    								case 'cat_group':
    								    //PHP code
    									break;
    								case 'status_group':
    								case 'field_group':
    								    //PHP code
    									break;
    								case 'deft_status':
    								case 'deft_status':
    								    //PHP code
    									break;
    								case 'search_excerpt':
    								    //PHP code
    									break;
    								case 'deft_category':
    								    //PHP code
    									break;
    								case 'blog_url':
    								case 'comment_url':
    								case 'search_results_url':
    								case 'rss_url':
    								    //PHP code
    									break;
    								default :
    								    //PHP code
    									break;
    							}


.. _case-multiply-by-one:

Multiply By One
###############

.. _case-sugarcrm-structures-multiplybyone:

SugarCrm
++++++++


:ref:`multiply-by-one`, in SugarCE-Full-6.5.26/modules/Relationships/views/view.editfields.php:74. 

Here, '$count % 1' is always true, after the first loop of the foreach. There is no need for % usage.

.. code-block:: php

    $count = 0;
            foreach($this->fields as $def)
            {
                if (!empty($def['relationship_field'])) {
                    $label = !empty($def['vname']) ? $def['vname'] : $def['name'];
                    echo <td> . translate($label, $this->module) . :</td>
                       . <td><input id='{$def['name']}' name='{$def['name']}'>  ;
    
                    if ($count%1)
                        echo </tr><tr>;
                    $count++;
                }
            }
            echo </tr></table></form>;


.. _case-edusoho-structures-multiplybyone:

Edusoho
+++++++


:ref:`multiply-by-one`, in wp-admin/includes/misc.php:74. 

1 is useless here, since 24 * 3600 is already an integer. And, of course, a day is not 24 * 3600... at least every day.

.. code-block:: php

    'yesterdayStart' => date('Y-m-d', strtotime(date('Y-m-d', time())) - 1 * 24 * 3600),


.. _case-nested-ternary:

Nested Ternary
##############

.. _case-spip-structures-nestedternary:

SPIP
++++


:ref:`nested-ternary`, in ecrire/inc/utils.php:2648. 

Interesting usage of both if/then, for the flow control, and ternary, for data process. Even on multiple lines, nested ternaries are quite hard to read. 

.. code-block:: php

    // le script de l'espace prive
    	// Mettre a "index.php" si DirectoryIndex ne le fait pas ou pb connexes:
    	// les anciens IIS n'acceptent pas les POST sur ecrire/ (#419)
    	// meme pb sur thttpd cf. http://forum.spip.net/fr_184153.html
    	if (!defined('_SPIP_ECRIRE_SCRIPT')) {
    		define('_SPIP_ECRIRE_SCRIPT', (empty($_SERVER['SERVER_SOFTWARE']) ? '' :
    			preg_match(',IIS|thttpd,', $_SERVER['SERVER_SOFTWARE']) ?
    				'index.php' : ''));
    	}


.. _case-zencart-structures-nestedternary:

Zencart
+++++++


:ref:`nested-ternary`, in app/library/zencart/ListingQueryAndOutput/src/formatters/TabularProduct.php:143. 

No more than one level of nesting for this ternary call, yet it feels a lot more, thanks to the usage of arrayed properties, constants, and functioncalls. 

.. code-block:: php

    $lc_text .= '<br />' . (zen_get_show_product_switch($listing->fields['products_id'], 'ALWAYS_FREE_SHIPPING_IMAGE_SWITCH') ? (zen_get_product_is_always_free_shipping($listing->fields['products_id']) ? TEXT_PRODUCT_FREE_SHIPPING_ICON . '<br />' : '') : '');


.. _case-always-positive-comparison:

Always Positive Comparison
##########################

.. _case-magento-structures-nevernegative:

Magento
+++++++


:ref:`always-positive-comparison`, in app/code/core/Mage/Dataflow/Model/Profile.php:85. 

strlen(($actiosXML) will never be negative, and hence, is always false. This exception is never thrown. 

.. code-block:: php

    if (strlen($actionsXML) < 0 &&
            @simplexml_load_string('<data>' . $actionsXML . '</data>', null, LIBXML_NOERROR) === false) {
                Mage::throwException(Mage::helper('dataflow')->__("Actions XML is not valid."));
            }


.. _case-next-month-trap:

Next Month Trap
###############

.. _case-contao-structures-nextmonthtrap:

Contao
++++++


:ref:`next-month-trap`, in system/modules/calendar/classes/Events.php:515. 

This code is wrong on August 29,th 30th and 31rst : 6 months before is caculated here as February 31rst, so march 2. Of course, this depends on the leap years.

.. code-block:: php

    case 'past_180':
    				return array(strtotime('-6 months'), time(), $GLOBALS['TL_LANG']['MSC']['cal_empty']);


.. _case-edusoho-structures-nextmonthtrap:

Edusoho
+++++++


:ref:`next-month-trap`, in src/AppBundle/Controller/Admin/AnalysisController.php:1426. 

The last month is wrong 8 times a year : on 31rst, and by the end of March. 

.. code-block:: php

    'lastMonthStart' => date('Y-m-d', strtotime(date('Y-m', strtotime('-1 month')))),
                'lastMonthEnd' => date('Y-m-d', strtotime(date('Y-m', time())) - 24 * 3600),
                'lastThreeMonthsStart' => date('Y-m-d', strtotime(date('Y-m', strtotime('-2 month')))),


.. _case-no-choice:

No Choice
#########

.. _case-nextcloud-structures-nochoice:

NextCloud
+++++++++


:ref:`no-choice`, in build/integration/features/bootstrap/FilesDropContext.php:71. 

Token is checked, but processed in the same way each time. This actual check is done twice, in the same class, in the method droppingFileWith(). 

.. code-block:: php

    public function creatingFolderInDrop($folder) {
    		$client = new Client();
    		$options = [];
    		if (count($this->lastShareData->data->element) > 0){
    			$token = $this->lastShareData->data[0]->token;
    		} else {
    			$token = $this->lastShareData->data[0]->token;
    		}
    		$base = substr($this->baseUrl, 0, -4);
    		$fullUrl = $base . '/public.php/webdav/' . $folder;
    
    		$options['auth'] = [$token, ''];


.. _case-zencart-structures-nochoice:

Zencart
+++++++


:ref:`no-choice`, in admin/includes/functions/html_output.php:179. 

At least, it always choose the most secure way : use SSL.

.. code-block:: php

    if ($usessl) {
            $form .= zen_href_link($action, $parameters, 'NONSSL');
          } else {
            $form .= zen_href_link($action, $parameters, 'NONSSL');
          }


.. _case-no-empty-regex:

No Empty Regex
##############

.. _case-tikiwiki-structures-noemptyregex:

Tikiwiki
++++++++


:ref:`no-empty-regex`, in lib/sheet/excel/writer/worksheet.php:1925. 

The initial 's' seems to be too much. May be a typo ? 

.. code-block:: php

    // Strip URL type
            $url = preg_replace('s[^internal:]', '', $url);


.. _case-no-isset()-with-empty():

No isset() With empty()
#######################

.. _case-xoops-structures-noissetwithempty:

XOOPS
+++++


:ref:`no-isset()-with-empty()`, in htdocs/class/tree.php:297. 

Too much vlaidation

.. code-block:: php

    isset($this->tree[$key]['child']) && !empty($this->tree[$key]['child']);


.. _case-no-parenthesis-for-language-construct:

No Parenthesis For Language Construct
#####################################

.. _case-phpdocumentor-structures-noparenthesisforlanguageconstruct:

Phpdocumentor
+++++++++++++


:ref:`no-parenthesis-for-language-construct`, in src/Application/Renderer/Router/StandardRouter.php:55. 

No need for parenthesis with require(). instanceof has a higher precedence than return anyway. 

.. code-block:: php

    $this[] = new Rule(function ($node) { return ($node instanceof NamespaceDescriptor); }, $namespaceGenerator);


.. _case-phpmyadmin-structures-noparenthesisforlanguageconstruct:

phpMyAdmin
++++++++++


:ref:`no-parenthesis-for-language-construct`, in db_datadict.php:170. 

Not only echo() doesn't use any parenthesis, but this syntax gives the illusion that echo() only accepts one argument, while it actually accepts an arbitrary number of argument.

.. code-block:: php

    echo (($row['Null'] == 'NO') ? __('No') : __('Yes'))


.. _case-avoid-substr()-one:

Avoid Substr() One
##################

.. _case-churchcrm-structures-nosubstrone:

ChurchCRM
+++++++++


:ref:`avoid-substr()-one`, in src/Login.php:141. 

No need to call substr() to get only one char. 

.. code-block:: php

    if (substr($LocationFromGet, 0, 1) == "/") {
        $LocationFromGet = substr($LocationFromGet, 1);
    }


.. _case-livezilla-structures-nosubstrone:

LiveZilla
+++++++++


:ref:`avoid-substr()-one`, in livezilla/_lib/objects.global.inc.php:2243. 

No need to call substr() to get only one char. 

.. code-block:: php

    $_hex = str_replace("#", "", $_hex);
                if(strlen($_hex) == 3) {
                $r = hexdec(substr($_hex,0,1).substr($_hex,0,1));
                $g = hexdec(substr($_hex,1,1).substr($_hex,1,1));
                $b = hexdec(substr($_hex,2,1).substr($_hex,2,1));
            } else {
                $r = hexdec(substr($_hex,0,2));
                $g = hexdec(substr($_hex,2,2));
                $b = hexdec(substr($_hex,4,2));
            }
            $rgb = array($r, $g, $b);
            return $rgb;


.. _case-@-operator:

@ Operator
##########

.. _case-phinx-structures-noscream:

Phinx
+++++


:ref:`@-operator`, in src/Phinx/Util/Util.php:239. 

fopen() may be tested for existence, readability before using it. Although, it actually emits some errors on Windows, with network volumes.

.. code-block:: php

    $isReadable = @\fopen($filePath, 'r') !== false;
    
            if (!$filePath || !$isReadable) {
                throw new \Exception(sprintf(Cannot open file %s \n, $filename));
            }


.. _case-phpipam-structures-noscream:

PhpIPAM
+++++++


:ref:`@-operator`, in functions/classes/class.Log.php:322. 

Variable and index existence should always be tested with isset() : it is faster than using ``@``.

.. code-block:: php

    $_SESSION['ipamusername']


.. _case-not-not:

Not Not
#######

.. _case-cleverstyle-structures-notnot:

Cleverstyle
+++++++++++


:ref:`not-not`, in modules/OAuth2/OAuth2.php:190. 

This double-call returns ``$results`` as a boolean, preventing a spill of data to the calling method. The ``(bool)`` operator would be clearer here.

.. code-block:: php

    $result = $this->db_prime()->q(
    			[
    				DELETE FROM `[prefix]oauth2_clients`
    				WHERE `id` = '%s',
    				DELETE FROM `[prefix]oauth2_clients_grant_access`
    				WHERE `id`	= '%s',
    				DELETE FROM `[prefix]oauth2_clients_sessions`
    				WHERE `id`	= '%s'
    			],
    			$id
    		);
    		unset($this->cache->{'/'});
    		return !!$result;


.. _case-tine20-structures-notnot:

Tine20
++++++


:ref:`not-not`, in tine20/Calendar/Controller/MSEventFacade.php:392. 

It seems that !! is almost superfluous, as a property called 'is_deleted' should already be a boolean.

.. code-block:: php

    foreach ($exceptions as $exception) {
                    $exception->assertAttendee($this->getCalendarUser());
                    $this->_prepareException($savedEvent, $exception);
                    $this->_preserveMetaData($savedEvent, $exception, true);
                    $this->_eventController->createRecurException($exception, !!$exception->is_deleted);
                }


.. _case-objects-don't-need-references:

Objects Don't Need References
#############################

.. _case-zencart-structures-objectreferences:

Zencart
+++++++


:ref:`objects-don't-need-references`, in includes/library/illuminate/support/helpers.php:484. 

No need for & operator when $class is only used for a method call.

.. code-block:: php

    /**
         * @param $class
         * @param $eventID
         * @param array $paramsArray
         */
        public function updateNotifyCheckoutflowFinishedManageSuccessOrderLinkEnd(&$class, $eventID, $paramsArray = array())
        {
            $class->getView()->getTplVarManager()->se('flag_show_order_link', false);
        }


.. _case-xoops-structures-objectreferences:

XOOPS
+++++


:ref:`objects-don't-need-references`, in htdocs/class/theme_blocks.phps:221. 

Here, $template is modified, when its properties are modified. When only the properties are modified, or read, then & is not necessary.

.. code-block:: php

    public function buildBlock($xobject, &$template)
        {
            // The lame type workaround will change
            // bid is added temporarily as workaround for specific block manipulation
            $block = array(
                'id'      => $xobject->getVar('bid'),
                'module'  => $xobject->getVar('dirname'),
                'title'   => $xobject->getVar('title'),
                // 'name'        => strtolower( preg_replace( '/[^0-9a-zA-Z_]/', '', str_replace( ' ', '_', $xobject->getVar( 'name' ) ) ) ),
                'weight'  => $xobject->getVar('weight'),
                'lastmod' => $xobject->getVar('last_modified'));
    
            $bcachetime = (int)$xobject->getVar('bcachetime');
            if (empty($bcachetime)) {
                $template->caching = 0;
            } else {
                $template->caching        = 2;
                $template->cache_lifetime = $bcachetime;
            }
            $template->setCompileId($xobject->getVar('dirname', 'n'));
            $tplName = ($tplName = $xobject->getVar('template')) ? db:$tplName : 'db:system_block_dummy.tpl';
            $cacheid = $this->generateCacheId('blk_' . $xobject->getVar('bid'));
    // more code to the end of the method


.. _case-include\_once()-usage:

include_once() Usage
####################

.. _case-xoops-structures-onceusage:

XOOPS
+++++


:ref:`include\_once()-usage`, in /htdocs/xoops_lib/modules/protector/admin/center.php:5. 

Loading() classes should be down with autoload(). autload() may be build in several distinct functions, using spl_autoload_register().

.. code-block:: php

    require_once dirname(__DIR__) . 'class/gtickets.php'


.. _case-tikiwiki-structures-onceusage:

Tikiwiki
++++++++


:ref:`include\_once()-usage`, in tiki-mytiki_shared.php :140. 

Turn the code from tiki-mytiki_shared.php into a function or a method, and call it when needed. 

.. code-block:: php

    include_once('tiki-mytiki_shared.php');


.. _case-or-die:

Or Die
######

.. _case-tine20-structures-ordie:

Tine20
++++++


:ref:`or-die`, in scripts/addgrant.php:34. 

Typical error handling, which also displays the MySQL error message, and leaks informations about the system. One may also note that mysql_connect is not supported anymore, and was replaced with mysqli and pdo : this may be a backward compatibile file.

.. code-block:: php

    $link = mysql_connect($host, $user, $pass) or die("No connection: " . mysql_error( ))


.. _case-openconf-structures-ordie:

OpenConf
++++++++


:ref:`or-die`, in openconf/chair/export.inc:143. 

or die() is also applied to many situations, where a blocking situation arise. Here, with the creation of a temporary file.

.. code-block:: php

    $coreFile = tempnam('/tmp/', 'ocexport') or die('could not generate Excel file (6)')


.. _case-printf-number-of-arguments:

Printf Number Of Arguments
##########################

.. _case-phpipam-structures-printfarguments:

PhpIPAM
+++++++


:ref:`printf-number-of-arguments`, in functions/classes/class.Common.php:1174. 

16 will not be displayed.

.. code-block:: php

    sprintf('%032s', gmp_strval(gmp_init($ipv6long, 10), 16);


.. _case-repeated-print():

Repeated print()
################

.. _case-edusoho-structures-repeatedprint:

Edusoho
+++++++


:ref:`repeated-print()`, in app/check.php:71. 

All echo may be merged into one : do this by turning the ; and . into ',', and removing the superfluous echo. Also, echo_style may be turned into a non-display function, returning the build style, rather than echoing it to the output.

.. code-block:: php

    echo PHP_EOL;
    echo_style('title', 'Note');
    echo '  The command console could use a different php.ini file'.PHP_EOL;
    echo_style('title', '~~~~');
    echo '  than the one used with your web server. To be on the'.PHP_EOL;
    echo '      safe side, please check the requirements from your web'.PHP_EOL;
    echo '      server using the ';
    echo_style('yellow', 'web/config.php');
    echo ' script.'.PHP_EOL;
    echo PHP_EOL;


.. _case-humo-gen-structures-repeatedprint:

HuMo-Gen
++++++++


:ref:`repeated-print()`, in menu.php:71. 

Simply calling print once is better than three times. Here too, echo usage would reduce the amount of memory allocation due to concatenation prior display.

.. code-block:: php

    print '<input type=text name=quicksearch value=.$quicksearch. size=10 '.$pattern.' title=.__(Minimum:).$min_chars.__(characters).>';
    			print ' <input type=submit value=.__(Search).>';
    		print </form>;


.. _case-repeated-regex:

Repeated Regex
##############

.. _case-vanilla-structures-repeatedregex:

Vanilla
+++++++


:ref:`repeated-regex`, in library/core/class.pluginmanager.php:1200. 

This regex is actually repeated 4 times across the Vanilla database, including this variation : '#^(https?:)?//#i'.

.. code-block:: php

    '`^https?://`'


.. _case-tikiwiki-structures-repeatedregex:

Tikiwiki
++++++++


:ref:`repeated-regex`, in tiki-login.php:369. 

This regex is use twice, identically, in the same file, with a few line of distance. It may be federated at the file level.

.. code-block:: php

    preg_match('/(tiki-register|tiki-login_validate|tiki-login_scr)\.php/', $url)


.. _case-return-true-false:

Return True False
#################

.. _case-mautic-structures-returntruefalse:

Mautic
++++++


:ref:`return-true-false`, in app/bundles/LeadBundle/Model/ListModel.php:125. 

$isNew could be a typecast.

.. code-block:: php

    $isNew = ($entity->getId()) ? false : true;


.. _case-fuelcms-structures-returntruefalse:

FuelCMS
+++++++


:ref:`return-true-false`, in fuel/modules/fuel/helpers/validator_helper.php:254. 

If/then is a lot of code to produce a boolean.

.. code-block:: php

    function length_min($str, $limit = 1)
    	{
    		if (strlen(strval($str)) < $limit)
    		{
    			return FALSE;
    		}
    		else
    		{
    			return TRUE;
    		}
    	}


.. _case-same-conditions-in-condition:

Same Conditions In Condition
############################

.. _case-teampass-structures-sameconditions:

TeamPass
++++++++


:ref:`same-conditions-in-condition`, in sources/identify.php:1096. 

`$result == 1` is use once in the main if/then, then again the second if/then/elseif structure. Both are incompatible, since, in the else, `$result` will be different from 1. 

.. code-block:: php

    if ($result == 1) {
                    $return = "";
                    $logError = "";
                    $proceedIdentification = true;
                    $userPasswordVerified = false;
                    unset($_SESSION['hedgeId']);
                    unset($_SESSION['flickercode']);
                } else {
                    if ($result < -10) {
                        $logError = "ERROR: ".$result;
                    } elseif ($result == -4) {
                        $logError = "Wrong response code, no more tries left.";
                    } elseif ($result == -3) {
                        $logError = "Wrong response code, try to reenter.";
                    } elseif ($result == -2) {
                        $logError = "Timeout. The response code is not valid anymore.";
                    } elseif ($result == -1) {
                        $logError = "Security Error. Did you try to verify the response from a different computer?";
                    } elseif ($result == 1) {
                        $logError = "Authentication successful, response code correct.
                              <br /><br />Authentification Method for SecureBrowser updated!";
                        // Add necessary code here for accessing your Business Application
                    }
                    $return = "agses_error";
                    echo '[{"value" : "'.$return.'", "user_admin":"',
                    isset($_SESSION['user_admin']) ? $_SESSION['user_admin'] : "",
                    '", "initial_url" : "'.@$_SESSION['initial_url'].'",
                    "error" : "'.$logError.'"}]';
    
                    exit();
                }


.. _case-typo3-structures-sameconditions:

Typo3
+++++


:ref:`same-conditions-in-condition`, in typo3/sysext/recordlist/Classes/RecordList/DatabaseRecordList.php:1696. 

`$table == 'pages` is caught initially, and if it fails, it is tested again in the final else. This won't happen.

.. code-block:: php

    } elseif ($table === 'pages') {
                                    $parameters = ['id' => $this->id, 'pagesOnly' => 1, 'returnUrl' => GeneralUtility::getIndpEnv('REQUEST_URI')];
                                    $href = (string)$uriBuilder->buildUriFromRoute('db_new', $parameters);
                                    $icon = '<a class="btn btn-default" href="' . htmlspecialchars($href) . '" title="' . htmlspecialchars($lang->getLL('new')) . '">'
                                        . $spriteIcon->render() . '</a>';
                                } else {
                                    $params = '&edit[' . $table . '][' . $this->id . ']=new';
                                    if ($table === 'pages') {
                                        $params .= '&overrideVals[pages][doktype]=' . (int)$this->pageRow['doktype'];
                                    }
                                    $icon = '<a class="btn btn-default" href="#" onclick="' . htmlspecialchars(BackendUtility::editOnClick($params, '', -1))
                                        . '" title="' . htmlspecialchars($lang->getLL('new')) . '">' . $spriteIcon->render() . '</a>';
                                }


.. _case-should-chain-exception:

Should Chain Exception
######################

.. _case-magento-structures-shouldchainexception:

Magento
+++++++


:ref:`should-chain-exception`, in lib/Mage/Backup/Filesystem/Rollback/Ftp.php:81. 

Instead of using the exception message as an argument, chaining the exception would send the whole exception, including the message, and other interesting information like file and line.

.. code-block:: php

    protected function _initFtpClient()
        {
            try {
                $this->_ftpClient = new Mage_System_Ftp();
                $this->_ftpClient->connect($this->_snapshot->getFtpConnectString());
            } catch (Exception $e) {
                throw new Mage_Backup_Exception_FtpConnectionFailed($e->getMessage());
            }
        }


.. _case-tine20-structures-shouldchainexception:

Tine20
++++++


:ref:`should-chain-exception`, in tine20/Setup/Controller.php:81. 

Here, the new exception gets an hardcoded message. More details about the reasons are already available in the $e exception, but they are not logged, not chained for later processing.

.. code-block:: php

    try {
                $dirIterator = new DirectoryIterator($this->_baseDir);
            } catch (Exception $e) {
                Setup_Core::getLogger()->warn(__METHOD__ . '::' . __LINE__ . ' Could not open base dir: ' . $this->_baseDir);
                throw new Tinebase_Exception_AccessDenied('Could not open Tine 2.0 root directory.');
            }


.. _case-should-make-ternary:

Should Make Ternary
###################

.. _case-churchcrm-structures-shouldmaketernary:

ChurchCRM
+++++++++


:ref:`should-make-ternary`, in src/CartToFamily.php:57. 

$sState could be the receiving part of a ternary operator. 

.. code-block:: php

    if ($sCountry == 'United States' || $sCountry == 'Canada') {
                $sState = InputUtils::LegacyFilterInput($_POST['State']);
            } else {
                $sState = InputUtils::LegacyFilterInput($_POST['StateTextbox']);
            }


.. _case-strpos()-like-comparison:

Strpos()-like Comparison
########################

.. _case-piwigo-structures-strposcompare:

Piwigo
++++++


:ref:`strpos()-like-comparison`, in admin/include/functions.php:2585. 

preg_match may return 0 if not found, and null if the $pattern is erroneous. While hardcoded regex may be checked at compile time, dynamically built regex may fail at execution time. This is particularly important here, since the function may be called with incoming data for maintenance : 'clear_derivative_cache($_GET['type']);' is in the /admin/maintenance.php.

.. code-block:: php

    function clear_derivative_cache_rec($path, $pattern)
    {
      $rmdir = true;
      $rm_index = false;
    
      if ($contents = opendir($path))
      {
        while (($node = readdir($contents)) !== false)
        {
          if ($node == '.' or $node == '..')
            continue;
          if (is_dir($path.'/'.$node))
          {
            $rmdir &= clear_derivative_cache_rec($path.'/'.$node, $pattern);
          }
          else
          {
            if (preg_match($pattern, $node))


.. _case-thelia-structures-strposcompare:

Thelia
++++++


:ref:`strpos()-like-comparison`, in core/lib/Thelia/Controller/Admin/FileController.php:198. 

preg_match is used here to identify files with a forbidden extension. The actual list of extension is provided to the method via the parameter $extBlackList, which is an array. In case of mis-configuration by the user of this array, preg_match may fail : for example, when regex special characters are provided. At that point, the whole filter becomes invalid, and can't distinguish good files (returning false) and other files (returning NULL). It is safe to use === false in this situation.

.. code-block:: php

    if (!empty($extBlackList)) {
                $regex = "#^(.+)\.(".implode("|", $extBlackList).")$#i";
    
                if (preg_match($regex, $realFileName)) {
                    $message = $this->getTranslator()
                        ->trans(
                            'Files with the following extension are not allowed: %extension, please do an archive of the file if you want to upload it',
                            [
                                '%extension' => $fileBeingUploaded->getClientOriginalExtension(),
                            ]
                        );
                }
            }


.. _case-switch-without-default:

Switch Without Default
######################

.. _case-zencart-structures-switchwithoutdefault:

Zencart
+++++++


:ref:`switch-without-default`, in admin/tax_rates.php:15. 

The 'action' is collected from $_GET and then, compared with various strings to handle the different actions to be taken. The default behavior is implicit here : if no 'action', display the initial form for taxes to be changed. This has to be understood as a general philosophy of ZenCart project, or by reading the rest of the HTML code. Adding a 'default' case here would help understand what happens in case 'action' is absent or unrecognized. 

.. code-block:: php

    $action = (isset($_GET['action']) ? $_GET['action'] : '');
    
      if (zen_not_null($action)) {
        switch ($action) {
          case 'insert':
            // PHP code 
            break;
          case 'save':
            // PHP code 
            break;
          case 'deleteconfirm':
            // PHP code
            break;
        }
      }
    ?> .... HTML code


.. _case-traq-structures-switchwithoutdefault:

Traq
++++


:ref:`switch-without-default`, in src/Helpers/Ticketlist.php:311. 

The default case is actually processed after the switch, by the next if/then structure. The structure deals with the customFields, while the else deals with any unknown situations. This if/then could be wrapped in the 'default' case of switch, for consistent processing. The if/then condition would be hard to use as a 'case' (possible, though). 

.. code-block:: php

    public static function dataFor($column, $ticket)
        {
            switch ($column) {
                // Ticket ID column
                case 'ticket_id':
                    return $ticket['ticket_id'];
                    break;
    
                // Status column
                case 'status':
                case 'type':
                case 'component':
                case 'priority':
                case 'severity':
                    return $ticket[{$column}_name];
                    break;
    
                // Votes
                case 'votes':
                    return $ticket['votes'];
                    break;
            }
    
            // If we're still here, it may be a custom field
            if ($value = $ticket->customFieldValue($column)) {
                return $value->value;
            }
    
            // Nothing!
            return '';
        }


.. _case-ternary-in-concat:

Ternary In Concat
#################

.. _case-teampass-structures-ternaryinconcat:

TeamPass
++++++++


:ref:`ternary-in-concat`, in includes/libraries/protect/AntiXSS/UTF8.php:5409. 

The concatenations in the initial comparison are disguised casting. When $str2 is empty too, the ternary operator yields a 0, leading to a systematic failure. 

.. code-block:: php

    $str1 . '' === $str2 . '' ? 0 : strnatcmp(self::strtonatfold($str1), self::strtonatfold($str2))


.. _case-timestamp-difference:

Timestamp Difference
####################

.. _case-zurmo-structures-timestampdifference:

Zurmo
+++++


:ref:`timestamp-difference`, in app/protected/modules/import/jobs/ImportCleanupJob.php:73. 

This is wrong twice a year, in countries that has day-ligth saving time. One of the weeks will be too short, and the other will be too long. 

.. code-block:: php

    /**
             * Get all imports where the modifiedDateTime was more than 1 week ago.  Then
             * delete the imports.
             * (non-PHPdoc)
             * @see BaseJob::run()
             */
            public function run()
            {
                $oneWeekAgoTimeStamp = DateTimeUtil::convertTimestampToDbFormatDateTime(time() - 60 * 60 *24 * 7);


.. _case-shopware-structures-timestampdifference:

shopware
++++++++


:ref:`timestamp-difference`, in engine/Shopware/Controllers/Backend/Newsletter.php:150. 

When daylight saving strike, the email may suddenly be locked for 1 hour minus 30 seconds ago. The lock will be set for the rest of the hour, until the server catch up. 

.. code-block:: php

    // Check lock time. Add a buffer of 30 seconds to the lock time (default request time)
                if (!empty($mailing['locked']) && strtotime($mailing['locked']) > time() - 30) {
                    echo "Current mail: '" . $subjectCurrentMailing . "'\n";
                    echo "Wait " . (strtotime($mailing['locked']) + 30 - time()) . " seconds ...\n";
                    return;
                }


.. _case-unconditional-break-in-loop:

Unconditional Break In Loop
###########################

.. _case-livezilla-structures-unconditionloopbreak:

LiveZilla
+++++++++


:ref:`unconditional-break-in-loop`, in wp-admin/includes/misc.php:74. 

Only one row is read from the DBManager, and the rest is ignored. The result has no more than one result, basedd on the `LIMIT 1` clause in the SQL. The while loop may be removed.

.. code-block:: php

    $result = DBManager::Execute(true, "SELECT * FROM `" . DB_PREFIX . DATABASE_STATS_AGGS . "` WHERE `month`>0 AND ((`year`='" . DBManager::RealEscape(date("Y")) . "' AND `month`<'" . DBManager::RealEscape(date("n")) . "') OR (`year`<'" . DBManager::RealEscape(date("Y")) . "')) AND (`aggregated`=0 OR `aggregated`>" . (time() - 300) . ") AND `day`=0 ORDER BY `year` ASC,`month` ASC LIMIT 1;");
            if ($result)
                while ($row = DBManager::FetchArray($result)) {
                    if (empty($row["aggregated"])) {
                        DBManager::Execute(true, "UPDATE `" . DB_PREFIX . DATABASE_STATS_AGGS . "` SET `aggregated`=" . time() . " WHERE `year`=" . $row["year"] . " AND `month`=" . $row["month"] . " AND `day`=0 LIMIT 1;");
                        $this->AggregateMonth($row["year"], $row["month"]);
                    }
                    return false;
                }


.. _case-mediawiki-structures-unconditionloopbreak:

MediaWiki
+++++++++


:ref:`unconditional-break-in-loop`, in includes/htmlform/HTMLFormField.php:138. 

The final break is useless : the execution has already reached the end of the loop.

.. code-block:: php

    for ( $i = count( $thisKeys ) - 1; $i >= 0; $i-- ) {
    			$keys = array_merge( array_slice( $thisKeys, 0, $i ), $nameKeys );
    			$data = $alldata;
    			foreach ( $keys as $key ) {
    				if ( !is_array( $data ) || !array_key_exists( $key, $data ) ) {
    					continue 2;
    				}
    				$data = $data[$key];
    			}
    			$testValue = (string)$data;
    			break;
    		}


.. _case-useless-brackets:

Useless Brackets
################

.. _case-churchcrm-structures-uselessbrackets:

ChurchCRM
+++++++++


:ref:`useless-brackets`, in src/Menu.php:72. 

Difficut to guess what was before the block here. It doesn't have any usage for control flow.

.. code-block:: php

    $new_row = false;
            $count_people = 0;
    
            {
                foreach ($peopleWithBirthDays as $peopleWithBirthDay) {
                    if ($new_row == false) {
                        ?>
    
                        <div class=row>
                    <?php
                        $new_row = true;
                    } ?>
                    <div class=col-sm-3>


.. _case-piwigo-structures-uselessbrackets:

Piwigo
++++++


:ref:`useless-brackets`, in picture.php:342. 

There is no need for block braces with case. In fact, it does give a false sense of break, while the case will still fall over to the next one. 

.. code-block:: php

    case 'rate' :
        {
          include_once(PHPWG_ROOT_PATH.'include/functions_rate.inc.php');
          rate_picture($page['image_id'], $_POST['rate']);
          redirect($url_self);
        }


.. _case-useless-type-casting:

Useless Type Casting
####################

.. _case-fuelcms-structures-uselesscasting:

FuelCMS
+++++++


:ref:`useless-type-casting`, in fuel/codeigniter/core/URI.php:214. 

substr() always returns a string, so there is no need to enforce this.

.. code-block:: php

    if (isset($_SERVER['SCRIPT_NAME'][0]))
    		{
    			if (strpos($uri, $_SERVER['SCRIPT_NAME']) === 0)
    			{
    				$uri = (string) substr($uri, strlen($_SERVER['SCRIPT_NAME']));
    			}
    			elseif (strpos($uri, dirname($_SERVER['SCRIPT_NAME'])) === 0)
    			{
    				$uri = (string) substr($uri, strlen(dirname($_SERVER['SCRIPT_NAME'])));
    			}
    		}


.. _case-thinkphp-structures-uselesscasting:

ThinkPHP
++++++++


:ref:`useless-type-casting`, in ThinkPHP/Library/Think/Db/Driver/Sqlsrv.class.php:67. 

A comparison always returns a boolean, except for the spaceship operator.

.. code-block:: php

    foreach ($result as $key => $val) {
                    $info[$val['column_name']] = array(
                        'name'    => $val['column_name'],
                        'type'    => $val['data_type'],
                        'notnull' => (bool) ('' === $val['is_nullable']), // not null is empty, null is yes
                        'default' => $val['column_default'],
                        'primary' => false,
                        'autoinc' => false,
                    );
                }


.. _case-useless-check:

Useless Check
#############

.. _case-magento-structures-uselesscheck:

Magento
+++++++


:ref:`useless-check`, in wp-admin/includes/misc.php:74. 

This code assumes that $delete is an array, then checks if it empty. Foreach will take care of the empty check.

.. code-block:: php

    if (!empty($delete)) {
                foreach ($delete as $categoryId) {
                    $where = array(
                        'product_id = ?'  => (int)$object->getId(),
                        'category_id = ?' => (int)$categoryId,
                    );
    
                    $write->delete($this->_productCategoryTable, $where);
                }
            }


.. _case-phinx-structures-uselesscheck:

Phinx
+++++


:ref:`useless-check`, in src/Phinx/Migration/Manager.php:828. 

If $dependencies is not empty, foreach() skips the loops.

.. code-block:: php

    private function getSeedDependenciesInstances(AbstractSeed $seed)
        {
            $dependenciesInstances = [];
            $dependencies = $seed->getDependencies();
            if (!empty($dependencies)) {
                foreach ($dependencies as $dependency) {
                    foreach ($this->seeds as $seed) {
                        if (get_class($seed) === $dependency) {
                            $dependenciesInstances[get_class($seed)] = $seed;
                        }
                    }
                }
            }
    
            return $dependenciesInstances;
        }


.. _case-useless-parenthesis:

Useless Parenthesis
###################

.. _case-mautic-structures-uselessparenthesis:

Mautic
++++++


:ref:`useless-parenthesis`, in code/app/bundles/EmailBundle/Controller/AjaxController.php:85. 

Parenthesis are useless around $progress[1], and around the division too. 

.. code-block:: php

    $dataArray['percent'] = ($progress[1]) ? ceil(($progress[0] / $progress[1]) * 100) : 100;


.. _case-woocommerce-structures-uselessparenthesis:

Woocommerce
+++++++++++


:ref:`useless-parenthesis`, in includes/class-wc-coupon.php:437. 

Parenthesis are useless for calculating $discount_percent, as it is a divisition. Moreover, it is not needed with $discount, (float) applies to the next element, but it does make the expression more readable. 

.. code-block:: php

    if ( wc_prices_include_tax() ) {
    	$discount_percent = ( wc_get_price_including_tax( $cart_item['data'] ) * $cart_item_qty ) / WC()->cart->subtotal;
    } else {
    	$discount_percent = ( wc_get_price_excluding_tax( $cart_item['data'] ) * $cart_item_qty ) / WC()->cart->subtotal_ex_tax;
    }
    $discount = ( (float) $this->get_amount() * $discount_percent ) / $cart_item_qty;


.. _case-useless-unset:

Useless Unset
#############

.. _case-tine20-structures-uselessunset:

Tine20
++++++


:ref:`useless-unset`, in tine20/Felamimail/Controller/Message.php:542. 

$_rawContent is unset after being sent to the stream. The variable is a parameter, and will be freed at the end of the call of the method. No need to do it explicitly.

.. code-block:: php

    protected function _createMimePart($_rawContent, $_partStructure)
        {
            if (Tinebase_Core::isLogLevel(Zend_Log::TRACE)) Tinebase_Core::getLogger()->trace(__METHOD__ . '::' . __LINE__ . ' Content: ' . $_rawContent);
            
            $stream = fopen(php://temp, 'r+');
            fputs($stream, $_rawContent);
            rewind($stream);
            
            unset($_rawContent);
            //..... More code, no usage of $_rawContent
        }


.. _case-typo3-structures-uselessunset:

Typo3
+++++


:ref:`useless-unset`, in typo3/sysext/frontend/Classes/Page/PageRepository.php:708. 

$row is unset under certain conditions : here, we can read it in the comments. Eventually, the $row will be returned, and turned into a NULL, by default. This will also create a notice in the logs. Here, the best would be to set a null value, instead of unsetting the variable.

.. code-block:: php

    public function getRecordOverlay($table, $row, $sys_language_content, $OLmode = '')
        {
    //....  a lot more code, with usage of $row, and several unset($row)
    //...... Reduced for simplicity
                        } else {
                            // When default language is displayed, we never want to return a record carrying
                            // another language!
                            if ($row[$GLOBALS['TCA'][$table]['ctrl']['languageField']] > 0) {
                                unset($row);
                            }
                        }
                    }
                }
            }
            foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_page.php']['getRecordOverlay'] ?? [] as $className) {
                $hookObject = GeneralUtility::makeInstance($className);
                if (!$hookObject instanceof PageRepositoryGetRecordOverlayHookInterface) {
                    throw new \UnexpectedValueException($className . ' must implement interface ' . PageRepositoryGetRecordOverlayHookInterface::class, 1269881659);
                }
                $hookObject->getRecordOverlay_postProcess($table, $row, $sys_language_content, $OLmode, $this);
            }
            return $row;
        }


.. _case-var\_dump()...-usage:

var_dump()... Usage
###################

.. _case-tine20-structures-vardumpusage:

Tine20
++++++


:ref:`var\_dump()...-usage`, in tine20/library/Ajam/Connection.php:122. 

Two usage of var_dump(). They are protected by configuration, since the debug property must be set to 'true'. Yet, it is safer to avoid them altogether, and log the information to an external file.

.. code-block:: php

    if($this->debug === true) {
                var_dump($this->getLastRequest());
                var_dump($response);
            }


.. _case-piwigo-structures-vardumpusage:

Piwigo
++++++


:ref:`var\_dump()...-usage`, in include/ws_core.inc.php:273. 

This is a hidden debug system : when the response format is not available, the whole object is dumped in the output.

.. code-block:: php

    function run()
      {
        if ( is_null($this->_responseEncoder) )
        {
          set_status_header(400);
          @header("Content-Type: text/plain");
          echo ("Cannot process your request. Unknown response format.
    Request format: ".@$this->_requestFormat." Response format: ".@$this->_responseFormat."\n");
          var_export($this);
          die(0);
        }


.. _case-while(list()-=-each()):

While(List() = Each())
######################

.. _case-openemr-structures-whilelisteach:

OpenEMR
+++++++


:ref:`while(list()-=-each())`, in library/report.inc:153. 

The first while() is needed, to read the arbitrary long list returned by the SQL query. The second list may be upgraded with a foreach, to read both the key and the value. This is certainly faster to execute and to read.

.. code-block:: php

    function getInsuranceReport($pid, $type = primary)
    {
        $sql = select * from insurance_data where pid=? and type=? order by date ASC;
        $res = sqlStatement($sql, array($pid, $type));
        while ($list = sqlFetchArray($res)) {
            while (list($key, $value) = each($list)) {
                if ($ret[$key]['content'] != $value && $ret[$key]['date'] < $list['date']) {
                    $ret[$key]['content'] = $value;
                    $ret[$key]['date'] = $list['date'];
                }
            }
        }
    
        return $ret;
    }


.. _case-dolphin-structures-whilelisteach:

Dolphin
+++++++


:ref:`while(list()-=-each())`, in Dolphin-v.7.3.5/modules/boonex/forum/classes/Forum.php:1875. 

This clever use of while() and list() is actually a foreach($a as $r) (the keys are ignored)

.. code-block:: php

    function getRssUpdatedTopics ()
        {
            global $gConf;
    
            $this->_rssPrepareConf ();
    
            $a = $this->fdb->getRecentTopics (0);
    
            $items = '';
            $lastBuildDate = '';
            $ui = array();
            reset ($a);
            while ( list (,$r) = each ($a) ) {
                // acquire user info
                if (!isset($ui[$r['last_post_user']]) && ($aa = $this->_getUserInfoReadyArray ($r['last_post_user'], false)))
                    $ui[$r['last_post_user']] = $aa;
    
                $td = orca_mb_replace('/#/', $r['count_posts'], '[L[# posts]]') . ' &#183; ' . orca_mb_replace('/#/', $ui[$r['last_post_user']]['title'], '[L[last reply by #]]') . ' &#183; ' . $r['cat_name'] . ' &#187; ' . $r['forum_title'];


.. _case-preg\_replace-with-option-e:

preg_replace With Option e
##########################

.. _case-edusoho-structures-pregoptione:

Edusoho
+++++++


:ref:`preg\_replace-with-option-e`, in vendor_user/uc_client/lib/uccode.class.php:32. 

This call extract text between [code] tags, then process it with $this->codedisp() and nest it again in the original string. preg_replace_callback() is a drop-in replacement for this piece of code. 

.. code-block:: php

    $message = preg_replace("/\s*\[code\](.+?)\[\/code\]\s*/ies", "$this->codedisp('\1')", $message);


.. _case-no-real-comparison:

No Real Comparison
##################

.. _case-magento-type-norealcomparison:

Magento
+++++++


:ref:`no-real-comparison`, in app/code/core/Mage/XmlConnect/Block/Catalog/Product/Options/Configurable.php:74. 

Compare prices and physical quantities with a difference, so as to avoid rounding errors.

.. code-block:: php

    if ((float)$option['price'] != 0.00) {
                            $valueNode->addAttribute('price', $option['price']);
                            $valueNode->addAttribute('formated_price', $option['formated_price']);
                        }


.. _case-spip-type-norealcomparison:

SPIP
++++


:ref:`no-real-comparison`, in ecrire/maj/v017.php:37. 

Here, the current version number is stored as a real number. With a string, though a longer value, it may be compared using the version_compare() function.

.. code-block:: php

    $version_installee == 1.701


.. _case-one-variable-string:

One Variable String
###################

.. _case-tikiwiki-type-onevariablestrings:

Tikiwiki
++++++++


:ref:`one-variable-string`, in lib/wiki-plugins/wikiplugin_addtocart.php:228. 

Double-quotes are not needed here. If casting to string is important, the (string) would be more explicit.

.. code-block:: php

    foreach ($plugininfo['params'] as $key => $param) {
    		$default["$key"] = $param['default'];
    	}


.. _case-nextcloud-type-onevariablestrings:

NextCloud
+++++++++


:ref:`one-variable-string`, in build/integration/features/bootstrap/BasicStructure.php:349. 

Both concatenations could be merged, independantly. If readability is important, why not put them inside curly brackets?

.. code-block:: php

    public static function removeFile($path, $filename) {
    		if (file_exists("$path" . "$filename")) {
    			unlink("$path" . "$filename");
    		}
    	}


.. _case-should-typecast:

Should Typecast
###############

.. _case-xataface-type-shouldtypecast:

xataface
++++++++


:ref:`should-typecast`, in Dataface/Relationship.php:1612. 

This is an exact example. A little further, the same applies to intval($max)) 

.. code-block:: php

    intval($min);


.. _case-openconf-type-shouldtypecast:

OpenConf
++++++++


:ref:`should-typecast`, in author/upload.php:62. 

This is another exact example. 

.. code-block:: php

    intval($_POST['pid']);


.. _case-silently-cast-integer:

Silently Cast Integer
#####################

.. _case-mediawiki-type-silentlycastinteger:

MediaWiki
+++++++++


:ref:`silently-cast-integer`, in includes/debug/logger/monolog/AvroFormatter.php:167. 

Too many ff in the masks. 

.. code-block:: php

    private function encodeLong( $id ) {
    		$high   = ( $id & 0xffffffff00000000 ) >> 32;
    		$low    = $id & 0x00000000ffffffff;
    		return pack( 'NN', $high, $low );
    	}


.. _case-strings-with-strange-space:

Strings With Strange Space
##########################

.. _case-openemr-type-stringwithstrangespace:

OpenEMR
+++++++


:ref:`strings-with-strange-space`, in library/globals.inc.php:3270. 

The name of the contry contains both an unsecable space (the first, after Tonga), and a normal space (between Tonga and Islands). Translations are stored in a database, which preserves the unbreakable spaces. This also means that fixing the translation must be applied to every piece of data at the same time. The xl() function, which handles the translations, is also a good place to clean the spaces before searching for the right translation.

.. code-block:: php

    'to' => xl('Tonga (Tonga Islands)'),


.. _case-thelia-type-stringwithstrangespace:

Thelia
++++++


:ref:`strings-with-strange-space`, in templates/backOffice/default/I18n/fr_FR.php:647. 

This is another example with a translation sentence. Here, the unbreakable space is before the question mark : this is a typography rule, that is common to many language. This would be a false positive, unless typography is handled by another part of the software.

.. code-block:: php

    'Mot de passe oublié ?'


.. _case-used-once-variables-(in-scope):

Used Once Variables (In Scope)
##############################

.. _case-shopware-variables-variableusedoncebycontext:

shopware
++++++++


:ref:`used-once-variables-(in-scope)`, in _sql/migrations/438-add-email-template-header-footer-fields.php:115. 

In the updateEmailTemplate method, $generatedQueries collects all the generated SQL queries. $generatedQueries is not initialized, and never used after initialization. 

.. code-block:: php

    private function updateEmailTemplate($name, $content, $contentHtml = null)
        {
            $sql = <<<SQL
    UPDATE `s_core_config_mails` SET `content` = "$content" WHERE `name` = "$name" AND dirty = 0
    SQL;
            $this->addSql($sql);
    
            if ($contentHtml != null) {
                $sql = <<<SQL
    UPDATE `s_core_config_mails` SET `content` = "$content", `contentHTML` = "$contentHtml" WHERE `name` = "$name" AND dirty = 0
    SQL;
                $generatedQueries[] = $sql;
            }
    
            $this->addSql($sql);
        }



