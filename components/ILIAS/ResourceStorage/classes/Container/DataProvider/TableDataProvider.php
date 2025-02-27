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

namespace ILIAS\components\ResourceStorage\Container\DataProvider;

use ILIAS\ResourceStorage\Services;
use ILIAS\components\ResourceStorage\Container\View\Request;
use ILIAS\components\ResourceStorage\Container\Wrapper\Dir;
use ILIAS\components\ResourceStorage\Container\Wrapper\File;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class TableDataProvider
{
    private Services $irss;

    public function __construct(
        private Request $view_request,
    ) {
        global $DIC;
        $this->irss = $DIC->resourceStorage();
    }

    public function getViewRequest(): Request
    {
        return $this->view_request;
    }

    /**
     * @return array<Dir|File>
     */
    public function getEntries(): array
    {
        static $entries_at_current_level;
        static $current_level;

        if ($current_level !== $this->view_request->getPath()) {
            unset($entries_at_current_level);
        }
        if (isset($entries_at_current_level)) {
            return $entries_at_current_level;
        }

        $current_level = $this->view_request->getPath();

        $entries_at_current_level = iterator_to_array(
            $this->view_request->getWrapper()->getEntries()
        );

        /** @var Dir[]|File[] $entries_at_current_level */
        usort($entries_at_current_level, function (File|Dir $a, File|Dir $b): int {
            $size_a = $a instanceof Dir ? 0 : $a->getSize();
            $size_b = $b instanceof Dir ? 0 : $b->getSize();
            $type_a = $a instanceof Dir ? '' : $a->getMimeType();
            $type_b = $b instanceof Dir ? '' : $b->getMimeType();
            return match ($this->view_request->getSortation()) {
                Request::BY_CREATION_DATE_DESC => $b->getModificationDate()->getTimestamp() <=> $a->getModificationDate()->getTimestamp(),
                Request::BY_CREATION_DATE_ASC => $b->getModificationDate()->getTimestamp() <=> $a->getModificationDate()->getTimestamp(),
                Request::BY_SIZE_DESC => $size_a - $size_b,
                Request::BY_SIZE_ASC => $size_b - $size_a,
                Request::BY_TITLE_DESC => strcasecmp($b->getTitle(), $a->getTitle()),
                Request::BY_TITLE_ASC => strcasecmp($a->getTitle(), $b->getTitle()),
                Request::BY_TYPE_DESC => strcasecmp($type_a, $type_b),
                Request::BY_TYPE_ASC => strcasecmp($type_b, $type_a),
                default => strcasecmp($a->getTitle(), $b->getTitle()),
            };
        });

        return $entries_at_current_level;
    }

    public function getTotal(): int
    {
        return count($this->getEntries());
    }
}
