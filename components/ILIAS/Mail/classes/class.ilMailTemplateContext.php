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

use OrgUnit\User\ilOrgUnitUser;
use OrgUnit\PublicApi\OrgUnitUserService;

abstract class ilMailTemplateContext
{
    protected ilLanguage $language;
    protected ilMailEnvironmentHelper $envHelper;
    protected ilMailLanguageHelper $languageHelper;
    protected ilMailUserHelper $userHelper;
    protected OrgUnitUserService $orgUnitUserService;

    public function __construct(
        OrgUnitUserService $orgUnitUserService = null,
        ilMailEnvironmentHelper $envHelper = null,
        ilMailUserHelper $usernameHelper = null,
        ilMailLanguageHelper $languageHelper = null
    ) {
        $this->orgUnitUserService = $orgUnitUserService ?? new OrgUnitUserService();
        $this->envHelper = $envHelper ?? new ilMailEnvironmentHelper();
        $this->userHelper = $usernameHelper ?? new ilMailUserHelper();
        $this->languageHelper = $languageHelper ?? new ilMailLanguageHelper();
    }

    public function getLanguage(): ilLanguage
    {
        return $this->language ?? $this->languageHelper->getCurrentLanguage();
    }

    abstract public function getId(): string;

    abstract public function getTitle(): string;

    abstract public function getDescription(): string;

