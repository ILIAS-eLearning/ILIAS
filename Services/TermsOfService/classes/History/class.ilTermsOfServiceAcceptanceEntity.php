<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Class ilTermsOfServiceAcceptanceEntity
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceAcceptanceEntity
{
    protected int $id = 0;
    protected int $user_id = 0;
    protected string $text = '';
    protected int $timestamp = 0;
    protected string $hash = '';
    protected string $title = '';
    protected int $document_id = 0;
    protected string $criteria = '';

    public function getHash(): string
    {
        return $this->hash;
    }

    public function withHash(string $hash): self
    {
        $clone = clone $this;

        $clone->hash = $hash;

        return $clone;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function withText(string $text): self
    {
        $clone = clone $this;

        $clone->text = $text;

        return $clone;
    }

    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    public function withTimestamp(int $timestamp): self
    {
        $clone = clone $this;

        $clone->timestamp = $timestamp;

        return $clone;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function withUserId(int $user_id): self
    {
        $clone = clone $this;

        $clone->user_id = $user_id;

        return $clone;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function withId(int $id): self
    {
        $clone = clone $this;

        $clone->id = $id;

        return $clone;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function withTitle(string $title): self
    {
        $clone = clone $this;

        $clone->title = $title;

        return $clone;
    }

    public function getDocumentId(): int
    {
        return $this->document_id;
    }

    public function withDocumentId(int $document_id): self
    {
        $clone = clone $this;

        $clone->document_id = $document_id;

        return $clone;
    }

    public function getSerializedCriteria(): string
    {
        return $this->criteria;
    }

    public function withSerializedCriteria(string $criteria): self
    {
        $clone = clone $this;

        $clone->criteria = $criteria;

        return $clone;
    }
}
