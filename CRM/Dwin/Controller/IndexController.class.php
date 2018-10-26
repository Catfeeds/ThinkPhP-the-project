<?php
/**
 * Created by PhpStorm.
 * User: hp
 * Date: 17-5-11
 * Time: 上午10:21
 */

namespace Dwin\Controller;

use Think\Controller;
// 首页
class IndexController extends CommonController
{

    /* ------------------菜单显示------------------- */
    /* 客户管理模块 */
    const CUS_MODULE        = 1;
    /* 项目管理模块 */
    const PROJECT_MODULE    = 60;
    /* 财务管理模块 */
    const FINANCE_MODULE    = 117;
    /* 客服管理模块 */
    const ONLINE_MODULE     = 107;
    /* 质控管理模块 */
    const SALE_MODULE       = 88;
    /* 行政管理模块 */
    const ADMIN_MODULE      = 141;
    /* 库房管理模块 */
    const STOCK_MODULE      = 178;
    /* 系统管理模块 */
    const SYSTEM_MODULE     = 132;
    /* 生产管理模块 */
    const PRODUCTION_MODULE = 200;
    /* 采购管理模块*/
    const PURCHASE_MODULE = [400,406];

    /* ------------------消息提醒------------------- */
    /* 审核项目进度提醒 */
    const PROJECT_CHECK = 76;
    /* 审核项目申请提醒 */
    const PROJECT_APPLY_CHECK = 73;
    /* 客服记录审核提醒 */
    const ONLINE_CHECK = 45;
    /* 售后记录审核提醒 */
    const SALE_CHECK = 39;
    /* 不满意回访记录提醒 */
    const CUS_CALLBACK = 116;
    /* ------------------返回状态------------------- */
    const TRUE_VALUE  = 2;
    const FALSE_VALUE = 1;

    /* ---------------数据库状态字段---------------- */
    const UN_CHECK_STATUS = '1';

    /*客户变更装哪台*/
    const EDIT_NAME = 2;
    const REMOVE_APPLY = 10;
    protected $rules;
    protected $staffId;
    protected $orderCheckRole;
    public function _initialize()
    {
        parent::_initialize();
        $this->rules   = explode(',', session('userRule'));
        $this->staffId = session('staffId');
        $this->orderCheckRole = "16";
    }
    public function index()
    {
        $map['crm_staff.id'] = array('EQ', (int) $this->staffId);
        $data = M('staff')
            ->field('crm_staff.name nickname,d.name deptname, GROUP_CONCAT(p.role_name) postname')
            ->where($map)
            ->join('LEFT JOIN `crm_dept` d ON d.id = crm_staff.deptid')
            ->join('LEFT JOIN `crm_auth_role` p ON FIND_IN_SET(crm_staff.id, p.staff_ids)')
            ->group('crm_staff.id')
            ->find();
        $this->assign('data', $data);
        $this->display();
    }
    public function home(){
        #展示模版
        /*$map['id'] = array('eq', $this->staffId);
        $staffData = M('staff')->where($map)->field('id, name, cus_child_id, prj_child_id, online_child_id, sale_child_id, order_child_id')->find();*/

        $this -> display();
    }

