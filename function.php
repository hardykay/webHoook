<?php
/**
 * 获取git config global
 * @param null $dir
 * @return array
 */
function get_git_config($dir=null){
    if (empty($dir)){
        $res = shell_exec("git config --list");
    } else {
        $res = shell_exec("cd {$dir} && git config --list");
    }
    if (empty($res)){
        $data = [];
    } else {
        $res = explode(PHP_EOL, $res);
        $data = [];
        foreach ($res as $item){
            if (empty($item)){
                continue;
            }
            $arr = explode('=',$item);
            $data[$arr[0]] = $arr[1];
        }
    }
    return $data;
}
/**
 * 设置git 全局变量
 * @param $name 变量名
 * @param $value    变量值
 * @return string|null
 */
function set_git_config_global($name, $value){
    return shell_exec("git config --global {$name} \"{$value}\"");
}

/**
 * 日志记录
 * @param $res_log
 * @param string $message
 * @param string $username
 * @param null $logPath
 */
function git_log($res_log, $message='未知', $username = '未知', $logPath=null){
    $res = PHP_EOL . "log start --------" . PHP_EOL;
    $logFile = date('Y-m-d-');
    switch ($message){
        case 'seed':
            $logFile .= 'seed.txt';
            $res .= "explain：php think seed". PHP_EOL;
            break;
        case 'migrate':
            $logFile .= 'migrate.txt';
            $res .= "explain：php think migrate:run". PHP_EOL;
            break;
        case 'rollback':
            $logFile .= 'migrate.txt';
            $res .= "explain：php think migrate:rollback". PHP_EOL;
            break;
        case 'noPull':
            $logFile .= 'no-pull.txt';
            $res .= "explain：no pull". PHP_EOL;
            break;
        default:
            $logFile .= 'log.txt';
            $res .= "explain：".$message. PHP_EOL;
    }
    $res .= "runtime：". date('Y-m-d H:i:s') . PHP_EOL;
    $res .= "username：". $username . PHP_EOL;
    $res .=  "info:".$res_log.PHP_EOL;
    $res .=  "log end --------".PHP_EOL;
    if (empty($logPath)){
        $logPath = __DIR__.'/log/'.date('Y-m').'/';
    } else {
        $logPath =$logPath.date('Y-m').'/';
    }

    if (!is_dir($logPath)){
        mkdir($logPath, 0777, true);
    }
    file_put_contents($logPath.$logFile, $res, FILE_APPEND);//写入日志到log文件中
}
