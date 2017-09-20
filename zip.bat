@echo off
rem Check usage and arguments.
if dummy==dummy%1 (
echo Usage: %~n0 version
exit /B 1;
)
set version=%1

rem call zip22.bat %version%
call zip3.bat %version%
call zip3-example.bat %version%
