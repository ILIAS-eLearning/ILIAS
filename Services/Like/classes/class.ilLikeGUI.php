<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * User interface for like feature
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesLike
 */
class ilLikeGUI
{
    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var int
     */
    protected $obj_id;

    /**
     * @var string
     */
    protected $obj_type;

    /**
     * @var int
     */
    protected $sub_obj_id;

    /**
     * @var string
     */
    protected $sub_obj_type;

    /**
     * @var int
     */
    protected $news_id;

    /**
     * @var string dom id
     */
    protected $dom_id;

    /**
     * @var ilLanguage
     */
    protected $language;

    /**
     * ilLikeGUI constructor.
     * @param ilLikeData $data
     * @param ilTemplate|null $main_tpl
     */
    public function __construct(\ilLikeData $data, \ilTemplate $main_tpl = null)
    {
        global $DIC;

        $this->main_tpl = ($main_tpl == null)
            ? $DIC->ui()->mainTemplate()
            : $main_tpl;

        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->user = $DIC->user();
        $this->ui = $DIC->ui();
        $this->lng = $DIC->language();

        $this->data = $data;

        $this->lng->loadLanguageModule("like");

        $this->initJavascript();
    }

    /**
     * Init javascript
     */
    protected function initJavascript()
    {
        $this->main_tpl->addJavaScript("./Services/Like/js/Like.js");
    }


    /**
     * Set Object.
     *
     * @param       int             $a_obj_id               Object ID
     * @param       string          $a_obj_type             Object Type
     * @param       int             $a_sub_obj_id           Subobject ID
     * @param       string          $a_sub_obj_type         Subobject Type
     */
    public function setObject($a_obj_id, $a_obj_type, $a_sub_obj_id = 0, $a_sub_obj_type = "", $a_news_id = 0)
    {
        $this->obj_id = $a_obj_id;
        $this->obj_type = $a_obj_type;
        $this->sub_obj_id = $a_sub_obj_id;
        $this->sub_obj_type = $a_sub_obj_type;
        $this->news_id = $a_news_id;
        $this->dom_id = "like_" . $this->obj_id . "_" . $this->obj_type . "_" . $this->sub_obj_id . "_" .
            $this->sub_obj_type . "_" . $this->news_id;
    }

    /**
     * Execute command
     * @return string
     */
    public function executeCommand()
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

    /**
     * Get HTML
     *
     * @param $a_obj_id
     * @param $a_obj_type
     * @param int $a_sub_obj_id
     * @param string $a_sub_obj_type
     * @param int $a_news_id
     * @return string
     * @throws ilLikeDataException
     */
    public function getHTML()
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
     *
     * @param $modal_signal
     * @return string
     * @throws ilLikeDataException
     */
    protected function renderEmoCounters($modal_signal = null)
    {
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
                $glyph = $this->getGlyphForConst($k);
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


    /**
     * Get glyph for const
     *
     * @param int $a_const
     * @return \ILIAS\UI\Component\Glyph\Glyph|null
     */
    protected function getGlyphForConst($a_const)
    {
        $f = $this->ui->factory();
        $like = null;
        switch ($a_const) {
            case ilLikeData::TYPE_LIKE: $like = $f->glyph()->like(); break;
            case ilLikeData::TYPE_DISLIKE: $like = $f->glyph()->dislike(); break;
            case ilLikeData::TYPE_LOVE: $like = $f->glyph()->love(); break;
            case ilLikeData::TYPE_LAUGH: $like = $f->glyph()->laugh(); break;
            case ilLikeData::TYPE_ASTOUNDED: $like = $f->glyph()->astounded(); break;
            case ilLikeData::TYPE_SAD: $like = $f->glyph()->sad(); break;
            case ilLikeData::TYPE_ANGRY: $like = $f->glyph()->angry(); break;
        }
        return $like;
    }



    /**
     * Render emoticons
     */
    public function renderEmoticons()
    {
        $ilCtrl = $this->ctrl;
        $r = $this->ui->renderer();

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
     * Save expresseion
     *
     * @throws ilLikeDataException
     */
    protected function saveExpression()
    {
        $exp_key = (int) $_GET["exp"];
        $exp_val = (int) $_GET["val"];
        $modal_show_sig_id = ilUtil::stripSlashes($_GET["modal_show_sig_id"]);
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
     * Render modal
     * @throws ilLikeDataException
     */
    public function renderModal()
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

            $g = $this->getGlyphForConst($exp["expression"]);

            $list_items[] = $f->item()->standard($name)
                ->withDescription($r->render($g) . " " .
                    ilDatePresentation::formatDate(new ilDateTime($exp["timestamp"], IL_CAL_DATETIME)))
                ->withLeadImage($image);
        }

        $std_list = $f->panel()->listing()->standard("", array(
            $f->item()->group("", $list_items)
        ));

        $header = $f->legacy($this->renderEmoCounters());
        //$header = $f->legacy("---");

        $modal = $f->modal()->roundtrip('', [$header, $std_list]);
        echo $r->render($modal);
        exit;
    }


    /**
     * Get unicode for const
     *
     * @param int $a_const
     * @return string
     */
    /*
    static public function getCharacter($a_const)
    {
        $tpl = new ilTemplate("tpl.unicodes.html", true, true, "Services/Like");
        $tpl->touchBlock("u".$a_const);
        return $tpl->get();
    }*/

    /**
     * Get expresseion text for const
     *
     * @param int $a_const
     * @return string
     */
    public static function getExpressionText($a_const)
    {
        global $DIC;

        $lng = $DIC->language();

        switch ($a_const) {
            case ilLikeData::TYPE_LIKE: return $lng->txt("like"); break;
            case ilLikeData::TYPE_DISLIKE: return $lng->txt("dislike"); break;
            case ilLikeData::TYPE_LOVE: return $lng->txt("love"); break;
            case ilLikeData::TYPE_LAUGH: return $lng->txt("laugh"); break;
            case ilLikeData::TYPE_ASTOUNDED: return $lng->txt("astounded"); break;
            case ilLikeData::TYPE_SAD: return $lng->txt("sad"); break;
            case ilLikeData::TYPE_ANGRY: return $lng->txt("angry"); break;
        }
        return "";
    }
}
