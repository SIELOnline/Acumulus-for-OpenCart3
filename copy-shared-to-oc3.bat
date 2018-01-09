@echo off
rem Link Common library to here.
mkdir D:\Projecten\Acumulus\Webkoppelingen\OpenCart3\acumulus.ocmod\upload\system\library\siel
mklink /J D:\Projecten\Acumulus\Webkoppelingen\OpenCart3\acumulus.ocmod\upload\system\library\siel\acumulus D:\Projecten\Acumulus\Webkoppelingen\libAcumulus

rem Link license files to here.
mklink /H D:\Projecten\Acumulus\Webkoppelingen\OpenCart3\acumulus.ocmod\license.txt D:\Projecten\Acumulus\Webkoppelingen\libAcumulus\license.txt
mklink /H D:\Projecten\Acumulus\Webkoppelingen\OpenCart3\acumulus.ocmod\licentie-nl.pdf D:\Projecten\Acumulus\Webkoppelingen\libAcumulus\licentie-nl.pdf
mklink /H D:\Projecten\Acumulus\Webkoppelingen\OpenCart3\acumulus.ocmod\leesmij.txt D:\Projecten\Acumulus\Webkoppelingen\leesmij.txt
