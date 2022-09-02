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

namespace ILIAS\Notifications;

use ilLanguage;
use ilTable2GUI;

/**
 * @author Jan Posselt <jposselt@databay.de>
 */
class ilNotificationSettingsTable extends ilTable2GUI
{
    private array $channels;
    private array $userdata;

    private bool $adminMode;
    private bool $editable = true;

    private ilLanguage $language;

    public function __construct(
        ?object $a_ref,
        string $title,
        array $channels,
        array $userdata,
        bool $adminMode = false,
        ilLanguage $language = null
    ) {
        if ($language === null) {
            global $DIC;
            $language = $DIC->language();
        }
        $this->language = $language;

        $this->language->loadLanguageModule('notification');

        $this->channels = $channels;
        $this->userdata = $userdata;
        $this->adminMode = $adminMode;

        parent::__construct($a_ref, $title);
        $this->setTitle($this->language->txt('notification_options'));

        $this->setId('notifications_settings');

        $this->addColumn($this->language->txt('notification_target'), '', '');

        foreach ($channels as $key => $channel) {
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
            if (array_key_exists($a_set['name'], $this->userdata) && in_array(
                $channeltype,
                $this->userdata[$a_set['name']],
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
