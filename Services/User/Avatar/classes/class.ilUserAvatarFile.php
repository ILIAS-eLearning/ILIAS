<?php declare(strict_types=1);
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilUserAvatarFile
 * @author Alexander Killing <killing@leifos.de>
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilUserAvatarFile extends ilUserAvatarBase
{
    protected string $size;

    public function __construct(string $size)
    {
        $this->size = $size;
    }

    public function getUrl() : string
    {
        return ilWACSignedPath::signFile(\ilUtil::getImagePath('no_photo_' . $this->size . '.jpg'));
    }
}
