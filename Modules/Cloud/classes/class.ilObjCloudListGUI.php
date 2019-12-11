<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Services/Repository/classes/class.ilObjectPluginListGUI.php";

/**
 * Class ilObjCloudListGUI
 *
 * @author Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * $Id:
 *
 * @extends ilObjectListGUI
 */
class ilObjCloudListGUI extends ilObjectListGUI
{

    /**
     * initialisation
     */
    public function init()
    {
        global $DIC;
        $lng = $DIC['lng'];

        $this->copy_enabled        = false;
        $this->delete_enabled      = true;
        $this->cut_enabled         = false;
        $this->subscribe_enabled   = true;
        $this->link_enabled        = false;
        $this->info_screen_enabled = true;
        $this->timings_enabled     = true;
        $this->type                = "cld";
        $this->gui_class_name      = "ilobjcloudgui";

        // general commands array
        include_once('./Modules/Cloud/classes/class.ilObjCloudAccess.php');
        $this->commands = ilObjCloudAccess::_getCommands();
        $lng->loadLanguageModule("cld");
    }


    public function getCommands()
    {
        $object = ilObjectFactory::getInstanceByRefId($this->ref_id);
        $header_action_gui = ilCloudConnector::getHeaderActionGUIClass(ilCloudConnector::getServiceClass($object->getServiceName(), $object->getId(), false));
        $custom_urls = [];

        if (method_exists($header_action_gui, "getCustomListActions")) {
            // Fetch custom actions
            $custom_list_actions = $header_action_gui->getCustomListActions();

            if (is_array($custom_list_actions)) {
                // Fetch custom URLs from the custom actions, if available
                $this->fetchCustomUrlsFromCustomActions($custom_list_actions, $custom_urls);
                // Adjust commands of this object by adding the new custom ones
                $this->commands = array_merge($this->commands, $custom_list_actions);
            }
        }

        // Generate ilias link, check permissions, etc...
        $ref_commands = parent::getCommands();

        // Remove recently added custom actions from dynamic field "commands" as
        // it may pass onto other ListGUIs and mess them up
        if (method_exists($header_action_gui, "getCustomListActions")) {
            $this->neutralizeCommands($this->commands, $custom_list_actions);
        }

        // Inject custom urls, if avilable
        if (!empty($custom_urls)) {
            $this->injectCustomUrlsInCommands($custom_urls, $ref_commands);
        }

        return $ref_commands;
    }


    /**
     * @return array
     */
    public function getProperties()
    {
        global $DIC;
        $lng = $DIC['lng'];

        $props = array();
        include_once('./Modules/Cloud/classes/class.ilObjCloudAccess.php');
        if (!ilObjCloudAccess::checkAuthStatus($this->obj_id)) {
            $props[] = array("alert" => true, "property" => $lng->txt("status"),
                             "value" => $lng->txt("cld_not_authenticated_offline"));
        } elseif (!ilObjCloudAccess::checkOnline($this->obj_id)) {
            $props[] = array("alert" => true, "property" => $lng->txt("status"),
                             "value" => $lng->txt("offline"));
        }

        return $props;
    }


    /**
     * Remove recently added custom actions from dynamic field "commands" as
     * it may pass onto other ListGUIs and mess them up
     *
     * @param array $commands
     * @param array $custom_list_actions
     */
    private function neutralizeCommands(array &$commands, array $custom_list_actions)
    {
        foreach ($custom_list_actions as $custom_list_action) {
            for ($i = 0; $i < count($commands); $i++) {
                if ($commands[$i]["lang_var"] == $custom_list_action["lang_var"]) {
                    unset($commands[$i]);
                }
            }
        }
    }


    /**
     * Inject predefined custom URLs into ref_commands and change its destination
     *
     * @param $custom_urls
     * @param $ref_commands
     */
    private function injectCustomUrlsInCommands($custom_urls, &$ref_commands)
    {
        foreach ($custom_urls as $custom_url) {
            foreach ($ref_commands as &$ref_command) {
                if ($custom_url["id"] === $ref_command["lang_var"]) {
                    $ref_command["link"] = $custom_url["link"];
                }
            }
        }
    }


    /**
     * Fetches custom URLs from predefined actions and structures them appropriately
     *
     * @param array $custom_list_actions
     * @param       $custom_urls
     */
    private function fetchCustomUrlsFromCustomActions(array $custom_list_actions, &$custom_urls)
    {
        foreach ($custom_list_actions as $custom_list_action) {
            if (array_key_exists("custom_url", $custom_list_action)) {
                array_push(
                    $custom_urls,
                    [
                        "id"   => $custom_list_action["lang_var"],
                        "link" => $custom_list_action["custom_url"],
                    ]
                );
            }
        }
    }
}
