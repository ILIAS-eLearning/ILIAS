<?php



/**
 * IlDclTable
 */
class IlDclTable
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var string|null
     */
    private $title;

    /**
     * @var bool
     */
    private $addPerm = '1';

    /**
     * @var bool
     */
    private $editPerm = '1';

    /**
     * @var bool
     */
    private $deletePerm = '1';

    /**
     * @var bool
     */
    private $editByOwner = '1';

    /**
     * @var bool
     */
    private $limited = '0';

    /**
     * @var \DateTime|null
     */
    private $limitStart;

    /**
     * @var \DateTime|null
     */
    private $limitEnd;

    /**
     * @var bool
     */
    private $isVisible = '1';

    /**
     * @var bool|null
     */
    private $exportEnabled;

    /**
     * @var string|null
     */
    private $description;

    /**
     * @var string
     */
    private $defaultSortFieldId = '0';

    /**
     * @var string
     */
    private $defaultSortFieldOrder = 'asc';

    /**
     * @var int
     */
    private $publicComments = '0';

    /**
     * @var int
     */
    private $viewOwnRecordsPerm = '0';

    /**
     * @var bool
     */
    private $deleteByOwner = '0';

    /**
     * @var bool
     */
    private $saveConfirmation = '0';

    /**
     * @var bool
     */
    private $importEnabled = '1';

    /**
     * @var int|null
     */
    private $tableOrder;


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
     * Set objId.
     *
     * @param int $objId
     *
     * @return IlDclTable
     */
    public function setObjId($objId)
    {
        $this->objId = $objId;

        return $this;
    }

    /**
     * Get objId.
     *
     * @return int
     */
    public function getObjId()
    {
        return $this->objId;
    }

    /**
     * Set title.
     *
     * @param string|null $title
     *
     * @return IlDclTable
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
     * Set addPerm.
     *
     * @param bool $addPerm
     *
     * @return IlDclTable
     */
    public function setAddPerm($addPerm)
    {
        $this->addPerm = $addPerm;

        return $this;
    }

    /**
     * Get addPerm.
     *
     * @return bool
     */
    public function getAddPerm()
    {
        return $this->addPerm;
    }

    /**
     * Set editPerm.
     *
     * @param bool $editPerm
     *
     * @return IlDclTable
     */
    public function setEditPerm($editPerm)
    {
        $this->editPerm = $editPerm;

        return $this;
    }

    /**
     * Get editPerm.
     *
     * @return bool
     */
    public function getEditPerm()
    {
        return $this->editPerm;
    }

    /**
     * Set deletePerm.
     *
     * @param bool $deletePerm
     *
     * @return IlDclTable
     */
    public function setDeletePerm($deletePerm)
    {
        $this->deletePerm = $deletePerm;

        return $this;
    }

    /**
     * Get deletePerm.
     *
     * @return bool
     */
    public function getDeletePerm()
    {
        return $this->deletePerm;
    }

    /**
     * Set editByOwner.
     *
     * @param bool $editByOwner
     *
     * @return IlDclTable
     */
    public function setEditByOwner($editByOwner)
    {
        $this->editByOwner = $editByOwner;

        return $this;
    }

    /**
     * Get editByOwner.
     *
     * @return bool
     */
    public function getEditByOwner()
    {
        return $this->editByOwner;
    }

    /**
     * Set limited.
     *
     * @param bool $limited
     *
     * @return IlDclTable
     */
    public function setLimited($limited)
    {
        $this->limited = $limited;

        return $this;
    }

    /**
     * Get limited.
     *
     * @return bool
     */
    public function getLimited()
    {
        return $this->limited;
    }

    /**
     * Set limitStart.
     *
     * @param \DateTime|null $limitStart
     *
     * @return IlDclTable
     */
    public function setLimitStart($limitStart = null)
    {
        $this->limitStart = $limitStart;

        return $this;
    }

    /**
     * Get limitStart.
     *
     * @return \DateTime|null
     */
    public function getLimitStart()
    {
        return $this->limitStart;
    }

    /**
     * Set limitEnd.
     *
     * @param \DateTime|null $limitEnd
     *
     * @return IlDclTable
     */
    public function setLimitEnd($limitEnd = null)
    {
        $this->limitEnd = $limitEnd;

        return $this;
    }

    /**
     * Get limitEnd.
     *
     * @return \DateTime|null
     */
    public function getLimitEnd()
    {
        return $this->limitEnd;
    }

    /**
     * Set isVisible.
     *
     * @param bool $isVisible
     *
     * @return IlDclTable
     */
    public function setIsVisible($isVisible)
    {
        $this->isVisible = $isVisible;

        return $this;
    }

    /**
     * Get isVisible.
     *
     * @return bool
     */
    public function getIsVisible()
    {
        return $this->isVisible;
    }

    /**
     * Set exportEnabled.
     *
     * @param bool|null $exportEnabled
     *
     * @return IlDclTable
     */
    public function setExportEnabled($exportEnabled = null)
    {
        $this->exportEnabled = $exportEnabled;

        return $this;
    }

    /**
     * Get exportEnabled.
     *
     * @return bool|null
     */
    public function getExportEnabled()
    {
        return $this->exportEnabled;
    }

    /**
     * Set description.
     *
     * @param string|null $description
     *
     * @return IlDclTable
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
     * Set defaultSortFieldId.
     *
     * @param string $defaultSortFieldId
     *
     * @return IlDclTable
     */
    public function setDefaultSortFieldId($defaultSortFieldId)
    {
        $this->defaultSortFieldId = $defaultSortFieldId;

        return $this;
    }

    /**
     * Get defaultSortFieldId.
     *
     * @return string
     */
    public function getDefaultSortFieldId()
    {
        return $this->defaultSortFieldId;
    }

    /**
     * Set defaultSortFieldOrder.
     *
     * @param string $defaultSortFieldOrder
     *
     * @return IlDclTable
     */
    public function setDefaultSortFieldOrder($defaultSortFieldOrder)
    {
        $this->defaultSortFieldOrder = $defaultSortFieldOrder;

        return $this;
    }

    /**
     * Get defaultSortFieldOrder.
     *
     * @return string
     */
    public function getDefaultSortFieldOrder()
    {
        return $this->defaultSortFieldOrder;
    }

    /**
     * Set publicComments.
     *
     * @param int $publicComments
     *
     * @return IlDclTable
     */
    public function setPublicComments($publicComments)
    {
        $this->publicComments = $publicComments;

        return $this;
    }

    /**
     * Get publicComments.
     *
     * @return int
     */
    public function getPublicComments()
    {
        return $this->publicComments;
    }

    /**
     * Set viewOwnRecordsPerm.
     *
     * @param int $viewOwnRecordsPerm
     *
     * @return IlDclTable
     */
    public function setViewOwnRecordsPerm($viewOwnRecordsPerm)
    {
        $this->viewOwnRecordsPerm = $viewOwnRecordsPerm;

        return $this;
    }

    /**
     * Get viewOwnRecordsPerm.
     *
     * @return int
     */
    public function getViewOwnRecordsPerm()
    {
        return $this->viewOwnRecordsPerm;
    }

    /**
     * Set deleteByOwner.
     *
     * @param bool $deleteByOwner
     *
     * @return IlDclTable
     */
    public function setDeleteByOwner($deleteByOwner)
    {
        $this->deleteByOwner = $deleteByOwner;

        return $this;
    }

    /**
     * Get deleteByOwner.
     *
     * @return bool
     */
    public function getDeleteByOwner()
    {
        return $this->deleteByOwner;
    }

    /**
     * Set saveConfirmation.
     *
     * @param bool $saveConfirmation
     *
     * @return IlDclTable
     */
    public function setSaveConfirmation($saveConfirmation)
    {
        $this->saveConfirmation = $saveConfirmation;

        return $this;
    }

    /**
     * Get saveConfirmation.
     *
     * @return bool
     */
    public function getSaveConfirmation()
    {
        return $this->saveConfirmation;
    }

    /**
     * Set importEnabled.
     *
     * @param bool $importEnabled
     *
     * @return IlDclTable
     */
    public function setImportEnabled($importEnabled)
    {
        $this->importEnabled = $importEnabled;

        return $this;
    }

    /**
     * Get importEnabled.
     *
     * @return bool
     */
    public function getImportEnabled()
    {
        return $this->importEnabled;
    }

    /**
     * Set tableOrder.
     *
     * @param int|null $tableOrder
     *
     * @return IlDclTable
     */
    public function setTableOrder($tableOrder = null)
    {
        $this->tableOrder = $tableOrder;

        return $this;
    }

    /**
     * Get tableOrder.
     *
     * @return int|null
     */
    public function getTableOrder()
    {
        return $this->tableOrder;
    }
}
