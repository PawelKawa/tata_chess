<template>
  <div class="contact">
    <h1>Kontakt</h1>
    <p class="intro">Masz pytanie dotyczące turnieju lub chcesz się zapisać? Napisz — odpiszę najszybciej jak mogę.</p>

    <!-- Formularz -->
    <form v-if="!sent" class="form" @submit.prevent="submit">
      <div class="field">
        <label for="name">Imię i nazwisko <span class="required">*</span></label>
        <input
          id="name"
          v-model="form.name"
          type="text"
          placeholder="Jan Kowalski"
          :class="{ 'input--error': errors.name }"
          autocomplete="name"
        />
        <span v-if="errors.name" class="error-msg">{{ errors.name }}</span>
      </div>

      <div class="field">
        <label for="email">Email <span class="optional">(opcjonalnie — jeśli oczekujesz odpowiedzi)</span></label>
        <input
          id="email"
          v-model="form.email"
          type="email"
          placeholder="jan@przykład.pl"
          :class="{ 'input--error': errors.email }"
          autocomplete="email"
        />
        <span v-if="errors.email" class="error-msg">{{ errors.email }}</span>
      </div>

      <div class="field">
        <label for="message">Wiadomość <span class="required">*</span></label>
        <textarea
          id="message"
          v-model="form.message"
          rows="5"
          placeholder="Napisz swoją wiadomość..."
          :class="{ 'input--error': errors.message }"
        />
        <span v-if="errors.message" class="error-msg">{{ errors.message }}</span>
      </div>

      <div v-if="serverError" class="server-error">
        Coś poszło nie tak. Spróbuj ponownie lub wróć później.
      </div>

      <button type="submit" class="btn" :disabled="loading">
        {{ loading ? 'Wysyłanie...' : 'Wyślij wiadomość' }}
      </button>
    </form>

    <!-- Potwierdzenie -->
    <div v-else class="success">
      <div class="success__icon">✓</div>
      <h2>Wiadomość wysłana!</h2>
      <p>Dziękuję za kontakt. Jeśli podałeś email, odpiszę najszybciej jak mogę.</p>
      <button class="btn btn--outline" @click="reset">Wyślij kolejną wiadomość</button>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { api } from '../services/api.js'

const form = reactive({ name: '', email: '', message: '' })
const errors = reactive({ name: '', email: '', message: '' })
const loading = ref(false)
const sent = ref(false)
const serverError = ref(false)

function validate() {
  errors.name = form.name.trim() ? '' : 'Imię i nazwisko jest wymagane.'
  errors.email = (!form.email || /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email))
    ? ''
    : 'Podaj poprawny adres email.'
  errors.message = form.message.trim() ? '' : 'Wiadomość jest wymagana.'
  return !errors.name && !errors.email && !errors.message
}

async function submit() {
  if (!validate()) return
  loading.value = true
  serverError.value = false
  try {
    await api.sendContact({
      name:    form.name.trim(),
      email:   form.email.trim() || null,
      message: form.message.trim(),
    })
    sent.value = true
  } catch {
    serverError.value = true
  } finally {
    loading.value = false
  }
}

function reset() {
  form.name = ''
  form.email = ''
  form.message = ''
  errors.name = ''
  errors.email = ''
  errors.message = ''
  sent.value = false
  serverError.value = false
}
</script>

<style scoped>
.contact { max-width: 560px; }

h1 {
  font-size: 1.8rem;
  color: #1a1a2e;
  margin-bottom: .5rem;
}
.intro {
  color: #555;
  margin-bottom: 2rem;
  line-height: 1.6;
}

.form { display: flex; flex-direction: column; gap: 1.25rem; }

.field { display: flex; flex-direction: column; gap: .35rem; }

label {
  font-size: .9rem;
  font-weight: 600;
  color: #333;
}
.required { color: #e53e3e; }
.optional { font-weight: 400; color: #888; font-size: .82rem; }

input, textarea {
  padding: .65rem .85rem;
  border: 1.5px solid #ddd;
  border-radius: 6px;
  font-size: 1rem;
  font-family: inherit;
  transition: border-color .15s;
  outline: none;
}
input:focus, textarea:focus { border-color: #4f46e5; }
textarea { resize: vertical; }

.input--error { border-color: #e53e3e; }
.error-msg { font-size: .82rem; color: #e53e3e; }

.server-error {
  background: #fff5f5;
  border: 1px solid #feb2b2;
  color: #c53030;
  padding: .75rem 1rem;
  border-radius: 6px;
  font-size: .9rem;
}

.btn {
  background: #1a1a2e;
  color: white;
  border: none;
  padding: .75rem 1.5rem;
  border-radius: 6px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  align-self: flex-start;
  transition: opacity .15s;
}
.btn:hover:not(:disabled) { opacity: .85; }
.btn:disabled { opacity: .5; cursor: not-allowed; }
.btn--outline {
  background: transparent;
  color: #1a1a2e;
  border: 2px solid #1a1a2e;
  margin-top: 1rem;
}

.success {
  text-align: center;
  padding: 3rem 1rem;
}
.success__icon {
  font-size: 3rem;
  background: #e6ffed;
  color: #22863a;
  width: 4rem;
  height: 4rem;
  line-height: 4rem;
  border-radius: 50%;
  margin: 0 auto 1rem;
}
.success h2 { color: #1a1a2e; margin-bottom: .5rem; }
.success p { color: #555; }
</style>
