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
 * Class ilObjCategory
 *
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilObjCategory extends ilContainer
{
    protected ilLogger $cat_log;

    public function __construct(int $a_id = 0, bool $a_call_by_reference = true)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->app_event_handler = $DIC["ilAppEventHandler"];
        $this->log = $DIC["ilLog"];
        $this->cat_log = ilLoggerFactory::getLogger("cat");
        $this->user = $DIC->user();
        $this->type = "cat";
        parent::__construct($a_id, $a_call_by_reference);
    }

    public function delete(): bool
    {
        $ilDB = $this->db;
        $ilAppEventHandler = $this->app_event_handler;

        // always call parent delete function first!!
        if (!parent::delete()) {
            return false;
        }

        // put here category specific stuff
        ilObjUserFolder::_updateUserFolderAssignment($this->ref_id, USER_FOLDER_ID);

        // taxonomies
        foreach (ilObjTaxonomy::getUsageOfObject($this->getId()) as $tax_id) {
            if ($tax_id) {
                $tax = new ilObjTaxonomy($tax_id);
                $tax->delete();
            }
        }

        $ilAppEventHandler->raise(
            'Modules/Category',
            'delete',
            [
                'object' => $this,
                'obj_id' => $this->getId()
            ]
        );

        return true;
    }

    public function cloneObject(int $target_id, int $copy_id = 0, bool $omit_tree = false): ?ilObject
    {
        $new_obj = parent::cloneObject($target_id, $copy_id, $omit_tree);

        return $new_obj;
    }

    public function cloneDependencies(int $target_id, int $copy_id): bool
    {
        parent::cloneDependencies($target_id, $copy_id);


        // clone taxonomies

        $all_tax = ilObjTaxonomy::getUsageOfObject($this->getId());
        if (count($all_tax)) {
            $cwo = ilCopyWizardOptions::_getInstance($copy_id);
            $mappings = $cwo->getMappings();

            foreach ($all_tax as $old_tax_id) {
                if ($old_tax_id) {
                    // clone it
                    $old_tax = new ilObjTaxonomy($old_tax_id);
                    $new_tax = $old_tax->cloneObject(0, 0, true);
                    $tax_map = $old_tax->getNodeMapping();

                    // assign new taxonomy to new category
                    ilObjTaxonomy::saveUsage($new_tax->getId(), ilObject::_lookupObjId($target_id));

                    // clone assignments (for all sub-items)
                    foreach ($mappings as $old_ref_id => $new_ref_id) {
                        if (!is_numeric($old_ref_id)) {     // e.g. 967_adv_rec
                            continue;
                        }
                        if ($old_ref_id != $new_ref_id) {
                            $old_obj_id = ilObject::_lookupObjId((int) $old_ref_id);
                            $new_obj_id = ilObject::_lookupObjId((int) $new_ref_id);
                            $obj_type = ilObject::_lookupType((int) $old_obj_id);

                            $this->cat_log->debug("Clone tax assignmets for old ref ID $old_ref_id of type $obj_type (old tax ID is $old_tax_id).");

                            $tax_ass = new ilTaxNodeAssignment($obj_type, $old_obj_id, "obj", $old_tax_id);
                            $assignmts = $tax_ass->getAssignmentsOfItem($old_obj_id);
                            if (count($assignmts)) {
                                $new_tax_ass = new ilTaxNodeAssignment($obj_type, $new_obj_id, "obj", $new_tax->getId());
                                foreach ($assignmts as $a) {
                                    if ($tax_map[$a["node_id"]]) {
                                        $new_tax_ass->addAssignment($tax_map[$a["node_id"]], $new_obj_id);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return true;
    }

    public function addAdditionalSubItemInformation(array &$object): void
    {
        ilObjectActivation::addAdditionalSubItemInformation($object);
    }
}
