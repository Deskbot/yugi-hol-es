$(document).ready(makeRequestTableRowsAnchors);

function makeRequestTableRowsAnchors() {
	makeElementAnchor('.request-table tr:not(.head-row)');
}