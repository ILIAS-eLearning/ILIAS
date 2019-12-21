<?php
require_once('./Services/UIComponent/Button/classes/class.ilSubmitButton.php');
require_once('./Services/UIComponent/Button/classes/class.ilLinkButton.php');

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Toolbar. The toolbar currently only supports a list of buttons as links.
 *
 * A default toolbar object is available in the $ilToolbar global object.
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 * @version $Id$
 * @ingroup ServicesUIComponent
 */
class ilToolbarGUI
{
    /**
     * @var ilLanguage
     */
    protected $lng;


    /**
     * @var int
     */
    protected static $instances = 0;

    /**
     * @var string
     */
    protected $id = '';

    /**
     * @var string
     */
    protected $form_action = '';

    /**
     * @var bool
     */
    protected $hidden;

    /**
     * @var array
     */
    public $items = array();

    /**
     * @var array
     */
    protected $lead_img = array(
        'img' => '',
        'alt' => '',
    );

    /**
     * @var bool
     */
    protected $open_form_tag = true;

    /**
     * @var bool
     */
    protected $close_form_tag = true;

    /**
     * @var string
     */
    protected $form_target = "";

    /**
     * @var string
     */
    protected $form_name = "";

    /**
     * @var bool
     */
    protected $prevent_double_submission = false;

    /**
     * @var array
     */
    protected $sticky_items = array();

    /**
     * @var bool
     */
    protected $has_separator = false;

    public function __construct()
    {
        global $DIC;
        $this->lng = $DIC->language();

        $this->ui = $DIC->ui();

        self::$instances++;
    }

    /**
     * Set form action (if form action is set, toolbar is wrapped into form tags)
     *
     * @param string $a_val form action
     * @param bool $a_multipart
     * @param string $a_target
     */
    public function setFormAction($a_val, $a_multipart = false, $a_target = "")
    {
        $this->form_action = $a_val;
        $this->multipart = $a_multipart;
        $this->form_target = $a_target;
    }

    /**
     * Get form action
     *
     * @return	string	form action
     */
    public function getFormAction()
    {
        return $this->form_action;
    }


    /**
     * Set leading image
     *
     * @param string $a_img
     * @param string $a_alt
     */
    public function setLeadingImage($a_img, $a_alt)
    {
        $this->lead_img = array("img" => $a_img, "alt" => $a_alt);
    }

    /**
     * Set hidden
     *
     * @param boolean $a_val hidden
     */
    public function setHidden($a_val)
    {
        $this->hidden = $a_val;
    }

    /**
     * Get hidden
     *
     * @return boolean hidden
     */
    public function getHidden()
    {
        return $this->hidden;
    }

    /**
     * Set id
     *
     * @param string $a_val id
     */
    public function setId($a_val)
    {
        $this->id = $a_val;
    }

    /**
     * Get id
     *
     * @return string id
     */
    public function getId()
    {
        return $this->id ? $this->id : self::$instances;
    }

    /**
     * Set prevent double submission
     *
     * @param bool $a_val prevent double submission
     */
    public function setPreventDoubleSubmission($a_val)
    {
        $this->prevent_double_submission = $a_val;
    }

    /**
     * Get prevent double submission
     *
     * @return bool prevent double submission
     */
    public function getPreventDoubleSubmission()
    {
        return $this->prevent_double_submission;
    }

    /**
     * Add button to toolbar
     *
     * @deprecated use addButtonInstance() instead!
     *
     * @param	string		text
     * @param	string		link href / submit command
     * @param	string		frame target
     * @param	string		access key
     */
    public function addButton(
        $a_txt,
        $a_cmd,
        $a_target = "",
        $a_acc_key = "",
        $a_additional_attrs = '',
        $a_id = "",
        $a_class = 'submit'
    ) {
        $this->items[] = array("type" => "button", "txt" => $a_txt, "cmd" => $a_cmd,
            "target" => $a_target, "acc_key" => $a_acc_key, 'add_attrs' => $a_additional_attrs,
            "id" => $a_id, "class" => $a_class);
    }

    /**
     * Add form button to toolbar
     *
     * @deprecated use addButtonInstance() instead!
     *
     * @param	string		text
     * @param	string		link href / submit command
     * @param	string		access key
     * @param	bool		primary action
     * @param	string		css class
     */
    public function addFormButton($a_txt, $a_cmd, $a_acc_key = "", $a_primary = false, $a_class = false)
    {
        if ($a_primary) {
            $button = ilSubmitButton::getInstance();
            $button->setPrimary(true);
            $button->setCaption($a_txt, false);
            $button->setCommand($a_cmd);
            $button->setAccessKey($a_acc_key);
            $this->addStickyItem($button);
        } else {
            $this->items[] = array("type" => "fbutton", "txt" => $a_txt, "cmd" => $a_cmd,
                "acc_key" => $a_acc_key, "primary" => $a_primary, "class" => $a_class);
        }
    }


    /**
     * Add input item
     *
     * @param ilToolbarItem $a_item
     * @param bool $a_output_label
     */
    public function addInputItem(ilToolbarItem $a_item, $a_output_label = false)
    {
        $this->items[] = array("type" => "input", "input" => $a_item, "label" => $a_output_label);
    }


    /**
     * Add a sticky item. Sticky items are always visible, also if the toolbar is collapsed (responsive view).
     * Sticky items are displayed first in the toolbar.
     *
     * @param ilToolbarItem|\ILIAS\UI\Component\Component $a_item
     * @param bool $a_output_label
     */
    public function addStickyItem($a_item, $a_output_label = false)
    {
        $this->sticky_items[] = array("item"=>$a_item, "label"=>$a_output_label);
    }


