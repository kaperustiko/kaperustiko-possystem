<script lang="ts">
    import { onMount } from "svelte";
    import { FontAwesomeIcon } from "@fortawesome/svelte-fontawesome";
    import { faStar, faUndo, faExclamationTriangle, faTrash, faDollarSign, faPrint, faArrowDown, faChartBar, faTrophy, faExclamationCircle, faTrashAlt } from "@fortawesome/free-solid-svg-icons";
    import Sidebar from "../sidebar/+page.svelte";
    import { Bar, Line, Pie } from 'svelte-chartjs';
    import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, ArcElement } from 'chart.js';

    ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, ArcElement);

    let salesRemitItems: any[] = [];
    let returnItems: any[] = [];

    // Sample data for charts
    let salesData = {
        labels: ['January', 'February', 'March', 'April', 'May', 'June'],
        datasets: [
            {
                label: 'Sales',
                data: [300, 500, 400, 600, 700, 800],
                backgroundColor: (context: { dataset: { data: number[] }, dataIndex: number }) => {
                    const value = context.dataset.data[context.dataIndex];
                    if (value < 400) return 'red'; // Critical
                    if (value < 600) return 'yellow'; // Warning
                    return 'green'; // Good
                },
            }
        ]
    };

    let inventoryData = {
        labels: [],
        datasets: [
            {
                label: 'Critical',
                data: [],
                backgroundColor: 'red',
            },
            {
                label: 'Warning',
                data: [],
                backgroundColor: 'yellow',
            },
            {
                label: 'Good',
                data: [],
                backgroundColor: 'green',
            }
        ]
    };

    let returnData = {
        labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
        datasets: [
            {
                label: 'Returns',
                data: [5, 10, 15, 20],
                backgroundColor: 'rgba(255, 99, 132, 0.6)',
                borderColor: 'rgba(255, 99, 132, 1)',
                fill: false,
            }
        ]
    };

    let totalSalesToday = 0; // Variable to store today's total sales
    let sortOption = 'qty'; // Declare sortOption with a default value
    let selectedCategory = 'Food'; // Reactive variable for selected category
    let overallTotalSales: number = 0; // Variable to store overall total sales
    let bestSeller: string = "Loading..."; // Initialize bestSeller
    let leastSeller: string = "Loading..."; // Initialize leastSeller
    let totalShortageCost = 0; // Variable to store total shortage cost
    let todayShortageCost = 0; // Variable to store today's shortage cost
    let todayReturnCost = 0; // Variable to store today's total return cost
    let totalReturnCost = 0; // Variable to store total return cost

    // Add these variables for confirmation modals
    let showDeleteRemitModal = false;
    let showDeleteReturnModal = false;
    let itemToDelete: number | null = null;

    let selectedDateRange = 'firstHalf'; // Default value

    // Add this interface before the forEach
    interface MonthlySale {
        month: number;
        total_amount: number;
    }

    onMount(async () => {
        const today = new Date();
        const formattedDate = `${today.getMonth() + 1}/${today.getDate()}/${today.getFullYear()}`; // Format date as MM/DD/YYYY
        const response = await fetch(`http://localhost/kaperustiko-possystem/backend/modules/get.php?action=getTodaySales&date=${formattedDate}`); // Use formatted date in the URL
        const data = await response.json();
        if (data.length > 0 && data[0].total_amount) { // Adjusted to check the correct structure of the response
            totalSalesToday = data[0].total_amount; // Update the total sales for today
        }

        // Fetch all sales remit items from the backend
        const responseRemit = await fetch('http://localhost/kaperustiko-possystem/backend/modules/get.php?action=getRemitSales');
        const remitItems = await responseRemit.json();
        salesRemitItems = remitItems; // Store fetched data in salesRemitItems

        fetchDataForCategory(selectedCategory); // Fetch data for Food on mount

        const responseReturn = await fetch('http://localhost/kaperustiko-possystem/backend/modules/get.php?action=getRemitReturns');
        const fetchedReturnItems = await responseReturn.json(); // Fetch return items
        returnItems = fetchedReturnItems; // Store fetched data in returnItems

        try {
            const response = await fetch('http://localhost/kaperustiko-possystem/backend/modules/get.php?action=getTotalSales', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                },
            });
            const data = await response.json();
            if (data.total_sales !== null) {
                overallTotalSales = data.total_sales; // Update the total sales variable
            }
        } catch (error) {
            console.error("Error fetching total sales:", error);
        }

        try {
            const response = await fetch('http://localhost/kaperustiko-possystem/backend/modules/get.php?action=getBestsellers', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                },
            });
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();
            console.log("Best seller data:", data);
            bestSeller = data.length > 0 ? data[0] : "No sales yet"; // Update bestSeller
        } catch (error) {
            console.error("Error fetching best seller:", error);
            bestSeller = "Error fetching data"; // Handle error
        }

        // Fetch least ordered item
        try {
            const responseLeastSeller = await fetch('http://localhost/kaperustiko-possystem/backend/modules/get.php?action=getLeastsellers', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                },
            });
            const data = await responseLeastSeller.json();
            leastSeller = data.length > 0 ? data[0] : "No orders yet"; // Update leastSeller
        } catch (error) {
            console.error("Error fetching least seller:", error);
            leastSeller = "Error fetching data"; // Handle error
        }

        // Fetch total shortage cost
        try {
            const response = await fetch('http://localhost/kaperustiko-possystem/backend/modules/get.php?action=getTotalShortage', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                },
            });
            const data = await response.json();
            if (data.total_shortage !== null) {
                totalShortageCost = data.total_shortage; // Update the total shortage cost variable
            }
        } catch (error) {
            console.error("Error fetching total shortage cost:", error);
        }

        // Fetch today's shortage cost
        try {
            const response = await fetch('http://localhost/kaperustiko-possystem/backend/modules/get.php?action=getTodayShortage', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                },
            });
            const data = await response.json();
            if (data.total_shortage !== null) {
                todayShortageCost = data.total_shortage; // Update the today's shortage cost variable
            }
        } catch (error) {
            console.error("Error fetching today's shortage cost:", error);
        }

        // Fetch today's return cost
        try {
            const response = await fetch('http://localhost/kaperustiko-possystem/backend/modules/get.php?action=getTodayReturnCost');
            const data = await response.json();
            if (data.total_return !== null) {
                todayReturnCost = data.total_return; // Update the return cost variable
            }
        } catch (error) {
            console.error("Error fetching today's return cost:", error);
        }

        // Fetch total return cost
        try {
            const response = await fetch('http://localhost/kaperustiko-possystem/backend/modules/get.php?action=getTotalReturnCost');
            const data = await response.json();
            if (data.total_return_cost !== null) {
                totalReturnCost = data.total_return_cost; // Update the total return cost variable
            }
        } catch (error) {
            console.error("Error fetching total return cost:", error);
        }

        // Fetch monthly sales data
        try {
            const response = await fetch('http://localhost/kaperustiko-possystem/backend/modules/get.php?action=getMonthlySales');
            const monthlySales = await response.json();
            
            // Prepare data for the sales chart
            salesData.labels = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
            salesData.datasets[0].data = Array(12).fill(0); // Initialize data array for 12 months

            // Populate the sales data
            monthlySales.forEach((sale: MonthlySale) => {
                salesData.datasets[0].data[sale.month - 1] = sale.total_amount; // Adjust index for 0-based array
            });
        } catch (error) {
            console.error("Error fetching monthly sales:", error);
        }
    });

    function printPage() {
        window.print();
    }

    function handleCategoryChange(event: Event) {
        selectedCategory = (event.target as HTMLSelectElement).value; // Update selectedCategory with the dropdown value
        console.log("Selected Category:", selectedCategory);
        
        // Fetch and filter data based on selectedCategory
        fetchDataForCategory(selectedCategory); // Call the new function to fetch data
    }

    async function fetchDataForCategory(category: string) {
        const responseMenu = await fetch(`http://localhost/kaperustiko-possystem/backend/modules/get.php?action=getMenuDashboard&label=${category}&label2=${category}`);
        const menuItems = await responseMenu.json();

        // Populate inventoryData with fetched menu items
        inventoryData.labels = menuItems.map((item: { title1: string, qty: number }) => `${item.title1}`); // Set labels to title and qty
        inventoryData.datasets[0].data = menuItems.map((item: { qty: number }) => item.qty); // Set data to qty

        // Set background colors based on qty with a maximum of 30
        inventoryData.datasets[0].backgroundColor = menuItems.map((item: { qty: number }) => {
            const quantity = Math.min(item.qty, 30); // Limit quantity to a maximum of 30
            if (quantity < 10) return 'red'; // Red
            if (quantity < 20) return 'yellow'; // Yellow
            return 'green';
        });
    }

    // Modify the delete functions to show confirmation first
    function confirmDeleteRemit(remit_id: number) {
        itemToDelete = remit_id;
        showDeleteRemitModal = true;
    }

    function confirmDeleteReturn(return_id: number) {
        itemToDelete = return_id;
        showDeleteReturnModal = true;
    }

    // Existing delete functions become the actual delete operations
    async function deleteRemit(remit_id: number) {
        try {
            const response = await fetch(`http://localhost/kaperustiko-possystem/backend/modules/delete.php?action=deleteRemit&remit_id=${remit_id}`, {
                method: 'DELETE'
            });
            const data = await response.json();
            if (data.success) {
                salesRemitItems = salesRemitItems.filter(item => item.remit_id !== remit_id);
            }
        } catch (error) {
            console.error('Error deleting remit:', error);
        } finally {
            showDeleteRemitModal = false;
            itemToDelete = null;
        }
    }

    async function deleteReturn(return_id: number) {
        try {
            const response = await fetch(`http://localhost/kaperustiko-possystem/backend/modules/delete.php?action=deleteReturn&return_id=${return_id}`, {
                method: 'DELETE'
            });
            const data = await response.json();
            if (data.success) {
                returnItems = returnItems.filter(item => item.return_id !== return_id);
            }
        } catch (error) {
            console.error('Error deleting return:', error);
        } finally {
            showDeleteReturnModal = false;
            itemToDelete = null;
        }
    }

