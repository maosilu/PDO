<?php

/**
 * PDO数据库类的封装
 * User: maosilu
 * Date: 2017/11/15
 * Time: 上午10:39
 */
header('Content-type:text/html;charset=utf-8');
class PdoMysql
{
    public static $config = array(); // 设置连接参数
    public static $link = null; //保存连接标识符
    public static $pconnect = false; // 是否开启持久连接
    public static $dbVersion = null; // 保存数据库版本
    public static $connected = false; // 判断是否连接成功
    public static $PDOStatement = null; // 保存PDOStatement对象
    public static $queryStr = null; // 保存最后执行的操作
    public static $error = null; // 保存错误信息
    public static $lastInsertId = null; // 最后插入记录的ID
    public static $numRows = null; // 上一步操作产生受影响记录的条数

    /**
     * 保存PDO的连接
     * @param string $dbConfig
     * @return boolean
    */
    public function __construct($dbConfig=''){
        if(!class_exists('PDO')){
            self::throw_exception('不支持PDO，请先开启！');
        }
        if(!is_array($dbConfig)){
            $dbConfig = array(
                'hostname' => DB_HOST,
                'username' => DB_USER,
                'password' => DB_PWD,
                'database' => DB_NAME,
                'hostport' => DB_PORT,
                'dbms'     => DB_TYPE,
                'dsn'      => DB_TYPE.':host='.DB_HOST.';dbname='.DB_NAME,
            );
        }

        if(empty($dbConfig['hostname'])) self::throw_exception('没有定义数据库配置，请先定义！');
        self::$config = $dbConfig;
        if(empty(self::$config['params'])) self::$config['params'] = array();
        if(!isset(self::$link)){
            $configs = self::$config;
            if(self::$pconnect){
                // 开启长连接，添加到配置数组中
                $configs['params'][constant('PDO::ATTR_PERSISTENT')] = true;
            }

            try{
               self::$link = new PDO($configs['dsn'], $configs['username'], $configs['password'], $configs['params']);
            }catch(PDOException $e){
                self::throw_exception($e->getMessage());
            }

            if(!self::$link){
                self::throw_exception('PDO连接错误！');
                return false;
            }
            self::$link->exec('SET NAMES '.DB_CHARSET);
            self::$dbVersion = self::$link->getAttribute(constant('PDO::ATTR_SERVER_VERSION'));
            self::$connected = true;

            unset($configs);
        }
    }

