<?php



/**
 * MailTemplate
 */
class MailTemplate
{
    /**
     * @var string
     */
    private $lang = ' ';

    /**
     * @var string
     */
    private $type = ' ';

    /**
     * @var string|null
     */
    private $subject;

    /**
     * @var string|null
     */
    private $body;

    /**
     * @var string|null
     */
    private $salF;

    /**
     * @var string|null
     */
    private $salM;

    /**
     * @var string|null
     */
    private $salG;

    /**
     * @var string|null
     */
    private $attFile;


    /**
     * Set lang.
     *
     * @param string $lang
     *
     * @return MailTemplate
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
     * Set type.
     *
     * @param string $type
     *
     * @return MailTemplate
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set subject.
     *
     * @param string|null $subject
     *
     * @return MailTemplate
     */
    public function setSubject($subject = null)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get subject.
     *
     * @return string|null
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set body.
     *
     * @param string|null $body
     *
     * @return MailTemplate
     */
    public function setBody($body = null)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Get body.
     *
     * @return string|null
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set salF.
     *
     * @param string|null $salF
     *
     * @return MailTemplate
     */
    public function setSalF($salF = null)
    {
        $this->salF = $salF;

        return $this;
    }

    /**
     * Get salF.
     *
     * @return string|null
     */
    public function getSalF()
    {
        return $this->salF;
    }

    /**
     * Set salM.
     *
     * @param string|null $salM
     *
     * @return MailTemplate
     */
    public function setSalM($salM = null)
    {
        $this->salM = $salM;

        return $this;
    }

    /**
     * Get salM.
     *
     * @return string|null
     */
    public function getSalM()
    {
        return $this->salM;
    }

    /**
     * Set salG.
     *
     * @param string|null $salG
     *
     * @return MailTemplate
     */
    public function setSalG($salG = null)
    {
        $this->salG = $salG;

        return $this;
    }

    /**
     * Get salG.
     *
     * @return string|null
     */
    public function getSalG()
    {
        return $this->salG;
    }

    /**
     * Set attFile.
     *
     * @param string|null $attFile
     *
     * @return MailTemplate
     */
    public function setAttFile($attFile = null)
    {
        $this->attFile = $attFile;

        return $this;
    }

    /**
     * Get attFile.
     *
     * @return string|null
     */
    public function getAttFile()
    {
        return $this->attFile;
    }
}
