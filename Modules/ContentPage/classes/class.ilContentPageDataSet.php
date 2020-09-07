<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilContentPageDataSet
 */
class ilContentPageDataSet extends \ilDataSet implements \ilContentPageObjectConstants
{
    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var int[]
     */
    protected $newMobIds = [];

    /**
     * @inheritdoc
     */
    public function getSupportedVersions()
    {
        return [
            '5.4.0',
        ];
    }

    /**
     * @inheritdoc
     */
    public function getXmlNamespace($a_entity, $a_schema_version)
    {
        return 'http://www.ilias.de/xml/Modules/ContentPage/' . $a_entity;
    }

    /**
     * @inheritdoc
     */
    protected function getTypes($a_entity, $a_version)
    {
        switch ($a_entity) {
            case self::OBJ_TYPE:
                return [
                    'id' => 'integer',
                    'title' => 'text',
                    'description' => 'text',
                    'info-tab' => 'integer',
                    'style-id' => 'integer',
                ];

            default:
                return [];
        }
    }

    /**
     * @inheritdoc
     */
    public function readData($a_entity, $a_version, $a_ids)
    {
        $this->data = [];

        if (!is_array($a_ids)) {
            $a_ids = [$a_ids];
        }

        $this->readEntityData($a_entity, $a_ids);
    }


    /**
     * @param string $entity
     * @param array $ids
     */
    protected function readEntityData($entity, $ids)
    {
        switch ($entity) {
            case self::OBJ_TYPE:
                foreach ($ids as $objId) {
                    if (\ilObject::_lookupType($objId) == self::OBJ_TYPE) {
                        /** @var \ilObjContentPage $obj */
                        $obj = \ilObjectFactory::getInstanceByObjId($objId);

                        $this->data[] = [
                            'id' => $obj->getId(),
                            'title' => $obj->getTitle(),
                            'description' => $obj->getDescription(),
                            'info-tab' => (int) \ilContainer::_lookupContainerSetting(
                                $obj->getId(),
                                \ilObjectServiceSettingsGUI::INFO_TAB_VISIBILITY,
                                true
                            ),
                            'style-id' => $obj->getStyleSheetId(),
                        ];
                    }
                }
                break;

            default:
                break;
        }
    }

    /**
     * @param $a_entity
     * @param $a_types
     * @param $a_rec
     * @param \ilImportMapping $a_mapping
     * @param $a_schema_version
     */
    public function importRecord($a_entity, $a_types, $a_rec, $a_mapping, $a_schema_version)
    {
        switch ($a_entity) {
            case self::OBJ_TYPE:
                if ($newObjId = $a_mapping->getMapping('Services/Container', 'objs', $a_rec['id'])) {
                    $newObject = \ilObjectFactory::getInstanceByObjId($newObjId, false);
                } else {
                    $newObject = new \ilObjContentPage();
                }

                $newObject->setTitle(\ilUtil::stripSlashes($a_rec['title']));
                $newObject->setDescription(\ilUtil::stripSlashes($a_rec['description']));
                $newObject->setStyleSheetId((int) \ilUtil::stripSlashes($a_rec['style-id']));

                if (!$newObject->getId()) {
                    $newObject->create();
                }

                \ilContainer::_writeContainerSetting(
                    $newObject->getId(),
                    \ilObjectServiceSettingsGUI::INFO_TAB_VISIBILITY,
                    (int) $a_rec['info-tab']
                );

                $a_mapping->addMapping('Modules/ContentPage', self::OBJ_TYPE, $a_rec['id'], $newObject->getId());
                $a_mapping->addMapping('Modules/ContentPage', 'style', $newObject->getId(), $newObject->getStyleSheetId());
                $a_mapping->addMapping(
                    'Services/COPage',
                    'pg',
                    self::OBJ_TYPE . ':' . $a_rec['id'],
                    self::OBJ_TYPE . ':' . $newObject->getId()
                );
                break;
        }
    }

    /**
     * @inheritdoc
     */
    protected function getDependencies($a_entity, $a_version, $a_rec, $a_ids)
    {
        return false;
    }
}
