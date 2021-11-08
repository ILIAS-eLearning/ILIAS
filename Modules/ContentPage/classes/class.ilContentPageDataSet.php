<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilContentPageDataSet
 */
class ilContentPageDataSet extends ilDataSet implements ilContentPageObjectConstants
{
    /** @var int[] */
    protected array $newMobIds = [];

    public function getSupportedVersions() : array
    {
        return [
            '5.4.0',
        ];
    }

    protected function getXmlNamespace(string $a_entity, string $a_schema_version) : string
    {
        return 'http://www.ilias.de/xml/Modules/ContentPage/' . $a_entity;
    }

    protected function getTypes(string $a_entity, string $a_version) : array
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

    public function readData(string $a_entity, string $a_version, array $a_ids) : void
    {
        $this->data = [];

        if (!is_array($a_ids)) {
            $a_ids = [$a_ids];
        }

        $this->readEntityData($a_entity, $a_ids);
    }


    /**
     * @param string $entity
     * @param int[] $ids
     */
    protected function readEntityData(string $entity, array $ids) : void
    {
        switch ($entity) {
            case self::OBJ_TYPE:
                foreach ($ids as $objId) {
                    if (ilObject::_lookupType($objId) === self::OBJ_TYPE) {
                        /** @var ilObjContentPage $obj */
                        $obj = ilObjectFactory::getInstanceByObjId((int) $objId);

                        $this->data[] = [
                            'id' => $obj->getId(),
                            'title' => $obj->getTitle(),
                            'description' => $obj->getDescription(),
                            'info-tab' => (string) ((bool) ilContainer::_lookupContainerSetting(
                                $obj->getId(),
                                ilObjectServiceSettingsGUI::INFO_TAB_VISIBILITY,
                                '1'
                            )),
                            'style-id' => $obj->getStyleSheetId(),
                        ];
                    }
                }
                break;

            default:
                break;
        }
    }

    public function importRecord(
        string $a_entity,
        array $a_types,
        array $a_rec,
        ilImportMapping $a_mapping,
        string $a_schema_version
    ) : void {
        switch ($a_entity) {
            case self::OBJ_TYPE:
                if ($newObjId = $a_mapping->getMapping('Services/Container', 'objs', (string) $a_rec['id'])) {
                    $newObject = ilObjectFactory::getInstanceByObjId((int) $newObjId, false);
                } else {
                    $newObject = new ilObjContentPage();
                }

                $newObject->setTitle(ilUtil::stripSlashes($a_rec['title']));
                $newObject->setDescription(ilUtil::stripSlashes($a_rec['description']));
                $newObject->setStyleSheetId((int) ilUtil::stripSlashes($a_rec['style-id']));

                if (!$newObject->getId()) {
                    $newObject->create();
                }

                ilContainer::_writeContainerSetting(
                    $newObject->getId(),
                    ilObjectServiceSettingsGUI::INFO_TAB_VISIBILITY,
                    (string) ((bool) $a_rec['info-tab'])
                );

                $a_mapping->addMapping(
                    'Modules/ContentPage',
                    self::OBJ_TYPE,
                    (string) $a_rec['id'],
                    (string) $newObject->getId()
                );
                $a_mapping->addMapping(
                    'Modules/ContentPage',
                    'style',
                    (string) $newObject->getId(),
                    (string) $newObject->getStyleSheetId()
                );
                $a_mapping->addMapping(
                    'Services/COPage',
                    'pg',
                    self::OBJ_TYPE . ':' . $a_rec['id'],
                    self::OBJ_TYPE . ':' . $newObject->getId()
                );
                break;
        }
    }
}
