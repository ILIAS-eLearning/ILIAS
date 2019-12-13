<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Modules/TestQuestionPool/classes/export/qti12/class.assQuestionExport.php";

/**
* Class for imagemap question exports
*
* assImagemapQuestionExport is a class for imagemap question exports
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesTestQuestionPool
*/
class assImagemapQuestionExport extends assQuestionExport
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
        $a_xml_writer->xmlElement("fieldentry", null, IMAGEMAP_QUESTION_IDENTIFIER);
        $a_xml_writer->xmlEndTag("qtimetadatafield");
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "IS_MULTIPLE_CHOICE");
        $a_xml_writer->xmlElement("fieldentry", null, ($this->object->getIsMultipleChoice())? "1": "0");
        $a_xml_writer->xmlEndTag("qtimetadatafield");
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "AUTHOR");
        $a_xml_writer->xmlElement("fieldentry", null, $this->object->getAuthor());
        $a_xml_writer->xmlEndTag("qtimetadatafield");
        
        // additional content editing information
        $this->addAdditionalContentEditingModeInformation($a_xml_writer);
        $this->addGeneralMetadata($a_xml_writer);
        
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
        $attrs = array(
            "ident" => "IM",
            "rcardinality" => "Single"
        );
        $a_xml_writer->xmlStartTag("response_xy", $attrs);
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
        $a_xml_writer->xmlStartTag("render_hotspot");
        $a_xml_writer->xmlStartTag("material");
        $imagetype = "image/jpeg";
        if (preg_match("/.*\.(png|gif)$/i", $this->object->getImageFilename(), $matches)) {
            $imagetype = "image/" . strtolower($matches[1]);
        }
        $attrs = array(
            "imagtype" => $imagetype,
            "label" => $this->object->getImageFilename()
        );
        if ($a_include_binary) {
            if ($force_image_references) {
                $attrs["uri"] = $this->object->getImagePathWeb() . $this->object->getImageFilename();
                $a_xml_writer->xmlElement("matimage", $attrs);
            } else {
                $attrs["embedded"] = "base64";
                $imagepath = $this->object->getImagePath() . $this->object->getImageFilename();
                $fh = fopen($imagepath, "rb");
                if ($fh == false) {
                    global $DIC;
                    $ilErr = $DIC['ilErr'];
                    $ilErr->raiseError($GLOBALS['DIC']['lng']->txt("error_open_image_file"), $ilErr->MESSAGE);
                    return;
                }
                $imagefile = fread($fh, filesize($imagepath));
                fclose($fh);
                $base64 = base64_encode($imagefile);
                $a_xml_writer->xmlElement("matimage", $attrs, $base64, false, false);
            }
        } else {
            $a_xml_writer->xmlElement("matimage", $attrs);
        }
        $a_xml_writer->xmlEndTag("material");

        // add answers
        foreach ($this->object->getAnswers() as $index => $answer) {
            $rared = "";
            switch ($answer->getArea()) {
                case "rect":
                    $rarea = "Rectangle";
                    break;
                case "circle":
                    $rarea = "Ellipse";
                    break;
                case "poly":
                    $rarea = "Bounded";
                    break;
            }
            $attrs = array(
                "ident" => $index,
                "rarea" => $rarea
            );
            $a_xml_writer->xmlStartTag("response_label", $attrs);
            $a_xml_writer->xmlData($answer->getCoords());
            $a_xml_writer->xmlStartTag("material");
            $a_xml_writer->xmlElement("mattext", null, $answer->getAnswertext());
            $a_xml_writer->xmlEndTag("material");
            $a_xml_writer->xmlEndTag("response_label");
        }
        $a_xml_writer->xmlEndTag("render_hotspot");
        $a_xml_writer->xmlEndTag("response_xy");
        $a_xml_writer->xmlEndTag("flow");
        $a_xml_writer->xmlEndTag("presentation");

        // PART II: qti resprocessing
        $a_xml_writer->xmlStartTag("resprocessing");
        $a_xml_writer->xmlStartTag("outcomes");
        $a_xml_writer->xmlStartTag("decvar");
        $a_xml_writer->xmlEndTag("decvar");
        $a_xml_writer->xmlEndTag("outcomes");
        // add response conditions
        foreach ($this->object->getAnswers() as $index => $answer) {
            $attrs = array(
                "continue" => "Yes"
            );
            $a_xml_writer->xmlStartTag("respcondition", $attrs);
            // qti conditionvar
            $a_xml_writer->xmlStartTag("conditionvar");
            if (!$answer->isStateSet()) {
                $a_xml_writer->xmlStartTag("not");
            }
            $areatype = "";
            switch ($answer->getArea()) {
                case "rect":
                    $areatype = "Rectangle";
                    break;
                case "circle":
                    $areatype = "Ellipse";
                    break;
                case "poly":
                    $areatype = "Bounded";
                    break;
            }
            $attrs = array(
                "respident" => "IM",
                "areatype" => $areatype
            );
            $a_xml_writer->xmlElement("varequal", $attrs, $answer->getCoords());
            if (!$answer->isStateSet()) {
                $a_xml_writer->xmlEndTag("not");
            }
            $a_xml_writer->xmlEndTag("conditionvar");
            // qti setvar
            $attrs = array(
                "action" => "Add"
            );
            $a_xml_writer->xmlElement("setvar", $attrs, $answer->getPoints());
            $linkrefid = "response_$index";
            $attrs = array(
                "feedbacktype" => "Response",
                "linkrefid" => $linkrefid
            );
            $a_xml_writer->xmlElement("displayfeedback", $attrs);
            $a_xml_writer->xmlEndTag("respcondition");
            $attrs = array(
                "continue" => "Yes"
            );
            $a_xml_writer->xmlStartTag("respcondition", $attrs);
            // qti conditionvar
            $a_xml_writer->xmlStartTag("conditionvar");
            $attrs = array(
                "respident" => "IM"
            );
            $a_xml_writer->xmlStartTag("not");
            $a_xml_writer->xmlElement("varequal", $attrs, $answer->getCoords());
            $a_xml_writer->xmlEndTag("not");
            $a_xml_writer->xmlEndTag("conditionvar");
            // qti setvar
            $attrs = array(
                "action" => "Add"
            );
            $a_xml_writer->xmlElement("setvar", $attrs, $answer->getPointsUnchecked());
            $a_xml_writer->xmlEndTag("respcondition");
        }

        $answers = $this->object->getAnswers();
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
            if (!$this->object->getIsMultipleChoice()) {
                $bestindex = 0;
                $maxpoints = 0;
                foreach ($answers as $index => $answer) {
                    if ($answer->getPoints() > $maxpoints) {
                        $maxpoints = $answer->getPoints();
                        $bestindex = $index;
                    }
                }
                $attrs = array(
                    "respident" => "IM"
                );
    
                $areatype = "";
                $answer = $answers[$bestindex];
                switch ($answer->getArea()) {
                    case "rect":
                        $areatype = "Rectangle";
                        break;
                    case "circle":
                        $areatype = "Ellipse";
                        break;
                    case "poly":
                        $areatype = "Bounded";
                        break;
                }
                $attrs = array(
                    "respident" => "IM",
                    "areatype" => $areatype
                );
                $a_xml_writer->xmlElement("varinside", $attrs, $answer->getCoords());
            } else {
                foreach ($answers as $index => $answer) {
                    if ($answer->getPoints() < $answer->getPointsUnchecked()) {
                        $a_xml_writer->xmlStartTag("not");
                    }
                    switch ($answer->getArea()) {
                        case "rect":
                            $areatype = "Rectangle";
                            break;
                        case "circle":
                            $areatype = "Ellipse";
                            break;
                        case "poly":
                            $areatype = "Bounded";
                            break;
                    }
                    $attrs = array(
                        "respident" => "IM",
                        "areatype" => $areatype
                    );
                    $a_xml_writer->xmlElement("varequal", $attrs, $index);
                    if ($answer->getPoints() < $answer->getPointsUnchecked()) {
                        $a_xml_writer->xmlEndTag("not");
                    }
                }
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
            if (!$this->object->getIsMultipleChoice()) {
                $bestindex = 0;
                $maxpoints = 0;
                foreach ($answers as $index => $answer) {
                    if ($answer->getPoints() > $maxpoints) {
                        $maxpoints = $answer->getPoints();
                        $bestindex = $index;
                    }
                }
                $attrs = array(
                    "respident" => "IM"
                );
                $a_xml_writer->xmlStartTag("not");

                $areatype = "";
                $answer = $answers[$bestindex];
                switch ($answer->getArea()) {
                    case "rect":
                        $areatype = "Rectangle";
                        break;
                    case "circle":
                        $areatype = "Ellipse";
                        break;
                    case "poly":
                        $areatype = "Bounded";
                        break;
                }
                $attrs = array(
                    "respident" => "IM",
                    "areatype" => $areatype
                );
                $a_xml_writer->xmlElement("varinside", $attrs, $answer->getCoords());

                $a_xml_writer->xmlEndTag("not");
            } else {
                foreach ($answers as $index => $answer) {
                    if ($index > 0) {
                        $a_xml_writer->xmlStartTag("or");
                    }
                    if ($answer->getPoints() >= $answer->getPointsUnchecked()) {
                        $a_xml_writer->xmlStartTag("not");
                    }
                    switch ($answer->getArea()) {
                        case "rect":
                            $areatype = "Rectangle";
                            break;
                        case "circle":
                            $areatype = "Ellipse";
                            break;
                        case "poly":
                            $areatype = "Bounded";
                            break;
                    }
                    $attrs = array(
                        "respident" => "IM",
                        "areatype" => $areatype
                    );
                    $a_xml_writer->xmlElement("varequal", $attrs, $index);
                    if ($answer->getPoints() >= $answer->getPointsUnchecked()) {
                        $a_xml_writer->xmlEndTag("not");
                    }
                    if ($index > 0) {
                        $a_xml_writer->xmlEndTag("or");
                    }
                }
            }
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
        foreach ($this->object->getAnswers() as $index => $answer) {
            $linkrefid = "";
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
