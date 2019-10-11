<?php


final class ilWebDAVMountInstructionsDocument
{
    /** @var int */
    protected $id;

    /** @var string */
    protected $title;

    /** @var string */
    protected $uploaded_instructions;

    /** @var string */
    protected $processed_instructions;

    /** @var string */
    protected $language;

    /** @var null */
    protected $creation_ts;

    /** @var null */
    protected $modification_ts;

    /** @var int */
    protected $owner_usr_id;

    /** @var int */
    protected $last_modified_usr_id;

    /** @var int */
    protected $sorting;

    public function __construct(int $a_id = 0,
                                string $a_title = "",
                                string $a_uploaded_instructions = "",
                                string $a_processed_instructions = "",
                                string $a_language = "",
                                $a_creation_ts = NULL,
                                $a_modification_ts = NULL,
                                int $a_owner_usr_id = 0,
                                int $a_last_modified_usr_id = 0,
                                int $a_sorting = 0
    )
    {
        $this->id = $a_id;
        $this->title = $a_title;
        $this->uploaded_instructions = $a_uploaded_instructions;
        $this->processed_instructions = $a_processed_instructions;
        $this->language = $a_language;
        $this->creation_ts = $a_creation_ts;
        $this->modification_ts = $a_modification_ts;
        $this->owner_usr_id = $a_owner_usr_id;
        $this->last_modified_usr_id = $a_last_modified_usr_id;
        $this->sorting = $a_sorting;
    }

    /**
     * @return int
     */
    public function getId() : int
    {
        return (int) $this->id;
    }

    /**
     * @return string
     */
    public function getTitle() : string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getUploadedInstructions() : string
    {
        return $this->uploaded_instructions;
    }

    /**
     * @return string
     */
    public function getProcessedInstructions() : string
    {
        return $this->processed_instructions;
    }

    /**
     * @return string
     */
    public function getLanguage() : string
    {
        return $this->language;
    }

    /**
     * @return null
     */
    public function getCreationTs()
    {
        return $this->creation_ts;
    }

    /**
     * @return null
     */
    public function getModificationTs()
    {
        return $this->modification_ts;
    }

    /**
     * @return int
     */
    public function getOwnerUsrId() : int
    {
        return $this->owner_usr_id;
    }

    /**
     * @return int
     */
    public function getLastModificationUsrId() : int
    {
        return $this->last_modified_usr_id;
    }

    /**
     * @return int
     */
    public function getSorting() : int
    {
        return $this->sorting;
    }
}