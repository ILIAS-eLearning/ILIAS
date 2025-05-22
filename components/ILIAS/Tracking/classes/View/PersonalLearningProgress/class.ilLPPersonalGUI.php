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

declare(strict_types=0);

use ILIAS\DI\UIServices;
use ILIAS\Refinery\Factory as RefineryFactory;
use ILIAS\Tracking\View\FactoryInterface as ViewFactoryInterface;
use ILIAS\UI\Component\Symbol\Icon\Icon as UIIconIcon;
use ILIAS\UI\Component\Symbol\Icon\Standard as UIStandardIcon;
use ILIAS\HTTP\Services as HTTPServices;
use ILIAS\Tracking\View\Factory as ViewFactory;
use ILIAS\UI\URLBuilder;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\UI\Component\Item\Standard as UIStandardItem;
use ILIAS\StaticURL\Services as StaticURL;
use ILIAS\Data\URI as URI;

/**
 * @ilCtrl_IsCalledBy ilLPPersonalGUI: ilDashboardGUI
 */
class ilLPPersonalGUI
{
    protected const PRESENTATION_OPTION_CURRENT = "current";
    protected const PRESENTATION_OPTION_FUTURE = "future";
    protected const PRESENTATION_OPTION_PAST = "past";
    protected const PRESENTATION_OPTION_ALL = "all";
    protected const URL_VAR_MODE = "viewcontrol_plp_mode";
    protected const URL_VAR_ACTION_MODE = "mode";
    protected const URL_NAMESPACE_PLP = "plp";
    protected const URL_NAMESPACE_VIEWCONTROL = "viewcontrol";
    protected const LNG_VAR_PRESENTATION_OPTION_CURRENT = "view_mode_current";
    protected const LNG_VAR_PRESENTATION_OPTION_FUTURE = "view_mode_future";
    protected const LNG_VAR_PRESENTATION_OPTION_PAST = "view_mode_past";
    protected const LNG_VAR_PRESENTATION_OPTION_ALL = "view_mode_all";
    protected const LNG_VAR_PROPERTY_CRS_START = "trac_begin_at";
    protected const LNG_VAR_PROPERTY_CRS_END = "trac_end_at";
    protected const LNG_VAR_PROPERTY_CRS_ONLINE = "online";
    protected const LNG_VAR_PROPERTY_CRS_ONLINE_YES = "yes";
    protected const LNG_VAR_PROPERTY_CRS_ONLINE_NO = "no";
    protected const LNG_VAR_LISTING_TITLE = "courses";
    protected HTTPServices $http;
    protected UIServices $ui;
    protected ilCtrl $ctrl;
    protected ilObjUser $user;
    protected ilLanguage $lng;
    protected RefineryFactory $refinery;
    protected DataFactory $data_factory;
    protected ilAccessHandler $access;
    protected StaticURL $static_url;
    protected ViewFactoryInterface $tracking_view;

    public function __construct()
    {
        global $DIC;

        $this->ui = $DIC->ui();
        $this->ctrl = $DIC->ctrl();
        $this->user = $DIC->user();
        $this->lng = $DIC->language();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->access = $DIC->access();
        $this->data_factory = new DataFactory();
        $this->static_url = $DIC['static_url'];
        $this->tracking_view = new ViewFactory();
        $this->lng->loadLanguageModule("trac");
    }

    public function executeCommand(): void
    {
        $this->listCourses();
    }

    protected function getCurrentPresentationModeFromQuery(): string
    {
        if ($this->http->wrapper()->query()->has(self::URL_VAR_MODE)) {
            return $this->http->wrapper()->query()->retrieve(
                self::URL_VAR_MODE,
                $this->refinery->kindlyTo()->string()
            );
        }
        return self::PRESENTATION_OPTION_CURRENT;
    }

    protected function buildViewControls(): array
    {
        $current_presentation = $this->getCurrentPresentationModeFromQuery();
        $presentation_options = [
            self::PRESENTATION_OPTION_CURRENT => $this->lng->txt(self::LNG_VAR_PRESENTATION_OPTION_CURRENT),
            self::PRESENTATION_OPTION_FUTURE => $this->lng->txt(self::LNG_VAR_PRESENTATION_OPTION_FUTURE),
            self::PRESENTATION_OPTION_PAST => $this->lng->txt(self::LNG_VAR_PRESENTATION_OPTION_PAST),
            self::PRESENTATION_OPTION_ALL => $this->lng->txt(self::LNG_VAR_PRESENTATION_OPTION_ALL),
        ];
        $uri = $this->http->request()->getUri()->__toString();
        $url_builder = new URLBuilder($this->data_factory->uri($uri));
        list($url_builder, $action_parameter_token) =
            $url_builder->acquireParameters(
                [self::URL_NAMESPACE_VIEWCONTROL, self::URL_NAMESPACE_PLP],
                self::URL_VAR_ACTION_MODE
            );
        $modes = $this->ui->factory()->viewControl()->mode(
            [
                $presentation_options[self::PRESENTATION_OPTION_CURRENT] => (string) $url_builder->withParameter($action_parameter_token, self::PRESENTATION_OPTION_CURRENT)->buildURI(),
                $presentation_options[self::PRESENTATION_OPTION_FUTURE] => (string) $url_builder->withParameter($action_parameter_token, self::PRESENTATION_OPTION_FUTURE)->buildURI(),
                $presentation_options[self::PRESENTATION_OPTION_PAST] => (string) $url_builder->withParameter($action_parameter_token, self::PRESENTATION_OPTION_PAST)->buildURI(),
                $presentation_options[self::PRESENTATION_OPTION_ALL] => (string) $url_builder->withParameter($action_parameter_token, self::PRESENTATION_OPTION_ALL)->buildURI(),
            ],
            'Presentation Mode'
        )
            ->withActive($presentation_options[$current_presentation]);
        return [
            $modes
        ];
    }

