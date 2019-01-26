$(document).ready(reportErrors);
$(document).ready(setKonamiCode);

function getUrlVars() {
    var vars = {};
    var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
        vars[key] = value;
    });
    return vars;
}

function reportErrors() {
	var _GET = getUrlVars();
	if (typeof _GET.error !== 'undefined') {
		alert(decodeURI(_GET.error));
	}
}

function setSessionExpiredAlert() {
	setTimeout(sessionExpired, 1430000);//23mins 50secs
}
function sessionExpired() {
	alert('Your session has expired. If you want to submit a form you may have to refresh the page first, or the submission will be considered invalid. If you have opened another page with a form submission after this one, you will have until that page times out instead.');
}

function makeElementAnchor(elem) {
    $(elem).click(function() {
		var win = window.open($(this).data('href'), '_blank');
		win.focus();
    });
}

function openInNewTab(url) {
  var win = window.open(url, '_blank');
  win.focus();
}