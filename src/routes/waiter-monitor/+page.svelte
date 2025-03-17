<script lang="ts">
	import Card from '../components/card.svelte';
	import Sidebar from '../sidebar/+page.svelte';
	import { orderedItemsStore } from '../../stores/orderedItemsStore';
	import { onMount, onDestroy } from 'svelte';
	import { handleButtonClick } from '../../utils/buttonHandler'; // Import the reusable function
	import { currentInputStore } from '../../stores/currentInputStore'; // Import the store

	let cardData: MenuItem[] = [];
	let amountPaid = '₱0.00';
	let isDineIn = false;
	let isTakeOut = false;
	let cashierName = '';
	let staffToken: string | null = null; // Declare a variable to hold the staff_token
	let currentTime: string;
	let currentDay: string;
	let isSleepActive = false; // Ensure this line exists

	let debounceTimeout: NodeJS.Timeout;
	let refreshInterval: NodeJS.Timeout;

	let isFetching = false; // Flag to prevent multiple fetch calls

	let tableStatus: { [key: string]: boolean } = {};

	let reservedTables: string[] = [];

	let selectedTableNumber: string = ''; // Declare the variable

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

	async function fetchMenu() {
		const response = await fetch(
			'http://localhost/kaperustiko-possystem/backend/modules/get.php?action=getMenu'
		);
		if (response.ok) {
			cardData = await response.json();
		} else {
			console.error('Failed to fetch menu items');
		}
	}

	let orderedItems: OrderedItem[] = [];

	// Function to fetch orders
	async function fetchOrders() {
		if (isFetching) return; // Prevent multiple fetch calls
		isFetching = true; // Set fetching flag

		const response = await fetch(
			'http://localhost/kaperustiko-possystem/backend/modules/get.php?action=getOrders'
		);
		if (response.ok) {
			const textResponse = await response.text(); // Get response as text
			try {
				const orders = JSON.parse(textResponse); // Attempt to parse JSON
				orderedItemsStore.set(orders);
				orderedItems = orders; // Update the orderedItems state
			} catch (error) {
				console.error('Failed to parse JSON:', error, 'Response:', textResponse);
			}
		} else {
			console.error('Failed to fetch orders:', response.statusText);
		}

		isFetching = false; // Reset fetching flag
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
		fetchMenu();
		fetchOrders();
		fetchQueuedOrders();
		fetchCashierName(); // Automatically fetch cashier name on mount
		// Retrieve ordered items from localStorage
		const storedItems = localStorage.getItem('orderedItems');
		if (storedItems) {
			orderedItems = JSON.parse(storedItems); // Parse and set orderedItems
		}
		// Start refreshing orders every 500 ms
		refreshInterval = setInterval(fetchOrders, 500); 
		refreshInterval = setInterval(fetchQueuedOrders, 500); // Set interval for refreshing orders
		fetchTableStatus();
		fetchReserveTables();
	});

	onDestroy(() => {
		clearInterval(refreshInterval); // Clear the interval on component destroy
	});

	let orderNumber = '';
	let totalCost = '₱00.00';
	let selectedCategory = 'All';
	let payment = '';
	let quantity = 1;
	let isPopupVisible = false;
	let isVariationVisible = false;
	let selectedItem: MenuItem | null = null;
	let selectedItemDetails: {
		title: string;
		price: string;
		size: string;
		quantity: number;
		addons: string[];
	} | null = null;

	type OrderedItem = {
		order_name: string;
		order_name2: string;
		order_price: number;
		order_size: string;
		order_quantity: number;
		order_addons: string;
		order_addons_price: number;
		order_addons2: string;
		order_addons_price2: number;
		order_addons3: string;
		order_addons_price3: number;
		basePrice: number;
		code: string;
	};

	let selectedAddons: string[] = [];
	let displayedPrice = selectedItem
		? formatPrice(
				quantity *
					(parseFloat((selectedItem as MenuItem).price1.replace('₱', '').replace(',', '')) +
						parseFloat(calculateAddonsPrice(selectedAddons).replace('₱', '').replace(',', '')))
			)
		: '';
	let selectedSize = 'Regular';
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


	function increaseQuantity() {
		quantity += 1;
	}

	function decreaseQuantity() {
		if (quantity > 0) {
			quantity -= 1;
		}
	}

	fetch('http://localhost/kaperustiko-possystem/backend/modules/get.php?action=getTotalOrders')
		.then((response) => response.json())
		.then((data) => {
			orderNumber = `#${data.total_order.toString().padStart(2, '0')}`; // Set order number based on fetched data
			console.log('Order Number:', orderNumber); // Log the order number
		})
		.catch((error) => {
			console.error('Failed to fetch total orders:', error);
		});

    function closePopup() {
		isPopupVisible = false;
		isVariationVisible = false;
	}


	function handlePlaceOrder() {
		// Check if total cost is null or zero
		if (orderedItems.length === 0 || 
			orderedItems.reduce((total, item) => total + (item.order_price || 0), 0) === 0) { // Allow null order_price
			showAlert('Cannot place order. Total cost is null or zero.', 'error'); // Use showAlert for
			return; // Exit the function
		}
		// Fetch cashier name when place order is clicked
		fetchCashierName(); // Call to fetch cashier name
		// Log the codes of all ordered items
		console.log(
			'Ordered Item Codes:',
			orderedItems.map((item) => item.code)
		); // Log the codes
		
		// Fetch the total order count from the database
		saveQueOrder();
	}


	function saveQueOrder() {
		// Prepare the receipt data
		const receiptData = {
			receiptNumber: parseInt(orderNumber.replace('#', '')), // Convert to integer
			date: new Date().toLocaleDateString(),
			time: new Date().toLocaleTimeString(),
			cashierName: cashierName,
			table_number: selectedTableNumber, // Use the selected table number
			itemsOrdered: orderedItems.map((item) => ({
				order_name: item.order_name,
				order_name2: item.order_name2,
				order_quantity: 'x' + item.order_quantity,
				order_size: item.order_size,
				order_addons: item.order_addons !== 'None' ? item.order_addons : undefined,
				order_addons_price: item.order_addons_price || 0,
				order_addons2: item.order_addons2 !== 'None' ? item.order_addons2 : undefined,
				order_addons_price2: item.order_addons_price2 || 0,
				order_addons3: item.order_addons3 !== 'None' ? item.order_addons3 : undefined,
				order_addons_price3: item.order_addons_price3 || 0,
				basePrice: item.basePrice // Include the base price of the item
			})),
			totalAmount: Math.round(
				orderedItems.reduce(
					(total, item) =>
						total + parseFloat(item.order_price.toString().replace('₱', '').replace(',', '')),
					0
				)
			),
			amountPaid: payment ? Math.round(parseFloat(payment.replace('₱', '').replace(',', ''))) : 0,
			change:
				orderedItems.length > 0 && payment
					? Math.round(
							parseFloat(payment.replace('₱', '').replace(',', '')) -
								orderedItems.reduce(
									(total, item) =>
										total +
										parseFloat(item.order_price.toString().replace('₱', '').replace(',', '')),
									0
								)
						)
						: 0,
			order_take: isDineIn ? 'Dine In' : 'Take Out', // Ensure this key matches
			saveQueOrder: true // Add this line to indicate saving to que_orders
		};

		// Log the receipt data
		console.log('Receipt Data:', receiptData); // Log all data when save order is clicked

		// Now delete all orders before saving the receipt
		// Call updateQuantity for each ordered item
		orderedItems.forEach((item) => {
			const code = item.code;
			console.log('Sending code to PHP:', code); // Log the code being sent
			// Prioritize deleting quantity before saving receipt
			fetch(`http://localhost/kaperustiko-possystem/backend/modules/qty_data.php?code=${code}&order_quantity=${item.order_quantity}`, {
				method: 'GET'
			})
				.then((response) => response.json())
				.then((data) => {
					if (data.status === 'success') {
						console.log(data.message); // Log success message
						fetchOrders(); // Refresh orders to reflect updated quantities
					} else {
						console.error(data.message); // Log error message
					}
				})
				.catch((error) => {
					console.error('Error updating quantity:', error);
				});
		});

		// Now delete all orders before saving the receipt
		fetch(
			'http://localhost/kaperustiko-possystem/backend/modules/delete.php?action=deleteAllOrders',
			{
				method: 'DELETE' // Assuming you have a DELETE endpoint
			}
		).then(async (deleteResponse) => {
			if (!deleteResponse.ok) {
				const text = await deleteResponse.text();
				throw new Error(`Failed to delete orders: ${text}`);
			}
			console.log('All orders deleted successfully');

			// Check if we are saving to que_orders
			if (receiptData.saveQueOrder) {
				// Send data to the server to save the receipt in que_orders
				const saveResponse = await fetch(
					'http://localhost/kaperustiko-possystem/backend/modules/save_que_order.php',
					{
						method: 'POST',
						headers: {
							'Content-Type': 'application/json'
						},
						body: JSON.stringify(receiptData) // Ensure this is correctly formatted
					}
				);

				const textResponse = await saveResponse.text(); // Get the response as text
				console.log('Response from save_receipt.php:', textResponse); // Log the response

				if (!saveResponse.ok) {
					throw new Error(`Failed to save receipt: ${textResponse}`);
				}
				// Show success alert
				showAlert('Order Success', 'success'); // Call showAlert with success type
			} else {
				// Logic for saving to total_sales if needed
			}
		});

		// Reset the receipt data
		orderNumber = '#01';
		totalCost = '₱00.00';
		selectedCategory = 'All';
		payment = '';
		quantity = 1;
		isPopupVisible = false;
		isVariationVisible = false;
		selectedItem = null;
		selectedItemDetails = null;
		orderedItems = [];
		selectedSize = 'Regular';
		selectedAddons = [];
		voidIndex = null;
		inputCode = '123456';

		// Reset all numbers in local storage, except for staff_token
		const staffToken = localStorage.getItem('staff_token'); // Preserve staff_token
		localStorage.clear(); // Clear all numbers in local storage
		if (staffToken) {
			localStorage.setItem('staff_token', staffToken); // Restore staff_token
		}

		// window.location.reload(); // Removed the refresh call
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

	function formatPrice(price: number): string {
		return price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
	}

	function handleAdd(item: MenuItem) {
		selectedItem = item;
		console.log('Selected Item Details:', selectedItem);
		isVariationVisible = true;
	}

	function handleOrder(item: MenuItem) {
		selectedItem = item;
		const addonsPrice = calculateAddonsPrice(selectedAddons); // Calculate addons price
		const basePrice =
			selectedSize === 'Regular'
				? parseFloat(item.price1.replace('₱', '').replace(',', ''))
				: selectedSize === 'Large'
					? parseFloat(item.price2.replace('₱', '').replace(',', ''))
					: parseFloat(item.price3.replace('₱', '').replace(',', '')); // Adjust for Family size

		const totalAddonsPrice = selectedAddons.reduce((total, addon) => {
			const addonPrice =
				parseFloat(calculateAddonsPrice([addon]).replace('₱', '').replace(',', '')) || 0; // Calculate price for each addon
			return total + addonPrice; // Sum up the prices
		}, 0); // Initialize total to 0

		const newItem = {
			title: item.title1,
			price: formatPrice(quantity * (basePrice + totalAddonsPrice)), // Correctly calculate total price
			originalPrice: formatPrice(basePrice),
			size: selectedSize,
			quantity: quantity,
			addons: selectedAddons,
			basePrice: basePrice, // Add basePrice to the newItem
			addonsPrices: selectedAddons
				.map((addon) => {
					const price = calculateAddonsPrice([addon]); // Get individual addon price
					return `${addon} - ₱${price}`; // Format as "Addon - Price"
				})
				.join(', '), // Join the prices into a single string
			code: item.code
		};
		orderedItems = [...orderedItems];

		// Prepare data to send to the server
		type OrderData = {
			order_name: string;
			order_name2: string;
			order_quantity: number;
			order_size: string;
			order_price: number;
			order_image: string;
			order_addons?: string; // Add optional properties for addons
			order_addons_price?: number;
			order_addons2?: string;
			order_addons_price2?: number;
			order_addons3?: string;
			order_addons_price3?: number;
			basePrice: number; // Add basePrice to the OrderData type
			code: string;
			table_number: string;
		};

		const currentAddonsPrice = calculateAddonsPrice(selectedAddons); // Renamed variable
		const totalOrderPrice = basePrice * quantity + totalAddonsPrice; // Calculate total price

		// Update the orderData to include the correct order_price
		const orderData: OrderData = {
			order_name: item.title1,
			order_name2: item.title2,
			order_quantity: quantity,
			order_size: selectedSize,
			order_price: totalOrderPrice, // Calculate total price as a number
			order_image: item.image,
			basePrice: basePrice, // Change this line to reflect the original price
			// Add-ons handling
			order_addons: selectedAddons.length > 0 ? selectedAddons[0] : 'None',
			order_addons_price:
				selectedAddons.length > 0
					? parseFloat(calculateAddonsPrice([selectedAddons[0]]).replace('₱', '').replace(',', ''))
					: 0,
			order_addons2: selectedAddons.length > 1 ? selectedAddons[1] : 'None',
			order_addons_price2:
				selectedAddons.length > 1
					? parseFloat(calculateAddonsPrice([selectedAddons[1]]).replace('₱', '').replace(',', ''))
					: 0,
			order_addons3: selectedAddons.length > 2 ? selectedAddons[2] : 'None',
			order_addons_price3:
				selectedAddons.length > 2
					? parseFloat(calculateAddonsPrice([selectedAddons[2]]).replace('₱', '').replace(',', ''))
					: 0,
			code: item.code,
			table_number: selectedTableNumber // Add table number to order data
		};

		console.log('Order Data:', orderData); // Log the order data

		// Save the order to the database
		fetch('http://localhost/kaperustiko-possystem/backend/modules/insert.php?action=save_order', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json'
			},
			body: JSON.stringify(orderData)
		})
			.then((response) => {
				if (!response.ok) {
					return response.text().then((text) => {
						throw new Error(text);
					});
				}
				return response.json();
			})
			.then((data) => {
				console.log(data.message);
			})
			.catch((error) => {
				console.error('Error saving order:', error);
			});

		fetchOrders();

		selectedItem = null;
		selectedSize = 'Regular';
		selectedAddons = [];
		quantity = 1; // Reset quantity to 1
		closePopup();

		// Save the order to localStorage
		localStorage.setItem('orderedItems', JSON.stringify(orderedItems)); // Store orderedItems in localStorage
	}

	function calculateAddonsPrice(addons: string[]): string {
		// Define prices for each addon
		const addonPrices: { [key: string]: number } = {
			Sugar: 5,
			Bobba: 10,
			Milk: 7,
			'Extra Cheese': 15,
			Bacon: 20,
			Olives: 10,
			Rice: 10
		};

		// Return a string of addon prices
		return addons.map((addon) => `${addonPrices[addon] || 0}`).join(', ');
	}

	function selectSize(size: string) {
		selectedSize = size;
		if (selectedItem) {
			if (size === 'Regular' || size === '0.25L') {
				displayedPrice = formatPrice(
					quantity * parseFloat(selectedItem.price1.replace('₱', '').replace(',', ''))
				);
			} else if (size === 'Large' || size === '0.33L') {
				displayedPrice = formatPrice(
					quantity * parseFloat(selectedItem.price2.replace('₱', '').replace(',', ''))
				);
			} else if (size === 'Family' || size === '1.5L') {
				displayedPrice = formatPrice(
					quantity * parseFloat(selectedItem.price3.replace('₱', '').replace(',', ''))
				);
			}
		}
	}

	$: {
		if (selectedItem) {
			let price = 0;
			if (selectedSize === 'Regular' || selectedSize === '0.25L') {
				price = parseFloat(selectedItem.price1.replace('₱', '').replace(',', ''));
			} else if (selectedSize === 'Large' || selectedSize === '0.33L') {
				price = parseFloat(selectedItem.price2.replace('₱', '').replace(',', ''));
			} else if (selectedSize === 'Family' || selectedSize === '1.5L') {
				price = parseFloat(selectedItem.price3.replace('₱', '').replace(',', ''));
			}
			displayedPrice = formatPrice(quantity * price);
		}
	}

	function voidOrder(index: number) {
		voidIndex = index;
		isCodePopupVisible = true;
	}

	function confirmVoid() {
		if (inputCode.length === 6) {
			const orderToVoid = orderedItems[voidIndex!]; // Get the order to void
			orderedItems.splice(voidIndex!, 1); // Remove from local array
			localStorage.setItem('orderedItems', JSON.stringify(orderedItems)); // Update localStorage after voiding

			// Send request to backend to delete the order
			fetch(
				`http://localhost/kaperustiko-possystem/backend/modules/delete.php?action=voidOrder&order_name=${encodeURIComponent(orderToVoid.order_name)}`,
				{
					method: 'DELETE'
				}
			)
				.then((response) => response.json())
				.then((data) => {
					console.log('Order voided:', data);
				})
				.catch((error) => {
					console.error('Error voiding order:', error);
				});

			voidIndex = null;
			inputCode = '';
			isCodePopupVisible = false;
		} else {
			alert('Please enter a valid 6-digit code.');
		}

		// window.location.reload(); // Removed the refresh call
	}

	function closeCodePopup() {
		isCodePopupVisible = false;
		inputCode = '';
	}

	function handleBackspace() {
		payment = payment.slice(0, -1);
	}

	function handleClear() {
		payment = '';
	}

	function handleNumberInput(num: string) {
		payment += num;
	}

	// Define a type for the queued orders
	type QueuedOrder = {
		table_number: string;
		receipt_number: string;
		total_amount: number;
		amount_paid: number;
		order_status: string;
	};

	// Use the defined type for queuedOrders
	let queuedOrders: QueuedOrder[] = [];

	async function fetchQueuedOrders() {
		const response = await fetch('http://localhost/kaperustiko-possystem/backend/modules/get.php?action=getQueOrders');
		if (response.ok) {
			queuedOrders = await response.json();
		} else {
			console.error('Failed to fetch queued orders', response.statusText); // Improved error logging
		}
	}

	async function fetchTableStatus() {
		const response = await fetch('http://localhost/kaperustiko-possystem/backend/modules/check_tables.php');
		if (response.ok) {
			tableStatus = await response.json();
		} else {
			console.error('Failed to fetch table status');
		}
	}

	// Function to fetch reserve tables
	async function fetchReserveTables() {
		const response = await fetch('http://localhost/kaperustiko-possystem/backend/modules/get.php?action=getReserveTables');
		if (response.ok) {
			const data = await response.json();
			reservedTables = data.map((table: { table_number: string }) => table.table_number); // Assuming the response contains an array of reserved table objects
		} else {
			console.error('Failed to fetch reserved tables', response.statusText);
		}
	}
