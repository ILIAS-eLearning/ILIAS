<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

/**
 * Class ilAsyncNotifications
 * Allows displaying async notifications on a page
 *
 * @author Michael Herren <mh@studer-raimann.ch>
 */
class ilAsyncNotifications
{
    private const JS_PATH = "./assets/js/";

    /**
     * @var bool Shows if the js is already added
     */
    protected bool $js_init;

    /**
     * @var string|null Id of the container to add the notifications
     */
    protected ?string $content_container_id;

    /**
     * @var array JavaScript configuration for the jquery plugin
     */
    protected array $js_config;

    public function __construct(string $content_container_id = null)
    {
        $this->js_init = false;
        $this->content_container_id = $content_container_id ?? "ilContentContainer";
    }

    /**
     * Setup the message templates and add the js onload code
     */
    public function initJs(): void
    {
        global $DIC;
        $tpl = $DIC['tpl'];

        if (!$this->js_init) {
            $tpl->addJavaScript($this->getJsPath() . 'ilStudyProgramme.js');

            // TODO: DW -> refactor ilUtil
            $templates['info'] = ilUtil::getSystemMessageHTML("[MESSAGE]");
            $templates['success'] = ilUtil::getSystemMessageHTML("[MESSAGE]", 'success');
            $templates['failure'] = ilUtil::getSystemMessageHTML("[MESSAGE]", 'failure');
            $templates['question'] = ilUtil::getSystemMessageHTML("[MESSAGE]", 'question');

            $this->addJsConfig('templates', $templates);

            $tpl->addOnLoadCode(
                "$('#" .
                $this->content_container_id .
                "').study_programme_notifications(" .
                json_encode($this->js_config, JSON_THROW_ON_ERROR) .
                ");"
            );

            $this->js_init = true;
        }
    }

    /**
     * Returns the component (returns the js tag)
     */
    public function getHTML(): void
    {
        $this->initJs();
    }

    /**
     * Gets the target container for the notification
     */
    public function getContentContainerId(): ?string
    {
        return $this->content_container_id;
    }

    /**
     * Sets the target container for the notification
     */
    public function setContentContainerId(?string $content_container_id): void
    {
        $this->content_container_id = $content_container_id;
    }

    /**
     * Return the path for the java scripts
     */
    public function getJsPath(): string
    {
        return self::JS_PATH;
    }

    /**
     * Gets a setting of the jquery-plugin config
     *
     * @return mixed $key
     */
    public function getJsConfig($key)
    {
        return $this->js_config[$key];
    }

    /**
     * Sets Jquery settings for the plugin
     *
     * @param mixed $key
     * @param mixed $value
     */
    public function addJsConfig($key, $value): void
    {
        $this->js_config[$key] = $value;
    }
}
