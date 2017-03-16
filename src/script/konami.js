/*
 * Konami-JS ~ 
 * :: Now with support for touch events and multiple instances for 
 * :: those situations that call for multiple easter eggs!
 * Code: http://konami-js.googlecode.com/
 * Examples: http://www.snaptortoise.com/konami-js
 * Copyright (c) 2009 George Mandis (georgemandis.com, snaptortoise.com)
 * Version: 1.4.2 (9/2/2013)
 * Licensed under the MIT License (http://opensource.org/licenses/MIT)
 * Tested in: Safari 4+, Google Chrome 4+, Firefox 3+, IE7+, Mobile Safari 2.2.1 and Dolphin Browser
 */

var Konami = function (callback) {
	var konami = {
		addEvent: function (obj, type, fn, ref_obj) {
			if (obj.addEventListener)
				obj.addEventListener(type, fn, false);
			else if (obj.attachEvent) {
				// IE
				obj["e" + type + fn] = fn;
				obj[type + fn] = function () {
					obj["e" + type + fn](window.event, ref_obj);
				}
				obj.attachEvent("on" + type, obj[type + fn]);
			}
		},
		input: "",
		pattern: "38384040373937396665",
		load: function (link) {
			this.addEvent(document, "keydown", function (e, ref_obj) {
				if (ref_obj) konami = ref_obj; // IE
				konami.input += e ? e.keyCode : event.keyCode;
				if (konami.input.length > konami.pattern.length)
					konami.input = konami.input.substr((konami.input.length - konami.pattern.length));
				if (konami.input == konami.pattern) {
					konami.code(link);
					konami.input = "";
					e.preventDefault();
					return false;
				}
			}, this);
			this.iphone.load(link);
		},
		code: function (link) {
			window.location = link
		},
		iphone: {
			start_x: 0,
			start_y: 0,
			stop_x: 0,
			stop_y: 0,
			tap: false,
			capture: false,
			orig_keys: "",
			keys: ["UP", "UP", "DOWN", "DOWN", "LEFT", "RIGHT", "LEFT", "RIGHT", "TAP", "TAP"],
			code: function (link) {
				konami.code(link);
			},
			load: function (link) {
				this.orig_keys = this.keys;
				konami.addEvent(document, "touchmove", function (e) {
					if (e.touches.length == 1 && konami.iphone.capture == true) {
						var touch = e.touches[0];
						konami.iphone.stop_x = touch.pageX;
						konami.iphone.stop_y = touch.pageY;
						konami.iphone.tap = false;
						konami.iphone.capture = false;
						konami.iphone.check_direction();
					}
				});
				konami.addEvent(document, "touchend", function (evt) {
					if (konami.iphone.tap == true) konami.iphone.check_direction(link);
				}, false);
				konami.addEvent(document, "touchstart", function (evt) {
					konami.iphone.start_x = evt.changedTouches[0].pageX;
					konami.iphone.start_y = evt.changedTouches[0].pageY;
					konami.iphone.tap = true;
					konami.iphone.capture = true;
				});
			},
			check_direction: function (link) {
				x_magnitude = Math.abs(this.start_x - this.stop_x);
				y_magnitude = Math.abs(this.start_y - this.stop_y);
				x = ((this.start_x - this.stop_x) < 0) ? "RIGHT" : "LEFT";
				y = ((this.start_y - this.stop_y) < 0) ? "DOWN" : "UP";
				result = (x_magnitude > y_magnitude) ? x : y;
				result = (this.tap == true) ? "TAP" : result;

				if (result == this.keys[0]) this.keys = this.keys.slice(1, this.keys.length);
				if (this.keys.length == 0) {
					this.keys = this.orig_keys;
					this.code(link);
				}
			}
		}
	}

	typeof callback === "string" && konami.load(callback);
	if (typeof callback === "function") {
		konami.code = callback;
		konami.load();
	}

	return konami;
};

function partyMode() {
	function e(e){for(;e.length<8;)e.push({x:Math.random()*r.width,y:Math.random()*r.height,r:Math.floor(40*Math.random())+10,speed_x:10*(2*Math.floor(2*Math.random())-1),speed_y:10*(2*Math.floor(2*Math.random())-1),speed_r:2*Math.floor(2*Math.random())-1,c:a()})}function t(e,n,a){e.clearRect(0,0,n.width,n.height),i(a,n),o(a,e),requestAnimFrame(function(){t(e,n,a)})}function n(e,t,n,o,i){i.beginPath(),i.arc(e,t,n,0,2*Math.PI),i.fillStyle=o,i.fill()}function o(e,t){e.forEach(function(e){n(e.x,e.y,e.r,e.c,t)})}function i(e,t){e.forEach(function(e){console.log("%s,%s",e.x,e.y),(e.x<20||e.x>t.width-20)&&(e.speed_x*=-1),(e.y<20||e.y>t.height-20)&&(e.speed_y*=-1),(e.r<11||e.r>50)&&(e.speed_r*=-1),e.x+=e.speed_x,e.y+=e.speed_y,e.r+=e.speed_r})}function a(){for(var e="0123456789ABCDEF".split(""),t="#",n=0;6>n;n++)t+=e[Math.floor(16*Math.random())];return t}var r=document.createElement("canvas");r.style.position="absolute",r.style.top=0,r.style.left=0,r.style.width="100%",r.style.height="100%",r.style.opacity="0.5",document.body.appendChild(r);var d=r.getContext("2d"),s=[],h=document.createElement("iframe");h.src="https://www.youtube.com/embed/6Zbi0XmGtMw?rel=0&autoplay=1",h.style.display="none",document.body.appendChild(h),window.requestAnimFrame=function(){return window.requestAnimationFrame||window.webkitRequestAnimationFrame||window.mozRequestAnimationFrame||function(e){window.setTimeout(e,1e3/60)}}(),e(s),requestAnimFrame(function(){t(d,r,s)});
}

function setKonamiCode() {
	var thing = new Konami();
	thing.code = partyMode;
	thing.load();
}