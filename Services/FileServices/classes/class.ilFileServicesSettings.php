<?php

declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
 * Class ilObjFileServices
 */
class ilFileServicesSettings
{
    private ilSetting $settings;
    private array $white_list_default = [];
    private array $white_list_negative = [];
    private array $white_list_positive = [];
    private array $white_list_overall = [];
    private array $black_list_prohibited = [];
    private array $black_list_overall = [];


    public function __construct(
        ilSetting $settings
    ) {
        $this->settings = $settings;
        /** @noRector */
        $this->white_list_default = include "Services/FileServices/defaults/default_whitelist.php";
        $this->read();
    }


    private function read(): void
    {
        $this->readBlackList();
        $this->readWhiteList();
    }

    private function readWhiteList(): void
    {
        $cleaner = $this->getCleaner();

        $this->white_list_negative = array_map(
            $cleaner,
            explode(",", $this->settings->get("suffix_repl_additional") ?? '')
        );

        $this->white_list_positive = array_map(
            $cleaner,
            explode(",", $this->settings->get("suffix_custom_white_list") ?? '')
        );

        $this->white_list_overall = array_merge($this->white_list_default, $this->white_list_positive);
        $this->white_list_overall = array_diff($this->white_list_overall, $this->white_list_negative);
        $this->white_list_overall = array_diff($this->white_list_overall, $this->black_list_overall);
        $this->white_list_overall[] = '';
        $this->white_list_overall = array_unique($this->white_list_overall);
    }

    private function readBlackList(): void
    {
        $cleaner = $this->getCleaner();

        $this->black_list_prohibited = array_map(
            $cleaner,
            explode(",", $this->settings->get("suffix_custom_expl_black") ?? '')
        );

        $this->black_list_prohibited = array_filter($this->black_list_prohibited, fn ($item): bool => $item !== '');
        $this->black_list_overall = $this->black_list_prohibited;
    }

    private function getCleaner(): Closure
    {
        return function (string $suffix): string {
            return trim(strtolower($suffix));
        };
    }

    public function getWhiteListedSuffixes(): array
    {
        return $this->white_list_overall;
    }

    public function getBlackListedSuffixes(): array
    {
        return $this->black_list_overall;
    }

    /**
     * @internal
     */
    public function getDefaultWhitelist()
    {
        return $this->white_list_default;
    }

    /**
     * @internal
     */
    public function getWhiteListNegative(): array
    {
        return $this->white_list_negative;
    }

    /**
     * @internal
     */
    public function getWhiteListPositive(): array
    {
        return $this->white_list_positive;
    }

    /**
     * @internal
     */
    public function getProhibited(): array
    {
        return $this->black_list_prohibited;
    }
}
