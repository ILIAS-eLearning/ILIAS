<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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

require_once ("content/classes/Pages/class.ilPageEditorGUI.php");
require_once("./content/classes/Pages/class.ilPageObject.php");
require_once("./classes/class.ilDOMUtil.php");


/**
* Class ilPageObjectGUI
*
* User Interface for Page Objects Editing
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package content
*/
class ilPageObjectGUI
{
	var $ilias;
	var $tpl;
	var $lng;
	var $ctrl;
	var $obj;
	var $output_mode;
	var $output_submode;
	var $presentation_title;
	var $target_script;
	var $return_location;
	var $target_var;
	var $template_output_var;
	var $output2template;
	var $link_params;
	var $bib_id;
	var $citation;
	var $sourcecode_download_script;

	/**
	* Constructor
	* @access	public
	*/
	function ilPageObjectGUI(&$a_page_object)
	{
		global $ilias, $tpl, $lng, $ilCtrl;

		$this->ctrl =& $ilCtrl;

		$this->ilias =& $ilias;
		$this->tpl =& $tpl;
		$this->ctrl =& $ilCtrl;
		$this->lng =& $lng;
		$this->obj =& $a_page_object;
		$this->output_mode = "presentation";
		$this->setPageObject($a_page_object);
		$this->output2template = true;

		// USED FOR TRANSLATIONS
		$this->template_output_var = "PAGE_CONTENT";
		$this->citation = false;
	}

	/**
	* get all gui classes that are called from this one (see class ilCtrl)
	*
	* @param	array		array of gui classes that are called
	*/
	function _forwards()
	{
		return array("ilPageEditorGUI");
	}


	function setBibId($a_id)
	{
		// USED FOR SELECTION WHICH PAGE TURNS AND LATER PAGES SHOULD BE SHOWN
		$this->bib_id = $a_id;
	}

	function getBibId()
	{
		return $this->bib_id ? $this->bib_id : 0;
	}

	function setPageObject(&$a_pg_obj)
	{
		$this->obj =& $a_pg_obj;
	}

	function &getPageObject()
	{
		return $this->obj;
	}

	/**
	* mode: "presentation" | "edit" | "preview"
	*/
	function setOutputMode($a_mode = "presentation")
	{
		$this->output_mode = $a_mode;
	}

	function getOutputMode()
	{
		return $this->output_mode;
	}

	function setTemplateOutput($a_output = true)
	{
		$this->output2template = $a_output;
	}

	function outputToTemplate()
	{
		return $this->output2template;
	}

	function setPresentationTitle($a_title = "")
	{
		$this->presentation_title = $a_title;
	}

	function getPresentationTitle()
	{
		return $this->presentation_title;
	}

	function setHeader($a_title = "")
	{
		$this->header = $a_title;
	}

	function getHeader()
	{
		return $this->header;
	}

	function setLinkParams($l_params = "")
	{
		$this->link_params = $l_params;
	}

	function getLinkParams()
	{
		return $this->link_params;
	}

	function setLinkFrame($l_frame = "")
	{
		$this->link_frame = $l_frame;
	}

	function getLinkFrame()
	{
		return $this->link_frame;
	}

	function setLinkXML($link_xml)
	{
		$this->link_xml = $link_xml;
	}

	function getLinkXML()
	{
		return $this->link_xml;
	}

	function setTemplateTargetVar($a_variable)
	{
		$this->target_var = $a_variable;
	}

	function getTemplateTargetVar()
	{
		return $this->target_var;
	}

	function setTemplateOutputVar($a_value)
	{
		// USED FOR TRANSLATION PRESENTATION OF dbk OBJECTS
		$this->template_output_var = $a_value;
	}

	function getTemplateOutputVar()
	{
		return $this->template_output_var;
	}

	function setOutputSubmode($a_mode)
	{
		// USED FOR TRANSLATION PRESENTATION OF dbk OBJECTS
		$this->output_submode = $a_mode;
	}

