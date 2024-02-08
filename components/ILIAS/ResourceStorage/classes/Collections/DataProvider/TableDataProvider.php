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

namespace ILIAS\components\ResourceStorage\Collections\DataProvider;

use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\components\ResourceStorage\Collections\View\Request;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class TableDataProvider
{
    private \ILIAS\ResourceStorage\Services $irss;

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
     * @return ResourceIdentification[]
     */
    public function getIdentifications(): array
    {
        $collection = $this->view_request->getCollection();
        // Sort
        $sorter = $this->irss->collection()->sort($collection);

        switch ($this->view_request->getSortation()) {
            case Request::BY_TITLE_ASC:
                $collection = $sorter->asc()->byTitle();
                break;
            case Request::BY_TITLE_DESC:
                $collection = $sorter->desc()->byTitle();
                break;
            case Request::BY_CREATION_DATE_ASC:
                $collection = $sorter->asc()->byCreationDate();
                break;
            case Request::BY_CREATION_DATE_DESC:
                $collection = $sorter->desc()->byCreationDate();
                break;
            case Request::BY_SIZE_ASC:
                $collection = $sorter->asc()->bySize();
                break;
            case Request::BY_SIZE_DESC:
                $collection = $sorter->desc()->bySize();
                break;
        }

        return $this->irss->collection()->rangeAsArray(
            $collection,
            $this->view_request->getPage() * $this->view_request->getItemsPerPage(),
            $this->view_request->getItemsPerPage(),
        );
    }

    public function getTotal(): int
    {
        return $this->view_request->getCollection()->count();
    }
}
