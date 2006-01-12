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


/**
* User Interface class for media pool objects
*
* @author Alex Killing <alex.killing@gmx.de>
*
* $Id$
*
* @ilCtrl_Calls ilObjMediaPoolGUI: ilObjMediaObjectGUI, ilObjFolderGUI, ilEditClipboardGUI, ilPermissionGUI
*
* @extends ilObjectGUI
* @package content
*/

include_once("classes/class.ilObjectGUI.php");
include_once("content/classes/class.ilObjMediaPool.php");
include_once("classes/class.ilTableGUI.php");
include_once("classes/class.ilObjFolderGUI.php");
include_once("content/classes/Media/class.ilObjMediaObjectGUI.php");
include_once("content/classes/Media/class.ilObjMediaObject.php");
include_once ("content/classes/class.ilEditClipboardGUI.php");

class ilObjMediaPoolGUI extends ilObjectGUI
{
	var $output_prepared;

	/**
	* Constructor
	*
	* @access	public
	*/
	function ilObjMediaPoolGUI($a_data,$a_id = 0,$a_call_by_reference = true, $a_prepare_output = false)
	{
		global $lng, $ilCtrl;

//echo "<br>ilobjmediapoolgui-constructor-id-$a_id";

		$this->ctrl =& $ilCtrl;
		$this->ctrl->saveParameter($this, array("ref_id", "obj_id"));
		$this->type = "mep";
		$lng->loadLanguageModule("content");
		parent::ilObjectGUI($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

		$this->output_prepared = $a_prepare_output;

		//if (defined("ILIAS_MODULE"))
		//{
		//	$this->setTabTargetScript("mep_edit.php");
		//}
//echo "ilobjmediapoolgui-".get_class($this->object)."-";
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		if ($this->ctrl->getRedirectSource() == "ilinternallinkgui")
		{
			$this->explorer();
			return;
		}
//echo "-".$cmd."-";

		if (!$this->creation_mode)
		{
			$tree =& $this->object->getTree();
		}
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
		
		if ($cmd == "create")
		{
			switch($_POST["new_type"])
			{
				case "mob":
					$this->ctrl->redirectByClass("ilobjmediaobjectgui", "create");
					break;
					
				case "fold":
					$this->ctrl->redirectByClass("ilobjfoldergui", "create");
					break;
			}
		}

		switch($next_class)
		{
			case "ilobjmediaobjectgui":

				//$cmd.="Object";
				if ($cmd == "create")
				{
					$ret_obj = $_GET["obj_id"];
					$ilObjMediaObjectGUI =& new ilObjMediaObjectGUI("", 0, false, false);
				}
				else
				{
					$ret_obj = $tree->getParentId($_GET["obj_id"]);
					$ilObjMediaObjectGUI =& new ilObjMediaObjectGUI("", $_GET["obj_id"], false, false);
				}
				if ($this->ctrl->getCmdClass() == "ilinternallinkgui")
				{
					$this->ctrl->setReturn($this, "explorer");
				}
				else
				{
					$this->ctrl->setParameter($this, "obj_id", $ret_obj);
					$this->ctrl->setReturn($this, "listMedia");
					$this->ctrl->setParameter($this, "obj_id", $_GET["obj_id"]);
				}
				$this->getTemplate();
				$ilObjMediaObjectGUI->setAdminTabs();
				$this->setLocator();

//echo ":".$tree->getParentId($_GET["obj_id"]).":";
				//$ret =& $ilObjMediaObjectGUI->executeCommand();
				$ret =& $this->ctrl->forwardCommand($ilObjMediaObjectGUI);
//echo "<br>ilObjMediaPoolGUI:afterexecute:<br>"; exit;
				switch($cmd)
				{
					case "save":
						$parent = ($_GET["obj_id"] == "")
							? $tree->getRootId()
							: $_GET["obj_id"];
						$tree->insertNode($ret->getId(), $parent);
						ilUtil::redirect("mep_edit.php?cmd=listMedia&ref_id=".
							$_GET["ref_id"]."&obj_id=".$_GET["obj_id"]);
						break;

					default:
						$this->tpl->show();
						break;
				}
				break;

			case "ilobjfoldergui":
				$folder_gui = new ilObjFolderGUI("", 0, false, false);
				$this->ctrl->setReturn($this, "listMedia");
				$cmd.="Object";
				switch($cmd)
				{
					case "createObject":
						$this->prepareOutput();
						$folder_gui =& new ilObjFolderGUI("", 0, false, false);
						$folder_gui->setFormAction("save",
							$this->ctrl->getFormActionByClass("ilobjfoldergui"));
						$folder_gui->createObject();
						$this->tpl->show();
						break;

					case "saveObject":
						//$folder_gui->setReturnLocation("save", $this->ctrl->getLinkTarget($this, "listMedia"));
						$parent = ($_GET["obj_id"] == "")
							? $tree->getRootId()
							: $_GET["obj_id"];
						$folder_gui->setFolderTree($tree);
						$folder_gui->saveObject($parent);
						//$this->ctrl->redirect($this, "listMedia");
						break;

					case "editObject":
						$this->prepareOutput();
						$folder_gui =& new ilObjFolderGUI("", $_GET["obj_id"], false, false);
						$folder_gui->setFormAction("update", $this->ctrl->getFormActionByClass("ilobjfoldergui"));
						$folder_gui->editObject();
						$this->tpl->show();
						break;

					case "updateObject":
						$folder_gui =& new ilObjFolderGUI("", $_GET["obj_id"], false, false);
						//$folder_gui->setReturnLocation("update", $this->ctrl->getLinkTarget($this, "listMedia"));
						$folder_gui->updateObject(true);
						$this->ctrl->redirect($this, "listMedia");
						break;

					case "cancelObject":
						sendInfo($this->lng->txt("action_aborted"), true);
						$this->ctrl->redirect($this, "listMedia");
						break;
				}
				break;

			case "ileditclipboardgui":
				$this->prepareOutput();
				$this->ctrl->setReturn($this, "listMedia");
				$clip_gui = new ilEditClipboardGUI();
				$clip_gui->setMultipleSelections(true);
				//$ret =& $clip_gui->executeCommand();
				$ret =& $this->ctrl->forwardCommand($clip_gui);
				$this->tpl->show();
				break;
				
			case 'ilpermissiongui':
				$this->prepareOutput();
				include_once("./classes/class.ilPermissionGUI.php");
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
				$this->tpl->show();
				break;

			default:
				$this->prepareOutput();
				$cmd = $this->ctrl->getCmd("frameset");
				if ($this->creation_mode)
				{
					$cmd.= "Object";
				}
				$this->$cmd();	
				break;
		}
	}
	
	function createObject()
	{
		parent::createObject();
		$this->tpl->setVariable("TARGET", ' target="'.
				ilFrameTargetInfo::_getFrame("MainContent").'" ');
	}

	/**
	* save object
	* @access	public
	*/
	function saveObject()
	{
		global $rbacadmin;

		// create and insert forum in objecttree
		$newObj = parent::saveObject();

		// setup rolefolder & default local roles
		//$roles = $newObj->initDefaultRoles();

		// ...finally assign role to creator of object
		//$rbacadmin->assignUser($roles[0], $newObj->getOwner(), "y");

		// put here object specific stuff

		// always send a message
		sendInfo($this->lng->txt("object_added"),true);

		//ilUtil::redirect($this->getReturnLocation("save","adm_object.php?".$this->link_params));
		ilUtil::redirect("content/mep_edit.php?ref_id=".$newObj->getRefId());

	}

	/**
	* edit properties of object (admin form)
	*
	* @access	public
	*/
	function editObject()
	{
		global $rbacsystem, $tree, $tpl;

		if (!$rbacsystem->checkAccess("visible,write",$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		// edit button
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");

		if (!defined("ILIAS_MODULE"))
		{
			$this->tpl->setCurrentBlock("btn_cell");
			$this->tpl->setVariable("BTN_LINK","content/mep_edit.php?ref_id=".$this->object->getRefID());
			$this->tpl->setVariable("BTN_TARGET"," target=\"bottom\" ");
			$this->tpl->setVariable("BTN_TXT",$this->lng->txt("edit"));
			$this->tpl->parseCurrentBlock();
		}

		parent::editObject();
	}

	/**
	* edit properties of object (module form)
	*/
	function edit()
	{
		//$this->prepareOutput();
		$this->setFormAction("update", "mep_edit.php?cmd=post&ref_id=".$_GET["ref_id"].
			"&obj_id=".$_GET["obj_id"]);
		$this->editObject();
		$this->tpl->show();
	}

	/**
	* cancel editing
	*/
	function cancel()
	{
		//$this->setReturnLocation("cancel","mep_edit.php?cmd=listMedia&ref_id=".$_GET["ref_id"].
		//	"&obj_id=".$_GET["obj_id"]);
		//$this->cancelObject();
		$this->ctrl->redirect($this, "listMedia");
	}
	
	/**
	* cancel action and go back to previous page
	* @access	public
	*
	*/
	function cancelObject($in_rep = false)
	{
		sendInfo($this->lng->txt("msg_cancel"),true);
		ilUtil::redirect("repository.php?cmd=frameset&ref_id=".$_GET["ref_id"]);
		//$this->ctrl->redirectByClass("ilrepositorygui", "frameset");
	}


	/**
	* update properties
	*/
	function update()
	{
		$this->setReturnLocation("update", "mep_edit.php?cmd=listMedia&ref_id=".$_GET["ref_id"].
			"&obj_id=".$_GET["obj_id"]);
		$this->updateObject();
	}

	/**
	* permission form
	*/
	/*
	function perm()
	{
		//$this->prepareOutput();
		$this->setFormAction("permSave", "mep_edit.php?cmd=permSave&ref_id=".$_GET["ref_id"].
			"&obj_id=".$_GET["obj_id"]);
		$this->setFormAction("addRole", "mep_edit.php?ref_id=".$_GET["ref_id"].
			"&obj_id=".$_GET["obj_id"]."&cmd=addRole");
		$this->permObject();
		$this->tpl->show();
	}*/

	/**
	* info form
	*/
	function info()
	{
		//$this->prepareOutput();
		$this->infoObject();
		$this->tpl->show();
	}

	/**
	* save permissions
	*/
	function permSave()
	{
		$this->setReturnLocation("permSave",
			"mep_edit.php?ref_id=".$_GET["ref_id"]."&obj_id=".$_GET["obj_id"]."&cmd=perm");
		$this->permSaveObject();
	}

	/**
	* add role
	*/
	function addRole()
	{
		$this->setReturnLocation("addRole",
			"mep_edit.php?ref_id=".$_GET["ref_id"]."&obj_id=".$_GET["obj_id"]."&cmd=perm");
		$this->addRoleObject();
	}

	/**
	* show owner of media pool
	*/
	/*
	function owner()
	{
		$this->prepareOutput();
		$this->ownerObject();
		$this->tpl->show();
	}*/

	/**
	* list media objects
	*/
	function listMedia()
	{
		global $tree;

		if (!$this->output_prepared)
		{
			//$this->prepareOutput();
		}

		//add template for view button
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");

		/*
		// create folder form button
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK",
			$this->ctrl->getLinkTargetByClass("ilobjfoldergui", "create"));
		$this->tpl->setVariable("BTN_TXT",$this->lng->txt("cont_create_folder"));
		$this->tpl->parseCurrentBlock();

		// create mob form button
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK",
			$this->ctrl->getLinkTargetByClass("ilobjmediaobjectgui", "create"));
		$this->tpl->setVariable("BTN_TXT",$this->lng->txt("cont_create_mob"));
		$this->tpl->parseCurrentBlock();*/

		$obj_id = ($_GET["obj_id"] == "")
			? $obj_id = $this->object->tree->getRootId()
			: $_GET["obj_id"];

		// create table
		require_once("classes/class.ilTableGUI.php");
		$tbl = new ilTableGUI();

		// load files templates
		$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.table.html");

		// load template for table content data
		$this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.mep_list_row.html", true);

		$num = 0;

		$this->tpl->setVariable("FORMACTION", "mep_edit.php?ref_id=".$_GET["ref_id"].
			"&obj_id=".$_GET["obj_id"]."&cmd=post");

		$tbl->setHeaderNames(array("", "", $this->lng->txt("title")));

		$cols = array("", "", "title");
		$header_params = array("ref_id" => $_GET["ref_id"],
			"obj_id" => $_GET["obj_id"], "cmd" => "listMedia");
		$tbl->setHeaderVars($cols, $header_params);
		$tbl->setColumnWidth(array("1%", "1%", "98%"));

		if ($obj_id != $this->object->tree->getRootId())
		{
			$node = $this->object->tree->getNodeData($obj_id);
			$tbl->setTitle($node["title"]);
		}
		else
		{
			$tbl->setTitle($this->object->getTitle());
		}
		
		// control
		$tbl->setOrderColumn($_GET["sort_by"]);
		$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setMaxCount($this->maxcount);		// ???

		$this->tpl->setVariable("COLUMN_COUNTS", 3);

		// remove button
		$this->tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));
		$this->tpl->setCurrentBlock("tbl_action_btn");
		$this->tpl->setVariable("BTN_NAME", "confirmRemove");
		$this->tpl->setVariable("BTN_VALUE", $this->lng->txt("remove"));
		$this->tpl->parseCurrentBlock();

		// copy to clipboard
		$this->tpl->setCurrentBlock("tbl_action_btn");
		$this->tpl->setVariable("BTN_NAME", "copyToClipboard");
		$this->tpl->setVariable("BTN_VALUE", $this->lng->txt("cont_copy_to_clipboard"));
		$this->tpl->parseCurrentBlock();
		
		// copy to clipboard
		$this->tpl->setCurrentBlock("tbl_action_btn");
		$this->tpl->setVariable("BTN_NAME", "pasteFromClipboard");
		$this->tpl->setVariable("BTN_VALUE", $this->lng->txt("cont_paste_from_clipboard"));
		$this->tpl->parseCurrentBlock();

		// footer
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		//$tbl->disable("footer");
		
		// get current folders
		$fobjs = $this->object->getChilds($_GET["obj_id"], "fold");
		$f2objs = array();
		foreach ($fobjs as $obj)
		{
			$f2objs[$obj["title"].":".$obj["id"]] = $obj;
		}
		ksort($f2objs);

		// get current media objects
		$mobjs = $this->object->getChilds($_GET["obj_id"], "mob");
		$m2objs = array();
		foreach ($mobjs as $obj)
		{
			$m2objs[$obj["title"].":".$obj["id"]] = $obj;
		}
		ksort($m2objs);
		
		// merge everything together
		$objs = array_merge($f2objs, $m2objs);
		//$objs = $this->object->getChilds($_GET["obj_id"]);

		$tbl->setMaxCount(count($objs));
		$objs = array_slice($objs, $_GET["offset"], $_GET["limit"]);

		$subobj = array(
			"mob" => $this->lng->txt("mob"),
			"fold" => $this->lng->txt("fold"));
		$opts = ilUtil::formSelect("", "new_type", $subobj, false, true);
		$this->tpl->setCurrentBlock("add_object");
		$this->tpl->setVariable("SELECT_OBJTYPE", $opts);
		$this->tpl->setVariable("BTN_NAME", "create");
		$this->tpl->setVariable("TXT_ADD", $this->lng->txt("add"));
		$this->tpl->parseCurrentBlock();
		$tbl->disable("sort");
		//$tbl->disable("title");
		$tbl->disable("header");
		$tbl->render();
		if(count($objs) > 0)
		{
			$i=0;
			foreach($objs as $obj)
			{
				$this->tpl->setCurrentBlock("link");
				$this->tpl->setVariable("TXT_TITLE", $obj["title"]);
				switch($obj["type"])
				{
					case "fold":
						$this->ctrl->setParameter($this, "obj_id", $obj["obj_id"]);
						$this->tpl->setVariable("LINK_VIEW",
							$this->ctrl->getLinkTarget($this, "listMedia"));
						$this->tpl->parseCurrentBlock();
						$this->tpl->setCurrentBlock("edit");
						$this->tpl->setVariable("TXT_EDIT", $this->lng->txt("edit"));
						$this->ctrl->setParameterByClass("ilobjfoldergui", "obj_id", $obj["obj_id"]);
						$this->tpl->setVariable("EDIT_LINK",
							$this->ctrl->getLinkTargetByClass("ilobjfoldergui", "edit"));
						$this->tpl->parseCurrentBlock();
						$this->tpl->setCurrentBlock("tbl_content");
						$this->tpl->setVariable("IMG_OBJ", ilUtil::getImagePath("icon_".$obj["type"].".gif"));
						break;

					case "mob":
						$this->ctrl->setParameterByClass("ilobjmediaobjectgui", "obj_id", $obj["obj_id"]);
						$this->tpl->setVariable("LINK_VIEW",
							$this->ctrl->getLinkTargetByClass("ilobjmediaobjectgui", "edit"));
						$this->tpl->setCurrentBlock("show");
						$this->tpl->setVariable("TXT_SHOW", $this->lng->txt("view"));
						$this->ctrl->setParameter($this, "mob_id", $obj["obj_id"]);
						$this->tpl->setVariable("SHOW_LINK", $this->ctrl->getLinktarget($this, "showMedia"));
						$this->tpl->parseCurrentBlock();
						$this->tpl->setCurrentBlock("link");
                        //$this->tpl->setVariable("OBJ_URL", ilUtil::getHtmlPath(ilObjMediaObject::_getDirectory($obj["obj_id"]) . '/'. $obj["title"]));
						$this->tpl->setCurrentBlock("tbl_content");
						
						// output thumbnail (or mob icon)
						$mob =& new ilObjMediaObject($obj["obj_id"]);
						$med =& $mob->getMediaItem("Standard");
						$target = $med->getThumbnailTarget();
						if ($target != "")
						{
							$this->tpl->setVariable("IMG_OBJ", $target);
						}
						else
						{
							$this->tpl->setVariable("IMG_OBJ", ilUtil::getImagePath("icon_".$obj["type"].".gif"));
						}
						
						// output media info
						include_once("content/classes/Media/class.ilObjMediaObjectGUI.php");
						$this->tpl->setVariable("MEDIA_INFO",
							ilObjMediaObjectGUI::_getMediaInfoHTML($mob));

						break;
				}


				$css_row = ilUtil::switchColor($i++, "tblrow1", "tblrow2");
				$this->tpl->setVariable("CHECKBOX_ID", $obj["obj_id"]);
				$this->tpl->setVariable("CSS_ROW", $css_row);

				$this->tpl->parseCurrentBlock();
			}
			$this->ctrl->setParameter($this, "obj_id", $_GET["obj_id"]);
		} //if is_array
		else
		{
			$this->tpl->setCurrentBlock("notfound");
			$this->tpl->setVariable("TXT_OBJECT_NOT_FOUND", $this->lng->txt("obj_not_found"));
			$this->tpl->setVariable("NUM_COLS", 3);
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->parseCurrentBlock();
		$this->tpl->show();
	}

	function getTemplate()
	{
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
	}

	
	/**
	* show upper icon (standard procedure will work, if no
	* obj_id is given)
	*/
	function showUpperIcon()
	{
		global $tpl;
		
		parent::showUpperIcon();
		
		if ($_GET["obj_id"] != "")
		{
			$par_id = $this->object->tree->getParentId($_GET["obj_id"]);
			if ($par_id != $this->object->tree->getRootId())
			{
				$this->ctrl->setParameter($this, "obj_id", $par_id);
			}
			else
			{
				$this->ctrl->setParameter($this, "obj_id", "");
			}
			$tpl->setUpperIcon($this->ctrl->getLinkTarget($this, "listMedia"));
			$this->ctrl->setParameter($this, "obj_id", $_GET["obj_id"]);
		}
	}
	
	/**
	* output main frameset of media pool
	* left frame: explorer tree of folders
	* right frame: media pool content
	*/
	function frameset()
	{
		$this->tpl = new ilTemplate("tpl.mep_edit_frameset.html", false, false, "content");
		$this->tpl->setVariable("REF_ID",$this->ref_id);
		$this->tpl->show();
	}

	/**
	* output explorer tree
	*/
	function explorer()
	{
		$this->tpl = new ilTemplate("tpl.main.html", true, true);

		$this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());

		$this->tpl->addBlockFile("CONTENT", "content", "tpl.explorer.html");

		require_once ("content/classes/class.ilMediaPoolExplorer.php");
		$exp = new ilMediaPoolExplorer($this->ctrl->getLinkTarget($this, "listMedia"), $this->object);
		$exp->setTargetGet("obj_id");
		//$exp->setExpandTarget("mep_edit.php?cmd=explorer&ref_id=".$this->object->getRefId());
		$exp->setExpandTarget($this->ctrl->getLinkTarget($this, "explorer"));

		$exp->addFilter("root");
		$exp->addFilter("fold");
		$exp->setFiltered(true);
		$exp->setFilterMode(IL_FM_POSITIVE);


		if ($_GET["mepexpand"] == "")
		{
			$mep_tree =& $this->object->getTree();
			$expanded = $mep_tree->readRootId();
		}
		else
		{
			$expanded = $_GET["mepexpand"];
		}

		$exp->setExpand($expanded);

		// build html-output
		$exp->setOutput(0);
		$output = $exp->getOutput();

		$this->tpl->setCurrentBlock("content");
		$this->tpl->setVariable("TXT_EXPLORER_HEADER", $this->lng->txt("cont_mep_structure"));
		$this->tpl->setVariable("EXP_REFRESH", $this->lng->txt("refresh"));
		$this->tpl->setVariable("EXPLORER",$output);
		$this->tpl->setVariable("ACTION", "mep_edit.php?cmd=explorer&ref_id=".$this->ref_id."&mepexpand=".$_GET["mepexpand"]);
		$this->tpl->parseCurrentBlock();
		$this->tpl->show(false);

	}
	
	/**
	* show media object
	*/
	function showMedia()
	{
		$this->tpl =& new ilTemplate("tpl.fullscreen.html", true, true, "content");
		include_once("classes/class.ilObjStyleSheet.php");
		$this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
		$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
			ilObjStyleSheet::getContentStylePath(0));

		//$int_links = $page_object->getInternalLinks();
		$med_links = ilMediaItem::_getMapAreasIntLinks($_GET["mob_id"]);
		
		// later
		//$link_xml = $this->getLinkXML($med_links, $this->getLayoutLinkTargets());
		
		$link_xlm = "";

		require_once("content/classes/Media/class.ilObjMediaObject.php");
		$media_obj =& new ilObjMediaObject($_GET["mob_id"]);
		
		$xml = "<dummy>";
		// todo: we get always the first alias now (problem if mob is used multiple
		// times in page)
		$xml.= $media_obj->getXML(IL_MODE_ALIAS);
		$xml.= $media_obj->getXML(IL_MODE_OUTPUT);
		$xml.= $link_xml;
		$xml.="</dummy>";

		$xsl = file_get_contents("./content/page.xsl");
		$args = array( '/_xml' => $xml, '/_xsl' => $xsl );
		$xh = xslt_create();

		$wb_path = ilUtil::getWebspaceDir("output");

		$mode = ($_GET["cmd"] != "showMedia")
			? "fullscreen"
			: "media";
		$enlarge_path = ilUtil::getImagePath("enlarge.gif", false, "output");
		$fullscreen_link =
			$this->ctrl->getLinkTarget($this, "showFullscreen");
		$params = array ('mode' => $mode, 'enlarge_path' => $enlarge_path,
			'link_params' => "ref_id=".$_GET["ref_id"],'fullscreen_link' => $fullscreen_link,
			'ref_id' => $_GET["ref_id"], 'pg_frame' => $pg_frame, 'webspace_path' => $wb_path);
		$output = xslt_process($xh,"arg:/_xml","arg:/_xsl",NULL,$args, $params);
		echo xslt_error($xh);
		xslt_free($xh);

		// unmask user html
		$this->tpl->setVariable("MEDIA_CONTENT", $output);

		$this->tpl->parseCurrentBlock();
		$this->tpl->show();

	}

