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
	var $ident;
	var $title;
	var $xmllang;
	var $comment;
	var $duration;
	var $qtimetadata;
	var $objectives;
	var $assessmentcontrol;
	var $rubric;
	/**
	 * @var ilQTIPresentationMaterial
	 */
	protected $presentation_material;
	var $outcomes_processing;
	var $assessproc_extension;
	var $assessfeedback;
	var $selection_ordering;
	var $reference;
	var $sectionref;
	var $section;
	
	function ilQTIAssessment()
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
	
	function addAssessmentcontrol($a_assessmentcontrol)
	{
		array_push($this->assessmentcontrol, $a_assessmentcontrol);
	}
	
	function addRubric($a_rubric)
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
	
	function addOutcomesProcessing($a_outcomes_processing)
	{
		array_push($this->outcomes_processing, $a_outcomes_processing);
	}
	
	function setAssessprocExtension($a_assessproc_extension)
	{
		$this->assessproc_extension = $a_assessproc_extension;
	}
	
	function getAssessprocExtension()
	{
		return $this->assessproc_extension;
	}
	
	function addAssessfeedback($a_assessfeedback)
	{
		array_push($this->assessfeedback, $a_assessfeedback);
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
