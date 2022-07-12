<?php declare(strict_types=1);

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
 * Class ilGitInformation
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilGitInformation implements ilVersionControlInformation
{
    /**
     * @var string[]|null
     */
    private static ?array $revision_information = null;

    private static function detect() : void
    {
        global $DIC;

        $lng = $DIC->language();

        if (null !== self::$revision_information) {
            return;
        }

        $info = array();

        if (!ilUtil::isWindows()) {
            $origin = ilShellUtil::execQuoted('git config --get remote.origin.url');
            $branch = ilShellUtil::execQuoted('git rev-parse --abbrev-ref HEAD');
            $version_mini_hash = ilShellUtil::execQuoted('git rev-parse --short HEAD');
            $version_number = ilShellUtil::execQuoted('git rev-list --count HEAD');
            $line = ilShellUtil::execQuoted('git log -1');

            if ($origin[0]) {
                $origin = $origin[0];
            }

            if ($branch[0]) {
                $branch = $branch[0];
            }

            if ($version_number[0]) {
                $version_number = $version_number[0];
            }

            if ($version_mini_hash[0]) {
                $version_mini_hash = $version_mini_hash[0];
            }

            if ($line && array_filter($line)) {
                $line = implode(' | ', array_filter($line));
            }
        } else {
            $origin = trim(exec('git config --get remote.origin.url'));
            $branch = trim(exec('git rev-parse --abbrev-ref HEAD'));
            $version_mini_hash = trim(exec('git rev-parse --short HEAD'));
            $version_number = exec('git rev-list --count HEAD');
            $line = trim(exec('git log -1'));
        }

        if ($origin) {
            $info[] = $origin;
        }

        if ($branch) {
            $info[] = $branch;
        }
        
        if ($version_number) {
            $info[] = sprintf($lng->txt('git_revision'), $version_number);
        }

        if ($version_mini_hash) {
            $info[] = sprintf($lng->txt('git_hash_short'), $version_mini_hash);
        }

        if ($line) {
            $info[] = sprintf($lng->txt('git_last_commit'), $line);
        }

        self::$revision_information = $info;
    }

    public function getInformationAsHtml() : string
    {
        self::detect();

        return implode("<br />", self::$revision_information);
    }
}
