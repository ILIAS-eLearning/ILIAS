<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2005 ILIAS open source, University of Cologne            |
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
* special template class to simplify handling of ITX/PEAR
* @author	Stefan Kesseler <skesseler@databay.de>
* @author	Sascha Hofmann <shofmann@databay.de>
* @version	$Id$
* @package	application
*/


class ilTemplate extends ilTemplateX
{
	/**
	* variablen die immer in jedem block ersetzt werden sollen
	* @var	array
	*/
	var $vars;

	/**
	* Aktueller Block
	* Der wird gemerkt bei der berladenen Funktion setCurrentBlock, damit beim ParseBlock
	* vorher ein replace auf alle Variablen gemacht werden kann, die mit dem BLockname anfangen.
	* @var	string
	*/
	var $activeBlock;

	/**
	* constructor
	* @param	string	$file 		templatefile (mit oder ohne pfad)
	* @param	boolean	$flag1 		remove unknown variables
	* @param	boolean	$flag2 		remove empty blocks
	* @param	boolean	$in_module	should be set to true, if template file is in module subdirectory
	* @param	array	$vars 		variables to replace
	* @access	public
	*/
	function ilTemplate($file,$flag1,$flag2,$in_module = false, $vars = "DEFAULT")
	{
		global $ilias;

		$this->activeBlock = "__global__";
		$this->vars = array();

		/*
		if (strpos($file,"/") === false)
		{
			//$fname = $ilias->tplPath;
			$base = "./";
			if ($module != "")
			{
				$base.= $module."/";
			}
			$base .= "templates/";
			$fname = $base.$ilias->account->skin."/".basename($file);
			if(!file_exists($fname))
			{
				$fname .= $base."default/".basename($file);
			}
		}
		else
		{
			$fname = $file;
		}*/
		$fname = $this->getTemplatePath($file, $in_module);

		$this->tplName = basename($fname);
		$this->tplPath = dirname($fname);

		if (!file_exists($fname))
		{
			$ilias->raiseError("template ".$fname." was not found.", $ilias->error_obj->FATAL);
			return false;
		}

		//$this->IntegratedTemplateExtension(dirname($fname));
		$this->callConstructor();
		//$this->loadTemplatefile(basename($fname), $flag1, $flag2);
		$this->loadTemplatefile($fname, $flag1, $flag2);

		//add tplPath to replacevars
		$this->vars["TPLPATH"] = $this->tplPath;

		return true;
	}

	/**
	* ???
	* @access	public
	* @param	string
	* @return	string
	*/
	function get($part = "DEFAULT", $add_error_mess = false,
		$handle_referer = false, $add_ilias_footer = false)
	{
		if ($add_error_mess)
		{
			$this->addErrorMessage();
		}

		if ($add_ilias_footer)
		{
			$this->addILIASFooter();
		}

		if ($handle_referer)
		{
			$this->handleReferer();
		}

		if ($part == "DEFAULT")
		{
			return parent::get();
		}
		else
		{
			return parent::get($part);
		}

	}

	function addErrorMessage()
	{
		// ERROR HANDLER SETS $_GET["message"] IN CASE OF $error_obj->MESSAGE
		if ($_SESSION["message"] || $_SESSION["info"])
		{
			if($this->blockExists("MESSAGE"))
			{
				$this->addBlockFile("MESSAGE", "message", "tpl.message.html");
#			$this->setCurrentBlock("message");

				$this->setVariable("MSG", $_SESSION["message"]);
				$this->setVariable("INFO", $_SESSION["info"]);

				session_unregister("message");
				session_unregister("info");

#			$this->parseCurrentBlock();
			}
		}
	}

	/**
	* @access	public
	* @param	string
	* @param bool fill template variable {TABS} with content of ilTabs
	*/
	function show($part = "DEFAULT",$a_fill_tabs = true)
	{
		global $ilias;

		header('Content-type: text/html; charset=UTF-8');

		$this->addErrorMessage();

		// display ILIAS footer
		if ($part !== false)
		{
			$this->addILIASFooter();
		}

		// Show tabs
		if($a_fill_tabs)
		{
			$this->fillTabs();
		}
		if ($part == "DEFAULT" or is_bool($part))
		{
			parent::show();
		}
		else
		{
			parent::show($part);
		}
		

		$this->handleReferer();
	}

