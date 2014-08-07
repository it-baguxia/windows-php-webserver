<?php
/*
  http://phpnow.org
  YinzCN_at_Gmail.com
*/

$Cfg['htd_port'] = env('htd_port');
$Cfg['err_404'] = '/404.html';
$Cfg['dir_index'] = 'index.php index.html index.htm';
$Cfg['ThreadsPerChild'] = '1024';
$Cfg['MaxRequestsPerChild'] = '10000';
$Cfg['upload_max_filesize'] = '32M';
$Cfg['post_max_size'] = $Cfg['upload_max_filesize'];
$Cfg['vhs_cfg'] = rpl('\\', '/', regrpl('^'.$htd_dir.'\\\\', '', $vhs_cfg));


// Initialize php-apache2handler.ini
function init_phpini() {
  global $Cfg, $php_dir, $php_ini, $PnDir, $sysroot;

  if(PHP_VERSION_ID < 50300)
    cp($php_dir.'/php.ini-recommended', $php_ini);
  else
    cp($php_dir.'/php.ini-production', $php_ini);

  $str = rfile($php_ini);

  $str = regrpl('^(short_open_tag)[ \t]+=[ \t]+Off', '$1 = On', $str);
  $str = regrpl('^(disable_functions)[ \t]+=', '$1 = proc_open, popen, exec, system, shell_exec, passthru', $str);
  $str = regrpl('^(display_errors)[ \t]+=[ \t]+Off', '$1 = On', $str);
  $str = regrpl('^(magic_quotes_gpc)[ \t]+=[ \t]+Off', '$1 = On', $str);

  $str = regrpl('^;(upload_tmp_dir)[ \t]+=', '$1 = "'.$sysroot.'\Temp"', $str);
  $str = regrpl('^(upload_max_filesize)[ \t]+=.*(\r\n)', '$1 = '.$Cfg['upload_max_filesize'].'$2', $str);
  $str = regrpl('^(post_max_size)[ \t]+=.*(\r\n)', '$1 = '.$Cfg['post_max_size'].'$2', $str);

  // PHP extensions
  if(PHP_VERSION_ID < 50300) {
    $str = regrpl('^(extension_dir)[ \t]+=[ \t]+".\/"', '$1 = "..\\..\\'.$php_dir.'\\ext"', $str);
    $str = regrpl('^;(.*zip\.dll)', '$1', $str);
  } else {
    $str = regrpl('; (extension_dir = )"ext"', '$1"..\\..\\'.$php_dir.'\\ext"', $str);
    $str = regrpl(';(date.timezone =)', '$1 "UTC"', $str);
  }
  $str = regrpl('enable_dl[ \t]+=[ \t]+On', 'enable_dl = Off', $str);
  $str = regrpl('^;(.*curl\.dll)', '$1', $str);
  $str = regrpl('^;(.*gd2\.dll)', '$1', $str);
  $str = regrpl('^;(.*mbstring\.dll)', '$1', $str);
  $str = regrpl('^;(.*mcrypt\.dll)', '$1', $str);
  $str = regrpl('^;(.*mhash\.dll)', '$1', $str);
  $str = regrpl('^;(.*php_mysql\.dll)', '$1', $str);
  $str = regrpl('^;(.*pdo\.dll)', '$1', $str);
  $str = regrpl('^;(.*pdo_mysql\.dll)', '$1', $str);
  $str = regrpl('^;(.*sockets\.dll)', '$1', $str);
  $str = regrpl('^;(.*xmlrpc\.dll)', '$1', $str);

  $str = regrpl('^;(session.save_path)[ \t]+=[ \t]+".*"(\r\n)', '$1 = "'.$sysroot.'\Temp"$2', $str);

  $str = $str.'

[eAccelerator]
;;zend_extension_ts="..\..\\'.$php_dir.'\ext\eAccelerator0953_'.PHP_VERSION.'.dll"

eaccelerator.shm_size="64"
eaccelerator.cache_dir="'.$sysroot.'\Temp"
eaccelerator.enable="1"
eaccelerator.optimizer="1"
eaccelerator.check_mtime="1"
eaccelerator.debug="0"
eaccelerator.filter=""
eaccelerator.shm_max="0"
eaccelerator.shm_ttl="0"
eaccelerator.shm_prune_period="0"
eaccelerator.shm_only="0"
eaccelerator.compress="1"
eaccelerator.compress_level="9"
eaccelerator.keys = "shm"
eaccelerator.sessions = "shm"
eaccelerator.content = "shm"
eaccelerator.admin.name="admin"
eaccelerator.admin.password="password"
eaccelerator.allowed_admin_path = "..\htdocs\control.php"

[Zend]
zend_extension_manager.optimizer_ts="..\ZendOptimizer"
zend_extension_ts="..\..\ZendOptimizer\ZendExtensionManager.dll"
';

  wfile($php_ini, $str);
}


