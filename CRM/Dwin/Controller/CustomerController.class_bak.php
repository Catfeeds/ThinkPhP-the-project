<?php
/**
 * Created by PhpStorm.
 * User: hp
 * Date: 17-5-25
 * Time: 下午4:33
 */

namespace Dwin\Controller;

use Org\Net\Http;
use phpDocumentor\Reflection\Types\Array_;
use Think\Controller;
use Think\Page;
use Think\Upload;

class CustomerController extends CommonController
{
    //客户关系管理类
    // 公司新客户申请
    /*----------------------------------客户添加----------------------------------------*/
    public function addCustomer()
    {
        // 新客户申报 具备条件可以申请
        if (IS_POST) {
            $post = I('post.');

            $post['addtime'] = time();
            $arr = array();
            for ($i = 3; $i > 0; $i--) {
                if ($post['street' . $i] != "") {

                    $$i = inject_filter($post['cmbProvince' . $i] . $post['cmbCity' . $i] . $post['cmbArea' . $i] . $post['street' . $i]);
                    array_push($arr, $$i);
                    $post['province'] = $post['cmbProvince' . $i];
                    $post['province'] = inject_filter($post['province']);
                }
            }
            $post['addr'] = json_encode($arr);
            $id = inject_id_filter(session('staffId'));
            $post['founderid'] = $id;
            $post['uid'] =  $id;
            $post['auditorid'] =  inject_id_filter($post['auditorid']);
            $post['cname'] = inject_filter($post['cname']);
            $post['ctype'] = inject_filter(str_replace("-", "", $post['cusType']));
            $post['csource'] = inject_filter($post['csource']);
            $post['website'] = inject_filter($post['website']);
            $post['cphonename'] = inject_filter(str_replace("-", "", $post['cusfcontact']));
            $post['tip'] = inject_filter($post['detail']);
            $post['website'] = inject_filter($post['website']);
            $model = M('customer');
            $data = $model->create($post);
            $rst = $model->add($data);
            if ($rst) {
                $msg = 1;
            } else {
                $msg = 2;
            }
            $this->ajaxReturn($msg);
        } else {
            // 获取审核人并返回给模板渲染
            $model = M('staff');
            $filter['roleid'] = array('EQ', '4');
            $rst = $model->where($filter)->field('id, name')->select();
            foreach ($rst as $key => $value) {
                $arr[$key]['id'] = $rst[$key]['id'];
                $arr[$key]['name'] = $rst[$key]['name'];
            }
            $industry = M('industry')->select();
            $indus = getTree($industry,0,0,'pid');
            $this->assign(array(
                'arr' => $arr,
                'indus' => $indus
            ));
            $this->display();
        }

    }

    public function checkCusMsg()
    {
        $cusName = I('post.name');
        $web = inject_filter(I('post.website'));
        $num = inject_filter(I('post.cPhone'));
        $map2['website'] = array('EQ', $web);
        $map3['cphonenumber'] = array('EQ', $num);
        require_once('sphinxapi.php');
        $c = new \SphinxClient();
        $c->setServer('localhost', 9312);
        $c->setMatchMode(SPH_MATCH_ALL);

        // $c->UpdateAttributes ( "d", array("group_id"), array(1=>array(456)) );
        $num = mb_strlen($cusName, 'utf8');
        if ($num <= 1) {
            $msg['status'] = 5;
            $this->ajaxReturn($msg);die;
        }
        $cusFilter = array(
            '有限公司', '科技有', '技有限', '有限公', '限公司', '科技', '技有', '有限', '限公', '公司',
            '北京', '上海', '天津', '重庆', '河北', '山西', '内蒙古', '辽宁', '吉林', '黑龙江',
            '江苏', '浙江', '安徽', '福建', '江西', '山东', '河南', '湖北', '湖南', '广东', '广州',
            '广西', '海南', '四川', '贵州', '云南', '西藏', '陕西', '甘肃', '青海', '宁夏','新疆','省',
            '石家庄', '张家口', '承德', '唐山', '秦皇岛', '沧州', '廊坊', '保定', '衡水', '邢台','邯郸','太原',
            '大同', '朔州', '忻州', '阳泉', '晋中', '吕梁', '长治', '临汾', '晋城', '运城','呼和浩特','呼伦贝尔',
            '通辽', '赤峰', '包头', '鄂尔多斯', '哈尔滨', '大庆', '长春', '吉林', '沈阳', '本溪','大连','南京',
            '泰州', '杭州', '扬州', '镇江', '南通', '无锡', '苏州', '宁波', '温州', '合肥','福州','莆田',
            '厦门', '济南', '烟台', '青岛', '郑州', '洛阳', '武汉', '长沙', '佛山', '东莞','深圳','珠海',
            '三亚', '海口', '成都', '贵阳', '拉萨', '丽江', '昆明', '兰州', '天水', '乌鲁木齐','西宁','中卫',
            'select','insert','update','delete','and','or','where','join','*','=','union','into','load_file','outfile','/','\''
        );
        $cusKey = str_replace($cusFilter, "", strtolower($cusName));
        if (mb_strlen($cusKey) <= 1) {
            $msg['status'] = 5;
            $this->ajaxReturn($msg);die;
        }

        $data1 = $c->Query($cusKey, "dwin");
        $index = array_keys($data1['matches']);
        $index_str = implode(',', $index);
        if ($index_str == null) {
            $msg['status'] = 2;
            $this->ajaxReturn($msg);die;
        }
        $map['cid'] = array('IN', $index_str);
        $rst1 = M('customer')->where($map)
            ->field('crm_customer.cid,crm_customer.cname')
            ->select();
        $c->close();

        $rst2 = M('customer')->where($map2)->find();
        $rst3 = M('customer')->where($map3)->find();
        if ($rst1 != false && count($rst1) != 0) {
            // 姓名重复
            $msg['status'] = 1;
            $msg['content'] = $rst1;
        } else {
            if ($rst2 != false && $rst2 != 0) {
                $msg['status'] = 3;
            } else {
                if ($rst3 != false && $rst3 != 0) {
                    $msg['status'] = 4;
                } else {
                    $msg['status'] = 2;
                }
            }
        }
        $this->ajaxReturn($msg);
    }

