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
 * @package     Modules/Test(QuestionPool)
 */
class ilTestRandomQuestionSetNonAvailablePoolsTableGUI extends ilTable2GUI
{
    public const IDENTIFIER = 'NonAvailPoolsTbl';

    public function __construct(ilCtrl $ctrl, ilLanguage $lng, $parentGUI, $parentCMD)
    {
        parent::__construct($parentGUI, $parentCMD);

        $this->ctrl = $ctrl;
        $this->lng = $lng;
    }

    private function setTableIdentifiers(): void
    {
        $this->setId(self::IDENTIFIER);
        $this->setPrefix(self::IDENTIFIER);
        $this->setFormName(self::IDENTIFIER);
    }

    public function build(): void
    {
        $this->setTableIdentifiers();

        $this->setTitle($this->lng->txt('tst_non_avail_pools_table'));

        $this->setRowTemplate('tpl.il_tst_non_avail_pools_row.html', 'Modules/Test');

        $this->enable('header');
        $this->disable('sort');

        $this->setExternalSegmentation(true);
        $this->setLimit(PHP_INT_MAX);

        $this->setFormAction($this->ctrl->getFormAction($this->parent_obj));

        $this->addColumns();
    }

    protected function addColumns(): void
    {
        $this->addColumn($this->lng->txt('title'), '', '30%');
        $this->addColumn($this->lng->txt('path'), '', '30%');
        $this->addColumn($this->lng->txt('status'), '', '40%');
        $this->addColumn($this->lng->txt('actions'), '', '');
    }

    public function init(ilTestRandomQuestionSetSourcePoolDefinitionList $sourcePoolDefinitionList): void
    {
        $rows = array();

        $pools = $sourcePoolDefinitionList->getNonAvailablePools();

        foreach ($pools as $nonAvailablePool) {
            /** @var ilTestRandomQuestionSetNonAvailablePool $nonAvailablePool */

            $set = array();

            $set['id'] = $nonAvailablePool->getId();
            $set['title'] = $nonAvailablePool->getTitle();
            $set['path'] = $nonAvailablePool->getPath();
            $set['status'] = $nonAvailablePool->getUnavailabilityStatus();

            $rows[] = $set;
        }

        $this->setData($rows);
    }

    protected function getDerivePoolLink($poolId): string
    {
        $this->ctrl->setParameter($this->parent_obj, 'derive_pool_id', $poolId);

        $link = $this->ctrl->getLinkTarget(
            $this->parent_obj,
            ilTestRandomQuestionSetConfigGUI::CMD_SELECT_DERIVATION_TARGET
        );

        return $link;
    }

    public function fillRow(array $a_set): void
    {
        if ($a_set['status'] == ilTestRandomQuestionSetNonAvailablePool::UNAVAILABILITY_STATUS_LOST) {
            $link = $this->getDerivePoolLink($a_set['id']);
            $this->tpl->setCurrentBlock('single_action');
            $this->tpl->setVariable('ACTION_HREF', $link);
            $this->tpl->setVariable('ACTION_TEXT', $this->lng->txt('tst_derive_new_pool'));
            $this->tpl->parseCurrentBlock();
        }

        $this->tpl->setVariable('TITLE', $a_set['title']);
        $this->tpl->setVariable('PATH', $a_set['path']);
        $this->tpl->setVariable('STATUS', $this->getStatusText($a_set['status']));
    }

    protected function getStatusText($status): string
    {
        return $this->lng->txt('tst_non_avail_pool_msg_status_' . $status);
    }
}
