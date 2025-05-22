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

use OrgUnit\PublicApi\OrgUnitUserService;
use OrgUnit\User\ilOrgUnitUser;

abstract class ilMailTemplateContext
{
    protected ilLanguage $language;
    protected ilMailEnvironmentHelper $env_helper;
    protected ilMailLanguageHelper $language_helper;
    protected ilMailUserHelper $user_helper;
    protected OrgUnitUserService $org_unit_user_service;

    public function __construct(
        ?OrgUnitUserService $org_unit_user_service = null,
        ?ilMailEnvironmentHelper $environment_helper = null,
        ?ilMailUserHelper $user_helper = null,
        ?ilMailLanguageHelper $language_helper = null
    ) {
        $this->org_unit_user_service = $org_unit_user_service ?? new OrgUnitUserService();
        $this->env_helper = $environment_helper ?? new ilMailEnvironmentHelper();
        $this->user_helper = $user_helper ?? new ilMailUserHelper();
        $this->language_helper = $language_helper ?? new ilMailLanguageHelper();
    }

    public function getLanguage(): ilLanguage
    {
        return $this->language ?? $this->language_helper->getCurrentLanguage();
    }

    abstract public function getId(): string;

    abstract public function getTitle(): string;

    abstract public function getDescription(): string;

    /**
     * @return array{mail_salutation: array{placeholder: string, label: string}, first_name: array{placeholder: string, label: string}, last_name: array{placeholder: string, label: string}, login: array{placeholder: string, label: string}, title: array{placeholder: string, label: string, supportsCondition: true}, firstname_lastname_superior: array{placeholder: string, label: string}, ilias_url: array{placeholder: string, label: string}, installation_name: array{placeholder: string, label: string}}
     */
    private function getGenericPlaceholders(): array
    {
        return [
            'mail_salutation' => [
                'placeholder' => 'MAIL_SALUTATION',
                'label' => $this->getLanguage()->txt('mail_nacc_salutation'),
            ],
            'first_name' => [
                'placeholder' => 'FIRST_NAME',
                'label' => $this->getLanguage()->txt('firstname'),
            ],
            'last_name' => [
                'placeholder' => 'LAST_NAME',
                'label' => $this->getLanguage()->txt('lastname'),
            ],
            'login' => [
                'placeholder' => 'LOGIN',
                'label' => $this->getLanguage()->txt('mail_nacc_login'),
            ],
            'title' => [
                'placeholder' => 'TITLE',
                'label' => $this->getLanguage()->txt('mail_nacc_title'),
                'supportsCondition' => true,
            ],
            'firstname_lastname_superior' => [
                'placeholder' => 'FIRSTNAME_LASTNAME_SUPERIOR',
                'label' => $this->getLanguage()->txt('mail_firstname_last_name_superior'),
            ],
            'ilias_url' => [
                'placeholder' => 'ILIAS_URL',
                'label' => $this->getLanguage()->txt('mail_nacc_ilias_url'),
            ],
            'installation_name' => [
                'placeholder' => 'INSTALLATION_NAME',
                'label' => $this->getLanguage()->txt('mail_nacc_installation_name'),
            ],
        ];
    }

    /**
     * @return array<string, array{placeholder: string, crs_period_end_mail_placeholder: string}>
     */
    final public function getPlaceholders(): array
    {
        $placeholders = $this->getGenericPlaceholders();
        $specific = $this->getSpecificPlaceholders();

        return array_merge($placeholders, $specific);
    }

    /**
     * @return array<string, array{placeholder: string, crs_period_end_mail_placeholder: string}>
     */
    abstract public function getSpecificPlaceholders(): array;

    /**
     * @param array<string, mixed> $context_parameters
     */
    abstract public function resolveSpecificPlaceholder(
        string $placeholder_id,
        array $context_parameters,
        ?ilObjUser $recipient = null
    ): string;

    /**
     * @param array<string, mixed> $context_parameters
     */
    public function resolvePlaceholder(
        string $placeholder_id,
        array $context_parameters,
        ?ilObjUser $recipient = null
    ): string {
        if ($recipient !== null) {
            $this->initLanguage($recipient);
        }

        $placeholder_id = strtolower($placeholder_id);
        $resolved = '';
        switch (true) {
            case ($placeholder_id === 'mail_salutation' && $recipient !== null):
                $resolved = $this->getLanguage()->txt('mail_salutation_n');
                switch ($recipient->getGender()) {
                    case 'f':
                        $resolved = $this->getLanguage()->txt('mail_salutation_f');
                        break;

                    case 'm':
                        $resolved = $this->getLanguage()->txt('mail_salutation_m');
                        break;

                    case 'n':
                        $resolved = $this->getLanguage()->txt('mail_salutation_n');
                        break;
                }
                break;

            case ($placeholder_id === 'first_name' && $recipient !== null):
                $resolved = $recipient->getFirstname();
                break;

            case ($placeholder_id === 'last_name' && $recipient !== null):
                $resolved = $recipient->getLastname();
                break;

            case ($placeholder_id === 'login' && $recipient !== null):
                $resolved = $recipient->getLogin();
                break;

            case ($placeholder_id === 'title' && $recipient !== null):
                $resolved = $recipient->getUTitle();
                break;

            case $placeholder_id === 'ilias_url':
                $resolved = $this->env_helper->getHttpPath() . ' ';
                break;

            case $placeholder_id === 'installation_name':
                $resolved = $this->env_helper->getClientId();
                break;

            case $placeholder_id === 'firstname_lastname_superior' && $recipient !== null:
                $ou_users = $this->org_unit_user_service->getUsers([$recipient->getId()], true);
                foreach ($ou_users as $ou_user) {
                    $superiors = $ou_user->getSuperiors();

                    $superior_usr_ids = array_map(static fn(ilOrgUnitUser $ou_user): int => $ou_user->getUserId(), $superiors);

                    $usr_id_by_name_map = $this->user_helper->getUsernameMapForIds($superior_usr_ids);

                    $resolved = implode(', ', $usr_id_by_name_map);
                    break;
                }
                break;

            case !array_key_exists($placeholder_id, $this->getGenericPlaceholders()):
                $date_presentation_language = ilDatePresentation::getLanguage();
                ilDatePresentation::setLanguage($this->getLanguage());

                $resolved = $this->resolveSpecificPlaceholder(
                    $placeholder_id,
                    $context_parameters,
                    $recipient
                );

                ilDatePresentation::setLanguage($date_presentation_language);
                break;
        }

        return $resolved;
    }

    protected function initLanguage(ilObjUser $user): void
    {
        $this->initLanguageByIso2Code($user->getLanguage());
    }

    protected function initLanguageByIso2Code(string $iso_code): void
    {
        $this->language = $this->language_helper->getLanguageByIsoCode($iso_code);
        $this->language->loadLanguageModule('mail');
    }
}
