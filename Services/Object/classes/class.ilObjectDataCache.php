<?php

declare(strict_types=1);

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

/**
 * class ilObjectDataCache
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 *
 * This class caches some properties of the object_data table. Like title description owner obj_id
 */
class ilObjectDataCache
{
    protected ilDBInterface $db;

    /** @var array<int, bool> */
    protected array $trans_loaded = [];
    protected array $reference_cache = [];
    protected array $object_data_cache = [];
    protected array $description_trans = [];

    public function __construct()
    {
        global $DIC;
        $this->db = $DIC->database();
    }

    public function deleteCachedEntry(int $obj_id): void
    {
        if (isset($this->object_data_cache[$obj_id])) {
            unset($this->object_data_cache[$obj_id]);
        }
    }

    public function lookupObjId(int $ref_id): int
    {
        if (!$this->__isReferenceCached($ref_id)) {
            $obj_id = $this->__storeReference($ref_id);
            $this->__storeObjectData($obj_id);
        }

        return (int) ($this->reference_cache[$ref_id] ?? 0);
    }

    public function lookupTitle(int $obj_id): string
    {
        if (!$this->__isObjectCached($obj_id)) {
            $this->__storeObjectData($obj_id);
        }

        return (string) ($this->object_data_cache[$obj_id]['title'] ?? '');
    }

    public function lookupType(int $obj_id): string
    {
        if (!$this->__isObjectCached($obj_id)) {
            $this->__storeObjectData($obj_id);
        }

        return (string) ($this->object_data_cache[$obj_id]['type'] ?? '');
    }

    public function lookupOwner(int $obj_id): int
    {
        if (!$this->__isObjectCached($obj_id)) {
            $this->__storeObjectData($obj_id);
        }

        return (int) ($this->object_data_cache[$obj_id]['owner']);
    }

    public function lookupDescription(int $obj_id): string
    {
        if (!$this->__isObjectCached($obj_id)) {
            $this->__storeObjectData($obj_id);
        }

        return (string) ($this->object_data_cache[$obj_id]['description'] ?? '');
    }

    public function lookupLastUpdate(int $obj_id): string
    {
        if (!$this->__isObjectCached($obj_id)) {
            $this->__storeObjectData($obj_id);
        }
        return (string) ($this->object_data_cache[$obj_id]['last_update']);
    }

    /**
     * Check if supports centralized offline handling and is offline
     */
    public function lookupOfflineStatus(int $obj_id): bool
    {
        if (!$this->__isObjectCached($obj_id)) {
            $this->__storeObjectData($obj_id);
        }

        return (bool) ($this->object_data_cache[$obj_id]['offline'] ?? false);
    }

    // PRIVATE

    /**
     * checks whether a reference id is already in cache or not
     */
    private function __isReferenceCached(int $ref_id): bool
    {
        if (isset($this->reference_cache[$ref_id])) {
            return true;
        }

        return false;
    }

    /**
     * checks whether an object is already in cache or not
     */
    private function __isObjectCached(int $obj_id): bool
    {
        if (isset($this->object_data_cache[$obj_id])) {
            return true;
        }

        return false;
    }

    /**
     * Stores Reference in cache.
     * Maybe it could be useful to find all references of that object and store them also in the cache.
     * But this would be an extra query.
     */
    private function __storeReference(int $ref_id): int
    {
        $sql =
            "SELECT obj_id" . PHP_EOL
            . "FROM object_reference" . PHP_EOL
            . "WHERE ref_id = " . $this->db->quote($ref_id, 'integer') . PHP_EOL
        ;
        $result = $this->db->query($sql);
        while ($row = $result->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
            $this->reference_cache[$ref_id] = (int) $row['obj_id'];
        }

        return (int) ($this->reference_cache[$ref_id] ?? 0);
    }

