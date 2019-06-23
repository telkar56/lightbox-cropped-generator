<?php

class LightboxPreparator {

	private $sizeFinal;
	public static $imagesTypes = array('jpg', 'JPG', 'jpeg', 'JPEG', 'png', 'PNG', 'gif', 'GIF', 'svg', 'SVG'); 

	function __construct ($sizeFinal, $picturesDir, $csvAssociation, $recalculateAll = true) {
		$this->config = parse_ini_file("config_lightbox_preparator.ini", true);
		$this->sizeFinal = $sizeFinal;

		$handle = opendir($picturesDir);
		if ($handle) {
			while (($file = readdir($handle)) !== false) {
				if ($file != '.' && $file != '..') {
					$info = new SplFileInfo($file);
					$generalDir = $picturesDir.'/'.$info->getPathname();

					$csvAssociation ? $csvHandle = $this->createAssociatedImagesCsv($generalDir) : $csvHandle = false;

					$inputDir = $generalDir.'/'.$info->getPathname().'-'.$this->config['InputFolderCode'];
					$outputDir = $generalDir.'/'.$info->getPathname().'-'.$this->config['OutputFolderCode'];

					$secondHandle = opendir($inputDir);
					/* si répertoire orig contenant les images existe */
					if ($secondHandle) {
						$this->analyseOutputFolders($outputDir);
						while (($file = readdir($secondHandle)) !== false) {
							if ($file != '.' && $file != '..') {
								$info = new SplFileInfo($file);
								$this->resizeAndDuplicate($file, $inputDir, $outputDir, $csvHandle);
							}
						}
					}

					$csvAssociation ? fclose($csvHandle) : false;
				}
			}
		}
	}

	function analyseOutputFolders ($outputDir) {	

		$thumbDir = $outputDir.'/'.$this->config['OutputFolderCode-thumb'];
		$fullDir = $outputDir.'/'.$this->config['OutputFolderCode-full'];

		if (!file_exists($outputDir)) {
			echo "pas de répertoire général lightbox (".$outputDir."), création...\n";
			mkdir($outputDir, 0755);
		}
		if (!file_exists($thumbDir)) {
			echo "pas de sous-répertoire thumbnails (".$thumbDir."), création....\n";
			mkdir($thumbDir, 0755);
		}
		if (!file_exists($fullDir)) {
			echo "pas de dossier de destination fullsize (".$fullDir."), création...\n";
			mkdir($fullDir, 0755);
		}
	}
		
	function resizeAndDuplicate ($image, $inputDir, $outputDir, $csvHandle) {

		$info = new SplFileInfo($image);
		$ext = $info->getExtension();
		if (in_array($ext, self::$imagesTypes)) {
			echo "traitement de ".$image."\n";

			$imgSize = getimagesize($inputDir.'/'.$image);
			$width = $imgSize[0];
			$height = $imgSize[1];
			$imgRatio = $width/$height;
			$imgType = explode('/', $imgSize['mime'])[1];

			$imgCreateAlias = 'imagecreatefrom'.$imgType;
			$imageSaveAlias = 'image'.$imgType;

			$loadedImage = $imgCreateAlias($inputDir.'/'.$image);
			$newImage = $this->buildResizedCroppedImage($width, $height, $imgRatio, $loadedImage);

			$imageSaveAlias($newImage, $outputDir.'/'.$this->config['OutputFolderCode-thumb'].'/'.explode('.', $info->getFilename())[0].'-square'.'.'.$ext, 80);
			$imageSaveAlias($loadedImage, $outputDir.'/'.$this->config['OutputFolderCode-full'].'/'.explode('.', $info->getFilename())[0].'-fullsize'.'.'.$ext, 80);

			$this->fillAssociatedImagesCsv($csvHandle, $info);
		}

	}

	function buildResizedCroppedImage ($width, $height, $imgRatio, $loadedImage) {
		$newImage = imagecreatetruecolor($this->sizeFinal, $this->sizeFinal);

		// selon le ratio on prend le carré le plus large possible de l'image d'origine
		if ($imgRatio < 1) {
			// si image verticale -> le côté du carré est déterminé par la largeur
			// (puisque moins de largeur que de hauteur)
			$size = $width;
			// uniquement besoin de calculer à partir de quand on coupe verticalement
			// puisqu'on prend tout horizontalement
			$srcY = (($height/2)-($size/2));
			$tmp = imagecreatetruecolor($size, $size);
			imagecopyresampled($tmp, $loadedImage, 0, 0, 0, $srcY, $size, $size, $size, $size);
		}
		else if ($imgRatio > 1) {
			// si image horizontale -> le côté du carré est déterminé par la hauteur
			// (puisque moins de hauteur que de largeur)
			$size = $height;
			// uniquement besoin de calculer à partir de quand on coupe horizontalement
			// puisqu'on prend tout verticalement
			$srcX = (($width/2)-($size/2));
			$tmp = imagecreatetruecolor($size, $size);
			imagecopyresampled($tmp, $loadedImage, 0, 0, $srcX, 0, $size, $size, $size, $size);
		}

		// l'image carrée obtenue est réduite à la dimension finale
		imagecopyresampled($newImage, $tmp, 0, 0, 0, 0, $this->sizeFinal, $this->sizeFinal, $size, $size);

		return $newImage;
	}

	function createAssociatedImagesCsv ($generalDir) {
		if (!file_exists($generalDir.'/'.$this->config['csvFilename'])) {
			file_put_contents($generalDir.'/'.$this->config['csvFilename'].'.csv', "");
		}
		$csvHandle = fopen($generalDir.'/'.$this->config['csvFilename'].'.csv', 'w');
		fputcsv($csvHandle, array($this->config['csvSquaresizeColumn'], $this->config['csvFullsizeColumn']), ';');

		return $csvHandle;
	}

	function fillAssociatedImagesCsv ($csvHandle, $fileInfo) {
		$ext = $fileInfo->getExtension();
		$filename = $fileInfo->getFilename();
		fputcsv(
			$csvHandle,
			array(
				explode('.', $filename)[0].'-square'.'.'.$ext, 
				explode('.', $filename)[0].'-fullsize'.'.'.$ext
			),
			';'
		);
	}

}

$test = new LightboxPreparator(310, '../photos', true);
// $test->resizeAndDuplicate(true);

?>