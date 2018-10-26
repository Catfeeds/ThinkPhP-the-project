<?php
/**
 * Created by PhpStorm.
 * User: chendongdong
 * Date: 2018/7/24
 * Time: 下午3:50
 */
namespace Dwin\Model;


use Think\Model;

class MaterialBomModel extends Model{
    const TYPE_NOT_AUDIT = 0;       // 未审核
    const TYPE_UNQUALIFIED = 1;     // 不合格
    const TYPE_QUALIFIED = 2;       // 合格
    const TYPE_FORBIDDEN = 3;       // 禁用

    const IS_DEL = 1; // 已删除
    const NO_DEL = 0; // 未删除

    const FINISHED_PRODUCT = 1001; //成品
    const SEMI_MANUFACTURES = 1002; // 半成品
    const COMPONENT_PART = 1003; //元器件

    public static $bomGroupMap = [
        self::FINISHED_PRODUCT => '成品',
        self::SEMI_MANUFACTURES => '半成品',
        self::COMPONENT_PART => '元器件',
    ];

    public static $statusMap = [
        self::IS_DEL => "已删除",
        self::NO_DEL => "未删除"
    ];

    public static $bomTypeMap = [
        self::TYPE_NOT_AUDIT   => '未审核',
        self::TYPE_UNQUALIFIED => '不合格',
        self::TYPE_QUALIFIED   => '合格',
        self::TYPE_FORBIDDEN   => '禁用'
    ];

    protected $_validate = array(
        array("bom_id","require","合同编号不能为空!"),
        array("bom_id","","合同编号必须唯一!",1,"unique"),
        array("product_id","require","物料主键不能为空!"),
        array("product_no","require","物料编号不能为空!"),
        array("bom_type","require","组别不能为空!"),
        array("bom_type_name","require","组别名称不能为空!"),
    );

    /**
     * create by  chendd 去除非表中字段
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

    /**
     * 新增bom基本信息
     * @param $postData
     * @return array
     */
    public function addBom($postData){
        $data = self::getNewField($postData);
        if(empty($data)){
            return [-1, [], '没有提交新增数据'];
        }
        if(empty($postData['id']) || empty($data['bom_id']) || empty($data['product_id']) || empty($data['product_no']) || empty($data['bom_type']) || empty($data['bom_type_name'])){
            return [-2, [], "请将数据填写完成，数量不可以为空"];
        }

        $data['create_time']  = time();
        $data['create_id']    = session('staffId');
        $data['update_time']  = time();
        $data['update_id']    = session('staffId');
        $data = $this->create($data);

        if($data){
            $res = $this->add($data);
            if($res){
                session("bomId", $postData['id']);
                return [0, $res, "新增入库单据基本信息成功"];
            }else {
                return [-2, [], $this->getError()];
            }
        }else {
            return [-2, [], $this->getError()];
        }
    }

    /**
     * 新增一个完整的bom
     * @param $postData
     * @return array
     */
    public function createBom($postData){
        $this->startTrans();
        list($code, $data, $msg) = $this->addBom($postData['bom']);
        if($code != 0){
            $this->rollback();
            return dataReturn($msg, 400);
        }

        $materialBomSubModel = new MaterialBomSubModel();

        list($subMsg,$subCode, $logData) = $materialBomSubModel->addBomSubMany($postData['bomSub']);
        if($subCode != 0){
            $this->rollback();
            return dataReturn($subMsg, 400);
        }

        // BOM 操作履历
        $logModel = new MaterialBomLogModel();
        list($logMsg, $logCode) = $logModel->createBomLog(session("bomId"), MaterialBomLogModel::TYPE_ADD, "BOM新增成功");
        if($logCode != 200){
            $this->rollback();
            return dataReturn($logMsg, 400);
        }

        $this->commit();
        return dataReturn("bom生成成功", 200);
    }

    /**
     * 修改bom基本信息
     * @param $params
     * @return array
     */
    public function editBomData($params)
    {
        if (empty($params)) {
            return [-1, '', "无修改数据提交"];
        }

        $oldData = $this->field("*")->find($params['id']);

        if($oldData['bom_status'] == self::TYPE_QUALIFIED){
            return [-2, '', '当前bom已启动，不可修改'];
        }

        list($editData, $logStr) = $this->compareData($oldData, $params);
        if ($editData === false) {
            return [-1, $logStr, '无数据修改'];
        } else {
            $data = $this->create($editData);
            if(!$data){
                return[-2, $logStr, $this->getError()];
            }
            $editRst = $this->save($data);
            if ($editRst === false) {
                return [$this->getError(), $logStr, -2];
            }
            return [0, $logStr, '数据实例化成功'];
        }
    }

