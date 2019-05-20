<?php



/**
 * FrmSettings
 */
class FrmSettings
{
    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var int
     */
    private $defaultView = '0';

    /**
     * @var bool
     */
    private $anonymized = '0';

    /**
     * @var bool
     */
    private $statisticsEnabled = '0';

    /**
     * @var bool
     */
    private $postActivation = '0';

    /**
     * @var bool
     */
    private $adminForceNoti = '0';

    /**
     * @var bool
     */
    private $userToggleNoti = '0';

    /**
     * @var bool
     */
    private $presetSubject = '1';

    /**
     * @var string|null
     */
    private $notificationType;

    /**
     * @var bool
     */
    private $addReSubject = '0';

    /**
     * @var bool
     */
    private $markModPosts = '0';

    /**
     * @var int
     */
    private $threadSorting = '0';

    /**
     * @var bool
     */
    private $threadRating = '0';

    /**
     * @var bool
     */
    private $fileUploadAllowed = '0';


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
     * Set defaultView.
     *
     * @param int $defaultView
     *
     * @return FrmSettings
     */
    public function setDefaultView($defaultView)
    {
        $this->defaultView = $defaultView;

        return $this;
    }

    /**
     * Get defaultView.
     *
     * @return int
     */
    public function getDefaultView()
    {
        return $this->defaultView;
    }

    /**
     * Set anonymized.
     *
     * @param bool $anonymized
     *
     * @return FrmSettings
     */
    public function setAnonymized($anonymized)
    {
        $this->anonymized = $anonymized;

        return $this;
    }

    /**
     * Get anonymized.
     *
     * @return bool
     */
    public function getAnonymized()
    {
        return $this->anonymized;
    }

    /**
     * Set statisticsEnabled.
     *
     * @param bool $statisticsEnabled
     *
     * @return FrmSettings
     */
    public function setStatisticsEnabled($statisticsEnabled)
    {
        $this->statisticsEnabled = $statisticsEnabled;

        return $this;
    }

    /**
     * Get statisticsEnabled.
     *
     * @return bool
     */
    public function getStatisticsEnabled()
    {
        return $this->statisticsEnabled;
    }

    /**
     * Set postActivation.
     *
     * @param bool $postActivation
     *
     * @return FrmSettings
     */
    public function setPostActivation($postActivation)
    {
        $this->postActivation = $postActivation;

        return $this;
    }

    /**
     * Get postActivation.
     *
     * @return bool
     */
    public function getPostActivation()
    {
        return $this->postActivation;
    }

    /**
     * Set adminForceNoti.
     *
     * @param bool $adminForceNoti
     *
     * @return FrmSettings
     */
    public function setAdminForceNoti($adminForceNoti)
    {
        $this->adminForceNoti = $adminForceNoti;

        return $this;
    }

    /**
     * Get adminForceNoti.
     *
     * @return bool
     */
    public function getAdminForceNoti()
    {
        return $this->adminForceNoti;
    }

    /**
     * Set userToggleNoti.
     *
     * @param bool $userToggleNoti
     *
     * @return FrmSettings
     */
    public function setUserToggleNoti($userToggleNoti)
    {
        $this->userToggleNoti = $userToggleNoti;

        return $this;
    }

    /**
     * Get userToggleNoti.
     *
     * @return bool
     */
    public function getUserToggleNoti()
    {
        return $this->userToggleNoti;
    }

    /**
     * Set presetSubject.
     *
     * @param bool $presetSubject
     *
     * @return FrmSettings
     */
    public function setPresetSubject($presetSubject)
    {
        $this->presetSubject = $presetSubject;

        return $this;
    }

    /**
     * Get presetSubject.
     *
     * @return bool
     */
    public function getPresetSubject()
    {
        return $this->presetSubject;
    }

    /**
     * Set notificationType.
     *
     * @param string|null $notificationType
     *
     * @return FrmSettings
     */
    public function setNotificationType($notificationType = null)
    {
        $this->notificationType = $notificationType;

        return $this;
    }

    /**
     * Get notificationType.
     *
     * @return string|null
     */
    public function getNotificationType()
    {
        return $this->notificationType;
    }

    /**
     * Set addReSubject.
     *
     * @param bool $addReSubject
     *
     * @return FrmSettings
     */
    public function setAddReSubject($addReSubject)
    {
        $this->addReSubject = $addReSubject;

        return $this;
    }

    /**
     * Get addReSubject.
     *
     * @return bool
     */
    public function getAddReSubject()
    {
        return $this->addReSubject;
    }

    /**
     * Set markModPosts.
     *
     * @param bool $markModPosts
     *
     * @return FrmSettings
     */
    public function setMarkModPosts($markModPosts)
    {
        $this->markModPosts = $markModPosts;

        return $this;
    }

    /**
     * Get markModPosts.
     *
     * @return bool
     */
    public function getMarkModPosts()
    {
        return $this->markModPosts;
    }

    /**
     * Set threadSorting.
     *
     * @param int $threadSorting
     *
     * @return FrmSettings
     */
    public function setThreadSorting($threadSorting)
    {
        $this->threadSorting = $threadSorting;

        return $this;
    }

    /**
     * Get threadSorting.
     *
     * @return int
     */
    public function getThreadSorting()
    {
        return $this->threadSorting;
    }

    /**
     * Set threadRating.
     *
     * @param bool $threadRating
     *
     * @return FrmSettings
     */
    public function setThreadRating($threadRating)
    {
        $this->threadRating = $threadRating;

        return $this;
    }

    /**
     * Get threadRating.
     *
     * @return bool
     */
    public function getThreadRating()
    {
        return $this->threadRating;
    }

    /**
     * Set fileUploadAllowed.
     *
     * @param bool $fileUploadAllowed
     *
     * @return FrmSettings
     */
    public function setFileUploadAllowed($fileUploadAllowed)
    {
        $this->fileUploadAllowed = $fileUploadAllowed;

        return $this;
    }

    /**
     * Get fileUploadAllowed.
     *
     * @return bool
     */
    public function getFileUploadAllowed()
    {
        return $this->fileUploadAllowed;
    }
}