	/**
	* show fullscreen 
	*/
	function showFullscreen()
	{
		$this->showMedia();
	}
	
	/**
	* confirm remove of mobs
	*/
	function confirmRemove()
	{
		if(!isset($_POST["id"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}

		//$this->prepareOutput();

		// SAVE POST VALUES
		$_SESSION["ilMepRemove"] = $_POST["id"];

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.confirm_deletion.html", true);

		sendInfo($this->lng->txt("info_delete_sure"));

		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));

		// BEGIN TABLE HEADER
		$this->tpl->setCurrentBlock("table_header");
		$this->tpl->setVariable("TEXT",$this->lng->txt("objects"));
		$this->tpl->parseCurrentBlock();

		// BEGIN TABLE DATA
		$counter = 0;
		foreach($_POST["id"] as $obj_id)
		{
			$type = ilObject::_lookupType($obj_id);
			$title = ilObject::_lookupTitle($obj_id);
			$this->tpl->setCurrentBlock("table_row");
			$this->tpl->setVariable("CSS_ROW",ilUtil::switchColor(++$counter,"tblrow1","tblrow2"));
			$this->tpl->setVariable("TEXT_CONTENT", $title);
			$this->tpl->setVariable("IMG_OBJ", ilUtil::getImagePath("icon_".$type.".gif"));
			$this->tpl->parseCurrentBlock();
		}

		// cancel/confirm button
		$this->tpl->setVariable("IMG_ARROW",ilUtil::getImagePath("arrow_downright.gif"));
		$buttons = array( "cancelRemove"  => $this->lng->txt("cancel"),
			"remove"  => $this->lng->txt("confirm"));
		foreach ($buttons as $name => $value)
		{
			$this->tpl->setCurrentBlock("operation_btn");
			$this->tpl->setVariable("BTN_NAME",$name);
			$this->tpl->setVariable("BTN_VALUE",$value);
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->show();
	}
	
	/**
	* paste from clipboard
	*/
	function pasteFromClipboard()
	{
		global $ilCtrl;

		$ilCtrl->setParameterByClass("ileditclipboardgui", "returnCommand",
			rawurlencode($ilCtrl->getLinkTarget($this,
			"insertFromClipboard")));
//echo ":".$ilCtrl->getLinkTarget($this, "insertFromClipboard").":";
		$ilCtrl->redirectByClass("ilEditClipboardGUI", "getObject");
	}
	
	
	/**
	* insert media object from clipboard
	*/
	function insertFromClipboard()
	{
		include_once("content/classes/class.ilEditClipboardGUI.php");
		$ids = ilEditClipboardGUI::_getSelectedIDs();
		$not_inserted = array();
		if (is_array($ids))
		{
			foreach ($ids as $id)
			{
				if (!$this->object->insertInTree($id, $_GET["obj_id"]))
				{
					$not_inserted[] = ilObject::_lookupTitle($id)." [".
						$id."]";
				}
			}
		}
		if (count($not_inserted) > 0)
		{
			sendInfo($this->lng->txt("mep_not_insert_already_exist")."<br>".
				implode($not_inserted,"<br>"), true);
		}
		$this->ctrl->redirect($this, "listMedia");
	}


	/**
	* cancel deletion of media objects/folders
	*/
	function cancelRemove()
	{
		session_unregister("ilMepRemove");
		$this->ctrl->redirect($this, "listMedia");
	}

	/**
	* confirm deletion of
	*/
	function remove()
	{
		foreach($_SESSION["ilMepRemove"] as $obj_id)
		{
			$this->object->deleteChild($obj_id);
		}

		sendInfo($this->lng->txt("cont_obj_removed"),true);
		session_unregister("ilMepRemove");
		$this->ctrl->redirect($this, "listMedia");
	}


	/**
	* copy media objects to clipboard
	*/
	function copyToClipboard()
	{
		global $ilUser;

		if(!isset($_POST["id"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}

		foreach ($_POST["id"] as $obj_id)
		{
			$type = ilObject::_lookupType($obj_id);
			if ($type == "fold")
			{
				$this->ilias->raiseError($this->lng->txt("cont_cant_copy_folders"), $this->ilias->error_obj->MESSAGE);
			}
		}

		foreach ($_POST["id"] as $obj_id)
		{
			$ilUser->addObjectToClipboard($obj_id, "mob", "");
		}

		sendInfo($this->lng->txt("copied_to_clipboard"),true);
		$this->ctrl->redirect($this, "listMedia");
	}

	/**
	* add locator items for media pool
	*/
	function addLocatorItems()
	{
		global $ilLocator;
		
		$tree =& $this->object->getTree();
		$obj_id = ($_GET["obj_id"] == "")
			? $tree->getRootId()
			: $_GET["obj_id"];
		$path = $tree->getPathFull($obj_id);
		foreach($path as $node)
		{
			if ($node["child"] == $tree->getRootId())
			{
				$this->ctrl->setParameter($this, "obj_id", "");
				$link = $this->ctrl->getLinkTarget($this, "listMedia");
				$title = $this->object->getTitle();
			}
			else
			{
				$this->ctrl->setParameter($this, "obj_id", $node["child"]);
				$link = $this->ctrl->getLinkTarget($this, "listMedia");
				$title = $node["title"];
			}
			$this->ctrl->setParameter($this, "obj_id", $_GET["obj_id"]);
			$ilLocator->addItem($title, $link);
		}
	}
	
	/**
	* create folder form
	*/
	function createFolderForm()
	{
		//$this->prepareOutput();

		$folder_gui =& new ilObjFolderGUI("", 0, false, false);
		$folder_gui->setFormAction("save", "mep_edit.php?cmd=post&cmdClass=ilObjFolderGUI&ref_id=".
			$_GET["ref_id"]."&obj_id=".$_GET["obj_id"]);
		$folder_gui->createObject();
		//$this->tpl->show();
	}

	/**
	* prepare output
	*/
	function prepareOutput()
	{
		//if (!defined("ILIAS_MODULE"))
		//{
			parent::prepareOutput();
			return;
		//}

		$this->tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");

		$title = $this->object->getTitle();

		// catch feedback message
		sendInfo();

		if (!empty($title))
		{
			$this->tpl->setCurrentBlock("header_image");
			$this->tpl->setVariable("IMG_HEADER", ilUtil::getImagePath("icon_mep_b.gif"));
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("content");
			$this->tpl->setVariable("HEADER", $title);
		}

		$this->setTabs();
		$this->setLocator();
	}

	/**
	* output tabs
	*/
	function setTabs()
	{
		// catch feedback message
		#include_once("classes/class.ilTabsGUI.php");
		#$tabs_gui =& new ilTabsGUI();
		$this->getTabs($this->tabs_gui);
		#$this->tpl->setVariable("TABS", $tabs_gui->getHTML());
		//$this->tpl->setVariable("HEADER", $this->object->getTitle());
	}

	/**
	* adds tabs to tab gui object
	*
	* @param	object		$tabs_gui		ilTabsGUI object
	*/
	function getTabs(&$tabs_gui)
	{
		$tabs_gui->addTarget("view_content", $this->ctrl->getLinkTarget($this, "listMedia"),
			"listMedia", "");

		$tabs_gui->addTarget("edit_properties", $this->ctrl->getLinkTarget($this, "edit"),
			"edit", get_class($this));

		$tabs_gui->addTarget("perm_settings",
			$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"), array("perm","info","owner"), 'ilpermissiongui');

		$tabs_gui->addTarget("clipboard", $this->ctrl->getLinkTargetByClass("ilEditClipboardGUI", "view"),
			"view", "ileditclipboardgui");
	}



}
?>
