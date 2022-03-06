<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjCloudListGUI
 * @author  Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @author  Martin Studer martin@fluxlabs.ch
 * $Id:
 * @extends ilObjectListGUI
 */
class ilObjCloudListGUI extends ilObjectListGUI
{
    public function init() : void
    {
        global $DIC;
        $lng = $DIC['lng'];

        $this->copy_enabled = false;
        $this->delete_enabled = true;
        $this->cut_enabled = false;
        $this->subscribe_enabled = true;
        $this->link_enabled = false;
        $this->info_screen_enabled = true;
        $this->timings_enabled = true;
        $this->type = "cld";
        $this->gui_class_name = "ilobjcloudgui";

        // general commands array
        require_once('./Modules/Cloud/classes/class.ilObjCloudAccess.php');
        $this->commands = ilObjCloudAccess::_getCommands();
        $lng->loadLanguageModule("cld");
    }

    public function getCommands() : array
    {
        $object = ilObjectFactory::getInstanceByRefId($this->ref_id);
        $header_action_gui = ilCloudConnector::getHeaderActionGUIClass(ilCloudConnector::getServiceClass($object->getServiceName(),
            $object->getId(), false));
        $custom_urls = [];

        if (method_exists($header_action_gui, "getCustomListActions")) {
            // Fetch custom actions
            $custom_list_actions = $header_action_gui->getCustomListActions();

            if (is_array($custom_list_actions)) {
                // Fetch custom URLs from the custom actions, if available
                $custom_urls = $this->fetchCustomUrlsFromCustomActions($custom_list_actions, $custom_urls);
                // Adjust commands of this object by adding the new custom ones
                $this->commands = array_merge($this->commands, $custom_list_actions);
            }
        }

        // Generate ilias link, check permissions, etc...
        $ref_commands = parent::getCommands();

        // Remove recently added custom actions from dynamic field "commands" as
        // it may pass onto other ListGUIs and mess them up
        if (method_exists($header_action_gui, "getCustomListActions")) {
            $this->commands = $this->neutralizeCommands($this->commands, $custom_list_actions);
        }

        // Inject custom urls, if avilable
        if (!empty($custom_urls)) {
            $ref_commands = $this->injectCustomUrlsInCommands($custom_urls, $ref_commands);
        }

        return $ref_commands;
    }

    public function getProperties() : array
    {
        global $DIC;
        $lng = $DIC['lng'];

        $props = array();
        require_once('./Modules/Cloud/classes/class.ilObjCloudAccess.php');
        if (!ilObjCloudAccess::checkAuthStatus($this->obj_id)) {
            $props[] = array(
                "alert" => true,
                "property" => $lng->txt("status"),
                "value" => $lng->txt("cld_not_authenticated_offline"),
            );
        } else {
            if (!ilObjCloudAccess::checkOnline($this->obj_id)) {
                $props[] = array(
                    "alert" => true,
                    "property" => $lng->txt("status"),
                    "value" => $lng->txt("offline"),
                );
            }
        }

        return $props;
    }

    /**
     * Remove recently added custom actions from dynamic field "commands" as
     * it may pass onto other ListGUIs and mess them up
     * @param array $commands
     * @param array $custom_list_actions
     */
    private function neutralizeCommands(array $commands, array $custom_list_actions) : array
    {
        foreach ($custom_list_actions as $custom_list_action) {
            for ($i = 0, $iMax = count($commands); $i < $iMax; $i++) {
                if ($commands[$i]["lang_var"] === $custom_list_action["lang_var"]) {
                    unset($commands[$i]);
                }
            }
        }
        return $commands;
    }

    /**
     * Inject predefined custom URLs into ref_commands and change its destination
     */
    private function injectCustomUrlsInCommands(array $custom_urls, array $ref_commands) : array
    {
        foreach ($custom_urls as $custom_url) {
            foreach ($ref_commands as &$ref_command) {
                if ($custom_url["id"] === $ref_command["lang_var"]) {
                    $ref_command["link"] = $custom_url["link"];
                }
            }
        }
        return $ref_commands;
    }

    /**
     * Fetches custom URLs from predefined actions and structures them appropriately
     */
    private function fetchCustomUrlsFromCustomActions(array $custom_list_actions, array $custom_urls) : array
    {
        foreach ($custom_list_actions as $custom_list_action) {
            if (array_key_exists("custom_url", $custom_list_action)) {
                $custom_urls[] = [
                    "id" => $custom_list_action["lang_var"],
                    "link" => $custom_list_action["custom_url"],
                ];
            }
        }

        return $custom_urls;
    }
}
