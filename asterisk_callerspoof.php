#!/asterisk/php/bin/php -q
<?

/* 
Written by Harper Reed
email: harper@nata2.org
web: nata2.info

this was my inspiration:http://www.rootsecure.net/?p=reports/callerid_spoofing

Notes:
This is a simple caller id spoofing script for asterisk.
It has been tested a bit - and was written more as a proof of concept then an actual production script (although it does work). 

Steps:
1) make sure this script is in your asterisk-install:/var/lib/asterisk/agi-bin/ dir, copy the gsm sounds into your sounds dir
2) add this or something similar to your extensions.conf:
	exten => 1337,1,Answer
	exten => 1337,2,AGI(asterisk_callerspoof.php)
	exten => 1337,3,Hangup

3) reload asterisk or just the extensions
3.5) agi debug is helpful if there are problems
5) Try it out. Dial 1337 (or whatever extension you set up) and you should be prompted to enter the number you wish to spoof. 
	then the number you wish to call. the call will be made immediately

As you can see - this is not a complicated script. in fact - it is really really simple. almost too simple. 



*/


//make sure php doesn't fail if there is a pause in execution
set_time_limit(30);
// require the phpagi class
require('phpagi.php');
error_reporting(E_ALL);

//instatiate a new AGI
$agi = new AGI();

//answer the phone. 
$agi->answer();
//wait a minute to give everything a chance to settle
sleep(2);


//I had it set up to check the callerid against a set of valid peeps - but i decided i was tired of that. and it made it too complex.
//get caller id. 
$cid = $agi->parse_callerid();
$cid= $cid[username];

//streamfile that says "Enter number to spoof"
$agi->stream_file('enter_spoof');
//beep then get the resulting 10 digits - set it to spoofnumber
$result = $agi->get_data('beep', 3000, 10);
$spoofnumber= $result['result'];
$agi->verbose("Spoof Number:".$spoofnumber);
//streamfile that says "enter number to call"
$agi->stream_file('call_spoof');
//beep then get the resulting 10 digits - set it to callnumber
$result = $agi->get_data('beep', 3000, 10);
$callnumber= $result['result'];
$agi->verbose("Number to call:".$callnumber);
// set caller id to the spoofnumber
$agi->set_callerid($spoofnumber);

//call the number using whatever you got
//notice i have placed the 1 before the callnumber. this is so that i can keep everything ten digits. and cuz i am lazy
$agi->exec("Dial IAX2/yourpassword@provider/1".$callnumber);


// That is it. simple.. scary and easy
?>
