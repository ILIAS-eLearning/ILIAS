<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * @author Stefan Kesseler <skesseler@databay.de>
 * @author Sascha Hofmann <shofmann@databay.de>
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface ilGlobalTemplateInterface
{
    public const MESSAGE_TYPE_FAILURE = 'failure';
    public const MESSAGE_TYPE_SUCCESS = "success";
    public const MESSAGE_TYPE_QUESTION = "question";
    public const MESSAGE_TYPE_INFO = "info";

    /**
     * @var array available types for messages.
     */
    public const MESSAGE_TYPES = [
        self::MESSAGE_TYPE_FAILURE,
        self::MESSAGE_TYPE_INFO,
        self::MESSAGE_TYPE_SUCCESS,
        self::MESSAGE_TYPE_QUESTION,
    ];

    /**
     * @var string default block for several operations.
     */
    public const DEFAULT_BLOCK = 'DEFAULT';

    /**
     * Make the template hide the footer.
     */
    public function hideFooter() : void;

    /**
     * Set a message to be displayed to the user. Please use ilUtil::sendInfo(),
     * ilUtil::sendSuccess() and ilUtil::sendFailure().
     */
    public function setOnScreenMessage(string $type, string $a_txt, bool $a_keep = false) : void;

    /**
     * Add a javascript file that should be included in the header.
     */
    public function addJavaScript(string $a_js_file, bool $a_add_version_parameter = true, int $a_batch = 2) : void;

    /**
     * Add on load code
     */
    public function addOnLoadCode(string $a_code, int $a_batch = 2) : void;

    /**
     * Get js onload code for ajax calls
     */
    public function getOnLoadCodeForAsynch() : string;

    /**
     * Reset javascript files
     */
    public function resetJavascript() : void;

    /**
     * Probably adds javascript files.
     */
    public function fillJavaScriptFiles(bool $a_force = false) : void;

    /**
     * Add a css file that should be included in the header.
     */
    public function addCss(string $a_css_file, string $media = "screen") : void;

    /**
     * Add a css file that should be included in the header.
     */
    public function addInlineCss(string $a_css, string $media = "screen") : void;

    /**
     * Sets the body-tags class.
     */
    public function setBodyClass(string $a_class = "") : void;

    /**
     * This loads the standard template "tpl.adm_content.html" and
     * "tpl.statusline.html" the CONTENT and STATUSLINE placeholders
     * if they are not already loaded.
     */
    public function loadStandardTemplate() : void;

    /**
     * Sets title in standard template.
     * Will override the header_page_title.
     */
    public function setTitle(string $a_title, bool $hidden = false) : void;

    /**
     * Sets description below title in standard template.
     */
    public function setDescription(string $a_descr) : void;

    /**
     * set title icon
     */
    public function setTitleIcon(string $a_icon_path, string $a_icon_desc = "") : void;

    /**
     * Set alert properties
     * @param array<int, array> $alerts
     */
    public function setAlertProperties(array $alerts) : void;

    /**
     * Clear header
     */
    public function clearHeader() : void;

    /**
     * Set header action menu
     */
    public function setHeaderActionMenu(string $a_header) : void;

    /**
     * Sets the title of the page (for browser window).
     */
    public function setHeaderPageTitle(string $a_title) : void;

    /**
     * Insert locator.
     */
    public function setLocator() : void;

    /**
     * sets tabs in standard template
     */
    public function setTabs(string $a_tabs_html) : void;

    /**
     * sets subtabs in standard template
     */
    public function setSubTabs(string $a_tabs_html) : void;

    /**
     * Sets content for standard template.
     */
    public function setContent(string $a_html) : void;

    /**
     * Sets content of left column.
     */
    public function setLeftContent(string $a_html) : void;

    /**
     * Sets content of left navigation column.
     */
    public function setLeftNavContent(string $a_content) : void;

    /**
     * Sets content of right column.
     */
    public function setRightContent(string $a_html) : void;

    /**
     * Sets the pages form action.
     */
    public function setPageFormAction(string $a_action) : void;

    /**
     * Set target parameter for login (public sector).
     * This is used by the main menu
     */
    public function setLoginTargetPar(string $a_val) : void;

    /**
     * Renders the page with specific elements enabled.
     */
    public function getSpecial(
        string $part = self::DEFAULT_BLOCK,
        bool $add_error_mess = false,
        bool $handle_referer = false,
        bool $add_ilias_footer = false,
        bool $add_standard_elements = false,
        bool $a_main_menu = true,
        bool $a_tabs = true
    ) : string;

    /**
     * @param bool $has_tabs       if template variable {TABS} should be filled with content of ilTabs
     * @param bool $skip_main_menu if the main menu should be rendered.
     */
    public function printToStdout(
        string $part = self::DEFAULT_BLOCK,
        bool $has_tabs = true,
        bool $skip_main_menu = false
    ) : void;

    /**
     * Use this method to get the finally rendered page as string
     */
    public function printToString() : string;

    /**
     * Sets a tree or flat icon.
     * @param string $a_mode ("tree" | "flat")
     */
    public function setTreeFlatIcon(string $a_link, string $a_mode) : void;

    /**
     * Add a lightbox html to the template.
     */
    public function addLightbox(string $a_html, string $a_id) : void;

    /**
     * Add admin panel commands as toolbar
     * @param bool $is_bottom_panel if the panel should be rendered at the bottom of the page as well.
     * @param bool $has_arrow       if the panel should be rendered with an arrow icon.
     */
    public function addAdminPanelToolbar(
        ilToolbarGUI $toolbar,
        bool $is_bottom_panel = true,
        bool $has_arrow = false
    ) : void;

    /**
     * Generates and sets a permanent ilias link.
     */
    public function setPermanentLink(
        string $a_type,
        ?int $a_id,
        string $a_append = "",
        string $a_target = "",
        string $a_title = ""
    ) : void;

    /**
     * Reset all header properties: title, icon, description, alerts, action menu
     */
    public function resetHeaderBlock(bool $a_reset_header_action = true) : void;

    /**
     * Enables the file upload into this object by dropping a file.
     */
    public function setFileUploadRefId(int $a_ref_id) : void;

    /**
     * Renders the given block and returns the html string.
     */
    public function get(string $part = self::DEFAULT_BLOCK) : string;

    /**
     * Sets the given variable to the given value.
     * @param mixed $value
     */
    public function setVariable(string $variable, $value = '') : void;

    /**
     * Sets the template to the given block.
     */
    public function setCurrentBlock(string $part = self::DEFAULT_BLOCK) : bool;

    /**
     * Parses the given block.
     */
    public function parseCurrentBlock(string $block_name = self::DEFAULT_BLOCK) : bool;

    /**
     * overwrites ITX::touchBlock.
     */
    public function touchBlock(string $block) : bool;

    /**
     * overwrites ITX::addBlockFile
     */
    public function addBlockFile(string $var, string $block, string $template_name, string $in_module = null) : bool;

    /**
     * check if block exists in actual template
     * @param string $block_name
     */
    public function blockExists(string $block_name) : bool;
}
