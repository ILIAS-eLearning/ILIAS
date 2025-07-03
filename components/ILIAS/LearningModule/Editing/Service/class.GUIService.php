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

namespace ILIAS\LearningModule\Editing;

use ILIAS\LearningModule\InternalGUIService;
use ILIAS\LearningModule\InternalDomainService;

class GUIService
{
    protected array $page_layouts;

    public function __construct(
        protected InternalDomainService $domain,
        protected InternalGUIService $gui
    ) {
        $this->page_layouts = \ilPageLayout::activeLayouts(
            \ilPageLayout::MODULE_LM
        );
    }

    public function request(
        ?array $passed_query_params = null,
        ?array $passed_post_data = null
    ): EditingGUIRequest {
        return new EditingGUIRequest(
            $this->gui->http(),
            $this->domain->refinery(),
            $passed_query_params,
            $passed_post_data
        );
    }

    public function editSubObjectsGUI(
        string $sub_type,
        \ilObjLearningModule $lm,
        string $table_title
    ): EditSubObjectsGUI {
        return new EditSubObjectsGUI(
            $this->domain,
            $this->gui,
            $sub_type,
            $lm,
            $table_title
        );
    }

    public function subObjectTableBuilder(
        string $title,
        int $lm_id,
        string $type,
        object $parent_gui,
        string $parent_cmd
    ): SubObjectTableBuilder {
        return new SubObjectTableBuilder(
            $this->domain,
            $this->gui,
            $title,
            $lm_id,
            $type,
            $parent_gui,
            $parent_cmd
        );
    }
}