    /**
     * Stores object data in cache
     */
    private function __storeObjectData(int $obj_id): void
    {
        global $DIC;

        $obj_definition = $DIC["objDefinition"];
        $user = $DIC["ilUser"];

        $sql =
            "SELECT object_data.obj_id, object_data.type, object_data.title, object_data.description, " . PHP_EOL
            . "object_data.owner, object_data.create_date, object_data.last_update, object_data.import_id, " . PHP_EOL
            . "object_data.offline, object_description.description as long_description " . PHP_EOL
            . "FROM object_data LEFT JOIN object_description ON object_data.obj_id = object_description.obj_id " . PHP_EOL
            . "WHERE object_data.obj_id = " . $this->db->quote($obj_id, 'integer') . PHP_EOL
        ;
        $res = $this->db->query($sql);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->object_data_cache[$obj_id]['title'] = $row->title;
            $this->object_data_cache[$obj_id]['description'] = $row->description;
            if ($row->long_description !== null) {
                $this->object_data_cache[$row->obj_id]['description'] = $row->long_description;
            }
            $this->object_data_cache[$obj_id]['type'] = $row->type;
            $this->object_data_cache[$obj_id]['owner'] = $row->owner;
            $this->object_data_cache[$obj_id]['last_update'] = $row->last_update;
            $this->object_data_cache[$obj_id]['offline'] = $row->offline;

            $translation_type = $obj_definition->getTranslationType($row->type);

            if ($translation_type === "db" && !isset($this->trans_loaded[$obj_id])) {
                $sql =
                    "SELECT title, description" . PHP_EOL
                    . "FROM object_translation" . PHP_EOL
                    . "WHERE obj_id = " . $this->db->quote($obj_id, 'integer') . PHP_EOL
                    . "AND lang_code = " . $this->db->quote($user->getLanguage(), 'text') . PHP_EOL
                ;
                $trans_res = $this->db->query($sql);

                $trans_row = $trans_res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);
                if ($trans_row) {
                    $this->object_data_cache[$obj_id]['title'] = $trans_row->title;
                    $this->object_data_cache[$obj_id]['description'] = $trans_row->description;
                    $this->description_trans[] = $obj_id;
                }
                $this->trans_loaded[$obj_id] = true;
            }
        }
    }

    public function isTranslatedDescription(int $obj_id): bool
    {
        return in_array($obj_id, $this->description_trans);
    }

    /**
     * Stores object data in cache
     * @param int[] $obj_ids
     * @param string $lang
     */
    public function preloadObjectCache(array $obj_ids, string $lang = ''): void
    {
        global $DIC;

        $obj_definition = $DIC["objDefinition"];
        $user = $DIC["ilUser"];

        if ($lang == "") {
            $lang = $user->getLanguage();
        }

        if ($obj_ids === []) {
            return;
        }

        $sql =
            "SELECT object_data.obj_id, object_data.type, object_data.title, object_data.description, " . PHP_EOL
            . "object_data.owner, object_data.create_date, object_data.last_update, object_data.import_id, " . PHP_EOL
            . "object_data.offline, object_description.description as long_description " . PHP_EOL
            . "FROM object_data LEFT JOIN object_description ON object_data.obj_id = object_description.obj_id " . PHP_EOL
            . "WHERE " . $this->db->in('object_data.obj_id', $obj_ids, false, 'integer') . PHP_EOL
        ;
        $res = $this->db->query($sql);
        $db_trans = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $obj_id = (int) $row->obj_id;

            if (!isset($this->trans_loaded[$obj_id])) {
                $this->object_data_cache[$obj_id]['title'] = $row->title;
                $this->object_data_cache[$obj_id]['description'] = $row->description;
                if ($row->long_description !== null) {
                    $this->object_data_cache[$row->obj_id]['description'] = $row->long_description;
                }
            }
            $this->object_data_cache[$obj_id]['type'] = $row->type;
            $this->object_data_cache[$obj_id]['owner'] = $row->owner;
            $this->object_data_cache[$obj_id]['last_update'] = $row->last_update;
            $this->object_data_cache[$obj_id]['offline'] = $row->offline;

            $translation_type = $obj_definition->getTranslationType($row->type);

            if ($translation_type === "db") {
                $db_trans[$obj_id] = $obj_id;
            }
        }

        if (count($db_trans) > 0) {
            $this->preloadTranslations($db_trans, $lang);
        }
    }

    /**
     * Preload translation information
     * @param int[] $obj_ids
     * @param string $lang
     */
    public function preloadTranslations(array $obj_ids, string $lang): void
    {
        $ids = [];
        foreach ($obj_ids as $id) {
            // do not load an id more than one time
            if (!isset($this->trans_loaded[$id])) {
                $ids[] = $id;
                $this->trans_loaded[$id] = true;
            }
        }

        if ($ids !== []) {
            $sql =
                "SELECT obj_id, title, description" . PHP_EOL
                . "FROM object_translation" . PHP_EOL
                . "WHERE " . $this->db->in('obj_id', $ids, false, 'integer') . PHP_EOL
                . "AND lang_code = " . $this->db->quote($lang, 'text') . PHP_EOL
            ;
            $result = $this->db->query($sql);
            while ($row = $result->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                $obj_id = (int) $row->obj_id;

                $this->object_data_cache[$obj_id]['title'] = $row->title;
                $this->object_data_cache[$obj_id]['description'] = $row->description;
                $this->description_trans[] = $obj_id;
            }
        }
    }

    /**
     * @param int[] $ref_ids
     * @param bool $incl_obj
     */
    public function preloadReferenceCache(array $ref_ids, bool $incl_obj = true): void
    {
        if ($ref_ids === []) {
            return;
        }

        $sql =
            "SELECT ref_id, obj_id" . PHP_EOL
            . "FROM object_reference" . PHP_EOL
            . "WHERE " . $this->db->in('ref_id', $ref_ids, false, 'integer') . PHP_EOL
        ;
        $res = $this->db->query($sql);

        $obj_ids = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
            $this->reference_cache[(int) $row['ref_id']] = (int) $row['obj_id'];
            $obj_ids[] = (int) $row['obj_id'];
        }

        if ($incl_obj) {
            $this->preloadObjectCache($obj_ids);
        }
    }
}
