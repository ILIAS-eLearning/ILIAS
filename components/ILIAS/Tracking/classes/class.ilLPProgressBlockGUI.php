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

use ILIAS\Tracking\View\Factory as ViewFactory;
use ILIAS\Tracking\View\DataRetrieval\FactoryInterface as DataRetrievalFactoryInterface;
use ILIAS\Tracking\View\Renderer\RendererInterface as RendererInterface;

/**
 * @ilCtrl_IsCalledBy ilLPProgressBlockGUI: ilColumnGUI
 */
class ilLPProgressBlockGUI extends ilBlockGUI
{
    protected DataRetrievalFactoryInterface $data_retrieval;
    protected RendererInterface $tracking_renderer;

    public function __construct()
    {
        parent::__construct();

        $view_factory = new ViewFactory();
        $this->data_retrieval = $view_factory->dataRetrieval();
        $this->tracking_renderer = $view_factory->renderer()->service();

        $this->lng->loadLanguageModule('trac');

        $this->setBlockId('lpprogress_' . $this->ctrl->getContextObjId());
        $this->setTitle($this->lng->txt('trac_progress_block_title'));
        $this->setPresentation(self::PRES_SEC_LEG);
    }

    public function getBlockType(): string
    {
        return 'lpprogress';
    }

    protected function isRepositoryObject(): bool
    {
        return false;
    }

    protected function getLegacyContent(): string
    {
        $filter = $this->data_retrieval
            ->filter()
            ->withObjectIds(ilObject::_lookupObjectId($this->requested_ref_id))
            ->withUserIds($this->user->getId());
        $lp_info = $this->data_retrieval
            ->service()
            ->retrieveViewInfo($filter)
            ->lpInfoIterator()
            ->current();

        $status = $lp_info->getLPStatus();

        $progress = $this->tracking_renderer->fixedSizeProgressMeter($lp_info);
        $mode_and_status = $this->ui->factory()->item()->standard(
            ilLearningProgressBaseGUI::_getStatusText($status, $this->lng)
        )->withDescription(
            $this->lng->txt('trac_mode_collection')
        );

        return $this->ui->renderer()->render([
            $progress,
            $mode_and_status
        ]);
    }
}
