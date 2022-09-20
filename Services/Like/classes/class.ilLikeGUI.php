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

use ILIAS\Like\StandardGUIRequest;

/**
 * User interface for like feature
 * @author Alexander Killing <killing@leifos.de>
 */
class ilLikeGUI
{
    protected ilLikeData $data;
    protected \ILIAS\DI\UIServices $ui;
    protected ilGlobalTemplateInterface $main_tpl;
    protected StandardGUIRequest $request;
    protected ilLanguage $lng;
    protected ilCtrl $ctrl;
    protected ilObjUser $user;
    protected int $obj_id;
    protected string $obj_type;
    protected int $sub_obj_id;
    protected string $sub_obj_type;
    protected int $news_id;
    protected string $dom_id;

    public function __construct(
        \ilLikeData $data,
        ?\ilTemplate $main_tpl = null
    ) {
        global $DIC;

        $this->main_tpl = ($main_tpl == null)
            ? $DIC->ui()->mainTemplate()
            : $main_tpl;

        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->user = $DIC->user();
        $this->ui = $DIC->ui();

        $this->data = $data;

        $this->lng->loadLanguageModule("like");
        $this->request = new StandardGUIRequest(
            $DIC->http(),
            $DIC->refinery()
        );

        $this->initJavascript();
    }

    protected function initJavascript(): void
    {
        $this->main_tpl->addJavaScript("./Services/Like/js/Like.js");
    }

    public function setObject(
        int $a_obj_id,
        string $a_obj_type,
        int $a_sub_obj_id = 0,
        string $a_sub_obj_type = "",
        int $a_news_id = 0
    ): void {
        $this->obj_id = $a_obj_id;
        $this->obj_type = $a_obj_type;
        $this->sub_obj_id = $a_sub_obj_id;
        $this->sub_obj_type = $a_sub_obj_type;
        $this->news_id = $a_news_id;
        $this->dom_id = "like_" . $this->obj_id . "_" . $this->obj_type . "_" . $this->sub_obj_id . "_" .
            $this->sub_obj_type . "_" . $this->news_id;
    }

    public function executeCommand(): string
    {
        $ilCtrl = $this->ctrl;

        $next_class = $ilCtrl->getNextClass($this);
        $cmd = $ilCtrl->getCmd("getHTML");

        switch ($next_class) {
            default:
                if (in_array($cmd, array("getHTML", "renderEmoticons", "renderModal", "saveExpression"))) {
                    return $this->$cmd();
                }
                break;
        }
        return "";
    }

    public function getHTML(): string
    {
        $f = $this->ui->factory();
        $r = $this->ui->renderer();
        $ctrl = $this->ctrl;
        $lng = $this->lng;

        $tpl = new ilTemplate("tpl.like.html", true, true, "Services/Like");

        // modal
        $modal_asyn_url = $ctrl->getLinkTarget($this, "renderModal", "", true, false);
        $modal = $f->modal()->roundtrip('', $f->legacy(""))
            ->withAsyncRenderUrl($modal_asyn_url);

        $modal_show_sig_id = $modal->getShowSignal()->getId();
        $this->ctrl->setParameter($this, "modal_show_sig_id", $modal_show_sig_id);
        $emo_counters = $this->renderEmoCounters($modal->getShowSignal());
        $tpl->setVariable("EMO_COUNTERS", $emo_counters . $r->render($modal));



        // emoticon popover
        $popover = $f->popover()->standard($f->legacy(''))->withTitle('');
        $ctrl->setParameter($this, "repl_sig", $popover->getReplaceContentSignal()->getId());
        $asyn_url = $ctrl->getLinkTarget($this, "renderEmoticons", "", true, false);
        $popover = $popover->withAsyncContentUrl($asyn_url);
        $button = $f->button()->shy($lng->txt("like"), '#')
            ->withOnClick($popover->getShowSignal());

        $tpl->setVariable("LIKE", $r->render([$popover, $button]));

        return $tpl->get();
    }

    /**
     * Render emo counters
     * @throws ilLikeDataException
     */
    protected function renderEmoCounters(
        ILIAS\UI\Component\Signal $modal_signal = null,
        bool $unavailable = false
    ): string {
        $ilCtrl = $this->ctrl;

        $tpl = new ilTemplate("tpl.emo_counters.html", true, true, "Services/Like");
        $f = $this->ui->factory();
        $r = $this->ui->renderer();

        $cnts = $this->data->getExpressionCounts(
            $this->obj_id,
            $this->obj_type,
            $this->sub_obj_id,
            $this->sub_obj_type,
            $this->news_id
        );
        $comps = array();
        foreach ($this->data->getExpressionTypes() as $k => $txt) {
            if ($cnts[$k] > 0) {
                $glyph = $this->getGlyphForConst($k, $unavailable);
                if ($modal_signal !== null) {
                    $glyph = $glyph->withOnClick($modal_signal);
                }
                $comps[] = $glyph->withCounter($f->counter()->status($cnts[$k]));
            }
        }

        if ($ilCtrl->isAsynch()) {
            $tpl->setVariable("MODAL_TRIGGER", $r->renderAsync($comps));
        } else {
            $tpl->setVariable("MODAL_TRIGGER", $r->render($comps));
        }
        if ($modal_signal !== null) {
            $tpl->setVariable("ID", $this->dom_id);
        }

        if (count($comps) > 0 && $modal_signal !== null) {
            $tpl->setVariable("SEP", $r->render($f->divider()->vertical()));
        }

        return $tpl->get();
    }

