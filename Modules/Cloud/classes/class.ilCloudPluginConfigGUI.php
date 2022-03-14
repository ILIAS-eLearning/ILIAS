<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/Component/classes/class.ilPluginConfigGUI.php");
require_once("class.ilCloudPluginConfig.php");

/**
 * Class ilCloudPluginConfigGUI
 * GUI class for the administration settings. Plugin classes can extend this method and override getFields to declare
 * the fields needed for the input of the settings.
 * public function getFields()
 * {
 *  return array(
 *   "app_name"               => array("type" => "ilTextInputGUI", "info" => "config_info_app_name", "subelements" => null),
 *  );
 * }
 * @author  Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @author  Martin Studer martin@fluxlabs.ch
 * @version $Id$
 * @extends ilPluginConfigGUI
 * @ingroup ModulesCloud
 */
abstract class ilCloudPluginConfigGUI extends ilPluginConfigGUI
{
    protected ilCloudPluginConfig $object;
    protected array $fields = array();

    public function getFields() : ?array
    {
        return null;
    }

    public function getTableName() : string
    {
        return $this->getPluginObject()->getPluginConfigTableName();
    }

    public function getObject() : ilCloudPluginConfig
    {
        return $this->object;
    }

    /**
     * Handles all commmands, default is "configure"
     */
    public function performCommand(string $cmd) : void
    {
        require_once("class.ilCloudPluginConfig.php");
        $this->object = new ilCloudPluginConfig($this->getTableName());
        $this->fields = $this->getFields();
        switch ($cmd) {
            case "configure":
            case "save":
                $this->$cmd();
                break;
        }
    }

    public function configure() : void
    {
        global $DIC;
        $tpl = $DIC['tpl'];

        $this->initConfigurationForm();
        $this->getValues();
        $tpl->setContent($this->form->getHTML());
    }

    public function getValues() : array
    {
        foreach ($this->fields as $key => $item) {
            $values[$key] = $this->object->getValue($key);
            if (is_array($item["subelements"])) {
                foreach ($item["subelements"] as $subkey => $subitem) {
                    $values[$key . "_" . $subkey] = $this->object->getValue($key . "_" . $subkey);
                }
            }
        }

        $this->form->setValuesByArray($values);
    }

    public function initConfigurationForm() : ilPropertyFormGUI
    {
        global $DIC;
        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];

        require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        $this->form = new ilPropertyFormGUI();

        foreach ($this->fields as $key => $item) {
            $field = new $item["type"]($this->plugin_object->txt($key), $key);
            $field->setInfo($this->plugin_object->txt($item["info"]));
            if (is_array($item["subelements"])) {
                foreach ($item["subelements"] as $subkey => $subitem) {
                    $subfield = new $subitem["type"]($this->plugin_object->txt($key . "_" . $subkey),
                        $key . "_" . $subkey);
                    $subfield->setInfo($this->plugin_object->txt($subitem["info"]));
                    $field->addSubItem($subfield);
                }
            }

            $this->form->addItem($field);
        }

        $this->form->addCommandButton("save", $lng->txt("save"));

        $this->form->setTitle($this->plugin_object->txt("configuration"));
        $this->form->setFormAction($ilCtrl->getFormAction($this));

        return $this->form;
    }

    public function save() : void
    {
        global $DIC;
        $tpl = $DIC['tpl'];
        $ilCtrl = $DIC['ilCtrl'];

        $this->initConfigurationForm();
        if ($this->form->checkInput()) {

            // Save Checkbox Values
            foreach ($this->fields as $key => $item) {
                $this->object->setValue($key, $this->form->getInput($key));
                if (is_array($item["subelements"])) {
                    foreach ($item["subelements"] as $subkey => $subitem) {
                        $this->object->setValue($key . "_" . $subkey, $this->form->getInput($key . "_" . $subkey));
                    }
                }
            }

            $ilCtrl->redirect($this, "configure");
        } else {
            $this->form->setValuesByPost();
            $tpl->setContent($this->form->getHtml());
        }
    }
}
