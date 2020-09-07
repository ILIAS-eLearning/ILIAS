<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/


/**
* Meta Data factory class
*
* @package ilias-core
* @version $Id$
*/

class ilMDFactory
{
    /*
     * get md element by index and type
     *
     * @param string type (name e.g meta_general,meta_language)
     *
     * @return MD object
     */
    public static function _getInstance($a_type, $a_index, $a_technical_id = 0)
    {
        switch ($a_type) {
            case 'meta_technical':
                include_once 'Services/MetaData/classes/class.ilMDTechnical.php';

                $tec = new ilMDTechnical();
                $tec->setMetaId($a_index);

                return $tec;

            case 'meta_or_composite':
                include_once 'Services/MetaData/classes/class.ilMDOrComposite.php';
                include_once 'Services/MetaData/classes/class.ilMDTechnical.php';

                $tec = new ilMDTechnical();
                $tec->setMetaId($a_technical_id);

                return $tec->getOrComposite($a_index);

            case 'meta_requirement':
                include_once 'Services/MetaData/classes/class.ilMDRequirement.php';

                $req = new ilMDRequirement();
                $req->setMetaId($a_index);

                return $req;
                

            case 'meta_location':
                include_once 'Services/MetaData/classes/class.ilMDLocation.php';

                $loc = new ilMDLocation();
                $loc->setMetaId($a_index);

                return $loc;

            case 'meta_format':
                include_once 'Services/MetaData/classes/class.ilMDFormat.php';

                $for = new ilMDFormat();
                $for->setMetaId($a_index);

                return $for;

            case 'meta_lifecycle':
                include_once 'Services/MetaData/classes/class.ilMDLifecycle.php';

                $lif = new ilMDLifecycle();
                $lif->setMetaId($a_index);

                return $lif;

            case 'meta_entity':
                include_once 'Services/MetaData/classes/class.ilMDEntity.php';

                $ent = new ilMDEntity();
                $ent->setMetaId($a_index);

                return $ent;

            case 'meta_contribute':
                include_once 'Services/MetaData/classes/class.ilMDContribute.php';

                $con = new ilMDContribute();
                $con->setMetaId($a_index);
                
                return $con;

            case 'meta_identifier':
                include_once 'Services/MetaData/classes/class.ilMDIdentifier.php';

                $ide = new ilMDIdentifier();
                $ide->setMetaId($a_index);
                
                return $ide;
            
            case 'educational_description':
            case 'meta_description':
                include_once 'Services/MetaData/classes/class.ilMDDescription.php';

                $des = new ilMDDescription();
                $des->setMetaId($a_index);
                
                return $des;

            case 'meta_keyword':
            case 'classification_keyword':
                include_once 'Services/MetaData/classes/class.ilMDKeyword.php';

                $key = new ilMDKeyword();
                $key->setMetaId($a_index);
                
                return $key;

            case 'educational_language':
            case 'meta_language':
                include_once 'Services/MetaData/classes/class.ilMDLanguage.php';

                $lan = new ilMDLanguage();
                $lan->setMetaId($a_index);

                return $lan;
                
            case 'meta_rights':
                include_once 'Services/MetaData/classes/class.ilMDRights.php';

                $rights = new ilMDRights();
                $rights->setMetaId($a_index);
                return $rights;

            case 'meta_educational':
                include_once 'Services/MetaData/classes/class.ilMDEducational.php';

                $edu = new ilMDEducational();
                $edu->setMetaId($a_index);
                return $edu;

            case 'educational_typical_age_range':
                include_once 'Services/MetaData/classes/class.ilMDTypicalAgeRange.php';

                $age = new ilMDTypicalAgeRange();
                $age->setMetaId($a_index);
                return $age;

            case 'meta_relation':
                include_once 'Services/MetaData/classes/class.ilMDRelation.php';

                $relation = new ilMDRelation();
                $relation->setMetaId($a_index);
                return $relation;
                
            case 'relation_resource_identifier':
                include_once 'Services/MetaData/classes/class.ilMDIdentifier_.php';

                $ide = new ilMDIdentifier_();
                $ide->setMetaId($a_index);
                
                return $ide;
                
            case 'relation_resource_description':
                include_once 'Services/MetaData/classes/class.ilMDDescription.php';

                $des = new ilMDDescription();
                $des->setMetaId($a_index);
                
                return $des;

            case 'meta_annotation':
                include_once 'Services/MetaData/classes/class.ilMDAnnotation.php';

                $anno = new ilMDAnnotation();
                $anno->setMetaId($a_index);
                return $anno;

            case 'meta_classification':
                include_once 'Services/MetaData/classes/class.ilMDClassification.php';

                $class = new ilMDClassification();
                $class->setMetaId($a_index);
                return $class;
                
            case 'classification_taxon_path':
                include_once 'Services/MetaData/classes/class.ilMDTaxonPath.php';

                $tax_path = new ilMDTaxonPath();

                $tax_path->setMetaId($a_index);
                return $tax_path;

            case 'classification_taxon':
                include_once 'Services/MetaData/classes/class.ilMDTaxon.php';

                $tax = new ilMDTaxon();
                $tax->setMetaId($a_index);
                return $tax;

            case 'meta_meta_metadata':
                include_once 'Services/MetaData/classes/class.ilMDMetaMetadata.php';

                $met = new ilMDMetaMetadata();
                $met->setMetaId($a_index);
                return $met;

            default:
                echo $a_type . " not known";
                
        }
    }
}
