<template>
  <div v-if="images.length > 0" class="gallery">
    <h3 class="gallery__title">Zdjęcia</h3>
    <div class="gallery__grid">
      <button
        v-for="(img, index) in images"
        :key="img.path"
        class="gallery__thumb-btn"
        @click="openLightbox(index)"
      >
        <img :src="img.path" :alt="`Zdjęcie ${index + 1}`" class="gallery__thumb" />
      </button>
    </div>

    <vue-easy-lightbox
      :visible="lightboxVisible"
      :imgs="lightboxImages"
      :index="lightboxIndex"
      @hide="lightboxVisible = false"
    />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import VueEasyLightbox from 'vue-easy-lightbox'

const props = defineProps({
  images: {
    type: Array,
    default: () => [],
  },
})

const lightboxVisible = ref(false)
const lightboxIndex   = ref(0)

const lightboxImages = computed(() =>
  props.images.map((img) => ({ src: img.path }))
)

function openLightbox(index) {
  lightboxIndex.value   = index
  lightboxVisible.value = true
}
</script>

<style scoped>
.gallery { margin-top: 2.5rem; }
.gallery__title {
  font-size: 1.1rem;
  color: #1a1a2e;
  margin-bottom: 1rem;
}
.gallery__grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: .75rem;
}
.gallery__thumb-btn {
  border: none;
  padding: 0;
  cursor: pointer;
  border-radius: 6px;
  overflow: hidden;
  aspect-ratio: 1;
}
.gallery__thumb {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
  transition: opacity .2s;
}
.gallery__thumb-btn:hover .gallery__thumb { opacity: .85; }
</style>
