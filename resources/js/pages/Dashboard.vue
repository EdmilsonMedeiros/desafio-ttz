<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import ModalAlert from '@/components/shared/ModalAlert.vue';
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import { watch, onMounted } from 'vue';
import axios from 'axios';
const page = usePage();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const alertShow = ref(false);
const fileInput = ref<HTMLInputElement | null>(null);
const error = ref('');
const errorDescription = ref('');

const search = ref('');
const importedFiles = ref<{ data: any[] }>({ data: [] });

const players = ref<{ data: any[] }>({ data: [] });
const mostCollectedItems = ref<{ data: any[] }>({ data: [] });
const mostKilledBosses = ref<{ data: any[] }>({ data: [] });

const showLoadedData = ref(false);

const events = ref<{ data: any[] }>({ data: [] });
const items = ref<{ data: any[] }>({ data: [] });
const leaderboard = ref<{ data: any[] }>({ data: [] });

const handleSubmit = () => {
    if (fileInput.value?.files && fileInput.value.files.length > 0) {
        importedFiles.value = { data: [] };

        const selectedFile = fileInput.value.files[0];
        // summit the file here
        router.post('/import', {
            file: selectedFile,
        }, {
            onSuccess: (response: any) => {
                loadImportedFiles();

                setTimeout(() => {
                    loadImportedFilesData();
                }, 2000);
            },
            onError: (error: any) => {
                loadImportedFiles();
            }
        });
        
    } else {
        alert("Nenhum arquivo selecionado. Por favor, selecione um arquivo para importar.");
        
        return;
    }
}

const loadImportedFiles = async () => {
    axios.get('/imported-files').then((response) => {
        importedFiles.value = response.data;
        console.log(importedFiles.value);
    });
}

const deleteFile = (id: number) => {

    if(!confirm('Tem certeza que deseja deletar este arquivo?')) {
        return;
    }

    router.delete(`/imported-files/${id}`, {
        onSuccess: () => {
            loadImportedFiles();
            loadImportedFilesData();
        },
        onError: () => {
            loadImportedFiles();
        }
    });
}

const downloadFile = (id: number) => {
    window.open(`/download-file/${id}`, '_blank');
}

const loadImportedFilesData = async () => {
    showLoadedData.value = false;
    
    try {
        // Carregar players e seus stats
        const playersResponse = await axios.get('/players');
        players.value = playersResponse.data;

        if (playersResponse.data && playersResponse.data.length > 0) {
            // Usar Promise.all para aguardar todos os stats serem carregados
            await Promise.all(playersResponse.data.map(async (player: any) => {
                const stats = await getPlayerStatById(player.id);
                player.stats = stats;
            }));
        }

        // Carregar todos os outros dados em paralelo
        const [
            collectedItemsResponse,
            killedBossesResponse,
            eventsResponse,
            itemsResponse,
            leaderboardResponse
        ] = await Promise.all([
            axios.get('/most-collected-items'),
            axios.get('/most-killed-bosses'),
            axios.get('/events'),
            axios.get('/items'),
            axios.get('/leaderboard')
        ]);

        // Atualizar todos os dados
        mostCollectedItems.value = collectedItemsResponse.data;
        mostKilledBosses.value = killedBossesResponse.data;
        events.value = eventsResponse.data;
        items.value = itemsResponse.data;
        leaderboard.value = leaderboardResponse.data;

        // Só mostra os dados depois que tudo foi carregado
        showLoadedData.value = true;
    } catch (error) {
        console.error('Erro ao carregar dados:', error);
        showLoadedData.value = false;
    }
}

const getPlayerStatById = async (id: number) => {
    const response = await axios.get(`players/${id}/stats`);
    return response.data;
}

onMounted(() => {
    loadImportedFiles();
    loadImportedFilesData();
});

