<?php

declare(strict_types=1);

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

namespace ILIAS\Notes;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class GUIService
{
    protected InternalGUIService $internal_gui_service;

    public function __construct(InternalGUIService $internal_gui_service)
    {
        $this->internal_gui_service = $internal_gui_service;
    }

    public function initJavascript(string $ajax_url = ""): void
    {
        $this->internal_gui_service->initJavascript($ajax_url);
    }

    public function getCommentsGUI(
        int $rep_obj_id,
        int $obj_id,
        string $obj_type,
        int $news_id = 0
    ): \ilCommentGUI {
        return $this->internal_gui_service->getCommentsGUI(
            $rep_obj_id,
            $obj_id,
            $obj_type,
            $news_id
        );
    }

    public function getMessagesGUI(
        int $recipient,
        int $rep_obj_id,
        int $obj_id,
        string $obj_type
    ): \ilMessageGUI {
        return $this->internal_gui_service->getMessagesGUI(
            $recipient,
            $rep_obj_id,
            $obj_id,
            $obj_type
        );
    }


}