// Initialize Apache 2.0
function init_apache_2_0_frpl() {
  global $Cfg, $htd_dir, $php_dir, $htd_cfg;

  cp($htd_dir.'/conf/httpd-win.conf', $htd_cfg);

  $str = rfile($htd_cfg);

  $str = rpl('ServerRoot "@@ServerRoot@@"', 'ServerRoot "."', $str);
  $str = rpl('@@Port@@', $Cfg['htd_port'], $str);
  $str = rpl('@@ServerAdmin@@', 'admin@phpnow.org', $str);
  $str = rpl('@@ServerName@@', 'PnServer', $str);
  $str = rpl('@@ServerRoot@@/htdocs', '../htdocs', $str);
  $str = rpl('@@ServerRoot@@/', './', $str);

  $str = regrpl('^[ \t]*(ThreadsPerChild)[ \t]+\d+', '$1 '.$Cfg['ThreadsPerChild'], $str);
  $str = regrpl('^[ \t]*(MaxRequestsPerChild)[ \t]+\d+', '$1 '.$Cfg['MaxRequestsPerChild'], $str);

  $str = regrpl('^#(Load.*rewrite.*\r\n)', '$1', $str);
  $str = regrpl('(#\r\n    AllowOverride) None', '$1 All', $str);

  $str = rpl('DirectoryIndex index.html', 'DirectoryIndex '.$Cfg['dir_index'], $str);

  $str = regrpl('#(<Location \/server-([a-z]+)>\r\n)#([^#]+)#([^#]+)#([^#]+)#([^#]+)#(<\/Location>)',
  "<IfModule mod_$2.c>\r\n$1$3$4$5$6$7\r\n</IfModule>", $str);
  $str = rpl(' .@@DomainName@@', ' 127.0.0.1', $str);

  // php5_module
  $str = $str.'
# Begin PHP Configure of PHPnow
LoadModule php5_module "../'.$php_dir.'/php5apache2.dll"
<IfModule mod_php5.c>
  PHPINIDir "../'.$php_dir.'/"
  AddType application/x-httpd-php .php
  AddType application/x-httpd-php-source .phps
</IfModule>
# End PHP Configure of PHPnow

ErrorDocument 404 '.$Cfg['err_404'].'

Include '.$Cfg['vhs_cfg'].'
';

  wfile($htd_cfg, $str);
}