    /**
     * 得到所有记录
     * */
    public static function getAll($sql = null){
        if($sql != null){
            self::query($sql);
        }
        $result = self::$PDOStatement->fetchAll(constant("PDO::FETCH_ASSOC"));
        return $result;
    }
    /**
     * 得到单条记录
     * @param sting $sql
     * @return mixed
    */
    public static function getRow($sql = ''){
        if($sql != null){
            self::query($sql);
        }
        $result = self::$PDOStatement->fetch(constant("PDO::FETCH_ASSOC"));
        return $result;
    }
    /**
     * 根据主键查找记录
     * @param string $tabName 表名
     * @param int $priId 主键值
     * @param string $fields 要查询的字段
     * @return array 根据主键查找出的一条记录结果
    */
    public static function findById($tabName, $priId, $fields='*'){
        $sql = "SELECT %s FROM %s WHERE id=%d";
        return self::getRow(sprintf($sql, self::parseFields($fields), $tabName, $priId));
    }
    /**
     * 执行普通查询
     * @param string $tables
     * @param string $where
     * @param string|array $fields
     * @param string|array $group
     * @param string $having
     * @param string|array $order
     * @param string|array $limit
     * @return array
    */
    public static function find($tables, $where=null, $fields='*', $group=null, $having=null, $order=null, $limit=null){
        $sql = 'SELECT '.self::parseFields($fields).' FROM '.$tables
            .self::parseWhere($where)
            .self::parseGroup($group)
            .self::parseHaving($having)
            .self::parseOrder($order)
            .self::parseLimit($limit);
        $dataAll = self::getAll($sql);
        return count($dataAll)==1 ? $dataAll[0] : $dataAll;
    }
    /**
     * 添加记录的操作
     * @param array $data 要添加的记录
     * @param string $table 表名
     * @return boolean|int
    */
    public static function add($data, $table){
        $keys = array_keys($data);
        array_walk($keys, array('PdoMysql', 'addSpecialChar'));
        $fieldsStr = join(',', $keys);
        $values = "'".join("','", array_values($data))."'";
        $sql = "INSERT INTO {$table}({$fieldsStr}) VALUES({$values})";
//        echo $sql;
        return self::execute($sql);
    }
    /**
     * 更新操作
     * @param array $data
     * @param string $table
     * @param $where
     * @param string|array $order
     * @param string|array|int $limit SQL更新语句中的limit只能有一个参数
     * @return boolean|int
    */
    public static function update($data,$table,$where='',$order='',$limit=0){
        $sets = '';
        foreach($data as $key=>$val){
            $sets .= $key."='".$val."',";
        }
        $sets = rtrim($sets, ',');
        $sql = "UPDATE {$table} SET {$sets} ".self::parseWhere($where).self::parseOrder($order).self::parseLimit($limit);
//        echo $sql;
        return self::execute($sql);
    }
    /**
     * 删除记录的操作
     * @param string $table
     * @param string $where
     * @param string|array $order
     * @param string|int|array $limit SQL删除语句中的limit只能有一个参数
     * @return boolean|int
    */
    public static function delete($table, $where=null, $order=null, $limit=0){
        $sql = "DELETE FROM {$table} ".self::parseWhere($where).self::parseOrder($order).self::parseLimit($limit);
        return self::execute($sql);
    }
    /**
     * 得到最后执行的SQL语句
     * @return string
    */
    public static function getLastSql(){
        $link = self::$link;
        if(!$link) return false;
        return self::$queryStr;
    }
    /**
     * 得到上一步插入操作产生的AUTO_INCREMENT
     * @return int
    */
    public static function getLastInsertId(){
        $link = self::$link;
        if(!$link) return false;
        return self::$lastInsertId;
    }
    /**
     * 得到数据库中的数据表
     * @return array $tables
    */
    public static function showTables(){
        $tables = array();
        if(self::query("SHOW tables")){
            $result = self::getAll();
            foreach($result as $key=>$val){
                $tables[$key] = current($val);
            }
        }
        return $tables;
    }
    /**
     * 得到数据库的版本
     * @return string
    */
    public static function getDBVersion(){
        $link = self::$link;
        if(!$link) return false;
        return self::$dbVersion;
    }
    /**
     * 解析where条件
     * @param string $where where条件
     * @return string where条件
    */
    public static function parseWhere($where){
        $whereStr = '';
        if(is_string($where) && !empty($where)){
            $whereStr = $where;
        }
        return empty($whereStr) ? '' : ' WHERE '.$whereStr;
    }
    /**
     * 解析group by
     * @param string $group 分组条件
     * @return string 分组条件
    */
    public static function parseGroup($group){
        $groupStr = '';
        if(is_array($group) && !empty($group)){
            $groupStr .= ' GROUP BY '.implode(',', $group);
        }elseif(is_string($group) && !empty($group)){
            $groupStr .= ' GROUP BY '.$group;
        }
        return empty($groupStr) ? '' : $groupStr;
    }
    /**
     * 对分组结果通过having字句进行二次筛选
     * @param string $having
     * @return string
    */
    public static function parseHaving($having){
        $havingStr = '';
        if(is_string($having) && !empty($having)){
            $havingStr = $having;
        }
        return empty($havingStr) ? '' : ' HAVING '.$havingStr;
    }
    /**
     * 解析order by
     * @param string|array $order
    */
    public static function parseOrder($order){
        $orderStr = '';
        if(is_array($order)){
            $orderStr .= ' ORDER BY '.join(',', $order);
        }elseif(is_string($order) && !empty($order)){
            $orderStr .= ' ORDER BY '.$order;
        }
        return $orderStr;
    }
    /**
     * 解析限制显示条数limit
     * 可以有两个值的形式 limit 3 等同于 limit 0,3
     * @param string|array|integer $limit
     * @return string
    */
    public static function parseLimit($limit){
        $limitStr = '';
        if(is_array($limit)){
            if(count($limit) > 1){
                $limitStr .= ' LIMIT '.$limit[0].','.$limit[1];
            }else{
                $limitStr .= ' LIMIT '.$limit[0];
            }
        }elseif(is_string($limit) && !empty($limit)){
            $limitStr .= ' LIMIT '.$limit;
        }elseif(is_numeric($limit) && $limit>0){
            $limitStr .= ' LIMIT '.$limit;
        }
        return $limitStr;
    }
    /**
     * 解析字段，检查拼接查询语句中要查询的字段，使之合理化
    */
    public static function parseFields($fields){
        if(is_array($fields) && !empty($fields)){
            array_walk($fields, array('PdoMysql', 'addSpecialChar'));
            $fieldsStr = implode(',', $fields);
        }elseif(is_string($fields) && !empty($fields) && $fields !== '*'){
            if(strpos($fields, '`') === false){
                $fields = explode(',', $fields);
                array_walk($fields, array('PdoMysql', 'addSpecialChar'));
                $fieldsStr = implode(',', $fields);
            }else{
                $fieldsStr = $fields;
            }
        }else{
            $fieldsStr = '*';
        }

        return $fieldsStr;
    }

