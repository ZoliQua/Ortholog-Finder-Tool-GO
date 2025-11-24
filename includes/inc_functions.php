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

// Classes and functions

	class VennDiagram {

		public $permutation = array();
		public $orderedPermutation = array();
		public $replacedPermutation = array();
		public $permutArray = array();
		public $translator = array();
		public $keys = array();

		public function __construct($NrOfSets = 5, $replace = false) {

			$rangeNR = range("A","M");
			$this->permuation = self::venn_permutation(range("A", $rangeNR[$NrOfSets-1] ), $NrOfSets);

			for($i = $NrOfSets;$i >= 1; $i--) $this->orderedPermutation[] = $this->permuation[$i];

			if(!$replace) $replace = array("A" => "AT", "B" => "CE", "C" => "DM", "D" => "DR", "E" => "HS", "F" => "SC", "G" => "SP");
			self::exchanger($replace);

			return $this->replacedPermutation;
		}

		private function factorial($num) {

			$returnNum = $num;

			for($i=$num-1;$i>1;$i--) $returnNum = $returnNum * $i;

			return $returnNum;
		}

		private function venn_permutation($tombom, $pos, $return = false){

			if(!$pos or $pos > count($tombom) ) return $return;

			if(!$return) $return = array();
			$return[$pos] = array();

			$level = count($tombom);

			$currentOptions = 0;

			switch ($level) {
				case $pos:
					$maxOptions = 1;
					break;
				
				default:
					$maxOptions = self::factorial($level) / self::factorial($level - $pos) / self::factorial($pos);
					break;
			}		

			while ($maxOptions != $currentOptions) {

				$rand = array_rand($tombom, $pos);
				$thisOption = array();

				if($pos != 1) foreach ($rand as $key => $v) $thisOption[] = $tombom[$v]; 
				else $thisOption[] = $tombom[$rand]; 
				
				sort($thisOption);
				$thisOption = implode(",", $thisOption);

				if(! in_array($thisOption, $return[$pos])) $return[$pos][] = $thisOption;

				$currentOptions = count($return[$pos]);			
			}

			sort($return[$pos]);
			$pos--;

			$return = self::venn_permutation($tombom, $pos, $return);

			return $return;
		}

		public function exchanger($List) {

			$newPermutation = array();
			$KeyChecker = array();
			$KeyCollector = array();
			$ConvertedList = array();

			$MockReplacR = array("A" => "XXX", "B" => "XYX", "C" => "ZYZ", "D" => "VVV", "E" => "ZXZ", "F" => "YMY", "G" => "YXY");

			foreach ($List as $from => $to) {

				$KeyCollector[$to] = array();
				$KeyChecker[] = $to;
				$ConvertedList[$MockReplacR[$from]] = $to;

			}		

			foreach ($this->orderedPermutation as $id => $stringArray) {
				
				foreach ($stringArray as $nr => $lineString) {

					$newLine = $lineString;

					foreach ($MockReplacR as $from => $to) $newLine = str_replace($from, $to, $newLine);
					foreach ($ConvertedList as $from => $to) $newLine = str_replace($from, $to, $newLine);

					$newPermutation[] = $newLine;

					foreach ($KeyChecker as $nr => $id ) {
						if(strpos($newLine, $id) !== false) $KeyCollector[$id][] = $newLine;

					}

					$this->permutArray[$newLine] = array();
					$this->translator[$newLine] = str_replace(",","", $lineString);
					
				}

			}

			$this->keys = $KeyCollector;
			$this->replacedPermutation = $newPermutation;

			return true;
		}
	}

	class SVG_File {

		public $svg = "";

		public function __construct($folders, $num, $type = 1){

			if($num < 2 OR $num > 7) $num = 5;

			if($type == 1) $strType = "venn";
			else $strType = "edwards-venn";

			$filename = $folders . "ortholog_" . $strType . "_" . $num . "_sample.svg";
			$svg_open = file_get_contents($filename);

			$this->svg = $svg_open;

			return true;
		}
	}

	function SpeciesValidation($arraySource, $species) {

		$arrayReturn = [];

		foreach ($arraySource as $k => $v) {
			if(in_array($v, $species)) $arrayReturn[] = $v;
		}

		if(count($arrayReturn) == 0) return false;
		else return $arrayReturn;
	}

	function TimeEnd($time_start, $plustxt = "Overall") {

		$time_end = microtime(true);
		$exection_time = $time_end - $time_start;

		$hours = (int) ($exection_time / 3600);
		$minutes = ( (int) ($exection_time / 60) ) - ($hours * 60);
		$seconds = $exection_time - ( ( $hours * 3600 ) + ( $minutes * 60 ) );

		$txt = $hours . " hours " . $minutes . " minutes and " . substr($seconds, 0, 5) . " seconds. [" . $exection_time . "]";

		return "<p>The <i>$plustxt</i> execution time was $txt</p>\n";
	}

	function PrintTheOutput($strVennDiagram = false, $strInfoPostVD = false, $strTable = false, $strInfoPostTable = false, $num = false, $this_file, $go, $gos, $species) {

		global $spec;
		global $threshold;

		$strOutput = "";

		if($strVennDiagram) {

			$strOutput .= "<div style=\"text-align: center;\">";
			$strOutput .= "<H1 stlye=\"center\">".$num."-set Venn Diagram</H1>\n";
			$strOutput .= "<H2 stlye=\"center\">$go - " . $gos[$go] . "</H2>";
			$strOutput .= $strVennDiagram;
			$strOutput .= "</div>" . "<BR><BR>\n";
			$strOutput .= "<div class=\"infobox\">\n";
			$strOutput .= $strInfoPostVD;
			$strOutput .= "</div>";
		}

		if($strTable) $strOutput .= $strTable;

		$strOutput .= "<BR><BR>\n<H1 stlye=\"center\">New Query</H1><BR>
			<div class=\"text\">
			<p style=\"font-style: italic;\">Please select a GO annotation, then 2-7 species with CTRL button (or CMD in Mac) to see their orthological GO analysis<BR> with Gene Ontology Extension Tool. You can choose the visaulization from three options. <BR><div style='color: red;'>CSV download is currently unavailable I'm working on to fix.</div></p><BR>
			<FORM method=\"POST\" name=\"goext\" action=\"main.php\">
			<TABLE align=center border=0 cellpadding=7 cellspacing=0>

				<TR id=\"k0\">

					<TD align=\"center\" width=\"50%\"><div class=ctext>GO annotation:</div></TD>
					<TD align=\"center\" width=\"50%\"><div class=dtext >
				    	<SELECT name=\"thisgo\" id=\"value_c\">";

		foreach ($gos as $k => $v) $strOutput .= "<OPTION value='$k' " . ($k == $go ? "selected" : "" ) . ">$k - $v</OPTION>\n";

		$strOutput .= "
				 		</SELECT> &nbsp;</div>
				 	</TD>

				</TR>

				<TR id=\"k1\">

					<TD align=\"center\" width=\"50%\"><div class=ctext>Species: </div></TD>
					<TD align=\"center\" width=\"50%\"><div class=dtext >
				    	<SELECT name=\"specs[]\" size=\"7\" id=\"value_q\" multiple>";

		foreach ($species as $v => $arr) $strOutput .= "<OPTION value='$v'".(in_array($v, $spec) ? " selected" : "").">".$arr["long"]."</OPTION>\n";

		$strOutput .= "
				 		</SELECT> &nbsp;</div>
				 	</TD>

				</TR>

				<TR id=\"k2\">

					<TD align=\"center\" width=\"50%\"><div class=ctext>Venn Diagram type: </div></TD>
					<TD align=\"center\" width=\"50%\"><div class=dtext >
				    	<SELECT name=\"type\" id=\"value_d\">
							<OPTION value=\"1\">Venn Diagram</OPTION>
							<OPTION value=\"2\">Edwards-Venn Diagram</OPTION>
							<OPTION value=\"3\">-no diagram-</OPTION>

				 		</SELECT> &nbsp;</div>
				 	</TD>

				</TR>

				<TR id=\"k3\">

					<TD align=\"center\" width=\"50%\"><div class=ctext>Table - Hit species threshold: </div></TD>
					<TD align=\"center\" width=\"50%\"><div class=dtext >
				    	<SELECT name=\"threshold\" id=\"value_k\">

							<OPTION value=\"2\"".($threshold == 2 ? " selected" : "").">2 species</OPTION>
							<OPTION value=\"3\"".($threshold == 3 ? " selected" : "").">3 species</OPTION>
							<OPTION value=\"4\"".($threshold == 4 ? " selected" : "").">4 species</OPTION>
							<OPTION value=\"5\"".($threshold == 5 ? " selected" : "").">5 species</OPTION>
							<OPTION value=\"6\"".($threshold == 6 ? " selected" : "").">6 species</OPTION>
							<OPTION value=\"7\"".($threshold == 7 ? " selected" : "").">7 species</OPTION>

				 		</SELECT> &nbsp;</div>
				 	</TD>

				</TR>

				<TR id=\"k4\" style=\"display: none;\">

					<TD align=\"center\" width=\"50%\"><div class=ctext>Size Manual list: </div></TD>
					<TD align=\"center\" width=\"50%\"><div class=dtext >
				    	<INPUT type='checkbox' name='sizemanual'>
				 	</TD>

				</TR>

			    <TR>
			    	<TD align=\"center\" width=\"50%\" colspan=2>
			    		<div style=\"text-align: center;\">
			    			<INPUT type=\"submit\" name=\"sent\" value=\"  GO  \">
			    		</div>
			    	</TD>
			    </TR>
			</TABLE>
			</FORM>
			</div>";

		$strOutput .= "<div class=\"infobox\">\n";
		$strOutput .= $strInfoPostTable;
		$strOutput .= "</div>";

		return $strOutput;
	}

?>