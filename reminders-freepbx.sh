#!/bin/bash

#    Incredible PBX Copyright (C) 2005-2016, Ward Mundy & Associates LLC.
#    This program installs Telephone Reminders on the XiVO platform
#    All programs copyrighted and licensed by their respective companies.
#
#    Portions Copyright (C) 1999-2016,  Digium, Inc.
#    Portions Copyright (C) 2005-2016,  Sangoma Technologies, Inc.
#    Portions Copyright (C) 2005-2016,  Ward Mundy & Associates LLC
#    Portions Copyright (C) 2006-2016,  Avencall

#    This program is free software: you can redistribute it and/or modify
#    it under the terms of the GNU General Public License as published by
#    the Free Software Foundation, either version 3 of the License, or
#    (at your option) any later version.

#    This program is distributed in the hope that it will be useful,
#    but WITHOUT ANY WARRANTY; without even the implied warranty of
#    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#    GNU General Public License for more details: /root/COPYING.

#    You should have received a copy of the GNU General Public License
#    along with this program.  If not, see <http://www.gnu.org/licenses/>.
#    GPL3 license file can be found at /root/COPYING after installation.

clear

version=0.002

echo ".-.                          .-. _ .-.   .-.            .---. .---. .-..-."
echo ": :                          : ::_;: :   : :  v$version    : .; :: .; :: \`' :"
echo ": :,-.,-. .--. .--.  .--.  .-' :.-.: \`-. : :   .--.     :  _.':   .' \`  ' "
#echo $version
echo ": :: ,. :'  ..': ..'' '_.'' .; :: :' .; :: :_ ' '_.'    : :   : .; :.'  \`."
echo ":_;:_;:_;\`.__.':_;  \`.__.'\`.__.':_;\`.__.'\`.__;\`.__.'    :_;   :___.':_;:_;"
echo "Copyright (c) 2005-2016, Ward Mundy & Associates LLC. All rights reserved."
echo " "
echo "THIS SCRIPT INSTALLS TELEPHONE REMINDERS 4.0 ONTO THE FREEPBX PLATFORM ONLY!"
echo " "
echo "BY USING INCREDIBLE PBX COMPONENTS, YOU AGREE TO ASSUME ALL RESPONSIBILITY"
echo "FOR USE OF THE PROGRAMS INCLUDED IN THIS INSTALLATION. NO WARRANTIES"
echo "EXPRESS OR IMPLIED INCLUDING MERCHANTABILITY AND FITNESS FOR PARTICULAR"
echo "USE ARE PROVIDED. YOU ASSUME ALL RISKS KNOWN AND UNKNOWN AND AGREE TO"
echo "HOLD WARD MUNDY, WARD MUNDY & ASSOCIATES LLC, NERD VITTLES, AND THE PBX"
echo "IN A FLASH DEVELOPMENT TEAM HARMLESS FROM ANY AND ALL LOSS OR DAMAGE"
echo "WHICH RESULTS FROM YOUR USE OF THIS SOFTWARE. AS CONFIGURED, THIS"
echo "SOFTWARE CANNOT BE USED TO MAKE 911 CALLS, AND YOU AGREE TO PROVIDE"
echo "AN ALTERNATE PHONE CAPABLE OF MAKING EMERGENCY CALLS. IF ANY OF THESE TERMS"
echo "AND CONDITIONS ARE RULED TO BE UNENFORCEABLE, YOU AGREE TO ACCEPT ONE"
echo "DOLLAR IN U.S. CURRENCY AS COMPENSATORY AND PUNITIVE LIQUIDATED DAMAGES"
echo "FOR ANY AND ALL CLAIMS YOU AND ANY USERS OF THIS SOFTWARE MIGHT HAVE."
echo " "
echo "If you do not agree with these terms and conditions of use, press Ctrl-C now."
read -p "Otherwise, press Enter to proceed at your own risk..."
clear
echo "Telephone Reminders 4.0 lets you schedule reminders by dialing 123."
echo "These reminders can be sent to ANY telephone worldwide so it is important"
echo "to password-protect access to the telephone reminders service. Choose a"
echo "6 to 8-digit password than cannot be easily guessed by an attacker!"
echo " "
echo -n "Choose Telephone Reminders Numeric Password: "
read reminderspw
echo " "
echo "Password chosen: $reminderspw"
echo " "
read -p "To proceed with installation, press ENTER. To cancel, press Ctrl-C now..."
echo " "
echo "There is a bug in certain XiVO releases that causes your server"
echo "not to recognize Daylight Savings Time correctly. To begin,"
read -p "SET YOUR CORRECT TIME ZONE now. Then the installation will proceed."
dpkg-reconfigure tzdata

