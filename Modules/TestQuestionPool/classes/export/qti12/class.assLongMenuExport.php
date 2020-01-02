<?php
require_once 'Modules/TestQuestionPool/classes/export/qti12/class.assQuestionExport.php';
class assLongMenuExport extends assQuestionExport
{
    /**
     * @var assKprimChoice
     */
    public $object;

    public function toXML($a_include_header = true, $a_include_binary = true, $a_shuffle = false, $test_output = false, $force_image_references = false)
    {
        global $DIC;
        $ilias = $DIC['ilias'];

        $correct_answers 	= $this->object->getCorrectAnswers();
        $answers 			= $this->object->getAnswers();

        include_once("./Services/Xml/classes/class.ilXmlWriter.php");
        $xml = new ilXmlWriter;
        // set xml header
        $xml->xmlHeader();
        $xml->xmlStartTag("questestinterop");
        // add question description
        $attrs = array(
            "ident" => "il_" . IL_INST_ID . "_qst_" . $this->object->getId(),
            "title" => $this->object->getTitle(),
            "maxattempts" => $this->object->getNrOfTries()
        );
        $xml->xmlStartTag("item", $attrs);
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
        $xml->xmlElement("fieldentry", null, LONG_MENU_QUESTION_IDENTIFIER);
        $xml->xmlEndTag("qtimetadatafield");
        $xml->xmlStartTag("qtimetadatafield");
        $xml->xmlElement("fieldlabel", null, "AUTHOR");
        $xml->xmlElement("fieldentry", null, $this->object->getAuthor());
        $xml->xmlEndTag("qtimetadatafield");

        $xml->xmlStartTag("qtimetadatafield");
        $xml->xmlElement("fieldlabel", null, "minAutoCompleteLength");
        $xml->xmlElement("fieldentry", null, $this->object->getMinAutoComplete());
        $xml->xmlEndTag("qtimetadatafield");
        $xml->xmlStartTag("qtimetadatafield");
        $xml->xmlElement("fieldlabel", null, "identical_scoring");
        $xml->xmlElement("fieldentry", null, $this->object->getIdenticalScoring());
        $xml->xmlEndTag("qtimetadatafield");

        $xml->xmlStartTag("qtimetadatafield");
        $xml->xmlElement("fieldlabel", null, "gapTypes");
        $gap_types = array();
        if (is_array($correct_answers)) {
            foreach ($correct_answers as $key => $value) {
                $gap_types[] = $value[2];
            }
        }
        $xml->xmlElement("fieldentry", null, json_encode($gap_types));
        $xml->xmlEndTag("qtimetadatafield");

        // additional content editing information
        $this->addAdditionalContentEditingModeInformation($xml);
        $this->addGeneralMetadata($xml);

        $xml->xmlStartTag("qtimetadatafield");
        $xml->xmlElement("fieldlabel", null, "feedback_setting");
        $xml->xmlElement("fieldentry", null, $this->object->getSpecificFeedbackSetting());
        $xml->xmlEndTag("qtimetadatafield");

        $xml->xmlEndTag("qtimetadata");
        $xml->xmlEndTag("itemmetadata");
        $xml->xmlStartTag("presentation");
        // add flow to presentation
        $xml->xmlStartTag("flow");

            
        $this->object->addQTIMaterial($xml, $this->object->getQuestion());
        $this->object->addQTIMaterial($xml, $this->object->getLongMenuTextValue());
    
        foreach ($answers as $key => $values) {
            $real_id = $key +1;
            $attrs = array(
                    "ident" => "LongMenu_" . $real_id,
                    "rcardinality" => "Single"
                );
            $xml->xmlStartTag("response_str", $attrs);
            foreach ($values as $index => $value) {
                $xml->xmlStartTag("response_label", array('ident' => $index));
                $xml->xmlStartTag("material");
                $xml->xmlElement("fieldentry", null, $value);
                $xml->xmlEndTag("material");
                $xml->xmlEndTag("response_label");
            }
            $xml->xmlEndTag("response_str");
        }
        $xml->xmlEndTag("flow");
        $xml->xmlEndTag("presentation");
        
        $xml->xmlStartTag("resprocessing");
        $xml->xmlStartTag("outcomes");
        $xml->xmlElement("decvar");
        $xml->xmlEndTag("outcomes");
        foreach ($answers as $key => $values) {
            $real_id = $key + 1;
            foreach ($values as $index => $value) {
                $xml->xmlStartTag("respcondition", array('continue' => 'Yes'));
                $xml->xmlStartTag("conditionvar");
                $xml->xmlElement("varequal", array('respident' => "LongMenu_" . $real_id), $value);
                $xml->xmlEndTag("conditionvar");
    
                if (in_array($value, $correct_answers[$key][0])) {
                    $xml->xmlElement("setvar", array('action' => "Add"), $correct_answers[$key][1]);
                } else {
                    $xml->xmlElement("setvar", array('action' => "Add"), 0);
                }
                $xml->xmlElement("displayfeedback", array('feedbacktype' => "Response", 'linkrefid' => $key . '_Response_' . $index));
                $xml->xmlEndTag("respcondition");
            }
        }
        $feedback_allcorrect = $this->object->feedbackOBJ->getGenericFeedbackExportPresentation(
            $this->object->getId(),
            true
        );
        if (strlen($feedback_allcorrect) > 0) {
            $xml->xmlStartTag("respcondition", array('continue' => 'Yes'));
            $xml->xmlStartTag("conditionvar");
            foreach ($correct_answers as $key => $values) {
                $real_id = $key + 1;
                if ($key > 0) {
                    $xml->xmlStartTag("and");
                }

                foreach ($values[0] as $index => $value) {
                    if ($index > 0) {
                        $xml->xmlStartTag("or");
                    }
                    $xml->xmlElement("varequal", array('respident' => "LongMenu_" . $real_id), $value);
                    if ($index > 0) {
                        $xml->xmlEndTag("or");
                    }
                }
                if ($key > 0) {
                    $xml->xmlEndTag("and");
                }
            }
            $xml->xmlEndTag("conditionvar");
            $xml->xmlElement("displayfeedback", array('feedbacktype' => "Response", 'linkrefid' => 'response_allcorrect'));
            $xml->xmlEndTag("respcondition");
        }

        $feedback_onenotcorrect = $this->object->feedbackOBJ->getGenericFeedbackExportPresentation(
            $this->object->getId(),
            false
        );
        if (strlen($feedback_onenotcorrect)) {
            $xml->xmlStartTag("respcondition", array('continue' => 'Yes'));
            $xml->xmlStartTag("conditionvar");
            $xml->xmlStartTag("not");
            foreach ($correct_answers as $key => $values) {
                $real_id = $key + 1;
                if ($key > 0) {
                    $xml->xmlStartTag("and");
                }

                foreach ($values[0] as $index => $value) {
                    if ($index > 0) {
                        $xml->xmlStartTag("or");
                    }
                    $xml->xmlElement("varequal", array('respident' => "LongMenu_" . $real_id), $value);
                    if ($index > 0) {
                        $xml->xmlEndTag("or");
                    }
                }
                if ($key > 0) {
                    $xml->xmlEndTag("and");
                }
            }
            $xml->xmlEndTag("not");
            $xml->xmlEndTag("conditionvar");
            $xml->xmlElement("displayfeedback", array('feedbacktype' => "Response", 'linkrefid' => 'response_onenotcorrect'));
            $xml->xmlEndTag("respcondition");
        }
        
        $xml->xmlEndTag("resprocessing");

        
        
        
        for ($i = 0; $i < sizeof($correct_answers); $i++) {
            $attrs = array(
                "ident" => $i,
                "view" => "All"
            );
            $xml->xmlStartTag("itemfeedback", $attrs);
            // qti flow_mat
            $xml->xmlStartTag("flow_mat");
            $fb = $this->object->feedbackOBJ->getSpecificAnswerFeedbackExportPresentation(
                $this->object->getId(),
                0,
                $i
            );
            $this->object->addQTIMaterial($xml, $fb);
            $xml->xmlEndTag("flow_mat");
            $xml->xmlEndTag("itemfeedback");
        }
        
        if (strlen($feedback_allcorrect) > 0) {
            $xml->xmlStartTag("itemfeedback", array('ident' => 'response_allcorrect','view' => 'All'));
            $xml->xmlStartTag("flow_mat");
            $xml->xmlStartTag("material");
            $xml->xmlElement("mattext", array('texttype' => 'text/xhtml'), $feedback_allcorrect);
            $xml->xmlEndTag("material");
            $xml->xmlEndTag("flow_mat");
            $xml->xmlEndTag("itemfeedback");
        }
        if (strlen($feedback_onenotcorrect) > 0) {
            $xml->xmlStartTag("itemfeedback", array('ident' => 'response_onenotcorrect', 'view' => 'All'));
            $xml->xmlStartTag("flow_mat");
            $xml->xmlStartTag("material");
            $xml->xmlElement("mattext", array('texttype' => 'text/xhtml'), $feedback_onenotcorrect);
            $xml->xmlEndTag("material");
            $xml->xmlEndTag("flow_mat");
            $xml->xmlEndTag("itemfeedback");
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
}