    public function editCustomer()
    {
        $model = M('customer');

        if (IS_POST) {
            $posts = I('post.');
            $cid =  inject_id_filter($posts['cid']);
            /*-------------------新数据------------------------*/
            $newData['cname'] = inject_filter($posts['companyName']);
            $newData['ctype'] = inject_id_filter($posts['cType']);
            $newData['website'] = inject_filter($posts['Website']);
            $newData['cphonename'] = inject_filter($posts['contactName']);
            $newData['cphonenumber'] = inject_filter($posts['companyPhone']);
            $arr = array();
            for ($i = 3; $i > 0; $i--) {
                if ($posts['addr' . $i] != "") {
                    array_push($arr, inject_filter($posts['addr' . $i]));
                }
            }
            $newData['addr'] = json_encode($arr);

            /*-------------------老数据------------------------*/
            $oldData['cname'] = inject_filter($posts['oldCompanyName']);
            $oldData['ctype'] = inject_filter($posts['oldCType']);
            $oldData['website'] = inject_filter($posts['oldWeb']);
            $oldData['cphonename'] = inject_filter($posts['oldContactName']);
            $oldData['cphonenumber'] = inject_filter($posts['oldcompanyPhone']);
            $arr = array();
            for ($i = 3; $i > 0; $i--) {
                if ($posts['oaddr' . $i] != "") {
                    array_push($arr, inject_filter($posts['oaddr' . $i]));
                }
            }
            $oldData['addr'] = json_encode($arr);
            if ($newData['cname'] == $oldData['cname'] && $newData['ctype'] == $oldData['ctype'] && $newData['website'] == $oldData['website'] && $newData['cphonename'] == $oldData['cphonename'] && $newData['cphonenumber'] == $oldData['cphonenumber'] && $newData['addr'] == $oldData['addr'] )
            {
                $this->ajaxReturn(1);
            } else {
                M()->startTrans();
                $changeRecords['cusid'] = $cid;
                $changeRecords['oldname'] = $oldData['cname'];
                $changeRecords['oldctype'] = $oldData['ctype'];
                $changeRecords['oldwebsite'] = $oldData['website'];
                $changeRecords['oldphone'] =  $oldData['cphonenumber'];
                $changeRecords['oldphonename'] =  $oldData['cphonename'];
                $changeRecords['oldaddr'] =  $oldData['addr'];
                $changeRecords['nowname'] = $newData['cname'];
                $changeRecords['nowctype'] = $newData['ctype'];
                $changeRecords['nowphone'] = $newData['cphonenumber'];
                $changeRecords['nowphonename'] = $newData['cphonename'];
                $changeRecords['nowaddr'] = $newData['addr'];
                $changeRecords['nowwebsite'] = $newData['website'];
                $changeRecords['changetime'] = time();

                $tap_1 = M()->table('crm_cuschangerecord')->add($changeRecords);

                $newData['type'] = 3;
                $newData['auditstatus'] = 1;
                $newData['auditorid'] = (int)$posts['audi'];
                $map['cid'] = array('EQ', $cid);
                $tap_2 = M()->table('crm_customer')->where($map)->setField($newData);

                if ($tap_1 > 0 && $tap_2 > 0) {
                    M()->commit();
                    $this->ajaxReturn(2);
                } else {
                    M()->rollback();
                    $this->ajaxReturn(3);
                }
            }
        } else {
            $cusId = inject_id_filter(I('get.cusId'));
            $map['cid'] = array('EQ', $cusId);
            $uids = $model->where($map)->field('uid')->find();
            if ($uids !== null) {
                $data = $model->where($map)
                    ->join('LEFT JOIN crm_staff AS a ON a.id = uid')
                    ->join('LEFT JOIN crm_staff AS b ON b.id = founderid')
                    ->join('LEFT JOIN crm_staff AS c ON c.id = auditorid')
                    ->join('LEFT JOIN crm_industry ind ON ind.id = ctype')
                    ->field('crm_customer.*,a.name AS uname,b.name AS foundername,c.name AS auditorname, ind.name indusname')
                    ->find();
            } else {
                $data = $model->where($map)
                    ->join('LEFT JOIN crm_staff AS b ON b.id = founderid')
                    ->join('LEFT JOIN crm_staff AS c ON c.id = auditorid')
                    ->join('LEFT JOIN crm_industry ind ON ind.id = ctype')
                    ->field('crm_customer.*, b.name AS foundername, c.name AS auditorname, ind.name indusname')
                    ->find();
            }

            $map['roleid'] = array('EQ', '4');
            $auditor = M('staff')->where($map)->field('id,name')->select();
            $data['addr'] = json_decode($data['addr']);
            $industry = M('industry')->select();
            $indus = getTree($industry,0,0,'pid');
            $this->assign(array(
                'data' => $data,
                'audi' => $auditor,
                'industry' => $indus
            ));
            $this->display();
        }
    }

    /*----------------------------------客户相关内容添加--------------------------------*/
    // 判断客户负责人是否为user
    public function checkUser()
    {
        $cid = inject_id_filter(I('post.cid'));
        $filter['cid'] = array('EQ', $cid);
        $rst = M('customer')->where($filter)->field('cid,uid')->find();
        if ($rst['uid'] != inject_id_filter(session('staffId'))) {
            $msg = 1;
        } else {
            $msg = 2;
        }
        $this->ajaxReturn($msg);
    }
    // 订单
    public function addSaleOrder()
    {
        $model = M('customer');
        $orderModel = M('orderform');

        if (IS_POST) {
            $posts = I('post.');
            $data['order_id'] = inject_filter($posts['orderId']);
            $data['oname']    = inject_filter($posts['orderName']);
            $data['oprice']   = inject_filter($posts['totalRmb']);
            $data['otime']    = strtotime(inject_filter($posts['orderTime']));
            $data['deliverytime'] = strtotime(inject_filter($posts['delTime']));
            $data['odetail']  = nl2br(inject_filter($posts['tips']));
            $data['cus_id']   = inject_id_filter($posts['cusid']);
            $data['picid']    = inject_id_filter(session('staffId'));
            if ($fin = $orderModel->create($data)) {
                $rst = $orderModel->add($fin);
                if ($rst) {
                    $msg['status'] = 1;
                } else {
                    $msg['status'] = 2;
                }
            }
            $this->ajaxReturn($msg);
        } else {
            $id = inject_id_filter(I('get.cusId'));
            $cusCondition['cid'] = array('EQ', $id);
            $resCondition['customerid'] = array('EQ', $id);
            $data = $model->where($cusCondition)->select();
            $list = M('research')
                ->where($resCondition)
                ->field('proid,customerid,proname')
                ->select();
            $this->assign(array(
                'data' => $data,
                'list' => $list
            ));
            $this->display();
        }
    }

    // 联系人
    public function addCusContact()
    {
        $model = M('cuscontacter');
        if (IS_POST) {
            $posts = I('post.');
            $data = array(
                'name' => inject_filter($posts['firstname']),
                'position' => inject_filter($posts['positionName']),
                'phone' => inject_filter($posts['phoneNum']),
                'cusid' => inject_id_filter($posts['cid']),
                'tel' => inject_filter($posts['telNum']),
                'emailaddr' => inject_filter($posts['pEmail']),
                'wechatnum' => inject_filter($posts['weChat']),
                'qqnum' => inject_filter($posts['qqNum']),
                'addid' => inject_id_filter(session('staffId')),
                'addtime' => time()
            );
            if ($fin = $model->create($data)) {
                $rst = $model->add($fin);
                if ($rst) {
                    $msg = 1;
                } else {
                    $msg = 2;
                }
            }
            $this->ajaxReturn($msg);
        } else {
            $cusId = inject_id_filter(I('get.cusId'));
            $map['cid'] = array('EQ', $cusId);
            $data = M('customer')->where($map)->field('cid,cname')->find();
            $this->assign('data', $data);
            $this->display();
        }

    }
    // 检查联系人是否在库
    public function checkCusContact()
    {
        if (IS_POST) {
            $phoneNum = inject_filter(I('post.number'));
            $map['phone'] = array('EQ', $phoneNum);
            $rst = M('cuscontacter')->where($map)->find();
            if ($rst != false && count($rst) != 0) {
                $this->ajaxReturn(2);
            } else {
                $this->ajaxReturn(1);
            }
        }
    }

    // 添加客户联系记录
    public function addContactRecords()
    {
        $conModel = M('contactrecord');

        if (IS_POST) {
            $posts = I('post.');
            $data['theme'] = inject_filter($posts['theme']);
            $data['ctime'] = strtotime(inject_filter($posts['dTime']));
            $data['content'] = nl2br(inject_filter($posts['conDetail']));
            $data['ctype'] = inject_id_filter($posts['type']);
            $data['customerid'] = inject_id_filter($posts['cusid']);
            $data['picid'] = inject_id_filter(session('staffId'));
            $data['posttime'] = time();
            if ($fin = $conModel->create($data)) {
                $rst = $conModel->add($fin);
                if ($rst) {
                    $msg['status'] = 1;
                } else {
                    $msg['status'] = 2;
                }
            }
            $this->ajaxReturn($msg);
        } else {
            $model = M('customer');
            $id = inject_id_filter(I('get.cusId'));
            $resCondition['cid'] = array('EQ', $id);
            $data1 = $model->where($resCondition)->field('cname')->select();
            $this->assign(array(
                'data' => $data1
            ));
            $this->display();
        }
    }

