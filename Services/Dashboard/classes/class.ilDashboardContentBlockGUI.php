<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * BlockGUI class for (centered) Content on Personal Desktop
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilDashboardContentBlockGUI extends ilBlockGUI
{
    public static $block_type = "dashcontent";
    
    /**
    * Constructor
    */
    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->user = $DIC->user();

        parent::__construct();
        
        $this->setEnableNumInfo(false);
        $this->setLimit(99999);
        $this->setPresentation(self::PRES_MAIN_LEG);
        $this->allow_moving = false;
    }

    /**
     * @inheritdoc
     */
    public function getBlockType() : string
    {
        return self::$block_type;
    }

    /**
    * Set Current Item Number.
    *
    * @param	int	$a_currentitemnumber	Current Item Number
    */
    public function setCurrentItemNumber($a_currentitemnumber)
    {
        $this->currentitemnumber = $a_currentitemnumber;
    }

    /**
    * Get Current Item Number.
    *
    * @return	int	Current Item Number
    */
    public function getCurrentItemNumber()
    {
        return $this->currentitemnumber;
    }

    /**
     * @inheritdoc
     */
    protected function isRepositoryObject() : bool
    {
        return false;
    }

    public function getHTML()
    {
        return parent::getHTML();
    }
    
    public function getContent()
    {
        return $this->content;
    }
    
    public function setContent($a_content)
    {
        $this->content = $a_content;
    }
    
    /**
    * Fill data section
    */
    public function fillDataSection()
    {
        $this->tpl->setVariable("BLOCK_ROW", $this->getContent());
    }

    /**
    * block footer
    */
    public function fillFooter()
    {
        //$this->fillFooterLinks();
        $lng = $this->lng;

        if (is_array($this->data)) {
            $this->max_count = count($this->data);
        }
                
        // table footer numinfo
        if ($this->getEnableNumInfo()) {
            $numinfo = "(" . $this->getCurrentItemNumber() . " " .
                strtolower($lng->txt("of")) . " " . $this->max_count . ")";
    
            if ($this->max_count > 0) {
                $this->tpl->setVariable("NUMINFO", $numinfo);
            }
        }
    }
    
    public function fillPreviousNext()
    {
    }
}
