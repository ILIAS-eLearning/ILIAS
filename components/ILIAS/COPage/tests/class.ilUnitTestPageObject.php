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

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ilUnitTestPageObject extends ilPageObject
{
    public function getParentType(): string
    {
        return "unit";
    }

    public function initPageConfig(): void
    {
        $this->setPageConfig(new ilUnitTestPageConfig());
    }

    protected function getIliasAbsolutePath(): string
    {
        return ".";
    }

    /**
     * @return array|bool
     */
    public function update(bool $a_validate = true, bool $a_no_history = false)
    {
        return true;
    }
}
