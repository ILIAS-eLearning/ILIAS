<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

define("CONTINUE_YES", "1");
define("CONTINUE_NO", "2");

/**
* QTI respcondition class
*
* @author Helmut Schottmüller <hschottm@gmx.de>
* @version $Id$
*
* @package assessment
*/
class ilQTIRespcondition
{
    public $continue;
    public $title;
    public $comment;
    public $conditionvar;
    public $setvar;
    public $displayfeedback;
    public $respcond_extension;
    
    public function __construct()
    {
        $this->setvar = array();
        $this->displayfeedback = array();
    }
    
    public function setContinue($a_continue)
    {
        switch (strtolower($a_continue)) {
            case "1":
            case "yes":
                $this->continue = CONTINUE_YES;
                break;
            case "2":
            case "no":
                $this->continue = CONTINUE_NO;
                break;
        }
    }
    
    public function getContinue()
    {
        return $this->continue;
    }
    
    public function setTitle($a_title)
    {
        $this->title = $a_title;
    }
    
    public function getTitle()
    {
        return $this->title;
    }
    
    public function setComment($a_comment)
    {
        $this->comment = $a_comment;
    }
    
    public function getComment()
    {
        return $this->comment;
    }
    
    public function setConditionvar($a_conditionvar)
    {
        $this->conditionvar = $a_conditionvar;
    }
    
    public function getConditionvar()
    {
        return $this->conditionvar;
    }
    
    public function setRespcond_extension($a_respcond_extension)
    {
        $this->respcond_extension = $a_respcond_extension;
    }
    
    public function getRespcond_extension()
    {
        return $this->respcond_extension;
    }
    
    public function addSetvar($a_setvar)
    {
        array_push($this->setvar, $a_setvar);
    }
    
    public function addDisplayfeedback($a_displayfeedback)
    {
        array_push($this->displayfeedback, $a_displayfeedback);
    }
}
