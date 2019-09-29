<?php

namespace Common\Models;



abstract class Base extends \Phalcon\Mvc\Model
{
    protected $pagesize = 10;
    private static $pdo = null;
    public $data = [];

    /**
     * 执行存储过程
     * Author: 18042542@qq.com 2018/4/21
     */
    public function exec_proc( $sqlStatement = '', $type = '', $count = '' )
    {
        $db = $this->getDI()->getDb();
//        $stmt = $this->getReadConnection()->query($sqlStatement)->getInternalResult();
        $stmt = $db->query($sqlStatement)->getInternalResult();
        $stmt->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        $list = $stmt->fetchAll();
        if ($type == 'list') {
            $stmt->nextRowset();
            $info = $stmt->fetchAll();
        }
        $stmt->closeCursor();
        if ($type == 'list') {
            return [
                'info' => (count($info) == 1) ? $info[0] : $info,
                'list' => $list
            ];
        } else {
            return (count($list) == 1 && $count == '') ? $list[0] : $list;
        }
    }


    public function exec_proc_list( $sqlStatement = '', $type = '', $count = '' )
    {

        $db = $this->getDI()->get('db');


        $stmt = $db->query($sqlStatement)->getInternalResult();
        $stmt->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        $list = $stmt->fetchAll();
        if ($type == 'list') {
            $stmt->nextRowset();
            $info = $stmt->fetchAll();
        }
        $stmt->closeCursor();
        if ($type == 'list') {
            return [
                'info' =>  $info,
                'list' => $list
            ];
        } else {
            return  $list;
        }
    }

    public function exec_proc_r( $sqlStatement = '', $type = '', $count = '' )
    {
        $config = $this->getDI()->get('config');
        if (property_exists($config, 'master_slave') && $config->master_slave == 'master') {
            // 主库
            $db = $this->getDI()->getDb();
        }else{
            // 从库
            $db = $this->getDI()->get('db_r');
        }
        $stmt = $db->query($sqlStatement)->getInternalResult();
        $stmt->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        $list = $stmt->fetchAll();
        if ($type == 'list') {
            $stmt->nextRowset();
            $info = $stmt->fetchAll();
        }
        $stmt->closeCursor();
        if ($type == 'list') {
            return [
                'info' => (count($info) == 1) ? $info[0] : $info,
                'list' => $list
            ];
        } else {
            return (count($list) == 1 && $count == '') ? $list[0] : $list;
        }
    }

    /**
     * 计算小时前
     * @param $the_time
     * @return string
     * Author: 18042542@qq.com 2018/5/23
     */
    protected function time_tran( $the_time )
    {
        $dur = time() - $the_time;
        if ($dur < 60) {
            return $dur . '秒前';
        }
        if ($dur < 3600) {
            return floor($dur / 60) . '分钟前';
        }
        if ($dur < 86400) {
            return floor($dur / 3600) . '小时前';
        }
        if ($dur < 30 * 86400) {
            return floor($dur / 86400) . '天前';
        }
        return floor($dur / (30 * 86400)) . '月前';
    }

    /**
     *  更具秒 计算时长
     * @param $seconds
     * @return string
     * Author: 18042542@qq.com 2018/5/24
     */
    protected function changeTimeType( $seconds )
    {
        if ($seconds > 3600) {
            $hours = intval($seconds / 3600);
            $minutes = $seconds % 3600;
            $time = $hours . ":" . gmstrftime('%M:%S', $minutes);
        } else {
            $time = gmstrftime('%H:%M:%S', $seconds);
        }
        return $time;
    }

    /**
     * 日期计算年龄
     * @param $birthday
     * @return false|string
     * Author: 18042542@qq.com 2018/5/30
     */
    protected function birthday2Age( $birthday )
    {
        list($year, $month, $day) = explode("-", $birthday);
        $year_diff = date("Y") - $year;
        $month_diff = date("m") - $month;
        $day_diff = date("d") - $day;
        if ($year_diff && ($day_diff < 0 || $month_diff < 0)) {
            $year_diff--;
        }
        return $year_diff;
    }

    /**
     * 映射字段
     *
     * author gitxuzan
     * @param array $data
     * @param array $colmap
     * @return array
     */
    protected function colMapList( array $data, array $colmap = [] )
    {
        $list = [];
        if (count($data) && count($colmap)) {
            foreach ($data as $key => $value) {
                foreach ($colmap as $k => $v) {
                    if (is_array($v)) {
                        $list[$key][$k] = $v[$value[$k]];
                    }elseif(in_array($v, ['create_at', 'update_at'])){
                        $list[$key][$v] = $value[$v] ? date('Y-m-d H:i', $value[$v]) : '-';
                    }else{
                        $list[$key][$v] = is_null($value[$v])?_str:$value[$v];
                    }
                }
            }
        }
        return $list;
    }

