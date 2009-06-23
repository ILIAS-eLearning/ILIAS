<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* This class represents an image file property in a property form.
*
* @author Helmut Schottmüller <ilias@aurealis.de> 
* @version $Id$
* @ingroup	ServicesForm
*/
class ilFlashFileInputGUI extends ilFileInputGUI
{
	protected $applet;
	protected $width;
	protected $height;
	protected $parameters;
	
	/**
	* Constructor
	*
	* @param	string	$a_title	Title
	* @param	string	$a_postvar	Post Variable
	*/
	function __construct($a_title = "", $a_postvar = "")
	{
		parent::__construct($a_title, $a_postvar);
		$this->setType("flash_file");
		$this->setSuffixes(array("swf"));
		$this->width = 550;
		$this->height = 400;
		$this->parameters = array();
	}

	/**
	* Set applet.
	*
	* @param	string	$a_applet	Applet
	*/
	function setApplet($a_applet)
	{
		$this->setFilename($a_applet);
		$this->setValue($a_applet);
	}

	/**
	* Get applet.
	*
	* @return	string	Applet
	*/
	function getApplet()
	{
		return $this->getFilename();
	}

	/**
	* Get width.
	*
	* @return	integer	width
	*/
	function getWidth()
	{
		return $this->width;
	}

	/**
	* Set width.
	*
	* @param	integer	$a_width	width
	*/
	function setWidth($a_width)
	{
		$this->width = $a_width;
	}

	/**
	* Get height.
	*
	* @return	integer	height
	*/
	function getHeight()
	{
		return $this->height;
	}

	/**
	* Set height.
	*
	* @param	integer	$a_height	height
	*/
	function setHeight($a_height)
	{
		$this->height = $a_height;
	}
	
	/**
	* Get parameters.
	*
	* @return	array Parameters
	*/
	function getParameters()
	{
		return $this->parameters;
	}
	
	/**
	* Set parameters.
	*
	* @param array $a_parameters Parameters
	*/
	function setParameters($a_parameters)
	{
		$this->parameters = $a_parameters;
	}

	/**
	* Add parameter.
	*
	* @param string $name Parameter name
	* @param string $value Parameter value
	*/
	function addParameter($name, $value)
	{
		$this->parameters[$name] = $value;
	}
	
	/**
	* Remove parameter.
	*
	* @param string $name Parameter name
	*/
	function removeParameter($name)
	{
		unset($this->parameters[$name]);
	}

	/**
	* Remove all parameters
	*/
	function clearParameters()
	{
		$this->parameters = array();
	}
	
	/**
	* Insert property html
	*/
	function insert(&$a_tpl)
	{
		global $lng;
		
		if ($this->getApplet() != "")
		{
			if (count($this->getParameters()))
			{
				$index = 0;
				$params = array();
				foreach ($this->getParameters() as $name => $value)
				{
					array_push($params, urlencode($name) . "=" . urlencode($value));
					$a_tpl->setCurrentBlock("applet_param_input");
					$a_tpl->setVariable("TEXT_NAME", $lng->txt("name"));
					$a_tpl->setVariable("TEXT_VALUE", $lng->txt("value"));
					$a_tpl->setVariable("PARAM_INDEX", $index);
					$a_tpl->setVariable("POST_VAR_P", $this->getPostVar());
					$a_tpl->setVariable("VALUE_NAME", "value=\"" . ilUtil::prepareFormOutput($name) . "\"");
					$a_tpl->setVariable("VALUE_VALUE", "value=\"" . ilUtil::prepareFormOutput($value) . "\"");
					$a_tpl->setVariable("TEXT_DELETE_PARAM", $lng->txt("delete_parameter"));
					$a_tpl->parseCurrentBlock();
					$index++;
				}
				$a_tpl->setCurrentBlock("applet_parameter");
				$a_tpl->setVariable("PARAM_VALUE", join($params, "&"));
				$a_tpl->parseCurrentBlock();
				$a_tpl->setCurrentBlock("flash_vars");
				$a_tpl->setVariable("PARAM_VALUE", join($params, "&"));
				$a_tpl->parseCurrentBlock();
			}
			$a_tpl->setCurrentBlock("applet");
			$a_tpl->setVariable("TEXT_ADD_PARAM", $lng->txt("add_parameter"));
			$a_tpl->setVariable("APPLET_WIDTH", $this->getWidth());
			$a_tpl->setVariable("APPLET_HEIGHT", $this->getHeight());
			$a_tpl->setVariable("POST_VAR_D", $this->getPostVar());
			$a_tpl->setVariable("TEXT_WIDTH", $lng->txt("width"));
			$a_tpl->setVariable("TEXT_HEIGHT", $lng->txt("height"));
			$a_tpl->setVariable("APPLET_FILE", str_replace(ILIAS_ABSOLUTE_PATH, ILIAS_HTTP_PATH, $this->getApplet()));
			$a_tpl->setVariable("APPLET_PATH", str_replace(ILIAS_ABSOLUTE_PATH, ILIAS_HTTP_PATH, $this->getApplet()));
			if ($this->getWidth()) $a_tpl->setVariable("VALUE_WIDTH", "value=\"" . $this->getWidth() . "\"");
			if ($this->getHeight()) $a_tpl->setVariable("VALUE_HEIGHT", "value=\"" . $this->getHeight() . "\"");
			$a_tpl->setVariable("ID", $this->getFieldId());
			$a_tpl->setVariable("TXT_DELETE_EXISTING",
				$lng->txt("delete_existing_file"));
			$a_tpl->parseCurrentBlock();
		}
		
		$js_tpl = new ilTemplate('tpl.flashAddParam.js', true, true, 'Services/Form');
		$js_tpl->setVariable("TEXT_NAME", $lng->txt("name"));
		$js_tpl->setVariable("TEXT_VALUE", $lng->txt("value"));
		$js_tpl->setVariable("POST_VAR", $this->getPostVar());
		$js_tpl->setVariable("TEXT_DELETE_PARAM", $lng->txt("delete_parameter"));
		$js_tpl->setVariable("TEXT_CONFIRM_DELETE_PARAMETER", $lng->txt("confirm_delete_parameter"));
		
		$a_tpl->setCurrentBlock("prop_flash_file");
		$a_tpl->setVariable("POST_VAR", $this->getPostVar());
		$a_tpl->setVariable("ID", $this->getFieldId());
		$a_tpl->setVariable("TXT_MAX_SIZE", $lng->txt("file_notice")." ".
			$this->getMaxFileSizeString());
		$a_tpl->setVariable("JAVASCRIPT_FLASH", $js_tpl->get());
		$a_tpl->parseCurrentBlock();

		include_once "./Services/YUI/classes/class.ilYuiUtil.php";
		ilYuiUtil::initConnectionWithAnimation();
	}

	/**
	* Get deletion flag
	*/
	function getDeletionFlag()
	{
		if ($_POST[$this->getPostVar()."_delete"])
		{
			return true;
		}
		return false;
	}

}
?>