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

namespace ILIAS\MetaData\Editor\Digest;

use ILIAS\UI\Component\Input\Container\Form\Standard as StandardForm;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Component\Input\Field\Section;
use ILIAS\UI\Component\Modal\Interruptive as InterruptiveModal;
use ILIAS\MetaData\Paths\FactoryInterface as PathFactory;
use ILIAS\MetaData\Editor\Presenter\PresenterInterface;
use ILIAS\MetaData\Elements\SetInterface;
use ILIAS\MetaData\Editor\Http\RequestForFormInterface;
use ILIAS\MetaData\Paths\Navigator\NavigatorFactoryInterface;
use ILIAS\MetaData\Editor\Http\LinkFactory;
use ILIAS\MetaData\Editor\Http\Command;
use ILIAS\UI\Component\Signal;
use ILIAS\MetaData\DataHelper\DataHelperInterface;
use ILIAS\MetaData\Editor\Vocabulary\AdapterInterface as VocabularyAdapter;
use ILIAS\MetaData\Vocabularies\Slots\Identifier as SlotIdentifier;
use ILIAS\UI\Component\Input\Input;

class ContentAssembler
{
    /**
     * POST VARS
     *
     * For some elements we can't just use the path
     * as the post key, because capitalization is not
     * preserved: LOMv1.0 as a data filter becomes lomv1.0
     */

    public const string GENERAL = 'general';
    public const string KEYWORDS = 'keywords';

    public const string CLASSIFICATION = 'classification';
    public const string LEARNING_RESOURCE_TYPE = 'learning_resource_type';
    public const string DISCIPLINE = 'discipline';

    public const string AUTHORS = 'authors';
    public const string FIRST_AUTHOR = 'first_author';
    public const string SECOND_AUTHOR = 'second_author';
    public const string THIRD_AUTHOR = 'third_author';
    public const string PUBLISHER = 'publisher';

    public const string RIGHTS = 'rights';
    public const string CUSTOM_CP = 'custom_cp';
    public const string CUSTOM_CP_DESCRIPTION = 'custom_cp_description';
    public const string OER_BLOCKED = 'oer_blocked_';

    protected PathFactory $path_factory;
    protected NavigatorFactoryInterface $navigator_factory;
    protected UIFactory $ui_factory;
    protected Refinery $refinery;
    protected PresenterInterface $presenter;
    protected PathCollection $path_collection;
    protected LinkFactory $link_factory;
    protected CopyrightHandler $copyright_handler;
    protected DataHelperInterface $data_helper;
    protected VocabularyAdapter $vocabulary_adapter;

    public function __construct(
        PathFactory $path_factory,
        NavigatorFactoryInterface $navigator_factory,
        UIFactory $factory,
        Refinery $refinery,
        PresenterInterface $presenter,
        PathCollection $path_collection,
        LinkFactory $link_factory,
        CopyrightHandler $copyright_handler,
        DataHelperInterface $data_helper,
        VocabularyAdapter $vocabulary_adapter
    ) {
        $this->path_factory = $path_factory;
        $this->navigator_factory = $navigator_factory;
        $this->ui_factory = $factory;
        $this->refinery = $refinery;
        $this->presenter = $presenter;
        $this->path_collection = $path_collection;
        $this->link_factory = $link_factory;
        $this->copyright_handler = $copyright_handler;
        $this->data_helper = $data_helper;
        $this->vocabulary_adapter = $vocabulary_adapter;
    }

    /**
     * @return StandardForm[]|InterruptiveModal[]|string[]
     */
    public function get(
        SetInterface $set,
        ?RequestForFormInterface $request = null
    ): \Generator {
        $sections = [
            self::GENERAL => $this->getGeneralSection($set),
            self::CLASSIFICATION => $this->getClassificationSection($set),
            self::AUTHORS => $this->getAuthorsSection($set)
        ];
        foreach ($this->getCopyrightContent($set) as $type => $entity) {
            if ($type === ContentType::FORM) {
                $sections[self::RIGHTS] = $entity;
                continue;
            }
            yield $type => $entity;
        }
        $form = $this->ui_factory->input()->container()->form()->standard(
            (string) $this->link_factory->custom(Command::UPDATE_DIGEST)->get(),
            $sections
        );

        if (isset($request)) {
            $form = $request->applyRequestToForm($form);
        }
        yield ContentType::FORM => $form;
    }

