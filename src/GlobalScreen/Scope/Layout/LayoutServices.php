<?php namespace ILIAS\GlobalScreen\Scope\Layout;

use Closure;
use ILIAS\GlobalScreen\Scope\Layout\Builder\DecoratedPageBuilder;
use ILIAS\GlobalScreen\Scope\Layout\Builder\StandardPageBuilder;
use ILIAS\GlobalScreen\Scope\Layout\MetaContent\MetaContent;
use ILIAS\GlobalScreen\Scope\Layout\Provider\DecoratedPagePartProvider;
use ILIAS\GlobalScreen\Scope\Layout\Provider\PagePartProvider;
use ILIAS\GlobalScreen\Scope\Layout\Provider\StandardPagePartProvider;
use ILIAS\UI\Component\Image\Image;
use ILIAS\UI\Component\Layout\Page\Page;
use ILIAS\UI\Component\Legacy\Legacy;
use ILIAS\UI\Component\MainControls\MainBar;
use ILIAS\UI\Component\MainControls\MetaBar;
use LogicException;
use ReflectionFunction;

/**
 * Class LayoutServices
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class LayoutServices
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
     * @var MetaContent
     */
    private $meta_content;


    /**
     * LayoutServices constructor.
     */
    public function __construct()
    {
        $this->current_page_builder = new StandardPageBuilder();
        $this->current_page_part_provider = new StandardPagePartProvider();
        $this->meta_content = new MetaContent();
    }


    //
    // Modifiers
    //
    /**
     * @param Closure $closure_returning_page
     */
    public function modifyPage(Closure $closure_returning_page)
    {
        $this->checkClosure($closure_returning_page, Page::class, Page::class);
        $this->current_page_builder = new DecoratedPageBuilder($this->current_page_builder, $closure_returning_page);
    }


    /**
     * @param Closure $closure_returning_content
     */
    public function modifyContent(Closure $closure_returning_content)
    {
        $this->replaceWithAutoWiredInstance(Legacy::class, $closure_returning_content);
    }


    /**
     * @param Closure $closure_returning_main_bar
     */
    public function modifyMainBar(Closure $closure_returning_main_bar)
    {
        $this->replaceWithAutoWiredInstance(MainBar::class, $closure_returning_main_bar);
    }


    /**
     * @param Closure $closure_returning_meta_bar
     */
    public function modifyMetaBar(Closure $closure_returning_meta_bar)
    {
        $this->replaceWithAutoWiredInstance(MetaBar::class, $closure_returning_meta_bar);
    }


    /**
     * @param Closure $closure_returning_image
     */
    public function modifyIcon(Closure $closure_returning_image)
    {
        $this->replaceWithAutoWiredInstance(Image::class, $closure_returning_image);
    }




    //
    // GETTERS
    //

    /**
     * @return Page
     */
    public function getFinalPage() : Page
    {
        return $this->current_page_builder->build($this->current_page_part_provider);
    }


    /**
     * @return MetaContent
     */
    public function getMetaContent() : MetaContent
    {
        return $this->meta_content;
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
