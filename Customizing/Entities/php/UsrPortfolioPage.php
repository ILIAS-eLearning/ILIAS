<?php



/**
 * UsrPortfolioPage
 */
class UsrPortfolioPage
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var int
     */
    private $portfolioId = '0';

    /**
     * @var string
     */
    private $title = '';

    /**
     * @var int
     */
    private $orderNr = '0';

    /**
     * @var bool
     */
    private $type = '1';


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
     * Set portfolioId.
     *
     * @param int $portfolioId
     *
     * @return UsrPortfolioPage
     */
    public function setPortfolioId($portfolioId)
    {
        $this->portfolioId = $portfolioId;

        return $this;
    }

    /**
     * Get portfolioId.
     *
     * @return int
     */
    public function getPortfolioId()
    {
        return $this->portfolioId;
    }

    /**
     * Set title.
     *
     * @param string $title
     *
     * @return UsrPortfolioPage
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set orderNr.
     *
     * @param int $orderNr
     *
     * @return UsrPortfolioPage
     */
    public function setOrderNr($orderNr)
    {
        $this->orderNr = $orderNr;

        return $this;
    }

    /**
     * Get orderNr.
     *
     * @return int
     */
    public function getOrderNr()
    {
        return $this->orderNr;
    }

    /**
     * Set type.
     *
     * @param bool $type
     *
     * @return UsrPortfolioPage
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return bool
     */
    public function getType()
    {
        return $this->type;
    }
}
