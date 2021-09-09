<?php declare(strict_types=1);
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilForumDraftsTableGUI
 * @author Nadia Matuschek <nmatuschek@databay.de>
 */
class ilForumDraftsTableGUI extends ilTable2GUI
{
    /** @var bool */
    protected $mayEdit = false;

    /**
     * ilForumDraftsTableGUI constructor.
     * @param        $a_parent_obj
     * @param string $a_parent_cmd
     * @param bool   $mayEdit
     */
    public function __construct($a_parent_obj, $a_parent_cmd, bool $mayEdit)
    {
        $this->mayEdit = $mayEdit;
        $this->setId('frm_drafts_' . substr(md5($a_parent_cmd), 0, 3) . '_' . $a_parent_obj->object->getId());

        parent::__construct($a_parent_obj, $a_parent_cmd);
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

    protected function fillRow($draft)
    {
        global $DIC;
        $draft_ids = [];
        if ($DIC->http()->wrapper()->post()->has('draft_ids')) {
            $draft_ids = $DIC->http()->wrapper()->post()->retrieve(
                'draft_ids',
                $DIC->refinery()->kindlyTo()->listOf($DIC->refinery()->kindlyTo()->int())
            );
        }

        $this->tpl->setVariable('VAL_CHECK', ilUtil::formCheckbox(
            isset($draft_ids) && in_array($draft['draft_id'], $draft_ids),
            'draft_ids[]',
            $draft['draft_id']
        ));

        if ($this->mayEdit) {
            $this->ctrl->setParameter($this->getParentObject(), 'draft_id', $draft['draft_id']);
            $url = $this->ctrl->getLinkTarget($this->getParentObject(), 'editThreadDraft');
            $this->ctrl->setParameter($this->getParentObject(), 'draft_id', null);
            $this->tpl->setVariable('VAL_EDIT_URL', $url);
            $this->tpl->setVariable('VAL_LINKED_SUBJECT', $draft['subject']);
        } else {
            $this->tpl->setVariable('VAL_UNLINKED_SUBJECT', $draft['subject']);
        }

        $date = ilDatePresentation::formatDate(new ilDateTime($draft['post_update'], IL_CAL_DATETIME));
        $this->tpl->setVariable('VAL_DATE', $date);
    }
}
