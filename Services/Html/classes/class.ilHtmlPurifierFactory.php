<?php declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Factory for creating purifier instances
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilHtmlPurifierFactory
{
    public static function getInstanceByType(string $type) : ilHtmlPurifierInterface
    {
        global $DIC;

        switch ($type) {
            case 'frm_post':
                return new ilHtmlForumPostPurifier();

            case 'qpl_usersolution':
                return new ilAssHtmlUserSolutionPurifier();
        }

        throw new ilHtmlPurifierNotFoundException(sprintf(
            $DIC->language()->txt('frm_purifier_not_implemented_for_type_x'),
            $type
        ));
    }
}
