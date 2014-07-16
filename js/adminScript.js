var fnameField = $("input[name='fname']");
var lnameField = $("input[name='lname']");
var winField = $("input[name='wins']");
var restField = $("input[name='rests']");
var playedField = $("input[name='played_games']");
var changeButton = $("input[name='change']");
var all = $("select[name='all']");
var getPlayerButton = $("input[name='getPlayer']");
var selectSex = $("select[name='sex']");
var purgePlayerButton = $("input[name='purge']");
var currentPlayer;
var selectMember = $("select[name='member']");
var addButton = $("input[type='submit']");
var sexRadio = $("input[name='insex']");
var infnameField = $("input[name='infname']");
var inlnameField = $("input[name='inlname']");
var inselectMember = $("select[name='inmember']");
var unsetSessionA = $("#unsetSession");

var loadAllPlayers = function() {
    $.ajax({
        url: 'allMembers.php',
        dataType: 'json',
        success: function(data)
        {
            data.forEach(function(player) {
                $("#all").append('<option value="' + player[0] + '">' + player[1] + ' ' + player[2] + '</option>');
            });
        }
    });
};

var loadEditPlayer = function(id) {
    $.ajax({
        url: 'get_player.php',
        data: {id: id},
        dataType: 'json',
        success: function(data)
        {
            $("#editPlayer").enhanceWithin();
            $("#idChange").val(id);
            $("#editPlayer input[type=radio]").prop("checked", false).checkboxradio("refresh", true);
            //currentPlayer = data[0];
            $("#edinfname").val(data[1]);
            $("#edinlname").val(data[2]);
            if (data[3] == "F") {
                $("#edinsexF").prop("checked", true).checkboxradio("refresh");
            }
            if (data[3] == "M") {
                $("#edinsexM").prop("checked", true).checkboxradio("refresh");
            }
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

        }
    });
    $("#editPlayer").popup({
        create: function(event, ui) {
            $("#editPlayer").enhanceWithin();
            console.log("triggered2");
        }
    });
    $("#editPlayer").popup("open");
//    $.mobile.changePage("#editPlayer", {role: "dialog"});
};
$(document).on("afteropen", "#editPlayer", function(event, ui) {
    $("#editPlayer").enhanceWithin();
    console.log("position");
});
$("#editPlayer").on("popupcreate", function(event, ui) {
    $("#editPlayer").enhanceWithin();
    console.log("triggered");
});
$(document).on("pageshow", "#editPlayer", function() {
    $("#editPlayer").enhanceWithin();
    console.log("triggered");
});
var changePlayer = function() {
    var idChange = $("#idChange").val();
    var fname = $("#edinfname").val();
    var lname = $("#edinlname").val();
    var sex = $("input[name='edinsex']:checked").val();
    var wins = $("#edinWins").val();
    var rests = $("#edinRests").val();
    var member = $("input[name='edinmember']:checked").val();
    var played_games = $("#edinPlayed").val();
    $.ajax({
        url: 'changePlayer.php',
        data: {id: idChange, fname: fname, lname: lname, sex: sex, wins: wins, rests: rests, member: member, played_games: played_games},
        dataType: 'text',
        success: function(data) {
            toast("Ändringar genomförda!");
            loadPlayerList();

        }
    });
};
var loadPlayerList = function() {
    $.ajax({
        url: 'getplayer.php',
        data: {},
        dataType: 'json',
        success: function(playerList)
        {
            $("#mainPlayerList").empty();
            $.each(playerList, function(index, player) {
                $("#mainPlayerList").append('<li><a class="ui-icon-user" href="" id="player' + player[0] + '" class="editLink">' + player[1] + ' ' + player[2] + '</a></li>');
            });
            $("#mainPlayerList").listview("refresh", false);
        }
    });
};
var sendAddplayerForm = function() {
    if (!($("input[name=infname]").val() == "" || $("input[name=inlname]").val() == "")) {
        if ($("input[name='insex']:checked").size() > 0) {

            var fname = $("input[name=infname]").val();
            var lname = $("input[name=inlname]").val();
            var sex = $("input[name='insex']:checked").val();


            //Skicka till php-databas, h�mta id
            $.get('addplayer.php', {fname: fname, lname: lname, sex: sex}, function(data) {

                if (data == "fail") {
                    alert("Spelarnamn upptaget")
                } else {
                    loadPlayerList();
                    $("input[name='insex']").removeAttr('checked');
                    $("input[name=infname]").val("");
                    $("input[name=inlname]").val("");
                    $("input[name=infname]").focus();
                    $("input[type='radio']").checkboxradio();
                    $("input[type='radio']").checkboxradio("refresh");
                }
            });
        } else {
            toast("Klicka i kön och medlemsstatus!");
        }
    } else {
        toast("Ange namn och efternamn!");

    }
};
$(document).ready(function() {
    if (document.URL.indexOf("#admin") > 0) {
      

    }
    $("a[href$='admin']").click(function() {
        loadPlayerList();
    });
    $('#mainPlayerList').delegate('li', 'click', function() {

        var id = $(this).find("a").attr("id");
        id = id.replace('player', '');
        loadEditPlayer(id);
    });

    unsetSessionA.click(function(e) {
        e.preventDefault();
        $.get('unsetSession.php');
    });
    addButton.click(function(e) {
        e.preventDefault();
        sendAddplayerForm();
    });
    $("#addPlayerForm").submit(function() {
        sendAddplayerForm();
        return false;
    });
    $("#setButton").on("click", function(e) {
        e.preventDefault();
        e.stopPropagation();
        var options = new Array();
        var settings = new Array();
        options.push($("#courts").attr("id"));
        options.push($("#timeSlide").attr("id"));
        settings.push($("#courts").val());
        settings.push($("#timeSlide").val());

        $.ajax({
            url: 'settings.php',
            data: {getset: "set", options: options, settings: settings},
            dataType: 'json',
            success: function(response)
            {
                toast("Settings applied!");
            }
        });
    });
    purgePlayerButton.click(function(e) {
        var idt = all.val();

        $.ajax({
            url: 'purge_player.php',
            data: {id: idt[0]},
            dataType: 'json',
            success: function(data)
            {
                console.log(data);
                $("select[name='all'] option[value='" + idt[0] + "']").remove();
                currentPlayer = data[0];
                fnameField.val("");
                lnameField.val("");


                $("#selectSex").val("M").attr('selected', true);

                winField.val("");
                restField.val("");

            }
        });

    });

    $("#changePlayerButton").on("click", function(e) {
        e.preventDefault();
        changePlayer();
        $('#editPlayer').popup('close');
    });

    getPlayerButton.click(function(e) {
        var idt = all.val();

        $.ajax({
            url: 'get_player.php',
            data: {id: idt[0]},
            dataType: 'json',
            success: function(data)
            {
                console.log(data);
                currentPlayer = data[0];
                fnameField.val(data[1]);
                lnameField.val(data[2]);

                if (data[3] == "F") {
                    $("#selectSex").val("F").attr('selected', true);
                }
                if (data[3] == "M") {
                    $("#selectSex").val("M").attr('selected', true);
                }
                winField.val(data[4]);
                restField.val(data[7]);
                playedField.val(data[6]);

            }
        });

    });

    changeButton.click(function() {
        if (currentPlayer === null) {
            alert("Hämta först in en spelare från listan!");
        } else {

            var fname = fnameField.val();
            var lname = lnameField.val();
            var sex = selectSex.val();
            var wins = winField.val();
            var rests = restField.val();
            var played_games = playedField.val();
            //     $('#all[value="'+currentPlayer+'"]').text('dfgdfg');
            $.ajax({
                url: 'changePlayer.php',
                data: {id: currentPlayer, fname: fname, lname: lname, sex: sex, wins: wins, rests: rests, played_games: played_games},
                dataType: 'text',
                success: function(data) {
                    alert("Ändringar genomförda!");
                    $('#all option[value="' + currentPlayer + '"]').text(fname + '  ' + lname);

                }
            });
        }
    });


});