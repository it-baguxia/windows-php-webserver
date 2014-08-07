@echo off

rem -- http://PHPnow.org
rem -- By Yinz ( MSN / QQ / Email : Cwood@qq.com )

setlocal enableextensions
if exist Pn\Config.cmd pushd . & goto cfg
if exist ..\Pn\Config.cmd pushd .. & goto cfg
goto :eof


:execmd
echo %1
if exist %1 call %1 && goto :eof
if exist %PnCmds%\%1 call %PnCmds%\%1 && goto :eof
echo # 找不到 %1, 请检查 %PnCmds% 或 %CD% 目录.
%pause%
goto :eof


:cfg
call Pn\Config.cmd
if "%php%"=="" exit /b
title 正在启动 Apache 和 MySQL 服务
echo.
call :execmd Apa_Start.cmd
echo.
call :execmd My_Start.cmd

popd
