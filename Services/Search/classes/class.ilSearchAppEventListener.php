<?php declare(strict_types=1);
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/


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
    public static function handleEvent(string $a_component, string $a_event, array $a_parameter) : void
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

        if ($type != 'file' and
            $type != 'htlm') {
            return;
        }
        
        switch ($a_component) {
            case 'Services/Help':
            case 'Services/Object':
                
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
    
    protected static function storeElement(string $a_command, array $a_params) : bool
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
