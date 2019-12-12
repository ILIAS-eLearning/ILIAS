<?php namespace ILIAS\GlobalScreen\Scope\Layout\Factory;

/**
 * Class ModificationFactory
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ModificationFactory
{

    /**
     * @return ContentModification
     */
    public function content() : ContentModification
    {
        return new ContentModification();
    }


    /**
     * @return LogoModification
     */
    public function logo() : LogoModification
    {
        return new LogoModification;
    }


    /**
     * @return MetaBarModification
     */
    public function metabar() : MetaBarModification
    {
        return new MetaBarModification();
    }


    /**
     * @return MainBarModification
     */
    public function mainbar() : MainBarModification
    {
        return new MainBarModification();
    }


    /**
     * @return BreadCrumbsModification
     */
    public function breadcrumbs() : BreadCrumbsModification
    {
        return new BreadCrumbsModification();
    }


    /**
     * @return PageBuilderModification
     */
    public function page() : PageBuilderModification
    {
        return new PageBuilderModification();
    }


    /**
     * @return FooterModification
     */
    public function footer() : FooterModification
    {
        return new FooterModification();
    }

    public function title() : TitleModification
    {
        return new TitleModification();
    }
    public function short_title() : ShortTitleModification
    {
        return new ShortTitleModification();
    }
    public function view_title() : ViewTitleModification
    {
        return new ViewTitleModification();
    }
}
