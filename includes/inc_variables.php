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
* INCL - VARIABLES
* *******************
*
* All code can be used under GNU General Public License version 2.
* If you have any question or find some bug please email me.
*
*/

/* .................. */
/* VARIABLES OF FILES */
/* .................. */

	$folderSource = "source/";
	$folderOutput = "output/";

// Name of Species and TaxIDs

	$species = array();
	$species["at"] = array("lil" => "AT", "mid" => "A.thaliana", "long" => "Arabidopsis thaliana", "taxid" => "3702");
	$species["ce"] = array("lil" => "CE", "mid" => "C.elegans", "long" => "Caenorhabditis elegans", "taxid" => "6239");
	$species["dm"] = array("lil" => "DM", "mid" => "D.melanogaster", "long" => "Drosophila melanogaster", "taxid" => "7227");
	$species["dr"] = array("lil" => "DR", "mid" => "D.rerio", "long" => "Danio rerio", "taxid" => "7955");
	$species["hs"] = array("lil" => "HS", "mid" => "H.sapiens", "long" => "Homo sapiens", "taxid" => "9606");
	$species["sc"] = array("lil" => "SC", "mid" => "S.cerevisiae", "long" => "Saccharomyces cerevisiae", "taxid" => "559292");
	$species["sp"] = array("lil" => "SP", "mid" => "S.pombe", "long" => "Schizosaccharomyces pombe", "taxid" => "4896");

// Gene Ontology - GO_SLIM Database

	$gos =  ["GO:0000003" => "reproduction", 
		"GO:0000902" => "cell morphogenesis", 
		"GO:0000910" => "cytokinesis", 
		"GO:0002376" => "immune system process", 
		"GO:0003013" => "circulatory system process", 
		"GO:0005975" => "carbohydrate metabolic process", 
		"GO:0006091" => "generation of precursor metabolites and ", 
		"GO:0006259" => "DNA metabolic process", 
		"GO:0006397" => "mRNA processing", 
		"GO:0006399" => "tRNA metabolic process", 
		"GO:0006412" => "translation", 
		"GO:0006457" => "protein folding", 
		"GO:0006461" => "protein complex assembly", 
		"GO:0006464" => "cellular protein modification process", 
		"GO:0006520" => "cellular amino acid metabolic process", 
		"GO:0006605" => "protein targeting", 
		"GO:0006629" => "lipid metabolic process", 
		"GO:0006790" => "sulfur compound metabolic process", 
		"GO:0006810" => "transport", 
		"GO:0006913" => "nucleocytoplasmic transport", 
		"GO:0006914" => "autophagy", 
		"GO:0006950" => "response to stress", 
		"GO:0007005" => "mitochondrion organization", 
		"GO:0007009" => "plasma membrane organization", 
		"GO:0007010" => "cytoskeleton organization", 
		"GO:0007034" => "vacuolar transport", 
		"GO:0007049" => "cell cycle", 
		"GO:0007059" => "chromosome segregation", 
		"GO:0007067" => "mitotic nuclear division", 
		"GO:0007155" => "cell adhesion", 
		"GO:0007163" => "establishment or maintenance of cell pol", 
		"GO:0007165" => "signal transduction", 
		"GO:0007267" => "cell-cell signaling", 
		"GO:0007568" => "aging", 
		"GO:0008150" => "biological_process", 
		"GO:0008219" => "cell death", 
		"GO:0008283" => "cell proliferation", 
		"GO:0008361" => "regulation of cell size", 
		"GO:0009056" => "catabolic process", 
		"GO:0009058" => "biosynthetic process", 
		"GO:0009790" => "embryo development", 
		"GO:0015979" => "photosynthesis", 
		"GO:0016192" => "vesicle-mediated transport", 
		"GO:0019748" => "secondary metabolic process", 
		"GO:0021700" => "developmental maturation", 
		"GO:0022607" => "cellular component assembly", 
		"GO:0022618" => "ribonucleoprotein complex assembly", 
		"GO:0030154" => "cell differentiation", 
		"GO:0030198" => "extracellular matrix organization", 
		"GO:0030705" => "cytoskeleton-dependent intracellular tra", 
		"GO:0032196" => "transposition", 
		"GO:0034330" => "cell junction organization", 
		"GO:0034641" => "cellular nitrogen compound metabolic pro", 
		"GO:0034655" => "nucleobase-containing compound catabolic", 
		"GO:0040007" => "growth", 
		"GO:0040011" => "locomotion", 
		"GO:0042254" => "ribosome biogenesis", 
		"GO:0042592" => "homeostatic process", 
		"GO:0043473" => "pigmentation", 
		"GO:0044281" => "small molecule metabolic process", 
		"GO:0044403" => "symbiosis, encompassing mutualism throug", 
		"GO:0048646" => "anatomical structure formation involved ", 
		"GO:0048856" => "anatomical structure development", 
		"GO:0048870" => "cell motility", 
		"GO:0050877" => "neurological system process", 
		"GO:0051186" => "cofactor metabolic process", 
		"GO:0051276" => "chromosome organization", 
		"GO:0051301" => "cell division", 
		"GO:0051604" => "protein maturation", 
		"GO:0051726" => "regulation of cell cycle", 
		"GO:0055085" => "transmembrane transport", 
		"GO:0061024" => "membrane organization", 
		"GO:0065003" => "macromolecular complex assembly", 
		"GO:0071554" => "cell wall organization or biogenesis", 
		"GO:0071941" => "nitrogen cycle metabolic process" ];

