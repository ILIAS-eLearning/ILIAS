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
use ILIAS\MetaData\Repository\Validation\Data\LangValidator;
use ILIAS\MetaData\Editor\Http\LinkFactory;
use ILIAS\MetaData\Editor\Http\Command;
use ILIAS\MetaData\Repository\Validation\Data\DurationValidator;
use ILIAS\UI\Component\Signal;
use ILIAS\MetaData\Editor\Manipulator\ManipulatorInterface;

class ContentAssembler
{
    // post variables
    public const KEYWORDS = 'keywords';
    public const GENERAL = 'general';
    public const AUTHORS = 'authors';
    public const RIGHTS = 'rights';
    public const TYPICAL_LEARNING_TIME = 'tlt';
    public const FIRST_AUTHOR = 'first_author';
    public const SECOND_AUTHOR = 'second_author';
    public const THIRD_AUTHOR = 'third_author';

    public const CUSTOM_CP = 'custom_cp';
    public const CUSTOM_CP_DESCRIPTION = 'custom_cp_description';
    public const OER_BLOCKED = 'oer_blocked_';

    protected PathFactory $path_factory;
    protected NavigatorFactoryInterface $navigator_factory;
    protected UIFactory $ui_factory;
    protected Refinery $refinery;
    protected PresenterInterface $presenter;
    protected PathCollection $path_collection;
    protected LinkFactory $link_factory;
    protected CopyrightHandler $copyright_handler;

    public function __construct(
        PathFactory $path_factory,
        NavigatorFactoryInterface $navigator_factory,
        UIFactory $factory,
        Refinery $refinery,
        PresenterInterface $presenter,
        PathCollection $path_collection,
        LinkFactory $link_factory,
        CopyrightHandler $copyright_handler
    ) {
        $this->path_factory = $path_factory;
        $this->navigator_factory = $navigator_factory;
        $this->ui_factory = $factory;
        $this->refinery = $refinery;
        $this->presenter = $presenter;
        $this->path_collection = $path_collection;
        $this->link_factory = $link_factory;
        $this->copyright_handler = $copyright_handler;
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
            self::AUTHORS => $this->getAuthorsSection($set)
        ];
        foreach ($this->getCopyrightContent($set) as $type => $entity) {
            if ($type === ContentType::FORM) {
                $sections[self::RIGHTS] = $entity;
                continue;
            }
            yield $type => $entity;
        }
        $sections[self::TYPICAL_LEARNING_TIME] = $this->getTypicalLearningTimeSection($set);
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
        foreach (LangValidator::LANGUAGES as $key) {
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
            self::THIRD_AUTHOR
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
        $signal = $modal->getShowSignal();

        yield ContentType::MODAL => $modal;
        yield ContentType::JS_SOURCE => 'Services/MetaData/js/ilMetaCopyrightListener.js';
        yield ContentType::FORM => $this->getCopyrightSection($set, $signal);
    }

    protected function getChangeCopyrightModal(): InterruptiveModal
    {
        $modal = $this->ui_factory->modal()->interruptive(
            $this->presenter->utilities()->txt("meta_copyright_change_warning_title"),
            $this->presenter->utilities()->txt("meta_copyright_change_info"),
            (string) $this->link_factory->custom(Command::UPDATE_DIGEST)->get()
        );

        return $modal;
    }

