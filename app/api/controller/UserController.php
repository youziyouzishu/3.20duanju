<?php

namespace app\api\controller;

use app\admin\model\Advice;
use app\admin\model\Classify;
use app\admin\model\Novel;
use app\admin\model\NovelOrders;
use app\admin\model\Playlet;
use app\admin\model\PlayletOrders;
use app\admin\model\RechargeOrders;
use app\admin\model\Sms;
use app\admin\model\User;
use app\admin\model\UsersBookrack;
use app\admin\model\UsersClass;
use app\admin\model\UsersPlayletLike;
use app\admin\model\UsersPlayletLog;
use app\admin\model\UsersReadLog;
use app\admin\model\UsersScoreLog;
use app\admin\model\UsersWithdraw;
use app\api\basic\Base;
use Carbon\Carbon;
use EasyWeChat\OpenPlatform\Application;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use plugin\admin\app\common\Util;
use support\Db;
use support\exception\BusinessException;
use support\Log;
use support\Request;
use support\Response;
use Tinywan\Jwt\JwtToken;
use Tinywan\Validate\Facade\Validate;

class UserController extends Base
{
    protected $noNeedLogin = ['login', 'register'];

    function login(Request $request): Response
    {
        $mobile = $request->post('mobile');
        $captcha = $request->post('captcha');
        $code = $request->post('code');
        $login_type = $request->post('login_type');# 1=密码登录 2=微信登陆 3=手机号登录
       if ($login_type == 2) {
            if (empty($code)){
                return $this->fail('请先获取code');
            }
            if ($request->client_type == 'app') {
                $config = config('wechat.OpenPlatform');
                $app = new \EasyWeChat\OpenPlatform\Application($config);
                $oauth = $app->getOauth();
                try {
                    $response = $oauth->userFromCode($code);
                } catch (\Throwable $e) {
                    return $this->fail($e->getMessage());
                }
                Log::info('微信登陆');
                Log::info(json_encode($response));
                $openid = $response->getId();
                $wechat_unionid = $response->getRaw()['unionid'];
                $user = User::where(['wechat_unionid' => $wechat_unionid])->first();
            } else {
                //小程序
                $config = config('wechat.MiniApp');
                $app = new \EasyWeChat\MiniApp\Application($config);
                $util = $app->getUtils();
                try {
                    $response = $util->codeToSession($code);
                } catch (\Throwable $e) {
                    return $this->fail($e->getMessage());
                }
                Log::info('小程序登录');
                Log::info(json_encode($response));
                $openid = $response['openid'];
                $wechat_unionid = $response['unionid'];
                $user = User::where(['wechat_unionid' => $wechat_unionid])->first();
            }
            if (!$user) {
                $user = User::create([
                    'nickname' => '用户' . Util::alnum(),
                    'avatar' => '/app/admin/avatar.png',
                    'join_time' => Carbon::now()->toDateTimeString(),
                    'join_ip' => $request->getRealIp(),
                    'invitecode' => Util::generateInvitecode(),
                    'platform_open_id' => $request->client_type == 'app' ? $openid : '',
                    'mini_open_id' => $request->client_type == 'mini' ? $openid : '',
                    'wechat_unionid' => $wechat_unionid,
                    'mobile'=>''
                ]);
            } else {
                if ($request->client_type == 'app' && empty($user->platform_open_id)) {
                    $user->platform_open_id = $openid;
                }
                if ($request->client_type == 'mini' && empty($user->mini_open_id)) {
                    $user->mini_open_id = $openid;
                }
            }
        } elseif ($login_type == 3) {
            $user = User::where(['mobile' => $mobile])->first();
            $smsResult = Sms::check($mobile, $captcha, 'login');
            if (!$smsResult) {
                return $this->fail('验证码错误');
            }
            if (!$user) {
                $user = User::create([
                    'nickname' => '用户' . Util::alnum(),
                    'avatar' => '/app/admin/avatar.png',
                    'join_time' => Carbon::now()->toDateTimeString(),
                    'join_ip' => $request->getRealIp(),
                    'invitecode' => Util::generateInvitecode(),
                    'mobile'=> $mobile,
                ]);
            }

        } else {
            return $this->fail('登陆方式错误');
        }
        $user->last_time = Carbon::now()->toDateTimeString();
        $user->last_ip = $request->getRealIp();
        $user->save();
        $token = JwtToken::generateToken([
            'id' => $user->id,
            'client' => JwtToken::TOKEN_CLIENT_MOBILE
        ]);
        return $this->success('登陆成功', ['user' => $user, 'token' => $token]);
    }