    /**
     * @return UIStandardItem[]
     */
    protected function buildPanelItems(
        string $presentation_mode
    ): array {
        $ids = ilParticipants::_getMembershipByType($this->user->getId(), ["crs"], true);
        $ids_with_lp = [];
        $filter = $this->tracking_view->dataRetrieval()->filter()
            ->withUserIds($this->user->getId())
            ->withObjectIds(...$ids)
            ->withOnlyDataOfObjectWithLPEnabled(false);
        $view_info = $this->tracking_view->dataRetrieval()->service()->retrieveViewInfo($filter);
        $items = [];
        foreach ($view_info->combinedInfoIterator() as $combinedInfo) {
            $obj_id = $combinedInfo->getObjectInfo()->getObjectId();
            $ids_with_lp[] = $obj_id;
            /** @var ilObjCourse $crs */
            $crs = ilObjectFactory::getInstanceByObjId($obj_id);
            if (
                !$this->hasReadAccess($obj_id) ||
                !$this->isPresentable($presentation_mode, $crs->getCourseStart(), $crs->getCourseEnd())
            ) {
                continue;
            }
            $offline_str = $crs->getOfflineStatus()
                ? $this->lng->txt(self::LNG_VAR_PROPERTY_CRS_ONLINE_NO)
                : $this->lng->txt(self::LNG_VAR_PROPERTY_CRS_ONLINE_YES);
            $property_builder = $this->tracking_view->propertyList()->builder();
            if (!is_null($crs->getCourseStart())) {
                $crs_start = ilDatePresentation::formatDate($crs->getCourseStart());
                $property_builder = $property_builder
                    ->withProperty($this->lng->txt(self::LNG_VAR_PROPERTY_CRS_START), $crs_start);
            }
            if (!is_null($crs->getCourseEnd())) {
                $crs_end = ilDatePresentation::formatDate($crs->getCourseEnd());
                $property_builder = $property_builder
                    ->withProperty($this->lng->txt(self::LNG_VAR_PROPERTY_CRS_END), $crs_end);
            }
            $property_builder = $property_builder
                ->withProperty($this->lng->txt(self::LNG_VAR_PROPERTY_CRS_ONLINE), $offline_str);
            $item = $this->tracking_view->renderer()->service()->standardItem(
                $combinedInfo->getObjectInfo(),
                $property_builder->getList(),
                $this->buildLinkToCourse($obj_id)
            );
            if (
                $combinedInfo->getLPInfo()->getLPMode() !== ilLPObjSettings::LP_MODE_UNDEFINED &&
                $combinedInfo->getLPInfo()->getLPMode() !== ilLPObjSettings::LP_MODE_DEACTIVATED
            ) {
                $progress_chart = $this->tracking_view->renderer()->service()->standardProgressMeter($combinedInfo->getLPInfo());
                $item = $item->withProgress($progress_chart);
            }
            $items[$obj_id] = $item;
        }
        return $items;
    }

    protected function isPresentable(
        string $presentation_mode,
        ilDateTime|null $crs_start,
        ilDateTime|null $crs_end
    ): bool {
        $now = new ilDateTime(time(), IL_CAL_UNIX);
        $crs_start = (is_null($crs_start) || $crs_start->isNull()) ? $now : $crs_start;
        $crs_end = (is_null($crs_end) || $crs_end->isNull()) ? $now : $crs_end;
        if ($presentation_mode === self::PRESENTATION_OPTION_ALL) {
            return true;
        }
        // courses without end are never in the past
        if (
            $presentation_mode === self::PRESENTATION_OPTION_PAST &&
            ilDateTime::_after($now, $crs_end)
        ) {
            return true;
        }
        // courses without start are never in the future
        if (
            $presentation_mode === self::PRESENTATION_OPTION_FUTURE &&
            ilDateTime::_before($now, $crs_start)
        ) {
            return true;
        }
        // courses without start and end are always current
        // courses without start or end are current if their end/start is not in the past/future
        if (
            $presentation_mode === self::PRESENTATION_OPTION_CURRENT &&
            ilDateTime::_within($now, $crs_start, $crs_end)
        ) {
            return true;
        }
        return false;
    }

    protected function listCourses(): void
    {
        $view_controls = $this->buildViewControls();
        $items = $this->buildPanelItems($this->getCurrentPresentationModeFromQuery());
        $crs_item_group = $this->ui->factory()->item()->group("", $items);
        $ui_panel = $this->ui->factory()->panel()->listing()->standard(
            $this->lng->txt(self::LNG_VAR_LISTING_TITLE),
            [
                $crs_item_group
            ]
        )
            ->withViewControls($view_controls);
        $this->ui->mainTemplate()->setContent($this->ui->renderer()->render([$ui_panel]));
    }

    protected function hasReadAccess(int $obj_id): bool
    {
        foreach (ilObject::_getAllReferences($obj_id) as $ref_id) {
            if ($this->access->checkAccess("read", "", $ref_id)) {
                return true;
            }
        }
        return false;
    }

    protected function buildLinkToCourse(int $obj_id): ?URI
    {
        $ref_ids = ilObject::_getAllReferences($obj_id);
        if (count($ref_ids) === 0) {
            return null;
        }
        return $this->static_url->builder()->build(
            'crs',
            $this->data_factory->refId((int) current($ref_ids))
        );
    }
}
