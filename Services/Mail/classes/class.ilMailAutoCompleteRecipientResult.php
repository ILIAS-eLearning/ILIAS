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
 * Class ilMailAutoCompleteRecipientResult
 */
class ilMailAutoCompleteRecipientResult
{
    public const MODE_STOP_ON_MAX_ENTRIES = 1;
    public const MODE_FETCH_ALL = 2;
    public const MAX_RESULT_ENTRIES = 1000;
    protected bool $allow_smtp;
    protected int $user_id;
    /** @var int[] */
    protected array $handled_recipients = [];
    protected int $mode = self::MODE_STOP_ON_MAX_ENTRIES;
    protected int $max_entries;
    /** @var array{hasMoreResults: bool, items: array} */
    public array $result = [
        'items' => [],
        'hasMoreResults' => false
    ];

    public function __construct(int $mode)
    {
        global $DIC;

        $this->allow_smtp = $DIC->rbac()->system()->checkAccess('smtp_mail', MAIL_SETTINGS_ID);
        $this->user_id = $DIC->user()->getId();
        $this->max_entries = ilSearchSettings::getInstance()->getAutoCompleteLength();

        $this->initMode($mode);
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function initMode(int $mode): void
    {
        if (!in_array($mode, [self::MODE_FETCH_ALL, self::MODE_STOP_ON_MAX_ENTRIES], true)) {
            throw new InvalidArgumentException("Wrong mode passed!");
        }
        $this->mode = $mode;
    }

    public function isResultAddable(): bool
    {
        if ($this->mode === self::MODE_STOP_ON_MAX_ENTRIES &&
            $this->max_entries >= 0 && count($this->result['items']) >= $this->max_entries) {
            return false;
        }

        if ($this->mode === self::MODE_FETCH_ALL &&
            count($this->result['items']) >= self::MAX_RESULT_ENTRIES) {
            return false;
        }

        return true;
    }

    public function addResult(string $login, string $firstname, string $lastname): void
    {
        if ($login !== '' && !isset($this->handled_recipients[$login])) {
            $recipient = [];
            $recipient['value'] = $login;

            $label = $login;
            if ($firstname && $lastname) {
                $label .= " [" . $firstname . ", " . $lastname . "]";
            }
            $recipient['label'] = $label;

            $this->result['items'][] = $recipient;
            $this->handled_recipients[$login] = 1;
        }
    }

    /**
     * @return array{hasMoreResults: bool, items: array{value: string, label: string}[]}
     */
    public function getItems(): array
    {
        return $this->result;
    }

    public function numItems(): int
    {
        return count($this->result['items']);
    }
}
