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
* Page - ANALYZER
* *******************
*
* All code can be used under GNU General Public License version 2.
* If you have any question or find some bug please email me.
*
*/

// Values for a THE class

		$given_values = array(	
				"mit" => "real", 
				"ins" => false, 
				"spec" => ["at", "ce", "dm", "dr", "hs", "sc", "sp"], 
				"first" => false, 
				"num" => 7, 
				"type" => 1, 
				"threshold" => 3, 
				"go" => "GO:0008361", 
				"sizemanual" => false);

class QueryGOExport extends QueryGO {

	public function __construct($files, $folders, $faj, $given_values, $gos) {


		// mysql connect
		self::mysql_conn();

		// handle given values
		self::get_values($given_values, $faj);

		// $faj & $folders handle
		$this->faj = $faj;
		$this->folders = $folders;

		// output file
		$this->kiir_fajl = $files["output"];

		foreach($faj as $key => $arri) {

			if(! in_array(strtoupper($key), $this->species["lil"]) ) continue;
			$this->protein_list[ $arri["lil"] ] = array();
			$this->protein_list2[ $arri["lil"] ] = array();
			$this->mappings[ $arri["mid"] ] = array();
			$this->faj_mid2lil[ $arri["mid"] ] = $arri["lil"];
		}

		// MySQL DB process
		$this->lista = self::db_processor($given_values["go"]);

		$this->values = self::analyzer($faj);
		
		self::CreateTableOutput();

		return true;
	}

	public function CreateTableOutput() {


		// Content

		$id = 0;
		$strTableLine = "";

		$strRowBase = array();
		$strRowBase["group"] = "NameOfGroup,";
		$strRowBase["numAll"] = "NumAll,";
		$strRowBase["numHit"] = "NumAll,";
		$strRowBase["AT"] = "NONE,";
		$strRowBase["AT-HIT"] = "NONE,";
		$strRowBase["CE"] = "NONE,";
		$strRowBase["CE-HIT"] = "NONE,";
		$strRowBase["DM"] = "NONE,";
		$strRowBase["DM-HIT"] = "NONE,";
		$strRowBase["DR"] = "NONE,";
		$strRowBase["DR-HIT"] = "NONE,";
		$strRowBase["HS"] = "NONE,";
		$strRowBase["HS-HIT"] = "NONE,";
		$strRowBase["SC"] = "NONE,";
		$strRowBase["SC-HIT"] = "NONE,";
		$strRowBase["SP"] = "NONE,";
		$strRowBase["SP-HIT"] = "NONE,";

		foreach ($this->containertable as $this_key => $groups) {

			// print $this_key . ": " . count($this->containertable[$this_key]) . "<BR>";

			$id++;

			$this_keys = explode(",", $this_key);

			// Foreaching each of the Orthology Groups

			foreach ($groups as $groupID => $arri2) {

				$strRow = $strRowBase;
				$numMeasureTotalSpeciesHit = 0;
				$numMeasureTotalSpecies = 0;
				
				foreach ($this_keys as $k => $v) {

					if( ! array_key_exists( $this->faj[strtolower($v)]["mid"], $groups[$groupID]) ) continue;

					$arrListOfAllProteins = array();
					$arrListOfHitProteins = array();

					foreach ($groups[$groupID][$this->faj[strtolower($v)]["mid"]] as $key => $strUniProt) {

						$strUniprotCleared = strip_tags($strUniProt);
					 	$arrListOfAllProteins[] = $strUniprotCleared;
					 	
					 	$booAnnotated = ((strpos($strUniProt, "STRONG") == true) ? true : false);
					 	if($booAnnotated) $arrListOfHitProteins[] = $strUniprotCleared;
					 }

					$strListOfAllProteins = implode(";", $arrListOfAllProteins);

					if(count($arrListOfAllProteins) > 0) $numMeasureTotalSpecies++;

					if(count($arrListOfHitProteins) > 0) {
						$strListOfHitProteins = implode(";", $arrListOfHitProteins);
						$numMeasureTotalSpeciesHit++;
					}
					else $strListOfHitProteins = "ZERO";

					$strRow[$v] = $strListOfAllProteins . ",";
					$strRow[$v."-HIT"] = $strListOfHitProteins . ",";
				}

				$strRow["group"] = $groupID . ",";
				$strRow["numAll"] = $numMeasureTotalSpecies. ",";
				$strRow["numHit"] = $numMeasureTotalSpeciesHit . ",";

				$this->strTablePrint .= substr(implode("", $strRow), 0, -1)  . "\n";
			}
		}

	}

}

$logFileName = $folderOutput . "GO-DUMP-LOG-" . date("Y-m-d-H-i-s") . ".txt";
$logWriter = fopen($logFileName, "a+");

fwrite($logWriter, "Dumping started on " . date("Y-m-d H:i:s") . "\n");

foreach($gos as $go_id => $go_name) {

	// This run in case of there was a query.

		$time_go = microtime(true);

		$files["output"] = $folderOutput  . "eggNOG-export-" . substr($go_id, 3) . "-" . $numSpecies . ".csv";
		$given_values["go"] = $go_id;

	// Run Script for Query GO

		$objResults = new QueryGOExport($files, $folderSource, $species, $given_values, $gos);

	// EXPORT results into a CSV file

		$csvFileWriter = file_put_contents($files["output"], $objResults->strTablePrint);

	// Print

		$numLines = substr_count($objResults->strTablePrint, "\n");
		$logGO = $go_id . " - " . $go_name . " finished with " . $numLines . " lines. " . strip_tags(TimeEnd($time_go, $go_id));

		fwrite($logWriter, $logGO);
		print $logGO . " <BR>\n";

	}

fclose($logWriter);



// End Time Write Out

	print "<BR><BR><div class=\"exec-time\">\n";
	print TimeEnd($time_start);
	print "</div>";

?>
