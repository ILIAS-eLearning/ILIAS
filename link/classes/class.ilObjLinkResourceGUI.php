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
* Class ilObjLinkResourceGUI
*
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$
* 
* @extends ilObjectGUI
* @package ilias-core
*/

include_once "./classes/class.ilObjectGUI.php";

class ilObjLinkResourceGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access public
	*/
	function ilObjLinkResourceGUI($a_data,$a_id,$a_call_by_reference,$a_prepare_output = true)
	{
		global $ilCtrl;

		$this->type = "webr";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference,$a_prepare_output);

		// CONTROL OPTIONS
		$this->ctrl =& $ilCtrl;
		$this->ctrl->saveParameter($this,array("ref_id","cmdClass"));

		$this->lng->loadLanguageModule('webr');
	}

	function &executeCommand()
	{
		global $rbacsystem;

		if($this->ctrl->getTargetScript() == 'link_resources.php')
		{
			$this->__prepareOutput();
		}

		$next_class = $this->ctrl->getNextClass($this);

		$cmd = $this->ctrl->getCmd();
		switch($next_class)
		{
			default:
				if(!$cmd)
				{
					$cmd = "view";
				}
				$cmd .= "Object";
				$this->$cmd();
					
				break;
		}
		return true;
	}

	function viewObject()
	{
		if($this->ctrl->getTargetScript() == 'adm_object.php')
		{
			parent::viewObject();

			return true;
		}
		else
		{
			$this->listItemsObject();

			return true;
		}
	}

	function listItemsObject()
	{
		global $rbacsystem;

		include_once './classes/class.ilTableGUI.php';

		// MINIMUM ACCESS LEVEL = 'read'
		if(!$rbacsystem->checkAccess("read", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}

		$this->object->initLinkResourceItemsObject();
		if(!count($items = $this->object->items_obj->getActivatedItems()))
		{
			sendInfo($this->lng->txt('webr_no_items_created'));

			return true;
		}

		$this->tpl->addBlockFile("ADM_CONTENT","adm_content","tpl.lnkr_view_items.html","link");
		
		$tpl =& new ilTemplate("tpl.table.html", true, true);
		#$items_sliced = array_slice($items, $_GET["offset"], $_GET["limit"]);

		$tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.lnkr_view_items_row.html",'link');

		$items = ilUtil::sortArray($items,
								   'title',
								   $_GET['sort_order'] ? $_GET['sort_order'] : 'asc');
		$counter = 0;
		foreach($items as $item_id => $item)
		{
			$tpl->setCurrentBlock("row");
			$tpl->setVariable("ROW_CSS",ilUtil::switchColor(++$counter,'tblrow1','tblrow2'));
			$tpl->setVariable("TITLE",$item['title']);
			$tpl->setVariable("TARGET",$item['target']);
			$tpl->parseCurrentBlock();
		}

		// create table
		$tbl = new ilTableGUI();

		// title & header columns
		$tbl->setTitle($this->lng->txt("web_resources"),"icon_webr_b.gif",$this->lng->txt("web_resources"));
		$tbl->setHeaderNames(array($this->lng->txt("description")));
		$tbl->setHeaderVars(array("title"),array("ref_id" => $this->object->getRefId(),
													   "cmd" => 'listItems'));
		$tbl->setColumnWidth(array("100%"));
		$tbl->disable('linkbar');
		$tbl->disable('numinfo');

		$tbl->setOrderColumn('title');
		$tbl->setOrderDirection($_GET['sort_order']);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setMaxCount(count($items));

		// render table
		$tbl->setTemplate($tpl);
		$tbl->render();

		$this->tpl->setVariable("ITEM_TABLE", $tpl->get());

		return true;
	}

	function editItemsObject()
	{
		global $rbacsystem;

		include_once './classes/class.ilTableGUI.php';

		// MINIMUM ACCESS LEVEL = 'read'
		if(!$rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}

		$this->tpl->addBlockFile("ADM_CONTENT","adm_content","tpl.lnkr_edit_items.html","link");
		$this->__showButton('showAddItem',$this->lng->txt('webr_add_item'));

		$this->object->initLinkResourceItemsObject();
		if(!count($items = $this->object->items_obj->getAllItems()))
		{
			sendInfo($this->lng->txt('webr_no_items_created'));

			return true;
		}
		
		$tpl =& new ilTemplate("tpl.table.html", true, true);
		#$items_sliced = array_slice($items, $_GET["offset"], $_GET["limit"]);

		$tpl->setCurrentBlock("tbl_form_header");
		$tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME",'askDeleteItems');
		$tpl->setVariable("BTN_VALUE",$this->lng->txt('delete'));
		$tpl->parseCurrentBlock();
		
		$tpl->setCurrentBlock("plain_buttons");
		$tpl->setVariable("PBTN_NAME",'updateItems');
		$tpl->setVariable("PBTN_VALUE",$this->lng->txt('save'));
		$tpl->parseCurrentBlock();
		
		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setVariable("IMG_ARROW",ilUtil::getImagePath('arrow_downright.gif'));
		$tpl->setVariable("COLUMN_COUNTS",7);
		$tpl->parseCurrentBlock();
		


		$tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.lnkr_edit_items_row.html",'link');

		$items = ilUtil::sortArray($items,
								   $_GET['sort_by'] ? $_GET['sort_by'] : 'title',
								   $_GET['sort_order'] ? $_GET['sort_order'] : 'asc');
		
		$counter = 0;
		foreach($items as $item_id => $item)
		{
			$tpl->setCurrentBlock("row");
			$tpl->setVariable("ROW_CSS",ilUtil::switchColor(++$counter,'tblrow1','tblrow2'));
			
			$tpl->setVariable("CHECK_ITEM",ilUtil::formCheckbox(0,'item_id[]',$item['link_id']));
			$tpl->setVariable("TITLE",$item['title']);

			if($item['last_check'])
			{
				$last_check = date('Y-m-d H:i:s',$item['last_check']);
			}
			else
			{
				$last_check = $this->lng->txt('webr_never_checked');
			}
			$tpl->setVariable("TXT_LAST_CHECK",$this->lng->txt('webr_last_check_table'));
			$tpl->setVariable("LAST_CHECK",$last_check);
			$tpl->setVariable("TARGET",$item['target']);
			$tpl->setVariable("VALID",ilUtil::formCheckbox($item['valid'] ? 1 : 0,'valid['.$item['link_id'].']',1));
			$tpl->setVariable("ACTIVE",ilUtil::formCheckbox($item['active'] ? 1 : 0,'active['.$item['link_id'].']',1));
			$tpl->setVariable("DISABLE_CHECK",ilUtil::formCheckbox($item['disable_check'] ? 1 : 0,'disable['.$item['link_id'].']',1));
			$tpl->setVariable("EDIT_IMG",ilUtil::getImagePath('icon_pencil.gif'));
			$tpl->setVariable("EDIT_ALT",$this->lng->txt('edit'));

			$this->ctrl->setParameter($this,'item_id',$item['link_id']);
			$tpl->setVariable("EDIT_LINK",$this->ctrl->getLinkTarget($this,'editItem'));

			$tpl->parseCurrentBlock();
		}

		// create table
		$tbl = new ilTableGUI();

		// title & header columns
		$tbl->setTitle($this->lng->txt("web_resources"),"icon_webr_b.gif",$this->lng->txt("web_resources"));
		$tbl->setHeaderNames(array('',
								   $this->lng->txt("description"),
								   $this->lng->txt("target"),
								   $this->lng->txt('valid'),
								   $this->lng->txt('active'),
								   $this->lng->txt('disable_check'),
								   $this->lng->txt('details')));
		$tbl->setHeaderVars(array("",
								  "title",
								  "target",
								  "valid",
								  "active",
								  "disable_check",
								  ""),array("ref_id" => $this->object->getRefId(),
											"cmd" => 'editItems'));
		$tbl->setColumnWidth(array("",
								   "50%",
								   "30%",
								   "5%",
								   "5%",
								   "5%",
								   "5%"));
		$tbl->disable('linkbar');
		$tbl->disable('numinfo');
		$tbl->enable('sort');

		$tbl->setOrderColumn($_GET['sort_by']);
		$tbl->setOrderDirection($_GET['sort_order']);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setMaxCount(count($items));

		// render table
		$tbl->setTemplate($tpl);
		$tbl->render();

		$this->tpl->setVariable("ITEM_TABLE", $tpl->get());

		return true;
	}

	function askDeleteItemsObject()
	{
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'write'
		if(!$rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}
		if(!count($_POST['item_id']))
		{
			sendInfo($this->lng->txt('webr_select_one'));
			$this->editItemsObject();

			return true;
		}

		sendInfo($this->lng->txt('webr_sure_delete_items'));
		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.lnkr_ask_delete.html','link');

		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TBL_TITLE_IMG",ilUtil::getImagePath('icon_webr.gif'));
		$this->tpl->setVariable("TBL_TITLE_IMG_ALT",$this->lng->txt('obj_webr'));
		$this->tpl->setVariable("TBL_TITLE",$this->lng->txt('webr_delete_items'));
		$this->tpl->setVariable("HEADER_DESC",$this->lng->txt('description'));
		$this->tpl->setVariable("BTN_CANCEL",$this->lng->txt('cancel'));
		$this->tpl->setVariable("BTN_DELETE",$this->lng->txt('delete'));

		$this->object->initLinkResourceItemsObject();
		
		$counter = 0;
		foreach($_POST['item_id'] as $id)
		{
			$this->object->items_obj->readItem($id);
			$this->tpl->setCurrentBlock("item_row");
			$this->tpl->setVariable("ITEM_TITLE",$this->object->items_obj->getTitle());
			$this->tpl->setVariable("TXT_TARGET",$this->lng->txt('target'));
			$this->tpl->setVariable("TARGET",$this->object->items_obj->getTarget());
			$this->tpl->setVariable("ROW_CLASS",ilUtil::switchColor(++$counter,'tblrow1','tblrow2'));
			$this->tpl->parseCurrentBlock();
		}
		$_SESSION['webr_item_ids'] = $_POST['item_id'];

		return true;
	}

	function deleteItemsObject()
	{
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'write'
		if(!$rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}
		if(!count($_SESSION['webr_item_ids']))
		{
			sendInfo($this->lng->txt('webr_select_one'));
			$this->editItemsObject();

			return true;
		}

		$this->object->initLinkResourceItemsObject();
		foreach($_SESSION['webr_item_ids'] as $id)
		{
			$this->object->items_obj->delete($id);
		}
		sendInfo($this->lng->txt('webr_deleted_items'));

		$this->editItemsObject();
		return true;
	}
		

	function updateItemsObject()
	{
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'write'
		if(!$rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}
		$this->object->initLinkResourceItemsObject();
		foreach($this->object->items_obj->getAllItems() as $item)
		{
			$update = false;

			$valid = (int) $_POST['valid'][$item['link_id']];
			$active = (int) $_POST['active'][$item['link_id']];
			$disable = (int) $_POST['disable'][$item['link_id']];

			if($valid != $item['valid'] or
			   $active != $item['active'] or 
			   $disable != $item['disable_check'])
			{
				$this->object->items_obj->readItem($item['link_id']);
				$this->object->items_obj->setValidStatus($valid);
				$this->object->items_obj->setActiveStatus($active);
				$this->object->items_obj->setDisableCheckStatus($disable);
				$this->object->items_obj->update();
			}
		}

		sendInfo($this->lng->txt('webr_modified_items'));
		$this->editItemsObject();

		return true;
	}

	function editItemObject()
	{
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'write'
		if(!$rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}

		$this->object->initLinkResourceItemsObject();
		$item = $this->object->items_obj->getItem($_GET['item_id'] ? $_GET['item_id'] : $_SESSION['webr_item_id']);


		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.lnkr_edit_item.html','link');

		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TBL_TITLE_IMG",ilUtil::getImagePath('icon_webr.gif'));
		$this->tpl->setVariable("TBL_TITLE_IMG_ALT",$this->lng->txt('obj_webr'));
		$this->tpl->setVariable("TBL_TITLE",$this->lng->txt('webr_edit_item'));
		$this->tpl->setVariable("TXT_DESCRIPTION",$this->lng->txt('title'));
		$this->tpl->setVariable("DESCRIPTION",ilUtil::prepareFormOutput($item['title']));
		$this->tpl->setVariable("TXT_TARGET",$this->lng->txt('target'));
		$this->tpl->setVariable("TARGET",ilUtil::prepareFormOutput($item['target']));
		$this->tpl->setVariable("TXT_ACTIVE",$this->lng->txt('webr_active'));
		$this->tpl->setVariable("ACTIVE_CHECK",ilUtil::formCheckbox($item['active'] ? 1 : 0,'active',1));
		$this->tpl->setVariable("TXT_VALID",$this->lng->txt('webr_valid'));
		$this->tpl->setVariable("VALID_CHECK",ilUtil::formCheckbox($item['valid'] ? 1 : 0,'valid',1));
		$this->tpl->setVariable("TXT_DISABLE",$this->lng->txt('webr_disable'));
		$this->tpl->setVariable("DISABLE_CHECK",ilUtil::formCheckbox($item['disable_check'] ? 1 : 0,'disable',1));
		$this->tpl->setVariable("TXT_CREATED",$this->lng->txt('created'));
		$this->tpl->setVariable("CREATED",date('Y-m-d H:i:s',$item['create_date']));
		$this->tpl->setVariable("TXT_MODIFIED",$this->lng->txt('last_modified'));
		$this->tpl->setVariable("MODIFIED",date('Y-m-d H:i:s',$item['last_update']));
		$this->tpl->setVariable("TXT_LAST_CHECK",$this->lng->txt('webr_last_check'));

		if($item['last_check'])
		{
			$last_check = date('Y-m-d H:i:s',$item['last_check']);
		}
		else
		{
			$last_check = $this->lng->txt('webr_never_checked');
		}

		$this->tpl->setVariable("LAST_CHECK",$last_check);
		$this->tpl->setVariable("BTN_CANCEL",$this->lng->txt('cancel'));
		$this->tpl->setVariable("BTN_UPDATE",$this->lng->txt('save'));

		$_SESSION['webr_item_id'] = $_GET['item_id'] ? $_GET['item_id'] : $_SESSION['webr_item_id'];

		return true;
	}

	function updateItemObject()
	{
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'write'
		if(!$rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}
		if(!$_POST['title'] or $_POST['target'] == 'http://')
		{
			sendInfo($this->lng->txt('webr_fillout_all'));

			$this->editItemObject();
			return false;
		}
		
		$this->object->initLinkResourceItemsObject();
		$this->object->items_obj->setLinkId($_SESSION['webr_item_id']);
		$this->object->items_obj->setTitle(ilUtil::stripSlashes($_POST['title']));
		$this->object->items_obj->setTarget(ilUtil::stripSlashes($_POST['target']));
		$this->object->items_obj->setActiveStatus($_POST['active']);
		$this->object->items_obj->setValidStatus($_POST['valid']);
		$this->object->items_obj->setDisableCheckStatus($_POST['disable']);
		$this->object->items_obj->update();

		unset($_SESSION['webr_item_id']);
		sendInfo($this->lng->txt('webr_item_updated'));
		$this->editItemsObject();
		
		return true;
	}

		

	function showAddItemObject()
	{
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'read'
		if(!$rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}

		$title = $_POST['title'] ? ilUtil::prepareFormOutput($_POST['title'],true) : '';
		$target = $_POST['target'] ? ilUtil::prepareFormOutput($_POST['target'],true) : 'http://';


		$this->tpl->addBlockFile("ADM_CONTENT","adm_content","tpl.lnkr_add_item.html","link");

		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_HEADER",$this->lng->txt('webr_add_item'));
		$this->tpl->setVariable("TXT_TITLE",$this->lng->txt('title'));
		$this->tpl->setVariable("TXT_TARGET",$this->lng->txt('target'));
		$this->tpl->setVariable("TARGET",$target);
		$this->tpl->setVariable("TXT_ACTIVE",$this->lng->txt('active'));
		$this->tpl->setVariable("TXT_CHECK",$this->lng->txt('webr_disable_check'));
		$this->tpl->setVariable("TXT_REQUIRED_FLD",$this->lng->txt('required'));
		$this->tpl->setVariable("TXT_CANCEL",$this->lng->txt('cancel'));
		$this->tpl->setVariable("TXT_SUBMIT",$this->lng->txt('add'));
		$this->tpl->setVariable("CMD_SUBMIT",'addItem');
		$this->tpl->setVariable("CMD_CANCEL",'editItems');

		$this->tpl->setVariable("ACTIVE_CHECK",ilUtil::formCheckBox(1,'active',1));
		$this->tpl->setVariable("CHECK_CHECK",ilUtil::formCheckBox(0,'disable_check',1));
	
	}

	function addItemObject()
	{
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'read'
		if(!$rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}

		$this->object->initLinkResourceItemsObject();
		
		if(!$_POST['title'] or $_POST['target'] == 'http://')
		{
			sendInfo($this->lng->txt('webr_fillout_all'));

			$this->showAddItemObject();
			return false;
		}
		$this->object->items_obj->setTitle(ilUtil::stripSlashes($_POST['title']));
		$this->object->items_obj->setTarget(ilUtil::stripSlashes($_POST['target']));
		$this->object->items_obj->setActiveStatus($_POST['active']);
		$this->object->items_obj->setDisableCheckStatus($_POST['disable_check']);
		$this->object->items_obj->add();

		$this->editItemsObject();
	}

	function historyObject()
	{
		global $rbacsystem;

		if (!$rbacsystem->checkAccess("write", $_GET["ref_id"]))
		{
			$this->ilErr->raiseError($this->lng->txt("permission_denied"),$this->ilErr->MESSAGE);
		}

		include_once("classes/class.ilHistoryGUI.php");
		
		$hist_gui =& new ilHistoryGUI($this->object->getId());
		
		$hist_html = $hist_gui->getHistoryTable(array("ref_id" => $_GET["ref_id"], 
													  "cmd" => "history",
													  "cmdClass" =>$_GET["cmdClass"],
													  "cmdNode" =>$_GET["cmdNode"]));
		
		$this->tpl->setVariable("ADM_CONTENT", $hist_html);
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
		
		ilUtil::redirect($this->getReturnLocation("save",'adm_object.php?ref_id='.$newObj->getRefId()));
	}	
	function linkCheckerObject()
	{
		global $ilias,$ilUser;

		$this->__initLinkChecker();
		$this->object->initLinkResourceItemsObject();

		$invalid_links = $this->link_checker_obj->getInvalidLinksFromDB();


		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.link_check.html",'link');

		if($last_access = $this->link_checker_obj->getLastCheckTimestamp())
		{
			$this->tpl->setCurrentBlock("LAST_MODIFIED");
			$this->tpl->setVariable("AS_OF",$this->lng->txt('last_change').": ");
			$this->tpl->setVariable("LAST_CHECK",date('Y-m-d H:i:s',$last_access));
			$this->tpl->parseCurrentBlock();
		}


		$this->tpl->setVariable("F_ACTION",$this->ctrl->getFormAction($this));

		$this->tpl->setVariable("TYPE_IMG",ilUtil::getImagePath('icon_webr.gif'));
		$this->tpl->setVariable("ALT_IMG",$this->lng->txt('obj_webr'));
		$this->tpl->setVariable("TITLE",$this->object->getTitle().' ('.$this->lng->txt('link_check').')');
		$this->tpl->setVariable("PAGE_TITLE",$this->lng->txt('title'));
		$this->tpl->setVariable("URL",$this->lng->txt('url'));
		$this->tpl->setVariable("OPTIONS",$this->lng->txt('edit'));

		if(!count($invalid_links))
		{
			$this->tpl->setCurrentBlock("no_invalid");
			$this->tpl->setVariable("TXT_NO_INVALID",$this->lng->txt('no_invalid_links'));
			$this->tpl->parseCurrentBlock();
		}
		else
		{
			$counter = 0;
			foreach($invalid_links as $invalid)
			{
				$this->object->items_obj->readItem($invalid['page_id']);

				$this->tpl->setCurrentBlock("invalid_row");
				$this->tpl->setVariable("ROW_COLOR",ilUtil::switchColor(++$counter,'tblrow1','tblrow2'));
				$this->tpl->setVariable("ROW_PAGE_TITLE",$this->object->items_obj->getTitle());
				$this->tpl->setVariable("ROW_URL",$invalid['url']);


				// EDIT IMAGE
				$this->ctrl->setParameter($this,'item_id',$invalid['page_id']);
				$this->tpl->setVariable("ROW_EDIT_LINK",$this->ctrl->getLinkTarget($this,'editItem'));
				$this->tpl->setVariable("ROW_IMG",ilUtil::getImagePath('icon_pencil.gif'));
				$this->tpl->setVariable("ROW_ALT_IMG",$this->lng->txt('edit'));
				$this->tpl->parseCurrentBlock();
			}
		}
		if((bool) $ilias->getSetting('cron_link_check'))
		{
			include_once './classes/class.ilLinkCheckNotify.php';

			// Show message block
			$this->tpl->setCurrentBlock("MESSAGE_BLOCK");
			$this->tpl->setVariable("INFO_MESSAGE",$this->lng->txt('link_check_message_a'));
			$this->tpl->setVariable("CHECK_MESSAGE",ilUtil::formCheckbox(
										ilLinkCheckNotify::_getNotifyStatus($ilUser->getId(),$this->object->getId()),
										'link_check_message',
										1));
			$this->tpl->setVariable("INFO_MESSAGE_LONG",$this->lng->txt('link_check_message_b'));
			$this->tpl->parseCurrentBlock();

			// Show save button
			$this->tpl->setCurrentBlock("CRON_ENABLED");
			$this->tpl->setVariable("DOWNRIGHT_IMG",ilUtil::getImagePath('arrow_downright.gif'));
			$this->tpl->setVariable("BTN_SUBMIT_LINK_CHECK",$this->lng->txt('save'));
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setVariable("BTN_REFRESH",$this->lng->txt('refresh'));

		return true;

	}
	function saveLinkCheckObject()
	{
		global $ilDB,$ilUser;

		include_once './classes/class.ilLinkCheckNotify.php';

		$link_check_notify =& new ilLinkCheckNotify($ilDB);
		$link_check_notify->setUserId($ilUser->getId());
		$link_check_notify->setObjId($this->object->getId());

		if($_POST['link_check_message'])
		{
			sendInfo($this->lng->txt('link_check_message_enabled'));
			$link_check_notify->addNotifier();
		}
		else
		{
			sendInfo($this->lng->txt('link_check_message_disabled'));
			$link_check_notify->deleteNotifier();
		}
		$this->linkCheckerObject();

		return true;
	}
		


	function refreshLinkCheckObject()
	{
		$this->__initLinkChecker();

		if(!$this->link_checker_obj->checkPear())
		{
			sendInfo($this->lng->txt('missing_pear_library'));
			$this->linkCheckerObject();

			return false;
		}

		$this->object->initLinkResourceItemsObject();
		foreach($this->link_checker_obj->checkWebResourceLinks() as $invalid)
		{
			$this->object->items_obj->readItem($invalid['page_id']);
			$this->object->items_obj->setActiveStatus(false);
			$this->object->items_obj->setValidStatus(false);
			$this->object->items_obj->update(false);
		}
		
		$this->object->items_obj->updateLastCheck();
		sendInfo($this->lng->txt('link_checker_refreshed'));

		$this->linkCheckerObject();

		return true;
	}

	function __initLinkChecker()
	{
		global $ilDB;

		include_once './classes/class.ilLinkChecker.php';

		$this->link_checker_obj =& new ilLinkChecker($ilDB,false);
		$this->link_checker_obj->setObjId($this->object->getId());

		return true;
	}
	/**
	* get tabs
	* @access	public
	* @param	object	tabs gui object
	*/
	function getTabs(&$tabs_gui)
	{
		global $rbacsystem,$rbacreview;

		$this->ctrl->setParameter($this,"ref_id",$this->object->getRefId());

		if($rbacsystem->checkAccess('read',$this->object->getRefId()))
		{
			$tabs_gui->addTarget("view_content",
								 $this->ctrl->getLinkTarget($this, "view"), "view", get_class($this));
		}
		if($rbacsystem->checkAccess('write',$this->object->getRefId()))
		{
			$tabs_gui->addTarget("edit_content",
								 $this->ctrl->getLinkTarget($this, "editItems"), "editItems", get_class($this));
		}
		if($rbacsystem->checkAccess('write',$this->object->getRefId()))
		{
			$tabs_gui->addTarget("edit_properties",
								 $this->ctrl->getLinkTarget($this, "edit"), "edit", get_class($this));
		}
		if($rbacsystem->checkAccess('write',$this->object->getRefId()))
		{
			$tabs_gui->addTarget("history",
								 $this->ctrl->getLinkTarget($this, "history"), "history", get_class($this));
		}
		if($rbacsystem->checkAccess('write',$this->object->getRefId()))
		{
			$tabs_gui->addTarget("link_check",
								 $this->ctrl->getLinkTarget($this, "linkChecker"), "linkChecker", get_class($this));
		}
		if($rbacsystem->checkAccess('edit_permission',$this->object->getRefId()))
		{
			$tabs_gui->addTarget("perm_settings",
								 $this->ctrl->getLinkTarget($this, "perm"), "perm", get_class($this));
		}
	}

	// PRIVATE
	function __prepareOutput()
	{
		// output objects
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.link_resource.html",'link');
		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");

		// output locator
		$this->__setLocator();

		// output message
		if ($this->message)
		{
			sendInfo($this->message);
		}

		// display infopanel if something happened
		infoPanel();

		// set header
		$this->__setHeader();
	}

	function __setHeader()
	{
		include_once './classes/class.ilTabsGUI.php';

		$this->tpl->setVariable("HEADER",$this->object->getTitle());
		$this->tpl->setVariable("H_DESCRIPTION",$this->object->getDescription());

		$tabs_gui =& new ilTabsGUI();
		$this->getTabs($tabs_gui);

		// output tabs
		$this->tpl->setVariable("TABS", $tabs_gui->getHTML());
	}

	function __setLocator()
	{
		global $tree;
		global $ilias_locator;

		$this->tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html");

		$counter = 0;
		foreach ($tree->getPathFull($this->object->getRefId()) as $key => $row)
		{
			if($counter++)
			{
				$this->tpl->touchBlock('locator_separator_prefix');
			}

			$this->tpl->setCurrentBlock("locator_item");

			if($row["type"] == 'webr')
			{
				$this->tpl->setVariable("ITEM",$this->object->getTitle());
				$this->tpl->setVariable("LINK_ITEM",$this->ctrl->getLinkTarget($this));
			}
			elseif ($row["child"] != $tree->getRootId())
			{
				$this->tpl->setVariable("ITEM", $row["title"]);
				$this->tpl->setVariable("LINK_ITEM","../repository.php?ref_id=".$row["child"]);
			}
			else
			{
				$this->tpl->setVariable("ITEM", $this->lng->txt("repository"));
				$this->tpl->setVariable("LINK_ITEM","../repository.php?ref_id=".$row["child"]);
			}

			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setVariable("TXT_LOCATOR",$this->lng->txt("locator"));
		$this->tpl->parseCurrentBlock();
	}


} // END class.ilObjLinkResource
?>
