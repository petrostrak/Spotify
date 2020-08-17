<?php 
    $songQuery = mysqli_query($con, "SELECT * FROM songs ORDER BY RAND() LIMIT 10");
    $resultArray = array();
    while($row = mysqli_fetch_array($songQuery)) {
        array_push($resultArray, $row['id']); 
    }
    $jsonArray = json_encode($resultArray);
?>

<script>
    $(document).ready(function() {
        var newPlaylist = <?php echo $jsonArray; ?>;
        audioElement = new Audio();
        setTrack(newPlaylist[0], newPlaylist, false);
        updateVolumeProgressBar(audioElement.audio)

        // prevent highlighting the elements when draging
        $("#nowPlayingBarContainer").on("mousedown touchstart mousemove touchmove", function(e) {
            e.preventDefault()
        })

        // drag the progress bar
        $(".playbackBar .progressBar").mousedown(function() {
            mouseDown = true
        })

        $(".playbackBar .progressBar").mousemove(function(e) {
            if(mouseDown) {
                //set time of song, depending on position of mouse
                timeFromOffset(e, this)
            }
        })

        $(".playbackBar .progressBar").mouseup(function(e) {
            timeFromOffset(e, this)
        })

        // drag the volume bar
        $(".volumeBar .progressBar").mousedown(function() {
            mouseDown = true
        })

        $(".volumeBar .progressBar").mousemove(function(e) {
            if(mouseDown) {
                var percentage = e.offsetX / $(this).width()
                if(percentage >= 0 && percentage <= 1) {
                    audioElement.audio.volume = percentage
                } 
            }
        })

        $(".volumeBar .progressBar").mouseup(function(e) {
            var percentage = e.offsetX / $(this).width()
            if(percentage >= 0 && percentage <= 1) {
                audioElement.audio.volume = percentage
            } 
        })

        $(document).mouseup(function() {
            mouseDown = false
        })
    }); 

    function timeFromOffset(mouse, progressBar) {
        var percentage = mouse.offsetX / $(progressBar).width() * 100
        var secs = audioElement.audio.duration * (percentage / 100)
        audioElement.setTime(secs)
    }

    function nextSong() {
        if(repeat) {
            audioElement.setTime(0)
            playSong()
            return
        }
        if(currentIndex == currentPlaylist.length -1) {
            currentIndex = 0
        } else {
            currentIndex++
        }
        var trackToPlay = shuffle ? shufflePlaylist[currentIndex] : currentPlaylist[currentIndex]
        setTrack(trackToPlay, currentPlaylist, true)
    }

    function previousSong() {
        if(audioElement.audio.currentTime >= 3 || currentIndex == 0) {
            audioElement.setTime(0)
        } else {
            currentIndex--
            setTrack(currentPlaylist[currentIndex], currentPlaylist, true)
        }
    }

    function setRepeat() {
        repeat = !repeat
        var imageName = repeat ? "repeat-active.png" : "repeat.png"
        $(".controlButton.repeat img").attr("src", "assets/images/icons/" + imageName)
    }

    function setMute() {
        audioElement.audio.muted = !audioElement.audio.muted
        var imageName = audioElement.audio.muted ? "volume-mute.png" : "volume.png"
        $(".controlButton.volume img").attr("src", "assets/images/icons/" + imageName)
    }

    function setShuffle() {
        shuffle = !shuffle
        var imageName = shuffle ? "shuffle-active.png" : "shuffle.png"
        $(".controlButton.shuffle img").attr("src", "assets/images/icons/" + imageName)
        
        if(shuffle) {
            //randomize
            shuffleArray(shufflePlaylist)
            currentIndex = shufflePlaylist.indexOf(audioElement.currentlyPlaying.id)
        } else {
            currentIndex = currentlyPlaying.indexOf(audioElement.currentlyPlaying.id)
        }
    }

    function shuffleArray(a) {
        var j, x, i
        for(i = a.length; i; i --) {
            j = Math.floor(Math.random() * i)
            x = a[i - 1]
            a[i - 1] = a[j]
            a[j] = x
        }
    }

    function setTrack(trackId, newPlaylist, play) {
        if(newPlaylist != currentPlaylist) {
            currentPlaylist = newPlaylist
            shufflePlaylist = currentPlaylist.slice()
            shuffleArray(shufflePlaylist)
        }

        if(shuffle) {
            currentIndex = shufflePlaylist.indexOf(trackId)    
        } else {
            currentIndex = currentPlaylist.indexOf(trackId)
        }
        pauseSong()
        // AJAX call
        $.post("includes/handlers/ajax/getSongJson.php", { songId: trackId }, function(data) {
            var track = JSON.parse(data)
            $(".trackName span").text(track.title)
            $.post("includes/handlers/ajax/getArtistJson.php", { artistId: track.artist }, function(data) {
                var artist = JSON.parse(data)
                $(".artistName span").text(artist.name)
            })
            $.post("includes/handlers/ajax/getAlbumJson.php", { albumId: track.album }, function(data) {
                var album = JSON.parse(data)
                $(".albumLink img").attr("src", album.artworkPath)
            })
            audioElement.setTrack(track);
            
            if(play){
                playSong()
            }
        })
    }

    function playSong() {
        if(audioElement.audio.currentTime == 0) {
            $.post("includes/handlers/ajax/updatePlays.php", { songId: audioElement.currentlyPlaying.id });
        }
        $(".controlButton.play").hide()
        $(".controlButton.pause").show()
        audioElement.play()
    }

    function pauseSong() {
        $(".controlButton.play").show()
        $(".controlButton.pause").hide()
        audioElement.pause()
    }
