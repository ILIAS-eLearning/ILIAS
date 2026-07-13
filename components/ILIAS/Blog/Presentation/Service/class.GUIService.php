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

namespace ILIAS\Blog\Presentation;

use ILIAS\Blog\InternalDataService;
use ILIAS\Blog\InternalDomainService;
use ILIAS\Blog\InternalGUIService;
use ILIAS\Blog\Permission\PermissionManager;

class GUIService
{
    public function __construct(
        protected InternalDataService $data,
        protected InternalDomainService $domain,
        protected InternalGUIService $gui
    ) {
    }

    public function util(): Util
    {
        return new Util();
    }

    public function presentationGUI(
        \ilObjBlogGUI $parent_gui,
        PermissionManager $perm,
        \ILIAS\Style\Content\Object\ObjectFacade $content_style_domain,
        string $current_month,
        ?int $node_id = null,
        int $id_type = \ilObjBlogGUI::REPOSITORY_NODE_ID
    ): PresentationGUI {
        return new PresentationGUI(
            $this->data,
            $this->domain,
            $this->gui,
            $parent_gui,
            $perm,
            $content_style_domain,
            $current_month,
            $node_id,
            $id_type
        );
    }
}
