<script lang="ts">
    import { onMount } from "svelte";
    import Sidebar from '../sidebar/+page.svelte';

    // Define the type for a voucher with the correct properties
    interface Voucher {
        voucher_code: string; // Add voucher_code property
        voucher_discount: number; // Add voucher_discount property
        voucher_deadline: string; // Add voucher_deadline property
    }

    // State variable for voucher data with explicit type
    let vouchers: Voucher[] = []; // Specify the type as an array of Voucher

    // State variable for popup visibility
    let showPopup = false;

    // New state variables for form inputs
    let newVoucherCode = '';
    let newVoucherDiscount = 0;
    let newVoucherDeadline = '';

    // Function to show alert messages
    function showAlert(message: string, type: string) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `fixed top-0 left-1/2 transform -translate-x-1/2 mt-4 p-4 ${type === 'success' ? 'bg-green-500' : 'bg-red-500'} text-white rounded shadow-lg`;
        alertDiv.innerText = message;
        document.body.appendChild(alertDiv);
        setTimeout(() => {
            alertDiv.remove();
        }, 3000); // Remove alert after 3 seconds
    }

    onMount(async () => {
        try {
            const response = await fetch('http://localhost/kaperustiko-possystem/backend/modules/get.php?action=getVouchers');
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            vouchers = await response.json(); // Store the fetched vouchers
            console.log(vouchers);
        } catch (error) {
            console.error('Error fetching vouchers:', error);
            showAlert('Failed to load vouchers', 'error');
        }
    });

    // Function to add a new voucher
    async function addVoucher() {
        const response = await fetch(
            'http://localhost/kaperustiko-possystem/backend/modules/insert.php?action=voucher_code',
            {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    voucher_code: newVoucherCode,
                    voucher_discount: newVoucherDiscount,
                    voucher_deadline: newVoucherDeadline
                })
            }
        );

        const result = await response.json();
        if (result.status === "success") {
            showAlert("Voucher added successfully!", "success"); // Show success alert
            showPopup = false; // Close the popup after adding
            // Optionally, refresh the vouchers list here
        } else {
            showAlert(result.message, "error"); // Show error alert
            console.error(result.message);
        }
    }
</script>

<div class="flex h-screen w-full bg-gray-100">
    <Sidebar />
    <div class="p-4 w-full">
        <button class="mb-4 bg-blue-500 w-full text-white font-bold py-2 px-4 rounded hover:bg-blue-600 transition" on:click={() => showPopup = true}>
            Add Voucher
        </button>

        {#if showPopup}
            <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
                <div class="bg-white p-6 rounded shadow-lg">
                    <h2 class="font-bold text-lg">Add Voucher</h2>
                    <form on:submit|preventDefault={addVoucher}>
                        <input type="text" placeholder="Voucher Code" bind:value={newVoucherCode} class="border p-2 w-full mb-4" required />
                        <input type="number" placeholder="Voucher Discount" bind:value={newVoucherDiscount} class="border p-2 w-full mb-4" required />
                        <input type="date" placeholder="Voucher Deadline" bind:value={newVoucherDeadline} class="border p-2 w-full mb-4" required />
                        <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded">Submit</button>
                        <button type="button" class="ml-2 py-2 px-4 rounded" on:click={() => showPopup = false}>Cancel</button>
                    </form>
                </div>
            </div>
        {/if}

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            {#each vouchers as voucher}
                <div class="flex border border-gray-300 rounded-lg shadow-lg overflow-hidden">
                    <div class="flex-shrink-0 bg-gradient-to-r from-indigo-600 to-blue-500 text-white p-6 flex items-center justify-center">
                        <h2 class="font-bold text-5xl">{voucher.voucher_discount}% OFF</h2>
                    </div>
                    <div class="ml-4 p-6 bg-gray-100 flex-1 rounded-lg shadow-md transition-transform transform hover:scale-105">
                        <h3 class="font-bold text-xl text-gray-800">{voucher.voucher_code}</h3>
                        <p class="text-sm text-gray-600 mb-2">Limited Redemptions; Valid through {voucher.voucher_deadline}</p>
                        <p class="text-sm text-gray-600 mb-4">Treat yourself to a memorable dining experience at Kape Rustiko! This voucher offers a fantastic opportunity to enjoy their delicious dishes.</p>
                        <button class="bg-red-600 text-white py-2 px-4 rounded-lg shadow">Delete</button>
                    </div>
                </div>
            {/each}
        </div>
    </div>
</div>