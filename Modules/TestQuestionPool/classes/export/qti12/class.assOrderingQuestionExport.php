<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Modules/TestQuestionPool/classes/export/qti12/class.assQuestionExport.php";

/**
* Class for ordering question exports
*
* assOrderingQuestionExport is a class for ordering question exports
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesTestQuestionPool
*/
class assOrderingQuestionExport extends assQuestionExport
{
    /**
     * @var assOrderingQuestion
     */
    public $object;
    
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
        $ilDB = $DIC['ilDB'];
        $ilUser = $DIC['ilUser'];
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
        $a_xml_writer->xmlElement("fieldentry", null, ORDERING_QUESTION_IDENTIFIER);
        $a_xml_writer->xmlEndTag("qtimetadatafield");
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "AUTHOR");
        $a_xml_writer->xmlElement("fieldentry", null, $this->object->getAuthor());
        $a_xml_writer->xmlEndTag("qtimetadatafield");
        
        // additional content editing information
        $this->addAdditionalContentEditingModeInformation($a_xml_writer);
        $this->addGeneralMetadata($a_xml_writer);
        
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "thumb_geometry");
        $a_xml_writer->xmlElement("fieldentry", null, $this->object->getThumbGeometry());
        $a_xml_writer->xmlEndTag("qtimetadatafield");
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "element_height");
        $a_xml_writer->xmlElement("fieldentry", null, $this->object->getElementHeight());
        $a_xml_writer->xmlEndTag("qtimetadatafield");
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "points");
        $a_xml_writer->xmlElement("fieldentry", null, $this->object->getPoints());
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

        if ($this->object->getOrderingType() == OQ_PICTURES) {
            $ordering_type ='OQP';
        } elseif ($this->object->getOrderingType() == OQ_NESTED_PICTURES) {
            $ordering_type ='OQNP';
        } elseif ($this->object->getOrderingType() == OQ_NESTED_TERMS) {
            $ordering_type ='OQNT';
        } elseif ($this->object->getOrderingType() == OQ_TERMS) {
            $ordering_type ='OQT';
        }

        $attrs = array(
            "ident"        => $ordering_type,
            "rcardinality" => "Ordered"
        );

        if ($this->object->getOutputType() == OUTPUT_JAVASCRIPT) {
            $attrs["output"] = "javascript";
        }
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

        // add answers
        foreach ($this->object->getOrderingElementList() as $element) {
            $attrs = array(
                'ident' => $element->getExportIdent()
            );
            $a_xml_writer->xmlStartTag("response_label", $attrs);
            if ($this->object->getOrderingType() == OQ_PICTURES
            || $this->object->getOrderingType() == OQ_NESTED_PICTURES) {
                $imagetype = "image/jpeg";
                
                $a_xml_writer->xmlStartTag("material");
                if ($force_image_references) {
                    $attrs = array(
                        "imagtype" => $imagetype,
                        "label" => $element->getContent(),
                        "uri" => $this->object->getImagePathWeb() . $element->getContent()
                    );
                    $a_xml_writer->xmlElement("matimage", $attrs);
                } else {
                    $imagepath = $this->object->getImagePath() . $element->getContent();
                    $fh = @fopen($imagepath, "rb");
                    if ($fh != false) {
                        $imagefile = fread($fh, filesize($imagepath));
                        fclose($fh);
                        $base64 = base64_encode($imagefile);
                        
                        if (preg_match("/.*\.(png|gif)$/", $element->getContent(), $matches)) {
                            $imagetype = "image/" . $matches[1];
                        }
                        $attrs = array(
                            "imagtype" => $imagetype,
                            "label" => $element->getContent(),
                            "embedded" => "base64"
                        );
                        $a_xml_writer->xmlElement("matimage", $attrs, $base64, false, false);
                    }
                }
                $a_xml_writer->xmlEndTag("material");
            } elseif ($this->object->getOrderingType() == OQ_TERMS
            || $this->object->getOrderingType() == OQ_NESTED_TERMS) {
                $a_xml_writer->xmlStartTag("material");
                $this->object->addQTIMaterial($a_xml_writer, $element->getContent(), true, false);
                $a_xml_writer->xmlEndTag("material");
                $a_xml_writer->xmlStartTag("material");
                $attrs = array("label" => "answerdepth");
                $a_xml_writer->xmlElement("mattext", $attrs, $element->getIndentation());
                $a_xml_writer->xmlEndTag("material");
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
        foreach ($this->object->getOrderingElementList() as $element) {
            $attrs = array(
                "continue" => "Yes"
            );
            $a_xml_writer->xmlStartTag("respcondition", $attrs);
            // qti conditionvar
            $a_xml_writer->xmlStartTag("conditionvar");
            $attrs = array();
            
            if ($this->object->getOrderingType() == OQ_PICTURES) {
                $ordering_type ='OQP';
            } elseif ($this->object->getOrderingType() == OQ_NESTED_PICTURES) {
                $ordering_type ='OQNP';
            } elseif ($this->object->getOrderingType() == OQ_NESTED_TERMS) {
                $ordering_type ='OQNT';
            } elseif ($this->object->getOrderingType() == OQ_TERMS) {
                $ordering_type ='OQT';
            }
            
            $attrs = array("respident" => $ordering_type);
            
            $attrs["index"] = $element->getPosition();
            $a_xml_writer->xmlElement("varequal", $attrs, $element->getPosition());
            $a_xml_writer->xmlEndTag("conditionvar");
            // qti setvar
            $attrs = array(
                "action" => "Add"
            );
            $points = $this->object->getPoints() / $this->object->getOrderingElementList()->countElements();
            $a_xml_writer->xmlElement("setvar", $attrs, $points);
            // qti displayfeedback
            $attrs = array(
                "feedbacktype" => "Response",
                "linkrefid" => "link_" . $element->getPosition()
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

            foreach ($this->object->getOrderingElementList() as $element) {
                $attrs = array();
                
                if ($this->object->getOrderingType() == OQ_PICTURES) {
                    $ordering_type ='OQP';
                } elseif ($this->object->getOrderingType() == OQ_NESTED_PICTURES) {
                    $ordering_type ='OQNP';
                } elseif ($this->object->getOrderingType() == OQ_NESTED_TERMS) {
                    $ordering_type ='OQNT';
                } elseif ($this->object->getOrderingType() == OQ_TERMS) {
                    $ordering_type ='OQT';
                }

                $attrs = array("respident" => $ordering_type);

                $attrs["index"] = $element->getPosition();
                $a_xml_writer->xmlElement("varequal", $attrs, $element->getPosition());
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
            $a_xml_writer->xmlStartTag("not");

            foreach ($this->object->getOrderingElementList() as $element) {
                $attrs = array();
                if ($this->object->getOrderingType() == OQ_PICTURES) {
                    $ordering_type ='OQP';
                } elseif ($this->object->getOrderingType() == OQ_NESTED_PICTURES) {
                    $ordering_type ='OQNP';
                } elseif ($this->object->getOrderingType() == OQ_NESTED_TERMS) {
                    $ordering_type ='OQNT';
                } elseif ($this->object->getOrderingType() == OQ_TERMS) {
                    $ordering_type ='OQT';
                }

                $attrs = array("respident" => $ordering_type);
                
                $attrs["index"] = $element->getPosition();
                $a_xml_writer->xmlElement("varequal", $attrs, $element->getPosition());
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
        foreach ($this->object->getOrderingElementList() as $element) {
            $attrs = array(
                "ident" => "link_" . $element->getPosition(),
                "view" => "All"
            );
            $a_xml_writer->xmlStartTag("itemfeedback", $attrs);
            // qti flow_mat
            $a_xml_writer->xmlStartTag("flow_mat");
            $a_xml_writer->xmlStartTag("material");
            $a_xml_writer->xmlElement("mattext", null, $this->object->feedbackOBJ->getSpecificAnswerFeedbackExportPresentation(
                $this->object->getId(),
                0,
                $element->getPosition()
            ));
            $a_xml_writer->xmlEndTag("material");
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
