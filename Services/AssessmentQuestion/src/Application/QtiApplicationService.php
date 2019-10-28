<?php

namespace ILIAS\AssessmentQuestion\Application;

use ILIAS\AssessmentQuestion\DomainModel\QuestionData;
use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;
use SimpleXMLElement;

/**
 * Class AuthoringApplicationService
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class QtiApplicationService
{


    const QTI_ITEM_META_DATA = "itemmetadata/qtimetadata/qtimetadatafield";

    const QTI_PRESENTATION = "presentation";
    const QTI_PRESENTATION_material_mattext = "flow/material/mattext";





    public function getQuestionDtoFromXml(string $qti_item_xml) {
        $question_xml =  simplexml_load_string($qti_item_xml);


        print_r($question_xml);

        $question_dto = new QuestionDto();

        $qti_metadata_fields = $question_xml->itemmetadata->qtimetadata->qtimetadatafield;
            foreach($qti_metadata_fields as $qti_metadata_field) {
                switch  ((string)$qti_metadata_field->fieldlabel) {
                    case 'AUTHOR':
                        $question_author = (string)$qti_metadata_field->fieldentry;
                        break;
                }
            }

        $qti_presentation = $question_xml->presentation;
            $question_title = (string)$qti_presentation->attributes()['label'];
            $question_text = (string)$qti_presentation->flow->material->mattext;

        $question_data = QuestionData::create($question_title,$question_text,$question_author);
        $question_dto->setData($question_data);

        print_r($question_dto);exit;

        return $question_dto;
    }




}