<?php declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Factory for creating purifier instances
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilHtmlPurifierFactory
{
    /**
     * Factory method for creating purifier instances
     * @param string $type type for the concrete purifier instance
     * @return ilHtmlPurifierInterface
     * @throws ilHtmlPurifierNotFoundException
     */
    public static function _getInstanceByType(string $type) : ilHtmlPurifierInterface
    {
        global $DIC;

        switch ($type) {
            case 'frm_post':
                return new ilHtmlForumPostPurifier();
                break;

            case 'qpl_usersolution':
                return new ilAssHtmlUserSolutionPurifier();
                break;
        }

        throw new ilHtmlPurifierNotFoundException(sprintf(
            $DIC->language()->txt('frm_purifier_not_implemented_for_type_x'),
            $type
        ));
    }
}