    function register(Request $request): Response
    {
        $mobile = $request->post('mobile');
        $captcha = $request->post('captcha');
        $password = $request->post('password');
        $password_confirm = $request->post('password_confirm');
        $invitecode = $request->post('invitecode');
        if ($password !== $password_confirm) {
            return $this->fail('两次密码不一致');
        }
        $captchaResult = Sms::check($mobile, $captcha, 'register');
        if (!$captchaResult) {
            return $this->fail('验证码错误');
        }

        if (!empty($invitecode) && !$parent = User::where('invitecode', $invitecode)->first()) {
            return $this->fail('邀请码不存在');
        }
        $user = User::create([
            'nickname' => '用户' . Util::alnum(),
            'avatar' => '/app/admin/avatar.png',
            'join_time' => Carbon::now()->toDateTimeString(),
            'join_ip' => $request->getRealIp(),
            'last_time' => Carbon::now()->toDateTimeString(),
            'last_ip' => $request->getRealIp(),
            'mobile' => $mobile,
            'password' => Util::passwordHash($password),
            'parent_id' => isset($parent) ? $parent->id : 0,
            'invitecode' => Util::generateInvitecode(),
        ]);



        $token = JwtToken::generateToken([
            'id' => $user->id,
            'client' => JwtToken::TOKEN_CLIENT_MOBILE
        ]);
        return $this->success('注册成功', ['user' => $user, 'token' => $token]);
    }

    #更改密码
    function changePassword(Request $request)
    {
        $password = $request->post('password');
        $password_confirm = $request->post('password_confirm');
        $mobile = $request->post('mobile');
        $captcha = $request->post('captcha');
        if ($password !== $password_confirm) {
            return $this->fail('两次密码不一致');
        }
        $captchaResult = Sms::check($mobile, $captcha, 'changepwd');
        if (!$captchaResult) {
            return $this->fail('验证码错误');
        }
        $user = User::find($request->user_id);
        $user->password = Util::passwordHash($password);
        $user->save();
        return $this->success();
    }


    function getUserInfo(Request $request)
    {
        $user_id = $request->post('user_id');
        if (!empty($user_id)) {
            $request->user_id = $user_id;
        }
        $row = User::find($request->user_id);
        if (!$row) {
            return $this->fail('用户不存在');
        }

        if ($row->created_at->addDays(7)->isPast() || RechargeOrders::where(['user_id' => $request->user_id, 'status' => 1])->exists()) {
            $row->setAttribute('new', false);
        } else {
            $row->setAttribute('new', true);
        }
        return $this->success('成功', $row);
    }

    function editUserInfo(Request $request)
    {
        $data = $request->post();
        $row = User::find($request->user_id);
        if (!$row) {
            return $this->fail('用户不存在');
        }

        $userAttributes = $row->getAttributes();
        foreach ($data as $key => $value) {
            if (array_key_exists($key, $userAttributes) && (!empty($value) || $value === 0)) {
                $row->setAttribute($key, $value);
            }
        }
        $row->save();
        return $this->success('成功');
    }

