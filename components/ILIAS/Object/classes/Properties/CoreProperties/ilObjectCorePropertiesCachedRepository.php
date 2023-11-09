<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

use ILIAS\Object\Properties\CoreProperties\TileImage\ilObjectPropertyTileImage;
use ILIAS\Object\Properties\CoreProperties\TileImage\ilObjectTileImage;
use ILIAS\Object\Properties\CoreProperties\TileImage\ilObjectTileImageStakeholder;
use ILIAS\Object\Properties\CoreProperties\TileImage\ilObjectTileImageFlavourDefinition;
use ILIAS\Object\Properties\ObjectTypeSpecificProperties\Factory as ObjectTypeSpecificPropertiesFactory;
use ILIAS\DI\UIServices;
use ILIAS\ResourceStorage\Services as ResourceStorageService;

/**
 *
 * @author Stephan Kergomard
 */
class ilObjectCorePropertiesCachedRepository implements ilObjectCorePropertiesRepository
{
    private const CORE_PROPERTIES_TABLE = 'object_data';
    private const DESCRIPTION_TABLE = 'object_description';

    private array $data_cache = [];

    public function __construct(
        private ilDBInterface $database,
        private UIServices $ui,
        private ResourceStorageService $storage_services,
        private ilObjectTileImageStakeholder $storage_stakeholder,
        private ilObjectTileImageFlavourDefinition $flavour_definition,
        private ObjectTypeSpecificPropertiesFactory $object_type_specific_properties_factory
    ) {
    }

    public function preload(array $object_ids): void
    {
        $this->data_cache += $this->retrieveDataForObjectIds($object_ids);
    }

    public function resetPreloadedData(): void
    {
        $this->data_cache = [];
    }

    public function getFor(?int $object_id): ilObjectCoreProperties
    {
        if ($object_id === null
            || $object_id === 0) {
            return $this->getDefaultCoreProperties();
        }

        if (!isset($this->data_cache[$object_id])) {
            $this->data_cache[$object_id] = $this->retrieveDataForObjectId($object_id);
        }

        $data = $this->data_cache[$object_id];

        $object_type_specific_properties = $this->object_type_specific_properties_factory->getForObjectTypeString($data['type']);
        $providers = null;
        $modifications = null;
        if ($object_type_specific_properties !== null) {
            $providers = $object_type_specific_properties->getProviders();
            $modifications = $object_type_specific_properties->getModifications();
        }
        return new ilObjectCoreProperties(
            new ilObjectPropertyTitleAndDescription(
                array_shift($data),
                array_shift($data),
                $modifications
            ),
            new ilObjectPropertyIsOnline(array_shift($data)),
            new ilObjectPropertyTileImage(
                new ilObjectTileImage(
                    $object_id,
                    $data['type'],
                    array_shift($data),
                    $this->ui->factory()->image(),
                    $this->storage_services,
                    $this->storage_stakeholder,
                    $this->flavour_definition,
                    $providers
                )
            ),
            $data
        );
    }

    public function store(ilObjectCoreProperties $properties): ilObjectCoreProperties
    {
        if ($properties->getObjectId() === null || $properties->getOwner() === null) {
            throw new \Exception('The current configuration cannot be saved.');
        }

        if ($properties->getPropertyTileImage()->getDeletedFlag()) {
            $this->deleteOldTileImage($properties->getPropertyTileImage()->getTileImage());
            $properties = $properties->withPropertyTileImage(
                $properties->getPropertyTileImage()->withTileImage(
                    $properties->getPropertyTileImage()->getTileImage()->withRid(null)
                )
            );
            /**
             * Remove with ILIAS10
             */
            $properties->getPropertyTileImage()->getTileImage()->deleteLegacyTileImage();
        }

        /**
         * Remove with ILIAS10
         */
        if ($properties->getPropertyTileImage()->getTileImage()->getRid() !== null) {
            $properties->getPropertyTileImage()->getTileImage()->deleteLegacyTileImage();
        }

        $where = [
            'obj_id' => [ilDBConstants::T_INTEGER, $properties->getObjectId()]
        ];

        $storage_array = [
            'type' => [ilDBConstants::T_TEXT, $properties->getType()],
            'title' => [ilDBConstants::T_TEXT, $properties->getPropertyTitleAndDescription()->getTitle()],
            'description' => [ilDBConstants::T_TEXT, $properties->getPropertyTitleAndDescription()->getDescription()],
            'owner' => [ilDBConstants::T_INTEGER, $properties->getOwner()],
            'create_date' => [ilDBConstants::T_DATETIME, $properties->getCreateDate()->format('Y-m-d H:i:s')],
            'last_update' => [ilDBConstants::T_DATETIME, $properties->getLastUpdateDate()->format('Y-m-d H:i:s')],
            'import_id' => [ilDBConstants::T_TEXT, $properties->getImportId()],
            'offline' => [ilDBConstants::T_INTEGER, (int) !$properties->getPropertyIsOnline()->getIsOnline()],
            'tile_image_rid' => [ilDBConstants::T_TEXT, $properties->getPropertyTileImage()->getTileImage()->getRid()]
        ];
        $this->database->update(self::CORE_PROPERTIES_TABLE, $storage_array, $where);

        $this->storeLongDescription($properties->getPropertyTitleAndDescription()->getLongDescription(), $where);

        return $properties;
    }

