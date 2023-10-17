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

class ilDclBooleanRecordRepresentation extends ilDclBaseRecordRepresentation
{
    /**
     * Outputs html of a certain field
     */
    public function getHTML(bool $link = true, array $options = []): string
    {
        $value = $this->getRecordField()->getValue();

        if ($value) {
            $icon = $this->factory->symbol()->icon()->custom(
                ilUtil::getImagePath('standard/icon_ok_monochrome.svg'),
                $this->lng->txt("yes")
            );
        } else {
            $icon = $this->factory->symbol()->icon()->custom(
                ilUtil::getImagePath('standard/icon_not_ok_monochrome.svg'),
                $this->lng->txt("no")
            );
        }

        return $this->renderer->render($icon);
    }
}
