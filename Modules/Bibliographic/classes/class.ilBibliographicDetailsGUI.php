<?php

require_once "./Modules/Bibliographic/classes/class.ilBibliographicEntry.php";

/**
 * Class ilBibliographicDetailsGUI
 * The detailled view on each entry
 */
class ilBibliographicDetailsGUI
{
    /**
     * @param $bibl_obj
     * @return void
     *
     */
    public function showDetails($bibl_obj)
    {
        global $tpl, $ilTabs, $ilCtrl, $lng;

        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();


        $ilTabs->clearTargets();
        $ilTabs->setBackTarget("back", $ilCtrl->getLinkTarget($this, 'showContent'));


        $entry = new ilBibliographicEntry($bibl_obj->getFiletype(), $_GET['entryId']);


        $form->setTitle($entry->getOverwiew());

        foreach($entry->getAttributes() as $key => $attribute){

            $ci = new ilCustomInputGUI($lng->txt($key));
            $ci->setHtml($attribute);
            $form->addItem($ci);

        }


        // set content and title
        $tpl->setContent($form->getHTML());
    }



}

?>