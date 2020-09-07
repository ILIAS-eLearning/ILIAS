<?php

use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\Hasher;
use ILIAS\UI\Component\Input\Container\Form\Standard;
use ILIAS\UI\Component\Input\Factory as InputFactory;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;

/**
 * Class ilMMTopItemFormGUI
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMTopItemFormGUI
{
    use Hasher;
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
     *
     * @param ilCtrl   $ctrl
     * @param Factory  $ui_fa
     * @param Renderer $ui_re
     */
    const F_ACTIVE = 'active';
    const F_TITLE = 'title';
    const F_TYPE = 'type';


    public function __construct(ilCtrl $ctrl, Factory $ui_fa, Renderer $ui_re, ilLanguage $lng, \ILIAS\DI\HTTPServices $http, ilMMItemFacadeInterface $item, ilMMItemRepository $repository)
    {
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

        // TYPE
        $type = $f()->field()->radio($txt('topitem_type'), $txt('topitem_type_byline'))->withRequired(true);
        $type_informations = $this->repository->getPossibleTopItemTypesWithInformation();

        $type_i = 0;
        foreach ($type_informations as $classname => $information) {
            if ($this->item_facade->isEmpty()
                || (!$this->item_facade->isEmpty() && $classname === $this->item_facade->getType() && $this->item_facade->isCustom())
            ) { // https://mantis.ilias.de/view.php?id=24152
                $inputs = $this->repository->getTypeHandlerForType($classname)->getAdditionalFieldsForSubForm($this->item_facade->identification());
                $type = $type->withOption($this->hash($classname), $information->getTypeNameForPresentation(), $information->getTypeBylineForPresentation(), $inputs);
                $type_i++;
            }
        }

        if (!$this->item_facade->isEmpty() && $this->item_facade->isCustom()) {
            $type = $type->withValue($this->hash($this->item_facade->getType()));
        } elseif ($this->item_facade->isCustom()) {
            $type = $type->withValue($this->hash(reset(array_keys($type_informations))));
        }

        if (($this->item_facade->isEmpty() || $this->item_facade->isCustom()) && $type_i > 0) {
            $items[self::F_TYPE] = $type;
        }

        // ACTIVE
        $active = $f()->field()->checkbox($txt('topitem_active'), $txt('topitem_active_byline'));
        if (!$this->item_facade->isEmpty()) {
            $active = $active->withValue($this->item_facade->isAvailable());
        }
        $items[self::F_ACTIVE] = $active;

        // RETURN FORM
        if ($this->item_facade->isEmpty()) {
            $section = $f()->field()->section($items, $txt(ilMMTopItemGUI::CMD_ADD), "");
            $this->form = $f()->container()->form()->standard($this->ctrl->getLinkTargetByClass(ilMMTopItemGUI::class, ilMMTopItemGUI::CMD_CREATE), [$section]);
        } else {
            $section = $f()->field()->section($items, $txt(ilMMTopItemGUI::CMD_EDIT), "");
            $this->form = $f()->container()->form()->standard($this->ctrl->getLinkTargetByClass(ilMMTopItemGUI::class, ilMMTopItemGUI::CMD_UPDATE), [$section]);
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
            $type = $this->unhash((string) ($data[0][self::F_TYPE]['value']));
            $this->item_facade->setType($type);
            $this->repository->createItem($this->item_facade);
        }

        if ($this->item_facade->isCustom()) {
            $type = $this->item_facade->getType();
            $type_specific_data = (array) $data[0][self::F_TYPE]['group_values'];
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
}
