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
use ILIAS\UI\Component\Input\ViewControl\Factory as ViewControlFactory;
use ILIAS\UI\Component\Input\Container\ViewControl\Factory as ViewControlContainerFactory;
use ILIAS\Data\Factory as DataFactory;
use Closure;

/**
 * Implementation of factory for tables
 */
class Factory implements T\Factory
{
    protected SignalGeneratorInterface $signal_generator;
    protected ViewControlFactory $view_control_factory;
    protected ViewControlContainerFactory $view_control_container_factory;
    protected DataFactory $data_factory;
    protected T\Column\Factory $column_factory;
    protected T\Action\Factory $action_factory;
    protected DataRowBuilder $data_row_builder;
    protected \ArrayAccess $storage;

    public function __construct(
        SignalGeneratorInterface $signal_generator,
        ViewControlFactory $view_control_factory,
        ViewControlContainerFactory $view_control_container_factory,
        DataFactory $data_factory,
        T\Column\Factory $column_factory,
        T\Action\Factory $action_factory,
        DataRowBuilder $data_row_builder,
        \ArrayAccess $storage
    ) {
        $this->signal_generator = $signal_generator;
        $this->view_control_factory = $view_control_factory;
        $this->view_control_container_factory = $view_control_container_factory;
        $this->data_factory = $data_factory;
        $this->column_factory = $column_factory;
        $this->action_factory = $action_factory;
        $this->data_row_builder = $data_row_builder;
        $this->storage = $storage;
    }

    /**
     * @inheritdoc
     */
    public function presentation(string $title, array $view_controls, Closure $row_mapping): T\Presentation
    {
        return new Presentation($title, $view_controls, $row_mapping, $this->signal_generator);
    }

    /**
     * @inheritdoc
     */
    public function data(
        string $title,
        array $columns,
        T\DataRetrieval $data_retrieval
    ): T\Data {
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

    /**
     * @inheritdoc
     */
    public function column(): T\Column\Factory
    {
        return $this->column_factory;
    }

    /**
     * @inheritdoc
     */
    public function action(): T\Action\Factory
    {
        return $this->action_factory;
    }
}
