<?php

interface AsqAdditionalConfigSection {

	public function completeFormWithAdditionalConfigSection(ilPropertyFormGUI $form):void;

	public function saveInput();
}