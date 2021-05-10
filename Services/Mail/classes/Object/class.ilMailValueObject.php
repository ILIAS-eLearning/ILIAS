<?php declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author Niels Theen <ntheen@databay.de>
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailValueObject
{
    /** @var int */
    private $actorUsrId;

    /** @var string */
    private $recipients;

    /** @var string */
    private $recipientsCC;

    /** @var string */
    private $recipientsBCC;

    /** @var string */
    private $subject;

    /** @var string */
    private $body;

    /** @var string[] */
    private $attachments = [];

    /** @var bool */
    private $usePlaceholders;

    /** @var bool */
    private $saveInSentBox;

    /** @var null|string */
    private $templateContextId;

    /** @var null|array */
    private $templateContextParams;

    /**
     * @param int $actorUsrId
     * @param string $recipients
     * @param string $recipientsCC
     * @param string $recipientsBCC
     * @param string $subject
     * @param string $body
     * @param string[] $attachments
     * @param bool $usePlaceholders
     * @param bool $saveInSentBox
     */
    public function __construct(
        int $actorUsrId,
        string $recipients,
        string $recipientsCC,
        string $recipientsBCC,
        string $subject,
        string $body,
        array $attachments,
        bool $usePlaceholders = false,
        bool $saveInSentBox = false
    ) {
        $this->actorUsrId = $actorUsrId;
        $this->recipients = $recipients;
        $this->recipientsCC = $recipientsCC;
        $this->recipientsBCC = $recipientsBCC;
        $this->subject = $subject;
        $this->body = $body;
        $this->attachments = array_filter(array_map('trim', $attachments));
        $this->usePlaceholders = $usePlaceholders;
        $this->saveInSentBox = $saveInSentBox;
    }

    public function getActorUsrId() : int
    {
        return $this->actorUsrId;
    }

    public function getRecipients() : string
    {
        return $this->recipients;
    }

    public function getRecipientsCC() : string
    {
        return $this->recipientsCC;
    }

    public function getRecipientsBCC() : string
    {
        return $this->recipientsBCC;
    }

    public function getSubject() : string
    {
        return $this->subject;
    }

    public function getBody() : string
    {
        return $this->body;
    }

    /**
     * @return string[]
     */
    public function getAttachments() : array
    {
        return $this->attachments;
    }

    public function isUsingPlaceholders() : bool
    {
        return $this->usePlaceholders;
    }

    public function shouldSaveInSentBox() : bool
    {
        return $this->saveInSentBox;
    }

    public function getTemplateContextId() : ?string
    {
        return $this->templateContextId;
    }

    public function getTemplateContextParams() : ?array
    {
        return $this->templateContextParams;
    }

    public function withTemplateContextId(?string $id) : self
    {
        $clone = clone $this;
        $clone->templateContextId = $id;
        return $clone;
    }

    public function withTemplateContextParams(?array $params) : self
    {
        $clone = clone $this;
        $clone->templateContextParams = $params;
        return $clone;
    }
}
