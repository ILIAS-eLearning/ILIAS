<?php namespace ILIAS\GlobalScreen\Scope\Layout\Provider;

use ILIAS\DI\Container;
use ILIAS\GlobalScreen\Provider\AbstractProvider;
use ILIAS\GlobalScreen\Scope\Layout\Factory\BreadCrumbsModification;
use ILIAS\GlobalScreen\Scope\Layout\Factory\ContentModification;
use ILIAS\GlobalScreen\Scope\Layout\Factory\LogoModification;
use ILIAS\GlobalScreen\Scope\Layout\Factory\MainBarModification;
use ILIAS\GlobalScreen\Scope\Layout\Factory\MetaBarModification;
use ILIAS\GlobalScreen\Scope\Layout\Factory\PageBuilderModification;
use ILIAS\GlobalScreen\Scope\Tool\Factory\ToolFactory;
use ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;

/**
 * Class AbstractModificationProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractModificationProvider extends AbstractProvider implements ModificationProvider
{

    /**
     * @var ContextCollection
     */
    protected $context_collection;
    /**
     * @var ToolFactory
     */
    protected $factory;


    /**
     * @inheritDoc
     */
    public function __construct(Container $dic)
    {
        parent::__construct($dic);
        $this->context_collection = $this->globalScreen()->tool()->context()->collection();
        $this->factory = $this->globalScreen()->layout()->factory();
    }


    /**
     * @inheritDoc
     */
    public function getContentModification(CalledContexts $screen_context_stack) : ?ContentModification
    {
        return null;
    }


    /**
     * @inheritDoc
     */
    public function getLogoModification(CalledContexts $screen_context_stack) : ?LogoModification
    {
        return null;
    }


    /**
     * @inheritDoc
     */
    public function getMainBarModification(CalledContexts $screen_context_stack) : ?MainBarModification
    {
        return null;
    }


    /**
     * @inheritDoc
     */
    public function getMetaBarModification(CalledContexts $screen_context_stack) : ?MetaBarModification
    {
        return null;
    }


    /**
     * @inheritDoc
     */
    public function getBreadCrumbsModification(CalledContexts $screen_context_stack) : ?BreadCrumbsModification
    {
        return null;
    }


    /**
     * @inheritDoc
     */
    public function getPageBuilderDecorator(CalledContexts $screen_context_stack) : ?PageBuilderModification
    {
        return null;
    }
}
