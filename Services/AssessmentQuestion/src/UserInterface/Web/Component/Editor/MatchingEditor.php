<?php
namespace ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor;

use ILIAS\AssessmentQuestion\DomainModel\AbstractConfiguration;
use ilSelectInputGUI;
use ilNumberInputGUI;
use ilRadioGroupInputGUI;
use ilRadioOption;
use ILIAS\AssessmentQuestion\UserInterface\Web\Fields\AsqTableInput;
use ILIAS\AssessmentQuestion\UserInterface\Web\Fields\AsqTableInputFieldDefinition;

/**
 * Class KprimChoiceEditor
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author Adrian Lüthi <al@studer-raimann.ch>
 * @author Björn Heyser <bh@bjoernheyser.de>
 * @author Martin Studer <ms@studer-raimann.ch>
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class MatchingEditor extends AbstractEditor
{

    const VAR_SHUFFLE = 'me_shuffle';

    const VAR_THUMBNAIL = 'me_thumbnail';

    const VAR_MATCHING_MODE = 'me_matching';

    const VAR_DEFINITION_TEXT = 'me_definition_text';

    const VAR_DEFINITION_IMAGE = 'me_definition_image';

    const VAR_TERM_TEXT = 'me_term_text';

    const VAR_TERM_IMAGE = 'me_term_image';

    const VAR_PAIR_DEFINITION = 'me_pair_definition';

    const VAR_PAIR_TERM = 'me_pair_term';

    const VAR_PAIR_POINTS = 'me_pair_points';

    const VAR_PAIRS = 'me_pairs';

    public function readAnswer(): string
    {}

    public function setAnswer(string $answer): void
    {}

    public function generateHtml(): string
    {}

    /**
     *
     * @param AbstractConfiguration|null $config
     *
     * @return array|null
     */
    public static function generateFields(?AbstractConfiguration $config): ?array
    {
        global $DIC;

        $fields = [];
        /** @var MatchingEditorConfiguration $config */

        $shuffle_answers = new ilSelectInputGUI($DIC->language()->txt('asq_label_shuffle_answers'), self::VAR_SHUFFLE);
        $shuffle_answers->setOptions([
            MatchingEditorConfiguration::SHUFFLE_NONE => $DIC->language()
                ->txt('asq_option_shuffle_none'),
            MatchingEditorConfiguration::SHUFFLE_DEFINITIONS => $DIC->language()
                ->txt('asq_option_shuffle_definitions'),
            MatchingEditorConfiguration::SHUFFLE_TERMS => $DIC->language()
                ->txt('asq_option_shuffle_terms'),
            MatchingEditorConfiguration::SHUFFLE_BOTH => $DIC->language()
                ->txt('asq_option_shuffle_both')
        ]);
        $fields[] = $shuffle_answers;

        $thumbnail = new ilNumberInputGUI($DIC->language()->txt('asq_label_thumbnail'), self::VAR_THUMBNAIL);
        $thumbnail->setRequired(true);
        $fields[] = $thumbnail;

        $matching_mode = new ilRadioGroupInputGUI($DIC->language()->txt('asq_label_matching_mode'), self::VAR_MATCHING_MODE);
        $matching_mode->addOption(new ilRadioOption($DIC->language()
            ->txt('asq_option_one_to_one'), MatchingEditorConfiguration::MATCHING_ONE_TO_ONE));
        $matching_mode->addOption(new ilRadioOption($DIC->language()
            ->txt('asq_option_many_to_one'), MatchingEditorConfiguration::MATCHING_MANY_TO_ONE));
        $matching_mode->addOption(new ilRadioOption($DIC->language()
            ->txt('asq_option_many_to_one'), MatchingEditorConfiguration::MATCHING_MANY_TO_MANY));
        $fields[] = $matching_mode;

        if (! is_null($config)) {
            $shuffle_answers->setValue($config->getShuffle());
            $thumbnail->setValue($config->getThumbnailSize());
            $matching_mode->setValue($config->getMatchingMode());
        } else {
            $thumbnail->setValue(100);
        }

        $fields[] = self::createDefinitionsTable($config);
        $fields[] = self::createTermsTable($config);
        $fields[] = self::createPairTable($config);

        return $fields;
    }

    private static function createDefinitionsTable(MatchingEditorConfiguration $config)
    {
        global $DIC;

        $columns = [];

        $columns[] = new AsqTableInputFieldDefinition($DIC->language()->txt('asq_header_definition_text'), 
            AsqTableInputFieldDefinition::TYPE_TEXT, 
            self::VAR_DEFINITION_TEXT);

        $columns[] = new AsqTableInputFieldDefinition($DIC->language()->txt('asq_header_definition_image'), 
            AsqTableInputFieldDefinition::TYPE_IMAGE, 
            self::VAR_DEFINITION_IMAGE);

        return new AsqTableInput($DIC->language()->txt('asq_label_definitions'), 
            $config->getDefinitions(), 
            $columns);
    }

    private static function createTermsTable(MatchingEditorConfiguration $config)
    {
        global $DIC;

        $columns = [];

        $columns[] = new AsqTableInputFieldDefinition($DIC->language()->txt('asq_header_term_text'), 
            AsqTableInputFieldDefinition::TYPE_TEXT, 
            self::VAR_TERM_TEXT);

        $columns[] = new AsqTableInputFieldDefinition($DIC->language()->txt('asq_header_term_image'), 
            AsqTableInputFieldDefinition::TYPE_IMAGE, 
            self::VAR_TERM_IMAGE);

        return new AsqTableInput($DIC->language()->txt('asq_label_terms'), 
            $config->getDefinitions(), 
            $columns);
    }

    private static function createPairTable(MatchingEditorConfiguration $config)
    {
        global $DIC;
        
        $columns = [];
        
        $columns[] = new AsqTableInputFieldDefinition($DIC->language()->txt('asq_header_pair_definition'),
            AsqTableInputFieldDefinition::TYPE_DROPDOWN,
            self::VAR_PAIR_DEFINITION, 
            [1,2,3]);
        
        $columns[] = new AsqTableInputFieldDefinition($DIC->language()->txt('asq_header_pair_term'),
            AsqTableInputFieldDefinition::TYPE_DROPDOWN,
            self::VAR_PAIR_TERM,
            [1,2,3]);

        $columns[] = new AsqTableInputFieldDefinition($DIC->language()->txt('asq_header_points'),
            AsqTableInputFieldDefinition::TYPE_NUMBER,
            self::VAR_PAIR_POINTS);
        
        return new AsqTableInput($DIC->language()->txt('asq_label_terms'),
            $config->getMatches(),
            $columns);
    }
    
    public static function readConfig()
    {
        return MatchingEditorConfiguration::create(intval($_POST[self::VAR_SHUFFLE]), intval($_POST[self::VAR_THUMBNAIL]), intval($_POST[self::VAR_MATCHING_MODE]));
    }

    /**
     *
     * @return string
     */
    static function getDisplayDefinitionClass(): string
    {
        return EmptyDisplayDefinition::class;
    }
}