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
                <DialogTitle>{{ $t('classroom.student.enrollDialog.title') }}</DialogTitle>
                <DialogDescription>
                    {{ $t('classroom.student.enrollDialog.description') }}
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
                    <Label for="code" :class="{ 'text-destructive': errors.code }">
                        {{ $t('classroom.student.enrollDialog.codeLabel') }}
                    </Label>
                    <Input
                        id="code"
                        name="code"
                        :placeholder="$t('classroom.student.enrollDialog.codePlaceholder')"
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
                    >{{ $t('classroom.student.enrollDialog.cancel') }}</Button>
                    <Button type="submit" :disabled="processing">
                        {{ processing ? $t('classroom.student.enrollDialog.joining') : $t('classroom.student.enrollDialog.join') }}
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
