<?
/**
 * class which contains all registered plugins
 */

include_once("./content/classes/Pages/class.ilParagraphPlugin.php");

class ilParagraphPlugins {
	/**
	 * array which contains an instance of each plugin
	 * keys are equal to the serialization string of a plugin
	 */
	var $plugins;
	
	/**
	 * contains the plugins directory
	 */
	var $pluginDirectory;
	/**
	 * array which contains all directories which should not be parsed 
	 * within the plugins directory, by default the sub directories
	 * resources, CVS and classes are skipped
	 */
	var $skipDirectories;
	
	/**
	 * constructor initializes skip Directories
	 */
	function ilParagraphPlugins () {
		$this->plugins = array();
		$this->pluginDirectory = ILIAS_ABSOLUTE_PATH."/content/plugins";
		$this->skipDirectories = array ();
		$this->skipDirectories [$this->pluginDirectory."/classes"] = "skip"; 
		$this->skipDirectories [$this->pluginDirectory."/resources"]= "skip";
		$this->skipDirectories [$this->pluginDirectory."/CVS"]= "skip";
	}
	
	/**
	 * getPluginArray
	 */
	function getRegisteredPluginsAsArray () {
		return $this->plugins;		
	}
	
	
	/**
	 * register plugin
	 */
	function registerPlugin ($plugin) {
		//echo "registered Plugin ".$plugin->getTitle();
		$this->plugins[$plugin->serializeToString()] = $plugin;
	}
	
	/**
	 * serializes all plugin to one string
	 * format filetype#title#link#image|filetype#title#link#image|...
	 */
	function serializeToString (){
		return implode ("|", array_keys($this->plugins));		
	}
	
	/**
	 * parses plugin subdirectory to determine registered plugins
	 */
	function initialize () {		
		if (file_exists($this->pluginDirectory)) {
			foreach (glob($this->pluginDirectory."/*",GLOB_ONLYDIR) as $pluginDir) {
				if (array_key_exists($pluginDir,$this->skipDirectories))
					continue;
				$pluginFile = $pluginDir . "/classes/class.plugin.php";
				if (file_exists($pluginFile)) {
					include ($pluginFile);
					if (is_a($plugin,"ilParagraphPlugin") && $plugin->isActive()) {
						$this->registerPlugin($plugin);
						unset ($plugin);
					}
				}
			}	
		}
	}
}
?>
