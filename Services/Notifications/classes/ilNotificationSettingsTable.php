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

namespace ILIAS\Notifications;

use ilLanguage;
use ilTable2GUI;
use ilNotificationGUI;

/**
 * @author Jan Posselt <jposselt@databay.de>
 */
class ilNotificationSettingsTable extends ilTable2GUI
{
    private bool $editable = true;

    private readonly ilLanguage $language;

    /**
     * @param array<string, array<string, mixed>> $channels
     * @param array<string, list<string>>         $usr_data
     */
    public function __construct(
        ilNotificationGUI $a_ref,
        string $title,
        private readonly array $channels,
        private readonly array $usr_data,
        private readonly bool $adminMode = false,
        ilLanguage $language = null
    ) {
        if ($language === null) {
            global $DIC;
            $language = $DIC->language();
        }
        $this->language = $language;

        $this->language->loadLanguageModule('notification');

        parent::__construct($a_ref, $title);
        $this->setTitle($this->language->txt('notification_options'));

        $this->setId('notifications_settings');

        $this->addColumn($this->language->txt('notification_target'), '', '');

        foreach ($channels as $channel) {
            $this->addColumn(
                $this->language->txt(
                    'notc_' . $channel['title']
                ),
                '',
                '20%',
                false,
                ''
            );
        }

        $this->setRowTemplate('tpl.type_line.html', 'Services/Notifications');
        $this->setSelectAllCheckbox('');
    }

    public function setEditable(bool $editable): void
    {
        $this->editable = $editable;
    }

    public function isEditable(): bool
    {
        return $this->editable;
    }

    protected function fillRow(array $a_set): void
    {
        $this->tpl->setVariable('NOTIFICATION_TARGET', $this->language->txt('nott_' . $a_set['title']));

        foreach ($this->channels as $channeltype => $channel) {
            if (array_key_exists($a_set['name'], $this->usr_data) && in_array(
                $channeltype,
                $this->usr_data[$a_set['name']],
                true
            )) {
                $this->tpl->touchBlock('notification_cell_checked');
            }

            if (!$this->isEditable()) {
                $this->tpl->touchBlock('notification_cell_disabled');
            }

            $this->tpl->setCurrentBlock('notification_cell');

            if (
                $this->adminMode &&
                isset($channel['config_type'], $a_set['config_type']) &&
                $channel['config_type'] === 'set_by_user' &&
                $a_set['config_type'] === 'set_by_user'
            ) {
                $this->tpl->setVariable('NOTIFICATION_SET_BY_USER_CELL', 'optionSetByUser');
            }

            $this->tpl->setVariable('CHANNEL', $channeltype);
            $this->tpl->setVariable('TYPE', $a_set['name']);

            $this->tpl->parseCurrentBlock();
        }
    }
}
