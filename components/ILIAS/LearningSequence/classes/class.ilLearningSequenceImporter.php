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

use ILIAS\COPage\Page\PageManagerInterface;
use ILIAS\LearningSequence\Content\Adaptive\LSOAdaptiveBoundaries;
use ILIAS\LearningSequence\Content\Condition\ConditionFactory;
use ILIAS\LearningSequence\Content\Condition\ConditionHandler;
use ILIAS\LearningSequence\Content\Condition\ilObjLearningSequenceConditionDiscover;
use ILIAS\LearningSequence\Content\Condition\SubtypeAwareInterface;

class ilLearningSequenceImporter extends ilXmlImporter
{
    protected PageManagerInterface $page_manager;
    protected ilObjUser $user;
    protected ilRbacAdmin $rbac_admin;
    protected ilLogger $log;
    protected ilObject $obj;
    protected array $data;

    public function __construct()
    {
        global $DIC;
        parent::__construct();
        $this->page_manager = $DIC->copage()->internal()->domain()->page();
    }

    public function init(): void
    {
        global $DIC;
        $this->user = $DIC["ilUser"];
        $this->rbac_admin = $DIC["rbacadmin"];
        $this->log = $DIC["ilLoggerFactory"]->getComponentLogger('lso');
    }

    public function importXmlRepresentation(string $a_entity, string $a_id, string $a_xml, ilImportMapping $a_mapping): void
    {
        if ($new_id = $a_mapping->getMapping("components/ILIAS/Container", "objs", $a_id)) {
            $this->obj = ilObjectFactory::getInstanceByObjId((int) $new_id, false);
        } else {
            $this->obj = new ilObjLearningSequence();
            $this->obj->create();
        }

        $parser = new ilLearningSequenceXMLParser($this->obj, $a_xml);
        $this->data = $parser->start();

        $a_mapping->addMapping("components/ILIAS/LearningSequence", "lso", $a_id, (string) $this->obj->getId());
        $a_mapping->addMapping(
            'Services/COPage',
            'pg',
            LSOPageType::INTRO->value . ':' . $a_id,
            LSOPageType::INTRO->value . ':' . (string) $this->obj->getId()
        );
        $a_mapping->addMapping(
            'Services/COPage',
            'pg',
            LSOPageType::EXTRO->value . ':' . $a_id,
            LSOPageType::EXTRO->value . ':' . (string) $this->obj->getId()
        );

        $a_mapping->addMapping(
            'components/ILIAS/MetaData',
            'md',
            $a_id . ':0:lso',
            (string) $this->obj->getId() . ':0:lso'
        );

        $a_mapping->addMapping(
            "components/ILIAS/Taxonomy",
            "tax_item",
            "lso:obj:" . $a_id,
            (string) $this->obj->getId()
        );
        $a_mapping->addMapping(
            "components/ILIAS/Taxonomy",
            "tax_item_obj_id",
            "lso:obj:" . $a_id,
            (string) $this->obj->getId()
        );
        $a_mapping->addMapping(
            "components/ILIAS/Tracking",
            "obj",
            (string) $a_id,
            (string) $this->obj->getId()
        );
    }

    public function finalProcessing(ilImportMapping $a_mapping): void
    {
        $this->buildSettings($this->data["settings"], $a_mapping);
        $this->obj->update();

        // pages
        $page_map = $a_mapping->getMappingsOfEntity('Services/COPage', 'pg');
        foreach ($page_map as $old_pg_id => $new_pg_id) {
            $parts = explode(':', $old_pg_id);
            $pg_type = $parts[0];
            $old_obj_id = $parts[1];
            $parts = explode(':', $new_pg_id);
            $new_pg_id = array_pop($parts);
            $new_obj_id = $this->obj->getId();
            $this->page_manager->writeParentId($pg_type, (int) $new_pg_id, (int) $new_obj_id);
        }
    }

    public function afterContainerImportProcessing(ilImportMapping $mapping): void
    {
        $this->updateRefId($mapping);
        $this->buildLSItems($this->data["item_data"], $mapping);
        $this->buildItemConditions($this->data["item_data"], $mapping);
        $this->buildLPSettings($this->data["lp_settings"], $mapping);

        $roles = $this->obj->getLSRoles();
        $roles->addLSMember(
            $this->user->getId(),
            $roles->getDefaultAdminRole()
        );
    }

    protected function updateRefId(ilImportMapping $mapping): void
    {
        $old_ref_id = $this->data["object"]["ref_id"];
        $new_ref_id = $mapping->getMapping("components/ILIAS/Container", "refs", $old_ref_id);

        $this->obj->setRefId((int) $new_ref_id);
    }

    protected function buildLSItems(array $ls_data, ilImportMapping $mapping): void
    {
        $mapped = [];
        foreach ($ls_data as $data) {
            $old_ref_id = $data["ref_id"];
            $new_ref_id = $mapping->getMapping("components/ILIAS/Container", "refs", $old_ref_id);
            $mapped[$new_ref_id] = $data;
        }

        $ls_items = $this->obj->getLSItems();
        $updated = [];
        foreach ($ls_items as $item) {
            $item_ref_id = $item->getRefId();
            if (array_key_exists($item_ref_id, $mapped)) {
                $item_data = $mapped[$item_ref_id];
                $post_condition = new ilLSPostCondition(
                    $item_ref_id,
                    $item_data["condition_type"],
                    $item_data["condition_value"]
                );
                $item = $item->withPostCondition($post_condition);
                if (isset($item_data["position"])) {
                    $item = $item->withOrderNumber((int) $item_data["position"]);
                }
                $updated[] = $item;
            }
        }

        if ($updated) {
            $this->obj->storeLSItems($updated);
        }
    }

