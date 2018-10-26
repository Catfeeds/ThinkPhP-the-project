<?php
/**
 * Created by PhpStorm.
 * User: hp
 * Date: 17-5-16
 * Time: 下午1:49
 */

namespace Dwin\Controller;

use Dwin\Model\ProductionLineModel;
use Dwin\Model\StafflogModel;
use Dwin\Model\StaffModel;
use Dwin\Model\StockIoCateModel;

use spec\Prophecy\Argument\Token\IdenticalValueTokenSpec;
class SystemController extends CommonController
{
    const CUS_RULE = 1;
    const ORDER_RULE = 28;
    const PRJ_RULE = 50;
    const ONLINE_RULE = 107;
    const SALE_RULE = 94;
    const SUCCESS_STATUS = 2;
    const FAIL_STATUS = 1;
    // 部门相关
    /**
     * showDept 部门列表
     * 显示公司组织架构
     * @param array $dept 部门名
    */
    public function showDept()
    {
        $dept = M('dept')->select();
        $dept = getTree($dept, 0, 0, 'parent_id');
        $this->assign(array(
            'dept' => $dept,
            ));
        $this->display();
    }

    /**
     * addDept 添加新部门
    */
    public function addDept()
    {
        if (IS_POST) {
            $pid = inject_id_filter(I('post.pid'));
            $addName = inject_filter(I('post.addName'));
            $map['parent_id'] = array('EQ', $pid);
            $rst = M('dept')->where($map)->find();
            $data['parent_id'] = $pid;
            $data['name'] = $addName;
            $data['level'] = isset($rst['level']) ? $rst['level'] : 5;
            $fin = M('dept')->create($data);
            $res = M('dept')->add($fin);
            $msg = $res ? self::SUCCESS_STATUS : self::FAIL_STATUS;
            $this->ajaxReturn($msg);
        } else {
            $dept = M('dept')->select();
            $dept = getTree($dept, 0, 0, 'parent_id');
            $this->assign('dept', $dept);
            $this->display();
        }
    }

    /**
     * 重命名部门名称
    */
    public function editDept()
    {
        if (IS_POST) {
            $name = inject_filter(I('post.deptName'));
            $id = inject_id_filter(I('post.deptId'));
            $pid = inject_id_filter(I('post.deptPId'));
            $map['id'] = array('EQ', $pid);
            $rstCheck = M('dept')->where($map)->find();
            $map1['parent_id'] = array('EQ', $rstCheck['id']);
            $rstNames = M('dept')->where($map1)->select();
            foreach ($rstNames as $value) {
                $deptNames[] = $value['name'];
            }
            if (in_array($name, $deptNames)) {
                $this->ajaxReturn(self::FAIL_STATUS);
            } else {
                $map2['id'] = array('EQ', $id);
                $change['name'] = $name;
                $rst = M('dept')->where($map2)->setField($change);
                $msg = $rst ? self::SUCCESS_STATUS : 3;
                $this->ajaxReturn($msg);
            }
        } else {
            $id = inject_id_filter(I('get.dId'));
            $data = M('dept')->find($id);
            $this->assign('data', $data);
            $this->display();
        }
    }

    /**
     * 组织架构变更，部门解散
     * 解散后，所有子部门删除
    */
    public function changeDept()
    {
        if (IS_POST) {
            $id = inject_id_filter(I('post.dId'));// 删除部门的id
            $delData = M('dept')->find($id);
            // 找出删除id的子id
            $map['parent_id'] = array('EQ', $id);
            $sonId = M('dept')->where($map)->field('id')->select();
            // 如果有，合并后删除
            M()->startTrans();
            if ($sonId != false && count($sonId) != 0) {
                $sonIds = getPrjIds($sonId, 'id');
                $map['parent_id'] = array('IN', $sonIds);
                $nextArray = M('dept')->where($map)->field('id')->select();
                $sonIds_2 = $nextArray ? getPrjIds($nextArray, 'id') : "";
                $sonIds = $sonIds_2 ? $sonIds . "," . $sonIds_2 : $sonIds;
                $delIds = $delData['id'] . "," . $sonIds;
            } else {
                $delIds = $delData['id'];
            }
            $delFilter['id'] = array('IN', $delIds);
            $rst = M()->table('crm_dept')->where($delFilter)->delete();
            $flag = $rst ? self::SUCCESS_STATUS : self::FAIL_STATUS;
            if ($flag == self::SUCCESS_STATUS) {
                M()->commit();
            } else {
                M()->rollback();
            }
            $this->ajaxReturn($flag);
        }
    }


    // 职位相关
    /**
     * showPosition 显示职位信息：职位名、权限、职工等
    */
    public function showPosition()
    {

        $position = M('auth_role')
            ->field('role_id id,role_name r_name,pid p_level, role_parent_id parent_id,GROUP_CONCAT(b.group_name) `rule_list`,staff_ids')
            ->join('LEFT JOIN `crm_auth_group` b ON FIND_IN_SET(b.group_id, crm_auth_role.rule_ids)')
            ->group('id')
            ->order('p_level asc,id asc')
            ->select();
        $position = getTree($position, 0, 0, 'parent_id');
        foreach($position as &$val) {
            $val['user_id'] = empty($val['staff_ids']) ? "" : "0," . $val['staff_ids'] . ",";
        }
        $staff = M('staff')->field('id staff_id, name staff_name')->select();
        $this->assign(array(
            'data'     => $position,
            'userList' => $staff
        ));
        $this->display();
    }

