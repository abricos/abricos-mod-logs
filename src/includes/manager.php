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
 */
class LogsManager extends Ab_ModuleManager {

    public function IsAdminRole(){
        return $this->IsRoleEnable(LogsAction::ADMIN);
    }

    public function IsWriteRole(){
        if ($this->IsAdminRole()){
            return true;
        }
        return $this->IsRoleEnable(LogsAction::WRITE);
    }

    public function IsViewRole(){
        if ($this->IsWriteRole()){
            return true;
        }
        return $this->IsRoleEnable(LogsAction::VIEW);
    }

    public function AJAX($d) {
        return $this->GetApp()->AJAX($d);
    }

    public function Bos_MenuData(){
        if (!$this->IsAdminRole()){
            return null;
        }
        $i18n = $this->module->I18n();
        return array(
            array(
                "name" => "logs",
                "title" => $i18n->Translate('title'),
                "icon" => "/modules/logs/images/cp_icon.gif",
                "url" => "logs/wspace/ws",
                "parent" => "controlPanel"
            )
        );
    }
}

?>