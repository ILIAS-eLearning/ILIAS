<?php declare(strict_types=1);

/**
 * Class ilStudyProgrammeAdvancedMetadataRecord
 *
 * @author Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilStudyProgrammeAdvancedMetadataRecord
{
    protected int $id;
    protected int $type_id = 0;
    protected int $rec_id = 0;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function setId(int $id) : void
    {
        $this->id = $id;
    }

    public function getTypeId() : int
    {
        return $this->type_id;
    }

    public function setTypeId(int $type_id) : void
    {
        $this->type_id = $type_id;
    }

    public function getRecId() : int
    {
        return $this->rec_id;
    }

    public function setRecId(int $rec_id) : void
    {
        $this->rec_id = $rec_id;
    }
}
