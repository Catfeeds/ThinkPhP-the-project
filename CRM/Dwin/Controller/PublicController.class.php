<?php

/**
 * @ Purpose:
 * 登录 退出操作类
 * @Package Name: Database
 * @Author: Maxu maxu@dwin.com.cn
 * @Tim e : 20180308
 * captcha 验证码生成
 * loginok 登录验证
 */
namespace Dwin\Controller;

use Dwin\Model\PurchaseContractModel;
use Think\Controller;
use Think\Verify;
// 登录及首页跳转功能实现
class PublicController extends Controller
{

    /*用户账户禁用常量*/
    const NO_LOGIN_AUTH = 1;
    /*用户账户锁定*/
    const LOCKED_LOGIN_AUTH = 3;
    /*用户账户状态正常*/
    const NORMAL_LOGIN_AUTH = 2;
    /* 验证码配置*/
    protected $config;
    /* 前端提交数据*/
    protected $post;

    /**
     * 登录验证验证码
     * @param int $fontsize 验证码字体大小(px)
     * @param boolean $curveFlag 验证码字体大小(px)
     * @param boolean $noiseFlag 是否添加杂点
     * @param int $height 高度
     * @param int $width 宽度
     * @param int $length 验证码位数
     * @param string $fontTtf 字体
     * @return array $config 验证码类的配置数组
    */
    protected function getCaptchaConfig($fontsize = 10, $curveFlag = false, $noiseFlag = false, $height = 38, $width = 80, $length = 4, $fontTtf = "4.ttf")
    {
        return $config =  array(

            'fontSize'  =>  $fontsize,              // 验证码字体大小(px)
            'useCurve'  =>  $curveFlag,            // 是否画混淆曲线
            'useNoise'  =>  $noiseFlag,            // 是否添加杂点
            'imageH'	=>	$height,
            'imageW'	=>	$width,
            'length'    =>  $length,               // 验证码位数
            'fontttf'   =>  $fontTtf,              // 验证码字体，不设置随机获取
        );

    }

    /**
     * 生成登录验证验证码方法
     * @return mixed 输出验证码图到前端
     */
    public function captcha()
    {
        #配置
        ob_end_clean();
        $this->config = $this->getCaptchaConfig();
        #实例化验证
        $verify = new Verify($this->config);
        #生成输出保存验证码
        $verify->entry();
    }

