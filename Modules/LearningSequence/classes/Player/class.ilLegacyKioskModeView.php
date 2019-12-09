<?php

declare(strict_types=1);

use ILIAS\KioskMode\ControlBuilder;
use ILIAS\KioskMode\State;
use ILIAS\KioskMode\URLBuilder;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Factory;

/**
 * Class ilLegacyKioskModeView
 */
class ilLegacyKioskModeView implements ILIAS\KioskMode\View
{
    const GET_VIEW_CMD_FROM_LIST_GUI_FOR = ['sahs'];

    protected $object;

    public function __construct(
        ilObject $object,
        ilLanguage $lng,
        ilAccess $access
    ) {
        $this->object = $object;
        $this->lng = $lng;
        $this->access = $access;
    }

    protected function getObjectTitle() : string
    {
        return $this->object->getTitle();
    }

    protected function getType() : string
    {
        return $this->object->getType();
    }

    /**
     * @inheritDoc
     */
    protected function setObject(\ilObject $object)
    {
        $this->object = $object;
    }

    /**
     * @inheritDoc
     */
    protected function hasPermissionToAccessKioskMode() : bool
    {
        return true;
        //return $this->access->checkAccess('read', '', $this->contentPageObject->getRefId());
    }

    /**
     * @inheritDoc
     */
    public function buildInitialState(State $empty_state) : State
    {
        return $empty_state;
    }

    /**
     * @inheritDoc
     */
    public function buildControls(State $state, ControlBuilder $builder) : ControlBuilder
    {
        if (!$builder instanceof LSControlBuilder) {
            throw new LogicException("The Legacy Mode in the Learning Sequence requires an LSControlBuilder explicitely.", 1);
        }

        $label = $this->lng->txt('lso_start_item') . ' ' . $this->getTitleByType($this->getType());

        $ref_id = $this->object->getRefId();
        $type = $this->object->getType();

        $url = \ilLink::_getStaticLink(
            $ref_id,
            $type,
            true,
            false
        );

        if (in_array($type, self::GET_VIEW_CMD_FROM_LIST_GUI_FOR)) {
            $obj_id = $this->object->getId();
            $item_list_gui = \ilObjectListGUIFactory::_getListGUIByType($type);
            $item_list_gui->initItem($ref_id, $obj_id);
            $view_link = $item_list_gui->getCommandLink('view');
            $view_link = str_replace('&amp;', '&', $view_link);
            $view_link = ILIAS_HTTP_PATH . '/' . $view_link;
            $url = $view_link;
        }

        $builder->start($label, $url, 0);
        //return $this->debugBuildAllControls($builder);
        return $builder;
    }

    /**
     * @inheritDoc
     */
    public function updateGet(State $state, string $command, int $param = null) : State
    {
        return $state;
    }

    /**
     * @inheritDoc
     */
    public function updatePost(State $state, string $command, array $post) : State
    {
        return $state;
    }

    /**
     * @inheritDoc
     */
    public function render(
        State $state,
        Factory $factory,
        URLBuilder $url_builder,
        array $post = null
    ) : Component {
        $obj_type = $this->object->getType();
        $obj_type_txt = $this->lng->txt('obj_' . $obj_type);
        $icon = $factory->icon()->standard($obj_type, $obj_type_txt, 'large');
        $md = $this->getMetadata((int) $this->object->getId(), $obj_type);
        $props = array_merge(
            [$this->lng->txt('obj_type') => $obj_type_txt],
            $this->getMetadata((int) $this->object->getId(), $obj_type)
        );

        $info =  $factory->item()->standard($this->object->getTitle())
            ->withLeadIcon($icon)
            ->withDescription($this->object->getDescription())
            ->withProperties($props);

        return $info;
    }

    //TODO: enhance metadata
    private function getMetadata(int $obj_id, string $type) : array
    {
        $md = new ilMD($obj_id, 0, $type);
        $meta_data = [];

        $section = $md->getGeneral();
        if (!$section) {
            return [];
        }

        $meta_data['language'] = [];
        foreach ($section->getLanguageIds() as $id) {
            $meta_data['language'][] = $section->getLanguage($id)->getLanguageCode();
        }
        $meta_data['keywords'] = [];
        foreach ($section->getKeywordIds() as $id) {
            $meta_data['keywords'][] = $section->getKeyword($id)->getKeyword();
        }

        $md_flat = [];
        foreach ($meta_data as $md_label => $values) {
            if (count($values) > 0) {
                $md_flat[$this->lng->txt($md_label)] = implode(', ', $values);
            }
        }
        return $md_flat;
    }

    private function debugBuildAllControls(ControlBuilder $builder) : ControlBuilder
    {
        $builder

        ->tableOfContent($this->getObjectTitle(), 'kommando', 666)
            ->node('node1')
                ->item('item1.1', 1)
                ->item('item1.2', 11)
                ->end()
            ->item('item2', 111)
            ->node('node3', 1111)
                ->item('item3.1', 2)
                ->node('node3.2')
                    ->item('item3.2.1', 122)
                ->end()
            ->end()
            ->end()

        ->locator('locator_cmd')
            ->item('item 1', 1)
            ->item('item 2', 2)
            ->item('item 3', 3)
            ->end()

        ->done('cmd', 1)
        ->next('cmd', 1)
        ->previous('', 1)
        //->exit('cmd', 1)
        ->generic('cmd 1', 'x', 1)
        ->generic('cmd 2', 'x', 2)
        //->toggle('toggle', 'cmd_on', 'cmd_off')
        ->mode('modecmd', ['m1', 'm2', 'm3'])
        ;

        return $builder;
    }

    private function getTitleByType(string $type) : string
    {
        return $this->lng->txt("obj_" . $type);
    }
}
