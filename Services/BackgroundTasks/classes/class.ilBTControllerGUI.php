<?php

use ILIAS\BackgroundTasks\Implementation\Tasks\UserInteraction\UserInteractionOption;
use ILIAS\Modules\OrgUnit\ARHelper\DIC;

/**
 * Class ilBTControllerGUI
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilBTControllerGUI
{
    use DIC;
    const FROM_URL = 'from_url';
    const OBSERVER_ID = 'observer_id';
    const SELECTED_OPTION = 'selected_option';
    const REPLACE_SIGNAL = 'replaceSignal';
    const CMD_ABORT = 'abortBucket';
    const CMD_REMOVE = 'abortBucket';
    const CMD_GET_POPOVER_CONTENT = 'getPopoverContent';
    const CMD_USER_INTERACTION = 'userInteraction';


    public function executeCommand()
    {
        switch ($this->ctrl()->getCmdClass()) {
            default:
                $this->performCommand();
        }
    }


    protected function performCommand()
    {
        $cmd = $this->ctrl()->getCmd();
        switch ($cmd) {
            case self::CMD_USER_INTERACTION:
            case self::CMD_GET_POPOVER_CONTENT:
            case self::CMD_ABORT:
            case self::CMD_REMOVE:
                $this->$cmd();
        }
    }


    protected function userInteraction()
    {
        $observer_id = (int) $this->http()->request()->getQueryParams()[self::OBSERVER_ID];
        $selected_option = $this->http()->request()->getQueryParams()[self::SELECTED_OPTION];
        $from_url = $this->getFromURL();

        $observer = $this->dic()->backgroundTasks()->persistence()->loadBucket($observer_id);
        $option = new UserInteractionOption("", $selected_option);
        $this->dic()->backgroundTasks()->taskManager()->continueTask($observer, $option);
        $this->ctrl()->redirectToURL($from_url);
    }


    protected function abortBucket()
    {
        $observer_id = (int) $this->http()->request()->getQueryParams()[self::OBSERVER_ID];
        $from_url = $this->getFromURL();

        $bucket = $this->dic()->backgroundTasks()->persistence()->loadBucket($observer_id);

        $this->dic()->backgroundTasks()->taskManager()->quitBucket($bucket);

        $this->ctrl()->redirectToURL($from_url);
    }


    protected function getPopoverContent()
    {
        /** @var ilBTPopOverGUI $gui */
        $gui = $this->dic()->backgroundTasks()->injector()->createInstance(ilBTPopOverGUI::class);
        $signal_id = $this->http()->request()->getQueryParams()[self::REPLACE_SIGNAL];

        $this->ctrl()
             ->setParameterByClass(ilBTControllerGUI::class, self::REPLACE_SIGNAL, $signal_id);

        $replace_url = $this->ctrl()
                            ->getLinkTargetByClass([ ilBTControllerGUI::class ], self::CMD_GET_POPOVER_CONTENT, "", true);

        echo $this->ui()->renderer()->renderAsync($gui->getPopOverContent($this->user()
                                                                               ->getId(), $this->getFromURL(), $replace_url));
    }


    /**
     * @return string
     */
    protected function getFromURL()
    {
        $from_url = self::unhash($this->http()->request()->getQueryParams()[self::FROM_URL]);

        return $from_url;
    }


    /**
     * @param $url
     *
     * @return string
     */
    public static function hash($url)
    {
        return base64_encode($url);
    }


    /**
     * @param $url
     *
     * @return string
     */
    public static function unhash($url)
    {
        return base64_decode($url);
    }
}
