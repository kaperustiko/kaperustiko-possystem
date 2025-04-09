<script lang="ts">
	import Sidebar from '../sidebar/+page.svelte';
	import { orderedItemsStore } from '../../stores/orderedItemsStore';
	import { onMount } from 'svelte';
	import { handleButtonClick } from '../../utils/buttonHandler'; // Import the reusable function
	import { currentInputStore } from '../../stores/currentInputStore'; // Import the store
	import { WindowsSolid } from 'flowbite-svelte-icons';

	let amountPaid = '₱0.00';
	let cashierName = '';
	let staffToken: string | null = null; // Declare a variable to hold the staff_token
	let currentTime: string;
	let currentDay: string;
	let orderedItems: OrderedItem[] = []; // Declare and initialize orderedItems
	let isTakeOut = false; // Declare and initialize isTakeOut
	let isDineIn = false; // Declare and initialize isDineIn
	let queuedOrders: QueuedOrder[] = []; // Use the defined type for queuedOrders
	let orders: any[] = []; // Declare a variable to hold fetched orders
	let isReservePopupVisible = false; // State variable to control the visibility of the reserve popup
	let tableStatus: { [key: string]: boolean } = {};
	let isReservePopup2Visible = false; // State variable to control the visibility of the reserve popup
	let isReceiptPopupVisible = false;

	let reserveDate = '';
	let reserveTime = '';
	let selectedTableNumber = '';
	let orderNumber = '';
	let selectedCategory = 'All';
	let payment = '';
	let isPopupVisible = false;

	let isCodePopupVisible = false;
	let voidIndex: number | null = null;
	let inputCode = '';

	let reservedTables: string[] = []; // Declare an array to hold reserved table numbers

	type QueuedOrder = {
		que_order_no: string;
		receipt_number: string;
		date: string;
		time: string;
		table_number: string;
		order_status: string;
		items_ordered: string;
		total_amount: number;
	};

	let totalOrderedItemsPrice = 0; // Variable to hold the total price of ordered items
	let voucherCode = ''; // Declare the voucherCode variable
	let voucherDiscount = 0; // Declare the voucherDiscount variable

	// Define the Voucher type
	type Voucher = {
		code: string;
		discount: number;
		voucher_discount: string;
		voucher_code: string;
		// Add other fields as necessary
	};

	// Function to calculate total price of ordered items
	function calculateTotalOrderedItemsPrice() {
		totalOrderedItemsPrice = orderedItems.reduce((total, item) => {
			// Calculate total addons price
			const addonsPrice =
				(item.order_addons_price || 0) +
				(item.order_addons_price2 || 0) +
				(item.order_addons_price3 || 0);

			// Calculate total for this item
			const itemTotal = item.total_amount;

			// Return the accumulated total
			return total + itemTotal;
		}, 0);

		// Log the total ordered items price
	}

	// Call this function whenever orderedItems changes
	$: calculateTotalOrderedItemsPrice();

	// Function to calculate voucher discount
	async function calculateVoucherDiscount() {
		const response = await fetch(
			`http://localhost/kaperustiko-possystem/backend/modules/get.php?action=getVouchersbyCode&voucher_code=${voucherCode}`,
			{
				method: 'GET' // Change to GET method
			}
		);
		if (response.ok) {
			const data = await response.json();
			voucherDiscount = data[0].voucher_discount;
		} else {
			console.error('Failed to fetch vouchers:', response.statusText);
			voucherDiscount = 0; // Reset discount on fetch failure
		}
		console.log('Voucher Code Inputted:', voucherCode); // Log the voucher code inputted
		console.log('Fetched Discount:', voucherDiscount); // Log the fetched discount
		// Log the link with the inputted voucher code
		console.log(
			`Fetch URL: http://localhost/kaperustiko-possystem/backend/modules/get.php?action=getVouchersbyCode&voucher_code=${voucherCode}`
		);
	}

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
	async function fetchQueuedOrders() {
		const response = await fetch(
			'http://localhost/kaperustiko-possystem/backend/modules/get.php?action=getQueOrders'
		);
		if (response.ok) {
			queuedOrders = await response.json(); // Check the response here
			// Assuming each order has a table_number field
			queuedOrders.forEach(order => {
				console.log('Table Number:', order.table_number); // Log the table number for each order
			});
		} else {
			console.error('Failed to fetch queued orders', response.statusText); // Improved error logging
		}
	}

	// Function to fetch reserve tables
	async function fetchReserveTables() {
		const response = await fetch(
			'http://localhost/kaperustiko-possystem/backend/modules/get.php?action=getReserveTables'
		);
		if (response.ok) {
			const data = await response.json();
			reservedTables = data.map((table: { table_number: string }) => table.table_number); // Assuming the response contains an array of reserved table objects
		} else {
			console.error('Failed to fetch reserved tables', response.statusText);
		}
	}

	async function fetchOrders() {
		const response = await fetch(
			'http://localhost/kaperustiko-possystem/backend/modules/get.php?action=getQueOrders'
		);
		if (response.ok) {
			orders = await response.json(); // Store the fetched orders
		} else {
			console.error('Failed to fetch orders:', response.statusText);
		}
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
		fetchQueuedOrders();
		fetchCashierName(); // Automatically fetch cashier name on mount
		fetchOrders(); // Fetch orders on component mount
		updateTime(); // Initial call to set the time
		const intervalTime = setInterval(updateTime, 1000); // Update time every second
		fetchReserveTables(); // Fetch reserved tables on component mount
		return () => {
			clearInterval(intervalTime); // Clear interval on component unmount
		};
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

	function showAlert(message: string, type: string) {
		const alertDiv = document.createElement('div');
		alertDiv.className = `fixed top-0 left-1/2 transform -translate-x-1/2 mt-4 p-4 ${type === 'success' ? 'bg-green-500' : 'bg-red-500'} text-white rounded shadow-lg`;
		alertDiv.innerText = message;
		document.body.appendChild(alertDiv);
		setTimeout(() => {
			alertDiv.remove();
		}, 3000); // Remove alert after 3 seconds
	}

	function voidOrder(index: number) {
		voidIndex = index;
		isCodePopupVisible = true;
	}

	function closeCodePopup() {
		isCodePopupVisible = false;
		inputCode = '';
	}

	let cards = Array.from({ length: 21 }, (_, index) => ({
		table: `${index + 1}`
	}));

	type OrderedItem = {
		que_order_no: string;
		receipt_number: string;
		date: string;
		time: string;
		items_ordered: string;
		total_amount: number;
		amount_paid: number;
		amount_change: number;
		order_status: string;
		table_number: string;
		order_name: string;
		order_name2?: string;
		order_quantity: number;
		order_size: string;
		basePrice: number;
		order_addons?: string;
		order_addons_price?: number;
		order_addons2?: string;
		order_addons_price2?: number;
		order_addons3?: string;
		order_addons_price3?: number;
		order_price?: number;
	};

	function handlePlaceOrder() {
		// Check if there are ordered items before opening the receipt popup
		if (orderedItems.length === 0) {
			showAlert('No items ordered yet. Please add items to your order.', 'error');
			return; // Exit the function if no items are ordered
		}

		// Validate payment amount before proceeding
		const totalCost = (
			((totalOrderedItemsPrice || 0) * 1.12 || 0) * (1 - (voucherDiscount || 0) / 100) + 
			((totalOrderedItemsPrice * 1.12 || 0) * 0.10)
		).toFixed(2);

		if (parseFloat(payment) < parseFloat(totalCost)) {
			showAlert('Invalid amount. Please enter an amount equal to or greater than the total cost.', 'error');
			return; // Exit the function if payment is invalid
		}

		isReceiptPopupVisible = true; // Show the receipt popup
	}

	function printReceipt() {
		const receiptData = {
			receiptNumber: orderNumber,
			date: new Date().toLocaleDateString(),
			time: new Date().toLocaleTimeString(),
			cashierName: cashierName,
			itemsOrdered: orderedItems,
			totalAmount: totalOrderedItemsPrice,
			amountPaid: parseFloat(payment) || 0,
			change: Math.max(0, parseFloat((parseFloat(payment) - totalOrderedItemsPrice).toFixed(2))),
			order_take: isTakeOut ? 'Take Out' : 'Dine In',
			table_number: selectedTableNumber // Ensure this is set correctly
		};

		// Log the data to check for issues
		console.log('Receipt Data:', JSON.stringify(receiptData, null, 2));

		fetch('http://localhost/kaperustiko-possystem/backend/modules/insert.php?action=insertReceipt', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json'
			},
			body: JSON.stringify(receiptData)
		})
		.then(response => {
			console.log('Response Status:', response.status); // Log the response status
			return response.json();
		})
		.then(data => {
			console.log('Response Data:', data); // Log the response data
			if (data.error) {
				showAlert(data.error, 'error');
			} else {
				showAlert(data.message, 'success');
				isReceiptPopupVisible = false; // Close the receipt popup
				window.location.reload(); // Reload the window after success

				// Call the delete API after successful receipt printing
				return fetch(`http://localhost/kaperustiko-possystem/backend/modules/delete.php?action=deleteTableOccupancy&receipt_number=${receiptData.receiptNumber}`, {
					method: 'DELETE'
				});
			}
		})
		.then(deleteResponse => {
			if (deleteResponse) {
				return deleteResponse.json();
			}
		})
		.then(deleteData => {
			if (deleteData && deleteData.success) {
				console.log('Table data deleted successfully.');
			} else {
				console.error('Failed to delete table data:', deleteData.message);
			}
		})
		.catch(error => {
			console.error('Error:', error);
			showAlert('Failed to save receipt.', 'error');
		});

	}

	let isCardPopupVisible = false;
	let selectedCard: any;

	function openCardPopup(card: any) {
		isCardPopupVisible = true;
		selectedCard = card;
	}

	function closeCardPopup() {
		isCardPopupVisible = false;
	}

	// Function to handle checkout
	function handleCheckOut(table: string) {
		const orderToCheckOut = queuedOrders.find((order) => order.table_number === table);
		if (orderToCheckOut) {
			try {
				orderedItems = JSON.parse(orderToCheckOut.items_ordered); // Add items to orderedItems
				totalOrderedItemsPrice = orderToCheckOut.total_amount; // Set totalOrderedItemsPrice to the total_amount of the order
				orderNumber = orderToCheckOut.que_order_no; // Set the order number
				console.log(totalOrderedItemsPrice);
				closeCardPopup(); // Close the popup after checking out
			} catch (error) {
				console.error("Error parsing items_ordered JSON during checkout:", error);
				console.log("Raw JSON data:", orderToCheckOut.items_ordered);
				showAlert('Error processing order data. Please contact support.', 'error');
			}
		} else {
			showAlert('No orders found for this table.', 'error'); // Show alert if no orders found
		}
	}

	// Function to handle table reservation
	async function handleReserveTable() {
		const response = await fetch(
			'http://localhost/kaperustiko-possystem/backend/modules/insert.php?action=reserve_date',
			{
				method: 'POST',
				headers: {
					'Content-Type': 'application/json'
				},
				body: JSON.stringify({
					reserve_date: reserveDate,
					reserve_time: reserveTime,
					table_number: selectedTableNumber
				})
			}
		);

		const result = await response.json();
		if (result.success) {
			showAlert('Table reserved successfully!', 'success');
			isReservePopupVisible = false;
			location.reload();
		} else {
			showAlert('Failed to reserve table: ' + result.error, 'error');
		}
	}

	async function openReservedTable2Popup(card: any) {
		isReservePopup2Visible = true; // Set the popup visibility to true
		selectedCard = card; // Store the selected card data

		// Fetch reserved table details
		const response = await fetch(
			`http://localhost/kaperustiko-possystem/backend/modules/get.php?action=getInfoReserveTables&table_number=${card.table}`
		);
		if (response.ok) {
			const reservedTableDetails = await response.json();
			// You can now use reservedTableDetails to display more information in the popup if needed
			reserveDate = reservedTableDetails[0]?.reserve_date || ''; // Assuming the response contains reserve_date
			reserveTime = reservedTableDetails[0]?.reserve_time || ''; // Assuming the response contains reserve_time
		} else {
			console.error('Failed to fetch reserved table details:', response.statusText);
		}
	}

	async function handleDeleteReservation() {
		const response = await fetch(
			`http://localhost/kaperustiko-possystem/backend/modules/delete.php?action=deleteByTableNumber&table_number=${selectedCard.table}`,
			{
				method: 'DELETE'
			}
		);

		const result = await response.json();
		if (result.success) {
			showAlert('Reservation deleted successfully!', 'success');
			isReservePopup2Visible = false; // Close the popup
			location.reload(); // Optionally reload to reflect changes
		} else {
			showAlert('Failed to delete reservation: ' + result.message, 'error');
		}
	}

	let clickedKey: string | null = null; // Variable to track the clicked key
