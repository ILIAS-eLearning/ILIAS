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
        $suffix ??= $this->extractSuffixFromFilename($title);

        if ($suffix !== null && ($length = strrpos($title, "." . $suffix)) > 0) {
            $title = substr($title, 0, $length);
        }

        return $this->secure($title);
    }

    protected function ensureSuffix(string $title, ?string $suffix = null): string
    {
        $title = $this->stripSuffix($title, $suffix);
        $suffix ??= $this->extractSuffixFromFilename($title);

        if ($suffix !== null && strrpos((string) $title, "." . $suffix) === false) {
            $title .= "." . $suffix;
        }

        return $this->secure(rtrim((string) $title, "."));
    }

    /**
     * The copy info ("- Copy", "- Copy (2)") is appended to the full title of the original, behind
     * the suffix, where ensureSuffix() cuts it off. Move it in front of the suffix and count up
     * against the titles already present, since those carry the copy info in front of the suffix
     * as well. See https://mantis.ilias.de/view.php?id=43460
     *
     * @param string   $suffix          the real suffix of the file, not the one of the title
     * @param string[] $existing_titles
     */
    protected function moveCopyInfoInFrontOfSuffix(
        string $original_title,
        string $cloned_title,
        string $suffix,
        array $existing_titles,
        string $copy_n_suffix
    ): string {
        if ($suffix === ''
            || !str_ends_with($original_title, '.' . $suffix)
            || !str_starts_with($cloned_title, $original_title . ' ')
        ) {
            return $cloned_title;
        }

        $base = substr($original_title, 0, -strlen('.' . $suffix));
        $title = $base . substr($cloned_title, strlen($original_title)) . '.' . $suffix;
        for ($i = 2; $i < 1000 && in_array($title, $existing_titles, true); $i++) {
            $title = $base . ' ' . $this->formatCopyNumber($copy_n_suffix, $i) . '.' . $suffix;
        }

        return $title;
    }

    /**
     * Some translations of copy_n_of_suffix have no or a broken placeholder.
     */
    private function formatCopyNumber(string $copy_n_suffix, int $number): string
    {
        try {
            $formatted = sprintf($copy_n_suffix, $number);
        } catch (\ValueError|\ArgumentCountError) {
            $formatted = $copy_n_suffix;
        }

        return $formatted !== $copy_n_suffix ? $formatted : sprintf('(%d)', $number);
    }

    protected function ensureSuffixInBrackets(string $title, ?string $suffix = null): string
    {
        $title = $this->stripSuffix($title, $suffix);
        $suffix ??= $this->extractSuffixFromFilename($title);

        if ($suffix !== null && strrpos((string) $title, "." . $suffix) === false) {
            $title .= " (" . $suffix . ")";
        }

        return $this->secure($title);
    }
}
