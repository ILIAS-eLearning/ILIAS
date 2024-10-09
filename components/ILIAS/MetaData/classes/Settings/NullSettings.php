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

namespace ILIAS\MetaData\Settings;

class NullSettings implements SettingsInterface
{
    public function isCopyrightSelectionActive(): bool
    {
        return false;
    }

    public function activateCopyrightSelection(bool $status): void
    {
    }

    public function isOAIPMHActive(): bool
    {
        return false;
    }

    public function activateOAIPMH(bool $status): void
    {
    }

    public function getOAIRepositoryName(): string
    {
        return '';
    }

    public function saveOAIRepositoryName(string $oai_repository_name): void
    {
    }

    public function getOAIIdentifierPrefix(): string
    {
        return '';
    }

    public function saveOAIIdentifierPrefix(string $oai_identifier_prefix): void
    {
    }

    public function getOAIContactMail(): string
    {
        return '';
    }

    public function saveOAIContactMail(string $oai_contact_mail): void
    {
    }
}
