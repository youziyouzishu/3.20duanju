<?php

namespace app\admin\controller;

use support\Request;
use support\Response;
use app\admin\model\SystemNotice;
use plugin\admin\app\controller\Crud;
use support\exception\BusinessException;

/**
 * 通知管理 
 */
class SystemNoticeController extends Crud
{
    
    /**
     * @var SystemNotice
     */
    protected $model = null;

    /**
     * 构造函数
     * @return void
     */
    public function __construct()
    {
        $this->model = new SystemNotice;
    }
    
    /**
     * 浏览
     * @return Response
     */
    public function index(): Response
    {
        return view('system-notice/index');
    }

    /**
     * 查询
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function select(Request $request): Response
    {
        [$where, $format, $limit, $field, $order] = $this->selectInput($request);
        $query = $this->doSelect($where, $field, $order)->with(['noticeable']);
        return $this->doFormat($query, $format, $limit);
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
            $noticeable_id = $request->post('noticeable_id');
            if (empty($noticeable_id)) {
                $request->setParams('post',[
                    'noticeable_id'=>0
                ]);
            }
            return parent::insert($request);
        }
        return view('system-notice/insert');
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
            $noticeable_id = $request->post('noticeable_id');
            if (empty($noticeable_id)) {
                $request->setParams('post',[
                    'noticeable_id'=>0
                ]);
            }
            return parent::update($request);
        }
        return view('system-notice/update');
    }

}
