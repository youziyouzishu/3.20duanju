<?php

namespace app\api\controller;

use app\api\basic\Base;
use support\Request;

class IndexController extends Base
{
    protected $noNeedLogin = ['*'];
    function index(Request $request)
    {
        return $this->success('success');
    }

}
