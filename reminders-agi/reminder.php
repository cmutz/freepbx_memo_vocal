#!/usr/bin/php -q 
<?php
 ob_implicit_flush(false); 
 error_reporting(0); 
 set_time_limit(300); 

//   Telephone Reminders ver. 4.6.19, (c) Copyright 2006-2016, Ward Mundy & Associates, LLC. All rights reserved.

//-------- DON'T CHANGE ANYTHING ABOVE THIS LINE ----------------

 $endofmonthflag=1;
 $extensionmaxdigits=4 ;
 $debug = 1; 
 $newlogeachdebug = 1;
 $emaildebuglog = 0;
 $email = "yourname@yourdomain" ;
 $trunk = "Local" ;
 $callerid = "6781234567" ;
 $numcallattempts=6 ;
 $calldelaybetweenruns=300 ;
 $timetoring=40 ;
 $acctcode= "Reminder" ;

//-------- DON'T CHANGE ANYTHING BELOW THIS LINE ----------------

$log = "/var/log/asterisk/reminder.txt" ;
if ($debug and $newlogeachdebug) :
 if (file_exists($log)) :
  unlink($log) ;
 endif ;
endif ;

 $stdlog = fopen($log, 'a'); 
 $stdin = fopen('php://stdin', 'r'); 
 $stdout = fopen( 'php://stdout', 'w' ); 

if ($debug) :
  fputs($stdlog, "Telephone Reminders 4.6.19 (c) Copyright 2006-2016, Ward Mundy & Associates, LLC. All Rights Reserved.\n\n" . date("F j, Y - H:i:s") . "  *** New session ***\n\n" ); 
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
while ($env=read()) {  
 $s = split(": ",$env);  
 $agi[str_replace("agi_","",$s[0])] = trim($s[1]);
 if (($env == "") || ($env == "\n")) {  
   break;  
 }  
}  

// $tmp = $agi['dnid'] ;

if ($debug) :
fputs($stdlog, "The following application-specific variables also were passed from Asterisk: \n" ); 
endif ;


$ThisApp = $_SERVER["argv"][0]; 
$ThisApp = trim($ThisApp); 
//$parts = split("@",$vmbox); 

if ($debug) :
fputs($stdlog, "ThisApp: " . $ThisApp . "\n" ); 
endif ;

// This tells us the APPTDT for the reminder call to be placed
$APPTDT = $_SERVER["argv"][1]; 
$APPTDT = trim($APPTDT); 
$APPTDT = ltrim($APPTDT); 

if ($debug) :
fputs($stdlog, "APPTDT: " . $APPTDT . "\n" ); 
endif ;

// This tells us the APPTTIME for the reminder call to be placed 
$APPTTIME = $_SERVER["argv"][2];
$APPTTIME = trim($APPTTIME);
$APPTTIME = ltrim($APPTTIME);

if ($debug) :
fputs($stdlog, "APPTTIME: " . $APPTTIME . "\n" );
endif ;

// This tells us the APPTPHONE for the reminder call to be placed 
$APPTPHONE = $_SERVER["argv"][3];
$APPTPHONE = trim($APPTPHONE);
$APPTPHONE = ltrim($APPTPHONE);

if ($debug) :
fputs($stdlog, "APPTPHONE: " . $APPTPHONE . "\n" );
endif ;

// This tells us the recurring option selected for this reminder. We'll translate it into English below.
$APPTRECUR = $_SERVER["argv"][4];
$APPTRECUR = trim($APPTRECUR);
$APPTRECUR = ltrim($APPTRECUR);

// If scheduled on last day of the month, check the $endofmonthflag to determine if we always want reminders sent on last day of the month rather than actual day scheduled.
$APPTYR = substr($APPTDT,0,4);
$APPTMO = substr($APPTDT,4,2);
$APPTDA = substr($APPTDT,6,2);
if (date ("m", mktime (0,0,0,$APPTMO,$APPTDA,$APPTYR))<>date ("m", mktime (0,0,0,$APPTMO,$APPTDA+1,$APPTYR)) and $endofmonthflag) :
 $APPTDA = "31" ;
endif ;

