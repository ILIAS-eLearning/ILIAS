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

namespace ILIAS\MetaData\Settings\Vocabularies;

use ILIAS\UI\Component\Table\DataRetrieval as BaseDataRetrieval;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\Data\Range;
use ILIAS\Data\Order;
use ILIAS\MetaData\Vocabularies\Manager\Manager as VocabManager;
use ILIAS\MetaData\Vocabularies\VocabularyInterface;
use ILIAS\UI\Factory as UIFactory;

class DataRetrieval implements BaseDataRetrieval
{
    protected const MAX_PREVIEW_VALUES = 5;

    protected VocabManager $vocab_manager;
    protected Presentation $presentation;
    protected UIFactory $ui_factory;

    /**
     * @var VocabularyInterface[]
     */
    protected array $vocabs;

    public function __construct(
        VocabManager $vocab_manager,
        Presentation $presentation,
        UIFactory $ui_factory
    ) {
        $this->vocab_manager = $vocab_manager;
        $this->presentation = $presentation;
        $this->ui_factory = $ui_factory;
    }

    public function getRows(
        DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        ?array $filter_data,
        ?array $additional_parameters
    ): \Generator {
        $checked_icon = $this->ui_factory->symbol()->icon()->custom(
            'assets/images/standard/icon_checked.svg',
            $this->presentation->txt('yes'),
            'small'
        );
        $unchecked_icon = $this->ui_factory->symbol()->icon()->custom(
            'assets/images/standard/icon_unchecked.svg',
            $this->presentation->txt('yes'),
            'small'
        );

        $infos = $this->vocab_manager->infos();
        foreach ($this->getVocabs($range) as $vocab) {
            $record = [];

            $record['element'] = $this->presentation->makeSlotPresentable($vocab->slot());
            $record['type'] = $this->presentation->makeTypePresentable($vocab->type());
            $record['source'] = $vocab->source();
            $record['preview'] = implode(
                ', ',
                $this->presentation->makeValuesPresentable($vocab, self::MAX_PREVIEW_VALUES)
            );
            $record['active'] = $vocab->isActive() ? $checked_icon : $unchecked_icon;
            if ($infos->isCustomInputApplicable($vocab)) {
                $record['custom_input'] = $vocab->allowsCustomInputs() ? $checked_icon : $unchecked_icon;
            }

            yield $row_builder->buildDataRow(
                $vocab->id(),
                $record
            )->withDisabledAction(
                'delete',
                !$infos->canBeDeleted($vocab)
            )->withDisabledAction(
                'activate',
                $vocab->isActive()
            )->withDisabledAction(
                'deactivate',
                !$vocab->isActive() ||
                !$infos->isDeactivatable($vocab)
            )->withDisabledAction(
                'allow_custom_input',
                !$infos->isCustomInputApplicable($vocab) ||
                $vocab->allowsCustomInputs()
            )->withDisabledAction(
                'disallow_custom_input',
                !$infos->isCustomInputApplicable($vocab) ||
                !$infos->canDisallowCustomInput($vocab) ||
                !$vocab->allowsCustomInputs()
            );
        };
    }

    public function getTotalRowCount(?array $filter_data, ?array $additional_parameters): ?int
    {
        return count($this->getVocabs());
    }

    protected function getVocabs(?Range $range = null): array
    {
        if (isset($this->vocabs)) {
            $vocabs = $this->vocabs;
        } else {
            $vocabs = iterator_to_array($this->vocab_manager->getAllVocabularies(), false);
        }

        if ($range) {
            $vocabs = array_slice($vocabs, $range->getStart(), $range->getLength());
        }

        return $this->vocabs = $vocabs;
    }
}
