@echo off
rem Link Common library to here.
mkdir D:\Projecten\Acumulus\Webkoppelingen\OpenCart3\acumulus.ocmod\upload\system\library\siel
rmdir /s /q D:\Projecten\Acumulus\Webkoppelingen\OpenCart3\acumulus.ocmod\upload\system\library\siel\acumulus 2> nul
mklink /J D:\Projecten\Acumulus\Webkoppelingen\OpenCart3\acumulus.ocmod\upload\system\library\siel\acumulus D:\Projecten\Acumulus\Webkoppelingen\libAcumulus

rem Link license files to here.
del D:\Projecten\Acumulus\Webkoppelingen\OpenCart3\acumulus.ocmod\license.txt 2> nul
mklink /H D:\Projecten\Acumulus\Webkoppelingen\OpenCart3\acumulus.ocmod\license.txt D:\Projecten\Acumulus\Webkoppelingen\libAcumulus\license.txt
del D:\Projecten\Acumulus\Webkoppelingen\OpenCart3\acumulus.ocmod\licentie-nl.pdf 2> nul
mklink /H D:\Projecten\Acumulus\Webkoppelingen\OpenCart3\acumulus.ocmod\licentie-nl.pdf D:\Projecten\Acumulus\Webkoppelingen\libAcumulus\licentie-nl.pdf
del D:\Projecten\Acumulus\Webkoppelingen\OpenCart3\acumulus.ocmod\leesmij.txt 2> nul
mklink /H D:\Projecten\Acumulus\Webkoppelingen\OpenCart3\acumulus.ocmod\leesmij.txt D:\Projecten\Acumulus\Webkoppelingen\leesmij.txt
