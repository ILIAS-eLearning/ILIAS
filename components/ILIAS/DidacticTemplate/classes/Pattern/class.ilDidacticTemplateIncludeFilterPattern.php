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
/**
 * Implementation of an include filter pattern for didactic template actions
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesDidacticTemplate
 */
class ilDidacticTemplateIncludeFilterPattern extends ilDidacticTemplateFilterPattern
{
    public function __construct(int $a_pattern_id = 0)
    {
        parent::__construct($a_pattern_id);
        $this->setPatternType(self::PATTERN_INCLUDE);
    }

    public function valid(string $a_source): bool
    {
        $a_source = trim($a_source);
        switch ($this->getPatternSubType()) {
            case self::PATTERN_SUBTYPE_REGEX:
                $this->logger->debug('Checking include pattern with ' . $a_source . ' against ' . $this->getPattern());
                return preg_match('/' . $this->getPattern() . '/', $a_source) === 1;
        }
        return false;
    }

    public function toXml(ilXmlWriter $writer): void
    {
        switch ($this->getPatternSubType()) {
            case ilDidacticTemplateFilterPattern::PATTERN_SUBTYPE_REGEX:
            default:

                $writer->xmlElement(
                    'includePattern',
                    [
                        'preg' => $this->getPattern()
                    ]
                );
        }
    }
}
