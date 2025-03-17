<script lang="ts">
    export let isLoading; // Declare isLoading as a prop

    // Add a new script to handle the timeout
    import { onMount } from 'svelte';
    onMount(() => {
        setTimeout(() => {
            // Redirect to the dashboard after 10 seconds
            window.location.href = '/dashboard'; // Update the URL as needed
        }, 10000); // 10 seconds
    });
</script>
  
  <div class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-75 z-50">
    <div class="flex flex-col items-center justify-center w-screen h-screen" aria-hidden="true">
        <p class="text-white text-lg text-center">Game Loading ...</p>
        <video autoplay loop muted class="absolute inset-0 w-full h-full object-cover">
            <source src="./loading-screen.mp4" type="video/mp4">
            Your browser does not support the video tag.
        </video>
        <img src="./codemasters-logo.png" alt="Fallback description if image fails to load" class="absolute top-0 left-0 max-w-[200px] h-auto ml-10" aria-hidden="true" />
        <div class="loading-bar-container" style="width: 100%; position: absolute; bottom: 20px; left: 0;">
            <div class="loading-bar" class:is-loading={isLoading} style="animation: loading 10s forwards;"></div>
        </div>
      </div>
    
    <style>
        @keyframes loading {
            0% { width: 0; }
            100% { width: 100%; }
        }
        .loading-bar-container {
            height: 4px; 
            background-color: #ccc; /* Background color for the loading bar */
            border-radius: 2px; /* Rounded edges */
            overflow: hidden; /* Ensure the loading bar stays within the container */
        }
        .loading-bar {
            height: 100%; 
            background-color: #4CAF50; /* Color of the loading bar */
            animation: loading 10s forwards; /* Fill over 10 seconds */
            transition: width 0.5s; /* Smooth transition for width changes */
        }
        .loading-bar:not(.is-loading) {
            animation: none; /* Stop animation when not loading */
        }
    </style>
  
  </div>