    /**
     * 比较前后修改数据的不同
     * @param $oldData
     * @param $editedData
     * @return array
     */
    private function compareData($oldData, $editedData)
    {
         $logStr = '';
        // 先把不存在当前表里面的字段剔除，然后在与原先的数据做对比
        foreach ($editedData as $key => $val) {
            if ($val == $oldData[$key]) {
                unset($editedData[$key]);
            } else {
                // 处理修改内容，便于存储log
                switch ($key){
                    case "bom_type":
                        $logStr .= "BOM组别由" . self::$bomGroupMap[$oldData[$key]] . "修改为" . self::$bomGroupMap[$val];
                        break;
                    default :
                        break;
                }
                continue;
            }
        }

        if(empty($editedData)){
            return [false, ''];
        }

        $editedData['bom_status'] == self::TYPE_NOT_AUDIT;
        $editedData['id']           = $oldData['id'];
        $editedData['update_time']  = time();
        $editedData['update_id']    = session('staffId');
        return [$editedData, $logStr];
    }

    /**
     * 查找bom基本信息
     * @param $bomId
     * @return mixed
     */
    public function findBomBaseMsg($bomId){
        $map = [];
        $map['is_del'] = ['eq', self::NO_DEL];
        $map['id'] = ['eq', $bomId];
        return $this->where($map)->find();
    }

    /**
     * 查找bom所有信息
     * @param $bomId
     * @return array
     */
    public function findBomMsgByBomId($bomId){
        $bomMsg = [];
        $bomMsg['bom'] = self::findBomBaseMsg($bomId);

        $materialBomSubModel = new MaterialBomSubModel();
        $bomMsgp['bomSub'] = $materialBomSubModel->findBomOtherMsg($bomId);

        return dataReturn("数据返回成功", 200, $bomMsg);

    }

    /**
     * 修改bom里面所有内容
     * @param $postData
     * @return array
     */
    public function editBomAllMsg($postData){
        try {
            $this->startTrans();

            $productModel = new ProductionOrderModel();
            $productData = $productModel->where(['bom_pid' => $postData['bom']['id'], 'is_del' => ProductionOrderModel::$notDel])->select();
            if(!empty($productData)){
                return dataReturn("当前bom在生产计划中有被使用，无法修改",400);
            }

            // 为了存储BOM修改log
            $logData = [];
            list($msg, $logStr, $code) = $this->editBomData($postData['bom']);
            if ($code == -2) {
                $this->rollback();
                return dataReturn($msg, 400);
            }
            $logData[] = $logStr;

            $materialBomSubModel = new MaterialBomSubModel();
            $editCode = 0;
            if(!empty($postData['edit_bom_sub'])){
                list($editMsg, $editCode, $editSubLog) = $materialBomSubModel->editBomSubMany($postData['edit_bom_sub']);
                if ($editCode == -2) {
                    $this->rollback();
                    return dataReturn($editMsg, 400);
                }
                $logData = array_merge($logData, $editSubLog);
            }

            if (!empty($postData['new_bom_sub'])){
                session('bomId', $postData['bom']['id']);
                list($newMsg, $newCode, $addSumLog) = $materialBomSubModel->addBomSubMany($postData['new_bom_sub']);
                if ($newCode != 0) {
                    $this->rollback();
                    return dataReturn($newMsg, 400);
                }
                $logData = array_merge($logData, $addSumLog);
            }

            if($code == -1 && ($editCode == -1 || empty($postData['edit_bom_sub'])) && empty($postData['new_bom_sub'])){
                return dataReturn('数据未发生修改', 400);
            }

            $this->where(['id' => $postData['bom']['id']])->setField(['bom_status' => MaterialBomModel::TYPE_NOT_AUDIT]);

            // BOM 操作履历
            $logModel = new MaterialBomLogModel();
            list($logMsg, $logCode) = $logModel->createBomLog($postData['bom']['id'], MaterialBomLogModel::TYPE_EDIT, implode(";", array_filter($logData)));
            if($logCode != 200){
                $this->rollback();
                return dataReturn($logMsg, 400);
            }

            $this->commit();
            return dataReturn("bom修改成功", 200);
        } catch (\Exception $exception) {
            return dataReturn($exception->getMessage(), 400);
        }
    }

