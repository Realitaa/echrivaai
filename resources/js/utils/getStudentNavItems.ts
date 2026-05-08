import { Home } from '@lucide/vue';
import classroom from '@/routes/student/classroom';
import type { NavItem } from '@/types';

export const studentNavItems: NavItem[] = [
    {
        title: 'Kelas',
        href: classroom.index(),
        icon: Home,
    },
];