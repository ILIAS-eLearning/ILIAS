<?php declare(strict_types=1);

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

use OrgUnit\PublicApi\OrgUnitUserService;
use OrgUnit\User\ilOrgUnitUser;

/**
 * Class ilMailTemplateContext
 * @author  Michael Jansen <mjansen@databay.de>
 * @ingroup ServicesMail
 */
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

    public function getLanguage() : ilLanguage
    {
        return $this->language ?? $this->languageHelper->getCurrentLanguage();
    }

    abstract public function getId() : string;

    abstract public function getTitle() : string;

    abstract public function getDescription() : string;

    private function getGenericPlaceholders() : array
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
            'firstname_last_name_superior' => [
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

    final public function getPlaceholders() : array
    {
        $placeholders = $this->getGenericPlaceholders();
        $specific = $this->getSpecificPlaceholders();

        return array_merge($placeholders, $specific);
    }

    abstract public function getSpecificPlaceholders() : array;

    abstract public function resolveSpecificPlaceholder(
        string $placeholder_id,
        array $context_parameters,
        ilObjUser $recipient = null,
        bool $html_markup = false
    ) : string;

    public function resolvePlaceholder(
        string $placeholder_id,
        array $context_parameters,
        ilObjUser $recipient = null,
        bool $html_markup = false
    ) : string {
        if ($recipient !== null) {
            $this->initLanguage($recipient);
        }

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
                $resolved = $this->envHelper->getHttpPath()
                    . '/login.php?client_id='
                    . $this->envHelper->getClientId();
                break;

            case 'installation_name' === $placeholder_id:
                $resolved = $this->envHelper->getClientId();
                break;

            case 'firstname_last_name_superior' === $placeholder_id && $recipient !== null:
                $ouUsers = $this->orgUnitUserService->getUsers([$recipient->getId()], true);
                foreach ($ouUsers as $ouUser) {
                    $superiors = $ouUser->getSuperiors();

                    $superiorUsrIds = array_map(static function (ilOrgUnitUser $ouUser) : int {
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
                    $recipient,
                    $html_markup
                );

                ilDatePresentation::setLanguage($datePresentationLanguage);
                break;
        }

        return $resolved;
    }
    
    protected function initLanguage(ilObjUser $user) : void
    {
        $this->initLanguageByIso2Code($user->getLanguage());
    }

    protected function initLanguageByIso2Code(string $isoCode) : void
    {
        $this->language = $this->languageHelper->getLanguageByIsoCode($isoCode);
        $this->language->loadLanguageModule('mail');
    }
}
