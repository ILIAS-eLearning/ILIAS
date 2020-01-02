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
    public function getSupportedVersions()
    {
        return array('5.2.0', '5.3.0');
    }


    /**
     * @param string $entity
     * @param string $schema_version
     *
     * @return string
     */
    public function getXmlNamespace($entity, $schema_version)
    {
        return 'http://www.ilias.de/xml/Modules/IndividualAssessment/' . $entity;
    }

    /**
     * Map XML attributes of entities to datatypes (text, integer...)
     *
     * @param string $entity
     * @param string $version
     *
     * @return array
     */
    protected function getTypes($entity, $version)
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
     *
     * @param string $entity
     * @param string $version
     * @param array  $rec
     * @param array  $ids
     *
     * @return array
     */
    protected function getDependencies($entity, $version, $rec, $ids)
    {
        return false;
    }

    /**
     * Read data from Cache for a given entity and ID(s)
     *
     * @param string $entity
     * @param string $version
     * @param array  $ids one or multiple ids
     */
    public function readData($entity, $version, $ids)
    {
        $this->data = array();
        if (!is_array($ids)) {
            $ids = array($ids);
        }
        $this->_readData($entity, $ids);
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
                            'content' => $settings->content(),
                            'recordTemplate' => $settings->recordTemplate(),
                            'eventTimePlaceRequired' => (int) $settings->eventTimePlaceRequired(),
                            'file_required' => (int) $settings->fileRequired(),
                            "contact" => $info->contact(),
                            "responsibility" => $info->responsibility(),
                            "phone" => $info->phone(),
                            "mails" => $info->mails(),
                            "consultation_hours" => $info->consultationHours()
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
     *
     * @param 	string 										$entity
     * @param 	ilIndividualAssessmentDataSet | null		$types
     * @param 	array 										$rec
     * @param 	ilImportMapping 							$mapping
     * @param 	string 										$schema_version
     * @return 	void
     */
    public function importRecord($entity, $types, array $rec, ilImportMapping $mapping, $schema_version)
    {
        assert(is_string($entity));
        assert(is_object($types) || is_null($types));
        assert(is_string($schema_version));

        switch ($entity) {
            case "iass":
                if ($new_id = $mapping->getMapping('Services/Container', 'objs', $rec['Id'])) {
                    $newObj = ilObjectFactory::getInstanceByObjId($new_id, false);
                } else {
                    $newObj = new ilObjIndividualAssessment();
                    $newObj->create();
                }
                $settings = new ilIndividualAssessmentSettings(
                    $newObj,
                    $rec["content"],
                    $rec["recordTemplate"],
                    $rec['file_required'],
                    $rec['eventTimePlaceRequired']
                );

                $info = new ilIndividualAssessmentInfoSettings(
                    $newObj,
                    $rec['contact'],
                    $rec['responsibility'],
                    $rec['phone'],
                    $rec['mails'],
                    $rec['consultation_hours']
                );

                $newObj->setTitle($rec["title"]);
                $newObj->setDescription($rec["description"]);
                $newObj->setSettings($settings);
                $newObj->setInfoSettings($info);
                $newObj->update();
                $newObj->updateInfo();

                $mapping->addMapping("Modules/IndividualAssessment", "iass", $rec["Id"], $newObj->getId());
                break;
        }
    }
}
