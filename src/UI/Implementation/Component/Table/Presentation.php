<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Table;

use ILIAS\UI\Component\Table as T;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use ILIAS\UI\Implementation\Component\ViewControl\HasViewControls;

class Presentation extends Table implements T\Presentation
{
    use ComponentHelper;
    use HasViewControls;

    /**
     * @var SignalGeneratorInterface
     */
    protected $signal_generator;
    
    /**
     * @var \Closure
     */
    private $row_mapping;

    /**
     * @var array<mixed>
     */
    private $records;

    /**
     * @var array<string,mixed>
     */
    private $environment;



    public function __construct($title, array $view_controls, \Closure $row_mapping, SignalGeneratorInterface $signal_generator)
    {
        $this->checkStringArg("string", $title);
        $this->title = $title;
        $this->view_controls = $view_controls;
        $this->row_mapping = $row_mapping;
        $this->signal_generator = $signal_generator;
    }

    /**
     * @inheritdoc
     */
    public function getSignalGenerator()
    {
        return $this->signal_generator;
    }

    /**
     * @inheritdoc
     */
    public function withRowMapping(\Closure $row_mapping)
    {
        $clone = clone $this;
        $clone->row_mapping = $row_mapping;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getRowMapping()
    {
        return $this->row_mapping;
    }

    /**
     * @inheritdoc
     */
    public function withEnvironment(array $environment)
    {
        $clone = clone $this;
        $clone->environment = $environment;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * @inheritdoc
     */
    public function withData(array $records)
    {
        $clone = clone $this;
        $clone->records = $records;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getData()
    {
        return $this->records;
    }
}