</script>

<div class="flex h-screen">
	<div class="flex flex-grow overflow-hidden bg-gray-100">
		<div class="flex flex-col w-[1560] overflow-auto p-4">
			<div class="mb-4 flex flex-wrap space-x-4">
				{#each ['All', 'Beverages', 'Food', 'Dessert', 'Coffee', 'Tea', 'Soda', 'Sandwich', 'Pasta', 'Burger', 'Ulam', 'Rice'] as category}
					<button
						class="rounded-md px-4 py-2 font-bold text-black transition duration-200"
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
					<p>Display All Menu</p>
				{:else if selectedCategory === 'Beverages'}
					<p>Display Beverages Menu</p>
				{:else if selectedCategory === 'Food'}
					<p>Display Food Menu</p>
				{:else if selectedCategory === 'Dessert'}
					<p>Display Dessert Menu</p>
				{:else if selectedCategory === 'Coffee'}
					<p>Display Coffee Menu</p>
				{:else if selectedCategory === 'Tea'}
					<p>Display Tea Menu</p>
				{:else if selectedCategory === 'Soda'}
					<p>Display Soda Menu</p>
				{:else if selectedCategory === 'Sandwich'}
					<p>Display Sandwich Menu</p>
				{:else if selectedCategory === 'Ulam'}
					<p>Display Ulam Menu</p>
				{:else if selectedCategory === 'Rice'}
					<p>Display Rice Menu</p>
				{:else if selectedCategory === 'Desserts'}
					<p>Display Desserts Menu</p>
				{/if}
				<p class="mr-4">{currentDay} - {currentTime}</p>
			</div>

			<div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3">
				{#each cardData.filter((item) => selectedCategory === 'All' || item.label === selectedCategory || item.label2 === selectedCategory) as { code, title1, title2, price1, price2, price3, image, menu_no, label, label2, qty }}
					<Card
						{code}
						{title1}
						{title2}
						{price1}
						{price2}
						{price3}
						{label}
						{label2}
						{image}
						{menu_no}
						{qty}
						onAdd={handleAdd}
					/>
				{/each}
			</div>
		</div>
	</div>

	<div class="flex flex-col w-[950px]">
        
		<!-- Orders Section -->
		<div class="fixed right-[350px] top-0 flex h-full w-[350px] flex-col items-center bg-gray-100 p-4 shadow-lg">
			<div class="mb-4 w-full rounded-md bg-green-800 py-2 text-center text-white">
				<p class="text-sm font-bold">Order Number {orderNumber}</p>
			</div>
			<!-- Added Table Number Dropdown -->
			<div class="mb-4 w-full">
				<label for="tableNumber" class="block text-gray-700">Table Number:</label>
				<select bind:value={selectedTableNumber} class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
					<option value="">Select Table Number</option>
					{#each Array(21) as _, index}
						<option value={index + 1} 
							class={queuedOrders.some(order => order.table_number === (index + 1).toString()) ? 'bg-red-500 text-white' : reservedTables.includes((index + 1).toString()) ? 'bg-orange-950 text-white'  : ''} 
							disabled={tableStatus[index + 1]}>
							Table {index + 1}
						</option>
					{/each}
				</select>
			</div>
			<div class="mb-4 max-h-full w-full flex-grow space-y-2 overflow-y-auto">
				{#if orderedItems.length > 0}
					{#each orderedItems as item, index}
						<div class="flex flex-col rounded-lg bg-white p-4 shadow-md">
							<div class="flex items-center justify-between">
								<p class="text-gray-600">Code: {item.code}</p>
							</div>
							<div class="flex items-center justify-between">
								<p class="font-semibold text-gray-800">{item.order_name} x {item.order_quantity}</p>
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
                <div class="grid h-[200px] w-full grid-cols-4 gap-2">
                    <button
                        class="col-span-2 rounded py-2 font-bold text-gray-800 {isDineIn
                            ? 'bg-blue-500 text-white'
                            : 'bg-gray-300'}"
                        on:click={() => {
                            isDineIn = true;
                            isTakeOut = false;
                        }}
                    >
                        Dine In
                    </button>
        
                    <button
                        class="col-span-2 rounded py-2 font-bold text-gray-800 {isTakeOut
                            ? 'bg-blue-500 text-white'
                            : 'bg-gray-300'}"
                        on:click={() => {
                            isDineIn = false;
                            isTakeOut = true;
                        }}
                    >
                        Take Out
                    </button>
        
                    {#each ['Void', 'Save Order'] as key, index}
                        <button
                            on:click={() => {
                                if (key === 'Void') {
                                    // Handle void action
                                    voidOrder(index);
                                } else if (key === 'Save Order') {
                                    // Handle save order action
									handlePlaceOrder();
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
                            class="col-span-2 rounded-lg py-2 font-bold text-white transition duration-200 
                                   {key === 'Save Order' ? 'bg-blue-600 hover:bg-blue-700' : 'bg-red-600 hover:bg-red-700'}"
                        >
                            {key}
                        </button>
                    {/each}
                </div>
			</div>
		</div>
		</div>

	<!-- Que Order Section -->
			<div class="fixed right-0 top-0 flex h-full w-[350px] flex-col items-center bg-gray-100 p-4 shadow-lg">
				<div class="mb-4 w-full rounded-md bg-green-800 py-2 text-center text-white">
					<p class="text-sm font-bold">Que Orders</p>
				</div>
				<div class="mb-4 max-h-full w-full flex-grow space-y-2 overflow-y-auto">
					{#if queuedOrders.length > 0}
						{#each queuedOrders as order}
						<div class="flex flex-col rounded-lg bg-white p-4 shadow-md">
							<div class="flex items-center justify-between">
								<p class="text-gray-600">Table No:</p>
								<span>{order.table_number}</span>
							</div>
							<div class="flex items-center justify-between">
								<p class="text-gray-600">Order No: </p>
								<span>{order.receipt_number}</span>
							</div>
							<div class="flex items-center justify-between">
								<p class="font-semibold text-gray-800">Order Total Price:</p>
								<span>₱{order.total_amount}</span>
							</div>
							<div class="flex items-center justify-between mb-2">
								<p class="text-gray-600">Amount Paid:</p>
								<span class={`font-semibold ${order.amount_paid == 0 ? 'bg-red-500 text-white rounded-md px-2 py-1 shadow-sm' : order.amount_paid > 0 ? 'bg-green-500 text-white rounded-md px-2 py-1 shadow-sm' : 'text-gray-800'}`}>
									{order.amount_paid == 0 ? 'not paid' : `paid`}
								</span>
							</div>
							<div class="flex items-center justify-between">
								<p class="text-gray-600">Status:</p>
								<span class={`font-semibold px-2 py-1 rounded-md 
									${order.order_status === 'pending' ? 'bg-red-500 text-white' : ''} 
									${order.order_status === 'processing' ? 'bg-yellow-500 text-white' : ''} 
									${order.order_status === 'done' ? 'bg-green-500 text-white' : ''}`}>
									{order.order_status}
								</span>
							</div>
						</div>
						{/each}
					{:else}
						<p class="text-center text-gray-600 text-lg">No queued orders available.</p>
					{/if}
				</div>
				<button on:click={() => isSleepActive = true} class="mt-2 w-full rounded-md bg-blue-500 px-4 py-2 text-white">
					Sleep
				</button>
			</div>
		</div>

{#if isVariationVisible}
	<div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-70">
		<div class="w-full max-w-md rounded-lg bg-white p-8 shadow-lg">
			<h2 class="mb-6 text-center text-2xl font-bold">
				Add {selectedItem?.title1}
				{selectedItem?.title2}
			</h2>
			<p class="mb-6 text-center text-lg">
				Price: ₱{displayedPrice} (Add-ons: ₱{calculateAddonsPrice(selectedAddons)})
			</p>
			{#if selectedItem?.image}
				<img
					src={`/foods/${selectedItem.image}`}
					alt={selectedItem.title1}
					class="mb-4 h-[400px] w-full rounded"
				/>
			{/if}

			<label for="size" class="mb-2 block text-sm font-medium">Size:</label>
			<div class="mb-6 flex w-full space-x-4">
				{#if selectedItem?.label === 'Soda' || selectedItem?.label2 === 'Soda'}
					<button
						on:click={() => selectSize('0.25L')}
						class="flex-1 rounded-md border border-gray-300 p-3 {selectedSize === '0.25L'
							? 'bg-blue-500 text-white'
							: ''}">0.25L</button
					>
					<button
						on:click={() => selectSize('0.33L')}
						class="flex-1 rounded-md border border-gray-300 p-3 {selectedSize === '0.33L'
							? 'bg-blue-500 text-white'
							: ''}">0.33L</button
					>
					<button
						on:click={() => selectSize('1.5L')}
						class="flex-1 rounded-md border border-gray-300 p-3 {selectedSize === '1.5L'
							? 'bg-blue-500 text-white'
							: ''}">1.5L</button
					>
				{:else}
					<button
						on:click={() => selectSize('Regular')}
						class="flex-1 rounded-md border border-gray-300 p-3 {selectedSize === 'Regular'
							? 'bg-blue-500 text-white'
							: ''}">Regular</button
					>
					<button
						on:click={() => selectSize('Large')}
						class="flex-1 rounded-md border border-gray-300 p-3 {selectedSize === 'Large'
							? 'bg-blue-500 text-white'
							: ''}">Large</button
					>
					<button
						on:click={() => selectSize('Family')}
						class="flex-1 rounded-md border border-gray-300 p-3 {selectedSize === 'Family'
							? 'bg-blue-500 text-white'
							: ''}">Family</button
					>
				{/if}
			</div>

			<label for="addons" class="mb-2 block text-sm font-medium">Add-ons:</label>
			<div class="mb-6">
				{#if selectedItem?.label === 'Tea' || selectedItem?.label2 === 'Tea'}
					<label class="mb-4 block">
						<input type="checkbox" bind:group={selectedAddons} value="Sugar" class="mr-2 h-6 w-6" />
						Sugar - ₱5
					</label>
					<label class="mb-4 block">
						<input type="checkbox" bind:group={selectedAddons} value="Bobba" class="mr-2 h-6 w-6" />
						Bobba - ₱10
					</label>
					<label class="mb-4 block">
						<input type="checkbox" bind:group={selectedAddons} value="Milk" class="mr-2 h-6 w-6" /> Milk
						- ₱7
					</label>
				{:else if selectedItem?.label === 'Pasta' || selectedItem?.label2 === 'Pasta'}
					<label class="mb-4 block">
						<input
							type="checkbox"
							bind:group={selectedAddons}
							value="Extra Cheese"
							class="mr-2 h-6 w-6"
						/> Extra Cheese - ₱15
					</label>
					<label class="mb-4 block">
						<input type="checkbox" bind:group={selectedAddons} value="Bacon" class="mr-2 h-6 w-6" />
						Bacon - ₱20
					</label>
					<label class="mb-4 block">
						<input
							type="checkbox"
							bind:group={selectedAddons}
							value="Olives"
							class="mr-2 h-6 w-6"
						/> Olives - ₱10
					</label>
				{:else if selectedItem?.label === 'Soda' || selectedItem?.label2 === 'Soda'}
					<!-- No add-ons for Soda -->
				{:else if selectedItem?.label === 'Ulam' || selectedItem?.label2 === 'Ulam'}
					<!-- No add-ons for Ulam -->
				{/if}
			</div>

			<label for="quantity" class="mb-2 block text-sm font-medium">Quantity:</label>
			<div class="mb-6 flex justify-between">
				<button on:click={decreaseQuantity} class="flex-1 rounded-md border border-gray-300 p-3"
					>-</button
				>
				<input
					type="number"
					id="quantity"
					bind:value={quantity}
					min="1"
					class="mx-2 block w-full rounded-md border border-gray-300 p-3 text-center"
				/>
				<button on:click={increaseQuantity} class="flex-1 rounded-md border border-gray-300 p-3"
					>+</button
				>
			</div>

			<div class="flex justify-between">
				<button
					on:click={closePopup}
					class="rounded-md bg-red-500 px-6 py-3 text-white transition hover:bg-red-600"
					>Cancel</button
				>
				<button
					on:click={() => selectedItem && handleOrder(selectedItem)}
					class="rounded-md bg-blue-500 px-6 py-3 text-white transition hover:bg-blue-600"
					>Add to Order</button
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

{#if isSleepActive}
	<button class="bg-cyan-950 h-screen w-full flex flex-col items-center justify-center z-50 fixed inset-0" on:click={() => isSleepActive = false} aria-label="Close Sleep Overlay">
		<ul class="circles">
			<li class="animate-pulse"></li>
			<li class="animate-pulse"></li>
			<li class="animate-pulse"></li>
			<li class="animate-pulse"></li>
			<li class="animate-pulse"></li>
			<li class="animate-pulse"></li>
			<li class="animate-pulse"></li>
			<li class="animate-pulse"></li>
			<li class="animate-pulse"></li>
		</ul>
		<div class="flex items-center justify-center w-full h-full">
			<img src="./icon.png" alt="Fallback description if image fails to load" class="max-w-full h-auto" aria-hidden="true" />
		</div>
	</button>
{/if}
