@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../sabre/dav/bin/sabredav
sh "%BIN_TARGET%" %*
