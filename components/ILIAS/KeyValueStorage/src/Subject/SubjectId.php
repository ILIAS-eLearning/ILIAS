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

namespace ILIAS\KeyValueStorage\Subject;

/**
 * Identifies a subject within the provider that names it.
 *
 * KeyValueStorage validates the format only. It does not assign meaning to the
 * id; that is up to the provider.
 */
final readonly class SubjectId
{
    public const int MAX_LENGTH = 128;

    private string $provider;
    private string $provider_class;

    public function __construct(SubjectProvider $provider, private string $id)
    {
        $this->provider = $provider->name();
        $this->provider_class = $provider::class;

        $this->assertIdentifier(
            $this->provider,
            SubjectProvider::MAX_NAME_LENGTH,
            '/^[a-z][a-z0-9_]*$/',
            'Subject provider name'
        );
        $this->assertIdentifier($id, self::MAX_LENGTH, '/^[a-z0-9_]+$/', 'Subject id');
    }

    public function provider(): string
    {
        return $this->provider;
    }

    public function id(): string
    {
        return $this->id;
    }

    /**
     * @internal KeyValueStorage checks it against the contributed provider of that name.
     * @return class-string<SubjectProvider>
     */
    public function providerClass(): string
    {
        return $this->provider_class;
    }

    private function assertIdentifier(string $value, int $max_length, string $pattern, string $what): void
    {
        if ($value === '') {
            throw new \InvalidArgumentException($what . ' must not be empty.');
        }

        $length = \mb_strlen($value, 'UTF-8');
        if ($length > $max_length) {
            throw new \InvalidArgumentException(
                $what . ' must not exceed ' . $max_length . ' characters, got ' . $length . '.'
            );
        }

        if (!\preg_match($pattern, $value)) {
            throw new \InvalidArgumentException(
                $what . ' must be a lowercase identifier, got "' . $value . '".'
            );
        }
    }
}
