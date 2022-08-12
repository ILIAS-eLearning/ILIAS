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
    private ilWebLinkRepository $web_link_repo;
    private int $mode = self::MODE_UNDEFINED;
    private bool $in_metadata = false;
    private array $sorting_positions = [];
    private string $cdata = '';

    private int $current_sorting_position = 0;
    private bool $current_item_create = false;
    private bool $current_item_update = false;
    private bool $current_item_delete = false;

    private ?int $current_link_id;
    private ?string $current_title;
    private ?string $current_target;
    private ?bool $current_active;
    /**
     * @var ilWebLinkDraftParameter[]
     */
    private array $current_parameters = [];
    private ?string $current_description;
    private ?bool $current_internal;

    public function __construct(ilObjLinkResource $webr, string $xml)
    {
        parent::__construct();
        $this->setXMLContent($xml);
        $this->setWebLink($webr);
        $this->web_link_repo = new ilWebLinkDatabaseRepository(
            $this->getWebLink()->getId()
        );

        $this->setMDObject(
            new ilMD(
                $this->getWebLink()->getId(),
                $this->getWebLink()->getId(),
                'webr'
            )
        );
        $this->setThrowException(true);
    }

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

    protected function resetStoredValues() : void
    {
        $this->current_item_create = false;
        $this->current_item_update = false;
        $this->current_item_delete = false;

        $this->current_link_id = null;
        $this->current_title = null;
        $this->current_target = null;
        $this->current_active = null;
        $this->current_parameters = [];
        $this->current_description = null;
        $this->current_internal = null;
    }

    public function start() : void
    {
        $this->startParsing();
    }

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
                $this->resetStoredValues();

                if (
                    $this->getMode() == self::MODE_CREATE ||
                    ($a_attribs['action'] ?? null) === 'Create'
                ) {
                    $this->current_item_create = true;
                } else {
                    if (!($a_attribs['id'] ?? false)) {
                        throw new ilWebLinkXmlParserException(
                            'Updating or deleting not possible, no id was given for element "Weblink"'
                        );
                    }
                    if (
                        $this->getMode() == self::MODE_UPDATE &&
                        ($a_attribs['action'] ?? null) === 'Delete'
                    ) {
                        $this->current_item_delete = true;
                        $this->web_link_repo->deleteItemByLinkId($a_attribs['id']);
                        break;
                    } elseif (
                        $this->getMode() == self::MODE_UPDATE &&
                        (!isset($a_attribs['action']) || $a_attribs['action'] == 'Update')
                    ) {
                        $this->current_link_id = $a_attribs['id'];
                        $this->current_item_update = true;
                    } else {
                        throw new ilWebLinkXmlParserException(
                            'Invalid action given for element "Weblink"'
                        );
                    }
                }

                // Active
                $this->current_active = (bool) $a_attribs['active'];

                // internal
                if (isset($a_attribs['internal'])) {
                    $this->current_internal = (bool) $a_attribs['internal'];
                }
                break;

            case 'Sorting':

                $sort = new ilContainerSortingSettings(
                    $this->getWebLink()->getId()
                );
                $sort->delete();

                switch ($a_attribs['type'] ?? null) {
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
                if (!($a_attribs['name'] ?? false)) {
                    throw new ilWebLinkXmlParserException(
                        'No attribute "name" given for element "Dynamic parameter". Aborting'
                    );
                }
                $name = $a_attribs['name'] ?? null;

                switch ($a_attribs['type'] ?? null) {
                    case 'userName':
                        $value = ilWebLinkBaseParameter::VALUES['login'];
                        break;

                    case 'userId':
                        $value = ilWebLinkBaseParameter::VALUES['user_id'];
                        break;

                    case 'matriculation':
                        $value = ilWebLinkBaseParameter::VALUES['matriculation'];
                        break;

                    default:
                        throw new ilWebLinkXmlParserException(
                            'Invalid attribute "type" given for element "Dynamic parameter". Aborting'
                        );
                }

                $param = new ilWebLinkDraftParameter($value, $name);
                if ($this->current_item_update && ($a_attribs['id'] ?? null)) {
                    $item = $this->web_link_repo->getItemByLinkId($this->current_link_id);
                    $old_param = $this->web_link_repo->getParameterinItemByParamId(
                        $item,
                        $a_attribs['id']
                    );
                    $param->replaces($old_param);
                }
                $this->current_parameters[] = $param;

                break;
        }
    }

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

                if ($this->current_item_delete) {
                    //Deletion is already handled in the begin tag.
                    break;
                }
                if (!$this->current_item_create && !$this->current_item_update) {
                    throw new ilSaxParserException(
                        'Invalid xml structure given. Missing start tag "WebLink"'
                    );
                }
                if (!$this->current_title || !$this->current_target) {
                    throw new ilWebLinkXmlParserException(
                        'Missing required elements "Title, Target"'
                    );
                }

                if ($this->current_item_update) {
                    $item = $this->web_link_repo->getItemByLinkId($this->current_link_id);
                    $draft = new ilWebLinkDraftItem(
                        $this->current_internal ?? $item->isInternal(),
                        $this->current_title ?? $item->getTitle(),
                        $this->current_description ?? $item->getDescription(),
                        $this->current_target ?? $item->getTarget(),
                        $this->current_active ?? $item->isActive(),
                        $this->current_parameters
                    );

                    $this->web_link_repo->updateItem($item, $draft);
                } else {
                    $draft = new ilWebLinkDraftItem(
                        $this->current_internal ?? ilLinkInputGUI::isInternalLink($this->current_target),
                        $this->current_title,
                        $this->current_description ?? null,
                        $this->current_target,
                        $this->current_active,
                        $this->current_parameters
                    );
                    $item = $this->web_link_repo->createItem($draft);
                }

                // store positions
                $this->sorting_positions[$item->getLinkId()] = $this->current_sorting_position;

                $this->resetStoredValues();
                break;

            case 'Title':
                $this->current_title = trim($this->cdata);
                break;

            case 'Description':
                $this->current_description = trim($this->cdata);
                break;

            case 'Target':
                $this->current_target = trim($this->cdata);
                break;
        }

        // Reset cdata
        $this->cdata = '';
    }

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