    /**
     * 渲染相关菜单 根据是否有权限
     * @todo 后续可能需要优化
     */
     protected function getRuleMsg($rules)
     {
         $msg['sys']            = in_array(276, $rules) ? self::TRUE_VALUE : in_array(self::SYSTEM_MODULE, $rules)     ? self::TRUE_VALUE : self::FALSE_VALUE;
         $msg['saleservice']    = in_array(276, $rules) ? self::TRUE_VALUE :in_array(self::SALE_MODULE, $rules)       ? self::TRUE_VALUE : self::FALSE_VALUE;
         $msg['online']         = in_array(276, $rules) ? self::TRUE_VALUE :in_array(self::ONLINE_MODULE, $rules)     ? self::TRUE_VALUE : self::FALSE_VALUE;
         $msg['finance']        = in_array(276, $rules) ? self::TRUE_VALUE :in_array(self::FINANCE_MODULE, $rules)    ? self::TRUE_VALUE : self::FALSE_VALUE;
         $msg['project']        = in_array(276, $rules) ? self::TRUE_VALUE :in_array(self::PROJECT_MODULE, $rules)    ? self::TRUE_VALUE : self::FALSE_VALUE;
         $msg['customer']       = in_array(276, $rules) ? self::TRUE_VALUE :in_array(self::CUS_MODULE,  $rules)       ? self::TRUE_VALUE : self::FALSE_VALUE;
         $msg['production']     = in_array(276, $rules) ? self::TRUE_VALUE :in_array(self::PRODUCTION_MODULE, $rules) ? self::TRUE_VALUE : self::FALSE_VALUE;//生产任务管理
         $msg['admin']          = in_array(276, $rules) ? self::TRUE_VALUE :in_array(self::ADMIN_MODULE, $rules)      ? self::TRUE_VALUE : self::FALSE_VALUE;//行政管理
         $msg['stock']          = in_array(276, $rules) ? self::TRUE_VALUE :in_array(self::STOCK_MODULE, $rules)      ? self::TRUE_VALUE : self::FALSE_VALUE;//库存管理
         $msg['proCheck']       = in_array(276, $rules) ? self::TRUE_VALUE :in_array(self::PROJECT_CHECK, $rules)     ? self::TRUE_VALUE : self::FALSE_VALUE;//审核项目进度权限
         $msg['proApply']       = in_array(276, $rules) ? self::TRUE_VALUE :in_array(self::PROJECT_APPLY_CHECK, $rules) ? self::TRUE_VALUE : self::FALSE_VALUE;//申请项目权限
         $msg['onlineMsgCheck'] = in_array(276, $rules) ? self::TRUE_VALUE :in_array(self::ONLINE_CHECK, $rules)      ? self::TRUE_VALUE : self::FALSE_VALUE;//客服记录审核权限
         $msg['saleMsgCheck']   = in_array(276, $rules) ? self::TRUE_VALUE :in_array(self::SALE_CHECK, $rules)        ? self::TRUE_VALUE : self::FALSE_VALUE;//售后记录审核权限
         $msg['callbackRes']    = in_array(276, $rules) ? self::TRUE_VALUE :in_array(self::CUS_CALLBACK, $rules)      ? self::TRUE_VALUE : self::FALSE_VALUE;//回访记录查看权限
         $msg['purchase']       = in_array(276, $rules) ? self::TRUE_VALUE :count(array_intersect(self::PURCHASE_MODULE, $rules)) > 0 ? self::TRUE_VALUE : self::FALSE_VALUE;//采购管理
         return $msg;
     }
     public function checkPostInfo()
    {
        if (IS_POST) {
            $msg = $this->getRuleMsg($this->rules);
            $this->ajaxReturn($msg);
        }
    }

    // 修改一些个人数据
    public function checkPwd()
    {
        $oldPwd = I('post.oldpwd');
        $map['id'] = array('EQ', session('staffId'));
        $rst = M('staff')->where($map)->find();
        $password = $rst['pwd'];
        $rst = password_verify($oldPwd, $password);
        if ($rst == false) {
            $this->ajaxReturn(self::FALSE_VALUE);
        } else {
            $this->ajaxReturn(self::TRUE_VALUE);
        }
    }
    public function changePwd()
    {
        $password = I('post.newpwd');

        // 密码加密存储
        $salt = base64_encode(mcrypt_create_iv(32, MCRYPT_DEV_URANDOM));
        /*$salt = base64_encode(random_bytes(32));*/ //php7可用
        // php5.5以上支持的密码加密
        $options = [
            'salt' => $salt,
            'cost' => 12,
        ];
        $hash = password_hash($password, PASSWORD_DEFAULT, $options);
        $data['pwd']  = $hash;
        $data['salt'] = $salt;

        $map['id'] = array('EQ', session('staffId'));
        $rst = M('staff')->where($map)->save($data);
        if ($rst !== false) {
            session(null);
            $this->ajaxReturn(self::TRUE_VALUE);
        } else {
            $this->ajaxReturn(self::FALSE_VALUE);
        }
    }
    public function editPhone()
    {
        if (IS_AJAX) {
            $posts = I('post.');
            $map['id'] = array('EQ', session('staffId'));
            $newPhone['phone'] = $posts['newphone'];
            $rst = M('staff')->where($map)->setField($newPhone);
            if ($rst !== false) {
                $this->ajaxReturn(self::TRUE_VALUE);
            } else {
                $this->ajaxReturn(self::FALSE_VALUE);
            }
        } else {
            $map['id'] = array('EQ', session('staffId'));
            $data = M('staff')->where($map)->field('name, phone')->find();
            $this->assign('data', $data);
            $this->display();
        }
    }
    public function feedBack()
    {
        if (IS_POST) {
            $posts = I('post.feedB');
            $data['title'] = inject_filter($posts[0]['value']);
            $data['content'] = inject_filter($posts[1]['value']);
            $data['addtime'] = time();
            $data['staff_id'] = (int)session('staffId');
            $fin = M('feedback')->create($data);
            $rst = M('feedback')->add($fin);
            if ($rst) {
                $this->ajaxReturn(self::TRUE_VALUE);
            } else {
                $this->ajaxReturn(self::FALSE_VALUE);
            }
        } else {
            $this->display();
        }
    }
/*------------------- 递归调用返回顶级部门信息---------------*/
    protected function findDept($rst)
    {
        $map['id'] = array('EQ', $rst['parent_id']);
        $rst = M('dept')->where($map)->find();
        if ($rst['parent_id'] <= 1) {
            switch ($rst['id']) {
                case 1 :
                    $msg = 1;break;
                case 2 :
                    $msg = 2;break;
                case 3 :
                    $msg = 3;break;
                case 4 :
                    $msg = 4;break;
                case 5 :
                    $msg = 5;break;
            }
            $this->ajaxReturn($msg);
        } else {
            self::findDept($rst);
        }
    }

