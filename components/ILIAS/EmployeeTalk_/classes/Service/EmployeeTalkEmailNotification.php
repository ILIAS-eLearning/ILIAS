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

namespace ILIAS\EmployeeTalk\Service;

final class EmployeeTalkEmailNotification
{
    private int $talk_ref_id;
    private string $talk_name;
    private string $talk_description;
    private string $talk_location;
    private string $subject_key;
    private string $message_key;
    private string $superior_name;
    /**
     * @var string[] $dates
     */
    private array $dates;
    private bool $add_goto;

    /**
     * @param int      $talk_ref_id
     * @param string   $talk_name
     * @param string   $talk_description
     * @param string   $talk_location
     * @param string   $subject_key
     * @param string   $message_key
     * @param string   $superior_name
     * @param string[] $dates
     * @param bool     $add_goto
     */
    public function __construct(
        int $talk_ref_id,
        string $talk_name,
        string $talk_description,
        string $talk_location,
        string $subject_key,
        string $message_key,
        string $superior_name,
        array $dates,
        bool $add_goto = true
    ) {
        $this->talk_ref_id = $talk_ref_id;
        $this->talk_name = $talk_name;
        $this->talk_description = $talk_description;
        $this->talk_location = $talk_location;
        $this->subject_key = $subject_key;
        $this->message_key = $message_key;
        $this->superior_name = $superior_name;
        $this->dates = $dates;
        $this->add_goto = $add_goto;
    }

    public function getTalkRefId(): int
    {
        return $this->talk_ref_id;
    }

    public function getTalkName(): string
    {
        return $this->talk_name;
    }

    public function getTalkDescription(): string
    {
        return $this->talk_description;
    }

    public function getTalkLocation(): string
    {
        return $this->talk_location;
    }

    public function getSubjectLangKey(): string
    {
        return $this->subject_key;
    }

    public function getMessageLangKey(): string
    {
        return $this->message_key;
    }

    public function getNameOfSuperior(): string
    {
        return $this->superior_name;
    }

    /**
     * @return string[]
     */
    public function getDates(): array
    {
        return $this->dates;
    }

    public function getAddGoto(): bool
    {
        return $this->add_goto;
    }
}
