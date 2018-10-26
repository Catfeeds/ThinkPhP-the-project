<?php
/**
 * Created by PhpStorm.
 * User: ml
 * Date: 2017/8/24
 * Time: 15:15
 */

namespace Dwin\Model;
 


use Think\Exception;
use Think\Model;

class CustomerModel extends Model
{
    /* 成功状态*/
    const OK_STATUS = 200;
    /* 失败状态*/
    const FAIL_STATUS = 404;
    /* 客户等级默认值*/
    const CUS_LEVEL_DEFAULT = 4;
    /*客户添加数据*/
    protected $cusAddData;
    /*条件字段*/
    protected $field;

    /**
     * @name showOwnCustomerList
     * @abstract 获取符合条件的客户，在一定时间内的各个记录数
     * @param mixed $where 筛选条件
     * @param int $time 时间戳
     * @return mixed 返回一条sql 子查询。
    */
    public function showOwnCustomerList($where, $field)
    {
        $subQuery = $this->where($where)
            ->join('LEFT JOIN crm_staff AS a ON crm_customer.uid = a.id')
            ->join('LEFT JOIN crm_industry AS ind ON crm_customer.ctype = ind.id')
            ->field($field)
            ->buildSql();
        return $subQuery;
        
    }


    public function getOwnCustomerWithMap($config)
    {
        $cfg = array (
            'time'          => $config['timeLimit'],
            'cusWithPid'    => $config['cusLimit'],
            'cusWithKpi'    => $config['kpiLimit'],
            'cusWithStaff'  => $config['staffLimit'],
            'cusWithSearch' => $config['searchLimit']
        );
        $map = [
            'cstatus' => ['eq', '2'],
            'auditstatus' => ['eq', '3'],
            'uid'         => ['in', $cfg['cusWithStaff']],
            'kpi_flag'    => $cfg['cusWithKpi'] ? ['eq', 1] : ['lt', 3]
        ];
        if ($config['searchFlag']) {
            $map['a.name|crm_customer.cname|ind.name'] = ['like', '%' . $cfg['cusWithSearch'] . '%'];
        }
        if ($cfg['cusWithPid']) {
            $map['cus_pid'] = ['exp', 'is null'];
        }


        $field = "
            crm_customer.kpi_flag,
            crm_customer.cid,
            crm_customer.cname,
            from_unixtime( max_order_time ) level,
            crm_customer.total_order_price titotal, 
            crm_customer.`four-month_order_amount` total_amount,
            a.name AS pname,
            ind.name indus,
            (SELECT IFNULL(count(b.cid),0) recordnum
                FROM `crm_contactrecord` AS b 
                WHERE b.customerid=crm_customer.cid AND posttime > {$cfg['time']}) AS countrecord,
            (SELECT IFNULL(count(b.sid),0)
                FROM `crm_salerecord` AS b 
                WHERE b.cusid=crm_customer.cid AND b.is_ok != 4 AND b.change_status_time > ({$cfg['time']})) AS sumservice,
            (SELECT IFNULL(count(c.id),0)
                FROM `crm_onlineservice` AS c 
                WHERE c.customer_id=crm_customer.cid AND c.addtime > ({$cfg['time']})) AS sumonline,
            (SELECT IFNULL(count(c.id),0)
                FROM `crm_onlineservice` AS c 
                WHERE c.customer_id=crm_customer.cid AND c.austatus='1' AND c.addtime > ({$cfg['time']})) AS uncheckonline";
        $buildSql = $this->showOwnCustomerList($map, $field);
        return $buildSql;
    }
    public function getWarningCusNum($config)
    {
        $cfg = array (
            'cusWithKpi'    => $config['kpiLimit'],
            'cusWithStaff'  => $config['staffLimit'],
            'cusWithSearch' => $config['searchLimit']
        );
        $time2 = time() - 86400 * 21;
        $threeMonthTime = strtotime("-3 months", time());
        $eightMonthTime = strtotime("-8 months", time());
        $sql = "SELECT count(cid) num 
                FROM
                    crm_customer
                    LEFT JOIN crm_staff AS a ON crm_customer.uid = a.id
                    LEFT JOIN crm_industry AS ind ON crm_customer.ctype = ind.id 
                WHERE
                    cus_pid IS NULL 
                    AND cstatus = '2'
                    AND auditstatus = '3'
                    AND uid IN ({$cfg['cusWithStaff']})";
        if ($cfg['cusWithKpi']) {
            $sql .= " AND kpi_flag = 1 ";
        }
        if ($config['searchFlag']) {
            $search = "'%" . $cfg['cusWithSearch'] . "%'";
            $sql .= " AND ( a.name LIKE " . $search . " OR cname LIKE " . $search . " OR ind.name LIKE " . $search . " ) ";
        }

        $sql .= " AND ( max_contact_time < {$time2} OR max_order_time < ( CASE has_order WHEN 1 THEN {$threeMonthTime} ELSE {$eightMonthTime} END ) )";
        $num  = $this->query($sql);
        return $num[0]['num'];

    }