    /**
     * Creates lso_conditions + condition payload (lso_c_*) based on parsed XML.
     *
     * Expected $ls_data format (per item):
     *  [
     *    'ref_id' => <old_ref_id>,
     *    'conditions' => [
     *      [
     *        'type_id' => <int|string>, // maps to lso_condition_types.type_id
     *        'subtype' => <string|null>,
     *        'data' => <payload array exported by AbstractCondition::export()>
     *      ],
     *      ...
     *    ]
     *  ]
     */
    protected function buildItemConditions(array $ls_data, ilImportMapping $mapping): void
    {
        global $DIC;

        $db = $DIC->database();

        $discover = new ilObjLearningSequenceConditionDiscover();
        $factory = new ConditionFactory($discover, $db);
        $handler = new ConditionHandler($db);

        $new_lso_ref_id = $this->obj->getRefId();

        foreach ($ls_data as $item_data) {
            $old_item_ref_id = (string) ($item_data['ref_id'] ?? '');
            if ($old_item_ref_id === '') {
                continue;
            }

            $new_item_ref_id = $mapping->getMapping('components/ILIAS/Container', 'refs', $old_item_ref_id);
            if ($new_item_ref_id === null) {
                continue;
            }
            $new_item_ref_id = (int) $new_item_ref_id;

            $conditions = is_array($item_data['conditions'] ?? null) ? $item_data['conditions'] : [];
            if ($conditions === []) {
                continue;
            }

            // Avoid duplicates when re-importing/updating: clear existing conditions for this item.
            $handler->deleteConditionsByRefId($new_lso_ref_id, $new_item_ref_id);

            foreach ($conditions as $cond) {
                $created = false;

                if (!is_array($cond)) {
                    continue;
                }

                $type_id_raw = $cond['type'] ?? null;
                $subtype = isset($cond['subtype']) && is_string($cond['subtype']) && $cond['subtype'] !== ''
                    ? $cond['subtype']
                    : null;

                $payload = is_array($cond['data'] ?? null) ? $cond['data'] : [];

                try {
                    $condition = $factory->getConditionInstanceByName($type_id_raw);
                    $condition->setLsoRefId($new_lso_ref_id);
                    $condition->setObjRefId($new_item_ref_id);
                    if ($subtype !== null && $condition instanceof SubtypeAwareInterface) {
                        $condition->setSubtype($subtype);
                    }

                    $condition->create(true);
                    $created = true;

                    $new_condition_id = (int) $condition->getConditionId();
                    if ($new_condition_id > 0) {
                        $ref_mappings = array_map(
                            'intval',
                            $mapping->getAllMappings()['components/ILIAS/Container']['refs'] ?? []
                        );
                        $condition->setImportMapping($ref_mappings);
                        $condition->import($payload, $new_condition_id);
                    }
                } catch (\Throwable $e) {
                    if ($created && isset($condition)) {
                        $condition->delete();
                    }

                    $this->log->warning(__METHOD__ . ': condition import failed: ' . $e->getMessage());
                    continue;
                }
            }
        }
    }

    protected function buildSettings(array $ls_settings, ilImportMapping $mapping): void
    {
        global $DIC;
        $settings = $this->obj->getLSSettings();
        $settings = $settings
            ->withMembersGallery($ls_settings["members_gallery"] === 'true' ? true : false)
        ;
        $settings = $settings->withMode((int) $ls_settings["mode"]);
        $this->obj->updateSettings($settings);

        // boundaries
        $start_ref_id = (string) ($ls_settings["start_ref_id"] ?? '');
        $end_ref_id = (string) ($ls_settings["end_ref_id"] ?? '');
        $new_start_ref_id = $mapping->getMapping("components/ILIAS/Container", "refs", $start_ref_id);
        $new_end_ref_id = $mapping->getMapping("components/ILIAS/Container", "refs", $end_ref_id);

        $new_start_ref_id = (!empty($new_start_ref_id)) ? (int) $new_start_ref_id : 0;
        $new_end_ref_id = (!empty($new_end_ref_id)) ? (int) $new_end_ref_id : 0;
        if ($new_start_ref_id > 0 || $new_end_ref_id > 0) {
            $boundaries = new LSOAdaptiveBoundaries($DIC->database());
            $boundaries->setStartRefId($this->obj->getId(), $new_start_ref_id);
            $boundaries->setEndRefId($this->obj->getId(), $new_end_ref_id);
        }
    }

    protected function buildLPSettings(array $lp_settings, ilImportMapping $mapping): void
    {
        $collection = ilLPCollection::getInstanceByMode($this->obj->getId(), (int) $lp_settings["lp_mode"]);

        $new_ref_ids = array_map(function ($old_ref_id) use ($mapping) {
            return $mapping->getMapping("components/ILIAS/Container", "refs", $old_ref_id);
        }, $lp_settings["lp_item_ref_ids"]);

        if (!is_null($collection)) {
            $collection->activateEntries($new_ref_ids);
        }

        $settings = new ilLPObjSettings($this->obj->getId());
        $settings->setMode((int) $lp_settings["lp_mode"]);
        $settings->insert();
    }

    protected function decodeImageData(string $data): string
    {
        return base64_decode($data);
    }

    protected function getNewImagePath(string $type, string $path): string
    {
        $fs = $this->obj->getDI()['db.filesystem'];
        return $fs->getStoragePathFor(
            $type,
            $this->obj->getId(),
            $fs->getSuffix($path)
        );
    }

    protected function writeToFileSystem($data, string $path): void
    {
        file_put_contents($path, $data);
    }
}
