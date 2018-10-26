<?php
/**
 * Created by PhpStorm.
 * User: MaXu
 * Date: 17-5-16
 * Time: 上午11:11
 */
namespace Dwin\Controller;

use Dwin\Model\AuthRoleModel;
use Dwin\Model\IndustrialSeralScreenModel;
use Dwin\Model\MaterialModel;
use Dwin\Model\RepairpersonModel;
use Dwin\Model\RepertorylistModel;
use Dwin\Model\SalerecordchangeModel;
use Dwin\Model\SalerecordModel;
use Dwin\Model\StaffModel;
use Dwin\Model\StockAuditModel;
use Dwin\Model\StockInOtherApplyModel;
use Dwin\Model\StockOutModel;
use Think\Controller;


/**
 *售后相关
 */
class SaleServiceController extends CommonController
{
    const SALE_ROLE = "1,2,34,35,21,39";
    const CUS_ROLE = "4,6,7,9,11,15,17,18,19,22,26,27,29,33";
    const RESEARCH_ROLE = "14,20,25,30,31,32,56,57,58,62,70";
    const REPAIR_ROLE = "36,5,14,20,25,28,30,31,32";
    const UN_CHANGE_STATUS = 3;
    const SUCCESS_STATUS = 2;
    const FAIL_STATUS = 1;

    // 更改状态1收货2维修3发货4完结5入库6审核时间7修改维修人8修改数量9修改维修品状态10维修费确认11驳回
    const CHANGE_STATUS_1 = "1";
    const CHANGE_STATUS_2 = "2";
    const CHANGE_STATUS_3 = "3";
    const CHANGE_STATUS_4 = "4";
    const CHANGE_STATUS_5 = "5";
    const CHANGE_STATUS_6 = "6";
    const CHANGE_STATUS_7 = "7";
    const CHANGE_STATUS_8 = "8";
    const CHANGE_STATUS_9 = "9";
    const CHANGE_STATUS_10 = "10";
    const CHANGE_STATUS_11 = "11";

    const AUDIT_ROLE = [43];  // 出入库审核员角色id 目前只能是物流线线长审核

    protected $staffId = "";
    
    protected $roleId = 36;         //维修专员组role_id
    
    protected $auditRule = 41;      //审核权限节点

    protected $saleAdd = 94;        //售后添加权限节点
    
    protected $market = 4;          //市场总监role_id
    
    protected $sales  = 6;           //销售总监role_id
    
    protected $cservice = 7;        //客服总监role_id

    protected $development = 5;     //研发总监role_id

    protected $assignFlag;
    public function _initialize()
    {
        parent::_initialize();
        $this->staffId = empty($this->staffId) ? (int)session('staffId') : $this->staffId;
        $this->assignFlag = [1,2,3];
    }  
    /**
     * 33 售后记录录入 showCustomer showSaleServiceHisList queryCustomer addServiceOk
    */
    // 1 获取客户信息并显示

