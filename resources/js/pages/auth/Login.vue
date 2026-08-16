<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { store } from '@/routes/login';

defineOptions({
    layout: {
        title: 'Scooter Spots',
        description: 'Log in om je plekken te bekijken.',
    },
});
</script>

<template>
    <Head title="Inloggen" />

    <Form
        v-bind="store.form()"
        :reset-on-success="['password']"
        v-slot="{ errors, processing }"
        class="space-y-5"
    >
        <div class="space-y-2">
            <Label for="email">E-mailadres</Label>
            <Input
                id="email"
                type="email"
                name="email"
                required
                autofocus
                placeholder="tom@scooterspots.nl"
            />
            <InputError :message="errors.email" />
        </div>

        <div class="space-y-2">
            <Label for="password">Wachtwoord</Label>
            <Input
                id="password"
                type="password"
                name="password"
                required
            />
            <InputError :message="errors.password" />
        </div>

        <Button
            type="submit"
            size="lg"
            class="w-full"
            :disabled="processing"
            data-test="login-button"
        >
            <Spinner v-if="processing" />
            Inloggen
        </Button>
    </Form>
</template>
