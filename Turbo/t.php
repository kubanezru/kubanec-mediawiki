<?php
//header('Content-Type: application/rss+xml; charset=utf-8');
//header("Content-Type: text/xml");

error_reporting(E_ALL);
ini_set('display_errors', 1);




function download_page($path){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$path);
    curl_setopt($ch, CURLOPT_FAILONERROR,1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $retValue = curl_exec($ch); 
	$data= curl_getinfo ($ch);	
	//  print_r($data);	 // смотрим статусы для отладки - http_code
    curl_close($ch);	
    return $retValue;	
}

$sXML = download_page('https://kuban.ec/mediawiki/api.php?hidebots=1&hideminor=1&days=10&limit=15&namespace=0&action=feedrecentchanges&feedformat=rss');
#$sXML = download_page('https://kuban.ec/mediawiki/api.php?action=query&format=json&prop=info&generator=categorymembers&utf8=1&inprop=url&gcmtitle=%D0%9A%D0%B0%D1%82%D0%B5%D0%B3%D0%BE%D1%80%D0%B8%D1%8F:%D0%93%D0%BE%D0%B4%D0%BD%D1%8B%D0%B5_%D1%81%D1%82%D0%B0%D1%82%D1%8C%D0%B8&gcmprop=ids%7Ctitle');
#?action=query&format=xml&prop=info%7Cdescription%7Cpageprops&generator=categorymembers&inprop=url%7Cdisplaytitle&gcmtitle=%D0%9A%D0%B0%D1%82%D0%B5%D0%B3%D0%BE%D1%80%D0%B8%D1%8F:%D0%93%D0%BE%D0%B4%D0%BD%D1%8B%D0%B5_%D1%81%D1%82%D0%B0%D1%82%D1%8C%D0%B8&gcmprop=ids%7Ctitle
#api.php?action=query&format=xml&list=recentchanges&rcprop=title%7Cids%7Csizes%7Cflags%7Cuser%7Ctimestamp&rclimit=35&rctype=edit%7Cnew&rctoponly=1
#api.php?action=query&format=xml&prop=&list=categorymembers&cmtitle=%D0%9A%D0%B0%D1%82%D0%B5%D0%B3%D0%BE%D1%80%D0%B8%D1%8F:%D0%93%D0%BE%D0%B4%D0%BD%D1%8B%D0%B5_%D1%81%D1%82%D0%B0%D1%82%D1%8C%D0%B8&cmprop=ids%7Ctitle%7Ctimestamp%7Ctype&cmlimit=35



$oXML = new SimpleXMLElement($sXML);
// возвращает массив
//print_r($oXML);


//$fffuuu = sanitize($oXML);
//$fffuuu = array_unique($fffuuu);
//print_r($fffuuu);





$turborss = '<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0"
    xmlns:content="http://purl.org/rss/1.0/modules/content/"
    xmlns:dc="http://purl.org/dc/elements/1.1/"
    xmlns:yandex="http://news.yandex.ru"
    xmlns:media="http://search.yahoo.com/mrss/"
    xmlns:turbo="http://turbo.yandex.ru"
    xmlns:atom="http://www.w3.org/2005/Atom"
    xmlns:georss="http://www.georss.org/georss">


 <channel>
    <title>Энциклопедия Кубанец</title>
    <link>http://kuban.ec</link>
    <language>ru</language>
	 <description>Сайт посвящён Краснодарскому краю</description>
';



foreach ($oXML->channel->item as $item){

$url = 'https://kuban.ec/mediawiki/api.php?action=parse&format=xml&page='.$item->title.'&prop=text%7Clinks%7Cimages%7Csections%7Cdisplaytitle%7Cproperties&section=0&disableeditsection=1&disabletidy=1&disablelimitreport=1';
$singleXML = download_page($url);
$osingleXML = new SimpleXMLElement($singleXML);
//print_r($osingleXML);
//echo $osingleXML['parse']['text'];
//echo 'properties: '.$osingleXML->parse[0]->properties->pp;
//echo 'text: '.$osingleXML->parse[0]->text;

if (isset($osingleXML->parse[0]->properties->pp)) $descr = $osingleXML->parse[0]->properties->pp;
if (isset($osingleXML->parse[0]->text)) $sectiontext = strip_tags($osingleXML->parse[0]->text, '<a><img>');
$link = explode('&', $item->link); 


$turborss = $turborss."
  <item turbo='true'>
      <title>".$item->title."</title>
      <link>".$link[0]."</link>
      <pubDate>".$item->pubDate."</pubDate>
      <author>".$item->children('http://purl.org/dc/elements/1.1/')->creator."</author>
          <turbo:content> <![CDATA[
                    <header>
                        <h1>".$item->title."</h1>
                    </header>
		<p>".$sectiontext."</p>
            ]]>
         </turbo:content>
       <description>".$descr."</description>
  </item>
";}






//echo '<item><link>'.$oXML[link].'</link>';

//print_r($oXML);


$turborss = $turborss.'
 </channel>
</rss>';

//$turborss->asXML('ttt.xml');
//echo $turborss->asXML();

//$dom = dom_import_simplexml($turborss)->ownerDocument;
//$dom->preserveWhiteSpace = false;
//$dom->formatOutput = true;
//echo $dom->saveXML();




file_put_contents('ttt.xml',$turborss);

print_r($turborss);





?>