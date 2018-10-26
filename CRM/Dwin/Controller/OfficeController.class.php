<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2018/5/19
 * Time: 10:53
 */
namespace Dwin\Controller;
use Dwin\Model\MaxIdModel;
use Dwin\Model\StaffInfoModel;

class OfficeController extends CommonController
{
    const SUCCESS_STATUS = 200;
    const FAIL_STATUS = 400;
    const FORBIDDEN_STATUS = 403;

    const EDIT_ROLE_ID = [1,2,34,35,37];
    /**
     * 投诉的基础展示方法
     * @param $table    string      选择3个不同的表格展示三种投诉
     */
    private function complainIndex($table)
    {
        if(IS_POST) {
            $params = I('post.');
            $staffModel = new StaffInfoModel();
            $mapTableData = $this->dataTable($params);
            $mapTableData['map']['is_del'] = ['EQ', 0];

            if(!empty($mapTableData['condition'])){
                $condition['on_job'] = ['eq', 1];
                $condition['name'] = ['like', "%" . $mapTableData['condition'] . "%"];
                $staffConditionData = $staffModel->field("id")->where($condition)->select();
                if(!empty($staffConditionData)){
                    $where = '(';

                    $i = 1;
                    $count = count($staffConditionData);
                    foreach ($staffConditionData as $item){
                        if($i < $count){
                            $where .= "find_in_set(". $item ." , liable)  or  ";
                        }else {
                            $where .= "find_in_set(". $item ." , liable))";
                        }
                        $i++;
                    }

                    $mapTableData['map']['_complex'] = $where;
                }
            }
            $model = M($table);
            $data['draw'] = (int)$params['draw'];
            $data['recordsTotal'] = $model->count();
            $data['recordsFiltered'] = $model->where($mapTableData['map'])->count();
            $data['data'] = $model
                 -> where($mapTableData['map'])
                 -> order($mapTableData['order'])
                 -> limit($params['start'], $params['length'])
                 -> select();
            $statusArr = ['', '等待调查处理', '调查完毕', '流程完毕'];
            $staffMap['on_job'] = ['eq' ,1 ];
            $staffData = $staffModel->field("id,concat(name,'-',department) as name")->where($staffMap)->select();
            $staffArrMap = array_column($staffData,"name",'id');

            foreach ($data['data'] as $key => &$value) {
                $value['create_time'] = date('Y-m-d H:i:s', $value['create_time']);
                $value['status_str'] = $statusArr[$value['status']];
                $staffArr = explode(',',$value['liable']);
                $value['liableArr'] = $staffArr;
                $value['liable'] = '';
                foreach ($staffArr as $item){
                    $value['liable'] .= $staffArrMap[$item];
                }
            }
            $this->ajaxReturn($data);
        }
    }

    /**
     * 生产延期投诉展示页
     */
    public function productionDelayComplain()
    {
        if (IS_POST){
            $this->complainIndex('production_delay_complain');
        }else{
            $postUrl = U('postProductionDelayComplain');
            $researchUrl = U('researchProductionDelayComplain','','');
            $validityUrl = U('validityProductionDelayComplain','','');
            $delUrl = U('delProductionDelayComplain', '', '');
            $title = '生产延期投诉';
            $this->assign(compact('postUrl', 'researchUrl', 'validityUrl', 'title', 'delUrl'));
            $this->display('productionDelayComplain');
        }
    }

    /**
     * 生产质量投诉展示页
     */
    public function productionQualityComplain()
    {
        if (IS_POST){
            $this->complainIndex('production_quality_complain');
        }else{
            $postUrl = U('postProductionQualityComplain');
            $researchUrl = U('researchProductionQualityComplain','','');
            $validityUrl = U('validityProductionQualityComplain','','');
            $delUrl = U('delProductionQualityComplain', '', '');
            $title = '生产质量投诉';
            $this->assign(compact('postUrl', 'researchUrl', 'validityUrl', 'title', 'delUrl'));
            $this->display('productionDelayComplain');
        }
    }

