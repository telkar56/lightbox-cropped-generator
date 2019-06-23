<?php

class PaginatedImgTable {

	private $picturesInputDirectory;
	private $csvColumns;
	private $csvFile;
	private $config;
	private $imagePerPage = 12;

	function __construct ($csvFile, $picturesInputDirectory) {
		$this->config = parse_ini_file("config_paginated_img_table.ini", true);
		$this->csvFile = $csvFile;
		$this->picturesInputDirectory = $picturesInputDirectory;
		$this->csvColumns = $this->config['csvColumns'];
	}

	function getFolderImages ($picturesInputDirectory, $imgFolder) {
		$images = array();
		$imagesTypes = array('jpg', 'JPG', 'png', 'PNG', 'gif', 'GIF', 'svg', 'SVG');
		$handle = opendir($picturesInputDirectory.'/'.$imgFolder);
	
		if ($handle) {
			while (($file = readdir($handle)) !== false) {
				$info = new SplFileInfo($file);
				$ext = $info->getExtension();
				if ($file != '.' && $file != '..') {
					if (in_array($ext, $imagesTypes)) {
						array_push($images, $file);
					}
				}
			}
		}
		return $images;
	}

	function renderImages ($lighboxAssociation) {
		$imgs = $this->getFolderImages($this->picturesInputDirectory, $this->config['directories']['squaresizeIntputFolder']);
		$imgQuantity = sizeOf($imgs);
		$pageNumber = $this->evaluatePaging();

		// protection pour éviter un numéro de page en dehors de la bonne plage
		if ($imgQuantity < ($pageNumber*($this->imagePerPage-1))) {
			$pageNumber = ceil($imgQuantity/$this->imagePerPage);
		}
		$selectedImgs = array_slice($imgs, $this->imagePerPage*($pageNumber-1),  $this->imagePerPage);

		echo $this->getNavigation($pageNumber, $imgQuantity);
		if ($lighboxAssociation) {
			$lighboxImgsArray = $this->checkAndBuildCsvAssociation($selectedImgs, $this->getCsvItems());
			echo $this->buildLightboxImgsCanvas($lighboxImgsArray);
		}
		else {
			echo $this->buildClassicImgsCanvas($selectedImgs);
		}
		echo $this->getNavigation($pageNumber, $imgQuantity);
		
	}

	function evaluatePaging () {
		if (!empty($_GET['page']) && is_numeric($_GET['page'])) {
			$pageNumber = $_GET['page'];
			if ($pageNumber < 0) {
				$pageNumber = 1;
			}
		}
		else {
			$pageNumber = 1;
		}
		return $pageNumber;
	}

	function buildClassicImgsCanvas ($imgs) {
		$html = '<div class="img-canvas-classic">';
		foreach ($imgs as $img) {
			$html .= '<img src="'.$this->picturesInputDirectory.'/'.$this->config['directories']['squaresizeIntputFolder'].'/'.$img.'" />';
		}
		$html .= '</div>';
		return $html;
	}

	function buildLightboxImgsCanvas ($imgs) {
		$html = '<div class="img-canvas-lightbox row">';
		foreach ($imgs as $img) {
			if (!empty($img[$this->config['csvFullsizeColumn']])) {
				$html .= '<div class="fancybox-wrapper col-6 col-sm-4"><a class="fancybox" data-fancybox="images" href="'.$this->picturesInputDirectory.'/'.$this->config['directories']['fullsizeIntputFolder'].'/'.$img[$this->config['csvFullsizeColumn']].'"><img src="'.$this->picturesInputDirectory.'/'.$this->config['directories']['squaresizeIntputFolder'].'/'.$img[$this->config['csvSquaresizeColumn']].'" alt="" /></a></div>';
			}
			else {
				$html .= '<div><img src="'.$this->picturesInputDirectory.'/'.$this->config['directories']['squaresizeIntputFolder'].'/'.$img[$this->config['csvSquaresizeColumn']].'" alt="" /></div>';
			}
		}
		$html .= '</div>';
		return $html;
	}

	function getNavigation ($pageNumber, $imgQuantity) {
		$html = '<div class="navigation"><span>Page : </span>';
		for ($i=1; $i <= ceil($imgQuantity/$this->imagePerPage); $i++) { 
			if ($i == $pageNumber) {
				$html .= '<a class="active" href="'.$_SERVER['PHP_SELF'].'?page='.($i).'">'.($i).'</a>';
			}
			else {
				$html .= '<a href="'.$_SERVER['PHP_SELF'].'?page='.($i).'">'.($i).'</a>';
			}
		}
		$html .= '</div>';
		return $html;
	}

	function checkAndBuildCsvAssociation ($physicalImgs, $csvItems) {
		$finalAssociatedImgs = array();
		$index = 0;
		foreach ($physicalImgs as $img) {
			$finalAssociatedImgs[$index] = array();
			$finalAssociatedImgs[$index][$this->config['csvSquaresizeColumn']] = $img;

			$indexFound = array_search($img, array_column($csvItems, $this->config['csvSquaresizeColumn']));
			if ($indexFound !== false) {
				$imgFull = $csvItems[$indexFound][$this->config['csvFullsizeColumn']];
				if (!empty($imgFull) && file_exists($this->picturesInputDirectory.'/'.$this->config['directories']['fullsizeIntputFolder'].'/'.$imgFull) ) {
					$finalAssociatedImgs[$index][$this->config['csvFullsizeColumn']] = $imgFull;
				}
			}
			$index++;
		}
		return $finalAssociatedImgs;
	}

	function getCsvItems() {
		$row = 0;
		$csvData = array();
		$csvSquaresizeColumn = $this->config['csvSquaresizeColumn'];
		$csvFullsizeColumn = $this->config['csvFullsizeColumn'];

		if (($handle = fopen($this->csvFile, "r")) !== FALSE) {
		    while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
		        $num = count($data);
		        if (!isset($csvData[$row])) {
		        	$csvData[$row] = array();
		        }
		        if ($row !== 0) {
		        	// $csvData[0][$c] correspond au titre de la colonne, pour obtenir un array associatif
		        	for ($c=0; $c < $num; $c++) {
			            $csvData[$row][$csvData[0][$c]] = $data[$c];
			        }
		        }
		        else {
		        	// on stock dans un array indexé les titres des colonnes du CVS, juste pour référence
		        	for ($c=0; $c < $num; $c++) {
			            array_push($csvData[$row], $data[$c]);
			        }
		        }
		        $row++;
		    }
		    fclose($handle);
		}

		// on enlève la référence des colonnes du CSV
		// print_r($csvData);
		array_shift($csvData);

		$finalSortedArray = array();
		$index = 0;
		foreach ($csvData as $rowArray) {
			if (!empty($rowArray[$csvSquaresizeColumn]) && !empty($rowArray[$csvFullsizeColumn])) {
				$finalSortedArray[$index] = array();
				$finalSortedArray[$index][$csvSquaresizeColumn] = $rowArray[$csvSquaresizeColumn];
				$finalSortedArray[$index][$csvFullsizeColumn] = $rowArray[$csvFullsizeColumn];
				foreach ($this->csvColumns as $column) {
					if (!empty($rowArray[$column])) {
						$finalSortedArray[$index][$column] = $rowArray[$column];	
					}
				}
			}
			$index ++;
		}
		// print_r($finalSortedArray);
		return $finalSortedArray;
	}
}

// $test = new PaginatedImgTable ('../photos/bonsais');
//$test->getCsvItems();
// print_r($test->getFolderImages('photos/bonsais-lightboxready', 'square'));

?>