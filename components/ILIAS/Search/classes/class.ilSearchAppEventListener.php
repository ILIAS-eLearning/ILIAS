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
* Update search command queue from Services/Object events
*
* @author Stefan Meyer <meyer@leifos.com>
*
*
* @ingroup ServicesSearch
*/
class ilSearchAppEventListener implements ilAppEventListener
{
    /**
     * @inheritDoc
     */
    public static function handleEvent(string $a_component, string $a_event, array $a_parameter): void
    {
        if (!isset($a_parameter['obj_id'])) {
            return;
        }

        // only for files in the moment
        if (!isset($a_parameter['obj_type'])) {
            $type = ilObject::_lookupType($a_parameter['obj_id']);
        } else {
            $type = $a_parameter['obj_type'];
        }

        switch ($a_component) {
            case 'components/ILIAS/Search':
                if ($a_event === 'contentChanged') {
                    ilSearchAppEventListener::storeElement(ilSearchCommandQueueElement::RESET, $a_parameter);
                }
                break;

            case 'components/ILIAS/Help':
            case 'components/ILIAS/Object':

                switch ($a_event) {
                    case 'undelete':
                    case 'update':
                        $command = ilSearchCommandQueueElement::RESET;
                        break;

                    case 'create':
                        $command = ilSearchCommandQueueElement::CREATE;
                        break;

                    case 'delete':
                    case 'toTrash':
                        $command = ilSearchCommandQueueElement::DELETE;
                        break;

                    default:
                        return;
                }
                ilSearchAppEventListener::storeElement($command, $a_parameter);
        }
    }

    protected static function storeElement(string $a_command, array $a_params): bool
    {
        if (!$a_command) {
            return false;
        }

        if (!isset($a_params['obj_id']) or !$a_params['obj_id']) {
            return false;
        }

        if (!isset($a_params['obj_type']) or !$a_params['obj_type']) {
            $a_params['obj_type'] = ilObject::_lookupType($a_params['obj_id']);
        }
        ilLoggerFactory::getLogger('src')->debug('Handling new command: ' . $a_command . ' for type ' . $a_params['obj_type']);

        $element = new ilSearchCommandQueueElement();
        $element->setObjId($a_params['obj_id']);
        $element->setObjType($a_params['obj_type']);
        $element->setCommand($a_command);

        ilSearchCommandQueue::factory()->store($element);
        return true;
    }
}