    /**
     * 服务质量投诉展示页
     */
    public function serviceQualityComplain()
    {
        if (IS_POST){
            $this->complainIndex('service_quality_complain');
        }else{
            $postUrl = U('postServiceQualityComplain');
            $researchUrl = U('researchServiceQualityComplain', '', '');
            $validityUrl = U('validityServiceQualityComplain', '', '');
            $delUrl = U('delServiceQualityComplain', '', '');
            $title = '服务质量投诉';
            $this->assign(compact('postUrl', 'researchUrl', 'validityUrl', 'title', 'delUrl'));
            $this->display('productionDelayComplain');
        }
    }

    /**
     * 新增投诉基础处理方法
     * @param $table    string      新增投诉的表格
     * @param $numberPrefix     string      投诉编号前缀
     */
    private function postComplain($table, $numberPrefix)
    {
        if (IS_POST){
            $params = I('post.data');
            $params['create_time'] = time();
            $params['proposer_id'] = session('staffId');
            $params['proposer_name'] = session('nickname');
            $maxIdModel = new MaxIdModel();
            $params['id'] = $maxIdModel -> getMaxId($table);
            $params['number'] = $numberPrefix . $params['id'];
            $params['status'] = 1;
            // 责任人的信息在新增时候不添加
//            $params['liable'] = implode(",",$params['liable']);
            $res = M($table) -> add($params);
            if ($res === false){
                $this->ajaxReturn([
                    'status' => self::FAIL_STATUS,
                    'msg' => '新增失败',
                ]);
            }else{
                $this->ajaxReturn([
                    'status' => self::SUCCESS_STATUS,
                    'msg' => '新增成功'
                ]);
            }
        }
    }

    /**
     * 新增生产延期投诉
     * @param null $productionOrder
     */
    public function postProductionDelayComplain($productionOrder = null)
    {
        if (IS_POST){
            $this->postComplain('production_delay_complain', 'JQTS-');
        }else{
            if ($productionOrder != null){
                $this->assign(compact('productionOrder'));
            }
            $title = '新增生产延期投诉';
            $form = 'null';
            $editLvl = '0';
            $this->assign(compact( 'title', 'form', 'editLvl'));
            $this->display('postProductionDelayComplain');
        }
    }

    /**
     * 新增生产质量投诉
     * @param null $productionOrder
     */
    public function postProductionQualityComplain($productionOrder = null)
    {
        if (IS_POST){
            $this->postComplain('production_quality_complain', 'ZLTS-');
        }else{
            if ($productionOrder != null){
                $this->assign(compact('productionOrder'));
            }
            $title = '新增生产质量投诉';
            $form = 'null';
            $editLvl = '0';
            $this->assign(compact('title', 'form', 'editLvl'));
            $this->display('postProductionDelayComplain');
        }
    }

    /**
     * 新增服务投诉
     */
    public function postServiceQualityComplain()
    {
        if (IS_POST){
            $this->postComplain('service_quality_complain', 'FWTS-');
        } else {
            $hideProductionOrder = '1';
            $title = '新增服务投诉';
            $form = 'null';
            $editLvl = '0';
            $this->assign(compact( 'hideProductionOrder', 'title', 'form', 'editLvl'));
            $this->display('postProductionDelayComplain');
        }
    }

    /**
     * 投诉调查阶段的基础处理方法
     * @param $table    string      调查处理的表格
     */
    private function researchComplain($table)
    {
        if (IS_POST){
            $params = I('post.data');
            $row = M($table) -> find($params['id']);
            if ($row['status'] > 2){
                $this->ajaxReturn([
                    'status' => self::FAIL_STATUS,
                    'msg' => '只可以处理等待调查阶段的投诉'
                ]);
            }
            $authFlag = $this->checkAuthByRole(self::EDIT_ROLE_ID);
            if (!$authFlag) {
                $this->ajaxReturn([
                    'status' => self::FAIL_STATUS,
                    'msg' => '您没有权限处理该投诉'
                ]);
            }

            $params['liable'] = implode(",",$params['liable']);
            $params['handler'] = session('nickname');
            $params['research_time'] = time();
            $params['status'] = 2;
            unset($params['create_time']);
            unset($params['process_time']);
            $res = M($table) -> save($params);
            if ($res === false){
                $this->ajaxReturn([
                    'status' => self::FAIL_STATUS,
                    'msg' => '修改失败',
                ]);
            }else{
                $this->ajaxReturn([
                    'status' => self::SUCCESS_STATUS,
                    'msg' => '修改成功'
                ]);
            }
        }
    }

