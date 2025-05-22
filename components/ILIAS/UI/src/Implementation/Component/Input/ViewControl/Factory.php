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
use ILIAS\Language\Language;

class Factory implements VCInterface\Factory
{
    public function __construct(
        protected FieldFactory $field_factory,
        protected DataFactory $data_factory,
        protected Refinery $refinery,
        protected SignalGeneratorInterface $signal_generator,
        protected Language $language,
    ) {
    }

    public function fieldSelection(array $options): FieldSelection
    {
        return new FieldSelection(
            $this->data_factory,
            $this->refinery,
            $this->signal_generator,
            $options
        );
    }

    public function sortation(array $options): Sortation
    {
        return new Sortation(
            $this->field_factory,
            $this->data_factory,
            $this->refinery,
            $this->signal_generator,
            $options
        );
    }

    public function pagination(): Pagination
    {
        return new Pagination(
            $this->field_factory,
            $this->data_factory,
            $this->refinery,
            $this->signal_generator
        );
    }

    public function group(array $view_controls): Group
    {
        return new Group(
            $this->data_factory,
            $this->refinery,
            $this->language,
            $view_controls,
        );
    }

    public function nullControl(): NullControl
    {
        return new NullControl(
            $this->data_factory,
            $this->refinery
        );
    }

    public function mode(array $options): VCInterface\Mode
    {
        return new Mode(
            $this->data_factory,
            $this->refinery,
            $options
        );
    }
}
