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

class ilMailMimeSubjectBuilder
{
    public function __construct(private readonly ilSetting $settings, private readonly string $default_prefix)
    {
    }

    public function subject(string $subject, bool $add_prefix = false, string $context_prefix = ''): string
    {
        $subject = trim($subject);
        $context_prefix = trim($context_prefix);

        if ($add_prefix) {
            // #9096
            $global_prefix = $this->settings->get('mail_subject_prefix');
            if (!is_string($global_prefix)) {
                $global_prefix = $this->default_prefix;
            }
            $global_prefix = trim($global_prefix);

            $prefix = $global_prefix;
            if ($context_prefix !== '') {
                $prefix = str_replace(['[', ']',], '', $prefix);
                if ($prefix !== '') {
                    $prefix = '[' . $prefix . ' : ' . $context_prefix . ']';
                } else {
                    $prefix = '[' . $context_prefix . ']';
                }
            }

            if ($prefix && $prefix !== '') {
                $subject = $prefix . ' ' . $subject;
            }
        }

        return $subject;
    }
}
