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

require_once 'Services/QTI/interfaces/interface.ilQTIPresentationMaterialAware.php';

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
    public ?string $ident;
    public ?string $title;
    public ?string $xmllang;
    public ?string $comment;
    /** @var null|array{h: string, m: string, s: string} */
    public ?array $duration;
    /** @var array{label: string, entry: string}[] */
    public array $qtimetadata;
    /** @var ilQTIObjectives[] */
    public array $objectives;
    /** @var ilQTIAssessmentcontrol[] */
    public array $assessmentcontrol;
    protected ilQTIPresentationMaterial $presentation_material;

    public function __construct()
    {
        $this->ident = null;
        $this->title = null;
        $this->xmllang = null;
        $this->comment = null;
        $this->duration = null;
        $this->qtimetadata = [];
        $this->objectives = [];
        $this->assessmentcontrol = [];
    }

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

    public function setDuration(string $a_duration) : void
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

    public function addObjectives(?ilQTIObjectives $a_objectives) : void
    {
        $this->objectives[] = $a_objectives;
    }

    public function addAssessmentcontrol(ilQTIAssessmentcontrol $a_assessmentcontrol) : void
    {
        $this->assessmentcontrol[] = $a_assessmentcontrol;
    }

    public function setPresentationMaterial(ilQTIPresentationMaterial $a_material) : void
    {
        $this->presentation_material = $a_material;
    }

    public function getPresentationMaterial() : ?ilQTIPresentationMaterial
    {
        return $this->presentation_material;
    }
}
