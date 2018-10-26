<?php
/**
 * Created by PhpStorm.
 * User: hp
 * Date: 17-5-11
 * Time: 下午4:02
 */

namespace Dwin\Controller;


use Dwin\Model\AuthRoleModel;
use Think\Controller;

class CommonController extends Controller
{
    /*系统相关记录查看时间限制*/
    protected $timeLimit;
    /*系统权限节点字符串*/
    protected $rules;
    /*用户职位*/
    protected $position;
    /*用户部门*/
    protected $dept;
    /*用户id*/
    protected $staffId;
    /*dataTables 数据表输出数组*/
    protected $output;
    /* 前端数据接收post数据*/
    protected $posts;
    /* 数据表查询sql条件中字段限制*/
    protected $field;
    /* dataTables 根据前端输入得到的sql查询条件*/
    protected $sqlCondition;
    /* 数据表where条件*/
    protected $whereCondition;
    protected $keyFilter;
    /**
     * 构造方法：执行权限判断以及一些属性的赋值操作
     * 本构造方法是所有其他子类都继承的
     * 功能1 ： 判断session中是否有用户id（PublicController中loginOk后，会存session
     * 功能2 ： 读取数据表中设置的timeLimit字段后赋值给 protected $timeLimit
     * 功能3 ： 判断权限 ：判断是否有审核权限： 有权限给用户增加对应权限；当前的ACTION_NAME 是否在数据表中auth_ids中
     * 功能4 ： sphinx中文分词类的加载
    */
    public function _initialize()
    {
        require_once('sphinxapi.php');
        // 判断session是否存在
        $this->staffId = empty($this->staffId) ? (int)session('staffId') : $this->staffId;
        if (empty($this->staffId)) {
            // 如果没有登录，则进行跳转
            $url = U('Public/login');
            // header("Location:$url");exit;
            // 通过javascript代码实现
            $script = "<script>window.top.location.href='$url';</script>";
            echo $script;exit;
        }
        $this->keyFilter   = "北京,上海,天津,重庆,(,),（,）,科技发展有限公司,科技有限公司,技术有限公司,实业有限公司,有限责任公司,电子有限公司,股份有限公司,有限公司,公司,研究所,研究院,市,省,河北,石家庄,张家口,承德,唐山,秦皇岛,廊坊,保定,沧州,衡水,邢台,邯郸,山西,太原,大同,朔州,忻州,阳泉,晋中,吕梁,长治,临汾,晋城,运城,内蒙古自治区,呼和浩特,呼伦贝尔,通辽,赤峰,巴彦淖尔,乌兰察布,包头,鄂尔多斯,乌海,黑龙江,哈尔滨,黑河,伊春,齐齐哈尔,鹤岗,佳木斯,双鸭山,绥化,大庆,七台河,鸡西,牡丹江,吉林,长春,白城,松原,吉林,四平,辽源,白山,通化,辽宁,沈阳,铁岭,阜新,抚顺,朝阳,本溪,辽阳,鞍山,盘锦,锦州,葫芦岛,营口,丹东,大连,江苏,南京,连云港,徐州,宿迁,淮安,盐城,泰州,扬州,镇江,南通,常州,无锡,苏州,浙江,杭州,湖州,嘉兴,绍兴,舟山,宁波,金华,衢州,台州,丽水,温州,安徽,合肥,淮北,亳州,宿州,蚌埠,阜阳,淮南,滁州,六安,马鞍山,巢湖,芜湖,宣城,铜陵,池州,安庆,黄山,福建,福州,宁德,南平,三明,莆田,龙岩,泉州,漳州,厦门,江西,南昌,九江,景德镇,上饶,鹰潭,抚州,新余,宜春,萍乡,吉安,赣州,山东,济南,德州,滨州,东营,烟台,威海,淄博,潍坊,聊城,泰安,莱芜,青岛,日照,济宁,菏泽,临沂,枣庄, 河南,郑州,安阳,鹤壁,濮阳,新乡,焦作,三门峡,开封,洛阳,商丘,许昌,平顶山,周口,漯河,南阳,驻马店,信阳,湖北,武汉,十堰,襄樊,随州,荆门,孝感,宜昌,黄冈,鄂州,荆州,黄石,咸宁,湖南,长沙,岳阳,张家界,常德,益阳,湘潭,株洲,娄底,怀化,邵阳,衡阳,永州,郴州,广东,广州,韶关,梅州,河源,清远,潮州,揭阳,汕头,肇庆,惠州,佛山,东莞,云浮,汕尾,江门,中山,深圳,珠海,阳江,茂名,湛江广西壮族自治区,南宁,桂林,河池,贺州,柳州,百色,来宾,梧州,贵港,玉林,崇左,钦州,防城港,北海,海南,海口,三亚,三沙,儋州,四川,成都,广元,巴中,绵阳,德阳,达州,南充,遂宁,广安,资阳,眉山,雅安,内江,乐山,自贡,泸州,宜宾,攀枝花,贵州,贵阳,遵义,六盘水,安顺,云南,昆明,昭通,丽江,曲靖,保山,玉溪,临沧,普洱,西藏自治区,拉萨,昌都,日喀则,林芝,陕西,西安,榆林,延安,铜川,渭南,宝鸡,咸阳,商洛,汉中,安康,甘肃,兰州,嘉峪关,酒泉,张掖,金昌,武威,白银,庆阳,平凉,定西,天水,陇南,青海,西宁,海东,宁夏回族自治区,银川,石嘴山,吴忠,中卫,固原,新疆维吾尔自治区,乌鲁木齐,克拉玛依,吐鲁番";
        if (!$this->timeLimit) {
            // 系统设定的相关记录最长时效（针对客服、售后）
            $map['id'] = array('EQ', 1);
            $rst = M('system')->where($map)->field('timelimit')->find();
            $this->timeLimit = time() -  $rst['timelimit'] * 3600 * 24;
        }
        // 获取user对应的权限
        $userFilter['auth_id'] = array('in', inject_filter(session('userRule')));
        $ruleData = M('auth_rule')->where($userFilter)->field('rule_string')->select();

        $authString = getPrjIds($ruleData, 'rule_string');
        $auth = explode(',', $authString);

        // RBAC权限判断
        $cname = strtolower(CONTROLLER_NAME);//控制器
        $aname = strtolower(ACTION_NAME);//方法
        if ($aname != "getmsgcount") {
            $dat = $this->staffLog();
            $rst = M('stafflog')->add($dat);
        }

        // 判断权限
        $roleId = empty($roleId) ? session('roleId') : $roleId;//审核权限
        /**
         * @param int $roleId
         * @return array $auth_1 对应审核权限的权限节点
        */
        switch ((int)($roleId)) {
            case 0 :
                $auth_1 = array();
                break;
            case 1 :
                $auth_1 = array('customer/showcustomer', 'customer/showcustomeraudit', 'customer/showcustomerauditlist', 'customer/checkcustomer');
                break;
            case 2 :
                $auth_1 = array('research/showprjaudit', 'research/checkproject');
                break;
            case 3 :
                $auth_1 = array('customer/showcustomer', 'customer/showcustomeraudit', 'customer/showcustomerauditlist', 'customer/checkcustomer', 'research/showprjaudit', 'research/checkproject');
                break;
            case 4 :
                $auth_1 = array('finance/showorderaudit', 'finance/checkorder', 'finance/addunqualified');
                break;
            case 5 :
                $auth_1 = array('customer/showcustomer', 'customer/showcustomeraudit', 'customer/showcustomerauditlist', 'customer/checkcustomer', 'finance/showorderaudit', 'finance/checkorder', 'finance/addunqualified');
                break;
            case 6 :
                $auth_1 = array('research/showprjaudit', 'research/checkproject', 'finance/showorderaudit', 'finance/checkorder', 'finance/addunqualified');
                break;
            case 7 :
                $auth_1 = array('customer/showcustomer', 'customer/showcustomeraudit', 'customer/showcustomerauditlist', 'customer/checkcustomer', 'research/showprjaudit', 'research/checkproject', 'finance/showorderaudit', 'finance/checkorder', 'finance/addunqualified');
                break;
            case 8 :
                $auth_1 = array('customer/*', 'research/*');
                break;
        }
        $auth = array_merge($auth, $auth_1);
        if(!in_array($cname . '/*',$auth) && !in_array($cname . '/' . $aname,$auth)){
//            if (IS_AJAX) {
//                $this->returnAjaxMsg('无权使用该功能，如有问题请联系管理', 403);
//            } else {
//                $this->redirect('Public/403');exit;
//            }

        }
    }