// PAGE DETECT

	if( ! isset($_GET["page"]) ) $numPage = 1;
	else {

		if ($_GET["page"] == "source") $numPage = 3;
		elseif ($_GET["page"] == "about") $numPage = 4;
		else $numPage = 1;

	}

// INCLUDING PAGES

	$arrThisPage = array();

	$arrThisPage[1] = array("QUERY","page_1_analyzer.php");
	$arrThisPage[2] = array("QUERY RESULTS","page_2_analysis_original.php");
	$arrThisPage[3] = array("Sources","page_3_sources.php");
	$arrThisPage[4] = array("About us","page_4_aboutus.php");

	$thisPageIncl = $folderIncl . "/" . $arrThisPage[$numPage][1];
	$thisPageTitle = $arrThisPage[$numPage][0];

// PAGE DETECTING VALUES

	$arrSpeciesAll = ["at", "ce", "dm", "dr", "hs", "sc", "sp"];

	if(! isset($_POST["thisgo"])) $go = "GO:0051726";
	elseif( array_key_exists(trim($_POST["thisgo"]), $gos) ) $go = trim($_POST["thisgo"]);
	else $go = "GO:0051726";

	if(! isset($_POST["specs"])) $spec = $arrSpeciesAll;
	elseif( count($_POST["specs"]) < 2 ) $spec = $arrSpeciesAll;
	elseif( SpeciesValidation($_POST["specs"], $arrSpeciesAll) ) $spec = SpeciesValidation($_POST["specs"], $arrSpeciesAll);
	else $spec = $arrSpeciesAll;

	if(! isset($_POST["type"])) $type = 1;
	elseif(trim($_POST["type"]) == 1 ) $type = 1;
	elseif(trim($_POST["type"]) == 2 ) $type = 2;
	else $type = 1;

	if(! isset($_POST["threshold"])) $threshold = 2;
	elseif(trim($_POST["threshold"]) > 2 AND trim($_POST["threshold"]) < 8 ) $threshold = trim($_POST["threshold"]);
	else $threshold = 2;

	if(! isset($_GET["sizemanual"])) $booSizeManual = false;
	elseif(trim($_GET["sizemanual"]) == "on" ) $booSizeManual  = true;
	else $booSizeManual = false;

	$mit = "real";
	$ins = false;
	$first = false;

	$possible_numbers = range(2, 7);

	$numSpecies = count($spec);

	$given_values = array("mit" => $mit, "ins" => $ins, "spec" => $spec, "first" => $first, "num" => $numSpecies, "type" => $type, "threshold" => $threshold, "go" => $go, 'sizemanual' => $booSizeManual);

	$files = array();
	$files["list"] = array();

	if($type == 1) $files["output"] = $folderOutput  . "GO" . substr($go, 3) . "-Venn-Diagram-" . $numSpecies . "-" . implode("-", $spec) . ".svg";
	else $files["output"] = $folderOutput  . "GO" . substr($go, 3) . "Edwards-Venn-Diagram-" . $numSpecies . "-" . implode("-", $spec) . ".svg";

?>