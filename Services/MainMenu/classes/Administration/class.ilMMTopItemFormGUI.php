<?php

use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\Hasher;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\TopItem\TopParentItem;
use ILIAS\UI\Component\Input\Container\Form\Standard;
use ILIAS\UI\Component\Input\Factory as InputFactory;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;

/**
 * Class ilMMTopItemFormGUI
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMTopItemFormGUI
{
    use Hasher;

    private const F_ICON = 'icon';
    /**
     * @var \ILIAS\DI\HTTPServices
     */
    private $http;
    /**
     * @var ilMMItemRepository
     */
    private $repository;
    /**
     * @var Standard
     */
    private $form;
    /**
     * @var ilMMItemFacadeInterface
     */
    private $item_facade;
    /**
     * @var ilLanguage
     */
    protected $lng;
    /**
     * @var ilCtrl
     */
    protected $ctrl;
    /**
     * @var ILIAS\UI\Factory
     */
    protected $ui_fa;
    /**
     * @var ILIAS\UI\Renderer
     */
    protected $ui_re;
    /**
     * ilMMTopItemFormGUI constructor.
     * @param ilCtrl   $ctrl
     * @param Factory  $ui_fa
     * @param Renderer $ui_re
     */
    const F_ACTIVE = 'active';
    const F_TITLE = 'title';
    const F_TYPE = 'type';
    const F_ROLE_BASED_VISIBILITY = "role_based_visibility";

    public function __construct(
        ilCtrl $ctrl,
        Factory $ui_fa,
        Renderer $ui_re,
        ilLanguage $lng,
        \ILIAS\DI\HTTPServices $http,
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
        if (!$this->item_facade->isEmpty()) {
            $this->ctrl->saveParameterByClass(ilMMTopItemGUI::class, ilMMTopItemGUI::IDENTIFIER);
        }

        $this->initForm();
    }

    private function initForm()
    {
        $txt = function ($key) {
            return $this->lng->txt($key);
        };
        $f = function () : InputFactory {
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
                        ->withAcceptedMimeTypes([ilMimeTypeUtil::IMAGE__SVG_XML]);
            if ($this->item_facade->getIconID() !== null) {
                $icon = $icon->withValue([$this->item_facade->getIconID()]);
            }

            $items[self::F_ICON] = $icon;
        }

        // TYPE
        if (($this->item_facade->isEmpty() || $this->item_facade->isCustom())) {
            $type_groups = $this->getTypeGroups($f);
            $type = $f()->field()->switchableGroup($type_groups, $txt('topitem_type'),
                $txt('topitem_type_byline'))->withRequired(true);
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

        // RETURN FORM
        if ($this->item_facade->isEmpty()) {
            $section = $f()->field()->section($items, $txt(ilMMTopItemGUI::CMD_ADD), "");
            $this->form = $f()->container()->form()->standard($this->ctrl->getLinkTargetByClass(ilMMTopItemGUI::class,
                ilMMTopItemGUI::CMD_CREATE), [$section]);
        } else {
            $section = $f()->field()->section($items, $txt(ilMMTopItemGUI::CMD_EDIT), "");
            $this->form = $f()->container()->form()->standard($this->ctrl->getLinkTargetByClass(ilMMTopItemGUI::class,
                ilMMTopItemGUI::CMD_UPDATE), [$section]);
        }
    }

    public function save()
    {
        $this->form = $this->form->withRequest($this->http->request());
        $data = $this->form->getData();
        if (is_null($data)) {
            return false;
        }

        $this->item_facade->setAction((string) $data[0]['action']);
        $this->item_facade->setDefaultTitle((string) $data[0][self::F_TITLE]);
        $this->item_facade->setActiveStatus((bool) $data[0][self::F_ACTIVE]);
        $this->item_facade->setIsTopItm(true);

        if ($this->item_facade->isEmpty()) {
            $type = $this->unhash((string) ($data[0][self::F_TYPE][0]));
            $this->item_facade->setType($type);
            $this->repository->createItem($this->item_facade);
        }

        if ($this->item_facade->supportsCustomIcon()) {
            $icon = (string) $data[0][self::F_ICON][0];
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
    public function getHTML() : string
    {
        return $this->ui_re->render([$this->form]);
    }

    /**
     * @param Closure $f
     * @return array
     */
    private function getTypeGroups(Closure $f) : array
    {
        $type_groups = [];
        $type_informations = $this->repository->getPossibleTopItemTypesWithInformation();
        foreach ($type_informations as $classname => $information) {
            if ($this->item_facade->isEmpty()
                || (!$this->item_facade->isEmpty() && $classname === $this->item_facade->getType() && $this->item_facade->isCustom())
            ) { // https://mantis.ilias.de/view.php?id=24152
                $inputs = $this->repository->getTypeHandlerForType($classname)->getAdditionalFieldsForSubForm($this->item_facade->identification());
                $type_groups[$this->hash($classname)] = $f()->field()->group($inputs,
                    $information->getTypeNameForPresentation());
            }
        }

        return $type_groups;
    }
}