if ($APPTRECUR=="2") :
 $RECURRING="weekday" ;
 if (date ("D", mktime (0,0,0,$APPTMO,$APPTDA,$APPTYR))=="Fri") :
  $NEXTDT=date("Ymd", mktime (0,0,0,$APPTMO,$APPTDA+3,$APPTYR));
 elseif (date ("D", mktime (0,0,0,$APPTMO,$APPTDA,$APPTYR))=="Sat") :
  $NEXTDT=date("Ymd", mktime (0,0,0,$APPTMO,$APPTDA+2,$APPTYR));
 else :
  $NEXTDT=date("Ymd", mktime (0,0,0,$APPTMO,$APPTDA+1,$APPTYR));
 endif ;
 $recur_script = "/var/spool/asterisk/recurring/" . $APPTTIME . "." . $NEXTDT . "." . $APPTPHONE . ".call." . $RECURRING ;
 $recur_msg = "/var/spool/asterisk/recurring/" . $APPTTIME . "." . $NEXTDT . "." . $APPTPHONE . ".ulaw" ;
elseif ($APPTRECUR=="3") :
 $RECURRING="daily" ;
 $NEXTDT=date("Ymd", mktime (0,0,0,$APPTMO,$APPTDA+1,$APPTYR));
 $recur_script = "/var/spool/asterisk/recurring/" . $APPTTIME . "." . $NEXTDT . "." . $APPTPHONE . ".call." . $RECURRING ;
 $recur_msg = "/var/spool/asterisk/recurring/" . $APPTTIME . "." . $NEXTDT . "." . $APPTPHONE . ".ulaw" ;
elseif ($APPTRECUR=="4") :
 $RECURRING="weekly" ;
 $NEXTDT=date("Ymd", mktime (0,0,0,$APPTMO,$APPTDA+7,$APPTYR));
 $recur_script = "/var/spool/asterisk/recurring/" . $APPTTIME . "." . $NEXTDT . "." . $APPTPHONE . ".call." . $RECURRING ;
 $recur_msg = "/var/spool/asterisk/recurring/" . $APPTTIME . "." . $NEXTDT . "." . $APPTPHONE . ".ulaw" ;
elseif ($APPTRECUR=="5") :
 $RECURRING="monthly" ;
 $NEXTDT=date("Ymd", mktime (0,0,0,$APPTMO+1,$APPTDA,$APPTYR));
 $NEXTYR = substr($NEXTDT,0,4);
 $NEXTMO = substr($NEXTDT,4,2);
 $NEXTDA = substr($NEXTDT,6,2);
 while (date ("m", mktime (0,0,0,$NEXTMO,$NEXTDA,$NEXTYR))<>date ("m", mktime (0,0,0,$APPTMO+1,1,$APPTYR))) :
  $NEXTDT=date("Ymd", mktime (0,0,0,$NEXTMO,$NEXTDA-1,$NEXTYR));
  $NEXTYR = substr($NEXTDT,0,4);
  $NEXTMO = substr($NEXTDT,4,2);
  $NEXTDA = substr($NEXTDT,6,2);
 endwhile ;
 $recur_script = "/var/spool/asterisk/recurring/" . $APPTTIME . "." . $NEXTDT . "." . $APPTPHONE . ".call." . $RECURRING . $APPTDA ;
 $recur_msg = "/var/spool/asterisk/recurring/" . $APPTTIME . "." . $NEXTDT . "." . $APPTPHONE . ".ulaw" ;
elseif ($APPTRECUR=="6") :
 $RECURRING="annually" ;
 $NEXTDT=date("Ymd", mktime (0,0,0,$APPTMO,$APPTDA,$APPTYR+1));
 $recur_script = "/var/spool/asterisk/recurring/" . $APPTTIME . "." . $NEXTDT . "." . $APPTPHONE . ".call." . $RECURRING ;
 $recur_msg = "/var/spool/asterisk/recurring/" . $APPTTIME . "." . $NEXTDT . "." . $APPTPHONE . ".ulaw" ;
else :
 $RECURRING="once" ;
 $recur_script="once" ;
endif ;


if ($debug) :
 fputs($stdlog, "APPTRECUR: " . $APPTRECUR . "\n" );
 fputs($stdlog, "RECURRING: " . $recur_script . "\n" );
endif ;


$trunk = strtolower($trunk) ;
$numcallattempts = $numcallattempts-1 ;

$fromfile = "/tmp/reminder.tmp" ;
if (date("Ymd") <> $APPTDT) :
 $tofile = "/var/spool/asterisk/reminders/" . $APPTTIME . "." . $APPTDT . "." . $APPTPHONE . ".call" ;
