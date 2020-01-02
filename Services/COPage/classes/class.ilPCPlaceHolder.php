<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/COPage/classes/class.ilPageContent.php");

/**
* Class ilPlaceHolder
*
* List content object (see ILIAS DTD)
*
* @version $Id$
*
* @ingroup ServicesCOPage
*/

class ilPCPlaceHolder extends ilPageContent
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilLanguage
     */
    protected $lng;
    
    //class of placeholder
    
    public $q_node;			// node of Paragraph element
    public $content_class;
    public $height;
    
    /**
    * Init page content component.
    */
    public function init()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->setType("plach");
    }
    
    
    /**
    * Set node
    */
    public function setNode($a_node)
    {
        parent::setNode($a_node);		// this is the PageContent node
        $this->q_node = $a_node->first_child();		//... and this the PlaceHolder
    }
    
    /**
    * Create PlaceHolder Element
    */
    public function create(&$a_pg_obj, $a_hier_id)
    {
        $this->createPageContentNode();
        $a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER);
        $this->q_node = $this->dom->create_element("PlaceHolder");
        $this->q_node = $this->node->append_child($this->q_node);
    }

    /**
    * Set Content Class.
    *
    * @param	string	$a_class	Content Class
    */
    public function setContentClass($a_class)
    {
        if (is_object($this->q_node)) {
            $this->q_node->set_attribute("ContentClass", $a_class);
        }
    }

    /**
    * Get Content Class.
    *
    * @return	string	Content Class
    */
    public function getContentClass()
    {
        if (is_object($this->q_node)) {
            return $this->q_node->get_attribute("ContentClass", $a_class);
        }
        return false;
    }
    
    /**
    * Set Height
    *
    * @param	string	$a_height	Height
    */
    public function setHeight($a_height)
    {
        if (is_object($this->q_node)) {
            $this->q_node->set_attribute("Height", $a_height);
        }
    }
    
    
    /**
    * Get Height
    *
    * @return	string	Content Class
    */
    public function getHeight()
    {
        if (is_object($this->q_node)) {
            return $this->q_node->get_attribute("Height", $a_class);
        }
        return false;
    }
    
    /**
    * Get characteristic of PlaceHolder.
    *
    * @return	string		characteristic
    */
    public function getClass()
    {
        return "";
    }
    
    /**
     * Get lang vars needed for editing
     * @return array array of lang var keys
     */
    public static function getLangVars()
    {
        return array("question_placeh","media_placeh","text_placeh",
            "ed_insert_plach","question_placehl","media_placehl","text_placehl",
            "verification_placeh", "verification_placehl");
    }


    /**
     * Modify page content after xsl
     *
     * @param string $a_html
     * @return string
     */
    public function modifyPageContentPostXsl($a_html, $a_mode)
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        //
        // Note: this standard output is "overwritten", e.g. by ilPortfolioPageGUI::postOutputProcessing
        //

        $c_pos = 0;
        $start = strpos($a_html, "{{{{{PlaceHolder#");
        if (is_int($start)) {
            $end = strpos($a_html, "}}}}}", $start);
        }
        $i = 1;
        while ($end > 0) {
            $param = substr($a_html, $start + 17, $end - $start - 17);
            $param = explode("#", $param);

            $html = $param[2];
            switch ($param[2]) {
                case "Text":
                    $html = $lng->txt("cont_text_placeh");
                    break;

                case "Media":
                    $html = $lng->txt("cont_media_placeh");
                    break;

                case "Question":
                    $html = $lng->txt("cont_question_placeh");
                    break;

                case "Verification":
                    $html = $lng->txt("cont_verification_placeh");
                    break;
            }

            $h2 = substr($a_html, 0, $start) .
                $html .
                substr($a_html, $end + 5);
            $a_html = $h2;
            $i++;

            $start = strpos($a_html, "{{{{{PlaceHolder#", $start + 5);
            $end = 0;
            if (is_int($start)) {
                $end = strpos($a_html, "}}}}}", $start);
            }
        }
        return $a_html;
    }
}
