<?php
 ob_implicit_flush(false); 
 error_reporting(0); 
 set_time_limit(300); 
 $ttsengine[0] = "/usr/bin/text2wave" ;
 $ttsengine[1] = "swift" ;

//   Telephone Web Reminders ver. 4.6.20, (c) Copyright 2006-2016, Ward Mundy & Associates LLC. All rights reserved.

//-------- DON'T CHANGE ANYTHING ABOVE THIS LINE ----------------

 $endofmonthflag=1;
 $extensionmaxdigits=6 ;
 $debug = 1; 
 $newlogeachdebug = 1;
 $emaildebuglog = 0;
 $email = "username@emailhost.xxx" ;
 $trunk = "Local" ;
 $callerid = "6781234567" ;
 $numcallattempts=6 ;
 $calldelaybetweenruns=300 ;
 $timetoring=40 ;
 $acctcode= "Web-Reminders" ;
 $ttspick = 0 ;

//-------- DON'T CHANGE ANYTHING BELOW THIS LINE ----------------

if($_POST['action']=="Review Existing Reminders") :
  header("Location: ./reminderlist.php"); 
 exit;
endif ;

$mytts = $ttsengine[$ttspick] ;

if ($_REQUEST['APPTPHONE'] < "0" and substr($_REQUEST['APPTPHONE'],0,1)<>"*" ) :
echo "    <html>\n";

echo "    <head>\n";
$APPTMO = date("m") ;
$APPTMONTH = date("F");
echo "                 <select name=\"APPTMO\">\n";

$APPTDA = date("d") ;
$APPTDAY = date("j") ;


$APPTYR = date("Y") ;
for ($i=1; $i<=10; $i++) {
$APPTYR = $APPTYR + 1 ;
echo "    <option value=$APPTYR> $APPTYR\n";
}
echo "          </tr>\n\n";

echo "          <tr bgcolor=\"#abcdef\">\n";

echo "    <p>\n";

echo "<script type=\"text/javascript\">\n";

 exit ;
endif ;

$log = "/var/log/asterisk/reminder-web.txt" ;
if ($debug and $newlogeachdebug) :
 if (file_exists($log)) :
  unlink($log) ;
 endif ;
endif ;

 $stdlog = fopen($log, 'a'); 
 $stdin = fopen('php://stdin', 'r'); 
 $stdout = fopen( 'php://stdout', 'w' ); 

if ($debug) :
  fputs($stdlog, "Telephone Web Reminders 4.6.20 (c) Copyright 2006-2014, Ward Mundy & Associates LLC. All Rights Reserved.\n\n" . date("F j, Y - H:i:s") . "  *** New session ***\n\n" ); 
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

$tts = $ttsengine[$ttspick] ;

$APPTDT=$_REQUEST['APPTYR'].$_REQUEST['APPTMO'].$_REQUEST['APPTDA'];
$APPTTIME=$_REQUEST['APPTHR'].$_REQUEST['APPTMIN'];
$APPTPHONE=$_REQUEST['APPTPHONE'];
$APPTRECUR=$_REQUEST['APPTRECUR'];
$APPTMSG=$_REQUEST['APPTMSG'];

$APPTDT    = urlencode(str_replace( array(chr(13),chr(10),"<",">","("), "", $APPTDT ));
$APPTTIME  = urlencode(str_replace( array(chr(13),chr(10),"<",">","("), "", $APPTTIME ));
$APPTPHONE = urlencode(str_replace( array(chr(13),chr(10),"<",">"," ","(",")","-","."), "", $APPTPHONE ));
$APPTRECUR = urlencode(str_replace( array(chr(13),chr(10),"<",">","("), "", $APPTRECUR ));
$APPTMSG    = str_replace( array(chr(13),chr(10),"<",">","+","("), "", $APPTMSG );




if ($debug) :
fputs($stdlog, "The following application-specific variables also were passed from Asterisk: \n" ); 
endif ;


if ($debug) :
fputs($stdlog, "APPTDT: " . $APPTDT . "\n" ); 
endif ;

if ($debug) :
fputs($stdlog, "APPTTIME: " . $APPTTIME . "\n" );
endif ;

if ($debug) :
fputs($stdlog, "APPTPHONE: " . $APPTPHONE . "\n" );
endif ;

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
 $recur_msg = "/var/spool/asterisk/recurring/" . $APPTTIME . "." . $NEXTDT . "." . $APPTPHONE . ".wav" ;
elseif ($APPTRECUR=="3") :
 $RECURRING="daily" ;
 $NEXTDT=date("Ymd", mktime (0,0,0,$APPTMO,$APPTDA+1,$APPTYR));
 $recur_script = "/var/spool/asterisk/recurring/" . $APPTTIME . "." . $NEXTDT . "." . $APPTPHONE . ".call." . $RECURRING ;
 $recur_msg = "/var/spool/asterisk/recurring/" . $APPTTIME . "." . $NEXTDT . "." . $APPTPHONE . ".wav" ;
