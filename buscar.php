<?php
/**
 * Inicialización de librerías necesarias de PKP-OJS
 */
require('lib/pkp/includes/bootstrap.inc.php');
import('classes.search.ArticleSearch');
import('classes.journal.Journal');
import('classes.article.Article');
import('classes.article.PublishedArticle');
import('classes.article.Author');
import('classes.db.DBResultRange');
if (!isset($_GET['pagina']) || $_GET['pagina']==0)
{
  $_GET['pagina'] = 1;
}
if (isset($_GET['prueba']) && $_GET['prueba'] == 1)
{
  $url = "http://192.168.220.28/ojs/";
}else{
  $url = "http://www.icesi.edu.co/revistas/";
}
if (!isset($_GET['revista']))
{
  $_GET['revista'] = 0;
}
switch($_GET['revista']){
  case 0:
    $path = "n00b";
    $_GET['biblio_apps'] = "http://www.icesi.edu.co/se/le/olvido/la/revista";   
  break;
  case 1:
    $path = "estudios_gerenciales";
    $_GET['biblio_apps'] = "http://www.icesi.edu.co/estudios_gerenciales/buscador";
  break;
  case 2:
    $path = "revista_cs";
    $_GET['biblio_apps'] = "http://www.icesi.edu.co/revista_cs/buscador";
  break;
  case 4:
    $path = "sistemas_telematica";
    $_GET['biblio_apps'] = "http://www.icesi.edu.co/sistemas_telematica/buscador";
  break;
  case 6: //desarrollo
    $path = "prueba";
    $_GET['biblio_apps'] = "http://192.168.220.28/estudios_gerenciales/buscador";
  break;
  case 7: //desarrollo
    $path = "prueba_gina";
    $_GET['biblio_apps'] = "http://192.168.220.28/revista_cs/buscador";
  break;
  case 9: //desarrollo
    $path = "sistemas_telematica";
    $_GET['biblio_apps'] = "http://192.168.220.28/sistemas_telematica/buscador";
  break;
}
if (!isset($_GET['query']) || $_GET['query'] == "")
{
  $_GET['query'] = "***";
}
if (!isset($_GET['rpp']))
{
  $_GET['rpp'] = 10;
}
if (!isset($_GET['tipo']))
{
  $_GET['tipo'] = 0;
}

$rangeInfo = new DBResultRange();
$rangeInfo->setCount($_GET['rpp']);
$rangeInfo->setPage($_GET['pagina']);
$journal = new Journal();
$journal->setId($_GET['revista']);
$journal->setPath($path);
$journal->setSequence(1);
$journal->setEnabled(1);
$journal->setPrimaryLocale('es_ES');

$searchType = $_GET['tipo']; // 0 Todos, 1 Autores, 2 Titulo, 4 Resumen, 120 Términos de indexación, 128 texto completo
if (!is_numeric($searchType))
  $searchType = null;

$keywords = array($searchType => ArticleSearch::parseQuery($_GET['query']));
$resultados = & ArticleSearch::retrieveResults($journal, $keywords, null, null, $rangeInfo);
foreach ($resultados as $resultado)
{
  $detalles[] = $resultado;
}
/**
* Posición 0: Resultados
* Posición 1: Resultados por página
* Posición 2: Página actual
* Posición 3: Cantidad de resultados
*/
//echo json_encode($detalles);

