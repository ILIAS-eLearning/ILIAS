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
 * User interface class for advanced drop-down selection lists
 *
 * @author Alexander Killing <killing@leifos.de>
 * @deprecated 10 Use KS Dropdowns instead
 */
class ilAdvancedSelectionListGUI implements ilToolbarItem
{
    private array $items = array();
    private string $id = "asl";
    private bool $asynch = false;

    public const DOWN_ARROW_DARK = "down_arrow_dark";
    public const ICON_ARROW = "caret";
    public const ICON_CONFIG = "glyphicon glyphicon-cog";
    public const NO_ICON = "";

    public const MODE_LINKS = "links";
    public const MODE_FORM_SELECT = "select";

    public const ON_ITEM_CLICK_HREF = "href";
    public const ON_ITEM_CLICK_FORM_SUBMIT = "submit";
    public const ON_ITEM_CLICK_FORM_SELECT = "select";
    public const ON_ITEM_CLICK_NOP = "nop";

    public const STYLE_DEFAULT = 0;
    public const STYLE_LINK = 1;
    public const STYLE_EMPH = 2;
    public const STYLE_LINK_BUTTON = 3;

    protected string $css_row = "";
    protected bool $access_key = false;
    protected ?array $toggle = null;
    protected bool $asynch_url = false;
    protected string $selected_value = "";
    protected string $trigger_event = "click";
    protected bool $auto_hide = false;
    protected ?ilGroupedListGUI $grouped_list = null;
    protected int $style = 0;
    private bool $dd_pullright = true;

    protected string $listtitle = "";
    protected string $aria_listtitle = "";
    protected bool $useimages = false;
    protected string $itemlinkclass = '';
    protected string $mode = "";
    protected array $links_mode = [];
    protected string $selectionheaderclass = "";
    protected string $headericon = "";
    protected string $nojslinkclass = "";
    protected string $on_click = "";

    /** @var array<string, mixed>  */
    protected array $form_mode = [
        "select_name" => '',
        "select_class" => '',
        "include_form_tag" => false,
        "form_action" => '',
        "form_id" => '',
        "form_class" => '',
        "form_target" => '',
        "button_text" => '',
        "button_class" => '',
        "button_cmd" => ''
    ];

    protected string $select_callback = '';
    protected string $sel_head_span_class = '';
    private \ILIAS\UI\Renderer $renderer;
    protected ilLanguage $lng;
    protected string $on_click_form_id;
    protected ilGlobalTemplateInterface $global_tpl;

    /*

    The modes implement the following html for non-js fallback:

    MODE_LINKS:

    <a href="...">...</a> <a href="...">...<a>

    MODE_FORM_SELECT: (form and submit tags are optional)

    <form id="..." class="..." method="post" action="..." target="_top">
    <select name="..."  class="..." size="0">
    <option value="...">...</option>
    ...
    </select>
    <input class="ilEditSubmit" type="submit" value="Go"/>
    </form>

    */

    public function __construct()
    {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        $this->renderer = $DIC->ui()->renderer();
        $this->lng = $DIC->language();
        $this->mode = self::MODE_LINKS;
        $this->setHeaderIcon(self::DOWN_ARROW_DARK);
        $this->setOnClickMode(self::ON_ITEM_CLICK_HREF);
        $this->global_tpl = $DIC['tpl'];
    }

    /**
     * Set links mode (for no js fallback)
     */
    public function setLinksMode(
        string $a_link_class = ""
    ): void {
        $this->mode = self::MODE_LINKS;
        $this->links_mode = array(
            "link_class" => $a_link_class);
    }

