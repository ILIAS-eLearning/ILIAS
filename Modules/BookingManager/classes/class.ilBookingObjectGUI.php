<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObjectGUI.php";

/**
* Class ilBookingObjectGUI
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id$
*/
class ilBookingObjectGUI
{
	protected $ref_id; // [int]
	protected $pool_id; // [int]
	protected $pool_has_schedule; // [bool]
	protected $pool_overall_limit; // [int]
	
	/**
	 * Constructor
	 * @param	object	$a_parent_obj
	 */
	function __construct($a_parent_obj)
	{
		$this->ref_id = $a_parent_obj->ref_id;
		$this->pool_id = $a_parent_obj->object->getId();		
		$this->pool_has_schedule = 
			($a_parent_obj->object->getScheduleType() != ilObjBookingPool::TYPE_NO_SCHEDULE);
		$this->pool_overall_limit = $this->pool_has_schedule 
			? null
			: $a_parent_obj->object->getOverallLimit();		
	}

	/**
	 * main switch
	 */
	function executeCommand()
	{
		global $ilCtrl;

		$next_class = $ilCtrl->getNextClass($this);
		
		switch($next_class)
		{
			default:
				$cmd = $ilCtrl->getCmd("render");
				$this->$cmd();
				break;
		}
		return true;
	}

	/**
	 * Render list of booking objects
	 *
	 * uses ilBookingObjectsTableGUI
	 */
	function render()
	{
		global $tpl, $ilCtrl, $lng, $ilAccess;

		if ($ilAccess->checkAccess('write', '', $this->ref_id))
		{
			include_once 'Services/UIComponent/Toolbar/classes/class.ilToolbarGUI.php';
			$bar = new ilToolbarGUI;
			$bar->addButton($lng->txt('book_add_object'), $ilCtrl->getLinkTarget($this, 'create'));
			$bar = $bar->getHTML();
		}
		
		$tpl->setPermanentLink('book', $this->ref_id);
		
		include_once 'Modules/BookingManager/classes/class.ilBookingObjectsTableGUI.php';
		$table = new ilBookingObjectsTableGUI($this, 'render', $this->ref_id, $this->pool_id, $this->pool_has_schedule, $this->pool_overall_limit);
		$tpl->setContent($bar.$table->getHTML());
	}

	/**
	 * Render creation form
	 */
	function create()
	{
		global $ilCtrl, $tpl, $lng, $ilTabs;

		$ilTabs->clearTargets();
		$ilTabs->setBackTarget($lng->txt('book_back_to_list'), $ilCtrl->getLinkTarget($this, 'render'));

		$form = $this->initForm();
		$tpl->setContent($form->getHTML());
	}

	/**
	 * Render edit form
	 */
	function edit()
    {
		global $tpl, $ilCtrl, $ilTabs, $lng;

		$ilTabs->clearTargets();
		$ilTabs->setBackTarget($lng->txt('book_back_to_list'), $ilCtrl->getLinkTarget($this, 'render'));

		$form = $this->initForm('edit', (int)$_GET['object_id']);
		$tpl->setContent($form->getHTML());
	}