    /**
     * savePositionInfo 保存职位内的职员
     * @todo 只改职位下人员，人员的权限修改：1 有修改的进行权限节点修改，并记录修改的人
    */
    public function savePositionInfo()
    {
        $posts = I('post.position_data');
        M()->startTrans();
        $changeRoleIds = array();
        for ($i = 0; $i < count($posts); $i++) {
            $map[$i]['role_id'] = array('EQ', $posts[$i][0]);
            $setData[$i]['staff_ids'] = $posts[$i][1];
            /* 查询原role_id的员工 */
            $oldData = M('auth_role')->where($map[$i])->field("staff_ids")->find();
            /* 修改原职位的员工id */
            $rst[$i] = M()->table('crm_auth_role')->where($map[$i])->setField($setData[$i]);
            if ($rst[$i] === false) {
                M()->rollback();
                $msg = 3;
                $this->ajaxReturn($msg);
            }
            /* 判断是否有修改  有修改添加后台修改数据 */
            if ($rst[$i] !== 0) {
                $add[$i] = array(
                    'role_id' => $posts[$i][0],
                    'change_content' => "修改了该职位下属人员，staff_ids变为：" . $posts[$i][1] . ", 原staff_ids: " . $oldData['staff_ids'],
                    'change_id' => $this->staffId,
                    'change_time' => time()
                );
                array_push($changeRoleIds, $posts[$i][0]);
                $rst_add[$i] = M()->table('crm_auth_role_record')->add($add[$i]);
                if ($rst_add[$i] === false) {
                    M()->rollback();
                    $msg = 3;
                    $this->ajaxReturn($msg);
                }
            }
        }
        /* role表及添加修改记录完毕 */
        M()->commit();
        M()->startTrans();
        // 清空离职人员的权限。清空不在职位分配内的
        $staffWithRole = M('auth_role')->field("staff_ids")->select();
        $staffWithRoleId = getPrjIds($staffWithRole, "staff_ids");
        $userFilter['loginstatus'] = array('EQ', "1");
        $userFilter['id'] = array('NOT IN', $staffWithRoleId);
        $userFilter['_logic'] = "OR";
        $userSetData = array(
            'rule_ids' => ""
        );
        $userRes_1 = M()->table('crm_staff')->where($userFilter)->save($userSetData);
        if ($userRes_1 === false) {
            M()->rollback();
            $msg = 3;
            $this->ajaxReturn($msg);
        }
        /*----------------------------------------------------------------------------------*/
        /* auth_role表保存staff_ids成功，取出所有id的权限，赋值给staff表 * /

            /**
             * $sql 取出user的group_ids => 子查询取出user的auth_rules
             * @todo 用子查询实现了需求，后续优化考虑能否利用连表代替子查询，优化查询速度
            */
        if (!empty(implode(",", $changeRoleIds)) || 1 == 1) {
            // $changeRole = "(" . implode(",", $changeRoleIds) . ")";
            /* where 条件：后面开发完毕后加 a.role_id in $changeRole*/
            $sql = "SELECT
                tmp.uid,
                GROUP_CONCAT(c.auth_rule) user_rules
            FROM
            (
                SELECT
                        b.id uid,
                        GROUP_CONCAT(a.rule_ids) user_group_ids
                    FROM
                        crm_auth_role a
                    LEFT JOIN crm_staff b ON FIND_IN_SET(b.id, a.staff_ids)
                    WHERE
                        staff_ids != ''
                    GROUP BY
                        uid
                    ORDER BY
                        uid
                ) tmp
            LEFT JOIN crm_auth_group c ON FIND_IN_SET(
                            c.group_id,
                            tmp.user_group_ids
                        )
            GROUP BY
                uid";
            $result = M('auth_role')->query($sql);
            foreach ($result as $k => &$val) {
                $val['user_rules'] = implode(",", array_unique(explode(',',  $val['user_rules'])));
            }
            for ($i = 0; $i < count($result); $i ++) {
                $setFilter[$i]['id'] = array('EQ', $result[$i]['uid']);
                $setData[$i] = array(
                    'rule_ids' => $result[$i]['user_rules']
                );
                $res[$i] = M()->table('crm_staff')->where($setFilter[$i])->save($setData[$i]);
                if ($res[$i] === false) {
                    M()->rollback();
                    $msg = 1;
                    $this->ajaxReturn($msg);
                }
                if ($res[$i] !== 0) {
                    $staffChangeData[$i] = array(
                        'change_id' => $this->staffId,
                        'change_time' => time(),
                        'staff_id' => $result[$i]['uid'],
                        'change_content' => "修改了该员工的系统访问权限。修改为：" . $setData[$i]['rule_ids']
                    );
                    $res_add[$i] = M()->table('crm_staff_record')->add($staffChangeData[$i]);
                    if ($res_add[$i] === false) {
                        M()->rollback();
                        $msg = self::FAIL_STATUS;
                        $this->ajaxReturn($msg);
                    }
                }
            }
            M()->commit();
            $msg = self::SUCCESS_STATUS;
        } else {
            $msg = self::SUCCESS_STATUS;
        }
        // 1 修改用户数据出问题，2成功 3修改职员表数据出问题
        $this->ajaxReturn($msg);
    }


    public function addPosition()
    {
        if (IS_POST) {
            $posts = I('post.');
            $map['role_id'] = array('EQ', (int)$posts['id']);
            $levelInfo = M('auth_role')->where($map)->field('pid')->find();
            $data = array(
                'role_name'      => $posts['name'],
                'rule_ids'       => $posts['rule'],
                'role_parent_id' => $posts['id'],
                'pid'            => $levelInfo['pid'] + 1
            );
            $rst = M('auth_role')->add($data);
            $msg = $rst ? self::SUCCESS_STATUS : self::FAIL_STATUS;
            if ($rst) {
                $condition['role_name'] = array('EQ', $data['role_name']);
                $condition['role_parent_id'] = array('EQ', $data['role_parent_id']);
                $role = M('auth_role')->field('role_id')->where($condition)->find();
                $roleAddRecord = array(
                    'role_id'     => $role['role_id'],
                    'change_content' => "新职位添加操作，rule_ids变为：" . $data['rule_ids'] . ", 职位名: " . $data['role_name'],
                    'change_id'   => $this->staffId,
                    'change_time' => time()
                );
                M('auth_role_record')->add($roleAddRecord);
            }
            $this->ajaxReturn($msg);
        } else {
            $roleData  = M('auth_role')->field('role_id id,role_name name, role_parent_id parent_id')->select();
            $roleData  = getTree($roleData, 0, 0, 'parent_id');
            $groupData = M('auth_group')->field('group_id,group_name')->select();
            $this->assign(array(
                'roleData'  => $roleData,
                'groupData' => $groupData
            ));
            $this->display();
        }
    }

    protected function getPositionId($ids)
    {
        $map['role_parent_id'] = array('IN', $ids);
        $idData = M('auth_role')->where($map)->field('role_id')->select();
        $idString = (count($idData) == 0) ? "" : getPrjIds($idData, 'role_id') ;
        return $idString;
    }

    public function delPosition()
    {
        $delId = I('post.position_data');
        // 根据要删除的id,查所有下级position,如果为总经理，禁止删除
        $map['role_id'] = array('EQ', (int)$delId);
        $data = M('auth_role')->where($map)->field('pid')->find();
        switch ($data['pid']) {
            case 1 :
                $this->ajaxReturn(3);
                break;
            case 2 :
                $lv3Ids = $this->getPositionId($delId);
                if ($lv3Ids == "") {
                    $rst = M('auth_role')->where($map)->delete();
                    $msg = $rst ? self::SUCCESS_STATUS : self::FAIL_STATUS;
                } else {
                    $lv4Ids = $this->getPositionId($lv3Ids);
                    if ($lv4Ids == "") {
                        $delFilter['role_id'] = array('IN', $delId . "," . $lv3Ids);
                        $rst = M('auth_role')->where($delFilter)->delete();
                        $msg = $rst ? self::SUCCESS_STATUS : self::FAIL_STATUS;
                    } else {
                        $lv5Ids = $this->getPositionId($lv4Ids);
                        $delString = empty($lv5Ids) ? $delId . "," . $lv3Ids . "," . $lv4Ids : $delId . "," . $lv3Ids . "," . $lv4Ids . "," . $lv5Ids;
                        $delFilter['role_id'] = array('IN', $delString);
                        $rst = M('auth_role')->where($delFilter)->delete();
                        $msg = $rst ? self::SUCCESS_STATUS : self::FAIL_STATUS;
                    }
                }
                $this->ajaxReturn($msg);
                break;
            case 3 :
                $lv4Ids = $this->getPositionId($delId);
                if ($lv4Ids == "") {
                    $delFilter['role_id'] = array('IN', $delId);
                    $rst = M('auth_role')->where($delFilter)->delete();
                    $msg = $rst ? self::SUCCESS_STATUS : self::FAIL_STATUS;
                } else {
                    $lv5Ids = $this->getPositionId($lv4Ids);
                    $delString = empty($lv5Ids) ? $delId . "," . $lv4Ids : $delId . "," . $lv4Ids . "," .$lv5Ids;
                    $delFilter['role_id'] = array('IN', $delString);
                    $rst = M('auth_role')->where($delFilter)->delete();
                    $msg = $rst ? self::SUCCESS_STATUS : self::FAIL_STATUS;
                }
                $this->ajaxReturn($msg);
                break;
            case 4 :
                $lv5Ids = $this->getPositionId($delId);
                $delString = empty($lv5Ids) ? $delId : $delId . "," . $lv5Ids;
                $delFilter['role_id'] = array('IN', $delString);
                $rst = M('auth_role')->where($delFilter)->delete();
                $msg = $rst ? self::SUCCESS_STATUS : self::FAIL_STATUS;
                $this->ajaxReturn($msg);
                break;
            case 5 :
                $delFilter['role_id'] = array('IN', $delId);
                $rst = M('auth_role')->where($delFilter)->delete();
                $msg = $rst ? self::SUCCESS_STATUS : self::FAIL_STATUS;
                $this->ajaxReturn($msg);
                break;
        }
    }