    protected function getGeneralSection(
        SetInterface $set
    ): Section {
        $ff = $this->ui_factory->input()->field();
        $root = $set->getRoot();
        $inputs = [];

        $title_el = $this->navigator_factory->navigator(
            $path = $this->path_collection->title(),
            $root
        )->lastElementAtFinalStep();
        $inputs[$path->toString()] = $ff
            ->text($this->presenter->utilities()->txt('meta_title'))
            ->withRequired(true)
            ->withValue($title_el?->getData()?->value() ?? '');

        $descr_els = $this->navigator_factory->navigator(
            $descr_path = $this->path_collection->descriptions(),
            $root
        )->elementsAtFinalStep();
        $descr_els = iterator_to_array($descr_els);
        $label = $this->presenter->utilities()->txt('meta_description');
        $empty_descr = true;
        foreach ($descr_els as $el) {
            $empty_descr = false;
            $label_with_lang = $label;
            foreach ($el->getSuperElement()->getSubElements() as $sub) {
                if (
                    $sub->getDefinition()->name() !== 'language' ||
                    ($value = $sub->getData()->value()) === ''
                ) {
                    continue;
                }
                $label_with_lang .= ' (' . $this->presenter->data()->language($value) . ')';
            }
            $inputs[$this->path_factory->toElement($el, true)->toString()] = $ff
                ->textarea($label_with_lang)
                ->withValue($el->getData()->value());
        }
        if ($empty_descr) {
            $inputs[$descr_path->toString()] = $ff
                ->textarea($label);
        }

        $langs = [];
        foreach ($this->data_helper->getAllLanguages() as $key) {
            $langs[$key] = $this->presenter->data()->language($key);
        }
        $lang_input = $ff->select(
            $this->presenter->utilities()->txt('meta_language'),
            $langs
        );
        $lang_els = $this->navigator_factory->navigator(
            $langs_path = $this->path_collection->languages(),
            $root
        )->elementsAtFinalStep();
        $empty_langs = true;
        foreach ($lang_els as $el) {
            $empty_langs = false;
            $inputs[$this->path_factory->toElement($el, true)->toString()] = (clone $lang_input)
                ->withValue($el->getData()->value());
        }
        if ($empty_langs) {
            $inputs[$langs_path->toString()] = clone $lang_input;
        }

        $keywords = [];
        $keyword_els = $this->navigator_factory->navigator(
            $keywords_path = $this->path_collection->keywords(),
            $root
        )->elementsAtFinalStep();
        foreach ($keyword_els as $el) {
            if (!$el->isScaffold()) {
                $keywords[] = $el->getData()->value();
            }
        }
        $inputs[self::KEYWORDS] = $ff->tag(
            $this->presenter->utilities()->txt('keywords'),
            $keywords
        )->withValue($keywords);

        return $ff->section(
            $inputs,
            $this->presenter->utilities()->txt('meta_general')
        );
    }

    protected function getClassificationSection(
        SetInterface $set
    ): Section {
        $ff = $this->ui_factory->input()->field();
        $inputs = [];

        $type_el = $this->navigator_factory->navigator(
            $this->path_collection->firstLearningResourceType(),
            $set->getRoot()
        )->lastElementAtFinalStep();
        $data = !$type_el?->isScaffold() ? $type_el?->getData()?->value() : null;
        $values = [];
        $raw_values = $this->vocabulary_adapter->valuesInVocabulariesForSlot(
            SlotIdentifier::EDUCATIONAL_LEARNING_RESOURCE_TYPE,
            $data
        );
        foreach ($this->presenter->data()->vocabularyValues(
            SlotIdentifier::EDUCATIONAL_LEARNING_RESOURCE_TYPE,
            ...$raw_values
        ) as $labelled_value) {
            $values[$labelled_value->value()] = $labelled_value->label();
        }
        $input = $this->ui_factory->input()->field()->select(
            $this->presenter->utilities()->txt('meta_learning_resource_type'),
            $values
        );
        if (isset($data)) {
            $input = $input->withValue($data);
        }
        $inputs[self::LEARNING_RESOURCE_TYPE] = $input;

        $discipline_el = $this->navigator_factory->navigator(
            $this->path_collection->firstDiscipline(),
            $set->getRoot()
        )->lastElementAtFinalStep();
        $inputs[self::DISCIPLINE] = $this->buildStringFromControlledVocabInput(
            $this->presenter->utilities()->txt('meta_discipline'),
            SlotIdentifier::CLASSIFICATION_TAXON_ENTRY,
            !$discipline_el?->isScaffold() ? $discipline_el?->getData()?->value() : null
        );

        return $ff->section(
            $inputs,
            $this->presenter->utilities()->txt('meta_classification')
        );
    }

