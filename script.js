var player; // Define a variable to hold the player
var isReset = false; // Flag to track if the photo has been reset
var isVideoPlaying = false; // Flag to track if the video is playing
var hasVideoPlayed = false; // Flag to track if the video has been played

function onYouTubeIframeAPIReady() {
    player = new YT.Player('player', {
        events: {
            'onStateChange': onPlayerStateChange
        }
    });
}

function onPlayerStateChange(event) {
    // Update isVideoPlaying based on the player state
    if (event.data == YT.PlayerState.PLAYING) {
        isVideoPlaying = true;
        isReset = false; // Allow minimization to re-activate when video plays again
        hasVideoPlayed = true; // Set the flag to true once the video starts playing
    } else if (event.data == YT.PlayerState.PAUSED || event.data == YT.PlayerState.ENDED) {
        isVideoPlaying = false;
    }
}

function ensurePlaceholder() {
    const photoContainer = document.querySelector('.photo-container');
    let existingPlaceholder = document.getElementById('video-placeholder');
    if (!existingPlaceholder) {
        existingPlaceholder = document.createElement('div');
        existingPlaceholder.setAttribute('id', 'video-placeholder');
        photoContainer.parentNode.insertBefore(existingPlaceholder, photoContainer);
    }
    // Ensure the placeholder matches the video player's dimensions exactly
    existingPlaceholder.style.width = photoContainer.offsetWidth + 'px';
    existingPlaceholder.style.height = photoContainer.offsetHeight + 'px';
}

function removePlaceholder() {
    const placeholder = document.getElementById('video-placeholder');
    if (placeholder) {
        placeholder.remove(); // Simpler removal syntax
    }
}

// New function to handle minimizing
function minimizePhotoContainer() {
    const photoContainer = document.querySelector('.photo-container');
    if (!photoContainer.classList.contains('minimized')) {
        // Calculate current position for animation
        const rect = photoContainer.getBoundingClientRect();
        const startX = rect.left;
        const startY = rect.top;

        // Apply initial transform based on current position
        photoContainer.style.transform = `translate(${startX}px, ${startY}px)`;

        // Trigger reflow to ensure the transform applies correctly
        photoContainer.offsetWidth;

        // Add minimized class to start the animation from the current position
        photoContainer.classList.add('minimized');

        // Resize the photo container and iframe when minimized with !important
        photoContainer.setAttribute('style', photoContainer.getAttribute('style') + '; width: 280px !important; height: 157px !important;');
        var iframe = document.getElementById('player');
        iframe.setAttribute('style', 'width: 280px !important; height: 157px !important;');
    }
}

function maximizePhotoContainer() {
    const photoContainer = document.querySelector('.photo-container');
    if (photoContainer.classList.contains('minimized')) {
        // Reset transform to none before removing minimized class to avoid jumping
        photoContainer.style.transform = '';

        // Remove minimized class
        photoContainer.classList.remove('minimized');

        // Reset the photo container and iframe size to its original dimensions with !important
        photoContainer.setAttribute('style', 'width: 560px !important; height: 315px !important;');
        var iframe = document.getElementById('player');
        iframe.setAttribute('style', 'width: 560px !important; height: 315px !important;');
    }
}




document.addEventListener('DOMContentLoaded', function() {
    var photoContainer = document.querySelector('.photo-container');
    var resetPhotoP = document.getElementById('resetPhoto');

    window.addEventListener('scroll', function() {
        // Modify condition to call minimize/maximize functions
        if (hasVideoPlayed) {
            if (!isReset && isVideoPlaying && window.scrollY > 400) {
                minimizePhotoContainer();
                ensurePlaceholder();
            } else if (!isReset && window.scrollY < 400) {
                maximizePhotoContainer();
                removePlaceholder();
            }
        }
    });

    resetPhotoP.addEventListener('click', function(e) {
        e.preventDefault();
        maximizePhotoContainer(); // Use maximize function to reset
        removePlaceholder(); // Ensure placeholder is removed when resetting
        isReset = true; // Mark as reset to prevent automatic minimization until video plays again
        player.pauseVideo(); // Pause the video when resetting
    });
});