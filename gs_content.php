<?php

namespace ILIAS\GlobalScreen\Client;

require_once('./libs/composer/vendor/autoload.php');

use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\Hasher;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\Lost;

class ContentRenderer
{
    use Hasher;

    public function run()
    {
        \ilContext::init(\ilContext::CONTEXT_WAC);
        \ilInitialisation::initILIAS();
        global $DIC;

        $GS = $DIC->globalScreen();

        $GS->collector()->mainmenu()->collectStructure();
        $GS->collector()->mainmenu()->filterItemsByVisibilty(true);
        $GS->collector()->mainmenu()->prepareItemsForUIRepresentation();

        $unhash         = $this->unhash($_GET['item']);
        $identification = $GS->identification()->fromSerializedIdentification($unhash);
        $item           = $GS->collector()->mainmenu()->getSingleItemFromFilter($identification);

        if ($item instanceof Lost) {
            $f         = $DIC->ui()->factory();
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
    (new ContentRenderer())->run();
}
