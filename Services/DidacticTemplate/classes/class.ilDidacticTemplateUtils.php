<?php declare(strict_types=1);

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Utilities for didactic templates
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilDidacticTemplateUtils
{
    public static function switchTemplate($a_ref_id, $a_new_tpl_id)
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
        $factory = new ilObjectFactory();
        $obj = $factory->getInstanceByRefId($a_ref_id, false);
        if ($obj instanceof ilObject) {
            $obj->applyDidacticTemplate($a_new_tpl_id);
        }
        return true;
    }
}