	function getOutputSubmode()
	{
		return $this->output_submode;
	}

	
	function setSourcecodeDownloadScript ($script_name) {
		$this->sourcecode_download_script = $script_name;
	}
	
	function getSourcecodeDownloadScript () {
		return $this->sourcecode_download_script;
	}

	
	

	function enableCitation($a_enabled)
	{
		$this->citation = $a_enabled;
	}

	function isEnabledCitation()
	{
		return $this->citation;
	}

	function setLocator(&$a_locator)
	{
		$this->locator =& $a_locator;
	}

	function setTabs($a_tabs)
	{
		$this->tabs = $a_tabs;
	}

	function setFileDownloadLink($a_download_link)
	{
		$this->file_download_link = $a_download_link;
	}

	function getFileDownloadLink()
	{
		return $this->file_download_link;
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);
//echo ":$next_class:";
		$cmd = $this->ctrl->getCmd();
		switch($next_class)
		{
			case "ilpageeditorgui":
				$page_editor =& new ilPageEditorGUI($this->getPageObject());
				$page_editor->setLocator($this->locator);
				$page_editor->setHeader($this->getHeader());
				$page_editor->executeCommand();
				break;

			default:

				$ret =& $this->$cmd();
				break;
		}
	}


	/*
	* display content of page
	*/
	function showPage()
	{
		global $tree, $ilUser;

		// init template
		if($this->outputToTemplate())
		{
			if($this->getOutputMode() == "edit")
			{
//echo ":".$this->getTemplateTargetVar().":";
				$this->tpl->addBlockFile($this->getTemplateTargetVar(), "adm_content", "tpl.page_edit_wysiwyg.html", true);
				$this->tpl->setVariable("TXT_CHANGE_EDIT_MODE", $this->lng->txt("cont_set_edit_mode"));
				$med_mode = array("enable" => $this->lng->txt("cont_enable_media"),
					"disable" => $this->lng->txt("cont_disable_media"));
				$sel_media_mode = ($ilUser->getPref("ilPageEditor_MediaMode") == "disable")
					? "disable"
					: "enable";
				$this->tpl->setVariable("SEL_MEDIA_MODE",
					ilUtil::formSelect($sel_media_mode, "media_mode", $med_mode, false, true));
			}
			else
			{
				if($this->getOutputSubmode() == 'translation')
				{
					$this->tpl->addBlockFile($this->getTemplateTargetVar(), "adm_content", "tpl.page_translation_content.html", true);
				}
				else
				{
					$this->tpl->addBlockFile($this->getTemplateTargetVar(), "adm_content", "tpl.page_content.html", true);
				}
			}

			$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormActionByClass("ilpageeditorgui"));
		}

		// get content
		$builded = $this->obj->buildDom();

		$this->obj->addFileSizes();
		if($this->getOutputMode() == "edit")
		{
			$this->obj->addHierIDs();
		}
		

		$this->obj->addSourceCodeHighlighting();

		$content = $this->obj->getXMLFromDom(false, true, true, $this->getLinkXML());

		// check validation errors
		if($builded !== true)
		{
			$this->displayValidationError($builded);
		}
		else
		{
			$this->displayValidationError($_SESSION["il_pg_error"]);
		}
		unset($_SESSION["il_pg_error"]);

		if(isset($_SESSION["citation_error"]))
		{
			sendInfo($this->lng->txt("cont_citation_selection_not_valid"));
			session_unregister("citation_error");
			unset($_SESSION["citation_error"]);
		}


		// get title
		$pg_title = $this->getPresentationTitle();

		// run xslt
		$xsl = file_get_contents("./content/page.xsl");
		$args = array( '/_xml' => $content, '/_xsl' => $xsl );
		$xh = xslt_create();
