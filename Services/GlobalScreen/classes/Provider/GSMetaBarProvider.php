<?php

namespace ILIAS\GlobalScreen\Provider;

use ILIAS\GlobalScreen\Client\ModeToggle;
use ILIAS\GlobalScreen\Scope\MetaBar\Provider\AbstractStaticMetaBarProvider;

/**
 * Class GSMetaBarProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class GSMetaBarProvider extends AbstractStaticMetaBarProvider
{

    /**
     * @inheritDoc
     */
    public function getMetaBarItems() : array
    {
        // REMOVE AFTER TESTING
        $t = new ModeToggle();
        $identification = $this->if->identifier('toggle');
        $symbol = $this->dic->ui()->factory()->symbol()->glyph()->settings();

        $top_item = $this->meta_bar->topLegacyItem($identification)
            ->withTitle("Toggle")
            ->withLegacyContent($this->dic->ui()
                ->factory()
                ->legacy("Zum Testen kann hier umgestellt werden, <br>ob Slates in der MainBar offen bleiben sollen oder nicht. <br><br>Aktueller Modus: {$t->getMode()}<br><br>(none: keine, all: alle typen)<br><br></br>Umschalten: <a  class='button btn btn-default' href='./src/GlobalScreen/Client/toggle.php'>Hier klicken...</a>"))
            ->withSymbol($symbol);

        return [$top_item];
        // END REMOVE
    }
}
