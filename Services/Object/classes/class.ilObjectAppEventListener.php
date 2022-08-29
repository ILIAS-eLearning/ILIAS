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
 * Class ilObjectAppEventListener
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilObjectAppEventListener implements ilAppEventListener
{
    /**
     * @inheritdoc
     */
    public static function handleEvent(string $component, string $event, array $parameter): void
    {
        global $DIC;

        if ('Services/Object' === $component && 'beforeDeletion' === $event) {
            /** @var \ilObjectCustomIconFactory  $customIconFactory */
            $customIconFactory = $DIC['object.customicons.factory'];
            $customIcon = $customIconFactory->getByObjId(
                $parameter['object']->getId(),
                $parameter['object']->getType()
            );
            $customIcon->delete();
        }
    }
}
