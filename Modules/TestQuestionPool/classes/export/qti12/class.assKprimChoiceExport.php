<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/TestQuestionPool/classes/export/qti12/class.assQuestionExport.php';

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class assKprimChoiceExport extends assQuestionExport
{
    /**
     * @var assKprimChoice
     */
    public $object;
    
    public function toXML($a_include_header = true, $a_include_binary = true, $a_shuffle = false, $test_output = false, $force_image_references = false)
    {
        global $DIC;
        $ilias = $DIC['ilias'];

        include_once("./Services/Xml/classes/class.ilXmlWriter.php");
        $xml = new ilXmlWriter;
        // set xml header
        $xml->xmlHeader();
        $xml->xmlStartTag("questestinterop");
        $attrs = array(
            "ident" => "il_" . IL_INST_ID . "_qst_" . $this->object->getId(),
            "title" => $this->object->getTitle(),
            "maxattempts" => $this->object->getNrOfTries()
        );
        $xml->xmlStartTag("item", $attrs);
        // add question description
        $xml->xmlElement("qticomment", null, $this->object->getComment());
        // add estimated working time
        $workingtime = $this->object->getEstimatedWorkingTime();
        $duration = sprintf("P0Y0M0DT%dH%dM%dS", $workingtime["h"], $workingtime["m"], $workingtime["s"]);
        $xml->xmlElement("duration", null, $duration);
        // add ILIAS specific metadata
        $xml->xmlStartTag("itemmetadata");
        $xml->xmlStartTag("qtimetadata");
        $xml->xmlStartTag("qtimetadatafield");
        $xml->xmlElement("fieldlabel", null, "ILIAS_VERSION");
        $xml->xmlElement("fieldentry", null, $ilias->getSetting("ilias_version"));
        $xml->xmlEndTag("qtimetadatafield");
        $xml->xmlStartTag("qtimetadatafield");
        $xml->xmlElement("fieldlabel", null, "QUESTIONTYPE");
        $xml->xmlElement("fieldentry", null, KPRIM_CHOICE_QUESTION_IDENTIFIER);
        $xml->xmlEndTag("qtimetadatafield");
        $xml->xmlStartTag("qtimetadatafield");
        $xml->xmlElement("fieldlabel", null, "AUTHOR");
        $xml->xmlElement("fieldentry", null, $this->object->getAuthor());
        $xml->xmlEndTag("qtimetadatafield");

        // additional content editing information
        $this->addAdditionalContentEditingModeInformation($xml);
        $this->addGeneralMetadata($xml);

        $xml->xmlStartTag("qtimetadatafield");
        $xml->xmlElement("fieldlabel", null, "answer_type");
        $xml->xmlElement("fieldentry", null, $this->object->getAnswerType());
        $xml->xmlEndTag("qtimetadatafield");

        $xml->xmlStartTag("qtimetadatafield");
        $xml->xmlElement("fieldlabel", null, "thumb_size");
        $xml->xmlElement("fieldentry", null, $this->object->getThumbSize());
        $xml->xmlEndTag("qtimetadatafield");

        $xml->xmlStartTag("qtimetadatafield");
        $xml->xmlElement("fieldlabel", null, "option_label_setting");
        $xml->xmlElement("fieldentry", null, $this->object->getOptionLabel());
        $xml->xmlEndTag("qtimetadatafield");
        $xml->xmlStartTag("qtimetadatafield");
        $xml->xmlElement("fieldlabel", null, "custom_true_option_label");
        $xml->xmlElement("fieldentry", null, $this->object->getCustomTrueOptionLabel());
        $xml->xmlEndTag("qtimetadatafield");
        $xml->xmlStartTag("qtimetadatafield");
        $xml->xmlElement("fieldlabel", null, "custom_false_option_label");
        $xml->xmlElement("fieldentry", null, $this->object->getCustomFalseOptionLabel());
        $xml->xmlEndTag("qtimetadatafield");

        $xml->xmlStartTag("qtimetadatafield");
        $xml->xmlElement("fieldlabel", null, "feedback_setting");
        $xml->xmlElement("fieldentry", null, $this->object->getSpecificFeedbackSetting());
        $xml->xmlEndTag("qtimetadatafield");

        $xml->xmlEndTag("qtimetadata");
        $xml->xmlEndTag("itemmetadata");

        // PART I: qti presentation
        $attrs = array(
            "label" => $this->object->getTitle()
        );
        $xml->xmlStartTag("presentation", $attrs);
        // add flow to presentation
        $xml->xmlStartTag("flow");
        // add material with question text to presentation
        $this->object->addQTIMaterial($xml, $this->object->getQuestion());
        // add answers to presentation
        $attrs = array(
            "ident" => "MCMR",
            "rcardinality" => "Multiple"
        );
        $xml->xmlStartTag("response_lid", $attrs);
        $solution = $this->object->getSuggestedSolution(0);
        if (count($solution)) {
            if (preg_match("/il_(\d*?)_(\w+)_(\d+)/", $solution["internal_link"], $matches)) {
                $xml->xmlStartTag("material");
                $intlink = "il_" . IL_INST_ID . "_" . $matches[2] . "_" . $matches[3];
                if (strcmp($matches[1], "") != 0) {
                    $intlink = $solution["internal_link"];
                }
                $attrs = array(
                    "label" => "suggested_solution"
                );
                $xml->xmlElement("mattext", $attrs, $intlink);
                $xml->xmlEndTag("material");
            }
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
        $xml->xmlStartTag("render_choice", $attrs);

        // add answers
        $answers =&$this->object->getAnswers();
        $akeys = array_keys($answers);
        foreach ($akeys as $index) {
            $answer = $this->object->getAnswer($index);
            
            $xml->xmlStartTag('response_label', array('ident' => $answer->getPosition()));

            if (strlen($answer->getImageFile())) {
                $this->object->addQTIMaterial($xml, $answer->getAnswertext(), false, false);
                $imagetype = "image/jpeg";
                if (preg_match("/.*\.(png|gif)$/", $answer->getImageFile(), $matches)) {
                    $imagetype = "image/" . $matches[1];
                }
                if ($force_image_references) {
                    $attrs = array(
                        "imagtype" => $imagetype,
                        "label" => $answer->getImageFile(),
                        "uri" => $answer->getImageWebPath()
                    );
                    $xml->xmlElement("matimage", $attrs);
                } else {
                    $imagepath = $answer->getImageFsPath();
                    $fh = @fopen($imagepath, "rb");
                    if ($fh != false) {
                        $imagefile = fread($fh, filesize($imagepath));
                        fclose($fh);
                        $base64 = base64_encode($imagefile);
                        $attrs = array(
                            "imagtype" => $imagetype,
                            "label" => $answer->getImageFile(),
                            "embedded" => "base64"
                        );
                        $xml->xmlElement("matimage", $attrs, $base64, false, false);
                    }
                }
                $xml->xmlEndTag("material");
            } else {
                $this->object->addQTIMaterial($xml, $answer->getAnswertext());
            }
            $xml->xmlEndTag("response_label");
        }
        $xml->xmlEndTag("render_choice");
        $xml->xmlEndTag("response_lid");
        $xml->xmlEndTag("flow");
        $xml->xmlEndTag("presentation");

        // PART II: qti resprocessing
        
        $xml->xmlStartTag('resprocessing');

        $xml->xmlStartTag('outcomes');
        $xml->xmlElement('decvar', array(
            'varname' => 'SCORE', 'vartype' => 'Decimal', 'defaultval' => '0',
            'minvalue' => $this->getMinPoints(), 'maxvalue' => $this->getMaxPoints()
        ));
        $xml->xmlEndTag('outcomes');


        foreach ($answers as $answer) {
            $xml->xmlStartTag('respcondition', array('continue' => 'Yes'));
            
            $xml->xmlStartTag('conditionvar');
            $xml->xmlElement('varequal', array('respident' => $answer->getPosition()), $answer->getCorrectness());
            $xml->xmlEndTag('conditionvar');

            $xml->xmlElement('displayfeedback', array(
                'feedbacktype' => 'Response', 'linkrefid' => "response_{$answer->getPosition()}"
            ));

            $xml->xmlEndTag('respcondition');
        }

        $feedback_allcorrect = $this->object->feedbackOBJ->getGenericFeedbackExportPresentation(
            $this->object->getId(),
            true
        );

        $xml->xmlStartTag('respcondition', array('continue' => 'Yes'));

        $xml->xmlStartTag('conditionvar');
        $xml->xmlStartTag('and');
        foreach ($answers as $answer) {
            $xml->xmlElement('varequal', array('respident' => $answer->getPosition()), $answer->getCorrectness());
        }
        $xml->xmlEndTag('and');
        $xml->xmlEndTag('conditionvar');

        $xml->xmlElement('setvar', array('action' => 'Add'), $this->object->getPoints());
        
        if (strlen($feedback_allcorrect)) {
            $xml->xmlElement('displayfeedback', array('feedbacktype' => 'Response', 'linkrefid' => 'response_allcorrect'));
        }

        $xml->xmlEndTag('respcondition');
        
        $feedback_onenotcorrect = $this->object->feedbackOBJ->getGenericFeedbackExportPresentation(
            $this->object->getId(),
            false
        );
        
        $xml->xmlStartTag('respcondition', array('continue' => 'Yes'));

        $xml->xmlStartTag('conditionvar');
        $xml->xmlStartTag('or');
        foreach ($answers as $answer) {
            $xml->xmlStartTag('not');
            $xml->xmlElement('varequal', array('respident' => $answer->getPosition()), $answer->getCorrectness());
            $xml->xmlEndTag('not');
        }
        $xml->xmlEndTag('or');
        $xml->xmlEndTag('conditionvar');

        $xml->xmlElement('setvar', array('action' => 'Add'), 0);

        if (strlen($feedback_onenotcorrect)) {
            $xml->xmlElement('displayfeedback', array('feedbacktype' => 'Response', 'linkrefid' => 'response_onenotcorrect'));
        }

        $xml->xmlEndTag('respcondition');
        
        $xml->xmlEndTag('resprocessing');

        foreach ($answers as $answer) {
            $xml->xmlStartTag('itemfeedback', array('ident' => "response_{$answer->getPosition()}", 'view' => 'All'));
            $xml->xmlStartTag('flow_mat');
            
            $this->object->addQTIMaterial($xml, $this->object->feedbackOBJ->getSpecificAnswerFeedbackExportPresentation(
                $this->object->getId(),
                0,
                $answer->getPosition()
            ));

            $xml->xmlEndTag('flow_mat');
            $xml->xmlEndTag('itemfeedback');
        }
        if (strlen($feedback_allcorrect)) {
            $xml->xmlStartTag('itemfeedback', array('ident' => 'response_allcorrect', 'view' => 'All'));
            $xml->xmlStartTag('flow_mat');
            
            $this->object->addQTIMaterial($xml, $feedback_allcorrect);
            
            $xml->xmlEndTag('flow_mat');
            $xml->xmlEndTag('itemfeedback');
        }
        if (strlen($feedback_onenotcorrect)) {
            $xml->xmlStartTag('itemfeedback', array('ident' => 'response_onenotcorrect', 'view' => 'All'));
            $xml->xmlStartTag('flow_mat');

            $this->object->addQTIMaterial($xml, $feedback_onenotcorrect);

            $xml->xmlEndTag('flow_mat');
            $xml->xmlEndTag('itemfeedback');
        }

        $xml->xmlEndTag("item");
        $xml->xmlEndTag("questestinterop");

        $xml = $xml->xmlDumpMem(false);
        if (!$a_include_header) {
            $pos = strpos($xml, "?>");
            $xml = substr($xml, $pos + 2);
        }
        return $xml;
    }
    
    private function getMinPoints()
    {
        if ($this->object->isScorePartialSolutionEnabled()) {
            return ($this->object->getPoints() / 2);
        }
        
        return 0;
    }
    
    private function getMaxPoints()
    {
        return $this->object->getPoints();
    }
}
