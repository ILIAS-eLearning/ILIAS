<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Services/Payment/classes/class.ilShopTopics.php';

/**
* Class ilShopTopicsGUI
*
* @author Michael Jansen <mjansen@databay.de> 
* @version $Id$
* 
* @ingroup ServicesPayment
* 
*/
class ilShopTopicsGUI
{
	private $objCurrentTopic = null;	
	private $ctrl = null;
	private $tpl = null;
	private $lng = null;	
	private $ask_for_deletion = false;
	
	public function __construct($a_gui_object)	
	{
		global $tpl, $ilCtrl, $lng;
		
		$this->ctrl = $ilCtrl;
		$this->lng = $lng;
		$this->tpl = $tpl;
		
		$a_gui_object->tabs_gui->setTabActive('topics');		

		$this->objCurrentTopic = new ilShopTopic(ilUtil::stripSlashes($_GET['topic_id']));
	}
		
	public function executeCommand()
	{
		$cmd = $this->ctrl->getCmd();
		switch($this->ctrl->getNextClass($this))
		{
			default:
				if(!$cmd = $this->ctrl->getCmd())
				{
					$cmd = 'showTopicsList';
				}
				$this->$cmd();
				break;
		}
		
		return true;
	}
	
	public function saveTopic()
	{
		$this->objCurrentTopic->setTitle(ilUtil::stripSlashes(trim($_POST['title'])));
		$this->objCurrentTopic->setSorting((int)ilUtil::stripSlashes(trim($_POST['sorting'])));
		
		if($_POST['title'] == '')
		{
			ilUtil::sendFailure($this->lng->txt('fill_out_all_required_fields'));
			
			$this->showTopicForm();
		}
		else
		{
			$mode = $this->objCurrentTopic->getId() ? 'edit' : 'create';
			
			if($this->objCurrentTopic->save())
			{			
				ilUtil::sendSuccess($this->lng->txt($mode == 'create' ? 'topic_saved' : 'topic_edited'));
			}			
			
			$this->showTopicsList();
		}		
		
		return true;
	}
	
	public function showTopicForm()
	{
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content', 'tpl.main_view.html', 'Services/Payment');
		
		include_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
		
		$form = new ilPropertyFormGUI();

		if($this->objCurrentTopic->getId())
		{
			$this->ctrl->setParameter($this, 'topic_id', $this->objCurrentTopic->getId());	
		}
		$form->setFormAction($this->ctrl->getFormAction($this, 'saveTopic'));
		
		$form->setTitle($this->lng->txt($this->objCurrentTopic->getId() ? 'edit_topic' : 'new_topic'));
		
		$title = new ilTextInputGUI($this->lng->txt('title'), 'title');
		$title->setValue($this->objCurrentTopic->getTitle());
		$title->setRequired(true);		
		$form->addItem($title);
		
		$sorting = new ilTextInputGUI($this->lng->txt('pay_sorting_value'), 'sorting');
		$sorting->setValue($this->objCurrentTopic->getSorting());
		$sorting->setMaxLength(11);
		$sorting->setSize(11);
		$form->addItem($sorting);
		
		$form->addCommandButton('saveTopic', $this->lng->txt('save'));
		$form->addCommandButton('showTopicsList', $this->lng->txt('cancel'));
		
		$this->tpl->setVariable('FORM', $form->getHTML());		
		
		return true;
	}
	
	public function confirmDeleteTopic()
	{
		if(!count($_POST['topic_id']))
		{
			ilUtil::sendInfo($this->lng->txt('select_one_topic'));
		}
		else
		{		
			$this->ask_for_deletion = true;
		}
		
		$this->showTopicsList();		
		
		return true;
	}
	
	public function saveSorting()
	{
		if(count($_POST['sorting']))
		{
			foreach($_POST['sorting'] as $topic_id => $sorting_value)
			{
				$oTopic = new ilShopTopic($topic_id);
				$oTopic->setSorting($sorting_value);
				$oTopic->save();
				unset($oTopic);
			}
		}
		
		ilUtil::sendSuccess($this->lng->txt('saved_successfully'));
		
		$this->showTopicsList();		
		
		return true;
	}
	
	public function performDeleteTopic()
	{
		if(!count($_POST['topic_id']))
		{
			ilUtil::sendInfo($this->lng->txt('select_one_topic'));
		}
		else
		{		
			foreach($_POST['topic_id'] as $topic_id)
			{
				$oTopic = new ilShopTopic($topic_id);
				$oTopic->delete();
				unset($oTopic);
			}

			ilUtil::sendInfo($this->lng->txt('topics_deleted'));
		}
		
		$this->showTopicsList();		
		
		return true;
	}
	
	public function showTopicsList()
	{
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content', 'tpl.main_view.html', 'Services/Payment');
	
		if($this->ask_for_deletion)
		{		
			include_once 'Services/Utilities/classes/class.ilConfirmationGUI.php';
			$c_gui = new ilConfirmationGUI();
			
			$c_gui->setFormAction($this->ctrl->getFormAction($this, 'performDeleteTopic'));
			$c_gui->setHeaderText($this->lng->txt('sure_delete_topics'));
			$c_gui->setCancel($this->lng->txt('cancel'), 'showTopicsList');
			$c_gui->setConfirm($this->lng->txt('confirm'), 'performDeleteTopic');
						
			foreach($_POST['topic_id'] as $topic_id)
			{
				$c_gui->addItem('topic_id[]', $topic_id, ilShopTopic::_lookupTitle($topic_id));
			}
				
			$this->tpl->setVariable('CONFIRMATION', $c_gui->getHTML());
			
			return true;			 
		}

		include_once 'Services/Payment/classes/class.ilShopTopicsTableGUI.php';
		$table_gui = new ilShopTopicsTableGUI($this, 'showTopicsList');
		$table_gui->setTitle($this->lng->txt('topics'));
		ilShopTopics::_getInstance()->setSortingType(ilShopTopics::TOPICS_SORT_MANUALLY);
		ilShopTopics::_getInstance()->setSortingDirection('ASC');
		ilShopTopics::_getInstance()->read();
		$table_gui->parseRecords(ilShopTopics::_getInstance()->getTopics());
		$table_gui->addCommandButton('showTopicForm', $this->lng->txt('add'));
		$table_gui->addCommandButton('saveSorting', $this->lng->txt('pay_save_sorting'));		
		
		$this->tpl->setVariable('TABLE', $table_gui->getHTML());
		
		return true;
	}
}
?>
