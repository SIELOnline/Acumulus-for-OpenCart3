@echo off
rem Check usage and arguments.
if dummy==dummy%1 (
echo Usage: %~n0 version
exit /B 1;
)
set version=%1
set archive=OpenCart3-Acumulus-Customise-Invoice-%version%.ocmod.zip

rem delete, recreate and check zip package.
del %archive% 2> nul
cd acumulus_customise_invoice.ocmod
"C:\Program Files\7-Zip\7z.exe" a -tzip ..\%archive% | findstr /i "Failed Error"
cd ..
"C:\Program Files\7-Zip\7z.exe" t %archive% | findstr /i "Processing Everything Failed Error"
