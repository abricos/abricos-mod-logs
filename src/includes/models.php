<?php
/**
 * @package Abricos
 * @subpackage Logs
 * @copyright 2016 Alexander Kuzmin
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @author Alexander Kuzmin <roosit@abricos.org>
 */


/**
 * Class LogsLog
 *
 * @property int $userid
 * @property string $ip4
 * @property string $level
 * @property string $ownerType
 * @property string $ownerName
 * @property string $message
 * @property string $debugInfo
 * @property int $dateline
 */
class LogsLog extends AbricosModel {
    protected $_structModule = 'logs';
    protected $_structName = 'Log';
}

/**
 * Class LogsLogList
 *
 * @method LogsLog Get($logid)
 */
class LogsLogList extends AbricosModelList {
}

/**
 * Class LogsAccess
 *
 * @property int $userid
 * @property string $method
 * @property string $path
 * @property string $ip4
 * @property LogsAccessVarList $vars
 * @property int $dateline
 */
class LogsAccess extends AbricosModel {
    protected $_structModule = 'logs';
    protected $_structName = 'Access';
}

/**
 * Class LogsAccessList
 *
 * @method LogsAccessVar Get($accessid)
 */
class LogsAccessList extends AbricosModelList {
}

/**
 * Class LogsAccessVar
 *
 * @property int $accessid
 * @property string $type
 * @property string $name
 * @property string $value
 */
class LogsAccessVar extends AbricosModel {
    protected $_structModule = 'logs';
    protected $_structName = 'AccessVar';
}

class LogsAccessVarList extends AbricosModelList {
}


/**
 * Class LogsConfig
 *
 * @property boolean $use
 * @property string $level Valid: off|fatal|error|warn|info|debug|trace|all
 * @property boolean $accessLog
 */
class LogsConfig extends AbricosModel {
    protected $_structModule = 'logs';
    protected $_structName = 'Config';
}


?>