<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { ChevronUp, GraduationCap } from "@lucide/vue"
import { computed, ref, watch } from 'vue';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from "@/components/ui/collapsible"
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubItem,
    SidebarMenuSubButton,
    useSidebar
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { useInitials } from '@/composables/useInitials';
import { index as indexStudent } from '@/routes/student/classroom/task';
import { index as indexTeacher } from '@/routes/teacher/classroom/task';
import type { NavItem } from '@/types';

defineProps<{
    items: NavItem[];
}>();

const page = usePage();
const userRole = page.props.auth.user.role;
const classLists = computed(() => (page.props.sidebar as any).list);
const { setOpen, open } = useSidebar();
const { isCurrentUrl, isCurrentOrParentUrl } = useCurrentUrl();
const { getInitials } = useInitials();

const openClassroom = ref(false);

watch(classLists, (newList) => {
    if (newList?.some((c: any) => isCurrentUrl(c.url))) {
        openClassroom.value = true;
    }
}, { immediate: true });

function toggleClassroom() {
    if (!open.value) {
        setOpen(true);
        openClassroom.value = true;
    }
}

const sidebarClassroomUrl = computed(() => userRole === 'teacher' ? indexTeacher : indexStudent);
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel v-if="userRole === 'admin'">{{ $t('navigation.section.admin') }}</SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem v-for="item in items" :key="item.title">
                <SidebarMenuButton
                    as-child
                    :is-active="isCurrentUrl(item.href)"
                    :tooltip="item.title"
                    size="lg"
                >
                    <Link :href="item.href">
                        <component :is="item.icon" class="ml-1 size-5!" />
                        <span class="text-md">{{ $t(item.title) }}</span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>

            <Collapsible as-child v-if="userRole !== 'admin'" v-model:open="openClassroom" class="group/collapsible">
                <SidebarMenuItem>
                    <CollapsibleTrigger as-child>
                        <SidebarMenuButton :tooltip="userRole === 'teacher' ? $t('navigation.toggle.teacherClassroomList') : $t('navigation.toggle.studentClassroomList')" size="lg" @click="toggleClassroom">
                            <GraduationCap class="ml-1 size-5!" />
                            <span class="text-md">{{ userRole === 'teacher' ? $t('navigation.toggle.teacherClassroomList') : $t('navigation.toggle.studentClassroomList') }}</span>
                            <ChevronUp class="ml-auto size-5 transition-transform duration-300 group-data-[state=open]/collapsible:rotate-180" />
                        </SidebarMenuButton>
                    </CollapsibleTrigger>
                        
                    <CollapsibleContent>
                        <SidebarMenuSub class="mx-0 px-0">
                            <SidebarMenuSubItem v-if="classLists?.length === 0">
                                <SidebarMenuSubButton size="md" class="py-6">
                                    <span class="ml-1 text-sm text-muted-foreground italic">{{ $t('navigation.toggle.empty') }}</span>
                                </SidebarMenuSubButton>
                            </SidebarMenuSubItem>
                            <SidebarMenuSubItem v-else v-for="classroom in classLists" :key="classroom.title">
                                <SidebarMenuSubButton as-child :is-active="isCurrentOrParentUrl(classroom.url)" size="md" class="py-6">
                                    <Link :href="sidebarClassroomUrl(classroom.id)">
                                        <Avatar class="size-6! overflow-hidden">
                                            <AvatarFallback class="rounded-md text-black dark:text-white">
                                                {{ getInitials(classroom.title).charAt(0) }}
                                            </AvatarFallback>
                                        </Avatar>
                                        <span class="text-md">{{ classroom.title }}</span>
                                    </Link>
                                </SidebarMenuSubButton>
                            </SidebarMenuSubItem>
                        </SidebarMenuSub>
                    </CollapsibleContent>
                </SidebarMenuItem>
            </Collapsible>
        </SidebarMenu>
    </SidebarGroup>
</template>
