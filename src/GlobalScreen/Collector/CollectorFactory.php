<?php namespace ILIAS\GlobalScreen\Collector;

use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Information\ItemInformation;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\MainMenuMainCollector;

/**
 * Class CollectorFactory
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class CollectorFactory
{

    const SCOPE_MAINBAR = 'mainmenu';
    /**
     * @var array
     */
    protected static $instances = [];


    /**
     * @param array                $providers
     * @param ItemInformation|null $information
     *
     * @return MainMenuMainCollector
     * @throws \Throwable
     */
    public function mainmenu(array $providers, ItemInformation $information = null) : MainMenuMainCollector
    {
        if (!isset(self::$instances[self::SCOPE_MAINBAR])) {
            self::$instances[self::SCOPE_MAINBAR] = new MainMenuMainCollector($providers, $information);
        }

        return self::$instances[self::SCOPE_MAINBAR];
    }
}
