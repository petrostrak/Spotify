var currentlyPlaying = [];
var audioElement;

function formatTime(seconds) {
    var time = Math.round(seconds)
    var mins = Math.floor(time / 60)
    var secs = time - (mins * 60)
    var extraZero = (secs < 10) ? "0" : ""
    return mins + ":" + extraZero + secs
}

function Audio() {
    this.currentlyPlaying;
    this.audio = document.createElement('audio');

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
}