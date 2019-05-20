<?php



/**
 * SvyQstMatrix
 */
class SvyQstMatrix
{
    /**
     * @var int
     */
    private $questionFi = '0';

    /**
     * @var int
     */
    private $subtype = '0';

    /**
     * @var string|null
     */
    private $columnSeparators = '0';

    /**
     * @var string|null
     */
    private $rowSeparators = '0';

    /**
     * @var string|null
     */
    private $neutralColumnSeparator = '1';

    /**
     * @var int
     */
    private $columnPlaceholders = '0';

    /**
     * @var string|null
     */
    private $legend = '0';

    /**
     * @var string|null
     */
    private $singlelineRowCaption = '0';

    /**
     * @var string|null
     */
    private $repeatColumnHeader = '0';

    /**
     * @var string|null
     */
    private $columnHeaderPosition = '0';

    /**
     * @var string|null
     */
    private $randomRows = '0';

    /**
     * @var string|null
     */
    private $columnOrder = '0';

    /**
     * @var string|null
     */
    private $columnImages = '0';

    /**
     * @var string|null
     */
    private $rowImages = '0';

    /**
     * @var string|null
     */
    private $bipolarAdjective1;

    /**
     * @var string|null
     */
    private $bipolarAdjective2;

    /**
     * @var string|null
     */
    private $layout;

    /**
     * @var int
     */
    private $tstamp = '0';


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
     * Set subtype.
     *
     * @param int $subtype
     *
     * @return SvyQstMatrix
     */
    public function setSubtype($subtype)
    {
        $this->subtype = $subtype;

        return $this;
    }

    /**
     * Get subtype.
     *
     * @return int
     */
    public function getSubtype()
    {
        return $this->subtype;
    }

    /**
     * Set columnSeparators.
     *
     * @param string|null $columnSeparators
     *
     * @return SvyQstMatrix
     */
    public function setColumnSeparators($columnSeparators = null)
    {
        $this->columnSeparators = $columnSeparators;

        return $this;
    }

    /**
     * Get columnSeparators.
     *
     * @return string|null
     */
    public function getColumnSeparators()
    {
        return $this->columnSeparators;
    }

    /**
     * Set rowSeparators.
     *
     * @param string|null $rowSeparators
     *
     * @return SvyQstMatrix
     */
    public function setRowSeparators($rowSeparators = null)
    {
        $this->rowSeparators = $rowSeparators;

        return $this;
    }

    /**
     * Get rowSeparators.
     *
     * @return string|null
     */
    public function getRowSeparators()
    {
        return $this->rowSeparators;
    }

    /**
     * Set neutralColumnSeparator.
     *
     * @param string|null $neutralColumnSeparator
     *
     * @return SvyQstMatrix
     */
    public function setNeutralColumnSeparator($neutralColumnSeparator = null)
    {
        $this->neutralColumnSeparator = $neutralColumnSeparator;

        return $this;
    }

    /**
     * Get neutralColumnSeparator.
     *
     * @return string|null
     */
    public function getNeutralColumnSeparator()
    {
        return $this->neutralColumnSeparator;
    }

    /**
     * Set columnPlaceholders.
     *
     * @param int $columnPlaceholders
     *
     * @return SvyQstMatrix
     */
    public function setColumnPlaceholders($columnPlaceholders)
    {
        $this->columnPlaceholders = $columnPlaceholders;

        return $this;
    }

    /**
     * Get columnPlaceholders.
     *
     * @return int
     */
    public function getColumnPlaceholders()
    {
        return $this->columnPlaceholders;
    }

    /**
     * Set legend.
     *
     * @param string|null $legend
     *
     * @return SvyQstMatrix
     */
    public function setLegend($legend = null)
    {
        $this->legend = $legend;

        return $this;
    }

    /**
     * Get legend.
     *
     * @return string|null
     */
    public function getLegend()
    {
        return $this->legend;
    }

    /**
     * Set singlelineRowCaption.
     *
     * @param string|null $singlelineRowCaption
     *
     * @return SvyQstMatrix
     */
    public function setSinglelineRowCaption($singlelineRowCaption = null)
    {
        $this->singlelineRowCaption = $singlelineRowCaption;

        return $this;
    }

