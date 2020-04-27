<?php
namespace Hook;

require_once 'function.php';
/**
 * git钩子执行类
 * Class WebHook
 * @package Hook
 */
class WebHook {
    private $isClone = false;
    // 本地git的配置信息
    public $gitConfigList;
    // 钩子配置类
    public $hook;
    function __construct(HookConfig $hook)
    {
        $this->hook = $hook;
        // 创建工作空间
        if ($this->hook->rootPath && !is_dir($this->hook->rootPath)){
            mkdir($this->hook->rootPath, '0777', true);
        }
    }

    /**
     * 克隆
     */
    public function gitClone(){
        // 设置邮箱和用户名
        $this->setGitUser();
        if (!is_dir($this->hook->projectPath)){
            $res = shell_exec("git clone -b {$this->hook->branch} {$this->hook->cloneUrl} {$this->hook->projectPath}");
            $this->log($res, 'git clone');
            $this->isClone = true;
        }
        // 获取配置信息备用
        $this->gitConfigList = FunTools::get_git_config();
    }
    /**
     * 设置git config global
     */
    private function setGitUser(){
        $data = FunTools::get_git_config();
        if (!array_key_exists('user.name', $data) || empty($data['user.name']) || !array_key_exists('user.email', $data) || empty($data['user.email'])){
            // 用户名
            FunTools::set_git_config_global('user.name', $this->hook->user['user_name']);
            // 邮箱
            FunTools::set_git_config_global('user.name', $this->hook->user['email']);
        }
    }
    /**
     * 拉取代码
     */
    public function pull(){
        if (!$this->getStatus()){
            $this->log('','noPull');
            return false;
        }
        $res = shell_exec("cd {$this->hook->projectPath} && git pull");//拉去代码
        $this->log($res, 'git pull');
    }

    /**
     * 项目状态
     * @return bool
     */
    private function  getStatus(){
        
        if ($this->isClone){
            return false;
        }
        if ($this->hook->webHookData['ref'] != 'refs/heads/'.$this->hook->branch){
            return false;
        }
        // 是否有代码更新
        if ($this->hook->webHookData['total_commits_count'] <= 0){
            return false;
        }
        return true;
    }

    /**
     * 数据迁移
     */
    public function migrate(){
        if (!$this->getStatus()){
            return false;
        }
        // 是否执行数据迁移
        if (!$this->existFile('/database/migrations')){
            return false;
        }
        // 是否有文件修改
        $flag = false;
        foreach ($this->hook->commits as &$item){
            if(strpos($item, '/database/migrations') === false){
                $flag = true;
            }
        }
        if (!$flag){
            return false;
        }
        // 生成/测试环境
        if ($this->hook->config['type'] == 'pro' || $this->hook->config['type'] == 'test'){
            $this->migrateRun();
        }
        // 开发环境
        if ($this->hook->config['type'] == 'dev'){
            $this->migrateRollback();
            $this->migrateRun();
            $this->seed();
        }
    }
    private function existFile($path){
        $path = $this->hook->projectPath.$path;
        if (!is_dir($path)){
            return false;
        }
        $name=opendir($path);
        $flag = false;
        while($file=readdir($name)){
            if($file=="." || $file==".."){
                continue;
            } else {
                $flag = true;
                break;
            }
        }
        closedir($name);
        return $flag;
    }

    /**
     * 执行数据迁移
     */
    private function migrateRun(){
        $res = shell_exec("cd {$this->hook->projectPath} && php think migrate:run");//拉去代码
        $this->log($res, 'php migrate:run');
    }
    /**
     *执行数据回滚
     */
    private function migrateRollback(){
        $log = '';
        $i = 0;
        do{
            $res = shell_exec("cd {$this->hook->projectPath} && php think migrate:rollback");
            if (empty($res)){
                break;
            }
            $log .= $res;
            $arr = explode(PHP_EOL, $res);
            $i++;
        }while($arr[0] != 'No migrations to rollback' && $i < 100);
        $this->log($log, 'php migrate:rollback');
    }
    /**
     * 数据填充
     */
    private function seed(){
        if (!$this->existFile('/database/seeds')){
            return false;
        }
        $res = shell_exec("cd {$this->hook->projectPath} && php think seed:run");//拉去代码
        $this->log($res, 'php seed');
    }
    public function log($res_log, $message='未知'){
        FunTools::git_log($res_log, $message, $this->hook->user['user_name'], $this->hook->logPath);
    }
}