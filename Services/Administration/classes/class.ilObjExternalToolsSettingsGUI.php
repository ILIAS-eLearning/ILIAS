<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObjectGUI.php";


/**
* Class ilObjExternalToolsSettingsGUI
*
* @author Sascha Hofmann <saschahofmann@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilObjExternalToolsSettingsGUI: ilPermissionGUI
*
* @extends ilObjectGUI
*/
class ilObjExternalToolsSettingsGUI extends ilObjectGUI
{
    /**
     * @var ilRbacSystem
     */
    protected $rbacsystem;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * @var ilRbacReview
     */
    protected $rbacreview;

    /**
    * Constructor
    * @access public
    */
    public function __construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output = true)
    {
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
    
    /**
    * get tabs
    * @access	public
    * @param	object	tabs gui object
    */
    public function getTabs()
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
    public function editMathJaxObject()
    {
        $ilAccess = $this->access;
        $rbacreview = $this->rbacreview;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        
        $mathJaxSetting = new ilSetting("MathJax");
        $path_to_mathjax = $mathJaxSetting->get("path_to_mathjax");
        
        $this->__initSubTabs("editMathJax");

        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this));
        $form->setTitle($lng->txt("mathjax_settings"));
        
        // Enable MathJax
        $enable = new ilCheckboxInputGUI($lng->txt("mathjax_enable_client"), "enable");
        $enable->setChecked($mathJaxSetting->get("enable"));
        $enable->setInfo($lng->txt("mathjax_enable_mathjax_info") . " <a target='blank' href='http://www.mathjax.org/'>"
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

        include_once './Services/MathJax/classes/class.ilMathJax.php';
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
        include_once './Services/MathJax/classes/class.ilMathJax.php';
        $html = ilMathJax::getInstance()->insertLatexImages('[tex]f(x)=\int_{-\infty}^x e^{-t^2}dt[/tex]');
        $test->setHtml($html);
        $test->setInfo($lng->txt('mathjax_test_expression_info'));
        $form->addItem($test);

        if ($ilAccess->checkAccess("write", "", $this->object->getRefId())) {
            $form->addCommandButton("saveMathJax", $lng->txt("save"));
        }
                
        $tpl->setVariable("ADM_CONTENT", $form->getHTML());
    }

    /**
     * Save MathJax Setttings
     */
    public function saveMathJaxObject()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $ilAccess = $this->access;
        
        if ($ilAccess->checkAccess("write", "", $this->object->getRefId())) {
            $mathJaxSetting = new ilSetting("MathJax");
            // Client settings
            $path_to_mathjax = ilUtil::stripSlashes($_POST["path_to_mathjax"]);
            if ($_POST["enable"]) {
                $mathJaxSetting->set("path_to_mathjax", $path_to_mathjax);
                $mathJaxSetting->set("limiter", (int) $_POST["limiter"]);
            }
            $mathJaxSetting->set("enable", ilUtil::stripSlashes($_POST["enable"]));

            // Server settings
            if ($_POST["enable_server"]) {
                $mathJaxSetting->set("server_address", ilUtil::stripSlashes($_POST["server_address"]));
                $mathJaxSetting->set("server_timeout", (int) ilUtil::stripSlashes($_POST["server_timeout"]));
                $mathJaxSetting->set("server_for_browser", (bool) ilUtil::stripSlashes($_POST["server_for_browser"]));
                $mathJaxSetting->set("server_for_export", (bool) ilUtil::stripSlashes($_POST["server_for_export"]));
                $mathJaxSetting->set("server_for_pdf", (bool) ilUtil::stripSlashes($_POST["server_for_pdf"]));
            }
            $mathJaxSetting->set("enable_server", (bool) ilUtil::stripSlashes($_POST["enable_server"]));

            ilUtil::sendInfo($lng->txt("msg_obj_modified"));
        }
        $ilCtrl->redirect($this, "editMathJax");
    }

    /**
     * Clear the directory with cached LaTeX graphics
     */
    public function clearMathJaxCacheObject()
    {
        $lng = $this->lng;

        include_once './Services/MathJax/classes/class.ilMathJax.php';
        ilMathJax::getInstance()->clearCache();

        ilUtil::sendSuccess($lng->txt('mathjax_server_cache_cleared'), true);
        $this->ctrl->redirect($this, 'editMathJax');
    }

    /**
    * Configure maps settings
    *
    * @access	public
    */
    public function editMapsObject()
    {
        require_once("Services/Maps/classes/class.ilMapUtil.php");
        
        $ilAccess = $this->access;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        
        $this->__initSubTabs("editMaps");
        $std_latitude = ilMapUtil::getStdLatitude();
        $std_longitude = ilMapUtil::getStdLongitude();
        $std_zoom = ilMapUtil::getStdZoom();
        $type = ilMapUtil::getType();
        
        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        include_once("./Services/Form/classes/class.ilCheckboxOption.php");
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
        
        $tpl->setVariable("ADM_CONTENT", $form->getHTML());
    }


    /**
    * Save Maps Setttings
    */
    public function saveMapsObject()
    {
        require_once("Services/Maps/classes/class.ilMapUtil.php");
        
        $ilCtrl = $this->ctrl;
        if (ilUtil::stripSlashes($_POST["type"]) == 'openlayers' && 'openlayers' == ilMapUtil::getType()) {
            ilMapUtil::setStdTileServers(ilUtil::stripSlashes($_POST["tile"]));
            ilMapUtil::setStdGeolocationServer(ilUtil::stripSlashes($_POST["geolocation"]));
        } else {
            ilMapUtil::setApiKey(ilUtil::stripSlashes(trim($_POST["api_key"])));
        }

        ilMapUtil::setActivated(ilUtil::stripSlashes($_POST["enable"]) == "1");
        ilMapUtil::setType(ilUtil::stripSlashes($_POST["type"]));
        ilMapUtil::setStdLatitude(ilUtil::stripSlashes($_POST["std_location"]["latitude"]));
        ilMapUtil::setStdLongitude(ilUtil::stripSlashes($_POST["std_location"]["longitude"]));
        ilMapUtil::setStdZoom(ilUtil::stripSlashes($_POST["std_location"]["zoom"]));
        $ilCtrl->redirect($this, "editMaps");
    }
    
    // init sub tabs
    public function __initSubTabs($a_cmd)
    {
        $maps = ($a_cmd == 'editMaps') ? true : false;
        $mathjax = ($a_cmd == 'editMathJax') ? true : false;

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
    
    public function executeCommand()
    {
        $ilAccess = $this->access;
        
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        $this->prepareOutput();
                
        if (!$ilAccess->checkAccess("read", "", $this->object->getRefId())) {
            $this->ilias->raiseError($this->lng->txt("permission_denied"), $this->ilias->error_obj->MESSAGE);
        }
        
        switch ($next_class) {
            case 'ilecssettingsgui':
                $this->tabs_gui->setTabActive('ecs_server_settings');
                include_once('./Services/WebServices/ECS/classes/class.ilECSSettingsGUI.php');
                $this->ctrl->forwardCommand(new ilECSSettingsGUI());
                break;
            
            case 'ilpermissiongui':
                include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
                $perm_gui = new ilPermissionGUI($this);
                $ret = &$this->ctrl->forwardCommand($perm_gui);
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
        return true;
    }
} // END class.ilObjExternalToolsSettingsGUI
