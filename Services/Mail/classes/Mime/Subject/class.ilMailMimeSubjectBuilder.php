<?php declare(strict_types=1);
/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailMimeSubjectBuilder
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailMimeSubjectBuilder
{
    /** @var ilSetting */
    private $settings;
    /** @var string */
    private $defaultPrefix;

    /**
     * ilMailMimeSubjectBuilder constructor.
     * @param ilSetting $settings
     * @param string $defaultPrefix
     */
    public function __construct(ilSetting $settings, string $defaultPrefix)
    {
        $this->settings = $settings;
        $this->defaultPrefix = $defaultPrefix;
    }

    /**
     * @param string $subject
     * @param bool $addPrefix
     * @param string $contextPrefix
     * @return string
     */
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
                    $prefix = '#' . $prefix . ' : ' . $contextPrefix . ']';
                } else {
                    $prefix = '#' . $contextPrefix . ']';
                }
            }

            if (strlen($prefix) > 0) {
                $subject = $prefix . ' ' . $subject;
            }
        }

        return $subject;
    }
}
