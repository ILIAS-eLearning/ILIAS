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

namespace ILIAS\MetaData\Editor\Full\Services\Tables;

use ILIAS\UI\Renderer;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\MetaData\Editor\Presenter\PresenterInterface as PresenterInterface;
use ILIAS\MetaData\Editor\Full\Services\DataFinder;
use ILIAS\MetaData\Elements\ElementInterface;
use ILIAS\MetaData\Paths\FactoryInterface as PathFactory;
use ILIAS\MetaData\Editor\Full\Services\Actions\FlexibleModal;
use ILIAS\MetaData\Editor\Full\Services\Actions\FlexibleSignal;
use ILIAS\MetaData\Elements\Data\Type;
use ILIAS\MetaData\Editor\Full\Services\Actions\ButtonFactory;

class TableBuilder
{
    protected UIFactory $ui_factory;
    protected Renderer $renderer;
    protected PresenterInterface $presenter;
    protected DataFinder $data_finder;
    protected ButtonFactory $button_factory;

    protected ElementInterface $template_element;
    protected array $data;

    public function __construct(
        UIFactory $ui_factory,
        Renderer $renderer,
        PresenterInterface $presenter,
        DataFinder $data_finder,
        ButtonFactory $button_factory
    ) {
        $this->ui_factory = $ui_factory;
        $this->renderer = $renderer;
        $this->presenter = $presenter;
        $this->data_finder = $data_finder;
        $this->button_factory = $button_factory;
    }

    public function get(): Table
    {
        $table = $this->init();
        $table->setData($this->data);
        return $table;
    }

    public function withAdditionalRow(
        ElementInterface $element,
        FlexibleSignal $update_signal,
        ?FlexibleSignal $delete_signal
    ): TableBuilder {
        if (!isset($this->template_element)) {
            $this->template_element = $element;
        }

        $res = [];

        foreach ($this->data_finder->getDataCarryingElements(
            $element,
            true
        ) as $data_el) {
            $data = '';
            if ($data_el->getData()->type() !== Type::NULL) {
                $data = $this->presenter->data()->dataValue($data_el->getData());
            }
            $res[] = $data;
        }

        $action_buttons = [];
        $action_buttons[] = $this->button_factory->update($update_signal);
        if ($delete_signal) {
            $action_buttons[] = $this->button_factory->delete(
                $delete_signal,
                true
            );
        }
        $dropdown = $this->ui_factory->dropdown()->standard($action_buttons);
        $res['dropdown'] = $this->renderer->render($dropdown);

        $clone = clone $this;
        $clone->data[] = $res;
        return $clone;
    }

    protected function init(): Table
    {
        if (!isset($this->template_element)) {
            throw new \ilMDEditorException('Table cannot be empty.');
        }
        $table = new Table();
        $table->setRowTemplate(
            'tpl.full_editor_row.html',
            'Services/MetaData'
        );
        $table->setTitle($this->presenter->elements()->nameWithParents(
            $this->template_element,
            null,
            true
        ));
        $table->setExternalSegmentation(true);

        foreach ($this->data_finder->getDataCarryingElements(
            $this->template_element,
            true
        ) as $data_el) {
            $table->addColumn($this->presenter->elements()->nameWithParents(
                $data_el,
                $this->template_element,
                false
            ));
        }
        $table->addColumn('');
        return $table;
    }
}
