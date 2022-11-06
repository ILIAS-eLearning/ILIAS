# UIComponent Service

***The UI Component Service is deprecated and MUST NOT be used anymore.***

## UserInterfaceHook Pluginslot
This plugin slot has been published as stable with ILIAS 4.2. The goal of the user interface plugin slot is to allow simple
 modifications of standard components of the ILIAS user interface. The slot is defined by the UIComponent Service of ILIAS
 and named "UserInterfaceHook". This means all plugins have to be installed into directories at:

`Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/<Plugin_Name>`

The ID of the UIComponent Service is "ui", the ID of the slot is "uihk". These are used as prefixes together with your
plugin id for database tables and for language variable identifiers:

DB Table / Language VariablePrefixes: `ui_uihk_<Plugin_ID>_`

## Plugin Directory Structure
A user interface plugin has the following minimum file/directory structure:
```
<PluginName> (Directory)
	classes (Directory)
		class.il<PluginName>Plugin.php
		class.il<PluginName>UIHookGUI.php
	plugin.php
```

### ilPluginNamePlugin.php
Default Structure:

```
<?php

require_once __DIR__ . "/../vendor/autoload.php";

/**
 * Previously there was not that much to do here except defining the Plugins name.
 *
 * Since the introduction of the Global Screen, this can be also used however, to manipulate the Main Menu of ILIAS.
 * Note that this does not only work for ilUserInterfaceHookPlugin plugins, but for all plugins descending ilPlugin.
 *
 * Class ilPluginNamePlugin
 */
class ilPluginNamePlugin extends ilUserInterfaceHookPlugin {
	/**
	 * @inheritdoc
	 */
	function getPluginName() {
		return 'UIHookDemo';
	}

	/**
	 * This method is used to promote a plugins own GlobalScreen provider. With such a provider, one can easily
	 * extend parts of the Global Screen such as the Main Menu. Note that this method is available for all types
	 * of plugins.
	 *
	 * @return AbstractStaticPluginMainMenuProvider
	 */
	public function promoteGlobalScreenProvider(): AbstractStaticPluginMainMenuProvider {
		return new ilPluginGlobalScreenNullProvider();
	}

	/**
	 * This methods allows to replace the UI Renderer (see src/UI) of ILIAS after initialization
	 * by returning a closure returning a custom renderer. E.g:
	 *
	 * return function(\ILIAS\DI\Container $c){
	 *   return new CustomRenderer();
	 * };
	 *
	 * Note: Note that plugins might conflict by replacing the renderer, so only use if you
	 * are sure, that no other plugin will do this for a given context.
	 *
	 * @param \ILIAS\DI\Container $dic
	 * @return Closure
	 */
	public function exchangeUIRendererAfterInitialization(\ILIAS\DI\Container $dic):Closure{
		//This returns the callable of $c['ui.renderer'] without executing it.
		return $dic->raw('ui.renderer');
	}

	/**
	 * This methods allows to replace some factory for UI Components (see src/UI) of ILIAS
	 * after initialization by returning a closure returning a custom factory. E.g:
	 *
	 * if($key == "ui.factory.nameOfFactory"){
	 *    return function(\ILIAS\DI\Container  $c){
	 *       return new CustomFactory($c['ui.signal_generator'],$c['ui.factory.maincontrols.slate']);
	 *    };
	 * }
	 *
	 * Note: Note that plugins might conflict by replacing the same factory, so only use if you
	 * are sure, that no other plugin will do this for a given context.
	 *
	 * @param string $dic_key
	 * @param \ILIAS\DI\Container $dic
	 * @return Closure
	 */
	public function exchangeUIFactoryAfterInitialization(string $dic_key, \ILIAS\DI\Container $dic):Closure{
		//This returns the callable of $c[$key] without executing it.
		return $dic->raw($dic_key);
	}
}
```

### class.ilPluginNameUIHookGUI.php
Note that the methods of this class are depricated since ILIAS 6.0. Only use if absolutely necessary. Note that
many scenarios might be better served by using the Global Screen Service or the UI Components (See above method).

```
<?php
require_once __DIR__ . "/../vendor/autoload.php";

/**
 * This is where the actual magic of the GUI modifications take place.
 *
 * Class ilPluginNameUIHookGUI
 */
class ilPluginNameUIHookGUI extends ilUIHookPluginGUI
{

	/**
	 * @deprecated Note this method is deprecated. There are several issues with hacking into already rendered html
	 * as provided here:
	 * - The generation of html might be performed twice (especially if REPLACE is used).
	 * - There is limited access to data used to generate the original html. If needed this data needs to be gathered again.
	 * - User Interface components are migrated towards the UIComponents and Global Screen which do not make use of the
	 *   mechanism provided here.
	 *
	 *
	 * Modify HTML output of GUI elements. Modifications modes are:
	 * - ilUIHookPluginGUI::KEEP (No modification)
	 * - ilUIHookPluginGUI::REPLACE (Replace default HTML with your HTML)
	 * - ilUIHookPluginGUI::APPEND (Append your HTML to the default HTML)
	 * - ilUIHookPluginGUI::PREPEND (Prepend your HTML to the default HTML)
	 *
	 * @param string $a_comp component
	 * @param string $a_part string that identifies the part of the UI that is handled
	 * @param array $a_par array of parameters (depend on $a_comp and $a_part), e.g. name of the used tpl.
	 *
	 * @return array array with entries "mode" => modification mode, "html" => your html
	 */
	function getHTML($a_comp, $a_part, $a_par = array())
	{
		//...
		return array("mode" => ilUIHookPluginGUI::KEEP, "html" => "");
	}


	/**
	 * @deprecated Note this method is deprecated. User Interface components are migrated towards the UIComponents and
	 * Global Screen which do not make use of the mechanism provided here. Make use of the extension possibilities provided
	 * by Global Screen and UI Components instead.
	 *
	 * In ILIAS 6.0 still working for working for:
	 * - $a_comp="Services/Ini" ; $a_part="init_style"
	 * - $a_comp="" ; $a_part="tabs"
	 * - $a_comp="" ; $a_part="sub_tabs"
	 *
	 * Allows to modify user interface objects before they generate their output.
	 *
	 * @param string $a_comp component
	 * @param string $a_part string that identifies the part of the UI that is handled
	 * @param array $a_par array of parameters (depend on $a_comp and $a_part)
	 */
	function modifyGUI($a_comp, $a_part, $a_par = array())
	{
		/**
		 * Tabs are not migrated to the UI Components/Global Screen, so they still might be manipulated here.
		 *
		 * Note that you currently do not get information in $a_comp
		 * here. So you need to use general GET/POST information
		 * like $_GET["baseClass"], $ilCtrl->getCmdClass/getCmd
		 * to determine the context.
		 */
		if ($a_part == "tabs" && $_GET["UIDemo"] == "addTab")
		{
			/**
			 * @var $tabs ilTabsGUI
			 */
			$tabs = $a_par["tabs"];
			$tabs->addTab("NewTabId","New Tab","#");
		}
	}

}
```

### Example Plugin
A working example for ILIAS 6.0 can be found at: https://github.com/Amstutz/UIHookDemo

The example show how to:
* Add Tabs by modify  GUI
* Replace the Metabar of ILIAS by Using getHTML
* Render parts of the page as json
* Add a new Item the the Main Bar of ILIAS (aka Main Menu)

There is also an advanced demo of how the Main Menu can be extended by using the new Global Screen service. The
extension adds top items that can be shown if a set of global roles is attached to the current user. You can see this
additional type in the Administration > Main Menu by adding a new Top Item.
