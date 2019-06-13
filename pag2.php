<?php

// adaptarse a las rotaciones
// buscar marca de tiempo start
// determinar el centro y descender por la vertical ajustando en cada subsiguiente marca de tiempo
//
// usa kernel sobel para determinar el gradiente   FRACASO
//
// implementar busqueda horizontal a la izquierda desde los centros de la marca de tiempo
// identifica numero de pregunta, numero de grupo, y centros de alveolos
// hay un detalle con la pregunta 151 y 152 que no existen
//
$NOINFO=0; $CONFIRMACIONES=1; $ERRORES=2; $ADVERTENCIAS=3; 
$DEPURACION_BAJA=4; $DEPURACION_MEDIA=5; $DEPURACION_ALTA=6;

$DEPURANDO=$DEPURACION_BAJA;

$filename='Imagen2.bmp';
$image = new Imagick();
$image->readImage($filename);
$height=$image->getImageHeight();
$width = $image->getImageWidth();

$pixeles = $image->exportImagePixels(0, 0, $width, $height, "RGB", Imagick::PIXEL_CHAR);

/*
es_ruido(704,1088);
exit;
 */

if($DEPURANDO>=$DEPURACION_BAJA) echo "Buscando centro de marcas de tiempo (rectangulos) del lado derecho PRIMERA\n";
$x=1580;   // 14 de la mitad derecha del alveolo izquierdo, 38 del espacio entre alveolos
$y=160;
$mt0=marcatiempo($x,$y);

$x=$mt0['x'];
$y=$mt0['y'];
for($n=1; $n<=24; $n++){
	// ubicar en el centro y descender
	if($DEPURANDO>=$DEPURACION_BAJA) echo "Buscando centro de marcas de tiempo (rectangulos) del lado derecho PREG $n\n";
	for(;;$y++){
		// buscar un blanco (parte inferior de recytangulo actual
	        $offsetp = $y*$width + $x;  // en el arreglo lineal
		$offsetprgb=$offsetp*3; // r,g,b  cada pixel son 3 elementos en el arreglo
		//if($DEPURANDO>=$DEPURACION_ALTA) print("\ty=$y ");
		if($pixeles[$offsetprgb]>200)  // umbral de gris, TAL VEZ SEA NECESARIO FILTRO PASA BAJAS
			break;
		$pixeles[$offsetprgb]=255;
		$pixeles[$offsetprgb+1]=0;
		$pixeles[$offsetprgb+2]=0;
	}
	if($DEPURANDO>=$DEPURACION_ALTA) {
		print "terminan negros en x:$x, y:$y\n";
		print("buscando blancos\n");
	}
	for(;;$y++){
		// buscar un blanco (parte inferior de rectangulo actual, rumbo a siguiente marca de tiempo)
	        $offsetp = $y*$width + $x;  // en el arreglo lineal
	        $offsetprgb=$offsetp*3; // r,g,b  cada pixel son 3 elementos en el arreglo
		if($pixeles[$offsetprgb]<50)  // umbral de gris, TAL VEZ SEA NECESARIO FILTRO PASA BAJAS
			break;
		$pixeles[$offsetprgb]=0;
		$pixeles[$offsetprgb+1]=0;
		$pixeles[$offsetprgb+2]=255;
	}
	$mt[$n]['ysup']=$y;
	$y+=6;  // posible centro de la siguiente marca de tiempo
	if($DEPURANDO>=$DEPURACION_MEDIA) print "centro posible en $x,$y   ";
	$sinccol=array('x'=>$x, 'y'=>$y);
	$sinccol=ajusta_centro_rectangulo_desde_centro($sinccol);
	if($DEPURANDO>=$DEPURACION_BAJA) print "Preg $n: centro ajustado a ".$sinccol['x'].",".$sinccol['y']."\n";
	$x=$sinccol['x']; $y=$sinccol['y'];
	$mt[$n]['x']=$x;
	$mt[$n]['y']=$y;
}


