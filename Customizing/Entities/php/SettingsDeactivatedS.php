<?php



/**
 * SettingsDeactivatedS
 */
class SettingsDeactivatedS
{
    /**
     * @var string
     */
    private $skin = ' ';

    /**
     * @var string
     */
    private $style = ' ';


    /**
     * Set skin.
     *
     * @param string $skin
     *
     * @return SettingsDeactivatedS
     */
    public function setSkin($skin)
    {
        $this->skin = $skin;

        return $this;
    }

    /**
     * Get skin.
     *
     * @return string
     */
    public function getSkin()
    {
        return $this->skin;
    }

    /**
     * Set style.
     *
     * @param string $style
     *
     * @return SettingsDeactivatedS
     */
    public function setStyle($style)
    {
        $this->style = $style;

        return $this;
    }

    /**
     * Get style.
     *
     * @return string
     */
    public function getStyle()
    {
        return $this->style;
    }
}
