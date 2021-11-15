<?php declare(strict_types=1);
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

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

    public function avatar(string $size) : ilUserAvatar
    {
        if ((int) $this->dic->settings()->get('letter_avatars')) {
            return $this->letter();
        }

        return $this->file($size);
    }

    public function letter() : ilUserAvatarLetter
    {
        return new ilUserAvatarLetter();
    }

    public function file(string $size) : ilUserAvatarFile
    {
        return new ilUserAvatarFile($size);
    }
}
