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
 * Class ilMailMimeSubjectBuilder
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailMimeSubjectBuilder
{
    private ilSetting $settings;
    private string $defaultPrefix;

    public function __construct(ilSetting $settings, string $defaultPrefix)
    {
        $this->settings = $settings;
        $this->defaultPrefix = $defaultPrefix;
    }

    public function subject(string $subject, bool $addPrefix = false, string $contextPrefix = '') : string
    {
        $subject = trim($subject);
        $contextPrefix = trim($contextPrefix);

        if ($addPrefix) {
            // #9096
            $globalPrefix = $this->settings->get('mail_subject_prefix');
            if (!is_string($globalPrefix)) {
                $globalPrefix = $this->defaultPrefix;
            }
            $globalPrefix = trim($globalPrefix);

            $prefix = $globalPrefix;
            if ($contextPrefix !== '') {
                $prefix = str_replace(['[', ']',], '', $prefix);
                if ($prefix !== '') {
                    $prefix = '[' . $prefix . ' : ' . $contextPrefix . ']';
                } else {
                    $prefix = '[' . $contextPrefix . ']';
                }
            }

            if ($prefix && $prefix !== '') {
                $subject = $prefix . ' ' . $subject;
            }
        }

        return $subject;
    }
}
