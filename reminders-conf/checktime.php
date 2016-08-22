#!/usr/bin/php -q 
<?php
 ob_implicit_flush(false); 
 error_reporting(0); 
 set_time_limit(300); 

//   CheckDate, (c) Copyright Ward Mundy, 2006. All rights reserved.

//-------- DON'T CHANGE ANYTHING ABOVE THIS LINE ----------------

 $debug = 1; 
 $emaildebuglog = 0;
 $email = "yourname@yourdomain" ;

//-------- DON'T CHANGE ANYTHING BELOW THIS LINE ----------------

$log = "/var/log/asterisk/checkdate.txt" ;

 $stdlog = fopen($log, 'a'); 
 $stdin = fopen('php://stdin', 'r'); 
 $stdout = fopen( 'php://stdout', 'w' ); 

if ($debug) :
  fputs($stdlog, "CheckTime (c) Copyright Ward Mundy, 2006. All Rights Reserved.\n\n" . date("F j, Y - H:i:s") . "  *** New session ***\n\n" ); 
endif ;

function read() {  
 global $stdin;  
 $input = str_replace("\n", "", fgets($stdin, 4096));  
 dlog("read: $input\n");  
 return $input;  
}  

function write($line) {  
 dlog("write: $line\n");  
 echo $line."\n";  
}  

function dlog($line) { 
 global $debug, $stdlog; 
 if ($debug) fputs($stdlog, $line); 
} 

function execute_agi( $command ) 
{ 
GLOBAL $stdin, $stdout, $stdlog, $debug; 
 
fputs( $stdout, $command . "\n" ); 
fflush( $stdout ); 
if ($debug) 
fputs( $stdlog, $command . "\n" ); 
 
$resp = fgets( $stdin, 4096 ); 
 
if ($debug) 
fputs( $stdlog, $resp ); 
 
if ( preg_match("/^([0-9]{1,3}) (.*)/", $resp, $matches) )  
{ 
if (preg_match('/result=([-0-9a-zA-Z]*)(.*)/', $matches[2], $match))  
{ 
$arr['code'] = $matches[1]; 
$arr['result'] = $match[1]; 
if (isset($match[3]) && $match[3]) 
$arr['data'] = $match[3]; 
return $arr; 
}  
else  
{ 
if ($debug) 
fputs( $stdlog, "Couldn't figure out returned string, Returning code=$matches[1] result=0\n" );  
$arr['code'] = $matches[1]; 
$arr['result'] = 0; 
return $arr; 
} 
}  
else  
{ 
if ($debug) 
fputs( $stdlog, "Could not process string, Returning -1\n" ); 
$arr['code'] = -1; 
$arr['result'] = -1; 
return $arr; 
} 
}  

// ------ Code execution begins here
// parse agi headers into array  
//while ($env=read()) {  
// $s = split(": ",$env);  
// $agi[str_replace("agi_","",$s0)] = trim($s1); 
// if (($env == "") || ($env == "\n")) {  
//   break;  
// }  
//}  

while ( !feof($stdin) )  
{ 
$temp = fgets( $stdin ); 
 
if ($debug) 
fputs( $stdlog, $temp ); 
 
// Strip off any new-line characters 
$temp = str_replace( "\n", "", $temp ); 
 
$s = explode( ":", $temp ); 
$agivar[$s[0]] = trim( $s[1] ); 
if ( ( $temp == "") || ($temp == "\n") ) 
{ 
break; 
} 
}  

if ($debug) :
fputs($stdlog, "The following application-specific variables also were passed from Asterisk: \n" ); 
endif ;


$ThisApp = $_SERVER["argv"][0]; 
$ThisApp = trim($ThisApp); 
//$parts = split("@",$vmbox); 

if ($debug) :
fputs($stdlog, "ThisApp: " . $ThisApp . "\n" ); 
endif ;

// This tells us the DATE to be checked
$APPTDT = $_SERVER["argv"][1]; 
$APPTDT = trim($APPTDT); 
$APPTDT = ltrim($APPTDT); 

if ($debug) :
fputs($stdlog, "APPTDT: " . $APPTDT . "\n\n\n" ); 
endif ;

