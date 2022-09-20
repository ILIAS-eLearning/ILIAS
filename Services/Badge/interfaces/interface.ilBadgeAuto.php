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
 * Manual Badge Auto
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
interface ilBadgeAuto
{
    /**
     * Evaluate if given user has earned badge
     */
    public function evaluate(int $a_user_id, array $a_params, array $a_config): bool;
}
