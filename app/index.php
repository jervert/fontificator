<?php
if (!defined('PHP_VERSION_ID')) {
	$versionPhp = explode('.', PHP_VERSION);
	define('PHP_VERSION_ID', ($versionPhp[0] * 10000 + $versionPhp[1] * 100 + $versionPhp[2]));
}
$sistema_php = PHP_OS;
if ($sistema_php=='Windows' or $sistema_php=='WIN32' or $sistema_php=='WINNT') {
  $sistema = 'windows';
} else if ($sistema_php=='Linux') {
  $sistema = 'linux_x86';
} else {
  //$sistema = 'windows';
  //$sistema = 'linux_x86';
  $sistema = 'linux_amd64';
}

$directorio_completo = getcwd();
if ($sistema=='windows') {

	$directorio_completo_archivos = $directorio_completo.'\\archivos\\';
	$bin_eot = '"'.$directorio_completo.'\bin\ttf2eot.exe"';
	$bin_woff = '"'.$directorio_completo.'\bin\sfnt2woff.exe"';
	$bin_svg = '"'.$directorio_completo.'\bin\batik\batik-ttf2svg.jar"';
	$separador_eot = ' ';
} elseif ($sistema=='linux_x86') {
	$directorio_completo_archivos = $directorio_completo."/archivos/";
	$bin_eot = ''.$directorio_completo.'/bin/ttf2eot';
	$bin_woff = ''.$directorio_completo.'/bin/sfnt2woff';
	$bin_svg = ''.$directorio_completo.'/bin/batik/batik-ttf2svg.jar';
	$separador_eot = ' > ';
} else {
	$directorio_completo_archivos = $directorio_completo."/archivos/";
	$bin_eot = ''.$directorio_completo.'/bin/ttf2eot_amd64';
	$bin_woff = ''.$directorio_completo.'/bin/sfnt2woff_amd64';
	$bin_svg = ''.$directorio_completo.'/bin/batik/batik-ttf2svg.jar';
	$separador_eot = ' > ';
}

