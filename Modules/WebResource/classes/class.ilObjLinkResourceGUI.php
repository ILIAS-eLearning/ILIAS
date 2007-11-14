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

include_once "./classes/class.ilObjectGUI.php";

/**
* Class ilObjLinkResourceGUI
*
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$
* 
* @ilCtrl_Calls ilObjLinkResourceGUI: ilMDEditorGUI, ilPermissionGUI, ilInfoScreenGUI
* 
*
* @ingroup ModulesWebResource
*/
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
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference,false);

		// CONTROL OPTIONS
		$this->ctrl =& $ilCtrl;
		$this->ctrl->saveParameter($this,array("ref_id","cmdClass"));

		$this->lng->loadLanguageModule('webr');
	}

	function &executeCommand()
	{
		global $rbacsystem;

		//if($this->ctrl->getTargetScript() == 'link_resources.php')
		if($_GET["baseClass"] == 'ilLinkResourceHandlerGUI')
		{
			$this->__prepareOutput();
		}
		
		if (strtolower($_GET["baseClass"]) == "iladministrationgui" ||
			$this->getCreationMode() == true)
		{
			$this->prepareOutput();
		}


		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		switch($next_class)
		{
			case "ilinfoscreengui":
				$this->infoScreen();	// forwards command
				break;

			case 'ilmdeditorgui':

				include_once 'Services/MetaData/classes/class.ilMDEditorGUI.php';

				$md_gui =& new ilMDEditorGUI($this->object->getId(), 0, $this->object->getType());
				$md_gui->addObserver($this->object,'MDUpdateListener','General');

				$this->ctrl->forwardCommand($md_gui);
				break;
				
			case 'ilpermissiongui':
				include_once("./classes/class.ilPermissionGUI.php");
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
				break;

			default:
				if(!$cmd)
				{
					$cmd = "view";
				}
				$cmd .= "Object";
				$this->$cmd();
					
				break;
		}
		
		if(!$this->getCreationMode())
		{
			// Fill meta header tags
			include_once('Services/MetaData/classes/class.ilMDUtils.php');
			ilMDUtils::_fillHTMLMetaTags($this->object->getId(),$this->object->getId(),'webr');
		}
		return true;
	}
	
	/**
	 * Overwritten to offer object cloning
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function createObject()
	{
	 	parent::createObject();
	 	$this->fillCloneTemplate('CLONE_WIZARD',$_REQUEST['new_type']);
	}

	function viewObject()
	{
		global $ilAccess,$ilErr;
		
		if(!$ilAccess->checkAccess('read','',$this->object->getRefId()))
		{
			$ilErr->raiseError($this->lng->txt("msg_no_perm_read"),$ilErr->MESSAGE);	
		}
		
		
		if (strtolower($_GET["baseClass"]) == "iladministrationgui")
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

		include_once "./Services/Table/classes/class.ilTableGUI.php";
		include_once './Modules/WebResource/classes/class.ilParameterAppender.php';

		// MINIMUM ACCESS LEVEL = 'read'
		if(!$rbacsystem->checkAccess("read", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}

		$this->object->initLinkResourceItemsObject();
		if(!count($items = $this->object->items_obj->getActivatedItems()))
		{
			ilUtil::sendInfo($this->lng->txt('webr_no_items_created'));

			return true;
		}

		$this->tpl->addBlockFile("ADM_CONTENT","adm_content","tpl.lnkr_view_items.html","Modules/WebResource");
		
		$tpl =& new ilTemplate("tpl.table.html", true, true);
		#$items_sliced = array_slice($items, $_GET["offset"], $_GET["limit"]);

		$tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.lnkr_view_items_row.html",'Modules/WebResource');

		$items = ilUtil::sortArray($items,
								   'title',
								   $_GET['sort_order'] ? $_GET['sort_order'] : 'asc');
		$counter = 0;
		foreach($items as $item_id => $item)
		{
			if(ilParameterAppender::_isEnabled())
			{
				$item = ilParameterAppender::_append($item);
			}
			if(strlen($item['description']))
			{
				$tpl->setCurrentBlock("description");
				$tpl->setVariable("DESCRIPTION",$item['description']);
				$tpl->parseCurrentBlock();
			}
			$tpl->setCurrentBlock("row");
			$tpl->setVariable("ROW_CSS",ilUtil::switchColor($counter++,'tblrow1','tblrow2'));
			$tpl->setVariable("TITLE",$item['title']);
			$tpl->setVariable("TARGET",$item['target']);
			$tpl->parseCurrentBlock();
		}

		// create table
		$tbl = new ilTableGUI();

		// title & header columns
		$tbl->setTitle($this->lng->txt("web_resources"),"icon_webr.gif",$this->lng->txt("web_resources"));
		$tbl->setHeaderNames(array($this->lng->txt("title")));
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

		include_once "./Services/Table/classes/class.ilTableGUI.php";
		include_once './Modules/WebResource/classes/class.ilParameterAppender.php';
		

		// MINIMUM ACCESS LEVEL = 'read'
		if(!$rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}

		$this->tpl->addBlockFile("ADM_CONTENT","adm_content","tpl.lnkr_edit_items.html","Modules/WebResource");
		$this->__showButton('showAddItem',$this->lng->txt('webr_add_item'));

		$this->object->initLinkResourceItemsObject();
		if(!count($items = $this->object->items_obj->getAllItems()))
		{
			ilUtil::sendInfo($this->lng->txt('webr_no_items_created'));

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
		$tpl->setVariable("COLUMN_COUNTS",ilParameterAppender::_isEnabled() ? 8 : 7);
		$tpl->parseCurrentBlock();
		


		$tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.lnkr_edit_items_row.html",'Modules/WebResource');

		$items = ilUtil::sortArray($items,
								   $_GET['sort_by'] ? $_GET['sort_by'] : 'title',
								   $_GET['sort_order'] ? $_GET['sort_order'] : 'asc');
		
		$counter = 0;
		foreach($items as $item_id => $item)
		{
			if(ilParameterAppender::_isEnabled())
			{
				$params_list = array();
				foreach($params = ilParameterAppender::_getParams($item['link_id']) as $id => $param)
				{
					$txt_param = $param['name'];
					switch($param['value'])
					{
						case LINKS_USER_ID:
							$txt_param .= '=IL_USER_ID';
							break;

						case LINKS_SESSION_ID:
							$txt_param .= '=IL_SESSION_ID';
							break;
						
						case LINKS_LOGIN:
							$txt_param .= '=IL_LOGIN';
							break;
					}
					$params_list[] = $txt_param;
				}
				$tpl->setCurrentBlock("params");
				$tpl->setVariable("DYN_PARAM",count($params_list) ? 
								  implode('<br />',$params_list) :
								  $this->lng->txt('links_not_available'));
				$tpl->parseCurrentBlock();
			}			


			if(strlen($item['description']))
			{
				$tpl->setCurrentBlock("description");
				$tpl->setVariable("DESCRIPTION",$item['description']);
				$tpl->parseCurrentBlock();
			}

			$tpl->setCurrentBlock("row");
			$tpl->setVariable("ROW_CSS",ilUtil::switchColor($counter++,'tblrow1','tblrow2'));
			
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

			$target = substr($item['target'],0,70);
			if(strlen($item['target']) > 70)
			{
				$target = substr($item['target'],0,70).'...';
			}
			else
			{
				$target = $item['target'];
			}

				

			$tpl->setVariable("TARGET",$target);
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
		$tbl->setTitle($this->lng->txt("web_resources"),"icon_webr.gif",$this->lng->txt("web_resources"));

		if(!ilParameterAppender::_isEnabled())
		{
			$tbl->setHeaderNames(array('',
									   $this->lng->txt("title"),
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
 		}
		else
		{
			$tbl->setHeaderNames(array('',
									   $this->lng->txt("title"),
									   $this->lng->txt("target"),
									   $this->lng->txt("links_dyn_parameter"),
									   $this->lng->txt('valid'),
									   $this->lng->txt('active'),
									   $this->lng->txt('disable_check'),
									   $this->lng->txt('details')));
			
			$tbl->setHeaderVars(array("",
									  "title",
									  "target",
									  "parameter",
									  "valid",
									  "active",
									  "disable_check",
									  ""),array("ref_id" => $this->object->getRefId(),
												"cmd" => 'editItems'));
			$tbl->setColumnWidth(array("",
									   "40%",
									   "20%",
									   "20%",
									   "5%",
									   "5%",
									   "5%",
									   "5%"));
		}
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
			ilUtil::sendInfo($this->lng->txt('webr_select_one'));
			$this->editItemsObject();

			return true;
		}

		ilUtil::sendInfo($this->lng->txt('webr_sure_delete_items'));
		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.lnkr_ask_delete.html','Modules/WebResource');

		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TBL_TITLE_IMG",ilUtil::getImagePath('icon_webr.gif'));
		$this->tpl->setVariable("TBL_TITLE_IMG_ALT",$this->lng->txt('obj_webr'));
		$this->tpl->setVariable("TBL_TITLE",$this->lng->txt('webr_delete_items'));
		$this->tpl->setVariable("HEADER_DESC",$this->lng->txt('title'));
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
			ilUtil::sendInfo($this->lng->txt('webr_select_one'));
			$this->editItemsObject();

			return true;
		}

		$this->object->initLinkResourceItemsObject();
		foreach($_SESSION['webr_item_ids'] as $id)
		{
			$this->object->items_obj->delete($id);
		}
		ilUtil::sendInfo($this->lng->txt('webr_deleted_items'));

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

		ilUtil::sendInfo($this->lng->txt('webr_modified_items'));
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


		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.lnkr_edit_item.html','Modules/WebResource');

		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TBL_TITLE_IMG",ilUtil::getImagePath('icon_webr.gif'));
		$this->tpl->setVariable("TBL_TITLE_IMG_ALT",$this->lng->txt('obj_webr'));
		$this->tpl->setVariable("TBL_TITLE",$this->lng->txt('webr_edit_item'));
		$this->tpl->setVariable("TXT_TITLE",$this->lng->txt('title'));
		$this->tpl->setVariable("TXT_DESCRIPTION",$this->lng->txt('description'));
		$this->tpl->setVariable("TITLE",ilUtil::prepareFormOutput($item['title']));
		$this->tpl->setVariable("DESCRIPTION",ilUtil::prepareFormOutput($item['description']));
		$this->tpl->setVariable("TXT_TARGET",$this->lng->txt('target'));
		$this->tpl->setVariable("TARGET",ilUtil::prepareFormOutput($item['target']));
		$this->tpl->setVariable("TXT_ACTIVE",$this->lng->txt('webr_active'));
		$this->tpl->setVariable("ACTIVE_CHECK",ilUtil::formCheckbox($item['active'] ? 1 : 0,'active',1));
		$this->tpl->setVariable("TXT_VALID",$this->lng->txt('valid'));
		$this->tpl->setVariable("VALID_CHECK",ilUtil::formCheckbox($item['valid'] ? 1 : 0,'valid',1));
		$this->tpl->setVariable("TXT_DISABLE",$this->lng->txt('disable_check'));
		$this->tpl->setVariable("DISABLE_CHECK",ilUtil::formCheckbox($item['disable_check'] ? 1 : 0,'disable',1));
		$this->tpl->setVariable("TXT_CREATED",$this->lng->txt('created'));
		$this->tpl->setVariable("CREATED",date('Y-m-d H:i:s',$item['create_date']));
		$this->tpl->setVariable("TXT_MODIFIED",$this->lng->txt('last_change'));
		$this->tpl->setVariable("MODIFIED",date('Y-m-d H:i:s',$item['last_update']));
		$this->tpl->setVariable("TXT_LAST_CHECK",$this->lng->txt('webr_last_check'));

		// add dynamic params
		include_once('./Modules/WebResource/classes/class.ilParameterAppender.php');

		if(ilParameterAppender::_isEnabled())
		{
			$counter = 0;
			foreach($params = ilParameterAppender::_getParams($item['link_id']) as $id => $param)
			{
				if(!$counter++)
				{
					$this->tpl->setCurrentBlock("header_info");
					$this->tpl->setVariable("TXT_PARAM_EXIST",$this->lng->txt('links_existing_params'));
					$this->tpl->parseCurrentBlock();
				}
				$this->tpl->setCurrentBlock("show_params");
				
				$txt_param = $param['name'];
				switch($param['value'])
				{
					case LINKS_USER_ID:
						$txt_param .= '=IL_USER_ID';
						break;

					case LINKS_SESSION_ID:
						$txt_param .= '=IL_SESSION_ID';
						break;
						
					case LINKS_LOGIN:
						$txt_param .= '=IL_LOGIN';
						break;
				}
				$this->tpl->setVariable("PARAMETER",$txt_param);
				
				// Delete link
				$this->ctrl->setParameter($this,'param_id',$id);
				$this->tpl->setVariable("DEL_TARGET",$this->ctrl->getLinkTarget($this,'deleteParameter'));
				$this->tpl->setVariable("TXT_DELETE",$this->lng->txt('delete'));
				$this->tpl->parseCurrentBlock();
			}

			$this->tpl->setCurrentBlock("params");
			$this->tpl->setVariable("TXT_ADD_PARAM",$this->lng->txt('links_add_param'));
			$this->tpl->setVariable("TXT_DYNAMIC",$this->lng->txt('links_dynamic'));
			$this->tpl->setVariable("TXT_NAME",$this->lng->txt('links_name'));
			$this->tpl->setVariable("TXT_VALUE",$this->lng->txt('links_value'));
			$this->tpl->setVariable("DYNAMIC_INFO",$this->lng->txt('link_dynamic_info'));

			$this->tpl->setVariable("NAME",$_POST['name'] ? ilUtil::prepareFormOutput($_POST['name'],true) : '');
			$this->tpl->setVariable("VAL_SEL",ilUtil::formSelect((int) $_POST['value'],
																 'value',
																 ilParameterAppender::_getOptionSelect(),
																 false,
																 true));
			$this->tpl->parseCurrentBlock();
		}

		

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

	function deleteParameterObject()
	{
		if(!((int) $_GET['param_id']))
		{
			ilUtil::sendInfo('No parameter id given');
			$this->editItemObject();

			return false;
		}

		include_once './Modules/WebResource/classes/class.ilParameterAppender.php';

		$appender = new ilParameterAppender($this->object->getId());
		$appender->delete((int) $_GET['param_id']);

		ilUtil::sendInfo($this->lng->txt('links_parameter_deleted'));

		$this->editItemObject();
		return true;
	}


	function updateItemObject()
	{
		include_once './Modules/WebResource/classes/class.ilParameterAppender.php';

		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'write'
		if(!$rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}
		if(!$_POST['title'] or $_POST['target'] == 'http://')
		{
			ilUtil::sendInfo($this->lng->txt('webr_fillout_all'));

			$this->editItemObject();
			return false;
		}
		if(ilParameterAppender::_isEnabled())
		{
			$appender =& new ilParameterAppender($this->object->getId());
			$appender->setName(ilUtil::stripSlashes($_POST['name']));
			$appender->setValue(ilUtil::stripSlashes($_POST['value']));
			
			if(!$appender->validate())
			{
				switch($appender->getErrorCode())
				{
					case LINKS_ERR_NO_NAME:
						ilUtil::sendInfo($this->lng->txt('links_no_name_given'));
						$this->editItemObject();
						return false;

					case LINKS_ERR_NO_VALUE:
						ilUtil::sendInfo($this->lng->txt('links_no_value_given'));
						$this->editItemObject();
						return false;

					default:
						break;
				}
			}
		}

		$this->object->initLinkResourceItemsObject();

		$this->object->items_obj->readItem($_SESSION['webr_item_id']);
		$this->object->items_obj->setLinkId($_SESSION['webr_item_id']);
		$this->object->items_obj->setTitle(ilUtil::stripSlashes($_POST['title']));
		$this->object->items_obj->setDescription(ilUtil::stripSlashes($_POST['description']));
		$this->object->items_obj->setTarget(ilUtil::stripSlashes($_POST['target']));
		$this->object->items_obj->setActiveStatus($_POST['active']);
		$this->object->items_obj->setValidStatus($_POST['valid']);
		$this->object->items_obj->setDisableCheckStatus($_POST['disable']);
		$this->object->items_obj->update();

		if(is_object($appender))
		{
			$appender->add($_SESSION['webr_item_id']);
		}

		unset($_SESSION['webr_item_id']);
		ilUtil::sendInfo($this->lng->txt('webr_item_updated'));
		$this->editItemsObject();
		
		return true;
	}

		

	function showAddItemObject()
	{
		global $rbacsystem;

		$this->tabs_gui->setTabActive('edit_content');

		// MINIMUM ACCESS LEVEL = 'read'
		if(!$rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}

		$title = $_POST['title'] ? ilUtil::prepareFormOutput($_POST['title'],true) : '';
		$target = $_POST['target'] ? ilUtil::prepareFormOutput($_POST['target'],true) : 'http://';


		$this->tpl->addBlockFile("ADM_CONTENT","adm_content","tpl.lnkr_add_item.html","Modules/WebResource");

		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_HEADER",$this->lng->txt('webr_add_item'));
		$this->tpl->setVariable("TXT_TITLE",$this->lng->txt('title'));
		$this->tpl->setVariable("TXT_DESC",$this->lng->txt('description'));
		$this->tpl->setVariable("TXT_TARGET",$this->lng->txt('target'));
		$this->tpl->setVariable("TARGET",$target);
		$this->tpl->setVariable("TXT_ACTIVE",$this->lng->txt('active'));
		$this->tpl->setVariable("TXT_CHECK",$this->lng->txt('webr_disable_check'));
		$this->tpl->setVariable("TXT_REQUIRED_FLD",$this->lng->txt('required_field'));
		$this->tpl->setVariable("TXT_CANCEL",$this->lng->txt('cancel'));
		$this->tpl->setVariable("TXT_SUBMIT",$this->lng->txt('add'));
		$this->tpl->setVariable("CMD_SUBMIT",'addItem');
		$this->tpl->setVariable("CMD_CANCEL",'editItems');

		// Params
		include_once('./Modules/WebResource/classes/class.ilParameterAppender.php');

		if(ilParameterAppender::_isEnabled())
		{
			$this->tpl->setCurrentBlock("params");
			$this->tpl->setVariable("TXT_DYNAMIC",$this->lng->txt('links_dynamic'));
			$this->tpl->setVariable("TXT_NAME",$this->lng->txt('links_name'));
			$this->tpl->setVariable("TXT_VALUE",$this->lng->txt('links_value'));
			$this->tpl->setVariable("DYNAMIC_INFO",$this->lng->txt('links_dynamic_info'));

			$this->tpl->setVariable("NAME",$_POST['name'] ? ilUtil::prepareFormOutput($_POST['name'],true) : '');
			$this->tpl->setVariable("VAL_SEL",ilUtil::formSelect((int) $_POST['value'],
																 'value',
																 ilParameterAppender::_getOptionSelect(),
																 false,
																 true));
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setVariable("ACTIVE_CHECK",ilUtil::formCheckBox(1,'active',1));
		$this->tpl->setVariable("CHECK_CHECK",ilUtil::formCheckBox(0,'disable_check',1));
	
	}

	function addItemObject()
	{
		include_once('./Modules/WebResource/classes/class.ilParameterAppender.php');

		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'read'
		if(!$rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}

		$this->object->initLinkResourceItemsObject();

		if(!$_POST['title'] or $_POST['target'] == 'http://')
		{
			ilUtil::sendInfo($this->lng->txt('webr_fillout_all'));

			$this->showAddItemObject();
			return false;
		}
		if(ilParameterAppender::_isEnabled())
		{
			$appender =& new ilParameterAppender($this->object->getId());
			$appender->setName(ilUtil::stripSlashes($_POST['name']));
			$appender->setValue(ilUtil::stripSlashes($_POST['value']));
			
			if(!$appender->validate())
			{
				switch($appender->getErrorCode())
				{
					case LINKS_ERR_NO_NAME:
						ilUtil::sendInfo($this->lng->txt('links_no_name_given'));
						$this->showAddItemObject();
						return false;

					case LINKS_ERR_NO_VALUE:
						ilUtil::sendInfo($this->lng->txt('links_no_name_given'));
						$this->showAddItemObject();
						return false;

					default:
						break;
				}
			}
		}
		$this->object->items_obj->setTitle(ilUtil::stripSlashes($_POST['title']));
		$this->object->items_obj->setDescription(ilUtil::stripSlashes($_POST['description']));
		$this->object->items_obj->setTarget(ilUtil::stripSlashes($_POST['target']));
		$this->object->items_obj->setActiveStatus($_POST['active']);
		$this->object->items_obj->setDisableCheckStatus($_POST['disable_check']);
		$link_id = $this->object->items_obj->add();

		if(is_object($appender))
		{
			$appender->add($link_id);
		}
		$this->editItemsObject();
	}
	
	/**
	* this one is called from the info button in the repository
	* not very nice to set cmdClass/Cmd manually, if everything
	* works through ilCtrl in the future this may be changed
	*/
	function infoScreenObject()
	{
		$this->ctrl->setCmd("showSummary");
		$this->ctrl->setCmdClass("ilinfoscreengui");
		$this->infoScreen();
	}

	/**
	* show information screen
	*/
	function infoScreen()
	{
		global $ilAccess;

		if (!$ilAccess->checkAccess("visible", "", $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}

		include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
		$info = new ilInfoScreenGUI($this);
		
		$info->enablePrivateNotes();
		
		// standard meta data
		$info->addMetaDataSections($this->object->getId(),0, $this->object->getType());
		
		// forward the command
		$this->ctrl->forwardCommand($info);
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
		//ilUtil::sendInfo($this->lng->txt("object_added"),true);
		ilUtil::redirect("ilias.php?baseClass=ilLinkResourceHandlerGUI&ref_id=".$newObj->getRefId().
			"&cmd=showAddItem");
		
		//ilUtil::redirect($this->getReturnLocation("save",'adm_object.php?ref_id='.$newObj->getRefId()));
	}

	
	function linkCheckerObject()
	{
		global $ilias,$ilUser;

		$this->__initLinkChecker();
		$this->object->initLinkResourceItemsObject();

		$invalid_links = $this->link_checker_obj->getInvalidLinksFromDB();


		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.link_check.html",'Modules/WebResource');

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
		if((bool) $ilias->getSetting('cron_web_resource_check'))
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
			ilUtil::sendInfo($this->lng->txt('link_check_message_enabled'));
			$link_check_notify->addNotifier();
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt('link_check_message_disabled'));
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
			ilUtil::sendInfo($this->lng->txt('missing_pear_library'));
			$this->linkCheckerObject();

			return false;
		}


		$this->object->initLinkResourceItemsObject();

		// Set all link to valid. After check invalid links will be set to invalid
		$this->object->items_obj->updateValidByCheck();
 		
		foreach($this->link_checker_obj->checkWebResourceLinks() as $invalid)
		{
			$this->object->items_obj->readItem($invalid['page_id']);
			$this->object->items_obj->setActiveStatus(false);
			$this->object->items_obj->setValidStatus(false);
			$this->object->items_obj->update(false);
		}
		
		$this->object->items_obj->updateLastCheck();
		ilUtil::sendInfo($this->lng->txt('link_checker_refreshed'));

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
		global $rbacsystem,$rbacreview,$ilAccess;

		if ($ilAccess->checkAccess('read','',$this->object->getRefId()))
		{
			$tabs_gui->addTarget("view_content",
				$this->ctrl->getLinkTarget($this, "view"), array("", "view"),
				array(strtolower(get_class($this)), ""));
		}

		if ($rbacsystem->checkAccess('write',$this->object->getRefId()))
		{
			$tabs_gui->addTarget("edit_content",
				$this->ctrl->getLinkTarget($this, "editItems"),
				array("editItems", "addItem", "deleteItems", "editItem", "updateItem"),
				"");
		}
		
		if ($ilAccess->checkAccess('visible','',$this->ref_id))
		{
			// this is not nice. tabs should be displayed in ilcoursegui
			// not via ilrepositorygui, then next_class == ilinfoscreengui
			// could be checked
			$force_active = (strtolower($_GET["cmdClass"]) == "ilinfoscreengui"
				|| $_GET["cmd"] == "infoScreen"
				|| strtolower($_GET["cmdClass"]) == "ilnotegui")
				? true
				: false;
			$tabs_gui->addTarget("info_short",
								 $this->ctrl->getLinkTargetByClass(
								 array("ilobjlinkresourcegui", "ilinfoscreengui"), "showSummary"),
								 "infoScreen",
								 "", "", $force_active);
		}

		if ($rbacsystem->checkAccess('write',$this->object->getRefId()))
		{
			$tabs_gui->addTarget("meta_data",
				 $this->ctrl->getLinkTargetByClass('ilmdeditorgui','listSection'),
				 "", 'ilmdeditorgui');
		}

		if ($rbacsystem->checkAccess('write',$this->object->getRefId()))
		{
			$tabs_gui->addTarget("history",
				$this->ctrl->getLinkTarget($this, "history"), "history", get_class($this));
		}

		if ($rbacsystem->checkAccess('write',$this->object->getRefId()))
		{
			// Check if pear library is available
			if(@include_once('HTTP/Request.php'))
			{
				$tabs_gui->addTarget("link_check",
									 $this->ctrl->getLinkTarget($this, "linkChecker"),
									 array("linkChecker", "refreshLinkCheck"), get_class($this));
			}
		}

		if ($rbacsystem->checkAccess('edit_permission',$this->object->getRefId()))
		{
			$tabs_gui->addTarget("perm_settings",
				$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"), array("perm","info","owner"), 'ilpermissiongui');
		}
	}

	// PRIVATE
	function __prepareOutput()
	{
		// output objects
		//$this->tpl->addBlockFile("CONTENT", "content", "tpl.link_resource.html",'link');
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");

		// output locator
		$this->__setLocator();

		// output message
		if ($this->message)
		{
			ilUtil::sendInfo($this->message);
		}

		// display infopanel if something happened
		ilUtil::infoPanel();

		// set header
		$this->__setHeader();
	}

	function __setHeader()
	{
		include_once './classes/class.ilTabsGUI.php';

		$this->tpl->setCurrentBlock("header_image");
		$this->tpl->setVariable("IMG_HEADER", ilUtil::getImagePath("icon_webr_b.gif"));
		$this->tpl->parseCurrentBlock();
		$this->tpl->setVariable("HEADER",$this->object->getTitle());
		$this->tpl->setVariable("H_DESCRIPTION",$this->object->getDescription());

		#$tabs_gui =& new ilTabsGUI();
		$this->getTabs($this->tabs_gui);

		// output tabs
		#$this->tpl->setVariable("TABS", $tabs_gui->getHTML());
	}

	function __setLocator()
	{
		global $tree;
		global $ilias_locator, $lng;

		$this->tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html");

		$counter = 0;
		
		//$this->tpl->touchBlock('locator_separator');
		//$this->tpl->touchBlock('locator_item');
		
		foreach ($tree->getPathFull($this->object->getRefId()) as $key => $row)
		{
			
			//if ($row["child"] == $tree->getRootId())
			//{
			//	continue;
			//}
			
			if($counter++)
			{
				$this->tpl->touchBlock('locator_separator_prefix');
			}

			if ($row["child"] > 0)
			{
				$this->tpl->setCurrentBlock("locator_img");
				$this->tpl->setVariable("IMG_SRC",
					ilUtil::getImagePath("icon_".$row["type"]."_s.gif"));
				$this->tpl->setVariable("IMG_ALT",
					$lng->txt("obj_".$type));
				$this->tpl->parseCurrentBlock();
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
				$this->tpl->setVariable("LINK_ITEM","./repository.php?ref_id=".$row["child"]);
			}
			else
			{
				$this->tpl->setVariable("ITEM", $this->lng->txt("repository"));
				$this->tpl->setVariable("LINK_ITEM","./repository.php?ref_id=".$row["child"]);
			}

			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setVariable("TXT_LOCATOR",$this->lng->txt("locator"));
		$this->tpl->parseCurrentBlock();
	}

	function _goto($a_target)
	{
		global $ilAccess, $ilErr, $lng;

		// Will be replaced in future releases by ilAccess::checkAccess()
		if ($ilAccess->checkAccess("read", "", $a_target))
		{
			ilUtil::redirect("ilias.php?baseClass=ilLinkResourceHandlerGUI&ref_id=$a_target");
		}
		else
		{
			// to do: force flat view
			if ($ilAccess->checkAccess("visible", "", $a_target))
			{
				ilUtil::redirect("ilias.php?baseClass=ilLinkResourceHandlerGUI&ref_id=".$a_target."&cmd=infoScreen");
			}
			else
			{
				if ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID))
				{
					$_GET["cmd"] = "frameset";
					$_GET["target"] = "";
					$_GET["ref_id"] = ROOT_FOLDER_ID;
					ilUtil::sendInfo(sprintf($lng->txt("msg_no_perm_read_item"),
						ilObject::_lookupTitle(ilObject::_lookupObjId($a_target))), true);
					include("repository.php");
					exit;
				}
			}
		}

		$ilErr->raiseError($lng->txt("msg_no_perm_read"), $ilErr->FATAL);
	}


} // END class.ilObjLinkResource
?>
