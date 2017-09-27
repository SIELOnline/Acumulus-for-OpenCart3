@echo off
rem Check usage and arguments.
if dummy==dummy%1 (
echo Usage: %~n0 version
exit /B 1;
)
set version=%1

del Acumulus-Customise-Invoice-%version%.ocmod.zip 2> nul

rem zip package.
cd acumulus_customise_invoice.ocmod
"C:\Program Files\7-Zip\7z.exe" a -x!install.xml -tzip ..\Acumulus-Customise-Invoice-%version%.ocmod.zip | findstr /i "Failed Error"
cd ..
"C:\Program Files\7-Zip\7z.exe" t Acumulus-Customise-Invoice-%version%.ocmod.zip | findstr /i "Processing Everything Failed Error"
