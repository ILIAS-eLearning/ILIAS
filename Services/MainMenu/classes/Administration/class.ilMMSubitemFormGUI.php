<?php

use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\Hasher;
use ILIAS\UI\Component\Input\Container\Form\Standard;
use ILIAS\UI\Component\Input\Factory as InputFactory;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;

/**
 * Class ilMMSubitemFormGUI
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMSubitemFormGUI
{
    use Hasher;
    const F_TITLE = "title";
    const F_TYPE = "type";
    const F_PARENT = "parent";
    const F_ACTIVE = "active";
    /**
     * @var ilMMItemRepository
     */
    private $repository;
    /**
     * @var Standard
     */
    private $form;
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
     * @var ilMMItemFacadeInterface
     */
    private $item_facade;


    /**
     * ilMMSubitemFormGUI constructor.
     *
     * @param ilCtrl                  $ctrl
     * @param Factory                 $ui_fa
     * @param Renderer                $ui_re
     * @param ilLanguage              $lng
     * @param ilMMItemFacadeInterface $item
     * @param ilMMItemRepository      $repository
     */
    public function __construct(ilCtrl $ctrl, Factory $ui_fa, Renderer $ui_re, ilLanguage $lng, ilMMItemFacadeInterface $item, ilMMItemRepository $repository)
    {
        $this->ctrl = $ctrl;
        $this->ui_fa = $ui_fa;
        $this->ui_re = $ui_re;
        $this->lng = $lng;
        $this->item_facade = $item;
        $this->repository = $repository;
        if (!$this->item_facade->isEmpty()) {
            $this->ctrl->saveParameterByClass(ilMMSubItemGUI::class, ilMMSubItemGUI::IDENTIFIER);
        }

        $this->initForm();
    }


    private function initForm()
    {
        // TITLE
        $txt = function ($id) : string {
            return $this->lng->txt($id);
        };
        $f = function () : InputFactory {
            return $this->ui_fa->input();
        };

        $title = $f()->field()->text($txt('sub_title_default'), $txt('sub_title_default_byline'));
        if (!$this->item_facade->isEmpty()) {
            $title = $title->withValue($this->item_facade->getDefaultTitle());
        }
        $items[self::F_TITLE] = $title;

        // TYPE
        $type = $f()->field()->radio($txt('sub_type'), $txt('sub_type_byline'))->withRequired(true);
        $type_informations = $this->repository->getPossibleSubItemTypesWithInformation();

        foreach ($type_informations as $classname => $information) {
            if ($this->item_facade->isEmpty()
                || (!$this->item_facade->isEmpty() && $classname === $this->item_facade->getType() && $this->item_facade->isCustom())
            ) { // https://mantis.ilias.de/view.php?id=24152
                $inputs = $this->repository->getTypeHandlerForType($classname)->getAdditionalFieldsForSubForm($this->item_facade->identification());
                $type = $type->withOption($this->hash($classname), $information->getTypeNameForPresentation(), $information->getTypeBylineForPresentation(), $inputs);
            }
        }

        if (!$this->item_facade->isEmpty() && $this->item_facade->isCustom()) {
            $type = $type->withValue($this->hash($this->item_facade->getType()));
        } elseif ($this->item_facade->isCustom()) {
            $type = $type->withValue($this->hash(reset(array_keys($type_informations))));
        }

        if ($this->item_facade->isEmpty() || $this->item_facade->isCustom()) {
            $items[self::F_TYPE] = $type;
        }

        // PARENT
        $parent = $f()->field()->select($txt('sub_parent'), $this->repository->getPossibleParentsForFormAndTable())
            ->withRequired(true);
        if (!$this->item_facade->isEmpty() && !$this->item_facade->isInLostItem()) {
            $parent = $parent->withValue($this->item_facade->getParentIdentificationString());
        } else {
            $parent = $parent->withValue(reset(array_keys($this->repository->getPossibleParentsForFormAndTable())));
        }
        $items[self::F_PARENT] = $parent;

        // ACTIVE
        $active = $f()->field()->checkbox($txt('sub_active'), $txt('sub_active_byline'));
        if (!$this->item_facade->isEmpty()) {
            $active = $active->withValue($this->item_facade->isAvailable());
        }
        $items[self::F_ACTIVE] = $active;

        // RETURN FORM
        if ($this->item_facade->isEmpty()) {
            $section = $f()->field()->section($items, $txt(ilMMSubItemGUI::CMD_ADD), "");
            $this->form = $f()->container()->form()
                ->standard($this->ctrl->getLinkTargetByClass(ilMMSubItemGUI::class, ilMMSubItemGUI::CMD_CREATE), [$section]);
        } else {
            $section = $f()->field()->section($items, $txt(ilMMSubItemGUI::CMD_EDIT), "");
            $this->form = $f()->container()->form()
                ->standard($this->ctrl->getLinkTargetByClass(ilMMSubItemGUI::class, ilMMSubItemGUI::CMD_UPDATE), [$section]);
        }
    }


    public function save() : bool
    {
        global $DIC;
        $r = new ilMMItemRepository();
        $this->form = $this->form->withRequest($DIC->http()->request());
        $data = $this->form->getData();

        if (is_null($data)) {
            return false;
        }

        $this->item_facade->setAction((string) $data[0]['action']);
        $this->item_facade->setDefaultTitle((string) $data[0][self::F_TITLE]);
        $this->item_facade->setActiveStatus((bool) $data[0][self::F_ACTIVE]);
        if ((string) $data[0][self::F_PARENT]) {
            $this->item_facade->setParent((string) $data[0][self::F_PARENT]);
        }
        $this->item_facade->setIsTopItm(false);

        if ($this->item_facade->isEmpty()) {
            $type = $this->unhash((string) ($data[0][self::F_TYPE]['value']));
            $this->item_facade->setType($type);
            $r->createItem($this->item_facade);
        }
        if ($this->item_facade->isCustom()) {
            $type = $this->item_facade->getType();
            $type_specific_data = (array) $data[0][self::F_TYPE]['group_values'];
            $type_handler = $this->repository->getTypeHandlerForType($type);
            $type_handler->saveFormFields($this->item_facade->identification(), $type_specific_data);
        }

        $r->updateItem($this->item_facade);

        return true;
    }


    public function getHTML()
    {
        return $this->ui_re->render([$this->form]);
    }
}
