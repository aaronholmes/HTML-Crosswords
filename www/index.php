<?php 

$fh = fopen("/Users/kyle/HTML-Crosswords/www/puzzles/classic120217.puz", "rb");

$data = fread($fh, filesize("/Users/kyle/HTML-Crosswords/www/puzzles/classic120217.puz"));
fclose($fh);

$dimdata = unpack("c2dim", substr($data, 0x2C, 2));

$width = $dimdata['dim1'];
$height = $dimdata['dim2'];

$answerstring = substr($data, 0x34, $width*$height); //Find the answers string

for ($i = 0; $i <= $height-1; $i++) { // Make a 2d array of answers
	$answergrid[$i] = preg_split('//', substr($answerstring, $i*$width, $width));
}

$bwstring = substr($data, 0x34+$width*$height, $width*$height); //Find the crossword structure

for ($i = 0; $i < $height; $i++) { // Make a 2d array of structure
	$bwgrid[$i] = str_split(substr($bwstring, $i*$width, $width));
}

$cluestring = substr($data, 0x34+($width*$height+$width*$height));

$newclues = preg_split('/\0/', $cluestring);

$header = array("title" => array_shift($newclues), "author" => array_shift($newclues), "copyright" => array_shift($newclues));

for ($i = 0; $i < count($bwgrid); $i++) { // 2d Array of Clue Numbers
	for ($j = 0; $j < count($bwgrid[$i]); $j++) {
		$numgrid[$i][$j] = $bwgrid[$i][$j];
	}
}

array_unshift($bwgrid, array()); // Add row of -1s to top and left of BWGrid
for ($i = 0; $i < $width; $i++) {
	array_push($bwgrid[0], -1);
}
for ($i = 0; $i <= $width; $i++) {
	array_unshift($bwgrid[$i], -1);
}

array_unshift($numgrid, array()); // Do the same thing to NumGrid
for ($i = 0; $i < $width; $i++) {
	array_push($numgrid[0], -1);
}
for ($i = 0; $i <= $width; $i++) {
	array_unshift($numgrid[$i], -1);
}

array_unshift($answergrid, array()); // Do the same thing to AnswerGrid
for ($i = 0; $i < $width; $i++) {
	array_push($answergrid[0], -1);
}
for ($i = 0; $i <= $width; $i++) {
	array_unshift($answergrid[$i], -1);
}

// Now we need to do some clue numbering!

$across = array();
$down = array();
$cluenumber = 0;

//for my $i (1..$h) {
for ($i = 1; $i <= $height; $i++) {
	
	for ($j = 1; $j <= $width; $j++) {
		
		if ($bwgrid[$i][$j] == '.'){ 
	      $bwgrid[$i][$j] = -1 ;
	      $numgrid[$i][$j] = -1;
	    }
	    
	    if ($bwgrid[$i][$j] == -1) {
	    	continue;
	    }
		
		if ($bwgrid[$i][$j-1] == -1 or $bwgrid[$i-1][$j] == -1){ // If a square has -1 to it's left or top, it's a clue. So give it a number!
	      $cluenumber++;
	      $numgrid[$i][$j] = $cluenumber;
	    }
	    
	    if ($bwgrid[$i][$j-1] == -1){ 
	    	array_push($across, array('cluenumber' => $cluenumber, 'cluetext' => array_shift($newclues)));
	    }
	    
	    if ($bwgrid[$i-1][$j] == -1){ 
	    	array_push($down, array('cluenumber' => $cluenumber, 'cluetext' => array_shift($newclues))); 
	    }
	    
	}

}

?>
<!DOCTYPE html>
<html lang="eng">

