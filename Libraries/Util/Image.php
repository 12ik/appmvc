<?
/**
 * 此类只确保 imagemagic 压缩没问题，其它的没测试
 * 
 * 请使用前先确认 todo:...
 */
class Util_Image {

	var $attachinfo = '';
	var $srcfile = '';
	var $targetfile = '';
	var $imagecreatefromfunc = '';
	var $imagefunc = '';
	var $attach = array();
	var $animatedgif = 0;

	var $imagelib = 1; //imagemagic
	var $imageimpath = "/usr/local/ImageMagick/bin/";
	var $thumbstatus = 1;
	var $watermarkstatus = 9;
	var $watermarktype = 1; //1png 0gif 2front
	var $watermarkminwidth = 100;
	var $watermarkminheight = 100;
	var $thumbquality = 100;
	var $watermarktrans = 65;
	var $watermarkquality = 80;

	/**
	 * 构造图片操作类
	 * @param $srcfile 源文件
	 * @param $targetfile 目标文件,不带后缀名的
	 */
	function __construct($srcfile,$targetfile) {
		$this->srcfile = $srcfile;
		$this->targetfile = $targetfile;
		$this->attach = array();
		$this->attachinfo = @getimagesize($this->srcfile);
		if(!$this->imagelib || !$this->imageimpath) {
			switch($this->attachinfo['mime']) {
				case 'image/jpeg':
					$this->imagecreatefromfunc = function_exists('imagecreatefromjpeg') ? 'imagecreatefromjpeg' : '';
					$this->imagefunc = function_exists('imagejpeg') ? 'imagejpeg' : '';
					break;
				case 'image/gif':
					$this->imagecreatefromfunc = function_exists('imagecreatefromgif') ? 'imagecreatefromgif' : '';
					$this->imagefunc = function_exists('imagegif') ? 'imagegif' : '';
					break;
				case 'image/png':
					$this->imagecreatefromfunc = function_exists('imagecreatefrompng') ? 'imagecreatefrompng' : '';
					$this->imagefunc = function_exists('imagepng') ? 'imagepng' : '';
					break;
			}
		} else {
			$this->imagecreatefromfunc = $this->imagefunc = TRUE;
		}

		$this->attach['size'] = empty($this->attach['size']) ? @filesize($this->srcfile) : $this->attach['size'];
		if($this->attachinfo['mime'] == 'image/gif') {
			$fp = fopen($this->srcfile, 'rb');
			$srcfilecontent = fread($fp, $this->attach['size']);
			fclose($fp);
			$this->animatedgif = strpos($srcfilecontent, 'NETSCAPE2.0') === FALSE ? 0 : 1;
		}
	}

	/**
	 * 要压缩图片
	 * @param $thumbwidth 宽
	 * @param $thumbheight 高
	 * @return false|目标文件
	 * 			没有压缩是返回false (尺寸太小没必要压缩)
	 */
	function Thumb($thumbwidth, $thumbheight) {
		if($this->imagelib && $this->imageimpath){
			$result = $this->Thumb_IM($thumbwidth, $thumbheight);
		}else{
			$result = $this->Thumb_GD($thumbwidth, $thumbheight);
		}
		
		if($this->thumbstatus == 2 && $this->watermarkstatus) {
		    //todo:error
			$this->Image($this->srcfile, $this->targetfile, $this->attach);
		}
		$this->attach['size'] = filesize($this->srcfile);
		return $result;
	}

	function Watermark($watermark_file) {
		if(!$watermark_file)
			return;
		if(($this->watermarkminwidth && $this->attachinfo[0] <= $this->watermarkminwidth && $this->watermarkminheight && $this->attachinfo[1] <= $this->watermarkminheight)) {
			return;
		}

		if($this->imagelib && $this->imageimpath){
			return $this->Watermark_IM($watermark_file);
		}else{
			return $this->Watermark_GD($watermark_file);
		}
	}

