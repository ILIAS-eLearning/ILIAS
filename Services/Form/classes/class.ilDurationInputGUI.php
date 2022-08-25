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

/**
 * This class represents a duration (typical hh:mm:ss) property in a property form.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilDurationInputGUI extends ilFormPropertyGUI
{
    protected int $months = 0;
    protected int $days = 0;
    protected int $hours = 0;
    protected int $minutes = 0;
    protected int $seconds = 0;
    protected bool $showmonths = false;
    protected bool $showdays = false;
    protected bool $showhours = true;
    protected bool $showminutes = true;
    protected bool $showseconds = false;

    public function __construct(
        string $a_title = "",
        string $a_postvar = ""
    ) {
        global $DIC;

        $this->lng = $DIC->language();
        parent::__construct($a_title, $a_postvar);
        $this->setType("duration");
    }

    public function setDays(int $a_days): void
    {
        $this->days = $a_days;
    }

    public function getDays(): int
    {
        return $this->days;
    }

    public function setHours(int $a_hours): void
    {
        $this->hours = $a_hours;
    }

    public function getHours(): int
    {
        return $this->hours;
    }

    public function setMinutes(int $a_minutes): void
    {
        $this->minutes = $a_minutes;
    }

    public function getMinutes(): int
    {
        return $this->minutes;
    }

    public function setSeconds(int $a_seconds): void
    {
        $this->seconds = $a_seconds;
    }

    public function setMonths(int $a_months): void
    {
        $this->months = $a_months;
    }

    public function getMonths(): int
    {
        return $this->months;
    }

    public function getSeconds(): int
    {
        return $this->seconds;
    }

    public function setShowMonths(bool $a_show_months): void
    {
        $this->showmonths = $a_show_months;
    }

    public function getShowMonths(): bool
    {
        return $this->showmonths;
    }

    public function setShowDays(bool $a_showdays): void
    {
        $this->showdays = $a_showdays;
    }

    public function getShowDays(): bool
    {
        return $this->showdays;
    }

    public function setShowHours(bool $a_showhours): void
    {
        $this->showhours = $a_showhours;
    }

    public function getShowHours(): bool
    {
        return $this->showhours;
    }

    public function setShowMinutes(bool $a_showminutes): void
    {
        $this->showminutes = $a_showminutes;
    }

    public function getShowMinutes(): bool
    {
        return $this->showminutes;
    }

    public function setShowSeconds(bool $a_showseconds): void
    {
        $this->showseconds = $a_showseconds;
    }

    public function getShowSeconds(): bool
    {
        return $this->showseconds;
    }

    public function setValueByArray(array $a_values): void
    {
        $values = $a_values[$this->getPostVar()];
        $value_or_zero = fn ($part) => array_key_exists($part, $values ?? []) ? (int) $values[$part] : 0;
        $this->setMonths($value_or_zero("MM"));
        $this->setDays($value_or_zero("dd"));
        $this->setHours($value_or_zero("hh"));
        $this->setMinutes($value_or_zero("mm"));
        $this->setSeconds($value_or_zero("ss"));
    }

    public function checkInput(): bool
    {
        return true;
    }

    public function getInput(): array
    {
        return $this->strArray($this->getPostVar());
    }

    public function insert(ilTemplate $a_tpl): void
    {
        $html = $this->render();

        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $html);
        $a_tpl->parseCurrentBlock();
    }

    public function render(): string
    {
        $lng = $this->lng;

        $tpl = new ilTemplate("tpl.prop_duration.html", true, true, "Services/Form");

        if ($this->getShowMonths()) {
            $tpl->setCurrentBlock("dur_months");
            $tpl->setVariable("TXT_MONTHS", $lng->txt("form_months"));
            $val = array();
            for ($i = 0; $i <= 36; $i++) {
                $val[$i] = $i;
            }
            $tpl->setVariable(
                "SELECT_MONTHS",
                ilLegacyFormElementsUtil::formSelect(
                    $this->getMonths(),
                    $this->getPostVar() . "[MM]",
                    $val,
                    false,
                    true,
                    0,
                    '',
                    [],
                    $this->getDisabled()
                )
            );
            $tpl->parseCurrentBlock();
        }
        if ($this->getShowDays()) {
            $tpl->setCurrentBlock("dur_days");
            $tpl->setVariable("TXT_DAYS", $lng->txt("form_days"));
            $val = array();
            for ($i = 0; $i <= 366; $i++) {
                $val[$i] = $i;
            }
            $tpl->setVariable(
                "SELECT_DAYS",
                ilLegacyFormElementsUtil::formSelect(
                    $this->getDays(),
                    $this->getPostVar() . "[dd]",
                    $val,
                    false,
                    true,
                    0,
                    '',
                    [],
                    $this->getDisabled()
                )
            );
            $tpl->parseCurrentBlock();
        }
        if ($this->getShowHours()) {
            $tpl->setCurrentBlock("dur_hours");
            $tpl->setVariable("TXT_HOURS", $lng->txt("form_hours"));
            $val = array();
            for ($i = 0; $i <= 23; $i++) {
                $val[$i] = $i;
            }
            $tpl->setVariable(
                "SELECT_HOURS",
                ilLegacyFormElementsUtil::formSelect(
                    $this->getHours(),
                    $this->getPostVar() . "[hh]",
                    $val,
                    false,
                    true,
                    0,
                    '',
                    [],
                    $this->getDisabled()
                )
            );
            $tpl->parseCurrentBlock();
        }
        if ($this->getShowMinutes()) {
            $tpl->setCurrentBlock("dur_minutes");
            $tpl->setVariable("TXT_MINUTES", $lng->txt("form_minutes"));
            $val = array();
            for ($i = 0; $i <= 59; $i++) {
                $val[$i] = $i;
            }
            $tpl->setVariable(
                "SELECT_MINUTES",
                ilLegacyFormElementsUtil::formSelect(
                    $this->getMinutes(),
                    $this->getPostVar() . "[mm]",
                    $val,
                    false,
                    true,
                    0,
                    '',
                    [],
                    $this->getDisabled()
                )
            );
            $tpl->parseCurrentBlock();
        }
        if ($this->getShowSeconds()) {
            $tpl->setCurrentBlock("dur_seconds");
            $tpl->setVariable("TXT_SECONDS", $lng->txt("form_seconds"));
            $val = array();
            for ($i = 0; $i <= 59; $i++) {
                $val[$i] = $i;
            }
            $tpl->setVariable(
                "SELECT_SECONDS",
                ilLegacyFormElementsUtil::formSelect(
                    $this->getSeconds(),
                    $this->getPostVar() . "[ss]",
                    $val,
                    false,
                    true,
                    0,
                    '',
                    [],
                    $this->getDisabled()
                )
            );
            $tpl->parseCurrentBlock();
        }

        return $tpl->get();
    }

    public function getTableFilterHTML(): string
    {
        $html = $this->render();
        return $html;
    }

    public function serializeData(): string
    {
        $data = array("months" => $this->getMonths(),
            "days" => $this->getDays(),
            "hours" => $this->getHours(),
            "minutes" => $this->getMinutes(),
            "seconds" => $this->getSeconds());

        return serialize($data);
    }

    public function unserializeData(string $a_data): void
    {
        $data = unserialize($a_data);

        $this->setMonths($data["months"]);
        $this->setDays($data["days"]);
        $this->setHours($data["hours"]);
        $this->setMinutes($data["minutes"]);
        $this->setSeconds($data["seconds"]);
    }

    public function getValueInSeconds(): int
    {
        $value = 0;
        if ($this->getShowMonths()) {
            $value += $this->getMonths() * 30 * 24 * 60 * 60;
        }
        if ($this->getShowDays()) {
            $value += $this->getDays() * 24 * 60 * 60;
        }
        if ($this->getShowHours()) {
            $value += $this->getHours() * 60 * 60;
        }
        if ($this->getShowMinutes()) {
            $value += $this->getMinutes() * 60;
        }
        if ($this->getShowSeconds()) {
            $value += $this->getSeconds();
        }
        return $value;
    }
}
