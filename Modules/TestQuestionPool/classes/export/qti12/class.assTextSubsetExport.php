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

include_once "./Modules/TestQuestionPool/classes/export/qti12/class.assQuestionExport.php";

/**
* Class for text subset question exports
*
* assTextSubsetExport is a class for text subset question exports
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesTestQuestionPool
*/
class assTextSubsetExport extends assQuestionExport
{
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
		global $ilias;
		
		include_once("./classes/class.ilXmlWriter.php");
		$a_xml_writer = new ilXmlWriter;
		// set xml header
		$a_xml_writer->xmlHeader();
		$a_xml_writer->xmlStartTag("questestinterop");
		$attrs = array(
			"ident" => "il_".IL_INST_ID."_qst_".$this->object->getId(),
			"title" => $this->object->getTitle()
		);
		$a_xml_writer->xmlStartTag("item", $attrs);
		// add question description
		$a_xml_writer->xmlElement("qticomment", NULL, $this->object->getComment());
		// add estimated working time
		$workingtime = $this->object->getEstimatedWorkingTime();
		$duration = sprintf("P0Y0M0DT%dH%dM%dS", $workingtime["h"], $workingtime["m"], $workingtime["s"]);
		$a_xml_writer->xmlElement("duration", NULL, $duration);
		// add ILIAS specific metadata
		$a_xml_writer->xmlStartTag("itemmetadata");
		$a_xml_writer->xmlStartTag("qtimetadata");
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "ILIAS_VERSION");
		$a_xml_writer->xmlElement("fieldentry", NULL, $ilias->getSetting("ilias_version"));
		$a_xml_writer->xmlEndTag("qtimetadatafield");
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "QUESTIONTYPE");
		$a_xml_writer->xmlElement("fieldentry", NULL, TEXTSUBSET_QUESTION_IDENTIFIER);
		$a_xml_writer->xmlEndTag("qtimetadatafield");
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "AUTHOR");
		$a_xml_writer->xmlElement("fieldentry", NULL, $this->object->getAuthor());
		$a_xml_writer->xmlEndTag("qtimetadatafield");
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "textrating");
		$a_xml_writer->xmlElement("fieldentry", NULL, $this->object->getTextRating());
		$a_xml_writer->xmlEndTag("qtimetadatafield");
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "correctanswers");
		$a_xml_writer->xmlElement("fieldentry", NULL, $this->object->getCorrectAnswers());
		$a_xml_writer->xmlEndTag("qtimetadatafield");
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "points");
		$a_xml_writer->xmlElement("fieldentry", NULL, $this->object->getPoints());
		$a_xml_writer->xmlEndTag("qtimetadatafield");
		$a_xml_writer->xmlEndTag("qtimetadata");
		$a_xml_writer->xmlEndTag("itemmetadata");

		// PART I: qti presentation
		$attrs = array(
			"label" => $this->object->getTitle()
		);
		$a_xml_writer->xmlStartTag("presentation", $attrs);
		// add flow to presentation
		$a_xml_writer->xmlStartTag("flow");
		// add material with question text to presentation
		$this->object->addQTIMaterial($a_xml_writer, $this->object->getQuestion());
		// add answers to presentation
		for ($counter = 1; $counter <= $this->object->getCorrectAnswers(); $counter++)
		{
			$attrs = array(
				"ident" => "TEXTSUBSET_" . sprintf("%02d", $counter),
				"rcardinality" => "Single"
			);
			$a_xml_writer->xmlStartTag("response_str", $attrs);
			$solution = $this->object->getSuggestedSolution(0);
			if (count($solution))
			{
				if (preg_match("/il_(\d*?)_(\w+)_(\d+)/", $solution["internal_link"], $matches))
				{
					$a_xml_writer->xmlStartTag("material");
					$intlink = "il_" . IL_INST_ID . "_" . $matches[2] . "_" . $matches[3];
					if (strcmp($matches[1], "") != 0)
					{
						$intlink = $solution["internal_link"];
					}
					$attrs = array(
						"label" => "suggested_solution"
					);
					$a_xml_writer->xmlElement("mattext", $attrs, $intlink);
					$a_xml_writer->xmlEndTag("material");
				}
			}
			// shuffle output
			$attrs = array(
				"fibtype" => "String",
				"columns" => $this->object->getMaxTextboxWidth()
			);
			$a_xml_writer->xmlStartTag("render_fib", $attrs);
			$a_xml_writer->xmlEndTag("render_fib");
			$a_xml_writer->xmlEndTag("response_str");
		}
		
		$a_xml_writer->xmlEndTag("flow");
		$a_xml_writer->xmlEndTag("presentation");
		
		// PART II: qti resprocessing
		$a_xml_writer->xmlStartTag("resprocessing");
		$a_xml_writer->xmlStartTag("outcomes");
		$a_xml_writer->xmlStartTag("decvar");
		$a_xml_writer->xmlEndTag("decvar");
		$attribs = array(
			"varname" => "matches",
			"defaultval" => "0"
		);
		$a_xml_writer->xmlElement("decvar", $attribs, NULL);
		$a_xml_writer->xmlEndTag("outcomes");
		// add response conditions
		for ($counter = 1; $counter <= $this->object->getCorrectAnswers(); $counter++)
		{
			$scoregroups =& $this->object->joinAnswers();
			foreach ($scoregroups as $points => $scoreanswers)
			{
				$attrs = array(
					"continue" => "Yes"
				);
				$a_xml_writer->xmlStartTag("respcondition", $attrs);
				// qti conditionvar
				$a_xml_writer->xmlStartTag("conditionvar");
				$attrs = array(
					"respident" => "TEXTSUBSET_" . sprintf("%02d", $counter)
				);
				$a_xml_writer->xmlElement("varsubset", $attrs, join(",", $scoreanswers));
				$a_xml_writer->xmlEndTag("conditionvar");
				// qti setvar
				$attrs = array(
					"varname" => "matches",
					"action" => "Add"
				);
				$a_xml_writer->xmlElement("setvar", $attrs, $points);
				// qti displayfeedback
				$attrs = array(
					"feedbacktype" => "Response",
					"linkrefid" => "Matches_" . sprintf("%02d", $counter)
				);
				$a_xml_writer->xmlElement("displayfeedback", $attrs);
				$a_xml_writer->xmlEndTag("respcondition");
			}
		}

		$feedback_allcorrect = $this->object->getFeedbackGeneric(1);
		$feedback_onenotcorrect = $this->object->getFeedbackGeneric(0);
		if (strlen($feedback_allcorrect . $feedback_onenotcorrect))
		{
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
				$a_xml_writer->xmlElement("varsubset", $attrs, $this->object->getMaximumPoints());
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
				$a_xml_writer->xmlElement("varsubset", $attrs, $this->object->getMaximumPoints());

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
		}

		$a_xml_writer->xmlEndTag("resprocessing");

		// PART III: qti itemfeedback
		for ($counter = 1; $counter <= $this->object->getCorrectAnswers(); $counter++)
		{
			$attrs = array(
				"ident" => "Matches_" . sprintf("%02d", $counter),
				"view" => "All"
			);
			$a_xml_writer->xmlStartTag("itemfeedback", $attrs);
			// qti flow_mat
			$a_xml_writer->xmlStartTag("flow_mat");
			$a_xml_writer->xmlStartTag("material");
			$a_xml_writer->xmlElement("mattext");
			$a_xml_writer->xmlEndTag("material");
			$a_xml_writer->xmlEndTag("flow_mat");
			$a_xml_writer->xmlEndTag("itemfeedback");
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

		$a_xml_writer->xmlEndTag("item");
		$a_xml_writer->xmlEndTag("questestinterop");

		$xml = $a_xml_writer->xmlDumpMem(FALSE);
		if (!$a_include_header)
		{
			$pos = strpos($xml, "?>");
			$xml = substr($xml, $pos + 2);
		}
		return $xml;
	}
}

?>
