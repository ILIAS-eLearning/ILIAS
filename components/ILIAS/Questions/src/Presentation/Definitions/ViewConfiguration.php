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

namespace ILIAS\Questions\Presentation\Definitions;

class ViewConfiguration
{
    public function __construct(
        private bool $interactive,
        private readonly bool $show_marks,
        private bool $show_best_response,
        private readonly bool $show_feedback
    ) {
    }

    public function isInteractive(): bool
    {
        return $this->interactive;
    }

    public function showMarks(): bool
    {
        return $this->show_marks;
    }

    public function showBestResponse(): bool
    {
        return $this->show_best_response;
    }

    public function withShowBestResponse(): self
    {
        $clone = clone $this;
        $clone->interactive = false;
        $clone->show_best_response = true;
        return $clone;
    }

    public function showFeedback(): bool
    {
        return $this->show_feedback;
    }

    public function getViewMode(): ViewMode
    {
        return $this->view_mode;
    }
}
