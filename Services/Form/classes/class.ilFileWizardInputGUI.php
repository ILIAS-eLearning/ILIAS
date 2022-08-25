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
 * This class represents a file wizard property in a property form.
 *
 * @author Helmut Schottmüller <ilias@aurealis.de>
 */
class ilFileWizardInputGUI extends ilFileInputGUI
{
    protected ilGlobalTemplateInterface $tpl;
    protected array $filenames = array();
    protected bool $allowMove = false;
    protected string $imagepath_web = "";

    public function __construct(
        string $a_title = "",
        string $a_postvar = ""
    ) {
        global $DIC;

        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule("form");
        $this->tpl = $DIC["tpl"];
        parent::__construct($a_title, $a_postvar);
    }

    public function setImagePathWeb(string $a_path): void
    {
        $this->imagepath_web = $a_path;
    }

    public function getImagePathWeb(): string
    {
        return $this->imagepath_web;
    }

    public function setFilenames(array $a_filenames): void
    {
        $this->filenames = $a_filenames;
    }

    public function getFilenames(): array
    {
        return $this->filenames;
    }

    public function setAllowMove(bool $a_allow_move): void
    {
        $this->allowMove = $a_allow_move;
    }

    public function getAllowMove(): bool
    {
        return $this->allowMove;
    }

    public function checkInput(): bool
    {
        $lng = $this->lng;

        // see ilFileInputGUI
        // if no information is received, something went wrong
        // this is e.g. the case, if the post_max_size has been exceeded
        if (!is_array($_FILES[$this->getPostVar()])) {
            $this->setAlert($lng->txt("form_msg_file_size_exceeds"));
            return false;
        }

        $pictures = $_FILES[$this->getPostVar()];
        $uploadcheck = true;
        if (is_array($pictures)) {
            foreach ($pictures['name'] as $index => $name) {
                // remove trailing '/'
                $name = rtrim($name, '/');

                $filename = $name;
                $filename_arr = pathinfo($name);
                $suffix = $filename_arr["extension"] ?? "";
                $temp_name = $pictures["tmp_name"][$index];
                $error = $pictures["error"][$index];

                $_FILES[$this->getPostVar()]["name"][$index] = utf8_encode($_FILES[$this->getPostVar()]["name"][$index]);


                // error handling
                if ($error > 0) {
                    switch ($error) {
                        case UPLOAD_ERR_FORM_SIZE:
                        case UPLOAD_ERR_INI_SIZE:
                            $this->setAlert($lng->txt("form_msg_file_size_exceeds"));
                            $uploadcheck = false;
                            break;

                        case UPLOAD_ERR_PARTIAL:
                            $this->setAlert($lng->txt("form_msg_file_partially_uploaded"));
                            $uploadcheck = false;
                            break;

                        case UPLOAD_ERR_NO_FILE:
                            if ($this->getRequired()) {
                                $filename = $this->filenames[$index];
                                if (!strlen($filename)) {
                                    $this->setAlert($lng->txt("form_msg_file_no_upload"));
                                    $uploadcheck = false;
                                }
                            }
                            break;

                        case UPLOAD_ERR_NO_TMP_DIR:
                            $this->setAlert($lng->txt("form_msg_file_missing_tmp_dir"));
                            $uploadcheck = false;
                            break;

                        case UPLOAD_ERR_CANT_WRITE:
                            $this->setAlert($lng->txt("form_msg_file_cannot_write_to_disk"));
                            $uploadcheck = false;
                            break;

                        case UPLOAD_ERR_EXTENSION:
                            $this->setAlert($lng->txt("form_msg_file_upload_stopped_ext"));
                            $uploadcheck = false;
                            break;
                    }
                }

                // check suffixes
                if ($pictures["tmp_name"][$index] != "" && is_array($this->getSuffixes()) && count($this->getSuffixes()) > 0) {
                    if (!in_array(strtolower($suffix), $this->getSuffixes())) {
                        $this->setAlert($lng->txt("form_msg_file_wrong_file_type"));
                        $uploadcheck = false;
                    }
                }

                // virus handling
                if ($pictures["tmp_name"][$index] != "") {
                    $vir = ilVirusScanner::virusHandling($temp_name, $filename);
                    if ($vir[0] == false) {
                        $this->setAlert($lng->txt("form_msg_file_virus_found") . "<br />" . $vir[1]);
                        $uploadcheck = false;
                    }
                }
            }
        }

        if (!$uploadcheck) {
            return false;
        }

        return $this->checkSubItemsInput();
    }

