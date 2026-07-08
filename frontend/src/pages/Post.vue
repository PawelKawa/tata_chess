<template>
  <div>
    <div v-if="loading" class="state">Ładowanie...</div>

    <div v-else-if="error" class="state state--error">
      Post nie został znaleziony.
    </div>

    <article v-else-if="post" class="post">
      <RouterLink to="/" class="post__back">← Wróć do aktualności</RouterLink>

      <img
        v-if="post.cover_image"
        :src="post.cover_image"
        :alt="post.title"
        class="post__cover"
      />

      <header class="post__header">
        <time class="post__date">{{ formattedDate }}</time>
        <h1 class="post__title">{{ post.title }}</h1>
      </header>

      <div class="post__content" v-html="post.content" />

      <PhotoGallery :images="post.images ?? []" />
    </article>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute }                 from 'vue-router'
import { api }                      from '../services/api.js'
import PhotoGallery                 from '../components/PhotoGallery.vue'

const route   = useRoute()
const post    = ref(null)
const loading = ref(true)
const error   = ref(false)

onMounted(async () => {
  try {
    post.value = await api.getPost(route.params.slug)
  } catch {
    error.value = true
  } finally {
    loading.value = false
  }
})

const formattedDate = computed(() => {
  if (!post.value?.published_at) return ''
  return new Date(post.value.published_at).toLocaleDateString('pl-PL', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  })
})
</script>

<style scoped>
.state { text-align: center; padding: 3rem; color: #888; }
.state--error { color: #c0392b; }

.post__back {
  display: inline-block;
  margin-bottom: 1.5rem;
  color: #4f46e5;
  text-decoration: none;
  font-size: .9rem;
}
.post__back:hover { text-decoration: underline; }

.post__cover {
  width: 100%;
  max-height: 420px;
  object-fit: cover;
  border-radius: 8px;
  margin-bottom: 1.5rem;
  display: block;
}
.post__header { margin-bottom: 1.5rem; }
.post__date { font-size: .85rem; color: #888; display: block; margin-bottom: .5rem; }
.post__title { font-size: 2rem; color: #1a1a2e; }

.post__content {
  line-height: 1.75;
  color: #333;
}
.post__content :deep(h1),
.post__content :deep(h2),
.post__content :deep(h3) {
  color: #1a1a2e;
  margin: 1.5rem 0 .75rem;
}
.post__content :deep(p)  { margin-bottom: 1rem; }
.post__content :deep(ul),
.post__content :deep(ol) { padding-left: 1.5rem; margin-bottom: 1rem; }
.post__content :deep(table) {
  border-collapse: collapse;
  width: 100%;
  margin-bottom: 1rem;
}
.post__content :deep(th),
.post__content :deep(td) {
  border: 1px solid #ddd;
  padding: .5rem .75rem;
  text-align: left;
}
.post__content :deep(th) { background: #f0f0f0; font-weight: 600; }
</style>
