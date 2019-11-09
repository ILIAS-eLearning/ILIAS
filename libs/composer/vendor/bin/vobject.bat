@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../sabre/vobject/bin/vobject
php "%BIN_TARGET%" %*
