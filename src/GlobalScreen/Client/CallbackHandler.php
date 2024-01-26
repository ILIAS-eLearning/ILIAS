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
namespace ILIAS\GlobalScreen\Client;

use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\Hasher;
use ILIAS\GlobalScreen\Scope\Tool\Factory\isToolItem;
use ilInitialisation;
use ILIAS\HTTP\Wrapper\WrapperFactory;
use ILIAS\Refinery\Factory;
use ILIAS\GlobalScreen\Identification\IdentificationInterface;

class CallbackHandler
{
    use Hasher;

    private const TARGET_SCRIPT = "/ilias.php";
    public const KEY_ITEM = 'item';

    /**
     * @var \ILIAS\HTTP\Wrapper\WrapperFactory
     */
    protected $wrapper;
    /**
     * @var \ILIAS\Refinery\Factory
     */
    protected $refinery;
    /**
     * @var \ilCtrlInterface
     */
    protected $ctrl;
    /**
     * @var \ILIAS\GlobalScreen\Services
     */
    protected $global_screen;

    public function __construct()
    {
        ilInitialisation::initILIAS();
        global $DIC;
        $this->ctrl = $DIC->ctrl();
        $this->wrapper = $DIC->http()->wrapper();
        $this->refinery = $DIC->refinery();
        $this->global_screen = $DIC->globalScreen();
    }

    public function run() : void
    {
        $this->ctrl->setTargetScript(self::TARGET_SCRIPT);

        $this->global_screen->collector()
                            ->tool()
                            ->collectOnce();

        $item = $this->global_screen->collector()
                                    ->tool()
                                    ->getSingleItem($this->getIdentification());

        if ($item instanceof isToolItem) {
            $callback = $this->resolveCallback($item);
            $callback();
        }
    }

    private function resolveCallback(isToolItem $item) : \Closure
    {
        return $item->hasCloseCallback()
            ? $item->getCloseCallback()
            : static function () : void {
            };
    }

    private function getIdentification() : IdentificationInterface
    {
        $hashed = $this->wrapper->query()->has(self::KEY_ITEM)
            ? $this->wrapper->query()->retrieve(self::KEY_ITEM, $this->refinery->to()->string())
            : '';

        $unhashed = $this->unhash($hashed);

        return $this->global_screen->identification()->fromSerializedIdentification($unhashed);
    }
}
