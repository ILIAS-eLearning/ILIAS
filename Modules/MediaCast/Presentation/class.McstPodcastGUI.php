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
 * Podcast GUI for mediacasts
 *
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls McstPodcastGUI: ilMediaObjectsPlayerWrapperGUI
 */
class McstPodcastGUI
{
    protected ilMediaObjectsPlayerWrapperGUI $player_wrapper;
    protected ilCtrl $ctrl;
    protected \ilObjMediaCast $media_cast;
    protected ilGlobalTemplateInterface $tpl;
    protected \ILIAS\DI\UIServices $ui;
    protected \ilLanguage $lng;
    protected \ilObjUser $user;

    public function __construct(
        \ilObjMediaCast $obj,
        ilGlobalTemplateInterface $tpl = null
    ) {
        global $DIC;

        $this->ui = $DIC->ui();
        $this->lng = $DIC->language();
        $this->media_cast = $obj;
        $this->tpl = $tpl;
        $this->user = $DIC->user();
        $this->ctrl = $DIC->ctrl();
        $this->player_wrapper = $DIC->mediaObjects()
            ->internal()
            ->gui()
            ->player()
            ->wrapper();
    }

    /**
     * @throws ilCtrlException
     */
    public function executeCommand(): void
    {
        $ctrl = $this->ctrl;

        $next_class = $ctrl->getNextClass($this);
        $cmd = $ctrl->getCmd("show");

        switch ($next_class) {
            case strtolower(ilMediaObjectsPlayerWrapperGUI::class):
                $ctrl->forwardCommand($this->player_wrapper);
                break;

            default:
                if (in_array($cmd, array(""))) {
                    $this->$cmd();
                }
        }
    }

    public function getHTML(): string
    {
        $f = $this->ui->factory();
        $renderer = $this->ui->renderer();
        $ctrl = $this->ctrl;
        $lng = $this->lng;

        $items = [];
        foreach ($this->media_cast->getSortedItemsArray() as $med_item) {
            $mob = new \ilObjMediaObject($med_item["mob_id"]);

            $audio = $this->player_wrapper->audio(
                $mob,
                $this->media_cast->getRefId()
            );

            if (is_null($audio)) {
                continue;
            }

            $item = $f->item()->standard($mob->getTitle())
                ->withAudioPlayer($audio)
                ->withProperties([$this->lng->txt("mcst_duration") => $med_item["playtime"]])
                ->withDescription($mob->getDescription());

            // $f->image()->responsive($mob->getVideoPreviewPic(), "")
            if ($mob->getVideoPreviewPic() != "") {
                $item = $item->withLeadImage(
                    $f->image()->responsive($mob->getVideoPreviewPic(), "")
                );
            }

            if ($this->media_cast->getDownloadable()) {
                $ctrl->setParameterByClass("ilobjmediacastgui", "item_id", $med_item["id"]);
                $ctrl->setParameterByClass("ilobjmediacastgui", "purpose", "Standard");
                $download = $ctrl->getLinkTargetByClass("ilobjmediacastgui", "downloadItem");
                $actions = $f->dropdown()->standard(array(
                    $f->button()->shy($lng->txt("download"), $download),
                ));
                $item = $item->withActions($actions);
            }


            $items[] = $item;
        }

        $list = $f->panel()->listing()->standard(
            $this->lng->txt("mcst_audio_files"),
            [
            $f->item()->group("", $items)
            ]
        );

        return $renderer->render($list);
    }
}
