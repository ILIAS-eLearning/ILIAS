<?php namespace ILIAS\GlobalScreen\Scope\Layout;

use Closure;
use ILIAS\GlobalScreen\Scope\Layout\Builder\DecoratedPageBuilder;
use ILIAS\GlobalScreen\Scope\Layout\Builder\PageBuilder;
use ILIAS\GlobalScreen\Scope\Layout\Builder\StandardPageBuilder;
use ILIAS\GlobalScreen\Scope\Layout\Modifier\BreadCrumbsModifier;
use ILIAS\GlobalScreen\Scope\Layout\Modifier\ContentModifier;
use ILIAS\GlobalScreen\Scope\Layout\Modifier\LogoModifier;
use ILIAS\GlobalScreen\Scope\Layout\Modifier\MainBarModifier;
use ILIAS\GlobalScreen\Scope\Layout\Modifier\MetaBarModifier;
use ILIAS\GlobalScreen\Scope\Layout\Provider\DecoratedPagePartProvider;
use ILIAS\GlobalScreen\Scope\Layout\Provider\InstancePagePartProvider;
use ILIAS\GlobalScreen\Scope\Layout\Provider\PagePartProvider;
use ILIAS\GlobalScreen\Scope\Layout\Provider\StandardPagePartProvider;
use ILIAS\UI\Component\Breadcrumbs\Breadcrumbs;
use ILIAS\UI\Component\Image\Image;
use ILIAS\UI\Component\Layout\Page\Page;
use ILIAS\UI\Component\Legacy\Legacy;
use ILIAS\UI\Component\MainControls\MainBar;
use ILIAS\UI\Component\MainControls\MetaBar;
use LogicException;
use ReflectionFunction;

/**
 * Class ModifierServices
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ModifierServices
{

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
        $this->checkClosure($closure_returning_page, Page::class, Page::class);
        $this->current_page_builder = new DecoratedPageBuilder($this->current_page_builder, $closure_returning_page);
    }


    /**
     * @param PageBuilder $page_builder
     *
     * Have a look at the README.md for an example.
     */
    public function modifyPageWithInstance(PageBuilder $page_builder)
    {
        $this->current_page_builder = $page_builder;
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
     * @param ContentModifier $modifier
     *
     * Have a look at the README.md for an example.
     */
    public function modifyContentWithInstance(ContentModifier $modifier)
    {
        $ppp = new InstancePagePartProvider($this->current_page_part_provider);
        $ppp->setContentProvider($modifier);

        $this->current_page_part_provider = $ppp;
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
     * @param MainBarModifier $modifier
     *
     * Have a look at the README.md for an example.
     */
    public function modifyMainBarWithInstance(MainBarModifier $modifier)
    {
        $ppp = new InstancePagePartProvider($this->current_page_part_provider);
        $ppp->setMainBarProvider($modifier);

        $this->current_page_part_provider = $ppp;
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
     * @param MetaBarModifier $modifier
     *
     * Have a look at the README.md for an example.
     */
    public function modifyMetaBarWithInstance(MetaBarModifier $modifier)
    {
        $ppp = new InstancePagePartProvider($this->current_page_part_provider);
        $ppp->setMetaBarProvider($modifier);

        $this->current_page_part_provider = $ppp;
    }


    /**
     * @param Closure $closure_returning_image
     *
     * Have a look at the README.md for an example.
     */
    public function modifyLogoWithClosure(Closure $closure_returning_image)
    {
        $this->replaceWithAutoWiredInstance(Image::class, $closure_returning_image);
    }


    /**
     * @param LogoModifier $modifier
     *
     * Have a look at the README.md for an example.
     */
    public function modifyLogoWithInstance(LogoModifier $modifier)
    {
        $ppp = new InstancePagePartProvider($this->current_page_part_provider);
        $ppp->setLogoProvider($modifier);

        $this->current_page_part_provider = $ppp;
    }


    /**
     * @param Closure $closure_returning_breadcrumbs
     *
     * Have a look at the README.md for an example.
     */
    public function modifyBreadCrumbsWithClosure(Closure $closure_returning_breadcrumbs)
    {
        $this->replaceWithAutoWiredInstance(Breadcrumbs::class, $closure_returning_breadcrumbs);
    }


    /**
     * @param BreadCrumbsModifier $modifier
     *
     * Have a look at the README.md for an example.
     */
    public function modifyBreadCrumbsWithInstance(BreadCrumbsModifier $modifier)
    {
        $ppp = new InstancePagePartProvider($this->current_page_part_provider);
        $ppp->setBreadCrumbsProvider($modifier);

        $this->current_page_part_provider = $ppp;
    }


    /**
     * @return Page
     */
    public function getFinalPage() : Page
    {
        return $this->current_page_builder->build($this->current_page_part_provider);
    }


    /**
     * @param Closure     $closure_returning_page_builder
     *
     * @param string      $return_type
     * @param string|null $first_argument_type
     */
    private function checkClosure(Closure $closure_returning_page_builder, string $return_type, string $first_argument_type = null) : void
    {
        try {
            $r = new ReflectionFunction($closure_returning_page_builder);

            if ($first_argument_type !== null) {
                if (!isset($r->getParameters()[0])
                    || !$r->getParameters()[0]->hasType()
                    || $r->getParameters()[0]->getType()->getName() !== $first_argument_type
                ) {
                    throw new LogicException("The Closure MUST has a first parameter of " . $first_argument_type);
                }
            }

            if (!$r->hasReturnType()
                || $r->getReturnType()->getName() !== $return_type
            ) {
                throw new LogicException("The Closure MUST return a " . $return_type . ", " . $r->getReturnType()->getName() . " given");
            }
        } catch (\ReflectionException $e) {
            throw new LogicException("Unknown Exception while checking Closure: " . $e->getMessage());
        }
    }


    /**
     * @param string  $interface
     * @param Closure $closure
     */
    private function replaceWithAutoWiredInstance(string $interface, Closure $closure) : void
    {
        $this->checkClosure($closure, $interface, $interface);

        $this->current_page_part_provider = new DecoratedPagePartProvider($this->current_page_part_provider, $closure, $interface);
    }
}
