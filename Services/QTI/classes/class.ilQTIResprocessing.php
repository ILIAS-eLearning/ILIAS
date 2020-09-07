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

/**
* QTI resprocessing class
*
* @author Helmut Schottmüller <hschottm@gmx.de>
* @version $Id$
*
* @package assessment
*/
class ilQTIResprocessing
{
    public $comment;
    public $outcomes;
    public $respcondition;
    public $itemproc_extension;
    public $scoremodel;
    
    public function __construct()
    {
        $this->respcondition = array();
        $this->itemproc_extension = array();
    }
    
    public function setComment($a_comment)
    {
        $this->comment = $a_comment;
    }
    
    public function getComment()
    {
        return $this->comment;
    }
    
    public function setOutcomes($a_outcomes)
    {
        $this->outcomes = $a_outcomes;
    }
    
    public function getOutcomes()
    {
        return $this->outcomes;
    }
    
    public function addRespcondition($a_respcondition)
    {
        array_push($this->respcondition, $a_respcondition);
    }
    
    public function setScoremodel($a_scoremodel)
    {
        $this->scoremodel = $a_scoremodel;
    }
    
    public function getScoremodel()
    {
        return $this->scoremodel;
    }
}
