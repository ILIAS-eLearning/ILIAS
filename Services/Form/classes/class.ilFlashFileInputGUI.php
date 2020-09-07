<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* This class represents an image file property in a property form.
*
* @author Helmut Schottmüller <ilias@aurealis.de>
* @version $Id$
* @ingroup	ServicesForm
*/
class ilFlashFileInputGUI extends ilFileInputGUI
{
    /**
     * @var ilLanguage
     */
    protected $lng;

    protected $applet;
    protected $applet_path_web;
    protected $width;
    protected $height;
    protected $parameters;
    
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
        $this->setType("flash_file");
        $this->setSuffixes(array("swf"));
        $this->width = 550;
        $this->height = 400;
        $this->parameters = array();
    }

    /**
    * Set value by array
    *
    * @param	array	$a_values	value array
    */
    public function setValueByArray($a_values)
    {
        $this->setValue($a_values[$this->getPostVar()]);
    }

    public function getValue()
    {
        return $this->getApplet();
    }

    public function setValue($a_value)
    {
        if (is_array($a_value)) {
            if (array_key_exists('width', $a_value)) {
                $this->setWidth($a_value['width']);
            }
            if (array_key_exists('height', $a_value)) {
                $this->setHeight($a_value['height']);
            }
            if (array_key_exists('filename', $a_value)) {
                $this->setApplet($a_value['filename']);
            }
            if (is_array($a_value['flash_param_name'])) {
                $this->parameters = array();
                foreach ($a_value['flash_param_name'] as $idx => $val) {
                    $this->parameters[$val] = $a_value['flash_param_value'][$idx];
                }
            }
        }
    }

    /**
    * Check input, strip slashes etc. set alert, if input is not ok.
    *
    * @return	boolean		Input ok, true/false
    */
    public function checkInput()
    {
        $lng = $this->lng;

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

        if (is_array($_POST[$this->getPostVar()])) {
            if (($this->getRequired() && strlen($_POST[$this->getPostVar()]['width']) == 0) ||
                ($this->getRequired() && strlen($_POST[$this->getPostVar()]['height']) == 0)) {
                $this->setAlert($lng->txt("msg_input_is_required"));
                return false;
            }
            if (is_array($_POST[$this->getPostVar()]['flash_param_name'])) {
                foreach ($_POST[$this->getPostVar()]['flash_param_name'] as $idx => $val) {
                    if (strlen($val) == 0 || strlen($_POST[$this->getPostVar()]['flash_param_value'][$idx]) == 0) {
                        $this->setAlert($lng->txt("msg_input_is_required"));
                        return false;
                    }
                }
            }
        }
        
        return true;
    }

    /**
    * Set applet.
    *
    * @param	string	$a_applet	Applet
    */
    public function setApplet($a_applet)
    {
        $this->applet = $a_applet;
    }

    /**
    * Get applet.
    *
    * @return	string	Applet
    */
    public function getApplet()
    {
        return $this->applet;
    }

    /**
    * Set applet.path web
    *
    * @param	string	$a_path	Applet path web
    */
    public function setAppletPathWeb($a_path)
    {
        $this->applet_path_web = $a_path;
    }

    /**
    * Get applet.path web
    *
    * @return	string	Applet path web
    */
    public function getAppletPathWeb()
    {
        return $this->applet_path_web;
    }

    /**
    * Get width.
    *
    * @return	integer	width
    */
    public function getWidth()
    {
        return $this->width;
    }

    /**
    * Set width.
    *
    * @param	integer	$a_width	width
    */
    public function setWidth($a_width)
    {
        $this->width = $a_width;
    }

    /**
    * Get height.
    *
    * @return	integer	height
    */
    public function getHeight()
    {
        return $this->height;
    }

    /**
    * Set height.
    *
    * @param	integer	$a_height	height
    */
    public function setHeight($a_height)
    {
        $this->height = $a_height;
    }
    
    /**
    * Get parameters.
    *
    * @return	array Parameters
    */
    public function getParameters()
    {
        return $this->parameters;
    }
    
    /**
    * Set parameters.
    *
    * @param array $a_parameters Parameters
    */
    public function setParameters($a_parameters)
    {
        $this->parameters = $a_parameters;
    }

    /**
    * Add parameter.
    *
    * @param string $name Parameter name
    * @param string $value Parameter value
    */
    public function addParameter($name, $value)
    {
        $this->parameters[$name] = $value;
    }
    
    /**
    * Remove parameter.
    *
    * @param string $name Parameter name
    */
    public function removeParameter($name)
    {
        unset($this->parameters[$name]);
    }

    /**
    * Remove all parameters
    */
    public function clearParameters()
    {
        $this->parameters = array();
    }
    
    /**
    * Insert property html
    */
    public function insert($a_tpl)
    {
        $lng = $this->lng;
        
        $template = new ilTemplate("tpl.prop_flashfile.html", true, true, "Services/Form");
        if ($this->getApplet() != "") {
            $this->outputSuffixes($template);
            if (count($this->getParameters())) {
                $index = 0;
                $params = array();
                foreach ($this->getParameters() as $name => $value) {
                    array_push($params, urlencode($name) . "=" . urlencode($value));
                    $template->setCurrentBlock("applet_param_input");
                    $template->setVariable("TEXT_NAME", $lng->txt("name"));
                    $template->setVariable("TEXT_VALUE", $lng->txt("value"));
                    $template->setVariable("PARAM_INDEX", $index);
                    $template->setVariable("POST_VAR_P", $this->getPostVar());
                    $template->setVariable("VALUE_NAME", "value=\"" . ilUtil::prepareFormOutput($name) . "\"");
                    $template->setVariable("VALUE_VALUE", "value=\"" . ilUtil::prepareFormOutput($value) . "\"");
                    $template->setVariable("TEXT_DELETE_PARAM", $lng->txt("delete_parameter"));
                    $template->parseCurrentBlock();
                    $index++;
                }
                $template->setCurrentBlock("applet_parameter");
                $template->setVariable("PARAM_VALUE", join($params, "&"));
                $template->parseCurrentBlock();
                $template->setCurrentBlock("flash_vars");
                $template->setVariable("PARAM_VALUE", join($params, "&"));
                $template->parseCurrentBlock();
            }
            $template->setCurrentBlock("applet");
            $template->setVariable("TEXT_ADD_PARAM", $lng->txt("add_parameter"));
            $template->setVariable("APPLET_WIDTH", $this->getWidth());
            $template->setVariable("APPLET_HEIGHT", $this->getHeight());
            $template->setVariable("POST_VAR_D", $this->getPostVar());
            $template->setVariable("FILENAME", $this->getApplet());
            $template->setVariable("TEXT_WIDTH", $lng->txt("width"));
            $template->setVariable("TEXT_HEIGHT", $lng->txt("height"));
            $template->setVariable("APPLET_FILE", $this->getApplet());
            $template->setVariable("APPLET_PATH", $this->getAppletPathWeb() . $this->getApplet());
            if ($this->getWidth()) {
                $template->setVariable("VALUE_WIDTH", "value=\"" . $this->getWidth() . "\"");
            }
            if ($this->getHeight()) {
                $template->setVariable("VALUE_HEIGHT", "value=\"" . $this->getHeight() . "\"");
            }
            $template->setVariable("ID", $this->getFieldId());
            $template->setVariable(
                "TXT_DELETE_EXISTING",
                $lng->txt("delete_existing_file")
            );
            $template->parseCurrentBlock();
        }
        
        $js_tpl = new ilTemplate('tpl.flashAddParam.js', true, true, 'Services/Form');
        $js_tpl->setVariable("TEXT_NAME", $lng->txt("name"));
        $js_tpl->setVariable("TEXT_VALUE", $lng->txt("value"));
        $js_tpl->setVariable("POST_VAR", $this->getPostVar());
        $js_tpl->setVariable("TEXT_DELETE_PARAM", $lng->txt("delete_parameter"));
        $js_tpl->setVariable("TEXT_CONFIRM_DELETE_PARAMETER", $lng->txt("confirm_delete_parameter"));
        
        $template->setVariable("POST_VAR", $this->getPostVar());
        $template->setVariable("ID", $this->getFieldId());
        $template->setVariable("TXT_MAX_SIZE", $lng->txt("file_notice") . " " . $this->getMaxFileSizeString());
        $template->setVariable("JAVASCRIPT_FLASH", $js_tpl->get());

        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $template->get());
        $a_tpl->parseCurrentBlock();
        
        include_once "./Services/YUI/classes/class.ilYuiUtil.php";
        ilYuiUtil::initConnectionWithAnimation();
    }

    /**
    * Get deletion flag
    */
    public function getDeletionFlag()
    {
        if ($_POST[$this->getPostVar() . "_delete"]) {
            return true;
        }
        return false;
    }
}