if($DEPURANDO>=$DEPURACION_BAJA) {
	$im = $image->getImage();
	$im->importImagePixels(0, 0, $width, $height, "RGB", Imagick::PIXEL_CHAR, $pixeles);
	$im->writeImages('converted0.jpg', false);
}

//-------------------------------------------------------
// BUSQUEDA A LA IZQUIERDA DEL PRIMER ALVEOLO
//
// reiniciar con la imagen original
$image->readImage($filename);
$pixeles = $image->exportImagePixels(0, 0, $width, $height, "RGB", Imagick::PIXEL_CHAR);

for($preg=129,$nr=1; $nr<=24; $nr++,$preg+=97){  // numero de renglon, no exiten la preg 151 y 152 (renglon 23 y 24, grupo de mas a la derecha)
   $x=$mt[$nr]['x'];	// centros de marca de tiempo
   $y=$mt[$nr]['y'];
   $pxy=salir_de_la_marca_de_tiempo($x,$y);
   for($gpo=1; $gpo<=4; $gpo++,$preg-=24){
	if($DEPURANDO>=$DEPURACION_MEDIA) echo "GRUPO $gpo, Buscando 1er alveolo a la izq de la marca de tiempo\n";
	$pxy=encontrar_alveolo_a_la_izq($x,$y);
	if($preg>150 AND $pxy[0]<1200){ // no exiten alveolos en el grupo de mas a la derecha, prego 150 y 151
		$preg=$preg-24;
		$gpo++;
	}
	if($DEPURANDO>=$DEPURACION_MEDIA) print "extremo derecho 1er alveolo en ".$pxy[0].",".$pxy[1]."\n";
	$centroxy['x']=$pxy[0]-15;
	$centroxy['y']=$pxy[1];
	//verificar_tipo_alveolo($centroxy);
	if($DEPURANDO>=$DEPURACION_MEDIA) print "centro posible en ".$centroxy['x'].",".$centroxy['y']."\n";
	$centroxy=ajusta_centro($centroxy);
	if($DEPURANDO>=$DEPURACION_MEDIA) print "centro corregido en ".$centroxy['x'].",".$centroxy['y']."\n";
		//-----------------------------------
		$alveolos[$preg][(4-1)]=$centroxy;
		//-----------------------------------
	plotcentro($centroxy);
	//if($DEPURANDO>=$DEPURACION_BAJA) print "renglon $nr, grupo $gpo, alveolo 1,  centro en ".$centroxy['x'].",".$centroxy['y']."\n";
		if($DEPURANDO>=$DEPURACION_BAJA) print "pregunta $preg\n";
	if($DEPURANDO>=$DEPURACION_BAJA) print "*renglon ".($nr-1).", grupo ".(4-$gpo).", alveolo ".(4-1).",  centro en ".$centroxy['x'].",".$centroxy['y']."\n";

	for($n=2; $n<=4; $n++){
		// siguiente alveolo  n
		$centroxy['x']=$centroxy['x']-15-37-15;  // del centro 15 para salir del alveolo, 37 entre alveolos y 15 al nuevo centro
		//verificar_tipo_alveolo($centroxy);
		if($DEPURANDO>=$DEPURACION_MEDIA) print "alveolo $n centro posible en ".$centroxy['x'].",".$centroxy['y']."\n";
		$centroxy=ajusta_centro($centroxy);
		if($DEPURANDO>=$DEPURACION_MEDIA) print "alveolo $n centro corregido en ".$centroxy['x'].",".$centroxy['y']."\n";
		//-----------------------------------
		$alveolos[$preg][(4-$n)]=$centroxy;
		//-----------------------------------
		plotcentro($centroxy);
		//if($DEPURANDO>=$DEPURACION_BAJA) print "renglon $nr, grupo $gpo, alveolo $n,  centro en ".$centroxy['x'].",".$centroxy['y']."\n";
		if($DEPURANDO>=$DEPURACION_BAJA) print "pregunta $preg\n";
		if($DEPURANDO>=$DEPURACION_BAJA) print "*renglon ".($nr-1).", grupo ".(4-$gpo).", alveolo ".(4-$n).",  centro en ".$centroxy['x'].",".$centroxy['y']."\n";
	}
	$x=$centroxy['x']-165;  // del centro de la resp 4 del grupo de la derecha a la respuesta D, punto de inicio de busqueda
	$y=$centroxy['y'];
	if($DEPURANDO>=$DEPURACION_BAJA) print "\n";
   }  // sig gpo
}

