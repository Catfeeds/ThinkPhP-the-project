<?php
/**
 * Created by PhpStorm.
 * User: MaXu
 * Date: 17-5-16
 * Time: 上午10:21
 */

namespace Dwin\Controller;

use Dwin\Model\OnlineserviceModel;
use Dwin\Model\StaffModel;
use Think\Controller;
// 电话客服
class OnlineServiceController extends CommonController
{
    /* 具有客服权限的职位id*/
    const ONLINE_ROLE = "7,11,22,28,29";
    const FAIL_STATUS = -1;
    const SUCCESS_STATUS = 1;
    protected $onlineServiceId;

    public function _initialize()
    {
        parent::_initialize();
        $this->onlineServiceId = "22,29";
    }

    /**
     * 35 客服记录录入 showCustomer showOnlineServiceHisList queryCustomer addServiceOk
     */

    /**
     * 获取客户信息并显示
     * post cusName:以输入的客户名检索客户
     * 返回符合条件的客户
    */
    public function showCustomer()
    {
        if (IS_POST) {
            $model = M('customer');
            $posts = I('post.');
            if (inject_filter($posts['cusName']) != "") {
                if (stripos($posts['cusName'], 'www') !== false) {
                    $map['website'] = array('EQ', strtolower(inject_filter($posts['cusName'])));
                    $data = $model->where($map)
                        ->join('LEFT JOIN `crm_industry` ind ON ind.id = crm_customer.ctype')
                        ->field('crm_customer.cid,crm_customer.cname,crm_customer.addtime,ind.name indusname,uid,
                                    (SELECT count(id) FROM crm_onlineservice AS ss WHERE ss.customer_id = crm_customer.cid) as counts')
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
                } elseif (is_numeric(inject_filter($posts['cusName'])) == true) {
                    $map['cphonenumber'] = array('EQ', strtolower(inject_filter($posts['cusName'])));
                    $data = $model->where($map)
                        ->join('LEFT JOIN `crm_industry` ind ON ind.id = crm_customer.ctype')
                        ->field('crm_customer.cid,crm_customer.cname,crm_customer.addtime,ind.name indusname,uid,
                                    (SELECT count(id) FROM crm_onlineservice AS ss WHERE ss.customer_id = crm_customer.cid) as counts')
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
                    /*require_once('sphinxapi.php');
                    $c = new \SphinxClient();
                    $c->setServer('localhost', 9312);
                    $c->setMatchMode(SPH_MATCH_ALL);
                    $num = mb_strlen($posts['cusName'], 'utf8');
                    if ($num <= 1) {
                        $this->ajaxReturn(false);die;
                    }
                    $cusFilter = array(
			'科技发展有限公司','科技有限公司','技术有限公司','实业有限公司','有限责任公司','电子有限公司','股份有限公司','有限公司','公司','研究所','研究院','市','省',
	                '北京', '上海', '天津', '重庆', '河北', '山西', '内蒙古', '辽宁', '吉林', '黑龙江','常州','威海',
        	        '江苏', '浙江', '安徽', '福建', '江西', '山东', '河南', '湖北', '湖南', '广东', '广州',
            		'广西', '海南', '四川', '贵州', '云南', '西藏', '陕西', '甘肃', '青海', '宁夏','新疆','东营',
           		'石家庄', '张家口', '承德', '唐山', '秦皇岛', '沧州', '廊坊', '保定', '衡水', '邢台','邯郸','太原',
        		'大同', '朔州', '忻州', '阳泉', '晋中', '吕梁', '长治', '临汾', '晋城', '运城','呼和浩特','呼伦贝尔',
   		        '通辽', '赤峰', '包头', '鄂尔多斯', '哈尔滨', '大庆', '长春', '吉林', '沈阳', '本溪','大连','南京',
        		'泰州', '杭州', '扬州', '镇江', '南通', '无锡', '苏州', '宁波', '温州', '合肥','福州','莆田',
        	        '厦门', '济南', '烟台', '青岛', '郑州', '洛阳', '武汉', '长沙', '佛山', '东莞','深圳','珠海',
        	        '三亚', '海口', '成都', '贵阳', '拉萨', '丽江', '昆明', '兰州', '天水', '乌鲁木齐','西宁','中卫',
    		        '(',')','（','）','select','insert','update','delete','and','or','where','join','*','=','union','into','load_file','outfile','/','\''
                    );
                    $cusKey = str_replace($cusFilter, "", strtolower($posts['cusName']));
                    if (mb_strlen($cusKey) <= 1) {
                        $this->ajaxReturn(false);die;
                    }

                    $data1 = $c->Query($cusKey, "dwin,delta");
                    $index = array_keys($data1['matches']);
                    $index_str = implode(',', $index);
                    if ($index_str == null) {
                        $this->ajaxReturn(false);die;
                    }*/
                    $map['cname'] = array('like', "%" . $posts['cusName'] . "%");
                    //$map['cid'] = array('IN', $index_str);
                    $data = M('customer')->where($map)
                        ->join('LEFT JOIN `crm_industry` ind ON ind.id = crm_customer.ctype')
                        ->field('crm_customer.cid,crm_customer.cname,crm_customer.addtime,ind.name indusname,uid,
                                    (SELECT count(id) FROM crm_onlineservice AS ss WHERE ss.customer_id = crm_customer.cid) as counts')
                        ->limit(0,20)
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
                    //$c->close();

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
    /**
     * 2 点击某客户查看以往客服记录
     * cid get传值
     * 返回客户的客服记录
     *
    */
    public function showOnlineServiceHisList()
    {
        $cid   = inject_id_filter(I('get.cid'));

        $model = M('onlineservice');
        $where['customer_id'] = array('EQ', $cid);
        $where['crm_onlineservice.addtime'] = array('GT', $this->timeLimit);
        $data  = $model
            ->where($where)
            ->join('LEFT JOIN crm_staff AS c ON crm_onlineservice.server_id = c.id')
            ->field('crm_onlineservice.*,c.name AS pname')
            ->select();

        $cname = M('customer')->field('cname')->find($cid);

        $this->assign(array(
            'data'    => $data,
            'cid'     => $cid,
            'cusName' => $cname
        ));
        $this->display();
    }
    /**
     * 3 处理添加数据的方法
     * post 传值
     * 联系记录的内容（客户问题、解决方式）
     * @return int 返回状态码 2 成功 1 失败
    */
    public function addServiceOk()
    {
        if(IS_POST) {
            $post = I('post.');
            $post['addtime'] = time();
            $post['server_id'] = (int)session('staffId');
            $post['content']   = nl2br(inject_filter($post['content']));
            $post['answercontent'] = nl2br(inject_filter($post['answercontent']));
            $model = M('onlineservice');
            $fin = $model->create($post);
            $rst = $model->add($fin);
            if ($rst) {
                $this->ajaxReturn(2);
            } else {
                $this->ajaxReturn(1);
            }
        }
    }


    public function queryCustomer()
    {
        $this->display('showCustomer');
    }

    /**
     * 36 客服记录查看  showServiceList
     * @return array $data 权限下的客户列表
     */
    /**
     * 获取业务员对应的客服记录
     * 没返回值，直接分配变量到模板中
     * 根据权限获取，得到满足条件的记录assign给模板
    */
    public function showServiceList()
    {
        $staffIds = $this->getStaffIds(session('staffId'), 'online_child_id', "");
        $staffIds = !empty($staffIds) ? $staffIds . "," . session('staffId') : session('staffId');

        $where['server_id'] = array('IN', $staffIds);

        $where['crm_onlineservice.addtime'] = array('GT', $this->timeLimit);
        $this->field = "crm_onlineservice.*, s.name AS sname,c.cname,ind.name indusname,d.name AS dname";
        $data = D('onlineservice')->getServiceList($where, $this->field, 0, 1000, 'crm_onlineservice.addtime');
        $this->assign('data', $data);
        $this->display();
    }

    /**
     * @name getCallbackCusId
     * @abstract 获取有联系记录的客户
     * 限制长度是因为抽取客户不是取所有
     * $param array $config 一维数组：包括where条件和查询长度
     * @return array $rst 二维数组，满足条件的客户数据
     *
    */
    protected function getCallbackCusId($config)
    {
        $rst = M('customer')
            ->where($config['map'])
            ->field('crm_customer.cid cus_id,cname cus_name,uid, s.name u_name')
            ->join('LEFT JOIN crm_staff s ON s.id = uid')
            ->join('INNER JOIN crm_contactrecord b ON b.customerid = crm_customer.cid')
            ->group('crm_customer.cid')
            ->limit(0, $config['length'])
            ->order('callback_times asc, max_contact_time desc')
            ->select();
        return $rst;
    }

    /**
     * @name assignServiceId
     * 将抽取的客户分配到客户工程师
     * 返回状态码
     * 200 成功 400分配出错 401 分配数量有误
    */
    protected function assignServiceId()
    {
        M()->startTrans();
        $time = time();
        // 分配客服专员
        // 获取职位为客服的id role_id:22,29
        $map['role_id'] = array('in', $this->onlineServiceId);
        $onlineData = M('auth_role')->where($map)->field('role_id,staff_ids')->select();
        $onlineIds = getPrjIds($onlineData, "staff_ids");
        $onlineIdArray = explode(",", $onlineIds);// 一维数组：客服专员id
        // 计算客服数量x
        $x = count($onlineIdArray);
        // 计算需要分配的客户数量y
        $callbackFilter['callback_status'] = array('EQ', '1');
        $y = M('cus_callback')->where($callbackFilter)->count('id');
        // $cusIds = M('cus_callback')->where($callbackFilter)->field('cus_id')->select();
        // 分配客户方式：平均分到的客户:n = y/x 需要取整数
        $n = floor($y / $x);
        // 按照顺序，一次更新n条数据,写循环 i最大为客服专员数量x
        for($j = 0;$j< $x; $j++) {
            $idAddOnlineId = M('cus_callback')->where($callbackFilter)->field('id')->limit(0, $n)->select();
            $filter[$j]['id'] = array('IN', getPrjIds($idAddOnlineId, "id"));
            $setData[$j]['online_id'] = $onlineIdArray[$j];
            $setData[$j]['callback_status'] = '2';
            $setData[$j]['assign_time'] = $time;
            $updCallback[$j] = M()->table('crm_cus_callback')->where($filter[$j])->setField($setData[$j]);
        }
        $s = $y - $n * $x;
        for ($p = 0; $p < $s; $p++) {
            $idAddOnlineId = M('cus_callback')->where($callbackFilter)->field('id')->find();
            $filter[$p]['id'] = array('IN', $idAddOnlineId['id']);
            $setData[$p]['online_id'] = $onlineIdArray[$p];
            $setData[$p]['callback_status'] = '2';
            $setData[$p]['assign_time'] = $time;
            $updCallback[$j] = M()->table('crm_cus_callback')->where($filter[$p])->setField($setData[$p]);
        }
        if(isset($updCallback)) {
            if (!in_array(0, $updCallback) && !in_array(false, $updCallback)) {
                M()->commit();
                $msg['status'] = 200;
                $msg['msg']  = "本次抽取客户已完毕。";
            } else {
                M()->rollback();
                $msg['status'] = 400;
                $msg['msg']  = "分配失败，有部分数据分配过程中出错";
            }
        } else {
            M()->rollback();
            $msg['status'] = 401;
            $msg['msg']  = "分配失败，分配的客服专员数或系统调查表数量有误";
        }
        return $msg;
    }

    /**
     * 获取上一期次客户抽取结果，是否可以进行下一次抽取
     * 返回结果（ 1 :上一批回访完毕）2 ：未完
    */
    protected function checkCallStatus()
    {
        $map['callback_status'] = array('EQ', "2");
        $rst = M('cus_callback')->where($map)->count('id');
        $flag['status'] = ($rst == 0) ? 1 : 2;
        $flag['data'] = $rst;
        return $flag;
    }

    /**
     * 客服工程师抽取客户方法
     * 优先获取回访次数少的、客户最近联系过的
     *
    */
    public function randCusByOnline()
    {
        /**
         *
        */
        if (IS_POST) {
            $postId = I('post.callId');
            $lockMap['id'] = array('EQ', $postId);
            $setData1['callback_status'] = "5";
            $setData1['callback_fail_reason'] = I('post.textContent');
            $RES = M('cus_callback')->where($lockMap)->setField($setData1);
            if ($RES !== false) {
                $filter['uid']     = array('neq', "");
                $filter['cus_pid'] = array('exp', 'is null or cus_pid=""');
                $filter['uid']     = array('neq', session('staffId'));
                $rst = M('customer')
                    ->where($filter)
                    ->field('crm_customer.cid cus_id,cname cus_name, uid, s.name u_name')
                    ->join('LEFT JOIN crm_staff s ON s.id = uid')
                    ->join('INNER JOIN crm_contactrecord b ON b.customerid = crm_customer.cid')
                    ->group('crm_customer.cid')
                    ->order('callback_times asc, max_contact_time desc')
                    ->find();
                M()->startTrans();
                $data['callback_flag'] = "回访中";
                $data['callback_times'] = array('exp', 'callback_times+1');
                // $cusIds = getPrjIds($rst, "cus_id");

                $condition['cid'] = array ('eq', $rst['cus_id']);
                $rst_updCus = M()->table('crm_customer')->where($condition)->setField($data);
                if ($rst_updCus) {
                    // 插入数据到回访表中
                    // 回访表执行insert语句，将取出的id存进回访表中
                    $time = M('cus_callback')->max('assign_time');
                    $rst['online_id'] = session('staffId');
                    $rst['callback_status'] = '2';
                    $rst['assign_time'] = $time;
                    $rst_add = M()->table('crm_cus_callback')->add($rst);
                    if ($rst_add) {
                        M()->commit();
                        $delTime['staff_id'] = array('EQ', session('staffId'));
                        $t = M('cus_callback_timerecord')->where($delTime)->field('static_id')->order('click_callback_time desc')->find();
                        $timeLimit['static_id'] = array('EQ', $t['static_id']);
                        M('cus_callback_timerecord')->where($timeLimit)->delete();
                        $msg['status'] = 200;
                        $msg['msg'] = "已重新抽取客户";
                    } else {
                        M()->rollback();
                        $msg['status'] = 401;
                        $msg['msg']  = "回访表数据添加失败，事务回滚";
                    }
                } else {
                    M()->rollback();
                    $msg['status'] = 401;
                    $msg['msg']  = "客户表数据更新失败，事务回滚";
                }
            } else {
                $msg['status'] = 403;
                $msg['status'] = "错误信息上报失败，未能重新抽取客户";
            }
        } else {
            $msg['status'] = 404;
            $msg['msg'] = "未得到参数回传，重新抽取失败";
        }

        $this->ajaxReturn($msg);
    }

    /**
     * 抽取客户方法，返回是否成功信息
     * 抽取客户采用两种方式：
     * 1 一种是在有效客户中，随机的取总数六分之一的客户
     * 2 在有效客户中，优先取未进行回访的客户。
     * 目前程序采用方法 2 后续有优化算法可以调整
     *
    */
    protected function randCusCallback()
    {
        $filter['uid'] = array('neq', "");
        $filter['cus_pid'] = array('exp', 'is null or cus_pid=""');
        // 有效客户数量
        $rst_total = M('customer')->field('crm_customer.cid')->where($filter)
            ->join('INNER JOIN crm_contactrecord b ON b.customerid = crm_customer.cid')
            ->group('crm_customer.cid')
            ->select();
        $totalArray = array_column($rst_total, 'cid', 'cid');
        $total = count($rst_total);// 所有客户
        // 需要抽取的客户数量
        $p = ceil($total / 6);


        // 方法1 ：完全随机，回访次数少的可能抽不到，重复抽取
        /*
        $randomIds = implode(",", array_rand($totalArray, $p));
        $randomIdFilter['cid'] = array('IN', $randomIds);
        $rst = M('customer')
            ->where($randomIdFilter)
            ->field('crm_customer.cid cus_id,cname cus_name, uid, s.name u_name')
            ->join('LEFT JOIN crm_staff s ON s.id = uid')
            ->select();

        $cusIds = $randomIds;
        */
        // 方法2 ：抽取客户数据，按回访次数、回访状态、联系记录时间排序
        $config = array(
            'map' => $filter,
            'length' => $p
        );
        $rst = $this->getCallbackCusId($config);

        $cusIds = getPrjIds($rst, "cus_id");
        // 开启事务修改客户表中客户回访状态 回访表添加数据
        M()->startTrans();
        $data['callback_flag'] = "回访中";
        $data['callback_times'] = array('exp', 'callback_times+1');


        $condition['cid'] = array ('in', $cusIds);
        $rst_updCus = M()->table('crm_customer')->where($condition)->setField($data);
        if ($rst_updCus) {
            // 插入数据到回访表中
            // 回访表执行insert语句，将取出的id存进回访表中
            foreach($rst as &$val) {
                $val['callback_status'] = 1;
            }
            $rst_add = M()->table('crm_cus_callback')->addAll($rst);
            if ($rst_add) {
                M()->commit();
                $msg = $this->assignServiceId();
            } else {
                M()->rollback();
                $msg['status'] = 401;
                $msg['msg']  = "回访表数据添加失败，事务回滚";
            }
        } else {
            M()->rollback();
            $msg['status'] = 401;
            $msg['msg']  = "客户表数据更新失败，事务回滚";
        }
        return $msg;
    }

    /**
     * 回访抽取按钮，点击触发相应操作
     * 1 post.flag == 1时
     * 返回是否能够进行抽取客户操作
     * return 返回值：200 进行自动抽取，400 需要确认
     * 2 post.flag == 2时 进行客户抽取，有未完成的也继续抽取
     * flag == 2 时的处理流程：
     * 如果没有未完成的回访，直接抽取客户randCusCallback方法
     * 如果有未完成的回访，客户表回访状态修改：回访次数 -1 回访完毕；把回访表的所有状态为3的改为4
     * return 返回值：200 成功 400 401 失败，具体信息$msg['msg']
     * checkCallStatus=>200:randCusCallback => getCallbackCusId => assignServiceId => return result
     *
     *                =>400: 修改客户回访数据等 =>200: randCusCallback => getCallbackCusId => assignServiceId => return result
     *                                       => 400: return false msg
     *
    */
    public function getCusCallback()
    {
        /*$data['callback_flag'] = "未回访";
        $data['callback_times'] = 0;
        $condition['uid'] = array('neq', "");
        M('customer')->where($condition)->setField($data);die;*/

        if (IS_POST) {
            $methodFlag = I('post.flag');
            // 尝试获取回访进度信息
            $flag = $this->checkCallStatus();
            if ($methodFlag == 1) {
                if ($flag['status'] == 1) {
                    $msg['status'] = 200;
                    $msg['msg'] = "上一批次已经回访完毕，系统将进行客户抽取";
                    $this->ajaxReturn($msg);
                } else {
                    $msg['status'] = 400;
                    $msg['msg'] = "上一批次统计还未回访完毕，还剩" . $flag['data'] . "家客户未进行回访，是否结束上批次回访，重新抽取客户?";
                    $this->ajaxReturn($msg);
                }
            } elseif ($methodFlag == 2) {
                // 回访抽取逻辑
                if ($flag['status'] == 1) {
                    $msg = $this->randCusCallback();
                } else {
                    // 状态2的修改去除，状态3的改状态4；状态2的客户表统计次数减1
                    $delFilter['callback_status'] = array('eq', "2");
                    $delCallbackId = M('cus_callback')->where($delFilter)->field('cus_id')->select();
                    $delIds = getPrjIds($delCallbackId, "cus_id");
                    M()->startTrans();

                    $delRes = M()->table('crm_cus_callback')->where($delFilter)->delete();
                    if ($delRes !== false) {
                        $updData['callback_times'] = array('exp', 'callback_times-1');
                        $updData['callback_flag']  =  "已回访";
                        $updCondition['cid'] = array('IN', $delIds);
                        $updRes = M()->table('crm_customer')->where($updCondition)->setField($updData);
                        if ($updRes !== false) {
                            M()->commit();
                            // 状态为3（本次回访结束标志）的修改为4（回访终结标志）
                            $stopFilter['callback_status'] = array('eq', "3");
                            $stopData['callback_status'] = "4";
                            $stopRes = M('cus_callback')->where($stopFilter)->setField($stopData);
                            if ($stopRes !== false) {
                                // 执行抽取客户逻辑
                                $msg = $this->randCusCallback();
                            } else {
                                $msg['status'] = 400;
                                $msg['msg'] = "终止以往回访安排失败，未进行客户抽取";
                            }
                        } else {
                            M()->rollback();
                            $msg['status'] = 400;
                            $msg['msg']    = "更新客户表数据失败，事务回滚";
                        }
                    } else {
                        M()->rollback();
                        $msg['status'] = 400;
                        $msg['msg']    = "删除未回访客户分配记录失败，事务回滚";
                    }
                }
                $this->ajaxReturn($msg);
            }
        }
    }

    protected function getSubStaff($staffFilter)
    {
        $staffs = M('staff')->where($staffFilter)->field('id,name,online_child_id')->select();
        foreach ($staffs as &$val) {
            if (empty($val['online_child_id'])) {
                for ($i = 0; $i < count($staffs); $i++) {
                    if(strstr($staffs[$i]['online_child_id'], $val['id'])){
                        $val['pid'] = $staffs[$i]['id'];
                    }
                }
            } else {
                $val['pid'] = 0;
            }
        }
        return $staffs = getTree($staffs, 0, 0, 'pid');
    }
    /**
     * 获取客服职位下的staffId 用于分配客户回访
    */
    protected function getOnlineServiceStaffIds()
    {
        $map['role_id'] = array('in', $this->onlineServiceId);
        $onlineData = M('auth_role')->where($map)->field('role_id,staff_ids')->select();
        $onlineIds = getPrjIds($onlineData, "staff_ids");
        return $onlineIds;
    }

    /**
     * 回访进度查看
     * 根据权限查看回访进度数据
     * post 传至时 作为datatables的数据接口
     * return 标准datatables 的json数组
     * 非post 加载页面。
    */
    public function showCallbackList()
    {
        if (IS_POST) {
            // 根据组织架构查看内容。
            $this->posts = I('post.');

            //获取Datatables发送的参数 必要
            $draw = $this->posts['draw'];

            $this->sqlCondition = $this->getSqlCondition($this->posts);

            $callbackType = empty($this->posts['type_limit']) ? 2 : $this->posts['type_limit'];
            $cusUIdLimit  = empty($this->posts['uid_limit']) ? $this->getStaffIds(session('staffId'), 'online_child_id', "")  : $this->posts['uid_limit'];

            $where = array(
                'online_id' => array('in', $cusUIdLimit),
                'callback_status' => array('in', $callbackType)
            );
            $count = M('cus_callback')->where($where)->count('id');
            if (!empty($this->sqlCondition['search'])) {
                $where['cus_name|online_name'] = array('like', "%" . $this->sqlCondition['search'] . "%");
            }
            $recordsFiltered = M('cus_callback')->where($where)->count('id');
            $this->field = "crm_cus_callback.id, cus_id, cus_name, uid,a.name u_name, online_id, b.name online_name, from_unnixtime(assign_time) callback_time, callback_status, satisfied_flag";
            $data = D('cus_callback')->getCusCallbackData($this->field, $where, $this->sqlCondition['order'], $this->sqlCondition['start'], $this->sqlCondition['length']);

            if (count($data) != 0) {
                foreach($data as $key => &$val) {
                    $val['DT_RowId']      = $val['id'];
                    $val['DT_RowClass']   = 'gradeX';
                    switch ($val['callback_status']) {
                        case 1 :
                            $val['callback_flag'] = "尚未分配给客服进行回访";
                            break;
                        case 2 :
                            $val['callback_flag'] = "已分配回访，回访中";
                            break;
                        case 3 :
                            $val['callback_flag'] = "已分配回访，本次回访结束";
                            break;
                        case 4 :
                            $val['callback_flag'] = "已完结回访记录";
                            break;
                        default :
                            $val['callback_flag'] = "回访进度未知，联系管理";
                    }
                }
            }
            $this->output = $this->getDataTableOut($draw, $count, $recordsFiltered, $data);

            $this->ajaxReturn($this->output);
        } else {
            $staffIds = $this->getStaffIds(session('staffId'), 'online_child_id', "");
            //dump($staffIds);
            // 判断当前是否是客服专员
            $onlineIds = $this->getRoleStaffIds($this->onlineServiceId);
            $onlineArr = explode(",", $onlineIds);
            if (in_array(session('staffId'), $onlineArr)) {
                // 是客服专员
                if (empty($staffIds)) {
                    $staffs = M('staff')->field('id,name')->find(session('staffId'));
                    $staffs['pid']   = 0;
                    $staffs['level'] = 0;
                } else {
                    $staffIds = $staffIds . "," . session('staffId');
                    $staffFilter['id'] = array('IN', $staffIds);
                    $staffs   = $this->getSubStaff($staffFilter);
                }
            } else {
                // 不是客服专员
                if (!empty($staffIds)) {
                    $staffFilter['id'] = array('IN', $staffIds);
                    $staffs = $this->getSubStaff($staffFilter);
                }
            }
            //dump($ownId);
            $this->assign('data', $staffs);
            $this->display();
        }
    }


    /**
     * 客户回访页面点击获取客户信息
    */
    public function getCusMsg()
    {
        /**
         * a 判断是否为本次回访中的调查人员  =》 不是返回信息 是则继续
         * b 判断是否还有待回访的客户       =》 没有返回信息 是则继续
         * c 抽取一个客户的信息 =》 返回给前端
        */
        $limit = array(
            'online_id' => session('staffId'),
            'callback_status' => array('IN','2,3')
        );
        // a
        $callbackConfig = M('cus_callback')->where($limit)->field('cus_id')->find();
        if ($callbackConfig) {
            $limit['callback_status'] = array('eq', '2');
            // b
            $callbackCus = M('cus_callback')->where($limit)->field('cus_id')->find();
            if ($callbackCus) {
                // c
                $map['cid'] = array('eq', $callbackCus['cus_id']);
                $record['customerid'] = array('eq', $callbackCus['cus_id']);

                $phoneMsg  = M('customer')->where($map)->field('phonenumber, phonename')->find();
                $recordMsg = M('customerrecord')->where($record)
                    ->join('crm_customer AS cus ON cus.cid = customerid')
                    ->join('crm_staff AS sta ON sta.id = picid')
                    ->field('theme, content, ctime,sta.name pname, ctype, picid, posttime')
                    ->limit(0, 5)
                    ->order('posttime desc')
                    ->select();
                foreach ($recordMsg as &$val) {
                    $val['posttime'] = date('Y-m-d H:i:s', $val['posttime']);
                    switch ($val['ctype']) {
                        case 1 :
                            $val['ctype'] = "电话";
                            break;
                        case 2 :
                            $val['ctype'] = "拜访";
                            break;
                        case 3 :
                            $val['ctype'] = "会议";
                            break;
                        case 4 :
                            $val['ctype'] = "即时消息(qq、微信等)";
                            break;
                        case 5 :
                            $val['ctype'] = "邮件";
                            break;
                        case 6 :
                            $val['ctype'] = "其他";
                            break;
                        case 7 :
                            $val['ctype'] = "高管约谈";
                            break;
                    }
                }
                $msg['status']  = 200;
                $msg['message'] = "进入客户回访调查流程";
                $msg['data'] = array(
                    'contact' => $phoneMsg,
                    'record'  => $recordMsg
                );
            } else {
                $msg = array(
                    'status' => 400,
                    'msg'    => "您本次回访任务已完成，没有未回访客户"
                );
            }
        } else {
            $msg = array(
                'status' => 300,
                'msg'    => "您不在本次回访任务的客服专员名单中，如有问题，请联系管理员"
            );
        }

        if ($callbackConfig['callback_status'] == 2) {
            // 回访中，检测id的负责人
            if ($callbackConfig['online_id'] == session('staffId')) {
                // 负责人，查询客户信息
                $map['cid'] = array('eq', $callbackConfig['cus_id']);
                $record['customerid'] = array('eq', $callbackConfig['cus_id']);
                $phoneMsg = M('customer')->where($map)->field('phonenumber, phonename')->find();
                $recordMsg = M('customerrecord')->where($record)
                    ->join('crm_customer AS cus ON cus.cid = customerid')
                    ->join('crm_staff AS sta ON sta.id = picid')
                    ->field('theme, content, ctime,sta.name pname, ctype, picid, posttime')
                    ->limit(0, 5)
                    ->order('posttime desc')
                    ->select();

                foreach ($recordMsg as &$val) {
                    $val['posttime'] = date('Y-m-d H:i:s',$val['posttime']);
                    switch ($val['ctype']) {
                        case 1 :
                            $val['ctype'] = "电话";
                            break;
                        case 2 :
                            $val['ctype'] = "拜访";
                            break;
                        case 3 :
                            $val['ctype'] = "会议";
                            break;
                        case 4 :
                            $val['ctype'] = "即时消息(qq、微信等)";
                            break;
                        case 5 :
                            $val['ctype'] = "邮件";
                            break;
                        case 6 :
                            $val['ctype'] = "其他";
                            break;
                        case 7 :
                            $val['ctype'] = "高管约谈";
                            break;
                    }
                }
                $msg['status'] = 200;
                $msg['message'] = "进入客户回访调查流程";
                $msg['data'] = array(
                    'contact' => $phoneMsg,
                    'record'  => $recordMsg
                );
            } else {
                $msg['status'] = 500;
                $msg['message'] = "非负责人，不能编辑调查表信息";
            }
            $this->ajaxReturn($msg);
        } else {
            // 回访状态已经完结，不再请求获取客户信息，只获取调查问卷信息。
            // @todo
        }
    }

    /**
     * @name getCallbackNum
     * @abstract 获取回访的数量,包含已回访、未回访、总数
     * @return array $data
     *
    */
    protected function getCallbackNum()
    {
        $assignTime = M('cus_callback')->field('assign_time')->order('assign_time desc')->find();
        $countFilter['online_id'] = array('EQ', session('staffId'));
        $countFilter['assign_time'] = array('EQ', $assignTime['assign_time']);
        $count1 = M('cus_callback')->where($countFilter)->count('id');
        $countFilter['callback_status'] = array('EQ', '2');
        $count2 = M('cus_callback')->where($countFilter)->count('id');
        $countFilter['callback_status'] = array('EQ', '3');
        $count3 = M('cus_callback')->where($countFilter)->count('id');
        $data = array(
            'total' => $count1,
            'none'  => $count2,
            'done'  => $count3
        );
        return $data;
    }

    /**
     * $name getCallbackCustomer
     * @abstract 抽取客户
     * @return array $msg 返回状态吗和状态信息
    */
    public function getCallbackCustomer()
    {
        if (IS_POST) {
            /**
             * a 判断是否为本次回访中的调查人员 =》 不是返回信息 是则继续
             * b 判断是否还有待回访的客户       =》 没有返回信息 是则继续
             * c 抽取一个客户的信息 =》 返回给前端
             */
            $limit = array(
                'online_id' => session('staffId'),
                'callback_status' => array('IN', '2,3')
            );
            // a
            $callbackConfig = M('cus_callback')->where($limit)->field('cus_id')->find();
            if ($callbackConfig) {
                $limit['callback_status'] = array('EQ', '2');
                // b
                $callbackCus = M('cus_callback')->where($limit)->field('id, cus_id, cus_name')->find();
                if ($callbackCus) {
                    $timeLimit['staff_id'] = session('staffId');
                    $timedata = M('cus_callback_timerecord')->where($timeLimit)->field('click_callback_time record_time')->order('record_time desc')->find();
                    $timeSetData = array(
                        'staff_id'   => session('staffId'),
                        'staff_name' => session('nickname'),
                        'click_callback_time' => time()
                    );
                    if ($timedata) {
                        $tinow = time();
                        $limitTime = $tinow - $timedata['record_time'];
                        if (($limitTime < 300)) {
                            $msg['status'] = 100;
                            $msg['msg']    = "距上次抽取客户回访还没到5分钟";
                            $msg['data']   = 300 - $limitTime;
                            $this->ajaxReturn($msg);die;
                        }
                    }
                    M('cus_callback_timerecord')->add($timeSetData);
                    // c
                    $map['cid'] = array('eq', $callbackCus['cus_id']);
                    $record['customerid'] = array('eq', $callbackCus['cus_id']);
                    $phoneMsg1  = M('customer')->where($map)->field('cphonenumber phone, cphonename name, uid')->find();
                    $contactFilter['cusid'] = array('eq', $callbackCus['cus_id']);
                    $phoneMsg = M('cuscontacter')->where($contactFilter)->field('id, name, phone, position')->select();
                    $phoneMsg[] = $phoneMsg1;
                    if ($phoneMsg['uid'] == session('staffId')) {
                        // 负责人与调查人一致
                        /**
                         * 修改负责人给另外的客服专员
                        */
                        // 获取客户专员人员列表
                        $map['role_id'] = array('in', $this->onlineServiceId);
                        $onlineData = M('auth_role')->where($map)->field('role_id,staff_ids')->select();
                        $onlineIdArray = explode(",", getPrjIds($onlineData, "staff_ids"));// 一维数组：客服专员id
                        $own = array(
                            0 => session('staffId')
                        );
                        // 分配给哪位客服 $changedId
                        $changedId = array_rand(array_diff($onlineIdArray, $own));

                        // 执行修改并从$changeId中抽取一家客户，改变online_id;
                        M()->startTrans();
                        $changeData['online_id'] = $changedId;
                        $changeCondition['id']     = array('eq', $callbackCus['id']);
                        $rst = M()->table('crm_cus_callback')->where($changeCondition)->setField($changeData);
                        if ($rst) {
                            $limits['uid']             = array('neq', session('staffId'));
                            $limits['online_id']       = array('eq', $changedId);
                            $limits['callback_status'] = array('eq', '2');
                            $callbackCusChange = M('cus_callback')->where($limits)->field('id, cus_id, cus_name')->find();
                            $changeConditioin2['id']  = $callbackCusChange['id'];
                            $changeData2['online_id'] = session('staffId');
                            $res = M()->table('crm_cus_callback')->where($changeConditioin2)->setField($changeData2);
                            if ($res) {
                                M()->commit();
                                $record['customerid'] = array('eq', $callbackCusChange['cus_id']);
                                $phoneMsg1  = M('customer')->where($map)->field('cphonenumber phone, cphonename name, uid')->find();
                                $contactFilter['cusid'] = array('eq', $callbackCusChange['cus_id']);
                                $phoneMsg = M('cuscontacter')->where($contactFilter)->field('id, name, phone, position')->select();
                                $phoneMsg[] = $phoneMsg1;
                                $recordMsg = M('contactrecord')->where($record)
                                    ->join('crm_customer AS cus ON cus.cid = customerid')
                                    ->join('crm_staff AS sta ON sta.id = picid')
                                    ->field('theme, content, ctime,sta.name pname, crm_contactrecord.ctype, picid, posttime,contact,contact_num')
                                    ->limit(0, 5)
                                    ->order('posttime desc')
                                    ->select();
                                foreach ($recordMsg as &$val) {
                                    $val['posttime'] = date('Y-m-d H:i:s',$val['posttime']);
                                    switch ($val['ctype']) {
                                        case 1 :
                                            $val['ctype'] = "电话";
                                            break;
                                        case 2 :
                                            $val['ctype'] = "拜访";
                                            break;
                                        case 3 :
                                            $val['ctype'] = "会议";
                                            break;
                                        case 4 :
                                            $val['ctype'] = "即时消息(qq、微信等)";
                                            break;
                                        case 5 :
                                            $val['ctype'] = "邮件";
                                            break;
                                        case 6 :
                                            $val['ctype'] = "其他";
                                            break;
                                        case 7 :
                                            $val['ctype'] = "高管约谈";
                                            break;
                                    }
                                }
                                $msg['status'] = 200;
                                $msg['msg']    = "进入回访流程，";
                                $msg['data'] = array(
                                    'phone' => $phoneMsg,
                                    'contact' => $recordMsg,
                                    'callback' => $callbackCusChange
                                );
                            } else {
                                M()->rollback();
                                $msg['status'] = 400;
                                $msg['msg'] = "发现当前客户负责人与回访人一致，事务回滚";
                            }
                        } else {
                            M()->rollback();
                            $msg['status'] = 400;
                            $msg['msg']    = "回访人与客户负责人冲突，修改失败，事务回滚";
                        }
                        $this->ajaxReturn($msg);
                    } else {
                        $recordMsg = M('contactrecord')->where($record)
                            ->join('crm_customer AS cus ON cus.cid = customerid')
                            ->join('crm_staff AS sta ON sta.id = picid')
                            ->field('theme, content, ctime,sta.name pname, crm_contactrecord.ctype, picid, posttime,contact,contact_num')
                            ->limit(0, 5)
                            ->order('posttime desc')
                            ->select();
                        foreach ($recordMsg as &$val) {
                            $val['posttime'] = date('Y-m-d H:i:s',$val['posttime']);
                            switch ($val['ctype']) {
                                case 1 :
                                    $val['ctype'] = "电话";
                                    break;
                                case 2 :
                                    $val['ctype'] = "拜访";
                                    break;
                                case 3 :
                                    $val['ctype'] = "会议";
                                    break;
                                case 4 :
                                    $val['ctype'] = "即时消息(qq、微信等)";
                                    break;
                                case 5 :
                                    $val['ctype'] = "邮件";
                                    break;
                                case 6 :
                                    $val['ctype'] = "其他";
                                    break;
                                case 7 :
                                    $val['ctype'] = "高管约谈";
                                    break;
                            }
                        }
                        $msg['status'] = 200;
                        $msg['msg']  = "进入回访流程";
                        $msg['data'] = array(
                            'phone'   => $phoneMsg,
                            'contact' => $recordMsg,
                            'callback' => $callbackCus
                        );
                    }
                } else {
                    $msg = array(
                        'status' => 400,
                        'msg'    => "您本次回访任务已完成，没有未回访客户"
                    );
                }
            } else {
                $msg = array(
                    'status' => 300,
                    'msg'    => "您不在本次回访任务的客服专员名单中，如有问题，请联系管理员"
                );
            }
            $this->ajaxReturn($msg);
        } else {
            $data = $this->getCallbackNum();
            $this->assign('data', $data);
            $this->display();
        }
    }

    /**
     * 添加回访记录按钮 post提交问题、答案信息想
     * 提交数据后，进行客户表回访状态的修改和客户回访表中数据的修改
     * @todo 该过程需要记录客户状态修改记录
     *
    */
    public function addCallbackRecord()
    {
        if (IS_POST) {
            $this->posts = I('post.');
            $this->posts['online_name'] = session('nickname');
            $this->posts['callback_status'] = "3";
            $map['id'] = array('eq', $this->posts['id']);
            if ($this->posts['question_1flag'] == "不满意" || $this->posts['question_2flag'] == "不怎么样") {
                $this->posts['satisfied_flag'] = 1;
            } else {
                $this->posts['satisfied_flag'] = 2;
            }
            //$rst = M('cus_callback')->where($map)->setField($this->posts);
            $count = count(M('cus_callback')->where($map)->field('id')->select());
            $this->posts['callback_batch'] = $count;
            M()->startTrans();
            $rst1 = M()->table('crm_cus_callback')->where($map)->setField($this->posts);
            // 需要修改回访记录 和客户表中回访状态
            if ($rst1 !== false) {
                $cusId = M('cus_callback')->where($map)->field('cus_id')->find();
                $cusFilter['cid'] = array('EQ', $cusId['cus_id']);
                $cusSetData = array(
                    'callback_flag' => "已回访"
                );
                $rst2 = M()->table('crm_customer')->where($cusFilter)->setField($cusSetData);
                if ($rst2 !== false) {
                    M()->commit();
                    $data = $this->getCallbackNum();
                    $msg['status'] = 200;
                    $msg['msg'] = "该客户回访记录提交成功";
                    $msg['data'] = $data;
                } else {
                    M()->rollback();
                    $msg['status'] = 500;
                    $msg['msg']    = "客户回访状态更新失败，事务回滚";
                }
            } else {
                M()->rollback();
                $msg['status'] = 500;
                $msg['msg'] = "回访记录提交失败，事务回滚";
            }
            $this->ajaxReturn($msg);
        }
    }

    /**
     * 回访总体进度查看页面
     * 各个客服工程师的回访进度
    */
    public function showCallbackInfo()
    {
        /*
         * 1 本次回访记录总数
         * 2 本次回访未完成数
         * 3 按人员分数量 + 回访进度 + 回访分配日期
         * */
       // $onlineIds = $this->getOnlineServiceStaffIds();
        //$searchLimit['callback_status'] = array('EQ', "2");
        $time = M('cus_callback')->field('assign_time')->order('assign_time desc')->find();
        if ($time) {
            $map['assign_time'] = array('EQ', $time['assign_time']);

            $rst = M('cus_callback')->where($map)
                ->field("online_id,a.name name,assign_time,count(online_id) total,
            (SELECT IFNULL(count(id),0)
                FROM `crm_cus_callback` AS b 
                WHERE callback_status = '2' and b.online_id = crm_cus_callback.online_id and assign_time = {$time['assign_time']}) AS do_not")
                ->join('LEFT JOIN crm_staff a ON a.id = crm_cus_callback.online_id')
                ->group('online_id')->select();
            foreach($rst as &$val) {
                $val['a'] = (100 - round($val['do_not'] / $val['total'] * 100)) . "%";
                $val['done'] = $val['total'] - $val['do_not'];
                if ($val['do_not'] == 0) {
                    $val['status'] = "回访完毕";
                } else {
                    $val['status'] = "进行中";
                }
            }
            $this->assign('data', $rst);
        }
        $this->display();
    }

    /**
     * author：maxu
     * 客服业绩统计
     * 当月有效客服记录、100元以上业绩客户
     * @todo:100元以上业绩客户和统计数据直接assign给模板，未用服务端分页
     */
    public function countServicePerformance(){


        if(IS_AJAX){
            $onlineModel = new OnlineserviceModel();
            $staffModel  = new StaffModel();
            if (I('post.startTime') == ''){
                $startTime = mktime(0,0,0,date('m'), 1, date('Y'));
            }else{
                $mouth = (int) explode('-', I('post.startTime'))[1];
                if ($mouth < date('m') - 2 || $mouth >  date('m')){
                    $this->ajaxReturn([
                        'status' => self::FAIL_STATUS,
                        'msg' => '时间不合法'
                    ]);
                }
                $startTime = strtotime(I('post.startTime'));
            }
            $endTime = strtotime('+1 month ', $startTime) -1;

            $roleFilter['role_id'] = array('IN', self::ONLINE_ROLE);
            $onlineIds = D('auth_role')->getRoleList('staff_ids', $roleFilter, 'staff_ids',0, 500);
            $onlineIds = getPrjIds(array_filter($onlineIds),'staff_ids');

            $map['crm_staff.id'] = array('IN', $onlineIds . ",1");
            $map['crm_staff.loginstatus'] = array('NEQ', "1");
            $this->field = "crm_staff.id,crm_staff.name,GROUP_CONCAT(c.role_name) role_name, b.name dept";

            $data = $staffModel->getStaffInfo($this->field, $map, 0, 50, 'crm_staff.id');

            $staffIds = getPrjIds($data, 'id');

            //查询属于客服部门的所有人
            $onlineFilter['server_id'] = array('IN', $staffIds);
            $onlineFilter['austatus']   = array('EQ', "2");
            $onlineFilter['crm_onlineservice.addtime']    = [['GT',$startTime], ['LT', $endTime]];
            //业务
            $this->posts        = I('post.');
            $draw = $this->posts['draw'];
            $this->sqlCondition = $this->getSqlCondition($this->posts);

            $this->field = "cname c_id, content cus_question,answercontent online_solution, s.name staff_name,from_unixtime(crm_onlineservice.addtime) service_time";
            $count = $onlineModel->where($onlineFilter)->count();

            if ($this->sqlCondition['search']) {
                $onlineFilter['c_id|staff_name'] = array('like', "%" . $this->sqlCondition['search'] . "%");
            }
            $filterRecord = $onlineModel->where($onlineFilter)->count();
            $serviceData = $onlineModel->getServiceList($onlineFilter, $this->field, $this->sqlCondition['start'], $this->sqlCondition['length'], $this->sqlCondition['order']);
            $this->output = $this->getDataTableOut($draw, $count, $filterRecord, $serviceData);

            // 大量冗余分隔线----------------------------------------------------------------------

            foreach ($data as &$val) {
                $val['cus_num'] = 0;
                $val['service_num'] = 0;
            }
            $where['b.picid']       = array('IN', $staffIds);
            $where['a.settle_time'] = [['GT',$startTime], ['LT', $endTime]];

            /* order collection data where staffIds in online positions and settle_time gt start of this month */

            $result = M('order_collection')
                ->alias('a')
                ->field('b.cus_id, round(
                    sum(case  
                        when settle_type in (1,7) and a.product_id in (15001,15002,15004) and p.statistics_performance_flag = 1
                            then (case
                                    when b.settlement_method in (\'JF05\', \'JF16\')
                                        then settle_price * 0.99
                                    when b.settlement_method in (\'HP01\', \'HP02\') 
                                         then settle_price * 0.98
                                else settle_price end)
                        when settle_type in (3,8) and a.product_id in (15001,15002,15004) and p.statistics_performance_flag = 1
                            then (case
                                    when b.settlement_method in (\'JF05\', \'JF16\')
                                        then settle_price * 0.99 * (-1)
                                    when b.settlement_method in (\'HP01\', \'HP02\') 
                                         then settle_price * 0.98 * (-1)
                                else settle_price * (-1) end)
                        when settle_type in (1,7) and a.product_id not in (15000,15001,15002,15003,15004) and p.statistics_performance_flag = 1 
                            then 
                                (case
                                    when b.settlement_method in (\'JF05\', \'JF16\')
                                        then settle_price * 0.99
                                    when b.settlement_method in (\'HP01\', \'HP02\') 
                                         then settle_price * 0.98
                                else settle_price end)
                        when settle_type in (3,8) and a.product_id not in (15000,15001,15002,15003,15004) and p.statistics_performance_flag = 1
                            then 
                                (case
                                    when b.settlement_method in (\'JF05\', \'JF16\')
                                        then settle_price * 0.99
                                    when b.settlement_method in (\'HP01\', \'HP02\') 
                                         then settle_price * 0.98
                                else settle_price end) * (-1)
                        else 0 end),2) settle_normal_price, b.picid, d.name, e.name as dept,k.cus_pid,k.cname')
                ->join('LEFT JOIN crm_orderform b ON a.cus_order_id = b.id')
                ->join('LEFT JOIN crm_staff d ON d.id = b.picid')
                ->join('LEFT JOIN crm_dept e ON e.id = d.deptid')
                ->join('LEFT JOIN crm_industrial_seral_screen p ON p.product_id = a.product_id')
                ->join('LEFT JOIN crm_customer k ON k.cid = b.cus_id')
                ->where($where)
                ->group('cus_id')
                ->select();

            $cusArr = array();
            foreach($result as $value) {
                $cusArr[] = $value['cus_id'];
            }
            foreach ($result as $item) {
                if (!empty($item['cus_pid'])) {
                    if (!in_array($item['cus_pid'], $cusArr)) {
                        // 总公司没有结算记录
                        $cusFilter['cid'] = array('EQ', $item['cus_pid']);
                        $addData = M('customer')->field("cid cus_id, cname")->where($cusFilter)->find();
                        $addData['name'] = $item['name'];
                        $addData['dept'] = $item['dept'];
                        $addData['settle_normal_price'] = 0;
                        $addData['cus_pid'] = null;
                        $addData['picid'] = $item['picid'];
                        array_push($result, $addData);
                        array_push($cusArr, $item['cus_pid']);
                    }
                }
            }

            /* 客户涉及到子公司，把子公司的业绩追加到总公司 */
            for ($i = 0; $i < count($result); $i++) {
                $result[$i]['count'] = $result[$i]['settle_normal_price'];
                for ($j = 0; $j < count($result); $j++) {
                    if ($result[$i]['cus_id'] == $result[$j]['cus_pid']) {
                        $result[$i]['count'] += $result[$j]['settle_normal_price'];
                    }
                }
            }
            /* 去除掉子公司 */
            $statistics_prev = array();
            for ($p = 0; $p < count($result); $p++) {
                if (empty($result[$p]['cus_pid'])) {
                    $statistics_prev[$p]['picid']  = $result[$p]['picid'];
                    $statistics_prev[$p]['name']  = $result[$p]['name'];
                    $statistics_prev[$p]['cname'] = $result[$p]['cname'];
                    $statistics_prev[$p]['dept']  = $result[$p]['dept'];
                    $statistics_prev[$p]['count'] = $result[$p]['count'];
                }
            }
            $statistics = array();
            $statistics_prev = array_values($statistics_prev);
            foreach ($statistics_prev as $value_2) {
                if ($value_2['count'] > 100) {
                    $statistics[] = $value_2;
                }
            }

            /* 客户服务记录 */
            $onlineModel = new OnlineserviceModel();
            $this->field = "count(crm_onlineservice.id) service_num, server_id";
            $serviceData = $onlineModel->getServiceListWithGroup($onlineFilter, $this->field, 'crm_onlineservice.addtime', 0,10000, 'server_id');

            /* 追加产生业绩的客户数量到客服部门各个员工。 */
            foreach ($data as &$val_2) {
                foreach ($statistics as $v) {
                    if ($v['picid'] == $val_2['id']) {
                        $val_2['cus_num'] += 1;
                    }
                }
                foreach ($serviceData as $p) {
                    if ($p['server_id'] == $val_2['id']) {
                        $val_2['service_num'] = $p['service_num'];
                    }
                }
            }
            // 排除数据中的null
            foreach ($statistics as &$value1) {
                foreach ($value1 as &$value2) {
                    if ($value2 == null){
                        $value2 = '';
                    }
                }
            }

            $this->output['kehu100yuan'] = $statistics;
            $this->output['staffData'] = $data;
            $this->ajaxReturn($this->output);
        } else{
            $this->display();
        }
    }

}
