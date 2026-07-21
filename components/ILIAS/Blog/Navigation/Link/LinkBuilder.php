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

namespace ILIAS\Blog\Navigation\Link;

/**
 * Interface for building links within the Blog component.
 */
interface LinkBuilder
{
    /**
     * Get link to a monthly list of postings.
     */
    public function forMonth(string $month): string;

    /**
     * Get link to a specific blog posting.
     */
    public function forPosting(int $posting_id, string $month = ""): string;

    /**
     * Get link to a list of postings filtered by keyword.
     */
    public function forKeyword(string $keyword): string;

    /**
     * Get link to a list of postings filtered by author.
     */
    public function forAuthor(int $user_id): string;

    /**
     * Get link to the main list (starting page).
     */
    public function forMainList(): string;
}
