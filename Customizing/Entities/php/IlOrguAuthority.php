<?php



/**
 * IlOrguAuthority
 */
class IlOrguAuthority
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var bool|null
     */
    private $over;

    /**
     * @var bool|null
     */
    private $scope;

    /**
     * @var bool|null
     */
    private $positionId;


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
     * Set over.
     *
     * @param bool|null $over
     *
     * @return IlOrguAuthority
     */
    public function setOver($over = null)
    {
        $this->over = $over;

        return $this;
    }

    /**
     * Get over.
     *
     * @return bool|null
     */
    public function getOver()
    {
        return $this->over;
    }

    /**
     * Set scope.
     *
     * @param bool|null $scope
     *
     * @return IlOrguAuthority
     */
    public function setScope($scope = null)
    {
        $this->scope = $scope;

        return $this;
    }

    /**
     * Get scope.
     *
     * @return bool|null
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * Set positionId.
     *
     * @param bool|null $positionId
     *
     * @return IlOrguAuthority
     */
    public function setPositionId($positionId = null)
    {
        $this->positionId = $positionId;

        return $this;
    }

    /**
     * Get positionId.
     *
     * @return bool|null
     */
    public function getPositionId()
    {
        return $this->positionId;
    }
}