	/**
	 * Build property form
	 * @param	string	$a_mode
	 * @param	int		$id
	 * @return	object
	 */
	function initForm($a_mode = "create", $id = NULL)
	{
		global $lng, $ilCtrl, $ilObjDataCache;

		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");

		$form_gui = new ilPropertyFormGUI();

		$title = new ilTextInputGUI($lng->txt("title"), "title");
		$title->setRequired(true);
		$title->setSize(40);
		$title->setMaxLength(120);
		$form_gui->addItem($title);
		
		$desc = new ilTextAreaInputGUI($lng->txt("description"), "desc");
		$desc->setCols(70);
		$desc->setRows(15);
		$form_gui->addItem($desc);
		
		$file = new ilFileInputGUI($lng->txt("book_additional_info_file"), "file");
		$file->setALlowDeletion(true);
		$form_gui->addItem($file);
		
		$nr = new ilNumberInputGUI($lng->txt("booking_nr_of_items"), "items");
		$nr->setRequired(true);
		$nr->setSize(3);
		$nr->setMaxLength(3);
		$form_gui->addItem($nr);
		
		if($this->pool_has_schedule)
		{
			$options = array();
			include_once 'Modules/BookingManager/classes/class.ilBookingSchedule.php';
			foreach(ilBookingSchedule::getList($ilObjDataCache->lookupObjId($this->ref_id)) as $schedule)
			{
				$options[$schedule["booking_schedule_id"]] = $schedule["title"];
			}	
			$schedule = new ilSelectInputGUI($lng->txt("book_schedule"), "schedule");
			$schedule->setRequired(true);
			$schedule->setOptions($options);
			$form_gui->addItem($schedule);
		}
		
		$post = new ilFormSectionHeaderGUI();
		$post->setTitle($lng->txt("book_post_booking_information"));
		$form_gui->addItem($post);
		
		$pdesc = new ilTextAreaInputGUI($lng->txt("book_post_booking_text"), "post_text");
		$pdesc->setCols(70);
		$pdesc->setRows(15);
		$pdesc->setInfo($lng->txt("book_post_booking_text_info"));
		$form_gui->addItem($pdesc);
		
		$pfile = new ilFileInputGUI($lng->txt("book_post_booking_file"), "post_file");
		$pfile->setALlowDeletion(true);
		$form_gui->addItem($pfile);

		if ($a_mode == "edit")
		{
			$form_gui->setTitle($lng->txt("book_edit_object"));

			$item = new ilHiddenInputGUI('object_id');
			$item->setValue($id);
			$form_gui->addItem($item);

			include_once 'Modules/BookingManager/classes/class.ilBookingObject.php';
			$obj = new ilBookingObject($id);
			$title->setValue($obj->getTitle());
			$desc->setValue($obj->getDescription());
			$nr->setValue($obj->getNrOfItems());
			$pdesc->setValue($obj->getPostText());
			$file->setValue($obj->getFile());
			$pfile->setValue($obj->getPostFile());
			
			if(isset($schedule))
			{
				$schedule->setValue($obj->getScheduleId());
			}
			
			$form_gui->addCommandButton("update", $lng->txt("save"));
		}
		else
		{
			$form_gui->setTitle($lng->txt("book_add_object"));
			$form_gui->addCommandButton("save", $lng->txt("save"));
			$form_gui->addCommandButton("render", $lng->txt("cancel"));
		}
		$form_gui->setFormAction($ilCtrl->getFormAction($this));

		return $form_gui;
	}

	/**
	 * Create new object dataset
	 */
	function save()
	{
		global $ilCtrl, $tpl, $lng, $ilTabs;

		$form = $this->initForm();
		if($form->checkInput())
		{
			include_once 'Modules/BookingManager/classes/class.ilBookingObject.php';
			$obj = new ilBookingObject;
			$obj->setPoolId($this->pool_id);
			$obj->setTitle($form->getInput("title"));
			$obj->setDescription($form->getInput("desc"));
			$obj->setNrOfItems($form->getInput("items"));
			$obj->setPostText($form->getInput("post_text"));					
			
			if($this->pool_has_schedule)
			{
				$obj->setScheduleId($form->getInput("schedule"));
			}
			
			$obj->save();
			
			$file = $form->getItemByPostVar("file");						
			if($_FILES["file"]["tmp_name"]) 
			{
				$obj->uploadFile($_FILES["file"]);
			}
			else if($file->getDeletionFlag())
			{
				$obj->deleteFile();
			}		
			
			$pfile = $form->getItemByPostVar("post_file");						
			if($_FILES["post_file"]["tmp_name"]) 
			{
				$obj->uploadPostFile($_FILES["post_file"]);
			}
			else if($pfile->getDeletionFlag())
			{
				$obj->deletePostFile();
			}		
			
			$obj->update();

			ilUtil::sendSuccess($lng->txt("book_object_added"));
			$this->render();
		}
		else
		{			
			$ilTabs->clearTargets();
			$ilTabs->setBackTarget($lng->txt('book_back_to_list'), $ilCtrl->getLinkTarget($this, 'render'));

			$form->setValuesByPost();
			$tpl->setContent($form->getHTML());
		}
	}

