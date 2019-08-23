<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$baseDir = dirname(dirname(__FILE__));
//require_once $baseDir.DIRECTORY_SEPARATOR."config".DIRECTORY_SEPARATOR."sml.config.php";

require_once $baseDir.DIRECTORY_SEPARATOR."lib".DIRECTORY_SEPARATOR."PHPMailer.php";
require_once $baseDir.DIRECTORY_SEPARATOR."lib".DIRECTORY_SEPARATOR."SMTP.php";
require_once $baseDir.DIRECTORY_SEPARATOR."lib".DIRECTORY_SEPARATOR."OAuth.php";
require_once $baseDir.DIRECTORY_SEPARATOR."lib".DIRECTORY_SEPARATOR."Exception.php";



class gwMail{
    const hostIn = MAILHOSTIN;
    const hostOut = MAILHOSTOUT;
    const user = MAILUSER;
    const pwdIn = MAILPWDIN;
    const pwdOut = MAILPWDOUT;
    const portIn = MAILPORTIN;
    const portOut = MAILPORTOUT;
    const secure = MAILSECURE;
    const tlsIn = MAILTLSIN;
    const sslIn = MAILSSLIN;
    const from = MAILFROM;
    const auth = MAILAUTH;
    const alias = MAILALIAS;
    
    const defaultLength = 32;
    
    const dsn = MAILDSN;
    
    static function getDB(){
        $dbh = new PDO(self::dsn);
        return $dbh;
    }
    
    static function getMailBox(){
        $mailbox = sprintf('{%s:%s/%s/%s}',self::hostIn,self::portIn,self::tlsIn,self::sslIn);
        $username = self::user;
        $password = self::pwdIn;
        $imapResource = imap_open($mailbox, $username, $password);
        if($imapResource === false){
            throw new Exception(imap_last_error());
        }

        return $imapResource;
    }
    static function generateRandomString($length = self::defaultLength) {
        return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
    }

    static function send($to,$subject,$text,$attachments=Array(),$html=0,$cc=Array(),$bcc=Array()){
        $mail = new PHPMailer;

        $mail->isSMTP();
        //Enable SMTP debugging
        // 0 = off (for production use)
        // 1 = client messages
        // 2 = client and server messages
        $mail->SMTPDebug = 0;
        //Set the hostname of the mail server
        $mail->Host = self::hostOut;
        //Set the SMTP port number - likely to be 25, 465 or 587
        $mail->Port = self::portOut;
        //Whether to use SMTP authentication
        $mail->SMTPAuth = true;
        //Username to use for SMTP authentication
        $mail->Username = self::user;
        //Password to use for SMTP authentication
        $mail->Password = self::pwdOut;
        //Set who the message is to be sent from
        $mail->setFrom(self::from, self::alias);
        //Set an alternative reply-to address
        //Set who the message is to be sent to
        for($i=0;$i<count($to);$i++){
            $mail->addAddress($to[$i]);
        }
        for($i=0;$i<count($cc);$i++){
            $mail->addCC($cc[$i]);
        }
        for($i=0;$i<count($bcc);$i++){
            $mail->addBCC($bcc[$i]);
        }
        for($i=0;$i<count($attachments);$i++){
            $mail->addStringAttachment($attachments[$i]["file"],$attachments[$i]["name"]);
        }
        
        $uuid = self::generateRandomString();
        //Set the subject line
        $mail->Subject = sprintf('%s - %s', $subject, $uuid);
        //Read an HTML message body from an external file, convert referenced images to embedded,
        //convert HTML into a basic plain-text alternative body
        if($html) $mail->isHTML (true);
        $mail->Body = $text;
        if (self::secure) $mail->SMTPSecure = self::secure;
        
        if (!$mail->send()) {
            return Array("success"=>0,"message"=>$mail->ErrorInfo, "uuid"=>"") ;
        } else {
            $sql = "INSERT INTO gw_mail.mail_out(uuid,project,application,oggetto,testo,a,cc,bcc,allegati,protocollo,data_protocollo,tms_protocollo,uidins) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?);";
            $dbh = self::getDB();
            $arrTO =(count($cc))?(sprintf("{'%s'}",implode("','",$to))):(NULL);
            $arrCC =(count($cc))?(sprintf("{'%s'}",implode("','",$cc))):(NULL);
            $arrBCC =(count($cc))?(sprintf("{'%s'}",implode("','",$bcc))):(NULL);
            $protocollo = rand(10000, 200000);
            $data = date('d/m/Y');
            $tms = date('d/m/Y H:i:s');
            $data = Array(
                $uuid,
                'PROGETTO TEST',
                'TEST',
                $mail->Subject,
                $mail->Body,
                $arrTO,
                $arrCC,
                $arrBCC,
                NULL,
                $protocollo,
                $data,
                $tms,
                1
            );
            $stmt = $dbh->prepare($sql);
            if(!$stmt->execute($data)){
                $err = $stmt->errorInfo();
                return Array("success"=>1,"message"=>$err[2], "uuid"=>"");
            }
            else{
                return Array("success"=>1,"message"=>"", "uuid"=>$uuid);
            }
        }
    }
    
