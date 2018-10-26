<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2018/3/24
 * Time: 11:07
 */
namespace Dwin\Model;
use Think\Model;
class ProductAddAuditModel extends Model
{
    /**
     * 新增产品添加申请
     * @param $params
     * @return bool
     */
    public function addAudit($params)
    {
        if ($this->create($params) !== false){
            if ($this->add() !== false){
                return true;
            }
        }
        return false;
    }

    public function index($map, $start, $length, $order)
    {
        $data = $this
             -> where($map)
             -> order($order)
             -> limit($start, $length)
             -> select();
        return $data;
    }

    public function changeAuditStatus($id, $status)
    {
        $productAdd = true;
        $productModel = new IndustrialSeralScreenModel();
        $this->startTrans();
        if ($status == 2) {
            $audit = $this->find($id);
            if ($productModel->create($audit) !== false) {
                if ($productModel->add() !== false) {

                }else{
                    $this->error = '产品添加失败';
                    $productAdd = false;
                }
            } else {
                $this->error = $productModel->getError();
                $productAdd = false;
            }
        }
        $data = [
            'audit_status' => $status,
            'update_time' => time(),
        ];
        $auditUpdate = $this->where(['id' => ['EQ', $id]])->save($data);
        if ($auditUpdate !== false && $productAdd !== false) {
            $this->commit();
            return true;
        } else {
            if ($auditUpdate === false){
                $this->error = '审核更新失败';
            }
            $this->rollback();
            return false;
        }
    }
}