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
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\Link;
use ILIAS\UI\Component\Input\Container\Form\Standard;
use ILIAS\UI\Component\Input\Factory as InputFactory;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use ILIAS\FileUpload\MimeType;
use ILIAS\GlobalScreen\GUI\I18n\Translator;
use Psr\Http\Message\RequestInterface;

/**
 * Class ilMMSubitemFormGUI
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ilMMSubitemFormGUI
{
    use Hasher;

    public const F_TITLE = "title";
    public const F_TYPE = "type";
    public const F_PARENT = "parent";
    public const F_ACTIVE = "active";
    public const F_ICON = "icon";
    public const F_ROLE_BASED_VISIBILITY = "role_based_visibility";

    private Standard $form;

    /**
     * ilMMSubitemFormGUI constructor.
     * @param ilCtrl                  $ctrl
     * @param Factory                 $ui_fa
     * @param Renderer                $ui_re
     * @param ilLanguage              $lng
     * @param ilMMItemFacadeInterface $item_facade
     * @param ilMMItemRepository      $repository
     */
    public function __construct(
        protected ilCtrl $ctrl,
        protected ILIAS\UI\Factory $ui_fa,
        protected Renderer $ui_re,
        protected Translator $lng,
        private RequestInterface $request,
        private ilMMItemFacadeInterface $item_facade,
        private ilMMItemRepository $repository,
        private ?ilMMItemFacadeInterface $parent_item = null
    ) {
        $this->initForm();
    }

    private function initForm(): void
    {
        // TITLE
        $txt = (fn($id): string => $this->lng->txt($id));
        $f = (fn(): InputFactory => $this->ui_fa->input());

        $title = $f()->field()->text($txt('sub_title_default'), $txt('sub_title_default_byline'));
        if (!$this->item_facade->isEmpty()) {
            $title = $title->withValue($this->item_facade->getDefaultTitle());
        }
        $items[self::F_TITLE] = $title;

        // TYPE
        if (($this->item_facade->isEmpty() || $this->item_facade->isCustom())) {
            $type_groups = $this->getTypeGroups($f);
            $type = $f()->field()->switchableGroup(
                $type_groups,
                $txt('sub_type'),
                $txt('sub_type_byline')
            )->withRequired(true);
            if (!$this->item_facade->isEmpty()) {
                $string = $this->item_facade->getType() === '' ? Link::class : $this->item_facade->getType();
                $type = $type->withValue($this->hash($string));
            } else {
                $type = $type->withValue($this->hash(Link::class));
            }
            $items[self::F_TYPE] = $type;
        }

        // ICON
        if ($this->item_facade->supportsCustomIcon()) {
            // ICON
            $icon = $f()->field()->file(new ilMMUploadHandlerGUI(), $txt('sub_icon'))
                        ->withByline($txt('sub_icon_byline'))
                        ->withAcceptedMimeTypes([MimeType::IMAGE__SVG_XML]);
            if ($this->item_facade->getIconID() !== null) {
                $icon = $icon->withValue([$this->item_facade->getIconID()]);
            }

            $items[self::F_ICON] = $icon;
        }

        // PARENT
        $possible_parents = array_keys($this->repository->getPossibleParentsForFormAndTable());
        if (!$this->item_facade->isEmpty()
            && !$this->item_facade->isInLostItem()
            && in_array($this->item_facade->getParentIdentificationString(), $possible_parents, true)) {

            $parent = $f()->field()
                          ->select(
                              $txt('sub_parent'),
                              $this->repository->getPossibleParentsForFormAndTable()
                          )
                          ->withRequired(true)
                          ->withValue($this->item_facade->getParentIdentificationString());
        } else {
            $parent = $f()->field()
                          ->hidden()
                          ->withRequired(true)
                          ->withValue(
                              $this->parent_item?->identification()->serialize() ?? reset($possible_parents)
                          );
        }
        $items[self::F_PARENT] = $parent;

        // ACTIVE
        $active = $f()->field()->checkbox($txt('sub_active'), $txt('sub_active_byline'));
        $active = $active->withValue($this->item_facade->isActivated());
        $items[self::F_ACTIVE] = $active;

        // ROLE BASED VISIBILITY
        if ($this->item_facade->supportsRoleBasedVisibility()) {
            $access = new ilObjMainMenuAccess();
            $value_role_based_visibility = null;
            if ($this->item_facade->hasRoleBasedVisibility() && !empty($this->item_facade->getGlobalRoleIDs())) {
                // remove deleted roles, see https://mantis.ilias.de/view.php?id=34936
                $value_role_based_visibility[0] = array_intersect(
                    $this->item_facade->getGlobalRoleIDs(),
                    array_keys($access->getGlobalRoles())
                );
            }
            $role_based_visibility = $f()->field()->optionalGroup(
                [
                    $f()->field()->multiSelect(
                        $txt('sub_global_roles'),
                        $access->getGlobalRoles()
                    )->withRequired(false)
                ],
                $txt('sub_role_based_visibility'),
                $txt('sub_role_based_visibility_byline')
            )->withValue($value_role_based_visibility);
            $items[self::F_ROLE_BASED_VISIBILITY] = $role_based_visibility;
        }

        // RETURN FORM
        if ($this->item_facade->isEmpty()) {
            $this->form = $f()->container()->form()->standard(
                $this->ctrl->getLinkTargetByClass(
                    ilMMSubItemGUI::class,
                    ilMMSubItemGUI::CMD_CREATE
                ),
                $items
            );
        } else {
            $this->form = $f()->container()->form()->standard(
                $this->ctrl->getLinkTargetByClass(
                    ilMMSubItemGUI::class,
                    ilMMSubItemGUI::CMD_UPDATE
                ),
                $items
            );
        }
    }

    public function save(): bool
    {
        $r = new ilMMItemRepository();
        $this->form = $this->form->withRequest($this->request);
        $data = $this->form->getData();

        if (is_null($data)) {
            return false;
        }

        $role_based_visibility = $data[self::F_ROLE_BASED_VISIBILITY] ?? false;
        $this->item_facade->setDefaultTitle((string) $data[self::F_TITLE]);
        $this->item_facade->setActiveStatus((bool) $data[self::F_ACTIVE]);
        $this->item_facade->setRoleBasedVisibility((bool) $role_based_visibility);

        if ($role_based_visibility) {
            $this->item_facade->setGlobalRoleIDs((array) $role_based_visibility[0]);
        }
        if ((string) $data[self::F_PARENT] !== '' && (string) $data[self::F_PARENT] !== '0') {
            $this->item_facade->setParent((string) $data[self::F_PARENT]);
        }
        $this->item_facade->setIsTopItm(false);

        if ($this->item_facade->isEmpty()) {
            $type = $this->unhash((string) ($data[self::F_TYPE][0]));
            $this->item_facade->setType($type);
            $r->createItem($this->item_facade);
        }

        if ($this->item_facade->supportsCustomIcon()) {
            $icon = (string) ($data[self::F_ICON][0] ?? '');
            $this->item_facade->setIconID($icon);
        }

        if ($this->item_facade->isCustom()) {
            $type = $this->item_facade->getType();
            $type_specific_data = (array) $data[self::F_TYPE][1];
            $type_handler = $this->repository->getTypeHandlerForType($type);
            $type_handler->saveFormFields($this->item_facade->identification(), $type_specific_data);
        }

        $r->updateItem($this->item_facade);

        return true;
    }

    /**
     * @deprecated use get() instead
     */
    public function getHTML(): string
    {
        return $this->ui_re->render([$this->form]);
    }

    public function get(): Standard
    {
        return $this->form;
    }

    /**
     * @param Closure $f
     * @return array
     */
    private function getTypeGroups(Closure $f): array
    {
        $type_groups = [];
        $type_informations = $this->repository->getPossibleSubItemTypesWithInformation();
        foreach ($type_informations as $classname => $information) {
            if ($this->item_facade->isEmpty()
                || (!$this->item_facade->isEmpty() && $classname === $this->item_facade->getType(
                ) && $this->item_facade->isCustom())
            ) { // https://mantis.ilias.de/view.php?id=24152
                $inputs = $this->repository->getTypeHandlerForType($classname)->getAdditionalFieldsForSubForm(
                    $this->item_facade->identification()
                );
                $type_groups[$this->hash($classname)] = $f()->field()->group(
                    $inputs,
                    $information->getTypeNameForPresentation()
                );
            }
        }

        return $type_groups;
    }
}
