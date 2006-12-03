<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_rmanint extends HFile{
   function HFile_rmanint(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// Rman Interface
/*************************************/
// Flags

$this->nocase            	= "0";
$this->notrim            	= "0";
$this->perl              	= "0";

// Colours

$this->colours        	= array("blue", "purple", "gray", "brown", "blue", "purple");
$this->quotecolour       	= "blue";
$this->blockcommentcolour	= "green";
$this->linecommentcolour 	= "green";

// Indent Strings

$this->indent            	= array("{", "Begin", "AttributeBegin", "FrameBegin", "MotionBegin", "ObjectBegin", "SolidBegin", "TransformBegin", "WorldBegin");
$this->unindent          	= array("}", "End", "AttributeEnd", "FrameEnd", "MotionEnd", "ObjectEnd", "SolidEnd", "TransformEnd", "WorldEnd");

// String characters and delimiters

$this->stringchars       	= array();
$this->delimiters        	= array("~", "!", "@", "%", "^", "&", "*", "(", ")", "-", "+", "=", "|", "\\", "/", "{", "}", "[", "]", ":", ";", "\"", "'", "<", ">", " ", ",", " ", ".", "?");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("#");
$this->blockcommenton    	= array("");
$this->blockcommentoff   	= array("");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"AreaLightSource" => "1", 
			"Attribute" => "1", 
			"AttributeBegin" => "1", 
			"AttributeEnd" => "1", 
			"Begin" => "1", 
			"Bound" => "1", 
			"Clipping" => "1", 
			"Color" => "1", 
			"ColorSamples" => "1", 
			"ConcatTransform" => "1", 
			"CoordinateSystem" => "1", 
			"CropWindow" => "1", 
			"Declare" => "1", 
			"DepthOfField" => "1", 
			"Detail" => "1", 
			"DetailRange" => "1", 
			"Displacement" => "1", 
			"Display" => "1", 
			"End" => "1", 
			"Exterior" => "1", 
			"Format" => "1", 
			"FrameAspectRatio" => "1", 
			"FrameBegin" => "1", 
			"FrameEnd" => "1", 
			"GeometricApproximation" => "1", 
			"Hider" => "1", 
			"Identity" => "1", 
			"Illuminance" => "1", 
			"Illuminate" => "1", 
			"Interior" => "1", 
			"LightSource" => "1", 
			"Matte" => "1", 
			"Opacity" => "1", 
			"Option" => "1", 
			"Orientation" => "1", 
			"Perspective" => "1", 
			"PixelFilter" => "1", 
			"PixelSamples" => "1", 
			"PixelVariance" => "1", 
			"Projection" => "1", 
			"Quantize" => "1", 
			"RelativeDetail" => "1", 
			"RiAreaLightSource" => "1", 
			"RiAttribute" => "1", 
			"RiAttributeEnd" => "1", 
			"RiBegin" => "1", 
			"RiBound" => "1", 
			"RiClipping" => "1", 
			"RiColor" => "1", 
			"RiColorSamples" => "1", 
			"RiConcatTransform" => "1", 
			"RiCoordinateSystem" => "1", 
			"RiCropWindow" => "1", 
			"RiDepthOfField" => "1", 
			"RiDetail" => "1", 
			"RiDetailRange" => "1", 
			"RiDisplacement" => "1", 
			"RiDisplay" => "1", 
			"RiEnd" => "1", 
			"RiExterior" => "1", 
			"RiFormat" => "1", 
			"RiFrameAspectRatio" => "1", 
			"RiFrameBegin" => "1", 
			"RiFrameEnd" => "1", 
			"RiGeometricApproximation" => "1", 
			"RiHider" => "1", 
			"RiIdentity" => "1", 
			"RiIlluminate" => "1", 
			"RiInterior" => "1", 
			"RiLightSource" => "1", 
			"RiMatte" => "1", 
			"RiOpacity" => "1", 
			"RiOption" => "1", 
			"RiOrientation" => "1", 
			"RiPerspective" => "1", 
			"RiPixelFilter" => "1", 
			"RiPixelSamples" => "1", 
			"RiPixelVariance" => "1", 
			"RiProjection" => "1", 
			"RiQuantize" => "1", 
			"RiRelativeDetail" => "1", 
			"RiRotate" => "1", 
			"RiScreenWindow" => "1", 
			"RiShadingInterpolation" => "1", 
			"RiShadingRate" => "1", 
			"RiShutter" => "1", 
			"RiSides" => "1", 
			"RiSkew" => "1", 
			"RiSurface" => "1", 
			"RiTextureCoordinates" => "1", 
			"RiTransform" => "1", 
			"RiTransformBegin" => "1", 
			"RiTransformEnd" => "1", 
			"RiTransformPoints" => "1", 
			"RiTranslate" => "1", 
			"RiWorldBegin" => "1", 
			"RiWorldEnd" => "1", 
			"Rotate" => "1", 
			"Scale" => "1", 
			"ScreenWindow" => "1", 
			"ShadingInterpolation" => "1", 
			"ShadingRate" => "1", 
			"Shutter" => "1", 
			"Sides" => "1", 
			"Skew" => "1", 
			"Surface" => "1", 
			"TextureCoordinates" => "1", 
			"Transform" => "1", 
			"TransformBegin" => "1", 
			"TransformEnd" => "1", 
			"TransformPoints" => "1", 
			"Translate" => "1", 
			"version" => "1", 
			"WorldBegin" => "1", 
			"WorldEnd" => "1", 
			"Basis" => "2", 
			"Cylinder" => "2", 
			"Disk" => "2", 
			"GeneralPolygon" => "2", 
			"Geometry" => "2", 
			"Hyperboloid" => "2", 
			"NuPatch" => "2", 
			"ObjectBegin" => "2", 
			"ObjectEnd" => "2", 
			"ObjectInstance" => "2", 
			"Patch" => "2", 
			"Paraboloid" => "2", 
			"PointsPolygons" => "2", 
			"PointsGeneralPolygons" => "2", 
			"Polygon" => "2", 
			"Procedural" => "2", 
			"SolidBegin" => "2", 
			"SolidEnd" => "2", 
			"Sphere" => "2", 
			"Torus" => "2", 
			"RiBasis" => "2", 
			"RiCylinder" => "2", 
			"RiGeneralPolygon" => "2", 
			"RiGeometry" => "2", 
			"RiHyperboloid" => "2", 
			"RiNuPatch" => "2", 
			"RiObjectBegin" => "2", 
			"RiObjectEnd" => "2", 
			"RiObjectInstance" => "2", 
			"RiPatch" => "2", 
			"RiParaboloid" => "2", 
			"RiPointsPolygons" => "2", 
			"RiPointsGeneralPolygons" => "2", 
			"RiPolygon" => "2", 
			"RiProcedural" => "2", 
			"RiSolidBegin" => "2", 
			"RiSolidEnd" => "2", 
			"RiSphere" => "2", 
			"RiTorus" => "2", 
			"MotionBegin" => "3", 
			"MotionEnd" => "3", 
			"RiMotionBegin" => "3", 
			"RiMotionEnd" => "3", 
			"MakeBump" => "4", 
			"MakeCubeFaceEnvironment" => "4", 
			"MakeLatLongEnvironment" => "4", 
			"MakeTexture" => "4", 
			"RiMakeBump" => "4", 
			"RiMakeCubeFaceEnvironment" => "4", 
			"RiMakeLatLongEnvironment" => "4", 
			"RiMakeTexture" => "4", 
			"ArchiveRecord" => "5", 
			"ErrorHandler" => "5", 
			"RiArchiveRecord" => "5", 
			"RiErrorHandler" => "5", 
			"Ci" => "6", 
			"Cl" => "6", 
			"Cs" => "6", 
			"E" => "6", 
			"I" => "6", 
			"L" => "6", 
			"N" => "6", 
			"Ng" => "6", 
			"Oi" => "6", 
			"Ol" => "6", 
			"Os" => "6", 
			"P" => "6", 
			"dPdu" => "6", 
			"dPdv" => "6", 
			"du" => "6", 
			"dv" => "6", 
			"ncomps" => "6", 
			"s" => "6", 
			"t" => "6", 
			"time" => "6", 
			"u" => "6", 
			"v" => "6");

// Special extensions

// Each category can specify a PHP function that returns an altered
// version of the keyword.
        
        

$this->linkscripts    	= array(
			"1" => "donothing", 
			"2" => "donothing", 
			"3" => "donothing", 
			"4" => "donothing", 
			"5" => "donothing", 
			"6" => "donothing");
}


function donothing($keywordin)
{
	return $keywordin;
}

}?>