    #获取海报
    function getPoster(Request $request)
    {
        $user = User::find($request->user_id);
        if ($request->client_type == 'mini') {
            $config = config('wechat.MiniApp');
            $app = new \EasyWeChat\MiniApp\Application($config);
            $data = [
                'scene' => '1',
                'page' => 'pages/home',
                'width' => 280,
                'check_path' => !config('app.debug'),
            ];
            $response = $app->getClient()->postJson('/wxa/getwxacodeunlimit', $data);
            $base64 = "data:image/png;base64," . base64_encode($response->getContent());
        } else {
            $writer = new PngWriter();
            $qrCode = new QrCode(
                data: 'https://longh.top/register/register.html#/?invitecode=' . $user->invitecode,
                encoding: new Encoding('UTF-8'),
                errorCorrectionLevel: ErrorCorrectionLevel::Low,
                size: 100,
                margin: 10,
                roundBlockSizeMode: RoundBlockSizeMode::Margin,
                foregroundColor: new Color(0, 0, 0),
                backgroundColor: new Color(255, 255, 255)
            );
            $base64 = $writer->write($qrCode)->getDataUri();
        }
        $total_income = UsersScoreLog::where('user_id',$request->user_id)->whereIn('memo', ['开通会员返佣','充值金币返佣'])->sum('score');
        $total_withdraw = UsersWithdraw::where('user_id',$request->user_id)->whereIn('status',[0,1])->sum('withdraw_amount');
        return $this->success('获取成功', [
            'base64' => $base64,
            'invitecode' => $user->invitecode,
            'invite_count' => $user->children->count(),
            'total_income' => $total_income,
            'total_withdraw'=>$total_withdraw,
        ]);
    }


    #浏览历史
    function history(Request $request)
    {
        $type = $request->post('type');#类型  1=书籍   2=短剧
        if ($type == 1) {
            $list = UsersReadLog::with(['novel'])->where('user_id', $request->user_id)->orderBy('id', 'desc')->paginate()->items();
            foreach ($list as $item) {
                $bookrack = UsersBookrack::where('user_id', $request->user_id)->where('novel_id', $item->novel_id)->exists();
                $item->setAttribute('bookrack_status', $bookrack);
            }
        } else {
            $list = UsersPlayletLog::with(['playlet'])->where('user_id', $request->user_id)->orderBy('id', 'desc')->paginate()->items();
        }
        return $this->success('获取成功', $list);
    }

    #删除历史记录
    function deleteHistory(Request $request)
    {
        $ids = $request->post('ids');
        $type = $request->post('type');#类型  1=书籍   2=短剧
        if ($type == 1) {
            UsersReadLog::whereIn('id', $ids)->delete();
        } else {
            UsersPlayletLog::whereIn('id', $ids)->delete();
        }
        return $this->success('删除成功');
    }


    #短剧点赞记录
    function getPlayletLikeList(Request $request)
    {
        // 子查询：获取每个 playlet_id 对应的最大 id
        $subQuery = UsersPlayletLike::select('playlet_id', DB::raw('MAX(id) as max_id'))
            ->where('user_id', $request->user_id)
            ->groupBy('playlet_id');

        $rows = UsersPlayletLike::with('playlet')
            ->whereIn('id', function ($query) use ($subQuery) {
                $query->fromSub($subQuery, 'sub')
                    ->select('max_id');
            })
            ->orderBy('id', 'desc')
            ->paginate()
            ->items();
        return $this->success('获取成功', $rows);
    }

    #获取购买记录
    function getBuyList(Request $request)
    {
        $type = $request->post('type');#类型  1=书籍   2=短剧
        if ($type == 1) {
            $rows = NovelOrders::with(['novel','novelDetail'])->where(['user_id' => $request->user_id])->orderBy('id', 'desc')->paginate()->items();
        } else {
            $rows = PlayletOrders::with(['playlet','playletDetail'])->where(['user_id' => $request->user_id])->orderBy('id', 'desc')->paginate()->items();
        }
        return $this->success('获取成功', $rows);
    }

    #获取用户内容偏好
    function getUserClass(Request $request)
    {
        $data = [];
        $user_class = UsersClass::where('user_id', $request->user_id)->pluck('class_id')->toArray();
        $data['type_1'] = Classify::where('pid', '<>', 0)->where('type', 1)->get()->each(function ($item) use ($user_class) {
            if (in_array($item->id, $user_class)) {
                $item->setAttribute('exists', true);
            } else {
                $item->setAttribute('exists', false);
            }
        });
        $data['type_2'] = Classify::where('pid', '<>', 0)->where('type', 2)->get()->each(function ($item) use ($user_class) {
            if (in_array($item->id, $user_class)) {
                $item->setAttribute('exists', true);
            } else {
                $item->setAttribute('exists', false);
            }
        });
        $data['type_3'] = Classify::where('pid', '<>', 0)->where('type', 3)->get()->each(function ($item) use ($user_class) {
            if (in_array($item->id, $user_class)) {
                $item->setAttribute('exists', true);
            } else {
                $item->setAttribute('exists', false);
            }
        });
        return $this->success('获取成功', $data);
    }

