<?php



/**
 * SahsSc13SeqTemplts
 */
class SahsSc13SeqTemplts
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var string|null
     */
    private $identifier;

    /**
     * @var string|null
     */
    private $filename;


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
     * Set identifier.
     *
     * @param string|null $identifier
     *
     * @return SahsSc13SeqTemplts
     */
    public function setIdentifier($identifier = null)
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * Get identifier.
     *
     * @return string|null
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Set filename.
     *
     * @param string|null $filename
     *
     * @return SahsSc13SeqTemplts
     */
    public function setFilename($filename = null)
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * Get filename.
     *
     * @return string|null
     */
    public function getFilename()
    {
        return $this->filename;
    }
}
