<script lang="ts">


	let email = '';
	let password = '';
	let isLoading = false; // New state variable to control loader visibility
	let emailError = ''; // State variable for email error
	let passwordError = ''; // State variable for password error
	let accountError = ''; // State variable for general account error
	let activeInput = ''; // Track which input is currently active
	let typingTimeout: NodeJS.Timeout; // Specify the type for typingTimeout
	let showAccountError = false; // Flag to control the display of account error messages

	async function handleLogin() {
		console.log('Logging in with', email, password);
		
		isLoading = true; // Set loading state to true

		// Send login request to the backend
		const response = await fetch('http://localhost/kaperustiko-possystem/backend/modules/auth.php', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
			body: new URLSearchParams({
				action: 'login',
				email: email,
				password: password
			}).toString()
		});

		const result = await response.json(); // Parse JSON response
		console.log(result.message); // Log the response message

		isLoading = false; // Reset loading state

		if (result.status === "success") { // Check for success status
			console.log('Login Successful', result.staff_token); // Log the staff_token
			localStorage.setItem('staff_token', result.staff_token); // Store staff_token in local storage
			showAlert('Login Successful', 'success'); // Call showAlert with success type
			setTimeout(() => {
				isLoading = true; // Set loading state to true
				window.location.href = '/main-pos'; // Redirect after 3 secondsS
			}, 3000); // 3000 milliseconds = 3 seconds
			
			// New lines to open additional windows
			window.open('/waiter-monitor', '_blank', 'location=no,menubar=no,scrollbars=no,status=no,resizable=no'); // Opens waiter monitor
		} else {
			// Reset error messages
			emailError = '';
			passwordError = '';
			accountError = '';

			// Set error messages based on response
			if (result.message.includes("password")) {
				passwordError = 'Wrong password'; // Set wrong password message
			} else if (result.message.includes("email")) {
				emailError = result.message; // Set email error message
			} else {
				accountError = 'Invalid account credentials'; // Set general account error message
			}
			showAccountError = true; // Show account error after submission
		}
	}

	function validateForm() {
		let isValid = true;
		emailError = '';
		passwordError = '';
		accountError = ''; // Reset account error

		// Validate email format
		const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
		if (activeInput === 'email' && !emailPattern.test(email)) {
			emailError = 'Please enter a valid email (format: youremail@gmail.com)';
			isValid = false;
		}

		return isValid;
	}

	// New function to handle input changes with debounce
	function handleInputChange() {
		clearTimeout(typingTimeout); // Clear the previous timeout
		typingTimeout = setTimeout(() => {
			validateForm(); // Validate form after a delay
		}, 300); // Adjust the delay as needed (300ms in this case)
	}

	// New function to handle input focus
	function handleFocus(input: string) {
		activeInput = input; // Set the active input
		validateForm(); // Validate form on focus
	}

	function handleSubmit() {
		if (validateForm()) {
			 handleLogin(); 
		}
	}

	// Update showAlert function to handle success type
	function showAlert(message: string, type: string) {
		const alertDiv = document.createElement('div');
		alertDiv.className = `fixed top-0 left-1/2 transform -translate-x-1/2 mt-4 p-4 ${type === 'success' ? 'bg-green-500' : 'bg-red-500'} text-white rounded shadow-lg`;
		alertDiv.innerText = message;
		document.body.appendChild(alertDiv);
		setTimeout(() => {
			alertDiv.remove();
		}, 3000); // Remove alert after 3 seconds
	}
</script>

{#if isLoading} <!-- Conditional rendering for loader -->
<div class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-75 z-50">
    
<div role="status">
    <svg fill="currentColor" viewBox="0 0 1792 1792" class="text-green-500 w-20 h-20 animate-spin"
    xmlns="http://www.w3.org/2000/svg">
    <path
        d="M1760 896q0 176-68.5 336t-184 275.5-275.5 184-336 68.5-336-68.5-275.5-184-184-275.5-68.5-336q0-213 97-398.5t265-305.5 374-151v228q-221 45-366.5 221t-145.5 406q0 130 51 248.5t136.5 204 204 136.5 248.5 51 248.5-51 204-136.5 136.5-204 51-248.5q0-230-145.5-406t-366.5-221v-228q206 31 374 151t265 305.5 97 398.5z" />
</svg>
    <span class="sr-only">Loading...</span>
</div>

</div>
{/if}
<div class="flex flex-col min-h-screen md:flex-row">
    <div class="flex-1 bg-cyan-950 flex items-center justify-center relative left-section">
       
        <ul class="circles">
            <li></li>
            <li></li>
            <li></li>
            <li></li>
            <li></li>
            <li></li>
            <li></li>
            <li></li>
            <li></li>
            <li></li>
        </ul>
        <div class="image-container">
            <img src="./icon.png" alt="Fallback description if image fails to load" class="max-w-full h-auto" aria-hidden="true" />
        </div>
        <div class="absolute bottom-10 left-10 text-white text-lg">
            <h1 class="font-bold">Welcome to Kape Rustiko POS System</h1>
            <p class="add">Your gateway to an exceptional POS experience. Discover new opportunities with us!</p>
        </div>
    </div>
    <div class="flex-1 flex items-center justify-center">
        <form on:submit|preventDefault={handleSubmit} class="p-6 w-full md:w-2/3">
            <h2 class="text-3xl font-extrabold mb-4 text-center text-gradient">Welcome Back to Your POS System!</h2>
            <p class="text-center mb-6 text-gray-600">Please enter your credentials to access the system.</p>
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" id="email" bind:value={email} on:input={handleInputChange} on:focus={() => handleFocus('email')} required placeholder="Enter your email" class="mt-1 block w-full p-2 border border-gray-300 rounded" />
                {#if activeInput === 'email' && emailError}<p class="text-red-500 text-sm">{emailError}</p>{/if} <!-- Error message for email -->
            </div>
            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" id="password" bind:value={password} on:input={handleInputChange} on:focus={() => handleFocus('password')} required placeholder="Enter your password" class="mt-1 block w-full p-2 border border-gray-300 rounded" />
                {#if passwordError}<p class="text-red-500 text-sm">{passwordError}</p>{/if} <!-- Error message for password -->
                {#if showAccountError}<p class="text-red-500 text-sm">{accountError}</p>{/if} <!-- Error message for invalid account -->
            </div>
            <button type="submit" class="w-full bg-blue-900 text-white p-2 rounded hover:bg-sky-600">Log In</button>
            <p class="mt-4 text-center">Don't Have an Account? 
                <button type="button" class="text-red-500" on:click={() => window.location.href='/register'}>Register</button>
            </p>
            <p class="mt-2 text-center text-sm text-gray-500">Version 1.0.0 Developed by Team Codeblitz</p>
        </form>
    </div>
</div>