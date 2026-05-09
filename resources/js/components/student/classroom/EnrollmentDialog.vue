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
import enrollment from '@/routes/student/classroom';

const dialogOpen = defineModel<boolean>('open', { default: false });
</script>

<template>
    <Dialog v-model:open="dialogOpen">
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle>Masuk ke Kelas</DialogTitle>
                <DialogDescription>
                    Masukkan kode kelas yang diberikan oleh guru Anda.
                </DialogDescription>
            </DialogHeader>
            <Form
                :action="enrollment.enroll()"
                method="post"
                @success="dialogOpen = false"
                class="space-y-4 py-4"
                #default="{ errors, processing }"
                autocomplete="off"
            >
                <div class="space-y-2">
                    <Label for="code" :class="{ 'text-destructive': errors.code }">Kode Kelas</Label>
                    <Input
                        id="code"
                        name="code"
                        placeholder="Contoh: 123456"
                        :class="{ 'border-destructive': errors.name }"
                        required
                    />
                    <span v-if="errors.name" class="text-xs text-destructive">{{
                        errors.name
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
                        {{ processing ? 'Memproses...' : 'Gabung' }}
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
