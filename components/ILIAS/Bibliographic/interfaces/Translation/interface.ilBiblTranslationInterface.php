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

/**
 * Interface ilBiblTranslationInterface
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilBiblTranslationInterface
{
    public function getId(): ?int;

    public function setId(int $id): void;

    public function getFieldId(): int;

    public function setFieldId(int $field_id): void;

    public function getLanguageKey(): string;

    public function setLanguageKey(string $language_key): void;

    public function getTranslation(): string;

    public function setTranslation(string $translation): void;

    public function getDescription(): string;

    public function setDescription(string $description): void;

    public function store(): void;
}
