<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 ********************************************************************
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
    public string $hintswitch = "";
    public string $solutionswitch = "";
    public string $view = "All";
    public string $feedbackswitch = "";

    public function setView(string $a_view): void
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

    public function getView(): string
    {
        return $this->view;
    }

    public function setHintswitch(string $a_hintswitch): void
    {
        $this->hintswitch = 'No' === $a_hintswitch ? 'No' : 'Yes';
    }

    public function getHintswitch(): string
    {
        return $this->hintswitch;
    }

    public function setSolutionswitch(string $a_solutionswitch): void
    {
        $this->solutionswitch = 'No' === $a_solutionswitch ? 'No' : 'Yes';
    }

    public function getSolutionswitch(): string
    {
        return $this->solutionswitch;
    }

    public function setFeedbackswitch(string $a_feedbackswitch): void
    {
        $this->feedbackswitch = 'No' === $a_feedbackswitch ? 'No' : 'Yes';
    }

    public function getFeedbackswitch(): string
    {
        return $this->feedbackswitch;
    }
}
