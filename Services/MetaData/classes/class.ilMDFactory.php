<?php declare(strict_types=1);
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
 * @package ilias-core
 * @version $Id$
 */
class ilMDFactory
{
    /**
     * @return null|ilMDTechnical|ilMDTechnical|ilMDRequirement|ilMDLocation|ilMDFormat|ilMDLifecycle|ilMDEntity|ilMDContribute|ilMDIdentifier|ilMDDescription|ilMDKeyword|ilMDLanguage|ilMDRights|ilMDEducational|ilMDTypicalAgeRange|ilMDRelation|ilMDIdentifier_|ilMDAnnotation|ilMDClassification|ilMDTaxonPath|ilMDTaxon|ilMDMetaMetadata
     */
    public static function _getInstance(string $a_type, int $a_index, ?int $a_technical_id = 0) : ?object
    {
        switch ($a_type) {
            case 'meta_technical':

                $tec = new ilMDTechnical();
                $tec->setMetaId($a_index);

                return $tec;

            case 'meta_or_composite':

                $tec = new ilMDTechnical();
                $tec->setMetaId($a_technical_id);

                return $tec->getOrComposite($a_index);

            case 'meta_requirement':

                $req = new ilMDRequirement();
                $req->setMetaId($a_index);

                return $req;

            case 'meta_location':

                $loc = new ilMDLocation();
                $loc->setMetaId($a_index);

                return $loc;

            case 'meta_format':

                $for = new ilMDFormat();
                $for->setMetaId($a_index);

                return $for;

            case 'meta_lifecycle':

                $lif = new ilMDLifecycle();
                $lif->setMetaId($a_index);

                return $lif;

            case 'meta_entity':

                $ent = new ilMDEntity();
                $ent->setMetaId($a_index);

                return $ent;

            case 'meta_contribute':

                $con = new ilMDContribute();
                $con->setMetaId($a_index);

                return $con;

            case 'meta_identifier':

                $ide = new ilMDIdentifier();
                $ide->setMetaId($a_index);

                return $ide;

            case 'educational_description':
            case 'meta_description':

                $des = new ilMDDescription();
                $des->setMetaId($a_index);

                return $des;

            case 'meta_keyword':
            case 'classification_keyword':

                $key = new ilMDKeyword();
                $key->setMetaId($a_index);

                return $key;

            case 'educational_language':
            case 'meta_language':

                $lan = new ilMDLanguage();
                $lan->setMetaId($a_index);

                return $lan;

            case 'meta_rights':

                $rights = new ilMDRights();
                $rights->setMetaId($a_index);
                return $rights;

            case 'meta_educational':

                $edu = new ilMDEducational();
                $edu->setMetaId($a_index);
                return $edu;

            case 'educational_typical_age_range':

                $age = new ilMDTypicalAgeRange();
                $age->setMetaId($a_index);
                return $age;

            case 'meta_relation':

                $relation = new ilMDRelation();
                $relation->setMetaId($a_index);
                return $relation;

            case 'relation_resource_identifier':

                $ide = new ilMDIdentifier_();
                $ide->setMetaId($a_index);

                return $ide;

            case 'relation_resource_description':

                $des = new ilMDDescription();
                $des->setMetaId($a_index);

                return $des;

            case 'meta_annotation':

                $anno = new ilMDAnnotation();
                $anno->setMetaId($a_index);
                return $anno;

            case 'meta_classification':

                $class = new ilMDClassification();
                $class->setMetaId($a_index);
                return $class;

            case 'classification_taxon_path':

                $tax_path = new ilMDTaxonPath();

                $tax_path->setMetaId($a_index);
                return $tax_path;

            case 'classification_taxon':

                $tax = new ilMDTaxon();
                $tax->setMetaId($a_index);
                return $tax;

            case 'meta_meta_metadata':

                $met = new ilMDMetaMetadata();
                $met->setMetaId($a_index);
                return $met;

            default:
                echo $a_type . " not known";
                return null;
        }
    }
}
