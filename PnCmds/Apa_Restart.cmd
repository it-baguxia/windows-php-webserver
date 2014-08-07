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

pushd %htd_dir%
bin\%htd_exe% -k restart -n %htd_svc% || (pushd .. & %pause% & popd)
popd

prompt
popd
