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

namespace ILIAS\Blog\Posting\Service;

use ILIAS\Blog\InternalDataService;
use ILIAS\Blog\InternalDomainService;
use ILIAS\Blog\InternalGUIService;
use ILIAS\Blog\Permission\PermissionManager;

/**
 * GUI service for blog postings
 */
class GUIService
{
    public function __construct(
        protected InternalDataService $data,
        protected InternalDomainService $domain,
        protected InternalGUIService $gui
    ) {
    }

    public function postingGUI(
        int $a_node_id,
        ?object $a_access_handler = null,
        int $a_id = 0,
        int $a_old_nr = 0,
        bool $a_enable_public_notes = true,
        bool $a_may_contribute = true,
        int $a_style_sheet_id = 0
    ): \ilBlogPostingGUI {
        return new \ilBlogPostingGUI(
            $a_node_id,
            $a_access_handler,
            $a_id,
            $a_old_nr,
            $a_enable_public_notes,
            $a_may_contribute,
            $a_style_sheet_id
        );
    }

    public function postingList(
        \ilObjBlogGUI $parent_gui,
        PermissionManager $perm,
        ?string $current_month = null,
        ?int $node_id = null,
        int $id_type = \ilObjBlogGUI::REPOSITORY_NODE_ID
    ): \ILIAS\Blog\Posting\PostingListGUI {
        return new \ILIAS\Blog\Posting\PostingListGUI(
            $this->data,
            $this->domain,
            $this->gui,
            $parent_gui,
            $perm,
            $current_month,
            $node_id,
            $id_type
        );
    }

    /**
     * Get first text paragraph of page
     */
    public function getSnippet(
        int $a_id,
        bool $a_truncate = false,
        int $a_truncate_length = 500,
        string $a_truncate_sign = "...",
        bool $a_include_picture = false,
        int $a_picture_width = 144,
        int $a_picture_height = 144,
        ?string $a_export_directory = null
    ): string {
        $bpgui = $this->postingGUI(0, null, $a_id);
        return $bpgui->getSnippet(
            $a_truncate,
            $a_truncate_length,
            $a_truncate_sign,
            $a_include_picture,
            $a_picture_width,
            $a_picture_height,
            $a_export_directory
        );
    }
}
