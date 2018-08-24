<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_ibis extends HFile{
   function HFile_ibis(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// IBIS
/*************************************/
// Flags

$this->nocase            	= "0";
$this->notrim            	= "0";
$this->perl              	= "0";

// Colours

$this->colours        	= array("blue", "purple", "gray");
$this->quotecolour       	= "blue";
$this->blockcommentcolour	= "green";
$this->linecommentcolour 	= "green";

// Indent Strings

$this->indent            	= array();
$this->unindent          	= array();

// String characters and delimiters

$this->stringchars       	= array();
$this->delimiters        	= array("~", "!", "@", "%", "^", "&", "*", "(", ")", "=", "|", "\\", "{", "}", ":", ";", "\"", "'", "<", ">", " ", ",", "	", ".", "?");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("|");
$this->blockcommenton    	= array("");
$this->blockcommentoff   	= array("");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"Board" => "1", 
			"Char]" => "1", 
			"Clamp" => "1", 
			"Clamp]" => "1", 
			"Current]" => "1", 
			"Data]" => "1", 
			"Description]" => "1", 
			"Designator" => "1", 
			"Groups]" => "1", 
			"List]" => "1", 
			"MOSFET]" => "1", 
			"Map]" => "1", 
			"Mapping]" => "1", 
			"Matrix]" => "1", 
			"Model" => "1", 
			"Model]" => "1", 
			"Name]" => "1", 
			"Numbers]" => "1", 
			"Of" => "1", 
			"Package" => "1", 
			"Pin" => "1", 
			"Pin]" => "1", 
			"Pins]" => "1", 
			"Pulse" => "1", 
			"Range]" => "1", 
			"Reference]" => "1", 
			"Rev]" => "1", 
			"Schedule]" => "1", 
			"Sections]" => "1", 
			"Selector]" => "1", 
			"Series]" => "1", 
			"Spec]" => "1", 
			"Submodel]" => "1", 
			"Switch" => "1", 
			"Table]" => "1", 
			"Ver]" => "1", 
			"Waveform]" => "1", 
			"[Add" => "1", 
			"[Bandwidth]" => "1", 
			"[Begin" => "1", 
			"[C" => "1", 
			"[Cac]" => "1", 
			"[Capacitance" => "1", 
			"[Comment" => "1", 
			"[Component]" => "1", 
			"[Copyright]" => "1", 
			"[Date]" => "1", 
			"[Define" => "1", 
			"[Description]" => "1", 
			"[Diff" => "1", 
			"[Disclaimer]" => "1", 
			"[Driver" => "1", 
			"[End" => "1", 
			"[End]" => "1", 
			"[Falling" => "1", 
			"[File" => "1", 
			"[GND" => "1", 
			"[IBIS" => "1", 
			"[Inductance" => "1", 
			"[L" => "1", 
			"[Lc" => "1", 
			"[Manufacturer]" => "1", 
			"[Model" => "1", 
			"[Model]" => "1", 
			"[Notes]" => "1", 
			"[Number" => "1", 
			"[OEM]" => "1", 
			"[Off]" => "1", 
			"[On]" => "1", 
			"[POWER" => "1", 
			"[Package" => "1", 
			"[Package]" => "1", 
			"[Path" => "1", 
			"[Pin" => "1", 
			"[Pin]" => "1", 
			"[Pulldown" => "1", 
			"[Pulldown]" => "1", 
			"[Pullup" => "1", 
			"[Pullup]" => "1", 
			"[R" => "1", 
			"[Rac]" => "1", 
			"[Ramp]" => "1", 
			"[Rc" => "1", 
			"[Reference" => "1", 
			"[Resistance" => "1", 
			"[Rgnd]" => "1", 
			"[Rising" => "1", 
			"[Rl" => "1", 
			"[Row]" => "1", 
			"[Rpower]" => "1", 
			"[Series" => "1", 
			"[Source]" => "1", 
			"[Submodel" => "1", 
			"[Submodel]" => "1", 
			"[TTgnd]" => "1", 
			"[TTpower]" => "1", 
			"[Temperature" => "1", 
			"[Voltage" => "1", 
			"Banded_matrix" => "2", 
			"C" => "2", 
			"C_comp" => "2", 
			"C_dut" => "2", 
			"C_fixture" => "2", 
			"C_pin" => "2", 
			"C_pkg" => "2", 
			"Cref" => "2", 
			"D_overshoot_high" => "2", 
			"D_overshoot_low" => "2", 
			"D_overshoot_time" => "2", 
			"Enable" => "2", 
			"Endfork" => "2", 
			"Fall_off_dly" => "2", 
			"Fall_on_dly" => "2", 
			"Fork" => "2", 
			"Full_matrix" => "2", 
			"L" => "2", 
			"L_dut" => "2", 
			"L_fixture" => "2", 
			"L_pin" => "2", 
			"L_pkg" => "2", 
			"Len" => "2", 
			"Model_type" => "2", 
			"Node" => "2", 
			"Off" => "2", 
			"Off_delay" => "2", 
			"On" => "2", 
			"Polarity" => "2", 
			"Pulse_high" => "2", 
			"Pulse_low" => "2", 
			"Pulse_time" => "2", 
			"R" => "2", 
			"R_dut" => "2", 
			"R_fixture" => "2", 
			"R_load" => "2", 
			"R_pin" => "2", 
			"R_pkg" => "2", 
			"Rise_off_dly" => "2", 
			"Rise_on_dly" => "2", 
			"Rref" => "2", 
			"S_overshoot_high" => "2", 
			"S_overshoot_low" => "2", 
			"Si_location" => "2", 
			"Sparse_matrix" => "2", 
			"Submodel_type" => "2", 
			"Timing_location" => "2", 
			"V_fixture" => "2", 
			"V_fixture_max" => "2", 
			"V_fixture_min" => "2", 
			"V_trigger_f" => "2", 
			"V_trigger_r" => "2", 
			"Vinh" => "2", 
			"Vinh+" => "2", 
			"Vinh-" => "2", 
			"Vinl" => "2", 
			"Vinl+" => "2", 
			"Vinl-" => "2", 
			"Vmeas" => "2", 
			"Vref" => "2", 
			"dV/dt_f" => "2", 
			"dV/dt_r" => "2", 
			"function_table_group" => "2", 
			"gnd_clamp_ref" => "2", 
			"inv_pin" => "2", 
			"model_name" => "2", 
			"pin_2" => "2", 
			"power_clamp_ref" => "2", 
			"pulldown_ref" => "2", 
			"pullup_ref" => "2", 
			"signal_name" => "2", 
			"tdelay_max" => "2", 
			"tdelay_min" => "2", 
			"tdelay_typ" => "2", 
			"vdiff" => "2", 
			"3-state" => "3", 
			"3-state_ECL" => "3", 
			"I/O" => "3", 
			"I/O_ECL" => "3", 
			"I/O_open_drain" => "3", 
			"I/O_open_sink" => "3", 
			"I/O_open_source" => "3", 
			"Input" => "3", 
			"Input_ECL" => "3", 
			"Open_drain" => "3", 
			"Open_sink" => "3", 
			"Open_source" => "3", 
			"Output" => "3", 
			"Output_ECL" => "3", 
			"Series" => "3", 
			"Series_switch" => "3", 
			"Terminator" => "3");

// Special extensions

// Each category can specify a PHP function that returns an altered
// version of the keyword.
        
        

$this->linkscripts    	= array(
			"1" => "donothing", 
			"2" => "donothing", 
			"3" => "donothing");
}


function donothing($keywordin)
{
	return $keywordin;
}

}?>
