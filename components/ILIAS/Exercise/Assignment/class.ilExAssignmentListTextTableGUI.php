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
 * Assignments table
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilExAssignmentListTextTableGUI extends ilTable2GUI
{
    protected ilExAssignment $ass;
    protected bool $show_peer_review;
    protected ilExPeerReview $peer_review;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        ilExAssignment $a_ass,
        bool $a_show_peer_review = false,
        bool $a_disable_peer_review = false
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();

        $this->ass = $a_ass;
        $this->show_peer_review = $a_show_peer_review;
        $this->setId("excassltxt" . $this->ass->getId());

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setTitle(
            $lng->txt("exc_list_text_assignment") .
                ": \"" . $this->ass->getTitle() . "\""
        );

        // if you add pagination and disable the unlimited setting:
        // fix saving of ordering of single pages!
        $this->setLimit(9999);

        $this->addColumn($this->lng->txt("user"), "uname", "15%");
        $this->addColumn($this->lng->txt("exc_last_submission"), "udate", "10%");

        if ($this->show_peer_review) {
            $this->addColumn($this->lng->txt("exc_files_returned_text"), "", "45%");
            $this->addColumn($this->lng->txt("exc_peer_review"), "", "30%");

            $this->peer_review = new ilExPeerReview($this->ass);
        } else {
            $this->addColumn($this->lng->txt("exc_files_returned_text"), "", "75%");
        }

        $this->setDefaultOrderField("uname");
        $this->setDefaultOrderDirection("asc");

        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.exc_list_text_assignment_row.html", "Modules/Exercise");

        if (!$a_disable_peer_review &&
            $this->ass->getPeerReview() &&
            !$a_show_peer_review) {
            $this->addCommandButton("listTextAssignment", $lng->txt("exc_show_peer_review"));
        }

        $this->parse();
    }

    public function numericOrdering(string $a_field): bool
    {
        return ($a_field == "udate");
    }

    protected function parse(): void
    {
        $peer_data = array();
        if ($this->show_peer_review) {
            $peer_data = $this->peer_review->getAllPeerReviews();
        }
        $data = [];
        foreach (ilExSubmission::getAllAssignmentFiles($this->ass->getExerciseId(), $this->ass->getId()) as $file) {
            if (trim($file["atext"])) {
                $data[$file["user_id"]] = array(
                    "uid" => $file["user_id"],
                    "uname" => ilUserUtil::getNamePresentation($file["user_id"]),
                    "udate" => $file["ts"],
                    "utext" => ilRTE::_replaceMediaObjectImageSrc($file["atext"], 1) // mob id to mob src
                );

                if (isset($peer_data[$file["user_id"]])) {
                    $data[$file["user_id"]]["peer"] = array_keys($peer_data[$file["user_id"]]);
                }
            }
        }

        $this->setData($data);
    }

    protected function fillRow(array $a_set): void
    {
        if ($this->show_peer_review) {
            $peer_data = "&nbsp;";
            if (isset($a_set["peer"])) {
                $acc = new ilAccordionGUI();
                $acc->setId($this->ass->getId() . "_" . $a_set["uid"]);

                foreach ($a_set["peer"] as $peer_id) {
                    $submission = new ilExSubmission($this->ass, $a_set["uid"]);
                    $values = $submission->getPeerReview()->getPeerReviewValues($peer_id, $a_set["uid"]);

                    $acc_html = array();
                    foreach ($this->ass->getPeerReviewCriteriaCatalogueItems() as $crit) {
                        $crit_id = $crit->getId()
                            ? $crit->getId()
                            : $crit->getType();
                        $crit->setPeerReviewContext($this->ass, $peer_id, $a_set["uid"]);

                        // see ilWikiAdvMetaDataBlockGUI
                        $acc_html[] = '<p>' .
                            '<div class="ilBlockPropertyCaption">' . $crit->getTitle() . '</div>' .
                            '<div>' . $crit->getHTML($values[$crit_id]) . '</div>' .
                            '</p>';
                    }

                    $acc->addItem(
                        ilUserUtil::getNamePresentation($peer_id, false, false, "", true),
                        '<div style="margin-left:10px;">' . implode("\n", $acc_html) . '</div>'
                    );
                }

                $peer_data = $acc->getHTML();
            }
            $this->tpl->setCurrentBlock("peer_bl");
            $this->tpl->setVariable("PEER_REVIEW", $peer_data);
            $this->tpl->parseCurrentBlock();
        }

        $this->tpl->setVariable("USER_NAME", $a_set["uname"]);
        $this->tpl->setVariable(
            "USER_DATE",
            ilDatePresentation::formatDate(new ilDate($a_set["udate"], IL_CAL_DATETIME))
        );
        $this->tpl->setVariable("USER_TEXT", nl2br($a_set["utext"]));
    }
}
