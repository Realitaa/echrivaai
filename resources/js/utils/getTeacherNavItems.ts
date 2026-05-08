import { Home } from '@lucide/vue';
import classroom from '@/routes/teacher/classroom';
import type { NavItem } from '@/types';

export const teacherNavItems: NavItem[] = [
    {
        title: 'Kelas',
        href: classroom.index(),
        icon: Home,
    },
];