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

/**
 * Administration settings form handler
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilAdministrationSettingsFormHandler
{
    /**
     * @var array<string, int>
     */
    protected static array $OBJ_MAP;

    public const FORM_PRIVACY = 1;
    public const FORM_SECURITY = 2;
    public const FORM_LP = 4;
    public const FORM_MAIL = 5;
    public const FORM_COURSE = 6;
    public const FORM_GROUP = 7;
    public const FORM_REPOSITORY = 8;
    public const FORM_LDAP = 9;
    public const FORM_FORUM = 10;
    public const FORM_ACCESSIBILITY = 11;
    public const FORM_WSP = 12;
    public const FORM_TAGGING = 13;
    public const FORM_CERTIFICATE = 14;
    public const FORM_META_COPYRIGHT = 15;
    public const FORM_TOS = 16;

    public const SETTINGS_USER = "usrf";
    public const SETTINGS_GENERAL = "adm";
    public const SETTINGS_FILE = "facs";
    public const SETTINGS_ROLE = "rolf";
    public const SETTINGS_FORUM = "frma";
    public const SETTINGS_LRES = "lrss";
    public const SETTINGS_REPOSITORY = "reps";
    public const SETTINGS_PR = "prss";
    public const SETTINGS_COURSE = "crss";
    public const SETTINGS_GROUP = "grps";
    public const SETTINGS_PRIVACY_SECURITY = "ps";
    public const SETTINGS_CALENDAR = "cals";
    public const SETTINGS_AUTH = "auth";
    public const SETTINGS_WIKI = "wiks";
    public const SETTINGS_PORTFOLIO = "prfa";
    public const SETTINGS_LP_COMPLETION_STATUS = "trac";
    public const SETTINGS_LEARNINGSEQUENCE = "lsos";
    public const SETTINGS_COMMENTS = "coms";

    public const VALUE_BOOL = "bool";

    protected static function initObjectMap(): void
    {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        $tree = $DIC->repositoryTree();

        $map = array("adm" => SYSTEM_FOLDER_ID);
        foreach ($tree->getChilds(SYSTEM_FOLDER_ID) as $obj) {
            $map[$obj["type"]] = (int) $obj["ref_id"];
        }

        self::$OBJ_MAP = $map;
    }

    protected static function getRefId(string $a_obj_type): int
    {
        if (!isset(self::$OBJ_MAP)) {
            self::initObjectMap();
        }
        return self::$OBJ_MAP[$a_obj_type] ?? 0;
    }

    public static function getSettingsGUIInstance(string $a_settings_obj_type): ilObjectGUI
    {
        global $DIC;

        $objDefinition = $DIC["objDefinition"];

        $ref_id = self::getRefId($a_settings_obj_type);
        $obj_type = ilObject::_lookupType($ref_id, true);

        $class_name = $objDefinition->getClassName($obj_type);
        $class_name = "ilObj" . $class_name . "GUI";
        if (is_subclass_of($class_name, "ilObject2GUI")) {
            $gui_obj = new $class_name($ref_id, ilObject2GUI::REPOSITORY_NODE_ID);
        } else {
            $gui_obj = new $class_name([], $ref_id, true, false);
        }

        $gui_obj->setCreationMode(true);

        return $gui_obj;
    }

    public static function addFieldsToForm(
        int $a_form_id,
        ilPropertyFormGUI $a_form,
        ilObjectGUI $a_parent_gui
    ): void {
        switch ($a_form_id) {
            case self::FORM_SECURITY:
                $types = array(self::SETTINGS_USER, self::SETTINGS_FILE, self::SETTINGS_ROLE);
                break;

            case self::FORM_PRIVACY:
                $types = array(self::SETTINGS_ROLE, self::SETTINGS_FORUM, self::SETTINGS_LRES, self::SETTINGS_COMMENTS);
                break;

            case self::FORM_TAGGING:
            case self::FORM_LP:
                $types = array(self::SETTINGS_REPOSITORY);
                break;

            case self::FORM_ACCESSIBILITY:
                $types = array(self::SETTINGS_FORUM, self::SETTINGS_AUTH, self::SETTINGS_WIKI);
                break;

            case self::FORM_MAIL:
                $types = array(self::SETTINGS_COURSE, self::SETTINGS_GROUP, self::SETTINGS_LEARNINGSEQUENCE);
                break;

            case self::FORM_COURSE:
            case self::FORM_GROUP:
                $types = array(self::SETTINGS_PRIVACY_SECURITY, self::SETTINGS_CALENDAR, self::SETTINGS_GENERAL);
                break;

            case self::FORM_WSP:
                $types = array(self::SETTINGS_PORTFOLIO);
                break;

            case self::FORM_CERTIFICATE:
                $types = array(self::SETTINGS_LP_COMPLETION_STATUS);
                break;

            case self::FORM_TOS:
                $types = [self::SETTINGS_USER];
                break;

            default:
                $types = null;
                break;
        }

        if (is_array($types)) {
            foreach ($types as $type) {
                $gui = self::getSettingsGUIInstance($type);
                if ($gui && method_exists($gui, "addToExternalSettingsForm")) {
                    $data = $gui->addToExternalSettingsForm($a_form_id);
                    if (is_array($data)) {
                        self::parseFieldDefinition($type, $a_form, $gui, $data);
                    }
                }
            }
        }

        // cron jobs - special handling

        $parent_gui = new ilObjSystemFolderGUI(null, SYSTEM_FOLDER_ID, true);
        $parent_gui->setCreationMode(true);

        $gui = new ilCronManagerGUI();
        $data = $gui->addToExternalSettingsForm($a_form_id);
        if (is_array($data) && count($data)) {
            self::parseFieldDefinition("cron", $a_form, $parent_gui, $data);
        }
    }

    /**
     * @param mixed $a_field_value
     * @return mixed
     */
    protected static function parseFieldValue(
        ?string $a_field_type,
        &$a_field_value
    ) {
        global $DIC;

        $lng = $DIC->language();

        switch ($a_field_type) {
            case self::VALUE_BOOL:
                $a_field_value = $a_field_value ?
                    $lng->txt("enabled") :
                    $lng->txt("disabled");
                return $a_field_value;
        }

        if (!is_numeric($a_field_value) &&
            $a_field_value !== null && !trim($a_field_value)) {
            $a_field_value = "-";
        }

        return is_numeric($a_field_value) || $a_field_value !== "";
    }

    protected static function parseFieldDefinition(
        string $a_type,
        ilPropertyFormGUI $a_form,
        ilObjectGUI $a_gui,
        $a_data
    ): void {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        $request = new \ILIAS\Administration\AdminGUIRequest(
            $DIC->http(),
            $DIC->refinery()
        );


        $lng = $DIC->language();
        $rbacsystem = $DIC->rbac()->system();
        $ilCtrl = $DIC->ctrl();
        $ilAccess = $DIC->access();

        if (!is_array($a_data)) {
            return;
        }

        ilLoggerFactory::getLogger('root')->dump($a_data, ilLogLevel::ERROR);

        // write permission for current gui?
        $has_write = $ilAccess->checkAccess(
            "write",
            "",
            $request->getRefId()
        );

        foreach ($a_data as $area_caption => $fields) {
            if (is_numeric($area_caption) || !trim($area_caption)) {
                $area_caption = "obj_" . $a_type;
            }

            if (is_array($fields) && count($fields) === 2) {
                $cmd = $fields[0];
                $fields = $fields[1];
                if (is_array($fields)) {
                    $ftpl = new ilTemplate("tpl.external_settings.html", true, true, "Services/Administration");


                    $stack = array();
                    foreach ($fields as $field_caption_id => $field_value) {
                        ilLoggerFactory::getLogger('root')->dump($field_caption_id, ilLogLevel::ERROR);
                        ilLoggerFactory::getLogger('root')->dump($field_value, ilLogLevel::ERROR);
                        $field_type = $subitems = null;
                        if (is_array($field_value)) {
                            $field_type = $field_value[1];
                            $subitems = $field_value[2] ?? [];
                            $field_value = $field_value[0];
                        }

                        if (self::parseFieldValue($field_type, $field_value)) {
                            $ftpl->setCurrentBlock("value_bl");
                            $ftpl->setVariable("VALUE", $field_value);
                            $ftpl->parseCurrentBlock();
                        }

                        if (is_array($subitems)) {
                            $ftpl->setCurrentBlock("subitem_bl");
                            foreach ($subitems as $sub_caption_id => $sub_value) {
                                $sub_type = null;
                                if (is_array($sub_value)) {
                                    $sub_type = $sub_value[1];
                                    $sub_value = $sub_value[0];
                                }
                                self::parseFieldValue($sub_type, $sub_value);

                                $ftpl->setVariable("SUBKEY", $lng->txt($sub_caption_id));
                                $ftpl->setVariable("SUBVALUE", $sub_value);
                                $ftpl->parseCurrentBlock();
                            }
                        }

                        $ftpl->setCurrentBlock("row_bl");
                        $ftpl->setVariable("KEY", $lng->txt($field_caption_id));
                        $ftpl->parseCurrentBlock();
                    }

                    if ($has_write &&
                        $rbacsystem->checkAccess("visible,read", $a_gui->getObject()->getRefId())) {
                        if (!$cmd) {
                            $cmd = "view";
                        }
                        $ilCtrl->setParameter($a_gui, "ref_id", $a_gui->getObject()->getRefId());

                        $ftpl->setCurrentBlock("edit_bl");
                        $ftpl->setVariable("URL_EDIT", $ilCtrl->getLinkTargetByClass(array("ilAdministrationGUI", get_class($a_gui)), $cmd));
                        $ftpl->setVariable("TXT_EDIT", $lng->txt("adm_external_setting_edit"));
                        $ftpl->parseCurrentBlock();
                    }

                    $ext = new ilCustomInputGUI($lng->txt($area_caption));
                    $ext->setHtml($ftpl->get());
                    $a_form->addItem($ext);
                }
            }
        }
    }
}
