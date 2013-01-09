<?
// xuhe added 2011-9-3 17:22:50

class Test_CharsetcheckController extends APP_Controller_Action
{

    public function test1Action()
    {
        // 检查字符串是否是指定字符集
        $str = '我爱中国';
        echo APP_Charset_Check::stringIs($str, 'gbk')    . '<br />';    //  1
        echo APP_Charset_Check::stringIs($str, 'gb2312') . '<br />';    //  1
        echo APP_Charset_Check::stringIs($str, 'gb18030'). '<br />';    //  1
        echo APP_Charset_Check::stringIs($str, 'utf-8')  . '<br />';    //  0
        echo APP_Charset_Check::stringIs($str, 'ascii')  . '<br />';    //  0

        // 获取字符串字符集
         echo APP_Charset_Check::getStringCharset($str);              // gb2312
    }


    public function test2Action()
    {
        // 获取文件字符集
        echo APP_Charset_Check::getFileCharset(__FILE__)  . '<br />';   // gb2312-8

        // 检查文件是否是指定字符集
        echo APP_Charset_Check::fileIs(__FILE__, 'gbk')   . '<br />';   //  1
        echo APP_Charset_Check::fileIs(__FILE__, 'utf-8') . '<br />';   //  0

    }
}

