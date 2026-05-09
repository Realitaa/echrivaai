<script setup lang="ts">
import {
    CalendarDate,
    getLocalTimeZone,
    today
} from '@internationalized/date';
import { CalendarIcon, Clock } from '@lucide/vue';
import dayjs from 'dayjs';
import type { DateValue } from 'reka-ui';
import { ref, computed, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import { Input } from '@/components/ui/input';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { cn } from '@/lib/utils';

interface Props {
    id?: string;
    modelValue?: string | null;
    error?: boolean;
    placeholder?: string;
    disablePast?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    placeholder: 'Pilih tanggal & waktu',
    disablePast: false,
});

const emit = defineEmits(['update:modelValue']);

// Internal state
const date = ref<DateValue | undefined>();
const time = ref('23:59'); // Default to end of day

// Sync from modelValue
watch(
    () => props.modelValue,
    (val) => {
        if (val) {
            const d = dayjs(val);
            
            if (d.isValid()) {
                date.value = new CalendarDate(d.year(), d.month() + 1, d.date()) as any;
                time.value = d.format('HH:mm');
            }
        }
    },
    { immediate: true },
);

// If disablePast is true, watch time, set it to next hour if time is past the current time
watch(time, (val) => {
    if (props.disablePast && date.value) {
        const [hours, minutes] = val.split(':').map(Number);
        const d = dayjs()
            .year((date.value as any).year)
            .month((date.value as any).month - 1)
            .date((date.value as any).day)
            .hour(hours || 0)
            .minute(minutes || 0)
            .second(0);
        
        if (d.isBefore(dayjs())) {
            time.value = dayjs().add(1, 'hour').format('HH:mm');
        }
    }
});

// Sync to modelValue
const updateValue = () => {
    if (date.value) {
        const [hours, minutes] = (time.value || '00:00').split(':').map(Number);
        const d = dayjs()
            .year((date.value as any).year)
            .month((date.value as any).month - 1)
            .date((date.value as any).day)
            .hour(hours || 0)
            .minute(minutes || 0)
            .second(0);
        
        const formatted = d.format('YYYY-MM-DD HH:mm:ss');

        if (formatted !== props.modelValue) {
            emit('update:modelValue', formatted);
        }
    } else if (props.modelValue) {
        emit('update:modelValue', null);
    }
};

watch([date, time], updateValue);

const formattedValue = computed(() => {
    if (!props.modelValue) {
        return props.placeholder;
    }

    return dayjs(props.modelValue).format('DD MMMM YYYY, HH:mm');
});

const isDateDisabled = (date: any) => {
    if (props.disablePast) {
        return date.compare(today(getLocalTimeZone())) < 0
    }

    return false
}
</script>

<template>
    <Popover>
        <PopoverTrigger as-child>
            <Button
                :id="id"
                type="button"
                variant="outline"
                :class="cn(
                    'w-full justify-start text-left font-normal',
                    !modelValue && 'text-muted-foreground',
                    error && 'border-destructive'
                )"
            >
                <CalendarIcon class="mr-2 h-4 w-4" />
                {{ formattedValue }}
            </Button>
        </PopoverTrigger>
        <PopoverContent class="w-auto p-0" align="start">
            <Calendar v-model="date" initial-focus :is-date-disabled="isDateDisabled" />
            <div class="border-t p-4">
                <div class="flex items-center gap-3">
                    <Clock class="h-4 w-4 text-muted-foreground" />
                    <div class="flex flex-1 items-center gap-2">
                        <span class="text-sm font-medium">Waktu:</span>
                        <Input
                            type="time"
                            v-model="time"
                            class="h-9 w-full"
                        />
                    </div>
                </div>
            </div>
        </PopoverContent>
    </Popover>
</template>
