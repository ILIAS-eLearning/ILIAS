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

namespace ILIAS\Blog\Export;

use ILIAS\Blog\InternalDataService;
use ILIAS\Blog\InternalDomainService;
use ILIAS\Blog\InternalGUIService;

class ExportGUI
{
    protected int $blog_id;
    protected ExportManager $exp_manager;

    public function __construct(
        protected InternalDataService $data,
        protected InternalDomainService $domain,
        protected InternalGUIService $gui,
        protected int $node_id,
        protected int $owner_id,
        protected bool $is_repository
    ) {
        if ($is_repository) {
            $this->blog_id = \ilObject::_lookupObjId($this->node_id);
        } else {
            $this->blog_id = $domain->getObjectIdForWspId($this->node_id);
        }
        $this->exp_manager = $this->domain->export()->manager();
    }

    public function executeCommand(): void
    {
        $ctrl = $this->gui->ctrl();
        $next_class = $ctrl->getNextClass($this);
        $cmd = $ctrl->getCmd();

        switch ($next_class) {
            default:
                if (in_array($cmd, ["createExportFileWithComments"])) {
                    $this->$cmd();
                }
        }
    }

    protected function createExportFileWithComments(): void
    {
        $ctrl = $this->gui->ctrl();
        $this->exp_manager->buildHtml(
            $this->node_id,
            $this->owner_id,
            "",
            $this->is_repository,
            true
        );
        $ctrl->redirectByClass(
            [
                \ilObjBlogGUI::class,
                \ilExportGUI::class
            ],
            \ilExportGUI::CMD_LIST_EXPORT_FILES
        );
    }


}
