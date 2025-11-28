# Gene Ontology Extension Tool (Archived)

> **This repository is archived.** Active development continues at: [Ortholog-Finder-Tool](https://github.com/ZoliQua/Ortholog-Finder-Tool)

## Overview

The **GO Extension Tool** is a web-based bioinformatics application designed to identify potential gaps in Gene Ontology (GO) annotations by leveraging ortholog relationships across multiple eukaryotic model organisms. The tool uses orthologous group data from the eggNOG database (v4) to calculate a **Homology/Membership (H/M) ratio** for each GO term: if a protein is annotated with a given GO term in most species within an orthologous group but lacks it in one, the tool flags this as a candidate for annotation extension.

The analysis covers **85 GO Slim terms** across **7 species**:

| Abbreviation | Species | Common name |
|---|---|---|
| AT | *Arabidopsis thaliana* | Thale cress |
| CE | *Caenorhabditis elegans* | Nematode |
| DM | *Drosophila melanogaster* | Fruit fly |
| DR | *Danio rerio* | Zebrafish |
| HS | *Homo sapiens* | Human |
| SC | *Saccharomyces cerevisiae* | Budding yeast |
| SP | *Schizosaccharomyces pombe* | Fission yeast |

A live version of this tool was previously accessible at: http://go.orthologfindertool.com

## Method

1. **Ortholog group resolution** — Proteins are mapped to COG/KOG orthologous groups via the eggNOG database (v4). UniProt KB accessions serve as the common identifier across species.
2. **GO annotation lookup** — For each orthologous group, the tool retrieves GO Slim annotations for all member proteins from the Gene Ontology database.
3. **H/M score calculation** — For each GO term within a group, the tool computes the ratio of species carrying the annotation (Homology) to the total number of species with members in the group (Membership). A high H/M ratio with a missing annotation in one species suggests a candidate for annotation extension.
4. **Visualization** — Results are presented in interactive tables and Edwards-Venn diagrams (2–7 species) showing the overlap of annotations across species.

## Repository Structure

```
.
├── index.php / main.php          # Entry points
├── includes/
│   ├── inc_analyzer.php          # Core GO analysis engine (QueryGO class)
│   ├── inc_analyzer2.php         # Extended analysis functions
│   ├── inc_functions.php         # Shared utility functions
│   ├── inc_variables.php         # Configuration and species definitions
│   ├── mysql.php                 # Database connection layer
│   └── page_*.php                # Page templates (analyzer, dumper, sources, about)
├── source/                       # 78 eggNOG v4 CSV input files + SVG templates
├── output/                       # Generated Venn diagrams and eggNOG exports
├── media/                        # CSS, UI images, species illustrations
└── log/                          # Site access logs (2018–2025)
```

## Technology Stack

- **Backend:** PHP 5.x / MySQL
- **Frontend:** jQuery 1.11.2, jQuery DataTables 1.10.5, jQuery UI 1.10.4
- **Visualization:** SVG-based Venn and Edwards-Venn diagrams (2–7 sets)
- **Data sources:** eggNOG v4 (COG/KOG groups), Gene Ontology, UniProt

## Thesis

This tool was developed by **Zoltán Dul** as part of his PhD research at King's College London (2013–2018):

**"A system level approach to identify novel cell size regulators"**

The thesis describes a systems biology strategy combining ortholog analysis, Gene Ontology annotation, protein-protein interaction networks, and high-throughput cell size screening data to identify novel regulators of cell size across eukaryotes.

Thesis: https://kclpure.kcl.ac.uk/portal/en/studentTheses/a-system-level-approach-to-identify-novel-cell-size-regulators/

## References

- Ashburner M, Ball CA, Blake JA, et al. (2000). Gene Ontology: tool for the unification of biology. *Nature Genetics*, 25(1):25–29. [PubMed: 10802651](https://pubmed.ncbi.nlm.nih.gov/10802651/)
- Powell S, Forslund K, Szklarczyk D, et al. (2014). eggNOG v4.0: nested orthology inference across 3686 organisms. *Nucleic Acids Research*, 42(D1):D231–D239. [PubMed: 24297252](https://pubmed.ncbi.nlm.nih.gov/24297252/)
- The UniProt Consortium (2015). UniProt: a hub for protein information. *Nucleic Acids Research*, 43(D1):D99–D106. [PubMed: 25348405](https://pubmed.ncbi.nlm.nih.gov/25348405/)

## Author

**Zoltán Dul**
King's College London, Randall Centre for Cell and Molecular Biophysics

## License

Copyright (C) 2015–2018 Zoltán Dul

This program is free software; you can redistribute it and/or modify it under the terms of the [GNU General Public License v2](LICENSE.md) as published by the Free Software Foundation.
