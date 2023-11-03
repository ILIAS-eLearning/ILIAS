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

namespace ILIAS\Glossary;

use ILIAS\DI\Container;
use ILIAS\Glossary\Term\TermManager;
use ILIAS\Glossary\Flashcard\FlashcardManager;
use ILIAS\Repository\GlobalDICDomainServices;
use ILIAS\Glossary\Flashcard\FlashcardShuffleManager;
use ILIAS\Glossary\Presentation\PresentationManager;
use ILIAS\Glossary\Taxonomy\TaxonomyManager;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class InternalDomainService
{
    use GlobalDICDomainServices;

    protected InternalRepoService $repo_service;
    protected InternalDataService $data_service;

    public function __construct(
        Container $DIC,
        InternalRepoService $repo_service,
        InternalDataService $data_service
    ) {
        $this->repo_service = $repo_service;
        $this->data_service = $data_service;
        $this->initDomainServices($DIC);
    }

    public function log(): \ilLogger
    {
        return $this->logger()->glo();
    }

    public function term(\ilObjGlossary $glossary, int $user_id = 0): TermManager
    {
        if ($user_id == 0) {
            $user_id = $this->user()->getId();
        }
        return new TermManager(
            $this,
            $this->repo_service->termSession(),
            $glossary,
            $user_id
        );
    }

    public function flashcard(int $glo_ref_id = 0, int $user_id = 0): FlashcardManager
    {
        if ($user_id == 0) {
            $user_id = $this->user()->getId();
        }
        return new FlashcardManager(
            $this,
            $this->repo_service,
            $glo_ref_id,
            $user_id
        );
    }

    public function flashcardShuffle(): FlashcardShuffleManager
    {
        return new FlashcardShuffleManager();
    }

    public function presentation(\ilObjGlossary $glossary, int $user_id = 0): PresentationManager
    {
        if ($user_id == 0) {
            $user_id = $this->user()->getId();
        }
        return new PresentationManager(
            $this,
            $this->repo_service->presentationSession(),
            $glossary,
            $user_id
        );
    }

    public function taxonomy(\ilObjGlossary $glossary): TaxonomyManager
    {
        return new TaxonomyManager(
            $this->DIC->taxonomy()->domain(),
            $glossary
        );
    }
}
