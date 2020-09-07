<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/UIComponent/Glyph/classes/class.ilGlyphGUI.php';

/**
* This class represents an image map file property in a property form.
*
* @author Helmut Schottmüller <ilias@aurealis.de>
* @version $Id$
* @ingroup	ServicesForm
*/
include_once "./Services/Form/classes/class.ilImageFileInputGUI.php";

class ilImagemapFileInputGUI extends ilImageFileInputGUI
{
    protected $areas = array();
    protected $image_path = "";
    protected $image_path_web = "";
    protected $line_color = "";
    
    protected $pointsUncheckedFieldEnabled = false;
    
    /**
    * Constructor
    *
    * @param	string	$a_title	Title
    * @param	string	$a_postvar	Post Variable
    */
    public function __construct($a_title = "", $a_postvar = "")
    {
        global $DIC;
        $lng = $DIC['lng'];

        parent::__construct($a_title, $a_postvar);
    }
    
    public function setPointsUncheckedFieldEnabled($pointsUncheckedFieldEnabled)
    {
        $this->pointsUncheckedFieldEnabled = (bool) $pointsUncheckedFieldEnabled;
    }
    
    public function getPointsUncheckedFieldEnabled()
    {
        return $this->pointsUncheckedFieldEnabled;
    }
    
    public function setAreas($a_areas)
    {
        $this->areas = $a_areas;
    }
    
    public function getLineColor()
    {
        return $this->line_color;
    }
    
    public function setLineColor($a_color)
    {
        $this->line_color = $a_color;
    }
    
    public function getImagePath()
    {
        return $this->image_path;
    }
    
    public function setImagePath($a_path)
    {
        $this->image_path = $a_path;
    }
    
    public function getImagePathWeb()
    {
        return $this->image_path_web;
    }
    
    public function setImagePathWeb($a_path_web)
    {
        $this->image_path_web = $a_path_web;
    }
    
    public function setAreasByArray($a_areas)
    {
        if (is_array($a_areas['name'])) {
            $this->areas = array();
            include_once "./Modules/TestQuestionPool/classes/class.assAnswerImagemap.php";
            foreach ($a_areas['name'] as $idx => $name) {
                if ($this->getPointsUncheckedFieldEnabled() && isset($a_areas['points_unchecked'])) {
                    $pointsUnchecked = $a_areas['points_unchecked'][$idx];
                } else {
                    $pointsUnchecked = 0.0;
                }
                
                array_push($this->areas, new ASS_AnswerImagemap(
                    $name,
                    $a_areas['points'][$idx],
                    $idx,
                    $a_areas['coords'][$idx],
                    $a_areas['shape'][$idx],
                    -1,
                    $pointsUnchecked
                ));
            }
        }
    }
    
    public function getAreas()
    {
        return $this->areas;
    }
    
    /**
    * Set value by array
    *
    * @param	array	$a_values	value array
    */
    public function setValueByArray($a_values)
    {
        $this->setValue($a_values[$this->getPostVar() . '_name']);
        $this->setAreasByArray($a_values[$this->getPostVar()]['coords']);
    }

    public function setValue($a_value)
    {
        parent::setValue($a_value);
    }

