<?php

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\MetaBar\Provider\AbstractStaticMetaBarProvider;
use ILIAS\GlobalScreen\Scope\MetaBar\Provider\StaticMetaBarProvider;

/**
 * Class ilMMCustomTopBarProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMCustomTopBarProvider extends AbstractStaticMetaBarProvider implements StaticMetaBarProvider
{

    /**
     * @inheritDoc
     */
    public function getMetaBarItems() : array
    {
        $f = $this->dic->ui()->factory();
        $txt = function ($id) {
            return $this->dic->language()->txt($id);
        };
        $mb = $this->globalScreen()->metaBar();
        $id = function ($id) : IdentificationInterface {
            return $this->if->identifier($id);
        };

        $item[] = $mb->topLegacyItem($id('help'))
            ->withLegacyContent($f->legacy("NOT PROVIDED"))
            ->withSymbol($f->symbol()->glyph()->help())
            ->withTitle("Help")
            ->withPosition(2);

        $item[] = $mb->topLegacyItem($id('notifications'))
            ->withLegacyContent($f->legacy("NOT PROVIDED"))
            ->withSymbol($f->symbol()->glyph()->notification()->withCounter($f->counter()->novelty(3)))
            ->withTitle("Notifications")
            ->withVisibilityCallable(
                function () {
                    return !$this->dic->user()->isAnonymous();
                }
            )
            ->withPosition(3);


        return $item;
    }
}
