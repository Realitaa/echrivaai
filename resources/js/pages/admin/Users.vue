<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    MoreHorizontal,
    Pencil,
    Trash2,
    CheckCircle,
    XCircle,
    Plus,
} from '@lucide/vue';
import { ref, watch } from 'vue';
import DeleteUserDialog from '@/components/admin/users/DeleteUserDialog.vue';
import FormUserDialog from '@/components/admin/users/FormUserDialog.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { useRelativeTime } from '@/composables/useDateFormat';
import { approve, index, destroy } from '@/routes/admin/user';
import type { User } from '@/types/auth';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'users.title',
                href: index,
            },
        ],
    },
});

const props = defineProps<{
    users: {
        data: User[];
        links: { url: string | null; label: string; active: boolean }[];
    };
    filters: {
        search?: string;
        role?: string;
        is_approved?: boolean | string | number;
    };
}>();

// Filters
const search = ref(props.filters.search ?? '');
const role = ref(props.filters.role ?? 'all');
const isApproved = ref(
    props.filters.is_approved !== undefined &&
        props.filters.is_approved !== null
        ? String(props.filters.is_approved)
        : 'all',
);

let timeout: ReturnType<typeof setTimeout> | null = null;

watch([search, role, isApproved], ([newSearch, newRole, newIsApproved]) => {
    if (timeout) {
        clearTimeout(timeout);
    }

    timeout = setTimeout(() => {
        router.get(
            index(),
            {
                search: newSearch,
                role: newRole === 'all' ? undefined : newRole,
                is_approved:
                    newIsApproved === 'all' ? undefined : newIsApproved,
            },
            { preserveState: true, replace: true },
        );
    }, 300);
});

// Create / Edit User Dialog
const dialogOpen = ref(false);
const editingUser = ref<User | null>(null);

const openCreateDialog = () => {
    editingUser.value = null;
    dialogOpen.value = true;
};

const openEditDialog = (user: User) => {
    editingUser.value = user;
    dialogOpen.value = true;
};

// Delete User Alert
const deleteDialogOpen = ref(false);
const deletingUserId = ref<number | null>(null);

const confirmDelete = (id: number) => {
    deletingUserId.value = id;
    deleteDialogOpen.value = true;
};

const deleteUser = () => {
    if (deletingUserId.value) {
        router.delete(destroy(deletingUserId.value));
        deleteDialogOpen.value = false;
    }
};

// Approve Toggle
const toggleApprove = (user: User) => {
    router.patch(approve(user.id));
};
</script>

