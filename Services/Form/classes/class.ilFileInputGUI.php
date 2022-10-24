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

/**
 * This class represents a file property in a property form.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilFileInputGUI extends ilSubEnabledFormPropertyGUI implements ilToolbarItem
{
    private string $filename = "";
    private string $filename_post = "";
    protected int $size = 40;
    protected string $pending = "";
    protected bool $allow_deletion = false;
    protected bool $filename_selection = false;
    protected array $forbidden_suffixes = [];
    protected array $suffixes = [];
    protected string $value = "";

    public function __construct(
        string $a_title = "",
        string $a_postvar = ""
    ) {
        global $DIC;

        $this->lng = $DIC->language();
        $lng = $DIC->language();

        parent::__construct($a_title, $a_postvar);
        $this->setType("file");
        $this->setHiddenTitle("(" . $lng->txt("form_file_input") . ")");
    }

    public function setValueByArray(array $a_values): void
    {
        $value = $a_values[$this->getPostVar()] ?? null;
        if (!is_array($value)) {
            $this->setValue((string) $value);
        }
        $filenam = $a_values[$this->getFileNamePostVar()] ?? '';
        $this->setFilename($filenam);
    }

    /**
     * Set Value. (used for displaying file title of existing file below input field)
     */
    public function setValue(string $a_value): void
    {
        $this->value = $a_value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setSize(int $a_size): void
    {
        $this->size = $a_size;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    // Set filename value (if filename selection is enabled)
    public function setFilename(string $a_val): void
    {
        $this->filename = $a_val;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function setSuffixes(array $a_suffixes): void
    {
        $this->suffixes = $a_suffixes;
    }

    public function getSuffixes(): array
    {
        return $this->suffixes;
    }

    public function setForbiddenSuffixes(array $a_suffixes): void
    {
        $this->forbidden_suffixes = $a_suffixes;
    }

    public function getForbiddenSuffixes(): array
    {
        return $this->forbidden_suffixes;
    }

    // Set pending filename value
    public function setPending(string $a_val): void
    {
        $this->pending = $a_val;
    }

    public function getPending(): string
    {
        return $this->pending;
    }

    // If enabled, users get the possibility to enter a filename for the uploaded file
    public function enableFileNameSelection(string $a_post_var): void
    {
        $this->filename_selection = true;
        $this->filename_post = $a_post_var;
    }

    public function isFileNameSelectionEnabled(): bool
    {
        return $this->filename_selection;
    }

    public function getFileNamePostVar(): string
    {
        return $this->filename_post;
    }

    public function setALlowDeletion(bool $a_val): void
    {
        $this->allow_deletion = $a_val;
    }

    public function getALlowDeletion(): bool
    {
        return $this->allow_deletion;
    }

    public function checkInput(): bool
    {
        $lng = $this->lng;

        // #18756
        if ($this->getDisabled()) {
            return true;
        }

        // if no information is received, something went wrong
        // this is e.g. the case, if the post_max_size has been exceeded
        if (!isset($_FILES[$this->getPostVar()]) || !is_array($_FILES[$this->getPostVar()])) {
            $this->setAlert($lng->txt("form_msg_file_size_exceeds"));
            return false;
        }

        $_FILES[$this->getPostVar()]["name"] = ilUtil::stripSlashes($_FILES[$this->getPostVar()]["name"]);

        $_FILES[$this->getPostVar()]["name"] = utf8_encode($_FILES[$this->getPostVar()]["name"]);

        // remove trailing '/'
        $_FILES[$this->getPostVar()]["name"] = rtrim($_FILES[$this->getPostVar()]["name"], '/');

        $filename = $_FILES[$this->getPostVar()]["name"];
        $filename_arr = pathinfo($_FILES[$this->getPostVar()]["name"]);
        $suffix = $filename_arr["extension"] ?? '';
        $temp_name = $_FILES[$this->getPostVar()]["tmp_name"];
        $error = $_FILES[$this->getPostVar()]["error"];

        // error handling
        if ($error > 0) {
            switch ($error) {
                case UPLOAD_ERR_FORM_SIZE:
                case UPLOAD_ERR_INI_SIZE:
                    $this->setAlert($lng->txt("form_msg_file_size_exceeds"));
                    return false;

                case UPLOAD_ERR_PARTIAL:
                    $this->setAlert($lng->txt("form_msg_file_partially_uploaded"));
                    return false;

                case UPLOAD_ERR_NO_FILE:
                    if ($this->getRequired()) {
                        if (!strlen($this->getValue())) {
                            $this->setAlert($lng->txt("form_msg_file_no_upload"));
                            return false;
                        }
                    }
                    break;

                case UPLOAD_ERR_NO_TMP_DIR:
                    $this->setAlert($lng->txt("form_msg_file_missing_tmp_dir"));
                    return false;

                case UPLOAD_ERR_CANT_WRITE:
                    $this->setAlert($lng->txt("form_msg_file_cannot_write_to_disk"));
                    return false;

                case UPLOAD_ERR_EXTENSION:
                    $this->setAlert($lng->txt("form_msg_file_upload_stopped_ext"));
                    return false;
            }
        }

        // check suffixes
        if ($_FILES[$this->getPostVar()]["tmp_name"] != "") {
            if (is_array($this->forbidden_suffixes) && in_array(strtolower($suffix), $this->forbidden_suffixes)) {
                $this->setAlert($lng->txt("form_msg_file_type_is_not_allowed") . " (" . $suffix . ")");
                return false;
            }
            if (is_array($this->getSuffixes()) && count($this->getSuffixes()) > 0) {
                if (!in_array(strtolower($suffix), $this->getSuffixes())) {
                    $this->setAlert($lng->txt("form_msg_file_wrong_file_type"));
                    return false;
                }
            }
        }

        // virus handling
        if ($_FILES[$this->getPostVar()]["tmp_name"] != "") {
            $vir = ilVirusScanner::virusHandling($temp_name, $filename);
            if ($vir[0] == false) {
                $this->setAlert($lng->txt("form_msg_file_virus_found") . "<br />" . $vir[1]);
                return false;
            }
        }

        $file_name = $this->str('file_name');
        if ($file_name === "") {
            $file_name = $_FILES[$this->getPostVar()]["name"];
        }
        $this->setFilename($file_name);

        return true;
    }

    public function getInput(): array
    {
        return $_FILES[$this->getPostVar()];
    }

    public function render(string $a_mode = ""): string
    {
        $lng = $this->lng;

        $quota_exceeded = $quota_legend = false;

        $f_tpl = new ilTemplate("tpl.prop_file.html", true, true, "Services/Form");


        // show filename selection if enabled
        if ($this->isFileNameSelectionEnabled()) {
            $f_tpl->setCurrentBlock('filename');
            $f_tpl->setVariable('POST_FILENAME', $this->getFileNamePostVar());
            $f_tpl->setVariable('VAL_FILENAME', $this->getFilename());
            $f_tpl->setVariable('FILENAME_ID', $this->getFieldId());
            $f_tpl->setVariable('TXT_FILENAME_HINT', $lng->txt('if_no_title_then_filename'));
            $f_tpl->parseCurrentBlock();
        } else {
            if (trim($this->getValue()) != "") {
                if (!$this->getDisabled() && $this->getALlowDeletion()) {
                    $f_tpl->setCurrentBlock("delete_bl");
                    $f_tpl->setVariable("POST_VAR_D", $this->getPostVar());
                    $f_tpl->setVariable(
                        "TXT_DELETE_EXISTING",
                        $lng->txt("delete_existing_file")
                    );
                    $f_tpl->parseCurrentBlock();
                }

                $f_tpl->setCurrentBlock('prop_file_propval');
                $f_tpl->setVariable('FILE_VAL', $this->getValue());
                $f_tpl->parseCurrentBlock();
            }
        }

        if ($a_mode != "toolbar") {
            if (!$quota_exceeded) {
                $this->outputSuffixes($f_tpl);

                $f_tpl->setCurrentBlock("max_size");
                $f_tpl->setVariable("TXT_MAX_SIZE", $lng->txt("file_notice") . " " .
                    $this->getMaxFileSizeString());
                $f_tpl->parseCurrentBlock();

                if ($quota_legend) {
                    $f_tpl->setVariable("TXT_MAX_SIZE", true);
                    $f_tpl->parseCurrentBlock();
                }
            } else {
                $f_tpl->setCurrentBlock("max_size");
                $f_tpl->setVariable("TXT_MAX_SIZE", $quota_exceeded);
                $f_tpl->parseCurrentBlock();
            }
        } elseif ($quota_exceeded) {
            return $quota_exceeded;
        }

        $pending = $this->getPending();
        if ($pending) {
            $f_tpl->setCurrentBlock("pending");
            $f_tpl->setVariable("TXT_PENDING", $lng->txt("file_upload_pending") .
                ": " . htmlentities($pending));
            $f_tpl->parseCurrentBlock();
        }

        if ($this->getDisabled() || $quota_exceeded) {
            $f_tpl->setVariable(
                "DISABLED",
                " disabled=\"disabled\""
            );
        }

        $f_tpl->setVariable("POST_VAR", $this->getPostVar());
        $f_tpl->setVariable("ID", $this->getFieldId());
        $f_tpl->setVariable("SIZE", $this->getSize());


        /* experimental: bootstrap'ed file upload */
        $f_tpl->setVariable("TXT_BROWSE", $lng->txt("select_file"));


        return $f_tpl->get();
    }

    public function insert(ilTemplate $a_tpl): void
    {
        $html = $this->render();

        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $html);
        $a_tpl->parseCurrentBlock();
    }


    protected function outputSuffixes(
        ilTemplate $a_tpl,
        string $a_block = "allowed_suffixes"
    ): void {
        $lng = $this->lng;

        if (is_array($this->getSuffixes()) && count($this->getSuffixes()) > 0) {
            $suff_str = $delim = "";
            foreach ($this->getSuffixes() as $suffix) {
                $suff_str .= $delim . "." . $suffix;
                $delim = ", ";
            }
            $a_tpl->setCurrentBlock($a_block);
            $a_tpl->setVariable(
                "TXT_ALLOWED_SUFFIXES",
                $lng->txt("file_allowed_suffixes") . " " . $suff_str
            );
            $a_tpl->parseCurrentBlock();
        }
    }

    protected function getMaxFileSizeString(): string
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

        //format for display in mega-bytes
        $max_filesize = sprintf("%.1f MB", $max_filesize / 1024 / 1024);

        return $max_filesize;
    }

    /**
     * Get number of maximum file uploads as declared in php.ini
     */
    protected function getMaxFileUploads(): int
    {
        return (int) ini_get("max_file_uploads");
    }

    public function getDeletionFlag(): bool
    {
        if ($this->int($this->getPostVar() . "_delete")) {
            return true;
        }
        return false;
    }

    public function getToolbarHTML(): string
    {
        $html = $this->render("toolbar");
        return $html;
    }
}
