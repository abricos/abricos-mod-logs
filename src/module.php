<?php
/**
 * @package Abricos
 * @subpackage Logs
 * @copyright 2016 Alexander Kuzmin
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

/**
 * Class LogsModule
 */
class LogsModule extends Ab_Module {

    public function __construct(){
        $this->version = "0.1.0";
        $this->name = "logs";

        $this->permission = new LogsPermission($this);
    }

    public function Bos_IsMenu(){
        return true;
    }
}

class LogsAction {
    const VIEW = 10;
    const WRITE = 30;
    const ADMIN = 50;
}

class LogsPermission extends Ab_UserPermission {

    public function __construct(LogsModule $module){
        $defRoles = array(
            new Ab_UserRole(LogsAction::VIEW, Ab_UserGroup::GUEST),
            new Ab_UserRole(LogsAction::VIEW, Ab_UserGroup::REGISTERED),
            new Ab_UserRole(LogsAction::VIEW, Ab_UserGroup::ADMIN),

            new Ab_UserRole(LogsAction::WRITE, Ab_UserGroup::ADMIN),

            new Ab_UserRole(LogsAction::ADMIN, Ab_UserGroup::ADMIN)
        );
        parent::__construct($module, $defRoles);
    }

    public function GetRoles(){
        return array(
            LogsAction::VIEW => $this->CheckAction(LogsAction::VIEW),
            LogsAction::WRITE => $this->CheckAction(LogsAction::WRITE),
            LogsAction::ADMIN => $this->CheckAction(LogsAction::ADMIN)
        );
    }
}

Abricos::ModuleRegister(new LogsModule());

?>