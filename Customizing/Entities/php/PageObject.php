<?php



/**
 * PageObject
 */
class PageObject
{
    /**
     * @var int
     */
    private $pageId = '0';

    /**
     * @var string
     */
    private $parentType = 'lm';

    /**
     * @var string
     */
    private $lang = '-';

    /**
     * @var int|null
     */
    private $parentId;

    /**
     * @var string|null
     */
    private $content;

    /**
     * @var int|null
     */
    private $lastChangeUser;

    /**
     * @var int|null
     */
    private $viewCnt = '0';

    /**
     * @var \DateTime|null
     */
    private $lastChange;

    /**
     * @var \DateTime|null
     */
    private $created;

    /**
     * @var int|null
     */
    private $createUser;

    /**
     * @var string|null
     */
    private $renderMd5;

    /**
     * @var string|null
     */
    private $renderedContent;

    /**
     * @var \DateTime|null
     */
    private $renderedTime;

    /**
     * @var \DateTime|null
     */
    private $activationStart;

    /**
     * @var \DateTime|null
     */
    private $activationEnd;

    /**
     * @var bool
     */
    private $active = '1';

    /**
     * @var bool
     */
    private $isEmpty = '0';

    /**
     * @var bool|null
     */
    private $inactiveElements = '0';

    /**
     * @var bool|null
     */
    private $intLinks = '0';

    /**
     * @var bool
     */
    private $showActivationInfo = '0';

    /**
     * @var int|null
     */
    private $editLockUser;

    /**
     * @var int
     */
    private $editLockTs = '0';


    /**
     * Set pageId.
     *
     * @param int $pageId
     *
     * @return PageObject
     */
    public function setPageId($pageId)
    {
        $this->pageId = $pageId;

        return $this;
    }

    /**
     * Get pageId.
     *
     * @return int
     */
    public function getPageId()
    {
        return $this->pageId;
    }

    /**
     * Set parentType.
     *
     * @param string $parentType
     *
     * @return PageObject
     */
    public function setParentType($parentType)
    {
        $this->parentType = $parentType;

        return $this;
    }

    /**
     * Get parentType.
     *
     * @return string
     */
    public function getParentType()
    {
        return $this->parentType;
    }

    /**
     * Set lang.
     *
     * @param string $lang
     *
     * @return PageObject
     */
    public function setLang($lang)
    {
        $this->lang = $lang;

        return $this;
    }

    /**
     * Get lang.
     *
     * @return string
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * Set parentId.
     *
     * @param int|null $parentId
     *
     * @return PageObject
     */
    public function setParentId($parentId = null)
    {
        $this->parentId = $parentId;

        return $this;
    }

    /**
     * Get parentId.
     *
     * @return int|null
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * Set content.
     *
     * @param string|null $content
     *
     * @return PageObject
     */
    public function setContent($content = null)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content.
     *
     * @return string|null
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set lastChangeUser.
     *
     * @param int|null $lastChangeUser
     *
     * @return PageObject
     */
    public function setLastChangeUser($lastChangeUser = null)
    {
        $this->lastChangeUser = $lastChangeUser;

        return $this;
    }

    /**
     * Get lastChangeUser.
     *
     * @return int|null
     */
    public function getLastChangeUser()
    {
        return $this->lastChangeUser;
    }

    /**
     * Set viewCnt.
     *
     * @param int|null $viewCnt
     *
     * @return PageObject
     */
    public function setViewCnt($viewCnt = null)
    {
        $this->viewCnt = $viewCnt;

        return $this;
    }

    /**
     * Get viewCnt.
     *
     * @return int|null
     */
    public function getViewCnt()
    {
        return $this->viewCnt;
    }

    /**
     * Set lastChange.
     *
     * @param \DateTime|null $lastChange
     *
     * @return PageObject
     */
    public function setLastChange($lastChange = null)
    {
        $this->lastChange = $lastChange;

        return $this;
    }

    /**
     * Get lastChange.
     *
     * @return \DateTime|null
     */
    public function getLastChange()
    {
        return $this->lastChange;
    }

    /**
     * Set created.
     *
     * @param \DateTime|null $created
     *
     * @return PageObject
     */
    public function setCreated($created = null)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created.
     *
     * @return \DateTime|null
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set createUser.
     *
     * @param int|null $createUser
     *
     * @return PageObject
     */
    public function setCreateUser($createUser = null)
    {
        $this->createUser = $createUser;

        return $this;
    }

    /**
     * Get createUser.
     *
     * @return int|null
     */
    public function getCreateUser()
    {
        return $this->createUser;
    }

