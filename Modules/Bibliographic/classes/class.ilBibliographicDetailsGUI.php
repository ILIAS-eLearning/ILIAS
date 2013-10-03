<?php

require_once "./Modules/Bibliographic/classes/class.ilBibliographicEntry.php";

/**
 * Class ilBibliographicDetailsGUI
 * The detailled view on each entry
 */
class ilBibliographicDetailsGUI
{
    /**
     * @param ilObjBibliographic $bibl_obj
     * @return void
     *
     */
    public function showDetails(ilObjBibliographic $bibl_obj)
    {
        global $tpl, $ilTabs, $ilCtrl, $lng;

        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();


        $ilTabs->clearTargets();
        $ilTabs->setBackTarget("back", $ilCtrl->getLinkTarget($this, 'showContent'));


        $entry = new ilBibliographicEntry($bibl_obj->getFiletype(), $_GET['entryId']);


        $form->setTitle($lng->txt('detail_view'));

        foreach($entry->getAttributes() as $key => $attribute)
        {

            //Check if there is a specific language entry
            if($lng->exists($key))
            {
                $strDescTranslated = $lng->txt($key);
            }
            //If not: get the default language entry
            else
            {
                $arrKey = explode("_",$key);
                $strDescTranslated = $lng->txt($arrKey[0]."_default_".$arrKey[2]);
            }

            $ci = new ilCustomInputGUI($strDescTranslated);
            $ci->setHtml($attribute);
            $form->addItem($ci);

        }

        // set content and title
        $tpl->setContent($form->getHTML());

        //Permanent Link
        $tpl->setPermanentLink("bibl", $bibl_obj->getRefId(),"_".$_GET['entryId']);

    }



}

?>