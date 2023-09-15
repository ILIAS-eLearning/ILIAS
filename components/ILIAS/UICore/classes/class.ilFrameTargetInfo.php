<?php

declare(strict_types=1);

/* Copyright (c) 1998-2022 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author Alex Killing <alex.killing@gmx.de>
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ilFrameTargetInfo
{
    public static function _getFrame(string $a_class): string
    {
        switch ($a_class) {
            case 'RepositoryContent':
            case 'MainContent':
                return self::getLtiFrame();

            case 'ExternalContent':
                return '_blank';

            default:
                return '';
        }
    }

    protected static function getLtiFrame(): string
    {
        global $DIC;

        if ($DIC->offsetExists('lti') && $DIC['lti']->isActive()) {
            return '_self';
        }

        return '_top';
    }
}
