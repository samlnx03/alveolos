<?php
//
// funciones para el scrip pag1.php
//
function marcatiempo($x,$y){   // return array('x'=>#, 'y'=>#)
	//investigar por las columnas a la vez para encontrar el borde izquierdo
	//al encontrar un negro investigar a la derecha al menos 28 pixeles
	global $NOINFO, $RESULTADOS, $ERRORES, $ADVERTENCIAS, 
		$DEPURACION_BAJA, $DEPURACION_MEDIA, $DEPURACION_ALTA, $DEPURANDO;
	global $width, $pixeles;
	$scx=$x; $scy=$y;
	for($x=$scx; $x<=$scx+90; $x++){
		for($y=$scy; $y<=$scy+40; $y++){   // antes 30
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
	if($DEPURANDO>=$DEPURACION_MEDIA){
		print "\tOk, posible marca de sinc de tiempo (borde mas a la izquierda) en $x,$y\n";
		print "\tPosible Centro en ".$sinccol['x'].",".$sinccol['y']."\n";
	}
	$sinccol=ajusta_centro_rectangulo($sinccol);
	if($DEPURANDO>=$DEPURACION_MEDIA)
		print "\tCentro corregido en ".$sinccol['x'].",".$sinccol['y']."\n";
	return $sinccol;
}
// -------------------------------------------------------------------------------------------------------------

function ajusta_centro_rectangulo_desde_centro($sinccol){  // return array('x'=>$centroX, 'y'=>$centroY);
	global $NOINFO, $RESULTADOS, $ERRORES, $ADVERTENCIAS, 
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
	if($DEPURANDO>=$DEPURACION_MEDIA) print "\tCentro ajustado de ".$sinccol['x'].",".$sinccol['y']." a: $centroX,$centroY\n";
	return array('x'=>$centroX, 'y'=>$centroY);
}
// -------------------------------------------------------------------------------------------------------------

function ajusta_centro_rectangulo($sinccol){ // return array('x'=>$centroX, 'y'=>$centroY);
	global $NOINFO, $RESULTADOS, $ERRORES, $ADVERTENCIAS, 
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
	if($DEPURANDO>=$DEPURACION_ALTA) print "Centro ajustado a: $centroX,$centroY\n";
	return array('x'=>$centroX, 'y'=>$centroY);
}
// -------------------------------------------------------------------------------------------------------------

function ajusta_centro($sinccol){  // return array('x'=>$centroX, 'y'=>$centroY);
	global $NOINFO, $RESULTADOS, $ERRORES, $ADVERTENCIAS, 
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
// -------------------------------------------------------------------------------------------------------------

function convolv_rect($x,$y){  // convolucion para rectangulo de tiempos,   return $sumapix;
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
// -------------------------------------------------------------------------------------------------------------

function convolv($x,$y){    // return $sumapix;
	global $width,$pixeles;
	$sumapix=0;
	for($xi=$x-14; $xi<=$x+14; $xi++){  // alveolo de 28*28
		for($yi=$y-14; $yi<=$y+14; $yi++){
			$offsetp = $yi*$width + $xi;  // en el arreglo lineal
			$offsetprgb=$offsetp*3; // r,g,b  cada pixel son 3 elementos en el arreglo
			$sumapix+=$pixeles[$offsetprgb];
		}
	}
	return $sumapix;
}
// -------------------------------------------------------------------------------------------------------------

function gris_alveolo($x,$y,$w,$h){   // return $sumapix;
	global $width,$pixeles;
	$sumapix=0;
	$xizq=$x-14+3;   // alveolo de 28, la mitad 14, 3 para no meter blancos de las esquinas
	$xder=$x+14-3;
	$ysup=$y-14+3;
	$yinf=$y+14-3;
	for($xi=$xizq; $xi<=$xder; $xi++){  // alveolo de 28*28
		for($yi=$ysup; $yi<=$yinf; $yi++){
			$offsetp = $yi*$width + $xi;  // en el arreglo lineal
			$offsetprgb=$offsetp*3; // r,g,b  cada pixel son 3 elementos en el arreglo
			$sumapix+=$pixeles[$offsetprgb];
		}
	}
	return $sumapix;
}
// -------------------------------------------------------------------------------------------------------------

function negros_a_la_derecha($x, $y){   // return suma de grises a la derecha de 28 pixeles iniciando en x,y
	global $NOINFO, $RESULTADOS, $ERRORES, $ADVERTENCIAS, 
		$DEPURACION_BAJA, $DEPURACION_MEDIA, $DEPURACION_ALTA, $DEPURANDO;
	global $width;
	$offsetp = $y*$width + $x;  // en el arreglo lineal
	$offsetprgb=$offsetp*3; // r,g,b  cada pixel son 3 elementos en el arreglo
	$negr = _negros_a_la_derecha($offsetprgb);
	if($DEPURANDO>=$DEPURACION_ALTA) printf("\tNegros a la derecha de coord %d,%d: %d\n",$x,$y,$negr);
	return $negr;
}
// -------------------------------------------------------------------------------------------------------------

function _negros_a_la_derecha($offsetprgb){   // return #
	global $pixeles;
	$negros=0;
	for($n=0; $n<=28; $n++){
		$negros+=$pixeles[$offsetprgb];
		$offsetprgb+=3;
	}
	//if($DEPURANDO>=$DEPURACION_ALTA) print "\t$negros\n";
	return $negros;
}
// -----------------------------------------------------------------------   busqueda a la izquierda

function salir_de_la_marca_de_tiempo($x,$y){  // return array($x,$y);
	$pxy=array($x,$y);
	$pxy=avanzar_sobre_negros_a_la_der($pxy); // salir de la marca de tiempo
	return $pxy;
}
// -------------------------------------------------------------------------------------------------------------

function encontrar_alveolo_a_la_der($x,$y){ // desde un punto blanco a la derecha del alveolo, return array($x,$y);
	$pxy=array($x,$y);
	$pxy=avanzar_sobre_blancos_a_la_der($pxy); // encontrar alveolo
	return $pxy;
}
// -------------------------------------------------------------------------------------------------------------
function avanzar_sobre_negros_a_la_der($pxy){   // return array($x,$y);
	$pxy= _avanzar_derecha($pxy,"GT",150);  // avanzar der y detenerse cuando gris>200
	return $pxy;
}	
// -------------------------------------------------------------------------------------------------------------

function avanzar_sobre_blancos_a_la_der($pxy){   // return array($x,$y);
	$pxy= _avanzar_derecha($pxy,"LT",127);  // avanzar der y detenerse cuando gris<50
	return $pxy;
}	
// -------------------------------------------------------------------------------------------------------------

function _avanzar_derecha($pxy,$cond,$color){
	global $NOINFO, $RESULTADOS, $ERRORES, $ADVERTENCIAS, 
		$DEPURACION_BAJA, $DEPURACION_MEDIA, $DEPURACION_ALTA, $DEPURANDO;
	global $width,$pixeles;

	// salir de la marca de tiempo hacia la derecha
	if($DEPURANDO>=$DEPURACION_ALTA) echo "Avanzando a la der desde ".$pxy[0].",".$pxy[1]." Detener cuando gris $cond $color\n";
	$x=$pxy[0];
	$y=$pxy[1];
	for(;;$x++){
		// buscar un blanco a la izquierda
	        $offsetp = $y*$width + $x;  // en el arreglo lineal
		$offsetprgb=$offsetp*3; // r,g,b  cada pixel son 3 elementos en el arreglo
		$colorpix=$pixeles[$offsetprgb];
		if($DEPURANDO>=$DEPURACION_ALTA) print("\tx=$x color=$colorpix\n");
		if($cond=='LT'){
			if($pixeles[$offsetprgb]<$color)  // umbral de gris, TAL VEZ SEA NECESARIO FILTRO PASA BAJAS
				break;
		} elseif($cond=='GT'){
			if($pixeles[$offsetprgb]>$color)  // umbral de gris, TAL VEZ SEA NECESARIO FILTRO PASA BAJAS
				break;
		}
		if($DEPURANDO>=$DEPURACION_ALTA) print("\t\trojo en $x,$y\n");
		$pixelesdebug[$offsetprgb]=255;
		$pixelesdebug[$offsetprgb+1]=0;
		$pixelesdebug[$offsetprgb+2]=0;
	}
	return array($x,$y);
}
// ------------------------------------------------------------------------------------------
function avanzar_sobre_blancos_a_la_izq($pxy){
	$pxy= _avanzar_izquierda($pxy,"LT",180);  // avanzar izq y detenerse cuando gris<50, luego 127
	return $pxy;
}	
// ------------------------------------------------------------------------------------------

function avanzar_sobre_blancos_hacia_arriba($pxy){
	$pxy= _avanzar_arriba($pxy,"LT",180);  // avanzar izq y detenerse cuando gris<50, luego 127
	return $pxy;
}	
// ------------------------------------------------------------------------------------------

function _avanzar_arriba($pxy,$cond,$color){
	global $NOINFO, $CONFIRMACIONES, $ERRORES, $ADVERTENCIAS, 
		$DEPURACION_BAJA, $DEPURACION_MEDIA, $DEPURACION_ALTA, $DEPURANDO;
	global $width,$pixeles;

	if($DEPURANDO>=$DEPURACION_ALTA) echo "Avanzando hacia arriba desde ".$pxy[0].",".$pxy[1]." Detener cuando gris $cond $color\n";
	$x=$pxy[0];
	$y=$pxy[1];
	for(;;$y--){
		// buscar un blanco a la izquierda
	        $offsetp = $y*$width + $x;  // en el arreglo lineal
		$offsetprgb=$offsetp*3; // r,g,b  cada pixel son 3 elementos en el arreglo
		$colorpix=$pixeles[$offsetprgb];
		if($DEPURANDO>=$DEPURACION_ALTA) print("\ty=$y color=$colorpix\n");
		if($cond=='LT'){
			if($pixeles[$offsetprgb]<$color)  // umbral de gris, TAL VEZ SEA NECESARIO FILTRO PASA BAJAS
				break;
		} elseif($cond=='GT'){
			if($pixeles[$offsetprgb]>$color)  // umbral de gris, TAL VEZ SEA NECESARIO FILTRO PASA BAJAS
				break;
		}
		if($DEPURANDO>=$DEPURACION_ALTA) print("\t\trojo en $x,$y\n");
		$pixelesdebug[$offsetprgb]=255;
		$pixelesdebug[$offsetprgb+1]=0;
		$pixelesdebug[$offsetprgb+2]=0;
	}
	return array($x,$y);
}
// ------------------------------------------------------------------------------------------

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
			//	if(es_ruido($x,$y)<$color){
					break;
				//}
			}

		} elseif($cond=='GT'){
			if($pixeles[$offsetprgb]>$color)  // umbral de gris, TAL VEZ SEA NECESARIO FILTRO PASA BAJAS
				break;
		}
		if($DEPURANDO>=$DEPURACION_ALTA) print("\t\trojo en $x,$y\n");
		$pixelesdebug[$offsetprgb]=255;
		$pixelesdebug[$offsetprgb+1]=0;
		$pixelesdebug[$offsetprgb+2]=0;
	}
	return array($x,$y);
}