    public function checkDept()
    {
        $deptId = (int)session('deptId');
        $map['id'] = array('EQ', $deptId);
        $rst = M('dept')->where($map)->find();
        if ($rst['parent_id'] <= 1) {
            switch ($rst['id']) {
                case 1 :
                    $msg = 1;break;
                case 2 :
                    $msg = 2;break;
                case 3 :
                    $msg = 3;break;
                case 4 :
                    $msg = 4;break;
                case 5 :
                    $msg = 5;break;
            }
            $this->ajaxReturn($msg);
        } else {
            $this->findDept($rst);
        }
    }


/*-------------------业务员获取待审的记录（本人负责客户）--------------*/
    //客户的ids -> getSaleMsg,getOnlineMsg获取未审核记录

    /**
     * 根据用户id,获取用户负责的客户id
     * @param int $staffId 用户id
     * @return string $cusIds 用户负责的客户ids
    */
    private function getCusIds($staffId)
    {
        // $cusIds 根据当前用户的staffId获取以，连接的客户id字符串
        $map['uid'] = array('EQ', $staffId);
        $cusData = M('customer')->where($map)->field('cid cus_id')->select();
        $cusIds = getPrjIds($cusData, 'cus_id');
        return $cusIds;
    }
    /**
     * 根据用户id,获取用户负责的客户的未审核售后记录数量
     * @param int $staffId 用户id
     * @param string $time 时间限制
     * @return int $saleCount 待审核售后记录数
     * @todo 后续需要量化$time,限制审核失效时间。
     */
    private function getSaleMsg($staffId, $time)
    {
        $cusIds = $this->getCusIds($staffId);// $cusIds 根据当前用户的staffId获取以，连接的客户id字符串
        // $filter 查询条件获取负责的客户的未审核记录数
        $filter['sstatus'] = array('EQ', '1');
        $filter['customer_id'] = array('IN', $cusIds);
        $filter['addtime'] = array('GT', $time);
        $saleCount = empty($cusIds) ? 0 : M('saleservice')->where($filter)->count();
        return $saleCount;
    }
    private function getSalesRepairMsg($staffId, $time)
    {
        $cusIds = $this->getCusIds($staffId);
        $subFilter['is_show'] = array('EQ', '0');
        $subFilter['is_ok']   = array('EQ', '2');
        $subFilter['_logic']  = "OR";
        $filter['_complex']   = $subFilter;
        $filter['yid'] = array('EQ', $staffId);
        $repairCount = empty($cusIds) ? 0 : M('salerecord')->where($filter)->count('sid');
        return $repairCount;
    }
    /**
     * 根据用户id,获取用户负责的客户的未审核售后记录数量
     * @param int $staffId 用户id
     * @param string $time 时间限制
     * @return int $onlineCount 待审核客服记录数
     * @todo 后续需要量化$time,限制审核失效时间。
     */
    private function getOnlineMsg($staffId, $time)
    {
        $cusIds = $this->getCusIds($staffId);// $cusIds 根据当前用户的staffId获取以，连接的客户id字符串

        $filter['austatus']    = array('EQ', self::UN_CHECK_STATUS);
        $filter['customer_id'] = array('IN', $cusIds);
        $filter['addtime']     = array('GT', $time);
        $onlineCount = empty($cusIds) ? 0 : M('onlineservice')->where($filter)->count();
        return $onlineCount;
    }