    /**
    * Check input, strip slashes etc. set alert, if input is not ok.
    *
    * @return	boolean		Input ok, true/false
    */
    public function checkInput()
    {
        global $DIC;
        $lng = $DIC['lng'];

        if (is_array($_POST[$this->getPostVar()])) {
            $_POST[$this->getPostVar()] = ilUtil::stripSlashesRecursive($_POST[$this->getPostVar()]);
        }
        // remove trailing '/'
        $_FILES[$this->getPostVar()]["name"] = rtrim($_FILES[$this->getPostVar()]["name"], '/');

        $filename = $_FILES[$this->getPostVar()]["name"];
        $filename_arr = pathinfo($_FILES[$this->getPostVar()]["name"]);
        $suffix = $filename_arr["extension"];
        $mimetype = $_FILES[$this->getPostVar()]["type"];
        $size_bytes = $_FILES[$this->getPostVar()]["size"];
        $temp_name = $_FILES[$this->getPostVar()]["tmp_name"];
        $error = $_FILES[$this->getPostVar()]["error"];

        // error handling
        if ($error > 0) {
            switch ($error) {
                case UPLOAD_ERR_INI_SIZE:
                    $this->setAlert($lng->txt("form_msg_file_size_exceeds"));
                    return false;
                    break;
                     
                case UPLOAD_ERR_FORM_SIZE:
                    $this->setAlert($lng->txt("form_msg_file_size_exceeds"));
                    return false;
                    break;
    
                case UPLOAD_ERR_PARTIAL:
                    $this->setAlert($lng->txt("form_msg_file_partially_uploaded"));
                    return false;
                    break;
    
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
                    break;
                     
                case UPLOAD_ERR_CANT_WRITE:
                    $this->setAlert($lng->txt("form_msg_file_cannot_write_to_disk"));
                    return false;
                    break;
     
                case UPLOAD_ERR_EXTENSION:
                    $this->setAlert($lng->txt("form_msg_file_upload_stopped_ext"));
                    return false;
                    break;
            }
        }
        
        // check suffixes
        if ($_FILES[$this->getPostVar()]["tmp_name"] != "" &&
            is_array($this->getSuffixes())) {
            if (!in_array(strtolower($suffix), $this->getSuffixes())) {
                $this->setAlert($lng->txt("form_msg_file_wrong_file_type"));
                return false;
            }
        }
        
        // virus handling
        if ($_FILES[$this->getPostVar()]["tmp_name"] != "") {
            $vir = ilUtil::virusHandling($temp_name, $filename);
            if ($vir[0] == false) {
                $this->setAlert($lng->txt("form_msg_file_virus_found") . "<br />" . $vir[1]);
                return false;
            }
        }
        
        $max = 0;
        if (is_array($_POST[$this->getPostVar()]['coords']['name'])) {
            foreach ($_POST[$this->getPostVar()]['coords']['name'] as $idx => $name) {
                if ((!strlen($_POST[$this->getPostVar()]['coords']['points'][$idx])) && ($this->getRequired)) {
                    $this->setAlert($lng->txt('form_msg_area_missing_points'));
                    return false;
                }
                if ((!is_numeric($_POST[$this->getPostVar()]['coords']['points'][$idx]))) {
                    $this->setAlert($lng->txt('form_msg_numeric_value_required'));
                    return false;
                }
                if ($_POST[$this->getPostVar()]['coords']['points'][$idx] > 0) {
                    $max = $_POST[$this->getPostVar()]['coords']['points'][$idx];
                }
            }
        }

        if ($max == 0 && (!$filename) && !$_FILES['imagemapfile']['tmp_name']) {
            $this->setAlert($lng->txt("enter_enough_positive_points"));
            return false;
        }
        return true;
    }