    protected function getAuthorsSection(
        SetInterface $set
    ): Section {
        $ff = $this->ui_factory->input()->field();
        $inputs = [];

        $paths = [
            $this->path_collection->firstAuthor(),
            $this->path_collection->secondAuthor(),
            $this->path_collection->thirdAuthor()
        ];
        $labels = [
            $this->presenter->utilities()->txt('meta_first_author'),
            $this->presenter->utilities()->txt('meta_second_author'),
            $this->presenter->utilities()->txt('meta_third_author')
        ];
        $post_keys = [
            self::FIRST_AUTHOR,
            self::SECOND_AUTHOR,
            self::THIRD_AUTHOR,
            self::PUBLISHER
        ];
        foreach ($paths as $path) {
            $el = $this->navigator_factory->navigator(
                $path,
                $set->getRoot()
            )->lastElementAtFinalStep();
            $inputs[array_shift($post_keys)] = $ff
                ->text(array_shift($labels))
                ->withValue($el?->getData()?->value() ?? '');
        }

        $publisher_el = $this->navigator_factory->navigator(
            $this->path_collection->firstPublisher(),
            $set->getRoot()
        )->lastElementAtFinalStep();
        $inputs[self::PUBLISHER] = $this->buildStringFromControlledVocabInput(
            $this->presenter->utilities()->txt('meta_publisher'),
            SlotIdentifier::LIFECYCLE_CONTRIBUTE_PUBLISHER,
            $publisher_el?->getData()?->value()
        );

        return $ff->section(
            $inputs,
            $this->presenter->utilities()->txt('meta_authors')
        );
    }

    /**
     * @return Section[]|InterruptiveModal[]|string[]
     */
    protected function getCopyrightContent(
        SetInterface $set
    ): \Generator {
        if (!$this->copyright_handler->isCPSelectionActive()) {
            return;
        }
        $modal = $this->getChangeCopyrightModal();

        yield ContentType::MODAL => $modal;
        yield ContentType::JS_SOURCE => 'assets/js/ilMetaCopyrightListener.js';
        yield ContentType::FORM => $this->getCopyrightSection($set);
    }

    protected function getChangeCopyrightModal(): InterruptiveModal
    {
        return $this->ui_factory->modal()->interruptive(
            $this->presenter->utilities()->txt("meta_copyright_change_warning_title"),
            $this->presenter->utilities()->txt("meta_copyright_change_info"),
            (string) $this->link_factory->custom(Command::UPDATE_DIGEST)->get()
        );
    }

