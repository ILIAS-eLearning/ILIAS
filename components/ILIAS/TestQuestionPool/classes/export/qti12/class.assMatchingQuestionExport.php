<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

use ILIAS\Test\QuestionIdentifiers;

/**
* Class for matching question exports
*
* assMatchingQuestionExport is a class for matching question exports
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup components\ILIASTestQuestionPool
*/
class assMatchingQuestionExport extends assQuestionExport
{
    /**
    * Returns a QTI xml representation of the question
    * Returns a QTI xml representation of the question and sets the internal
    * domxml variable with the DOM XML representation of the QTI xml representation
    */
    public function toXML($a_include_header = true, $a_include_binary = true, $a_shuffle = false, $test_output = false, $force_image_references = false): string
    {
        global $DIC;
        $ilias = $DIC['ilias'];

        $a_xml_writer = new ilXmlWriter();
        // set xml header
        $a_xml_writer->xmlHeader();
        $a_xml_writer->xmlStartTag("questestinterop");
        $attrs = [
            "ident" => "il_" . IL_INST_ID . "_qst_" . $this->object->getId(),
            "title" => $this->object->getTitle(),
            "maxattempts" => $this->object->getNrOfTries()
        ];
        $a_xml_writer->xmlStartTag("item", $attrs);
        // add question description
        $a_xml_writer->xmlElement("qticomment", null, $this->object->getComment());
        $a_xml_writer->xmlStartTag("itemmetadata");
        $a_xml_writer->xmlStartTag("qtimetadata");
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "ILIAS_VERSION");
        $a_xml_writer->xmlElement("fieldentry", null, $ilias->getSetting("ilias_version"));
        $a_xml_writer->xmlEndTag("qtimetadatafield");
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "QUESTIONTYPE");
        $a_xml_writer->xmlElement("fieldentry", null, QuestionIdentifiers::MATCHING_QUESTION_IDENTIFIER);
        $a_xml_writer->xmlEndTag("qtimetadatafield");
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "AUTHOR");
        $a_xml_writer->xmlElement("fieldentry", null, $this->object->getAuthor());
        $a_xml_writer->xmlEndTag("qtimetadatafield");

        // additional content editing information
        $this->addAdditionalContentEditingModeInformation($a_xml_writer);
        $this->addGeneralMetadata($a_xml_writer);

        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "shuffle");
        $a_xml_writer->xmlElement("fieldentry", null, $this->object->getShuffle());
        $a_xml_writer->xmlEndTag("qtimetadatafield");
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "thumb_geometry");
        $a_xml_writer->xmlElement("fieldentry", null, $this->object->getThumbGeometry());
        $a_xml_writer->xmlEndTag("qtimetadatafield");
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, 'matching_mode');
        $a_xml_writer->xmlElement("fieldentry", null, $this->object->getMatchingMode());
        $a_xml_writer->xmlEndTag("qtimetadatafield");
        $a_xml_writer->xmlEndTag("qtimetadata");
        $a_xml_writer->xmlEndTag("itemmetadata");

        // PART I: qti presentation
        $attrs = [
            "label" => $this->object->getTitle()
        ];
        $a_xml_writer->xmlStartTag("presentation", $attrs);
        // add flow to presentation
        $a_xml_writer->xmlStartTag("flow");
        // add material with question text to presentation
        $this->addQTIMaterial($a_xml_writer, $this->object->getQuestion());
        // add answers to presentation
        $attrs = [
            "ident" => "MQ",
            "rcardinality" => "Multiple"
        ];
        $a_xml_writer->xmlStartTag("response_grp", $attrs);
        $solution = $this->object->getSuggestedSolution(0);
        if ($solution !== null) {
            $a_xml_writer = $this->addSuggestedSolutionLink($a_xml_writer, $solution);
        }
        // shuffle output
        $attrs = [];
        if ($this->object->getShuffle()) {
            $attrs = [
                "shuffle" => "Yes"
            ];
        } else {
            $attrs = [
                "shuffle" => "No"
            ];
        }
        $a_xml_writer->xmlStartTag("render_choice", $attrs);
        // add answertext
        $matchingtext_orders = [];
        foreach ($this->object->getMatchingPairs() as $index => $matchingpair) {
            array_push($matchingtext_orders, $matchingpair->getTerm()->getIdentifier());
        }

        $termids = [];
        foreach ($this->object->getTerms() as $term) {
            array_push($termids, $term->getidentifier());
        }
        // add answers
        foreach ($this->object->getDefinitions() as $definition) {
            $attrs = [
                "ident" => $definition->getIdentifier(),
                "match_max" => "1",
                "match_group" => join(",", $termids)
            ];
            $a_xml_writer->xmlStartTag("response_label", $attrs);
            $a_xml_writer->xmlStartTag("material");
            if (strlen($definition->getPicture())) {
                if ($force_image_references) {
                    $attrs = [
                        "imagtype" => "image/jpeg",
                        "label" => $definition->getPicture(),
                        "uri" => $this->object->getImagePathWeb() . $definition->getPicture()
                    ];
                    $a_xml_writer->xmlElement("matimage", $attrs);
                } else {
                    $imagepath = $this->object->getImagePath() . $definition->getPicture();
                    $fh = @fopen($imagepath, "rb");
                    if ($fh != false) {
                        $imagefile = fread($fh, filesize($imagepath));
                        fclose($fh);
                        $base64 = base64_encode($imagefile);
                        $attrs = [
                            "imagtype" => "image/jpeg",
                            "label" => $definition->getPicture(),
                            "embedded" => "base64"
                        ];
                        $a_xml_writer->xmlElement("matimage", $attrs, $base64, false, false);
                    }
                }
            }
            if (strlen($definition->getText())) {
                $attrs = [
                    "texttype" => "text/plain"
                ];
                if (ilUtil::isHTML($definition->getText())) {
                    $attrs["texttype"] = "text/xhtml";
                }
                $a_xml_writer->xmlElement("mattext", $attrs, $definition->getText());
            }
            $a_xml_writer->xmlEndTag("material");
            $a_xml_writer->xmlEndTag("response_label");
        }
        // add matchingtext
        foreach ($this->object->getTerms() as $term) {
            $attrs = [
                "ident" => $term->getIdentifier()
            ];
            $a_xml_writer->xmlStartTag("response_label", $attrs);
            $a_xml_writer->xmlStartTag("material");
            if (strlen($term->getPicture())) {
                if ($force_image_references) {
                    $attrs = [
                        "imagtype" => "image/jpeg",
                        "label" => $term->getPicture(),
                        "uri" => $this->object->getImagePathWeb() . $term->getPicture()
                    ];
                    $a_xml_writer->xmlElement("matimage", $attrs);
                } else {
                    $imagepath = $this->object->getImagePath() . $term->getPicture();
                    $fh = @fopen($imagepath, "rb");
                    if ($fh != false) {
                        $imagefile = fread($fh, filesize($imagepath));
                        fclose($fh);
                        $base64 = base64_encode($imagefile);
                        $attrs = [
                            "imagtype" => "image/jpeg",
                            "label" => $term->getPicture(),
                            "embedded" => "base64"
                        ];
                        $a_xml_writer->xmlElement("matimage", $attrs, $base64, false, false);
                    }
                }
            }
            if (strlen($term->getText())) {
                $attrs = [
                    "texttype" => "text/plain"
                ];
                if (method_exists($this->object, 'isHTML') && $this->object->isHTML($term->text)) {
                    $attrs["texttype"] = "text/xhtml";
                }
                $a_xml_writer->xmlElement("mattext", $attrs, $term->getText());
            }
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
        foreach ($this->object->getMatchingPairs() as $matchingpair) {
            $attrs = [
                "continue" => "Yes"
            ];
            $a_xml_writer->xmlStartTag("respcondition", $attrs);
            // qti conditionvar
            $a_xml_writer->xmlStartTag("conditionvar");
            $attrs = [
                "respident" => "MQ"
            ];
            $a_xml_writer->xmlElement("varsubset", $attrs, $matchingpair->getTerm()->getIdentifier() . "," . $matchingpair->getDefinition()->getidentifier());
            $a_xml_writer->xmlEndTag("conditionvar");

            // qti setvar
            $attrs = [
                "action" => "Add"
            ];
            $a_xml_writer->xmlElement("setvar", $attrs, $matchingpair->getPoints());
            // qti displayfeedback
            $attrs = [
                "feedbacktype" => "Response",
                "linkrefid" => "correct_" . $matchingpair->getTerm()->getIdentifier() . "_" . $matchingpair->getDefinition()->getIdentifier()
            ];
            $a_xml_writer->xmlElement("displayfeedback", $attrs);
            $a_xml_writer->xmlEndTag("respcondition");
        }

        $feedback_allcorrect = $this->object->feedbackOBJ->getGenericFeedbackExportPresentation(
            $this->object->getId(),
            true
        );
        if (strlen($feedback_allcorrect)) {
            $attrs = [
                "continue" => "Yes"
            ];
            $a_xml_writer->xmlStartTag("respcondition", $attrs);
            // qti conditionvar
            $a_xml_writer->xmlStartTag("conditionvar");

            foreach ($this->object->getMatchingPairs() as $matchingpair) {
                $attrs = [
                    "respident" => "MQ"
                ];
                $a_xml_writer->xmlElement("varsubset", $attrs, $matchingpair->getTerm()->getIdentifier() . "," . $matchingpair->getDefinition()->getIdentifier());
            }
            $a_xml_writer->xmlEndTag("conditionvar");
            // qti displayfeedback
            $attrs = [
                "feedbacktype" => "Response",
                "linkrefid" => "response_allcorrect"
            ];
            $a_xml_writer->xmlElement("displayfeedback", $attrs);
            $a_xml_writer->xmlEndTag("respcondition");
        }
        $feedback_onenotcorrect = $this->object->feedbackOBJ->getGenericFeedbackExportPresentation(
            $this->object->getId(),
            false
        );
        if (strlen($feedback_onenotcorrect)) {
            $attrs = [
                "continue" => "Yes"
            ];
            $a_xml_writer->xmlStartTag("respcondition", $attrs);
            // qti conditionvar
            $a_xml_writer->xmlStartTag("conditionvar");
            $a_xml_writer->xmlStartTag("not");
            foreach ($this->object->getMatchingPairs() as $matchingpair) {
                $attrs = [
                    "respident" => "MQ"
                ];
                $a_xml_writer->xmlElement("varsubset", $attrs, $matchingpair->getTerm()->getIdentifier() . "," . $matchingpair->getDefinition()->getIdentifier());
            }
            $a_xml_writer->xmlEndTag("not");
            $a_xml_writer->xmlEndTag("conditionvar");
            // qti displayfeedback
            $attrs = [
                "feedbacktype" => "Response",
                "linkrefid" => "response_onenotcorrect"
            ];
            $a_xml_writer->xmlElement("displayfeedback", $attrs);
            $a_xml_writer->xmlEndTag("respcondition");
        }

        $a_xml_writer->xmlEndTag("resprocessing");

        // PART III: qti itemfeedback
        foreach ($this->object->getMatchingPairs() as $index => $matchingpair) {
            $attrs = [
                "ident" => "correct_" . $matchingpair->getTerm()->getIdentifier() . "_" . $matchingpair->getDefinition()->getIdentifier(),
                "view" => "All"
            ];
            $a_xml_writer->xmlStartTag("itemfeedback", $attrs);
            // qti flow_mat
            $a_xml_writer->xmlStartTag("flow_mat");
            $a_xml_writer->xmlStartTag("material");
            $a_xml_writer->xmlElement("mattext", null, $this->object->feedbackOBJ->getSpecificAnswerFeedbackExportPresentation(
                $this->object->getId(),
                0,
                $index
            ));
            $a_xml_writer->xmlEndTag("material");
            $a_xml_writer->xmlEndTag("flow_mat");
            $a_xml_writer->xmlEndTag("itemfeedback");
        }

        if (strlen($feedback_allcorrect)) {
            $attrs = [
                "ident" => "response_allcorrect",
                "view" => "All"
            ];
            $a_xml_writer->xmlStartTag("itemfeedback", $attrs);
            // qti flow_mat
            $a_xml_writer->xmlStartTag("flow_mat");
            $this->addQTIMaterial($a_xml_writer, $feedback_allcorrect);
            $a_xml_writer->xmlEndTag("flow_mat");
            $a_xml_writer->xmlEndTag("itemfeedback");
        }
        if (strlen($feedback_onenotcorrect)) {
            $attrs = [
                "ident" => "response_onenotcorrect",
                "view" => "All"
            ];
            $a_xml_writer->xmlStartTag("itemfeedback", $attrs);
            // qti flow_mat
            $a_xml_writer->xmlStartTag("flow_mat");
            $this->addQTIMaterial($a_xml_writer, $feedback_onenotcorrect);
            $a_xml_writer->xmlEndTag("flow_mat");
            $a_xml_writer->xmlEndTag("itemfeedback");
        }

        $a_xml_writer = $this->addSolutionHints($a_xml_writer);

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