	function Thumb_GD($thumbwidth, $thumbheight) {

		if($this->thumbstatus && function_exists('imagecreatetruecolor') && function_exists('imagecopyresampled') && function_exists('imagejpeg')) {
			$imagecreatefromfunc = $this->imagecreatefromfunc;
			$imagefunc = $this->thumbstatus == 1 ? 'imagejpeg' : $this->imagefunc;
			list($img_w, $img_h) = $this->attachinfo;

			if(!$this->animatedgif && ($img_w >= $thumbwidth || $img_h >= $thumbheight)) {

				if($this->thumbstatus != 3) {
					$attach_photo = $imagecreatefromfunc($this->targetfile);

					$x_ratio = $thumbwidth / $img_w;
					$y_ratio = $thumbheight / $img_h;

					if(($x_ratio * $img_h) < $thumbheight) {
						$thumb['height'] = ceil($x_ratio * $img_h);
						$thumb['width'] = $thumbwidth;
					} else {
						$thumb['width'] = ceil($y_ratio * $img_w);
						$thumb['height'] = $thumbheight;
					}

					$targetfile = $this->thumbstatus == 1 ? $this->targetfile.'.thumb.jpg' : $this->targetfile;
					$cx = $img_w;
					$cy = $img_h;
				} else {
					$attach_photo = $imagecreatefromfunc($this->targetfile);

					$imgratio = $img_w / $img_h;
					$thumbratio = $thumbwidth / $thumbheight;

					if($imgratio >= 1 && $imgratio >= $thumbratio || $imgratio < 1 && $imgratio > $thumbratio) {
						$cuty = $img_h;
						$cutx = $cuty * $thumbratio;
					} elseif($imgratio >= 1 && $imgratio <= $thumbratio || $imgratio < 1 && $imgratio < $thumbratio) {
						$cutx = $img_w;
						$cuty = $cutx / $thumbratio;
					}

					$dst_photo = imagecreatetruecolor($cutx, $cuty);
					imageCopyMerge($dst_photo, $attach_photo, 0, 0, 0, 0, $cutx, $cuty, 100);

					$thumb['width'] = $thumbwidth;
					$thumb['height'] = $thumbheight;

					$targetfile = !$preview ? $this->targetfile.'.thumb.jpg' : ROOT_PATH.'/cache/watermark_temp.jpg';
					$cx = $cutx;
					$cy = $cuty;
				}

				$thumb_photo = imagecreatetruecolor($thumb['width'], $thumb['height']);
				imageCopyreSampled($thumb_photo, $attach_photo ,0, 0, 0, 0, $thumb['width'], $thumb['height'], $cx, $cy);
				clearstatcache();
				if($this->attachinfo['mime'] == 'image/jpeg') {
					$imagefunc($thumb_photo, $targetfile, $this->thumbquality);
				} else {
					$imagefunc($thumb_photo, $targetfile);
				}
				$this->attach['thumb'] = $this->thumbstatus == 1 || $this->thumbstatus == 3 ? 1 : 0;
			}
		}
	}

	function Watermark_GD($watermark_file) {

		if($this->watermarkstatus && function_exists('imagecopy') && function_exists('imagealphablending') && function_exists('imagecopymerge')) {
			$imagecreatefromfunc = $this->imagecreatefromfunc;
			$imagefunc = $this->imagefunc;
			list($img_w, $img_h) = $this->attachinfo;
			if($this->watermarktype < 2) {
				$watermark_file = $this->watermarktype == 1 ? LOCAL_PATH.'/images/common/watermark.png' : LOCAL_PATH.'/images/common/watermark.gif';
				$watermarkinfo	= @getimagesize($watermark_file);
				$watermark_logo	= $this->watermarktype == 1 ? @imageCreateFromPNG($watermark_file) : @imageCreateFromGIF($watermark_file);
				if(!$watermark_logo) {
					return;
				}
				list($logo_w, $logo_h) = $watermarkinfo;
			} else {
				$watermarktextcvt = pack("H*", $watermarktext['text']);
				$box = imagettfbbox($watermarktext['size'], $watermarktext['angle'], $watermarktext['fontpath'], $watermarktextcvt);
				$logo_h = max($box[1], $box[3]) - min($box[5], $box[7]);
				$logo_w = max($box[2], $box[4]) - min($box[0], $box[6]);
				$ax = min($box[0], $box[6]) * -1;
   				$ay = min($box[5], $box[7]) * -1;
			}
			$wmwidth = $img_w - $logo_w;
			$wmheight = $img_h - $logo_h;

			if(($this->watermarktype < 2 && is_readable($watermark_file) || $this->watermarktype == 2) && $wmwidth > 10 && $wmheight > 10 && !$this->animatedgif) {
				switch($this->watermarkstatus) {
					case 1:
						$x = +5;
						$y = +5;
						break;
					case 2:
						$x = ($img_w - $logo_w) / 2;
						$y = +5;
						break;
					case 3:
						$x = $img_w - $logo_w - 5;
						$y = +5;
						break;
					case 4:
						$x = +5;
						$y = ($img_h - $logo_h) / 2;
						break;
					case 5:
						$x = ($img_w - $logo_w) / 2;
						$y = ($img_h - $logo_h) / 2;
						break;
					case 6:
						$x = $img_w - $logo_w;
						$y = ($img_h - $logo_h) / 2;
						break;
					case 7:
						$x = +5;
						$y = $img_h - $logo_h - 5;
						break;
					case 8:
						$x = ($img_w - $logo_w) / 2;
						$y = $img_h - $logo_h - 5;
						break;
					case 9:
						$x = $img_w - $logo_w - 5;
						$y = $img_h - $logo_h - 5;
						break;
				}

				$dst_photo = imagecreatetruecolor($img_w, $img_h);
				$target_photo = @$imagecreatefromfunc($this->targetfile);
				imageCopy($dst_photo, $target_photo, 0, 0, 0, 0, $img_w, $img_h);

				if($this->watermarktype == 1) {
					imageCopy($dst_photo, $watermark_logo, $x, $y, 0, 0, $logo_w, $logo_h);
				} elseif($this->watermarktype == 2) {
					if(($watermarktext['shadowx'] || $watermarktext['shadowy']) && $watermarktext['shadowcolor']) {
						$shadowcolorrgb = explode(',', $watermarktext['shadowcolor']);
						$shadowcolor = imagecolorallocate($dst_photo, $shadowcolorrgb[0], $shadowcolorrgb[1], $shadowcolorrgb[2]);
						imagettftext($dst_photo, $watermarktext['size'], $watermarktext['angle'], $x + $ax + $watermarktext['shadowx'], $y + $ay + $watermarktext['shadowy'], $shadowcolor, $watermarktext['fontpath'], $watermarktextcvt);
					}
					$colorrgb = explode(',', $watermarktext['color']);
					$color = imagecolorallocate($dst_photo, $colorrgb[0], $colorrgb[1], $colorrgb[2]);
					imagettftext($dst_photo, $watermarktext['size'], $watermarktext['angle'], $x + $ax, $y + $ay, $color, $watermarktext['fontpath'], $watermarktextcvt);
				} else {
					imageAlphaBlending($watermark_logo, true);
					imageCopyMerge($dst_photo, $watermark_logo, $x, $y, 0, 0, $logo_w, $logo_h, $this->watermarktrans);
				}

				$targetfile = !$preview ? $this->targetfile : ROOT_PATH.'/cache/watermark_temp.jpg';
				clearstatcache();
				if($this->attachinfo['mime'] == 'image/jpeg') {
					$imagefunc($dst_photo, $targetfile, $this->watermarkquality);
				} else {
					$imagefunc($dst_photo, $targetfile);
				}

				$this->attach['size'] = filesize($targetfile);
			}
		}
	}