    protected function staffLog()
    {
        // $Ip = new \Org\Net\IpLocation('qqwry.dat'); // 实例化类 参数表示IP地址库文件
        // $area = $Ip->getlocation($_SERVER['REMOTE_ADDR']); // 获取某个IP地址所在的位置
        // RBAC权限判断
        $thisRule = strtolower(CONTROLLER_NAME) . "/" . strtolower(ACTION_NAME);
        $authFilter['rule_string'] = array('LIKE', "%". $thisRule . "%");
        $data = M('auth_rule')->where($authFilter)->field('rule_name auth_rule_name,log_level level')->order('log_level desc')->find();
        $data['staffid'] = $this->staffId;
        $data['remote_addr'] = $_SERVER['REMOTE_ADDR'];
        $data['request_uri'] = $thisRule;
        $data['request_time'] = time();
        //$data['ip_location']  = iconv('gbk','utf-8',$area['country'].$area['area']);
        $data['user_agent']  = $_SERVER['HTTP_USER_AGENT'];
        if (IS_POST) {
            $data['request_method'] = "POST";
            $data['request_info'] = json_encode(I('post.'));
        } elseif (IS_GET) {
            $data['request_method'] = "GET";
            $data['request_info'] = json_encode(I('get.'));
        } else {
            $data['request_method'] = "NONE";
        }
        return $data;

    }
    /**
     * @name getSqlCondition
     * @abstract 利用dataTables通过post得到的数据$data返回sql查询条件
     * @param array $data 包含$data['order']排序 $data['columns']查询的字段名 $data['start']|$data['length']查询limit字段 $data['search']查询的搜索条件
     * @return array $sqlCondition 输入多为表格列索引 进行处理后返回实际字段名。
    */
    protected function getSqlCondition($data)
    {
        $sqlCondition = array();
        $order_dir    = $data['order']['0']['dir'];//ase desc 升序或者降序
        $order_column = (int)$data['order']['0']['column'];
        $sqlCondition['order'] = $data['columns'][$order_column]['data'] . " " . $order_dir;
        $limitFlag  = isset($data['start']) && $data['length'] != -1 ;
        if ($limitFlag) {
            $sqlCondition['start']  = (int)$data['start'];
            $sqlCondition['length'] = (int)$data['length'];
        } else {
            $sqlCondition['start']  = 0;
            $sqlCondition['length'] = 10;
        }
        //搜索
        $sqlCondition['search'] = $data['search']['value'];//获取前台传过来的过滤条件
        return $sqlCondition;
    }



