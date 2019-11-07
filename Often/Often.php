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

class Often
{
    /**
     * 获取客户端IP地址
     * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
     * @param boolean $adv 是否进行高级模式获取（有可能被伪装）
     * @return mixed
     */
    static function get_client_ip($type = 0, $adv = false)
    {
        $type = $type ? 1 : 0;
        static $ip = NULL;
        if ($ip !== NULL) return $ip[$type];
        if ($adv) { //高级模式获取(防止伪装)
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                $pos = array_search('unknown', $arr);
                if (false !== $pos) unset($arr[$pos]);
                $ip = trim($arr[0]);
            } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } elseif (isset($_SERVER['REMOTE_ADDR'])) {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        // IP地址合法验证
        $long = sprintf("%u", ip2long($ip));
        $ip = $long ? array($ip, $long) : array('0.0.0.0', 0);
        return $ip[$type];
    }
    /**
     * 根据客户端IP地址获取用户所在地信息
     * 利用淘宝接口根据ip查询所在区域信息
     */
    static function getSiteInfo1()
    {
        $ip = self::get_client_ip();
        //利用淘宝接口根据ip查询所在区域信息
        $res = file_get_contents("http://ip.taobao.com/service/getIpInfo.php?ip=$ip");
        return $res;
    }
    /**
     * 根据客户端IP地址获取用户所在地信息
     * 利用淘宝接口根据ip查询所在区域信息
     */
    static function getSiteInfo2()
    {
        $ip = self::get_client_ip();
        //利用百度地图
        $res = file_get_contents("http://api.map.baidu.com/location/ip?ak=YWNt8VcHK7Goj1yljLlMVHnWl6ZWS26t&ip={$ip}&coor=bd09ll");
        return $res;
    }

    /**
     *图片上传（files:上传图片，$path:图片路径）
     */
    static function uploadimg($files, $path)
    {
        $picture = $files;
        $length = count(explode(".", $picture['name']));
        $picture['name'] = (explode(".", $picture['name']))[$length - 1];
        $newFilename = (string) time() . (string) rand(1000, 9999) . "." . $picture['name'];
        move_uploaded_file($picture['tmp_name'], $path . $newFilename);
        ob_clean();
        return $newFilename;
    }

    /**
     * 文件删除
     */
    static function delFlie($str)
    {
        $file = str_replace(getHttp(), $_SERVER['DOCUMENT_ROOT'], $str);
        if (file_exists($file)) {
            unlink($file);
            return true;
        } else {
            return false;
        }
    }
    /*
   * 适用于低版本的PHP获取
   * 5.0版本以上建议使用框架自带的获取方法
   * 按REST协议获取参数值
   */
    static function fun($status)
    {
        //获取
        if ($status === 'get') {
            if ($_SERVER['REQUEST_METHOD'] === "GET") {
                if ($_GET) {
                    return $_GET;
                    exit;
                }
            }
        }
        if ($status == 2) {
            if ($_SERVER['REQUEST_METHOD'] === "POST") {
                if ($_POST) {
                    return $_POST;
                    exit;
                } else {
                    $_POST = json_decode(file_get_contents("php://input"), 1);
                    return $_POST;
                    exit;
                }
            }
        }
        if ($status == 'post') {
            //全部更新
            if ($_SERVER['REQUEST_METHOD'] === "PUT") {
                $_PUT = json_decode(file_get_contents("php://input"), 1);
                return $_PUT;
                exit;
            }
        }
        if ($status == 'patch') {
            //部分更新
            if ($_SERVER['REQUEST_METHOD'] === "PATCH") {
                $_PATCH = json_decode(file_get_contents("php://input"), 1);
                return $_PATCH;
                exit;
            }
        }
        if ($status == 'delete') {
            //删除
            if ($_SERVER['REQUEST_METHOD'] === "DELETE") {
                return $_GET;
                exit;
            }
        }
        if ($status == 'all') {
            if ($_SERVER['REQUEST_METHOD'] === "GET") {
                if ($_GET) {
                    return $_GET;
                    exit;
                }
            }
            if ($_SERVER['REQUEST_METHOD'] === "POST") {
                if ($_POST) {
                    return $_POST;
                    exit;
                } else {
                    $_POST = json_decode(file_get_contents("php://input"), 1);
                    return $_POST;
                    exit;
                }
            }
            //全部更新
            if ($_SERVER['REQUEST_METHOD'] === "PUT") {
                $_PUT = json_decode(file_get_contents("php://input"), 1);
                return $_PUT;
                exit;
            }
            //部分更新
            if ($_SERVER['REQUEST_METHOD'] === "PATCH") {
                $_PATCH = json_decode(file_get_contents("php://input"), 1);
                return $_PATCH;
                exit;
            }
            //删除
            if ($_SERVER['REQUEST_METHOD'] === "DELETE") {
                return $_GET;
                exit;
            }
        }
    }