    static function getAccettazione($uuid){

        $imapResource = self::getMailBox();
        
        $searchDate = 'SINCE "' . date("j F Y", strtotime("-10 days")) . '"';
        $searchSubject = sprintf('SUBJECT "%s"',$uuid);
        $search = sprintf("%s %s",$searchDate,$searchSubject);
        
        $emails = imap_search($imapResource, $search);

        $folders = imap_listmailbox($imapResource, $mailbox, "*");
        $result = Array("success"=>1,"data"=>Array());
        if(!empty($emails)){
            //Loop through the emails.
            foreach($emails as $email){
                //Fetch an overview of the email.
                $overview = imap_fetch_overview($imapResource, $email);
                $res = $overview;
                $overview = $overview[0];

                $message = imap_fetchbody($imapResource, $email, 1, FT_PEEK);
                if (preg_match("/^ACCETTAZIONE:(.+)/",$overview->subject)){
                    $res["message"]=$message;
                    $res["accettazione"] = 1;
                    $result["data"][] = $res; 
                }
                elseif (preg_match("/^AVVISO DI NON ACCETTAZIONE:(.+)/",$overview->subject)) {
                    $res["message"]=$message;
                    $res["accettazione"] = 0;
                    $result["data"][] = $res; 
                }
            }
        }
        return $result;
    }
    static function getConsegna($uuid,$pec=""){
            
        $imapResource = self::getMailBox();
        $searchDate = 'SINCE "' . date("j F Y", strtotime("-10 days")) . '"';
        $searchSubject = sprintf('SUBJECT "%s"',$uuid);
        //$searchText = ($pec)?(sprintf('TEXT "indirizzato a %s"',$pec)):("");
        $search = sprintf("%s %s",$searchDate,$searchSubject);
        $emails = imap_search($imapResource, $search);

        //$folders = imap_listmailbox($imapResource, $mailbox, "*");
        $result = Array("success"=>1,"data"=>Array());
        if(!empty($emails)){
            //Loop through the emails.
            foreach($emails as $email){
                //Fetch an overview of the email.
                
                $overview = imap_fetch_overview($imapResource, $email);
                
                $overview = $overview[0];
                $res = $overview;
                $message = imap_fetchbody($imapResource, $email, 1.1, FT_PEEK);
                $message = html_entity_decode($message);
                $res = json_decode(json_encode($res),TRUE);
                if (preg_match("/^CONSEGNA:(.+)/",$overview->subject)){
                    $regexp = sprintf('/indirizzato a "%s"/',$pec);
                    if($pec){
                        if (preg_match($regexp,$message)){
                            $res["message"]=$message;
                            $res["consegna"] = 1;
                            $result["data"][] = $res;
                        }
                    }
                    else{
                        $res["message"]=$message;
                        $res["consegna"] = 1;
                        $result["data"][] = $res;
                    }
                }
                elseif (preg_match("/AVVISO DI MANCATA CONSEGNA:(.+)/",$overview->subject)) {
                    if($pec){
                        $regexp = sprintf('/destinato all\'utente "%s"/',$pec);
                        
                        if (preg_match($regexp, $message)){
                            $res["message"]=$message;
                            $res["consegna"] = 0;
                            $result["data"][] = $res;    
                        }
                    }
                    else{
                        $res["message"]=$message;
                        $res["consegna"] = 0;
                        $result["data"][] = $res;
                    }
                }
            }
        }
        return $result;
    }
}
?>