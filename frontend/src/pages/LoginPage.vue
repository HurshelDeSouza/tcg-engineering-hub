<template>
  <div class="auth-wrapper">
    <div class="auth-card">
      <!-- Logo -->
      <div class="auth-logo">
        <div class="logo-icon">TCG</div>
        <h1>Engineering Hub</h1>
        <p>The Cloud Group · Framework Manager</p>
      </div>

      <!-- Form -->
      <form @submit.prevent="handleLogin" class="auth-form">
        <div class="form-group" :class="{ error: errors.email }">
          <label>Email</label>
          <div class="input-wrapper">
            <i class="icon">✉</i>
            <input v-model="form.email" type="email" placeholder="admin@tcg.com" autocomplete="email" />
          </div>
          <span v-if="errors.email" class="error-msg">{{ errors.email[0] }}</span>
        </div>

        <div class="form-group" :class="{ error: errors.password }">
          <label>Password</label>
          <div class="input-wrapper">
            <i class="icon">🔒</i>
            <input v-model="form.password" :type="showPass ? 'text' : 'password'" placeholder="••••••••" />
            <button type="button" class="pass-toggle" @click="showPass = !showPass">
              {{ showPass ? '🙈' : '👁' }}
            </button>
          </div>
        </div>

        <div v-if="generalError" class="alert alert-danger">
          {{ generalError }}
        </div>

        <button type="submit" class="btn btn-primary btn-block" :disabled="loading">
          <span v-if="loading" class="spinner" />
          <span v-else>Sign In</span>
        </button>
      </form>

      <!-- Demo hint -->
      <div class="demo-hint">
        <small>Demo: admin@tcg.com / password</small>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const auth    = useAuthStore()
const router  = useRouter()
const loading = ref(false)
const showPass = ref(false)
const generalError = ref(null)
const errors  = ref({})

const form = reactive({ email: '', password: '' })

async function handleLogin() {
  loading.value = true
  errors.value  = {}
  generalError.value = null

  try {
    await auth.login(form.email, form.password)
    router.push('/projects')
  } catch (err) {
    if (err.response?.status === 422) {
      errors.value = err.response.data.errors || {}
      generalError.value = err.response.data.message || 'Validation error'
    } else if (err.response?.data?.message) {
      generalError.value = err.response.data.message
    } else {
      generalError.value = 'Invalid credentials. Please try again.'
    }
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.auth-wrapper {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
}
.auth-card {
  background: #fff;
  border-radius: 16px;
  padding: 2.5rem;
  width: 100%;
  max-width: 420px;
  box-shadow: 0 25px 50px rgba(0,0,0,.3);
}
.auth-logo { text-align: center; margin-bottom: 2rem; }
.logo-icon {
  display: inline-flex; align-items: center; justify-content: center;
  width: 56px; height: 56px; background: #7367f0;
  border-radius: 12px; color: #fff; font-weight: 800; font-size: 1.1rem;
  margin-bottom: .75rem;
}
.auth-logo h1 { font-size: 1.5rem; font-weight: 700; color: #2d3748; margin: 0; }
.auth-logo p  { color: #718096; font-size: .875rem; margin: .25rem 0 0; }
.form-group { margin-bottom: 1.25rem; }
.form-group label { display: block; font-size: .875rem; font-weight: 500; color: #4a5568; margin-bottom: .5rem; }
.input-wrapper { position: relative; display: flex; align-items: center; }
.input-wrapper .icon { position: absolute; left: .875rem; font-style: normal; }
.input-wrapper input {
  width: 100%; padding: .75rem .875rem .75rem 2.5rem;
  border: 1.5px solid #e2e8f0; border-radius: 8px; font-size: .95rem;
  transition: border-color .2s;
}
.input-wrapper input:focus { outline: none; border-color: #7367f0; }
.form-group.error input { border-color: #e53e3e; }
.error-msg { font-size: .8rem; color: #e53e3e; margin-top: .25rem; display: block; }
.pass-toggle { position: absolute; right: .875rem; background: none; border: none; cursor: pointer; padding: 0; }
.alert-danger { background: #fff5f5; border: 1px solid #fed7d7; color: #c53030; border-radius: 8px; padding: .75rem 1rem; font-size: .875rem; margin-bottom: 1rem; }
.btn-primary {
  width: 100%; padding: .875rem; background: #7367f0; color: #fff;
  border: none; border-radius: 8px; font-size: 1rem; font-weight: 600; cursor: pointer;
  transition: background .2s;
}
.btn-primary:hover:not(:disabled) { background: #5e50ee; }
.btn-primary:disabled { opacity: .6; cursor: not-allowed; }
.spinner {
  display: inline-block; width: 18px; height: 18px; border: 2px solid rgba(255,255,255,.3);
  border-top-color: #fff; border-radius: 50%; animation: spin .7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }
.demo-hint { text-align: center; margin-top: 1.5rem; color: #a0aec0; font-size: .8rem; }
</style>