// -------------------------------------------------------------------------------------------------

function plot_alveolo($xy,$w,$h){   // centro ancho alto
	plot_mt($xy,$w,$h);
}
// ------------------------------------------------------------------------------------------

function plot_mt($xy,$w,$h){
        global $NOINFO, $RESULTADOS, $ERRORES, $ADVERTENCIAS,
                $DEPURACION_BAJA, $DEPURACION_MEDIA, $DEPURACION_ALTA, $DEPURANDO;
        global $width,$pixeles,$pixelesdebug;

        $x=$xy['x'];
        $y=$xy['y'];
	// $w wide,  $h height
	for($xi=$x-(int)($w/2); $xi<$x+(int)($w/2); $xi++){  // marca de tiempo de 12 de alto x 30 de ancho
        	$yi=$y-(int)($h/2);
		$offsetp = $yi*$width + $xi;  // en el arreglo lineal
                $offsetprgb=$offsetp*3; // r,g,b  cada pixel son 3 elementos en el arreglo
                $pixelesdebug[$offsetprgb]=255;
                $pixelesdebug[$offsetprgb+1]=0;
		$pixelesdebug[$offsetprgb+2]=0;
		$yi+=$h;
		$offsetp = $yi*$width + $xi;  // en el arreglo lineal
                $offsetprgb=$offsetp*3; // r,g,b  cada pixel son 3 elementos en el arreglo
                $pixelesdebug[$offsetprgb]=255;
                $pixelesdebug[$offsetprgb+1]=0;
		$pixelesdebug[$offsetprgb+2]=0;
	}
	for($yi=$y-(int)($h/2); $yi<$y+(int)($h/2); $yi++){
		$xi=$x-(int)($w/2);
                $offsetp = $yi*$width + $xi;  // en el arreglo lineal
                $offsetprgb=$offsetp*3; // r,g,b  cada pixel son 3 elementos en el arreglo
                $pixelesdebug[$offsetprgb]=255;
                $pixelesdebug[$offsetprgb+1]=0;
		$pixelesdebug[$offsetprgb+2]=0;
		$xi+=$w;
                $offsetp = $yi*$width + $xi;  // en el arreglo lineal
                $offsetprgb=$offsetp*3; // r,g,b  cada pixel son 3 elementos en el arreglo
                $pixelesdebug[$offsetprgb]=255;
                $pixelesdebug[$offsetprgb+1]=0;
		$pixelesdebug[$offsetprgb+2]=0;
        }
}
// ------------------------------------------------------------------------------------------


