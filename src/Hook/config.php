<?php

/*
 * 格式
 * '项目名称' =>[
 *      'rootPath'=> 保存根目录
 *      'branch' => 更新的分支,选填，默认值为master
 *      'logPath' => '日志保存的路径'
 *      'password' => '密码，选填'
 *      'project_name' => '选填、项目'
 * ]
 * 注意，所有路径最后一个字符不能是“/”
 */
return [
    'logPath' => '/var/www/',
    'project'=> [
        'default' => [ // 默认值是没有project_name的
            'rootPath' => '/var/www/',
            'branch'   => 'master',
            'password'  => '123456',
            // dev 代表开发阶段，会执行数据迁移和数据填充
            // pro 代表生产阶段，只会执行数据迁移
            // test // 测试环境
            'type'  => 'dev',
        ],
        'gitee' => [
            'rootPath' => 'C:/wamp64/',
            'branch'   => 'master',
            'password'  => '123456',
        ],
        'think5api' => [ // 默认值是没有project_name的
            'rootPath' => 'C:/wamp64/www/',
            'branch'   => 'master',
            'password'  => '123456',
            // dev 代表开发阶段，会执行数据迁移和数据填充
            // pro 代表生产阶段，只会执行数据迁移
            // test // 测试环境
            'type'  => 'dev',
        ],
    ],
];