    /**
     * 生产延期投诉调查处理信息
     * @param $id
     *
     */
    public function researchProductionDelayComplain($id)
    {
        if (IS_POST){
            $this->researchComplain('production_delay_complain');
        }else{
            $authFlag = $this->checkAuthByRole(self::EDIT_ROLE_ID);
            if (!$authFlag) {
                die('<h3 style="text-align: center;margin-top: 20%;">您无权进行处理，如有问题请联系管理</h3>');
            }
            $title = '填写生产延期投诉调查处理信息';
            $row = $this->getComplainInfo('production_delay_complain', $id);
            $form = json_encode($row);
            $editLvl = 1;
            if ($row['status'] > 2){
                die('<h3 style="text-align: center;margin-top: 20%;">监督反馈已完成,不可修改</h3>');
            }
            $this->assign(compact('title', 'form', 'editLvl'));
            $this->display('postProductionDelayComplain');
        }
    }

    /**
     * 生产质量投诉调查处理信息
     * @param $id
     */
    public function researchProductionQualityComplain($id)
    {
        if (IS_POST){
            $this->researchComplain('production_quality_complain');
        }else{
            $authFlag = $this->checkAuthByRole(self::EDIT_ROLE_ID);
            if (!$authFlag) {
                die('<h3 style="text-align: center;margin-top: 20%;">您无权进行处理，如有问题请联系管理</h3>');
            }
            $title = '填写生产质量投诉调查处理信息';
            $row = $this->getComplainInfo('production_quality_complain', $id);
            $form = json_encode($row);
            $editLvl = 1;
            if ($row['status'] > 2){
                die('<h3 style="text-align: center;margin-top: 20%;">监督反馈已完成,不可修改</h3>');
            }
            $this->assign(compact('title', 'form', 'editLvl'));
            $this->display('postProductionDelayComplain');
        }
    }

    /**
     * 服务投诉调查处理信息
     * @param $id
     */
    public function researchServiceQualityComplain($id)
    {
        if (IS_POST){
            $this->researchComplain('service_quality_complain');
        } else {
            $authFlag = $this->checkAuthByRole(self::EDIT_ROLE_ID);
            if (!$authFlag) {
                die('<h3 style="text-align: center;margin-top: 20%;">您无权进行处理，如有问题请联系管理</h3>');
            }
            $hideProductionOrder = '1';
            $title = '填写服务投诉调查处理信息';
            $editLvl = 1;
            $row = $this->getComplainInfo('service_quality_complain', $id);
            $form = json_encode($row);
            if ($row['status'] > 2){
                die('<h3 style="text-align: center;margin-top: 20%;">监督反馈已完成,不可修改</h3>');
                $this->error('监督反馈已完成,不可修改');
            }
            $this->assign(compact('hideProductionOrder', 'title', 'form', 'editLvl'));
            $this->display('postProductionDelayComplain');
        }
    }

    /**
     * 监督处理基础方法
     * @param $table    string      表名
     */
    private function validityComplain($table)
    {
        if (IS_POST){
            $params = I('post.data');
            $auditor = M($table) -> find($params['id'])['auditor'];
            if ($auditor != session('nickname')) {
                $this->ajaxReturn([
                    'status' => self::FAIL_STATUS,
                    'msg' => '只有监督人才可以修改'
                ]);
            }
            $params['process_time'] = time();
            $params['status'] = 3;
            unset($params['create_time']);
            $res = M($table) -> save($params);
            if ($res === false){
                $this->ajaxReturn([
                    'status' => self::FAIL_STATUS,
                    'msg' => '修改失败',
                ]);
            }else{
                $this->ajaxReturn([
                    'status' => self::SUCCESS_STATUS,
                    'msg' => '修改成功'
                ]);
            }
        }
    }

