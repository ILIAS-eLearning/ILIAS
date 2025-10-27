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

use ILIAS\GlobalScreen\Scope\MetaBar\Provider\AbstractStaticFooterProvider;
use ILIAS\DI\Container;

/**
 * Class ilAccessibilitySupportFooterProvider
 */
class ilAccessibilitySupportFooterProvider extends AbstractStaticFooterProvider
{
    private ilLanguage $lng;

    public function __construct(Container $dic)
    {
        parent::__construct($dic);
        $this->lng = $dic->language();
        $this->lng->loadLanguageModule('gsfo');
    }

    public function getGroups(): array
    {
        return [
            $this->item_factory->group(
                $this->id_factory->identifier(ilFooterStandardGroups::ACCESSIBILITY->value),
                $this->lng->txt('accessibility')
            )->withPosition(10),
        ];
    }

    public function getEntries(): array
    {
        $entries = [];
        // Accessibility Items
        // accessibility control concept
        if (($accessibility_control_url = \ilAccessibilityControlConceptGUI::getFooterLink()) !== '') {
            $accessibility_control_title = \ilAccessibilityControlConceptGUI::getFooterText();
            $entries[] = $this->item_factory
                ->link(
                    $this->id_factory->identifier('accessibility_control'),
                    $accessibility_control_title
                )
                ->withAction($accessibility_control_url)
                ->withParent($this->id_factory->identifier(ilFooterStandardGroups::ACCESSIBILITY->value));
        }

        // report accessibility issue
        if (($accessibility_report_url = \ilAccessibilitySupportContactsGUI::getFooterLink()) !== '') {
            $accessibility_report_title = \ilAccessibilitySupportContactsGUI::getFooterText();
            $entries[] = $this->item_factory
                ->link(
                    $this->id_factory->identifier('accessibility_report'),
                    $accessibility_report_title
                )
                ->withAction($accessibility_report_url)
                ->withParent($this->id_factory->identifier(ilFooterStandardGroups::ACCESSIBILITY->value));
        }

        return $entries;
    }

}
