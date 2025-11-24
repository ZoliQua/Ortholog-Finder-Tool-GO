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

if( isset($_POST["sent"])) {

	// This run in case of there was a query.

	// Run Script for Query GO

		$objResults = new QueryGO($files, $folderSource, $species, $given_values, $gos);

	// EXPORT results in a SVG file

		$svgFileWriter = file_put_contents($files["output"], $objResults->printelni);

	// Add Download button to under the SVG graphics

		$strInfos = "<BR>\n<A href=\"".$files["output"]."\" target=\"_blank\">Download Venn Diagram</A> in SVG file.";

	// Get the results for print

		$strOutput = PrintTheOutput($objResults->printelni, $strInfos, $objResults->strTablePrint, $objResults->infos, $objResults->species_number, $this_file, $go, $gos, $species);
	// Pritn

		print $strOutput;

}

else {

	// in Case there were NO query taken.

	$strOutput = PrintTheOutput(false, false, false, false, false, $this_file, $go, $gos, $species);

	print $strOutput;

}


// End Time Write Out

	print "<BR><BR><div class=\"exec-time\">\n";
	print TimeEnd($time_start);
	print "</div>";

?>

<!--  DATATABLE JAVASCRIPT - START //-->

	<script type="text/javascript">

		$(document).ready(function() {
			
		    $('#results').DataTable( {

		        "bProcessing": true,
		   		"bJQueryUI": true,
       			"order": [[ 1, "desc" ]],
       			"lengthMenu": [[10, 20, 40, 80, -1], [10, 20, 40, 80, "All"]]

    		} );

		} );


		$(document).ready(function() {
			
		    $('#resultsexpanded').DataTable( {

		        "bProcessing": true,
		   		"bJQueryUI": true,
       			"order": [[ 1, "desc" ]],
       			"lengthMenu": [[10, 20, 40, 80, -1], [10, 20, 40, 80, "All"]]

    		} );

		} );


		$(document).ready(function() {
			
		    $('#conservedcore').DataTable( {

		        "bProcessing": true,
		   		"bJQueryUI": true,
       			"order": [[ 1, "desc" ]],
       			"lengthMenu": [[10, 20, 40, 80, -1], [10, 20, 40, 80, "All"]]

    		} );

		} );



		$(document).ready(function() {
			
		    $('#novelannotations').DataTable( {

		        "bProcessing": true,
		   		"bJQueryUI": true,
       			"order": [[ 1, "desc" ]],
       			"lengthMenu": [[10, 20, 40, 80, -1], [10, 20, 40, 80, "All"]]

    		} );

		} );

	</script>

<!--  DATATABLE JAVASCRIPT - END //-->
