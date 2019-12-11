<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceAcceptanceEntity
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceAcceptanceEntity
{
    /** @var int */
    protected $id = 0;

    /** @var int */
    protected $user_id = 0;

    /** @var string */
    protected $text = '';

    /** @var int */
    protected $timestamp = 0;

    /** @var string */
    protected $hash = '';

    /** @var string */
    protected $title = '';

    /** @var int */
    protected $document_id = 0;

    /** @var string */
    protected $criteria = '';

    /**
     * @return string
     */
    public function getHash() : string
    {
        return $this->hash;
    }

    /**
     * @param string $hash
     * @return \ilTermsOfServiceAcceptanceEntity
     */
    public function withHash(string $hash) : self
    {
        $clone = clone $this;

        $clone->hash = $hash;

        return $clone;
    }

    /**
     * @return string
     */
    public function getText() : string
    {
        return $this->text;
    }

    /**
     * @param string $text
     * @return \ilTermsOfServiceAcceptanceEntity
     */
    public function withText(string $text) : self
    {
        $clone = clone $this;

        $clone->text = $text;

        return $clone;
    }

    /**
     * @return int
     */
    public function getTimestamp() : int
    {
        return $this->timestamp;
    }

    /**
     * @param int $timestamp
     * @return \ilTermsOfServiceAcceptanceEntity
     */
    public function withTimestamp(int $timestamp) : self
    {
        $clone = clone $this;

        $clone->timestamp = $timestamp;

        return $clone;
    }

    /**
     * @return int
     */
    public function getUserId() : int
    {
        return $this->user_id;
    }

    /**
     * @param int $user_id
     * @return \ilTermsOfServiceAcceptanceEntity
     */
    public function withUserId(int $user_id) : self
    {
        $clone = clone $this;

        $clone->user_id = $user_id;

        return $clone;
    }

    /**
     * @return int
     */
    public function getId() : int
    {
        return $this->id;
    }

    /**
     * @param $id
     * @return \ilTermsOfServiceAcceptanceEntity
     */
    public function withId(int $id) : self
    {
        $clone = clone $this;

        $clone->id = $id;

        return $clone;
    }

    /**
     * @return string
     */
    public function getTitle() : string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return \ilTermsOfServiceAcceptanceEntity
     */
    public function withTitle(string $title) : self
    {
        $clone = clone $this;

        $clone->title = $title;

        return $clone;
    }

    /**
     * @return int
     */
    public function getDocumentId() : int
    {
        return $this->document_id;
    }

    /**
     * @param int $document_id
     * @return \ilTermsOfServiceAcceptanceEntity
     */
    public function withDocumentId(int $document_id) : self
    {
        $clone = clone $this;

        $clone->document_id = $document_id;

        return $clone;
    }

    /**
     * @return string
     */
    public function getSerializedCriteria() : string
    {
        return $this->criteria;
    }

    /**
     * @param string $criteria
     * @return \ilTermsOfServiceAcceptanceEntity
     */
    public function withSerializedCriteria(string $criteria) : self
    {
        $clone = clone $this;

        $clone->criteria = $criteria;

        return $clone;
    }
}
