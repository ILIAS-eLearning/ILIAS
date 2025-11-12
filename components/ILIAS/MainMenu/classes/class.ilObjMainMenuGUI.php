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
use ILIAS\DI\Container;

/**
 * Class ilObjMainMenuGUI
 * @ilCtrl_IsCalledBy ilObjMainMenuGUI: ilAdministrationGUI
 * @ilCtrl_Calls      ilObjMainMenuGUI: ilPermissionGUI
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 */
class ilObjMainMenuGUI extends ilObject2GUI
{
    /**
     * @var string
     */
    public const MAINTAB_VIEW = 'view';
    /**
     * @var string
     */
    public const SUBTAB_TOP_ITEMS = 'top_items';
    /**
     * @var string
     */
    public const SUBTAB_SUB_ITEMS = 'sub_items';
    /**
     * @var string
     */
    public const TAB_PERMISSIONS = 'permissions';

    private Container $dic;

    /**
     * ilObjMainMenuGUI constructor.
     */
    public function __construct()
    {
        global $DIC;

        $this->dic = $DIC;

        $this->ref_id = $DIC->http()->wrapper()->query()->has('ref_id')
            ? $DIC->http()->wrapper()->query()->retrieve('ref_id', $DIC->refinery()->kindlyTo()->int())
            : null;

        parent::__construct($this->ref_id);
        $this->ctrl = $DIC['ilCtrl'];
        $this->assignObject();
    }

    #[\Override]
    public function executeCommand(): void
    {
        $this->prepareOutput();

        $mediator = Pons::fromDIC(['mme', 'gsfo'], new ilMMItemTranslationRepository($this->dic->database()));
        $tabs = $mediator->tabs();
        $tabs->add(
            $view = $tabs
                ->build(self::MAINTAB_VIEW, self::MAINTAB_VIEW, [self::class])
                ->withPermission('read'),
            $main = $tabs
                ->build(self::SUBTAB_TOP_ITEMS, 'subtab_topitems', [ilMMTopItemGUI::class], $view)
                ->withPermission('read'),
            $sub_items = $tabs
                ->build(
                    self::SUBTAB_SUB_ITEMS,
                    'subtab_subitems',
                    [[ilMMTopItemGUI::class, ilMMSubItemGUI::class]],
                    $main
                )
                ->withPermission('read'),
            // Permissions Tab
            $tabs
                ->build(self::TAB_PERMISSIONS, 'rbac_permissions', [[self::class, ilPermissionGUI::class], 'perm'])
                ->withPermission('edit_permissions')
        );

        $next_class = $this->ctrl->getNextClass();
        if ($next_class === '') {
            $this->ctrl->redirectByClass(
                ilMMTopItemGUI::class
            );
        }

        $mediator->handle(self::SUBTAB_TOP_ITEMS, [ilPermissionGUI::class, ilMMUploadHandlerGUI::class]);

        switch ($next_class) {
            case strtolower(ilPermissionGUI::class):
                $tabs->activate(self::TAB_PERMISSIONS);
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;
            case strtolower(ilMMUploadHandlerGUI::class):
                $g = new ilMMUploadHandlerGUI();
                $this->ctrl->forwardCommand($g);
                break;
            default:
                break;
        }
    }

    public function getType(): string
    {
        return "mme";
    }
}