else :
 $tofile = "/var/spool/asterisk/outgoing/" . $APPTTIME . "." . $APPTDT . "." . $APPTPHONE . ".call" ;
endif ;
$fptmp = fopen($fromfile, "w");
if ($trunk<>"Local") :
 if (strlen($APPTPHONE)<=$extensionmaxdigits) :
  $txt2write = "Channel: " . $trunk . "/" . $APPTPHONE . "@default\n" ;
 else :
  $txt2write = "Channel: " . $trunk . "/" . $APPTPHONE . "@default\n" ;
 endif;
else :
 if (strlen($APPTPHONE)<=$extensionmaxdigits) :
  $txt2write = "Channel: " . $trunk . "/" . $APPTPHONE . "@default\n" ;
 else :
  $txt2write = "Channel: " . $trunk . "/" . $APPTPHONE . "@default\n" ;
 endif ;
endif;
fwrite($fptmp,$txt2write) ;
if ($trunk<>"local") :
 $txt2write = "Callerid: " . $callerid . "\n" ;
 fwrite($fptmp,$txt2write) ;
else:
 $localid = chr(34)."NV-Reminder".chr(34)." <123>" ;
 $txt2write = "Callerid: " . $localid . "\n" ;
 fwrite($fptmp,$txt2write) ;
endif ;
$txt2write = "MaxRetries: " . $numcallattempts . "\n" ;
fwrite($fptmp,$txt2write) ;
$txt2write = "RetryTime: " . $calldelaybetweenruns . "\n" ;
fwrite($fptmp,$txt2write) ;
$txt2write = "WaitTime: " . $timetoring . "\n" ;
fwrite($fptmp,$txt2write) ;
$txt2write = "Account: " . $acctcode . "\n" ;
fwrite($fptmp,$txt2write) ;
$txt2write = "context: remindem\n" ;
fwrite($fptmp,$txt2write) ;
$txt2write = "extension: s\n" ;
fwrite($fptmp,$txt2write) ;
$txt2write = "priority: 1\n" ;
fwrite($fptmp,$txt2write) ;
$txt2write = "Set: MSG=" . $APPTTIME . "." . $APPTDT . "." . $APPTPHONE . "\n" ;
fwrite($fptmp,$txt2write) ;
$txt2write = "Set: APPTDT=" . $APPTDT . "\n" ;
fwrite($fptmp,$txt2write) ;
$txt2write = "Set: APPTTIME=" . $APPTTIME . "\n" ;
fwrite($fptmp,$txt2write) ;
$txt2write = "Set: APPTPHONE=" . $APPTPHONE . "\n" ;
fwrite($fptmp,$txt2write) ;
$txt2write = "Set: CALLATTEMPTS=" . $numcallattempts . "\n" ;
fwrite($fptmp,$txt2write) ;
$txt2write = "Set: CALLDELAY=" . $calldelaybetweenruns . "\n" ;
fwrite($fptmp,$txt2write) ;
fclose($fptmp) ;

// set time stamp here to make .call file work at the correct time with Asterisk. Then move it.

//$time2call =  mktime (substr($APPTTIME,0,2),substr($APPTTIME,2,2),5,substr($APPTDT,4,2),substr($APPTDT,6,2),substr($APPTDT,0,4)) ;
//touch($fromfile, $time2call);

$newtime=$APPTDT.$APPTTIME;
System("touch -t $newtime $fromfile");


if ($RECURRING<>"once") :
 $frommsg = "/usr/share/asterisk/sounds/custom/" . $APPTTIME . "." . $APPTDT . "." . $APPTPHONE . ".ulaw" ;
 copy ($frommsg,$recur_msg) ;
endif;

System("mv $fromfile $tofile");

//$moveok = rename($fromfile,$tofile) ;
//touch($tofile, $time2call);