    /**
    * Insert property html
    */
    public function insert($a_tpl)
    {
        global $DIC;
        $lng = $DIC['lng'];
        
        $template = new ilTemplate("tpl.prop_imagemap_file.html", true, true, "Modules/TestQuestionPool");
        
        $this->outputSuffixes($template, "allowed_image_suffixes");
        
        if ($this->getImage() != "") {
            if (strlen($this->getValue())) {
                $template->setCurrentBlock("has_value");
                $template->setVariable("TEXT_IMAGE_NAME", $this->getValue());
                $template->setVariable("POST_VAR_D", $this->getPostVar());
                $template->parseCurrentBlock();
            }
            $template->setCurrentBlock("image");
            if (count($this->getAreas())) {
                include_once "./Modules/TestQuestionPool/classes/class.ilImagemapPreview.php";
                $preview = new ilImagemapPreview($this->getImagePath() . $this->getValue());
                foreach ($this->getAreas() as $index => $area) {
                    $preview->addArea($index, $area->getArea(), $area->getCoords(), $area->getAnswertext(), "", "", true, $this->getLineColor());
                }
                $preview->createPreview();
                $imagepath = $this->getImagePathWeb() . $preview->getPreviewFilename($this->getImagePath(), $this->getValue()) . "?img=" . time();
                $template->setVariable("SRC_IMAGE", $imagepath);
            } else {
                $template->setVariable("SRC_IMAGE", $this->getImage());
            }
            $template->setVariable("ALT_IMAGE", $this->getAlt());
            $template->setVariable("POST_VAR_D", $this->getPostVar());
            $template->setVariable(
                "TXT_DELETE_EXISTING",
                $lng->txt("delete_existing_file")
            );
            $template->setVariable("TEXT_ADD_RECT", $lng->txt('add_rect'));
            $template->setVariable("TEXT_ADD_CIRCLE", $lng->txt('add_circle'));
            $template->setVariable("TEXT_ADD_POLY", $lng->txt('add_poly'));
            $template->parseCurrentBlock();
        }

        if (is_array($this->getAreas()) && $this->getAreas()) {
            $counter = 0;
            foreach ($this->getAreas() as $area) {
                if (strlen($area->getPoints())) {
                    $template->setCurrentBlock('area_points_value');
                    $template->setVariable('VALUE_POINTS', $area->getPoints());
                    $template->parseCurrentBlock();
                }
                if ($this->getPointsUncheckedFieldEnabled()) {
                    if (strlen($area->getPointsUnchecked())) {
                        $template->setCurrentBlock('area_points_unchecked_value');
                        $template->setVariable('VALUE_POINTS_UNCHECKED', $area->getPointsUnchecked());
                        $template->parseCurrentBlock();
                    }
                    
                    $template->setCurrentBlock('area_points_unchecked_field');
                    $template->parseCurrentBlock();
                }
                if (strlen($area->getAnswertext())) {
                    $template->setCurrentBlock('area_name_value');
                    $template->setVariable('VALUE_NAME', $area->getAnswertext());
                    $template->parseCurrentBlock();
                }
                $template->setCurrentBlock('row');
                $template->setVariable('POST_VAR_R', $this->getPostVar());
                $template->setVariable('TEXT_SHAPE', strtoupper($area->getArea()));
                $template->setVariable('VALUE_SHAPE', $area->getArea());
                $coords = preg_replace("/(\d+,\d+,)/", "\$1 ", $area->getCoords());
                $template->setVariable('VALUE_COORDINATES', $area->getCoords());
                $template->setVariable('TEXT_COORDINATES', $coords);
                $template->setVariable('COUNTER', $counter);
                $template->setVariable("REMOVE_BUTTON", ilGlyphGUI::get(ilGlyphGUI::REMOVE));
                $template->parseCurrentBlock();
                $counter++;
            }
            $template->setCurrentBlock("areas");
            $template->setVariable("TEXT_NAME", $lng->txt("ass_imap_hint"));
            if ($this->getPointsUncheckedFieldEnabled()) {
                $template->setVariable("TEXT_POINTS", $lng->txt("points_checked"));
                            
                $template->setCurrentBlock('area_points_unchecked_head');
                $template->setVariable("TEXT_POINTS_UNCHECKED", $lng->txt("points_unchecked"));
                $template->parseCurrentBlock();
            } else {
                $template->setVariable("TEXT_POINTS", $lng->txt("points"));
            }
            $template->setVariable("TEXT_SHAPE", $lng->txt("shape"));
            $template->setVariable("TEXT_COORDINATES", $lng->txt("coordinates"));
            $template->setVariable("TEXT_COMMANDS", $lng->txt("actions"));
            $template->parseCurrentBlock();
        }
        
        $template->setVariable("POST_VAR", $this->getPostVar());
        $template->setVariable("ID", $this->getFieldId());
        $template->setVariable("TXT_BROWSE", $lng->txt("select_file"));
        $template->setVariable("TXT_MAX_SIZE", $lng->txt("file_notice") . " " .
            $this->getMaxFileSizeString());
            
        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $template->get());
        $a_tpl->parseCurrentBlock();

        global $DIC;
        $tpl = $DIC['tpl'];
        $tpl->addJavascript("./Services/Form/js/ServiceFormWizardInput.js");
        $tpl->addJavascript("./Modules/TestQuestionPool/templates/default/imagemap.js");
    }
}
