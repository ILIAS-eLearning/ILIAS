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

namespace ILIAS\User;

use ILIAS\User\LocalDIC;
use ILIAS\User\Account\DeleteAccountGUI;
use ILIAS\User\Profile\PersonalProfileGUI;
use ILIAS\User\Profile\PublicProfileGUI;
use ILIAS\User\Settings\PersonalSettingsGUI;
use ILIAS\User\Settings\StartingPoint\Repository as StartingPointRepository;
use ILIAS\Data\ReferenceId;
use ILIAS\LegalDocuments\Conductor as LegalDocumentsConductor;
use ILIAS\StaticURL\Handler\Handler;
use ILIAS\StaticURL\Request\Request;
use ILIAS\StaticURL\Context;
use ILIAS\StaticURL\Response\Response;
use ILIAS\StaticURL\Response\Factory;
use ILIAS\StaticURL\Handler\BaseHandler;
use ILIAS\StaticURL\Builder\StandardURIBuilder;
use ILIAS\StaticURL\StaticURLConfig;

class StaticURLHandler extends BaseHandler implements Handler
{
    public const NAMESPACE = 'usr';
    public const CHANGE_EMAIL_OPERATION = 'email';
    public const REGISTRATION_OPERATION = 'registration';
    public const USERNAME_ASSIST_OPERATION = 'nameassist';
    public const PASSWORD_ASSIST_OPERATION = 'pwassist';
    public const DEL_OWN_ACCOUNT_OPERATION = 'delown';
    public const CONTACT_APPROVE_OPERATION = '_contact_approved';
    public const CONTACT_IGNORE_OPERATION = '_contact_ignored';

    private readonly LegalDocumentsConductor $legal_documents;
    private readonly \ilObjUser $user;
    private readonly StartingPointRepository $starting_point_repository;

    public function __construct()
    {
        global $DIC;
        $this->legal_documents = $DIC['legalDocuments'];
        $this->user = $DIC['ilUser'];
        $this->starting_point_repository = LocalDIC::dic()[StartingPointRepository::class];
    }

    public function getNamespace(): string
    {
        return self::NAMESPACE;
    }

    public function handle(
        Request $request,
        Context $context,
        Factory $response_factory
    ): Response {
        $additional_params = $request->getAdditionalParameters();
        $cmd = $additional_params[0] ?? '';

        $uri = match ($cmd) {
            self::CHANGE_EMAIL_OPERATION => $context->isUserLoggedIn()
                    ? $this->buildChangeEmailUrl($additional_params[1], $context->ctrl())
                    : $this->getLoginUrl($request, $context),
            self::REGISTRATION_OPERATION => $context->ctrl()->redirectByClass(
                [\ilStartUpGUI::class, \ilAccountRegistrationGUI::class],
                ''
            ),
            self::USERNAME_ASSIST_OPERATION => $context->ctrl()->redirectByClass(
                [\ilStartUpGUI::class, \ilPasswordAssistanceGUI::class],
                'showUsernameAssistanceForm'
            ),
            self::PASSWORD_ASSIST_OPERATION => $context->ctrl()->redirectByClass(
                [\ilStartUpGUI::class, \ilPasswordAssistanceGUI::class],
                ''
            ),
            self::CONTACT_APPROVE_OPERATION => $this->buildProfileUrl(
                $context->ctrl(),
                $request->getReferenceId(),
                'approveContactRequest'
            ),
            self::CONTACT_IGNORE_OPERATION => $this->buildProfileUrl(
                $context->ctrl(),
                $request->getReferenceId(),
                'ignoreContactRequest'
            ),
            self::DEL_OWN_ACCOUNT_OPERATION => $this->buildDeleteUsrUrl($context),
            default => $this->getRedirectToOtherComponentsOrProfile(
                $context->ctrl(),
                $request->getReferenceId(),
                $cmd
            )
        };

        return $response_factory->can($uri);
    }

    private function buildChangeEmailUrl(string $token, \ilCtrl $ctrl): string
    {
        $ctrl->setParameterByClass(PersonalProfileGUI::class, 'token', $token);
        $link = $ctrl->getLinkTargetByClass([\ilDashboardGUI::class, PersonalProfileGUI::class], PersonalProfileGUI::CHANGE_EMAIL_CMD);
        $ctrl->clearParameterByClass(PersonalProfileGUI::class, 'token');
        return $link;
    }

    private function getLoginUrl(
        Request $request,
        Context $context
    ): string {
        $target = (new StandardURIBuilder(new StaticURLConfig()))->buildTarget(
            $request->getNamespace(),
            $request->getReferenceId(),
            $request->getAdditionalParameters()
        );

        return '/login.php?target='
            . str_replace('/', '_', rtrim($target, '/'))
            . '&cmd=force_login&lang=' . $context->getUserLanguage();

    }

    private function buildDeleteUsrUrl(
        Context $context
    ): string {
        if ($context->getUserId() !== ANONYMOUS_USER_ID
            && $this->user->hasDeletionFlag()) {
            $context->ctrl()->setTargetScript('ilias.php');
            return $context->ctrl()->getLinkTargetByClass(
                [\ilDashboardGUI::class, PersonalSettingsGUI::class, DeleteAccountGUI::class],
                'deleteOwnAccountStep2'
            );
        }

        $context->mainTemplate()->setOnScreenMessage(
            'failure',
            $context->lng()->txt('account_not_flagged_for_deletion'),
            true
        );
        return $this->starting_point_repository->getValidAndAccessibleStartingPointAsUrl();
    }

    private function getRedirectToOtherComponentsOrProfile(
        \ilCtrl $ctrl,
        ?ReferenceId $target_user_id,
        string $cmd
    ): string {
        if (str_starts_with($cmd, '_bdg')) {
            return $ctrl->getLinkTargetByClass(\ilDashboardGUI::class, 'jumpToBadges');
        }

        $legal_documents_target = $this->legal_documents->findGotoLink($cmd);
        if ($legal_documents_target->isOK()) {
            $ctrl->setTargetScript('ilias.php');
            foreach ($legal_documents_target->value()->queryParams() as $key => $value) {
                $ctrl->setParameterByClass(
                    $legal_documents_target->value()->guiName(),
                    (string) $key,
                    $value
                );
            }
            return $ctrl->getLinkTargetByClass(
                $legal_documents_target->value()->guiPath(),
                $legal_documents_target->value()->command()
            );
        }

        return $this->buildProfileUrl(
            $ctrl,
            $target_user_id,
            PublicProfileGUI::DEFAULT_CMD
        );
    }

    private function buildProfileUrl(
        \ilCtrl $ctrl,
        ?ReferenceId $target_user_id,
        string $cmd
    ): string {
        if ($target_user_id === null) {
            return $ctrl->getLinkTargetByClass(
                [\ilDashboardGUI::class],
                'jumpToProfile'
            );
        }
        $ctrl->setParameterByClass(PublicProfileGUI::class, 'user_id', $target_user_id->toInt());
        return $ctrl->getLinkTargetByClass([\ilPublicProfileBaseClassGUI::class, PublicProfileGUI::class], $cmd);
    }
}
