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
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { store, update } from '@/routes/admin/user';
import type { User } from '@/types/auth';

defineProps<{
    action: 'create' | 'edit';
    user?: User;
}>();

const dialogOpen = defineModel<boolean>('open', { default: false });
</script>

<template>
    <Dialog v-model:open="dialogOpen">
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle>{{
                    action === 'edit' ? 'Edit Pengguna' : 'Tambah Pengguna'
                }}</DialogTitle>
                <DialogDescription>
                    {{
                        action === 'edit'
                            ? 'Ubah data pengguna di sini.'
                            : 'Tambahkan pengguna baru ke sistem.'
                    }}
                </DialogDescription>
            </DialogHeader>
            <Form
                :action="action === 'create' ? store() : update(user!.id)"
                :method="action === 'create' ? 'post' : 'put'"
                @success="dialogOpen = false"
                class="space-y-4"
                #default="{ errors, processing }"
            >
                <div class="space-y-2">
                    <Label for="name">Nama</Label>
                    <Input
                        id="name"
                        name="name"
                        :defaultValue="user?.name"
                        required
                    />
                    <span v-if="errors.name" class="text-sm text-destructive">{{
                        errors.name
                    }}</span>
                </div>
                <div class="space-y-2">
                    <Label for="email">Email</Label>
                    <Input
                        id="email"
                        name="email"
                        type="email"
                        :defaultValue="user?.email"
                        required
                    />
                    <span
                        v-if="errors.email"
                        class="text-sm text-destructive"
                        >{{ errors.email }}</span
                    >
                </div>
                <div class="space-y-2">
                    <Label for="password"
                        >Password
                        {{
                            action === 'edit'
                                ? '(Kosongkan jika tidak diubah)'
                                : ''
                        }}</Label
                    >
                    <Input
                        id="password"
                        name="password"
                        type="password"
                        :required="action === 'create'"
                    />
                    <span
                        v-if="errors.password"
                        class="text-sm text-destructive"
                        >{{ errors.password }}</span
                    >
                </div>
                <div class="space-y-2">
                    <Label for="role">Peran</Label>
                    <Select name="role" :defaultValue="user?.role ?? 'student'">
                        <SelectTrigger>
                            <SelectValue placeholder="Pilih Peran" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectGroup>
                                <SelectItem value="admin">Admin</SelectItem>
                                <SelectItem value="teacher">Teacher</SelectItem>
                                <SelectItem value="student">Student</SelectItem>
                            </SelectGroup>
                        </SelectContent>
                    </Select>
                    <span v-if="errors.role" class="text-sm text-destructive">{{
                        errors.role
                    }}</span>
                </div>

                <DialogFooter>
                    <Button
                        type="button"
                        variant="outline"
                        @click="dialogOpen = false"
                        >Batal</Button
                    >
                    <Button type="submit" :disabled="processing">
                        {{ processing ? 'Menyimpan...' : 'Simpan' }}
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
