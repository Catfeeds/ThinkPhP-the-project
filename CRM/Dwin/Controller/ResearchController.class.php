<?php
/**
 * Created by PhpStorm.
 * User: MaXu
 * Date: 17-5-13
 * Time: 下午2:44
 */

namespace Dwin\Controller;

// 研发相关需求
class ResearchController extends CommonController
{

    protected $staffId;
    protected $rules;
    public function _initialize()
    {
        parent::_initialize();
        $this->staffId = session('staffId');
        $this->rules = session('userRule');
    }

    /**
     * 21 立项权限节点  addPublicPrj addPublicPrjOk  addProject addProjectOk
     * @todo 修改权限架构后，重新编写判定是否能添加的权限
     * addPublicPrj :立项前公示
     * addProject addProjectOk：立项申请
     */
    public function addPublicPrj()
    {
        if (IS_POST) {
            $posts = I('post.');
            $posts['builderid'] = $this->staffId;
            $posts['addtime']  = time();
            $posts['protime']      = strtotime(inject_filter($posts['protime']));
            $posts['deliverytime'] = strtotime(inject_filter($posts['prjdtime']));
            $posts['proneeds'] = nl2br(inject_filter($posts['proneeds']));

            // 对当前提交的数据进行处理，需要提交到绩效表里的存放在另外的变量中，然后unset不是项目表中的数据
            $pIds    = $posts['pids'];
            $pNames  = $posts['pnames'];
            $posts['performbonus'] = inject_filter($posts['performancebonus']);
            unset($posts['pids']);
            unset($posts['pnames']);
            unset($posts['performancebonus']);
            $model = M();
            $model->startTrans();
            // 执行项目表添加操作
            if ($posts = $model->table('crm_respublic')->create($posts)) {
                $rst = $model->table('crm_respublic')->add($posts);
                $flag_1 = $rst ? 1 : 2;
            } else {
                $flag_1 = 2;
            }
            // 项目添加成功，执行绩效添加操作
            $con['proname'] = array("EQ", $posts['proname']);
            $proId  = $model->table('crm_respublic')->where($con)->field('proid')->select();
            for ($i = 0; $i < count($pIds); $i++) {
                $list[$i] = array(
                    'prjid'        => inject_id_filter($proId[0]['proid']),
                    'typename'     => inject_filter($pNames[$i]),
                    'typeid'       => inject_id_filter($pIds[$i]),
                );
            }
            $rst1 = $model->table('crm_restype')->addAll($list);
            $flag_2 = $rst1 ? 1 : 2;
            if ($flag_1 * $flag_2 != 1) {
                $model->rollback();
                $msg = 2;
            } else {
                $model->commit();
                $msg = 1;
            }
            $this->ajaxReturn($msg);
        } else {
            // 客户信息：通过审核的本人负责的客户
            $conOfcus['uid'] = array('EQ', session('staffId'));
            $conOfcus['auditstatus'] = array('EQ', '3');
            $cus = M('customer')->where($conOfcus)->field('cid,cname')->select();
            $this->assign(array(
                'cus' => $cus,
            ));
        }
        $this->display();
    }

    public function addProject()
    {
        $model = M('staff');
        if (IS_POST) {
            // 选择部门后返回该部门的职员
            if (I('post.deptID')) {
                $deptId = (int)I('post.deptID');
                // 提交为研发部下一级部门id(研发1部、2部...)
                $map_2['parent_id'] = array('EQ', $deptId);
                $data2 = M('dept')->where($map_2)->field('id')->select();
                // nextDept 为部门下辖各组id
                $nextDept = getPrjIds($data2, 'id');
                $deptIds = !empty($nextDept) ? $deptId . "," . $nextDept : $deptId;
                // 部门下所有职员id
                $map['deptid'] = array('IN', $deptIds);
                $staff = M('staff')->where($map)->field('id,name')->select();
                $this->ajaxReturn($staff);
            }

            // 选择达成立项条件的项目，返回前端项目信息，填充表单
            if (I('post.prjId')) {
                $prjId = inject_id_filter(I('post.prjId'));
                $condi['proid'] = array('EQ', $prjId);
                $typeFileter['prjid'] = array('EQ', $prjId);
                $data_1 = M('respublic')->where($condi)
                    ->join('LEFT JOIN `crm_customer` cus ON cus.cid=customerid')
                    ->field('cus.cid cid,cus.cname cusname,proprice,docwrite,codedesign,propcb,protemp,promaint,protime,deliverytime,proneeds,performbonus')
                    ->select();
                $data_2 = M('restype')->where($typeFileter)->select();
                $data_1['prostring'] = date('Y-m-d', $data_1[0]['protime']);
                $data_1['delistirng'] = date('Y-m-d', $data_1[0]['deliverytime']);
                $data_1[0]['proneeds'] = preg_replace('/<br\\s*?\/??>/i', '', $data_1[0]['proneeds']);
                $data['basic'] = $data_1;
                $data['staff'] = $data_2;
                $this->ajaxReturn($data);
            }
        } else {
            // 部门信息：研发部门
            $condition['parent_id'] = array('EQ', '2');
            $dept = M('dept')->where($condition)->select();
            // 审核人信息：项目审核人列表
            $con['roleid'] = array('IN', "2,3,6,7");
            $audit = $model
                ->where($con)
                ->field('id, name')
                ->select();
            // 客户信息：通过审核的本人负责的客户
            $conOfcus['uid'] = array('EQ', $this->staffId);
            $conOfcus['auditstatus'] = array('EQ', '3');
            $cus = M('customer')->where($conOfcus)->field('cid,cname')->select();

            // 申请人数已满未立项的项目
            $filter['auditstatus'] = array('EQ', '2');
            $filter['builderid'] = array('EQ', $this->staffId);
            $prjData = M('respublic')->where($filter)
                ->field('proid, proname')
                ->select();
            $this->assign(array(
                'dept'  => $dept,
                'audit' => $audit,
                'cus'   => $cus,
                'prjData' => $prjData
            ));
        }
        $this->display();
    }
    public function addProjectOk()
    {
        if (IS_POST) {
            $posts = I('post.');
            $prjId = (int)$posts['publicprjid'];
            $posts['builderid'] = (int)session('staffId');
            $posts['addtime']   = time();
            $posts['protime']      = strtotime($posts['protime']);
            $posts['deliverytime'] = strtotime($posts['prjdtime']);
            $posts['proneeds']  = nl2br(inject_filter($posts['proneeds']));

            // 对当前提交的数据进行处理，需要提交到绩效表里的存放在另外的变量中，然后unset不是项目表中的数据
            $pIds    = $posts['pids'];
            $jxLists = $posts['jxvallists'];
            $pNames  = $posts['pnames'];
            $posts['performbonus'] = inject_filter($posts['performancebonus']);
            unset($posts['pids']);
            unset($posts['jxvallists']);
            unset($posts['pnames']);
            unset($posts['performancebonus']);
            $model = M();
            $model->startTrans();
            // 执行项目表添加操作
            if ($posts = $model->table('crm_research')->create($posts)) {
                $rst   = $model->table('crm_research')->add($posts);
                $flag_1 = ($rst !== false) ? 1 : 2;
            } else {
                $flag_1 = 2;
            }
            // 项目添加成功，执行绩效添加操作
            $con['proname'] = array("EQ", $posts['proname']);
            $proId  = $model->table('crm_research')->where($con)->field('proid')->select();
            for ($i = 0; $i < count($pIds); $i++) {
                $list[$i] = array(
                    'prjid' => (int)$proId[0]['proid'],
                    'pname' => inject_filter($pNames[$i]),
                    'pid'   => inject_id_filter($pIds[$i]),
                    'jxval' => inject_filter($jxLists[$i]),
                );
            }
            $rst1 = $model->table('crm_resjixiao')->addAll($list);
            $flag_2 = ($rst1 !== false) ? 1 : 2;

            $mapPublic['proid'] = array('EQ', $prjId);
            $data['auditstatus'] = "3";
            $rst2 = $model->table('crm_respublic')->where($mapPublic)->setField($data);
            $flag_3 = ($rst2 !== false && count($rst2) != 0) ? 1 : 2;
            if ($flag_1 * $flag_2 * $flag_3 != 1) {
                $model->rollback();
                $msg = 2;
            } else {
                $model->commit();
                $msg = 1;
            }
            $this->ajaxReturn($msg);
        }
    }