    /*-------------------------------研发未审核项目进度及可申请项目--------------------*/

    private function getUnPro($ids)
    {
        //get unChecked progress of project
        // 获取未审核的项目进度记录
        // time为提交时间，ids为需要查询的项目人员ids
        $filter['prjer_id'] = array('IN', $ids);
        $filter['audistatus'] = array('EQ', self::UN_CHECK_STATUS);
        $prjProgressCount = M('resprogress')->where($filter)->count('id');
        return $prjProgressCount;
    }

    private function getUNumPrj()
    {
        $map_prj['status']= array('EQ',  self::UN_CHECK_STATUS);
        $prjId = M('restype')->where($map_prj)->field('prjid')->select();
        $prjIds = getPrjIds($prjId, 'prjid');
        if ($prjIds != "") {
            $filter_res['proid'] = array('IN', $prjIds);
            $filter_res['auditstatus'] = array('EQ',  self::UN_CHECK_STATUS);
            $rst = M('respublic')->where($filter_res)->count();
        } else {
            $rst = 0;
        }

        return $rst;
    }

    /*------------------------待审核客户、项目-----------------------------*/
    private function getRoleCount($time, $modelName, $status)
    {
        // get customer or project uncount number distinct by user'role
        // time 为申请时间  modelName 区分customer/project $status审核状态
        $condi['addtime'] = array('GT', $time);
        $condi['auditstatus'] = array('EQ', $status);
	    $condi['auditorid'] = array('EQ', $this->staffId);
        $totalNum = M($modelName)->where($condi)->count();
        return $totalNum;
    }

    /*------------------------普通组织架构待审核内容函数-----------------------------*/
    /**
     * 根据当前登录用户的权限，返回各记录数
     * @param string $rule 当前用户的rules
     * @param int    $staffId 当前用户的id
     * @return array $data 各项记录数
     *
    */
    private function getMsgByRule($rules, $staffId, $time)
    {
        $staffAuth = $this->getRuleMsg($rules); // 返回对应权限信息 2 对应有权 1为无

        $data['prjUNum']     = ($staffAuth['proApply']       == self::TRUE_VALUE) ? $this->getUNumPrj() : null;
        $data['onlineCount'] = ($staffAuth['onlineMsgCheck'] == self::TRUE_VALUE) ? $this->getOnlineMsg($staffId, $time) : null;
        //$data['saleCount']   = ($staffAuth['saleMsgCheck']   == self::TRUE_VALUE) ? $this->getSaleMsg($staffId, $time) : null;
        $data['repairCount'] = ($staffAuth['saleMsgCheck']   == self::TRUE_VALUE) ? $this->getSalesRepairMsg($staffId, $time) : null;
        if ($staffAuth['proCheck'] == self::TRUE_VALUE) {
            $filter['id'] = array('EQ', $staffId);
            $prjCheckId = M('staff')->field('prj_child_id ids')->where($filter)->find();
            $data['prjProgressCount'] = !empty($prjCheckId) ? $this->getUnPro($prjCheckId['ids']) : 0;// 待审核项目进度数量
        }
        return $data;
    }

