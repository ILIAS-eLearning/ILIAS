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

namespace ILIAS\UI\Implementation\Component\Table;

use ILIAS\UI\Component\Table as T;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use ILIAS\UI\Implementation\Component\Input\ViewControl\Factory as ViewControlFactory;
use ILIAS\UI\Implementation\Component\Input\Container\ViewControl\Factory as ViewControlContainerFactory;
use ILIAS\Data\Factory as DataFactory;
use Closure;
use ILIAS\Data\URI;

class Factory implements T\Factory
{
    public function __construct(
        protected SignalGeneratorInterface $signal_generator,
        protected ViewControlFactory $view_control_factory,
        protected ViewControlContainerFactory $view_control_container_factory,
        protected DataFactory $data_factory,
        protected Column\Factory $column_factory,
        protected Action\Factory $action_factory,
        protected \ArrayAccess $storage,
        protected DataRowBuilder $data_row_builder,
        protected OrderingRowBuilder $ordering_row_builder
    ) {
    }

    public function presentation(string $title, array $view_controls, Closure $row_mapping): Presentation
    {
        return new Presentation($title, $view_controls, $row_mapping, $this->signal_generator);
    }

    public function data(
        T\DataRetrieval $data_retrieval,
        string $title,
        array $columns,
    ): Data {
        return new Data(
            $this->signal_generator,
            $this->view_control_factory,
            $this->view_control_container_factory,
            $this->data_factory,
            $this->data_row_builder,
            $title,
            $columns,
            $data_retrieval,
            $this->storage
        );
    }

    public function column(): Column\Factory
    {
        return $this->column_factory;
    }

    public function action(): Action\Factory
    {
        return $this->action_factory;
    }

    public function ordering(
        T\OrderingRetrieval $ordering_retrieval,
        URI $target_url,
        string $title,
        array $columns,
    ): Ordering {
        return new Ordering(
            $this->signal_generator,
            $this->view_control_factory,
            $this->view_control_container_factory,
            $this->ordering_row_builder,
            $title,
            $columns,
            $ordering_retrieval,
            $target_url,
            $this->storage
        );
    }
}
