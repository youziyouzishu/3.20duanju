<?php

namespace app\api\controller;

use app\admin\model\RechargeOrders;
use app\admin\model\User;
use app\admin\model\UsersScoreLog;
use app\admin\model\UsersWithdraw;
use app\api\basic\Base;
use app\api\service\Pay;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use plugin\admin\app\common\Util;
use support\Request;

class MoneyController extends Base
{
    function recharge(Request $request)
    {
        $amount = $request->post('amount');
        if (empty($amount)||$amount < 1) {
            return $this->fail('充值金额不能小于1');
        }
        $pay_type = $request->post('pay_type');#支付方式:1=微信,2=支付宝
        $ordersn = Util::generateOrdersn();
        RechargeOrders::create([
            'user_id' => $request->user_id,
            'pay_type' => $pay_type,
            'ordersn' => $ordersn,
            'pay_amount' => $amount,
        ]);

        try {
            $result = Pay::pay($pay_type, $amount, $ordersn, '充值金币', 'recharge');
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage());
        }
        return $this->success('成功', $result);
    }

    function getMoneyLog(Request $request)
    {
        $type = $request->post('type');#money = 金币
        $date = $request->post('date');
        $status = $request->post('status'); #0=全部 1=支出，2=收入
        $date = Carbon::parse($date);
        // 提取年份和月份
        $year = $date->year;
        $month = $date->month;
        $rows = UsersScoreLog::where(['type' => $type])
            ->when(!empty($status), function (Builder $query) use ($status) {
                if ($status == 1) {
                    $query->where('score', '<', 0);
                } else {
                    $query->where('score', '>', 0);
                }
            })
            ->whereYear('created_at',$year)
            ->whereMonth('created_at',$month)
            ->where('user_id', $request->user_id)
            ->orderByDesc('id')
            ->paginate()
            ->getCollection()
            ->each(function (UsersScoreLog $item) {
                if ($item->score > 0) {
                    $item->score = '+' . $item->score;
                }
            });
        return $this->success('获取成功', $rows);
    }

    #提现
    function doWithdraw(Request $request)
    {
        $withdraw_amount = $request->post('withdraw_amount');
        $ali_name = $request->post('ali_name');
        $ali_account = $request->post('ali_account');
        $user = User::find($request->user_id);
        if ($user->money < $withdraw_amount) {
            return $this->fail('余额不足');
        }
        $chance_rate = 0.1;
        $chance_amount = $withdraw_amount * $chance_rate;
        $into_amount = $withdraw_amount - $chance_amount;
        User::score(-$withdraw_amount, $request->user_id, '用户提现', 'money');
        UsersWithdraw::create([
            'user_id' => $request->user_id,
            'withdraw_amount' => $withdraw_amount,
            'chance_amount' => $chance_amount,
            'into_amount'=> $into_amount,
            'ali_name' => $ali_name,
            'ali_account' => $ali_account,
            'chance_rate'=> $chance_rate,
        ]);
        return $this->success('提交成功');
    }

    #获取提现记录
    function getWithdrawList(Request $request)
    {
        $rows = UsersWithdraw::where('user_id', $request->user_id)
            ->orderByDesc('id')
            ->paginate()
            ->items();
        return $this->success('获取成功', $rows);
    }

}
