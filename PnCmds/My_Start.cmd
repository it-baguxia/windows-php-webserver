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

if not exist %myd_dir%\data\%COMPUTERNAME%.pid goto startsvc
echo   ____________________________________________________________
echo  ^|                                                            ^|
echo  ^|    MySQL 似乎已经运行.                                     ^|
echo  ^|____________________________________________________________^|

set /p input= -^> 尝试停止后继续? (y/N) 
echo.
if /i "%input%"=="y" goto stopsvc
goto end

:stopsvc
%net% stop %myd_svc%
%myd_dir%\bin\%myd_exe% --remove %myd_svc%

:startsvc
%php% frpl('%myd_dir%\my.ini', '^^(port\s*=)\s*\d+', '$1 %myd_port%');
%myd_dir%\bin\%myd_exe% --install %myd_svc% --defaults-file="%CD%\%myd_dir%\my.ini"
%net% start %myd_svc% || %pause%

:end
prompt
popd