    // 上传客户资料
    public function checkCusUName()
    {
        $posts = I('post.');
        $cusId = inject_id_filter($posts['cusId']);
        if ($cusId != false) {
            $map['cid'] = array("EQ", $cusId);
            $rst = M('customer')->where($map)->field('uid')->find();
            if ($rst['uid'] != inject_id_filter(session('staffId'))) {
                $msg = 1;
            } else {
                $msg = 2;
            }
        } else {
            $msg = 1;
        }
        $this->ajaxReturn($msg);
    }
    public function uploadCusFile()
    {
        $cusId = inject_id_filter(I('get.cusId'));
        $this->assign('cusId', $cusId);
        $this->display();
    }
    public function uploadFile()
    {
        $posts = I('post.');
        $cusId = inject_id_filter($posts['cid']);

        if ($cusId != false) {
            $map['cid'] = array("EQ", $cusId);

            $rst = M('customer')->where($map)->field('uid')->find();
            if ($rst['uid'] != inject_id_filter(session('staffId'))) {
                $this->ajaxReturn(5);
            } else {
                // 文件上传类配置项
                // 检测根目录是否存在，不存在创建
                $rootPath = WORKING_PATH . UPLOAD_ROOT_PATH . "/" . $cusId . "/";
                if (!file_exists($rootPath)) {
                    mkdir($rootPath);
                }
                $fName = $_FILES['file']['name'];
                $ext = array('gif', 'jpg', 'jpeg', 'bmp', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'txt', 'zip', 'rar', 'pdf', 'mp3');
                $cfg = array(
                    'rootPath' => $rootPath, // 保存根路径
                    'mimes' => array('image/jpeg', 'image/gif', 'text/plain' ,'audio/mpeg', 'application/x-rar-compressed', 'application/zip','image/bmp', 'application/msword', 'application/pdf', 'application/vnd.ms-excel', 'application/vnd.ms-powerpoint', 'application/vnd.ms-office'),
                    'replace' => true,
                    'exts' => $ext
                );

                # 实例化上传类
                $upload = new Upload($cfg);
                # 上传
                $info = $upload->upload();
                $data = array();
                if (!$info) {
                    // 返回错误信息
                    $error = $upload->getError();
                    $data['error_info'] = $error;
                    echo json_encode($data);
                } else {
                    // 返回成功信息
                    foreach ($info as $file) {
                        $data['name'] = trim($file['savepath'] . $file['savename'], '.');
                        $saveMsg['cid'] = $cusId;
                        $saveMsg['addtime'] = time();
                        $saveMsg['builderid'] = session('staffId');
                        $saveMsg['fpath'] = UPLOAD_ROOT_PATH . "/" . $cusId . "/" . $file['savepath'] . $file['savename'];
                        $saveMsg['fname'] = $fName;
                        $saveMsg['hasfile'] = '1';
                        $rst = M('cusfile')->add($saveMsg);
                        echo json_encode($data);
                    }
                }
            }
        }
    }
    // 客户资料下载
    public function download()
    {
        $id = inject_id_filter(I('get.id'));
        $model = M('cusfile');
        $filter['fid'] = array('EQ', $id);
        $data = $model->where($filter)->find();
        $map['cid'] = array('EQ', $data['cid']);
        $cus = M('customer')->field('cid,uid')->find($data['cid']);

        if ($cus['uid'] == session('staffId')) {
            # 拼凑文件路径
            $file = WORKING_PATH . $data['fpath'];
            #将文件输出
            import('Org.Net.Http');
            Http::download($file, $data['name']);
        }
        else {
            echo "<script>alert('非法操作');</script>";
        }
    }

    /*----------------------------------公共客户池---------------------------------------*/
    // 公共客户池
    public function showCommonCustomerList()
    {
        $where['c.cstatus'] = array('eq', '1');
        //$where['c.founderid'] = array('eq','.id');
        // 连表查询获得创建人姓名
        $data = M()
            ->table(array('crm_customer' => 'c'))
            ->join('LEFT JOIN crm_staff s ON s.id = c.founderid')
            ->join('LEFT JOIN crm_industry ind ON ind.id = c.ctype')
            ->field('c.cid,c.cname,c.cphonenumber,c.province,c.ctype,c.csource,c.clevel,c.addtime,c.founderid,s.name,ind.name indus')
            ->where($where)
            ->order('c.addtime desc')
            ->select();
        // 数据分配，渲染模板
        $this->assign('data', $data);
        $this->display();
    }

