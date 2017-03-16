$(document).ready(function() {
	$('#changeRequestButton').click(showEditRequest);
	$('#deleteRequestForm').submit(confirmDelete);
	$('#changeRequestForm').submit(confirmChange);
});

//functions
function showEditRequest() {
	$('#changeRequestForm').toggleClass('visible');
}

function confirmDelete() {
	return confirm('You are about to delete this request. Are you ok with that?')
}

function confirmChange() {
	return confirm('You are about to change this request. Are you ok with that?');
}