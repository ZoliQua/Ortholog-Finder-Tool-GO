<?php

/*
* ** DO NOT REMOVE **
* *******************
* Project Name: GeneOntology Extension Tool
* Project Website: http://go.orthologfindertool.com
* Project Version: Public Version 1.0
*
* Project Source Code: https://github.com/ZoliQua/GO-Extension-Tool
*
* Author: Zoltan Dul, 2018
* Email: zoltan.dul@kcl.ac.uk and zoltan.dul@gmail.com
* Twitter: @ZoliQa
*
* DESCRIPTION
* ****************
* A bioinformatics tool that aims to extend Gene Ontology and give novel suggestions for 
* funcional annotation, based on their orthological relation.
*
* PHP FILE
* *******************
* Page - LOGGER file
* *******************
*
* *******************
*
* All code can be used under GNU General Public License version 2.
* If you have any question or find some bug please email me.
*
*/

include_once("ip2country.php");

class MyLogPHP extends ip2country{

	// Name of the file where the message logs will be appended.
	private $LOGFILENAME;

	// Define the separator for the fields. Default is comma (,).
	private $SEPARATOR;

	// headers
	private $HEADERS;

	// Default tag.
	const DEFAULT_TAG = '--';

	// CONSTRUCTOR
	function __construct($separator = ';', $conf) {
		$mylog = 'log/sitelog_'.date("Ym").'.csv';
		$this->LOGFILENAME = $mylog;
		$this->SEPARATOR = $separator;
		$this->HEADERS =
			'EVENT' . $this->SEPARATOR .
			'DATETIME' . $this->SEPARATOR .
			'SERVER_HOST' . $this->SEPARATOR .
			'SERVER_ADDR' . $this->SEPARATOR .
			'METHOD' . $this->SEPARATOR .
			'FILE' . $this->SEPARATOR .
			'ACTION_NAME' . $this->SEPARATOR .
			'ACTION_ADD' . $this->SEPARATOR .
			'COUNTRY' . $this->SEPARATOR .
			'IP_ADDR' . $this->SEPARATOR .
			'USER_AGENT' . $this->SEPARATOR .
			'BROWSER_NAME' . $this->SEPARATOR .
			'BROWSER_VERSION' . $this->SEPARATOR .
			'PLATFORM' . $this->SEPARATOR .
			'LANGUAGE' . $this->SEPARATOR .
			'DOCUMENT_ROOT';
		$this->mysql_host = $conf['host'];
		$this->db_name = $conf['data'];
		$this->db_user = $conf['user'];
		$this->db_pass = $conf['pass'];
	}