	/**
	 * Update object dataset
	 */
	function update()
	{
		global $tpl, $lng;

		$form = $this->initForm('edit', (int)$_POST['object_id']);
		if($form->checkInput())
		{
			include_once 'Modules/BookingManager/classes/class.ilBookingObject.php';
			$obj = new ilBookingObject((int)$_POST['object_id']);
			$obj->setTitle($form->getInput("title"));
			$obj->setDescription($form->getInput("desc"));
			$obj->setNrOfItems($form->getInput("items"));
			$obj->setPostText($form->getInput("post_text"));	
			
			$file = $form->getItemByPostVar("file");						
			if($_FILES["file"]["tmp_name"]) 
			{
				$obj->uploadFile($_FILES["file"]);
			}
			else if($file->getDeletionFlag())
			{
				$obj->deleteFile();
			}		
			
			$pfile = $form->getItemByPostVar("post_file");						
			if($_FILES["post_file"]["tmp_name"]) 
			{
				$obj->uploadPostFile($_FILES["post_file"]);
			}
			else if($pfile->getDeletionFlag())
			{
				$obj->deletePostFile();
			}		
			
			if($this->pool_has_schedule)
			{
				$obj->setScheduleId($form->getInput("schedule"));
			}
			
			$obj->update();

			ilUtil::sendSuccess($lng->txt("book_object_updated"));
			$this->render();
		}
		else
		{
			$form->setValuesByPost();
			$tpl->setContent($form->getHTML());
		}
	}

	/**
	 * Confirm delete
	 */
	function confirmDelete()
	{
		global $ilCtrl, $lng, $tpl, $ilTabs;
		
		$ilTabs->clearTargets();
		$ilTabs->setBackTarget($lng->txt('book_back_to_list'), $ilCtrl->getLinkTarget($this, 'render'));

		include_once 'Services/Utilities/classes/class.ilConfirmationGUI.php';
		$conf = new ilConfirmationGUI();
		$conf->setFormAction($ilCtrl->getFormAction($this));
		$conf->setHeaderText($lng->txt('book_confirm_delete'));

		include_once 'Modules/BookingManager/classes/class.ilBookingObject.php';
		$type = new ilBookingObject((int)$_GET['object_id']);
		$conf->addItem('object_id', (int)$_GET['object_id'], $type->getTitle());
		$conf->setConfirm($lng->txt('delete'), 'delete');
		$conf->setCancel($lng->txt('cancel'), 'render');

		$tpl->setContent($conf->getHTML());
	}

	/**
	 * Delete object
	 */
	function delete()
	{
		global $ilCtrl, $lng;

		include_once 'Modules/BookingManager/classes/class.ilBookingObject.php';
		$obj = new ilBookingObject((int)$_POST['object_id']);
		$obj->delete();

		ilUtil::sendSuccess($lng->txt('book_object_deleted'), true);
		$ilCtrl->redirect($this, 'render');
	}
		
	function rsvConfirmCancelUser()
	{
		global $ilCtrl, $lng, $tpl;
		
		$id = (int)$_GET["object_id"];
		if(!$id)
		{
			return;
		}
		
		include_once 'Services/Utilities/classes/class.ilConfirmationGUI.php';
		$conf = new ilConfirmationGUI();
		$conf->setFormAction($ilCtrl->getFormAction($this));
		$conf->setHeaderText($lng->txt('book_confirm_cancel'));

		include_once 'Modules/BookingManager/classes/class.ilBookingObject.php';
		$type = new ilBookingObject($id);
		$conf->addItem('object_id', $id, $type->getTitle());
		$conf->setConfirm($lng->txt('book_set_cancel'), 'rsvCancelUser');
		$conf->setCancel($lng->txt('cancel'), 'render');

		$tpl->setContent($conf->getHTML());		
	}
	
	function rsvCancelUser()
	{
		global $ilCtrl, $ilUser, $lng;
		
		$id = (int)$_REQUEST["object_id"];
		if(!$id)
		{
			return;
		}
		
		include_once 'Modules/BookingManager/classes/class.ilBookingReservation.php';
		$id = ilBookingReservation::getObjectReservationForUser($id, $ilUser->getId());
		$obj = new ilBookingReservation($id);
		if ($obj->getUserId() != $ilUser->getId())
		{
			ilUtil::sendFailure($lng->txt('permission_denied'), true);
			$ilCtrl->redirect($this, 'render');
		}

		$obj->setStatus(ilBookingReservation::STATUS_CANCELLED);
		$obj->update();

		ilUtil::sendSuccess($lng->txt('settings_saved'));
	    $ilCtrl->redirect($this, 'render');
	}
	
