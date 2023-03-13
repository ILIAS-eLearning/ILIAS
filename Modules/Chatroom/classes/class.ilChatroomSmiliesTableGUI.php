<?php

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

declare(strict_types=1);

/**
 * Class ilChatroomSmiliesTableGUI
 * Prepares table rows and fills them.
 * @author  Andreas Kordosz <akordosz@databay.de>
 * @version $Id$
 * @ingroup ModulesChatroom
 */
class ilChatroomSmiliesTableGUI extends ilTable2GUI
{
    public function __construct(
        private readonly ilChatroomObjectGUI $gui,
        private readonly \ILIAS\UI\Factory $ui_factory,
        private readonly \ILIAS\UI\Renderer $ui_renderer,
        private readonly ilRbacSystem $rbac_system,
        string $cmd
    ) {
        $this->setId('chatroom_smilies_tbl');
        parent::__construct($gui, $cmd);

        $this->setTitle($this->lng->txt('chatroom_available_smilies'));

        $this->addColumn('', 'checkbox', '2%', true);
        $this->addColumn($this->lng->txt('chatroom_smiley_image'), '', '28%');
        $this->addColumn($this->lng->txt('chatroom_smiley_keyword'), 'keyword', '55%');
        $this->addColumn($this->lng->txt('actions'), '', '15%');

        $this->setFormAction($this->ctrl->getFormAction($this->gui));
        $this->setRowTemplate('tpl.chatroom_smiley_list_row.html', 'Modules/Chatroom');
        $this->setSelectAllCheckbox('smiley_id');

        if ($this->rbac_system->checkAccess('write', $this->gui->getRefId())) {
            $this->addMultiCommand(
                'smiley-deleteMultipleObject',
                $this->lng->txt('chatroom_delete_selected')
            );
        }
    }

    protected function fillRow(array $a_set): void
    {
        $this->tpl->setVariable('VAL_SMILEY_ID', $a_set['smiley_id']);
        $this->tpl->setVariable('VAL_SMILEY_PATH', $a_set['smiley_fullpath']);
        $this->tpl->setVariable('VAL_SMILEY_KEYWORDS', $a_set['smiley_keywords']);
        $this->tpl->setVariable(
            'VAL_SMILEY_KEYWORDS_NONL',
            str_replace("\n", "", $a_set['smiley_keywords'])
        );

        if ($this->rbac_system->checkAccess('write', $this->gui->getRefId())) {
            $buttons = [];

            $this->ctrl->setParameter($this->gui, 'smiley_id', $a_set['smiley_id']);
            $buttons[] = $this->ui_factory
                ->button()
                ->shy(
                    $this->lng->txt('edit'),
                    $this->ctrl->getLinkTarget($this->gui, 'smiley-showEditSmileyEntryFormObject')
                );
            $buttons[] = $this->ui_factory
                ->button()
                ->shy(
                    $this->lng->txt('delete'),
                    $this->ctrl->getLinkTarget($this->gui, 'smiley-showDeleteSmileyFormObject')
                );
            $this->ctrl->setParameter($this->gui, 'smiley_id', null);

            $drop_down = $this->ui_factory
                ->dropdown()
                ->standard($buttons)
                ->withLabel($this->lng->txt('actions'));

            $this->tpl->setVariable('VAL_ACTIONS', $this->ui_renderer->render($drop_down));
        }
    }
}
