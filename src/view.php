<?php
//GET: requestID
include 'php/lib.php';

if (!isset($_GET['requestID'])) {
	redirect('index.php');
}

$userBrowser = new UserBrowser();
$userIsIdentified = $userBrowser->set_available_ids();
?>

<?php include 'html/head.html';?>

<body>
	<?php include 'html/header.html';?>
	
	<h2>View</h2>
	<div id="content">
		<h3 class="first">Request Info</h3>
		<table class="request-table" id="find-list">
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
			<?=getSingleRequest($_GET['requestID']);?>
		</table>
		
		<?=($userIsIdentified && $userBrowser->made_request($_GET['requestID'])) ? getViewControls($_GET['requestID']) : '';?>
		
		<h3>Make Pledge</h3>
		<form action="submission/pledge_submit.php" method="POST">
			<input name="name" placeholder="Your name" type="text" value="<?=$userIsIdentified ? $userBrowser->get_name_from_db() : '';?>">
			<input name="amountPledged" placeholder="Amount to Pledge" type="number">
			<input type="submit">
			<input name="requestID" type="hidden" value="<?=$_GET['requestID']?>">
		</form>
		
		<h3>Pledges</h3>
		<table class="pledge-table">
			<col></col><col></col><col></col><col></col>
			<tr class="head-row">
				<th rowspan="2">Pledger Name</th>
				<th rowspan="2">Pledge Date</th>
				<th rowspan="2" class="controls-head"></th>
				<th colspan="2" rowspan="1">Quantity</th>
			</tr>
			<tr class="head-row quantity-heads">
				<th rowspan="1">F</th>
				<th rowspan="1">P</th>
			</tr>
			<?=getRequestPledges($_GET['requestID'], $_GET['sortBy'], $_GET['order'], $_GET['startAt']);?>
		</table>
	</div>
</body>

<!-- advert -->