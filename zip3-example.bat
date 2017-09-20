@echo off
rem Check usage and arguments.
if dummy==dummy%1 (
echo Usage: %~n0 version
exit /B 1;
)
set version=%1

del Customise-Acumulus-Invoice-Example-%version%.ocmod.zip 2> nul

rem zip package.
cd customise_acumulus_invoice.ocmod
"C:\Program Files\7-Zip\7z.exe" a -x!install.xml -tzip ..\Customise-Acumulus-Invoice-Example-%version%.ocmod.zip | findstr /i "Failed Error"
cd ..
"C:\Program Files\7-Zip\7z.exe" t Customise-Acumulus-Invoice-Example-%version%.ocmod.zip | findstr /i "Processing Everything Failed Error"
