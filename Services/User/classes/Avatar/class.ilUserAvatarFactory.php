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
 * Class ilUserAvatarFactory
 * @author Alexander Killing <killing@leifos.de>
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilUserAvatarFactory
{
    protected \ILIAS\DI\Container $dic;

    public function __construct(\ILIAS\DI\Container $dic)
    {
        $this->dic = $dic;
    }

    public function avatar(string $size): ilUserAvatar
    {
        if ((int) $this->dic->settings()->get('letter_avatars')) {
            return $this->letter();
        }

        return $this->file($size);
    }

    public function letter(): ilUserAvatarLetter
    {
        return new ilUserAvatarLetter();
    }

    public function file(string $size): ilUserAvatarFile
    {
        return new ilUserAvatarFile($size);
    }
}
