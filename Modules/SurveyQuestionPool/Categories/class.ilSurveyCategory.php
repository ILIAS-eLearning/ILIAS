<?php
 /*
   +----------------------------------------------------------------------------+
   | ILIAS open source                                                          |
   +----------------------------------------------------------------------------+
   | Copyright (c) 1998-2001 ILIAS open source, University of Cologne           |
   |                                                                            |
   | This program is free software; you can redistribute it and/or              |
   | modify it under the terms of the GNU General Public License                |
   | as published by the Free Software Foundation; either version 2             |
   | of the License, or (at your option) any later version.                     |
   |                                                                            |
   | This program is distributed in the hope that it will be useful,            |
   | but WITHOUT ANY WARRANTY; without even the implied warranty of             |
   | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the              |
   | GNU General Public License for more details.                               |
   |                                                                            |
   | You should have received a copy of the GNU General Public License          |
   | along with this program; if not, write to the Free Software                |
   | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA. |
   +----------------------------------------------------------------------------+
*/

/**
* Survey category class
*
* The ilSurveyCategory class encapsules a survey category
*
* @author		Helmut Schottmüller <ilias@aurealis.de>
* @version	$Id$
* @ingroup ModulesSurveyQuestionPool
*/
class ilSurveyCategory
{
    private $arrData;
    
    /**
    * ilSurveyPhrases constructor
    */
    public function __construct($title = null, $other = 0, $neutral = 0, $label = null, $scale = null)
    {
        $this->arrData = array(
            "title" => $title,
            "other" => $other,
            "neutral" => $neutral,
            "label" => $label,
            "scale" => $scale
        );
    }
    
    /**
    * Object getter
    */
    public function __get($value)
    {
        switch ($value) {
            case 'other':
            case 'neutral':
                return ($this->arrData[$value]) ? 1 : 0;
                break;
            default:
                if (array_key_exists($value, $this->arrData)) {
                    return $this->arrData[$value];
                } else {
                    return null;
                }
                break;
        }
    }

    /**
    * Object setter
    */
    public function __set($key, $value)
    {
        switch ($key) {
            default:
                $this->arrData[$key] = $value;
                break;
        }
    }
}
