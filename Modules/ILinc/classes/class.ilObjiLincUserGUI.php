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
* Class ilObjiLincUserGUI
* iLinc related user settings
*
* @author Sascha Hofmann <saschahofmann@gmx.de>
*
* @version $Id$
*
*/

require_once ('./Modules/ILinc/classes/class.ilnetucateXMLAPI.php');
require_once ('./Modules/ILinc/classes/class.ilObjiLincUser.php');

class ilObjiLincUserGUI
{
	/**
	* Constructor
	* @access	public
	* @param	array	??
	* @param	integer	object id
	* @param	boolean	call be reference
	*/
	function ilObjiLincUserGUI(&$a_user_obj,&$a_usrf_ref_id)
	{
		global $ilias, $tpl, $ilCtrl, $ilErr, $lng;

		if (!isset($ilErr))
		{
			$ilErr = new ilErrorHandling();
			$ilErr->setErrorHandling(PEAR_ERROR_CALLBACK,array($ilErr,'errorHandler'));
		}
		else
		{
			$this->ilErr =& $ilErr;
		}

		$this->ilias =& $ilias;
		$this->tpl =& $tpl;
		$this->lng =& $lng;
		$this->ctrl =& $ilCtrl;
		
		$this->lng->loadLanguageModule('ilinc');
		
		$this->usrf_ref_id =& $a_usrf_ref_id;
		
		$this->user =& $a_user_obj;
		
		$this->ilinc_user = new ilObjiLincUser($a_user_obj);
	}
	
	function &executeCommand()
	{
		global $rbacsystem, $ilErr;

		// User folder
		if($this->usrf_ref_id == USER_FOLDER_ID and !$rbacsystem->checkAccess('visible,read',$this->usrf_ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_modify_user"),$this->ilias->error_obj->MESSAGE);
		}
		// if called from local administration $this->usrf_ref_id is category id 
		// Todo: this has to be fixed. Do not mix user folder id and category id
		if($this->usrf_ref_id != USER_FOLDER_ID)
		{
			// check if user is assigned to category
			if(!$rbacsystem->checkAccess('cat_administrate_users',$this->user->getTimeLimitOwner()))
			{
				$this->ilias->raiseError($this->lng->txt("msg_no_perm_modify_user"),$this->ilias->error_obj->MESSAGE);
			}
		}

		$next_class = $this->ctrl->getNextClass($this);

		switch($next_class)
		{
			default:
				$cmd = $this->ctrl->getCmd();
				
				if ($cmd == '')
				{
					$cmd = "view";
				}

				$this->$cmd();
				break;
		}

		return true;
	}
	
	function view()
	{
		// return iLinc user account data if user already exists on iLinc server
		if ($this->ilinc_user->id)
		{
			if (!$data = $this->ilinc_user->find($this->ilinc_user->id))
			{
				$this->ilErr->raiseError($this->ilinc_user->getErrorMsg(),$this->ilErr->MESSAGE);
			}
			
			$this->ilinc_user->setVar('akuservalue1',$data['users'][$this->ilinc_user->id]['akuservalue1']);
			$this->ilinc_user->setVar('akuservalue2',$data['users'][$this->ilinc_user->id]['akuservalue2']);
		}
	
		if (!$this->ilinc_user->id)
		{
			$ilinc_id = $this->lng->txt("ilinc_no_account_yet");
			$ilinc_login = $ilinc_id;
			$ilinc_passwd = $ilinc_id;
			
			$submit_button_title = $this->lng->txt("ilinc_add_user");
		}
		else
		{
			$ilinc_id = $this->ilinc_user->id;
			$ilinc_login = $this->ilinc_user->login;
			$ilinc_passwd = $this->ilinc_user->passwd;
			
			$submit_button_title = $this->lng->txt("refresh");
		}
		
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.icrs_usr_edit.html","Modules/ILinc");
		
		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_HEADER", $this->lng->txt("ilinc_user_settings"));
		$this->tpl->setVariable("TXT_LOGIN_DATA", $this->lng->txt("login_data"));
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));

