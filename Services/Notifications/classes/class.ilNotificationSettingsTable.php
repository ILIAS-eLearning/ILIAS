<?php

require_once 'Services/Table/classes/class.ilTable2GUI.php';

class ilNotificationSettingsTable extends ilTable2GUI {

    private $channels;
    private $userdata = array();

    private $adminMode = false;
    private $editable = true;

    public function __construct($a_ref, $title, $channels, $userdata, $adminMode = false) {

        global $lng, $ilCtrl;

        $lng->loadLanguageModule('notification');

        $this->channels = $channels;
        $this->userdata = $userdata;
        $this->adminMode = $adminMode;

        parent::__construct($a_ref, $title);
        $this->setTitle($lng->txt('notification_options'));

        $this->setId('notifications_settings');

        $this->addColumn($lng->txt('notification_target'), '', '');

        foreach ($channels as $key => $channel) {
            $this->addColumn($lng->txt('notc_' . $channel['title']), '', '20%', false, ($channel['config_type'] == 'set_by_user' && false ? 'optionSetByUser' : ''));
        }

        $this->setRowTemplate('tpl.type_line.html', 'Services/Notifications');
        $this->setSelectAllCheckbox('');
    }

    public function setEditable($editable) {
        $this->editable = $editable;
    }

    public function isEditable() {
        return (bool) $this->editable;
    }

    public function fillRow($type) {
        global $ilCtrl, $lng;
        $this->tpl->setVariable('NOTIFICATION_TARGET', $lng->txt('nott_' . $type['title']));

        foreach ($this->channels as $channeltype => $channel) {
            if (array_key_exists($type['name'], $this->userdata) && in_array($channeltype, $this->userdata[$type['name']]))
                    $this->tpl->touchBlock ('notification_cell_checked');

            if (!$this->isEditable()) {
                $this->tpl->touchBlock ('notification_cell_disabled');
            }

            $this->tpl->setCurrentBlock('notification_cell');

            if ($this->adminMode && $channel['config_type'] == 'set_by_user' && $type['config_type'] == 'set_by_user')
                $this->tpl->setVariable('NOTIFICATION_SET_BY_USER_CELL', 'optionSetByUser');

            $this->tpl->setVariable('CHANNEL', $channeltype);
            $this->tpl->setVariable('TYPE', $type['name']);
            
            $this->tpl->parseCurrentBlock();
        }
    }

}

?>
