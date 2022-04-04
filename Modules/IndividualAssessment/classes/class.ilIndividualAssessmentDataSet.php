<?php declare(strict_types=1);

/* Copyright (c) 2019 - Stefan Hecken <stefan.hecken@concepts-and-training.de> - Extended GPL, see LICENSE */

/**
 * Individual Assessment dataset class
 */
class ilIndividualAssessmentDataSet extends ilDataSet
{
    public function getSupportedVersions() : array
    {
        return ['5.2.0', '5.3.0'];
    }

    // PHP8-Review: Method visibility should not be overridden
    // PHP8-Review: Parameter's name changed during inheritance
    public function getXmlNamespace(string $entity, string $schema_version) : string
    {
        // PHP8-Review: Link with unencrypted protocol
        return 'http://www.ilias.de/xml/Modules/IndividualAssessment/' . $entity;
    }

    /**
     * Map XML attributes of entities to data types (text, integer...)
     */
    // PHP8-Review: Parameter's name changed during inheritance
    protected function getTypes(string $entity, string $version) : array
    {
        switch ($entity) {
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
     */
    // PHP8-Review: Parameter's name changed during inheritance
    protected function getDependencies(
        string $entity,
        string $version,
        ?array $rec = null,
        ?array $ids = null
    ) : array {
        return [];
    }

    /**
     * Read data from Cache for a given entity and ID(s)
     */
    // PHP8-Review: Parameter's name changed during inheritance
    public function readData(string $entity, string $version, array $ids) : void
    {
        $this->data = array();
        if (!is_array($ids)) {
            $ids = array($ids);
        }
        $this->_readData($entity, $ids);
    }

    /**
     * Build data array, data is read from cache except iass object itself
     */
    protected function _readData(string $entity, array $ids) : void
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
     */
    // PHP8-Review: Parameter's name changed during inheritance
    public function importRecord(
        string $entity,
        array $types,
        array $rec,
        ilImportMapping $mapping,
        string $schema_version
    ) : void {
        if ($entity == "iass") {
            if ($new_id = $mapping->getMapping('Services/Container', 'objs', $rec['id'])) {
                $newObj = ilObjectFactory::getInstanceByObjId($new_id, false);
            } else {
                $newObj = new ilObjIndividualAssessment();
                $newObj->create();
            }

            $newObj->setTitle($rec["title"]);
            $newObj->setDescription($rec["description"]);

            $settings = new ilIndividualAssessmentSettings(
                $newObj->getId(),
                $newObj->getTitle(),
                $newObj->getDescription(),
                $rec["content"],
                $rec["recordTemplate"],
                $rec['eventTimePlaceRequired'],
                $rec['file_required']
            );

            $info = new ilIndividualAssessmentInfoSettings(
                $newObj->getId(),
                $rec['contact'],
                $rec['responsibility'],
                $rec['phone'],
                $rec['mails'],
                $rec['consultation_hours']
            );

            $newObj->setSettings($settings);
            $newObj->setInfoSettings($info);
            $newObj->update();
            $newObj->updateInfo();
            $mapping->addMapping("Modules/IndividualAssessment", "iass", $rec["id"], (string) $newObj->getId());
        }
    }
}
