<template>
  <div class="app-layout">
    <!-- Sidebar -->
    <aside class="sidebar" :class="{ collapsed: sidebarCollapsed }">
      <div class="sidebar-header">
        <div class="brand">
          <span class="brand-icon">TCG</span>
          <span class="brand-name" v-if="!sidebarCollapsed">Engineering Hub</span>
        </div>
        <button class="collapse-btn" @click="sidebarCollapsed = !sidebarCollapsed">
          {{ sidebarCollapsed ? '›' : '‹' }}
        </button>
      </div>

      <nav class="sidebar-nav">
        <router-link to="/projects" class="nav-item">
          <span class="nav-icon">📁</span>
          <span class="nav-label" v-if="!sidebarCollapsed">Projects</span>
        </router-link>
      </nav>

      <div class="sidebar-footer">
        <div class="user-info" v-if="!sidebarCollapsed">
          <div class="user-avatar">{{ auth.user?.name?.charAt(0) }}</div>
          <div class="user-meta">
            <div class="user-name">{{ auth.user?.name }}</div>
            <div class="user-role">{{ auth.user?.role }}</div>
          </div>
        </div>
        <button class="logout-btn" @click="handleLogout" title="Logout">⬅</button>
      </div>
    </aside>

    <!-- Main content -->
    <main class="main-content">
      <slot />
    </main>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const auth             = useAuthStore()
const router           = useRouter()
const sidebarCollapsed = ref(false)

async function handleLogout() {
  await auth.logout()
  router.push('/login')
}
</script>

<style scoped>
.app-layout { display: flex; min-height: 100vh; background: #f8f7fa; }

.sidebar {
  width: 260px; min-height: 100vh; background: #2d2d2d;
  display: flex; flex-direction: column; transition: width .25s ease;
  flex-shrink: 0;
}
.sidebar.collapsed { width: 72px; }

.sidebar-header {
  padding: 1.25rem 1rem; display: flex; align-items: center; justify-content: space-between;
  border-bottom: 1px solid rgba(255,255,255,.08);
}
.brand { display: flex; align-items: center; gap: .75rem; overflow: hidden; }
.brand-icon {
  min-width: 36px; height: 36px; background: #7367f0; border-radius: 8px;
  display: flex; align-items: center; justify-content: center;
  color: #fff; font-weight: 800; font-size: .85rem;
}
.brand-name { color: #fff; font-weight: 600; font-size: .95rem; white-space: nowrap; }
.collapse-btn {
  background: none; border: none; color: rgba(255,255,255,.5); cursor: pointer;
  font-size: 1.2rem; padding: 0 .25rem;
}

.sidebar-nav { padding: 1rem .75rem; flex: 1; }
.nav-item {
  display: flex; align-items: center; gap: .75rem; padding: .75rem 1rem;
  border-radius: 8px; color: rgba(255,255,255,.7); text-decoration: none;
  font-size: .9rem; font-weight: 500; transition: all .2s;
}
.nav-item:hover, .nav-item.router-link-active {
  background: rgba(115,103,240,.2); color: #7367f0;
}
.nav-icon { font-size: 1.1rem; }

.sidebar-footer {
  padding: 1rem; border-top: 1px solid rgba(255,255,255,.08);
  display: flex; align-items: center; gap: .75rem;
}
.user-info { display: flex; align-items: center; gap: .75rem; flex: 1; overflow: hidden; }
.user-avatar {
  min-width: 36px; height: 36px; background: #7367f0; border-radius: 50%;
  display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 600;
}
.user-name { color: #fff; font-size: .875rem; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.user-role { color: rgba(255,255,255,.5); font-size: .75rem; text-transform: capitalize; }
.logout-btn {
  background: none; border: none; color: rgba(255,255,255,.5); cursor: pointer;
  font-size: 1.1rem; padding: .25rem;
}
.logout-btn:hover { color: #ff4c51; }

.main-content { flex: 1; overflow: auto; }
</style>
