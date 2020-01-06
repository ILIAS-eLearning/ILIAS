<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Page;

/**
 * Class PageConfig
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class AsqPageObjectConfig extends \ilPageConfig
{
    public function init()
    {
        $this->setEnablePCType('Tabs', true);
        $this->setEnableInternalLinks(false);
    }
}
