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

namespace ILIAS\LearningSequence\Content\Condition;

use ILIAS\LearningSequence\Content\Condition\InputCondition\InputConditionInterface;
use ILIAS\LearningSequence\Content\Condition\OutputCondition\OutputConditionInterface;
use ReflectionClass;

class ilObjLearningSequenceConditionDiscover
{
    private const string BASE_PATH = __DIR__;

    /**
     * Discovering the condition classes means scanning the file system and
     * reflecting over every candidate class. The set of condition classes
     * cannot change while the request is running, so the result is cached
     * per base class/interface for the whole request.
     *
     * @var array<string, string[]>
     */
    private static array $discovered = [];

    /**
     * @var array<int, int[]> cache for the condition-ids per item ref_id
     */
    private static array $condition_ids_by_item = [];

    /**
     * @return string[]
     */
    public function getAllInputConditions(): array
    {
        return $this->discover(InputConditionInterface::class);
    }

    /**
     * @return string[]
     */
    public function getAllOutputConditions(): array
    {
        return $this->discover(OutputConditionInterface::class);
    }

    /**
     * @return string[]
     */
    public function getAllConditions(): array
    {
        return array_unique(array_merge(
            $this->getAllInputConditions(),
            $this->getAllOutputConditions()
        ));
    }

    public function getConditionByName(string $name_to_find): ?string
    {
        return array_find(
            $this->getAllConditions(),
            static fn(string $class): bool => AbstractCondition::getIdentifierForClass($class) === $name_to_find
        );
    }

    /**
     * @param int $item_ref_id
     * @return array
     */
    public function getAllConditionIdsForItem(int $item_ref_id): array
    {
        if (isset(self::$condition_ids_by_item[$item_ref_id])) {
            return self::$condition_ids_by_item[$item_ref_id];
        }

        global $DIC;
        $db = $DIC->database();

        $query = $db->queryF(
            'SELECT condition_id FROM lso_conditions WHERE obj_ref_id = %s',
            ['integer'],
            [$item_ref_id],
        );

        $conditions = [];
        while ($record = $db->fetchAssoc($query)) {
            $conditions[] = (int) $record['condition_id'];
        }

        self::$condition_ids_by_item[$item_ref_id] = $conditions;
        return $conditions;
    }

    /**
     * Loads the condition-ids for many items with a single query instead of
     * one query per item. Subsequent calls to getAllConditionIdsForItem() for
     * the given items are served from the cache.
     *
     * @param int[] $item_ref_ids
     * @return array<int, int[]> condition-ids per item ref_id
     */
    public function preloadConditionIdsForItems(array $item_ref_ids): array
    {
        $item_ref_ids = array_values(array_unique(array_map('intval', $item_ref_ids)));
        $missing = array_values(array_filter(
            $item_ref_ids,
            static fn(int $ref_id): bool => !isset(self::$condition_ids_by_item[$ref_id])
        ));

        if ($missing !== []) {
            global $DIC;
            $db = $DIC->database();

            foreach ($missing as $ref_id) {
                self::$condition_ids_by_item[$ref_id] = [];
            }

            $query = $db->query(
                'SELECT condition_id, obj_ref_id FROM lso_conditions WHERE '
                . $db->in('obj_ref_id', $missing, false, 'integer')
            );
            while ($record = $db->fetchAssoc($query)) {
                self::$condition_ids_by_item[(int) $record['obj_ref_id']][] = (int) $record['condition_id'];
            }
        }

        $result = [];
        foreach ($item_ref_ids as $ref_id) {
            $result[$ref_id] = self::$condition_ids_by_item[$ref_id];
        }
        return $result;
    }

    /**
     * Drops the request-caches. The caches only live within one request, so
     * this is merely a safety net for code that creates, changes or deletes
     * conditions and reads them again within the very same request.
     */
    public static function flushCaches(): void
    {
        self::$condition_ids_by_item = [];
    }

    /**
     * @return string[]
     */
    private function discover(string $baseClassOrInterface): array
    {
        if (isset(self::$discovered[$baseClassOrInterface])) {
            return self::$discovered[$baseClassOrInterface];
        }

        $classes = [];
        $path = self::BASE_PATH;

        if (!is_dir($path)) {
            return $classes;
        }

        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));
        foreach ($iterator as $fileInfo) {
            /** @var \SplFileInfo $fileInfo */
            if ($fileInfo->isDir() || $fileInfo->getExtension() !== 'php') {
                continue;
            }

            $filePath = $fileInfo->getRealPath();
            $content = file_get_contents($filePath);

            if (preg_match('/namespace\s+(.+?);/', $content, $m)) {
                $namespace = trim($m[1]);
                $className = $fileInfo->getBasename('.php');
                $fullClass = $namespace . "\\" . $className;

                try {
                    if (!class_exists($fullClass, true)) {
                        continue;
                    }
                    $reflection = new ReflectionClass($fullClass);
                    if ($reflection->isInstantiable() &&
                        $reflection->isSubclassOf($baseClassOrInterface)
                    ) {
                        $classes[] = $fullClass;
                    }
                } catch (\Throwable $t) {
                    continue;
                }
            }
        }

        self::$discovered[$baseClassOrInterface] = $classes;
        return $classes;
    }
}
