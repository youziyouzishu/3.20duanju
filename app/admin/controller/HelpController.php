<?php

namespace app\admin\controller;

use support\Request;
use support\Response;
use app\admin\model\Help;
use plugin\admin\app\controller\Crud;
use support\exception\BusinessException;

/**
 * 帮助管理 
 */
class HelpController extends Crud
{
    
    /**
     * @var Help
     */
    protected $model = null;

    /**
     * 构造函数
     * @return void
     */
    public function __construct()
    {
        $this->model = new Help;
    }
    
    /**
     * 浏览
     * @return Response
     */
    public function index(): Response
    {
        return view('help/index');
    }

    /**
     * 插入
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function insert(Request $request): Response
    {
        if ($request->method() === 'POST') {
            return parent::insert($request);
        }
        return view('help/insert');
    }

    /**
     * 更新
     * @param Request $request
     * @return Response
     * @throws BusinessException
    */
    public function update(Request $request): Response
    {
        if ($request->method() === 'POST') {
            return parent::update($request);
        }
        return view('help/update');
    }

}
