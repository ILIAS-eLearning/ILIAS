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
 * Toolbar. The toolbar currently only supports a list of buttons as links.
 * A default toolbar object is available in the $ilToolbar global object.
 * @author Alexander Killing <killing@leifos.de>
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class ilToolbarGUI
{
    protected ilLanguage $lng;
    protected static int $instances = 0;
    protected string $id = '';
    protected string $form_action = '';
    protected bool $hidden = false;
    public array $items = array();
    protected array $lead_img = array(
        'img' => '',
        'alt' => '',
    );
    protected bool $open_form_tag = true;
    protected bool $close_form_tag = true;
    protected string $form_target = "";
    protected string $form_name = "";
    protected bool $prevent_double_submission = false;
    protected array $sticky_items = array();
    protected bool $has_separator = false;
    protected \ILIAS\DI\UIServices $ui;
    protected bool $multipart = false;

    public function __construct()
    {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        $this->lng = $DIC->language();
        $this->ui = $DIC->ui();

        self::$instances++;
    }

    /**
     * Set form action (if form action is set, toolbar is wrapped into form tags)
     */
    public function setFormAction(
        string $a_val,
        bool $a_multipart = false,
        string $a_target = ""
    ): void {
        $this->form_action = $a_val;
        $this->multipart = $a_multipart;
        $this->form_target = $a_target;
    }

    public function getFormAction(): string
    {
        return $this->form_action;
    }

    public function setLeadingImage(
        string $a_img,
        string $a_alt
    ): void {
        $this->lead_img = array("img" => $a_img, "alt" => $a_alt);
    }

    public function setHidden(bool $a_val): void
    {
        $this->hidden = $a_val;
    }

    public function getHidden(): bool
    {
        return $this->hidden;
    }

    public function setId(string $a_val): void
    {
        $this->id = $a_val;
    }

    public function getId(): string
    {
        return $this->id ?: self::$instances;
    }

    public function setPreventDoubleSubmission(bool $a_val): void
    {
        $this->prevent_double_submission = $a_val;
    }

    public function getPreventDoubleSubmission(): bool
    {
        return $this->prevent_double_submission;
    }

    /**
     * @deprecated use addButtonInstance() instead!
     */
    public function addButton(
        string $a_txt,
        string $a_cmd,
        string $a_target = "",
        ?int $a_acc_key = null,
        string $a_additional_attrs = '',
        string $a_id = "",
        string $a_class = 'submit'
    ): void {
        $this->items[] = array("type" => "button", "txt" => $a_txt, "cmd" => $a_cmd,
            "target" => $a_target, "acc_key" => $a_acc_key, 'add_attrs' => $a_additional_attrs,
            "id" => $a_id, "class" => $a_class);
    }

    /**
     * @deprecated use addButtonInstance() instead!
     */
    public function addFormButton(
        string $a_txt,
        string $a_cmd,
        ?int $a_acc_key = null,
        bool $a_primary = false,
        ?string $a_class = null
    ): void {
        if ($a_primary) {
            $button = ilSubmitButton::getInstance();
            $button->setPrimary(true);
            $button->setCaption($a_txt, false);
            $button->setCommand($a_cmd);
            $this->addStickyItem($button);
        } else {
            $this->items[] = array("type" => "fbutton", "txt" => $a_txt, "cmd" => $a_cmd,
                "acc_key" => $a_acc_key, "primary" => $a_primary, "class" => $a_class);
        }
    }

    public function addInputItem(
        ilToolbarItem $a_item,
        bool $a_output_label = false
    ): void {
        $this->items[] = array("type" => "input", "input" => $a_item, "label" => $a_output_label);
    }


    /**
     * Add a sticky item. Sticky items are always visible, also if the toolbar is collapsed (responsive view).
     * Sticky items are displayed first in the toolbar.
     * @param ilToolbarItem|\ILIAS\UI\Component\Component $a_item
     */
    public function addStickyItem(
        $a_item,
        bool $a_output_label = false
    ): void {
        $this->sticky_items[] = array("item" => $a_item, "label" => $a_output_label);
    }

    /**
     * Add button instance
     * @param ilButtonBase $a_button
     */
    public function addButtonInstance(ilButtonBase $a_button): void
    {
        if ($a_button->isPrimary()) {
            $this->addStickyItem($a_button);
        } else {
            $this->items[] = array("type" => "button_obj", "instance" => $a_button);
        }
    }

    public function addDropDown(
        string $a_txt,
        string $a_dd_html
    ): void {
        $this->items[] = array("type" => "dropdown", "txt" => $a_txt, "dd_html" => $a_dd_html);
    }

    public function addAdvancedSelectionList(ilAdvancedSelectionListGUI $adv): void
    {
        $this->items[] = array("type" => "adv_sel_list", "list" => $adv);
    }

    public function addSeparator(): void
    {
        $this->items[] = array("type" => "separator");
        $this->has_separator = true;
    }

    public function addText(string $a_text): void
    {
        $this->items[] = array("type" => "text", "text" => $a_text);
    }

    public function addSpacer(string $a_width = null): void
    {
        $this->items[] = array("type" => "spacer", "width" => $a_width);
    }

    public function addComponent(\ILIAS\UI\Component\Component $a_comp): void
    {
        $this->items[] = array("type" => "component", "component" => $a_comp);
    }

    public function addLink(
        string $a_caption,
        string $a_url,
        bool $a_disabled = false
    ): void {
        $this->items[] = array("type" => "link", "txt" => $a_caption, "cmd" => $a_url, "disabled" => $a_disabled);
    }

    public function setOpenFormTag(
        bool $a_val
    ): void {
        $this->open_form_tag = $a_val;
    }

    public function getOpenFormTag(): bool
    {
        return $this->open_form_tag;
    }

    public function setCloseFormTag(bool $a_val): void
    {
        $this->close_form_tag = $a_val;
    }

    public function getCloseFormTag(): bool
    {
        return $this->close_form_tag;
    }

    public function setFormName(string $a_val): void
    {
        $this->form_name = $a_val;
    }

    public function getFormName(): string
    {
        return $this->form_name;
    }


    /**
     * Get all groups (items separated by a separator)
     */
    public function getGroupedItems(): array
    {
        $groups = array();
        $group = array();
        foreach ($this->items as $item) {
            if ($item['type'] === 'separator') {
                $groups[] = $group;
                $group = array();
            } else {
                $group[] = $item;
            }
        }
        if (count($group)) {
            $groups[] = $group;
        }

        return $groups;
    }

    public function getHTML(): string
    {
        $lng = $this->lng;

        $this->applyAutoStickyToSingleElement();

        if (count($this->items) || count($this->sticky_items)) {
            $tpl = new ilTemplate("tpl.toolbar.html", true, true, "Services/UIComponent/Toolbar");
            $tpl->setVariable('TOOLBAR_ID', $this->getId());
            $tpl->setVariable('MORE_LABEL', $this->lng->txt('toolbar_more_actions'));

            if (count($this->sticky_items)) {
                $tpl_sticky = new ilTemplate("tpl.toolbar_sticky_items.html", true, true, "Services/UIComponent/Toolbar");
                /** @var ilToolbarItem $sticky_item */
                foreach ($this->sticky_items as $sticky_item) {
                    if ($sticky_item['label']) {
                        $tpl_sticky->setCurrentBlock('input_label');
                        $tpl_sticky->setVariable('TXT_INPUT', $sticky_item['item']->getTitle());
                        $tpl_sticky->parseCurrentBlock();
                    }

                    if ($sticky_item['item'] instanceof ilToolbarItem) {
                        $tpl_sticky->setCurrentBlock('sticky_item');
                        $tpl_sticky->setVariable('STICKY_ITEM_HTML', $sticky_item['item']->getToolbarHTML());
                        $tpl_sticky->parseCurrentBlock();
                    } elseif ($sticky_item['item'] instanceof \ILIAS\UI\Component\Component) {
                        $tpl_sticky->setCurrentBlock("sticky_item");
                        $tpl_sticky->setVariable("STICKY_ITEM_HTML", $this->ui->renderer()->render($sticky_item['item']));
                        $tpl_sticky->parseCurrentBlock();
                    }
                }
                $tpl->setCurrentBlock('sticky_items');
                $tpl->setVariable('STICKY_ITEMS', $tpl_sticky->get());
                $tpl->parseCurrentBlock();
            }

            // Hide toggle button if only sticky items are in the toolbar
            if (count($this->items) === 0) {
                $tpl->setVariable('HIDE_TOGGLE_CLASS', ' hidden');
            }

            $markup_items = '';
            foreach ($this->getGroupedItems() as $i => $group) {
                $tpl_items = new ilTemplate("tpl.toolbar_items.html", true, true, "Services/UIComponent/Toolbar");
                if ($i > 0) {
                    static $tpl_separator;
                    if ($tpl_separator === null) {
                        $tpl_separator = new ilTemplate('tpl.toolbar_separator.html', true, true, 'Services/UIComponent/Toolbar');
                    }
                    $tpl_separator->touchBlock('separator');
                    $markup_items .= $tpl_separator->get();
                }
                foreach ($group as $item) {
                    switch ($item["type"]) {
                        case "button":
                            $tpl_items->setCurrentBlock("button");
                            $tpl_items->setVariable("BTN_TXT", $item["txt"]);
                            $tpl_items->setVariable("BTN_LINK", $item["cmd"]);
                            if ($item["target"] != "") {
                                $tpl_items->setVariable("BTN_TARGET", 'target="' . $item["target"] . '"');
                            }
                            if ($item["id"] != "") {
                                $tpl_items->setVariable("BID", 'id="' . $item["id"] . '"');
                            }
                            if (($item['add_attrs'])) {
                                $tpl_items->setVariable('BTN_ADD_ARG', $item['add_attrs']);
                            }
                            $tpl_items->setVariable('BTN_CLASS', $item['class']);
                            $tpl_items->parseCurrentBlock();
                            $tpl_items->touchBlock("item");
                            break;

                        case "fbutton":
                            $tpl_items->setCurrentBlock("form_button");
                            $tpl_items->setVariable("SUB_TXT", $item["txt"]);
                            $tpl_items->setVariable("SUB_CMD", $item["cmd"]);
                            if ($item["primary"]) {
                                $tpl_items->setVariable("SUB_CLASS", " emphsubmit");
                            } elseif ($item["class"]) {
                                $tpl_items->setVariable("SUB_CLASS", " " . $item["class"]);
                            }
                            $tpl_items->parseCurrentBlock();
                            $tpl_items->touchBlock("item");
                            break;

                        case "button_obj":
                            $tpl_items->setCurrentBlock("button_instance");
                            $tpl_items->setVariable("BUTTON_OBJ", $item["instance"]->render());
                            $tpl_items->parseCurrentBlock();
                            $tpl_items->touchBlock("item");
                            break;

                        case "input":
                            if ($item["label"]) {
                                $tpl_items->setCurrentBlock("input_label");
                                $tpl_items->setVariable("TXT_INPUT", $item["input"]->getTitle());
                                $tpl_items->parseCurrentBlock();
                            }
                            $tpl_items->setCurrentBlock("input");
                            $tpl_items->setVariable("INPUT_HTML", $item["input"]->getToolbarHTML());
                            $tpl_items->parseCurrentBlock();
                            $tpl_items->touchBlock("item");
                            break;

                        // bs-patch start
                        case "dropdown":
                            $tpl_items->setCurrentBlock("dropdown");
                            $tpl_items->setVariable("TXT_DROPDOWN", $item["txt"]);
                            $tpl_items->setVariable("DROP_DOWN", $item["dd_html"]);
                            $tpl_items->parseCurrentBlock();
                            $tpl_items->touchBlock("item");
                            break;
                        // bs-patch end
                        case "text":
                            $tpl_items->setCurrentBlock("text");
                            $tpl_items->setVariable("VAL_TEXT", $item["text"]);
                            $tpl_items->touchBlock("item");
                            break;

                        case "component":
                            $tpl_items->setCurrentBlock("component");
                            $tpl_items->setVariable("COMPONENT", $this->ui->renderer()->render($item["component"]));
                            $tpl_items->touchBlock("item");
                            break;

                        case "adv_sel_list":
                            $tpl_items->setCurrentBlock("component");
                            $tpl_items->setVariable("COMPONENT", $item["list"]->getHTML());
                            $tpl_items->touchBlock("item");
                            break;


                        case "spacer":
                            $tpl_items->touchBlock("spacer");
                            if (!$item["width"]) {
                                $item["width"] = 2;
                            }
                            $tpl_items->setVariable("SPACER_WIDTH", $item["width"]);
                            $tpl_items->touchBlock("item");
                            break;

                        case "link":
                            if ($item["disabled"] == false) {
                                $tpl_items->setCurrentBlock("link");
                                $tpl_items->setVariable("LINK_TXT", $item["txt"]);
                                $tpl_items->setVariable("LINK_URL", $item["cmd"]);
                            } else {
                                $tpl_items->setCurrentBlock("link_disabled");
                                $tpl_items->setVariable("LINK_DISABLED_TXT", $item["txt"]);
                                //$tpl_items->setVariable("LINK_URL", $item["cmd"]);
                            }
                            $tpl_items->parseCurrentBlock();
                            $tpl_items->touchBlock("item");
                            break;
                    }
                }
                $li = (count($group) > 1) ? "<li class='ilToolbarGroup'>" : "<li>";
                $markup_items .= $li . $tpl_items->get() . '</li>';
            }

            $tpl->setVariable('ITEMS', $markup_items);
            $tpl->setVariable("TXT_FUNCTIONS", $lng->txt("functions"));
            if ($this->lead_img["img"] != "") {
                $tpl->setCurrentBlock("lead_image");
                $tpl->setVariable("IMG_SRC", $this->lead_img["img"]);
                $tpl->setVariable("IMG_ALT", $this->lead_img["alt"]);
                $tpl->parseCurrentBlock();
            }

            // form?
            if ($this->getFormAction() !== "") {
                // #18947
                $GLOBALS["tpl"]->addJavaScript("Services/Form/js/Form.js");

                if ($this->getOpenFormTag()) {
                    $tpl->setCurrentBlock("form_open");
                    $tpl->setVariable("FORMACTION", $this->getFormAction());
                    if ($this->getPreventDoubleSubmission()) {
                        $tpl->setVariable("FORM_CLASS", "preventDoubleSubmission");
                    }
                    if ($this->multipart) {
                        $tpl->setVariable("ENC_TYPE", 'enctype="multipart/form-data"');
                    }
                    if ($this->form_target !== "") {
                        $tpl->setVariable("TARGET", ' target="' . $this->form_target . '" ');
                    }
                    if ($this->form_name !== "") {
                        $tpl->setVariable("FORMNAME", 'name="' . $this->getFormName() . '"');
                    }

                    $tpl->parseCurrentBlock();
                }
                if ($this->getCloseFormTag()) {
                    $tpl->touchBlock("form_close");
                }
            }

            // id
            if ($this->getId() !== "") {
                $tpl->setVariable("ID", ' id="' . $this->getId() . '" ');
            }

            // hidden style
            if ($this->getHidden()) {
                $tpl->setVariable("HIDDEN_CLASS", 'ilNoDisplay');
            }

            return $tpl->get();
        }
        return "";
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function setItems(array $items): void
    {
        $this->items = $items;
    }

    /**
     * If the toolbar consists of only one button, make it sticky
     * Note: Atm this is only possible for buttons. If we are dealing with objects implementing the ilToolbarItem
     * interface one day, other elements can be added as sticky.
     */
    protected function applyAutoStickyToSingleElement(): void
    {
        if (count($this->items) === 1 && count($this->sticky_items) === 0) {
            $supported_types = ['button', 'fbutton', 'button_obj'];
            $item = $this->items[0];
            if (!in_array($item['type'], $supported_types)) {
                return;
            }
            $button = null;
            switch ($item['type']) {
                case 'button_obj':
                    $button = $item['instance'];
                    break;
                case 'fbutton':
                    $button = ilSubmitButton::getInstance();
                    $button->setPrimary($item['primary']);
                    $button->setCaption($item['txt'], false);
                    $button->setCommand($item['cmd']);
                    break;
                case 'button':
                    $button = ilLinkButton::getInstance();
                    $button->setCaption($item['txt'], false);
                    $button->setUrl($item['cmd']);
                    $button->setTarget($item['target']);
                    $button->setId($item['id']);
                    break;
            }
            $this->addStickyItem($button);
            $this->items = [];
        }
    }
}
