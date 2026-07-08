<template>
  <div class="app">
    <Navbar />
    <main class="main">
      <RouterView />
    </main>
    <Footer />
  </div>
</template>

<script setup>
import Navbar from './components/Navbar.vue'
import Footer from './components/Footer.vue'
</script>

<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: system-ui, sans-serif; background: #f5f5f5; color: #222; }
.app { display: flex; flex-direction: column; min-height: 100vh; }
.main { flex: 1; max-width: 860px; margin: 0 auto; padding: 2rem 1rem; width: 100%; }
</style>
