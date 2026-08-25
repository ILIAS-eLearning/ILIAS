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

namespace ILIAS\Blog\Posting;

class PostingList
{
    /** @var Posting[]|null */
    protected ?array $postings = null;

    public function __construct(
        protected int $obj_id,
        protected PostingManager $posting_manager,
        protected \ILIAS\Blog\Settings\Settings $settings,
        protected bool $include_inactive = true
    ) {
    }

    /**
     * @return Posting[]
     */
    public function getPostingsForView(
        int $author_id = 0,
        string $keyword = "",
        string $month = ""
    ): array {
        if ($author_id > 0) {
            return $this->getByAuthor($author_id);
        }
        if ($keyword !== "") {
            return $this->getByKeyword($keyword);
        }

        $max = $this->settings->getOverviewPostings();
        if ($month === "" && $max > 0) {
            $list_items = [];
            $all_items = $this->getPostingsGroupedByMonth();
            foreach ($all_items as $postings) {
                foreach ($postings as $id => $item) {
                    $list_items[$id] = $item;
                    if (count($list_items) >= $max) {
                        break(2);
                    }
                }
            }
            return $list_items;
        }

        return $this->getByMonth($month);
    }

    /**
     * @return Posting[]
     */
    protected function getPostings(): array
    {
        if ($this->postings === null) {
            $this->postings = $this->posting_manager->getAllPostings($this->obj_id);
            if (!$this->include_inactive) {
                $this->postings = array_filter($this->postings, function (Posting $posting) {
                    return $posting->isActive();
                });
            }
        }
        return $this->postings;
    }

    /**
     * @return Posting[]
     */
    public function getByMonth(string $month): array
    {
        $res = [];
        foreach ($this->getPostings() as $posting) {
            if (substr($posting->getCreated()->get(IL_CAL_DATE), 0, 7) === $month) {
                $res[$posting->getId()] = $posting;
            }
        }
        return $res;
    }

    /**
     * @return Posting[]
     */
    public function getByAuthor(int $author_id): array
    {
        $res = [];
        foreach ($this->getPostings() as $posting) {
            if ($posting->getAuthor() === $author_id) {
                $res[$posting->getId()] = $posting;
                continue;
            }
            foreach (\ilPageObject::getPageContributors("blp", $posting->getId()) as $editor) {
                if ((int) $editor["user_id"] === $author_id) {
                    $res[$posting->getId()] = $posting;
                    break;
                }
            }
        }
        return $res;
    }

    /**
     * @return Posting[]
     */
    public function getByKeyword(string $keyword): array
    {
        $res = [];
        foreach ($this->getPostings() as $posting) {
            if (in_array(
                $keyword,
                $this->posting_manager->getKeywords($this->obj_id, $posting->getId()),
                true
            )) {
                $res[$posting->getId()] = $posting;
            }
        }
        return $res;
    }

    /**
     * @return array<string, array<int, Posting>> [month => [posting_id => Posting]]
     */
    public function getPostingsGroupedByMonth(): array
    {
        $items = [];
        foreach ($this->getPostings() as $posting) {
            $month = substr($posting->getCreated()->get(IL_CAL_DATE), 0, 7);
            $items[$month][$posting->getId()] = $posting;
        }
        return $items;
    }

    public function prepareViewState(
        string $month,
        ?int $author
    ): PostingViewState {
        if ($author && !$this->hasAuthorPostings($author)) {
            $author = null;
        }

        $items = $this->getPostingsGroupedByMonth();
        if ($items && (!$month || !isset($items[$month]) || $items[$month] === [])) {
            $month = (string) array_key_first($items);
        }

        return new PostingViewState($month, $author);
    }

    /**
     * @return string[] months (YYYY-MM)
     */
    public function getMonthsWithPostings(): array
    {
        $months = [];
        foreach ($this->getPostings() as $posting) {
            $month = substr($posting->getCreated()->get(IL_CAL_DATE), 0, 7);
            if (!in_array($month, $months, true)) {
                $months[] = $month;
            }
        }
        rsort($months);
        return $months;
    }

    /**
     * @return int[] user ids
     */
    public function getAuthors(): array
    {
        $authors = [];
        foreach ($this->getPostings() as $posting) {
            $author_id = $posting->getAuthor();
            if ($author_id > 0 && !in_array($author_id, $authors, true)) {
                $authors[] = $author_id;
            }

            foreach (\ilPageObject::getPageContributors("blp", $posting->getId()) as $editor) {
                $editor_id = (int) $editor["user_id"];
                if ($editor_id > 0 && !in_array($editor_id, $authors, true)) {
                    $authors[] = $editor_id;
                }
            }
        }
        return $authors;
    }

    public function hasAuthorPostings(int $user_id): bool
    {
        foreach ($this->getPostings() as $posting) {
            if ($posting->getAuthor() === $user_id) {
                return true;
            }
            foreach (\ilPageObject::getPageContributors("blp", $posting->getId()) as $editor) {
                if ((int) $editor["user_id"] === $user_id) {
                    return true;
                }
            }
        }
        return false;
    }
}
