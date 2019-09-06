<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Mail/classes/class.ilMailTemplateContext.php';

/**
 * Handles exercise Submit reminder mail placeholders
 * If all contexts are using the same placeholders,constructor etc. todo: create base class.
 *
 * @author Jesús López <lopez@leifos.com>
 * @package ModulesExercise
 */
class ilExcMailTemplateSubmitReminderContext extends ilMailTemplateContext
{
    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilObjectDataCache
     */
    protected $obj_data_cache;


    /**
     * Constructor
     */
    function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
        if (isset($DIC["ilObjDataCache"])) {
            $this->obj_data_cache = $DIC["ilObjDataCache"];
        }
    }

    const ID = 'exc_context_submit_rmd';

    /**
     * @return string
     */
    public function getId() : string
    {
        return self::ID;
    }

    /**
     * @return string
     */
    public function getTitle() : string
    {
        $lng = $this->lng;

        $lng->loadLanguageModule('exc');

        return $lng->txt('exc_mail_context_submit_reminder_title');
    }

    /**
     * @return string
     */
    public function getDescription() : string
    {
        $lng = $this->lng;

        $lng->loadLanguageModule('exc');

        return $lng->txt('exc_mail_context_submit_reminder_info');
    }

    /**
     * Return an array of placeholders
     * @return array
     */
    public function getSpecificPlaceholders() : array
    {
        $lng = $this->lng;
        $lng->loadLanguageModule('exc');

        $placeholders = array();

        $placeholders['ass_title'] = array(
            'placeholder' => 'ASSIGNMENT_TITLE',
            'label' => $lng->txt('exc_mail_context_reminder_assignment_title')
        );
        $placeholders['exc_title'] = array(
            'placeholder' => 'EXERCISE_TITLE',
            'label' => $lng->txt('exc_mail_context_reminder_exercise_title')
        );

        $placeholders['ass_link'] = array(
            'placeholder' => 'ASSIGNMENT_LINK',
            'label' => $lng->txt('perma_link')
        );

        return $placeholders;
    }

    /**
     * {@inheritdoc}
     */
    public function resolveSpecificPlaceholder(
        string $placeholder_id,
        array $context_parameters,
        ilObjUser $recipient = null,
        bool $html_markup = false
    ) : string {
        $ilObjDataCache = $this->obj_data_cache;

        if ($placeholder_id == 'ass_title') {
            return ilExAssignment::lookupTitle($context_parameters["ass_id"]);
        } else {
            if ($placeholder_id == 'exc_title') {
                return $ilObjDataCache->lookupTitle($context_parameters["exc_id"]);

            } else {
                if ($placeholder_id == 'ass_link') {
                    require_once './Services/Link/classes/class.ilLink.php';
                    return ilLink::_getLink($context_parameters["exc_ref"], "exc", array(),
                        "_" . $context_parameters["ass_id"]);
                }
            }
        }

        return '';
    }
}