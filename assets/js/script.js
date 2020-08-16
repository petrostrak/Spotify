var currentlyPlaying = [];
var audioElement;
var mouseDown = false;
var currentIndex = 0;
var repeat = false;

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

function Audio() {
    this.currentlyPlaying;
    this.audio = document.createElement('audio');

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