#################################################
# Script  : circular.py
# Autor   : Ing. Gary Sandi Vigabriel
# Objetivo: Obtencion de circulares de la ASFI
#################################################

import mechanize
import subprocess

link_asfi="TEMPO"

def get_circular(parametro):
	url_circular = 'http://servdmzw.asfi.gob.bo/GeneraActualizacionCirculares/Paginas/archivo.aspx?Numeros='+parametro
	brc = mechanize.Browser()
	rcirc = brc.open(url_circular)
	tcirc= rcirc.read()
	lseg=tcirc.split("</a>")
	lseg2=lseg[0].split(">")
	link=lseg2[1]
	return_code = subprocess.call(["wget","-c",link])
	global link_asfi
	link_asfi=lseg2[1]
	print return_code

def circular_online():
	url = 'http://servdmzw.asfi.gob.bo/GeneraActualizacionCirculares/Paginas/Actualizaciones.aspx'
	br = mechanize.Browser()
	response = br.open(url)
	valores= response.read()
	seg=valores.split("</label>")
	cir=seg[0].replace("<label id='Circulares'>","")
	circular=cir.split(",")
	return circular[-1]

def circular_local():
	command = "ls -a /var/www/html/asfi/Circulares/ASFI_*"
	process = subprocess.Popen(command, stdout=subprocess.PIPE, stderr=subprocess.PIPE, shell=True)
	output, error = process.communicate()
	archivos = output.split("\n")
	lastfile = archivos[-2].split(".pdf")
	localfile= lastfile[0].split("/")
	return localfile[6]

def uncompress(link):
	larch=link.split("/")
	arch=larch[-1]	
	ucode= subprocess.call(["unzip","-o",arch,"-d","/home/"])

local=circular_local()
print "CIRCULAR LOCAL: "+ local

online=circular_online()
print "CIRCULAR ONLINE: "+ online

if local==online:
	print "No tengo circulares nuevas"

else:
	lfon1=local.split("_")
	lfon=lfon1[1]
	onfi1=online.split("_")
	onfi=onfi1[1]
	cirasfi="";
	for circular in range(int(lfon)+1,int(onfi)+1):
		cirasfi += "ASFI_"+str(circular);
		cirasfi += ",";
	param = cirasfi.rstrip(',')
	print "Obteniendo:"+param
	get_circular(param)
	uncompress (link_asfi)
