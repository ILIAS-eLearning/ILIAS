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

/**
 * List all peers to be reviewed for user
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @author Alexander Killing <killing@leifos.de>
 */
class ilExAssignmentPeerReviewTableGUI extends ilTable2GUI
{
    protected ilExAssignment $ass;
    protected int $user_id = 0;
    protected array $peer_data = [];
    protected ilFSStorageExercise $fstorage;
    protected int $invalid = 0;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        ilExAssignment $a_ass,
        int $a_user_id,
        array $a_peer_data
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->ass = $a_ass;
        $this->user_id = $a_user_id;
        $this->peer_data = $a_peer_data;

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setLimit(9999);

        $this->setTitle(
            $a_ass->getTitle() . ": " . $this->lng->txt("exc_peer_review") .
                " - " . $this->lng->txt("exc_peer_review_give")
        );

        if (!$this->ass->hasPeerReviewPersonalized()) {
            $this->addColumn($this->lng->txt("id"), "seq");
            #21260
            $this->setDefaultOrderField("seq");
        } else {
            $this->addColumn($this->lng->txt("exc_peer_review_recipient"), "name");
            #21260
            $this->setDefaultOrderField("name");
        }
        $this->addColumn($this->lng->txt("last_update"), "tstamp");
        $this->addColumn($this->lng->txt("valid"), "valid");
        $this->addColumn($this->lng->txt("action"), "");

        $this->setRowTemplate("tpl.exc_peer_review_row.html", "Modules/Exercise");

        $this->disable("numinfo");

        $this->getItems();

        if ($this->ass->hasPeerReviewFileUpload()) {
            $this->fstorage = new ilFSStorageExercise($this->ass->getExerciseId(), $this->ass->getId());
            $this->fstorage->create();
        }
    }

    public function getInvalidItems(): int
    {
        return $this->invalid;
    }

    protected function getItems(): void
    {
        $data = array();

        $personal = $this->ass->hasPeerReviewPersonalized();

        foreach ($this->peer_data as $item) {
            $row = array();

            if (ilObject::_lookupType($item["peer_id"]) != "usr") {
                continue;
            }

            $row["giver_id"] = $item["giver_id"];
            $row["peer_id"] = $item["peer_id"];
            $row["tstamp"] = $item["tstamp"];

            if (!$personal) {
                $row["seq"] = $item["seq"];
            } else {
                $row["name"] = ilUserUtil::getNamePresentation($item["peer_id"]);
            }

            // validate
            $row["valid"] = $all_empty = true;
            $submission = new ilExSubmission($this->ass, $item["peer_id"]);
            $values = $submission->getPeerReview()->getPeerReviewValues($item["giver_id"], $item["peer_id"]);
            foreach ($this->ass->getPeerReviewCriteriaCatalogueItems() as $crit) {
                $crit_id = $crit->getId()
                    ? $crit->getId()
                    : $crit->getType();
                $crit->setPeerReviewContext(
                    $this->ass,
                    $item["giver_id"],
                    $item["peer_id"]
                );
                if (!$crit->validate($values[$crit_id] ?? null)) {
                    $row["valid"] = false;
                }
                if ($crit->hasValue($values[$crit_id] ?? null)) {
                    $all_empty = false;
                }
            }
            if ($all_empty) {
                $row["valid"] = false;
            }
            if (!$row["valid"]) {
                $this->invalid++;
            }

            $data[] = $row;
        }

        $this->setData($data);
    }

    public function numericOrdering(string $a_field): bool
    {
        return $a_field === "seq";
    }

    /**
     * @throws ilDateTimeException
     */
    protected function fillRow(array $a_set): void
    {
        $ilCtrl = $this->ctrl;

        if (isset($a_set["seq"])) {
            $this->tpl->setVariable("VAL_SEQ", $a_set["seq"]);
        } else {
            $this->tpl->setVariable("VAL_SEQ", $a_set["name"]);
        }

        if ($a_set["tstamp"]) {
            $a_set["tstamp"] = ilDatePresentation::formatDate(new ilDateTime($a_set["tstamp"], IL_CAL_DATETIME));
        }
        $this->tpl->setVariable("VAL_TSTAMP", $a_set["tstamp"]);

        $this->tpl->setVariable(
            "VAL_STATUS",
            $a_set["valid"]
            ? $this->lng->txt("yes")
            : $this->lng->txt("no")
        );

        $ilCtrl->setParameter($this->parent_obj, "peer_id", $a_set["peer_id"]);
        $url = $ilCtrl->getLinkTarget($this->parent_obj, "editPeerReviewItem");
        $ilCtrl->setParameter($this->parent_obj, "pid", "");

        $this->tpl->setVariable("TXT_ACTION", $this->lng->txt("edit"));
        $this->tpl->setVariable("URL_ACTION", $url);
    }
}
