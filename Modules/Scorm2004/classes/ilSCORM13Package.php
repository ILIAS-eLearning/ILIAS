<?php declare(strict_types=1);

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * @author Alex Killing <alex.killing@gmx.de>
 * @author Hendrik Holtmann <holtmann@mac.com>
 * @authro Alfred Kohnert <alfred.kohnert@bigfoot.com>
*/
class ilSCORM13Package
{
    const DB_ENCODE_XSL = './Modules/Scorm2004/templates/xsl/op/op-scorm13.xsl';
    const CONVERT_XSL = './Modules/Scorm2004/templates/xsl/op/scorm12To2004.xsl';
    const DB_DECODE_XSL = './Modules/Scorm2004/templates/xsl/op/op-scorm13-revert.xsl';
    const VALIDATE_XSD = './libs/ilias/Scorm2004/xsd/op/op-scorm13.xsd';
    
    const WRAPPER_HTML = './Modules/Scorm2004/scripts/converter/GenericRunTimeWrapper1.0_aadlc/GenericRunTimeWrapper.htm';
    const WRAPPER_JS = './Modules/Scorm2004/scripts/converter/GenericRunTimeWrapper1.0_aadlc/SCOPlayerWrapper.js';
    

//    private $packageFile;
    private string $packageFolder;
    private string $packagesFolder;
    private array $packageData = [];
//    private $slm;
//    private $slm_tree;

    public \DOMDocument $imsmanifest;
    /**
     * @var DOMDocument|bool
     */
    public $manifest;
    public array $diagnostic;
//    public $status;
    public int $packageId;
    public string $packageName = "";
    public string $packageHash = "";
    public int $userId;

//    private $idmap = array();
    private float $progress = 0.0;

    /**
     * @var string[][]
     */
    private static array $elements = array(
        'cp' => array(
            'manifest',
            'organization',
            'item',
            'hideLMSUI',
            'resource',
            'file',
            'dependency',
            'sequencing',
            'rule',
            'auxilaryResource',
            'condition',
            'mapinfo',
            'objective',
        ),
        'cmi' => array(
            'comment',
            'correct_response',
            'interaction',
            'node',
            'objective',
        ),
    );

    public function __construct(?int $packageId = null)
    {
        $this->packagesFolder = ''; // #25372
        if ($packageId != null) {
            $this->load($packageId);
        }
    }
    
    public function load(int $packageId) : void
    {
        global $DIC;
        $ilDB = $DIC->database();
        
        $lm_set = $ilDB->queryF('SELECT * FROM sahs_lm WHERE id = %s', array('integer'), array($packageId));
        $lm_data = $ilDB->fetchAssoc($lm_set);
        $pg_set = $ilDB->queryF('SELECT * FROM cp_package WHERE obj_id  = %s', array('integer'), array($packageId));
        $pg_data = $ilDB->fetchAssoc($lm_set);
        
        $this->packageData = array_merge($lm_data, $pg_data);
        $this->packageId = $packageId;
        $this->packageFolder = $this->packagesFolder . '/' . $packageId;
        $this->packageFile = $this->packageFolder . '.zip';
        $this->imsmanifestFile = $this->packageFolder . '/' . 'imsmanifest.xml';
    }

