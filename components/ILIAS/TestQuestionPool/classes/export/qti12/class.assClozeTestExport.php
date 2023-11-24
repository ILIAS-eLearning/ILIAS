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

use ILIAS\Refinery\Random\Group as RandomGroup;
use ILIAS\Test\QuestionIdentifiers;

/**
* Class for cloze question exports
*
* assClozeTestExport is a class for cloze question exports
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup components\ILIASTestQuestionPool
*/
class assClozeTestExport extends assQuestionExport
{
    private RandomGroup $randomGroup;

    public function __construct($object)
    {
        global $DIC;

        parent::__construct($object);

        $this->randomGroup = $DIC->refinery()->random();
    }

    /**
    * Returns a QTI xml representation of the question
    * Returns a QTI xml representation of the question and sets the internal
    * domxml variable with the DOM XML representation of the QTI xml representation
    */
    public function toXML($a_include_header = true, $a_include_binary = true, $a_shuffle = false, $test_output = false, $force_image_references = false): string
    {
        global $DIC;
        $ilias = $DIC['ilias'];

        $eval = new EvalMath();
        $eval->suppress_errors = true;

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
        $a_xml_writer->xmlElement("fieldentry", null, QuestionIdentifiers::CLOZE_TEST_IDENTIFIER);
        $a_xml_writer->xmlEndTag("qtimetadatafield");
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "AUTHOR");
        $a_xml_writer->xmlElement("fieldentry", null, $this->object->getAuthor());
        $a_xml_writer->xmlEndTag("qtimetadatafield");

