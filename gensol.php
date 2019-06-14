<?php
if(!isset($argv[1])){
	echo "uso: php gensol.php prefijo start end\n";
	exit;
}

$prefijo=$argv[1];
$start=$argv[2];
$end=$argv[3];

for ($i=$start; $i<=$end ; $i++){
	if($i%2){ // impar
		printf("php sol-archivo.php $prefijo"."_%03d.pgm\n",$i);
	} 
}

?>