</script>

<div class="flex h-screen bg-gradient-to-b from-green-500 to-green-700">
    <Sidebar />

    <!-- Main Content -->
    <div class="flex-grow p-6 bg-gray-100">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-8 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow-md p-4 text-center">
                <div class="text-gray-500">Today's Total Sales</div>
                <div class="text-3xl font-bold">₱{totalSalesToday}</div>
                <div class="text-green-600">
                    <FontAwesomeIcon icon={faDollarSign} />
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-md p-4 text-center">
                <div class="text-gray-500">Overall Total Sales</div>
                <div class="text-3xl font-bold">₱{overallTotalSales}</div>
                <div class="text-green-500">
                    <FontAwesomeIcon icon={faChartBar} />
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-md p-4 text-center">
                <div class="text-gray-500">Best Seller</div>
                <div class="text-3xl font-bold">{bestSeller || 'N/A'}</div>
                <div class="text-green-500">
                    <FontAwesomeIcon icon={faTrophy} />
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-md p-4 text-center">
                <div class="text-gray-500">Least Seller</div>
                <div class="text-3xl font-bold">{leastSeller || 'N/A'}</div>
                <div class="text-red-500">
                    <FontAwesomeIcon icon={faArrowDown} />
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-md p-4 text-center">
                <div class="text-gray-500">Shortage Cost</div>
                <div class="text-3xl font-bold">₱{todayShortageCost}</div>
                <div class="text-red-500">
                    <FontAwesomeIcon icon={faExclamationTriangle} />
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-md p-4 text-center">
                <div class="text-gray-500">Overall Shortage</div>
                <div class="text-3xl font-bold">₱{totalShortageCost}</div>
                <div class="text-red-500">
                    <FontAwesomeIcon icon={faExclamationTriangle} />
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-md p-4 text-center">
                <div class="text-gray-500">Return Cost</div> 
                <div class="text-3xl font-bold">₱{todayReturnCost}</div>
                <div class="text-red-500">
                    <FontAwesomeIcon icon={faExclamationCircle} />
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-md p-4 text-center">
                <div class="text-gray-500">Overall Returns</div>
                <div class="text-3xl font-bold">₱{totalReturnCost}</div>
                <div class="text-red-500">
                    <FontAwesomeIcon icon={faTrashAlt} />
                </div>
            </div>
           
        </div>

         <!-- Date Sorter -->
         

        <!-- Charts Section -->
        <div class="grid grid-cols-3 gap-2 mb-6">
            <div class="bg-white rounded-lg shadow-lg p-2">
                <h3 class="text-center font-bold text-sm">Sales Chart</h3>
                <Bar data={salesData} />
            </div>
            <div class="bg-white rounded-lg shadow-lg p-2">
                <div class="flex justify-between items-center mb-2">
                    <h3 class="text-center font-bold text-sm">Inventory Chart</h3>
                    <select class="bg-white text-black border-none shadow-sm focus:outline-none" on:change={handleCategoryChange} value={selectedCategory}>
                        <option value="Beverages">Beverages</option>
                        <option value="Food">Food</option>
                        <option value="Dessert">Dessert</option>
                        <option value="Coffee">Coffee</option>
                        <option value="Pasta">Pasta</option>
                        <option value="Burger">Burger</option>
                        <option value="Ulam">Ulam</option>
                    </select>
                </div>
                <Bar data={inventoryData} options={{ 
                    responsive: true, 
                    scales: {
                        y: {
                            max: 30 // Set maximum value for y-axis
                        }
                    }
                }} />
            </div>
            <div class="bg-white rounded-lg shadow-lg p-2">
                <h3 class="text-center font-bold text-sm">Returns Chart</h3>
                <Bar data={returnData} options={{ responsive: true }} />
            </div>
        </div>
        <div class="flex justify-end mb-4 w-full">
            <input type="text" placeholder="Search..." class="bg-white text-black border border-gray-300 rounded p-2 mr-2 flex-grow shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
           
            <button class="ml-2 p-2 bg-blue-600 text-white rounded shadow hover:bg-blue-700 transition duration-200" on:click={printPage}>
                <FontAwesomeIcon icon={faPrint} />
            </button>
        </div>
        <!-- Tables Section -->
        <div class="grid grid-cols-2 gap-4">
            <!-- Sales Remit Table -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="bg-gray-800 text-white text-center font-bold p-2">Sales Remit</div>
                <table class="w-full text-left table-fixed border-collapse">
                    <thead class="bg-gray-700 text-white">
                        <tr>
                            <th class="p-2 text-center">Remit ID</th>
                            <th class="p-2 text-center">Name</th>
                            <th class="p-2 text-center">Total Sales</th>    
                            <th class="p-2 text-center">Date</th>
                            <th class="p-2 text-center">Time</th>
                            <th class="p-2 text-center">Shortage</th>
                            <th class="p-2 text-center">Validate</th>
                            <th class="p-2 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        {#if salesRemitItems.length === 0}
                            <tr>
                                <td colspan="8" class="p-2 text-center">No data yet</td>
                            </tr>
                        {:else}
                            {#each salesRemitItems as item}
                                <tr class="border-t border-gray-300 hover:bg-gray-200 transition-colors duration-200">
                                    <td class="p-2 text-center">{item.remit_id}</td>
                                    <td class="p-2 text-center">{item.cashier_name}</td>
                                    <td class="p-2 text-center">₱{item.total_sales}.00</td>
                                    <td class="p-2 text-center">{item.remit_date}</td>
                                    <td class="p-2 text-center">{item.remit_time}</td>
                                    <td class="p-2 text-center">₱{item.remit_shortage}.00</td>
                                    <td class="p-2 text-center">
                                        <button class="p-1 {item.remit_validation === "Validated" ? 'bg-green-500' : item.remit_validation === "Pending" ? 'bg-yellow-500' : 'bg-gray-200'} text-white rounded">{item.remit_validation}</button>
                                    </td>
                                    <td class="p-2 text-center">
                                        <button 
                                            class="p-1 bg-red-500 text-white rounded hover:bg-red-600" 
                                            on:click={() => confirmDeleteRemit(item.remit_id)}>
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            {/each}
                        {/if}
                    </tbody>
                </table>
            </div>

            <!-- Inventory Item Returns Table -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="bg-gray-800 text-white text-center font-bold p-2">Inventory Item Returns</div>
                <table class="w-full text-left table-fixed border-collapse">
                    <thead class="bg-gray-700 text-white">
                        <tr>
                            <th class="p-2 text-center">Return ID</th>
                            <th class="p-2 text-center">Name</th>
                            <th class="p-2 text-center">Total Cost</th>    
                            <th class="p-2 text-center">Date</th>
                            <th class="p-2 text-center">Time</th>
                            <th class="p-2 text-center">Validate</th>
                            <th class="p-2 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        {#if returnItems.length === 0}
                            <tr>
                                <td colspan="7" class="p-2 text-center">No data yet</td>
                            </tr>
                        {:else}
                            {#each returnItems as item}
                                <tr class="border-t border-gray-300 hover:bg-gray-200 transition-colors duration-200">
                                    <td class="p-2 text-center">{item.return_id}</td>
                                    <td class="p-2 text-center">{item.cashier_name}</td>    
                                    <td class="p-2 text-center">{item.total_sales}</td>
                                    <td class="p-2 text-center">{item.return_date}</td>
                                    <td class="p-2 text-center">{item.return_time}</td>
                                    <td class="p-2 text-center">
                                        <button class="p-1 {item.return_validation === "Validated" ? 'bg-green-500' : item.return_validation === "Pending" ? 'bg-yellow-500' : 'bg-gray-200'} text-white rounded">{item.return_validation}</button>
                                    </td>
                                    <td class="p-2 text-center">
                                        <button 
                                            class="p-1 bg-red-500 text-white rounded hover:bg-red-600" 
                                            on:click={() => confirmDeleteReturn(item.return_id)}>
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            {/each}
                        {/if}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add these modal components just before the closing </div> of the main content -->
{#if showDeleteRemitModal}
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <h3 class="text-lg font-bold mb-4">Confirm Delete</h3>
            <p class="mb-4">Are you sure you want to delete this remit record?</p>
            <div class="flex justify-end gap-2">
                <button 
                    class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400"
                    on:click={() => {showDeleteRemitModal = false; itemToDelete = null;}}>
                    Cancel
                </button>
                <button 
                    class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600"
                    on:click={() => itemToDelete && deleteRemit(itemToDelete)}>
                    Delete
                </button>
            </div>
        </div>
    </div>
{/if}

{#if showDeleteReturnModal}
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <h3 class="text-lg font-bold mb-4">Confirm Delete</h3>
            <p class="mb-4">Are you sure you want to delete this return record?</p>
            <div class="flex justify-end gap-2">
                <button 
                    class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400"
                    on:click={() => {showDeleteReturnModal = false; itemToDelete = null;}}>
                    Cancel
                </button>
                <button 
                    class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600"
                    on:click={() => itemToDelete && deleteReturn(itemToDelete)}>
                    Delete
                </button>
            </div>
        </div>
    </div>
{/if}
  