@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../gettext/languages/bin/export-plural-rules.php
php "%BIN_TARGET%" %*
