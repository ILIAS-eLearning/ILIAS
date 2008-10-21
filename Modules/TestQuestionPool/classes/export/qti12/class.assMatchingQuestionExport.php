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
* Class for matching question exports
*
* assMatchingQuestionExport is a class for matching question exports
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesTestQuestionPool
*/
class assMatchingQuestionExport extends assQuestionExport
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
		$a_xml_writer->xmlElement("fieldentry", NULL, MATCHING_QUESTION_IDENTIFIER);
		$a_xml_writer->xmlEndTag("qtimetadatafield");
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "AUTHOR");
		$a_xml_writer->xmlElement("fieldentry", NULL, $this->object->getAuthor());
		$a_xml_writer->xmlEndTag("qtimetadatafield");
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "shuffle");
		$a_xml_writer->xmlElement("fieldentry", NULL, $this->object->getShuffle());
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
		$attrs = array();
		if ($this->object->getMatchingType() == MT_TERMS_PICTURES)
		{
			$attrs = array(
				"ident" => "MQP",
				"rcardinality" => "Multiple"
			);
		}
		else
		{
			$attrs = array(
				"ident" => "MQT",
				"rcardinality" => "Multiple"
			);
		}
		if ($this->object->getOutputType() == OUTPUT_JAVASCRIPT)
		{
			$attrs["output"] = "javascript";
		}
		$a_xml_writer->xmlStartTag("response_grp", $attrs);
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
		$attrs = array();
		if ($this->object->getShuffle())
		{
			$attrs = array(
				"shuffle" => "Yes"
			);
		}
		else
		{
			$attrs = array(
				"shuffle" => "No"
			);
		}
		$a_xml_writer->xmlStartTag("render_choice", $attrs);
		// add answertext
		$matchingtext_orders = array();
		foreach ($this->object->getMatchingPairs() as $index => $matchingpair)
		{
			array_push($matchingtext_orders, $matchingpair->getTermId());
		}

		// shuffle it
		$pkeys = array_keys($this->object->getMatchingPairs());
		if ($this->object->getShuffle() && $a_shuffle)
		{
			$pkeys = $this->object->pcArrayShuffle($pkeys);
		}
		// add answers
		foreach ($pkeys as $index)
		{
			$matchingpair = $this->object->getMatchingPair($index);
			$attrs = array(
				"ident" => $matchingpair->getDefinitionId(),
				"match_max" => "1",
				"match_group" => join($matchingtext_orders, ",")
			);
			$a_xml_writer->xmlStartTag("response_label", $attrs);
			if ($this->object->getMatchingType() == MT_TERMS_PICTURES)
			{
				$a_xml_writer->xmlStartTag("material");
				if ($force_image_references)
				{
					$attrs = array(
						"imagtype" => "image/jpeg",
						"label" => $matchingpair->getPicture(),
						"uri" => $this->object->getImagePathWeb() . $matchingpair->getPicture()
					);
					$a_xml_writer->xmlElement("matimage", $attrs);
				}
				else
				{
					$imagepath = $this->object->getImagePath() . $matchingpair->getPicture();
					$fh = @fopen($imagepath, "rb");
					if ($fh != false)
					{
						$imagefile = fread($fh, filesize($imagepath));
						fclose($fh);
						$base64 = base64_encode($imagefile);
						$attrs = array(
							"imagtype" => "image/jpeg",
							"label" => $matchingpair->getPicture(),
							"embedded" => "base64"
						);
						$a_xml_writer->xmlElement("matimage", $attrs, $base64, FALSE, FALSE);
					}
				}
				$a_xml_writer->xmlEndTag("material");
			}
			else
			{
				$a_xml_writer->xmlStartTag("material");
				$this->object->addQTIMaterial($a_xml_writer, $matchingpair->getDefinition(), TRUE, FALSE);
				$a_xml_writer->xmlEndTag("material");
			}
			$a_xml_writer->xmlEndTag("response_label");
		}
		// add matchingtext
		foreach ($this->object->getTerms() as $index => $term)
		{
			$attrs = array(
				"ident" => $index
			);
			$a_xml_writer->xmlStartTag("response_label", $attrs);
			$a_xml_writer->xmlStartTag("material");
			$attrs = array(
				"texttype" => "text/plain"
			);
			if ($this->object->isHTML($term))
			{
				$attrs["texttype"] = "text/xhtml";
			}
			$a_xml_writer->xmlElement("mattext", $attrs, $term);
			$a_xml_writer->xmlEndTag("material");
			$a_xml_writer->xmlEndTag("response_label");
		}
		$a_xml_writer->xmlEndTag("render_choice");
		$a_xml_writer->xmlEndTag("response_grp");
		$a_xml_writer->xmlEndTag("flow");
		$a_xml_writer->xmlEndTag("presentation");

		// PART II: qti resprocessing
		$a_xml_writer->xmlStartTag("resprocessing");
		$a_xml_writer->xmlStartTag("outcomes");
		$a_xml_writer->xmlStartTag("decvar");
		$a_xml_writer->xmlEndTag("decvar");
		$a_xml_writer->xmlEndTag("outcomes");
		// add response conditions
		foreach ($this->object->getMatchingPairs() as $index => $matchingpair)
		{
			$attrs = array(
				"continue" => "Yes"
			);
			$a_xml_writer->xmlStartTag("respcondition", $attrs);
			// qti conditionvar
			$a_xml_writer->xmlStartTag("conditionvar");
			$attrs = array();
			if ($this->object->getMatchingType() == MT_TERMS_PICTURES)
			{
				$attrs = array(
					"respident" => "MQP"
				);
			}
				else
			{
				$attrs = array(
					"respident" => "MQT"
				);
			}
			$a_xml_writer->xmlElement("varsubset", $attrs, $matchingpair->getTermId() . "," . $matchingpair->getDefinitionId());
			$a_xml_writer->xmlEndTag("conditionvar");

			// qti setvar
			$attrs = array(
				"action" => "Add"
			);
			$a_xml_writer->xmlElement("setvar", $attrs, $matchingpair->getPoints());
			// qti displayfeedback
			$attrs = array(
				"feedbacktype" => "Response",
				"linkrefid" => "correct_" . $matchingpair->getTermId() . "_" . $matchingpair->getDefinitionId()
			);
			$a_xml_writer->xmlElement("displayfeedback", $attrs);
			$a_xml_writer->xmlEndTag("respcondition");
		}

		$feedback_allcorrect = $this->object->getFeedbackGeneric(1);
		if (strlen($feedback_allcorrect))
		{
			$attrs = array(
				"continue" => "Yes"
			);
			$a_xml_writer->xmlStartTag("respcondition", $attrs);
			// qti conditionvar
			$a_xml_writer->xmlStartTag("conditionvar");

			foreach ($this->object->getMatchingPairs() as $index => $matchingpair)
			{
				$attrs = array();
				if ($this->object->getMatchingType() == MT_TERMS_PICTURES)
				{
					$attrs = array(
						"respident" => "MQP"
					);
				}
					else
				{
					$attrs = array(
						"respident" => "MQT"
					);
				}
				$a_xml_writer->xmlElement("varsubset", $attrs, $matchingpair->getTermId() . "," . $matchingpair->getDefinitionId());
			}
			$a_xml_writer->xmlEndTag("conditionvar");
			// qti displayfeedback
			$attrs = array(
				"feedbacktype" => "Response",
				"linkrefid" => "response_allcorrect"
			);
			$a_xml_writer->xmlElement("displayfeedback", $attrs);
			$a_xml_writer->xmlEndTag("respcondition");
		}
		$feedback_onenotcorrect = $this->object->getFeedbackGeneric(0);
		if (strlen($feedback_onenotcorrect))
		{
			$attrs = array(
				"continue" => "Yes"
			);
			$a_xml_writer->xmlStartTag("respcondition", $attrs);
			// qti conditionvar
			$a_xml_writer->xmlStartTag("conditionvar");
			$a_xml_writer->xmlStartTag("not");
			foreach ($this->object->getMatchingPairs() as $index => $matchingpair)
			{
				$attrs = array();
				if ($this->object->getMatchingType() == MT_TERMS_PICTURES)
				{
					$attrs = array(
						"respident" => "MQP"
					);
				}
					else
				{
					$attrs = array(
						"respident" => "MQT"
					);
				}
				$a_xml_writer->xmlElement("varsubset", $attrs, $matchingpair->getTermId() . "," . $matchingpair->getDefinitionId());
			}
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

		// PART III: qti itemfeedback
		foreach ($this->object->getMatchingPairs() as $index => $matchingpair)
		{
			$attrs = array(
				"ident" => "correct_" . $matchingpair->getTermId() . "_" . $matchingpair->getDefinitionId(),
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
