<template>
  <article class="post-card">
    <RouterLink :to="{ name: 'post', params: { slug: post.slug } }" class="post-card__link">
      <img
        v-if="post.cover_image"
        :src="post.cover_image"
        :alt="post.title"
        class="post-card__image"
      />
      <div class="post-card__body">
        <time class="post-card__date">{{ formattedDate }}</time>
        <h2 class="post-card__title">{{ post.title }}</h2>
        <p class="post-card__excerpt">{{ post.excerpt }}</p>
        <span class="post-card__more">Czytaj więcej →</span>
      </div>
    </RouterLink>
  </article>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  post: {
    type: Object,
    required: true,
  },
})

const formattedDate = computed(() => {
  if (!props.post.published_at) return ''
  return new Date(props.post.published_at).toLocaleDateString('pl-PL', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  })
})
</script>

<style scoped>
.post-card {
  background: white;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0,0,0,.08);
  margin-bottom: 2rem;
}
.post-card__link {
  display: block;
  text-decoration: none;
  color: inherit;
}
.post-card__image {
  width: 100%;
  height: 280px;
  object-fit: cover;
  display: block;
}
.post-card__body {
  padding: 1.5rem;
}
.post-card__date {
  font-size: .85rem;
  color: #888;
  display: block;
  margin-bottom: .4rem;
}
.post-card__title {
  font-size: 1.4rem;
  margin-bottom: .75rem;
  color: #1a1a2e;
}
.post-card__excerpt {
  color: #555;
  line-height: 1.6;
  margin-bottom: 1rem;
}
.post-card__more {
  color: #4f46e5;
  font-weight: 600;
  font-size: .9rem;
}
.post-card:hover .post-card__more {
  text-decoration: underline;
}
</style>