    /**
     * 生产延期投诉监督信息
     * @param $id
     */
    public function validityProductionDelayComplain($id)
    {
        if (IS_POST){
            $this->validityComplain('production_delay_complain');
        }else{
            $title = '填写生产延期投诉监督信息';
            $row = $this->getComplainInfo('production_delay_complain', $id);
            if ($row['status'] < 2){
                $this->error('监督信息应在调查处理之后');
            }
            $form = json_encode($row);
            $editLvl = 2;
            $this->assign(compact('title', 'form', 'editLvl'));
            $this->display('postProductionDelayComplain');
        }
    }

    /**
     * 产质量投诉监督信息
     * @param $id
     */
    public function validityProductionQualityComplain($id)
    {
        if (IS_POST){
            $this->validityComplain('production_quality_complain');
        }else{
            $title = '填写生产质量投诉监督信息';
            $row = $this->getComplainInfo('production_quality_complain', $id);
            if ($row['status'] < 2){
                $this->error('监督信息应在调查处理之后');
            }
            $form = json_encode($row);
            $editLvl = 2;
            $this->assign(compact('title', 'form', 'editLvl'));
            $this->display('postProductionDelayComplain');
        }
    }

    /**
     * 服务投诉监督信息
     * @param $id
     */
    public function validityServiceQualityComplain($id)
    {
        if (IS_POST){
            $this->validityComplain('service_quality_complain');
        } else {
            $hideProductionOrder = '1';
            $title = '填写服务投诉监督信息';
            $row = $this->getComplainInfo('service_quality_complain', $id);
            if ($row['status'] < 2){
                $this->error('监督信息应在调查处理之后');
            }
            $form = json_encode($row);
            $editLvl = 2;
            $this->assign(compact( 'hideProductionOrder', 'title', 'form', 'editLvl'));
            $this->display('postProductionDelayComplain');
        }
    }

    /**
     * 根据表和id获取投诉详情
     * @param $table
     * @param $id
     * @return mixed
     */
    private function getComplainInfo($table, $id)
    {
        $data = M($table) -> find($id);
        $data['create_time'] = $data['create_time'] == '' ? '' : date('Y-m-d H:i:s', $data['create_time']);
        $data['research_time'] = $data['research_time'] == '' ? '' : date('Y-m-d H:i:s', $data['research_time']);
        $data['process_time'] = $data['process_time'] == '' ? '' : date('Y-m-d H:i:s', $data['process_time']);
        $data['liable'] = empty($data) ? [] :explode(",",$data['liable']);
        return $data;
    }