<head>
	
	<style type="text/css">

		body {
			font-family: Arial, sans-serif;
		}
	
		table {
			border: 1px solid black;
			border-collapse: collapse;
			empty-cells: show;
		}
		
		td {
			width: 40px;
			height: 40px;
			border: 1px solid black;
			text-align: center;
		}
		
		td.black {
			background: black;
		}
		
		td .wrapper {
			width: 100%;
			height: 100%;
			position: relative;
		}
		
		.number {
			font-size: 12px;
			top: 0;
			position: absolute;
			pointer-events: none;
			text-align: left;
			color: #666;
		}
		
		span.answer {
			font-weight: bold;
			font-size: 18px;
			
			display: block;
			width: 100%;
			height: 100%;
			line-height: 40px;
		}
		
		input.answer {
			font-weight: bold;
			font-family: Arial,sans-serif;
			width: 100%;
			height: 100%;
			border: 0;
			padding: 0;
			text-align: center;
			font-size: 18px;
			text-transform:uppercase;
		}
	
	</style>
	
	<script type="text/javascript" src="http://www.google.com/jsapi"></script>
	<script type="text/javascript">google.load("jquery", "1");</script>
	<script type="text/javascript" src="scripts/jquery.autotab-1.1b.js"></script>
	
	<script type="text/javascript">
		$(function(){
			$(':input').autotab_magic();
			
			$('a.show').click(function() {
				$('#answerkey').toggle();
				return false;
			});
		});
	</script>
	
</head>

<body>

<?php echo $answerstring; ?>

<h1><?php echo $header['title']; ?></h1>
<p><?php echo $header['author'].', '.$header['copyright']; ?></p>

<div class="clues" id="across">
	<h2>Across:</h2>
	
		<ol>
		<?php 
		
			$i = 0;	foreach($across as $clue) {
			
					echo "\n\t\t\t<li value='".$clue['cluenumber']."'>".$clue['cluetext']."</li>";
					
			$i++;	} 
		?>
	
		</ol>
</div>

<div class="clues" id="down">
	<h2>Down:</h2>
	
		<ol>
		<?php 
		
			$i = 0;	foreach($down as $clue) {
			
					echo "\n\t\t\t<li value='".$clue['cluenumber']."'>".$clue['cluetext']."</li>";
					
			$i++;	} 
		?>
	
		</ol>
</div>

<div id="puzzle">

	<?php
	
		$html = "<table>";
		
		for ($i = 1; $i <= $height; $i++) {

			$html .= "\n\t<tr>";
			
			for ($j = 1; $j <= $width; $j++) {
			
				if ($numgrid[$i][$j] == -1){ //It's a black square!
					$html .= "\n\t\t<td class='black'></td>";
				} else if ($numgrid[$i][$j] > 0) { // It's a clue!
					$html .= "\n\t\t<td class='space'><div class='wrapper'><div class='number'>".$numgrid[$i][$j]."</div><input type='text' class='answer' maxlength='1'></div></td>";
				} else { // It's a blank square!
					$html .= "\n\t\t<td class='space'><div class='wrapper'><input type='text' class='answer' maxlength='1'></div></td>";
				}
			}
			
			$html .= "\n\t</tr>";
		}
		
		$html .= "\n</table>";
		
		echo $html;
	
	?>
</div>

<p><a href="#" class="show">Toggle answer key</a></p>

<div id="answerkey" style="display: none;">

	<?php // Render Answer Key
	
		$html = "<table>";
		
		for ($i = 1; $i <= $height; $i++) {

			$html .= "\n\t<tr>";
			
			for ($j = 1; $j <= $width; $j++) {
			
				if ($numgrid[$i][$j] == -1){ //It's a black square!
					$html .= "\n\t\t<td class='black'></td>";
				} else if ($numgrid[$i][$j] > 0) { // It's a clue!
					$html .= "\n\t\t<td class='space'><div class='number'>".$numgrid[$i][$j]."</div><span class='answer'>".$answergrid[$i][$j+1]."</span></td>";
				} else { // It's a blank square!
					$html .= "\n\t\t<td class='space'><span class='answer'>".$answergrid[$i][$j+1]."</span></td>";
				}
			}
			
			$html .= "\n\t</tr>";
		}
		
		$html .= "\n</table>";
		
		echo $html;
	
	?>
</div>

</body>

</html>