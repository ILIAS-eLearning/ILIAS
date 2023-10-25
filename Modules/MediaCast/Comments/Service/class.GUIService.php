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

namespace ILIAS\MediaCast\Comments;

use ILIAS\MediaCast\InternalDomainService;
use ILIAS\MediaCast\InternalGUIService;

class GUIService
{
    protected \ilSetting $settings;
    protected \ilAccessHandler $access;
    protected \ILIAS\Notes\GUIService $notes_gui;
    protected InternalDomainService $domain;
    protected InternalGUIService $gui;

    public function __construct(
        InternalDomainService $domain,
        InternalGUIService $gui,
        \ILIAS\Notes\GUIService $notes_gui
    ) {
        $this->domain = $domain;
        $this->notes_gui = $notes_gui;
        $this->gui = $gui;
        $this->access = $domain->access();
        $this->settings = $domain->settings();
    }

    public function commentGUI(int $mcst_ref_id, int $news_id): \ilCommentGUI
    {
        $comments_gui = $this->notes_gui->getCommentsGUI(
            \ilObject::_lookupObjectId($mcst_ref_id),
            0,
            "mcst",
            $news_id
        );
        $comments_gui->setUseObjectTitleHeader(false);

        if ($this->access->checkAccess("write", "", $mcst_ref_id) &&
            $this->settings->get("comments_del_tutor", '1')) {
            $comments_gui->enablePublicNotesDeletion(true);
        }

        return $comments_gui;
    }
}
