<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
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
*/


class ilTemplate extends ilTemplateX
{
	/**
	* variablen die immer in jedem block ersetzt werden sollen
	* @var	array
	*/
	var $vars;
	var $js_files = array(0 => "./Services/JavaScript/js/Basic.js");		// list of JS files that should be included
	
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
	/*function ilTemplate($root)
	{
		
		$this->callConstructor();
		
		$this->setRoot($root);

		return true;
	}*/
	function ilTemplate($file,$flag1,$flag2,$in_module = false, $vars = "DEFAULT")
	{
		$this->activeBlock = "__global__";
		$this->vars = array();

		$fname = $this->getTemplatePath($file, $in_module);

		$this->tplName = basename($fname);
		$this->tplPath = dirname($fname);
		// set default content-type to text/html
		$this->contenttype = "text/html";
		if (!file_exists($fname))
		{
			die("template ".$fname." was not found.");
			return false;
		}

		//$this->IntegratedTemplateExtension(dirname($fname));
		$this->callConstructor();
		//$this->loadTemplatefile(basename($fname), $flag1, $flag2);
		$this->loadTemplatefile($fname, $flag1, $flag2);
		//add tplPath to replacevars
		$this->vars["TPLPATH"] = $this->tplPath;
		
		// set Options
		if (method_exists($this, "setOption"))
		{
			$this->setOption('use_preg', false);
		}

		return true;
	}

	/**
	* builds a full template path with template and module name
	*
	* @param	string		$a_tplname		template name
	* @param	boolean		$in_module		should be set to true, if template file is in module subdirectory
	*
	* @return	string		full template path
	*/
	function getTemplatePath($a_tplname, $a_in_module = false, $a_plugin = false)
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
			$module_path = "";
			
			//$fname = $ilias->tplPath;
			if ($a_in_module)
			{
				if ($a_in_module === true)
				{
					$module_path = ILIAS_MODULE."/";
				}
				else
				{
					$module_path = $a_in_module."/";
				}
			}

			if($fname == "" || !file_exists($fname))
			{
				if ($a_in_module == "setup")
				{
					$fname = "./".$module_path."templates/".basename($a_tplname);
				}
				else
				{
					$fname = "./".$module_path."templates/default/".basename($a_tplname);
				}
			}
		}
		else
		{
			$fname = $a_tplname;
		}
		
		return $fname;
	}

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
	* @access	public
	* @param	string
	*/
	function show($part = "DEFAULT")
	{
		header('Content-type: text/html; charset=UTF-8');

		$this->fillJavaScriptFiles();
		
		// ERROR HANDLER SETS $_GET["message"] IN CASE OF $error_obj->MESSAGE
		if ($_SESSION["message"] || $_SESSION["info"])
		{
			if ($this->blockExists("MESSAGE"))
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
		if ($part == "DEFAULT")
		{
			parent::show();
		}
		else
		{
			parent::show($part);
		}

		if (((substr(strrchr($_SERVER["PHP_SELF"],"/"),1) != "error.php")
			&& (substr(strrchr($_SERVER["PHP_SELF"],"/"),1) != "adm_menu.php")))
		{
			$_SESSION["post_vars"] = $_POST;

			// referer is modified if query string contains cmd=gateway and $_POST is not empty.
			// this is a workaround to display formular again in case of error and if the referer points to another page
			$url_parts = parse_url($_SERVER["REQUEST_URI"]);

			if (preg_match("/cmd=gateway/",$url_parts["query"]))
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
			else
			{
				$_SESSION["referer"] = $_SERVER["REQUEST_URI"];
			}

			unset($_SESSION["error_post_vars"]);
		}
	}

	/**
	* Set message. Please use ilUtil::sendInfo(), ilUtil::sendSuccess()
	* and ilUtil::sendFailure()
	*/
	function setMessage($a_type, $a_txt, $a_keep = false)
	{
		if (!in_array($a_type, array("info", "success", "failure", "question")) || $a_txt == "")
		{
			return;
		}
		if ($a_type == "question")
		{
			$a_type = "mess_question";
		}
		if (!$a_keep)
		{
			$this->message[$a_type] = $a_txt;
		}
		else
		{
			$_SESSION[$a_type] = $a_txt;
		}
	}
	
	function fillMessage()
	{
		global $lng;
		
		$ms = array("info", "success", "failure", "question");
		$out = "";
		
		foreach ($ms as $m)
		{
			if ($m == "question")
			{
				$m = "mess_question";
			}

			$txt = ($_SESSION[$m] != "")
				? $_SESSION[$m]
				: $this->message[$m];
				
			if ($m == "mess_question")
			{
				$m = "question";
			}

			if ($txt != "")
			{
				$mtpl = new ilTemplate("tpl.message.html", true, true, "Services/Utilities");
				$mtpl->setCurrentBlock($m."_message");
				$mtpl->setVariable("TEXT", $txt);
				$mtpl->setVariable("MESSAGE_HEADING", $lng->txt($m."_message"));
				$mtpl->setVariable("ALT_IMAGE", $lng->txt("icon")." ".$lng->txt($m."_message"));
				$mtpl->setVariable("SRC_IMAGE", ilUtil::getImagePath("mess_".$m.".gif"));
				$mtpl->parseCurrentBlock();
				$out.= $mtpl->get();
			}
		
			if ($m == "question")
			{
				$m = "mess_question";
			}

			if ($_SESSION[$m])
			{
				session_unregister($m);
			}
		}
		
		if ($out != "")
		{
			$this->setVariable("MESSAGE", $out);
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
	* Add a javascript file that should be included in the header.
	*/
	function addJavaScript($a_js_file)
	{
		if (!in_array($a_js_file, $this->js_files))
		{
			$this->js_files[] = $a_js_file;
		}
	}

	function fillJavaScriptFiles()
	{
		global $ilias,$ilTabs;
		if ($this->blockExists("js_file"))
		{
			foreach($this->js_files as $file)
			{
				if (is_file($file) || substr($file, 0, 4) == "http")
				{
					$this->setCurrentBlock("js_file");
					$this->setVariable("JS_FILE", $file);
					$this->parseCurrentBlock();
				}
			}
		}
	}
	
	function get($part = "DEFAULT")
	{
		if ($part == "DEFAULT")
		{
			return parent::get();
		}
		else
		{
			return parent::get($part);
		}
	}


}
?>