    /**
     * Imports an extracted SCORM 2004 module from ilias-data dir into database
     * @return string|false title of package or false
     * @throws ilSaxParserException
     */
    public function il_import(string $packageFolder, int $packageId, bool $reimport = false)
    {
        global $DIC;
        $ilDB = $DIC->database();
        $ilLog = ilLoggerFactory::getLogger('sc13');
        $ilErr = $DIC['ilErr'];
        
        $title = "";

        if ($reimport === true) {
            $this->packageId = $packageId;
            $this->dbRemoveAll();
        }

        $this->packageData['persistprevattempts'] = 0;
        $this->packageData['default_lesson_mode'] = 'normal';
        $this->packageData['credit'] = 'credit';
        $this->packageData['auto_review'] = 'n';

        $this->packageFolder = $packageFolder;
        $this->packageId = $packageId;
        $this->imsmanifestFile = $this->packageFolder . '/' . 'imsmanifest.xml';
        //step 1 - parse Manifest-File and validate
        $this->imsmanifest = new DOMDocument;
        $this->imsmanifest->async = false;
        if (!@$this->imsmanifest->load($this->imsmanifestFile)) {
            $this->diagnostic[] = 'XML not wellformed';
            return false;
        }

        //step 2 tranform
        $this->manifest = $this->transform($this->imsmanifest, self::DB_ENCODE_XSL);
  
        if (!$this->manifest) {
            $this->diagnostic[] = 'Cannot transform into normalized manifest';
            return false;
        }
        //setp 2.5 if only a single item, make sure the scormType of it's linked resource is SCO
        $path = new DOMXpath($this->manifest);
        $path->registerNamespace("scorm", "http://www.openpalms.net/scorm/scorm13");
        $items = $path->query("//scorm:item");
        if ($items->length == 1) {
            $n = $items->item(0);
            $resource = $path->query("//scorm:resource");//[&id='"+$n->getAttribute("resourceId")+"']");
            foreach ($resource as $res) {
                if ($n !== null && $res->getAttribute('id') == $n->getAttribute("resourceId")) {
                    $res->setAttribute('scormType', 'sco');
                }
            }
        }
        $this->dbImport($this->manifest);

        if (file_exists($this->packageFolder . '/' . 'index.xml')) {
            $doc = simplexml_load_file($this->packageFolder . '/' . 'index.xml');//PHP8Review: This may cause no trouble here but i still worth a look: https://bugs.php.net/bug.php?id=62577
            $l = $doc->xpath("/ContentObject/MetaData");
            if ($l[0]) {
                $mdxml = new ilMDXMLCopier($l[0]->asXML(), $packageId, $packageId, ilObject::_lookupType($packageId));
                $mdxml->startParsing();
                $mdo = $mdxml->getMDObject();
                if ($mdo) {
                    $mdo->update();
                }
            }
        } else {
            $importer = new ilSCORM13MDImporter($this->imsmanifest, $packageId);
            $importer->import();
            $title = $importer->getTitle();
            $description = $importer->getDescription();
            if ($description != "") {
                ilObject::_writeDescription($packageId, $description);
            }
        }

        //step 5
        $x = simplexml_load_string($this->manifest->saveXML());
        $x['persistPreviousAttempts'] = $this->packageData['persistprevattempts'];
        // $x['online'] = !$this->getOfflineStatus();//$this->packageData['c_online'];
        
        $x['defaultLessonMode'] = $this->packageData['default_lesson_mode'];
        $x['credit'] = $this->packageData['credit'];
        $x['autoReview'] = $this->packageData['auto_review'];
        $j = array();
        // first read resources into flat array to resolve item/identifierref later
        $r = array();
        foreach ($x->resource as $xe) {
            $r[strval($xe['id'])] = $xe;
            unset($xe);
        }
        // iterate through items and set href and scoType as activity attributes
        foreach ($x->xpath('//*[local-name()="item"]') as $xe) {
            // get reference to resource and set href accordingly
            if ($b = $r[strval($xe['resourceId'])]) {
                $xe['href'] = strval($b['base']) . strval($b['href']);
                unset($xe['resourceId']);
                if (strval($b['scormType']) === 'sco') {
                    $xe['sco'] = true;
                }
            }
        }
        // iterate recursivly through activities and build up simple php object
        // with items and associated sequencings
        // top node is the default organization which is handled as an item
        $this->jsonNode($x->organization, $j['item']);
        foreach ($x->sequencing as $s) {
            $this->jsonNode($s, $j['sequencing'][]);
        }
        // combined manifest+resources xml:base is set as organization base
        $j['item']['base'] = strval($x['base']);
        // package folder is base to whole playing process
        $j['base'] = $packageFolder . '/';
        $j['foreignId'] = floatval($x['foreignId']); // manifest cp_node_id for associating global (package wide) objectives
        $j['id'] = strval($x['id']); // manifest id for associating global (package wide) objectives
    

        //last step - build ADL Activity tree
        $act = new SeqTreeBuilder();
        $adl_tree = $act->buildNodeSeqTree($this->imsmanifestFile);
        $ilDB->update(
            'cp_package',
            array(
                'xmldata' => array('clob', $x->asXML()),
                'jsdata' => array('clob', json_encode($j)),
                'activitytree' => array('clob', json_encode($adl_tree['tree'])),
                'global_to_system' => array('integer', (int) $adl_tree['global']),
                'shared_data_global_to_system' => array('integer', (int) $adl_tree['dataglobal'])
            ),
            array(
                'obj_id' => array('integer', (int) $this->packageId)
            )
        );

        // title retrieved by importer
        if ($title != "") {
            return $title;
        }

        return $j['item']['title'];
    }
    
    
    /**
     * Helper for UploadAndImport
     * Recursively copies values from XML into PHP array for export as json
     * Elements are translated into sub array, attributes into literals
     * xml element to process
     * reference to array object where to copy values
     */
    public function jsonNode(object $node, ?array &$sink) : void
    {
        foreach ($node->attributes() as $k => $v) {
            // cast to boolean and number if possible
            $v = strval($v);
            if ($v === "true") {
                $v = true;
            } elseif ($v === "false") {
                $v = false;
            } elseif (is_numeric($v)) {
                $v = (float) $v;
            }
            $sink[$k] = $v;
        }
        foreach ($node->children() as $name => $child) {
            self::jsonNode($child, $sink[$name][]); // RECURSION
        }
    }

