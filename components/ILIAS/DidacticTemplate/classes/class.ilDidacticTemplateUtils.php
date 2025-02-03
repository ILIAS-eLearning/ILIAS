<?php

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

declare(strict_types=1);

/**
 * Utilities for didactic templates
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilDidacticTemplateUtils
{
    public static function switchTemplate(int $a_ref_id, int $a_new_tpl_id): bool
    {
        global $DIC;

        $logger = $DIC->logger()->otpl();
        $current_tpl_id = ilDidacticTemplateObjSettings::lookupTemplateId(
            $a_ref_id
        );
        $logger->debug('Current template id: ' . $current_tpl_id);
        // Revert current template
        if ($current_tpl_id) {
            $logger->debug('Reverting template with id: ' . $current_tpl_id);
            foreach (ilDidacticTemplateActionFactory::getActionsByTemplateId($current_tpl_id) as $action) {
                $action->setRefId($a_ref_id);
                $action->revert();
            }
        }

        $obj = ilObjectFactory::getInstanceByRefId($a_ref_id, false);
        if ($obj instanceof ilObject) {
            $obj->applyDidacticTemplate($a_new_tpl_id);
        }

        return true;
    }
}
