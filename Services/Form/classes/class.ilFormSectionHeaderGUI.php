<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * This class represents a section header in a property form.
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilFormSectionHeaderGUI
{
    protected $type;
    protected $title;
    protected $info;
    protected $section_icon;
    protected $section_anchor;
    
    /**
    * Constructor
    *
    * @param
    */
    public function __construct()
    {
        $this->setType("section_header");
    }
    
    public function checkInput()
    {
        return true;
    }

    /**
    * Set Type.
    *
    * @param	string	$a_type	Type
    */
    public function setType($a_type)
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
     * Set section icon
     *
     * @access public
     * @param string path to icon
     * @param string alternative text
     *
     */
    public function setSectionIcon($a_file, $a_alt)
    {
        $this->section_icon['file'] = $a_file;
        $this->section_icon['alt'] = $a_alt;
    }
    
    /**
     * Get section icon
     *
     * @access public
     *
     */
    public function getSectionIcon()
    {
        return $this->section_icon ? $this->section_icon : array();
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
    * Set Parent Form.
    *
    * @param	object	$a_parentform	Parent Form
    */
    public function setParentForm($a_parentform)
    {
        $this->parentform = $a_parentform;
    }

    /**
    * Get Parent Form.
    *
    * @return	object	Parent Form
    */
    public function getParentForm()
    {
        return $this->parentform;
    }
    
    /**
     * set section label;
     *
     * @param unknown_type $value
     */
    public function setSectionAnchor($value)
    {
        $this->section_anchor = $value;
    }

    /**
    * Insert property html
    *
    */
    public function insert($a_tpl)
    {
        $section_icon = $this->getSectionIcon();
        
        if (isset($section_icon['file']) && is_file($section_icon['file'])) {
            $a_tpl->setCurrentBlock("title_icon");
            $a_tpl->setVariable("IMG_ICON", $section_icon['file']);
            $a_tpl->setVariable('IMG_ALT', $section_icon['alt']);
            $a_tpl->parseCurrentBlock();
        }
        
        $a_tpl->setCurrentBlock("header");
        $a_tpl->setVariable("TXT_TITLE", $this->getTitle());
        $a_tpl->setVariable("TXT_DESCRIPTION", $this->getInfo());
        $a_tpl->setVariable('HEAD_COLSPAN', 2);
        if (isset($this->section_anchor)) {
            $a_tpl->setVariable('LABEL', $this->section_anchor);
        }
        $a_tpl->parseCurrentBlock();
    }
    
    /**
    * Set value by array
    *
    * @param	array	$a_values	value array
    */
    public function setValueByArray($a_values)
    {
        // nothing to do since is a header
    }
    
    public function getPostVar()
    {
        // nothing to do since is a header
    }
}
