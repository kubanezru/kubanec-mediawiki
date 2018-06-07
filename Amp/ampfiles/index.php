<?php

$xml = new SimpleXMLElement('<data/>');

scanFiles(__DIR__, $xml);

function scanFiles($path, SimpleXMLElement $xml, $allowed = array("html","htm")) {
    $list = scandir($path);
    foreach ( $list as $file ) {
        if ($file == "." || $file == ".." || ! in_array(pathinfo($file, PATHINFO_EXTENSION), $allowed))
            continue;
        $image = $xml->addChild('url');
        $image->addChild('loc', 'https://kuban.ec/mediawiki/extensions/Amp/ampfiles/'.$file);
        $image->addChild('size', filesize($file));
    }
}





header("Content-Type: text/xml");
$xml->asXML('data.xml');

echo $xml->asXML();


?>