<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_vrml extends HFile{
   function HFile_vrml(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// VRML
/*************************************/
// Flags

$this->nocase            	= "0";
$this->notrim            	= "0";
$this->perl              	= "0";

// Colours

$this->colours        	= array("blue", "purple", "gray", "brown", "blue");
$this->quotecolour       	= "blue";
$this->blockcommentcolour	= "green";
$this->linecommentcolour 	= "green";

// Indent Strings

$this->indent            	= array("{", "[");
$this->unindent          	= array("}", "]");

// String characters and delimiters

$this->stringchars       	= array();
$this->delimiters        	= array("~", "!", "@", "%", "^", "&", "*", "(", ")", "-", "+", "=", "|", "\\", "/", "{", "}", "[", "]", ":", ";", "\"", "'", "<", ">", " ", ",", "	", ".", "?");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("#");
$this->blockcommenton    	= array("");
$this->blockcommentoff   	= array("");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"AsciiText" => "1", 
			"Box" => "1", 
			"box" => "1", 
			"Cone" => "1", 
			"Cube" => "1", 
			"Cylinder" => "1", 
			"cone" => "1", 
			"cube" => "1", 
			"cylinder" => "1", 
			"Group" => "1", 
			"IndexedFaceSet" => "1", 
			"IndexedLineSet" => "1", 
			"Info" => "2", 
			"LOD" => "1", 
			"PointSet" => "1", 
			"Separator" => "1", 
			"Sphere" => "1", 
			"Switch" => "1", 
			"sphere" => "1", 
			"WWWAnchor" => "1", 
			"Appearance" => "2", 
			"Coordinate3" => "2", 
			"DirectionalLight" => "2", 
			"FontStyle" => "2", 
			"Material" => "2", 
			"MaterialBinding" => "2", 
			"MatrixTransform" => "2", 
			"Normal" => "2", 
			"NormalBinding" => "2", 
			"OrthographicCamera" => "2", 
			"PerspectiveCamera" => "2", 
			"PointLight" => "2", 
			"Rotation" => "2", 
			"Scale" => "2", 
			"Shape" => "2", 
			"ShapeHints" => "2", 
			"SpotLight" => "2", 
			"Texture2" => "2", 
			"Texture2Transform" => "2", 
			"TextureCoordinate2" => "2", 
			"Transform" => "2", 
			"Translation" => "2", 
			"WWWInline" => "2", 
			"Approach" => "3", 
			"Arches" => "3", 
			"Beauty" => "3", 
			"BitMask" => "3", 
			"Bool" => "3", 
			"Cameras" => "3", 
			"Closeup" => "3", 
			"Color" => "3", 
			"Enum" => "3", 
			"Float" => "3", 
			"Guide" => "3", 
			"Image" => "3", 
			"Inside" => "3", 
			"Long" => "3", 
			"Matrix" => "3", 
			"SceneInfo" => "3", 
			"String" => "3", 
			"Title" => "3", 
			"Vec2f" => "3", 
			"Vec3f" => "3", 
			"Viewer" => "3", 
			"ViewerSpeed" => "3", 
			"ALL" => "4", 
			"AUTO" => "4", 
			"BINDINGS" => "4", 
			"BOLD" => "4", 
			"BOTTOM" => "4", 
			"CENTER" => "4", 
			"CLAMP" => "4", 
			"CLOCKWISE" => "4", 
			"CONVEX" => "4", 
			"COUNTERCLOCKWISE" => "4", 
			"CULLING" => "4", 
			"DEF" => "4", 
			"DEFAULT" => "4", 
			"DEFAULTS" => "4", 
			"ENUMS" => "4", 
			"FACE" => "4", 
			"FAMILY" => "4", 
			"FILE" => "4", 
			"FORMAT" => "4", 
			"ITALIC" => "4", 
			"JUSTIFICATION" => "4", 
			"LEFT" => "4", 
			"NONE" => "4", 
			"OFF" => "4", 
			"ON" => "4", 
			"OVERALL" => "4", 
			"PARTS" => "4", 
			"PER_FACE" => "4", 
			"PER_FACE_INDEXED" => "4", 
			"PER_PART" => "4", 
			"PER_PART_INDEXED" => "4", 
			"PER_VERTEX" => "4", 
			"PER_VERTEX_INDEXED" => "4", 
			"REPEAT" => "4", 
			"RIGHT" => "4", 
			"SHAPE" => "4", 
			"SIDES" => "4", 
			"SOLID" => "4", 
			"STYLE" => "4", 
			"TYPE" => "4", 
			"UNKNOWN_FACE_TYPE" => "4", 
			"UNKNOWN_ORDERING" => "4", 
			"UNKNOWN_SHAPE_TYPE" => "4", 
			"USE" => "4", 
			"WRAP" => "4", 
			"Adirection" => "5", 
			"ambientColor" => "5", 
			"appearance" => "5", 
			"bboxCenter" => "5", 
			"bboxSize" => "5", 
			"bottomRadius" => "5", 
			"center" => "5", 
			"children" => "5", 
			"color" => "5", 
			"coordIndex" => "5", 
			"creaseAngle" => "5", 
			"cutOffAngle" => "5", 
			"depth" => "5", 
			"description" => "5", 
			"diffuseColor" => "5", 
			"direction" => "5", 
			"dropOffRate" => "5", 
			"emissiveColor" => "5", 
			"faceType" => "5", 
			"family" => "5", 
			"fields" => "5", 
			"filename" => "5", 
			"focalDistance" => "5", 
			"geometry" => "5", 
			"height" => "5", 
			"heightAngle" => "5", 
			"image" => "5", 
			"indexOfRefraction" => "5", 
			"intensity" => "5", 
			"isA" => "5", 
			"justification" => "5", 
			"location" => "5", 
			"map" => "5", 
			"material" => "5", 
			"materialIndex" => "5", 
			"matrix" => "5", 
			"name" => "5", 
			"normalIndex" => "5", 
			"numPoints" => "5", 
			"on" => "5", 
			"orientation" => "5", 
			"parts" => "5", 
			"point" => "5", 
			"position" => "5", 
			"radius" => "5", 
			"range" => "5", 
			"renderCulling" => "5", 
			"rotation" => "5", 
			"scaleFactor" => "5", 
			"scaleOrientation" => "5", 
			"shapeType" => "5", 
			"shininess" => "5", 
			"size" => "5", 
			"spacing" => "5", 
			"specularColor" => "5", 
			"startIndex" => "5", 
			"string" => "5", 
			"style" => "5", 
			"textureCoordIndex" => "5", 
			"translation" => "5", 
			"transparency" => "5", 
			"value" => "5", 
			"vector" => "5", 
			"vertexOrdering" => "5", 
			"whichChild" => "5", 
			"width" => "5", 
			"wrapS" => "5", 
			"wrapT" => "5");

// Special extensions

// Each category can specify a PHP function that returns an altered
// version of the keyword.
        
        

$this->linkscripts    	= array(
			"1" => "donothing", 
			"2" => "donothing", 
			"3" => "donothing", 
			"4" => "donothing", 
			"5" => "donothing");
}


function donothing($keywordin)
{
	return $keywordin;
}

}?>
