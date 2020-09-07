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
require_once('./webservice/soap/classes/class.ilSoapAdministration.php');


/**
 * Soap data-collection administration methods
 *
 * @author Michael Herren <mh@studer-raimann.ch>
 *
 * @package ilias
 */
class ilSoapDataCollectionAdministration extends ilSoapAdministration
{

    /**
     * Export DataCollection async
     *
     * @param               $sid
     * @param               $target_ref_id
     * @param null|int      $table_id
     * @param string        $format
     * @param null|string   $filepath
     *
     * @return soap_fault|SoapFault
     */
    public function exportDataCollectionContent($sid, $target_ref_id, $table_id = null, $format = ilDclContentExporter::EXPORT_EXCEL, $filepath = null)
    {
        $this->initAuth($sid);
        $this->initIlias();
        if (!$this->__checkSession($sid)) {
            return $this->__raiseError($this->__getMessage(), $this->__getMessageCode());
        }

        require_once "Modules/DataCollection/classes/class.ilObjDataCollection.php";
        if (!$target_obj = new ilObjDataCollection($target_ref_id)) {
            return $this->__raiseError('No valid target given.', 'CLIENT');
        }

        if (ilObject::_isInTrash($target_ref_id)) {
            return $this->__raiseError(
                "Parent with ID $target_ref_id has been deleted.",
                'CLIENT_TARGET_DELETED'
            );
        }

        if (!ilObjDataCollectionAccess::hasReadAccess($target_ref_id)) {
            return $this->__raiseError(
                'Check access failed. No permission to read DataCollection',
                "CLIENT_PERMISSION_ISSUE"
            );
        }

        try {
            require_once "Modules/DataCollection/classes/Content/class.ilDclContentExporter.php";
            $exporter = new ilDclContentExporter($target_ref_id, $table_id);
            return $exporter->export($format, $filepath);
        } catch (ilException $exception) {
            return $this->__raiseError($exception->getMessage(), $exception->getCode());
        }
    }
}