elseif ($APPTRECUR=="4") :
 $RECURRING="weekly" ;
 $NEXTDT=date("Ymd", mktime (0,0,0,$APPTMO,$APPTDA+7,$APPTYR));
 $recur_script = "/var/spool/asterisk/recurring/" . $APPTTIME . "." . $NEXTDT . "." . $APPTPHONE . ".call." . $RECURRING ;
 $recur_msg = "/var/spool/asterisk/recurring/" . $APPTTIME . "." . $NEXTDT . "." . $APPTPHONE . ".wav" ;
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
 $recur_msg = "/var/spool/asterisk/recurring/" . $APPTTIME . "." . $NEXTDT . "." . $APPTPHONE . ".wav" ;
elseif ($APPTRECUR=="6") :
 $RECURRING="annually" ;
 $NEXTDT=date("Ymd", mktime (0,0,0,$APPTMO,$APPTDA,$APPTYR+1));
 $recur_script = "/var/spool/asterisk/recurring/" . $APPTTIME . "." . $NEXTDT . "." . $APPTPHONE . ".call." . $RECURRING ;
 $recur_msg = "/var/spool/asterisk/recurring/" . $APPTTIME . "." . $NEXTDT . "." . $APPTPHONE . ".wav" ;
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

$token = md5 (uniqid (""));

$tmptext = "/tmp/$token.txt" ;
$tmpwave = "/tmp/$token.wav" ;

$fd = fopen($tmptext, "w");
if (!$fd) {
 echo "<p>Unable to open temporary text file in /tmp for writing. \n";
 exit;
} 
$APPTMSG=str_replace("+"," ",$APPTMSG);
$retcode = fwrite($fd,$APPTMSG);
fclose($fd);

//$retcode2 = system ("$tts -f  $tmptext -o $tmpwave") ;
$newgsm   = "/usr/share/asterisk/sounds/custom/" . $APPTTIME . "." . $APPTDT . "." . $APPTPHONE . ".wav" ;
//$retcode3 = system("sox $tmpwave -r 8000 -c 1 $newgsm") ;

$retcode2 = system ("cat $tmptext | $mytts -F 8000 -o $newgsm");


unlink ("$tmptext") ;
unlink ("$tmpwave") ;

$fromfile = "/tmp/$token.tmp" ;
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
if ($trunk<>"Local") :
 $txt2write = "Callerid: " . $callerid . "\n" ;
 fwrite($fptmp,$txt2write) ;
else:
 $localid = chr(34)."Reminder".chr(34)." <123>" ;
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
//$txt2write = "Set: MSG=" . "rem2" . "\n" ;
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

$time2call =  mktime (substr($APPTTIME,0,2),substr($APPTTIME,2,2),5,substr($APPTDT,4,2),substr($APPTDT,6,2),substr($APPTDT,0,4)) ;
touch($fromfile, $time2call);

if ($RECURRING<>"once") :
 $frommsg = "/usr/share/asterisk/sounds/custom/" . $APPTTIME . "." . $APPTDT . "." . $APPTPHONE . ".wav" ;
 copy ($frommsg,$recur_msg) ;
endif;

$moveok = rename($fromfile,$tofile) ;
touch($tofile, $time2call);

//Now we're ready to copy the reminder script (message file is copied above to avoid a problem if call is placed immediately) to recurring storage if one of those options was chosen.
if ($RECURRING<>"once") :

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
 if ($trunk<>"Local") :
  $txt2write = "Callerid: " . $callerid . "\n" ;
  fwrite($fptmp,$txt2write) ;
 else:
  $localid = chr(34)."Reminder".chr(34)." <123>" ;
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
  fputs($stdlog, "Also remember to delete the actual message sound file: /usr/share/asterisk/sounds/custom/" . $APPTTIME . "." . $APPTDT . "." . $APPTPHONE  . ".wav.\n\n");
  if ($RECURRING<>"once") :  
   fputs($stdlog, "To delete the recurring reminder, delete the following files from /var/spool/asterisk/recurring:\n"); 
   fputs($stdlog, "$recur_script AND $recur_msg.\n");
  endif;
endif ;

if ($debug) :
  if ($emaildebuglog) :
   fputs($stdlog, "\n\nTelephone Web Reminders 4.6.20 session log emailed to " . $email . ".\n" ); 
  endif ;
  fputs($stdlog, "\n\n" . date("F j, Y - H:i:s") . "  *** End of session ***\n\n\n" ); 
endif ;

if ($emaildebuglog) :
 system("mime-construct --to $email --subject " . chr(34) . "Telephone Web Reminders 4.6.20 Session Log" . chr(34) . " --attachment $log --type text/plain --file $log") ;
endif ;

// clean up file handlers etc.  
fclose($stdin);  
fclose($stdout);
fclose($stdlog);  

?>

    <html>
<?php
 echo "         <td>$APPTPHONE</td>\n" ;
?>
        </tr>
<?php
echo "         <td>" . date("l, F j, Y - g:i a", mktime (substr($APPTTIME,0,2),substr($APPTTIME,2,2),0,substr($APPTDT,4,2),substr($APPTDT,6,2),substr($APPTDT,0,4))) . " ($RECURRING)</td>\n";
?>
        </tr>
<?php
?>
        </tr>

<?php
exit;   
?>