<template>
    <Head :title="$t('users.title')" />

    <div class="flex h-full flex-1 flex-col gap-4 p-4 lg:p-8">
        <div
            class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
        >
            <div class="flex flex-col gap-1">
                <h1 class="text-2xl font-bold tracking-tight">{{ $t('users.title') }}</h1>
                <p class="text-sm text-muted-foreground">
                    {{ $t('users.description') }}
                </p>
            </div>
            <Button @click="openCreateDialog">
                <Plus class="h-4 w-4" /> {{ $t('users.createDialog.create') }}
            </Button>
        </div>

        <div class="flex flex-col gap-4 md:flex-row md:items-center">
            <Input
                v-model="search"
                :placeholder="$t('users.search')"
                class="md:max-w-[300px]"
            />

            <Select v-model="role">
                <SelectTrigger class="w-full md:w-[180px]">
                    <SelectValue :placeholder="$t('users.filter.roles.all')" />
                </SelectTrigger>
                <SelectContent>
                    <SelectGroup>
                        <SelectItem value="all">{{ $t('users.filter.roles.all') }}</SelectItem>
                        <SelectItem value="admin">{{ $t('users.filter.roles.admin') }}</SelectItem>
                        <SelectItem value="teacher">{{ $t('users.filter.roles.teacher') }}</SelectItem>
                        <SelectItem value="student">{{ $t('users.filter.roles.student') }}</SelectItem>
                    </SelectGroup>
                </SelectContent>
            </Select>

            <Select v-model="isApproved">
                <SelectTrigger class="w-full md:w-[180px]">
                    <SelectValue :placeholder="$t('users.filter.statuses.all')" />
                </SelectTrigger>
                <SelectContent>
                    <SelectGroup>
                        <SelectItem value="all">{{ $t('users.filter.statuses.all') }}</SelectItem>
                        <SelectItem value="1">{{ $t('users.filter.statuses.approved') }}</SelectItem>
                        <SelectItem value="0">{{ $t('users.filter.statuses.unapproved') }}</SelectItem>
                    </SelectGroup>
                </SelectContent>
            </Select>
        </div>

        <div class="rounded-md border bg-card">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>{{ $t('users.table.name') }}</TableHead>
                        <TableHead>{{ $t('users.table.email') }}</TableHead>
                        <TableHead>{{ $t('users.table.role') }}</TableHead>
                        <TableHead>{{ $t('users.table.status') }}</TableHead>
                        <TableHead>{{ $t('users.table.joined') }}</TableHead>
                        <TableHead class="text-right">{{ $t('users.table.actions') }}</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="user in users.data" :key="user.id">
                        <TableCell class="font-medium">{{
                            user.name
                        }}</TableCell>
                        <TableCell>{{ user.email }}</TableCell>
                        <TableCell>
                            <span class="capitalize">{{ $t('users.filter.roles.' + user.role) }}</span>
                        </TableCell>
                        <TableCell>
                            <Badge
                                :variant="
                                    user.is_approved ? 'default' : 'destructive'
                                "
                            >
                                {{
                                    user.is_approved
                                        ? $t('users.filter.statuses.approved')
                                        : $t('users.filter.statuses.unapproved')
                                }}
                            </Badge>
                        </TableCell>
                        <TableCell>{{ useRelativeTime(user.created_at) }}</TableCell>
                        <TableCell class="text-right">
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="ghost" class="h-8 w-8 p-0">
                                        <span class="sr-only">{{ $t('users.table.actions') }}</span>
                                        <MoreHorizontal class="h-4 w-4" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end">
                                    <DropdownMenuLabel>{{ $t('users.table.actions') }}</DropdownMenuLabel>
                                    <DropdownMenuItem
                                        @click="openEditDialog(user)"
                                    >
                                        <Pencil class="mr-2 h-4 w-4" /> {{ $t('users.actions.edit') ?? 'Edit' }}
                                    </DropdownMenuItem>
                                    <DropdownMenuItem
                                        @click="toggleApprove(user)"
                                        v-if="user.role === 'teacher'"
                                    >
                                        <template v-if="user.is_approved">
                                            <XCircle
                                                class="mr-2 h-4 w-4 text-destructive"
                                            />
                                            {{ $t('users.actions.revoke') }}
                                        </template>
                                        <template v-else>
                                            <CheckCircle
                                                class="mr-2 h-4 w-4 text-green-500"
                                            />
                                            {{ $t('users.actions.approve') }}
                                        </template>
                                    </DropdownMenuItem>
                                    <DropdownMenuSeparator />
                                    <DropdownMenuItem
                                        class="text-destructive"
                                        @click="confirmDelete(user.id)"
                                    >
                                        <Trash2 class="mr-2 h-4 w-4" /> {{ $t('users.actions.delete') }}
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </TableCell>
                    </TableRow>
                    <TableRow v-if="users.data.length === 0">
                        <TableCell
                            colspan="6"
                            class="h-24 text-center text-muted-foreground"
                        >
                            {{ $t('users.empty') }}
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <div
            class="flex items-center justify-end space-x-2"
            v-if="users.links && users.links.length > 3"
        >
            <template v-for="(link, index) in users.links" :key="index">
                <Button
                    v-if="link.url"
                    :variant="link.active ? 'default' : 'outline'"
                    size="sm"
                    class="min-w-9"
                    :as="Link"
                    :href="link.url"
                >
                    <span v-html="link.label"></span>
                </Button>
                <span
                    v-else
                    class="px-2 text-muted-foreground"
                    v-html="link.label"
                ></span>
            </template>
        </div>
    </div>

    <!-- Form Dialog -->
    <FormUserDialog
        v-model:open="dialogOpen"
        :action="editingUser ? 'edit' : 'create'"
        :user="editingUser ?? undefined"
    />

    <!-- Delete Alert Dialog -->
    <DeleteUserDialog v-model:open="deleteDialogOpen" @delete="deleteUser" />
</template>
