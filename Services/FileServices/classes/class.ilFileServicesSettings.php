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
    private bool $convert_to_ascii = true;
    private ?bool $bypass = null;
    protected int $file_admin_ref_id;

    public function __construct(
        ilSetting $settings,
        ilIniFile $client_ini
    ) {
        $this->convert_to_ascii = (bool) !$client_ini->readVariable('file_access', 'disable_ascii');
        $this->settings = $settings;
        /** @noRector */
        $this->white_list_default = include "Services/FileServices/defaults/default_whitelist.php";
        $this->file_admin_ref_id = $this->determineFileAdminRefId();
        $this->read();
    }

    private function determineFileAdminRefId(): int
    {
        $objects_by_type = ilObject2::_getObjectsByType('facs');
        $id = (int) reset($objects_by_type)['obj_id'];
        $references = ilObject2::_getAllReferences($id);
        return (int) reset($references);
    }

    private function determineByPass(): bool
    {
        global $DIC;
        return $DIC->isDependencyAvailable('rbac')
            && $DIC->rbac()->system()->checkAccess(
                'upload_blacklisted_files',
                $this->file_admin_ref_id
            );
    }

    public function isByPassAllowedForCurrentUser(): bool
    {
        if ($this->bypass !== null) {
            return $this->bypass;
        }
        return $this->bypass = $this->determineByPass();
    }

    private function read(): void
    {
        $this->readBlackList();
        $this->readWhiteList();
    }

    public function isASCIIConvertionEnabled(): bool
    {
        return $this->convert_to_ascii;
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
