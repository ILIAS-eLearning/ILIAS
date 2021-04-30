<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* This class represents a property in a property form.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ingroup	ServicesForm
*/
class ilFormPropertyGUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilLanguage
     */
    protected $lng;

    protected $type;
    protected $title;
    protected $postvar;
    protected $info;
    protected $alert;
    protected $required = false;
    protected $parentgui;
    protected $parentform;
    protected $hidden_title = "";
    protected $multi = false;
    protected $multi_sortable = false;
    protected $multi_addremove = true;
    protected $multi_values;
    
    /**
    * Constructor
    *
    * @param	string	$a_title	Title
    * @param	string	$a_postvar	Post Variable
    */
    public function __construct($a_title = "", $a_postvar = "")
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->setTitle($a_title);
        $this->setPostVar($a_postvar);
        $this->setDisabled(false);
    }

    /**
    * Execute command.
    */
    public function executeCommand()
    {
        $ilCtrl = $this->ctrl;
        
        $next_class = $ilCtrl->getNextClass($this);
        $cmd = $ilCtrl->getCmd();
        
        return $this->$cmd();
    }

    /**
    * Set Type.
    *
    * @param	string	$a_type	Type
    */
    protected function setType($a_type)
    {
        $this->type = $a_type;
    }

    /**
    * Get Type.
    *
    * @return	string	Type
    */
    public function getType()
    {
        return $this->type;
    }

    /**
    * Set Title.
    *
    * @param	string	$a_title	Title
    */
    public function setTitle($a_title)
    {
        $this->title = $a_title;
    }

    /**
    * Get Title.
    *
    * @return	string	Title
    */
    public function getTitle()
    {
        return $this->title;
    }

    /**
    * Set Post Variable.
    *
    * @param	string	$a_postvar	Post Variable
    */
    public function setPostVar($a_postvar)
    {
        $this->postvar = $a_postvar;
    }

    /**
    * Get Post Variable.
    *
    * @return	string	Post Variable
    */
    public function getPostVar()
    {
        return $this->postvar;
    }

    /**
    * Get Post Variable.
    *
    * @return	string	Post Variable
    */
    public function getFieldId()
    {
        $id = str_replace("[", "__", $this->getPostVar());
        $id = str_replace("]", "__", $id);
        
        return $id;
    }

    /**
    * Set Information Text.
    *
    * @param	string	$a_info	Information Text
    */
    public function setInfo($a_info)
    {
        $this->info = $a_info;
    }

    /**
    * Get Information Text.
    *
    * @return	string	Information Text
    */
    public function getInfo()
    {
        return $this->info;
    }

    /**
    * Set Alert Text.
    *
    * @param	string	$a_alert	Alert Text
    */
    public function setAlert($a_alert)
    {
        $this->alert = $a_alert;
    }

    /**
    * Get Alert Text.
    *
    * @return	string	Alert Text
    */
    public function getAlert()
    {
        return $this->alert;
    }

    /**
    * Set Required.
    *
    * @param	boolean	$a_required	Required
    */
    public function setRequired($a_required)
    {
        $this->required = $a_required;
    }

    /**
    * Get Required.
    *
    * @return	boolean	Required
    */
    public function getRequired()
    {
        return $this->required;
    }
    
    /**
    * Set Disabled.
    *
    * @param	boolean	$a_disabled	Disabled
    */
    public function setDisabled($a_disabled)
    {
        $this->disabled = $a_disabled;
    }

    /**
    * Get Disabled.
    *
    * @return	boolean	Disabled
    */
    public function getDisabled()
    {
        return $this->disabled;
    }
    
    /**
    * Check input, strip slashes etc. set alert, if input is not ok.
    *
    * @return	boolean		Input ok, true/false
    */
    public function checkInput()
    {
        return false;		// please overwrite
    }

    /**
    * Set Parent Form.
    *
    * @param	object	$a_parentform	Parent Form
    */
    public function setParentForm($a_parentform)
    {
        $this->setParent($a_parentform);
    }

    /**
    * Get Parent Form.
    *
    * @return	object	Parent Form
    */
    public function getParentForm()
    {
        return $this->getParent();
    }

    /**
    * Set Parent GUI object.
    *
    * @param	object	parent gui object
    */
    public function setParent($a_val)
    {
        $this->parent_gui = $a_val;
    }
    
    /**
    * Get  Parent GUI object.
    *
    * @return	object	parent gui object
    */
    public function getParent()
    {
        return $this->parent_gui;
    }

    /**
    * Get sub form html
    *
    */
    public function getSubForm()
    {
        return "";
    }

    /**
    * Sub form hidden on init?
    *
    */
    public function hideSubForm()
    {
        return false;
    }

    /**
    * Set hidden title (for screenreaders)
    *
    * @param	string	hidden title
    */
    public function setHiddenTitle($a_val)
    {
        $this->hidden_title = $a_val;
    }
    
    /**
    * Get hidden title
    *
    * @return	string	hidden title
    */
    public function getHiddenTitle()
    {
        return $this->hidden_title;
    }
    
    /**
    * Get item by post var
    *
    * @return	mixed	false or item object
    */
    public function getItemByPostVar($a_post_var)
    {
        if ($this->getPostVar() == $a_post_var) {
            return $this;
        }
        
        return false;
    }
    
    /**
    * serialize data
    */
    public function serializeData()
    {
        return serialize($this->getValue());
    }
    
    /**
    * unserialize data
    */
    public function unserializeData($a_data)
    {
        $data = unserialize($a_data);

        if ($data) {
            $this->setValue($data);
        } else {
            $this->setValue(false);
        }
    }
    
    /**
    * Write to session
    */
    public function writeToSession()
    {
        $parent = $this->getParent();
        if (!is_object($parent)) {
            die("You must set parent for " . get_class($this) . " to use serialize feature.");
        }
        $_SESSION["form_" . $parent->getId()][$this->getFieldId()] =
            $this->serializeData();
    }

    /**
    * Clear session value
    */
    public function clearFromSession()
    {
        $parent = $this->getParent();
        if (!is_object($parent)) {
            die("You must set parent for " . get_class($this) . " to use serialize feature.");
        }
        $_SESSION["form_" . $parent->getId()][$this->getFieldId()] = false;
    }

    /**
    * Read from session
    */
    public function readFromSession()
    {
        $parent = $this->getParent();
        if (!is_object($parent)) {
            die("You must set parent for " . get_class($this) . " to use serialize feature.");
        }
        $this->unserializeData($_SESSION["form_" . $parent->getId()][$this->getFieldId()]);
    }
    
    /**
     * Get hidden tag (used for disabled properties)
     */
    public function getHiddenTag($a_post_var, $a_value)
    {
        return '<input type="hidden" name="' . $a_post_var . '" value="' . ilUtil::prepareFormOutput($a_value) . '" />';
    }
    
    /**
     * Set Multi
     *
     * @param	bool	$a_multi	Multi
     */
    public function setMulti($a_multi, $a_sortable = false, $a_addremove = true)
    {
        if (!$this instanceof ilMultiValuesItem) {
            require_once 'Services/Form/exceptions/class.ilFormException.php';
            throw new ilFormException(sprintf(
                "%s not supported for form property type %s",
                __FUNCTION__,
                get_class($this)
            ));
        }
        
        $this->multi = (bool) $a_multi;
        $this->multi_sortable = (bool) $a_sortable;
        $this->multi_addremove = (bool) $a_addremove;
    }

    /**
     * Get Multi
     *
     * @return	bool	Multi
     */
    public function getMulti()
    {
        return $this->multi;
    }
    
    /**
     * Set multi values
     *
     * @param array $a_values
     */
    public function setMultiValues(array $a_values)
    {
        $this->multi_values = array_unique($a_values);
    }
    
    /**
     * Get multi values
     *
     * @return array
     */
    public function getMultiValues()
    {
        return $this->multi_values;
    }
    
    /**
     * Get HTML for multiple value icons
     *
     * @param bool $a_sortable
     * @return string;
     */
    protected function getMultiIconsHTML()
    {
        $lng = $this->lng;
        
        $id = $this->getFieldId();
        
        $tpl = new ilTemplate("tpl.multi_icons.html", true, true, "Services/Form");
        
        $html = "";
        if ($this->multi_addremove) {
            $tpl->setCurrentBlock("addremove");
            $tpl->setVariable("ID", $id);
            $tpl->setVariable("TXT_ADD", $lng->txt("add"));
            $tpl->setVariable("TXT_REMOVE", $lng->txt("remove"));
            include_once("./Services/UIComponent/Glyph/classes/class.ilGlyphGUI.php");
            $tpl->setVariable("SRC_ADD", ilGlyphGUI::get(ilGlyphGUI::ADD));
            $tpl->setVariable("SRC_REMOVE", ilGlyphGUI::get(ilGlyphGUI::REMOVE));
            $tpl->parseCurrentBlock();
        }
        
        if ($this->multi_sortable) {
            $tpl->setCurrentBlock("sortable");
            $tpl->setVariable("ID", $id);
            $tpl->setVariable("TXT_DOWN", $lng->txt("down"));
            $tpl->setVariable("TXT_UP", $lng->txt("up"));
            include_once("./Services/UIComponent/Glyph/classes/class.ilGlyphGUI.php");
            $tpl->setVariable("SRC_UP", ilGlyphGUI::get(ilGlyphGUI::UP));
            $tpl->setVariable("SRC_DOWN", ilGlyphGUI::get(ilGlyphGUI::DOWN));
            $tpl->parseCurrentBlock();
        }
        
        return $tpl->get();
    }
    
    /**
     * Get content that has to reside outside of the parent form tag, e.g. panels/layers
     *
     * @return string
     */
    public function getContentOutsideFormTag()
    {
    }

    /**
     * Remove prohibited characters
     * see #19159
     *
     * @param string $a_text
     * @return string
     */
    public static function removeProhibitedCharacters($a_text)
    {
        return str_replace("\x0B", "", $a_text);
    }

    /**
     * Strip slashes with add space fallback, see https://www.ilias.de/mantis/view.php?id=19727
     *
     * @param string $a_str string
     * @return string
     */
    public function stripSlashesAddSpaceFallback($a_str)
    {
        $str = ilUtil::stripSlashes($a_str);
        if ($str != $a_str) {
            $str = ilUtil::stripSlashes(str_replace("<", "< ", $a_str));
        }
        return $str;
    }

    /**
     * Get label "for" attribute value for filter
     * @return string
     */
    public function getTableFilterLabelFor() {
        return $this->getFieldId();
    }

    /**
     * Get label "for" attribute value for form
     * @return string
     */
    public function getFormLabelFor() {
        return $this->getFieldId();
    }

}
