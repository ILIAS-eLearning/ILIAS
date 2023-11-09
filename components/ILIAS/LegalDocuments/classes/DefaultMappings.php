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

namespace ILIAS\LegalDocuments;

use ILIAS\Refinery\Constraint;
use ILIAS\LegalDocuments\Condition\Definition\RoleDefinition;
use ILIAS\LegalDocuments\Condition\Definition\UserLanguageDefinition;
use ILIAS\LegalDocuments\Condition\Definition\UserCountryDefinition;
use ILIAS\LegalDocuments\ConsumerToolbox\UI;
use ILIAS\DI\Container;

class DefaultMappings
{
    public function __construct(
        private readonly string $id,
        private readonly Container $container
    ) {
    }

    public function conditionDefinitions(): array
    {
        $ui = new UI(
            $this->id,
            $this->container->ui()->factory(),
            $this->container->ui()->mainTemplate(),
            $this->container->language()
        );

        $this->container->language()->loadLanguageModule('rbac');
        $this->container->language()->loadLanguageModule('meta');

        $required = fn(array $options): Constraint => $this->container->refinery()->custom()->constraint(
            static fn(?string $s): bool => $s !== null && isset($options[$s]),
            static fn(): string => $ui->txt('msg_input_is_required')
        );

        return [
            'usr_global_role' => new RoleDefinition($ui, $this->container['ilObjDataCache'], $this->container->rbac()->review(), $required),
            'usr_language' => new UserLanguageDefinition($ui, $this->container->language()->getInstalledLanguages(), $required),
            'usr_country' => new UserCountryDefinition($ui, $required),
        ];
    }

    public function contentAsComponent(): array
    {
        return [
            'html' => fn($x) => $this->container->ui()->factory()->legacy($x->value()),
        ];
    }
}
