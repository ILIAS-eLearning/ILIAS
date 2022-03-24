<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * BlockGUI class for (centered) Content on Personal Desktop
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilDashboardContentBlockGUI extends ilBlockGUI
{
    public static string $block_type = "dashcontent";
    protected int $currentitemnumber;
    protected string $content;
    
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

    public function getBlockType() : string
    {
        return self::$block_type;
    }

    public function setCurrentItemNumber(int $a_currentitemnumber) : void
    {
        $this->currentitemnumber = $a_currentitemnumber;
    }

    public function getCurrentItemNumber() : int
    {
        return $this->currentitemnumber;
    }

    protected function isRepositoryObject() : bool
    {
        return false;
    }

    public function getHTML() : string
    {
        return parent::getHTML();
    }
    
    public function getContent() : string
    {
        return $this->content;
    }
    
    public function setContent(string $a_content) : void
    {
        $this->content = $a_content;
    }
    
    public function fillDataSection() : void
    {
        $this->tpl->setVariable("BLOCK_ROW", $this->getContent());
    }

    public function fillFooter() : void
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
    
    public function fillPreviousNext() : void
    {
    }
}
