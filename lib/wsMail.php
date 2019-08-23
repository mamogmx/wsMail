<?php

require_once "nusoap".DIRECTORY_SEPARATOR."nusoap.php";

$server = new nusoap_server; 
$server->soap_defencoding = 'UTF-8';
$server->decode_utf8 = false;
$server->configureWSDL('wsMail', SERVICE_URL);

$server->wsdl->addComplexType(
    'verificaInvio','complexType','struct','all','',Array(
        "uuid"=>Array("name"=>"uuid","type"=>"xsd:string"),
        "message_id"=>Array("name"=>"message_id","type"=>"xsd:string"),
        "oggetto"=>Array("name"=>"oggetto","type"=>"xsd:string"),
        "from"=>Array("name"=>"from","type"=>"xsd:string"),
        "to"=>Array("name"=>"to","type"=>"xsd:string"),
        "pec"=>Array("name"=>"pec","type"=>"xsd:string"),
        "data"=>Array("name"=>"data","type"=>"xsd:string"),
        "uid"=>Array("name"=>"uid","type"=>"xsd:string"),
        "accettazione"=>Array("name"=>"accettazione","type"=>"xsd:string"),
        "consegna"=>Array("name"=>"consegna","type"=>"xsd:string")
    )
);
$server->wsdl->addComplexType(
    'allegato','complexType','struct','all','',Array(
        "name"=>Array("name"=>"name","type"=>"xsd:string"),
        "file"=>Array("name"=>"file","type"=>"xsd:string")
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
            "type"=>"tns:allegato"
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
$server->register('inviaMail',
    Array(
        "to"=>"tns:strArray", 
        "oggetto"=>"xsd:string",
        "testo"=>"xsd:string",
        "allegati"=>"tns:allegati",
        "cc"=>"xsd:strArray",
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

function inviaPec($to,$oggetto,$testo,$allegati=Array(),$cc=Array(),$hash){
    $attachments=Array();
    for($i=0;$i<count($allegati);$i++){
        $contenuto = base64_decode($allegati[$i]["file"]);
        $attachments[]=Array("name"=>$allegati[$i]["name"],"file"=>$contenuto);
    }
    $res = gwMail::send($to, $oggetto, $testo,  $attachments, 0, $cc);
    return $res;
}

function verificaInvio($uuid,$hash){
    $result = Array(
        "success"=>1,
        "message"=>"",
        "accettazioni"=>Array(),
        "consegne"=>Array()
    );
    $res = gwMail::getAccettazione($uuid);
    if ($res["success"]==1){

        $data = $res["data"];
        for($i=0;$i<count($data);$i++){
            $r = $data[$i];
            $result["accettazioni"][] =  Array(
                "uuid"=>$uuid,
                "message_id"=>$r["message_id"],
                "oggetto"=>$r["oggetto"],
                "from"=>$r["from"],
                "to"=>$r["to"],
                "pec"=>"",
                "data"=>$r["data"],
                "uid"=>$r["uid"],
                "accettazione"=>$r["accettazione"],
                "consegna"=>"0"
            );
        }
    }
    $res = gwMail::getConsegna($uuid);
    if ($res["success"]==1){

        $data = $res["data"];
        for($i=0;$i<count($data);$i++){
            $r = $data[$i];
            $result["accettazioni"][] =  Array(
                "uuid"=>$uuid,
                "message_id"=>$r["message_id"],
                "oggetto"=>$r["oggetto"],
                "from"=>$r["from"],
                "to"=>$r["to"],
                "pec"=>$r["pec"],
                "data"=>$r["data"],
                "uid"=>$r["uid"],
                "accettazione"=>"0",
                "consegna"=>$r["consegna"]
            );
        }
    }
    
}

$HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '';

$server->service($HTTP_RAW_POST_DATA);
?>