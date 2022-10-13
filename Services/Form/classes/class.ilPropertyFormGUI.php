<?php

declare(strict_types=1);

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

use ILIAS\HTTP;
use ILIAS\Refinery;

/**
 * This class represents a property form user interface
 *
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilPropertyFormGUI: ilFormPropertyDispatchGUI
 */
class ilPropertyFormGUI extends ilFormGUI
{
    protected bool $required_text = false;
    protected ilLanguage $lng;
    protected ilCtrl $ctrl;
    protected ilTemplate $tpl;
    protected ?ilObjUser $user = null;
    protected ?ilSetting $settings = null;
    private array $buttons = array();
    private array $items = array();
    protected string $mode = "std";
    protected bool $check_input_called = false;
    protected bool $disable_standard_message = false;
    protected string $top_anchor = "il_form_top";
    protected string $title = '';
    protected string $titleicon = "";
    protected string $description = "";
    protected string $tbl_width = "";
    protected bool $show_top_buttons = true;
    protected bool $hide_labels = false;
    protected bool $force_top_buttons = false;
    protected HTTP\Services $http;
    protected ?Refinery\Factory $refinery = null;

    protected ?ilGlobalTemplateInterface $global_tpl = null;
    protected $onload_code = [];

    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();

        $this->user = null;
        if (isset($DIC["ilUser"])) {
            $this->user = $DIC["ilUser"];
        }

        $this->settings = null;
        if (isset($DIC["ilSetting"])) {
            $this->settings = $DIC["ilSetting"];
        }

        $lng = $DIC->language();

        $lng->loadLanguageModule("form");

        // avoid double submission
        $this->setPreventDoubleSubmission(true);

