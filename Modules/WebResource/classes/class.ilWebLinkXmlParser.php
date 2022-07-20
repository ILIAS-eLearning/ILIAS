<?php declare(strict_types=1);

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
 * XML  parser for weblink xml
 * @author  Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 * @ingroup ModulesWebResource
 */
class ilWebLinkXmlParser extends ilMDSaxParser
{
    protected const MODE_UNDEFINED = 0;
    public const MODE_UPDATE = 1;
    public const MODE_CREATE = 2;

    private ilObjLinkResource $webl;
    private int $mode = self::MODE_UNDEFINED;
    private bool $in_metadata = false;
    private array $sorting_positions = [];
    private string $cdata = '';

    private int $current_sorting_position = 0;
    private bool $current_link_update = false;
    private bool $current_link_delete = false;
    private array $current_parameters = [];
    private ?ilLinkResourceItems $current_link = null;

    /**
     * Constructor
     */
    public function __construct(ilObjLinkResource $webr, string $xml)
    {
        parent::__construct();
        $this->setXMLContent($xml);
        $this->setWebLink($webr);

        $this->setMDObject(
            new ilMD(
                $this->getWebLink()->getId(),
                $this->getWebLink()->getId(),
                'webr'
            )
        );
        $this->setThrowException(true);
    }

    /**
     * set weblink
     */
    public function setWebLink(ilObjLinkResource $webl) : void
    {
        $this->webl = $webl;
    }

    public function getWebLink() : ilObjLinkResource
    {
        return $this->webl;
    }

    public function setMode(int $a_mode) : void
    {
        $this->mode = $a_mode;
    }

    public function getMode() : int
    {
        return $this->mode;
    }

    public function start() : void
    {
        $this->startParsing();
    }

    /**
     * @inheritDoc
     */
    public function setHandlers($a_xml_parser) : void
    {
        xml_set_object($a_xml_parser, $this);
        xml_set_element_handler(
            $a_xml_parser,
            'handlerBeginTag',
            'handlerEndTag'
        );
        xml_set_character_data_handler($a_xml_parser, 'handlerCharacterData');
    }

    /**
     * @inheritDoc
     */
    public function handlerBeginTag(
        $a_xml_parser,
        string $a_name,
        array $a_attribs
    ) : void {
        global $DIC;

        if ($this->in_metadata) {
            parent::handlerBeginTag($a_xml_parser, $a_name, $a_attribs);
            return;
        }

        switch ($a_name) {
            case "MetaData":
                $this->in_metadata = true;

                // Delete old meta data
                $md = new ilMD($this->getWebLink()->getId(), 0, 'webr');
                $md->deleteAll();

                parent::handlerBeginTag($a_xml_parser, $a_name, $a_attribs);
                break;

            case 'WebLink':

                $this->current_sorting_position = (int) ($a_attribs['position'] ?: 0);
                $this->current_link_update = false;
                $this->current_link_delete = false;
                $this->current_parameters = [];

                if ($this->getMode(
                    ) == self::MODE_CREATE || isset($a_attribs['action']) && $a_attribs['action'] == 'Create') {
                    // New weblink
                    $this->current_link = new ilLinkResourceItems(
                        $this->getWebLink()->getId()
                    );
                } elseif ($this->getMode(
                    ) == self::MODE_UPDATE && $a_attribs['action'] == 'Delete') {
                    $this->current_link_delete = true;
                    $this->current_link = new ilLinkResourceItems(
                        $this->getWebLink()->getId()
                    );
                    $this->current_link->delete($a_attribs['id']);
                    break;
                } elseif ($this->getMode(
                    ) == self::MODE_UPDATE && ($a_attribs['action'] == 'Update' || !isset($a_attribs['action']))) {
                    $this->current_link = new ilLinkResourceItems(
                        $this->getWebLink()->getId()
                    );
                    $this->current_link->readItem($a_attribs['id']);
                    $this->current_link_update = true;
                    foreach (ilParameterAppender::getParameterIds(
                        $this->getWebLink()->getId(),
                        $a_attribs['id']
                    ) as $param_id) {
                        $param = new ilParameterAppender(
                            $this->getWebLink()->getId()
                        );
                        $param->delete($param_id);
                    }
                } else {
                    throw new ilWebLinkXmlParserException(
                        'Invalid action given for element "Weblink"'
                    );
                }

                // Active
                $this->current_link->setActiveStatus(
                    (bool) $a_attribs['active']
                );

                // internal
                if (isset($a_attribs['internal'])) {
                    $this->current_link->setInternal((bool) $a_attribs['internal']);
                }
                break;

            case 'Sorting':

                $sort = new ilContainerSortingSettings(
                    $this->getWebLink()->getId()
                );
                $sort->delete();

                switch ($a_attribs['type']) {
                    case 'Manual':
                        $sort->setSortMode(ilContainer::SORT_MANUAL);
                        break;

                    case 'Title':
                    default:
                        $sort->setSortMode(ilContainer::SORT_TITLE);
                }
                $sort->save();
                break;

            case 'WebLinks':
                $this->sorting_positions = array();
            // no break
            case 'Title':
            case 'Description':
            case 'Target':
                // Nothing to do
                break;

            case 'DynamicParameter':
                $param = new ilParameterAppender($this->getWebLink()->getId());
                $param->setName($a_attribs['name']);

                switch ($a_attribs['type']) {
                    case 'userName':
                        $param->setValue(ilParameterAppender::LINKS_LOGIN);
                        break;

                    case 'userId':
                        $param->setValue(ilParameterAppender::LINKS_USER_ID);
                        break;

                    case 'matriculation':
                        $param->setValue(
                            ilParameterAppender::LINKS_MATRICULATION
                        );
                        break;

                    default:
                        throw new ilWebLinkXmlParserException(
                            'Invalid attribute "type" given for element "Dynamic parameter". Aborting'
                        );
                }
                $this->current_parameters[] = $param;
                break;
        }
    }

