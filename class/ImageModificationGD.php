<?php
	/**
	* Класс для работы с изображениями с использованием GD
	* Работает с форматами jpeg, jpg, gif, png
	* 
	* rotateImage 		- поворот картинки на заданный угол
	* rotatePngImage 	- поворот PNG картинки с сохранением прозрачности
	* resizePhoto		- пропорциональный ресайз картинки
	* resizePngPhoto	- пропорциональный ресайз PNG картинки с сохранением прозрачности
	* overlayImage		- наложение картинки на фон
	* addWatermark		- добавление водяного знака
	* saveImage			- сохранить/вывести на экран картинку
	*
	* -------------------------------
	* @version   0.1
	* @author    Ipatov Evgeniy <admin@ipatov-soft.ru>
	*
	*/
	class ImageModificationGD {
		
		var $image;
		var $imagePath;
		var $imageWidth;
		var $imageHeight;
		var $imageSize;
		private $imgCreateFunction;
		
		// Errors
		private $error_fileNotFound = 'File not found';
		private $error_notResource = 'var $image not resource';
		
		public function __construct(){
			// construct
			if(!($this->_checkGD())) die('GD not installed'); // проверка установленного GD
		}
		
		// загрузка картинки
		public function loadImage($filePath){
			if (!file_exists($filePath)) die($this->error_fileNotFound);
			$imgCreateFunc = $this->_getFunctionLoadImage($filePath);
			if(!$imgCreateFunc) return false;
			$this->imgCreateFunction = $imgCreateFunc;
			$this->imageSize = getimagesize($filePath);
			$this->imagePath = $filePath;
			$this->image = $imgCreateFunc($filePath);
			$this->_setImageSize();
			// @todo если png, то сделать прозрачность
			return $this->image;
		}
		
		// поворот картинки
		public function rotateImage($degree, $background = 0xffffff, $image = false){
			if(!$image) $image = $this->image;
			if(gettype($image) != 'resource') die($this->error_notResource);
			$rotate = imagerotate($image, $degree, $background);
			$this->image = $rotate;
			$this->_setImageSize();
			return $rotate;
		}
		
		// поворот PNG картинки, с сохранением прозрачности
		public function rotatePngImage($degree, $image = false){
			if(!$image) $image = $this->image;
			if(gettype($image) != 'resource') die($this->error_notResource);
			// задаем картинке прозрачность
			imagealphablending($image, true); 
			imagesavealpha($image, true); 
			// создаем прозрачный фон
			$background = imagecolorallocatealpha($image, 0, 0, 0, 127);			
			$rotate = imagerotate($image, $degree, $background);
			// задаем прозрачность для повернутой картинки
			imagealphablending($rotate, true); 
			imagesavealpha($rotate, true);   
			$this->image = $rotate;
			$this->_setImageSize();
			return $rotate;
		}		
		
		// сохранение картинки
		public function saveImage($filePath, $name, $format = "jpg"){
			$imgSaveFunc = $this->_getFunctionSaveImage($format);
			if(!$imgSaveFunc) return false;
			$result = @imagepng($this->image, $filePath.$name.'.'.$format);
			return $result;
		}
		
		// зесайз картинки
		public function resizePhoto($height, $width, $rgb = 0xffffff, $image = false){
			if(!$image) $image = $this->image;
			if(gettype($image) != 'resource') die($this->error_notResource);		
			$size[0] = imagesx($image);
			$size[1] = imagesy($image);	
			
			$xRatio = $width / $size[0];
			$yRatio = $height / $size[1];
			$ratio = min($xRatio, $yRatio);
			$kRatio = ($xRatio == $ratio); //соотношения ширины к высоте
			$newWidth   = $kRatio  ? $width  : floor($size[0] * $ratio);
			$newHeight  = !$kRatio ? $height : floor($size[1] * $ratio);
			$newLeft = $kRatio  ? 0 : floor(($width - $newWidth) / 2);
			//расхождение с заданными параметрами по высоте
			$new_top = !$kRatio ? 0 : floor(($height - $newHeight) / 2);
			$img = imagecreatetruecolor($width, $height);
			imagefill($img, 0, 0, $rgb);			
			imagecopyresampled($img, $image, $newLeft, $new_top, 0, 0, $newWidth, $newHeight, $size[0], $size[1]);
			$this->image = $img;
			$this->_setImageSize();
			return $img;
		}
		
		// зесайз png картинки
		public function resizePngPhoto($height, $width, $image = false){
			if(!$image) $image = $this->image;
			if(gettype($image) != 'resource') die($this->error_notResource);	
			$size[0] = imagesx($image);
			$size[1] = imagesy($image);			
			$xRatio = $width / $size[0];
			$yRatio = $height / $size[1];
			$ratio = min($xRatio, $yRatio);
			$kRatio = ($xRatio == $ratio); //соотношения ширины к высоте
			$newWidth   = $kRatio  ? $width  : floor($size[0] * $ratio);
			$newHeight  = !$kRatio ? $height : floor($size[1] * $ratio);
			$newLeft = $kRatio  ? 0 : floor(($width - $newWidth) / 2);
			//расхождение с заданными параметрами по высоте
			$new_top = !$kRatio ? 0 : floor(($height - $newHeight) / 2);
			$img = imagecreatetruecolor($width, $height);
			// делаем его прозрачным
			imagealphablending($img, false); 
			imagesavealpha($img, true);
			//imagefill($img, 0, 0, $rgb);			
			imagecopyresampled($img, $image, $newLeft, $new_top, 0, 0, $newWidth, $newHeight, $size[0], $size[1]);
			$this->image = $img;
			$this->_setImageSize();
			return $img;
		}
		
		// наложение одной картинки на другую
		public function overlayImage($imageX, $imageY, $imgFon, $image = false){
			if(!$image) $image = $this->image;
			if(gettype($image) != 'resource') die($this->error_notResource);
			if (!file_exists($imgFon)) die($this->error_fileNotFound);
			$width = imagesx($image);
			$height = imagesy($image);
			$imgCreateFunc = $this->_getFunctionLoadImage($imgFon);
			if(!$imgCreateFunc) return false;
			$imageFon = $imgCreateFunc($imgFon);
			imagealphablending($image, false);		
			imagesavealpha($image, true);
			imagecopy($imageFon, $image, $imageX, $imageY, 0, 0, imagesx($image), imagesy($image));	
			$this->image = $imageFon;
			$this->_setImageSize();
			imagedestroy($image);
			return $this->image;
		}
		
		// наложение одной картинки на другую
		public function addWatermark($imageX, $imageY, $watermark, $image = false){
			if(!$image) $image = $this->image;
			if(gettype($image) != 'resource') die($this->error_notResource);
			if (!file_exists($watermark)) die($this->error_fileNotFound);
			$width = imagesx($image);
			$height = imagesy($image);
			$imgCreateFunc = $this->_getFunctionLoadImage($watermark);
			if(!$imgCreateFunc) return false;
			$imgWatermark = $imgCreateFunc($watermark);
			imagealphablending($imgWatermark, false);		
			imagesavealpha($imgWatermark, true);
			imagecopy($image, $imgWatermark, $imageX, $imageY, 0, 0, imagesx($imgWatermark), imagesy($imgWatermark));	
			$this->image = $image;
			$this->_setImageSize();
			imagedestroy($imgWatermark);
			return $this->image;
		}
		
		// очистка памяти
		public function destroy(){
			imagedestroy($this->image);
			unset($this->image);
		}
		
		// получение функции для загрузки изображения
		private function _getFunctionLoadImage($filePath){
			// @todo сделать функцию для BMP
			$arrFile = explode(".", $filePath);
			$format = end($arrFile);
			if($format == "jpg") $format = "jpeg";
			$imgCreatefunc = "imagecreatefrom" . $format;
			$this->imagecreatefrombmp($filePath);
			if (!function_exists($imgCreatefunc)){
				return false; 
			}else{
				return $imgCreatefunc;
			}
		}
		
		// получение функции для сохранения изображения
		private function _getFunctionSaveImage($format = "jpg"){
			// @todo сделать функцию для BMP
			// jpeg, png, gif
			if($format == "jpg") $format = "jpeg";
			$imgSavefunc = "image" . $format;
			if (!function_exists($imgSavefunc)){
				return false; 
			}else{
				return $imgSavefunc;
			}
		}
		
		private function _checkGD(){
			if (!extension_loaded('gd')) {
				if (!dl('gd.so')) {
					return false;
				}
			}
			return true;
		}
		
		private function _setImageSize(){
			$this->imageWidth = imagesx($this->image);
			$this->imageHeight = imagesy($this->image);
		}
		
		// загрузка картинки в формате BMP
		public function imagecreatefrombmp($path) {
			//var_dump(method_exists($this, 'imagecreatefrombmp'));exit;
			// Loading image from bitmap 
			if ($file = fopen($path, 'rb')) {
				$meta = unpack('vmagic/V4/Vwidth/Vheight/v/vbpp/V/Vlength/V4', fread($file, 54)); 
				if ($meta['magic'] == 0x4d42 && $meta['bpp'] == 24) $data = fread($file, $meta['length']); 
				fclose($file); 
				if (isset($data)) { 
					$image = ImageCreateTrueColor($meta['width'], $meta['height']); 
					for ($y = $meta['height'] - 1, $ptr = 0; $y > -1; --$y) { 
						for ($x = 0; $x < $meta['width']; ++$x, $ptr += 3) { 
							list(, $r, $g, $b) = unpack('C3', substr($data, $ptr, 3)); 
							ImageSetPixel($image, $x, $y, $b << 16 | $g << 8 | $r); 
						} 
					} 
					return $image; 
				} 
			} 
			return false; 
		}
		
	}