    /**
     * Get singlelineRowCaption.
     *
     * @return string|null
     */
    public function getSinglelineRowCaption()
    {
        return $this->singlelineRowCaption;
    }

    /**
     * Set repeatColumnHeader.
     *
     * @param string|null $repeatColumnHeader
     *
     * @return SvyQstMatrix
     */
    public function setRepeatColumnHeader($repeatColumnHeader = null)
    {
        $this->repeatColumnHeader = $repeatColumnHeader;

        return $this;
    }

    /**
     * Get repeatColumnHeader.
     *
     * @return string|null
     */
    public function getRepeatColumnHeader()
    {
        return $this->repeatColumnHeader;
    }

    /**
     * Set columnHeaderPosition.
     *
     * @param string|null $columnHeaderPosition
     *
     * @return SvyQstMatrix
     */
    public function setColumnHeaderPosition($columnHeaderPosition = null)
    {
        $this->columnHeaderPosition = $columnHeaderPosition;

        return $this;
    }

    /**
     * Get columnHeaderPosition.
     *
     * @return string|null
     */
    public function getColumnHeaderPosition()
    {
        return $this->columnHeaderPosition;
    }

    /**
     * Set randomRows.
     *
     * @param string|null $randomRows
     *
     * @return SvyQstMatrix
     */
    public function setRandomRows($randomRows = null)
    {
        $this->randomRows = $randomRows;

        return $this;
    }

    /**
     * Get randomRows.
     *
     * @return string|null
     */
    public function getRandomRows()
    {
        return $this->randomRows;
    }

    /**
     * Set columnOrder.
     *
     * @param string|null $columnOrder
     *
     * @return SvyQstMatrix
     */
    public function setColumnOrder($columnOrder = null)
    {
        $this->columnOrder = $columnOrder;

        return $this;
    }

    /**
     * Get columnOrder.
     *
     * @return string|null
     */
    public function getColumnOrder()
    {
        return $this->columnOrder;
    }

    /**
     * Set columnImages.
     *
     * @param string|null $columnImages
     *
     * @return SvyQstMatrix
     */
    public function setColumnImages($columnImages = null)
    {
        $this->columnImages = $columnImages;

        return $this;
    }

    /**
     * Get columnImages.
     *
     * @return string|null
     */
    public function getColumnImages()
    {
        return $this->columnImages;
    }

    /**
     * Set rowImages.
     *
     * @param string|null $rowImages
     *
     * @return SvyQstMatrix
     */
    public function setRowImages($rowImages = null)
    {
        $this->rowImages = $rowImages;

        return $this;
    }

    /**
     * Get rowImages.
     *
     * @return string|null
     */
    public function getRowImages()
    {
        return $this->rowImages;
    }

    /**
     * Set bipolarAdjective1.
     *
     * @param string|null $bipolarAdjective1
     *
     * @return SvyQstMatrix
     */
    public function setBipolarAdjective1($bipolarAdjective1 = null)
    {
        $this->bipolarAdjective1 = $bipolarAdjective1;

        return $this;
    }

    /**
     * Get bipolarAdjective1.
     *
     * @return string|null
     */
    public function getBipolarAdjective1()
    {
        return $this->bipolarAdjective1;
    }

    /**
     * Set bipolarAdjective2.
     *
     * @param string|null $bipolarAdjective2
     *
     * @return SvyQstMatrix
     */
    public function setBipolarAdjective2($bipolarAdjective2 = null)
    {
        $this->bipolarAdjective2 = $bipolarAdjective2;

        return $this;
    }

    /**
     * Get bipolarAdjective2.
     *
     * @return string|null
     */
    public function getBipolarAdjective2()
    {
        return $this->bipolarAdjective2;
    }

    /**
     * Set layout.
     *
     * @param string|null $layout
     *
     * @return SvyQstMatrix
     */
    public function setLayout($layout = null)
    {
        $this->layout = $layout;

        return $this;
    }

    /**
     * Get layout.
     *
     * @return string|null
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * Set tstamp.
     *
     * @param int $tstamp
     *
     * @return SvyQstMatrix
     */
    public function setTstamp($tstamp)
    {
        $this->tstamp = $tstamp;

        return $this;
    }

    /**
     * Get tstamp.
     *
     * @return int
     */
    public function getTstamp()
    {
        return $this->tstamp;
    }
}
