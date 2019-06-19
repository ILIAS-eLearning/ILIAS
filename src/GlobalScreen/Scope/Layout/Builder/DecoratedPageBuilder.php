<?php namespace ILIAS\GlobalScreen\Scope\Layout\Builder;

use Closure;
use ILIAS\GlobalScreen\Scope\Layout\Provider\PagePartProvider;
use ILIAS\UI\Component\Layout\Page\Page;

/**
 * Interface DecoratedPageBuilder
 *
 * @internal
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class DecoratedPageBuilder implements PageBuilder
{

    /**
     * @var PageBuilder
     */
    private $original;
    /**
     * @var Closure
     */
    private $deco;


    /**
     * DecoratedPageBuilder constructor.
     *
     * @param PageBuilder $original
     * @param Closure     $deco
     */
    public function __construct(PageBuilder $original, Closure $deco)
    {
        $this->original = $original;
        $this->deco = $deco;
    }


    /**
     * @inheritDoc
     */
    public function build(PagePartProvider $parts) : Page
    {
        $deco = $this->deco;

        return $deco($this->original->build($parts));
    }
}
