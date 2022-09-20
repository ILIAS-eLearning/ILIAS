<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Class ilForumDraftsTableGUI
 * @author Nadia Matuschek <nmatuschek@databay.de>
 */
class ilForumDraftsTableGUI extends ilTable2GUI
{
    public function __construct(ilObjForumGUI $a_parent_obj, string $a_parent_cmd, protected bool $mayEdit)
    {
        $this->setId('frm_drafts_' . substr(md5($a_parent_cmd), 0, 3) . '_' . $a_parent_obj->getObject()->getId());

        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->initTableColumns();
        $this->setFormAction($this->ctrl->getFormAction($this->getParentObject(), 'showThreads'));
        $this->setRowTemplate('tpl.forums_threads_drafts_table.html', 'Modules/Forum');
    }

    public function initTableColumns(): void
    {
        $this->addColumn('', 'check', '1px', true);
        $this->addColumn($this->lng->txt('drafts'), '');
        $this->addColumn($this->lng->txt('edited_on'), '');

        $this->addMultiCommand('confirmDeleteThreadDrafts', $this->lng->txt('delete'));
        $this->setSelectAllCheckbox('draft_ids');
    }

    protected function fillRow(array $a_set): void
    {
        global $DIC;

        $selected_draft_ids = [];
        if ($DIC->http()->wrapper()->post()->has('draft_ids')) {
            $selected_draft_ids = $DIC->http()->wrapper()->post()->retrieve(
                'draft_ids',
                $DIC->refinery()->kindlyTo()->listOf($DIC->refinery()->kindlyTo()->int())
            );
        }

        $this->tpl->setVariable(
            'VAL_CHECK',
            ilLegacyFormElementsUtil::formCheckbox(
                in_array($a_set['draft_id'], $selected_draft_ids, true),
                'draft_ids[]',
                (string) $a_set['draft_id']
            )
        );

        if ($this->mayEdit) {
            $this->ctrl->setParameter($this->getParentObject(), 'draft_id', $a_set['draft_id']);
            $url = $this->ctrl->getLinkTarget($this->getParentObject(), 'editThreadDraft');
            $this->ctrl->setParameter($this->getParentObject(), 'draft_id', null);
            $this->tpl->setVariable('VAL_EDIT_URL', $url);
            $this->tpl->setVariable('VAL_LINKED_SUBJECT', $a_set['subject']);
        } else {
            $this->tpl->setVariable('VAL_UNLINKED_SUBJECT', $a_set['subject']);
        }

        $date = ilDatePresentation::formatDate(new ilDateTime($a_set['post_update'], IL_CAL_DATETIME));
        $this->tpl->setVariable('VAL_DATE', $date);
    }
}