function salir_marca_tiempo_vert($axy){
	global $NOINFO, $RESULTADOS, $ERRORES, $ADVERTENCIAS, 
		$DEPURACION_BAJA, $DEPURACION_MEDIA, $DEPURACION_ALTA, $DEPURANDO;
        global $width,$pixeles,$pixelesdebug;
	$x=$axy['x'];
	$y=$axy['y'];
	if($DEPURANDO>=$DEPURACION_ALTA) print(" salir de marca de tiempo desde $x,$y\n");
	for(;;$y++){
		// buscar un blanco (parte inferior de recytangulo actual
	        $offsetp = $y*$width + $x;  // en el arreglo lineal
		$offsetprgb=$offsetp*3; // r,g,b  cada pixel son 3 elementos en el arreglo
		if($DEPURANDO>=$DEPURACION_ALTA)printf("\ty=$y :%d\n",$pixeles[$offsetprgb]);
		if($pixeles[$offsetprgb]>180)  // umbral de gris, TAL VEZ SEA NECESARIO FILTRO PASA BAJAS
			break;
		$pixelesdebug[$offsetprgb]=255;
		$pixelesdebug[$offsetprgb+1]=0;
		$pixelesdebug[$offsetprgb+2]=0;
	}
	if($DEPURANDO>=$DEPURACION_ALTA)
		print "terminan negros en x:$x, y:$y\n";
	return array('x'=>$x, 'y'=>$y);
}
// ------------------------------------------------------------------------------------------


