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

namespace ILIAS\COPage\PC;

use ILIAS\COPage\InternalGUIService;
use ILIAS\COPage\InternalDomainService;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class GUIService
{
    protected InternalGUIService $gui_service;
    protected InternalDomainService $domain_service;

    public function __construct(
        InternalDomainService $domain_service,
        InternalGUIService $gui_service
    ) {
        $this->gui_service = $gui_service;
        $this->domain_service = $domain_service;
    }

    public function editRequest(): EditGUIRequest
    {
        return new EditGUIRequest(
            $this->gui_service->http(),
            $this->domain_service->refinery()
        );
    }

    public function interactiveImage(): InteractiveImage\GUIService
    {
        return new InteractiveImage\GUIService(
            $this->domain_service,
            $this->gui_service
        );
    }

    public function paragraph(): Paragraph\GUIService
    {
        return new Paragraph\GUIService(
            $this->domain_service,
            $this->gui_service
        );
    }

    public function fileListTableBuilder(
        \ilPCFileList $file_list,
        object $parent_gui,
        string $parent_cmd
    ): FileList\FileListTableBuilder {
        return new FileList\FileListTableBuilder(
            $this->domain_service,
            $this->gui_service,
            $file_list,
            $parent_gui,
            $parent_cmd
        );
    }

    public function copySelfAssQuestionTableBuilder(
        int $pool_ref_id,
        int $pool_obj_id,
        object $parent_gui,
        string $parent_cmd
    ): Question\CopySelfAssQuestionTableBuilder {
        return new Question\CopySelfAssQuestionTableBuilder(
            $this->domain_service,
            $this->gui_service,
            $pool_ref_id,
            $pool_obj_id,
            $parent_gui,
            $parent_cmd
        );
    }

    public function gridCellTableBuilder(
        \ilPCGrid $grid,
        object $parent_gui,
        string $parent_cmd
    ): Grid\GridCellTableBuilder {
        return new Grid\GridCellTableBuilder(
            $this->domain_service,
            $this->gui_service,
            $grid,
            $parent_gui,
            $parent_cmd
        );
    }

}
