<?php
/**
 * Created by PhpStorm.
 * User: hp
 * Date: 17-5-25
 * Time: 下午4:33
 */  

namespace Dwin\Controller;

use Behavior\ChromeShowPageTraceBehavior;
use Dwin\Model\AuthRoleModel;
use Dwin\Model\CuschangerecordModel;
use Dwin\Model\CustomerModel;
use Dwin\Model\MaterialModel;
use Dwin\Model\OrderChangeRecordModel;
use Dwin\Model\OrderCollectionModel;
use Dwin\Model\OrderformModel;
use Dwin\Model\OrderproductModel;
use Dwin\Model\ProductionPlanModel;
use Dwin\Model\StaffModel;
use Dwin\Model\StockModel;
use Org\Net\Http;
use Think\Upload;
/**
 * 客户关系管理类
 * 用于业务子功能
 */
class CustomerController extends CommonController
{
    const DELETE_AUTH_ID = "65";
    const ONLINE_DEPT    = "7,11,12,22,28,29";
    const FINANCE_ROLE   = "3,10,16";

    //临时使用角色Id
    const ROLE_SALE = [6,15,17,23,26,27,33];
    const ROLE_ONLINE = [7,11,12,22,28,29];
    const ROLE_MARKET = [4,9,18,19,41,42];

    const ROLE_REMOVE_AUTH_SALE = [1,6,15];
    const ROLE_REMOVE_AUTH_MARKET = [1,4,41];
    const ROLE_REMOVE_AUTH_ONLINE = [1,7,11];


    /*------------------审核人权限------------------------*/
    const CUS_AUDIT = "1,3,5,7";
    // 审核人信息
    const CUS_NAME_CHANGE_ROLE = "1,6,7";

    /* 订单状态：2 不合格   3 待审核  5 保存订单*/
    const FINANCE_STATUS = 3;
    const SAVE_STATUS    = 5;
    const UN_STATUS      = 2;
    /** kpi 客户标识*/
    const KPI_FLAG = 1;
    const KPI_CONTACT_LIMIT = 100;

    /**
     * 检测客户名称返回值标识
    */
    const CHECK_CUS_RETURN_ILLEGAL = 5; //字符数非法
    const CHECK_CUS_RETURN_NOT_FIND = 2; //未检索到客户名

    /*客户联系记录状态说明：*/
    static public  $contactTypeMap = [
        1 => '电话',
        2 => '拜访',
        3 => '会议',
        4 => '即时消息(qq、微信等)',
        5 => '邮件',
        6 => '其他',
        7 => '高管约谈',
    ];
    /*当前用户客户权限下的所有用户ID*/
    protected $cusStaffIds;

    public function _initialize()
    {
        parent::_initialize();
        $this->cusStaffIds = $this->getStaffIds((string)$this->staffId, 'cus_child_id', "");

    }

    /**
     * 1 客户添加节点（addCustomer checkCusMsg）
     * @access public
     * @name addCustomer:
     * @param array $post (提交的客户字段)
     * @return string json
     * checkCusMsg:
     * @param array $post(客户名称、电话)
     * @return int $msg
     * 运用了sphinx分词模糊检索
    */
    public function addCustomer()
    {
        // 新客户申报 具备条件可以申请
        if (IS_POST) {
            $this->posts = I('post.');
            $cusModel = new CustomerModel();
            $rst  = $cusModel->addCus($this->posts, $this->staffId);
            if ($rst !== false) {
                $map['cname'] = array('EQ', $this->posts['cname']);
                $changeReason = "客户添加操作，添加人id " . $this->staffId . ',添加时间:'. date('Y-m-d H:i:s');
                $msg = $cusModel->recordCusChangeData($map, $changeReason, $this->staffId);
            } else {
                $msg = array(
                    'msg' => '客户数据添加失败',
                    'status' => 500
                );
            }
            $this->ajaxReturn($msg);
        } else {
            // 获取审核人并返回给模板渲染
            $model = new StaffModel();
            $authRst = $model->getCusAddAuditId();
            $industry = M('industry')->select();
            $indus = getTree($industry, 0, 0,'pid');
            // 个人负责客户列表
            $map['uid'] = array('EQ', $this->staffId);
            $map['cus_pid'] = array('exp', 'IS NULL');
            $cusList = M('customer')->where($map)->field('cid, cname')->select();
            $this->assign(array(
                'arr'   => $authRst,
                'indus' => $indus,
                'cusList' => $cusList
            ));
            $this->display();
        }
    }
    /**
     * @name checkCusMsg
     * 根据提交的客户名进行客户排重
     * 返回前端json数据
    */
    public function checkCusMsg()
    {
        $cusName = trim(I('post.name'));
        //require_once('sphinxapi.php');

        $cusModel = new CustomerModel();
        $num = mb_strlen($cusName, 'utf8');
        if ($num <= 1) {
            $this->returnAjaxMsg('输入非法（字数小于2个字）',self::CHECK_CUS_RETURN_ILLEGAL);
        }
        $cusFilter = array(
            '科技发展有限公司','科技有限公司','技术有限公司','实业有限公司','有限责任公司','电子有限公司','股份有限公司','有限公司','公司','研究所','研究院','市','省',
            '北京', '上海', '天津', '重庆',
            '(',')','（','）','select','insert','update','delete','and','or','where','join','*','=','union','into','load_file','outfile','/','\''
        );
        $cusKey = str_replace(array_merge(explode(",", $this->keyFilter), $cusFilter), "", strtolower($cusName));
        if (mb_strlen($cusKey) <= 1) {
            $this->returnAjaxMsg('输入非法（过滤后字数小于2个字）',self::CHECK_CUS_RETURN_ILLEGAL);
        }
        $cusFindFilter['crm_customer.cname'] = array('EQ', $cusName);
        $cusDataFind = $cusModel->getCusBaseInfo($cusFindFilter, "cid,cname,uid");
        if ($cusDataFind) {
            // 系统中已经有该客户
          $msg = $cusModel->getCusUNameInfo($cusFindFilter,'crm_customer.cname c_name, c.name u_name,b.cname parent_name');
          $this->ajaxReturn($msg);
        }
        $c = new \SphinxClient();
        $c->setServer('localhost', 9312);
        $c->setMatchMode(SPH_MATCH_ALL);
        $data1 = $c->Query($cusKey, "dwin,delta");
        $index = array_keys($data1['matches']);
        $index_str = implode(',', $index);
        $c->close();
        if ($index_str == null) {
            $this->returnAjaxMsg('未检索到客户名',self::CHECK_CUS_RETURN_NOT_FIND);
        }
        $map['crm_customer.cid'] = array('IN', $index_str);
        /** @var string = $sql 查询语句获得录入客户类似名称的信息：负责人、上级公司等*/
        $msg = $cusModel->getCusUNameInfo($map,'crm_customer.cname c_name, c.name u_name,b.cname parent_name');
        $this->ajaxReturn($msg);
    }

    /**
     * 2 修改客户信息节点（editCustomer,checkUser,editCustomerName）
     * checkUser:检查客户负责人，仅负责人有权修改
     * editCustomer:渲染模板并涉及提交修改的数据
     * @todo editCustomer需要记录修改信息，已经有记录，还要优化一下20170927
     * editCustomerName 提交客户名称修改申请
    */


    /**
     * @name editCustomerName 提交客户名称修改申请
     * IS_POST判定是否post传参，不传参则根据GET的客户Id获取用户信息，根据部门经理职位获取部门经理审核人信息
     * 如IS_POST true 将修改信息提交到cuschangerecord表中。等待审核。
     */
    public function editCustomerName()
    {

        $cusModel = new CustomerModel();
        if (IS_POST) {
            $this->posts = I('post.');
            $map['cid'] = array('EQ', (int)$this->posts['cid']);
            $field = "cname";
            $recordModel = new CuschangerecordModel();
            $cusData = $cusModel->getCusBaseInfo($map,$field);
            $changeData = $recordModel->getRecordDataWithEditCustomerName($this->posts,$cusData['cname']);
            $filter['crm_customer.cname'] = array('EQ', $changeData['nowname']);
            $msgCheck = $cusModel->getCusUNameInfo($filter,'crm_customer.cname c_name, c.name u_name,b.cname parent_name');
            if ($msgCheck['status'] == 1) {
                $this->ajaxReturn($msgCheck);
            } else {
                $rst = $recordModel->add($changeData);
                if ($rst) {
                    $this->returnAjaxMsg("申请已提交，部门经理受理后自动修改客户名称",200);
                } else {
                    $this->returnAjaxMsg("提交失败了，请联系管理员",400);
                }
            }
        } else {
            $cusId = inject_id_filter(I('get.cusId'));
            $map['cid'] = array('EQ', $cusId);
            $field = 'cid, cname, uid';
            $data = $cusModel->getCusBaseInfo($map,$field);
            // 审核人信息
            $map_2['role_id']    = array('IN', self::CUS_NAME_CHANGE_ROLE);
            $authIdsFilter['id'] = array('IN', getPrjIds(M('auth_role')->where($map_2)->field('staff_ids')->select(),'staff_ids'));
            $authData = M('staff')->field('id,name')->where($authIdsFilter)->select();
            $this->assign(array(
                'data'     => $data,
                'auth'     => $authData
            ));
            $this->display();
        }
    }

    /**
     * @name showCusEditApply
     * @abstract IS_POST 做判断 false显示审核人为登录人的所有待处理客户名修改申请。
     * IS_POST 为true 处理申请 audi_flag 为是否同意申请
     * 返回 $msg 给前端
    */
    public function showCusEditApply()
    {
        if (IS_POST) {
            // 通过或驳回修改申请
            $this->posts = I('post.');
            // change_ids
            $cusIdArr = explode(",", $this->posts['change_ids']);
            // audi_flag 200 批准 400 不批准
            if (200 == $this->posts['audi_flag']) {
                $updateData = array(
                    'auth_flag' => 3
                );
                M()->startTrans();
                $filter_1['id'] = array('IN', $this->posts['change_ids']);
                $rst = M()->table('crm_cuschangerecord')->where($filter_1)->setField($updateData);
                if ($rst !== false) {
                    for ($i = 0; $i < count($cusIdArr); $i++) {
                        $map[$i]['id'] = array('EQ', $cusIdArr[$i]);
                        $changeData[$i] = M('cuschangerecord')->where($map[$i])->field('cusid cid, nowname cname')->find();
                        $filter_2[$i]['cid'] = array('EQ', $changeData[$i]['cid']);
                        $rst_2[$i] = M()->table('crm_customer')->where($filter_2[$i])->setField($changeData[$i]);
                        if ($rst_2[$i] === false) {
                            M()->rollback();
                            $msg = array(
                                'status' => 400,
                                'msg'   => "批准失败，请联系管理处理"
                            );
                            $this->ajaxReturn($msg);die;
                        }
                    }
                    M()->commit();
                    $msg = array(
                        'status' => 200,
                        'msg' => "已批准并修改了对应客户名"
                    );
                } else {
                    M()->rollback();
                    $msg = array(
                        'status' => 400,
                        'msg'   => "批准失败，请联系管理处理"
                    );
                }
            } else {
                $updateData = array(
                    'auth_flag' => 4
                );
                $filter_1['id'] = array('IN', $this->posts['change_ids']);
                $rst = M('cuschangerecord')->where($filter_1)->setField($updateData);
                if ($rst !== false) {
                    $msg = array(
                        'status' => 300,
                        'msg'   => "已驳回申请，未进行名称修改"
                    );
                } else {
                    $msg = array(
                        'status' => 300,
                        'msg'   => "未驳回申请，未进行名称修改，请联系管理"
                    );
                }
            }
            $this->ajaxReturn($msg);
        } else {
            $filter['auth_flag'] = array('EQ', 2);
            $filter['auth_id']   = array('EQ', $this->staffId);
            $rst = M('cuschangerecord')
                ->alias('a')
                ->field('a.id,oldname,nowname cname,from_unixtime(changetime) change_time,change_reason,b.name u_name,c.name a_name')
                ->join('LEFT JOIN crm_staff b ON b.id = a.change_id')
                ->join('LEFT JOIN crm_staff c ON c.id = a.auth_id')
                ->where($filter)
                ->select();
            $data = $this->getSimilar($rst); // 数组加一列类似名称
            $this->assign('data', $data);
            $this->display();
        }
    }


