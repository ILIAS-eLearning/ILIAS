<?php
/**
 * Created by PhpStorm.
 * User: fschmid
 * Date: 2019-03-18
 * Time: 11:22
 */

/**
 * special template class to simplify handling of ITX/PEAR
 *
 * @author     Stefan Kesseler <skesseler@databay.de>
 * @author     Sascha Hofmann <shofmann@databay.de>
 * @version    $Id$
 */
interface ilGlobalTemplateInterface
{

    /**
     * Make the template hide the footer.
     */
    public function hideFooter();


    /**
     * Set a message to be displayed to the user. Please use ilUtil::sendInfo(),
     * ilUtil::sendSuccess() and ilUtil::sendFailure()
     *
     * @param string $a_type   \ilTemplate::MESSAGE_TYPE_SUCCESS,
     *                         \ilTemplate::MESSAGE_TYPE_FAILURE,,
     *                         \ilTemplate::MESSAGE_TYPE_QUESTION,
     *                         \ilTemplate::MESSAGE_TYPE_INFO
     * @param string $a_txt    The message to be sent
     * @param bool   $a_keep   Keep this message over one redirect
     */
    public function setOnScreenMessage($a_type, $a_txt, $a_keep = false);


    /**
     * Add a javascript file that should be included in the header.
     */
    public function addJavaScript($a_js_file, $a_add_version_parameter = true, $a_batch = 2);


    /**
     * Add on load code
     */
    public function addOnLoadCode($a_code, $a_batch = 2);


    /**
     * Get js onload code for ajax calls
     *
     * @return string
     */
    public function getOnLoadCodeForAsynch();


    /**
     * Reset javascript files
     */
    public function resetJavascript();


    public function fillJavaScriptFiles($a_force = false);


    /**
     * Add a css file that should be included in the header.
     */
    public function addCss($a_css_file, $media = "screen");


    /**
     * Add a css file that should be included in the header.
     */
    public function addInlineCss($a_css, $media = "screen");


    public function setBodyClass($a_class = "");


    /**
     * This loads the standard template "tpl.adm_content.html" and
     * "tpl.statusline.html" the CONTENT and STATUSLINE placeholders
     * if they are not already loaded.
     */
    public function loadStandardTemplate();


    /**
     * Sets title in standard template.
     *
     * Will override the header_page_title.
     */
    public function setTitle($a_title, $hidden = false);


    /**
     * Sets descripton below title in standard template.
     */
    public function setDescription($a_descr);


    /**
     * set title icon
     */
    public function setTitleIcon($a_icon_path, $a_icon_desc = "");


    /**
     * Set alert properties
     *
     * @param array $a_props
     *
     * @return void
     */
    public function setAlertProperties(array $a_props);


    /**
     * Clear header
     */
    public function clearHeader();


    /**
     * Set header action menu
     *
     * @param string $a_gui $a_header
     */
    public function setHeaderActionMenu($a_header);


    /**
     * Sets the title of the page (for browser window).
     */
    public function setHeaderPageTitle($a_title);


    /**
     * Insert locator.
     */
    public function setLocator();


    /**
     * sets tabs in standard template
     */
    public function setTabs($a_tabs_html);


    /**
     * sets subtabs in standard template
     */
    public function setSubTabs($a_tabs_html);


    /**
     * Sets content for standard template.
     */
    public function setContent($a_html);


    /**
     * Sets content of left column.
     */
    public function setLeftContent($a_html);


    /**
     * Sets content of left navigation column.
     */
    public function setLeftNavContent($a_content);


    /**
     * Sets content of right column.
     */
    public function setRightContent($a_html);


    public function setPageFormAction($a_action);


    /**
     * Set target parameter for login (public sector).
     * This is used by the main menu
     */
    public function setLoginTargetPar($a_val);


    /**
     * @param string
     *
     * @return    string
     */
    public function getSpecial($part = "DEFAULT", $add_error_mess = false, $handle_referer = false, $add_ilias_footer = false, $add_standard_elements = false, $a_main_menu = true, $a_tabs = true);


    /**
     * @param string|bool $part
     * @param bool        $a_fill_tabs fill template variable {TABS} with content of ilTabs
     * @param bool        $a_skip_main_menu
     */
    public function printToStdout($part = "DEFAULT", $a_fill_tabs = true, $a_skip_main_menu = false);


    /**
     * Use this method to get the finally rendered page as string
     *
     * @return string
     */
    public function printToString() : string;


    /**
     * set tree/flat icon
     *
     * @param string        link target
     * @param strong        mode ("tree" | "flat")
     */
    public function setTreeFlatIcon($a_link, $a_mode);


    /**
     * Add lightbox html
     */
    public function addLightbox($a_html, $a_id);


    /**
     * Add admin panel commands as toolbar
     *
     * @param ilToolbarGUI $toolb
     * @param bool         $a_top_only
     */
    public function addAdminPanelToolbar(ilToolbarGUI $toolb, $a_bottom_panel = true, $a_arrow = false);


    public function setPermanentLink($a_type, $a_id, $a_append = "", $a_target = "", $a_title = "");


    /**
     * Reset all header properties: title, icon, description, alerts, action menu
     */
    public function resetHeaderBlock($a_reset_header_action = true);


    /**
     * Enables the file upload into this object by dropping a file.
     */
    public function enableDragDropFileUpload($a_ref_id);


    /**
     * @param string
     *
     * @return    string
     */
    public function get($part = "DEFAULT");


    public function setVariable($variable, $value = '');


    /**
     * @access    public
     *
     * @param string
     *
     * @return    ???
     */
    public function setCurrentBlock($part = "DEFAULT");


    /**
     * overwrites ITX::touchBlock.
     *
     * @access    public
     *
     * @param string
     *
     * @return    ???
     */
    public function touchBlock($block);


    /**
     * Überladene Funktion, die auf den aktuelle Block vorher noch ein replace ausführt
     *
     * @access    public
     *
     * @param string
     *
     * @return    string
     */
    public function parseCurrentBlock($part = "DEFAULT");


    /**
     * overwrites ITX::addBlockFile
     *
     * @access    public
     *
     * @param string
     * @param string
     * @param string  $tplname   template name
     * @param boolean $in_module should be set to true, if template file is in module subdirectory
     *
     * @return    boolean/string
     */
    public function addBlockFile($var, $block, $tplname, $in_module = false);


    /**
     * check if block exists in actual template
     *
     * @access    private
     *
     * @param string blockname
     *
     * @return    boolean
     */
    public function blockExists($a_blockname);
}
