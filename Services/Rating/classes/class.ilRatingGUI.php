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
 * Class ilRatingGUI. User interface class for rating.
 *
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilRatingGUI: ilRatingCategoryGUI
 */
class ilRatingGUI
{
    protected ilLanguage$lng;
    protected ilCtrl $ctrl;
    protected ilObjUser $user;
    protected string $id = "rtg_";
    protected $export_callback;
    protected string $export_subobj_title = "";
    protected array $ctrl_path = [];
    protected bool $enable_categories = false;
    protected string $your_rating_text = "";
    protected \ILIAS\DI\UIServices $ui;

    protected int $obj_id;
    protected string $obj_type;
    protected ?int $sub_obj_id;
    protected ?string $sub_obj_type;
    protected int $userid;
    protected $update_callback = null;
    protected ?array $requested_ratings = null;
    protected int $requested_rating;

    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->user = $DIC->user();
        $lng = $DIC->language();

        $this->ui = $DIC->ui();

        $params = $DIC->http()->request()->getQueryParams();
        $body = $DIC->http()->request()->getParsedBody();

        if (isset($body["rating"]) && is_array($body["rating"])) {
            $this->requested_ratings = ($body["rating"] ?? null);
        }
        $this->requested_rating = (int) ($params["rating"] ?? 0);

