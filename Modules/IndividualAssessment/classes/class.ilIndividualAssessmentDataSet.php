<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once("./Services/DataSet/classes/class.ilDataSet.php");

/**
 * Individual Assessment dataset class
 *
 * @author  Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ilIndividualAssessmentDataSet extends ilDataSet
{

    /**
     * @return array
     */
    public function getSupportedVersions() : array
    {
        return array('5.2.0', '5.3.0');
    }


    /**
     * @param string $a_entity
     * @param string $a_schema_version
     * @return string
     */
    public function getXmlNamespace(string $a_entity, string $a_schema_version) : string
    {
        return 'http://www.ilias.de/xml/Modules/IndividualAssessment/' . $a_entity;
    }

    /**
     * Map XML attributes of entities to datatypes (text, integer...)
     * @param string $a_entity
     * @param string $a_version
     * @return array
     */
    protected function getTypes(string $a_entity, string $a_version) : array
    {
        switch ($a_entity) {
            case 'iass':
                return array(
                    "id" => "integer",
                    "title" => "text",
                    "description" => "text",
                    "content" => "text",
                    "recordTemplate" => "text",
                    "eventTimePlaceRequired" => "integer",
                    "file_required" => "integer",
                    "contact" => "text",
                    "responsibility" => "text",
                    "phone" => "text",
                    "mails" => "text",
                    "consultation_hours" => "text"
                );
            default:
                return array();
        }
    }

    /**
     * Return dependencies form entities to other entities (in our case these are all the DB relations)
     * @param string $a_entity
     * @param string $a_version
     * @param array|null $a_rec
     * @param array|null $a_ids
     * @return array
     */
    protected function getDependencies(
        string $a_entity,
        string $a_version,
        ?array $a_rec = null,
        ?array $a_ids = null
    ) : array {
        return [];
    }

    /**
     * Read data from Cache for a given entity and ID(s)
     * @param string $a_entity
     * @param string $a_version
     * @param array  $a_ids one or multiple ids
     */
    public function readData(string $a_entity, string $a_version, array $a_ids) : void
    {
        $this->data = array();
        if (!is_array($a_ids)) {
            $a_ids = array($a_ids);
        }
        $this->_readData($a_entity, $a_ids);
    }

    /**
     * Build data array, data is read from cache except iass object itself
     *
     * @param string $entity
     * @param array  $ids
     */
    protected function _readData($entity, $ids)
    {
        switch ($entity) {
            case 'iass':
                foreach ($ids as $iass_id) {
                    if (ilObject::_lookupType($iass_id) == 'iass') {
                        $obj = new ilObjIndividualAssessment($iass_id, false);
                        $settings = $obj->getSettings();
                        $info = $obj->getInfoSettings();
                        $data = array(
                            'id' => $iass_id,
                            'title' => $obj->getTitle(),
                            'description' => $obj->getDescription(),
                            'content' => $settings->getContent(),
                            'recordTemplate' => $settings->getRecordTemplate(),
                            'eventTimePlaceRequired' => (int) $settings->isEventTimePlaceRequired(),
                            'file_required' => (int) $settings->isFileRequired(),
                            "contact" => $info->getContact(),
                            "responsibility" => $info->getResponsibility(),
                            "phone" => $info->getPhone(),
                            "mails" => $info->getMails(),
                            "consultation_hours" => $info->getConsultationHours()
                        );
                        $this->data[] = $data;
                    }
                }
                break;
            default:
        }
    }

    /**
     * Import record
     * @param 	string $a_entity
     * @param 	array $a_types
     * @param 	array $a_rec
     * @param 	ilImportMapping $a_mapping
     * @param 	string $a_schema_version
     * @return 	void
     */
    public function importRecord(
        string $a_entity,
        array $a_types,
        array $a_rec,
        ilImportMapping $a_mapping,
        string $a_schema_version
    ) : void {
        assert(is_string($a_entity));
        assert(is_object($a_types) || is_null($a_types));
        assert(is_string($a_schema_version));

        switch ($a_entity) {
            case "iass":
                if ($new_id = $a_mapping->getMapping('Services/Container', 'objs', $a_rec['id'])) {
                    $newObj = ilObjectFactory::getInstanceByObjId($new_id, false);
                } else {
                    $newObj = new ilObjIndividualAssessment();
                    $newObj->create();
                }

                $newObj->setTitle($a_rec["title"]);
                $newObj->setDescription($a_rec["description"]);

                $settings = new ilIndividualAssessmentSettings(
                    (int) $newObj->getId(),
                    $newObj->getTitle(),
                    $newObj->getDescription(),
                    $a_rec["content"],
                    $a_rec["recordTemplate"],
                    $a_rec['eventTimePlaceRequired'],
                    $a_rec['file_required']
                );

                $info = new ilIndividualAssessmentInfoSettings(
                    (int) $newObj->getId(),
                    $a_rec['contact'],
                    $a_rec['responsibility'],
                    $a_rec['phone'],
                    $a_rec['mails'],
                    $a_rec['consultation_hours']
                );

                $newObj->setSettings($settings);
                $newObj->setInfoSettings($info);
                $newObj->update();
                $newObj->updateInfo();
                $a_mapping->addMapping("Modules/IndividualAssessment", "iass", $a_rec["id"], $newObj->getId());
                break;
        }
    }
}
