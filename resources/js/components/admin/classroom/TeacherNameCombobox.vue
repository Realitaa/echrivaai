<script setup lang="ts">
import { CheckIcon, ChevronsUpDownIcon } from '@lucide/vue'
import { computed, ref } from 'vue'
import { Button } from '@/components/ui/button'
import {
  Command,
  CommandEmpty,
  CommandGroup,
  CommandInput,
  CommandItem,
  CommandList,
} from '@/components/ui/command'
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from '@/components/ui/popover'
import { cn } from '@/lib/utils'
import type { Teacher } from '@/types';

const props = defineProps<{
  teachers: Teacher[];
}>();

const emit = defineEmits(['selected']);

const comboboxOpen = ref(false);

const value = ref<number | undefined>(undefined)

const selectedTeacher = computed(() =>
  props.teachers.find(teacher => teacher.id === value.value),
)

function selectTeacher(selectedValue: string) {
  const id = Number(selectedValue)
  value.value = id === value.value ? undefined : id

  emit('selected', value.value)
  comboboxOpen.value = false
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
        {{ selectedTeacher?.name || "Pilih Guru..." }}
        <ChevronsUpDownIcon class="opacity-50" />
      </Button>
    </PopoverTrigger>
    <PopoverContent class="w-[200px] p-0">
      <Command>
        <CommandInput class="h-9" placeholder="Cari guru..." />
        <CommandList>
          <CommandEmpty>Tidak ada guru.</CommandEmpty>
          <CommandGroup>
            <CommandItem
              v-for="teacher in teachers"
              :key="teacher.id"
              :value="teacher.id"
              @select="(ev) => {
                selectTeacher(ev.detail.value as string)
              }"
            >
              {{ teacher.name }}
              <CheckIcon
                :class="cn(
                  'ml-auto',
                  value === teacher.id ? 'opacity-100' : 'opacity-0',
                )"
              />
            </CommandItem>
          </CommandGroup>
        </CommandList>
      </Command>
    </PopoverContent>
  </Popover>
</template>
