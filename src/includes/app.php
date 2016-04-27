<?php
/**
 * @package Abricos
 * @subpackage Logs
 * @copyright 2016 Alexander Kuzmin
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

/**
 * Class LogsManager
 *
 * @property LogsManager $manager
 */
class LogsApp extends AbricosApplication {

    protected function GetClasses(){
        return array(
            'Log' => 'LogsLog',
            'LogList' => 'LogsLogList',
            'Access' => 'LogsAccess',
            'AccessList' => 'LogsAccessList',
            'AccessVar' => 'LogsAccessVar',
            'AccessVarList' => 'LogsAccessVarList',
            'Config' => 'LogsConfig'
        );
    }

    protected function GetStructures(){
        return 'Log,Access,AccessVar,Config';
    }

    public function ResponseToJSON($d){
        switch ($d->do){
            case "logList":
                return $this->LogListToJSON($d->filter);
            case "logOwnerList":
                return $this->LogOwnerListToJSON();
            case "accessList":
                return $this->AccessListToJSON($d->filter);
            case "config":
                return $this->ConfigToJSON();
            case "configSave":
                return $this->ConfigSaveToJSON($d->config);
        }
        return null;
    }

    public function GetIP(){
        if (!empty($_SERVER['HTTP_CLIENT_IP'])){
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'];
        }
    }

    public function GetRequestMethod(){
        return $_SERVER['REQUEST_METHOD'];
    }

    public function FetchURI(){
        if ($_SERVER['REQUEST_URI'] OR $_ENV['REQUEST_URI']){
            return $_SERVER['REQUEST_URI'] ? $_SERVER['REQUEST_URI'] : $_ENV['REQUEST_URI'];
        }
        if ($_SERVER['PATH_INFO'] OR $_ENV['PATH_INFO']){
            $scriptPath = $_SERVER['PATH_INFO'] ? $_SERVER['PATH_INFO'] : $_ENV['PATH_INFO'];
        } else if ($_SERVER['REDIRECT_URL'] OR $_ENV['REDIRECT_URL']){
            $scriptPath = $_SERVER['REDIRECT_URL'] ? $_SERVER['REDIRECT_URL'] : $_ENV['REDIRECT_URL'];
        } else {
            $scriptPath = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_ENV['PHP_SELF'];
        }
        if ($_SERVER['QUERY_STRING'] OR $_ENV['QUERY_STRING']){
            $scriptPath .= '?'.($_SERVER['QUERY_STRING'] ? $_SERVER['QUERY_STRING'] : $_ENV['QUERY_STRING']);
        }
        return $scriptPath;
    }

    private static $_cfgLevels = array(
        'off' => 0,
        AbricosLogger::TRACE => 500,
        AbricosLogger::DEBUG => 1000,
        AbricosLogger::INFO => 1500,
        AbricosLogger::WARN => 2000,
        AbricosLogger::ERROR => 2500,
        AbricosLogger::FATAL => 3000,
        'all' => 10000
    );

    private static $_levels = array(
        AbricosLogger::TRACE => 500,
        AbricosLogger::DEBUG => 1000,
        AbricosLogger::INFO => 1500,
        AbricosLogger::WARN => 2000,
        AbricosLogger::ERROR => 2500,
        AbricosLogger::FATAL => 3000,
    );

    public function IsLogAppend($level){
        $config = $this->Config();
        $numCfgLevel = LogsApp::$_cfgLevels[$config->level];
        $numLevel = LogsApp::$_levels[$level];

        return $numLevel <= $numCfgLevel;
    }

    public function LogAppend($level, $message, $ownerType, $ownerName, $debugInfo){
        if (!$this->IsLogAppend($level)){
            return;
        }

        if (is_array($debugInfo)){
            $debugInfo = json_encode($debugInfo);
        } else {
            $debugInfo = '';
        }

        $ip = $this->GetIP();
        $logid = LogsQuery::LogAppend($this, $ip, $level, $ownerType, $ownerName, $message, $debugInfo);
        return $logid;
    }

    public function LogListToJSON($filter){
        $res = $this->LogList($filter);
        return $this->ResultToJSON('logList', $res);
    }

    /**
     * @param $filter
     * @return int|LogsLogList
     */
    public function LogList($filter){
        if (!$this->manager->IsAdminRole()){
            return AbricosResponse::ERR_FORBIDDEN;
        }

        $level = isset($filter->level) ? $filter->level : 'all';
        $cfgLevels = LogsApp::$_cfgLevels;
        $levels = LogsApp::$_levels;
        $nLevel = isset($cfgLevels[$level]) ? $cfgLevels[$level] : $cfgLevels['all'];

        $aFilterLevels = array();

        foreach ($levels as $key => $value){
            if ($value >= $nLevel || $level === 'all'){
                $aFilterLevels[] = $key;
            }
        }

        /** @var LogsLogList $list */
        $list = $this->InstanceClass('LogList');

        $rows = LogsQuery::LogList($this, $aFilterLevels);
        while (($d = $this->db->fetch_array($rows))){
            $list->Add($this->InstanceClass('Log', $d));
        }

        return $list;
    }

