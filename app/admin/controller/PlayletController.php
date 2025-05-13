<?php

namespace app\admin\controller;

use support\Request;
use support\Response;
use app\admin\model\Playlet;
use plugin\admin\app\controller\Crud;
use support\exception\BusinessException;

/**
 * 短剧管理 
 */
class PlayletController extends Crud
{
    
    /**
     * @var Playlet
     */
    protected $model = null;

    /**
     * 构造函数
     * @return void
     */
    public function __construct()
    {
        $this->model = new Playlet;
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
        $query = $this->doSelect($where, $field, $order)->with(['class','tags']);
        return $this->doFormat($query, $format, $limit);
    }


    /**
     * 浏览
     * @return Response
     */
    public function index(): Response
    {
        return view('playlet/index');
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
            $data = $this->insertInput($request);
            $id = $this->doInsert($data);
            $tags = $request->post('tags');
            $tags = explode(',', $tags);
            if (is_array($tags) && !empty($tags)) {
                $this->model->find($id)->tags()->sync($tags);
            }
            return $this->json(0, 'ok', ['id' => $id]);
        }
        return view('playlet/insert');
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
            $id = $request->post('id');
            $status = $request->post('status');
            $row = $this->model->find($id);
            if ($row->status == 0 && $status == 1 && $row->detail->isEmpty()){
                return $this->fail('请先添加剧集');
            }
            $tags = $request->post('tags');
            $tags = explode(',', $tags);
            if (is_array($tags) && !empty($tags)) {
                $row->tags()->sync($tags);
            }
            return parent::update($request);
        }
        return view('playlet/update');
    }

}
