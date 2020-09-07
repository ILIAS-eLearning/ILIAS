<?php
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

include_once('./webservice/soap/classes/class.ilSoapAdministration.php');

/**
 * Soap background task administration methods
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 *
 * @package ilias
 */
class ilSoapBackgroundTaskAdministration extends ilSoapAdministration
{
    /**
     * Process task
     *
     * @param int $a_task_id
     * @return boolean
     */
    public function processBackgroundTask($a_sid, $a_task_id)
    {
        $this->initAuth($a_sid);
        $this->initIlias();

        if (!$this->__checkSession($a_sid)) {
            return $this->__raiseError($this->__getMessage(), $this->__getMessageCode());
        }
        
        include_once "Services/BackgroundTask/classes/class.ilBackgroundTask.php";
        $task = new ilBackgroundTask($a_task_id);
        if ($task->exists()) {
            $handler = $task->getHandlerInstance();
            $handler->process();

            return true;
        }
    
        return false;
    }
}
