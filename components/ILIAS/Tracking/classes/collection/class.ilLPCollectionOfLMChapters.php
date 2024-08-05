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

declare(strict_types=0);

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
                    $child["tlt"] = ilLPStatus::_getTypicalLearningTime(
                        $child["type"],
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
