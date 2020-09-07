<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Form/classes/class.ilFileInputGUI.php");

/**
* This class represents a file input property where multiple files can be dopped in a property form.
*
* @author Stefan Born <stefan.born@phzh.ch>
* @version $Id$
* @ingroup	ServicesForm
*/
class ilDragDropFileInputGUI extends ilFileInputGUI
{
    /**
     * @var ilLanguage
     */
    protected $lng;

    private $uniqueId = 0;
    private $archive_suffixes = array();
    private $submit_button_name = null;
    private $cancel_button_name = null;
    
    private static $uniqueInc = 1;
    
    private static function getNextUniqueId()
    {
        return self::$uniqueInc++;
    }
    
    /**
     * Constructor
     *
     * @param	string	$a_title	Title
     * @param	string	$a_postvar	Post Variable
     */
    public function __construct($a_title = "", $a_postvar = "")
    {
        global $DIC;

        $this->lng = $DIC->language();
        parent::__construct($a_title, $a_postvar);
        $this->uniqueId = self::getNextUniqueId();
    }
    
    /**
    * Set accepted archive suffixes.
    *
    * @param	array	$a_suffixes	Accepted archive suffixes.
    */
    public function setArchiveSuffixes($a_suffixes)
    {
        $this->archive_suffixes = $a_suffixes;
    }

    /**
    * Get accepted archive suffixes.
    *
    * @return	array	Accepted archive suffixes.
    */
    public function getArchiveSuffixes()
    {
        return $this->archive_suffixes;
    }
    
    public function setCommandButtonNames($a_submit_name, $a_cancel_name)
    {
        $this->submit_button_name = $a_submit_name;
        $this->cancel_button_name = $a_cancel_name;
    }
    
    /**
     * Render html
     */
    public function render($a_mode = "")
    {
        $lng = $this->lng;

        $quota_exceeded = $quota_legend = false;
        if (self::$check_wsp_quota) {
            include_once "Services/DiskQuota/classes/class.ilDiskQuotaHandler.php";
            if (!ilDiskQuotaHandler::isUploadPossible()) {
                $lng->loadLanguageModule("file");
                return $lng->txt("personal_workspace_quota_exceeded_warning");
            } else {
                $quota_legend = ilDiskQuotaHandler::getStatusLegend();
            }
        }

        // make sure jQuery is loaded
        iljQueryUtil::initjQuery();
        
        // add file upload scripts
        include_once("./Services/FileUpload/classes/class.ilFileUploadGUI.php");
        ilFileUploadGUI::initFileUpload();
        
        // load template
        $this->tpl = new ilTemplate("tpl.prop_dndfiles.html", true, true, "Services/Form");
        
        // general variables
        $this->tpl->setVariable("UPLOAD_ID", $this->uniqueId);
        
        // input
        $this->tpl->setVariable("FILE_SELECT_ICON", ilObject::_getIcon("", "", "fold"));
        $this->tpl->setVariable("TXT_SHOW_ALL_DETAILS", $lng->txt('show_all_details'));
        $this->tpl->setVariable("TXT_HIDE_ALL_DETAILS", $lng->txt('hide_all_details'));
        $this->tpl->setVariable("TXT_SELECTED_FILES", $lng->txt('selected_files'));
        $this->tpl->setVariable("TXT_DRAG_FILES_HERE", $lng->txt('drag_files_here'));
        $this->tpl->setVariable("TXT_NUM_OF_SELECTED_FILES", $lng->txt('num_of_selected_files'));
        $this->tpl->setVariable("TXT_SELECT_FILES_FROM_COMPUTER", $lng->txt('select_files_from_computer'));
        $this->tpl->setVariable("TXT_OR", $lng->txt('logic_or'));
        $this->tpl->setVariable("INPUT_ACCEPT_SUFFIXES", $this->getInputAcceptSuffixes($this->getSuffixes()));

        // info
        $this->tpl->setCurrentBlock("max_size");
        $this->tpl->setVariable("TXT_MAX_SIZE", $lng->txt("file_notice") . " " . $this->getMaxFileSizeString());
        $this->tpl->parseCurrentBlock();
        
        if ($quota_legend) {
            $this->tpl->setVariable("TXT_MAX_SIZE", $quota_legend);
            $this->tpl->parseCurrentBlock();
        }
        
        $this->outputSuffixes($this->tpl);
        
        // create file upload object
        $upload = new ilFileUploadGUI("ilFileUploadDropZone_" . $this->uniqueId, $this->uniqueId, false);
        $upload->enableFormSubmit("ilFileUploadInput_" . $this->uniqueId, $this->submit_button_name, $this->cancel_button_name);
        $upload->setDropAreaId("ilFileUploadDropArea_" . $this->uniqueId);
        $upload->setFileListId("ilFileUploadList_" . $this->uniqueId);
        $upload->setFileSelectButtonId("ilFileUploadFileSelect_" . $this->uniqueId);
        
        $this->tpl->setVariable("FILE_UPLOAD", $upload->getHTML());
        
        return $this->tpl->get();
    }
    
