<?php declare(strict_types=1);

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
* QTI resprocessing class
*
* @author Helmut Schottmüller <hschottm@gmx.de>
* @version $Id$
*
* @package assessment
*/
class ilQTIResprocessing
{
    public ?string $comment = null;
    public ?ilQTIOutcomes $outcomes = null;
    /** @var ilQTIRespcondition[] */
    public array $respcondition = [];
    public ?string $scoremodel = null;

    public function setComment(string $a_comment) : void
    {
        $this->comment = $a_comment;
    }

    public function getComment() : ?string
    {
        return $this->comment;
    }

    public function setOutcomes(ilQTIOutcomes $a_outcomes) : void
    {
        $this->outcomes = $a_outcomes;
    }

    public function getOutcomes() : ?ilQTIOutcomes
    {
        return $this->outcomes;
    }
    
    public function addRespcondition(ilQTIRespcondition $a_respcondition) : void
    {
        $this->respcondition[] = $a_respcondition;
    }

    public function setScoremodel(string $a_scoremodel) : void
    {
        $this->scoremodel = $a_scoremodel;
    }

    public function getScoremodel() : ?string
    {
        return $this->scoremodel;
    }
}
