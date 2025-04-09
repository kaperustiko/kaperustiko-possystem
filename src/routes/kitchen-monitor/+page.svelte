<script lang=ts>
    import { onMount } from 'svelte';

    // Define the card data with explicit types
    interface OrderItem {
        item: any; // Replace 'any' with the actual type if known
        table_number: number;
        que_order_no: number;
        receipt_number: string;
        items_ordered: string;
        order_status: string;
        waiter_name?: string; // Add optional waiter name field
        waiter_code?: string; // Add optional waiter code field
    }

    let pendingItems: OrderItem[] = [];
    let processingItems: OrderItem[] = [];
    let doneItems: OrderItem[] = [];
    let updateSuccess: boolean = false;
    let updateMessage: string = '';

    // Fetch data from the backend
    onMount(() => {
        const fetchData = async () => {
            const response = await fetch('http://localhost/kaperustiko-possystem/backend/modules/get.php?action=getQueOrders');
            const data: OrderItem[] = await response.json();

            // Assuming the data structure matches your items
            pendingItems = data.filter(item => item.order_status === 'pending');
            processingItems = data.filter(item => item.order_status === 'processing');
            doneItems = data.filter(item => item.order_status === 'done');
        };

        fetchData(); // Initial fetch
        const interval = setInterval(fetchData, 1000); // Fetch every 1 second

        return () => clearInterval(interval); // Cleanup on component unmount
    });

    // Function to update order status
    async function updateOrderStatus(receiptNumber: number, orderStatus: string) {
        try {
            updateMessage = '';
            updateSuccess = false;
            
            const response = await fetch('http://localhost/kaperustiko-possystem/backend/modules/update.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ receipt_number: receiptNumber, order_status: orderStatus })
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            console.log(data.message); // Log success or error message
            
            // Update UI to show success message
            updateSuccess = data.status === 'success';
            updateMessage = data.message;
            
            // Also update table status
            await fetch('http://localhost/kaperustiko-possystem/backend/modules/check_tables.php');
            
            // After 2 seconds, hide the message
            setTimeout(() => {
                updateMessage = '';
            }, 2000);
        } catch (error) {
            console.error('Error updating order status:', error);
            updateSuccess = false;
            updateMessage = 'Error updating order status';
        }
    }
</script>

<div class="flex flex-col md:flex-row bg-gray-600 h-screen p-4">
    {#if updateMessage}
        <div class={`fixed top-4 right-4 px-4 py-2 rounded ${updateSuccess ? 'bg-green-500' : 'bg-red-500'} text-white shadow-lg z-50`}>
            {updateMessage}
        </div>
    {/if}
    <div class="column pending w-full md:w-1/3 bg-white mx-2 my-4 p-6 rounded-lg shadow-lg border border-gray-300 hover:shadow-xl transition-shadow duration-300 overflow-auto">
        <h2 class="text-2xl font-bold mb-4 text-gray-800 flex justify-between items-center">
            Pending 
            <span class="bg-blue-500 text-white rounded-full w-8 h-8 flex items-center justify-center">{pendingItems.length}</span>
        </h2>
        <div class="space-y-2">
            <!-- Group pending orders by table -->
            {#each [...new Set(pendingItems.map(item => item.table_number))].sort((a, b) => Number(a) - Number(b)) as tableNum}
                <div class="bg-blue-50 p-2 rounded-lg mb-3">
                    <div class="bg-blue-500 text-white p-2 rounded-t-lg mb-2 flex justify-between">
                        <h3 class="font-bold">Table {tableNum}</h3>
                        <span class="bg-white text-blue-500 px-2 py-1 rounded-full text-xs font-bold">
                            {pendingItems.filter(item => item.table_number === tableNum).length} orders
                        </span>
                    </div>
                    
                    {#each pendingItems.filter(item => item.table_number === tableNum) as { item, que_order_no, items_ordered, waiter_name }}
                        <div class="card p-4 bg-gray-100 rounded-lg shadow hover:bg-gray-200 transition-colors duration-300 border border-gray-300 mb-2">
                            <p class="text-gray-600">Order No: <span class="font-medium">{que_order_no}</span></p>
                            {#if waiter_name}
                            <p class="text-gray-600">Waiter: <span class="font-medium">{waiter_name}</span></p>
                            {/if}
                            <p class="text-gray-600">Order Details:</p>
                            <div class="font-medium space-y-1">
                                {#each JSON.parse(items_ordered) as item}
                                    <div class="flex items-center justify-between border-b border-gray-200 py-4">
                                        <div class="flex-1">
                                            <p class="font-semibold text-lg">Name: {item.order_name} {item.order_name2}</p>
                                            <p class="font-normal text-gray-600">Quantity: {item.order_quantity}</p>
                                            <p class="font-normal text-gray-600">Size: {item.order_size}</p>
                                        </div>
                                        <div class="flex-none text-right">
                                            
                                            {#if item.order_addons && item.order_addons_price != null && item.order_addons_price > 0}
                                                <div class="flex justify-between">
                                                    <p class="font-normal">Addons:</p>
                                                    <p class="font-normal">{item.order_addons}</p>
                                                </div>
                                            {/if}
                                        </div>
                                    </div>
                                    {#if item.order_addons2}
                                        <p class="font-normal text-gray-600">Addons 2: {item.order_addons2}</p>
                                    {/if}
                                    {#if item.order_addons3}
                                        <p class="font-normal text-gray-600">Addons 3: {item.order_addons3}</p>
                                    {/if}
                                {/each}
                            </div>
                            <div class="mt-4 flex space-x-2">
                                <button class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600" 
                                        on:click={() => {
                                            console.log(`Receipt Number: ${que_order_no}`);
                                            updateOrderStatus(que_order_no, 'pending');
                                        }}>
                                    Pending
                                </button>
                                <button class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600" 
                                        on:click={() => {
                                            console.log(`Receipt Number: ${que_order_no}`);
                                            updateOrderStatus(que_order_no, 'processing');
                                        }}>
                                    Processing
                                </button>
                            </div>
                        </div>
                    {/each}
                </div>
            {/each}
        </div>
    </div>
    <div class="column processing w-full md:w-1/3 bg-white mx-2 my-4 p-6 rounded-lg shadow-lg border border-gray-300 hover:shadow-xl transition-shadow duration-300 overflow-auto">
        <h2 class="text-2xl font-bold mb-4 text-gray-800 flex justify-between items-center">
            Processing 
            <span class ="bg-yellow-500 text-white rounded-full w-8 h-8 flex items-center justify-center">{processingItems.length}</span>
        </h2>
        <div class="space-y-2">
            <!-- Group processing orders by table -->
            {#each [...new Set(processingItems.map(item => item.table_number))].sort((a, b) => Number(a) - Number(b)) as tableNum}
                <div class="bg-yellow-50 p-2 rounded-lg mb-3">
                    <div class="bg-yellow-500 text-white p-2 rounded-t-lg mb-2 flex justify-between">
                        <h3 class="font-bold">Table {tableNum}</h3>
                        <span class="bg-white text-yellow-500 px-2 py-1 rounded-full text-xs font-bold">
                            {processingItems.filter(item => item.table_number === tableNum).length} orders
                        </span>
                    </div>
                    
                    {#each processingItems.filter(item => item.table_number === tableNum) as { item, que_order_no, items_ordered, waiter_name }}
                        <div class="card p-4 bg-gray-100 rounded-lg shadow hover:bg-gray-200 transition-colors duration-300 border border-gray-300 mb-2">
                            <p class="text-gray-600">Order No: <span class="font-medium">{que_order_no}</span></p>
                            {#if waiter_name}
                            <p class="text-gray-600">Waiter: <span class="font-medium">{waiter_name}</span></p>
                            {/if}
                            <p class="text-gray-600">Order Details:</p>
                            <div class="font-medium space-y-1">
                                {#each JSON.parse(items_ordered) as item}
                                    <div class="flex items-center justify-between border-b border-gray-200 py-4">
                                        <div class="flex-1">
                                            <p class="font-semibold text-lg">Name: {item.order_name} {item.order_name2}</p>
                                            <p class="font-normal text-gray-600">Quantity: {item.order_quantity}</p>
                                            <p class="font-normal text-gray-600">Size: {item.order_size}</p>
                                        </div>
                                        <div class="flex-none text-right">
                                            
                                            {#if item.order_addons && item.order_addons_price != null && item.order_addons_price > 0}
                                                <div class="flex justify-between">
                                                    <p class="font-normal">Addons:</p>
                                                    <p class="font-normal">{item.order_addons}</p>
                                                </div>
                                            {/if}
                                        </div>
                                    </div>
                                    {#if item.order_addons2}
                                        <p class="font-normal text-gray-600">Addons 2: {item.order_addons2}</p>
                                    {/if}
                                    {#if item.order_addons3}
                                        <p class="font-normal text-gray-600">Addons 3: {item.order_addons3}</p>
                                    {/if}
                                {/each}
                            </div>
                            <div class="mt-4 flex space-x-2">
                                <button class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600" 
                                        on:click={() => {
                                            console.log(`Receipt Number: ${que_order_no}`);
                                            updateOrderStatus(que_order_no, 'processing');
                                        }}>
                                    Processing
                                </button>
                                <button class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600" 
                                        on:click={() => {
                                            console.log(`Receipt Number: ${que_order_no}`);
                                            updateOrderStatus(que_order_no, 'done');
                                        }}>
                                    Done
                                </button>
                            </div>
                        </div>
                    {/each}
                </div>
            {/each}
        </div>
    </div>
    <div class="column done w-full md:w-1/3 bg-white mx-2 my-4 p-6 rounded-lg shadow-lg border border-gray-300 hover:shadow-xl transition-shadow duration-300 overflow-auto">
        <h2 class="text-2xl font-bold mb-4 text-gray-800 flex justify-between items-center">
            Done 
            <span class="text-white bg-green-500 rounded-full w-8 h-8 flex items-center justify-center">{doneItems.length}</span>
        </h2>
        <div class="space-y-2">
            <!-- Group done orders by table -->
            {#each [...new Set(doneItems.map(item => item.table_number))].sort((a, b) => Number(a) - Number(b)) as tableNum}
                <div class="bg-green-50 p-2 rounded-lg mb-3">
                    <div class="bg-green-500 text-white p-2 rounded-t-lg mb-2 flex justify-between">
                        <h3 class="font-bold">Table {tableNum}</h3>
                        <span class="bg-white text-green-500 px-2 py-1 rounded-full text-xs font-bold">
                            {doneItems.filter(item => item.table_number === tableNum).length} orders
                        </span>
                    </div>
                    
                    {#each doneItems.filter(item => item.table_number === tableNum) as { item, que_order_no, items_ordered, waiter_name }}
                        <div class="card p-4 bg-gray-100 rounded-lg shadow hover:bg-gray-200 transition-colors duration-300 border border-gray-300 mb-2">
                            <p class="text-gray-600">Order No: <span class="font-medium">{que_order_no}</span></p>
                            {#if waiter_name}
                            <p class="text-gray-600">Waiter: <span class="font-medium">{waiter_name}</span></p>
                            {/if}
                            <p class="text-gray-600">Order Details:</p>
                            <div class="font-medium space-y-1">
                                {#each JSON.parse(items_ordered) as item}
                                    <div class="flex items-center justify-between border-b border-gray-200 py-4">
                                        <div class="flex-1">
                                            <p class="font-semibold text-lg">Name: {item.order_name} {item.order_name2}</p>
                                            <p class="font-normal text-gray-600">Quantity: {item.order_quantity}</p>
                                            <p class="font-normal text-gray-600">Size: {item.order_size}</p>
                                        </div>
                                        <div class="flex-none text-right">
                                            
                                            {#if item.order_addons && item.order_addons_price != null && item.order_addons_price > 0}
                                                <div class="flex justify-between">
                                                    <p class="font-normal">Addons:</p>
                                                    <p class="font-normal">{item.order_addons}</p>
                                                </div>
                                            {/if}
                                        </div>
                                    </div>
                                    {#if item.order_addons2}
                                        <p class="font-normal text-gray-600">Addons 2: {item.order_addons2}</p>
                                    {/if}
                                    {#if item.order_addons3}
                                        <p class="font-normal text-gray-600">Addons 3: {item.order_addons3}</p>
                                    {/if}
                                {/each}
                            </div>
                        </div>
                    {/each}
                </div>
            {/each}
        </div>
    </div>
 
</div>
<div class="column w-full bg-white mx-2 my-4 p-6 rounded-lg shadow-lg border border-gray-300 hover:shadow-xl transition-shadow duration-300 overflow-auto">
    <h2 class="text-2xl font-bold mb-4 text-gray-800">Total Orders</h2>
    <p class="text-gray-600">Total: {pendingItems.length + processingItems.length + doneItems.length}</p>
</div>
<div class="column w-full bg-white mx-2 my-4 p-6 rounded-lg shadow-lg border border-gray-300 hover:shadow-xl transition-shadow duration-300 overflow-auto">
    <h2 class="text-2xl font-bold mb-4 text-gray-800">Chart 1</h2>
    <!-- Placeholder for Chart 1 -->
    <div class="h-48 bg-gray-200 rounded-lg"></div>
</div>
<div class="column w-full bg-white mx-2 my-4 p-6 rounded-lg shadow-lg border border-gray-300 hover:shadow-xl transition-shadow duration-300 overflow-auto">
    <h2 class="text-2xl font-bold mb-4 text-gray-800">Chart 2</h2>
    <!-- Placeholder for Chart 2 -->
    <div class="h-48 bg-gray-200 rounded-lg"></div>
</div>