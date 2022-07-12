<?php declare(strict_types = 1);

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

namespace ILIAS\Survey\Editing;

use ILIAS\Survey\InternalDomainService;
use ILIAS\Survey\InternalRepoService;

/**
 * Editing manager. Provides common editing feature, like
 * wrapping the edit session repository.
 * @author Alexander Killing <killing@leifos.de>
 */
class EditManager
{
    private EditSessionRepo $repo;
    protected InternalDomainService $domain_service;

    public function __construct(
        InternalRepoService $repo_service,
        InternalDomainService $domain_service
    ) {
        $this->repo = $repo_service->edit();
        $this->domain_service = $domain_service;
    }

    public function setConstraintStructure(?array $structure) : void
    {
        $this->repo->setConstraintStructure($structure);
    }

    public function getConstraintStructure() : ?array
    {
        return $this->repo->getConstraintStructure();
    }

    public function clearConstraintStructure() : void
    {
        $this->repo->clearConstraintStructure();
    }

    public function setConstraintElements(?array $elements) : void
    {
        $this->repo->setConstraintElements($elements);
    }

    public function getConstraintElements() : ?array
    {
        return $this->repo->getConstraintElements();
    }

    public function clearConstraintElements() : void
    {
        $this->repo->clearConstraintElements();
    }

    public function setMoveSurveyQuestions(int $survey_id, array $question_ids) : void
    {
        $this->repo->setMoveSurveyQuestions($survey_id, $question_ids);
    }

    public function clearMoveSurveyQuestions() : void
    {
        $this->repo->clearMoveSurveyQuestions();
    }

    public function getMoveSurveyQuestions() : array
    {
        return $this->repo->getMoveSurveyQuestions();
    }

    public function getMoveSurveyId() : int
    {
        return $this->repo->getMoveSurveyId();
    }

    public function setQuestionClipboard(
        int $ref_id,
        int $page,
        string $mode,
        array $question_ids
    ) : void {
        $this->repo->setQuestionClipboard($ref_id, $page, $mode, $question_ids);
    }

    public function clearQuestionClipboard(int $ref_id) : void
    {
        $this->repo->clearQuestionClipboard($ref_id);
    }

    public function getQuestionClipboardSourcePage(int $ref_id) : ?int
    {
        return $this->repo->getQuestionClipboardSourcePage($ref_id);
    }

    public function getQuestionClipboardMode(int $ref_id) : string
    {
        return $this->repo->getQuestionClipboardMode($ref_id);
    }

    public function getQuestionClipboardQuestions(int $ref_id) : array
    {
        return $this->repo->getQuestionClipboardQuestions($ref_id);
    }

    public function isQuestionClipboardEmpty(int $ref_id) : bool
    {
        return $this->repo->isQuestionClipboardEmpty($ref_id);
    }

    // 1: no pool; 2: new pool, 3: existing pool
    // @todo: avoid session use or introduce constants
    public function setPoolChoice(int $id) : void
    {
        $this->repo->setPoolChoice($id);
    }

    public function getPoolChoice() : int
    {
        return $this->repo->getPoolChoice();
    }

    public function setExternalText(string $text) : void
    {
        $this->repo->setExternalText($text);
    }

    public function getExternalText() : string
    {
        return $this->repo->getExternalText();
    }
}
