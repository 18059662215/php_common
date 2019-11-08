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
class Loader
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
    static function &getSiteInfo1()
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
    static function &getSiteInfo2()
    {
        $ip = self::get_client_ip();
        //利用百度地图
        $res = file_get_contents("http://api.map.baidu.com/location/ip?ak=YWNt8VcHK7Goj1yljLlMVHnWl6ZWS26t&ip={$ip}&coor=bd09ll");
        return $res;
    }

    /**
     *图片上传（files:上传图片，$path:图片路径）
     */
    static function &uploadimg($files, $path)
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
    static function &delFlie($str)
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
    static function &fun($status)
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
        if ($status == 'post') {
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
        if ($status == 'put') {
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
    static function &getDistance($lat1, $lng1, $lat2, $lng2,$bz=6367000)
    {
        $earthRadius = $bz; //approximate radius of earth in meters
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
    static function &getDs($num, $latitude, $longitude,$bz=6372.3797)
    {
        $range = 180 / pi() * $num /$bz;
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
    static function &msg($data, $success)
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
    static function &message($a, $b, $c, $d = null, $e = null, $ais = true, $dis = true, $eis = true)
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
    /**
     * @param 文本编辑器上传
     */
    static function &upEditor($content)
    {
        if (!empty($content)){
            //正则表达式匹配查找图片路径
            $pattern = '/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.jpeg|\.png]))[\'|\"].*?[\/]?>/i';
            preg_match_all($pattern, $content, $res);
            $num = count($res[1]);
            for ($i = 0; $i < $num; $i++) {
                //获取完整图片路径
                $img = $res[1][$i];
                //去除域名信息
                $imgs=explode(self::getHttp(),$img);
                //对剩余部分进行切割
                $tmp_arr = explode('/', $imgs[1]);

                $datefloder = '/static/temporary/images/'.$tmp_arr[4];
                //拼接出完整的服务器路径
                $datefloder=$_SERVER["DOCUMENT_ROOT"].$datefloder;
                //查看临时图片目录是否存在不存在则创建(需要有父级目录权限)
                $file_box=$_SERVER["DOCUMENT_ROOT"].'/static/temporary/images';
                if (!is_dir($file_box)) {
                    mkdir($file_box, 0777);
                }
                //查看正式图片目录是否存在不存在则创建(需要有父级目录权限)
                $file_boxs=$_SERVER["DOCUMENT_ROOT"].'/static/formal/images';
                if (!is_dir($file_boxs)) {
                    mkdir($file_box, 0777);
                }
                $tmpimg = $datefloder;
                $newimg = str_replace('/temporary/', '/formal/', $tmpimg);
                //将图片从临时目录转移到正式目录
                if (rename($tmpimg, $newimg)) {
                    //图片转移完成，进行内容替换
                    $content = str_replace('/temporary/', '/formal/', $content);
                }
            }
            return $content;
        }
    }
    /**
     * @param 文本编辑器修改
     */
    static function &editEditor($content,$oldcontent){
        //下次编辑文章内容的时候使用同样的思路，不过要先判断是否是新上传的图片，原来的就不要动了。
        //还有一种情况是原来已经上传的图片在被编辑的时候删除了，虽然数据库修改了，但是文件还在，所以需要和原内容进行比较之后删除。
        //转移editor文件
        if(!empty($content))
        {
            //正则表达式匹配查找图片路径
            $pattern='/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.jpeg|\.png]))[\'|\"].*?[\/]?>/i';
            preg_match_all($pattern,$content,$res);
            $num=count($res[1]);
            $imgarr=[];
            for($i=0;$i<$num;$i++)
            {
                $img=$res[1][$i];
                //判断是否是新上传的图片
                $pos=stripos($img,"/temporary/");
                //判断是否是原来的图片
                $oldimg=stripos($img,"/formal/");
                if($oldimg>0){
                    //将所有新内容的老图片放一个数组里
                    $imgarr[]=$img;
                }

                //判断有新传的图片时，处理新上传的图片
                if($pos>0)
                {
                    //去除域名信息
                    $imgs=explode(self::getHttp(),$img);

                    $tmp_arr=explode('/',$imgs[1]);

                    $datefloder='/static/temporary/images/'.$tmp_arr[4];
                    $datefloder=$_SERVER["DOCUMENT_ROOT"].$datefloder;


                    //查看临时图片目录是否存在不存在则创建(需要有父级目录权限)
                    $file_box=$_SERVER["DOCUMENT_ROOT"].'/static/temporary/images';
                    if (!is_dir($file_box)) {
                        mkdir($file_box, 0777);
                    }
                    //查看正式图片目录是否存在不存在则创建(需要有父级目录权限)
                    $file_boxs=$_SERVER["DOCUMENT_ROOT"].'/static/formal/images';
                    if (!is_dir($file_boxs)) {
                        mkdir($file_box, 0777);
                    }
                    $tmpimg = $datefloder;
                    $newimg = str_replace('/temporary/', '/formal/', $tmpimg);
                    //转移图片
                    if(rename($tmpimg, $newimg))
                    {
                        $content=str_replace('/temporary/','/formal/',$content);
                    }
                }
            }
        }
        //删除在编辑时被删除的原有图片
        if(!empty($oldcontent))
        {
            //正则表达式匹配查找图片路径
            $pattern='/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.jpeg|\.png]))[\'|\"].*?[\/]?>/i';
            preg_match_all($pattern,$oldcontent,$oldres);
            $num=count($oldres[1]);
            for($i=0;$i<$num;$i++)
            {
                $delimg=$oldres[1][$i];
                //判断在新内容中不存在的图片进行删除
                if(!in_array($delimg[1], $imgarr))
                {
                    //去除域名信息
                    $delimg=explode(self::getHttp(),$delimg);
                    $delimage=$_SERVER["DOCUMENT_ROOT"].$delimg[1];
                    if(file_exists($delimage)){
                        unlink($delimage);
                    }

                }
            }
        }
        return $content;
    }
    //删除文本编辑器内容里的图片
    static  function &delEditor($content){
        //正则表达式匹配查找图片路径
        $pattern='/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.jpeg|\.png]))[\'|\"].*?[\/]?>/i';
        preg_match_all($pattern,$content,$res);
        $num=count($res[1]);
        for($i=0;$i<$num;$i++)
        {
            $img=$res[1][$i];
            //去除域名信息
            $imgs=explode(self::getHttp(),$img);
            $tmp_arr=explode('/',$imgs[1]);
            $datefloder='/static/formal/images/'.$tmp_arr[4];
            //获取图片在服务器的完整路径
            $datefloder=$_SERVER["DOCUMENT_ROOT"].$datefloder;
            //判断并删除图片
            if(file_exists($datefloder)){
                unlink($datefloder);
            }
        }
        return true;
    }

     /**
     * @desc 验证上传照片/视频的大小，类型
     * @param  @size  文件大小
     * @param  @type  文件类型
     * @return  1-通过,2-错误文件类型，3-文件过大
     **/
    static function uploadFileValidate($type, $size)
    {
        $allow_type = array("image/jpg", "image/jpeg", "image/gif", "image/png", "video/mp4");

        if (!in_array($type, $allow_type)) {
            return 2;
        }
        if ($size > 1000000) {
            return 3;
        }
        return 1;
    }
    /**
     * 递归创建目录
     * @param $dir
     * @param int $mod
     * @return bool
     */
    protected function mk_dir($dir, $mod = 0777)
    {
        if (is_dir($dir)) {
            return true;
        }
        return is_dir(dirname($dir)) || $this->mk_dir(dirname($dir)) ? mkdir($dir, $mod) : false;

    }

    /**
     * @desc  图片上传临时目录
     * @param  @path       文件路径  入口文件所在目录/static/temporary/images 开始
     * @param  @filename   文件名称(不带后缀)
     * @return
     **/
    static function &uploadFiles()
    {
    //进行多图上传
        $leg=0;
        foreach ($_FILES as $key=>&$val){
            $uploadConfig = '/static/temporary/images';
            if (is_uploaded_file($val['tmp_name'])) {
                $type = $val["type"];            //被上传文件的类型
                $size = $val["size"];            //被上传文件的大小
                $tep_name = $val["tmp_name"];    //存储在服务器上的临时副本
                $typeArr = explode('/', $type);
                $returnStatus = $this->uploadFileValidate($type, $size);
                if ($returnStatus == 2) {
                    throw new Exception("Error UploadFile Type");
                } else if ($returnStatus == 3) {
                    throw new Exception("Error UploadFile Size");
                }
                $file_path = $_SERVER["DOCUMENT_ROOT"].$uploadConfig;
                if (!file_exists($file_path)) {
                    self::mk_dir($file_path);
                }
                $fileName = 'Image_' . time() . rand(10000, 99999) . '.' . $typeArr[1];
                if (move_uploaded_file($tep_name, iconv("utf-8", "gb2312", $file_path.'/'.$fileName))) {
                    $path='/public/'.$uploadConfig."/".$fileName;
                    $lpath=self::getHttp().'/'.'static/temporary/images'."/".$fileName;
                    $leg+=1;
                    $data[]=$path;
                    $ldata[]=$lpath;
                } else {
                    throw new Exception("Error UploadFile");
                }
            } else {
                throw new Exception("Error UploadFile NULL");
            }
        }
        if(count($_FILES)==$leg){
            return [
                "errno"=>0,
                "data"=>$ldata,
                'ldata'=>$data,
                "msg"=>"图片上传成功",
            ];
        }
    }

    //将图片转移到正式图片目录
    static function &unPic($img){
        $imgs=explode(self::getHttp(),$img);
        $datefloder=$_SERVER["DOCUMENT_ROOT"].$imgs[1];
        $newimg = str_replace('/temporary/', '/formal/', $datefloder);
        //转移图片
        if(rename($datefloder, $newimg))
        {
            $content=str_replace('/temporary/','/formal/',$img);
        }
        return $content;
    }
    //删除图片
    static function &delPic($img){
        $imgs=explode(self::getHttp(),$img);
        $datefloder=$_SERVER["DOCUMENT_ROOT"].$imgs[1];
        if(file_exists($img)){
            unlink($datefloder);
        }
        return true;
    }
    //修改图片
    static function &editPic($img,$oldimg){
        //判断是否是新上传的图片
        $pos=stripos($img,"/temporary/");
        if($pos>0){
            //转移新图片到正式目录
            $imgs=explode(self::getHttp(),$img);
            $datefloder=$_SERVER["DOCUMENT_ROOT"].$imgs[1];
            $newimg = str_replace('/temporary/', '/formal/', $datefloder);
            //转移图片
            if(rename($datefloder, $newimg))
            {
                $content=str_replace('/temporary/','/formal/',$img);
            }

            //删除旧图片
            $oldimgs=explode($this->getHttp(),$oldimg);
            $root_path = Env::get('root_path');
            $datefloders=$root_path.'public'.$oldimgs[1];
            if(file_exists($datefloders)){
                unlink($datefloders);
            }
            return $content;
        }else{
            return $img;
        }
    }
    //权限获取
    static function getAuth($array=array(1=>'添加',2=>'删除',3=>'修改',4=>'查看',5=>'启用',6=>'禁用'),$qz=18){
        //权限管理  
        $qx_list=[];
        foreach ($array as $key=>$val){
            //权限判断
            if($key & $qz){
                $qx_list[$key]=$val;
            }
        }
        return json($qx_list);
    }
}
