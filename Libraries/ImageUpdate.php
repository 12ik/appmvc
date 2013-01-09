<?php        
     class ImageUpdate
     {  
         var $src;           //源地址  
         var $newsrc;        //新图路径(本地化后)  
         var $allowtype=array(".gif",".jpg",".png",".jpeg");     //允许的图片类型  
         var $regif=0;       //是否缩略GIF, 为0不处理  
         var $keep=0;        //是否保留源文件(1为保留, 0为MD5)  
         var $over=0;        //是否可以覆盖已存在的图片,为0则不可覆盖  
         var $dir;           //图片源目录  
         var $newdir;        //处理后的目录  
         var $newname="";
    
         function __construct($olddir=null,$newdir=null)  
         {  
             $this->dir=$olddir ? $olddir : "D:\\11";  
             $this->newdir=$newdir ? $newdir : "D:\\11";  
         }  
    	function file_listt($path)
		{ 
				if ($handle = opendir($path)) 
				{ 
					while (false !== ($file = readdir($handle))) 
						{ 
							if ($file != "." && $file != "..") 
								{ 
									
									if (is_dir($path."/".$file)) 
									{ //echo $path.": ".$file."<br />";//去掉此行显示的是所有的非目录文件 
											$this->file_list($path."/".$file); 
									}
									else 
									{ 
										if(strpos($file,'.png',1)||strpos($file,'.JPG',1)||strpos($file,'.jpg',1)||strpos($file,'.gif',1))
											{ 
												$this->newdir = $path;
												$this->reImg($path.'/'.$file,320,73,80);
												echo $path.'/'.$file."</br>";
											} 
									} 
								} 
						} 
					} 
		}
         function reNames($src)  
         {  
         	 $pinfo = pathinfo($src);	
             $md5file=$pinfo['filename']."_new".strrchr($src,"."); //MD5文件名后(例如:3293okoe.gif)  
             return $this->newdir."/".$md5file;                   //将源图片,MD5文件名后保存到新的目录里  
         }  
    
         function Mini($src,$w,$h,$q=80)     //生成缩略图 Mini(图片地址, 宽度, 高度, 质量)  
         {  
             $this->src=$src;  
             $this->w=$w;  
             $this->h=$h;  
             if(strrchr($src,".")==".gif" && $this->regif==0) //是否处理GIF图  
             {  
                 return $this->src;  
             }  
             if($this->keep==0)       //是否保留源文件，默认不保留  
             {  
                 $newsrc=$this->reNames($src);    //改名后的文件地址  
             }  
             else                    //保持原名  
             {  
                 $src=str_replace("\\","/",$src);  
                 $newsrc=$this->newdir.strrchr($src,"/");  
             }  
             
             if(file_exists($newsrc) && $this->over==0)       //如果已存在,直接返回地址  
             {  
                 return $newsrc;  
             }  
             if(strstr($src,"http://") && !strstr($src,$_SERVER['HTTP_HOST']))//如果是网络文件,先保存  
             {  
                 $src=$this->getimg($src);  
             }  
             $arr=getimagesize($src);    //获取图片属性  
             $width=$arr[0];  
             $height=$arr[1];  
             $type=$arr[2];  
             switch($type)  
             {  
                 case 1:     //1 = GIF，  
                     $im=imagecreatefromgif($src);  
                     break;  
                 case 2:     //2 = JPG  
                     $im=imagecreatefromjpeg($src);  
                     break;  
                 case 3:     //3 = PNG  
                     $im=imagecreatefrompng($src);  
                     break;  
                 default:  
                    return 0;  
             }  
    
             //处理缩略图  
             $nim=imagecreatetruecolor($w,$h);  
             $k1=round($h/$w,2);  
             $k2=round($height/$width,2);  
             
             if($k1<$k2)  
             {  
                 $width_a=$width;  
                 $height_a=round($width*$k1);  
                 $sw=0;  
                 $sh=($height-$height_a)/2;  
    
             }  
             else 
             {  
                  $width_a=$height/$k1;  
                  $height_a=$height;  
                  $sw=($width-$width_a)/2;  
                  $sh = 0;  
             }  
    
             //生成图片  
             if(function_exists(imagecopyresampled))  
             {  
                 imagecopyresampled($nim,$im,0,0,$sw,$sh,$w,$h,$width_a,$height_a);  
             }  
             else 
             {  
                 imagecopyresized($nim,$im,0,0,$sw,$sh,$w,$h,$width_a,$height_a);  
             }  
             if(!is_dir($this->newdir))  
             {  
                 mkdir($this->newdir);  
             }  
    
             switch($type)       //保存图片  
             {  
                 case 1:  
                     $rs=imagegif($nim,$newsrc);  
                     break;  
                 case 2:  
                     $rs=imagejpeg($nim,$newsrc,$q);  
                     break;  
                 case 3:  
                     $rs=imagepng($nim,$newsrc);  
                     break;  
                 default:  
                     return 0;  
             }  
             return $newsrc;     //返回处理后路径  
         }  
    
         function getimg($filename)  
         {  
             $md5file=$this->dir."/"."mini".strrchr($filename,".");  
             if(file_exists($md5file))  
             {  
                 return $md5file;  
             }  
             //开始获取文件,并返回新路径  
             $img=file_get_contents($filename);  
             if($img)  
             {  
                 if(!is_dir($this->dir))  
                 {  
                     mkdir($this->dir);  
                 }  
                 savefile($md5file,$img);  
                 return $md5file;  
             }  
         }  
         function reImg($src,$w,$h,$q)   //转换缩略图(文件名和结构不变)  
         {  
             return $this->Mini($src,$w,$h,$q);       //return 生成的地址  
         }  
     }  

?> 
