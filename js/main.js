var toast = function(msg) {
	$("<div class='ui-loader ui-overlay-shadow ui-body-e ui-corner-all'><h3>" + msg + "</h3></div>").css({
		display : "block",
		opacity : 0.90,
		position : "fixed",
		padding : "7px",
		"text-align" : "center",
		width : "270px",
		left : ($(window).width() - 284) / 2,
		top : $(window).height() / 2
	}).addClass("ui-bar-a").appendTo($.mobile.pageContainer).delay(1500).fadeOut(400, function() {
		$(this).remove();
	});
};

function onBodyLoad() {
	$("#login_form").on("submit", function(e) {
		e.preventDefault();
		//        $("#submitButton", this).attr("disabled", "disabled");
		var p = formhash(document.forms['login_form'], document.forms['login_form'].password);
		var u = $("#mail").val();
		console.log(p);

		$.post("loginscr.php", {
			username : u,
			password : p
		}, function(res) {
			if (res == "success") {
				$.mobile.pageContainer.pagecontainer("change", "#editSession", {
					transition : "flip"
				});
			} else {
				if (res == "nActive") {
					toast("Your account is not yet activated. Please check your email.");
				} else {
					toast("Login failed. Please check your account information");
				}
				console.log(res);
				//                navigator.notification.alert("Your login failed", function() {
				//                });
			}
		});
	});
	$("#registerForm").on("submit", function(e) {
		e.preventDefault();
		console.log(e);
		console.log($('#regPassword').val());
		console.log(document.forms['registerForm'].password);
		console.log(checkStrength(document.forms['registerForm'].password.value, $('#regPassword')));
		if (checkStrength($('#regPassword').val(), $('#regPassword')) < 2) {
			toast("Password to weak.");
		} else {

			var p = formhash(document.forms['registerForm'], document.forms['registerForm'].password);
			var u = $("#regUser").val();
			var e = $("#regEmail").val();
			console.log(p);
			console.log(u);
			console.log(e);
			$.ajax({
				url : 'register.php',
				data : {
					username : u,
					password : p,
					email : e
				},
				dataType : 'json',
			}).then(function(res) {
				console.log(res);
				if (res == "registered") {
					toast("Activation email sent, please check your email.");
					$.mobile.pageContainer.pagecontainer("change", "index.php", {
					transition : "flip"
					});
				} else {
					console.log(res);
					toast(res);
					var out = "";
					$.each(res, function(i, obj) {
						out += obj;
					});
					toast(out);
				}
			});

		}

	});
	$('#regPassword').keyup(function() {
		checkStrength($('#regPassword').val(), $('#regPassword'));
	});

	/*
	 checkStrength is function which will do the
	 main password strength checking for us
	 */

	function checkStrength(password, input) {
		//initial strength
		var strength = 0;

		//if the password length is less than 6, return message.
		if (password.length < 6) {
			input.removeClass();
			input.addClass('short');
			return strength;
		}

		//length is ok, lets continue.

		//if length is 8 characters or more, increase strength value
		if (password.length > 7)
			strength += 1

		//if password contains both lower and uppercase characters, increase strength value
		if (password.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/))
			strength += 1

		//if it has numbers and characters, increase strength value
		if (password.match(/([a-zA-Z])/) && password.match(/([0-9])/))
			strength += 1

		//if it has one special character, increase strength value
		if (password.match(/([!,%,&,@,#,$,^,*,?,_,~])/))
			strength += 1

		//if it has two special characters, increase strength value
		if (password.match(/(.*[!,%,&,@,#,$,^,*,?,_,~].*[!,%,&,@,#,$,^,*,?,_,~])/))
			strength += 1

		//now we have calculated strength value, we can return messages
console.log(strength);
		//if value is less than 2
		if (strength < 2) {
			input.removeClass();
			input.addClass('weak');
			return strength;
		} else if (strength == 2) {
			input.removeClass();
			input.addClass('good');
			return strength;
		} else {
			input.removeClass();
			input.addClass('strong');
			return strength;
		}
	}

	function formhash(form, password) {
		// Create a new element input, this will be our hashed password field.
		var p = document.createElement("input");
		// Add the new element to our form.
		form.appendChild(p);
		p.name = "p";
		p.type = "hidden";
		var passw = hex_sha512(password.value);
		// Make sure the plaintext password doesn't get sent.
		password.value = "";
		// Finally submit the form.
		return passw;
	}

}


$(document).ready(function() {
	var timerTime = 60, nextPage, prevPage, toAllButton = $("input[name='toAll']");
	var toDayButton = $("input[name='toDay']");
	var fnameField = $("input[name='fname']");
	var lnameField = $("input[name='lname']");
	var all = $("select[name='all']");
	var day = $("select[name='day']");
	var id;
	var matchKnapp = $("input[name='matchning']");
	var matchDiv = $("#matchResults");
	var resting = $("#restList");
	var reset = $("#resetButton");
	var resultButtonDiv = $("#resultButtonDiv");
	var resultButton;
	var toRest = $("input[name='toRest']");
	var fromRest = $("input[name='fromRest']");
	var restBox = $("select[name='rest']");
	var sortByNr = $("#sortByNum");
	var sortByFname = $("#sortByFname");
	var winnerArrayCss = new Array();
	var expResult = $("#expResult");
	var populData;
	var dayList = $("#dayList");
	var resultButton = $("#resultButton");
	//    resultButton.button();
	var loadedSession = 0;
	var newSessionSet;
	var brandNewSession;
	var sessionSet;
	var fromMatching = false;

	var loadEditPlayerBN = function(id) {
		var error = new Boolean();
		$.ajax({
			url : 'get_player.php',
			data : {
				id : id,
				BN : "true"
			},
			dataType : 'json',
			success : function(data) {
				$("#editPlayerBN").enhanceWithin();
				$("#idBNChange").val(id);
				$("#editPlayerBN input[type=radio]").prop("checked", false).checkboxradio("refresh", true);
				//currentPlayer = data[0];
				$("#edBNinfname").val(data[1]);
				$("#edBNinlname").val(data[2]);
				if (data[3] == "F") {
					$("#edBNinsexF").prop("checked", true).checkboxradio("refresh");
				}
				if (data[3] == "M") {
					$("#edBNinsexM").prop("checked", true).checkboxradio("refresh");
				}
				$("#edBNboardNum").val(data[6]);
				console.log(data);

				//            $("#edinWins").val(data[4]);
				//            $("#edinRests").val(data[7]);
				//            $("#edinPlayed").val(data[6]);
				//
				//            if (data[5] == "Y") {
				//                $("#edinmemberY").prop("checked", true).checkboxradio("refresh");
				//            }
				//            if (data[5] == "N") {
				//                $("#edinmemberN").prop("checked", true).checkboxradio("refresh");
				//            }
				$.ajax({
					url : "populList.php",
					dataType : 'json',
					data : {
						action : "freeBN"
					},
				}).done(function(response) {
					var freeBNs = new Array();
					$.each(response[0], function(i, val) {
						freeBNs.push(val);
					});
					localStorage["freeBNs"] = JSON.stringify(freeBNs);
					localStorage["maxBN"] = response[1];

					$("#edBNboardNum").each(function() {
						var elem = $(this);

						// Save current value of element
						elem.data('oldVal', elem.val());

						// Look for changes in the value
						elem.unbind();
						elem.bind("propertychange keyup input paste", function(event) {
							// If value has changed...
							if (elem.data('oldVal') != elem.val()) {
								// Updated stored value
								elem.data('oldVal', elem.val());

								// Do action
								error = true;
								$.each(freeBNs, function(i, val) {
									if (elem.val() == val) {
										error = false;
									}
								});
								if (elem.val() >= response[1] || elem.val() == data[6]) {
									error = false;
								}

								if (!error) {
									//grönt
									elem.css("color", "#01DF01");
								} else {
									//rött
									elem.css("color", "#FE2E2E");
								}
							}
						});
					});
					$("#changePlayerBNButton").off('click').on("click", function(e) {
						e.preventDefault();
						if ($("#edBNboardNum").val() == data[6]) {
							error = false;
						}

						if (error) {
							toast("Boardnumber occupied");
						} else if (!error) {
							$("#edBNboardNum").css("color", "black");
							toast("Changes applied");
							changePlayerBN();
							$("#editPlayerBN").popup("close");
						}
					});
					$("#editPlayerBN").popup({
						create : function(event, ui) {
							$("#editPlayerBN").enhanceWithin();
							console.log("triggeredz2");
						}
					});

					$('#editPlayerBN').on({
						popupafterclose : function() {
							$("#edBNboardNum").css("color", "black");
						}
					});

					$("#editPlayerBN").popup("open");
					//    $.mobile.changePage("#editPlayer", {role: "dialog"});

				});
			}
		});

	};
	var changePlayerBN = function() {
		var idChange = $("#idBNChange").val();
		var fname = $("#edBNinfname").val();
		var lname = $("#edBNinlname").val();
		var sex = $("input[name='edBNinsex']:checked").val();
		var BN = $("#edBNboardNum").val();
		$.ajax({
			url : 'changePlayer.php',
			data : {
				id : idChange,
				fname : fname,
				lname : lname,
				sex : sex,
				BN : BN,
				BoN : true
			},
			dataType : 'text',
			success : function(data) {
				populAll();

			}
		});
	};
	// -------------------- ADMINSIDAN ------------------//
	//Här finns alla funktioner bundna till objekt bara visade på adminsidan
	//------------------------------------------------------//
	$("#logoutButton").click(function(e) {
		e.preventDefault();
		$.get('logout.php', {}, function(data) {
			$.mobile.pageContainer.pagecontainer("change", "#login", {
				transition : "flip"
			});
		});
	});

	//------------------------------------------------------//

	// -------------------- PREPSIDAN ------------------//
	//Här finns alla funktioner bundna till objekt bara visade på prepsidan
	//------------------------------------------------------//
	$("#makePair").on("click", function(e) {
		e.preventDefault();
		$.mobile.pageContainer.pagecontainer("change", "#customPairs", {
			transition : "flip"
		});
	});

	$(document).on('pagebeforeshow', '#customPairs', function() {
		var courts;
		$.ajax({
			url : 'settings.php',
			data : {
				getset : "get",
				courtRound : "yes"
			},
			dataType : 'json'
		}).then(function(response) {
			courts = response[0];
			localStorage.setItem("round", response[1]);
			$("#numberOfGames").text('');
			for (var i = 0; i < courts; i++) {
				var j = i + 1;
				$("#numberOfGames").append("<li data-theme='a'><a href='#' id='" + j + "'>Court " + j + "</a></li>");
			}
			$(document).off("click", "#numberOfGames a").on("click", "#numberOfGames a", function(e) {
				e.preventDefault();
				changePairContent($(this).attr("id"));
			});

			$("#numberOfGames").listview();
			$("#numberOfGames").listview("refresh");
		});

	});
	$("#playModeI").on("click", function(e) {
		e.preventDefault();
		$("#gameInfo").popup("open");
	});
	//------------------------------------------------------//

	var changePairContent = function(id) {
		//        alert(id);
		// $("#pairContent").text(id);
		$("#pairContentLeft").text("");
		$("#pairContentRight").text("");
		$("#pairContentLeft").append('<ul class="choosePlayers team1" data-role="listview" data-inset="true" data-filter="true" data-filter-placeholder="Choose player" data-filter-theme="a"></ul>');
		$("#pairContentRight").append('<ul class="choosePlayers team2" data-role="listview" data-inset="true" data-filter="true" data-filter-placeholder="Choose player" data-filter-theme="a"></ul>');
		$("#pairContentLeft").append('<ul data-role="listview" id="team1"></ul>');
		$("#pairContentRight").append('<ul data-role="listview" id="team2"></ul>');
		//REQUEST PLAYERS AJAX
		localStorage.setItem("courtChoose", id);
		$.ajax({
			url : "choosePairs.php",
			dataType : 'json',
			data : {
				mode : "checkRound",
				round : localStorage.getItem("round"),
				court : id
			}
		}).then(function(response) {
			if (response == "emptyRound") {
				$("#team1").append('<li id="t1p1"><span id="0">Player 1</span></li>');
				$("#team1").append('<li id="t1p2"><span id="0">Player 2</span></li>');
				$("#team2").append('<li id="t2p1"><span id="0">Player 1</span></li>');
				$("#team2").append('<li id="t2p2"><span id="0">Player 2</span></li>');
				$("#pairContent").enhanceWithin();
			} else {
				var i = 0;
				response.forEach(function(pairs) {
					if (pairs == "emptyRound") {
						if (i == 0) {
							$("#team1").append('<li id="t1p1"><span id="0">Player 1</span></li>');
							$("#team1").append('<li id="t1p2"><span id="0">Player 2</span></li>');
						} else {
							$("#team2").append('<li id="t2p1"><span id="0">Player 1</span></li>');
							$("#team2").append('<li id="t2p2"><span id="0">Player 2</span></li>');
						}
					} else {
						pairs.forEach(function(pairObj) {
							if (pairObj.player1 == 0 || pairObj.player1 == null) {
								$("#team" + pairObj.team).append('<li id="t' + pairObj.team + 'p1"><span id="' + pairObj.player1 + '">' + 'Player 1' + '</li>');
							} else {
								$("#team" + pairObj.team).append('<li id="t' + pairObj.team + 'p1"><span id="' + pairObj.player1 + '">' + pairObj.player1Name + '</li>');
							}
							if (pairObj.player2 == 0 || pairObj.player2 == null) {
								$("#team" + pairObj.team).append('<li id="t' + pairObj.team + 'p2"><span id="' + pairObj.player2 + '">' + "Player 2" + '</li>');
							} else {
								$("#team" + pairObj.team).append('<li id="t' + pairObj.team + 'p2"><span id="' + pairObj.player2 + '">' + pairObj.player2Name + '</li>');
							}
						});
					}
					i++;
				});
				$("#pairContent").enhanceWithin();
			}
		});
		$(document).on("listviewbeforefilter", ".choosePlayers", function(e, data) {
			var team;
			if ($(this).hasClass("team2")) {
				team = "2";
			} else {
				team = "1";
			}
			var $ul = $(this), $input = $(data.input), value = $input.val(), html = "";
			if (value && value.length > 2) {
				$ul.html("<li><div class='ui-loader'><span class='ui-icon ui-icon-loading'></span></div></li>");
				$ul.listview("refresh");
				$.ajax({
					url : "populList.php",
					dataType : 'json',
					data : {
						action : "filter",
						q : $input.val(),
						mode : 'mPairs',
						round : localStorage.getItem("round")
					},
				}).done(function(response) {
					$.each(response, function(i, val) {
						html += "<li><a href='#' id='player" + val[0] + "' class='playerListLink team" + team + "'>" + val[1] + "</a></li>";
					});
					$ul.html(html);
					$ul.listview("refresh");
					$ul.trigger("change");
					$("#pairContent").enhanceWithin();
				});
			} else {
				$ul.html("");
				$ul.listview("refresh");
				$ul.trigger("change");
			}
		});
		$(document).off("click", ".choosePlayers .playerListLink").on("click", ".choosePlayers .playerListLink", function(e) {
			e.preventDefault();
			var team;
			if ($(this).hasClass("team2")) {
				team = "2";
			} else {
				team = "1";
			}
			if ($(this).text() != null) {
				var nogo = false;
				var chosenID = $(this).attr("id");
				chosenID = chosenID.replace("player", "");
				var court = localStorage.getItem("courtChoose");
				var p1, p2;
				if ($("#t" + team + "p1 span#0").length && !$("#t" + team + "p2 span#0").length) {
					p2 = $("#t" + team + "p2 span").attr("id");
					p1 = chosenID;
				} else if (!$("#t" + team + "p1 span#0").length && $("#t" + team + "p2 span#0").length) {
					p1 = $("#t" + team + "p1 span").attr("id");
					p2 = chosenID;
				} else if ($("#t" + team + "p1 span#0").length && $("#t" + team + "p2 span#0").length) {
					p2 = 0;
					p1 = chosenID;
				} else {
					toast("Team full");
					nogo = true;
				}
				if (!nogo) {
					$.ajax({
						url : "choosePairs.php",
						dataType : 'json',
						data : {
							mode : "insertPair",
							round : localStorage.getItem("round"),
							court : court,
							p1 : p1,
							p2 : p2,
							team : team
						}
					}).then(function(response) {
						$("#team1").text("");
						$("#team2").text("");
						var i = 0;
						response.forEach(function(pairs) {
							if (pairs == "emptyRound") {
								if (i == 0) {
									$("#team1").append('<li id="t1p1"><span id="0">Player 1</span></li>');
									$("#team1").append('<li id="t1p2"><span id="0">Player 2</span></li>');
								} else {
									$("#team2").append('<li id="t2p1"><span id="0">Player 1</span></li>');
									$("#team2").append('<li id="t2p2"><span id="0">Player 2</span></li>');
								}
							} else {
								pairs.forEach(function(pairObj) {
									if (pairObj.player1 == 0 || pairObj.player1 == null) {
										$("#team" + pairObj.team).append('<li id="t' + pairObj.team + 'p1"><span id="' + pairObj.player1 + '">' + 'Player 1' + '</li>');
									} else {
										$("#team" + pairObj.team).append('<li id="t' + pairObj.team + 'p1"><span id="' + pairObj.player1 + '">' + pairObj.player1Name + '</li>');
									}
									if (pairObj.player2 == 0 || pairObj.player2 == null) {
										$("#team" + pairObj.team).append('<li id="t' + pairObj.team + 'p2"><span id="' + pairObj.player2 + '">' + "Player 2" + '</li>');
									} else {
										$("#team" + pairObj.team).append('<li id="t' + pairObj.team + 'p2"><span id="' + pairObj.player2 + '">' + pairObj.player2Name + '</li>');
									}
								});
							}
							i++;
						});
						$("#team1").listview('refresh');
						$("#team2").listview('refresh');
						$("#pairContent").enhanceWithin();
					});

					$('input[data-type="search"]').val("");
					$('input[data-type="search"]').trigger("change");

				}
			}
		});
	};

	// -------------------- MATCHSIDAN ------------------//
	//Här finns alla funktioner bundna till objekt bara visade på matchsidan
	//------------------------------------------------------//

	$('#matcher').on('pageshow ', function() {

		//

	});
	$("#timeButtonRestart").on("click", function(e) {
		e.preventDefault();
		e.stopPropagation();
		$(".timeExample").data('timer', timerTime);
		$(".timeExample").TimeCircles({
			"circle_bg_color" : "#EEEEEE",
			time : {
				Days : {
					show : false
				},
				Hours : {
					show : false
				},
				Minutes : {
					color : '#CCCCCC'
				},
				Seconds : {
					color : '#CCCCCC'
				}
			}
		});
		//        $(".timeExample").TimeCircles().rebuild();
		$(".timeExample").TimeCircles().restart();
	});
	$("#timeButtonStart").on("click", function(e) {
		e.preventDefault();
		e.stopPropagation();
		$(".timeExample").TimeCircles().start();
	});
	$("#timeButtonPause").on("click", function(e) {
		e.preventDefault();
		e.stopPropagation();
		$(".timeExample").TimeCircles().stop();

	});
	var loadMatches = function() {
		$.ajax({
			url : "loadMatches.php",
			dataType : 'json',
			data : {},
			success : function(data1) {

				if (data1.length > 0) {
					$.post('okToMatch.php', {}, function(data) {

						data = data.trim();
						if (data == "true") {
							writeMatches(data1);
						} else if (data == "false") {
							writeMatches(data1, "false");
						}
					});
				}
			}
		});
	};
	var roundResults = function() {
		$("#playedRounds").empty();
		$.ajax({
			url : "getRounds.php",
			dataType : 'json',
			data : {},
			success : function(data) {
				if ( typeof data[1] == "object" && data[1][0][0] != null) {
					$.each(data[1], function(index, val) {
						//  SKRIV UT BOXEN FÖR RONDEN
						$("#playedRounds").append('<div id="round' + index + '" data-role="collapsible" data-theme="a" data-content-theme="a"></div>');
						var rondNum = index + 1;
						$("#round" + index).append('<h3>Round ' + rondNum + '</h3>');
						// SKRIV UT ETT DRAGSPEL MED MATCHER + VILANDE
						$("#round" + index).append('<div id="collaps' + index + '" data-role="collapsible-set"></div>');
						//SRIV UT MATCHERNA
						$.each(val, function(index2, val2) {
							$("#collaps" + index).append('<div id="round' + index + 'match' + index2 + '" data-role="collapsible" data-theme="a" data-content-theme="a"></div>');
							$("#round" + index + 'match' + index2).append('<h3>' + val2[1].substr(0, val2[1].indexOf(' ')) + " - " + val2[2].substr(0, val2[2].indexOf(' ')) + " vs " + val2[3].substr(0, val2[3].indexOf(' ')) + " - " + val2[4].substr(0, val2[4].indexOf(' ')) + '</h3>');
							$("#round" + index + 'match' + index2).append('<p class="matchText">' + val2[1] + " - " + val2[2] + " <br /> vs<br/> " + val2[3] + " - " + val2[4] + '</p>');
						});
						//SKRIV UT VILANDE
						var restInd = index + 1;
						if ( typeof data[0][restInd] == "object" && data[0][restInd] != null) {
							$("#collaps" + index).append('<div id="round' + index + 'rest" data-role="collapsible" data-theme="a" data-content-theme="a"></div>');
							$("#round" + index + "rest").append("<h3>Resters</h3>");
							$("#round" + index + "rest").append("<ul id='restUL" + index + "' data-theme='a' data-role='listview'></ul>");
							$.each(data[0][restInd], function(indexRest, valRest) {
								if (indexRest % 2 == 0) {
									if (data[0][restInd][indexRest + 1] == 'Y') {
										$("#restUL" + index).append("<li data-theme='c'>" + valRest + "</li>");
									} else {
										$("#restUL" + index).append("<li data-theme='a'>" + valRest + "</li>");
									}
								}
							});
						}
						//SLUT PÅ SKRIVA UT VILANDE SPELARE

						//UPPDATERA UTSEENDET MED DE NYA GREJERNA
						$("#round" + index).collapsibleset();
						$("#round" + index).collapsibleset("refresh");
						$("#playedRounds").trigger('create');
					});
				} else {
					console.log(data);
				}

			}
		});
	};
	//------------------------------------------------------//

	//-----------------------FOOTER + SESSION --------------------------//
	//
	//
	$(document).on("click", ".sessionDisp", function(e) {
		e.preventDefault();
		//        e.stopPropagation();
		$.mobile.pageContainer.pagecontainer("change", "#editSession", {
			transition : "flip"
		});
	});

	$("#editSessionLink").on("click", function(e) {
		e.preventDefault();
	});

	$(document).on('pagebeforeshow', '#editSession', function() {
		$("#oldSessions").empty();
		$.ajax({
			url : "getSetSession.php",
			dataType : 'json',
			data : {
				getset : 'getAll'
			}
		}).then(function(data) {
			$("#oldSessions").append('<div id="sessionsShow" data-role="collapsible-set" data-theme="a" data-content-theme="a"></div>');
			$.each(data, function(period, sessions) {
				$("#sessionsShow").append('<div id="period' + period + '" data-role="collapsible" data-theme="a" data-content-theme="a"></div>');
				$("#period" + period).append('<h3>' + period + '</h3>');
				$.each(sessions, function(key, session) {
					$("#period" + period).append('<a href="#" data-role="button" id="' + session + '">' + session + '</a>');
				});
			});
			$("#sessionsShow").collapsibleset();
			$("#sessionsShow").collapsibleset("refresh");
			$("#oldSessions").trigger("create");
		});

	});

	$(document).on("click", '#sessionsShow a', function(e) {
		e.preventDefault();
		//            e.stopPropagation();
		brandNewSession = true;
		chooseSession(e.currentTarget.id);

	});

	$('#newSessionLink').on("click", function(e) {
		e.preventDefault();
		$.ajax({
			url : "getSetSession.php",
			data : {
				getset : 'set',
				chosen : 'new'
			},
			success : function(data) {
				toast("Sessionen satt till <br>" + data);
				getSessionName();
			}
		});
	});
	var chooseSession = function(choose) {
		if (choose != "cancel") {
			$.ajax({
				url : "getSetSession.php",
				data : {
					getset : 'set',
					chosen : choose
				},
				success : function(data) {
					toast("Sessionen satt till <br>" + data);
					getSessionName();
					newSessionSet = true;
				}
			});
		}

	};
	$(document).on("click", '#editSession a', function(e) {
		e.preventDefault();
		$.mobile.pageContainer.pagecontainer("change", "#admin", {
			transition : "flip"
		});
	});
	//
	//------------------------------------------------------//
	//    $(document).on('pageinit', 'index.php', function() {
	//        $.mobile.navigate("#editSession", {role: "dialog"});
	//    });

	//    $("#winTableTable").tablesorter();
	//    function newPopup(url) {
	//        popupWindow = window.open(
	//                url, 'popUpWindow', 'height=700,width=800,left=10,top=10,resizable=yes,scrollbars=yes,toolbar=yes,menubar=no,location=no,directories=no,status=yes');
	//    }
	var populList = function(action, selector) {
		$.ajax({
			url : "populList.php",
			dataType : 'json',
			data : {
				action : action
			},
			success : function(data) {

				switch (action) {
					case "main":
					case "day":
					case "rest":
						selector.children().remove();
						if (data != "empty") {
							$.each(data, function(key, option) {
								selector.append(option);
							});
						}
						selector.selectmenu('refresh', true);
						break;
					case "list":
						dayList.children().remove();
						if (data[0] != "empty") {
							selector.append('<li data-role="list-divider">Playing</li>');
							$.each(data[0], function(key, day) {
								selector.append(day);
							});
						}
						if (data[1] != "empty") {
							selector.append('<li data-role="list-divider">Resting</li>');
							$.each(data[1], function(key, rest) {
								selector.append(rest);
							});
						}

						selector.listview("refresh", true);
						break;
				}

			}
		});
	};
	var populAll = function() {
		populList('main', all);
		populList('day', day);
		populList('rest', restBox);
		populList('list', dayList);
	};
	$(":mobile-pagecontainer").on("pagecontainerbeforehide", function(event, ui) {
		nextPage = ui.nextPage[0].id;
	});
	var checkLogged = function() {
		$.ajax({
			url : "checkLogged.php",
			dataType : 'json',
			success : function(data) {
				if (data == 1) {
					return true;
				} else if (nextPage == "register") {

				} else {
					$.mobile.pageContainer.pagecontainer("change", "#login", {
						transition : "flip"
					});
				}

			}
		});
	};

	$(document).on('pagebeforeshow', '#matcher', function() {
		$("#currentResting").trigger("create");
		$("#restList").listview();
		$("#restList").listview("refresh", true);
		loadMatches();
		roundResults();

	});
	$('#dayListContainer').delegate('li', 'click', function() {

		var id = $(this).find("a").attr("id");

		id = id.replace('player', '');
		console.log(id);
		loadEditPlayerBN(id);
	});
	$(document).on('pagebeforeshow', '#prepp', function() {
		if (newSessionSet) {
			$('#loadedSession').show();
			newSessionSet = false;

		} else if (!newSessionSet) {
			$('#loadedSession').hide();
		}
		if (brandNewSession) {
			brandNewSession = false;
		}

		$("#filterPlayers").on("listviewbeforefilter", function(e, data) {
			var $ul = $(this), $input = $(data.input), value = $input.val(), html = "";
			$ul.html("");
			if (value && value.length > 2) {
				$ul.html("<li><div class='ui-loader'><span class='ui-icon ui-icon-loading'></span></div></li>");
				$ul.listview("refresh");
				$.ajax({
					url : "populList.php",
					dataType : 'json',
					data : {
						action : "filter",
						q : $input.val()
					},
				}).then(function(response) {
					$.each(response, function(i, val) {
						html += "<li><a href='#' id='" + val[0] + "' class='playerListLink'>" + val[1] + "</a></li>";
					});
					$ul.html(html);
					$ul.listview("refresh");
					$ul.trigger("updatelayout");

				});
			}
		});
		$(".mainPrepp form").submit(function() {
			if ($(".playerListLink").first().text() != '') {
				var idt = $(".playerListLink").first().attr("id");
				if (!$('#restSelect option[value="' + idt + '"]').length && !$('#daySelect option[value="' + idt + '"]').length) {

					$.get('assignBoardNumber.php', {
						ids : idt
					}, function(data) {

						day.append('<option value="' + idt + '">' + data + ' ' + $("#allSelect option[value='" + idt + "']").text() + '</option>');
						refresh();
						$.ajax({
							url : "saveState.php",
							data : {
								ids : idt,
							},
							dataType : 'json'
						}).then(function(data) {
							populAll();
						});
						// $.get('saveState.php', {
						// ids : idt,
						// type : "day"
						// }, function(data) {
						// populList('list', dayList);
						// });
					});
					toast($(".playerListLink").first().text() + " tillagd");
					$('input[data-type="search"]').val("");
					$('input[data-type="search"]').trigger("change");
				} else {
					toast($(".playerListLink").first().text() + " är redan tillagd");
				}
			} else {
				toast('Skriv in namnet på en spelare');
			}

		});

	});
	$(":mobile-pagecontainer").on("pagecontainerbeforeshow", function(event, ui) {

		checkLogged();
		//        if (typeof ui.prevPage[0].id != 'undefined') {
		//            prevPage = ui.prevPage[0].id;
		//        }

		if (nextPage == "prepp" && (ui.prevPage[0].id != "allSelect-dialog" || ui.prevPage[0].id != "daySelect-dialog")) {

		} else if (nextPage == "matcher") {

		} else if (nextPage == "admin") {
			loadPlayerList();
			$.ajax({
				url : 'settings.php',
				data : {
					getset : "get"
				},
				dataType : 'json',
				success : function(response) {
					$("#" + response[0][0]).val(response[0][1]);
					$("#" + response[1][0]).slider();
					$("#" + response[0][0]).slider("refresh");
					$("#" + response[1][0]).val(response[1][1]);
					$("#" + response[1][0]).slider();
					$("#" + response[1][0]).slider("refresh");
				}
			});
		}
	});
	$(document).on("pageshow", "#matcher", function() {
		if (fromMatching) {
			$(".timeExample").TimeCircles().destroy();
		}
		$.ajax({
			url : "settings.php",
			dataType : 'json',
			data : {
				getset : "get",
				specific : "timeSlide"
			},
		}).done(function(response) {

			timerTime = response * 60;
			$(".timeExample").data("timer", timerTime);
			$(".timeExample").TimeCircles({
				"start" : true,
				"animation" : "smooth",
				"bg_width" : 1,
				"fg_width" : 0.08,
				"circle_bg_color" : "#EEEEEE",
				count_past_zero : false,
				"time" : {
					"Days" : {
						"text" : "Days",
						"color" : "#CCCCCC",
						"show" : false
					},
					"Hours" : {
						"text" : "Hours",
						"color" : "#CCCCCC",
						"show" : false
					},
					"Minutes" : {
						"text" : "Minuter",
						"color" : "#CCCCCC",
						"show" : true
					},
					"Seconds" : {
						"text" : "Sekunder",
						"color" : "#CCCCCC",
						"show" : true
					}
				}
			}).addListener(function(unit, value, total) {
				if (total == 0) {
					$(".timeExample").data('timer', 0);
					$(".timeExample").TimeCircles({
						circle_bg_color : "#900",
						time : {
							Days : {
								show : false
							},
							Hours : {
								show : false
							},
							Minutes : {
								color : '#900'
							},
							Seconds : {
								color : '#900'
							}
						}
					});
				}
			});

			if (fromMatching || newSessionSet || brandNewSession) {
				$(".timeExample").TimeCircles().stop();
				fromMatching = false;
			}
		});

	});
	//    $(document).on("click", "#sessionCancel", function(e) {
	//        e.preventDefault();
	//        e.stopPropagation();
	//        $.mobile.pageContainer.pagecontainer("change", "#" + prevPage, {transition: "flip"});
	//    });

	$(document).on("click", "#prepp .playerListLink", function(e) {
		e.preventDefault();

		if ($(this).text() != null) {
			var idt = $(this).attr("id");
			if (!$('#restSelect option[value="' + idt + '"]').length && !$('#daySelect option[value="' + idt + '"]').length) {

				$.get('assignBoardNumber.php', {
					ids : idt
				}, function(data) {

					day.append('<option value="' + idt + '">' + data + ' ' + $("#allSelect option[value='" + idt + "']").text() + '</option>');
					refresh();
					$.ajax({
						url : "saveState.php",
						data : {
							ids : idt,
							action : "addPlayer"
						},
						dataType : 'json'
					}).then(function(data) {
						populAll();
					});
					// $.get('saveState.php', {
					// ids : idt,
					// type : "day"
					// }, function(data) {
					// populList('list', dayList);
					// });
				});
				toast($(this).text() + " tillagd");
				$('input[data-type="search"]').val("");
				$('input[data-type="search"]').trigger("change");
			} else {
				toast($(this).text() + " är redan tillagd");
			}
		}

	});
	var getSessionName = function() {
		var returnera;
		$.get('getSetSession.php', {
			getset : 'get',
			reqSess : "ja"
		}, function(data) {
			returnera = data.toString();
			$(".sessionDisp").empty();
			if (data == "") {
				$(".sessionDisp").append("Välj en session eller skapa ny");
			} else {
				$(".sessionDisp").append(returnera);
				sessionSet = true;
			}
			$(".sessionDisp").trigger("refresh");
		});
	};
	getSessionName();
	/*-------------------MENYN------------------*/

	$("a[href$='#admin']").click(function(e) {
		e.preventDefault();
		e.stopPropagation();
		$.mobile.pageContainer.pagecontainer("change", "#admin", {
			transition : "flip"
		});
	});
	$("a[href$='#prepp']").click(function(e) {
		e.preventDefault();
		e.stopPropagation();
		if (sessionSet) {
			$.mobile.pageContainer.pagecontainer("change", "#prepp", {
				transition : "flip"
			});
			populAll();
		} else {
			toast('Välj eller skapa en ny session först!');
		}

	});
	$("a[href$='#matcher']").click(function(e) {
		e.preventDefault();
		e.stopPropagation();
		if (sessionSet) {
			$.mobile.pageContainer.pagecontainer("change", "#matcher", {
				transition : "flip"
			});

		} else {
			toast('Välj eller skapa en ny session först!');
		}

	});
	$("a[href$='#statistik']").click(function(e) {
		e.preventDefault();
		e.stopPropagation();
		$.mobile.pageContainer.pagecontainer("change", "#statistik", {
			transition : "flip"
		});
	});
	$("a[href$='#register']").click(function(e) {
		e.preventDefault();
		e.stopPropagation();
		$.mobile.pageContainer.pagecontainer("change", "#register", {
			transition : "flip"
		});
	});

	/*-------------/slutMENYN/---------------------*/

	expResult.click(function(e) {
		e.preventDefault();
		toast("Resultat exporterade!");
		$.get('export_today.php', {}, function(data) {
		});
	});
	var refresh = function() {
		all.children('option').removeAttr('selected');
		all.selectmenu('refresh');
		day.children('option').removeAttr('selected');
		day.selectmenu('refresh', true);
		restBox.children('option').removeAttr('selected');
		restBox.selectmenu('refresh', true);
		$("#dayList").listview('refresh', true);
	};
	toRest.click(function(e) {
		if (day.val() != null) {
			var idt = day.val();
			var onlyNewPlayers = new Array();
			$.each(idt, function(index, val) {
				if (!$('#restSelect option[value="' + val + '"]').length) {
					onlyNewPlayers.push(val);
				}
			});

			$.ajax({
				url : "saveState.php",
				data : {
					ids : idt,
					action : "movePlayer",
					type : "dayToRest"
				},
				dataType : 'json'
			}).then(function(data) {
				populAll();
			});
			// $.get('saveState.php', {
			// ids : onlyNewPlayers,
			// type : "rest"
			// }, function(data) {
			// populList('list', dayList);
			// });
			// $.get('saveStateRemove.php', {
			// ids : onlyNewPlayers,
			// type : "day"
			// }, function(data) {
			// populList('list', dayList);
			// });
			refresh();
		} else {
			toast('Pick a player from the chosen-list');
		}
	});
	fromRest.click(function(e) {
		if (restBox.val() != null) {
			var idt = restBox.val();
			var onlyNewPlayers = new Array();
			$.each(idt, function(index, val) {
				if (!$('#daySelect option[value="' + val + '"]').length) {
					onlyNewPlayers.push(val);
				}
			});

			$.ajax({
				url : "saveState.php",
				data : {
					ids : idt,
					action : "movePlayer",
					type : "restToDay"
				},
				dataType : 'json'
			}).then(function(data) {
				populAll();
			});
			// $.get('saveState.php', {
			// ids : onlyNewPlayers,
			// type : "day"
			// }, function(data) {
			// populList('list', dayList);
			// });
			// $.get('saveStateRemove.php', {
			// ids : onlyNewPlayers,
			// type : "rest"
			// }, function(data) {
			// populList('list', dayList);
			// });
			refresh();
		} else {
			toast('Pick a player from the resting-list');
		}
	});
	toAllButton.click(function(e) {
		if (day.val() != null) {
			$("#myDialog").popup("open", {
				defaults : true
			});
		} else {
			toast('Pick a player from the chosen-list');
		}
	});
	$("#allConfirm").click(function() {
		$('#myDialog').popup("close");
		var idt = day.val();
		$("#daySelect option:selected").remove();
		$.each(idt, function(index, val) {
			$("#" + val).remove();
		});
		refresh();
		$.ajax({
			url : "saveState.php",
			data : {
				ids : idt,
				action : "removePlayer"
			},
			dataType : 'json'
		}).then(function(data) {
			populAll();
		});
		// $.get('saveStateRemove.php', {
		// ids : idt,
		// type : "day",
		// removeBoard : "true"
		// }, function(data) {
		// populList('list', dayList);
		// });
	});
	toDayButton.click(function(e) {
		if (all.val() != null) {
			var idt = all.val();
			var playerIds = '';
			var onlyNewPlayers = new Array();
			$.each(idt, function(index, val) {
				if (!$('#restSelect option[value="' + val + '"]').length && !$('#daySelect option[value="' + val + '"]').length) {
					onlyNewPlayers.push(val);
					playerIds += ',' + val;
				}
			});
			if (playerIds.length > 0) {
				playerIds = playerIds.substr(1);
				$.get('assignBoardNumber.php', {
					ids : playerIds
				}, function(data) {

					$.ajax({
						url : "saveState.php",
						data : {
							ids : idt,
							action : "addPlayer"
						},
						dataType : 'json'
					}).then(function(data) {
						console.log("then");
						populAll();
					});
					// $.get('saveState.php', {
					// ids : onlyNewPlayers,
					// type : "day"
					// }, function(data) {
					// populList('list', dayList);
					// });
				});
			} else {
				toast('No new players chosen!');
			}
		} else {
			toast('Pick a player from the whole-list');
		}
	});
	reset.click(function(e) {
		$("#currentGames").text('');
		resting.children().remove();
		resultButton.addClass("ui-state-disabled");
		reset.addClass("ui-state-disabled");
		$.ajax({
			url : "resetround.php",
			data : {},
			dataType : 'json',
			success : function(data) {

				$("#daySelect option").remove();
				$.each(data, function(z, player) {
					if (!$('#restSelect option[value="' + player.id + '"]').length)
						day.append('<option value="' + player.id + '">' + player.boardNumber + ' ' + player.firstname + " " + player.lastname + '</option>');
				});
				day.enhanceWithin();
				$.mobile.pageContainer.pagecontainer("change", "#prepp", {
					transition : "flip"
				});

			}
		});
	});
	matchKnapp.click(function() {
		var playerIds = '';
		var Ids = 0;
		var prioRest = $("input:radio[name=prioRest]:checked").val();
		$.each($("#daySelect option").not('.headSel'), function(index, val) {
			playerIds += ',' + val.value;
			Ids++;
		});
		playerIds = playerIds.substr(1);
		var courts = $("#courts").val();
		var restingIds = new Array();
		$.each($("#restSelect option"), function() {
			restingIds.push($(this).val());
		});
		$.post('okToMatch.php', {}, function(data) {
			data = data.trim();
			if (data == "true") {
				toast("Report the earlier results first!");
			} else if (data == "false") {
				if (Ids > 3 && courts > 0) {
					$.ajax({
						url : "matchning.php",
						data : {
							ids : playerIds,
							courts : courts,
							prioRest : prioRest,
							chR : restingIds
						},
						dataType : 'json',
						success : function(data) {
							var matchData = new Array();
							fromMatching = true;
							matchData = data;

							restingIds.forEach(function(ID) {
								matchData[1].push(ID);
							});
							console.log(matchData);
							writeMatches(matchData);
							$.mobile.pageContainer.pagecontainer("change", "#matcher", {
								transition : "flip"
							});

						}
					});
				} else {
					console.log(Ids);
					console.log(courts);
					toast("Choose 4 players minimum");
				}
			} else {
				console.log("Data:" + data);
			}
		});
	});

	function writeMatches(data, reported) {
		$("#currentGames").text('');
		resting.children().remove();
		resting.append('<li data-theme="a" data-role="list-divider">Resters</li>');
		resting.listview();
		$("#currentGames input[type='radio']").checkboxradio();
		if (reported === "false") {
			resultButton.addClass("ui-state-disabled");
			reset.addClass("ui-state-disabled");
			$("#currentGames input[type='radio']").checkboxradio('disable');
		}
		$.each(data[0], function(key, val) {
			$.each(val, function(x, matchArray) {
				$.each(matchArray, function(z, match) {
					var matchnummer = key + 1;
					$("#currentGames").append("<fieldset id='match" + matchnummer + "' data-role='controlgroup' data-type='horizontal' data-role='fieldcontain'>" + "<input type='radio' value='par1' id='par1' name='" + key + "' />" + "<label for='par1'>" + match.par1.player1.boardNumber + " - " + match.par1.player2.boardNumber + "</label>" + "<input type='radio' value='par2' id='par2' name='" + key + "' />" + "<label for='par2'>" + match.par2.player1.boardNumber + " - " + match.par2.player2.boardNumber + "</label>" + "</fieldset>");
					$("#currentGames").trigger('create');
					if (reported == "false") {
						resultButton.addClass("ui-state-disabled");
						reset.addClass("ui-state-disabled");
						$("#currentGames input[type='radio']").checkboxradio('disable');
						$.each(winnerArrayCss, function(key, val) {
							key = key + 1;
							$("fieldset:contains('Match " + key + "') ." + val + "").prop("checked", "true");
						});
					} else {
						resultButton.removeClass("ui-state-disabled");
						reset.removeClass("ui-state-disabled");
					}
				});
			});
		});
		resultButton.unbind();
		resultButton.bind("click", function() {
			var winnerRadios = $('#currentGames input[type="radio"]:checked');
			if (winnerRadios.length < data[0].length) {
				toast('Select the winners');
			} else {
				var winnerArray = Array();
				var loserArray = Array();
				winnerRadios.each(function(index) {
					var winnerTeam = $(this).val();
					var loserTeam;
					if (winnerTeam == "par1") {
						loserTeam = "par2";
					} else {
						loserTeam = "par1";
					}
					var wonMatch = $(this).attr('name');
					winnerArray.push(data[0][wonMatch]['match']['0'][winnerTeam]['player1']['id']);
					winnerArray.push(data[0][wonMatch]['match']['0'][winnerTeam]['player2']['id']);
					loserArray.push(data[0][wonMatch]['match']['0'][loserTeam]['player1']['id']);
					loserArray.push(data[0][wonMatch]['match']['0'][loserTeam]['player2']['id']);
					winnerArrayCss[index] = winnerTeam;
				});
				$.ajax({
					url : "reportResult.php",
					data : {
						winnerIds : winnerArray,
						loserIDs : loserArray
					},
					dataType : 'text',
					success : function(data) {
						data = data.trim();
						if (data == 'NO') {
							toast('Already reported');
						} else if (data == 'YES') {
							roundResults();
							resultButton.addClass("ui-state-disabled");
							$("#currentGames input[type='radio']").checkboxradio('disable');
							$(".winText").css("display", "none");
							reset.addClass("ui-state-disabled");
							$.each(winnerArrayCss, function(key, val) {
								key = key + 1;
								$("fieldset:contains('Match " + key + "') ." + val + "").prop("checked", "true");
							});
						}
					}
				});
			}
		});
		$("#restSelect option").each(function() {
			if ($(this).text() == "Resters") {
				//                    $("#restList").append("<li data-role='list-divider'>" + $(this).text() + "</li>");
			} else {
				//                $("#restList").append("<li data-theme='a'>" + $(this).text() + "</li>");
			}

			//                resting.append($(this).text() + '<br>');
		});
		if (data[1] != null) {
			var found = false;
			$.each(data[1], function(key, val) {
				$("#restSelect option").not(".headSel").each(function() {
					console.log("restSel:");
					console.log($(this)[0].value);
					console.log(val.id);
					if (val.id === $(this)[0].value) {
						console.log("found");
						found = true;
					} else {
						console.log("notFound");
					}
				});
				if (found) {
					$("#restList").append("<li data-theme='c'>" + val.boardNumber + ' ' + val.firstname + ' ' + val.lastname + "</li>");
				} else {
					$("#restList").append("<li data-theme='a'>" + val.boardNumber + ' ' + val.firstname + ' ' + val.lastname + "</li>");
				}

				//                resting.append(val.boardNumber + ' ' + val.firstname + ' ' + val.lastname + '<br>');
			});
		} else {
			console.log('var den null?!');
		}
		$("#restList").listview();
		$("#restList").listview('refresh', true);
	}

	/*------------ sorting --------------*/
	sortByNr.click(function(e) {
		e.preventDefault();
		var my_options = $("select[name='day'] option");
		my_options.sort(function(a, b) {
			var atext = $(a).text();
			var btext = $(b).text();
			var aSplitText = atext.split(" ");
			var bSplitText = btext.split(" ");
			return parseInt(aSplitText[0]) == parseInt(bSplitText[0]) ? 0 : parseInt(aSplitText[0]) < parseInt(bSplitText[0]) ? -1 : 1;
		});
		$("select[name='day']").empty().append(my_options);
	});
	sortByFname.click(function(e) {
		e.preventDefault();
		var my_options = $("select[name='day'] option");
		my_options.sort(function(a, b) {
			var atext = $(a).text();
			var btext = $(b).text();
			var aSplitText = atext.split(" ");
			var bSplitText = btext.split(" ");
			return aSplitText[1] == bSplitText[1] ? 0 : aSplitText[1] < bSplitText[1] ? -1 : 1;
		});
		$("select[name='day']").empty().append(my_options);
	});
	/*---------------------slutSorting---------------*/
});