    /**
     * @param $lat1当前的经纬度
     * @param $lng1当前的经纬度
     * @param $lat2 数据库的经纬度
     * @param $lng2 数据库的经纬度
     * @return float
     * 计算距离
     */
    static function getDistance($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6367000; //approximate radius of earth in meters
        /*
          Convert these degrees to radians
          to work with the formula
        */
        $lat1 = ($lat1 * pi()) / 180;
        $lng1 = ($lng1 * pi()) / 180;
        $lat2 = ($lat2 * pi()) / 180;
        $lng2 = ($lng2 * pi()) / 180;
        /*
          Using the
          Haversine formula
          http://en.wikipedia.org/wiki/Haversine_formula
          calculate the distance
        */
        $calcLongitude = $lng2 - $lng1;
        $calcLatitude = $lat2 - $lat1;
        $stepOne = pow(sin($calcLatitude / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($calcLongitude / 2), 2);
        $stepTwo = 2 * asin(min(1, sqrt($stepOne)));
        $calculatedDistance = $earthRadius * $stepTwo;
        return round($calculatedDistance / 1000, 1);
    }

    /**
     * @param 几公里
     * @param 经纬度
     * @param 经纬度63723797
     * 计算几公里以内的最大最小经纬度
     */
    static function getDs($num, $latitude, $longitude)
    {
        $range = 180 / pi() * $num / 6372.3797;
        $linR = $range / cos($latitude * pi() / 180);
        $maxLat = round($latitude + $range, 7); //最大纬度
        $minLat = round($latitude - $range, 7); //最小纬度
        $maxLng = round($longitude + $linR, 7); //最大经度
        $minLng = round($longitude - $linR, 7); //最小经度
        return array("maxLat" => $maxLat, "minLat" => $minLat, "maxLng" => $maxLng, "minLng" => $minLng);
    }
    /**
     * 获取当前环境域名
     */
    static function getHttp()
    {
        $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
        return $http_type . $_SERVER['HTTP_HOST'];
    }

    /**
     * 判断是否拥有对应的参数并返回对应的json数据
     */
    static function msg($data, $success)
    {
        if ($data) {
            return json(array("code" => 10001, 'msg' => $success, 'data' => $data));
        } else {
            return json(array('code' => 10004, 'msg' => '查询获取到的参数不存在'));
        }
    }
    /**
     * @param $a (参数a)
     * @param $b （存在a的返回信息）
     * @param $c （不存在a的返回信息）
     * @param null $d （参数d）
     * @param null $e （参数e）
     * 判断存在回调函数
     */
    static function message($a, $b, $c, $d = null, $e = null, $ais = true, $dis = true, $eis = true)
    {
        if ($a) {
            if ($d) {
                if ($e) {
                    echo json_encode(array("errorCode" => 10001, "errorMsg" => $b, "errorArray" => $a, "errorArray2" => $d, "errorArray3" => $e));
                    exit;
                } else {
                    //存在a跟d两种情况
                    if ($ais == false && $dis == false) {
                        //a的返回消息跟b的返回消息都关闭
                        echo json_encode(array("errorCode" => 10001, "errorMsg" => $b));
                        exit;
                    } else {
                        if ($ais == true && $dis == true) {
                            echo json_encode(array("errorCode" => 10001, "errorMsg" => $b, "errorArray" => $a, "errorArray2" => $d));
                            exit;
                        }
                        if ($dis == true && $ais == false) {
                            echo json_encode(array("errorCode" => 10001, "errorMsg" => $b, "errorArray" => $d));
                            exit;
                        }
                        if ($dis == false && $ais == true) {
                            echo json_encode(array("errorCode" => 10001, "errorMsg" => $b, "errorArray" => $a));
                            exit;
                        }
                    }
                }
            } else {
                //只存在a
                if ($ais == false) {
                    //关闭a的参数传递
                    echo json_encode(array("errorCode" => 10001, "errorMsg" => $b));
                    exit;
                } else {
                    //不关闭a的参数传递
                    echo json_encode(array("errorCode" => 10001, "errorMsg" => $b, "errorArray" => $a));
                    exit;
                }
            }
        } else {
            echo json_encode(array("errorCode" => 10004, "errorMsg" => $c));
            exit;
        }
    }
}
