<?php
/**
 * 常用工具类
 */

namespace common\components;

use yii\caching\FileCache;
use yii\caching\MemCache;
use yii;

class Tool
{
    /**
     * xml转成Array
     * <?xml version=\"1.0\" encoding=\"utf-8\"?>
     *  <ArrayOfGCPPicture>
     *  <GCPPicture>
     *  <Size>
     *  <Width>0</Width>
     *  <Height>0</Height>
     *  </Size>
     *  <Thumbnail>files/2016/04/28/9b7192b56d35d2923f233986325d0304.jpg</Thumbnail>
     *  <URL />
     *  </GCPPicture>
     *  </ArrayOfGCPPicture>
     * @param $xml
     * @param $unkey 是否去除头node
     * @return xml | array
     */
    public static function XmlToArray( $xml ,$unkey=false){
        $reg = "/<(\\w+)[^>]*?>([\\x00-\\xFF]*?)<\\/\\1>/";
        if(preg_match_all($reg, $xml, $matches))
        {
            $count = count($matches[0]);
            $arr = array();
            for($i = 0; $i < $count; $i++)
            {
                $key= $matches[1][$i];
                $val = self::XmlToArray( $matches[2][$i] );  // 递归
                if(array_key_exists($key, $arr))
                {
                    if(is_array($arr[$key]))
                    {
                        if(!array_key_exists(0,$arr[$key]))
                        {
                            $arr[$key] = array($arr[$key]);
                        }
                    }else{
                        $arr[$key] = array($arr[$key]);
                    }
                    $arr[$key][] = $val;
                }else{
                    $arr[$key] = $val;
                }
            }

            if ($unkey != false){
                if ( count(array_keys($arr)) <= 1 )
                    return $arr[array_keys($arr)[0]];
            }
            return $arr;
        }else{
            return $xml;
        }
    }
    
    /**
     * 显示金额，千分位为,
     * @return float
     */
    public static function showMoney($money) {
//        return number_format($money, 2, '.', ',');
        return round($money, 2);
    }
    
    /**
     * 创建目录
     * 可以递归创建，默认是以当前网站根目录下创建
     * 第二个参数指定，就以第二参数目录下创建
     * @param string $path      要创建的目录
     * @param string $webroot   要创建目录的根目录
     * @return boolean
     */
    public static function createDir($path, $webroot = null) {
    	$path = preg_replace('/\/+|\\+/', DS, $path);
    	$dirs = explode(DS, $path);
    	if (!is_dir($webroot))
    		$webroot = \Yii::getAlias("@webroot");
    	foreach ($dirs as $element) {
    		$webroot .= DS . $element;
    		if (!is_dir($webroot)) {
    			if (!mkdir($webroot, 0777))
    				return false;
    			else
    				chmod($webroot, 0777);
    		}
    	}
    	return true;
    }
    
    /**
     * 设定缓存路径
     * @param string $directory
     * @return FileCache|MemCache
     */
    public static function cache($directory='') {
        /** @var FileCache|MemCache $cache */
        $cache = \Yii::$app->cache;
        if (in_array(get_class($cache),['common\components\Cache','yii\redis\Cache'])) {
            //memcache
            $cache->keyPrefix = $directory;
            return $cache;
        } else {
            //文件缓存
            $cachePath = \Yii::getAlias('@cache');
            $path =  $cachePath. DS . $directory;
            if (!is_dir($path))
                self::createDir($directory,$cachePath);
            $cache->cachePath = \Yii::getAlias('@cache') . DS . $directory;
            return $cache;
        }
    }
    
    const FLASH_SESSION_PREFIX = 'FLASH_';
    
    /**
     * 判断是否存在flash值
     * @param string $key
     * @param string|array $defaultValue
     * @return string|array
     */
    public static  function hasFlash($key) {
    	$key = self::FLASH_SESSION_PREFIX.$key;
    	return isset($_SESSION[$key])&&!empty($_SESSION[$key]);
    }
    
    
    /**
     * 获取flash值
     * @param string $key
     * @param string|array $defaultValue
     * @return string|array
     */
    public static function getFlash($key, $defaultValue = null, $delete = true) {
    	$key = self::FLASH_SESSION_PREFIX.$key;
    	$val = isset($_SESSION[$key])?$_SESSION[$key]:$defaultValue;
    	if($delete) unset($_SESSION[$key]);
    	return $val;
    }
    