    /**
     * Set renderMd5.
     *
     * @param string|null $renderMd5
     *
     * @return PageObject
     */
    public function setRenderMd5($renderMd5 = null)
    {
        $this->renderMd5 = $renderMd5;

        return $this;
    }

    /**
     * Get renderMd5.
     *
     * @return string|null
     */
    public function getRenderMd5()
    {
        return $this->renderMd5;
    }

    /**
     * Set renderedContent.
     *
     * @param string|null $renderedContent
     *
     * @return PageObject
     */
    public function setRenderedContent($renderedContent = null)
    {
        $this->renderedContent = $renderedContent;

        return $this;
    }

    /**
     * Get renderedContent.
     *
     * @return string|null
     */
    public function getRenderedContent()
    {
        return $this->renderedContent;
    }

    /**
     * Set renderedTime.
     *
     * @param \DateTime|null $renderedTime
     *
     * @return PageObject
     */
    public function setRenderedTime($renderedTime = null)
    {
        $this->renderedTime = $renderedTime;

        return $this;
    }

    /**
     * Get renderedTime.
     *
     * @return \DateTime|null
     */
    public function getRenderedTime()
    {
        return $this->renderedTime;
    }

    /**
     * Set activationStart.
     *
     * @param \DateTime|null $activationStart
     *
     * @return PageObject
     */
    public function setActivationStart($activationStart = null)
    {
        $this->activationStart = $activationStart;

        return $this;
    }

    /**
     * Get activationStart.
     *
     * @return \DateTime|null
     */
    public function getActivationStart()
    {
        return $this->activationStart;
    }

    /**
     * Set activationEnd.
     *
     * @param \DateTime|null $activationEnd
     *
     * @return PageObject
     */
    public function setActivationEnd($activationEnd = null)
    {
        $this->activationEnd = $activationEnd;

        return $this;
    }

    /**
     * Get activationEnd.
     *
     * @return \DateTime|null
     */
    public function getActivationEnd()
    {
        return $this->activationEnd;
    }

    /**
     * Set active.
     *
     * @param bool $active
     *
     * @return PageObject
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active.
     *
     * @return bool
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set isEmpty.
     *
     * @param bool $isEmpty
     *
     * @return PageObject
     */
    public function setIsEmpty($isEmpty)
    {
        $this->isEmpty = $isEmpty;

        return $this;
    }

    /**
     * Get isEmpty.
     *
     * @return bool
     */
    public function getIsEmpty()
    {
        return $this->isEmpty;
    }

    /**
     * Set inactiveElements.
     *
     * @param bool|null $inactiveElements
     *
     * @return PageObject
     */
    public function setInactiveElements($inactiveElements = null)
    {
        $this->inactiveElements = $inactiveElements;

        return $this;
    }

    /**
     * Get inactiveElements.
     *
     * @return bool|null
     */
    public function getInactiveElements()
    {
        return $this->inactiveElements;
    }

    /**
     * Set intLinks.
     *
     * @param bool|null $intLinks
     *
     * @return PageObject
     */
    public function setIntLinks($intLinks = null)
    {
        $this->intLinks = $intLinks;

        return $this;
    }

    /**
     * Get intLinks.
     *
     * @return bool|null
     */
    public function getIntLinks()
    {
        return $this->intLinks;
    }

    /**
     * Set showActivationInfo.
     *
     * @param bool $showActivationInfo
     *
     * @return PageObject
     */
    public function setShowActivationInfo($showActivationInfo)
    {
        $this->showActivationInfo = $showActivationInfo;

        return $this;
    }

    /**
     * Get showActivationInfo.
     *
     * @return bool
     */
    public function getShowActivationInfo()
    {
        return $this->showActivationInfo;
    }

    /**
     * Set editLockUser.
     *
     * @param int|null $editLockUser
     *
     * @return PageObject
     */
    public function setEditLockUser($editLockUser = null)
    {
        $this->editLockUser = $editLockUser;

        return $this;
    }

    /**
     * Get editLockUser.
     *
     * @return int|null
     */
    public function getEditLockUser()
    {
        return $this->editLockUser;
    }

    /**
     * Set editLockTs.
     *
     * @param int $editLockTs
     *
     * @return PageObject
     */
    public function setEditLockTs($editLockTs)
    {
        $this->editLockTs = $editLockTs;

        return $this;
    }

    /**
     * Get editLockTs.
     *
     * @return int
     */
    public function getEditLockTs()
    {
        return $this->editLockTs;
    }
}
