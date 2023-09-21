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

use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\Hasher;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\TopItem\TopParentItem;
use ILIAS\UI\Component\Input\Container\Form\Standard;
use ILIAS\UI\Component\Input\Factory as InputFactory;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use ILIAS\FileUpload\MimeType;
use ILIAS\HTTP\Services;

/**
 * Class ilMMTopItemFormGUI
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ilMMTopItemFormGUI
{
    use Hasher;

    private const F_ICON = 'icon';

    private Services $http;

    private ilMMItemRepository $repository;

    private Standard $form;

    private ilMMItemFacadeInterface $item_facade;

    private ilObjMainMenuAccess $access;

    protected ilLanguage $lng;

    protected ilCtrl $ctrl;

    protected ILIAS\UI\Factory $ui_fa;

    protected ILIAS\UI\Renderer $ui_re;
    /**
     * ilMMTopItemFormGUI constructor.
     * @param ilCtrl   $ctrl
     * @param Factory  $ui_fa
     * @param Renderer $ui_re
     */
    public const F_ACTIVE = 'active';
    public const F_TITLE = 'title';
    public const F_TYPE = 'type';
    public const F_ROLE_BASED_VISIBILITY = "role_based_visibility";

    public function __construct(
        ilCtrl $ctrl,
        Factory $ui_fa,
        Renderer $ui_re,
        ilLanguage $lng,
        Services $http,
        ilMMItemFacadeInterface $item,
        ilMMItemRepository $repository
    ) {
        $this->repository = $repository;
        $this->http = $http;
        $this->ctrl = $ctrl;
        $this->ui_fa = $ui_fa;
        $this->ui_re = $ui_re;
        $this->lng = $lng;
        $this->item_facade = $item;
        $this->access = new ilObjMainMenuAccess();
        if (!$this->item_facade->isEmpty()) {
            $this->ctrl->saveParameterByClass(ilMMTopItemGUI::class, ilMMAbstractItemGUI::IDENTIFIER);
        }

        $this->initForm();
    }

    private function initForm(): void
    {
        $txt = function ($key) {
            return $this->lng->txt($key);
        };
        $f = function (): InputFactory {
            return $this->ui_fa->input();
        };

        // TITLE
        $title = $f()->field()->text($txt('topitem_title_default'), $txt('topitem_title_default_byline'))
                     ->withRequired(true);
        if (!$this->item_facade->isEmpty()) {
            $title = $title->withValue($this->item_facade->getDefaultTitle());
        }

        $items[self::F_TITLE] = $title;

        if ($this->item_facade->supportsCustomIcon()) {
            // ICON
            $icon = $f()->field()->file(new ilMMUploadHandlerGUI(), $txt('topitem_icon'))
                        ->withByline($txt('topitem_icon_byline'))
                        ->withAcceptedMimeTypes([MimeType::IMAGE__SVG_XML]);
            if ($this->item_facade->getIconID() !== null) {
                $icon = $icon->withValue([$this->item_facade->getIconID()]);
            }

            $items[self::F_ICON] = $icon;
        }

        // TYPE
        if (($this->item_facade->isEmpty() || $this->item_facade->isCustom())) {
            $type_groups = $this->getTypeGroups($f, $this->item_facade->isEmpty());
            $type = $f()->field()->switchableGroup(
                $type_groups,
                $txt('topitem_type'),
                $txt('topitem_type_byline')
            )->withRequired(true);
            if (!$this->item_facade->isEmpty()) {
                $string = $this->item_facade->getType() === '' ? TopParentItem::class : $this->item_facade->getType();
                $type = $type->withValue($this->hash($string));
            } else {
                $type = $type->withValue($this->hash(TopParentItem::class));
            }
            $items[self::F_TYPE] = $type;
        }

        // ACTIVE
        $active = $f()->field()->checkbox($txt('topitem_active'), $txt('topitem_active_byline'));
        $active = $active->withValue($this->item_facade->isActivated());
        $items[self::F_ACTIVE] = $active;

        // ROLE BASED VISIBILITY
        if ($this->item_facade->supportsRoleBasedVisibility()) {
            $value_role_based_visibility = null;
            $global_roles = $this->access->getGlobalRoles();
            $global_role_ids = $this->item_facade->getGlobalRoleIDs();
            if ($this->item_facade->hasRoleBasedVisibility() && !empty($global_role_ids)) {
                // remove deleted roles, see https://mantis.ilias.de/view.php?id=34936
                $value_role_based_visibility[0] = array_intersect(
                    $global_role_ids,
                    array_keys($global_roles)
                );
            }
            $role_based_visibility = $f()->field()->optionalGroup(
                [
                    $f()->field()->multiSelect(
                        $txt('sub_global_roles'),
                        $global_roles
                    )->withRequired(false)
                ],
                $txt('sub_role_based_visibility'),
                $txt('sub_role_based_visibility_byline')
            )->withValue($value_role_based_visibility);
            $items[self::F_ROLE_BASED_VISIBILITY] = $role_based_visibility;
        }

        // RETURN FORM
        if ($this->item_facade->isEmpty()) {
            $section = $f()->field()->section($items, $txt(ilMMTopItemGUI::CMD_ADD), "");
            $this->form = $f()->container()->form()->standard($this->ctrl->getLinkTargetByClass(
                ilMMTopItemGUI::class,
                ilMMTopItemGUI::CMD_CREATE
            ), [$section]);
        } else {
            $section = $f()->field()->section($items, $txt(ilMMTopItemGUI::CMD_EDIT), "");
            $this->form = $f()->container()->form()->standard($this->ctrl->getLinkTargetByClass(
                ilMMTopItemGUI::class,
                ilMMTopItemGUI::CMD_UPDATE
            ), [$section]);
        }
    }

    public function save(): bool
    {
        $this->form = $this->form->withRequest($this->http->request());
        $data = $this->form->getData();
        if (is_null($data)) {
            return false;
        }

        $this->item_facade->setAction((string) ($data[0]['action'] ?? ''));
        $this->item_facade->setDefaultTitle((string) $data[0][self::F_TITLE]);
        $this->item_facade->setActiveStatus((bool) $data[0][self::F_ACTIVE]);
        if ($this->item_facade->supportsRoleBasedVisibility()) {
            $this->item_facade->setRoleBasedVisibility((bool) $data[0][self::F_ROLE_BASED_VISIBILITY]);
            if ($data[0][self::F_ROLE_BASED_VISIBILITY] and !empty($data[0][self::F_ROLE_BASED_VISIBILITY])) {
                $this->item_facade->setGlobalRoleIDs((array) $data[0][self::F_ROLE_BASED_VISIBILITY][0]);
            }
        }

        $this->item_facade->setIsTopItm(true);

        if ($this->item_facade->isEmpty()) {
            $type = $this->unhash((string) ($data[0][self::F_TYPE][0]));
            $this->item_facade->setType($type);
            $this->repository->createItem($this->item_facade);
        }

        if ($this->item_facade->supportsCustomIcon()) {
            $icon = (string) ($data[0][self::F_ICON][0] ?? '');
            $this->item_facade->setIconID($icon);
        }

        if ($this->item_facade->isCustom()) {
            $type = $this->item_facade->getType();
            $type_specific_data = (array) $data[0][self::F_TYPE][1];
            $type_handler = $this->repository->getTypeHandlerForType($type);
            $type_handler->saveFormFields($this->item_facade->identification(), $type_specific_data);
        }

        $this->repository->updateItem($this->item_facade);

        return true;
    }

    /**
     * @return string
     */
    public function getHTML(): string
    {
        return $this->ui_re->render([$this->form]);
    }

    /**
     * @param Closure $f
     * @return array
     */
    private function getTypeGroups(Closure $f, bool $new): array
    {
        $type_groups = [];
        $type_informations = $this->repository->getPossibleTopItemTypesWithInformation($new);
        foreach ($type_informations as $classname => $information) {
            if ($this->item_facade->isEmpty()
                || (!$this->item_facade->isEmpty() && $classname === $this->item_facade->getType() && $this->item_facade->isCustom())
            ) { // https://mantis.ilias.de/view.php?id=24152
                $inputs = $this->repository->getTypeHandlerForType($classname)->getAdditionalFieldsForSubForm($this->item_facade->identification());
                $type_groups[$this->hash($classname)] = $f()->field()->group(
                    $inputs,
                    $information->getTypeNameForPresentation()
                );
            }
        }

        return $type_groups;
    }
}
