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
use ILIAS\components\WOPI\Discovery\Crawler;
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
            )->withAdditionalTransformation(
                $this->refinery->custom()->transformation(function ($v): true {
                    $this->settings->set("wopi_discovery_url", $v);

                    return true;
                })
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
                    )->withAdditionalTransformation(
                        $this->refinery->custom()->transformation(function ($v): true {
                            if ($v === null || $v === 0) {
                                $this->settings->delete("saving_interval");
                                return true;
                            }

                            $this->settings->set("saving_interval", (string) $v);

                            return true;
                        })
                    )->withValue(
                        $saving_interval_value
                    )
            ],
            $this->lng->txt("activate_saving_interval")
        )->withValue(
            $saving_interval_value === null ? null : [$saving_interval_value]
        )->withAdditionalTransformation(
            $this->refinery->custom()->transformation(function ($v) {
                if ($v === null || $v === [null]) {
                    $this->settings->delete("saving_interval");
                }
                return $v;
            })
        );

        return $this->ui_factory
            ->input()
            ->field()
            ->section(
                [
                    $this->ui_factory
                        ->input()
                        ->field()
                        ->optionalGroup(
                            [$wopi_url, $saving_interval],
                            $this->lng->txt("activate_wopi")
                        )
                        ->withDisabled(!$this->write_access)
                        ->withValue(
                            $wopi_discovery_url === null ? null : [
                                $wopi_discovery_url,
                                $saving_interval_value === null ? null : [$saving_interval_value]
                            ]
                        )->withAdditionalTransformation(
                            $this->refinery->custom()->transformation(function ($v) {
                                if ($v === null || $v === [null]) {
                                    $this->settings->set("wopi_activated", '0');
                                    $this->settings->delete("wopi_discovery_url");
                                } else {
                                    $this->settings->set("wopi_activated", "1");
                                }
                                return $v;
                            })
                        )
                ],
                $this->lng->txt("wopi_settings"),
            );
    }

    public function proceed(RequestInterface $request): bool
    {
        $this->form = $this->form->withRequest($request);

        return $this->form->getData() !== null;
    }

    public function getHTML(): string
    {
        return $this->ui_renderer->render($this->form);
    }
}
