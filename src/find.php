<?php
//GET: completed, sortBy, order, startAt
include 'php/lib.php';
?>

<?php include 'html/head.html';?>
<body>
	<?php include 'html/header.html';?>
	
	<h2>Find</h2>
	<div id="content">
		<table class="request-table multi-line" id="find-list">
			<tr class="head-row">
				<th rowspan="2">Collector</th>
				<th rowspan="2">Card Name</th>
				<th rowspan="2">Set</th>
				<th rowspan="2">Edition</th>
				<th class="th-head" colspan="3" rowspan="1">Quantity</th>
			</tr>
			<tr class="head-row quantity-heads">
				<th title="Fulfilled">F</th>
                <th title="Pledged">P</th>
                <th title="Wanted">W</th>
			</tr>
			<?=getCurrentRequests($_GET['completed'],$_GET['sortBy'],$_GET['order'],$_GET['startAt']);?>
		</table>
	</div>
</body>

<!-- advert -->