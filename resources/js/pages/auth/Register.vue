<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { RadioGroupItem } from '@/components/ui/radio-group';
import { RadioGroup } from '@/components/ui/radio-group';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';
import { store } from '@/routes/register';

defineOptions({
    layout: {
        title: 'auth.register.title',
        description: 'auth.register.subtitle',
    },
});
</script>

<template>
    <Head :title="$t('auth.register.title')" />

    <Form
        v-bind="store.form()"
        :reset-on-success="['password', 'password_confirmation']"
        v-slot="{ errors, processing }"
        class="flex flex-col gap-6"
    >
        <div class="grid gap-6">
            <div class="grid gap-2">
                <Label for="name">{{ $t('auth.form.name') }}</Label>
                <Input
                    id="name"
                    type="text"
                    required
                    autofocus
                    :tabindex="1"
                    autocomplete="name"
                    name="name"
                    :placeholder="$t('auth.placeholder.name')"
                />
                <InputError :message="errors.name" />
            </div>

            <div class="grid gap-2">
                <Label for="email">{{ $t('auth.form.email') }}</Label>
                <Input
                    id="email"
                    type="email"
                    required
                    :tabindex="2"
                    autocomplete="email"
                    name="email"
                    placeholder="email@example.com"
                />
                <InputError :message="errors.email" />
            </div>

            <div class="grid gap-2">
                <Label for="password">{{ $t('auth.form.password') }}</Label>
                <PasswordInput
                    id="password"
                    required
                    :tabindex="3"
                    autocomplete="new-password"
                    name="password"
                    :placeholder="$t('auth.form.password')"
                />
                <InputError :message="errors.password" />
            </div>

            <div class="grid gap-2">
                <Label for="password_confirmation">{{ $t('auth.form.confirm_password') }}</Label>
                <PasswordInput
                    id="password_confirmation"
                    required
                    :tabindex="4"
                    autocomplete="new-password"
                    name="password_confirmation"
                    :placeholder="$t('auth.form.confirm_password')"
                />
                <InputError :message="errors.password_confirmation" />
            </div>

            <div class="grid gap-2">
                <Label for="role">{{ $t('auth.form.role') }}</Label>
                <RadioGroup
                    name="role"
                    class="grid grid-cols-2 gap-4"
                    :default-value="errors.role ?? 'student'"
                    required
                >
                    <div class="flex items-center gap-2">
                        <RadioGroupItem value="student" id="student" />
                        <Label for="student">{{ $t('auth.form.role_student') }}</Label>
                    </div>
                    <div class="flex items-center gap-2">
                        <RadioGroupItem value="teacher" id="teacher" />
                        <Label for="teacher">{{ $t('auth.form.role_teacher') }}</Label>
                    </div>
                </RadioGroup>
                <InputError :message="errors.role" />
            </div>

            <Button
                type="submit"
                class="w-full"
                tabindex="5"
                :disabled="processing"
                data-test="register-user-button"
            >
                <Spinner v-if="processing" />
                {{ $t('auth.register.button') }}
            </Button>
        </div>

        <div class="text-center text-sm text-muted-foreground">
            {{ $t('auth.links.sign_in') }}
            <TextLink
                :href="login()"
                class="underline underline-offset-4"
                :tabindex="6"
                >{{ $t('auth.links.sign_in_link') }}</TextLink
            >
        </div>
    </Form>
</template>
