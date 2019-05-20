<?php



/**
 * GlossaryDefinition
 */
class GlossaryDefinition
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var int
     */
    private $termId = '0';

    /**
     * @var string|null
     */
    private $shortText;

    /**
     * @var int
     */
    private $nr = '0';

    /**
     * @var int
     */
    private $shortTextDirty = '0';


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
     * Set termId.
     *
     * @param int $termId
     *
     * @return GlossaryDefinition
     */
    public function setTermId($termId)
    {
        $this->termId = $termId;

        return $this;
    }

    /**
     * Get termId.
     *
     * @return int
     */
    public function getTermId()
    {
        return $this->termId;
    }

    /**
     * Set shortText.
     *
     * @param string|null $shortText
     *
     * @return GlossaryDefinition
     */
    public function setShortText($shortText = null)
    {
        $this->shortText = $shortText;

        return $this;
    }

    /**
     * Get shortText.
     *
     * @return string|null
     */
    public function getShortText()
    {
        return $this->shortText;
    }

    /**
     * Set nr.
     *
     * @param int $nr
     *
     * @return GlossaryDefinition
     */
    public function setNr($nr)
    {
        $this->nr = $nr;

        return $this;
    }

    /**
     * Get nr.
     *
     * @return int
     */
    public function getNr()
    {
        return $this->nr;
    }

    /**
     * Set shortTextDirty.
     *
     * @param int $shortTextDirty
     *
     * @return GlossaryDefinition
     */
    public function setShortTextDirty($shortTextDirty)
    {
        $this->shortTextDirty = $shortTextDirty;

        return $this;
    }

    /**
     * Get shortTextDirty.
     *
     * @return int
     */
    public function getShortTextDirty()
    {
        return $this->shortTextDirty;
    }
}
