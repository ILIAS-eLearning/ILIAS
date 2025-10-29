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
use ILIAS\GlobalScreen\GUI\Pons;

/**
 * @ilCtrl_IsCalledBy ilObjStaticUrlServiceGUI: ilAdministrationGUI
 * @ilCtrl_Calls      ilObjStaticUrlServiceGUI: ilPermissionGUI
 * @author            Fabian Schmid <fabian@sr.solutions>
 */
class ilObjStaticUrlServiceGUI extends ilObject2GUI
{
    /**
     * @var string
     */
    public const CMD_DEFAULT = 'main';
    /**
     * @var string
     */
    public const TAB_INDEX = 'index';
    /**
     * @var string
     */
    public const TAB_PERMISSIONS = 'permissions';
    public const string TAB_INFO = 'info';

    public function __construct()
    {
        global $DIC;

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

        $mediator = Pons::fromDIC(['stus', 'rbac', 'common', 'ui']);
        $tabs = $mediator->tabs();
        $tabs->add(
            $view = $tabs
                ->build(self::TAB_INDEX, 'index', [ShortlinkAdministrationGUI::class])
                ->withPermission('read'),
            /*$view = $tabs
                ->build(self::TAB_INFO, 'info', [ShortlinkInfoGUI::class])
                ->withPermission('read'),*/
            $permissions = $tabs
                ->build(self::TAB_PERMISSIONS, 'rbac_permissions', [[self::class, ilPermissionGUI::class], 'perm'])
                ->withPermission('edit_permissions')
        );

        $next_class = $this->ctrl->getNextClass();
        if ($next_class === '') {
            $this->ctrl->redirectByClass(
                ShortlinkAdministrationGUI::class,
                'index'
            );
        }

        $mediator->handle(self::TAB_INDEX, [ilPermissionGUI::class]);

        // must handle PermissionsGUI separately as it is not handled by the mediator
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
        return 'stus';
    }
}
