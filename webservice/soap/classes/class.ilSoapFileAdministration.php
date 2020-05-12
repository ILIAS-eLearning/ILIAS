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


  /**
   * Soap file administration methods
   *
   * @author Roland Küstermann <roland@kuestermann.com>
   * @version $Id: class.ilSoapFileAdministration.php 12992 2007-01-25 10:04:26Z rkuester $
   *
   * @package ilias
   */
include_once './webservice/soap/classes/class.ilSoapAdministration.php';

class ilSoapFileAdministration extends ilSoapAdministration
{

    /**
     * add an File with id.
     *
     * @param string $session_id    current session
     * @param int $target_id refid of parent in repository
     * @param string $file_xml   qti xml description of test
     *
     * @return int reference id in the tree, 0 if not successful
     */
    public function addFile($sid, $target_id, $file_xml)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->__checkSession($sid)) {
            return $this->__raiseError($this->__getMessage(), $this->__getMessageCode());
        }
        global $DIC;

        $ilAccess   = $DIC['ilAccess'];

        if (!$target_obj = ilObjectFactory::getInstanceByRefId($target_id, false)) {
            return $this->__raiseError('No valid target given.', 'Client');
        }

        if (ilObject::_isInTrash($target_id)) {
            return $this->__raiseError("Parent with ID $target_id has been deleted.", 'CLIENT_TARGET_DELETED');
        }

        // Check access
        $allowed_types = array('cat', 'grp', 'crs', 'fold', 'root');
        if (!in_array($target_obj->getType(), $allowed_types)) {
            return $this->__raiseError('No valid target type. Target must be reference id of "course, group, category or folder"', 'Client');
        }

        if (!$ilAccess->checkAccess('create', '', $target_id, "file")) {
            return $this->__raiseError('No permission to create Files in target  ' . $target_id . '!', 'Client');
        }

        // create object, put it into the tree and use the parser to update the settings
        include_once './Modules/File/classes/class.ilFileXMLParser.php';
        include_once './Modules/File/classes/class.ilFileException.php';
        include_once './Modules/File/classes/class.ilObjFile.php';

        $file = new ilObjFile();
        try {
            $fileXMLParser = new ilFileXMLParser($file, $file_xml);

            if ($fileXMLParser->start()) {
                global $DIC;

                $ilLog = $DIC['ilLog'];

                $ilLog->write(__METHOD__ . ': File type: ' . $file->getFileType());

                $file->create();
                $file->createReference();
                $file->putInTree($target_id);
                $file->setPermissions($target_id);

                // we now can save the file contents since we know the obj id now.
                $fileXMLParser->setFileContents();
                #$file->update();

                return $file->getRefId();
            } else {
                return $this->__raiseError("Could not add file", "Server");
            }
        } catch (ilFileException $exception) {
            return $this->__raiseError($exception->getMessage(), $exception->getCode() == ilFileException::$ID_MISMATCH ? "Client" : "Server");
        }
    }


    /**
     * update a File with id.
     *
     * @param string $session_id    current session
     * @param int $ref_id   refid id of File in repository
     * @param string $file_xml   qti xml description of test
     *
     * @return boolean true, if update successful, false otherwise
     */
    public function updateFile($sid, $ref_id, $file_xml)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->__checkSession($sid)) {
            return $this->__raiseError($this->__getMessage(), $this->__getMessageCode());
        }
        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];
        $tree = $DIC['tree'];
        $ilLog = $DIC['ilLog'];
        $ilAccess = $DIC['ilAccess'];

        if (ilObject::_isInTrash($ref_id)) {
            return $this->__raiseError('Cannot perform update since file has been deleted.', 'CLIENT_OBJECT_DELETED');
        }
        // get obj_id
        if (!$obj_id = ilObject::_lookupObjectId($ref_id)) {
            return $this->__raiseError(
                'No File found for id: ' . $ref_id,
                'Client'
            );
        }

        // Check access
        $permission_ok = false;
        foreach ($ref_ids = ilObject::_getAllReferences($obj_id) as $ref_id) {
            if ($ilAccess->checkAccess('write', '', $ref_id)) {
                $permission_ok = true;
                break;
            }
        }

        if (!$permission_ok) {
            return $this->__raiseError(
                'No permission to edit the File with id: ' . $ref_id,
                'Server'
            );
        }


        $file = ilObjectFactory::getInstanceByObjId($obj_id, false);

        if (!is_object($file) || $file->getType()!= "file") {
            return $this->__raiseError(
                'Wrong obj id or type for File with id ' . $ref_id,
                'Server'
            );
        }

        include_once './Modules/File/classes/class.ilFileXMLParser.php';
        include_once './Modules/File/classes/class.ilFileException.php';
        $fileXMLParser = new ilFileXMLParser($file, $file_xml, $obj_id);

        try {
            if ($fileXMLParser->start()) {
                $fileXMLParser->updateFileContents();

                return  $file->update();
            }
        } catch (ilFileException $exception) {
            return $this->__raiseError(
               $exception->getMessage(),
               $exception->getCode() == ilFileException::$ID_MISMATCH ? "Client" : "Server"
           );
        }
        return false;
    }

    /**
     * get File xml
     *
     * @param string $sid
     * @param int $ref_id
     * @param boolean $attachFileContentsMode
     *
     * @return xml following ilias_file_x.dtd
     */

    public function getFileXML($sid, $ref_id, $attachFileContentsMode)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->__checkSession($sid)) {
            return $this->__raiseError($this->__getMessage(), $this->__getMessageCode());
        }
        if (!strlen($ref_id)) {
            return $this->__raiseError(
                'No ref id given. Aborting!',
                'Client'
            );
        }
        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];
        $tree = $DIC['tree'];
        $ilLog = $DIC['ilLog'];
        $ilAccess = $DIC['ilAccess'];


        // get obj_id
        if (!$obj_id = ilObject::_lookupObjectId($ref_id)) {
            return $this->__raiseError(
                'No File found for id: ' . $ref_id,
                'Client'
            );
        }

        if (ilObject::_isInTrash($ref_id)) {
            return $this->__raiseError("Object with ID $ref_id has been deleted.", 'Client');
        }

        // Check access
        $permission_ok = false;
        foreach ($ref_ids = ilObject::_getAllReferences($obj_id) as $ref_id) {
            if ($ilAccess->checkAccess('read', '', $ref_id)) {
                $permission_ok = true;
                break;
            }
        }

        if (!$permission_ok) {
            return $this->__raiseError(
                'No permission to edit the object with id: ' . $ref_id,
                'Server'
            );
        }

        $file = ilObjectFactory::getInstanceByObjId($obj_id, false);

        if (!is_object($file) || $file->getType()!= "file") {
            return $this->__raiseError(
                'Wrong obj id or type for File with id ' . $ref_id,
                'Server'
            );
        }
        // store into xml result set
        include_once './Modules/File/classes/class.ilFileXMLWriter.php';

        // create writer
        $xmlWriter = new ilFileXMLWriter();
        $xmlWriter->setFile($file);
        $xmlWriter->setAttachFileContents($attachFileContentsMode);
        $xmlWriter->start();

        return $xmlWriter->getXML();
    }
}
