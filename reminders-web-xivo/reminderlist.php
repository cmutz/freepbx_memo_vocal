    <html>    <head>      <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">      <title>Telephone Web Reminders for Asterisk</title>    <link rel="stylesheet" href="style.css" type="text/css">    </head>    <body bgcolor="#84b0fd" text="#030303" link="#9abcde">    <a href=""><h2 align="center">Telephone Web Reminders for Asterisk</h2></a>      <table border="0" cellspacing="0" cellpadding="1" width="600" bgcolor="#000000" align="center">      <tr>        <td>          <table border="0" cellspacing="0" cellpadding="3" width="100%" bgcolor="#ffffff" align="center">            <tr bgcolor="#abcdef">              <td><b>Memos vocaux en attente</b></td>            </tr>            <tr>              <td> <?php$d = dir("/var/spool/asterisk/outgoing");//echo "Handle: ".$d->handle."<br>\n";echo "<i>Today's Events in ".$d->path.":</i><br>\n";while($entry=$d->read()) { if ($entry <>".." and $entry<>".") {  $ext=substr(stristr($entry,"."),1);  $ext=substr(stristr($ext,"."),1);  $ext=substr($ext,0,strpos($ext,"."));  echo "&nbsp;&nbsp;<b>" . $entry . "</b>&nbsp;&nbsp;" . date("F d, Y - h:i a",filemtime("/var/spool/asterisk/outgoing/$entry")) . " call to $ext" ."<br>\n"; }}$d->close();echo "<br>\n";$d = dir("/var/spool/asterisk/reminders");//echo "Handle: ".$d->handle."<br>\n";echo "<i>Future Reminders in ".$d->path.":</i><br>\n";while($entry=$d->read()) { if ($entry <>".." and $entry<>".") {  $ext=substr(stristr($entry,"."),1);  $ext=substr(stristr($ext,"."),1);  $ext=substr($ext,0,strpos($ext,"."));  echo "&nbsp;&nbsp;<b>" . $entry . "</b>&nbsp;&nbsp;" . date("F d, Y - h:i a",filemtime("/var/spool/asterisk/reminders/$entry")) . " call to $ext" . "<br>\n"; }}$d->close();echo "<br>\n";$d = dir("/var/lib/asterisk/sounds/custom");//echo "Handle: ".$d->handle."<br>\n";echo "<i>Reminder Messages in ".$d->path.":</i><br>\n";while($entry=$d->read()) { if ($entry <>".." and $entry<>"." and $entry<>"today.wav" and $entry<>"broadcast.gsm" and substr($entry,0,4)<>"ivr-" and substr($entry,0,5)<>"news-" and substr($entry,0,3)<>"nv-" and substr($entry,0,8)<>"noanswer" and substr($entry,0,8)<>"reminder" and substr($entry,0,3)<>"pls" and substr($entry,0,2)<>"aa" and substr($entry,0,4)<>"busy" and substr($entry,0,5)<>"callq") {  echo "&nbsp;&nbsp;" . $entry."<br>\n"; }}$d->close();echo "<br>\n";$d = dir("/var/spool/asterisk/recurring");//echo "Handle: ".$d->handle."<br>\n";echo "<i>Reminder Messages in ".$d->path.":</i><br>\n";while($entry=$d->read()) { if ($entry <>".." and $entry<>".") {  echo "&nbsp;&nbsp;" . $entry."<br>\n"; }}$d->close();?>      <p></td>            </tr>            <tr>              <td>&nbsp;</td>            </tr>            <tr>              <td bgcolor="#eeeeee"><a href="./index.php">Schedule Another Reminder</a></td>            </tr>          </table>        </td>      </tr>    </table>      <p>    <table border="0" cellspacing="0" cellpadding="1" width="400" bgcolor="#000000" align="center">      <tr>        <td>          <table border="0" cellspacing="0" cellpadding="3" width="100%" bgcolor="#eeeeee" align="center">            <tr>              <td align="center">                Created by <a href="http://nerdvittles.com/">Nerd Vittles</a>. Optimized for <a href="http://pbxinaflash.com/">PBX in a Flash</a>.              </td>            </tr>          </table>        </td>      </tr>    </table>      </body>    </html>  