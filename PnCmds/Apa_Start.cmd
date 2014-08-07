@echo off

setlocal enableextensions
if exist Pn\Config.cmd pushd . & goto cfg
if exist ..\Pn\Config.cmd pushd .. & goto cfg
goto :eof

:cfg
call Pn\Config.cmd
if "%php%"=="" exit /b
prompt -$g

if not exist %htd_dir%\logs\httpd.pid goto startsvc
echo   ____________________________________________________________
echo  ^|                                                            ^|
echo  ^|    Apache 似乎已经运行.                                    ^|
echo  ^|____________________________________________________________^|

set input=n
set /p input= -^> 继续执行? (y/N) 
echo.
if /i "%input%"=="y" goto startsvc
goto end

:startsvc
%php% upcfg(); || %pause% && goto end
%php% chg_port(env('htd_port')); || %pause% && goto end
pushd %htd_dir%
bin\%htd_exe% -k install -n %htd_svc%
set errno=%errorlevel%
bin\%htd_exe% -k start -n %htd_svc%
set /a errno=%errno% + %errorlevel%
popd

if %errno% GTR 0 %pause%

:end
prompt
popd
