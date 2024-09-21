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

namespace ILIAS\Blog\PermanentLink;

use ILIAS\Blog\InternalGUIService;
use ILIAS\Blog\InternalDomainService;
use ILIAS\UICore\PageContentProvider;
use ILIAS\StaticURL\Services as StaticUrl;
use ILIAS\Data\ReferenceId;

/**
 * search for illink::
 * target=blog_4 : blog
 * target=blog_4_4 : blog posting
 * target=blog_4_4_edit : edit blog posting (tasks)
 * target=blog_4_wsp : workspace blog
 * target=blog_4_4_wsp : posting in workspace blog
 * target=blog_4_4_edit_wsp : edit posting in workspace blog (tasks)
 */
class PermanentLinkManager
{
    protected InternalGUIService $gui;
    protected InternalDomainService $domain;

    public function __construct(
        protected StaticUrl $static_url,
        InternalGUIService $gui,
        protected $ref_id = 0,
        protected $wsp_id = 0
    ) {
        $this->gui = $gui;
        if ($ref_id === 0 && !$wsp_id) {
            $this->ref_id = $this->gui->standardRequest()->getRefId();
        }
        if ($wsp_id === 0 && !$ref_id) {
            $this->wsp_id = $this->gui->standardRequest()->getWspId();
        }
    }

    public function getAppend(
        int $posting = 0,
        bool $edit = false
    ): array {
        $append = [];
        if ($posting > 0) {
            $append[] = $posting;
        }
        if ($edit) {
            $append[] = "edit";
        }
        if ($this->ref_id === 0 && $this->wsp_id > 0) {
            $append[] = "wsp";
        }
        return $append;
    }

    public function getPermanentLink(
        int $posting = 0,
        bool $edit = false
    ): string {
        $id = $this->ref_id > 0
            ? $this->ref_id
            : $this->wsp_id;
        $uri = $this->static_url->builder()->build(
            'blog', // namespace
            $id > 0 ? new ReferenceId($id) : null,
            $this->getAppend($posting, $edit)
        );
        return (string) $uri;
    }

    public function setPermanentLink(
        int $posting = 0,
        bool $edit = false
    ): void {
        $uri = $this->getPermanentLink($posting, $edit);
        PageContentProvider::setPermaLink($uri);
    }
}
