<?php

use ILIAS\DI\Container;

/**
 * Class ilADNNotificationUIFormGUI
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilADNNotificationUIFormGUI
{

    const F_TITLE = 'title';
    const F_BODY = 'body';
    const F_TYPE = 'type';
    const F_TYPE_DURING_EVENT = 'type_during_event';
    const F_EVENT_DATE = 'event_date';
    const F_DISPLAY_DATE = 'display_date';
    const F_PERMANENT = 'permanent';
    const F_POSITION = 'position';
    const F_ADDITIONAL_CLASSES = 'additional_classes';
    const F_PREVENT_LOGIN = 'prevent_login';
    const F_INTERRUPTIVE = 'interruptive';
    const F_ALLOWED_USERS = 'allowed_users';
    const F_DISMISSABLE = 'dismissable';
    const F_LIMIT_TO_ROLES = 'limit_to_roles';
    const F_LIMITED_TO_ROLE_IDS = 'limited_to_role_ids';
    const F_DISPLAY_DATE_START = 'display_date_start';
    const F_DISPLAY_DATE_END = 'display_date_end';
    const F_EVENT_DATE_START = 'event_date_start';
    const F_EVENT_DATE_END = 'event_date_end';
    const F_SHOW_TO_ALL_ROLES = 'show_to_all_roles';
    const F_PRESENTATION = 'presentation';
    private $refinery;
    /**
     * @var ilADNNotification
     */
    protected $notification;
    /**
     * @var array
     */
    protected static $tags = array(
        'a',
        'strong',
        'ol',
        'ul',
        'li',
        'p',
    );

    /**
     * @var ilCtrl
     */
    protected $ctrl;
    /**
     * @var \ILIAS\UI\Factory
     */
    protected $ui;
    /**
     * @var \ILIAS\UI\Renderer
     */
    protected $renderer;
    /**
     * @var \ILIAS\UI\Component\Input\Container\Form\Standard
     */
    protected $form;
    /**
     * @var string
     */
    protected $action;
    /**
     * @var \Psr\Http\Message\RequestInterface|\Psr\Http\Message\ServerRequestInterface
     */
    protected $request;
    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * ilADNNotificationFormGUI constructor.
     * @param ilADNNotification $notification
     * @param string            $action
     */
    public function __construct(ilADNNotification $notification, string $action)
    {
        /**
         * @var $DIC Container
         */
        global $DIC;
        $this->ui = $DIC->ui()->factory();
        $this->renderer = $DIC->ui()->renderer();
        $this->request = $DIC->http()->request();
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->notification = $notification;
        $this->action = $action;
        $this->refinery = $DIC->refinery();
        $this->initForm();
    }

    protected $called = [];

    /**
     * @param string $var
     * @return string
     */
    protected function txt(string $var) : string
    {
        return $this->lng->txt('msg_' . $var);
    }

    /**
     * @param string $var
     * @return string
     */
    protected function infoTxt(string $var) : ?string
    {
        return $this->txt($var . '_info');
    }

    /**
     * @return string[]
     */
    protected function getDenotations() : array
    {
        return [
            ilADNNotification::TYPE_INFO => $this->txt(self::F_TYPE . '_' . ilADNNotification::TYPE_INFO),
            ilADNNotification::TYPE_WARNING => $this->txt(self::F_TYPE . '_' . ilADNNotification::TYPE_WARNING),
            ilADNNotification::TYPE_ERROR => $this->txt(self::F_TYPE . '_' . ilADNNotification::TYPE_ERROR),
        ];
    }

    public function getHTML() : string
    {
        return $this->renderer->render($this->form);
    }

    public function initForm() : void
    {
        $field = $this->ui->input()->field();
        $custom_trafo = function (callable $c) {
            return $this->refinery->custom()->transformation($c);
        };
        $custom_constraint = function (callable $c, string $error) {
            return $this->refinery->custom()->constraint($c, $error);
        };

        // DENOTATION
        $types = $this->getDenotations();
        $denotation = $field->select($this->txt(self::F_TYPE), $types, $this->infoTxt(self::F_TYPE))
                            ->withRequired(true)
                            ->withValue($this->notification->getType())
                            ->withAdditionalTransformation($custom_trafo(function ($v) {
                                $this->notification->setType((int) $v);
                            }));

        // TITLE
        $title = $field->text($this->txt(self::F_TITLE), $this->infoTxt(self::F_TITLE))
                       ->withRequired(true)
                       ->withValue($this->notification->getTitle())
                       ->withAdditionalTransformation($custom_trafo(function ($v) {
                           $this->notification->setTitle((string) $v);
                       }));

        // BODY
        $body = $field->textarea($this->txt(self::F_BODY), $this->infoTxt(self::F_BODY))
                      ->withValue($this->notification->getBody())
                      ->withAdditionalTransformation($custom_trafo(function ($v) {
                          $this->notification->setBody((string) $v);
                      }));

        // PERMANENT AND DATES
        $format = (new ILIAS\Data\Factory())->dateFormat()->standard();
        $str = $format->toString() . ' H:i:s';

        $display_date_start = $field->dateTime($this->txt(self::F_DISPLAY_DATE_START))
                                    ->withUseTime(true)
                                    ->withFormat($format)
                                    ->withValue($this->notification->getDisplayStart()->format($str))
                                    ->withAdditionalTransformation($custom_trafo(function (?DateTimeImmutable $v) {
                                        $this->notification->setDisplayStart($v ?? new DateTimeImmutable());
                                        return $v;
                                    }));
        $display_date_end = $field->dateTime($this->txt(self::F_DISPLAY_DATE_END))
                                  ->withUseTime(true)
                                  ->withFormat($format)
                                  ->withValue($this->notification->getDisplayEnd()->format($str))
                                  ->withAdditionalTransformation($custom_trafo(function (?DateTimeImmutable $v) {
                                      $this->notification->setDisplayEnd($v ?? new DateTimeImmutable());
                                      return $v;
                                  }));
        $event_date_start = $field->dateTime($this->txt(self::F_EVENT_DATE_START))
                                  ->withUseTime(true)
                                  ->withFormat($format)
                                  ->withValue($this->notification->getEventStart()->format($str))
                                  ->withAdditionalTransformation($custom_trafo(function (?DateTimeImmutable $v) {
                                      $this->notification->setEventStart($v ?? new DateTimeImmutable());
                                      return $v;
                                  }));
        $event_date_end = $field->dateTime($this->txt(self::F_EVENT_DATE_END))
                                ->withUseTime(true)
                                ->withFormat($format)
                                ->withValue($this->notification->getEventEnd()->format($str))
                                ->withAdditionalTransformation($custom_trafo(function (?DateTimeImmutable $v) {
                                    $this->notification->setEventEnd($v ?? new DateTimeImmutable());
                                    return $v;
                                }));

        $type_during_event = $field->select($this->txt(self::F_TYPE_DURING_EVENT), $types)
                                   ->withValue($this->notification->getTypeDuringEvent())
                                   ->withAdditionalTransformation($custom_trafo(function ($v) {
                                       $this->notification->setTypeDuringEvent((int) $v);
                                   }));

        $permanent = $field->switchableGroup([
            self::F_PERMANENT . '_yes' => $field->group([], $this->txt(self::F_PERMANENT . '_yes')),
            self::F_PERMANENT . '_no' => $field->group(
                [
                    self::F_DISPLAY_DATE_START => $display_date_start,
                    self::F_DISPLAY_DATE_END => $display_date_end,
                    self::F_EVENT_DATE_START => $event_date_start,
                    self::F_EVENT_DATE_END => $event_date_end,
                    self::F_TYPE_DURING_EVENT => $type_during_event
                ],
                $this->txt(self::F_PERMANENT . '_no')
            )
        ], $this->txt(self::F_PERMANENT), $this->infoTxt(self::F_PERMANENT))
                           ->withValue($this->notification->isPermanent() ? self::F_PERMANENT . '_yes' : self::F_PERMANENT . '_no')
                           ->withAdditionalTransformation($custom_trafo(function ($v) {
                               $permanent = isset($v[0]) && $v[0] === self::F_PERMANENT . '_yes';
                               $this->notification->setPermanent($permanent);
                               return $permanent ? null : $v[1];
                           }))
                           ->withAdditionalTransformation($custom_constraint(static function ($v) {
                               if (is_null($v)) {
                                   return true;
                               }
                               /**
                                * @var $v DateTimeImmutable[]
                                */
                               $display_start = $v[self::F_DISPLAY_DATE_START];
                               $display_end = $v[self::F_DISPLAY_DATE_END];
                               $event_start = $v[self::F_EVENT_DATE_START];
                               $event_end = $v[self::F_EVENT_DATE_END];

                               if ($display_start >= $display_end) {
                                   return false;
                               }
                               if ($event_start >= $event_end) {
                                   return false;
                               }
                               if ($event_start < $display_start) {
                                   return false;
                               }
                               if ($event_end > $display_end) {
                                   return false;
                               }

                               return true;
                           }, $this->txt('error_false_date_configuration')));

        // DISMISSABLE
        $dismissable = $field->checkbox($this->txt(self::F_DISMISSABLE), $this->infoTxt(self::F_DISMISSABLE))
                             ->withValue($this->notification->getDismissable())
                             ->withAdditionalTransformation($custom_trafo(function ($v) {
                                 $this->notification->setDismissable((bool) $v);
                             }));
    
        // LIMITED TO ROLES
        $available_roles = $this->getRoles(ilRbacReview::FILTER_ALL_GLOBAL);
        $limited_to_role_ids = $field->multiSelect('', $available_roles)
                                     ->withValue($this->notification->getLimitedToRoleIds())
                                     ->withAdditionalTransformation(
                                         $custom_trafo(function ($v) {
                                             $this->notification->setLimitedToRoleIds((array) $v);
                                         })
                                     );
    
        $value = $this->notification->isLimitToRoles()
            ? self::F_LIMIT_TO_ROLES
            : self::F_SHOW_TO_ALL_ROLES;
    
    
        $roles = $field->switchableGroup([
            self::F_SHOW_TO_ALL_ROLES => $field->group(
                [],
                $this->txt(self::F_SHOW_TO_ALL_ROLES)
            )->withByline($this->infoTxt(self::F_SHOW_TO_ALL_ROLES)),
            self::F_LIMIT_TO_ROLES => $field->group(
                [$limited_to_role_ids],
                $this->txt(self::F_LIMIT_TO_ROLES)
            )->withByline($this->infoTxt(self::F_LIMIT_TO_ROLES))
        ], $this->txt(self::F_PRESENTATION))
                       ->withValue($value)
                       ->withAdditionalTransformation(
                           $custom_trafo(function ($v) {
                               $this->notification->setLimitToRoles(($v[0] ?? null) === self::F_PRESENTATION);
                           })
                       );

        // COMPLETE FORM
        $section = $field->section([
            self::F_TYPE => $denotation,
            self::F_TITLE => $title,
            self::F_BODY => $body,
            self::F_PERMANENT => $permanent,
            self::F_DISMISSABLE => $dismissable,
            self::F_LIMIT_TO_ROLES => $roles,
        ], $this->txt('form_title'))->withAdditionalTransformation($custom_trafo(function ($v) : ilADNNotification {
            return $this->notification;
        }));;

        $this->form = $this->ui->input()->container()->form()->standard($this->action,
            [$section])->withAdditionalTransformation($this->refinery->custom()->transformation(function ($v) {
            return array_shift($v);
        }));
    }

    public function setValuesByPost() : void
    {
        global $DIC;
        $this->form = $this->form->withRequest($DIC->http()->request());
    }

    public function fillForm() : void
    {

    }

    /**
     * @return bool
     */
    protected function fillObject() : bool
    {
        $this->notification = $this->form->getData();
        return $this->notification instanceof ilADNNotification;
    }

    public function saveObject() : int
    {
        if (!$this->fillObject()) {
            return false;
        }
        if ($this->notification->getId() > 0) {
            $this->notification->update();
        } else {
            $this->notification->create();
        }

        return $this->notification->getId();
    }

    /**
     * @param $filter
     * @return array|int[]
     */
    protected function getRoles($filter) : array
    {
        global $DIC;
        $opt = [];
        foreach ($DIC->rbac()->review()->getRolesByFilter($filter) as $role) {
            $opt[$role['obj_id']] = $role['title'] . ' (' . $role['obj_id'] . ')';
        }

        return $opt;

    }
}
