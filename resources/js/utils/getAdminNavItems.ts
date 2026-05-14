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
        title: 'navigation.admin.dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'navigation.admin.users',
        href: users.index(),
        icon: Users,
    },
    {
        title: 'navigation.admin.classrooms',
        href: classroom.index(),
        icon: GraduationCap,
    },
    {
        title: 'navigation.admin.tasks',
        href: task.index(),
        icon: ClipboardList,
    },
    {
        title: 'navigation.admin.submissions',
        href: submission.index(),
        icon: ClipboardCheck,
    },
];