function encontrar_siguiente_marca_tiempo($axy){
	global $NOINFO, $RESULTADOS, $ERRORES, $ADVERTENCIAS, 
		$DEPURACION_BAJA, $DEPURACION_MEDIA, $DEPURACION_ALTA, $DEPURANDO;
        global $width,$pixeles,$pixelesdebug;
	$x=$axy['x'];
	$y=$axy['y'];
	for(;;$y++){
		// buscar un blanco (parte inferior de rectangulo actual, rumbo a siguiente marca de tiempo)
	        $offsetp = $y*$width + $x;  // en el arreglo lineal
	        $offsetprgb=$offsetp*3; // r,g,b  cada pixel son 3 elementos en el arreglo
		if($pixeles[$offsetprgb]<50)  // umbral de gris, TAL VEZ SEA NECESARIO FILTRO PASA BAJAS
			break;
		$pixelesdebug[$offsetprgb]=0;
		$pixelesdebug[$offsetprgb+1]=0;
		$pixelesdebug[$offsetprgb+2]=255;
	}
	if($DEPURANDO>=$DEPURACION_ALTA) print "Siguiente marca de tiempo en: $x,$y\n";
	return array('x'=>$x, 'y'=>$y);
}
// ------------------------------------------------------------------------------------------

function es_ruido($x,$y){   // promedio de gris de region de 
        global $NOINFO, $CONFIRMACIONES, $ERRORES, $ADVERTENCIAS,
               $DEPURACION_BAJA, $DEPURACION_MEDIA, $DEPURACION_ALTA, $DEPURANDO;
	global $width,$pixeles;
	$sumapix=0;
	for($xi=$x-1; $xi<=$x; $xi++){  // alveolo de 28*28
		for($yi=$y-1; $yi<=$y+1; $yi++){
			$offsetp = $yi*$width + $xi;  // en el arreglo lineal
			$offsetprgb=$offsetp*3; // r,g,b  cada pixel son 3 elementos en el arreglo
			$sumapix+=$pixeles[$offsetprgb];
			if($DEPURANDO>=$DEPURACION_ALTA) printf("x:$xi,y:$yi gris:%d\n",$pixeles[$offsetprgb]);
		}
	}
	if($DEPURANDO>=$DEPURACION_ALTA) printf("x:$x,y:$y prom:%d\n",(int)($sumapix/6));
	return (int)($sumapix/6);
}
?>
