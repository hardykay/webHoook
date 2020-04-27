<?php
namespace Hook;


class HookConfig {
    // 项目保存的目录，即根目录
    public $rootPath;
    // 所在分支
    public $branch = 'master';
    // 日志保存地址
    public $logPath;
    // 项目名称
    public $project_name;
    // 配置
    public $config;
    // 钩子传过来的数据
    public $webHookData;
    // git_http 地址
    public $cloneUrl;
    public $gitUrl;
    // 文件更改列表
    public $commits = [];
    // 用户名
    public $user;
    // 项目路径
    public $projectPath;

    function __construct($project_name = '')
    {
        // 先获取传过来的参数
        $this->setWebHookData();
        $this->setProjectName($project_name);
    }

    /**
     * 获取git钩子传递数据
     */
    private function setWebHookData(){
        $requestBody = file_get_contents('php://input');
        FunTools::git_log($requestBody,'post');
        if (empty($requestBody)) {
            die('send fail');
        }
        $this->webHookData = json_decode($requestBody, true);
        // 空值直接退出
        if (empty($this->webHookData)){
            die('send fail');
        }
        // 判断是否是提交代码
        if (!array_key_exists('ref', $this->webHookData)){
            die('web hook test');
        }
        // 判断是否是提交代码
        if (array_key_exists('user_name', $this->webHookData) && $this->webHookData['user_name'] == 'Gitee'){
            die('Gitee send');
        }
        $this->gitUrl = $this->webHookData['project']['git_url'];
        $this->cloneUrl = $this->webHookData['project']['clone_url'];
        // 合并所有修改的文件用来判断是否执行数据迁移
        foreach ($this->webHookData['commits'] as &$item) {
            $this->commits = array_merge($this->commits,$item['modified'],$item['removed'],$item['added']);
        }
        $this->user = $this->webHookData['user'];
    }

    /**
     * 设置配置信息
     */
    private function setConfig(){
        $config = include(__DIR__.'/config.php');
        $this->config = $config['project'][$this->project_name];
        if (empty($this->config)){
            $this->config = $config['project']['default'];
        }
        if (!empty($this->config['password']) && $this->config['password'] != $this->webHookData['password']) {
            die('密码错误');
        }
        $this->setBranch();
        $this->rootPath = $this->config['rootPath'];
        // 日志记录
        if (!array_key_exists('logPath', $config['logPath']) || empty($config['logPath']) || strpos($config['logPath'], './') === false){
            $this->logPath = __DIR__ . '/log/';
        } else {
            $this->logPath = $config['logPath'] . $this->project_name. '/';
        }
        $this->projectPath = $this->rootPath.$this->project_name;
    }

    /**
     * 设置项目名称
     * @param $project_name
     */
    private function setProjectName($project_name){
        if (empty($project_name)){
            $this->project_name = $this->webHookData['repository']['path'];
        } else {
            $this->project_name = $project_name;
        }
        $this->setConfig();
    }

    /**
     * 设置拉取代码的分支
     */
    private function setBranch(){
        if (empty($this->config['branch'])){
            $this->branch = 'master';
        } else {
            $this->branch = $this->config['branch'];
        }
    }
}
