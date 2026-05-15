import { usePage } from '@inertiajs/vue3';
import { adminNavItems } from '@/utils/getAdminNavItems';
import { studentNavItems } from '@/utils/getStudentNavItems';
import { teacherNavItems } from '@/utils/getTeacherNavItems';

export function useNavItems() {
    const page = usePage();
    const userRole = page.props.auth.user.role;

    switch (userRole) {
        case 'admin':
            return adminNavItems;
        case 'teacher':
            return teacherNavItems;
        case 'student':
            return studentNavItems;
        default:
            return [];
    }
}