    protected function getGlyphForConst(
        int $a_const,
        bool $unavailable = false
    ): ?\ILIAS\UI\Component\Symbol\Glyph\Glyph {
        $f = $this->ui->factory();
        $like = null;
        switch ($a_const) {
            case ilLikeData::TYPE_LIKE: $like = $f->symbol()->glyph()->like();
                break;
            case ilLikeData::TYPE_DISLIKE: $like = $f->symbol()->glyph()->dislike();
                break;
            case ilLikeData::TYPE_LOVE: $like = $f->symbol()->glyph()->love();
                break;
            case ilLikeData::TYPE_LAUGH: $like = $f->symbol()->glyph()->laugh();
                break;
            case ilLikeData::TYPE_ASTOUNDED: $like = $f->symbol()->glyph()->astounded();
                break;
            case ilLikeData::TYPE_SAD: $like = $f->symbol()->glyph()->sad();
                break;
            case ilLikeData::TYPE_ANGRY: $like = $f->symbol()->glyph()->angry();
                break;
        }
        if ($unavailable) {
            $like = $like->withUnavailableAction();
        }
        return $like;
    }

    /**
     * Render emoticons (asynch)
     */
    public function renderEmoticons(): void
    {
        $ilCtrl = $this->ctrl;
        $r = $this->ui->renderer();
        $glyphs = [];

        $ilCtrl->saveParameter($this, "modal_show_sig_id");

        $tpl = new ilTemplate("tpl.emoticons.html", true, true, "Services/Like");
        $tpl->setVariable("ID", $this->dom_id);

        $url = $ilCtrl->getLinkTarget($this, "", "", true);
        foreach ($this->data->getExpressionTypes() as $k => $txt) {
            $g = $this->getGlyphForConst($k);

            if ($this->data->isExpressionSet(
                $this->user->getId(),
                $k,
                $this->obj_id,
                $this->obj_type,
                $this->sub_obj_id,
                $this->sub_obj_type,
                $this->news_id
            )) {
                $g = $g->withHighlight();
            }

            $g = $g->withAdditionalOnLoadCode(function ($id) use ($k, $url) {
                return
                    "$('#" . $id . "').click(function() { il.Like.toggle('" . $url . "','" . $id . "','" . $this->dom_id . "'," . $k . ");});";
            });
            $glyphs[] = $g;
        }

        $tpl->setVariable("GLYPHS", $r->renderAsync($glyphs));

        echo $tpl->get();
        exit;
    }

    /**
     * Save expression (asynch)
     * @throws ilLikeDataException
     */
    protected function saveExpression(): void
    {
        $exp_key = $this->request->getExpressionKey();
        $exp_val = $this->request->getValue();
        $modal_show_sig_id = $this->request->getModalSignalId();
        $show_signal = new \ILIAS\UI\Implementation\Component\Signal($modal_show_sig_id);

        if ($exp_val) {
            $this->data->addExpression(
                $this->user->getId(),
                $exp_key,
                $this->obj_id,
                $this->obj_type,
                $this->sub_obj_id,
                $this->sub_obj_type,
                $this->news_id
            );
        } else {
            $this->data->removeExpression(
                $this->user->getId(),
                $exp_key,
                $this->obj_id,
                $this->obj_type,
                $this->sub_obj_id,
                $this->sub_obj_type,
                $this->news_id
            );
        }
        echo $this->renderEmoCounters($show_signal);
        exit;
    }


    /**
     * Render modal (asynch)
     * @throws ilDateTimeException
     * @throws ilLikeDataException
     * @throws ilWACException
     */
    public function renderModal(): void
    {
        $user = $this->user;

        $f = $this->ui->factory();
        $r = $this->ui->renderer();


        $list_items = [];
        foreach ($this->data->getExpressionEntries(
            $this->obj_id,
            $this->obj_type,
            $this->sub_obj_id,
            $this->sub_obj_type,
            $this->news_id
        ) as $exp) {
            $name = ilUserUtil::getNamePresentation($exp["user_id"]);

            $image = $f->image()->responsive(
                ilObjUser::_getPersonalPicturePath($exp["user_id"]),
                $name
            );

            $g = $this->getGlyphForConst($exp["expression"], true);

            $list_items[] = $f->item()->standard($name)
                ->withDescription($r->render($g) . " " .
                    ilDatePresentation::formatDate(new ilDateTime($exp["timestamp"], IL_CAL_DATETIME)))
                ->withLeadImage($image);
        }

        $std_list = $f->panel()->listing()->standard("", array(
            $f->item()->group("", $list_items)
        ));

        $header = $f->legacy($this->renderEmoCounters(null, true));
        //$header = $f->legacy("---");

        $modal = $f->modal()->roundtrip('', [$header, $std_list]);
        echo $r->render($modal);
        exit;
    }

    /**
     * Get expression text for const
     */
    public static function getExpressionText(
        int $a_const
    ): string {
        global $DIC;

        $lng = $DIC->language();

        switch ($a_const) {
            case ilLikeData::TYPE_LIKE: return $lng->txt("like");
            case ilLikeData::TYPE_DISLIKE: return $lng->txt("dislike");
            case ilLikeData::TYPE_LOVE: return $lng->txt("love");
            case ilLikeData::TYPE_LAUGH: return $lng->txt("laugh");
            case ilLikeData::TYPE_ASTOUNDED: return $lng->txt("astounded");
            case ilLikeData::TYPE_SAD: return $lng->txt("sad");
            case ilLikeData::TYPE_ANGRY: return $lng->txt("angry");
        }
        return "";
    }
}