    /**
     * Check input, strip slashes etc. set alert, if input is not ok.
     *
     * @return	boolean		Input ok, true/false
     */
    public function checkInput()
    {
        $lng = $this->lng;
        
        // if no information is received, something went wrong
        // this is e.g. the case, if the post_max_size has been exceeded
        if (!is_array($_FILES[$this->getPostVar()])) {
            $this->setAlert($lng->txt("form_msg_file_size_exceeds"));
            return false;
        }
        
        // empty file, could be a folder
        if ($_FILES[$this->getPostVar()]["size"] < 1) {
            $this->setAlert($lng->txt("error_upload_was_zero_bytes"));
            return false;
        }

        // call base
        $inputValid = parent::checkInput();
        
        // set additionally sent input on post array
        if ($inputValid) {
            $_POST[$this->getPostVar()]["extract"] = isset($_POST["extract"]) ? (bool) $_POST["extract"] : false;
            $_POST[$this->getPostVar()]["title"] = isset($_POST["title"]) ? $_POST["title"] : "";
            $_POST[$this->getPostVar()]["description"] = isset($_POST["description"]) ? $_POST["description"] : "";
            $_POST[$this->getPostVar()]["keep_structure"] = isset($_POST["keep_structure"]) ? (bool) $_POST["keep_structure"] : true;

            include_once("./Services/Utilities/classes/class.ilStr.php");
            $_POST[$this->getPostVar()]["name"] = ilStr::normalizeUtf8String($_POST[$this->getPostVar()]["name"]);
            $_POST[$this->getPostVar()]["title"] = ilStr::normalizeUtf8String($_POST[$this->getPostVar()]["title"]);
        }
        
        return $inputValid;
    }
    
    protected function getInputAcceptSuffixes($suffixes)
    {
        $list = $delim = "";
        
        if (is_array($suffixes) && count($suffixes) > 0) {
            foreach ($suffixes as $suffix) {
                $list .= $delim . "." . $suffix;
                $delim = ",";
            }
        }
        
        return $list;
    }
    
    protected function buildSuffixList($suffixes)
    {
        $list = $delim = "";
        
        if (is_array($suffixes) && count($suffixes) > 0) {
            foreach ($suffixes as $suffix) {
                $list .= $delim . "\"" . $suffix . "\"";
                $delim = ", ";
            }
        }
        
        return $list;
    }
    
    protected function getMaxFileSize()
    {
        // get the value for the maximal uploadable filesize from the php.ini (if available)
        $umf = ini_get("upload_max_filesize");
        // get the value for the maximal post data from the php.ini (if available)
        $pms = ini_get("post_max_size");
    
        //convert from short-string representation to "real" bytes
        $multiplier_a = array("K" => 1024, "M" => 1024 * 1024, "G" => 1024 * 1024 * 1024);
    
        $umf_parts = preg_split("/(\d+)([K|G|M])/", $umf, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        $pms_parts = preg_split("/(\d+)([K|G|M])/", $pms, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
    
        if (count($umf_parts) == 2) {
            $umf = $umf_parts[0] * $multiplier_a[$umf_parts[1]];
        }
        if (count($pms_parts) == 2) {
            $pms = $pms_parts[0] * $multiplier_a[$pms_parts[1]];
        }
    
        // use the smaller one as limit
        $max_filesize = min($umf, $pms);
    
        if (!$max_filesize) {
            $max_filesize = max($umf, $pms);
        }
        
        return $max_filesize;
    }
}
