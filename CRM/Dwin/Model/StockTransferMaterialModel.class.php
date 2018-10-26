<?php
/**
 * Created by PhpStorm.
 * User: invokerx
 * Date: 2018/8/10
 * Time: 下午5:38
 */

namespace Dwin\Model;


use Think\Model;

class StockTransferMaterialModel extends Model
{
    const IS_DEL = 1; // 已被删除
    const NO_DEL = 0; // 有效

    const SUCCESS_STATUS = 200;
    const FAIL_STATUS    = 404;
    const FORBIDDEN_STATUS = 403;

    protected $validate = [
        ['product_id',    'require', '调拨物料型号内部主键不能为空', 1],
        ['num',   'checkNum', '调拨数量非法', 1, 'callback'],
        ['remark',   'require', '调拨原因未填写', 1],
        ['rep_id_in',          'require', '入库仓库id不能为空', 1],
        ['rep_id_out',      'require', '出库仓库id不能为空', 1],
        ['transfer_pid', 'require', 'pid不能为空', 1],
    ];

    public function checkNum($num)
    {
        if (!is_numeric($num)) {
            return false;
        }
        if ($num <= 0) {
            return false;
        }
        return true;
    }

    public function getAddData($material, $pidArray)
    {
        $time = time();
        $data = [];
        foreach($material as $key => $item) {
            $data[$key]['product_id'] = $item['product_id'];
            $data[$key]['product_no'] = $item['product_no'];
            $data[$key]['num']        = $item['num'];
            $data[$key]['remark']     = $item['reason'];
            $data[$key]['rep_id_in']  = getStringId($item['repInArr']);
            $data[$key]['rep_name_in'] = getStringChar($item['repInArr']);
            $data[$key]['rep_id_out'] = getStringId($item['repOutArr']);
            $data[$key]['rep_name_out'] = getStringChar($item['repOutArr']);
            $data[$key]['in_out'] = $data[$key]['rep_id_in'] . "_" . $data[$key]['rep_id_out'];
            $data[$key]['create_time'] = $time;
            $data[$key]['update_time'] = $time;
            $data[$key]['transfer_pid'] = "";
        }

        for ($i = 0; $i < count($pidArray); $i++) {
            for ($j = 0; $j < count($data); $j++) {
                if (empty($data[$j]['transfer_pid'])) {
                    if (empty($tmp)) {
                        $data[$j]['transfer_pid'] = $pidArray[$i]['orderId'];
                        $tmp = $data[$j]['in_out'];
                    } else {
                        if ($data[$j]['in_out'] == $tmp) {
                            $data[$j]['transfer_pid'] = $pidArray[$i]['orderId'];
                        }
                    }
                    if ($j == count($data) - 1) {
                        $tmp = "";
                    }
                }

            }
        }

        return $data;
    }

    public function getEditData($material)
    {
        $data = [];
        $tmp  = [];
        foreach ($material as $key => $value) {
            $tmp['id']     = $value['id'];
            $tmp['num']    = $value['num'];
            $tmp['remark'] = $value['remark'];
            $data[] = $tmp;
        }
        return $data;
    }

    public function getUpdStockDataWithEditMaterial($material)
    {
        $data = [];
        $tmp  = [];
        foreach ($material as $key => $value) {
            if ($value['num'] != $value['base_num']) {
                $tmp['product_id'] = $value['product_id'];
                $tmp['warehouse_number'] = $value['rep_id_out'];
                $tmp['num'] = $value['num'] - $value['base_num'];
                $data[] = $tmp;
            }
        }
        return $data;
    }

    public function validateAddData($params)
    {
        $stockModel = new StockModel();
        $item = [];
        foreach ($params as $key => $param) {
            $item[$key] = $this->validate($this->validate)->create($param);
            if ($item[$key] === false) {
                return false;
            }
            $num[$key] = $stockModel->getStockNumberWithRepAndPid($item[$key]['rep_id_out'], $item[$key]['product_id']);
            if ($num[$key] < $item[$key]['num'] || $item[$key]['num'] <= 0) {
                $this->error = "第" . ($key + 1) . "项的调拨数量超出了" . $item['rep_name_out'] ."的库存数量 " . $num[$key] . "|" . $item[$key]['num'];
                return false;
            }
        }
        return $item;


    }

