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

use ILIAS\GlobalScreen\Scope\MetaBar\Provider\AbstractStaticFooterProvider;
use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\Data\URI;
use ILIAS\GlobalScreen\Scope\Footer\Factory\Permanent;
use ILIAS\UICore\PageContentProvider;
use ILIAS\DI\Container;
use ILIAS\GlobalScreen_\UI\Translator;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class ilFooterStandardGroupsProvider extends AbstractStaticFooterProvider
{
    private readonly Translator $translator;

    public function __construct(Container $dic)
    {
        parent::__construct($dic);
        $this->translator = new Translator($dic);
    }

    public function getIdentificationFor(ilFooterStandardGroups $group): IdentificationInterface
    {
        return $this->id_factory->identifier($group->value);
    }

    public function getGroups(): array
    {
        return [
            $this->item_factory->group(
                $this->getIdentificationFor(ilFooterStandardGroups::ACCESSIBILITY),
                $this->translator->translate('accessibility')
            )->withPosition(10),
            $this->item_factory->group(
                $this->getIdentificationFor(ilFooterStandardGroups::LEGAL_INFORMATION),
                $this->translator->translate('legal_information')
            )->withPosition(20),
            $this->item_factory->group(
                $this->getIdentificationFor(ilFooterStandardGroups::SUPPORT),
                $this->translator->translate('support')
            )->withPosition(30),
            $this->item_factory->group(
                $this->getIdentificationFor(ilFooterStandardGroups::SERVICES),
                $this->translator->translate('services')
            )->withPosition(40),
        ];
    }

    private function buildURI(string $from_path): URI
    {
        $request = $this->dic->http()->request()->getUri();
        return new URI($request->getScheme() . '://' . $request->getHost() . '/' . ltrim($from_path, '/'));
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
                ->withAction($this->buildURI($accessibility_control_url))
                ->withParent($this->getIdentificationFor(ilFooterStandardGroups::ACCESSIBILITY));
        }

        // report accessibility issue
        if (($accessibility_report_url = \ilAccessibilitySupportContactsGUI::getFooterLink()) !== '') {
            $accessibility_report_title = \ilAccessibilitySupportContactsGUI::getFooterText();
            $entries[] = $this->item_factory
                ->link(
                    $this->id_factory->identifier('accessibility_report'),
                    $accessibility_report_title
                )
                ->withAction($this->buildURI($accessibility_report_url))
                ->withParent($this->getIdentificationFor(ilFooterStandardGroups::ACCESSIBILITY));
        }

        // Imprint
        /*$base_class = ($this->dic->http()->wrapper()->query()->has(\ilCtrlInterface::PARAM_BASE_CLASS)) ?
            $this->dic->http()->wrapper()->query()->retrieve(
                \ilCtrlInterface::PARAM_BASE_CLASS,
                $this->dic->refinery()->kindlyTo()->string()
            ) : null;*/

        // there was another check $base_class !== \ilImprintGUI::class &&
        if (\ilImprint::isActive()) {
            $imprint_title = $this->dic->language()->txt("imprint");

            $entries[] = $this->item_factory
                ->link(
                    $this->id_factory->identifier('imprint'),
                    $imprint_title
                )
                ->withAction(new URI(\ilLink::_getStaticLink(0, "impr")))
                ->withParent($this->getIdentificationFor(ilFooterStandardGroups::LEGAL_INFORMATION));
        }

        // system support contacts
        if (($system_support_url = \ilSystemSupportContactsGUI::getFooterLink()) !== '') {
            $system_support_title = \ilSystemSupportContactsGUI::getFooterText();
            $entries[] = $this->item_factory
                ->link(
                    $this->id_factory->identifier('system_support'),
                    $system_support_title
                )
                ->withAction($this->buildURI($system_support_url))
                ->withParent($this->getIdentificationFor(ilFooterStandardGroups::SUPPORT));
        }

        // output translation link
        if (\ilObjLanguageAccess::_checkTranslate() && !\ilObjLanguageAccess::_isPageTranslation()) {
            $translation_url = \ilObjLanguageAccess::_getTranslationLink();
            $translation_title = $this->dic->language()->txt('translation');
            $entries[] = $this->item_factory
                ->link(
                    $this->id_factory->identifier('translation'),
                    $translation_title
                )
                ->withAction($this->buildURI($translation_url))
                ->withOpenInNewViewport(true)
                ->withParent($this->getIdentificationFor(ilFooterStandardGroups::SERVICES));
        }

        return $entries;
    }

    #[\Override]
    public function getAdditionalTexts(): array
    {
        $ilias_version = ILIAS_VERSION;
        $text = "powered by ILIAS (v{$ilias_version})";

        return [
            $this->item_factory->text(
                $this->id_factory->identifier('ilias_version'),
                $text
            )
        ];
    }

    public function getPermanentURI(): ?Permanent
    {
        $permanant_link = PageContentProvider::getPermaLink();
        if (empty($permanant_link)) {
            return null;
        }

        return $this->item_factory->permanent(
            $this->id_factory->identifier('permanent'),
            $this->translator->translate('permanent'),
            new URI($permanant_link)
        );
    }

    private function txt(string $key): string
    {
        return $this->dic->language()->txt($key);
    }

}