    public function dbImport(object $node, ?int &$lft = 1, ?int $depth = 1, ?int $parent = 0) : void
    {
        global $DIC;
        $ilDB = $DIC->database();
        
        switch ($node->nodeType) {
            case XML_DOCUMENT_NODE:

                // insert into cp_package

                $res = $ilDB->queryF(
                    'SELECT * FROM cp_package WHERE obj_id = %s AND c_identifier = %s',
                    array('integer', 'text'),
                    array($this->packageId, $this->packageName)
                );
                if ($num_rows = $ilDB->numRows($res)) {
                    $query = 'UPDATE cp_package '
                           . 'SET persistprevattempts = %s, c_settings = %s '
                           . 'WHERE obj_id = %s AND c_identifier= %s';
                    $ilDB->manipulateF(
                        $query,
                        array('integer', 'text', 'integer', 'text'),
                        array(0, null, $this->packageId, $this->packageName)
                    );
                } else {
                    $query = 'INSERT INTO cp_package (obj_id, c_identifier, persistprevattempts, c_settings) '
                           . 'VALUES (%s, %s, %s, %s)';
                    $ilDB->manipulateF(
                        $query,
                        array('integer','text','integer', 'text'),
                        array($this->packageId, $this->packageName, 0, null)
                    );
                }
                
                // run sub nodes
                $this->dbImport($node->documentElement); // RECURSION
                break;

            case XML_ELEMENT_NODE:
                if ($node->nodeName === 'manifest') {
                    if ($node->getAttribute('uri') == "") {
                        // default URI is md5 hash of zip file, i.e. packageHash
                        $node->setAttribute('uri', 'md5:' . $this->packageHash);
                    }
                }

                $cp_node_id = $ilDB->nextId('cp_node');
                
                $query = 'INSERT INTO cp_node (cp_node_id, slm_id, nodename) '
                       . 'VALUES (%s, %s, %s)';
                $ilDB->manipulateF(
                    $query,
                    array('integer', 'integer', 'text'),
                    array($cp_node_id, $this->packageId, $node->nodeName)
                );
                
                $query = 'INSERT INTO cp_tree (child, depth, lft, obj_id, parent, rgt) '
                       . 'VALUES (%s, %s, %s, %s, %s, %s)';
                $ilDB->manipulateF(
                    $query,
                    array('integer', 'integer', 'integer', 'integer', 'integer', 'integer'),
                    array($cp_node_id, $depth, $lft++, $this->packageId, $parent, 0)
                );

                // insert into cp_*
                //$a = array('cp_node_id' => $cp_node_id);
                $names = array('cp_node_id');
                $values = array($cp_node_id);
                $types = array('integer');
        
                foreach ($node->attributes as $attr) {
                    switch (strtolower($attr->name)) {
                        case 'completionsetbycontent': $names[] = 'completionbycontent';break;
                        case 'objectivesetbycontent': $names[] = 'objectivebycontent';break;
                        case 'type': $names[] = 'c_type';break;
                        case 'mode': $names[] = 'c_mode';break;
                        case 'language': $names[] = 'c_language';break;
                        case 'condition': $names[] = 'c_condition';break;
                        case 'operator': $names[] = 'c_operator';break;
//                        case 'condition': $names[] = 'c_condition';break;
                        case 'readnormalizedmeasure': $names[] = 'readnormalmeasure';break;
                        case 'writenormalizedmeasure': $names[] = 'writenormalmeasure';break;
                        case 'minnormalizedmeasure': $names[] = 'minnormalmeasure';break;
                        case 'primary': $names[] = 'c_primary';break;
//                        case 'minnormalizedmeasure': $names[] = 'minnormalmeasure';break;
                        case 'persistpreviousattempts': $names[] = 'persistprevattempts';break;
                        case 'identifier': $names[] = 'c_identifier';break;
                        case 'settings': $names[] = 'c_settings';break;
                        case 'activityabsolutedurationlimit': $names[] = 'activityabsdurlimit';break;
                        case 'activityexperienceddurationlimit': $names[] = 'activityexpdurlimit';break;
                        case 'attemptabsolutedurationlimit': $names[] = 'attemptabsdurlimit';break;
                        case 'measuresatisfactionifactive': $names[] = 'measuresatisfactive';break;
                        case 'objectivemeasureweight': $names[] = 'objectivemeasweight';break;
                        case 'requiredforcompleted': $names[] = 'requiredcompleted';break;
                        case 'requiredforincomplete': $names[] = 'requiredincomplete';break;
                        case 'requiredfornotsatisfied': $names[] = 'requirednotsatisfied';break;
                        case 'rollupobjectivesatisfied': $names[] = 'rollupobjectivesatis';break;
                        case 'rollupprogresscompletion': $names[] = 'rollupprogcompletion';break;
                        case 'usecurrentattemptobjectiveinfo': $names[] = 'usecurattemptobjinfo';break;
                        case 'usecurrentattemptprogressinfo': $names[] = 'usecurattemptproginfo';break;
                        default: $names[] = strtolower($attr->name);break;
                    }
                    
                    if (in_array(
                        $names[count($names) - 1],
                        array('flow', 'completionbycontent',
                                      'objectivebycontent', 'rollupobjectivesatis',
                                      'tracked', 'choice',
                                      'choiceexit', 'satisfiedbymeasure',
                                      'c_primary', 'constrainchoice',
                                      'forwardonly', 'global_to_system',
                                      'writenormalmeasure', 'writesatisfiedstatus',
                                      'readnormalmeasure', 'readsatisfiedstatus',
                                      'preventactivation', 'measuresatisfactive',
                                      'reorderchildren', 'usecurattemptproginfo',
                                      'usecurattemptobjinfo', 'rollupprogcompletion',
                                      'read_shared_data', 'write_shared_data',
                                      'shared_data_global_to_system', 'completedbymeasure')
                    )) {
                        if ($attr->value === 'true') {
                            $values[] = 1;
                        } elseif ($attr->value === 'false') {
                            $values[] = 0;
                        } else {
                            $values[] = (int) $attr->value;
                        }
                    } else {
                        $values[] = $attr->value;
                    }
                                        
                    if (in_array(
                        $names[count($names) - 1],
                        array('objectivesglobtosys', 'attemptlimit',
                                       'flow', 'completionbycontent',
                                       'objectivebycontent', 'rollupobjectivesatis',
                                       'tracked', 'choice',
                                       'choiceexit', 'satisfiedbymeasure',
                                       'c_primary', 'constrainchoice',
                                       'forwardonly', 'global_to_system',
                                       'writenormalmeasure', 'writesatisfiedstatus',
                                       'readnormalmeasure', 'readsatisfiedstatus',
                                       'preventactivation', 'measuresatisfactive',
                                       'reorderchildren', 'usecurattemptproginfo',
                                       'usecurattemptobjinfo', 'rollupprogcompletion',
                                       'read_shared_data', 'write_shared_data',
                                       'shared_data_global_to_system')
                    )) {
                        $types[] = 'integer';
                    } elseif (in_array(
                        $names[count($names) - 1],
                        array('jsdata', 'xmldata', 'activitytree', 'data')
                    )) {
                        $types[] = 'clob';
                    } elseif ($names[count($names) - 1] === 'objectivemeasweight') {
                        $types[] = 'float';
                    } else {
                        $types[] = 'text';
                    }
                }
                
                if ($node->nodeName === 'datamap') {
                    $names[] = 'slm_id';
                    $values[] = $this->packageId;
                    $types[] = 'integer';
                    
                    $names[] = 'sco_node_id';
                    $values[] = $parent;
                    $types[] = 'integer';
                }
                
                // we have to change the insert method because of clob fields ($ilDB->manipulate does not work here)
                $insert_data = array();
                foreach ($names as $key => $db_field) {
                    $insert_data[$db_field] = array($types[$key], trim((string) $values[$key]));
                }
                $ilDB->insert('cp_' . strtolower($node->nodeName), $insert_data);
    
                $node->setAttribute('foreignId', (string) $cp_node_id);
                $this->idmap[$node->getAttribute('id')] = $cp_node_id;

                // run sub nodes
                foreach ($node->childNodes as $child) {
                    $this->dbImport($child, $lft, $depth + 1, $cp_node_id); // RECURSION
                }

                // update cp_tree (rgt value for pre order walk in sql tree)
                $query = 'UPDATE cp_tree SET rgt = %s WHERE child = %s';
                $ilDB->manipulateF(
                    $query,
                    array('integer', 'integer'),
                    array($lft++, $cp_node_id)
                );
        
                break;
        }
    }


