<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Fields;

use ilFileInputGUI;

/**
 * Class OrderingEditor
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class AsqImageUpload extends ilFileInputGUI {
    
    /**
     * @var string
     */
    private $image_path;

    /**
     * Constructor
     *
     * @param	string	$a_title	Title
     * @param	string	$a_postvar	Post Variable
     */
    function __construct($a_title = "", $a_postvar = "")
    {
        parent::__construct($a_title, $a_postvar);
        $this->setType("image_file");
        $this->setSuffixes(array("jpg", "jpeg", "png", "gif", "svg"));
    }
    
    /**
     * Set Value. (used for displaying file title of existing file below input field)
     *
     * @param	string	$a_value	Value
     */
    function setImagePath($a_value)
    {
        $this->image_path = $a_value;
        
        if (!empty($a_value)) {
            parent::setValue(' ');
        }
        else {
            parent::setValue('');
        }
    }
    
    /**
     * Get Value.
     *
     * @return	string	Value
     */
    function getImagePath()
    {
        return $this->image;
    }

    /**
     * @return	boolean		Input ok, true/false
     */
    function checkInput()
    {
        $post = $_POST[$this->getPostVar()];
        
        $value = parent::checkInput();
        
        $_POST[$this->getPostVar()] = $post;
        
        return $value;
    }
    
    /**
     * Render html
     */
    function render($a_mode = "")
    {
        global $DIC;
        
        //TODO create template when definitive
        $additional = '<input type="hidden" name="' . $this->getPostVar() . '" value="' . $this->image_path . '" />';
        
        if (!empty($this->image_path)) {
            $additional .= '<img style="margin: 5px 0px 5px 0px; max-width: 333px;" src="' . $this->image_path . '" border="0" /><br />';
        }
        
        $delete = '';
        if (!$this->required) {
            $delete = '<div class="checkbox">
                        <label>
                            <input type="checkbox" 
                                   name="' . $this->getPostVar() . '_delete" 
                                   id="' . $this->getPostVar() . '_delete" 
                                   value="1" />' .
                                   $DIC->language()->txt("delete_existing_file") . 
                        '</label>
                       </div>';
        }
        
        if ($this->getDisabled()) {
           return $additional;
        }
        else {
            return '<div style="width: 333px;">' . parent::render($a_mode) . '</ div>' . $additional . $delete;
        }
        
    }
}