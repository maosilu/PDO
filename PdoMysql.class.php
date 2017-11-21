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
//$sql = "SELECT * FROM pdo_user1";
//var_dump($PdoMysql->getAll($sql));
/*$sql = "SELECT * FROM pdo_user WHERE id=23";
var_dump($PdoMysql->getRow($sql));*/
/*$sql = "INSERT INTO pdo_user(username, password, email) VALUES('test3', 'test1', 'test1@imooc.com')";
echo $PdoMysql->execute($sql);
echo "<hr/>";
echo $PdoMysql::$lastInsertId;*/
echo "<pre>";
$tabName = 'pdo_user';
$priId = 27;
//$fields = array();
//$fields = 'username, email';
//$fields = '*';
$fields = array('username', 'email');
var_dump($PdoMysql->findById($tabName, $priId, $fields));
