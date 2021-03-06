<?php
/**
 * @package Abricos
 * @subpackage Logs
 * @copyright 2016 Alexander Kuzmin
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

$charset = "CHARACTER SET 'utf8' COLLATE 'utf8_general_ci'";
$updateManager = Ab_UpdateManager::$current;
$db = Abricos::$db;
$pfx = $db->prefix;

if ($updateManager->isUpdate('0.1.0')){
    Abricos::GetModule('logs')->permission->Install();

    $db->query_write("
		CREATE TABLE IF NOT EXISTS ".$pfx."logs_access (
            accessid int(10) unsigned NOT NULL auto_increment,
            userid INT(10) unsigned NOT NULL DEFAULT 0,
            method VARCHAR(10) NOT NULL DEFAULT '' COMMENT 'GET/POST',
            uri VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Example: /sitemap/index.html?userid=2',
            path VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Example: /sitemap/index.html',
            ip4 VARCHAR(50) NOT NULL DEFAULT '' COMMENT '',
            dateline INT(10) unsigned NOT NULL DEFAULT 0,
            PRIMARY KEY (accessid)
		 )".$charset
    );

    $db->query_write("
		CREATE TABLE IF NOT EXISTS ".$pfx."logs_accessVar (
            varid int(10) unsigned NOT NULL auto_increment,
            accessid INT(10) unsigned NOT NULL DEFAULT 0,
            varType ENUM('GET', 'POST') DEFAULT 'GET' COMMENT '',
            varName VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'GET/POST',
            varValue TEXT NOT NULL COMMENT '',
            PRIMARY KEY (varid),
            KEY accessid (accessid)
		 )".$charset
    );

}

if ($updateManager->isUpdate('0.1.1')){

    $db->query_write("
		CREATE TABLE IF NOT EXISTS ".$pfx."logs (
            logid int(10) unsigned NOT NULL auto_increment,
            userid INT(10) unsigned NOT NULL DEFAULT 0,
            ip4 VARCHAR(50) NOT NULL DEFAULT '' COMMENT '',
            logLevel ENUM('trace', 'debug', 'info', 'warn', 'error', 'fatal') DEFAULT 'trace' COMMENT '',
            ownerType ENUM('core', 'module', 'over') DEFAULT 'over' COMMENT '',
            ownerName VARCHAR(32) NOT NULL DEFAULT '' COMMENT '',
            message VARCHAR(255) NOT NULL DEFAULT '' COMMENT '',
            debugInfo TEXT NOT NULL COMMENT 'JSON format',
            dateline INT(10) unsigned NOT NULL DEFAULT 0,
            PRIMARY KEY (logid)
		 )".$charset
    );

}

?>