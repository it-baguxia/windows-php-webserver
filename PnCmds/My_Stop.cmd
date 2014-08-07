@echo off

rem -- http://PHPnow.org
rem -- By Yinz ( MSN / QQ / Email : Cwood@qq.com )

setlocal enableextensions
if exist Pn\Config.cmd pushd . & goto cfg
if exist ..\Pn\Config.cmd pushd .. & goto cfg
goto :eof

:cfg
call Pn\Config.cmd
if "%php%"=="" exit /b
prompt -$g

if exist %myd_dir%\data\%COMPUTERNAME%.pid goto stopsvc
echo   ____________________________________________________________
echo  ^|                                                            ^|
echo  ^|    MySQL 似乎没有运行.                                     ^|
echo  ^|____________________________________________________________^|
echo.

:stopsvc
%net% stop %myd_svc%
set errlevel=%errorlevel%
%myd_dir%\bin\%myd_exe% --remove %myd_svc%
if not %errlevel%==0 %pause%

prompt
popd
