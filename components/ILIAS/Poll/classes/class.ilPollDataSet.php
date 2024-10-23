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

use ILIAS\Poll\Image\I\FactoryInterface as PollImageFactoryInterface;
use ILIAS\Poll\Image\Factory as PollImageFactory;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Notes\Service as NotesService;

/**
 * Poll Dataset class
 *
 * This class implements the following entities:
 * - poll: object data
 * - poll_answer: data from table il_poll_answer
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilPollDataSet extends ilDataSet
{
    protected const ENTITY = "poll";
    protected NotesService $notes;
    protected DataFactory $data_factory;
    protected ilObjuser $user;
    protected PollImageFactoryInterface $poll_image_factory;

    public function __construct()
    {
        global $DIC;

        parent::__construct();
        $this->notes = $DIC->notes();
        $this->data_factory = new DataFactory();
        $this->user = $DIC->user();
        $this->poll_image_factory = new PollImageFactory();
    }

    public function getSupportedVersions(): array
    {
        return array("4.3.0", "5.0.0", "10.0");
    }

    protected function getXmlNamespace(string $a_entity, string $a_schema_version): string
    {
        return "http://www.ilias.de/xml/Modules/Poll/" . $a_entity;
    }

    /**
     * @inheritdoc
     */
    protected function getTypes(string $a_entity, string $a_version): array
    {
        if ($a_entity === self::ENTITY) {
            switch ($a_version) {
                case "4.3.0":
                    return array(
                        "Id" => "integer",
                        "Title" => "text",
                        "Description" => "text",
                        "Question" => "text",
                        "Image" => "text",
                        "ViewResults" => "integer",
                        "Dir" => "directory"
                        );
                    break;
                case "5.0.0":
                    return array(
                        "Id" => "integer",
                        "Title" => "text",
                        "Description" => "text",
                        "Question" => "text",
                        "Image" => "text",
                        "ViewResults" => "integer",
                        "Dir" => "directory",
                        "ShowResultsAs" => "integer",
                        "ShowComments" => "integer",
                        "MaxAnswers" => "integer",
                        "ResultSort" => "integer",
                        "NonAnon" => "integer",
                        "Period" => "integer",
                        "PeriodBegin" => "integer",
                        "PeriodEnd" => "integer"

                    );
                    break;
                case "10.0":
                    return array(
                        "Id" => "integer",
                        "Title" => "text",
                        "Description" => "text",
                        "Question" => "text",
                        "Image" => "text", // Image now contains the full path, not just the name
                        "ViewResults" => "integer",
                        "ShowResultsAs" => "integer",
                        "ShowComments" => "integer",
                        "MaxAnswers" => "integer",
                        "ResultSort" => "integer",
                        "NonAnon" => "integer",
                        "Period" => "integer",
                        "PeriodBegin" => "integer",
                        "PeriodEnd" => "integer"
                    );
                    break;
            }
        }

        if ($a_entity === "poll_answer") {
            switch ($a_version) {
                case "4.3.0":
                case "5.0.0":
                case "10.0":
                    return array(
                        "Id" => "integer",
                        "PollId" => "integer",
                        "Answer" => "text",
                        "Pos" => "integer",
                    );
                    break;
            }
        }

        return [];
    }

    public function readData(string $a_entity, string $a_version, array $a_ids): void
    {
        $ilDB = $this->db;

        if ($a_entity === self::ENTITY) {
            switch ($a_version) {
                case "4.3.0":
                    $this->getDirectDataFromQuery("SELECT pl.id,od.title,od.description," .
                        "pl.question,pl.image,pl.view_results" .
                        " FROM il_poll pl" .
                        " JOIN object_data od ON (od.obj_id = pl.id)" .
                        " WHERE " . $ilDB->in("pl.id", $a_ids, false, "integer") .
                        " AND od.type = " . $ilDB->quote("poll", "text"));
                    break;
                case "5.0.0":
                    $this->getDirectDataFromQuery("SELECT pl.id,od.title,od.description" .
                        ",pl.question,pl.image,pl.view_results,pl.show_results_as" .
                        ",pl.max_answers,pl.result_sort,pl.non_anon,pl.period,pl.period_begin,pl.period_end" .
                        " FROM il_poll pl" .
                        " JOIN object_data od ON (od.obj_id = pl.id)" .
                        " WHERE " . $ilDB->in("pl.id", $a_ids, false, "integer") .
                        " AND od.type = " . $ilDB->quote("poll", "text"));
                    break;
                case "10.0":
                    $this->getDirectDataFromQuery("SELECT pl.id,od.title,od.description" .
                        ",pl.question,pl.view_results,pl.show_results_as" .
                        ",pl.max_answers,pl.result_sort,pl.non_anon,pl.period,pl.period_begin,pl.period_end" .
                        " FROM il_poll pl" .
                        " JOIN object_data od ON (od.obj_id = pl.id)" .
                        " WHERE " . $ilDB->in("pl.id", $a_ids, false, "integer") .
                        " AND od.type = " . $ilDB->quote("poll", "text"));
                    break;
            }
        }

        if ($a_entity === "poll_answer") {
            switch ($a_version) {
                case "4.3.0":
                case "5.0.0":
                case "10.0":
                    $this->getDirectDataFromQuery("SELECT id,poll_id,answer,pos" .
                        " FROM il_poll_answer WHERE " .
                        $ilDB->in("poll_id", $a_ids, false, "integer"));
                    break;
            }
        }
    }

    protected function getDependencies(
        string $a_entity,
        string $a_version,
        ?array $a_rec = null,
        ?array $a_ids = null
    ): array {
        switch ($a_entity) {
            case "poll":
                return array(
                    "poll_answer" => array("ids" => $a_rec["Id"] ?? null)
                );
        }
        return [];
    }

    public function getXmlRecord(string $a_entity, string $a_version, array $a_set): array
    {
        if ($a_entity === self::ENTITY) {
            $resource = $this->poll_image_factory->handler()->getRessource(
                $this->data_factory->objId((int) $a_set["Id"])
            );
            if ($resource !== null) {
                $title = $resource->getTitle();
                $path_in_container = ltrim($this->export->getExportDirInContainer(), '/') . '/image/' . $title;
                $this->export->getExportWriter()->writeFilesByResourceId(
                    $resource->getIdentification()->serialize(),
                    $path_in_container
                );
                /*
                 * For some reason, the name of the import file itself is included in getImportDirectory,
                 * so has to be removed here. Might need to take this out again if anything changes.
                 */
                $path_to_image = explode('/', $path_in_container);
                unset($path_to_image[0]);
                $path_to_image = implode('/', $path_to_image);
                $a_set["Image"] = $path_to_image;
            } else {
                $a_set["Image"] = '';
            }

            $a_set["ShowComments"] = $this->notes->domain()->commentsActive((int) $a_set["Id"]);
        }
        return $a_set;
    }

    public function importRecord(
        string $a_entity,
        array $a_types,
        array $a_rec,
        ilImportMapping $a_mapping,
        string $a_schema_version
    ): void {
        $a_rec = $this->stripTags(
            $a_rec,
            [
                'Id',
                'MaxAnswers',
                'ResultSort',
                'NonAnon',
                'ShowResultsAs',
                'ShowComments',
                'ViewResults',
                'Period',
                'PeriodBegin',
                'PeriodEnd',
                'PollId',
                'pos',
            ]
        );

        switch ($a_entity) {
            case "poll":
                // container copy
                if ($new_id = $a_mapping->getMapping("components/ILIAS/Container", "objs", (string) ($a_rec["Id"] ?? "0"))) {
                    $newObj = ilObjectFactory::getInstanceByObjId((int) $new_id, false);
                } else {
                    $newObj = new ilObjPoll();
                    $newObj->create();
                }

                /** @var ilObjPoll $newObj */
                $newObj->setTitle((string) ($a_rec["Title"] ?? ''));
                $newObj->setDescription((string) ($a_rec["Description"]));
                if ((int) $a_rec["MaxAnswers"]) {
                    $newObj->setMaxNumberOfAnswers((int) $a_rec["MaxAnswers"]);
                }
                $newObj->setSortResultByVotes((bool) ($a_rec["ResultSort"] ?? false));
                $newObj->setNonAnonymous((bool) ($a_rec["NonAnon"] ?? false));
                if ((int) $a_rec["ShowResultsAs"]) {
                    $newObj->setShowResultsAs((int) $a_rec["ShowResultsAs"]);
                }
                $newObj->setShowComments((bool) ($a_rec["ShowComments"] ?? false));
                $newObj->setQuestion((string) ($a_rec["Question"] ?? ''));
                $newObj->setViewResults((int) ($a_rec["ViewResults"] ?? ilObjPoll::VIEW_RESULTS_AFTER_VOTE));
                $newObj->setVotingPeriod((bool) ($a_rec["Period"] ?? 0));
                $newObj->setVotingPeriodBegin((int) ($a_rec["PeriodBegin"] ?? 0));
                $newObj->setVotingPeriodEnd((int) ($a_rec["PeriodEnd"] ?? 0));
                $newObj->update();

                // handle image(s)
                if ($a_rec["Image"] && $this->getImportDirectory() !== "") {
                    $dir = str_replace("..", "", (string) ($a_rec["Dir"] ?? ''));
                    if ($a_schema_version === "4.3.0" || $a_schema_version === "5.0.0") {
                        $source = $this->getImportDirectory() . "/" . $dir . "/org_" . $a_rec["Image"];
                        $name = $a_rec["Image"];
                    } else {
                        $source = $this->getImportDirectory() . "/" . $a_rec["Image"];
                        $name = basename($source);
                    }
                    $this->poll_image_factory->handler()->uploadImage(
                        $this->data_factory->objId($newObj->getId()),
                        $source,
                        $name,
                        $this->user->getId()
                    );
                }

                $a_mapping->addMapping("components/ILIAS/Poll", "poll", (string) ($a_rec["Id"] ?? "0"), (string) $newObj->getId());
                break;

            case "poll_answer":
                $poll_id = (int) $a_mapping->getMapping("components/ILIAS/Poll", "poll", (string) ($a_rec["PollId"] ?? "0"));
                if ($poll_id) {
                    $poll = new ilObjPoll($poll_id, false);
                    $poll->saveAnswer((string) ($a_rec["Answer"] ?? ''), (int) ($a_rec["pos"] ?? 10));
                }
                break;
        }
    }
}
