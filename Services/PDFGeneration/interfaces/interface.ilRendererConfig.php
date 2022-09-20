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

interface ilRendererConfig
{
    /**
     * @param ilPropertyFormGUI $form The config form for the administration
     * @param string $service Service Title
     * @param string $purpose Purpose Title
     * @return void
     */
    public function addConfigElementsToForm(ilPropertyFormGUI $form, string $service, string $purpose): void;

    /**
     * @param ilPropertyFormGUI $form The config form for the administration
     * @param string $service Service Title
     * @param string $purpose Purpose Title
     * @param array<string, mixed> $config KV-array with config
     * @return void
     */
    public function populateConfigElementsInForm(
        ilPropertyFormGUI $form,
        string $service,
        string $purpose,
        array $config
    ): void;

    /**
     * @param ilPropertyFormGUI $form The config form for the administration
     * @param string $service Service Title
     * @param string $purpose Purpose Title
     * @return bool True, if the form holds a valid config
     */
    public function validateConfigInForm(ilPropertyFormGUI $form, string $service, string $purpose): bool;

    /**
     * @param ilPropertyFormGUI $form The config form for the administration
     * @param string $service Service Title
     * @param string $purpose Purpose Title
     * @return array<string, mixed> KV-array with config
     */
    public function getConfigFromForm(ilPropertyFormGUI $form, string $service, string $purpose): array;

    /**
     * @param string $service Service Title
     * @param string $purpose Purpose Title
     * @return mixed KV-array with config or an config instance
     */
    public function getDefaultConfig(string $service, string $purpose);
}
