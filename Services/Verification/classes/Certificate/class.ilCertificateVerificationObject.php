<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateVerificationObject extends ilVerificationObject
{
    public function __construct(string $type, int $a_id = 0, bool $a_reference = true)
    {
        $this->type = $type;

        parent::__construct($a_id, $a_reference);
    }

    /**
     * @inheritDoc
     */
    protected function initType(): void
    {
    }

    /**
     * @inheritDoc
     */
    protected function getPropertyMap(): array
    {
        return array(
            "issued_on" => self::TYPE_DATE,
            "file" => self::TYPE_STRING
        );
    }
}