    /**
     * @name getSearchIndex
     * @abstract 根据查询条件以及要搜索的索引进行搜索，得到查询关键字在索引中的匹配主键，并以逗号连接的形式返回字符串
     * @param string $search 查询的搜索词
     * @const $matchMode 匹配模式
     * @param string $indexString 要搜索的索引名
     * @param boolean $returnLimitFlag 是否默认限制查询结果数量（false 为默认20条 true 为默认200条）
     * @return string $indexStr 索引中的匹配主键，并以逗号连接的形式返回字符串
     */
    protected function getSearchIndex($search, $matchMode, $indexString, $returnLimitFlag)
    {
        if (strlen($search) >= 2) {
            $c = new \SphinxClient();
            $c->setServer('localhost', 9312);
            $c->setMatchMode($matchMode);
            if ($returnLimitFlag) {
                $c->setLimits(0,1000);
            }
            $data = $c->Query($search, $indexString);
            $index = array_keys($data['matches']);
            $c->close();
            $indexStr = implode(',', $index);
        } else {
            $indexStr = null;
        }
        return $indexStr;
    }


    /**
     * @name getDataTableOut
     * @abstract 返回DataTables 需要的数据
     * @param int $draw 表格加载的次数
     * @param int $count 筛选前的总数
     * @param int $recordsFiltered 关键词筛选后的数量
     * @param array $content 要返回给前端并加载的数据
     * @return array $out 返回的数据
    */
    protected function getDataTableOut($draw,$count,$recordsFiltered,$contents)
    {
        return $out = array(
            "draw"            => intval($draw),
            "recordsTotal"    => $count,
            "recordsFiltered" => $recordsFiltered,
            "data"            => $contents
        );
    }

