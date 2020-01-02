<?php

require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';

/**
 * Methods for building the administration forms
 */
class ilNotificationAdminSettingsForm
{
    public static function getTypeForm($types)
    {
        global $DIC;

        $lng = $DIC->language();

        $lng->loadLanguageModule('notification');

        $form = new ilPropertyFormGUI();

        $options = array(
            'set_by_user' => $lng->txt('set_by_user'),
            'set_by_admin' => $lng->txt('set_by_admin'),
            'disabled' => $lng->txt('disabled'),
        );

        foreach ($types as $type) {
            $select = new ilSelectInputGUI($lng->txt('nott_' . $type['name']), 'notifications[' . $type['name'] . ']');
            $select->setOptions($options);
            $select->setValue($type['config_type']);
            $form->addItem($select);
        }

        return $form;
    }

    public static function getChannelForm($types)
    {
        global $DIC;
        $lng = $DIC->language();

        $form = new ilPropertyFormGUI();

        $options = array(
            'set_by_user' => $lng->txt('set_by_user'),
            'set_by_admin' => $lng->txt('set_by_admin'),
            'disabled' => $lng->txt('disabled'),
        );

        foreach ($types as $type) {
            $select = new ilSelectInputGUI($lng->txt('notc_' . $type['name']), 'notifications[' . $type['name'] . ']');
            $select->setOptions($options);
            $select->setValue($type['config_type']);
            $form->addItem($select);
        }

        return $form;
    }

    public static function getGeneralSettingsForm()
    {
        global $DIC;
        $lng = $DIC->language();

        $form = new ilPropertyFormGUI();

        require_once 'Services/Notifications/classes/class.ilNotificationDatabaseHelper.php';

        $channels = ilNotificationDatabaseHandler::getAvailableChannels(array(), true);

        $options = array(
            'set_by_user' => $lng->txt('set_by_user'),
            'set_by_admin' => $lng->txt('set_by_admin'),
                //'disabled' => $lng->txt('disabled'),
        );
        /**
         * @todo dirty...
         */
        $form->restored_values = array();
        $store_values = array();
        foreach ($channels as $channel) {
            $chb = new ilCheckboxInputGUI($lng->txt('enable_' . $channel['name']), 'enable_' . $channel['name']);

            $store_values[] = 'enable_' . $channel['name'];

            $select = new ilSelectInputGUI($lng->txt('config_type'), 'notifications[' . $channel['name'] . ']');
            $select->setOptions($options);
            $select->setValue($channel['config_type']);
            $chb->addSubItem($select);

            /**
             * @todo dirty...
             */
            $form->restored_values['notifications[' . $channel['name'] . ']'] = $channel['config_type'];
            require_once $channel['include'];
            
            // let the channel display their own settings below the "enable channel"
            // checkbox
            $result = call_user_func(array($channel['handler'], 'showSettings'), $chb);
            if ($result) {
                $store_values = array_merge($result, $store_values);
            }


            $form->addItem($chb);
        }

        /**
         * @todo dirty...
         */
        $form->store_values = $store_values;

        return $form;
    }
}