// impresion de centros de alveolos ordenados por pregunta
for($np=57; $np<=150; $np++){
	printf("pregunta $np, ");
	for($alv=0; $alv<=3; $alv++){
		printf(" alveolo $alv, centro en (%d,%d)",$alveolos[$np][$alv]['x'],$alveolos[$np][$alv]['y']);
	}
	echo "\n";
}
for($np=57; $np<=150; $np++){
        $respuesta[$np]=0;
        for($opcion=0; $opcion<=3; $opcion++){
                $x=$alveolos[$np][$opcion]['x'];
                $y=$alveolos[$np][$opcion]['y'];
                $gris=convolv($x,$y);
                if($gris<100000)
                        //$respuesta[$np][$opcion]=1;
                        $respuesta [$np] = $respuesta[$np] | pow (2, $opcion);
                //else
                //      $respuesta[$np][$opcion]=0;
                if($DEPURANDO>=2){
                        echo "preg $np, opcion $opcion ($x,$y) gris:$gris\n";
                        if($gris<100000)
                                echo "preg $np, opcion $opcion ($x,$y) gris:$gris RELLENADO\n";
                }
                elseif($DEPURANDO>=1){
                        if($gris<100000)
                                echo "preg $np, opcion $opcion ($x,$y) gris:$gris RELLENADO\n";
                }
        }
}

for($np=57; $np<=150; $np++){
	printf("*** preg $np:");
	printf("%d",$respuesta[$np]);
	echo "\n";
}


/*
// linea horizontal en la direccion de la marca de tiempo
for($n=1; $n<=24; $n++){
   $x=$mt[$n]['x'];
   $y=$mt[$n]['y'];
   //$d=$mt[$n]['dir'];
   // linea por la horizontal
   for($x1=$x;$x1>10;$x1--){
		// buscar un blanco (parte inferior de rectangulo actual, rumbo a siguiente marca de tiempo)
	        $offsetp = $y*$width + $x1;  // en el arreglo lineal
	        $offsetprgb=$offsetp*3; // r,g,b  cada pixel son 3 elementos en el arreglo
		$pixeles[$offsetprgb]=0;
		$pixeles[$offsetprgb+1]=255;
		$pixeles[$offsetprgb+2]=0;
   }
}
*/

//
if($DEPURANDO>=$DEPURACION_BAJA) {
	$im = $image->getImage();
	$im->importImagePixels(0, 0, $width, $height, "RGB", Imagick::PIXEL_CHAR, $pixeles);
	$im->writeImages('converted1.jpg', false);
}

exit;

//
// ---------------------------------------------------------------------------------------------------------------
//



/*
// vertical desde el nuevo centro
$x=$sinccol['x'];
for($y=$sinccol['y']; $y<1900; $y++){
		$offsetp = $y*$width + $x;  // en el arreglo lineal
		$offsetprgb=$offsetp*3; // r,g,b  cada pixel son 3 elementos en el arreglo
		$pixeles[$offsetprgb]=0;
		$pixeles[$offsetprgb+1]=0;
		$pixeles[$offsetprgb+2]=0;
}
 */
//echo "para primera pregunta x,y:$x,$y\n";
//$mt[1]=marcatiempo($x,$y);

