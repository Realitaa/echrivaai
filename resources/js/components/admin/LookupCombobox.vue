<script setup lang="ts">
import { CheckIcon, ChevronsUpDownIcon } from '@lucide/vue';
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Command,
    CommandEmpty,
    CommandGroup,
    CommandInput,
    CommandItem,
    CommandList,
} from '@/components/ui/command';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import { cn } from '@/lib/utils';

const props = defineProps<{
    items: any[];
    label: string;
    placeholder?: string;
    emptyText?: string;
    defaultValue?: number | string;
}>();

const emit = defineEmits(['selected']);

const comboboxOpen = ref(false);

const value = ref<number | undefined>(
    props.defaultValue ? Number(props.defaultValue) : undefined,
);

const selectedValue = computed(() =>
    props.items.find((item) => item.id === value.value),
);

function selectValue(selectedValue: string) {
    const id = Number(selectedValue);
    value.value = id === value.value ? undefined : id;

    emit('selected', value.value);
    comboboxOpen.value = false;
}
</script>

<template>
    <Popover v-model:open="comboboxOpen">
        <PopoverTrigger as-child>
            <Button
                variant="outline"
                role="combobox"
                :aria-expanded="comboboxOpen"
                class="w-[200px] justify-between"
            >
                {{ selectedValue?.name || label }}
                <ChevronsUpDownIcon class="opacity-50" />
            </Button>
        </PopoverTrigger>
        <PopoverContent class="w-[200px] p-0">
            <Command>
                <CommandInput class="h-9" :placeholder="placeholder" />
                <CommandList>
                    <CommandEmpty>{{ emptyText }}</CommandEmpty>
                    <CommandGroup>
                        <CommandItem
                            v-for="item in items"
                            :key="item.id"
                            :value="item.id"
                            @select="
                                (ev) => {
                                    selectValue(ev.detail.value as string);
                                }
                            "
                        >
                            {{ item.name }}
                            <CheckIcon
                                :class="
                                    cn(
                                        'ml-auto',
                                        value === item.id
                                            ? 'opacity-100'
                                            : 'opacity-0',
                                    )
                                "
                            />
                        </CommandItem>
                    </CommandGroup>
                </CommandList>
            </Command>
        </PopoverContent>
    </Popover>
</template>