</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container p-4 space-y-4 mt-4 mb-4">
            <!-- Header Section with Import and Alerts -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Import Card -->
                <div class="bg-card rounded-xl shadow-sm border border-border/40 overflow-hidden flex flex-col lg:flex-row items-center lg:items-start p-4 gap-4">
                    <i class="pi pi-upload text-2xl text-primary"></i>
                    <div class="flex-1">
                        <h2 class="text-xl font-semibold text-foreground mb-2">Importar Log</h2>
                        <div class="flex flex-col lg:flex-row lg:items-center gap-4">
                            <div class="flex-1">
                                
                                <Input 
                                    id="log" 
                                    type="file" 
                                    ref="fileInput"
                                    accept=".txt"
                                    @change="(e: any) => fileInput = e.target"
                                    class="bg-background/50 text-foreground border-border/50 hover:bg-background/80 transition-colors w-full"
                                />
                            </div>
                            <Button type="button" @click="handleSubmit" class="bg-primary hover:bg-primary/90 transition-colors flex-shrink-0">
                                <i class="pi pi-upload mr-2"></i>
                                Importar Arquivo
                            </Button>
                        </div>
                    </div>
                </div>

                <!-- Alerts Card -->
                <div class="space-y-4">
                    <div v-if="page.props.success" 
                        class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4 shadow-sm animate-in fade-in duration-200">
                        <div class="flex items-center gap-3">
                            <i class="pi pi-check-circle text-green-500 dark:text-green-400 text-xl"></i>
                            <p class="text-sm font-medium text-green-800 dark:text-green-200">
                                {{ page.props.success }}
                            </p>
                        </div>
                    </div>

                    <div v-if="page.props.error"
                        class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 shadow-sm animate-in fade-in duration-200">
                        <div class="flex items-center gap-3">
                            <i class="pi pi-times-circle text-red-500 dark:text-red-400 text-xl"></i>
                            <p class="text-sm font-medium text-red-800 dark:text-red-200">
                                {{ page.props.error }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Files Table Section -->
            <div class="bg-card rounded-xl shadow-sm border border-border/40 overflow-hidden">
                <div class="p-4">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-2">
                            <i class="pi pi-file text-2xl text-primary"></i>
                            <h2 class="text-xl font-semibold text-foreground">Arquivos Importados</h2>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-border/40">
                                    <th class="px-6 py-4 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Arquivo</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Data</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border/40">
                                <tr v-if="importedFiles.data.length === 0">
                                    <td colspan="3" class="px-6 py-8 text-center text-muted-foreground">
                                        <div class="flex flex-col items-center gap-2">
                                            <i class="pi pi-inbox text-3xl"></i>
                                            <p>Nenhum arquivo importado</p>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-else v-for="file in importedFiles.data" :key="file.id" 
                                    class="hover:bg-muted/40 transition-colors">
                                    <td class="px-6 py-4 text-sm text-foreground">
                                        <div class="flex items-center gap-2">
                                            <i class="pi pi-file-text text-primary"></i>
                                            {{ file.file_name }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-foreground">
                                        {{ new Date(file.created_at).toLocaleDateString('pt-BR') }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <Button type="button" variant="ghost" size="icon" @click="deleteFile(file.id)" 
                                                class="text-destructive hover:text-destructive/90 hover:bg-destructive/10 transition-colors">
                                                <i class="pi pi-trash"></i>
                                            </Button>
                                            <Button type="button" variant="ghost" size="icon" @click="downloadFile(file.id)" 
                                                class="text-primary hover:text-primary/90 hover:bg-primary/10 transition-colors">
                                                <i class="pi pi-download"></i>
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Statistics Section -->
            <div class="space-y-6">
                <div class="flex items-center gap-2">
                    <i class="pi pi-chart-bar text-2xl text-primary"></i>
                    <h2 class="text-xl font-semibold text-foreground">Estatísticas do Jogo</h2>
                </div>
                
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Players Card -->
                    <div class="col-span-2 bg-card rounded-xl shadow-sm border border-border/40 p-6">
                        <div class="flex items-center gap-2 mb-6">
                            <i class="pi pi-users text-xl text-primary"></i>
                            <h3 class="text-lg font-semibold text-foreground">Players (Total: {{ players.length }})</h3>
                        </div>
                        <div class="space-y-4">
                            <div v-if="!players || players.length === 0" class="text-center text-muted-foreground py-4">
                                <p>Nenhum player encontrado</p>
                            </div>
                            <template v-else-if="showLoadedData" class="animate-in fade-in duration-200 showLoadedData">
                                <div v-for="player in players" :key="player.id" 
                                    class="bg-muted/40 rounded-lg p-4 hover:bg-muted/60 transition-colors">
                                    <div class="flex items-center gap-2 mb-2">
                                        <i class="pi pi-user text-primary"></i>
                                        <span class="font-semibold text-foreground">{{ player?.name ?? 'Jogador desconhecido' }}</span>
                                    </div>
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm text-muted-foreground">
                                        <div v-if="player" class="flex items-center gap-1">
                                            <i class="pi pi-star-fill text-yellow-500"></i>
                                            Nível {{ player?.level }}
                                        </div>
                                        <div v-if="player" class="flex items-center gap-1">
                                            <i class="pi pi-bolt text-blue-500"></i>
                                            {{ player?.stats?.xp_total }} XP
                                        </div>
                                        <div v-if="player" class="flex items-center gap-1">
                                            <i class="pi pi-shield text-green-500"></i>
                                            {{ player?.stats?.bosses_defeated }} Chefes
                                        </div>
                                        <div v-if="player" class="flex items-center gap-1">
                                            <i class="pi pi-heart-fill text-red-500"></i>
                                            {{ player?.stats?.kills_pvp }} PVP Kills
                                        </div>
                                        <div v-if="player" class="flex items-center gap-1">
                                            <i class="pi pi-heart-fill text-red-500"></i>
                                            {{ player?.stats?.deaths ?? 0 }} Mortes
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Side Stats -->
                    <div class="space-y-6">
                        <!-- Most Collected Items -->
                        <div class="bg-card rounded-xl shadow-sm border border-border/40 p-6">
                            <div class="flex items-center gap-2 mb-4">
                                <i class="pi pi-box text-xl text-primary"></i>
                                <h3 class="text-lg font-semibold text-foreground">Top {{ mostCollectedItems.length }} Itens</h3>
                            </div>
                            <div class="space-y-3">
                                <div v-if="!mostCollectedItems || mostCollectedItems.length === 0" class="text-center text-muted-foreground py-4">
                                    <p>Nenhum item coletado ainda</p>
                                </div>
                                <template v-else>
                                    <div v-for="item in mostCollectedItems" :key="item.id"
                                        class="flex items-center justify-between p-2 rounded bg-muted/40 hover:bg-muted/60 transition-colors">
                                        <span class="text-sm text-foreground">
                                            {{ item?.item_name ? (item.item_name.replace(/_/g, ' ').charAt(0).toUpperCase() + item.item_name.replace(/_/g, ' ').slice(1)) : 'Item desconhecido' }}
                                        </span>
                                        <span class="text-sm font-medium text-primary">{{ item?.total_collected ?? 0 }}</span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Defeated Bosses -->
                        <div class="bg-card rounded-xl shadow-sm border border-border/40 p-6">
                            <div class="flex items-center gap-2 mb-4">
                                <i class="pi pi-shield text-xl text-primary"></i>
                                <h3 class="text-lg font-semibold text-foreground">Chefes Derrotados ({{ mostKilledBosses.length }})</h3>
                            </div>
                            <div class="space-y-2">
                                <div v-if="!mostKilledBosses || mostKilledBosses.length === 0" class="text-center text-muted-foreground py-4">
                                    <p>Nenhum chefe derrotado ainda</p>
                                </div>
                                <template v-else>
                                    <div v-for="(boss, index) in mostKilledBosses" :key="index"
                                        class="flex items-center gap-3 p-2 rounded bg-muted/40 hover:bg-muted/60 transition-colors">
                                        <span class="flex items-center justify-center w-6 h-6 rounded-full bg-primary/10 text-primary text-sm">
                                            {{ index + 1 }}
                                        </span>
                                        <span class="text-sm text-foreground">{{ boss?.boss_name ?? 'Chefe desconhecido' }}</span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <ModalAlert :show="alertShow" @close="alertShow = false" :error="error" :description="errorDescription" />
    </AppLayout>
</template>
