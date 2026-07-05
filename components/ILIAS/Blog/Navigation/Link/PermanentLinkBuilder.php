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

use ILIAS\PermanentLink\PermanentLinkManager;

/**
 * Link builder using PermanentLinkManager.
 */
class PermanentLinkBuilder implements LinkBuilder
{
    public function __construct(
        protected PermanentLinkManager $manager
    ) {
    }

    public function forMonth(string $month): string
    {
        // Not supported by permanent links
        return "";
    }

    public function forPosting(int $posting_id, string $month = ""): string
    {
        return $this->manager->getPermanentLink($posting_id);
    }

    public function forKeyword(string $keyword): string
    {
        // Not supported by permanent links
        return "";
    }

    public function forAuthor(int $user_id): string
    {
        // Not supported by permanent links
        return "";
    }

    public function forMainList(): string
    {
        return $this->manager->getPermanentLink();
    }
}
