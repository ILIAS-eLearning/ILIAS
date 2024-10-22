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

namespace ILIAS\LegalDocuments;

use ILIAS\LegalDocuments\ConsumerSlots\Agreement;
use ILIAS\LegalDocuments\ConsumerSlots\CriterionToCondition;
use ILIAS\LegalDocuments\ConsumerSlots\SelfRegistration;
use ILIAS\LegalDocuments\ConsumerSlots\WithdrawProcess;
use ILIAS\LegalDocuments\ConsumerSlots\PublicApi;
use ILIAS\Refinery\Constraint;
use ILIAS\UI\Component\MainControls\Footer;
use ilObjUser;
use ilNonEditableValueGUI;

interface UseSlot
{
    /**
     * @param callable(): void $after_login
     */
    public function afterLogin(callable $after_login): self;

    /**
     * @param callable(Footer): Footer $show
     */
    public function showInFooter(callable $show): self;

    /**
     * @param callable(): string $show
     */
    public function showOnLoginPage(callable $show): self;

    /**
     * @param callable(int[]): int[] $only_visible_users
     */
    public function hasOnlineStatusFilter(callable $only_visible_users): self;

    /**
     * @param array<string, callable(DocumentContent): Component> $content_as_component
     * @param null|SelectionMap<ConditionDefinition> $available_conditions
     */
    public function hasDocuments(array $content_as_component = [], ?SelectionMap $available_conditions = null): self;

    /**
     * @param callable(ilObjUser): array<string, string|ilNonEditableValueGUI> $fields
     */
    public function hasUserManagementFields(callable $fields): self;
    public function canWithdraw(WithdrawProcess $withdraw_process): self;

    /**
     * @param callable(string, string): PageFragment $public_page
     */
    public function hasPublicPage(callable $public_page, ?string $goto_name = null): self;
    public function hasAgreement(Agreement $on_login, ?string $goto_name = null): self;
    public function hasHistory(): self;
    public function onSelfRegistration(SelfRegistration $self_registration): self;
    public function canReadInternalMails(Constraint $constraint): self;
    public function canUseSoapApi(Constraint $constraint): self;
    public function hasPublicApi(PublicApi $api): self;
}