	function fillTabs()
	{
		global $ilias,$ilTabs;
		
		$this->setVariable("TABS",$ilTabs->getHTML());
		$this->setVariable("SUB_TABS",$ilTabs->getSubTabHTML());
	}		
	/**
	* add ILIAS footer
	*/
	function addILIASFooter()
	{
		global $ilias;

		$this->addBlockFile("FOOTER", "footer", "tpl.footer.html");
		$this->setVariable("ILIAS_VERSION", $ilias->getSetting("ilias_version"));
		if (DEVMODE)
		{
			$mem_usage = "";
			if(function_exists("memory_get_usage"))
			{
				$mem_usage.=
					"<br /> Memory Usage: ".memory_get_usage()." Bytes";
			}
			if(function_exists("xdebug_peak_memory_usage"))
			{
				$mem_usage.=
					"<br /> XDebug Peak Memory Usage: ".xdebug_peak_memory_usage()." Bytes";
			}
			if ($mem_usage != "")
			{
				$this->setVariable("MEMORY_USAGE", $mem_usage);
			}
			
			if (version_compare(PHP_VERSION,'5','>='))
			{
				$this->setVariable("VALIDATION_LINKS",
					'<br /><a href="'.
					ilUtil::appendUrlParameterString($_SERVER["REQUEST_URI"], "do_dev_validate=xhtml").
					'">Validate</a> | <a href="'.
					ilUtil::appendUrlParameterString($_SERVER["REQUEST_URI"], "do_dev_validate=accessibility").
					'">Accessibility</a>');
			}
			if (!empty($_GET["do_dev_validate"]))
			{
				require_once("Services/XHTMLValidator/classes/class.ilValidatorAdapter.php");
				$template2 = ilPHP::cloneObject($this);
//echo "-".ilValidatorAdapter::validate($template2->get(), $_GET["do_dev_validate"])."-";
				$this->setCurrentBlock("xhtml_validation");
				$this->setVariable("VALIDATION",
					ilValidatorAdapter::validate($template2->get(), $_GET["do_dev_validate"]));
				$this->parseCurrentBlock();
			}
		}
	}


	/**
	* TODO: this is nice, but shouldn't be done here
	* (-> maybe at the end of ilias.php!?, alex)
	*/
	function handleReferer()
	{
		if (((substr(strrchr($_SERVER["PHP_SELF"],"/"),1) != "error.php")
			&& (substr(strrchr($_SERVER["PHP_SELF"],"/"),1) != "adm_menu.php")
			&& (substr(strrchr($_SERVER["PHP_SELF"],"/"),1) != "chat.php")))
		{
			$_SESSION["post_vars"] = $_POST;

			// referer is modified if query string contains cmd=gateway and $_POST is not empty.
			// this is a workaround to display formular again in case of error and if the referer points to another page
			$url_parts = parse_url($_SERVER["REQUEST_URI"]);

			if (preg_match("/cmd=gateway/",$url_parts["query"]) && (isset($_POST["cmd"]["create"])))
			{
				foreach ($_POST as $key => $val)
				{
					if (is_array($val))
					{
						$val = key($val);
					}

					$str .= "&".$key."=".$val;
				}

				$_SESSION["referer"] = preg_replace("/cmd=gateway/",substr($str,1),$_SERVER["REQUEST_URI"]);
			}
			else if (preg_match("/cmd=post/",$url_parts["query"]) && (isset($_POST["cmd"]["create"])))
			{
				foreach ($_POST as $key => $val)
				{
					if (is_array($val))
					{
						$val = key($val);
					}

					$str .= "&".$key."=".$val;
				}

				$_SESSION["referer"] = preg_replace("/cmd=post/",substr($str,1),$_SERVER["REQUEST_URI"]);
			}
			else
			{
				$_SESSION["referer"] = $_SERVER["REQUEST_URI"];
			}

			unset($_SESSION["error_post_vars"]);
		}
	}

	/**
	* check if block exists in actual template
	* @access	private
	* @param string blockname
	* @return	boolean
	*/
	function blockExists($a_blockname)
	{
		return $this->blockvariables["content"][$a_blockname] ? true : false;
	}


	/**
	* all template vars defined in $vars will be replaced automatically
	* without setting and parsing them with setVariable & parseCurrentBlock
	* @access	private
	* @return	integer
	*/
	function fillVars()
	{
		$count = 0;
		reset($this->vars);

		while(list($key, $val) = each($this->vars))
		{
			if (is_array($this->blockvariables[$this->activeBlock]))
			{
				if  (array_key_exists($key, $this->blockvariables[$this->activeBlock]))
				{
					$count++;

					$this->setVariable($key, $val);
				}
			}
		}
		
		return $count;
	}
	
