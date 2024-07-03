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

namespace ILIAS\Container\Content;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ViewManager
{
    private int $user_id;
    protected ViewSessionRepository $view_repo;

    public function __construct(
        ViewSessionRepository $view_repo,
        ?int $user_id = null
    ) {
        global $DIC;    // fixes 41305, to do: move to constructor
        if (is_null($user_id)) {
            $this->user_id = $DIC->user()->getId();
        } else {
            $this->user_id = $user_id;
        }

        $this->view_repo = $view_repo;
    }

    public function setAdminView(): void
    {
        if (in_array($this->user_id, [ANONYMOUS_USER_ID, 0], true)) {
            return;
        }
        $this->view_repo->setAdminView();
    }

    public function setContentView(): void
    {
        $this->view_repo->setContentView();
    }

    public function isAdminView(): bool
    {
        return $this->view_repo->isAdminView();
    }

    public function isContentView(): bool
    {
        return $this->view_repo->isContentView();
    }
}
