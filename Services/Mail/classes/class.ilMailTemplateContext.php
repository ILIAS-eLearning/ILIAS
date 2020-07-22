<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Services/Language/classes/class.ilLanguageFactory.php';

/**
 * Class ilMailTemplateContext
 * @author  Michael Jansen <mjansen@databay.de>
 * @ingroup ServicesMail
 */
abstract class ilMailTemplateContext
{
    /**
     * @var ilLanguage|null
     */
    protected $language;

    /**
     * @return ilLanguage|null
     */
    public function getLanguage()
    {
        global $DIC;

        return $this->language ? $this->language : $DIC->language();
    }

    /**
     * @param ilLanguage|null $language
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    }

    /**
     * Returns a unique (in the context of mail template contexts) id
     * @return string
     */
    abstract public function getId();

    /**
     * Returns a translated title (depending on the current language) which is displayed in the user interface
     * @return string
     */
    abstract public function getTitle();

    /**
     * Returns a translated description (depending on the current language) which is displayed in the user interface
     * @return string
     */
    abstract public function getDescription();

    /**
     * @return array
     */
    final private static function getGenericPlaceholders()
    {
        global $DIC;

        return array(
            'mail_salutation' => array(
                'placeholder' => 'MAIL_SALUTATION',
                'label' => $DIC->language()->txt('mail_nacc_salutation')
            ),
            'first_name' => array(
                'placeholder' => 'FIRST_NAME',
                'label' => $DIC->language()->txt('firstname')
            ),
            'last_name' => array(
                'placeholder' => 'LAST_NAME',
                'label' => $DIC->language()->txt('lastname')
            ),
            'login' => array(
                'placeholder' => 'LOGIN',
                'label' => $DIC->language()->txt('mail_nacc_login')
            ),
            'title' => array(
                'placeholder' => 'TITLE',
                'label' => $DIC->language()->txt('mail_nacc_title'),
                'supportsCondition' => true
            ),
            'ilias_url' => array(
                'placeholder' => 'ILIAS_URL',
                'label' => $DIC->language()->txt('mail_nacc_ilias_url')
            ),
            'client_name' => array(
                'placeholder' => 'CLIENT_NAME',
                'label' => $DIC->language()->txt('mail_nacc_client_name')
            )
        );
    }

    /**
     * Return an array of placeholders
     * @return array
     */
    final public function getPlaceholders()
    {
        $placeholders = self::getGenericPlaceholders();
        $specific = $this->getSpecificPlaceholders();

        return $placeholders + $specific;
    }

    /**
     * Return an array of placeholders
     * @return array
     */
    abstract public function getSpecificPlaceholders();

    /**
     * @param string         $placeholder_id
     * @param array          $context_parameters
     * @param ilObjUser|null $recipient
     * @param bool           $html_markup
     * @return string
     */
    abstract public function resolveSpecificPlaceholder($placeholder_id, array $context_parameters, ilObjUser $recipient = null, $html_markup = false);

    /**
     * @param string         $placeholder_id     The unique (in the context of your class) placeholder id
     * @param array          $context_parameters The context parameters given by the mail system (array of key/value pairs)
     * @param ilObjUser|null $recipient          The recipient for this mail
     * @param bool           $html_markup        A flag whether or not the return value may contain HTML markup
     * @return string
     */
    public function resolvePlaceholder($placeholder_id, array $context_parameters, ilObjUser $recipient = null, $html_markup = false)
    {
        if ($recipient !== null) {
            $this->initLanguage($recipient);
        }

        $old_lang = ilDatePresentation::getLanguage();
        ilDatePresentation::setLanguage($this->getLanguage());

        $resolved = '';

        switch (true) {
            case ('mail_salutation' == $placeholder_id && $recipient !== null):
                switch ($recipient->getGender()) {
                    case 'f':
                        $resolved = $this->getLanguage()->txt('mail_salutation_f');
                        break;

                    case 'm':
                        $resolved = $this->getLanguage()->txt('mail_salutation_m');
                        break;

                    case 'n':
                        $resolved = $this->getLanguage()->txt('mail_salutation_n');
                        break;

                    default:
                        $resolved = $this->getLanguage()->txt('mail_salutation_n');
                }
                break;
            
            case ('first_name' == $placeholder_id && $recipient !== null):
                $resolved = $recipient->getFirstname();
                break;

            case ('last_name' == $placeholder_id && $recipient !== null):
                $resolved = $recipient->getLastname();
                break;

            case ('login' == $placeholder_id && $recipient !== null):
                $resolved = $recipient->getLogin();
                break;

            case ('title' == $placeholder_id && $recipient !== null):
                $resolved = $recipient->getUTitle();
                break;

            case 'ilias_url' == $placeholder_id:
                $resolved = ILIAS_HTTP_PATH . '/login.php?client_id=' . CLIENT_ID;
                break;

            case 'client_name' == $placeholder_id:
                $resolved = CLIENT_NAME;
                break;

            case !in_array($placeholder_id, array_keys(self::getGenericPlaceholders())):
                $resolved = $this->resolveSpecificPlaceholder($placeholder_id, $context_parameters, $recipient, $html_markup);
                break;
        }

        ilDatePresentation::setLanguage($old_lang);

        return $resolved;
    }

    /**
     * @param ilObjUser $user
     */
    protected function initLanguage(ilObjUser $user)
    {
        $this->initLanguageByIso2Code($user->getLanguage());
    }

    /**
     * Init language by ISO2 code
     * @param string $a_code
     */
    protected function initLanguageByIso2Code($a_code)
    {
        $this->language = ilLanguageFactory::_getLanguage($a_code);
        $this->language->loadLanguageModule('mail');
    }
}
