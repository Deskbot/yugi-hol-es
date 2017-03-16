<?php
include 'php/lib.php';
$userBrowser = new UserBrowser();

if ($userBrowser->set_available_ids()) {
	if (!$userBrowser->ids_are_valid()) {
		throwAway('UserID');
		throwAway('SecretID');
	} else {
		$name = $userBrowser->get_name_from_db();
	}
}

?>
<?php include 'html/head.html';?>
<body>
	<?php include 'html/header.html';?>
	
	<h2>Add</h2>
	<div id="content">
		<form action="submission/add_submit.php" method="POST">
			<div>
				<input name="collector_name" placeholder="Your Name" type="text" value="<?=$name;?>">
			</div>
			<div>
				<input name="tag" placeholder="Print Tag (SDMM-EN004)" type="text">
			</div>
			<div>
				<span>Edition:&nbsp;</span>
				<select name="edition">
					<option value="1">1st</option>
					<option value="u">Unlimited</option>
					<option value="o">Other</option>
					<option title="This isn't wise for making sleeves." value="a">Any</option>
				</select>
			</div>
			<div>
				<input min="0" name="quantity" placeholder="Quantity Wanted" type="number">
				<input id="infinite_checkbox" name="infinite" type="checkbox">
				<label for="infinite_checkbox">Infinitely Many</label>
			</div>
			<div>
				<input name="note" placeholder="Additional Notes" type="text">
			</div>
			<div>
				<input type="submit">
			</div>
		</form>
		<noscript>Due to you not running Javascript, if the name you put in has been taken, you won't be notified and your request won't go through.</noscript>
	</div>
	<footer>
		<p>La seule langue qui cette site soutienne est l'anglais.</p>
		<p>Also if you delete your cookies, you'll no longer have access to the name you use and you won't be able to edit your stuff.</p>
		<p>If you want me to add in proper log in, I will if the site gets used enough.</p>
	</footer>
</body>

<!-- advert -->