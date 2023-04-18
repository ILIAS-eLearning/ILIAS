<?php

declare(strict_types=1);

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory;

/**
 * @author       Jesús López <lopez@leifos.com>
 * @ilCtrl_Calls ilMDCopyrightUsageGUI: ilPublicUserProfileGUI
 * @ingroup      ServicesMetaData
 */
class ilMDCopyrightUsageGUI
{
    public const DEFAULT_CMD = 'showUsageTable';

    protected int $entry_id;

    protected ilGlobalTemplateInterface $tpl;
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilTabsGUI $tabs;
    protected GlobalHttpState $http;
    protected Factory $refinery;

    public function __construct(int $a_entry_id)
    {
        global $DIC;

        $this->tpl = $DIC->ui()->mainTemplate();
        $this->http = $DIC->http();
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tabs = $DIC->tabs();
        $this->refinery = $DIC->refinery();

        $this->entry_id = $a_entry_id;
    }

    public function executeCommand(): void
    {
        // save usage id for all request
        $this->ctrl->saveParameter($this, 'entry_id');

        $user = '';
        if ($this->http->wrapper()->query()->has('user')) {
            $user = $this->http->wrapper()->query()->retrieve(
                'user',
                $this->refinery->kindlyTo()->string()
            );
        }

        $this->setTabs();
        $next_class = $this->ctrl->getNextClass($this);
        switch ($this->ctrl->getNextClass($this)) {
            case 'ilpublicuserprofilegui':
                $profile_gui = new ilPublicUserProfileGUI($this->http->wrapper()->query()->retrieve(
                    'user',
                    $this->refinery->kindlyTo()->int()
                ));
                $profile_gui->setBackUrl(
                    $this->ctrl->getLinkTarget($this, self::DEFAULT_CMD)
                );
                $html = $this->ctrl->forwardCommand($profile_gui);
                $this->tpl->setContent($html);
                break;

            default:
                $cmd = $this->ctrl->getCmd(self::DEFAULT_CMD);
                $this->$cmd();
                break;
        }
    }

    public function showUsageTable(): void
    {
        $this->tpl->setOnScreenMessage('info', $this->lng->txt("meta_info_only_repository_objects"));

        $table_gui = new ilMDCopyrightUsageTableGUI(
            $this,
            self::DEFAULT_CMD,
        );
        $table_gui->setFilterCommand("applyUsageFilter");
        $table_gui->setResetCommand("resetUsageFilter");
        $table_gui->init();
        $table_gui->parse();

        $this->tpl->setContent($table_gui->getHTML());
    }

    public function getEntryId(): int
    {
        return $this->entry_id;
    }

    protected function applyUsageFilter(): void
    {
        $table_gui = new ilMDCopyrightUsageTableGUI(
            $this,
            self::DEFAULT_CMD,
        );
        $table_gui->init();
        $table_gui->resetOffset();        // sets record offset to 0 (first page)
        $table_gui->writeFilterToSession();    // writes filter to session

        $this->ctrl->redirect($this, self::DEFAULT_CMD);
    }

    protected function resetUsageFilter(): void
    {
        $table_gui = new ilMDCopyrightUsageTableGUI(
            $this,
            self::DEFAULT_CMD,
        );
        $table_gui->init();
        $table_gui->resetOffset();        // sets record offest to 0 (first page)
        $table_gui->resetFilter();        // clears filter

        $this->ctrl->redirect($this, self::DEFAULT_CMD);
    }

    protected function setTabs(): void
    {
        $this->tabs->clearTargets();
        $this->tabs->setBackTarget(
            $this->lng->txt('back'),
            $this->ctrl->getParentReturn($this)
        );
    }
}