</script>

<div class="flex h-screen">
	<Sidebar />
	<div class="flex flex-grow overflow-hidden bg-gray-100">
		<div class="flex-start w-full overflow-auto p-4">
			<div class="mb-4 flex w-full space-x-4">
				{#each ['All', 'Occupied', 'Available', 'Reserved', 'Take Out'] as category}
					<button
						class="w-full rounded-md px-6 py-2 font-bold text-black"
						class:bg-cyan-950={selectedCategory === category}
						class:text-white={selectedCategory === category}
						class:bg-white={selectedCategory !== category}
						class:shadow-md={selectedCategory !== category}
						on:click={() => (selectedCategory = category)}
					>
						{category}
					</button>
				{/each}
				<button
					class="w-full rounded-md bg-cyan-950 px-6 py-2 font-bold text-white hover:bg-blue-600"
					on:click={() => (isReservePopupVisible = true)}
				>
					Reserve Table
				</button>
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
				{:else if selectedCategory === 'Take Out'}
					<p>Display Take Out Tables</p>
				{/if}
				<p class="mr-4">{currentDay} - {currentTime}</p>
			</div>

			<div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3">
				{#each cards as card}
					{#if selectedCategory === 'All' || 
						(selectedCategory === 'Occupied' && orders.some((order) => order.table_number === card.table)) || 
						(selectedCategory === 'Available' && !orders.some((order) => order.table_number === card.table) && !reservedTables.includes(card.table)) || 
						(selectedCategory === 'Reserved' && reservedTables.includes(card.table))}
						<button
							class={`border-2 ${orders.some((order) => order.table_number === card.table) ? 'border-white bg-cyan-950 text-white' : reservedTables.includes(card.table) ? 'border-white bg-red-950 text-white' : 'border-cyan-950 bg-white text-black'} flex flex-col items-center justify-center rounded-full p-8 shadow-lg`}
							on:click={() => {
								if (reservedTables.includes(card.table)) {
									openReservedTable2Popup(card); // Open popup for reserved table
								} else if (orders.some((order) => order.table_number === card.table)) {
									openCardPopup(card); // Open popup only if the table is occupied
								}
							}}
							aria-label={`Open popup for table ${card.table}`}
						>
							<h3 class="text-6xl font-bold">{card.table}</h3>
							<span
								class={`mt-2 rounded-full bg-gray-200 px-4 py-2 text-xs text-gray-500 ${orders.some((order) => order.table_number === card.table) ? 'bg-red-500 text-white' : reservedTables.includes(card.table) ? 'bg-red-500 text-white' : 'bg-green-500 text-white'}`}
							>
								{orders.some((order) => order.table_number === card.table)
									? 'Occupied'
									: reservedTables.includes(card.table)
										? 'Reserved'
										: 'Available'}
							</span>
						</button>
					{/if}
				{/each}
			</div>

			{#if isCardPopupVisible}
				<div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-70">
					<div class="w-full max-w-md rounded-lg bg-white p-6 shadow-lg">
						<h2 class="mb-4 text-center text-2xl font-bold text-gray-800">
							Table {selectedCard.table}
						</h2>
						{#if queuedOrders.length > 0}
							{#each queuedOrders as order}
								{#if order.table_number === selectedCard.table}
									<div class="border-b border-gray-300 py-2">
										<p class="font-semibold">
											Order No: <span class="font-normal">{order.que_order_no}</span>
										</p>
										<p class="font-semibold">
											Receipt No: <span class="font-normal">{order.receipt_number}</span>
										</p>
										<p class="font-semibold">Date: <span class="font-normal">{order.date}</span></p>
										<p class="font-semibold">Time: <span class="font-normal">{order.time}</span></p>
										<p class="font-semibold">Items Ordered:</p>
										{#each (() => {
											try {
												return JSON.parse(order.items_ordered);
											} catch (error) {
												console.error("Error parsing items_ordered JSON:", error);
												console.log("Raw JSON data:", order.items_ordered);
												return []; // Return empty array in case of parsing error
											}
										})() as item}
											<div class="flex items-center justify-between border-b border-gray-200 py-2">
												<div class="flex-1">
													<p class="font-normal">Name: {item.order_name} {item.order_name2}</p>
													<p class="font-normal">Quantity: {item.order_quantity}</p>
													<p class="font-normal">Size: {item.order_size}</p>
												</div>
												<div class="flex-none text-right">
													<p class="font-normal">₱{item.basePrice}.00</p>
												</div>
											</div>
											<p class="font-normal">Addons:</p>
											{#if item.order_addons && item.order_addons_price != null && item.order_addons_price > 0}
												<div class="flex justify-between">
													<p class="font-normal">{item.order_addons}</p>
													<p class="text-right font-normal">₱{item.order_addons_price}.00</p>
												</div>
											{/if}
											{#if item.order_addons2}
												<div class="flex justify-between">
													<p class="font-normal">{item.order_addons2}</p>
													<p class="text-right font-normal">₱{item.order_addons_price2}.00</p>
												</div>
											{/if}
											{#if item.order_addons3}
												<div class="flex justify-between">
													<p class="font-normal">{item.order_addons3}</p>
													<p class="text-right font-normal">₱{item.order_addons_price3}.00</p>
												</div>
											{/if}
										{/each}
										<div class="flex justify-between">
											<p class="text-lg font-bold">Total Price:</p>
											<p class="text-right text-lg font-normal">₱{order.total_amount}.00</p>
										</div>
									</div>
									<p class="font-semibold">
										Status: <span class="font-normal">{order.order_status}</span>
									</p>
								{/if}
							{/each}
						{:else}
							<p class="text-center text-gray-600">No orders for this table.</p>
						{/if}
						<div class="mt-4 flex justify-center space-x-4">
							<button
								on:click={closeCardPopup}
								class="w-full max-w-xs rounded-md bg-red-500 px-4 py-2 text-white transition hover:bg-red-600"
								>Close</button
							>
							<button
								on:click={() => handleCheckOut(selectedCard.table)}
								class="w-full max-w-xs rounded-md bg-green-500 px-4 py-2 text-white transition hover:bg-green-600"
								>Check Out</button
							>
						</div>
					</div>
				</div>
			{/if}
		</div>
	</div>

	<div class="w-[350px]">
		<div
			class="fixed right-0 top-0 flex h-full w-[350px] flex-col items-center bg-gray-100 p-4 shadow-lg"
		>
			<div class="mb-4 w-full rounded-md bg-green-800 py-2 text-center text-white">
				<p class="text-sm font-bold">Order Number {orderNumber}</p>
			</div>

			<div class="mb-4 max-h-[400px] w-full flex-grow space-y-2 overflow-y-auto">
				{#if orderedItems.length > 0}
					{#each orderedItems as item}
						<div class="rounded-lg border bg-white p-4 shadow-md">
							<p class="font-semibold">
								{item.order_name}
								{item.order_name2} {item.order_quantity}
							</p>
							<div class="mb-2 flex w-full items-center justify-between border-b pb-1">
								<p class="font-normal">{item.order_size}</p>
								<p class="text-right font-normal">₱{item.basePrice}.00</p>
							</div>

							{#if item.order_addons && item.order_addons_price != null && item.order_addons_price > 0}
								<p class="font-normal">Addons:</p>
								<div class="flex flex-col">
									{#if item.order_addons}
										<div class="flex justify-between">
											<p class="font-normal">{item.order_addons}</p>
											<p class="text-right font-normal">₱{item.order_addons_price}.00</p>
										</div>
									{/if}
									{#if item.order_addons2}
										<div class="flex justify-between">
											<p class="font-normal">{item.order_addons2}</p>
											<p class="text-right font-normal">₱{item.order_addons_price2}.00</p>
										</div>
									{/if}
									{#if item.order_addons3}
										<div class="flex justify-between">
											<p class="font-normal">{item.order_addons3}</p>
											<p class="text-right font-normal">₱{item.order_addons_price3}.00</p>
										</div>
									{/if}
								</div>
							{/if}
						</div>
					{/each}
				{:else}
					<p class="text-center text-gray-600">No items ordered yet.</p>
				{/if}
			</div>

			<div class="mt-auto w-full rounded-lg p-2 shadow-md">
				<div class="mb-4 flex w-full items-center justify-between border-b pb-2">
					<p class="text-sm font-semibold text-gray-700">Voucher Code:</p>
					<input
						type="text"
						bind:value={voucherCode}
						class="text-sm font-bold text-gray-800 flex-grow mr-2 w-[60%]"
						placeholder="Enter code"
					/>
					<button
						on:click={calculateVoucherDiscount}
						class="rounded-md bg-blue-500 px-4 py-2 text-white transition hover:bg-blue-600"
					>
						Redeem
					</button>
				</div>
				<div class="flex w-full items-center justify-between border-b pb-1">
					<p class="text-sm font-semibold text-gray-700">VAT (12%):</p>
					<p class="text-sm font-bold text-gray-800">₱{((totalOrderedItemsPrice * 0.12) || 0).toFixed(2)}</p>
				</div>
				<div class="flex w-full items-center justify-between border-b pb-1">
					<p class="text-sm font-semibold text-gray-700">Service Charge (10%):</p>
					<p class="text-sm font-bold text-gray-800">₱{((totalOrderedItemsPrice * 1.12 || 0) * 0.10).toFixed(2)}</p>
				</div>
				<div class="mb-2 flex w-full items-center justify-between border-b pb-1">
					<p class="text-sm font-semibold text-gray-700">Voucher Discount:</p>
					<p class="text-sm font-bold text-gray-800">{voucherDiscount || 0}%</p>
				</div>
				<div class="mb-2 flex w-full items-center justify-between border-b pb-1">
					<p class="text-md font-semibold text-gray-700">Total Cost:</p>
					<p class="text-md font-bold text-gray-800">
						₱{(
							((totalOrderedItemsPrice || 0) * 1.12 || 0) * (1 - (voucherDiscount || 0) / 100) + ((totalOrderedItemsPrice * 1.12 || 0) * 0.10)
						).toFixed(2)}
					</p>
				</div>
				<div class="flex justify-between">
					<p class="text-sm">Amount Paid:</p>
					<span class="text-sm">₱{(parseFloat(payment) || 0)}</span>
				</div>
				<div class="flex justify-between">
					<p class="text-sm">Change:</p>
					<span class="text-sm">
						₱{Math.max(0, parseFloat(((parseFloat(payment) || 0) - ((totalOrderedItemsPrice * 1.12 || 0) + 20 + ((totalOrderedItemsPrice * 1.12 || 0) * 0.10))).toFixed(2)))}
					</span>
				</div>
			</div>

			<div class="w-full">
				<div class="mb-4">
					<input
						id="payment-input"
						type="text"
						bind:value={payment}
						placeholder="Enter amount"
						class="w-full rounded-md border border-gray-300 p-3 text-gray-800 focus:border-blue-500 focus:ring-blue-500"
						on:input={() => {
							// Store in localStorage to maintain compatibility with other functions
							localStorage.setItem('payment', payment);
							// Validate payment amount
							const totalCost = (
								((totalOrderedItemsPrice || 0) * 1.12 || 0) * (1 - (voucherDiscount || 0) / 100) + 
								((totalOrderedItemsPrice * 1.12 || 0) * 0.10)
							).toFixed(2);
							if (parseFloat(payment) < parseFloat(totalCost)) {
								showAlert('Invalid amount. Please enter an amount equal to or greater than the total cost.', 'error');
							}
						}}
					/>
				</div>
				
				<div class="grid grid-cols-2 gap-4">
					<button
						on:click={() => {
							// Place Order functionality
							handleButtonClick(
								'Place Order',
								0,
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
									currentInput: 'Place Order',
									amountPaid: parseFloat(amountPaid.replace('₱', '').replace(',', ''))
								};
							});
						}}
						class="rounded py-3 font-bold text-white bg-cyan-950 hover:bg-blue-700 col-span-2"
					>
						Checkout Bill
					</button>
				</div>
			</div>
		</div>
	</div>
</div>


{#if isReservePopupVisible}
	<div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-70">
		<div class="w-full max-w-md rounded-lg bg-white p-8 shadow-lg">
			<h2 class="mb-6 text-center text-2xl font-bold text-gray-800">Reserve a Table</h2>
			<input
				type="date"
				bind:value={reserveDate}
				class="mb-4 w-full rounded border border-gray-300 p-3 shadow-sm focus:outline-none focus:ring focus:ring-blue-200"
				min={new Date().toISOString().split('T')[0]}
			/>
			<input
				type="time"
				bind:value={reserveTime}
				class="mb-4 w-full rounded border border-gray-300 p-3 shadow-sm focus:outline-none focus:ring focus:ring-blue-200"
			/>
			<select
				bind:value={selectedTableNumber}
				class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
			>
				<option value="">Select Table Number</option>
				{#each Array(21) as _, index}
					<option
						value={index + 1}
						class={queuedOrders.some((order) => order.table_number === (index + 1).toString())
							? 'bg-red-500 text-white'
							: reservedTables.includes((index + 1).toString())
								? 'bg-orange-950 text-white'
								: ''}
						disabled={tableStatus[index + 1]}
					>
						Table {index + 1}
					</option>
				{/each}
			</select>
			<div class="mt-6 flex justify-between">
				<button
					on:click={() => (isReservePopupVisible = false)}
					class="rounded-md bg-red-600 px-4 py-2 text-white transition hover:bg-red-700"
					>Cancel</button
				>
				<button
					on:click={handleReserveTable}
					class="rounded-md bg-blue-600 px-4 py-2 text-white transition hover:bg-blue-700"
					>Confirm</button
				>
			</div>
		</div>
	</div>
{/if}

{#if isReservePopup2Visible}
	<div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-70">
		<div class="w-full max-w-md rounded-lg bg-white p-8 shadow-lg">
			<h2 class="mb-4 text-center text-2xl font-bold">Reserved Table Details</h2>
			<p>Table Number: {selectedCard.table}</p>
			<p>Reserved Date: {reserveDate}</p>
			<p>Reserved Time: {reserveTime}</p>
			<div class="mt-4 flex justify-center space-x-4">
				<button
					on:click={() => (isReservePopup2Visible = false)}
					class="w-full max-w-xs rounded-md bg-red-500 px-4 py-2 text-white transition hover:bg-red-600"
					>Close</button
				>
				<button
					on:click={handleDeleteReservation}
					class="w-full max-w-xs rounded-md bg-red-600 px-4 py-2 text-white transition hover:bg-red-700"
					>Delete Reservation</button
				>
			</div>
		</div>
	</div>
{/if}

{#if isReceiptPopupVisible}
	<div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-70">
		<div class="rounded-lg bg-white p-8 shadow-lg max-w-lg w-full">
			<div class="mb-4 flex justify-center">
				<img src="icon.png" alt="Restaurant Logo" class="h-24" />
			</div>
			<h2 class="text-center text-2xl font-bold">Kape Rustiko Cafe and Restaurant</h2>
			<p class="text-center text-lg">Dewey Ave, Subic Bay Freeport Zone</p>
			<p class="text-center text-lg">VAT REG TIN: 123-456-789-12345</p>
			<h2 class="mb-4 mt-4 text-center text-2xl font-bold">SALES INVOICE</h2>
			<p class="text-lg">Transaction Date: {new Date().toLocaleDateString()}</p>
			<p class="text-lg">Transaction Time: {new Date().toLocaleTimeString()}</p>
			<p class="text-lg">Cashier Name: {cashierName}</p>
			<p class="text-lg">Receipt Number: {orderNumber}</p>
			<div class="mt-4">
				<div class="flex justify-between font-semibold">
					<h2 class="mt-4 text-lg">Items Ordered:</h2>
					<span class="mt-4 text-lg">Price</span>
				</div>
				{#if orderedItems.length > 0}
					{#each orderedItems as item}
						<div class="flex justify-between border-b py-2">
							<p class="font-normal">{item.order_name} {item.order_name2} x{item.order_quantity}</p>
							<p class="text-right font-normal">₱{item.basePrice}.00</p>
						</div>
						{#if item.order_addons && item.order_addons_price != null && item.order_addons_price > 0}
							<p class="font-normal">Addons:</p>
							<div class="flex flex-col">
								{#if item.order_addons}
									<div class="flex justify-between">
										<p class="font-normal">{item.order_addons}</p>
										<p class="text-right font-normal">₱{item.order_addons_price}.00</p>
									</div>
								{/if}
								{#if item.order_addons2}
									<div class="flex justify-between">
										<p class="font-normal">{item.order_addons2}</p>
										<p class="text-right font-normal">₱{item.order_addons_price2}.00</p>
									</div>
								{/if}
								{#if item.order_addons3}
									<div class="flex justify-between">
										<p class="font-normal">{item.order_addons3}</p>
										<p class="text-right font-normal">₱{item.order_addons_price3}.00</p>
									</div>
								{/if}
							</div>
						{/if}
					{/each}
				{:else}
					<p class="text-center text-gray-600">No items ordered yet.</p>
				{/if}
			</div>

			<div class="mt-4 w-full rounded-lg p-2 shadow-md">
				<div class="mb-4 flex w-full items-center justify-between border-b pb-2">
					<p class="text-sm font-semibold text-gray-700">Subtotal:</p>
					<p class="text-sm font-bold text-gray-800">₱{totalOrderedItemsPrice}</p>
				</div>
				<div class="flex w-full items-center justify-between border-b pb-1">
					<p class="text-sm font-semibold text-gray-700">Total Discount:</p>
					<p class="text-sm font-bold text-gray-800">₱{(totalOrderedItemsPrice * voucherDiscount / 100)}</p>
				</div>
				<div class="flex w-full items-center justify-between border-b pb-1">
					<p class="text-sm font-semibold text-gray-700">Service Charge (10%):</p>
					<p class="text-sm font-bold text-gray-800">₱{((totalOrderedItemsPrice * 1.12) * 0.10).toFixed(2)}</p>
				</div>
				<div class="flex w-full items-center justify-between border-b pb-1">
					<p class="text-sm font-semibold text-gray-700">Total:</p>
					<p class="text-sm font-bold text-gray-800">₱{(
						totalOrderedItemsPrice * (1 - voucherDiscount / 100) + ((totalOrderedItemsPrice * 1.12) * 0.10)
					).toFixed(2)}</p>
				</div>
				<div class="flex justify-between">
					<p class="text-sm">Amount Paid:</p>
					<span class="text-sm">₱{(parseFloat(payment) || 0)}</span>
				</div>
				<div class="flex justify-between">
					<p class="text-sm">Change:</p>
					<span class="text-sm">
						₱{Math.max(0, parseFloat(((parseFloat(payment) || 0) - ((totalOrderedItemsPrice * 1.12 || 0) + 20 + ((totalOrderedItemsPrice * 1.12) * 0.10))).toFixed(2)))}
					</span>
				</div>
				<div class="flex justify-between">
					<p class="text-sm">Service Charge (SC):</p>
					<span class="text-sm">₱{((totalOrderedItemsPrice * 1.12) * 0.10).toFixed(2)}</span>
				</div>
				<div class="flex justify-between">
					<p class="text-sm">Vatable Sales (V):</p>
					<span class="text-sm">₱{(totalOrderedItemsPrice * 1.12).toFixed(2)}</span>
				</div>
				<div class="flex justify-between">
					<p class="text-sm">VAT (12%):</p>
					<span class="text-sm">₱{((totalOrderedItemsPrice * 0.12) || 0).toFixed(2)}</span>
				</div>
				<div class="flex justify-between">
					<p class="text-sm">VAT Exempt Sales:</p>
					<span class="text-sm">₱0.00</span>
				</div>
				<div class="flex justify-between">
					<p class="text-sm">VAT Zero-Rated Sales:</p>
					<span class="text-sm">₱0.00</span>
				</div>
			</div>

			<div class="mt-4 flex justify-center space-x-4">
				<button on:click={printReceipt} class="rounded-md bg-blue-500 px-4 py-2 text-white transition hover:bg-blue-600">Checkout Bill</button>
				<button on:click={() => isReceiptPopupVisible = false} class="rounded-md bg-red-500 px-4 py-2 text-white transition hover:bg-red-600">Cancel</button>
			</div>
			<p class="text-center mt-4 text-sm text-gray-600">Thank you for having your meal with us!</p>
		</div>
	</div>
{/if}