    /**
     * 自定义datatables处理方法
     * @param $params
     * @param array $_map
     * @return array
     */
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
        $word = $params['search']['value'];
        if ($word == ''){
            $map = [];
        }else{
            $searchAble = rtrim(implode('|', $searchAble), '|');
            $map = [$searchAble => ['LIKE',"%".$word."%"]];
        }
        return [
            'order' => $order,
            'map' => $map,
            'condition' => $word
        ];
    }

    /**
     * 奖励公告
     */
    public function award()
    {
        if(IS_POST) {
            $params = I('post.');
            $mapTableData = $this->dataTable($params);
            $model = M('staff_punish_rewards');
            $mapTableData['map']['type'] = ['EQ', '奖励'];
            $data['draw'] = (int)$params['draw'];
            $data['recordsTotal'] = $model->count();
            $data['recordsFiltered'] = $model->where($mapTableData['map'])->count();
            $data['data'] = $model
                -> where($mapTableData['map'])
                -> order($mapTableData['order'])
                -> limit($params['start'], $params['length'])
                -> select();
            foreach ($data['data'] as $key => &$value) {
                $value['record_time'] = date('Y-m-d H:i:s', $value['record_time']);
            }
            $this->ajaxReturn($data);
        }
        else{
            $this->display();
        }
    }

    /**
     * 惩罚公示
     */
    public function punish()
    {
        if(IS_POST) {
            $params = I('post.');
            $mapTableData = $this->dataTable($params);
            $model = M('staff_punish_rewards');
            $mapTableData['map']['type'] = ['EQ', '处罚'];
            $data['draw'] = (int)$params['draw'];
            $data['recordsTotal'] = $model->count();
            $data['recordsFiltered'] = $model->where($mapTableData['map'])->count();
            $data['data'] = $model
                -> where($mapTableData['map'])
                -> order($mapTableData['order'])
                -> limit($params['start'], $params['length'])
                -> select();
            foreach ($data['data'] as $key => &$value) {
                $value['record_time'] = date('Y-m-d H:i:s', $value['record_time']);
            }
            $this->ajaxReturn($data);
        }
        else{
            $this->display();
        }
    }

    /**
     * 获取投诉的调查人
     */
    public function getComplainAuditor()
    {
        $roleIds = M('auth_role') -> field('staff_ids') -> where(['pid' => ['ELT', 2]]) -> select();
        $arr = [];
        foreach ($roleIds as $key => $value) {
            if ($value['staff_ids']){
                $arr[] = $value['staff_ids'];
            }
        }
        $arr = implode(',', $arr);
        $auditor = M('staff') -> field('id, name') -> where(['id' => ['IN', $arr]]) -> select();
        $this->ajaxReturn([
            'status' => self::SUCCESS_STATUS,
            'data' => $auditor
        ]);
    }

    /**
     * 删除投诉基础方法
     * @param $table
     * @param $id
     */
    private function delComplain($table, $id)
    {
        $model = M($table);
        $row = $model -> find($id);
        if ($row['status'] == 1){
            if (session('staffId') == $row['proposer_id']){
                $res = $model -> save(['id' => $id, 'is_del' => 1]) === false ? false : true;
                if ($res){
                    $status = self::SUCCESS_STATUS;
                    $msg = '删除成功';
                }else{
                    $status = self::FAIL_STATUS;
                    $msg = '删除失败';
                }
            }else{
                $status = self::FORBIDDEN_STATUS;
                $msg = '只有投诉人本人可以删除';
            }
        }else{
            $status = self::FORBIDDEN_STATUS;
            $msg = '该投诉已经处理,不可删除';
        }
        $this->ajaxReturn([
            'status' => $status,
            'msg' => $msg,
        ]);
    }

    /**
     * 删除生产延期投诉
     * @param $id
     */
    public function delProductionDelayComplain($id)
    {
        $this->delComplain('production_delay_complain', $id);
    }

    /**
     * 删除生产质量投诉
     * @param $id
     */
    public function delProductionQualityComplain($id)
    {
        $this->delComplain('production_quality_complain', $id);

    }

    /**
     * 删除服务质量投诉
     * @param $id
     */
    public function delServiceQualityComplain($id)
    {
        $this->delComplain('service_quality_complain', $id);

    }

    /**
     * 获取在职人员信息
     */
    public function getIsStaffMsg(){
        if(IS_POST){
//            $name = I("post.name");
//            if(empty($name)){
//                $this->returnAjaxMsg("数据返回成功",200,[]);
//            }

            $staffModel = new StaffInfoModel();
            $map['on_job'] = ['eq' ,1 ];
//            $where['name'] = ['like', "%" . $name . "%"];
//            $where['username'] = ['like', "%" . $name . "%"];
//            $where['_logic'] = 'OR';
//            $map['_complex'] = $where;

            $data = $staffModel->field("id,concat(name,'-',department) as name")->where($map)->select();
            $this->returnAjaxMsg("数据返回成功",200,$data);
        }else {
            die("非法");
        }
    }

}
