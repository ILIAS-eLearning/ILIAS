<?php

declare(strict_types=1);

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

use ILIAS\KioskMode\ControlBuilder;
use ILIAS\KioskMode\State;
use ILIAS\KioskMode\URLBuilder;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Factory;

class ilLegacyKioskModeView implements ILIAS\KioskMode\View
{
    public const GET_VIEW_CMD_FROM_LIST_GUI_FOR = ['sahs'];

    protected ilObject $object;
    protected ilLanguage $lng;
    protected ilAccess $access;

    public function __construct(
        ilObject $object,
        ilLanguage $lng,
        ilAccess $access
    ) {
        $this->object = $object;
        $this->lng = $lng;
        $this->access = $access;
    }

    protected function getObjectTitle(): string
    {
        return $this->object->getTitle();
    }

    protected function setObject(\ilObject $object): void
    {
        $this->object = $object;
    }

    protected function hasPermissionToAccessKioskMode(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function buildInitialState(State $empty_state): State
    {
        return $empty_state;
    }

    /**
     * @inheritDoc
     */
    public function buildControls(State $state, ControlBuilder $builder): ControlBuilder
    {
        if (!$builder instanceof LSControlBuilder) {
            throw new LogicException("The Legacy Mode in the Learning Sequence requires an LSControlBuilder explicitely.", 1);
        }

        $ref_id = $this->object->getRefId();
        $type = $this->object->getType();

        $label = sprintf(
            $this->lng->txt('lso_start_item'),
            $this->getTitleByType($type)
        );

        $url = \ilLink::_getStaticLink(
            $ref_id,
            $type
        );

        $obj_id = $this->object->getId();
        if (in_array($type, self::GET_VIEW_CMD_FROM_LIST_GUI_FOR)) {
            $item_list_gui = \ilObjectListGUIFactory::_getListGUIByType($type);
            $item_list_gui->initItem($ref_id, $obj_id, $type);
            $view_link = $item_list_gui->getCommandLink('view');
            $view_link = str_replace('&amp;', '&', $view_link);
            $view_link = ILIAS_HTTP_PATH . '/' . $view_link;
            $url = $view_link;
        }

        $builder->start($label, $url, $obj_id);

        return $builder;
    }

    /**
     * @inheritDoc
     */
    public function updateGet(State $state, string $command, int $parameter = null): State
    {
        return $state;
    }

    /**
     * @inheritDoc
     */
    public function updatePost(State $state, string $command, array $post): State
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
    ): Component {
        $obj_type = $this->object->getType();
        $obj_type_txt = $this->lng->txt('obj_' . $obj_type);
        $icon = $factory->symbol()->icon()->standard($obj_type, $obj_type_txt, 'large');

        $props = array_merge(
            [$this->lng->txt('obj_type') => $obj_type_txt],
            $this->getMetadata($this->object->getId(), $obj_type)
        );

        return $factory->item()->standard($this->object->getTitle())
            ->withLeadIcon($icon)
            ->withDescription($this->object->getDescription())
            ->withProperties($props);
    }

    //TODO: enhance metadata
    /**
     * @return array<string, string>|[]
     */
    private function getMetadata(int $obj_id, string $type): array
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
            if ($values !== []) {
                $md_flat[$this->lng->txt($md_label)] = implode(', ', $values);
            }
        }
        return $md_flat;
    }

    private function getTitleByType(string $type): string
    {
        return $this->lng->txt("obj_" . $type);
    }
}
