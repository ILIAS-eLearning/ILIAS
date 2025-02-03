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

namespace ILIAS\Glossary\Flashcard;

use ILIAS\Glossary;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\Clock\ClockInterface;
use DateTime;

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class FlashcardManager
{
    protected Glossary\InternalDomainService $domain;
    protected FlashcardShuffleManager $shuffle_manager;
    protected Glossary\InternalRepoServiceInterface $repo;
    protected Glossary\InternalDataService $data_service;
    protected FlashcardTermDBRepository $term_db_repo;
    protected FlashcardBoxDBRepository $box_db_repo;
    protected FlashcardSessionRepositoryInterface $session_repo;
    protected int $glo_id;
    protected int $user_id;
    protected \ilObjGlossary $glossary;
    protected ClockInterface $clock;
    /**
     * @var int[]
     */
    protected array $all_glossary_term_ids = [];

    public function __construct(
        Glossary\InternalDomainService $domain_service,
        Glossary\InternalRepoServiceInterface $repo,
        Glossary\InternalDataService $data_service,
        int $glo_ref_id,
        int $user_id
    ) {
        $data_factory = new DataFactory();

        $this->domain = $domain_service;
        $this->shuffle_manager = $this->domain->flashcardShuffle();
        $this->repo = $repo;
        $this->data_service = $data_service;
        $this->term_db_repo = $this->repo->flashcardTerm();
        $this->box_db_repo = $this->repo->flashcardBox();
        $this->session_repo = $this->repo->flashcardSession();
        $this->glo_id = \ilObject::_lookupObjectId($glo_ref_id);
        $this->user_id = $user_id;
        $this->glossary = new \ilObjGlossary($glo_ref_id);
        $this->clock = $data_factory->clock()->system();

        $all_glossary_terms = $this->glossary->getTermList(
            "",
            "",
            "",
            0,
            false,
            false,
            null,
            false,
            true
        );
        foreach ($all_glossary_terms as $term) {
            $this->all_glossary_term_ids[] = (int) $term["id"];
        }
    }

    protected function getNow(): \DateTimeImmutable
    {
        $now = $this->clock->now();

        return $now;
    }

    public function setSessionInitialTerms(
        int $box_nr,
        array $initial_terms
    ): void {
        $this->session_repo->setInitialTerms($this->glo_id, $this->user_id, $box_nr, $initial_terms);
    }

    /**
     * @return Term[]
     */
    public function getSessionInitialTerms(
        int $box_nr
    ): array {
        return $this->session_repo->getInitialTerms($this->glo_id, $this->user_id, $box_nr);
    }

    public function setSessionTerms(
        int $box_nr,
        array $terms
    ): void {
        $this->session_repo->setTerms($this->glo_id, $this->user_id, $box_nr, $terms);
    }

    /**
     * @return Term[]
     */
    public function getSessionTerms(
        int $box_nr
    ): array {
        return $this->session_repo->getTerms($this->glo_id, $this->user_id, $box_nr);
    }

    /**
     * @return Term[]
     */
    public function getAllTermsWithoutEntry(): array
    {
        $terms_with_entry = array_map(fn($term) => $term->getTermId(), $this->getAllUserTerms());

        $terms_without_entry = [];
        foreach ($this->all_glossary_term_ids as $term_id) {
            if (!in_array($term_id, $terms_with_entry)) {
                $terms_without_entry[] = $this->data_service->flashcardTerm($term_id, $this->user_id, $this->glo_id, 1);
            }
        }
        $terms_without_entry = $this->shuffle_manager->shuffleEntries($terms_without_entry);

        return $terms_without_entry;
    }

    /**
     * @return Term[]
     */
    public function getAllUserTerms(): array
    {
        $entries = $this->term_db_repo->getAllUserEntries($this->user_id, $this->glo_id);

        return $entries;
    }

    /**
     * Filter out the terms, for which already exist entries, but are not part of the glossary currently/anymore.
     * Only relevant for virtual glossaries.
     *
     * @param Term[] $terms
     * @return Term[]
     */
    protected function filterTermsNotInGlossary(
        array $terms
    ): array {
        $terms_filtered = [];
        foreach ($terms as $term) {
            if (in_array($term->getTermId(), $this->all_glossary_term_ids)) {
                $terms_filtered[] = $term;
            }
        }

        return $terms_filtered;
    }

    /**
     * @return Term[]
     */
    public function getUserTermsForBox(
        int $box_nr
    ): array {
        $entries = $this->term_db_repo->getUserEntriesForBox($box_nr, $this->user_id, $this->glo_id);
        $entries = $this->shuffle_manager->shuffleEntriesWithEqualDay($entries);
        $entries = $this->filterTermsNotInGlossary($entries);

        return $entries;
    }

    /**
     * @return Term[]
     */
    public function getNonTodayUserTermsForBox(
        int $box_nr
    ): array {
        $entries = $this->term_db_repo->getUserEntriesForBox($box_nr, $this->user_id, $this->glo_id);
        $entries = $this->shuffle_manager->shuffleEntriesWithEqualDay($entries);
        $non_recent_terms = [];
        foreach ($entries as $entry) {
            $entry_day = substr($entry->getLastAccess(), 0, 10);
            $today = $this->getNow()->format("Y-m-d");
            if ($entry_day !== $today) {
                $non_recent_terms[] = $entry;
            }
        }
        $non_recent_terms = $this->filterTermsNotInGlossary($non_recent_terms);

        return $non_recent_terms;
    }

    /**
     * @return Term[]
     */
    public function getTodayUserTermsForBox(
        int $box_nr
    ): array {
        $entries = $this->term_db_repo->getUserEntriesForBox($box_nr, $this->user_id, $this->glo_id);
        $recent_terms = [];
        foreach ($entries as $entry) {
            $entry_day = substr($entry->getLastAccess(), 0, 10);
            $today = $this->getNow()->format("Y-m-d");
            if ($entry_day === $today) {
                $recent_terms[] = $entry;
            }
        }
        $recent_terms = $this->shuffle_manager->shuffleEntries($recent_terms);
        $recent_terms = $this->filterTermsNotInGlossary($recent_terms);

        return $recent_terms;
    }

    public function getItemsForBoxCount(
        int $box_nr
    ): int {
        if ($box_nr === FlashcardBox::FIRST_BOX) {
            $items_without_box = count($this->getAllTermsWithoutEntry());
            $items_in_box = count($this->getUserTermsForBox($box_nr));
            $item_cnt = $items_without_box + $items_in_box;
        } else {
            $item_cnt = count($this->getUserTermsForBox($box_nr));
        }

        return $item_cnt;
    }

    public function getLastAccessForBox(
        int $box_nr
    ): ?string {
        $entry = $this->box_db_repo->getEntry($box_nr, $this->user_id, $this->glo_id);

        return $entry?->getLastAccess();
    }

    public function getLastAccessForBoxAsDaysText(
        int $box_nr
    ): string {
        $lng = $this->domain->lng();
        $date_str = $this->getLastAccessForBox($box_nr);
        if (!$date_str) {
            return $lng->txt("never");
        }
        $date_tmp = new \ilDateTime($date_str, IL_CAL_DATETIME);
        $date = new DateTime($date_tmp->get(IL_CAL_DATE));
        $now = new DateTime($this->getNow()->format("Y-m-d"));
        $diff = $date->diff($now)->days;
        if ($diff < 0) {
            return "invalid";
        } elseif ($diff === 0) {
            return $lng->txt("today");
        } elseif ($diff === 1) {
            return $lng->txt("yesterday");
        } else {
            return sprintf($lng->txt("glo_days_ago"), $diff);
        }
    }

    public function getBoxNr(
        int $term_id
    ): ?int {
        $entry = $this->term_db_repo->getEntry($term_id, $this->user_id, $this->glo_id);

        return $entry?->getBoxNr();
    }

    public function getBoxProgress(
        array $current_terms,
        array $all_terms
    ): int {
        if (count($all_terms) === 0) {
            return 0;
        }
        $shown_terms_cnt = count($all_terms) - count($current_terms);
        $progress = (int) round((($shown_terms_cnt + 1) / count($all_terms)) * 100);

        return $progress;
    }

    public function createOrUpdateBoxAccessEntry(
        int $box_nr
    ): void {
        $now = $this->getNow()->format("Y-m-d H:i:s");
        $box = $this->data_service->flashcardBox($box_nr, $this->user_id, $this->glo_id, $now);
        $this->box_db_repo->createOrUpdateEntry($box);
    }

    public function createOrUpdateUserTermEntry(
        int $term_id,
        bool $correct
    ): void {
        $box_nr = $this->getBoxNr($term_id);
        $now = $this->getNow()->format("Y-m-d H:i:s");

        if ($box_nr) {
            $box_nr = $correct ? ($box_nr + 1) : 1;
            $term = $this->data_service->flashcardTerm($term_id, $this->user_id, $this->glo_id, $box_nr, $now);
            $this->term_db_repo->updateEntry($term);
        } else {
            $box_nr = $correct ? 2 : 1;
            $term = $this->data_service->flashcardTerm($term_id, $this->user_id, $this->glo_id, $box_nr, $now);
            $this->term_db_repo->createEntry($term);
        }
    }

    public function resetEntries(): void
    {
        $this->term_db_repo->deleteEntries($this->glo_id, $this->user_id);
        $this->box_db_repo->deleteEntries($this->glo_id, $this->user_id);
    }

    public function deleteAllUserEntries(): void
    {
        $this->term_db_repo->deleteAllUserEntries($this->user_id);
        $this->box_db_repo->deleteAllUserEntries($this->user_id);
    }

    public function deleteAllGlossaryEntries(): void
    {
        if ($this->glo_id === 0) {
            throw new \ilGlossaryException("No glossary id given in FlashcardManager.");
        }
        $this->term_db_repo->deleteAllGlossaryEntries($this->glo_id);
        $this->box_db_repo->deleteAllGlossaryEntries($this->glo_id);
    }

    public function deleteAllTermEntries(
        int $term_id
    ): void {
        $this->term_db_repo->deleteAllTermEntries($term_id);
    }
}
