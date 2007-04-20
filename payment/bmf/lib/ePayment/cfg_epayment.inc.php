<?php
/******************************************************************************
Description : Konfiguration der ePayment-Bibliothek

In dieser Datei werden die allgemeine Variablen definiert, 
die fuer den Zugriff auf ePayment benoetigt werden

This file contains all general variables of the ePayment access

*******************************************************************************/

// Die Mandanten-Nr wird von ePayment vergeben
$mandantNr = "B000010";

// Die Bewirtschafter-Nr wird von der Bundeskasse vergeben und muss
// im ePayment fuer den Mandanten konfiguriert werden
$bewirtschafterNr = "63217809"; 

$haushaltsstelle = "6963756938";
$objektNr = "5736059964";
$kennzeichenMahnverfahren = "61000";
$waehrungskennzeichen = "EUR";

// Die URL des ePayment-Servers wird vom ePayment-Team bekannt gegeben,
// die Standard-Einstellung https://epay.bff-online.de:7443/soap/servlet/rpcrouter/
// entspricht dem Integrationssystem
$ePaymentServer = "https://epay-integration.bff-online.de:8443/soap/servlet/rpcrouter";

// Eine Anleitung zum Erstellen eines eigenen Client-Zertifikat 
// ist in der Dokumentation vorhanden
$clientCertificate = 'W:\certs\client.crt';
#$clientCertificate = '/home/jc/certs/client.crt';
#$clientCertificate = realpath('.') . '/../../certs/client.crt';

// Das CA-Zertifikat des ePayment-Systems wird vom ePayment-Team zur Verfuegung
// gestellt
$caCertificate = 'W:\certs\ca.crt';
#$caCertificate = '/home/jc/certs/ca.crt';
#$caCertificate = realpath('.') . '/../../certs/ca.crt';

// Wenn fuer den Zugriff in das Internet Proxys noetig sind, koennen
// die folgenden Zeilen aktiviert und angepasst werden.
//$proxy_host = "10.72.10.91";
//$proxy_port = "3128";

// Timeout f�r die Verbindung setzen (in Sekunden)
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