    protected function getCopyrightSection(
        SetInterface $set,
        Signal $signal
    ): Section {
        $ff = $this->ui_factory->input()->field();

        $cp_description_el = $this->navigator_factory->navigator(
            $this->path_collection->copyright(),
            $set->getRoot()
        )->lastElementAtFinalStep();
        $cp_description = $cp_description_el?->getData()->value();
        if ($cp_description !== '' && $cp_description !== null) {
            $current_id = $this->copyright_handler->extractCPEntryID($cp_description);
        } else {
            $current_id = $this->copyright_handler->getDefaultCPEntryID();
        }

        $options = [];
        $outdated = [];
        foreach ($this->copyright_handler->getCPEntries() as $entry) {
            //give the option to block harvesting
            $sub_inputs = [];
            if (
                $this->copyright_handler->doesObjectTypeSupportHarvesting($set->getRessourceID()->type()) &&
                $this->copyright_handler->isCopyrightTemplateActive($entry)
            ) {
                $sub_inputs[self::OER_BLOCKED] = $ff
                    ->checkbox(
                        $this->presenter->utilities()->txt('meta_oer_blocked'),
                        $this->presenter->utilities()->txt('meta_oer_blocked_info')
                    )
                    ->withValue(
                        $this->copyright_handler->isOerHarvesterBlocked($set->getRessourceID()->objID())
                    );
            }

            $option = $ff->group($sub_inputs, $entry->getTitle());
            $identifier = $this->copyright_handler->createIdentifierForID($entry->getEntryId());

            // outdated entries throw an error when selected
            if ($entry->getOutdated()) {
                $option = $option->withLabel(
                    '(' . $this->presenter->utilities()->txt('meta_copyright_outdated') .
                    ') ' . $entry->getTitle()
                );
                $outdated[] = $identifier;
            }
            $options[$identifier] = $option;
        }

        //custom input as the last option
        $custom_text = $ff
            ->textarea($this->presenter->utilities()->txt('meta_description'))
            ->withValue($current_id === 0 ? $cp_description : '');
        $custom = $ff->group(
            [self::CUSTOM_CP_DESCRIPTION => $custom_text],
            $this->presenter->utilities()->txt('meta_cp_own')
        );
        $options[self::CUSTOM_CP] = $custom;

        $value = $current_id === 0 ?
            self::CUSTOM_CP :
            $this->copyright_handler->createIdentifierForID($current_id);
        $copyright = $ff
            ->switchableGroup(
                $options,
                $this->presenter->utilities()->txt('meta_copyright')
            )
            ->withValue($value)
            ->withAdditionalOnLoadCode(
                function ($id) use ($signal) {
                    return 'il.MetaDataCopyrightListener.init("' .
                        $signal . '","' . $id . '");';
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

    protected function getTypicalLearningTimeSection(
        SetInterface $set
    ): Section {
        $ff = $this->ui_factory->input()->field();
        $inputs = [];

        $tlt_el = $this->navigator_factory->navigator(
            $path = $this->path_collection->firstTypicalLearningTime(),
            $set->getRoot()
        )->lastElementAtFinalStep();
        preg_match(
            DurationValidator::DURATION_REGEX,
            $tlt_el?->getData()?->value() ?? '',
            $matches,
            PREG_UNMATCHED_AS_NULL
        );
        $num = $ff->numeric('placeholder')
                  ->withAdditionalTransformation($this->refinery->int()->isGreaterThanOrEqual(0));
        $labels = [
            $this->presenter->utilities()->txt('years'),
            $this->presenter->utilities()->txt('months'),
            $this->presenter->utilities()->txt('days'),
            $this->presenter->utilities()->txt('hours'),
            $this->presenter->utilities()->txt('minutes'),
            $this->presenter->utilities()->txt('seconds')
        ];
        $inputs = [];
        foreach ($labels as $key => $label) {
            $inputs[] = (clone $num)
                ->withLabel($label)
                ->withValue($matches[$key + 1] ?? null);
        }
        $group = $ff->group(
            $inputs
        )->withAdditionalTransformation(
            $this->refinery->custom()->transformation(function ($vs) use ($path) {
                if (
                    count(array_unique($vs)) === 1 &&
                    array_unique($vs)[0] === null
                ) {
                    return '';
                }
                $r = 'P';
                $signifiers = ['Y', 'M', 'D', 'H', 'M', 'S'];
                foreach ($vs as $key => $int) {
                    if (isset($int)) {
                        $r .= $int . $signifiers[$key];
                    }
                    if (
                        $key === 2 &&
                        !isset($vs[3]) &&
                        !isset($vs[4]) &&
                        !isset($vs[5])
                    ) {
                        return $r;
                    }
                    if ($key === 2) {
                        $r .= 'T';
                    }
                }
                return $r;
            })
        );

        return $ff->section(
            [$path->toString() => $group],
            $this->presenter->utilities()->txt('meta_typical_learning_time')
        );
    }
}
