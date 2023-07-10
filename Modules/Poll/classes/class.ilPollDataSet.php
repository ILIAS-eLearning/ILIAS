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
 ********************************************************************
 */

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
    protected \ILIAS\Notes\Service $notes;

    public function __construct()
    {
        global $DIC;

        parent::__construct();
        $this->notes = $DIC->notes();
    }

    public function getSupportedVersions(): array
    {
        return array("4.3.0", "5.0.0");
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
        if ($a_entity === "poll") {
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
            }
        }

        if ($a_entity === "poll_answer") {
            switch ($a_version) {
                case "4.3.0":
                case "5.0.0":
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

        if ($a_entity === "poll") {
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
            }
        }

        if ($a_entity === "poll_answer") {
            switch ($a_version) {
                case "4.3.0":
                case "5.0.0":
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
        if ($a_entity === "poll") {
            $dir = ilObjPoll::initStorage((int) $a_set["Id"]);
            $a_set["Dir"] = $dir;

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
        switch ($a_entity) {
            case "poll":
                // container copy
                if ($new_id = $a_mapping->getMapping("Services/Container", "objs", (string) ($a_rec["Id"] ?? "0"))) {
                    $newObj = ilObjectFactory::getInstanceByObjId((int) $new_id, false);
                } else {
                    $newObj = new ilObjPoll();
                    $newObj->create();
                }

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
                $newObj->setImage((string) ($a_rec["Image"] ?? ''));
                $newObj->setViewResults((int) ($a_rec["ViewResults"] ?? ilObjPoll::VIEW_RESULTS_AFTER_VOTE));
                $newObj->setVotingPeriod((bool) ($a_rec["Period"] ?? 0));
                $newObj->setVotingPeriodBegin((int) ($a_rec["PeriodBegin"] ?? 0));
                $newObj->setVotingPeriodEnd((int) ($a_rec["PeriodEnd"] ?? 0));
                $newObj->update();

                // handle image(s)
                if ($a_rec["Image"]) {
                    $dir = str_replace("..", "", (string) ($a_rec["Dir"] ?? ''));
                    if ($dir !== "" && $this->getImportDirectory() !== "") {
                        $source_dir = $this->getImportDirectory() . "/" . $dir;
                        $target_dir = ilObjPoll::initStorage($newObj->getId());
                        ilFileUtils::rCopy($source_dir, $target_dir);
                    }
                }

                $a_mapping->addMapping("Modules/Poll", "poll", (string) ($a_rec["Id"] ?? "0"), (string) $newObj->getId());
                break;

            case "poll_answer":
                $poll_id = (int) $a_mapping->getMapping("Modules/Poll", "poll", (string) ($a_rec["PollId"] ?? "0"));
                if ($poll_id) {
                    $poll = new ilObjPoll($poll_id, false);
                    $poll->saveAnswer((string) ($a_rec["Answer"] ?? ''), (int) ($a_rec["pos"] ?? 10));
                }
                break;
        }
    }
}
