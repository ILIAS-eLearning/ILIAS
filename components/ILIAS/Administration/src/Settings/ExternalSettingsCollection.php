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

namespace ILIAS\Administration\Settings;

/**
 * All external settings contributed by the components.
 */
class ExternalSettingsCollection
{
    /**
     * @param ExternalSettings[] $external_settings
     */
    public function __construct(
        private readonly array $external_settings
    ) {
    }

    /**
     * @return ExternalSettings[] sorted by position on the form
     */
    public function forForm(SettingsForm $form): array
    {
        $settings = array_values(array_filter(
            $this->external_settings,
            static fn(ExternalSettings $settings): bool => in_array($form, $settings->getForms(), true)
        ));
        usort(
            $settings,
            static fn(ExternalSettings $a, ExternalSettings $b): int => $a->getPosition($form) <=> $b->getPosition($form)
        );
        return $settings;
    }
}