        // additional content editing information
        $this->addAdditionalContentEditingModeInformation($a_xml_writer);
        $this->addGeneralMetadata($a_xml_writer);

        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "textgaprating");
        $a_xml_writer->xmlElement("fieldentry", null, $this->object->getTextgapRating());
        $a_xml_writer->xmlEndTag("qtimetadatafield");

        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "fixedTextLength");
        $a_xml_writer->xmlElement("fieldentry", null, $this->object->getFixedTextLength());
        $a_xml_writer->xmlEndTag("qtimetadatafield");

        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "identicalScoring");
        $a_xml_writer->xmlElement("fieldentry", null, $this->object->getIdenticalScoring());
        $a_xml_writer->xmlEndTag("qtimetadatafield");

        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "feedback_mode");
        $a_xml_writer->xmlElement("fieldentry", null, $this->object->getFeedbackMode());
        $a_xml_writer->xmlEndTag("qtimetadatafield");

        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "combinations");
        $a_xml_writer->xmlElement("fieldentry", null, base64_encode(json_encode($this->object->getGapCombinations())));
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

        $questionText = $this->object->getQuestion() ? $this->object->getQuestion() : '&nbsp;';
        $this->addQTIMaterial($a_xml_writer, $questionText);

        $text_parts = preg_split("/\[gap.*?\[\/gap\]/", $this->object->getClozeText());

        // add material with question text to presentation
        for ($i = 0; $i <= $this->object->getGapCount(); $i++) {
            $this->addQTIMaterial($a_xml_writer, $text_parts[$i]);

            if ($i < $this->object->getGapCount()) {
                // add gap
                $gap = $this->object->getGap($i);
                switch ($gap->getType()) {
                    case assClozeGap::TYPE_SELECT:
                        // comboboxes
                        $attrs = [
                            "ident" => "gap_$i",
                            "rcardinality" => "Single"
                        ];
                        $a_xml_writer->xmlStartTag("response_str", $attrs);
                        $solution = $this->object->getSuggestedSolution($i);
                        if ($solution !== null) {
                            $a_xml_writer = $this->addSuggestedSolutionLink($a_xml_writer, $solution);
                        }

                        $attrs = ["shuffle" => ($gap->getShuffle() ? "Yes" : "No")];
                        $a_xml_writer->xmlStartTag("render_choice", $attrs);

                        // add answers
                        foreach ($gap->getItems($this->randomGroup->dontShuffle()) as $answeritem) {
                            $attrs = [
                                "ident" => $answeritem->getOrder()
                            ];
                            $a_xml_writer->xmlStartTag("response_label", $attrs);
                            $a_xml_writer->xmlStartTag("material");
                            $a_xml_writer->xmlElement("mattext", null, $answeritem->getAnswertext());
                            $a_xml_writer->xmlEndTag("material");
                            $a_xml_writer->xmlEndTag("response_label");
                        }
                        $a_xml_writer->xmlEndTag("render_choice");
                        $a_xml_writer->xmlEndTag("response_str");
                        break;
                    case assClozeGap::TYPE_TEXT:
                        // text fields
                        $attrs = [
                            "ident" => "gap_$i",
                            "rcardinality" => "Single"
                        ];
                        $a_xml_writer->xmlStartTag("response_str", $attrs);
                        $solution = $this->object->getSuggestedSolution($i);
                        if ($solution !== null && count($solution)) {
                            if (preg_match("/il_(\d*?)_(\w+)_(\d+)/", $solution["internal_link"], $matches)) {
                                $attrs = [
                                    "label" => "suggested_solution"
                                ];
                                $a_xml_writer->xmlStartTag("material", $attrs);
                                $intlink = "il_" . IL_INST_ID . "_" . $matches[2] . "_" . $matches[3];
                                if (strcmp($matches[1], "") != 0) {
                                    $intlink = $solution["internal_link"];
                                }
                                $a_xml_writer->xmlElement("mattext", null, $intlink);
                                $a_xml_writer->xmlEndTag("material");
                            }
                        }
                        $attrs = [
                            "fibtype" => "String",
                            "prompt" => "Box",
                            "columns" => $gap->getMaxWidth(),
                            "maxchars" => $gap->getGapSize()
                        ];
                        $a_xml_writer->xmlStartTag("render_fib", $attrs);
                        $a_xml_writer->xmlEndTag("render_fib");
                        $a_xml_writer->xmlEndTag("response_str");
                        break;
                    case assClozeGap::TYPE_NUMERIC:
                        // numeric fields
                        $attrs = [
                            "ident" => "gap_$i",
                            "numtype" => "Decimal",
                            "rcardinality" => "Single"
                        ];
                        $a_xml_writer->xmlStartTag("response_num", $attrs);
                        $solution = $this->object->getSuggestedSolution($i);
                        if ($solution !== null && count($solution)) {
                            if (preg_match("/il_(\d*?)_(\w+)_(\d+)/", $solution["internal_link"], $matches)) {
                                $attrs = [
                                    "label" => "suggested_solution"
                                ];
                                $a_xml_writer->xmlStartTag("material", $attrs);
                                $intlink = "il_" . IL_INST_ID . "_" . $matches[2] . "_" . $matches[3];
                                if (strcmp($matches[1], "") != 0) {
                                    $intlink = $solution["internal_link"];
                                }
                                $a_xml_writer->xmlElement("mattext", null, $intlink);
                                $a_xml_writer->xmlEndTag("material");
                            }
                        }
                        $answeritem = $gap->getItem(0);
                        $attrs = [
                            "fibtype" => "Decimal",
                            "prompt" => "Box",
                            "columns" => $gap->getMaxWidth(),
                            "maxchars" => $gap->getGapSize()
                        ];
                        if (is_object($answeritem)) {
                            if ($eval->e($answeritem->getLowerBound()) !== false) {
                                $attrs["minnumber"] = $answeritem->getLowerBound();
                            }
                            if ($eval->e($answeritem->getUpperBound()) !== false) {
                                $attrs["maxnumber"] = $answeritem->getUpperBound();
                            }
                        }
                        $a_xml_writer->xmlStartTag("render_fib", $attrs);
                        $a_xml_writer->xmlEndTag("render_fib");
                        $a_xml_writer->xmlEndTag("response_num");
                        break;
                }
            }
        }
        $a_xml_writer->xmlEndTag("flow");
        $a_xml_writer->xmlEndTag("presentation");

        // PART II: qti resprocessing
        $a_xml_writer->xmlStartTag("resprocessing");
        $a_xml_writer->xmlStartTag("outcomes");
        $a_xml_writer->xmlStartTag("decvar");
        $a_xml_writer->xmlEndTag("decvar");
        $a_xml_writer->xmlEndTag("outcomes");

        // add response conditions
        for ($i = 0; $i < $this->object->getGapCount(); $i++) {
            $gap = $this->object->getGap($i);
            switch ($gap->getType()) {
                case assClozeGap::TYPE_SELECT:
                    foreach ($gap->getItems($this->randomGroup->dontShuffle()) as $answer) {
                        $attrs = [
                            "continue" => "Yes"
                        ];
                        $a_xml_writer->xmlStartTag("respcondition", $attrs);
                        // qti conditionvar
                        $a_xml_writer->xmlStartTag("conditionvar");

                        $attrs = [
                            "respident" => "gap_$i"
                        ];
                        $a_xml_writer->xmlElement("varequal", $attrs, $answer->getAnswertext());
                        $a_xml_writer->xmlEndTag("conditionvar");
                        // qti setvar
                        $attrs = [
                            "action" => "Add"
                        ];
                        $a_xml_writer->xmlElement("setvar", $attrs, $answer->getPoints());
                        // qti displayfeedback
                        $linkrefid = "$i" . "_Response_" . $answer->getOrder();
                        $attrs = [
                            "feedbacktype" => "Response",
                            "linkrefid" => $linkrefid
                        ];
                        $a_xml_writer->xmlElement("displayfeedback", $attrs);
                        $a_xml_writer->xmlEndTag("respcondition");
                    }
                    break;
                case assClozeGap::TYPE_NUMERIC:
                case assClozeGap::TYPE_TEXT:
                    foreach ($gap->getItems($this->randomGroup->dontShuffle()) as $answer) {
                        $attrs = [
                            "continue" => "Yes"
                        ];
                        $a_xml_writer->xmlStartTag("respcondition", $attrs);
                        // qti conditionvar
                        $a_xml_writer->xmlStartTag("conditionvar");
                        $attrs = [
                            "respident" => "gap_$i"
                        ];
                        $a_xml_writer->xmlElement("varequal", $attrs, $answer->getAnswertext());
                        $a_xml_writer->xmlEndTag("conditionvar");
                        // qti setvar
                        $attrs = [
                            "action" => "Add"
                        ];
                        $a_xml_writer->xmlElement("setvar", $attrs, $answer->getPoints());
                        // qti displayfeedback
                        $linkrefid = "$i" . "_Response_" . $answer->getOrder();
                        $attrs = [
                            "feedbacktype" => "Response",
                            "linkrefid" => $linkrefid
                        ];
                        $a_xml_writer->xmlElement("displayfeedback", $attrs);
                        $a_xml_writer->xmlEndTag("respcondition");
                    }
                    break;
            }
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

            for ($i = 0; $i < $this->object->getGapCount(); $i++) {
                $gap = $this->object->getGap($i);
                $indexes = $gap->getBestSolutionIndexes();
                if ($i > 0) {
                    $a_xml_writer->xmlStartTag("and");
                }
                switch ($gap->getType()) {
                    case assClozeGap::TYPE_TEXT:
                    case assClozeGap::TYPE_NUMERIC:
                    case assClozeGap::TYPE_SELECT:
                        $k = 0;
                        foreach ($indexes as $key) {
                            if ($k > 0) {
                                $a_xml_writer->xmlStartTag("or");
                            }
                            $attrs = [
                                "respident" => "gap_$i"
                            ];
                            $answer = $gap->getItem($key);
                            $a_xml_writer->xmlElement("varequal", $attrs, $answer->getAnswertext());
                            if ($k > 0) {
                                $a_xml_writer->xmlEndTag("or");
                            }
                            $k++;
                        }
                        break;
                }
                if ($i > 0) {
                    $a_xml_writer->xmlEndTag("and");
                }
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
            for ($i = 0; $i < $this->object->getGapCount(); $i++) {
                $gap = $this->object->getGap($i);
                $indexes = $gap->getBestSolutionIndexes();
                if ($i > 0) {
                    $a_xml_writer->xmlStartTag("and");
                }
                switch ($gap->getType()) {
                    case assClozeGap::TYPE_TEXT:
                    case assClozeGap::TYPE_NUMERIC:
                    case assClozeGap::TYPE_SELECT:
                        $k = 0;
                        foreach ($indexes as $key) {
                            if ($k > 0) {
                                $a_xml_writer->xmlStartTag("or");
                            }
                            $attrs = [
                                "respident" => "gap_$i"
                            ];
                            $answer = $gap->getItem($key);
                            $a_xml_writer->xmlElement("varequal", $attrs, $answer->getAnswertext());
                            if ($k > 0) {
                                $a_xml_writer->xmlEndTag("or");
                            }
                            $k++;
                        }
                        break;
                }
                if ($i > 0) {
                    $a_xml_writer->xmlEndTag("and");
                }
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
        for ($i = 0; $i < $this->object->getGapCount(); $i++) {
            $gap = $this->object->getGap($i);
            switch ($gap->getType()) {
                case assClozeGap::TYPE_TEXT:
                case assClozeGap::TYPE_NUMERIC:
                case assClozeGap::TYPE_SELECT:
                    break;
            }
        }
        $this->exportAnswerSpecificFeedbacks($a_xml_writer);

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

    /**
     * @param ilXmlWriter $xmlWriter
     */
    protected function exportAnswerSpecificFeedbacks(ilXmlWriter $xmlWriter): void
    {
        $feedbackIdentifierList = new ilAssSpecificFeedbackIdentifierList();
        $feedbackIdentifierList->load($this->object->getId());

        foreach ($feedbackIdentifierList as $fbIdentifier) {
            $feedback = $this->object->feedbackOBJ->getSpecificAnswerFeedbackExportPresentation(
                $this->object->getId(),
                $fbIdentifier->getQuestionIndex(),
                $fbIdentifier->getAnswerIndex()
            );

            $xmlWriter->xmlStartTag("itemfeedback", [
                "ident" => $this->buildQtiExportIdent($fbIdentifier), "view" => "All"
            ]);

            $xmlWriter->xmlStartTag("flow_mat");
            $this->addQTIMaterial($xmlWriter, $feedback);
            $xmlWriter->xmlEndTag("flow_mat");

            $xmlWriter->xmlEndTag("itemfeedback");
        }
    }

    /**
     * @param ilAssSpecificFeedbackIdentifier $fbIdentifier
     * @return string
     */
    public function buildQtiExportIdent(ilAssSpecificFeedbackIdentifier $fbIdentifier): string
    {
        return "{$fbIdentifier->getQuestionIndex()}_{$fbIdentifier->getAnswerIndex()}";
    }
}
