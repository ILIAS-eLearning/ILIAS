<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateQueueEntry
{
    private int $objId;
    private int $userId;
    private string $adapterClass;
    private string $state;
    private ?int $startedTimestamp;
    private ?int $id;
    private int $templateId;

    public function __construct(
        int $objId,
        int $userId,
        string $adapterClass,
        string $state,
        int $templateId,
        ?int $startedTimestamp = null,
        ?int $id = null
    ) {
        $this->objId = $objId;
        $this->userId = $userId;
        $this->adapterClass = $adapterClass;
        $this->state = $state;
        $this->templateId = $templateId;
        $this->startedTimestamp = $startedTimestamp;
        $this->id = $id;
    }

    public function getObjId() : int
    {
        return $this->objId;
    }

    public function getUserId() : int
    {
        return $this->userId;
    }

    public function getAdapterClass() : string
    {
        return $this->adapterClass;
    }

    public function getState() : string
    {
        return $this->state;
    }

    public function getStartedTimestamp() : int
    {
        return $this->startedTimestamp;
    }

    public function getId() : ?int
    {
        return $this->id;
    }

    public function getTemplateId() : int
    {
        return $this->templateId;
    }
}
