<?
class ModelToolImage extends Model {
  public function resizeFormat($filename, $width, $height, $isWatermark = false) {
    if ($filename == 'placeholder.png') $isWatermark = false;
    $fullpathOld = DIR_IMAGE . $filename;
    if (!is_file($fullpathOld)) return;

    $uri = "{$this->config->get('config_' . ($this->request->server['HTTPS'] ? 'ssl' : 'url'))}image/";

    $file = pathinfo($filename);
    $sufixFileName = $width && $height ? "-{$width}x{$height}" : '';
    $pathCache = "cache/{$file['dirname']}/{$file['filename']}{$sufixFileName}";
    // $pathWEBP = "{$pathCache}.webp";
    $pathJPEG = "{$pathCache}.jpeg";
    // $fullpathWEBP = DIR_IMAGE . $pathWEBP;
    $fullpathJPEG = DIR_IMAGE . $pathJPEG;

    if (!is_file($fullpathJPEG) || filemtime($fullpathOld) > filemtime($fullpathJPEG)) {
    //   || !is_file($fullpathWEBP) || filemtime($fullpathOld) > filemtime($fullpathWEBP)) {
      $info = getimagesize($fullpathOld);
      $mime = $info['mime'] ?? '';

      $dirNew = dirname($fullpathJPEG);
      if (!is_dir($dirNew)) mkdir($dirNew, 0777, true);

      if ($mime == 'image/gif') $image = imagecreatefromgif($fullpathOld);
      elseif ($mime == 'image/png') $image = imagecreatefrompng($fullpathOld);
      elseif ($mime == 'image/jpeg') $image = imagecreatefromjpeg($fullpathOld);

      $widthOrigin = $info[0];
      $heightOrigin = $info[1];

      if (!$width) $width = $widthOrigin;
      if (!$height) $height = $heightOrigin;

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

      if ($isWatermark) {
        $imageStamp = imagecreatefrompng(DIR_IMAGE . 'stamp.png');

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

  public function resize($filename, $width, $height, $isWatermark = false) {
    // return $this->resizeFormat($filename, $width, $height, $isWatermark)[1];
    return $this->resizeFormat($filename, $width, $height, $isWatermark);
  }
}
