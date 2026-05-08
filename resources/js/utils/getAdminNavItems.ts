import {
    ClipboardCheck,
    ClipboardList,
    GraduationCap,
    LayoutGrid,
    Users,
} from '@lucide/vue';
import { dashboard } from '@/routes';
import classroom from '@/routes/admin/classroom';
import submission from '@/routes/admin/submission';
import task from '@/routes/admin/task';
import users from '@/routes/admin/user';
import type { NavItem } from '@/types';

export const adminNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Pengguna',
        href: users.index(),
        icon: Users,
    },
    {
        title: 'Kelas',
        href: classroom.index(),
        icon: GraduationCap,
    },
    {
        title: 'Tugas',
        href: task.index(),
        icon: ClipboardList,
    },
    {
        title: 'Penugasan',
        href: submission.index(),
        icon: ClipboardCheck,
    },
];