<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/
/**
 * This class describes plugin
 * 
 *  * use the following plugin directory structure
 * 
 * 
 * -- content
 *      -- plugins
 *              -- classes              (contains helpful classes like this)
 *              -- plugin1              (plugin with name "plugin1")
 *                      -- classes      (classes needed for plugin)
 *                      -- ressources   (bin ressources for plugin, jars etc...)
 *                      -- templates    (ilias templates, etc.)
 * @ingroup ServicesCOPage
 */
 
 class ilParagraphPlugin {
 	/**
 	 * all plugin properties are stored in a associative array
 	 * to be processed very easy
 	 * Properties: filetype, title, link and image 
 	 */
	var $properties;
	
	/*
	 * the directory we are plugin resides within the plugins directory
	 */
	var $directory;
	
	/**
	 * plugin description 
	 *
	 * @var string
	 */
	var $description;
	
	/**
	 * switch, which activates the plugin, defaults to false
	 */
	var $active;
		

	/**
	 * create paragraph plugin instance
	 *
	 * @param string $directory  relative sub directory name, e.g. edit
	 * @param string $title      title of plugin 
	 * @param string $filetype   filetype to which the plugin will be applied
	 * @param string $link       link which to start when clicking on plugin
	 * @param string $description plugin description
	 * @param boolean $active     activate plugin or not
	 * @return ilParagraphPlugin
	 */
	function ilParagraphPlugin ($directory, $title, $filetype, $link, $description = "", $active = FALSE) {
       	$this->directory = $directory;
		$this->properties = array ("filetype" => "", "title" => "", "link" => "");
		$this->setTitle($title);
		$this->setFileType($filetype);
		$this->setLink ($link);
		$this->setActive($active);		
		$this->setDescription($description);
	}
	
	
	
	/**
	 * returns a string representation used to active a plugin in page.xsl
	 * 
	 * all properties separatad by #
	 * 
	 * @return returns serialized string
	 */
	function serializeToString (){		
		return implode("#",$this->properties);
	}
	
	
	/** 
	 * set title of plugin used within alt tag of image
	 * replaces |,# sign with _
	 */
	function setTitle ($title) {
		$title = str_replace (array("|","#"), array ("_","_"),$title);			
		$this->properties["title"] = $title;
	}
	
	/** 
	 * set link of plugin relativ to plugin url
	 * replaces |,# sign with _
	 */
	function setLink ($link) {
		$link = str_replace (array("|","#"), array ("_","_"),$link);
		$this->properties["link"] = $this->getPluginURL()."/".$link;
	}
	
	/** 
	 * set image link relative to plugin url
	 * replaces |,# sign with _
	 */
	function setImage ($image) {
		$image = str_replace (array("|","#"), array ("_","_"),$image);
		$this->properties["image"] = $this->getTemplateURL()."/".$image;
	}
	
	/** 
	 * set filetype of plugin to determine for which paragraph it will be activated
	 * replaces |,# sign with _
	 */
	function setFileType ($filetype) {
		$filetype = str_replace (array("|","#"), array ("_","_"),$filetype);
		$this->properties["filetype"] = $filetype;
	}	
	
	/**
	 * @return title
	 */
	
	function getTitle () {
		return $this->properties["title"];
	}
		
	/**
	 * @return absolute plugin directory
	 */
	function getPluginDir () {
		return ILIAS_ABSOLUTE_PATH."/Services/COPage/plugins/".$this->directory;
	}
	
	/**
	 * @return  absolute template directory
	 */
	function getTemplateDir () {
		return $this->getPluginDir()."/templates";	
	}
	
    /**
     * @return template url
     *
     */
	
	function getTemplateURL () {
	    return $this->getPluginURL()."/templates";
	}
	/**
	 * @return  absolute class directory
	 */
	function getClassDir () {
		return $this->getPluginDir()."/classes";	
	}
	
	/**
	 * @return absolute resource directory
	 */
	
	function getResourceDir () {
		return $this->getPluginDir()."/resources";	
	}
	
	/**
	 * @return resource url
	 */
	
	function getResourceURL () {
		return $this->getSystemURL()."/Services/COPage/plugins/".$this->directory."/resources";	
	}
	
	/**
	 * @return plugin url
	 */
	function getPluginURL () {
		return $this->getSystemURL()."/Services/COPage/plugins/".$this->directory;	
	}
	
	/**
	*	@return System base URL
	*
	*/
	function getSystemURL () {
		return str_replace("/Services/COPage/plugins","",ILIAS_HTTP_PATH);
	}
	
	/**
	 * @return true if plugin is active
	 */
	function isActive() {
		return $this->active;
	}
	
	/**
	 * sets active to value bool
	 * @param boolean sets active
	 */
	function setActive ($bool) {
		$this->active = ($bool)?true:false;
	}
	
	/**
	 * set plugin description
	 *
	 * @param string $description
	 */
	function setDescription ($description) {
	    $this->description = $description;
	}
	
	/**
	 * returns description of plugin
	 *
	 * @return description
	 */
	function getDescription () {
	    return $this->description;
	}
	
	/**
	 * returns plugin directory name
	 *
	 * @return string directory name of plugin
	 */
	function getDirectory () {
	    return $this->directory;
	}
}
 
?>
