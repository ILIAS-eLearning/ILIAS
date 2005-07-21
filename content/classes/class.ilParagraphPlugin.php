<?

class ParagraphPlugins {
	var $plugins;
	var $pluginDirectory;
	var $skipDirectories;
	
	function ParagraphPlugins () {
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
	 * parses plugin subdirectory and 
	 */
	function initialize () {		
		foreach (glob($this->pluginDirectory."/*",GLOB_ONLYDIR) as $pluginDir) {
			if (array_key_exists($pluginDir,$this->skipDirectories))
				continue;
			$pluginFile = $pluginDir . "/classes/class.plugin.php";
			if (file_exists($pluginFile)) {
				include ($pluginFile);
				if (is_object($plugin)) {
					$this->registerPlugin($plugin);
					unset ($plugin);
				}
			}
		}	
	}
}

class ParagraphPlugin {
	var $properties;
	var $directory;
	
	function ParagraphPlugin ($directory, $title, $filetype, $link) {
		$this->directory = $directory;
		$this->properties = array ("filetype" => "", "title" => "", "link" => "");
		$this->setTitle($title);
		$this->setFileType($filetype);
		$this->setLink ($link);		
	}
	
	function serializeToString (){		
		return implode("#",$this->properties);
	}
	
	function setTitle ($title) {
		$this->properties["title"] = $title;
	}
	
	function setLink ($link) {
		$this->properties["link"] = $this->getPluginURL()."/".$link;
	}
	
	function setImage ($image) {
		$this->properties["image"] = $this->getResourceURL()."/".$image;
	}
	
	function setFileType ($filetype) {
		$this->properties["filetype"] = $filetype;
	}	
	
	function getTitle () {
		return $this->properties["title"];
	}
		
	function getPluginDir () {
		return ILIAS_ABSOLUTE_PATH."/content/plugins"."/".$this->directory;
	}
	
	function getTemplateDir () {
		return $this->getPluginDir()."/templates";	
	}
	
	function getClassDir () {
		return $this->getPluginDir()."/classes";	
	}
	
	
	function getResourceDir () {
		return $this->getPluginDir()."/resources";	
	}
	
	function getResourceURL () {
		return ILIAS_HTTP_PATH."/content/plugins/".$this->directory."/resources";	
	}
	
	function getPluginURL () {
		return ILIAS_HTTP_PATH."/content/plugins/".$this->directory;	
	}
}

$paragraph_plugins = new ParagraphPlugins();

$paragraph_plugins->initialize ();

$GLOBALS["paragraph_plugins"] = $paragraph_plugins;

?>