//Now we're ready to copy the reminder script (message file is copied above to avoid a problem if call is placed immediately) to recurring storage if one of those options was chosen.
if ($RECURRING<>"once") :

 $fptmp = fopen($fromfile, "w");
 if ($trunk<>"local") :
  if (strlen($APPTPHONE)<=$extensionmaxdigits) :
   $txt2write = "Channel: " . $trunk . "/" . $APPTPHONE . "@default\n" ;
  else :
   $txt2write = "Channel: " . $trunk . "/" . $APPTPHONE . "@default\n" ;
  endif;
 else :
  if (strlen($APPTPHONE)<=$extensionmaxdigits) :
   $txt2write = "Channel: " . $trunk . "/" . $APPTPHONE . "@default\n" ;
  else :
   $txt2write = "Channel: " . $trunk . "/" . $APPTPHONE . "@default\n" ;
  endif ;
 endif;
 fwrite($fptmp,$txt2write) ;
 if ($trunk<>"Local") :
  $txt2write = "Callerid: " . $callerid . "\n" ;
  fwrite($fptmp,$txt2write) ;
 else:
  $localid = chr(34)."NV-Reminder".chr(34)." <123>" ;
  $txt2write = "Callerid: " . $localid . "\n" ;
  fwrite($fptmp,$txt2write) ;
 endif ;
 $txt2write = "MaxRetries: " . $numcallattempts . "\n" ;
 fwrite($fptmp,$txt2write) ;
 $txt2write = "RetryTime: " . $calldelaybetweenruns . "\n" ;
 fwrite($fptmp,$txt2write) ;
 $txt2write = "WaitTime: " . $timetoring . "\n" ;
 fwrite($fptmp,$txt2write) ;
 $txt2write = "Account: " . $acctcode . "\n" ;
 fwrite($fptmp,$txt2write) ;
 $txt2write = "context: remindem\n" ;
 fwrite($fptmp,$txt2write) ;
 $txt2write = "extension: s\n" ;
 fwrite($fptmp,$txt2write) ;
 $txt2write = "priority: 1\n" ;
 fwrite($fptmp,$txt2write) ;
 $txt2write = "Set: MSG=" . $APPTTIME . "." . $NEXTDT . "." . $APPTPHONE . "\n" ;
 fwrite($fptmp,$txt2write) ;
 $txt2write = "Set: APPTDT=" . $NEXTDT . "\n" ;
 fwrite($fptmp,$txt2write) ;
 $txt2write = "Set: APPTTIME=" . $APPTTIME . "\n" ;
 fwrite($fptmp,$txt2write) ;
 $txt2write = "Set: APPTPHONE=" . $APPTPHONE . "\n" ;
 fwrite($fptmp,$txt2write) ;
 $txt2write = "Set: CALLATTEMPTS=" . $numcallattempts . "\n" ;
 fwrite($fptmp,$txt2write) ;
 $txt2write = "Set: CALLDELAY=" . $calldelaybetweenruns . "\n" ;
 fwrite($fptmp,$txt2write) ;
 fclose($fptmp) ;

 $moveok = rename($fromfile,$recur_script) ;
endif ;

if ($debug) :
  fputs($stdlog, "Reminder saved to " . $tofile . "  Date/Time: " . $APPTDT . "/" . $APPTTIME  . "  Phone#: " .  $APPTPHONE . "\n" ); 
  fputs($stdlog, "\n\nNOTE: To delete this reminder prior to the actual date of the reminder, delete the above file.\n");
  fputs($stdlog, "To delete this reminder on the actual date of the reminder, delete the above file from /var/spool/asterisk/outgoing.\n");
  fputs($stdlog, "Also remember to delete the actual message sound file: /usr/share/asterisk/sounds/custom/" . $APPTTIME . "." . $APPTDT . "." . $APPTPHONE  . ".ulaw.\n\n");
  if ($RECURRING<>"once") :  
   fputs($stdlog, "To delete the recurring reminder, delete the following files from /var/spool/asterisk/recurring:\n"); 
   fputs($stdlog, "$recur_script AND $recur_msg.\n");
  endif;
endif ;


if ($debug) :
  if ($emaildebuglog) :
   fputs($stdlog, "\n\nTelephone Reminders 4.6.19 session log emailed to " . $email . ".\n" ); 
  endif ;
  fputs($stdlog, "\n\n" . date("F j, Y - H:i:s") . "  *** End of session ***\n\n\n" ); 
endif ;

if ($emaildebuglog) :
 system("mime-construct --to $email --subject " . chr(34) . "Telephone Reminders 4.6.19 Session Log" . chr(34) . " --attachment $log --type text/plain --file $log") ;
endif ;


// clean up file handlers etc.  
fclose($stdin);  
fclose($stdout);
fclose($stdlog);  
exit;   
  
?>
