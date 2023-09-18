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

namespace ILIAS\UI\Implementation\Component;

use ILIAS\UI\Help\Topic;

trait HasHelpTopics
{
    /** @var Help\Topic[] **/
    protected array $help_topics = [];

    /**
     * @see ILIAS\UI\Component\HasHelpTopic::withHelpTopics
     */
    public function withHelpTopics(Topic ...$topics): static
    {
        $clone = clone $this;
        $clone->help_topics = array_unique($topics, SORT_REGULAR);
        sort($clone->help_topics);
        return $clone;
    }

    /**
     * @see ILIAS\UI\Component\HasHelpTopic::withAdditionalHelpTopics
     */
    public function withAdditionalHelpTopics(Topic ...$topics): static
    {
        $clone = clone $this;
        $clone->help_topics = array_unique(array_merge($this->help_topics, $topics), SORT_REGULAR);
        sort($clone->help_topics);
        return $clone;
    }

    /**
     * @see ILIAS\UI\Component\HasHelpTopic::getHelpTopics
     */
    public function getHelpTopics(): array
    {
        return $this->help_topics;
    }
}
