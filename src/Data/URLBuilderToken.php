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
 ********************************************************************
 */
declare(strict_types=1);

namespace ILIAS\Data;

class URLBuilderToken implements URLBuilderTokenInterface
{
    public const TOKEN_LENGTH = 12;
    private array $namespace;
    private string $name;
    private string $token;

    public function __construct(array $namespace, string $name)
    {
        $this->namespace = $namespace;
        $this->name = $name;
        $this->token = $this->createToken();
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getName(): string
    {
        return implode(URLBuilder::SEPARATOR, $this->namespace) . URLBuilder::SEPARATOR . $this->name;
    }

    private function createToken(): string
    {
        return bin2hex(random_bytes(self::TOKEN_LENGTH));
    }

    public function render(): string
    {
        $namespace = [];
        foreach ($this->namespace as $name) {
            $namespace[] = '"' . $name . '"';
        }
        $output = 'new il.UI.core.URLBuilderToken([' . implode(',', $namespace) . '], "' . $this->name . '", "' . $this->token . '")';
        return $output;
    }
}