echo " "
echo "Time Zone setup completed. Verify that your server time is accurate above."
echo "Failure to do so will mean Telephone Reminders do not arrive as scheduled."
read -p "To proceed with installation, press ENTER. To cancel, press Ctrl-C now..."

clear
echo "Installing Telephone Reminders. One moment please..."
echo " "

cd /var/spool/asterisk
chown asterisk:www-data outgoing
chmod 770 outgoing
chmod g+s outgoing
cp -pr outgoing reminders
cp -pr outgoing recurring

chown asterisk:www-data /var/lib/asterisk/sounds

echo "Installing Telephone Reminders voice prompts..."
mkdir /var/lib/asterisk/sounds/custom/
cp -pr /root/freepbx_memo_vocal/reminders-sounds/* /var/lib/asterisk/sounds/custom/
echo " "

echo "Installing Telephone Reminders AGI scripts..."
cp -pr /root/freepbx_memo_vocal/reminders-agi/* /var/lib/asterisk/agi-bin/
echo " "

echo "Installing Telephone Reminders dialplan code..."
echo "#include extensions_extra.d/*" >> /etc/asterisk/extensions_custom.conf
mkdir /etc/asterisk/extensions_extra.d
cp -pr /root/freepbx_memo_vocal/reminders-conf-fpx/* /etc/asterisk/extensions_extra.d/
echo " "

echo "Installing Telephone Reminders 123 extension..."
touch /etc/asterisk/extensions_extra.d/freepbx-extrafeatures.conf
sed -i '\:// BEGIN Reminders:,\:// END Reminders:d' /etc/asterisk/extensions_extra.d/freepbx-extrafeatures.conf
echo ";# // BEGIN Reminders
exten => 123,1,Answer
exten => 123,2,Wait(1)
exten => 123,3,Authenticate($reminderspw)
exten => 123,4,Goto(reminder,s,1)
;# // END Reminders
" >> /etc/asterisk/extensions_extra.d/extensions_custom.conf

echo "Installing Telephone Web Reminders application..."
mkdir /var/www/html/reminders
cp -pr /root/freepbx_memo_vocal/reminders-web-fpx/* /var/www/html/reminders/
chown asterisk:www-data /var/www/html/reminders
chmod 775 /var/www/html/reminders/
echo " "

#echo "Reconfiguring NGINX to support Incredible PBX web apps..."
#cd /
#tar zxvf /root/reminders-nginx.tar.gz
#/etc/init.d/nginx restart
#echo " "

echo "Checking for pico TTS to support Web Reminders..."
#test=`ps aux| grep pico2wave`
echo "Installing Pico2wave TTS engine..."
cd /root/
add-apt-repository 'deb http://ftp.fr.debian.org/debian/ jessie contrib non-free main'
sh -c "echo deb-src http://ftp.fr.debian.org/debian/ jessie contrib non-free main \ >> /etc/apt/sources.list"
apt-get update
# installation dependance
apt-get install -y automake1.11  fakeroot debhelper automake autoconf libtool help2man libpopt-dev hardening-wrapper
mkdir pico_build
cd pico_build
apt-get source libttspico-utils
versionsSox=$(ls |head -1)
cd $versionsSox
dpkg-buildpackage -rfakeroot -us -uc
cd ..
dpkg -i libttspico-data*.deb
dpkg -i libttspico0*.deb
dpkg -i libttspico-utils*.deb


#echo "Checking for Festival TTS to support Web Reminders..."
#test=`ps aux | grep "festival --server" | head -n -1`
#if [ -z "$test" ]; then
# echo "Installing Festival TTS engine..."
# cd /
# wget http://incrediblepbx.com/festival-xivo.tar.gz
# tar zxvf festival-xivo.tar.gz
# rm -f festival-xivo.tar.gz
# cd /root
# ./festival-xivo.sh
#fi
#echo " "

echo "Setting up cron jobs for recurring reminders..."
sed -i '/run_recurring/d' /etc/crontab
sed -i '/run_reminders/d' /etc/crontab
echo "0 0 * * * root /var/lib/asterisk/agi-bin/run_recurring >/dev/null 2>&1" >> /etc/crontab
echo "3 0 * * * root /var/lib/asterisk/agi-bin/run_reminders >/dev/null 2>&1" >> /etc/crontab
echo " "

echo "Reloading Asterisk dialplan to complete install..."
asterisk -rx "dialplan reload"
rm -f /root/freepbx_memo_vocal/reminders-xivo.tar.gz
echo " "
echo "Done. Dial 123 to schedule a reminder from any XiVO phone."
echo "Schedulle web reminders at http://XiVO-ipaddress/reminders"