    /**
     * @name 编辑客户信息
     * @abstract 直接编辑不许审核（编辑后，记录编辑记录）
    */
    public function editCustomer()
    {
        $model = M('customer');

        if (IS_POST) {
            $this->posts = I('post.');
            $cid =  inject_id_filter($this->posts['cid']);
            /*-------------------新数据------------------------*/
            $newData['cname']        = $this->posts['companyName'];
            $newData['ctype']        = inject_id_filter($this->posts['cType']);
            $newData['website']      = $this->posts['Website'];
            $newData['cphonename']   = $this->posts['contactName'];
            $newData['cphonenumber'] = inject_filter($this->posts['companyPhone']);
            $newData['cphoneposition'] = $this->posts['contactPosition'];
            $newData['cus_pid']      = empty($this->posts['parentCus']) ? null : $this->posts['parentCus'];//上级公司id
            $map['cid'] = array('EQ', $cid);
            $cidAudit = M('customer')->where($cid)->field('auditorid')->find();
            $arr = array();
            for ($i = 3; $i > 0; $i--) {
                if ($this->posts['addr' . $i] != "") {
                    array_push($arr, ($this->posts['addr' . $i]));
                }
            }
            $newData['addr'] = json_encode($arr);

            /*-------------------老数据------------------------*/
            $cusIdFilter['cid'] = array('eq', $cid);
            $oldData = M('customer')->where($cusIdFilter)->field('cname, ctype, website, cphonename, cphonenumber, auditorid audit, addr, cus_pid,auditstatus')->find();
            if (!empty($oldData['cus_pid']) && $newData['cus_pid']!=$oldData['cus_pid']) {
                //修改子公司的上级公司 不允许
                $msg = array(
                    'status' => 5,
                    'msg'    => "该公司为附属公司，不能在系统内直接修改该公司的上级公司，请与管理员联系"
                );
                $this->ajaxReturn($msg);
            }
            if (!empty($oldData['cus_pid']) && $newData['cus_pid'] == $cid){
                // 上级公司为自己 不允许
                $msg = array(
                    'status' => 5,
                    'msg'    => "不能以自己的公司为上级公司"
                );
                $this->ajaxReturn($msg);
            }
            if ($oldData['cus_pid'] == $newData['cus_pid'] && $oldData['audit'] == $this->posts['audi'] && $newData['cname'] == $oldData['cname'] && $newData['ctype'] == $oldData['ctype'] && $newData['website'] == $oldData['website'] && $newData['cphonename'] == $oldData['cphonename'] && $newData['cphonenumber'] == $oldData['cphonenumber'] && $newData['addr'] == $oldData['addr'] )
            {
                $msg = array(
                    'status' => 3,
                    'msg'    => "好像没有做出修改，请修改后再重试"
                );
            } else {
                M()->startTrans();
                $changeRecords['cusid']        = $cid;
                $changeRecords['oldname']      = $oldData['cname'];
                $changeRecords['oldctype']     = $oldData['ctype'];
                $changeRecords['oldwebsite']   = $oldData['website'];
                $changeRecords['oldphone']     = $oldData['cphonenumber'];
                $changeRecords['oldphonename'] = $oldData['cphonename'];
                $changeRecords['oldaddr']      = $oldData['addr'];
                $changeRecords['old_cus_pid']  = $oldData['cus_pid'];
                $changeRecords['nowname']      = $newData['cname'];
                $changeRecords['nowctype']     = $newData['ctype'];
                $changeRecords['nowphone']     = $newData['cphonenumber'];
                $changeRecords['nowphonename'] = $newData['cphonename'];
                $changeRecords['nowaddr']      = $newData['addr'];
                $changeRecords['nowwebsite']   = $newData['website'];
                $changeRecords['now_cus_pid']  = $newData['cus_pid'];
                $changeRecords['changetime']   = time();
                $changeRecords['change_id']    = $this->staffId;

                $tap_1 = M()->table('crm_cuschangerecord')->add($changeRecords);

                $newData['auditorid'] = (int)$this->posts['audi'];
                if ($oldData['auditstatus'] == 4) {
                    $newData['auditstatus'] = 1;
                }
                $map['cid'] = array('EQ', $cid);
                $tap_2 = M()->table('crm_customer')->where($map)->setField($newData);

                if ($tap_1 > 0 && $tap_2 > 0) {
                    M()->commit();
                    $msg = array(
                        'status' => 2,
                        'msg'    => "修改客户信息成功，请返回查看"
                    );
                } else {
                    M()->rollback();
                    $msg = array(
                        'status' => 3,
                        'msg'    => "修改失败，重试仍出现此提示，请联系管理员解决"
                    );
                }
            }
            $this->ajaxReturn($msg);
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
                    ->field('crm_customer.*,a.name AS uname,b.name AS foundername,c.name AS auditorname, ind.name indusname,ind.id indid')
                    ->find();
            } else {
                $data = $model->where($map)
                    ->join('LEFT JOIN crm_staff AS b ON b.id = founderid')
                    ->join('LEFT JOIN crm_staff AS c ON c.id = auditorid')
                    ->join('LEFT JOIN crm_industry ind ON ind.id = ctype')
                    ->field('crm_customer.*, b.name AS foundername, c.name AS auditorname, ind.name indusname,ind.id indid')
                    ->find();
            }

            $ownCusFilter = array(
                'uid'         => array('eq', $this->staffId),
                'cus_pid'     => array('exp', 'is null or cus_pid = ""'),
                'auditstatus' => array('eq', "3")
            );
            $ownCus = M('customer')->where($ownCusFilter)->field('cid id, cname name')->select();

            $map['roleid'] = array('IN', self::CUS_AUDIT);
            $auditor = M('staff')->where($map)->field('id,name')->select();
            $data['addr'] = json_decode($data['addr']);
            $industry = M('industry')->select();
            $indus = getTree($industry, 0, 0, 'pid');
            $this->assign(array(
                'data'     => $data,
                'audi'     => $auditor,
                'industry' => $indus,
                'ownCus'   => $ownCus
            ));
            $this->display();
        }
    }

    /**
     * 判断客户负责人是否为user
    */
    public function checkUser()
    {
        $cid = inject_id_filter(I('post.cid'));
        $filter['cid'] = array('EQ', $cid);
        $rst = D('customer')->getCusBaseInfo($filter, 'cid,uid');
        $msg = ($rst['uid'] != $this->staffId) ? 1 : 2 ;
        $this->ajaxReturn($msg);
    }

    /**
     * 批量修改客户行业信息
     * 之前启用，目前禁用，后续如需使用，改为public
    */
    private function changeIndus()
    {
        $postData = I('post.');
        $indus   = explode(',', $postData['indus']);
        $ids     = explode(",", $postData['ids']);
        $countId = count($ids);

        for ($i = 0; $i < $countId; $i++) {
            $map[$i]['cid'] = array('EQ', $ids[$i]);
            if ($indus[$i] == "null") {
                $indus[$i] = 19;
            }
            $changeData[$i]['ctype'] = $indus[$i];
            $rst[$i] = M('customer')->where($map[$i])->setField($changeData[$i]);
        }
        if ($rst) {
            $this->ajaxReturn(2);
        } else {
            $this->ajaxReturn(1);
        }
    }
    protected function delCustomerAuth($id)
    {
        $authArray = explode(",", self::DELETE_AUTH_ID);
        $flag = in_array($id, $authArray) ? 200 : 403;
        return $flag;
    }

    /**
     * @name deleteCustomer
     * @abstract 删除客户方法
     * 传值 post
    */
    public function deleteCustomer()
    {
        $idArray = I('post.cus_id');
        $this->staffId = self::DELETE_AUTH_ID;
        $cusModel = new CustomerModel();
        $authFlag = $this->delCustomerAuth($this->staffId);
        if (200 == $authFlag) {
            $rst = $cusModel->delTrans($idArray);
            $this->ajaxReturn($rst);
        } else {
            $this->returnAjaxMsg('无权限', 400);
        }
    }

    /**
     * 3 查看客户列表节点
     * showbusinesslist,showbusinessdata,（权限约束）
     * showbusinessdetail,showcontactrecordlist,showprjupdatelist,showonlineservicelist,
     * showsaleservicelist,showsaleorderlist
     * showBusinessList showBusinessData:查看权限下的客户列表
     * 其他方法为各项记录的查看
     * @todo showBusinessList showBusinessData客户列表查看涉及权限，系统权限架构修改后需要调整 20171011已更新
     */


    private function sphinxSearch($search)
    {
        $c = new \SphinxClient();
        $c->setServer('localhost', 9312);
        $c->setMatchMode(SPL_MATCH_All);
        $c->SetLimits(0, 200);
        $cusKey = $search;
        $data1 = $c->Query($cusKey, "cusfilter");
        $index = array_keys($data1['matches']);
        $index_str = implode(',', $index);
        return $index_str;
    }
    /**
     * 客户业务列表数据获取
     * 根据post得到的参数获取满足条件的客户数据
     * $this->posts['k']为客户类型限制（个人客户、下属客户）
     * $this->posts['s']为客户记录更新时间限制（24小时 1周 所有
     * @todo 后续加一个option是否包含子公司
     */
    public function showBusinessData()
    {
        $cusModel = new CustomerModel();
        // 根据组织架构查看内容。
        $this->posts = I('post.');
        // 获取Datatables发送的参数 必要
        $draw = $this->posts['draw'];
        // 获取条件信息
        $this->sqlCondition = $this->getSqlCondition($this->posts);



        // 根据条件，显示对应内容的客户
        //@var string $k 前端传递的参数，预处理之后得到对应需要显示的客户类型
        //@var string $s 联系时间限制,预处理得到对应的n天内联系记录的客户

        $cusType       = empty($this->posts['k']) ? 'cus-1' : $this->posts['k'];
        $cusTimeLimit  = empty($this->posts['s']) ?  1 : (int)$this->posts['s'];
        $cusChildLimit = empty($this->posts['hasChild']) ? false : true;
        $cusKpiLimit   = empty($this->posts['kpiFlag'])  ? false : true;

        /*-------------获取当前用户对应权限下的ids，查询对应记录----------------*/
        $ti = time();

        // 权限下的合格客户列表查询条件
        if (in_array($cusTimeLimit, array(1, 7))) {
            $time = $ti - 86400 * $cusTimeLimit;
            $changeFilter = array(
                'k.countrecord' => array('NEQ', 0),
                'k.sumservice'  => array('NEQ', 0),
                'k.sumonline'   => array('NEQ', 0),
                '_logic'        => 'OR'
            );
        } elseif ($cusTimeLimit == 30) {
            $changeFilter = array();
            $time = $ti - 86400 * 60;
        } else {
            $time = $ti - 86400 * $cusTimeLimit;
            $changeFilter = array(
                'k.countrecord' => array('GT', - 1)
            );
        }


        $staffId = ($cusType == 'cus-1')
            ? (string)$this->staffId
            : empty($this->cusStaffIds) ? (string)$this->staffId : $this->cusStaffIds;
        $config = [
            'timeLimit'   => $time,
            'cusLimit'    => $cusChildLimit,
            'kpiLimit'    => $cusKpiLimit,
            'staffLimit'  => $staffId,
            'searchLimit' => trim($this->sqlCondition['search']),
            'searchFlag'  => false
        ];
        if ($cusTimeLimit != 30) {
            $sqlFilter = $cusModel->getOwnCustomerWithMap($config);
            $count = $cusModel->table($sqlFilter . " k")->where($changeFilter)->count();


            if (!empty($config['searchLimit'])) {
                $config['searchFlag'] = true;
            }
            $sqlFilter_2 = $cusModel->getOwnCustomerWithMap($config);
            $recordsFiltered = $cusModel->table($sqlFilter_2 . " k")->where($changeFilter)->count();
            $data1 =$cusModel->filterCus($changeFilter, $sqlFilter_2, $this->sqlCondition['order'], $this->sqlCondition['start'], $this->sqlCondition['length']);
        } else {
            $count =$cusModel->getWarningCusNum($config);


            if (!empty(trim($this->sqlCondition['search']))) {
                $config['searchFlag'] = true;
                $recordsFiltered = $cusModel->getWarningCusNum($config);
            } else {
                $recordsFiltered = $count;
            }

            $data1 = $cusModel->getWarningCusData($this->sqlCondition,$config);
        }

        if (count($data1) != 0) {
            if ($cusTimeLimit != 30) {
                foreach($data1 as $key => &$val) {
                    $val['DT_RowId']    = $val['cid'];
                    $val['DT_RowClass'] = 'gradeX';
                    $val['online']['tot']  = $val['sumonline'];
                    $val['online']['un']   = $val['uncheckonline'];
                    $val['amount']['all']     = $val['titotal'];
                    $val['amount']['checked'] = $val['total_amount'];
                }
            } else {
                foreach($data1 as $key => &$val) {
                    $val['DT_RowId']    = $val['cid'];
                    $val['DT_RowClass'] = 'gradeX';
                    $val['amount']['all']     = $val['titotal'];
                    $val['amount']['checked'] = $val['total_amount'];
                }
            }
        } else {
            $data1 = "";
        }

        $output = array(
            "draw"            => intval($draw),
            "recordsTotal"    => $count,
            "recordsFiltered" => $recordsFiltered,
            "data"            => $data1
        );
        $this->ajaxReturn($output);
    }



    /**
     * 业务列表加载
     * assign 客户待审核数据到页面
     * @todo 后续加一个option是否包含子公司
     */
    public function showBusinessList()
    {
        $ti = time();
        $cusModel = new CustomerModel();
        $condi['auditstatus'] = array('NEQ', '3');
        $staffIds = empty($this->cusStaffIds) ? (string)$this->staffId : $this->cusStaffIds . "," . (string)$this->staffId;
        $condi['uid'] = array('IN', $staffIds);
        $cusNum = M('customer')->where($condi)->count('cid');

        $this->field = "crm_customer.*,b.name AS uname,c.name AS auditorname,ind.name indus,
                                        (SELECT IFNULL(COUNT(record.cid),0) 
                                            FROM `crm_contactrecord` record 
                                            WHERE record.customerid = crm_customer.cid AND record.posttime > ({$ti} - 86400*7)) AS recordnum";
        $order = '`crm_customer`.addtime asc, crm_customer.cname DESC';
        $data2 = $cusNum ? $cusModel->getBusListNAudit($condi, $this->field, 0, 500,'cid', $order) : array();
        $this->assign('data2', $data2);
        $this->display();
    }


    /**
     * 获取某客户详细内容，依次为：订单 售后 电话客服 联系记录 项目进度 文件列表（目前未启用）
     */

    public function showBusinessDetail()
    {
        if (IS_POST) {
            // 修改客户信息
            /**
             * id    : contactId,
                name  : contact,
                phone : phone,
                tel   : tel,
                qq    : qq,
                wechat: wechat,
                mail  : mail,
                postion : postion
             * id name phone
            */
            $this->posts = I('post.');
            $setData = array(
                'id'        => $this->posts['id'],
                'name'      => $this->posts['name'],
                'phone'     => $this->posts['phone'],
                'tel'       => $this->posts['tel'],
                'qqnum'     => $this->posts['qq'],
                'wechatnum' => $this->posts['wechat'],
                'emailaddr' => $this->posts['mail'],
                'position'  => $this->posts['position']
            );
            $recordData = M('cuscontacter')->field('id contactid,cusid,name,phone,tel,qqnum,wechatnum,emailaddr,position')->find($this->posts['id']);
            $recordData['new_name']     = $setData['name'];
            $recordData['new_phone']    = $setData['phone'];
            $recordData['new_tel']      = $setData['tel'];
            $recordData['new_qq']       = $setData['qqnum'];
            $recordData['new_wechat']   = $setData['wechatnum'];
            $recordData['new_email']    = $setData['emailaddr'];
            $recordData['new_position'] = $setData['position'];
            $recordData['change_time']  = time();
            $recordData['change_id']    = session('staffId');

            M()->startTrans();
            $rst = M()->table('crm_cuscontacter')->setField($setData);
            if ($rst) {
                $res = M()->table('crm_cuscontacter_change_record')->add($recordData);
                if ($res) {
                    M()->commit();
                    $msg['status'] = 2;
                    $msg['msg']    = "联系方式修改成功";
                } else {
                    M()->rollback();
                    $msg['status'] = 3;
                    $msg['msg']    = "记录修改失败，事务回滚";
                }
            } else {
                M()->rollback();
                $msg['status'] = 1;
                $msg['msg']    = "提交修改失败，事务回滚";
            }
            $this->ajaxReturn($msg);
        } else {
            $model = M('customer');
            $cusId = inject_id_filter(I('get.cusId'));
            $map['crm_customer.cid'] = array('EQ', $cusId);
            // uid可能不存在，加一层逻辑判断。
            $uids  = $model->where($map)->field('uid')->find();
            $aTime = (int)($this->timeLimit);
            if ($uids !== null) {
                $data = $model->where($map)
                    ->join('LEFT JOIN crm_staff AS a ON a.id = uid')
                    ->join('LEFT JOIN crm_staff AS b ON b.id = founderid')
                    ->join('LEFT JOIN crm_staff AS c ON c.id = auditorid')
                    ->join('LEFT JOIN crm_industry ind ON ind.id = crm_customer.ctype')
                    ->join('LEFT JOIN crm_customer cus ON crm_customer.cid = cus.cus_pid and cus.cus_pid is not null')
                    ->field("crm_customer.*,crm_customer.annual_order_amount ototal,a.name AS uname,b.name AS foundername,c.name AS auditorname,ind.name indus,GROUP_CONCAT(cus.cname) son_name,
                    (SELECT IFNULL(count(*),0) FROM `crm_orderform` AS d WHERE d.cus_id = crm_customer.cid) AS ordernum,
                    (SELECT IFNULL(count(b.cid),0) FROM `crm_contactrecord` AS b WHERE b.customerid = crm_customer.cid) AS countrecord,
                    (SELECT IFNULL(SUM(t.acount),0) 
                        FROM 
                        (SELECT *,
                            (SELECT count(*) 
                                FROM `crm_resprogress` AS pro 
                                WHERE pro.project_id = crm_research.proid AND pro.posttime > ({$aTime})) 
                            AS acount FROM `crm_research`) AS t 
                        WHERE t.customerid = crm_customer.cid) AS prosum,
                    (SELECT IFNULL(count(b.sid),0)
                        FROM `crm_salerecord` AS b 
                        WHERE b.cusid=crm_customer.cid AND b.is_ok != 4 AND b.change_status_time > ({$aTime})) AS sumservice,
                    (SELECT IFNULL(count(c.id),0)
                        FROM `crm_onlineservice` AS c 
                        WHERE c.customer_id=crm_customer.cid AND c.addtime > ({$aTime})) AS sumonline,
                    (SELECT IFNULL(count(*),0) FROM `crm_cuscontacter` AS d WHERE d.cusid=crm_customer.cid) AS sumcontacter, 
                    (SELECT count(*) FROM `crm_cusfile` AS f
                                            WHERE f.cid=crm_customer.cid) AS fnum")
                    ->order('crm_customer.cid')
                    ->find();
            } else {
                $data = $model->where($map)
                    ->join('LEFT JOIN crm_staff AS b ON b.id = founderid')
                    ->join('LEFT JOIN crm_staff AS c ON c.id = auditorid')
                    ->join('LEFT JOIN crm_industry ind ON ind.id = crm_customer.ctype')
                    ->join('LEFT JOIN crm_customer cus ON FIND_IN_SET(crm_customer.cid,cus.cus_pid)')
                    ->field("crm_customer.*,b.name AS foundername,c.name AS auditorname,ind.name indus,GROUP_CONCAT(cus.cname) son_name,crm_customer.annual_order_amount as ototal,
                    (SELECT IFNULL(count(b.cid),0)
                        FROM `crm_contactrecord` AS b 
                        WHERE b.customerid=crm_customer.cid AND posttime > ({$aTime})) AS countrecord,
                    (SELECT IFNULL(SUM(t.acount),0) 
                        FROM 
                        (SELECT *,
                            (SELECT count(*) 
                                FROM `crm_resprogress` AS pro 
                                WHERE pro.project_id = crm_research.proid AND pro.posttime > ({$aTime}))-604800)) 
                            AS acount FROM `crm_research`) AS t 
                        WHERE t.customerid = crm_customer.cid) AS prosum,
                    (SELECT IFNULL(count(b.sid),0)
                        FROM `crm_salerecord` AS b 
                        WHERE b.cusid=crm_customer.cid AND b.change_status_time > (unix_timestamp(now())-604800)) AS sumservice,
                    (SELECT IFNULL(count(c.id),0)
                        FROM `crm_onlineservice` AS c 
                        WHERE c.customer_id=crm_customer.cid AND c.addtime > ({$aTime})) AS sumonline,
                        (SELECT count(*) FROM `crm_cusfile` AS f
                                            WHERE f.cid=crm_customer.cid) AS fnum")
                    ->order('crm_customer.cid')
                    ->find();
            }
            $data['addr'] = json_decode($data['addr']);
            if ($data['cus_pid']) {
                $filter['cid'] = array('EQ', $data['cus_pid']);
                $data['sub_name'] = M('customer')->where($filter)->field('cname sub_name')->find();
            }
            $cModel = M('cuscontacter');
            $condi_1['cusid'] = array('EQ', $cusId);
            $contacters = $cModel->where($condi_1)
                ->join('crm_staff AS sta ON sta.id = addid')
                ->field('crm_cuscontacter.*,sta.name AS addname')->select();

            $saleModel = D('salerecord');
            $where1['cusid'] = array('EQ', $cusId);
            $where1['crm_salerecord.change_status_time'] = array('GT', $aTime);
            $saleService = $saleModel->getSaleServiceList($where1);


            $orderModel = new OrderformModel();
            $where2['cus_id'] = array('EQ', $cusId);
            //$where2['otime'] = array('GT', time() - 13068000);

            $this->field = "crm_orderform.cpo_id,
                  crm_orderform.id,
                  oname,
                  order_type,
                  oprice cur_num,
                  cus_name cusname,
                  d.order_type_name,
                  f.logistics_type_name log_type,
                  g.freight_payment_name freight_payment_method,
                  group_concat(b.repertory_name) ware_house,
                  c.settle_name,
                  i.invoice_situation_name inv_situation,
                  h.invoice_name inv_type,
                  j.check_type_name audit_status,
                  from_unixtime(otime) time,
                  pic_name staname,
                  pic_phone staff_phone,
                  stock_status,
                  production_status";

            $orderContents = $orderModel->getOrderDataUseGroup($this->field,$where2,'crm_orderform.id','crm_orderform.id desc', 0, 50);


            $onlineModel = D('onlineservice');
            $where3['customer_id'] = array('EQ', $cusId);
            $where3['crm_onlineservice.addtime'] = array('GT', $aTime);
            $onlineService = $onlineModel->getOnlineServiceList($where3);


            $contactModel = D('contactrecord');
            $where4['customerid'] = array('EQ', $cusId);
            //$where4['crm_contactrecord.posttime'] = array('GT', $aTime);
            $contacts = $contactModel->getContactList($where4);


            $resModel = M('research');
            $proModel = D('resprogress');
            $where['customerid'] = array('EQ', $cusId);

            // 获取客户id => 获取客户id下的项目id数组（项目表） => in查询查去在项目ids内的所有更新记录
            $tempPrjIds = $resModel->where($where)->field('proid')->select();
            $ids = getPrjIds($tempPrjIds, 'proid');
            if ($ids !== false) {
                $where5['project_id'] = array('IN', $ids);
                $where5['crm_resprogress.posttime'] = array('GT', $aTime);
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
                'data'          => $data,
                'contacters'    => $contacters,
                'saleService'   => $saleService,
                'orderContent'  => $orderContents,
                'onlineService' => $onlineService,
                'contacts'      => $contacts,
                'prjProgress'   => $prjProgress,
                'cusFile'       => $files
            ));
            $this->display();
        }
    }
    
    /**
     * @name showBusinessData
     * @abstract获取订单基本数据列表
     * 根据get得到的参数客户id
     * return 返回数据为json数组 包含订单的时间、状态等基本情况
     */
    public function showSaleOrderList()
    {
        $model = new OrderformModel();
        $cusId = inject_id_filter(I('get.id'));
        //四个月
        $month = date("m", time());
        $year  = date('Y');
        $lastMonth = $month - 4;
        if ($lastMonth <= 0) {
            $lastMonth  = $lastMonth + 12;
            $year = $year - 1;
        }
        $aTime = strtotime($year . "-" . $lastMonth);
        $where['otime'] = array('GT', $aTime);
        $where['cus_id'] = array('EQ', $cusId);// 客户ID查询条件
        /*field:order_K3 otime order_type invoice_situation invoice_type check_status oprice*/
        $this->field = "crm_orderform.order_id, from_unixtime(otime) otime,d.order_type_name order_type,i.invoice_situation_name invoice_situation,h.invoice_name invoice_type, j.check_type_name check_status,oprice";
        $orderContents = $model->getOrderDataUseGroup($this->field, $where, 'crm_orderform.id desc', 'crm_orderform.id', 0, 10);
        //$orderPrice = $model->where($where)->field('sum(oprice) AS totalprice')->find();

        foreach ($orderContents as $key => &$val) {
            $val['order_K3'] = $val['order_id'];
        }
        $this->ajaxReturn($orderContents);
    }
    
    /**
     * 获取维修记录信息
    */
    public function showSaleServiceList()
    {
        $cusId = inject_id_filter(I('get.id'));
        $k = I('get.k');
        if ($k == "" || empty($k)) {
            $k = 1;
        }
        $kId = inject_id_filter($k);
        if (in_array($kId, array(2, 30))) {
            $aTime = $this->timeLimit;
        } elseif($kId == 1) {
            $aTime = time() - $kId * 86400;
        }else {
            $aTime = time() - $kId * 86400 * 7;
        }
        //查询主表
        $where['crm_salerecord.cusid']              = array('EQ', $cusId);// 客户ID查询条件
        $where['crm_salerecord.is_over']            = array('EQ', '0');// 客户ID查询条件
        $where['crm_salerecord.change_status_time'] = array('GT',$aTime);
        $result = M('salerecord')
            ->join(' LEFT JOIN crm_customer as c ON  c.cid = crm_salerecord.cusid ')
            ->join(' LEFT JOIN crm_salerecord_isok as i ON  i.id = crm_salerecord.is_ok ')
            ->field('sid,sale_number,is_show,i.name as is_ok')
            ->where($where)
            ->order('sid desc')
            ->limit(3)
            ->select();
        foreach ($result as $val){
            $saleid[] = $val['sid'];
        }
        $map['saleid']        = array('IN', $saleid);
        $map['change_status'] = array('NEQ','6');
        $map['change_status_time'] = array('GT',$this->timeLimit);
        $result1 = M('salerecordchange')
            ->field('saleid,change_status,change_status_time,changemanname,changemanid,repersonorderid,oldreperson_message,
                    newreperson_message,oldnum_message,newnum_message,oldrestatus_message,newrestatus_message,audit_flag,s.name as change_status,s.id')
            ->join(' LEFT JOIN crm_salerecordchange_status as s ON s.id = crm_salerecordchange.change_status')
            ->where($map)
            ->order('change_status_time desc')
            ->select();
        //总维修记录个数
        $count = count($result);
        //更新记录条数
        $count1 = count($result1);
        for($i = 0; $i<$count; $i++){
            switch ($result[$i]['is_show']) {
                case 0 :
                    $result[$i]['is_show'] = "未审核";
                    break;
                case 1 :
                    $result[$i]['is_show'] = "有效";
                    break;
                case 2 :
                    $result[$i]['is_show'] = "无效";
                    break;
            }
            $list[$i] = array(
                'sale_number' => $result[$i]['sale_number'],
                'is_show'     => $result[$i]['is_show'],
                'is_ok'       => $result[$i]['is_ok'],
            );

            for($j = 0; $j<$count1; $j++){
                //处理数组取出 saleid = sid 的更新记录
                $a[$i]['data'][$j] = $result1[$j];
                if($a[$i]['data'][$j]['saleid'] == $result[$i]['sid']){
                    $list[$i]['data'][$j] = $a[$i]['data'][$j];
                    $list[$i]['data'][$j]['change_status_time'] = date('Y-m-d H:i:s',$a[$i]['data'][$j]['change_status_time']);
                    switch ($list[$i]['data'][$j]['id']) {
                        case 7:
                            $list[$i]['data'][$j]['change_status'] = "更改维修人";
                            break;
                        case 8 :
                            $old = $list[$i]['data'][$j]['oldnum_message'];
                            $new = $list[$i]['data'][$j]['newnum_message'];
                            $list[$i]['data'][$j]['change_status'] = "更改维修品数量 " . $old . '==>>' . $new;
                            break;
                        case 9 :
                            $condition[] = $list[$i]['data'][$j]['oldrestatus_message'];
                            $condition[] = $list[$i]['data'][$j]['newrestatus_message'];
                            $where1['id'] = array('IN',$condition);
                            $status = M('repairperson_restatus')->field('name')->where($where1)->select();
                            $old = $status[0]['name'];
                            $new = $status[1]['name'];
                            $list[$i]['data'][$j]['change_status'] = "更新维修单状态 " . $old . '==>' . $new;
                            break;
                    }
                }
            }
            //键重新重0排列
            $list[$i]['data'] = array_values($list[$i]['data']);
        }
        $this->ajaxReturn($list);
    }

    /**
     * 获取客服记录信息
     */
    public function showOnlineServiceList()
    {
        $model = D('onlineservice');

        $cusId = inject_id_filter(I('get.id'));
        $k = I('get.k');
        if ($k == "" || empty($k)) {
            $k = 1;
        }

        $kId = inject_id_filter($k);
        if (in_array($kId,array(2, 30))) {
            $aTime = $this->timeLimit;
        } elseif($kId == 1) {
            $aTime = time() - $kId * 86400;
        }else {
            $aTime = time() - $kId * 86400;
        }
        $where['customer_id'] = array('EQ', $cusId);// 项目ID查询条件
        $where['crm_onlineservice.addtime'] = array('GT', $aTime);

        $onlineService = $model->getOnlineServiceList($where);
        foreach ($onlineService as &$val) {
            $val['addtime'] = date('Y-m-d H:i:s',$val['addtime']);
            switch ($val['austatus']) {
                case 1 :
                    $val['austatus'] = "未审核";
                    break;
                case 2 :
                    $val['austatus'] = "有效";
                    break;
                case 3 :
                    $val['austatus'] = "无效";
                    break;
            }

        }
        $this->ajaxReturn($onlineService);
    }
    
    /**
     * 获取联系记录信息
     */
    public function showContactRecordList()
    {
        $model = D('contactrecord');

        $cusId = inject_id_filter(I('get.id'));
        $kId   = inject_id_filter(I('get.k')) == 30 ? 60 : inject_id_filter(I('get.k'));
        $aTime = time() - $kId * 86400;

        $where['customerid'] = array('EQ', $cusId);
        $where['crm_contactrecord.posttime'] = array('GT', $aTime);

        $contacts = array_slice($model->getContactList($where), 0, 5);
        foreach ($contacts as &$val) {
            $val['posttime'] = date('Y-m-d H:i:s',$val['posttime']);
            $val['ctype'] = self::$contactTypeMap[$val['ctype']];
        }
        $this->ajaxReturn($contacts);
    }


    /**
     * 获取联系记录信息
     */
    public function getRecordMsg()
    {
        if(IS_POST) {
            $cusId = I('post.cusId');
            $model = D('contactrecord');
            $where['customerid'] = array('EQ', $cusId);// 项目ID查询条件

            $contacts = array_slice($model->getContactList($where), 0, 10);
            foreach ($contacts as &$val) {
                $val['posttime'] = date('Y-m-d H:i:s',$val['posttime']);
                $val['ctype'] = self::$contactTypeMap[$val['ctype']];
            }
            $this->ajaxReturn($contacts);
        } else {
            $map['uid'] = array('eq', $this->staffId);
            $data = M('customer')
                ->where($map)
                ->join('LEFT JOIN crm_staff c ON crm_customer.uid = c.id')
                ->field('cid, cname, uid, c.name uname, max_contact_time')->select();
            foreach($data as &$val)
            {
                if (empty($val['max_contact_time'])) {
                    $val['max_contact_time'] = "无记录";
                } else {
                    $val['max_contact_time'] = date('Y-m-d H:i:s', $val['max_contact_time']);
                }
            }
            $this->assign('data', $data);
            $this->display();
        }
    }

    /**
     * 项目进展情况
    */
    public function showPrjUpdateList()
    {
        $model = M('research');
        $proModel = D('resprogress');

        $cusId = inject_id_filter(I('get.id'));
        $k = I('get.k');
        if (empty($k)) {
            $k = 1;
        }
        $kId = inject_id_filter($k);
        if (in_array($kId, array(2, 30))) {
            $aTime = $this->timeLimit;
        } elseif($kId == 1) {
            $aTime = time() - $kId * 86400;
        }else {
            $aTime = time() - $kId * 86400;
        }
        $where['customerid'] = array('EQ', $cusId);// 项目ID查询条件

        // 获取客户id => 获取客户id下的项目id数组（项目表） => in查询查去在项目ids内的所有更新记录
        $tempPrjIds = $model->where($where)->field('proid')->select();
        $ids = getPrjIds($tempPrjIds, 'proid');

        $condi['project_id'] = array('IN', $ids);
        $condi['crm_resprogress.posttime'] = array('GT', $aTime);
        $prjProgress = $proModel->where($condi)
            ->join('crm_staff AS sta ON sta.id = prjer_id')
            ->join('crm_research AS res ON res.proid = project_id')
            ->field('crm_resprogress.*,sta.name AS prjername,res.proname AS prjname,res.builderid')
            ->order('posttime DESC')
            ->select();

        foreach ($prjProgress as &$val) {
            $val['posttime'] = date('Y-m-d H:i:s', $val['posttime']);
        }
        $this->ajaxReturn($prjProgress);
    }

    /**
     * 文件列表
    */
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

    /**
     * 4 放弃客户节点（removeCustomer）
     * 放弃选择的客户 并且记录后台记录到客户修改表
     * @param string $cusId
     * @return string $msg
     */
    public function removeCustomer()
    {
        $authFlag = 10;//放弃客户flag;
        $returnStatusSuccess  = 1;
        $returnStatusFail     = 2;
        $returnStatusNoAuth   = 3;
        $returnStatusHasPid   = 4;
        $returnStatusHasChild = 5;
        $returnStatusCannotAdd  = 6;

        $postFlagHasPid = 4;
        $postFlagHasChild = 5;
        $postFlagArray = [4, 5];

        if (IS_POST) {
            $cusChangeModel = new CuschangerecordModel();
            $cusModel = new CustomerModel();
            $cusId = inject_id_filter(I('post.cusId'));
            if (empty($cusId)) {
                $this->returnAjaxMsg('参数不全', $returnStatusCannotAdd);
            }
            $removeFlag = $cusChangeModel->getAbandonCusData($cusId);
            if (count($removeFlag) !== 0) {
                $this->returnAjaxMsg('您已经提交过申请，不必重复提交', $returnStatusCannotAdd, $removeFlag);
            }
            $map['cid'] = array('EQ', $cusId);
            $rst_1 = $cusModel->getCusBaseInfo($map, 'cid,uid,cus_pid,cname');
            if (!I('post.flag')) {
                // 判定是不是负责人
                if ($rst_1['uid'] != $this->staffId) {
                    $this->returnAjaxMsg('非负责人',$returnStatusNoAuth);
                } else {
                    // 判定是否为子公司
                    if ($rst_1['cus_pid']) {
                        $filter['cid'] = array('EQ', $rst_1['cus_pid']);
                        $rst_2 = M('customer')->where($filter)->field('cname name')->find();
                        $msg = array('status' => $returnStatusHasPid, 'name' => $rst_2['name']);
                        $this->ajaxReturn($msg);
                    } else {
                        // 判定是否有子公司
                        $map_3['cus_pid'] = array('EQ', $cusId);
                        $rst_3 = M('customer')->where($map_3)->find();
                        if ($rst_3) {
                            $this->returnAjaxMsg('有关联公司',$returnStatusHasChild);
                        } else {
                            // 无子公司，无父公司直接放弃 20181008修改
                            // 记录客户放弃信息
                            $rst = $cusChangeModel->abandonCustomer($rst_1['cid'],I('post.authId'),$authFlag, $rst_1['cname']);
                            $this->returnAjaxMsg('',$rst === false ? $returnStatusFail : $returnStatusSuccess);
                        }
                    }
                }
            } else {
                $flag = I('post.flag');
                if (in_array($flag, $postFlagArray)) {
                    if($flag == $postFlagHasPid) {
                        $subMap['cid'] = array('EQ', $rst_1['cus_pid']);
                        $subId = M('customer')->where($subMap)->field('cid id')->find();
                        $sonMap['cus_pid'] = array('EQ', $subId['id']);
                        $sonId = M('customer')->where($sonMap)->field('cid id')->select();
                        $ids = $subId['id'] . "," . getPrjIds($sonId, "id");

                    } else {
                        // 有子公司
                        $sonMap['cus_pid'] = array('EQ', $cusId);
                        $sonId = M('customer')->where($sonMap)->field('cid id')->select();
                        $ids = $cusId . "," . getPrjIds($sonId, "id");
                    }
                    $idsCondition['cid'] = array('IN', $ids);
                    $abandonCusData = M('customer')->where($idsCondition)->field('cid,cname,uid')->select();

                    foreach ($abandonCusData as $cusData) {
                        if ($cusData['uid'] != $this->staffId) {
                            $this->returnAjaxMsg("关联公司出现了负责人不一致的情况，请找管理员处理BUG",$returnStatusCannotAdd);die;
                        }
                    }
                    $changeRecordRst = $cusChangeModel->abandonCustomerAll($abandonCusData, I('post.authId'));
                    $msg['status'] = ($changeRecordRst === false) ? $returnStatusFail : $returnStatusSuccess;
                    $this->ajaxReturn($msg);
                } else {
                    $this->returnAjaxMsg('', $returnStatusSuccess);
                }
            }
        } else {
            $condition['_string'] = "FIND_IN_SET({$this->staffId},staff_ids)";
            $role = M('auth_role')->where($condition)->select();
            $map['role_id'] =['in', getPrjIds($role,'role_id')];
            $authRoleModel = new AuthRoleModel();
            $roleData = $authRoleModel->getRoleList("*",$map,'pid',0,1000);
            $roleId = $roleData[0]['role_id'];
            if (in_array($roleId,self::ROLE_MARKET)) {
                $map['role_id'] = ['in', self::ROLE_REMOVE_AUTH_MARKET];
                $flag = true;
            } elseif (in_array($roleId,self::ROLE_ONLINE)) {
                $map['role_id'] = ['in', self::ROLE_REMOVE_AUTH_ONLINE];
                $flag = false;
            } elseif (in_array($roleId,self::ROLE_SALE)) {
                $map['role_id'] = ['in', self::ROLE_REMOVE_AUTH_SALE];
                $flag = true;
            } else {
                $flag = false;
            }
            if ($flag) {
                $roleDataTmp = $authRoleModel->getRoleList("*", $map,'pid',0,1000);
                $staffIds = getPrjIds($roleDataTmp,'staff_ids');
                $mapAuth['id'] = ['in', $staffIds];
            } else {
                $mapAuth['_string'] = "find_in_set({$this->staffId},cus_child_id)";
            }

            $authData = M('staff')->where($mapAuth)->field('id,name')->select();


            $cusMap['cid'] = ['eq', I('get.cusId')];
            $customerModel = new CustomerModel();
            $cusChildMap['cus_pid'] = ['eq', I('get.cusId')];
            $tmp = $customerModel->getCusBaseInfo($cusMap,'cid,cname,cus_pid');
            $childTmp = $customerModel->getCusBaseInfo($cusMap,'cid,cname,cus_pid');
            $data['hasPid'] = ($tmp['cus_pid'] || count($childTmp) !== 0) ? '是' : "否";
            $data['cusId'] = $tmp['cid'];
            $data['cusName'] = $tmp['cname'];
            $this->assign(compact('data', 'authData'));
            $this->display();

        }

    }

    public function showRemoveList()
    {
        $authFlag = 10;
        $authFlagArray = [11,12];
        $true = 11;
        if (IS_POST) {
            $this->posts = I('post.');
            if (empty($this->posts['changeIds'])) {
                $this->returnAjaxMsg('没选中客户', 400);
            }
            if (!in_array($this->posts['authFlag'], $authFlagArray)) {
                $this->returnAjaxMsg('是否同意该客户的放弃请求未知', 400);
            }
            $searchMap['id'] = ['IN', $this->posts['changeIds']];
            $validateData = M('cuschangerecord')->where($searchMap)->select();
            foreach ($validateData as $item) {
                if ($item['auth_id'] != $this->staffId) {
                    $this->returnAjaxMsg('您不是受理人，请核实', 400);
                }
                $filterMap['cusid'] = ['eq', $item['cusid']];
                $filterMap['auth_flag'] = ['eq', $authFlag];
                $filterMap['auth_id'] = ['eq', $this->staffId];
                $setData['auth_flag'] = $this->posts['authFlag'];
                $setRst =  M('cuschangerecord')->where($filterMap)->setField($setData);
                if (false === $setRst) {
                    $this->returnAjaxMsg('同意失败1',400);
                }
                if ($this->posts['authFlag'] == $true) {
                    $data = array(
                        'uid'         => null,
                        'cstatus'     => 1,
                        'auditstatus' => 3
                    );
                    $customerMap['cid'] = ['eq', $item['cusid']];
                    $cusRemoveRst = M('customer')->where($customerMap)->setField($data);
                    if (false === $cusRemoveRst) {
                        $this->returnAjaxMsg('同意失败2',400);
                    }
                }

                $changeData = array(
                    'cusid'         => $item['cusid'],
                    'changetime'    => time(),
                    'change_id'     =>  $this->staffId,
                    'auth_id'       => $this->staffId,
                    'auth_flag'      => 1,
                    'oldname'       =>$item['oldname'],
                    'change_reason' => "该客户于" . date("Y-m-d H:i:s",time()) . "执行了客户放弃审核操作，审核" . $true == $this->posts['authFlag'] ? '通过' : '驳回'
                );
                $record = M('cuschangerecord')->add($changeData);
                if (false === $record) {
                    $this->returnAjaxMsg('同意失败',400);
                }
            }
            $this->returnAjaxMsg('受理完毕', 200);

        } else {
            $filter['auth_id'] = ['EQ', $this->staffId];
            $filter['auth_flag'] = ['EQ', $authFlag];
            $field = "a.id,oldname,nowname cname,cusid,from_unixtime(changetime) change_time,change_reason,b.name u_name,c.name a_name";
            $data = M('cuschangerecord')
                ->alias('a')
                ->where($filter)
                ->field($field)
                ->join('LEFT JOIN crm_staff b ON b.id = a.change_id')
                ->join('LEFT JOIN crm_staff c ON c.id = a.auth_id')
                ->select();
            $this->assign('data', $data);
            $this->display();
        }
    }

    /**
     * 5 申请客户节点（showCommonCustomerList businessApplication applicationOk）
     * 公共池客户申请
     * businessApplication:申请客户的信息
     * applicationOk:提交申请并返回结果
     * @todo businessApplication UI有待优化
     */

    /**
     * showCommonCustomerList
     * 公共客户池列表
     */
    public function showCommonCustomerList()
    {
        if (IS_POST) {
            $this->posts = I('post.');
            //获取Datatables发送的参数 必要
            $draw = $this->posts['draw'];
            $this->sqlCondition = $this->getSqlCondition($this->posts);


            $where['crm_customer.cstatus'] = array('EQ', '1');
            $count = M('customer')->where($where)->count('cid');
            //$where['c.founderid'] = array('eq','.id');
            // 连表查询获得创建人姓名
            if (!empty($this->sqlCondition['search'])) {
                $where['crm_customer.cname|crm_industry.name|crm_customer.province|crm_staff.name'] = array('LIKE', "%" . $this->sqlCondition['search'] . "%");
            }
            $filterCount = M('customer')
                ->join('LEFT JOIN crm_industry ON crm_industry.id = crm_customer.ctype')
                ->join('LEFT JOIN crm_staff ON crm_staff.id = crm_customer.founderid')
                ->where($where)->count('cid');
            $commonSql = D('customer')->getCommonCustomerList($where, $this->sqlCondition['order'], $this->sqlCondition['start'], $this->sqlCondition['length']);
            $data = D('customer')->getCommonCustomerData($commonSql);

            if (count($data) != 0) {
                foreach($data as $key => &$val) {
                    $val['DT_RowId']    = $val['cid'];
                    $val['DT_RowClass'] = 'gradeX';
                    $val['indus']       = empty($val['indus']) ? "待修改" : $val['indus'];
                    if ($val['sub_name']) {
                        $val['sub_name'] = $val['sub_name'];
                    }
                    if ($val['son_name']) {
                        $val['sub_name'] = $val['son_name'];
                    }
                    $val['sub_name'] = empty($val['sub_name']) ? "" : $val['sub_name'];
                }
            } else {
                $data = "";
            }
            $output = array(
                "draw"            => intval($draw),
                "recordsTotal"    => $count,
                "recordsFiltered" => $filterCount,
                "data"            => $data
            );
            $this->ajaxReturn($output);
        } else {
            $flag = $this->delCustomerAuth($this->staffId);
            $this->assign('auth', json_encode($flag));
            $this->display();
        }
    }

    /**
     * businessApplication 客户申请页面
    */
    public function businessApplication()
    {
        $model = M('customer');
        $cusId = inject_id_filter(I('get.cusId'));
        $map['crm_customer.cid'] = array('EQ', $cusId);

        $data = $model->where($map)
            ->join('crm_staff AS b ON b.id = founderid')
            ->join('crm_staff AS c ON c.id = auditorid')
            ->join('LEFT JOIN crm_industry ind ON ind.id = crm_customer.ctype')
            ->join('LEFT JOIN crm_customer d ON d.cid = crm_customer.cus_pid')
            ->join('LEFT JOIN crm_customer e ON FIND_IN_SET(crm_customer.cid, e.cus_pid)')
            ->field('crm_customer.*,b.name AS builder_name,c.name AS auditor_name,ind.name indus,d.cname sub_name,GROUP_CONCAT(e.cname) son_name,
            (SELECT IFNULL(count(*),0) 
                FROM `crm_orderform` AS d 
                WHERE d.cus_id = crm_customer.cid) AS ordernum,
            (SELECT IFNULL(sum(oprice),0) 
                FROM `crm_orderform` AS d 
                WHERE d.cus_id = crm_customer.cid AND d.check_status = 4) AS ototal,
                (SELECT IFNULL(count(b.cid),0)
                    FROM `crm_contactrecord` AS b 
                    WHERE b.customerid = crm_customer.cid AND posttime > (unix_timestamp(now())-6048000)) AS countrecord,
            (SELECT IFNULL(SUM(t.acount),0) 
                FROM 
                (SELECT *,
                    (SELECT count(*) 
                        FROM `crm_resprogress` AS pro 
                        WHERE pro.project_id = crm_research.proid AND pro.posttime > (unix_timestamp(now())-6048000)) 
                    AS acount FROM `crm_research`) AS t 
                WHERE t.customerid = crm_customer.cid) AS prosum,
            (SELECT IFNULL(count(b.sid),0)
                FROM `crm_salerecord` AS b 
                WHERE b.cusid=crm_customer.cid AND b.change_status_time > (unix_timestamp(now())-6048000)) AS sumservice,
            (SELECT IFNULL(count(c.id),0)
                FROM `crm_onlineservice` AS c 
                WHERE c.customer_id = crm_customer.cid AND c.addtime > (unix_timestamp(now())-6048000)) AS sumonline')
            ->find();

        $data['addr'] = json_decode($data['addr']);

        $cModel = M('cuscontacter');
        $condi_1['cusid'] = array('EQ', $cusId);
        $contacters = $cModel->where($condi_1)
            ->join('crm_staff AS sta ON sta.id = addid')
            ->field('crm_cuscontacter.*,sta.name AS addname')->select();

        $saleModel = D('salerecord');
        $where1['cusid'] = array('EQ', $cusId);
        $where1['crm_salerecord.change_status_time'] = array('GT', time() - 6048000);
        $saleService = $saleModel->getSaleServiceList($where1);


        $orderModel = D('orderform');
        $where2['cus_id'] = array('EQ', $cusId);
        $where2['otime'] = array('GT', time() - 13068000);
        $this->field = "crm_orderform.id,crm_orderform.oname,crm_orderform.oprice,crm_orderform.otime,crm_orderform.pic_name,crm_orderform.order_type,crm_orderform.check_status";
        $orderContents = $orderModel->getOrderList($where2, $this->field);

        $onlineModel = D('onlineservice');
        $where3['customer_id'] = array('EQ', $cusId);
        $where3['crm_onlineservice.addtime'] = array('GT', time() - 6048000);
        $onlineService = $onlineModel->getOnlineServiceList($where3);


        $contactModel = D('contactrecord');
        $where4['customerid'] = array('EQ', $cusId);// 项目ID查询条件
        $where4['crm_contactrecord.posttime'] = array('GT', time() - 6048000);
        $contacts = $contactModel->getContactList($where4);


        $resModel = M('research');
        $proModel = D('resprogress');
        $where['customerid'] = array('EQ', $cusId);// 项目ID查询条件

        // 获取客户id => 获取客户id下的项目id数组（项目表） => in查询查去在项目ids内的所有更新记录
        $tempPrjIds = $resModel->where($where)->field('proid')->select();
        $ids = getPrjIds($tempPrjIds, 'proid');
        if ($ids !== false) {
            $where5['project_id'] = array('IN', $ids);
            $where5['crm_resprogress.posttime'] = array('GT', time() - 6048000);
            $prjProgress = $proModel->where($where5)
                ->join('crm_staff AS sta ON sta.id = prjer_id')
                ->join('crm_research AS res ON res.proid = project_id')
                ->field('crm_resprogress.*,sta.name AS prjername,res.proname AS prjname')
                ->order('posttime DESC')
                ->select();
        }

        $conditions['roleid'] = array('IN', self::CUS_AUDIT);
        $auIds = M('staff')->where($conditions)->field('id,name')->select();
        $this->assign(array(
            'data' => $data,
            'contacters'    => $contacters,
            'saleService'   => $saleService,
            'orderContent'  => $orderContents,
            'onlineService' => $onlineService,
            'contacts'      => $contacts,
            'prjProgress'   => $prjProgress,
            'auId'          => $auIds
        ));
        $this->display();
    }

    /**
     * 提交客户申请操作
     * @todo 逻辑有问题，last_uids不加申请人。审核后才加
    */
    public function applicationOk()
    {
        // 申请flag：1=>有关联公司 2=>无关联公司
        $this->posts = I('post.');
        $model = M('customer');
        $cid = inject_id_filter($this->posts['cid']);
        $data = array(
            'uid'         => $this->staffId,
            'last_uid'    => $this->staffId,
            'cstatus'     => 2,
            'auditstatus' => 1,
            'auditorid'   => $this->posts['auditorid'],
            'max_contact_time' => time(),
            'max_order_time'   => time()
        );
        $map['crm_customer.cid'] = array('EQ', $cid);
        $uid = M('customer')->where($map)->field('uid,cus_pid,last_uids,cname,FROM_UNIXTIME(max_order_time) t1,FROM_UNIXTIME(max_contact_time) t2')->find();
        $uidArr = explode(",", $uid['last_uids']);
        // 去除数组中的客户专员id
        $roleFilter['role_id'] = array('IN', self::ONLINE_DEPT);
        $onlineIds = D('auth_role')->getRoleList('staff_ids', $roleFilter, 'staff_ids',0, 500);
        $onlineIds = getPrjIds(array_filter($onlineIds),'staff_ids');
        $onlineArr = explode(",", $onlineIds);
        if (!in_array($this->staffId, $onlineArr)) {
            if (in_array($this->staffId, $uidArr)) {
                $msg['status'] = 403;
                $msg['msg'] = "负责过该客户，不能继续申请";
                $this->ajaxReturn($msg);
            }
        }
        if ($uid['uid']) {
            $msg['status'] = 404;
            $msg['msg'] = "该客户被别人申请了";
        } else {
            if ($this->posts['flag'] == 2) {
                $rst = $model->where($map)->setField($data);
                if ($rst !== false) {
                    $msg['status'] = 200;
                    $msg['msg'] = "客户申请成功";
                } else {
                    $msg['status'] = 405;
                    $msg['msg'] = "该客户申请失败，请联系管理";
                }
            } else {
                // 有关联公司需要处理对应的关系
                if ($uid['cus_pid']) {
                    // 为某家客户子公司情况
                    $filter['crm_customer.cid'] = array('EQ', $uid['cus_pid']);
                    $subId = M('customer')->where($filter)->field('cid, uid')->find();
                    if (!$subId['uid']) {
                        // uid 为空 一起申请
                        $map_2['crm_customer.cid'] = array('EQ', $subId['cid']);
                        $sonId = M('customer')->where($map_2)
                            ->field('crm_customer.cid, GROUP_CONCAT(b.cid) son_id')
                            ->join('LEFT JOIN crm_customer b ON crm_customer.cid = b.cus_pid and b.cus_pid is not null')
                            ->group('crm_customer.cid')
                            ->order('crm_customer.cid')
                            ->find();
                        $ids = $subId['cid'] . "," . $sonId['son_id'];
                        $map_3['crm_customer.cid'] = array('IN', $ids);
                        $rst = $model->where($map_3)->setField($data);
                        if ($rst !== false) {
                            $msg['status'] = 200;
                            $msg['msg'] = "客户申请成功（含子公司）";
                        } else {
                            $msg['status'] = 405;
                            $msg['msg'] = "该客户及子公司申请失败，请联系管理";
                        }
                    } else {
                        // uid 不为空，对比staffid 与uid 不一致返回，一致可以申请
                        if ($this->staffId != $subId['uid']) {
                            $msg['status'] = 406;
                            $msg['msg'] = "该客户禁止申请";
                        } else {
                            $rst = $model->where($map)->setField($data);
                            if ($rst !== false) {
                                $msg['status'] = 200;
                                $msg['msg'] = "客户申请成功（含子公司）";
                            } else {
                                $msg['status'] = 407;
                                $msg['msg'] = "该客户及子公司申请失败，请联系管理";
                            }
                        }
                    }
                } else {
                    // 为主公司，对应子公司直接申请
                    // $uid = M('customer')->where($map)->field('uid,cus_pid')->find();
                    $sonId = M('customer')->where($map)
                        ->field('crm_customer.cid, GROUP_CONCAT(b.cid) son_id')
                        ->join('LEFT JOIN crm_customer b ON crm_customer.cid = b.cus_pid and b.cus_pid is not null')
                        ->group('crm_customer.cid')
                        ->order('crm_customer.cid')
                        ->find();
                    $ids = $cid . "," . $sonId['son_id'];
                    $map_3['crm_customer.cid'] = array('IN', $ids);
                    $rst = $model->where($map_3)->setField($data);
                    if ($rst !== false) {
                        $msg['status'] = 200;
                        $msg['msg'] = "客户申请成功！";
                    } else {
                        $msg['status'] = 408;
                        $msg['msg'] = "该客户申请失败，请联系管理";
                    }
                }
            }
        }
        if ($msg == 2) {
            $cusFilter1['cid'] = array('EQ', $cid);
            $cusData = M('customer')->where($cusFilter1)->field('cid cusid,cname oldname')->find();
            $cusData['change_id'] = $this->staffId;
            $cusData['changetime'] = $data['max_contact_time'];
            $cusData['change_reason'] = "客户申请操作，操作时间：" . date("Y-m-d H:i:s") . "last_uid = " . $uid['last_uid'] . "原保护时间(订单 联系记录依次排序)：" . $uid['t1'] . "," . $uid['t2'];

            M('cuschangerecord')->add($cusData);
        }
        $this->ajaxReturn($msg);
    }

    /**
     * 6 添加联系人节点（addCusContact checkCusContact）
     * checkCusContact:检查添加信息是否重复
     * addCusContact:执行添加
     * @todo 排重核查20170927
     */

    /**
     * 添加客户联系人
    */
    public function addCusContact()
    {
        $model = M('cuscontacter');
        if (IS_POST) {
            $this->posts = I('post.');
            $data = array(
                'name'      => inject_filter($this->posts['firstname']),
                'position'  => inject_filter($this->posts['positionName']),
                'phone'     => inject_filter($this->posts['phoneNum']),
                'cusid'     => inject_id_filter($this->posts['cid']),
                'tel'       => inject_filter($this->posts['telNum']),
                'emailaddr' => inject_filter($this->posts['pEmail']),
                'wechatnum' => inject_filter($this->posts['weChat']),
                'qqnum'     => inject_filter($this->posts['qqNum']),
                'addid'     => $this->staffId,
                'addtime'   => time()
            );
            if ($fin = $model->create($data)) {
                $rst = $model->add($fin);
                $msg = $rst ? 1 : 2;
            } else {
                $msg = 2;
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

    /**
     * 检查客户联系人的电话号码是否唯一
    */
    public function checkCusContact()
    {
        if (IS_POST) {
            $phoneNum = inject_filter(I('post.number'));
            $map['phone'] = array('EQ', $phoneNum);
            $map['cusid'] = array('EQ', I('post.cusId'));
            $rst = M('cuscontacter')->where($map)->find();
            $msg = $rst ? 2 : 1;
            $this->ajaxReturn($msg);
        }
    }

    /**
     * 7 修改联系人节点（editCusContact）
     * checkCusContact:检查添加信息是否重复
     * addCusContact:执行添加
     * @todo 空缺，需要添加该功能，修改后核查是否与已有联系人重复
     */

    /**
     * 8 修改联系人节点（showCusContact）
     * @todo 空缺，如有必要添加该功能，查看个人的所有联系人
     */

    /**
     * 9 上传文件节点（uploadCusFile uploadFile）
     * @todo mime类型的优化以及提醒
     */

    /**
     * 检查客户的所有人
    */
    public function checkCusUName()
    {
        $this->posts = I('post.');
        $cusId = inject_id_filter($this->posts['cusId']);
        if ($cusId != false) {
            $map['cid'] = array("EQ", $cusId);
            $rst = M('customer')->where($map)->field('uid')->find();
            $msg = ($rst['uid'] != $this->staffId) ? 1 : 2;
        } else {
            $msg = 1;
        }
        $this->ajaxReturn($msg);
    }

    /**
     * 上传文件的页面
    */
    public function uploadCusFile()
    {
        $cusId = inject_id_filter(I('get.cusId'));
        $this->assign('cusId', $cusId);
        $this->display();
    }

    /**
     * 文件上传方法
     * @todo $ext $cfg封装
    */
    public function uploadFile()
    {
        $this->posts = I('post.');
        $cusId = inject_id_filter($this->posts['cid']);

        if ($cusId != false) {
            $map['cid'] = array("EQ", $cusId);

            $rst = M('customer')->where($map)->field('uid')->find();
            if ($rst['uid'] != $this->staffId) {
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
                        $saveMsg = array(
                            'cid'       => $cusId,
                            'addtime'   => time(),
                            'builderid' => $this->staffId,
                            'fpath'     => UPLOAD_ROOT_PATH . "/" . $cusId . "/" . $file['savepath'] . $file['savename'],
                            'fname'     => $fName,
                            'hasfile'   => '1'
                        );
                        $rst = M('cusfile')->add($saveMsg);
                        echo json_encode($data);
                    }
                }
            }
        }
    }

    /**
     * 10 下载文件（download）
     * @todo 各个浏览器的测试，是否有另存为
     */
    public function download()
    {
        $id = inject_id_filter(I('get.id'));
        $model = M('cusfile');
        $filter['fid'] = array('EQ', $id);
        $data = $model->where($filter)->find();
        $map['cid'] = array('EQ', $data['cid']);
        $cus = M('customer')->field('cid,uid')->find($data['cid']);

        if ($cus['uid'] == $this->staffId) {
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


    /**
     * 11 添加联系记录（）
     * 新的联系人自动保存
     */
    public function addContactRecords()
    {
        $conModel = M('contactrecord');

        if (IS_POST) {
            if(I('post.contactId')) {
                // 搜索客户联系方式返回数据
                $limit['id'] = array('eq', I('post.contactId'));
                $type = I('post.type');
                if ($type == 4) {
                    // 微信、qq concat_ws(',','11','22','33')
                    $field = "id, name, CONCAT_WS(',',wechatnum,qqnum) number";
                } elseif ($type == 5) {
                    // 邮箱
                    $field = "id, name, emailaddr number";
                } else {
                    // 电话
                    $field = "id, name, phone number";
                }
                $contactData = M('cuscontacter')->where($limit)->field($field)->find();
                $this->ajaxReturn($contactData);
            }
            // 添加联系记录
            // 更新联系记录保护时间
            // 查联系方式是否存在，不存在添加。
            $this->posts = I('post.');
            $data = array(
                'ctype'      => $this->posts['contact-type'],
                'ctime'      => strtotime($this->posts['contact-time']),
                'theme'      => $this->posts['contact-theme'],
                'content'    => nl2br($this->posts['contact-content']),
                'contact'    => $this->posts['contact-name'],
                'contact_num' => $this->posts['contact-num'],
                'customerid' => $this->posts['cusid'],
                'picid'      => $this->staffId,
                'posttime'   => time(),
            );
            $limit['name'] = array('EQ', $data['contact']);
            $limit['cusid'] = array('EQ', $data['customerid']);
            $contactFlag   = M('cuscontacter')->where($limit)->field('id')->find();
            if (!$contactFlag) {
                if ($data['ctype'] == 5) {
                   $setData = array(
                       'cusid'     => $data['customerid'],
                       'name'      => $data['contact'],
                       'emailaddr' => $data['contact_num'],
                       'addtime'   => time(),
                       'addid'     => session('staffId')
                   );
                } elseif ($data['ctype'] == 4) {
                    $setData = array(
                        'cusid'     => $data['customerid'],
                        'name'      => $data['contact'],
                        'wechatnum' => $data['contact_num'],
                        'qqnum' => $data['contact_num'],
                        'addtime'   => time(),
                        'addid'     => session('staffId')
                    );
                } else {
                    $setData = array(
                        'cusid' => $data['customerid'],
                        'name'  => $data['contact'],
                        'phone' => $data['contact_num'],
                        'addtime'   => time(),
                        'addid'     => session('staffId')
                    );
                }
                $rst = M('cuscontacter')->add($setData);
            }
            M()->startTrans();
            // 开启事务，添加联系记录并更新联系时间：
            //1 添加的记录为总公司，直接添加记录并更新总公司的联系时间
            //2 添加的记录为分公司记录，添加后分别更新分公司和总公司的联系时间。
            $cusType = M('customer')->field('cid, cus_pid, kpi_flag')->find($data['customerid']);//添加的客户信息的id
            if (self::KPI_FLAG == $cusType['kpi_flag']) {
                $filterString = [' ','  ','\n','\r','\t'];
                $filteredString = str_replace($filterString, "", trim($data['content']));
                if(mb_strlen($filteredString) < self::KPI_CONTACT_LIMIT) {
                    $this->returnAjaxMsg('KPI客户，您至少需要提交100个字段联系记录','3');
                }
            }
            if ($fin = $conModel->create($data)) {
                $rst_1 = M()->table('crm_contactrecord')->filter('strip_tags')->add($fin);
                if ($rst_1) {
                    $data_2 = array(
                        'cid'              => $data['customerid'],
                        'max_contact_time' => (int)$data['posttime']
                    );
                    $map_2['cid'] = $data_2['cid'];
                    if (empty($cusType['cus_pid'])) {
                        //1添加的记录为总公司，直接添加记录并更新总公司的联系时间
                        // $rst_2 = M()->table('crm_customer_statistics')->filter('strip_tags')->save($data_2);
                        $rst_2 = M('customer')->filter('strip_tags')->where($map_2)->save($data_2);
                        $msg['status'] = $rst_2 ? 1 : 2;
                        if ($rst_2) {
                            M()->commit();
                            $msg['status'] = 1;
                        } else {
                            M()->rollback();
                            $msg['status'] = 2;
                            $msg['msg'] = "更新客户保护期失败，请截图当前联系记录内容邮件管理员";
                        }
                    } else {
                        //2添加的记录为分公司记录，添加后分别更新分公司和总公司的联系时间。
                        $data_3 = array(
                            'cid' => $cusType['cus_pid'],
                            'max_contact_time' => time()
                        );

                        $map_3['cid'] = $data_3['cid'];
                        $rst_2 = M()->table('crm_customer')->where($map_2)->save($data_2);
                        $rst_3 = M()->table('crm_customer')->where($map_3)->save($data_3);
                        if ($rst_2 && $rst_3) {
                            M()->commit();
                            $msg['status'] = 1;
                        } else {
                            M()->rollback();
                            $msg['status'] = 2;
                            $msg['msg']  = "您更新的记录未更新客户联系记录保护期，事务已回滚";
                        }
                    }
                } else {
                    M()->rollback();
                    $msg['status'] = 2;
                    $msg['msg'] = "联系记录添加失败，事务回滚";
                }
            } else {
                $msg['status'] = 3;
                $msg['msg'] = "填写内容非法，添加记录失败";
            }
            $this->ajaxReturn($msg);
        } else {
            $model = M('customer');
            $id = inject_id_filter(I('get.cusId'));
            // 客户姓名
            $resCondition['cid'] = array('EQ', $id);
            $data1 = $model->where($resCondition)->field('cname')->select();
            // 客户联系人菜单
            $cusFilter['cusid'] = array('eq', $id);
            $contactData = M('cuscontacter')->where($cusFilter)->field('id, name, position,phone')->select();
            $cusFilter2['cid'] = array('EQ', $id);
            $contactData[] = M('customer')->where($cusFilter2)->field('cid id,cphonename name,cphonenumber phone')->find();
            $this->assign(array(
                'data' => $data1,
                'contactSel' => $contactData
            ));
            $this->display();
        }
    }

    /**
     * 12 查看联系人节点（showContactRecordList）
     * @todo 空缺
     */

    /**
     * 13 修改联系人节点（editContactRecord）
     * @todo 空缺，如有必要添加该功能
     */

    /**
     * 14 添加订单节点（addSaleOrder）
     */


    /**
     * getCusData 根据传入的$id,获取cusid = $id 的客户信息以及上级公司的客户信息（如果cusid的客户的cus_pid != null）
     * @param int $id 需要获取客户联系人、地址信息的客户名
     * @return array $cusData  客户联系人、地址等信息
    */
    protected function getCusData($id)
    {
        $cusCondition['cid'] = array('EQ', $id);
        $cusData = M('customer')->where($cusCondition)
            ->field('cus_pid')
            ->find();
        if ($cusData['cus_pid']) {
            $cusIds = $id . "," . $cusData['cus_pid'];
            $cusFilter['cid'] = array('IN', $cusIds);
            $contactFilter['cusid'] = array('IN', $cusIds);
        } else {
            $cusFilter['cid'] = array('EQ', $id);
            $contactFilter['cusid'] = array('EQ', $id);
        }
        $cusData = M('customer')->where($cusFilter)
        ->field('addr cusaddr, cphonename, cphonenumber, cus_pid')
        ->select();
        // 获取地址信息
        foreach ($cusData as $key => $val) {
            $cusAddr[$key]['addr'] = json_decode($val['cusaddr']);
            $data[$key]['contact_name']  = $val['cphonename'];
            $data[$key]['contact_phone'] = $val['cphonenumber'];
        }
        $addressList = count($cusData) == 1 ? $cusAddr[0]['addr'] : array_merge($cusAddr[0]['addr'], $cusAddr[1]['addr']);
        // 联系人及联系方式
        $contactData = M('cuscontacter')->where($contactFilter)->field('id,name contact_name, phone')->select();
        $count = count($contactData);
        if ($count != 0) {
            for ($i = count($cusData); $i < ($count + count($cusData)); $i++) {
                $data[$i]['contact_name']  = $contactData[$i - count($cusData)]['contact_name'];
                $data[$i]['contact_phone'] = $contactData[$i - count($cusData)]['phone'];
            }
        }
        $datas = array(
            'cusAddr' => $addressList,
            'contact' => $data
        );
        return $datas;
    }

    /* 获取添加订单所需要的信息：结算方式、审核人等*/
    protected function getOrderInfo($returnArray = ['orderType','performanceType','logisticsType','settleType','repType','staffData','financeData','prodType','prodData','invoiceT','invoiceSituation','freightPayMethod'])
    {
        $orderInfo = array();
        $allTypeArray = ['orderType','performanceType','logisticsType','settleType','repType','staffData','financeData','prodType','prodData','invoiceT','invoiceSituation','freightPayMethod'];
        foreach ($returnArray as $key => $item) {
            if (in_array($item,$allTypeArray)) {
                switch ($item) {
                    case 'orderType' :
                        /* orderType */
                        $orderInfo['orderType'] = M('order_type')->field('order_type_name type_name,type_id')->select();
                        break;
                    case 'performanceType' :
                        /* statistic type*/
                        $orderInfo['performanceType'] = M('order_performance_type')->field('performance_type_name,type_id')->order('type_id desc')->select();
                        break;
                    case 'logisticsType' :
                        /* logistics*/
                        $orderInfo['logisticsType'] = M('order_logistics_type')->field('logistics_type_name,type_id')->select();
                        break;
                    case 'repType' :
                        /*发货仓库*/
                        $orderInfo['repType']    = M('repertorylist')->field('rep_id repid, repertory_name repname')->select();
                        break;
                    case 'settleType' :
                        /*结算方式和 */
                        $orderInfo['settleType'] = M('settlementlist')->field('settle_id seid,settle_name sename')->select();
                        break;
                    case 'staffData' :
                        // 业务员电话
                        $map['id'] = array('EQ', $this->staffId);
                        $orderInfo['staffData'] = M('staff')->where($map)->field('name staffname,phone staffphone')->find();
                        break;
                    case 'financeData' :
                        // 财务审核
                        $filter['roleid'] = array('IN', '4,5,6,7');
                        $orderInfo['financeData'] = M('staff')->where($filter)->field('id staffid, name')->select();
                        break;
                    case 'prodType' :
                        // 产品类型
                        $orderInfo['prodType'] = M('product_type')->select();
                        break;
                    case 'prodData' :
                        $orderInfo['prodData'] = M('material')->field('product_id pro_id, product_name pro_name')->select();
                        break;
                    case 'invoiceT' :
                        // 产品表遍历 chosen 供选择
                        $orderInfo['invoiceT'] = M('order_invoice')->field('type_id, invoice_name name')->select();
                        break;
                    case 'invoiceSituation' :
                        $orderInfo['invoiceSituation'] = M('order_invoice_situation')->field('type_id, invoice_situation_name name')->select();
                        break;
                    case 'freightPayMethod' :
                        $orderInfo['freightPayMethod'] = M('order_freight_payment_type')->field('type_id, freight_payment_name name')->select();
                        break;
                    default :
                        break;
                }
            }
        }
        return $orderInfo;
    }
    /**
     * 添加订单时的获取订单最大id方法 防止并发
    */
    protected function getOrderId()
    {
        $model = M();
        M()->startTrans();
        // 执行SQL语句 锁掉order表
        // 表的WRITE锁定，阻塞其他所有mysql查询进程
        $order_id_rst = M()->table('crm_order_id')->lock(true)->field('id, order_id')->find();// 获取唯一order_id

        // 执行更新操作
        $setData['order_id'] = $order_id_rst['order_id'] + 1;
        $lockFilter['id'] = array('EQ', $order_id_rst['id']);
        $model->table('crm_order_id')->lock(false);

        $order_rst = M('order_id')->where($lockFilter)->save($setData);
        // 当前请求的所有写操作做完后，执行解锁sql语句
        $model->table('crm_order_id')->lock(false);
        $model->commit();
        return $order_id_rst;
    }

    /**
     * da@name getProductData
     * 获取提交的订单产品数据
     * @param int $proNum 产品数量
     * @param array $orderData 包含订单ID 数据
     * @param array $postData 提交的post数据 包含提交的产品信息
     * @return array $productData 用于提交的orderproduct的数据

     * @todo 免费样品还需要加
    */
    protected function getProductData($proNum, $orderData, $postData)
    {
        for ($i = 0; $i <= $proNum; $i++) {
            $productData[$i] = array(
                'order_id'            => $orderData['id'],
                'product_type'        => $postData['productName' . $i],
                'product_id'          => $postData['productId' . $i],
                'product_price'       => $postData['productprice' . $i],
                'product_num'         => $postData['productNum' . $i],
                'product_total_price' => $postData['singlePrice' . $i],
            );
            if (empty($productData[$i]['product_total_price'])) {
                unset($productData[$i]);
            } else {
                $productData[$i]['order_id'] = $orderData['id'];
            }
        }
        return $productData;
    }

    /**
     * 获取订单的总金额
     * 获取订单的总金额
     * @param int $cusId 客户id
     * @return array $data 返回客户4个月以来的订单总金额

     */
    protected function getOrderCount($cusId)
    {
        $orderLimit = strtotime(date("Y-m", strtotime("-3 month")));
        //$map_statistics['otime'] = array('gt', $orderLimit);
        $map_statistics['check_status'] = array('in', "1,3,4");
        $map_statistics['cus_id'] = array('eq', $cusId);
        $data = M('orderform')->where($map_statistics)->field('cus_id cid,sum(oprice) `total_order_price`')->group('cid')->find();
        return $data;
    }

    /**
     * 获取权限下的部门订单审核人
    */
    protected function getDeptAuditId()
    {
        $sql = "SELECT `id` FROM `crm_staff` WHERE find_in_set({$this->staffId}, `order_child_id`)";
        $mangeIdArray = M('staff')->query($sql);
        $mangeString = $mangeIdArray ? getPrjIds($mangeIdArray, 'id') : $this->staffId;
        $flag = in_array(65, explode(",", $mangeString));
        if ($flag == true) {
            $mangeString  = $mangeString . "," . $this->staffId;
        }
        return $mangeString;
    }



    /**
     * 15 查看订单（showOrderList showUnqualified showInvoiceDetail）
     * showOrderList：查看订单列表，根据审核阶段划分
     * showUnqualified:onmouseover查看不合格原因
     * showInvoiceDetail:查看订单单据，可以js打印
     * @todo 后续权限架构修改需要调整可查看的内容20170927
     */

    /**
     * 订单列表
    */
    public function showOrderList()
    {
        if (IS_AJAX) {
            $orderModel = new OrderformModel();
            $this->posts = I('post.');
            $orderType = $this->posts['orderType'];
            $orderNum = (int)$this->posts['orderNum'];
            switch ($orderType) {
                case 'order_1' :
                    $k = "1,3";
                    break;
                case 'order_2' :
                    $k = "4";
                    break;
                case 'order_3' :
                    $k = '2';
                    break;
                case 'order_4' :
                    $k = '5';
                    break;
                default :
                    $k = '1,3';
                    break;
            }
            //获取Datatables发送的参数 必要
            $draw = $this->posts['draw'];

            $this->sqlCondition = $this->getSqlCondition($this->posts);

            $indexStr = $this->getSearchIndex($this->sqlCondition['search'], SPL_MATCH_ANY, 'order', true);

            $map['check_status'] = array('IN', (string)$k);
            $staffIds = $this->getStaffIds((string)$this->staffId, 'order_child_id', "");
            $staffIds = ($orderNum == 1) ? (string)$this->staffId : $staffIds;
            $map['picid'] = array('IN', $staffIds);
            $map['crm_orderform.is_del'] = array('eq', 0);
            $count = M('orderform')->where($map)->count();
            if ($indexStr !== null) {
                $map['crm_orderform.id'] = array('in', $indexStr);
            }

            $recordsFiltered = M('orderform')->where($map)->count();

            $orderContents = $orderModel->getOrderIndexData($map, $this->sqlCondition);

            $this->output = $this->getDataTableOut($draw, $count, $recordsFiltered, $orderContents);
            $this->ajaxReturn($this->output);
        } else {
            $this->display();
        }
    }

    /**
     * 订单不合格信息
    */
    public function showUnqualified()
    {
        $orderId = I('get.id');
        $orderFilter['id'] = array('EQ', $orderId);
        $unqualifiedContent = M('orderform')->field('dept_feedback deptFeedback,finance_feedback financeFeedback')->where($orderFilter)->find();
        $this->ajaxReturn($unqualifiedContent);
    }

    /**
     * 订单单据详情
    */
    public function showInvoiceDetail()
    {
        $orderId = (int)I('get.orderId');//订单id
        $map['crm_orderform.id'] = array('EQ', $orderId);
        $orderContents = M('orderform')
            ->where($map)
            ->join('LEFT JOIN `crm_repertorylist` rep ON rep.rep_id = delivery_ware_house')
            ->join('LEFT JOIN `crm_settlementlist` sett ON sett.settle_id = settlement_method')
            ->field('crm_orderform.*,rep.repertory_name ware_house,sett.settle_name')
            ->find();
        $map_2['order_id'] = array('EQ', $orderId);
        $productData = M('orderproduct')
            ->field('crm_orderproduct.*, typ.prod_type_name prodtype')
            ->where($map_2)
            ->join('LEFT JOIN crm_product_type typ ON typ.type_id = crm_orderproduct.product_type')
            ->select();
        $this->assign(array(
            'data' => $orderContents,
            'prod' => $productData
        ));
        $this->display();
    }

    /**
     * 16 修改不合格订单 （checkOrderOwner editUnqualifiedOrder）
     */
    public function checkOrderOwner()
    {
        $orderId = I('post.order_id');
        $filter['id'] = array('EQ', $orderId);
        $filter['picid'] = array('EQ', $this->staffId);
        $rst = M('orderform')->where($filter)->field('id')->find();
        $msg = empty($rst) ? 1 : 2;
        $this->ajaxReturn($msg);
    }

    public function delUnqualifiedOrder()
    {
        if (IS_POST) {
            $orderModel = new OrderformModel();
            $id = (int)I('post.order_id');//要删除的订单
            $map['a.id'] = array('EQ', $id);
            $unqualifiedOrderInfo = M('orderform')->alias('a')->where($map)->field('a.picid,a.check_status,a.id,a.stock_status')->find();
            if (($unqualifiedOrderInfo['check_status'] != self::SAVE_STATUS) && ($unqualifiedOrderInfo['check_status'] != self::UN_STATUS)) {
                $this->returnAjaxMsg('非不合格订单\个人保存订单，禁止删除', 403);
            }
            if ($unqualifiedOrderInfo['picid'] != $this->staffId) {
                $model = new AuthRoleModel();
                $authCondition['role_id'] = array('IN', self::FINANCE_ROLE);
                $data = $model->getRoleList('staff_ids', $authCondition, 'role_id', 0, 1000);
                $financeIdArr = array_filter(explode(",", getPrjIds($data,"staff_ids")));
                if (!in_array($this->staffId, $financeIdArr)) {
                    $this->returnAjaxMsg('非订单负责人不允许删除', 403);
                }
            }
            $productionModel = new ProductionPlanModel();
            $production['order_id'] = ['eq', $unqualifiedOrderInfo['id']];
            $productionData = $productionModel->where($production)->field('id,production_status')->find();
            if ($productionData) {
                $this->returnAjaxMsg('该订单下了生产任务，不能直接删除,请删除生产任务后再删除订单:' . $productionData['prodcution_order'], 403);
            }
            $msg = $orderModel->delOrderTrans($unqualifiedOrderInfo['id']);
            $this->ajaxReturn($msg);

        } else {
            die('FORBIDDEN');
        }

    }


    /**
     * 19 售后记录审核 showUnCheckServiceList checkSaleService
    */
    public function showUnCheckServiceList()
    {
        $map['uid'] = array('EQ', $this->staffId);
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


    public function checkSaleService()
    {
        if (IS_POST) {
            $auId = inject_id_filter(I('post.auid')); // 创建人id
            $flag = inject_id_filter(I('post.k')); // 审核状态码
            $serviceId = inject_id_filter(I('post.conid')); // 需要审核的编号
            $flag = ($flag == 3) ? 2 : $flag;
            $this->changeRecordStatus($auId, $serviceId, $flag, 'sstatus', 'saleservice', 2, 3);
        } else {
            $this->ajaxReturn(4);
        }
    }
    // 权限
    public function checkAuditRole()
    {
        $id  = inject_id_filter(I('post.id'));
        $msg = ($id == $this->staffId) ? 2 : 1;
        $this->ajaxReturn($msg);
    }

    /**
     * 20 客服记录审核 showUnCheckServiceList checkSaleService
     */
    public function showUnCheckOnlineList()
    {
        $map['uid'] = array('EQ', $this->staffId);
        $cusData = M('customer')->where($map)->field('cid')->select();
        $cusIds = getPrjIds($cusData, 'cid');
        $filter['customer_id'] = array('IN', $cusIds);
        $filter['austatus'] = array('EQ', '1');
        $data = M('onlineservice')
            ->where($filter)
            ->join('LEFT JOIN crm_customer AS cus ON cus.cid = customer_id')
            ->join('LEFT JOIN crm_staff AS sta ON sta.id = server_id')
            ->field("crm_onlineservice.*, cus.cname, cus.keyword,cus.uid AS rpbid,sta.name AS pname")
            ->order('addtime DESC')
            ->select();
        $this->assign('data', $data);
        $this->display();
    }

    /**
     * 审核客服联系记录
     * 成功返回2 失败1 无权限 4
    */
    public function checkOnlineService()
    {
        if (IS_POST) {
            $auId = inject_id_filter(I('post.auid')); // 创建人id
            $flag = inject_id_filter(I('post.k')); // 审核状态码
            $serviceId = inject_id_filter(I('post.conid')); // 需要审核的编号
            $flag = ($flag == 3) ? 2 : $flag;
            $this->changeRecordStatus($auId, $serviceId, $flag, 'austatus', 'onlineservice', '2', '3');
        } else {
            $this->ajaxReturn(4);
        }
    }

    /**
     * 25 客户转移 assignCustomer showAssignData assignAll assignSel
     */

    /**
     * 客户转移页面，有下属的人可以转移
    */
    public function assignCustomer()
    {
        $staffIds = $this->cusStaffIds . "," . $this->staffId;

        $map_2['id'] = array('IN', $staffIds);
        $ownIds = M('staff')->where($map_2)->field('id, name')->order('post_id')->select();
        $this->assign('totalIds', $ownIds);
        $this->display();
    }

    /**
     * 客户转移数据加载。
     */
    public function showAssignData()
    {
        $this->posts = I('post.');
        $k = $this->posts['k'];
        //获取Datatables发送的参数 必要
        $draw = $this->posts['draw'];

        $this->sqlCondition = $this->getSqlCondition($this->posts);


        $where['uid'] = array('EQ', $this->posts['k']);
        $count = M('customer')->where($where)->count();

        if ($this->sqlCondition['search'] != "") {
            $c = new \SphinxClient();
            $c->setServer('localhost', 9312);
            $c->setMatchMode(SPL_MATCH_ANY);
            $data1 = $c->Query($this->sqlCondition['search'], "dwin,delta");
            $index = array_keys($data1['matches']);
            $index_str = implode(',', $index);
            if ($index_str == null) {
                $this->ajaxReturn(false);die;
            } else {
                $where['cid'] = array('IN', $index_str);
            }
            $recordsFiltered = M('customer')->where($where)->count();
        } else {
            $recordsFiltered = $count;
        }

        $this->field = "cid,cname,s.name fname, from_unixtime(addtime) builder_time, auditstatus au_status";
        $data1 = M('customer')
            ->join('LEFT JOIN crm_staff s ON s.id = crm_customer.founderid')
            ->where($where)
            ->field($this->field)
            ->order($this->sqlCondition['order'])
            ->limit($this->sqlCondition['start'], $this->sqlCondition['length'])
            ->select();

        foreach($data1 as $key => &$val) {
            $val['DT_RowId']    = $val['cid'];
            $val['DT_RowClass'] = 'gradeX';
            switch($val['au_status']){
                case 1 :
                    $val['au_status'] = '未审核';break;
                case 3 :
                    $val['au_status'] = '已审核';break;
            }
        }
        $output = array(
            "draw"            => intval($draw),
            "recordsTotal"    => $count,
            "recordsFiltered" => $recordsFiltered,
            "data"            => $data1
        );
        $this->ajaxReturn($output);
    }
    /**
     * 转移客户操作
    */
    public function assignAll()
    {
        $map['role_id']    = array('IN', self::CUS_NAME_CHANGE_ROLE);
        $authIds = getPrjIds(M('auth_role')->where($map)->field('staff_ids')->select(),'staff_ids');

        if (!in_array($this->staffId, explode(',',$authIds))) {
            $this->ajaxReturn([
                'status'    => 403,
                'msg' => '仅部门经理以上具备客户分配权限'
            ]);
        }
        $postData = I('post.');
        $customerModel = new CustomerModel();

        // 第一次查询判断是否存在上级或下级公司
        $flag = !empty($postData['flag']) ? $postData['flag'] : 0;
        $fromId = (int)$postData['fId'];
        $toId   = (int)$postData['tId'];
        $cusIdArray = !empty($postData['sel']) ? $postData['sel'] : [];
        if(empty($fromId) || empty($toId)){
            $this->returnAjaxMsg("参数不全",400);
        }

        $upData = !empty($postData['upData']) ? $postData['upData'] : [];
        $loadData = !empty($postData['loadData']) ? $postData['loadData'] : [];
        if(count($cusIdArray) == 0){
            $uidMap['uid'] = ['eq', $fromId];
            $customerData = $customerModel->field('cid')->where($uidMap)->select();
            $cusIds = implode(',', array_column($customerData,'cid'));
        }else {
            $cusIds = implode(',', $cusIdArray);
            if($flag == 0){
                // 标志是第一次传参，首先得判断是否存在上下级公司
                // 查询当前所传客户数据是否存在上级公司
                $customerField = "u.cid,u.cname,u.uid,cs.name";
                $customerMap = [];
                $customerMap = "c.cid in ($cusIds) and u.cid is not null";
                $upData = $customerModel->getUpCustomerMsg($customerMap, $customerField);

                // 查询当前所传客户数据是否存在下级公司
                $loadData = $customerModel->getLoadCustomerMsg($customerMap, $customerField);

                if(!empty($upData) || !empty($loadData)){
                    $this->returnAjaxMsg("当前数据存在上下级公司",201,[
                        'upData' => $upData,  // 上级公司
                        'loadData' => $loadData, // 下级公司
                    ]);
                }
            }

        }

        $customerIdArr = array_merge($upData, $loadData);
        if(!empty($customerIdArr)){
            $cusIds = implode(',', array_unique(array_merge($cusIdArray,array_column($customerIdArr,'cid'))));
        }

        $map = [];
        $map['cid'] = array('IN', $cusIds);

        $name1Filter1['id'] = array('EQ', $fromId);
        $name1Filter2['id'] = array('EQ', $toId);
        $field = "id, name";
        $name1 = D('staff')->getOneStaffInfo($name1Filter1, $field);
        $name2 = D('staff')->getOneStaffInfo($name1Filter2, $field);

        // 除去负责人id等于被转移人的id的条件限制
//        $map['uid']  = array('EQ', $fromId);
        $changeData = array(
            'changeId' => $this->staffId,
            'oldUid'   => $fromId,
            'uid'      => $toId,
            'fromName' => $name1['name'],
            'toName'   => $name2['name']
        );
        M()->startTrans();
        $rst = $customerModel->changeCusUid($map, $changeData);

        if ($rst['updateResult']) {
            for($i = 0; $i < count($rst['changeRecordData']);$i++) {
                unset($rst['changeRecordData'][$i]['last_uids']);
                unset($rst['changeRecordData'][$i]['uid']);
            }
            $res = D('cuschangerecord')->cusChangeRecord($rst['changeRecordData']);
            if ($res !== false) {
//                if (!empty($rst['changeArr']['unChangeCusName2'])){
//                    M()->rollback();
//                    $msg = array(
//                        'status' => 200,
//                        'msg' => "如下客户"  . $changeData['toName'] . "曾经负责过，不能再次转移：<br>" . $rst['changeArr']['unChangeCusName2']
//                    );
//                }else {
                    M()->commit();
                    $msg = array(
                        'status' => 200,
                        'msg' => '转移成功'
//                            . ($rst['changeArr']['unChangeCusName2'] ? "如下客户"  . $changeData['toName'] . "曾经负责过，不能再次转移：<br>" . $rst['changeArr']['unChangeCusName2'] : ".")
                    );
//                }
            } else {
                M()->rollback();
                $msg = array(
                    'status' => 401,
                    'msg'    => '转移失败，请联系管理员，status = 401'
                );
            }

        } else {
            $msg = array(
                'status' => 400,
                'msg'    => '转移失败，' . $changeData['toName'] . "之前负责过这些部分或全部客户"
            );
        }

        $this->ajaxReturn($msg);
    }


    /**
     * 26 公共客户导入 addCommonCustomer importCus
    */
    public function addCommonCustomer()
    {
        // 新客户申报 具备条件可以申请
        if (IS_POST) {
            $post = I('post.');

            $post['addtime'] = time();
            $arr = array();
            for ($i = 3; $i > 0; $i--) {
                if ($post['street' . $i] != "") {

                    $$i = ($post['street' . $i]);
                    array_push($arr, $$i);
                    $post['province'] = $post['city'];
                    $post['province'] = ($post['province']);
                }
            }
            $post['addr'] = json_encode($arr);
            $post['founderid'] = $this->staffId;
            $post['auditorid'] = $this->staffId;
            $post['cname']   = inject_filter($post['cname']);
            $post['ctype']   = inject_filter(str_replace("-", "", $post['cusType']));
            $post['csource'] = inject_filter($post['csource']);
            $post['website'] = ($post['website']);
            $post['cphonename'] = inject_filter(str_replace("-", "", $post['cusfcontact']));
            $post['tip'] = inject_filter($post['detail']);
            $post['auditstatus'] = 3;
            $post['cstatus'] = 1;
            $post['type'] = 2;
            $model = M('customer');
            $data = $model->create($post);
            $rst = $model->add($data);
            $msg = $rst ? 1 : 2;
            $this->ajaxReturn($msg);
        } else {
            // 获取审核人并返回给模板渲染
            $industry = M('industry')->select();
            $indus = getTree($industry, 0, 0, 'pid');
            $this->assign(array(
                'indus' => $indus
            ));
            $this->display();
        }
    }
    /**
     * 导入excel文件
     * @param  string $file excel文件路径
     * @return array        excel文件内容数组
     */
    
    /**
     * getCusType 根据输入字符，获取客户分类id
     * @param string $data 字符串，导入时输入的产品类型
     * @return int $type
    */
    protected function getCusType($data)
    {
        switch ($data) {
            case "美容、理疗和保健设备" :
                $type = 70;
                break;
            case "医疗仪器" :
                $type = 71;
                break;
            case "空气处理" :
                $type = 72;
                break;
            case "金融机具" :
                $type = 75;
                break;
            case "文教和培训" :
                $type = 76;
                break;
            case "交通运输" :
                $type = 77;
                break;
            case "仪器仪表和自动化" :
                $type = 78;
                break;
            case "电力设备" :
                $type = 79;
                break;
            case "能源和矿产" :
                $type = 80;
                break;
            case "科研机构" :
                $type = 81;
                break;
            case "单片机、嵌入式系统开发与应用" :
                $type = 82;
                break;
            case "互联网公司" :
                $type = 83;
                break;
            case "计算机软件" :
                $type = 84;
                break;
            case "水处理" :
                $type = 86;
                break;
            case "IC设计与制造" :
                $type = 87;
                break;
            case "厨房电器" :
                $type = 88;
                break;
            case "空调" :
                $type = 89;
                break;
            case "新能源" :
                $type = 90;
                break;
            case "家用电器" :
                $type = 91;
                break;
        }
        $type = isset($type) ? $type : 81;
        return $type;
    }
    
    /**
     * 获取类似名称的客户
     * 检查是否有重名
    */
    protected function getSimilarInfo($index)
    {
        $map['crm_customer.cid'] = array('IN', $index);
        /** @var string = $sql 查询语句获得录入客户类似名称的信息：负责人、上级公司等*/
        $rst = M('customer')->where($map)
            ->join(' LEFT JOIN `crm_staff` c ON crm_customer.uid = c.id AND crm_customer.uid IS NOT NULL')
            ->join(' LEFT JOIN `crm_customer` b ON b.cid = crm_customer.cus_pid AND crm_customer.cus_pid IS NOT NULL')
            ->field('`crm_customer`.`cname` c_name, c.`name` u_name,b.`cname` parent_name')
            ->select();
        if ($rst) {
            for ($j = 0; $j < count($rst); $j++) {
                $rst[$j]['out'] =
                    empty($rst[$j]['parent_name'])
                        ?
                        (empty($rst[$j]['u_name'])
                            ?
                            '<br> ' . $rst[$j]['c_name']
                            :
                            '<br> ' . $rst[$j]['c_name'] . "(负责人：" . $rst[$j]['u_name'] . ")")
                        :
                        (empty($rst[$j]['u_name'])
                            ?
                            '<br> ' . $rst[$j]['c_name'] . "(有上级公司：" . $rst[$j]['parent_name'] . ")"
                            :
                            '<br> ' . $rst[$j]['c_name'] . "(有上级公司：" . $rst[$j]['parent_name'] . ",负责人：" . $rst[$j]['u_name'] . ")");
            }
        }
        return $similarName = getPrjIds($rst, 'out');
    }

    /**
     * 客户审核使用检查客户的查重结果
    */
    protected function getSimilar($data2)
    {
        $c = new \SphinxClient();
        $c->setServer('localhost', 9312);
        $c->setMatchMode(SPH_MATCH_All);
        for($i = 0; $i < count($data2); $i++) {
            if (strlen($data2[$i]['cname']) <= 3) {
                $data2[$i]['similar'] = "个人客户，未检查重名";
            } else {
                $cusKey[$i]    = str_replace(explode(",", $this->keyFilter), "", $data2[$i]['cname']);//去除地名信息后的关键字
                $searchRes[$i] = $c->Query($cusKey[$i], "dwin,delta");
                $index[$i]     = str_replace($data2[$i]['cid'], "", array_keys($searchRes[$i]['matches']));
                $cusData[$i]['cus_index'] = (count($index[$i]) == 0) ? "" : implode(',', $index[$i]);
                if (!empty($cusData[$i]['cus_index'])) {
                    $data2[$i]['similar'] = $this->getSimilarInfo($cusData[$i]['cus_index']);
                } else {
                    $data2[$i]['similar'] = "无重名";
                }
            }
        }
        $c->close();
        return $data2;
    }

    /**
     * 上传文件（xls)并且使用phpexcel读取文件后显示
    */
    public function importCus()
   {
       if (!empty($_FILES)) {
           $rootPath = WORKING_PATH . UPLOAD_ROOT_PATH . "/excelUpload/";
           if (!file_exists($rootPath)) {
               mkdir($rootPath);
           }
           $cfg = array(
               'allowExts' => array('xlsx', 'xls'),
               'saveRule'  => 'time',
               'rootPath'  => $rootPath, // 保存根路径
           );
           # 实例化上传类
           $upload = new Upload($cfg);
           # 上传
           $info = $upload->upload();
           if (!$info) {
               $msg = array(
                   'status' => 3
               );
               $this->ajaxReturn($msg);
               die;
           } else {
               $filePath = $rootPath . $info['excelFile']['savepath'] . $info['excelFile']['savename'];
               $data = import_excel($filePath);
               $totalNum   = count($data);
               $importTime = time();

               $cusFilterString = "河北,石家庄,张家口,承德,唐山,秦皇岛,廊坊,保定,沧州,衡水,邢台,邯郸,山西,太原,大同,朔州,忻州,阳泉,晋中,吕梁,长治,临汾,晋城,运城,内蒙古自治区,呼和浩特,呼伦贝尔,通辽,赤峰,巴彦淖尔,乌兰察布,包头,鄂尔多斯,乌海,黑龙江,哈尔滨,黑河,伊春,齐齐哈尔,鹤岗,佳木斯,双鸭山,绥化,大庆,七台河,鸡西,牡丹江,吉林,长春,白城,松原,吉林,四平,辽源,白山,通化,辽宁,沈阳,铁岭,阜新,抚顺,朝阳,本溪,辽阳,鞍山,盘锦,锦州,葫芦岛,营口,丹东,大连,江苏,南京,连云港,徐州,宿迁,淮安,盐城,泰州,扬州,镇江,南通,常州,无锡,苏州,浙江,杭州,湖州,嘉兴,绍兴,舟山,宁波,金华,衢州,台州,丽水,温州,安徽,合肥,淮北,亳州,宿州,蚌埠,阜阳,淮南,滁州,六安,马鞍山,巢湖,芜湖,宣城,铜陵,池州,安庆,黄山,福建,福州,宁德,南平,三明,莆田,龙岩,泉州,漳州,厦门,江西,南昌,九江,景德镇,上饶,鹰潭,抚州,新余,宜春,萍乡,吉安,赣州,山东,济南,德州,滨州,东营,烟台,威海,淄博,潍坊,聊城,泰安,莱芜,青岛,日照,济宁,菏泽,临沂,枣庄, 河南,郑州,安阳,鹤壁,濮阳,新乡,焦作,三门峡,开封,洛阳,商丘,许昌,平顶山,周口,漯河,南阳,驻马店,信阳,湖北,武汉,十堰,襄樊,随州,荆门,孝感,宜昌,黄冈,鄂州,荆州,黄石,咸宁,湖南,长沙,岳阳,张家界,常德,益阳,湘潭,株洲,娄底,怀化,邵阳,衡阳,永州,郴州,广东,广州,韶关,梅州,河源,清远,潮州,揭阳,汕头,肇庆,惠州,佛山,东莞,云浮,汕尾,江门,中山,深圳,珠海,阳江,茂名,湛江广西壮族自治区,南宁,桂林,河池,贺州,柳州,百色,来宾,梧州,贵港,玉林,崇左,钦州,防城港,北海,海南,海口,三亚,三沙,儋州,四川,成都,广元,巴中,绵阳,德阳,达州,南充,遂宁,广安,资阳,眉山,雅安,内江,乐山,自贡,泸州,宜宾,攀枝花,贵州,贵阳,遵义,六盘水,安顺,云南,昆明,昭通,丽江,曲靖,保山,玉溪,临沧,普洱,西藏自治区,拉萨,昌都,日喀则,林芝,陕西,西安,榆林,延安,铜川,渭南,宝鸡,咸阳,商洛,汉中,安康,甘肃,兰州,嘉峪关,酒泉,张掖,金昌,武威,白银,庆阳,平凉,定西,天水,陇南,青海,西宁,海东,宁夏回族自治区,银川,石嘴山,吴忠,中卫,固原,新疆维吾尔自治区,乌鲁木齐,克拉玛依,吐鲁番";
               $cusFilter = array(
               '分公司', '子公司', '代理商', '科技发展有限公司', '科技有限公司', '技术有限公司', '实业有限公司', '有限责任公司', '电子有限公司', '股份有限公司', '有限公司','公司','研究所','研究院','市','省',
                   '北京', '上海', '天津', '重庆',
                   '(',')','（','）','select','insert','update','delete','and','or','where','join','*','=','union','into','load_file','outfile','/','\''
               );
               require_once('sphinxapi.php');
               $c = new \SphinxClient();
               $c->setServer('localhost', 9312);
               $c->setMatchMode(SPH_MATCH_ALL);
               
               for ($i = 2; $i <= $totalNum; $i++) {
                   $cusData[$i] = array(
                       'cname'        => addslashes($data[$i]['0']),
                       'cphonename'   => empty(addslashes($data[$i]['1'])) ? "无" : addslashes($data[$i]['1']),
                       'cphonenumber' => empty(addslashes($data[$i]['2'])) ? "无" : addslashes($data[$i]['2']),
                       'tip'          => addslashes(empty($data[$i]['3']) ? "无" : $data[$i]['3']),
                       'province'     => empty(addslashes($data[$i]['4'])) ? "无" : addslashes($data[$i]['4']),
                       'addr'         => empty($data[$i]['5']) ? "无" : $data[$i]['5'],
                       'ctype'        => (int)$this->getCusType($data[$i]['6']),
                       'website'      => empty($data[$i]['7']) ? "无" : $data[$i]['7'],
                       'csource'      => empty($data[$i]['8']) ? "展会" : $data[$i]['8'],
                       'csource_detail' => empty($data[$i]['9']) ? "" : $data[$i]['9'],
                       'addtime'      => (int)$importTime,
                       'auditstatus'  => "3",
                       'founderid'    => (int)$this->staffId,
                       'auditorid'    => (int)$this->staffId,
                       'cstatus'      => "1",
                       'type'         => "2",
                       'name_size'    => (int)mb_strlen($data[$i]['0'], 'utf8'),
                       'cus_key'      => str_replace(array_merge(explode(",", $cusFilterString), $cusFilter), "", strtolower($data[$i]['0'])),
                       'import_info'  => 'IMPORT' . $importTime,
                   );
                   $filter['cname'] = array('EQ', $cusData[$i]['cname']);
                   $rst[$i] = M('customer')->field('cid')->where($filter)->find();
                   if ($rst[$i]) {
                       $cusData[$i]['cus_index']  = empty($rst[$i]['cid']) ? "" : $rst[$i]['cid'];
                       $cusData[$i]['check_info'] = 5; // 5 客户名已存在
                       $cusData[$i]['similar_name'] = empty($cusData[$i]['cus_index']) ? "" : $this->getSimilarInfo($rst[$i]['cid']);
                   } else {
                       $data1[$i] = $c->Query($cusData[$i]['cus_key'], "dwin,delta");
                       $index = array_keys($data1[$i]['matches']);
                       $cusData[$i]['cus_index'] = (count($index) == 0) ? "" : implode(',', $index);

                       // 读取的数据检查用户名
                       $cusName[$i] = $cusData[$i]['cname'];

                       if ($cusData[$i]['name_size'] < 4) {
                           if (empty($cusData[$i]['cus_index'])) {
                               $cusData[$i]['check_info'] = 1; // 1 客户名为个人
                               $cusData[$i]['similar_name'] = "";
                           } else {
                               $cusData[$i]['check_info'] = 4; // 4 客户名有疑似重名
                               $cusData[$i]['similar_name'] = $this->getSimilarInfo($cusData[$i]['cus_index']);
                           }
                       } else {
                           if (mb_strlen($cusData[$i]['cus_key'], 'utf8') <= 1) {
                               $cusData[$i]['check_info'] = 2; // 2 客户名可能不合法
                               $cusData[$i]['similar_name'] = "";
                           } else {
                               if (empty($cusData[$i]['cus_index'])) {
                                   $cusData[$i]['check_info'] = 3; // 3 客户名未在数据空中检索到
                                   $cusData[$i]['similar_name'] = "";
                               } else {
                                   $cusData[$i]['check_info'] = 4; // 4 客户名有疑似重名
                                   $cusData[$i]['similar_name'] = $this->getSimilarInfo($cusData[$i]['cus_index']);
                               }
                           }
                       }
                   }
               }
               $c->close();
               $cusData = array_values($cusData);
               $result = M('import_cus')->addAll($cusData);
               $msg['status'] = $result ? 2 : 1;
               $msg['info'] = 'IMPORT' . $importTime;
               $this->ajaxReturn($msg);
           }
       } else {
           $this->display();
       }
   }


    /**
     * 显示对应导入的数据列表
    */
    public function showTempCusList()
    {
        if (IS_POST) {
            $this->posts = I('post.');
            // $this->posts 包括列信息，importId,check_info
            //获取Datatables发送的参数 必要
            $draw = $this->posts['draw'];

            // 排序
            $order_dir = $this->posts['order']['0']['dir'];//ase desc 升序或者降序
            $order_column = (int)$this->posts['order']['0']['column'];
            switch ($order_column) {
                case 0 :
                    $order = "check_info " . $order_dir;
                    break;
                case 1 :
                    $order = "cus_key " . $order_dir;
                    break;
                case 2 :
                    $order = "similar_name " . $order_dir;
                    break;
                case 3 :
                    $order = "cname " . $order_dir;
                    break;
                case 4 :
                    $order = "contact " . $order_dir;
                    break;
                case 5 :
                    $order = "contact_phone " . $order_dir;
                    break;
                default :
                    $order = "check_info desc";
                    break;
            }

            $map['import_info'] = array('EQ', $this->posts['importId']);
            if ($this->posts['check_info']) {
                $map['check_info'] = array('EQ', $this->posts['check_info']);
            }
            $count = M('import_cus')->where($map)->count();
            $data = M('import_cus')
                ->field('cid,check_info cus_type,cus_key keyword,similar_name,cname cus_name,cphonename contact,cphonenumber contact_phone,ind.id indus_id,ind.name indus_name,province,addr')
                ->where($map)
                ->join('LEFT JOIN crm_industry ind ON ind.id = ctype')
                ->order($order)
                ->select();
            $indusArray = M('industry')->field('id,name')->select();
            if ($count != 0) {
                foreach($data as $key => $val) {
                    $info[$key]['DT_RowId']      = $val['cid'];
                    $info[$key]['DT_RowClass']   = 'gradeX';
                    $info[$key]['cus_type']      = $val['cus_type'];
                    $info[$key]['keyword']       = $val['keyword'];
                    $info[$key]['similar_name']  = $val['similar_name'];
                    $info[$key]['cus_name']      = $val['cus_name'];
                    $info[$key]['contact']       = $val['contact'];
                    $info[$key]['contact_phone']       = $val['contact_phone'];
                    $info[$key]['indus']['indus_name'] = $val['indus_name'];
                    $info[$key]['indus']['indus_id']  = $val['indus_id'];
                    $info[$key]['indus']['sel_indus'] = $indusArray;
                    $info[$key]['city'] = $val['province'];
                    $info[$key]['address'] = $val['addr'];
                }
                $info = empty($info) ? "" : $info;
            } else {
                $info = "";
            }

            $output = array(
                "draw"            => intval($draw),
                "data"            => $info,
                'count'           => $count
            );
            $this->ajaxReturn($output);

        } else {
            $this->display();
        }
    }

    /**
     * 删除临时客户方法
    */
    public function delTempCus()
    {
        $this->posts = I('post.');
        switch ($this->posts['Flag']) {
            case 1 :
                $filter['cid'] = array('IN', $this->posts['idString']);
                break;
            case 2 :
                $filter['check_info'] = array('NEQ','3');
                break;
            default :
                $filter['check_info'] = array('NEQ','3');
                break;
        }
        $filter['import_id'] = array('EQ', $this->posts['Id']);
        $rst = M('import_cus')->where($filter)->delete();
        $msg = $rst ? 2 : 1;
        $this->ajaxReturn($msg);

    }

    /**
     * 提交临时表中的客户到客户表中。
     * 提交完成删除对应临时表的数据。
    */
    public function submitTempCus()
    {
        $this->posts = I('post.');
        switch ($this->posts['Flag']) {
            case 1 :
                $map['cid'] = array('IN', $this->posts['idString']);
                break;
            case 2 :
                $map['check_info'] = array('EQ', 3);
                break;
            default :
                $map['check_info'] = array('EQ', 3);
                break;
        }
        $map['import_info'] =array('EQ', $this->posts['Id']);
        $tempCus = M('import_cus')
            ->field('cname,cphonename,cphonenumber,tip,province,addr,ctype,website,csource,addtime,auditstatus,founderid,auditorid,cstatus,type')
            ->where($map)
            ->select();
        foreach ($tempCus as &$val) {
            $val['addr'] = json_encode($val['addr']);
        }
        M()->startTrans();
        $rst = M()->table('crm_customer')->addAll($tempCus);
        if ($rst) {
            $rst_2 = M()->table('crm_import_cus')->where($map)->delete();
            if ($rst_2) {
                M()->commit();
                $msg = 2;
            } else {
                M()->rollback();
                $msg = 3;
            }
        } else {
            M()->rollback();
            $msg = 4;
        }
        $this->ajaxReturn($msg);
    }

    /**
     * 38 客户审核节点 showCustomer showCustomerAudit showCustomerAuditList checkCustomer
     *
    */

    /**
     * 提交临时表中的客户到客户表中。
     * 提交完成删除对应临时表的数据。
     */
    public function showCustomerAudit()
    {
        // 根据组织架构查看内容。
        $cusModel = new CustomerModel();
        $data2 = $this->getSimilar($cusModel->getAuditCustomer());
        $this->assign(array(
            'data2' => $data2
        ));
        $this->display();
    }

    /**
     * 客户待审核列表
    */
    public function showCustomerAuditList()
    {
        // 根据组织架构查看内容。

        $cusModel = new CustomerModel();
        $data2 = $this->getSimilar($cusModel->getAuditCustomer());
        $this->assign(array(
            'data2' => $data2
        ));
        $this->display();
    }

    /**
     * 客户待审核列表中查找客户，用于检索客户
     */
    public function showCustomer()
    {
        if (IS_POST) {
            $this->posts = I('post.');
//            $c = new \SphinxClient();
//            $c->setServer('localhost', 9312);
//            $c->setMatchMode(SPL_MATCH_ANY);
//            $num = mb_strlen($this->posts['cusName'], 'utf8');
//            if ($num <= 1) {
//                $this->ajaxReturn(false);die;
//            }
//            $cusKey = str_replace(array(
//                '有限公司','科技有','技有限','有限公', '限公司', '科技','技有','有限','限公','公司',
//                'select', 'insert', 'update', 'delete', 'and', 'or', 'where', 'join', '*', '=', 'union', 'into', 'load_file', 'outfile','/','\''),"",$this->posts['cusName']);
//            if (mb_strlen($cusKey) <= 1) {
//                $this->ajaxReturn(false);die;
//            }
//            $cusKey = $this->posts['cusName'];
//            $data1 = $c->Query($cusKey, "dwin,delta");
//            $c->close();
//            $index = array_keys($data1['matches']);
//            $index_str = implode(',', $index);
//            if ($index_str == null) {
//                $this->ajaxReturn(false);die;
//            }
            $map['cname'] = ['like', "%" . $this->posts['cusName'] . "%"];
//            $map['cid'] = array('IN', $index_str);
            $data = M('customer')->where($map)
                ->join('LEFT JOIN crm_industry ind ON ind.id = crm_customer.ctype')
                ->field('crm_customer.cid,crm_customer.cname,crm_customer.addtime,uid,ind.name indusname,
                  (SELECT count(sid) FROM crm_salerecord AS ss WHERE ss.cusid = crm_customer.cid) as counts')
                ->limit(0,10)
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

            if ($data === false || $data == []) {
                $this->ajaxReturn(false);
            } else {
                $this->ajaxReturn($data);
            }
        } else {
            $this->ajaxReturn(false);
        }

    }
    /**
     * 审核处理 客户审核
     * 同意、反对将记录在客户变更表中
     * 返回前端结构状态：
     * $msg = 2 成功  3 失败，程序有问题 4 没有权限。
     * @todo 审核记录
    */
    public function checkCustomer()
    {
        if (IS_POST) {
            $auId = inject_id_filter(I('post.auid'));
            $flag = (I('post.k')); // 一个内容
            $cusId = (I('post.conid'));//字符串
            $cusIdArr = explode(",", $cusId);

            $map1['cid'] = array('IN', $cusId);
            $cusMsg = M('customer')->where($map1)->field('type,uid,cid,cname,b.name uname')
                ->join('LEFT JOIN crm_staff b ON b.id = crm_customer.uid')->select();

            $num = count($cusMsg);
            $map['id'] = array('EQ', $this->staffId);

            if ($flag == 3) {
                $flag = 2;
            }
            M()->startTrans();
            if ($auId == $this->staffId) {
                if ($flag == 1) {
                    for ($i = 0; $i < $num; $i++) {
                        $type[$i] = $cusMsg[$i]['type'];
                        if ($type[$i] == 2) {
                            $data[$i] = array(
                                'cstatus' => 1,
                                'uid'     => null,
                                'auditstatus' => 3,
                                'max_order_time'   => time(),
                                'max_contact_time' => time()
                            );
                        } elseif ($type[$i] == 1) {
                            $data[$i] = array(
                                'auditstatus' => 4
                            );
                        } else {
                            $data[$i] = array(
                                'auditstatus' => 4
                            );
                        }
                        $map['cid'] = array('EQ', $cusIdArr[$i]);
                        $rst[$i] = M()->table('crm_customer')->where($map)->setField($data[$i]);
                        if ($rst[$i] !== false) {
                            $addData[$i] = array(
                                'cusid'         => $cusMsg[$i]['cid'],
                                'changetime'    => time(),
                                'change_reason' => "客户审核驳回操作，驳回了" . $cusMsg[$i]['uname'] . " 的客户申请(" . $cusMsg[$i]['cname'] . ")，处理人" . session('nickname'),
                                'change_id'     => $this->staffId,
                                'oldname'       => $cusMsg[$i]['cname'],
                            );
                            $res[$i] = M()->table('crm_cuschangerecord')->add($addData[$i]);
                            if ($res[$i] === false) {
                                M()->rollback();
                                $this->ajaxReturn(3);
                            }
                        } else {
                            M()->rollback();
                            $this->ajaxReturn(3);
                        }
                    }
                } else {
                    for ($i = 0; $i < $num; $i++) {
                        $data[$i] = array(
                            'auditstatus' => 3,
                            'type' => 2,
                            'max_order_time'   => time(),
                            'max_contact_time' => time(),
                            'last_uids'   => array('exp', "CONCAT_WS(',',`last_uids`,{$cusMsg[$i]['uid']})"),
                        );
                        $map[$i]['cid'] = array('EQ', $cusMsg[$i]['cid']);
                        $rst[$i] = M()->table('crm_customer')->where($map[$i])->setField($data[$i]);
                        if ($rst !== false) {
                            $addData[$i] = array(
                                'cusid'         => $cusMsg[$i]['cid'],
                                'changetime'    => time(),
                                'change_reason' => "客户审核同意操作，通过了" . $cusMsg[$i]['uname'] . " 的客户申请(" . $cusMsg[$i]['cname'] . ")，处理人" . session('nickname'),
                                'change_id'     => $this->staffId,
                                'oldname'       => $cusMsg[$i]['cname'],
                            );
                            $res[$i] = M()->table('crm_cuschangerecord')->add($addData[$i]);

                            if ($res[$i] === false) {
                                M()->rollback();
                                $this->ajaxReturn(3);
                            }
                        } else {
                            M()->rollback();
                            $this->ajaxReturn(3);
                        }

                    }
                }
                M()->commit();
                $this->ajaxReturn(2);
            } else {
                $this->ajaxReturn(4);//无权限
            }
        }
    }

    // 满意度调查结果查看
    /*
     * 总经理查看所有
     * 部门总监可查看本部门结果
     * 目前只查看本批次的调查结果
     * 返回datatable需要的json数组
     * */
    public function showCallbackResult()
    {
        if (IS_POST) {
            $this->posts = I('post.');
            // $this->posts 包括列信息，importId,check_info
            //获取Datatables发送的参数 必要
            $draw = $this->posts['draw'];
            $this->sqlCondition = $this->getSqlCondition($this->posts);


            $staffIds = empty($this->cusStaffIds) ? (string)$this->staffId : $this->cusStaffIds . "," . (string)$this->staffId;
            $map['callback_status'] = array('EQ', "3");
            $map['uid'] = array('IN', $staffIds);
            $time = M('cus_callback')->field('assign_time')->order('assign_time desc')->find();
            $map['assign_time'] = array('EQ', $time['assign_time']);// @todo 后续传参 不同批次记录查看

            $count = M('cus_callback')->where($map)->count('id');
            if ($this->sqlCondition['search'] != "") {
               if (strlen($this->sqlCondition['search']) <= 3) {
                   $this->ajaxReturn(false);die;
               } else {
                   $keyArray = explode(" ", $this->sqlCondition['search']);
                   for($i = 0; $i < count($keyArray); $i++) {
                       $key[] = array('like', "%" . $keyArray[$i] . "%");
                   }
                   if (count($keyArray) > 1) {
                       $key[] = "or";
                   }
                   $filter['cus_name'] = $key;
                   $filter['u_name']   = $key;
                   $filter['_logic'] = "or";
                   $map['_complex'] = $filter;
                   $recordsFiltered = M('cus_callback')->where($map)->count();
               }
            } else {
                $recordsFiltered = $count;
            }
            $data = M('cus_callback')->where($map)
                ->field('id,cus_id,cus_name,satisfied_flag,contact_name, contact_number,uid,u_name,
                question_1, question_1flag, question_1tip, question_2, question_2flag, question_2tip,
                question_3, question_3flag')
                ->order($this->sqlCondition['order'])
                ->limit($this->sqlCondition['start'], $this->sqlCondition['length'])
                ->select();
            if ($count != 0) {
                foreach ($data as $key => &$val) {
                    $val['DT_RowId']       = $val['id'];
                    $val['DT_RowClass']    = 'gradeX';
                    $val['satisfied_flag'] = $val['satisfied_flag'] == 2 ? "满意" : "不满意";
                }
            }
            $output = array(
                "draw"            => intval($draw),
                "recordsTotal"    => $count,
                "recordsFiltered" => $recordsFiltered,
                "data"            => $data
            );
            $this->ajaxReturn($output);
        } else {
            $staffIds = empty($this->cusStaffIds) ? (string)$this->staffId : $this->cusStaffIds . "," . (string)$this->staffId;
            $map['callback_status'] = array('EQ', "3");
            $map['uid'] = array('IN', $staffIds);
            $time = M('cus_callback')->field('assign_time')->order('assign_time desc')->find();
            $map['assign_time'] = array('EQ', $time['assign_time']);// @todo 后续传参 不同批次记录查看
            $total = M('cus_callback')->where($map)->count('id');
            $map['satisfied_flag'] = array('EQ', "2");
            $unNum = M('cus_callback')->where($map)->count('id');
            $this->assign(array(
                'all' => $total,
                'un' => $total - $unNum
            ));
            $this->display();
        }
    }

    public function countCallback()
    {
        if (IS_POST) {
            $this->posts = I('post.data');
            if ($this->posts['flag'] == '1') {
                $map['assign_time'] = ['eq', strtotime($this->posts['assign_times'])];
            } elseif ($this->posts['flag'] == 2) {
                $map['assign_time'] = ['between', [strtotime($this->posts['start']), strtotime($this->posts['end'])]];
            } else {
                $this->returnAjaxMsg('禁止', 403);
            }
            $map['callback_status'] = ['IN', '3,4'];

            $arr = [1 => ['满意', '一般', '不满意', '不愿接受调查'], 2 => ['不错','一般','不怎么样','记不清'], 3 => ['不了解情况', '有', '没有']];

            for ($i = 1; $i < 4; $i++) {
                $question = "question_" . $i . "flag";
                $field = "";
                for($j = 0; $j < count($arr[$i]); $j++) {
                    $field .= "sum(IF( " . $question . "= '" . $arr[$i][$j] . "', 1, 0)) AS '". $arr[$i][$j] . "',";
                }
                $field .= "question_" . $i . " AS 'question" . $i . "', count(id) 'total_num'";
                $statistic[$i] = M('cus_callback')->where($map)->field($field)->group('question_' . $i)->select()[0];
                foreach ($statistic[$i] as $key => $value) {
                    $rst[$i][] = ['name' => $key, 'value' => $value];
                }
                $data[$i - 1]['question']  = $statistic[$i]['question' . $i];
                $data[$i - 1]['total_num'] = $statistic[$i]['total_num'];
                $st[$i] = array_splice($statistic[$i], 0,count($arr[$i]));
                //$st[$i] = array_diff_key($statistic[$i], $array);
                foreach($st[$i] as $key => $value) {
                    $data[$i - 1]['name'][] = $key;
                    $data[$i - 1]['value'][] = $value;
                }
            }

            $this->returnAjaxMsg('统计结果返回', 200, $data);
        } else {
            $assignT = M('cus_callback')->field('from_unixtime(assign_time) assign_times')->group('assign_time')->select();
            $this->assign(compact('assignT'));
            $this->display();
        }
    }



    /**
     * 业绩统计信息
     * 根据不同的orderT确定返回的数据
     * 结算结果信息*/

    public function showPerformanceResult()
    {
        if(IS_POST) {
            $collectionModel = new OrderCollectionModel();
            $this->posts = I('post.');

            // 获取Datatables发送的参数 必要
            $draw = $this->posts['draw'];

            $this->sqlCondition = $this->getSqlCondition($this->posts);

            if (strlen($this->sqlCondition['search']) >= 2) {
                $c = new \SphinxClient();
                $c->setServer('localhost', 9312);
                $c->setMatchMode(SPL_MATCH_ANY);
                $c->setLimits(0,1000,1000,0);
                $data1 = $c->Query($this->sqlCondition['search'], "order");
                $index = array_keys($data1['matches']);
                $c->close();
                $index_str = implode(',', $index);
                if ($index_str !== null) {
                    $map['b.id'] = array('in', $index_str);
                }
            }
            // 下拉菜单传值
            $orderAudit = array(4,5,6,7,8);
            $roleId = (int)session('roleId');
            if (in_array($roleId, $orderAudit)) {
                $this->orderIds = $this->getStaffIds((string)session('staffId'), 'order_child_id', "");
            }
            $thismonth = date('m');
            $thisyear  = date('Y');
            $startDay  = $thisyear . '-' . $thismonth - 1 . '-1';
            $timeLimit1 = empty(I('post.timeLimit1')) ? strtotime($startDay) : strtotime(I('post.timeLimit1'));
            $timeLimit2 = empty(I('post.timeLimit2')) ? time() : strtotime(I('post.timeLimit2'));
            if ($timeLimit2 - $timeLimit1 <= 0) {
                $output = array(
                    "draw" => intval($draw),
                    "recordsTotal" => 0,
                    "recordsFiltered" => 0,
                    "data" => array()
                );
                $this->ajaxReturn($output);
            }
            $map['settle_time'] = array(array('egt', $timeLimit1), array('elt', $timeLimit2));

            $staffLimit = I('post.staffLimit');
            if (empty($staffLimit)) {
                if (empty($this->cusStaffIds)) {
                    $staffIdsFilter['id'] = array('EQ', $this->staffId);
                } else {
                    $staffIdsFilter['id'] = array('IN', $this->cusStaffIds . "," . $this->staffId);
                }
            } else {
                $staffIdsFilter['id'] = array('EQ', (int)$staffLimit);
            }
            $staffIds = M('staff')->field('id,name')->where($staffIdsFilter)->select();
            $map['b.picid'] = array('IN', getPrjIds($staffIds, 'id'));
            $type = (int)$this->posts['dateT'];
            switch($type) {
                case 2 :
                    $this->sqlCondition['group'] = 'b.picid';
                    break;
                case 3 :
                    $this->sqlCondition['group'] = 'b.cus_id';
                    break;
                default:
                    break;
            }
            list($count, $countFilter,$data) = $collectionModel->getCollectionStatistics($type, $map, $this->sqlCondition);
            $this->output = $this->getDataTableOut($draw, $count, $countFilter, $data);
            $this->output['statistics'] = $collectionModel->getStatisticsAmount($map);
            $this->ajaxReturn($this->output);
        } else {
            $this->orderIds = $this->getStaffIds((string)session('staffId'), 'order_child_id', "");
            if ($this->orderIds) {
                $staffIdsFilter['id'] = array('IN', $this->orderIds . "," . $this->staffId);
                $staffIds = M('staff')->field('id,name')->where($staffIdsFilter)->select();
            } else {
                $staffIdsFilter['id'] = array('EQ', $this->staffId);
                $staffIds = M('staff')->field('id,name')->where($staffIdsFilter)->select();
            }
            $this->assign('staffIds', $staffIds);
            $this->display();
        }
    }

    public function resetCusPid()
    {
        if (IS_POST) {
            if (!in_array($this->staffId,[1,65])) {
                $this->returnAjaxMsg('无权限处理', 400);
            }
            $cusId = I('post.cusId');
            $id = M('customer')->find($cusId)['cus_pid'];
            if ($id) {
                $data['cus_pid'] = null;
                $data['cid'] = $cusId;
                $map['cid'] = ['EQ', $cusId];
                $rst = M('customer')->where($map)->setField($data);
                if ($rst === false) {
                    $this->returnAjaxMsg('失败',404);
                }
                $this->returnAjaxMsg('已解除与上级公司的关系', 200);
            }
            $this->returnAjaxMsg('该客户无上级公司, 无需解绑', 400);

        } else {
            die('非法');
        }
    }


    public function getCusStatusField()
    {
        $params = I('post.');
        $config = getCusStatisticsConfig($params['startT'], $params['endT']);
        if ($config['startYear'] > $config['endYear']) {
            $this->ajaxReturn('');
        }

        unset($co);
        $co = [
                ['title' => '部门',   'data' => 'dept_name'],
                ['title' => '客户名', 'data' => 'cus_name'],
                ['title' => '行业', 'data' => 'industry_name'],
                ['title' => '业务',   'data' => 'cus_pic_name'],
                ['title' => '订单数', 'data' => 'order_num_all'],
                ['title' => '出货量', 'data' => 'product_nums'],
                ['title' => '总业绩', 'data' => 'settle_prices']
            ];
        if ($config['endYear'] >= $config['startYear']) {
            $tmpEndM = $config['endM'] + 12 * ($config['endYear'] - $config['startYear']);
        } else {
            $tmpEndM = $config['endM'];
        }

        for ($i = (int)$config['startM']; $i < $tmpEndM; $i++) {
            $string = "order_number" . (int)$i;
            if ($config['endYear'] === $config['startYear']) {
                $startYM = $config['startYear'] . "-" . $i;
            } else {
                $startYM = getYearMonth($config['startYear'], $i);

            }
            array_push($co,['title' => $startYM . '<br>订单数', 'data'=> $string, 'className' => 'orderFilter']);

        }

        for ($i = (int)$config['startM']; $i < $tmpEndM; $i++) {
            $string2 = "product_nums" . (int)$i;
            if ($config['endYear'] === $config['startYear']) {
                $startYM = $config['startYear'] . "-" . $i;
            } else {
                $startYM = getYearMonth($config['startYear'], $i);

            }

            array_push($co,['title' => $startYM . '<br>出货量', 'data'=> $string2, 'className' => 'numFilter']);

        }
        for ($i = (int)$config['startM']; $i < $tmpEndM; $i++) {
            $string3 = "settle_prices" . (int)$i;
            if ($config['endYear'] === $config['startYear']) {
                $startYM = $config['startYear'] . "-" . $i;
            } else {
                $startYM = getYearMonth($config['startYear'], $i);
            }
            array_push($co,['title' => $startYM . '<br>业绩', 'data'=> $string3, 'className' => 'performFilter']);
        }
        $this->ajaxReturn($co);
    }
    /**
     * 默认当前月向前推12个月,显示
     * 用户可选择月份范围
     * 按客户分组
     * 按业务员分组
     *
     *
    */
    public function showCusStatus()
    {

        if (IS_POST) {
            if (!in_array($this->staffId,[1,65])) {
                $this->returnAjaxMsg('无权限', 400);
            }
            $params = I('post.');
            $this->sqlCondition = $this->getSqlCondition($params);
            $config = getCusStatisticsConfig($params['startT'], $params['endT']);

            if ($config['startYear'] > $config['endYear']) {
                $this->ajaxReturn('');
            }
            $collectionModel = new OrderCollectionModel();
            $limitConfig = [
                'industry' => $params['industry'],
                'staff'    => !empty($params['staffId']) ? $params['staffId'] : $this->cusStaffIds . "," . $this->staffId,
                'kpi'      => $params['kpiFlag'],
            ];
            list($count, $filterCount, $data) = $collectionModel->getCusStatistics($config, $this->sqlCondition, $limitConfig);

            $this->output = $this->getDataTableOut($params['draw'], $count, $filterCount, $data);
            $this->ajaxReturn($this->output);
        } else {
            $industry = M('industry')->field('id,name')->select();
            $staff =M('staff')->where(['id'=>['in', $this->cusStaffIds . "," . $this->staffId]])->field('id,name')->select();
            $this->assign(compact('industry','staff'));
            $this->display();
        }

    }



    protected function getOrderBasicData($cusId)
    {

        if (!empty($cusId) && $cusId != 10000000) {

            $id = inject_id_filter($cusId);
            // 客户基本信息
            $cusDatas = $this->getCusData($id);
            $cusCondition['cid']  = array('EQ', $id);

            $cusData  = M('customer')->where($cusCondition)
                ->field('cid cus_id,cname cus_name,addr cus_addr,cphonename cus_contact,cphonenumber cus_contact_number')
                ->find();
            $orderData = [
                'cusAddress'   => $cusDatas['cusAddr'],
                'contact'      => $cusDatas['contact'],
                'customerInfo' => $cusData,
            ];
        } else {
            $orderData = [];
        }
        return $orderData;
    }
    /**
     * 添加订单
    */
    /**
     * 提交客户订单
     *   1 获取提交订单表数据
     *   2 验证提交权限
     *   3 获取提交订单产品数据
     *   4 获取提交订单后台记录数据
     *   5 开启事务，提交数据。
     * @todo 发货仓库在主表中进行提现。不是手动选择的
     */
    private $addOrderSearchCusFlag;
    private $addOrderGetOrderBaseInfoFlag;
    private $addOrderGetProductInfoFlag;
    public function addOrder()
    {
        $customerModel = new CustomerModel();
        $orderModel    = new OrderformModel();

        $this->addOrderSearchCusFlag        = 1;
        $this->addOrderGetOrderBaseInfoFlag = 2;
        $this->addOrderGetProductInfoFlag   = 3;
        if(IS_POST) {
            $this->posts = I('post.');
            if (isset($this->posts['flag']) && $this->posts['flag'] == $this->addOrderSearchCusFlag) {
                if (isset($this->posts['customerkeyword']) && strlen($this->posts['customerkeyword']) > 2) {
                    $customerCondition['uid'] = ['EQ', $this->staffId];
                    $customerCondition['auditstatus'] = ['EQ','3'];
                    $customerCondition['cname'] = ['LIKE', "%" . $this->posts['customerkeyword'] . "%"];
                    $customerInfo = $customerModel->getBusListNAudit($customerCondition, "cid cus_id,cname cus_name", 0, 10,'cid','cid');

                    $this->returnAjaxMsg('返回个人客户信息，最多10条',200, $customerInfo);
                } else {
                    $this->returnAjaxMsg('提交客户关键字需大于两个字符长度',403);
                }

            }
            if (isset($this->posts['flag']) && $this->posts['flag'] == $this->addOrderGetOrderBaseInfoFlag){
                // 生成订单id  获取客户信息、客户下属子公司列表、订单填写人信息、结算方式及发货仓库、产品类型、审核人列表
                // @todo ajax,点击选择客户子客户，返回该客户下的联系人和地址信息。
                $returnArraySet = ['orderType','performanceType','logisticsType','settleType','repType','staffData','prodType','invoiceT','invoiceSituation','freightPayMethod'];
                $orderData  = $this->getOrderBasicData($this->posts['cusId']);
                $orderData['orderInfo']   = $this->getOrderInfo($returnArraySet);
                $orderData['orderIdInfo'] = $this->getOrderId();
                $this->ajaxReturn($orderData);
            }
            if (isset($this->posts['flag']) && $this->posts['flag'] == $this->addOrderGetProductInfoFlag) {
                if (isset($this->posts['productName'])) {
                    $productModel = new MaterialModel();
                    $productData = $productModel->getOrderData(trim($this->posts['productName']));
                } else {
                    $productData = [];
                }
                $this->ajaxReturn($productData);
            }
            if (isset($this->posts['flag']) && ($this->posts['flag'] == 4 || $this->posts['flag'] == 5)) {
                
                $submitOrderRst = $orderModel->submitOrder($this->posts['orderData'],$this->posts['productData'], $this->staffId, (int)$this->posts['flag']);
                $this->ajaxReturn($submitOrderRst);
            }
            $this->returnAjaxMsg("非法提交数据",403);
        } else {
            // 生成订单id  获取客户信息、客户下属子公司列表、订单填写人信息、结算方式及发货仓库、产品类型、审核人列表
            // @todo ajax,点击选择客户子客户，返回该客户下的联系人和地址信息。
            $orderInfo = $this->getOrderInfo(['orderType']);
            if (IS_GET && !empty(I('get.cusId'))) {
                $cusId = I('get.cusId');

                $returnArraySet = ['orderType','performanceType','logisticsType','settleType','repType','staffData','prodType','invoiceT','invoiceSituation','freightPayMethod'];
                $data  = $this->getOrderBasicData($cusId);

                $data['orderInfo']   = $this->getOrderInfo($returnArraySet);
                $data['orderIdInfo'] = $this->getOrderId();
               
                $orderInfo = $data['orderInfo'];
                $hasCus = 1;
                $this->assign(compact('orderInfo', 'data', 'hasCus'));
            } else {
                $this->assign(array('orderInfo' => $orderInfo,'hasCus' => 0,'data' => []));
            }
            $this->display();
        }
    }

    protected function getOrderEditData($cusId,$returnArraySet, $orderId, $orderData, $flag)
    {
        $productModel = new OrderproductModel();
        $basicInfo  = $this->getOrderBasicData($cusId);

        $basicInfo['orderInfo']   = $this->getOrderInfo($returnArraySet);
        if ($flag == 1) {
            $orderData['is_copy'] = 1;
            $orderData['id'] = $this->getOrderId()['order_id'];
            $orderData['cpo_id'] = "CPO" . $orderData['id'];
        }
        $productFilter['order_id'] = ['EQ', $orderId];
        $productFilter['is_del'] = ['EQ', 0];

        $productData = $productModel->alias('order_product')->where($productFilter)
            ->join("LEFT JOIN crm_stock ON order_product.product_id = crm_stock.product_id AND crm_stock.warehouse_number = 'K004'")
            ->join('LEFT JOIN crm_repertorylist rep ON crm_stock.warehouse_number = rep.rep_id')
            ->field('order_product.*,rep.*')
            ->select();
        $msg = [
            'status' => 200,
            'data'   =>[compact('basicInfo','productData', 'orderData')]
        ];

        return $msg;
    }
    public function editOrder()
    {
        $orderModel   = new OrderformModel();

        $orderId = I('get.orderId');


        $orderFilter['crm_orderform.id'] = ['EQ', $orderId];
        $orderFilter['crm_orderform.picid'] = ['EQ', $this->staffId];
        $this->field = "crm_orderform.*";
        $orderData = $orderModel->getOrderOneData($orderFilter, $this->field,'crm_orderform.id');
        $filter['cid'] = ['EQ', $orderData['cus_id']];
        $cusName = M('customer')->where($filter)->field('cname')->find()['cname'];
        $orderData['cus_name'] = $cusName;
        $type = (int)I('get.is_copy');
        if (empty($orderData)) {
            $msg = ['status' => 403, 'msg'=> '订单号不存在或不是您负责的订单'];
            die('错误码：' . $msg['status'] .'；'. $msg['msg']);
        } else {
            $returnArraySet = ['orderType','performanceType', 'logisticsType', 'settleType','repType','staffData','prodType','invoiceT','invoiceSituation','freightPayMethod'];

            if (!empty($type) && $type == 1) {
                $msg = $this->getOrderEditData($orderData['cus_id'], $returnArraySet, $orderId, $orderData, 1);
            } else {
                if ($orderData['check_status'] != OrderformModel::UN_STATUS && $orderData['check_status'] != OrderformModel::SAVE_STATUS) {
                    $msg = ['status' => 403, 'msg' => "仅不合格订单支持修改"];
                    die('错误码：' . $msg['status'] .'；'. $msg['msg']);
                } else {
                    $msg = $this->getOrderEditData($orderData['cus_id'], $returnArraySet, $orderId, $orderData, 0);
                }
            }

            $this->assign(compact('msg'));
            $this->display();
        }
    }

    /**
     * 添加KPI客户
     * @param $cusId
     */
    public function addKpiCusAudit()
    {

        if (IS_POST){
            if (session('addAuth') != $this->staffId) {
                unset($_SESSION['addAuth']);
                $this->returnAjaxMsg('禁止提交', -1);
            }

            $params = array_column(I('post.formData'), 'value', 'name');
            $params['changetime'] = time();
            $params['auth_flag'] = 4;
            $params['change_id'] = session('staffId');
            $params['change_reason'] = "客户KPI申请操作，申请时间" . date('Y-m-d H:i:s') . "申请人：" . session('nickname');
            // 判断提交人身份
            $res = M('cuschangerecord') -> add($params);
            if ($res !== false){
                $msg    = '提交成功';
                $status = 1;
            }else{
                $msg    = '提交失败';
                $status = -1;
            }
            unset($_SESSION['addAuth']);
            $this->ajaxReturn([
                'status' => $status,
                'msg'    => $msg,
            ]);
        }else{

            $cusId = I('get.cusId');

            $cusInfo = M('customer')
                -> field('customer.*, industry.name as industry')
                -> alias('customer')
                -> join('left join crm_industry as industry on customer.ctype = industry.id')
                -> find($cusId);

            $staffIds = $this->getRoleStaffIds(self::CUS_NAME_CHANGE_ROLE);
            if (!in_array($this->staffId, explode(',',$staffIds)) && $this->staffId != $cusInfo['uid']) {
                $html = "<h3 style='text-align: center;margin-top: 20%;'>";
                $html .= '您无权提交申请';
                $html .="</h3>";
                die($html);
            }
            session('addAuth', $this->staffId);
            $map['id'] = ['IN', $staffIds];
            $staffData = M('staff')->where($map)->field('id,name')->select();
            $this->assign(compact('cusInfo','staffData'));
            $this->display();
        }
    }

    /**
     *  审核KPI客户申请
     */
    public function auditKpiCus()
    {
        if (IS_POST){
            // 获取所有审核人是当前用户的未审核申请
            if (I('post.flag') == 1){
                $map = [
                    'auth_flag' => ['EQ', 4],
                    'auth_id' => ['EQ', session('staffId')],
                ];
                $table = M('cuschangerecord')
                    -> alias('record')
                    -> field('record.*, staff.name')
                    -> join('left join crm_staff as staff on record.change_id = staff.id')
                    -> where($map)
                    -> select();
                $this->ajaxReturn($table);
            }else{
                //处理审核
                $currentID = session('staffId');
                $staffIds = $this->getRoleStaffIds(self::CUS_NAME_CHANGE_ROLE);
                if (!in_array($currentID, explode(",",$staffIds))) {
                    $this->ajaxReturn([
                        'status' => -1,
                        'msg' => '无权限审核'
                    ]);
                }
                M() -> startTrans();
                $id = I('post.id');
                $res = I('post.res');
                $kpi_auth_tip = I('post.kpi_auth_tip');
                // 更新记录
                if ($res == 2){
                    $str1 = '添加KPI客户申请通过,';
                }else{
                    $str1 = '添加KPI客户申请驳回,';
                }
                $audior = session('nickname');
                $proposer = M('staff') -> find(M('cuschangerecord') -> find($id)['change_id'])['name'];
                $str2 = "申请人$proposer, 审核人$audior, 时间" . date('Y-m-d H:i:s');
                $change_reason = $str1 . $str2;
                $recordUpdate = M('cuschangerecord') -> save(
                    [
                        'id' => $id,
                        'auth_flag' => $res == 2 ? 5 : 6,
                        'kpi_auth_tip' => $kpi_auth_tip,
                        'changetime' => time()
                    ]
                );

                $customerUpdate = true;
                if ($recordUpdate !== false){
                    // 修改kpi标识
                    $record = M('cuschangerecord') -> find($id);
                    $addData = [
                        'changetime' => time(),
                        'change_id'  => session('staffId'),
                        'change_reason' => $change_reason,
                        'cusid'      => $record['cusid'],
                        'oldname'    => $record['oldname']
                    ];
                    $customerUpdate = M('cuschangerecord')->add($addData);
                    if ($res == 2 && $customerUpdate !== false){
                        $data = [
                            'kpi_background' => $record['kpi_background'],
                            'kpi_annual_turnover' => $record['kpi_annual_turnover'],
                            'kpi_application' => $record['kpi_application'],
                            'kpi_potential'   => $record['kpi_potential'],
                            'kpi_auth_tip' => $record['kpi_auth_tip'],
                            'kpi_industry' => $record['kpi_industry'],
                            'kpi_flag' => '1',
                            'cid' => $record['cusid']
                        ];
                        $customerUpdate = M('customer') -> save($data);
                    }
                }
                if ($recordUpdate === false || $customerUpdate === false){
                    M() -> rollback();
                    $this->ajaxReturn([
                        'status' => -1,
                        'msg' => '审核失败'
                    ]);
                }else{
                    M() -> commit();
                    $this->ajaxReturn([
                        'status' => 1,
                        'msg' => '审核成功'
                    ]);
                }
            }
        }else{
            $this->assign(compact('table'));
            $this->display();
        }
    }

    /**
     * 取消KPI客户身份
     * @param $id   int     客户id
     */
    public function cancelKPI()
    {
        if (IS_POST){
            // 检查职位
            $id = I('post.id');
            $currentID = session('staffId');
            $staffIds = $this->getRoleStaffIds(self::CUS_NAME_CHANGE_ROLE);
            if (in_array($currentID, explode(",",$staffIds))) {
                $res = M('customer') -> save(['cid' => $id, 'kpi_flag' => 0]);

                if ($res === false){
                    $this->ajaxReturn([
                        'status' => -2,
                        'msg' => '数据更新失败'
                    ]);
                }else{
                    $this->ajaxReturn([
                        'status' => 1,
                        'msg' => '取消成功'
                    ]);
                }
            } else {

                $this->ajaxReturn([
                    'status' => -1,
                    'msg' => '没权限取消该KPI客户资格'
                ]);
            }
        }
    }

    /**
     * 查看所有KPI客户
     */
    public function getKpiCus()
    {
        if (IS_POST){
            if (I('post.flag') == 'getSelectInfo'){
                $res = [
                    'potential' => ['全部', '1-5万台', '5-10万台', '10万台以上'],
                ];
                $res['staff'] = M('staff') -> field('id, name') -> where(['id' => ['IN', $this -> cusStaffIds]]) -> select();
                array_unshift($res['staff'], ['id' => 0, 'name' => '全部']);
                $res['industry'] = M('industry') -> select();
                array_unshift($res['industry'], ['id' => 0, 'name' => '全部']);
                $this->ajaxReturn($res);
            }
            $where = I('post.where');
            $page  = I('post.page');
            $map = [
                'staff' => 'uid',
                'potential' => 'kpi_potential',
                'industry' => 'ctype',
                'cname' => 'cname',
            ];
            $newWhere = [];
            foreach ($where as $key => $value) {
                // 过滤空条件
                if ($value !== '' && $value !== 'false' && $value !== '0' && $value !== '全部'){
                    if ($key === 'hasChild'){
                        $newWhere['uid'] = ['IN', $this->cusStaffIds];
                    }else{
                        $newWhere[$map[$key]] = ['EQ', $value];
                    }
                }
            }

            if(count($newWhere) == 0){
                $newWhere = ['uid' => ['EQ', session('staffId')]];
            }
            $newWhere['kpi_flag'] = ['EQ', '1'];
            $res = [
                'data' => [],
                'page' => [],
                'auditNum' => 0
            ];
            $res['auditNum'] = M('cuschangerecord') -> where(['auth_flag' => ['EQ', 4], 'auth_id' => ['EQ', session('staffId')]]) -> count();
            $res['page']['total'] = M('customer') -> where($newWhere) -> count();
            $res['page']['page'] = $page['page'];
            $res['data'] = M('customer')
                 -> alias('customer')
                 -> field('customer.cid, customer.cname, customer.annual_order_amount, customer.kpi_background, customer.kpi_application, customer.kpi_potential, customer.kpi_auth_tip, staff.name staff')
                 -> join('left join crm_staff as staff on customer.uid = staff.id')
                 -> where($newWhere)
                 -> page($page['page'], 10)
                 -> select();
            $this->ajaxReturn($res);

        }else{
            $this->display();
        }
    }

    public function addCusContactRecordIndex()
    {
        $this->display();
    }

    /**
     * 市场部客户联系统计页
     */
    public function cusContactStatis()
    {
        $highRole = "4";
        $medRole = "41,42";
        $lowRole = "9,18,19";
        $allRole = "4,9,18,19,41,42";
        if (IS_POST) {
            $highRoleIds = $this->getRoleStaffIds($highRole);
            $medRoleIds = $this->getRoleStaffIds($medRole);
            $allRoleIds = $this->getRoleStaffIds($allRole);
            if (!in_array($this->staffId, explode(',', $allRoleIds))) {
                $idString = (string)$this->staffId;
            } elseif (in_array($this->staffId,explode(',', $highRoleIds))) {
                $idString = (string)$allRoleIds;
            } elseif (in_array($this->staffId,explode(',', $medRoleIds))) {

                $idString = $this->cusStaffIds . "," . $this->staffId;
            } else {
                $idString = (string)$this->staffId;
            }
            $where = I('post.where');
            $model = M('contactrecord');
            $map = [
                'ctime' => ['BETWEEN', [$where['startTime'], $where['endTime']]],
                'picid' => ['in', $idString]
            ];
            if (empty($where['startTime']) || empty($where['endTime'])){
                $map['ctime'] = ['BETWEEN', [mktime(0,0,0,date('m'),1,date('Y')),time()]];
            }
            // 获取基础统计信息
            $subQuery = $model
                 -> field("count(*) contactCount, picid, count(customerid) cusCount")
                 -> where($map)
                 -> group('picid, customerid')
                 -> buildSql();
            $data = $model
                 -> table($subQuery)
                 -> alias('contact')
                 -> field("count(cusCount) cusCount, picid, sum(contactCount) contactCount, staff.name")
                 -> join('left join crm_staff staff on contact.picid = staff.id')
                 -> group('picid')
                 -> page($where['page'], 10)
                 -> select();
            $count = $model
                -> where($map)
                -> group('picid')
                -> count();
            $count = ceil($count / 10);

            // 获取每个人的联系详情
            foreach ($data as $key => &$cus) {
                $map['picid'] = ['EQ', $cus['picid']];
                $cus['detail'] = $model
                     -> alias('detail')
                     -> field("cname, industry.name industry_name, staff.name staff_name, count(*) contact_count, customerid")
                     -> where($map)
                     -> join('left join crm_customer cus on detail.customerid = cus.cid')
                     -> join('left join crm_staff staff on cus.uid = staff.id')
                     -> join('left join crm_industry industry on industry.id = cus.ctype')
                     -> group('customerid')
                     -> select();
                foreach ($cus['detail'] as $key2 => &$item) {
                    $map['customerid'] = ['EQ', $item['customerid']];
                    $item['records'] = $model
                         -> field("theme, content, FROM_UNIXTIME(ctime, '%Y-%m-%d') ctime")
                         -> where($map)
                         -> select();
                    unset($map['customerid']);
                }
                unset($map['picid']);
            }
            $res = [
                'data' => $data,
                'total' => $count
            ];
            $this->ajaxReturn($res);

        }else{
            $this->display();
        }
    }

    public function showMarketCusStatistics()
    {
        if (IS_POST) {
            $highRole = "4";
            $medRole  = "41,42";
            $lowRole  = "9,18,19";
            $allRole  = "4,9,18,19,41,42";
            $highRoleIds = $this->getRoleStaffIds($highRole);
            $medRoleIds = $this->getRoleStaffIds($medRole);
            $allRoleIds = $this->getRoleStaffIds($allRole);
            if (!in_array($this->staffId, explode(',', $allRoleIds))) {
                $idString = (string)$this->staffId;
            } elseif (in_array($this->staffId,explode(',', $highRoleIds))) {
                $idString = (string)$allRoleIds;
            } elseif (in_array($this->staffId,explode(',', $medRoleIds))) {
                $idString = $this->cusStaffIds . "," . $this->staffId;
            } else {
                $idString = (string)$this->staffId;
            }

            $map['addtime']    = ['between', [I('post.startT'), I('post.endT')]];
            $map['founderid'] = ['in', $idString];
            $this->sqlCondition = $this->getSqlCondition(I('post.'));
            $cusModel = new CustomerModel();
            if (I('post.staff_id')) {
                $map['founderid'] = ['eq', (int)I('post.staff_id')];
                $data = $cusModel->alias('cus')->where($map)->field("cid, cname cus_name,ifnull(b.name,'无') u_name,from_unixtime(addtime,'%Y-%m-%d') add_time")
                    ->join('LEFT JOIN crm_staff b ON b.id = cus.uid and uid is not null')->select();
                $this->returnAjaxMsg('ok',200, $data);
            }

            $data = $cusModel->alias('cus')
                ->field('sum(case when uid is not null then 1 else 0 end) cus_market_num,count(cid) cus_sale_num, founderid, crm_staff.name staff_name')
                ->join('LEFT JOIN crm_staff ON founderid = crm_staff.id')
                ->where($map)
                ->group('founderid')
                ->limit($this->sqlCondition['start'], $this->sqlCondition['length'])
                ->select();
            foreach ($data as &$item) {
                $item['DT_RowId'] = $item['founderid'];
            }
            $count = $cusModel->where($map)->count();
            $this->output = $this->getDataTableOut(I('post.draw'), $count, $count, $data);
            $this->output['d']= $map;
            $this->ajaxReturn($this->output);

        } else {
            $this->display();
        }
    }

    public function getDelDataList()
    {
        if ($this->staffId !== 65) {
            die('仅总经理账户有权进入客户删除页面');
        }
        if (IS_POST) {
            $cusModel = new CustomerModel();
            $this->posts = I('post.');
            //获取Datatables发送的参数 必要
            $draw = $this->posts['draw'];
            $this->sqlCondition = $this->getSqlCondition($this->posts);


            $where['crm_customer.cstatus'] = array('EQ', '1');
            $count = M('customer')->where($where)->count('cid');
            //$where['c.founderid'] = array('eq','.id');
            // 连表查询获得创建人姓名
            if (!empty($this->sqlCondition['search'])) {
                $where['crm_customer.cname|crm_industry.name|crm_customer.province|crm_staff.name'] = array('LIKE', "%" . $this->sqlCondition['search'] . "%");
            }
            $filterCount = M('customer')
                ->join('LEFT JOIN crm_industry ON crm_industry.id = crm_customer.ctype')
                ->join('LEFT JOIN crm_staff ON crm_staff.id = crm_customer.founderid')
                ->where($where)->count('cid');
            $commonSql = $cusModel->getCommonCustomerList($where, $this->sqlCondition['order'], $this->sqlCondition['start'], $this->sqlCondition['length']);
            $data = $cusModel->getCommonCustomerData($commonSql);

            if (count($data) != 0) {
                foreach($data as $key => &$val) {
                    $val['DT_RowId']    = $val['cid'];
                    $val['id']    = $val['cid'];
                    $val['DT_RowClass'] = 'gradeX';
                    $val['indus']       = empty($val['indus']) ? "未填" : $val['indus'];
                    if ($val['sub_name']) {
                        $val['sub_name'] = $val['sub_name'];
                    }
                    if ($val['son_name']) {
                        $val['sub_name'] = $val['son_name'];
                    }
                    $val['sub_name'] = empty($val['sub_name']) ? "" : $val['sub_name'];
                }
            } else {
                $data = "";
            }
            $output = array(
                "draw"            => intval($draw),
                "recordsTotal"    => $count,
                "recordsFiltered" => $filterCount,
                "data"            => $data
            );
            $this->ajaxReturn($output);
        } else {
            $this->display('delDataList');
        }
    }


    /**
     * 查询客户负责人名下客户数量，kpi客户数，去除子公司（cus_pid有值）的数量
     */
    public function customerNumberList()
    {
        if(IS_POST){
            $this->posts = I('post.');
            // 获取Datatables发送的参数 必要
            $draw = $this->posts['draw'];
            $this->sqlCondition = $this->getSqlCondition($this->posts);

            $customerModel = new CustomerModel();
            $map['uid'] = ['in', $this->cusStaffIds . "," . $this->staffId];
            list($data,$count,$recordsFiltered) = $customerModel->getCustomerNumberList($map,$this->sqlCondition['search'], $this->sqlCondition['start'], $this->sqlCondition['length'], $this->sqlCondition['order']);

            $this->output = $this->getDataTableOut($draw, $count, $recordsFiltered, $data);
            $this->ajaxReturn($this->output);
        }else {
            $this->display();
        }
    }
}