    /**
     * @return array{
     *     mail_salutation: array{placeholder: string, label: string, requiresRecipient: true, supportsNestedPlaceholders: true},
     *     first_name: array{placeholder: string, label: string, requiresRecipient: true},
     *     last_name: array{placeholder: string, label: string, requiresRecipient: true},
     *     login: array{placeholder: string, label: string, requiresRecipient: true},
     *     title: array{placeholder: string, label: string, requiresRecipient: true},
     *     firstname_lastname_superior: array{placeholder: string, label: string, requiresRecipient: true},
     *     ilias_url: array{placeholder: string, label: string},
     *     installation_name: array{placeholder: string, label: string}
     * }
     */
    private function getGenericPlaceholders(): array
    {
        return [
            'mail_salutation' => [
                'placeholder' => 'MAIL_SALUTATION',
                'label' => $this->getLanguage()->txt('mail_nacc_salutation'),
                'requiresRecipient' => true,
                'supportsNestedPlaceholders' => true,
            ],
            'first_name' => [
                'placeholder' => 'FIRST_NAME',
                'label' => $this->getLanguage()->txt('firstname'),
                'requiresRecipient' => true,
            ],
            'last_name' => [
                'placeholder' => 'LAST_NAME',
                'label' => $this->getLanguage()->txt('lastname'),
                'requiresRecipient' => true,
            ],
            'login' => [
                'placeholder' => 'LOGIN',
                'label' => $this->getLanguage()->txt('mail_nacc_login'),
                'requiresRecipient' => true,
            ],
            'title' => [
                'placeholder' => 'TITLE',
                'label' => $this->getLanguage()->txt('mail_nacc_title'),
                'requiresRecipient' => true,
            ],
            'firstname_lastname_superior' => [
                'placeholder' => 'FIRSTNAME_LASTNAME_SUPERIOR',
                'label' => $this->getLanguage()->txt('mail_firstname_last_name_superior'),
                'requiresRecipient' => true,
            ],
            'ilias_url' => [
                'placeholder' => 'ILIAS_URL',
                'label' => $this->getLanguage()->txt('mail_nacc_ilias_url'),
                'requiresRecipient' => false,
            ],
            'installation_name' => [
                'placeholder' => 'INSTALLATION_NAME',
                'label' => $this->getLanguage()->txt('mail_nacc_installation_name'),
                'requiresRecipient' => false,
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public function getNestedPlaceholders(): array
    {
        $nested_placeholders = [];
        foreach ($this->getPlaceholders() as $key => $ph) {
            if (isset($ph['supportsNestedPlaceholders']) && $ph['supportsNestedPlaceholders']) {
                $nested_placeholders[] = $ph['placeholder'];
            }
        }

        return $nested_placeholders;
    }

    /**
     * @return array<string, array{
     *     placeholder: string,
     *     label: string,
     *     requiresRecipient?: bool,
     *     supportsNestedPlaceholders?: bool
     * }>
     */
    final public function getPlaceholders(): array
    {
        $placeholders = $this->getGenericPlaceholders();
        $specific = $this->getSpecificPlaceholders();

        return array_merge($placeholders, $specific);
    }

    /**
     * @return array<string, array{
     *     placeholder: string,
     *     label: string,
     *     requiresRecipient?: bool,
     *     supportsNestedPlaceholders?: bool
     * }>
     */
    abstract public function getSpecificPlaceholders(): array;

    public function requiresRecipientByPlaceholderName(string $placeholder_name): bool
    {
        $found = $this->findPlaceholderByMustacheName($placeholder_name);
        if ($found === null) {
            return false;
        }

        return $this->placeholderDefinitionRequiresRecipient($found['definition']);
    }

    /**
     * @return array{key: string, definition: array{
     *     placeholder: string,
     *     label: string,
     *     requiresRecipient?: bool,
     *     supportsNestedPlaceholders?: bool
     * }}|null
     */
    private function findPlaceholderByMustacheName(string $placeholder_name): ?array
    {
        foreach ($this->getPlaceholders() as $key => $placeholder_definition) {
            if (strtoupper($placeholder_definition['placeholder']) !== strtoupper($placeholder_name)) {
                continue;
            }

            return [
                'key' => (string) $key,
                'definition' => $placeholder_definition,
            ];
        }

        return null;
    }

    /**
     * @param array{
     *     placeholder: string,
     *     label: string,
     *     requiresRecipient?: bool,
     *     supportsNestedPlaceholders?: bool
     * } $placeholder_definition
     */
    private function placeholderDefinitionRequiresRecipient(array $placeholder_definition): bool
    {
        return $placeholder_definition['requiresRecipient'] ?? false;
    }

    /**
     * @param array<string, mixed> $context_parameters
     */
    abstract public function resolveSpecificPlaceholder(
        string $placeholder_id,
        array $context_parameters,
        ilObjUser $recipient = null
    ): string;

    /**
     * @param array<string, mixed> $context_parameters
     */
    public function resolvePlaceholder(
        string $placeholder_id,
        array $context_parameters,
        ilObjUser $recipient = null
    ): string {
        if ($recipient !== null) {
            $this->initLanguage($recipient);
        }

        $placeholder_id = strtolower($placeholder_id);
        $resolved = '';
        switch (true) {
            case ('mail_salutation' === $placeholder_id && $recipient !== null):
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

            case ('first_name' === $placeholder_id && $recipient !== null):
                $resolved = $recipient->getFirstname();
                break;

            case ('last_name' === $placeholder_id && $recipient !== null):
                $resolved = $recipient->getLastname();
                break;

            case ('login' === $placeholder_id && $recipient !== null):
                $resolved = $recipient->getLogin();
                break;

            case ('title' === $placeholder_id && $recipient !== null):
                $resolved = $recipient->getUTitle();
                break;

            case 'ilias_url' === $placeholder_id:
                $resolved = $this->envHelper->getHttpPath() . ' ';
                break;

            case 'installation_name' === $placeholder_id:
                $resolved = $this->envHelper->getClientId();
                break;

            case 'firstname_lastname_superior' === $placeholder_id && $recipient !== null:
                $ouUsers = $this->orgUnitUserService->getUsers([$recipient->getId()], true);
                foreach ($ouUsers as $ouUser) {
                    $superiors = $ouUser->getSuperiors();

                    $superiorUsrIds = array_map(static function (ilOrgUnitUser $ouUser): int {
                        return $ouUser->getUserId();
                    }, $superiors);

                    $usrIdByNameMap = $this->userHelper->getUsernameMapForIds($superiorUsrIds);

                    $resolved = implode(', ', $usrIdByNameMap);
                    break;
                }
                break;

            case !array_key_exists($placeholder_id, $this->getGenericPlaceholders()):
                $datePresentationLanguage = ilDatePresentation::getLanguage();
                ilDatePresentation::setLanguage($this->getLanguage());

                $resolved = $this->resolveSpecificPlaceholder(
                    $placeholder_id,
                    $context_parameters,
                    $recipient
                );

                ilDatePresentation::setLanguage($datePresentationLanguage);
                break;
        }

        return $resolved;
    }

    protected function initLanguage(ilObjUser $user): void
    {
        $this->initLanguageByIso2Code($user->getLanguage());
    }

    protected function initLanguageByIso2Code(string $isoCode): void
    {
        $this->language = $this->languageHelper->getLanguageByIsoCode($isoCode);
        $this->language->loadLanguageModule('mail');
    }
}
