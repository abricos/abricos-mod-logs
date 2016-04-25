<?php
/**
 * @package Abricos
 * @subpackage Logs
 * @copyright 2016 Alexander Kuzmin
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @author Alexander Kuzmin <roosit@abricos.org>
 */


/**
 * Class LogsConfig
 *
 * @property boolean $use
 * @property boolean $accessLog
 */
class LogsConfig extends AbricosModel {
    protected $_structModule = 'logs';
    protected $_structName = 'Config';
}


?>