/*
$x=$mt[1]['x']-20;
for($npreg=2; $npreg<=24; $npreg++){
	echo "Buscando centro de marcas de tiempo (rectangulos) del lado derecho PREG $npreg\n";
	$y=$mt[$npreg-1]['y']+56;  // 56 es el espacio vertical entre rectangulos
	$mt[$npreg]=marcatiempo($x,$y);
}
 */




// buscar los circulos de sincronizacion de columnas
// 	3 puntos del lado izquierdo desde x=86 hasta x=248
// 	desde Y=152 hasta Y=182
// 
// buscar el circulo de sinc izquierdo
/*
$x=50;
$y=100;
echo "Buscando centro de sincronizacion de columnas izquierdo\n";
$sincizq=alveolo_sinc_col_izq($x,$y);  // centro x,y del alveolo de sinc de columnas izquierdo
echo "\n";

echo "Buscando centro de sincronizacion de columnas central\n";
$x=$sincizq['x']+14+30;   // 14 de la mitad derecha del alveolo izquierdo, 38 del espacio entre alveolos
$y=$sincizq['y'];
$sinccentral=alveolo_sinc_col_central($x,$y);

echo "Buscando centro de sincronizacion de columnas derecho\n";
$x=$sinccentral['x']+14+30;   // 14 de la mitad derecha del alveolo izquierdo, 38 del espacio entre alveolos
$y=$sinccentral['y'];
$sincder=alveolo_sinc_col_central($x,$y);

// probando lineas guias
// g1a g1b g1c g1d   son las columnas del primer grupo
// g2a g2b g2c g2d   son las columnas del segundo grupo
// g3a g3b g3c g3d   son las columnas del tercer grupo
// g4a g4b g4c g4d   son las columnas del cuarto grupo

$distcols1=$sinccentral['x']-$sincizq['x'];
$distcols2=$sincder['x']-$sinccentral['x'];
$distcol=intdiv($distcols1+$distcols2,2);
echo "dist1=$distcols1, dist2=$distcols2, prom:$distcol\n";

$startx=$mt0['x'];
$p1x=$mt[1]['x'];
$p24x=$mt[24]['x'];
$difx=$startx-$p24x;
echo "tiempo: startx=$startx, p24x:$p24x, dif:$difx\n";

//lineas horizontales desde los centros de las marcas de tiempo
for($nr=1; $nr<=24; $nr++){
	$y=$mt[$nr]['y'];
	for($x=$sincizq['x']; $x<$mt[$nr]['x']; $x++){
		$offsetp = $y*$width + $x;  // en el arreglo lineal
		$offsetprgb=$offsetp*3; // r,g,b  cada pixel son 3 elementos en el arreglo
		$pixeles[$offsetprgb]=0;
		$pixeles[$offsetprgb+1]=0;
		$pixeles[$offsetprgb+2]=0;
	}
}
// linea ver sobre las marcas de tiempo tomando el centro de start
// para evidenciar rotacion

//for($x=$sincizq['x'] ; $x<=$mt0['x']; $x+=$distcol){
for($x=$sincizq['x'] ; $x<=$mt0['x']; $x+=67){
	$y=$sincizq['y'];
	for($y=$sincizq['y']; $y<1900; $y++){
		$offsetp = $y*$width + $x;  // en el arreglo lineal
		$offsetprgb=$offsetp*3; // r,g,b  cada pixel son 3 elementos en el arreglo
		$pixeles[$offsetprgb]=0;
		$pixeles[$offsetprgb+1]=0;
		$pixeles[$offsetprgb+2]=0;
	}
}

$im = $image->getImage();
$im->importImagePixels(0, 0, $width, $height, "RGB", Imagick::PIXEL_CHAR, $pixeles);
$im->writeImages('converted.jpg', false);
return;
 */



