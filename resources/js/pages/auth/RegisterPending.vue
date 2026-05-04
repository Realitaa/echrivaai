<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

defineOptions({
    layout: {
        title: 'Menunggu Persetujuan',
    },
});
</script>

<template>
    <Head title="Register Pending" />

    <div class="grid gap-2">
        <h1>Maaf, pendaftaranmu sudah selesai dan menunggu persetujuan pendaftaran dari admin. Refresh halaman ini secara berkala untuk mengecek status pendaftaranmu.</h1>
    </div>
</template>