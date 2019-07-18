<?php


class ilWebDAVMountInstructionsRepositoryImpl implements ilWebDAVMountInstructionsRepository
{
    const TABLE_MOUNT_INSTRUCTIONS = 'webdav_mount_instructions';

    /** @var ilDBInterface */
    protected $db;

    /**
     * ilWebDAVMountInstructionsRepository constructor.
     *
     * @param ilDBInterface $a_db
     */
    public function __construct(ilDBInterface $a_db)
    {
        $this->db = $a_db;
    }

    public function createMountInstructionsDocumentEntry(ilWebDAVMountInstructionsDocument $document)
    {
        $this->db->insert(
            // table
            self::TABLE_MOUNT_INSTRUCTIONS,

            // values
            array(
            'id' => array('int', $document->getId()),
            'title' => array('text', $document->getTitle()),
            'uploaded_instructions' => array('text', $document->getUploadedInstructions()),
            'processed_instructions' => array('text', $document->getProcessedInstructions()),
            'lng' => array('text', $document->getLanguage()),
            'creation_ts' => array('timestamp', $document->getCreationTs()),
            'modification_ts' => array('timestamp', $document->getModificationTs()),
            'owner_usr_id' => array('int', $document->getOwnerUsrId()),
            'last_modification_usr_id' => array('int', $document->getLastModificationUsrId()),
            'sorting' => array('int', $document->getSorting())
        ));
    }

    public function getNextMountInstructionsDocumentId() : int
    {
        if(!$this->db->sequenceExists(self::TABLE_MOUNT_INSTRUCTIONS))
        {
            $this->db->createSequence(self::TABLE_MOUNT_INSTRUCTIONS);
        }

        return $this->db->nextId(self::TABLE_MOUNT_INSTRUCTIONS);
    }

    public function getHighestSortingNumber() : int
    {
        $query = "SELECT max(sorting) as max_sort FROM " . $this->db->quoteIdentifier(self::TABLE_MOUNT_INSTRUCTIONS);
        $result = $this->db->query($query);

        $row = $this->db->fetchAssoc($result);
        return isset($row) && !is_null($row['max_sort']) ? $row['max_sort'] : 0;
    }

    public function getMountInstructionsDocumentById(int $id) : ilWebDAVMountInstructionsDocument
    {
        $query = "SELECT * FROM " . $this->db->quoteIdentifier(self::TABLE_MOUNT_INSTRUCTIONS)
            . " WHERE id=" . $this->db->quote($id, 'int');

        $result = $this->db->query($query);
        $record = $this->db->fetchAssoc($result);

        if(!$record)
        {
            throw new ilException("Document with the id $id not found");
        }

        return $this->buildDocumentFromDatabaseRecord($record);
    }

    public function getMountInstructionsByLanguage(string $language) : ilWebDAVMountInstructionsDocument
    {
        $query = "SELECT * FROM " . $this->db->quoteIdentifier(self::TABLE_MOUNT_INSTRUCTIONS)
            . " WHERE lng=" . $this->db->quote($language, 'text');

        $result = $this->db->query($query);
        $record = $this->db->fetchAssoc($result);

        if(!$record)
        {
            throw new ilException("Document for the language $language not found");
        }

        return $this->buildDocumentFromDatabaseRecord($record);
    }

    public function getAllMountInstructions() : array
    {
        $query = "SELECT * FROM " . $this->db->quoteIdentifier(self::TABLE_MOUNT_INSTRUCTIONS);
        $result = $this->db->query($query);

        $document_list = array();
        while($record = $this->db->fetchAssoc($result))
        {
            $document_list[] = $this->buildDocumentFromDatabaseRecord($record);
        }

        return $document_list;
    }

    public function updateMountInstructionsById(ilWebDAVMountInstructionsDocument $document)
    {
        $this->db->update(
            // table name
            self::TABLE_MOUNT_INSTRUCTIONS,

            // values to update
            array(
                'title' => array('text', $document->getTitle()),
                'uploaded_instructions' => array('text', $document->getUploadedInstructions()),
                'processed_instructions' => array('text', $document->getProcessedInstructions()),
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

    public function deleteMountInstructionsById(int $id)
    {
        $query = "DELETE FROM " . $this->db->quoteIdentifier(self::TABLE_MOUNT_INSTRUCTIONS)
            . ' WHERE id=' . $this->db->quote($id, 'integer');

        $this->db->manipulate($query);
    }

    protected function buildDocumentFromDatabaseRecord(array $result)
    {
        return new ilWebDAVMountInstructionsDocument(
            $result['id'],
            $result['title'],
            $result['uploaded_instructions'],
            $result['processed_instructions'],
            $result['lng'],
            $result['creation_ts'],
            $result['modification_ts'],
            $result['owner_usr_id'],
            $result['last_modification_usr_id'],
            $result['sorting']
        );
    }
}