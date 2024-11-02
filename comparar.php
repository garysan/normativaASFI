<?php 
#################################################
# Script  : comparar.php
# Autor   : Ing. Gary Sandi Vigabriel
# Objetivo: Comparar estructura actual con la WEB
#################################################

$urlASFI="https://servdmzw.asfi.gob.bo/circular";



$local = circular_local();
$local = explode("_",$local)[1];



echo "Actualizando circulares posteriores a:".$local."\n";
get_circular_posterior($urlASFI,$local);

$localn = circular_local();
$localn = explode("_",$localn)[1];

if ($local<$localn){
	$localFiles = getDirContents('normativa');
	$localDir = realpath('normativa');

	echo "Actualizando Caratula de CIRCULARES.pdf\n";	
	$circularesURL = $urlASFI."/Circulares/Circulares.pdf";
	$circularesLocal = $localDir."\Circulares\Circulares.pdf";
	get_file_asfi($circularesURL,$circularesLocal);

	echo "CIRCULARES ACTUALIZADAS, Actualizando contenido...\n";
	$i = 0;
	foreach ($localFiles as $key => $file) {
		//continue; 
		if (!is_file($file)) {
			continue; 
		}
		if (!strpos($file, "ASFI_785") === false ) {
			// lo tenemos disminuido
			continue; 
		}
		$arch = str_replace($localDir, '', $file);

		$archivo = str_replace('\\', '/', $arch);
		$url = $urlASFI.$archivo;
		$url = str_replace(' ', '%20', $url);
		
		
		$headers = get_headers($url, 1);
		if($headers === false ){
			echo "Error en URL: ".$url." Quizas no existe\n";
		}
		$websize = $headers['Content-Length'];
		if (is_array($websize)) {
			$websize = $websize[1];
		} else {
			$websize = $websize;
		}

		$localsize = filesize($file);
		
		if($websize > $localsize){
			get_file_asfi($url,$file);
			echo "Actualizando... $file \n";			
			$i++;
		}
	}
	echo "Se actualizaron:".$i." Archivos...\n";	
}


	
function get_circular_posterior($urlASFI,$local,$intentos = 0){
	$descarga = true;
	if ($intentos >= 5) {
		return;
	}
	$local = (int)$local+1;
	$circular = "ASFI_".$local.".pdf";	
	$file = "normativa/Circulares/".$circular;
	$url = $urlASFI."/Circulares/".$circular;
	$headers = @get_headers($url, 1);

	if($headers === false ){
		echo "No existe respuesta de la cabecera.\n";
		$descarga = false;
	}
	if (isset($headers['Content-Type']) && $headers['Content-Type'] !== 'application/pdf') {
		echo "No existe ".$circular.", Comprobando los siguiente\n";
		$descarga = false;
    }
	
	if($descarga){
		echo "Descargando... ".$circular."\n";
		//$fechaRemoto = strtotime($headers['Last-Modified']);
		get_file_asfi($url,$file);
	}
	get_circular_posterior($urlASFI,$local,$intentos +1 );	
}

function get_file_asfi($url,$file){
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_ENCODING, "");
	//curl_setopt($ch, CURLOPT_HEADER, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		
	$webFile = curl_exec($ch);
	curl_close($ch);
	file_put_contents($file, $webFile);

	//touch($file, $fechaRemoto);
}
function getDirContents($dir, &$results = array()) {
	$files = scandir($dir);
    foreach ($files as $key => $value) {
		
        $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
		// omitimos esta circular por que se comprimio de forma externa
		// era demasiado grande
		if (strpos($path, "ASFI_785") === false && 
			strpos($path, "Anexos") === false && 
			//strpos($path, "Circulares/Circulares.pdf") === false && 
			strpos($path, "Manuales") === false && 
			strpos($path, "Textos") === false) {
			continue; 
		}
		
        if (!is_dir($path)) {
            $results[] = $path;
        } else if ($value != "." && $value != "..") {
            getDirContents($path, $results);
            $results[] = $path;
        }
    }

    return $results;
}

function circular_local(){
	$path    = 'normativa/Circulares';
	$circulares = array();
	foreach (glob($path."/ASFI_*.pdf") as $archivos)
	   	$circulares[] = $archivos;
		$circular = str_replace($path."/","",end($circulares));
		$circular = str_replace(".pdf","",$circular);
	return trim($circular);
}
