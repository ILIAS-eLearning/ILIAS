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

use ILIAS\Test\QuestionIdentifiers;

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package components\ILIAS/Test
 */
class assKprimChoiceExport extends assQuestionExport
{
    /**
     * @var assKprimChoice
     */
    public $object;

    public function toXML($a_include_header = true, $a_include_binary = true, $a_shuffle = false, $test_output = false, $force_image_references = false): string
    {
        global $DIC;
        $ilias = $DIC['ilias'];

        $a_xml_writer = new ilXmlWriter();
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
        $a_xml_writer->xmlStartTag("itemmetadata");
        $a_xml_writer->xmlStartTag("qtimetadata");
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "ILIAS_VERSION");
        $a_xml_writer->xmlElement("fieldentry", null, $ilias->getSetting("ilias_version"));
        $a_xml_writer->xmlEndTag("qtimetadatafield");
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "QUESTIONTYPE");
        $a_xml_writer->xmlElement("fieldentry", null, QuestionIdentifiers::KPRIM_CHOICE_QUESTION_IDENTIFIER);
        $a_xml_writer->xmlEndTag("qtimetadatafield");
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "AUTHOR");
        $a_xml_writer->xmlElement("fieldentry", null, $this->object->getAuthor());
        $a_xml_writer->xmlEndTag("qtimetadatafield");

        // additional content editing information
        $this->addAdditionalContentEditingModeInformation($a_xml_writer);
        $this->addGeneralMetadata($a_xml_writer);

        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "answer_type");
        $a_xml_writer->xmlElement("fieldentry", null, $this->object->getAnswerType());
        $a_xml_writer->xmlEndTag("qtimetadatafield");

        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "thumb_size");
        $a_xml_writer->xmlElement("fieldentry", null, $this->object->getThumbSize());
        $a_xml_writer->xmlEndTag("qtimetadatafield");

        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "option_label_setting");
        $a_xml_writer->xmlElement("fieldentry", null, $this->object->getOptionLabel());
        $a_xml_writer->xmlEndTag("qtimetadatafield");
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "custom_true_option_label");
        $a_xml_writer->xmlElement("fieldentry", null, $this->object->getCustomTrueOptionLabel());
        $a_xml_writer->xmlEndTag("qtimetadatafield");
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "custom_false_option_label");
        $a_xml_writer->xmlElement("fieldentry", null, $this->object->getCustomFalseOptionLabel());
        $a_xml_writer->xmlEndTag("qtimetadatafield");

        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "feedback_setting");
        $a_xml_writer->xmlElement("fieldentry", null, $this->object->getSpecificFeedbackSetting());
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
        $this->addQTIMaterial($a_xml_writer, $this->object->getQuestion());
        // add answers to presentation
        $attrs = array(
            "ident" => "MCMR",
            "rcardinality" => "Multiple"
        );
        $a_xml_writer->xmlStartTag("response_lid", $attrs);
        $solution = $this->object->getSuggestedSolution(0);

        if ($solution !== null) {
            $a_xml_writer = $this->addSuggestedSolutionLink($a_xml_writer, $solution);
        }
        // shuffle output
        $attrs = array();
        if ($this->object->isShuffleAnswersEnabled()) {
            $attrs = array(
                "shuffle" => "Yes"
            );
        } else {
            $attrs = array(
                "shuffle" => "No"
            );
        }
        $a_xml_writer->xmlStartTag("render_choice", $attrs);

        // add answers
        $answers = $this->object->getAnswers();
        $akeys = array_keys($answers);
        foreach ($akeys as $index) {
            $answer = $this->object->getAnswer($index);

            $a_xml_writer->xmlStartTag('response_label', array('ident' => $answer->getPosition()));

            $image_file = $answer->getImageFile() ?? '';
            if ($image_file !== '') {
                $this->addQTIMaterial($a_xml_writer, $answer->getAnswertext(), false, false);
                $imagetype = "image/jpeg";
                if (preg_match("/.*\.(png|gif)$/", $image_file, $matches)) {
                    $imagetype = "image/" . $matches[1];
                }
                if ($force_image_references) {
                    $attrs = array(
                        "imagtype" => $imagetype,
                        "label" => $image_file,
                        "uri" => $answer->getImageWebPath()
                    );
                    $a_xml_writer->xmlElement("matimage", $attrs);
                } else {
                    $imagepath = $answer->getImageFsPath();
                    $fh = @fopen($imagepath, "rb");
                    if ($fh != false) {
                        $imagefile = fread($fh, filesize($imagepath));
                        fclose($fh);
                        $base64 = base64_encode($imagefile);
                        $attrs = array(
                            "imagtype" => $imagetype,
                            "label" => $image_file,
                            "embedded" => "base64"
                        );
                        $a_xml_writer->xmlElement("matimage", $attrs, $base64, false, false);
                    }
                }
                $a_xml_writer->xmlEndTag("material");
            } else {
                $this->addQTIMaterial($a_xml_writer, $answer->getAnswertext());
            }
            $a_xml_writer->xmlEndTag("response_label");
        }
        $a_xml_writer->xmlEndTag("render_choice");
        $a_xml_writer->xmlEndTag("response_lid");
        $a_xml_writer->xmlEndTag("flow");
        $a_xml_writer->xmlEndTag("presentation");

        // PART II: qti resprocessing

        $a_xml_writer->xmlStartTag('resprocessing');

        $a_xml_writer->xmlStartTag('outcomes');
        $a_xml_writer->xmlElement('decvar', array(
            'varname' => 'SCORE', 'vartype' => 'Decimal', 'defaultval' => '0',
            'minvalue' => $this->getMinPoints(), 'maxvalue' => $this->getMaxPoints()
        ));
        $a_xml_writer->xmlEndTag('outcomes');


        foreach ($answers as $answer) {
            $a_xml_writer->xmlStartTag('respcondition', array('continue' => 'Yes'));

            $a_xml_writer->xmlStartTag('conditionvar');
            $a_xml_writer->xmlElement('varequal', array('respident' => $answer->getPosition()), $answer->getCorrectness());
            $a_xml_writer->xmlEndTag('conditionvar');

            $a_xml_writer->xmlElement('displayfeedback', array(
                'feedbacktype' => 'Response', 'linkrefid' => "response_{$answer->getPosition()}"
            ));

            $a_xml_writer->xmlEndTag('respcondition');
        }

        $feedback_allcorrect = $this->object->feedbackOBJ->getGenericFeedbackExportPresentation(
            $this->object->getId(),
            true
        );

        $a_xml_writer->xmlStartTag('respcondition', array('continue' => 'Yes'));

        $a_xml_writer->xmlStartTag('conditionvar');
        $a_xml_writer->xmlStartTag('and');
        foreach ($answers as $answer) {
            $a_xml_writer->xmlElement('varequal', array('respident' => $answer->getPosition()), $answer->getCorrectness());
        }
        $a_xml_writer->xmlEndTag('and');
        $a_xml_writer->xmlEndTag('conditionvar');

        $a_xml_writer->xmlElement('setvar', array('action' => 'Add'), $this->object->getPoints());

        if (strlen($feedback_allcorrect)) {
            $a_xml_writer->xmlElement('displayfeedback', array('feedbacktype' => 'Response', 'linkrefid' => 'response_allcorrect'));
        }

        $a_xml_writer->xmlEndTag('respcondition');

        $feedback_onenotcorrect = $this->object->feedbackOBJ->getGenericFeedbackExportPresentation(
            $this->object->getId(),
            false
        );

        $a_xml_writer->xmlStartTag('respcondition', array('continue' => 'Yes'));

        $a_xml_writer->xmlStartTag('conditionvar');
        $a_xml_writer->xmlStartTag('or');
        foreach ($answers as $answer) {
            $a_xml_writer->xmlStartTag('not');
            $a_xml_writer->xmlElement('varequal', array('respident' => $answer->getPosition()), $answer->getCorrectness());
            $a_xml_writer->xmlEndTag('not');
        }
        $a_xml_writer->xmlEndTag('or');
        $a_xml_writer->xmlEndTag('conditionvar');

        $a_xml_writer->xmlElement('setvar', array('action' => 'Add'), 0);

        if (strlen($feedback_onenotcorrect)) {
            $a_xml_writer->xmlElement('displayfeedback', array('feedbacktype' => 'Response', 'linkrefid' => 'response_onenotcorrect'));
        }

        $a_xml_writer->xmlEndTag('respcondition');

        $a_xml_writer->xmlEndTag('resprocessing');

        foreach ($answers as $answer) {
            $a_xml_writer->xmlStartTag('itemfeedback', array('ident' => "response_{$answer->getPosition()}", 'view' => 'All'));
            $a_xml_writer->xmlStartTag('flow_mat');

            $this->addQTIMaterial($a_xml_writer, $this->object->feedbackOBJ->getSpecificAnswerFeedbackExportPresentation(
                $this->object->getId(),
                0,
                $answer->getPosition()
            ));

            $a_xml_writer->xmlEndTag('flow_mat');
            $a_xml_writer->xmlEndTag('itemfeedback');
        }
        if (strlen($feedback_allcorrect)) {
            $a_xml_writer->xmlStartTag('itemfeedback', array('ident' => 'response_allcorrect', 'view' => 'All'));
            $a_xml_writer->xmlStartTag('flow_mat');

            $this->addQTIMaterial($a_xml_writer, $feedback_allcorrect);

            $a_xml_writer->xmlEndTag('flow_mat');
            $a_xml_writer->xmlEndTag('itemfeedback');
        }
        if (strlen($feedback_onenotcorrect)) {
            $a_xml_writer->xmlStartTag('itemfeedback', array('ident' => 'response_onenotcorrect', 'view' => 'All'));
            $a_xml_writer->xmlStartTag('flow_mat');

            $this->addQTIMaterial($a_xml_writer, $feedback_onenotcorrect);

            $a_xml_writer->xmlEndTag('flow_mat');
            $a_xml_writer->xmlEndTag('itemfeedback');
        }

        $a_xml_writer = $this->addSolutionHints($a_xml_writer);

        $a_xml_writer->xmlEndTag("item");
        $a_xml_writer->xmlEndTag("questestinterop");

        $a_xml_writer = $a_xml_writer->xmlDumpMem(false);
        if (!$a_include_header) {
            $pos = strpos($a_xml_writer, "?>");
            $a_xml_writer = substr($a_xml_writer, $pos + 2);
        }
        return $a_xml_writer;
    }

    private function getMinPoints()
    {
        if ($this->object->isScorePartialSolutionEnabled()) {
            return ($this->object->getPoints() / 2);
        }

        return 0;
    }

    private function getMaxPoints(): float
    {
        return $this->object->getPoints();
    }
}
