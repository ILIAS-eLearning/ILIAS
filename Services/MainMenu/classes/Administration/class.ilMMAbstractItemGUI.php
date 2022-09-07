<?php

declare(strict_types=1);

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

use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\Hasher;
use ILIAS\DI\UIServices;
use ILIAS\HTTP\Services;

/**
 * Class ilMMAbstractItemGUI
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class ilMMAbstractItemGUI
{
    use Hasher;
    public const IDENTIFIER = 'identifier';
    public const CMD_FLUSH = 'flush';

    protected UIServices $ui;

    protected Services $http;

    protected ilMMItemRepository $repository;

    protected ilToolbarGUI $toolbar;

    protected ilMMTabHandling $tab_handling;

    protected ilTabsGUI $tabs;

    public ilLanguage $lng;

    protected ilCtrl $ctrl;

    public ilGlobalTemplateInterface $tpl;

    public ilTree $tree;

    protected ilObjMainMenuAccess $access;

    /**
     * ilMMAbstractItemGUI constructor.
     * @param ilMMTabHandling $tab_handling
     * @throws Throwable
     */
    public function __construct(ilMMTabHandling $tab_handling)
    {
        global $DIC;

        $this->repository = new ilMMItemRepository();
        $this->tab_handling = $tab_handling;
        $this->tabs = $DIC['ilTabs'];
        $this->lng = $DIC->language();
        $this->ctrl = $DIC['ilCtrl'];
        $this->tpl = $DIC['tpl'];
        $this->tree = $DIC['tree'];
        $this->toolbar = $DIC['ilToolbar'];
        $this->http = $DIC->http();
        $this->ui = $DIC->ui();
        $this->access = new ilObjMainMenuAccess();

        $this->lng->loadLanguageModule('form');
    }

    /**
     * @param string $standard
     * @param string $delete
     * @return string
     * @throws ilException
     */
    protected function determineCommand(string $standard, string $delete): string
    {
        $this->access->checkAccessAndThrowException('visible,read');
        $cmd = $this->ctrl->getCmd();
        if ($cmd !== '') {
            return $cmd;
        }

        $r = $this->http->request();
        $post = $r->getParsedBody();

        if ($cmd == "" && isset($post['interruptive_items'])) {
            $cmd = $delete;
        } else {
            $cmd = $standard;
        }

        return $cmd;
    }

    /**
     * @return ilMMItemFacadeInterface
     * @throws Throwable
     */
    protected function getMMItemFromRequest(): ilMMItemFacadeInterface
    {
        $r = $this->http->request();
        $get = $r->getQueryParams();
        $post = $r->getParsedBody();

        if (isset($post['interruptive_items'])) {
            $string = $post['interruptive_items'][0];
            $identification = $this->unhash($string);
        } else {
            $identification = $this->unhash($get[self::IDENTIFIER]);
        }

        return $this->repository->getItemFacadeForIdentificationString($identification);
    }

    protected function flush(): void
    {
        $this->repository->flushLostItems();
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_subitem_flushed"), true);
        $this->cancel();
    }

    abstract protected function cancel(): void;
}
