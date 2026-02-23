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

namespace ILIAS\LegalDocuments\GlobalScreen;

use ILIAS\Data\URI;
use ILIAS\LegalDocuments\Conductor;
use ILIAS\DI\Container;
use ILIAS\GlobalScreen\Scope\MetaBar\Provider\AbstractStaticFooterProvider;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\GlobalScreen\Identification\IdentificationInterface as Id;
use ilFooterStandardGroupsProvider;
use ilFooterStandardGroups;
use Closure;
use ILIAS\UI\Component\Modal\Modal;
use ILIAS\GlobalScreen\Scope\Footer\Factory\isItem;

class FooterProvider extends AbstractStaticFooterProvider
{
    private readonly Id $parent_id;
    private readonly Conductor $ldoc;

    public function __construct(Container $dic)
    {
        parent::__construct($dic);
        $this->parent_id = (new ilFooterStandardGroupsProvider($dic))->getIdentificationFor(ilFooterStandardGroups::LEGAL_INFORMATION);
        $this->ldoc = $dic['legalDocuments'];
    }

    public function getGroups(): array
    {
        return [];
    }

    public function getEntries(): array
    {
        return array_map(
            fn(array $args) => $this->item(...$args),
            $this->ldoc->modifyFooter($this->collect([]))()
        );
    }

    private function collect(array $items): Closure
    {
        return function (...$args) use ($items) {
            if ($args === []) {
                return $items;
            }
            return $this->collect(array_merge($items, [$args]));
        };
    }

    private function item(string $id, string $title, ?object $obj): isItem
    {
        $false = fn() => false;
        $id = $this->id_factory->identifier($id);

        $item = match (true) {
            $obj === null => $this->item_factory->link($id, $title)
                                  ->withAction(new URI(ILIAS_HTTP_PATH))
                                  ->withAvailableCallable($false)
                                  ->withVisibilityCallable($false),
            $obj instanceof Modal => $this->item_factory->modal($id, $title, $obj),
            default => $this->item_factory->link($id, $title)->withAction($obj),
        };

        return $item->withParent($this->parent_id);
    }
}
