<?php

use ILIAS\DI\Container;
use ILIAS\Membership\Changelog\ChangelogService;
use ILIAS\Membership\Changelog\Infrastructure\Repository\ilDBEventRepository;
use ILIAS\Membership\Changelog\Query\EventDTO;
use ILIAS\Membership\Changelog\Query\Filter;
use ILIAS\Membership\Changelog\UI\MembershipHistoryTableGUI;

/**
 * Class ChangelogTableGUI
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class MembershipHistoryCourseTableGUI extends MembershipHistoryTableGUI
{

    const F_MEMBER_LOGIN = 'member_login';
    const F_ACTOR_LOGIN = 'actor_login';
    const F_DATE_RANGE = 'date_range';
    const F_DATE_START = 'date_start';
    const F_DATE_END = 'date_end';

    /**
     * @var int
     */
    protected $course_obj_id;
    /**
     * @var array
     */
    protected $filter = [];


    /**
     * MembershipHistoryCourseTableGUI constructor.
     *
     * @param              $a_parent_obj
     * @param Container    $dic
     * @param TableOptions $options
     * @param int          $course_obj_id
     */
    public function __construct($a_parent_obj, Container $dic, TableOptions $options, int $course_obj_id)
    {
        $this->course_obj_id = $course_obj_id;
        $this->setDefaultOrderField('timestamp');
        parent::__construct($a_parent_obj, $dic, $options);
    }


    /**
     *
     */
    protected function initColumns() : void
    {
        $this->addColumn($this->dic->language()->txt('col_event_name'), 'event_name');
        $this->addColumn($this->dic->language()->txt('col_member'), 'usr_data_2_lastname');
        $this->addColumn($this->dic->language()->txt('col_member_login'), 'usr_data_2_login');
        $this->addColumn($this->dic->language()->txt('col_actor'), 'usr_data_lastname');
        $this->addColumn($this->dic->language()->txt('col_actor_login'), 'usr_data_login');
        $this->addColumn($this->dic->language()->txt('col_occurred_at'), 'timestamp');
    }

    /**
     *
     */
    protected function initData() : void
    {
        $changelog_service = new ChangelogService(new ilDBEventRepository());
        $filter = $changelog_service->queryFactory()->filter()->withSubjectObjIds([$this->course_obj_id]);
        $filter = $this->applyFilters($filter);

        $response = $changelog_service->query(
            $filter,
            $changelog_service->queryFactory()->options()
                ->withOrderField($this->getOrderField() ?: $this->getDefaultOrderField())
                ->withOrderDirection($this->getOrderDirection() ?: $this->getDefaultOrderDirection())
                ->withLimit($this->getLimit())
                ->withOffset($this->getOffset())
        );

        $this->setMaxCount($response->getMaxCount());
        $this->setData($response->getEvents());
    }


    /**
     * @param EventDTO $event_dto
     */
    protected function fillRow($event_dto)
    {
        $this->parseColumnValue($this->dic->language()->txt('event_' . $event_dto->getEventName()));
        $this->parseColumnValue($event_dto->getSubjectUserName());
        $this->parseColumnValue(ilObjUser::_lookupLogin($event_dto->getSubjectUserId()));
        $this->parseColumnValue($event_dto->getActorUserName());
        $this->parseColumnValue(ilObjUser::_lookupLogin($event_dto->getActorUserId()));
        $this->parseColumnValue(date('d.m.Y H:i:s', $event_dto->getTimestamp()));
    }


    /**
     *
     */
    function initFilter()
    {
        $ajax_url = $this->dic->ctrl()->getLinkTargetByClass(
            array(ilCourseMembershipGUI::class, ilRepositorySearchGUI::class),
            'doUserAutoComplete',
            '',
            true,
            false
        );

        // member
        $subject_user_login = new ilTextInputGUI($this->dic->language()->txt('member'), self::F_MEMBER_LOGIN);
        $subject_user_login->setDataSource($ajax_url);
        $subject_user_login->setSize(15);
        $this->addFilterItem($subject_user_login);
        $subject_user_login->readFromSession();
        $this->filter[self::F_MEMBER_LOGIN] = $subject_user_login->getValue();

        // actor
        $actor_user_login = new ilTextInputGUI($this->dic->language()->txt('col_actor'), self::F_ACTOR_LOGIN);
        $actor_user_login->setDataSource($ajax_url);
        $actor_user_login->setSize(15);
        $this->addFilterItem($actor_user_login);
        $actor_user_login->readFromSession();
        $this->filter[self::F_ACTOR_LOGIN] = $actor_user_login->getValue();

        // date range
        $date_range = $this->addFilterItemByMetaType(self::F_DATE_RANGE, self::FILTER_DATE_RANGE, false, $this->dic->language()->txt('date'));
        $this->filter[self::F_DATE_START] = $date_range->getValue()['from'];
        $this->filter[self::F_DATE_END] = $date_range->getValue()['to'];
    }


    /**
     * @param Filter $filter
     *
     * @return Filter
     */
    protected function applyFilters(Filter $filter) : Filter
    {
        $member_login = $this->filter[self::F_MEMBER_LOGIN];
        if (is_string($member_login) && $member_login !== '') {
            $filter = $filter->withSubjectLogins([$member_login]);
        }
        $actor_login = $this->filter[self::F_ACTOR_LOGIN];
        if (is_string($actor_login) && $actor_login !== '') {
            $filter = $filter->withActorLogins([$actor_login]);
        }
        $date_start = $this->filter[self::F_DATE_START];
        if ($date_start instanceof ilDate) {
            $filter = $filter->withTimestampFrom($date_start->getUnixTime());
        }
        $date_end = $this->filter[self::F_DATE_END];
        if ($date_end instanceof ilDate) {
            $filter = $filter->withTimestampTo($date_end->getUnixTime());
        }
        return $filter;
    }
}