$resultados = $detalles[0];
$paginas = ceil(intval($detalles[3]) / intval($_GET['rpp']));
$cont = (intval($detalles[2])-1)*intval($_GET['rpp']);
echo '<div class="app_navigation">';
echo HTMLNavegacionMapas($cont, $paginas, intval($_GET['rpp']), intval($detalles[3]), $_GET['query']);
echo '</div>';
if($detalles[3] <= 0){
  echo "No hay resultados";
}
foreach($resultados as $articulo){
  $titulo = htmlentities(utf8_decode($articulo['publishedArticle']->_data['title']['es_ES']));
  $resumen = substr(strtolower(utf8_decode($articulo['publishedArticle']->_data['abstract']['es_ES'])),0,200)."...";
  $resumen = htmlentities(strtr($resumen,"ÀÈÌÒÙÁÉÍÓÚÇÑÄËÏÖÜ","àèìòùáéíóúçñäëïöü"));
  $revista = $articulo['journal']->_data['path'];
  $edicion = $articulo['issue']->_data['id'];
  $id_envio = $articulo['publishedArticle']->_data['galleys'][0]->_data['submissionId'];
  $titulo_edicion = $articulo['issue']->_data['title']['es_ES'];  
  $enlace_edicion = $url."index.php/$revista/issue/view/$edicion";
  $enlace_articulo = $url."index.php/$revista/article/view/$id_envio";
  foreach($articulo['publishedArticle']->_data['galleys'] as $archivo){
	$id_envio = $archivo->_data['submissionId'];
	$id_archivo = $archivo->_data['id'];
	$etiqueta = $archivo->_data['label'];
	if(!empty($enlace_archivo)){
      $enlace_archivo .= " | ";
    }
	$enlace_archivo .= "<a href=\"".$url."index.php/$revista/article/view/$id_envio/$id_archivo"."\" target=\"_blank\">".$etiqueta."</a>";
  }
  $autores = "";
  foreach($articulo['authors'] as $autor){
    if(!empty($autores)){
      $autores .= ", ";
    }
    $autores .= $autor->_data['firstName'];
  }
  echo "<fieldset>";
  echo "<div>";
  echo "<a href=\"$enlace_edicion\" target=\"_blank\">".$titulo_edicion."</a> | <a href=\"$enlace_articulo\" target=\"_blank\">".$titulo."</a>";
  echo "<span class=\"small\">".$autores."</span>";
  echo "</div>";
  echo "<div>";
  echo $resumen;
  echo "</br>";
  echo $enlace_archivo;
  echo "</div>";
  echo "</fieldset>";
  $enlace_archivo = "";
}
echo '<div class="app_navigation">';
echo HTMLNavegacionMapas($cont, $paginas, intval($_GET['rpp']), intval($detalles[3]), $_GET['query']);
echo '</div>';
echo '<p>Consejos de b&uacute;squeda: </p>
<li>Los t&eacute;rminos de b&uacute;squeda no distinguen min&uacute;sculas/MAY&Uacute;SCULAS</li>
<li>Se ignoran las palabras comunes</li>
<li>Por defecto s&oacute;lo se recuperan los art&iacute;culos que contienen <em>todos</em> los t&eacute;rminos de la b&uacute;squeda (se usa por defecto el operador <strong>AND</strong>)</li>
<li>Combine diferentes palabras con <strong>OR</strong> para encontrar art&iacute;culos que contengan cualquiera de los t&eacute;rminos; p.e., <em>formaci&oacute;n OR investigaci&oacute;n</em></li>
<li>Use par&eacute;ntesis para crear consultas m&eacute;s complejas; p.e., <em>archivo ((revista OR congreso) NOT tesis)</em></li>
<li>Para buscar una frase exacta, p&oacute;ngala entre par&eacute;ntesis; p.e., <em>"publicaciones de acceso abierto"</em></li>
<li>Si quiere excluir una palabra p&oacute;ngale como prefijo <strong>-</strong> o <strong>NOT</strong>; p.e. <em>online -pol&iacute;tica</em> or <em>online NOT pol&iacute;tica</em></li>
<li>Use <strong>*</strong> en un t&eacute;rmino como comod&iacute;n para encontrar cualquier secuencia de caracteres; p.e., <em>moralidad soci* </em> encontrar&iacute;a documentos que contengan "sociol&oacute;gico" o "sociolog&iacute;a"</li>';
/*echo "<pre>";
echo "Items por página:\n";
print_r($detalles[1]);
echo "\nPágina actual:\n";
print_r($detalles[2]);
echo "\nTotal de resultados:\n";
print_r($detalles[3]);
echo "\nDetalle resultados:\n";
print_r($detalles[0]);
echo "</pre>";*/

