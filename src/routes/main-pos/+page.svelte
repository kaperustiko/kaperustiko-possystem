<script lang="ts">
	import Card from '../components/card.svelte';
	import Sidebar from '../sidebar/+page.svelte';
	import { orderedItemsStore } from '../../stores/orderedItemsStore';
	import { onMount } from 'svelte';
	import { handleButtonClick } from '../../utils/buttonHandler'; // Import the reusable function
	import { currentInputStore } from '../../stores/currentInputStore'; // Import the store

	let cardData: MenuItem[] = [];
	let amountPaid = '₱0.00';
	let cashierName = '';
	let staffToken: string | null = null; // Declare a variable to hold the staff_token
	let currentTime: string;
	let currentDay: string;
	let orderedItems: OrderedItem[] = []; // Declare and initialize orderedItems
	let isTakeOut = false; // Declare and initialize isTakeOut
	let isDineIn = false; // Declare and initialize isDineIn

	async function fetchCashierName() {
		// Retrieve staff_token from local storage only if not already fetched
		if (!staffToken) {
			staffToken = localStorage.getItem('staff_token'); // Get the staff_token
		}
		console.log('Fetched staff_token:', staffToken); // Log the fetched staff_token
		console.log(
			`Fetching user data from: http://localhost/kaperustiko-possystem/backend/modules/get.php?action=getUser&staff_token=${staffToken}`
		); // Log the URL with the staff_token
		const response = await fetch(
			`http://localhost/kaperustiko-possystem/backend/modules/get.php?action=getUser&staff_token=${staffToken}`
		);
		if (response.ok) {
			const userData = await response.json();
			console.log('Fetched user data:', userData); // Log the fetched user data
			cashierName = userData || 'Unknown'; // Default to 'Unknown' if firstName is not available
		}
	}


	// Function to fetch orders
	async function fetchQueOrders() {
	}

	function updateTime() {
		const now = new Date();
		currentTime = now.toLocaleString('en-US', {
			year: 'numeric',
			month: 'long',
			day: 'numeric',
			hour: '2-digit',
			minute: '2-digit',
			second: '2-digit'
		});
		currentDay = now.toLocaleString('en-US', { weekday: 'long' });
	}

	onMount(() => {
		fetchQueOrders();
		fetchCashierName(); // Automatically fetch cashier name on mount
		updateTime(); // Initial call to set the time
		const intervalTime = setInterval(updateTime, 1000); // Update time every second
		return () => {
			clearInterval(intervalTime); // Clear interval on component unmount
		};
	});

	let orderNumber = '';
	let selectedCategory = 'All';
	let payment = '';
	let isPopupVisible = false;



	let isCodePopupVisible = false;
	let voidIndex: number | null = null;
	let inputCode = '';

	// Sort cardData by label1 and label2
	cardData.sort((a, b) => {
		if (a.label < b.label) return -1;
		if (a.label > b.label) return 1;
		if (a.label2 < b.label2) return -1;
		if (a.label2 > b.label2) return 1;
		return 0;
	});

	function handleNumberInput(num: string) {
		payment += num;
	}

	function handleBackspace() {
		payment = payment.slice(0, -1);
	}

	function handleClear() {
		payment = '';
	}

	function closePopup() {
		isPopupVisible = false;
	}

	function printReceipt() {
	}

	function showAlert(message: string, type: string) {
		const alertDiv = document.createElement('div');
		alertDiv.className = `fixed top-0 left-1/2 transform -translate-x-1/2 mt-4 p-4 ${type === 'success' ? 'bg-green-500' : 'bg-red-500'} text-white rounded shadow-lg`;
		alertDiv.innerText = message;
		document.body.appendChild(alertDiv);
		setTimeout(() => {
			alertDiv.remove();
		}, 3000); // Remove alert after 3 seconds
	}

	type MenuItem = {
		menu_no: string;
		code: string;
		title1: string;
		title2: string;
		price1: string;
		price2: string;
		price3: string;
		image: string;
		label: string;
		label2: string;
		qty: string;
	};

	function voidOrder(index: number) {
		voidIndex = index;
		isCodePopupVisible = true;
	}

	function closeCodePopup() {
		isCodePopupVisible = false;
		inputCode = '';
	}

	let cards = Array.from({ length: 21 }, (_, index) => ({
		title: `Card ${index + 1}`,
		description: `Description for Card ${index + 1}`
	}));

	type OrderedItem = {
		code: string;
		order_name: string;
		order_name2?: string;
		order_quantity: number;
		order_price: string;
		order_size: string;
		basePrice: number;
		order_addons?: string;
		order_addons_price?: string;
		order_addons2?: string;
		order_addons_price2?: string;
		order_addons3?: string;
		order_addons_price3?: string;
	};

	function handlePlaceOrder() {
		// Implement the
	}

	function confirmVoid() {
		// Implement the
	}
