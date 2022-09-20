<?php

declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Implementation of an include filter pattern for didactic template actions
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesDidacticTemplate
 */
class ilDidacticTemplateExcludeFilterPattern extends ilDidacticTemplateFilterPattern
{
    public function __construct(int $a_pattern_id = 0)
    {
        parent::__construct($a_pattern_id);
        $this->setPatternType(self::PATTERN_EXCLUDE);
    }

    /**
     * Check if patttern matches
     */
    public function valid(string $a_source): bool
    {
        $a_source = trim($a_source);
        switch ($this->getPatternSubType()) {
            case self::PATTERN_SUBTYPE_REGEX:
                $this->logger->debug('Checking exclude pattern with ' . $a_source . ' against ' . $this->getPattern());
                return preg_match('/' . $this->getPattern() . '/', $a_source) !== 1;
        }
        return true;
    }

    public function toXml(ilXmlWriter $writer): void
    {
        switch ($this->getPatternSubType()) {
            case ilDidacticTemplateFilterPattern::PATTERN_SUBTYPE_REGEX:
            default:

                $writer->xmlElement(
                    'excludePattern',
                    [
                        'preg' => $this->getPattern()
                    ]
                );
        }
    }
}
