<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* Accordion user interface class
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id:$
*/
class ilAccordionGUI
{
    /**
     * @var ilObjUser
     */
    protected $user;

    protected $items = array();
    protected $force_open = array();
    protected static $accordion_cnt = 0;
    protected $use_session_storage = false;
    protected $allow_multi_opened = false;
    protected $show_all_element = null;
    protected $hide_all_element = null;
    
    const VERTICAL = "vertical";
    const HORIZONTAL = "horizontal";
    const FORCE_ALL_OPEN = "ForceAllOpen";
    const FIRST_OPEN = "FirstOpen";
    const ALL_CLOSED = "AllClosed";

    public static $owl_path = "./libs/bower/bower_components/owl.carousel/dist";
    public static $owl_js_path = "/owl.carousel.js";
    public static $owl_css_path = "/assets/owl.carousel.css";

    /**
    * Constructor
    */
    public function __construct()
    {
        global $DIC;

        $this->user = $DIC->user();
        $this->setOrientation(ilAccordionGUI::VERTICAL);
    }
    
    /**
    * Set id
    *
    * @param	string	 id
    */
    public function setId($a_val)
    {
        $this->id = $a_val;
    }
    
    /**
    * Get id
    *
    * @return	string	id
    */
    public function getId()
    {
        return $this->id;
    }
    
    /**
    * Set Orientation.
    *
    * @param	string	$a_orientation	Orientation
    */
    public function setOrientation($a_orientation)
    {
        if (in_array(
            $a_orientation,
            array(ilAccordionGUI::VERTICAL, ilAccordionGUI::HORIZONTAL)
        )) {
            $this->orientation = $a_orientation;
        }
    }

    /**
    * Get Orientation.
    *
    * @return	string	Orientation
    */
    public function getOrientation()
    {
        return $this->orientation;
    }

    /**
     * Set Container CSS Class.
     *
     * @param	string	$a_containerclass	Container CSS Class
     */
    public function setContainerClass($a_containerclass)
    {
        $this->containerclass = $a_containerclass;
    }

    /**
     * Get Container CSS Class.
     *
     * @return	string	Container CSS Class
     */
    public function getContainerClass()
    {
        return $this->containerclass;
    }

    /**
     * Set inner Container CSS Class.
     *
     * @param	string	$a_containerclass	Container CSS Class
     */
    public function setInnerContainerClass($a_containerclass)
    {
        $this->icontainerclass = $a_containerclass;
    }

    /**
     * Get inner Container CSS Class.
     *
     * @return	string	Container CSS Class
     */
    public function getInnerContainerClass()
    {
        return $this->icontainerclass;
    }

    /**
    * Set Header CSS Class.
    *
    * @param	string	$a_headerclass	Header CSS Class
    */
    public function setHeaderClass($a_headerclass)
    {
        $this->headerclass = $a_headerclass;
    }

    /**
    * Get Header CSS Class.
    *
    * @return	string	Header CSS Class
    */
    public function getHeaderClass()
    {
        return $this->headerclass;
    }

    /**
     * Set active header class
     *
     * @param	string	$a_h_class	Active Header CSS Class
     */
    public function setActiveHeaderClass($a_h_class)
    {
        $this->active_headerclass = $a_h_class;
    }

    /**
     * Get active Header CSS Class.
     *
     * @return	string	Active header CSS Class
     */
    public function getActiveHeaderClass()
    {
        return $this->active_headerclass;
    }

    /**
    * Set Content CSS Class.
    *
    * @param	string	$a_contentclass	Content CSS Class
    */
    public function setContentClass($a_contentclass)
    {
        $this->contentclass = $a_contentclass;
    }

    /**
    * Get Content CSS Class.
    *
    * @return	string	Content CSS Class
    */
    public function getContentClass()
    {
        return $this->contentclass;
    }

    /**
    * Set ContentWidth.
    *
    * @param	integer	$a_contentwidth	ContentWidth
    */
    public function setContentWidth($a_contentwidth)
    {
        $this->contentwidth = $a_contentwidth;
    }

    /**
    * Get ContentWidth.
    *
    * @return	integer	ContentWidth
    */
    public function getContentWidth()
    {
        return $this->contentwidth;
    }

    /**
    * Set ContentHeight.
    *
    * @param	integer	$a_contentheight	ContentHeight
    */
    public function setContentHeight($a_contentheight)
    {
        $this->contentheight = $a_contentheight;
    }

