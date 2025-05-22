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

namespace ILIAS\GlobalScreen\Client;

require_once(__DIR__ . '/../vendor/composer/vendor/autoload.php');
require_once(__DIR__ . '/../artifacts/bootstrap_default.php');

use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\Hasher;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\Lost;

class ContentRenderer
{
    use Hasher;

    public function run(): void
    {
        global $DIC;

        $GS = $DIC->globalScreen();

        $GS->collector()->mainmenu()->collectStructure();
        $GS->collector()->mainmenu()->filterItemsByVisibilty(true);
        $GS->collector()->mainmenu()->prepareItemsForUIRepresentation();

        $unhash = $this->unhash($_GET['item']);
        $identification = $GS->identification()->fromSerializedIdentification($unhash);
        $item = $GS->collector()->mainmenu()->getSingleItemFromFilter($identification);

        if ($item instanceof Lost) {
            $f = $DIC->ui()->factory();
            $component = $f->button()->bulky(
                $f->symbol()->glyph()->login(),
                $DIC->language()->txt('login'),
                'login.php?client_id=' . rawurlencode(CLIENT_ID) . '&cmd=force_login&lang=' . $DIC->user()->getCurrentLanguage()
            );
        } else {
            $component = $item->getTypeInformation()->getRenderer()->getComponentForItem($item, true);
        }
        echo $DIC->ui()->renderer()->renderAsync($component);
    }
}

if (php_sapi_name() !== 'cli') {
    \ilContext::init(\ilContext::CONTEXT_WAC);
    entry_point('ILIAS Legacy Initialisation Adapter');
    (new ContentRenderer())->run();
}