    /**
     * 删除一个bom配方
     * @param $bomId
     * @return array
     */
    public function deleteBom($bomId){
        try {
            $this->startTrans();
            $bomData = $this->find($bomId);
            if($bomData['bom_status'] == self::TYPE_QUALIFIED){
                $this->rollback();
                return dataReturn("审核合格的bom不能被删除",400);
            }

            $bomRes = $this->where(['id' => $bomId])->setField(['is_del' => self::IS_DEL]);
            if($bomRes === false){
                $this->rollback();
                return dataReturn($this->getError(),400);
            }

            $productModel = new ProductionOrderModel();
            $productData = $productModel->where(['bom_pid' => $bomId, 'is_del' => ProductionOrderModel::$notDel])->select();
            if(!empty($productData)){
                return dataReturn("当前bom在生产计划中有被使用，无法删除",400);
            }

            $materialBomSubModel = new MaterialBomSubModel();
            $bomSubRes = $materialBomSubModel->where(['bom_pid' => $bomId,'is_del' => MaterialBomSubModel::NO_DEL])->setField(['is_del' => $materialBomSubModel::IS_DEL]);
            if($bomSubRes === false){
                $this->rollback();
                return dataReturn($this->getError(),400);
            }

            // BOM 操作履历
            $logModel = new MaterialBomLogModel();
            list($logMsg, $logCode) = $logModel->createBomLog($bomId, MaterialBomLogModel::TYPE_DEL, "BOM删除成功");
            if($logCode != 200){
                $this->rollback();
                return dataReturn($logMsg, 400);
            }

            $this->commit();
            return dataReturn("删除成功", 200);
        } catch (\Exception $exception) {
            return dataReturn($exception->getMessage(), 400);
        }
    }

    /**
     * 获取bom列表页信息
     */
    public function getList($condition, $start, $length, $order, $bomStatus, $map = []){
        $map['b.is_del'] = ['eq', self::NO_DEL];

        switch ($bomStatus){
            case 0 :
                $map['b.bom_status'] = ['eq', self::TYPE_NOT_AUDIT];
                break;
            case 1 :
                $map['b.bom_status'] = ['eq', self::TYPE_UNQUALIFIED];
                break;
            case 2 :
                $map['b.bom_status'] = ['eq', self::TYPE_QUALIFIED];
                break;
            case 3 :
                $map['b.bom_status'] = ['eq', self::TYPE_FORBIDDEN];
                break;
            default:
                break;
        }

        $recordMap = $map;

        if(strlen($condition) != 0){
            $where['b.product_no'] = ['like', "%" . $condition . "%"];
            $where['m.product_name']=['like', "%" . $condition . "%"];
            $where['m.product_number']=['like', "%" . $condition . "%"];
            $where['bs.product_no'] = ['like', "%" . $condition . "%"];
            $where['sm.product_name']=['like', "%" . $condition . "%"];
            $where['_logic'] = 'OR';
            $recordMap['_complex'] = $where;
        }

        $data =  $this->alias("b")
            ->field("b.*,cs.name,m.product_name,m.product_number, m.warehouse_id,r.repertory_name,us.name as update_name")
            ->join("left join crm_staff cs on cs.id = b.create_id")
            ->join("left join crm_staff us on us.id = b.update_id")
            ->join("left join crm_material m on m.product_id = b.product_id")
            ->join("crm_repertorylist r on r.rep_id = m.warehouse_id")
            ->join("left join crm_material_bom_sub bs on bs.bom_pid = b.id and bs.is_del = " . MaterialBomSubModel::NO_DEL)
            ->join("left join crm_material sm on sm.product_id = bs.product_id")
            ->limit($start, $length)
            ->where($recordMap)
            ->order($order)
            ->group("b.id")
            ->select();


        /** 后台传输局到前台
        @param $count 总记录数 $recordsFiltered search 后数据总数 $info 传递的数据*/
        $count = $this->alias("b")
            ->join("left join crm_staff on crm_staff.id = b.create_id")
            ->join("left join crm_material m on m.product_id = b.product_id")
            ->where($map)
            ->count();
        $recordsFilteredData = $this->alias("b")
            ->join("left join crm_staff on crm_staff.id = b.create_id")
            ->join("left join crm_material m on m.product_id = b.product_id")
            ->join("left join crm_material_bom_sub bs on bs.bom_pid = b.id and bs.is_del = " . MaterialBomSubModel::NO_DEL)
            ->join("left join crm_material sm on sm.product_id = bs.product_id")
            ->where($recordMap)
            ->group("b.id")
            ->select();
        $recordsFiltered = count($recordsFilteredData);

        return [$data,$count,$recordsFiltered];
    }