?>
<?php
function marcatiempo($x,$y){
	//investigar por las columnas a la vez para encontrar el borde izquierdo
	//al encontrar un negro investigar a la derecha al menos 28 pixeles
	global $width, $pixeles;
	$scx=$x; $scy=$y;
	for($x=$scx; $x<=$scx+90; $x++){
		for($y=$scy; $y<=$scy+30; $y++){
			$offsetp = $y*$width + $x;  // en el arreglo lineal
			$offsetprgb=$offsetp*3; // r,g,b  cada pixel son 3 elementos en el arreglo
			if($pixeles[$offsetprgb]<100){
				// tal vez se encontro borde izquierdo
				if(negros_a_la_derecha($x,$y)<24*50) { // rectangulo 12 alto x 30 ancho  de 50 de gris
					$sinccol=array('x'=>$x+15, 'y'=>$y+6);
					break 2;
				}
			}
		}
	}
	if(!isset($sinccol)){
		echo "No se encontro marca de sincronizacion de tiempo\n";
		exit;
	}
	print "\tOk, posible marca de sinc de tiempo (borde mas a la izquierda) en $x,$y\n";
	print "\tPosible Centro en ".$sinccol['x'].",".$sinccol['y']."\n";
	$sinccol=ajusta_centro_rectangulo($sinccol);
	print "Centro de sinc de col corregido en ".$sinccol['x'].",".$sinccol['y']."\n";
	return $sinccol;
}


?>



<?php
function ajusta_centro_rectangulo_desde_centro($sinccol){
	global $NOINFO, $CONFIRMACIONES, $ERRORES, $ADVERTENCIAS, 
		$DEPURACION_BAJA, $DEPURACION_MEDIA, $DEPURACION_ALTA, $DEPURANDO;
	$sumanegros=30*12*255; // too high to force first change, rectangulo de 30 de ancho x 12 de alto
	$centroX=$sinccol['x']; $centroY=$sinccol['y'];
	for($x=$sinccol['x']-15; $x<$sinccol['x']+15; $x++){
		for($y=$sinccol['y']-6; $y<$sinccol['y']+6; $y++){
			$sn=convolv_rect($x,$y);
			if($DEPURANDO>=$DEPURACION_ALTA) echo "\tprobando centro en $x, $y: suma $sn ";
			if($sn<$sumanegros){
				$sumanegros=$sn;
				$centroX=$x; $centroY=$y;
				if($DEPURANDO>=$DEPURACION_ALTA) echo "minimo hasta el momento";
			}
			if($DEPURANDO>=$DEPURACION_ALTA) echo "\n";
		}
	}
	return array('x'=>$centroX, 'y'=>$centroY);
}

function ajusta_centro_rectangulo($sinccol){
	global $NOINFO, $CONFIRMACIONES, $ERRORES, $ADVERTENCIAS, 
		$DEPURACION_BAJA, $DEPURACION_MEDIA, $DEPURACION_ALTA, $DEPURANDO;
	$sumanegros=30*12*255; // too high to force first change, rectangulo de 30 de ancho x 12 de alto
	$centroX=$sinccol['x']; $centroY=$sinccol['y'];
	for($x=$sinccol['x']-5; $x<$sinccol['x']+5; $x++){
		for($y=$sinccol['y']-3; $y<$sinccol['y']+3; $y++){
			$sn=convolv_rect($x,$y);
			if($DEPURANDO>=$DEPURACION_ALTA) echo "\tprobando centro en $x, $y: suma $sn ";
			if($sn<$sumanegros){
				$sumanegros=$sn;
				$centroX=$x; $centroY=$y;
				if($DEPURANDO>=$DEPURACION_ALTA) echo "minimo hasta el momento";
			}
			if($DEPURANDO>=$DEPURACION_ALTA) echo "\n";
		}
	}
	return array('x'=>$centroX, 'y'=>$centroY);
}
function ajusta_centro($sinccol){
	global $NOINFO, $CONFIRMACIONES, $ERRORES, $ADVERTENCIAS, 
		$DEPURACION_BAJA, $DEPURACION_MEDIA, $DEPURACION_ALTA, $DEPURANDO;
	$sumanegros=28*28*255; // too high to force first change, alveolo de 28x28
	$centroX=$sinccol['x']; $centroY=$sinccol['y'];
	for($x=$sinccol['x']-3; $x<$sinccol['x']+3; $x++){
		for($y=$sinccol['y']-3; $y<$sinccol['y']+3; $y++){
			$sn=convolv($x,$y);
			if($DEPURANDO>=$DEPURACION_ALTA) echo "\tprobando centro en $x, $y: suma $sn ";
			if($sn<$sumanegros){
				$sumanegros=$sn;
				$centroX=$x; $centroY=$y;
				if($DEPURANDO>=$DEPURACION_ALTA) echo "minimo hasta el momento";
			}
			if($DEPURANDO>=$DEPURACION_ALTA) echo "\n";
		}
	}
	return array('x'=>$centroX, 'y'=>$centroY);
}

