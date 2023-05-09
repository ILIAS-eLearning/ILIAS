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
 * Base class for parameters attached to Web Link items
 * @author Tim Schmitz <schmitz@leifos.de>
 */
abstract class ilWebLinkBaseParameter
{
    public const UNDEFINED_NAME = 'undefined';
    public const USER_ID_NAME = 'user_id';
    public const SESSION_ID_NAME = 'session_id';
    public const LOGIN_NAME = 'login';
    public const MATRICULATION_NAME = 'matriculation';

    /**
     * TODO Once the GUI is updated, undefined can be dropped.
     */
    public const VALUES = [
        self::UNDEFINED_NAME => 0,
        self::USER_ID_NAME => 1,
        self::SESSION_ID_NAME => 2,
        self::LOGIN_NAME => 3,
        self::MATRICULATION_NAME => 4
    ];

    /**
     * Keys of the language variables to the possible values,
     * e.g. to fill a select input.
     */
    public const VALUES_TEXT = [
        self::VALUES[self::UNDEFINED_NAME] => 'links_select_one',
        self::VALUES[self::USER_ID_NAME] => 'links_user_id',
        self::VALUES[self::SESSION_ID_NAME] => 'links_session_id',
        self::VALUES[self::LOGIN_NAME] => 'links_user_name',
        self::VALUES[self::MATRICULATION_NAME] => 'matriculation',
    ];

    protected int $value;
    protected string $name;

    public function __construct(int $value, string $name)
    {
        $this->value = $value;
        $this->name = $name;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
