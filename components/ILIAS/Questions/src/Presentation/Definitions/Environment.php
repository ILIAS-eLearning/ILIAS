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

namespace ILIAS\Questions\Presentation\Definitions;

use ILIAS\Questions\AnswerForm\Properties;
use ILIAS\Questions\Presentation\Layout\Factory;
use ILIAS\Data\UUID\Uuid;
use ILIAS\Language\Language;
use ILIAS\HTTP\Services as HTTPServices;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;

interface Environment
{
    public function getHttpServices(): HTTPServices;

    public function getLanguage(): Language;

    public function getRefinery(): Refinery;

    public function getUIFactory(): UIFactory;

    public function setEditAnswerFormBackTarget(): void;

    public function addEditAnswerFormSubTab(
        string $step,
        string $text
    ): void;

    public function activateEditAnswerFormSubTab(
        string $step
    ): void;

    public function getPresentationFactory(): Factory;

    public function getUrlBuilder(): URLBuilder;

    public function getTableRowIdToken(): URLBuilderToken;

    public function getTableRowIds(): array;

    public function getStep(): string;

    public function withDefaultStep(): self;

    public function getEditability(): Editability;

    public function isCapabilityRequired(
        string $capability
    ): bool;

    public function isInCreationContext(): bool;

    /**
     * Returns the answer form id of the current context either from the answer
     * form properties, if available, or from the corresponding get parameter.
     * There should always be one of the two ids availabe, but be aware that
     * is not guaranteed, e.g. if somebody messed with the URI.
     */
    public function getAnswerFormId(): ?Uuid;

    public function getAnswerFormProperties(): ?Properties;

    public function withAnswerFormProperties(
        Properties $properties
    ): self;

    public function withStepParameter(
        string $step
    ): self;

    public function withPreservedTableRowIdsParameter(): self;

    public function redirectTo(URLBuilder $target): void;
}