    /**登录验证
     * 存储session
     */
    public function loginOk()
    {
	// 实例化模型

        $model = M('staff');
        $this->post  = I('post.');
        // 验证码的验证功能实现
        $verify = new Verify();
        $rst = $verify->check($this->post['captcha']);
        // 1 验证码检测逻
        if ($rst !== false)
        {
            // 2 用户名检测逻辑
            $userMap['username'] = array('EQ', ($this->post['username']));
            $field = "id,name,pwd,errorcount,lastlogintime,loginaddr,loginstatus";

            $obj_1 = D('staff')->getOneStaffInfo($userMap, $field);

               if ($obj_1 != false) {
                $uid = $obj_1['id'];
                $filter['id'] = array("EQ", $uid);
                // 登录时间大于一天，对密码错误次数重设
                if (($_SERVER['REQUEST_TIME'] - $obj_1['lastlogintime']) > 3600 * 24) {
                    $reset['errorcount'] = 0;
                    $result = D('staff')->setStaffData($filter, $reset);
                }

                // 重新获得个人信息，对状态进行验证后验证密码
                $obj = D('staff')->getOneStaffInfo($filter, $field);
                switch ($obj['loginstatus']) {
                    case self::NO_LOGIN_AUTH :
                        // 账号永远不能使用
                        $msg = 5;break;
                    case self::NORMAL_LOGIN_AUTH :
                        // 没锁定，验证 账户是否冻结1天
                        // 判断账户是否锁定
                        if ($obj['errorcount'] >= 5 && (($_SERVER['REQUEST_TIME'] - $obj['lastlogintime']) < 3600 * 24)) {
                            // 输错了次数大于3且上次输错时间距今未超过1天
                            $errorMsg['loginstatus'] = 3;
                            D('staff')->setStaffData($filter, $errorMsg);
                            $msg = 5;
                        } else {
                            // 3 加密密码检测逻辑
                            $password = $obj['pwd'];
                            $rst = password_verify($this->post['password'], $password);

                            if ($rst == false) {
                                // 错误
                                $errorMsg['loginaddr'] = $_SERVER['REMOTE_ADDR'];
                                $errorMsg['lastlogintime'] = $_SERVER['REQUEST_TIME'];
                                $errorMsg['errorcount'] = $obj['errorcount'] + 1;
                                if ($errorMsg['errorcount'] >= 5) {
                                    $errorMsg['loginstatus'] = 3;
                                }
                                D('staff')->setStaffData($filter, $errorMsg);
                                $msg = 3;
                            } else {
                                // 密码正确
                                // 存储登录信息：addr loginTime
                                $loginMess['lastlogintime'] = $_SERVER['REQUEST_TIME'];
                                $loginMess['loginaddr'] = $_SERVER['REMOTE_ADDR'];
                                $loginMess['errorcount'] = 0;

                                D('staff')->setStaffData($filter, $loginMess);
                                // session存权限
                                // 用户名密码正确，存重要信息于session
                                $map_1['username'] = array('EQ', ($this->post['username']));
                                $field = "crm_staff.id user_id, name user_name, roleid role_id, rule_ids rules,deptid dept_id,post_id position";
                                $data = D('staff')->getOneStaffInfo($map_1, $field);
                                if ($data) {
                                    // session存储
                                    $condition['_string'] = "FIND_IN_SET({$data['user_id']},staff_ids)";
                                    $role = M('auth_role')->where($condition)->select();
                                    if (count($role) !== 0)
                                        session('deptRoleId', getPrjIds($role,'role_id'));
                                    session('staffId', $data['user_id']);
                                    session('nickname', $data['user_name']);
                                    session('roleId', $data['role_id']);
                                    session('deptId', $data['deptid']);
                                    session('postId', $data['position']);
                                    session('rId', $data['role_id']);
                                    session('userRule', $data['rules']);
                                    $msg = 2;
                                } else {
                                    $msg = 3;
                                }
                            }
                        }
                        break;
                    case self::LOCKED_LOGIN_AUTH :
                        // 上次输错时间距今未超过1天不允许登录
                        if (($_SERVER['REQUEST_TIME'] - $obj['lastlogintime']) < 3600 * 24) {
                            // 输错了次数大于3且上次输错时间距今未超过1天
                            $msg = 5;
                        } else {
                            //满足登录条件，进行验证
                            // 3 加密密码检测逻辑
                            $password = $obj['pwd'];
                            $rst = password_verify($this->post['password'], $password);

                            if ($rst == false) {
                                // 错误
                                $errorMsg['loginaddr'] = $_SERVER['REMOTE_ADDR'];
                                $errorMsg['lastlogintime'] = $_SERVER['REQUEST_TIME'];
                                $errorMsg['errorcount'] = $obj['errorcount'] + 1;


                                D('staff')->setStaffData($filter, $errorMsg);
                                $msg = 3;
                            } else {
                                // 密码正确
                                // 存储登录信息：addr loginTime
                                $loginMess['lastlogintime'] = $_SERVER['REQUEST_TIME'];

                                $loginMess['loginaddr'] = $_SERVER['REMOTE_ADDR'];
                                $loginMess['errorcount'] = 0;
                                $loginMess['loginstatus'] = 2;

                                D('staff')->setStaffData($filter, $loginMess);
                                // session存权限
                                // 用户名密码正确，存重要信息于session
                                $map_1['username'] = array('EQ', inject_filter($this->post['username']));
                                $field = "crm_staff.id user_id, name user_name, roleid role_id, rule_ids rules,deptid dept_id,post_id position";
                                $data = D('staff')->getOneStaffInfo($map_1, $field);

                                if ($data) {
                                    // session存储
                                    session('staffId', $data['user_id']);
                                    session('nickname', $data['user_name']);
                                    session('roleId', $data['role_id']);
                                    session('deptId', $data['deptid']);
                                    session('postId', $data['position']);
                                    session('rId', $data['role_id']);
                                    session('userRule', $data['rules']);
                                    $msg = 2;
                                } else {
                                    $msg = 3;
                                }
                            }
                        }
                        break;
                }
            } else {
                $msg = 3;
            }

        } else {
            // 验证码错误
           $msg = 1;
        }
        $this->ajaxReturn($msg);
    }


