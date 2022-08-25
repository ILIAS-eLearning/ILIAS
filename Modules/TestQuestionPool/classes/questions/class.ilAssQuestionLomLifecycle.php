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

/**
 * Class ilAssQuestionLomLifecycle
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @package     Modules/Test
 */
class ilAssQuestionLomLifecycle
{
    public const DRAFT = 'draft';
    public const FINAL = 'final';
    public const REVISED = 'revised';
    public const UNAVAILABLE = 'unavailable';

    protected string $identifier;

    /**
     * ilAssQuestionLomLifecycle constructor.
     * @param mixed $identifier
     * @throws ilTestQuestionPoolInvalidArgumentException
     */
    public function __construct($identifier = '')
    {
        if (is_string($identifier) && $identifier !== '') {
            $identifier = strtolower($identifier);
        }

        $this->validateIdentifier($identifier);
        $this->setIdentifier($identifier);
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @param mixed $identifier
     * @throws ilTestQuestionPoolInvalidArgumentException
     */
    public function setIdentifier($identifier): void
    {
        $this->validateIdentifier($identifier);
        $this->identifier = $identifier;
    }

    /**
     * @return string[]
     */
    public function getValidIdentifiers(): array
    {
        return [self::DRAFT, self::FINAL, self::REVISED, self::UNAVAILABLE];
    }

    /**
     * @param mixed $identifier
     * @throws ilTestQuestionPoolInvalidArgumentException
     */
    public function validateIdentifier($identifier): void
    {
        if (!in_array($identifier, $this->getValidIdentifiers(), true)) {
            throw new ilTestQuestionPoolInvalidArgumentException(
                'Invalid lom lifecycle given: ' . $identifier
            );
        }
    }

    public function getMappedIliasLifecycleIdentifer(): string
    {
        switch ($this->getIdentifier()) {
            case self::UNAVAILABLE:
                return ilAssQuestionLifecycle::OUTDATED;

            case self::REVISED:
            case self::FINAL:
                return ilAssQuestionLifecycle::FINAL;

            case self::DRAFT:
            default:
                return ilAssQuestionLifecycle::DRAFT;
        }
    }
}
