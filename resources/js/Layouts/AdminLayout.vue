<script setup lang="ts">
import { ref } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { cn } from '@/lib/utils'
import { Button } from '@/components/ui/button'
import {
    LayoutDashboard,
    ShieldCheck,
    FileText,
    ExternalLink,
    Users,
    ChevronLeft,
    ChevronRight,
    LogOut,
    Settings
} from 'lucide-vue-next'

const isCollapsed = ref(false)
const page = usePage()

// Liste des liens de navigation
const navigation = [
    { name: 'Tableau de bord', href: route('dashboard'), icon: LayoutDashboard, active: route().current('dashboard') },
    { name: 'Gestion des Clubs', href: route('clubs.index'), icon: ShieldCheck, active: route().current('clubs.*') },
    { name: 'Mentions Légales', href: route('legals.index'), icon: FileText, active: route().current('legals.*') },
    { name: 'Liens Utiles', href: route('liensUtiles.index'), icon: ExternalLink, active: route().current('liensUtiles.*') },
    { name: 'Utilisateurs', href: '#', icon: Users, active: false },
]
</script>

<template>
    <div class="flex min-h-screen bg-zinc-50 dark:bg-zinc-950">
        <aside
            :class="cn(
        'relative flex flex-col border-r bg-white dark:bg-zinc-900 transition-all duration-300 ease-in-out',
        isCollapsed ? 'w-20' : 'w-64'
      )"
        >
            <div class="flex h-16 items-center border-b px-6">
                <div v-if="!isCollapsed" class="font-bold text-indigo-600 transition-opacity">
                    Collect&Share <span class="text-zinc-400 text-xs">Admin</span>
                </div>
                <div v-else class="mx-auto font-bold text-indigo-600">C&S</div>
            </div>

            <nav class="flex-1 space-y-1 p-3">
                <Link
                    v-for="item in navigation"
                    :key="item.name"
                    :href="item.href"
                    :class="cn(
            'flex items-center rounded-lg px-3 py-2 transition-colors',
            item.active
              ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/20 dark:text-indigo-400'
              : 'text-zinc-600 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800'
          )"
                >
                    <component :is="item.icon" class="h-5 w-5 shrink-0" />
                    <span v-if="!isCollapsed" class="ml-3 font-medium transition-opacity">
            {{ item.name }}
          </span>
                </Link>
            </nav>

            <div class="border-t p-3 space-y-1">
                <button
                    @click="isCollapsed = !isCollapsed"
                    class="flex w-full items-center rounded-lg px-3 py-2 text-zinc-600 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800"
                >
                    <ChevronLeft v-if="!isCollapsed" class="h-5 w-5" />
                    <ChevronRight v-else class="h-5 w-5 mx-auto" />
                    <span v-if="!isCollapsed" class="ml-3 font-medium">Réduire</span>
                </button>

                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="flex w-full items-center rounded-lg px-3 py-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/10"
                >
                    <LogOut class="h-5 w-5" />
                    <span v-if="!isCollapsed" class="ml-3 font-medium">Déconnexion</span>
                </Link>
            </div>
        </aside>

        <div class="flex-1">
            <header class="flex h-16 items-center border-b bg-white px-8 dark:bg-zinc-900">
                <div class="flex flex-1 items-center">
                    <slot name="header" />
                </div>

                <div class="flex items-center gap-4">
          <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
             {{ $page.props.auth.user.firstname }}
          </span>
                    <div class="h-8 w-8 rounded-full bg-indigo-600 flex items-center justify-center text-white text-xs">
                        AD
                    </div>
                </div>
            </header>

            <main class="p-8">
                <slot />
            </main>
        </div>
    </div>
</template>
