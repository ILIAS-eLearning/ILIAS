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
use ILIAS\UI\NotImplementedException;
use ILIAS\Data\Factory as DataFactory;
use Closure;

/**
 * Implementation of factory for tables
 *
 * @author Nils Haagen <nhaagen@concepts-and-training.de>
 */
class Factory implements T\Factory
{
    protected SignalGeneratorInterface $signal_generator;
    protected DataFactory $data_factory;

    public function __construct(
        SignalGeneratorInterface $signal_generator,
        DataFactory $data_factory
    ) {
        $this->signal_generator = $signal_generator;
        $this->data_factory = $data_factory;
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
            $this->data_factory,
            $title,
            $columns,
            $data_retrieval
        );
    }

    /**
     * @inheritdoc
     */
    public function column(): T\Column\Factory
    {
        return new Column\Factory();
    }

    /**
     * @inheritdoc
     */
    public function action(): T\Action\Factory
    {
        return new Action\Factory();
    }
}
