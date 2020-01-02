<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Administration/interfaces/interface.ilVersionControlInformation.php';

/**
 * Class ilGitInformation
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilGitInformation implements ilVersionControlInformation
{
    /**
     * @var string
     */
    private static $revision_information = null;

    /**
     *
     */
    private static function detect()
    {
        global $DIC;

        $lng = $DIC->language();

        if (null !== self::$revision_information) {
            return self::$revision_information;
        }

        $info = array();

        if (!ilUtil::isWindows()) {
            $version_mini_hash = ilUtil::execQuoted('git rev-parse --short HEAD');
            $version_number    = ilUtil::execQuoted('git rev-list --count HEAD');
            $line              = ilUtil::execQuoted('git log -1');

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
            $version_mini_hash = trim(exec('git rev-parse --short HEAD'));
            $version_number    = exec('git rev-list --count HEAD');
            $line              = trim(exec('git log -1'));
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

    /**
     * @return string
     */
    public function getInformationAsHtml()
    {
        self::detect();

        return implode("<br />", self::$revision_information);
    }
}
