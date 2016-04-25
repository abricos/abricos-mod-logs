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
            'Config' => 'LogsConfig'
        );
    }

    protected function GetStructures(){
        return 'Config';
    }

    public function ResponseToJSON($d){
        switch ($d->do){
            case "config":
                return $this->ConfigToJSON();
            case "configSave":
                return $this->ConfigSaveToJSON($d->config);

        }
        return null;
    }

    function GetIP(){
        if (!empty(@$_SERVER['HTTP_CLIENT_IP'])){
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty(@$_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return @$_SERVER['REMOTE_ADDR'];
        }
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

    public function AccessLogAppend(){
        $ip = $this->GetIP();
        $uri = $this->FetchURI();
        $arr = parse_url($uri);

        $accessid = LogsQuery::AccessLogAppend($this, $uri, $arr['path'], $ip);

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
                $vars[$name] = $value;
            }
        }

        if (count($vars) > 0){
            LogsQuery::AccessLogVarsAppend($this, $accessid, "POST", $vars);
        }

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

        $phs = Abricos::GetModule('logs')->GetPhrases();
        $phs->Set("accessLog", !!$d->accessLog);

        Abricos::$phrases->Save();
    }

}

?>