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
* QTI section class
*
* @author Helmut Schottmüller <hschottm@gmx.de>
* @version $Id$
*
* @package assessment
*/
class ilQTISection
{
    public $ident;
    public $title;
    public $xmllang;
    public $comment;

    /**
     * @var array|null ['h' => string, 'm' => string, 's' => string]
     */
    public $duration;

    /** @var array */
    public $qtimetadata;

    /** @var array */
    public $objectives;

    /** @var array */
    public $sectioncontrol;

    /** @var array */
    public $sectionprecondition;

    /** @var array */
    public $sectionpostcondition;

    /** @var array */
    public $rubric;
    public $presentation_material;

    /** @var array */
    public $outcomes_processing;
    public $sectionproc_extension;

    /** @var array */
    public $sectionfeedback;
    public $selection_ordering;
    public $reference;

    /** @var array */
    public $itemref;

    /** @var array */
    public $item;

    /** @var array */
    public $sectionref;

    /** @var array */
    public $section;
    
    public function __construct()
    {
        $this->qtimetadata = array();
        $this->objectives = array();
        $this->sectioncontrol = array();
        $this->sectionprecondition = array();
        $this->sectionpostcondition = array();
        $this->rubric = array();
        $this->outcomes_processing = array();
        $this->sectionfeedback = array();
        $this->itemref = array();
        $this->item = array();
        $this->sectionref = array();
        $this->section = array();
    }
    
    public function setIdent($a_ident) : void
    {
        $this->ident = $a_ident;
    }
    
    public function getIdent()
    {
        return $this->ident;
    }
    
    public function setTitle($a_title) : void
    {
        $this->title = $a_title;
    }
    
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

    /**
     * @param string $a_duration
     */
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

    /**
     * @return null|array ['h' => string, 'm' => string, 's' => string]
     */
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
    
    public function addQtiMetadata($a_metadata) : void
    {
        $this->qtimetadata[] = $a_metadata;
    }
    
    public function addObjectives($a_objectives) : void
    {
        $this->objectives[] = $a_objectives;
    }
    
    public function addSectioncontrol($a_sectioncontrol) : void
    {
        $this->sectioncontrol[] = $a_sectioncontrol;
    }
    
    public function addSectionprecondition($a_sectionprecondition) : void
    {
        $this->sectionprecondition[] = $a_sectionprecondition;
    }
    
    public function addSectionpostcondition($a_sectionpostcondition) : void
    {
        $this->sectionpostcondition[] = $a_sectionpostcondition;
    }
    
    public function addRubric($a_rubric) : void
    {
        $this->rubric[] = $a_rubric;
    }
    
    public function setPresentationMaterial($a_material) : void
    {
        $this->presentation_material = $a_material;
    }
    
    public function getPresentationMaterial()
    {
        return $this->presentation_material;
    }
    
    public function addOutcomesProcessing($a_outcomes_processing) : void
    {
        $this->outcomes_processing[] = $a_outcomes_processing;
    }
    
    public function setSectionprocExtension($a_sectionproc_extension) : void
    {
        $this->sectionproc_extension = $a_sectionproc_extension;
    }
    
    public function getSectionprocExtension()
    {
        return $this->sectionproc_extension;
    }
    
    public function addSectionfeedback($a_sectionfeedback) : void
    {
        $this->sectionfeedback[] = $a_sectionfeedback;
    }
    
    public function setSelectionOrdering($a_selection_ordering) : void
    {
        $this->selection_ordering = $a_selection_ordering;
    }
    
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
    
    public function addItemref($a_itemref) : void
    {
        $this->itemref[] = $a_itemref;
    }
    
    public function addItem($a_item) : void
    {
        $this->item[] = $a_item;
    }

    public function addSectionref($a_sectionref) : void
    {
        $this->sectionref[] = $a_sectionref;
    }
    
    public function addSection($a_section) : void
    {
        $this->section[] = $a_section;
    }
}
