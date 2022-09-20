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
 * Factory for creating purifier instances
 * @author Michael Jansen <mjansen@databay.de>
 */
final class ilHtmlPurifierFactory
{
    public static function getInstanceByType(string $type): ilHtmlPurifierInterface
    {
        global $DIC;

        return match ($type) {
            'frm_post' => new ilHtmlForumPostPurifier(),
            'qpl_usersolution' => new ilAssHtmlUserSolutionPurifier(),
            default => throw new ilHtmlPurifierNotFoundException(sprintf(
                $DIC->language()->txt('frm_purifier_not_implemented_for_type_x'),
                $type
            )),
        };
    }
}
