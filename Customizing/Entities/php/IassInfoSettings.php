<?php



/**
 * IassInfoSettings
 */
class IassInfoSettings
{
    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var string|null
     */
    private $contact;

    /**
     * @var string|null
     */
    private $responsibility;

    /**
     * @var string|null
     */
    private $phone;

    /**
     * @var string|null
     */
    private $mails;

    /**
     * @var string|null
     */
    private $consultationHours;


    /**
     * Get objId.
     *
     * @return int
     */
    public function getObjId()
    {
        return $this->objId;
    }

    /**
     * Set contact.
     *
     * @param string|null $contact
     *
     * @return IassInfoSettings
     */
    public function setContact($contact = null)
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * Get contact.
     *
     * @return string|null
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * Set responsibility.
     *
     * @param string|null $responsibility
     *
     * @return IassInfoSettings
     */
    public function setResponsibility($responsibility = null)
    {
        $this->responsibility = $responsibility;

        return $this;
    }

    /**
     * Get responsibility.
     *
     * @return string|null
     */
    public function getResponsibility()
    {
        return $this->responsibility;
    }

    /**
     * Set phone.
     *
     * @param string|null $phone
     *
     * @return IassInfoSettings
     */
    public function setPhone($phone = null)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone.
     *
     * @return string|null
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set mails.
     *
     * @param string|null $mails
     *
     * @return IassInfoSettings
     */
    public function setMails($mails = null)
    {
        $this->mails = $mails;

        return $this;
    }

    /**
     * Get mails.
     *
     * @return string|null
     */
    public function getMails()
    {
        return $this->mails;
    }

    /**
     * Set consultationHours.
     *
     * @param string|null $consultationHours
     *
     * @return IassInfoSettings
     */
    public function setConsultationHours($consultationHours = null)
    {
        $this->consultationHours = $consultationHours;

        return $this;
    }

    /**
     * Get consultationHours.
     *
     * @return string|null
     */
    public function getConsultationHours()
    {
        return $this->consultationHours;
    }
}
