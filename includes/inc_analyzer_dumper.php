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

// Classes

class QueryGO {

	public $sorsz = 0;
	public $lista;
	public $values;
	public $printelni = ""; // ebbe megy az SVG kódja
	public $infos = ""; // ez az információk stringje
	public $faj = array();
	public $faj_mid2lil = array();
	public $kiiras_beolv;
	public $kiir_fajl;
	public $kiiras = false;
	public $species = array();
	public $species_number = 0;
	public $get_values = array();
	public $fajok = array();
	public $counter = array();
	public $mappings = array();
	public $unip2groupID = array();
	public $groupID2unip = array();
	public $groupID2spec = array();
	public $container = array();
	public $value_tomb = array();
	public $protein_list = array();
	public $protein_list2 = array();
	public $folders;
	public $arrSizeControl = array();
	public $containertable = array();
	public $strTablePrint = "";
	public $strTablePrintExpanded = "";
	public $strConservedCore = "";
	public $strNovelAnnotations = "";
	public $arrGroupsAll = array();
	public $arrGroupsHit = array();
	public $arrGroupsByOrder = array();

	public function __construct($files, $folders, $faj, $given_values, $gos) {

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

		// open file
		self::FileReader();


		// Counter Shower
		self::Counter();


		/*

		// MySQL DB process
		$this->lista = self::db_processor($given_values["go"]);

		$this->values = self::analyzer($faj);
		*/

		self::CreateTable();

		return true;
	}

	public function FileReader () {

		$tmeFileReader = microtime(true);
		$strSourceFolderName = "source/";
		$strSourceGOFileName = $strSourceFolderName . "eggNOG-export-" . substr($this->get_values["go"], 3) . "-7.csv"; 

		$strError = "";

		$objFileOpen = fopen($strSourceGOFileName, "r");

		if(!$objFileOpen) $strError .= "Cannot read file  <b>" . $strSourceGOFileName . "</b>!";
		if($strError != "") die($strError);

		$arrRowSpec = array();
		$arrRowSpecBase = array("AT" => array(3, 4), 
							"CE" => array(5, 6), 
							"DM" => array(7, 8), 
							"DR" => array(9, 10), 
							"HS" => array(11, 12), 
							"SC" => array(13, 14), 
							"SP" => array(15, 16) );		

		foreach ($arrRowSpecBase as $strSpecName => $arrSpecRowID) {

			$strSpecN = strtolower($strSpecName);
			if(in_array($strSpecN, $this->get_values["species"])) $arrRowSpec[$strSpecName] = $arrRowSpecBase[$strSpecName];

		}	

		// print_r($arrRowSpec);

		$numRowNr = 0;
		$arrGroupContainer = array();

		while ( ($strRowContent = fgets($objFileOpen) ) !== false) {

			$numRowNr++;
					
			if ( empty($strRowContent) ) continue;
			else {

				$arrExplodedFields = explode(",", $strRowContent);

				$arrSpeciesID = array();

				$arrFields = array();
				$arrFields["group"] = trim($arrExplodedFields[0]);
				$arrFields["All"] = (int) trim($arrExplodedFields[1]);
				$arrFields["Hit"] = (int) trim($arrExplodedFields[2]);

				$arrGroupContainer[$arrFields["group"]] = array();

				//if($arrFields["Hit"] < 1) continue;

				foreach ($arrRowSpec as $strSpecName => $arrSpecRowID) {

					$strSpecHitName = $strSpecName . "-HIT";
					
					if(trim($arrExplodedFields[$arrSpecRowID[0]]) == "NONE") {
						$arrFields[$strSpecName] = array();
						$arrFields[$strSpecHitName] = array();
					}

					else {
						$arrFields[$strSpecName] = explode(";", trim($arrExplodedFields[$arrSpecRowID[0]]));

						$arrSpeciesID[] = $strSpecName;
						$strMidFaj = $this->faj[strtolower($strSpecName)]["mid"];

						if(trim($arrExplodedFields[$arrSpecRowID[1]]) == "ZERO") $arrFields[$strSpecHitName] = array();
						else {
							$arrFields[$strSpecHitName] = explode(";", trim($arrExplodedFields[$arrSpecRowID[1]]));
						}

						$arrGroupContainer[$arrFields["group"]][$strMidFaj] = self::PutStronger($arrFields[$strSpecName], $arrFields[$strSpecHitName]);
					}
				}

				sort($arrSpeciesID);
				$strSpeciesID = implode(",", $arrSpeciesID);

				if(! array_key_exists($strSpeciesID, $this->containertable)) {

					$this->containertable[$strSpeciesID] = array($arrFields["group"] => $arrGroupContainer[$arrFields["group"]]);

				}
				else $this->containertable[$strSpeciesID][$arrFields["group"]] = $arrGroupContainer[$arrFields["group"]];
				

				if(!array_key_exists($strSpeciesID, $this->arrGroupsByOrder)) $this->arrGroupsByOrder[$strSpeciesID] = 1;
				else $this->arrGroupsByOrder[$strSpeciesID]++;

			}

		}

		ksort($this->arrGroupsByOrder);

		/*

		$sum = 0;

		foreach ($this->arrGroupsByOrder as $key => $value) {

			$sum += $value;
			print $value . "<BR>";
		}

		print "<BR>SUM: " . $sum . "<BR>";

		*/

		$this->infos .= TimeEnd($tmeFileReader, "File Reader");
	}