    public function editPosition()
    {
        if (IS_POST) {
            $posts = I('post.');
            // 编辑职位分两部分 1 信息的编辑    2 对应权限的编辑及人员的权限更新
            M()->startTrans();
            // pid的查询 pid代表职位等级
            $map['role_id'] = array('EQ', $posts['id']);
            $pidInfo = M('auth_role')->where($map)->field('pid')->find(); // 所要更改的parent_id的等级
            $data = array(
                'role_name'      => $posts['name'],
                'rule_ids'       => $posts['rule'],
                'role_parent_id' => $posts['id'],
                'pid'            => $pidInfo['pid'] + 1,
            );
            $updateFilter['role_id'] = array('EQ', (int)$posts['getId']);
            $roleData = M('auth_role')->where($updateFilter)->field('role_id,role_name,rule_ids,pid')->find();
            /* 判断是否有内容修改，拼接修改内容 */
            $changeContent = "";
            if ($roleData['role_name'] != $data['role_name']) {
                $changeContent .= "职位名变更：由" . $roleData['role_name'] . "变为" . $data['role_name'] . ";";
            }
            if ($roleData['rule_ids'] != $data['rule_ids']) {
                $changeContent .= "职位权限变更：节点由" . $roleData['rule_ids'] . "变为" . $data['rule_ids'] . ";";
            }
            if ($roleData['role_parent_id'] != $data['role_parent_id']) {
                $changeContent .= "职位上级职位变更：id由" . $roleData['role_parent_id'] . "变为" . $data['role_parent_id'] . ";";
            }
            if ($roleData['pid'] != $data['pid']) {
                $changeContent .= "职位级别变更：pid由" . $roleData['pid'] . "变为" . $data['pid'] . ";";
            }
            /* 判断是否修改，无修改直接返回，有修改继续执行流程 */
            if (!empty($changeContent)) {
                $rst_1 = M()->table('crm_auth_role')->where($updateFilter)->setField($data);
                if ($rst_1 !== false) {
                    $roleChangeData = array(
                        'role_id'     => $roleData['role_id'],
                        'change_content' => $changeContent . "(修改人：" . session('nickname') . ")",
                        'change_id'   => $this->staffId,
                        'change_time' => time()
                    );
                    $roleChangeRst = M()->table('crm_auth_role_record')->add($roleChangeData);
                    if ($roleChangeRst === false) {
                        M()->rollback();
                        $msg = self::FAIL_STATUS;
                        $this->ajaxReturn($msg);
                    }
                    // 权限有变动 执行人员表权限修改 取当前权限 => 给staff_ids赋对应权限
                    /* 获取对应id的staff_ids 以及权限 */
                    $ruleData = M('auth_role')->where($updateFilter)
                        ->field('GROUP_CONCAT(auth_rule) rules,staff_ids')
                        ->join('LEFT JOIN crm_auth_group ON FIND_IN_SET(group_id,crm_auth_role.rule_ids)')
                        ->find();
                    if (!empty($ruleData['staff_ids'])) {
                        $staffFilter['id'] = array('IN', $ruleData['staff_ids']);
                        $ruleString = array(
                            'rule_ids' => $ruleData['rules']
                        );

                        $rst_2 = M()->table('crm_staff')->where($staffFilter)->setField($ruleString);
                        if ($rst_2 !== false) {
                            $staffChangeData = array(
                                'change_id' => $this->staffId,
                                'change_time' => time(),
                                'staff_id' => $ruleData['staff_ids'],
                                'change_content' => "批量修改员工的系统访问权限。修改为：" . $ruleString['rule_ids'] . "；修改人：" . session('nickname')
                            );
                            $staffChangeRst = M()->table('crm_staff_record')->add($staffChangeData);
                            if ($staffChangeRst !== false) {
                                M()->commit();
                                $msg = self::SUCCESS_STATUS;
                            } else {
                                M()->rollback();
                                $msg = self::FAIL_STATUS;
                            }
                        } else {
                            M()->rollback();
                            $msg = self::FAIL_STATUS;
                        }
                    } else {
                        M()->commit();
                        $msg = self::SUCCESS_STATUS;
                    }
                } else {
                    M()->rollback();
                    $msg = self::FAIL_STATUS;
                }
            } else {
                $msg = self::FAIL_STATUS;
            }
            $this->ajaxReturn($msg);
        } else {
            $id = I('get.r_id');
            $map['role_id'] = array('eq', $id);

            $roleData = M('auth_role')->where($map)->field('role_id,role_name,rule_ids,pid,role_parent_id')->find();
            $roleSel  = M('auth_role')->field('role_id id,role_name name, role_parent_id parent_id')->select();
            $roleSel  = getTree($roleSel, 0, 0, 'parent_id');
            $groupData = M('auth_group')->field('group_id,group_name')->select();
            $this->assign(array(
                'roleData'  => $roleData,
                'roleSel'   => $roleSel,
                'groupData' => $groupData
            ));
            $this->display();
        }
    }
    public function resetPwd()
    {
        $password = "abc123!@#";
        $salt = base64_encode(mcrypt_create_iv(32, MCRYPT_DEV_URANDOM));
        $options = [
            'salt' => $salt,
            'cost' => 10,
        ];
        $hash = password_hash($password, PASSWORD_DEFAULT, $options);
        $data['pwd']       = $hash;
        $data['salt']      = $salt;
        $data['errorcount']  = 0;
        $data['loginstatus'] = 2;
        $map['id'] = ['eq', (int)I('post.sid')];
        $staffModel = new StaffModel();
        $rst = $staffModel->where($map)->save($data);
        if ($rst !== false) {
            $this->returnAjaxMsg('重置成功', 200);
        }  else {
            $this->returnAjaxMsg('重置失败',404);
        }
    }