	function Thumb_IM($thumbwidth, $thumbheight) {
		if(!$this->animatedgif) {
			$targetfile = $this->thumbstatus == 1 || $this->thumbstatus == 3 ? $this->targetfile.'.jpg' : $this->targetfile;
		}else{
			$targetfile = $this->thumbstatus == 1 || $this->thumbstatus == 3 ? $this->targetfile.'.gif' : $this->targetfile;
		}		
		if($this->thumbstatus) {
			list($img_w, $img_h) = $this->attachinfo;
			
			if($img_w >= $thumbwidth || $img_h >= $thumbheight){
				if($this->thumbstatus != 3) {
					$exec_str = $this->imageimpath.'/convert -quality '.intval($this->thumbquality).' -geometry '.$thumbwidth.'x'.$thumbheight.' '.$this->srcfile.' '.$targetfile;
					@exec($exec_str, $output, $return);
					if(empty($return) && empty($output)) {
						$this->attach['thumb'] = $this->thumbstatus == 1 ? 1 : 0;
					}
				} else {
					$imgratio = $img_w / $img_h;
					$thumbratio = $thumbwidth / $thumbheight;

					if($imgratio >= 1 && $imgratio >= $thumbratio || $imgratio < 1 && $imgratio > $thumbratio) {
						$cuty = $img_h;
						$cutx = $cuty * $thumbratio;
					} elseif($imgratio >= 1 && $imgratio <= $thumbratio || $imgratio < 1 && $imgratio < $thumbratio) {
						$cutx = $img_w;
						$cuty = $cutx / $thumbratio;
					}
					$exec_str = $this->imageimpath.'/convert -crop '.$cutx.'x'.$cuty.'+0+0  '.$this->srcfile.' '.$targetfile;
					@exec($exec_str, $output, $return);
					
					$exec_str = $this->imageimpath.'/convert -quality '.intval($this->thumbquality).' -geometry '.$thumbwidth.'x'.$thumbheight.' '.$targetfile.' '.$targetfile;
					@exec($exec_str, $output, $return);
					if(empty($return) && empty($output)) {
						$this->attach['thumb'] = $this->thumbstatus == 1 || $this->thumbstatus == 3 ? 1 : 0;
					}
				}
			}
			else{
				return false;
			}
		}else{
			return false;
		}
		return $targetfile;
	}

	function Watermark_IM($watermark_file) {
		//global $actinfo;
		switch($this->watermarkstatus) {
			case 1:
				$gravity = 'NorthWest';
				break;
			case 2:
				$gravity = 'North';
				break;
			case 3:
				$gravity = 'NorthEast';
				break;
			case 4:
				$gravity = 'West';
				break;
			case 5:
				$gravity = 'Center';
				break;
			case 6:
				$gravity = 'East';
				break;
			case 7:
				$gravity = 'SouthWest';
				break;
			case 8:
				$gravity = 'South';
				break;
			case 9:
				$gravity = 'SouthEast';
				break;
		}

		$watermarktype = strrchr($watermark_file,'.');

		$targetfile = $this->targetfile;
		
		$exec_str = $this->imageimpath.'/composite'.
			($watermarktype != '.png' && $this->watermarktrans != '100' ? ' -watermark '.$this->watermarktrans.'%' : '').
			' -quality '.$this->watermarkquality.
			' -gravity '.$gravity.
			' '.$watermark_file.' '.$this->srcfile.' '.$targetfile;
	
		@exec($exec_str, $output, $return);
		if(empty($return) && empty($output)) {
			$this->attach['size'] = filesize($this->targetfile);
		}
		return $this->targetfile;
	}

}