    /**
     * @name getStaffIds
     * @abstract 获取员工id方法 根据某员工id,及要获取某种下属权限的字符串，递归的获取该员工权限下的所有id以逗号连接
     * @param int $id userID
     * $param string $string 取出的userID哪个流程节点对应的数据库字段名
     * @param string $uid 递归调用返回值，初始值设置为"";
     * @return  array $staffId 负责的所有员工id
     */
    protected function getStaffIds($id, $string, $uid)
    {
        $filter['crm_staff.id'] = array('IN', $id);
        $field = "crm_staff.id,min(b.pid)," . $string;
        $uidArray = M('staff')
            ->where($filter)
            ->field($field)
            ->join('LEFT JOIN crm_auth_role b ON FIND_IN_SET(crm_staff.id,b.staff_ids)')
            ->group('crm_staff.id')
            ->select();//$id对应的直属下属的uidArray 可能有""
        // 直属的也可能有级别高于当前id的人，这种情况不进行递归查询下一级id
        foreach ($uidArray as $k => $val) {
            if (empty($val[$string])) {
                unset($uidArray[$k]);
            }
        }
        $uidArray = array_values(array_filter($uidArray)); //空值去除
        $uuid = $uidArray ?  getPrjIds($uidArray, $string) : "";
        $uid = empty($uuid) ? ($uid) : (empty($uid) ? $uuid : $uid . "," . $uuid);

        if (!empty($uuid)) {
            $rst = $this->getStaffIds($uuid, $string, $uid);
            return $rst;
        }
        return $uid;
    }

    /**
     * @name getRoleStaffIds
     * @abstract 通过职位的id,获取具有该职位的用户id,以逗号连接
     * @param mixed $roleIds 字符串，以逗号连接
     * @return string $onlineIds 返回以逗号连接的用户id字符串
    */
    protected function getRoleStaffIds($roleIds)
    {
        $map['role_id'] = array('in', $roleIds);
        $data = M('auth_role')->where($map)->field('role_id,staff_ids')->select();
        $ids = getPrjIds($data, "staff_ids");
        return $ids;
    }
    /**
     * @name get_amount
     * @abstract 数字金额转换成中文大写金额的函数
     * @param mixed  $num  要转换的小写数字或小写字符串
     * @return string 大写的金额
     * 小数位为两位
     * @todo 此方法需要封装在function中而不是在Controller里
     **/
    protected function get_amount($num)
    {
        $c1 = "零壹贰叁肆伍陆柒捌玖";
        $c2 = "分角圆拾佰仟万拾佰仟亿";
        $num = round($num, 2);
        $num = $num * 100;
        if (strlen($num) > 10) {
            return "数据太长，没有这么大的钱吧，检查下";
        }
        $i = 0;
        $c = "";
        while (1) {
            if ($i == 0) {
                $n = substr($num, strlen($num)-1, 1);
            } else {
                $n = $num % 10;
            }
            $p1 = substr($c1, 3 * $n, 3);
            $p2 = substr($c2, 3 * $i, 3);
            if ($n != '0' || ($n == '0' && ($p2 == '亿' || $p2 == '万' || $p2 == '圆'))) {
                $c = $p1 . $p2 . $c;
            } else {
                $c = $p1 . $c;
            }
            $i = $i + 1;
            $num = $num / 10;
            $num = (int)$num;
            if ($num == 0) {
                break;
            }
        }
        $j = 0;
        $slen = strlen($c);
        while ($j < $slen) {
            $m = substr($c, $j, 6);
            if ($m == '零圆' || $m == '零万' || $m == '零亿' || $m == '零零') {
                $left = substr($c, 0, $j);
                $right = substr($c, $j + 3);
                $c = $left . $right;
                $j = $j-3;
                $slen = $slen-3;
            }
            $j = $j + 3;
        }

        if (substr($c, strlen($c)-3, 3) == '零') {
            $c = substr($c, 0, strlen($c)-3);
        }
        if (empty($c)) {
            return "零圆整";
        }else{
            return $c . "整";
        }
    }

