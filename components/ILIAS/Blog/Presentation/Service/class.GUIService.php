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

use ILIAS\Blog\BlogGUIContext;
use ILIAS\Blog\InternalDataService;
use ILIAS\Blog\InternalDomainService;
use ILIAS\Blog\InternalGUIService;
use ilObject;

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
        BlogGUIContext $context,
        \ILIAS\Style\Content\Object\ObjectFacade $content_style_domain,
        ?\Closure $add_header_callback = null
    ): PresentationGUI {
        return new PresentationGUI(
            $this->data,
            $this->domain,
            $this->gui,
            $context,
            $content_style_domain,
            $add_header_callback
        );
    }

    public function getPrintView(
        int $node_id,
        bool $is_repository_node,
        ?array $selected_pages = null
    ): \ILIAS\Export\PrintProcessGUI {
        global $DIC;

        $id_type = $is_repository_node
            ? \ilObjBlogGUI::REPOSITORY_NODE_ID
            : \ilObjBlogGUI::WORKSPACE_NODE_ID;
        $cs = $DIC->contentStyle();
        if ($is_repository_node) {
            $obj_id = \ilObject::_lookupObjectId($node_id);
            $content_style = $cs->domain()->styleForRefId($node_id);
        } else {
            $obj_id = $this->domain->getObjectIdForWspId($node_id);
            $content_style = $cs->domain()->styleForObjId($obj_id);
        }
        $style_sheet_id = $content_style->getEffectiveStyleId();

        $provider = new \ILIAS\Blog\BlogPrintViewProviderGUI(
            $this->domain->lng(),
            $this->gui->ctrl(),
            $obj_id,
            $node_id,
            $this->domain->getBlogAccessHandler($id_type),
            $style_sheet_id,
            $selected_pages
        );

        return new \ILIAS\Export\PrintProcessGUI(
            $provider,
            $this->gui->http(),
            $this->gui->ui(),
            $this->domain->lng()
        );
    }

}