</script>

<div class="flex h-screen">
	<Sidebar />
	<div class="flex flex-grow overflow-hidden bg-gray-100">
		<div class="flex-start w-full overflow-auto p-4">
			<div class="mb-4 flex space-x-4 w-full">
				{#each ['All', 'Occupied', 'Available', 'Reserved', 'Not Paid', 'Take Out'] as category}
					<button
						class="rounded-md px-6 py-2 font-bold text-black w-full"
						class:bg-cyan-950={selectedCategory === category}
						class:text-white={selectedCategory === category}
						class:bg-white={selectedCategory !== category}
						class:shadow-md={selectedCategory !== category}
						on:click={() => (selectedCategory = category)}
					>
						{category}
					</button>
				{/each}
			</div>

			<div class="mb-4 flex items-center justify-between font-bold text-black">
				{#if selectedCategory === 'All'}
					<p>Display All Tables</p>
				{:else if selectedCategory === 'Occupied'}
					<p>Display Occupied Tables</p>
				{:else if selectedCategory === 'Available'}
					<p>Display Available Tables</p>
				{:else if selectedCategory === 'Reserved'}
					<p>Display Reserved Tables</p>
				{:else if selectedCategory === 'Not Paid'}
					<p>Display Not Paid Tables</p>
				{:else if selectedCategory === 'Take Out'}
					<p>Display Take Out Tables</p>
				{/if}
				<p class="mr-4">{currentDay} - {currentTime}</p>
			</div>

			<div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3">
				{#each cards as card}
					<div class="border p-4 rounded shadow">
						<h3 class="font-bold">{card.title}</h3>
						<p>{card.description}</p>
					</div>
				{/each}
			</div>
		</div>
	</div>

	<div class="w-[380px]">
		<div
			class="fixed right-0 top-0 flex h-full w-[350px] flex-col items-center bg-gray-100 p-4 shadow-lg"
		>
			<div class="mb-4 w-full rounded-md bg-green-800 py-2 text-center text-white">
				<p class="text-sm font-bold">Order Number {orderNumber}</p>
			</div>

			<div class="mb-4 max-h-[400px] w-full flex-grow space-y-2 overflow-y-auto">
				{#if orderedItems.length > 0}
					{#each orderedItems as item, index}
						<div class="flex flex-col rounded-lg bg-white p-4 shadow-md">
							<div class="flex items-center justify-between">
								<p class="text-gray-600">Code: {item.code}</p>
							</div>
							<div class="flex items-center justify-between">
								<p class="font-semibold text-gray-800">{item.order_name} x {item.order_quantity} {item.order_size}</p>
								<p class="text-right font-semibold text-gray-800">₱{item.order_price}.00</p>
							</div>
							<div class="flex items-center justify-between">
								<p class="text-gray-600">Size: {item.order_size}</p>
							</div>
							{#if item.order_addons !== 'None'}
								<ul class="list-disc pl-5">
									{#if item.order_addons !== 'None'}
										<div class="flex items-center justify-between">
											<li class="text-gray-600">{item.order_addons}</li>
											<p class="text-right text-gray-600">₱{item.order_addons_price}.00</p>
										</div>
									{/if}
									{#if item.order_addons2 !== 'None'}
										<div class="flex items-center justify-between">
											<li class="text-gray-600">{item.order_addons2}</li>
											<p class="text-right text-gray-600">₱{item.order_addons_price2}.00</p>
										</div>
									{/if}
									{#if item.order_addons3 !== 'None'}
										<div class="flex items-center justify-between">
											<li class="text-gray-600">{item.order_addons3}</li>
											<p class="text-right text-gray-600">₱{item.order_addons_price3}.00</p>
										</div>
									{/if}
								</ul>
							{/if}
							<button on:click={() => voidOrder(index)} class="mt-2 text-center text-red-500"
								>Void</button
							>
						</div>
					{/each}
				{:else}
					<p class="text-center text-gray-600">No items ordered yet.</p>
				{/if}
			</div>

			<div class="mt-auto w-full rounded-lg p-4 shadow-md">
				<div class="mb-4 flex w-full items-center justify-between border-b pb-2">
					<p class="font-semibold text-gray-700">Total Cost:</p>
					<p class="font-bold text-gray-800">
						₱{orderedItems
							.reduce(
								(total, item) =>
									total + parseFloat(item.order_price.toString().replace('₱', '').replace(',', '')),
								0
							)
							.toFixed(2)}
					</p>
				</div>
				<div class="flex justify-between">
					<p class="text-lg">Amount Paid:</p>
					<span class="text-lg">₱{payment || '0'}.00</span>
				</div>
				<div class="flex justify-between">
					<p class="text-lg">Change:</p>
					<span class="text-lg"
						>₱{orderedItems.length > 0 && payment
							? Math.max(
									0,
									parseFloat(
										(
											parseFloat(payment.replace('₱', '').replace(',', '')) -
											orderedItems.reduce(
												(total, item) =>
													total +
													parseFloat(item.order_price.toString().replace('₱', '').replace(',', '')),
												0
											)
										).toFixed(2)
									)
								)
							: '0'}.00</span
					>
				</div>
			</div>

			<div class="grid h-[400px] w-full grid-cols-4 gap-2">
	
				{#each ['7', '8', '9', '⌫', '4', '5', '6', 'Clr', '1', '2', '3', 'Void', '0', '00', 'Place Order'] as key, index}
					<button
						on:click={() => {
							if (key === 'Clr') {
								localStorage.removeItem('payment'); // Clear all numbers in local storage
								console.log('Cleared all numbers from local storage');
							} else if (key === '⌫') {
								const currentPayment = localStorage.getItem('payment') || '';
								localStorage.setItem('payment', currentPayment.slice(0, -1)); // Delete last number
								console.log('Deleted last number, current value:', localStorage.getItem('payment'));
							} else {
								const currentPayment = localStorage.getItem('payment') || '';
								localStorage.setItem('payment', currentPayment + key); // Store number in local storage
								console.log(
									'Stored number:',
									key,
									'Current value:',
									localStorage.getItem('payment')
								);
							}
							handleButtonClick(
								key,
								index,
								orderedItems,
								payment,
								handleBackspace,
								handleClear,
								voidOrder,
								handlePlaceOrder,
								handleNumberInput,
								isDineIn,
								isTakeOut
							);
							currentInputStore.update((store) => {
								return {
									...store,
									currentInput: key, // Update the current input
									amountPaid: parseFloat(amountPaid.replace('₱', '').replace(',', ''))
								};
							});
						}}
						class="rounded bg-gray-200 py-2 font-bold text-gray-800 col-span-{key === 'Place Order'
							? '2'
							: '1'} {key === 'Void' ? 'bg-red-900 text-white' : ''}"
					>
						{key}
					</button>
				{/each}
			</div>
		</div>
	</div>
</div>

{#if isPopupVisible}
	<div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
		<div class="rounded-lg bg-white p-8 shadow-lg">
			<div class="mb-4 flex justify-center">
				<img src="icon.png" alt="Restaurant Logo" class="h-24" />
			</div>
			<h2 class="text-center text-2xl font-bold">Kape Rustiko Restaurant</h2>
			<p class="text-center text-lg">Dewey Ave, Subic Bay Freeport Zone</p>
			<p class="text-center text-lg">Contact: (047) 998-0000</p>
			<p class="text-center text-lg">TIN: 123-456-789</p>
			<p class="text-center text-lg">Address: Dewey Ave, SBFZ</p>
			<h2 class="mb-4 mt-4 text-center text-2xl font-bold">Sales Preview</h2>
			<p class="text-lg">Receipt Number: {orderNumber}</p>
			<p class="text-lg">Date and Time: {new Date().toLocaleString()}</p>
			<p class="text-lg">Cashier Name: {cashierName}</p>
			<p class="text-lg">Order Type: {isDineIn ? 'Dine In' : 'Take Out'}</p>
			<div class="flex justify-between">
				<h2 class="mt-4 text-lg font-bold">Items Ordered:</h2>
				<span class="mt-4 text-lg font-bold">Items Price</span>
			</div>
			<ul class="max-h-[200px] overflow-auto">
				{#each orderedItems as item}
					<li class="flex flex-col justify-between text-lg">
						<div class="flex justify-between">
							<span
								>{item.order_name}
								{item.order_name2} x {item.order_quantity}
								{item.order_size}</span
							>
							<span>₱{item.basePrice}.00</span>
						</div>
						<ul class="list-disc pl-5">
							{#if Array.isArray(item.order_addons) && item.order_addons.length > 0}
								<li>{item.order_addons.join(', ')}</li>
							{/if}
							{#if item.order_addons !== 'None'}
								<li class="flex justify-between">
									<span>{item.order_addons}</span>
									<span class="text-right">₱{item.order_addons_price}.00</span>
								</li>
							{/if}
							{#if item.order_addons2 !== 'None'}
								<li class="flex justify-between">
									<span>{item.order_addons2}</span>
									<span class="text-right">₱{item.order_addons_price2}.00</span>
								</li>
							{/if}
							{#if item.order_addons3 !== 'None'}
								<li class="flex justify-between">
									<span>{item.order_addons3}</span>
									<span class="text-right">₱{item.order_addons_price3}.00</span>
								</li>
							{/if}
						</ul>
						<span>---------------------------------------------</span>
					</li>
				{/each}
			</ul>
			<div class="flex justify-between">
				<p class="mt-4 text-lg font-bold">Total Amount</p>
				<span class="mt-4 text-lg font-bold"
					>₱{orderedItems
						.reduce(
							(total, item) =>
								total + parseFloat(item.order_price.toString().replace('₱', '').replace(',', '')),
							0
						)
						.toFixed(2)}</span
				>
			</div>
			<div class="flex justify-between">
				<p class="text-lg">Amount Paid:</p>
				<span class="text-lg">₱{payment}</span>
			</div>
			<div class="flex justify-between">
				<p class="text-lg">Change:</p>
				<span class="text-lg"
					>₱{orderedItems.length > 0 && payment
						? Math.max(
								0,
								parseFloat(
									(
										parseFloat(payment.replace('₱', '').replace(',', '')) -
										orderedItems.reduce(
											(total, item) =>
												total +
												parseFloat(item.order_price.toString().replace('₱', '').replace(',', '')),
											0
										)
									).toFixed(2)
								)
							)
						: '0.00'}</span
				>
			</div>
			<h2 class="mt-4 text-center text-2xl font-bold">Thank You for Dining with Us!</h2>
			<div class="mt-4 flex justify-between">
				<button on:click={closePopup} class="mr-1 w-full rounded bg-red-500 px-6 py-3 text-white"
					>Cancel Order</button
				>
				<button on:click={printReceipt} class="ml-1 w-full rounded bg-blue-500 px-6 py-3 text-white"
					>Print Receipt</button
				>
			</div>
		</div>
	</div>
{/if}



{#if isCodePopupVisible}
	<div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-70">
		<div class="w-full max-w-md rounded-lg bg-white p-8 shadow-lg">
			<h2 class="mb-4 text-center text-2xl font-bold">Input 6-Digit Code</h2>
			<input
				type="text"
				bind:value={inputCode}
				maxlength="6"
				class="w-full rounded border border-gray-300 p-2 text-center"
				placeholder="Enter 6-digit code"
			/>
			<div class="mt-4 flex justify-between">
				<button on:click={closeCodePopup} class="rounded-md bg-red-500 px-4 py-2 text-white"
					>Cancel</button
				>
				<button on:click={confirmVoid} class="rounded-md bg-blue-500 px-4 py-2 text-white"
					>Confirm</button
				>
			</div>
		</div>
	</div>
{/if}
