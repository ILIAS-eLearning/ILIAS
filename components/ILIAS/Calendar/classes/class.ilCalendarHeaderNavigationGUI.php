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
/**
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesCalendar
 */
class ilCalendarHeaderNavigationGUI
{
    protected object $cmdClass;
    protected string $cmd;
    protected ilDate $seed;
    protected string $increment = '';

    protected string $html;

    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;
    protected ilCtrlInterface $ctrl;
    protected ilToolbarGUI $toolbar;
    protected ilObjUser $user;
    protected ilCalendarUserSettings $user_settings;
    protected \ILIAS\DI\UIServices $ui;

    public function __construct(object $cmdClass, ilDate $seed, string $a_increment, string $cmd = '')
    {
        global $DIC;

        $this->lng = $DIC->language();

        $this->ctrl = $DIC->ctrl();
        $this->cmdClass = $cmdClass;
        $this->seed = clone $seed;
        $this->increment = $a_increment;
        $this->cmd = $cmd;
        $this->toolbar = $DIC->toolbar();
        $this->ui = $DIC->ui();
        $this->user = $DIC->user();
        $this->user_settings = ilCalendarUserSettings::_getInstanceByUserId($this->user->getId());
    }

    public function getHTML(): string
    {
        $today = new ilDateTime(time(), IL_CAL_UNIX, $this->user->getTimeZone());
        $tpl = new ilTemplate("tpl.navigation_header.html", true, true, "components/ILIAS/Calendar");

        // previous button
        $contains_today = false;
        $this->incrementDate(-1);
        $this->ctrl->setParameterByClass(get_class($this->cmdClass), 'seed', $this->seed->get(IL_CAL_DATE));
        $b1 = $this->ui->factory()->button()->standard(
            $this->lng->txt("previous"),
            $this->ctrl->getLinkTarget($this->cmdClass, $this->cmd)
        );

        // today button
        $this->incrementDate(1);
        ilDatePresentation::setUseRelativeDates(false);
        switch ($this->increment) {
            case ilDateTime::DAY:
                $tpl->setVariable(
                    "TXT_TITLE",
                    ilCalendarUtil::_numericDayToString((int) $this->seed->get(IL_CAL_FKT_DATE, 'w')) .
                    ", " . ilDatePresentation::formatDate($this->seed)
                );
                if (date("Y-m-d") === $this->seed->get(IL_CAL_FKT_DATE, 'Y-m-d')) {
                    $contains_today = true;
                }
                break;

            case ilDateTime::WEEK:
                $weekday_list = ilCalendarUtil::_buildWeekDayList(
                    $this->seed,
                    $this->user_settings->getWeekStart()
                )->get();
                $start = current($weekday_list);
                $end = end($weekday_list);
                $tpl->setVariable("TXT_TITLE", $this->lng->txt('week') . ' ' . $this->seed->get(IL_CAL_FKT_DATE, 'W') .
                    ", " . ilDatePresentation::formatDate($start) . " - " .
                    ilDatePresentation::formatDate($end));
                $il_date_now = new ilDateTime(ilUtil::now(), IL_CAL_DATETIME);
                if (ilDate::_within($il_date_now, $start, $end)) {
                    $contains_today = true;
                }
                break;

            case ilDateTime::MONTH:
                $tpl->setVariable(
                    "TXT_TITLE",
                    $this->lng->txt('month_' . $this->seed->get(IL_CAL_FKT_DATE, 'm') . '_long') .
                    ' ' . $this->seed->get(IL_CAL_FKT_DATE, 'Y')
                );
                if ($this->seed->get(IL_CAL_FKT_DATE, 'Y-m') == date("Y-m")) {
                    $contains_today = true;
                }
                break;
        }
        ilDatePresentation::setUseRelativeDates(true);
        $this->ctrl->setParameterByClass(
            get_class($this->cmdClass),
            'seed',
            $today->get(IL_CAL_DATE)
        );
        if ($contains_today) {
            $b2 = $this->ui->factory()->button()->standard(
                $this->lng->txt("today"),
                $this->ctrl->getLinkTarget($this->cmdClass, $this->cmd)
            )->withEngagedState(true);
        } else {
            $b2 = $this->ui->factory()->button()->standard(
                $this->lng->txt("today"),
                $this->ctrl->getLinkTarget($this->cmdClass, $this->cmd)
            );
        }
        // next button
        $this->incrementDate(1);
        $this->ctrl->setParameterByClass(get_class($this->cmdClass), 'seed', $this->seed->get(IL_CAL_DATE));
        $b3 = $this->ui->factory()->button()->standard(
            $this->lng->txt("next"),
            $this->ctrl->getLinkTarget($this->cmdClass, $this->cmd)
        );
        $this->ctrl->setParameterByClass(get_class($this->cmdClass), 'seed', '');
        $this->toolbar->addStickyItem($this->ui->factory()->viewControl()->section($b1, $b2, $b3));
        $this->toolbar->addSeparator();

        return $tpl->get();
    }

    protected function incrementDate(int $a_count): void
    {
        switch ($this->increment) {
            case ilDateTime::MONTH:
                $day = $this->seed->get(IL_CAL_FKT_DATE, 'j');
                if ($day > 28) {
                    $this->seed->increment(IL_CAL_DAY, (31 - $day) * -1);
                }
                // no break
            default:
                $this->seed->increment($this->increment, $a_count);
                break;
        }
    }
}
