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

/**
 * Show administrate clipboard content
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilUserClipboardTableGUI extends ilTable2GUI
{
    private ilUserClipboard $clipboard;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        int $a_id
    ) {
        $this->setId('obj_table_' . $a_id);
        parent::__construct($a_parent_obj, $a_parent_cmd, '');

        $this->clipboard = ilUserClipboard::getInstance($a_id);
        $this->lng->loadLanguageModule('user');
    }

    public function init(): void
    {
        $this->setTitle($this->lng->txt('clipboard_table_title'));

        $this->addColumn('', 'id', '5px');
        $this->addColumn($this->lng->txt('name'), 'name', '70%');
        $this->addColumn($this->lng->txt('login'), 'login', '30%');

        $this->setOrderColumn('name');
        $this->setRowTemplate('tpl.usr_clipboard_table_row.html', 'Services/User');

        $this->setSelectAllCheckbox('uids');

        $this->addMultiCommand(
            'addFromClipboard',
            $this->lng->txt('add')
        );

        $this->addMultiCommand(
            'removeFromClipboard',
            $this->lng->txt('clipboard_remove_btn')
        );

        $this->addCommandButton('emptyClipboard', $this->lng->txt('clipboard_empty_btn'));
        $this->addCommandButton('cancel', $this->lng->txt('cancel'));
    }

    /**
     * @param array<string,mixed> $a_set
     */
    protected function fillRow(array $a_set): void
    {
        $this->tpl->setVariable('VAL_POSTNAME', 'uids');
        $this->tpl->setVariable('VAL_ID', $a_set['usr_id']);
        $this->tpl->setVariable('VAL_NAME', $a_set['name']);
        $this->tpl->setVariable('VAL_LOGIN', $a_set['login']);
    }

    public function parse(): void
    {
        $content = array();
        foreach ($this->clipboard->getValidatedContent() as $user_id) {
            $row['usr_id'] = $user_id;
            $name_arr = ilObjUser::_lookupName($user_id);

            $row['name'] = ($name_arr['lastname'] . ', ' . $name_arr['firstname']);
            $row['login'] = ilObjUser::_lookupLogin($user_id);

            $content[] = $row;
        }
        $this->setMaxCount(count($this->clipboard->getValidatedContent()));
        $this->setData($content);
    }
}