function convolv_rect($x,$y){  // convolucion para rectangulo de tiempos
	global $width,$pixeles;
	$sumapix=0;
	for($xi=$x-17; $xi<$x+17; $xi++){  // alveolo de 28*28
		for($yi=$y-8; $yi<$y+8; $yi++){
			$offsetp = $yi*$width + $xi;  // en el arreglo lineal
			$offsetprgb=$offsetp*3; // r,g,b  cada pixel son 3 elementos en el arreglo
			$sumapix+=$pixeles[$offsetprgb];
		}
	}
	return $sumapix;
}

function convolv($x,$y){
	global $width,$pixeles;
	$sumapix=0;
	for($xi=$x-14; $xi<$x+14; $xi++){  // alveolo de 28*28
		for($yi=$y-14; $yi<$y+14; $yi++){
			$offsetp = $yi*$width + $xi;  // en el arreglo lineal
			$offsetprgb=$offsetp*3; // r,g,b  cada pixel son 3 elementos en el arreglo
			$sumapix+=$pixeles[$offsetprgb];
		}
	}
	return $sumapix;
}
function negros_a_la_derecha($x, $y){
	global $NOINFO, $CONFIRMACIONES, $ERRORES, $ADVERTENCIAS, 
		$DEPURACION_BAJA, $DEPURACION_MEDIA, $DEPURACION_ALTA, $DEPURANDO;
	global $width;
	$offsetp = $y*$width + $x;  // en el arreglo lineal
	$offsetprgb=$offsetp*3; // r,g,b  cada pixel son 3 elementos en el arreglo
	$negr = _negros_a_la_derecha($offsetprgb);
	if($DEPURANDO>=$DEPURACION_ALTA) printf("\tNegros a la derecha de coord %d,%d: %d\n",$x,$y,$negr);
	return $negr;
}
function _negros_a_la_derecha($offsetprgb){
	global $pixeles;
	$negros=0;
	for($n=0; $n<=28; $n++){
		$negros+=$pixeles[$offsetprgb];
		$offsetprgb+=3;
	}
	//if($DEPURANDO>=$DEPURACION_ALTA) print "\t$negros\n";
	return $negros;
}

?>

<?php
// -----------------------------------------------------------------------   busqueda a la izquierda
function salir_de_la_marca_de_tiempo($x,$y){
	$pxy=array($x,$y);
	$pxy=avanzar_sobre_negros_a_la_izq($pxy); // salir de la marca de tiempo
}

