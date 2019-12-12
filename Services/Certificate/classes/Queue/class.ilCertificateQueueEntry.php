<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateQueueEntry
{
    /**
     * @var int
     */
    private $objId;

    /**
     * @var int
     */
    private $userId;

    /**
     * @var string
     */
    private $adapterClass;

    /**
     * @var string
     */
    private $state;

    /**
     * @var int
     */
    private $startedTimestamp;

    /**
     * @var int|null
     */
    private $id;

    /**
     * @var int
     */
    private $templateId;

    /**
     * @param integer $objId
     * @param integer $userId
     * @param string $adapterClass
     * @param string $state
     * @param $templateId
     * @param integer|null $startedTimestamp
     * @param integer|null $id
     */
    public function __construct(
        int $objId,
        int $userId,
        string $adapterClass,
        string $state,
        int $templateId,
        int $startedTimestamp = null,
        int $id = null
    ) {
        $this->objId = $objId;
        $this->userId = $userId;
        $this->adapterClass = $adapterClass;
        $this->state = $state;
        $this->templateId = $templateId;
        $this->startedTimestamp = $startedTimestamp;
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getObjId() : int
    {
        return $this->objId;
    }

    /**
     * @return int
     */
    public function getUserId() : int
    {
        return $this->userId;
    }

    /**
     * @return string
     */
    public function getAdapterClass() : string
    {
        return $this->adapterClass;
    }

    /**
     * @return string
     */
    public function getState() : string
    {
        return $this->state;
    }

    /**
     * @return int
     */
    public function getStartedTimestamp()
    {
        return $this->startedTimestamp;
    }

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getTemplateId() : int
    {
        return $this->templateId;
    }
}
