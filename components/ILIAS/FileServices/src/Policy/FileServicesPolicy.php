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

namespace ILIAS\FileServices\Policy;

use ILIAS\ResourceStorage\Policy\FileNamePolicy;
use ILIAS\ResourceStorage\Policy\FileNamePolicyException;
use ILIAS\Filesystem\Security\Sanitizing\FilenameSanitizer;
use ILIAS\Filesystem\Security\Sanitizing\DefaultFilenameSanitizer;
use ILIAS\Filesystem\Configuration\FilesystemConfig;

/**
 * Lazy-initializing file name policy backed by FilesystemConfig.
 * Constructor stores the config but does NOT read from it — all DB reads
 * are deferred until the first method call via {@see self::init()}.
 */
class FileServicesPolicy implements FileNamePolicy
{
    private array $umlaut_mapping = [
        "\u{00C4}" => "Ae", // Ä
        "\u{00D6}" => "Oe", // Ö
        "\u{00DC}" => "Ue", // Ü
        "\u{00E4}" => "ae", // ä
        "\u{00F6}" => "oe", // ö
        "\u{00FC}" => "ue", // ü
        "\u{00E8}" => "e",  // è
        "\u{00E9}" => "e",  // é
        "\u{00EA}" => "e",  // ê
        "\u{00DF}" => "ss", // ß
    ];

    private bool $initialized = false;
    private array $blacklisted = [];
    private array $whitelisted = [];
    private bool $as_ascii = true;
    private FilenameSanitizer $sanitizer;

    public function __construct(private FilesystemConfig $settings)
    {
    }

    private function init(): void
    {
        if ($this->initialized) {
            return;
        }
        $this->blacklisted = $this->settings->getBlackListedSuffixes();
        $this->whitelisted = $this->settings->getWhiteListedSuffixes();
        $this->as_ascii = $this->settings->isASCIIConvertionEnabled();
        $this->sanitizer = new DefaultFilenameSanitizer($this->settings);
        $this->initialized = true;
    }

    public function check(string $extension): bool
    {
        if ($this->isBlockedExtension($extension)) {
            throw new FileNamePolicyException("Extension '$extension' is blacklisted.");
        }
        return true;
    }

    public function isValidExtension(string $extension): bool
    {
        $this->init();
        $extension = strtolower($extension);
        return in_array($extension, $this->whitelisted, true) && !in_array($extension, $this->blacklisted, true);
    }

    public function isBlockedExtension(string $extension): bool
    {
        $this->init();
        if ($this->settings->isByPassAllowedForCurrentUser()) {
            return false;
        }
        $extension = strtolower($extension);
        return in_array($extension, $this->blacklisted, true);
    }

    public function prepareFileNameForConsumer(string $filename_with_extension): string
    {
        $this->init();
        $filename = $this->sanitizer->sanitize(basename($filename_with_extension));
        if ($this->as_ascii) {
            $filename = $this->ascii($filename);
        }
        // remove all control characters, see https://mantis.ilias.de/view.php?id=34975
        $filename = preg_replace('/&#.*;/U', '_', $filename, 1);
        return $filename;
    }

    public function ascii(string $filename): string
    {
        foreach ($this->umlaut_mapping as $src => $tgt) {
            $filename = str_replace($src, $tgt, $filename);
        }

        $ascii_filename = htmlentities($filename, ENT_NOQUOTES, 'UTF-8');
        $ascii_filename = preg_replace('/\&(.)[^;]*;/', '\\1', $ascii_filename);
        $ascii_filename = preg_replace('/[\x7f-\xff]/', '_', (string) $ascii_filename);

        // OS do not allow the following characters in filenames: \/:*?"<>|
        // control characters are replaced as well: they are invisible in the title, but
        // break paths and HTTP headers built from it, see
        // https://mantis.ilias.de/view.php?id=30709
        $ascii_filename = preg_replace(
            '/[\x00-\x1f:\x5c\/\*\?\"<>\|]/',
            '_',
            (string) $ascii_filename
        );
        return $ascii_filename;
    }
}
