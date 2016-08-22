#!/usr/bin/php -q 
<?php
 ob_implicit_flush(false); 
 error_reporting(0); 
 set_time_limit(300); 

//   CheckDate, (c) Copyright Ward Mundy, 2006. All rights reserved.

//-------- DON'T CHANGE ANYTHING ABOVE THIS LINE ----------------

 $debug = 1; 
 $newlogeachdebug = 1;
 $emaildebuglog = 0;
 $email = "yourname@yourdomain" ;

//-------- DON'T CHANGE ANYTHING BELOW THIS LINE ----------------

$log = "/var/log/asterisk/checkdate.txt" ;
if ($debug and $newlogeachdebug) :
 if (file_exists($log)) :
  unlink($log) ;
 endif ;
endif ;

 $stdlog = fopen($log, 'a'); 
 $stdin = fopen('php://stdin', 'r'); 
 $stdout = fopen( 'php://stdout', 'w' ); 

if ($debug) :
  fputs($stdlog, "CheckDate (c) Copyright Ward Mundy, 2006. All Rights Reserved.\n\n" . date("F j, Y - H:i:s") . "  *** New session ***\n\n" ); 
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

if (strlen($APPTDT)<8) :
 $APPTDT = date("Ymd") ;
endif ;

if ($debug) :
fputs($stdlog, "APPTDT: " . $APPTDT . "\n\n\n" ); 
endif ;

$BADFLAG=0 ;

if (strlen($APPTDT)<>8) :
 $BADFLAG=1 ;
endif ;
if (substr($APPTDT,0,4)<date("Y")) :
 $BADFLAG=1 ;
endif ;
if (substr($APPTDT,0,4)>"2020") :
 $BADFLAG=1 ; 
endif ;
if (substr($APPTDT,4,2)<"01") :
 $BADFLAG=1 ; 
endif ;
if (substr($APPTDT,4,2)>"12") :
 $BADFLAG=1 ; 
endif ;
if (substr($APPTDT,0,6)<date("Ym")) :
 $BADFLAG=1 ; 
endif ;
if (substr($APPTDT,6,2)<"01") :
 $BADFLAG=1 ; 
endif ;
if (substr($APPTDT,6,2)>"31") :
 $BADFLAG=1 ; 
endif ;
if (substr($APPTDT,0,8)<date("Ymd")) :
 $BADFLAG=1 ; 
endif ;


if ($BADFLAG<>0)  :
 $txt2write = "SET VARIABLE HOTDATE \"BAD\"" ;
 execute_agi($txt2write) ;
else :
 if ($APPTDT<>date("Ymd")) :
  $thismo = substr($APPTDT,4,2) + 0 ;
  $thisda = intval(substr($APPTDT,6,2)) + 0 ;
  $thisyr = intval(substr($APPTDT,0,4)) + 0 ;
  $NEWDATE = mktime(0,0,1,$thismo,$thisda,$thisyr) ;
  $txt2write = "SET VARIABLE HOTDATE \"" . $NEWDATE . "\"" ;
  execute_agi($txt2write) ; 
 else :
  $txt2write = "SET VARIABLE HOTDATE \"TODAY\"" ;
  execute_agi($txt2write) ;
  $txt2write = "SET VARIABLE APPTDT \"" . $APPTDT . "\"" ;
  execute_agi($txt2write) ;
 endif;
endif ;

if ($debug) :
  if ($emaildebuglog) :
   fputs($stdlog, "\n\nCheckDate session log emailed to " . $email . ".\n" ); 
  endif ;
  fputs($stdlog, "\n\n" . date("F j, Y - H:i:s") . "  *** End of session ***\n\n\n" ); 
endif ;

if ($emaildebuglog) :
 system("mime-construct --to $email --subject " . chr(34) . "CheckDate Session Log" . chr(34) . " --attachment $log --type text/plain --file $log") ;
endif ;


// clean up file handlers etc.  
fclose($stdin);  
fclose($stdout);
fclose($stdlog);  
exit;   
  
?>
