<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once 'Services/Table/classes/class.ilTable2GUI.php';
require_once 'Services/Calendar/classes/class.ilDatePresentation.php';

/**
 * Class ilForumDraftsTableGUI
 * @author Nadia Matuschek <nmatuschek@databay.de> 
 */
class ilForumDraftsTableGUI extends ilTable2GUI
{
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @var string
	 */
	protected $parent_cmd;

	/**
	 * ilForumDraftsTableGUI constructor.
	 * @param        $a_parent_obj
	 * @param string $a_parent_cmd
	 * @param string $a_template_context
	 */
	public function __construct($a_parent_obj, $a_parent_cmd, $a_template_context)
	{	
		global $DIC;
		
		$this->lng  = $DIC->language();
		$this->ctrl = $DIC->ctrl();
		$this->parent_cmd = $a_parent_cmd;

		$this->setId('frm_drafts_' . substr(md5($this->parent_cmd), 0, 3).'_'.$a_parent_obj->object->getId() );
		
		parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);
		$this->initTableColumns();
		$this->setFormAction($this->ctrl->getFormAction($this->getParentObject(), 'showThreads'));
		$this->setRowTemplate('tpl.forums_threads_drafts_table.html', 'Modules/Forum');
	}
	
	public function initTableColumns()
	{
		$this->addColumn('', 'check', '1px', true);
		$this->addColumn($this->lng->txt('drafts'), '');
		$this->addColumn($this->lng->txt('edited_on'), '');
		
		$this->addMultiCommand('confirmDeleteThreadDrafts', $this->lng->txt('delete'));
		$this->setSelectAllCheckbox('draft_ids');
	}

	public function fillRow($draft)
	{
		$this->tpl->setVariable('VAL_CHECK', ilUtil::formCheckbox(
			(isset($_POST['draft_ids']) && in_array($draft['draft_id'], $_POST['draft_ids']) ? true : false), 'draft_ids[]', $draft['draft_id']
		));

		$subject = '<div><a href="' . $this->ctrl->getLinkTarget($this->getParentObject(), 'editThreadDraft&draft_id='.$draft['draft_id']) . '">' . $draft['subject'] . '</a></div>';
		$date =   ilDatePresentation::formatDate(new ilDateTime($draft['post_update'], IL_CAL_DATETIME));
		$this->tpl->setVariable('VAL_SUBJECT', $subject);
		$this->tpl->setVariable('VAL_DATE', $date);
	}
}