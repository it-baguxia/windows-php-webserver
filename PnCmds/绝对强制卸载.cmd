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

echo   ____________________________________________________________
echo  ^|                                                            ^|
echo  ^|  注意: 强行结束 apache.exe httpd.exe mysqld-nt.exe ...     ^|
echo  ^|        强制结束可能会导致 MySQL 数据丢失或损坏！           ^|
echo  ^|____________________________________________________________^|
set input=n
set /p input= -^> 继续执行? (y/N) 
echo.
if /i "%input%"=="y" goto kill
goto end


:kill
%Sys32%\taskkill.exe /IM %htd_exe% /IM mysqld* /F /T
%php% exec('taskkill /IM rotatelogs.exe /IM ApacheMonitor.exe /IM php.exe /IM php-cgi.exe /F /T');
%php% exec('%htd_dir%\bin\%htd_exe% -k uninstall -n %htd_svc%');
%php% exec('%myd_dir%\bin\%myd_exe% --remove %myd_svc%');
call :del %myd_dir%\data\%COMPUTERNAME%.pid
call :del %htd_dir%\logs\httpd.pid
pause & goto end


:del
if exist %1 del %1 /q
goto :eof


:end
prompt
popd
