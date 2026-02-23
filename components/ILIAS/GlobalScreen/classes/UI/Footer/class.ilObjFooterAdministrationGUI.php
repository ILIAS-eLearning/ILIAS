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

use ILIAS\DI\Container;
use ILIAS\GlobalScreen\GUI\Pons;
use ILIAS\GlobalScreen\UI\Footer\Translation\TranslationsRepositoryDB;

/**
 * @author            Fabian Schmid <fabian@sr.solutions>
 *
 * @ilCtrl_isCalledBy ilObjFooterAdministrationGUI: ilAdministrationGUI
 * @ilCtrl_Calls      ilObjFooterAdministrationGUI: ilPermissionGUI
 * @ilCtrl_Calls      ilObjFooterAdministrationGUI: ilFooterGroupsGUI
 */
final class ilObjFooterAdministrationGUI extends ilObject2GUI
{
    /**
     * @var string
     */
    public const CMD_DEFAULT = 'view';
    /**
     * @var string
     */
    public const TAB_INDEX = 'index';
    /**
     * @var string
     */
    public const TAB_PERMISSIONS = 'permissions';
    /**
     * @var string
     */
    public const TAB_GROUPS = 'groups';
    /**
     * @var string
     */
    public const TAB_SUB_ITEMS = 'sub_items';
    private readonly Container $dic;

    public function __construct()
    {
        global $DIC;

        $this->dic = $DIC;

        $this->ref_id = $DIC->http()->wrapper()->query()->has('ref_id')
            ? $DIC->http()->wrapper()->query()->retrieve('ref_id', $DIC->refinery()->kindlyTo()->int())
            : null;

        parent::__construct($this->ref_id);
        $this->ctrl = $DIC->ctrl();
        $this->assignObject();
    }

    #[\Override]
    public function executeCommand(): void
    {
        $this->prepareOutput();

        $mediator = Pons::fromDIC(['gsfo', 'rbac', 'common'], new TranslationsRepositoryDB($this->dic->database()));
        $tabs = $mediator->tabs();
        $tabs->add(
            $view = $tabs
                ->build(self::TAB_INDEX, 'groups', [self::class])
                ->withPermission('read'),
            $groups = $tabs
                ->build(self::TAB_GROUPS, self::TAB_GROUPS, [ilFooterGroupsGUI::class], $view)
                ->withPermission('read'),
            $entries = $tabs
                ->build(
                    self::TAB_SUB_ITEMS,
                    self::TAB_SUB_ITEMS,
                    [[ilFooterGroupsGUI::class, ilFooterEntriesGUI::class]],
                    $groups
                )
                ->withPermission('read'),
            $permissions = $tabs
                ->build(self::TAB_PERMISSIONS, 'rbac_permissions', [[self::class, ilPermissionGUI::class], 'perm'])
                ->withPermission('edit_permissions')
        );

        $next_class = $this->ctrl->getNextClass();
        if ($next_class === '') {
            $this->ctrl->redirectByClass(
                ilFooterGroupsGUI::class
            );
        }

        $mediator->handle(self::TAB_INDEX, [ilPermissionGUI::class]);

        switch (strtolower((string) $next_class)) {
            case strtolower(ilPermissionGUI::class):
                $tabs->activate(self::TAB_PERMISSIONS);
                $this->ctrl->forwardCommand(new ilPermissionGUI($this));
                return;
            default:
                return;
        }
    }

    public function getType(): string
    {
        return 'gsfo';
    }
}
