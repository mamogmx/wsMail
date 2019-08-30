<?php

require_once "nusoap".DIRECTORY_SEPARATOR."nusoap.php";

$server = new nusoap_server; 
$server->soap_defencoding = 'UTF-8';
$server->decode_utf8 = false;
$server->configureWSDL('wsMail', SERVICE_URL);

$server->wsdl->addComplexType(
    'strArray','complexType','array','',
    'SOAP-ENC:Array',
    array(),
    array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'xsd:string[]'))
);

$server->wsdl->addComplexType(
    'mail','complexType','struct','all','',Array(
        "uuid"=>Array("name"=>"uuid","type"=>"xsd:string"),
        "message_id"=>Array("name"=>"message_id","type"=>"xsd:string"),
        "oggetto"=>Array("name"=>"oggetto","type"=>"xsd:string"),
        "from"=>Array("name"=>"from","type"=>"xsd:string","description"=>"Host dal quale arriva la ricevuta"),
        "to"=>Array("name"=>"to","type"=>"xsd:string","description"=>"Host al quale arriva la ricevuta"),    
        "data"=>Array("name"=>"data","type"=>"xsd:string"),
        "uid"=>Array("name"=>"uid","type"=>"xsd:string"),
        "testo"=>Array("name"=>"testo","type"=>"xsd:string")
    )
);

$server->wsdl->addComplexType(
    'verificaInvio','complexType','struct','all','',Array(
        "uuid"=>Array("name"=>"uuid","type"=>"xsd:string"),
        "message_id"=>Array("name"=>"message_id","type"=>"xsd:string"),
        "oggetto"=>Array("name"=>"oggetto","type"=>"xsd:string"),
        "from"=>Array("name"=>"from","type"=>"xsd:string","description"=>"Host dal quale arriva la ricevuta"),
        "to"=>Array("name"=>"to","type"=>"xsd:string","description"=>"Host al quale arriva la ricevuta"),
        "pec"=>Array("name"=>"pec","type"=>"xsd:string","description"=>"Pec della quale si verifica la consegna"),
        "data"=>Array("name"=>"data","type"=>"xsd:string"),
        "uid"=>Array("name"=>"uid","type"=>"xsd:string"),
        "accettazione"=>Array("name"=>"accettazione","type"=>"xsd:string"),
        "consegna"=>Array("name"=>"consegna","type"=>"xsd:string")
    )
);
$server->wsdl->addComplexType(
    'allegato','complexType','struct','all','',Array(
        "name"=>Array("name"=>"name","type"=>"xsd:string","description"=>"Nome del file"),
        "file"=>Array("name"=>"file","type"=>"xsd:string","description"=>"Contenuto del file codificato in base 64")
    )
);

$server->wsdl->addComplexType(
    'allegati',
    'complexType',
    'array',
    '',
    'SOAP-ENC:Array',
    Array("allegato"=>
        Array(
            "name"=>"allegato",
            "type"=>"tns:allegato",
            
        )
    ),
    Array( 
        Array( 
            "ref" => "SOAP-ENC:arrayType",
            "wsdl:arrayType" => "tns:allegato[]"
        )
    ),
    "tns:allegato"
);

$server->wsdl->addComplexType(
    'verificaInvii',
    'complexType',
    'array',
    '',
    'SOAP-ENC:Array',
    Array("allegato"=>
        Array(
            "name"=>"verificaInvio",
            "type"=>"tns:verificaInvio"
        )
    ),
    Array( 
        Array( 
            "ref" => "SOAP-ENC:arrayType",
            "wsdl:arrayType" => "tns:verificaInvio[]"
        )
    ),
    "tns:verificaInvio"
);
$server->register('inviaPec',
    Array(
        "to"=>"tns:strArray", 
        "oggetto"=>"xsd:string",
        "testo"=>"xsd:string",
        "allegati"=>"tns:allegati",
        "cc"=>"tns:strArray",
        "hash"=>"xsd:string"
    ),
    Array(
        "success"=>"xsd:int",
        "message"=>"xsd:string",
        "uuid"=>"xsd:string"
    ),
    'urn:wsMail',
    'urn:wsMail#inviaMail',
    'rpc',
    'encoded',
    'Metodo che invia una Pec con eventuali allegati'
);
$server->register('inviaMail',
    Array(
        "to"=>"tns:strArray", 
        "oggetto"=>"xsd:string",
        "testo"=>"xsd:string",
        "allegati"=>"tns:allegati",
        "cc"=>"tns:strArray",
        "hash"=>"xsd:string"
    ),
    Array(
        "success"=>"xsd:int",
        "message"=>"xsd:string",
        "uuid"=>"xsd:string"
    ),
    'urn:wsMail',
    'urn:wsMail#inviaMail',
    'rpc',
    'encoded',
    'Metodo che invia una Mail con eventuali allegati'
);
$server->register('verificaPec',
    Array(
        "uuid"=>"xsd:string", 
        "hash"=>"xsd:string"
    ),
    Array(
        "success"=>"xsd:int",
        "message"=>"xsd:string",
        "accettazioni"=>"tns:verificaInvii",
        "consegne"=>"tns:verificaInvii"
        
    ),
    'urn:wsMail',
    'urn:wsMail#verificaInvio',
    'rpc',
    'encoded',
    'Metodo che verifica lo stato di accettazzione e di consegna di una PEC dato il suo UUID'
);


$server->register('leggiMail',
    Array(
        "uuid"=>"xsd:string", 
        "hash"=>"xsd:string"
    ),
    Array(
        "success"=>"xsd:int",
        "message"=>"xsd:string",
        "result"=>"tns:mail"
        
    ),
    'urn:wsMail',
    'urn:wsMail#leggiMail',
    'rpc',
    'encoded',
    'Metodo che legge una Mail dato il suo UUID'
);
function inviaPec($to,$oggetto,$testo,$allegati=Array(),$cc=Array(),$hash){
    $attachments=Array();
    for($i=0;$i<count($allegati);$i++){
        $contenuto = base64_decode($allegati[$i]["file"]);
        $attachments[]=Array("name"=>$allegati[$i]["name"],"file"=>$contenuto);
    }
    $res = gwMail::send($to, $oggetto, $testo,  $attachments, 0, $cc);
    return $res;
}

function inviaMail()
{
    $attachments=Array();
    for($i=0;$i<count($allegati);$i++){
        $contenuto = base64_decode($allegati[$i]["file"]);
        $attachments[]=Array("name"=>$allegati[$i]["name"],"file"=>$contenuto);
    }
    $res = gwMail::send($to, $oggetto, $testo,  $attachments, 0, $cc);
    return $res;
}

function verificaPec($uuid,$hash){
    $result = Array(
        "success"=>1,
        "message"=>"",
        "accettazioni"=>Array(),
        "consegne"=>Array()
    );
    $result = gwMail::verificaUUID($uuid);
    
    return $result;
}

function leggiMail($uuid,$hash){
    $result = Array(
        "success" => 0,
        "message" => "Funzionalita non ancora implementata",
        "mail" => Array(
            "uuid"=>"",
            "message_id"=>"",
            "oggetto"=>"",
            "from"=>"",
            "to"=>"",    
            "data"=>"",
            "uid"=>"",
            "testo"=>""
        )
    );
}
$HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '';

$server->service($HTTP_RAW_POST_DATA);
?>