<?php

namespace Common\Models;

class Member extends Base
{
    public static $instance = null;

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function test(  )
    {
        $sql = "select * from t_sys_user";
        return $this->exec_proc_list($sql,'list');
    }

    public function insertDevice( $params )
    {
        extract($params);

        $sql = "CALL Proc_AgentInvited_Device_apply($agent_user_id,$user_id, '$fp', '$ip', '$lan' , $screen_width, $screen_height, '$vc_vendor', '$vc_renderer', '$ua', '$platform', '$os' , '$channel' , $is_regist,$package)";

        $res = $this->exec_proc($sql);

        return $res;
    }

    public function bindRegeistAgent( $user_id, $agent_user_id, $p_pack_id, $is_reg = 'reg', $amount = 0 )
    {

        $sql = "CALL Proc_AgentInvited_RelationLogs_apply('$is_reg', $user_id, $agent_user_id, '$p_pack_id', $amount)";


        $res = $this->exec_proc($sql);
        return $res;
    }


    public function agentAdd( $user_id, $agent_phone, $wechat_name, $content, $package_type )
    {
        $sql = "CALL Proc_Agent_apply($user_id, '$agent_phone', '$wechat_name', '$content', '$package_type')";

        $res = $this->exec_proc($sql);
        return $res;
    }


    public function getUserAgentInfo( $user_id )
    {
        $sql = "CALL Proc_AgentInvited_IncomeLogs_total($user_id)";
        $res = $this->exec_proc_r($sql);
        return $res;
    }


    public function getUserAgentInfoDetail( $page, $user_id, $startTime, $endTime, $pageSize = 10 )
    {
        $page = $page - 1 >= 0 ? $page - 1 : 0;
        $sql = "CALL Proc_AgentInvited_IncomeLogs_list($user_id,$startTime,$endTime,$page,$pageSize)";
        $res = $this->exec_proc_r($sql, 'list');

        return $this->getParams(intval($res['info']['total']), $pageSize, $res['list'])->mergeParams(['total_energy' => intval($res['info']['energy'])]);
    }

    public function agentExchange( $user_id, $energy, $is_gold_masonry )
    {
        $sql = "CALL Proc_AgentInvite_Withdrawals_apply($user_id,$is_gold_masonry, $energy)";
        return $this->exec_proc($sql);
    }

    public function getAgentConfig( $type )
    {
        $sql = "CALL Proc_Sys_AgentInvited_IncomeConfig_details($type)";
        return $this->exec_proc_r($sql);
    }

    public function agentAuto( $agent_id,$android_package = '2,7,8,10',$ios_package = '1,3,5,6,9',$sys_user_id = 1,$agent_type = 'add' )
    {
//        const ANDROID_PACKAGE_TYPE = [2 => 'Android', 7 => 'Android喵播', 8=>'Android富聊', 10 => 'Android全球版'];
//        const IOS_PACKAGE_TYPE = [1 => 'IOS', 6 => 'IOS喵播',3=>'IOS富聊', 5=> 'IOS主包',  9=> 'IOS全球版'];
        $sql = "CALL Proc_Sys_AgentInvite_apply('$agent_type', $agent_id, '$android_package', '$ios_package', $sys_user_id)";
        return $this->exec_proc($sql);
    }

    public function setCompanyInfo( $type, $content,$select = 0 )
    {
        $sql = "call Proc_Sys_company_apply($type, '$content',$select)";
        return $this->exec_proc($sql);

    }

}
