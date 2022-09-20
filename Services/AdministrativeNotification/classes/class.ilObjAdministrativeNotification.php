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
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 */
class ilObjAdministrativeNotification extends ilObject
{
    public const TYPE_ADN = "adn";

    public function __construct(int $id = 0, bool $call_by_reference = true)
    {
        $this->type = self::TYPE_ADN;
        parent::__construct($id, $call_by_reference);
    }

    /**
     * @inheritDoc
     */
    public function getPresentationTitle(): string
    {
        return $this->lng->txt("obj_adn");
    }

    /**
     * @inheritDoc
     */
    public function getLongDescription(): string
    {
        return $this->lng->txt("administrative_notification_description");
    }
}
