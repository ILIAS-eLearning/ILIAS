<?php

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

declare(strict_types=1);

namespace ILIAS\Test\Settings\Templates;

use ILIAS\Test\ExportImport\Exportable;

class PersonalSettingsTemplate implements Exportable
{
    public function __construct(
        private int $id,
        private int $user_id,
        private string $name,
        private string $description,
        private string $author,
        private \DateTimeImmutable $created_at,
        private int $settings_id = -1
    ) {
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

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->created_at;
    }

    public function getSettingsId(): int
    {
        return $this->settings_id;
    }

    public function toExport(): array
    {
        return [
            'id' => $this->getId(),
            'user_id' => $this->getUserId(),
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'author' => $this->getAuthor(),
            'created_at' => $this->getCreatedAt()->format(\DateTimeInterface::ATOM),
        ];
    }

    public static function fromExport(array $data): static
    {
        return new self(
            (int) $data['id'],
            (int) $data['user_id'],
            (string) $data['name'],
            (string) $data['description'],
            (string) $data['author'],
            new \DateTimeImmutable($data['created_at'])
        );
    }
}
