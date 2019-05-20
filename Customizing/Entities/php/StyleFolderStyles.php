<?php



/**
 * StyleFolderStyles
 */
class StyleFolderStyles
{
    /**
     * @var int
     */
    private $folderId = '0';

    /**
     * @var int
     */
    private $styleId = '0';


    /**
     * Set folderId.
     *
     * @param int $folderId
     *
     * @return StyleFolderStyles
     */
    public function setFolderId($folderId)
    {
        $this->folderId = $folderId;

        return $this;
    }

    /**
     * Get folderId.
     *
     * @return int
     */
    public function getFolderId()
    {
        return $this->folderId;
    }

    /**
     * Set styleId.
     *
     * @param int $styleId
     *
     * @return StyleFolderStyles
     */
    public function setStyleId($styleId)
    {
        $this->styleId = $styleId;

        return $this;
    }

    /**
     * Get styleId.
     *
     * @return int
     */
    public function getStyleId()
    {
        return $this->styleId;
    }
}
