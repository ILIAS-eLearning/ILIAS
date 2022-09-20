<?php

declare(strict_types=1);

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

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
