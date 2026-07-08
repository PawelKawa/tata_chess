<template>
  <footer class="footer">
    <p>© {{ year }} Szachy Taty</p>
  </footer>
</template>

<script setup>
const year = new Date().getFullYear()
</script>

<style scoped>
.footer {
  text-align: center;
  padding: 2rem;
  background: #1a1a2e;
  color: #aaa;
  margin-top: auto;
}
</style>
