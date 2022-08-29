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
 * Class ilUserAvatarBase
 * @author Alexander Killing <killing@leifos.de>
 * @author Michael Jansen <mjansen@databay.de>
 */
abstract class ilUserAvatarBase implements ilUserAvatar
{
    protected string $name = '';
    protected int $usrId = 0;

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setUsrId(int $usrId): void
    {
        $this->usrId = $usrId;
    }
}
