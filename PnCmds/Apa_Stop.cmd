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

if exist %htd_dir%\logs\httpd.pid goto stopsvc
echo   ____________________________________________________________
echo  ^|                                                            ^|
echo  ^|    Apache 似乎没有运行.                                    ^|
echo  ^|____________________________________________________________^|
echo.

:stopsvc
pushd %htd_dir%
bin\%htd_exe% -k stop -n %htd_svc%
set errno=%errorlevel%
bin\%htd_exe% -k uninstall -n %htd_svc%
set /a errno=%errno%+%errorlevel%
popd

if %errno% GTR 0 %pause%

if exist %homedrive%\ZendOptimizer_errors.txt del %homedrive%\ZendOptimizer_errors.txt /q

prompt
popd
