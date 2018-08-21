<?php
/**
 * 常用功能
 */
namespace common\components;

use yii;

class Helper
{
    /**
     * 图片地址转换
     * (用于输出时添加域名,支持图片数组)
     * @user tanbin.gao
     * @date 2017-5-26
     * @param string | array $imgPath 图片地址 (添加是否存在http://重复验证)
     * @param bool $absoluteUrl 是否绝对地址
     * @return string
     */
    public static function imageUrlConvert($imgPath, $absoluteUrl = true)
    {
        if (empty($imgPath)){
            if (is_array($imgPath)){
                return [];
            }
            return '';
        }
        if ($absoluteUrl) {
            if (is_array($imgPath)) {
                foreach ($imgPath as $key => $item) {
                    $imgPath[$key] = self::imageUrlConvert($item,$absoluteUrl);
                }
                return $imgPath;
            } else if (is_string($imgPath)) {
                if (stripos($imgPath,"http:/") === FALSE){
                    if (substr($imgPath, 0, 1) != '/') {
                        return Yii::$app->params['cdnUrl'] . DIRECTORY_SEPARATOR . $imgPath;
                    }
                }
                return $imgPath;
            } else {
                return "";
            }
        } else {
            if (substr($imgPath, 0, 1) != '/') {
                $imgPath = Yii::$app->params['uploadPath']. DIRECTORY_SEPARATOR . $imgPath;
            }
            return $imgPath;
        }
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
     * @return Cache|fileCache
     */
    public static function cache($directory='') {
        /** @var FileCache|MemCache $cache */
        $cache = \Yii::$app->cache;
        if (in_array(get_class($cache),['common\components\Cache','yii\redis\Cache'])) {
            //memcache
            //$cache->keyPrefix = $directory;  //前缀不再另设。每个项目统一配置表设置
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
}