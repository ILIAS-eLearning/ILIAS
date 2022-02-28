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

const CONTINUE_YES = "1";
const CONTINUE_NO = "2";

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
    /** @var string|null */
    public $continue;

    /** @var string|null */
    public $title;
    public $comment;
    public $conditionvar;

    /** @var array */
    public $setvar;

    /** @var array */
    public $displayfeedback;
    public $respcond_extension;
    
    public function __construct()
    {
        $this->setvar = array();
        $this->displayfeedback = array();
    }

    /**
     * @param string
     */
    public function setContinue($a_continue) : void
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

    /**
     * @return string|null
     */
    public function getContinue()
    {
        return $this->continue;
    }

    /**
     * @param string $a_title
     */
    public function setTitle($a_title) : void
    {
        $this->title = $a_title;
    }

    /**
     * @return string|null
     */
    public function getTitle()
    {
        return $this->title;
    }
    
    public function setComment($a_comment) : void
    {
        $this->comment = $a_comment;
    }
    
    public function getComment()
    {
        return $this->comment;
    }
    
    public function setConditionvar($a_conditionvar) : void
    {
        $this->conditionvar = $a_conditionvar;
    }
    
    public function getConditionvar()
    {
        return $this->conditionvar;
    }
    
    public function setRespcond_extension($a_respcond_extension) : void
    {
        $this->respcond_extension = $a_respcond_extension;
    }
    
    public function getRespcond_extension()
    {
        return $this->respcond_extension;
    }
    
    public function addSetvar($a_setvar) : void
    {
        $this->setvar[] = $a_setvar;
    }
    
    public function addDisplayfeedback($a_displayfeedback) : void
    {
        $this->displayfeedback[] = $a_displayfeedback;
    }
}