	public function PutStronger ($arrProteins, $arrHits) {

		$arrReturn = array();

		foreach ($arrProteins as $nr => $protein) {
			$arrReturn[] = ( in_array($protein, $arrHits) ? "<STRONG><A href=\"http://www.uniprot.org/uniprot/".$protein."\" target=\"_blank\">".$protein."</A></STRONG>" : "<A href=\"http://www.uniprot.org/uniprot/".$protein."\" target=\"_blank\">".$protein."</A>" );
		}

		return $arrReturn;
	}

	public function Counter () {

		// SVG File

			$svg_diagram = new SVG_File($this->folders, $this->species_number, $this->get_values["type"]);

		// create ReplacR

			// Creating a array [LETTER]:[lilfaj as value]

			$replaceR = array();
			$range = range("A","J");

			foreach ($this->species["lil"] as $k => $lilfaj) $replaceR[$range[$k]] = trim($lilfaj);

		// VENN DIAGRAM - with PERMUTATATION

			$permutation = new VennDiagram($this->species_number, $replaceR);

			$sorrend = $permutation->replacedPermutation;
			$translator = $permutation->translator;


		// Create Output

			foreach ($sorrend as $nr => $this_key) {

				if(array_key_exists($this_key, $this->arrGroupsByOrder)) $tomb_count[$this_key] = $this->arrGroupsByOrder[$this_key];
				else $tomb_count[$this_key] = 0;
			}

			if(in_array("at", $this->get_values["species"])) $tomb_count["AT"] = "AT";
			if(in_array("ce", $this->get_values["species"])) $tomb_count["CE"] = "CE";
			if(in_array("dm", $this->get_values["species"])) $tomb_count["DM"] = "DM";
			if(in_array("dr", $this->get_values["species"])) $tomb_count["DR"] = "DR";
			if(in_array("hs", $this->get_values["species"])) $tomb_count["HS"] = "HS";
			if(in_array("sc", $this->get_values["species"])) $tomb_count["SC"] = "SC";
			if(in_array("sp", $this->get_values["species"])) $tomb_count["SP"] = "SP";

			self::Kiir($svg_diagram->svg, $tomb_count, $translator);

		/*

			if($NumOfSpecs != count($this->groupID2spec[$this->unip2groupID[$unip1]]) ) continue;

			if(! array_key_exists($this->unip2groupID[$unip1], $ListOfGroups)) $ListOfGroups[$this->unip2groupID[$unip1]] = array($faj1 => array($unip1 => ((array_key_exists($unip1, $this->protein_list[$fajKeys_mid2lil[$faj1]])) ? "<STRONG><A href=\"http://www.uniprot.org/uniprot/".$unip1."\" target=\"_blank\">".$unip1."</A></STRONG>" : "<A href=\"http://www.uniprot.org/uniprot/".$unip1."\" target=\"_blank\">".$unip1."</A>" )));
			if(! array_key_exists($faj1, $ListOfGroups[$this->unip2groupID[$unip1]])) $ListOfGroups[$this->unip2groupID[$unip1]][$faj1] = array($unip1 => ((array_key_exists($unip1, $this->protein_list[$fajKeys_mid2lil[$faj1]])) ? "<STRONG><A href=\"http://www.uniprot.org/uniprot/".$unip1."\" target=\"_blank\">".$unip1."</A></STRONG>" : "<A href=\"http://www.uniprot.org/uniprot/".$unip1."\" target=\"_blank\">".$unip1."</A>" ));
			else $ListOfGroups[$this->unip2groupID[$unip1]][$faj1][$unip1] = ((array_key_exists($unip1, $this->protein_list[$fajKeys_mid2lil[$faj1]])) ? "<STRONG><A href=\"http://www.uniprot.org/uniprot/".$unip1."\" target=\"_blank\">".$unip1."</A></STRONG>" : "<A href=\"http://www.uniprot.org/uniprot/".$unip1."\" target=\"_blank\">".$unip1."</A>" );

		}						

		$tomb_count[$this_key] = count($ListOfGroups);

		$this->containertable[$this_key] = $ListOfGroups;

		*/
	}

