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

define("ACTION_SET", "1");
define("ACTION_ADD", "2");
define("ACTION_SUBTRACT", "3");
define("ACTION_MULTIPLY", "4");
define("ACTION_DIVIDE", "5");

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
    public $varname;
    public $action;
    public $content;
    
    public function __construct()
    {
    }

    public function setVarname($a_varname)
    {
        $this->varname = $a_varname;
    }
    
    public function getVarname()
    {
        return $this->varname;
    }
    
    public function setAction($a_action)
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
    
    public function getAction()
    {
        return $this->action;
    }
    
    public function setContent($a_content)
    {
        $this->content = $a_content;
    }
    
    public function getContent()
    {
        return $this->content;
    }
}
