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
use ILIAS\UI\Implementation\Component\Input\Field\Factory as FieldFactory;

/**
 * Factory for View Controls
 */
class Factory implements VCInterface\Factory
{
    public function __construct(
        protected FieldFactory $field_factory,
        protected DataFactory $data_factory,
        protected Refinery $refinery,
        protected SignalGeneratorInterface $signal_generator,
        protected \ilLanguage $language,
    ) {
    }

    public function fieldSelection(array $options): VCInterface\FieldSelection
    {
        return new FieldSelection(
            $this->data_factory,
            $this->refinery,
            $this->signal_generator,
            $options
        );
    }

    public function sortation(array $options): VCInterface\Sortation
    {
        return new Sortation(
            $this->field_factory,
            $this->data_factory,
            $this->refinery,
            $this->signal_generator,
            $options
        );
    }

    public function pagination(): VCInterface\Pagination
    {
        return new Pagination(
            $this->field_factory,
            $this->data_factory,
            $this->refinery,
            $this->signal_generator
        );
    }

    public function group(array $view_contorls): VCInterface\Group
    {
        return new Group(
            $this->data_factory,
            $this->refinery,
            $this->language,
            $view_contorls,
        );
    }
}
