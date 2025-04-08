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

	let debounceTimeout: ReturnType<typeof setTimeout>;
	let refreshInterval: ReturnType<typeof setInterval>;

	let isFetching = false; // Flag to prevent multiple fetch calls

	let tableStatus: { [key: string]: boolean } = {};

	let reservedTables: string[] = [];

	let selectedTableNumber: string = ''; // Declare the variable
	
	let isWaiterCodePopupVisible = false; // Add this for waiter code popup
	let waiterCode = ''; // Variable to store waiter code
	let waiterName = ''; // Variable to store waiter name

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
		
		// Check if no table is selected
		if (!selectedTableNumber) {
			showAlert('Please select a table number before placing an order.', 'error'); // Alert for no table selected
			return; // Exit the function
		}
		
		// Show waiter code popup instead of directly saving order
		isWaiterCodePopupVisible = true;
	}

	function verifyWaiterCode() {
		if (waiterCode.length < 4) {
			showAlert('Please enter a valid waiter code (min 4 digits)', 'error');
			return;
		}

		// Send the waiter code to the server for verification
		fetch('http://localhost/kaperustiko-possystem/backend/modules/auth.php', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
			body: new URLSearchParams({
				'action': 'verifyWaiterCode',
				'waiter_code': waiterCode
			})
		})
		.then(response => response.json())
		.then(data => {
			if (data.status === 'success') {
				// Waiter code is valid
				console.log('Waiter verification successful:', data);
				
				// Store the waiter name from the database response
				waiterName = data.waiterName;
				
				// Hide the waiter code popup
				isWaiterCodePopupVisible = false;
				
				// Continue with saving the order
				saveQueOrder();
			} else {
				// Waiter code is invalid
				showAlert('Invalid waiter code. Please try again.', 'error');
			}
		})
		.catch(error => {
			console.error('Error verifying waiter code:', error);
			showAlert('Error verifying waiter code. Please try again.', 'error');
		});
	}

	function closeWaiterCodePopup() {
		isWaiterCodePopupVisible = false;
		waiterCode = '';
	}

	function handleWaiterCodeInput(num: string) {
		if (waiterCode.length < 6) {
			waiterCode += num;
		}
	}
	
	function handleWaiterCodeBackspace() {
		waiterCode = waiterCode.slice(0, -1);
	}
	
	function handleWaiterCodeClear() {
		waiterCode = '';
	}

	function saveQueOrder() {
		// Prepare the receipt data
		const receiptData = {
			receiptNumber: parseInt(orderNumber.replace('#', '')), // Convert to integer
			date: new Date().toLocaleDateString(),
			time: new Date().toLocaleTimeString(),
			cashierName: cashierName,
			waiterName: waiterName, // Add waiter name
			waiterCode: waiterCode, // Add waiter code
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

		console.log('Sending order data with waiter:', waiterName, waiterCode);

		// Send data to the server
		fetch('http://localhost/kaperustiko-possystem/backend/modules/save_que_order.php', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json'
			},
			body: JSON.stringify(receiptData)
		})
			.then((response) => {
				if (!response.ok) {
					return response.text().then(text => {
						throw new Error('Server response not OK: ' + text);
					});
				}
				return response.json();
			})
			.then((data) => {
				if (data.error) {
					console.error('Error saving order:', data.error);
					showAlert(data.error, 'error');
					return;
				}
				
				console.log('Order saved:', data);
				showAlert('Order queued successfully!', 'success');
				
				// After successful order, update table status
				fetchTableStatus();
				
				// Call updateQuantity for each ordered item
				orderedItems.forEach((item) => {
					const code = item.code;
					console.log('Updating inventory for:', code);
					fetch(`http://localhost/kaperustiko-possystem/backend/modules/qty_data.php?code=${code}&order_quantity=${item.order_quantity}`, {
						method: 'GET'
					})
						.then((response) => response.json())
						.then((data) => {
							if (data.status === 'success') {
								console.log('Inventory updated:', data.message);
							} else {
								console.error('Inventory update error:', data.message);
							}
						})
						.catch((error) => {
							console.error('Error updating quantity:', error);
						});
				});
				
				// Now delete all orders from the orders table
				fetch('http://localhost/kaperustiko-possystem/backend/modules/delete.php?action=deleteAllOrders', {
					method: 'DELETE'
				})
					.then(response => response.json())
					.then(data => {
						console.log('Orders cleared:', data);
					})
					.catch(error => {
						console.error('Error clearing orders:', error);
					});
				
				// Clear ordered items
				orderedItems = [];
				// Reset waiter code
				waiterCode = '';
				waiterName = '';
				// Reset table number selection
				selectedTableNumber = '';
				// Reset payment
				payment = '';
			})
			.catch((error) => {
				console.error('Error:', error);
				showAlert('Failed to queue order. Please try again.', 'error');
			});
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
		const orderToVoid = orderedItems[index]; // Get the order to void
		orderedItems.splice(index, 1); // Remove from local array
		localStorage.setItem('orderedItems', JSON.stringify(orderedItems)); // Update localStorage after voiding

		// Send request to backend to delete the order - include both code and size to identify the specific item
		fetch(
			`http://localhost/kaperustiko-possystem/backend/modules/delete.php?action=voidOrder&order_name=${encodeURIComponent(orderToVoid.order_name)}&order_size=${encodeURIComponent(orderToVoid.order_size)}&index=${index}`,
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
		waiter_name?: string; // Add waiter name field
		waiter_code?: string; // Add waiter code field
	};

	// Define a type for table order items
	type TableOrderItem = {
		receipt_number: string;
		order_name: string;
		order_size: string;
		order_quantity: number;
		order_status: string;
		waiter_name?: string;
		order_addons: string;
		order_addons_price: number;
		order_addons2: string;
		order_addons_price2: number;
		order_addons3: string;
		order_addons_price3: number;
		item_total_price: number;
		delivered: boolean;
	};

	// Use the defined type for queuedOrders
	let queuedOrders: QueuedOrder[] = [];
	
	// Add state for table details modal
	let isTableDetailsModalVisible = false;
	let selectedTableDetails: { tableNumber: string; orders: QueuedOrder[] } = { tableNumber: '', orders: [] };
	let selectedTableItems: TableOrderItem[] = [];

	async function fetchQueuedOrders() {
		const response = await fetch('http://localhost/kaperustiko-possystem/backend/modules/get.php?action=getQueOrders');
		if (response.ok) {
			queuedOrders = await response.json();
		} else {
			console.error('Failed to fetch queued orders', response.statusText); // Improved error logging
		}
	}

	// Function to open table details modal
	function openTableDetails(tableNumber: string) {
		selectedTableDetails.tableNumber = tableNumber;
		selectedTableDetails.orders = queuedOrders.filter(order => order.table_number === tableNumber);
		
		// Fetch detailed items for this table
		fetchTableOrderDetails(tableNumber);
	}
	
	// Function to fetch detailed order items for a table
	async function fetchTableOrderDetails(tableNumber: string) {
		try {
			const response = await fetch(`http://localhost/kaperustiko-possystem/backend/modules/get.php?action=getTableOrderDetails&table_number=${tableNumber}`);
			if (response.ok) {
				const data = await response.json();
				console.log('Table order details:', data); // Log for debugging
				// Add delivered property to each item
				selectedTableItems = data.map((item: any) => ({
					...item,
					delivered: item.delivered === "1" || false
				}));
				isTableDetailsModalVisible = true;
			} else {
				console.error('Failed to fetch table order details');
				showAlert('Failed to fetch order details', 'error');
			}
		} catch (error) {
			console.error('Error fetching table order details:', error);
			showAlert('Error loading order details', 'error');
		}
	}
	
	// Function to toggle delivery status of an item
	async function toggleDeliveryStatus(item: TableOrderItem) {
		const originalDelivered = item.delivered;
		item.delivered = !item.delivered;
		
		try {
			const payload = {
				receipt_number: item.receipt_number,
				order_name: item.order_name,
				order_size: item.order_size,
				delivered: item.delivered ? "1" : "0"
			};
			
			console.log('Sending delivery status update:', payload);
			
			const response = await fetch(`http://localhost/kaperustiko-possystem/backend/modules/update.php?action=updateDeliveryStatus`, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json'
				},
				body: JSON.stringify(payload)
			});
			
			console.log('Response status:', response.status, response.statusText);
			const responseText = await response.text();
			console.log('Response text:', responseText);
			
			let result;
			try {
				result = JSON.parse(responseText);
				console.log('Parsed result:', result);
			} catch (e) {
				console.error('Failed to parse JSON response:', e);
				showAlert('Error parsing server response', 'error');
				item.delivered = originalDelivered; // Revert to original state
				return;
			}
			
			if (result.success) {
				let message = item.delivered ? 'Item marked as delivered' : 'Item unmarked as delivered';
				
				// If the status was changed to 'done', update all items with this receipt number
				if (result.status_changed) {
					message += ' and order marked as DONE';
					// Update all items with the same receipt number to show the new status
					selectedTableItems.forEach(i => {
						if (i.receipt_number === item.receipt_number) {
							i.order_status = result.new_status;
						}
					});
					
					// Also update the order in the queuedOrders list
					const orderToUpdate = queuedOrders.find(order => order.receipt_number === item.receipt_number);
					if (orderToUpdate) {
						orderToUpdate.order_status = result.new_status;
					}
					
					// Force a UI update by triggering a state change
					selectedTableItems = [...selectedTableItems];
					queuedOrders = [...queuedOrders];
				}
				
				showAlert(message, 'success');
			} else {
				console.error('Server returned error:', result.message);
				
				// If we got an "Item not found" error with available items, show a more helpful message
				if (result.message && result.message.includes('Item not found in order') && result.available_items) {
					console.log('Available items:', result.available_items);
					
					// Create a more detailed error message
					let errorMessage = 'Item not found in order. ';
					if (result.available_items.length > 0) {
						errorMessage += `Available items: ${result.available_items.join(', ')}`;
					}
					
					showAlert(errorMessage, 'error');
					
					// Try to refresh the table data, there might be a mismatch
					setTimeout(() => fetchTableOrderDetails(selectedTableDetails.tableNumber), 1000);
				} else {
					// Generic error
					showAlert(result.message || 'Failed to update delivery status', 'error');
				}
				
				// Revert the change if update failed
				item.delivered = originalDelivered;
			}
		} catch (error) {
			console.error('Network error updating delivery status:', error);
			showAlert('Error communicating with server', 'error');
			// Revert the change if update failed
			item.delivered = originalDelivered;
		}
	}
	
	// Function to close table details modal
	function closeTableDetailsModal() {
		isTableDetailsModalVisible = false;
		selectedTableItems = [];
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
				<select 
					bind:value={selectedTableNumber} 
					class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
				>
					<option value="">Select Table Number</option>
					{#each Array.from({length: 21}, (_, i) => (i + 1).toString()) as tableNum}
						{@const isOccupied = tableStatus[tableNum] === true}
						{@const isReserved = reservedTables.includes(tableNum)}
						<option 
							value={tableNum} 
							disabled={isReserved} 
							class={isOccupied ? 'bg-yellow-300 text-black' : isReserved ? 'bg-red-500 text-white' : ''}
						>
							Table {tableNum} {isOccupied ? '(has orders)' : isReserved ? '(reserved)' : ''}
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
						<!-- Group orders by table -->
						{#each [...new Set(queuedOrders.map(order => order.table_number))].sort((a, b) => Number(a) - Number(b)) as tableNum}
							<div class="rounded-lg bg-blue-100 p-2 mb-3">
								<div class="flex items-center justify-between bg-blue-500 text-white p-2 rounded-t-lg">
									<h3 class="font-bold">Table {tableNum}</h3>
									<span class="bg-white text-blue-500 px-2 py-1 rounded-full text-xs font-bold">
										{queuedOrders.filter(order => order.table_number === tableNum).length} orders
									</span>
								</div>
								
								<!-- Make the whole table section clickable -->
								<div 
									class="p-2 bg-white rounded-b-lg cursor-pointer hover:bg-blue-50 transition-colors"
									on:click={() => openTableDetails(tableNum)}
								>
									<p class="text-center text-blue-600 font-semibold mb-1">Click to view orders</p>
									
									{#each queuedOrders.filter(order => order.table_number === tableNum) as order}
									<div class="flex flex-col rounded-lg bg-white p-4 shadow-md mt-2">
										<div class="flex items-center justify-between">
											<p class="text-gray-600">Order No: </p>
											<span>{order.receipt_number}</span>
										</div>
										{#if order.waiter_name}
										<div class="flex items-center justify-between">
											<p class="text-gray-600">Waiter:</p>
											<span>{order.waiter_name}</span>
										</div>
										{/if}
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

{#if isWaiterCodePopupVisible}
	<div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-70">
		<div class="w-full max-w-md rounded-lg bg-white p-8 shadow-lg">
			<h2 class="mb-4 text-center text-2xl font-bold">Enter Waiter Code</h2>
			<p class="mb-4 text-center text-gray-600">Please enter your waiter code to process this order</p>
			<div
				class="mb-6 w-full rounded border border-gray-300 p-4 text-center text-4xl font-bold h-20 flex items-center justify-center"
			>
				{waiterCode ? '*'.repeat(waiterCode.length) : ''}
			</div>
			
			<!-- Numeric Keypad -->
			<div class="mb-6">
				<!-- Backspace and Clear buttons above the numeric keypad -->
				<div class="mb-4 flex justify-between gap-2">
					<button
						on:click={handleWaiterCodeClear}
						class="flex-1 flex h-16 items-center justify-center rounded-md bg-yellow-500 text-xl font-bold text-white"
					>
						Clear
					</button>
					<button
						on:click={handleWaiterCodeBackspace}
						class="flex-1 flex h-16 items-center justify-center rounded-md bg-red-500 text-xl font-bold text-white"
					>
						⌫
					</button>
				</div>
				
				<!-- Numbers row -->
				<div class="mb-4 flex justify-center gap-2">
					{#each [1, 2, 3, 4, 5, 6, 7, 8, 9, 0] as num}
						<button
							on:click={() => handleWaiterCodeInput(num.toString())}
							class="flex h-16 w-16 items-center justify-center rounded-md bg-gray-200 text-2xl font-bold hover:bg-gray-300"
						>
							{num}
						</button>
					{/each}
				</div>
			</div>

			<!-- Letter Keypad in QWERTY layout -->
			<div class="mt-6">
				<!-- First row -->
				<div class="mb-2 flex justify-center gap-1">
					{#each ['Q','W','E','R','T','Y','U','I','O','P'] as letter}
						<button
							on:click={() => handleWaiterCodeInput(letter)}
							class="flex h-16 w-11 items-center justify-center rounded-md bg-blue-100 text-xl font-bold hover:bg-blue-200"
						>
							{letter}
						</button>
					{/each}
				</div>
				
				<!-- Second row -->
				<div class="mb-2 flex justify-center gap-1">
					{#each ['A','S','D','F','G','H','J','K','L'] as letter}
						<button
							on:click={() => handleWaiterCodeInput(letter)}
							class="flex h-16 w-11 items-center justify-center rounded-md bg-blue-100 text-xl font-bold hover:bg-blue-200"
						>
							{letter}
						</button>
					{/each}
				</div>
				
				<!-- Third row -->
				<div class="mb-2 flex justify-center gap-1">
					{#each ['Z','X','C','V','B','N','M'] as letter}
						<button
							on:click={() => handleWaiterCodeInput(letter)}
							class="flex h-16 w-11 items-center justify-center rounded-md bg-blue-100 text-xl font-bold hover:bg-blue-200"
						>
							{letter}
						</button>
					{/each}
				</div>
			</div>
			
			<div class="mt-6 flex justify-between">
				<button on:click={closeWaiterCodePopup} class="rounded-md bg-red-500 px-8 py-4 text-xl font-bold text-white"
					>Cancel</button
				>
				<button on:click={verifyWaiterCode} class="rounded-md bg-blue-500 px-8 py-4 text-xl font-bold text-white"
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

<!-- Table Details Modal -->
{#if isTableDetailsModalVisible}
    <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-70 z-50">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] overflow-hidden flex flex-col">
            <!-- Modal Header -->
            <div class="bg-blue-600 text-white px-6 py-4 flex justify-between items-center">
                <h2 class="text-2xl font-bold">Table {selectedTableDetails.tableNumber} Orders</h2>
                <button 
                    on:click={closeTableDetailsModal}
                    class="bg-blue-700 hover:bg-blue-800 rounded-full p-2 focus:outline-none"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Modal Body -->
            <div class="p-6 overflow-y-auto flex-grow">
                {#if selectedTableItems.length > 0}
                    <!-- Group items by receipt number -->
                    {#each [...new Set(selectedTableItems.map(item => item.receipt_number))].sort() as receiptNumber}
                        <div class="mb-8 border border-gray-200 rounded-lg shadow-md">
                            <div class="bg-gray-100 px-4 py-3 border-b rounded-t-lg">
                                <div class="flex justify-between items-center">
                                    <h3 class="text-lg font-bold text-gray-800">Order #{receiptNumber}</h3>
                                    <div>
                                        {#if selectedTableItems.find(item => item.receipt_number === receiptNumber)?.waiter_name}
                                            <span class="text-sm text-gray-600 mr-4">
                                                Waiter: {selectedTableItems.find(item => item.receipt_number === receiptNumber)?.waiter_name}
                                            </span>
                                        {/if}
                                        <span class={`px-3 py-1 rounded-full text-xs font-bold 
                                            ${selectedTableItems.find(item => item.receipt_number === receiptNumber)?.order_status === 'pending' ? 'bg-red-500 text-white' : ''} 
                                            ${selectedTableItems.find(item => item.receipt_number === receiptNumber)?.order_status === 'processing' ? 'bg-yellow-500 text-white' : ''} 
                                            ${selectedTableItems.find(item => item.receipt_number === receiptNumber)?.order_status === 'done' ? 'bg-green-500 text-white' : ''}`}
                                        >
                                            {selectedTableItems.find(item => item.receipt_number === receiptNumber)?.order_status}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="p-4">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Size</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Addons</th>
                                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Delivered</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            {#each selectedTableItems.filter(item => item.receipt_number === receiptNumber) as item}
                                                <tr class={`hover:bg-gray-50 ${item.delivered ? 'bg-green-50' : ''}`}>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{item.order_name}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{item.order_size}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{item.order_quantity}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        {#if item.order_addons !== 'None' || item.order_addons2 !== 'None' || item.order_addons3 !== 'None'}
                                                            <ul class="list-disc pl-5">
                                                                {#if item.order_addons && item.order_addons !== 'None'}<li>{item.order_addons} (+₱{item.order_addons_price})</li>{/if}
                                                                {#if item.order_addons2 && item.order_addons2 !== 'None'}<li>{item.order_addons2} (+₱{item.order_addons_price2})</li>{/if}
                                                                {#if item.order_addons3 && item.order_addons3 !== 'None'}<li>{item.order_addons3} (+₱{item.order_addons_price3})</li>{/if}
                                                            </ul>
                                                        {:else}
                                                            None
                                                        {/if}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 font-semibold">₱{item.item_total_price}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                                        <div class="flex items-center justify-center">
                                                            <input 
                                                                type="checkbox" 
                                                                checked={item.delivered} 
                                                                on:change={() => toggleDeliveryStatus(item)}
                                                                class="h-5 w-5 text-blue-600 rounded focus:ring-blue-500"
                                                                aria-label="Mark as delivered"
                                                            />
                                                            <span class="ml-2 text-sm text-gray-500 sr-only">
                                                                {item.delivered ? 'Delivered' : 'Not delivered'}
                                                            </span>
                                                        </div>
                                                    </td>
                                                </tr>
                                            {/each}
                                            <!-- Total row -->
                                            <tr class="bg-gray-50">
                                                <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 text-right">Total:</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-right text-gray-900">
                                                    ₱{selectedTableItems
                                                        .filter(item => item.receipt_number === receiptNumber)
                                                        .reduce((total, item) => total + parseFloat(item.item_total_price.toString()), 0)
                                                        .toFixed(2)}
                                                </td>
                                                <td></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    {/each}
                {:else}
                    <p class="text-center text-gray-500 py-8">No detailed order information available for this table.</p>
                {/if}
            </div>
            
            <!-- Modal Footer -->
            <div class="bg-gray-100 px-6 py-4 border-t">
                <button 
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded focus:outline-none focus:shadow-outline"
                    on:click={closeTableDetailsModal}
                >
                    Close
                </button>
            </div>
        </div>
    </div>
{/if}
