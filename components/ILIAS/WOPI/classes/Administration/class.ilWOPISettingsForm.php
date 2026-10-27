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

use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use ILIAS\Administration\Setting;
use ILIAS\UI\Component\Input\Container\Form\Standard;
use ILIAS\UI\Component\Input\Field\Section;
use Psr\Http\Message\RequestInterface;
use ILIAS\WOPI\Discovery\Crawler;
use ILIAS\Data\URI;

/**
 * @author            Fabian Schmid <fabian@sr.solutions>
 */
class ilWOPISettingsForm
{
    private Standard $form;
    private Factory $ui_factory;
    private Renderer $ui_renderer;
    private ilCtrlInterface $ctrl;
    private ilLanguage $lng;
    private \ILIAS\Refinery\Factory $refinery;

    public function __construct(
        private Setting $settings,
        private bool $write_access
    ) {
        global $DIC;
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->refinery = $DIC->refinery();

        $this->form = $this->initForm();
    }

    private function initForm(): Standard
    {
        return $this->ui_factory->input()->container()->form()->standard(
            $this->ctrl->getFormActionByClass(ilWOPIAdministrationGUI::class, ilWOPIAdministrationGUI::CMD_STORE),
            $this->getSection()->getInputs()
        );
    }

    private function getSection(): Section
    {
        $wopi_discovery_url = $this->settings->get("wopi_discovery_url");
        $saving_interval_value = (int) $this->settings->get("saving_interval", '0');
        $saving_interval_value = $saving_interval_value === 0 ? null : $saving_interval_value;
        $create_templates_value = $this->settings->get("create_templates", '0') === '1';

        $wopi_url = $this->ui_factory
            ->input()
            ->field()
            ->text(
                $this->lng->txt("wopi_url"),
                $this->lng->txt("wopi_url_byline")
            )
            ->withDisabled(!$this->write_access)
            ->withAdditionalTransformation(
                $this->refinery->custom()->transformation(fn($v) => $v === '' ? null : $v)
            )->withAdditionalTransformation(
                $this->refinery->custom()->constraint(function ($v): bool {
                    if ($v === null) {
                        return false;
                    }
                    return (new Crawler())->validate(new URI($v));
                }, $this->lng->txt('msg_error_wopi_invalid_discorvery_url'))
            )->withValue(
                $wopi_discovery_url ?? ''
            );

        $saving_interval = $this->ui_factory->input()->field()->optionalGroup(
            [
                $this->ui_factory
                    ->input()
                    ->field()
                    ->numeric(
                        $this->lng->txt("saving_interval"),
                        $this->lng->txt("saving_interval_byline")
                    )
                    ->withDisabled(!$this->write_access)
                    ->withAdditionalTransformation(
                        $this->refinery->custom()->transformation(fn($v) => $v === '' ? null : $v)
                    )->withValue(
                        $saving_interval_value
                    )
            ],
            $this->lng->txt("activate_saving_interval")
        )->withValue(
            $saving_interval_value === null ? null : [$saving_interval_value]
        );

        $create_templates = $this->ui_factory
            ->input()
            ->field()
            ->checkbox(
                $this->lng->txt("create_templates"),
                $this->lng->txt("create_templates_byline")
            )
            ->withDisabled(!$this->write_access)
            ->withValue($create_templates_value);

        return $this->ui_factory
            ->input()
            ->field()
            ->section(
                [
                    $this->ui_factory
                        ->input()
                        ->field()
                        ->optionalGroup(
                            [$wopi_url, $saving_interval, $create_templates],
                            $this->lng->txt("activate_wopi")
                        )
                        ->withDisabled(!$this->write_access)
                        ->withValue(
                            $wopi_discovery_url === null ? null : [
                                $wopi_discovery_url,
                                $saving_interval_value === null ? null : [$saving_interval_value],
                                $create_templates_value
                            ]
                        )
                ],
                $this->lng->txt("wopi_settings"),
            );
    }

    /**
     * Stores the settings once the whole form is valid, nothing is stored otherwise.
     */
    public function proceed(RequestInterface $request): bool
    {
        $this->form = $this->form->withRequest($request);
        $data = $this->form->getData();
        if ($data === null) {
            return false;
        }

        $wopi = $data[0] ?? null;
        if ($wopi === null || $wopi === [null]) {
            $this->settings->set("wopi_activated", '0');
            $this->settings->delete("wopi_discovery_url");
            return true;
        }

        [$discovery_url, $saving_interval, $create_templates] = $wopi;
        $this->settings->set("wopi_activated", '1');
        $this->settings->set("wopi_discovery_url", $discovery_url);
        $this->settings->set("create_templates", $create_templates ? '1' : '0');

        $interval = (int) ($saving_interval[0] ?? 0);
        if ($interval > 0) {
            $this->settings->set("saving_interval", (string) $interval);
        } else {
            $this->settings->delete("saving_interval");
        }

        return true;
    }

    public function getHTML(): string
    {
        return $this->ui_renderer->render($this->form);
    }
}
