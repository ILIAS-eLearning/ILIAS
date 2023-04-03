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
namespace ILIAS\GlobalScreen\Scope\Tool\Provider;

use ILIAS\DI\Container;
use ILIAS\GlobalScreen\Identification\ToolIdentificationProviderInterface;
use ILIAS\GlobalScreen\Provider\AbstractProvider;
use ILIAS\GlobalScreen\Scope\Tool\Factory\ToolFactory;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;

/**
 * Class AbstractDynamicToolProvider
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractDynamicToolProvider extends AbstractProvider implements DynamicToolProvider
{
    /**
     * @var \ILIAS\GlobalScreen\Identification\ToolIdentificationProviderInterface
     */
    protected $identification_provider;

    /**
     * @var \ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection
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
        $this->identification_provider = $this->globalScreen()->identification()->tool($this);
    }
}
