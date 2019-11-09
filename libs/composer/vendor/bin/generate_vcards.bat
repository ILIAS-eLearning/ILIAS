@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../sabre/vobject/bin/generate_vcards
php "%BIN_TARGET%" %*
