<?php

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
        include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateObjSettings.php';
        $current_tpl_id = ilDidacticTemplateObjSettings::lookupTemplateId(
            $a_ref_id
        );
        
        ilLoggerFactory::getLogger('dtpl')->debug('Current template id: ' . $current_tpl_id);

        // Revert current template
        if ($current_tpl_id) {
            ilLoggerFactory::getLogger('dtpl')->debug('Reverting template with id: ' . $current_tpl_id);
            
            include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateActionFactory.php';
            foreach (ilDidacticTemplateActionFactory::getActionsByTemplateId($current_tpl_id) as $action) {
                $action->setRefId($a_ref_id);
                $action->revert();
            }
        }
        
        include_once './Services/Object/classes/class.ilObjectFactory.php';
        $factory = new ilObjectFactory();
        $obj = $factory->getInstanceByRefId($a_ref_id, false);
        if ($obj instanceof ilObject) {
            $obj->applyDidacticTemplate($a_new_tpl_id);
        }
        return true;
    }
}
