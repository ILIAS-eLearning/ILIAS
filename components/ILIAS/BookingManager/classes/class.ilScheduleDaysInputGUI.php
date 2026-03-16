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

class ilScheduleDaysInputGUI extends ilFormPropertyGUI
{
    public const DAY_KEYS = ['mo', 'tu', 'we', 'th', 'fr', 'sa', 'su'];

    protected array $value = [];

    public function setValue(array $a_value): void
    {
        $this->value = $a_value;
    }

    public function getValue(): array
    {
        return $this->value;
    }

    public function checkInput(): bool
    {
        $data = $this->getPostData();
        foreach ($data as $day) {
            if (!in_array($day, self::DAY_KEYS, true)) {
                $this->setAlert($this->lng->txt('msg_input_does_not_match_regexp'));
                return false;
            }
        }
        return true;
    }

    public function getPostData(): array
    {
        return $this->strArray($this->getPostVar());
    }

    public function setValueByArray(array $a_values): void
    {
        $this->setValue($this->getPostData());
    }

    protected function render(): string
    {
        $tpl = new ilTemplate('tpl.schedule_days_input.html', true, true, 'components/ILIAS/BookingManager');

        foreach (self::DAY_KEYS as $offset => $day_value) {
            $tpl->setCurrentBlock('day');
            $tpl->setVariable('ID', $this->getFieldId());
            $tpl->setVariable('POST_VAR', $this->getPostVar());
            $tpl->setVariable('DAY', $day_value);
            $tpl->setVariable('TXT_DAY', ilCalendarUtil::_numericDayToString(($offset + 1) % 7, false, $this->lng));
            $tpl->setVariable('DAY_STATUS', in_array($day_value, $this->getValue(), true) ? ' checked="checked"' : '');
            $tpl->parseCurrentBlock();
        }

        return $tpl->get();
    }

    public function insert(ilTemplate $a_tpl): void
    {
        $a_tpl->setCurrentBlock('prop_generic');
        $a_tpl->setVariable('PROP_GENERIC', $this->render());
        $a_tpl->parseCurrentBlock();
    }
}
