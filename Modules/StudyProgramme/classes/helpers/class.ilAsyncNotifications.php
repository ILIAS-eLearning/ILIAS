<?php

/**
 * Class ilAsyncNotifications
 * Allows to display async notifications on a page
 *
 * @author Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilAsyncNotifications
{

    /**
     * @var bool Shows if the js is already added
     */
    protected $js_init;

    /**
     * @var string|null Id of the container to add the notifications
     */
    protected $content_container_id;

    /**
     * @var string Path to the js-path of the module
     */
    protected $js_path;

    /**
     * @var array JavaScript configuration for the jquery plugin
     */
    protected $js_config;


    public function __construct($content_container_id = null)
    {
        $this->js_init = false;
        $this->js_path = "./Modules/StudyProgramme/templates/js/";
        $this->content_container_id = ($content_container_id != null)? $content_container_id : "ilContentContainer";
    }


    /**
     * Setup the message templates and add the js onload code
     */
    public function initJs()
    {
        global $DIC;
        $tpl = $DIC['tpl'];

        if (!$this->js_init) {
            $tpl->addJavaScript($this->getJsPath() . 'ilStudyProgramme.js');

            $templates['info'] = $tpl->getMessageHTML("[MESSAGE]");
            $templates['success'] = $tpl->getMessageHTML("[MESSAGE]", 'success');
            $templates['failure'] = $tpl->getMessageHTML("[MESSAGE]", 'failure');
            $templates['question'] = $tpl->getMessageHTML("[MESSAGE]", 'question');

            $this->addJsConfig('templates', $templates);

            $tpl->addOnLoadCode("$('#" . $this->content_container_id . "').study_programme_notifications(" . json_encode($this->js_config) . ");");

            $this->js_init = true;
        }
    }


    /**
     * Returns the component (returns the js tag)
     */
    public function getHTML()
    {
        global $DIC;
        $tpl = $DIC['tpl'];

        $this->initJs();
    }

    /**
     * Gets the target container for the notification
     *
     * @return null|string
     */
    public function getContentContainerId()
    {
        return $this->content_container_id;
    }


    /**
     * Sets the target container for the notification
     *
     * @param null|string $content_container_id
     */
    public function setContentContainerId($content_container_id)
    {
        $this->content_container_id = $content_container_id;
    }


    /**
     * Return the path for the javascripts
     *
     * @return string
     */
    public function getJsPath()
    {
        return $this->js_path;
    }


    /**
     * Sets the path for the javascripts
     *
     * @param string $js_path
     */
    public function setJsPath($js_path)
    {
        $this->js_path = $js_path;
    }


    /**
     * Gets a setting of the jquery-plugin config
     *
     * @return mixed
     */
    public function getJsConfig($key)
    {
        return $this->js_config[$key];
    }


    /**
     * Sets Jquery settings for the plugin
     *
     * @param mixed $js_config
     */
    public function addJsConfig($key, $value)
    {
        $this->js_config[$key] = $value;
    }
}
