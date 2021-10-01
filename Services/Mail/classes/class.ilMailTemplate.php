<?php declare(strict_types=1);
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailTemplate
 * @author Nadia Ahmad <nahmad@databay.de>
 */
class ilMailTemplate
{
    protected int $templateId = 0;
    protected string $title = '';
    protected string $context = '';
    protected string $lang = '';
    protected string $subject = '';
    protected string $message = '';
    protected bool $isDefault = false;

    public function __construct(array $data = null)
    {
        if ($data) {
            $this->setTplId((int) $data['tpl_id']);
            $this->setTitle((string) $data['title']);
            $this->setContext((string) $data['context']);
            $this->setLang((string) $data['lang']);
            $this->setSubject((string) $data['m_subject']);
            $this->setMessage((string) $data['m_message']);
            $this->setAsDefault((bool) $data['is_default']);
        }
    }

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

    public function getTplId() : int
    {
        return $this->templateId;
    }

    public function setTplId(int $templateId) : void
    {
        $this->templateId = $templateId;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    public function setTitle(string $title) : void
    {
        $this->title = $title;
    }

    public function getContext() : string
    {
        return $this->context;
    }

    public function setContext(string $context) : void
    {
        $this->context = $context;
    }
    
    public function getLang() : string
    {
        return $this->lang;
    }

    public function setLang(string $lang) : void
    {
        $this->lang = $lang;
    }

    public function getSubject() : string
    {
        return $this->subject;
    }

    public function setSubject(string $subject) : void
    {
        $this->subject = $subject;
    }

    public function getMessage() : string
    {
        return $this->message;
    }

    public function setMessage(string $message) : void
    {
        $this->message = $message;
    }

    public function isDefault() : bool
    {
        return $this->isDefault;
    }

    public function setAsDefault(bool $isDefault) : void
    {
        $this->isDefault = $isDefault;
    }
}