	/**
	* �berladene Funktion, die sich hier lokal noch den aktuellen Block merkt.
	* @access	public
	* @param	string
	* @return	???
	*/
	function setCurrentBlock ($part = "DEFAULT")
	{
		$this->activeBlock = $part;

		if ($part == "DEFAULT")
		{
			return parent::setCurrentBlock();
		}
		else
		{
			return parent::setCurrentBlock($part);
		}
	}

	/**
	* overwrites ITX::touchBlock.
	* @access	public
	* @param	string
	* @return	???
	*/
	function touchBlock($block)
	{
		$this->setCurrentBlock($block);
		$count = $this->fillVars();
		$this->parseCurrentBlock();

		if ($count == 0)
		{
			parent::touchBlock($block);
		}
	}

	/**
	* �berladene Funktion, die auf den aktuelle Block vorher noch ein replace ausfhrt
	* @access	public
	* @param	string
	* @return	string
	*/
	function parseCurrentBlock($part = "DEFAULT")
	{
		// Hier erst noch ein replace aufrufen
		if ($part != "DEFAULT")
		{
			$tmp = $this->activeBlock;
			$this->activeBlock = $part;
		}

		if ($part != "DEFAULT")
		{
			$this->activeBlock = $tmp;
		}

		$this->fillVars();

		$this->activeBlock = "__global__";

		if ($part == "DEFAULT")
		{
			return parent::parseCurrentBlock();
		}
		else
		{
			return parent::parseCurrentBlock($part);
		}
	}

	/**
	* ???
	* TODO: Adjust var names to ilias. This method wasn't used so far
	* and isn't translated yet
	* @access	public
	* @param	string
	* @param	string
	* @param	string
	* @param	string
	*/
	function replaceFromDatabase(&$DB,$block,$conv,$select="default")
	{
		$res = $DB->selectDbAll();

		while ($DB->getDbNextElement($res))
		{
			$this->setCurrentBlock($block);
			$result = array();
			reset($conv);

			while (list ($key,$val) = each ($conv))
			{
				$result[$val]=$DB->element->data[$key];
			}

			if (($select != "default")
				&& ($DB->element->data[$select["id"]]==$select["value"]
				|| (strtolower($select["text"]) == "checked"
				&& strpos( ",,".$select["value"].",," , ",".$DB->element->data[$select["id"]]."," )!=false)))
			{
				$result[$select["field"]] = $select["text"];
			}

			$this->replace($result);
			$this->parseCurrentBlock($block);
		}
	}

	/**
	* Wird angewendet, wenn die Daten in ein Formular replaced werden sollen,
	* Dann wird erst noch ein htmlspecialchars drumherum gemacht.
	* @access	public
	* @param	string
	*/
	function prepareForFormular($vars)
	{
		if (!is_array($vars))
		{
			return;
		}

		reset($vars);

		while (list($i) = each($vars))
		{
			$vars[$i] = stripslashes($vars[$i]);
			$vars[$i] = htmlspecialchars($vars[$i]);
		}

		return($vars);
	}

	/**
	* ???
	* @access	public
	*/
	function replace()
	{
		reset($this->vars);

		while(list($key, $val) = each($this->vars))
		{
			$this->setVariable($key, $val);
		}
	}

	/**
	* ???
	* @access	public
	*/
	function replaceDefault()
	{
		$this->replace($this->vars);
	}

	/**
	* checks for a topic in the template
	* @access	private
 	* @param	string
	* @param	string
	* @return	boolean
	*/
	function checkTopic($a_block, $a_topic)
	{
		return array_key_exists($a_topic, $this->blockvariables[$a_block]);
	}

	/**
	* check if there is a NAVIGATION-topic
	* @access	public
	* @return	boolean
	*/
	function includeNavigation()
	{
		return $this->checkTopic("__global__", "NAVIGATION");
	}

	/**
	* check if there is a TREE-topic
	* @access	public
	* @return	boolean
	*/
	function includeTree()
	{
		return $this->checkTopic("__global__", "TREE");
	}

	/**
	* check if a file exists
	* @access	public
	* @return	boolean
	*/
	function fileExists($filename)
	{
		return file_exists($this->tplPath."/".$filename);
	}


	/**
	* overwrites ITX::addBlockFile
	* @access	public
	* @param	string
	* @param	string
	* @param	string		$tplname		template name
	* @param	boolean		$in_module		should be set to true, if template file is in module subdirectory
	* @return	boolean/string
	*/
	function addBlockFile($var, $block, $tplname, $in_module = false)
	{
		if (DEBUG)
		{
			echo "<br/>Template '".$this->tplPath."/".$tplname."'";
		}

		$tplfile = $this->getTemplatePath($tplname, $in_module);
		if (file_exists($tplfile) == false)
		{
			echo "<br/>Template '".$tplfile."' doesn't exist! aborting...";
			return false;
		}

		return parent::addBlockFile($var, $block, $tplfile);
	}

