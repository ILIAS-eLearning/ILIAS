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

use ILIAS\UI\Component\ViewControl\Sortation as SortationViewControl;

trait ilSearchResultTableHelper
{
    protected string $current_sortation;

    /**
     * Returns key => label
     */
    abstract protected function getPossibleSortations(): array;

    abstract protected function getDefaultSortation(): string;

    protected function getCurrentSortation(): string
    {
        if (isset($this->current_sortation)) {
            return $this->current_sortation;
        }

        $sortation = $this->getDefaultSortation();
        if ($this->http->wrapper()->query()->has('sortation')) {
            $sortation = $this->http->wrapper()->query()->retrieve(
                'sortation',
                $this->refinery->kindlyTo()->string()
            );
        }
        if (!array_key_exists($sortation, $this->getPossibleSortations())) {
            $sortation = $this->getDefaultSortation();
        }
        return $this->current_sortation = $sortation;
    }

    protected function buildSortationViewControl(): SortationViewControl
    {
        $options = $this->getPossibleSortations();
        $current_sortation = $this->getCurrentSortation();

        return $this->ui->factory()->viewControl()
                        ->sortation($options, $current_sortation)
                        ->withLabelPrefix($this->lng->txt('search_sort_by'))
                        ->withTargetURL(
                            $this->ctrl->getLinkTarget($this->parent_obj, $this->parent_cmd),
                            'sortation'
                        );
    }
}