    /**
     * 22 查看项目节点 showPublicPrj  showPubPrjDetail showOwnPrj showOwnDeliveryPrj showOCPrj showOwnPrjAudit
     * showProjectDetail showPerformanceDetail showPrjUpdateList showPrjChangeList showAllPrjNow
     * @todo 项目列表的查看需要根据权限设置，后续要调整
     * 个人项目列表需要调整
     * 根据项目id查看以及公示的项目无需权限
     */
    public function showPublicPrj()
    {
        // 根据日常权限查看，
        $map['status'] = array('EQ', '1');
        $prjIds = M('restype')->where($map)->select();
        $prjId = getPrjIds($prjIds, 'prjid');
        if ($prjId != "") {
            $filter['proid'] = array('IN', $prjId);
            $data = M('respublic')->where($filter)
                ->join('LEFT JOIN `crm_staff` s ON builderid = s.id')
                ->join('LEFT JOIN `crm_customer` cus ON customerid = cus.cid')
                ->field("crm_respublic.*,s.name buildname,cus.cname cusname,
                    (SELECT GROUP_CONCAT(typename) pname FROM `crm_restype` ty WHERE ty.prjid = crm_respublic.proid) pname")
                ->select();
        } else {
            $data = "";
        }

        $map2['crm_respublic.auditstatus'] = array('EQ', '2');
        $prjPub = M('respublic')->where($map2)
            ->join('LEFT JOIN `crm_staff` AS s ON builderid = s.id')
            ->join('LEFT JOIN `crm_customer` AS cus ON customerid = cus.cid')
            ->field("crm_respublic.*,s.name AS buildname,cus.cname AS cusname,
                (SELECT GROUP_CONCAT(typename) pname FROM `crm_restype` ty WHERE ty.prjid = crm_respublic.proid) pname,
                (SELECT GROUP_CONCAT(staffname) staffname FROM `crm_restype` ty WHERE ty.prjid = crm_respublic.proid) staffname")
            ->select();

        $this->assign(array(
            'data' => $data,
            'prjPub' => $prjPub
        ));
        $this->display();
    }
    public function showPubPrjDetail()
    {
        // (1)相关模型
        $model   = D('respublic');
        $jxModel = D('restype');
        // (2)项目id
        $id = inject_id_filter(I('get.id'));
        // (4)项目基本情况
        $rst = $model
            ->join('LEFT JOIN crm_staff AS s ON crm_respublic.builderid = s.id')
            ->join('LEFT JOIN crm_customer AS cus ON crm_respublic.customerid = cus.cid')
            ->join('LEFT JOIN crm_restype AS ty ON crm_respublic.proid = ty.prjid')
            ->field('crm_respublic.*,s.name AS buildname,cus.cname AS cusname')
            ->find($id);
        // (5)绩效分配情况
        $condi['prjid'] = array('EQ', $id);
        $jxList = $jxModel->where($condi)
            ->join('LEFT JOIN crm_respublic AS r ON prjid = r.proid')
            ->join('LEFT JOIN crm_staff AS s ON staff_id = s.id')
            ->field('crm_restype.*,r.proname,r.performbonus')
            ->select();
        $filter['prjid'] = array('EQ', $id);
        $filter['status'] = array('EQ', '1');
        $selList = $jxModel->where($filter)->select();
        // 传递数据，渲染模板
        $this->assign(array(
            'data1'      => $rst,
            'data2'      => $jxList,
            'data3'      => $selList
        ));
        $this->display();
    }

    protected function getPrjCondition()
    {
        $prjStaffIds = empty($this->getStaffIds($this->staffId, 'prj_child_id', "")) ? $this->staffId : $this->staffId . "," . $this->getStaffIds($this->staffId, 'prj_child_id', "");
        $cusStaffIds = empty($this->getStaffIds($this->staffId, 'cus_child_id', "")) ? $this->staffId : $this->staffId . "," . $this->getStaffIds($this->staffId, 'cus_child_id', "");
        $condi['pid'] = array('IN', $prjStaffIds);
        $ids = M('resjixiao')->where($condi)->field('prjid')->select();
        $proId = count($ids) == 0 ? "" : getPrjIds($ids, 'prjid');
        $condis['crm_research.builderid'] = array('IN', $cusStaffIds);// 立项人

        if (!empty($proId)) {
            $condis['_logic'] = 'OR';
            $condis['proid'] = array('IN', $proId);// 项目ID查询条件
        }
        return $condis;
    }
    // 项目列表
    public function showOwnPrj()
    {
        // 查看权限下所有项目
        /* 项目参与人和项目创建人查看 参与人，研发人员可查看职能下的所有客户 项目创建人可以查看自己管辖的人的项目*/
        // 首先获取权限下的staffIds
        // 查询作为项目参与人的项目列表（绩效表查） + 作为项目创建人的项目列表
        if(IS_POST) {
            // 加载按钮
            $posts = I('post.');
            // range 1 个人 2 下属 个人时查看是否有变更、验收、完成权限
            $msg = 0;
            if ($posts['prj_range'] == 1) {
                if (in_array(27, explode(',', $this->rules))) {
                    $msg += 1;
                }
                if ($posts['prj_type'] == 1) {
                    // 个人进展中项目，查看有无更新，有无变更权
                    if (in_array(21, explode(',', $this->rules))) {
                        $msg += 2;
                    }
                } elseif ($posts['prj_type'] == 2) {
                    if (in_array(21, explode(',', $this->rules))) {
                        $msg += 3;
                    }
                } else {
                    if (in_array(21, explode(',', $this->rules))) {
                        $msg += 4;
                    }
                }
            }
            $this->ajaxReturn($msg);
        } else {
            $n = inject_id_filter(empty(I('get.n')) ? 1 : I('get.n'));
            $nTime = 3600 * 24 * $n;
            $map['_complex']  = $this->getPrjCondition();
            $map['prostatus'] = array('EQ', '1');
            $map['crm_research.auditstatus'] = array('IN', '1,2');

            $data = $this->getResList($map, 'research', $nTime);

            if (empty($data)) { $this->assign('audit', $data); }
            $this->display();
        }
    }
    public function showOwnDeliveryPrj()
    {
        // 验收中
        // 根据组织架构查看内容。
        $posts = I('post.');

        //获取Datatables发送的参数 必要
        $draw = $posts['draw'];

        // 排序
        $order_dir = $posts['order']['0']['dir'];//ase desc 升序或者降序
        $order_column = (int)$posts['order']['0']['column'];
        switch ($order_column) {
            case 0 :
                $order = "pname " . $order_dir;
                break;
            case 1 :
                $order = "dept_name " . $order_dir;
                break;
            case 2 :
                $order = "prj_name " . $order_dir;
                break;
            case 3 :
                $order = "prj_record " . $order_dir;
                break;
            case 4 :
                $order = "prj_price " . $order_dir;
                break;
            case 5 :
                $order = "start_time " . $order_dir;
                break;
            case 6 :
                $order = "delivery_time " . $order_dir;
                break;
            case 7 :
                $order = "prjdtime " . $order_dir;
                break;
            case 8 :
                $order = "complete_time " . $order_dir;
                break;
            case 9 :
                $order = "change_num " . $order_dir;
                break;
            case 10 :
                $order = "builder_name " . $order_dir;
                break;
            case 11 :
                $order = "builder_name " . $order_dir;
                break;
            case 12 :
                $order = "cus_name " . $order_dir;
                break;
            default :
                $order = "builder_name";
        }

        //分页
        $start  = $posts['start'];  //从多少开始
        $length = $posts['length']; //数据长度
        $limitFlag = isset($posts['start']) && $length != - 1 ;
        if ($limitFlag) {
            $start  = (int)$start;
            $length = (int)$length;
        }
        //搜索
        $search = $posts['search']['value'];//获取前台传过来的过滤条件

        // 传递的自定义参数
        /**
         * @var $prj_type
         * @var $prj_range
         * @var $time_limit
         */
        $prj_type   = $posts['prj_type'];
        $prj_range  = $posts['prj_range'];
        $time_limit = $posts['time_limit'];
        $month = strtotime(date('Y-m-1'));
        $map['res.auditstatus'] = array('EQ', '3');
        $map['prostatus'] = array('EQ', "$prj_type");
        if ($prj_range == 1) {
            $prjStaffIds = $this->staffId;
            $cusStaffIds = $this->staffId;
        } else {
            $prjStaffIds = empty($this->getStaffIds($this->staffId, 'prj_child_id', "")) ? $this->staffId : $this->staffId . "," . $this->getStaffIds($this->staffId, 'prj_child_id', "");
            $cusStaffIds = empty($this->getStaffIds($this->staffId, 'cus_child_id', "")) ? $this->staffId : $this->staffId . "," . $this->getStaffIds($this->staffId, 'cus_child_id', "");
        }
        $condi['pid'] = array('IN', $prjStaffIds);
        $ids = M('resjixiao')->where($condi)->field('prjid')->select();
        $proId = count($ids) == 0 ? "" : getPrjIds($ids, 'prjid'); // 参与人
        $map_1['builderid'] = array('IN', $cusStaffIds);// 立项人
        if (!empty($proId)) {
            $map_1['_logic'] = 'OR';
            $map_1['proid'] = array('IN', $proId);// 项目参与人条件
        }
        if ($time_limit) {
            $timeLimit = time() - 86400 * (int)$time_limit;
        } else {
            $timeLimit = time() - 86400 * 30;
        }
        $map['_complex'] = $map_1;
        $count = M('research')
            ->alias('res')
            ->join('LEFT JOIN crm_resprogress resp ON resp.project_id = res.proid')
            ->where($map)->count();
        $recordsFiltered = $count;
        $data = M('research')->alias('res')
            ->where($map)
            ->join('LEFT JOIN `crm_staff` s ON res.builderid = s.id')
            ->join('LEFT JOIN `crm_dept` d ON  d.id= res.projectdepartment')
            ->join('LEFT JOIN `crm_customer` cus ON res.customerid = cus.cid')
            ->field("res.proid, res.proname prj_name, res.performbonus prj_price, res.protime start_time,
                 res.deliverytime delivery_time, res.prodtime prjdtime, res.finaltime complete_time, res.proneeds tips,
                 s.name builder_name, d.name dept_name,cus.cname cus_name,
                    (SELECT GROUP_CONCAT(pname) pname FROM `crm_resjixiao` jx 
                        WHERE jx.prjid = res.proid) pname,
                    (SELECT IFNULL(count(*),0) FROM `crm_resprogress` AS pro 
                        WHERE pro.project_id = res.proid AND posttime > {$timeLimit})AS prj_record,
                    (SELECT IFNULL(count(*),0) FROM `crm_resprogress` AS pro 
                        WHERE pro.project_id = res.proid AND pro.audistatus = '1' AND posttime > {$timeLimit})AS unchecknum,
                    (SELECT IFNULL(count(*),0) FROM `crm_reschange` AS cha
                        WHERE cha.projid = res.proid) AS change_num")
            ->order($order)
            ->limit($start, $length)
            ->select();
        if (count($data) != 0) {
            foreach($data as $key => $val) {
                $info[$key] = array(
                    'DT_RowId'      => $val['proid'],
                    'pname'         => $val['pname'],
                    'dept_name'     => $val['dept_name'],
                    'prj_name'      => $val['prj_name'],
                    'prj_record'    => $val['prj_record'],
                    'prj_price'     => $val['prj_price'] ? $val['prj_price'] . "元" : "",
                    'start_time'    => date('Y-m-d', $val['start_time']),
                    'delivery_time' => date('Y-m-d', $val['delivery_time']),
                    'prjdtime'      => empty($val['prjdtime']) ? "未验收" : date('Y-m-d', $val['prjdtime']),
                    'complete_time' => empty($val['complete_time']) ? "未完成" : date('Y-m-d', $val['complete_time']),
                    'change_num'    => $val['change_num'],
                    'builder_name'  => $val['builder_name'],
                    'cus_name'      => $val['cus_name']
                );
            }
        } else {
            $info = "";
        }
        $info = empty($info) ? "" : $info;
        $output = array(
            "draw"            => intval($draw),
            "recordsTotal"    => $count,
            "recordsFiltered" => $recordsFiltered,
            "data"            => $info
        );
        $this->ajaxReturn($output);
    }
    public function showOCPrj()
    {
        //show own completed project
        $month = strtotime(date('Y-m-1'));
        $map['_complex']  = $this->getPrjCondition();
        $map['crm_research.prostatus'] = array('EQ','2');
        $map['crm_research.auditstatus'] = array('EQ', '3');
        $map['finaltime'] = array('GT', $month);
        $nTime = 3600 * 24 * 7;
        $data = $this->getResList($map, 'research', $nTime);
        $this->assign('idPrj', $data);
        $this->display();
    }
    public function showOwnPrjAudit()
    {
        // 根据个人id，总经理：可查看所有；然后根据组织架构查看
        $nTime = 3600 * 24 * 7;
        $map['_complex']  = $this->getPrjCondition();
        $map['crm_research.auditstatus'] = array('IN', "1,2,4");
        $data = $this->getResList($map, 'research', $nTime);
        $this->assign('data', $data);
        $this->display();
    }
    // 个人项目 按照项目id查看的内容 项目详情 项目绩效 进度列表 项目变更
    public function showProjectDetail()
    {
        // (1)相关模型
        $model      = D('research');
        $jxModel    = D('resjixiao');
        $prjUpModel = D('resprogress');
        $prjChModel = M('reschange');
        // (2)项目id
        $id = inject_id_filter(I('get.id'));
        $where['project_id'] = array('EQ', $id);// 项目绩效
        $condition['projid'] = array('EQ', $id); // 项目变更查询
        // (4)项目基本情况
        $rst = $model->getPrjBasic($id);
        // (5)绩效分配情况
        $jxList = $jxModel->getPrjJX($id);

        // (6)项目进度信息
        $count = $prjUpModel->getCounts($where);

        $updateContents = $prjUpModel->where($where)
            ->join('LEFT JOIN crm_staff AS sta ON sta.id = prjer_id')
            ->join('LEFT JOIN crm_research AS res ON res.proid = project_id')
            ->field('crm_resprogress.*,sta.name AS prjername,res.proname AS prjname')
            ->order('posttime DESC')
            ->select();

        // 项目变更情况
        $changeCount = $prjChModel->where($condition)->count();
        $data = $prjChModel->where($condition)
            ->join('LEFT JOIN crm_staff AS a ON postId = a.id')
            ->join('LEFT JOIN crm_staff AS b ON auditId = b.id')
            ->field('crm_reschange.*,a.name AS postname,b.name AS auditname')
            ->select();
        //$data2 = array_filter($data);
        foreach ($data as $val) {
            $data2[] = array_filter($val);
        }
        foreach ($data2 as &$val) {
            if (isset($val['newpartname'])) {
                $val['newpartname'] = json_decode($val['newpartname']);
                $val['oldpartname'] = json_decode($val['oldpartname']);
                $val['newjxval']    = json_decode($val['newjxval']);
                $val['oldjxval']    = json_decode($val['oldjxval']);
                $val['newpartner']  = json_decode($val['newpartner']);
                $val['oldpartner']  = json_decode($val['oldpartner']);
            }
        }
        // 传递数据，渲染模板
        $this->assign(array(
            'data1'      => $rst,
            'data2'      => $jxList,
            'updContent' => $updateContents,
            'chCount'    => $changeCount,
            'chData'     => $data2,
            'updCount'   => $count,
        ));
        $this->display();
    }
    public function showPerformanceDetail()
    {
        $id    = inject_id_filter(I('post.prj_id'));
        $model = M('resjixiao');
        $where['proid'] = array('EQ', $id);
        $rst   = $model->where($where)
            ->join('LEFT JOIN crm_research AS r ON prjid = r.proid')
            ->field('pname user_name,jxval per')
            ->select();
        $outMsg = "";
        foreach($rst as $val) {
            $outMsg .= $val['user_name'] . ':' . $val['per'] ."%<br/>";
        }
        $this->ajaxReturn($outMsg);
    }
    public function showPrjUpdateList()
    {
        $prjId = inject_id_filter(I('post.id'));
        $nId = (int)I('post.time_limit');
        $nId = empty($nid) ? 1 : $nId;
        $nTime = time() - 3600 * 24 * $nId;
        $model = D('resprogress');
        $condition['project_id'] = array('EQ', $prjId);
        $condition['posttime']   = array('GT', $nTime);
        $updateContents   = $model->where($condition)
            ->join('LEFT JOIN crm_staff AS sta ON sta.id = prjer_id')
            ->join('LEFT JOIN crm_research AS res ON res.proid = project_id')
            ->field('crm_resprogress.*,sta.name AS prjername,res.proname AS prjname')
            ->order('posttime DESC')
            ->select();
        $output = "";
        foreach ($updateContents as $key => &$val) {
            switch ($val['audistatus']) {
                case 1 :
                    $val['audistatus'] = "未审核";
                    break;
                case 2 :
                    $val['audistatus'] = "通过";
                    break;
                case 3 :
                    $val['audistatus'] = "未通过";
                    break;
                default :
                    $val['audistatus'] = "未知情况";
                    break;
            }
            $output .= "<br/>提交时间：" . date("Y-m-d H:i:s",$val['posttime']) . "&emsp;<span style='color:red;'>（" . $val['audistatus'] ."）</span>主题：" . $val['theme']. "更新人：" . $val['prjername'];
            $output .= "<br/> 内容：" . $val['prjcontent'] ;
        }
        $this->ajaxReturn($output);
    }
    public function showPrjChangeList()
    {
        $prjId = I('post.prj_id');
        $model = M('reschange');
        $condition['projid'] = array('EQ', $prjId);
        $data = $model->where($condition)
            ->join('LEFT JOIN crm_staff AS a ON postId = a.id')
            ->join('LEFT JOIN crm_staff AS b ON auditId = b.id')
            ->field('crm_reschange.*,a.name AS postname,b.name AS auditname')
            ->select();
        //$data2 = array_filter($data);
        foreach ($data as $val) {
            $data2[] = array_filter($val);
        }
        foreach ($data2 as $key => &$val) {
            if (isset($val['newpartname'])) {
                $val['newpartname'] = json_decode($val['newpartname']);
                $val['oldpartname'] = json_decode($val['oldpartname']);
                $val['newjxval']    = json_decode($val['newjxval']);
                $val['oldjxval']    = json_decode($val['oldjxval']);
                $val['newpartner']  = json_decode($val['newpartner']);
                $val['oldpartner']  = json_decode($val['oldpartner']);
            }
            $info[$key] = "";
            $info[$key] .= "修改时间：" . date('Y-m-d',$val['changetime']);
            if ($val['olddeliverytime']) {
                $info[$key] .= "<br/>验收时间由&emsp;" . date('Y-m-d',$val['olddeliverytime']) . "&nbsp;变为：" . date('Y-m-d', $val['newdeliverytime']);
            }
            if ($val['oldprjneeds']) {
                $info[$key] .= "<br/>项目需求由<br/>" . $val['oldprjneeds'] . "<br/>变为：<br/>" . $val['newprjneeds'];
            }
            if ($val['oldbonus']) {
                $info[$key] .= "<br/>总绩效由：" . $val['oldbonus'] . "元，变为：" . $val['newbonus'] . "元";
            }
            if ($val['newpartner']) {
                $info[$key] .= "<br/>项目参与人及绩效分配变更：";
                for($j = 0; $j < count($val['newpartner']); $j++) {
                    $info[$key] .= $val['newpartname'][$j] . "(" . $val['newjxval'][$j] . "%)<br/>";
                }
            }
        }
        $this->ajaxReturn($info);
    }
    // 公示项目 只能查看的内容
    public function showAllPrjNow()
    {
        if (IS_POST) {
            // 根据组织架构查看内容。
            $posts = I('post.');

            //获取Datatables发送的参数 必要
            $draw = $posts['draw'];

            // 排序
            $order_dir = $posts['order']['0']['dir'];//ase desc 升序或者降序
            $order_column = (int)$posts['order']['0']['column'];
            switch ($order_column) {
                case 0 :
                    $order = "pname " . $order_dir;
                    break;
                case 1 :
                    $order = "dept_name " . $order_dir;
                    break;
                case 2 :
                    $order = "prj_name " . $order_dir;
                    break;
                case 3 :
                    $order = "start_time " . $order_dir;
                    break;
                case 4 :
                    $order = "delivery_time " . $order_dir;
                    break;
                case 5 :
                    $order = "bonus " . $order_dir;
                    break;
                case 6 :
                    $order = "maintenance " . $order_dir;
                    break;
                case 7 :
                    $order = "temp " . $order_dir;
                    break;
                case 8 :
                    $order = "pcb_design " . $order_dir;
                    break;
                case 9 :
                    $order = "code_design " . $order_dir;
                    break;
                case 10 :
                    $order = "txt_design " . $order_dir;
                    break;
                case 11 :
                    $order = "prj_price " . $order_dir;
                    break;
                case 12 :
                    $order = "prjdtime " . $order_dir;
                    break;
                case 13 :
                    $order = "complete_time " . $order_dir;
                    break;
                case 14 :
                    $order = "pcb_design " . $order_dir;
                    break;
                case 15 :
                    $order = "change_num " . $order_dir;
                    break;
                default :
                    $order = "builder_name";
            }

            //分页
            $start  = $posts['start'];  //从多少开始
            $length = $posts['length']; //数据长度
            $limitFlag = isset($posts['start']) && $length != - 1 ;
            if ($limitFlag) {
                $start  = (int)$start;
                $length = (int)$length;
            }
            //搜索
            $search = $posts['search']['value'];//获取前台传过来的过滤条件

            $k = $posts['k'];
            $month = strtotime(date('Y-m-1'));
            $map['auditstatus'] = array('EQ', '3');
            $map['prostatus'] = array('EQ', "$k");
            if ($k == 3) {
                $map['finaltime'] = array('GT', $month);
            }
            $count = M('research')->where($map)->count();
            $recordsFiltered = $count;
            $field = "res.proid,
                        res.proname prj_name,
                        res.proprice bonus,
                        res.promaint maintenance,
                        res.protemp temp,
                        res.propcb pcb_design,
                        res.codedesign code_design,
                        res.docwrite txt_design,
                        res.performbonus prj_price,
                        res.protime start_time, 
                        res.deliverytime delivery_time,
                        res.prodtime prjdtime,
                        res.finaltime complete_time,
                        res.proneeds tips,
                        s.name builder_name,
                        d.name dept_name,
                        GROUP_CONCAT(pname) pname, 
                        count(cha.changeid) change_num";
            $data = M('research')->alias('res')
                ->field($field)
                ->where($map)
                ->join('LEFT JOIN crm_staff AS s ON builderid = s.id')
                ->join('LEFT JOIN `crm_dept` AS d ON  d.id = res.projectdepartment')
                ->join('LEFT JOIN `crm_resjixiao` jx ON jx.prjid = res.proid')
                ->join('LEFT JOIN `crm_reschange` cha ON cha.projid = res.proid')
                ->group('res.proid')
                ->order($order)
                ->limit($start, $length)
                ->select();


            if (count($data) != 0) {
                foreach($data as $key => $val) {
                    $info[$key] = array(
                        'DT_RowId'      => $val['proid'],
                        'pname'         => $val['pname'],
                        'dept_name'     => $val['dept_name'],
                        'prj_name'      => $val['prj_name'],
                        'start_time'    => date('Y-m-d',$val['start_time']),
                        'delivery_time' => date('Y-m-d',$val['delivery_time']),
                        'bonus'         => $val['bonus'] ? $val['bonus'] . "元" : "",
                        'maintenance'   => $val['maintenance'] ? $val['maintenance'] . "元" : "",
                        'temp'          => $val['temp'] ? $val['temp'] . "元" : "",
                        'pcb_design'    => $val['pcb_design'] ? $val['pcb_design'] . "元" : "",
                        'code_design'   => $val['code_design'] ? $val['code_design'] . "元" : "",
                        'txt_design'    => $val['txt_design'] ? $val['txt_design'] . "元" : "",
                        'prj_price'     => $val['prj_price'] ? $val['prj_price'] . "元" : "",
                        'prjdtime'      => $val['prjdtime'] ? date('Y-m-d',$val['prjdtime']) : '',
                        'complete_time' => $val['complete_time'] ? date('Y-m-d', $val['complete_time']) : '',
                        'tips'          => $val['tips'],
                        'change_num'    => $val['change_num'],
                        'builder_name'  => $val['builder_name']
                    );
                }
            } else {
                $info = "";
            }
            $info = empty($info) ? "" : $info;
            $output = array(
                "draw"            => intval($draw),
                "recordsTotal"    => $count,
                "recordsFiltered" => $recordsFiltered,
                "data"            => $info
            );
            $this->ajaxReturn($output);
        } else {
            $this->display();
        }
    }

    /**
     * 23 项目变更节点 changeProject checkChange
    */
    public function changeProject()
    {
        // (1) 相关模型
        $resJXModel   = D('resjixiao');
        $resPrjModel  = D('research');
        $staffModel   = D('staff');
        $changeModel  = D('reschange');
        // (2) 判断ajax提交
        if (IS_POST) {
            // _1 提交进行数据处理
            $posts    = I('post.');
            $postTime = time();
            $staffId  = (int)session('staffId');
            // a $posts数据处理确定需要change的内容，在reschangemodel里执行
            $msg      = $changeModel->findChangeRecord($posts, $staffId, $postTime);
            // b 事务处理修改数据库内容
            //   涉及事务的表为：项目、项目绩效、项目变更记录
            $Model   = D();
            $Model->startTrans();//开启事务处理
            // c 对信息分别处理，使得匹配数据库内容
            if ($msg != 2 && $msg != 5) {
                // 判断审核id、项目id、提交人id
                if ($msg['projid'] != '' && $msg['postId'] != '') {
                    // 1 项目变更信息获取
                    $prjDataFilter = inject_id_filter($msg['projid']);

                    $msg['auditId']         = (int)$posts['auditId'];
                    $prjData['auditstatus'] = 1;

                    if ($msg['newDeliveryTime'] != "") {
                        $prjData['deliverytime'] = inject_filter($msg['newDeliveryTime']);
                    }
                    if ($msg['newauditorid'] != "") {
                        $prjData['auditorid'] = inject_id_filter($msg['newauditorid']);
                    }
                    if ($msg['newPrjNeeds'] != "") {
                        $prjData['proneeds'] = nl2br(inject_filter($msg['newPrjNeeds']));
                    }
                    if ($msg['newBonus'] != "") {
                        $prjData['performbonus'] = (int)$msg['newBonus'];
                        $prjData['proprice']     = (int)$msg['newPrjPrice'];
                        $prjData['promaint']     = (int)$msg['newPrjMaint'];
                        $prjData['protemp']      = (int)$msg['newPrjTemp'];
                        $prjData['propcb']       = (int)$msg['newPrjPcb'];
                        $prjData['codedesign']   = (int)$msg['newDocWrite'];
                        $prjData['docwrite']     = (int)$msg['newCodeDesign'];
                    }


                    // 2 项目参与人、绩效变更数据
                    if (isset($msg['newJXVal'])) {
                        $performArr     = json_decode($msg['newJXVal']);
                        $partnerArr     = json_decode($msg['newPartner']);
                        $partNameArr    = json_decode($msg['newPartName']);
                        for($i = 0; $i  < count($performArr); $i++) {
                            $list[$i] = array(
                                'prjid'  => (int)$msg['projid'],
                                'pname'  => inject_filter($partNameArr[$i]),
                                'pid'    => inject_filter($partnerArr[$i]),
                                'jxval'  => inject_filter($performArr[$i]),
                                'status' => 1
                            );
                        }
                    }
                    // d 执行数据添加
                    $fin = $Model->table('crm_reschange')->create($msg);
                    $rst_1 = $Model->table('crm_reschange')->add($fin);

                    $flag_1 = ($rst_1 !== false) ? 1 : 2;

                    if ($prjData['auditorid'] ||$prjData['deliverytime'] || $prjData['proneeds'] || $prjData['performbonus'] || $msg) {
                        $dataOf = $Model->table('crm_research')->create($prjData);
                        $where_1['proid'] = array('EQ', $prjDataFilter);
                        $rst_2 = $Model->table('crm_research')->where($where_1)->setField($dataOf);
                        $flag_2 = ($rst_2 !== false && $rst_2 != 0) ? 1 : 2;
                    } else {
                        $flag_2 = 1;
                    }

                    if (isset($list) && $list[0]['pname']) {
                        $delFilter['prjid'] = array('EQ', $list[0]['prjid']);
                        $rst_4 = $Model->table('crm_resjixiao')->where($delFilter)->delete();
                        $rst_3 = $Model->table('crm_resjixiao')->addAll($list);
                        $flag_3 = ($rst_3 !== false && $rst_4 != false) ? 1 : 2;
                    } else {
                        $flag_3 = 1;
                    }
                    if ($flag_3 * $flag_1 * $flag_2 == 1) {
                        $Model->commit();
                        $msg = 1;
                    } else {
                        $Model->rollback();
                        $msg = 2;
                    }
                } else {
                    $msg  = 2; //4:数据中没有项目id，审核人id，提交人id的其中一个
                }
            } else { $msg = 2;}

            $this->ajaxReturn($msg);
        } else {
            // _2 没提交 获取基本数据渲染模板
            $prjId    = inject_id_filter(I('get.id'));
            // a 项目相关信息查询
            //  _a 基本情况
            $prjBasic = $resPrjModel->getPrjBasic($prjId);
            //  _b 绩效情况
            $prjJX    = $resJXModel ->getPrjJX($prjId);
            //  _c 审核人id、name
            $where['roleid'] = array('IN', '2,3,6,7');
            $auditors = $staffModel->where($where)
                ->field('id,name,deptid')
                ->select();

            //  _d 部门人员列表,项目变更不涉及跨部门
            $deptId = $prjBasic['projectdepartment'];
            $map['parent_id'] = array('EQ', $deptId);
            $group = M('dept')->where($map)->field('id,name')->select();
            $groupIds = getPrjIds($group, 'id');
            $deptIds = empty($groupIds) ? $deptId :  $deptId . "," . $groupIds;
            $staffMap['deptid'] = array('IN', $deptIds);
            $staffs = M('staff')->where($staffMap)->field('id,name')->select();
            $this->assign(array(
                'data1' => $prjBasic,
                'data2' => $prjJX,
                'data3' => $auditors,
                'data4' => $staffs
            ));
            $this->display();
        }
    }
    public function checkChange()
    {
        $model    = M('research');
        if (IS_POST) {
            $prjId = inject_id_filter(I('post.proid'));
            $map['proid'] = array('EQ', $prjId);
            $map['builderid'] = array('EQ', session('staffId'));

            $rst = $model->where($map)->field('proname')->find();
            $msg =  ($rst !== false) ? (!empty($rst) ? 1 : 2) : 2;
            $this->ajaxReturn($msg);
        }
    }

    /**
     * 24 验收项目节点  accessProject completePrj
    */
    public function accessProject()
    {
        $model = M('research');

        $data = array(
            'prostatus' => 2,
            'prodtime'  => time()
        );
        if (IS_POST) {
            $proId = I('post.proid');
            $map['proid'] = array('EQ', $proId);
            $map['builderid'] = array('EQ', $this->staffId);
            $rst = $model->where($map)->setField($data);
            $msg =  ($rst !== false) ? (($rst != 0) ? 1 : 2) : 2;
        } else {
            $msg = 2;
        }
        $this->ajaxReturn($msg);
    }
    public function completePrj()
    {
        $model = M('research');
        $data = array(
            'prostatus' => 3,
            'finaltime'  => time()
        );
        if (IS_POST) {
            $proId = I('post.proid');
            $map['builderid'] = array('EQ', $this->staffId);
            $map['proid'] = array('EQ', $proId);
            $rst = $model->where($map)->setField($data);
            $msg = ($rst !== false) ? (($rst != 0) ? 1 : 2) : 2;
        } else {
            $msg = 2;
        }
        $this->ajaxReturn($msg);
    }

    /**
     * 27 申请项目节点 selectPrj
    */
    public function selectPrj()
    {
        $posts = I('post.ids');
        foreach ($posts as $key => $val) {
            $rst = M('restype')->find($val);
            $filter['jxid'] = array('EQ', $val);
            if ($rst['status'] == 1) {
                $data['staff_id'] = $this->staffId;
                $data['status'] = '2';
                $data['staffname'] = session('nickname');
                $res[$key] = M('restype')->where($filter)->setField($data);
            }
        }

        $result = M('restype')->field('prjid')->find($posts[0]);
        $map['status'] = array('EQ', '1');
        $map['prjid'] = array('EQ', $result['prjid']);
        $jxIds = M('restype')->where($map)->field('jxid')->select();
        $num = count($jxIds);
        if ($num == 0) {
            $data['auditstatus'] = '2';
            $data['fintime'] = time();
            $condi['proid'] = array('EQ', $result['prjid']);
            M('respublic')->where($condi)->setField($data);
        }
        if ($res && !in_array(0, $res)) {
            $this->ajaxReturn(2);
        } else {
            $this->ajaxReturn(1);
        }
    }

    /**
     * 28 项目进度更新节点 updateProject updateProjectOk
     */
    public function updateProject()
    {
        /*项目进度表添加逻辑
        id是否在项目参与人list中
        是进行添加 否，提示，跳回项目列表*/
        $model    = M('resjixiao');
        $proModel = M('research');

        if (IS_POST) {
            $prjId = (int)(I('post.proid'));
            $map['prjid'] = array('EQ', $prjId);

            $rst = $model->where($map)->field('pid')->select();
            if ($rst !== false) {
                if (count($rst) != 0) {
                    foreach($rst as $val) {
                        $ids[] = $val['pid'];
                    }
                    $msg = in_array($this->staffId, $ids) ? 1 : 2;
                } else {
                    $msg = 2;
                }
            } else {
                $msg = 2;
            }
            $this->ajaxReturn($msg);
        } else {
            $proId = inject_id_filter(I('get.id'));
            $data  = $proModel->field('proname, prostatus')->find($proId);
            $data['prjid'] = $proId;
            $this->assign('data', $data);
            $this->display();
        }
    }
    public function updateProjectOk()
    {
        $model = M('resprogress');
        if (IS_POST) {
            $posts              = I('post.');
            $data['prjcontent'] = nl2br(inject_filter($posts['content']));
            $data['prjer_id']   = $this->staffId;
            $data['posttime']   = time();
            $data['theme']      = $posts['theme'];
            $data['project_id'] = inject_id_filter($posts['proid']);
            if ($data1 = $model->create($data)) {
                $rst   = $model->add($data1);
                if ($rst !== false) {
                    $msg['status']  = 2;
                    $msg['id'] = $data['project_id'];
                }
            } else {
                $msg['status']  = 3;
                $msg['content'] = '信息添加失败，联系管理员';
            }
        } else {
            $msg['status']  = 3;
            $msg['content'] = '提交了吗？问问管理员怎么回事';
        }
        $this->ajaxReturn($msg);
    }

    /**
     * 29 项目进度审核节点 checkProgress showProgressAuditList
     * @todo 项目进度审核后续根据架构调整
    */
    // 需审核项目
    public function showProgressAuditList()
    {
        // 项目参与人是下属的
        $staffIds = $this->getStaffIds(session('staffId'), 'prj_child_id', "");
        $map['id'] = array('EQ',session('staffId'));
        $staffIds = M('staff')->field('prj_child_id')->where($map)->find();
        $model = M('resprogress');
        $condition['prjer_id'] = array('in', $staffIds['prj_child_id']);
        $condition['audistatus'] = array('EQ', '1');
        $updateContents   = $model->where($condition)
            ->join('LEFT JOIN crm_staff AS sta ON sta.id = prjer_id')
            ->join('LEFT JOIN crm_research AS res ON res.proid = project_id')
            ->field('crm_resprogress.*,sta.name AS prjername,res.proname AS prjname')
            ->order('posttime DESC')
            ->select();

        $this->assign(array(
            'data'   => $updateContents,
        ));
        $this->display();
    }
    /* 项目进度审核， changeId ：id needs changed in this func;
        flag : 1, 2 which is map of ture or false, statusName is field's name,id : primary key name, table：mysql table name
    */

    /**
     * changeStatus 项目进度审核方法
     * @param array $msg 审核的一些基本参数
     * changeId需要审核的记录id, uid记录的提交人id, flag, statusName, id, table, trueValue, falseValue 数组下标
    */
    protected function changeStatus($checkArray)
    {
        $prjStaffIds = $this->getStaffIds($this->staffId, 'prj_child_id', "");
        $msg = in_array($checkArray['uid'], empty($prjStaffIds) ? array() : explode(',', $prjStaffIds)) ? 2 : 1;
        if ($msg == 2) {
            if ($checkArray['flag'] == 2) {
                $data = array(
                    $checkArray['statusName'] => $checkArray['trueValue']
                );
                $map[$checkArray['id']] = array('EQ', $checkArray['changeId']);
                $rst = M($checkArray['table'])->where($map)->setField($data);
                $ajaxMsg = $rst ? 2 : 3;
            } elseif ($checkArray['flag'] == 1) {
                $data = array(
                    $checkArray['statusName'] => $checkArray['falseValue']
                );
                // 查id而不是项目id
                $map[$checkArray['id']] = array('EQ', $checkArray['changeId']);
                $rst = M($checkArray['table'])->where($map)->setField($data);
                $ajaxMsg = $rst ? 2 : 3;
            } else {
                $ajaxMsg = 3;
            }
        } else {
            $ajaxMsg = 4;
        }
        $this->ajaxReturn($ajaxMsg);
    }
    // 项目进度审核
    public function checkProgress()
    {
        if (IS_POST) {
            // 两个参数：记录的id和是否通过。
            $progressId = inject_id_filter(I('post.prjid'));
            $flag = inject_id_filter(I('post.k')) == 3 ? 2 : inject_id_filter(I('post.k'));
            $filter['id'] = array('EQ', $progressId);
            $progressData = M('resprogress')->where($filter)->find();
            //$cfg 审核需要的一些内容
            $cfg = array(
                'changeId'   => $progressId,
                'uid'        => $progressData['prjer_id'],
                'flag'       => $flag,
                'statusName' => 'audistatus',
                'id'         => 'id',
                'table'      => 'resprogress',
                'trueValue'  => 2,
                'falseValue' => 3
            );
            $this->changeStatus($cfg);
        } else {
            $this->ajaxReturn(4);
        }
    }

    /**
     * 30 项目数据上传 uploadPrjData
     * @todo 后续可能由此需求
    */

    /**
     * 31 项目数据搜索 searchPrjData
     * @todo 后续可能由此需求
     */

    /**
     * 32 项目数据下载 uploadPrjData
     * @todo 后续可能由此需求
     */

    /**
     * 39 项目审核节点 showPrjAudit checkProject
    */
    public function showPrjAudit()
    {
        // 根据个人id查看权限下的需要审核的项目
        if (in_array(session('roleId'), array(2, 3, 6, 7))) {
            $map['crm_research.auditstatus'] = array('EQ', '1');
            $map['crm_research.auditorid'] = array('EQ', $this->staffId);
            $data = $this->getResList($map, 'research', 0);
            $this->assign('data', $data);
            $this->display();
        } else {
            $this->display();
        }
    }
    public function checkProject()
    {
        //指定审核人可以审核

        if (IS_POST) {
            $role  = (int)session('roleId');
            $proId = inject_id_filter(I('post.proid')); // 需要审核的编号
            $flag  = inject_id_filter(I('post.k')); // 审核状态码
            if (!in_array($role, array(2, 3, 6, 7))) {
                $this->ajaxReturn(4);
            } else {
                $map['proid'] = array('EQ',$proId);
                $data['auditstatus'] = $flag == 2 ? 3 : 4;
                $rst = M('research')->where($map)->setField($data);
                $msg = $rst ? 2 : 3;
                $this->ajaxReturn($msg);
            }
        } else {
            $this->ajaxReturn(4);
        }
    }

}