    public function removeCMIData() : void
    {
        ilSCORM2004DeleteData::removeCMIDataForPackage($this->packageId);
        ilLPStatusWrapper::_refreshStatus($this->packageId);
    }
    
    public function removeCPData() : void
    {
        global $DIC;
        $ilDB = $DIC->database();
        $ilLog = ilLoggerFactory::getLogger('sc13');
        
        //get relevant nodes
        $cp_nodes = array();
        
        $res = $ilDB->queryF(
            'SELECT cp_node.cp_node_id FROM cp_node WHERE cp_node.slm_id = %s',
            array('integer'),
            array($this->packageId)
        );
        while ($data = $ilDB->fetchAssoc($res)) {
            $cp_nodes[] = $data['cp_node_id'];
        }
        
        //remove package data
        foreach (self::$elements['cp'] as $t) {
            $t = 'cp_' . $t;
            
            $in = $ilDB->in(strtolower($t) . '.cp_node_id', $cp_nodes, false, 'integer');
            $ilDB->manipulate('DELETE FROM ' . strtolower($t) . ' WHERE ' . $in);
        }
        
        // remove CP structure entries in tree and node
        $ilDB->manipulateF(
            'DELETE FROM cp_tree WHERE cp_tree.obj_id = %s',
            array('integer'),
            array($this->packageId)
        );

        $ilDB->manipulateF(
            'DELETE FROM cp_node WHERE cp_node.slm_id = %s',
            array('integer'),
            array($this->packageId)
        );
        
        // remove general package entry
        $ilDB->manipulateF(
            'DELETE FROM cp_package WHERE cp_package.obj_id = %s',
            array('integer'),
            array($this->packageId)
        );
    }

    public function dbRemoveAll() : void
    {
        //dont change order of calls
        $this->removeCMIData();
        $this->removeCPData();
    }

    /**
     * @return DOMDocument|false|void
     */
    public function transform(\DOMDocument $inputdoc, string $xslfile, ?string $outputpath = null)
    {
        $xsl = new DOMDocument;
        $xsl->async = false;
        if (!@$xsl->load($xslfile)) {
            die('ERROR: load StyleSheet ' . $xslfile);
        }
        $prc = new XSLTProcessor;
        $prc->registerPHPFunctions();
        $r = @$prc->importStyleSheet($xsl);
        if (false === @$prc->importStyleSheet($xsl)) {
            die('ERROR: importStyleSheet ' . $xslfile);
        }
        if ($outputpath) {
            file_put_contents($outputpath, $prc->transformToXML($inputdoc));
        } else {
            return $prc->transformToDoc($inputdoc);
        }
    }

    //to be called from IlObjUser
    public static function _removeTrackingDataForUser(int $user_id) : void
    {
        ilSCORM2004DeleteData::removeCMIDataForUser($user_id);
    }
}
