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
* Handles Administration commands (cut, delete paste)
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
*
* @ingroup ServicesAdministration
*/
class ilAdministrationCommandGUI 
{
	protected $ctrl = null;
	protected $lng = null;
	private $container = null;

	/**
	 * Constructor
	 */
	public function __construct($a_container) 
	{
		global $ilCtrl, $lng;

		$this->container = $a_container;
		$this->ctrl = $ilCtrl;
		$this->lng = $lng;
	}

	/**
	 * Get container object
	 */
	public function getContainer() 
	{
		return $this->container;
	}

	/**
	 * Show delete confirmation
	 */
	public function delete() 
	{
		global $tpl,$ilSetting,$ilErr;

		$this->ctrl->setReturnByClass(get_class($this->getContainer()), '');

		$to_delete = array ();
		if ((int) $_GET['item_ref_id']) 
		{
			$to_delete = array (
				(int) $_GET['item_ref_id']
			);
		}

		if (isset ($_POST['id']) and is_array($_POST['id'])) 
		{
			$to_delete = $_POST['id'];
		}

		if(!$to_delete)
		{
			$ilErr->raiseError($this->lng->txt('no_checkbox'),$ilErr->MESSAGE);
		}

		include_once ('./Services/Utilities/classes/class.ilConfirmationGUI.php');
		$confirm = new ilConfirmationGUI();
		$confirm->setFormAction($this->ctrl->getFormActionByClass(get_class($this->getContainer()), 'cancel'));
		$confirm->setHeaderText('');
		$confirm->setCancel($this->lng->txt('cancel'), 'cancelDelete');
		$confirm->setConfirm($this->lng->txt('delete'), 'performDelete');

		foreach ($to_delete as $delete) 
		{
			$obj_id = ilObject :: _lookupObjId($delete);
			$type = ilObject :: _lookupType($obj_id);
			
			$confirm->addItem(
				'id[]',
				$delete,
				call_user_func(array(ilObjectFactory::getClassByType($type),'_lookupTitle'),$obj_id),
				ilUtil :: getTypeIconPath($type, $obj_id)
			);
		}

		$msg = $this->lng->txt("info_delete_sure");
			
		if(!$ilSetting->get('enable_trash'))
		{
			$msg .= "<br/>".$this->lng->txt("info_delete_warning_no_trash");
		}
		ilUtil::sendQuestion($msg);

		$tpl->setContent($confirm->getHTML());
	}

	/**
	 * Perform delete
	 */
	public function performDelete() 
	{
		$this->ctrl->setReturnByClass(get_class($this->getContainer()), '');

		include_once './Services/Object/classes/class.ilObjectGUI.php';
		$_SESSION['saved_post'] = $_POST['id'];
		$object = new ilObjectGUI(array (), 0, false, false);
		$object->confirmedDeleteObject();
		return true;
	}

	/**
	 * Cut object
	 */
	public function cut() 
	{
		global $tree;
		
		$this->ctrl->setReturnByClass(get_class($this->getContainer()), '');

		$_GET['ref_id'] = $tree->getParentId((int) $_GET['item_ref_id']);

		include_once './Services/Container/classes/class.ilContainerGUI.php';
		$container = new ilContainerGUI(array (), 0, false, false);
		$container->cutObject();
		return true;
	}
	
	/**
	 * Show target selection
	 * @return 
	 */
	public function showMoveIntoObjectTree()
	{
		global $objDefinition;

		$this->ctrl->setReturnByClass(get_class($this->getContainer()), '');

		$obj_id = ilObject :: _lookupObjId((int) $_GET['ref_id']);
		$type = ilObject :: _lookupType($obj_id);

		$location = $objDefinition->getLocation($type);
		$class_name = "ilObj" . $objDefinition->getClassName($type) . 'GUI';

		// create instance
		include_once ($location . "/class." . $class_name . ".php");
		$container = new $class_name (array (), (int) $_GET['ref_id'], true, false);
		$container->showMoveIntoObjectTreeObject();
		return true;
		
	}
	
	/**
	 * Target selection
	 * @return 
	 */
	public function showLinkIntoMultipleObjectsTree()
	{
		global $objDefinition;

		$this->ctrl->setReturnByClass(get_class($this->getContainer()), '');

		$obj_id = ilObject :: _lookupObjId((int) $_GET['ref_id']);
		$type = ilObject :: _lookupType($obj_id);

		$location = $objDefinition->getLocation($type);
		$class_name = "ilObj" . $objDefinition->getClassName($type) . 'GUI';

		// create instance
		include_once ($location . "/class." . $class_name . ".php");
		$container = new $class_name (array (), (int) $_GET['ref_id'], true, false);
		$container->showLinkIntoMultipleObjectsTreeObject();
		return true;
	}

	/**
	 * Start linking object
	 */
	public function link() 
	{
		global $tree;
		
		$this->ctrl->setReturnByClass(get_class($this->getContainer()), '');

		$_GET['ref_id'] = $tree->getParentId((int) $_GET['item_ref_id']);

		include_once './Services/Container/classes/class.ilContainerGUI.php';
		$container = new ilContainerGUI(array (), 0, false, false);
		$container->linkObject();
		return true;
	}

	/**
	 * Paste object
	 */
	public function paste() 
	{
		global $objDefinition;

		$this->ctrl->setReturnByClass(get_class($this->getContainer()), '');
		$_GET['ref_id'] = (int) $_GET['item_ref_id'];

		$obj_id = ilObject :: _lookupObjId((int) $_GET['item_ref_id']);
		$type = ilObject :: _lookupType($obj_id);

		$location = $objDefinition->getLocation($type);
		$class_name = "ilObj" . $objDefinition->getClassName($type) . 'GUI';

		// create instance
		include_once ($location . "/class." . $class_name . ".php");
		$container = new $class_name (array (), (int) $_GET['item_ref_id'], true, false);
		$container->pasteObject();
		return true;
	}
	
	public function performPasteIntoMultipleObjects()
	{
		global $objDefinition;

		$this->ctrl->setReturnByClass(get_class($this->getContainer()), '');

		$obj_id = ilObject :: _lookupObjId((int) $_GET['ref_id']);
		$type = ilObject :: _lookupType($obj_id);

		$location = $objDefinition->getLocation($type);
		$class_name = "ilObj" . $objDefinition->getClassName($type) . 'GUI';

		// create instance
		include_once ($location . "/class." . $class_name . ".php");
		$container = new $class_name (array (), (int) $_GET['ref_id'], true, false);
		$container->performPasteIntoMultipleObjectsObject();
		return true;
	}
}
?>
