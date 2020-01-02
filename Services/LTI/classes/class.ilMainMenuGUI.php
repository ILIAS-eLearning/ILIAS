<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */
namespace LTI;

use LTI\ilMainMenuGUI;

use 	\ilSearchSettings;
use \ilMainMenuSearchGUI;
use \ilUIHookProcessor;
use \ilRegistrationSettings;
use \ilSetting;
use \iljQueryUtil;
use \ilPlayerUtil;
use \ilLink;
use \ilNotificationOSDHandler;
use \ilGlyphGUI;
use \ilObjSystemFolder;
use \ilUtil;
use \ilSession;
use \ilMemberViewSettings;
use \ilObject;

include_once("Services/Mail/classes/class.ilMailGlobalServices.php");
require_once("Services/MainMenu/classes/class.ilMainMenuGUI.php");

/**
* Handles display of the main menu for LTI
*
* @author Stefan Schneider
* @version $Id$
*/
class ilMainMenuGUI extends \ilMainMenuGUI
{
    /**
    * @param	string		$a_target				target frame
    * @param	boolean		$a_use_start_template	true means: target scripts should
    *												be called through start template
    */
    
    public function __construct($a_target = "_top", $a_use_start_template = false, $a_main_tpl = null)
    {
        global $ilias, $rbacsystem, $ilUser, $ilLog, $DIC, $lng;
        
        if ($a_main_tpl != null) {
            $this->main_tpl = $a_main_tpl;
        } else {
            $this->main_tpl = $DIC["tpl"];
        }
        
        $this->rbacsystem = $DIC->rbac()->system();
        $this->user = $DIC->user();
        $this->lng = $DIC->language();
        $this->plugin_admin = $DIC["ilPluginAdmin"];
        $this->tree = $DIC->repositoryTree();
        $this->access = $DIC->access();
        $this->nav_history = $DIC["ilNavigationHistory"];
        $this->settings = $DIC->settings();
        $this->ctrl = $DIC->ctrl();
        $this->help = $DIC["ilHelp"];
        $this->ui = $DIC->ui();
        $rbacsystem = $DIC->rbac()->system();
        $ilUser = $DIC->user();
        
        $this->tpl = new ilTemplate(
            "tpl.main_menu.html",
            true,
            true,
            "Services/LTI"
        );
        
        $this->target = $a_target;
        $this->start_template = $a_use_start_template;
        
        $this->mail = false;
        
        $this->setMode(self::MODE_FULL);
        
        // member view
        include_once './Services/Container/classes/class.ilMemberViewSettings.php';
        $set = ilMemberViewSettings::getInstance();
        if ($set->isActive()) {
            $ref_id = ilMemberViewSettings::getInstance()->getCurrentRefId();

            if (!$ref_id) {
                $DIC["lti"]->member_view = false;
                $DIC["lti"]->member_view_url = "";
                return;
            }
            include_once './Services/Link/classes/class.ilLink.php';
            $url = ilLink::_getLink(
                $ref_id,
                ilObject::_lookupType(ilObject::_lookupObjId($ref_id)),
                array('mv' => 0)
            );
            $DIC["lti"]->member_view = true;
            $DIC["lti"]->member_view_url = $url;
            $DIC["lti"]->member_view_close_txt = $lng->txt('mem_view_close');
        } else {
            $DIC["lti"]->member_view = false;
            $DIC["lti"]->member_view_url = "";
        }
    }
    
    
    public function getSpacerClass()
    {
        return "ilFixedTopSpacerBarOnly";
    }
    
    /**
     * @deprecated
     * @return string
     * @throws ilTemplateException
     */
    public function getHTML()
    {
        $this->setTemplateVars();

        return $this->tpl->get();
    }


    /**
    * set all template variables (images, scripts, target frames, ...)
    */
    private function setTemplateVars()
    {
        global $DIC, $rbacsystem, $lng, $ilias, $tree, $ilUser, $ilSetting, $ilPluginAdmin;
        //$DIC["lti"]->log("setTemplateVars in ilMainMenu");
        // append internal and external LTI css just before </body> end-tag
        $view = $DIC["lti"];
        if ($this->main_tpl->blockExists('view_append_inline_css')) {
            $css_html = "";
            $css = $view->appendInlineCss();
            foreach ($css as $cssfile) {
                $css_html .= "<style type=\"text/css\">\n";
                $css_html .= file_get_contents($cssfile);
                $css_html .= "</style>\n";
            }
            $this->main_tpl->setCurrentBlock("view_append_inline_css");
            $this->main_tpl->setVariable("APPEND_STYLES", $css_html);
            $this->main_tpl->parseCurrentBlock();
        }
        $view->render($this->tpl, 'top_bar_header');
        if (!$view->member_view) {
            $view->render($this->tpl, 'view_nav');
            $view->render($this->tpl, 'user_logged_in');
        } else {
            $this->tpl->setVariable("TOPBAR_CLASS", " ilMemberViewMainHeader");
            $this->tpl->setVariable("MEMBER_VIEW_INFO", $lng->txt("mem_view_long"));
        }
        //$view->checkMessages();
        $this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
        include_once("./Modules/SystemFolder/classes/class.ilObjSystemFolder.php");
        $this->tpl->setVariable("TXT_MAIN_MENU", $lng->txt("main_menu"));
        $this->tpl->parseCurrentBlock();
    }
    
    private function log($txt)
    {
        global $DIC;
        $DIC->logger()->lti()->write($txt);
    }
}
