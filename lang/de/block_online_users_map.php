<?php //

$string['pluginname'] = 'Besucherkarte';

// config setting titles
$string['centrelat'] = 'Ursprüngliche Breite';
$string['centrelng'] = 'Ursprüngliche Länge';
$string['centreuser'] = 'Zentraler Besucherort';
$string['debug'] = 'Fehlernachrichten zeigen';
$string['googleapikey'] = 'Google Maps API Schlüssel'; 
$string['offline'] = 'Offline Teilnehmer zeigen';
$string['timetosee'] = 'Nach Inaktivität entfernen';
$string['updatelimit'] = 'Maximale Orte zum hochladen'; 
$string['zoomlevel'] = 'Anfängliche Zoom-Stufe';

// config setting explanations
$string['configcentrelat'] = 'Anfängliche zentrale Breite der Karte - in ganzem Dezimalformat (keine Grad/Minuten)';
$string['configcentrelng'] = 'Anfängliche zentrale Länge der Karte - in ganzem Dezimalformat (keine Grad/Minuten)';
$string['configcentreuser'] = 'Karte auf den augenblicklichen Besucherort hin zentrieren mit obiger Zoomstufe. Diese Einstellung hat Vorrang gegenüber obigen Breite/Länge Koordinaten, es sei denn der augenblickliche Besucher hat keinen gültigen Ort';
$string['configdebug'] = 'Während Cron läuft Fehlermeldungen anzeigen';
$string['configgoogleapikey'] = 'Google Maps API Schlüssel, enthält einen Schlüssel von $a'; 
$string['configoffline'] = 'Offline Teilnehmer auch anzeigen?';
$string['configtimetosee'] = 'Anzahl an Minuten, die eine Periode von Inaktivität bestimmen, nach der ein Teilnehmer nicht mehr länger als online angesehen wird.';
$string['configupdatelimit'] = 'Maximale Zahl an Orten für ein Hochladen bei jedem Cron damit es keine Auswirkung auf die Arbeitsleistung hat. Dies muss eine ganze Zahl größer oder gleich 0 sein. Beim Setzen von 0 werden alle Datensätze aktualisiert.'; 
$string['configzoomlevel'] = 'Anfängliche Zoomstufe der Karte.';

$string['periodnminutes'] = 'der letzten {$a} Minuten';
?>