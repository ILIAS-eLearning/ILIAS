<?php
/******************************************************************************
Description : Konfiguration der ePayment-Bibliothek

In dieser Datei werden die allgemeine Variablen definiert, 
die fuer den Zugriff auf ePayment benoetigt werden

This file contains all general variables of the ePayment access

*******************************************************************************/

// Die Mandanten-Nr wird von ePayment vergeben
$mandantNr = "B000000";

// Die Bewirtschafter-Nr wird von der Bundeskasse vergeben und muss
// im ePayment fuer den Mandanten konfiguriert werden
$bewirtschafterNr = "00000000"; 

$haushaltsstelle = "0000000000";
$objektNr = "0000000000";
$kennzeichenMahnverfahren = "61000";
$waehrungskennzeichen = "EUR";

// Die URL des ePayment-Servers wird vom ePayment-Team bekannt gegeben,
// die Standard-Einstellung https://epay.bff-online.de:7443/soap/servlet/rpcrouter/
// entspricht dem Integrationssystem
$ePaymentServer = "https://epay.bff-online.de:7443/soap/servlet/rpcrouter/";

// Eine Anleitung zum Erstellen eines eigenen Client-Zertifikat 
// ist in der Dokumentation vorhanden
$clientCertificate = realpath('.') . '/../../certs/client.crt';

// Das CA-Zertifikat des ePayment-Systems wird vom ePayment-Team zur Verfuegung
// gestellt
$caCertificate = realpath('.') . '/../../certs/ca.crt';

// Wenn fuer den Zugriff in das Internet Proxys noetig sind, koennen
// die folgenden Zeilen aktiviert und angepasst werden.
//$proxy_host = "10.72.10.91";
//$proxy_port = "3128";

// Timeout für die Verbindung setzen (in Sekunden)
$timeOut = 60;

$bmfConfig = array(
	"mandantNr" => $mandantNr,
	"bewirtschafterNr" => $bewirtschafterNr, 
	"haushaltsstelle" => $haushaltsstelle,
	"objektNr" => $objektNr,
	"kennzeichenMahnverfahren" => $kennzeichenMahnverfahren,
	"waehrungskennzeichen" => $waehrungskennzeichen,
	"ePaymentServer" => $ePaymentServer,
	"clientCertificate" => $clientCertificate,
	"caCertificate" => $caCertificate,
	"timeOut" => $timeOut
);

?>