    public function getWarningCusData($sqlCondition, $config)
    {
        $cfg = array (
            'time'          => $config['timeLimit'],
            'cusWithPid'    => $config['cusLimit'],
            'cusWithKpi'    => $config['kpiLimit'],
            'cusWithStaff'  => $config['staffLimit'],
            'cusWithSearch' => $config['searchLimit']
        );

        $time  = time() - 86400 * 30;
        $time2 = time() - 86400 * 21;
        $threeMonthTime = strtotime("-3 months", time());
        $eightMonthTime = strtotime("-8 months", time());
        $sql = "SELECT
                    kpi_flag,
                    cid,
                    cname,
                    has_order,
                    from_unixtime( max_contact_time ) max_contact_time,
                    from_unixtime( max_order_time ) max_order_time,
                    a.name AS pname,
                    ind.name indus,
                    ind.id indusid,
                    total_order_price titotal,
                    `four-month_order_amount` total_amount,
                    (
                    SELECT
                        IFNULL( count( b.cid ), 0 ) recordnum 
                    FROM
                        `crm_contactrecord` AS b 
                    WHERE
                        b.customerid = crm_customer.cid 
                        AND posttime > ( {$time} ) 
                    ) AS countrecord 
                FROM
                    crm_customer
                    LEFT JOIN crm_staff AS a ON crm_customer.uid = a.id
                    LEFT JOIN crm_industry AS ind ON crm_customer.ctype = ind.id 
                WHERE
                    cus_pid IS NULL 
                    AND cstatus = '2' 
                    AND auditstatus = '3'
                    AND uid in ({$cfg['cusWithStaff']})";
        if ($cfg['cusWithKpi']) {
            $sql .= " AND kpi_flag = 1 ";
        }
        if ($sqlCondition['search']) {
            $search = "'%" . $sqlCondition['search'] . "%'";
            $sql .= " AND ( a.name LIKE " . $search . " OR cname LIKE " . $search . " OR ind.name LIKE " . $search . " ) ";
        }

        $sql .= " AND ( max_contact_time < {$time2} OR max_order_time < ( CASE has_order WHEN 1 THEN {$threeMonthTime} ELSE {$eightMonthTime} END ) )
            ORDER BY {$sqlCondition['order']} 
            LIMIT {$sqlCondition['start']}, {$sqlCondition['length']}";
        return $data = $this->query($sql);

    }
    /**
     * @name filterCus
     * @abstract 根据子查询结果，以$changeFilter作为条件查询得到满足的结果
     * @param mixed $changeFilter 资产讯筛选条件
     * @param mixed $subQuery 子查询语句
     * @param string $order 排序
     * @param int $start limit查询起始位置
     * @param int $length limit查询长度
     * @return array $data 结果数组
     */
    public function filterCus($changeFilter,$subQuery, $order, $start, $length)
    {

        return $this->table($subQuery.' k')->where($changeFilter)->field('k.*')->order($order)->limit($start, $length)->select();

    }


