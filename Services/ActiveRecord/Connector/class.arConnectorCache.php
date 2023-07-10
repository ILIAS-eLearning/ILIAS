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

use ILIAS\Cache\Container\Container;
use ILIAS\Cache\Container\Request;
use ILIAS\Refinery\Custom\Transformation;

/**
 * Class ilGSStorageCache
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 */
class arConnectorCache extends arConnector implements Request
{
    private Container $cache_container;

    /**
     * ilGSStorageCache constructor.
     * @param int $ttl
     */
    public function __construct(private arConnector $arConnector)
    {
        global $DIC;
        $this->cache_container = $DIC->globalCache()->get($this);
    }

    protected function buildCacheKey(ActiveRecord $activeRecord): string
    {
        return $activeRecord->getConnectorContainerName() . "_" . $activeRecord->getPrimaryFieldValue();
    }

    public function getContainerKey(): string
    {
        return 'ar_cache';
    }

    public function isForced(): bool
    {
        return false;
    }

    /**
     * @return mixed
     */
    public function nextID(ActiveRecord $activeRecord)
    {
        return $this->arConnector->nextID($activeRecord);
    }

    public function checkConnection(ActiveRecord $activeRecord): bool
    {
        return $this->arConnector->checkConnection($activeRecord);
    }

    public function installDatabase(ActiveRecord $activeRecord, array $fields): bool
    {
        return $this->arConnector->installDatabase($activeRecord, $fields);
    }

    public function updateDatabase(ActiveRecord $activeRecord): bool
    {
        return $this->arConnector->updateDatabase($activeRecord);
    }

    public function resetDatabase(ActiveRecord $activeRecord): bool
    {
        return $this->arConnector->resetDatabase($activeRecord);
    }

    public function truncateDatabase(ActiveRecord $activeRecord): bool
    {
        return $this->arConnector->truncateDatabase($activeRecord);
    }

    public function checkTableExists(ActiveRecord $activeRecord): bool
    {
        return $this->arConnector->checkTableExists($activeRecord);
    }

    public function checkFieldExists(ActiveRecord $activeRecord, string $field_name): bool
    {
        return $this->arConnector->checkFieldExists($activeRecord, $field_name);
    }

    public function removeField(ActiveRecord $activeRecord, string $field_name): bool
    {
        return $this->arConnector->removeField($activeRecord, $field_name);
    }

    public function renameField(ActiveRecord $activeRecord, string $old_name, string $new_name): bool
    {
        return $this->arConnector->renameField($activeRecord, $old_name, $new_name);
    }

    public function create(ActiveRecord $activeRecord): void
    {
        $this->arConnector->create($activeRecord);
        $this->storeActiveRecordInCache($activeRecord);
    }

    /**
     * @return \stdClass[]|mixed[]
     */
    public function read(ActiveRecord $activeRecord): array
    {
        $key = $this->buildCacheKey($activeRecord);
        if ($this->cache_container->has($key)) {
            $cached_value = $this->cache_container->get(
                $key,
                new Transformation(function ($value): ?array {
                    return is_array($value) ? $value : null;
                })
            );
            if (is_array($cached_value)) {
                return array_map(function ($result): \stdClass {
                    return (object) $result;
                }, $cached_value);
            }
        }

        $results = $this->arConnector->read($activeRecord);

        $this->cache_container->set(
            $key,
            array_map(function ($result): array {
                return (array) $result;
            }, $results)
        );

        return $results;
    }

    public function update(ActiveRecord $activeRecord): void
    {
        $this->arConnector->update($activeRecord);
        $this->storeActiveRecordInCache($activeRecord);
    }

    public function delete(ActiveRecord $activeRecord): void
    {
        $this->arConnector->delete($activeRecord);
        $key = $this->buildCacheKey($activeRecord);
        $this->cache_container->delete($key);
    }

    /**
     * @return mixed[]
     */
    public function readSet(ActiveRecordList $activeRecordList): array
    {
        return $this->arConnector->readSet($activeRecordList);
    }

    public function affectedRows(ActiveRecordList $activeRecordList): int
    {
        return $this->arConnector->affectedRows($activeRecordList);
    }

    /**
     * @param        $value
     */
    public function quote($value, string $type): string
    {
        return $this->arConnector->quote($value, $type);
    }

    public function updateIndices(ActiveRecord $activeRecord): void
    {
        $this->arConnector->updateIndices($activeRecord);
    }

    /**
     * Stores an active record into the Cache.
     */
    private function storeActiveRecordInCache(ActiveRecord $activeRecord): void
    {
        $key = $this->buildCacheKey($activeRecord);
        $value = $activeRecord->asArray();

        $this->cache_container->set($key, $value);
    }
}
