<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;

/**
 * Show calendar subscription info
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * $Id$
 */
class ilCalendarSubscriptionGUI
{
    private int $cal_id = 0;
    private int $ref_id = 0;

    protected ilObjUser $user;
    protected ilCtrlInterface $ctrl;
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;
    protected Factory $ui_factory;
    protected Renderer $ui_renderer;

    public function __construct(int $a_calendar_id, int $a_ref_id = 0)
    {
        global $DIC;

        $this->cal_id = $a_calendar_id;
        $this->ref_id = $a_ref_id;
        $this->user = $DIC->user();
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();
    }

    public function executeCommand() : void
    {
        $next_class = $this->ctrl->getNextClass($this);
        switch ($next_class) {
            default:
                $cmd = $this->ctrl->getCmd("show");
                $this->$cmd();
                break;
        }
    }

    /**
     * Show subscription info
     */
    protected function show() : void
    {
        $this->tpl->setOnScreenMessage('info', $this->lng->txt('cal_subscription_info'));

        $info = new ilInfoScreenGUI($this);
        $info->setFormAction($GLOBALS['DIC']['ilCtrl']->getFormAction($this));

        if ($this->cal_id > 0) {
            $selection = ilCalendarAuthenticationToken::SELECTION_CALENDAR;
            $id = $this->cal_id;
        } elseif ($this->ref_id > 0) {
            $selection = ilCalendarAuthenticationToken::SELECTION_CALENDAR;
            $category = ilCalendarCategory::_getInstanceByObjId(ilObject::_lookupObjId($this->ref_id));
            $id = $category->getCategoryID();
        } else {
            $selection = ilCalendarAuthenticationToken::SELECTION_PD;
            $id = 0;
        }

        $hash = $this->createToken($this->user->getId(), $selection, $id);
        $url = ILIAS_HTTP_PATH . '/calendar.php?client_id=' . CLIENT_ID . '&token=' . $hash;
        $info->addSection($this->lng->txt("cal_subscription"));
        $info->addProperty($this->lng->txt('cal_ical_url'), $url, $url);

        $this->tpl->setContent($info->getHTML());
    }

    private function createToken($user_id, $selection, $id) : string
    {
        $hash = ilCalendarAuthenticationToken::lookupAuthToken($user_id, $selection, $id);
        if (strlen($hash)) {
            return $hash;
        }
        $token = new ilCalendarAuthenticationToken($user_id);
        $token->setSelectionType($selection);
        $token->setCalendar($id);
        return $token->add();
    }

    protected function getModalForSubscription() : void
    {
        $tpl = new ilTemplate(
            'tpl.subscription_dialog.html',
            true,
            true,
            'Services/Calendar'
        );

        $tpl->setVariable('TXT_SUBSCRIPTION_INFO', $this->lng->txt('cal_subscription_info'));

        if ($this->cal_id > 0) {
            $selection = ilCalendarAuthenticationToken::SELECTION_CALENDAR;
            $id = $this->cal_id;
        } elseif ($this->ref_id > 0) {
            $selection = ilCalendarAuthenticationToken::SELECTION_CALENDAR;
            $category = ilCalendarCategory::_getInstanceByObjId(ilObject::_lookupObjId($this->ref_id));
            $id = $category->getCategoryID();
        } else {
            $selection = ilCalendarAuthenticationToken::SELECTION_PD;
            $id = 0;
        }
        $hash = $this->createToken($this->user->getId(), $selection, $id);
        $url = ILIAS_HTTP_PATH . '/calendar.php?client_id=' . CLIENT_ID . '&token=' . $hash;

        $tpl->setVariable('LINK', $url);
        $tpl->setVariable('TXT_PERMA', $this->lng->txt('cal_ical_url'));

        $roundtrip = $this->ui_factory->modal()->roundtrip(
            $this->lng->txt('cal_calendar_subscription_modal_title'),
            $this->ui_factory->legacy($tpl->get())
        );
        echo $this->ui_renderer->render($roundtrip);
        exit;
    }
}