    /**
     * @inheritDoc
     */
    public function handlerEndTag($a_xml_parser, string $a_name) : void
    {
        if ($this->in_metadata) {
            parent::handlerEndTag($a_xml_parser, $a_name);
        }

        switch ($a_name) {
            case 'MetaData':
                $this->in_metadata = false;
                parent::handlerEndTag($a_xml_parser, $a_name);
                break;

            case 'WebLinks':
                $this->getWebLink()->MDUpdateListener('General');
                $this->getWebLink()->update();

                // save sorting
                $sorting = ilContainerSorting::_getInstance(
                    $this->getWebLink()->getId()
                );
                $sorting->savePost($this->sorting_positions);
                ilLoggerFactory::getLogger('webr')->dump(
                    $this->sorting_positions
                );
                break;

            case 'WebLink':

                if ($this->current_link_delete) {
                    break;
                }
                if (!$this->current_link) {
                    throw new ilSaxParserException(
                        'Invalid xml structure given. Missing start tag "WebLink"'
                    );
                }
                if (!$this->current_link->validate()) {
                    throw new ilWebLinkXmlParserException(
                        'Missing required elements "Title, Target"'
                    );
                }

                if ($this->current_link_update) {
                    $this->current_link->update();
                } else {
                    $this->current_link->add();
                }

                // Save dynamic parameters
                foreach ($this->current_parameters as $param) {
                    $param->add($this->current_link->getLinkId());
                }

                // store positions
                $this->sorting_positions[$this->current_link->getLinkId(
                )] = $this->current_sorting_position;

                unset($this->current_link);
                break;

            case 'Title':
                if ($this->current_link) {
                    $this->current_link->setTitle(trim($this->cdata));
                }
                break;

            case 'Description':
                if ($this->current_link) {
                    $this->current_link->setDescription(trim($this->cdata));
                }
                break;

            case 'Target':
                if ($this->current_link) {
                    $this->current_link->setTarget(trim($this->cdata));
                }
                break;
        }

        // Reset cdata
        $this->cdata = '';
    }

    /**
     * @inheritDoc
     */
    public function handlerCharacterData($a_xml_parser, string $a_data) : void
    {
        if ($this->in_metadata) {
            parent::handlerCharacterData($a_xml_parser, $a_data);
        }

        if ($a_data != "\n") {
            // Replace multiple tabs with one space
            $a_data = preg_replace("/\t+/", " ", $a_data);
            $this->cdata .= $a_data;
        }
    }
}
