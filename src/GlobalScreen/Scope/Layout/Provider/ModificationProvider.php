<?php namespace ILIAS\GlobalScreen\Scope\Layout\Provider;

use ILIAS\GlobalScreen\Provider\Provider;
use ILIAS\GlobalScreen\Scope\Layout\Factory\BreadCrumbsModification;
use ILIAS\GlobalScreen\Scope\Layout\Factory\ContentModification;
use ILIAS\GlobalScreen\Scope\Layout\Factory\FooterModification;
use ILIAS\GlobalScreen\Scope\Layout\Factory\LogoModification;
use ILIAS\GlobalScreen\Scope\Layout\Factory\MainBarModification;
use ILIAS\GlobalScreen\Scope\Layout\Factory\MetaBarModification;
use ILIAS\GlobalScreen\Scope\Layout\Factory\PageBuilderModification;
use ILIAS\GlobalScreen\ScreenContext\ScreenContextAwareProvider;
use ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts;
use ILIAS\GlobalScreen\Scope\Layout\Factory\TitleModification;
use ILIAS\GlobalScreen\Scope\Layout\Factory\ShortTitleModification;
use ILIAS\GlobalScreen\Scope\Layout\Factory\ViewTitleModification;

/**
 * Interface ModificationProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ModificationProvider extends Provider, ScreenContextAwareProvider
{

    /**
     * @param CalledContexts $screen_context_stack
     *
     * @return ContentModification
     */
    public function getContentModification(CalledContexts $screen_context_stack) : ?ContentModification;

    /**
     * @param CalledContexts $screen_context_stack
     * @return LogoModification|null
     */
    public function getLogoModification(CalledContexts $screen_context_stack) : ?LogoModification;

    public function getResponsiveLogoModification(CalledContexts $screen_context_stack) : ?LogoModification;

    /**
     * @param CalledContexts $screen_context_stack
     * @return MainBarModification|null
     */
    public function getMainBarModification(CalledContexts $screen_context_stack) : ?MainBarModification;


    /**
     * @param CalledContexts $screen_context_stack
     *
     * @return MetaBarModification|null
     */
    public function getMetaBarModification(CalledContexts $screen_context_stack) : ?MetaBarModification;


    /**
     * @param CalledContexts $screen_context_stack
     *
     * @return BreadCrumbsModification|null
     */
    public function getBreadCrumbsModification(CalledContexts $screen_context_stack) : ?BreadCrumbsModification;


    /**
     * @param CalledContexts $screen_context_stack
     *
     * @return FooterModification|null
     */
    public function getFooterModification(CalledContexts $screen_context_stack) : ?FooterModification;


    /**
     * @param CalledContexts $screen_context_stack
     *
     * @return PageBuilderModification|null
     */
    public function getPageBuilderDecorator(CalledContexts $screen_context_stack) : ?PageBuilderModification;

    /**
     * @param CalledContexts $screen_context_stack
     *
     * @return TitleModification|null
     */
    public function getTitleModification(CalledContexts $screen_context_stack) : ?TitleModification;

    /**
     * @param CalledContexts $screen_context_stack
     *
     * @return ShortTitleModification|null
     */
    public function getShortTitleModification(CalledContexts $screen_context_stack) : ?ShortTitleModification;

    /**
     * @param CalledContexts $screen_context_stack
     *
     * @return ViewTitleModification|null
     */
    public function getViewTitleModification(CalledContexts $screen_context_stack) : ?ViewTitleModification;
}
