<?php

function sortClubByAdminState($club1, $club2)
{
    if ($club1['Audit'] == $club2['Audit'])
        return 0;
    
    return  ($club1['Audit'] > $club2['Audit'])? -1 : 1;
}

/**
 * 用户俱乐部列表操作类(工具类)
 * @author Ericcao
 **/

//下面开始处理字符串，返回最新的俱乐部列表。
//v1.0中，存储格式为id_权限，中间用;隔开
//eg: 333_2;443_1
class Util_ClubList
{
    private function __construct(){}

    /**
     * 加入俱乐部
     * @param $clublist string 源用户俱乐部列表
     * @param $id		int    俱乐部id
     * @param $audit	权限
     * return 返回新的俱乐部列表
     */
    public static function addClub($clublist,$id,$audit)
    {
        $newclubArray = array();
        $clubArray = explode(';',$clublist);
        if($clubArray)
        {
            foreach($clubArray as &$value)
            {
                if($value=='') continue;
                list($clubId,$au) = explode("_",$value);
                if ($clubId != $id)
                {
                    array_push($newclubArray,$value);
                }
            }
        }
        array_push($newclubArray,$id."_".$audit);
        return implode(";",$newclubArray);
    }

    /**
     * 加入俱乐部
     * @param $clublist string 源用户俱乐部列表
     * @param $id		int    俱乐部id
     * return 返回新的俱乐部列表
     */
    public static function removeClub($clublist,$id)
    {
        $newclubArray = array();
        $clubArray = explode(';',$clublist);
        if($clubArray)
        {
            foreach($clubArray as &$value)
            {
                list($clubId,$au) = split("_",$value);
                if ($clubId != $id)
                {
                    array_push($newclubArray,$value);
                }
            }
        }
        return implode(";",$newclubArray);
    }

    /**
     * 判断指定权限个数的俱乐部个数
     * @param $clublist string
     * @param $audit int
     * @return int
     */
    public static function auditCount($clublist,$audit)
    {
        $clubArray = split(";",$clublist);
        $count = 0;
        foreach($clubArray as &$value)
        {
            if(substr($value,-1) == $audit)
            {
                $count++;
            }
        }
        return $count;
    }

    public static function count($clublist)
    {
        $clubArray = split(";",$clublist);
        $count = 0;
        foreach($clubArray as &$value)
        {
            if(!empty($value))
            {
                $count++;
            }
        }
        return $count;
    }

    /**
     * 更新某个俱乐部的权限标志位
     * @param $clublist string
     * @param $audit int
     * @param $id int
     * @return 返回新的俱乐部列表
     */

    public static function modClubAudit($clublist,$id,$audit)
    {
        //下次修改使用explode函数
        $newclubArray = array();
        $clubArray = split(';',$clublist);
        foreach($clubArray as &$value)
        {
            if(!empty($value))
            {
                list($clubId,$au) = split("_",$value);
                if ($clubId != $id)
                {
                    array_push($newclubArray,$value);
                }
                else
                {
                    array_push($newclubArray, $clubId."_".$audit);
                }
            }
        }

        return implode(";",$newclubArray);
    }

    /**
     * 获取俱乐部列表
     * @param $clublist
     * @return array(ClubId=>)
     */
    public static function getClubList($clublist)
    {
        $clubArray = explode(';',$clublist);
        $clubIdArray = array();
        if($clubArray)
        {
            foreach($clubArray as &$value)
            {
                if(empty($value)) continue;
                list($clubId,$au) = split("_",$value);
                $clubIdArray[] = array('ClubId'=>$clubId,'Audit' =>$au);
            }
        }
        return $clubIdArray;
    }

    
    /**
     * 获取按照按照成员管理身份的俱乐部列表
     * @param $clublist
     * @return array(ClubId=>)
     */
    public static function getMemberAuditClubList($clublist)
    {
        $clubArray = explode(';',$clublist);
        $clubIdArray = array();
        if($clubArray)
        {
            foreach($clubArray as &$value)
            {
                if(empty($value)) continue;
                list($clubId,$au) = split("_",$value);
                $clubIdArray[] = array('ClubId'=>$clubId,'Audit' =>$au);
            }
        }
        
        uasort($clubIdArray, "sortClubByAdminState");
        
        return $clubIdArray;
    }
    
    
}
?>