<?php
/**
 * @package Abricos
 * @subpackage Logs
 * @copyright 2016 Alexander Kuzmin
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

/**
 * Class LogsQuery
 */
class LogsQuery {

    public static function AccessLogAppend(AbricosApplication $app, $uri, $path, $ip4){
        $db = $app->db;
        $sql = "
            INSERT INTO ".$db->prefix."logs_access (method, uri, path, ip4, dateline) VALUES (
                'GET',
                '".bkstr($uri)."',
                '".bkstr($path)."',
                '".bkstr($ip4)."',
                ".intval(TIMENOW)."
            )
        ";
        $db->query_write($sql);
        return $db->insert_id();
    }

    public static function AccessLogVarsAppend(AbricosApplication $app, $accessid, $type, $vars){
        $db = $app->db;
        $ins = array();
        foreach ($vars as $varName => $varValue){
            $ins[] = "(".intval($accessid).", '".bkstr($type)."', '".bkstr($varName)."', '".bkstr($varValue)."')";
        }

        $sql = "
            INSERT INTO ".$db->prefix."logs_accessvar (accessid, varType, varName, varValue) VALUES
            ".implode(", ", $ins)."
        ";
        $db->query_write($sql);
    }
}

?>