    /**
     * @name showAllCustomerList
     * @return $data 获取客户的相关信息
     * @todo 该方法在Controller无使用，防止出错不要删除
     */
    public function showAllCustomerList($where, $time, $order, $start, $length)
    {
        $data = $this->where($where)
            ->join('LEFT JOIN crm_staff AS a ON crm_customer.uid=a.id')
            ->join('LEFT JOIN crm_industry AS ind ON crm_customer.ctype=ind.id')
            ->field("crm_customer.cid,crm_customer.cname,crm_customer.clevel,crm_customer.total_order_price titotal,crm_customer.`four-month_order_amount` total_amount,
            a.name AS uname,ind.name indus,ind.id indusid,
                                        (SELECT IFNULL(count(b.cid),0) recordnum
                                            FROM `crm_contactrecord` AS b 
                                            WHERE b.customerid = crm_customer.cid AND posttime > ({$time})) AS countrecord,
                                        (SELECT IFNULL(SUM(t.acount),0) 
                                            FROM 
                                            (SELECT *,
                                                (SELECT count(*) 
                                                    FROM `crm_resprogress` AS pro 
                                                    WHERE pro.project_id = crm_research.proid AND pro.posttime > ({$time})) 
                                                AS acount FROM `crm_research`) AS t 
                                            WHERE t.customerid = crm_customer.cid) AS prosum,
                                        (SELECT IFNULL(count(b.sid),0)
                                            FROM `crm_salerecord` AS b 
                                            WHERE b.cusd=crm_customer.cid AND b.change_status_time > ({$time})) AS sumservice,
                                        (SELECT IFNULL(count(c.id),0)
                                            FROM `crm_onlineservice` AS c 
                                            WHERE c.customer_id=crm_customer.cid AND c.addtime > ({$time})) AS sumonline,
                                        (SELECT IFNULL(count(c.id),0)
                                            FROM `crm_onlineservice` AS c 
                                            WHERE c.customer_id=crm_customer.cid AND c.austatus='1' AND c.addtime > ({$time})) AS uncheckonline,
                                        (SELECT IFNULL(max(d.order_addtime),0)
                                            FROM `crm_orderform` AS d 
                                            WHERE d.cus_id=crm_customer.cid) AS odtime")
            ->order($order)->limit($start, $length)
            ->select();
        return $data;
    }


    /**
     * @name getCommonCustomerList
     * @abstract 公共客户列表数据查询
     * @param mixed $where 筛选条件
     * @param string $order 排序
     * @param int $start limit查询起始位置
     * @param int $length limit查询长度
     * @return mixed 返回一条sql 子查询。
     */
    public function getCommonCustomerList($where, $order, $start, $length)
    {
        $subQuery = $this->field('crm_customer.cid,crm_customer.cname cus_name,crm_customer.cphonenumber,
                crm_customer.province,crm_customer.ctype,
                crm_customer.csource,crm_customer.clevel cus_level,from_unixtime(crm_customer.addtime) add_time,crm_customer.founderid,
                crm_staff.name builder_name,crm_industry.name indus,d.cname sub_name')
                   ->where($where)
                   ->join('LEFT JOIN crm_staff ON crm_staff.id = crm_customer.founderid')
                   ->join('LEFT JOIN crm_industry ON crm_industry.id = crm_customer.ctype')
                   ->join('LEFT JOIN crm_customer d ON d.cid = crm_customer.cus_pid')
                   ->order($order)
                   ->limit($start, $length)
                   ->buildSql();
        return $subQuery;
    }

    /**
     * @name getCommonCustomerData
     * @abstract 公共客户列表数据查询
     * @param mixed $subQuery 子查询语句
     * @return array $data 连表了客户，查询客户池里的数据
     */
    public function getCommonCustomerData($subQuery)
    {

        $data = $this->table($subQuery.' k')
            ->join('LEFT JOIN crm_customer cus ON cus.cus_pid = k.cid')
            ->field('k.*, GROUP_CONCAT(cus.cname) son_name')
            ->group('k.cid')
            ->select();
        return $data;
    }

    /**
     * @name getCusBaseInfo
     * @abstract 单表查单一客户表数据
     * @param array $map 查询条件
     * @param string $field 查询字段
     * @return array $cusBaseInfo
    */
    public function getCusBaseInfo($map, $field)
    {
        return $cusBaseInfo = $this->field($field)
            ->join('LEFT JOIN crm_staff b ON b.id = crm_customer.founderid')
            ->join('LEFT JOIN crm_industry ON crm_industry.id = crm_customer.ctype')
            ->where($map)
            ->find();
    }

    /**
     * @name getCusBaseInfoMulti
     * 不连表获取客户表数据
     * @param array $map 查询条件
     * @param string $field 查询字段
     * @return array $data 二维数组
    */
    public function getCusBaseInfoMulti($map, $field)
    {
        return $data = $this->where($map)->field($field)->select();
    }

    /**
     * 获取上级客户表信息，并查询到负责人姓名，后续可增加别的字段需求。
     * @param $map
     * @param $field
     * @return mixed
     */
    public function getUpCustomerMsg($map, $field){
        $data = $this->alias('c')
            ->field($field)
            ->join("left join crm_staff cs on cs.id = c.uid")
            ->join("left join crm_customer u on u.cid = c.cus_pid")
            ->join("left join crm_staff us on us.id = u.uid")
            ->where($map)
            ->select();
        return $data;
    }

    /**
     * 获取下级客户表信息，并查询到负责人姓名，后续可增加别的字段需求。
     * @param $map
     * @param $field
     * @return mixed
     */
    public function getLoadCustomerMsg($map, $field){
        $data = $this->alias('c')
            ->field($field)
            ->join("left join crm_staff cs on cs.id = c.uid")
            ->join("left join crm_customer u on u.cus_pid = c.cid")
            ->join("left join crm_staff us on us.id = u.uid")
            ->where($map)
            ->select();
        return $data;
    }

    public function getBusListNAudit($condi,$field, $start, $length, $group='cid', $order)
    {
        return $data = $this->where($condi)
            ->join('LEFT JOIN`crm_staff` AS b ON crm_customer.uid = b.id')
            ->join('LEFT JOIN `crm_staff` AS c ON crm_customer.auditorid = c.id')
            ->join('LEFT JOIN `crm_industry` `ind` ON crm_customer.ctype = ind.id')
            ->field($field)
            ->group($group)
            ->limit($start, $length)
            ->order($order)
            ->select();
    }

    public function getAuditCustomer()
    {
        $time = time();
        $condi['crm_customer.auditorid'] = array('EQ', session('staffId'));
        $condi['crm_customer.auditstatus'] = array('IN', '1,2');
        $this->field = "crm_customer.*,ifnull(cus.cname,'非子公司') child_cus,b.name AS uname,c.name AS auditorname,ind.name indus,
                                        (SELECT IFNULL(COUNT(record.cid),0) 
                                            FROM `crm_contactrecord` record 
                                            WHERE record.customerid = crm_customer.cid AND record.posttime > ({$time} - 86400*7)) AS recordnum1";
        $order = '`crm_customer`.addtime asc, crm_customer.cname DESC';
        return $data = $this->where($condi)
            ->join('LEFT JOIN`crm_staff` AS b ON crm_customer.uid = b.id')
            ->join('LEFT JOIN `crm_staff` AS c ON crm_customer.auditorid = c.id')
            ->join('LEFT JOIN `crm_industry` `ind` ON crm_customer.ctype = ind.id')
            ->join('LEFT JOIN crm_customer cus ON crm_customer.cus_pid = cus.cid and crm_customer.cus_pid is not null')
            ->field($this->field)
            ->group('crm_customer.cid')
            ->limit(0, 500)
            ->order($order)
            ->select();
    }
    /**
     * @name getCusNameInfo
     * 连表查客户数据（负责人，关联公司）
     * @param array $map 查询条件
     * @param string $field 查询字段
     * @return array $msg $msg['status'] == 1 时，查到了客户重名信息
     */
    public function getCusUNameInfo($map, $field)
    {
        $rst = M('customer')->where($map)
            ->join(' LEFT JOIN `crm_staff` c ON crm_customer.uid = c.id AND crm_customer.uid IS NOT NULL')
            ->join(' LEFT JOIN `crm_customer` b ON b.cid = crm_customer.cus_pid AND crm_customer.cus_pid IS NOT NULL')
            ->field($field)
            ->select();
        if ($rst) {
            for ($i = 0; $i < count($rst); $i++) {
                $rst[$i]['out'] =
                    empty($rst[$i]['parent_name'])
                        ?
                        (empty($rst[$i]['u_name'])
                            ?
                            '<br> '. $rst[$i]['c_name']
                            :
                            '<br> '. $rst[$i]['c_name'] . "(负责人：". $rst[$i]['u_name'] . ")")
                        :
                        (empty($rst[$i]['u_name'])
                            ?
                            '<br> '. $rst[$i]['c_name'] . "(有上级公司：" . $rst[$i]['parent_name'] .")"
                            :
                            '<br> '. $rst[$i]['c_name'] . "(有上级公司：" . $rst[$i]['parent_name'] . ",负责人：". $rst[$i]['u_name'] . ")");
            }
            $msg['status'] = 1;
            $msg['msg'] = getPrjIds($rst,'out');
        } else {
            $msg['status'] = 4;
            $msg['msg'] = "系统已有该客户，未检索到负责人，请前往公共池查找";
        }
        return $msg;
    }


    /**
     * recordCusChangeData 客户表添加操作执行
     * @param [array] $cusFilter 客户筛选条件
     * @param [string] $changeReason 客户表变更的内容字符串
     * @param [int] $changeId 修改人id
     * @return [array] $msg 返回信息和状态
    */
    public function recordCusChangeData($cusFilter, $changeReason,$changeId)
    {
        $cusBaseInfo = $this->getCusBaseInfo($cusFilter, 'cid,cname');
        if (!$cusBaseInfo) {
            $msg = array(
                'msg'    => "未查询到数据",
                'status' => self::FAIL_STATUS
            );
        } else {
            $recordData = array(
                'cusid'         => $cusBaseInfo['cid'],
                'changetime'    => time(),
                'change_reason' => $changeReason,
                'change_id'     => $changeId,
                'oldname'       => $cusBaseInfo['cname']
            );
            $rst = M()->table('crm_cuschangerecord')->add($recordData);
            if ($rst !== false) {
                $msg = array(
                    'msg'    => "添加成功",
                    'status' => self::OK_STATUS
                );
            } else {
                $msg = array(
                    'msg'    => "添加记录失败",
                    'status' => self::FAIL_STATUS
                );
            }
        }
        return $msg;

    }

    /**
     * 地址数据转json
    */
    public function getJsonAddress($data)
    {
        $arr = array();
        for ($i = 3; $i > 0; $i--) {
            if ($data['street' . $i] != "") {
                $$i = ($data['street' . $i]);
                array_push($arr, $$i);
            }
        }
        return json_encode($arr);
    }

    /**
     * @name addCus
     * @abstract 添加客户操作，$data 有特殊要求，具体请看@example
     * @param array $data 添加时的数据（非默认）
     * @example
     * $data = array(
            'cname' => ,
            'cphonenumber' => ,
            'cusfcontact' => ,
            'cphoneposition' => ,
            'csource' => ,
            'street1' => ,
            'street2' => ,
            'street3' => ,
            'detail' => ,
            'auditorid' => ,
            'cusType' => ,
            'website' => ,
            'sub_cus' => ,
     * );
     * @param int $staffId 添加人id
     * @return int $rst
     *
     */
    public function addCus($data, $staffId)
    {
        $this->cusAddData = array(
            'cname'          => trim($data['cname']),
            'cphonenumber'   => trim($data['cphonenumber']),
            'cphonename'     => str_replace("-", "", $data['cusfcontact']),
            'cphoneposition' => trim($data['cphoneposition']),
            'province'       => $data['city'],
            'addr'           => $this->getJsonAddress($data),
            'csource'        => $data['csource'] ? $data['csource'] : "独立开发",
            'clevel'         => self::CUS_LEVEL_DEFAULT,
            'uid'            => $staffId,
            'last_uid'       => $staffId,
            'last_uids'      => $staffId,
            'founderid'      => $staffId,
            'tip'            => $data['detail'],
            'addtime'        => time(),
            'auditorid'      => $data['auditorid'],
            'ctype'          => str_replace("-", "", $data['cusType']),
            'website'        => $data['website'] ? $data['website'] : null,
            'cus_pid'        => $data['sub_cus'] ? (int)$data['sub_cus'] : null
        );
        return $rst  = $this->add(M('customer')->create($this->cusAddData));
    }

    public function changeCusUid($cusFilter, $staffIdArr)
    {

        $this->field = "last_uids,uid,cid cusid,cname oldname";
        $changeArr = array(
            'unChangeIds1'     => "",
            'unChangeCusName1' => "",
            'unChangeIds2'     => "",
            'unChangeCusName2' => "",
            'changeIds'        => ""
        );

        $cusChangeData = $this->getCusBaseInfoMulti($cusFilter, $this->field);//获取要改uid客户的信息
        // 循环逻辑 修改和为修改数据分离
        for($i = 0; $i < count($cusChangeData); $i++) {
            $lastUidArr[$i] = explode(",", $cusChangeData[$i]['last_uids']);
            if ($cusChangeData[$i]['uid'] == $staffIdArr['uid']) {
                $changeArr['unChangeIds1']     .= empty($changeData['unChangeIds1'])    ? $cusChangeData[$i]['cusid']   :"," . $cusChangeData[$i]['cusid'];
                $changeArr['unChangeCusName1'] .= empty($changeArr['unChangeCusName1']) ? $cusChangeData[$i]['oldname'] :"," . $cusChangeData[$i]['oldname'];
            } elseif (in_array($staffIdArr['uid'], $lastUidArr[$i])) {
                $changeArr['unChangeIds2']     .= empty($changeArr['unChangeIds2'])     ? $cusChangeData[$i]['cusid']   :"," . $cusChangeData[$i]['cusid'];
                $changeArr['unChangeCusName2'] .= empty($changeArr['unChangeCusName2']) ? $cusChangeData[$i]['oldname'] :"," . $cusChangeData[$i]['oldname'];
            } else {
                $changeArr['changeIds']        .= empty($changeArr['changeIds']) ? $cusChangeData[$i]['cusid'] :"," . $cusChangeData[$i]['cusid'];
            }
        }

        // 当前数据如果存在有重复的数据就直接返回。
        if(!empty($changeArr['unChangeCusName2'])){
            $data = array(
                'updateResult'     => false,
                'changeRecordData' => array(),
                'changeArr'        => array()
            );
            return $data;
        }

        if ($changeArr['changeIds']) {
            $map['crm_customer.cid'] = array('IN', $changeArr['changeIds']);
            $changeData = array(
                'uid'              => $staffIdArr['uid'],
                'max_contact_time' => time(),
                'max_order_time'   => time(),
                'last_uid'         => $staffIdArr['uid'],
                'last_uids'        => array('exp', "CONCAT_WS(',',`last_uids`,{$staffIdArr['uid']})")
            );
            foreach($cusChangeData as &$val) {
                $val['change_id']     = $staffIdArr['changeId'];
                $val['changetime']    = $changeData['max_contact_time'];
                $val['change_reason'] = "客户转移操作，操作时间：" . date("Y-m-d H:i:s") . "由" . $staffIdArr['formName'] . "转给" . $staffIdArr['toName'];
            }
            $rst = M()->table('crm_customer')->where($map)->setField($changeData);
            $data = array(
                'updateResult'     => $rst,
                'changeRecordData' => $cusChangeData,
                'changeArr'        => $changeArr
            );

        } else {
            $data = array(
                'updateResult'     => false,
                'changeRecordData' => array(),
                'changeArr'        => array()
            );
        }
        return $data;
    }

    public function getOrderAuth($cusId, $staffId)
    {
        $authMap['cid'] = ['EQ', $cusId];
        $authRst = $this->getCusBaseInfo($authMap,'uid');
        return ($authRst['uid'] != $staffId) ? false : true;
    }


    public function checkUid($id)
    {
        $map['cid'] = array('EQ', $id);
        $this->field = "cid,cname,uid";
        $data = $this->getCusBaseInfo($map, $this->field);
        if (!empty($data['uid'])) {
            return dataReturn($data['cname'] . '客户有负责人或有人申请，不能删除', 400);
        } else {
            return dataReturn('ok', 200, $data);
        }
    }
    public function delTrans($idArray)
    {
        if (!count($idArray)) {
            return dataReturn('未提交有效数据', 400, count($idArray));
        }
        $addData = [];
        for ($i = 0; $i < count($idArray); $i++) {
            $checkRst[$i] = $this->checkUid($idArray[$i]);
            if ($checkRst[$i]['status'] === 400) {
                return $checkRst[$i];
            }
            $addData[$i] = array(
                'cusid'         => (int)$idArray[$i],
                'changetime'    => time(),
                'change_reason' => "客户删除操作，删除了名为" . $checkRst[$i]['cname'] . "，删除人" . session('nickname'),
                'change_id'     => session('staffId'),
                'oldname'       => $checkRst[$i]['data']['cname'],
            );
        }
        $map['cid'] = ['in', $idArray];
        try {
            $this->startTrans();
            $rst = $this->where($map)->delete();
            if ($rst === false) {
                $this->rollback();
                return dataReturn('客户删除失败', 400);
            }

            $cusRecordModel = new CuschangerecordModel();
            $addRst = $cusRecordModel->addAll($addData);
            if ($addRst === false) {
                $this->rollback();
                return dataReturn('客户删除失败', 400);
            }
            $this->commit();
            return dataReturn('成功删除了' . count($idArray) . "家客户", 400);
        } catch (Exception $exception) {
            return dataReturn('删除操作有问题', 500, $exception->getMessage());
        }

    }

    /**
     * 查询客户负责人名下客户数量，kpi客户数，去除子公司（cus_pid有值）的数量
     * @param $condition
     * @param $start
     * @param $length
     * @param $order
     * @return array
     */
    public function getCustomerNumberList($map,$search, $start, $length, $order){
        $map['cstatus'] = ['EQ', '2'];
        $map['auditstatus'] = ['eq', '3'];
        $field = "s.`name`,
                COUNT(1) as customers,
                count(1) - count(c.cus_pid) as main_customers,
                count(IF(c.kpi_flag = 1,kpi_flag,NULL)) as kpi_customers,
                d.name as dep_name";
        if (trim($search)) {
            $map['s.name|d.name'] = ['like', "%" . trim($search) . "%"];
        }
        $data = $this->alias("c")
            ->field($field)
            ->join("left join crm_staff s on s.id = c.uid")
            ->join("left join crm_dept d on s.deptid = d.id")
            ->where($map)
            ->group("c.uid")
            ->limit($start, $length)
            ->order($order)
            ->select();

        $countData = $this->alias("c")
            ->join("left join crm_staff s on s.id = c.uid")
            ->join("left join crm_dept d on s.deptid = d.id")
            ->where($map)->group("c.uid")->select();
        $count = count($countData);


        return [$data,$count,$count];
    }
}
