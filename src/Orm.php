<?php

/** 数据库访问帮助类, 这个不配叫为Orm, 但又没有好名字 */
class Orm
{
    private $pdo;
    /** 1.sql server  2.mysql */
    private $dbType;

    /**
     * @param $type 1.sql server  2.mysql
     */
    function __construct($type, $host, $dbName, $user, $pwd, $options = [])
    {
        if ($type == 1) {
            if (!extension_loaded('pdo_sqlsrv')) {
                die('pdo_sqlsrv extension not loaded');
            }

        } else if ($type == 2) {
            if (!extension_loaded('pdo_mysql')) {
                die('pdo_mysql extension not loaded');
            }
        }
        // 判断有没有数据库模型文件
        $root = __DIR__;
        $modelFile = $root . '/models.php';
        if (file_exists($modelFile)) {
            require_once $modelFile;
        }


        $this->dbType = $type;
        try {
            // 如果是sql server, 判断是否含sqlsvr的扩展
            if ($type == 1) {
                $port = $options['port'] ?? 1433;
                $this->pdo = new PDO("sqlsrv:Server=$host,$port;Database=$dbName", $user, $pwd);
            } else if ($type == 2) {
                $port = $options['port'] ?? 3306;
                $this->pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbName", $user, $pwd, array(PDO::MYSQL_ATTR_INIT_COMMAND => "set names utf8"));
            } else {
                die('type error');
            }
        } catch (PDOException $e) {
            echo 'DB ERROR: ' . $e->getMessage();
            die;
        }
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /** 查询所有数据 */
    public function query($sql, $parameters = [])
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($parameters);
        return $stmt->fetchAll();
    }