    /**
     * 发送系统消息
     * @param $url
     * @return string
     * @Auther: 18042542@qq.com 2018/8/2
     */
    protected function getTimUrl( $url )
    {
        $this->config = $this->getDI()->getConfig();
        $appid = $this->config->tencent->im->SdkAppId;
        $api = new TLSSigAPI();
        $api->SetAppid($appid);
        $private = file_get_contents(APP_PATH . '/common/library/pem/private_key');
        $api->SetPrivateKey($private);
        $public = file_get_contents(APP_PATH . '/common/library/pem/public_key');
        $api->SetPublicKey($public);
        $random = [];
        for ($i = 0; $i < 4; $i++) {
            $random[] = rand(1000, 9999) . rand(1000, 9999);
        }

        $params = [
            'usersig' => $api->genSig($this->config->tencent->im->identifier),
            'identifier' => $this->config->tencent->im->identifier,
            'sdkappid' => strval($appid),
            'random' => join('', $random),
            'contenttype' => 'json'
        ];


        return 'https://console.tim.qq.com/v4/' . $url . '?' . http_build_query($params);
    }

    public function sendSysMsg( $user_id ,$msg = '',$desc = '' )
    {
        //发送系统消息、通知结果
        $url = $this->getTimUrl('openim/batchsendmsg');
        $params = [
            "SyncOtherMachine" => 2,//消息不同步至发送方
            "From_Account" => "100000",
            "MsgBody" => [                       //消息
                [
                    "MsgType" => "TIMCustomElem",
                    "MsgContent" => [
                        "Data" => "",
                        "Desc" => ""
                    ]
                ]
            ]
        ];
        //发送
        $params["To_Account"] =is_array($user_id) ?$user_id : [ $user_id ];
        $params['MsgBody'][0]['MsgContent']['Data'] = "$msg";
        $params['MsgBody'][0]['MsgContent']['Desc'] = "$desc";
        $params["MsgRandom"] = rand(1000000, 9999999);
        //用body json方式发送
        $res = functions::https($url, $params, 1);
        return $res;

    }




    /**
     * author gitxuzan
     * @return null|\PDO
     */
    public function getPdo()
    {
        if (!self::$pdo) {
            $dbConfig = $dbConfig = $this->di['config']->database;


            $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8', $dbConfig->host, $dbConfig->port, $dbConfig->dbname);
            $conn = new \PDO($dsn, $dbConfig->username, $dbConfig->password);
            $conn->setAttribute(\PDO::ATTR_STRINGIFY_FETCHES, false);
            $conn->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
            self::$pdo = $conn;
        }
        return self::$pdo;
    }

    public function getPageSizeList( $table, $page,$where = '',$pageSize = 15, $col = '*' )
    {
        $start = $this->getStart($page, $pageSize);
        $sql = "select $col from $table $where limit $start,$pageSize";

        $list = $this->read($sql);

        $totalsql = "select count(*) total from $table $where ";

        $total = $this->read($totalsql,[],1)['total'];

        $this->getParams($total, $pageSize, $list);
        return $this->data;

    }

    /**
     * 从库只读
     * author gitxuzan
     * @param $sql
     * @param array $binds
     * @param int $findOne
     * @return mixed
     * @throws \Exception
     */
    public function r_read($sql, $binds = [], $findOne = 0)
    {
        $config = $this->getDI()->get('config');

        if (property_exists($config, 'master_slave') && $config->master_slave == 'master') {
            // 主库
            $pdo_r = $this->getPdo();
        }else{
            // 从库
            $pdo_r = $this->getDI()->get('pdo_r');
        }

        $stmt = $this->_doPrepare($sql, $binds, $pdo_r);

        $errorInfo = $stmt->errorInfo();
        if (!$errorInfo[0]) {
            throw new \Exception('mysql error:' . $errorInfo[2]);
        } else {
            $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            return $findOne && isset($data[0]) ? $data[0] : $data;
        }
    }


    public function read( $sql, $binds = [], $findOne = 0 )
    {
        $pdo = $this->getPdo();
        $stmt = $this->_doPrepare($sql, $binds, $pdo);

        $errorInfo = $stmt->errorInfo();
        if (!$errorInfo[0]) {
            throw new \Exception('mysql error:' . $errorInfo[2]);
        } else {
            if ($findOne == 1) {
                $data = $stmt->fetch(\PDO::FETCH_ASSOC);
            } else {
                $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            }
            return $data;
        }
    }