function HTMLNavegacionMapas($cont, $paginas, $elemPaginas, $tamano, $keyword) {
    $html = '';
    if ($tamano <= 0) {
        $html = '-';
        return $html;
    }
    $numpag = 0;
    $paginaactual = $_GET['pagina'];
    $html.='<table border="0" align="center" class="app_navlinks"><tr>';

    /*Se generan el total de resultados y el indicador de página*/
//    $html.= '<td width="15%" align="center">';
//    $html.= '<table>';
//    $html.= '<tr><td align="center"><strong>Resultados:</strong>'.$tamano.'</td></tr>';
//    $html.= '<tr><td align="center"><strong>P&aacute;gina:</strong>'.$paginaactual.'/'.$paginas.'</td></tr>';
//    $html.= '</table>';
//    $html.= '</td>';
    

    /* Se generan los botones inicio y anterior */
    $style_anterior = 'class="app_navlink_disabled"';
    $style_inicio = 'class="app_navlink_disabled"';
    $tipo_imagen='_disabled';
    if ($paginaactual > 1) {
        $style_inicio = 'class="app_navlink" title="Inicio" onclick="loadBDResults(\'1\',\'' . $elemPaginas . '\', \''.$_GET['tipo'].'\',\'' . $keyword . '\');"';
        $style_anterior = "class=\"app_navlink\" title=\"Anterior\" onclick=\"loadBDResults('".($paginaactual-1)."','$elemPaginas', '".$_GET['tipo']."','$keyword');\"";
        $tipo_imagen='';
    }
    $html.= '<td width="10%" align="left">';
    $html.= '<table><tr>';
    $html.= '<td><a ' . $style_inicio . '><img src="buscador/commons/images/start'.$tipo_imagen.'.png" alt="Inicio" width="20" height="20"></a></td>';
    $html.= '<td><a ' . $style_anterior . '><img src="buscador/commons/images/previous'.$tipo_imagen.'.png" alt="Anterior" title="Anterior" width="20" height="20"></a></td>';
    $html.= '</tr></table>';
    $html.= '</td>';

    /*Se Generan las páginas*/
    $html.= '<td width="80%" align="center">';
    $html.= '<table align="center"><tr>';
    $diferencia_inicio=(($paginaactual-8)<0)? abs(($paginaactual-8)):0;   
    $diferencia_final=(($paginaactual+8)>$paginas)? abs(($paginaactual+8)-$paginas):0;
    for ($i = 0; $i < $paginas; $i++) {
        $claseactual = '';
        $numpag = $i + 1;
        if ($numpag == $paginaactual) {
            $claseactual = '_active';
        }        
        if (($numpag <= $paginas) && (($numpag >= ($paginaactual - 7 - $diferencia_final)) && ($numpag <= ($paginaactual + 7 + $diferencia_inicio)))) {
            $html.= '<td><a class="app_navlink' . $claseactual . '" onclick="loadBDResults(\'' . ($i+1) . '\',\'' . $elemPaginas . '\', \''.$_GET['tipo'].'\',\'' . $keyword . '\');">' . $numpag . '</a></td>';
        }
    }
    $html.= '</tr></table>';
    $html.= '</td>';
    

    /*Se generan los botones siguiente y fin */
    $style_siguiente = 'class="app_navlink_disabled"';
    $style_fin = 'class="app_navlink_disabled"';
    $tipo_imagen_fin='_disabled';
    $modulo_pag=($tamano%$elemPaginas);
    if($modulo_pag==0){
        $modulo_pag=$elemPaginas;
    }
    if ($paginaactual < $paginas) {
        $style_siguiente = 'class="app_navlink" title="Siguiente" onclick="loadBDResults(\'' . ($paginaactual+1) . '\',\'' . $elemPaginas . '\', \''.$_GET['tipo'].'\',\'' . $keyword . '\');"';
        $style_fin = 'class="app_navlink" title="Fin" onclick="loadBDResults(\''.($paginas).'\',\'' . $elemPaginas . '\', \''.$_GET['tipo'].'\',\'' . $keyword . '\');"';
        $tipo_imagen_fin='';
    }

    $html.= '<td width="10%" align="right">';
    $html.= '<table><tr>';
    $html.= '<td><a '.$style_siguiente.'><img src="buscador/commons/images/next'.$tipo_imagen_fin.'.png" alt="Siguiente" title="Siguiente" width="20" height="20"></a></td>';
    $html.='<td><a '.$style_fin.'><img src="buscador/commons/images/end'.$tipo_imagen_fin.'.png" alt="Fin" title="Fin" width="20" height="20"></a></td>';
    $html.= '</tr></table>';
    $html.= '</td>';    

    $html.= '</tr></table>';
    /*indicador de página y resultados*/
    $html.= '<table table border="0" align="center" class="app_navlinks">';
    $html.= '<tr><td align="center">P&aacute;gina '.$paginaactual.' de '.$paginas.'</td></tr>';
    $html.= '<tr><td align="center"><strong>'.$tamano.'</strong> resultados en total</td></tr>';
    $html.= '</table>';
    return $html;
}
?>