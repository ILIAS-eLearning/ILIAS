<?php namespace ILIAS\GlobalScreen\Client;

use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\Hasher;
use ILIAS\GlobalScreen\Scope\Tool\Factory\isToolItem;
use ILIAS\DI\Container;

class CallbackHandler
{
    use Hasher;

    public function run()
    {
        \ilInitialisation::initILIAS();
        global $DIC;
        $DIC->ctrl()->setTargetScript("/ilias.php");
        $GS = $DIC->globalScreen();
        $GS->collector()->tool()->collectOnce();
        $unhash = $this->unhash($_GET['item']);
        $identification = $GS->identification()->fromSerializedIdentification($unhash);
        $item = $GS->collector()->tool()->getSingleItem($identification);
        /**
         * @var $item isToolItem
         */
        $callback = $item->hasCloseCallback() ? $item->getCloseCallback() : static function () {
        };
        $callback();
    }
}
