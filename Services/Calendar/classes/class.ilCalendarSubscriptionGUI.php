<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Calendar/classes/class.ilCalendarCategory.php';

/**
 * Show calendar subscription info
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * $Id$
 */
class ilCalendarSubscriptionGUI
{
    private $cal_id = 0;
    private $calendar = null;

    /**
     * Constructor
     * @param int $a_clendar_id
     */
    public function __construct($a_calendar_id, $a_ref_id = 0)
    {
        global $DIC;

        $this->cal_id = $a_calendar_id;
        $this->ref_id = $a_ref_id;
        $this->user = $DIC->user();
        $this->lng = $DIC->language();
        $this->tpl = $DIC["tpl"];

        include_once './Services/Calendar/classes/class.ilCalendarCategory.php';
    }

    /**
     * Execute command
     */
    public function executeCommand()
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];

        $next_class = $ilCtrl->getNextClass($this);
        switch ($next_class) {
            default:
                $cmd = $ilCtrl->getCmd("show");

                $this->$cmd();
                break;
        }
        return true;
    }

    /**
     * Show subscription info
     */
    protected function show()
    {
        ilUtil::sendInfo($this->lng->txt('cal_subscription_info'));

        include_once './Services/InfoScreen/classes/class.ilInfoScreenGUI.php';
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

        $hash = $this->createToken($this->user->getID(), $selection, $id);
        $url = ILIAS_HTTP_PATH . '/calendar.php?client_id=' . CLIENT_ID . '&token=' . $hash;
        $info->addSection($this->lng->txt("cal_subscription"));
        $info->addProperty($this->lng->txt('cal_ical_url'), $url, $url);

        $this->tpl->setContent($info->getHTML());
    }

    /**
     * Create calendar token
     */
    private function createToken($user_id, $selection, $id)
    {
        include_once './Services/Calendar/classes/class.ilCalendarAuthenticationToken.php';
        $hash = ilCalendarAuthenticationToken::lookupAuthToken($user_id, $selection, $id);
        if (strlen($hash)) {
            return $hash;
        }
        $token = new ilCalendarAuthenticationToken($user_id);
        $token->setSelectionType($selection);
        $token->setCalendar($id);
        return $token->add();
    }

    /**
     * gGet modal for subscription
     */
    protected function getModalForSubscription()
    {
        global $DIC;

        $lng = $DIC->language();

        $ui_factory = $DIC->ui()->factory();
        $ui_renderer = $DIC->ui()->renderer();

        $tpl = new ilTemplate(
            'tpl.subscription_dialog.html',
            true,
            true,
            'Services/Calendar'
        );

        $tpl->setVariable('TXT_SUBSCRIPTION_INFO', $lng->txt('cal_subscription_info'));


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
        $hash = $this->createToken($this->user->getID(), $selection, $id);
        $url = ILIAS_HTTP_PATH . '/calendar.php?client_id=' . CLIENT_ID . '&token=' . $hash;

        $tpl->setVariable('LINK', $url);
        $tpl->setVariable('TXT_PERMA', $lng->txt('cal_ical_url'));

        $roundtrip = $ui_factory->modal()->roundtrip(
            $lng->txt('cal_calendar_subscription_modal_title'),
            $ui_factory->legacy($tpl->get())
        );
        echo $ui_renderer->render($roundtrip);
        exit;
    }
}