    /**
     * 登录页面模板跳转
    */
    public function login()
    {
        $this->display('login');
    }

    /**
     * 检查用户名上次登录时间
     *
     */

    public function checkLog()
    {
        $name  = inject_filter(I('post.name'));
        $map['username'] = array('EQ', $name);
        $field = "id,name,count,lastlogintime";
        $obj   = D('staff')->getOneStaffInfo($map, $field);
        if ($obj) {
            $msg = ((time() - $obj['lastlogintime']) <= 3600*24 && $obj['count'] >= 3) ? 1 : 2;
        } else {
            $msg = 2;
        }
        $this->ajaxReturn($msg);
    }
    public function logout()
    {
        // 删除全部的session
        session(null);
        // 跳转到登录页
        $this->redirect("Public/login");exit;
    }

    public function insertStatic()
    {
//        $data = M('orderform')->field('cus_id cid,max(order_addtime) max_order_time')->group('cid')->order('id')->select();
//        $data = M('contactrecord')->field('customerid cid,max(posttime) max_contact_time')->group('cid')->order('cid')->select();
//        $filter['max_contact_time'] = array('EXP', 'IS NULL');
//        $sda =  M('customer')->where($filter)->field('cid,addtime max_contact_time')->select();
        $orderLimit = strtotime(date("Y-m",strtotime("-3 month")));
        $map['add_time'] = array('gt', $orderLimit);
        $map['is_del'] = array('eq', 0);
        $map['check_status'] = array('in', "1,3,4");
        $data = M('orderform')->where($map)->field('cus_id cid,sum(oprice) `total_order_amount`')->group('cid')->order('id')->select();
        $count = count($data);
        for ($i = 0; $i < $count; $i++) {
            $rst[$i] = M('customer')->save($data[$i]);
        }
        dump($rst);
    }

    public function sek()
    {
        $orderLimit = strtotime(date("Y-m",strtotime("-3 month")));
        $map['add_time'] = array('gt', $orderLimit);
        $map['is_del'] = array('eq', 0);
        $map['check_status'] = array('in', "1,3,4");
        $data = M('orderform')->where($map)->field('cus_id cid,sum(oprice) `total_order_price`')->group('cid')->order('id')->select();
        $count = count($data);
        for ($i = 0; $i < $count; $i++) {
            $rst[$i] = M('customer')->save($data[$i]);
        }
        dump($rst);
    }

    public function te()
    {
        for($t=0;$t<360;$t++)
        {
            $y=2*cos($t)-cos(2*$t); //笛卡尔心形曲线函数
            $x=2*sin($t)-sin(2*$t);
            $x+=3;
            $y+=3;
            $x*=70;
            $y*=70;
            $x=round($x);
            $y=round($y);
            $str[]=$x;
            $y=$y+2*(180-$y);//图像上下翻转
            $x=$y;
            $str[]=$x;
        }
        $im=imagecreate(400,400);//创建画布400*400
        $black=imagecolorallocate($im,0,0,0);
        $red=imagecolorallocate($im,255,0,0);//设置颜色
        imagepolygon($im,$str,180,$red);
        imagestring($im,5,190,190,"whoyaf",$red);//输出字符串
        header('Content-type:image/gif');//通知浏览器输出的是gif图片
        imagegif($im);//输出图片
        imagedestroy($im);//销毁
    }

    /**
     * 获取一个合同的全部信息
     * @param $id
     */
    public function contractLoad(){
        if (IS_POST) {
            die("非法");
        } else {
            $id = I("get.id");
            $supplierContractModel = new PurchaseContractModel();
            $contractData = $supplierContractModel->getContractData($id, ['contract', 'product']);
            $url = 'http://' . $_SERVER['SERVER_NAME'] . "/Public/Admin/images/dwinlogo.png";
            $this->assign([
                'contract' => $contractData['contract'],
                'product' => $contractData['product'],
                'url'   => $url
            ]);
            $this->display('Purchase/contractLoad');
        }
    }

}
