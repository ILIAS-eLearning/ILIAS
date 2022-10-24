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

use ILIAS\BackgroundTasks\Implementation\Tasks\UserInteraction\UserInteractionOption;
use ILIAS\Modules\OrgUnit\ARHelper\DIC;

/**
 * Class ilBTControllerGUI
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilBTControllerGUI implements ilCtrlBaseClassInterface
{
    use DIC;
    public const FROM_URL = 'from_url';
    public const OBSERVER_ID = 'observer_id';
    public const SELECTED_OPTION = 'selected_option';
    public const CMD_ABORT = 'abortBucket';
    public const CMD_REMOVE = 'abortBucket';
    public const CMD_USER_INTERACTION = 'userInteraction';
    public const IS_ASYNC = 'bt_task_is_async';
    public const CMD_GET_REPLACEMENT_ITEM = "getAsyncReplacementItem";


    public function executeCommand(): void
    {
        $cmd = $this->ctrl()->getCmd();
        switch ($cmd) {
            case self::CMD_GET_REPLACEMENT_ITEM:
                $this->getAsyncReplacementItem();
                break;
            case self::CMD_USER_INTERACTION:
                $this->userInteraction();
                break;
            case self::CMD_ABORT:
            case self::CMD_REMOVE:
                $this->abortBucket();
                break;
            default:
                break;
        }
    }


    protected function userInteraction(): void
    {
        $observer_id = (int) $this->http()->request()->getQueryParams()[self::OBSERVER_ID];
        $selected_option = $this->http()->request()->getQueryParams()[self::SELECTED_OPTION];
        $from_url = $this->getFromURL();

        $observer = $this->dic()->backgroundTasks()->persistence()->loadBucket($observer_id);
        $option = new UserInteractionOption("", $selected_option);
        $this->dic()->backgroundTasks()->taskManager()->continueTask($observer, $option);
        if ($this->http()->request()->getQueryParams()[self::IS_ASYNC] === "true") {
            exit;
        }
        $this->ctrl()->redirectToURL($from_url);
    }


    protected function abortBucket(): void
    {
        $observer_id = (int) $this->http()->request()->getQueryParams()[self::OBSERVER_ID];
        $from_url = $this->getFromURL();

        $bucket = $this->dic()->backgroundTasks()->persistence()->loadBucket($observer_id);

        $this->dic()->backgroundTasks()->taskManager()->quitBucket($bucket);
        if ($this->http()->request()->getQueryParams()[self::IS_ASYNC] === "true") {
            exit;
        }
        $this->ctrl()->redirectToURL($from_url);
    }


    /**
     * Loads one single aggregate notification item representing a button async
     * to replace an existing one.
     */
    protected function getAsyncReplacementItem(): void
    {
        $observer_id = (int) $this->http()->request()->getQueryParams()[self::OBSERVER_ID];
        $bucket = $this->dic()->backgroundTasks()->persistence()->loadBucket($observer_id);

        $item_source = new ilBTPopOverGUI($this->dic());
        $this->dic()->language()->loadLanguageModule('background_tasks');
        $item = $item_source->getItemForObserver($bucket);
        echo $this->dic()->ui()->renderer()->renderAsync($item);
        exit;
    }


    protected function getFromURL(): string
    {
        return self::unhash($this->http()->request()->getQueryParams()[self::FROM_URL]);
    }


    /**
     * @param $url
     */
    public static function hash($url): string
    {
        return base64_encode($url);
    }


    /**
     * @param $url
     */
    public static function unhash($url): string
    {
        return base64_decode($url);
    }
}