    /*------------------------订单、客户、项目待审核记录数-----------------------------*/
    /**
     * 根据当前登录用户的权限，返回各记录数
     * @param int    $staffId 当前用户的id
     * @param int   $staffRole 当前用户的审核权限
     * @return array $data 各项待审核记录数
     *
     */
    private function getCheckNum($staffRole, $time)
    {
        $condi['check_status'] = array('EQ', '3'); // 1 部门未审 2不合格 3 财务未审 4 合格
        // staffRole 1 客户 2 项目  4 订单
        switch ($staffRole) {
            case 1 :
                $data['cus'] = $this->getRoleCount($time, 'customer',  self::UN_CHECK_STATUS);
                break;
            case 2 :
                $data['prj'] = $this->getRoleCount($time, 'research',  self::UN_CHECK_STATUS);
                break;
            case 4 :
                $data['order'] = M('orderform')->where($condi)->count();
                break;
            case 3 :
                $data['prj'] = $this->getRoleCount($time, 'research',  self::UN_CHECK_STATUS);
                $data['cus'] = $this->getRoleCount($time, 'customer',  self::UN_CHECK_STATUS);
                break;
            case 5 :
                $data['cus'] = $this->getRoleCount($time, 'customer',  self::UN_CHECK_STATUS);
                $data['order'] = M('orderform')->where($condi)->count();
                break;
            case 6 :
                $data['prj'] = $this->getRoleCount($time, 'research',  self::UN_CHECK_STATUS);
                $data['order'] = M('orderform')->where($condi)->count();
                break;
            case 7 :
                $data['prj'] = $this->getRoleCount($time, 'research',  self::UN_CHECK_STATUS);
                $data['cus'] = $this->getRoleCount($time, 'customer',  self::UN_CHECK_STATUS);
                $data['order'] = M('orderform')->where($condi)->count();
                break;
            default :
                $data = null;
                break;
        }
        return $data;
    }
    private function getEditCusCount()
    {
        $filter['auth_flag'] = array('EQ', self::EDIT_NAME);
        $filter['auth_id']   = array('EQ', $this->staffId);
        return $rst = M('cuschangerecord')->where($filter)->count('id');
    }
    private function getRemoveCusCount()
    {
        $filter['auth_flag'] = array('EQ', self::REMOVE_APPLY);
        $filter['auth_id']   = array('EQ', $this->staffId);
        return $rst = M('cuschangerecord')->where($filter)->count('id');
    }
    public function getMsgCount()
    {
        # 获取当前用户审核日常进度的数量
        // 仅显示直属下属的进度情况
        # 获取未审核内容的数量
        // 按照职员审核权限显示  订单审核人  项目审核人  客户审核人   普通人
        /*----------------------------------------------------------------------*/
        /*-----------需要审核的日常记录：项目进度、客服记录、售后记录-----------*/
        /*-----------------审核方式：项目进度按照上下级关系审核-----------------*/
        /*------------------客服售后记录按照对应客户业务员审核------------------*/
        /*----------------------------------------------------------------------*/
        /*---------------------ps:未审核记录提醒暂时不显示----------------------*/
        /*----------------------------------------------------------------------*/
        if (IS_AJAX) {
            $staffRole = (int)session('roleId'); // 审核权限
            $time = - 0.1;

            $financeCheckStaffIds = $this->getRoleStaffIds($this->orderCheckRole);
            $checkMsg = $this->getCheckNum($staffRole, $time); // 返回需要审核的数目

            /*----------------------------普通组织架构----------------------------*/
            // 根据日常管理权限获得消息对应直属的消息数
            // 根据是否有权限查看对应记录，查询负责的职员id
            $data = $this->getMsgByRule($this->rules, $this->staffId, $time);// 项目进度、可申请项目、客服、售后记录
            $deptCondi['check_status']  = array('EQ',  self::UN_CHECK_STATUS);
            $deptCondi['dept_check_id'] = array('EQ', $this->staffId);
            $data['deptOrderCheck'] = M('orderform')->where($deptCondi)->count();

            $data['cusCount']    = $checkMsg['cus'];
            $data['prjCount']    = $checkMsg['prj'];
            $data['orderCount']  = $checkMsg['order'] + $data['deptOrderCheck'];
            $data['editCusCount'] = (int)$this->getEditCusCount();
            $data['removeCount'] = (int)$this->getRemoveCusCount();
            foreach( $data as $key => $val) {
                if(!$val) unset($data[$key]);
            }
            if (!in_array($this->staffId, explode(",", $financeCheckStaffIds))) {
                $data['orderCount'] = 0;
            }
            $this->ajaxReturn($data);
        }
    }

    public function getOrderMsg()
    {
        if (IS_POST) {
            $map['picid'] = array('EQ', $this->staffId);
            $map['check_status'] = array('EQ', "2");
            $map['is_del'] = array('EQ', 0);
            $data = M('orderform')->field('cus_name')->where($map)->select();
            $msg = count($data) == 0 ? 1 : 2;
            $this->ajaxReturn($msg);
        }
    }
}