	function getBrowser() {

	    $u_agent = $_SERVER['HTTP_USER_AGENT'];
		$httpAcceptLanguage = (isset($_SERVER["HTTP_ACCEPT_LANGUAGE"]) ? $_SERVER["HTTP_ACCEPT_LANGUAGE"] : "Unknown" );
	    $ub = "Unknown";
	    $bname = 'Unknown';
	    $platform = 'Unknown';
	    $version= "Unknown";

	    // Platform
	    if (preg_match('/linux/i', $u_agent)) $platform = 'linux';
	    elseif (preg_match('/macintosh|mac os x/i', $u_agent)) $platform = 'mac';
	    elseif (preg_match('/windows|win32/i', $u_agent)) $platform = 'windows';
	    elseif (preg_match('/google|googlebot/i', $u_agent)) $platform = 'bot';
	    elseif (preg_match('/facebook/i', $u_agent)) $platform = 'bot';
	    elseif (preg_match('/bot|crawl/i', $u_agent)) $platform = 'bot';

		$isBot = (( $platform == "bot" ) ? true : false);

	    // Browser
	    if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent))
	    {
	        $bname = 'Internet Explorer';
	        $ub = "MSIE";
	    }
	    elseif(preg_match('/Firefox/i',$u_agent))
	    {
	        $bname = 'Mozilla Firefox';
	        $ub = "Firefox";
	    }
	    elseif(preg_match('/Chrome/i',$u_agent))
	    {
	        $bname = 'Google Chrome';
	        $ub = "Chrome";
	    }
	    elseif(preg_match('/Safari/i',$u_agent))
	    {
	        $bname = 'Apple Safari';
	        $ub = "Safari";
	    }
	    elseif(preg_match('/Opera/i',$u_agent))
	    {
	        $bname = 'Opera';
	        $ub = "Opera";
	    }
	    elseif(preg_match('/Netscape/i',$u_agent))
	    {
	        $bname = 'Netscape';
	        $ub = "Netscape";
	    }
	    elseif(preg_match('/Google/i',$u_agent))
	    {
	        $bname = 'Google';
	        $ub = "Google";
	    }

	    // finally get the correct version number
	    $known = array('Version', $ub, 'other');
	    $pattern = '#(?<browser>' . join('|', $known) .
	    ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
	    if (!preg_match_all($pattern, $u_agent, $matches)) {
	        // we have no matching number just continue
	    }

	    // see how many we have
	    $i = count($matches['browser']);
	    if ($i != 1) {
	        //we will have two since we are not using 'other' argument yet
	        //see if version is before or after the name
	        if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
	            $version = $matches['version'][0];
	        }
	        else {
	            $version = $matches['version'][1];
	        }
	    }
	    else {
	        $version = $matches['version'][0];
	    }

	    // check if we have a number
	    if ($version == null || $version == "Unknown" || empty($version)) {
			$version = "Unknown";
		}

	    return array(
	        'userAgent'	=> $u_agent,
	        'name'		=> $bname,
	        'version'	=> $version,
	        'platform'  => $platform,
			'language'	=> $httpAcceptLanguage,
			'isBot'		=> $isBot,
	        'pattern'	=> $pattern
	    );
	}

	function getServer() {

		// Get Server information host-name, server-address, request method and self php file.

		$ServerHost = (isset($_SERVER["HTTP_HOST"]) ? $_SERVER["HTTP_HOST"] : "Unknown" );
		$ServerAddr = (isset($_SERVER["SERVER_ADDR"]) ? $_SERVER["SERVER_ADDR"] : "Unknown" );
		$ServerMethod = (isset($_SERVER["REQUEST_METHOD"]) ? $_SERVER["REQUEST_METHOD"] : "Unknown" );
	    $ServerFile = (isset($_SERVER["PHP_SELF"]) ? $_SERVER["PHP_SELF"] : (isset($_SERVER["SCRIPT_NAME"]) ? $_SERVER["SCRIPT_NAME"] : "Unknown" ) );

		return array(
					'host' => $ServerHost,
					'addr' => $ServerAddr,
					'method' => $ServerMethod,
					'file' => $ServerFile
		);

	}

	// Private method that will write the text logs into the $LOGFILENAME.
	private function log($event = 'VISIT', $more1 = '', $more2 = '') {

		$datetime = date("Y-m-d H:i:s e");
		if (!file_exists($this->LOGFILENAME)) {
			$headers = $this->HEADERS . "\n";
			$fajl_beolvas = fopen($this->LOGFILENAME,"w");
			fclose($fajl_beolvas);
		}

		$fd = fopen($this->LOGFILENAME, "a+");

		if (@$headers) {
			fwrite($fd, $headers);
		}

		$this_browser = $this->getBrowser();
		$this_server = $this->getServer();
		$debugBacktrace = debug_backtrace();
		$document_root = $debugBacktrace[1]['file'];
		$country = ip2country::get_country_name();
		$ip = ip2country::get_client_ip();

		$entry = array(
					$event,
					$datetime,
					$this_server['host'],
					$this_server['addr'],
					$this_server['method'],
					$this_server['file'],
					$more1,
					$more2,
					$country,
					$ip,
					$this_browser["userAgent"],
					$this_browser["name"],
					$this_browser["version"],
					$this_browser["platform"],
					$this_browser["language"],
					$document_root );

		fputcsv($fd, $entry, $this->SEPARATOR);
		fclose($fd);

	}

	public function logging($mit = 'VISIT', $m1 = self::DEFAULT_TAG, $m2 = self::DEFAULT_TAG) {

		self::log($mit, $m1, $m2);
	}

	// Function to write ERROR messages that will be written into $LOGFILENAME.
	// These messages are fatal errors. Your script will NOT work properly if an ERROR happens, right?
	public function error($m1 = self::DEFAULT_TAG, $m2 = self::DEFAULT_TAG) {

		self::log('ERROR', $value, $tag);
	}

}

// Set $log variable

$log = new MyLogPHP(';', $config);

?>