    public function showCustomer()
    {
        if (IS_POST) {
            $model = M('customer');
            $posts = I('post.');
            if ($posts['cusName'] != "") {
                if (stripos($posts['cusName'],'www') !== false) {
                    $map['website'] = array('EQ', strtolower(inject_filter($posts['cusName'])));
                    $data = $model->where($map)
                        ->join('LEFT JOIN `crm_industry` ind ON ind.id = crm_customer.ctype')
                        ->field('crm_customer.cid,crm_customer.cname,crm_customer.addtime,ind.name indusname,uid,
                                    (SELECT count(id) FROM crm_saleservice AS ss WHERE ss.customer_id = crm_customer.cid) as counts')
                        ->select();
                    foreach ($data as &$value) {
                        if ($value['uid'] != null) {
                            $condition['id'] = array('EQ', $value['uid']);
                            $uname = M('staff')->where($condition)->field('name')->find();
                            $value['uname'] = $uname['name'];
                        } else {
                            $value['uname'] = "";
                        }
                        $value['addtime'] = date('Y-m-d H:i:s', $value['addtime']);
                    }
                } elseif (is_numeric($posts['cusName']) == true) {
                    $map['cphonenumber'] = array('EQ', strtolower(inject_filter($posts['cusName'])));
                    $data = $model->where($map)
                        ->join('LEFT JOIN `crm_industry` ind ON ind.id = crm_customer.ctype')
                        ->field('crm_customer.cid,crm_customer.cname,crm_customer.addtime,ind.name indusname,uid,
                                    (SELECT count(id) FROM crm_saleservice AS ss WHERE ss.customer_id = crm_customer.cid) as counts')
                        ->select();
                    foreach ($data as &$value) {
                        if ($value['uid'] != null) {
                            $condition['id'] = array('EQ', $value['uid']);
                            $uname = M('staff')->where($condition)->field('name')->find();
                            $value['uname'] = $uname['name'];
                        } else {
                            $value['uname'] = "";
                        }
                        $value['addtime'] = date('Y-m-d H:i:s', $value['addtime']);
                    }
                } else {
                    $num = mb_strlen($posts['cusName'], 'utf8');
                    if ($num <= 1) {
                        $this->ajaxReturn(false);die;
                    }
                   $index_str = $this->getCustomerInfo($posts['cusName']);
                    if ($index_str == null) {
                        $this->ajaxReturn(false);die;
                    }
                    $map['cid'] = array('IN', $index_str);
                    $data = M('customer')->where($map)
                        ->join('LEFT JOIN `crm_industry` ind ON ind.id = crm_customer.ctype')
                        ->field('crm_customer.cid,crm_customer.cname,crm_customer.addtime,ind.name indusname,uid,
                                    (SELECT count(id) FROM crm_saleservice AS ss WHERE ss.customer_id = crm_customer.cid) as counts')
                        ->select();
                    foreach ($data as &$value) {
                        if ($value['uid'] != null) {
                            $condition['id'] = array('EQ', $value['uid']);
                            $uname = M('staff')->where($condition)->field('name')->find();
                            $value['uname'] = $uname['name'];
                        } else {
                            $value['uname'] = "";
                        }
                        $value['addtime'] = date('Y-m-d H:i:s', $value['addtime']);
                    }
                    $c->close();

                }
                if ($data === false || $data == []) {
                    $this->ajaxReturn(false);
                    } else {
                    $this->ajaxReturn($data);
                }
            } else {
                $this->ajaxReturn(false);
            }
        } else {
            $this->display();
        }
    }
    // 2 点击某客户查看以往售后记录，并可以添加
//    public function showSaleServiceHisList()
//    {
//        $cid = inject_id_filter(I('get.cid'));
//        $model = M('saleservice');
//        $where['customer_id'] = array("EQ", $cid);
//        $where['crm_saleservice.addtime'] = array('GT', $this->timeLimit);
//        $data = $model
//            ->where($where)
//            ->join('LEFT JOIN crm_staff AS c ON crm_saleservice.pid = c.id')
//            ->join('LEFT JOIN crm_customer AS d ON crm_saleservice.customer_id = d.cid')
//            ->field('crm_saleservice.*,c.name AS pname, d.cname AS dname')
//            ->select();
//        $this->assign(array(
//            'data' => $data,
//            'cid'  => $cid,
//            ));
//        $this->display();
//    }
    // 3 处理添加数据的方法
//    public function addServiceOk()
//    {
//        if(IS_POST) {
//            $post = I('post.');
//            $post['pro_description'] = nl2br(($post['pro_description']));
//            $post['pro_solve'] = nl2br(($post['pro_solve']));
//            $post['addtime'] = time();
//            $post['pid'] = session('staffId');
//            $model = M('saleservice');
//            $fin = $model->create($post);
//            $rst = $model->add($fin);
//            if ($rst) {
//                $this->ajaxReturn(2);
//            } else {
//                $this->ajaxReturn(1);
//            }
//        }
//    }
    // 查询信息
    public function queryCustomer()
    {
        $this->display('showCustomer');
    }
    /**
     * 34 售后记录查看 showSaleServiceList
     * @todo 权限修改后重写
    * */
    //获取权限下所有售后服务记录
//    public function showSaleServiceList()
//    {
//        $model = M('saleservice');
//        $staffIds = $this->getStaffIds(session('staffId'), 'sale_child_id', "");
//        $staffIds = !empty($staffIds) ? $staffIds . "," . session('staffId') : session('staffId');
//
//        $where['crm_saleservice.pid'] = array('IN', $staffIds);
//
//        $where['crm_saleservice.addtime'] = array('GT', $this->timeLimit);
//        $data = $model
//            ->join('LEFT JOIN crm_staff AS s ON crm_saleservice.pid = s.id')
//            ->join('LEFT JOIN crm_customer AS c ON crm_saleservice.customer_id = c.cid')
//            ->join('LEFT JOIN `crm_industry` ind ON ind.id = c.ctype')
//            ->join('LEFT JOIN crm_staff AS d ON c.uid = d.id')
//            ->field('crm_saleservice.*, s.name AS sname,c.cname,ind.name indusname,uid,d.name AS dname')
//            ->where($where)
//            ->select();
//        $this->assign('data', $data);
//        $this->display();
//    }
    //同时拥有审核和售后添加权限
    //业务权限also
    public function checkDoubleAudit()
    {
        $posts = I('post.');
        $map['sid'] = $posts['sid'];
        $result = M('salerecord')->field('is_show,yid,is_ok')->where($map)->find();
        if(($result['yid'] == session('staffId')) && ($result['is_show'] == 0) && ($result['is_ok'] == 2)){
            $this->ajaxReturn(1);
        }elseif((($result['yid'] == session('staffId'))&& ($result['is_show'] != 0) && ($result['is_ok'] == 2))){
            $this->ajaxReturn(3);
        }elseif(($result['yid'] == session('staffId')) && ($result['is_show'] == 0) && ($result['is_ok'] != 2)){
            $this->ajaxReturn(2);
        }else{
            $this->ajaxReturn(4);
        }
    }
    //检测是否可以删除
    public function checkDelete()
    {
        $posts = I('post.');
        $map['sid'] = $posts['sid'];
        $result = M('salerecord')->field('is_show,yid')->where($map)->find();
        if(($result['is_show'] == session('staffId')) && ($result['is_show'] == 3)){
            $this->ajaxReturn(1);
        }else{
            $this->ajaxReturn(2);
        }
    }
    //维修记录显示
    public function showSaleRepairing()
    {
        $saleRecordModel = new SalerecordModel();
        $map['id'] = array('eq', $this->staffId);
        $staffData = M('staff')->where($map)->field('rule_ids')->find();
        //判断是否有审核权限
        $audit = in_array($this->auditRule, explode(",", $staffData['rule_ids']));
        //判断是否有售后添加权限
        $sale = in_array($this->saleAdd, explode(",", $staffData['rule_ids']));

        if (IS_AJAX) {
            $this->posts   = I('post.');
            $draw          = $this->posts['draw'];
            $this->sqlCondition = $this->getSqlCondition($this->posts);
            $this->field = "crm_salerecord.sid,crm_salerecord.sale_number,crm_salerecord.cusname,crm_salerecord.salename,from_unixtime(c.start_date) start_t,
                    e.name as reperson_question,crm_salerecord.is_over, crm_salerecord.is_repairorder, from_unixtime(crm_salerecord.over_time) over_t,crm_salerecord.sale_commissioner_name,
                    i.name as is_ok,crm_salerecord.courier_number,crm_salerecord.is_audit,crm_salerecord.is_show";

            //过滤条件 orderType 个人记录 0        下属记录 1
            // 是否完结流程 orderOver  未完结 0     完结 1
            $orderType = $this->posts['prj_order_type'];
            $orderOver = $this->posts['prj_order_over'];

            if ($audit && $sale) {
                $staffIds  = $this->getStaffIds((string)$this->staffId, 'cus_child_id', "");
                $staffIds  = !empty($staffIds) ? $staffIds . "," . $this->staffId : (string)$this->staffId;
                $staffIds1 = $this->getStaffIds((string)$this->staffId, 'sale_child_id', "");
                $staffIds1 = !empty($staffIds1) ? $staffIds1 . "," . $this->staffId : (string)$this->staffId;
                //搜索
                $searchFieldComplex['yid']               = array('IN', $staffIds);
                $searchFieldComplex['sale_commissioner'] = array('IN', $staffIds1);
                $searchFieldComplex['_logic']            = "or";
                $searchField['_string'] = "1 = 1";
                $searchField['_complex'] = $searchFieldComplex;
                $count = $saleRecordModel->where($searchField)->count();
            } elseif ($audit) {
                $staffIds = $this->getStaffIds((string)$this->staffId, 'cus_child_id', "");
                switch ($orderType) {
                    case 0 :
                        $searchField['yid'] = array('IN',(string)$this->staffId);
                        break;
                    case 1 :
                        $searchField['yid'] = array('IN',$staffIds);
                        break;
                    default :
                        $searchField['yid'] = array ('IN', (string)$this->staffId);
                }
                switch ($orderOver) {
                    case 0 :
                        $searchField['is_over'] = array('EQ', 0);
                        break;
                    case 1 :
                        $searchField['is_over'] = array('EQ', 1);
                        break;
                    default :
                        $searchField['is_over'] = array('EQ', 0);
                }
                $count = $saleRecordModel->where($searchField)->count();

            } else {
                switch ($orderType) {
                    case 0 :
                        $searchField['_string'] = "1 = 1";//全部记录
                        break;
                    case 1 :
                        //待检测记录
                        $searchField = array(
                            'crm_salerecord.is_ok'   => array('EQ', "1"),
                            'crm_salerecord.is_over' => array('EQ', "0")
                        );
                        break;
                    case 2 :
                        //待收费确认记录
                        $searchField = array(
                            'crm_salerecord.is_ok'   => array('EQ', "2"),
                            'crm_salerecord.is_over' => array('EQ', "0"),
                            'is_show'                => array('EQ', "1"),
                        );
                        break;
                    case 3 :
                        //待维修记录
                        $searchField = array(
                            'crm_salerecord.is_ok'   => array('EQ', "3"),
                            'crm_salerecord.is_over' => array('EQ', "0"),
                            'is_show'                => array('EQ', "1"),
                        );
                        break;
                    default :
                        $searchField['_string'] = "1 = 1";
                }
                $count = $saleRecordModel->where($searchField)->count();
            }
            //搜索
            if(!empty($this->sqlCondition['search'])){
                if(strlen($this->sqlCondition['search']) < 2){
                    $this->ajaxReturn(false);
                }else{
                    $searchField1['cusname|salename|courier_number|sale_commissioner_name|sale_number'] = array('like',"%".$this->sqlCondition['search']."%");
                    $searchField['_complex'] = $searchField1;
                }
            }
            $recordsFiltered = $saleRecordModel->where($searchField)->count();
            $result = $saleRecordModel->getSaleRecord($searchField, $this->field,$this->sqlCondition['start'], $this->sqlCondition['length'], $this->sqlCondition['order'],'crm_salerecord.sid');
            if (count($result) != 0) {
                foreach($result as &$val) {
                    $val['DT_RowId'] = $val['sid'];
                }
            }
            $this->output = $this->getDataTableOut($draw, $count, $recordsFiltered, $result);
            $this->ajaxReturn($this->output);
        } else {
            $this->assign([
                "saleType" => SalerecordModel::$saleTypeMap,
            ]);
            if ($audit && $sale) {
                $this->display('doubleshowSaleRepairing');
            } elseif ($audit) {
                $this->display('cusshowSaleRepairing');
            } else {
                $this->display();
            }
        }
    }
    //业务维修记录展示
    public function cusshowdetailSaleRepairing()
    {
        $saleRecordModel = new SalerecordModel();
        $sid = inject_id_filter(I('get.sid'));
        $map['sid'] = $sid;
        //基本信息
        $this->field = "crm_salerecord.sid,crm_salerecord.yid,crm_salerecord.reback_address,crm_salerecord.cusname,
                   crm_salerecord.sale_commissioner,crm_salerecord.note,crm_salerecord.sale_commissioner_name,crm_salerecord.courier_number,crm_salerecord.repair_date,
                   r.sale_way";
        $saleRecordData = $saleRecordModel->getCusSaleBasicInfo($map,$this->field);



        //维修单信息
        $repairProductMap['pid'] = array('EQ', $sid);
        $this->field = "product_category, product_name, num, customer_question,barcode_date, b.name sale_type";
        $repairProductInfo = M('repairgoodsinfo')
                        ->field($this->field)
                        ->join('LEFT JOIN `crm_repairgoodsinfo_saleway` AS b ON b.id = sale_way')
                        ->where($repairProductMap)->select();

        $result  = M('repairgoodsinfo')->field('product_name')->where($repairProductMap)->group('product_name')->select();
        $result1 = $saleRecordModel->getCusSaleInfo($map,'r.rpid,r.rpid,r.pid,IFNULL(piece_wage,0) as money,r.product_name,r.fault_info,r.reperson_name,
                    r.re_num,r.start_date,r.re_status,b.name as re_mode,r.piece_wage,c.name as reperson_question,r.meter_piece,r.mode_info,r.situation,a.customer_question');

        $this->assign(array(
            'saleRecordBasicData' => $saleRecordData,
            'repairProductInfo' => $repairProductInfo,
            'result'    => $result,
            'result1'   => $result1
        ));
        //费用总计 维修费用+人工费
        $money1 = M('salerecord')
            ->where($map)
            ->join(' LEFT JOIN `crm_repairperson` AS r  ON crm_salerecord.sid = r.pid ')
            ->sum('piece_wage');
        //人工费
        $rgmoney = M('salerecord')->field('rgmoney')->where($map)->find();
        $rgmoney = $rgmoney['rgmoney'];
        $money = $money1 + $rgmoney;
        $this->assign('money1',$money1);
        $this->assign('rgmoney',$rgmoney);
        $this->assign('money',$money);
        //判断售后方式，发货还是入库
        if($saleRecordData['sale_way'] == 0){
            $result3 = D('salerecord')->getCusSaleSendgoodsInfo($map,'r.id,r.pid,r.product_name,r.track_number,r.send_date,r.bactch,r.send_num');
                $flag = 1;
                $this->assign('result3',$result3);
                $this->assign('flag',$flag);
        }else{
            $result4 = D('salerecord')->getCusSaleSendgoodsInfo($map,'r.id,r.pid,r.product_name,r.track_number,r.send_date,r.bactch,r.send_num');
                $this->assign('result4',$result4);
        }
        $this->display();
    }
    //添加维修记录
    public function addSaleRepairing()
    {
        $productModel = new MaterialModel();
        $authModel    = new AuthRoleModel();
        if(IS_POST){
            $saleRecordModel       = new SalerecordModel();
            $saleRecordChangeModel = new SalerecordchangeModel();
            /**
             * post数据：
             * data1 :售后单基本数据，下标0到5为客户维修单基本信息
             *        下标6之后的为收到的维修货物数据
             * datas : 售后单对应的维修分配情况数据：产品型号 数量 问题 维修员等信息
             * aaa   : 业务员的信息
             * todo: aaa下标需要修改 N多数据不知道什么鬼，写代码能不能想好逻辑再写。
             * cusname: 客户信息
             * fnNum : data1中有几组产品数据
             * anumber :datas 中有几组维修分配数据
             * money : 维修金额
             * gnumber :发货批次
             * flag : 0 添加基本信息 1 添加维修分配信息
            */
            $this->posts = I('post.');
            /**
             * todo:根据页面请求的产品类型 动态返回产品型号，该逻辑未使用，原因：前端数据加载未做
             * 返回产品型号*/
            if ($this->posts['method'] == "getProd") {
                $productCateFilter['platform_id'] = array('EQ', $this->posts['cate_id']);
                $this->field = "product_id, platform_id, product_name";
                $this->output = $productModel->where($productCateFilter)->field($this->field)->select();
                $this->ajaxReturn($this->output);
            }
            //添加维修记录
            if(1 == $this->posts['flag']){
                $data   = array_column($this->posts['datas'], 'value', 'name');
                $data1  = array_column($this->posts['data1'], 'value', 'name');
                $userId = session('staffId');
                $map123['id'] = array('EQ', (int)$userId);
                $totalRepairMoney = 0;
                //插入售后记录基本信息
                $array = array(
                    'sale_number'      => $data1['sale_number'],
                    'courier_number'   => $data1['courier_number'],
                    'repair_date'      => time(),
                    'is_repairorder'   => $data1['is_repairorder'],
                    'cusname'          => $this->posts['cusname'],
                    'sale_commissioner'=> $this->staffId,
                    'sale_commissioner_name' => session('nickname'),
                    'reback_address'   => $data1['reback_address'],
                    'is_ok'            => '1',
                    'is_show'          => '0',
                    'note'             => $data['note'],
                    'rgmoney'          => $data['rgmoney'],
                    'status'           => '2',
                    'sum_fee'          => $data['rgmoney'],
                    'change_status_time'=> time(),
                );
                /* 有客户负责人，添加数据中加入客户负责人信息*/
                if(!empty($this->posts['aaa'])){
                    $ywid['id'] = $this->posts['aaa'];
                    $res = $saleRecordModel->findSingleInfo('crm_staff','name',$ywid);
                    $array['yid'] = $this->posts['aaa'];
                    $array['salename'] = $res['name'];
                }
                M()->startTrans();
                /* 添加基本信息 */
                $basicData = $saleRecordModel->create($array);
                $res = $saleRecordModel->add($basicData);
                //插入收货时间到记录表

                $remrepair_date = $saleRecordChangeModel->changeSaleStatus($res, self::CHANGE_STATUS_1);
                if (($res !== false) && ($remrepair_date !== false)) {
                    //插入维修品信息
                    $pid = $res;
                    $num = $this->posts['fnNum'];
                    for ($i = 0; $i < $num; $i++) {
                        $where[$i]['id'] = array('eq', $data1['product_category' . $i]);
                        $info[$i] = $saleRecordModel->findSingleInfo('crm_screen_category','name',$where[$i]);
                        $where1[$i]['product_id'] = array('eq', $data1['product_name' . $i]);
                        $info1[$i] = $saleRecordModel->findSingleInfo('crm_material','product_name',$where1[$i]);
                        $productdata[$i] = array(
                            'pid'                 => $pid,
                            'product_category_id' => $data1['product_category' . $i],
                            'product_category'    => $info[$i]['name'],
                            'product_name'        => $info1[$i]['product_name'],
                            'product_id'          => $data1['product_name' . $i],
                            'num'                 => $data1['num' . $i],
                            'barcode_date'        => $data1['barcode_date' . $i],
                            'customer_question'   => $data1['customer_question' . $i],
                            'sale_way'            => $data1['sale_mode' . $i],
                        );
                    }
                    $res2 = $saleRecordModel->addSaleRecordInfo('crm_repairgoodsinfo', $productdata);
                    if($res2){
                        //插入维修产品分配信息
                        $repairPersonAssign = array();
                        for ($i=0; $i < $this->posts['anumber']; $i++) {
                            $where[$i]['id'] = $data['reperson_name' . $i];
                            $re[$i] = $saleRecordModel->findSingleInfo('crm_staff','name',$where[$i]);
                            $repairPersonAssign[$i] = array(
                                'pid'               => $pid,
                                'product_name'      => $data['a_name' . $i],
                                'reperson_name'     => $re[$i]['name'],
                                'reperson_id'       => $data['reperson_name' . $i],
                                'start_date'        => time(),
                                're_num'            => $data['re_num' . $i],
                                're_status'         => $data['re_status' . $i],
                                're_mode'           => $data['re_mode' . $i],
                                'situation'         => $data['situation' . $i],
                                'mode_info'         => $data['mode_info' . $i],
                                'piece_wage'        => $data['piece_wage' . $i],
                                'meter_piece'       => $data['meter_piece' . $i],
                                'fault_info'        => $data['fault_info' . $i],
                                'reperson_question' => $data['reperson_question' . $i],
                            );
                            $totalRepairMoney+= $repairPersonAssign[$i]['piece_wage'];
                        }
                        $saleRecordMap['sid'] = ['EQ', $pid];
                        $updRepairMoney['sum_fee'] = $totalRepairMoney + $data['rgmoney'];
                        $saleRecordModel->where($saleRecordMap)->setField($updRepairMoney);
                        $res3 = $saleRecordModel->addSaleRecordInfo('crm_repairperson', $repairPersonAssign);
                        $map123['pid'] = $pid;
                        //取出刚插入的rpid
                        $rpid = $saleRecordModel->findMoreInfo('crm_repairperson', 'rpid', $map123);
                        //循环添加更新纪录表
                        $changeData = array();
                        for ($j = 0; $j < $this->posts['anumber']; $j++) {
                            $changeData[$j] = array(
                                'saleid'          => $pid,
                                'repersonorderid' => $rpid[$j]['rpid'],
                                'change_status_time' => time(),
                                'change_status'   => self::CHANGE_STATUS_2,
                                'changemanname'   => session('nickname'),
                                'changemanid'     => session('staffId'),
                            );
                        }
                        $result = $saleRecordModel->changeSaleMoreStatus($changeData);
                        //更改时间记录表并更改主表状态 is_ok = 2
                        $map1['sid'] = array('EQ', $pid);
                        $tiaojian1['change_status_time'] = time();
                        $tiaojian1['is_ok'] = ($this->posts['money'] == 1) ? "2" : "3";//产生费用需要审核
                        $result1 = $saleRecordModel->updateSaleRecord('crm_salerecord',$tiaojian1,$map1);

                        if ($res3) {
                            //判断是否填写发货信息，不写也行
                            if(1 == $this->posts['gnumber']){
                                //插入发货信息且只有一批次
                                $sendData = array();
                                for ($i = 0; $i < $this->posts['fnNum']; $i++) {
                                    $sendData[$i] = array(
                                        'pid'           => $pid,
                                        'bactch'        => $data['bactch0'],
                                        'track_number'  => $data['track_number0'],
                                        'send_date'     => time(),
                                        'product_name'  => $data['reproduct_name' . $i],
                                        'send_num'      => $data['send_num' . $i],
                                    );
                                }

                                $res4 = $saleRecordModel->addSaleRecordInfo('crm_sendgoods', $sendData);
                                /**
                                 * 插入信息到打印表
                                 * 售后服务单打印信息
                                 * @todo:产品维修信息存到打印表中
                                 */
                                $printCondition['pid'] = array('EQ', $pid);
                                //获取条码日期和客户反馈
                                $this->field = "product_name, num, customer_question, barcode_date, customer_question";
                                $basicData   = $saleRecordModel->findMoreInfo('crm_repairgoodsinfo', $this->field, $printCondition);
                                //获取发货数量
                                $this->field  = "product_name,track_number,from_unixtime(send_date) send_date,bactch, send_num, send_man";
                                $deliveryData = $saleRecordModel->findMoreInfo('crm_sendgoods', $this->field, $printCondition);
                                $salecount    = count($basicData);
                                $printAddData = array();
                                /* 退回产品信息打印 */
                                for ($i = 0; $i < $salecount; $i++) {
                                    $printAddData[$i] = array(
                                        'barcode_date'      => $basicData[$i]['barcode_date'],
                                        'customer_question' => $basicData[$i]['customer_question'],
                                        'send_num'          => $deliveryData[$i]['send_num'],
                                        'sid'               => $pid,
                                        'product_name'      => $basicData[$i]['product_name'],
                                        're_mode'           => "",
                                        'fault_info'        => "",
                                        'reperson_question' => "",
                                        'mode_info'         => "",
                                    );
                                }
                                for ($i = 0; $i < count($printAddData); $i++) {
                                    for ($j = 0; $j < count($repairPersonAssign); $j++) {
                                        if ($repairPersonAssign[$j]['product_name'] == $printAddData[$i]['product_name']) {
                                            $printAddData[$i]['re_mode'] = empty($printAddData[$i]['re_mode'])
                                                ? $repairPersonAssign[$j]['re_mode']
                                                : $printAddData[$i]['re_mode'] . "," . $repairPersonAssign[$j]['re_mode'];
                                            $printAddData[$i]['fault_info'] = empty($printAddData[$i]['fault_info'])
                                                ? "维修人：" . $repairPersonAssign[$j]['reperson_name'] . ",数量：". $repairPersonAssign[$j]['re_num'] . ",备注信息" . $i . ":" . $repairPersonAssign[$j]['fault_info']
                                                : $printAddData[$i]['fault_info'] . ";维修人：" . $repairPersonAssign[$j]['reperson_name'] . ",数量：".$repairPersonAssign[$j]['re_num'] .",备注信息" . $i . "：" . $repairPersonAssign[$j]['fault_info'];
                                            $printAddData[$i]['reperson_question'] = empty($printAddData[$i]['reperson_question'])
                                                ? $repairPersonAssign[$j]['reperson_question']
                                                : $printAddData[$i]['reperson_question'] . "," . $repairPersonAssign[$j]['reperson_question'];
                                            $printAddData[$i]['mode_info'] = empty($printAddData[$i]['mode_info'])
                                                ? "维修人：" . $repairPersonAssign[$j]['reperson_name'] . ",数量：". $repairPersonAssign[$j]['re_num'] ."故障现象备注" . $i . "：" . $repairPersonAssign[$j]['mode_info']
                                                : $printAddData[$i]['mode_info'] . "；维修人：" . $repairPersonAssign[$j]['reperson_name'] . ",数量：". $repairPersonAssign[$j]['re_num'] ."，故障现象备注 ". $i . "：" . $repairPersonAssign[$j]['mode_info'];

                                        }
                                    }
                                }

                                    $result_1        = $saleRecordModel->addSaleRecordInfo('crm_saleprint', $printAddData);
                                    $recordStatusAdd = $saleRecordModel->changeSaleStatus($pid, self::CHANGE_STATUS_3);
                                    $map33['sid']    = array('eq', $pid);
                                    $tiaojian1['is_ok'] = '4';
                                    $tiaojian1['change_status_time'] = time();
                                    $is_ok = $saleRecordModel->updateSaleRecord('crm_salerecord', $tiaojian1, $map33);

                                    if ($res4) {
                                            M()->commit();
                                            $this->ajaxReturn(1);
                                        } else {
                                            M()->rollback();
                                            $this->ajaxReturn(2);
                                        }
                                } else {
                                    M()->commit();
                                    $this->ajaxReturn(1);
                                }
                            } else {
                                M()->rollback();
                                $this->ajaxReturn(2);
                            }
                        } else {
                            M()->rollback();
                            $this->ajaxReturn(2);
                        }
                } else {
                    M()->rollback();
                    $this->ajaxReturn(2);
                }
            } elseif(0 == $this->posts['flag']) {
                //添加基本信息
                $data1 = array_column($this->posts['data1'], 'value', 'name');

                //插入售后记录基本信息
                $array = array(
                    'sale_number'      => $data1['sale_number'],
                    'courier_number'   => $data1['courier_number'],
                    'repair_date'      => time(),
                    'is_repairorder'   => $data1['is_repairorder'],
                    'cusname'          => $this->posts['cusname'],
                    'sale_commissioner'=> $this->staffId,
                    'sale_commissioner_name'=> session('nickname'),
                    'reback_address'   => $data1['reback_address'],
                    'is_ok'            => '1',
                    'is_show'          => '0',
                    'status'           => '1',
                    'change_status_time'=> time(),
                );
                if(!empty($this->posts['aaa'])){
                    $responsibleFilter['id'] = array('eq', $this->posts['aaa']);
                    $cusResponsibleName = $saleRecordModel->findSingleInfo('crm_staff','name',$responsibleFilter);
                    $array['yid'] = $this->posts['aaa'];
                    $array['salename'] = $cusResponsibleName['name'];
                }

                M()->startTrans();
                $res = $saleRecordModel->addSaleRecordBasic('crm_salerecord', $array);
                //插入收货时间到记录表并更改主表字段为已收货 is_ok = 1 ;

                $remrepair_date = $saleRecordModel->changeSaleStatus($res, self::CHANGE_STATUS_1);
                if($res && $remrepair_date){
                //插入维修品信息
                    $pid = $res;
                    $productdata = array();
                    for ($i = 0; $i < $this->posts['fnNum']; $i++) {
                        $where[$i]['id'] = array('eq', $data1['product_category' . $i]);
                        $info[$i] = $saleRecordModel->findSingleInfo('crm_screen_category', 'name', $where[$i]);

                        $where1[$i]['product_id'] = array('eq', $data1['product_name' . $i]);
                        $info1[$i] = $saleRecordModel->findSingleInfo('crm_material', 'product_name', $where1[$i]);
                            $productdata[$i] = array(
                                'pid'               => $pid,
                                'product_category_id' => $data1['product_category' . $i],
                                'product_category'  => $info[$i]['name'],
                                'product_id'        => $data1['product_name' . $i],
                                'product_name'      => $info1[$i]['product_name'],
                                'num'               => $data1['num' . $i],
                                'barcode_date'      => $data1['barcode_date' . $i],
                                'customer_question' => $data1['customer_question' . $i],
                                'sale_way'          => $data1['sale_mode' . $i],
                            );
                        }
                        $res2 = $saleRecordModel->addSaleRecordInfo('crm_repairgoodsinfo', $productdata);
                        if ($res2) {
                            M()->commit();
                            $this->ajaxReturn(1);
                        } else {
                            M()->rollback();
                            $this->ajaxReturn(2);
                        }
                } else {
                    M()->rollback();
                    $this->ajaxReturn(2);
                }
            } else {
                //添加编辑的插入维修人
                $data = array_column($this->posts['data'], 'value', 'name');
                $pid = $data['sid'];
                $repersondata = array();
                for ($i = 0; $i < $this->posts['anumber']; $i++) {
                    $where[$i]['id'] = $data['reperson_name' . $i];
                    $re[$i] = $saleRecordModel->findSingleInfo('crm_staff', 'name', $where[$i]);
                    $repersondata[$i] = array(
                                'pid'               => $pid,
                                'product_name'      => $data['a_name' . $i],
                                'reperson_name'     => $re[$i]['name'],
                                'reperson_id'       => $data['reperson_name' . $i],
                                'start_date'        => time(),
                                're_num'            => $data['re_num' . $i],
                                're_status'         => $data['re_status' . $i],
                                'situation'         => $data['situation' . $i],
                                're_mode'           => $data['re_mode' . $i],
                                'mode_info'         => $data['mode_info' . $i],
                                'piece_wage'        => $data['piece_wage' . $i],
                                'meter_piece'       => $data['meter_piece' . $i],
                                'fault_info'        => $data['fault_info' . $i],
                                'reperson_question' => $data['reperson_question' . $i],
                            );
                }
                M()->startTrans();
                $res3 = $saleRecordModel->addSaleRecordInfo('crm_repairperson', $repersondata);
                $mapRPId['pid'] = array('eq', $pid);
                //取出刚插入的rpid
                $rpid = $saleRecordModel->findMoreInfo('crm_repairperson','rpid', $mapRPId);
                //循环添加更新纪录表
                $changedata = array();
                for ($j=0; $j < $this->posts['anumber']; $j++) {
                    $changedata[$j] = array(
                                'saleid'          => $pid,
                                'repersonorderid' => $rpid[$j]['rpid'],
                                'change_status_time' => time(),
                                'change_status'   => '2',
                                'changemanname'   => session('nickname'),
                                'changemanid'     => session('staffId'),
                            );
                }
                $result = $saleRecordModel->changeSaleMoreStatus($changedata);
                $map1['sid'] = array('eq', $pid);
                $tiaojian1['rgmoney'] = $data['rgmoney'];
                $tiaojian1['note']    = $data['note'];
                $tiaojian1['status']  = '2';
                $tiaojian1['change_status_time'] = time();
                $tiaojian1['is_ok']   = ($this->posts['money'] == 1) ? "2" : "3";

                $result1 = $saleRecordModel->updateSaleRecord('crm_salerecord',$tiaojian1,$map1);
                if($res3){
                    M()->commit();
                    $this->ajaxReturn(1);
                }else{
                    M()->rollback();
                    $this->ajaxReturn(2);
                }
            }
        }else{

            $model = M();
            $model->startTrans();
            //执行SQL语句 锁掉salerecord_id表
            //表的WRITE锁定，阻塞其他所有mysql查询进程
            $order_id_rst = $model->table('crm_salerecord_id')->lock(true)->field('id, salerecord_id')->find();// 获取唯一salerecord_id
            //执行更新操作
            $setData['salerecord_id'] = $order_id_rst['salerecord_id'] + 1;
            $lockFilter['id'] = array('EQ', $order_id_rst['id']);
            $order_rst = $model->table('crm_salerecord_id')->where($lockFilter)->setField($setData);
            //当前请求的所有写操作做完后，执行解锁sql语句
            $model->table('crm_salerecord_id')->lock(false);
            $model->commit();

            //系统自动生成的售后单号(大写SH.日期.id+1)
            $saleRecordNumber = "SH" ."-". date('Ymd') . $setData['salerecord_id'];
            $this->assign('orderNumber', $saleRecordNumber);

            //获取产品分类
            $map1['level'] = array('EQ', '1');
            $productCate = D('salerecord')->findMoreInfo('crm_screen_category', 'id,name', $map1);
            $this->assign('proCate', $productCate);

            //获取产品型号
            $product = M('material')
            ->field('product_id,platform_id,product_name')->where("1 = 1")->select();
            $this->assign('productCate', $product);

            //获取所有业务名称
            $roleFilter['role_id'] = array('IN', self::CUS_ROLE);
            $this->field = "staff_ids";

            $roleStaffArray = $authModel->getRoleList($this->field, $roleFilter, 'role_id', 0, 1000);
            $staffIds = getPrjIds($roleStaffArray, "staff_ids");
            $staffArray['loginstatus'] = array('NEQ', "1");
            $staffArray['id'] = array("IN",$staffIds);
            $res = D('salerecord')->findMoreInfo('crm_staff','id,name',$staffArray);
            $this->assign('res',$res);

            //获取维修人 维修专员
            $roleFilter['role_id'] = array('IN', self::REPAIR_ROLE);
            $roleStaffArray = $authModel->getRoleList($this->field, $roleFilter, 'role_id', 0, 1000);
            $staffIds = getPrjIds($roleStaffArray, "staff_ids");
            $staffArray['id'] = array("IN",$staffIds);
            $result3 = M('staff')->field('id, name')->where($staffArray)->order('id desc')->select();
            $this->assign('result3',$result3);
            //状态
            $rst = M('repairperson_restatus')->select();
            $this->assign('rst', $rst);
            //维修方式
            $rst1 = M('repairperson_remode')->select();
            $this->assign('rst1', $rst1);
            //故障类型
            $rst2 = M('repairperson_repersonquestion')->select();
            $this->assign('rst2', $rst2);
            //售后方式
            $shmethod = M('repairgoodsinfo_saleway')->field('id,name')->select();
            $this->assign('shmethod',$shmethod);
            $this->display();
        }
    }
    //客户对应业务员联动
    public function secondMove()
    {
        $posts = I('post.');
        $map['cid'] = $posts['id'];
        if(empty($map['cid'])){
            $this->ajaxReturn(1);
        }else{
            $result = M('customer')
            ->join(' LEFT JOIN `crm_staff` c ON crm_customer.uid = c.id ')
            ->where($map)
            ->field('c.id,c.name')
            ->select();
            if(empty($result[0]['id']) || empty($result[0]['name'])){
                $this->ajaxReturn(1);
            }else{
                $this->ajaxReturn($result);
            }
        }
    }
    //读取客户地址
    public function readAddr()
    {
        $posts = I('post.');
        $map['cid'] = $posts['id'];
        if(empty($map['cid'])){
            $this->ajaxReturn(1);
        }else{
            $result = M('customer')
            ->join(' LEFT JOIN `crm_staff` c ON crm_customer.uid = c.id ')
            ->where($map)
            ->field('addr')
            ->select();
            $this->ajaxReturn($result);
        }
    }
    //产品类别联动型号@todo 没用 industrial拆分为stock material两个表
    public function productMove()
    {
        $posts = I('post.');
        $map['id'] = $posts['id'];
        $map['level'] = '1';
        $result = M('ScreenCategory')
        ->join(' LEFT JOIN `crm_material` c ON c.platform_id = crm_screen_category.id ')
        ->where($map)
        ->field('c.product_id,c.product_name')
        ->find();
        if($result){
            $this->ajaxReturn($result);
        }else{
            $this->ajaxReturn(1);
        }
    }
    //完结流程
    public function isOver()
    {
        if(IS_POST){
            $posts   = I('post.');
            $is_over = I('post.isover');
            $sid     = I('post.sid');
            $map['sid']     = $sid;
            $a['is_over']   =  $is_over;
            $a['over_time'] = date('Y-m-d H:i:s',time());
                $result = M('salerecord')->where($map)->save($a);
                if($result){
                    $this->ajaxReturn(1);
                }
        }
    }
    //客户模糊搜索
    public function addSelect()
    {
        $posts = I('get.');
        $results = [];
        $cusName = $posts['q'];
        require_once('sphinxapi.php');
        $c = new \SphinxClient();
        $c->setServer('localhost', 9312);
        $c->setMatchMode(SPH_MATCH_ALL);
        $num = mb_strlen($cusName, 'utf8');
        if ($num <= 1) {
            $msg['status'] = 5;
            $this->ajaxReturn($msg);die;
        }
        $cusFilterString = "河北,石家庄,张家口,承德,唐山,秦皇岛,廊坊,保定,沧州,衡水,邢台,邯郸,山西,太原,大同,朔州,忻州,阳泉,晋中,吕梁,长治,临汾,晋城,运城,内蒙古自治区,呼和浩特,呼伦贝尔,通辽,赤峰,巴彦淖尔,乌兰察布,包头,鄂尔多斯,乌海,黑龙江,哈尔滨,黑河,伊春,齐齐哈尔,鹤岗,佳木斯,双鸭山,绥化,大庆,七台河,鸡西,牡丹江,吉林,长春,白城,松原,吉林,四平,辽源,白山,通化,辽宁,沈阳,铁岭,阜新,抚顺,朝阳,本溪,辽阳,鞍山,盘锦,锦州,葫芦岛,营口,丹东,大连,江苏,南京,连云港,徐州,宿迁,淮安,盐城,泰州,扬州,镇江,南通,常州,无锡,苏州,浙江,杭州,湖州,嘉兴,绍兴,舟山,宁波,金华,衢州,台州,丽水,温州,安徽,合肥,淮北,亳州,宿州,蚌埠,阜阳,淮南,滁州,六安,马鞍山,巢湖,芜湖,宣城,铜陵,池州,安庆,黄山,福建,福州,宁德,南平,三明,莆田,龙岩,泉州,漳州,厦门,江西,南昌,九江,景德镇,上饶,鹰潭,抚州,新余,宜春,萍乡,吉安,赣州,山东,济南,德州,滨州,东营,烟台,威海,淄博,潍坊,聊城,泰安,莱芜,青岛,日照,济宁,菏泽,临沂,枣庄, 河南,郑州,安阳,鹤壁,濮阳,新乡,焦作,三门峡,开封,洛阳,商丘,许昌,平顶山,周口,漯河,南阳,驻马店,信阳,湖北,武汉,十堰,襄樊,随州,荆门,孝感,宜昌,黄冈,鄂州,荆州,黄石,咸宁,湖南,长沙,岳阳,张家界,常德,益阳,湘潭,株洲,娄底,怀化,邵阳,衡阳,永州,郴州,广东,广州,韶关,梅州,河源,清远,潮州,揭阳,汕头,肇庆,惠州,佛山,东莞,云浮,汕尾,江门,中山,深圳,珠海,阳江,茂名,湛江广西壮族自治区,南宁,桂林,河池,贺州,柳州,百色,来宾,梧州,贵港,玉林,崇左,钦州,防城港,北海,海南,海口,三亚,三沙,儋州,四川,成都,广元,巴中,绵阳,德阳,达州,南充,遂宁,广安,资阳,眉山,雅安,内江,乐山,自贡,泸州,宜宾,攀枝花,贵州,贵阳,遵义,六盘水,安顺,云南,昆明,昭通,丽江,曲靖,保山,玉溪,临沧,普洱,西藏自治区,拉萨,昌都,日喀则,林芝,陕西,西安,榆林,延安,铜川,渭南,宝鸡,咸阳,商洛,汉中,安康,甘肃,兰州,嘉峪关,酒泉,张掖,金昌,武威,白银,庆阳,平凉,定西,天水,陇南,青海,西宁,海东,宁夏回族自治区,银川,石嘴山,吴忠,中卫,固原,新疆维吾尔自治区,乌鲁木齐,克拉玛依,吐鲁番";
        $cusFilter = array(
            '科技发展有限公司','科技有限公司','技术有限公司','实业有限公司','有限责任公司','电子有限公司','股份有限公司','有限公司','公司','研究所','研究院','市','省',
            '北京', '上海', '天津', '重庆',
            '(',')','（','）','select','insert','update','delete','and','or','where','join','*','=','union','into','load_file','outfile','/','\''

        );
        $cusKey = str_replace(array_merge(explode(",", $cusFilterString), $cusFilter), "", strtolower($cusName));
        if (mb_strlen($cusKey) <= 1) {
            $results[] = array("id"=>"0","text"=> $posts['q']);
            $this->ajaxReturn(array('results' => $results));
        }

        $data1 = $c->Query($cusKey, "dwin,delta");
        $index = array_keys($data1['matches']);

        $index_str = implode(',', $index);
        if ($index_str == null) {
            $results[] = array("id"=>"0","text"=> $posts['q']);
            $this->ajaxReturn(array('results' => $results));
        }
        $map['crm_customer.cid'] = array('IN', $index_str);
        /** @var string = $sql 查询语句获得录入客户类似名称的信息：负责人、上级公司等*/
        //$data['cname'] = array('like',"%$cusName%");
        $result1 = M('customer')->where($map)
            ->join(' LEFT JOIN `crm_staff` c ON crm_customer.uid = c.id ')
            ->field('`crm_customer`.cid as id,`crm_customer`.cname as text')
            ->select();
        $c->close();
            if($result1){
                foreach ($result1 as $key => $value) {
                    $results[] = $value;
                }
                $results[] = array("id"=>"0","text"=> $posts['q']);
                $this->ajaxReturn(array('results' => $results));
            }else{
                $results[] = array("id"=>"0","text"=> $posts['q']);
                $this->ajaxReturn(array('results' => $results));
            }
    }
    //业务审核不通过回执
    public function addAuditReback()
    {
        $sid = I('get.orderId');
        $this->assign('sid',$sid);
        $this->display();
    }
    //去修改主表字段，审核状态
    public function checkIsOk()
    {
        $posts = I('post.');

        $map['sid'] = I('post.sid');
        $result = M('salerecord')->where($map)->field('yid,is_ok,is_show,sale_slip,sale_type')->find();

        if($posts['flag'] == 5){
            if(!isset($posts['sale_slip']) || empty($posts['sale_type']) || !isset($posts['sale_type']) || empty($posts['sale_slip'])){
                $msg['status'] = 2;
                $this->ajaxReturn($msg);
            }
        }

        $saleType = isset($posts['sale_type']) ? $posts['sale_type'] : $result['sale_type'];
        $saleSlip = isset($posts['sale_slip']) ? $posts['sale_slip'] : $result['sale_slip'];
        //是本人的单子才能操作
        if($result['yid'] == session('staffId')) {
            //审核无费用
            if($posts['money'] == 1){
                if ($posts['flag'] == 5) {
                    //有效
                    $isok['is_audit'] = 0;
                    $isok['is_show']  = 1;
                    $isok['sale_type'] = $saleType;
                    $isok['sale_slip'] = $saleSlip;
                    $result1 = M('salerecord')->where($map)->save($isok);
                    if ($result1 !== false) {
                        $map['changemanname'] = session('nickname');
                        $map['changemanid']   = session('staffId');
                        $map['change_status'] = '6';
                        $map['audit_flag']    = '0';
                        $map['change_status_time'] = time();
                        $map['saleid'] = I('post.sid');
                        $result2 = M('salerecordchange')->add($map);
                        $msg['status'] = 1;
                    } else {
                        $msg['status'] = 2;
                    }
                } else if ($posts['flag'] == 6 && $posts['sale_way'] == 0) {
                    //无效
                    $isok['reback_content'] = trim(I('post.content'));
                    $isok['is_show']  = 2;
                    $isok['is_audit'] = 1;
                    $isok['sale_type'] = $saleType;
                    $isok['sale_slip'] = $saleSlip;
                    $result1 = M('salerecord')->where($map)->save($isok);
                    if ($result1 !== false) {
                        $map['saleid']        = I('post.sid');
                        $map['changemanname'] = session('nickname');
                        $map['changemanid']   = session('staffId');
                        $map['change_status'] = '6';
                        $map['audit_flag']    = '1';
                        $map['change_status_time'] = time();
                        $result2 = M('salerecordchange')->add($map);
                        $map['change_status'] = self::CHANGE_STATUS_11;
                        $resRecordUnSatusRst = M('salerecordchange')->add($map);
                        $msg['status'] = 1;
                    } else {
                        $msg['status'] = 2;
                    }
                } else if ($posts['flag'] == 6 && $posts['sale_way'] == 5) {
                    //修改了反馈类型为入库
                    $isok['reback_content'] = trim(I('post.content'));
                    $isok['is_show']  = 1;
                    $isok['is_audit'] = 1;
                    $isok['sale_type'] = $saleType;
                    $isok['sale_slip'] = $saleSlip;
                    $result1     = M('salerecord')->where($map)->save($isok);
                    $map1['pid'] = I('post.sid');
                    $tiaojian['sale_way'] = $posts['sale_way'];
                    $result3     = M('repairgoodsinfo')->where($map1)->save($tiaojian);
                    //审核时间插入记录
                    $map['saleid']        = I('post.sid');
                    $map['changemanname'] = session('nickname');
                    $map['changemanid']   = session('staffId');
                    $map['change_status'] = '6';
                    $map['audit_flag']    = '2';
                    $map['change_status_time'] = time();
                    $result4 = M('salerecordchange')->add($map);
                    $map['change_status'] = self::CHANGE_STATUS_11;
                    $resRecordUnSatusRst = M('salerecordchange')->add($map);
                    if ($result1 !== false && $result3 !== false) {
                        $msg['status'] = 1;
                    } else {
                        $msg['status'] = 2;
                    }
                }
            }else{
                //审核产生费用
                if ($posts['flag'] == 5) {
                    //有效
                    $isok['is_audit'] = 0;
                    $isok['is_ok']    = 3;
                    $isok['is_show']  = 1;
                    $isok['sale_type'] = $saleType;
                    $isok['sale_slip'] = $saleSlip;
                    $result1 = M('salerecord')->where($map)->save($isok);
                    if ($result1 !== false) {
                        $map['changemanname']      = session('nickname');
                        $map['changemanid']        = session('staffId');
                        $map['change_status']      = '6';
                        $map['audit_flag']         = '0';
                        $map['change_status_time'] = time();
                        $map['saleid'] = I('post.sid');
                        $result2 = M('salerecordchange')->add($map);
                        // todo: 为了记录上业务确认收费,较之前增加了1次记录
                        $map['change_status'] = self::CHANGE_STATUS_10;
                        $res = M('salerecordchange')->add($map);
                        $msg['status'] = 1;
                    } else {
                        $msg['status'] = 2;
                    }
                } else if ($posts['flag'] == 6 && $posts['sale_way'] == 0) {
                    //无效
                    $isok['reback_content'] = trim(I('post.content'));
                    $isok['is_ok']    = 6;
                    $isok['is_show']  = 2;
                    $isok['is_audit'] = 1;
                    $isok['sale_type'] = $saleType;
                    $isok['sale_slip'] = $saleSlip;
                    $result1 = M('salerecord')->where($map)->save($isok);
                    if ($result1 !== false) {
                        $map['saleid'] = I('post.sid');
                        $map['changemanname'] = session('nickname');
                        $map['changemanid']   = session('staffId');
                        $map['change_status'] = '6';
                        $map['audit_flag']    = '1';
                        $map['change_status_time'] = time();
                        $result2 = M('salerecordchange')->add($map);
                        $map['change_status'] = self::CHANGE_STATUS_11;
                        $resRecordUnSatusRst = M('salerecordchange')->add($map);
                        $msg['status'] = 1;
                    } else {
                        $msg['status'] = 2;
                    }
                } else if ($posts['flag'] == 6 && $posts['sale_way'] == 5) {
                    //修改了反馈类型为入库
                    $isok['reback_content'] = trim(I('post.content'));
                    $isok['is_ok']    = 3;
                    $isok['is_audit'] = 1;
                    $isok['is_show']  = 1;
                    $isok['sale_type'] = $saleType;
                    $isok['sale_slip'] = $saleSlip;
                    $result1     = M('salerecord')->where($map)->save($isok);
                    $map1['pid'] = I('post.sid');
                    $tiaojian['sale_way'] = $posts['sale_way'];
                    $result3 = M('repairgoodsinfo')->where($map1)->save($tiaojian);
                    //审核时间插入记录
                    $map['saleid']        = I('post.sid');
                    $map['changemanname'] = session('nickname');
                    $map['changemanid']   = session('staffId');
                    $map['change_status'] = '6';
                    $map['audit_flag']    = '2';
                    $map['change_status_time'] = time();
                    $result4 = M('salerecordchange')->add($map);
                    $map['change_status'] = self::CHANGE_STATUS_11;
                    $resRecordUnSatusRst = M('salerecordchange')->add($map);
                    if ($result1 !== false && $result3 !== false && $result4 !== false) {
                        $msg['status'] = 1;
                    } else {
                        $msg['status'] = 2;
                    }
                }
            }
        }
        $this->ajaxReturn($msg);
    }
    //第一步审核不通过，is_show = 2
    public function checkIsNo()
    {
        $posts = I('post.');
        $map['sid'] = I('post.sid');
        $data['is_show'] = 2;
        $result = M('salerecord')->where($map)->save($data);
        $changeData['changemanname']      = session('nickname');
        $changeData['changemanid']        = session('staffId');
        $changeData['change_status']      = self::CHANGE_STATUS_11;
        $changeData['audit_flag']         = '0';
        $changeData['change_status_time'] = time();
        $changeData['saleid'] = I('post.sid');
        $result2 = M('salerecordchange')->add($changeData);
        if($result){
            $this->ajaxReturn(1);
        }else{
            $this->ajaxReturn(2);
        }

    }
    //编辑维修记录
    protected function getPrintAddData($pid, $repairPersonAssign) {
        $saleRecordModel = new SalerecordModel();
        $printCondition['pid'] = array('EQ', $pid);
        //获取条码日期和客户反馈
        $this->field = "product_name, num, customer_question, barcode_date, customer_question";
        $basicData   = $saleRecordModel->findMoreInfo('crm_repairgoodsinfo', $this->field, $printCondition);
        //获取发货数量
        $this->field  = "product_name,track_number,from_unixtime(send_date) send_date,bactch, send_num, send_man";
        $deliveryData = $saleRecordModel->findMoreInfo('crm_sendgoods', $this->field, $printCondition);
        $saleCount    = count($basicData);
        for ($i = 0; $i < $saleCount; $i++) {
            $printAddData[$i] = array(
                'barcode_date'      => $basicData[$i]['barcode_date'],
                'customer_question' => $basicData[$i]['customer_question'],
                'send_num'          => $deliveryData[$i]['send_num'],
                'sid'               => (int)$pid,
                'product_name'      => $basicData[$i]['product_name'],
                're_mode'           => "",
                'fault_info'        => "",
                'reperson_question' => "",
                'mode_info'         => "",
            );
        }
        for ($i = 0; $i < count($printAddData); $i++) {
            for ($j = 0; $j < count($repairPersonAssign); $j++) {
                if ($repairPersonAssign[$j]['product_name'] == $printAddData[$i]['product_name']) {
                    $printAddData[$i]['re_mode'] = empty($printAddData[$i]['re_mode'])
                        ? $repairPersonAssign[$j]['re_mode']
                        : $printAddData[$i]['re_mode'] . "," . $repairPersonAssign[$j]['re_mode'];

                    $printAddData[$i]['fault_info'] = empty($printAddData[$i]['fault_info'])
                        ? "维修人：" . $repairPersonAssign[$j]['reperson_name'] . ",数量：". $repairPersonAssign[$j]['re_num'] . ",备注信息" . $i . ":" . $repairPersonAssign[$j]['fault_info']
                        : $printAddData[$i]['fault_info'] . ";维修人：" . $repairPersonAssign[$j]['reperson_name'] . ",数量：".$repairPersonAssign[$j]['re_num'] .",备注信息" . $i . "：" . $repairPersonAssign[$j]['fault_info'];

                    $printAddData[$i]['reperson_question'] = empty($printAddData[$i]['reperson_question'])
                        ? $repairPersonAssign[$j]['reperson_question']
                        : $printAddData[$i]['reperson_question'] . "," . $repairPersonAssign[$j]['reperson_question'];

                    $printAddData[$i]['mode_info'] = empty($printAddData[$i]['mode_info'])
                        ? "维修人：" . $repairPersonAssign[$j]['reperson_name'] . ",数量：". $repairPersonAssign[$j]['re_num'] ."故障现象备注" . $i . "：" . $repairPersonAssign[$j]['mode_info']
                        : $printAddData[$i]['mode_info'] . "；维修人：" . $repairPersonAssign[$j]['reperson_name'] . ",数量：". $repairPersonAssign[$j]['re_num'] ."，故障现象备注 ". $i . "：" . $repairPersonAssign[$j]['mode_info'];
                }
            }
        }
        return $printAddData;
    }

    private function getDeletePrimaryId($repairPersonUpdateData, $repairPersonOldData, $primaryKey)
    {
        $deleteId = array();
        $repairPersonOldDataCount = count($repairPersonOldData);
        $repairPersonUpdateNumber = count($repairPersonUpdateData);
        if ($repairPersonOldDataCount > 0) {
            $oldIdArray = array();
            for ($i = 0; $i < $repairPersonOldDataCount; $i++) {
                $oldIdArray[] = $repairPersonOldData[$i][$primaryKey];
            }
            if ($repairPersonUpdateNumber > 0) {
                if ($repairPersonOldDataCount > $repairPersonUpdateNumber) {
                    $newIdArray = array();
                    for ($j = 0; $j < $repairPersonUpdateNumber; $j++) {
                        $newIdArray[] = $repairPersonUpdateData[$j][$primaryKey];
                    }
                    $deleteId = array_diff($oldIdArray, $newIdArray);
                }
            } else {
                $deleteId = $oldIdArray;
            }
        }
        return $deleteId;
    }


    private function getRepairPersonChangeData($repairPersonOldData, $repairPersonUpdateData)
    {
        //获取老数据比对是否修改，将修改的部分写到记录表中
        $repairPersonOldDataCount    = count($repairPersonOldData);
        $repairPersonUpdateDataCount = count($repairPersonUpdateData);
        $changeData = array();
        for ($i = 0; $i < $repairPersonOldDataCount; $i++) {
            //老数据 ==> 新数据
            for ($j = 0; $j < $repairPersonUpdateDataCount; $j ++) {
                if ($repairPersonOldData[$i]['rpid'] == $repairPersonUpdateData[$j]['rpid']) {
                    //对比是否修改维修人
                    if ($repairPersonOldData[$i]['reperson_id'] != $repairPersonUpdateData[$j]['reperson_id']) {
                        $changeData_1[$j]['repersonorderid']     = $repairPersonUpdateData[$j]['rpid'];
                        $changeData_1[$j]['oldreperson_message'] = $repairPersonOldData[$i]['reperson_id'];
                        $changeData_1[$j]['newreperson_message'] = $repairPersonUpdateData[$j]['reperson_id'];
                        $changeData_1[$j]['change_status']       = self::CHANGE_STATUS_7;
                        $changeData[] = $changeData_1[$j];
                    }
                    //对比是否修改数量
                    if($repairPersonOldData[$i]['re_num'] != $repairPersonUpdateData[$j]['re_num']){
                        $changeData_2[$j]['repersonorderid'] = $repairPersonUpdateData[$j]['rpid'];
                        $changeData_2[$j]['oldnum_message']  = $repairPersonOldData[$i]['re_num'];
                        $changeData_2[$j]['newnum_message']  = $repairPersonUpdateData[$j]['re_num'];
                        $changeData_2[$j]['change_status']   = self::CHANGE_STATUS_8;
                        $changeData[] = $changeData_2[$j];
                    }
                    //对比是否修改状态
                    if($repairPersonOldData[$i]['re_status'] != $repairPersonUpdateData[$j]['re_status']){
                        //组装插入的数据
                        $changeData_3[$j]['repersonorderid']     = $repairPersonUpdateData[$j]['rpid'];
                        $changeData_3[$j]['oldrestatus_message'] = $repairPersonOldData[$i]['re_status'];
                        $changeData_3[$j]['newrestatus_message'] = $repairPersonUpdateData[$j]['re_status'];
                        $changeData_3[$j]['change_status']       = self::CHANGE_STATUS_9;
                        $changeData[] = $changeData_3[$j];
                    }
                }
            }
        }
        $changeData = array_values($changeData);
        foreach ($changeData as &$list) {
            $list['saleid'] = $repairPersonOldData[0]['pid'];
            $list['change_status_time'] = time();
            $list['changemanid']   = session('staffId');
            $list['changemanname'] = session('nickname');
        }
        return $changeData;
    }

    private function getDeliveryUpdateData($data)
    {
        $deliveryUpdateData = array();
        for ($i = 0; $i < count($data); $i++) {
            for ($j = 0; $j < count($data[$i]['data']); $j++) {
            if (isset($data[$i]['data'][$j]['id'])) {
                    $deliveryUpdateData[] = array(
                        'pid'          => (int)$data[$i]['data'][$j]['pid'],
                        'product_name' => $data[$i]['data'][$j]['product_name'],
                        'send_num'     => $data[$i]['data'][$j]['send_num'],
                        'track_number' => $data[$i]['track_number'],
                        'bactch'       => $data[$i]['bactch'],
                        'id'           => (int)$data[$i]['data'][$j]['id'],
                        'send_date'    => (int)$data[$i]['send_date'],
                        'send_manid'   => session('staffId'),
                        'send_man'     => session('nickname'),
                    );
                }
            }
        }
        return $deliveryUpdateData = array_values($deliveryUpdateData);
    }
    private function getDeliveryAddData($data)
    {
        $deliveryAddData = array();
        //套循环  外层循环批次数  内部循环产品型号个数i
        //获取的批次数.$this->posts['gnumber'] 批次数
        for ($i = 0; $i < count($data); $i++) {
            for ($j = 0; $j < count($data[$i]['data']); $j++) {
                if (!isset($data[$i]['data'][$j]['id'])) {
                    $deliveryAddData[] =  array(
                        'pid'          => (int)$data[$i]['data'][$j]['pid'],
                        'product_name' => $data[$i]['data'][$j]['product_name'],
                        'send_num'     => $data[$i]['data'][$j]['send_num'],
                        'track_number' => $data[$i]['track_number'],
                        'bactch'       => $data[$i]['bactch'],
                        'send_date'    => (int)$data[$i]['send_date'],
                        'send_manid'   => session('staffId'),
                        'send_man'     => session('nickname'),
                    );
                }
            }
        }
        return $deliveryAddData = array_values($deliveryAddData);
    }
    protected function getChangeRight($id)
    {
        $map['sid'] = array('EQ', $id);
        $rst = M('salerecord')->field('sale_commissioner sale_id, is_over,is_ok,note,rgmoney,sum_fee,is_show')->where($map)->find();
        $flag['edit_auth']   = $rst['sale_id'] == $this->staffId ? true : false;
        $flag['is_editable'] = $rst['is_over'] == 1 ? false : true;
        return $flag;
    }

    public function editSaleRepairing()
    {
        /**
         * 规定接口返回值：1 成功 2 失败 3 无权修改
         * 这方法写的真的垃圾，优化，不存在的
        */
        $saleRecordModel = new SalerecordModel();
        if (IS_POST) {
            //获取维修人信息
            $this->posts = I('post.');
            $auth = $this->getChangeRight((int)$this->posts['feeAndTips']['sid']);
            if (!$auth['edit_auth']) {
                $this->returnAjaxMsg("您无权修改该单据", 500);
            }
            if (!$auth['is_editable']) {
                $this->returnAjaxMsg("该单据已经处于完结状态，您无法修改", 500);
            }
            $saleRecordFilter['sid']= array('EQ', (int)$this->posts['feeAndTips']['sid']);
            $saleRecordBasicData = $saleRecordModel->findMoreInfo('crm_salerecord', 'is_over,is_ok,note,rgmoney,ifnull(sum_fee,0) sum_fee,is_show', $saleRecordFilter);

            $repairPersonModel = new RepairpersonModel();
            $repairPersonUpdateData = $repairPersonModel->getRepairPersonUpdateData($this->posts['product_list']);
            if ($repairPersonUpdateData === false) {
                $this->returnAjaxMsg("提交的维修产品数据有误，超出了要维修的总数，未提交更新，请重试", 404);
            }
            $repairPersonAddData = $repairPersonModel->getRepairPersonAddData($this->posts['product_list'], (int)$this->posts['feeAndTips']['sid']);
            if ($repairPersonAddData === false) {
                $this->returnAjaxMsg("提交的维修产品数据有误，未提交更新，请重试", 404);
            }

            if (isset($this->posts['stock'])) {
                $stockInAppId = $this->getOrderNumber('stock_in_apply');
            }
            M()->startTrans();
            //获取pid 查询维修人表中的对应数据条数
            $map['pid']  = array('EQ', (int)$this->posts['feeAndTips']['sid']);
            // 分配维修品情况原有信息查询
            $repairPersonOldData = M('repairperson')->field('*')->where($map)->select();
            $repairPersonUpdateNumber = count($repairPersonUpdateData);

            /* delete data which mysql's table name is crm_repairperson */
            $repairPersonDeleteIdArray = $this->getDeletePrimaryId($repairPersonUpdateData, $repairPersonOldData, 'rpid');

            if (count($repairPersonDeleteIdArray) != 0) {
                $repairPersonDeleteIds = implode(",", $repairPersonDeleteIdArray);
                $deleteFilter['rpid'] = array('IN', $repairPersonDeleteIds);
                $repairPersonDelRst = M()->table('crm_repairperson')->where($deleteFilter)->delete();
                if ($repairPersonDelRst === false) {
                    M()->rollback();
                    $this->returnAjaxMsg("修改失败，删除旧数据出错，事务回滚，未提交更新，请重试", 405);
                }
            }

            /* use where condition limit by primary key to update data which data in table crm_repairperson*/
            if ($repairPersonUpdateNumber > 0) {
                for ($i = 0; $i < $repairPersonUpdateNumber; $i++) {
                    $updateMap[$i]['rpid']        = array('EQ', $repairPersonUpdateData[$i]['rpid']);
                    $repairPersonChangeRst[$i] = M()->table('crm_repairperson')->where($updateMap[$i])->save($repairPersonUpdateData[$i]);
                    if ($repairPersonChangeRst[$i] === false) {
                        M()->rollback();
                        $this->returnAjaxMsg("维修分配数据更新失败，事务回滚，未提交更新，请重试", 405);
                    }
                }

                $changeData = $this->getRepairPersonChangeData($repairPersonOldData, $repairPersonUpdateData);
                if (count($changeData) != 0) {
                    $changeDataAddRst = M()->table('crm_salerecordchange')->addAll($changeData);
                    if ($changeDataAddRst === false) {
                        M()->rollback();
                        $this->returnAjaxMsg("修改提交失败，事务回滚，未提交更新，请重试", 405);
                    }
                }
            }

            /* add new data of crm_repairpersion */
            if (count($repairPersonAddData) >= 1) {
                $repairPersonAddRst = M()->table('crm_repairperson')->addAll($repairPersonAddData);
                if ($repairPersonAddRst === false) {
                    M()->rollback();
                    $this->returnAjaxMsg("提交的维修产品数据失败，回滚，未提交更新，请重试", 405);
                }
                $changeAddData = array();
                for ($j = 0; $j < count($repairPersonAddData); $j++) {
                    $changeAddData[$j] = array(
                        'saleid'            => $repairPersonAddData[$j]['pid'],
                        'repersonorderid'   => $repairPersonAddRst + $j,
                        'change_status_time'=> time(),
                        'change_status'     => '2',
                        'changemanname'     => session('nickname'),
                        'changemanid'       => session('staffId'),
                    );
                }
                $changeAddRst = M()->table('crm_salerecordchange')->addAll($changeAddData);
                if ($changeAddRst === false) {
                    M()->rollback();
                    $this->returnAjaxMsg("维修产品状态记录失败，未提交更新，请重试", 405);
                }
            }

            /* update crm_salerecord's data (most of these changes is status and record some change_time etc*/
            $saleRecordUpdateData = array(
                'note'               => $this->posts['feeAndTips']['note'],
                'rgmoney'            => $this->posts['feeAndTips']['rgmoney'],
                'sum_fee'            => $this->posts['feeAndTips']['totalFee'],
                'change_status_time' => time(),
            );

            /* Determine whether the charge amount has changed  If there is a change, modify the repair order status*/
            if ($saleRecordBasicData[0]['rgmoney'] != $saleRecordUpdateData['rgmoney'] || $saleRecordBasicData[0]['sum_fee'] != $saleRecordUpdateData['sum_fee']) {
                $saleRecordUpdateData['is_ok']   = '2';
                $saleRecordUpdateData['is_show'] = '0';
            }
            $saleRecordUpdateRst = M()->table('crm_salerecord')->where($saleRecordFilter)->save($saleRecordUpdateData);
            if ($saleRecordUpdateRst === false) {
                M()->rollback();
                $this->returnAjaxMsg("修改提交失败，未提交更新，请重试", 405);
            }
            $recordRst = $saleRecordModel->changeSaleStatus((int)$this->posts['feeAndTips']['sid'], self::CHANGE_STATUS_2);
            if ($recordRst === false) {
                M()->rollback();
                $this->returnAjaxMsg("修改提交失败，未提交更新，请重试", 405);
            }

            /* Submit transaction, submit modify table data */
            M()->commit();
            /* -------------------------------------------------------------------------------------------------- */
            /* Determine the status of the repair order, whether you can enter the delivery, storage information */
            $this->field = "is_ok";
            $saleRecordStatus = M('salerecord')->where($saleRecordFilter)->field($this->field)->find();
            if ($saleRecordStatus['is_ok'] == 2) {
                /*Wait for maintenance confirmation status, remove shipment, storage information, return msg*/
                M()->startTrans();
                $deleteDeliveryRst = M()->table('crm_sendgoods')->where($map)->delete();
                if ($deleteDeliveryRst === false) {
                    M()->rollback();
                    $this->returnAjaxMsg("更新了维修分配情况，发货更新失败", 405);
                }
                M()->commit();
                /**
                 * @todo : 删除掉入库信息对应减库存等操作
                */
                $this->returnAjaxMsg("提交成功，产生维修费用，需要确认后才填写发货等信息并完结流程", 200);
            } else {
                /* 维修费用未产生变化或者未产生维修费 => 进行维修 可提交发货等信息 提交完毕如果不为空，salerecord主表修改数据 */

                if (isset($this->posts['sendGoods'])) {

                    M()->startTrans();
                    $this->field = "id,pid,product_name,track_number,send_date,bactch,send_num,send_manid,send_man";
                    $deliveryOldData = M('sendgoods')->where($map)->field($this->field)->select();
                    $deliveryUpdateData = array();

                    if (count($deliveryOldData) != 0) {
                        $deliveryUpdateData = $this->getDeliveryUpdateData($this->posts['sendGoods']);
                        $deliveryDelIdArray = $this->getDeletePrimaryId($deliveryUpdateData, $deliveryOldData, 'id');
                        if (count($deliveryDelIdArray) != 0) {
                            $deleteIds = implode(",", $deliveryDelIdArray);
                            $deleteFilter['id'] = array('IN', $deleteIds);
                            $repairPersonDelRst = M()->table('crm_sendgoods')->where($deleteFilter)->delete();
                            if ($repairPersonDelRst === false) {
                                M()->rollback();
                                $this->returnAjaxMsg("提交成功维修记录", 405);
                            }
                        }
                        if (count($deliveryUpdateData) != 0) {
                            for ($i = 0; $i < count($deliveryUpdateData); $i++) {
                                $updateFilter[$i]['id'] = array('EQ', $deliveryUpdateData[$i]['id']);
                                $deliveryUpdateRst[$i]  = M()->table('crm_sendgoods')->where($updateFilter[$i])->save($deliveryUpdateData[$i]);
                                if ($deliveryUpdateRst[$i] === false) {
                                    M()->rollback();
                                    $this->returnAjaxMsg("提交成功维修记录", 405);
                                }
                            }
                        }
                    }
                    $deliveryAddData = $this->getDeliveryAddData($this->posts['sendGoods']);

                    if (count($deliveryAddData) != 0) {
                        $deliveryAddRst = M()->table('crm_sendgoods')->addAll($deliveryAddData);
                        if ($deliveryAddRst === false) {
                            M()->rollback();
                            $this->returnAjaxMsg("发货信息提交失败", 405);
                        }
                    }
                    $changeStatus = self::CHANGE_STATUS_2;
                    if (count($deliveryAddData) != 0 || count($deliveryUpdateData) != 0) {
                        $saleRecordUpdateData['is_ok']  = '4';
                        $saleRecordUpdateData['status'] = '3';
                        $changeStatus = self::CHANGE_STATUS_3;
                    }
                    $saleRecordUpdateRst = M()->table('crm_salerecord')->where($saleRecordFilter)->save($saleRecordUpdateData);
                    if ($saleRecordUpdateRst === false) {
                        M()->rollback();
                        $this->returnAjaxMsg("发货信息提交失败", 405);
                    }
                    $recordRst = $saleRecordModel->changeSaleStatus((int)$this->posts['feeAndTips']['sid'], $changeStatus);
                    if ($recordRst === false) {
                        M()->rollback();
                        $this->returnAjaxMsg("发货信息提交失败", 405);
                    }
                    // add or update data of saleprint form which sid = $data['pid']
                    $printCondition['sid'] = array('EQ', $this->posts['feeAndTips']['sid']);
                    $salePrintData = M('saleprint')->where($printCondition)->field("id,sid,product_name")->select();
                    $repairPersonAssign = M('repairperson')->field('*')->where($map)->select();
                    if (count($repairPersonAssign) != 0) {
                        $printAddData = $this->getPrintAddData($this->posts['feeAndTips']['sid'], $repairPersonAssign);
                        if (count($salePrintData) == 0) {
                            $printAddRst = M()->table('crm_saleprint')->addAll($printAddData);
                            if ($printAddRst === false) {
                                M()->rollback();
                                $this->returnAjaxMsg("发货信息提交失败", 405);
                            }
                        } else {
                            for ($i = 0; $i < count($salePrintData); $i++) {
                                for ($j = 0; $j < count($printAddData); $j++) {
                                    if ($printAddData[$j]['product_name'] == $salePrintData[$i]['product_name']) {
                                        $printAddData[$j]['id'] = $salePrintData[$i]['id'];
                                    }
                                }
                            }
                            for ($j = 0; $j < count($printAddData); $j++) {
                                $printUpdateCondition[$j]['id'] = array('EQ', $printAddData[$j]['id']);
                                $printUpdateRst[$j] = M()->table('crm_saleprint')->where($printUpdateCondition[$j])->save($printAddData[$j]);
                                if ($printUpdateRst[$j] === false) {
                                    M()->rollback();
                                    $this->ajaxReturn(2);
                                }
                            }
                        }
                    }

                    M()->commit();
                }

                if (isset($this->posts['stock'])) {
                    M()->startTrans();
                    /* 添加入库记录 */
                    $stockOldData = $this->getStockOldData($this->posts['stock'][0]['data'][0]['action_order_number']);
                    $stockData   = $this->getStockData($this->posts['stock'], $stockOldData);
                    // @todo 不支持更新入库数据 只能添加
                    if (count($stockData['stockAddData']) != 0) {
                        for ($i = 0; $i < count($stockData['stockAddData']); $i ++) {
                            $idArr[$i] = $this->getOrderNumber('stock_audit');
                            $stockData['stockAddData'][$i]['audit_order_number'] = "RK" .  $idArr[$i]['orderString'];
                            $stockData['stockAddData'][$i]['id'] = $idArr[$i]['orderId'];
                        }
                        $stockAddRst = M()->table('crm_stock_audit')->addAll($stockData['stockAddData']);
                        if ($stockAddRst === false) {
                            M()->rollback();
                            $this->returnAjaxMsg("入库信息提交失败", 405);
                        }
                        /**
                         * 提交售后入库申请单20180904更新内容
                        ****/
                        $stockInAppModel = new StockInOtherApplyModel();

                        $stockInAfterSaleRst = $stockInAppModel->addStockInWithAfterSaleData($stockData['stockAddData'], $stockInAppId);
                        if ($stockInAfterSaleRst === false) {
                            M()->rollback();
                            $this->returnAjaxMsg($stockInAppModel->getError(), 405);
                        }
                    }
                    M()->commit();
                }

                $this->field = "is_ok";
                $saleRecordFilter['sid']= array('EQ', (int)$this->posts['feeAndTips']['sid']);
                $saleRecordStatus = M('salerecord')->where($saleRecordFilter)->field($this->field)->find();
                if ($saleRecordStatus['is_ok'] == 2) {
                    $this->returnAjaxMsg("保存数据成功，该单为收费单据，业务还未确认,不能保存发货信息，不能完结售后单", 200);
                } else {
                    switch ((int)$this->posts['orderStatusFlag']) {
                        case 1 :
                            $saleRecordUpdateData['is_ok'] = 4;
                            break;
                        case 2 :
                            $saleRecordUpdateData['is_ok'] = 5;
                            break;
                        case 3 :
                            $saleRecordUpdateData['is_ok'] = 7;
                            break;
                        default :
                            break;
                    }
                    if (isset($this->posts['flag']) && $this->posts['flag'] == 1) {
                        $saleRecordUpdateData['is_over'] = 1;
                        $saleRecordUpdateData['over_time'] = time();
                        $saleRecordUpdateData['change_status_time'] = time();
                    }

                    $saleRecordUpdateMap['sid'] = array('EQ', (int)$this->posts['feeAndTips']['sid']);
                    $updateSaleRecordRst = $saleRecordModel->updateSaleRecord('crm_salerecord', $saleRecordUpdateData, $saleRecordUpdateMap);
                    if ($updateSaleRecordRst === false) {
                        //添加发货时间到记录表 更改主表is_ok = 4
                        $this->returnAjaxMsg("数据修改成功，完结失败", 200);
                    }
                    $saleRecordLogRst = $saleRecordModel->changeSaleStatus((int)$this->posts['feeAndTips']['sid'], self::CHANGE_STATUS_4);
                    if ($saleRecordLogRst === false) {
                        $this->returnAjaxMsg("数据修改成功，完结失败", 200);
                    }
                    $this->returnAjaxMsg('提交处理成功',200);
                }
                $this->returnAjaxMsg('提交处理成功',200);
            }
        } else {
            $productModel = new IndustrialSeralScreenModel();
            $sid = inject_id_filter(I('get.sid'));
            $map['pid'] = array('EQ', $sid);
            $saleRecordMap['sid'] = array('EQ', $sid);
            //获取整体备注信息
            $this->field = "sid,yid,sale_commissioner sale_id,is_over,is_ok,is_show,note,rgmoney,reback_content";
            $saleRecordBasicData = $saleRecordModel->findMoreInfo('crm_salerecord',$this->field,$saleRecordMap);
            $editableFlag = array();
            $editableFlag['completeFlag']     = $saleRecordBasicData[0]['is_over'] == 0 ? false : true;
            $editableFlag['editFlag']         = $saleRecordBasicData[0]['sale_id'] == $this->staffId ? true : false;
            $editableFlag['repairAssignFlag'] = in_array((int)$saleRecordBasicData[0]['is_ok'], $this->assignFlag) ? true : false;
            $editableFlag['showFeedbackFlag'] = (int)$saleRecordBasicData[0]['is_show'] == 2 ? true : false;

            //1 获取repairgoodsinfo 基本信息，assign变量到模板
            $this->field = "is_show, yid, sid, reback_address, r.product_category uname, r.product_name,
                        r.num, r.barcode_date,a.name sale_mode, is_over, r.customer_question, cusname, sale_commissioner,
                        salename, is_audit, courier_number, repair_date, r.sale_way, sale_number";
            $repairProductData = $saleRecordModel->getProInfo($this->field, $map);
            $productInfo = M('repairgoodsinfo')->field('product_name')->group('product_name')->where($map)->select();
            for ($i = 0; $i < count($productInfo); $i++) {
                $condition[$i]['product_name'] = array('EQ', $productInfo[$i]['product_name']);
                $this->field = "warehouse_name,warehouse_number";
                $productInfo[$i]['warehouse'] = $productModel->where($condition[$i])->field($this->field)->select();
            }
            //2 有维修产品的分配维修信息，获取repairgoodsinfo表中维修人信息
            $this->field = 'r.rpid, r.pid, r.product_name, r.fault_info, r.reperson_name, r.re_num, r.start_date, r.re_status,
                    r.re_mode, r.mode_info, r.piece_wage, r.reperson_question, r.meter_piece,r.situation';
            $repairAssignData = $saleRecordModel->getRepersonInfo($this->field, $map);


            foreach ($productInfo as $key1 => &$value1) {
                $value1['product_id'] = $productModel -> getProductIDbyProductName($value1['product_name']);
                foreach ($repairAssignData as $key2 => $value2) {
                    if ($value1['product_name'] == $value2['product_name']){
                        $value1['data'][] = $value2;
                    }
                }
            }
            //获取维修人 维修专员
            $repairStaffIds = $this->getRoleStaffIds(self::REPAIR_ROLE);
            $repairStaffArrMap['id'] = array("IN", $repairStaffIds);
            $repairStaffArray = M('staff')->field('id, name')->where($repairStaffArrMap)->order('id desc')->select();

            //状态
            $repairStatus = M('repairperson_restatus')->field('id,name')->select();

            //维修方式
            $repairMode = M('repairperson_remode')->select();

            //故障类型
            $repairQuestion = M('repairperson_repersonquestion')->select();

            //获取状态，维修方式，故障类型
            $where['pid'] = $sid;
            $rst3 = M('repairperson')->where($where)->field('re_status,re_mode,reperson_question')->select();
            $rst3 = json_encode($rst3);

            //取出后面的产品型号和数量
            $field = 'r.id,r.send_man,r.pid,r.product_name,r.track_number,r.send_date,r.bactch,r.send_num';
            $xiangqing = $saleRecordModel->getSendInfo($field, $map);
            $arr = [];
            foreach ($xiangqing as $key => $value) {
                $arr[$value['bactch']]['bactch'] = $value['bactch'];
                $arr[$value['bactch']]['send_man'] = $value['send_man'];
                $arr[$value['bactch']]['track_number'] = $value['track_number'];
                $arr[$value['bactch']]['send_date'] = $value['send_date'];
                $arr[$value['bactch']]['data'][] = $value;
            }
            $sendGoods = [];
            foreach ($arr as $key => $value) {
                $sendGoods[] = $value;
            }

            // 获得审核员名单
            $res = M('auth_role') -> where(['role_id' => ['IN', self::AUDIT_ROLE]]) -> select();
            $role_ids = [];
            foreach ($res as $key => $value) {
                $role_ids = array_merge(explode(',',$value['staff_ids']), $role_ids);
            }
            $map = ['id' => ['IN', $role_ids], 'loginstatus' => ['NEQ','1']];
            $auditorArr = M('staff') -> field('id, name') -> where($map) -> select();


            //出入库审核记录
            $orderNumber = $repairProductData[0]['sale_number'];

            $map = [
                'action_order_number' => ['EQ', $orderNumber],
                'is_del' => ['eq',0]
            ];
            $stock_io_record = M('stock_audit')
                 -> alias('audit')
                 -> field('audit.*, staff.name auditor_name')
                 -> join('left join crm_staff as staff on audit.auditor = staff.id')
                 -> where($map)
                 -> order('audit.batch asc')
                 -> select();
            $arr = [];
            foreach ($stock_io_record as $key => $value) {
                $arr[$value['batch']]['batch'] = $value['batch'];
                $arr[$value['batch']]['data'][] = $value;
            }
            $stock_io_record = [];
            foreach ($arr as $key => $value) {
                $stock_io_record[] = $value;
            }
            // 仓库名称map  从crm_repertorylist 表中查出
            $repertoryListModel = new RepertorylistModel();
            $repMap = $repertoryListModel->getStockOutList();

            $this->assign([
                'repMap' => $repMap, // 出库仓库Map
                'flagArray' => $editableFlag,
                'productList'=> $productInfo,
                'repairProductData'=> $repairProductData,
                'repairAssignData'=> $repairAssignData,
                'saleRecordBasicData'=>$saleRecordBasicData,
                'repairStaffArray'=>$repairStaffArray,
                'repairStatus'=>json_encode($repairStatus),
                'repairMode'=>json_encode($repairMode),
                'repairQuestion'=>json_encode($repairQuestion),
                'rst3'=> $rst3,
                'sendGoods'=>$sendGoods,
                'auditorArr'=>$auditorArr,
                'stock_io_record'=>$stock_io_record,
                'sid'=>$sid
            ]);
            $this->display('editSaleRepairingInputStock');
        }
    }

    /**
     * 修改售后单基本信息
     */
    public function editSaleBaseMsg(){
        $saleRecordModel = new SalerecordModel();
        if(IS_POST){
            $data = I("post.");

            if(!isset($data['baseMsg']) || !isset($data['productMsg'])){
                $this->returnAjaxMsg("参数不全",400);
            }

            $returnData = $saleRecordModel->editSaleRecord($data['baseMsg'], $data['productMsg']);
            $this->ajaxReturn($returnData);
        }else {
            $id = I("get.id");

            // 维修单基本信息
            $recordData = $saleRecordModel->find($id);

            // 判断基本内容是否可以改变
            $map['action_order_number'] = ['eq', $recordData['sale_number']];
            $map['is_del'] = ['eq', StockOutModel::NO_DEL];
            $stockData = M()->table("crm_stock_audit")->where($map)->select();
            if(!empty($stockData)){
                die("已有出库记录，无法修改当前内容");
            }

            $goodData = M()->table("crm_sendgoods")->where(['pid' => $id])->select();
            if(!empty($goodData)){
                die("已有发货批次，无法修改当前内容");
            }

            // 维修单物料信息crm_repairgoodsinfo
            $productData = M()->table("crm_repairgoodsinfo")->where(['pid' => $id])->select();

            // 判断是否单据下方有维修人员信息存在。
            $repairpersonModel = new RepairpersonModel();
            $personData = $repairpersonModel->where(['pid' => $id])->select();
            $flag = 0;
            if(empty($personData)){
                $flag = 1;
            }

            // 售后方式
            $shmethod = M('repairgoodsinfo_saleway')->field('id,name')->select();

            //获取所有业务名称
            $roleFilter['role_id'] = array('IN', self::CUS_ROLE);
            $this->field = "staff_ids";
            $authModel    = new AuthRoleModel();
            $roleStaffArray = $authModel->getRoleList($this->field, $roleFilter, 'role_id', 0, 1000);
            $staffIds = getPrjIds($roleStaffArray, "staff_ids");
            $staffArray['loginstatus'] = array('NEQ', "1");
            $staffArray['id'] = array("IN",$staffIds);
            $res = D('salerecord')->findMoreInfo('crm_staff','id,name',$staffArray);

            //获取产品分类
            $map1['level'] = array('EQ', '1');
            $productCate = D('salerecord')->findMoreInfo('crm_screen_category', 'id,name', $map1);

            //获取产品型号
            $product = M('material')
                ->field('product_id,platform_id,product_name')->where("1 = 1")->select();
            $this->assign('productCate', $product);

            $this->assign([
                'recordData' => $recordData,  // 基本信息
                'flag' => $flag,  // 标志数量、产品分类、物料型号是否可以改变。 1=> 可以修改 0=>不可以修改
                'productData' => $productData, // 物料信息
                'shmethod' => $shmethod, // 售后方式
                'res' => $res, // 业务员信息
                'proCate' => $productCate, // 产品分类信息
                'productCate' => $product, // 产品型号
            ]);
            $this->display();
        }
    }

    /**
     * 修改维修人员的维修内容，本人修改。
     */
    public function editRepairpersonMsg(){
        $personModel = new RepairpersonModel();
        if(IS_POST){
            $data = I("post.data");
            if(empty($data)){
                $this->returnAjaxMsg("参数错误",400);
            }

            $returnData = $personModel->editPersonMsg($data);
            $this->ajaxReturn($returnData);
        }else {
            $id = I("get.id");
            $saleRecordModel = new SalerecordModel();

            // 维修单基本信息
            $recordData = $saleRecordModel->find($id);

            // 判断基本内容是否可以改变
            $map['action_order_number'] = ['eq', $recordData['sale_number']];
            $map['is_del'] = ['eq', StockOutModel::NO_DEL];
            $stockData = M()->table("crm_stock_audit")->where($map)->select();
            if(!empty($stockData)){
                die("已有出库记录，无法修改当前内容");
            }

            $goodData = M()->table("crm_sendgoods")->where(['pid' => $id])->select();
            if(!empty($goodData)){
                die("已有发货批次，无法修改当前内容");
            }

            // 维修单物料信息crm_repairgoodsinfo
            $productData = M()->table("crm_repairgoodsinfo")->where(['pid' => $id])->select();

            // 当前登陆维修工的的维修内容
            $map['pid'] = ['eq', $id];
            $map['reperson_id'] = ['eq', session('staffId')];
            $personData = $personModel->where($map)->select();
            if(empty($personData)){
                die("当前售后单据中不存在您的维修任务。请重新选择");
            }

            // 状态
            $rst = M('repairperson_restatus')->select();

            // 维修方式
            $rst1 = M('repairperson_remode')->select();

            //故障类型
            $rst2 = M('repairperson_repersonquestion')->select();

            $this->assign([
                'rst' => $rst, // 状态
                'rst1' => $rst1, // 维修方式
                'rst2' => $rst2, // 故障类型
                'recordData' => $recordData,  // 基本信息
                'productData' => $productData, // 物料信息
                'personData' => $personData, // 当前登陆人对应的维修内容
            ]);
            $this->display();
        }
    }

    protected function getStockOldData($sourceOrderId)
    {
        $stockFilter['action_order_number'] = array('EQ', $sourceOrderId);
        $this->field = "id,
                        product_id,
                        product_number,
                        product_no,
                        product_name,
                        num,
                        type,
                        tips,
                        audit_tips,
                        audit_status,
                        proposer,
                        proposer_name,
                        auditor,
                        auditor_name,
                        warehouse_name,
                        warehouse_number,
                        create_time,
                        update_time,
                        cate_name,
                        cate,
                        audit_order_number,
                        action_order_number,
                        batch";
        $stockOldData = M('stock_audit')
            ->where($stockFilter)
            ->field($this->field)
            ->select();
        return $stockOldData;
    }
    protected function getStockData($postsData, $stockOldData)
    {
        $stockData = array();
        for ($i = 0; $i < count($postsData); $i++) {
            for ($j = 0; $j < count($postsData[$i]['data']); $j++) {
                $condition[$j]['product_id'] = array('EQ', $postsData[$i]['data'][$j]['product_id']);
                $this->field = "product_id,product_name,product_number,product_no";
                $productData[$j] = M('material')->where($condition[$j])->field($this->field)->find();
                $basicData[$j] = array(
                    'product_id'         => $postsData[$i]['data'][$j]['product_id'],
                    'product_number'     => $productData[$j]['product_number'],
                    'product_no'         => $productData[$j]['product_no'],
                    'product_name'       => $productData[$j]['product_name'],
                    'num'                => (int)$postsData[$i]['data'][$j]['num'],
                    'type'               => StockAuditModel::IN_TYPE,
                    'tips'               => $postsData[$i]['data'][$j]['tips'],
                    'audit_status'       => StockAuditModel::AUDIT_PASS,
                    'proposer'           => session('staffId'),
                    'proposer_name'      => session('nickname'),
                    'auditor'            => getStringId($postsData[$i]['data'][$j]['auditorArr']),
                    'auditor_name'       => getStringChar($postsData[$i]['data'][$j]['auditorArr']),
                    'warehouse_name'     => getStringChar($postsData[$i]['data'][$j]['warehouseArr']),
                    'warehouse_number'   => getStringId($postsData[$i]['data'][$j]['warehouseArr']),
                    'update_time'        => time(),
                    'cate_name'          => getStringChar($postsData[$i]['data'][$j]['cateArr']),
                    'cate'               => getStringId($postsData[$i]['data'][$j]['cateArr']),
                    'action_order_number'=> $postsData[$i]['data'][$j]['action_order_number'],
                    'batch'              => empty($postsData[$i]['batch']) ? "1" : $postsData[$i]['batch'],
                );
                if (isset($postsData[$i]['data'][$j]['id'])) {
                    for ($p = 0; $p < count($stockOldData); $p++) {
                        if ($stockOldData[$p]['id'] == $postsData[$i]['data'][$j]['id']) {
                            if ($stockOldData[$p]['audit_status'] != "2") {
                                $stockData['stockUpdateData'][count($postsData[$i]['data']) * $i + $j]= $basicData[$j];
                                $stockData['stockUpdateData'][count($postsData[$i]['data']) * $i + $j]['id'] = (int)$postsData[$i]['data'][$j]['id'];
                                $stockData['stockUpdateData'][count($postsData[$i]['data']) * $i + $j]['audit_order_number'] = $postsData[$i]['data'][$j]['audit_order_number'];
                            }
                        }
                    }
                } else {
                    $stockData['stockAddData'][count($postsData[$i]['data']) * $i + $j]= $basicData[$j];
                }
            }
        }
        $stockData['stockUpdateData'] = array_values($stockData['stockUpdateData']);
        $stockData['stockAddData']    = array_values($stockData['stockAddData']);
        return $stockData;
    }
    protected function getStockDeleteId($deleteIdArr, $stockOldData)
    {
        if (count($deleteIdArr) != 0) {
            for ($i = 0; $i < count($deleteIdArr); $i++) {
                for ($j = 0; $j < count($stockOldData); $j++) {
                    if ($deleteIdArr[$i] == $stockOldData[$j]['id']) {
                        if ($stockOldData[$j]['audit_status'] != 2) {
                            $stockDeleteId[] = $deleteIdArr[$i];
                        }
                    }
                }
            }
        }
    }


    //结束流程
    public function editSendgoods()
    {
        $this->posts = I('post.');
        $data = array_column($this->posts['data'], 'value', 'name');
        $map['pid'] = array('EQ', $data['pid']);
        M()->startTrans();
        $productNum  = M('repairgoodsinfo')->where($map)->field('product_name')->group('product_name')->count();
        $this->field = "id,pid,product_name,track_number,send_date,bactch,send_num,send_manid,send_man";
        $deliveryOldData = M('sendgoods')->where($map)->field($this->field)->select();

        $deliveryUpdateData = array();
        if (count($deliveryOldData) != 0) {
            $deliveryUpdateData = $this->getDeliveryUpdateData($data);
            $deliveryDelIdArray = $this->getDeletePrimaryId($deliveryUpdateData, $deliveryOldData, 'id');
            /* delete delivery data which not in updateData array */
            if (count($deliveryDelIdArray) != 0) {
                $deleteIds = implode(",", $deliveryDelIdArray);
                $deleteFilter['id'] = array('IN', $deleteIds);
                $repairPersonDelRst = M()->table('crm_sendgoods')->where($deleteFilter)->delete();
                if ($repairPersonDelRst === false) {
                    M()->rollback();
                    $this->ajaxReturn(2);
                }
            }
            /* update delivery data which in updateData array*/
            if (count($deliveryUpdateData) != 0) {
                for ($i = 0; $i < count($deliveryUpdateData); $i++) {
                    $updateFilter[$i]['id'] = array('EQ', $deliveryUpdateData[$i]['id']);
                    $deliveryUpdateRst[$i]  = M()->table('crm_sendgoods')->where($updateFilter[$i])->save($deliveryUpdateData[$i]);
                    if ($deliveryUpdateRst[$i] === false) {
                        M()->rollback();
                        $this->ajaxReturn(2);
                    }
                }
            }
        }

        /* add delivery data*/
        $deliveryAddData = $this->getDeliveryAddData($data, (int)$this->posts['gnumber'] + 1, $productNum);
        if (count($deliveryAddData) != 0) {
            $deliveryAddRst = M()->table('crm_sendgoods')->addAll($deliveryAddData);
            if ($deliveryAddRst === false) {
                M()->rollback();
                $this->ajaxReturn(2);
            }
        }

        if ($this->posts['flag'] == 1) {
            $saleRecordUpdateMap['sid'] = array('EQ', $data['pid']);
            $saleRecordUpdateData = array (
                'is_over'            => 1,
                'is_ok'              => 4,
                'over_time'          => time(),
                'change_status_time' => time()
            );

            $updateSaleRecordRst = D('salerecord')->updateSaleRecord('crm_salerecord', $saleRecordUpdateData, $saleRecordUpdateMap);
            if ($updateSaleRecordRst !== false) {
                //添加发货时间到记录表 更改主表is_ok = 4
                $saleRecordLogRst = M('salerecord')->changeSaleStatus($data['pid'], self::CHANGE_STATUS_4);
                if ($saleRecordLogRst === false) {
                    $this->ajaxReturn(2);
                }
            } else {
                $this->ajaxReturn(2);
            }
        }
        M()->commit();
        $this->ajaxReturn(1);
    }
    //退换货结束流程
    public function singleFinish()
    {
        $posts = I('post.');
        //无需审核的直接完结
        if($posts['flag'] == 1){
            $result = D('salerecord')->changeSaleStatus($posts['sid'],'5');
            $map['sid'] = $posts['sid'];
            $tiaojian1['is_over'] = 1;
            $tiaojian1['is_ok']   = 5;
            $tiaojian1['status']  = 4;
            $tiaojian1['over_time'] = time();
            $tiaojian1['change_status_time'] = time();
            $result1 = D('salerecord')->updateSaleRecord('crm_salerecord',$tiaojian1,$map);
            //获取产品数量，将入库数量写入发货表
            $where['pid'] = $posts['sid'];
            $result2 = D('salerecord')->findMoreInfo('crm_repairgoodsinfo','product_name,num',$where);
            foreach ($result2 as $key => $value) {
                $condition['product_name'] = $value['product_name'];
                $condition['send_num']     = $value['num'];
                $condition['send_date']    = time();
                $condition['send_manid']   = session('staffId');
                $condition['send_man']     = session('nickname');
                $condition['pid']          = $posts['sid'];
                $result3 = M('sendgoods')->add($condition);
            }
            if($result1){
                $this->ajaxReturn(1);
            }else{
                $this->ajaxReturn(2);
            }
            //需要审核直接完结
        }else{
            $result = D('salerecord')->changeSaleStatus($posts['sid'],'5');
            $map['sid'] = $posts['sid'];
            $tiaojian1['status']  = 4;
            $tiaojian1['is_over'] = '1';
            $tiaojian1['is_ok']   = 5;
            $tiaojian1['over_time'] = time();
            $tiaojian1['change_status_time'] = time();
            $result1 = D('salerecord')->updateSaleRecord('crm_salerecord',$tiaojian1,$map);
            //获取产品数量，将入库数量写入发货表
            $where['pid'] = $posts['sid'];
            $result2 = D('salerecord')->findMoreInfo('crm_repairgoodsinfo','product_name,num',$where);
            foreach ($result2 as $key => $value) {
                $condition['product_name'] = $value['product_name'];
                $condition['send_num']     = $value['num'];
                $condition['pid']          = $posts['sid'];
                $condition['send_date']    = time();
                $condition['send_manid']   = session('staffId');
                $condition['send_man']     = session('nickname');
                $result3 = M('sendgoods')->add($condition);
            }
            if($result1){
                $this->ajaxReturn(1);
            }else{
                $this->ajaxReturn(2);
            }
        }

    }
    //打印售后维修单
    public function printSalerecord()
    {
        $sid = inject_id_filter(I('get.sid'));
        $where['sid'] = $sid;
        $result = M('saleprint')
            ->join(' LEFT JOIN `crm_repairperson_repersonquestion` as a ON a.id = crm_saleprint.reperson_question')
            ->where($where)
            ->select();
        $this->assign('result',$result);
        $map['sid'] = $sid;
        $result1 = M('salerecord')->where($map)->select();
        $time = time();
        $this->assign('time',$time);
        $this->assign('result1',$result1);
        $this->display();
    }
    //打印维修合同
    public function saleContract()
    {
        $sid = inject_id_filter(I('get.sid'));
        $map['sid'] = array("EQ", $sid);
        $this->field = "salename,cusname,reback_address,phone,cphonenumber,cphonename,addr,rgmoney";
        $result = M('salerecord')->where($map)
        ->join(' LEFT JOIN `crm_staff` c ON c.id = crm_salerecord.yid ')
        ->join(' LEFT JOIN `crm_customer` k ON k.cid = crm_salerecord.cusid ')
        ->field($this->field)
        ->find();
        $result['addr'] = json_decode($result['addr']);
        $time = time();

        //查询维修单表格，获取对应信息，生成总价
        $map1['pid'] = array('EQ', $sid);
        $result1 = M('repairperson')
            ->join(' LEFT JOIN `crm_repairperson_remode` a ON a.id = crm_repairperson.re_mode ')
            ->field('pid,product_name,re_num,piece_wage,a.name as re_mode, fault_info, mode_info')
            ->where($map1)->select();
        $sum = 0;
        foreach ($result1 as $key => $value) {
            if (empty($value['piece_wage'])) {
                unset($result1[$key]);
            } else {
                $total = $value['piece_wage'];
                $result1[$key]['total']  = $total;
                $result1[$key]['danjia'] = round($total/$value['re_num'],2); //round($total/$value['re_num']);

            }
            $sum += $result1[$key]['total'];
        }
        //人工费
        $sum1 = $sum + $result['rgmoney'] . '.00';
        /**
         * 杨超3.29修复不显示人工费bug
         */
        $rgmoney = $result['rgmoney'] . '.00';
        $this->assign(array(
            'saleRecordInfo' => $result,
            'time'      => $time,
        ));
        $jine = $this->get_amount($sum1);
        $this->assign('sum1', $sum1);
        $this->assign('jine', $jine);
        $this->assign('result1', $result1);
        //合同编号
        $where['yid'] = array('EQ',session('staffId'));
        $where['is_printcontract'] = array('EQ', 1);
        $today = strtotime(date('Y-m-d', time()));
        $end = $today + 24 * 60 * 60;
        $where['contract_time'] = array('BETWEEN',"$today, $end");
        $result3 = M('salerecord')->where($where)->select();
        $count = count($result3);
        if($count < 10){
            $num = '00' . ($count + 1);
        }elseif(10 <= $count){
            $num = '0' . ($count + 1);
        }elseif(100 <= $count){
            $num = $count + 1;
        }
        $now = date('Ymd', time());
        $where1['id'] = array('EQ', session('staffId'));
        $phone = substr($jphone, -4);
        $num = 'WX' . $now . $num . 'T' . $phone;
        $this->assign(compact('num', 'rgmoney'));
        $this->display();
    }
    //维修合同基本信息
    public function contractInfo()
    {
        $id['id'] = I('post.id');
        $result = M('salecontract')->where($id)->find();
        $this->ajaxReturn($result);
    }
    //打印修改主表
    public function changeContract()
    {
        $posts = I('post.');
        $map['is_printcontract'] = $posts['flag'];
        $map['contract_time'] = strtotime(date('Y-m-d', time()));
        $where['sid'] = $posts['pid'];
        $result = M('salerecord')->where($where)->save($map);
        if($result){
            $this->ajaxReturn(1);
        }else{
            $this->ajaxReturn(2);
        }

    }
    //数据报表生成
    public function saledataExport()
    {
        if(IS_AJAX){
            $posts = I('post.');
            // 获取Datatables发送的参数 必要
            $draw = $posts['draw'];
            //排序
            $order_dir = $posts['order']['0']['dir'];//ase desc 升序或者降序
            $order_column = (int)$posts['order']['0']['column'];
            switch ($order_column) {
                case 0 :
                    $order = "sale_number " . $order_dir;
                    break;
                case 1 :
                    $order = "courier_number " . $order_dir;
                    break;
                case 2 :
                    $order = "start_date " . $order_dir;
                    break;
                case 3 :
                    $order = "cusname " . $order_dir;
                    break;
                case 4 :
                    $order = "product_name " . $order_dir;
                    break;
                case 5 :
                    $order = "num " . $order_dir;
                    break;
                case 6 :
                    $order = "barcode_date " . $order_dir;
                    break;
                case 7 :
                    $order = "salename " . $order_dir;
                    break;
                case 8 :
                    $order = "is_show " . $order_dir;
                    break;
                case 9 :
                    $order = "status " . $order_dir;
                    break;
                case 10 :
                    $order = "reperson_name " . $order_dir;
                    break;
                case 11 :
                    $order = "sale_mode " . $order_dir;
                    break;
                case 12 :
                    $order = "customer_question " . $order_dir;
                    break;
                case 13 :
                    $order = "situation " . $order_dir;
                    break;
                case 14 :
                    $order = "re_mode " . $order_dir;
                    break;
                case 15 :
                    $order = "piece_wage " . $order_dir;
                    break;
                case 16 :
                    $order = "fault_info " . $order_dir;
                    break;
                case 17 :
                    $order = "track_number " . $order_dir;
                    break;
                case 18 :
                    $order = "bactch " . $order_dir;
                    break;
                case 19 :
                    $order = "send_num " . $order_dir;
                    break;
                case 20 :
                    $order = "send_date " . $order_dir;
                    break;
                case 21 :
                    $order = "note " . $order_dir;
                    break;
                default :
                    $order = "sale_number desc";
            }

            //分页
            $start  = $posts['start'];//从多少开始
            $length = $posts['length'];//数据长度
            $limitFlag  = isset($posts['start']) && $length != -1 ;
            if ($limitFlag) {
                $start  = (int)$start;
                $length = (int)$length;
            } else {
                $start  = 0;
                $length = 10;
            }

            $thismonth = date('m');
            $thisyear  = date('Y');
            $startDay  = $thisyear . '-' . $thismonth - 1 . '-1';
            $timeLimit1 = empty(I('post.timeLimit1')) ? strtotime($startDay) : strtotime(I('post.timeLimit1'));
            $timeLimit2 = empty(I('post.timeLimit2')) ? time() : strtotime(I('post.timeLimit2'));
            if ($timeLimit2 - $timeLimit1 <= 0) {
                $output = array(
                    "draw" => intval($draw),
                    "recordsTotal" => 0,
                    "recordsFiltered" => 0,
                    "data" => array()
                );
                $this->ajaxReturn($output);
            }
            //分组条件
            $groupCondition = I('post.groupCondition');
            if(empty($groupCondition)){
                $group = '';
            }else{
                if($groupCondition == 1){
                    $group = ' a.sale_number';
                }else{
                    $group = ' b.product_name';
                }
            }
            //过滤条件（客户和时间）搜索
            $cusLimit = I('post.deptLimit');
            $search = $posts['search']['value'];

            if(empty($cusLimit) && empty($search)){
                $where['repair_date'] = array(array('egt', $timeLimit1), array('elt', $timeLimit2));
            }else if(empty($cusLimit) && !empty($search)){
                if(strlen($search) < 3){
                    $this->ajaxReturn(false);
                }else{
                    $where1['cusname']          = array('like',"%".$search."%");       //客户
                    $where1['salename']         = array('like',"%".$search."%");       //业务员
                    $where1['sale_commissioner_name'] = array('like',"%".$search."%"); //售后专员
                    $where1['courier_number']   = array('like',"%".$search."%");       //收货快递单号
                    $where1['sale_number']      = array('like',"%".$search."%");       //CRM单号
                    $where1['b.product_name']   = array('like',"%".$search."%");       //产品型号
                    $where1['reperson_name']    = array('like',"%".$search."%");       //维修员
                    $where1['track_number']     = array('like',"%".$search."%");       //发货单号
                    $where1['_logic']           = "or";
                    $where['_complex']          = $where1;
                    $where['repair_date']       = array(array('egt', $timeLimit1), array('elt', $timeLimit2));
                }
            }else if(!empty($cusLimit) && empty($search)){
                $where['repair_date']   = array(array('egt', $timeLimit1), array('elt', $timeLimit2));
                $where['cusid']         = array("EQ",$cusLimit);
            }else if(!empty($cusLimit) && !empty($search)){
                if(strlen($search) < 3){
                    $this->ajaxReturn(false);
                }else{
                    $where1['cusname']              = array('like',"%".$search."%");      //客户
                    $where1['salename']             = array('like',"%".$search."%");      //业务员
                    $where1['sale_commissioner_name'] = array('like',"%".$search."%");    //售后专员
                    $where1['courier_number']       = array('like',"%".$search."%");      //收货快递单号
                    $where1['sale_number']          = array('like',"%".$search."%");      //CRM单号
                    $where1['b.product_name']       = array('like',"%".$search."%");      //产品型号
                    $where1['reperson_name']        = array('like',"%".$search."%");      //维修员
                    $where1['track_number']         = array('like',"%".$search."%");      //发货单号
                    $where1['_logic']               = "or";
                    $where['_complex']              = $where1;
                    $where['repair_date']           = array(array('egt', $timeLimit1), array('elt', $timeLimit2));
                    $where['cusid']                 = array("EQ",$cusLimit);
                }
            }
            $result = D('salerecord')->getSaleDataExport('a.sid,a.over_time,a.courier_number,a.sale_number,c.start_date,a.cusname,b.product_name,b.num,b.barcode_date,a.salename,
                       c.re_status,c.reperson_name,f.name as sale_mode,b.customer_question,IFNULL(e.name,0) as re_mode,c.fault_info,d.track_number,d.send_num,d.send_date,a.note,
                       b.sale_way,IFNULL(c.mode_info,0) as mode_info,d.bactch,a.status,c.piece_wage,c.situation,i.name as is_ok,a.is_show,d.send_man',$where,$group,$start,$length,$order);
            //数量
            $result1 = D('salerecord')->getSaleDataExport('a.sid,a.over_time,a.courier_number,a.sale_number,c.start_date,a.cusname,b.product_name,b.num,b.barcode_date,a.salename,
                       c.re_status,c.reperson_name,f.name as sale_mode,b.customer_question,IFNULL(e.name,0) as re_mode,c.fault_info,d.track_number,d.send_num,d.send_date,a.note,
                       b.sale_way,IFNULL(c.mode_info,0) as mode_info,d.bactch,a.status,c.piece_wage,c.situation,i.name as is_ok,a.is_show,d.send_man',$where,$group,'','',$order);
            $count = count($result1);
            $recordsFiltered = $count;

            if (count($result) != 0) {
                foreach($result as &$val) {
                    $val['DT_RowId']                 = $val['sid'];
                }
            }
            $output = $this->getDataTableOut($draw,$count,$recordsFiltered,$result);
            $this->ajaxReturn($output);
        }else{
            $this->display();
        }
    }

    /**
     * 售后管理生成Excel
     */
    public function exportToExcel(){
        if(IS_POST){
            $thismonth = date('m');
            $thisyear  = date('Y');
            $startDay  = $thisyear . '-' . $thismonth - 1 . '-1';
            $timeLimit1 = empty(I('post.timeLimit1')) ? strtotime($startDay) : strtotime(I('post.timeLimit1'));
            $timeLimit2 = empty(I('post.timeLimit2')) ? time() : strtotime(I('post.timeLimit2'));
            if ($timeLimit2 - $timeLimit1 <= 0) {
                $this->returnAjaxMsg("收货开始时间大于结束时间，请修改！",400);
            }
            //分组条件
            $groupCondition = I('post.groupCondition');
            if(empty($groupCondition)){
                $group = '';
            }else{
                if($groupCondition == 1){
                    $group = ' a.sale_number';
                }else{
                    $group = ' b.product_name';
                }
            }
            $where['repair_date'] = array(array('egt', $timeLimit1), array('elt', $timeLimit2));

            $result = D('salerecord')->getSaleDataExport('a.sid,a.over_time,a.courier_number,a.sale_number,c.start_date,a.cusname,b.product_name,b.num,b.barcode_date,a.salename,
                       c.re_status,c.reperson_name,f.name as sale_mode,b.customer_question,IFNULL(e.name,0) as re_mode,c.fault_info,d.track_number,d.send_num,d.send_date,a.note,
                       b.sale_way,IFNULL(c.mode_info,0) as mode_info,d.bactch,a.status,c.piece_wage,c.situation,i.name as is_ok,a.is_show,d.send_man',$where,$group);

            if(empty($result)){
                $this->returnAjaxMsg("当前筛选条件下无数据，请修改！",400);
            }

            Vendor('PHPExcel.PHPExcel');//引入类
            Vendor('PHPExcel.PHPExcel_IOFactory');//引入类
//        Vendor('PHPExcel.Writer.Excel5');  // 后缀是xls
            Vendor('PHPExcel.Writer.Excel2007'); // 后缀是xlsx

            $objPHPExcel = new \PHPExcel();                        //初始化PHPExcel(),不使用模板

            $objActSheet = $objPHPExcel->getActiveSheet();

            //这里是设置单元格的内容
            $objActSheet->setCellValue("A1","CRM单号");
            $objActSheet->setCellValue("B1","收货单号");
            $objActSheet->setCellValue("C1","送修日期");
//            $objActSheet->setCellValue("D1","客户名称");
            $objActSheet->setCellValue("D1","产品型号");
            $objActSheet->setCellValue("E1","数量");
            $objActSheet->setCellValue("F1","条码日期");
            $objActSheet->setCellValue("G1","业务员");
            $objActSheet->setCellValue("H1","业务审核");
            $objActSheet->setCellValue("I1","状态");
            $objActSheet->setCellValue("J1","维修员");
            $objActSheet->setCellValue("K1","售后方式");
            $objActSheet->setCellValue("L1","客户反馈");
            $objActSheet->setCellValue("M1","故障现象");
            $objActSheet->setCellValue("N1","维修反馈");
            $objActSheet->setCellValue("O1","维修费用(元)");
            $objActSheet->setCellValue("P1","费用明细");
            $objActSheet->setCellValue("Q1","发货单号");
            $objActSheet->setCellValue("R1","发货批次");
            $objActSheet->setCellValue("S1","发货/入库数量");
            $objActSheet->setCellValue("T1","发货/入库时间");
            $objActSheet->setCellValue("U1","发货/入库人");
            $objActSheet->setCellValue("V1","备注");

            $i = 2;
            foreach ($result as $k=>$v){
                //这里是设置单元格的内容
                $objActSheet->setCellValue("A".$i,$v['sale_number']);
                $objActSheet->setCellValue("B".$i,$v['courier_number']);
                $objActSheet->setCellValue("C".$i,date("Y-m-d H:i:s",$v['start_date']));
//                $objActSheet->setCellValue("D".$i,$v['cusname']);
                $objActSheet->setCellValue("D".$i,$v['product_name']);
                $objActSheet->setCellValue("E".$i,$v['num']);
                $objActSheet->setCellValue("F".$i,$v['barcode_date']);
                $objActSheet->setCellValue("G".$i,$v['salename']);
                $objActSheet->setCellValue("H".$i,SalerecordModel::$auditTypeMap[$v['is_show']]);
                $objActSheet->setCellValue("I".$i,$v['is_ok']);
                $objActSheet->setCellValue("J".$i,$v['reperson_name']);
                $objActSheet->setCellValue("K".$i,$v['sale_mode']);
                $objActSheet->setCellValue("L".$i,$v['customer_question']);
                $objActSheet->setCellValue("M".$i,$v['situation']);
                $objActSheet->setCellValue("N".$i,$v['re_mode']);
                $objActSheet->setCellValue("O".$i,$v['piece_wage']);
                $objActSheet->setCellValue("P".$i,$v['fault_info']);
                $objActSheet->setCellValue("Q".$i,$v['track_number']);
                $objActSheet->setCellValue("R".$i,$v['bactch']);
                $objActSheet->setCellValue("S".$i,$v['send_num']);
                $objActSheet->setCellValue("T".$i,date("Y-m-d H:i:s",$v['send_date']));
                $objActSheet->setCellValue("U".$i,$v['send_man']);
                $objActSheet->setCellValue("V".$i,$v['note']);
                $i++;
            }

            $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
            $fileName = "售后管理". '_' . date('Ymd') . '.xlsx';
            // $fileName = iconv('utf-8', 'gb2312', $fileName);//文件名称

            // 1.保存至本地Excel表格
            $rootPath = WORKING_PATH . UPLOAD_ROOT_PATH . "/excel/";
            if (!file_exists($rootPath)) {
                mkdir($rootPath, 0777,true);
            }
            $objWriter->save($rootPath . $fileName);

            $this->returnAjaxMsg("下载成功",200,[
                'file_url' => UPLOAD_ROOT_PATH . "/excel/" . $fileName
            ]);
        }else {
            die("非法");
        }
    }

    //显示个人的未审核记录
    public function showOwnUnrecord()
    {
        if(IS_AJAX){
            $posts         = I('post.');
            $draw          = $posts['draw'];
            $order_dir     = $posts['order']['0']['dir'];//ase desc 升序或者降序
            $order_column  = (int)$posts['order']['0']['column'];
            switch ($order_column) {
                case 0 :
                    $order = "sid " . $order_dir;
                    break;
                case 1 :
                    $order = "courier_number " . $order_dir;
                    break;
                case 2 :
                    $order = "cusname " . $order_dir;
                    break;
                case 3 :
                    $order = "salename " . $order_dir;
                    break;
                case 4 :
                    $order = "sale_commissioner_name " . $order_dir;
                    break;
                case 5 :
                    $order = "start_date " . $order_dir;
                    break;
                case 6 :
                    $order = "reperson_question " . $order_dir;
                    break;
                case 7 :
                    $order = "is_ok " . $order_dir;
                    break;
                case 8 :
                    $order = "is_show " . $order_dir;
                    break;
                case 9 :
                    $order = "is_repairorder " . $order_dir;
                    break;
                case 10 :
                    $order = "over_time " . $order_dir;
                    break;
                case 11 :
                    $order = "is_over " . $order_dir;
                    break;
                default :
                    $order = "sid desc, is_show desc";
            }
            //分页
            $start  = $posts['start'];//从多少开始
            $length = $posts['length'];//数据长度

            $limitFlag  = isset($posts['start']) && $length != -1 ;
            if ($limitFlag) {
                $start  = (int)$start;
                $length = (int)$length;
            } else {
                $start  = 0;
                $length = 10;
            }
            //过滤条件
            $orderType = $posts['prj_order_type'];
            switch ($orderType) {
                case 0 :
                    $where['crm_salerecord.is_show'] = array('EQ','0');
                    break;
                case 1 :
                    $where['crm_salerecord.is_ok'] = array('EQ','2');
                    break;
            }
            if($orderType == 0){
                //未审核记录
                $search = $posts['search']['value'];
                if(!empty($search)){
                    if(strlen($search) < 3){
                        $this->ajaxReturn(false);
                    }else{
                        $where1['cusname']  = array('like',"%".$search."%");
                        $where1['salename'] = array('like',"%".$search."%");
                        $where1['sale_commissioner_name'] = array('like',"%".$search."%");
                        $where1['sale_number'] = array('like',"%".$search."%");
                        $where1['_logic']   = "or";
                        $where['_complex']  = $where1;
                        $where['crm_salerecord.yid']     = array('EQ', session('staffId'));
                        $where['crm_salerecord.is_show'] = array('EQ', '0');
                    }
                }else{
                    $where['crm_salerecord.yid']     = array('EQ', session('staffId'));
                    $where['crm_salerecord.is_show'] = array('EQ', '0');
                }
            }else{
                //待收费确认
                $search = $posts['search']['value'];
                if(!empty($search)){
                    if(strlen($search) < 3){
                        $this->ajaxReturn(false);
                    }else{
                        $where1['cusname']  = array('like',"%".$search."%");
                        $where1['salename'] = array('like',"%".$search."%");
                        $where1['sale_commissioner_name'] = array('like',"%".$search."%");
                        $where1['sale_number'] = array('like',"%".$search."%");
                        $where1['_logic']   = "or";
                        $where['_complex']  = $where1;
                        $where['crm_salerecord.yid']     = array('EQ', session('staffId'));
                        $where['crm_salerecord.is_ok']   = array('EQ', '2');
                    }
                }else{
                    $where['crm_salerecord.yid']     = array('EQ', session('staffId'));
                    $where['crm_salerecord.is_ok']   = array('EQ', '2');
                }
            }

            $result = D('salerecord')->getSaleRecord($where,'crm_salerecord.sid,crm_salerecord.sale_number,crm_salerecord.cusname,crm_salerecord.salename,c.start_date,
                    e.name as reperson_question,crm_salerecord.is_over,crm_salerecord.is_repairorder,crm_salerecord.over_time,crm_salerecord.sale_commissioner_name,
                    i.name as is_ok,crm_salerecord.courier_number,crm_salerecord.is_audit,crm_salerecord.is_show',$start,$length,$order,'crm_salerecord.sid');
            //显示的数目信息
            $count = M('salerecord')->where($where)->count();
            $recordsFiltered = $count;
            if (count($result) != 0) {
                foreach($result as $key => $val) {
                    $info[$key]['DT_RowId']                 = $val['sid'];
                    $info[$key]['sale_number']              = $val['sale_number'];
                    $info[$key]['cusname']                  = $val['cusname'];
                    $info[$key]['salename']                 = $val['salename'];
                    $info[$key]['start_date']               = $val['start_date'];
                    $info[$key]['reperson_question']        = $val['reperson_question'];
                    $info[$key]['is_over']                  = $val['is_over'];
                    $info[$key]['is_show']                  = $val['is_show'];
                    $info[$key]['is_audit']                 = $val['is_audit'];
                    $info[$key]['is_repairorder']           = $val['is_repairorder'];
                    $info[$key]['courier_number']           = $val['courier_number'];
                    $info[$key]['over_time']                = $val['over_time'];
                    $info[$key]['sale_commissioner_name']   = $val['sale_commissioner_name'];
                    $info[$key]['is_ok']                    = $val['is_ok'];
                }
            } else {
                $info = '';
            }
            $output = $this->getDataTableOut($draw,$count,$recordsFiltered,$info);
            $this->ajaxReturn($output);
        }else{
            $this->display('showOwnUnrecord');
        }
    }
    //显示所有未审核记录
    public function showAllUnrecord()
    {
        $saleModel = new SalerecordModel();
        if(IS_AJAX){
            $this->posts = I('post.');

            $draw  = $this->posts['draw'];
            $this->sqlCondition = $this->getSqlCondition($this->posts);

            $staffIds = $this->getStaffIds(session('staffId'), 'cus_child_id', "");
            //过滤条件
            $orderType = (int)$this->posts['prj_order_type'];
            $orderOver = (int)$this->posts['prj_order_over'];
            if($orderOver == 0){
                //未审核
                $where['crm_salerecord.is_show'] = array('EQ', '0');
            }else{
                //待收费确认
                $where['crm_salerecord.is_ok']   = array('EQ', '2');
            }
            if($orderType == 0){
                //个人
                $where['crm_salerecord.yid'] = array('EQ', $this->staffId);
            }elseif ($orderType == 1) {
                $where['crm_salerecord.yid'] = array('IN', $staffIds);
            } elseif ($orderType == 2) {
                $staffModel = new AuthRoleModel();
                $this->whereCondition['role_id'] = array('IN', self::SALE_ROLE);
                $this->field = "staff_ids";
                $saleStaffIds = $staffModel->where($this->whereCondition)->field($this->field)->select();
                $saleStaffIdArr = explode(",", getPrjIds($saleStaffIds, "staff_ids"));
                if (!in_array($this->staffId,$saleStaffIdArr)) {
                    $this->output = $this->getDataTableOut($draw,0 , 0, array());
                    $this->ajaxReturn($this->output);
                }
            }
            //显示的数目信息
            $count = $saleModel->where($where)->count();
            $this->field = "crm_salerecord.sid,crm_salerecord.sale_number,crm_salerecord.cusname,crm_salerecord.salename,ifnull(from_unixtime(c.start_date),'') start_t,
                    e.name as reperson_question,crm_salerecord.is_over,crm_salerecord.is_repairorder,ifnull(from_unixtime(crm_salerecord.over_time),'') over_t,crm_salerecord.sale_commissioner_name,
                    i.name as is_ok,crm_salerecord.courier_number,crm_salerecord.is_audit,crm_salerecord.is_show,from_unixtime(repair_date) repair_t, crm_salerecord.sale_type, crm_salerecord.sale_slip";

            $result = $saleModel->getSaleRecord($where, $this->field,$this->sqlCondition['start'],$this->sqlCondition['length'],$this->sqlCondition['order'],"sid");


            if(!empty($this->sqlCondition['search'])){
                if(strlen($this->sqlCondition['search']) < 3){
                    $this->ajaxReturn(false);
                }else{
                    $where['cusname|salename|sale_commissioner_name|sale_number']  = array('like',"%".$this->sqlCondition['search']."%");
                }
            }
            $recordsFiltered = $saleModel->where($where)->count();
            if (count($result) != 0) {
                foreach($result as $key => &$val) {
                   $val['DT_RowId'] = $val['sid'];
                }
            }
            $output = $this->getDataTableOut($draw,$count,$recordsFiltered,$result);
            $this->ajaxReturn($output);
        }else{
            $this->assign([
                "saleType" => SalerecordModel::$saleTypeMap,
            ]);
            $this->display();
        }
    }



    //售后记录分配业务员
    public function assignSale()
    {
        $salerecordModel = new SalerecordModel();
        $roleModel       = new AuthRoleModel();
        $staffModel      = new StaffModel();
        if (IS_POST) {
            $this->posts = I('post.');

            $changeFilter['sid'] = array('EQ', (int)$this->posts['change_id']);
            $saleRecordData = $salerecordModel->getCusSaleBasicInfo($changeFilter, "yid, salename");

            $this->whereCondition['role_id'] = array('IN', self::SALE_ROLE);
            $this->field = "staff_ids";
            $saleStaffIds = $roleModel->where($this->whereCondition)->field($this->field)->select();
            $saleStaffIdArr = explode(",", getPrjIds($saleStaffIds, "staff_ids"));
            if (in_array($this->staffId,  $saleStaffIdArr)) {
                if ($saleRecordData['yid'] == $this->posts['user_id']) {
                    $msg = array(
                        'status' => self::UN_CHANGE_STATUS,
                        'msg'    => "未做修改"
                    );
                    $this->ajaxReturn($msg);
                } else {
                    $staffFilter['id'] = array('EQ', (int)$this->posts['user_id']);
                    $changeData = $staffModel->getOneStaffInfo($staffFilter, "id yid, name salename", "id");
                    $changeData['is_show'] = "0";
                    $changeRst = $salerecordModel->where($changeFilter)->setField($changeData);
                    if ($changeRst !== false) {
                        $msg = array(
                            'status' => self::SUCCESS_STATUS,
                            'msg'    => "修改成功"
                        );
                        $this->ajaxReturn($msg);
                    } else {
                        $msg = array(
                            'status' => self::FAIL_STATUS,
                            'msg'    => "修改失败"
                        );
                        $this->ajaxReturn($msg);
                    }
                }
            } else {
                $msg = array(
                    'status' => self::UN_CHANGE_STATUS,
                    'msg'    => "未做修改（无权限）"
                );
                $this->ajaxReturn($msg);
            }
        } else {
            $sale_id = I('get.sale_id');
            $changeFilter['sid'] = array('EQ', (int)$sale_id);
            $saleData = $salerecordModel->getCusSaleBasicInfo($changeFilter, "yid, salename,cusname,sale_number,sid");
            $this->whereCondition['role_id'] = array('IN', self::CUS_ROLE . "," . self::RESEARCH_ROLE);
            $this->field = "staff_ids";
            $cusStaffArr = $roleModel->where($this->whereCondition)->field($this->field)->select();
            $cusStaffIds['id'] = array('IN', getPrjIds($cusStaffArr, "staff_ids"));
            $cusStaffIds['loginstatus'] = array('NEQ', "1");
            $staffData = $staffModel->where($cusStaffIds)->field("id,name")->select();
            $this->assign(array(
                    'staff'      => $staffData,
                    'saleRecord' => $saleData,
                    'changeName' => session('nickname')
            ));
            $this->display();
        }
    }

    /**
     * 显示最近业务通过的审核
     * @param   $id
     */
    public function showRecentlySaleManRecord()
    {
        if (IS_POST){
            $id = I('post.id');
            $res = M('salerecordchange') -> where(['id' => ['EQ', $id]]) -> save(['is_del' => 1]);
            if ($res === false){
                $this->ajaxReturn([
                    'status' => self::FAIL_STATUS,
                    'msg' => '操作失败'
                ]);
            }else{
                $this->ajaxReturn([
                    'status' => self::SUCCESS_STATUS,
                    'msg' => '操作成功'
                ]);
            }
        }else{
            $map = [
                'record.change_status' => ['IN', self::CHANGE_STATUS_10 . ',' . self::CHANGE_STATUS_11],
                'record.is_del' => ['EQ', 0]
            ];
            $model = M('salerecordchange');
            $res = $model
                -> field("server.sale_number, ifnull(server.repair_date,'wu') repair_date, record.*,record_status.name status_name")
                -> alias('record')
                -> join('left join crm_salerecord as server on record.saleid = server.sid')
                -> join('LEFT JOIN crm_salerecordchange_status record_status on record_status.id = record.change_status')
                -> where($map)
                -> order('record.change_status_time desc')
                -> select();
            foreach ($res as $key => &$value) {
                $value['change_status_time'] = date('Y-m-d H:i:s', $value['change_status_time']);
                $value['repair_date'] = $value['repair_date'] == 'wu' ? "无" :date('Y-m-d H:i:s', $value['repair_date']);
            }
            $this->assign(compact('res'));
            $this->display();
        }
    }

    /**
     * 查看回访调查结果
     */
    public function callbackRes()
    {
        if (IS_POST){
            $map = I('post.');
            $data = [
                'total' => 0,
                'data' => []
            ];
            $map['order'] = implode(' ', $map['order']);
            if ($map['flag'] == 1){
                $map['where']['question_4'] = ['exp', 'is not null'];
                $map['where']['question_4flag'] = ['EQ', '有'];
            }
            $data['data'] = M('cus_callback') -> field('cus_name, u_name, assign_time, question_4flag, question_4tip') -> where($map['where']) -> order($map['order']) -> page($map['page'], $map['pageSize']) -> select();
            $data['total'] = M('cus_callback') -> where($map['where']) -> count();
            $map['where']['question_4'] = ['exp', 'is not null'];
            $map['where']['question_4flag'] = ['EQ', '有'];

            //计算比例
            $a = M('cus_callback') -> where($map['where']) -> order($map['order']) -> count();
            unset($map['where']['question_4flag']);
            $b = M('cus_callback') -> where($map['where']) -> order($map['order']) -> count();
            $data['cus'] = [$a,$b];
            $data['ratio'] = ceil($a / $b * 100) . '%';

            $this->ajaxReturn($data);
        }else{
            $tmp = M() -> query('SELECT assign_time FROM crm_cus_callback GROUP BY assign_time');
            $timeBatch = [];
            foreach ($tmp as $key => $value) {
                $timeBatch[$key]['timestamp'] = $value['assign_time'];
                $timeBatch[$key]['time'] = date('Y-m-d H:i:s', $value['assign_time']);
            }
            $this->assign(compact('timeBatch'));
            $this->display();
        }
    }

    public function exportCallback()
    {
        if (IS_POST) {
            $map = I('post.');
            $map['order'] = implode(' ', $map['order']);
            if ($map['flag'] == 1){
                $map['where']['question_4flag'] = ['EQ', '有'];
            }
            $data = M('cus_callback')
                -> field("cus_name, ifnull(u_name,'无') u_name, from_unixtime(assign_time) assign_date, question_4flag, question_4tip")
                -> where($map['where'])
                -> order($map['order'])
                -> select();

            if(empty($data)){
                $this->returnAjaxMsg("当前筛选条件下无数据，请修改！",400);
            }

            Vendor('PHPExcel.PHPExcel');//引入类
            Vendor('PHPExcel.PHPExcel_IOFactory');//引入类
//        Vendor('PHPExcel.Writer.Excel5');  // 后缀是xls
            Vendor('PHPExcel.Writer.Excel2007'); // 后缀是xlsx

            $objPHPExcel = new \PHPExcel();                        //初始化PHPExcel(),不使用模板

            $objActSheet = $objPHPExcel->getActiveSheet();

            //这里是设置单元格的内容
            $objActSheet->setCellValue("A1","客户名");
            $objActSheet->setCellValue("B1",'业务员');
            $objActSheet->setCellValue("C1","客户电话回访时间");
//            $objActSheet->setCellValue("D1","客户名称");
            $objActSheet->setCellValue("D1","二次返修情况");
            $objActSheet->setCellValue("E1","备注");

            $i = 2;
            foreach ($data as $k=>$v){
                //这里是设置单元格的内容
                $objActSheet->setCellValue("A".$i,$v['cus_name']);
                $objActSheet->setCellValue("B".$i,$v['u_name']);
                $objActSheet->setCellValue("C".$i, $v['assign_date']);
//                $objActSheet->setCellValue("D".$i,$v['cusname']);
                $objActSheet->setCellValue("D".$i,$v['question_4flag']);
                $objActSheet->setCellValue("E".$i,$v['question_4tip']);
                $i++;
            }

            $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
            $fileName = "二次返修". '_' . date('Ymd') . '.xlsx';
            // $fileName = iconv('utf-8', 'gb2312', $fileName);//文件名称

            // 1.保存至本地Excel表格
            $rootPath = WORKING_PATH . UPLOAD_ROOT_PATH . "/excel/";
            if (!file_exists($rootPath)) {
                mkdir($rootPath, 0777,true);
            }
            $objWriter->save($rootPath . $fileName);

            $this->returnAjaxMsg("下载成功",200,[
                'file_url' => UPLOAD_ROOT_PATH . "/excel/" . $fileName
            ]);
        }
    }
}
                    
            
        