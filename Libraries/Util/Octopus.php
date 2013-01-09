<?php
/**
 * 章鱼分发
 * @author Ericcao
 *
 */
class Util_Octopus
{
    /**
     * 上传文件
     * @param $source 源路径
     * @param $desc 服务器上路径
     * @param $params array(host=>,port=,app)
     * @return boolean
     */
    static function upload($source,$desc,$params)
    {
        $host = $params['host'];
        $port = $params['port'];
        $type = 0x1;
        $site = $params['site'];
        if(oct_upload_file($host, $port, $type, $site, $desc, $source)==0)
        {
            return true;
        }
        return false;
    }
    
    /**
     * 删除文件
     * @param $desc 服务器上路径
     * @param $params array(host=>,port=,app)
     * @return boolean
     */
    static function del($desc,$params)
    {
        $host = $params['host'];
        $port = $params['port'];
        $site = $params['site'];
        if(oct_delete_file($host, $port,$site, $desc)==0)
        {
            return true;
        }
        return false;
    }
}
?>