		$this->tpl->setVariable("TXT_SUBMIT", $submit_button_title);
		$this->tpl->setVariable("CMD_SUBMIT", "save");
		
		$this->tpl->setVariable("TXT_ILINC_ID", $this->lng->txt("ilinc_user_id"));
		$this->tpl->setVariable("ILINC_ID", $ilinc_id);
		$this->tpl->setVariable("TXT_ILINC_LOGIN", $this->lng->txt("login"));
		$this->tpl->setVariable("ILINC_LOGIN", $ilinc_login);
		/*
		$this->tpl->setVariable("TXT_ILINC_PASSWD", $this->lng->txt("passwd"));
		$this->tpl->setVariable("ILINC_PASSWD", $ilinc_passwd);
		*/
		
		$this->tpl->setVariable("TXT_ILINC_AKUSERVALUES", $this->lng->txt("ilinc_akuservalues"));
		$this->tpl->setVariable("TXT_ILINC_AKUSERVALUES_DESC", $this->lng->txt("ilinc_akuservalues_desc"));
		$this->tpl->setVariable("TXT_ILINC_AKUSERVALUE1", $this->lng->txt("ilinc_akuservalue1"));
		$this->tpl->setVariable("ILINC_AKUSERVALUE1", $this->ilinc_user->akuservalue1);
		$this->tpl->setVariable("TXT_ILINC_AKUSERVALUE2", $this->lng->txt("ilinc_akuservalue2"));
		$this->tpl->setVariable("ILINC_AKUSERVALUE2", $this->ilinc_user->akuservalue2);
	}

	function cancel()
	{
		session_unregister("saved_post");

		ilUtil::sendInfo($this->lng->txt("msg_cancel"),true);

		if(strtolower($_GET["baseClass"]) == 'iladministrationgui')
		{
			$this->ctrl->redirectByClass("ilobjusergui", "view");
		}
		else
		{
			$this->ctrl->redirectByClass('ilobjcategorygui','listUsers');
		}
	}
	
	function save()
	{
		if (!$this->ilinc_user->id)
		{
			$this->ilinc_user->setVar('akuservalue1',$_POST['Fobject']['ilinc_akuservalue1']);
			$this->ilinc_user->setVar('akuservalue2',$_POST['Fobject']['ilinc_akuservalue2']);
			
			if (!$this->ilinc_user->add())
			{
				$this->ilErr->raiseError($this->ilinc_user->getErrorMsg(),$this->ilErr->MESSAGE);
			}

			$info_message = $this->lng->txt("ilinc_user_added");
		}
		else
		{
			$this->ilinc_user->setVar('akuservalue1',$_POST['Fobject']['ilinc_akuservalue1']);
			$this->ilinc_user->setVar('akuservalue2',$_POST['Fobject']['ilinc_akuservalue2']);

			if (!$this->ilinc_user->edit())
			{
				$this->ilErr->raiseError($this->ilinc_user->getErrorMsg(),$this->ilErr->MESSAGE);
			}
			
			$info_message = $this->lng->txt("ilinc_akuservalues_refreshed");
		}
		
		ilUtil::sendInfo($info_message,true);

		$this->ctrl->redirectByClass("ilobjilincusergui", "view");
	}
	
	// init sub tabs
	// not used yet
	function __initSubTabs($a_cmd)
	{
		global $ilTabs;

		$perm = ($a_cmd == 'perm') ? true : false;
		$info = ($a_cmd == 'info') ? true : false;
		$owner = ($a_cmd == 'owner') ? true : false;

		$ilTabs->addSubTabTarget("permission_settings", $this->ctrl->getLinkTarget($this, "perm"),
								 "", "", "", $perm);
		$ilTabs->addSubTabTarget("info_status_info", $this->ctrl->getLinkTarget($this, "info"),
								 "", "", "", $info);
		$ilTabs->addSubTabTarget("owner", $this->ctrl->getLinkTarget($this, "owner"),
								 "", "", "", $owner);
	}
} // END class.ilObjiLincUserGUI
?>