    /**
     * 上传bom
     * @param string $filePath
     * @param int $sheet
     * @return array
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     */
    public function readExecl($filePath='', $sheet=0){
        Vendor('PHPExcel.PHPExcel');//引入类
        Vendor('PHPExcel.Reader.Excel5');  // 后缀是xls
        Vendor('PHPExcel.Reader.Excel2007'); // 后缀是xlsx
//        $reader = \PHPExcel_IOFactory::createReader('Excel2007');
        $ext = strrev(explode('.', strrev($filePath))[0]);

        if($ext == 'xls'){
            $reader=new \PHPExcel_Reader_Excel5();
        }else if($ext == 'xlsx'){
            $reader = new \PHPExcel_Reader_Excel2007();
        }else {
            return dataReturn("请使用文件后缀为xls 或 xlsx的文件", 400);
        }
        $PHPExcel = $reader->load($filePath); // 文档名称
        $currentSheet = $PHPExcel->getSheet($sheet);        //**读取excel文件中的指定工作表*/
        $allColumn = $currentSheet->getHighestColumn();        //**取得最大的列号*/
        $allRow = $currentSheet->getHighestRow();        //**取得一共有多少行*/

        $bom['bom_type'] = self::getValue($currentSheet, "A2");
        $bom['bom_id'] = self::getValue($currentSheet, "A5");
        $bom['bom_type_name'] = self::getValue($currentSheet, "B2");
        $bom['product_no'] = self::getValue($currentSheet, "B5");
        $bom['bom_status'] = self::getValue($currentSheet, "E5");
        switch ($bom['bom_status']){
            case "合格":
                $bom['bom_status'] = self::TYPE_QUALIFIED;
                break;
            case "不合格":
                $bom['bom_status'] = self::TYPE_UNQUALIFIED;
                break;
            case "未审核":
                $bom['bom_status'] = self::TYPE_NOT_AUDIT;
                break;
            default:
                $bom['bom_status'] = self::TYPE_NOT_AUDIT;
                break;
        }
        $materialModel = new MaterialModel();
        $materialData = $materialModel->checkIsset($bom['product_no']);
        if(empty($materialData)){
            return dataReturn("不存在编号为" . $bom['product_no'] . "的物料", 400);
        }
        $bom['product_id'] = $materialData['product_id'];
        $this->startTrans();
        list($code, $data, $msg) = self::addBom($bom);
        if($code != 0){
            $this->rollback();
            return dataReturn($msg, 400);
        }

        $bomSubData = [];
        for($rowIndex=8;$rowIndex<=$allRow;$rowIndex++){        //循环读取每个单元格的内容。注意行从1开始，列从A开始
            $middData = [];
            for($colIndex='A';$colIndex<=$allColumn;$colIndex++){
                $addr = $colIndex.$rowIndex;
                switch ($colIndex){
                    case 'A' :
                        $middData['product_no'] = self::getValue($currentSheet, $addr);
                        break;
                    case 'B' :
//                        $middData['product_number'] = self::getValue($currentSheet, $addr);
                        break;
                    case 'C' :
//                        $middData['product_name'] = self::getValue($currentSheet, $addr);
                        break;
                    case 'D' :
                        $middData['num'] = self::getValue($currentSheet, $addr);
                        break;
                    default:
                        break;
                }
            }

            $materialData = $materialModel->checkIsset($middData['product_no']);
            if(empty($materialData)){
                return dataReturn("不存在编号为" . $bom['product_no'] . "的物料", 400);
            }
            $middData['product_id'] = $materialData['product_id'];
            $bomSubData[] = $middData;
        }

        $materialBomSubModel = new MaterialBomSubModel();

        list($subMsg,$subCode, $logData) = $materialBomSubModel->addBomSubMany($bomSubData);
        if($subCode != 0){
            $this->rollback();
            return dataReturn($subMsg, 400);
        }

        // BOM 操作履历
        $logModel = new MaterialBomLogModel();
        list($logMsg, $logCode) = $logModel->createBomLog(session("bomId"), MaterialBomLogModel::TYPE_ADD, "导入Excel，BOM新增成功");
        if($logCode != 200){
            $this->rollback();
            return dataReturn($logMsg, 400);
        }

        $this->commit();
        return dataReturn("bom生成成功", 200);
    }

