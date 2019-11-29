<?php

use ILIAS\MainMenu\Storage\Services;

/**
 * Class ilMMIconGUI
 *
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 *
 * @ilCtrl_isCalledBy ilMMIconGUI: ilUIPluginRouterGUI
 */
class ilMMIconGUI
{

    public const ICON_ID = 'icon_id';
    /**
     * @var \ILIAS\DI\HTTPServices
     */
    private $http;
    /**
     * @var Services
     */
    private $storage;


    /**
     * ilUIDemoFileUploadHandlerGUI constructor.
     */
    public function __construct()
    {
        global $DIC;
        $this->http = $DIC->http();
        $this->storage = new Services();
    }


    public function executeCommand() : void
    {
        $i = $this->storage->find($this->http->request()->getQueryParams()[self::ICON_ID]);
        if ($i !== null) {
            $this->storage->inline($i)->run();
        }
    }
}
