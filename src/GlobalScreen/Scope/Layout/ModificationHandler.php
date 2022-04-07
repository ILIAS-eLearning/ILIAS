<?php namespace ILIAS\GlobalScreen\Scope\Layout;

use Closure;
use ILIAS\GlobalScreen\Scope\Layout\Builder\DecoratedPageBuilder;
use ILIAS\GlobalScreen\Scope\Layout\Builder\StandardPageBuilder;
use ILIAS\GlobalScreen\Scope\Layout\Provider\PagePart\DecoratedPagePartProvider;
use ILIAS\GlobalScreen\Scope\Layout\Provider\PagePart\PagePartProvider;
use ILIAS\GlobalScreen\Scope\Layout\Provider\PagePart\StandardPagePartProvider;
use ILIAS\GlobalScreen\SingletonTrait;
use ILIAS\UI\Component\Breadcrumbs\Breadcrumbs;
use ILIAS\UI\Component\Image\Image;
use ILIAS\UI\Component\Layout\Page\Page;
use ILIAS\UI\Component\Legacy\Legacy;
use ILIAS\UI\Component\MainControls\Footer;
use ILIAS\UI\Component\MainControls\MainBar;
use ILIAS\UI\Component\MainControls\MetaBar;

/**
 * Class ModifierServices
 *
 * @internal
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ModificationHandler
{
    use SingletonTrait;
    /**
     * @var DecoratedPageBuilder
     */
    private $current_page_builder;
    /**
     * @var PagePartProvider
     */
    private $current_page_part_provider;


    /**
     * LayoutServices constructor.
     */
    public function __construct()
    {
        $this->current_page_builder = new StandardPageBuilder();
        $this->current_page_part_provider = new StandardPagePartProvider();
    }




    //
    // Modifiers
    //
    /**
     * @param Closure $closure_returning_page
     *
     * You can pass a Closure which will get the Page as the first argument and
     * MUST return a Page as well.
     *
     * Have a look at the README.md for an example.
     *
     */
    public function modifyPageWithClosure(Closure $closure_returning_page)
    {
        $this->current_page_builder = new DecoratedPageBuilder($this->current_page_builder, $closure_returning_page);
    }


    /**
     * @param Closure $closure_returning_content
     *
     * Have a look at the README.md for an example.
     */
    public function modifyContentWithClosure(Closure $closure_returning_content)
    {
        $this->replaceWithAutoWiredInstance(Legacy::class, $closure_returning_content);
    }


    /**
     * @param Closure $closure_returning_main_bar
     *
     * Have a look at the README.md for an example.
     */
    public function modifyMainBarWithClosure(Closure $closure_returning_main_bar)
    {
        $this->replaceWithAutoWiredInstance(MainBar::class, $closure_returning_main_bar);
    }


    /**
     * @param Closure $closure_returning_meta_bar
     *
     * Have a look at the README.md for an example.
     */
    public function modifyMetaBarWithClosure(Closure $closure_returning_meta_bar)
    {
        $this->replaceWithAutoWiredInstance(MetaBar::class, $closure_returning_meta_bar);
    }

    /**
     * @param Closure $closure_returning_image
     * Have a look at the README.md for an example.
     */
    public function modifyLogoWithClosure(Closure $closure_returning_image)
    {
        $this->replaceWithAutoWiredInstance(DecoratedPagePartProvider::PURPOSE_LOGO, $closure_returning_image);
    }

    public function modifyResponsiveLogoWithClosure(Closure $closure_returning_image)
    {
        $this->replaceWithAutoWiredInstance(
            DecoratedPagePartProvider::PURPOSE_RESPONSIVE_LOGO,
            $closure_returning_image
        );
    }

    /**
     * @param Closure $closure_returning_breadcrumbs
     * Have a look at the README.md for an example.
     */
    public function modifyBreadCrumbsWithClosure(Closure $closure_returning_breadcrumbs)
    {
        $this->replaceWithAutoWiredInstance(Breadcrumbs::class, $closure_returning_breadcrumbs);
    }


    /**
     * @param Closure $closure_returning_page
     */
    public function modifyPageBuilderWithClosure(Closure $closure_returning_page) : void
    {
        $this->current_page_builder = new DecoratedPageBuilder($this->current_page_builder, $closure_returning_page);
    }


    /**
     * @param Closure $closure_returning_footer
     */
    public function modifyFooterWithClosure(Closure $closure_returning_footer) : void
    {
        $this->replaceWithAutoWiredInstance(Footer::class, $closure_returning_footer);
    }


    /**
     * @return Page
     */
    public function getPageWithPagePartProviders() : Page
    {
        return $this->current_page_builder->build($this->current_page_part_provider);
    }


    public function modifyTitleWithClosure(Closure $closure_returning_title) : void
    {
        $this->replaceWithAutoWiredInstance(
            DecoratedPagePartProvider::PURPOSE_TITLE,
            $closure_returning_title
        );
    }
    public function modifyShortTitleWithClosure(Closure $closure_returning_short_title) : void
    {
        $this->replaceWithAutoWiredInstance(
            DecoratedPagePartProvider::PURPOSE_SHORTTITLE,
            $closure_returning_short_title
        );
    }
    public function modifyViewTitleWithClosure(Closure $closure_returning_view_title) : void
    {
        $this->replaceWithAutoWiredInstance(
            DecoratedPagePartProvider::PURPOSE_VIEWTITLE,
            $closure_returning_view_title
        );
    }



    /**
     * @param string  $interface
     * @param Closure $closure
     */
    private function replaceWithAutoWiredInstance(string $interface, Closure $closure) : void
    {
        $this->current_page_part_provider = new DecoratedPagePartProvider($this->current_page_part_provider, $closure, $interface);
    }
}
