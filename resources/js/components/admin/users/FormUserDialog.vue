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
                    action === 'edit' ? $t('users.editDialog.title') : $t('users.createDialog.title')
                }}</DialogTitle>
                <DialogDescription>
                    {{
                        action === 'edit'
                            ? $t('users.editDialog.description')
                            : $t('users.createDialog.description')
                    }}
                </DialogDescription>
            </DialogHeader>
            <Form
                :action="action === 'create' ? store() : update(user!.id)"
                :method="action === 'create' ? 'post' : 'put'"
                @success="dialogOpen = false"
                class="space-y-4"
                #default="{ errors, processing }"
                autocomplete="off"
            >
                <div class="space-y-2">
                    <Label for="name">{{ $t('users.createDialog.name') }}</Label>
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
                    <Label for="email">{{ $t('users.createDialog.email') }}</Label>
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
                        >{{ $t('users.createDialog.password') }}
                        {{
                            action === 'edit'
                                ? '(' + $t('users.editDialog.passwordNote') + ')'
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
                    <Label for="role">{{ $t('users.createDialog.role') }}</Label>
                    <Select name="role" :defaultValue="user?.role ?? 'student'">
                        <SelectTrigger>
                            <SelectValue :placeholder="$t('users.createDialog.role')" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectGroup>
                                <SelectItem value="admin">{{ $t('users.createDialog.roles.admin') }}</SelectItem>
                                <SelectItem value="teacher">{{ $t('users.createDialog.roles.teacher') }}</SelectItem>
                                <SelectItem value="student">{{ $t('users.createDialog.roles.student') }}</SelectItem>
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
                        :disabled="processing"
                        >{{ $t('users.createDialog.close') }}</Button
                    >
                    <Button type="submit" :disabled="processing">
                        {{ processing ? $t('users.createDialog.creating') : (action === 'edit' ? $t('users.editDialog.update') : $t('users.createDialog.create')) }}
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