    // 申请公共客户
    public function businessApplication()
    {
        $model = M('customer');
        $cusId = inject_id_filter(I('get.cusId'));
        $map['cid'] = array('EQ', $cusId);
        // uid可能不存在，加一层逻辑判断。
        $data = $model->where($map)
            ->join('crm_staff AS b ON b.id = founderid')
            ->join('crm_staff AS c ON c.id = auditorid')
            ->join('LEFT JOIN crm_industry ind ON ind.id = crm_customer.ctype')
            ->field('crm_customer.*,b.name AS foundername,c.name AS auditorname,ind.name indus,
            (SELECT IFNULL(count(*),0) 
                FROM `crm_orderform` AS d 
                WHERE d.cus_id = cid AND d.otime > (unix_timestamp(now())-10368000)) AS ordernum,
            (SELECT IFNULL(sum(oprice),0) 
                FROM `crm_orderform` AS d 
                WHERE d.cus_id = cid AND d.otime > (unix_timestamp(now())-10368000)) AS ototal,
                (SELECT IFNULL(count(b.cid),0)
                    FROM `crm_contactrecord` AS b 
                    WHERE b.customerid=crm_customer.cid AND posttime > (unix_timestamp(now())-604800)) AS countrecord,
            (SELECT IFNULL(SUM(t.acount),0) 
                    FROM 
                    (SELECT *,
                        (SELECT count(*) 
                            FROM `crm_resprogress` AS pro 
                            WHERE pro.project_id = crm_research.proid AND pro.posttime > (unix_timestamp(now())-604800)) 
                        AS acount FROM `crm_research`) AS t 
                    WHERE t.customerid = crm_customer.cid) AS prosum,
            (SELECT IFNULL(count(b.id),0)
                    FROM `crm_saleservice` AS b 
                    WHERE b.customer_id=crm_customer.cid AND b.addtime > (unix_timestamp(now())-604800)) AS sumservice,
            (SELECT IFNULL(count(c.id),0)
                    FROM `crm_onlineservice` AS c 
                    WHERE c.customer_id=crm_customer.cid AND c.addtime > (unix_timestamp(now())-604800)) AS sumonline')
            ->find();

        $data['addr'] = json_decode($data['addr']);

        $cModel = M('cuscontacter');
        $condi_1['cusid'] = array('EQ', $cusId);
        $contacters = $cModel->where($condi_1)
            ->join('crm_staff AS sta ON sta.id = addid')
            ->field('crm_cuscontacter.*,sta.name AS addname')->select();

        $saleModel = D('saleservice');
        $where1['customer_id'] = array('EQ', $cusId);
        $where1['crm_saleservice.addtime'] = array('GT', time() - 604800);
        $saleService = $saleModel->getSaleServiceList($where1);


        $orderModel = D('orderform');
        $where2['cus_id'] = array('EQ', $cusId);
        $where2['otime'] = array('GT', time() - 13068000);
        $orderContents = $orderModel->getOrderList($where2, $cusId);

        $onlineModel = D('onlineservice');
        $where3['customer_id'] = array('EQ', $cusId);
        $where3['crm_onlineservice.addtime'] = array('GT', time() - 604800);
        $onlineService = $onlineModel->getOnlineServiceList($where3);


        $contactModel = D('contactrecord');
        $where4['customerid'] = array('EQ', $cusId);// 项目ID查询条件
        $where4['crm_contactrecord.posttime'] = array('GT', time() - 604800);
        $contacts = $contactModel->getContactList($where4);


        $resModel = M('research');
        $proModel = D('resprogress');
        $where['customerid'] = array('EQ', $cusId);// 项目ID查询条件

        // 获取客户id => 获取客户id下的项目id数组（项目表） => in查询查去在项目ids内的所有更新记录
        $tempPrjIds = $resModel->where($where)->field('proid')->select();
        $ids = getPrjIds($tempPrjIds, 'proid');
        if ($ids !== false) {
            $where5['project_id'] = array('IN', $ids);
            $where5['crm_resprogress.posttime'] = array('GT', time() - 604800);
            $prjProgress = $proModel->where($where5)
                ->join('crm_staff AS sta ON sta.id = prjer_id')
                ->join('crm_research AS res ON res.proid = project_id')
                ->field('crm_resprogress.*,sta.name AS prjername,res.proname AS prjname')
                ->order('posttime DESC')
                ->select();
        }

        $conditions['roleid'] = array('EQ', '4');
        $auIds = M('staff')->where($conditions)->field('id,name')->select();
        $this->assign(array(
            'data' => $data,
            'contacters' => $contacters,
            'saleService' => $saleService,
            'orderContent' => $orderContents,
            'onlineService' => $onlineService,
            'contacts' => $contacts,
            'prjProgress' => $prjProgress,
            'auId' => $auIds
        ));
        $this->display();
    }
    //  提交申请执行操作
    public function applicationOk()
    {
        $post = I('post.');
        $model = M('customer');
        $cid = inject_id_filter($post['cid']);
        $data = array(
            'uid' => session('staffId'),
            'cstatus' => 2,
            'auditstatus' => 1,
            'auditorid' => $post['auditorid'],
        );
        $map['cid'] = array('EQ', $cid);
        $rst = $model->where($map)->setField($data);
        if ($rst !== false) {
            $this->ajaxReturn(1);
        } else {
            $this->ajaxReturn(2);
        }
    }


    /*----------------------------------个人客户管理-------------------------------------*/
    // 客户业务列表
    // 管理员可查看所有，其他人根据权限查看
    public function showBusinessData()
    {
        // 根据组织架构查看内容。
        $k = I('get.k');
        if ($k == "" || empty($k)) {
            $k = 1;
        }
        $k = inject_id_filter($k);

        $posts = I('post.');

        //获取Datatables发送的参数 必要
        $draw = $posts['draw'];

        // 排序
        $order_dir = $posts['order']['0']['dir'];//ase desc 升序或者降序
        $order_column = (int)$posts['order']['0']['column'];

        //分页
        $start = $posts['start'];//从多少开始
        $length = $posts['length'];//数据长度
        $limitFlag = isset($posts['start']) && $length != -1 ;
        if ($limitFlag ) {
            $start = (int)$start;
            $next = (int)$start + (int)$length - 1;
        } else {
            $start = 0;
            $next  = 5;
        }

        $where['cstatus'] = array('EQ', '2');
        $where['auditstatus'] = array('EQ', '3');
        $condi['auditstatus'] = array('NEQ', '3');


        /*-------------获取用户角色的级别，查询级别下的记录----------------
        /*-------------待审核显示所有权限下的。但不能审核，审核页面单做----------------*/
        $uLevel = (int)session("rLevel");
        $dept = (int)session('deptId');
        $roleInfo = (int)session('rId');
        $maxKey = array_search(min($this->uLevels), $this->uLevels);
        $prjArray = array(1, 2, 7, 11, 14);

        // 总经理权限
        if ($this->uLevels[$maxKey] == 1) {

            // 总记录数
            $count = M('customer')->count('cid');

            /*//搜索
            $search = $posts['search']['value'];//获取前台传过来的过滤条件
            $searchFilter[''] = array('EQ', $search);
            if ($searchFilter != "") {
                $recordsFiltered =  M('customer')->where($searchFilter)->count('cid');
            } else {
                $recordsFiltered = $count;
            }*/

            $ti = time();
            $time = 86400 * $k;
            $data1 =  M('customer')->where($where)
                ->join('LEFT JOIN crm_staff AS a ON crm_customer.uid=a.id')
                ->join('LEFT JOIN crm_industry AS ind ON crm_customer.ctype=ind.id')
                ->field("crm_customer.*,a.name AS uname,ind.name indus,
                                        (SELECT IFNULL(count(b.cid),0)
                                            FROM `crm_contactrecord` AS b 
                                            WHERE b.customerid=crm_customer.cid AND posttime > (unix_timestamp(now())-{$time})) AS countrecord,
                                        (SELECT IFNULL(SUM(t.acount),0) 
                                            FROM 
                                            (SELECT *,
                                                (SELECT count(*) 
                                                    FROM `crm_resprogress` AS pro 
                                                    WHERE pro.project_id = crm_research.proid AND pro.posttime > (unix_timestamp(now())-{$time})) 
                                                AS acount FROM `crm_research`) AS t 
                                            WHERE t.customerid = crm_customer.cid) AS prosum,
                                        (SELECT IFNULL(count(b.id),0)
                                            FROM `crm_saleservice` AS b 
                                            WHERE b.customer_id=crm_customer.cid AND b.addtime > (unix_timestamp(now())-{$time})) AS sumservice,
                                        (SELECT IFNULL(count(b.id),0)
                                            FROM `crm_saleservice` AS b 
                                            WHERE b.customer_id=crm_customer.cid AND b.sstatus='1' AND b.addtime > (unix_timestamp(now())-{$time})) AS uncheckservice,
                                        (SELECT IFNULL(count(c.id),0)
                                            FROM `crm_onlineservice` AS c 
                                            WHERE c.customer_id=crm_customer.cid AND c.addtime > (unix_timestamp(now())-{$time})) AS sumonline,
                                        (SELECT IFNULL(count(c.id),0)
                                            FROM `crm_onlineservice` AS c 
                                            WHERE c.customer_id=crm_customer.cid AND c.austatus='1' AND c.addtime > (unix_timestamp(now())-{$time})) AS uncheckonline,
                                        (SELECT IFNULL(max(d.otime),0)
                                            FROM `crm_orderform` AS d 
                                            WHERE d.cus_id=crm_customer.cid) AS odtime,
                                        (SELECT IFNULL(SUM(e.oprice),0) 
                                            FROM `crm_orderform` AS e 
                                            WHERE e.cus_id=crm_customer.cid AND (({$ti}-e.otime)<10368000)) AS titotal")
                ->order('countrecord DESC,sumservice DESC,sumonline DESC,cid DESC')
                ->limit($start,$next)
                ->select();

            foreach($data1 as $key => $val) {
                $info[$key]['cusid'] = $val['cid'];
                $info[$key]['cname'] = $val['cname'];
                $info[$key]['indus'] = $val['indus'];
                $info[$key]['countrecord'] = $val['countrecord'];
                $info[$key]['sumservice'] = $val['sumservice'];
                $info[$key]['sumonline'] = $val['sumonline'];
                $info[$key]['titotal'] = $val['titotal'];
                $info[$key]['pname'] = $val['uname'];
                $info[$key]['prosum'] = $val['prosum'];
                $info[$key]['clevel'] = $val['clevel'];
            }
            $data2 = $this->getBusListNAudit('customer', $condi);
        } else {
            if (in_array((int)$roleInfo, $prjArray)) {
                if ($uLevel <= 2) {
                    $count = M('customer')->count('cid');

                    $ti = time();
                    $time = 86400 * $k;
                    $data1 =  M('customer')->where($where)
                        ->join('LEFT JOIN crm_staff AS a ON crm_customer.uid=a.id')
                        ->join('LEFT JOIN crm_industry AS ind ON crm_customer.ctype=ind.id')
                        ->field("crm_customer.*,a.name AS uname,ind.name indus,
                                        (SELECT IFNULL(count(b.cid),0)
                                            FROM `crm_contactrecord` AS b 
                                            WHERE b.customerid=crm_customer.cid AND posttime > (unix_timestamp(now())-{$time})) AS countrecord,
                                        (SELECT IFNULL(SUM(t.acount),0) 
                                            FROM 
                                            (SELECT *,
                                                (SELECT count(*) 
                                                    FROM `crm_resprogress` AS pro 
                                                    WHERE pro.project_id = crm_research.proid AND pro.posttime > (unix_timestamp(now())-{$time})) 
                                                AS acount FROM `crm_research`) AS t 
                                            WHERE t.customerid = crm_customer.cid) AS prosum,
                                        (SELECT IFNULL(count(b.id),0)
                                            FROM `crm_saleservice` AS b 
                                            WHERE b.customer_id=crm_customer.cid AND b.addtime > (unix_timestamp(now())-{$time})) AS sumservice,
                                        (SELECT IFNULL(count(b.id),0)
                                            FROM `crm_saleservice` AS b 
                                            WHERE b.customer_id=crm_customer.cid AND b.sstatus='1' AND b.addtime > (unix_timestamp(now())-{$time})) AS uncheckservice,
                                        (SELECT IFNULL(count(c.id),0)
                                            FROM `crm_onlineservice` AS c 
                                            WHERE c.customer_id=crm_customer.cid AND c.addtime > (unix_timestamp(now())-{$time})) AS sumonline,
                                        (SELECT IFNULL(count(c.id),0)
                                            FROM `crm_onlineservice` AS c 
                                            WHERE c.customer_id=crm_customer.cid AND c.austatus='1' AND c.addtime > (unix_timestamp(now())-{$time})) AS uncheckonline,
                                        (SELECT IFNULL(max(d.otime),0)
                                            FROM `crm_orderform` AS d 
                                            WHERE d.cus_id=crm_customer.cid) AS odtime,
                                        (SELECT IFNULL(SUM(e.oprice),0) 
                                            FROM `crm_orderform` AS e 
                                            WHERE e.cus_id=crm_customer.cid AND (({$ti}-e.otime)<10368000)) AS titotal")
                        ->order('countrecord DESC,sumservice DESC,sumonline DESC,cid DESC')
                        ->limit($page->firstRow,$page->listRows)
                        ->select();
                    $data2 = $this->getBusListNAudit('customer', $condi);
                } else {
                    $staffIds = $this->getStaffIds($uLevel, $dept);
                    $where['uid'] = array('IN', $staffIds);
                    $condi['uid'] = array('IN', $staffIds);
                    $count = M('customer')->count('cid');
                    $ti = time();
                    $time = 86400 * $k;
                    $data1 =  M('customer')->where($where)
                        ->join('LEFT JOIN crm_staff AS a ON crm_customer.uid=a.id')
                        ->join('LEFT JOIN crm_industry AS ind ON crm_customer.ctype=ind.id')
                        ->field("crm_customer.*,a.name AS uname,ind.name indus,
                                        (SELECT IFNULL(count(b.cid),0)
                                            FROM `crm_contactrecord` AS b 
                                            WHERE b.customerid=crm_customer.cid AND posttime > (unix_timestamp(now())-{$time})) AS countrecord,
                                        (SELECT IFNULL(SUM(t.acount),0) 
                                            FROM 
                                            (SELECT *,
                                                (SELECT count(*) 
                                                    FROM `crm_resprogress` AS pro 
                                                    WHERE pro.project_id = crm_research.proid AND pro.posttime > (unix_timestamp(now())-{$time})) 
                                                AS acount FROM `crm_research`) AS t 
                                            WHERE t.customerid = crm_customer.cid) AS prosum,
                                        (SELECT IFNULL(count(b.id),0)
                                            FROM `crm_saleservice` AS b 
                                            WHERE b.customer_id=crm_customer.cid AND b.addtime > (unix_timestamp(now())-{$time})) AS sumservice,
                                        (SELECT IFNULL(count(b.id),0)
                                            FROM `crm_saleservice` AS b 
                                            WHERE b.customer_id=crm_customer.cid AND b.sstatus='1' AND b.addtime > (unix_timestamp(now())-{$time})) AS uncheckservice,
                                        (SELECT IFNULL(count(c.id),0)
                                            FROM `crm_onlineservice` AS c 
                                            WHERE c.customer_id=crm_customer.cid AND c.addtime > (unix_timestamp(now())-{$time})) AS sumonline,
                                        (SELECT IFNULL(count(c.id),0)
                                            FROM `crm_onlineservice` AS c 
                                            WHERE c.customer_id=crm_customer.cid AND c.austatus='1' AND c.addtime > (unix_timestamp(now())-{$time})) AS uncheckonline,
                                        (SELECT IFNULL(max(d.otime),0)
                                            FROM `crm_orderform` AS d 
                                            WHERE d.cus_id=crm_customer.cid) AS odtime,
                                        (SELECT IFNULL(SUM(e.oprice),0) 
                                            FROM `crm_orderform` AS e 
                                            WHERE e.cus_id=crm_customer.cid AND (({$ti}-e.otime)<10368000)) AS titotal")
                        ->order('countrecord DESC,sumservice DESC,sumonline DESC,cid DESC')
                        ->limit($page->firstRow,$page->listRows)
                        ->select();
                    $data2 = $this->getBusListNAudit('customer', $condi);
                }
            } else {
                $data1 = array();
                $data2= array();
            }
        }
        $this->assign(array(
            'data1' => $data1,
            'data2' => $data2
        ));
        $this->display();
        $output = array(
            "draw" => intval($draw),
            "recordsTotal" => $count,
            "recordsFiltered" => $recordsFiltered,
            "data" => $info
        );
        $this->ajaxReturn($output);

    }
    public function showBusinessList()
    {
        $this->display();
    }

    // 获取某客户详情页（包括基本信息，采购历史，相关联系记录等内容）
    public function showBusinessDetail()
    {
        $model = M('customer');
        $cusId = inject_id_filter(I('get.cusId'));
        $map['cid'] = array('EQ', $cusId);
        // uid可能不存在，加一层逻辑判断。
        $uids = $model->where($map)->field('uid')->find();
        if ($uids !== null) {
            $data = $model->where($map)
                ->join('LEFT JOIN crm_staff AS a ON a.id = uid')
                ->join('LEFT JOIN crm_staff AS b ON b.id = founderid')
                ->join('LEFT JOIN crm_staff AS c ON c.id = auditorid')
                ->join('LEFT JOIN crm_industry ind ON ind.id = crm_customer.ctype')
                ->field('crm_customer.*,a.name AS uname,b.name AS foundername,c.name AS auditorname,ind.name indus,
                (SELECT IFNULL(count(*),0) 
                    FROM `crm_orderform` AS d 
                    WHERE d.cus_id = cid AND d.otime > (unix_timestamp(now())-10368000)) AS ordernum,
                (SELECT IFNULL(sum(oprice),0) 
                    FROM `crm_orderform` AS d 
                    WHERE d.cus_id = cid AND d.otime > (unix_timestamp(now())-10368000)) AS ototal,
                (SELECT IFNULL(count(b.cid),0)
                    FROM `crm_contactrecord` AS b 
                    WHERE b.customerid = crm_customer.cid AND posttime > (unix_timestamp(now())-604800)) AS countrecord,
                (SELECT IFNULL(SUM(t.acount),0) 
                        FROM 
                        (SELECT *,
                            (SELECT count(*) 
                                FROM `crm_resprogress` AS pro 
                                WHERE pro.project_id = crm_research.proid AND pro.posttime > (unix_timestamp(now())-604800)) 
                            AS acount FROM `crm_research`) AS t 
                        WHERE t.customerid = crm_customer.cid) AS prosum,
                (SELECT IFNULL(count(b.id),0)
                        FROM `crm_saleservice` AS b 
                        WHERE b.customer_id=crm_customer.cid AND b.addtime > (unix_timestamp(now())-604800)) AS sumservice,
                (SELECT IFNULL(count(c.id),0)
                        FROM `crm_onlineservice` AS c 
                        WHERE c.customer_id=crm_customer.cid AND c.addtime > (unix_timestamp(now())-604800)) AS sumonline,
                (SELECT IFNULL(count(*),0) FROM `crm_cuscontacter` AS d WHERE d.cusid=crm_customer.cid) AS sumcontacter, 
                (SELECT count(*) FROM `crm_cusfile` AS f
                                            WHERE f.cid=crm_customer.cid) AS fnum')
                ->find();
        } else {
            $data = $model->where($map)
                ->join('crm_staff AS b ON b.id = founderid')
                ->join('crm_staff AS c ON c.id = auditorid')
                ->join('LEFT JOIN crm_industry ind ON ind.id = crm_customer.ctype')
                ->field('crm_customer.*,b.name AS foundername,c.name AS auditorname,ind.name indus,
                (SELECT IFNULL(count(*),0) 
                    FROM `crm_orderform` AS d 
                    WHERE d.cus_id = cid AND d.otime > (unix_timestamp(now())-10368000)) AS ordernum,
                (SELECT IFNULL(sum(oprice),0) 
                    FROM `crm_orderform` AS d 
                    WHERE d.cus_id = cid AND d.otime > (unix_timestamp(now())-10368000)) AS ototal,
                    (SELECT IFNULL(count(b.cid),0)
                        FROM `crm_contactrecord` AS b 
                        WHERE b.customerid=crm_customer.cid AND posttime > (unix_timestamp(now())-604800)) AS countrecord,
                (SELECT IFNULL(SUM(t.acount),0) 
                        FROM 
                        (SELECT *,
                            (SELECT count(*) 
                                FROM `crm_resprogress` AS pro 
                                WHERE pro.project_id = crm_research.proid AND pro.posttime > (unix_timestamp(now())-604800)) 
                            AS acount FROM `crm_research`) AS t 
                        WHERE t.customerid = crm_customer.cid) AS prosum,
                (SELECT IFNULL(count(b.id),0)
                        FROM `crm_saleservice` AS b 
                        WHERE b.customer_id=crm_customer.cid AND b.addtime > (unix_timestamp(now())-604800)) AS sumservice,
                (SELECT IFNULL(count(c.id),0)
                        FROM `crm_onlineservice` AS c 
                        WHERE c.customer_id=crm_customer.cid AND c.addtime > (unix_timestamp(now())-604800)) AS sumonline,
                        (SELECT count(*) FROM `crm_cusfile` AS f
                                            WHERE f.cid=crm_customer.cid) AS fnum')
                ->find();
        }

        $data['addr'] = json_decode($data['addr']);

        $cModel = M('cuscontacter');
        $condi_1['cusid'] = array('EQ', $cusId);
        $contacters = $cModel->where($condi_1)
            ->join('crm_staff AS sta ON sta.id = addid')
            ->field('crm_cuscontacter.*,sta.name AS addname')->select();

        $saleModel = D('saleservice');
        $where1['customer_id'] = array('EQ', $cusId);
        $where1['crm_saleservice.addtime'] = array('GT', time() - 604800);
        $saleService = $saleModel->getSaleServiceList($where1);


        $orderModel = D('orderform');
        $where2['cus_id'] = array('EQ', $cusId);
        $where2['otime'] = array('GT', time() - 13068000);
        $orderContents = $orderModel->getOrderList($where2, $cusId);

        $onlineModel = D('onlineservice');
        $where3['customer_id'] = array('EQ', $cusId);
        $where3['crm_onlineservice.addtime'] = array('GT', time() - 604800);
        $onlineService = $onlineModel->getOnlineServiceList($where3);


        $contactModel = D('contactrecord');
        $where4['customerid'] = array('EQ', $cusId);// 项目ID查询条件
        $where4['crm_contactrecord.posttime'] = array('GT', time() - 604800);
        $contacts = $contactModel->getContactList($where4);


        $resModel = M('research');
        $proModel = D('resprogress');
        $where['customerid'] = array('EQ', $cusId);// 项目ID查询条件

        // 获取客户id => 获取客户id下的项目id数组（项目表） => in查询查去在项目ids内的所有更新记录
        $tempPrjIds = $resModel->where($where)->field('proid')->select();
        $ids = getPrjIds($tempPrjIds, 'proid');
        if ($ids !== false) {
            $where5['project_id'] = array('IN', $ids);
            $where5['crm_resprogress.posttime'] = array('GT', time() - 604800);
            $prjProgress = $proModel->where($where5)
                ->join('crm_staff AS sta ON sta.id = prjer_id')
                ->join('crm_research AS res ON res.proid = project_id')
                ->field('crm_resprogress.*,sta.name AS prjername,res.proname AS prjname')
                ->order('posttime DESC')
                ->select();
        }

        $where6['cid'] = array('EQ', $cusId);
        $files = M('cusfile')->where($where6)
            ->join("LEFT JOIN crm_staff AS s ON s.id = builderid")
            ->field('crm_cusfile.*,s.name AS buildername')
            ->select();
        $this->assign(array(
            'data' => $data,
            'contacters' => $contacters,
            'saleService' => $saleService,
            'orderContent' => $orderContents,
            'onlineService' => $onlineService,
            'contacts' => $contacts,
            'prjProgress' => $prjProgress,
            'cusFile' => $files
        ));
        $this->display();
    }

    public function showCustomerList()
    {
        $cusId = inject_id_filter(I('get.cusId'));
        $where['keyword'] = array('EQ', $cusId);
        $rst = M('customer')->where($where)
            ->join('crm_staff AS s ON s.id = crm_customer.founderid')
            ->field('crm_customer.*,s.name AS foundername')
            ->select();
        $this->assign('data', $rst);
        $this->display();
    }
    // 放弃客户
    public function removeCustomer()
    {

        $cusId = inject_id_filter(I('post.id'));
        $map['cid'] = array('EQ', $cusId);
        $rst_1 = M('customer')->where($map)->field('cid,uid')->find();
        if ($rst_1['uid'] != session('staffId')) {
            $msg = 3;
        } else {
            $data = array(
                'uid' => null,
                'cstatus' => 1,
                'auditstatus' => 3
            );
            $rst = M('customer')->where($map)->setField($data);
            if ($rst !== false) {
                $msg = 1;
            } else {
                $msg = 2;
            }
        }

        $this->ajaxReturn($msg);
    }



    /*----------------------------------客户各项记录查看---------------------------------*/
    // 订单
    public function showSaleOrderList()
    {
        $model = D('orderform');

        $cusId = inject_id_filter(I('get.id'));
        $k = I('get.k');
        if ($k == "" || empty($k)) {
            $k = 1;
        }
        $kId = inject_id_filter($k);

        if ($kId == "" || empty($kId)) {
            $kId = 1;
        }
        //四个月
        $month = date("m", time());
        $year = date('Y');
        $lastMonth = $month - 4;
        if ($lastMonth <= 0) {
            $lastMonth = $lastMonth + 12;
            $year = $year - 1;
        }
        $aTime = strtotime($year . "-" . $lastMonth);
        $where['otime'] = array('GT', $aTime);
        $where['cus_id'] = array('EQ', $cusId);// 项目ID查询条件

        $orderContents = $model->getOrderList($where);
        $orderPrice = $model->where($where)->field('sum(oprice) AS totalprice')->find();

        $this->assign(array(
            'data' => $orderContents,
            'odprice' => $orderPrice
        ));
        $this->display();
    }

    // 售后
    public function showSaleServiceList()
    {
        $model = D('saleservice');
        $cusId = inject_id_filter(I('get.id'));
        $k = I('get.k');
        if ($k == "" || empty($k)) {
            $k = 1;
        }
        $kId = inject_id_filter($k);
        $aTime = time() - $kId * 86400;

        $where['customer_id'] = array('EQ', $cusId);// 项目ID查询条件
        $where['crm_saleservice.addtime'] = array('GT', $aTime);
        $saleService = $model->where($where)
            ->join('crm_customer AS cus ON cus.cid = customer_id')
            ->join('crm_staff AS sta ON sta.id = pid')
            ->field("crm_saleservice.*,cus.cname,cus.keyword,cus.uid AS rpbid,sta.name AS pname")
            ->order('addtime DESC')
            ->select();
        $this->assign(array(
            'data' => $saleService,
        ));
        $this->display();
    }

    // 电话客服
    public function showOnlineServiceList()
    {
        $model = D('onlineservice');

        $cusId = inject_id_filter(I('get.id'));
        $k = I('get.k');
        if ($k == "" || empty($k)) {
            $k = 1;
        }
        $kId = inject_id_filter($k);
        $aTime = time() - $kId * 86400;
        $where['customer_id'] = array('EQ', $cusId);// 项目ID查询条件
        $where['crm_onlineservice.addtime'] = array('GT', $aTime);

        $onlineService = $model->getOnlineServiceList($where);

        $this->assign(array(
            'data' => $onlineService,
        ));
        $this->display();
    }

    // 联系记录
    public function showContactRecordList()
    {
        $model = D('contactrecord');

        $cusId = inject_id_filter(I('get.id'));
        $k = I('get.k');
        if ($k == "" || empty($k)) {
            $k = 1;
        }
        $kId = inject_id_filter($k);
        $aTime = time() - $kId * 86400;
        $where['customerid'] = array('EQ', $cusId);// 项目ID查询条件
        $where['crm_contactrecord.posttime'] = array('GT', $aTime);
        //$where['crm_contactrecord.posttime'] = array('GT', $this->timeLimit);


        $contacts = $model->getContactList($where);
        $this->assign(array(
            'data' => $contacts,
        ));
        $this->display();
    }

    // 项目进度
    public function showPrjUpdateList()
    {
        $model = M('research');
        $proModel = D('resprogress');

        $cusId = inject_id_filter(I('get.id'));
        $k = I('get.k');
        if ($k == "" || empty($k)) {
            $k = 1;
        }
        $kId = inject_id_filter($k);
        $aTime = time() - $kId * 86400;
        $where['customerid'] = array('EQ', $cusId);// 项目ID查询条件

        // 获取客户id => 获取客户id下的项目id数组（项目表） => in查询查去在项目ids内的所有更新记录
        $tempPrjIds = $model->where($where)->field('proid')->select();
        $ids = getPrjIds($tempPrjIds, 'proid');
        if ($ids == false) {
            $this->display();
            die;
        }
        $condi['project_id'] = array('IN', $ids);
        $condi['crm_resprogress.posttime'] = array('GT', $aTime);
        $prjProgress = $proModel->where($condi)
            ->join('crm_staff AS sta ON sta.id = prjer_id')
            ->join('crm_research AS res ON res.proid = project_id')
            ->field('crm_resprogress.*,sta.name AS prjername,res.proname AS prjname,res.builderid')
            ->order('posttime DESC')
            ->select();

        $this->assign(array(
            'data' => $prjProgress,
        ));
        $this->display();
    }

    // 文件列表（目前未启用）
    public function showCusFileList()
    {
        $model = M('cusfile');
        $cusId = inject_id_filter(I('get.id'));
        $map['cid'] = array('EQ', $cusId);
        $files = $model->where($map)
            ->join("LEFT JOIN crm_staff AS s ON s.id = builderid")
            ->field('crm_cusfile.*,s.name AS buildername')
            ->select();
        $this->assign(array(
            'data' => $files,
        ));
        $this->display();
    }


    /*----------------------------------客户审核管理-------------------------------------*/
    // 待审列表
    public function showCustomerAudit()
    {
        // 根据组织架构查看内容。
        $id = (int)session('staffId');

        $map['id'] = array('EQ', $id);
        $user = M('staff')->where($map)->field('roleid')->find();
        if ($user['roleid'] != 1) {
            $condi['auditstatus'] = array('EQ', '1');
        } else {
            $condi['auditstatus'] = array('IN', '1,2');
        }
        $data2 = $this->getBusListNAudit('customer', $condi);
        $this->assign(array(
            'data2' => $data2
        ));
        $this->display();
    }

    public function showCustomer()
    {
        if (IS_POST) {
            $posts = I('post.');
            require_once('sphinxapi.php');
            $c = new \SphinxClient();
            $c->setServer('localhost', 9312);
            $c->setMatchMode(SPL_MATCH_ANY);
            $num = mb_strlen($posts['cusName'], 'utf8');
            if ($num <= 1) {
                $this->ajaxReturn(false);die;
            }
            $cusKey = str_replace(array(
                '有限公司','科技有','技有限','有限公', '限公司', '科技','技有','有限','限公','公司',
                'select', 'insert', 'update', 'delete', 'and', 'or', 'where', 'join', '*', '=', 'union', 'into', 'load_file', 'outfile','/','\''),"",$posts['cusName']);
            if (mb_strlen($cusKey) <= 1) {
                $this->ajaxReturn(false);die;
            }
            $cusKey = $posts['cusName'];
            $data1 = $c->Query($cusKey, "dwin");
            $index = array_keys($data1['matches']);
            $index_str = implode(',', $index);
            if ($index_str == null) {
                $this->ajaxReturn(false);die;
            }
            $map['cid'] = array('IN', $index_str);
            $data = M('customer')->where($map)
                ->join('LEFT JOIN crm_industry ind ON ind.id = crm_customer.ctype')
                ->field('crm_customer.cid,crm_customer.cname,crm_customer.addtime,uid,ind.name indusname,
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
            if ($data === false || $data == []) {
                $this->ajaxReturn(false);
            } else {
                $this->ajaxReturn($data);
            }
        } else {
            $this->ajaxReturn(false);
        }

    }
    // 审核处理
    public function checkCustomer()
    {
        if (IS_POST) {
            $auId = inject_id_filter(I('post.auid'));
            $flag = inject_id_filter(I('post.k'));
            $cusId = inject_id_filter(I('post.conid'));
            $map1['cid'] = array('EQ', $cusId);
            $cusMsg = M('customer')->where($map1)->field('type')->find();
            $type = $cusMsg['type'];
            if ($flag == 3) {
                $flag = 2;
            }
            $staffId = (int)session('staffId');
            $map['id'] = array('EQ', $staffId);
            $rst = M('staff')->where($map)->field('roleid')->find();
            if ($rst['roleid'] != 1) {
                if ($auId == $staffId) {
                    if ($flag == 1) {
                        if ($type == 2) {
                            $data = array(
                                'cstatus' => 1,
                                'uid' => null,
                                'auditstatus' => 3
                            );
                        } elseif ($type == 1) {
                            $data = array(
                                'auditstatus' => 4
                            );
                        } else {
                            $data = array(
                                'auditstatus' => 4
                            );
                        }
                    } else {
                        $data = array(
                            'auditstatus' => 2
                        );
                    }
                    $map['cid'] = array('EQ', $cusId);
                    $rst = M('customer')->where($map)->setField($data);
                    if ($rst != false && count($rst) > 0) {
                        $this->ajaxReturn(2);
                    } else {
                        $this->ajaxReturn(3);
                    }
                } else {
                    $this->ajaxReturn(4);//无权限
                }
            } else {
                if ($flag == 1) {
                    if ($type == 1) {
                        //新申请的客户不通过，不反回公共客户池，个人列表可见不合格。
                        $data = array(
                            'auditstatus' => 4
                        );
                    } elseif ($type == 2) {
                        // 公共客户池的客户申请
                        $data = array(
                            'cstatus' => 1,
                            'uid' => null,
                            'auditstatus' => 3
                        );
                    } else {
                        //提交数据修改。客户资料修改数据
                        $data = array(
                            'auditstatus' => 4
                        );
                    }
                } else {
                    if ($type == 1 || $type == 2) {
                        $data = array(
                            'auditstatus' => 3,
                            'type' => 2
                        );
                    } else {
                        $data = array(
                            'auditstatus' => 3,
                            'type' => 2,
                        );
                    }
                }
                $map['cid'] = array('EQ', $cusId);
                $rst = M('customer')->where($map)->setField($data);
                if ($rst) {
                    $this->ajaxReturn(2);
                } else {
                    $this->ajaxReturn(3);
                }
            }
        }
    }

    // 评级列表
    public function showCusLevel()
    {
        $sql = 'SELECT clevel,count(cid) from `crm_customer`
                  WHERE 1 = 1 GROUP BY clevel';
        $data = M('customer')->query($sql);
        $sql_2 = "SELECT
	                a.name AS uname,
	                count(cus.cid) AS totalnum,
	                (SELECT count(cus2.cid) AS levelTnum FROM crm_customer AS cus2 WHERE cus2.clevel=1 and cus2.uid=cus.uid) AS lv1,
	                (SELECT count(cus3.cid) AS levelTnum FROM crm_customer AS cus3 WHERE cus3.clevel=2 and cus3.uid=cus.uid) AS lv2,
	                (SELECT count(cus4.cid) AS levelTnum FROM crm_customer AS cus4 WHERE cus4.clevel=3 and cus4.uid=cus.uid) AS lv3,
	                (SELECT count(cus5.cid) AS levelTnum FROM crm_customer AS cus5 WHERE cus5.clevel=4 and cus5.uid=cus.uid) AS lv4
                  FROM
	                crm_customer AS cus
                  LEFT JOIN crm_staff AS a ON a.id = uid
                  WHERE cus.cstatus = 2
                  GROUP BY
	                uid";
        $data2 = M('customer')->query($sql_2);
        $total = M('customer')->count('cid');

        $this->assign(array(
            'total' => $total,
            'totaldata' => $data,
            'data' => $data2
        ));
        $this->display();
    }

    // 客户评级
    public function resetLevel()
    {
        $year = date('Y');
        // 当前年份
        $season = ceil(date('n')/3);
        if ($season == 1) {
            // 不更新客户级别，延续上年
            $this->ajaxReturn(1);
        } else {
            $start = ($year - 1) . "-01";
            $end = ($year) . "-01";
            $startTime = strtotime($start);
            $endTime = strtotime($end);

            $map['otime'] = array(array('EGT', $startTime),array('LT', $endTime),'AND');
            // 1级客户 采购金额月均大于200000，采购间隔小于3个月
            // 2级 月均采购大于50000 || 年度总金额大于400000
            // 3级 月均大于10000 || 年度大于100000
            // 4级 其他客户
            $rst =M('orderform')
                ->where($map)
                ->field('cus_id,cus.cname AS cusname,IFNULL(count(id),0) AS ordernum,IFNULL(sum(oprice),0) AS totalPrice')
                ->join('LEFT JOIN crm_customer AS cus ON cus_id=cus.cid')
                ->group('cus_id')
                ->select();
            if (count($rst) != 0) {
                foreach($rst as $key =>&$val) {
                    $cIds[] = $val['cus_id'];
                    $val['avg'] = $val['totalprice'] / 12;
                    if ($val['avg'] > 200000) {
                        $data[$key]['clevel'] = 1;
                        $data[$key]['cid'] = $val['cus_id'];
                    } elseif (50000 < $val['avg'] && $val['avg'] < 200000) {
                        $data[$key]['clevel'] = 2;
                        $data[$key]['cid'] = $val['cus_id'];
                    } elseif (10000 < $val['avg'] && $val['avg'] < 50000) {
                        $data[$key]['clevel'] = 3;
                        $data[$key]['cid'] = $val['cus_id'];
                    } else {
                        $data[$key]['clevel'] = 4;
                        $data[$key]['cid'] = $val['cus_id'];
                    }
                }
                $cids = getPrjIds($rst,'cus_id');
                $totalIds = M('customer')->field('cid')->select();
                $Model = M();
                $Model->startTrans();
                foreach ($data as $value) {
                    $map1['cid'] = array('EQ', $value['cid']);
                    $rst1[] = $Model->table('crm_customer')->where($map1)->setField('clevel',$value['clevel']);
                }
                $map2['cid'] = array('NOT IN', $cids);
                $rst2 =$Model->table('crm_customer')->where($map2)->setField('clevel', 4);
                if ($rst1 !=false && $rst2 != false) {
                    $Model->commit();
                    $this->ajaxReturn(2);
                } else {
                    $Model->rollback();
                    $this->ajaxReturn(3);
                }
            } else {
                $this->ajaxReturn(3);
            }
        }

    }

    // 权限
    public function checkAuditRole()
    {
        $id = inject_id_filter(I('post.id'));
        if ($id != (int)session('staffId')) {
            $this->ajaxReturn(1);
        } else {
            if (min($this->uLevels) != 1) {
                $this->ajaxReturn(1);
            } else {
                $this->ajaxReturn(2);
            }
        }
    }

    // 业务员审核售后、客服记录

    public function showUnCheckServiceList()
    {
        $id = (int)session('staffId');
        $map['uid'] = array('EQ', $id);
        $cusData = M('customer')->where($map)->field('cid')->select();
        $cusIds = getPrjIds($cusData, 'cid');
        $filter['customer_id'] = array('IN', $cusIds);
        $filter['sstatus'] = array('EQ', '1');
        $data = M('saleservice')
            ->where($filter)
            ->join('LEFT JOIN crm_customer AS cus ON cus.cid = customer_id')
            ->join('LEFT JOIN crm_staff AS sta ON sta.id = pid')
            ->field("crm_saleservice.*,cus.cname,cus.keyword,cus.uid AS rpbid,sta.name AS pname")
            ->order('addtime DESC')
            ->select();
        $this->assign('data', $data);
        $this->display();
    }

    public function showUnCheckOnlineList()
    {
        $id = (int)session('staffId');
        $map['uid'] = array('EQ', $id);
        $cusData = M('customer')->where($map)->field('cid')->select();
        $cusIds = getPrjIds($cusData, 'cid');
        $filter['customer_id'] = array('IN', $cusIds);
        $filter['austatus'] = array('EQ', '1');
        $data = M('onlineservice')
            ->where($filter)
            ->join('LEFT JOIN crm_customer AS cus ON cus.cid = customer_id')
            ->join('LEFT JOIN crm_staff AS sta ON sta.id = server_id')
            ->field("crm_onlineservice.*,cus.cname,cus.keyword,cus.uid AS rpbid,sta.name AS pname")
            ->order('addtime DESC')
            ->select();
        $this->assign('data', $data);
        $this->display();
    }

    public function checkSaleService()
    {
        if (IS_POST) {
            $auId = inject_id_filter(I('post.auid')); // 创建人id
            $flag = inject_id_filter(I('post.k')); // 审核状态码
            $serviceId = inject_id_filter(I('post.conid')); // 需要审核的编号
            if ($flag == 3) {
                $flag = 2;
            }
            $this->changeRecordStatus($auId, $serviceId, $flag, 'sstatus', 'id', 'saleservice', 2, 3);
        } else {
            $this->ajaxReturn(4);
        }
    }

    public function checkOnlineService()
    {
        if (IS_POST) {
            $auId = inject_id_filter(I('post.auid')); // 创建人id
            $flag = inject_id_filter(I('post.k')); // 审核状态码
            $serviceId = inject_id_filter(I('post.conid')); // 需要审核的编号
            if ($flag == 3) {
                $flag = 2;
            }
            $this->changeRecordStatus($auId, $serviceId, $flag, 'austatus', 'id', 'onlineservice', '2', '3');
        } else {
            $this->ajaxReturn(4);
        }
    }
}