    /**
     * @name checkRole
     * @abstract 检查账户的审核权限
     * @return int $msg
     * @todo 该方法为系统架构初期使用的函数，有不合理之处，后续方法不必使用，但是如无特殊情况禁止修改删除
    */
    public function checkRole()
    {
        $roleId = (int)session('roleId');
        $uLevel = (int)session('rLevel');

        if ($uLevel != 1) {
            switch ($roleId) {
                case 0 :
                    $msg['status'] = 0;break;
                case 1 :
                    $msg['status'] = 1;break;
                case 2 :
                    $msg['status'] = 2;break;
                case 3 :
                    $msg['status'] = 3;break;
                case 4 :
                    $msg['status'] = 4;break;
                case 5 :
                    $msg['status'] = 5;break;
                case 6 :
                    $msg['status'] = 6;break;
                case 7 :
                    $msg['status'] = 7;break;
                case 8 :
                    $msg['status'] = 8;break;
                default :
                    $msg['status'] = 0;break;
            }
        } else {
            $msg['status'] = 8;
        }
        $this->ajaxReturn($msg);
    }

    /**
     * @name changeRecordStatus
     * @abstract 审核改变表状态使用的方法（2017年编写，用于客服记录、售后记录审核修改状态使用）
     * @param int $uid 记录选定审核人的id
     * @param int $changeId 要修改状态的记录主键ID
     * @param int $flag 同意、驳回记录对应的值 2 为同意  1 为反对
     * @param string $statusName 数据表中要修改的数据的字段名
     * @param string $table 要修改的数据表明
     * @param int||string $trueValue 同意时设置的字段
     * @param int||string $falseValue 反对时设置的字段
     * @return int $msg 2 为成功，1为失败
     */
    protected function changeRecordStatus($uid, $changeId, $flag, $statusName, $table, $trueValue, $falseValue)
    {
        $staffId = session('staffId');
        //记录审核，业务员审核。
        if ($uid != $staffId) {
            $this->ajaxReturn(3);
        } else {
            $map1['id'] = array('EQ', $changeId);
            if ($flag == 2) {
                $data = array(
                    $statusName => $trueValue
                );
            } elseif ($flag == 1) {
                $data = array(
                    $statusName => $falseValue
                );
            }
            $rst = M($table)->where($map1)->setField($data);
            $msg = $rst ? 2 : 1;
            $this->ajaxReturn($msg);
        }
    }



    /**
     * @name getBusListAudit
     * @abstract 获取客户对应时间的各个记录的数量
     * @todo 售后记录连表要进行修改
    */
    protected function getBusListAudit($k, $cusName, $map)
    {
        $ti = time();
        $time = 86400 * $k;
        return $data1 =  M($cusName)->where($map)
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
            ->select();
    }


