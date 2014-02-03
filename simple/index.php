<?php	
	include "../class/ImageModificationGD.php";

	$imgMod = new ImageModificationGD();
	// загрузка картинки
	$resultLoad = $imgMod->loadImage('images/1_png.png');
	if($resultLoad){
		//$r = $imgMod->loadImage('test_png.png');
		//header('Content-Type: image/png');
		//imagepng($r);
		
		// поворот картиинки
		//$imageegree = 25;
		//$result = $imgMod->rotateImage($imageegree);
		
		// поворот PNG картинки с прозрачностью
		//$imageegree = -45;
		//$result = $imgMod->rotatePngImage($imageegree);
		
		// ресайз картинки до 100х100
		//$result = $imgMod->resizePhoto(100, 100, 0xffffff, 'images/1_png.png');
		
		
		// поворот
		//$imageegree = 45;
		//$result = $imgMod->rotateImage($imageegree);
		
		// ресайз картинки до 100х100
		//$result = $imgMod->resizePhoto(100, 100);
		//$result = $imgMod->resizePhoto(100, 100, 0xffffff, 'images/1_png.png');
		
		// ресайз картинки с сохранением прозрачности до 100х100
		//$result = $imgMod->resizePngPhoto(100, 100);
		//$result = $imgMod->resizePngPhoto(100, 100, 'images/1_png.png');
		// поворот
		
		// наложение картинки на фон
		//$result = $imgMod->overlayImage(100, 200, 'images/1_jpeg.jpg');
		
		// наложение водяного знака
		$result = $imgMod->addWatermark(100, 50, 'images/news_50x50.png');
		
		header('Content-Type: image/png');
		imagepng($imgMod->image);
		
		// сохранение картинки
		//$r = $imgMod->saveImage('./', 'test_jpeg', 'jpg');
		
		// вывод в браузере
		//header('Content-Type: image/png');
		//imagepng($result);
		
		// Очистка памяти от картинки
		$imgMod->destroy();		
	}