// Initialize Apache 2.2
function init_apache_2_2_frpl() {
  global $Cfg, $htd_dir, $php_dir, $htd_cfg;

  cp($htd_dir.'/conf/original/httpd.conf.in', $htd_cfg);

$LoadModule = <<< EOT
LoadModule actions_module modules/mod_actions.so
LoadModule alias_module modules/mod_alias.so
LoadModule asis_module modules/mod_asis.so
LoadModule auth_basic_module modules/mod_auth_basic.so
#LoadModule auth_digest_module modules/mod_auth_digest.so
#LoadModule authn_alias_module modules/mod_authn_alias.so
#LoadModule authn_anon_module modules/mod_authn_anon.so
#LoadModule authn_dbd_module modules/mod_authn_dbd.so
#LoadModule authn_dbm_module modules/mod_authn_dbm.so
LoadModule authn_default_module modules/mod_authn_default.so
LoadModule authn_file_module modules/mod_authn_file.so
#LoadModule authnz_ldap_module modules/mod_authnz_ldap.so
#LoadModule authz_dbm_module modules/mod_authz_dbm.so
LoadModule authz_default_module modules/mod_authz_default.so
LoadModule authz_groupfile_module modules/mod_authz_groupfile.so
LoadModule authz_host_module modules/mod_authz_host.so
#LoadModule authz_owner_module modules/mod_authz_owner.so
LoadModule authz_user_module modules/mod_authz_user.so
LoadModule autoindex_module modules/mod_autoindex.so
#LoadModule cache_module modules/mod_cache.so
#LoadModule cern_meta_module modules/mod_cern_meta.so
LoadModule cgi_module modules/mod_cgi.so
#LoadModule charset_lite_module modules/mod_charset_lite.so
#LoadModule dav_module modules/mod_dav.so
#LoadModule dav_fs_module modules/mod_dav_fs.so
#LoadModule dav_lock_module modules/mod_dav_lock.so
#LoadModule dbd_module modules/mod_dbd.so
#LoadModule deflate_module modules/mod_deflate.so
LoadModule dir_module modules/mod_dir.so
#LoadModule disk_cache_module modules/mod_disk_cache.so
#LoadModule dumpio_module modules/mod_dumpio.so
LoadModule env_module modules/mod_env.so
#LoadModule expires_module modules/mod_expires.so
#LoadModule ext_filter_module modules/mod_ext_filter.so
#LoadModule file_cache_module modules/mod_file_cache.so
#LoadModule filter_module modules/mod_filter.so
#LoadModule headers_module modules/mod_headers.so
#LoadModule ident_module modules/mod_ident.so
#LoadModule imagemap_module modules/mod_imagemap.so
LoadModule include_module modules/mod_include.so
#LoadModule info_module modules/mod_info.so
LoadModule isapi_module modules/mod_isapi.so
#LoadModule ldap_module modules/mod_ldap.so
#LoadModule logio_module modules/mod_logio.so
LoadModule log_config_module modules/mod_log_config.so
#LoadModule log_forensic_module modules/mod_log_forensic.so
#LoadModule mem_cache_module modules/mod_mem_cache.so
LoadModule mime_module modules/mod_mime.so
#LoadModule mime_magic_module modules/mod_mime_magic.so
LoadModule negotiation_module modules/mod_negotiation.so
#LoadModule proxy_module modules/mod_proxy.so
#LoadModule proxy_ajp_module modules/mod_proxy_ajp.so
#LoadModule proxy_balancer_module modules/mod_proxy_balancer.so
#LoadModule proxy_connect_module modules/mod_proxy_connect.so
#LoadModule proxy_ftp_module modules/mod_proxy_ftp.so
#LoadModule proxy_http_module modules/mod_proxy_http.so
#LoadModule rewrite_module modules/mod_rewrite.so
LoadModule setenvif_module modules/mod_setenvif.so
#LoadModule speling_module modules/mod_speling.so
#LoadModule ssl_module modules/mod_ssl.so
#LoadModule status_module modules/mod_status.so
#LoadModule substitute_module modules/mod_substitute.so
#LoadModule unique_id_module modules/mod_unique_id.so
#LoadModule userdir_module modules/mod_userdir.so
#LoadModule usertrack_module modules/mod_usertrack.so
#LoadModule version_module modules/mod_version.so
#LoadModule vhost_alias_module modules/mod_vhost_alias.so
EOT;

  $str = rfile($htd_cfg);

  $str = rpl('ServerRoot "@@ServerRoot@@"', 'ServerRoot "."', $str);
  $str = rpl('@@Port@@', $Cfg['htd_port'], $str);
  $str = rpl('@exp_htdocsdir@', '../htdocs', $str);
  $str = rpl('@exp_cgidir@', './htdocs/cgi-bin', $str);
  $str = rpl('@rel_sysconfdir@/', 'conf/', $str);
  $str = rpl('@rel_logfiledir@', 'logs', $str);
  $str = rpl('error_log', 'error.log', $str);
  $str = rpl('access_log', 'access.log', $str);
  $str = rpl('@@LoadModule@@', $LoadModule, $str);

  $str = rpl('DirectoryIndex index.html', 'DirectoryIndex '.$Cfg['dir_index'], $str);

  $str = regrpl('^#?(ServerName).*(\r\n)', '$1 PnServer:'.$Cfg['htd_port'].'$2', $str);
  $str = regrpl('^(ServerAdmin).*(\r\n)', "$1 admin@phpnow.org$2", $str);

  $str = regrpl('(    #\r\n    AllowOverride) None', '$1 All', $str);

  $str = regrpl('^#?(Load.*mod_autoindex.*\r\n)', '##$1', $str);
  $str = regrpl('^#?(Load.*mod_rewrite.*\r\n)', '$1', $str);

  $str = regrpl('^#(Include.*httpd-mpm.conf\r\n)', '$1', $str);
  $str = regrpl('^#(Include).*httpd-vhosts.conf(\r\n)', '$1 '.$Cfg['vhs_cfg'].'$2', $str);

  // php5_module
  $str = $str.'

# Begin PHP Configure of PHPnow
LoadModule php5_module "../'.$php_dir.'/php5apache2_2.dll"
<IfModule mod_php5.c>
  PHPINIDir "../'.$php_dir.'/"
  AddType application/x-httpd-php .php
  AddType application/x-httpd-php-source .phps
</IfModule>
# End PHP Configure of PHPnow

ErrorDocument 404 '.$Cfg['err_404'].'
';

  wfile($htd_cfg, $str);

  // httpd-mpm.conf
  $str = rfile($htd_dir.'/conf/original/extra/httpd-mpm.conf.in');
  $str = rpl('@rel_runtimedir@', 'logs', $str);
  $str = rpl('@rel_logfiledir@', 'logs', $str);
  $str = regrpl('(<IfModule mpm_winnt_module>[^<]+ThreadsPerChild)[ \t]+\d+([^<]+MaxRequestsPerChild)[ \t]+\d+([^<]+<\/IfModule>)', '$1 '.$Cfg['ThreadsPerChild'].'$2 '.$Cfg['MaxRequestsPerChild'].'$3', $str);
  wfile($htd_dir.'/conf/extra/httpd-mpm.conf', $str);

  // httpd-autoindex.conf
  $fn = $htd_dir.'/conf/extra/httpd-autoindex.conf';
  cp($htd_dir.'/conf/original/extra/httpd-autoindex.conf.in', $fn);
  frpl($fn, '@exp_iconsdir@', './icons');

  // httpd-info.conf
  $fn = $htd_dir.'/conf/extra/httpd-info.conf';
  cp($htd_dir.'/conf/original/extra/httpd-info.conf.in', $fn);
  frpl($fn, '(Allow from) .+\r\n', "$1 127.0.0.1\r\n");
}
?>