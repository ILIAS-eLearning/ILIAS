<?php namespace ILIAS\GlobalScreen\Scope\Tool\Provider;

use ILIAS\DI\Container;
use ILIAS\GlobalScreen\Identification\ToolIdentificationProviderInterface;
use ILIAS\GlobalScreen\Provider\AbstractProvider;
use ILIAS\GlobalScreen\Scope\Tool\Factory\ToolFactory;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Class AbstractDynamicToolProvider
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractDynamicToolProvider extends AbstractProvider implements DynamicToolProvider
{
    protected ToolIdentificationProviderInterface $identification_provider;
  
    protected ContextCollection $context_collection;

    protected ToolFactory $factory;
    
    /**
     * @inheritDoc
     */
    public function __construct(Container $dic)
    {
        parent::__construct($dic);
        $this->context_collection = $this->globalScreen()->tool()->context()->collection();
        $this->factory = $this->globalScreen()->tool()->factory();
        $this->identification_provider = $this->globalScreen()->identification()->tool($this);
    }
}
