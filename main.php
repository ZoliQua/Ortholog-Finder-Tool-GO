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
* Page - MAIN File
* *******************
*
* This file redirects user handles database.
*
* *******************
*
* All code can be used under GNU General Public License version 2.
* If you have any question or find some bug please email me.
*
*/

// COUNTING TIME

	$time_start = microtime(true);

// THIS FILE

	$this_file = "main.php";

// INCLUDING files

	$folderIncl = "includes/";

	// include_once($folderIncl . "mysql.php"); // MySQL connect
	include_once($folderIncl . "mylog.php"); // MyLOG Logger file

	include_once($folderIncl . "inc_functions.php"); // INCL functions
	include_once($folderIncl . "inc_variables.php"); // INCL variables
	include_once($folderIncl . "inc_analyzer_dumper.php"); // INCL variables	

// LOGGING

	$log->logging('PAGE VISIT', "page request", $arrThisPage[$numPage][0] . " (" . $numPage . ")");

?>
<html>
<head>

	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<meta http-equiv="cache-control" content="max-age=86400, public">
	<meta http-equiv="content-language" content="en-US">
	<title>GeneOntology Extension Tool</title>
	<meta name="description" content="A bioinformatics tool that collects evolutionarily conserved proteins, which have been described as a funcional regulators in genome-wide studies previously. Currently it focueses on cell size.">
	<meta name="keywords" content="Orthology Finder Tool, Ortholog, Orthology, evolutionally, conserved, functional ortholgos, orthologous proteins, cell size, cell cycle, systems biology, network biology, kegg database, gene ontology" />
	<meta name="copyright" content="Zoltan Dul, 2018">
	<meta nmae="author" content="https://plus.google.com/+ZoltánDúl">
	<meta name="googlebot" content="index, follow">
	<meta name="robots" content="index, follow">
	 
	<script type="text/javascript" src="https://code.jquery.com/jquery-1.12.4.js"></script>
	<script type="text/javascript" src="https://cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js"></script>
	<script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.10.15/datatables.min.js"></script>

	<link rel="stylesheet" href="media/css/go.css" type="text/css" />
	<link rel="stylesheet" href="media/css/table.css" type="text/css" />
	<link rel="stylesheet" href="media/css/table_jui.css" type="text/css" />
	<link rel="stylesheet" href="media/css/jquery-ui-1.10.4.custom.min.css" type="text/css" />

</head>
<body class="background">
<div id="main">
	<div id="header-w">
    	<div id="header"></div>
	</div>

	<div id="wrapper">
	<div id="nav">
		<div id="nav-inside">
		<ul class="menu">
			<li><a href='<?php print $this_file; ?>?page=query'>QUERY</a></li>
			<li><a href='<?php print $this_file; ?>?page=source'>REFERENCES</a></li>
			<li><a href='http://www.orthologfindertool.com/'>LINK TO ORTH TOOL</a></li>
			<li><a href='<?php print $this_file; ?>?page=about'>ABOUT US</a></li>
		</ul>
		</div>
	</div>
	</div>

<!-- Google Analytics START //-->

	<script>
	  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
	  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
	  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
	  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

	  ga('create', 'UA-28455960-3', 'auto');
	  ga('send', 'pageview');
	  ga(‘set’, ‘&uid’, {{USER_ID}}); // Set the user ID using signed-in user_id.

	</script>

<!-- Google Analytics END //-->

	<div id="main-content">
		<h2><a href name='zero'></a><?php print $thisPageTitle; ?></h2>

		<div id="center"><?php include_once($thisPageIncl); ?></div>

		<div id="gototop"><a href='#zero'>Go to the top</a></div>
		<div id="netbiol">&copy; <?php echo date("Y"); ?> <a href="http://www.kcl.ac.uk/index.aspx" target="_blank" class="pagebottomlink">King's College London</a> & <a href="http://www.fmach.it/" target="_blank" class="pagebottomlink">Fondazione Edmund Mach</a></div>
		<div class="bottompage"></div>
	</div>

</div>
</body>
</html>
