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
 * The certificate a user earned in an LTI object, kept as a file in the personal workspace so that it
 * can be shown in a portfolio. It is a snapshot: it does not change when the object changes.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
class ilObjLTIToolVerification extends ilVerificationObject
{
    protected function initType(): void
    {
        $this->type = 'ltiv';
    }

    /**
     * @return array
     */
    protected function getPropertyMap(): array
    {
        return [
            'issued_on' => self::TYPE_DATE,
            'file' => self::TYPE_STRING,
        ];
    }
}
