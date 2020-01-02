<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilUserAvatarFactory
 * @author Alexander Killing <killing@leifos.de>
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilUserAvatarFactory
{
    /**
     * @var \ILIAS\DI\Container
     */
    protected $dic;

    /**
     * ilUserAvatarFactory constructor.
     * @param \ILIAS\DI\Container $dic
     */
    public function __construct(\ILIAS\DI\Container $dic)
    {
        $this->dic = $dic;
    }

    /**
     * @param string $size
     * @return ilUserAvatar
     */
    public function avatar($size)
    {
        if ((int) $this->dic->settings()->get('letter_avatars')) {
            return $this->letter();
        }

        return $this->file($size);
    }

    /**
     * @return ilUserAvatarLetter
     */
    public function letter()
    {
        return new ilUserAvatarLetter();
    }

    /**
     * @param string $size
     * @return ilUserAvatarFile
     */
    public function file($size)
    {
        return new ilUserAvatarFile($size);
    }
}
