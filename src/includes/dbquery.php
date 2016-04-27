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

    public static function LogList(AbricosApplication $app, $levels){

        $whLevels = array();
        for ($i = 0; $i < count($levels); $i++){
            $whLevels[] = "logLevel='".bkstr($levels[$i])."'";
        }

        $db = $app->db;
        $sql = "
            SELECT *
            FROM ".$db->prefix."logs
            WHERE (".implode(" OR ", $whLevels).")
            ORDER BY logid DESC
            LIMIT 50
        ";
        return $db->query_read($sql);
    }

    public static function LogOwnerList(AbricosApplication $app){
        $db = $app->db;
        $sql = "
            SELECT DISTINCT l.ownerType, l.ownerName
            FROM ".$db->prefix."logs l
        ";
        return $db->query_read($sql);
    }

    public static function LogAppend(AbricosApplication $app, $ip4, $level, $ownerType, $ownerName, $message, $debugInfo){
        $db = $app->db;
        $sql = "
            INSERT INTO ".$db->prefix."logs
            (userid, ip4, logLevel, ownerType, ownerName, message, debugInfo, dateline) VALUES (
                ".intval(Abricos::$user->id).",
                '".bkstr($ip4)."',
                '".bkstr($level)."',
                '".bkstr($ownerType)."',
                '".bkstr($ownerName)."',
                '".bkstr($message)."',
                '".bkstr($debugInfo)."',
                ".intval(TIMENOW)."
            )
        ";
        $db->query_write($sql);
        return $db->insert_id();
    }

    public static function AccessLogAppend(AbricosApplication $app, $method, $uri, $path, $ip4){
        $db = $app->db;
        $sql = "
            INSERT INTO ".$db->prefix."logs_access (userid, method, uri, path, ip4, dateline) VALUES (
                ".intval(Abricos::$user->id).",
                '".bkstr($method)."',
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
            INSERT INTO ".$db->prefix."logs_accessVar (accessid, varType, varName, varValue) VALUES
            ".implode(", ", $ins)."
        ";
        $db->query_write($sql);
    }

    public static function AccessList(AbricosApplication $app, $search = ''){
        $where = '';

        if (!empty($search)){
            $where = "WHERE path LIKE '%".bkstr($search)."%'";
        }

        $db = $app->db;
        $sql = "
            SELECT *
            FROM ".$db->prefix."logs_access
            ".$where."
            ORDER BY accessid DESC
            LIMIT 50
        ";
        return $db->query_read($sql);
    }

    public static function AccessVarList(AbricosApplication $app, $ids){
        $wha = array("accessid=0");
        $cnt = count($ids);
        for ($i = 0; $i < $cnt; $i++){
            $wha[] = "accessid=".intval($ids[$i]);
        }

        $db = $app->db;
        $sql = "
            SELECT *
            FROM ".$db->prefix."logs_accessVar
            WHERE ".implode(" OR ", $wha)."
            ORDER BY accessid DESC
        ";
        return $db->query_read($sql);
    }

}

?>