    /**
     * Set form mode (for no js fallback)
     * Outputs form selection including surrounding form
     */
    public function setFormSelectMode(
        string $a_select_name,
        string $a_select_class = "",
        bool $a_include_form_tag = false,
        string $a_form_action = "",
        string $a_form_id = "",
        string $a_form_class = "",
        string $a_form_target = "_top",
        string $a_button_text = "",
        string $a_button_class = "",
        string $a_button_cmd = ""
    ): void {
        $this->mode = self::MODE_FORM_SELECT;
        $this->form_mode = array(
            "select_name" => $a_select_name,
            "select_class" => $a_select_class,
            "include_form_tag" => $a_include_form_tag,
            "form_action" => $a_form_action,
            "form_id" => $a_form_id,
            "form_class" => $a_form_class,
            "form_target" => $a_form_target,
            "button_text" => $a_button_text,
            "button_class" => $a_button_class,
            "button_cmd" => $a_button_cmd
            );
    }

    public function addItem(
        string $a_title,
        string $a_value = "",
        string $a_link = "",
        string $a_img = "",
        string $a_alt = "",
        string $a_frame = "",
        string $a_html = "",
        bool $a_prevent_background_click = false,
        string $a_onclick = "",
        string $a_ttip = "",
        string $a_tt_my = "right center",
        string $a_tt_at = "left center",
        bool $a_tt_use_htmlspecialchars = true,
        array $a_data = array()
    ): void {
        $this->items[] = array("title" => $a_title, "value" => $a_value,
            "link" => $a_link, "img" => $a_img, "alt" => $a_alt, "frame" => $a_frame,
            "html" => $a_html, "prevent_background_click" => $a_prevent_background_click,
            "onclick" => $a_onclick, "ttip" => $a_ttip, "tt_my" => $a_tt_my, "tt_at" => $a_tt_at,
            "tt_use_htmlspecialchars" => $a_tt_use_htmlspecialchars, "data" => $a_data);
    }

    public function addComponent(\ILIAS\UI\Component\Component $component): void
    {
        $this->items[] = [
            'component' => $component,
        ];
    }

    public function setGroupedList(ilGroupedListGUI $a_val): void
    {
        $this->grouped_list = $a_val;
    }

    public function getGroupedList(): ?ilGroupedListGUI
    {
        return $this->grouped_list;
    }

