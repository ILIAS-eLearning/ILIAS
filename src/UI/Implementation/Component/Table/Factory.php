<?php declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Table;

use ILIAS\UI\Component\Table as T;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;

/**
 * Implementation of factory for tables
 *
 * @author Nils Haagen <nhaagen@concepts-and-training.de>
 */
class Factory implements T\Factory
{

    /**
     * @var SignalGeneratorInterface
     */
    protected $signal_generator;

    /**
     * @param SignalGeneratorInterface $signal_generator
     */
    public function __construct(SignalGeneratorInterface $signal_generator)
    {
        $this->signal_generator = $signal_generator;
    }

    /**
     * @inheritdoc
     */
    public function presentation(string $title, array $view_controls, \Closure $row_mapping) : T\Presentation
    {
        return new Presentation($title, $view_controls, $row_mapping, $this->signal_generator);
    }

    /**
     * @inheritdoc
     */
    public function data(string $title, ?int $page_size = 50) : T\Data
    {
        throw new \ILIAS\UI\NotImplementedException('NYI');
    }

    /**
     * @inheritdoc
     */
    public function column() : T\Column\Factory
    {
        return new Column\Factory();
    }
}