    /**
     * Add button instance

     * @param ilButtonBase $a_button
     */
    public function addButtonInstance(ilButtonBase $a_button)
    {
        if ($a_button->isPrimary()) {
            $this->addStickyItem($a_button);
        } else {
            $this->items[] = array("type" => "button_obj", "instance" => $a_button);
        }
    }

    // bs-patch start
    /**
     * Add input item
     */
    public function addDropDown($a_txt, $a_dd_html)
    {
        $this->items[] = array("type" => "dropdown", "txt" => $a_txt, "dd_html" => $a_dd_html);
    }
    // bs-patch end

    /**
     * Add separator
     */
    public function addSeparator()
    {
        $this->items[] = array("type" => "separator");
        $this->has_separator = true;
    }

    /**
     * Add text
     */
    public function addText($a_text)
    {
        $this->items[] = array("type" => "text", "text" => $a_text);
    }

    /**
     * Add spacer
     */
    public function addSpacer($a_width = null)
    {
        $this->items[] = array("type" => "spacer", "width" => $a_width);
    }

    /**
     * Add component
     */
    public function addComponent(\ILIAS\UI\Component\Component $a_comp)
    {
        $this->items[] = array("type" => "component", "component" => $a_comp);
    }

    /**
     * Add link
     *
     * @param  string $a_caption
     * @param string $a_url
     * @param boolean $a_disabled
     */
    public function addLink($a_caption, $a_url, $a_disabled = false)
    {
        $this->items[] = array("type" => "link", "txt" => $a_caption, "cmd" => $a_url, "disabled" => $a_disabled);
    }

    /**
     * Set open form tag
     *
     * @param boolean $a_val open form tag
     */
    public function setOpenFormTag($a_val)
    {
        $this->open_form_tag = $a_val;
    }

    /**
     * Get open form tag
     *
     * @return	boolean	open form tag
     */
    public function getOpenFormTag()
    {
        return $this->open_form_tag;
    }

    /**
     * Set close form tag
     *
     * @param boolean $a_val close form tag
     */
    public function setCloseFormTag($a_val)
    {
        $this->close_form_tag = $a_val;
    }

    /**
     * Get close form tag
     *
     * @return	boolean	close form tag
     */
    public function getCloseFormTag()
    {
        return $this->close_form_tag;
    }

    /**
     * Set form name
     *
     * @param string $a_val form name
     */
    public function setFormName($a_val)
    {
        $this->form_name = $a_val;
    }

    /**
     * Get form name
     *
     * @return	string form name
     */
    public function getFormName()
    {
        return $this->form_name;
    }


    /**
     * Get all groups (items separated by a separator)
     *
     * @return array
     */
    public function getGroupedItems()
    {
        $groups = array();
        $group = array();
        foreach ($this->items as $item) {
            if ($item['type'] == 'separator') {
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

    /**
     * Get toolbar html
     */
    public function getHTML()
    {
        $lng = $this->lng;

        $this->applyAutoStickyToSingleElement();

        if (count($this->items) || count($this->sticky_items)) {
            $tpl = new ilTemplate("tpl.toolbar.html", true, true, "Services/UIComponent/Toolbar");
            $tpl->setVariable('TOOLBAR_ID', $this->getId());
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
            if (count($this->items) == 0) {
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
                            if ($item["acc_key"] != "") {
                                include_once("./Services/Accessibility/classes/class.ilAccessKeyGUI.php");
                                $tpl_items->setVariable(
                                    "BTN_ACC_KEY",
                                    ilAccessKeyGUI::getAttribute($item["acc_key"])
                                );
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
                                $tpl_items->parseCurrentBlock();
                                $tpl_items->touchBlock("item");
                                break;
                            } else {
                                $tpl_items->setCurrentBlock("link_disabled");
                                $tpl_items->setVariable("LINK_DISABLED_TXT", $item["txt"]);
                                //$tpl_items->setVariable("LINK_URL", $item["cmd"]);
                                $tpl_items->parseCurrentBlock();
                                $tpl_items->touchBlock("item");
                                break;
                            }
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
            if ($this->getFormAction() != "") {
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
                    if ($this->form_target != "") {
                        $tpl->setVariable("TARGET", ' target="' . $this->form_target . '" ');
                    }
                    if ($this->form_name != "") {
                        $tpl->setVariable("FORMNAME", 'name="' . $this->getFormName() . '"');
                    }

                    $tpl->parseCurrentBlock();
                }
                if ($this->getCloseFormTag()) {
                    $tpl->touchBlock("form_close");
                }
            }

            // id
            if ($this->getId() != "") {
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


    /**
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }


    /**
     * @param array $items
     */
    public function setItems($items)
    {
        $this->items = $items;
    }


    /**
     * If the toolbar consists of only one button, make it sticky
     * Note: Atm this is only possible for buttons. If we are dealing with objects implementing the ilToolbarItem
     * interface one day, other elements can be added as sticky.
     */
    protected function applyAutoStickyToSingleElement()
    {
        if (count($this->items) == 1 && count($this->sticky_items) == 0) {
            $supported_types = array('button', 'fbutton', 'button_obj');
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
                    $button->setAccessKey($item['acc_key']);
                    break;
                case 'button':
                    $button = ilLinkButton::getInstance();
                    $button->setCaption($item['txt'], false);
                    $button->setUrl($item['cmd']);
                    $button->setTarget($item['target']);
                    $button->setId($item['id']);
                    $button->setAccessKey($item['acc_key']);
                    break;
            }
            $this->addStickyItem($button);
            $this->items = array();
        }
    }
}
