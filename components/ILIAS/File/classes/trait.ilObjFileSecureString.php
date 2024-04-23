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

use ILIAS\ResourceStorage\Preloader\SecureString;

/**
 * Trait ilObjFileSecureString
 * @author Fabian Schmid <fabian@sr.solutions>
 */
trait ilObjFileSecureString
{
    use SecureString;

    protected function extractSuffixFromFilename(string $filename): string
    {
        if (!preg_match('/^(.+?)(?<!\s)\.([^.]*$|$)/', $filename, $matches)) {
            return '';
        }
        return $this->secure($matches[2]);
    }

    protected function stripSuffix(string $title, ?string $suffix = null): string
    {
        $suffix = $suffix ?? $this->extractSuffixFromFilename($title);

        if ($suffix !== null && ($length = strrpos($title, "." . $suffix)) > 0) {
            $title = substr($title, 0, $length);
        }

        return $this->secure($title);
    }

    protected function ensureSuffix(string $title, ?string $suffix = null): string
    {
        $title = $this->stripSuffix($title, $suffix);
        $suffix = $suffix ?? $this->extractSuffixFromFilename($title);

        if ($suffix !== null && strrpos($title, "." . $suffix) === false) {
            $title .= "." . $suffix;
        }

        return $this->secure(rtrim($title, "."));
    }

    protected function ensureSuffixInBrackets(string $title, ?string $suffix = null): string
    {
        $title = $this->stripSuffix($title, $suffix);
        $suffix = $suffix ?? $this->extractSuffixFromFilename($title);

        if ($suffix !== null && strrpos($title, "." . $suffix) === false) {
            $title .= " (" . $suffix . ")";
        }

        return $this->secure($title);
    }
}
