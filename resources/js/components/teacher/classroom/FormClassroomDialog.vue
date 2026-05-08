<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { store, update } from '@/routes/teacher/classroom';
import type { Classroom } from '@/types';
import { Textarea } from '@/components/ui/textarea';

defineProps<{
    action: 'create' | 'edit';
    classroom?: Classroom | null;
}>();

const dialogOpen = defineModel<boolean>('open', { default: false });
</script>

<template>
    <Dialog v-model:open="dialogOpen">
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle>{{
                    action === 'edit' ? 'Edit Kelas' : 'Buat Kelas Baru'
                }}</DialogTitle>
                <DialogDescription>
                    {{
                        action === 'edit'
                            ? 'Ubah informasi kelas Anda di sini.'
                            : 'Tambahkan kelas baru ke daftar kelas Anda. Kode kelas akan dibuat secara otomatis.'
                    }}
                </DialogDescription>
            </DialogHeader>
            <Form
                :action="action === 'create' ? store() : update(classroom!.id)"
                :method="action === 'create' ? 'post' : 'put'"
                @success="dialogOpen = false"
                class="space-y-4 py-4"
                #default="{ errors, processing }"
                autocomplete="off"
            >
                <div class="space-y-2">
                    <Label for="name" :class="{ 'text-destructive': errors.name }">Nama Kelas</Label>
                    <Input
                        id="name"
                        name="name"
                        :defaultValue="classroom?.name"
                        placeholder="Contoh: Matematika Dasar"
                        :class="{ 'border-destructive': errors.name }"
                        required
                    />
                    <span v-if="errors.name" class="text-xs text-destructive">{{
                        errors.name
                    }}</span>
                </div>
                <div class="space-y-2">
                    <Label for="description" :class="{ 'text-destructive': errors.description }">Deskripsi (Opsional)</Label>
                    <Textarea
                        id="description"
                        name="description"
                        :defaultValue="classroom?.description"
                        placeholder="Masukkan deskripsi kelas..."
                        rows="3"
                        :class="{ 'border-destructive': errors.description }"
                    />
                    <span v-if="errors.description" class="text-xs text-destructive">{{
                        errors.description
                    }}</span>
                </div>

                <DialogFooter>
                    <Button
                        type="button"
                        variant="outline"
                        @click="dialogOpen = false"
                        :disabled="processing"
                    >Batal</Button>
                    <Button type="submit" :disabled="processing">
                        {{ processing ? 'Menyimpan...' : 'Simpan' }}
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
