<?php 
#################################################
# Script  : circular.php
# Autor   : Ing. Gary Sandi Vigabriel
# Objetivo: Obtencion de circulares de la ASFI
#################################################

$link_asfi="TEMPO";
$local = circular_local();
$online = circular_online();
if (trim($local)==trim($online)){
	echo "No tengo circulares nuevas";
	exit();
}
else{
	$local = explode("_",$local)[1];
	$online= explode("_",$online)[1];
	$circulares = "";
	for ($i=(int)$local+1; $i <=(int)$online ; $i++) { 
		$circulares.="ASFI_".$i.",";
	}
	$circulares = trim($circulares,",");
	get_circular($circulares);
	uncompress();

}

function get_circular($circulares){
	echo "Descargando: ".$circulares;
	$url = "http://servdmzw.asfi.gob.bo/GeneraActualizacionCirculares/Paginas/archivo.aspx?Numeros=".$circulares;
	$circulares = file_get_contents($url);
	$circulares = explode("</a>",$circulares);
	$link = explode(">",$circulares[0])[1];
	$ch = curl_init($link);
	curl_setopt($ch, CURLOPT_HEADER, 1);
	//curl_setopt($ch, CURLOPT_VERBOSE, 1); // debug
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);	
	$raw_file_data = curl_exec($ch);
	file_put_contents("update.zip", $raw_file_data);
	echo "<br/>Proceso concluido!";	
}


function circular_online(){
	$url = "http://servdmzw.asfi.gob.bo/GeneraActualizacionCirculares/Paginas/Actualizaciones.aspx";
	$circulares = file_get_contents($url);
	$circulares = explode("</label>",$circulares);
	$circulares = explode(",",$circulares[0]);
	$circular = end($circulares);
	return trim($circular);
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

function uncompress(){
	$zip = new ZipArchive;
	exec("unzip -qq -o update.zip -d .");
	
	$source = "reconorm";
	$dest= "normativa";
	foreach (
		$iterator = new \RecursiveIteratorIterator(
		 new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
		 \RecursiveIteratorIterator::SELF_FIRST) as $item
	   ) {
		 if ($item->isDir()) {
		   mkdir($dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
		 } else {
		   copy($item, $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
		 }
	   }
	   borrar_reconorm($source);
	echo "<br/>Descompreso y actualizado!";	
}

function borrar_reconorm($path){
	if (PHP_OS === 'Windows'){
		exec(sprintf("rd /s /q %s", escapeshellarg($path)));
		exec("del update.zip");
	}
	else{
		exec(sprintf("rm -rf %s", escapeshellarg($path)));
		exec("rm update.zip");
	}
}