	/**
	* builds a full template path with template and module name
	*
	* @param	string		$a_tplname		template name
	* @param	boolean		$in_module		should be set to true, if template file is in module subdirectory
	*
	* @return	string		full template path
	*/
	function getTemplatePath($a_tplname, $a_in_module = false)
	{
		global $ilias, $ilCtrl;
		
		// if baseClass functionality is used (ilias.php):
		// get template directory from ilCtrl
		if (!empty($_GET["baseClass"]) && $a_in_module === true)
		{
			$a_in_module = $ilCtrl->getModuleDir();
		}

		if (strpos($a_tplname,"/") === false)
		{
			//$fname = $ilias->tplPath;
			$base = "./";
			if ($a_in_module)
			{
				if ($a_in_module === true)
				{
					$base.= ILIAS_MODULE."/";
				}
				else
				{
					$base.= $a_in_module."/";
				}
			}
			$base .= "templates/";
			$fname = $base.$ilias->account->skin."/".basename($a_tplname);
			//echo "looking for :$fname:<br>";
			if(!file_exists($fname))
			{
				$fname = $base."default/".basename($a_tplname);
			}
		}
		else
		{
			$fname = $a_tplname;
		}
		return $fname;
	}
	
	function setHeaderPageTitle($a_title)
	{
		$this->setVariable("PAGETITLE", $a_title);
	}
	
	function setStyleSheetLocation($a_stylesheet)
	{
		$this->setVariable("LOCATION_STYLESHEET", $a_stylesheet);
	}
	
	function getStandardTemplate()
	{
		$this->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
		$this->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
	}
	
	/**
	* sets title in standard template
	*/
	function setTitle($a_title)
	{
		$this->setVariable("HEADER", $a_title);
	}
	
	/**
	* set title icon
	*/
	function setTitleIcon($a_icon_path)
	{
		$this->setCurrentBlock("header_image");
		$this->setVariable("IMG_HEADER", $a_icon_path);
		$this->parseCurrentBlock();
	}
	
	/**
	* sets title in standard template
	*/
	function setDescription($a_descr)
	{
		$this->setVariable("H_DESCRIPTION", $a_descr);
	}
	
	/**
	* stop floating (if no tabs are used)
	*/
	function stopTitleFloating()
	{
		$this->touchBlock("stop_floating");
	}
	
	/**
	* sets content for standard template
	*/
	function setContent($a_html)
	{
		$this->setVariable("ADM_CONTENT", $a_html);
	}
	
	/**
	* insert locator
	* (add 
	*/
	function setLocator()
	{
		global $ilLocator;
		
		$this->addBlockFile("LOCATOR", "locator", "tpl.locator.html");
		
		$items = $ilLocator->getItems();
		$first = true;
		foreach($items as $item)
		{
			if (!$first)
			{
				$this->touchBlock("locator_separator_prefix");
			}
			
			$this->setCurrentBlock("locator_item");
			if ($item["link"] != "")
			{
				$this->setVariable("LINK_ITEM", $item["link"]);
				if ($item["frame"] != "")
				{
					$this->setVariable("LINK_TARGET", ' target="'.$item["frame"].'" ');
				}
				$this->setVariable("ITEM", $item["title"]);
			}
			else
			{
				$this->setVariable("PREFIX", $item["title"]);
			}
			$this->parseCurrentBlock();
			
			$first = false;
		}
	}
	
	/**
	* sets tabs in standard template
	*/
	function setTabs($a_tabs_html)
	{
		$this->setVariable("TABS", $a_tabs_html);
	}

	/**
	* sets subtabs in standard template
	*/
	function setSubTabs($a_tabs_html)
	{
		$this->setVariable("SUB_TABS", $a_tabs_html);
	}
	
	/**
	* sets icon to upper level
	*/
	function setUpperIcon($a_link)
	{
		$this->setCurrentBlock("top");
		$this->setVariable("LINK_TOP", $a_link);
		$this->setVariable("IMG_TOP",ilUtil::getImagePath("ic_top.gif"));
		$this->parseCurrentBlock();
	}
	
	/**
	* set tree/flat icon
	*/
	function setTreeFlatIcon($a_link, $a_mode)
	{
		$this->setCurrentBlock("tree_mode");
		$this->setVariable("LINK_MODE", $a_link);
		$this->setVariable("IMG_TREE",ilUtil::getImagePath("ic_".$a_mode."view.gif"));
		$this->parseCurrentBlock();
	}


}
?>
