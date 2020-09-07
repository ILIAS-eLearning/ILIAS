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
    public $ident;
    public $title;
    public $xmllang;
    public $comment;
    public $duration;
    public $qtimetadata;
    public $objectives;
    public $assessmentcontrol;
    public $rubric;
    /**
     * @var ilQTIPresentationMaterial
     */
    protected $presentation_material;
    public $outcomes_processing;
    public $assessproc_extension;
    public $assessfeedback;
    public $selection_ordering;
    public $reference;
    public $sectionref;
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
    
    public function setIdent($a_ident)
    {
        $this->ident = $a_ident;
    }
    
    public function getIdent()
    {
        return $this->ident;
    }
    
    public function setTitle($a_title)
    {
        $this->title = $a_title;
    }
    
    public function getTitle()
    {
        return $this->title;
    }
    
    public function setComment($a_comment)
    {
        $this->comment = $a_comment;
    }
    
    public function getComment()
    {
        return $this->comment;
    }
    
    public function setDuration($a_duration)
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
    
    public function setXmllang($a_xmllang)
    {
        $this->xmllang = $a_xmllang;
    }
    
    public function getXmllang()
    {
        return $this->xmllang;
    }
    
    public function addQtiMetadata($a_metadata)
    {
        array_push($this->qtimetadata, $a_metadata);
    }
    
    public function addObjectives($a_objectives)
    {
        array_push($this->objectives, $a_objectives);
    }
    
    public function addAssessmentcontrol($a_assessmentcontrol)
    {
        array_push($this->assessmentcontrol, $a_assessmentcontrol);
    }
    
    public function addRubric($a_rubric)
    {
        array_push($this->rubric, $a_rubric);
    }

    /**
     * {@inheritdoc}
     */
    public function setPresentationMaterial(ilQTIPresentationMaterial $a_material)
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
    
    public function addOutcomesProcessing($a_outcomes_processing)
    {
        array_push($this->outcomes_processing, $a_outcomes_processing);
    }
    
    public function setAssessprocExtension($a_assessproc_extension)
    {
        $this->assessproc_extension = $a_assessproc_extension;
    }
    
    public function getAssessprocExtension()
    {
        return $this->assessproc_extension;
    }
    
    public function addAssessfeedback($a_assessfeedback)
    {
        array_push($this->assessfeedback, $a_assessfeedback);
    }
    
    public function setSelectionOrdering($a_selection_ordering)
    {
        $this->selection_ordering = $a_selection_ordering;
    }
    
    public function getSelectionOrdering()
    {
        return $this->selection_ordering;
    }
    
    public function setReference($a_reference)
    {
        $this->reference = $a_reference;
    }
    
    public function getReference()
    {
        return $this->reference;
    }
    
    public function addSectionref($a_sectionref)
    {
        array_push($this->sectionref, $a_sectionref);
    }
    
    public function addSection($a_section)
    {
        array_push($this->section, $a_section);
    }
}
