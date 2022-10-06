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

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package        Modules/Test(QuestionPool)
 */
class ilTestQuestionPoolSelectorExplorer extends ilRepositorySelectorExplorerGUI
{
    protected $availableQuestionPools = array();

    public function __construct($targetGUI, $roundtripCMD, $selectCMD)
    {
        parent::__construct($targetGUI, $roundtripCMD, $targetGUI, $selectCMD);

        $this->setTypeWhiteList(array('grp', 'cat', 'crs', 'fold', 'qpl'));
        $this->setClickableTypes(array('qpl'));
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

    public function isAvailableQuestionPool($qplRefId): bool
    {
        /* @var ilObjectDataCache $objCache */
        $objCache = isset($GLOBALS['DIC']) ? $GLOBALS['DIC']['ilObjDataCache'] : $GLOBALS['ilObjDataCache'];

        $qplObjId = $objCache->lookupObjId((int) $qplRefId);
        return in_array($qplObjId, $this->getAvailableQuestionPools());
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
