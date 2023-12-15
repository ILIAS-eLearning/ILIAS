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

use ILIAS\Cache\Container\Request;
use ILIAS\Refinery\Custom\Transformation;

/**
 * Cache for object definitions, based on ilGlobalCache.
 */
class ilCachedObjectDefinition implements Request
{
    protected static ?ilCachedObjectDefinition $instance = null;
    private \ILIAS\Cache\Container\Container $cache;

    protected array $cached_results = [];
    protected bool $changed = false;
    protected array $il_object_def = [];
    protected array $subobj_for_parent = [];
    protected array $grouped_rep_obj_types = [];
    protected array $il_object_group = [];
    protected array $il_object_sub_type = [];

    protected function __construct()
    {
        global $DIC;
        $this->cache = $DIC->globalCache()->get($this);
        $this->init();
    }

    public function getContainerKey(): string
    {
        return 'objdef';
    }


    public function isForced(): bool
    {
        return false;
    }

    public function init(): void
    {
        if ($this->cache->has('data')) {
            $this->readFromCache();
        } else {
            $this->readFromDB();
            $this->saveToCache();
        }
    }

    protected function loadFromRawData(array $data): void
    {
        $this->il_object_def = $data['il_object_def'];
        $this->subobj_for_parent = $data['subobj_for_parent'];
        $this->grouped_rep_obj_types = $data['grouped_rep_obj_types'];
        $this->il_object_group = $data['il_object_group'];
        $this->il_object_sub_type = $data['il_object_sub_type'];
    }

    protected function getRawData(): array
    {
        return [
            'il_object_def' => $this->il_object_def,
            'subobj_for_parent' => $this->subobj_for_parent,
            'grouped_rep_obj_types' => $this->grouped_rep_obj_types,
            'il_object_group' => $this->il_object_group,
            'il_object_sub_type' => $this->il_object_sub_type,
        ];
    }

    protected function saveToCache(): void
    {
        $this->cache->set('data', $this->getRawData());
    }

    protected function readFromCache(): void
    {
        // This is a workaround for the fact that transformatuin cannot be created by
        // $DIC->refinery()->xy() since we are in a hell of dependencies. E.g. we cant instantiate the
        // caching service with $DIC->refinery() since the Refinery needs ilLanguage, but ilLanguage
        // needs the caching service and so on...
        $cached_results = $this->cache->get('data', new Transformation(function ($data) {
            return $data;
        }));
        if (is_array($cached_results)) {
            $this->loadFromRawData($cached_results);
        } else {
            $this->readFromDB();
        }
    }

    protected function readFromDB(): void
    {
        global $DIC;
        $db = $DIC->database();

        $data = [];

        $sql =
            "SELECT id, class_name, component, location, checkbox, inherit, translate, devmode, allow_link," . PHP_EOL
            . "allow_copy, rbac, `system`, sideblock, default_pos, grp, default_pres_pos, `export`, repository," . PHP_EOL
            . "workspace, administration, amet, orgunit_permissions, lti_provider, offline_handling" . PHP_EOL
            . "FROM il_object_def" . PHP_EOL;
        $set = $db->query($sql);
        while ($rec = $db->fetchAssoc($set)) {
            $data['il_object_def'][$rec['id']] = $rec;
        }

        $sql =
            "SELECT parent, subobj, mmax" . PHP_EOL
            . "FROM il_object_subobj" . PHP_EOL;
        $set = $db->query($sql);
        while ($rec = $db->fetchAssoc($set)) {
            $parent = $rec['parent'];
            $data['subobj_for_parent'][$parent][] = $rec;
        }

        $sql =
            "SELECT DISTINCT(id) AS sid, parent, id, class_name, component, location, checkbox, inherit," . PHP_EOL
            . "translate, devmode, allow_link, allow_copy, rbac, `system`, sideblock, default_pos, grp," . PHP_EOL
            . "default_pres_pos, `export`, repository, workspace, administration, amet, orgunit_permissions," . PHP_EOL
            . "lti_provider, offline_handling" . PHP_EOL
            . "FROM il_object_def, il_object_subobj" . PHP_EOL
            . "WHERE NOT (" . $db->quoteIdentifier('system') . " = 1)" . PHP_EOL
            . "AND NOT (sideblock = 1)" . PHP_EOL
            . "AND subobj = id" . PHP_EOL;
        $set = $db->query($sql);
        while ($rec = $db->fetchAssoc($set)) {
            $data['grouped_rep_obj_types'][$rec['parent']][] = $rec;
        }

        $sql =
            "SELECT id, name, default_pres_pos" . PHP_EOL
            . "FROM il_object_group" . PHP_EOL;
        $set = $db->query($sql);
        while ($rec = $db->fetchAssoc($set)) {
            $data['il_object_group'][$rec['id']] = $rec;
        }

        $sql =
            "SELECT obj_type, sub_type, amet" . PHP_EOL
            . "FROM il_object_sub_type" . PHP_EOL;
        $set = $db->query($sql);
        while ($rec = $db->fetchAssoc($set)) {
            $data['il_object_sub_type'][$rec['obj_type']][] = $rec;
        }
        $this->loadFromRawData($data);
    }

    public function getIlObjectDef(): array
    {
        return $this->il_object_def;
    }

    public function getIlObjectGroup(): array
    {
        return $this->il_object_group;
    }

    public function getIlObjectSubType(): array
    {
        return $this->il_object_sub_type;
    }

    public static function getInstance(): ilCachedObjectDefinition
    {
        if (!isset(self::$instance)) {
            return self::$instance = new self();
        }

        return self::$instance;
    }


    /**
     * @param mixed $parent
     *
     * @return mixed
     */
    public function lookupSubObjForParent($parent)
    {
        if (is_array($parent)) {
            $index = md5(serialize($parent));
            if (isset($this->cached_results['subop_par'][$index])) {
                return $this->cached_results['subop_par'][$index];
            }

            $return = [];
            foreach ($parent as $p) {
                if (isset($this->subobj_for_parent[$p]) && is_array($this->subobj_for_parent[$p])) {
                    foreach ($this->subobj_for_parent[$p] as $rec) {
                        $return[] = $rec;
                    }
                }
            }

            $this->cached_results['subop_par'][$index] = $return;
            $this->changed = true;

            return $return;
        }

        return $this->subobj_for_parent[$parent];
    }

    public function __destruct()
    {
        if ($this->changed) {
            $this->changed = false;
            $this->cache->set('data', $this->getRawData());
        }
    }

    /**
     * @param mixed $parent
     *
     * @return mixed
     */
    public function lookupGroupedRepObj($parent)
    {
        if (is_array($parent)) {
            $index = md5(serialize($parent));
            if (isset($this->cached_results['grpd_repo'][$index])) {
                return $this->cached_results['grpd_repo'][$index];
            }

            $return = [];
            $sids = [];
            foreach ($parent as $p) {
                $s = $this->grouped_rep_obj_types[$p];
                foreach ($s as $child) {
                    if (!in_array($child['sid'], $sids)) {
                        $sids[] = $child['sid'];
                        $return[] = $child;
                    }
                }
            }
            $this->changed = true;
            $this->cached_results['grpd_repo'][$index] = $return;

            return $return;
        } else {
            return $this->grouped_rep_obj_types[$parent] ?? null;
        }
    }
}
