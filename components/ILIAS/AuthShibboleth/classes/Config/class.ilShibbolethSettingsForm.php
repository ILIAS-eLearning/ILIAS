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

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer;
use ILIAS\UI\Component\Input\Container\Form\Standard as StandardForm;
use Psr\Http\Message\RequestInterface;
use ILIAS\Refinery\Factory as Refinery;

/**
 * Class ilShibbolethSettingsForm
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilShibbolethSettingsForm
{
    protected string $action;
    protected ilCtrl $ctrl;
    protected ?StandardForm $form = null;
    protected ilLanguage $lng;
    protected ilRbacReview $rbac_review;
    protected Refinery $refinery;
    protected Renderer $renderer;
    protected RequestInterface $request;
    protected ilShibbolethSettings $settings;
    protected UIFactory $ui;

    public function __construct(ilShibbolethSettings $settings, string $action)
    {
        global $DIC;

        $this->action = $action;
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->rbac_review = $DIC->rbac()->review();
        $this->refinery = $DIC->refinery();
        $this->renderer = $DIC->ui()->renderer();
        $this->request = $DIC->http()->request();
        $this->settings = $settings;
        $this->ui = $DIC->ui()->factory();

        $this->initForm();
    }

    protected function txt(string $var): string
    {
        return $this->lng->txt($var);
    }

    protected function infoTxt(string $var): string
    {
        return $this->txt($var . '_info');
    }

    public function getHTML(): string
    {
        return $this->renderer->render($this->form);
    }

    public function initForm(): void
    {
        $field = $this->ui->input()->field();
        $custom_trafo = fn(callable $c) => $this->refinery->custom()->transformation($c);
        /** @noRector  */
        $active = $field->checkbox($this->txt('shib_active'), $this->lng->txt("auth_shib_instructions"))
                        ->withValue($this->settings->isActive())
                        ->withAdditionalTransformation($custom_trafo(function ($v): void {
                            $this->settings->setActive((bool) $v);
                        }));

        $auth_allow_local = $field->checkbox($this->txt('auth_allow_local'))
                                  ->withValue($this->settings->isLocalAuthAllowed())
                                  ->withAdditionalTransformation($custom_trafo(function ($v): void {
                                      $this->settings->setAllowLocalAuth((bool) $v);
                                  }));

        $account_creation = $field->switchableGroup(
            [
                ilShibbolethSettings::ACCOUNT_CREATION_ENABLED => $field->group(
                    [],
                    $this->lng->txt("shib_account_creation_enabled"),
                    $this->lng->txt("shib_account_creation_enabled_info")
                ),
                ilShibbolethSettings::ACCOUNT_CREATION_WITH_APPROVAL => $field->group(
                    [],
                    $this->lng->txt("shib_account_creation_with_approval"),
                    $this->lng->txt("shib_account_creation_with_approval_info")
                ),
                ilShibbolethSettings::ACCOUNT_CREATION_DISABLED => $field->group(
                    [],
                    $this->lng->txt("shib_account_creation_disabled"),
                    $this->lng->txt("shib_account_creation_disabled_info")
                )
            ],
            $this->lng->txt("shib_account_creation"),
            $this->lng->txt("shib_account_creation_info")
        )->withValue(
            $this->settings->getAccountCreation()
        )->withRequired(
            true
        )->withAdditionalTransformation($custom_trafo(function ($v): void {
            $this->settings->setAccountCreation((string) $v[0]);
        }));

        $default_user_role = $field->select($this->txt('shib_user_default_role'), $this->getRoles())
                                   ->withRequired(true)
                                   ->withValue($this->settings->getDefaultRole())
                                   ->withAdditionalTransformation($custom_trafo(function ($v): void {
                                       $this->settings->setDefaultRole((int) $v);
                                   }));

        $basic_section = $field->section([
            $active,
            $auth_allow_local,
            $account_creation,
            $default_user_role,
        ], $this->txt('shib'));

        // Federation
        $federation_name = $field->text($this->txt('shib_federation_name'))
                                 ->withRequired(true)
                                 ->withValue($this->settings->getFederationName())
                                 ->withAdditionalTransformation($custom_trafo(function ($v): void {
                                     $this->settings->setFederationName((string) $v);
                                 }));

        $login_type = $field->switchableGroup([
            'internal_wayf' => $field->group([
                $field->textarea('', $this->txt('shib_idp_list'))
                      ->withValue($this->settings->getIdPList())
                      ->withAdditionalTransformation($custom_trafo(function ($v): void {
                          $this->settings->setIdPList((string) $v);
                      }))
            ], $this->txt('shib_login_internal_wayf')),
            'external_wayf' => $field->group([
                $field->text('', $this->txt('shib_login_button'))
                      ->withValue($this->settings->getLoginButton())
                      ->withAdditionalTransformation($custom_trafo(function ($v): void {
                          $this->settings->setLoginButton((string) $v);
                      }))
            ], $this->txt('shib_login_external_wayf')),
            'embedded_wayf' => $field->group([], $this->txt('shib_login_embedded_wayf'))
                                     ->withByline($this->txt('shib_login_embedded_wayf_description')),
        ], $this->txt('shib_login_type'))
                            ->withRequired(true)
                            ->withValue($this->settings->getOrganisationSelectionType())
                            ->withAdditionalTransformation($custom_trafo(function ($v): void {
                                $this->settings->setOrganisationSelectionType($v[0]);
                            }));

        $instructions = $field->textarea($this->txt('auth_login_instructions'))
                              ->withValue($this->settings->get('login_instructions', ''))
                              ->withAdditionalTransformation($custom_trafo(function ($v): void {
                                  $this->settings->set('login_instructions', (string) $v);
                              }));

        $data_manipulation = $field->text($this->txt('shib_data_conv'))
                                   ->withValue($this->settings->get('data_conv'))
                                   ->withAdditionalTransformation($custom_trafo(function ($v): void {
                                       $this->settings->set('data_conv', (string) $v);
                                   }));

        $federation_section = $field->section([
            $federation_name,
            $login_type,
            $instructions,
            $data_manipulation
        ], '');

        // User Fields
        $fields = [];
        $fields[] = $field->text($this->txt('shib_login'))
                          ->withValue($this->settings->get('shib_login'))
                          ->withRequired(true)
                          ->withAdditionalTransformation($custom_trafo(function ($v): void {
                              $this->settings->set('shib_login', (string) $v);
                          }));
        foreach ($this->settings->getUserFields() as $field_name => $required) {
            $fields[] = $field->text($this->txt($field_name))
                              ->withValue($this->settings->get($field_name))
                              ->withRequired($required)
                              ->withAdditionalTransformation($custom_trafo(function ($v) use ($field_name): void {
                                  $this->settings->set($field_name, (string) $v);
                              }));
            $fields[] = $field->checkbox($this->txt('shib_update'))
                              ->withValue((bool) $this->settings->get('update_' . $field_name))
                              ->withAdditionalTransformation($custom_trafo(function ($v) use ($field_name): void {
                                  $this->settings->set('update_' . $field_name, (string) $v);
                              }));
        }

        $user_fields = $field->section(
            $fields,
            ''
        );

        // COMPLETE FORM

        $this->form = $this->ui->input()->container()->form()->standard(
            $this->action,
            [
                $basic_section,
                $federation_section,
                $user_fields
            ]
        );
    }

    public function setValuesByPost(): void
    {
        $this->form = $this->form->withRequest($this->request);
    }

    protected function fillObject(): bool
    {
        return $this->form->getData() !== null;
    }

    public function saveObject(): bool
    {
        if (!$this->fillObject()) {
            return false;
        }
        $this->settings->store();
        return true;
    }

    /**
     * @return array<int|string, string>
     */
    protected function getRoles(int $filter = ilRbacReview::FILTER_ALL_GLOBAL): array
    {
        $opt = [];
        foreach ($this->rbac_review->getRolesByFilter($filter) as $role) {
            $opt[$role['obj_id']] = $role['title'] . ' (' . $role['obj_id'] . ')';
        }

        return $opt;
    }
}
