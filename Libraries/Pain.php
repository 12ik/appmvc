<?php 
define('MATRIX_PHP_PATH', '/data/apache/www/_work');
define('MATRIX_ERROR_FILE',dirname(__FILE__).'/error.php');
$host = 'cms.ledu.com';
define('PAIN_STATIC_HOST',"http://$host/");
define('PAIN_HOST',"http://$host/pain");
$GLOBALS['gPAIN_HOST'] = PAIN_HOST;

/*
//独立的新闻城市
$GLOBALS['gPAIN_MAIN_CITY']= array(
        'all' => '全国',
        'bj' => '北京',
        'sh' => '上海',
        'gz' => '广州',
        'nn' => '南宁',
        'sz' => '深圳'
);
*/

#$gPAIN_DEBUG = true;

class Pain{

        /**
         * 碎片显示地址
         */
        function pain_get_show_path($id, $city = 'all'){
                $p = md5($id);
                return   MATRIX_PHP_PATH . "/{$p[0]}{$p[1]}/{$p[2]}{$p[$h]}/{$id}.{$city}";
        }
        /**
         * 碎片编辑地址
         */
        function pain_get_edit_path($id,$city = 'all'){
                $p = md5($id);
                return  MATRIX_PHP_PATH . "/{$p[0]}{$p[1]}/{$p[2]}{$p[$h]}/$id.{$city}.edit";
        }

        /** * 定时更新碎片执行路径
         */
        function pain_get_run_path($id,$city = 'all'){
                $p = md5($id);
                return   MATRIX_PHP_PATH . "/{$p[0]}{$p[1]}/{$p[2]}{$p[$h]}/{$id}.{$city}.run.php";
        } 

        function show_matrix($id){
                self::pain_show_matrix($id);
        }

        /**
         * 显示一个碎片 
         */
        function pain_show_matrix($id,$force_city = null){
                //check login 
        /*
        if( $_GET['ala_edit_matrix'] || $GLOBALS['gPAIN_DEBUG']){
                if($GLOBALS['login_id'] &&  $GLOBALS['login_name'] && preg_match('@^(dev\.)?tool\.ledu\.com$@',$_SERVER['HTTP_HOST'])){
                        $edit = true ;
                }
        }

        if($GLOBALS['gPAIN_EDIT_MATRIX']){
                $edit = true;
        }
         */
                if($_GET['ala_edit_matrix'] || $GLOBALS['gPAIN_DEBUG'] || $GLOBALS['gPAIN_EDIT_MATRIX']){
                        $edit = true;
                }


                /*

                if(! empty($force_city)){
                        $city = $force_city;
                }else{
                        $city = $GLOBALS['gCITY_SIMPLE_NM'];
                }

                if(! array_key_exists($city, $GLOBALS['gPAIN_MAIN_CITY']))
                        $city = 'all';
                 */

                //for ledu  no need city matching
                $city = 'all';
                //end for ledu 

                if($edit)
                        $path = self::pain_get_edit_path($id, $city);
                else
                        $path = self::pain_get_show_path($id, $city);

                if(is_readable($path)){
                        include($path);
                }
                else{
                        echo "\n<!-- matrix not found (m_id: $id;city: $city;  path: $path) -->\n";
                }
        }
        /**
         * 定时更新碎片执行
         */
        function pain_run_matrix($id, $city = 'all'){
                $run_file = pain_get_run_path($id, $city);
                $show_file = pain_get_show_path($id, $city);
                if(!is_readable($run_file))
                        $run_file = MATRIX_ERROR_FILE;
                $content = "<!-- matrix: $id time:".date('Y-m-d H:i:s')."-->\n";

                ob_start();
                include($run_file);
                $res =  ob_get_clean();
                $content .= $res."<!-- matrix: $id end-->";
                file_put_contents($show_file , $content);
                return $content;
        }

        /**
         * 初始化
         * */
        function pain_start_edit($group = 'news'){
                //check login 
                if(empty($GLOBALS['login_id'])){
                        return ;
                }
                //check edit 
                if($GLOBALS['gPAIN_DEBUG']  ||  $GLOBALS['gPAIN_EDIT_MATRIX'] || ($_GET['ala_edit_matrix'] &&  preg_match('@^(dev\.)?cms\.ledu\.com$@',$_SERVER['HTTP_HOST']))){

                        $edit_type = 'frag';
                        $static  = PAIN_STATIC_HOST;
                        $str =<<<_EOF_
                                <!--- 碎片编辑 开始-->
                                <link rel="stylesheet" href="$static/pain/css/focusCms.css" type="text/css" media="screen" />
                                <link rel="stylesheet" href="$static/pain/css/mooRainbow.css" type="text/css" media="screen" />
                                <script type="text/javascript" src="$static/pain/js/log4js.js"></script>
<script type="text/javascript" src="$static/pain/js/mootools.js"></script>
<script type="text/javascript" src="$static/pain/js/mootools-more.js"></script>
<script type="text/javascript" src="$static/pain/js/mooRainbow.1.2b2.js"></script>
<script type="text/javascript">
var gEditType = '$edit_type'; 
var gDebug = "{$GLOBLAS['gPAIN_DEBUG']}"; 
var pain_group = '$group';
                </script>
                <script type="text/javascript" src="$static/pain/js/sys.js"></script>
                <script type="text/javascript" src="$static/pain/js/cms.js"></script>
                <script type="text/javascript" src="$static/pain/js/cmsUtils.js"></script>
                <script type="text/javascript" src="$static/pain/js/window.js"></script>
                <script type="text/javascript" src="$static/pain/js/ztEditorWindow.js"></script>
                <div id="focus_frag_block"></div>
                <!--- 碎片编辑 结束-->
_EOF_;
echo $str;
        }
}

function _pain_get_css($p) {
        echo '<link rel="stylesheet" href="'.PAIN_STATIC_HOST.'/'.$p.'" type="text/css" media="screen" />';
}
function _pain_get_js($p){
        echo '<script type="text/javascript" src="'.PAIN_STATIC_HOST.'/'.$p.'"></script>';
}
}