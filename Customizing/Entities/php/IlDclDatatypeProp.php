<?php



/**
 * IlDclDatatypeProp
 */
class IlDclDatatypeProp
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var int|null
     */
    private $datatypeId;

    /**
     * @var string|null
     */
    private $title;

    /**
     * @var bool
     */
    private $inputformat = '0';


    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set datatypeId.
     *
     * @param int|null $datatypeId
     *
     * @return IlDclDatatypeProp
     */
    public function setDatatypeId($datatypeId = null)
    {
        $this->datatypeId = $datatypeId;

        return $this;
    }

    /**
     * Get datatypeId.
     *
     * @return int|null
     */
    public function getDatatypeId()
    {
        return $this->datatypeId;
    }

    /**
     * Set title.
     *
     * @param string|null $title
     *
     * @return IlDclDatatypeProp
     */
    public function setTitle($title = null)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string|null
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set inputformat.
     *
     * @param bool $inputformat
     *
     * @return IlDclDatatypeProp
     */
    public function setInputformat($inputformat)
    {
        $this->inputformat = $inputformat;

        return $this;
    }

    /**
     * Get inputformat.
     *
     * @return bool
     */
    public function getInputformat()
    {
        return $this->inputformat;
    }
}
