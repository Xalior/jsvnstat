   _                     _        _   
  (_)                   | |      | |  
   _ _____   ___ __  ___| |_ __ _| |_ 
  | / __\ \ / / '_ \/ __| __/ _` | __|
  | \__ \\ V /| | | \__ \ || (_| | |_ 
  | |___/ \_/ |_| |_|___/\__\__,_|\__|
 _/ |                                 
|__/                                  
 interactive network traffic analysis


jsvnstat is a web frontend for vnstat. For more information
about vnstat, visit http://humdi.net/vnstat/

Requirements
°°°°°°°°°°°°
- vnstat installed and working
- vnstat database created (vnstat -u -i eth0)
- running webserver with PHP support
- vnstat executable from the web (check PHP security settings)

Installation
°°°°°°°°°°°°
- Copy all provided files to your webserver
- Apply appropriate permissions (chown -R www-data some/where/jsvnstat)
- Adjust settings.php if necessary

Info
°°°°
- Visit http://www.rakudave.ch/?q=jsvnstat and drop a comment
- Send a mail to public@rakudave.ch with jsvnstat in the subject line

License
°°°°°°°
jsvnstat is published under the GPLv3, libraries under their respective licenses.

Used Libraries
°°°°°°°°°°°°°°
flot - http://code.google.com/p/flot/
crir - http://www.chriserwin.com/scripts/crir/
