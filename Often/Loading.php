<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2019-2019.
// +----------------------------------------------------------------------
// | Licensed ( https://github.com/18059662215/php_common )
// +----------------------------------------------------------------------
// | Author: 刘开明 <907635375@qq.com>
// +----------------------------------------------------------------------

namespace Often;
// 应用公共文件
/**
 * 注册SDK自动加载机制
 */
spl_autoload_register(function ($class) {
    $filename = getcwd().DIRECTORY_SEPARATOR. str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
    //print_r($filename);exit;
    file_exists($filename) && require($filename);
});
class Loading
{
    /**
     * 获取当前环境域名
     */
    static function getHttp(){
        $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
        return $http_type . $_SERVER['HTTP_HOST'];
    }

    
   
}
