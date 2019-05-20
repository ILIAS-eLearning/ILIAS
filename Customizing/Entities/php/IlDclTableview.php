<?php



/**
 * IlDclTableview
 */
class IlDclTableview
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var int
     */
    private $tableId = '0';

    /**
     * @var string
     */
    private $title = '';

    /**
     * @var string|null
     */
    private $roles;

    /**
     * @var string|null
     */
    private $description;

    /**
     * @var int|null
     */
    private $tableviewOrder;


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
     * Set tableId.
     *
     * @param int $tableId
     *
     * @return IlDclTableview
     */
    public function setTableId($tableId)
    {
        $this->tableId = $tableId;

        return $this;
    }

    /**
     * Get tableId.
     *
     * @return int
     */
    public function getTableId()
    {
        return $this->tableId;
    }

    /**
     * Set title.
     *
     * @param string $title
     *
     * @return IlDclTableview
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
     * Set roles.
     *
     * @param string|null $roles
     *
     * @return IlDclTableview
     */
    public function setRoles($roles = null)
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * Get roles.
     *
     * @return string|null
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Set description.
     *
     * @param string|null $description
     *
     * @return IlDclTableview
     */
    public function setDescription($description = null)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set tableviewOrder.
     *
     * @param int|null $tableviewOrder
     *
     * @return IlDclTableview
     */
    public function setTableviewOrder($tableviewOrder = null)
    {
        $this->tableviewOrder = $tableviewOrder;

        return $this;
    }

    /**
     * Get tableviewOrder.
     *
     * @return int|null
     */
    public function getTableviewOrder()
    {
        return $this->tableviewOrder;
    }
}