    private function deleteOldTileImage(ilObjectTileImage $tile_image): void
    {
        if ($tile_image->getRid() === null) {
            return;
        }

        $i = $this->storage_services->manage()->find($tile_image->getRid());
        if ($i === null) {
            return;
        }

        $this->storage_services->manage()->remove(
            $i,
            $this->storage_stakeholder
        );
    }

    private function getDefaultCoreProperties(): ilObjectCoreProperties
    {
        return new ilObjectCoreProperties(
            new ilObjectPropertyTitleAndDescription(),
            new ilObjectPropertyIsOnline(),
            new ilObjectPropertyTileImage()
        );
    }

    /**
     * @return array<mixed>
     */
    protected function retrieveDataForObjectId(int $object_id): array
    {
        $where = 'WHERE obj.obj_id=' . $this->database->quote($object_id, 'integer');
        $data = $this->retrieveDataForWhereClause($where);

        if ($data === []) {
            throw new \Exception('The object with the following id does not exist: '
                . (string) $object_id);
        }

        return $data[$object_id];
    }

    /**
     * @param array<int> $object_ids
     */
    protected function retrieveDataForObjectIds(array $object_ids): array
    {
        $where = 'WHERE ' . $this->database->in('obj.obj_id', $object_ids, false, ilDBConstants::T_INTEGER);
        return $this->retrieveDataForWhereClause($where);
    }

    protected function retrieveDataForWhereClause(string $where): array
    {
        $query = 'SELECT '
            . 'obj.obj_id, obj.type, obj.title, obj.description, obj.owner,' . PHP_EOL
            . 'obj.create_date, obj.last_update, obj.import_id, obj.offline,' . PHP_EOL
            . 'obj.tile_image_rid, descr.description' . PHP_EOL
            . 'FROM ' . self::CORE_PROPERTIES_TABLE . ' AS obj' . PHP_EOL
            . 'LEFT JOIN ' . self::DESCRIPTION_TABLE . ' AS descr' . PHP_EOL
            . 'ON obj.obj_id = descr.obj_id' . PHP_EOL
            . $where;

        $statement = $this->database->query($query);
        $num_rows = $this->database->numRows($statement);

        if ($num_rows === 0) {
            return [];
        }

        $data = [];
        while ($row = $this->database->fetchAssoc($statement)) {
            $data[$row['obj_id']] = [
                'title' => $row['title'],
                'long_description' => $row['description'] ?? '',
                'is_online' => !((bool) $row['offline']),
                'tile_image_rid' => $row['tile_image_rid'],
                'object_id' => $row['obj_id'],
                'type' => $row['type'],
                'owner' => $row['owner'],
                'import_id' => $row['import_id'],
                'create_date' => new DateTimeImmutable($row['create_date']),
                'update_date' => new DateTimeImmutable($row['last_update'])
            ];
        }

        return $data;
    }

    protected function storeLongDescription(string $long_description, array $where): void
    {
        $description_array = [
            'description' => [ilDBConstants::T_TEXT, $long_description]
        ];
        $this->database->update(self::DESCRIPTION_TABLE, $description_array, $where);
    }
}
