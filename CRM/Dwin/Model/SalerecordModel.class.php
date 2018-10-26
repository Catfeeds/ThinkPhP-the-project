<?php
/**
 * Created by Sublime.
 * User: Jason
 * Date: 2017/11/27
 * Time: 9:31
 */
namespace Dwin\Model;
use Think\Model;
class SalerecordModel extends Model
{
    const TYPE_NOT_AUDIT = 0;       // 未审核
    const TYPE_QUALIFIED = 1;     // 有效
    const TYPE_UNQUALIFIED = 2;       // 无效

    public static $auditTypeMap = [
        self::TYPE_NOT_AUDIT   => '未审核',
        self::TYPE_QUALIFIED   => '有效',
        self::TYPE_UNQUALIFIED => '无效',
    ];

    // 售后单类型
    const TYPE_NOT_HANDLE = 0; // 未处理
    const TYPE_RETURN_POLICY = 1; // 退换货
    const TYPE_BORROW_THING = 2; // 借物
    const TYPE_MAINTAIN = 3; //维修

    static public $saleTypeMap = [
        self::TYPE_NOT_HANDLE => '未处理',
        self::TYPE_RETURN_POLICY => '退换货',
        self::TYPE_BORROW_THING => '借物',
        self::TYPE_MAINTAIN => '维修',
    ];

    /**
     * 去除非此表的字段数据
     * @param $params
     * @return array
     */
    public function getNewField($params){
        $fieldData = $this->getDbFields();
        $data = [];
        foreach ($fieldData as $key => $field){
            if(isset($params[$field])){
                $data[$field] = $params[$field];
            }
        }
        return $data;
    }

    private function compareData($oldData, $editedData)
    {
        // 先把不存在当前表里面的字段剔除，然后在与原先的数据做对比
        foreach ($editedData as $key => $val) {
            if ($val == $oldData[$key]) {
                unset($editedData[$key]);
            } else {
                if($key == "rgmoney"){
                    // 修改了费用就要重新确认维修费是否可以通过，并且修改总费用
                    $money = $val- $oldData[$key];
                    $editedData['sum_fee'] = $oldData['sum_fee'] + $money;
                    if($oldData['is_ok'] != 1 && $oldData != 2){
                        $editedData['is_ok'] = 2;
                        $editedData['change_status_time'] = time();
                    }
                }
                continue;
            }
        }

        if(empty($editedData)){
            return false;
        }

        $editedData['sid']   = $oldData['sid'];
        return $editedData;
    }


	public function getSaleServiceList($where)
    {
        return $this->where($where)
            ->join('crm_customer AS cus ON cus.cid = cusid')
            ->join('crm_staff AS sta ON sta.id = yid')
            ->field("crm_salerecord.*,cus.cname,cus.keyword,cus.uid AS rpbid,sta.name AS pname")
            ->order('repair_date DESC')
            ->limit(0, 5)
	    ->select();

    }

    /**
     * 获取售后记录展示列表数据
     * 售后记录主表crm_salerecord的sid与维修单表crm_repairperson的pid关联
     * @param $map array         查询条件
     * @param  string $field             查询字段
     * @param string $start      分页起始
     * @param string $length     分页条数
     * @param string $order             排序
     * @param string $group      分组
     * @return mixed             查询结果
     */
    public function getSaleRecord($map = [],$field,$start = '0',$length = '10',$order, $group = '')
    {
        return $this->join(' LEFT JOIN `crm_repairperson` c ON crm_salerecord.sid = c.pid ')
            ->join(' LEFT JOIN `crm_salerecord_isok` i ON crm_salerecord.is_ok = i.id ')
            ->join(' LEFT JOIN `crm_staff` a ON crm_salerecord.sale_commissioner = a.id ')
            ->join(' LEFT JOIN `crm_repairperson_repersonquestion` e ON c.reperson_question = e.id ')
            ->field($field)
            ->where($map)
            ->group($group)
            ->limit($start,$length)
            ->order($order)
            ->select();
    }

    /**
     * 获取给业务展示维修单页面的基本信息
     * 售后记录主表crm_salerecord的sid与维修品表crm_repairgoods的pid关联
     * @param array $map          查询条件
     * @param string $field        查询字段
     * @return mixed        查询结果
     */
    public function getCusSaleBasicInfo($map,$field)
    {
        return $this->join(' LEFT JOIN `crm_repairgoodsinfo` AS r  ON crm_salerecord.sid = r.pid ')->field($field)->where($map)->find();
    }

