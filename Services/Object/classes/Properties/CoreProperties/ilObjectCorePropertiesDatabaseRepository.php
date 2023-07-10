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

/**
 *
 * @author Stephan Kergomard
 */
class ilObjectCorePropertiesDatabaseRepository implements ilObjectCorePropertiesRepository
{
    private const CORE_PROPERTIES_TABLE = 'object_data';
    private const DESCRIPTION_TABLE = 'object_description';

    public function __construct(
        private ilDBInterface $database
    ) {
    }

    public function getFor(?int $object_id): ilObjectCoreProperties
    {
        if ($object_id === null) {
            return $this->getDefaultCoreProperties();
        }

        $data =  $this->retrieveDataForObjectId($object_id);
        return new ilObjectCoreProperties(
            new ilObjectPropertyTitleAndDescription(array_shift($data), array_shift($data)),
            new ilObjectPropertyIsOnline(array_shift($data)),
            $data
        );
    }

    public function store(ilObjectCoreProperties $properties): ilObjectCoreProperties
    {
        if ($properties->getObjectId() === null || $properties->getOwner() === null) {
            throw new \Exception('The current configuration cannot be saved.');
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
            'offline' => [ilDBConstants::T_INTEGER, (int) !$properties->getPropertyIsOnline()->getIsOnline()]
        ];
        $this->database->update(self::CORE_PROPERTIES_TABLE, $storage_array, $where);

        $this->storeLongDescription($properties->getPropertyTitleAndDescription()->getLongDescription(), $where);

        return $properties;
    }

    private function getDefaultCoreProperties(): ilObjectCoreProperties
    {
        return new ilObjectCoreProperties(
            new ilObjectPropertyTitleAndDescription(),
            new ilObjectPropertyIsOnline()
        );
    }

    /**
     * @return array<mixed>
     */
    protected function retrieveDataForObjectId(int $object_id): array
    {
        $query = 'SELECT '
            . 'type, title, description, owner, create_date, last_update, import_id, offline' . PHP_EOL
            . 'FROM ' . self::CORE_PROPERTIES_TABLE . PHP_EOL
            . 'WHERE obj_id=' . $this->database->quote($object_id, 'integer');

        $statement = $this->database->query($query);
        $num_rows = $this->database->numRows($statement);

        if ($num_rows === 0) {
            throw new \Exception('The object with the following id does not exist: '
                . (string) $object_id);
        }

        if ($num_rows > 1) {
            throw new \Exception('There is more than one object with the following id.'
                . 'This should very definitely never happen: '
                . (string) $object_id);
        }

        $row = $this->database->fetchAssoc($statement);

        $data = [
            'title' => $row['title'],
            'long_description' => $this->retrieveLongDescriptionForObjectId($object_id),
            'is_online' => !((bool) $row['offline']),
            'object_id' => $object_id,
            'type' => $row['type'],
            'owner' => $row['owner'],
            'import_id' => $row['import_id'],
            'create_date' => new DateTimeImmutable($row['create_date']),
            'update_date' => new DateTimeImmutable($row['last_update'])
        ];

        return $data;
    }

    protected function retrieveLongDescriptionForObjectId(int $object_id): string
    {
        $query = 'SELECT '
            . 'description FROM ' . self::DESCRIPTION_TABLE . PHP_EOL
            . 'WHERE obj_id=' . $this->database->quote($object_id, 'integer');

        $statement = $this->database->query($query);
        $num_rows = $this->database->numRows($statement);

        if ($num_rows === 0) {
            return '';
        }

        return $this->database->fetchAssoc($statement)['description'];
    }

    protected function storeLongDescription(string $long_description, array $where): void
    {
        $description_array = [
            'description' => [ilDBConstants::T_TEXT, $long_description]
        ];
        $this->database->update(self::DESCRIPTION_TABLE, $description_array, $where);
    }
}
