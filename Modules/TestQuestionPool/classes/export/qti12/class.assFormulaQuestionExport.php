<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Modules/TestQuestionPool/classes/export/qti12/class.assQuestionExport.php";

/**
* Class for formula question question exports
*
* assSingleChoiceExport is a class for single choice question exports
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id: class.assFormulaQuestionExport.php 1185 2010-02-02 08:36:26Z hschottm $
* @ingroup ModulesTestQuestionPool
*/
class assFormulaQuestionExport extends assQuestionExport
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
        
        include_once 'Services/Xml/classes/class.ilXmlWriter.php';
        $a_xml_writer = new ilXmlWriter;
        // set xml header
        $a_xml_writer->xmlHeader();
        $a_xml_writer->xmlStartTag("questestinterop");
        $attrs = array(
            "ident" => "il_" . IL_INST_ID . "_qst_" . $this->object->getId(),
            "title" => $this->object->getTitle()
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
        $a_xml_writer->xmlElement("fieldentry", null, $this->object->getQuestionType());
        $a_xml_writer->xmlEndTag("qtimetadatafield");
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "AUTHOR");
        $a_xml_writer->xmlElement("fieldentry", null, $this->object->getAuthor());
        $a_xml_writer->xmlEndTag("qtimetadatafield");
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "points");
        $a_xml_writer->xmlElement("fieldentry", null, $this->object->getPoints());
        $a_xml_writer->xmlEndTag("qtimetadatafield");
        foreach ($this->object->getVariables() as $variable) {
            $var = array(
                "precision" => $variable->getPrecision(),
                "intprecision" => $variable->getIntprecision(),
                "rangemin" => $variable->getRangeMin(),
                "rangemax" => $variable->getRangeMax(),
                "unit" => (is_object($variable->getUnit())) ? $variable->getUnit()->getUnit() : "",
                "unitvalue" => (is_object($variable->getUnit())) ? $variable->getUnit()->getId() : ""
            );
            $a_xml_writer->xmlStartTag("qtimetadatafield");
            $a_xml_writer->xmlElement("fieldlabel", null, $variable->getVariable());
            $a_xml_writer->xmlElement("fieldentry", null, serialize($var));
            $a_xml_writer->xmlEndTag("qtimetadatafield");
        }
        foreach ($this->object->getResults() as $result) {
            $resultunits = $this->object->getResultUnits($result);
            $ru = array();
            foreach ($resultunits as $unit) {
                array_push($ru, array("unit" => $unit->getUnit(), "unitvalue" => $unit->getId()));
            }
            $res = array(
                "precision" => $result->getPrecision(),
                "tolerance" => $result->getTolerance(),
                "rangemin" => $result->getRangeMin(),
                "rangemax" => $result->getRangeMax(),
                "points" => $result->getPoints(),
                "formula" => $result->getFormula(),
                "rating" => ($result->getRatingSimple()) ? "" : array("sign" => $result->getRatingSign(), "value" => $result->getRatingValue(), "unit" => $result->getRatingUnit()),
                "unit" => (is_object($result->getUnit())) ? $result->getUnit()->getUnit() : "",
                "unitvalue" => (is_object($result->getUnit())) ? $result->getUnit()->getId() : "",
                "resultunits" => $ru
            );
            $a_xml_writer->xmlStartTag("qtimetadatafield");
            $a_xml_writer->xmlElement("fieldlabel", null, $result->getResult());
            $a_xml_writer->xmlElement("fieldentry", null, serialize($res));
            $a_xml_writer->xmlEndTag("qtimetadatafield");
        }
        
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
        $a_xml_writer->xmlEndTag("flow");
        $a_xml_writer->xmlEndTag("presentation");
        
        $this->addGenericFeedback($a_xml_writer);
        
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