    #更新用户内容偏好
    function updateUserClass(Request $request)
    {
        $class_ids = $request->post('class_ids');# array [1,2,3]
        $user = User::find($request->user_id);
        $user->class()->sync($class_ids);
        return $this->success('更新成功');
    }

    #意见反馈
    function addAdvice(Request $request)
    {
        $class_name = $request->post('class_name');
        $content = $request->post('content');
        $images = $request->post('images');

        Advice::create([
            'class_name' => $class_name,
            'content' => $content,
            'user_id' => $request->user_id,
            'images' => $images,
        ]);
        return $this->success('添加成功');
    }

    function getTeamList(Request $request)
    {
        $rows = User::where(['parent_id' => $request->user_id])->paginate()->items();
        $total_income = UsersScoreLog::where('user_id',$request->user_id)->whereIn('memo', ['开通会员返佣','充值金币返佣'])->sum('score');
        return $this->success('获取成功', ['list'=>$rows,'total_income'=>$total_income]);
    }

    #绑定微信
    function bindWechat(Request $request)
    {
        $code = $request->post('code');
        $config = config('wechat.OpenPlatform');
        $app = new Application($config);
        $oauth = $app->getOauth();
        try {
            $response = $oauth->userFromCode($code);
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage());
        }
        $user = User::find($request->user_id);
        $platform_open_id = $response->getId();
        $wechat_unionid = $response->getRaw()['unionid'];

        $users = User::where('platform_open_id', $platform_open_id)->first();
        if ($users) {
            return $this->fail('该微信已绑定其他账号');
        }
        $user->platform_open_id = $platform_open_id;
        $user->wechat_unionid = $wechat_unionid;
        $user->save();
        return $this->success('绑定成功');
    }

    #举报
    function report(Request $request)
    {
        $id = $request->post('id');
        $type = $request->post('type');
        if ($type == 1) {
            $row = Novel::find($id);
        }else{
            $row = Playlet::find($id);
        }
        $report = $row->report()->where(['user_id'=>$request->user_id])->first();
        if ($report){
            $report->updated_at = date('Y-m-d H:i:s');
            $report->save();
        }else{
            $row->report()->create([
                'user_id'=>$request->user_id,
            ]);
        }
        return $this->success('举报成功');
    }

    #绑定手机号
    function bindMobile(Request $request)
    {
        $mobile = $request->post('mobile');
        $captcha = $request->post('captcha');
        if (!$mobile || !Validate::checkRule($mobile, 'mobile')) {
            return $this->fail('手机号不正确');
        }
        $smsResult = Sms::check($mobile, $captcha, 'changemobile');
        if (!$smsResult) {
            return $this->fail('验证码不正确');
        }
        $user = User::where('mobile', $mobile)->first();
        if ($user) {
            return $this->fail('手机号已被绑定');
        }
        $user = User::find($request->user_id);
        $user->mobile = $mobile;
        $user->save();
        return $this->success();
    }

    function bindWechatMobile(Request $request)
    {
        $code = $request->post('code');
        //小程序
        $config = config('wechat.MiniApp');
        $app = new \EasyWeChat\MiniApp\Application($config);
        $api = $app->getClient();
        $ret = $api->postJson('/wxa/business/getuserphonenumber', [
            'code' => $code
        ]);
        $ret = json_decode($ret);
        if ($ret->errcode != 0) {
            return $this->fail('获取手机号失败');
        }
        $mobile = $ret->phone_info->phoneNumber;
        $user = User::find($request->user_id);
        if ($row = User::where('mobile', $mobile)->first()){
            $row->mini_open_id = $user->mini_open_id;
            $row->wechat_unionid = $user->wechat_unionid;
            $row->save();
            $user->delete();
            $user = $row;
        }else{
            $user->mobile = $mobile;
            $user->save();
        }
        $token = JwtToken::generateToken([
            'id' => $user->id,
            'client' => JwtToken::TOKEN_CLIENT_MOBILE
        ]);
        return $this->success('成功',['user'=>$user,'token'=>$token]);
    }







}