</script>

<div id="nowPlayingBarContainer">
    <div id="nowPlayingBar">
        <div id="nowPlayingLeft">
            <div class="content">
                <span class="albumLink">
                    <img class="albumArtwork" src="" alt="">
                </span>
                <div class="trackInfo">
                    <span class="trackName">
                        <span></span>
                    </span>
                    <span class="artistName">
                        <span></span>
                    </span>
                </div>
            </div>
        </div>
        <div id="nowPlayingCenter">
            <div class="content playerControls">
                <div class="buttons">
                    <button class="controlButton shuffle" title="Shuffle button" onclick="setShuffle()">
                        <img src="assets/images/icons/shuffle.png" alt="Shuffle">
                    </button>
                    <button class="controlButton previous" title="Previous button" onclick="previousSong()">
                        <img src="assets/images/icons/previous.png" alt="Previous">
                    </button>
                    <button class="controlButton play" title="Play button" onclick="playSong()">
                        <img src="assets/images/icons/play.png" alt="Play">
                    </button>
                    <button class="controlButton pause" title="Pause button" style="display: none;" onclick="pauseSong()">
                        <img src="assets/images/icons/pause.png" alt="Pause">
                    </button>
                    <button class="controlButton next" title="Next button" onclick="nextSong()">
                        <img src="assets/images/icons/next.png" alt="Next">
                    </button>
                    <button class="controlButton repeat" title="Repeat button" onclick="setRepeat()">
                        <img src="assets/images/icons/repeat.png" alt="Repeat">
                    </button>
                </div>
                <div class="playbackBar">
                    <span class="progressTime current">0.00</span>
                    <div class="progressBar">
                        <div class="progressBarBg">
                            <div class="progress"></div>
                        </div>
                    </div>
                    <span class="progressTime remaining">0.00</span>
                </div>
            </div>
        </div>
        <div id="nowPlayingRight">
            <div class="volumeBar">
                <button class="controlButton volume" title="Volume button" onclick="setMute()">
                    <img src="assets/images/icons/volume.png" alt="Volume">
                </button>
                <div class="progressBar">
                    <div class="progressBarBg">
                        <div class="progress"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>