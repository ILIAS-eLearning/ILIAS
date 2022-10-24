<?php

declare(strict_types=1);

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

namespace ILIAS\Notifications\Model;

use ILIAS\Notifications\ilNotificationSystem;
use ilNotification;
use ilObjUser;

/**
 * @author Jan Posselt <jposselt@databay.de>
 */
class ilNotificationConfig
{
    public const TTL_LONG = 1800;
    public const TTL_SHORT = 120;
    public const DEFAULT_TTS = 5;

    private string $type;
    /**
     * @var ilNotificationLink[]
     */
    private array $links = [];
    private ilNotificationParameter $title;
    private string $iconPath;
    private ilNotificationParameter $short_description;
    private ilNotificationParameter $long_description;
    private bool $disableAfterDelivery = false;
    private int $validForSeconds = 0;
    protected int $visibleForSeconds = 0;
    private array $handlerParams = [];

    public function __construct(string $type)
    {
        $this->type = $type;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setAutoDisable(bool $value): void
    {
        $this->disableAfterDelivery = $value;
    }

    public function hasDisableAfterDeliverySet(): bool
    {
        return $this->disableAfterDelivery;
    }

    /**
     * @param ilNotificationLink[] $links
     */
    public function setLinks(array $links): void
    {
        $this->links = $links;
    }

    /**
     * @return ilNotificationLink[]
     */
    public function getLinks(): array
    {
        return $this->links;
    }

    public function setIconPath(string $path): void
    {
        $this->iconPath = $path;
    }

    public function getIconPath(): ?string
    {
        return $this->iconPath;
    }

    /**
     * @param string[]  $parameters
     */
    public function setTitleVar(string $name, array $parameters = [], string $language_module = 'notification'): void
    {
        $this->title = new ilNotificationParameter($name, $parameters, $language_module);
    }

    public function getTitleVar(): string
    {
        return $this->title->getName();
    }

    /**
     * @param string[]  $parameters
     */
    public function setShortDescriptionVar(string $name, array $parameters = [], string $language_module = 'notification'): void
    {
        $this->short_description = new ilNotificationParameter($name, $parameters, $language_module);
    }

    public function getShortDescriptionVar(): string
    {
        return $this->short_description->getName();
    }

    /**
     * @param string[]  $parameters
     */
    public function setLongDescriptionVar(string $name, array $parameters = [], string $language_module = 'notification'): void
    {
        $this->long_description = new ilNotificationParameter($name, $parameters, $language_module);
    }

    public function getLongDescriptionVar(): string
    {
        return $this->long_description->getName();
    }

    /**
     * @return array<string, ilNotificationParameter>
     */
    public function getLanguageParameters(): array
    {
        $params = [
            'title' => $this->title,
            'longDescription' => $this->long_description,
            'shortDescription' => $this->short_description,
        ];

        foreach ($this->links as $id => $link) {
            $params['link_' . $id] = $link->getTitle();
        }

        return $params;
    }

    public function setValidForSeconds(int $seconds): void
    {
        $this->validForSeconds = $seconds;
    }

    public function getValidForSeconds(): int
    {
        return $this->validForSeconds;
    }

    public function getVisibleForSeconds(): int
    {
        return $this->visibleForSeconds;
    }

    public function setVisibleForSeconds(int $visibleForSeconds): void
    {
        $this->visibleForSeconds = $visibleForSeconds;
    }

    protected function beforeSendToUsers(): void
    {
    }

    protected function afterSendToUsers(): void
    {
    }

    protected function beforeSendToListeners(): void
    {
    }

    protected function afterSendToListeners(): void
    {
    }

    /**
     * @param int[] $recipients
     */
    final public function notifyByUsers(array $recipients, bool $processAsync = false): void
    {
        $this->beforeSendToUsers();
        ilNotificationSystem::sendNotificationToUsers($this, $recipients, $processAsync);
        $this->afterSendToUsers();
    }

    final public function notifyByListeners(int $ref_id, $processAsync = false): void
    {
        $this->beforeSendToListeners();
        ilNotificationSystem::sendNotificationToListeners($this, $ref_id, $processAsync);
        $this->afterSendToListeners();
    }

    /**
     * @param string[] $roles
     */
    final public function notifyByRoles(array $roles, bool $processAsync = false): void
    {
        ilNotificationSystem::sendNotificationToRoles($this, $roles, $processAsync);
    }

    public function getUserInstance(ilObjUser $user, array $languageVars, string $defaultLanguage): ilNotificationObject
    {
        $notificationObject = new ilNotificationObject($this, $user);

        $title = $this->title->getName();
        if (isset($languageVars[$this->title->getName()])) {
            $var = $languageVars[$this->title->getName()]->lang;
            if (isset($var[$user->getLanguage()])) {
                $title = $var[$user->getLanguage()];
            } elseif (isset($var[$defaultLanguage])) {
                $title = $var[$defaultLanguage];
            }
        }
        $notificationObject->title = $title;

        $short = $this->short_description->getName();
        if (isset($languageVars[$this->short_description->getName()])) {
            $var = $languageVars[$this->short_description->getName()]->lang;
            if (isset($var[$user->getLanguage()])) {
                $short = $var[$user->getLanguage()];
            } elseif (isset($var[$defaultLanguage])) {
                $short = $var[$defaultLanguage];
            }
        }
        $notificationObject->shortDescription = $short;

        $long = $this->long_description->getName();
        if (isset($languageVars[$this->long_description->getName()])) {
            $var = $languageVars[$this->long_description->getName()]->lang;
            if (isset($var[$user->getLanguage()])) {
                $long = $var[$user->getLanguage()];
            } elseif (isset($var[$defaultLanguage])) {
                $long = $var[$defaultLanguage];
            }
        }
        $notificationObject->longDescription = $long;

        $process_links = [];
        foreach ($this->links as $link) {
            $link_title = $link->getTitle()->getName();
            if (isset($languageVars[$link->getTitle()->getName()])) {
                $var = $languageVars[$link->getTitle()->getName()]->lang;
                if (isset($var[$user->getLanguage()])) {
                    $link_title = $var[$user->getLanguage()];
                } elseif (isset($var[$defaultLanguage])) {
                    $link_title = $var[$defaultLanguage];
                }
            }

            $process_link = clone $link;
            $process_link->setTitle($link_title);
            $process_links[] = $process_link;
        }
        $notificationObject->links = $process_links;

        $notificationObject->iconPath = $this->iconPath;

        return $notificationObject;
    }

    public function setHandlerParam(string $name, string $value): void
    {
        if (strpos($name, '.')) {
            $nsParts = explode('.', $name, 2);
            $ns = $nsParts[0];
            $field = $nsParts[1];
            $this->handlerParams[$ns][$field] = $value;
        } else {
            $this->handlerParams[''][$name] = $value;
        }
    }

    public function getHandlerParams(): array
    {
        return $this->handlerParams;
    }

    public function unsetHandlerParam(string $name): void
    {
        unset($this->handlerParams[$name]);
    }
}
