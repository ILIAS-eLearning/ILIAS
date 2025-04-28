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

use ILIAS\Data\DataSize;

/**
 * This class handles some upload limit based functionality
 * that is not explicitly UI related. 
 * (Otherwise UploadLimitResolver might be a better place!)
 * 
 * @author Thomas Hufschmidt <hufschmt@hrz.uni-marburg.de>
 */
class UploadFileLimits
{
    protected int $php_upload_limit_in_bytes;

    /**
     * @param $language Instance of ilLanguage for some string conversions
     * @param $role_based_upload_limit_in_bytes Preferred role-based upload limit for current user (or null)
     */
    public function __construct(
        protected ilLanguage $language,
        protected ?int $role_based_upload_limit_in_bytes,
    ) {
        $this->php_upload_limit_in_bytes = self::fetchPhpUploadSizeLimitInBytes();
    }

    /**
     * Returns the php upload limit calculated on class construction.
     * 
     * @return The maximum supported upload size on bytes
     */
    public function getPhpUploadSizeLimitInBytes(): int
    {
        return $this->php_upload_limit_in_bytes;
    }

    /**
     * Utility method that will return either the role-based upload
     * limit if it is lower then the supported upload limit, or
     * the maximum php upload limit otherwise.
     * 
     * @return int Maximum/Best role based upload limit given on construction (or max php upload if less)
     */
    public function getRoleBasedUploadLimitInBytes(): int
    {
        // Return role-based upload limit if within php limit
        if ($this->role_based_upload_limit_in_bytes !== null && $this->role_based_upload_limit_in_bytes <= $this->getPhpUploadSizeLimitInBytes()) {
            return $this->role_based_upload_limit_in_bytes;
        }

        // Fallback to php limit otherwise
        return $this->getPhpUploadSizeLimitInBytes();
    }

    /**
     * Converts the given upload size (in bytes) into mega-bytes.
     * Outputs a string with MB suffix!
     * 
     * @param $size The value (given in bytes!) that should be converted
     * 
     * @return The given value converted to mega-bytes
     */
    public function getUploadSizeInfo(int $size): string
    {
        $size_str = new DataSize($size, DataSize::MB);
        return $this->language->txt("file_notice") . " " . $size_str;
    }

    /**
     * Converts the given upload size (in bytes) into mega-bytes,
     * if non is given (default) will fallback to current role-based upload limit.
     * Outputs a string with MB suffix!
     * 
     * @param $size (Optional) The value (given in bytes!) that should be converted
     * 
     * @return The given value or current role-based upload limit converted to mega-bytes
     */
    public function getRoleBasedUploadSizeInfo(?int $size = null): string
    {
        $size_str = new DataSize($size ?? $this->getRoleBasedUploadLimitInBytes(), DataSize::MB);
        return $this->language->txt("file_notice") . " " . $size_str;
    }

    /**
     * Fetches current maximum supported upload size (in bytes) from php config.
     * This should be the only place where the actual value is read with!
     * 
     * @return The maximum supported upload size on bytes
     */
    public static function fetchPhpUploadSizeLimitInBytes(): int
    {
        // Converts unit suffic into multiplicated value
        $convertPhpIniSizeValueToBytes = function ($phpIniSizeValue) {
            // No conversion needed
            if (is_numeric($phpIniSizeValue)) {
                return $phpIniSizeValue;
            }

            // Find value and suffix (eg. 100M)
            $suffix = substr($phpIniSizeValue, -1);
            $value = substr($phpIniSizeValue, 0, -1);

            // Multiply from biggest suffix to smallest
            switch (strtoupper($suffix)) {
                case 'P':
                    $value *= 1024;
                    // no break
                case 'T':
                    $value *= 1024;
                    // no break
                case 'G':
                    $value *= 1024;
                    // no break
                case 'M':
                    $value *= 1024;
                    // no break
                case 'K':
                    $value *= 1024;
                    break;
            }
            return $value;
        };

        // Find minimum of two php configurations and return
        return min(
            $convertPhpIniSizeValueToBytes(ini_get('post_max_size')),
            $convertPhpIniSizeValueToBytes(ini_get('upload_max_filesize'))
        );
    }
}
