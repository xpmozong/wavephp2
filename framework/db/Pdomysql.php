<?php
/**
 * PHP 5.0 以上
 *
 * @package         Wavephp
 * @author          许萍
 * @copyright       Copyright (c) 2016
 * @link            https://github.com/xpmozong/wavephp2
 * @since           Version 2.0
 *
 */

/**
 * Wavephp Application Mysql Class
 *
 * pdo数据库类
 *
 * @package         Wavephp
 * @subpackage      db
 * @author          许萍
 *
 */
class Pdomysql extends Db_Abstract
{
    private     $errno;             // 错误信息
    private     $execNums = 0;      // 执行数量

    public function __construct($config) {
        if (isset($config['slave'])) {
            $this->is_single = false;
        }
        $this->config = $config;
    }

    /**
     * 初始化
     *
     * @param array $dbConfig
     *
     */
    protected function _connect($tag)
    {
        $dbport = isset($this->config[$tag]['dbport']) ? $this->config[$tag]['dbport'] : 3306;
        $dbh = 'mysql:host='.$this->config[$tag]['dbhost'].';dbname='.$this->config[$tag]['dbname'].';port='.$dbport;
        $username = $this->config[$tag]['username'];
        $password = $this->config[$tag]['password'];
        $error = PDO::ERRMODE_SILENT;
        if (Wave::app()->config['crash_show_sql']) {
            $error = PDO::ERRMODE_EXCEPTION;
        }
        $driverOptions = array(
            PDO::ATTR_PERSISTENT                 => $this->config[$tag]['pconnect'],
            PDO::ATTR_ERRMODE                    => $error,
            PDO::MYSQL_ATTR_INIT_COMMAND         => 'SET NAMES '.$this->config[$tag]['charset'],
        );
        try{
            return new PDO($dbh, $username, $password, $driverOptions);
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * 数据库选择
     */
    protected function db_select($tag) {
        // $this->conn[$tag]->exec('use '.$this->config[$tag]['dbname']);

        return true;
    }

    /**
     * 数据库字符类型选择
     */
    protected function db_set_charset($tag) {
        // $this->conn[$tag]->exec('SET NAMES '.$this->config[$tag]['charset']);

        return true;
    }

    /**
     * 数据库执行语句
     *
     * @return blooean
     *
     */
    protected function _query($sql, $conn, $is_rw)
    {
        $start_time = microtime(TRUE);

        if ($is_rw) {
            $result = $conn->exec($sql);
            $this->execNums = $result;
        } else {
            $result = $conn->query($sql);
        }
        if ($result) {
            if (Wave::app()->config['debuger']) {
                Wave::debug_log('database', (microtime(TRUE) - $start_time), $sql);
            }
            if (isset(Wave::app()->config['write_sql_log']) && Wave::app()->config['write_sql_log']) {
                $data = array(  'op'    => 'sql_log',
                                'time'  => time(),
                                'sql'   => $sql,
                                'execute_time'=>(microtime(TRUE) - $start_time));
                $content = json_encode($data);
                $file = Wave::app()->config['write_sql_dir'].'sql_log_'.date('Y-m-d').'.txt';
                Wave::writeCache($file, $content."\n", 'a+');
            }
        }

        return $result;
    }

    /**
     * 插入数据
     *
     * @param string $table         表名
     * @param array  $array         数据数组
     *
     * @return boolean
     *
     */
    protected function _insertdb($table, $array)
    {
        $tbcolumn = $tbvalue = '';
        foreach ($array  as $key=>$value) {
            $value = $this->escape($value);
            $tbcolumn .= '`'.$key.'`'.",";
            $tbvalue  .= ''.$value.',';
        }
        $tbcolumn = "(".trim($tbcolumn,',').")";
        $tbvalue = "(".trim($tbvalue,',').")";
        $sql = "INSERT INTO `".$table."` ".$tbcolumn." VALUES ".$tbvalue;

        return $this->dbquery($sql);
    }

    /**
     * 获得刚插入数据的id
     *
     * @return int id
     *
     */
    protected function _insertId($conn)
    {
        return $conn->lastInsertId();
    }

    /**
     * 更新数据
     *
     * @param string $table         表名
     * @param array  $array         数据数组
     * @param string $conditions    条件
     *
     * @return boolean
     *
     */
    protected function _updatedb($table, $array, $conditions)
    {
        $update = array();
        foreach ($array as $key => $value) {
            $value = $this->escape($value);
            $update[] = "`$key`=$value";
        }
        $update = implode(",", $update);
        $sql = 'UPDATE `'.$table.'` SET '.$update.' WHERE '.$conditions;

        return $this->dbquery($sql);
    }

    /**
     * 获得刚执行完的条数
     *
     * @return int num
     *
     */
    protected function _affectedRows()
    {
        return $this->execNums;
    }

    /**
     * 获得查询语句单条结果
     *
     * @return array
     *
     */
    protected function _getOne($sql)
    {
        if ($this->dbquery($sql)) {
            return $this->dbquery($sql)->fetch(PDO::FETCH_ASSOC);
        } else {
            return array();
        }
    }

    /**
     * 获得查询语句多条结果
     *
     * @return array
     *
     */
    protected function _getAll($sql)
    {
        if ($this->dbquery($sql)) {
            return $this->dbquery($sql)->fetchAll(PDO::FETCH_ASSOC);
        } else {
            return array();
        }
    }

    /**
     * 删除数据
     *
     * @param string $table         表名
     * @param string $fields        条件
     *
     * @return boolean
     *
     */
    protected function _delete($table, $fields)
    {
        $sql = "DELETE FROM $table WHERE $fields";

        return $this->dbquery($sql);
    }

    /**
     * table 加 `
     */
    protected function _table($table) {
        return '`' .$table. '`';
    }

    /**
     * table list
     */
    protected function _list_tables() {
        $sql = 'SHOW TABLES FROM `'.$this->dbname.'`';

        return $this->_getAll($sql);
    }

    /**
     * table columns
     */
    protected function _list_columns($table) {
        $sql = 'SHOW COLUMNS FROM '.$this->_table($table);

        return $this->dbquery($sql);
    }

    /**
     * 清空表
     */
    protected function _truncate($table) {
        $sql = 'TRUNCATE '.$this->_table($table);

        return $this->dbquery($sql);
    }

    /**
     * mysql limit
     */
    protected function _limit($offset, $limit) {
        if ($offset == 0) {
            $offset = '';
        } else {
            $offset .= ", ";
        }

        return " LIMIT ".$offset.$limit;
    }

    /**
     * 关闭数据库连接，当您使用持续连接时该功能失效
     *
     * @return blooean
     *
     */
    protected function _close($tag)
    {
        return $this->config[$tag] = null;
    }

    /**
     * 显示自定义错误
     */
    protected function msg()
    {
        if ($this->errno && !empty(Wave::app()->config['crash_show_sql'])) {
            echo $this->getLastSql()."<br>";
            echo "<div style='color:red;'>\n";
                echo "<h4>数据库操作错误</h4>\n";
                echo "<h5>错误信息：".var_dump($this->errno)."</h5>\n";
            echo "</div>";
            die;
        } else {
            exit('数据库操作错误');
        }
    }

}

?>