    /**
     * 获取给业务展示维修品的所有产品型号
     * 售后记录主表crm_salerecord的sid与维修品表crm_repairgoods的pid关联
     * @param array $map          查询条件
     * @param string $field       查询字段
     * @return mixed        查询结果
     */
    public function getCusSaleProname($map,$field)
    {
        return $this->join(' LEFT JOIN `crm_repairgoodsinfo` AS r  ON crm_salerecord.sid = r.pid ')->field($field)->where($map)->select();
    }
    
    /**
     * 获取给业务展示维修单的全部信息
     * 售后记录主表crm_salerecord的sid与维修品表crm_repairperson的pid关联
     * @param array $map          查询条件
     * @param string $field        查询字段
     * @return mixed        查询结果
     */
    public function getCusSaleInfo($map,$field)
    {
        return $this
            ->join(' LEFT JOIN `crm_repairperson` AS r  ON crm_salerecord.sid = r.pid ')
            ->join(' LEFT JOIN `crm_repairperson_remode` AS b  ON b.id = r.re_mode ')
            ->join(' LEFT JOIN `crm_repairperson_repersonquestion` AS c  ON c.id = r.reperson_question ')
            ->join(' LEFT JOIN `crm_repairgoodsinfo` AS a  ON crm_salerecord.sid = a.pid and a.product_name = r.product_name')
            ->field($field)
            ->where($map)
            ->group('rpid')
            ->select();
    }

    /**
     * 获取给业务展示维修单的发货/入库信息
     * 售后记录主表crm_salerecord的sid与发货表crm_sendgoods的pid关联
     * @todo ERP对接时将修改入库信息
     * @param array $map          查询条件
     * @param string $field        查询字段
     * @return mixed        查询结果
     */
    public function getCusSaleSendgoodsInfo($map,$field)
    {
        return $this
            ->join(' LEFT JOIN `crm_sendgoods` AS r  ON crm_salerecord.sid = r.pid ')
            ->field($field)
            ->where($map)
            ->select();
    }

    /**
     * 添加售后记录基本信息
     * @param string $field        添加的字段信息
     * @return bool|int     返回插入的id
     */
    public function addSaleRecordBasic($table,$field)
    {
        $data = M()->table($table)->add($field);
        return $data ? $data : false;
    }

    /**
     * 售后记录更新操作的记录
     * @param int  $id           对应salerecord的sid
     * @param string $status       插入状态
     * @return bool|int     返回插入成功id
     */
    public function changeSaleStatus($id, $status)
    {
        $data['saleid']             = $id;
        $data['change_status']      = $status;
        $data['changemanid']        = session('staffId');
        $data['changemanname']      = session('nickname');
        $data['change_status_time'] = time();
        $jilu = M()->table('crm_salerecordchange')->add($data);
        return $jilu ? $jilu : false;
    }

    /**
     * 售后记录更新批量操作的记录
     * @param array $field        批量更新的记录数据
     * @return bool|int     返回插入成功id
     */
    public function changeSaleMoreStatus($field)
    {
        $jilu = M()->table('crm_salerecordchange')->addAll($field);
        return $jilu ? $jilu : false;
    }

    /**
     * 插入记录之前的查询信息
     * 通过页面select框获取的id，关联其他表去查出id对应的name或者product_name去插入，保证每个模块部分的独立性
     * @param string $table        查询表格
     * @param string $field        查询字段（多数为name | product_name）
     * @param array $map          查询条件
     * @return bool|int     查询结果
     */
    public function findSingleInfo($table,$field,$map)
    {
        $data = M()->table($table)->field($field)->where($map)->find();
        return $data ? $data : false;
    }

    /**
     * 插入记录之前的查询信息(多条)
     * @param string $table        查询表格
     * @param string $field        查询字段
     * @param array $map          查询条件
     * @return bool|int     查询结果
     */
    public function findMoreInfo($table, $field, $map)
    {
        $data = M()->table($table)->field($field)->where($map)->select();
        return $data ? $data : false;
    }

    /**
     * 插入多条信息
     * @param string $table        插入表名
     * @param array $addData        插入字段
     * @return bool|int     返回结果
     */
    public function addSaleRecordInfo($table,$addData)
    {
        $data = M()->table($table)->addAll($addData);
        return $data ? $data : false;
    }

    /**
     * 更改单条信息
     * @param string $table        表名
     * @param array $field        字段
     * @param array $map          条件
     * @return bool         返回值
     */
    public function updateSaleRecord($table,$field,$map)
    {
        $data = M()->table($table)->where($map)->save($field);
        return $data;
    }

