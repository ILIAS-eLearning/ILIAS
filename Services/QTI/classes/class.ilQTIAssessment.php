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
    /** @var string|null */
    public $ident;

    /** @var string|null */
    public $title;
    public $xmllang;
    public $comment;

    /** @var array|null */
    public $duration;

    /** @var array{label: string, entry: string}[] */
    public $qtimetadata;

    /** @var ilQTIObjectives[] */
    public $objectives;

    /** @var ilQTIAssessmentcontrol[] */
    public $assessmentcontrol;

    /** @var array */
    public $rubric;

    /**
     * @var ilQTIPresentationMaterial
     */
    protected $presentation_material;

    /** @var array */
    public $outcomes_processing;
    public $assessproc_extension;

    /** @var array */
    public $assessfeedback;
    public $selection_ordering;
    public $reference;

    /** @var array */
    public $sectionref;

    /** @var array */
    public $section;
    
    public function __construct()
    {
        $this->qtimetadata = array();
        $this->objectives = array();
        $this->assessmentcontrol = array();
        $this->rubric = array();
        $this->outcomes_processing = array();
        $this->assessfeedback = array();
        $this->sectionref = array();
        $this->section = array();
    }

    /**
     * @param string $a_ident
     */
    public function setIdent($a_ident) : void
    {
        $this->ident = $a_ident;
    }

    /**
     * @return string|null
     */
    public function getIdent()
    {
        return $this->ident;
    }

    /**
     * @param string $a_title
     */
    public function setTitle($a_title) : void
    {
        $this->title = $a_title;
    }

    /**
     * @return string|null
     */
    public function getTitle()
    {
        return $this->title;
    }
    
    public function setComment($a_comment) : void
    {
        $this->comment = $a_comment;
    }
    
    public function getComment()
    {
        return $this->comment;
    }
    
    public function setDuration($a_duration) : void
    {
        if (preg_match("/P(\d+)Y(\d+)M(\d+)DT(\d+)H(\d+)M(\d+)S/", $a_duration, $matches)) {
            $this->duration = array(
                "h" => $matches[4],
                "m" => $matches[5],
                "s" => $matches[6]
            );
        }
    }
    
    public function getDuration()
    {
        return $this->duration;
    }
    
    public function setXmllang($a_xmllang) : void
    {
        $this->xmllang = $a_xmllang;
    }
    
    public function getXmllang()
    {
        return $this->xmllang;
    }

    /**
     * @param arrary ['label' => string, 'entry' => string] $a_metadata
     */
    public function addQtiMetadata($a_metadata) : void
    {
        $this->qtimetadata[] = $a_metadata;
    }

    /**
     * @param ilQTIObjectives $a_objectives
     */
    public function addObjectives($a_objectives) : void
    {
        $this->objectives[] = $a_objectives;
    }

    /**
     * @param ilQTIAssessmentcontrol
     */
    public function addAssessmentcontrol($a_assessmentcontrol) : void
    {
        $this->assessmentcontrol[] = $a_assessmentcontrol;
    }

    /**
     * Never used.
     */
    public function addRubric($a_rubric) : void
    {
        $this->rubric[] = $a_rubric;
    }

    /**
     * {@inheritdoc}
     */
    public function setPresentationMaterial(ilQTIPresentationMaterial $a_material) : void
    {
        $this->presentation_material = $a_material;
    }

    /**
     * {@inheritdoc}
     */
    public function getPresentationMaterial()
    {
        return $this->presentation_material;
    }
    
    public function addOutcomesProcessing($a_outcomes_processing) : void
    {
        $this->outcomes_processing[] = $a_outcomes_processing;
    }

    /**
     * Never used.
     */
    public function setAssessprocExtension($a_assessproc_extension) : void
    {
        $this->assessproc_extension = $a_assessproc_extension;
    }

    /**
     * Never used.
     */
    public function getAssessprocExtension()
    {
        return $this->assessproc_extension;
    }

    /**
     * Never used.
     */
    public function addAssessfeedback($a_assessfeedback) : void
    {
        $this->assessfeedback[] = $a_assessfeedback;
    }

    /**
     * Never used.
     */
    public function setSelectionOrdering($a_selection_ordering) : void
    {
        $this->selection_ordering = $a_selection_ordering;
    }

    /**
     * Never used.
     */
    public function getSelectionOrdering()
    {
        return $this->selection_ordering;
    }

    public function setReference($a_reference) : void
    {
        $this->reference = $a_reference;
    }
    
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * Never used.
     */
    public function addSectionref($a_sectionref) : void
    {
        $this->sectionref[] = $a_sectionref;
    }

    public function addSection($a_section) : void
    {
        $this->section[] = $a_section;
    }
}
