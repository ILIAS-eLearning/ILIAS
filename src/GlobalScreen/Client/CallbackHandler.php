<?php namespace ILIAS\GlobalScreen\Client;

use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\Hasher;
use ILIAS\GlobalScreen\Scope\Tool\Factory\isToolItem;
use ilInitialisation;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
class CallbackHandler
{
    use Hasher;
    
    public function run() : void
    {
        ilInitialisation::initILIAS();
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
        $callback = $item->hasCloseCallback() ? $item->getCloseCallback() : static function () : void {
        };
        $callback();
    }
}