	public function get_values($given_values, $faj) {

		$this->get_values["mit"] = $given_values["mit"];
		$this->get_values["species"] = $given_values["spec"];
		$this->get_values["inside"] = $given_values["ins"];
		$this->get_values["first"] = $given_values["first"];
		$this->get_values["go"] = $given_values["go"];
		$this->get_values["type"] = $given_values["type"];
		$this->get_values["threshold"] = $given_values["threshold"];
		$this->get_values["sizemanual"] = $given_values["sizemanual"];

		if (count($this->get_values["species"] ) == $given_values["num"] ) {

			// case where "number of species" given and "number" given is identical
			// we check back, that each species must be in our library if not given_numbers species is getting selected.

			$check_back = false;

			foreach ($this->get_values["species"] as $key => $this_spec) {
				if(array_key_exists(strtolower($this_spec), $faj)) $this->species[] =  strtolower($this_spec);
				else $check_back = true;
			}

			if($check_back) {
				$random_species = array_rand($faj, $given_values["num"]);
				$this->species = $random_species;
			}

		}
		else {

			// in case where something is not identical in numbers. We selec 5 random species and choose 5-set venn diagram

			$random_species = array_rand($faj, 5);
			$this->species = $random_species;

		}

		$lils = array();
		$mids = array();
		$count = 0;

		sort($this->species);

		foreach ($this->species as $key => $lil) {

			$lils[] = strtoupper($lil);
			$mids[$faj[$lil]["mid"]] = $faj[$lil]["mid"];

			// counter beállítása

			$this->counter[$faj[$lil]["mid"]] = 0;

			$count++;
		}

		$this->species = array("lil" => $lils, "mid" => $mids);
		$this->species_number = $count;

		return true;
	}

	public function Kiir($svg_diagram, $tomb_count, $translator){

		$csere = $svg_diagram;

		foreach ($tomb_count as $key => $count) {
			$mit_csereljek = ">" . $translator[$key] . "<";
			$mire_csereljem = ">" . $count . "<";
			$csere = str_replace($mit_csereljek, $mire_csereljem, $csere);
		}

		$names = array("NameA", "NameB", "NameC", "NameD", "NameE", "NameF", "NameG");
		$i = 0;
		
		foreach ($this->species["mid"] as $key => $mid) {
			$mit_csereljek = ">" . $names[$i] . "<";
			$mire_csereljem = ">" . $mid. "<";
			$csere = str_replace($mit_csereljek, $mire_csereljem, $csere);
			$i++;
		}

		$this->printelni = $csere;
		return true;
	}

