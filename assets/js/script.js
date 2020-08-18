var currentPlaylist = [];
var shufflePlaylist = [];
var tempPlaylist = [];
var audioElement;
var mouseDown = false;
var currentIndex = 0;
var repeat = false;
var shuffle = false;
var userLoggedIn;
var timer;

$(document).click(function(click) {
	var target = $(click.target);

	if(!target.hasClass("item") && !target.hasClass("optionsButton")) {
		hideOptionsMenu();
	}
});

$(window).scroll(function() {
	hideOptionsMenu();
});

$(document).on("change", "select.playlist", function() {
	var select = $(this)
	var playlistId = select.val()
	var songId = select.prev(".songId").val()

	$.post("includes/handlers/ajax/addToPlaylist.php", { playlistId, songId})
	.done(function(error) {

		if(error != "") {
			alert(error)
			return
        }
        
		hideOptionsMenu()
		select.val("")
	})
})

function logout() {
	$.post("includes/handlers/ajax/logout.php", function() {
		location.reload()
	})
}

function removeFromPlaylist(button, playlistId) {
	var songId = $(button).prevAll(".songId").val()
	
	$.post("includes/handlers/ajax/removeFromPlaylist.php", {playlistId, songId})
	.done(function(error) {
		if(error != "") {
			alert(error);
			return;
		}
		//do something when ajax returns
		openPage("playlist.php?id=" + playlistId);
	});
}

function openPage(url) {
	if(timer != null) {
		clearTimeout(timer);
	}

	if(url.indexOf("?") == -1) {
		url = url + "?";
	}
	var encodedUrl = encodeURI(url + "&userLoggedIn=" + userLoggedIn);
	console.log(encodedUrl);
	$("#mainContent").load(encodedUrl);
	$("body").scrollTop(0);
	history.pushState(null, null, url);
}

function createPlaylist() {
	console.log(userLoggedIn);
	var popup = prompt("Please enter the name of your playlist");
	if(popup != null) {
		$.post("includes/handlers/ajax/createPlaylist.php", { name: popup, username: userLoggedIn })
		.done(function(error) {
			if(error != "") {
				alert(error);
				return;
			}
			//do something when ajax returns
			openPage("yourMusic.php");
		});
	}   
}

function deletePlaylist(playlistId) {
    var prompt = confirm("Are you sure you want to delete this playlist?");
    if(prompt) {
        $.post("includes/handlers/ajax/deletePlaylist.php", {playlistId})
		.done(function(error) {
			if(error != "") {
				alert(error);
				return;
			}
			//do something when ajax returns
			openPage("yourMusic.php");
		});
    }
}

function hideOptionsMenu() {
	var menu = $(".optionsMenu");
	if(menu.css("display") != "none") {
		menu.css("display", "none");
	}
}

function showOptionsMenu(button) {
    var songId = $(button).prevAll(".songId").val()
	var menu = $(".optionsMenu");
    var menuWidth = menu.width();
    menu.find(".songId").val(songId)

	var scrollTop = $(window).scrollTop(); //Distance from top of window to top of document
	var elementOffset = $(button).offset().top; //Distance from top of document

	var top = elementOffset - scrollTop;
	var left = $(button).position().left;

	menu.css({ "top": top + "px", "left": left - menuWidth + "px", "display": "inline" });

}

function formatTime(seconds) {
    var time = Math.round(seconds)
    var mins = Math.floor(time / 60)
    var secs = time - (mins * 60)
    var extraZero = (secs < 10) ? "0" : ""
    return mins + ":" + extraZero + secs
}

function updateTimeProgressBar(audio) {
    $(".progressTime.current").text(formatTime(audio.currentTime))
    $(".progressTime.remaining").text(formatTime(audio.duration - audio.currentTime))
    var progress = audio.currentTime / audio.duration * 100
    $(".playbackBar .progress").css("width", progress + "%")
}

function updateVolumeProgressBar(audio) {
    var volume = audio.volume * 100
    $(".volumeBar .progress").css("width", volume + "%")
}

function playFirstSong() {
    setTrack(tempPlaylist[0], tempPlaylist, true)
}

function Audio() {
    this.currentlyPlaying;
    this.audio = document.createElement('audio');

    this.audio.addEventListener("ended", function() {
        nextSong()
    })

    this.audio.addEventListener("volumechange", function() {
        updateVolumeProgressBar(this)
    })

    this.audio.addEventListener("timeupdate", function() {
        if(this.duration) {
            updateTimeProgressBar(this)
        }
    })

    this.audio.addEventListener("canplay", function() {
        $(".progressTime.remaining").text(formatTime(this.duration))
    }) 

    this.setTrack = function(track) {
        this.currentlyPlaying = track
        this.audio.src = track.path
    }

    this.play = function() {
        this.audio.play();
    }

    this.pause = function() {
        this.audio.pause();
    }

    this.setTime = function(seconds) {
        this.audio.currentTime = seconds
    }
}