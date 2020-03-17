<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Modules/TestQuestionPool/classes/export/qti12/class.assQuestionExport.php";

/**
* Class for single choice question exports
*
* assSingleChoiceExport is a class for single choice question exports
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesTestQuestionPool
*/
class assSingleChoiceExport extends assQuestionExport
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
    public function toXML($a_include_header = true, $a_include_binary = true, $a_shuffle = false, $test_output = false, $force_image_references = false)
    {
        global $DIC;
        $ilias = $DIC['ilias'];
        
        include_once("./Services/Xml/classes/class.ilXmlWriter.php");
        $a_xml_writer = new ilXmlWriter;
        // set xml header
        $a_xml_writer->xmlHeader();
        $a_xml_writer->xmlStartTag("questestinterop");
        $attrs = array(
            "ident" => "il_" . IL_INST_ID . "_qst_" . $this->object->getId(),
            "title" => $this->object->getTitle(),
            "maxattempts" => $this->object->getNrOfTries()
        );
        $a_xml_writer->xmlStartTag("item", $attrs);
        // add question description
        $a_xml_writer->xmlElement("qticomment", null, $this->object->getComment());
        // add estimated working time
        $workingtime = $this->object->getEstimatedWorkingTime();
        $duration = sprintf("P0Y0M0DT%dH%dM%dS", $workingtime["h"], $workingtime["m"], $workingtime["s"]);
        $a_xml_writer->xmlElement("duration", null, $duration);
        // add ILIAS specific metadata
        $a_xml_writer->xmlStartTag("itemmetadata");
        $a_xml_writer->xmlStartTag("qtimetadata");
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "ILIAS_VERSION");
        $a_xml_writer->xmlElement("fieldentry", null, $ilias->getSetting("ilias_version"));
        $a_xml_writer->xmlEndTag("qtimetadatafield");
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "QUESTIONTYPE");
        $a_xml_writer->xmlElement("fieldentry", null, SINGLE_CHOICE_QUESTION_IDENTIFIER);
        $a_xml_writer->xmlEndTag("qtimetadatafield");
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "AUTHOR");
        $a_xml_writer->xmlElement("fieldentry", null, $this->object->getAuthor());
        $a_xml_writer->xmlEndTag("qtimetadatafield");
        
        // additional content editing information
        $this->addAdditionalContentEditingModeInformation($a_xml_writer);
        $this->addGeneralMetadata($a_xml_writer);
        
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "thumb_size");
        $a_xml_writer->xmlElement("fieldentry", null, $this->object->getThumbSize());
        $a_xml_writer->xmlEndTag("qtimetadatafield");

        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "feedback_setting");
        $a_xml_writer->xmlElement("fieldentry", null, $this->object->getSpecificFeedbackSetting());
        $a_xml_writer->xmlEndTag("qtimetadatafield");

        $this->addQtiMetaDataField($a_xml_writer, 'singleline', $this->object->isSingleline ? 1 : 0);
        
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
        $attrs = array(
            "ident" => "MCSR",
            "rcardinality" => "Single"
        );
        $a_xml_writer->xmlStartTag("response_lid", $attrs);
        $solution = $this->object->getSuggestedSolution(0);
        if (count($solution)) {
            if (preg_match("/il_(\d*?)_(\w+)_(\d+)/", $solution["internal_link"], $matches)) {
                $a_xml_writer->xmlStartTag("material");
                $intlink = "il_" . IL_INST_ID . "_" . $matches[2] . "_" . $matches[3];
                if (strcmp($matches[1], "") != 0) {
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
        if ($this->object->getShuffle()) {
            $attrs = array(
                "shuffle" => "Yes"
            );
        } else {
            $attrs = array(
                "shuffle" => "No"
            );
        }
        $a_xml_writer->xmlStartTag("render_choice", $attrs);
        $answers = &$this->object->getAnswers();
        $akeys = array_keys($answers);
        if ($this->object->getShuffle() && $a_shuffle) {
            $akeys = $this->object->pcArrayShuffle($akeys);
        }
        // add answers
        foreach ($akeys as $index) {
            $answer = $answers[$index];
            $attrs = array(
                "ident" => $index
            );
            $a_xml_writer->xmlStartTag("response_label", $attrs);
            
            if (strlen($answer->getImage())) {
                $this->object->addQTIMaterial($a_xml_writer, $answer->getAnswertext(), false, false);
                $imagetype = "image/jpeg";
                if (preg_match("/.*\.(png|gif)$/", $answer->getImage(), $matches)) {
                    $imagetype = "image/" . $matches[1];
                }
                if ($force_image_references) {
                    $attrs = array(
                        "imagtype" => $imagetype,
                        "label" => $answer->getImage(),
                        "uri" => $this->object->getImagePathWeb() . $answer->getImage()
                    );
                    $a_xml_writer->xmlElement("matimage", $attrs);
                } else {
                    $imagepath = $this->object->getImagePath() . $answer->getImage();
                    $fh = @fopen($imagepath, "rb");
                    if ($fh != false) {
                        $imagefile = fread($fh, filesize($imagepath));
                        fclose($fh);
                        $base64 = base64_encode($imagefile);
                        $attrs = array(
                            "imagtype" => $imagetype,
                            "label" => $answer->getImage(),
                            "embedded" => "base64"
                        );
                        $a_xml_writer->xmlElement("matimage", $attrs, $base64, false, false);
                    }
                }
                $a_xml_writer->xmlEndTag("material");
            } else {
                $this->object->addQTIMaterial($a_xml_writer, $answer->getAnswertext());
            }
            $a_xml_writer->xmlEndTag("response_label");
        }
        $a_xml_writer->xmlEndTag("render_choice");
        $a_xml_writer->xmlEndTag("response_lid");
        $a_xml_writer->xmlEndTag("flow");
        $a_xml_writer->xmlEndTag("presentation");
        
        // PART II: qti resprocessing
        $a_xml_writer->xmlStartTag("resprocessing");
        $a_xml_writer->xmlStartTag("outcomes");
        $a_xml_writer->xmlStartTag("decvar");
        $a_xml_writer->xmlEndTag("decvar");
        $a_xml_writer->xmlEndTag("outcomes");
        // add response conditions
        foreach ($answers as $index => $answer) {
            $attrs = array(
                "continue" => "Yes"
            );
            $a_xml_writer->xmlStartTag("respcondition", $attrs);
            // qti conditionvar
            $a_xml_writer->xmlStartTag("conditionvar");
            $attrs = array();
            $attrs = array(
                "respident" => "MCSR"
            );
            $a_xml_writer->xmlElement("varequal", $attrs, $index);
            $a_xml_writer->xmlEndTag("conditionvar");
            // qti setvar
            $attrs = array(
                "action" => "Add"
            );
            $a_xml_writer->xmlElement("setvar", $attrs, $answer->getPoints());
            // qti displayfeedback
            $linkrefid = "response_$index";
            $attrs = array(
                "feedbacktype" => "Response",
                "linkrefid" => $linkrefid
            );
            $a_xml_writer->xmlElement("displayfeedback", $attrs);
            $a_xml_writer->xmlEndTag("respcondition");
        }

        $feedback_allcorrect = $this->object->feedbackOBJ->getGenericFeedbackExportPresentation(
            $this->object->getId(),
            true
        );
        if (strlen($feedback_allcorrect)) {
            $attrs = array(
                "continue" => "Yes"
            );
            $a_xml_writer->xmlStartTag("respcondition", $attrs);
            // qti conditionvar
            $a_xml_writer->xmlStartTag("conditionvar");
            $bestindex = 0;
            $maxpoints = 0;
            foreach ($answers as $index => $answer) {
                if ($answer->getPoints() > $maxpoints) {
                    $maxpoints = $answer->getPoints();
                    $bestindex = $index;
                }
            }
            $attrs = array(
                "respident" => "MCSR"
            );
            $a_xml_writer->xmlElement("varequal", $attrs, $bestindex);
            $a_xml_writer->xmlEndTag("conditionvar");
            // qti displayfeedback
            $attrs = array(
                "feedbacktype" => "Response",
                "linkrefid" => "response_allcorrect"
            );
            $a_xml_writer->xmlElement("displayfeedback", $attrs);
            $a_xml_writer->xmlEndTag("respcondition");
        }
        
        $feedback_onenotcorrect = $this->object->feedbackOBJ->getGenericFeedbackExportPresentation(
            $this->object->getId(),
            false
        );
        if (strlen($feedback_onenotcorrect)) {
            $attrs = array(
                "continue" => "Yes"
            );
            $a_xml_writer->xmlStartTag("respcondition", $attrs);
            // qti conditionvar
            $a_xml_writer->xmlStartTag("conditionvar");
            $bestindex = 0;
            $maxpoints = 0;
            foreach ($answers as $index => $answer) {
                if ($answer->getPoints() > $maxpoints) {
                    $maxpoints = $answer->getPoints();
                    $bestindex = $index;
                }
            }
            $attrs = array(
                "respident" => "MCSR"
            );
            $a_xml_writer->xmlStartTag("not");
            $a_xml_writer->xmlElement("varequal", $attrs, $bestindex);
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
        foreach ($answers as $index => $answer) {
            $linkrefid = "response_$index";
            $attrs = array(
                "ident" => $linkrefid,
                "view" => "All"
            );
            $a_xml_writer->xmlStartTag("itemfeedback", $attrs);
            // qti flow_mat
            $a_xml_writer->xmlStartTag("flow_mat");
            $fb = $this->object->feedbackOBJ->getSpecificAnswerFeedbackExportPresentation(
                $this->object->getId(),
                0,
                $index
            );
            $this->object->addQTIMaterial($a_xml_writer, $fb);
            $a_xml_writer->xmlEndTag("flow_mat");
            $a_xml_writer->xmlEndTag("itemfeedback");
        }
        if (strlen($feedback_allcorrect)) {
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
        if (strlen($feedback_onenotcorrect)) {
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

        $xml = $a_xml_writer->xmlDumpMem(false);
        if (!$a_include_header) {
            $pos = strpos($xml, "?>");
            $xml = substr($xml, $pos + 2);
        }
        return $xml;
    }
}
