<?php

namespace app\api\controller;

use app\admin\model\User;
use app\admin\model\Vip;
use app\admin\model\VipOrders;
use app\api\basic\Base;
use app\api\service\Pay;
use plugin\admin\app\common\Util;
use support\Request;

class VipController extends Base
{
    #创建订单
    function createOrder(Request $request)
    {
        $pay_type = $request->post('pay_type');#支付方式:1=微信,2=支付宝
        $vip_id = $request->post('vip_id');
        if (!$vip_id) {
            return $this->fail('请选择会员');
        }
        $vip = Vip::find($vip_id);
        if (!$vip) {
            return $this->fail('会员不存在');
        }
        $user = User::find($request->user_id);
        if (!$user) {
            return $this->fail('用户不存在');
        }
        $ordersn = Util::generateOrdersn();
        $order = VipOrders::create([
            'vip_id'=>$vip->id,
            'user_id'=>$request->user_id,
            'pay_type'=>$pay_type,
            'ordersn'=>$ordersn,
            'pay_amount'=>$vip->price,
        ]);
        try {
            $result = Pay::pay($pay_type,$order->pay_amount,$order->ordersn,'开通会员','vip',$user->mini_open_id);
        }catch (\Throwable $e){
            return $this->fail($e->getMessage());
        }
        return $this->success('成功',$result);
    }
}
