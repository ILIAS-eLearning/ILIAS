<?php



/**
 * QplAMdef
 */
class QplAMdef
{
    /**
     * @var int
     */
    private $defId = '0';

    /**
     * @var int
     */
    private $questionFi = '0';

    /**
     * @var string|null
     */
    private $definition;

    /**
     * @var int
     */
    private $ident = '0';

    /**
     * @var string|null
     */
    private $picture;


    /**
     * Get defId.
     *
     * @return int
     */
    public function getDefId()
    {
        return $this->defId;
    }

    /**
     * Set questionFi.
     *
     * @param int $questionFi
     *
     * @return QplAMdef
     */
    public function setQuestionFi($questionFi)
    {
        $this->questionFi = $questionFi;

        return $this;
    }

    /**
     * Get questionFi.
     *
     * @return int
     */
    public function getQuestionFi()
    {
        return $this->questionFi;
    }

    /**
     * Set definition.
     *
     * @param string|null $definition
     *
     * @return QplAMdef
     */
    public function setDefinition($definition = null)
    {
        $this->definition = $definition;

        return $this;
    }

    /**
     * Get definition.
     *
     * @return string|null
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * Set ident.
     *
     * @param int $ident
     *
     * @return QplAMdef
     */
    public function setIdent($ident)
    {
        $this->ident = $ident;

        return $this;
    }

    /**
     * Get ident.
     *
     * @return int
     */
    public function getIdent()
    {
        return $this->ident;
    }

    /**
     * Set picture.
     *
     * @param string|null $picture
     *
     * @return QplAMdef
     */
    public function setPicture($picture = null)
    {
        $this->picture = $picture;

        return $this;
    }

    /**
     * Get picture.
     *
     * @return string|null
     */
    public function getPicture()
    {
        return $this->picture;
    }
}
