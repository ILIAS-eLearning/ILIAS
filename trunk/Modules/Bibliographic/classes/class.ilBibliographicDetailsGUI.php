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

        $form->setTitle($lng->txt('detail_view'));


        $entry = new ilBibliographicEntry($bibl_obj->getFiletype(), $_GET['entryId']);
        $attributes = $entry->getAttributes();

        //translate array key in order to sort by those keys
        foreach($attributes as $key => $attribute)
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
            unset($attributes[$key]);
            $attributes[$strDescTranslated] = $attribute;
        }

        // sort attributes alphabetically by their array-key
        ksort($attributes, SORT_STRING);

        // render attributes to html
        foreach($attributes as $key => $attribute)
        {
            $ci = new ilCustomInputGUI($key);
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