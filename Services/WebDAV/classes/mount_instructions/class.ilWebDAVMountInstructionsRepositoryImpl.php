<?php declare(strict_types = 1);


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
 
class ilWebDAVMountInstructionsRepositoryImpl implements ilWebDAVMountInstructionsRepository
{
    const TABLE_MOUNT_INSTRUCTIONS = 'webdav_instructions';

    protected ilDBInterface $db;
    
    public function __construct(ilDBInterface $a_db)
    {
        $this->db = $a_db;
    }
    
    public function createMountInstructionsDocumentEntry(ilWebDAVMountInstructionsDocument $document) : void
    {
        $this->db->insert(
            // table
            self::TABLE_MOUNT_INSTRUCTIONS,

            // values
            array(
                'id' => array('int', $document->getId()),
                'title' => array('text', $document->getTitle()),
                'uploaded_instructions' => array('clob', $document->getUploadedInstructions()),
                'processed_instructions' => array('clob', $document->getProcessedInstructions()),
                'lng' => array('text', $document->getLanguage()),
                'creation_ts' => array('timestamp', $document->getCreationTs()),
                'modification_ts' => array('timestamp', $document->getModificationTs()),
                'owner_usr_id' => array('int', $document->getOwnerUsrId()),
                'last_modification_usr_id' => array('int', $document->getLastModificationUsrId()),
                'sorting' => array('int', $document->getSorting())
            )
        );
    }
    
    public function getNextMountInstructionsDocumentId() : int
    {
        if (!$this->db->sequenceExists(self::TABLE_MOUNT_INSTRUCTIONS)) {
            $this->db->createSequence(self::TABLE_MOUNT_INSTRUCTIONS);
        }

        return $this->db->nextId(self::TABLE_MOUNT_INSTRUCTIONS);
    }
    
    public function getHighestSortingNumber() : int
    {
        $query = "SELECT max(sorting) as max_sort FROM " . $this->db->quoteIdentifier(self::TABLE_MOUNT_INSTRUCTIONS);
        $result = $this->db->query($query);

        $row = $this->db->fetchAssoc($result);
        return isset($row) && !is_null($row['max_sort']) ? (int) $row['max_sort'] : 0;
    }
    
    public function getMountInstructionsDocumentById(int $id) : ilWebDAVMountInstructionsDocument
    {
        $query = "SELECT * FROM " . $this->db->quoteIdentifier(self::TABLE_MOUNT_INSTRUCTIONS)
            . " WHERE id=" . $this->db->quote($id, 'int');

        $result = $this->db->query($query);
        $record = $this->db->fetchAssoc($result);

        if (!$record) {
            throw new InvalidArgumentException("Document with the id $id not found");
        }

        return $this->buildDocumentFromDatabaseRecord($record);
    }
    
    public function getMountInstructionsByLanguage(string $language) : ilWebDAVMountInstructionsDocument
    {
        $query = "SELECT * FROM " . $this->db->quoteIdentifier(self::TABLE_MOUNT_INSTRUCTIONS)
            . " WHERE lng=" . $this->db->quote($language, 'text');

        $result = $this->db->query($query);
        $record = $this->db->fetchAssoc($result);

        if (!$record) {
            throw new InvalidArgumentException("Document for the language $language not found");
        }

        return $this->buildDocumentFromDatabaseRecord($record);
    }

    public function getAllMountInstructions() : array
    {
        $query = "SELECT * FROM " . $this->db->quoteIdentifier(self::TABLE_MOUNT_INSTRUCTIONS) . " ORDER BY sorting";
        $result = $this->db->query($query);

        $document_list = array();
        while ($record = $this->db->fetchAssoc($result)) {
            $document_list[] = $this->buildDocumentFromDatabaseRecord($record);
        }

        return $document_list;
    }
    
    public function doMountInstructionsExistByLanguage(string $language) : int
    {
        $query = "SELECT id FROM " . $this->db->quoteIdentifier(self::TABLE_MOUNT_INSTRUCTIONS)
            . " WHERE lng=" . $this->db->quote($language, 'text');

        $result = $this->db->query($query);
        $record = $this->db->fetchAssoc($result);
        
        return ($record === null ? 0 : (int) $record['id']);
    }
    
    public function updateMountInstructions(ilWebDAVMountInstructionsDocument $document) : void
    {
        $this->db->update(
            // table name
            self::TABLE_MOUNT_INSTRUCTIONS,

            // values to update
            array(
                'title' => array('text', $document->getTitle()),
                'lng' => array('text', $document->getLanguage()),
                'creation_ts' => array('timestamp', $document->getCreationTs()),
                'modification_ts' => array('timestamp', $document->getModificationTs()),
                'owner_usr_id' => array('int', $document->getOwnerUsrId()),
                'last_modification_usr_id' => array('int', $document->getLastModificationUsrId()),
                'sorting' => array('int', $document->getSorting())
            ),

            // which rows to update
            array(
                'id' => array('int', $document->getId()),
            )
        );
    }
    
    public function updateSortingValueById(int $id, int $a_new_sorting_value) : void
    {
        $this->db->update(
        // table name
            self::TABLE_MOUNT_INSTRUCTIONS,

            // values to update
            array(
                'sorting' => array('int', $a_new_sorting_value)
            ),

            // which rows to update
            array(
                'id' => array('int', $id),
            )
        );
    }
    
    public function deleteMountInstructionsById(int $id) : void
    {
        $query = "DELETE FROM " . $this->db->quoteIdentifier(self::TABLE_MOUNT_INSTRUCTIONS)
            . ' WHERE id=' . $this->db->quote($id, 'integer');

        $this->db->manipulate($query);
    }
    
    protected function buildDocumentFromDatabaseRecord(array $result) : ilWebDAVMountInstructionsDocument
    {
        return new ilWebDAVMountInstructionsDocument(
            (int) $result['id'],
            $result['title'],
            $result['uploaded_instructions'],
            $result['processed_instructions'],
            $result['lng'],
            $result['creation_ts'],
            $result['modification_ts'],
            (int) $result['owner_usr_id'],
            (int) $result['last_modification_usr_id'],
            (int) $result['sorting']
        );
    }
}