        // do it as early as possible
        $this->rebuildUploadedFiles();
        if (isset($DIC["http"])) {
            $this->http = $DIC->http();
        }
        if (isset($DIC["refinery"])) {
            $this->refinery = $DIC->refinery();
        }
        if (isset($DIC["tpl"])) {      // some unit tests will fail otherwise
            $this->global_tpl = $DIC['tpl'];
        }
    }

    /**
     * @return mixed
     * @throws ilCtrlException
     */
    public function executeCommand()
    {
        $ilCtrl = $this->ctrl;

        $next_class = $ilCtrl->getNextClass($this);

        switch ($next_class) {
            case 'ilformpropertydispatchgui':
                $ilCtrl->saveParameter($this, 'postvar');
                $form_prop_dispatch = new ilFormPropertyDispatchGUI();
                $item = $this->getItemByPostVar($this->getRequestedPostVar());
                $form_prop_dispatch->setItem($item);
                return $ilCtrl->forwardCommand($form_prop_dispatch);
        }
        return false;
    }

    protected function getRequestedPostVar(): ?string
    {
        $t = $this->refinery->kindlyTo()->string();
        $w = $this->http->wrapper();
        if ($w->post()->has("postvar")) {
            return $w->post()->retrieve("postvar", $t);
        }
        if ($w->query()->has("postvar")) {
            return $w->query()->retrieve("postvar", $t);
        }
        return null;
    }

    final public function setTableWidth(string $a_width): void
    {
        $this->tbl_width = $a_width;
    }

    final public function getTableWidth(): string
    {
        return $this->tbl_width;
    }

    // Set Mode ('std', 'subform').
    public function setMode(string $a_mode): void
    {
        $this->mode = $a_mode;
    }

    public function getMode(): string
    {
        return $this->mode;
    }

    public function setTitle(string $a_title): void
    {
        $this->title = $a_title;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitleIcon(string $a_titleicon): void
    {
        $this->titleicon = $a_titleicon;
    }

    public function getTitleIcon(): string
    {
        return $this->titleicon;
    }

    public function setDescription(string $a_val): void
    {
        $this->description = $a_val;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setTopAnchor(string $a_val): void
    {
        $this->top_anchor = $a_val;
    }

    public function getTopAnchor(): string
    {
        return $this->top_anchor;
    }

    public function setShowTopButtons(bool $a_val): void
    {
        $this->show_top_buttons = $a_val;
    }

    public function getShowTopButtons(): bool
    {
        return $this->show_top_buttons;
    }

    public function setForceTopButtons(bool $a_val): void
    {
        $this->force_top_buttons = $a_val;
    }

    public function getForceTopButtons(): bool
    {
        return $this->force_top_buttons;
    }

    /**
     * @param ilFormPropertyGUI|ilFormSectionHeaderGUI $a_item
     */
    public function addItem($a_item): void
    {
        $a_item->setParentForm($this);
        $this->items[] = $a_item;
    }

    public function removeItemByPostVar(
        string $a_post_var,
        bool $a_remove_unused_headers = false
    ): void {
        foreach ($this->items as $key => $item) {
            if (method_exists($item, "getPostVar") && $item->getPostVar() == $a_post_var) {
                unset($this->items[$key]);
            }
        }

        // remove section headers if they do not contain any items anymore
        if ($a_remove_unused_headers) {
            $unset_keys = array();
            $last_item = null;
            $last_key = null;
            foreach ($this->items as $key => $item) {
                if ($item instanceof ilFormSectionHeaderGUI && $last_item instanceof ilFormSectionHeaderGUI) {
                    $unset_keys[] = $last_key;
                }
                $last_item = $item;
                $last_key = $key;
            }
            if ($last_item instanceof ilFormSectionHeaderGUI) {
                $unset_keys[] = $last_key;
            }
            foreach ($unset_keys as $key) {
                unset($this->items[$key]);
            }
        }
    }

    public function getItemByPostVar(string $a_post_var): ?ilFormPropertyGUI
    {
        foreach ($this->items as $item) {
            if ($item->getType() != "section_header") {
                //if ($item->getPostVar() == $a_post_var)
                $ret = $item->getItemByPostVar($a_post_var);
                if (is_object($ret)) {
                    return $ret;
                }
            }
        }

        return null;
    }

    public function setItems(array $a_items): void
    {
        $this->items = $a_items;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * returns a flat array of all input items including
     * the possibly existing subitems recursively
     */
    public function getInputItemsRecursive(): array
    {
        $inputItems = array();

        foreach ($this->items as $item) {
            if ($item->getType() == 'section_header') {
                continue;
            }

            $inputItems[] = $item;

            if ($item instanceof ilSubEnabledFormPropertyGUI) {
                $inputItems = array_merge($inputItems, $item->getSubInputItemsRecursive());
            }
        }

        return $inputItems;
    }

    public function setDisableStandardMessage(bool $a_val): void
    {
        $this->disable_standard_message = $a_val;
    }

    public function getDisableStandardMessage(): bool
    {
        return $this->disable_standard_message;
    }

    // Get a value indicating whether the labels should be hidden or not.
    public function getHideLabels(): bool
    {
        return $this->hide_labels;
    }

    public function setHideLabels(bool $a_value = true): void
    {
        $this->hide_labels = $a_value;
    }

    public function setValuesByArray(
        array $a_values,
        bool $a_restrict_to_value_keys = false
    ): void {
        foreach ($this->items as $item) {
            if (!($a_restrict_to_value_keys) ||
                in_array($item->getPostVar(), array_keys($a_values))) {
                $item->setValueByArray($a_values);
            }
        }
    }

    public function setValuesByPost()
    {
        global $DIC;

        if (!isset($DIC["http"])) {
            return null;
        }

        foreach ($this->items as $item) {
            $item->setValueByArray($DIC->http()->request()->getParsedBody());
        }
    }

    public function checkInput(): bool
    {
        global $DIC;

        if ($this->check_input_called) {
            die("Error: ilPropertyFormGUI->checkInput() called twice.");
        }

        $ok = true;
        foreach ($this->items as $item) {
            $item_ok = $item->checkInput();
            if (!$item_ok) {
                $ok = false;
            }
        }

        // check if POST is missing completely (if post_max_size exceeded)
        $post = $this->http->request()->getParsedBody();
        if (count($this->items) > 0 && count($post) === 0) {
            $ok = false;
        }

        $this->check_input_called = true;

        // try to keep uploads for another try
        $filehash = $this->getFileHash();
        if (!$ok && !is_null($filehash) && $filehash && count($_FILES)) {
            $hash = $filehash;

            foreach ($_FILES as $field => $data) {
                // only try to keep files that are ok
                // see 25484: Wrong error handling when uploading icon instead of tile
                $item = $this->getItemByPostVar($field);
                if (is_bool($item) || !$item->checkInput()) {
                    continue;
                }
                // we support up to 2 nesting levels (see test/assessment)
                if (is_array($data["tmp_name"])) {
                    foreach ($data["tmp_name"] as $idx => $upload) {
                        if (is_array($upload)) {
                            foreach ($upload as $idx2 => $file) {
                                if ($file && is_uploaded_file($file)) {
                                    $file_name = $data["name"][$idx][$idx2];
                                    $file_type = $data["type"][$idx][$idx2];
                                    $this->keepFileUpload($hash, $field, $file, $file_name, $file_type, (string) $idx, (string) $idx2);
                                }
                            }
                        } elseif ($upload && is_uploaded_file($upload)) {
                            $file_name = $data["name"][$idx];
                            $file_type = $data["type"][$idx];
                            $this->keepFileUpload($hash, $field, $upload, $file_name, $file_type, (string) $idx);
                        }
                    }
                } else {
                    $this->keepFileUpload($hash, $field, $data["tmp_name"], $data["name"], $data["type"]);
                }
            }
        }
        $http = $DIC->http();
        $txt = $DIC->language()->txt("form_input_not_valid");
        switch ($http->request()->getHeaderLine('Accept')) {
            // When JS asks for a valid JSON-Response, we send the success and message as JSON
            case 'application/json':
                $stream = \ILIAS\Filesystem\Stream\Streams::ofString(json_encode([
                    'success' => $ok,
                    'message' => $txt,
                ]));
                $http->saveResponse($http->response()->withBody($stream));

                return $ok;

            // Otherwise, we send it using ilUtil, and it will be rendered in the Template
            default:

                if (!$ok && !$this->getDisableStandardMessage()) {
                    $this->global_tpl->setOnScreenMessage('failure', $txt);
                }

                return $ok;
        }
    }

    protected function getFileHash(): ?string
    {
        if (is_null($this->refinery)) {
            return null;
        }
        // try to keep uploads for another try
        $t = $this->refinery->kindlyTo()->string();
        $w = $this->http->wrapper();
        $filehash = null;
        if ($w->post()->has("ilfilehash")) {
            $filehash = $w->post()->retrieve("ilfilehash", $t);
        }
        return $filehash;
    }

    /**
     * Returns the input of an item, if item provides getInput method
     * and as fallback the value of the HTTP-POST variable, identified by the passed postvar
     * @param string $a_post_var       The key used for value determination
     * @param bool   $ensureValidation A flag whether the form input has to be validated before calling this method
     * @return mixed The value of a HTTP-POST variable, identified by the passed id
     */
    public function getInput(
        string $a_post_var,
        bool $ensureValidation = true
    ) {
        // this check ensures, that checkInput has been called (incl. stripSlashes())
        if (!$this->check_input_called && $ensureValidation) {
            throw new LogicException('Error: ilPropertyFormGUI->getInput() called without calling checkInput() first.');
        }

        $item = $this->getItemByPostVar($a_post_var);
        if (is_object($item) && method_exists($item, "getInput")) {
            return $item->getInput();
        }

        $post = $this->http->request()->getParsedBody();
        return $post[$a_post_var] ?? '';
    }

    public function addCommandButton(
        string $a_cmd,
        string $a_text,
        string $a_id = ""
    ): void {
        $this->buttons[] = array("cmd" => $a_cmd, "text" => $a_text, "id" => $a_id);
    }


    public function getCommandButtons(): array
    {
        return $this->buttons;
    }

    public function clearCommandButtons(): void
    {
        $this->buttons = array();
    }

    public function getContent(): string
    {
        global $DIC;
        $lng = $this->lng;
        $tpl = $DIC["tpl"];
        $ilSetting = $this->settings;

        ilYuiUtil::initEvent();
        ilYuiUtil::initDom();

        $tpl->addJavaScript("./Services/JavaScript/js/Basic.js");
        $tpl->addJavaScript("Services/Form/js/Form.js");

        $this->tpl = new ilTemplate("tpl.property_form.html", true, true, "Services/Form");

        // check if form has not title and first item is a section header
        // -> use section header for title and remove section header
        // -> command buttons are presented on top
        $fi = $this->items[0] ?? null;
        if ($this->getMode() == "std" &&
            $this->getTitle() == "" &&
            is_object($fi) && $fi->getType() == "section_header"
            ) {
            $this->setTitle($fi->getTitle());
            unset($this->items[0]);
        }


        // title icon
        if ($this->getTitleIcon() != "" && is_file($this->getTitleIcon())) {
            $this->tpl->setCurrentBlock("title_icon");
            $this->tpl->setVariable("IMG_ICON", $this->getTitleIcon());
            $this->tpl->parseCurrentBlock();
        }

        // title
        if ($this->getTitle() != "") {
            // commands on top
            if (count($this->buttons) > 0 && $this->getShowTopButtons() && (count($this->items) > 2 || $this->force_top_buttons)) {
                // command buttons
                foreach ($this->buttons as $button) {
                    $this->tpl->setCurrentBlock("cmd2");
                    $this->tpl->setVariable("CMD", $button["cmd"]);
                    $this->tpl->setVariable("CMD_TXT", $button["text"]);
                    if ($button["id"] != "") {
                        $this->tpl->setVariable("CMD2_ID", " id='" . $button["id"] . "_top'");
                    }
                    $this->tpl->parseCurrentBlock();
                }
                $this->tpl->setCurrentBlock("commands2");
                $this->tpl->parseCurrentBlock();
            }

            if (is_object($ilSetting)) {
                if ($ilSetting->get('char_selector_availability') > 0) {
                    if (ilCharSelectorGUI::_isAllowed()) {
                        $char_selector = ilCharSelectorGUI::_getCurrentGUI();
                        if ($char_selector->getConfig()->getAvailability() == ilCharSelectorConfig::ENABLED) {
                            $char_selector->addToPage();
                            $this->tpl->touchBlock('char_selector');
                        }
                    }
                }
            }

            $this->tpl->setCurrentBlock("header");
            $this->tpl->setVariable("TXT_TITLE", $this->getTitle());
            //$this->tpl->setVariable("LABEL", $this->getTopAnchor());
            $this->tpl->setVariable("TXT_DESCRIPTION", $this->getDescription());
            $this->tpl->parseCurrentBlock();
        }
        $this->tpl->touchBlock("item");

        // properties
        $this->required_text = false;
        foreach ($this->items as $item) {
            if ($item->getType() != "hidden") {
                $this->insertItem($item);
            }
        }

        // required
        if ($this->required_text && $this->getMode() == "std") {
            $this->tpl->setCurrentBlock("required_text");
            $this->tpl->setVariable("TXT_REQUIRED", $lng->txt("required_field"));
            $this->tpl->parseCurrentBlock();
        }

        // command buttons
        foreach ($this->buttons as $button) {
            $this->tpl->setCurrentBlock("cmd");
            $this->tpl->setVariable("CMD", $button["cmd"]);
            $this->tpl->setVariable("CMD_TXT", $button["text"]);

            if ($button["id"] != "") {
                $this->tpl->setVariable("CMD_ID", " id='" . $button["id"] . "'");
            }

            $this->tpl->parseCurrentBlock();
        }

        // #18808
        if ($this->getMode() != "subform") {
            // try to keep uploads even if checking input fails
            if ($this->getMultipart()) {
                $hash = $this->getFileHash() ?? null;
                if (!$hash) {
                    $hash = md5(uniqid((string) mt_rand(), true));
                }
                $fhash = new ilHiddenInputGUI("ilfilehash");
                $fhash->setValue($hash);
                $this->addItem($fhash);
            }
        }

        // hidden properties
        $hidden_fields = false;
        foreach ($this->items as $item) {
            if ($item->getType() == "hidden") {
                $item->insert($this->tpl);
                $hidden_fields = true;
            }
        }

        if ($this->required_text || count($this->buttons) > 0 || $hidden_fields) {
            $this->tpl->setCurrentBlock("commands");
            $this->tpl->parseCurrentBlock();
        }


        if ($this->getMode() == "subform") {
            $this->tpl->touchBlock("sub_table");
        } else {
            $this->tpl->touchBlock("std_table");
            $this->tpl->setVariable('STD_TABLE_WIDTH', $this->getTableWidth());
        }

        return $this->tpl->get();
    }

    protected function hideRequired(string $a_type): bool
    {
        // #15818
        return $a_type == "non_editable_value";
    }

    /**
     * @param ilFormPropertyGUI|ilFormSectionHeaderGUI $item
     */
    public function insertItem(
        $item,
        bool $a_sub_item = false
    ): void {
        global $DIC;
        $tpl = $DIC["tpl"];
        $lng = $this->lng;


        //$cfg = array();

        //if(method_exists($item, "getMulti") && $item->getMulti())
        if ($item instanceof ilMultiValuesItem && $item->getMulti()) {
            $tpl->addJavascript("./Services/Form/js/ServiceFormMulti.js");

            $this->tpl->setCurrentBlock("multi_in");
            $this->tpl->setVariable("ID", $item->getFieldId());
            $this->tpl->parseCurrentBlock();

            $this->tpl->touchBlock("multi_out");


            // add hidden item to enable preset multi items
            // not used yet, should replace hidden field stuff
            $multi_values = $item->getMultiValues();
            if (is_array($multi_values) && sizeof($multi_values) > 1) {
                $multi_value = new ilHiddenInputGUI("ilMultiValues~" . $item->getPostVar());
                $multi_value->setValue(implode("~", $multi_values));
                $this->addItem($multi_value);
            }
            //$cfg["multi_values"] = $multi_values;
        }

        $item->insert($this->tpl);

        if ($item->getType() == "file" || $item->getType() == "image_file") {
            $this->setMultipart(true);
        }

        if ($item->getType() != "section_header") {
            //$cfg["id"] = $item->getFieldId();

            // info text
            if ($item->getInfo() != "") {
                $this->tpl->setCurrentBlock("description");
                $this->tpl->setVariable(
                    "PROPERTY_DESCRIPTION",
                    $item->getInfo()
                );
                $this->tpl->parseCurrentBlock();
            }

            if ($this->getMode() == "subform") {
                // required
                if (!$this->hideRequired($item->getType())) {
                    if ($item->getRequired()) {
                        $this->tpl->touchBlock("sub_required");
                        $this->required_text = true;
                    }
                }

                // hidden title (for accessibility, e.g. file upload)
                if ($item->getHiddenTitle() != "") {
                    $this->tpl->setCurrentBlock("sub_hid_title");
                    $this->tpl->setVariable(
                        "SPHID_TITLE",
                        $item->getHiddenTitle()
                    );
                    $this->tpl->parseCurrentBlock();
                }

                $this->tpl->setCurrentBlock("sub_prop_start");
                $this->tpl->setVariable("PROPERTY_TITLE", $item->getTitle());
                $this->tpl->setVariable("PROPERTY_CLASS", "il_" . $item->getType());
                if ($item->getType() != "non_editable_value" && $item->getFormLabelFor() != "") {
                    $this->tpl->setVariable("FOR_ID", ' for="' . $item->getFormLabelFor() . '" ');
                }
                $this->tpl->setVariable("LAB_ID", $item->getFieldId());
            } else {
                // required
                if (!$this->hideRequired($item->getType())) {
                    if ($item->getRequired()) {
                        $this->tpl->touchBlock("required");
                        $this->required_text = true;
                    }
                }

                // hidden title (for accessibility, e.g. file upload)
                if ($item->getHiddenTitle() != "") {
                    $this->tpl->setCurrentBlock("std_hid_title");
                    $this->tpl->setVariable(
                        "PHID_TITLE",
                        $item->getHiddenTitle()
                    );
                    $this->tpl->parseCurrentBlock();
                }

                $this->tpl->setCurrentBlock("std_prop_start");
                $this->tpl->setVariable("PROPERTY_TITLE", $item->getTitle());
                if ($item->getType() != "non_editable_value" && $item->getFormLabelFor() != "") {
                    $this->tpl->setVariable("FOR_ID", ' for="' . $item->getFormLabelFor() . '" ');
                }
                $this->tpl->setVariable("LAB_ID", $item->getFieldId());
                if ($this->getHideLabels()) {
                    $this->tpl->setVariable("HIDE_LABELS_STYLE", " ilFormOptionHidden");
                }
            }
            $this->tpl->parseCurrentBlock();

            // alert
            if ($item->getType() != "non_editable_value" && $item->getAlert() != "") {
                $this->tpl->setCurrentBlock("alert");
                $this->tpl->setVariable(
                    "IMG_ALERT",
                    ilUtil::getImagePath("icon_alert.svg")
                );
                $this->tpl->setVariable(
                    "ALT_ALERT",
                    $lng->txt("alert")
                );
                $this->tpl->setVariable(
                    "TXT_ALERT",
                    $item->getAlert()
                );
                $this->tpl->parseCurrentBlock();
            }

            // subitems
            $sf = null;
            if ($item->getType() != "non_editable_value" or 1) {
                $sf = $item->getSubForm();
                if ($item->hideSubForm() && is_object($sf)) {
                    if ($this->global_tpl) {
                        $dsfid = $item->getFieldId();
                        $this->global_tpl->addOnloadCode(
                            "il.Form.hideSubForm('subform_$dsfid');"
                        );
                    }
                    $this->addOnloadCode("il.Form.hideSubForm('subform_$dsfid');");
                }
            }

            $sf_content = "";
            if (is_object($sf)) {
                $sf_content = $sf->getContent();
                if ($sf->getMultipart()) {
                    $this->setMultipart(true);
                }
                $this->tpl->setCurrentBlock("sub_form");
                $this->tpl->setVariable("PROP_SUB_FORM", $sf_content);
                $this->tpl->setVariable("SFID", $item->getFieldId());
                $this->tpl->parseCurrentBlock();
            }

            $this->tpl->setCurrentBlock("prop");
            /* not used yet
            $this->tpl->setVariable("ID", $item->getFieldId());
            $this->tpl->setVariable("CFG", json_encode($cfg, JSON_THROW_ON_ERROR));*/
            $this->tpl->parseCurrentBlock();
        }


        $this->tpl->touchBlock("item");
    }

    public function addOnloadCode(string $code): void
    {
        $this->onload_code[] = $code;
    }

    public function getHTML(): string
    {
        $html = parent::getHTML();

        // #13531 - get content that has to reside outside of the parent form tag, e.g. panels/layers
        foreach ($this->items as $item) {
            // #13536 - ilFormSectionHeaderGUI does NOT extend ilFormPropertyGUI ?!
            if (method_exists($item, "getContentOutsideFormTag")) {
                $outside = $item->getContentOutsideFormTag();
                if ($outside) {
                    $html .= $outside;
                }
            }
        }
        if ($this->ctrl->isAsynch()) {
            $html = $this->appendOnloadCode($html);
        }
        return $html;
    }

    public function getHTMLAsync(): string
    {
        $html = $this->getHTML();
        if (!$this->ctrl->isAsynch()) {
            $html = $this->appendOnloadCode($html);
        }
        return $html;
    }

    protected function appendOnloadCode(string $html): string
    {
        if (count($this->onload_code) > 0) {
            $html.= "<script>";
            foreach ($this->onload_code as $code) {
                $html.= $code . "\n";
            }
            $html.= "</script>";
        }
        return $html;
    }

    //
    // UPLOAD HANDLING
    //

    /**
     * Import upload into temp directory
     *
     * @param string $a_hash unique form hash
     * @param string $a_field form field
     * @param string $a_tmp_name temp file name
     * @param string $a_name original file name
     * @param string $a_type file mime type
     * @param ?string $a_index form field index (if array)
     * @param ?string $a_sub_index form field subindex (if array)
     * @throws ilException
     */
    protected function keepFileUpload(
        string $a_hash,
        string $a_field,
        string $a_tmp_name,
        string $a_name,
        string $a_type,
        ?string $a_index = null,
        ?string $a_sub_index = null
    ): void {
        if (trim($a_tmp_name) == "") {
            return;
        }

        $a_name = ilFileUtils::getASCIIFilename($a_name);

        $tmp_file_name = implode("~~", array(session_id(),
            $a_hash,
            $a_field,
            $a_index,
            $a_sub_index,
            str_replace("/", "~~", $a_type),
            str_replace("~~", "_", $a_name)));

        // make sure temp directory exists
        $temp_path = ilFileUtils::getDataDir() . "/temp";
        if (!is_dir($temp_path)) {
            ilFileUtils::createDirectory($temp_path);
        }

        ilFileUtils::moveUploadedFile($a_tmp_name, $tmp_file_name, $temp_path . "/" . $tmp_file_name);

        /** @var ilFileInputGUI $file_input */
        $file_input = $this->getItemByPostVar($a_field);
        $file_input->setPending($a_name);
    }

    /**
     * Get file upload data
     *
     * @param string $a_field form field
     * @param mixed $a_index form field index (if array)
     * @param mixed $a_sub_index form field subindex (if array)
     * @return array (tmp_name, name, type, error, size, is_upload)
     */
    public function getFileUpload(
        string $a_field,
        ?string $a_index = null,
        ?string $a_sub_index = null
    ): array {
        $res = array();
        if ($a_index) {
            if ($_FILES[$a_field]["tmp_name"][$a_index][$a_sub_index] ?? false) {
                $res = array(
                    "tmp_name" => $_FILES[$a_field]["tmp_name"][$a_index][$a_sub_index],
                    "name" => $_FILES[$a_field]["name"][$a_index][$a_sub_index],
                    "type" => $_FILES[$a_field]["type"][$a_index][$a_sub_index],
                    "error" => $_FILES[$a_field]["error"][$a_index][$a_sub_index],
                    "size" => $_FILES[$a_field]["size"][$a_index][$a_sub_index],
                    "is_upload" => true
                );
            }
        } elseif ($a_sub_index) {
            if ($_FILES[$a_field]["tmp_name"][$a_index] ?? false) {
                $res = array(
                    "tmp_name" => $_FILES[$a_field]["tmp_name"][$a_index],
                    "name" => $_FILES[$a_field]["name"][$a_index],
                    "type" => $_FILES[$a_field]["type"][$a_index],
                    "error" => $_FILES[$a_field]["error"][$a_index],
                    "size" => $_FILES[$a_field]["size"][$a_index],
                    "is_upload" => true
                );
            }
        } else {
            if ($_FILES[$a_field]["tmp_name"] ?? false) {
                $res = array(
                    "tmp_name" => $_FILES[$a_field]["tmp_name"],
                    "name" => $_FILES[$a_field]["name"],
                    "type" => $_FILES[$a_field]["type"],
                    "error" => $_FILES[$a_field]["error"],
                    "size" => $_FILES[$a_field]["size"],
                    "is_upload" => true
                );
            }
        }
        return $res;
    }

    public function hasFileUpload(
        string $a_field,
        ?string $a_index = null,
        ?string $a_sub_index = null
    ): bool {
        $data = $this->getFileUpload($a_field, $a_index, $a_sub_index);
        return (bool) ($data["tmp_name"] ?? false);
    }

    /**
     * Move upload to target directory
     *
     * @param string $a_target_directory target directory (without filename!)
     * @param string $a_field form field
     * @param ?string $a_target_name target file name (if different from uploaded file)
     * @param ?string $a_index form field index (if array)
     * @param ?string $a_sub_index form field subindex (if array)
     * @return string target file name incl. path
     * @throws ilException
     */
    public function moveFileUpload(
        string $a_target_directory,
        string $a_field,
        ?string $a_target_name = null,
        ?string $a_index = null,
        ?string $a_sub_index = null
    ): string {
        if (!is_dir($a_target_directory)) {
            return "";
        }

        $data = $this->getFileUpload($a_field, $a_index, $a_sub_index);
        if ($data["tmp_name"] && file_exists($data["tmp_name"])) {
            if ($a_target_name) {
                $data["name"] = $a_target_name;
            }

            $target_file = $a_target_directory . "/" . $data["name"];
            $target_file = str_replace("//", "/", $target_file);

            if ($data["is_upload"]) {
                if (!ilFileUtils::moveUploadedFile($data["tmp_name"], $data["name"], $target_file)) {
                    return "";
                }
            } else {
                if (!rename($data["tmp_name"], $target_file)) {
                    return "";
                }
            }

            return $target_file;
        }
        return "";
    }

    protected function rebuildUploadedFiles(): void
    {
        $file_hash = (string) $this->getFileHash();
        if ($file_hash != "") {
            $temp_path = ilFileUtils::getDataDir() . "/temp";
            if (is_dir($temp_path)) {
                $temp_files = glob($temp_path . "/" . session_id() . "~~" . $file_hash . "~~*");
                if (is_array($temp_files)) {
                    foreach ($temp_files as $full_file) {
                        $file = explode("~~", basename($full_file));
                        $field = $file[2];
                        $idx = $file[3];
                        $idx2 = $file[4];
                        $type = $file[5] . "/" . $file[6];
                        $name = $file[7];

                        if ($idx2 != "") {
                            if (!$_FILES[$field]["tmp_name"][$idx][$idx2]) {
                                $_FILES[$field]["tmp_name"][$idx][$idx2] = $full_file;
                                $_FILES[$field]["name"][$idx][$idx2] = $name;
                                $_FILES[$field]["type"][$idx][$idx2] = $type;
                                $_FILES[$field]["error"][$idx][$idx2] = 0;
                                $_FILES[$field]["size"][$idx][$idx2] = filesize($full_file);
                            }
                        } elseif ($idx != "") {
                            if (!$_FILES[$field]["tmp_name"][$idx]) {
                                $_FILES[$field]["tmp_name"][$idx] = $full_file;
                                $_FILES[$field]["name"][$idx] = $name;
                                $_FILES[$field]["type"][$idx] = $type;
                                $_FILES[$field]["error"][$idx] = 0;
                                $_FILES[$field]["size"][$idx] = filesize($full_file);
                            }
                        } else {
                            if (!$_FILES[$field]["tmp_name"]) {
                                $_FILES[$field]["tmp_name"] = $full_file;
                                $_FILES[$field]["name"] = $name;
                                $_FILES[$field]["type"] = $type;
                                $_FILES[$field]["error"] = 0;
                                $_FILES[$field]["size"] = filesize($full_file);
                            }
                        }
                    }
                }
            }
        }
    }
}
