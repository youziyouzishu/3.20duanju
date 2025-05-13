<?php

namespace app\admin\controller;

use support\Request;
use support\Response;
use app\admin\model\NovelClass;
use plugin\admin\app\controller\Crud;
use support\exception\BusinessException;

/**
 * 小说分类 
 */
class NovelClassController extends Crud
{
    
    /**
     * @var NovelClass
     */
    protected $model = null;

    /**
     * 构造函数
     * @return void
     */
    public function __construct()
    {
        $this->model = new NovelClass;
    }
    
    /**
     * 浏览
     * @return Response
     */
    public function index(): Response
    {
        return view('novel-class/index');
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
        return view('novel-class/insert');
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
        return view('novel-class/update');
    }

}