    /**
     * 通过反引号引用字段，防止使用mysql保留的特殊字作为字段而产生错误
     * @param array &$value 应用mysql中需要查找的字段$fields数组中的值
     * @return array 返回一个数组，数组中的每个值都添加了反引号
    */
    public static function addSpecialChar(&$value){
        if($value==='*' || strpos($value, '.') !== false || strpos($value, '`') !== false){
            //不用做处理
        }elseif(strpos($value, '`') === false){
            $value = '`'.trim($value).'`';
        }

        return $value;
    }
    /**
     * 执行增删改操作，返回受影响的记录的条数
     * @param string $sql
     * $return boolean|int 
    */
    public static function execute($sql = null){
        $link = self::$link;
        if(!$link) return false;
        if(!empty(self::$PDOStatement)) self::free();
        self::$queryStr = $sql;
        $result = $link->exec(self::$queryStr);
        self::haveErrorThrowException();
        if($result){
            self::$lastInsertId = $link->lastInsertId(); // 如果执行插入语句，保存上一步插入操作产生的的AUTO_INCREMENT
            self::$numRows = $result; // 如果执行的是删除、更新操作，返回的是受影响的行数
            return self::$numRows;
        }else{
            return false;
        }
    }
    /**
     * 释放结果集
     * */
    public static function free(){
        self::$PDOStatement = null;
    }
    public static function query($sql = ''){
        $link = self::$link;
        if(!$link) return false;
        //判断之前是否有结果集，如果有的话，释放结果集
        if(!empty(self::$PDOStatement)) self::free();
        // 保存最后使用的sql语句
        self::$queryStr = $sql;
        self::$PDOStatement = $link->prepare(self::$queryStr);
        $res = self::$PDOStatement->execute();
        self::haveErrorThrowException();
        return $res;
    }

    public static function haveErrorThrowException(){
        $obj = empty(self::$PDOStatement) ? self::$link : self::$PDOStatement;
        $arrError = $obj->errorInfo();
//        var_dump($arrError);
        if($arrError[0] != '00000'){
            self::$error = 'SQLSTATE: '.$arrError[0].'<br/>SQL Error: '.$arrError[2]."<br/>Error SQL: ".self::$queryStr; // 保存错误信息
            self::throw_exception(self::$error);
            return false;
        }
        if(self::$queryStr == ''){
            self::throw_exception('没有执行的SQL语句！');
            return false;
        }
    }

    /**
     * 自定义错误处理
     * @param string $errMsg 错误信息
    */
    public static function throw_exception($errMsg){
        echo '<div style="width:100%;background-color:#ABCDEF;color:black;font-size:20px;padding: 20px 0px;">
'.$errMsg.'
</div>';
    }

}

require_once 'config.php';
$PdoMysql = new PdoMysql();
//var_dump($PdoMysql);

//测试getAll()方法
//$sql = "SELECT * FROM pdo_user1";
//var_dump($PdoMysql->getAll($sql));

//测试getRow()方法
/*$sql = "SELECT * FROM pdo_user WHERE id=23";
var_dump($PdoMysql->getRow($sql));*/

//测试execute()方法
/*$sql = "INSERT INTO pdo_user(username, password, email) VALUES('test3', 'test1', 'test1@imooc.com')";
echo $PdoMysql->execute($sql);
echo "<hr/>";
echo $PdoMysql::$lastInsertId;*/

//echo "<pre>";

//测试findById()方法
/*$tabName = 'pdo_user';
$priId = 27;*/
//$fields = array();
//$fields = 'username, email';
//$fields = '*';
/*$fields = array('username', 'email');
var_dump($PdoMysql->findById($tabName, $priId, $fields));*/

//测试find()方法
$tables = 'pdo_user';
$where = 'id > 20';
$fields = '*';
//$group = 'username, email';
//$having = "email = 'test1@imooc.com'";
//$order = 'email asc,id desc';
//$order = 'email,id asc';
//$limit = '1,1';
//$limit = 1;
/*$limit = array(1);
var_dump($PdoMysql->find($tables, $where, $fields, null, $having=null, $order=null, $limit));*/

//add()方法测试
/*$data = array(
    'username' => 'meimei1',
    'password' => 'meimei1',
    'email' => 'meimei1@imooc.com'
);
$table = 'pdo_user';
var_dump($PdoMysql->add($data, $table));*/

//update()方法测试
/*$data = array(
    'username' => 'meimei4',
    'password' => 'meimei3',
    'email' => 'meimei3@imooc.com'
);
$table = 'pdo_user';
$where = 'id<=19 ';
$order = 'id desc';
$limit = array(1);
var_dump($PdoMysql->update($data, $table,$where, $order, $limit));*/

//delete()方法的测试
/*$table = 'pdo_user';
$where = 'id<6';
$order = 'id DESC';
$limit = '2';
var_dump($PdoMysql->delete($table, $where, $order=null, $limit));*/

//showTables()函数测试
var_dump($PdoMysql->showTables());