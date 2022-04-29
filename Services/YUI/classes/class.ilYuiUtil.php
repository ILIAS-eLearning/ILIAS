<?php

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
 * Yahoo YUI Library Utility functions
 * @author Alexander Killing <killing@leifos.de>
 */
class ilYuiUtil
{
    protected const YUI_BASE = "./libs/bower/bower_components/yui2/build";
    
    private static function ensureGlobalTemplate(
        ?ilGlobalTemplateInterface $main_tpl = null
    ) : ilGlobalTemplateInterface {
        global $DIC;
        return $main_tpl === null ? $DIC->ui()->mainTemplate() : $main_tpl;
    }
    
    /**
     * Init YUI Connection module
     */
    public static function initConnection(
        ?ilGlobalTemplateInterface $a_main_tpl = null
    ) : void {
        $tpl = self::ensureGlobalTemplate($a_main_tpl);
        $tpl->addJavaScript(self::YUI_BASE . "/yahoo-dom-event/yahoo-dom-event.js");
        $tpl->addJavaScript(self::YUI_BASE . "/connection/connection-min.js");
    }


    /**
     * Init YUI Event
     */
    public static function initEvent(
        ?ilGlobalTemplateInterface $a_main_tpl = null
    ) : void {
        $tpl = self::ensureGlobalTemplate($a_main_tpl);
        $tpl->addJavaScript(self::YUI_BASE . "/yahoo-dom-event/yahoo-dom-event.js");
    }


    /**
     * Init YUI Dom
     */
    public static function initDom(
        ?ilGlobalTemplateInterface $a_main_tpl = null
    ) : void {
        $tpl = self::ensureGlobalTemplate($a_main_tpl);
        $tpl->addJavaScript(self::YUI_BASE . "/yahoo-dom-event/yahoo-dom-event.js");
    }


    /**
     * Init YUI Drag and Drop
     * used in Modules/Survey, Services/Calendar, Services/COPage, Services/Form (Jan 2022)
     * @deprecated
     */
    public static function initDragDrop(
        ?ilGlobalTemplateInterface $a_main_tpl = null
    ) : void {
        $tpl = self::ensureGlobalTemplate($a_main_tpl);
        $tpl->addJavaScript(self::YUI_BASE . "/yahoo-dom-event/yahoo-dom-event.js");
        $tpl->addJavaScript(self::YUI_BASE . "/dragdrop/dragdrop-min.js");
        $tpl->addJavaScript(self::YUI_BASE . "/element/element-min.js");
    }


    /**
     * Init YUI DomEvent
     * used in Services/Calendar, Modules/Session, Modules/Test (Jan 2022)
     * @deprecated
     */
    public static function initDomEvent(
        ?ilGlobalTemplateInterface $a_main_tpl = null
    ) : void {
        $tpl = self::ensureGlobalTemplate($a_main_tpl);
        $tpl->addJavaScript(self::YUI_BASE . "/yahoo-dom-event/yahoo-dom-event.js");
    }

    /**
     * Init yui panel
     * used in Modules/Test, Services/TermsOfService (Jan 2022)
     * @deprecated
     */
    public static function initPanel(
        bool $a_resize = false,
        ?ilGlobalTemplateInterface $a_main_tpl = null
    ) : void {
        $tpl = self::ensureGlobalTemplate($a_main_tpl);
        $tpl->addJavaScript(self::YUI_BASE . "/yahoo-dom-event/yahoo-dom-event.js");
        $tpl->addJavaScript(self::YUI_BASE . "/container/container-min.js");
        self::addContainerCss($tpl);
        $tpl->addCss("./Services/Calendar/css/panel_min.css");

        if ($a_resize) {
            $tpl->addCss(self::YUI_BASE . "/resize/assets/skins/sam/resize.css");
            $tpl->addJavaScript(self::YUI_BASE . "/utilities/utilities-min.js");
            $tpl->addJavaScript(self::YUI_BASE . "/resize/resize-min.js");
        }
    }


    /**
     * Init YUI connection and animation module
     * used in Modules/Test (Jan 2022)
     * @deprecated
     */
    public static function initConnectionWithAnimation() : void
    {
        $tpl = self::ensureGlobalTemplate();
        $tpl->addJavaScript(self::YUI_BASE . "/yahoo-dom-event/yahoo-dom-event.js");
        $tpl->addJavaScript(self::YUI_BASE . "/animation/animation-min.js");
        $tpl->addJavaScript(self::YUI_BASE . "/connection/connection-min.js");
    }



    /**
     * Init YUI Overlay module
     * used in Modules/Test, Services/TermsOfService, Services/Tracking, Services/UIComponent
     * @deprecated
     */
    public static function initOverlay(
        ?ilGlobalTemplateInterface $a_main_tpl = null
    ) : void {
        $tpl = self::ensureGlobalTemplate($a_main_tpl);
        $tpl->addJavaScript(self::YUI_BASE . "/yahoo-dom-event/yahoo-dom-event.js");
        $tpl->addJavaScript(self::YUI_BASE . "/container/container_core-min.js");
        self::addContainerCss($tpl);
    }


    /**
     * init button control
     * In the moment used for calendar color picker button
     */
    public static function initButtonControl() : void
    {
        $tpl = self::ensureGlobalTemplate();
        $tpl->addJavaScript(self::YUI_BASE . "/yahoo-dom-event/yahoo-dom-event.js");
        $tpl->addJavaScript(self::YUI_BASE . "/element/element-min.js");

        $tpl->addJavaScript(self::YUI_BASE . "/container/container_core-min.js");
        $tpl->addJavaScript(self::YUI_BASE . "/menu/menu-min.js");

        $tpl->addJavaScript(self::YUI_BASE . "/button/button-min.js");

        $tpl->addCss(self::YUI_BASE . "/button/assets/skins/sam/button.css");
        $tpl->addCss(self::YUI_BASE . "/menu/assets/skins/sam/menu.css");
    }

    /**
     * used in Services/Authentication (Session Reminder) Jan 2022
     * @deprecated
     */
    public static function initCookie() : void
    {
        $tpl = self::ensureGlobalTemplate();
        $tpl->addJavaScript(self::YUI_BASE . "/yahoo/yahoo-min.js", 1);
        $tpl->addJavaScript(self::YUI_BASE . "/cookie/cookie.js", 1);
    }


    /**
     * Get local path of a YUI js file
     */
    public static function getLocalPath(string $a_name = "") : string
    {
        return self::YUI_BASE . "/" . $a_name;
    }


    /**
     * Add container css
     */
    protected static function addContainerCss(
        ?ilGlobalTemplateInterface $a_main_tpl = null
    ) : void {
        $tpl = self::ensureGlobalTemplate($a_main_tpl);
        $tpl->addCss(self::getLocalPath("container/assets/skins/sam/container.css"));
    }
}
