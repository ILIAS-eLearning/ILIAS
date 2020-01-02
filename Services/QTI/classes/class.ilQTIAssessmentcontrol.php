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
* QTI assessmentcontrol class
*
* @author Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version $Id$
*
* @package assessment
*/
class ilQTIAssessmentcontrol
{
    public $hintswitch;
    public $solutionswitch;
    public $view;
    public $feedbackswitch;
    
    public function __construct()
    {
        $this->hintswitch = "";
        $this->solutionswitch = "";
        $this->view = "All";
        $this->feedbackswitch = "";
    }
    
    public function setView($a_view)
    {
        switch ($a_view) {
            case "Administrator":
            case "AdminAuthority":
            case "Assessor":
            case "Author":
            case "Candidate":
            case "InvigilatorProctor":
            case "Psychometrician":
            case "Scorer":
            case "Tutor":
                $this->view = $a_view;
                break;
            default:
                $this->view = "All";
                break;
        }
    }
    
    public function getView()
    {
        return $this->view;
    }

    public function setHintswitch($a_hintswitch)
    {
        switch ($a_hintswitch) {
            case "Yes":
            case "No":
                $this->hintswitch = $a_hintswitch;
                break;
            default:
                $this->hintswitch = "Yes";
                break;
        }
    }
    
    public function getHintswitch()
    {
        return $this->hintswitch;
    }
    
    public function setSolutionswitch($a_solutionswitch)
    {
        switch ($a_solutionswitch) {
            case "Yes":
            case "No":
                $this->solutionswitch = $a_solutionswitch;
                break;
            default:
                $this->solutionswitch = "Yes";
                break;
        }
    }
    
    public function getSolutionswitch()
    {
        return $this->solutionswitch;
    }

    public function setFeedbackswitch($a_feedbackswitch)
    {
        switch ($a_feedbackswitch) {
            case "Yes":
            case "No":
                $this->feedbackswitch = $a_feedbackswitch;
                break;
            default:
                $this->feedbackswitch = "Yes";
                break;
        }
    }
    
    public function getFeedbackswitch()
    {
        return $this->feedbackswitch;
    }
}
