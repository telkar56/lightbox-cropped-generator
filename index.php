<?php 
include_once 'php/PaginatedImgTable.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="UTF-8">
	<title>Document</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
	<link rel="stylesheet" href="vendor/node_modules/@fancyapps/fancybox/dist/jquery.fancybox.css" type="text/css" media="screen" />
	<style type="text/css">
	
		.carrousel {
			margin: 0 auto;
		}

		.img-canvas-classic img {
			display: block;
			width: 100%;
			height: auto;
			padding: 7px;
		}

		.img-canvas-lightbox > div {
			position: relative;
		}

		.img-canvas-lightbox > div {
			margin-bottom: 10px;
		}

		.img-canvas-lightbox img {
			width: 100%;
			height: auto;
		}

		@media (min-width: 993px) {
			.after {
				text-align: center;
				padding: 5px;
				width: 12%;
				height: 12%;
		   		position: absolute;
		   		bottom: 0;
		   		left: 0;
		   		border-radius: 0 5px 0 0;
		   		opacity: 0.7;
		   		display: none;
				background-color: white;
				background-image: url('expand.png');
				background-position: center top;
		    	background-size: 100% auto;
				animation-duration: .7s;
		  		animation-name: bounce;
		  		animation-iteration-count: infinite;
		  		animation-direction: alternate;
		  		animation-timing-function: easeInCirc;
			}
			.after:hover {
				cursor: pointer;
				-webkit-animation-play-state: paused;
				-moz-animation-play-state: paused;
			}

			@keyframes bounce {
			  from {
		   		width: 12%;
				height: 12%;
			  }
			  to {
		   		width: 17%;
				height: 17%;
			  }
			}
		}

		.navigation a {
			display: inline-block;
			padding: 3px 6px;
			text-decoration: none;
		}

		.navigation a.active {
			color: black;
			font-weight: bold;
			pointer-events: none;
		}

		.img-canvas-classic, .img-canvas-lightbox {
			padding: 5px;
			-webkit-column-count: 3;
	    	-webkit-column-gap: 10px;
	    	-moz-column-count: 3;
	    	-moz-column-gap: 10px;
	    	column-count: 3;
	    	column-gap: 10px;
		}

		@media (max-width: 480px) {
			.img-canvas-classic, .img-canvas-lightbox {
				-webkit-column-count: 2;
		    	-webkit-column-gap: 10px;
		    	-moz-column-count: 2;
		    	-moz-column-gap: 10px;
		    	column-count: 2;
		    	column-gap: 10px;
			}
		}
	
	</style>
</head>
<body>

	<p>test</p>
	<div class="carrousel col-xs-12 col-sm-12 col-md-6">
	<?php 
		$test = new PaginatedImgTable ('photos/bonsais/associated-images.csv', 'photos/bonsais/bonsais-lightboxready');
		$test->renderImages(true);
	?>
	</div>

	<script type="text/javascript" src="vendor/node_modules/jquery/dist/jquery.js"></script>
	<script type="text/javascript" src="vendor/node_modules/@fancyapps/fancybox/dist/jquery.fancybox.js"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			$(".fancybox").fancybox();
			$('.fancybox').after('<span title="Cliquer pour agrandir" class="after" href="#"></span>');
			if ($(window).width() > 992) {
		  		setFancyBounce();
			}
			else {
				unsetFancyBounce();
			}
		});
		$(window).resize(function() {
		  	if ($(window).width() > 992) {
		  		setFancyBounce();
			}
			else {
				unsetFancyBounce();
			}
		});

		function setFancyBounce () {
			$('.fancybox').mouseover(function () {
				$(this).next('.after').show();
			});
			$('.fancybox-wrapper').mouseleave(function () {
				$('.after').hide();
			});
			$('.after').click(function (e) {
  				e.preventDefault();
  				$(this).prev().click();
  			});
		}

		function unsetFancyBounce () {
			$('.after').off('click');
			$('.fancybox').off('mouseover');
			$('.fancybox-wrapper').off('mouseleave');
		}
		
  		
	</script>
</body>
</html>