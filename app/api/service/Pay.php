<?php

namespace app\api\service;

use support\exception\BusinessException;
use Yansongda\Artful\Rocket;
use Yansongda\Supports\Collection;

class Pay
{
    /**
     * 支付
     * @param $pay_type *支付类型:1=微信,2=支付宝
     * @param  $pay_amount
     * @param  $order_no
     * @param $mark
     * @param $attach
     * @param string $openid
     * @return string|Rocket|Collection
     * @throws \Exception
     */
    public static function pay($pay_type, $pay_amount, $order_no, $mark, $attach, string $openid='')
    {
        $config = config('payment');
        try {
            if ($pay_type == 1){
                if (request()->client_type == 'app'){
                    $result = \Yansongda\Pay\Pay::wechat($config)->app([
                        'out_trade_no' => $order_no,
                        'description' => $mark,
                        'amount' => [
                            'total' => function_exists('bcmul') ? (int)bcmul($pay_amount, 100, 2) : $pay_amount * 100,
                            'currency' => 'CNY',
                        ],
                        'attach' => $attach
                    ]);
                }else{
                    if (empty($openid)){
                        throw new \Exception('请先绑定微信小程序');
                    }
                    $result = \Yansongda\Pay\Pay::wechat()->mini([
                        'out_trade_no' => $order_no,
                        'description' => $mark,
                        'amount' => [
                            'total' => function_exists('bcmul') ? (int)bcmul($pay_amount, 100, 2) : $pay_amount * 100,
                            'currency' => 'CNY',
                        ],
                        'payer' => [
                            'openid' => $openid,
                        ],
                        'attach' => $attach
                    ]);
                }

            }else{
                if (request()->client_type == 'app'){
                    $result = \Yansongda\Pay\Pay::alipay($config)->app([
                        'out_trade_no' => $order_no,
                        'total_amount' => $pay_amount,
                        'subject' => $mark,
                        'passback_params' => urlencode($attach)
                    ])->getBody()->getContents();
                }else{
                    throw new \Exception('微信小程序不支持支付宝支付');
                }

            }
            return $result;
        }catch (\Throwable $e){
            throw new \Exception($e->getMessage());
        }

    }

    #退款
    public static function refund($pay_type, $pay_amount, $order_no, $refund_order_no, $reason)
    {
        $config = config('payment');
        return match ($pay_type) {
            1 => \Yansongda\Pay\Pay::wechat($config)->refund([
                'out_trade_no' => $order_no,
                'out_refund_no' => $refund_order_no,
                'amount' => [
                    'refund' => (int)bcmul($pay_amount, 100, 2),
                    'total' => (int)bcmul($pay_amount, 100, 2),
                    'currency' => 'CNY',
                ],
                'reason' => $reason
            ]),
            default => throw new \Exception('支付类型错误'),
        };
    }
}