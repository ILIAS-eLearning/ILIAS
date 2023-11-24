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

class ilLearningSequenceImporter extends ilXmlImporter
{
    protected ilObjUser $user;
    protected ilRbacAdmin $rbac_admin;
    protected ilLogger $log;
    protected ilObject $obj;
    protected array $data;

    public function init(): void
    {
        global $DIC;
        $this->user = $DIC["ilUser"];
        $this->rbac_admin = $DIC["rbacadmin"];
        $this->log = $DIC["ilLoggerFactory"]->getRootLogger();
    }

    public function importXmlRepresentation(string $a_entity, string $a_id, string $a_xml, ilImportMapping $a_mapping): void
    {
        if ($new_id = $a_mapping->getMapping("Services/Container", "objs", $a_id)) {
            $this->obj = ilObjectFactory::getInstanceByObjId((int) $new_id, false);
        } else {
            $this->obj = new ilObjLearningSequence();
            $this->obj->create();
        }

        $parser = new ilLearningSequenceXMLParser($this->obj, $a_xml);
        $this->data = $parser->start();

        $a_mapping->addMapping("Modules/LearningSequence", "lso", $a_id, (string) $this->obj->getId());
        $a_mapping->addMapping('Services/COPage', 'pg', 'cont:' . $a_id * ilObjLearningSequence::CP_INTRO, 'cont:' . (string) $this->obj->getId() * ilObjLearningSequence::CP_INTRO);
        $a_mapping->addMapping('Services/COPage', 'pg', 'cont:' . $a_id * ilObjLearningSequence::CP_EXTRO, 'cont:' . (string) $this->obj->getId() * ilObjLearningSequence::CP_EXTRO);

    }

    public function finalProcessing(ilImportMapping $a_mapping): void
    {
        $this->buildSettings($this->data["settings"]);
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
            ilPageObject::_writeParentId($pg_type, (int) $new_pg_id, (int) $new_obj_id);
        }
    }

    public function afterContainerImportProcessing(ilImportMapping $mapping): void
    {
        $this->updateRefId($mapping);
        $this->buildLSItems($this->data["item_data"], $mapping);
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
        $new_ref_id = $mapping->getMapping("Services/Container", "refs", $old_ref_id);

        $this->obj->setRefId((int) $new_ref_id);
    }

    protected function buildLSItems(array $ls_data, ilImportMapping $mapping): void
    {
        $mapped = [];
        foreach ($ls_data as $data) {
            $old_ref_id = $data["ref_id"];
            $new_ref_id = $mapping->getMapping("Services/Container", "refs", $old_ref_id);
            $mapped[$new_ref_id] = $data;
        }

        $ls_items = $this->obj->getLSItems($this->obj->getRefId());
        $updated = [];
        foreach ($ls_items as $item) {
            $item_ref_id = $item->getRefId();
            if(array_key_exists($item_ref_id, $mapped)) {
                $item_data = $mapped[$item_ref_id];
                $post_condition = new ilLSPostCondition(
                    $item_ref_id,
                    $item_data["condition_type"],
                    $item_data["condition_value"]
                );
                $updated[] = $item->withPostCondition($post_condition);
            }
        }

        if($updated) {
            $this->obj->storeLSItems($updated);
        }
    }

    protected function buildSettings(array $ls_settings): void
    {
        $settings = $this->obj->getLSSettings();
        $settings = $settings
            ->withMembersGallery($ls_settings["members_gallery"] === 'true' ? true : false)
        ;
        $this->obj->updateSettings($settings);
    }

    protected function buildLPSettings(array $lp_settings, ilImportMapping $mapping): void
    {
        $collection = ilLPCollection::getInstanceByMode($this->obj->getId(), (int) $lp_settings["lp_mode"]);

        $new_ref_ids = array_map(function ($old_ref_id) use ($mapping) {
            return $mapping->getMapping("Services/Container", "refs", $old_ref_id);
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