    public function exportToExcel($postData){
        //处理筛选条件 与前端商量好怎么样的一个传参方式
        if(strlen($postData['is_del']) != 0){
            $map['bom.is_del'] = ['eq', $postData['is_del']];
        }
        if(strlen($postData['bom_status']) != 0){
            $map['bom.bom_status'] = ['eq', $postData['bom_status']];
        }
        if(strlen($postData['bom_type']) != 0){
            $map['bom.bom_type'] = ['eq', $postData['bom_type']];
        }
        if(!empty($postData['bomArr'])){
//                $map['bom.bom_id'] = $postData['bomArr'];
            $map['bom.bom_id'] = ['in', $postData['bomArr']];
        }
        if(empty($map)) {
            $map['bom.is_del'] = ['eq', self::NO_DEL];
        }
        $data = self::selectValidBom($map);
        if(empty($data)){
            return dataReturn("当前条件查询数据为空，请更换条件",400);
        }
        list($bomData, $subdata) = self::processingData($data);

        Vendor('PHPExcel.PHPExcel');//引入类
        Vendor('PHPExcel.PHPExcel_IOFactory');//引入类
//        Vendor('PHPExcel.Writer.Excel5');  // 后缀是xls
        Vendor('PHPExcel.Writer.Excel2007'); // 后缀是xlsx

        $objPHPExcel = new \PHPExcel();                        //初始化PHPExcel(),不使用模板
//        $template = dirname(__FILE__).'/template.xls';          //使用模板
//        $objPHPExcel = \PHPExcel_IOFactory::load($template);     //加载excel文件,设置模板
//        $objWriter = new \PHPExcel_Writer_Excel5($objPHPExcel);  //设置保存版本格式

        for ($i = 0; $i < count($bomData); $i++ ){
            if($i > 0 ){
                $objActSheet = $objPHPExcel->createSheet();
                $objActSheet = $objPHPExcel->setactivesheetindex($i);
            }else {
                //接下来就是写数据到表格里面去
                $objActSheet = $objPHPExcel->getActiveSheet();
            }

            //这里是设置单元格的内容
            $objActSheet->setCellValue("A1","[G]组别");
            $objActSheet->setCellValue("B1","名称");
            $objActSheet->setCellValue("A2",$bomData[$i]['bom_type']);
            $objActSheet->setCellValue("B2",$bomData[$i]['bom_type_name']);

            $objActSheet->setCellValue("A3","[P]产品");
            $objActSheet->setCellValue("A4","BOM代码");
            $objActSheet->setCellValue("B4","物料代码");
            $objActSheet->setCellValue("C4","物料名称");
            $objActSheet->setCellValue("D4","物料型号");
            $objActSheet->setCellValue("E4","审核状态");
            $objActSheet->setCellValue("A5",$bomData[$i]['bom_id']);
            $objActSheet->setCellValue("B5",$bomData[$i]['bom_product_no']);
            $objActSheet->setCellValue("C5",$bomData[$i]['bom_product_name']);
            $objActSheet->setCellValue("D5",$bomData[$i]['bom_product_number']);
            $objActSheet->setCellValue("E5",$bomData[$i]['bom_status']);
            $objActSheet->setCellValue("F5","合格/不合格/未审核");

            $objActSheet->setCellValue("A6","[D]材料");
            $objActSheet->setCellValue("A7","物料代码");
            $objActSheet->setCellValue("B7","物料名称");
            $objActSheet->setCellValue("C7","物料型号");
            $objActSheet->setCellValue("D7","数量");

            $arr = $subdata[$bomData[$i]['bom_id']];
            for ($j = 0; $j < count($arr); $j++){
                $n = $j + 8;
                $objActSheet->setCellValue("A" . $n, $arr[$j]['sub_product_no']);
                $objActSheet->setCellValue("B" . $n, $arr[$j]['sub_product_number']);
                $objActSheet->setCellValue("C" . $n, $arr[$j]['sub_product_name']);
                $objActSheet->setCellValue("D" . $n, $arr[$j]['num']);
            }
        }

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $fileName = "有效BOM". '_' . date('Ymd') . '.xlsx';
        // $fileName = iconv('utf-8', 'gb2312', $fileName);//文件名称


        // 1.保存至本地Excel表格
        $rootPath = WORKING_PATH . UPLOAD_ROOT_PATH . "/excel/";
        if (!file_exists($rootPath)) {
                mkdir($rootPath, 0777,true);
        }
        $objWriter->save($rootPath . $fileName);

        // 2.接下来当然是下载这个表格了，在浏览器输出就好了
//        header("Pragma: public");
//        header("Expires: 0");
//        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
//        header("Content-Type:application/force-download");
//        header("Content-Type:application/vnd.ms-execl");
//        header("Content-Type:application/octet-stream");
//        header("Content-Type:application/download");;
//        header('Content-Disposition:attachment;filename="'.$fileName.'"');
//        header("Content-Transfer-Encoding:binary");
//        $objWriter->save('php://output');

        return dataReturn("BOM导出excel成功", 200, [
            'file_url' => UPLOAD_ROOT_PATH . "/excel/" . $fileName
        ]);
    }

