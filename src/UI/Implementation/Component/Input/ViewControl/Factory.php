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

namespace ILIAS\UI\Implementation\Component\Input\ViewControl;

use ILIAS\UI\Component\Input\ViewControl as VCInterface;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Refinery\Factory as Refinery;

/**
 * Factory for View Controls
 */
class Factory implements VCInterface\Factory
{
    public function __construct(
        protected DataFactory $data_factory,
        protected Refinery $refinery,
        protected SignalGeneratorInterface $signal_generator
    ) {
    }

    public function fieldSelection(
        array $options,
        string $label = FieldSelection::DEFAULT_DROPDOWN_LABEL,
        string $button_label = FieldSelection::DEFAULT_BUTTON_LABEL
    ): VCInterface\FieldSelection {
        return new FieldSelection(
            $this->data_factory,
            $this->refinery,
            $this->signal_generator,
            $options,
            $label,
            $button_label
        );
    }

    public function sortation(
        array $options,
        string $label = Sortation::DEFAULT_DROPDOWN_LABEL
    ): VCInterface\Sortation {
        return new Sortation(
            $this->data_factory,
            $this->refinery,
            $this->signal_generator,
            $options,
            $label
        );
    }

    public function pagination(
        string $label_offset = Pagination::DEFAULT_DROPDOWN_LABEL_OFFSET,
        string $label_limit = Pagination::DEFAULT_DROPDOWN_LABEL_LIMIT
    ): VCInterface\Pagination {
        return new Pagination(
            $this->data_factory,
            $this->refinery,
            $this->signal_generator,
            $label_offset,
            $label_limit
        );
    }
}
