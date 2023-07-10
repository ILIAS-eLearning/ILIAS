<?php

declare(strict_types=0);

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * LP collection of SCOs
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesTracking
 */
class ilLPCollectionOfSCOs extends ilLPCollection
{
    protected static array $possible_items = array();

    public function getPossibleItems(): array
    {
        if (!isset(self::$possible_items[$this->obj_id])) {
            $items = array();

            switch (ilObjSAHSLearningModule::_lookupSubType($this->obj_id)) {
                case 'hacp':
                case 'aicc':
                    foreach (ilObjAICCLearningModule::_getTrackingItems(
                        $this->obj_id
                    ) as $item) {
                        $items[$item['obj_id']]['title'] = $item['title'];
                    }
                    break;

                case 'scorm':
                    foreach (ilObjSCORMLearningModule::_getTrackingItems(
                        $this->obj_id
                    ) as $item) {
                        $items[$item->getId()]['title'] = $item->getTitle();
                    }
                    break;

                case 'scorm2004':
                    foreach (ilObjSCORM2004LearningModule::_getTrackingItems(
                        $this->obj_id
                    ) as $item) {
                        $items[$item['id']]['title'] = $item['title'];
                    }
                    break;
            }

            self::$possible_items[$this->obj_id] = $items;
        }

        return self::$possible_items[$this->obj_id];
    }

    public function getTableGUIData(int $a_parent_ref_id): array
    {
        $data = array();

        foreach ($this->getPossibleItems() as $sco_id => $item) {
            $tmp = array();
            $tmp['id'] = $sco_id;
            $tmp['ref_id'] = 0;
            $tmp['type'] = 'sco';
            $tmp['title'] = $item['title'];
            $tmp["status"] = $this->isAssignedEntry($sco_id);

            $data[] = $tmp;
        }

        return $data;
    }


    //
    // HELPER
    //

    // see ilSCORMCertificateAdapter
    public function getScoresForUserAndCP_Node_Id(
        int $item_id,
        int $user_id
    ): array {
        switch (ilObjSAHSLearningModule::_lookupSubType($this->obj_id)) {
            case 'hacp':
            case 'aicc':
                return ilObjAICCLearningModule::_getScoresForUser(
                    $item_id,
                    $user_id
                );

            case 'scorm':
                return ilObjSCORMLearningModule::_getScoresForUser(
                    $item_id,
                    $user_id
                );

            case 'scorm2004':
                return ilObjSCORM2004LearningModule::_getScores2004ForUser(
                    $item_id,
                    $user_id
                );
        }

        return array("raw" => null, "max" => null, "scaled" => null);
    }

    /**
     * Scorm items are not copied, they are newly created by reading the manifest.
     * Therefore, they do not have a mapping. So we need to map them via the import_id/identifierref
     * @param int $a_target_id
     * @param int $a_copy_id
     */
    public function cloneCollection(int $a_target_id, int $a_copy_id): void
    {
        $target_obj_id = ilObject::_lookupObjId($a_target_id);
        $new_collection = new static($target_obj_id, $this->mode);
        $possible_items = $new_collection->getPossibleItems();
        foreach ($this->items as $item_id) {
            foreach ($possible_items as $pos_item_id => $pos_item) {
                if ($this->itemsAreEqual($item_id, $pos_item_id)) {
                    $new_collection->addEntry($pos_item_id);
                }
            }
        }
    }

    protected function itemsAreEqual(int $item_a_id, int $item_b_id): bool
    {
        global $DIC;
        switch (ilObjSAHSLearningModule::_lookupSubType($this->obj_id)) {
            case 'scorm':
                $res_a = $DIC->database()->query(
                    'SELECT import_id, identifierref FROM sc_item WHERE obj_id = ' . $DIC->database(
                    )->quote(
                        $item_a_id,
                        'integer'
                    )
                )->fetchAssoc();
                $res_b = $DIC->database()->query(
                    'SELECT import_id, identifierref FROM sc_item WHERE obj_id = ' . $DIC->database(
                    )->quote(
                        $item_b_id,
                        'integer'
                    )
                )->fetchAssoc();
                return (
                    $res_a
                    && $res_b
                    && ($res_a['import_id'] == $res_b['import_id'])
                    && ($res_a['identifierref'] == $res_b['identifierref'])
                );
            case 'scorm2004':
                $res_a = $DIC->database()->query(
                    'SELECT id, resourceid FROM cp_item WHERE cp_node_id = ' . $DIC->database(
                    )->quote(
                        $item_a_id,
                        'integer'
                    )
                )->fetchAssoc();
                $res_b = $DIC->database()->query(
                    'SELECT id, resourceid FROM cp_item WHERE cp_node_id = ' . $DIC->database(
                    )->quote(
                        $item_b_id,
                        'integer'
                    )
                )->fetchAssoc();
                return (
                    $res_a
                    && $res_b
                    && ($res_a['import_id'] == $res_b['import_id'])
                    && ($res_a['identifierref'] == $res_b['identifierref'])
                );
            default:
                return false;
        }
    }
}
