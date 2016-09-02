<?php

namespace CaT\Plugins\TalentAssessment;

interface ObjTalentAssessment {
	public function getTitle();
	public function getDescription();
	public function setTitle($a_title);
	public function setDescription($a_desc);
	public function update();
	public function updateSettings(\Closure $update);
	public function getSettings();
}