    public function flush(): void
    {
        $this->items = array();
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function setListTitle(string $a_listtitle): void
    {
        $this->listtitle = $a_listtitle;
    }

    public function getListTitle(): string
    {
        return $this->listtitle;
    }

    public function setAriaListTitle(string $a_listtitle): void
    {
        $this->aria_listtitle = $a_listtitle;
    }

    public function getAriaListTitle(): string
    {
        return strip_tags($this->aria_listtitle);
    }

    /**
     * DEPRECATED use set style instead
     * @deprecated
     */
    public function setSelectionHeaderClass(string $a_selectionheaderclass): void
    {
        $this->selectionheaderclass = $a_selectionheaderclass;
    }

    public function getSelectionHeaderClass(): string
    {
        return $this->selectionheaderclass;
    }

    /**
     * @param int $a_val button style STYLE_DEFAULT, STYLE_LINK, STYLE_EMPH
     */
    public function setStyle(int $a_val): void
    {
        $this->style = $a_val;
    }

    /**
     * @return int button style STYLE_DEFAULT, STYLE_LINK, STYLE_EMPH
     */
    public function getStyle(): int
    {
        return $this->style;
    }

    public function setSelectionHeaderSpanClass(string $a_val): void
    {
        $this->sel_head_span_class = $a_val;
    }

    public function getSelectionHeaderSpanClass(): string
    {
        return $this->sel_head_span_class;
    }

    public function setHeaderIcon(string $a_headericon): void
    {
        $this->headericon = $a_headericon;
    }

    public function getHeaderIcon(): string
    {
        return $this->headericon;
    }

    public function setNoJSLinkClass(string $a_nojslinkclass): void
    {
        $this->nojslinkclass = $a_nojslinkclass;
    }

    public function getNoJSLinkClass(): string
    {
        return $this->nojslinkclass;
    }

    public function setItemLinkClass(string $a_itemlinkclass): void
    {
        $this->itemlinkclass = $a_itemlinkclass;
    }

    public function getItemLinkClass(): string
    {
        return $this->itemlinkclass;
    }

    public function setId(string $a_id): void
    {
        $this->id = $a_id;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setUseImages(bool $a_useimages): void
    {
        $this->useimages = $a_useimages;
    }

    public function getUseImages(): bool
    {
        return $this->useimages;
    }

    public function setTriggerEvent(string $a_val): void
    {
        $this->trigger_event = $a_val;
    }

    public function getTriggerEvent(): string
    {
        return $this->trigger_event;
    }

    public function setAutoHide(bool $a_val): void
    {
        $this->auto_hide = $a_val;
    }

    public function getAutoHide(): bool
    {
        return $this->auto_hide;
    }

    /**
     * Set "onClick"- Mode
     *
     * Valid values are:
     * ilAdvancedSelectionList::ON_ITEM_CLICK_HREF or
     * ilAdvancedSelectionList::ON_ITEM_CLICK_FORM_SUBMIT
     * ilAdvancedSelectionList::ON_ITEM_CLICK_FORM_SELECT
     */
    public function setOnClickMode(
        string $a_val,
        string $a_onclick_form_id = ""
    ): void {
        $this->on_click = $a_val;
        $this->on_click_form_id = $a_onclick_form_id;
    }

    public function getOnClickMode(): string
    {
        return $this->on_click;
    }

    public function setSelectedValue(string $a_val): void
    {
        $this->selected_value = $a_val;
    }

    public function getSelectedValue(): string
    {
        return $this->selected_value;
    }

    /**
     * Set additional toggle element
     * @param string $a_el element id
     * @param string $a_on class for "on"
     */
    public function setAdditionalToggleElement(string $a_el, string $a_on): void
    {
        $this->toggle = array("el" => $a_el, "class_on" => $a_on);
    }

    /**
     * Get additional toggle element
     */
    public function getAdditionalToggleElement(): ?array
    {
        return $this->toggle;
    }

    public function setAsynch(bool $a_val): void
    {
        if ($a_val) {
            ilYuiUtil::initConnection();
        }
        $this->asynch = $a_val;
    }

    public function getAsynch(): bool
    {
        return $this->asynch;
    }

    public function setAsynchUrl(string $a_val): void
    {
        $this->asynch_url = $a_val;
    }

    public function getAsynchUrl(): string
    {
        return $this->asynch_url;
    }

    public function setSelectCallback(string $a_val): void
    {
        $this->select_callback = $a_val;
    }

    public function getSelectCallback(): string
    {
        return $this->select_callback;
    }

    public function setPullRight(bool $a_val): void
    {
        $this->dd_pullright = $a_val;
    }

    public function getPullRight(): bool
    {
        return $this->dd_pullright;
    }

    public function getToolbarHTML(): string
    {
        return $this->getHTML();
    }

    public function getHTML(bool $a_only_cmd_list_asynch = false): string
    {
        $items = $this->getItems();

        // do not show list, if no item is in list
        if (count($items) === 0 && !$this->getAsynch() && $this->getGroupedList() === null) {
            return "";
        }

        $this->global_tpl->addJavaScript("./Services/UIComponent/AdvancedSelectionList/js/AdvancedSelectionList.js");

        $js_tpl = new ilTemplate(
            "tpl.adv_selection_list_js_init.js",
            true,
            true,
            "Services/UIComponent/AdvancedSelectionList",
            "DEFAULT",
            false,
            true
        );

        $tpl = new ilTemplate(
            "tpl.adv_selection_list.html",
            true,
            true,
            "Services/UIComponent/AdvancedSelectionList",
            "DEFAULT",
            false,
            true
        );

        reset($items);

        $cnt = 0;

        if ($this->getAsynch()) {
            $tpl->setCurrentBlock("asynch_request");
            $tpl->setVariable("IMG_LOADER", ilUtil::getImagePath("loader.svg"));
            $tpl->parseCurrentBlock();
        } elseif ($this->getGroupedList() !== null) {
            $tpl->setVariable("GROUPED_LIST_HTML", $this->getGroupedList()->getHTML());
        } else {
            foreach ($items as $item) {
                $this->css_row = ($this->css_row !== "tblrow1_mo")
                    ? "tblrow1_mo"
                    : "tblrow2_mo";

                if (isset($item['component'])) {
                    $tpl->setCurrentBlock('component');
                    $tpl->setVariable('COMPONENT', $this->renderer->render([$item['component']]));
                    $tpl->parseCurrentBlock();

                    $tpl->setCurrentBlock('item_loop');
                    $tpl->parseCurrentBlock();
                    continue;
                }

                if ($this->getUseImages()) {
                    if ($item["img"]) {
                        $tpl->setCurrentBlock("image");
                        $tpl->setVariable("IMG_ITEM", $item["img"]);
                        $tpl->setVariable("ALT_ITEM", $item["alt"]);
                        $tpl->parseCurrentBlock();
                    } else {
                        $tpl->touchBlock("no_image");
                    }
                }

                if ($this->getOnClickMode() === self::ON_ITEM_CLICK_HREF || $this->getItemLinkClass() !== "") {
                    if ($item["frame"]) {
                        $tpl->setCurrentBlock("frame");
                        $tpl->setVariable("TARGET_ITEM", $item["frame"]);
                        $tpl->parseCurrentBlock();
                    }

                    if ($this->getItemLinkClass() !== "") {
                        $tpl->setCurrentBlock("item_link_class");
                        $tpl->setVariable("ITEM_LINK_CLASS", $this->getItemLinkClass());
                        $tpl->parseCurrentBlock();
                    }

                    if (is_array($item["data"])) {
                        foreach ($item["data"] as $k => $v) {
                            $tpl->setCurrentBlock("f_data");
                            $tpl->setVariable("DATA_KEY", $k);
                            $tpl->setVariable("DATA_VAL", ilLegacyFormElementsUtil::prepareFormOutput($v));
                            $tpl->parseCurrentBlock();
                        }
                    }
                    if ($item["value"] != "") {
                        $tpl->setCurrentBlock("item_id");
                        $tpl->setVariable("ID_ITEM", $this->getId() . "_" . $item["value"]);
                        $tpl->parseCurrentBlock();
                    }

                    $tpl->setCurrentBlock("href_s");
                    $tpl->setVariable("HREF_ITEM", 'href="' . $item["link"] . '"');
                    $tpl->parseCurrentBlock();

                    $tpl->touchBlock("href_e");
                }

                $tpl->setCurrentBlock("item");
                if ($this->getOnClickMode() === self::ON_ITEM_CLICK_HREF) {
                    if ($item["prevent_background_click"]) {
                        $tpl->setVariable("ONCLICK_ITEM", '');
                    } elseif ($item["onclick"] == "" && $item["frame"] != "") {       // see #28730
                        $tpl->setVariable(
                            "ONCLICK_ITEM",
                            'onclick="' . "return il.AdvancedSelectionList.openTarget('" . $item["link"] . "','" . $item["frame"] . "');" . '"'
                        );
                    } elseif ($item["onclick"] != "") {
                        $tpl->setVariable(
                            "ONCLICK_ITEM",
                            'onclick="' . "return " . $item["onclick"] . ";" . '"'
                        );
                    }
                } elseif ($this->getOnClickMode() === self::ON_ITEM_CLICK_FORM_SUBMIT) {
                    $tpl->setVariable(
                        "ONCLICK_ITEM",
                        'onclick="return il.AdvancedSelectionList.submitForm(\'' . $this->getId() . '\'' .
                            ", '" . $this->form_mode["select_name"] . "','" . $item["value"] . "'," .
                            "'" . $this->on_click_form_id . "','" . $this->form_mode["button_cmd"] . "');\""
                    );
                } elseif ($this->getOnClickMode() === self::ON_ITEM_CLICK_FORM_SELECT) {
                    $tpl->setVariable(
                        "ONCLICK_ITEM",
                        'onclick="return il.AdvancedSelectionList.selectForm(\'' . $this->getId() . '\'' .
                            ", '" . $this->form_mode["select_name"] . "','" . $item["value"] . "'," .
                            "'" . $item["title"] . "');\""
                    );
                } elseif ($this->getOnClickMode() === self::ON_ITEM_CLICK_NOP) {
                    $tpl->setVariable(
                        "ONCLICK_ITEM",
                        'onclick="il.AdvancedSelectionList.clickNop(\'' . $this->getId() . '\'' .
                            ", '" . $this->form_mode["select_name"] . "','" . $item["value"] . "'," .
                            "'" . $item["title"] . "');\""
                    );
                }

                $tpl->setVariable("CSS_ROW", $this->css_row);
                if ($item["html"] == "") {
                    $tpl->setVariable("TXT_ITEM", $item["title"]);
                } else {
                    $tpl->setVariable("TXT_ITEM", $item["html"]);
                }

                $tpl->setVariable("ID_ITEM_TR", $this->getId() . "_" . $item["value"] . "_tr");
                if ($item["ttip"] != "") {
                    ilTooltipGUI::addTooltip(
                        $this->getId() . "_" . $item["value"] . "_tr",
                        $item["ttip"],
                        "",
                        $item["tt_my"],
                        $item["tt_at"],
                        $item["tt_use_htmlspecialchars"]
                    );
                }

                $tpl->parseCurrentBlock();

                $tpl->setCurrentBlock('item_loop');
                $tpl->parseCurrentBlock();

                // add item to js object
                $js_tpl->setCurrentBlock("js_item");
                $js_tpl->setVariable("IT_ID", $this->getId());
                $js_tpl->setVariable("IT_HID_NAME", $this->form_mode["select_name"]);

                $js_tpl->setVariable("IT_HID_VAL", $item["value"]);
                $js_tpl->setVariable("IT_TITLE", str_replace("'", "\\'", $item["title"]));
                $js_tpl->parseCurrentBlock();
            }

            // output hidden input, if click mode is form submission
            if ($this->getOnClickMode() === self::ON_ITEM_CLICK_FORM_SUBMIT) {
                $tpl->setCurrentBlock("hidden_input");
                $tpl->setVariable("HID", $this->getId());
                $tpl->parseCurrentBlock();

                $js_tpl->setCurrentBlock("hidden_input");
                $js_tpl->setVariable("HID", $this->getId());
                $js_tpl->parseCurrentBlock();
            }

            // output hidden input and initialize
            if ($this->getOnClickMode() === self::ON_ITEM_CLICK_FORM_SELECT) {
                $tpl->setCurrentBlock("hidden_input");
                $tpl->setVariable("HID", $this->getId());
                $tpl->parseCurrentBlock();

                // init hidden input with selected value
                $js_tpl->setCurrentBlock("init_hidden_input");
                $js_tpl->setVariable("H2ID", $this->getId());
                $js_tpl->setVariable("HID_NAME", $this->form_mode["select_name"]);
                $js_tpl->setVariable("HID_VALUE", $this->getSelectedValue());
                $js_tpl->parseCurrentBlock();
            }
        }

        if ($a_only_cmd_list_asynch) {
            $tpl->touchBlock("cmd_table");
            return $tpl->get("cmd_table");
        }

        if ($this->getGroupedList() === null) {
            $tpl->setCurrentBlock("dd_content");
            if ($this->getPullRight()) {
                $tpl->setVariable("UL_CLASS", "dropdown-menu pull-right");
            } else {
                $tpl->setVariable("UL_CLASS", "dropdown-menu");
            }
            $tpl->setVariable("TABLE_ID", $this->getId());
            $tpl->parseCurrentBlock();
        }

        if ($this->getHeaderIcon() !== self::NO_ICON) {
            $tpl->setCurrentBlock("top_img");
            switch ($this->getHeaderIcon()) {
                case self::ICON_CONFIG:
                    $tpl->setVariable("IMG_SPAN_STYLE", self::ICON_CONFIG);
                    break;

                case self::DOWN_ARROW_DARK:
                default:
                    $tpl->setVariable("IMG_SPAN_STYLE", self::ICON_ARROW);
                    break;
            }
            $tpl->parseCurrentBlock();
        }


        if ($this->getAsynch()) {
            $tpl->setCurrentBlock("asynch_bl");
            $tpl->setVariable("ASYNCH_URL", $this->getAsynchUrl());
            $tpl->setVariable("ASYNCH_ID", $this->getId());
            $tpl->setVariable("ASYNCH_TRIGGER_ID", $this->getId());
            $tpl->parseCurrentBlock();
        }

        // js section
        $tpl->setCurrentBlock("js_section");

        $cfg["trigger_event"] = $this->getTriggerEvent();
        $cfg["auto_hide"] = $this->getAutoHide();

        if ($this->getSelectCallback() !== "") {
            $cfg["select_callback"] = $this->getSelectCallback();
        }
        $cfg["anchor_id"] = "ilAdvSelListAnchorElement_" . $this->getId();
        $cfg["asynch"] = $this->getAsynch();
        $cfg["asynch_url"] = $this->getAsynchUrl();
        $toggle = $this->getAdditionalToggleElement();
        if (is_array($toggle)) {
            $cfg["toggle_el"] = $toggle["el"];
            $cfg["toggle_class_on"] = $toggle["class_on"];
        }
        //echo "<br>".htmlentities($this->getAsynchUrl());
        $tpl->setVariable("CFG", json_encode($cfg, JSON_THROW_ON_ERROR));

        //echo htmlentities(json_encode($cfg, JSON_THROW_ON_ERROR));

        $tpl->setVariable("TXT_SEL_TOP", $this->getListTitle());
        if ($this->getListTitle() === "" || $this->getAriaListTitle() !== "") {
            $aria_title = ($this->getAriaListTitle() !== "")
                ? $this->getAriaListTitle()
                : $this->lng->txt("actions");
            $tpl->setVariable("TXT_ARIA_TOP", $aria_title);
        }
        $tpl->setVariable("ID", $this->getId());

        $js_tpl->setVariable("ID", $this->getId());

        //$tpl->setVariable("CLASS_SEL_TOP", $this->getSelectionHeaderClass());
        switch ($this->getStyle()) {
            case self::STYLE_DEFAULT:
                $tpl->setVariable("BTN_CLASS", "btn btn-default");
                $tpl->setVariable("TAG", "button");
                break;

            case self::STYLE_EMPH:
                $tpl->setVariable("BTN_CLASS", "btn btn-primary");
                $tpl->setVariable("TAG", "button");
                break;

            case self::STYLE_LINK_BUTTON:
                $tpl->setVariable("BTN_CLASS", "btn btn-link");
                $tpl->setVariable("TAG", "button");
                break;

            case self::STYLE_LINK:
                $tpl->setVariable("BTN_CLASS", "");
                $tpl->setVariable("TAG", "a");
                $tpl->touchBlock("href_link");
                break;
        }


        if ($this->getSelectionHeaderSpanClass() !== "") {
            $tpl->setVariable(
                "CLASS_SEL_TOP_SPAN",
                $this->getSelectionHeaderSpanClass()
            );
        }

        // set the async url to an extra template variable
        // (needed for a mobile skin)
        // $tpl->setVariable("ASYNC_URL", $this->getAsynchUrl());

        $tpl->parseCurrentBlock();

        $this->global_tpl->addOnLoadCode(
            $js_tpl->get()
        );

        return $tpl->get();
    }
}
