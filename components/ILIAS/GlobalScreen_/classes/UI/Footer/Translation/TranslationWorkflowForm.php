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

namespace ILIAS\GlobalScreen\UI\Footer\Translation;

use ILIAS\UI\Factory;
use ILIAS\MetaData\Services\ServicesInterface;
use ILIAS\UI\Component\Prompt\State\State;
use ILIAS\DI\UIServices;
use ILIAS\UI\Component\Input\Container\Form\Standard;
use ILIAS\Data\URI;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\Component\Modal\RoundTrip;
use ILIAS\GlobalScreen\UI\Footer\Groups\GroupDTO;
use ILIAS\GlobalScreen\UI\Footer\Entries\EntryDTO;

class TranslationWorkflowForm
{
    /**
     * @var string
     */
    private const STEP = 'step';
    /**
     * @var string
     */
    private const STEP_SELECTED_LNGS = 'selectedLngs';
    /**
     * @var string
     */
    private const STEP_SAVE_TRANSLATIONS = 'saveTranslations';
    private readonly Factory $ui_factory;
    private readonly \ilLanguage $lng;
    private readonly ServerRequestInterface $request;

    private bool $all_languages = false;
    private readonly Translations $translations;
    private readonly \ILIAS\UI\Component\Input\Field\Factory $fields;
    private readonly \ILIAS\Refinery\Factory $refinery;
    private readonly \ILIAS\UI\Component\Prompt\State\Factory $states;

    public function __construct(
        private readonly ServicesInterface $lom_services,
        private readonly UIServices $ui,
        private readonly TranslationsRepository $repository,
        private readonly TranslatableItem $item
    ) {
        global $DIC;
        $this->request = $DIC->http()->request();
        $this->refinery = $DIC->refinery();
        $this->ui_factory = $ui->factory();
        $this->lng = $DIC->language();
        $this->translations = $repository->get($item);
        $this->fields = $this->ui_factory->input()->field();
        $this->states = $this->ui_factory->prompt()->state();
    }

    public function asTranslationWorkflow(
        URI $here_uri,
        URI $success_target
    ): State {
        $step = $this->request->getQueryParams()[self::STEP] ?? null;

        $language_slection = $this->getLanguageSelectionForm(
            $here_uri->withParameter(self::STEP, self::STEP_SELECTED_LNGS)
        );

        switch ($step) {
            default:
                return $this->states->show($language_slection);
            case self::STEP_SELECTED_LNGS:
                // store
                $data = $language_slection->withRequest($this->request)->getData();
                return $this->states
                    ->show(
                        $this->getTranslationForm(
                            $here_uri->withParameter(self::STEP, self::STEP_SAVE_TRANSLATIONS),
                            $data
                        )
                    );
            case self::STEP_SAVE_TRANSLATIONS:
                $active_language_keys = [];
                foreach ($this->translations->getLanguageKeys() as $language_key) {
                    $active_language_keys[$language_key] = true;
                }

                $tranlation_form = $this
                    ->getTranslationForm(
                        $here_uri->withParameter(self::STEP, self::STEP_SAVE_TRANSLATIONS),
                        $active_language_keys
                    )
                    ->withRequest($this->request);

                $data = $tranlation_form->getData();
                if ($data !== null) {
                    return $this->states
                        ->redirect($success_target->withParameter(self::STEP, ''));
                }
                return $this->states->show($tranlation_form);
        }
    }

    public function getTranslationForm(URI $form_target, array $language_keys): Standard
    {
        $inputs = $this->getTranslationInputs($language_keys);
        return $this->ui_factory->input()->container()->form()->standard(
            (string) $form_target,
            $inputs
        )->withAdditionalTransformation(
            $this->refinery->custom()->transformation(function ($value) {
                $this->repository->store($this->translations);
                return $value;
            })
        );
    }

    protected function getTranslationInputs(array $language_keys, bool $required = true): array
    {
        $default_value = match (true) {
            ($this->item instanceof GroupDTO) => $this->item->getTitle(),
            ($this->item instanceof EntryDTO) => $this->item->getTitle(),
            default => ''
        };

        $languages = [];
        foreach ($this->lom_services->dataHelper()->getAllLanguages() as $language) {
            $languages[$language->value()] = $language->presentableLabel();
        }

        $inputs = [];
        foreach ($language_keys as $language_key => $active) {
            if (!$active) {
                continue;
            }
            $translation = $this->translations->getLanguageCode($language_key)?->getTranslation();
            $language_title = $languages[$language_key] ?? null;
            if ($language_title === null) {
                continue;
            }
            $inputs[$language_key] = $this->fields
                ->text($language_title)
                ->withRequired($required)
                ->withValue(
                    $translation ?? $default_value
                )
                ->withAdditionalTransformation(
                    $this->refinery->custom()->transformation(function ($value) use ($language_key) {
                        $this->translations->add(
                            $this->repository->blank($this->item, $language_key, $value)
                        );
                        return $value;
                    })
                );
        }
        return $inputs;
    }

    public function getLanguageSelectionForm(URI $form_target): Standard
    {
        $inputs = $this->getLanguageSelectionInputs();

        return $this->ui_factory->input()->container()->form()->standard(
            (string) $form_target,
            $inputs
        )->withAdditionalTransformation(
            $this->refinery->custom()->transformation(function ($value) {
                $this->repository->store($this->translations);
                return $value;
            })
        );
    }

    private function getLanguageSelectionInputs(): array
    {
        $inputs = [];
        $all_languages = $this->lom_services->dataHelper()->getAllLanguages();
        $installed_languages = $this->lng->getInstalledLanguages();

        foreach ($all_languages as $language) {
            $language_code = $language->value();
            if (!$this->all_languages && !in_array($language_code, $installed_languages, true)) {
                continue;
            }
            $inputs[$language_code] = $this->fields
                ->checkbox($language->presentableLabel())
                ->withValue(
                    $this->translations->getLanguageCode($language_code) !== null
                )->withAdditionalTransformation(
                    $this->refinery->custom()->transformation(function ($value) use ($language_code) {
                        if (!$value) {
                            $this->translations->remove($language_code);
                        } else {
                            $this->translations->add(
                                $this->repository->blank(
                                    $this->item,
                                    $language_code,
                                    $this->translations->getLanguageCode($language_code)?->getTranslation() ?? ''
                                )
                            );
                        }
                        return $value;
                    })
                );
        }
        return $inputs;
    }

    // ALTERNATIVE

    public function asTranslationModal(URI $form_target): RoundTrip
    {
        $languages = [];
        foreach ($this->lng->getInstalledLanguages() as $installed_language) {
            $languages[$installed_language] = true;
        }

        return $this->ui_factory->modal()->roundtrip(
            $this->lng->txt('translations'),
            null,
            $this->getTranslationInputs($languages, false),
            $form_target
        )->withAdditionalTransformation(
            $this->refinery->custom()->transformation(function ($value) {
                $this->repository->store($this->translations);
                return $value;
            })
        );
    }

}
