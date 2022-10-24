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

namespace ILIAS\SurveyQuestionPool\Editing;

/**
 * Manages editing processes/repos
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class EditManager
{
    protected EditSessionRepository $repo;

    public function __construct(EditSessionRepository $repo)
    {
        $this->repo = $repo;
    }

    public function addQuestionToClipboard(int $qid, string $action): void
    {
        $this->repo->addQuestionToClipboard($qid, $action);
    }

    public function getQuestionsFromClipboard(): array
    {
        return $this->repo->getQuestionsFromClipboard();
    }

    public function clearClipboardQuestions(): void
    {
        $this->repo->clearClipboardQuestions();
    }

    public function setSearchLinkType(string $type): void
    {
        $this->repo->setSearchLinkType($type);
    }

    public function getSearchLinkType(): string
    {
        return $this->repo->getSearchLinkType();
    }

    public function clearSearchLinkType(): void
    {
        $this->repo->clearSearchLinkType();
    }

    public function setNewLinkType(string $type): void
    {
        $this->repo->setNewLinkType($type);
    }

    public function getNewLinkType(): string
    {
        return $this->repo->getNewLinkType();
    }

    public function clearNewLinkType(): void
    {
        $this->repo->clearNewLinkType();
    }

    public function setPhraseData(array $data): void
    {
        $this->repo->setPhraseData($data);
    }

    public function getPhraseData(): array
    {
        return $this->repo->getPhraseData();
    }
}