    /**
    * Get ContentHeight.
    *
    * @return	integer	ContentHeight
    */
    public function getContentHeight()
    {
        return $this->contentheight;
    }

    /**
     * Set behaviour "ForceAllOpen" | "FirstOpen" | "AllClosed"
     *
     * @param	string	behaviour
     */
    public function setBehaviour($a_val)
    {
        $this->behaviour = $a_val;
    }
    
    /**
     * Get behaviour
     *
     * @return
     */
    public function getBehaviour()
    {
        return $this->behaviour;
    }

    /**
     * Set use session storage
     *
     * @param bool $a_val use session storage
     */
    public function setUseSessionStorage($a_val)
    {
        $this->use_session_storage = $a_val;
    }

    /**
     * Get use session storage
     *
     * @return bool use session storage
     */
    public function getUseSessionStorage()
    {
        return $this->use_session_storage;
    }

    /**
     * Set allow multi opened
     *
     * @param bool $a_val allow multiple accordions being opened
     */
    public function setAllowMultiOpened($a_val)
    {
        $this->allow_multi_opened = $a_val;
    }
    
    /**
     * Get allow multi opened
     *
     * @return bool allow multiple accordions being opened
     */
    public function getAllowMultiOpened()
    {
        return $this->allow_multi_opened;
    }

    /**
     * Set show all element
     *
     * @param string $a_val ID of show all html element
     */
    public function setShowAllElement($a_val)
    {
        $this->show_all_element = $a_val;
    }

    /**
     * Get show all element
     *
     * @return string ID of show all html element
     */
    public function getShowAllElement()
    {
        return $this->show_all_element;
    }

    /**
     * Set hide all element
     *
     * @param string $a_val ID of hide all html element
     */
    public function setHideAllElement($a_val)
    {
        $this->hide_all_element = $a_val;
    }

    /**
     * Get hide all element
     *
     * @return string ID of hide all html element
     */
    public function getHideAllElement()
    {
        return $this->hide_all_element;
    }

    /**
    * Add javascript files that are necessary to run accordion
    */
    public static function addJavaScript(ilTemplate $main_tpl = null)
    {
        global $DIC;

        if ($main_tpl != null) {
            $tpl = $main_tpl;
        } else {
            $tpl = $DIC["tpl"];
        }

        include_once("./Services/YUI/classes/class.ilYuiUtil.php");
        ilYuiUtil::initConnection($tpl);

        include_once("./Services/jQuery/classes/class.iljQueryUtil.php");
        iljQueryUtil::initjQueryUI($tpl);

        foreach (self::getLocalJavascriptFiles() as $f) {
            $tpl->addJavaScript($f, true, 3);
        }
    }
    
    /**
    * Add required css
    */
    public static function addCss()
    {
        global $DIC;

        $tpl = $DIC["tpl"];

        foreach (self::getLocalCssFiles() as $f) {
            $tpl->addCss($f);
        }
    }

    /**
     * @return array
     */
    public static function getLocalJavascriptFiles()
    {
        return array(
            "./Services/Accordion/js/accordion.js",
            self::$owl_path . self::$owl_js_path
        );
    }

    /**
     * @return array
     */
    public static function getLocalCssFiles()
    {
        return array(
            "./Services/Accordion/css/accordion.css",
            self::$owl_path . self::$owl_css_path
        );
    }

    /**
    * Add item
    */
    public function addItem($a_header, $a_content, $a_force_open = false)
    {
        $this->items[] = array("header" => $a_header,
            "content" => $a_content, "force_open" => $a_force_open);
        
        if ($a_force_open) {
            $this->force_open[] = sizeof($this->items);
        }
    }
    
    /**
    * Get all items
    */
    public function getItems()
    {
        return $this->items;
    }
    
