<?php



/**
 * MailOptions
 */
class MailOptions
{
    /**
     * @var int
     */
    private $userId = '0';

    /**
     * @var bool
     */
    private $linebreak = '0';

    /**
     * @var string|null
     */
    private $signature;

    /**
     * @var bool|null
     */
    private $incomingType;

    /**
     * @var bool
     */
    private $cronjobNotification = '0';

    /**
     * @var bool
     */
    private $mailAddressOption = '3';


    /**
     * Get userId.
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set linebreak.
     *
     * @param bool $linebreak
     *
     * @return MailOptions
     */
    public function setLinebreak($linebreak)
    {
        $this->linebreak = $linebreak;

        return $this;
    }

    /**
     * Get linebreak.
     *
     * @return bool
     */
    public function getLinebreak()
    {
        return $this->linebreak;
    }

    /**
     * Set signature.
     *
     * @param string|null $signature
     *
     * @return MailOptions
     */
    public function setSignature($signature = null)
    {
        $this->signature = $signature;

        return $this;
    }

    /**
     * Get signature.
     *
     * @return string|null
     */
    public function getSignature()
    {
        return $this->signature;
    }

    /**
     * Set incomingType.
     *
     * @param bool|null $incomingType
     *
     * @return MailOptions
     */
    public function setIncomingType($incomingType = null)
    {
        $this->incomingType = $incomingType;

        return $this;
    }

    /**
     * Get incomingType.
     *
     * @return bool|null
     */
    public function getIncomingType()
    {
        return $this->incomingType;
    }

    /**
     * Set cronjobNotification.
     *
     * @param bool $cronjobNotification
     *
     * @return MailOptions
     */
    public function setCronjobNotification($cronjobNotification)
    {
        $this->cronjobNotification = $cronjobNotification;

        return $this;
    }

    /**
     * Get cronjobNotification.
     *
     * @return bool
     */
    public function getCronjobNotification()
    {
        return $this->cronjobNotification;
    }

    /**
     * Set mailAddressOption.
     *
     * @param bool $mailAddressOption
     *
     * @return MailOptions
     */
    public function setMailAddressOption($mailAddressOption)
    {
        $this->mailAddressOption = $mailAddressOption;

        return $this;
    }

    /**
     * Get mailAddressOption.
     *
     * @return bool
     */
    public function getMailAddressOption()
    {
        return $this->mailAddressOption;
    }
}
