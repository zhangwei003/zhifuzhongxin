<?php
/**
 * Created by PhpStorm.
 * User: zhangxiaohei
 * Date: 2019/12/2
 * Time: 21:07
 */

namespace app\ms\controller;


use app\common\controller\Common;
use app\common\library\CryptAes;
use app\common\library\enum\CodeEnum;
use app\common\model\Ms;
use app\common\model\MsWhiteIp;
use app\common\model\UserModel;
use think\Controller;
use think\Request;

//基类
class Base extends Common
{

    protected $agent;
    protected $agent_id;
    protected $children = [];

    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
        $agent_id = intval(session('agent_id'));
        if (false == $this->gateVisiteIp($agent_id)) {
//           $this->error('当前ip访问受限,禁止登陆', url('Login/index'));
        }
        if (!$agent_id) {
            $this->redirect(url('Login/index'));
        }
        $user = Ms::where(['userid' => $agent_id])->find();

        if (!$user) {
            $this->redirect(url('Login/index'));
        }
        if ($user['status'] != '1') {
            $this->error('当前账号被冻结,禁止登陆', url('Login/index'));
        }

        //判断是否设置安全码 没有设置的话 跳转到修改安全码页面
//        $request = Request::instance();
        //      $action = Request::instance()->controller() . DS . $request->action();
//        if (!$user['security_pwd'] && $action != 'User/editsafety' && $action != 'Index/logut') {
//            $this->redirect('User/editSafety');
//        }

        $this->agent    = $user;
        $this->agent_id = $user['userid'];
        $this->assign('agent', $this->agent);
//        $request = Request::instance();
        //    $this->assign('request', $request);
    }


    /**
     * 判断码商访问ip
     * @param $msId
     */
    protected function gateVisiteIp($msId)
    {
        $MsWhiteIp    = new MsWhiteIp();
        $map['ms_id'] = $msId;
        if ($MsWhiteIp->where($map)->count()) {
            //如果有设置访问白名单
            $aesKey            = config('aes_key', 'kqwwFRmKyloO');
            $aes               = new CryptAes($aesKey);
            $msWhiteIp         = new MsWhiteIp;
            $encrypt_ip        = $aes->encrypt(clientIp());
            $map['encrypt_ip'] = $encrypt_ip;
            if ($MsWhiteIp->where($map)->count() == 0) {
                //不在白名单内
                return false;
            }
        }
        return true;
    }

}
