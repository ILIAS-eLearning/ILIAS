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

namespace ILIAS\Questions\Presentation\Layout\Definitions;

use ILIAS\Questions\AnswerForm\Properties;
use ILIAS\Questions\Presentation\Definitions\Editability;
use ILIAS\UI\URLBuilder;

interface Environment
{
    public function getDefinitionsFactory(): Factory;
    public function getUrlBuilder(): URLBuilder;
    public function getUrlBuilderWithStepParameter(string $step): URLBuilder;
    public function getStep(): string;
    public function withDefaultStep(): self;
    public function getEditability(): Editability;
    public function getProperties(): ?Properties;
    public function withProperties(Properties $properties): self;
}
