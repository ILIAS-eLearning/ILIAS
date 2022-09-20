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
    public string $title = '';
    public ?string $xmllang = null;
    public string $comment = '';
    /** @var array{label: string, entry: string}[] */
    public array $qtimetadata = [];
    /** @var ilQTIObjectives[] */
    public array $objectives = [];
    /** @var ilQTIAssessmentcontrol[] */
    public array $assessmentcontrol = [];
    protected ?ilQTIPresentationMaterial $presentation_material = null;

    public function setIdent(string $a_ident): void
    {
        $this->ident = $a_ident;
    }

    public function getIdent(): ?string
    {
        return $this->ident;
    }

    public function setTitle(string $a_title): void
    {
        $this->title = $a_title;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setComment(string $a_comment): void
    {
        $this->comment = $a_comment;
    }

    public function getComment(): string
    {
        return $this->comment;
    }

    public function setXmllang(string $a_xmllang): void
    {
        $this->xmllang = $a_xmllang;
    }

    public function getXmllang(): ?string
    {
        return $this->xmllang;
    }

    /**
     * @param array{label: string, entry: string} $a_metadata
     */
    public function addQtiMetadata(array $a_metadata): void
    {
        $this->qtimetadata[] = $a_metadata;
    }

    public function addObjectives(ilQTIObjectives $a_objectives): void
    {
        $this->objectives[] = $a_objectives;
    }

    public function addAssessmentcontrol(ilQTIAssessmentcontrol $a_assessmentcontrol): void
    {
        $this->assessmentcontrol[] = $a_assessmentcontrol;
    }

    public function setPresentationMaterial(ilQTIPresentationMaterial $presentation_material): void
    {
        $this->presentation_material = $presentation_material;
    }

    public function getPresentationMaterial(): ?ilQTIPresentationMaterial
    {
        return $this->presentation_material;
    }
}
