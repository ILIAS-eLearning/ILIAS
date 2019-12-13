<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilUserAvatarFile
 * @author Alexander Killing <killing@leifos.de>
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilUserAvatarFile extends ilUserAvatarBase
{
    /**
     * @var string
     */
    protected $size = '';

    /**
     * ilUserAvatarFile constructor.
     * @param string $size
     */
    public function __construct($size)
    {
        $this->size = $size;
    }

    /**
     * @inheritdoc
     */
    public function getUrl()
    {
        return \ilWACSignedPath::signFile(\ilUtil::getImagePath('no_photo_' . $this->size . '.jpg'));
    }
}
