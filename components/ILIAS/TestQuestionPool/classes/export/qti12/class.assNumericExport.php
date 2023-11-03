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

/**
* Class for numeric question exports
*
* assNumericExport is a class for numeric question exports
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesTestQuestionPool
*/
class assNumericExport extends assQuestionExport
{
    /**
    * Returns a QTI xml representation of the question
    * Returns a QTI xml representation of the question and sets the internal
    * domxml variable with the DOM XML representation of the QTI xml representation
    * @access public
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
        $a_xml_writer->xmlElement("fieldentry", null, NUMERIC_QUESTION_IDENTIFIER);
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
            "ident" => "NUM",
            "rcardinality" => "Single",
            "numtype" => "Decimal"
        ];
        $a_xml_writer->xmlStartTag("response_num", $attrs);
        $solution = $this->object->getSuggestedSolution(0);
        if ($solution !== null) {
            $a_xml_writer = $this->addSuggestedSolutionLink($a_xml_writer, $solution);
        }
        // shuffle output
        $attrs = [
            "fibtype" => "Decimal",
            "maxchars" => $this->object->getMaxChars()
        ];
        $a_xml_writer->xmlStartTag("render_fib", $attrs);
        $a_xml_writer->xmlEndTag("render_fib");
        $a_xml_writer->xmlEndTag("response_num");
        $a_xml_writer->xmlEndTag("flow");
        $a_xml_writer->xmlEndTag("presentation");

        // PART II: qti resprocessing
        $a_xml_writer->xmlStartTag("resprocessing");
        $a_xml_writer->xmlStartTag("outcomes");
        $a_xml_writer->xmlStartTag("decvar");
        $a_xml_writer->xmlEndTag("decvar");
        $a_xml_writer->xmlEndTag("outcomes");
        // add response conditions
        $a_xml_writer->xmlStartTag("respcondition");
        // qti conditionvar
        $a_xml_writer->xmlStartTag("conditionvar");
        $attrs = [
            "respident" => "NUM"
        ];
        $a_xml_writer->xmlElement("vargte", $attrs, $this->object->getLowerLimit());
        $a_xml_writer->xmlElement("varlte", $attrs, $this->object->getUpperLimit());
        $a_xml_writer->xmlEndTag("conditionvar");
        // qti setvar
        $attrs = [
            "action" => "Add"
        ];
        $a_xml_writer->xmlElement("setvar", $attrs, $this->object->getPoints());
        // qti displayfeedback
        $attrs = [
            "feedbacktype" => "Response",
            "linkrefid" => "Correct"
        ];
        $a_xml_writer->xmlElement("displayfeedback", $attrs);
        $a_xml_writer->xmlEndTag("respcondition");

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
            $attrs = [
                "respident" => "NUM"
            ];
            $a_xml_writer->xmlElement("vargte", $attrs, $this->object->getLowerLimit());
            $a_xml_writer->xmlElement("varlte", $attrs, $this->object->getUpperLimit());
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
            $attrs = [
                "respident" => "NUM"
            ];
            $a_xml_writer->xmlElement("vargte", $attrs, $this->object->getLowerLimit());
            $a_xml_writer->xmlElement("varlte", $attrs, $this->object->getUpperLimit());
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
        $attrs = [
            "ident" => "Correct",
            "view" => "All"
        ];
        $a_xml_writer->xmlStartTag("itemfeedback", $attrs);
        // qti flow_mat
        $a_xml_writer->xmlStartTag("flow_mat");
        $a_xml_writer->xmlStartTag("material");
        $a_xml_writer->xmlElement("mattext");
        $a_xml_writer->xmlEndTag("material");
        $a_xml_writer->xmlEndTag("flow_mat");
        $a_xml_writer->xmlEndTag("itemfeedback");
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