function encontrar_alveolo_a_la_izq($x,$y){ // desde un punto blanco a la derecha del alveolo
	$pxy=array($x,$y);
	$pxy=avanzar_sobre_blancos_a_la_izq($pxy); // encontrar alveolo
	return $pxy;
}
function avanzar_sobre_negros_a_la_izq($pxy){
	$pxy= _avanzar_izquierda($pxy,"GT",150);  // avanzar izq y detenerse cuando gris>200
	return $pxy;
}	
function avanzar_sobre_blancos_a_la_izq($pxy){
	$pxy= _avanzar_izquierda($pxy,"LT",200);  // avanzar izq y detenerse cuando gris<50, 2a opcion  127, funciona con 200 pero checar ruido
	return $pxy;
}	


function _avanzar_izquierda($pxy,$cond,$color){
	global $NOINFO, $CONFIRMACIONES, $ERRORES, $ADVERTENCIAS, 
		$DEPURACION_BAJA, $DEPURACION_MEDIA, $DEPURACION_ALTA, $DEPURANDO;
	global $width,$pixeles;

	// salir de la marca de tiempo hacia la izquierda
	if($DEPURANDO>=$DEPURACION_ALTA) echo "Avanzando a la izq desde ".$pxy[0].",".$pxy[1]." Detener cuando gris $cond $color\n";
	$x=$pxy[0];
	$y=$pxy[1];
	for(;;$x--){
		// buscar un blanco a la izquierda
	        $offsetp = $y*$width + $x;  // en el arreglo lineal
		$offsetprgb=$offsetp*3; // r,g,b  cada pixel son 3 elementos en el arreglo
		$colorpix=$pixeles[$offsetprgb];
		if($DEPURANDO>=$DEPURACION_ALTA) print("\tx=$x color=$colorpix\n");
		if($cond=='LT'){
			if($pixeles[$offsetprgb]<$color){ // umbral de gris, TAL VEZ SEA NECESARIO FILTRO PASA BAJAS
				if(es_ruido($x,$y)<$color){
					break;
				}
			}
		} elseif($cond=='GT'){
			if($pixeles[$offsetprgb]>$color)  // umbral de gris, TAL VEZ SEA NECESARIO FILTRO PASA BAJAS
				break;
		}
		if($DEPURANDO>=$DEPURACION_ALTA) print("\t\trojo en $x,$y\n");
		$pixeles[$offsetprgb]=255;
		$pixeles[$offsetprgb+1]=0;
		$pixeles[$offsetprgb+2]=0;
	}
	return array($x,$y);
}
function es_ruido($x,$y){
	global $width,$pixeles;
	$sumapix=0;
	for($xi=$x-1; $xi<=$x; $xi++){  // alveolo de 28*28
		for($yi=$y-1; $yi<=$y+1; $yi++){
			$offsetp = $yi*$width + $xi;  // en el arreglo lineal
			$offsetprgb=$offsetp*3; // r,g,b  cada pixel son 3 elementos en el arreglo
			$sumapix+=$pixeles[$offsetprgb];
			printf("x:$xi,y:$yi gris:%d\n",$pixeles[$offsetprgb]);
		}
	}
	printf("x:$x,y:$y prom:%d\n",(int)($sumapix/6));
	return (int)($sumapix/6);
}
function plotcentro($xy){
        global $NOINFO, $CONFIRMACIONES, $ERRORES, $ADVERTENCIAS,
                $DEPURACION_BAJA, $DEPURACION_MEDIA, $DEPURACION_ALTA, $DEPURANDO;
        global $width,$pixeles;

	$x=$xy['x'];
	$y=$xy['y'];

	for($xi=$x-1; $xi<$x+1; $xi++){  // alveolo de 28*28
		for($yi=$y-1; $yi<$y+1; $yi++){
			$offsetp = $yi*$width + $xi;  // en el arreglo lineal
			$offsetprgb=$offsetp*3; // r,g,b  cada pixel son 3 elementos en el arreglo
			$pixeles[$offsetprgb]=0;
			$pixeles[$offsetprgb+1]=0;
			$pixeles[$offsetprgb+2]=255;
		}
	}
}

?>