    /**
     * 获取维修品基本信息
     * @param string $field        查询字段
     * @param array  $map          查询条件
     * @return mixed        查询结果
     */
    public function getProInfo($field,$map)
    {
        return $this
            ->join(' LEFT JOIN `crm_repairgoodsinfo` AS r  ON crm_salerecord.sid = r.pid ')
            ->join(' LEFT JOIN `crm_repairgoodsinfo_saleway` AS a  ON a.id = r.sale_way ')
            ->field($field)
            ->where($map)
            ->select();
    }

    /**
     * 获取维修人基本信息
     * @param string $field        查询字段
     * @param array $map          查询条件
     * @return mixed        返回结果
     */
    public function getRepersonInfo($field,$map)
    {
        return $this
            ->join(' LEFT JOIN `crm_repairperson` AS r  ON crm_salerecord.sid = r.pid ')
            ->join(' LEFT JOIN `crm_repairperson_restatus` AS a  ON r.re_status = a.id ')
            ->join(' LEFT JOIN `crm_repairperson_remode` AS b  ON r.re_mode = b.id ')
            ->join(' LEFT JOIN `crm_repairperson_repersonquestion` AS c  ON r.reperson_question = c.id ')
            ->field($field)
            ->where($map)
            ->select();
    }

    /**
     * 获取发货信息               
     * @param string $field            查询字段
     * @param array $map              查询条件
     * @param string $group     分组条件
     * @return mixed            返回结果
     */
    public function getSendInfo($field,$map,$group = '')
    {
        return $this
            ->join(' LEFT JOIN `crm_sendgoods` AS r  ON crm_salerecord.sid = r.pid ')
            ->field($field)
            ->where($map)
            ->group($group)
            ->select();
    }

    /**
     * 数据报表列表显示数据查询
     * @param $field            查询字段
     * @param $where            查询条件
     * @param string $group     分组
     * @param string $start     分页起始
     * @param string $length    每页显示数
     * @param string $order     排序
     * @return mixed            返回结果
     */
    public function getSaleDataExport($field, $where, $group = '', $start = '', $length = '', $order = '')
    {
        return $this
            ->alias('a')
            ->join('LEFT JOIN crm_repairgoodsinfo b ON b.pid = a.sid')
            ->join('LEFT JOIN crm_salerecord_isok i ON i.id = a.is_ok')
            ->join('LEFT JOIN crm_repairperson c ON c.product_name = b.product_name and c.pid = a.sid')
            ->join('LEFT JOIN crm_repairperson_remode e ON e.id = c.re_mode')
            ->join('LEFT JOIN crm_repairgoodsinfo_saleway f ON f.id = b.sale_way')
            ->join('LEFT JOIN crm_sendgoods d ON d.product_name = b.product_name and d.pid = a.sid')
            ->field($field)
            ->where($where)
            ->group($group)
            ->limit($start,$length)
            ->order($order)
            ->select();
    }

    /**
     * 判断此次售后是退货单还是维修单
     * @param $sid int 售后记录主键
     * @return bool 如果是退货单,返回true ,否则返回false
     */
    public function matchServiceType($sid)
    {
        $map = [
            'pid' => ['EQ', $sid]
        ];
        $repairpersonList = M('repairperson') -> field('reperson_question') -> where($map) -> select();
        foreach ($repairpersonList as $key => $value) {
            if ($value['reperson_question'] == '15'){
                return true;
            }
        }
        return false;
    }

    /**
     * 修改售后单主表信息
     * @param $postData
     * @return array
     */
    public function editSaleRecordBaseMsg($postData){
        if(empty($postData['sid'])){
            return ["参数错误",2];
        }

        $data = self::getNewField($postData);

        $oldData = $this->find($data['sid']);
        $editData = self::compareData($oldData, $data);
        if(!$editData){
            return ['无数据修改',1];
        }

        $res = $this->save($editData);
        if($res === false){
            return [$this->getError(),2];
        }
        return ["数据修改成功",0];
    }

    /**
     * 修改售后单基本信息
     * @param $recordData
     * @param $productData
     * @return array
     */
    public function editSaleRecord($recordData, $productData){
        try{
            $this->startTrans();
            list($msg, $code) = self::editSaleRecordBaseMsg($recordData);
            if($code == 2){
                $this->rollback();
                return dataReturn($msg,400);
            }
            // 修改物料信息。
            $productModel = new RepairgoodsinfoModel();
            list($productMsg, $productCode) = $productModel->editProductMany($productData);
            if($productCode == 1 && $code == 1){
                $this->rollback();
                return dataReturn("无数据修改",400);
            }

            if($productCode == 2){
                $this->rollback();
                return dataReturn($productMsg,400);
            }

            $this->commit();
            return dataReturn("数据修改成功", 200);

        }catch (\Exception $exception){
            $this->rollback();
            return dataReturn($exception->getMessage(),400);
        }
    }
}