    /**
    * Get accordion html
    */
    public function getHTML()
    {
        $ilUser = $this->user;
        
        self::$accordion_cnt++;
        
        $or_short = ($this->getOrientation() == ilAccordionGUI::HORIZONTAL)
            ? "H"
            : "V";
            
        $width = (int) $this->getContentWidth();
        $height = (int) $this->getContentHeight();
        if ($this->getOrientation() == ilAccordionGUI::HORIZONTAL) {
            if ($width == 0) {
                $width = 200;
            }
            if ($height == 0) {
                $height = 100;
            }
        }
        
        $this->addJavascript();
        $this->addCss();
        
        $tpl = new ilTemplate("tpl.accordion.html", true, true, "Services/Accordion");
        foreach ($this->getItems() as $item) {
            $tpl->setCurrentBlock("item");
            $tpl->setVariable("HEADER", $item["header"]);
            $tpl->setVariable("CONTENT", $item["content"]);
            $tpl->setVariable("HEADER_CLASS", $this->getHeaderClass()
                ? $this->getHeaderClass() : "il_" . $or_short . "AccordionHead");
            $tpl->setVariable("CONTENT_CLASS", $this->getContentClass()
                ? $this->getContentClass() : "il_" . $or_short . "AccordionContent");

            if ($this->getBehaviour() != self::FORCE_ALL_OPEN) {
                $tpl->setVariable("HIDE_CONTENT_CLASS", "ilAccHideContent");
            }

            $tpl->setVariable("OR_SHORT", $or_short);
            
            $tpl->setVariable("INNER_CONTAINER_CLASS", $this->getInnerContainerClass()
                ? $this->getInnerContainerClass() : "il_" . $or_short . "AccordionInnerContainer");


            if ($height > 0) {
                $tpl->setVariable("HEIGHT", "height:" . $height . "px;");
            }
            if ($height > 0 && $this->getOrientation() == ilAccordionGUI::HORIZONTAL) {
                $tpl->setVariable("HHEIGHT", "height:" . $height . "px;");
            }
            $tpl->parseCurrentBlock();
        }

        $tpl->setVariable("CONTAINER_CLASS", $this->getContainerClass()
            ? $this->getContainerClass() : "il_" . $or_short . "AccordionContainer");

        $options["orientation"] = $this->getOrientation();
        $options["int_id"] = $this->getId();

        if ($this->getUseSessionStorage() && $this->getId() != "") {
            include_once("./Services/Accordion/classes/class.ilAccordionPropertiesStorage.php");
            $stor = new ilAccordionPropertiesStorage();
            
            $ctab = $stor->getProperty(
                $this->getId(),
                $ilUser->getId(),
                "opened"
            );
            $ctab_arr = explode(";", $ctab);

            foreach ($this->force_open as $fo) {
                if (!in_array($fo, $ctab_arr)) {
                    $ctab_arr[] = $fo;
                }
            }
            $ctab = implode(";", $ctab_arr);

            if ($ctab == "0") {
                $ctab = "";
            }

            $options["initial_opened"] = $ctab;
            $options["save_url"] = "./ilias.php?baseClass=ilaccordionpropertiesstorage&cmd=setOpenedTab" .
                "&accordion_id=" . $this->getId() . "&user_id=" . $ilUser->getId();
        }

        $options["behaviour"] = $this->getBehaviour();
        if ($this->getOrientation() == ilAccordionGUI::HORIZONTAL) {
            $options["toggle_class"] = 'il_HAccordionToggleDef';
            $options["toggle_act_class"] = 'il_HAccordionToggleActiveDef';
            $options["content_class"] = 'il_HAccordionContentDef';
        } else {
            $options["toggle_class"] = 'il_VAccordionToggleDef';
            $options["toggle_act_class"] = 'il_VAccordionToggleActiveDef';
            $options["content_class"] = 'il_VAccordionContentDef';
        }


        if ($width > 0) {
            $options["width"] = $width;
        } else {
            $options["width"] = null;
        }
        if ($width > 0 && $this->getOrientation() == ilAccordionGUI::VERTICAL) {
            $tpl->setVariable("CWIDTH", 'style="width:' . $width . 'px;"');
        }

        if ($this->head_class_set) {
            $options["active_head_class"] = $this->getActiveHeaderClass();
        } else {
            if ($this->getOrientation() == ilAccordionGUI::VERTICAL) {
                $options["active_head_class"] = "il_HAccordionHeadActive";
            } else {
                $options["active_head_class"] =  "il_VAccordionHeadActive";
            }
        }

        $options["height"] = null;
        $options["id"] = 'accordion_' . $this->getId() . '_' . self::$accordion_cnt;
        $options["multi"] = (bool) $this->getAllowMultiOpened();
        $options["show_all_element"] = $this->getShowAllElement();
        $options["hide_all_element"] = $this->getHideAllElement();

        include_once("./Services/JSON/classes/class.ilJsonUtil.php");
        $tpl->setVariable("OPTIONS", $str = ilJsonUtil::encode($options));
        $tpl->setVariable("ACC_ID", $options["id"]);
        //echo "<br><br><br><br><br><br>".$str;
        return $tpl->get();
    }
}