    /** 只要第一行数据 */
    public function queryFirst($sql, $parameters = [])
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($parameters);
        return $stmt->fetch();
    }

    /** 执行sql语句, 返回受影响的行数 */
    public function execute($sql, $parameters = [], $expectedRow = 1): int
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($parameters);
        $count = $stmt->rowCount();
        if (isset($expectedRow) && $expectedRow >= 0 && $expectedRow != $count) {
            $errMsg = $stmt->errorInfo()[2];
            throw new Exception("Unexpected number of affected rows\nCurrent row count: ${count}\nExpected row count: ${expectedRow}\nSQL: ${sql}\nError message: ${errMsg}");
        }

        return $count;
    }

    /**是否在事务内*/
    public function inTransaction(): bool
    {
        return $this->pdo->inTransaction();
    }

    /** 开始一个事务 */
    public function transaction($callback): bool
    {
        $this->pdo->beginTransaction();
        try {
            $callback();
            $this->pdo->commit();
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
        return true;
    }

    ////////// 帮助类方法 //////////
    // 从数据库中查询数据, 支持分页, 以及页数
    public function select($table, $columns = '*', $condition = '', $order = '', $page = 1, $pageSize = 10000, &$totalPage = null)
    {

        // sql server
        if ($this->dbType == 1) {
            $sql = "SELECT ";
            if (is_string($columns)) {
                $sql .= $columns;
            } else {
                $sql .= implode(',', $columns);
            }
            $sql .= " FROM $table";
            $fields = [];
            $pdo_parameters = [];
            $where = '';
            if (is_string($condition)) {
                $where = $condition;
            } else if (is_array($condition)) {
                foreach ($condition as $field => $value) {
                    $fields[] = $field . '=:' . $field;
                    $pdo_parameters[$field] = $value;
                }
                $where = implode(" AND ", $fields);
            }
            if (!empty($where)) {
                $sql .= ' WHERE ' . $where;
            }
            if ($order == '')
                $order = 'Id';

            if (!empty($order)) {
                $sql .= ' ORDER BY ' . $order;
            }
            if ($page > 0 && $pageSize > 0) {
                $sql .= ' OFFSET ' . ($page - 1) * $pageSize . ' ROWS FETCH NEXT ' . $pageSize . ' ROWS ONLY';
            }
            $result = $this->query($sql, $pdo_parameters);
            if ($page > 0 && $pageSize > 0) {
                if (isset($totalPage)) {
                    $count = $this->select($table, 'count(*)', $condition)[0][0];
                    $totalPage = ceil($count * 1.0 / $pageSize);

                }
            }
            return $result;
        } // mysql
        else if ($this->dbType == 2) {

            $sql = "SELECT ";
            if (is_string($columns)) {
                $sql .= $columns;
            } else {
                $sql .= implode(',', $columns);
            }
            $sql .= " FROM $table";

            $fields = [];
            $pdo_parameters = [];
            $where = '';
            if (is_string($condition)) {
                $where = $condition;
            } else if (is_array($condition)) {
                foreach ($condition as $field => $value) {
                    $fields[] = '`' . $field . '`=:condition_' . $field;
                    $pdo_parameters['condition_' . $field] = $value;
                }
                $where = implode(" AND ", $fields);
            }
            if (!empty($where)) {
                $sql .= ' WHERE ' . $where;
            }
            if (!empty($order)) {
                $sql .= ' ORDER BY ' . $order;
            }
            if ($page > 0 && $pageSize > 0) {
                $sql .= ' LIMIT ' . ($page - 1) * $pageSize . ',' . $pageSize;
            }
            $result = $this->query($sql, $pdo_parameters);
            if ($page > 0 && $pageSize > 0) {
                if (isset($totalPage)) {
                    $count = $this->select($table, 'count(*)', $condition)[0][0];
                    $totalPage = ceil($count * 1.0 / $pageSize);

                }
            }
            return $result;
        } else {
            die('type error');
        }
    }

    public function search($table, $columns = '*', $condition = '', $order = '', $page = 1, $pageSize = 10000, &$totalPage = null)
    {

        // sql server
        if ($this->dbType == 1) {
            $sql = "SELECT ";
            if (is_string($columns)) {
                $sql .= $columns;
            } else {
                $sql .= implode(',', $columns);
            }
            $sql .= " FROM $table";
            $fields = [];
            $pdo_parameters = [];
            $where = '';
            if (is_string($condition)) {
                $where = $condition;
            } else if (is_array($condition)) {
                foreach ($condition as $field => $value) {
                    $fields[] = $field . ' LIKE :' . $field;
                    $pdo_parameters[$field] = $value;
                }
                $where = implode(" OR ", $fields);
            }
            if (!empty($where)) {
                $sql .= ' WHERE ' . $where;
            }
            if ($order == '')
                $order = 'Id';

            if (!empty($order)) {
                $sql .= ' ORDER BY ' . $order;
            }
            if ($page > 0 && $pageSize > 0) {
                $sql .= ' OFFSET ' . ($page - 1) * $pageSize . ' ROWS FETCH NEXT ' . $pageSize . ' ROWS ONLY';
            }
            $result = $this->query($sql, $pdo_parameters);
            if ($page > 0 && $pageSize > 0) {
                if (isset($totalPage)) {
                    $count = $this->select($table, 'count(*)', $condition)[0][0];
                    $totalPage = ceil($count * 1.0 / $pageSize);

                }
            }
            return $result;
        } // mysql
        else if ($this->dbType == 2) {

            $sql = "SELECT ";
            if (is_string($columns)) {
                $sql .= $columns;
            } else {
                $sql .= implode(',', $columns);
            }
            $sql .= " FROM $table";

            $fields = [];
            $pdo_parameters = [];
            $where = '';
            if (is_string($condition)) {
                $where = $condition;
            } else if (is_array($condition)) {
                foreach ($condition as $field => $value) {
                    $fields[] = '`' . $field . '` LIKE :condition_' . $field;
                    $pdo_parameters['condition_' . $field] = $value;
                }
                $where = implode(" OR ", $fields);
            }
            if (!empty($where)) {
                $sql .= ' WHERE ' . $where;
            }
            if (!empty($order)) {
                $sql .= ' ORDER BY ' . $order;
            }
            if ($page > 0 && $pageSize > 0) {
                $sql .= ' LIMIT ' . ($page - 1) * $pageSize . ',' . $pageSize;
            }
            $result = $this->query($sql, $pdo_parameters);
            if ($page > 0 && $pageSize > 0) {
                if (isset($totalPage)) {
                    $count = $this->select($table, 'count(*)', $condition)[0][0];
                    $totalPage = ceil($count * 1.0 / $pageSize);

                }
            }
            return $result;
        } else {
            die('type error');
        }
    }

    public function update($table, $parameters = [], $condition = [], $expectedRow = 1): int
    {

        $sql = "UPDATE $table SET ";
        $fields = [];
        $pdo_parameters = [];
        foreach ($parameters as $field => $value) {
            $fields[] = '`' . $field . '`=:field_' . $field;
            $pdo_parameters['field_' . $field] = $value;
        }
        $sql .= implode(',', $fields);
        $fields = [];
        $where = '';
        if (is_string($condition)) {
            $where = $condition;
        } else if (is_array($condition)) {
            foreach ($condition as $field => $value) {
                $parameters[$field] = $value;
                $fields[] = '`' . $field . '`=:condition_' . $field;
                $pdo_parameters['condition_' . $field] = $value;
            }
            $where = implode(' AND ', $fields);
        }
        if (!empty($where)) {
            $sql .= ' WHERE ' . $where;
        }


        return $this->execute($sql, $pdo_parameters, $expectedRow);
    }

    /** 插入数据, 并返回主键Id */
    public function insert($table, $parameters = [], $expectedRow = 1)
    {

        $sql = "INSERT INTO $table";
        $fields = [];
        $placeholder = [];
        foreach ($parameters as $field => $value) {
            $placeholder[] = ':' . $field;
            $fields[] = '`' . $field . '`';
        }
        $sql .= '(' . implode(",", $fields) . ') VALUES (' . implode(",", $placeholder) . ')';

        $this->execute($sql, $parameters, $expectedRow);
        return $this->pdo->lastInsertId();
    }

    public function delete($table, $condition = [], $expectedRow = 1): int
    {

        $sql = "DELETE FROM $table";
        $fields = [];
        $pdo_parameters = [];
        $where = '';
        if (is_string($condition)) {
            $where = $condition;
        } else if (is_array($condition)) {
            foreach ($condition as $field => $value) {
                $fields[] = '`' . $field . '`=:condition_' . $field;
                $pdo_parameters['condition_' . $field] = $value;
            }
            $where = implode(' AND ', $fields);
        }
        if (!empty($where)) {
            $sql .= ' WHERE ' . $where;
        }
        return $this->execute($sql, $pdo_parameters, $expectedRow);
    }


    function __destruct()
    {
        $this->pdo = null;
    }

}

