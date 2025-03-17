<script lang="ts">
    import Header from '$lib/header/+page.svelte';
    import { onMount } from 'svelte';

    interface Player {
        username: string;
        rank: string;
        rank_star: number;
        points: number;
        tries: number;
        winrate?: number;
    }

    let players: Player[] = [];

	const rankMapping: { [key: number]: string } = {
        1: 'Grand-Master',
        2: 'Epic',
        3: 'Legend',
        4: 'Mythic',
        5: 'Mythical-Honor',
        6: 'Mythical-Glory',
    };

    onMount(async () => {
        try {
            const response = await fetch('http://localhost/codemaster/codemasters-backend/modules/get_all_user.php');
            const data = await response.json();
            console.log('Fetched data:', data);
            if (data.status === 'success') {
                players = data.data.map((player: Player) => ({
                    ...player,
                    rank: rankMapping[parseInt(player.rank)] || player.rank,
                    rank_star: player.rank_star,
                    winrate: player.points / player.tries
                })).sort((a: Player, b: Player) => {
                    // Sort by winrate from highest to lowest
                    return (b.winrate ?? 0) - (a.winrate ?? 0);
                });
                console.log(players.map(player => player.winrate));
            }
        } catch (error) {   
            console.error('Failed to fetch leaderboard data:', error);
        }
    });
</script>

<style>
    /* Additional styles for a more game-like feel */
    .table-row {
        transition: transform 0.2s;
    }
    .table-row:hover {
        transform: scale(1.02);
    }
    .glow {
        animation: glow-animation 1.5s infinite alternate;
        filter: drop-shadow(0 0 2px rgba(255, 255, 255, 0.2));
    }
    @keyframes glow-animation {
        0% {
            filter: drop-shadow(0 0 2px rgba(255, 255, 255, 0.2));
        }
        100% {
            filter: drop-shadow(0 0 5px rgba(255, 255, 255, 0.3));
        }
    }
</style>

<div class="h-screen bg-gradient-to-b from-gray-900 to-black">
    <div class="w-full flex justify-center">
        <Header />
    </div>

    <div class="flex flex-col items-center justify-center mt-10">
        <div class="w-3/4 bg-gray-800 rounded-lg shadow-lg overflow-hidden">
            <table class="w-full text-white border border-gray-700">
                <thead class="bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left">Ranking</th>
                        <th class="px-6 py-3 text-left">Player</th>
                        <th class="px-6 py-3 text-left">Rank</th>
                        <th class="px-6 py-3 text-left">Stars</th>
                        <th class="px-6 py-3 text-left">Winrate</th>
                    </tr>
                </thead>
                <tbody>
                    {#if players.length === 0}
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                No players registered yet.
                            </td>
                        </tr>
                    {:else}
                        {#each players as player, index}
                        <tr class="border-b border-gray-700 table-row 
                                   bg-gray-800">
                            <td class="px-6 py-4 font-bold">
                                {#if index < 3}
                                    <img 
                                        src={`./badges/${index === 0 ? 'first' : index === 1 ? 'second' : 'third'}.png`}
                                        alt={`Rank ${index + 1}`}
                                        class="w-[70px] h-[70px] glow"
                                    />
                                {:else}
                                    <div class="flex items-center justify-left ml-6">
                                        {index + 1}
                                    </div>
                                {/if}
                            </td>
                            <td class="px-6 py-4 font-semibold">{player.username}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2">
                                    <img 
                                        src={`./ranks/${player.rank.replace(' ', '')}.png`} 
                                        alt="Rank" 
                                        class="w-[50px] h-[50px] inline-block" 
                                    />
                                    <span class="text-lg font-semibold">{player.rank}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">{player.rank_star}</td>
                            <td class="px-6 py-4">{(player.tries > 0) ? ((player.points / player.tries) * 100).toFixed(2) + '%' : '100%'}</td>
                        </tr>
                        {/each}
                    {/if}
                </tbody>
            </table>
        </div>
    </div>
</div>