<?
class ModelToolImage {
  public function resizeFormat($filename, $width, $height, $isWatermark = false) {
    if ($filename == 'placeholder.png') $isWatermark = false;
    $isResize = $width && $height;
    $fullpathOld = DIR_IMAGE . $filename;
    if (!is_file($fullpathOld)) return;

    $isCron = strpos($_SERVER['REQUEST_URI'], '/crons/');
    $domain = $_SERVER['HTTPS'] || $isCron ? HTTPS_SERVER : HTTP_SERVER;
    $uri = "{$domain}image/";

    $file = pathinfo($filename);
    $sizeFilename = $isResize ? "-{$width}x{$height}" : '';
    $wtFilename = $isWatermark ? '-w' : '';
    $fileDirname = $file['dirname'] === '.' ? '' : "{$file['dirname']}/";
    $pathCache = "cache/{$fileDirname}{$file['filename']}{$sizeFilename}{$wtFilename}";
    // $pathWEBP = "{$pathCache}.webp";
    $pathJPEG = "{$pathCache}.jpeg";
    // $fullpathWEBP = DIR_IMAGE . $pathWEBP;
    $fullpathJPEG = DIR_IMAGE . $pathJPEG;

    // if (true) {
    if (!is_file($fullpathJPEG) || filemtime($fullpathOld) > filemtime($fullpathJPEG)) {
    //   || !is_file($fullpathWEBP) || filemtime($fullpathOld) > filemtime($fullpathWEBP)) {
      $info = getimagesize($fullpathOld);
      $mime = $info['mime'] ?? '';

      $dirNew = dirname($fullpathJPEG);
      if (!is_dir($dirNew)) mkdir($dirNew, 0777, true);

      if ($mime == 'image/gif') $image = imagecreatefromgif($fullpathOld);
      elseif ($mime == 'image/png') $image = imagecreatefrompng($fullpathOld);
      elseif ($mime == 'image/jpeg') $image = imagecreatefromjpeg($fullpathOld);

      $imageNew = $image;
      $widthOrigin = $info[0];
      $heightOrigin = $info[1];

      if ($isResize) {
        $scaleW = $width / $widthOrigin;
        $scaleH = $height / $heightOrigin;
        $scale = min($scaleW, $scaleH);

        $newWidth = $widthOrigin * $scale;
        $newHeight = $heightOrigin * $scale;
        $xpos = ($width - $newWidth) / 2;
        $ypos = ($height - $newHeight) / 2;

        $imageNew = imagecreatetruecolor($width, $height);
        $background = imagecolorallocate($imageNew, 255, 255, 255);
        imagefilledrectangle($imageNew, 0, 0, $width, $height, $background);

        imagecopyresampled($imageNew, $image, $xpos, $ypos, 0, 0,
          $newWidth, $newHeight, $widthOrigin, $heightOrigin);

        imagedestroy($image);
      } else {
        $width = $widthOrigin;
        $height = $heightOrigin;
      }

      if ($isWatermark) {
        $isSizeNormal = $width == 1024 && $height == 1024;
        $imageStamp = imagecreatefrompng(DIR_IMAGE . 'stamp.png');

        if ($isSizeNormal) {
          imagecopy($imageNew, $imageStamp, 0, 0, 0, 0, $width, $height);
        } else {
          $widthOriginStamp = imagesx($imageStamp);
          $heightOriginStamp = imagesy($imageStamp);

          $scaleWStamp = $width / $widthOriginStamp;
          $scaleHStamp = $height / $heightOriginStamp;
          $scaleStamp = min($scaleWStamp, $scaleHStamp);

          $newWidthStamp = $widthOriginStamp * $scaleStamp;
          $newHeightStamp = $heightOriginStamp * $scaleStamp;
          $xposStamp = ($width - $newWidthStamp) / 2;
          $yposStamp = ($height - $newHeightStamp) / 2;

          imagecopyresampled($imageNew, $imageStamp, $xposStamp, $yposStamp, 0, 0,
            $newWidthStamp, $newHeightStamp, $widthOriginStamp, $heightOriginStamp);
        }

        imagedestroy($imageStamp);
      }

      imagejpeg($imageNew, $fullpathJPEG, 90);
      // imagewebp($imageNew, $fullpathWEBP, 90);
      imagedestroy($imageNew);
    }

    // $pathWEBP = str_replace(' ', '%20', $pathWEBP);
    $pathJPEG = str_replace(' ', '%20', $pathJPEG);

    // return ["{$uri}{$pathWEBP}", "{$uri}{$pathJPEG}"];
    return "{$uri}{$pathJPEG}";
  }

  public function resize($filename, $width = 0, $height = 0, $isWatermark = false) {
    // return $this->resizeFormat($filename, $width, $height, $isWatermark)[1];
    return $this->resizeFormat($filename, $width, $height, $isWatermark);
  }
}
