<?php namespace ILIAS\GlobalScreen\Scope\Tool\Provider;

use ILIAS\DI\Container;
use ILIAS\GlobalScreen\Provider\AbstractProvider;

/**
 * Class AbstractDynamicToolProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractDynamicToolProvider extends AbstractProvider implements DynamicToolProvider
{

    /**
     * @var \ILIAS\GlobalScreen\Identification\IdentificationProviderInterface
     */
    protected $identification_provider;
    /**
     * @var \ILIAS\GlobalScreen\Scope\Tool\Context\Stack\ContextCollection
     */
    protected $context_collection;
    /**
     * @var \ILIAS\GlobalScreen\Scope\Tool\Factory\ToolFactory
     */
    protected $factory;


    /**
     * @inheritDoc
     */
    public function __construct(Container $dic)
    {
        parent::__construct($dic);
        $this->context_collection = $this->globalScreen()->tool()->context()->collection();
        $this->factory = $this->globalScreen()->tool()->factory();
        $this->identification_provider = $this->globalScreen()->identification()->core($this);
    }
}