    /**
     * 设置flash值
     * @param string $key
     * @param string|array $value
     */
    public static function setFlash($key, $value = null) {
    	$key = self::FLASH_SESSION_PREFIX.$key;
    	$_SESSION[$key] = $value;
    	return true;
    }

    /**
     * 计算两个日期相差天数
     * @param $day1 开始日期
     * @param $day2 结束日期
     * @return float
     */
    public static function diffBetweenTwoDays($day1, $day2)
    {
        $second1 = strtotime($day1);
        $second2 = strtotime($day2);

        if ($second1 < $second2) {
            $tmp = $second2;
            $second2 = $second1;
            $second1 = $tmp;
        }
        return ($second1 - $second2) / 86400;
    }

    /**
     * discuz 的可逆加密解密函数
     * @param string $string 明文 或 密文
     * @param string $operation DECODE 表示解密,其它表示加密
     * @param string $key 密匙
     * @param number $expiry 密文有效期
     * @return string
     */
    static public function authcode($string, $operation = 'ENCODE', $key = '', $expiry = 0) {

        $ckey_length = 4; // 随机密钥长度 取值 0-32;
        // 加入随机密钥，可以令密文无任何规律，即便是原文和密钥完全相同，加密结果也会每次不同，增大破解难度。
        // 取值越大，密文变动规律越大，密文变化 = 16 的 $ckey_length 次方
        // 当此值为 0 时，则不产生随机密钥

        $key = md5($key ? $key : 'GATE23450dfsdfasfsdf*(&^&%^%^%$345345324523sdfsf'.HOTEL_ASYCN_KEY);
        $keya = md5(substr($key, 0, 16));
        $keyb = md5(substr($key, 16, 16));
        $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';

        $cryptkey = $keya . md5($keya . $keyc);
        $key_length = strlen($cryptkey);

        $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
        $string_length = strlen($string);

        $result = '';
        $box = range(0, 255);

        $rndkey = array();
        for ($i = 0; $i <= 255; $i++) {
            $rndkey[$i] = ord($cryptkey[$i % $key_length]);
        }

        for ($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }

        for ($a = $j = $i = 0; $i < $string_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }

        if ($operation == 'DECODE') {
            if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
                return substr($result, 26);
            } else {
                return '';
            }
        } else {
            return $keyc . str_replace('=', '', base64_encode($result));
        }
    }

    const CURRENCY_RMB = 'CNY';
    const CURRENCY_HK = 'HKD';

    /**
     * imageView2 生成缩略图
     * 更多方法：http://developer.qiniu.com/code/v6/api/kodo-api/image/imageview2.html
     *
     * @param string $url  http://obfe239za.bkt.clouddn.com/ad/3d2199f05841260860c1030dad23fa09.jpg
     * @param string $params  宽度*高度 100*100，也可以是自定义其他图片处理
     * @return  string
     */
    public static function showImg($url, $params,$type= 5)
    {
        if (stripos($params, '*') !== false) {
            $arr = explode('*', $params);
            $interlace = substr($url, -3) == 'jpg' ? '/interlace/1' : ''; //jpg 图片渐进显示处理
            return $url . '?imageView2/'. $type .'/w/' . $arr[0] . '/h/' . $arr[1] . $interlace;
        } else {
            return $url . '?imageView2/' . $params;
        }
    }

    /**
     *
     * 全局唯一标识符（GUID，Globally Unique Identifier）是一种由算法生成的二进制长度为128位的数字标识符
     * @return string
     */
    public static function createGUID() {
        $charId = md5(uniqid(mt_rand(), true));
        $hyphen = chr(45);// "-"
        $uuid = substr($charId, 0, 8).$hyphen
            .substr($charId, 8, 4).$hyphen
            .substr($charId,12, 4).$hyphen
            .substr($charId,16, 4).$hyphen
            .substr($charId,20,12);
        return $uuid;
    }


    /**
     * 获取数组出现频率最高的元素
     * @param $array
     * @param int $length
     * @return array|bool
     */
    public static function mostRepeatedValues($array,$length=0){
        if(empty($array) or !is_array($array)){
            return false;
        }
        //1. 计算数组的重复值
        $array = array_count_values($array);
        //2. 根据重复值 倒排序
        arsort($array);
        if($length>0){
            //3. 返回前 $length 重复值
            $array = array_slice($array, 0, $length, true);
        }
        $keys = array_keys($array);
        return isset($keys[0])?$keys[0]:'';
    }


    /**
     * 过滤字符串中的符号。
     *
     * @param $text
     * @return string
     */
    function filter_mark($text){
        if(trim($text)=='')return '';
        $text=preg_replace("/[[:punct:]\s]/",' ',$text);
        $text=urlencode($text);
        $text=preg_replace("/(%7E|%60|%21|%40|%23|%24|%25|%5E|%26|%27|%2A|%28|%29|%2B|%7C|%5C|%3D|\-|_|%5B|%5D|%7D|%7B|%3B|%22|%3A|%3F|%3E|%3C|%2C|\.|%2F|%A3%BF|%A1%B7|%A1%B6|%A1%A2|%A1%A3|%A3%AC|%7D|%A1%B0|%A3%BA|%A3%BB|%A1%AE|%A1%AF|%A1%B1|%A3%FC|%A3%BD|%A1%AA|%A3%A9|%A3%A8|%A1%AD|%A3%A4|%A1%A4|%A3%A1|%E3%80%82|%EF%BC%81|%EF%BC%8C|%EF%BC%9B|%EF%BC%9F|%EF%BC%9A|%E3%80%81|%E2%80%A6%E2%80%A6|%E2%80%9D|%E2%80%9C|%E2%80%98|%E2%80%99|%EF%BD%9E|%EF%BC%8E|%EF%BC%88)+/",' ',$text);
        $text=urldecode($text);
        return trim($text);
    }


    /**
     * 获取剩余时间
     * @param $second
     * @return string
     */
    public static function time2string($second,$returnString=true){
        $day = floor($second/(3600*24));
        $second = $second%(3600*24);//除去整天之后剩余的时间
        $hour = floor($second/3600);
        $second = $second%3600;//除去整小时之后剩余的时间
        $minute = floor($second/60);
        $second = $second%60;//除去整分钟之后剩余的时间
        //返回字符串
        return $returnString?$day.'天'.$hour.'小时'.$minute.'分'.$second.'秒':['day'=>$day,'hour'=>$hour,'minute'=>$minute,'second'=>$second,];
    }

    /**
     * ip转换为整型
     * @param string $ip
     * @return int
     */
    public static function ip2int($ip) {
        return bindec(decbin(ip2long($ip)));
    }

    /**
     * 生成唯一订单号
     * @param int $length 订单号长度(不包含前缀)，最小19位
     * @param string $prefix
     * @return string
     */
    public static function buildOrderNo($length = 20, $prefix = null) {
        $main = date('YmdHis') . substr(microtime(), 2, 3) . sprintf('%02d', mt_rand(0, 99));
        return $prefix . str_pad($main, $length, mt_rand(0, 99999));
    }

    /**
     * 格式化 世界时间
     * @param $string 2015-12-11T00:00:00
     * @param string $format 输入时间格式
     * @return false|string
     */
    public static function formatStrDate($string,$format='Y-m-d H:i:s'){
        return empty($string) ? '' : date($format,strtotime($string));
    }

    /**
     * @param 输出商品名称
     *
     * 名称大于15未 自动省略加。。。
     *
     * @param int $len
     */
    public static function showProductName($name,$len=15,$apan = '...'){
        return mb_substr($name,0,$len).(mb_strlen($name)>=$len?$apan:'');
    }

    public static function curl($url, $data = [], $type = 'post')
    {
        $ch = curl_init();
        $queryData = http_build_query($data);
        if($type == 'get'){
            $url = $url . '?' . $queryData;
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);    // 要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_HEADER, 0); // 不要http header 加快效率
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);

        if($type == 'post'){
            curl_setopt($ch, CURLOPT_POST, 1);    // post 提交方式
            curl_setopt($ch, CURLOPT_POSTFIELDS, $queryData);
        }

        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }


    /**
     * 获取ip地址
     * 经过cdn 处理，真实地址在 HTTP_X_FORWARDED_FOR
     */
    public static function getIP() {
        $real_ip = false;
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(', ', $_SERVER['HTTP_X_FORWARDED_FOR']);
            foreach($ips as $ip) {
                if (preg_match( '/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $ip )) {
                    $real_ip = $ip;
                    break;
                }
            }
        }
        return $real_ip ? $real_ip : $_SERVER['REMOTE_ADDR'];
    }


    /**
     * 格式化时间
     * @param string $timeStr
     * @param string $format
     * @return false|string
     */
    public static function formatTimeStr($timeStr='',$format='Y-m-d G:i:s'){
        if (empty($timeStr)) return date($format,time());

        return date($format,strtotime($timeStr));

    }


    /**
     *  @DESC 数据导出
     *  @example
     *  $data = [1, "小明", "25"];
     *  $header = ["id", "姓名", "年龄"];
     *  Myhelpers::exportData($data, $header);
     *  @return void, Browser direct output
     */
    public static function exportData ($data, $header, $title = "simple", $filename = "data")
    {
        if (!is_array ($data) || !is_array ($header)) return false;
        $objPHPExcel = new \PHPExcel();
        // Set properties
        $objPHPExcel->getProperties()->setCreator("Maarten Balliauw");
        $objPHPExcel->getProperties()->setLastModifiedBy("Maarten Balliauw");
        $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.");
        // Add some data
        $objPHPExcel->setActiveSheetIndex(0);
        //添加头部
        $hk = 0;
        foreach ($header as $k => $v)
        {
            $colum = \PHPExcel_Cell::stringFromColumnIndex($hk);
            $objPHPExcel->setActiveSheetIndex(0) ->setCellValue($colum."1", $v);
            $hk += 1;
        }
        $column = 2;
        $objActSheet = $objPHPExcel->getActiveSheet();
        foreach($data as $key => $rows)  //行写入
        {
            $span = 0;
            foreach($rows as $keyName => $value) // 列写入
            {
                $j = \PHPExcel_Cell::stringFromColumnIndex($span);
                $objActSheet->setCellValue($j.$column, $value);
                $span++;
            }
            $column++;
        }
        // Rename sheet
        $objPHPExcel->getActiveSheet()->setTitle($title);
        // Save Excel 2007 file
        $objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);
        header("Pragma:public");
        header("Content-Type:application/x-msexecl;name=\"{$filename}.xls\"");
        header("Content-Disposition:inline;filename=\"{$filename}.xls\"");
        $objWriter->save("php://output");
    }

    /**
     *
     * 输出手机号码 带隐藏符号
     * @param $data
     * @param $header
     * @param string $title
     * @param string $filename
     */
    public static function showMobile ($mobile='',$patten='*',$start=4,$end=7)
    {
        if(!$mobile) return $mobile;
        $hidden = '';
        $hidden = str_pad($hidden,$end-$start+1,$patten);
        return substr($mobile,0,$start-1).$hidden.substr($mobile,$end);
    }

    /**
     *
     * 输出邮箱 带隐藏符号
     * @param $data
     * @param $header
     * @param string $title
     * @param string $filename
     */
    public static function showEMail ($email='',$patten='*')
    {
        if(!$email) return $email;
        $emailArr = explode('@',$email);
        $len = round(strlen($emailArr[0])/2);
        $start = round($len/2);
        $hidden = '';
        $hidden = str_pad($hidden,$len-$start+1,$patten);
        return substr($emailArr[0],0,$start).$hidden.substr($emailArr[0],$len+1).'@'.$emailArr[1];
    }

    public static $badWords = [];

    /**
     * 过滤接口中的json字段的特殊字符
     * @param string $jsonStr
     * @return mixed
     */
    public static function filterJsonString($jsonStr=''){
        return str_replace(array(" ","　","\n","\r","\t"),array("","","","",""),$jsonStr);
    }

    /**
     * 获取数组重复元素
     *
     */
    public static function array_repeat($arr)
    {
        if(!is_array($arr)) return $arr;

        $arr1 = array_unique($arr);

        $arr3 = array_diff_key($arr,$arr1);

        return array_unique($arr3);
    }

    /**
     * 获取随机数
     * @param int $lenth
     * @param string $chars
     * @return string
     */
    public static function randomStr($lenth = 6, $chars = '123456789abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ') {
        $hash = '';
        $max = strlen($chars) - 1;
        for ($i = 0; $i < $lenth; $i++) {
            $hash .= $chars[mt_rand(0, $max)];
        }
        return $hash;
    }

}