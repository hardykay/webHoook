<?php
require_once 'HookConfig.php';
require_once 'WebHook.php';

try{
    // 获取项目名
    $name = @$_GET['project_name'];
    // 获取钩子信息
    $hookConfig = new \Hook\HookConfig($name);
    $runHook = new  \Hook\WebHook($hookConfig);
    // 初始化项目
    $runHook->gitClone();
    // 拉取代码
    $runHook->pull();
    // 数据迁移
    // 数据填充
    $runHook->migrate();
} catch (\Exception $e){
    FunTools::git_log($e->getMessage(),'报错信息','',__DIR__.'/log/Error/');
}




