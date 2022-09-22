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

use ILIAS\ResourceStorage\Policy\FileNamePolicy;
use ILIAS\ResourceStorage\Policy\FileNamePolicyException;
use ILIAS\FileUpload\Processor\BlacklistExtensionPreProcessor;
use ILIAS\ResourceStorage\Policy\WhiteAndBlacklistedFileNamePolicy;

/**
 * Class ilFileServicesPolicy
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilFileServicesPolicy extends WhiteAndBlacklistedFileNamePolicy
{
    private array $umlaut_mapping = [
        "Ä" => "Ae",
        "Ö" => "Oe",
        "Ü" => "Ue",
        "ä" => "ae",
        "ö" => "oe",
        "ü" => "ue",
        "ß" => "ss"
    ];
    protected bool $as_ascii = true;
    protected ilFileServicesSettings $settings;
    protected ilFileServicesFilenameSanitizer $sanitizer;
    protected ?bool $bypass = null;

    public function __construct(ilFileServicesSettings $settings)
    {
        parent::__construct($settings->getBlackListedSuffixes(), $settings->getWhiteListedSuffixes());
        $this->sanitizer = new ilFileServicesFilenameSanitizer($settings);
        $this->as_ascii = $settings->convertToASCII();
    }

    public function prepareFileNameForConsumer(string $filename_with_extension): string
    {
        $filename = $this->sanitizer->sanitize(basename($filename_with_extension));
        if ($this->as_ascii) {
            return $this->ascii($filename);
        }
        return $filename;
    }

    public function ascii(string $filename): string
    {
        foreach ($this->umlaut_mapping as $src => $tgt) {
            $filename = str_replace($src, $tgt, $filename);
        }

        $ascii_filename = htmlentities($filename, ENT_NOQUOTES, 'UTF-8');
        $ascii_filename = preg_replace('/\&(.)[^;]*;/', '\\1', $ascii_filename);
        $ascii_filename = preg_replace('/[\x7f-\xff]/', '_', $ascii_filename);

        // OS do not allow the following characters in filenames: \/:*?"<>|
        $ascii_filename = preg_replace(
            '/[:\x5c\/\*\?\"<>\|]/',
            '_',
            $ascii_filename
        );
        return $ascii_filename;
    }

    private function determineFileAdminRefId(): int
    {
        $objects_by_type = ilObject2::_getObjectsByType('facs');
        $id = (int) reset($objects_by_type)['obj_id'];
        $references = ilObject2::_getAllReferences($id);
        return (int) reset($references);
    }

    public function isBlockedExtension(string $extension): bool
    {
        if ($this->bypass !== null && $this->bypass === true) {
            return false;
        }
        global $DIC;
        if (($this->bypass = $DIC->rbac()->system()->checkAccess(
            'upload_blacklisted_files',
            $this->determineFileAdminRefId()
        )) === true) {
            return false;
        }
        return parent::isBlockedExtension($extension);
    }
}
