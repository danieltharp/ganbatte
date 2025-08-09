@props(['audioFile', 'showSpeedControls' => true, 'showMultiplePlayers' => false])

<div class="audio-player bg-gray-50 border border-gray-200 rounded-lg p-4 mb-4 dark:bg-gray-700 dark:border-gray-700">
    @if($showMultiplePlayers)
        <!-- Multiple players at different speeds -->
        <div class="space-y-3">
            <div class="flex items-center space-x-3">
                <span class="text-sm font-medium text-gray-700 w-12">1.00x</span>
                <audio 
                    controls 
                    class="flex-1 h-10"
                    data-speed="1.0"
                >
                    <source src="{{ asset('mp3/' . $audioFile) }}" type="audio/mpeg">
                    Your browser does not support the audio element.
                </audio>
            </div>
            
            <div class="flex items-center space-x-3">
                <span class="text-sm font-medium text-gray-700 w-12">0.75x</span>
                <audio 
                    controls 
                    class="flex-1 h-10"
                    data-speed="0.75"
                >
                    <source src="{{ asset('mp3/' . $audioFile) }}" type="audio/mpeg">
                    Your browser does not support the audio element.
                </audio>
            </div>
            
            <div class="flex items-center space-x-3">
                <span class="text-sm font-medium text-gray-700 w-12">0.50x</span>
                <audio 
                    controls 
                    class="flex-1 h-10"
                    data-speed="0.50"
                >
                    <source src="{{ asset('mp3/' . $audioFile) }}" type="audio/mpeg">
                    Your browser does not support the audio element.
                </audio>
            </div>
        </div>
    @else
        <!-- Single player with speed controls -->
        @php $playerId = 'audio-player-' . uniqid(); @endphp
        <div class="space-y-3">
            <audio 
                id="{{ $playerId }}" 
                controls 
                class="w-full h-12"
            >
                <source src="{{ asset('mp3/' . $audioFile) }}" type="audio/mpeg">
                Your browser does not support the audio element.
            </audio>
            
            @if($showSpeedControls)
                <div class="flex items-center justify-center space-x-2">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Speed:</span>
                    <div class="flex space-x-1" data-audio-id="{{ $playerId }}">
                        <button 
                            class="speed-btn px-3 py-1 text-sm bg-blue-100 text-blue-800 rounded hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors"
                            data-speed="0.50"
                        >
                            0.5x
                        </button>
                        <button 
                            class="speed-btn px-3 py-1 text-sm bg-blue-100 text-blue-800 rounded hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors"
                            data-speed="0.75"
                        >
                            0.75x
                        </button>
                        <button 
                            class="speed-btn px-3 py-1 text-sm bg-blue-600 text-white rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors active"
                            data-speed="1.0"
                        >
                            1.0x
                        </button>
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>

<script>
(function() {
    function initializeAudioPlayer() {
        // Handle single player with speed controls
        document.querySelectorAll('.speed-btn').forEach(button => {
            button.addEventListener('click', function() {
                const speed = parseFloat(this.dataset.speed);
                const controlGroup = this.closest('[data-audio-id]');
                const audioId = controlGroup ? controlGroup.dataset.audioId : null;
                const audio = audioId ? document.getElementById(audioId) : null;
                
                if (audio) {
                    // Function to set the playback rate
                    const setPlaybackRate = () => {
                        audio.playbackRate = speed;
                    };
                    
                    // If audio is already loaded, set immediately
                    if (audio.readyState >= 1) {
                        setPlaybackRate();
                    } else {
                        // Wait for metadata to load
                        const handleLoadedMetadata = () => {
                            setPlaybackRate();
                            audio.removeEventListener('loadedmetadata', handleLoadedMetadata);
                        };
                        audio.addEventListener('loadedmetadata', handleLoadedMetadata);
                        
                        // Also try when it can play
                        const handleCanPlay = () => {
                            setPlaybackRate();
                            audio.removeEventListener('canplay', handleCanPlay);
                        };
                        audio.addEventListener('canplay', handleCanPlay);
                    }
                    
                    // Update active button styling
                    controlGroup.querySelectorAll('.speed-btn').forEach(btn => {
                        btn.classList.remove('bg-blue-600', 'text-white', 'active');
                        btn.classList.add('bg-blue-100', 'text-blue-800');
                    });
                    
                    this.classList.remove('bg-blue-100', 'text-blue-800');
                    this.classList.add('bg-blue-600', 'text-white', 'active');
                }
            });
        });
        
        // Handle multiple players with fixed speeds
        document.querySelectorAll('audio[data-speed]').forEach(audio => {
            const targetSpeed = parseFloat(audio.dataset.speed);
            
            const setSpeed = () => {
                audio.playbackRate = targetSpeed;
            };
            
            // Set speed when metadata loads
            audio.addEventListener('loadedmetadata', setSpeed);
            
            // Also set when it can play
            audio.addEventListener('canplay', setSpeed);
            
            // If already loaded, set immediately
            if (audio.readyState >= 1) {
                setSpeed();
            }
        });
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeAudioPlayer);
    } else {
        initializeAudioPlayer();
    }
})();
</script>
