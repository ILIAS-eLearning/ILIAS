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
* QTI assessment class
*
* @author Helmut Schottmüller <hschottm@gmx.de>
* @version $Id$
*
* @package assessment
*/
class ilQTIAssessment implements ilQTIPresentationMaterialAware
{
    public ?string $ident = null;
    public ?string $title = null;
    public ?string $xmllang = null;
    public ?string $comment = null;
    /** @var null|array{h: string, m: string, s: string} */
    public ?array $duration = null;
    /** @var array{label: string, entry: string}[] */
    public array $qtimetadata = [];
    /** @var ilQTIObjectives[] */
    public array $objectives = [];
    /** @var ilQTIAssessmentcontrol[] */
    public array $assessmentcontrol = [];
    protected ?ilQTIPresentationMaterial $presentation_material = null;

    public function setIdent(string $a_ident) : void
    {
        $this->ident = $a_ident;
    }

    public function getIdent() : ?string
    {
        return $this->ident;
    }

    public function setTitle(string $a_title) : void
    {
        $this->title = $a_title;
    }

    public function getTitle() : ?string
    {
        return $this->title;
    }

    public function setComment(string $a_comment) : void
    {
        $this->comment = $a_comment;
    }

    public function getComment() : ?string
    {
        return $this->comment;
    }

    public function setDuration(string $a_duration) : void // TODO PHP8-REVIEW Check if this function is really used
    {
        if (preg_match("/P(\d+)Y(\d+)M(\d+)DT(\d+)H(\d+)M(\d+)S/", $a_duration, $matches)) {
            $this->duration = array(
                "h" => $matches[4],
                "m" => $matches[5],
                "s" => $matches[6]
            );
        }
    }

    /**
     * @return null|array{h: string, m: string, s: string}
     */
    public function getDuration() : ?array
    {
        return $this->duration;
    }

    public function setXmllang(string $a_xmllang) : void
    {
        $this->xmllang = $a_xmllang;
    }

    public function getXmllang() : ?string
    {
        return $this->xmllang;
    }

    /**
     * @param array{label: string, entry: string} $a_metadata
     */
    public function addQtiMetadata(array $a_metadata) : void
    {
        $this->qtimetadata[] = $a_metadata;
    }

    public function addObjectives(?ilQTIObjectives $a_objectives) : void // TODO PHP8-REVIEW Should null really be allowed here as possible/useful value?
    {
        $this->objectives[] = $a_objectives;
    }

    public function addAssessmentcontrol(?ilQTIAssessmentcontrol $a_assessmentcontrol) : void // TODO PHP8-REVIEW Should null really be allowed here as possible/useful value?
    {
        $this->assessmentcontrol[] = $a_assessmentcontrol;
    }

    public function setPresentationMaterial(ilQTIPresentationMaterial $presentation_material) : void
    {
        $this->presentation_material = $presentation_material;
    }

    public function getPresentationMaterial() : ?ilQTIPresentationMaterial
    {
        return $this->presentation_material;
    }
}
