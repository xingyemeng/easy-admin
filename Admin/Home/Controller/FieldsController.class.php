<?php
namespace Home\Controller;

/**
 * FieldsController
 * 字段管理
 */
class FieldsController extends CommonController {
    /**
     * 字段列表
     * @return
     */
    public function index(){
        $this->display();
    }

    /**
     * 字段信息
     * @return
     */
    public function show() {
        $this->display();
    }    

    /**
     * 添加字段
     * @return
     */
    public function add() {
        if (!isset($_GET['model_id'])) {
            return $this->error('您需要添加字段的模型不存在');
        }

        // 得到可选的关联模型
        $models = D('Model', 'Service')->getAll();

        // 得到所有可用的filter和fill函数
        $filters = get_registry_filter();
        $fills = get_registry_fill();

        $model = M('Model')->getById($_GET['model_id']);
        if (empty($model)) {
            return $this->error('您需要添加字段的模型不存在');
        }

        $this->assign('models', $models);
        $this->assign('model', $model);
        $this->assign('filters', $filters);
        $this->assign('fills', $fills);
        $this->display();
    }

    /**
     * 创建字段
     * @return
     */
    public function create() {
        if (!IS_POST || !isset($_POST['field']) || !isset($_POST['input'])) {
            return $this->errorReturn('无效的操作');
        }

        $fieldService = D('Field', 'Service');
        $inputService = D('input', 'Service');
        $field = $_POST['field'];
        $input = $_POST['input'];

        if (!D('Model', 'Service')->existModel($field['model_id'])) {
            return $this->error('您需要添加字段的模型不存在');
        }

        // field
        $result = $fieldService->checkField($field);
        if (!$result['status']) {
            return $this->errorReturn($result['data']['error']);
        }

        // input
        $input['label'] = $field['comment'];
        $input = $inputService->create($input, $field);
        $result = $inputService->checkInput($input);
        if (!$result['status']) {
            return $this->errorReturn($result['data']['error']);
        }

        // 插入field
        $result = $fieldService->add($field);
        if (!$result['status']) {
            return $this->errorReturn('系统出错了!');
        }

        // 插入input
        $input['field_id'] = $result['data']['id'];
        $result = $inputService->add($input);
        if (!$result['status']) {
            return $this->errorReturn('系统出错了!');
        }

        $url = U('Models/show', array('id' => $field['model_id']));
        return $this->successReturn('字段添加成功!', $url);
    }

    /**
     * 检查字段名称可用性
     * @return
     */
    public function checkFieldName() {
        $result = D('Field', 'Service')->checkFieldName($_GET['field_name'],
                                                        $_GET['model_id'],
                                                        $_GET['field_id']);
        if ($result['status']) {
            return $this->successReturn('字段名称可用');
        }

        return $this->errorReturn($result['data']['error']);
    }

    /**
     * 检查字段标签可用性
     * @return
     */
    public function checkFieldLabel() {
        $result = D('Field', 'Service')
                   ->checkFieldComment($_GET['field_label'],
                                       $_GET['model_id'],
                                       $_GET['field_id']);
        if ($result['status']) {
            return $this->successReturn('字段标签可用');
        }

        return $this->errorReturn($result['data']['error']);    
    }

    /**
     * 编辑字段
     * @return
     */
    public function edit() {
        if (!isset($_GET['model_id'])
            || !isset($_GET['field_id'])
            || !D('Model', 'Service')->existModel($_GET['model_id'])
            || !D('Field', 'Service')->existField($_GET['field_id'])) {
            return $this->error('您需要编辑的字段不存在');
        }

        $model = M('Model')->getById($_GET['model_id']);
        $field = D('Field')->relation(true)->getById($_GET['field_id']);
        $input = $field['input'];
        $models = D('Model', 'Service')->getAll();
        $filters = get_registry_filter();
        $fills = get_registry_fill();

        D('Field', 'Logic')->resetLength($field);

        $this->assign('models', $models);
        $this->assign('model', $model);
        $this->assign('field', $field);
        $this->assign('input', $input);
        $this->assign('filters', $filters);
        $this->assign('fills', $fills);
        $this->display();
    }

    /**
     * 更新字段
     * @return
     */
    public function update() {
        if (!IS_POST || !isset($_POST['field']) || !isset($_POST['input'])) {
            // return $this->errorReturn('无效的操作');
        }

        $fieldService = D('Field', 'Service');
        $inputService = D('input', 'Service');
        $field = $_POST['field'];
        $input = $_POST['input'];

        if (!D('Model', 'Service')->existModel($field['model_id'])) {
            var_dump('您需要修改字段的模型不存在');
            // return $this->error('您需要修改字段的模型不存在');
        }

       if (!$fieldService->existField($field['id'])) {
           var_dump('您需要修改的字段不存在');
       }

        // field
        $result = $fieldService->checkField($field, $field['id']);
        if (!$result['status']) {
            var_dump($result['data']['error']);
            // return $this->errorReturn($result['data']['error']);
        }

        // input
        $input['label'] = $field['comment'];
        $input = $inputService->create($input, $field);
        $result = $inputService->checkInput($input);
        if (!$result['status']) {
            var_dump($result['data']['error']);
            // return $this->errorReturn($result['data']['error']);
        }

        // 插入field
        $result = $fieldService->update($field);
        if (!$result['status']) {
            return $this->errorReturn('系统出错了!');
        }

        // 插入input
        // $input['field_id'] = $result['data']['id'];
        // $result = $inputService->add($input);
        // if (!$result['status']) {
        //     return $this->errorReturn('系统出错了!');
        // }

        // $url = U('Models/show', array('id' => $field['model_id']));
        // return $this->successReturn('字段更新成功!', $url);
    }
}
