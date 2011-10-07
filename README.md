#Overview of the application
The XML files are downloaded from City24. compile\_object\_data.php takes an
XML file, processes it, and spits out a JSON-encoded file which contains
information about all the objects (of that language), coordinates and other
meta-info. The front end (map) reads the JSON-encoded file and creates a map
of all the objects (split into two categories [for sale and for rent]). The
front end is language-dependent, uses the $Lang variable to deduce the language
to use.

#Pulling XML files from City24
The XML files from City24 are pulled using these 4 cURL commands:
* curl --user user:pass --output /path/to/est.xml https://maakler.city24.ee/broker/city24broker/xml?lang=EST
* curl --user user:pass --output /path/to/eng.xml https://maakler.city24.ee/broker/city24broker/xml?lang=ENG
* curl --user user:pass --output /path/to/fin.xml https://maakler.city24.ee/broker/city24broker/xml?lang=FIN
* curl --user user:pass --output /path/to/rus.xml https://maakler.city24.ee/broker/city24broker/xml?lang=RUS


#Generating JSON files
To generate the corresponding JSON files for the front-end, run:
./compile\_object\_data.php </path/to/<est|eng|fin|rus>.xml> <est|eng|fin|rus> [0|1]
E.g:
* ./compile\_object\_data.php /home/karl/lvm/est.xml est
* ./compile\_object\_data.php /home/karl/lvm/eng.xml eng
* ./compile\_object\_data.php /home/karl/lvm/fin.xml fin
* ./compile\_object\_data.php /home/karl/lvm/rus.xml rus

##Arguments to compile\_object\_data.php
The first argument is the XML file downloaded from City24
The second argument is the corresponding language -- if the downloaded file was
est.xml then the second argument should be est.
The third argument is optional and turns debugging messages on (1) or off (0)

#The map
The map itself is in index.php. It relies on some Javascript files (namely,
map.js, markerclusterer\_packed.js and jQuery libraries) and some CSS to put the
data on the map and create the look-and-feel.

######Note
Lots of hacks in this project, because I had no access apart from FTP (no SSH,
no databases, nothing). There is some reverse-engineered stuff, some weird
API hacks and so on.
