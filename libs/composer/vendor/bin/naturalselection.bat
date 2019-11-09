@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../sabre/dav/bin/naturalselection
python "%BIN_TARGET%" %*
