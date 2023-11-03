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

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package components\ILIAS/Test(QuestionPool)
 */
class ilTestQuestionPoolSelectorExplorer extends ilRepositorySelectorExplorerGUI
{
    protected $availableQuestionPools = [];

    public function __construct(
        ilTestRandomQuestionSetConfigGUI $target_gui,
        string $roundtrip_cmd,
        string $select_cmd,
        private ilObjectDataCache $obj_cache
    ) {
        parent::__construct($target_gui, $roundtrip_cmd, $target_gui, $select_cmd);

        $this->setTypeWhiteList(['grp', 'cat', 'crs', 'fold', 'qpl']);
        $this->setClickableTypes(['qpl']);
        $this->setSelectMode('', false);
        $this->selection_par = 'quest_pool_ref';
    }

    public function getAvailableQuestionPools(): array
    {
        return $this->availableQuestionPools;
    }

    public function setAvailableQuestionPools($availableQuestionPools)
    {
        $this->availableQuestionPools = $availableQuestionPools;
    }

    public function isAvailableQuestionPool($qpl_ref_id): bool
    {
        $qpl_obj_id = $this->obj_cache->lookupObjId((int) $qpl_ref_id);
        return in_array($qpl_obj_id, $this->getAvailableQuestionPools());
    }

    public function isNodeClickable($a_node): bool
    {
        if ($a_node['type'] != 'qpl') {
            return parent::isNodeClickable($a_node);
        }

        return $this->isAvailableQuestionPool($a_node['child']);
    }

    public function isNodeVisible($a_node): bool
    {
        if ($a_node['type'] != 'qpl') {
            return parent::isNodeVisible($a_node);
        }

        return $this->isAvailableQuestionPool($a_node['child']);
    }
}
