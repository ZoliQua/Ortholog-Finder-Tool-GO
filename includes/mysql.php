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
* Page - MySQL connector file
* *******************
*
* *******************
*
* All code can be used under GNU General Public License version 2.
* If you have any question or find some bug please email me.
*
*/

/*

COMMMENT IN if needed to show error report
*/
error_reporting(E_ALL);
ini_set('display_errors', 'on');
ini_set('memory_limit', '-1');
ini_set('max_execution_time', '-1');
ini_set('auto_detect_line_endings', true);

/* PHP + MySQL connect és database selector */

header('Content-Type: text/html; charset=UTF-8');

$config['host'] = 'localhost';		// host name
$config['user'] = 'root';			// database username
$config['pass'] = 'zolis';			// database password
$config['data'] = 'ortholog';		// database name

$mysqli = new mysqli($config['host'], $config['user'], $config['pass'], $config['data']);

if ($mysqli->connect_errno) {
    printf("I cannot connect to the database, error ::  %s\n", $mysqli->connect_error);
    exit();
}

?>
