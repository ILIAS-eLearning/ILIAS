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

/**
 * Abstract learning history provider
 * @author Alexander Killing <killing@leifos.de>
 */
abstract class ilAbstractLearningHistoryProvider //TODO-PHP8-REVIEW please add the methods that need to be overwritten
{
    protected int $user_id;         // User id. This is the user the history will be retrieved for.
    protected ilLearningHistoryFactory $factory;
    protected ilLanguage $lng;
    private ?ilTemplate $template;

    public function __construct(
        int $user_id,
        ilLearningHistoryFactory $factory,
        ilLanguage $lng,
        ilTemplate $template = null
    ) {
        $this->user_id = $user_id;
        $this->factory = $factory;
        $this->lng = $lng;

        if ($template === null) {
            $template = new ilTemplate(
                'tpl.emphasized_title.php',
                true,
                true,
                'Services/LearningHistory'
            );
        }
        $this->template = $template;
    }

    protected function getUserId(): int
    {
        return $this->user_id;
    }

    protected function getFactory(): ilLearningHistoryFactory
    {
        return $this->factory;
    }

    protected function getLanguage(): ilLanguage
    {
        return $this->lng;
    }

    protected function getEmphasizedTitle(string $title): string
    {
        $clone = clone $this->template;
        $clone->setVariable("TITLE", $title);
        return $clone->get();
    }
}
