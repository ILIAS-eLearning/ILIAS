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

namespace ILIAS\Blog\Editing;

use ILIAS\Blog\InternalDataService;
use ILIAS\Blog\InternalDomainService;
use ILIAS\Blog\InternalGUIService;
use ILIAS\File\Capabilities\Permissions;
use ILIAS\Blog\Permission\PermissionManager;

class GUIService
{
    public function __construct(
        protected InternalDataService $data,
        protected InternalDomainService $domain,
        protected InternalGUIService $gui
    ) {
    }

    public function editingGUI(
        int $node_id,
        int $id_type,
        PermissionManager $perm,
        ?string $month,
        \ILIAS\Style\Content\Object\ObjectFacade $content_style_domain,
        \ilObjBlogGUI $parent_gui
    ): EditingGUI {
        return new EditingGUI(
            $this->data,
            $this->domain,
            $this->gui,
            $node_id,
            $id_type,
            $perm,
            $month,
            $content_style_domain,
            $parent_gui
        );
    }
}