    protected function getCopyrightSection(
        SetInterface $set
    ): Section {
        $ff = $this->ui_factory->input()->field();

        $cp_description_el = $this->navigator_factory->navigator(
            $this->path_collection->copyright(),
            $set->getRoot()
        )->lastElementAtFinalStep();
        $cp_description = $cp_description_el?->getData()->value();

        $current_id = $this->copyright_handler->extractCPEntryID((string) $cp_description);
        $default_id = 0;
        $options = [];
        $outdated = [];
        $potential_oer_values = [];
        $current_id_exists = false;
        $is_custom = !is_null($cp_description) && !$current_id;

        foreach ($this->copyright_handler->getCPEntries() as $entry) {
            if ($entry->isDefault()) {
                $default_id = $entry->id();
            }
            if ($current_id === $entry->id()) {
                $current_id_exists = true;
            }

            $identifier = $this->copyright_handler->createIdentifierForID($entry->id());
            if (
                $this->copyright_handler->isObjectTypePublished($set->getRessourceID()->type()) &&
                $this->copyright_handler->isCopyrightEntryPublished($entry)
            ) {
                $potential_oer_values[] = $identifier;
            }

            $option = $ff->group([], $entry->title(), $entry->description());

            // outdated entries throw an error when selected
            if ($entry->isOutdated()) {
                $option = $option->withLabel(
                    '(' . $this->presenter->utilities()->txt('meta_copyright_outdated') .
                    ') ' . $entry->title()
                )->withDisabled(true);
                $outdated[] = $identifier;
            }
            $options[$identifier] = $option;
        }

        //custom input as the last option
        $custom_text = $ff
            ->textarea($this->presenter->utilities()->txt('meta_description'))
            ->withValue($is_custom ? (string) $cp_description : '');
        $custom = $ff->group(
            [self::CUSTOM_CP_DESCRIPTION => $custom_text],
            $this->presenter->utilities()->txt('meta_cp_own')
        );
        $options[self::CUSTOM_CP] = $custom;

        $value = self::CUSTOM_CP;
        if (!$is_custom) {
            $id = ($current_id && $current_id_exists) ? $current_id : $default_id;
            $value = $this->copyright_handler->createIdentifierForID($id);
        }

        $copyright = $ff
            ->switchableGroup(
                $options,
                $this->presenter->utilities()->txt('meta_copyright')
            )
            ->withValue($value)
            ->withAdditionalOnLoadCode(
                function ($id) use ($potential_oer_values) {
                    $cp_change_message = $this->presenter->utilities()->txt("meta_copyright_change_info");
                    $cp_change_message_with_warning = $cp_change_message . "<br/><br/>" .
                        $this->presenter->utilities()->txt("meta_copyright_change_oer_info");

                    return 'il.MetaDataCopyrightListener.init(\'' .
                        $cp_change_message . '\',\'' .
                        $cp_change_message_with_warning . '\',\'' .
                        json_encode($potential_oer_values) . '\',\'' .
                        $id . '\');';
                }
            )->withAdditionalTransformation(
                $this->refinery->custom()->constraint(
                    function ($v) use ($outdated) {
                        if (in_array($v[0], $outdated, true)) {
                            return false;
                        }
                        return true;
                    },
                    $this->presenter->utilities()->txt('meta_copyright_outdated_error')
                )
            );

        return $ff->section(
            [$copyright],
            $this->presenter->utilities()->txt('meta_rights')
        );
    }

    protected function buildStringFromControlledVocabInput(
        string $label,
        SlotIdentifier $slot,
        ?string $data = null
    ): Input {
        if (!$this->vocabulary_adapter->doesSlotHaveVocabularies($slot)) {
            $input = $this->ui_factory->input()->field()->text($label);
            if (isset($data)) {
                $input = $input->withValue($data);
            }
            return $input;
        }

        $values = [];
        $raw_values = iterator_to_array($this->vocabulary_adapter->valuesInVocabulariesForSlot(
            $slot,
            !$this->vocabulary_adapter->doesSlotAllowCustomInput($slot) ? $data : null
        ));
        foreach ($this->presenter->data()->vocabularyValues($slot, ...$raw_values) as $labelled_value) {
            $values[$labelled_value->value()] = $labelled_value->label();
        }

        if (!$this->vocabulary_adapter->doesSlotAllowCustomInput($slot)) {
            $input = $this->ui_factory->input()->field()->select($label, $values);
            if (isset($data)) {
                $input = $input->withValue($data);
            }
            return $input;
        }

        $value_label = $this->presenter->utilities()->txt('md_editor_value');
        $text_input = $this->ui_factory->input()->field()->text($value_label);
        $select_input = $this->ui_factory->input()->field()->select($value_label, $values);

        $radio_value = 'from_vocab';
        if (isset($data)) {
            if (in_array($data, $raw_values)) {
                $select_input = $select_input->withValue($data);
                $radio_value = 'from_vocab';
            } else {
                $text_input = $text_input->withValue($data);
                $radio_value = 'custom';
            }
        }

        $input = $this->ui_factory->input()->field()->switchableGroup(
            [
                'from_vocab' => $this->ui_factory->input()->field()->group(
                    ['value' => $select_input],
                    $this->presenter->utilities()->txt('md_editor_from_vocab_input')
                ),
                'custom' => $this->ui_factory->input()->field()->group(
                    ['value' => $text_input],
                    $this->presenter->utilities()->txt('md_editor_custom_input')
                )
            ],
            $label
        );
        if (isset($radio_value)) {
            $input = $input->withValue($radio_value);
        }
        return $input->withAdditionalTransformation(
            $this->refinery->custom()->transformation(function ($vs) {
                return $vs[1]['value'] ?? null;
            })
        );
    }
}
