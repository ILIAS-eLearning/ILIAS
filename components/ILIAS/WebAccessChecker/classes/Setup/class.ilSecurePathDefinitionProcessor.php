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

class ilSecurePathDefinitionProcessor implements ilComponentDefinitionProcessor
{
    protected ?string $component = null;

    public function __construct(protected \ilDBInterface $db)
    {
    }

    public function purge(): void
    {
    }

    public function beginComponent(string $component, string $type): void
    {
        $this->component = $type . "/" . $component;
    }

    public function endComponent(string $component, string $type): void
    {
    }

    public function beginTag(string $name, array $attributes): void
    {
        if ($name !== "secure_path") {
            return;
        }

        try {
            $ilWACSecurePath = ilWACSecurePath::findOrFail($attributes["path"]);
        } catch (arException) {
            $ilWACSecurePath = new ilWACSecurePath();
            $ilWACSecurePath->setPath($attributes["path"]);
            $ilWACSecurePath->create();
        }
        $ilWACSecurePath->setCheckingClass($attributes["checking-class"]);
        $ilWACSecurePath->setInSecFolder((bool) ($attributes["in-sec-folder"] ?? null));
        $ilWACSecurePath->setComponentDirectory(realpath(__DIR__ . "/../../../../../" . $this->component));
        $ilWACSecurePath->update();
    }

    public function endTag(string $name): void
    {
    }
}
