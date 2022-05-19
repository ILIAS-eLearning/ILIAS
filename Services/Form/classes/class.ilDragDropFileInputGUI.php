<?php declare(strict_types=1);

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
 * This class represents a file input property where multiple files can be dopped in a property form.
 *
 * @author Stefan Born <stefan.born@phzh.ch>
 */
class ilDragDropFileInputGUI extends ilFileInputGUI
{
    private int $uniqueId;
    private array $archive_suffixes = array();
    private ?string $submit_button_name = null;
    private ?string $cancel_button_name = null;
    private static int $uniqueInc = 1;

    public function __construct(
        string $a_title = "",
        string $a_postvar = ""
    ) {
        global $DIC;

        $this->lng = $DIC->language();
        parent::__construct($a_title, $a_postvar);
        $this->uniqueId = self::getNextUniqueId();
    }

    private static function getNextUniqueId() : int
    {
        return self::$uniqueInc++;
    }

    // Set accepted archive suffixes.
    public function setArchiveSuffixes(array $a_suffixes) : void
    {
        $this->archive_suffixes = $a_suffixes;
    }

    public function getArchiveSuffixes() : array
    {
        return $this->archive_suffixes;
    }
    
    public function setCommandButtonNames(
        string $a_submit_name,
        string $a_cancel_name
    ) : void {
        $this->submit_button_name = $a_submit_name;
        $this->cancel_button_name = $a_cancel_name;
    }
    
    public function render($a_mode = "") : string
    {
        $lng = $this->lng;

        // make sure jQuery is loaded
        iljQueryUtil::initjQuery();
        
        // add file upload scripts
        ilFileUploadGUI::initFileUpload();
        
        // load template
        $tpl = new ilTemplate("tpl.prop_dndfiles.html", true, true, "Services/Form");
        
        // general variables
        $tpl->setVariable("UPLOAD_ID", $this->uniqueId);
        
        // input
        $tpl->setVariable("FILE_SELECT_ICON", ilObject::_getIcon(0, "", "fold"));
        $tpl->setVariable("TXT_SHOW_ALL_DETAILS", $lng->txt('show_all_details'));
        $tpl->setVariable("TXT_HIDE_ALL_DETAILS", $lng->txt('hide_all_details'));
        $tpl->setVariable("TXT_SELECTED_FILES", $lng->txt('selected_files'));
        $tpl->setVariable("TXT_DRAG_FILES_HERE", $lng->txt('drag_files_here'));
        $tpl->setVariable("TXT_NUM_OF_SELECTED_FILES", $lng->txt('num_of_selected_files'));
        $tpl->setVariable("TXT_SELECT_FILES_FROM_COMPUTER", $lng->txt('select_files_from_computer'));
        $tpl->setVariable("TXT_OR", $lng->txt('logic_or'));
        $tpl->setVariable("INPUT_ACCEPT_SUFFIXES", $this->getInputAcceptSuffixes($this->getSuffixes()));

        // info
        $tpl->setCurrentBlock("max_size");
        $tpl->setVariable("TXT_MAX_SIZE", $lng->txt("file_notice") . " " . $this->getMaxFileSizeString());
        $tpl->parseCurrentBlock();
        
        $this->outputSuffixes($tpl);
        
        // create file upload object
        $upload = new ilFileUploadGUI("ilFileUploadDropZone_" . $this->uniqueId, $this->uniqueId, false);
        $upload->enableFormSubmit("ilFileUploadInput_" . $this->uniqueId, $this->submit_button_name, $this->cancel_button_name);
        $upload->setDropAreaId("ilFileUploadDropArea_" . $this->uniqueId);
        $upload->setFileListId("ilFileUploadList_" . $this->uniqueId);
        $upload->setFileSelectButtonId("ilFileUploadFileSelect_" . $this->uniqueId);
        
        $tpl->setVariable("FILE_UPLOAD", $upload->getHTML());
        
        return $tpl->get();
    }
    
    public function checkInput() : bool
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
        return parent::checkInput();
    }

    public function getInput() : array
    {
        $val = $this->strArray($this->getPostVar());
        $val["extract"] = (bool) $val["extract"];
        $val["keep_structure"] = (bool) $val["keep_structure"];
        $val["name"] = utf8_encode($val["name"]);
        $val["title"] = utf8_encode($val["title"]);
        return $val;
    }
    
    protected function getInputAcceptSuffixes(?array $suffixes) : string
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
    
    protected function buildSuffixList(?array $suffixes) : string
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
    
    protected function getMaxFileSize() : int
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
