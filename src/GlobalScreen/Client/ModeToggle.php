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

use ILIAS\HTTP\Wrapper\WrapperFactory;
use ILIAS\Refinery\Factory;
use ILIAS\HTTP\Cookies\CookieFactoryImpl;

/**
 * Class ModeToggle
 * This is just for testing!!! And will be removed after
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ModeToggle
{
    public const GS_MODE = 'gs_mode';
    public const MODE1 = "all";
    public const MODE2 = "none";

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
    /**
     * @var \ILIAS\HTTP\Services
     */
    protected $http;

    public function __construct()
    {
        \ilInitialisation::initILIAS();
        global $DIC;
        $this->ctrl = $DIC->ctrl();
        $this->wrapper = $DIC->http()->wrapper();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->global_screen = $DIC->globalScreen();
    }

    public function getMode() : string
    {
        return $this->wrapper->cookie()->has(self::GS_MODE)
            ? $this->wrapper->cookie()->retrieve(self::GS_MODE, $this->refinery->to()->string())
            : self::MODE1;
    }

    public function saveStateOfAll() : bool
    {
        return $this->getMode() == ItemState::LEVEL_OF_TOOL;
    }

    public function toggle() : void
    {
        $current_mode = $this->getMode();
        $new_mode = $current_mode == self::MODE1 ? self::MODE2 : self::MODE1;
        $cookie_factory = new CookieFactoryImpl();
        $cookie = $cookie_factory->create(self::GS_MODE, $new_mode)
                                 ->withExpires(time() + 3600);
        $this->http->cookieJar()->with($cookie);
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    }
}