    public function LogOwnerListToJSON(){
        $res = $this->LogOwnerList();
        return $this->ResultToJSON('logOwnerList', $res);
    }

    public function LogOwnerList(){
        if (!$this->manager->IsAdminRole()){
            return AbricosResponse::ERR_FORBIDDEN;
        }

        $ret = array();

        $rows = LogsQuery::LogOwnerList($this);
        while (($d = $this->db->fetch_array($rows))){
            $ret[] = $d['ownerType'].":".$d['ownerName'];
        }
        return $ret;
    }

    public function AccessLogAppend(){
        $config = $this->Config();

        if (!$config->accessLog){
            return;
        }

        $method = $this->GetRequestMethod();
        $ip = $this->GetIP();
        $uri = $this->FetchURI();
        $arr = parse_url($uri);

        $accessid = LogsQuery::AccessLogAppend($this, $method, $uri, $arr['path'], $ip);

        $vars = array();
        foreach ($_GET as $name => $value){
            $value = trim($value);
            if (!empty($value)){
                $vars[$name] = $value;
            }
        }

        if (count($vars) > 0){
            LogsQuery::AccessLogVarsAppend($this, $accessid, "GET", $vars);
        }

        $vars = array();
        foreach ($_POST as $name => $value){
            $value = trim($value);
            if (!empty($value)){
                $vars[$name] = '';
            }
        }

        if (count($vars) > 0){
            LogsQuery::AccessLogVarsAppend($this, $accessid, "POST", $vars);
        }
    }

    public function AccessListToJSON($filter){
        $res = $this->AccessList($filter);
        return $this->ResultToJSON('accessList', $res);
    }

    public function AccessList($filter){
        if (!$this->manager->IsAdminRole()){
            return AbricosResponse::ERR_FORBIDDEN;
        }


        /** @var LogsAccessVarList $list */
        $list = $this->InstanceClass('AccessList');

        $ids = array();

        $search = isset($filter->search) ? $filter->search : '';

        $rows = LogsQuery::AccessList($this, $search);
        while (($d = $this->db->fetch_array($rows))){
            /** @var LogsAccess $access */
            $access = $this->InstanceClass('Access', $d);
            $list->Add($access);
            $ids[] = $access->id;
        }

        $rows = LogsQuery::AccessVarList($this, $ids);
        while (($d = $this->db->fetch_array($rows))){
            /** @var LogsAccessVar $accessVar */
            $accessVar = $this->InstanceClass('AccessVar', $d);

            $access = $list->Get($accessVar->accessid);
            $access->vars->Add($accessVar);
        }
        return $list;
    }

    public function ConfigToJSON(){
        $res = $this->Config();
        return $this->ResultToJSON('config', $res);
    }

    /**
     * @return LogsConfig
     */
    public function Config(){
        if (isset($this->_cache['Config'])){
            return $this->_cache['Config'];
        }

        if (!$this->manager->IsViewRole()){
            return AbricosResponse::ERR_FORBIDDEN;
        }

        $phrases = Abricos::GetModule('logs')->GetPhrases();

        $d = array();
        for ($i = 0; $i < $phrases->Count(); $i++){
            $ph = $phrases->GetByIndex($i);
            $d[$ph->id] = $ph->value;
        }

        if (!isset($d['level'])){
            $d['level'] = "off";
        }

        if (!isset($d['accessLog'])){
            $d['accessLog'] = "true";
        }

        /** @var LogsConfig $config */
        $config = $this->InstanceClass('Config', $d);
        $config->use = false;
        if (isset(Abricos::$config['module']['logs']['use'])){
            $config->use = !!Abricos::$config['module']['logs']['use'];
        }

        return $this->_cache['Config'] = $config;
    }

    public function ConfigSaveToJSON($d){
        $this->ConfigSave($d);
        return $this->ConfigToJSON();
    }

    public function ConfigSave($d){
        if (!$this->manager->IsAdminRole()){
            return AbricosResponse::ERR_FORBIDDEN;
        }

        $config = $this->Config();

        if (!isset($d->level)){
            $d->level = 'off';
        }
        $config->level = $d->level;

        if (!isset($d->accessLog)){
            $d->accessLog = false;
        }
        $config->accessLog = $d->accessLog;

        $phs = Abricos::GetModule('logs')->GetPhrases();
        $phs->Set("level", $config->level);
        $phs->Set("accessLog", $config->accessLog);

        Abricos::$phrases->Save();
    }

}

?>