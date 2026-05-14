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
                    action === 'edit' ? $t('classroom.teacher.form.editTitle') : $t('classroom.teacher.form.createTitle')
                }}</DialogTitle>
                <DialogDescription>
                    {{
                        action === 'edit'
                            ? $t('classroom.teacher.form.editDesc')
                            : $t('classroom.teacher.form.createDesc')
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
                    <Label for="name" :class="{ 'text-destructive': errors.name }">{{ $t('classroom.teacher.form.name') }}</Label>
                    <Input
                        id="name"
                        name="name"
                        :defaultValue="classroom?.name"
                        :placeholder="$t('classroom.teacher.form.namePlaceholder')"
                        :class="{ 'border-destructive': errors.name }"
                        required
                    />
                    <span v-if="errors.name" class="text-xs text-destructive">{{
                        errors.name
                    }}</span>
                </div>
                <div class="space-y-2">
                    <Label for="description" :class="{ 'text-destructive': errors.description }">{{ $t('classroom.teacher.form.description') }}</Label>
                    <Textarea
                        id="description"
                        name="description"
                        :defaultValue="classroom?.description"
                        :placeholder="$t('classroom.teacher.form.descriptionPlaceholder')"
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
                    >{{ $t('classroom.teacher.form.cancel') }}</Button>
                    <Button type="submit" :disabled="processing">
                        {{ processing ? $t('classroom.teacher.form.saving') : $t('classroom.teacher.form.save') }}
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
