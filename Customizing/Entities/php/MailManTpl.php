<?php



/**
 * MailManTpl
 */
class MailManTpl
{
    /**
     * @var int
     */
    private $tplId = '0';

    /**
     * @var string
     */
    private $title = '';

    /**
     * @var string
     */
    private $context = '';

    /**
     * @var string
     */
    private $lang = '';

    /**
     * @var string|null
     */
    private $mSubject;

    /**
     * @var string|null
     */
    private $mMessage;

    /**
     * @var bool
     */
    private $isDefault = '0';


    /**
     * Get tplId.
     *
     * @return int
     */
    public function getTplId()
    {
        return $this->tplId;
    }

    /**
     * Set title.
     *
     * @param string $title
     *
     * @return MailManTpl
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set context.
     *
     * @param string $context
     *
     * @return MailManTpl
     */
    public function setContext($context)
    {
        $this->context = $context;

        return $this;
    }

    /**
     * Get context.
     *
     * @return string
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Set lang.
     *
     * @param string $lang
     *
     * @return MailManTpl
     */
    public function setLang($lang)
    {
        $this->lang = $lang;

        return $this;
    }

    /**
     * Get lang.
     *
     * @return string
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * Set mSubject.
     *
     * @param string|null $mSubject
     *
     * @return MailManTpl
     */
    public function setMSubject($mSubject = null)
    {
        $this->mSubject = $mSubject;

        return $this;
    }

    /**
     * Get mSubject.
     *
     * @return string|null
     */
    public function getMSubject()
    {
        return $this->mSubject;
    }

    /**
     * Set mMessage.
     *
     * @param string|null $mMessage
     *
     * @return MailManTpl
     */
    public function setMMessage($mMessage = null)
    {
        $this->mMessage = $mMessage;

        return $this;
    }

    /**
     * Get mMessage.
     *
     * @return string|null
     */
    public function getMMessage()
    {
        return $this->mMessage;
    }

    /**
     * Set isDefault.
     *
     * @param bool $isDefault
     *
     * @return MailManTpl
     */
    public function setIsDefault($isDefault)
    {
        $this->isDefault = $isDefault;

        return $this;
    }

    /**
     * Get isDefault.
     *
     * @return bool
     */
    public function getIsDefault()
    {
        return $this->isDefault;
    }
}