//echo "<b>XML</b>:".htmlentities($content).":<br>";
//echo "<b>XSLT</b>:".htmlentities($xsl).":<br>";
//echo "mode:".$this->getOutputMode().":<br>";
		$enlarge_path = ilUtil::getImagePath("enlarge.gif");
		$med_disabled_path = ilUtil::getImagePath("media_disabled.gif");
		$wb_path = ilUtil::getWebspaceDir("output");
//$wb_path = "../".$this->ilias->ini->readVariable("server","webspace_dir");
		$params = array ('mode' => $this->getOutputMode(), 'pg_title' => $pg_title, 'pg_id' => $this->obj->getId(),
						 'webspace_path' => $wb_path, 'enlarge_path' => $enlarge_path, 'link_params' => $this->link_params,
						 'file_download_link' => $this->getFileDownloadLink(),
						 'med_disabled_path' => $med_disabled_path,
						 'download_script' => $this->sourcecode_download_script,
						 'bib_id' => $this->getBibId(),'citation' => (int) $this->isEnabledCitation(),
						 'media_mode' => $ilUser->getPref("ilPageEditor_MediaMode"));

		if($this->link_frame != "")		// todo other link types
			$params["pg_frame"] = $this->link_frame;

		$output = xslt_process($xh,"arg:/_xml","arg:/_xsl",NULL,$args, $params);
//echo xslt_error($xh);
		xslt_free($xh);

		// unmask user html
		$output = str_replace("&lt;","<",$output);
		$output = str_replace("&gt;",">",$output);
		$output = str_replace("&amp;", "&", $output);

		// (horrible) workaround for preventing template engine
		// from hiding paragraph text that is enclosed
		// in curly brackets (e.g. "{a}", see ilLMEditorGUI::executeCommand())
		$output = str_replace("{", "&#123;", $output);
		$output = str_replace("}", "&#125;", $output);

//echo "<b>HTML</b>:".htmlentities($output).":<br>";

		// remove all newlines (important for code / pre output)
		$output = str_replace("\n", "", $output);

		// output
		if($this->outputToTemplate())
		{
			$this->tpl->setVariable($this->getTemplateOutputVar(), $output);
            return $output;
		}
		else
		{
			return $output;
		}
	}

	/*
	* preview
	*/
	function preview()
	{
		global $tree;
		$this->setOutputMode("preview");
		return $this->showPage();
	}

	/*
	* edit
	*/
	function view()
	{
		global $tree;
		$this->setOutputMode("edit");
		return $this->showPage();
	}

	/*
	* presentation
	*/
	function presentation()
	{
		global $tree;
		$this->setOutputMode("presentation");
		return $this->showPage();
	}


	/**
	* save page
	*/
	/*
	function save()
	{
		// create new object
		$meta_gui =& new ilMetaDataGUI();
		$meta_data =& $meta_gui->create();
		$this->obj =& new ilPageObject();
		$this->obj->assignMetaData($meta_data);
		$this->obj->setType($_GET["new_type"]);
		$this->obj->setLMId($this->lm_obj->getId());
		$this->obj->create();

		// obj_id is empty, if page is created from "all pages" screen
		// -> a free page is created (not in the tree)
		if (empty($_GET["obj_id"]))
		{
			ilUtil::redirect("lm_edit.php?cmd=pages&ref_id=".$this->lm_obj->getRefId());
		}
		else
		{
			$this->putInTree();
			ilUtil::redirect("lm_edit.php?cmd=view&ref_id=".$this->lm_obj->getRefId()."&obj_id=".
				$_GET["obj_id"]);
		}
	}*/

	/**
	* display validation error
	*
	* @param	string		$a_error		error string
	*/
	function displayValidationError($a_error)
	{
		if(is_array($a_error))
		{
			$error_str = "<b>Validation Error(s):</b><br>";
			foreach ($a_error as $error)
			{
				$err_mess = implode($error, " - ");
				if (!is_int(strpos($err_mess, ":0:")))
				{
					$error_str .= htmlentities($err_mess)."<br />";
				}
			}
			$this->tpl->setVariable("MESSAGE", $error_str);
		}
	}

}
?>