    /**
     * 导入excel读取值，需要转换
     * @param $currentSheet 当前excel对象
     * @param $addr     填入地址
     * @return string
     */
    protected function getValue($currentSheet, $addr){
        $cell = $currentSheet->getCell($addr)->getValue();
        if($cell instanceof \PHPExcel_RichText){ //富文本转换字符串
            $cell = $cell->__toString();
        }
        return $cell;
    }

    /**
     * 查出当前审核合格的bom
     * @return mixed
     */
    public function selectValidBom($map = []){
        $map['bom.is_del'] = ['eq', self::NO_DEL];
        return  $this-> alias('bom')
            ->field("bom.bom_id as bom_id,bom.product_no as bom_product_no,material_bom.product_name as bom_product_name,bom.bom_status,material_bom.product_number as bom_product_number,bom.bom_type,bom.bom_type_name,sub.product_no as sub_product_no,material_sub.product_number as sub_product_number,material_sub.product_name as sub_product_name,sub.num")
            ->join('left join crm_material_bom_sub sub on sub.bom_pid = bom.id and sub.is_del = ' . MaterialBomSubModel::NO_DEL)
            ->join('left join crm_material material_bom on material_bom.product_id = bom.product_id')
            ->join('left join crm_material material_sub on material_sub.product_id = sub.product_id')
            ->where($map)
            ->select();
    }

    /**
     * 处理bom数据，使数据格式变成两个数组，并且 subData = [ 'bom_id' => [],[]]
     * @param $data
     * @return array
     */
    public function processingData($data){
        $bom = [];
        $bomData = [];
        $subData = [];
        foreach ($data as $key => $item) {
            $subData[$item['bom_id']][] = [
                'sub_product_no' => $item['sub_product_no'],
                'sub_product_number' => $item['sub_product_number'],
                'sub_product_name' => $item['sub_product_name'],
                'num' => $item['num'],
            ];


            if (!isset($bom['bom_id']) || $bom['bom_id'] != $item['bom_id']) {
                $bom = [
                    'bom_id' => $item['bom_id'],
                    'bom_status' => isset(self::$bomTypeMap[$item['bom_status']]) ? self::$bomTypeMap[$item['bom_status']] : '审核数据错误',
                    'bom_product_no' => $item['bom_product_no'],
                    'bom_product_name' => $item['bom_product_name'],
                    'bom_product_number' => $item['bom_product_number'],
                    'bom_type' => $item['bom_type'],
                    'bom_type_name' => $item['bom_type_name']
                ];
                $bomData[] = $bom;
            }
        }
        return [$bomData, $subData];
    }

    public function getBomDataWithProductId($productId)
    {
        $map['product_id'] = ['eq', $productId];
        $map['bom_status'] = ['EQ', self::TYPE_QUALIFIED];
        $map['is_del']     = ['EQ', self::NO_DEL];
        $field = "bom_type_name, product_no,bom_id,id";
        return $this->where($map)->field($field)->select();
    }
}