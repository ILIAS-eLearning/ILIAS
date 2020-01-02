<?php

/**
 * Class ilBiblEntryTablePresentationGUI
 *
 * @author     Fabian Schmid <fs@studer-raimann.ch>
 * @version    1.0.0
 */
class ilBiblEntryTablePresentationGUI
{

    /**
     * @var \ilBiblEntry
     */
    protected $entry;
    /**
     * @var string
     */
    protected $html = '';
    /**
     * @var ilBiblFactoryFacadeInterface
     */
    protected $facade;


    /**
     * ilBiblEntryTablePresentationGUI constructor.
     *
     * @param \ilBiblEntry $entry
     */
    public function __construct(ilBiblEntry $entry, ilBiblFactoryFacadeInterface $facade)
    {
        $this->entry = $entry;
        $this->facade = $facade;
        $this->render();
    }


    /**
     * @deprecated Has to be refactored. Active records verwenden statt array
     *
     * @return mixed|string
     */
    protected function render()
    {
        $attributes = $this->facade->entryFactory()->loadParsedAttributesByEntryId($this->getEntry()->getId());
        //Get the model which declares which attributes to show in the overview table and how to show them
        //example for overviewModels: $overviewModels['bib']['default'] => "[<strong>|bib_default_author|</strong>: ][|bib_default_title|. ]<Emph>[|bib_default_publisher|][, |bib_default_year|][, |bib_default_address|].</Emph>"
        $overviewModels = $this->facade->overviewModelFactory()->getAllOverviewModelsByType($this->facade->type());
        //get design for specific entry type or get filetypes default design if type is not specified
        $entryType = $this->getEntry()->getType();
        //if there is no model for the specific entrytype (book, article, ....) the entry overview will be structured by the default entrytype from the given filetype (ris, bib, ...)
        if (!$overviewModels[$this->facade->typeFactory()->getDataTypeIdentifierByInstance($this->facade->entryFactory()->getFileType())][$entryType]) {
            $entryType = 'default';
        }
        $single_entry = $overviewModels[$entryType];
        //split the model into single attributes (which begin and end with a bracket, eg [|bib_default_title|. ] )
        //such values are saved in $placeholders[0] while the same values but whithout brackets are saved in $placeholders[1] (eg |bib_default_title|.  )
        preg_match_all('/\[(.*?)\]/', $single_entry, $placeholders);
        foreach ($placeholders[1] as $key => $placeholder) {
            //cut a moedel attribute like |bib_default_title|. in three pieces while $cuts[1] is the attribute key for the actual value and $cuts[0] is what comes before respectively $cuts[2] is what comes after the value if it is not empty.
            $cuts = explode('|', $placeholder);
            //if attribute key does not exist, because it comes from the default entry (e.g. ris_default_u2), we replace 'default' with the entrys type (e.g. ris_book_u2)
            if (!$attributes[$cuts[1]]) {
                $attribute_elements = explode('_', $cuts[1]);
                $attribute_elements[1] = strtolower($this->getEntry()->getType());
                $cuts[1] = implode('_', $attribute_elements);
            }
            if ($attributes[$cuts[1]]) {
                //if the attribute for the attribute key exists, replace one attribute in the overview text line of a single entry with its actual value and the text before and after the value given by the model
                $single_entry = str_replace($placeholders[0][$key], $cuts[0] . $attributes[$cuts[1]]
                                                                    . $cuts[2], $single_entry);
                // replace the <emph> tags with a span, in order to make text italic by css
                do {
                    $first_sign_after_begin_emph_tag = strpos(strtolower($single_entry), '<emph>')
                                                       + 6;
                    $last_sign_after_end_emph_tag = strpos(strtolower($single_entry), '</emph>');
                    $italic_text_length = $last_sign_after_end_emph_tag
                                          - $first_sign_after_begin_emph_tag;
                    //would not be true if there is no <emph> tag left
                    if ($last_sign_after_end_emph_tag) {
                        $italic_text = substr($single_entry, $first_sign_after_begin_emph_tag, $italic_text_length);
                        //parse
                        $it_tpl = new ilTemplate("tpl.bibliographic_italicizer.html", true, true, "Modules/Bibliographic");
                        $it_tpl->setCurrentBlock("italic_section");
                        $it_tpl->setVariable('ITALIC_STRING', $italic_text);
                        $it_tpl->parseCurrentBlock();
                        //replace the emph tags and the text between with the parsed text from il_tpl
                        $text_before_emph_tag = substr($single_entry, 0, $first_sign_after_begin_emph_tag
                                                                         - 6);
                        $text_after_emph_tag = substr($single_entry, $last_sign_after_end_emph_tag
                                                                     + 7);
                        $single_entry = $text_before_emph_tag . $it_tpl->get()
                                        . $text_after_emph_tag;
                    }
                } while ($last_sign_after_end_emph_tag);
            } else {
                //if the attribute for the attribute key does not exist, just remove this attribute-key from the overview text line of a single entry
                $single_entry = str_replace($placeholders[0][$key], '', $single_entry);
            }
        }
        $this->setHtml($single_entry);
    }


    /**
     * @return string
     */
    public function getHtml()
    {
        return $this->html;
    }


    /**
     * @param string $html
     */
    public function setHtml($html)
    {
        $this->html = $html;
    }


    /**
     * @return ilBiblEntry
     */
    public function getEntry()
    {
        return $this->entry;
    }


    /**
     * @param ilBiblEntry $entry
     */
    public function setEntry($entry)
    {
        $this->entry = $entry;
    }
}