    public function addStaff()
    {
        if (IS_POST) {
            // 获取表单提交数据，I方法过滤
            $post = I('post.');
            $password = "abc123!@#";
            // 密码加密存储
            $salt = base64_encode(mcrypt_create_iv(32, MCRYPT_DEV_URANDOM));
            // php5.5以上支持的密码加密
            $options = [
                'salt' => $salt,
                'cost' => 10,
            ];
            $hash = password_hash($password, PASSWORD_DEFAULT, $options);
            $data['name']      = inject_filter($post['name']);
            $data['pwd']       = $hash;
            $data['salt']      = $salt;
            $data['deptid']    = inject_id_filter($post['userDept']);
            $data['post_id']   = inject_id_filter($post['userPost']);
            $data['username']  = inject_filter($post['staffName']);
            $data['entrytime'] = strtotime(inject_filter($post['enTime']));
            M()->startTrans();
            $rst_1 = M()->table('crm_staff')->add($data);

            $map['name'] = array('EQ', $data['name']);
            $map['pwd'] = array('EQ', $data['pwd']);
            $res = M('staff')->where($map)->field('id')->find();

            $filter['role_id'] = array('EQ', $data['post_id']);
            $staff_ids = M('auth_role')->where($filter)->field('staff_ids')->find();

            $uid['staff_ids'] = empty($staff_ids['staff_ids']) ? $res['id'] : $staff_ids['staff_ids'] . "," . $res['id'];
            $rst_2 = M()->table('crm_auth_role')->where($filter)->setField($uid);
            if ($rst_1 * $rst_2 !== false) {
                $msg = 1;
                M()->commit();
            } else {
                $msg = 2;
                M()->rollback();
            }
            $this->ajaxReturn($msg);
        } else {
            $postInfo = M('auth_role')->field('role_id id,role_name name')->select();
            $deptInfo = M('dept')->select();
            $deptInfo = getTree($deptInfo, 0, 0, 'parent_id');
            $this->assign(array(
                'postInfo' => $postInfo,
                'deptInfo' => $deptInfo
            ));
            $this->display();
        }
    }
    public function checkStaffUN()
    {
        $uname = I('post.name');
        $map['username'] = array('EQ', $uname);
        $rst = M('staff')->where($map)->field('username')->find();
        $msg = $rst ? self::SUCCESS_STATUS : self::FAIL_STATUS;
        $this->ajaxReturn($msg);
    }
    // 2 显示职员列表方法
    public function showStaff()
    {
        if (IS_AJAX) {
            $posts = I('post.');
            //获取Datatables发送的参数 必要
            $draw = $posts['draw'];
            $this->sqlCondition = $this->getSqlCondition($posts);

            //搜索
            $search = $posts['search']['value'];//获取前台传过来的过滤条件

            // 权限下的合格客户列表查询条件

            $map['crm_staff.name'] = array('LIKE', "%" . $search . "%");
            $map['dept.name']      = array('LIKE', "%" . $search . "%");
            $map['posi.role_name'] = array('LIKE', "%" . $search . "%");
            $map['_logic'] = 'OR';
            $filter['loginstatus'] = array('NEQ', "1");
            $count = M('staff')->where($filter)->count('id');
            if ($search) {
                $filter['_complex'] = $map;
            }

            $recordsFiltered =
                $search ? M('staff')->where($filter)
                ->join('LEFT JOIN `crm_dept` AS dept ON dept.id = deptid')
                ->join('LEFT JOIN `crm_auth_role` posi ON posi.role_id = post_id')
                ->field('crm_staff.id')->count() : $count;
            $data = M('staff')->where($filter)
                    ->field('crm_staff.id, crm_staff.name, entrytime,b.name loginstatus,crm_staff.roleid AS auid, deptid, dept.name AS dname, GROUP_CONCAT(posi.role_name) AS posname, username login_name')
                    ->join('LEFT JOIN `crm_dept` AS dept ON dept.id = deptid')
                    ->join('LEFT JOIN `crm_staff_login_status` AS b ON b.id = loginstatus')
                    ->join('LEFT JOIN `crm_auth_role` AS posi ON FIND_IN_SET(crm_staff.id, posi.staff_ids)')
                    ->order($this->sqlCondition['order'])
                    ->group('crm_staff.id')
                    ->limit($this->sqlCondition['start'], $this->sqlCondition['length'])
                    ->select();
            if (count($data) != 0) {
                foreach($data as $key => $val) {
                    $info[$key]['DT_RowId']    = $val['id'];
                    $info[$key]['DT_RowClass'] = 'gradeX';
                    $info[$key]['name']        = $val['name'];
                    $info[$key]['login_name']        = $val['login_name'];
                    $info[$key]['dname']       = $val['dname'];
                    $info[$key]['loginstatus']       = $val['loginstatus'];
                    $info[$key]['posname']     = $val['posname'];
                    $info[$key]['entrytime']   = date('Y-m-d',$val['entrytime']);
                    switch($val['auid']) {
                        case 0 :
                            $info[$key]['auid'] =  '无权限';
                            break;
                        case 1 :
                            $info[$key]['auid'] =  '客户审核';
                            break;
                        case 2 :
                            $info[$key]['auid'] =  '项目审核';
                            break;
                        case 3 :
                            $info[$key]['auid'] =  '客户+项目审核';
                            break;
                        case 4 :
                            $info[$key]['auid'] =  '订单审核';
                            break;
                        case 5 :
                            $info[$key]['auid'] =  '客户+订单审核';
                            break;
                        case 6 :
                            $info[$key]['auid'] =  '项目+订单审核';
                            break;
                        case 7 :
                            $info[$key]['auid'] =  '客户+项目+订单审核';
                            break;
                        case 8 :
                            $info[$key]['auid'] =  '客户+项目+订单审核';
                            break;
                    }
                    $info[$key]['options']     = "";
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
            $filter['loginstatus'] = array('EQ', 1);
            $data = M('staff')->where($filter)
                ->field('crm_staff.id, crm_staff.name, username info, b.name status')
                ->join('LEFT JOIN `crm_staff_login_status` AS b ON b.id = loginstatus')
                ->select();
            $this->assign('data', $data);
            $this->display();
        }
    }

    /**
     * getChildIds 根据当前选择的职员的职位以及需要获取的流程类别，获取对应职员列表
     * @param int $k 功能节点的id:如果客户查看流程$k = 3;订单 15 项目 22 售后34 客服36
     * @param int $level 职员的职位等级，共四级
     * @return array 低于当前职位、功能节点筛选得到的员工列表
    */
    protected function getChildIds($k, $level)
    {
        /*$sql = "SELECT
                    `id`,
                    `name`
                FROM
                    `crm_staff`
                LEFT JOIN `crm_auth_role` `position` ON FIND_IN_SET(crm_staff.id, staff_ids)
                WHERE
                    FIND_IN_SET({$k},crm_staff.rule_ids)
                AND
                    `position`.pid > {$level}
                GROUP BY `id`
                ORDER BY
                    `position`.pid ASC,
                    post_id ASC";
        return $cusStaffData = M('staff')->query($sql);*/
        $map['_string'] = "FIND_IN_SET({$k},crm_staff.rule_ids)";
        $map['position.pid'] = array('gt', $level);
        $this->field = "`id`, `name`";
        $cusStaffData = M('staff')->field($this->field)
            ->join('LEFT JOIN crm_auth_role position ON FIND_IN_SET(crm_staff.id, staff_ids)')
            ->where($map)
            ->group('`id`')
            ->order('position.pid asc, post_id asc')
            ->select();
        return $cusStaffData;
    }
    /**
     * getIds 获取对应流程的直属职员id,name
     * @param  string $ids 对应流程直属职员的主键id连接的字符串
     * @return array 查询数据库获取对应职员ids的数组
    */
    protected function getIds($ids)
    {
        $filter['id'] = array('IN', $ids);
        return empty($ids) ? array(array()) : M('staff')->where($filter)->field('id,name')->select();
    }
    /**
     * 权限编辑
     * @param int $id 被编辑权限的职员id
     * @todo 多个职位的时候可能会有level不是最高级不能选下属的情况，后续需要注意
    */
    public function roleManage()
    {
        if (IS_AJAX) {
            $posts = I('post.');
            $staffId = (int)$posts['changeId'];
            $map['id'] = array('EQ', $staffId);
            /*// 系统各功能节点权限设置
            if ($posts['rule']) {
                foreach($posts['rule'] as $k => $val) {
                    $cData[$k] = substr($val['name'], 6);
                }
                $ruleData['rule_ids'] = implode(",", $cData);
                $rst = M('staff')->where($map)->setField($ruleData);
                $msg = $rst ? 2 : 1;
                $this->ajaxReturn($msg);
            }*/
            //管理下属设置
            if ($posts['manage']) {
                $cusArray = $orderArray = $onlineArray = $saleArray = $prjArray = [];
                foreach ($posts['manage'] as $key => $val) {
                    switch($val['name']) {
                        case "cusStaffList"    :
                            array_push($cusArray, $val['value']);
                            break;
                        case "orderStaffList"  :
                            array_push($orderArray, $val['value']);
                            break;
                        case "onlineStaffList" :
                            array_push($onlineArray, $val['value']);
                            break;
                        case "saleStaffList"   :
                            array_push($saleArray, $val['value']);
                            break;
                        case "prjStaffList"    :
                            array_push($prjArray, $val['value']);
                            break;
                    }
                }
                $manageData['cus_child_id']    = count($cusArray)    == 0 ? "" : implode(",", $cusArray);
                $manageData['order_child_id']  = count($orderArray)  == 0 ? "" : implode(",", $orderArray);
                $manageData['online_child_id'] = count($onlineArray) == 0 ? "" : implode(",", $onlineArray);
                $manageData['sale_child_id']   = count($saleArray)   == 0 ? "" : implode(",", $saleArray);
                $manageData['prj_child_id']    = count($prjArray)    == 0 ? "" : implode(",", $prjArray);
                $rst = M('staff')->where($map)->setField($manageData);
                $msg = $rst ? self::SUCCESS_STATUS : self::FAIL_STATUS;
                $this->ajaxReturn($msg);
            }
        } else {
            $id = inject_id_filter(I('get.id'));
            $model = M('staff');
            $map['crm_staff.id'] = array('EQ', $id);
            // 获取要编辑的人员的职位、权限信息、
            $data = $model->where($map)
                ->join('LEFT JOIN `crm_auth_role` role ON FIND_IN_SET(crm_staff.id, role.staff_ids)')
                ->field('crm_staff.id user_id, crm_staff.name user_name,GROUP_CONCAT(role.role_name) pos_name,GROUP_CONCAT(role.role_id) pos_id,crm_staff.rule_ids')
                ->group('user_id')
                ->find();
            /*1 系统访问权限编辑*/
            //当前的权限节点
            $ruleFilter['auth_id'] = array('IN', $data['rule_ids']);
            $staffRule = M('auth_rule')->where($ruleFilter)->field('auth_id user_rule_id,rule_name user_rule_name')->select();

            /**
             * 暂时关掉获取系统节点列表以及选取相关节点功能
             * 原html为roleManage_bak.html
             *
            */
            /*//系统权限节点列表
            $ruleArray = M('auth_rule')->field('auth_id rule_id,rule_name rule_name')->select();*/

            /*2 系统管理权限编辑（选员工）*/

            // cus/order/sale/prj/online对应各个查看权限的功能节点id
            // 该员工直属的职员
            $staFilter['id'] = array('EQ', $id);
            $staManage = M('staff')->where($staFilter)->field('cus_child_id cus,order_child_id `order`,prj_child_id prj,online_child_id `online`,sale_child_id sale')->find();

            // 对应流程节点的直属直选数组
            $childIds = array(
                'cus'    => $this->getIds($staManage['cus']),
                'order'  => $this->getIds($staManage['order']),
                'prj'    => $this->getIds($staManage['prj']),
                'online' => $this->getIds($staManage['online']),
                'sale'   => $this->getIds($staManage['sale'])
            );

            $staString = array(
                'cus'    => empty(getPrjIds($childIds['cus'], 'id'))    ? "0" : "0," . getPrjIds($childIds['cus'], 'id'),
                'order'  => empty(getPrjIds($childIds['order'], 'id'))  ? "0" : "0," . getPrjIds($childIds['order'], 'id'),
                'prj'    => empty(getPrjIds($childIds['prj'], 'id'))    ? "0" : "0," . getPrjIds($childIds['prj'], 'id'),
                'online' => empty(getPrjIds($childIds['online'], 'id')) ? "0" : "0," . getPrjIds($childIds['online'], 'id'),
                'sale'   => empty(getPrjIds($childIds['sale'], 'id'))   ? "0" : "0," . getPrjIds($childIds['sale'], 'id'),
            );


            // 可选员工列表（分权限类型和职位等级 职位等级为该员工最高等级）
            // $map 为条件
            $roleLevel['role_id'] = array('IN', $data['pos_id']);
            $staffLevel = M('auth_role')->where($roleLevel)->field('min(pid) maxLevel')->select();
            $level = $staffLevel[0]['maxlevel'];
            $staffData = array(
                'cus'    => $this->getChildIds(self::CUS_RULE, $level),
                'order'  => $this->getChildIds(self::ORDER_RULE, $level),
                'prj'    => $this->getChildIds(self::PRJ_RULE, $level),
                'online' => $this->getChildIds(self::ONLINE_RULE, $level),
                'sale'   => $this->getChildIds(self::SALE_RULE, $level),
            );

            $this->assign(array(
                'staffRule'   => $staffRule,
                'staffString' => $data['rule_ids'],
                'baseInfo'    => $data,
                'cusManage'   => self::CUS_RULE,
                'orderManage' => self::ORDER_RULE,
                'prjManage'   => self::PRJ_RULE,
                'saleManage'  => self::SALE_RULE,
                'onlineManage'=> self::ONLINE_RULE,
                'childIds'    => $childIds,
                'staffData'   => $staffData,
                'staString'   => $staString
            ));// 职员权限不用在这修改，暂时关闭，$ruleArray不分配给视图层提供职员权限的修改。
            $this->display();
        }
    }

    //修改职员职位
    public function changePosition()
    {
        if (IS_POST) {
            $post = I('post.');
            $data['deptid'] = inject_id_filter($post['userDept']);
            //$data['post_id'] = inject_id_filter($post['userPost']);
            $map['id'] = array('EQ', inject_id_filter($post['uid']));
            $inf = M('staff')->create($data);
            M()->startTrans();
            $rst_1 = M()->table('crm_staff')->where($map)->setField($inf);

            $filter['role_id'] = array('EQ', $post['userPost']);
            $staff_ids = M('auth_role')->where($filter)->field('staff_ids')->find();
            $uid['staff_ids'] = empty($staff_ids['staff_ids']) ? $post['uid'] : $staff_ids['staff_ids'] . "," . $post['uid'];

            $rst_2 = M()->table('crm_auth_role')->where($filter)->setField($uid);

            ($rst_1 * $rst_2 !== false) ? M()->commit() : M()->rollback();
            $msg = ($rst_1 * $rst_2 !== false) ? 2 : 1 ;
            $this->ajaxReturn($msg);
        } else {
            $id = inject_id_filter(I('get.id'));
            $model = M('staff');
            $map['crm_staff.id'] = array('EQ', $id);
            $data = $model->where($map)
                ->join('LEFT JOIN `crm_dept` d ON d.id = crm_staff.deptid')
                ->join('LEFT JOIN `crm_auth_role` p ON FIND_IN_SET(crm_staff.id, p.staff_ids)')
                ->field('crm_staff.id AS uid, crm_staff.name AS uname, d.name AS deptname, GROUP_CONCAT(p.role_name) AS postname')
                ->find();

            $sysPostInfo = M('auth_role')
                ->field('role_id,role_name,pid')
                ->order('role_parent_id,pid')
                ->select();
            $sysDeptInfo = M('dept')->select();
            $sysDeptInfo = getTree($sysDeptInfo, 0, 0, 'parent_id');
            $this->assign(array(
                'sysPostInfo' => $sysPostInfo,
                'sysDeptInfo' => $sysDeptInfo,
                'data' => $data
            ));
            $this->display();
        }
    }
    protected function changeChildInfo($id, $childString)
    {
        M()->startTrans();
        $where['_string'] = "find_in_set({$id},$childString)";
        $staffInfo = M('staff')->where($where)->field("id,{$childString}")->select();
        if (count($staffInfo) !== 0) {
            foreach ($staffInfo as &$val) {
                $val[$childString] = implode(",",array_diff(explode(",", $val[$childString]), array('0' => $id)));
            }
        }
        for ($i = 0; $i < count($staffInfo); $i++) {
            $filter[$i]['id'] = array('EQ', $staffInfo[$i]['id']);
            $res[$i] = M()->table('crm_staff')->where($filter[$i])->setField($staffInfo[$i]);
            if ($res[$i] === false) {
                M()->rollback();
                return self::FAIL_STATUS;
            }
        }
        M()->commit();
        return self::SUCCESS_STATUS;
    }

    // 员工离职，锁定账户
    public function lockStaff()
    {
        $id = I('post.sid');
        $id = inject_id_filter($id);
        $map['id'] = array('EQ', $id);
        $changeData['loginstatus'] = 1;
        $changeData['rule_ids'] = $changeData['deptid'] = $changeData['online_child_id'] = $changeData['sale_child_id'] = $changeData['prj_child_id'] = $changeData['cus_child_id'] = $changeData['order_child_id'] = "";
        $rst = M('staff')->where($map)->setField($changeData);
        /* 账户状态禁用结果判断 */
        if ($rst !== false) {
            /* update staff 删除对应上级对该账户的查看权 */
            $changeRes =  array(
                'cus'    => $this->changeChildInfo($id, 'cus_child_id'),
                'prj'    => $this->changeChildInfo($id, 'prj_child_id'),
                'order'  => $this->changeChildInfo($id, 'order_child_id'),
                'sale'   => $this->changeChildInfo($id, 'sale_child_id'),
                'online' => $this->changeChildInfo($id, 'online_child_id'),
            );
            foreach ($changeRes as $val) {
                if ($val == self::FAIL_STATUS) {
                    $this->ajaxReturn(self::FAIL_STATUS);
                }
            }
            /* 添加操作记录 */
            $staffChangeData = array(
                'change_id'   => $this->staffId,
                'change_time' => time(),
                'staff_id'    => (int)$id,
                'change_content' => "禁用了该账户，所有权限清空。处理人：" . session('nickname')
            );
            $staffAddRst = M('staff_record')->add($staffChangeData);
            if ($staffAddRst !== false) {
                $this->ajaxReturn(self::SUCCESS_STATUS);
            } else {
                $this->ajaxReturn(self::FAIL_STATUS);
            }
        } else {
            $this->ajaxReturn(self::FAIL_STATUS);
        }
    }

    // 审核权限编辑页面
    public function editRole()
    {
        $staffModel = M('staff');
        if (IS_POST) {
            $posts = I('post.');
            $idNum_1 = count($posts['Ids']);
            $idNum_2 = count($posts['Id']);
            $model = M();
            $model->startTrans();
            if ($idNum_1 > 0) {
                // 对提交的客户审核人id进行检查
                // 原来是审核让你的不改变，原来不是的roleid + 1
                for ($i = 0; $i < $idNum_1; $i++) {
                    $id[$i] = (int)$posts['Ids'][$i];
                    $searchFilter[$i]['id'] = array('EQ', $id[$i]);
                    $thisData[$i] = M('staff')->where($searchFilter[$i])->field('id,roleid')->find();
                    if (!in_array($thisData[$i]['roleid'], array(1,3,5,7,8))) {
                        $newRole[$i] = $thisData[$i]['roleid'] + 1;
                        $changeData[$i]['roleid'] = $newRole[$i];
                        $rst_1[$i] = $model->table('crm_staff')->where($searchFilter[$i])->setField($changeData[$i]);// 新审核人权限添加
                        if ($rst_1[$i] >= 0) {
                            $flag_1[$i] = 1;
                        } else {
                            $flag_1[$i] = 0;
                        }
                    } else {
                        $flag_1[0] = 1;
                    }
                    $cusCheckIds[$i] = (int)$posts['Ids'][$i];
                }
                $fl_1 = array_product($flag_1);
                // 获取除审核列表外是否有审核人，有就roleid - 1
                $cusIdStr = implode(",", $cusCheckIds);
                $searchFilter_1['id'] = array('NOT IN', $cusIdStr);
                $searchFilter_1['roleid'] = array('IN','1,3,5,7');
                $allData = M('staff')->where($searchFilter_1)->field('id,roleid')->select();
                $num = count($allData);
                if ($num != 0) {
                    for ($i = 0; $i < $num; $i++) {
                        $where[$i]['id'] = $allData[$i]['id'];
                        $newRole_1[$i] = $allData[$i]['roleid'] - 1;
                        $setData[$i]['roleid'] = $newRole_1[$i];
                        $rst_2[$i] = $model->table('crm_staff')->where($where[$i])->setField($setData[$i]);
                        if ($rst_2[$i] >= 0) {
                            $flag_2[$i] = 1;
                        } else {
                            $flag_2[$i] = 0;
                        }
                    }
                    $fl_2 = array_product($flag_2);
                } else {
                    $fl_2 = 1;
                }
                $res_1 = $fl_1 * $fl_2;
                if ($res_1 != 1) {
                    $model->rollback();
                    $msg = 2;
                } else {
                    $model->commit();
                    $msg = 1;
                }
                $this->ajaxReturn($msg);
            } elseif ($idNum_2) {
                $model_2 = M();
                $model_2->startTrans();
                for ($i = 0; $i < $idNum_2; $i++) {
                    $id[$i] = (int)$posts['Id'][$i];
                    $searchFilter[$i]['id'] = array('EQ', $id[$i]);
                    $thisData[$i] = M('staff')->where($searchFilter[$i])->field('id,roleid')->find();
                    if (!in_array($thisData[$i]['roleid'], array(2,3,6,7,8))) {
                        $newRole[$i] = $thisData[$i]['roleid'] + 2;
                        $changeData[$i]['roleid'] = $newRole[$i];
                        $rst_1[$i] = $model->table('crm_staff')->where($searchFilter[$i])->setField($changeData[$i]);// 新审核人权限添加
                        if ($rst_1[$i] >= 0) {
                            $flag_1[$i] = 1;
                        } else {
                            $flag_1[$i] = 0;
                        }
                    } else {$flag_1[0] = 1;}
                    $cusCheckIds[$i] = (int)$posts['Id'][$i];
                }
                $fl_1 = array_product($flag_1);
                // 获取除审核列表外是否有审核人，有就roleid - 1
                $cusIdStr = implode(",", $cusCheckIds);
                $searchFilter_1['id'] = array('NOT IN', $cusIdStr);
                $searchFilter_1['roleid'] = array('IN','2,3,6,7');
                $allData = M('staff')->where($searchFilter_1)->field('id,roleid')->select();
                $num = count($allData);
                if ($num != 0) {
                    for ($i = 0; $i < $num; $i++) {
                        $where[$i]['id'] = $allData[$i]['id'];
                        $newRole_1[$i] = $allData[$i]['roleid'] - 2;
                        $setData[$i]['roleid'] = $newRole_1[$i];
                        $rst_2[$i] = $model->table('crm_staff')->where($where[$i])->setField($setData[$i]);
                        if ($rst_2[$i] >= 0) {
                            $flag_2[$i] = 1;
                        } else {
                            $flag_2[$i] = 0;
                        }
                    }
                    $fl_2 = array_product($flag_2);
                } else {
                    $fl_2 = 1;
                }
                $res_1 = $fl_1 * $fl_2;
                if ($res_1 != 1) {
                    $model->rollback();
                    $msg = 2;
                } else {
                    $model->commit();
                    $msg = 1;
                }
                $this->ajaxReturn($msg);
            } else {
                $this->ajaxReturn(3);
            }
        } else {
            $cusCheckList = "1,3,5,7,8";
            $resCheckList = "2,3,6,7,8";

            $cusFilter_1['roleid'] = array('NOT IN', $cusCheckList);
            $cusFilter_2['roleid'] = array('IN', '1,3,5,7');
            $cusData = M('staff')->where($cusFilter_1)->field('id sid,name staffname,roleid rid')->order('staffname asc')->select();
            $unCusData = M('staff')->where($cusFilter_2)->field('id sid,name staffname,roleid rid')->order('staffname asc')->select();

            $resFilter_1['roleid'] = array('NOT IN', $resCheckList);
            $resFilter_2['roleid'] = array('IN', '2,3,6,7');
            $resData = M('staff')->where($resFilter_1)->field('id sid,name staffname,roleid rid')->order('staffname asc')->select();
            $unResData = M('staff')->where($resFilter_2)->field('id sid,name staffname,roleid rid')->order('staffname asc')->select();

            $this->assign(array(
                'staffList_1' => $cusData,
                'staffList_2' => $unCusData,
                'staffList_3' => $resData,
                'staffList_4' => $unResData,
            ));
            $this->display();
        }
    }
    // 提交审核权限修改信息
    public function editOk()
    {
        $post = I('post.');
        $id = inject_id_filter($post['id']);
        $roleid = inject_id_filter($post['role']);
        $model = M('staff');
        $map['id'] = array('EQ', $id);
        $rst = $model->where($map)->setField('roleId', $roleid);
        if ($rst !==false) {
            $this->success('success', U('showStaff'), 3);
        } else {
            $this->error('error', U('editStaff',array('sid' => $id)), 3);
        }
    }


    public function editRecordTime()
    {
        if (IS_POST) {
            $newTime = I('post.timelimit');
            $data1 = array(
                'timelimit' => $newTime
            );
            $rst = M('system')->where('id=1')->setField($data1);
            if ($rst != false && $rst != 0) {
                $msg = 1;
            } else {
                $msg = 2;
            }
            $this->ajaxReturn($msg);
        }
        $data = M('system')->field('timelimit')->find();
        $this->assign('data', $data);
        $this->display();
    }

    public function showFeedBack()
    {
        $model = M('feedback');
        $data = $model
                ->join('LEFT JOIN `crm_staff` d ON staff_id = d.id')
                ->field('title,content,d.name name, crm_feedback.addtime')
                ->select();
        foreach($data as &$val) {
            $val['content'] = htmlspecialchars_decode($val['content']);
        }
        $this->assign('msg', $data);
        $this->display();

    }

    // 客户行业编辑
    public function showCusIndus()
    {
        $data = M('industry')->select();
        $indus = getTree($data, 0, 0, 'pid');
        $this->assign(array(
            'indus' => $indus
        ));
        $this->display();
    }
    public function addIndus()
    {
        if (IS_POST) {
            $pid     = I('post.pid');
            $addName = I('post.addName');
            $data['pid']  = $pid;
            $data['name'] = $addName;
            $fin = M('industry')->create($data);
            $res = M('industry')->add($fin);
            if ($res > 0) {
                $this->ajaxReturn(2);
            } else {
                $this->ajaxReturn(1);
            }
        } else {
            $map['pid'] = array('eq', 0);
            $data = M('industry')->where($map)->select();
            $this->assign(array(
                'data' => $data
            ));
            $this->display();
        }
    }

    public function editIndus()
    {
        if (IS_POST) {
            $name = I('post.deptName');
            $id   = I('post.deptId');

            $map2['id'] = array('EQ', $id);
            $change['name'] = $name;
            $rst = M('industry')->where($map2)->setField($change);
            if ($rst != false && $rst != 0) {
                $this->ajaxReturn(2);
            } else {
                $this->ajaxReturn(3);
            }

        } else {
            $id = inject_id_filter(I('get.dId'));
            $data = M('industry')->find($id);
            $this->assign('data', $data);
            $this->display();
        }
    }

    public function delIndus()
    {
        if (IS_POST) {
            $id = I('post.id');
            $filter_1['id'] = array('EQ', $id);
            $filter_2['pid'] = array('EQ', $id);
            $rst_1 = M('industry')->where($filter_1)->delete();
	    if ($dat_2) {
                $rst_2 = M('industry')->where($filter_2)->delete();
            } else {
                $rst_2 = 1;
            }
            if ($rst_1 && $rst_2) {
                $this->ajaxReturn(2);
            } else {
                $this->ajaxReturn(3);
            }
        }
    }

   /* public function addTime()
    {
        $nowData = strtotime('2017-00-00');
        $oneDay = 3600*24;
        $addArr = array();
        for ($i = 0; $i < 3650;$i ++) {
            $addArr[$i]['date_timestamp_start'] = $nowData + $oneDay * $i;
            $addArr[$i]['date_timestamp_end'] = $nowData + ($oneDay * ($i + 1)) - 1;
            $addArr[$i]['date_name'] = date('Y-m-d',$nowData + $oneDay * $i);
            $addArr[$i]['week'] = date('w',$nowData + $oneDay * $i);
        }
        M('schedule_manage')->addAll($addArr);
    }*/
    public function getSchedule()
    {
        $this->posts = I('post.');
        $filter['date_timestap_start'] = array(array('gt',strtotime($this->post['start'])),array('lt',strtotime($this->post['end'])));
        $field = "date_id id,from_unixtime(date_timestamp_start) start,info title,color_id color,flag";
        $data  = D('schedule_manage')->selectScheduleData($filter,$field);
        foreach($data as &$val) {
            $val['allDay'] = 1;
        }
        $this->ajaxReturn($data);
    }
    public function editSchedule()
    {
        $this->posts = I('post.');
        $map['date_id'] = array('EQ',(int)$this->posts['id']);
        $updateData = array(
            'date_id' => (int)$this->posts['id'],
            'info'    => $this->posts['title'],
            'flag'    => $this->posts['flag'],
            'staff_id' => $this->staffId,
        );
        $rst = D('schedule_manage')->setScheduleData($map,$updateData);
        if ($rst !== false) {
            $msg['status'] = 200;
            $msg['msg'] = "修改完毕";
        } else {
            $msg['status'] = 400;
            $msg['msg'] = "修改出错";
        }
        $this->ajaxReturn($msg);
    }
    public function calendar()
    {
        $this->display();
    }

    /**
     * author:  杨超
     * date:    2018-3-14 09:26:28
     * 出入库分类CURD
     */
    public function stockIoCate()
    {
        $model = new StockIoCateModel();
        if (IS_POST){
            $method = I('post.method');
            //增加
            if ($method == 'add'){
                $result = $model -> create();
                if (!$result) {
                    $this->ajaxReturn([
                        'status' => -1,
                        'msg' => $model -> getError()
                    ]);
                }
                $result = $model -> add();
                if ($result) {
                    $this->ajaxReturn([
                        'status' => 1,
                        'msg'    => '添加成功'
                    ]);
                } else {
                    $this->ajaxReturn([
                        'status' => -1,
                        'msg' => '添加失败'
                    ]);
                }
            }
            if ($method == 'del'){
                $result = M('stock_io_cate') -> delete(I('post.id'));
                if ($result) {
                    $this->ajaxReturn([
                        'status' => 1,
                        'msg'    => '删除成功'
                    ]);
                } else {
                    if ($result) {
                        $this->ajaxReturn([
                            'status' => -1,
                            'msg' => '删除失败'
                        ]);
                    }
                }
            }
            if ($method == 'edit'){
                $result = $model -> create();
                if (!$result) {
                    $this->ajaxReturn([
                        'status' => -1,
                        'msg' => $model -> getError()
                    ]);
                }
                $result = $model -> save();
                if ($result) {
                    $this->ajaxReturn([
                        'status' => 1,
                        'msg'    => '修改成功'
                    ]);
                } else {
                    if ($result) {
                        $this->ajaxReturn([
                            'status' => -1,
                            'msg' => '修改失败'
                        ]);
                    }
                }
            }
        } else {
            $data = $model -> index();
            $inputCate = $data['iCate'];
            $outputCate = $data['oCate'];
            $this->assign(compact('inputCate','outputCate'));
            $this->display();
        }
    }

    /**
     * 备货方式管理
     * @author yang
     *
     */
    public function stockCate()
    {
        $model = M('stock_cate');
        if (IS_POST){
            $method = I('post.method');
            //增加
            if ($method == 'add'){
                $result = $model -> create();
                if (!$result){
                    $this->ajaxReturn([
                        'status' => -1,
                        'msg' => $model -> getError()
                    ]);
                }
                $result = $model -> add();
                if ($result){
                    $this->ajaxReturn([
                        'status' => 1,
                        'msg'    => '添加成功'
                    ]);
                }else {
                    $this->ajaxReturn([
                        'status' => -1,
                        'msg' => '添加失败'
                    ]);
                }
            }
            if ($method == 'del'){
                $result = $model -> delete(I('post.id'));
                if ($result){
                    $this->ajaxReturn([
                        'status' => 1,
                        'msg'    => '删除成功'
                    ]);
                }else {
                    if ($result) {
                        $this->ajaxReturn([
                            'status' => -1,
                            'msg' => '删除失败'
                        ]);
                    }
                }
            }
            if ($method == 'edit'){
                $result = $model -> create();
                if (!$result){
                    $this->ajaxReturn([
                        'status' => -1,
                        'msg' => $model -> getError()
                    ]);
                }
                $result = $model -> save();
                if ($result){
                    $this->ajaxReturn([
                        'status' => 1,
                        'msg'    => '修改成功'
                    ]);
                }else {
                    if ($result) {
                        $this->ajaxReturn([
                            'status' => -1,
                            'msg' => '修改失败'
                        ]);
                    }
                }
            }
        }else{
            $data = $model -> select();
            $this->assign(compact('data'));
            $this->display();
        }
    }

    /**
     * 生产公司管理
     * @author yang
     *
     */
    public function productionCompany()
    {
        $model = M('production_company');
        if (IS_POST){
            $method = I('post.method');
            //增加
            if ($method == 'add'){
                $result = $model -> create();
                if (!$result){
                    $this->ajaxReturn([
                        'status' => -1,
                        'msg' => $model -> getError()
                    ]);
                }
                $result = $model -> add();
                if ($result){
                    $this->ajaxReturn([
                        'status' => 1,
                        'msg'    => '添加成功'
                    ]);
                }else {
                    $this->ajaxReturn([
                        'status' => -1,
                        'msg' => '添加失败'
                    ]);
                }
            }
            if ($method == 'del'){
                $result = $model -> delete(I('post.id'));
                if ($result){
                    $this->ajaxReturn([
                        'status' => 1,
                        'msg'    => '删除成功'
                    ]);
                }else {
                    if ($result) {
                        $this->ajaxReturn([
                            'status' => -1,
                            'msg' => '删除失败'
                        ]);
                    }
                }
            }
            if ($method == 'edit'){
                $result = $model -> create();
                if (!$result){
                    $this->ajaxReturn([
                        'status' => -1,
                        'msg' => $model -> getError()
                    ]);
                }
                $result = $model -> save();
                if ($result){
                    $this->ajaxReturn([
                        'status' => 1,
                        'msg'    => '修改成功'
                    ]);
                }else {
                    if ($result) {
                        $this->ajaxReturn([
                            'status' => -1,
                            'msg' => '修改失败'
                        ]);
                    }
                }
            }
        }else{
            $data = $model -> select();
            $this->assign(compact('data'));
            $this->display();
        }
    }

    /**
     * 生产线管理
     * @author yang
     *
     */
    public function productionLine()
    {
        $model = M('production_line');
        if (IS_POST){
            $method = I('post.method');
            //增加
            if ($method == 'add'){
                $result = $model -> create();
                if (!$result){
                    $this->ajaxReturn([
                        'status' => -1,
                        'msg' => $model -> getError()
                    ]);
                }
                if (empty($result['production_line'])) {
                    $this->returnAjaxMsg('未填写产线名称', -1);
                }
                $result = $model -> add();
                if ($result){
                    $this->ajaxReturn([
                        'status' => 1,
                        'msg'    => '添加成功'
                    ]);
                }else {
                    $this->ajaxReturn([
                        'status' => -1,
                        'msg' => '添加失败'
                    ]);
                }
            }
            if ($method == 'del'){
                $data['id'] = I('post.id');
                $data['is_del'] = ProductionLineModel::$isDel;
                $result = $model -> save($data);
                if ($result){
                    $this->ajaxReturn([
                        'status' => 1,
                        'msg'    => '删除成功'
                    ]);
                }else {
                    if ($result) {
                        $this->ajaxReturn([
                            'status' => -1,
                            'msg' => '删除失败'
                        ]);
                    }
                }
            }
            if ($method == 'edit'){
                $result = $model -> create();
                if (!$result){
                    $this->ajaxReturn([
                        'status' => -1,
                        'msg' => $model -> getError()
                    ]);
                }
                $result = $model -> save();
                if ($result){
                    $this->ajaxReturn([
                        'status' => 1,
                        'msg'    => '修改成功'
                    ]);
                }else {
                    if ($result) {
                        $this->ajaxReturn([
                            'status' => -1,
                            'msg' => '修改失败'
                        ]);
                    }
                }
            }
        }else{
            $map['pid'] = ['eq', 0];
            $map['is_del'] = ['EQ',ProductionLineModel::$notDel];
            $data = $model ->where($map)-> select();
            $this->assign(compact('data'));
            $this->display();
        }
    }

    public function logIndex()
    {
        if (IS_POST) {
            $staffLogModel = new StafflogModel();
            $this->posts = I('post.');
            $this->sqlCondition = $this->getSqlCondition($this->posts);

            $count = M('stafflog')->where($this->whereCondition)->count('id');
            if ($this->sqlCondition['search']) {
                $this->whereCondition['name|position|department|education|birth_place'] = ['like', '%' . $this->sqlCondition['search'] . '%'];
            }

            $data = $staffLogModel->getIndexData($this->whereCondition, $this->sqlCondition);
            foreach ($data as &$val) {
                $val['DT_RowId'] = $val['request_time'];
            }
            $filterCount = $staffLogModel->where($this->whereCondition)->count('id');

            $this->ajaxReturn($this->getDataTableOut($this->posts['draw'], $count, $filterCount, $data));
        } else {
            $this->display();
        }
    }

    public function accountManager()
    {
        if (IS_POST){
            $params = I('post.');
            $tableData = $this->dataTable($params);
            $model = M('staff');
            $data['draw'] = (int) $params['draw'];
            $data['recordsTotal'] = $model -> where($tableData['map']) -> count();
            $tableData['map']['loginstatus'] = ['EQ', 3];
            $data['recordsFiltered'] = $model -> where($tableData['map']) -> count();
            $data['data'] = $model
                -> field('id, name, phone, errorcount, loginstatus')
                -> where($tableData['map'])
                -> order($tableData['order'])
                -> limit($params['start'], $params['length'])
                -> select();

            foreach ($data['data'] as $key => &$value) {
                $value['change_time'] = date('Y-m-d H:i:s', $value['change_time']);
                $value['exec_time'] = date('Y-m-d H:i:s', $value['exec_time']);
            }
            $this->ajaxReturn($data);
        }else{
            $this->display();
        }
    }

    public function unlockStaffAccount($id)
    {
        $map = [
            'id' => ['EQ', $id],
            'loginstatus' => ['NEQ', 1],
        ];
        $data = [
            'errorcount' => 0,
            'loginstatus' => 2
        ];
        $res = M('staff') -> where($map) -> save($data) === false ? false : true;
        if ($res){
            $this->ajaxReturn([
                'status' => self::SUCCESS_STATUS,
                'msg' => '修改成功'
            ]);
        }else{
            $this->ajaxReturn([
                'status'  => self::FAIL_STATUS,
                'msg' => '修改失败'
            ]);
        }
    }

    protected function dataTable($params, $_map = [])
    {
        $dataField = [];
        $searchAble = [];
        foreach ($params['columns'] as $k => $v) {
            if (isset($_map[$v['data']])){
                $dataField[] = $_map[$v['data']];
            }else{
                $dataField[] = $v['data'];
            }
            if ($v['searchable'] == 'true'){
                if (isset($_map[$v['data']])){
                    $searchAble[] = $_map[$v['data']];
                }else{
                    $searchAble[] = $v['data'];
                }
            }
        }
        $order = $dataField[$params['order'][0]['column']] . ' ' . $params['order'][0]['dir'];
        if ($params['search']['value'] == ''){
            $map = [];
        }else{
            $searchAble = rtrim(implode('|', $searchAble), '|');
            $word = $params['search']['value'];
            $map = [$searchAble => ['LIKE',"%".$word."%"]];
        }
        return [
            'order' => $order,
            'map' => $map,
        ];
    }
}
