<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * Class ilObjExternalToolsSettingsGUI
 *
 * @author Sascha Hofmann <saschahofmann@gmx.de>
 * @ilCtrl_Calls ilObjExternalToolsSettingsGUI: ilPermissionGUI
 */
class ilObjExternalToolsSettingsGUI extends ilObjectGUI
{
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

    public function getAdminTabs()
    {
        $this->getTabs();
    }
    
    protected function getTabs()
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

    /**
     * Configure MathJax settings
     */
    public function editMathJaxObject() : void
    {
        $tpl = $this->tpl;
        $this->__initSubTabs("editMathJax");
        $form = $this->getMathJaxForm();
        $tpl->setContent($form->getHTML());
    }

    protected function getMathJaxForm() : ilPropertyFormGUI
    {
        $ilAccess = $this->access;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $mathJaxSetting = new ilSetting("MathJax");
        $path_to_mathjax = $mathJaxSetting->get("path_to_mathjax");

        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this));
        $form->setTitle($lng->txt("mathjax_settings"));
        
        // Enable MathJax
        $enable = new ilCheckboxInputGUI($lng->txt("mathjax_enable_client"), "enable");
        $enable->setChecked($mathJaxSetting->get("enable"));
        $enable->setInfo($lng->txt("mathjax_enable_mathjax_info") . " <a target='blank' href='https://www.mathjax.org/'>"
            . $lng->txt("mathjax_home_link") . "</a>");
        $form->addItem($enable);

        // Path to mathjax
        $text_prop = new ilTextInputGUI($lng->txt("mathjax_path_to_mathjax"), "path_to_mathjax");
        $text_prop->setInfo($lng->txt("mathjax_path_to_mathjax_desc"));
        $text_prop->setValue($path_to_mathjax);
        $text_prop->setRequired(true);
        $text_prop->setMaxLength(400);
        $text_prop->setSize(100);
        $enable->addSubItem($text_prop);
        
        // mathjax limiter
        $options = array(
            0 => '\&#8203;(...\&#8203;)',
            1 => '[tex]...[/tex]',
            2 => '&lt;span class="math"&gt;...&lt;/span&gt;'
            );
        $si = new ilSelectInputGUI($this->lng->txt("mathjax_limiter"), "limiter");
        $si->setOptions($options);
        $si->setValue($mathJaxSetting->get("limiter"));
        $si->setInfo($this->lng->txt("mathjax_limiter_info"));
        $enable->addSubItem($si);

        $install_link = ' <a target="_blank" href="Services/MathJax/docs/Install-MathJax-Server.txt">'
            . $lng->txt("mathjax_server_installation") . '</a>';
        $clear_cache_link = ' <a href="' . $this->ctrl->getLinkTarget($this, 'clearMathJaxCache') . '"">'
            . $lng->txt("mathjax_server_clear_cache") . '</a>';

        // Enable Server MathJax
        $server = new ilCheckboxInputGUI($lng->txt("mathjax_enable_server"), "enable_server");
        $server->setChecked($mathJaxSetting->get("enable_server"));
        $server->setInfo($lng->txt("mathjax_enable_server_info") . $install_link);

        $form->addItem($server);

        // Path to Server MathJax
        $text_prop = new ilTextInputGUI($lng->txt("mathjax_server_address"), "server_address");
        $text_prop->setInfo($lng->txt("mathjax_server_address_info"));
        $text_prop->setValue($mathJaxSetting->get("server_address"));
        $text_prop->setRequired(true);
        $text_prop->setMaxLength(400);
        $text_prop->setSize(100);
        $server->addSubItem($text_prop);

        // Server Timeout
        $number_prop = new ilNumberInputGUI($lng->txt("mathjax_server_timeout"), "server_timeout");
        $number_prop->setInfo($lng->txt("mathjax_server_timeout_info"));
        $number_prop->setValue($mathJaxSetting->get("server_timeout") ? (int) $mathJaxSetting->get("server_timeout") : 5);
        $number_prop->setRequired(true);
        $number_prop->setSize(3);
        $server->addSubItem($number_prop);

        // Server for Browser
        $checkbox = new ilCheckboxInputGUI($lng->txt("mathjax_server_for_browser"), "server_for_browser");
        $checkbox->setInfo($lng->txt("mathjax_server_for_browser_info"));
        $checkbox->setChecked((bool) $mathJaxSetting->get("server_for_browser"));
        $server->addSubItem($checkbox);

        // Server for HTML Export
        $checkbox = new ilCheckboxInputGUI($lng->txt("mathjax_server_for_export"), "server_for_export");
        $checkbox->setInfo($lng->txt("mathjax_server_for_export_info"));
        $checkbox->setChecked((bool) $mathJaxSetting->get("server_for_export"));
        $server->addSubItem($checkbox);

        // Server for PDF
        $checkbox = new ilCheckboxInputGUI($lng->txt("mathjax_server_for_pdf"), "server_for_pdf");
        $checkbox->setInfo($lng->txt("mathjax_server_for_pdf_info"));
        $checkbox->setChecked((bool) $mathJaxSetting->get("server_for_pdf"));
        $server->addSubItem($checkbox);

        // Cache Size / Clear Cache
        $size = new ilNonEditableValueGUI($lng->txt("mathjax_server_cache_size"));
        $size->setInfo($lng->txt("mathjax_server_cache_size_info") . $clear_cache_link);
        $size->setValue(ilMathJax::getInstance()->getCacheSize());
        $server->addSubItem($size);

        // Test expression
        $test = new ilCustomInputGUI($lng->txt("mathjax_test_expression"));
        $html = ilMathJax::getInstance()->insertLatexImages('[tex]f(x)=\int_{-\infty}^x e^{-t^2}dt[/tex]');
        $test->setHtml($html);
        $test->setInfo($lng->txt('mathjax_test_expression_info'));
        $form->addItem($test);

        if ($ilAccess->checkAccess("write", "", $this->object->getRefId())) {
            $form->addCommandButton("saveMathJax", $lng->txt("save"));
        }

        return $form;
    }

    /**
     * Save MathJax Settings
     */
    public function saveMathJaxObject() : void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $ilAccess = $this->access;
        $form = $this->getMathJaxForm();
        if ($ilAccess->checkAccess("write", "", $this->object->getRefId()) &&
            $form->checkInput()) {
            $mathJaxSetting = new ilSetting("MathJax");
            // Client settings
            $path_to_mathjax = $form->getInput("path_to_mathjax");
            if ($form->getInput("enable")) {
                $mathJaxSetting->set("path_to_mathjax", $path_to_mathjax);
                $mathJaxSetting->set("limiter", (int) $form->getInput("limiter"));
            }
            $mathJaxSetting->set("enable", $form->getInput("enable"));

            // Server settings
            if ($form->getInput("enable_server")) {
                $mathJaxSetting->set("server_address", $form->getInput("server_address"));
                $mathJaxSetting->set("server_timeout", (int) $form->getInput("server_timeout"));
                $mathJaxSetting->set("server_for_browser", (bool) $form->getInput("server_for_browser"));
                $mathJaxSetting->set("server_for_export", (bool) $form->getInput("server_for_export"));
                $mathJaxSetting->set("server_for_pdf", (bool) $form->getInput("server_for_pdf"));
            }
            $mathJaxSetting->set("enable_server", (bool) $form->getInput("enable_server"));

            ilUtil::sendInfo($lng->txt("msg_obj_modified"));
        }
        $ilCtrl->redirect($this, "editMathJax");
    }

    /**
     * Clear the directory with cached LaTeX graphics
     */
    public function clearMathJaxCacheObject() : void
    {
        $lng = $this->lng;

        ilMathJax::getInstance()->clearCache();

        ilUtil::sendSuccess($lng->txt('mathjax_server_cache_cleared'), true);
        $this->ctrl->redirect($this, 'editMathJax');
    }

    /**
     * Configure maps settings
     */
    public function editMapsObject() : void
    {
        $tpl = $this->tpl;

        $this->__initSubTabs("editMaps");
        $form = $this->getMapsForm();
        $tpl->setContent($form->getHTML());
    }

    /**
     * Configure maps settings
     */
    public function getMapsForm() : ilPropertyFormGUI
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
        if ($type == "openlayers") {
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
        $loc_prop->setZoom($std_zoom);
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
    public function saveMapsObject() : void
    {
        $ilCtrl = $this->ctrl;

        $form = $this->getMapsForm();
        if ($form->checkInput()) {
            if ($form->getInput("type") == 'openlayers' && 'openlayers' == ilMapUtil::getType()) {
                ilMapUtil::setStdTileServers($form->getInput("title"));
                ilMapUtil::setStdGeolocationServer(
                    $form->getInput("geolocation")
                );
            } else {
                ilMapUtil::setApiKey($form->getInput("api_key"));
            }

            ilMapUtil::setActivated($form->getInput("enable") == "1");
            ilMapUtil::setType($form->getInput("type"));
            $location = $form->getInput("std_location");
            ilMapUtil::setStdLatitude($location["latitude"]);
            ilMapUtil::setStdLongitude($location["longitude"]);
            ilMapUtil::setStdZoom($location["zoom"]);
        }
        $ilCtrl->redirect($this, "editMaps");
    }
    
    // init sub tabs
    public function __initSubTabs(string $a_cmd) : void
    {
        $maps = $a_cmd == 'editMaps';
        $mathjax = $a_cmd == 'editMathJax';

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
            $this->ctrl->getLinkTarget($this, "editMathJax"),
            "",
            "",
            "",
            $mathjax
        );
    }
    
    public function executeCommand() : void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        $this->prepareOutput();

        if (!$this->rbacsystem->checkAccess("visible,read", $this->object->getRefId())) {
            $this->ilias->raiseError($this->lng->txt("permission_denied"), $this->ilias->error_obj->MESSAGE);
        }
        
        switch ($next_class) {
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
                if (!$cmd || $cmd == 'view') {
                    $cmd = "editMaps";
                }
                $cmd .= "Object";
                $this->$cmd();

                break;
        }
    }
}
