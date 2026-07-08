<template>
  <div>
    <h1 class="page-title">Aktualności</h1>

    <div v-if="loading" class="state">Ładowanie...</div>

    <div v-else-if="error" class="state state--error">
      Nie udało się załadować postów. Spróbuj ponownie później.
    </div>

    <div v-else-if="posts.length === 0" class="state">
      Brak postów.
    </div>

    <template v-else>
      <PostCard v-for="post in posts" :key="post.id" :post="post" />
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { api } from '../services/api.js'
import PostCard from '../components/PostCard.vue'

const posts   = ref([])
const loading = ref(true)
const error   = ref(false)

onMounted(async () => {
  try {
    posts.value = await api.getPosts()
  } catch {
    error.value = true
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.page-title {
  font-size: 1.8rem;
  color: #1a1a2e;
  margin-bottom: 1.5rem;
}
.state {
  text-align: center;
  padding: 3rem;
  color: #888;
}
.state--error { color: #c0392b; }
</style>
