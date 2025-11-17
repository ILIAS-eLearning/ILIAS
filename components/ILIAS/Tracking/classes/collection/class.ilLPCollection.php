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

use ILIAS\Tracking\DB\Factory as TrackingDBFactory;
use ILIAS\Tracking\DB\FactoryInterface as TrackingDBFactoryInterface;

abstract class ilLPCollection
{
    protected array $items;
    protected int $obj_id;
    protected int $mode;

    protected ilDBInterface $db;
    protected ilLogger $logger;
    protected TrackingDBFactoryInterface $tracking_db_factory;

    public function __construct(int $a_obj_id, int $a_mode)
    {
        global $DIC;
        $this->items = [];
        $this->db = $DIC->database();
        $this->logger = $DIC->logger()->trac();
        $this->tracking_db_factory = new TrackingDBFactory($this->db);
        $this->obj_id = $a_obj_id;
        $this->mode = $a_mode;
        $this->read($a_obj_id);
    }

    public static function getInstanceByMode(
        int $a_obj_id,
        int $a_mode
    ): ?ilLPCollection {
        $path = "components/ILIAS/Tracking/classes/collection/";

        switch ($a_mode) {
            case ilLPObjSettings::LP_MODE_COLLECTION:
            case ilLPObjSettings::LP_MODE_MANUAL_BY_TUTOR:
                return new ilLPCollectionOfRepositoryObjects(
                    $a_obj_id,
                    $a_mode
                );

            case ilLPObjSettings::LP_MODE_OBJECTIVES:
                return new ilLPCollectionOfObjectives($a_obj_id, $a_mode);

            case ilLPObjSettings::LP_MODE_SCORM:
                return new ilLPCollectionOfSCOs($a_obj_id, $a_mode);

            case ilLPObjSettings::LP_MODE_COLLECTION_MANUAL:
            case ilLPObjSettings::LP_MODE_COLLECTION_TLT:
                return new ilLPCollectionOfLMChapters($a_obj_id, $a_mode);

            case ilLPObjSettings::LP_MODE_COLLECTION_MOBS:
                return new ilLPCollectionOfMediaObjects($a_obj_id, $a_mode);
        }
        return null;
    }

    public static function getCollectionModes(): array
    {
        return array(
            ilLPObjSettings::LP_MODE_COLLECTION,
            ilLPObjSettings::LP_MODE_COLLECTION_TLT,
            ilLPObjSettings::LP_MODE_COLLECTION_MANUAL,
            ilLPObjSettings::LP_MODE_SCORM,
            ilLPObjSettings::LP_MODE_OBJECTIVES,
            ilLPObjSettings::LP_MODE_COLLECTION_MOBS
        );
    }

    public function hasSelectableItems(): bool
    {
        return true;
    }

    public function cloneCollection(int $a_target_id, int $a_copy_id): void
    {
        $target_obj_id = ilObject::_lookupObjId($a_target_id);
        $cwo = ilCopyWizardOptions::_getInstance($a_copy_id);
        $mappings = $cwo->getMappings();

        // #12067
        $new_collection = new static($target_obj_id, $this->mode);
        foreach ($this->items as $item) {
            if (!isset($mappings[$item]) or !$mappings[$item]) {
                continue;
            }

            $new_collection->addEntry($mappings[$item]);
        }
        $this->logger->debug('cloned learning progress collection.');
    }

    public function getItems(): array
    {
        return $this->items;
    }

    protected function read(int $a_obj_id): void
    {
        $items = [];
        $lp_collection = $this->tracking_db_factory->lpCollection()->repository()->readLPCollection($a_obj_id);
        foreach ($lp_collection as $lp_collection_entry) {
            if ($this->validateEntry($lp_collection_entry->getItemId())) {
                $items[] = $lp_collection_entry->getItemId();
            } else {
                $this->deleteEntry($lp_collection_entry->getItemId());
            }
        }
        $this->items = $items;
    }

    public function delete(): void
    {
        $this->tracking_db_factory->lpCollection()->repository()->deleteLPCollection($this->obj_id);
        $this->tracking_db_factory->lpCollection()->repository()->deleteLPCollectionManual($this->obj_id);
        $this->items = [];
    }

    protected function validateEntry(int $a_item_id): bool
    {
        return true;
    }

    public function isAssignedEntry(int $a_item_id): bool
    {
        return in_array($a_item_id, $this->items);
    }

    protected function addEntry(int $a_item_id): bool
    {
        $element = $this->tracking_db_factory->lpCollection()->element()->lpCollectionElement()
            ->withItemId($a_item_id)
            ->withLPMode($this->mode)
            ->withGroupingId(0)
            ->withIsActive(true)
            ->withNumObligatory(0);
        $collection = $this->tracking_db_factory->lpCollection()->element()->lpCollection($element)
            ->withObjectId($this->obj_id);
        $this->tracking_db_factory->lpCollection()->repository()->writeLPCollection($collection);
        $this->items[] = $a_item_id;
        return true;
    }

    protected function deleteEntry(int $a_item_id): bool
    {
        $this->tracking_db_factory->lpCollection()->repository()->deleteLPCollectionEntry($this->obj_id, $a_item_id);
        return true;
    }

    /**
     * @param int[] $a_item_ids
     */
    public function deactivateEntries(array $a_item_ids): void
    {
        foreach ($a_item_ids as $item_id) {
            $this->deleteEntry($item_id);
        }
    }

    /**
     * @param int[] $a_item_ids
     */
    public function activateEntries(array $a_item_ids): void
    {
        foreach ($a_item_ids as $item_id) {
            $this->addEntry($item_id);
        }
    }
}
