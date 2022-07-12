<?php declare(strict_types=1);

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
 * Handles delete events from courses and categories.
 *
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilContainerReferenceAppEventListener implements ilAppEventListener
{
    public static function handleEvent(string $a_component, string $a_event, array $a_parameter) : void
    {
        global $DIC;

        $ilLog = $DIC["ilLog"];
        
        switch ($a_component) {
            case 'Modules/Course':
            case 'Modules/Category':
            case 'Modules/StudyProgramme':
                switch ($a_event) {
                    case 'delete':
                        $ilLog->write(__METHOD__ . ': Handling delete event.');
                        self::deleteReferences((int) $a_parameter['obj_id']);
                        break;
                }
                break;
        }
    }
    
    public static function deleteReferences(int $a_target_id) : void
    {
        global $DIC;

        $ilLog = $DIC["ilLog"];
        $ilAppEventHandler = $DIC["ilAppEventHandler"];
        $tree = $DIC["tree"];
        
        if (!$source_id = ilContainerReference::_lookupSourceId($a_target_id)) {
            return;
        }
        foreach (ilObject::_getAllReferences($source_id) as $ref_id) {
            if (!$instance = ilObjectFactory::getInstanceByRefId($ref_id, false)) {
                continue;
            }
            $type = $instance->getType();
            switch ($type) {
                case 'grpr':
                case 'crsr':
                case 'catr':
                case 'prgr':
                    $parent_id = $tree->getParentId($ref_id);
                    $instance->delete();
                    $ilLog->write(__METHOD__ . ': Deleted reference object of type ' . $instance->getType() . ' with Id ' . $instance->getId());
                    $ilAppEventHandler->raise(
                        'Services/ContainerReference',
                        'deleteReference',
                        [
                            'ref_id' => $ref_id,
                            'old_parent_ref_id' => $parent_id,
                            'type' => $type
                        ]
                    );
                    break;
                    
                default:
                    $ilLog->write(__METHOD__ . ': Unexpected object type ' . $instance->getType() . ' with Id ' . $instance->getId());
                    break;
            }
        }
    }
}
