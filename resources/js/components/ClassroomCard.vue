<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Edit, Eye, Trash2, Copy } from '@lucide/vue';
import { useClipboard } from '@vueuse/core';
import { toast } from 'vue-sonner'
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { show } from '@/routes/teacher/classroom';
import index from '@/routes/teacher/classroom/task';
import type { Classroom } from '@/types';

defineProps<{
    classroom: Classroom;
}>();

defineEmits<{
    (e: 'edit', classroom: Classroom): void;
    (e: 'delete', id: number): void;
}>();

const { copy } = useClipboard()

function copyClassroomCode(code: string) {
    copy(code);
    toast.success(`Kode kelas ${code} berhasil disalin!`);
}
</script>

<template>
    <div class="group relative flex flex-col overflow-hidden rounded-xl border bg-card shadow-sm transition-all hover:shadow-md">
        <!-- Header Image Area -->
        <div class="relative h-28 w-full overflow-hidden">
            <img 
                src="/assets/images/bg_library.jpg" 
                alt="Classroom cover" 
                class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110" 
            />
            <!-- Faded effect on lower part of the image -->
            <div class="absolute inset-0 bg-linear-to-t from-card via-transparent to-transparent opacity-90"></div>
        </div>

        <!-- Avatar Overlapping -->
        <div class="absolute right-4 top-16 z-10">
            <Avatar class="h-20 w-20 border-4 border-card shadow-sm ring-1 ring-border/50">
                <AvatarFallback class="bg-slate-500 text-2xl font-bold text-white">
                    {{ classroom.name.charAt(0).toUpperCase() }}
                </AvatarFallback>
            </Avatar>
        </div>

        <!-- Content Area -->
        <div class="flex flex-1 flex-col p-6 pt-2">
            <div class="mb-4">
                <Link :href="index.index(classroom.id)">
                    <h3 class="line-clamp-1 text-xl font-bold tracking-tight group-hover:text-primary transition-colors hover:underline">
                        {{ classroom.name }}
                    </h3>
                </Link>
                <p 
                  @click="copyClassroomCode(classroom.code)"
                  title="Salin kode kelas"
                  class="line-clamp-1 text-xs font-mono font-medium text-muted-foreground tracking-wider cursor-pointer flex items-center gap-2">
                    <span>
                        Kode: {{ classroom.code }}
                    </span>
                    <Button
                        variant="ghost"
                        size="icon"
                        class="h-6 w-6 cursor-pointer"
                    >
                        <Copy class="h-4 w-4" />
                    </Button>
                </p>
            </div>

            <p class="line-clamp-2 min-h-10 text-sm text-muted-foreground">
                {{ classroom.description || 'Tidak ada deskripsi untuk kelas ini.' }}
            </p>
            
            <!-- Actions Area (Footer) -->
            <div class="mt-6 flex items-center justify-between border-t pt-4">
                <div class="flex gap-1">
                    <Button 
                        variant="ghost" 
                        size="icon" 
                        as-child 
                        class="h-10 w-10 rounded-full hover:bg-primary/10 hover:text-primary transition-colors" 
                        title="Lihat Detail"
                    >
                        <Link :href="show(classroom.id).url">
                            <Eye class="h-5 w-5" />
                        </Link>
                    </Button>
                    <Button 
                        variant="ghost" 
                        size="icon" 
                        @click="$emit('edit', classroom)" 
                        class="h-10 w-10 rounded-full hover:bg-primary/10 hover:text-primary transition-colors" 
                        title="Edit Kelas"
                    >
                        <Edit class="h-5 w-5" />
                    </Button>
                </div>
                
                <Button 
                    variant="ghost" 
                    size="icon" 
                    @click="$emit('delete', classroom.id)" 
                    class="h-10 w-10 rounded-full text-destructive hover:bg-destructive/10 hover:text-destructive transition-colors" 
                    title="Hapus"
                >
                    <Trash2 class="h-5 w-5" />
                </Button>
            </div>
        </div>
    </div>
</template>
