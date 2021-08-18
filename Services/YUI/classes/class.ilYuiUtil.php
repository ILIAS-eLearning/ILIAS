<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Yahoo YUI Library Utility functions
 *
 * @author  Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 */
class ilYuiUtil
{
    const YUI_BASE = "./libs/bower/bower_components/yui2/build";


    /**
     * Init YUI Connection module
     *
     * @param ilGlobalTemplateInterface|null $a_main_tpl
     */
    public static function initConnection(ilGlobalTemplateInterface $a_main_tpl = null)
    {
        global $DIC;

        if ($a_main_tpl == null) {
            $tpl = $DIC["tpl"];
        } else {
            $tpl = $a_main_tpl;
        }
        $tpl->addJavaScript(self::YUI_BASE . "/yahoo-dom-event/yahoo-dom-event.js");
        $tpl->addJavaScript(self::YUI_BASE . "/connection/connection-min.js");
    }


    /**
     * Init YUI Event
     *
     * @param ilGlobalTemplateInterface|null $a_main_tpl
     */
    public static function initEvent(ilGlobalTemplateInterface $a_main_tpl = null)
    {
        global $DIC;

        if ($a_main_tpl == null) {
            $tpl = $DIC["tpl"];
        } else {
            $tpl = $a_main_tpl;
        }

        $tpl->addJavaScript(self::YUI_BASE . "/yahoo-dom-event/yahoo-dom-event.js");
    }


    /**
     * Init YUI Dom
     *
     * @param ilGlobalTemplateInterface|null $a_main_tpl
     */
    public static function initDom(ilGlobalTemplateInterface $a_main_tpl = null)
    {
        global $DIC;

        if ($a_main_tpl == null) {
            $tpl = $DIC["tpl"];
        } else {
            $tpl = $a_main_tpl;
        }

        $tpl->addJavaScript(self::YUI_BASE . "/yahoo-dom-event/yahoo-dom-event.js");
    }


    /**
     * Init YUI Drag and Drop
     *
     * @param ilGlobalTemplateInterface|null $a_main_tpl
     */
    public static function initDragDrop(ilGlobalTemplateInterface $a_main_tpl = null)
    {
        global $DIC;

        if ($a_main_tpl == null) {
            $tpl = $DIC["tpl"];
        } else {
            $tpl = $a_main_tpl;
        }

        $tpl->addJavaScript(self::YUI_BASE . "/yahoo-dom-event/yahoo-dom-event.js");
        $tpl->addJavaScript(self::YUI_BASE . "/dragdrop/dragdrop-min.js");
        $tpl->addJavaScript(self::YUI_BASE . "/element/element-min.js");
    }


    /**
     * Init YUI DomEvent
     *
     * @param ilGlobalTemplateInterface|null $a_main_tpl
     */
    public static function initDomEvent(ilGlobalTemplateInterface $a_main_tpl = null)
    {
        global $DIC;

        if ($a_main_tpl == null) {
            $tpl = $DIC["tpl"];
        } else {
            $tpl = $a_main_tpl;
        }

        $tpl->addJavaScript(self::YUI_BASE . "/yahoo-dom-event/yahoo-dom-event.js");
    }

    /**
     * Init yui panel
     *
     * @access public
     *
     * @param bool                           $a_resize
     * @param ilGlobalTemplateInterface|null $a_main_tpl
     *
     * @return void
     */
    public static function initPanel($a_resize = false, ilGlobalTemplateInterface $a_main_tpl = null)
    {
        global $DIC;

        if ($a_main_tpl == null) {
            $tpl = $DIC["tpl"];
        } else {
            $tpl = $a_main_tpl;
        }

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
     * Init YUI Connection module
     */
    public static function initConnectionWithAnimation()
    {
        global $DIC;

        $tpl = $DIC["tpl"];

        $tpl->addJavaScript(self::YUI_BASE . "/yahoo-dom-event/yahoo-dom-event.js");
        $tpl->addJavaScript(self::YUI_BASE . "/animation/animation-min.js");
        $tpl->addJavaScript(self::YUI_BASE . "/connection/connection-min.js");
    }



    /**
     * Init YUI Overlay module
     */
    public static function initOverlay(ilGlobalTemplateInterface $a_main_tpl = null)
    {
        global $DIC;

        if ($a_main_tpl == null) {
            $tpl = $DIC["tpl"];
        } else {
            $tpl = $a_main_tpl;
        }

        $tpl->addJavaScript(self::YUI_BASE . "/yahoo-dom-event/yahoo-dom-event.js");
        $tpl->addJavaScript(self::YUI_BASE . "/container/container_core-min.js");
        self::addContainerCss($tpl);
    }


    /**
     * init button control
     * In the moment used for calendar color picker button
     *
     * @access public
     * @return void
     * @static
     */
    public static function initButtonControl()
    {
        global $DIC;

        $tpl = $DIC["tpl"];

        $tpl->addJavaScript(self::YUI_BASE . "/yahoo-dom-event/yahoo-dom-event.js");
        $tpl->addJavaScript(self::YUI_BASE . "/element/element-min.js");

        $tpl->addJavaScript(self::YUI_BASE . "/container/container_core-min.js");
        $tpl->addJavaScript(self::YUI_BASE . "/menu/menu-min.js");

        $tpl->addJavaScript(self::YUI_BASE . "/button/button-min.js");

        $tpl->addCss(self::YUI_BASE . "/button/assets/skins/sam/button.css");
        $tpl->addCss(self::YUI_BASE . "/menu/assets/skins/sam/menu.css");
    }

    /**
     *
     */
    public static function initCookie()
    {
        /**
         * @var $tpl ilTemplate
         */
        global $DIC;

        $tpl = $DIC["tpl"];
        $tpl->addJavaScript(self::YUI_BASE . "/yahoo/yahoo-min.js", 1);
        $tpl->addJavaScript(self::YUI_BASE . "/cookie/cookie.js", 1);
    }


    /**
     * Get local path of a YUI js file
     */
    public static function getLocalPath($a_name = "")
    {
        return self::YUI_BASE . "/" . $a_name;
    }


    /**
     * Add container css
     */
    protected static function addContainerCss(ilGlobalTemplateInterface $a_main_tpl = null)
    {
        global $DIC;

        if ($a_main_tpl == null) {
            $tpl = $DIC["tpl"];
        } else {
            $tpl = $a_main_tpl;
        }

        $tpl->addCss(self::getLocalPath("container/assets/skins/sam/container.css"));
    }
}