	public function CreateTable(){

		// Content

		$id = 0;
		$boolPrintTitle = true;
		$strConservedCore = ""; 
		$strNovelAnnotations = ""; 


		$this->strTablePrint .= "<BR>\n<H1 stlye=\"center\">Current Annotations based on Orthology</H1>\n";
		$this->strTablePrint .= "<div style=\"text-align: center;\">Hit species threshold: <b>" . $this->get_values["threshold"] . "</b></div>\n";

		$this->strTablePrintExpanded .= "<BR>\n<H1 stlye=\"center\">Current Annotations based on Orthology (Expanded)</H1>\n";
		$this->strTablePrintExpanded .= "<div style=\"text-align: center;\">Hit species threshold: <b>" . $this->get_values["threshold"] . "</b></div>\n";

		$this->strConservedCore .= "<BR>\n<H1 stlye=\"center\">Conserved Core</H1>\n";
		$this->strConservedCore .= "<div style=\"text-align: center;\">Hit species threshold: <b>" . $this->get_values["threshold"] . "</b></div>\n";

		$this->strNovelAnnotations .= "<BR>\n<H1 stlye=\"center\">Novel Annotations based on Orthology</H1>\n";

		$strTableHead = "
			<THEAD>
				<TR>
				<TH><B>Group</B></TH>
				<TH><B>Average H/M</B></TH>
				<TH><B>Total H/M</B></TH>
				<TH><B>Hit species</B></TH>
				<TH><B>Total species</B></TH>
				<TH><B>Hit protein</B></TH>
				<TH><B>Total protein</B></TH>
				<TH><B>List of Hit species</B></TH>
				</TR>
			</THEAD>\n";

		$strTableHeadNovelAnnotation = "
			<THEAD>
				<TR>
				<TH><B>Group</B></TH>
				<TH><B>Average H/M</B></TH>
				<TH><B>Total species</B></TH>
				<TH><B>A. thaliana</B></TH>
				<TH><B>C. elegans</B></TH>
				<TH><B>D. melanogaster</B></TH>
				<TH><B>D. rerio</B></TH>
				<TH><B>H. sapiens</B></TH>
				<TH><B>S. cerevisiae</B></TH>
				<TH><B>S. pombe</B></TH>
				</TR>
			</THEAD>\n";

		$strTableHeadResultsExpanded = "
			<THEAD>
				<TR>
				<TH><B>Group</B></TH>
				<TH><B>Average H/M</B></TH>
				<TH><B>Total H/M</B></TH>
				<TH><B>Hit species</B></TH>
				<TH><B>Total species</B></TH>
				<TH><B>Hit protein</B></TH>
				<TH><B>Total protein</B></TH>
				<TH><B>A. thaliana</B></TH>
				<TH><B>C. elegans</B></TH>
				<TH><B>D. melanogaster</B></TH>
				<TH><B>D. rerio</B></TH>
				<TH><B>H. sapiens</B></TH>
				<TH><B>S. cerevisiae</B></TH>
				<TH><B>S. pombe</B></TH>
				</TR>
			</THEAD>\n";

		$strExportTable = preg_replace('/\s+/', '', substr(strip_tags(str_replace("</B>", ",", $strTableHead)), 0, -1));
		// print $strExportTable;

		$this->strTablePrint .= "<P></P>
			<DIV align='center'>
			<TABLE cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"display\" id=\"results\">\n";

		$this->strTablePrintExpanded .= "<P></P>
			<DIV align='center'>
			<TABLE cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"display\" id=\"resultsexpanded\">\n";

		$this->strConservedCore .= "<P></P>
			<DIV align='center'>
			<TABLE cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"display\" id=\"conservedcore\">\n";

		$this->strNovelAnnotations .= "<P></P>
			<DIV align='center'>
			<TABLE cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"display\" id=\"novelannotations\">\n";

		$this->strTablePrint .= $strTableHead;
		$this->strTablePrintExpanded .= $strTableHeadResultsExpanded;
		$this->strConservedCore .= $strTableHead;
		$this->strNovelAnnotations .= $strTableHeadNovelAnnotation;

		$this->strTablePrint .= "\t\t\t<TBODY>\n";
		$this->strTablePrintExpanded .= "\t\t\t<TBODY>\n";
		$this->strConservedCore .= "\t\t\t<TBODY>\n";
		$this->strNovelAnnotations .= "\t\t\t<TBODY>\n";

		$strRowNovelAnnotationBase = array();
		$strRowNovelAnnotationBase["group"] = "\t<TD>-</TD>\n";
		$strRowNovelAnnotationBase["ratio"] = "\t<TD>-</TD>\n";
		$strRowNovelAnnotationBase["totspec"] = "\t<TD>-</TD>\n";
		$strRowNovelAnnotationBase["AT"] = "\t<TD>-</TD>\n";
		$strRowNovelAnnotationBase["CE"] = "\t<TD>-</TD>\n";
		$strRowNovelAnnotationBase["DM"] = "\t<TD>-</TD>\n";
		$strRowNovelAnnotationBase["DR"] = "\t<TD>-</TD>\n";
		$strRowNovelAnnotationBase["HS"] = "\t<TD>-</TD>\n";
		$strRowNovelAnnotationBase["SC"] = "\t<TD>-</TD>\n";
		$strRowNovelAnnotationBase["SP"] = "\t<TD>-</TD>\n";

		$strRowExpandedResultsBase = array();
		$strRowExpandedResultsBase["group"] = "\t<TD>-</TD>\n";
		$strRowExpandedResultsBase["ratio1"] = "\t<TD>-</TD>\n";
		$strRowExpandedResultsBase["ratio2"] = "\t<TD>-</TD>\n";
		$strRowExpandedResultsBase["hitspec"] = "\t<TD>-</TD>\n";
		$strRowExpandedResultsBase["totspec"] = "\t<TD>-</TD>\n";
		$strRowExpandedResultsBase["hitprot"] = "\t<TD>-</TD>\n";
		$strRowExpandedResultsBase["totprot"] = "\t<TD>-</TD>\n";
		$strRowExpandedResultsBase["AT"] = "\t<TD>-</TD>\n";
		$strRowExpandedResultsBase["CE"] = "\t<TD>-</TD>\n";
		$strRowExpandedResultsBase["DM"] = "\t<TD>-</TD>\n";
		$strRowExpandedResultsBase["DR"] = "\t<TD>-</TD>\n";
		$strRowExpandedResultsBase["HS"] = "\t<TD>-</TD>\n";
		$strRowExpandedResultsBase["SC"] = "\t<TD>-</TD>\n";
		$strRowExpandedResultsBase["SP"] = "\t<TD>-</TD>\n";

		foreach ($this->containertable as $this_key => $groups) {

			// print $this_key . ": " . count($this->containertable[$this_key]) . "<BR>";

			$id++;

			$this_keys = explode(",", $this_key);

			$numMeasureTotalSpecies = count($this_keys);
			$numRowsInTable = 0;

			// Foreaching each of the Orthology Groups

			foreach ($groups as $groupID => $arri2) {

				$strRowNovelAnnotation = $strRowNovelAnnotationBase;
				$strRowExpandedResults = $strRowExpandedResultsBase;

				$numMeasureTotalSpeciesHit = 0;
				$numMeasureTotalMember = 0;
				$numMeasureTotalHit = 0;
				$numMeasureTotalRatio = 0;

				$arrMeasureMember = array();
				$arrMeasureHit = array();
				$arrMeasureRatio = array(); 

				$arrMembers = array();

				$numLargestMemberInARow = 0;
				
				foreach ($this_keys as $k => $v) {

					if(! array_key_exists(strtolower($v), $this->faj) ) continue;
					if( ! array_key_exists( $this->faj[strtolower($v)]["mid"], $groups[$groupID]) ) continue;

					$txtListOfProteins = implode(", ", $groups[$groupID][$this->faj[strtolower($v)]["mid"]]);

					$arrMeasureMember[$v] = count($groups[$groupID][$this->faj[strtolower($v)]["mid"]]);
					$arrMeasureHit[$v] = substr_count($txtListOfProteins, "STRONG") / 2;

					if($arrMeasureHit[$v] != 0 ) $arrMeasureRatio[$v] = $arrMeasureHit[$v] / $arrMeasureMember[$v];
					else $arrMeasureRatio[$v] = "0";

					if($arrMeasureHit[$v] > 0) $arrMembers[] = $this->faj[strtolower($v)]["mid"];

					if($numLargestMemberInARow <= $arrMeasureMember[$v]) $numLargestMemberInARow = $arrMeasureMember[$v];

					$numMeasureTotalMember += $arrMeasureMember[$v];
					$numMeasureTotalHit += $arrMeasureHit[$v];

					$numMeasureTotalSpeciesHit = ((strpos($txtListOfProteins, "STRONG") == true) ? ($numMeasureTotalSpeciesHit+1) : $numMeasureTotalSpeciesHit );

					$strRowNovelAnnotation[$v] = "\t " . ((strpos($txtListOfProteins, "STRONG") == true) ? "<TD><B>HIT</B>: " : "<TD bgcolor=\"#32CD32\"><I><B>PREDICTION</B></I>: " ) . $txtListOfProteins . "</TD>\n";

					$strRowExpandedResults[$v] = "\t <TD>" . $txtListOfProteins . "</TD>\n";
				}

				if($numMeasureTotalSpeciesHit < $this->get_values["threshold"]) continue;

				$numRatioWAvHM = self::TableRowStatistics($arrMeasureRatio);
				$numRatioHM = number_format( ( $numMeasureTotalHit / $numMeasureTotalMember), 3);

				$row = array();

				$row[] = "<TR>\n";
				$row[] = "\t<TD><i><A href='http://eggnogdb.embl.de/#/app/results?target_nogs=$groupID' target='_blank'>$groupID</A></i></TD>\n";
				//$row[] = "\t<TD><i><A href='http://eggnogdb.embl.de/#/app/results?target_nogs=$groupID' target='_blank'>$groupID (".$numMeasureTotalSpecies.")</A></i></TD>\n";
				$row[] = "\t<TD>" . $numRatioWAvHM . "</TD>\n";
				$row[] = "\t<TD>" . $numRatioHM . "</TD>\n";
				$row[] = "\t<TD>" . $numMeasureTotalSpeciesHit . "</TD>\n";
				$row[] = "\t<TD>" . $numMeasureTotalSpecies . "</TD>\n";
				$row[] = "\t<TD>" . $numMeasureTotalHit . "</TD>\n";
				$row[] = "\t<TD>" . $numMeasureTotalMember . "</TD>\n";
				// $row[] = "\t<TD>" . $numMeasureTotalSpeciesHit . " hit species in " . $numMeasureTotalSpecies . " species" . "</TD>\n";
				// $row[] = "\t<TD>" . $numMeasureTotalHit . " hit proteins in ". count($arrMembers) ." hit species from <BR>" . $numMeasureTotalMember . "  total proteins in " . $numMeasureTotalSpecies . " total species</TD>\n";
				$row[] = "\t<TD>" . implode(", ", $arrMembers) . "</TD>\n";

				$row[] = "</TR>\n";

				$this->strTablePrint .= implode("", $row);

				$strRowExpandedResults["group"] = "\t<TD><A href='http://eggnogdb.embl.de/#/app/results?target_nogs=$groupID' target='_blank'>$groupID (".$numMeasureTotalSpecies.")</A></TD>\n";
				$strRowExpandedResults["ratio1"] = "\t<TD>" . $numRatioWAvHM . "</TD>\n";
				$strRowExpandedResults["ratio2"] = "\t<TD>" . $numRatioHM . "</TD>\n";
				$strRowExpandedResults["hitspec"] = "\t<TD>" . $numMeasureTotalSpeciesHit . "</TD>\n";
				$strRowExpandedResults["totspec"] = "\t<TD>" . $numMeasureTotalSpecies . "</TD>\n";
				$strRowExpandedResults["hitprot"] = "\t<TD>" . $numMeasureTotalHit . "</TD>\n";
				$strRowExpandedResults["totprot"] = "\t<TD>" . $numMeasureTotalMember . "</TD>\n";

				if( $numLargestMemberInARow <= 1 ) $this->strTablePrintExpanded .= "<TR>\n" . implode("", $strRowExpandedResults) . "</TR>\n";

				if( $numMeasureTotalSpecies == $numMeasureTotalSpeciesHit) $this->strConservedCore .= implode("", $row);

				if( $numMeasureTotalSpecies == ($numMeasureTotalSpeciesHit + 1) AND ($numRatioWAvHM > 0.79) AND ($numRatioHM > 0.49)) {

					$strRowNovelAnnotation["group"] = "\t<TD><A href='http://eggnogdb.embl.de/#/app/results?target_nogs=$groupID' target='_blank'>$groupID (".$numMeasureTotalSpecies.")</A></TD>\n";
					$strRowNovelAnnotation["ratio"] = "\t<TD>" . $numRatioWAvHM . "</TD>\n";
					$strRowNovelAnnotation["totspec"] = "\t<TD>" . $numMeasureTotalSpecies . "</TD>\n";

					$this->strNovelAnnotations .= "<TR>\n" . implode("", $strRowNovelAnnotation) . "</TR>\n";

				}
			}

		}

		$this->strTablePrint .= "\t\t\t</TBODY>\n";
		$this->strTablePrintExpanded .= "\t\t\t</TBODY>\n";
		$this->strConservedCore .= "\t\t\t</TBODY>\n";
		$this->strNovelAnnotations .= "\t\t\t</TBODY>\n";

		$this->strTablePrint .= str_replace("HEAD", "FOOT", $strTableHead);
		$this->strTablePrintExpanded .= str_replace("HEAD", "FOOT", $strTableHeadResultsExpanded);
		$this->strConservedCore .= str_replace("HEAD", "FOOT", $strTableHead);
		$this->strNovelAnnotations .= str_replace("HEAD", "FOOT", $strTableHeadNovelAnnotation);

		$this->strTablePrint .= "</TABLE>\n";
		$this->strTablePrintExpanded .= "</TABLE>\n";
		$this->strConservedCore .= "</TABLE>\n";
		$this->strNovelAnnotations .= "</TABLE>\n";
		$this->strTablePrint .= "</DIV>";
		$this->strTablePrintExpanded .= "</DIV>";
		$this->strConservedCore .= "</DIV>";
		$this->strNovelAnnotations .= "</DIV>";

		$this->strTablePrint .= "<BR>\n<div class=\"infobox\">\n<A href=\"#\" target=\"_blank\">Download this table</A> in CSV file.</div><BR>";
		$this->strTablePrintExpanded .= "<BR>\n<div class=\"infobox\">\n<A href=\"#\" target=\"_blank\">Download this table</A> in CSV file.</div><BR>";
		$this->strConservedCore.= "<BR>\n<div class=\"infobox\">\n<A href=\"#\" target=\"_blank\">Download this table</A> in CSV file.</div><BR>";
		$this->strNovelAnnotations .= "<BR>\n<div class=\"infobox\">\n<A href=\"#\" target=\"_blank\">Download this table</A> in CSV file.</div><BR>";

		$this->strTablePrint .= $this->strTablePrintExpanded;
		$this->strTablePrint .= $this->strConservedCore;
		$this->strTablePrint .= $this->strNovelAnnotations;

	}

	public function TableRowStatistics($arrMeasureRatio)  {

		$numThisRatio = 0;

		foreach ($arrMeasureRatio as $k => $v) $numThisRatio += $v;
	
		$numThisRatio = $numThisRatio / count($arrMeasureRatio);

		return number_format($numThisRatio, 6);

	}
}

?>