	function deliverInfo()
	{
		$id = (int)$_GET["object_id"];
		if(!$id)
		{
			return;
		}
		
		include_once 'Modules/BookingManager/classes/class.ilBookingObject.php';
		$obj = new ilBookingObject($id);
		$file = $obj->getFileFullPath();
		if($file)
		{
			ilUtil::deliverFile($file, $obj->getFile());						
		}
	}
	
	public function displayPostInfo()
	{
		global $tpl, $ilUser, $lng, $ilCtrl;
		
		$id = (int)$_GET["object_id"];
		if(!$id)
		{
			return;
		}
		
		
		// placeholder 
		
		include_once 'Modules/BookingManager/classes/class.ilBookingReservation.php';
		$book_ids = ilBookingReservation::getObjectReservationForUser($id, $ilUser->getId(), true);				
		$tmp = array();
		$rsv_ids = explode(";", $_GET["rsv_ids"]);
		foreach($book_ids as $book_id)
		{		
			if(in_array($book_id, $rsv_ids))
			{
				$obj = new ilBookingReservation($book_id);
				$from = $obj->getFrom();
				$to = $obj->getTo();
				if($from > time())
				{
					$tmp[$from."-".$to]++;
				}
			}
		}
		
		$olddt = ilDatePresentation::useRelativeDates();
		ilDatePresentation::setUseRelativeDates(false);		
		
		$period = array();
		ksort($tmp);
		foreach($tmp as $time => $counter)
		{
			$time = explode("-", $time);
			$time = ilDatePresentation::formatPeriod(
				new ilDateTime($time[0], IL_CAL_UNIX),
				new ilDateTime($time[1], IL_CAL_UNIX));
			if($counter > 1)
			{
				$time .= " (".$counter.")";
			}
			$period[] = $time;
		}
		$book_id = array_shift($book_ids);
		
		ilDatePresentation::setUseRelativeDates($olddt);		
		
		
		$obj = new ilBookingReservation($book_id);
		if ($obj->getUserId() != $ilUser->getId())
		{
			return;
		}
		
		include_once 'Modules/BookingManager/classes/class.ilBookingObject.php';
		$obj = new ilBookingObject($id);
		$pfile = $obj->getPostFile();
		$ptext = $obj->getPostText();
		
		$mytpl = new ilTemplate('tpl.booking_reservation_post.html', true, true, 'Modules/BookingManager');
		$mytpl->setVariable("TITLE", $lng->txt('book_post_booking_information'));

		if($ptext)
		{
			// placeholder
			$ptext = str_replace("[OBJECT]", $obj->getTitle(), $ptext);						
			$ptext = str_replace("[PERIOD]", implode("<br />", $period), $ptext);
			
			$mytpl->setVariable("POST_TEXT", nl2br($ptext));
		}

		if($pfile)
		{
			$ilCtrl->setParameter($this, "object_id", $obj->getId());
			$url = $ilCtrl->getLinkTarget($this, 'deliverPostFile');
			$ilCtrl->setParameter($this, "object_id", "");

			$mytpl->setVariable("DOWNLOAD", $lng->txt('download'));
			$mytpl->setVariable("URL_FILE", $url);
			$mytpl->setVariable("TXT_FILE", $pfile);
		}

		$mytpl->setVariable("TXT_SUBMIT", $lng->txt('ok'));
		$mytpl->setVariable("URL_SUBMIT", $ilCtrl->getLinkTargetByClass('ilobjbookingpoolgui', 'render'));

		$tpl->setContent($mytpl->get());
	}
	
	public function deliverPostFile()
	{
		global $ilUser;
		
		$id = (int)$_GET["object_id"];
		if(!$id)
		{
			return;
		}
		
		include_once 'Modules/BookingManager/classes/class.ilBookingReservation.php';
		$book_id = ilBookingReservation::getObjectReservationForUser($id, $ilUser->getId());
		$obj = new ilBookingReservation($book_id);
		if ($obj->getUserId() != $ilUser->getId())
		{
			return;
		}
		
		include_once 'Modules/BookingManager/classes/class.ilBookingObject.php';
		$obj = new ilBookingObject($id);
		$file = $obj->getPostFileFullPath();
		if($file)
		{
			ilUtil::deliverFile($file, $obj->getPostFile());						
		}
	}
}

?>