<?php
 /*
   +----------------------------------------------------------------------------+
   | ILIAS open source                                                          |
   +----------------------------------------------------------------------------+
   | Copyright (c) 1998-2001 ILIAS open source, University of Cologne           |
   |                                                                            |
   | This program is free software; you can redistribute it and/or              |
   | modify it under the terms of the GNU General Public License                |
   | as published by the Free Software Foundation; either version 2             |
   | of the License, or (at your option) any later version.                     |
   |                                                                            |
   | This program is distributed in the hope that it will be useful,            |
   | but WITHOUT ANY WARRANTY; without even the implied warranty of             |
   | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the              |
   | GNU General Public License for more details.                               |
   |                                                                            |
   | You should have received a copy of the GNU General Public License          |
   | along with this program; if not, write to the Free Software                |
   | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA. |
   +----------------------------------------------------------------------------+
*/
include_once "./Modules/Test/classes/inc.AssessmentConstants.php";

/**
* Class for question exports
*
* exportQuestion is a basis class question exports
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesTestQuestionPool
*/
class assQuestionExport
{
	/**
	* The question object
	*
	* The question object
	*
	* @var object
	*/
	var $object;

	/**
	* assQuestionExport constructor
	*
	* @param object $a_object The question object
	* @access public
	*/
	function assQuestionExport(&$a_object)
	{
		$this->object =& $a_object;
	}
	
	function exportFeedbackOnly($a_xml_writer)
	{
		$feedback_allcorrect = $this->object->getFeedbackGeneric(1);
		$feedback_onenotcorrect = $this->object->getFeedbackGeneric(0);
		if (strlen($feedback_allcorrect . $feedback_onenotcorrect))
		{
			$a_xml_writer->xmlStartTag("resprocessing");
			$a_xml_writer->xmlStartTag("outcomes");
			$a_xml_writer->xmlStartTag("decvar");
			$a_xml_writer->xmlEndTag("decvar");
			$a_xml_writer->xmlEndTag("outcomes");

			if (strlen($feedback_allcorrect))
			{
				$attrs = array(
					"continue" => "Yes"
				);
				$a_xml_writer->xmlStartTag("respcondition", $attrs);
				// qti conditionvar
				$a_xml_writer->xmlStartTag("conditionvar");
				$attrs = array(
					"respident" => "points"
				);
				$a_xml_writer->xmlElement("varequal", $attrs, $this->object->getPoints());
				$a_xml_writer->xmlEndTag("conditionvar");
				// qti displayfeedback
				$attrs = array(
					"feedbacktype" => "Response",
					"linkrefid" => "response_allcorrect"
				);
				$a_xml_writer->xmlElement("displayfeedback", $attrs);
				$a_xml_writer->xmlEndTag("respcondition");
			}

			if (strlen($feedback_onenotcorrect))
			{
				$attrs = array(
					"continue" => "Yes"
				);
				$a_xml_writer->xmlStartTag("respcondition", $attrs);
				// qti conditionvar
				$a_xml_writer->xmlStartTag("conditionvar");
				$a_xml_writer->xmlStartTag("not");
				$attrs = array(
					"respident" => "points"
				);
				$a_xml_writer->xmlElement("varequal", $attrs, $this->object->getPoints());
				$a_xml_writer->xmlEndTag("not");
				$a_xml_writer->xmlEndTag("conditionvar");
				// qti displayfeedback
				$attrs = array(
					"feedbacktype" => "Response",
					"linkrefid" => "response_onenotcorrect"
				);
				$a_xml_writer->xmlElement("displayfeedback", $attrs);
				$a_xml_writer->xmlEndTag("respcondition");
			}
			$a_xml_writer->xmlEndTag("resprocessing");
		}

		if (strlen($feedback_allcorrect))
		{
			$attrs = array(
				"ident" => "response_allcorrect",
				"view" => "All"
			);
			$a_xml_writer->xmlStartTag("itemfeedback", $attrs);
			// qti flow_mat
			$a_xml_writer->xmlStartTag("flow_mat");
			$this->object->addQTIMaterial($a_xml_writer, $feedback_allcorrect);
			$a_xml_writer->xmlEndTag("flow_mat");
			$a_xml_writer->xmlEndTag("itemfeedback");
		}
		if (strlen($feedback_onenotcorrect))
		{
			$attrs = array(
				"ident" => "response_onenotcorrect",
				"view" => "All"
			);
			$a_xml_writer->xmlStartTag("itemfeedback", $attrs);
			// qti flow_mat
			$a_xml_writer->xmlStartTag("flow_mat");
			$this->object->addQTIMaterial($a_xml_writer, $feedback_onenotcorrect);
			$a_xml_writer->xmlEndTag("flow_mat");
			$a_xml_writer->xmlEndTag("itemfeedback");
		}
	}

	/**
	* Returns a QTI xml representation of the question
	*
	* Returns a QTI xml representation of the question and sets the internal
	* domxml variable with the DOM XML representation of the QTI xml representation
	*
	* @return string The QTI xml representation of the question
	* @access public
	*/
	function toXML($a_include_header = true, $a_include_binary = true, $a_shuffle = false, $test_output = false, $force_image_references = false)
	{
	}
}

?>