    public function insert(ilTemplate $a_tpl): void
    {
        $lng = $this->lng;

        $tpl = new ilTemplate("tpl.prop_filewizardinput.html", true, true, "Services/Form");

        $i = 0;
        foreach ($this->filenames as $value) {
            if (strlen($value)) {
                $tpl->setCurrentBlock("image");
                $tpl->setVariable(
                    "SRC_IMAGE",
                    $this->getImagePathWeb() . ilLegacyFormElementsUtil::prepareFormOutput(
                        $value
                    )
                );
                $tpl->setVariable("PICTURE_FILE", ilLegacyFormElementsUtil::prepareFormOutput($value));
                $tpl->setVariable("ID", $this->getFieldId() . "[$i]");
                $tpl->setVariable("ALT_IMAGE", ilLegacyFormElementsUtil::prepareFormOutput($value));
                $tpl->parseCurrentBlock();
            }
            if ($this->getAllowMove()) {
                $tpl->setCurrentBlock("move");
                $tpl->setVariable("CMD_UP", "cmd[up" . $this->getFieldId() . "][$i]");
                $tpl->setVariable("CMD_DOWN", "cmd[down" . $this->getFieldId() . "][$i]");
                $tpl->setVariable("ID", $this->getFieldId() . "[$i]");
                $tpl->setVariable("UP_BUTTON", ilGlyphGUI::get(ilGlyphGUI::UP));
                $tpl->setVariable("DOWN_BUTTON", ilGlyphGUI::get(ilGlyphGUI::DOWN));
                $tpl->parseCurrentBlock();
            }

            $this->outputSuffixes($tpl, "allowed_image_suffixes");

            $tpl->setCurrentBlock("row");
            $tpl->setVariable("POST_VAR", $this->getPostVar() . "[$i]");
            $tpl->setVariable("ID", $this->getFieldId() . "[$i]");
            $tpl->setVariable("CMD_ADD", "cmd[add" . $this->getFieldId() . "][$i]");
            $tpl->setVariable("CMD_REMOVE", "cmd[remove" . $this->getFieldId() . "][$i]");
            $tpl->setVariable("ALT_ADD", $lng->txt("add"));
            $tpl->setVariable("ALT_REMOVE", $lng->txt("remove"));
            if ($this->getDisabled()) {
                $tpl->setVariable(
                    "DISABLED",
                    " disabled=\"disabled\""
                );
            }

            $tpl->setVariable("ADD_BUTTON", ilGlyphGUI::get(ilGlyphGUI::ADD));
            $tpl->setVariable("REMOVE_BUTTON", ilGlyphGUI::get(ilGlyphGUI::REMOVE));
            $tpl->setVariable("TXT_MAX_SIZE", $lng->txt("file_notice") . " " . $this->getMaxFileSizeString());
            $tpl->setVariable("MAX_UPLOAD_VALUE", $this->getMaxFileUploads());
            $tpl->setVariable("TXT_MAX_UPLOADS", $lng->txt("form_msg_max_upload") . " " . $this->getMaxFileUploads());
            $tpl->parseCurrentBlock();
            $i++;
        }
        $tpl->setVariable("ELEMENT_ID", $this->getFieldId());

        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $tpl->get());
        $a_tpl->parseCurrentBlock();

        $main_tpl = $this->tpl;
        $main_tpl->addJavascript("./Services/Form/js/ServiceFormWizardInput.js");
        $main_tpl->addJavascript("./Services/Form/templates/default/filewizard.js");
    }
}
