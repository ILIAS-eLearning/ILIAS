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
     */
    public static function initConnection(ilTemplate $a_main_tpl = null)
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
     */
    public static function initEvent()
    {
        global $DIC;

        $tpl = $DIC["tpl"];

        $tpl->addJavaScript(self::YUI_BASE . "/yahoo-dom-event/yahoo-dom-event.js");
    }


    /**
     * Init YUI Dom
     */
    public static function initDom()
    {
        global $DIC;

        $tpl = $DIC["tpl"];

        $tpl->addJavaScript(self::YUI_BASE . "/yahoo-dom-event/yahoo-dom-event.js");
    }


    /**
     * Init YUI Animation
     */
    public static function initAnimation()
    {
        global $DIC;

        $tpl = $DIC["tpl"];

        $tpl->addJavaScript(self::YUI_BASE . "/yahoo-dom-event/yahoo-dom-event.js");
        $tpl->addJavaScript(self::YUI_BASE . "/animation/animation-min.js");
    }


    /**
     * Init YUI Drag and Drop
     */
    public static function initDragDrop()
    {
        global $DIC;

        $tpl = $DIC["tpl"];

        $tpl->addJavaScript(self::YUI_BASE . "/yahoo-dom-event/yahoo-dom-event.js");
        $tpl->addJavaScript(self::YUI_BASE . "/dragdrop/dragdrop-min.js");
        $tpl->addJavaScript(self::YUI_BASE . "/element/element-min.js");
    }


    /**
     * Init YUI DomEvent
     */
    public static function initDomEvent()
    {
        global $DIC;

        $tpl = $DIC["tpl"];

        $tpl->addJavaScript(self::YUI_BASE . "/yahoo-dom-event/yahoo-dom-event.js");
    }


    /**
     * Init yui panel
     *
     * @access public
     *
     * @param void
     *
     * @return void
     */
    public static function initPanel($a_resize = false, ilTemplate $a_main_tpl = null)
    {
        global $DIC;

        if ($a_main_tpl != null) {
            $tpl = $a_main_tpl;
        } else {
            $tpl = $DIC["tpl"];
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
     * Init YUI Menu module
     */
    public static function initMenu()
    {
        global $DIC;

        $tpl = $DIC["tpl"];

        $tpl->addJavaScript(self::YUI_BASE . "/yahoo-dom-event/yahoo-dom-event.js");
        $tpl->addJavaScript(self::YUI_BASE . "/container/container_core.js");
        $tpl->addJavaScript(self::YUI_BASE . "/menu/menu-min.js");
        $tpl->addCss(self::YUI_BASE . "/menu/assets/menu.css");
    }


    /**
     * Init YUI Overlay module
     */
    public static function initOverlay(ilTemplate $a_main_tpl = null)
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
     * Init YUI Simple Dialog
     */
    public static function initSimpleDialog()
    {
        global $DIC;

        $tpl = $DIC["tpl"];

        $tpl->addJavaScript(self::YUI_BASE . "/yahoo-dom-event/yahoo-dom-event.js");
        $tpl->addJavaScript(self::YUI_BASE . "/container/container-min.js");
        $tpl->addJavaScript(self::YUI_BASE . "/dragdrop/dragdrop-min.js");
        self::addContainerCss();
        $tpl->addCss("./Services/YUI/templates/default/tpl.simpledialog.css");
    }


    /**
     * Init assessment wizard
     */
    public static function initAssessmentWizard()
    {
        global $DIC;

        $tpl = $DIC["tpl"];

        $tpl->addJavaScript(self::YUI_BASE . "/yahoo-dom-event/yahoo-dom-event.js");
        $tpl->addJavaScript(self::YUI_BASE . "/element/element-min.js");
        $tpl->addJavaScript(self::YUI_BASE . "/button/button-min.js");
        $tpl->addJavaScript(self::YUI_BASE . "/container/container-min.js");
        $tpl->addCss(self::YUI_BASE . "/button/assets/skins/sam/button.css");
        self::addContainerCss();
    }


    /**
     * init drag & drop list
     */
    public static function initDragDropList()
    {
        global $DIC;

        $tpl = $DIC["tpl"];

        $tpl->addJavaScript(self::YUI_BASE . "/yahoo-dom-event/yahoo-dom-event.js");
        $tpl->addJavaScript(self::YUI_BASE . "/animation/animation-min.js");
        $tpl->addJavaScript(self::YUI_BASE . "/dragdrop/dragdrop-min.js");
        $tpl->addCss("./Services/YUI/templates/default/DragDropList.css");
    }


    /**
     * init drag & drop and animation
     */
    public static function initDragDropAnimation()
    {
        global $DIC;

        $tpl = $DIC["tpl"];

        $tpl->addJavaScript(self::YUI_BASE . "/yahoo-dom-event/yahoo-dom-event.js");
        $tpl->addJavaScript(self::YUI_BASE . "/animation/animation-min.js");
        $tpl->addJavaScript(self::YUI_BASE . "/dragdrop/dragdrop-min.js");
    }


    /**
     * init element selection
     */
    public static function initElementSelection()
    {
        global $DIC;

        $tpl = $DIC["tpl"];

        $tpl->addJavaScript(self::YUI_BASE . "/yahoo-dom-event/yahoo-dom-event.js");
        $tpl->addJavaScript(self::YUI_BASE . "/element/element-min.js");
    }


    /**
     * get a drag & drop list
     */
    public static function getDragDropList($id_source, $title_source, $source, $id_dest, $title_dest, $dest)
    {
        self::initDragDropList();

        $template = new ilTemplate("tpl.dragdroplist.html", true, true, "Services/YUI");
        foreach ($source as $id => $name) {
            $template->setCurrentBlock("source_element");
            $template->setVariable("ELEMENT_ID", $id);
            $template->setVariable("ELEMENT_NAME", $name);
            $template->parseCurrentBlock();
            $template->setCurrentBlock("element");
            $template->setVariable("ELEMENT_ID", $id);
            $template->parseCurrentBlock();
        }
        foreach ($dest as $id => $name) {
            $template->setCurrentBlock("dest_element");
            $template->setVariable("ELEMENT_ID", $id);
            $template->setVariable("ELEMENT_NAME", $name);
            $template->parseCurrentBlock();
            $template->setCurrentBlock("element");
            $template->setVariable("ELEMENT_ID", $id);
            $template->parseCurrentBlock();
        }
        $template->setVariable("TITLE_LIST_1", $title_source);
        $template->setVariable("TITLE_LIST_2", $title_dest);
        $template->setVariable("LIST_1", $id_source);
        $template->setVariable("LIST_2", $id_dest);

        return $template->get();
    }


    public static function addYesNoDialog($dialogname, $headertext, $message, $yesaction, $noaction, $defaultyes, $icon = "help")
    {
        global $DIC;

        $tpl = $DIC["tpl"];
        $lng = $DIC->language();

        self::initSimpleDialog();

        $template = new ilTemplate("tpl.yes_no_dialog.js", true, true, "Services/YUI");
        $template->setVariable("DIALOGNAME", $dialogname);
        $template->setVariable("YES_ACTION", $yesaction);
        $template->setVariable("NO_ACTION", $noaction);
        $template->setVariable("DIALOG_HEADER", $headertext);
        $template->setVariable("DIALOG_MESSAGE", $message);
        $template->setVariable("TEXT_YES", $lng->txt("yes"));
        $template->setVariable("TEXT_NO", $lng->txt("no"));
        switch ($icon) {
            case "warn":
                $template->setVariable("ICON", "YAHOO.widget.SimpleDialog.ICON_WARN");
                break;
            case "tip":
                $template->setVariable("ICON", "YAHOO.widget.SimpleDialog.ICON_TIP");
                break;
            case "info":
                $template->setVariable("ICON", "YAHOO.widget.SimpleDialog.ICON_INFO");
                break;
            case "block":
                $template->setVariable("ICON", "YAHOO.widget.SimpleDialog.ICON_BLOCK");
                break;
            case "alarm":
                $template->setVariable("ICON", "YAHOO.widget.SimpleDialog.ICON_ALARM");
                break;
            case "help":
            default:
                $template->setVariable("ICON", "YAHOO.widget.SimpleDialog.ICON_HELP");
                break;
        }
        if ($defaultyes) {
            $template->touchBlock("isDefaultYes");
        } else {
            $template->touchBlock("isDefaultNo");
        }
        $tpl->setCurrentBlock("HeadContent");
        $tpl->setVariable("CONTENT_BLOCK", $template->get());
        $tpl->parseCurrentBlock();
    }


    /**
     * init calendar
     *
     * @access public
     * @return
     * @static
     */
    public static function initCalendar()
    {
        global $DIC;

        $tpl = $DIC["tpl"];

        $tpl->addJavaScript(self::YUI_BASE . "/yahoo-dom-event/yahoo-dom-event.js");
        $tpl->addJavaScript(self::YUI_BASE . "/calendar/calendar-min.js");

        $tpl->addCss(self::YUI_BASE . "/calendar/assets/skins/sam/calendar.css");
        $tpl->addCss("./Services/Calendar/css/calendar.css");
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
     * init color picker button
     *
     * @access public
     * @return void
     * @static
     */
    public static function initColorPicker()
    {
        global $DIC;

        $tpl = $DIC["tpl"];

        self::initButtonControl();

        $tpl->addJavaScript(self::YUI_BASE . "/dragdrop/dragdrop-min.js");
        $tpl->addJavaScript(self::YUI_BASE . "/slider/slider-min.js");
        $tpl->addJavaScript(self::YUI_BASE . "/colorpicker/colorpicker-min.js");

        $tpl->addCss('./Services/Form/css/color_picker.css');
        $tpl->addCss(self::YUI_BASE . "/colorpicker/assets/skins/sam/colorpicker.css");
    }


    /**
     * Init YUI TabView component
     */
    public static function initTabView()
    {
        global $DIC;

        $tpl = $DIC["tpl"];

        $tpl->addCss(self::YUI_BASE . "/tabview/assets/skins/sam/tabview.css");
        $tpl->addJavaScript(self::YUI_BASE . "/yahoo-dom-event/yahoo-dom-event.js");
        $tpl->addJavaScript(self::YUI_BASE . "/element/element-min.js");
        $tpl->addJavaScript(self::YUI_BASE . "/tabview/tabview-min.js");
    }


    /**
     * Init YUI JSON component
     *
     * @author jposselt@databay.de
     */
    public static function initJson()
    {
        global $DIC;

        $tpl = $DIC["tpl"];
        $tpl->addJavaScript(self::YUI_BASE . "/yahoo-dom-event/yahoo-dom-event.js");
        $tpl->addJavaScript(self::YUI_BASE . "/json/json-min.js");
    }


    /**
     * Init layout (alpha!)
     */
    public static function initLayout()
    {
        global $DIC;

        $tpl = $DIC["tpl"];

        $tpl->addCss(self::YUI_BASE . "/assets/skins/sam/resize.css");
        $tpl->addCss(self::YUI_BASE . "/assets/skins/sam/layout.css");

        $tpl->addJavaScript(self::YUI_BASE . "/yahoo-dom-event/yahoo-dom-event.js");
        $tpl->addJavaScript(self::YUI_BASE . "/dragdrop/dragdrop-min.js");
        $tpl->addJavaScript(self::YUI_BASE . "/element/element-min.js");
        $tpl->addJavaScript(self::YUI_BASE . "/animation/animation-min.js");
        $tpl->addJavaScript(self::YUI_BASE . "/resize/resize-min.js");
        $tpl->addJavaScript(self::YUI_BASE . "/layout/layout-min.js");
    }


    /**
     * Init treeView
     */
    public static function initTreeView()
    {
        global $DIC;

        $tpl = $DIC["tpl"];

        $tpl->addJavaScript(self::getLocalPath() . '/yahoo/yahoo-min.js');
        $tpl->addJavaScript(self::getLocalPath() . '/event/event-min.js');
        $tpl->addJavaScript(self::getLocalPath() . '/treeview/treeview.js');
    }


    /**
     * Init YUI Event
     */
    public static function initTooltip()
    {
        global $DIC;

        $tpl = $DIC["tpl"];

        self::addContainerCss();
        $tpl->addJavaScript(self::YUI_BASE . "/yahoo-dom-event/yahoo-dom-event.js");
        $tpl->addJavaScript(self::YUI_BASE . "/animation/animation-min.js");
        $tpl->addJavaScript(self::YUI_BASE . "/container/container-min.js");
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

        $tpl->addJavaScript(self::YUI_BASE . "/cookie/cookie.js");
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
    protected static function addContainerCss(ilTemplate $a_main_tpl = null)
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