// Bloque subir archivos
$directorio_destino = "archivos/";
$accion_subir = 'enviar_archivos';
$html = '';
$estilo = '';
if (isset($_POST['c_accion']) and $_POST['c_accion'] == $accion_subir) {
	
	$html = '<section id="resultados"><h2>Tipografía convertida</h2>';
	
	$i=0;
	$num = count($_FILES['archivos']['name']);
	if ($num>1) {$tagMensaje='li'; $html .= '<ul>';} else {$tagMensaje='p';}
	$mensaje = '1';
	$char1 = array(' ','á','Á','é','É','í','Í','ó','Ó','ú','Ú','ü','Ü',',',"'",'ç','Ç','.TTF','.OTF');
	$char2 = array('_','a','A','e','E','i','I','o','O','u','U','u','U','_','_','c','C','.ttf','.otf');
	while ($i<$num) {
		$archivo_solo = str_replace($char1,$char2,basename($_FILES['archivos']['name'][$i]));
		$archivo = $directorio_destino.$archivo_solo;
		
		
		$extensiones_tipografias = array('.ttf','.otf');
		if (move_uploaded_file($_FILES['archivos']['tmp_name'][$i], $archivo)) {
			$archivo_solo_eot = str_replace($extensiones_tipografias,'.eot',$archivo_solo);
			$archivo_solo_woff = str_replace($extensiones_tipografias,'.woff',$archivo_solo);
			$archivo_solo_svg = str_replace($extensiones_tipografias,'.svg',$archivo_solo);
			$archivo_solo_zip = str_replace($extensiones_tipografias,'_'.microtime().'.zip',$archivo_solo);
			$archivo_entero = '"'.$directorio_completo_archivos.$archivo_solo.'"';
			$archivo_eot = '"'.$directorio_completo_archivos.$archivo_solo_eot.'"';
			$archivo_woff = '"'.$directorio_completo_archivos.$archivo_solo_woff.'"';
			$archivo_svg = '"'.$directorio_completo_archivos.$archivo_solo_svg.'"';
			$archivo_zip = '"'.$directorio_completo_archivos.$archivo_solo_zip.'"';
			
			$exec_woff = $bin_woff.' '.$archivo_entero;
			$exec_eot = $bin_eot.' '.$archivo_entero.$separador_eot.$archivo_eot;
			echo PHP_VERSION_ID;
			if ($sistema=='windows' and PHP_VERSION_ID<50300) {
				$comillaExec = '"';
			} else {
				$comillaExec = '';
			}
			
			exec($comillaExec.$exec_woff.$comillaExec); //echo $bin_woff.' '.$archivo_entero.'<br>';
			exec($comillaExec.$exec_eot.$comillaExec); //echo $bin_eot.' '.$archivo_entero.$separador_eot.$archivo_eot.'<br>';
			exec('java -jar '.$bin_svg.' '.$archivo_entero.' -o '.$archivo_svg.' -id '.$_POST['c_nombre_tipografia'].'');
			
			
			$codigo_css = "@font-face {
	font-family: '".$_POST['c_nombre_tipografia']."';
	src: url('".$archivo_solo_eot."');
	src: local('☺'), url('".$archivo_solo_woff."') format('woff'), url('".$archivo_solo."') format('truetype'), url('".$archivo_solo_svg."#".$_POST['c_nombre_tipografia']."') format('svg');
	font-weight: normal;
	font-style: normal;
}
.prueba {font-family:".$_POST['c_nombre_tipografia']."; font-size:1.6em;}";
$estilos_pre = '<pre><code>@font-face &#123;
	font-family: &#039;'.$_POST['c_nombre_tipografia'].'&#039;;
	src: url(&#039;'.$archivo_solo_eot.'&#039;);
	src: local(&#039;☺&#039;), url(&#039;'.$archivo_solo_woff.'&#039;) format(&#039;woff&#039;), url(&#039;'.$archivo_solo.'&#039;) format(&#039;truetype&#039;), url(&#039;'.$archivo_solo_svg.'#'.$_POST['c_nombre_tipografia'].'&#039;) format(&#039;svg&#039;);
	font-weight: normal;
	font-style: normal;
&#125;</code></pre>';
			$archivo_solo_css = str_replace($extensiones_tipografias,'.css',$archivo_solo);
			$archivo_css = 'archivos/'.$archivo_solo_css;
			$fh = fopen($archivo_css, 'w') or die("can't open file");
			fwrite($fh, $codigo_css);
			fclose($fh);

			$codigo_html = '

<!doctype html>
<html>
<head>
	<title>@font-face: Prueba de tipografía '.$archivo_solo.'</title>
	<meta charset="utf-8" />
	<link href="'.$archivo_solo_css.'" rel="stylesheet" type="text/css" media="all" />
</head>
<body>
<h1>@font-face: Prueba de tipografía '.$archivo_solo.'</h1>
<p class="prueba">Prueba de texto con la tipografía '.$archivo_solo.'.</p>
'.$estilos_pre.'

</body>
</html>

';
$archivo_solo_html = str_replace($extensiones_tipografias,'.html',$archivo_solo);
			$archivo_html = 'archivos/'.$archivo_solo_html;
			$fh = fopen($archivo_html, 'w') or die("can't open file");
			fwrite($fh, $codigo_html);
			fclose($fh);


function create_zip($files = array(),$destination = '',$overwrite = true) {
	if(file_exists($destination) && !$overwrite) { return false; }
	$valid_files = array();
	if(is_array($files)) {
		foreach($files as $file) {
			if(file_exists($file)) {
				$valid_files[] = $file;
			}
		}
	}
	if(count($valid_files)) {
		$zip = new ZipArchive();
		if($zip->open($destination,$overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) !== true) {
			return false;
		}
		foreach($valid_files as $file) {
			$zip->addFile($file,$file);
		}
		//echo 'The zip archive contains ',$zip->numFiles,' files with a status of ',$zip->status;
		$zip->close();
		
		return file_exists($destination);
	}
	else
	{
		return false;
	}
}
$files_to_zip = array('archivos/'.$archivo_solo_eot,'archivos/'.$archivo_solo_woff,'archivos/'.$archivo_solo_svg,'archivos/'.$archivo_solo,'archivos/'.$archivo_solo_css,'archivos/'.$archivo_solo_html);
create_zip($files_to_zip,'archivos/'.$archivo_solo_zip);
$mm=0; while ($mm<count($files_to_zip)) {
	unlink($files_to_zip[$mm]);
	$mm++;
}
			
			
			$html .= '<div class="exito"><'.$tagMensaje.'><strong>'.$archivo_solo.'</strong> se ha convertido correctamente.</'.$tagMensaje.'></div>
			<p class="descargar"><strong><a href="archivos/'.$archivo_solo_zip.'">Descargar en formato ZIP</a></strong></p>
			';
			$html .= '<p>Ejemplo de CSS:</p>';
			$html .= $estilos_pre;



		} else {
			$html .= '<'.$tagMensaje.'><em>'.$archivo.'</em> no se ha podido enviar.</'.$tagMensaje.'>';
			$mensaje = '2';
		}
		$i++;
	}
	//if ($num>1) {$html .= '</ul>';}
	//if (empty($_POST['js'])) {header("Location: uploader.php?mensaje=".$mensaje."");} else {echo '<div id="iframe_mensaje">'.$mensaje.'</div>';}
	$html .= '</section>';
}
?><!doctype html>
<html lang="en">
<head>
	<title>Fontificator: Asistente de CSS3 @font-face y conversión de tipografías</title>
	<meta charset="utf-8" />
	<link media="screen" type="text/css" href="ficheros/OTB_CSS_asisente_font_face.css" rel="stylesheet">
</head>
<body>

<div id="contenedor">
<header id="cabecera">
	<div id="cabecera_titulo">
		<div class="fondo"></div>
		<h1 class="sin_enlace">Fontificator</h1>
		<p class="descripcion">Asistente de CSS3 @font-face y conversión de tipografías</p>
	</div>
</header>
<div id="cuerpo">
<?php
echo $html;
?>
<section id="formulario_tipografia">
<form action="index.php" method="post" enctype="multipart/form-data">
	<input type="hidden" name="c_accion" value="<?php echo $accion_subir; ?>" />
	<fieldset>
		<legend>Envío de tipografía</legend>
		<p class="advertencia">Asegúrate de tener los derechos necesarios para poder utilizar la tipografía.</p>
		<p class="clear"><label for="c_tipografia"><span>Archivo de tipografía <em>(formato TTF)</em>:</span><input type="file" id="c_tipografia" name="archivos[]" /></label></p>
		
		<p class="clear"><label for="c_nombre_tipografia"><span>Nombre de tipografía<em>(sin espacios, acentos, etc.)</em>:</span><input type="text" id="c_nombre_tipografia" name="c_nombre_tipografia" /></label></p>
		
		<!--div class="lista">
		  <p class="label">Convertir a:</p>
		  <ul class="campoCheckbox clear">
			 <li class="clear"><label for="c_formato_eot"><input type="checkbox" name="c_formato_eot" id="c_formato_eot" value="1" checked="checked" /><span><span class="indentado">Formato </span>EOT</span></label></li>
			 <li class="clear"><label for="c_formato_woff"><input type="checkbox" name="c_formato_woff" id="c_formato_woff" value="1" checked="checked" /><span><span class="indentado">Formato </span>WOFF</span></label></li>
			 <li class="clear"><label for="c_formato_svg"><input type="checkbox" name="c_formato_svg" id="c_formato_svg" value="1" /><span><span class="indentado">Formato </span>SVG</span></label></li>
		  </ul>
		</div-->
		
	</fieldset>
	
	<p class="botonera"><input type="submit" value="Convertir" /></p>
</form>
</section>
<article id="contenido">
<h2>Uso de @font-face</h2>
<p>Lo primero de todo es advertir que ésta técnica no valida en CSS 2.1, aunque si lo hace en CSS 3.</p>

<p>La llamada desde la CSS a una tipografía ayuda a eludir la necesidad de utilizar imágenes con texto (o Flash) en aquellos casos en los que se quiere utilizar una tipografía que no es de sistema.</p>
<p>Hay que limitarse en el uso de tipografías distintas de las habituales, evitando utilizarlas más allá de títulos (para no empeorar la legibilidad) y no utilizar varias (para evitar que la descarga de las mismas lleve un tiempo excesivo).</p>
<p>En la CSS hay que hacer uso de la regla <code class="css">@font-face</code>:</p>
<pre><code class="css">@font-face &#123;
  font-family: &#039;nombre-de-tipografia-en-la-css&#039;;
  src: url(&#039;fuente.eot&#039;);
  src: local(&#039;☺&#039;), url(&#039;fuente.woff&#039;) format(&#039;woff&#039;), url(&#039;fuente.ttf&#039;) format(&#039;truetype&#039;), url(&#039;fuente.svg#nombre-de-tipografia-en-la-css&#039;) format(&#039;svg&#039;);
  font-weight: normal;
  font-style: normal;
&#125;</code></pre>
<p>Propiedades utilizadas:</p>
<ul>
<li><code class="css">font-family</code>: este es el nombre que se usará para la tipografía en las reglas de la CSS.</li>
<li><code class="css">src</code>: ruta del archivo de tipografía.</li>
</ul>
<p>Un ejemplo de uso:</p>
<pre><code class="css">#idElemento &#123;font-family:nombre-de-tipografia-en-la-css, arial, sans-serif;&#125;</code></pre>

<p>Se utilizan en total cuatro archivos de tipografía:</p>
<ul>
  <li><strong>TTF</strong></li>
  <li><strong>EOT:</strong> para Internet Explorer (¡Funciona en Internet Explorer 5.5!).</li>
  <li><strong>WOFF:</strong> estándar W3C para tipografías web en estado de borrador, soportado ya por Firefox (3.6+), Chrome (6+), Opera (11.10+), Internet Explorer (9+), y proximamente Safari. Este formato de tipografía no se puede usar más que para la web, por lo que facilitará la obtención de permisos de uso de las tipografías en la web.</li>
  <li><strong>SVG:</strong> para dispositivos iOS (iPhone, iPod Touch, iPad).</li>
</ul>

<p>Previsiblemente en el futuro el único formato a utilizar será WOFF.</p>

<p>Habitualmente las tipografías nos llegan en formato TTF, por lo que habrá que convertirlas a los distintos formatos:</p>
<ul>
<li><a hreflang="en" href="http://www.fontsquirrel.com/fontface/generator"><span lang="en">Font Squirrel @font-face Generator</span> (enlace externo, en inglés)</a></li>
<li><a hreflang="en" href="http://www.kirsle.net/wizards/ttf2eot.cgi"><span lang="en">TTF to EOT Font Converter</span> (enlace externo, en inglés)</a></li>
</ul>
<h3>Saber más</h3>
<ul>
<li lang="en"><a hreflang="en" href="http://www.w3.org/TR/css3-fonts/#font-face-rule">CSS Fonts Module Level 3: The @font-face rule</a></li>
<li lang="en"><a hreflang="en" href="http://paulirish.com/2009/bulletproof-font-face-implementation-syntax/">Bulletproof @font-face syntax</a></li>
<li><a href="http://www.cssblog.es/sintaxis-de-font-face/">Sintaxis de @font-face</a></li>
<li lang="en"><a href="http://en.wikipedia.org/wiki/Web_Open_Font_Format" hreflang="en">Web Open Font Format (WOFF)</a></li>
</ul>
</article>
</div>
<footer id="pie">
	<div class="licencia">
		<p class="clear">
		<span class="imagen_licencia"><a rel="license" href="http://creativecommons.org/licenses/by-nc-sa/3.0/"><img alt="Licencia de Creative Commons" style="border-width:0" src="http://i.creativecommons.org/l/by-nc-sa/3.0/88x31.png" /></a></span>
		<span class="texto_licencia"><span xmlns:dct="http://purl.org/dc/terms/" href="http://purl.org/dc/dcmitype/InteractiveResource" property="dct:title" rel="dct:type">Fontificator</span> por <span xmlns:cc="http://creativecommons.org/ns#" property="cc:attributionName">Antonio Rodríguez</span> se distribuye bajo licencia <a rel="license" href="http://creativecommons.org/licenses/by-nc-sa/3.0/">Creative Commons Reconocimiento-NoComercial-CompartirIgual 3.0 Unported License</a>.</span>
		
		</p>
	</div>
	<p>Los binarios incluidos en Fontificator son distribuidos mediante las licencias dadas por sus creadores:</p>
	<ul>
		<li><a href="http://people.mozilla.com/~jkew/woff/" hreflang="en">Conversión TTF a WOFF</a></li>
		<li><a href="http://code.google.com/p/ttf2eot/" hreflang="en">ttf2eot, conversión TTF a EOT</a> (<a href="http://www.gnu.org/licenses/old-licenses/gpl-2.0.html" hreflang="en">GNU GPL v2</a>)</li>
		<li><a href="http://xmlgraphics.apache.org/batik/" hreflang="en">Batik, conversión TTF a SVG</a> (<a href="http://xmlgraphics.apache.org/batik/license.html" hreflang="en">Apache</a>)</li>
	</ul>
	<p>Tipografía en títulos de cabecera: <a href="http://www.kimberlygeswein.com/?p=214" hreflang="en"><cite>Architects daughter</cite> por Kimberly Geswein</a> (gratuita para uso no comercial).</p>
</footer>
</div>
</body>
</html>