$APPTTIME = $_SERVER["argv"][2];
$APPTTIME = trim($APPTTIME);
$APPTTIME = ltrim($APPTTIME); 

if ($debug) :
fputs($stdlog, "APPTTIME: " . $APPTTIME . "\n\n\n" );
endif ;

$PMADJUST = $_SERVER["argv"][3];
$PMADJUST = trim($PMADJUST);
$PMADJUST = ltrim($PMADJUST); 

if ($debug) :
fputs($stdlog, "PMADJUST: " . $PMADJUST . "\n\n\n" );
endif ;

if ($PMADJUST<>"0") :
 $APPTTIME = $APPTTIME + 1200 ;
 $txt2write = "SET VARIABLE APPTTIME " . $APPTTIME . "\n" ;
 execute_agi($txt2write) ;
endif ;

$APPTPHONE = $_SERVER["argv"][4];
$APPTPHONE = trim($APPTPHONE);
$APPTPHONE = ltrim($APPTPHONE);

if ($debug) :
fputs($stdlog, "APPTPHONE: " . $APPTPHONE . "\n\n\n" );
endif ;


$BADFLAG=0 ;

if (strlen($APPTTIME)<>4) :
 $BADFLAG=1 ;
endif ;
if (substr($APPTTIME,0,4)<"0005") :
 $BADFLAG=1 ;
endif ;
if (substr($APPTTIME,0,4)>"2359") :
 $BADFLAG=1 ; 
endif ;
if (date("Ymd") <> $APPTDT) :
 $tofile = "/var/spool/asterisk/reminders/" . $APPTTIME . "." . $APPTDT . "." . $APPTPHONE . ".call" ;
else :
 $tofile = "/var/spool/asterisk/outgoing/" . $APPTTIME . "." . $APPTDT . "." . $APPTPHONE . ".call" ;
endif ;

if (file_exists($tofile)) :
  $BADFLAG=1 ;
  if ($debug) :
   fputs($stdlog, "DUP FILE EXISTS: " . $tofile . "\n\n\n" );
   endif ;
else :
  if ($debug) :
   fputs($stdlog, "DUP FILE NOT FOUND: " . $tofile . "\n\n\n" );
  endif ;
endif ; 

$thismo = substr($APPTDT,4,2) + 0 ;
$thisda = substr($APPTDT,6,2) + 0 ;
$thisyr = substr($APPTDT,0,4) + 0 ;
$thishr = substr($APPTTIME,0,2) + 0 ;
$thismin= substr($APPTTIME,2,2) + 0 ;
$NEWTIME = mktime($thishr,$thismin,1,$thismo,$thisda,$thisyr) ;

$nowmo = date("n") + 0 ;
$nowda = date("j") + 0 ;
$nowyr = date("Y") + 0 ;
$nowhr= date("G") + 0 ;
$nowmin=date("i") + 4 ;
$RIGHTNOW = mktime($nowhr,$nowmin,1,$nowmo,$nowda,$nowyr) ;

if ($NEWTIME<$RIGHTNOW) :
 $BADFLAG = 1 ;
endif ;

if ($BADFLAG<>0)  :
 $txt2write = "SET VARIABLE HOTTIME \"BAD\"" ;
 execute_agi($txt2write) ;
else :
 $txt2write = "SET VARIABLE HOTTIME \"" . $NEWTIME . "\"" ;
 execute_agi($txt2write) ; 
endif ;

if ($debug) :
  if ($emaildebuglog) :
   fputs($stdlog, "\n\nCheckTime session log emailed to " . $email . ".\n" ); 
  endif ;
  fputs($stdlog, "\n\n" . date("F j, Y - H:i:s") . "  *** End of session ***\n\n\n" ); 
endif ;

if ($emaildebuglog) :
 system("mime-construct --to $email --subject " . chr(34) . "CheckTime Session Log" . chr(34) . " --attachment $log --type text/plain --file $log") ;
endif ;


// clean up file handlers etc.  
fclose($stdin);  
fclose($stdout);
fclose($stdlog);  
exit;   
  
?>
