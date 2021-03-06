<?php
class manageController extends AdminController
{
    public function importAction()
    {
        if ($this->request->isPost()) {
            if (!empty($_FILES['file']['tmp_name'])) {
                $zym_7 = unserialize(base64_decode(explode("\n", F($_FILES['file']['tmp_name']))['4']));
				
                if ($_POST['site']) {
                    $zym_6 = (new NovelSearch_SiteModel())->add(['name' => $zym_7['sitename'], 'url' => $zym_7['siteurl'], 'key' => $zym_7['sitekey'],'isoriginal'=>'2']);
                    if (!$zym_6) {
                        $this->error('无法添加新站点');
                    }
                    $zym_7['siteid'] = $zym_6;
                } else {
                    $zym_7['siteid'] = $_POST['siteid'];
                }
				
                if ($_POST['rule']) {
                    $zym_7['addnew'] = (int) isset($_POST['addnew']);
                    $zym_7['newreplace'] = (int) isset($_POST['newreplace']);
                    $zym_7['collectallchapter'] = (int) isset($_POST['collectallchapter']);
					
                    (new RuledModel())->add($zym_7);
                } else {
                    (new RuledModel())->edit($zym_7, ['id' => $_POST['ruleid']]);
                }
                $this->success('导入成功');
            } else {
                $this->error('规则文件读取失败');
            }
        } else {
            $this->rulelist = $this->model('rule')->field('id,name')->order('id asc')->select();
            $this->sitelist = $this->model('novelsearch_site')->where(['status' => 1])->field('id,name')->order('id asc')->select();
            $this->display();
        }
    }
    public function exportAction()
    {
        if ($this->request->isPost()) {
            $zym_5 = $this->input->post('ruleid', 'int', 0);
            if (!$zym_5) {
                $this->error('请选择规则');
            }
            (new ruledModel())->export($zym_5);
        } else {
            $this->rulelist = $this->model('ruled')->field('id,name')->order('id asc')->select();
            $this->display();
        }
    }
}
?>