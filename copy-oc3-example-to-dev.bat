@echo off
rem Copy files in our folder structure to development installation.
del D:\Projecten\Acumulus\OpenCart\www30\www\admin\controller\extension\module\acumulus_customise_invoice.php 2> nul
mklink /H D:\Projecten\Acumulus\OpenCart\www30\www\admin\controller\extension\module\acumulus_customise_invoice.php D:\Projecten\Acumulus\Webkoppelingen\OpenCart3\acumulus_customise_invoice.ocmod\upload\admin\controller\extension\module\acumulus_customise_invoice.php
del D:\Projecten\Acumulus\OpenCart\www30\www\admin\language\en-gb\extension\module\acumulus_customise_invoice.php 2> nul
mklink /H D:\Projecten\Acumulus\OpenCart\www30\www\admin\language\en-gb\extension\module\acumulus_customise_invoice.php D:\Projecten\Acumulus\Webkoppelingen\OpenCart3\acumulus_customise_invoice.ocmod\upload\admin\language\en-gb\extension\module\acumulus_customise_invoice.php
del D:\Projecten\Acumulus\OpenCart\www30\www\admin\view\template\extension\module\acumulus_customise_invoice.twig 2> nul
mklink /H D:\Projecten\Acumulus\OpenCart\www30\www\admin\view\template\extension\module\acumulus_customise_invoice.twig D:\Projecten\Acumulus\Webkoppelingen\OpenCart3\acumulus_customise_invoice.ocmod\upload\admin\view\template\extension\module\acumulus_customise_invoice.twig
