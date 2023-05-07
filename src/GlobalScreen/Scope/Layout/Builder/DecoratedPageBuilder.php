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
/** @noinspection PhpPropertyOnlyWrittenInspection */
namespace ILIAS\GlobalScreen\Scope\Layout\Builder;

use Closure;
use ILIAS\GlobalScreen\Scope\Layout\Provider\PagePart\PagePartProvider;
use ILIAS\UI\Component\Layout\Page\Page;

/**
 * Interface DecoratedPageBuilder
 * @internal
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class DecoratedPageBuilder implements PageBuilder
{
    /**
     * @var \ILIAS\GlobalScreen\Scope\Layout\Builder\PageBuilder
     */
    private $original;
    /**
     * @var \Closure
     */
    private $deco;

    /**
     * DecoratedPageBuilder constructor.
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
        return $deco($parts);
    }
}
