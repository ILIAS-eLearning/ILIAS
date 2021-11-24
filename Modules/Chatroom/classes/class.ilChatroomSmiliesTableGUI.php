<?php declare(strict_types=1);
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilChatroomSmiliesTableGUI
 * Prepares table rows and fills them.
 * @author  Andreas Kordosz <akordosz@databay.de>
 * @version $Id$
 * @ingroup ModulesChatroom
 */
class ilChatroomSmiliesTableGUI extends ilTable2GUI
{
    private ilChatroomObjectGUI $gui;
    protected \ILIAS\DI\Container $dic;

    public function __construct(ilChatroomObjectGUI $a_ref, string $cmd)
    {
        $this->setId('chatroom_smilies_tbl');
        parent::__construct($a_ref, $cmd);

        global $DIC;

        $this->dic = $DIC;
        $this->gui = $a_ref;

        $this->setTitle($this->lng->txt('chatroom_available_smilies'));

        $this->addColumn('', 'checkbox', '2%', true);
        $this->addColumn($this->lng->txt('chatroom_smiley_image'), '', '28%');
        $this->addColumn($this->lng->txt('chatroom_smiley_keyword'), 'keyword', '55%');
        $this->addColumn($this->lng->txt('actions'), '', '15%');

        $this->setFormAction($this->ctrl->getFormAction($a_ref));
        $this->setRowTemplate('tpl.chatroom_smiley_list_row.html', 'Modules/Chatroom');
        $this->setSelectAllCheckbox('smiley_id');

        if ($this->dic->rbac()->system()->checkAccess('write', $this->gui->ref_id)) {
            $this->addMultiCommand(
                'smiley-deleteMultipleObject',
                $this->lng->txt('chatroom_delete_selected')
            );
        }
    }

    protected function fillRow($a_set) : void
    {
        $this->tpl->setVariable('VAL_SMILEY_ID', $a_set['smiley_id']);
        $this->tpl->setVariable('VAL_SMILEY_PATH', $a_set['smiley_fullpath']);
        $this->tpl->setVariable('VAL_SMILEY_KEYWORDS', $a_set['smiley_keywords']);
        $this->tpl->setVariable(
            'VAL_SMILEY_KEYWORDS_NONL',
            str_replace("\n", "", $a_set['smiley_keywords'])
        );

        if ($this->dic->rbac()->system()->checkAccess('write', $this->gui->ref_id)) {
            $current_selection_list = new ilAdvancedSelectionListGUI();
            $current_selection_list->setListTitle($this->lng->txt('actions'));
            $current_selection_list->setId('act_' . $a_set['smiley_id']);
            
            $this->ctrl->setParameter($this->gui, 'smiley_id', $a_set['smiley_id']);
            $current_selection_list->addItem(
                $this->lng->txt('edit'),
                '',
                $this->ctrl->getLinkTarget($this->gui, 'smiley-showEditSmileyEntryFormObject')
            );
            $current_selection_list->addItem(
                $this->lng->txt('delete'),
                '',
                $this->ctrl->getLinkTarget($this->gui, 'smiley-showDeleteSmileyFormObject')
            );
            $this->ctrl->setParameter($this->gui, 'smiley_id', null);

            $this->tpl->setVariable('VAL_ACTIONS', $current_selection_list->getHTML());
        }
    }
}