        $lng->loadLanguageModule("rating");
    }

    /**
     * execute command
     */
    public function executeCommand(): void
    {
        $ilCtrl = $this->ctrl;

        $next_class = $ilCtrl->getNextClass($this);
        $cmd = $ilCtrl->getCmd();

        switch ($next_class) {
            case "ilratingcategorygui":
                $gui = new ilRatingCategoryGUI($this->obj_id, $this->export_callback, $this->export_subobj_title);
                $ilCtrl->forwardCommand($gui);
                break;

            default:
                $this->$cmd();
                break;
        }
    }

    /**
    * Set Object.
    *
    * @param	int			$a_obj_id			Object ID
    * @param	string		$a_obj_type			Object Type
    * @param	int			$a_sub_obj_id		Subobject ID
    * @param	string		$a_sub_obj_type		Subobject Type
    */
    public function setObject(
        int $a_obj_id,
        string $a_obj_type,
        ?int $a_sub_obj_id = 0,
        ?string $a_sub_obj_type = ""
    ): void {
        $ilUser = $this->user;

        if (!trim($a_sub_obj_type)) {
            $a_sub_obj_type = "-";
        }

        $this->obj_id = $a_obj_id;
        $this->obj_type = $a_obj_type;
        $this->sub_obj_id = $a_sub_obj_id;
        $this->sub_obj_type = $a_sub_obj_type;
        $this->id = "rtg_" . $this->obj_id . "_" . $this->obj_type . "_" . $this->sub_obj_id . "_" .
            $this->sub_obj_type;

        $this->setUserId($ilUser->getId());
    }

    public function setUserId(int $a_userid): void
    {
        $this->userid = $a_userid;
    }

    public function getUserId(): int
    {
        return $this->userid;
    }

    public function setYourRatingText(string $a_val): void
    {
        $this->your_rating_text = $a_val;
    }

    public function getYourRatingText(): string
    {
        return $this->your_rating_text;
    }

    public function enableCategories(bool $a_value): void
    {
        $this->enable_categories = $a_value;
    }

    public function setCtrlPath(array $a_value): void
    {
        $this->ctrl_path = $a_value;
    }

    // Render rating details
    protected function renderDetails(
        string $a_js_id,
        bool $a_may_rate,
        array $a_categories = null,
        string $a_onclick = null,
        bool $a_average = false,
        bool $add_tooltip = false
    ): string {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $ttpl = new ilTemplate("tpl.rating_details.html", true, true, "Services/Rating");

        $rate_text = null;
        if ($this->getYourRatingText() != "#") {
            $rate_text = ($this->getYourRatingText() != "")
                ? $this->getYourRatingText()
                : $lng->txt("rating_your_rating");
        }

        // no categories: 1 simple rating (link)
        if (!$a_categories) {
            if ($a_may_rate) {
                $rating = ilRating::getRatingForUserAndObject(
                    $this->obj_id,
                    $this->obj_type,
                    $this->sub_obj_id,
                    $this->sub_obj_type,
                    $this->getUserId(),
                    0
                );
                $overall_rating = [
                    "avg" => 0,
                    "cnt" => 0
                ];
                if ($a_average) {
                    $overall_rating = ilRating::getOverallRatingForObject(
                        $this->obj_id,
                        $this->obj_type,
                        $this->sub_obj_id,
                        $this->sub_obj_type
                    );
                }

                // user rating links
                for ($i = 1; $i <= 5; $i++) {
                    if ($a_average &&
                        $i == $rating) {
                        $ttpl->setCurrentBlock("rating_mark_simple");
                        $ttpl->setVariable(
                            "SRC_MARK_SIMPLE",
                            ilUtil::getImagePath("icon_rate_marker.svg")
                        );
                        $ttpl->parseCurrentBlock();
                    }

                    $ttpl->setCurrentBlock("rating_link_simple");
                    if (stristr($a_onclick, "%rating%")) {
                        $url_save = "#";
                    } else {
                        $ilCtrl->setParameter($this, "rating", $i);
                        if (!$this->ctrl_path) {
                            $url_save = $ilCtrl->getLinkTarget($this, "saveRating");
                        } else {
                            $url_save = $ilCtrl->getLinkTargetByClass($this->ctrl_path, "saveRating");
                        }
                    }
                    $ttpl->setVariable("HREF_RATING", $url_save);

                    if ($a_onclick) {
                        $onclick = str_replace("%rating%", $i, $a_onclick);
                        $ttpl->setVariable("ONCLICK_RATING", ' onclick="' . $onclick . '"');
                    }

                    if ($a_average) {
                        $ref_rating = $overall_rating["avg"];
                    } else {
                        $ref_rating = $rating;
                    }

                    if ($ref_rating >= $i) {
                        $ttpl->setVariable(
                            "SRC_ICON",
                            ilUtil::getImagePath("icon_rate_on.svg")
                        );
                    } else {
                        $ttpl->setVariable(
                            "SRC_ICON",
                            ilUtil::getImagePath("icon_rate_off.svg")
                        );
                    }
                    $ttpl->setVariable(
                        "ALT_ICON",
                        sprintf($lng->txt("rating_rate_x_of_5"), $i)
                    );
                    $ttpl->parseCurrentBlock();
                }

                // remove
                if ($rating) {
                    $ttpl->setCurrentBlock("rating_simple_del_bl");
                    $ttpl->setVariable("CAPTION_RATING_DEL", $lng->txt("rating_remove"));

                    if (stristr($a_onclick, "%rating%")) {
                        $url_save = "#";
                    } else {
                        $ilCtrl->setParameter($this, "rating", 0);
                        if (!$this->ctrl_path) {
                            $url_save = $ilCtrl->getLinkTarget($this, "saveRating");
                        } else {
                            $url_save = $ilCtrl->getLinkTargetByClass($this->ctrl_path, "saveRating");
                        }
                    }
                    $ttpl->setVariable("HREF_RATING_DEL", $url_save);

                    if ($a_onclick) {
                        $onclick = str_replace("%rating%", 0, $a_onclick);
                        $ttpl->setVariable("ONCLICK_RATING_DEL", ' onclick="' . $onclick . '"');
                    }

                    $ttpl->parseCurrentBlock();
                }

                if ($rate_text) {
                    $ttpl->setCurrentBlock("rating_simple_title");
                    $ttpl->setVariable("TXT_RATING_SIMPLE", $rate_text);
                    $ttpl->parseCurrentBlock();
                }

                if ($a_average &&
                    $overall_rating["cnt"]) {
                    $ttpl->setCurrentBlock("number_votes_simple");
                    $ttpl->setVariable("NUMBER_VOTES_SIMPLE", $overall_rating["cnt"]);
                    $ttpl->parseCurrentBlock();
                }

                if ($add_tooltip) {
                    $unique_id = $this->id . "_block";
                    $ttpl->setVariable("TTID", $unique_id);
                    $this->addTooltip(
                        $unique_id,
                        (int) ($overall_rating["cnt"] ?? 0),
                        (float) ($overall_rating["avg"] ?? 0),
                        (int) ($rating ?? 0)
                    );
                }

                // user rating text
                $ttpl->setCurrentBlock("user_rating_simple");
                $ttpl->parseCurrentBlock();
            }
        }
        // categories: overall & user (form)
        else {
            $has_user_rating = false;
            $overall_rating = [
                "avg" => 0,
                "cnt" => 0
            ];
            foreach ($a_categories as $category) {
                $user_rating = round(ilRating::getRatingForUserAndObject(
                    $this->obj_id,
                    $this->obj_type,
                    $this->sub_obj_id,
                    $this->sub_obj_type,
                    $this->getUserId(),
                    $category["id"]
                ));

                $overall_rating = ilRating::getOverallRatingForObject(
                    $this->obj_id,
                    $this->obj_type,
                    $this->sub_obj_id,
                    $this->sub_obj_type,
                    $category["id"]
                );

                for ($i = 1; $i <= 5; $i++) {
                    if ($a_may_rate && $i == $user_rating) {
                        $has_user_rating = true;

                        $ttpl->setCurrentBlock("rating_mark");
                        $ttpl->setVariable(
                            "SRC_MARK",
                            ilUtil::getImagePath("icon_rate_marker.svg")
                        );
                        $ttpl->parseCurrentBlock();
                    }

                    $ttpl->setCurrentBlock("user_rating_icon");
                    if ($overall_rating["avg"] >= $i) {
                        $ttpl->setVariable(
                            "SRC_ICON",
                            ilUtil::getImagePath("icon_rate_on.svg")
                        );
                    } elseif ($overall_rating["avg"] + 1 <= $i) {
                        $ttpl->setVariable(
                            "SRC_ICON",
                            ilUtil::getImagePath("icon_rate_off.svg")
                        );
                    } else {
                        $nr = round(($overall_rating["avg"] + 1 - $i) * 10);
                        $ttpl->setVariable(
                            "SRC_ICON",
                            ilUtil::getImagePath("icon_rate_$nr.svg")
                        );
                    }
                    $ttpl->setVariable(
                        "ALT_ICON",
                        sprintf($lng->txt("rating_rate_x_of_5"), $i)
                    );

                    if ($a_may_rate) {
                        $ttpl->setVariable("HREF_RATING", "il.Rating.setValue(" . $category["id"] . "," . $i . ", '" . $a_js_id . "')");
                        $ttpl->setVariable("CATEGORY_ID", $category["id"]);
                        $ttpl->setVariable("ICON_VALUE", $i);
                        $ttpl->setVariable("JS_ID", $a_js_id);
                        $ttpl->setVariable("ICON_MOUSEACTION", " onmouseover=\"il.Rating.toggleIcon(this," . $i . ")\"" .
                            " onmouseout=\"il.Rating.toggleIcon(this," . $i . ",1)\"");
                    }

                    $ttpl->parseCurrentBlock();
                }

                if ($a_may_rate) {
                    $ttpl->setCurrentBlock("user_rating_category_column");
                    $ttpl->setVariable("JS_ID", $a_js_id);
                    $ttpl->setVariable("CATEGORY_ID", $category["id"]);
                    $ttpl->setVariable("CATEGORY_VALUE", $user_rating);
                    if ($add_tooltip) {
                        $unique_id = $this->id . "_block_" . $category["id"];
                        $ttpl->setVariable("CAT_TTID", $unique_id);
                        $this->addTooltip(
                            $unique_id,
                            (int) ($overall_rating["cnt"] ?? 0),
                            (float) ($overall_rating["avg"] ?? 0),
                            (int) ($user_rating ?? 0)
                        );
                    }
                    $ttpl->parseCurrentBlock();
                }


                // category title
                $ttpl->setCurrentBlock("user_rating_category");
                $ttpl->setVariable("TXT_RATING_CATEGORY", $category["title"]);
                $ttpl->parseCurrentBlock();
            }

            if ($overall_rating["cnt"] > 0) {
                $ttpl->setCurrentBlock("votes_number_bl");
                $ttpl->setVariable("NUMBER_VOTES", sprintf($lng->txt("rating_number_votes"), $overall_rating["cnt"]));
                $ttpl->parseCurrentBlock();
            }

            if ($a_may_rate) {
                // remove
                if ($has_user_rating) {
                    $ttpl->setCurrentBlock("user_rating_categories_del_bl");
                    $ttpl->setVariable("CAPTION_RATING_DEL_CAT", $lng->txt("rating_remove"));

                    $ilCtrl->setParameter($this, "rating", 0);
                    if (!$this->ctrl_path) {
                        $url_save = $ilCtrl->getLinkTarget($this, "resetUserRating");
                    } else {
                        $url_save = $ilCtrl->getLinkTargetByClass($this->ctrl_path, "resetUserRating");
                    }
                    $ttpl->setVariable("HREF_RATING_DEL_CAT", $url_save);

                    $ttpl->parseCurrentBlock();
                }

                if (!$this->ctrl_path) {
                    $url_form = $ilCtrl->getFormAction($this, "saveRating");
                } else {
                    $url_form = $ilCtrl->getFormActionByClass($this->ctrl_path, "saveRating");
                }
                $ttpl->setVariable("FORM_ACTION", $url_form);
                $ttpl->setVariable("TXT_SUBMIT", $lng->txt("rating_overlay_submit"));
                $ttpl->setVariable("CMD_SUBMIT", "saveRating");
                $ttpl->touchBlock("user_rating_categories_form_out");
            }
        }

        return $ttpl->get();
    }

    // Get HTML for rating of an object (and a user)
    public function getHTML(
        bool $a_show_overall = true,
        bool $a_may_rate = true,
        string $a_onclick = null,
        string $a_additional_id = null
    ): string {
        $lng = $this->lng;
        $unique_id = $this->id;
        if ($a_additional_id) {
            $unique_id .= "_" . $a_additional_id;
        }

        $categories = array();
        if ($this->enable_categories) {
            $categories = ilRatingCategory::getAllForObject($this->obj_id);
        }

        $may_rate = ($this->getUserId() != ANONYMOUS_USER_ID);
        if ($may_rate && !$a_may_rate) {
            $may_rate = false;
        }

        $has_overlay = false;
        if ($may_rate || $categories) {
            $has_overlay = true;
        }

        $ttpl = new ilTemplate("tpl.rating_input.html", true, true, "Services/Rating");

        // user rating
        $user_rating = 0;
        if ($may_rate || !$a_show_overall) {
            $user_rating = round(ilRating::getRatingForUserAndObject(
                $this->obj_id,
                $this->obj_type,
                $this->sub_obj_id,
                $this->sub_obj_type,
                $this->getUserId()
            ));
        }

        // (1) overall rating
        if ($a_show_overall) {
            $rating = ilRating::getOverallRatingForObject(
                $this->obj_id,
                $this->obj_type,
                $this->sub_obj_id,
                $this->sub_obj_type
            );
        } else {
            $rating = array("avg" => $user_rating);
        }

        for ($i = 1; $i <= 5; $i++) {
            if ($a_show_overall &&
                $i == $user_rating) {
                $ttpl->setCurrentBlock("rating_mark");
                $ttpl->setVariable(
                    "SRC_MARK",
                    ilUtil::getImagePath("icon_rate_marker.svg")
                );
                $ttpl->parseCurrentBlock();
            }

            $ttpl->setCurrentBlock("rating_icon");
            if ($rating["avg"] >= $i) {
                $ttpl->setVariable(
                    "SRC_ICON",
                    ilUtil::getImagePath("icon_rate_on.svg")
                );
            } elseif ($rating["avg"] + 1 <= $i) {
                $ttpl->setVariable(
                    "SRC_ICON",
                    ilUtil::getImagePath("icon_rate_off.svg")
                );
            } else {
                $nr = round(($rating["avg"] + 1 - $i) * 10);
                $ttpl->setVariable(
                    "SRC_ICON",
                    ilUtil::getImagePath("icon_rate_$nr.svg")
                );
            }
            $ttpl->setVariable("ALT_ICON", "");
            $ttpl->parseCurrentBlock();
        }
        $ttpl->setCurrentBlock("rating_icon");

        if ($a_show_overall) {
            if ($rating["cnt"] > 0) {
                $ttpl->setCurrentBlock("rat_nr");
                $ttpl->setVariable("RT_NR", $rating["cnt"]);
                $ttpl->parseCurrentBlock();
            }
        }

        $this->addTooltip(
            $unique_id . "_tt",
            (int) ($rating["cnt"] ?? 0),
            (float) ($rating["avg"] ?? 0),
            (int) ($user_rating ?? 0)
        );

        // add overlay (trigger)
        if ($has_overlay) {
            $ov = new ilOverlayGUI($unique_id);
            $ov->setTrigger("tr_" . $unique_id, "click", "tr_" . $unique_id);
            $ov->add();

            $ttpl->setCurrentBlock("act_rat_start");
            $ttpl->setVariable("ID", $unique_id);
            $ttpl->setVariable("TXT_OPEN_DIALOG", $lng->txt("rating_open_dialog"));
            $ttpl->parseCurrentBlock();

            $ttpl->touchBlock("act_rat_end");
        }

        $ttpl->parseCurrentBlock();


        // (2) user rating

        if ($has_overlay) {
            $ttpl->setVariable(
                "RATING_DETAILS",
                $this->renderDetails("rtov_", $may_rate, $categories, $a_onclick)
            );

            $ttpl->setCurrentBlock("user_rating");
            $ttpl->setVariable("ID", $unique_id);
            $ttpl->parseCurrentBlock();
        }

        $ttpl->setVariable("TTID", $unique_id);

        return $ttpl->get();
    }

    protected function addTooltip(
        string $id,
        int $cnt = 0,
        float $avg = 0,
        int $user = 0
    ): void {
        $lng = $this->lng;

        $tt = "";
        if ($cnt == 0) {
            $tt = $lng->txt("rat_not_rated_yet");
        } else {
            if ($cnt == 1) {
                $tt = $lng->txt("rat_one_rating");
            } else {
                $tt = sprintf($lng->txt("rat_nr_ratings"), $cnt);
            }
            $tt .= "<br>" . $lng->txt("rating_avg_rating") . ": " . round($avg, 1);
        }

        if ($user > 0) {
            $tt .= "<br>" . $lng->txt("rating_personal_rating") . ": " . $user;
        }
        if ($tt !== "") {
            ilTooltipGUI::addTooltip(
                $id,
                $tt,
                "",
                "bottom center",
                "top center",
                false
            );
        }
    }

    public function getBlockHTML(string $a_title): string
    {
        $ui = $this->ui;

        $categories = array();
        if ($this->enable_categories) {
            $categories = ilRatingCategory::getAllForObject($this->obj_id);
        }

        $may_rate = ($this->getUserId() != ANONYMOUS_USER_ID);


        $panel = $ui->factory()->panel()->secondary()->legacy(
            $a_title,
            $ui->factory()->legacy(
                $this->renderDetails("rtsb_", $may_rate, $categories, null, true, true)
            )
        );

        return $ui->renderer()->render($panel);
    }

    /**
     * Save Rating
     */
    public function saveRating(): void
    {
        $ilCtrl = $this->ctrl;

        if (!is_array($this->requested_ratings)) {
            $rating = $this->requested_rating;
            if ($rating == 0) {
                $this->resetUserRating();
            } else {
                ilRating::writeRatingForUserAndObject(
                    $this->obj_id,
                    $this->obj_type,
                    $this->sub_obj_id,
                    $this->sub_obj_type,
                    $this->getUserId(),
                    $rating
                );
            }
        } else {
            foreach ($this->requested_ratings as $cat_id => $rating) {
                ilRating::writeRatingForUserAndObject(
                    $this->obj_id,
                    $this->obj_type,
                    $this->sub_obj_id,
                    $this->sub_obj_type,
                    $this->getUserId(),
                    $rating,
                    $cat_id
                );
            }
        }

        if ($this->update_callback) {
            call_user_func(
                $this->update_callback,
                $this->obj_id,
                $this->obj_type,
                $this->sub_obj_id,
                $this->sub_obj_type
            );
        }

        if ($ilCtrl->isAsynch()) {
            exit();
        }
    }

    public function setUpdateCallback($a_callback): void
    {
        $this->update_callback = $a_callback;
    }

    public function resetUserRating(): void
    {
        ilRating::resetRatingForUserAndObject(
            $this->obj_id,
            $this->obj_type,
            (int) $this->sub_obj_id,
            $this->sub_obj_type,
            $this->getUserId()
        );
    }

    public function setExportCallback($a_callback, string $a_subobj_title): void
    {
        $this->export_callback = $a_callback;
        $this->export_subobj_title = $a_subobj_title;
    }

    // Build list gui property for object
    public function getListGUIProperty(
        int $a_ref_id,
        bool $a_may_rate,
        string $a_ajax_hash,
        int $parent_ref_id
    ): string {
        return $this->getHTML(
            true,
            $a_may_rate,
            "il.Object.saveRatingFromListGUI(" . $a_ref_id . ", '" . $a_ajax_hash . "', %rating%);",
            $parent_ref_id
        );
    }
}
