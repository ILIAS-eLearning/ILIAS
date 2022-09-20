<?php

declare(strict_types=0);

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * LP collection of learning module chapters
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesTracking
 */
class ilLPCollectionOfLMChapters extends ilLPCollection
{
    protected static array $possible_items = array();

    /**
     * @return array|mixed
     */
    public function getPossibleItems(int $a_ref_id)
    {
        if (!isset(self::$possible_items[$a_ref_id])) {
            $obj_id = ilObject::_lookupObjectId($a_ref_id);

            $items = array();

            // only top-level chapters
            $tree = new ilTree($obj_id);
            $tree->setTableNames('lm_tree', 'lm_data');
            $tree->setTreeTablePK("lm_id");
            foreach ($tree->getChilds($tree->readRootId()) as $child) {
                if ($child["type"] == "st") {
                    $child["tlt"] = ilMDEducational::_getTypicalLearningTimeSeconds(
                        $obj_id,
                        $child["obj_id"]
                    );
                    $items[$child["obj_id"]] = $child;
                }
            }

            self::$possible_items[$a_ref_id] = $items;
        }

        return self::$possible_items[$a_ref_id];
    }

    /**
     * @return array
     */
    public function getTableGUIData(int $a_parent_ref_id): array
    {
        $data = array();

        $parent_type = ilObject::_lookupType($a_parent_ref_id, true);

        foreach ($this->getPossibleItems($a_parent_ref_id) as $item) {
            $tmp = array();
            $tmp['id'] = $item['obj_id'];
            $tmp['ref_id'] = 0;
            $tmp['title'] = $item['title'];
            $tmp['type'] = $item['type'];
            $tmp['status'] = $this->isAssignedEntry($item['obj_id']);

            // #12158
            $tmp['url'] = ilLink::_getLink(
                $a_parent_ref_id,
                $parent_type,
                [],
                "_" . $tmp['id']
            );

            if ($this->mode == ilLPObjSettings::LP_MODE_COLLECTION_TLT) {
                $tmp['tlt'] = $item['tlt'];
            }

            $data[] = $tmp;
        }

        return $data;
    }
}