    /**
     * @name getResList
     * @abstract 获取项目各个记录的数
     */
    protected function getResList($map, $modelName, $nTime)
    {
        return $data = M($modelName)->where($map)
            ->join('LEFT JOIN `crm_staff` AS s ON builderid = s.id')
            ->join('LEFT JOIN `crm_staff` AS a ON auditorid = a.id')
            ->join('LEFT JOIN `crm_dept` AS d ON  d.id= crm_research.projectdepartment')
            ->join('LEFT JOIN `crm_customer` AS cus ON customerid = cus.cid')
            ->field("crm_research.*,s.name AS buildname,d.name AS deptname,
                                        a.name AS auditname,cus.cname AS cusname,cus.keyword,
                                        (SELECT GROUP_CONCAT(pname) AS pname 
                                            FROM `crm_resjixiao` AS jx 
                                            WHERE jx.prjid = crm_research.proid) AS pname,
                                        (SELECT GROUP_CONCAT(jxval) AS jxval
                                            FROM `crm_resjixiao` AS jx
                                            WHERE jx.prjid = crm_research.proid) AS jxval,
                                        (SELECT IFNULL(count(*),0) FROM `crm_resprogress` AS pro 
                                            WHERE pro.project_id = crm_research.proid AND posttime > (unix_timestamp(now())-$nTime))AS num,
                                        (SELECT IFNULL(count(*),0) FROM `crm_resprogress` AS pro 
                                            WHERE pro.project_id = crm_research.proid AND pro.audistatus = '1' AND posttime > (unix_timestamp(now())-$nTime))AS unchecknum,
                                        (SELECT IFNULL(count(*),0) FROM `crm_reschange` AS cha
                                            WHERE cha.projid = crm_research.proid) AS cnum")
            ->select();
        // responname负责人姓名，auditname审核人姓名，cusname客户名称，buildname立项人姓名，keyword，客户关键字
    }

    /**
     *
    */
    protected function getCustomerInfo($cusName)
    {
        $cusKey = str_replace($this->keyFilter, "", $cusName);
        if (mb_strlen($cusKey) <= 1) {
            $indexStr = "";
        } else {
            $indexStr = $this->getSearchIndex($cusKey, SPH_MATCH_ALL, "dwin,delta", false);
        }
        return $indexStr;
    }


    protected function getOrderNumber($table){
        $model = new \Dwin\Model\MaxIdModel();
        $idArr = array();
        $maxId = $model->getMaxId($table);
        $id = getIdWithZero($maxId);
        $idArr['orderString'] = '-'.date('ymd') . $id;
        $idArr['orderId']     = $maxId;
        return $idArr;
    }


    /**
     * 根据角色检查权限
     * @param $roleArr  array   auth_role表主键序列
     * @return bool
     */
    protected function checkAuthByRole ($roleArr)
    {
        $current = session('staffId');
        $str = '';
        foreach ($roleArr as $key => $value) {
            $str .= M('auth_role') -> field('staff_ids') -> find($value)['staff_ids'] . ',';
        }
        $str = rtrim($str, ',');
        $arr = explode(',', $str);
        return in_array($current, $arr);
    }

    protected function returnAjaxMsg($msg, $status, $data =[])
    {
        $data = array(
            'msg'    => $msg,
            'status' => $status,
            'data'   => $data
        );
        $this->ajaxReturn($data);
    }

    /**
     * showOrderList页面获取订单详情方法
     */
    public function getOrderDetail()
    {
        $params = I('post.');
        $model = new \Dwin\Model\OrderformModel();
        $data = $model -> getOrderPendingDataById($params['id'], $params['cpoId'], $params['returnDataSet']);
        $planStatus = ['', '待审核', '齐料确认中', '生产中','待产线确认'];
        foreach ($data['productionPlanData'] as $key => &$value) {
            $value['create_time'] = date('Y-m-d H:i:s', $value['create_time']);
            $value['delivery_time'] = date('Y-m-d', $value['delivery_time']);
            $value['production_status'] = $planStatus[$value['production_status']];
        }
        $stockOutStatus = ['未审核', '不通过', '审核通过', '出库完毕'];
        foreach ($data['stockOutData'] as $key => &$value) {
            $value['audit_status'] = $stockOutStatus[$value['audit_status']];
        }
        $this->ajaxReturn($data);
    }

    /**
     * @param mixed $authArr 权限ID字符串，以逗号相连的职位字符串
     */
    public function isAuthToOperation($authArr){
        $roleStaffIds = self::getRoleStaffIds($authArr);
        if(!in_array($this->staffId, explode(',',$roleStaffIds))){
            return false;
        }
        return true;
    }

    /**
     * 获取当前员工的所有下属
     * @param string $roleId
     * @param array $staffArr
     * @return string
     */
    public function getStaffRoleAuth($roleId = "", $staffArr = []){
        if(empty($roleId)){
            $roleId = session("deptRoleId");
        }

        if (empty($roleId)){
            return false;
        }

        if(is_array($roleId)){
            $roleIdStr = implode(",", $roleId);
            $map['role_parent_id'] = ['in', $roleIdStr];
            $where['role_id'] = ['in', $roleIdStr];
        }else {
            $map['role_parent_id'] = ['in', $roleId];
            $where['role_id'] = ['in', $roleId];
        }


        $roleModel = new AuthRoleModel();

        // 查当前职位的员工
        $selfData = $roleModel->field("staff_ids")->where($where)->select();
        foreach ($selfData as $k => $v){
            $staffArr = array_merge($staffArr,explode(',', $v['staff_ids']));
        }

        $data = $roleModel->where($map)->select();

        if(!empty($data)){
            foreach ($data as $key => $value){
                $staffArr = array_merge($staffArr,explode(',', $value['staff_ids']));
            }
            $roleIdArr = array_column($data, 'id');
            self::getStaffRoleAuth($roleIdArr, $staffArr);
        }

        $staffIdStr = implode(',', array_filter($staffArr));
        return $staffIdStr;
    }
}
