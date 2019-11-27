<?php namespace ILIAS\GlobalScreen\Client;

require_once('./libs/composer/vendor/autoload.php');

use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\Hasher;

class ContentRenderer
{

    use Hasher;


    public function run()
    {
        \ilInitialisation::initILIAS();
        global $DIC;
        $DIC->ctrl()->setTargetScript("/ilias.php");

        $GS = $DIC->globalScreen();

        $GS->collector()->mainmenu()->collectStructure();
        $GS->collector()->mainmenu()->prepareItemsForUIRepresentation();

        $unhash = $this->unhash($_GET['item']);
        $identification = $GS->identification()->fromSerializedIdentification($unhash);
        $item = $GS->collector()->mainmenu()->getSingleItem($identification);

        $component = $item->getTypeInformation()->getRenderer()->getComponentForItem($item, true);

        echo $DIC->ui()->renderer()->renderAsync($component);
    }
}

if (php_sapi_name() !== 'cli') {
    (new ContentRenderer())->run();
}

