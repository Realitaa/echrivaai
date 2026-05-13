<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    Brain,
    Clock,
    FileText,
    ArrowRight,
    CheckCircle2,
    Users,
    BarChart3,
    UploadCloud,
    Sparkles,
} from '@lucide/vue';
import { computed } from 'vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import CardIcon from '@/components/CardIcon.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardHeader,
    CardTitle,
    CardDescription,
} from '@/components/ui/card';
import { dashboard, login, register } from '@/routes';
import studentClassroom from '@/routes/student/classroom/index';
import teacherClassroom from '@/routes/teacher/classroom/index';

withDefaults(
    defineProps<{
        canRegister: boolean;
    }>(),
    {
        canRegister: true,
    },
);

const page = usePage();
const userRole = computed(() => page.props.auth.user?.role);
const loggedInNavBtn = computed(() => {
    if (userRole.value === 'teacher') {
        return teacherClassroom.index();
    }

    if (userRole.value === 'student') {
        return studentClassroom.index();
    }

    return dashboard();
});
</script>

<template>
    <Head title="Welcome to EchrivaAI" />
    <div
        class="min-h-screen bg-linear-to-b from-[#EDE9FE] to-white font-sans text-slate-900 selection:bg-purple-200"
    >
        <!-- Navigation -->
        <header
            class="container mx-auto flex items-center justify-between px-6 py-4"
        >
            <AppLogoIcon />
            <nav
                class="hidden items-center gap-6 text-sm font-medium text-slate-600 md:flex"
            >
                <a
                    href="#features"
                    class="transition-colors hover:text-purple-600"
                    >{{ $t('landing.nav.features') }}</a
                >
                <a
                    href="#how-it-works"
                    class="transition-colors hover:text-purple-600"
                    >{{ $t('landing.nav.howItWorks') }}</a
                >
                <a
                    href="#benefits"
                    class="transition-colors hover:text-purple-600"
                    >{{ $t('landing.nav.benefits') }}</a
                >
            </nav>
            <div class="flex items-center gap-4">
                <template v-if="$page.props.auth.user">
                    <Button
                        as-child
                        variant="default"
                        class="bg-purple-600 hover:bg-purple-700"
                    >
                        <Link
                            :href="loggedInNavBtn"
                            >{{ userRole === 'admin' ? 'Dashboard' : 'Classroom' }}</Link
                        >
                    </Button>
                </template>
                <template v-else>
                    <Button
                        as-child
                        variant="ghost"
                        class="text-slate-700 hover:text-purple-600"
                    >
                        <Link :href="login()">Log in</Link>
                    </Button>
                    <Button
                        v-if="canRegister"
                        as-child
                        variant="default"
                        class="bg-purple-600 text-white shadow-sm hover:bg-purple-700"
                    >
                        <Link :href="register()">Get Started</Link>
                    </Button>
                </template>
            </div>
        </header>

        <!-- Hero Section -->
        <section
            class="container mx-auto px-6 pt-24 pb-20 text-center lg:pt-32 lg:pb-28"
        >
            <Badge
                class="mb-6 border-purple-200 bg-white/60 text-purple-700 backdrop-blur-sm transition-colors hover:bg-white/80"
                variant="outline"
            >
                <Sparkles class="mr-2 h-4 w-4" />
                Smarter grading for modern classrooms
            </Badge>
            <h1
                class="mx-auto mb-8 max-w-4xl text-5xl leading-tight font-extrabold tracking-tight text-slate-900 md:text-7xl"
            >
                AI-Powered Essay Feedback in
                <span
                    class="bg-linear-to-r from-purple-600 to-indigo-600 bg-clip-text text-transparent"
                    >Seconds</span
                >
            </h1>
            <p
                class="mx-auto mb-10 max-w-2xl text-xl leading-relaxed text-slate-600"
            >
                Save hours of manual grading while giving your students the
                detailed, instant feedback they need to improve their writing
                faster.
            </p>
            <div
                class="flex flex-col items-center justify-center gap-4 sm:flex-row"
            >
                <Button
                    v-if="!$page.props.auth.user && canRegister"
                    as-child
                    size="lg"
                    class="h-14 bg-purple-600 px-8 text-lg shadow-lg shadow-purple-200 hover:bg-purple-700"
                >
                    <Link :href="register()">Start Free Trial</Link>
                </Button>
                <Button
                    as-child
                    size="lg"
                    variant="outline"
                    class="h-14 border-slate-200 bg-white px-8 text-lg text-slate-700 hover:bg-slate-50"
                >
                    <a href="#how-it-works">See How It Works</a>
                </Button>
            </div>

            <!-- Dashboard Preview Hero -->
            <div class="relative mx-auto mt-20 max-w-5xl">
                <div
                    class="absolute -inset-1 rounded-2xl bg-linear-to-b from-purple-200 to-transparent opacity-50 blur-lg"
                ></div>
                <div
                    class="relative flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl md:flex-row"
                >
                    <div
                        class="border-r border-slate-100 bg-slate-50/50 p-6 md:w-2/3"
                    >
                        <div class="mb-4 flex items-center gap-2">
                            <div class="h-3 w-3 rounded-full bg-red-400"></div>
                            <div
                                class="h-3 w-3 rounded-full bg-amber-400"
                            ></div>
                            <div
                                class="h-3 w-3 rounded-full bg-green-400"
                            ></div>
                        </div>
                        <div class="space-y-4">
                            <div
                                class="h-8 w-1/3 rounded border border-slate-200 bg-white"
                            ></div>
                            <div class="space-y-2">
                                <div
                                    class="h-4 w-full rounded border border-slate-200 bg-white"
                                ></div>
                                <div
                                    class="h-4 w-5/6 rounded border border-slate-200 bg-white"
                                ></div>
                                <div
                                    class="h-4 w-full rounded border border-slate-200 bg-white"
                                ></div>
                                <div
                                    class="h-4 w-4/5 rounded border border-slate-200 bg-white"
                                ></div>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col gap-4 bg-white p-6 md:w-1/3">
                        <div
                            class="rounded-lg border border-purple-100 bg-purple-50 p-4"
                        >
                            <div class="mb-2 flex items-center justify-between">
                                <span class="font-semibold text-purple-900"
                                    >AI Score</span
                                >
                                <span class="text-xl font-bold text-purple-600"
                                    >85/100</span
                                >
                            </div>
                            <div
                                class="h-2 overflow-hidden rounded-full bg-purple-200"
                            >
                                <div class="h-full w-[85%] bg-purple-500"></div>
                            </div>
                        </div>
                        <div class="rounded-lg border border-slate-100 p-4">
                            <h4
                                class="mb-2 flex items-center gap-2 font-medium text-slate-800"
                            >
                                <Sparkles class="h-4 w-4 text-purple-500" />
                                Grammar & Style
                            </h4>
                            <p class="text-sm text-slate-600">
                                Excellent sentence variety. Consider
                                strengthening your thesis statement in paragraph
                                1.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Problem -> Solution Section -->
        <section
            class="border-y border-slate-100 bg-white py-24"
            id="problem-solution"
        >
            <div class="container mx-auto max-w-5xl px-6 text-center">
                <h2 class="mb-16 text-3xl font-bold text-slate-900">
                    Grading shouldn't be the hardest part of teaching
                </h2>
                <div class="grid items-center gap-12 md:grid-cols-2">
                    <CardIcon :icon="Clock">
                        <template #title>
                            <h3
                                class="mb-4 flex items-center gap-2 text-xl font-semibold text-red-500"
                            >
                                The Problem
                            </h3>
                        </template>
                        <template #default>
                            <p class="mb-6 text-slate-600">
                                Manual grading is slow, exhausting, and often
                                inconsistent. Teachers spend weekends reading
                                essays, while students wait days or weeks for
                                feedback.
                            </p>
                            <ul class="space-y-3">
                                <li
                                    class="flex items-center gap-3 text-slate-600"
                                >
                                    <span
                                        class="h-1.5 w-1.5 shrink-0 rounded-full bg-red-400"
                                    ></span>
                                    Burnout from endless paperwork
                                </li>
                                <li
                                    class="flex items-center gap-3 text-slate-600"
                                >
                                    <span
                                        class="h-1.5 w-1.5 shrink-0 rounded-full bg-red-400"
                                    ></span>
                                    Delayed student learning cycle
                                </li>
                                <li
                                    class="flex items-center gap-3 text-slate-600"
                                >
                                    <span
                                        class="h-1.5 w-1.5 shrink-0 rounded-full bg-red-400"
                                    ></span>
                                    Subjective scoring variations
                                </li>
                            </ul>
                        </template>
                    </CardIcon>
                    <CardIcon
                        :icon="Sparkles"
                        cardClass="bg-purple-50"
                        :iconClass="'text-purple-600'"
                    >
                        <template #title>
                            <h3
                                class="mb-4 flex items-center gap-2 text-xl font-semibold text-purple-900"
                            >
                                The EchrivaAI Solution
                            </h3>
                        </template>
                        <template #default>
                            <p class="mb-6 text-purple-800/80">
                                Automate the initial review process. Let AI
                                handle the mechanics and structured feedback, so
                                teachers can focus on higher-level mentoring.
                            </p>
                            <ul class="space-y-3">
                                <li
                                    class="flex items-center gap-3 text-purple-800/80"
                                >
                                    <CheckCircle2
                                        class="h-5 w-5 text-purple-600"
                                    />
                                    Instant, structured AI analysis
                                </li>
                                <li
                                    class="flex items-center gap-3 text-purple-800/80"
                                >
                                    <CheckCircle2
                                        class="h-5 w-5 text-purple-600"
                                    />
                                    Objective and consistent scoring
                                </li>
                                <li
                                    class="flex items-center gap-3 text-purple-800/80"
                                >
                                    <CheckCircle2
                                        class="h-5 w-5 text-purple-600"
                                    />
                                    More time for student interaction
                                </li>
                            </ul>
                        </template>
                    </CardIcon>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="bg-slate-50 py-24" id="features">
            <div class="container mx-auto max-w-6xl px-6">
                <div class="mb-16 text-center">
                    <h2
                        class="mb-4 text-3xl font-bold text-slate-900 md:text-4xl"
                    >
                        Powerful tools for educators and students
                    </h2>
                    <p class="mx-auto max-w-2xl text-lg text-slate-600">
                        Everything you need to manage assignments, evaluate
                        writing, and track progress over time.
                    </p>
                </div>

                <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                    <Card
                        class="border-slate-200 shadow-sm transition-shadow hover:shadow-md"
                    >
                        <CardHeader>
                            <div
                                class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-purple-100 text-purple-600"
                            >
                                <Brain class="h-6 w-6" />
                            </div>
                            <CardTitle>AI Essay Evaluation</CardTitle>
                            <CardDescription
                                >Instant automated scoring and detailed
                                grammatical and structural
                                feedback.</CardDescription
                            >
                        </CardHeader>
                    </Card>

                    <Card
                        class="border-slate-200 shadow-sm transition-shadow hover:shadow-md"
                    >
                        <CardHeader>
                            <div
                                class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-blue-100 text-blue-600"
                            >
                                <ArrowRight class="h-6 w-6" />
                            </div>
                            <CardTitle>Multiple Attempts</CardTitle>
                            <CardDescription
                                >Students can submit drafts, review AI feedback,
                                and improve before the final
                                deadline.</CardDescription
                            >
                        </CardHeader>
                    </Card>

                    <Card
                        class="border-slate-200 shadow-sm transition-shadow hover:shadow-md"
                    >
                        <CardHeader>
                            <div
                                class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600"
                            >
                                <Users class="h-6 w-6" />
                            </div>
                            <CardTitle>Teacher Review</CardTitle>
                            <CardDescription
                                >Teachers retain control with the ability to
                                review AI scores and provide the final
                                grade.</CardDescription
                            >
                        </CardHeader>
                    </Card>

                    <Card
                        class="border-slate-200 shadow-sm transition-shadow hover:shadow-md"
                    >
                        <CardHeader>
                            <div
                                class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-amber-100 text-amber-600"
                            >
                                <FileText class="h-6 w-6" />
                            </div>
                            <CardTitle>Classroom Management</CardTitle>
                            <CardDescription
                                >Easily create classes, invite students, and
                                organize tasks in one unified
                                workspace.</CardDescription
                            >
                        </CardHeader>
                    </Card>

                    <Card
                        class="border-slate-200 shadow-sm transition-shadow hover:shadow-md"
                    >
                        <CardHeader>
                            <div
                                class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600"
                            >
                                <UploadCloud class="h-6 w-6" />
                            </div>
                            <CardTitle>File Uploads</CardTitle>
                            <CardDescription
                                >Seamlessly attach Word documents or PDFs to
                                assignments for AI processing.</CardDescription
                            >
                        </CardHeader>
                    </Card>

                    <Card
                        class="border-slate-200 shadow-sm transition-shadow hover:shadow-md"
                    >
                        <CardHeader>
                            <div
                                class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-rose-100 text-rose-600"
                            >
                                <BarChart3 class="h-6 w-6" />
                            </div>
                            <CardTitle>Feedback History</CardTitle>
                            <CardDescription
                                >Track student improvement over time with
                                detailed analytics and submission
                                history.</CardDescription
                            >
                        </CardHeader>
                    </Card>
                </div>
            </div>
        </section>

        <!-- How It Works -->
        <section class="bg-white py-24" id="how-it-works">
            <div class="container mx-auto max-w-5xl px-6">
                <div class="mb-16 text-center">
                    <h2
                        class="mb-4 text-3xl font-bold text-slate-900 md:text-4xl"
                    >
                        How EchrivaAI Works
                    </h2>
                    <p class="mx-auto max-w-2xl text-lg text-slate-600">
                        A seamless workflow designed for both educators and
                        students.
                    </p>
                </div>

                <div class="relative">
                    <div
                        class="absolute top-0 bottom-0 left-8 w-px bg-slate-200 md:left-1/2 md:-translate-x-1/2"
                    ></div>

                    <div class="space-y-16">
                        <!-- Step 1 -->
                        <div
                            class="relative flex flex-col items-center gap-8 md:flex-row md:gap-16"
                        >
                            <div
                                class="flex justify-start md:w-1/2 md:justify-end"
                            >
                                <div
                                    class="w-full max-w-md rounded-2xl border border-purple-100 bg-purple-50 p-6 shadow-sm"
                                >
                                    <h4 class="mb-2 font-bold text-slate-900">
                                        Create Task
                                    </h4>
                                    <div
                                        class="mb-2 h-2 w-3/4 rounded bg-white"
                                    ></div>
                                    <div
                                        class="mb-4 h-2 w-1/2 rounded bg-white"
                                    ></div>
                                    <Button
                                        disabled
                                        size="sm"
                                        class="w-full bg-purple-200 text-purple-700"
                                        >Assign to Class</Button
                                    >
                                </div>
                            </div>
                            <div
                                class="absolute left-8 z-10 flex h-8 w-8 -translate-x-1/2 items-center justify-center rounded-full border-4 border-white bg-purple-600 font-bold text-white shadow-sm md:left-1/2"
                            >
                                1
                            </div>
                            <div class="pl-16 md:w-1/2 md:pl-0">
                                <h3
                                    class="mb-2 text-xl font-bold text-slate-900"
                                >
                                    Teacher creates an assignment
                                </h3>
                                <p class="text-slate-600">
                                    Set instructions, grading rubrics, and
                                    deadlines. Invite your students to the
                                    classroom.
                                </p>
                            </div>
                        </div>

                        <!-- Step 2 -->
                        <div
                            class="relative flex flex-col items-center gap-8 md:flex-row-reverse md:gap-16"
                        >
                            <div class="flex justify-start md:w-1/2">
                                <div
                                    class="w-full max-w-md rounded-2xl border border-blue-100 bg-blue-50 p-6 shadow-sm"
                                >
                                    <div class="mb-4 flex items-center gap-3">
                                        <div
                                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded bg-blue-200"
                                        >
                                            <FileText
                                                class="h-5 w-5 text-blue-600"
                                            />
                                        </div>
                                        <div class="flex-1">
                                            <div
                                                class="mb-1 h-3 w-full rounded bg-white"
                                            ></div>
                                            <div
                                                class="h-3 w-2/3 rounded bg-white"
                                            ></div>
                                        </div>
                                    </div>
                                    <Button
                                        disabled
                                        size="sm"
                                        class="w-full bg-blue-600 text-white"
                                        >Submit Essay</Button
                                    >
                                </div>
                            </div>
                            <div
                                class="absolute left-8 z-10 flex h-8 w-8 -translate-x-1/2 items-center justify-center rounded-full border-4 border-white bg-blue-500 font-bold text-white shadow-sm md:left-1/2"
                            >
                                2
                            </div>
                            <div class="pl-16 md:w-1/2 md:pl-0 md:text-right">
                                <h3
                                    class="mb-2 text-xl font-bold text-slate-900"
                                >
                                    Student submits their essay
                                </h3>
                                <p class="text-slate-600">
                                    Students write and upload their documents.
                                    They can submit drafts for preliminary AI
                                    feedback before the final deadline.
                                </p>
                            </div>
                        </div>

                        <!-- Step 3 -->
                        <div
                            class="relative flex flex-col items-center gap-8 md:flex-row md:gap-16"
                        >
                            <div
                                class="flex justify-start md:w-1/2 md:justify-end"
                            >
                                <div
                                    class="w-full max-w-md rounded-2xl border border-emerald-100 bg-emerald-50 p-6 shadow-sm"
                                >
                                    <div
                                        class="mb-4 flex items-center justify-between"
                                    >
                                        <span class="font-bold text-emerald-900"
                                            >AI Evaluation Complete</span
                                        >
                                        <Badge
                                            class="bg-emerald-200 text-emerald-800 hover:bg-emerald-200"
                                            >Score: 92</Badge
                                        >
                                    </div>
                                    <div class="space-y-2">
                                        <div
                                            class="h-2 w-full rounded bg-emerald-200/50"
                                        ></div>
                                        <div
                                            class="h-2 w-full rounded bg-emerald-200/50"
                                        ></div>
                                        <div
                                            class="h-2 w-4/5 rounded bg-emerald-200/50"
                                        ></div>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="absolute left-8 z-10 flex h-8 w-8 -translate-x-1/2 items-center justify-center rounded-full border-4 border-white bg-emerald-500 font-bold text-white shadow-sm md:left-1/2"
                            >
                                3
                            </div>
                            <div class="pl-16 md:w-1/2 md:pl-0">
                                <h3
                                    class="mb-2 text-xl font-bold text-slate-900"
                                >
                                    Instant AI Feedback
                                </h3>
                                <p class="text-slate-600">
                                    The system instantly evaluates the essay,
                                    provides structured feedback, and suggests a
                                    score. The teacher can then review and
                                    finalize.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section
            class="bg-linear-to-br from-purple-900 to-indigo-900 py-24 text-white"
        >
            <div class="container mx-auto max-w-3xl px-6 text-center">
                <h2 class="mb-6 text-4xl font-bold">
                    Start improving writing with AI today
                </h2>
                <p class="mb-10 text-xl text-purple-200">
                    Join thousands of educators who are saving time and giving
                    better feedback to their students.
                </p>
                <div class="flex flex-col justify-center gap-4 sm:flex-row">
                    <Button
                        v-if="canRegister"
                        as-child
                        size="lg"
                        class="h-14 border-none bg-white px-8 text-lg text-purple-900 hover:bg-slate-100"
                    >
                        <Link :href="register()">Get Started for Free</Link>
                    </Button>
                    <Button
                        as-child
                        size="lg"
                        variant="outline"
                        class="h-14 border-purple-400 px-8 text-lg text-white hover:bg-purple-800/50"
                    >
                        <Link :href="login()">Log in to Dashboard</Link>
                    </Button>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-t border-slate-200 bg-slate-50 py-12">
            <div
                class="container mx-auto flex flex-col items-center justify-between gap-6 px-6 md:flex-row"
            >
                <div class="flex items-center gap-2">
                    <Brain class="h-6 w-6 text-purple-600" />
                    <span class="text-lg font-bold text-slate-900"
                        >EchrivaAI</span
                    >
                </div>
                <div class="text-sm text-slate-500">
                    &copy; {{ new Date().getFullYear() }} EchrivaAI. All rights
                    reserved.
                </div>
                <div class="flex gap-6 text-sm text-slate-600">
                    <a href="#" class="hover:text-purple-600">Privacy Policy</a>
                    <a href="#" class="hover:text-purple-600"
                        >Terms of Service</a
                    >
                    <a href="#" class="hover:text-purple-600"
                        >Contact Support</a
                    >
                </div>
            </div>
        </footer>
    </div>
</template>
