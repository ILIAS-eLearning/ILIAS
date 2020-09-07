<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailTemplate
 * @author Nadia Ahmad <nahmad@databay.de>
 */
class ilMailTemplate
{
    /** @var int */
    protected $templateId = 0;

    /** @var string */
    protected $title = '';

    /*** @var string */
    protected $context = '';

    /** @var string */
    protected $lang = '';

    /** @var string  */
    protected $subject = '';

    /** @var string */
    protected $message = '';

    /** @var bool */
    protected $isDefault = false;

    /**
     * @param array $data
     */
    public function __construct(array $data = null)
    {
        if (is_array($data)) {
            $this->setTplId((int) $data['tpl_id']);
            $this->setTitle((string) $data['title']);
            $this->setContext((string) $data['context']);
            $this->setLang((string) $data['lang']);
            $this->setSubject((string) $data['m_subject']);
            $this->setMessage((string) $data['m_message']);
            $this->setAsDefault((bool) $data['is_default']);
        }
    }

    /**
     * @return array
     */
    public function toArray() : array
    {
        return [
            'tpl_id' => $this->getTplId(),
            'title' => $this->getTitle(),
            'context' => $this->getContext(),
            'lang' => $this->getLang(),
            'm_subject' => $this->getSubject(),
            'm_message' => $this->getMessage(),
            'is_default' => $this->isDefault(),
        ];
    }

    /**
     * @return int
     */
    public function getTplId() : int
    {
        return $this->templateId;
    }

    /**
     * @param int $templateId
     */
    public function setTplId(int $templateId)
    {
        $this->templateId = $templateId;
    }

    /**
     * @return string
     */
    public function getTitle() : string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getContext() : string
    {
        return $this->context;
    }

    /**
     * @param string $context
     */
    public function setContext(string $context)
    {
        $this->context = $context;
    }

    /**
     * @return string
     */
    public function getLang() : string
    {
        return $this->lang;
    }

    /**
     * @param string $lang
     */
    public function setLang(string $lang)
    {
        $this->lang = $lang;
    }

    /**
     * @return string
     */
    public function getSubject() : string
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     */
    public function setSubject(string $subject)
    {
        $this->subject = $subject;
    }

    /**
     * @return string
     */
    public function getMessage() : string
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage(string $message)
    {
        $this->message = $message;
    }

    /**
     * @return bool
     */
    public function isDefault() : bool
    {
        return $this->isDefault;
    }

    /**
     * @param bool $isDefault
     */
    public function setAsDefault(bool $isDefault)
    {
        $this->isDefault = $isDefault;
    }
}
