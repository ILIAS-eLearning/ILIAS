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
 * Accordion user interface class
 * @author Alexander Killing <killing@leifos.de>
 */
class ilAccordionGUI
{
    public const VERTICAL = "vertical";
    public const HORIZONTAL = "horizontal";
    public const FORCE_ALL_OPEN = "ForceAllOpen";
    public const FIRST_OPEN = "FirstOpen";
    public const ALL_CLOSED = "AllClosed";
    protected string $orientation;
    protected ilObjUser $user;
    protected array $items = array();
    protected array $force_open = array();
    protected static int $accordion_cnt = 0;
    protected bool $use_session_storage = false;
    protected bool $allow_multi_opened = false;
    protected string $show_all_element = "";
    protected string $hide_all_element = "";
    protected ?int $contentwidth = null;
    protected ?int $contentheight = null;
    protected string $headerclass = "";
    protected string $contentclass = "";
    protected string $icontainerclass = "";
    protected string $containerclass = "";
    protected string $id = "";
    protected bool $head_class_set = false;
    public static string $owl_path = "./libs/bower/bower_components/owl.carousel/dist";
    public static string $owl_js_path = "/owl.carousel.js";
    public static string $owl_css_path = "/assets/owl.carousel.css";
    protected ilGlobalTemplateInterface $main_tpl;
    protected string $active_headerclass = "";
    protected string $behaviour = self::FIRST_OPEN;

    public function __construct()
    {
        global $DIC;

        $this->main_tpl = $DIC->ui()->mainTemplate();

        $this->user = $DIC->user();
        $this->setOrientation(ilAccordionGUI::VERTICAL);
    }

    public function setId(string $a_val): void
    {
        $this->id = $a_val;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setOrientation(string $a_orientation): void
    {
        if (in_array(
            $a_orientation,
            array(ilAccordionGUI::VERTICAL, ilAccordionGUI::HORIZONTAL)
        )) {
            $this->orientation = $a_orientation;
        }
    }

    public function getOrientation(): string
    {
        return $this->orientation;
    }

    public function setContainerClass(string $a_containerclass): void
    {
        $this->containerclass = $a_containerclass;
    }

    public function getContainerClass(): string
    {
        return $this->containerclass;
    }

    public function setInnerContainerClass(string $a_containerclass): void
    {
        $this->icontainerclass = $a_containerclass;
    }

    public function getInnerContainerClass(): string
    {
        return $this->icontainerclass;
    }

    public function setHeaderClass(string $a_headerclass): void
    {
        $this->headerclass = $a_headerclass;
    }

    public function getHeaderClass(): string
    {
        return $this->headerclass;
    }

    public function setActiveHeaderClass(string $a_h_class): void
    {
        $this->active_headerclass = $a_h_class;
    }

    public function getActiveHeaderClass(): string
    {
        return $this->active_headerclass;
    }

    public function setContentClass(string $a_contentclass): void
    {
        $this->contentclass = $a_contentclass;
    }

    public function getContentClass(): string
    {
        return $this->contentclass;
    }

    public function setContentWidth(?int $a_contentwidth): void
    {
        $this->contentwidth = $a_contentwidth;
    }

    public function getContentWidth(): ?int
    {
        return $this->contentwidth;
    }

    public function setContentHeight(?int $a_contentheight): void
    {
        $this->contentheight = $a_contentheight;
    }

    public function getContentHeight(): ?int
    {
        return $this->contentheight;
    }

    /**
     * Set behaviour "ForceAllOpen" | "FirstOpen" | "AllClosed"
     */
    public function setBehaviour(string $a_val): void
    {
        $this->behaviour = $a_val;
    }

    public function getBehaviour(): string
    {
        return $this->behaviour;
    }

    public function setUseSessionStorage(bool $a_val): void
    {
        $this->use_session_storage = $a_val;
    }

    public function getUseSessionStorage(): bool
    {
        return $this->use_session_storage;
    }

    public function setAllowMultiOpened(bool $a_val): void
    {
        $this->allow_multi_opened = $a_val;
    }

    public function getAllowMultiOpened(): bool
    {
        return $this->allow_multi_opened;
    }

    /**
     * @param string $a_val ID of show all html element
     */
    public function setShowAllElement(string $a_val): void
    {
        $this->show_all_element = $a_val;
    }

    public function getShowAllElement(): string
    {
        return $this->show_all_element;
    }

    /**
     * @param string $a_val ID of hide all html element
     */
    public function setHideAllElement(string $a_val): void
    {
        $this->hide_all_element = $a_val;
    }

    public function getHideAllElement(): string
    {
        return $this->hide_all_element;
    }

    /**
    * Add javascript files that are necessary to run accordion
    */
    public static function addJavaScript(ilGlobalTemplate $main_tpl = null): void
    {
        global $DIC;

        if ($main_tpl != null) {
            $tpl = $main_tpl;
        } else {
            $tpl = $DIC["tpl"];
        }

        ilYuiUtil::initConnection($tpl);

        iljQueryUtil::initjQueryUI($tpl);

        foreach (self::getLocalJavascriptFiles() as $f) {
            $tpl->addJavaScript($f, true, 3);
        }
    }

    /**
    * Add required css
    */
    public static function addCss(): void
    {
        global $DIC;

        $tpl = $DIC["tpl"];

        foreach (self::getLocalCssFiles() as $f) {
            $tpl->addCss($f);
        }
    }

    public static function getLocalJavascriptFiles(): array
    {
        return array(
            "./Services/Accordion/js/accordion.js",
            self::$owl_path . self::$owl_js_path
        );
    }

    public static function getLocalCssFiles(): array
    {
        return array(
            "./Services/Accordion/css/accordion.css",
            self::$owl_path . self::$owl_css_path
        );
    }

    public function addItem(
        string $a_header,
        string $a_content,
        bool $a_force_open = false
    ): void {
        $this->items[] = array("header" => $a_header,
            "content" => $a_content, "force_open" => $a_force_open);

        if ($a_force_open) {
            $this->force_open[] = sizeof($this->items);
        }
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function getHTML(bool $async = false): string
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
            $stor = new ilAccordionPropertiesStorageGUI();

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
            $options["save_url"] = "./ilias.php?baseClass=ilaccordionpropertiesstoragegui&cmd=setOpenedTab" .
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
                $options["active_head_class"] = "il_VAccordionHeadActive";
            }
        }

        $options["height"] = null;
        $options["id"] = 'accordion_' . $this->getId() . '_' . self::$accordion_cnt;
        $options["multi"] = (bool) $this->getAllowMultiOpened();
        $options["show_all_element"] = $this->getShowAllElement();
        $options["hide_all_element"] = $this->getHideAllElement();

        $tpl->setVariable("ACC_ID", $options["id"]);

        $html = $tpl->get();
        $code = $this->getOnloadCode($options);
        if (!$async) {
            $this->main_tpl->addOnLoadCode($code);
        } else {
            $html .= "<script>$code</script>";
        }
        return $html;
    }

    protected function getOnloadCode(array $options): string
    {
        return 'il.Accordion.add(' . json_encode($options, JSON_THROW_ON_ERROR) . ');';
    }
}
