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
 * Class ilObjExternalToolsSettingsGUI
 *
 * @author Sascha Hofmann <saschahofmann@gmx.de>
 * @ilCtrl_Calls ilObjExternalToolsSettingsGUI: ilPermissionGUI, ilMathJaxSettingsGUI
 */
class ilObjExternalToolsSettingsGUI extends ilObjectGUI
{
    public ilRbacSystem $rbacsystem;
    public ilRbacReview $rbacreview;

    public function __construct(
        $a_data,
        int $a_id,
        bool $a_call_by_reference,
        bool $a_prepare_output = true
    ) {
        global $DIC;

        $this->lng = $DIC->language();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->access = $DIC->access();
        $this->rbacreview = $DIC->rbac()->review();
        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC["tpl"];
        $lng = $DIC->language();

        $this->type = "extt";
        parent::__construct($a_data, $a_id, $a_call_by_reference, false);

        $lng->loadLanguageModule("delic");
        $lng->loadLanguageModule("maps");
        $lng->loadLanguageModule("mathjax");
    }

    public function getAdminTabs(): void
    {
        $this->getTabs();
    }

    protected function getTabs(): void
    {
        $rbacsystem = $this->rbacsystem;

        $this->ctrl->setParameter($this, "ref_id", $this->object->getRefId());

        if ($rbacsystem->checkAccess("visible,read", $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "settings",
                $this->ctrl->getLinkTarget($this, "view"),
                array("editMaps", "editMathJax", ""),
                "",
                ""
            );
            $this->lng->loadLanguageModule('ecs');
        }

        if ($rbacsystem->checkAccess('edit_permission', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "perm_settings",
                $this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"),
                array("perm","info","owner"),
                'ilpermissiongui'
            );
        }
    }


    public function editMapsObject(): void
    {
        $tpl = $this->tpl;

        $this->initSubTabs("editMaps");
        $form = $this->getMapsForm();
        $tpl->setContent($form->getHTML());
    }

    public function getMapsForm(): ilPropertyFormGUI
    {
        $ilAccess = $this->access;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $std_latitude = (float) ilMapUtil::getStdLatitude();
        $std_longitude = (float) ilMapUtil::getStdLongitude();
        $std_zoom = ilMapUtil::getStdZoom();
        $type = ilMapUtil::getType();
        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this));
        $form->setTitle($lng->txt("maps_settings"));

        // Enable Maps
        $enable = new ilCheckboxInputGUI($lng->txt("maps_enable_maps"), "enable");
        $enable->setChecked(ilMapUtil::isActivated());
        $enable->setInfo($lng->txt("maps_enable_maps_info"));
        $form->addItem($enable);

        // Select type
        $types = new ilSelectInputGUI($lng->txt("maps_map_type"), "type");
        $types->setOptions(ilMapUtil::getAvailableMapTypes());
        $types->setValue($type);
        $form->addItem($types);

        // map data server property
        if ($type === "openlayers") {
            $tile = new ilTextInputGUI($lng->txt("maps_tile_server"), "tile");
            $tile->setValue(ilMapUtil::getStdTileServers());
            $tile->setInfo(sprintf($lng->txt("maps_custom_tile_server_info"), ilMapUtil::DEFAULT_TILE));
            $geolocation = new ilTextInputGUI($lng->txt("maps_geolocation_server"), "geolocation");
            $geolocation->setValue(ilMapUtil::getStdGeolocationServer());
            $geolocation->setInfo($lng->txt("maps_custom_geolocation_server_info"));

            $form->addItem($tile);
            $form->addItem($geolocation);
        } else {
            // api key for google
            $key = new ilTextInputGUI("Google API Key", "api_key");
            $key->setMaxLength(200);
            $key->setValue(ilMapUtil::getApiKey());
            $form->addItem($key);
        }

        // location property
        $loc_prop = new ilLocationInputGUI(
            $lng->txt("maps_std_location"),
            "std_location"
        );

        $loc_prop->setLatitude($std_latitude);
        $loc_prop->setLongitude($std_longitude);
        $loc_prop->setZoom((int) $std_zoom);
        $form->addItem($loc_prop);

        if ($ilAccess->checkAccess("write", "", $this->object->getRefId())) {
            $form->addCommandButton("saveMaps", $lng->txt("save"));
            $form->addCommandButton("view", $lng->txt("cancel"));
        }

        return $form;
    }

    /**
     * Save Maps Settings
     */
    public function saveMapsObject(): void
    {
        $ilCtrl = $this->ctrl;

        $form = $this->getMapsForm();
        if ($form->checkInput()) {
            if ($form->getInput("type") === 'openlayers' && 'openlayers' === ilMapUtil::getType()) {
                ilMapUtil::setStdTileServers($form->getInput("title"));
                ilMapUtil::setStdGeolocationServer(
                    $form->getInput("geolocation")
                );
            } else {
                ilMapUtil::setApiKey($form->getInput("api_key"));
            }

            ilMapUtil::setActivated($form->getInput("enable") === "1");
            ilMapUtil::setType($form->getInput("type"));
            $location = $form->getInput("std_location");
            ilMapUtil::setStdLatitude((string) $location["latitude"]);
            ilMapUtil::setStdLongitude((string) $location["longitude"]);
            ilMapUtil::setStdZoom((string) $location["zoom"]);
        }
        $ilCtrl->redirect($this, "editMaps");
    }

    // init sub tabs
    public function initSubTabs(string $a_cmd): void
    {
        $maps = $a_cmd === 'editMaps';
        $mathjax = $a_cmd === 'editMathJax';

        $this->tabs_gui->addSubTabTarget(
            "maps_extt_maps",
            $this->ctrl->getLinkTarget($this, "editMaps"),
            "",
            "",
            "",
            $maps
        );
        $this->tabs_gui->addSubTabTarget(
            "mathjax_mathjax",
            $this->ctrl->getLinkTargetByClass('ilMathJaxSettingsGUI'),
            "",
            "",
            "",
            $mathjax
        );
    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        $this->prepareOutput();

        if (!$this->rbacsystem->checkAccess("visible,read", $this->object->getRefId())) {
            $this->ilias->raiseError($this->lng->txt("permission_denied"), $this->ilias->error_obj->MESSAGE);
        }

        switch ($next_class) {
            case 'ilmathjaxsettingsgui':
                $this->tabs_gui->setTabActive('settings');
                $this->initSubTabs("editMathJax");
                $this->ctrl->forwardCommand(new ilMathJaxSettingsGUI());
                break;

            case 'ilecssettingsgui':
                $this->tabs_gui->setTabActive('ecs_server_settings');
                $this->ctrl->forwardCommand(new ilECSSettingsGUI());
                break;

            case 'ilpermissiongui':
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                $this->tabs_gui->setTabActive('perm_settings');
                break;

            default:
                $this->tabs_gui->setTabActive('settings');
                if (!$cmd || $cmd === 'view') {
                    $cmd = "editMaps";
                }
                $cmd .= "Object";
                $this->$cmd();

                break;
        }
    }
}
