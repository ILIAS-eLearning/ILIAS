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
	var $ident;
	var $title;
	var $xmllang;
	var $comment;
	var $duration;
	var $qtimetadata;
	var $objectives;
	var $sectioncontrol;
	var $sectionprecondition;
	var $sectionpostcondition;
	var $rubric;
	var $presentation_material;
	var $outcomes_processing;
	var $sectionproc_extension;
	var $sectionfeedback;
	var $selection_ordering;
	var $reference;
	var $itemref;
	var $item;
	var $sectionref;
	var $section;
	
	function ilQTISection()
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
	
	function setIdent($a_ident)
	{
		$this->ident = $a_ident;
	}
	
	function getIdent()
	{
		return $this->ident;
	}
	
	function setTitle($a_title)
	{
		$this->title = $a_title;
	}
	
	function getTitle()
	{
		return $this->title;
	}
	
	function setComment($a_comment)
	{
		$this->comment = $a_comment;
	}
	
	function getComment()
	{
		return $this->comment;
	}
	
	function setDuration($a_duration)
	{
		if (preg_match("/P(\d+)Y(\d+)M(\d+)DT(\d+)H(\d+)M(\d+)S/", $a_duration, $matches))
		{
			$this->duration = array(
				"h" => $matches[4], 
				"m" => $matches[5], 
				"s" => $matches[6]
			);
		}
	}
	
	function getDuration()
	{
		return $this->duration;
	}
	
	function setXmllang($a_xmllang)
	{
		$this->xmllang = $a_xmllang;
	}
	
	function getXmllang()
	{
		return $this->xmllang;
	}
	
	function addQtiMetadata($a_metadata)
	{
		array_push($this->qtimetadata, $a_metadata);
	}
	
	function addObjectives($a_objectives)
	{
		array_push($this->objectives, $a_objectives);
	}
	
	function addSectioncontrol($a_sectioncontrol)
	{
		array_push($this->sectioncontrol, $a_sectioncontrol);
	}
	
	function addSectionprecondition($a_sectionprecondition)
	{
		array_push($this->sectionprecondition, $a_sectionprecondition);
	}
	
	function addSectionpostcondition($a_sectionpostcondition)
	{
		array_push($this->sectionpostcondition, $a_sectionpostcondition);
	}
	
	function addRubric($a_rubric)
	{
		array_push($this->rubric, $a_rubric);
	}
	
	function setPresentationMaterial($a_material)
	{
		$this->presentation_material = $a_material;
	}
	
	function getPresentationMaterial()
	{
		return $this->presentation_material;
	}
	
	function addOutcomesProcessing($a_outcomes_processing)
	{
		array_push($this->outcomes_processing, $a_outcomes_processing);
	}
	
	function setSectionprocExtension($a_sectionproc_extension)
	{
		$this->sectionproc_extension = $a_sectionproc_extension;
	}
	
	function getSectionprocExtension()
	{
		return $this->sectionproc_extension;
	}
	
	function addSectionfeedback($a_sectionfeedback)
	{
		array_push($this->sectionfeedback, $a_sectionfeedback);
	}
	
	function setSelectionOrdering($a_selection_ordering)
	{
		$this->selection_ordering = $a_selection_ordering;
	}
	
	function getSelectionOrdering()
	{
		return $this->selection_ordering;
	}
	
	function setReference($a_reference)
	{
		$this->reference = $a_reference;
	}
	
	function getReference()
	{
		return $this->reference;
	}
	
	function addItemref($a_itemref)
	{
		array_push($this->itemref, $a_itemref);
	}
	
	function addItem($a_item)
	{
		array_push($this->item, $a_item);
	}

	function addSectionref($a_sectionref)
	{
		array_push($this->sectionref, $a_sectionref);
	}
	
	function addSection($a_section)
	{
		array_push($this->section, $a_section);
	}
}
?>