    public function write($sql, $binds = [])
    {
        $pdo = $this->getPdo();
        $stmt = $this->_doPrepare($sql, $binds, $pdo);

        $errorInfo = $stmt->errorInfo();
        if (!$errorInfo[0]) {
            throw new \Exception('mysql error:' . $errorInfo[2]);
        }
    }

    protected function _doPrepare( $sql, $binds, $pdo )
    {
        $ps = $pdo->prepare($sql);
        if (isset($binds['i']) && !empty($binds['i'])) {
            foreach ($binds['i'] as $k => $v) {
                $vv = (int)$v;
                $ps->bindValue($k, $vv, \PDO::PARAM_INT);
            }
        }
        if (isset($binds['s']) && !empty($binds['s'])) {
            foreach ($binds['s'] as $k => $v) {
                $ps->bindValue($k, $v, \PDO::PARAM_STR);
            }
        }
        $ps->execute();

        return $ps;
    }

    public function getDatas( $info, $pageSize, $list )
    {
        return [
            'total' => intval($info['info']['total']),
            'pagesize' => $pageSize,
            'list' => $list,
            'last_page' => ceil(intval($info['info']['total']) / $pageSize)
        ];
    }

    public function getParams( $total, $pageSize, $list, $str = 'list' )
    {
        
        $this->data = [
            'total' => $total,
            'pagesize' => $pageSize,
            $str => $list,
            'last_page' => ceil($total / $pageSize)
        ];
        return $this;
    }

    public function mergeParams( array $arr = [])
    {
        return array_merge($this->data, $arr);
    }


    /**
     * redis 获取或设置redis
     * author gitxuzan
     * @param $key
     * @param $callback
     * @param int $lifetime 单位秒
     * @param int $redis_db 库名称
     * @return mixed|void
     */
    public function getCallback( $key, $callback, $lifetime = 0, $redis_db = 0 , $redis_name = 'redis')
    {
        $redis = $this->getDI()->get($redis_name);
        if($redis_db > 0){
            $redis->select($redis_db);
        }

        $value = $redis->get($key);
        if (empty($value)) {
            $value = call_user_func($callback);
            if (is_null($value)) {
                return;
            }
            $redis->set($key, $value);
            if ($lifetime) {
                $redis->expire($key,$lifetime);
            }
        }
        return $value;
    }



    public function getSaddCallback( $key, $callback, $lifetime = 0, $redis_db = 0 , $redis_name = 'redis')
    {
        $redis = $this->getDI()->get($redis_name);
        if($redis_db > 0){
            $redis->select($redis_db);
        }
        $arr = $redis->sMembers($key);

        if (empty($arr)) {
            $arr = call_user_func($callback);
            if (!$arr) {
                return [];
            }
            $redis->sAddArray($key, $arr);
            if ($lifetime) {
                $redis->expire($key,$lifetime);
            }
        }
        return $arr;
    }

    /**
     * author gitxuzan
     * @param $key
     * @param $callback
     * @param int $lifetime 单位秒
     * @param int $redis_db 库名称
     * @return mixed|void
     */
    public function getCallbackSerialize( $key, $callback, $lifetime = 0, $redis_db = 0 , $redis_name = 'redis')
    {
        $redis = $this->getDI()->get($redis_name);
        if($redis_db>0){
            $redis->select($redis_db);
        }

        $value = $redis->get($key);
        if (empty($value)) {
            $value = call_user_func($callback);
            if (is_null($value)) {
                return;
            }
            $value = serialize($value);
            $redis->set($key, $value);
            if ($lifetime) {
                $redis->expire($key,$lifetime);
            }
        }
        return unserialize($value);
    }

    public function getStart( $page , $pageSize = 10 )
    {
        $page = intval($page) ? : 1;
        $start = ($page - 1) * $pageSize;
        return $start;
    }


    public function getCdnUrl(  )
    {
        $config = $this->getDI()->get('config');
        $proBool = !!($config->environment == 'pro');
        $cdn_url = '';
        if($proBool){
            $redis_act = $this->getDI()->get('redis_act');
            $cdn_url = $redis_act->hGet(TaskRedis::HOST_URL_LIST,'host_cdn') ? :$this->getDI()->get('config')->cdn_url;
        }

        return $cdn_url;

    }

    public function isPro(  )
    {
        $config = $this->getDI()->get('config');
        return !!($config->environment == 'pro');
    }
}
