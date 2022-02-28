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

const ACTION_SET = "1";
const ACTION_ADD = "2";
const ACTION_SUBTRACT = "3";
const ACTION_MULTIPLY = "4";
const ACTION_DIVIDE = "5";

/**
* QTI setvar class
*
* @author Helmut Schottmüller <hschottm@gmx.de>
* @version $Id$
*
* @package assessment
*/
class ilQTISetvar
{
    /** @var string|null */
    public $varname;

    /** @var string|null */
    public $action;

    /** @var string|null */
    public $content;
    
    public function __construct()
    {
    }

    /**
     * @param string $a_varname
     */
    public function setVarname($a_varname) : void
    {
        $this->varname = $a_varname;
    }

    /**
     * @return string|null
     */
    public function getVarname()
    {
        return $this->varname;
    }

    /**
     * @param string $a_action
     */
    public function setAction($a_action) : void
    {
        switch (strtolower($a_action)) {
            case "set":
            case "1":
                $this->action = ACTION_SET;
                break;
            case "add":
            case "2":
                $this->action = ACTION_ADD;
                break;
            case "subtract":
            case "3":
                $this->action = ACTION_SUBTRACT;
                break;
            case "multiply":
            case "4":
                $this->action = ACTION_MULTIPLY;
                break;
            case "divide":
            case "5":
                $this->action = ACTION_DIVIDE;
                break;
        }
    }

    /**
     * @return string|null
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param string $a_content
     */
    public function setContent($a_content) : void
    {
        $this->content = $a_content;
    }

    /**
     * @return string|null
     */
    public function getContent()
    {
        return $this->content;
    }
}