    public function obtainAuditIdWithTransferId($transferId)
    {
        $map['transfer_pid'] = ['eq', $transferId];
        $map['is_del'] = ['EQ', self::NO_DEL];
        $info = $this->where($map)->find();
        $repIds = $info['rep_id_in'] . "," . $info['rep_id_out'];
        $repModel = new RepertorylistModel();
        return $staffIds = $repModel->getWarehouseLogisticsIds($repIds);
    }

    public function getMaterialWithPid($id)
    {
        $map['a.transfer_pid'] = ['eq', $id];
        $map['a.is_del'] = ['EQ', self::NO_DEL];
        $field = "a.id,
            a.product_id,
            material.product_no,
            material.product_name,
            material.product_number,
            a.num,
            a.rep_id_in,
            a.rep_name_in,
            a.rep_id_out,
            a.rep_name_out,
            a.remark,
            from_unixtime(a.create_time) create_time,
            from_unixtime(a.update_time) update_time,
            rep.logistics_staff_id staff_ids,
            st.stock_number stock_total_number";
        $data = $this->alias('a')
            ->field($field)
            ->join('LEFT JOIN crm_material material ON material.product_id = a.product_id')
            ->join('LEFT JOIN crm_repertorylist rep ON rep.rep_id = a.rep_id_out')
            ->join("LEFT JOIN crm_stock st ON st.product_id = a.product_id and st.warehouse_number = a.rep_id_out")
            ->where($map)->select();
        foreach ($data as &$val) {
            $val['rep_id_arr'] = explode(',',$val['staff_ids']);
        }
        return $data;
    }

    /**
     * 获取基本信息 目前用于导出PDF
     * @param $id
     * @param array $map
     * @return mixed
     */
    public function getBaseMaterialMsgByPid($id, $map = []){
        $map['a.transfer_pid'] = ['eq', $id];
        $map['a.is_del'] = ['EQ', self::NO_DEL];
        $data = $this->alias('a')
            ->field("a.*,m.product_name, m.product_number")
            ->join('LEFT JOIN crm_material m ON m.product_id = a.product_id')
            ->where($map)->select();
        return $data;
    }

    public function deleteMaterialWithPid($sourceId)
    {
        $map['transfer_pid'] = ['EQ', $sourceId];
        $map['is_del'] = ['EQ', self::NO_DEL];
        $info = $this->where($map)->select();
        if (0 == count($info))
            return true;
        $data['is_del'] = self::IS_DEL;
        $rst = $this->where($map)->setField($data);
        if (false === $rst) {
            $this->error = "删除material失败";
            return false;
        }
        $stockModel = new StockModel();
        foreach ($info as $item) {
            $filter['product_id'] = ['eq', $item['product_id']];
            $filter['warehouse_number'] = ['eq', $item['rep_id_out']];
            $upd = $stockModel->updateWithFlag('stockOutNoActionOrderFalse',$filter, $item['num']);
            if (false === $upd) {
                $this->error = "删除material失败2";
                return false;
            }
        }

        return true;
    }

    public function deleteMaterialWithId($id)
    {
        $this->startTrans();
        $map['id'] = ['EQ', $id];
        $map['is_del'] = ['EQ', self::NO_DEL];
        $info = $this->where($map)->find();
        if (0 == count($info))
            return true;
        $data['is_del'] = self::IS_DEL;
        $rst = $this->where($map)->setField($data);

        if (false === $rst) {
            $this->rollback();
            $this->error = "删除material失败";
            return false;
        }
        $filter['product_id'] = ['eq', $info['product_id']];
        $filter['warehouse_number'] = ['eq', $info['rep_id_out']];
        $num = $info['num'];
        $stockModel = new StockModel();
        $upd = $stockModel->updateWithFlag('stockOutNoActionOrderFalse',$filter, $num);
        if (false === $upd) {
            $this->rollback();
            $this->error = "删除material失败2";
            return false;
        }
        $this->commit();
        return true;
    }



}