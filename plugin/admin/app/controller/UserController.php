<?php

namespace plugin\admin\app\controller;

use app\admin\model\RechargeOrders;
use app\admin\model\UsersWithdraw;
use app\admin\model\VipOrders;
use plugin\admin\app\model\User;
use support\exception\BusinessException;
use support\Request;
use support\Response;
use Throwable;

/**
 * 用户管理 
 */
class UserController extends Crud
{
    
    /**
     * @var User
     */
    protected $model = null;

    /**
     * 构造函数
     * @return void
     */
    public function __construct()
    {
        $this->model = new User;
    }

    /**
     * 浏览
     * @return Response
     * @throws Throwable
     */
    public function index(): Response
    {
        $profit_amount = RechargeOrders::where(['status'=>1])->sum('pay_amount') + VipOrders::where(['status'=>1])->sum('pay_amount') - UsersWithdraw::where(['status'=>2])->sum('into_amount');
        $data = [
            'profit_amount' => $profit_amount,
        ];
        return view('user/index',$data);
    }

    /**
     * 插入
     * @param Request $request
     * @return Response
     * @throws BusinessException|Throwable
     */
    public function insert(Request $request): Response
    {
        if ($request->method() === 'POST') {
            return parent::insert($request);
        }
        return raw_view('user/insert');
    }

    /**
     * 更新
     * @param Request $request
     * @return Response
     * @throws BusinessException|Throwable
     */
    public function update(Request $request): Response
    {
        if ($request->method() === 'POST') {
            $param = $request->post();
            $user = $this->model->find($param['id']);
            if ($user->money != $param['money']){
                //变了账户
                $difference = $param['money'] - $user->money;
                \app\admin\model\User::score($difference, $user->id, $difference>0?'管理员增加':'管理员扣除','money');
            }

            return parent::update($request);
        }
        return raw_view('user/update');
    }

}
