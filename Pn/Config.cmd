set pn_ver= V1.0

set htd_svc=Apache_pn
set htd_port=80
set myd_svc=MySQL5_pn
set myd_port=3306


for /d %%d in (*) do (
 if exist %%d\bin\apache.exe set htd_dir=%%d&& set htd_exe=apache.exe&& set htd_ver=2.0
 if exist %%d\bin\httpd.exe set htd_dir=%%d&& set htd_exe=httpd.exe&& set htd_ver=2.2
 if exist %%d\php.exe set php_dir=%cd%/%%d
 if exist %%d\bin\mysqld-nt.exe set myd_dir=%%d&& set myd_exe=mysqld-nt.exe&& set myd_ver=5.0
 if exist %%d\bin\mysqld.exe set myd_dir=%%d&& set myd_exe=mysqld.exe&& set myd_ver=5.1
)
if "%htd_dir%"=="" echo # Apache Not Found. & pause & exit /b
if "%php_dir%"=="" echo # PHP Not Found. & pause & exit /b
if "%myd_dir%"=="" echo # MySQL Not Found. & pause & exit /b


set php=%php_dir%\php.exe -d extension_dir=.\ext -d date.timezone=UTC -n Pn\Main.php
set pause=%php% echo ` - 按任意键继续...`; ^&^& pause^>nul

set vhs_cfg=%htd_dir%\conf\extra\httpd-vhosts.conf
set PnCmds=PnCmds
set cfg_bak_zip=Pn\cfg_bak.zip

set Sys32=%SystemRoot%\system32
set Path=%Sys32%;%Sys32%\wbem;%SystemRoot%
set net=%Sys32%\net.exe
if not exist %net% set net=%Sys32%\net1.exe
if not exist %net% echo  # 缺少 %Sys32%\net.exe, 不可继续. &%pause%&set php=&exit /b
%php% "chk_path(getcwd());" || %pause% && set php=
